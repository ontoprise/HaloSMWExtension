<?php

define('TF_SHOW_AJAX_LOADER_HTML_PARAM', '__tf_show_ajax_loader');
define('TF_TABULAR_FORM_ID_PARAM', '__tf_tabular_form_id');

global $wgAjaxExportList;
$wgAjaxExportList[] = 'tff_getTabularForm';
$wgAjaxExportList[] = 'tff_updateInstanceData';
$wgAjaxExportList[] = 'tff_checkArticleName';
$wgAjaxExportList[] = 'tff_deleteInstance';
$wgAjaxExportList[] = 'tff_getLostInstances';
$wgAjaxExportList[] = 'tff_checkAnnotationValues';

/*
 * Called by UI in order to load a tabular form
 */
function tff_getTabularForm($querySerialization, $isSPARQL, $tabularFormId){
	$querySerialization = json_decode($querySerialization, true);

	$queryString = '';
	$queryParams = array();
	$printRequests = array();

	if($isSPARQL){
		SMWSPARQLQueryProcessor::processFunctionParams(
		$querySerialization, $queryString, $queryParams, $printRequests);

		//Replace strange encoding
		$queryString = str_replace('&nbsp;', ' ', $queryString);
			
		$queryParams[TF_SHOW_AJAX_LOADER_HTML_PARAM] = 'false';
		$queryParams[TF_TABULAR_FORM_ID_PARAM] = $tabularFormId;
			
		$result = SMWSPARQLQueryProcessor::getResultFromQueryString
		( $queryString, $queryParams, $printRequests, 0);
	} else {
		$querySerialization[] = TF_SHOW_AJAX_LOADER_HTML_PARAM.'=false';
		$querySerialization[] = TF_TABULAR_FORM_ID_PARAM.'='.$tabularFormId;

		SMWQueryProcessor::processFunctionParams(
		$querySerialization, $queryString, $queryParams, $printRequests);

		//$queryFormat = SMWQueryProcessor::getResultFormat($queryParams);
		$queryFormat = 'tabularform';
		$queryQbject = SMWQueryProcessor::createQuery(
		$queryString, $queryParams, 0, $queryFormat, $printRequests );
			
		$result = SMWQueryProcessor::getResultFromQueryString
		( $queryString, $queryParams, $printRequests, 0);
	}

	$useSilentAnnotationsTemplate = false;
	if(array_key_exists('use silent annotations template', $queryParams)){
		if($queryParams['use silent annotations template'] == 'true'){
			$useSilentAnnotationsTemplate = true;
		}
	}

	$result = array('result' => $result, 'tabularFormId' => $tabularFormId,
		'useSAT' => $useSilentAnnotationsTemplate);
	$result = json_encode($result);

	return '--##starttf##--' . $result . '--##endtf##--';
}


/*
 * Called by UI in order to add or modify a particular insatnce
 */
function tff_updateInstanceData($updates, $articleTitle, $revisionId, $rowNr, $tabularFormId, $useSAT){

	//dom't know why, but this has to be done twice
	$updates = json_decode(print_r($updates, true), true);
	if(!is_array($updates)){
		$updates = json_decode(print_r($updates, true), true);
	}

	$annotations = new TFAnnotationDataCollection();
	$params = array();
	foreach($updates as $update){
		if($update['isTemplateParam'] == 'true'){
			if(strlen($update['originalValue']) == 0) $update['originalValue'] = null;
			$params[$update['address']]['originalValues'][$update['templateId']] = $update['originalValue'];
			$params[$update['address']]['newValues'][$update['templateId']] = trim($update['newValue']);
		} else {
			//if(strlen($update['address']) == 0) continue;
			if($revisionId == '-1') $update['originalValue'] = null;
			if(!array_key_exists('hash', $update)) $update['hash'] = null;
			$annotations->addAnnotation(new TFAnnotationData(
			$update['address'], $update['originalValue'], null, $update['hash'], $update['typeId'], trim($update['newValue'])));
		}
	}

	$parameters = new TFTemplateParameterCollection();
	foreach($params as $param => $values){
		$parameters->addTemplateParameter(new
		TFTemplateParameter(	$param, $values['originalValues'], $values['newValues']));
	}

	//Make sure that updates are stored in the TSC
	define('SMWH_FORCE_TS_UPDATE', 'TRUE');

	$title = Title::newFromText($articleTitle);

	if($revisionId == '-1'){
		//add instance
		$result = TFDataAPIAccess::getInstance($title)->createInstance($annotations, $parameters, $useSAT);
	} else {
		//edit instance
		$result = TFDataAPIAccess::getInstance($title)->updateValues($annotations, $parameters, $revisionId, $useSAT);
	}

	//a error msg is returnd if not successfull
	if(is_string($result)){
		$msg = $result;
		$result = false;
	} else {
		$msg = '';
	}

	$result = array('success' => $result, 'msg' => $msg, 'rowNr' => $rowNr, 
		'tabularFormId' => $tabularFormId, $revisionId, 'title' => $title->getFullText());
	$result = json_encode($result);

	return '--##starttf##--' . $result . '--##endtf##--';
}

/*
 * Called by UI in order to cecck if article name is new and valid
 */
function tff_checkArticleName($articleName, $rowNr, $tabularFormId){
	$articleName = trim($articleName);
	$articleName = explode(':', $articleName, 2);
	foreach($articleName as $key => $value){
		$articleName[$key] = ucfirst($value);
	}
	$articleName = implode(':', $articleName);

	$exists = false;
	$message = "";
	
	$validTitle = false;
	if(strpos($articleName, '#') === false){
		$title = Title::newFromText($articleName);
		if($title){
			if($title->getFullText() == $articleName){
				if($title->exists()){
					$exists = true;
				} else {
					$validTitle = true;
				}
			}
		}
	}
	
	if(strlen($articleName) == 0){
		$message = wfMsg( 'tabf_instancename_blank');
	} else if(!$validTitle){
		$articleName = str_replace(array('<', '>'), array('&lt;', '&gt;'), $articleName);
		if($exists){
			$message = wfMsg( 'tabf_instancename_exists', ucfirst($articleName));
		} else {
			$message = wfMsg( 'tabf_instancename_invalid', ucfirst($articleName));	
		}
	}
		

	$result = array('validTitle' => $validTitle, 'rowNr' => $rowNr, 
		'tabularFormId' => $tabularFormId, 'message' => $message);
	$result = json_encode($result);

	return '--##starttf##--' . $result . '--##endtf##--';
}


/*
 * This method is called by the UI in order to delete an instance
 */
function tff_deleteInstance($articleTitle, $revisionId, $rowNr, $tabularFormId){

	//Make sure that updates are stored in the TSC
	define('SMWH_FORCE_TS_UPDATE', 'TRUE');

	$title = Title::newFromText($articleTitle);
	$result = TFDataAPIAccess::getInstance($title)->deleteInstance($revisionId);

	if(is_string($result)){
		$msg = $result;
		$result = false;
	} else {
		$msg = '';
	}

	$result = array('success' => $result, 'rowNr' => $rowNr, 'msg' => $msg, 'tabularFormId' => $tabularFormId, $revisionId);
	$result = json_encode($result);

	return '--##starttf##--' . $result . '--##endtf##--';
}

function tff_getLostInstances($querySerialization, $isSPARQL, $tabularFormId, $instanceNames){

	$querySerialization = json_decode($querySerialization, true);

	$queryString = '';
	$queryParams = array();
	$printRequests = array();

	$queryString = TFQueryAnalyser::getQueryString($querySerialization, $isSPARQL);
	
	global $smwgQMaxInlineLimit;
	$offset = TFQueryAnalyser::getQueryOffset($querySerialization, $isSPARQL);
	if($offset > $smwgQMaxInlineLimit){
		$offset -= $smwgQMaxInlineLimit/2;
	} else {
		$offset = 0;
	}
	
	$querySerialization = array();
	$querySerialization[] = $queryString;
	$querySerialization[] = 'format = ul';
	$querySerialization[] = 'limit = '.$smwgQMaxInlineLimit;
	$querySerialization[] = 'offset = '.$offset;
	$querySerialization[] = 'link = none';
		
	if($isSPARQL){
		$querySerialization[] = 'src = tsc';
		
		SMWSPARQLQueryProcessor::processFunctionParams(
			$querySerialization, $queryString, $queryParams, $printRequests);
		
		$queryFormat = 'ul';
		$queryQbject = SMWSPARQLQueryProcessor::createQuery(
			$queryString, $queryParams, 0, $queryFormat, $printRequests );
			
		$result = SMWSPARQLQueryProcessor::getResultFromQueryString
			( $queryString, $queryParams, $printRequests, 0);
	} else {
					
		SMWQueryProcessor::processFunctionParams(
			$querySerialization, $queryString, $queryParams, $printRequests);

		$queryFormat = 'ul';
		$queryQbject = SMWQueryProcessor::createQuery(
			$queryString, $queryParams, 0, $queryFormat, $printRequests );
			
		$result = SMWQueryProcessor::getResultFromQueryString
			( $queryString, $queryParams, $printRequests, 0);
	}
	
	$result = substr($result, 0, strpos($result, '</ul>'));
	$result = substr($result, strpos($result, '<ul>') + strlen('<ul>'));
	$result = str_replace('<li>', '', $result);
	$result = explode('</li>', trim($result));
	foreach($result as $key => $val){
		$result[$key] = trim($val);
	}

	$instanceNames = explode('||', $instanceNames);
	foreach($instanceNames as $key => $name){
		$instanceNames[$key] = trim($name);
	}

	foreach($instanceNames as $key => $name){
		if(in_array(ucfirst($name), $result)){
			unset($instanceNames[$key]);
		}
	}
		
	$result = array_values($instanceNames);

	$result = array('result' => $result, 'tabularFormId' => $tabularFormId);
	$result = json_encode($result);

	return '--##starttf##--' . $result . '--##endtf##--';
}


/*
 * Checks if an annotation is
 * 1) valid according to the annotations type
 * 2) if the annotation matches the query's constraints, i.e. if the corresponding instance is part of the query result
 */
function tff_checkAnnotationValues($annotationName, $annotationLabel, $annotationValue, 
		$annotationValues, $queryConditions, $cssSelector, $fieldNr, $articleName){

	//first check type
	$property = SMWPropertyValue::makeUserProperty($annotationName);
	
	//test with record data type
	
	//do type check
	if(strlen($annotationValue) == 0){
		$isValid = true;
	} else {
		$nDV = SMWDataValueFactory::newPropertyObjectValue($property, $annotationValue);
		$isValid = $nDV->isValid();
	}
	$invalidValueMsg = '';
	if(!$isValid){
		if(strlen($annotationValue) > 30){
			$annotationValue = substr($annotationValue, 0,30).'...';
		}
		$articleName = str_replace(array('<', '>'), array('&lt;', '&gt;'), $articleName);
		$annotationValue = str_replace(array('<', '>'), array('&lt;', '&gt;'), $annotationValue);
		 $invalidValueMsg = 		wfMsg('tabf_annotationnamme_invalid', 
			$articleName, $annotationValue, $annotationLabel); 
	}
	
	//do instance looose test
	$warnings = array();
	if($queryConditions != 'false'){

		$annotationValues = substr(str_replace('\;', '++##/##++', $annotationValues),1);
		$annotationValues = explode(';', $annotationValues);
		
		foreach($annotationValues as $i => $value){
			if(strlen(trim($value)) == 0){
				unset($annotationValues[$i]);
			} else {
				$annotationValues[$i] = trim($value); 
			}
		}
		
		file_put_contents('d://record.rtf', print_r($queryConditions, true));
		
		$queryConditions = json_decode($queryConditions, true);
		
		
		
		foreach($queryConditions as $comparator => $compareValues){
			
			foreach($compareValues as $i => $compareValue){
				
				$cDV = SMWDataValueFactory::newPropertyObjectValue($property, $compareValue);
				$cdbKeys = $cDV->getDBkeys();
				$cVal = $cdbKeys[$cDV->getValueIndex()];
				
				if($cDV instanceof SMWWikiPageValue ){
					if($annotationName != TF_CATEGORY_KEYWORD){
						$cVal = $cDV->getNamespace()-':'.$cVal;
					} 
				}
				
				$getsLost = true;
				
				if(count($annotationValues) == 0){
					
					$supportedComparators = array();
					$supportedComparators[TF_IS_EXISTS_CMP] = 'EXISTS';
					$supportedComparators[SMW_CMP_EQ] = 'EQ';
					$supportedComparators[SMW_CMP_NEQ] = 'NEQ';
					$supportedComparators[SMW_CMP_LEQ] = 'LEQ';
					$supportedComparators[SMW_CMP_GEQ] = 'GEQ';
					$supportedComparators[SMW_CMP_LESS] = 'LESS';
					$supportedComparators[SMW_CMP_GRTR] = 'GRTR';
					
					if(!array_key_exists($comparator, $supportedComparators)){
						$getsLost = false;	
					} else {
						$compareId = $supportedComparators[$comparator];
					}
				} else {
					foreach($annotationValues as $key => $annotationValue){
					
						$aDV = SMWDataValueFactory::newPropertyObjectValue(
							$property, str_replace('++##/##++', ';', $annotationValue));
						$adbKeys = $aDV->getDBkeys();
						$aVal = $adbKeys[$aDV->getValueIndex()];
						
						if($aDV instanceof SMWWikiPageValue){
							if($annotationName != TF_CATEGORY_KEYWORD){
								$aVal = $aDV->getNamespace()-':'.$aVal;
							} 
						}
					
						switch($comparator){
							case SMW_CMP_EQ :
								$compareId = 'EQ';
								if($aVal == $cVal){
									$getsLost = false;
								}
								break;
							case SMW_CMP_NEQ :
								$compareId = 'NEQ';
								if($aVal != $cVal){
									$getsLost = false;
								}
								break;
							case SMW_CMP_LEQ :
								$compareId = 'LEQ';
								if($aVal <= $cVal){
									$getsLost = false;
								}
								break;
							case SMW_CMP_GEQ :
								$compareId = 'GEQ';
								if($aVal >= $cVal){
									$getsLost = false;
								}
								break;
							case SMW_CMP_LESS :
								$compareId = 'LESS';
								if($aVal < $cVal){
									$getsLost = false;
								}
								break;
							case SMW_CMP_GRTR :
								$compareId = 'GRTR';
								if($aVal > $cVal){
									$getsLost = false;
								}
								break;
							case TF_IS_EXISTS_CMP :
								$compareId = 'EXISTS';
								if($aDV->isValid()){
									$getsLost = false;
								}
								break;
							default:
								//SMW_CMP_LIKE
								//SMW_CMP_NLKE
								//TF_IS_QC_CMP
								
								$getsLost = false;
								break;
						}
						
						if(!$getsLost){
							break;
						}
					}
				
				}
					
				if($getsLost){
					$warnings[] = 	wfMsg('tabf_lost_reason_'.$compareId, $compareValue);
				}
			}
		}
	}
	
	if(count($warnings) > 0){
		$getsLost = 0;
		$articleName = str_replace(array('<', '>'), array('&lt;', '&gt;'), $articleName);
		if(count($warnings) > 1){
			$tmp = $warnings[count($warnings)-1];
			unset($warnings[count($warnings)-1]);
			$warnings = implode(', none ', $warnings).' and none '.$tmp;
		} else {
			$warnings = $warnings[0];
		}
		
		$warnings = wfMsg('tabf_lost_reason_introTS', $articleName, $annotationLabel).$warnings;
	}
	
	$getsLost = (count($warnings) > 0) ? true : false;
		
	$result = array('isValid' => $isValid, 'lost' => $getsLost, 'looseWarnings' => $warnings,
		'cssSelector' => $cssSelector, 'fieldNr' => $fieldNr, 'invalidValueMsg' => $invalidValueMsg);
	$result = json_encode($result);

	return '--##starttf##--' . $result . '--##endtf##--';
}



