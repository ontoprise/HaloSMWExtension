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
 * Provides results for web service calls,
 * which are formulated in the #ask syntax.
 */
class DIWSSMWStore extends SMWSQLStore2 {
	
	/*
	 * Get the query result from a web service
	 */
	public function getQueryResult(SMWQuery $query){
		$wsCallParameters = $this->parseQueryArgs($query);
		
		$result = DIWebServiceUsage::processSMWQueryASWSCall($wsCallParameters);
		
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
