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
 * The DAL accesses a data source and creates terms in an XML format. These are
 * returned to a module of the Transport layer.
 * This implementation reads the TIXML format ann returns its content in a form
 * appropriate for the creation of articles.
 *
 * @author Ingo Steinbauer
 */

global $smwgDIIP;
require_once($smwgDIIP . '/specials/TermImport/SMW_IDAL.php');

define('DAL_TIXML_RET_ERR_START',
			'<?xml version="1.0"?>'."\n".
			'<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">'."\n".
    		'<value>false</value>'."\n".
    		'<message>');

define('DAL_TIXML_RET_ERR_END',
			'</message>'."\n".
    		'</ReturnValue>'."\n");


class DALReadTIXML implements IDAL {

	// Name of the current TIXML article
	private $articleName;

	// The content of the current TIXML article
	private $tixmlContent;

	/**
	 * Constructor of class DALReadTIXML.
	 *
	 */
	function __construct() {
		$this->tixmlContent = array();
	}

	/**
	 * Returns a specification of the data source.
	 * See further details in SMW_IDAL.php
	 *
	 * @return string:
	 * 		The returned XML structure specifies the data source
	 */
	public function getSourceSpecification() {
		return
			'<?xml version="1.0"?>'."\n".
			'<DataSource xmlns="http://www.ontoprise.de/smwplus#">'."\n".
			' 	<articleName display="'.wfMsg('smw_ti_articlename').'" autocomplete="true"></articleName>'."\n".
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
	 * 		If the operation fails, an error message is returned.
	 */
	public function getImportSets($dataSourceSpec) {
		$articleName = $this->getArticleNameFromSpec($dataSourceSpec);
		$importSets = '';

		if (!$this->readContent($articleName)
				|| count($this->tixmlContent) == 0) {
			return DAL_TIXML_RET_ERR_START.
			wfMsg('smw_ti_articleerror', $articleName).
			DAL_TIXML_RET_ERR_END;
		}

		$firstLine = &$this->tixmlContent[0];
		$impSet = array();
		if (strtolower($firstLine[0]) == 'importset') {
			$len = count($this->tixmlContent);
			for ($i = 1; $i < $len; ++$i) {
				$impSet[$this->tixmlContent[$i][0]] = true;
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
	*/
	public function getProperties($dataSourceSpec, $importSet) {
		$articleName = $this->getArticleNameFromSpec($dataSourceSpec);
		$properties = '';

		if (!$this->readContent($articleName)
		|| count($this->tixmlContent) == 0) {
			return DAL_TIXML_RET_ERR_START.
			wfMsg('smw_ti_articleerror', $articleName).
			DAL_TIXML_RET_ERR_END;
		}
		$firstLine = &$this->tixmlContent[0];
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
	 * 
	 * @return string
	 *		An XML structure that contains all requested terms together with
	 * 		their properties. The XML of requested terms that could not be
	 * 		retrieved contains an error message.
	 */
	public function getTerms($dataSourceSpec, $importSet, $inputPolicy, $conflictPolicy) {
		return $this->createTerms($dataSourceSpec, $importSet, $inputPolicy, false);
	}

	/**
	 * Extracts the articleName from the data source specification.
	 *
	 * @param string $dataSourceSpec
	 * 		The XML structure that specifies the data source must contain the
	 * 		<articleName> element with the name of an article in the wiki.
	 *
	 * @return string
	 * 		Name of the article or <null> if the value in not present.
	 */
	private function getArticleNameFromSpec($dataSourceSpec) {
		preg_match('/<articleName.*?>(.*?)<\/articleName>/i', $dataSourceSpec, $articleName);

		return (count($articleName) == 2) ? $articleName[1] : null;
	}

	/**
	 * Reads the content of the article with the name $articleName if it has not
	 * already been read.
	 * The content is stored in $this->tixmlContent for further operations.
	 *
	 * @param string $articleName
	 * 		Name of the article to read
	 * @return boolean
	 * 		<true>, if the article was read successfully (or is already cached)
	 * 		<false>, otherwise
	 */
	private function readContent($articleName) {
		if ($this->articleName == $articleName) {
			return true;
		} else if (!$articleName) {
			return false;
		}

		$this->tixmlContent = array();

		$article = new Article(Title::newFromText($articleName));
		$content = $article->getContent();
		$options = new ParserOptions();
		global $wgParser;
		$titleTemp = Title::newFromText($articleName);
		$wgParser->startExternalParse($titleTemp, $options, 1);
		$content = $wgParser->replaceVariables($content);
		
		$startPos = 0;
		$endPos = 0;
		$goon = true;
		$first = true;
		$i=1;
		while($goon){
			$startPos = @ strpos($content, "<tixml", $startPos);
			if($startPos !== false){
				$endPos = @ strpos($content, "</tixml>", $startPos);
				if($endPos !== false){
					$tContent = substr($content, $startPos, $endPos + 8 - $startPos);
					$tContent = new SimpleXMLElement($tContent);
					$startPos += 8;
				
					if($first){
						foreach($tContent->columns->title as $title){
							$this->tixmlContent[0][] = "".$title;
						}
						$first = false;
					}

					foreach($tContent->row as $row){
						foreach($row->item as $item){
							$this->tixmlContent[$i][] = urldecode("".$item);
						}
						$i++;
					}
				} else {
					$goon = false;
				}
			} else {
				$goon = false;
			}
		}
		
		$this->articleName = $articleName;
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

		$articleName = $this->getArticleNameFromSpec($dataSourceSpec);
		$importSets = $this->parseImportSets($importSet);
		$policy = $this->parseInputPolicy($inputPolicy);
		$terms = '';

		if (!$this->readContent($articleName)
			|| ($len = count($this->tixmlContent)) == 0) {
			return DAL_TIXML_RET_ERR_START.
			wfMsg('smw_ti_articleerror', $articleName).
			DAL_TIXML_RET_ERR_END;
		}

		$indexMap = array();
		foreach ($this->tixmlContent[0] as $idx => $prop) {
			$p = trim($prop);
			$p = preg_replace("/ +/", "__SPACE__", $p);
			if (strtolower($p) == 'Articlename') {
				$p = 'articleName';
			} else if (strtolower($p) == 'importset') {
				$p = 'ImportSet';
			}
			$indexMap[$p] = $idx;
		}
		$articleIdx = $indexMap['articleName'];
		if ($articleIdx === null) {
			return DAL_TIXML_RET_ERR_START.
			wfMsg('smw_ti_no_article_names', $articleName).
			DAL_TIXML_RET_ERR_END;
		}

		$impSetIdx = array_key_exists('ImportSet', $indexMap)
			? $indexMap['ImportSet']
			: null;

		for ($i = 1; $i < $len; ++$i) {
			$impSet = ($impSetIdx === null) ? null
				: $this->tixmlContent[$i][$impSetIdx];
			if (!array_key_exists($i, $this->tixmlContent) || !array_key_exists($articleIdx, $this->tixmlContent[$i])) {
				continue;
			}
			$term = $this->tixmlContent[$i][$articleIdx];
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
						$prop = "".trim(preg_replace("/ +/", "__SPACE__", $prop));
						
						$idx = $indexMap[$prop];
						
						if ($idx !== null) {
							$value = htmlspecialchars(trim($this->tixmlContent[$i][$idx]));
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