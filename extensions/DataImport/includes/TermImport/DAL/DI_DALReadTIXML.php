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
 * @file
 * @ingroup DITIDataAccessLayer
 * 
 * This DAM reads web service results in the TIXML format
 *
 * @author Ingo Steinbauer
 */
class DALReadTIXML implements IDAL {

	private $articleName;
	private $tixmlContent;

	function __construct() {
		$this->tixmlContent = array();
	}

	public function getSourceSpecification() {
		//todo: language
		return
			'<?xml version="1.0"?>'."\n".
			'<DataSource xmlns="http://www.ontoprise.de/smwplus#">'."\n".
			' 	<articleName display="'.wfMsg('smw_ti_articlename').'" autocomplete="true"></articleName>'."\n".
			'</DataSource>'."\n";
	}
	 
	public function getImportSets($dataSourceSpec) {
		$articleName = $this->getArticleNameFromSpec($dataSourceSpec);
		$importSets = array();
		
		if (!$this->readContent($articleName)
				|| count($this->tixmlContent) == 0) {
			return wfMsg('smw_ti_articleerror', $articleName);
		}

		$firstLine = &$this->tixmlContent[0];
		if (strtolower($firstLine[0]) == 'importset') {
			$len = count($this->tixmlContent);
			for ($i = 1; $i < $len; ++$i) {
				$importSets[$this->tixmlContent[$i][0]] = true;
			}
		}

		return array_keys($importSets);
	}
	 
	public function getProperties($dataSourceSpec, $importSet) {
		$articleName = $this->getArticleNameFromSpec($dataSourceSpec);
		$properties = array();

		if (!$this->readContent($articleName)
				|| count($this->tixmlContent) == 0) {
			return wfMsg('smw_ti_articleerror', $articleName);
		}
		
		$firstLine = &$this->tixmlContent[0];
		foreach ($firstLine as $prop) {
			if (strtolower($prop) != 'importset') {
				$properties[] = trim($prop);
			}
		}

		return $properties;
	}

	public function getTermList($dataSourceSpec, $importSet, $inputPolicy) {
		return $this->createTerms($dataSourceSpec, $importSet, $inputPolicy, true);
	}

	public function getTerms($dataSourceSpec, $importSet, $inputPolicy, $conflictPolicy) {
		return $this->createTerms($dataSourceSpec, $importSet, $inputPolicy, false);
	}

	/**
	 * Extracts the articleName from the data source specification.
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
	 * Generates the Term Collection which will be imported
	
	 * @param string $dataSourceSpec
	 * 		The XML structure from getSourceSpecification, filled with the data
	 * 		the user entered.
	 * @param string $importSet
	 * 		One of the <importSet>-elements from 
	 * 		getImportSets() or empty.
	 * @param string $inputPolicy
	 * 		The XML structure of the input policy. It contains the specification
	 * 		of the terms to import and their properties.
	 * @param boolean $createTermList
	 * 		only term titles must be extracted if the term list should be created
	 *
	 * @return DITermCollection
	 *
	 */
	private function createTerms($dataSourceSpec, $givenImportSet, $inputPolicy,
			$createTermList) {

		$articleName = $this->getArticleNameFromSpec($dataSourceSpec);
		$inputPolicy = DIDALHelper::parseInputPolicy($inputPolicy);
		
		$terms = new DITermCollection();

		if (!$this->readContent($articleName)
				|| ($len = count($this->tixmlContent)) == 0) {
			return wfMsg('smw_ti_articleerror', $articleName);
		}

		$indexMap = array();
		foreach ($this->tixmlContent[0] as $idx => $prop) {
			$p = trim($prop);
			if (strtolower($p) == 'articlename') {
				$p = 'articleName';
			} else if (strtolower($p) == 'importset') {
				$p = 'ImportSet';
			}
			$indexMap[$p] = $idx;
		}
		$articleIdx = $indexMap['articleName'];
		if ($articleIdx === null) {
			return wfMsg('smw_ti_no_article_names', $articleName);
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
			$articleName = $this->tixmlContent[$i][$articleIdx];
			if (DIDALHelper::termMatchesRules($impSet, $articleName,
					$givenImportSet, $inputPolicy)) {
				
				$term = new DITerm();
				$term->setArticleName($articleName);
				
				if (!$createTermList) {
					// add all requested properties
					$props = $inputPolicy['properties'];
					
					foreach ($props as $prop) {
						$prop = "".trim($prop);
						$idx = $indexMap[$prop];
						
						if ($idx !== null) {
							$value = trim($this->tixmlContent[$i][$idx]);
							if (strlen($value) > 0) {
								$term->addProperty($prop, $value);
							}
						}
					}
				}
				$terms->addTerm($term);
			} 
		}
		
		//echo(print_r($terms, true));
		
		return $terms;
	}
	
	public function executeCallBack($callback, $templateName, $extraCategories, $delimiter, $conflictPolicy, $termImportName){
		return array(true, array());
	}
}