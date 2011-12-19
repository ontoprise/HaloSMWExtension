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

/* @file
 * @ingroup DITIDataAccessLayer
 * Implementation of the Data Access Layer (DAL) that is part of the term import feature.
 * The DAL access a data source and creates terms in an XML format. These are 
 * returned to a module of the Transport layer.
 * This implementation imports articles from another Mediwiki into the local wiki.
 * 
 * @author Thomas Schweitzer
 */

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
	 */ 
	 public function getImportSets($dataSourceSpec) {
	 	return array('Articles', 'Template', 'Images');
	}
     
	/**
	 * Returns a list of properties and their description.
	*/          
	public function getProperties($dataSourceSpec, $importSet) {
		return array('articleName', 'linkToReport');
	}
	
	/**
	 * Returns a list of the names of all terms that match the input policy. 
	*/
	public function getTermList($dataSourceSpec, $importSet, $inputPolicy) {
		return $this->createTerms($dataSourceSpec, $importSet, $inputPolicy, null, true);
	}
	
	/**
	 * Generates the XML description of all terms in the data source that match 
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
	 * Generates the Term Collection.
	 *
	 * @param string $dataSourceSpec
	 * 		The XML structure from getSourceSpecification, filled with the data 
	 * 		the user entered.
     * @param string $importSet
     * 		One of the <importSet>-elements from  
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
     * @return Term Collection
	 */
	private function createTerms($dataSourceSpec, $importSet, $inputPolicy, 
			$conflictPolicy, $createTermList) {
	                            	
		$wiki = $this->getSourceWikiFromSpec($dataSourceSpec);
		if (!array_key_exists($wiki, $this->mWikiAPIs)) {
			echo "Unknown wiki: $wiki\n";
			return 'The specified data source "'.$wiki.'" does not exist.';
		}
		
		$iai = new IAIArticleImporter($this->mWikiAPIs[$wiki]);

		$policy = DIDALHelper::parseInputPolicy($inputPolicy);
		
		$terms = new DITermCollection();
		
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
		
		$diTerm = new DITerm();
		$diTerm->setArticleName($t);
		
		if (!$createTermList) {
			foreach ($ipTerms as $t) {
				$diTerm = new DITerm();
				$diTerm->setArticleName($t);
				$terms->addTerm($diTerm);
			}
		} else {
			// Conflict policy
			$overwrite = true;
			preg_match('/<Name.*?>(.*?)<\/Name>/i', 
						$conflictPolicy, $overwriteMatch);
			if (count($overwriteMatch) == 2) {
				$overwrite = $overwriteMatch[1] != 'ignore';
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
				return 'Caught an exception: '.$e->getMessage();
			}
			$report = $iai->createReport(true);
			echo "Saved report for articles in: $report\n";
							
			$diTerm = new DITerm();
			$diTerm->setArticleName($report.'_TIF');
			$diTerm->addAttribute('linkToReport', $report);
			$terms->addTerm($diTerm);
		}
		
		return $terms;
	}
	
	public function executeCallBack($signature, $templateName, $extraCategories, $delimiter, $overwriteExistingArticles, $termImportName){
		return array(true, array());
	}
	
}