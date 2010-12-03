<?php


global $smwgDIIP;
require_once($smwgDIIP . '/specials/TermImport/SMW_IDAL.php');

define('DAL_SXML_RET_ERR_START',
			'<?xml version="1.0"?>'."\n".
			'<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">'."\n".
    		'<value>false</value>'."\n".
    		'<message>');

define('DAL_SXML_RET_ERR_END',
			'</message>'."\n".
    		'</ReturnValue>'."\n");


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
		
		$importSets = '';
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

		$result = '';
		if(strlen($importSetLabel) > 0){
			$impSets = array();
			foreach($this->queryResult as $row){
				$iS = $row[$importSetLabel];
				if(array_key_exists($iS, $impSets)){
					continue;
				}
				$impSets[$iS] = true;
				
				$result .= '<importSet>'."\n".'	<name>'.$iS.'</name>'."\n".'</importSet>'."\n";
			}
		}
		
		return
			'<?xml version="1.0"?>'."\n".
			'<ImportSets xmlns="http://www.ontoprise.de/smwplus#">'."\n".
			$result.
			'</ImportSets>'."\n";
	}
	 
	
	public function getProperties($dataSourceSpec, $importSet) {
		$endPointName = $this->getEndpointURIFromSourceSpec($dataSourceSpec);
		$query = $this->getQueryFromSourceSpec($dataSourceSpec);
		
		$importSets = '';
		if (!$this->readContent($endPointName, $query)
				|| count($this->queryResultColumns) == 0) {
			return $this->errorMSG;
		}
		
		$properties = '';
		foreach ($this->queryResultColumns as $column => $dontCare) {
			$column = (strtolower($column) == 'articlename') ? 'articleName' : $column;
			$column = (strtolower($column) == 'importset') ? 'importSet' : $column;
			$properties .=
				'<property>'."\n".
				'	<name>'.trim($column).'</name>'."\n".
				'</property>'."\n";
		}

		return
			'<?xml version="1.0"?>'."\n".
			'<Properties xmlns="http://www.ontoprise.de/smwplus#">'."\n".
			$properties.
			'</Properties>'."\n";
	}
	
	public function getTermList($dataSourceSpec, $importSet, $inputPolicy) {
		$result = $this->createTerms($dataSourceSpec, $importSet, $inputPolicy, true);
		//file_put_contents("d://terms-list.rtf", print_r($result, true));
		return $result;
	}
	
	public function getTerms($dataSourceSpec, $importSet, $inputPolicy, $conflictPolicy) {
		$result = $this->createTerms($dataSourceSpec, $importSet, $inputPolicy, false);
		//file_put_contents("d://terms-list.rtf", print_r($result, true));
		return $result;
	}
	
	private function createTerms($dataSourceSpec, $importSet, $inputPolicy, $createTermList) {
		$endPointURI = $this->getEndpointURIFromSourceSpec($dataSourceSpec);
		$query = $this->getQueryFromSourceSpec($dataSourceSpec);
		
		if(!$this->readContent($endPointURI, $query)){
			return $this->errorMSG;
		}
		
		$importSets = $this->parseImportSets($importSet);
		$inputPolicy = $this->parseInputPolicy($inputPolicy);
		
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
			return DAL_SXML_RET_ERR_START.'One of the variables in the query must be called "articlename".'.DAL_SXML_RET_ERR_END;
		}
		
		$terms = '';
		$processedArticleNames = array();
		foreach($this->queryResult as $row){
			
			//removes duplicate article names
			if(array_key_exists($row[$articleNameLabel], $processedArticleNames)){
				continue;
			}
			$processedArticleNames[$row[$articleNameLabel]] = true;
			$importSet = (!$importSetLabel) ? null : $row[$importSetLabel]; 
			if (!$this->termMatchesRules($importSet, $row[$articleNameLabel], 
			                            $importSets, $inputPolicy)) {
				continue;                            	
			}
			
			$articleNameXML = "<articleName>".$row[$articleNameLabel]."</articleName>\n"; 
			if($createTermList){
				$terms .= $articleNameXML;
			} else {
				$terms .= "<term>\n";
				$terms .= $articleNameXML; 
				foreach($this->queryResultColumns as $columnName => $dontCare){
					$terms .= '<'.$columnName.'>';
					@ $terms .= $row[$columnName];
					$terms .= '</'.$columnName.'>';					
				}
				$terms .= "</term>\n";
			}
		}
		
		return
			'<?xml version="1.0"?>'."\n".
			'<terms xmlns="http://www.ontoprise.de/smwplus#">'."\n".
			$terms.
			'</terms>'."\n";
		
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
			$this->errorMSG = DAL_SXML_RET_ERR_START.'No results could be retrieved from the SPARQL endpoint.'.DAL_SXML_RET_ERR_END;
			return false;
		}
		
		$this->queryResultColumns = array();
		foreach($result['result']['variables'] as $column){
			$this->queryResultColumns[htmlspecialchars(trim($column))] = $column; 
		}
		
		$this->queryResult = array();
		foreach($result['result']['rows'] as $key => $row){
			foreach($this->queryResultColumns as $column => $oColumn){
				@ $this->queryResult[$key][$column] = htmlspecialchars(trim($row[$oColumn]));
			}
		}
		
		//echo('<pre>'.print_r($this->queryResultColumns, true).'</pre>');
		//echo('<pre>'.print_r($this->queryResult, true).'</pre>');
		return true;		
	}
	
	private function getEndpointURIFromSourceSpec($dataSourceSpec){
		preg_match('/<endpoint.*?>(.*?)<\/endpoint>/i', $dataSourceSpec, $endpoint);
		
		return (count($endpoint) == 2) ? $endpoint[1] : null;
	}
	
	private function getQueryFromSourceSpec($dataSourceSpec){
		
		if(strpos($dataSourceSpec, '<query')){
			$start = strpos($dataSourceSpec, '<query');
			$start = strpos($dataSourceSpec, '>', $start) + 1;
			$end = strpos($dataSourceSpec, '</query>');
		} else {
			$start = strpos($dataSourceSpec, '<QUERY');
			$start = strpos($dataSourceSpec, '>', $start) + 1;
			$end = strpos($dataSourceSpec, '</QUERY>');
		}
		$query = substr($dataSourceSpec, $start, $end-$start);
		$query = str_replace('&gt;', '>', $query);
		$query = str_replace('&lt;', '<', $query);
		
		return $query;
	}
	
	
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
	
	public function executeCallBack($signature, $mappingPolicy, $conflictPolicy, $termImportName){
		return true;
	}
	
}




