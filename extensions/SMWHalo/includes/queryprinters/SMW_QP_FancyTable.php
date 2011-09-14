<?php
/*  Copyright 2011, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


/**
 * @file
 * @ingroup SMWHaloResultPrinter
 * 
 * This result printer formats results in form of a table. The labels of result
 * items can be taken from other properties in the result. 
 * The style of the table (i.e. the css-class) can be specified in the parameter
 * "style".
 * Based on the SMW's standard table printer.
 * 
 * @author Thomas Schweitzer
 */

/**
 * New implementation of SMW's printer for result tables that allows relabeling
 * of results.
 *
 * The syntax looks like this
 * |mainlabel = Subject
 * |?unreadableProperty = Property
 * |?subjectLabel
 * |?readableProperty
 * |replace(?) = ?subjectLabel
 * |replace(?unreadableProperty) = ?readableProperty
 * 
 * This means the following:
 * 1. Three properties are queried: unreadableProperty and readableProperty and
 *    subjectLabel
 * 2. Two table columns are displayed:
 *     * The subject column with header "Subject"
 *     * The column for property unreadableProperty with header "Property"
 * 3. Properties that are assigned to other properties are not displayed in their
 *    own columns i.e. there are no columns for subjectLabel and readableProperty.
 * 4. The labels of values are replaced:
 *     * The label of subject items is provided by property subjectLabel
 *     * The label of unreadableProperty is provided by property readableProperty
 *     
 * The CSS class of the table can be specified with the parameter "style" e.g.:
 * |style=fancystyle
 * 
 * This will lead to this HTML:
 * <table class="fancystyle">...
 *     
 * @ingroup SMWQuery
 */
class SMWFancyTableResultPrinter extends SMWResultPrinter {

	//--- Fields ---
	// An array of strings, containing warnings if some replacement statements
	// are invalid.
	private $mReplaceWarnings = array();

	/**
	 * List of printrequests for which numeric sort keys are used.
	 * print request hash => true
	 * 
	 * @since 1.6.1
	 * 
	 * @var array
	 */
	protected $mColumnsWithSortKey = array();
	
	//--- Methods ---
	
	public function getName() {
		return "FancyTable";
	}

	protected function getResultText(SMWQueryResult $res, $outputmode) {
		SMWOutputs::requireHeadItem( SMW_HEADER_SORTTABLE );

		$replacements = $this->getReplacements();
		
		// Create an array with all print requests. The key is the hash code of
		// the print request. If the values in one colum are replaced by another
		// column, the value in the array is hash code of the print request of
		// the replacement. e.g. 
		// $prReplacements['1::PropertyToReplace:'] = '1::ReplacementProperty::'
		// Otherwise the value is false.
		
		$propertyPrintRequestMap = $this->createPropertyPrintRequestMap($res);
		
		// Now create the actual map of print request hashes
		$prReplacements = $this->createPrintRequestReplacementMap($res, $replacements, $propertyPrintRequestMap);
		
		// Create the HTML for the table
		$result = $this->createTableHeader();
		// The body must be created before the header as the sort keays are
		// determined there
		$body    = $this->createTableBody($res, $outputmode, $prReplacements);
		$result .= $this->createTableColumnHeaders($res, $outputmode, $prReplacements);
		$result .= $body;
		$result .= $this->createTableFooter($res, $outputmode);
		$result .= $this->createWarnings();
		
		$this->isHTML = ( $outputmode == SMW_OUTPUT_HTML ); // yes, our code can be viewed as HTML if requested, no more parsing needed
		return $result;
	}

	public function getParameters() {
		$params = parent::getParameters();
		$params = array_merge( $params, parent::textDisplayParameters() );
		return $params;
	}
	
	//--- Private methods ---
	
	/**
	 * 
	 * @return {string}
	 * 		Header of table
	 */
	private function createTableHeader() {
		global $smwgIQRunningNumber;
		// Get the style for the table
		$params = $this->m_params;
		$style = array_key_exists('style', $params) ? $params['style'] 
													: 'smwtable'; 
		
		return "<table class=\"$style\"" .
			  ( $this->mFormat == 'broadtable' ? ' width="100%"' : '' ) .
				  " id=\"querytable$smwgIQRunningNumber\">\n";
		
	}
	
	/**
	 * This table printer can relabel values in columns by values of other
	 * columns. These definitions are given in the params of the query and
	 * have the following format:
	 * replace(?propertyToReplace)=?replacementProperty
	 * 
	 * @return {array<string> => string}
	 * 		An array of replacements
	 */
	private function getReplacements() {
		$replacements = array();
		$params = $this->m_params;
		foreach ($params as $param => $pValue ) {
			if (preg_match("/^replace\((.*?)\)$/", $param, $matches)) {
				$replace = $matches[1];
				if ($replace !== '?') {
					// This is not the subject of the query result but a property
					if ($replace{0} === '?') { 
						$replace = substr($replace, 1);
					}	
				}
				if ($pValue{0} === '?') { 
					$pValue = substr($pValue, 1);
				}	
				$replacement = Title::capitalize($pValue, SMW_NS_PROPERTY);
				$replacements[$replace] = $replacement;
			}
		}
		return $replacements;
	}
	
	/**
	 * Create a map from property names to the hash code of their 
	 * print requests e.g. 
	 * $propertyPrintRequestMap['SomeProperty'] = '1::SomeProperty::'
	 * 
	 * @param {SMWQueryResult} $res
	 * 		The query result
	 * @return {array<string> => <string>}
	 */
	private function createPropertyPrintRequestMap(SMWQueryResult $res) {
		$propertyPrintRequestMap = array();
		$prs = $res->getPrintRequests();
		foreach ($prs as $pr) {
			if ($pr->getMode() === SMWPrintRequest::PRINT_THIS) {
				// PR for the subject
				$propertyPrintRequestMap['?'] = $pr->getHash();
			} else if ($pr->getMode() === SMWPrintRequest::PRINT_PROP) {
				// PR for a property
				if ($pr->getData() instanceof SMWPropertyValue) {
					$propTitle = $pr->getData()->getWikiValue();
					$propertyPrintRequestMap[$propTitle] = $pr->getHash();
				}
			}
		}
		return $propertyPrintRequestMap;
	}
	
	/**
	 * Creates a map of print requests that are replaced by other print requests
 	 * @param {SMWQueryResult} $res
	 * 		The query result
	 * @param $replacements
	 * 		A map from property names that are replaced to their replacement 
	 * 		properties.
	 * @param $propertyPrintRequestMap
	 * 		The map if print request
	 * @return {array} 
	 * 		A map of print requests and their replacements
	 */
	private function createPrintRequestReplacementMap(SMWQueryResult $res,
													  array $replacements,
													  array $propertyPrintRequestMap) {
		$prReplacements = array();
		$prs = $res->getPrintRequests();
		foreach ($prs as $pr) {
			$hash = $pr->getHash();
			// Check if there is a replacement for the PR
			$replacement = false;
			if ($pr->getMode() === SMWPrintRequest::PRINT_THIS) {
				// PR for the subject (normally first column)
				$replacement = array_key_exists('?', $replacements) 
								? $replacements['?']
								: false;
			} else if ($pr->getMode() === SMWPrintRequest::PRINT_PROP) {
				// PR for a property
				$label = strtolower($pr->getLabel());
				$replacement = array_key_exists($label, $replacements) 
								? $replacements[$label]
								: false;
			}
			if ($replacement) {
				// Get the hash code of the PR that replaces the original PR
				$repl = $replacement;
				$replacement = array_key_exists($replacement, $propertyPrintRequestMap)
								? $propertyPrintRequestMap[$replacement]
								: false;
			}
			$prReplacements[$hash] = $replacement;
		}
		
		// Create warning messages for replace statements with unknown properties
		// Store the lower case name of all properties in print requests
		$prPropertyNamesLC = array_keys($propertyPrintRequestMap);
		foreach ($prPropertyNamesLC as $key => $pName) {
			$prPropertyNamesLC[$key] = strtolower($pName);
		}
		foreach ($replacements as $replace => $replacement) {
			$unknownReplace = false;
			$unknownReplacement = false;
			$qmark = '';
			if ($replace != '?') {
				if (!in_array($replace, $prPropertyNamesLC)) {
					// The property to replace does not exist
					$unknownReplace = true;
				}
				$qmark = '?';
			}
			if (!array_key_exists(Title::capitalize($replacement, SMW_NS_PROPERTY),
								  $propertyPrintRequestMap)) {
					// The replacement property does not exist
					$unknownReplacement = true;
			}
			if ($unknownReplacement || $unknownReplace) {
				$warningStmt = "replace($qmark"
								. ($unknownReplace ? "<b>$replace</b>" : $replace)
								. ")=?"
								. ($unknownReplacement ? "<b>$replacement</b>" : $replacement);
				$this->mReplaceWarnings[] = $warningStmt;
			}
		}
				
		return $prReplacements;
	}
	
	/**
	 * Generates the HTML-code for the column headers of the table.
	 * 
	 * @param $res
	 * 		The result
	 * @param $outputmode
	 * 		The output mode
	 * @param $prReplacements
	 * 		The print request replacements
	 * @return string
	 * 		HTML of the column headers
	 */
	private function createTableColumnHeaders(SMWQueryResult $res, $outputmode, array $prReplacements) {
		$result = '';
		if ( $this->mShowHeaders != SMW_HEADERS_HIDE ) { // building headers
			$result .= "\t<tr>\n";
			
			foreach ($res->getPrintRequests() as $pr) {
				// Ignore colums that serve as replacement for other columns
				if (!in_array($pr->getHash(), $prReplacements)) {
					$attribs = array();
					
					if ( array_key_exists( $pr->getHash(), $this->mColumnsWithSortKey ) ) {
						$attribs['class'] = 'numericsort';
					}
					
					$result .= Html::rawElement(
						'th',
						$attribs,
						$pr->getText( $outputmode, ( $this->mShowHeaders == SMW_HEADERS_PLAIN ? null:$this->mLinker ) )
					);
				}
			}
			
			$result .= "\t</tr>\n";
		}
				
		return $result;
	}
	
	/**
	 * Generates the HTML-code for the body of the table.
	 * 
	 * @param $res
	 * 		The result
	 * @param $outputmode
	 * 		The output mode
	 * @param $prReplacements
	 * 		The print request replacements
	 * @return string
	 * 		HTML of the table's body
	 */
	private function createTableBody(SMWQueryResult $res, $outputmode, array $prReplacements) {
		$result = '';
		// print all result rows
		while ( $row = $res->getNext() ) {
			$result .= $this->createTableRow($row, $outputmode, $prReplacements);
		}
		return $result;
	}

	/**
	 * Creates the HTML for the footer of the table.
	 * @param $res
	 * 		The result
	 * @param $outputmode
	 * 		The output mode
	 */
	private function createTableFooter(SMWQueryResult $res, $outputmode) {
		$result = '';
		if ( $this->linkFurtherResults( $res ) ) {
			$link = $res->getQueryLink();
			if ( $this->getSearchLabel( $outputmode ) ) {
				$link->setCaption( $this->getSearchLabel( $outputmode ) );
			}
			$result .= "\t<tr class=\"smwfooter\"><td class=\"sortbottom\" colspan=\"" . $res->getColumnCount() . '"> ' . $link->getText( $outputmode, $this->mLinker ) . "</td></tr>\n";
		}
		$result .= "</table>\n"; // print footer
		return $result;
	}
	
	/**
	 * Creates the HTML for warnings of at least one "replace" statement is 
	 * invalid.
	 *  
	 * @return {string}
	 * 		The HTML for warnings.
	 */
	private function createWarnings() {
		if (empty($this->mReplaceWarnings)) {
			return '';
		}
		$result = '<ul>';
		foreach ($this->mReplaceWarnings as $warning) {
			$result .= "<li>$warning";
		}
		$result .= '</ul>';
		$msg = wfMsg('ftrp_warning');
		$result = "<div>$msg$result</div>";
		
		return $result;
	}
	
	/**
	 * Creates the HTML for a row of the result
	 * @param $row
	 * 		The result row.
	 * @param $outputmode
	 * 		The output mode
	 * @param $prReplacements
	 * 		The print request replacements
	 * @return {string} 
	 * 		The HTML for the row.
	 */
	private function createTableRow($row, $outputmode, array $prReplacements) {
		$result = "\t<tr>\n";
		
		// Transfer the content of a row in an array. We need that to get
		// random access to replacements for value labels
		$rowArray = $this->convertRowToArray($row);
		
		foreach ( $row as $field ) {
			// Process all fields in a row and consider the replacements
			// of print requests
			// Ignore columns that are replacements for other columns
			$fpr = $field->getPrintRequest();
			if (in_array($fpr->getHash(), $prReplacements)) {
				continue;
			}
			
			$result .= $this->createTableField($field, $rowArray, $outputmode, $prReplacements);
		}
		$result .= "\t</tr>\n";
		return $result;
	}
	
	/**
	 * Creates the HTML for a field of the table.
	 * @param $field
	 * 		The current field
	 * @param $rowArray
	 * 		The complete row as array for random access
	 * @param $outputmode
	 * 		The output mode
	 * @param $prReplacements
	 * 		The print request replacements
	 * @return {string}
	 * 		The HTML for a table cell
	 */
	private function createTableField($field, array $rowArray, $outputmode, 
										array $prReplacements) {
		$result = '';
		// Set alignment if specified
		$result .= "\t\t<td";
		$fpr = $field->getPrintRequest();
		$alignment = trim( $fpr->getParameter( 'align' ) );
		if ( ( $alignment == 'right' ) || ( $alignment == 'left' ) || ( $alignment == 'center' ) ) {
			$result .= ' style="text-align:' . $alignment . ';"';
		}
		$result .= ">";

		$replacementObject = $prReplacements[$fpr->getHash()]
								? $rowArray[$prReplacements[$fpr->getHash()]]
								: false;
		$objIdx = 0;
		// Iterate over all objects in the field
		$content = $rowArray[$fpr->getHash()];
		foreach ($content as $dv) {
			$sortKey = '';
			
			if ( $objIdx === 0 ) {
				$sortkey = $dv->getDataItem()->getSortKey();

				if ( is_numeric($sortkey) ) { // additional hidden sortkey for numeric entries
					$this->mColumnsWithSortKey[$field->getPrintRequest()->getHash()] = true;
					$sortKey .= '<span class="smwsortkey">' . $sortkey . '</span>';
				}
			}
			$isSubject = $field->getPrintRequest()->getMode() == SMWPrintRequest::PRINT_THIS;
			// use shorter "LongText" for wikipage
			$valueRep = ( ( $dv->getTypeID() == '_wpg' ) || ( $dv->getTypeID() == '__sin' ) ) 
				? $dv->getLongText( $outputmode, $this->getLinker( $isSubject ) )
				: $dv->getShortText( $outputmode, $this->getLinker( $isSubject ) );
				   
			$valueRep = $this->replaceLabel($replacementObject, $objIdx, $valueRep);
			$result .= $sortKey.$valueRep;
			++$objIdx;
		}
		$result .= "</td>\n";
	
		return $result;
	}
	/**
	 * Converts a row of the result into an array for random access.
	 * 
	 * @param  $row
	 * 		A row of the result
	 * @return array
	 * 		The row represented as an array
	 */
	private function convertRowToArray($row) {
		$rowArray = array();
		foreach ($row as $field) {
			// The field's print request is the index in the array
			$hash = $field->getPrintRequest()->getHash();
			$fieldValues = array();
			while ( ( $object = $field->getNextObject() ) !== false ) {
				$fieldValues[] = $object;
			}
			$rowArray[$hash] = $fieldValues;
		}
		return $rowArray;
	}
	
	/**
	 * Replaces a the label in $valueRep if certain conditions are met.
	 * 
	 * @param $replacementObject
	 * 		false or an array that contains the value that are used as replacement
	 * @param $objIdx
	 * 		current index in the $replacementObject
	 * @param {string} $valueRep
	 * 		The current representation of the value. This may be an <ilink> tag,
	 * 		a wikitext annotation or a plain value
	 * @return {string}
	 * 		The new representation of the value
	 */
	private function replaceLabel($replacementObject, $objIdx, $valueRep) {
		if ($replacementObject) {
			// Get the representation of the replacement value
			$newLabel = count($replacementObject) > $objIdx
							? $replacementObject[$objIdx]
							: false;
			if ($newLabel) {
				$newLabel = htmlspecialchars($newLabel->getShortWikiText(), ENT_QUOTES);
				
				// Replace the label in the representation of the
				// original value
				
				// Value may be an <ilink> tag or a wikitext annotation
				$ilinkRE = '/(<ilink label=")(.*?)(".*?>.*?<\/ilink>)/';
				$wikiAnnotationRE = '/(\[\[.*?\|)(.*?)(\]\])/';
				if (preg_match($ilinkRE, $valueRep, $matches)) {
					$valueRep = $matches[1].$newLabel.$matches[3];
				} else if (preg_match($wikiAnnotationRE, $valueRep, $matches)) {
					$valueRep = $matches[1].$newLabel.$matches[3];
				} else {
					// a simple value
					$valueRep = $newLabel;
				}
			}
		}
		return $valueRep;
	}
}
