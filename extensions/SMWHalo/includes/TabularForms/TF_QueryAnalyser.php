<?php


/*
 * Provides some static helper methods for doing stuff with the
 * inline query behind the tabular forms
 */
class TFQueryAnalyser {
	
	/*
	 * Combines manual and automatic preload values and returns them as an array
	 * 
	 * @param querySerialization : arry of #ask üarser function paramerers
	 */
	public static function getPreloadValues($querySerialization, $isSPARQL){
		
		global $wgLang;
		
		$annotationPreloadValues = array();
		$instanceNamePreloadValue = null;
		
		//first get manually defined annotation preload values from the query serialization
		foreach($querySerialization as $part){
			if($part[0] == '?'){
				$part = explode('=', substr($part, 1), 3);
				
				if(array_key_exists(2, $part)){
					
					$values = trim($part[2]);
					$values = str_replace('\;', '##,-,##', $values);
					$values = explode(';', $values);
					foreach($values as $key => $value){
						$values[$key] = trim(str_replace('##,-,##', ';', $value));
					}
					
					//rplace category with the internally used magic word
					if($part[0] == $wgLang->getNSText(NS_CATEGORY)){
						$part[0] = TF_CATEGORY_KEYWORD;
					}
					
					$annotationPreloadValues[ucfirst($part[0])] = $values;;
				}
			}
		}
		
		//check if there is a manual instance name preload value
		foreach($querySerialization as $part){
			if(strpos($part, 'instance name preload value') === 0
					&& strpos($part, '=') > 0){
				$part = explode('=', $part, 2);
				$instanceNamePreloadValue = trim($part[1]);
			}
		}
		
		//get conditions from the instance selector of the query
		if($isSPARQL){
			$conditions = array();
		} else {
			$conditions = self::getQueryConditions($querySerialization, $isSPARQL);
		}
		
		//combine manual and automatic preload values
		foreach($conditions as $name => $condition){
			if($name == TF_INSTANCENAME_KEYWORD){ //instance name preload value
				if($instanceNamePreloadValue == null){ //nor LEWs< set manually
					if(array_key_exists(TF_NAMESPACE_CMP, $condition) && count($condition[TF_NAMESPACE_CMP]) == 1){
						$instanceNamePreloadValue = 
							$wgLang->getNSText($condition[TF_NAMESPACE_CMP][0]).':';
					}
				}	
			} else { //annotation reload value
				if(!array_key_exists($name, $annotationPreloadValues)){
					if(array_key_exists(SMW_CMP_EQ, $condition)){
						$annotationPreloadValues[$name] = $condition[SMW_CMP_EQ];
					}  
				}
			}
		}
		
		if($instanceNamePreloadValue == null){
			$instanceNamePreloadValue = '';
		}
		
		return array($annotationPreloadValues, $instanceNamePreloadValue);
	}
	
	
	/*
	 * Get all conditions from the instance selector of the query
	 * 
	 * @param querySerialization : arry of #ask üarser function paramerers
	 */
	public static function getQueryConditions(array $querySerialization, $isSPARQL){
		
		if($isSPARQL){
			return array();
		} else {
			//first create query objectt in irder to retrieve query description
			$queryObject = self::getQueryObject($querySerialization);
			
			$conditions = self::doGetQueryConditions($queryObject->getDescription());
	
			return $conditions;
		}
	}
	
	
/*
 * Walk down the query descrption tree and get necessary condition information
 */
	private static function doGetQueryConditions($queryDescription, $conditions = array()){

		if(is_null($queryDescription)){
			//end of description tree leaf reached
			return;
		}
		
		if(!is_array($queryDescription)){
			$queryDescription = array($queryDescription);
		}
		
		foreach($queryDescription as $desc){
		
			if($desc instanceof SMWClassDescription){ //one or more category conditions
				
				$categoryItems = $desc->getCategories();
				foreach($categoryItems as $item){
					if($item instanceof SMWDIWikiPage){			
						if(@ !in_array($item->getTitle()->getText(), $conditions[TF_CATEGORY_KEYWORD][SMW_CMP_EQ])){
							$conditions[TF_CATEGORY_KEYWORD][SMW_CMP_EQ][] = $item->getTitle()->getText(); 
						}
					}
				}
			
			} else if($desc instanceof SMWSomeProperty){ // property condition
				list($name, $value, $comparator, $isQueryChain) = 
					self::getPropertyDescriptionData($desc);
				
				if($isQueryChain){
					$realName = substr($name, 0, strpos($name, '.'));
					
					if(@ !in_array($value, 
							$conditions[ucfirst($realName)][TF_IS_QC_CMP][$name][$comparator])){
						$conditions[ucfirst($realName)][TF_IS_QC_CMP][$name][$comparator] = $value;
					}
				} else {
					if(@ !in_array($value, $conditions[ucfirst($name)][$comparator])){
						$conditions[ucfirst($name)][$comparator][] = $value;
					}
				}
			} else if($desc instanceof SMWNamespaceDescription){
				$namespaceId = $desc->getNamespace();
				if(@ !in_array($namespaceId, $conditions[TF_INSTANCENAME_KEYWORD][TF_NAMESPACE_CMP])){
					$conditions[TF_INSTANCENAME_KEYWORD][TF_NAMESPACE_CMP][] = $namespaceId; 
				}
			} else if($desc instanceof SMWDisjunction || $desc instanceof SMWConjunction){
				$conditions = TFQueryAnalyser::doGetQueryConditions(
					$desc->getDescriptions(), $conditions);
			}
		}
		
		return $conditions;
	}
	
	
	/*
	 * Get the information which is encoded in a Property description
	 */
	private static function getPropertyDescriptionData(SMWDescription $desc){
		
		$subDesc = $desc->getDescription();
		$propertyChain = $desc->getProperty()->getLabel();
		$propertyName = 'loop ...';
		$isQueryChain = false;
			
		while ( ( $propertyName != '' ) && ( $subDesc instanceof SMWSomeProperty ) ) {
			$propertyName = $subDesc->getProperty()->getLabel();
			if ( $propertyName != '' ) {
				$isQueryChain = true;
				$propertyChain .= '.' . $propertyName;
				$subDesc = $subDesc->getDescription();
			}
		}
		
		if($subDesc instanceof SMWValueDescription){
			$value = $subDesc->getDataValue()->getSortKey();
			$comparator = $subDesc->getComparator();	
		} else if($subDesc instanceof SMWThingDescription){ // if exists condition
			$value = '';
			$comparator = TF_IS_EXISTS_CMP;
		}
		
		return array($propertyChain, $value, $comparator, $isQueryChain);
	}
	
	
	public static function getDisjunctivelyConnectedQueryStringParts($querySerialization){

		//first create query objectt in irder to retrieve query description
		$queryObject = self::getQueryObject($querySerialization);
		
		$queryStringParts = array();
			
		if($queryObject->getDescription() instanceof SMWDisjunction){
			foreach($queryObject->getDescription()->getDescriptions() as $desc){
				$queryStringParts[] = $desc->getQueryString();
			}	 
		} else {
			$queryStringParts[] = $queryObject->getDescription()->getQueryString();
		}
		
		return $queryStringParts;
	}
	
	
	/*
	 * Create query object from query serialization
	 */
	private static function getQueryObject($querySerialization){
		SMWQueryProcessor::processFunctionParams( 
			$querySerialization, $queryString, $queryParams, $printRequests);
		$queryFormat = 'table';
		$queryObject = SMWQueryProcessor::createQuery( 
			$queryString, $queryParams, 0, $queryFormat, $printRequests );
			
		return $queryObject;
	}
	
	
	/*
	 * Create SPARQL query object from query serialization
	 */
	private static function getQueryObjectSPARQL($querySerialization){
		SMWSPARQLQueryProcessor::processFunctionParams( 
			$querySerialization, $queryString, $queryParams, $printRequests);
		$queryFormat = 'table';
		$queryObject = SMWSPARQLQueryProcessor::createQuery( 
			$queryString, $queryParams, 0, $queryFormat, $printRequests );
			
		return $queryObject;
	}
	
		
	/**
	 * Get the query steing from a query serialization (array of inline query params)
	 */
	public static function getQueryString($querySerialization, $isSPARQL = false){
		if($isSPARQL){
			$queryObject = self::getQueryObjectSPARQL($querySerialization);
		} else {
			$queryObject = self::getQueryObject($querySerialization);
		}
		
		return  $queryObject->getDescription()->getQueryString();
	}
	
/**
	 * Get offset rom query serialization (array of inline query params)
	 */
	public static function getQueryOffset($querySerialization, $isSPARQL = false){
		if($isSPARQL){
			$queryObject = self::getQueryObjectSPARQL($querySerialization);
		} else {
			$queryObject = self::getQueryObject($querySerialization);
		}
		
		return  $queryObject->getOffset();
	}
	
	
}