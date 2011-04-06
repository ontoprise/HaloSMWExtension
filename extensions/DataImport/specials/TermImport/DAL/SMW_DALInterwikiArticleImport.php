<?php
/*  Copyright 2009, ontoprise GmbH
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

/* @file
 * @ingroup DITIDataAccessLayer
 * Implementation of the Data Access Layer (DAL) that is part of the term import feature.
 * The DAL access a data source and creates terms in an XML format. These are 
 * returned to a module of the Transport layer.
 * This implementation imports articles from another Mediwiki into the local wiki.
 * 
 * @author Thomas Schweitzer
 */

global $smwgDIIP;
require_once($smwgDIIP . '/specials/TermImport/SMW_IDAL.php');

define('DAL_IAI_RET_ERR_START',
			'<?xml version="1.0"?>'."\n".
			'<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">'."\n".
    		'<value>false</value>'."\n".
    		'<message>');

define('DAL_IAI_RET_ERR_END',
			'</message>'."\n".
    		'</ReturnValue>'."\n");


class DALInterwikiArticleImport implements IDAL {
	
//--- Fields ---

	private $mWikiAPIs = array(
		'Wikipedia' => 'http://en.wikipedia.org/w/',
		'Wikipedia German' => 'http://de.wikipedia.org/w/',
	
	);
	
//--- Public methods ---

	/**
	 * Constructor of class DALInterwikiArticleImport.
	 *
	 */
	function __construct() {
	}
	
	/**
	 * Returns a specification of the data source.
	 * See further details in SMW_IDAL.php
	 * 
	 * @return string:
	 * 		The returned XML structure specifies the data source i.e. an external 
	 * 		Mediawiki. 
	 * 		The name of the file has to be specified by the user.
	 *		<?xml version="1.0"?>
	 *		<DataSource xmlns=http://www.ontoprise.de/smwplus#">
	 *	    	<filename display="Filename:" type="text"></filename>
	 *		</DataSource>
	 * 
	 */
	public function getSourceSpecification() {
		$sources = array_keys($this->mWikiAPIs);
		$defaultsSource = $sources[0];
		return 
			'<?xml version="1.0"?>'."\n".
			'<DataSource xmlns="http://www.ontoprise.de/smwplus#">'."\n".
			' 	<wiki display="Wiki" type="text">'.$defaultsSource.'</wiki>'."\n".
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
		$importSets = 
			'<importSet>'."\n".
			'	<name>Articles</name>'."\n".
			'	<desc>Import the specified articles.</desc>'."\n".
			'</importSet>'."\n".
			'<importSet>'."\n".
			'	<name>Templates</name>'."\n".
			'	<desc>Import the templates needed by the specified articles.</desc>'."\n".
			'</importSet>'."\n".
				'<importSet>'."\n".
			'	<name>Images</name>'."\n".
			'	<desc>Import the images needed by the specified articles.</desc>'."\n".
			'</importSet>'."\n";
		
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
		
		return 
			'<?xml version="1.0"?>'."\n".
			'<Properties xmlns="http://www.ontoprise.de/smwplus#">'."\n".
				'<property>'."\n".
				'	<name>articleName</name>'."\n".
				'</property>'."\n".
				'<property>'."\n".
				'	<name>linkToReport</name>'."\n".
				'</property>'."\n".
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
		return $this->createTerms($dataSourceSpec, $importSet, $inputPolicy, null, true);
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
		return $this->createTerms($dataSourceSpec, $importSet, $inputPolicy, $conflictPolicy, false);
		
	}
	
	//--- Private methods ---
	
	/**
	 * Extracts the name of the wiki from the data source specification.
	 *
	 * @param string $dataSourceSpec
	 * 		The XML structure that specifies the data source must contain the
	 * 		<wiki> element with the name of a wiki.
	 * 
	 * @return string
	 * 		Name of a wiki or <null> if the value in not present. 
	 */
	private function getSourceWikiFromSpec($dataSourceSpec) {
		preg_match('/<wiki.*?>(.*?)<\/wiki>/i', $dataSourceSpec, $wiki);
		
		return (count($wiki) == 2) ? $wiki[1] : null;
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
     * @param string $conflictPolicy
     * 		The XML structure of the conflict policy. It defines if existing articles
     * 		are overwritten or not.
     *  
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
	                             $conflictPolicy, $createTermList) {
	                            	
		$wiki = $this->getSourceWikiFromSpec($dataSourceSpec);
		if (!array_key_exists($wiki, $this->mWikiAPIs)) {
			echo "Unknown wiki: $wiki\n";
			return '<?xml version="1.0"?>
					<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">
						<value>false</value>
						<message>The specified data source "'.$wiki.'" does not exist.</message>
					</ReturnValue>';
		}
		
		$iai = new IAIArticleImporter($this->mWikiAPIs[$wiki]);

		$importSets = $this->parseImportSets($importSet);
		$policy = $this->parseInputPolicy($inputPolicy);
		
		$terms = '';
		
		// Find the terms in the source wiki closest to the requested terms
		$ipTerms = $policy['terms'];
		foreach ($ipTerms as $k => $term) {
			if (empty($term)) {
				continue;
			}
			// check if the term is available in the source wiki
			$term = trim($term);
			$ns = 0;
			$t = Title::newFromText($term);
			if ($t) {
				$ns = $t->getNamespace();
				$term = $t->getText();
			}
			$matches = $iai->getArticles($term, $ns, 1);
			$ipTerms[$k] = $matches[0];
		}
		if ($createTermList) {
			foreach ($ipTerms as $t) {
				$terms .= "<articleName>".$t."</articleName>\n";
			}
		} else {
			
			// Conflict policy
			$overwrite = true;
			preg_match('/<overwriteExistingTerms.*?>(.*?)<\/overwriteExistingTerms>/i', 
						$conflictPolicy, $overwriteMatch);
			if (count($overwriteMatch) == 2) {
				$overwrite = $overwriteMatch[1] == 'true';
				echo "\nSkip existing articles: ". ($overwrite ? "false\n" : "true\n");
			}
			echo "Importing from wiki: $wiki\n";
			
			echo "Articles to import: \n";
			foreach ($ipTerms as $t) {
				echo "$t\n";
			}
			
			echo "\n";
			
			$importArticles = true;
			$importTemplates = true;
			$importImages = true;
			
			echo "Import sets: ";
			
			if (!empty($importSets)) {
				if (!in_array('Articles', $importSets)) {
					$importArticles = false;			
				}
				if (!in_array('Templates', $importSets)) {
					$importTemplates = false;			
				}
				if (!in_array('Images', $importSets)) {
					$importImages = false;			
				}
			}

			if ($importArticles) echo "Articles, ";
			if ($importTemplates) echo "Templates, ";
			if ($importImages) echo "Images ";
			echo "\n";
			
			try {
				$iai->startReport();
				if ($importArticles) {
					$iai->importArticles($ipTerms, $importTemplates, $importImages, !$overwrite);
				} else {
					if ($importTemplates) {
						$iai->importTemplates($ipTerms);
					}
					if ($importImages) {
						$iai->importImagesForArticle($ipTerms);
					}
				}
				if (!empty($images)) {
					$iai->importImages($images, true);
				}
			} catch (Exception $e) {
				echo "Caught an exception: \n".$e->getMessage();
				return '<?xml version="1.0"?>
						<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">
							<value>false</value>
							<message>Caught an exception: '.$e->getMessage().'</message>
						</ReturnValue>';
			}
			$report = $iai->createReport(true);
			echo "Saved report for articles in: $report\n";
							
			$terms .= "<term>\n";
			$terms .= "<articleName>{$report}_TIF</articleName>\n";
			$terms .= "<linkToReport>{$report}</linkToReport>\n";
			$terms .= "</term>\n";
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