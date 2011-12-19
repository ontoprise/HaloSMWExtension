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

/*
 * @author Ingo Steinbauer
 */

class DALReadSPARQLXML implements IDAL {  
	
	private $queryResult;
	private $queryResultColumns;
	private $errorMSG;

	
	public function getSourceSpecification() {
		return 
			'<?xml version="1.0"?>'."\n".
			'<DataSource xmlns="http://www.ontoprise.de/smwplus#">'."\n".
			' 	<endpoint display="URL:" type="text" class="long"></endpoint>'."\n".
			' 	<query display="Query:" type="textarea" rows="10" class="long"></query>'."\n".
			'</DataSource>'."\n";
	}
	
	
	public function getImportSets($dataSourceSpec) {
		$endPointName = $this->getEndpointURIFromSourceSpec($dataSourceSpec);
		$query = $this->getQueryFromSourceSpec($dataSourceSpec);
		$importSets = array();
		
		if (!$this->readContent($endPointName, $query)
				|| count($this->queryResultColumns) == 0) {
			return $this->errorMSG;;
		}
		
		$importSetLabel = '';
		foreach($this->queryResultColumns as $columnName => $dontCare){
			if(strtolower($columnName) == 'importset'){
				$importSetLabel = $columnName;
			}
		}

		if(strlen($importSetLabel) > 0){
			foreach($this->queryResult as $row){
				$iS = $row[$importSetLabel];
				if(array_key_exists($iS, $impSets)){
					continue;
				}
				$importSets[$iS] = true;
			}
		}
		
		return array_flip($importSets);
	}
	 
	
	public function getProperties($dataSourceSpec, $importSet) {
		$endPointName = $this->getEndpointURIFromSourceSpec($dataSourceSpec);
		$query = $this->getQueryFromSourceSpec($dataSourceSpec);
		
		if (!$this->readContent($endPointName, $query)
				|| count($this->queryResultColumns) == 0) {
			return $this->errorMSG;
		}
		
		$properties = array();
		foreach ($this->queryResultColumns as $column => $dontCare) {
			$column = (strtolower($column) == 'articlename') ? 'articleName' : $column;
			$column = (strtolower($column) == 'importset') ? 'importSet' : $column;
			
			$properties[trim($column)] = true;
		}

		return array_keys($properties);
	}
	
	public function getTermList($dataSourceSpec, $importSet, $inputPolicy) {
		$result = $this->createTerms($dataSourceSpec, $importSet, $inputPolicy, true);
		return $result;
	}
	
	public function getTerms($dataSourceSpec, $importSet, $inputPolicy, $conflictPolicy) {
		$result = $this->createTerms($dataSourceSpec, $importSet, $inputPolicy, false);
		return $result;
	}
	
	private function createTerms($dataSourceSpec, $givenImportSet, $inputPolicy, $createTermList) {
		$endPointURI = $this->getEndpointURIFromSourceSpec($dataSourceSpec);
		$query = $this->getQueryFromSourceSpec($dataSourceSpec);
		
		if(!$this->readContent($endPointURI, $query)){
			return $this->errorMSG;
		}
		
		$inputPolicy = DIDALHelper::parseInputPolicy($inputPolicy);
		
		//Get articleName and importSet
		$articleNameLabel = false;
		$importSetLabel = false;
		foreach($this->queryResultColumns as $columnName => $dontCare){
			if(strtolower($columnName) == 'articlename'){
				$articleNameLabel = $columnName;
				unset($this->queryResultColumns[$articleNameLabel]);
			} else if(strtolower($columnName) == 'importset'){
				$importSetLabel = $columnName;
				unset($this->queryResultColumns[$importSetLabel]);
			}
		}
		
		if(!$articleNameLabel){
			return wfMsg('smw_ti_sparql_wrong_variable_name');;
		}
		
		$terms = new DITermCollection();
		$processedArticleNames = array();
		foreach($this->queryResult as $row){
			
			//removes duplicate article names
			if(array_key_exists($row[$articleNameLabel], $processedArticleNames)){
				continue;
			}
			$processedArticleNames[$row[$articleNameLabel]] = true;
			$importSet = (!$importSetLabel) ? null : $row[$importSetLabel]; 
			if (!DIDALHelper::termMatchesRules($importSet, $row[$articleNameLabel], 
					$givenImportSet, $inputPolicy)) {
				continue;                            	
			}
			
			$term = new DITerm();
			$term->setArticleName($row[$articleNameLabel]); 
			if(!$createTermList){
				
				foreach($this->queryResultColumns as $columnName => $dontCare){
					if(array_key_exists($columnName, $row)){
						$term->addAttribute($columnName, $row[$columnName]);			
					}
				}
			}
			$terms->addTerm($term);
		}
		
		return $terms;
	}
	
	private function readContent($endPointURI, $query){
		if(!is_null($this->errorMSG)){
			//query already processed
			return false;
		}
		
		if(is_array($this->queryResult)){
			//query already processed
			return true;
		}
		
		$config = array('remote_store_endpoint' => $endPointURI,);
		$store = ARC2::getRemoteStore($config);
		
		$result = $store->query($query);
		
		if(!is_array($result['result'])){
			$this->errorMSG = 'No results could be retrieved from the SPARQL endpoint.';
			return false;
		}
		
		$this->queryResultColumns = array();
		foreach($result['result']['variables'] as $column){
			$this->queryResultColumns[trim($column)] = $column; 
		}
		
		$this->queryResult = array();
		foreach($result['result']['rows'] as $key => $row){
			foreach($this->queryResultColumns as $column => $oColumn){
				@ $this->queryResult[$key][$column] = trim($row[$oColumn]);
			}
		}
		
		//echo('<pre>'.print_r($this->queryResultColumns, true).'</pre>');
		//echo('<pre>'.print_r($this->queryResult, true).'</pre>');
		return true;		
	}
	
	private function getEndpointURIFromSourceSpec($dataSourceSpec){
		$dataSourceSpec = new SimpleXMLElement($dataSourceSpec);
		$res = $dataSourceSpec->xpath('//endpoint');
		if(count($res) > 0){
			return ''.trim($res[0]);
		} else {
			return null;
		}
	}
	
	private function getQueryFromSourceSpec($dataSourceSpec){
		$dataSourceSpec = new SimpleXMLElement($dataSourceSpec);
		$res = $dataSourceSpec->xpath('//query');
		if(count($res) > 0){
			return ''.trim($res[0]);
		} else {
			return null;
		}
	}
	
	public function executeCallBack($signature, $templateName, $extraCategories, $delimiter, $overwriteExistingArticles, $termImportName){
		return array(true, array());
	}
	
}




