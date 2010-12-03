<?php
/*  Copyright 2008, ontoprise GmbH
*  This file is part of the Data Import-Extension.
*
*   The Data Import-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Data Import-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @file
 * @ingroup DITIDataAccessLayer
 * Implementation of the Data Access Layer (DAL) that is part of the term import feature.
 * The DAL access a data source and creates terms in an XML format. These are 
 * returned to a module of the Transport layer.
 * This implementation reads a CSV file an returns its content in a form 
 * appropriate for the creation of articles.
 * 
 * @author Thomas Schweitzer
 */

global $smwgDIIP;
require_once($smwgDIIP . '/specials/TermImport/SMW_IDAL.php');

define('DAL_CVS_RET_ERR_START',
			'<?xml version="1.0"?>'."\n".
			'<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">'."\n".
    		'<value>false</value>'."\n".
    		'<message>');

define('DAL_CVS_RET_ERR_END',
			'</message>'."\n".
    		'</ReturnValue>'."\n");


class DALReadCSV implements IDAL {
	
//--- Fields ---

	// Name of the current CSV file
	private $filename;
	
	// The content of the current CSV file
	private $csvContent;

	
//--- Public methods ---

	/**
	 * Constructor of class DALReadCSV.
	 *
	 */
	function __construct() {
		$this->csvContent = array();
	}
	
	/**
	 * Returns a specification of the data source.
	 * See further details in SMW_IDAL.php
	 * 
	 * @return string:
	 * 		The returned XML structure specifies the data source i.e. a file. 
	 * 		The name of the file has to be specified by the user.
	 *		<?xml version="1.0"?>
	 *		<DataSource xmlns=http://www.ontoprise.de/smwplus#">
	 *	    	<filename display="Filename:" type="t"></filename>
	 *		</DataSource>
	 * 
	 */
	public function getSourceSpecification() {
		return 
			'<?xml version="1.0"?>'."\n".
			'<DataSource xmlns="http://www.ontoprise.de/smwplus#">'."\n".
			' 	<filename display="'.wfMsg('smw_ti_filename').'" type="text"></filename>'."\n".
			'</DataSource>'."\n";
	}
     
	/**
	 * Returns a list of import sets and their description.
	 * 
	 * @param string $dataSourceSpec: 
	 * 		The XML structure from getSourceSpecification(), filled with the data
	 * 		the user entered. 
	 * @return string:
     * 		Returns a list of import sets and their description (for the user) 
     * 		that the module can extract from the data source. An import set is 
     * 		just a name for a set of terms that module can extract e.g. different
     * 		domains of knowledge like Biological terms, Chemical terms etc. 
     * 		Each XML element <importSet> has the mandatory elements <name> and 
     * 		<desc>. Arbitrary, module dependent elements can be added. 
     * 		Example:
     * 		<?xml version="1.0"?>
	 *		<ImportSets xmlns="http://www.ontoprise.de/smwplus#">
	 *		    <importSet>
	 *		        <name>Biological terms</name>
	 *     			 <desc>Import all terms from the biology domain.</desc>
	 * 			</importSet>
	 *		    <importSet>
	 *		        <name>Biological terms</name>
	 *		        <desc>mport all terms from the chemistry domain.</desc>
	 *		    </importSet>
	 *		</ImportSets>
	 * 
	 * 		If the operation fails, an error message is returned.
	 * 		Example:
	 * 		<?xml version="1.0"?>
	 *		<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">
	 *		    <value>false</value>
	 *		    <message>The specified data source does not exist.</message>
	 *		</ReturnValue>
	 * 
	 */
	public function getImportSets($dataSourceSpec) {
		$filename = $this->getFilenameFromSpec($dataSourceSpec);
		$importSets = '';
		
		if (!$this->readContent($filename) 
		    || count($this->csvContent) == 0) {
		    return DAL_CVS_RET_ERR_START.
		           wfMsg('smw_ti_fileerror', $filename).
		           DAL_CVS_RET_ERR_END;
		}
		$firstLine = &$this->csvContent[0];
		$impSet = array();
		if (strtolower($firstLine[0]) == 'importset') {
			$len = count($this->csvContent);
			for ($i = 1; $i < $len; ++$i) {
				$impSet[$this->csvContent[$i][0]] = true;
			}
			foreach ($impSet as $is => $val) {
				$importSets .=
					'<importSet>'."\n".
					'	<name>'.$is.'</name>'."\n".
					'</importSet>'."\n";
			}
		}
		
		return 
			'<?xml version="1.0"?>'."\n".
			'<ImportSets xmlns="http://www.ontoprise.de/smwplus#">'."\n".
			$importSets.
			'</ImportSets>'."\n";
		
	}
     
	/**
	 * Returns a list of properties and their description.
	 *          
	 * @param string $dataSourceSpec: 
	 * 		The XML structure from getSourceSpecification(), filled with the data
	 * 		the user entered.
     * @param string $importSet: 
     * 		One of the import sets that can be retrieved with getImportSet() or 
     * 		empty. The complete XML element <importSet> as specified above is 
     * 		passed as it may contain values besides <name> and <desc>.
     * @return string: 
     * 		Returns a list of properties and their description (for the user) 
     * 		that the module can extract from the data source for each term in the
     * 		specified import set.
     * 		Example:
     * 		<?xml version="1.0"?>
     *		<Properties xmlns="http://www.ontoprise.de/smwplus#">
     *		    <property>
     *		        <name>articleName</name>
     *		        <desc>An article with this name will be created for the term of the vocabulary.</desc>
     *		    </property>
     *		    <property>
     *		        <name>content</name>
     *		        <desc>The description of the term.</desc>
     *		    </property>
     *		    <property>
     *		        <name>author</name>
     *		        <desc>Name of the person who describe the term.</desc>
     *		    </property>
     *		</Properties>
	 * 
	 * 		If the operation fails, an error message is returned.
	 * 		Example:
	 * 		<?xml version="1.0"?>
	 *		<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">
	 *		    <value>false</value>
	 *		    <message>The property 'articleName' is not defined in file "..."</message>
	 *		</ReturnValue>
	 * 
	 */
	public function getProperties($dataSourceSpec, $importSet) {
		$filename = $this->getFilenameFromSpec($dataSourceSpec);
		$properties = '';
		
		if (!$this->readContent($filename) 
		    || count($this->csvContent) == 0) {
		    return DAL_CVS_RET_ERR_START.
		           wfMsg('smw_ti_fileerror', $filename).
		           DAL_CVS_RET_ERR_END;
		}
    	$firstLine = &$this->csvContent[0];
		foreach ($firstLine as $prop) {
			if (strtolower($prop) != 'importset') {
				$properties .=
					'<property>'."\n".
					'	<name>'.trim($prop).'</name>'."\n".
					'</property>'."\n";
			}
		
		}
		
		return 
			'<?xml version="1.0"?>'."\n".
			'<Properties xmlns="http://www.ontoprise.de/smwplus#">'."\n".
			$properties.
			'</Properties>'."\n";
	}
	
	/**
	 * Returns a list of the names of all terms that match the input policy. 
	 *
	 * @param string $dataSourceSpec
	 * 		The XML structure from getSourceSpecification(), filled with the data 
	 * 		the user entered.
	 * @param string $importSet
	 * 		One of the <importSet>-elements from the XML structure from 
	 * 		getImportSets() or empty.
	 * @param string $inputPolicy
	 * 		The XML structure of the input policy as defined in importTerms().
	 * 
	 * @return string
	 * 		An XML structure that contains the names of all terms that match the
	 * 		input policy.
	 * 		Example:
	 *		<?xml version="1.0"?>
	 *		<terms xmlns="http://www.ontoprise.de/smwplus#">
	 *		    <articleName>Hydrogen</articleName>
	 *		    <articleName>Helium</articleName>
	 *		</terms>
	 * 
	 * 		If the operation fails, an error message is returned.
	 * 		Example:
	 * 		<?xml version="1.0"?>
	 *		<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">
	 *		    <value>false</value>
	 *		    <message>The specified data source does not exist.</message>
	 * 		</ReturnValue>
	 * 
	 */
	public function getTermList($dataSourceSpec, $importSet, $inputPolicy) {
		return $this->createTerms($dataSourceSpec, $importSet, $inputPolicy, true);
	}
	
	/**
	 * Generates the XML description of all terms in the data source that match 
	 * the input policy.
	 * @param string $dataSourceSpec
	 * 		The XML structure from getSourceSpecification, filled with the data 
	 * 		the user entered.
     * @param string $importSet
     * 		One of the <importSet>-elements from the XML structure from 
     * 		getImportSets() or empty.
     * @param string $inputPolicy
     * 		The XML structure of the input policy. It contains the specification
     * 		of the terms to import and their properties.
     * @param string $conflictPolicy
     * 		The XML structure of the conflict policy. It defines if existing articles
     * 		are overwritten or not.
     *      * 
     * @return string
	 *		An XML structure that contains all requested terms together with 
	 * 		their properties. The XML of requested terms that could not be 
	 * 		retrieved contains an error message.
	 * 		Example: 
	 *		<?xml version="1.0"?>
	 *		<terms xmlns="http://www.ontoprise.de/smwplus#">
	 *		    <term>
	 *		        <articleName>Helium</articleName>
	 *		        <content>Helium is a gas under normal conditions.</content>
	 *		        <!--
	 *		        Additional properties with type "string" may be specified.
	 *		        -->
	 *		    </term>
	 *		    <term error="The term 'Hydrogen' could not be found.">
	 *		        <articleName>Hydrogen</articleName>
	 *		    </term>
	 *		</terms>
	 *
	 */
	public function getTerms($dataSourceSpec, $importSet, $inputPolicy, $conflictPolicy) {
		return $this->createTerms($dataSourceSpec, $importSet, $inputPolicy, false);
		
	}
	
	//--- Private methods ---
	
	/**
	 * Extracts the filename from the data source specification.
	 *
	 * @param string $dataSourceSpec
	 * 		The XML structure that specifies the data source must contain the
	 * 		<filename> element with the name of a file.
	 * 
	 * @return string
	 * 		Name of a file or <null> if the value in not present. 
	 */
	private function getFilenameFromSpec($dataSourceSpec) {
		preg_match('/<filename.*?>(.*?)<\/filename>/i', $dataSourceSpec, $filename);
		
		return (count($filename) == 2) ? $filename[1] : null;
	}
	
	/**
	 * Reads the content of the csv file with the name $filename if it has not
	 * already been read. 
	 * The content is stored in $this->csvContent for further operations.
	 * Each line of the CSV file is an array of the separated values in the 
	 * array $this->csvContent. The first line/array contains the names of the
	 * properties of the terms.
	 *
	 * @param string $filename
	 * 		Name of the CSV file to read
	 * @return boolean
	 * 		<true>, if the file was read successfully (or is already cached)
	 * 		<false>, otherwise
	 */
	private function readContent($filename) {
		if ($this->filename == $filename) {
			return true;
		}
		
		if (!$filename) {
			return false;
		}
				
		$file = @ fopen($filename, 'r');
		if (!$file) {
			return false;
		}
		
		$this->csvContent = array();
		while (!feof($file)) {
			$line = fgets($file);
			if ($line) {
				//escape special characters in an XML document:
				$line = htmlspecialchars($line);
				@ $vals = &explode("\t", $line);
				$this->csvContent[] = $vals;
			}
		}
		fclose($file);
		
		$this->filename = $filename;
		
		return true;
		
	}
	
	/**
	 * Extracts the names of the import sets from the XML string <$importSets>.
	 *
	 * @param string $importSets 
	 * 		An XML string that contains tags with the name "importSet". 
	 * @return array<string>
	 * 		The names of all import sets in <$importSets> or an error message
	 * 		if the XML is not valid.
	 */
	private function parseImportSets(&$importSets) {
    	global $smwgDIIP;
		require_once($smwgDIIP . '/specials/TermImport/SMW_XMLParser.php');

		$parser = new XMLParser($importSets);
		$result = $parser->parse();
    	
		if ($result !== TRUE) {
			return $result;
    	}
    	
    	return $parser->getValuesOfElement(array('importSet','name'));
	}
	
	/**
	 * Parses the input policy in the XML string <$inputPolicy>. The policy 
	 * specifies concrete terms, regular expression for terms to import and
	 * the properties of the terms to import.
	 *
	 * @param string $inputPolicy 
	 * 		An XML string that contains the input policy.  
	 * @return array(array<string>)
	 * 		An array with three arrays (keys: "terms", "regex", "properties") 
	 * 		that contain the values from the XML string or an error message
	 * 		if the XML is not valid. 
	 */
	private function parseInputPolicy(&$inputPolicy) {
    	global $smwgDIIP;
		require_once($smwgDIIP . '/specials/TermImport/SMW_XMLParser.php');

		$parser = new XMLParser($inputPolicy);
		$result = $parser->parse();
    	if ($result !== TRUE) {
			return $result;
    	}
    	
    	$policy = array();
    	$policy['terms'] = $parser->getValuesOfElement(array('terms', 'term'));
    	$policy['regex'] = $parser->getValuesOfElement(array('terms', 'regex'));
    	$policy['properties'] = $parser->getValuesOfElement(array('properties', 'property'));
    	return $policy;
		
	}
	
	/**
	 * Checks if a term (that may belong to an import set) matches the restriction
	 * of import sets and the input policy.
	 *
	 * @param string $impSet
	 * 		The name of the import that the term belongs to. Can be <null>.
	 * @param string $term
	 * 		The name of the term.
	 * @param array<string> $importSets
	 * 		An array of allowed import sets.
	 * @param array(array<string>) $policy
	 * 		An array with the keys 'terms', 'regex' and 'properties'. The value for 
	 * 		each key is an array of strings with terms, regular expressions and 
	 * 		properties, respectively.
	 * @return boolean
	 * 		<true>, if the term matches the rules and should be imported
	 * 		<false> otherwise
	 */
	private function termMatchesRules($impSet, $term, 
			                          &$importSets, &$policy) {
		
		// Check import set
		if ($impSet != null && count($importSets) > 0) {
			if (!in_array($impSet, $importSets)) {
				// Term belongs to the wrong import set.
				return false;	                          	
			}
		}

		// Check term policy
		$terms = &$policy['terms'];
		if (in_array($term, $terms)) {
			return true;
		}
		
		// Check regex policy
		$regex = &$policy['regex'];
		foreach ($regex as $re) {
			$re = trim($re);
			if (preg_match('/'.$re.'/', $term)) {
				return true;
			}
		}
		
		return false;          	
			                          	
	}
	
	/**
	 * Generates the XML description of all terms in the data source that match 
	 * the input policy.
	 * @param string $dataSourceSpec
	 * 		The XML structure from getSourceSpecification, filled with the data 
	 * 		the user entered.
     * @param string $importSet
     * 		One of the <importSet>-elements from the XML structure from 
     * 		getImportSets() or empty.
     * @param string $inputPolicy
     * 		The XML structure of the input policy. It contains the specification
     * 		of the terms to import and their properties.
     * @param boolean $createTermList
     * 		If <true>, the XML structure for <getTermList> is created otherwise
     * 		the one for <getTerms>
     * 
     * @return string
	 *		An XML structure that contains all requested terms.
	 * 		If the operation fails, an error message is returned.
	 * 		Example:
	 * 		<?xml version="1.0"?>
	 *		<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">
	 *		    <value>false</value>
	 *		    <message>The specified data source does not exist.</message>
	 * 		</ReturnValue>
	 *  
	 */
	private function createTerms($dataSourceSpec, $importSet, $inputPolicy, 
	                             $createTermList) {
	                            	
		$filename = $this->getFilenameFromSpec($dataSourceSpec);
		$importSets = $this->parseImportSets($importSet);
		$policy = $this->parseInputPolicy($inputPolicy);
		$terms = '';
		
		if (!$this->readContent($filename) 
		    || ($len = count($this->csvContent)) == 0) {
		    return DAL_CVS_RET_ERR_START.
		           wfMsg('smw_ti_fileerror', $filename).
		           DAL_CVS_RET_ERR_END;
		}

		$indexMap = array();
		foreach ($this->csvContent[0] as $idx => $prop) {
			$p = trim($prop);
			$p = preg_replace("/ +/", "__SPACE__", $p);
			if (strtolower($p) == 'articlename') {
				$p = 'articleName';
			} else if (strtolower($p) == 'importset') {
				$p = 'ImportSet';
			}
			$indexMap[$p] = $idx;
		}
		$articleIdx = $indexMap['articleName'];
		if ($articleIdx === null) {
		    return DAL_CVS_RET_ERR_START.
		           wfMsg('smw_ti_no_article_names', $filename).
		           DAL_CVS_RET_ERR_END;
		}
		
		$impSetIdx = array_key_exists('ImportSet', $indexMap)
						? $indexMap['ImportSet']
						: null;
		
		for ($i = 1; $i < $len; ++$i) {
			$impSet = ($impSetIdx === null) ? null
			                                : $this->csvContent[$i][$impSetIdx];
			if (!array_key_exists($i, $this->csvContent) || !array_key_exists($articleIdx, $this->csvContent[$i])) {                                
				continue;
			}
			$term = $this->csvContent[$i][$articleIdx];
			if ($this->termMatchesRules($impSet, $term, 
			                            $importSets, $policy)) {
			    // The term matches the policies.
				// => add the term to the result
				if ($createTermList) {
					$terms .= "<articleName>".trim($term)."</articleName>\n";                          	
				} else {
					$terms .= "<term>\n";
					// add all requested properties
					$props = &$policy['properties'];
					foreach ($props as $prop) {
						$prop = "".preg_replace("/ +/", "__SPACE__", $prop);
						$idx = $indexMap[$prop];
						if ($idx !== null) {
							$value = htmlspecialchars(trim($this->csvContent[$i][$idx]));
							if (strlen($value) > 0) {
								// The property is only written, if it exists.
								$terms .= "<".$prop.">";
								$terms .= $value;
								$terms .= "</".$prop.">\n";
							}
						}
					}
					$terms .= "</term>\n";
				}
			}
		}
		
		return 
			'<?xml version="1.0"?>'."\n".
			'<terms xmlns="http://www.ontoprise.de/smwplus#">'."\n".
			$terms.
			'</terms>'."\n";
		
	                            	
		
	}
	
	public function executeCallBack($signature, $mappingPolicy, $conflictPolicy, $termImportName){
		return true;
	}
	
}