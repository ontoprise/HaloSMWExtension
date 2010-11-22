<?php


/*
 * Provides results for web service calls,
 * which are formulated in the #ask syntax.
 */
class SMWWSSMWStore extends SMWSQLStore2 {
	
	/*
	 * Get the query result from a web service
	 */
	public function getQueryResult(SMWQuery $query){
		$wsCallParameters = $this->parseQueryArgs($query);
		
		$result = SMWWebServiceUsage::processSMWQueryASWSCall($wsCallParameters);
		
		//echo('<pre>'.print_r($result, true).'</pre>');
		
		return $result;
	}
	
	/*
	 * Translate the query into a #ws call. 
	 */
	private function parseQueryArgs($query){
		
		//echo('<pre>'.print_r($query->params, true).'</pre>');
		
		$wsParameters = array();
		$configParameters = array();
		$wsName = '';
		foreach($query->params as $paramName => $paramValue){
			if(strpos($paramName, '_') === 0){
				$wsParameters[] = substr($paramName, 1).'='.$paramValue;
			} else if ($paramName == 'webservice'){
				$wsName = $paramValue;
			} else {
				if(strlen(trim($paramValue)) > 0){
					$paramValue = '='.$paramValue;
				}
				$configParameters[] = '_'.$paramName.$paramValue;
			}
		}
		
		$resultParts = array();
		foreach($query->getExtraPrintouts() as $printRequest){
		$label = $printRequest->getLabel();
			if($label != $printRequest->getData()->getText()){
				$label = '='.$label;;
			} else {
				$label = "";
			}
			
			$resultParts[] = '?'.$printRequest->getData()->getText().$label;
		}
		
		$wsCallParameters = array(null, $wsName);
		$wsCallParameters = array_merge($wsCallParameters, $wsParameters, $resultParts, $configParameters);
		
		if(!array_key_exists('limit', $wsCallParameters)){
			$wsCallParameters[] = '_limit='.$query->getLimit();
		}
		
		if(!array_key_exists('offset', $wsCallParameters)){
			$wsCallParameters[] = '_offset='.$query->getOffset();
		}
		
		//echo('<pre>'.print_r($wsCallParameters, true).'</pre>');
		
		return $wsCallParameters;
	}
}