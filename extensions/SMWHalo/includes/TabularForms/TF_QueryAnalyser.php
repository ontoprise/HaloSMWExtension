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
	public static function getPreloadValues($querySerialization){
		
		global $wgLang;
		
		//todo: what about sparql queries
		
		//first get manually defined preload values from the query serialization
		$preloadValues = array();
		foreach($querySerialization as $part){
			if($part[0] == '?'){
				$part = explode('=', substr($part, 1), 3);
				
				if(array_key_exists(2, $part)){
					
					$values = trim($part[2]);
					$values = str_replace('/;', '##,-,##', $values);
					$values = explode(';', $values);
					foreach($values as $key => $value){
						$values[$key] = trim(str_replace('##,-,##', ';', $value));
					}
					
					//rplace category with the internally used magic word
					if($part[0] == $wgLang->getNSText(NS_CATEGORY)){
						$part[0] = TF_CATEGORY_KEYWORD;
					}
					
					$preloadValues[ucfirst($part[0])] = $values;;
				}
			}
		}
		
		//get conditions from the instance selector of the query
		$conditions = self::getQueryConditions($querySerialization);
		
		//combine manual and automatic preload values
		foreach($conditions as $name => $condition){
			if(!array_key_exists($name, $preloadValues)){
				if(array_key_exists(SMW_CMP_EQ, $condition)){
					$preloadValues[$name] = $condition[SMW_CMP_EQ];
				}  
			}
		}
		
		return $preloadValues;
	}
	
	
	/*
	 * Get all conditions from the instance selector of the query
	 * 
	 * @param querySerialization : arry of #ask üarser function paramerers
	 */
	public static function getQueryConditions(array $querySerialization){
		
		//todo: what about sparql queries
		
		//first create query objectt in irder to retrieve query description
		$queryObject = self::getQueryObject($querySerialization);

		$conditions = self::doGetQueryConditions($queryObject->getDescription());

		//file_put_contents("d://tf_conditions.rtf", print_r($conditions, true));

		return $conditions; 
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
				
				$categoryTitles = $desc->getCategories();
				foreach($categoryTitles as $title){
					if($title instanceof Title){			
						if(@ !in_array($title->getText(), $conditions[TF_CATEGORY_KEYWORD])){
							$conditions[TF_CATEGORY_KEYWORD][SMW_CMP_EQ][] = $title->getText(); 
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
		$propertyChain = $desc->getProperty()->getWikiValue();
		$propertyName = 'loop ...';
		$isQueryChain = false;
			
		while ( ( $propertyName != '' ) && ( $subDesc instanceof SMWSomeProperty ) ) {
			$propertyName = $subDesc->getProperty()->getWikiValue();
			if ( $propertyName != '' ) {
				$isQueryChain = true;
				$propertyChain .= '.' . $propertyName;
				$subDesc = $subDesc->getDescription();
			}
		}
		
		if($subDesc instanceof SMWValueDescription){
			$value = $subDesc->getDataValue()->getWikiValue();
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
	
	
	/**
	 * Get the query steing from a query serialization (array of inline query params)
	 */
	public static function getQueryString($querySerialization){
		$queryObject = self::getQueryObject($querySerialization);
		
		return  $queryObject->getDescription()->getQueryString();
	}
	
/**
	 * Get offset rom query serialization (array of inline query params)
	 */
	public static function getQueryOffset($querySerialization){
		$queryObject = self::getQueryObject($querySerialization);
		
		return  $queryObject->getOffset();
	}
	
	
}