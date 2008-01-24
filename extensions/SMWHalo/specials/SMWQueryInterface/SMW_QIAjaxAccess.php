<?php

global $wgAjaxExportList;
global $smwgIP;
//require_once( "$smwgIP/includes/SMW_Datatype.php" );
require_once($smwgIP . '/includes/SMW_QueryProcessor.php');
$wgAjaxExportList[] = 'smwfQIAccess';


function smwfQIAccess($method, $params) {
	$p_array = explode(",", $params);
	global $smwgQEnabled;


	if($method == "getPropertyInformation"){
		$relationName = $p_array[0];
		global $smwgContLang, $smwgHaloContLang;

		$smwSpecialSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();

		// get type definition (if it exists)
		$relationTitle = Title::newFromText($relationName, SMW_NS_PROPERTY);
		if(!($relationTitle instanceof Title)){
			$relSchema = '<relationSchema name="'.$relationName.'" arity="2">'.
							'<param name="Page"/>'.
	           	  		 '</relationSchema>';
			return $relschema;
		}
		$type = smwfGetStore()->getSpecialValues($relationTitle, SMW_SP_HAS_TYPE);

		// if no 'has type' annotation => normal binary relation
		if (count($type) == 0) {
			// return binary schema (arity = 2)
			$relSchema = '<relationSchema name="'.$relationName.'" arity="2">'.
							'<param name="Page"/>'.
	           	  		 '</relationSchema>';
		} else {
			$typeLabels = $type[0]->getTypeLabels();
			$typeValues = $type[0]->getTypeValues();
			if ($type[0] instanceof SMWTypesValue) {

				// get arity
				$arity = count($typeLabels) + 1;  // +1 because of subject
		   		$relSchema = '<relationSchema name="'.$relationName.'" arity="'.$arity.'">';

		   		for($i = 0, $n = $arity-1; $i < $n; $i++) {
		   			//$th = SMWTypeHandlerFactory::getTypeHandlerByLabel($typeLabels[$i]);
		   			// change from KK: $isNum = $th->isNumeric()?"true":"false";
		   			$pvalues = smwfGetStore()->getSpecialValues($relationTitle, SMW_SP_POSSIBLE_VALUE);
		   			$relSchema .= '<param name="'.$typeLabels[$i].'">';
		   			for($j = 0; $j < sizeof($pvalues); $j++){
		   				$relSchema .= '<allowedValue value="' . $pvalues[$j]->getXSDValue() . '"/>';
		   			}
					$relSchema .= '</param>';
				}
				$relSchema .= '</relationSchema>';

			} else { // this should never happen, huh?
			$relSchema = '<relationSchema name="'.$relationName.'" arity="2">'.
							'<param name="Page"/>'.
	           	  		 '</relationSchema>';
			}
		}
		return $relSchema;
	}

	else if($method == "getNumericTypes"){
		$numtypes = array();

		$types = SMWDataValueFactory::getKnownTypeLabels();
		foreach($types as $v){
			$id = SMWDataValueFactory::findTypeID($v);
			if(SMWDataValueFactory::newTypeIDValue($id)->isNumeric())
				array_push($numtypes, strtolower($v));
		}
		return implode(",", $numtypes);
	}

	else if($method == "getQueryResult"){
		$result="null";
		if ($smwgQEnabled) {
			$params = array('format' => $p_array[1], 'link' => $p_array[2], 'intro' => $p_array[3], 'sort' => $p_array[4], 'limit' => $p_array[5], 'mainlabel' => $p_array[6], 'order' => $p_array[7], 'default' => $p_array[8], 'headers' => $p_array[9]);
			//$result = applyQueryHighlighting($p_array[0], $params);
			$result = SMWQueryProcessor::getResultFromHookParams($p_array[0],$params,SMW_OUTPUT_HTML);
			// add target="_new" for all links
			$pattern = "|<a|i";
			$result = preg_replace($pattern, '<a target="_new"', $result);
		}
		return $result;
	}
	//TODO: Unify this method with "getQueryResult", maybe add another parameter in JS for check
	else if($method == "getQueryResultForDownload"){
		$result="null";
		if ($smwgQEnabled) {
			$params = array('format' => $p_array[1], 'link' => $p_array[2], 'intro' => $p_array[3], 'sort' => $p_array[4], 'limit' => $p_array[5], 'mainlabel' => $p_array[6], 'order' => $p_array[7], 'default' => $p_array[8], 'headers' => $p_array[9]);
			$result = applyQueryHighlighting($p_array[0], $params);
			// add target="_new" for all links
			$pattern = "|<a|i";
			$result = preg_replace($pattern, '<a target="_new"', $result);
		}
		if ($result != "null" && $result != ""){
			global $request_query;
			$request_query = true;
		}
		return $result;
	}
	//TODO: Save Query functionality
	/*
	else if($method == "saveQuery"){
		$title = "Query:" . $p_array[0];
		$query = $p_array[1];
		$wikiTitle = Title::newFromText($title, NS_TEMPLATE);

		if($wikiTitle->exists()){
			return "exists";
		} else {
			$article = new Article($wikiTitle);
			$success = $article->doEdit($query, wfMsg('smw_qi_querySaved'), EDIT_NEW);
			return $success ? "true" : "false";
		}
	}
	// TODO: Load query functionality
	else if ($method == "loadQuery"){
		$title =  Title::newFromText($p_array[0], NS_TEMPLATE);
		if($title->exists()){
			$revision = Revision::newFromTitle($title);
			$fullQuery = $revision->getRawText();

			//extract display settings and actual query
			$pattern = '/<ask ([^>]+)>(.*?)<\/ask>/';
			$matches = array();
			if(!preg_match($pattern, $fullQuery, $matches)){
				return "false";
			}
			$display = $matches[1];
			$query = $matches[2];
		} else {
			return "false";
		}
	}
	*/
	else {
		return "false";
	}
}
?>