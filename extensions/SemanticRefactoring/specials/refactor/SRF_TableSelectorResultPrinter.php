<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */
/**
 * Print query results in tables for selecting instances
 * to refactor. Based on SMWTableResultPrinter
 *
 * @author Kai Kühn
 *
 * SMWTableResultPrinter:
 *
 * @author Markus Krötzsch
 * @author Jeroen De Dauw  < jeroendedauw@gmail.com >
 *
 * @file
 * @ingroup SREFSpecials
 */
class SRFTableSelectorResultPrinter extends SMWTableResultPrinter {
	
	protected function handleParameters( array $params, $outputmode ) {
		SMWResultPrinter::handleParameters( $params, $outputmode );
	}
	protected function getResultText( SMWQueryResult $res, $outputmode ) {
		global $smwgIQRunningNumber;
		SMWOutputs::requireHeadItem( SMW_HEADER_SORTTABLE );


		$tableRows = array();

		$rowNum = 1;
		while ( $subject = $res->getNext() ) {
			$tableRows[] = $this->getRowForSubject( $subject, $outputmode, array(), $rowNum++ );
		}

		$firstSlice = true;
		$result = "";

		$pageNum = intval(count($tableRows) / SREF_QUERY_PAGE_LIMIT);
		$pageNum += count($tableRows) % SREF_QUERY_PAGE_LIMIT === 0 ? 0 : 1;

		for($i = 0; $i < $pageNum; $i++) {

			// print header
			$visible = $firstSlice ? 'style="display: block" pageNum="'.$pageNum.'"' : 'style="display: none"';
			$firstSlice = false;
			$result .= '<div id="sref_slice'.$i.'" '.$visible.'>';
			$result .= '<table class="smwtable"' .
			( $this->mFormat == 'broadtable' ? ' width="100%"' : '' ) .
                  " id=\"querytable$smwgIQRunningNumber\">\n";

			if ( $this->mShowHeaders != SMW_HEADERS_HIDE ) { // building headers
				$headers = array();

				foreach ( $res->getPrintRequests() as $pr ) {
					$attribs = array();

					//					if ( array_key_exists( $pr->getHash(), $this->columnsWithSortKey ) ) {
					//						$attribs['class'] = 'numericsort';
					//					}

					$headers[] = Html::rawElement(
                    'th',
					$attribs,
					$pr->getText( $outputmode, ( $this->mShowHeaders == SMW_HEADERS_PLAIN ? null:$this->mLinker ) )
					);
				}
				if ($firstSlice) {
					array_unshift( $tableRows, '<tr>' . implode( "\n", $headers ) . '</tr>' );
				}
			}

			$result .= implode( "\n", array_slice($tableRows, $i*SREF_QUERY_PAGE_LIMIT, SREF_QUERY_PAGE_LIMIT) );

			$result .= "</table>\n"; // print footer
			$result .= "</div>\n";
		}

		$this->isHTML = ( $outputmode == SMW_OUTPUT_HTML ); // yes, our code can be viewed as HTML if requested, no more parsing needed

		return $result;
	}
	/**
	 * Gets the contents for a table cell for all values of a property of a subject.
	 *
	 * @since 1.6.1
	 *
	 * @param SMWResultArray $resultArray
	 * @param $outputmode
	 *
	 * @return string
	 */
	protected function getCellContent( array /* of SMWDataValue */ $dataValues, $outputmode, $isSubject ) {
		$values = array();
		$isFirst = true;
		foreach ( $dataValues as $dv ) {
			$checkbox = "";
			if ( $isFirst ) {
				$isFirst = false;
				$sortkey = $dv->getDataItem()->getSortKey();
				$enc_sortkey = $isSubject ? Sanitizer::encodeAttribute($dv->getDataItem()->getTitle()->getPrefixedDBkey()) : "";
				$checkbox = '<input class="sref_instance_selector" type="checkbox" checked="true" prefixedTitle="'.$enc_sortkey.'"></input>';
				
			}
			$value = $checkbox . $dv->getShortText( $outputmode, $this->getLinker( $isSubject ) );
			$values[] = $value;
		}

		return implode( '<br />', $values );
	}


}
