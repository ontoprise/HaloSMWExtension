<?php
if ( !defined( 'MEDIAWIKI' ) ) die;

global $wgAjaxExportList;
$wgAjaxExportList[] = 'smwf_qi_QIAccess';
$wgAjaxExportList[] = 'smwf_qi_getPage';


function smwf_qi_QIAccess($method, $params) {
	$p_array = explode(",", $params);
	global $smwgQEnabled;


	if($method == "getPropertyInformation"){
		$relationName = htmlspecialchars_decode($p_array[0]);
		global $smwgContLang, $smwgHaloContLang;

		//$smwSpecialSchemaProperties = $smwgHaloContLang->getSpecialSchemaPropertyArray();

		// get type definition (if it exists)
		try {
			$relationTitle = Title::newFromText($relationName, SMW_NS_PROPERTY);
			if(!($relationTitle instanceof Title)){
				$relSchema = '<relationSchema name="'.htmlspecialchars($relationName).'" arity="2">'.
								'<param name="Page"/>'.
		           	  		 '</relationSchema>';
				return $relSchema;
			}
			$hasTypeDV = SMWPropertyValue::makeProperty("_TYPE");
			$possibleValueDV = SMWPropertyValue::makeProperty("_PVAL");
			$type = smwfGetStore()->getPropertyValues($relationTitle, $hasTypeDV);
	
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
			   			$pvalues = smwfGetStore()->getPropertyValues($relationTitle, $possibleValueDV);
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
		} catch (Exception $e){
			echo "c";
			$relSchema = '<relationSchema name="'.$relationName.'" arity="2">'.
				'<param name="Page"/>'.
		        '</relationSchema>';
			return $relSchema;
		}
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
			// read fix parameters from QI GUI
            $fixparams = array('format' => $p_array[1], 'link' => $p_array[2], 'intro' => $p_array[3], 'sort' => $p_array[4], 'limit' => $p_array[5], 'mainlabel' => $p_array[6], 'order' => $p_array[7], 'default' => $p_array[8], 'headers' => $p_array[9]);
            
            // read additional parameters for different result printers
            switch ($p_array[1]) {
            	case 'exhibit':
            		$fixparams['views'] = $p_array[10];
            		break;
            	default:
            }            
            
            // read query with printouts and (possibly) other parameters like sort, order, limit, etc...
            $pos = strpos($p_array[0], "|?");
            if ($pos > 0) {
            	$rawparams[] = trim(substr($p_array[0], 0, $pos));
            	$ps = explode("|?", trim(substr($p_array[0], $pos+2)));
	            foreach ($ps as $param) {
	            	$rawparams[] = "?" . trim($param);
	            }
            } else {
	            $ps = preg_split('/[^\|]{1}\|{1}(?!\|)/s', $p_array[0]);  
	            if (count($ps) > 1) {
	            	// last char of query condition is missing (matched with [^\|]{1}) therefore copy from original
	            	$rawparams[] = trim(substr($p_array[0], 0, strlen($ps[0]) + 1));
	            	array_shift($ps); // remove the query condition
	                // add other params for formating etc.
	                foreach ($ps as $param) 
	                    $rawparams[] = trim($param);
	            } // no single pipe found, no params specified in query
	            else $rawparams[] = trim($p_array[0]);
            }    
            
            // parse params and answer query
            SMWQueryProcessor::processFunctionParams($rawparams,$querystring,$params,$printouts);
            // merge fix parameters from GUI, they always overwrite others
            $params = array_merge($params, $fixparams);
            $result = SMWQueryProcessor::getResultFromQueryString($querystring,$params,$printouts, SMW_OUTPUT_WIKI);
			switch ($p_array[1]) {
            	case 'timeline':
            		return $result;
            		break;
            	case 'eventline':
            		return $result;
            		break;
            	case 'googlepie':
            		return $result[0];
            		break;     
            	case 'googlebar':
            		return $result[0];
            		break;            		
            	case 'exhibit':
            		return $result;
            		break;
            	default:            		
            }
            $result = parseWikiText($result);
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

/**
 * function content copied from SMWResultPrinter::getResult(). Using the constant
 * SMW_OUTPUT_HTML doesn't always work. Details see bug #10494
 * 
 * @param string  wikitext
 * @return string html
 */
function parseWikiText($text) {
	global $wgParser;
            
   	if ( ($wgParser->getTitle() instanceof Title) && ($wgParser->getOptions() instanceof ParserOptions) ) {
		$result = $wgParser->recursiveTagParse($text);
	} else {
		global $wgTitle;
		$popt = new ParserOptions();
		$popt->setEditSection(false);
		$pout = $wgParser->parse($text . '__NOTOC__', $wgTitle, $popt);
		/// NOTE: as of MW 1.14SVN, there is apparently no better way to hide the TOC
		SMWOutputs::requireFromParserOutput($pout);
		$result = $pout->getText();
	}
    return $result;           
}

/**
 * returns the complete HTML for the query interface without the Wiki toolbars etc.
 * so that the QI can be embedded into another application such as the FCK editor or
 * the Excel Client.
 * Within the parameter string the options noPreview and noLayout can be set (value
 * is not required). If the parameter is set, in the html the div for the result
 * preview of the current query is not show, and/or the layout manager is not
 * displayed.
 * 
 * @param  string key=value pairs urlencoded i.e. noPreview%26noLayout
 * @return string $html
 */
function smwf_qi_getPage($args= "") {
	global $wgServer, $wgScript;

	// fetch the Query Interface by calling the URL http://host/wiki/index.php/Special:QueryInterface
	// save the source code of the above URL in $page 
	$page = "";
	$fp = null;
	if (isset($_SERVER['AUTH_TYPE']) && isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
		$serverWithAuth = preg_replace('%^(http(s)?://)%i', "\$1".$_SERVER['PHP_AUTH_USER'].':'.$_SERVER['PHP_AUTH_PW'].'@', $wgServer);
		$fp = fopen($serverWithAuth.$wgScript."/Special:QueryInterface", "r");
	}
	else $fp = fopen($wgServer.$wgScript."/Special:QueryInterface", "r");

	// this happens at any error (also if the URL can be called but a 404 is returned)
	if (!$fp) return false;

	// fetch data from URL into $page
	while ($data = fgets($fp, 4096))
		$page .= $data;
	fclose($fp);

	// create the new source code, by removing the wiki stuff,
	// keep the header (because of all css and javascripts) and the main content part only
	$newPage = "";
	mvDataFromPage($page, $newPage, '<body');
	$newPage.= '<body style="background-image:none; background-color: #ffffff;"><div id="globalWrapper">';
	
	mvDataFromPage($page, $newPage, "<!-- start content -->", false);
	mvDataFromPage($page, $newPage, "<!-- end content -->");
	$newPage.="</div></body></html>";

	// remove the Switch to Semantic Notification button, incase it's there
	$newPage= preg_replace('/<button id="qi-insert-notification-btn"([^>]*)>(.*?)<\/button>/m', '', $newPage);
	
	// check params 
	$params = array();
	parse_str($args, $params);
	if (isset($params['noPreview']))
		delDataFromPage($newPage, '<div id="previewlayout">', '<div id="querylayout">'); 
	if (isset($params['noLayout']))
		delDataFromPage($newPage, '<div id="querylayout">', '<div id="qimenubar">'); 
	
	return $newPage;
		
}

/**
 * copy data from page to newPage by defing a pattern, up to where
 * the string is copied from the begining. If $copy is set to false
 * the data will be deleted from $page without copying it to $newPage 
 */
function mvDataFromPage(&$page, &$newPage, $pattern, $copy= true) {
	$pos = strpos($page, $pattern);
	if ($pos === false) return;
	if ($copy) {
		$newPage.= substr($page, 0, $pos);
	}
	$page = substr($page, $pos -1); 
}

/**
 * delete data from page by defing a start string and a end string.
 * the string is copied from the begining. If $copy is set to false
 * the data will be deleted from $page without copying it to $newPage 
 */
function delDataFromPage(&$page, $start, $end) {
	$pos1 = strpos($page, $start);
	if ($pos1 === false) return;
	$pos2 = strpos($page, $end, $pos1);
	if ($pos2 === false) return;
	$new = substr($page, 0, $pos1);
	$new.= substr($page, $pos2);
	$page = $new;
}

?>