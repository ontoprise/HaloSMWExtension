<?php
/**
 * @file
 * @ingroup SMWHaloSpecials
 * @ingroup SMWHaloQueryInterface
 * 
 * @author Markus Nitsche
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

global $wgAjaxExportList, $wgHooks;
$wgAjaxExportList[] = 'smwf_qi_QIAccess';
$wgAjaxExportList[] = 'smwf_qi_getPage';
$wgAjaxExportList[] = 'smwf_qi_getAskPage';

$wgHooks['ajaxMIMEtype'][] = 'smwf_qi_getPageMimeType';

function smwf_qi_getPageMimeType($func, & $mimeType) {
    if ($func == 'smwf_qi_getPage') $mimeType = 'text/html; charset=utf-8';
    if ($func == 'smwf_qi_getAskPage') $mimeType = 'text/html; charset=utf-8';
   return true;
}

function smwf_qi_QIAccess($method, $params, $currentPage= null) {
	$p_array = explode(",", $params);
	global $smwgQEnabled;

	if($method == "getPropertyInformation"){
		return qiGetPropertyInformation($p_array[0]);
	}
	else if ($method == "getPropertyTypes") {
		$p_array = func_get_args();
		$types = "<propertyTypes>";
		for ($i = 1; $i < count($p_array); $i++) {
			$types .= qiGetPropertyInformation($p_array[$i]);
		}
		$types.= "</propertyTypes>";
		return $types;
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

             $params = ( count($p_array) > 1 ) ? explode("|",$p_array[1]) : array();
             $fixparams = array();
             foreach($params as $p) {
                 if (strlen($p) > 0 && strpos($p, "=") !== false) {
                    list($key, $value) = explode("=", $p);
                     $fixparams[trim($key)] = str_replace('%2C', ',', $value);
                 }
             }
             // indicate that it comes from an ajax call
            $fixparams['ajaxCall'] = true;
            
            // fix bug 10812: if query string contains a ,
            $p_array[0] = str_replace('%2C', ',', $p_array[0]);

            // fix bug 14308
            if (!empty($currentPage) && $currentPage != 'Special:QueryInterface') {
                $p_array[0] = parseQuery($p_array[0], $currentPage);
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
         
            $rawparams = array_merge($rawparams, $fixparams);
            // set some default values, if params are not set
            $useTsc = (in_array('source', array_keys($fixparams)) && strtolower($fixparams['source']) == 'tsc');
            if (! in_array('format', array_keys($fixparams))) $fixparams['format'] = 'table';

            // use SMW classes or TSC classes and parse params and answer query
            if ($useTsc)
                SMWSPARQLQueryProcessor::processFunctionParams($rawparams,$querystring,$params,$printouts);
            else
                SMWQueryProcessor::processFunctionParams($rawparams,$querystring,$params,$printouts);
            // check if there is any result and if it corresponds to the selected format
            $mainlabel = (isset($rawparams['mainlabel']) && $rawparams['mainlabel'] == '-');
            $invalidRes = smwf_qi_CheckValidResult($printouts, $fixparams['format'], $mainlabel);
            if ( $invalidRes != 0 )
                return wfMsg('smw_qi_printout_err'.$invalidRes);
                
            // quickfix: unset conflicting params for maps
            if (in_array($fixparams['format'], array( "map", "googlemaps2", "openlayers", "yahoomaps" ) ) ) {
                if (isset($params['reasoner'])) unset($params['reasoner']);
                if (isset($params['ajaxcall'])) unset($params['ajaxcall']);
                if (isset($params['merge'])) unset($params['merge']);
            }
            // answer query using the SMW classes or TSC classes
            if ($useTsc)
                $result = SMWSPARQLQueryProcessor::getResultFromQueryString($querystring,$params,$printouts, SMW_OUTPUT_WIKI);
            else
                $result = SMWQueryProcessor::getResultFromQueryString($querystring,$params,$printouts, SMW_OUTPUT_WIKI);

            // check for empty result
            if (is_array($result) && trim($result[0]) == '' || trim($result == '') ) {
                $msg = wfMsg('smw_qi_printout_err4');
                if (defined('LOD_LINKEDDATA_VERSION'))
                    $msg.= ' '.wfMsg('smw_qi_printout_err4_lod');
                return $msg;
            }

            switch ($fixparams['format']) {
            	case 'timeline':
            	case 'exhibit':
            	case 'eventline':
            		return $result;
            		break;
            	case 'gallery':
            	case 'googlepie':
            	case 'googlebar':
                case 'ofc':
            	case 'ofc-pie':
           		case 'ofc-bar':
           		case 'ofc-bar_3d':
           		case 'ofc-line':
           	    case 'ofc-scatterline':
                case 'jqplotbar':
                case 'jqplotpie':
                case 'tabularform':
                case 'tagcloud':
                    return (is_array($result)) ? $result[0] : $result;
                    break;   
                case 'map':
                case 'googlemaps2':
                case 'openlayers':
                case 'yahoomaps':
                    return wfMsg('smw_qi_printout_notavailable');
                    default:            		
            }
          
            $result = parseWikiText($result);
			// add target="_new" for all links
			$pattern = "|<a|i";
			$result = preg_replace($pattern, '<a target="_new"', $result);
			return $result;
		}
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
	} else if ($method == "getSupportedParameters") {
		global $smwgResultFormats;
		wfLoadExtensionMessages('SemanticMediaWiki');
	
		$format = $p_array[0];
        if (array_key_exists($format, $smwgResultFormats))
            $formatclass = $smwgResultFormats[$format];
        else
            $formatclass = "SMWListResultPrinter";

        // fix for missing parameter order
        $order_missing = true;
        $limit_missibg = true;
        $offset_missing = true;
        $intro_missing = true;
        $outro_missing = true;
        $qp = new $formatclass($format, false);
        $params = $qp->getParameters();
        // repair some misplaced parameters
        for ($i =0; $i < count($params); $i++) {
            switch ($params[$i]['name']) {
                case "order" :
                    $order_missing = false;
                    break;
                case "limit" :
                    $limit_missing = false;
                    break;
                case "offset" :
                    $offset_missing = false;
                    break;
                case "template" :
                    if ( $format != "template" )
                        array_splice($params, $i, 1);
                    break;
                case "intro" :
                    if (substr($format, 0, 4) != "ofc-")
                        $intro_missing = false;
                    break;
                case "outro" :
                    if (substr($format, 0, 4) != "ofc-")
                        $outro_missing = false;
                    break;
                case "headers" :
                    if ( $format != "table" && $format != "broadtable" )
                        array_splice($params, $i, 1);
                    break;
            }
        }
        if ($order_missing) {
            $params[]= array(
                'name' => 'order',
                'type' => 'enumeration',
                'description' => wfMsg('smw_qi_tt_order'),
                'values' => array('ascending', 'descending'),
            );
        }
        if ($limit_missing) {
            $params[]= array(
                'name' => 'limit',
                'type' => 'int',
                'description' => wfMsg('smw_qi_tt_limit')
            );
        }
        if ($offset_missing) {
            $params[]= array(
                'name' => 'offset',
                'type' => 'int',
                'description' => wfMsg('smw_qi_tt_offset')
            );
        }
        if ($intro_missing) {
            $params[]= array(
                'name' => 'intro',
                'type' => 'string',
                'description' => wfMsg('smw_qi_tt_intro'),
            );
        }
        if ($outro_missing) {
            $params[]= array(
                'name' => 'outro',
                'type' => 'string',
                'description' => wfMsg('smw_qi_tt_outro'),
            );
        }
        $jsonEnc = new Services_JSON();
       
        return $jsonEnc->encode($params);
	}
    else if ($method == "searchQueries") {
        $p_array = func_get_args();
        if (count($p_array) != 3) return false;
        $term = $p_array[1];
        $type = $p_array[2];
        $result = array();

        // some weird bug allows to send only 3 or conditions. Therefore we must
        // send two requests when type = * is set.
        if ($type == 's') {
            $queryMetadataPattern = new SMWQMQueryMetadata(true, array(ucfirst($term) => true),
            '0');
        }
        else if ($type == 'c') {
            $queryMetadataPattern = new SMWQMQueryMetadata(true, null,
            '0', array(ucfirst($term) => true));
        }
        else if ($type == 'p') {
            $queryMetadataPattern = new SMWQMQueryMetadata(true, null,
            '0', null, array(ucfirst($term) => true));
        }
        else if ($type == 'r') {
            $queryMetadataPattern = new SMWQMQueryMetadata(true, null,
            '0', null, null, null, $term);
        }
        else if ($type == 'i') {
            $queryMetadataPattern = new SMWQMQueryMetadata(true, null,
            '0', null, null, ucfirst($term));
        }
        else if ($type == 'q') {
            $queryMetadataPattern = new SMWQMQueryMetadata(true, null,
            '0', null, null, null, null, $term);
        }
        else if ($type == '*') {
            // type in s c p
            $queryMetadataPattern = new SMWQMQueryMetadata(true,
            array(ucfirst($term) => true), '0', array(ucfirst($term) => true),
            array(ucfirst($term) => true));
            // type in i q
            $queryMetadataPattern1 = new SMWQMQueryMetadata(true, null, '0',
            null, null, ucfirst($term), null, $term);
        }
        
        //ask for all queries which use the #ask syntax
        $queryMetadataPattern->isSparqlQuery = null;
        $queryMetadataPattern->usesASKSyntax = '1';
        
        $queryMetadataResults = SMWQMQueryManagementHandler::getInstance()->searchQueries($queryMetadataPattern);


        $result = qiMergeQueryMetadataResults($queryMetadataResults, $result);
        if (isset($queryMetadataPattern1)) {
            //ask for all queries which use the #ask syntax
        	$queryMetadataPattern1->isSparqlQuery = null;
        	$queryMetadataPattern1->usesASKSyntax = '1';
        	
        	$queryMetadataResults = SMWQMQueryManagementHandler::getInstance()->searchQueries($queryMetadataPattern1);
            $result = qiMergeQueryMetadataResults($queryMetadataResults, $result);
        }
        $jsonEnc = new Services_JSON();

        return $jsonEnc->encode($result);
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
 * Gets a querystring like [[Category:User]][[Is project member of::{{FULLPAGENAME}}]]
 * and replaces the magic word with the applicable for the current page.
 *
 * @param string query text
 * @param string page name
 * @return string query text
 */
function parseQuery($query, $page) {
    $query = str_replace('[', '%%%BrOpen%%%', $query);
    $query = str_replace(']', '%%%BrClose%%%', $query);
    $query = str_replace('|', '%%%Pipe%%%', $query);
    
    // do not use the global parser that screws up things
    $parser = new Parser();
    $title = Title::newFromText($page);
    $popt = new ParserOptions();
    $popt->setEditSection(false);
    $pout = $parser->parse($query . '__NOTOC__', $title, $popt);
    SMWOutputs::requireFromParserOutput($pout);
    $query = $pout->getText();

    $query = parseWikiText( $query, $page );
    $query = str_replace('%%%BrOpen%%%', '[', $query);
    $query = str_replace('%%%BrClose%%%', ']', $query);
    $query = str_replace('%%%Pipe%%%','|', $query);
    $query = strip_tags($query);
    $query = str_replace('&amp;', '&', $query);
    $query = str_replace('&nbsp;', ' ', $query);
    return $query;
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
	global $wgServer, $wgScript, $wgLang;
        $qiScript = $wgScript.'/'.$wgLang->getNsText(NS_SPECIAL).':QueryInterface';

        // fetch the Query Interface by calling the URL http://host/wiki/index.php/Special:QueryInterface
	// save the source code of the above URL in $page 
	$page = "";
	if (function_exists('curl_init')) {
		list($httpErr, $page) = doHttpRequestWithCurl($wgServer, $qiScript);
	}
	else {
      return "Error: please activate the Curl module in your PHP configuration";
	}
	// this happens at any error (also if the URL can be called but a 404 is returned)
	if ($page === false || $httpErr != 200)
	   return "Error: SMWHalo seems not to be installed. Please install the SMWHalo extension to be able to use the Query Interface.<br/>HTTP Error code ".$httpErr;

	// create the new source code, by removing the wiki stuff,
	// keep the header (because of all css and javascripts) and the main content part only
	$newPage = "";
	mvDataFromPage($page, $newPage, '<body');
	$newPage.= '<body style="background-image:none; background-color: #ffffff;"><div id="globalWrapper"><div id="content">';
	
	mvDataFromPage($page, $newPage, "<!-- start content -->", false);
	mvDataFromPage($page, $newPage, "<!-- end content -->");
	$newPage.="</div></div></body></html>";

	// remove the Switch to Semantic Notification button, incase it's there
	$newPage= preg_replace('/<button id="qi-insert-notification-btn"([^>]*)>(.*?)<\/button>/m', '', $newPage);

    // remove smwCSH.js include because we do not want a help link in the query interface popup to appear
    $newPage = preg_replace('/<script.*?\/smwCSH.js.*?<\/script>/', "", $newPage);
	
	// have a string where to store JS command for onload event
	$onloadArgs = ''; 

	// parse submited params 
        $params = array();
        parse_str($args, $params);
	// when called from the Excel Bridge, params noPreview and noLayout must be set
	// also the "Copy to clipboard" button must be hidden. The Excel Bridge is recognized by the
	// appropriate Useragent, params may not be set but will adjusted automatically
	if (isset($_SERVER['HTTP_USER_AGENT']) &&
	    stripos($_SERVER['HTTP_USER_AGENT'], 'Excel Bridge') !== false) {
	      $params['noPreview'] = true;
	      $params['noLayout'] = true;
	      $newPage = str_replace('onclick="qihelper.copyToClipboard()"',
	                             'onclick="qihelper.copyToClipboard()" style="display: none;"',
	                             $newPage);
          $onloadArgs.='initialize_qi_from_excelbridge(); '; 
    }
    else $excelBridge = '';

    // check params and change HTML of the Query Interface
	if (isset($params['noPreview']))
		$newPage = str_replace('<div id="previewlayout">', '<div id="previewlayout" style="display: none;">', $newPage); 
	if (isset($params['noLayout']))
		$newPage = str_replace('<div id="querylayout">', '<div id="querylayout" style="display: none;">', $newPage);
    if (isset($params['query'])) {
        $queryString = str_replace('"', '&quot;', $params['query']);
        $queryString = str_replace("'", "\'", $queryString);
        $onloadArgs .= 'initialize_qi_from_querystring(\''.$queryString.'\');';      
    } 
    if (strlen($onloadArgs) > 0)
        $newPage = str_replace('<body',
                               '<body onload="'.$onloadArgs.'"',
                               $newPage);
    // for the CKEditor we set a smaller font size
    if (isset($params['CKE']))
        $newPage = preg_replace('/(<body.*?)(style=")([^"]*")/i', '$1$2font-size: 70%; $3', $newPage);
    
    // remove unnecessary scripts
    $newPage = preg_replace_callback('/<script[^>]+>([^<]+|<!)*<\/script>/','smwf_qi_deleteScriptsCallback', $newPage);

	return $newPage;
		
}

/**
 * Wrapper function to build layout around 'normal' ajax QI
 * 
 * @param  string key=value pairs urlencoded i.e. noPreview%26noLayout
 * @return string $html
 */
function smwf_qi_getAskPage($args= "") {

	$html = smwf_qi_getPage($args);
	$html .= '<div id="stb-qi-footer-spacer"></div>';
	$html .= '<div id="stb-qi-footer-wrap">';
	$html .= '<div id="stb-qi-footer-container">';
	$html .= '<div id="stb-qi-footer">';
	$html .= '<input id="stb-qi-save-button" type="button" value="OK" name="ok" onclick="javascript:qihelper.querySaved=true;parent.jQuery.fancybox.close();" />'.
		'<input id="stb-qi-cancel-button" type="button" value="Cancel" name="cancel" onclick="javascript:qihelper.querySaved=false;parent.jQuery.fancybox.close();" />';
	$html .= '</div>';
	$html .= '</div>';
	$html .= '</div>';
	return $html;
}

/**
 * Check the print out params for types and return an error if the choosen format
 * doesn't have corresponding types
 *
 * @param array(SMWDataValue) $printRequest
 * @param string $format
 * @param boolean $mainlabel (true if surpressed, false otherwise);
 * @return int $result
 *         0 = format matches printouts
 *         1 = format needs at least 1 additional printout
 *         2 = format needs at least 1 printout of the type date
 *         3 = format needs at least 1 numeric printout
 */
function smwf_qi_CheckValidResult ($printRequest, $format, $mainlabel) {

    $formatNeedsNumber = array('sum', 'min', 'max');
    $formatNeeds2Cols = array('googlepie', 'googlebar', 'ofc-pie', 'ofc-bar', 'ofc-bar_3d', 'ofc-line', 'ofc-scatterline');
    $formatNeedsDate = array('timeline', 'eventline');
    $numericTypes = array('_num', '_tem');
    if ((count($printRequest) == 0 ||
        ( count($printRequest) == 1 && $mainlabel) ) &&
        in_array($format, array_merge($formatNeeds2Cols, $formatNeedsDate)))
            return 1;
    // check types
    $haveDate = false;
    $haveNumber = false;
    foreach( $printRequest as $object ) {
        $prData = $object->getData();
        if (is_null($prData)) continue; // for Category print requests
        $propName = $prData->getDBkey();
        $propXML = qiGetPropertyInformation($propName);
        $xmlDoc = DOMDocument::loadXML($propXML);
        for ($i = 0; $i < count($xmlDoc->documentElement->childNodes); $i++) {
            $type = $xmlDoc->documentElement->childNodes->item($i)->getAttribute('type');
            $unit = $xmlDoc->documentElement->childNodes->item($i)->getElementsByTagName('unit');
            if ( $unit->length > 0 || in_array($type, $numericTypes) )
                $haveNumber = true;
            if ($type == '_dat')
                $haveDate = true;
        }
    }
    if (in_array($format, $formatNeedsDate) && !$haveDate)
        return 2;
    if (in_array($format, array_merge($formatNeedsNumber, $formatNeeds2Cols)) && !$haveNumber)
        return 3;
    return 0;
}

function smwf_qi_deleteScriptsCallback($match) {
	
	
	 /* // positive list
	 $keepScripts = array("wgServer","generalTools","SMW_sortable", "SMW_tooltip", "ajax","language",
	                     "wick", "prototype", "QIHelper.js", "qi_tooltip", "Query", 
	                     "deployQueryInterface", "deploy_qi_tooltip");
	
	foreach($keepScripts as $script) {
	   $contains = stripos($match[0], $script);
	   if ($contains !== false) break;
	}
	return ($contains !== false) ? $match[0] : '';*/
	
	// negative list
	$removeScripts = array('acl', 'richmedia');
	foreach($removeScripts as $script) {
       $remove = stripos($match[0], $script);
       if ($remove !== false) break;
    }
    return ($remove !== false) ? '' : $match[0];
}

// below this line there are functions that are needed by the Ajax functions above
// but which are not called from outside directly

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
 * retrieve a web page via curl
 *
 * @param string server i.e. http://www.domain.com (incl protocol prefix) 
 * @param string file	i.e. /path/to/script.cgi or /some/file.html
 * @param boolean debug turn on debugging - default is off
 * @return array(int, string) with httpCode, page 
 */
function doHttpRequestWithCurl($server, $file, $debug = false) {
	if ($file{0} != "/") $file = "/".$file;
	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $server.$file);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    // needs authentication?	
    if (isset($_SERVER['AUTH_TYPE']) ) {
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            curl_setopt($c, CURLOPT_USERPWD, $_SERVER['PHP_AUTH_USER'].":".$_SERVER['PHP_AUTH_PW']);
        }
        else if (isset($_SERVER['PHP_AUTH_DIGEST'])) {
            $authData = qiParseHttpDigest($_SERVER['PHP_AUTH_DIGEST']);
            global $smwgHttpAuthPassword;
            curl_setopt($c, CURLOPT_USERPWD, $authData['username'].':'.$smwgHttpAuthPassword);
            curl_setopt($c, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        }
	}
    // user agent (important i.e. for Popup in FCK Editor)
	if (isset($_SERVER['HTTP_USER_AGENT']))
		curl_setopt($c, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

	$page = curl_exec($c); 
	$httpErr = curl_getinfo($c, CURLINFO_HTTP_CODE);
    if ($debug) {
        $contentType = curl_getinfo($c, CURLINFO_CONTENT_TYPE);
        $curlErr = curl_errno ( $c );
        var_dump($httpErr, $contentType, $curlErr);
    }
	curl_close($c);
	return array($httpErr, $page);
}

/**
 * Returns information about a property in the form of:
 * <relationSchema name="${property_name}" arity="${arity}">
 *   <param name="${Property_type}">
 *   <!-- if the property has type Enumeration then this follows -->
 *      <allowedValue value="${some_value}" />
 *       ...
 *   </param>
 * </relationSchema>
 *
 * The result xml is parsed by the Javascript of the Query Interface
 * to show the correct type in the property dialogue after a property
 * name has been entered above. In case of an enumeration, the select
 * options with the possible values are filled. If the property has the
 * type page, then the restiction selector (>,<,=) is disabled.
 * 
 * Also the property information are needed when a query is parsed and
 * displayed in the Query Interface in the navigation tree. Within the
 * tree the property type is show and the dialogue box must be filled with
 * the correct values, if an existing property in the query is edited.
 * This function is then called several times by smwf_qi_QIAccess().
 * 
 * @param string $relationName
 * @return string xml
 */
function qiGetPropertyInformation($relationName) {		
		$relationName = htmlspecialchars_decode($relationName);
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
            $range = qiGetPropertyRangeInformation($relationName);

			// if no 'has type' annotation => normal binary relation
			if (count($type) == 0) {
				// return binary schema (arity = 2)
				$relSchema = '<relationSchema name="'.$relationName.'" arity="2">'.
								'<param name="Page" type="_wpg"'.$range.'/>'.
		           	  		 '</relationSchema>';
			} else {
                $units = "";
				if ($type[0] instanceof SMWTypesValue) {
                    $typeValues = $type[0]->getTypeValues();
                    // check if the type is a Record
                    if ($typeValues[0]->getDBkey() == "_rec") {
                        $record = smwfGetStore()->getPropertyValues($relationTitle, SMWPropertyValue::makeProperty("_LIST"));
                        if (count($record) > 0)
                            $typeValues= $record[0]->getTypeValues();
                    }
                    // check if it's a custom type
                    else if (! $typeValues[0]->isBuiltIn() ) {
                        $units= qiGetPropertyCustomTypeInformation($typeValues[0]->getText());
                    }
					// get arity
					$arity = count($typeValues) + 1;  // +1 because of subject
			   		$relSchema = '<relationSchema name="'.$relationName.'" arity="'.$arity.'">';
	
			   		for($i = 0, $n = $arity-1; $i < $n; $i++) {
			   			$pvalues = smwfGetStore()->getPropertyValues($relationTitle, $possibleValueDV);
			   			$relSchema .= '<param name="'.$typeValues[$i]->getWikiValue().'" type="'.$typeValues[$i]->getDBkey().'"'.$range.'>';
			   			for($j = 0; $j < sizeof($pvalues); $j++){
			   				$relSchema .= '<allowedValue value="' . array_shift($pvalues[$j]->getDBkeys()) . '"/>';
			   			}
						$relSchema .= $units.'</param>';
					}
					$relSchema .= '</relationSchema>';
	
				} else { // this should never happen, huh?
				$relSchema = '<relationSchema name="'.$relationName.'" arity="2">'.
								'<param name="Page" type="_wpg"'.$range.'/>'.
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

/**
 * Get the range annotation of a property if such exist. The special property
 * "Domain and range" must be defined at the property page.
 *
 * @param string $relationName
 * @return string range attribute i.e. 'range="Category:SomeCat"' or empty
 */
function qiGetPropertyRangeInformation($relationName) {
    global $smwgHaloContLang;
    $range= "";
    $title = Title::newFromText($relationName, SMW_NS_PROPERTY);
    $sspa = $smwgHaloContLang->getSpecialSchemaPropertyArray();
    $prop = SMWPropertyValue::makeProperty($sspa[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT]);
    $smwValues = smwfGetStore()->getPropertyValues($title, $prop);
    if (count($smwValues) > 0) {
        $domainAndRange = $smwValues[0]->getDVs();
        if (count($domainAndRange) > 1) {
            $range = ' range="'.$domainAndRange[1]->getPrefixedText().'"';
        }
    }
    return $range;
}
/**
 * For custom types, units can be defined. This is done at the page in the type
 * namespace. The special property 'Corresponds to' contains a constant for the
 * conversion and one or more labels separated by comma. These labels are
 * supposed to be displayed in the query interface so that the user may choose
 * that a specific value is of a certain type and that he also may choose which
 * unit to display in the results. If a unit has several labels, the first one
 * is used only.
 *
 * @global SMWLanguage $smwgContLang
 * @param  string $typeName
 * @return string xml snippet
 */
function qiGetPropertyCustomTypeInformation($typeName) {
    global $smwgContLang;
    $units = "";
    $conv = array();
    $title = Title::newFromText($typeName, SMW_NS_TYPE);
    $sspa = $smwgContLang->getPropertyLabels();
    $prop = SMWPropertyValue::makeProperty($sspa['_CONV']);
    $smwValues = smwfGetStore()->getPropertyValues($title, $prop);
    if (count($smwValues) > 0) {
        for ($i= 0, $is = count($smwValues); $i < $is; $i++) {
            $un = $smwValues[$i]->getDBkeys();
            if (preg_match('/([\d\.]+)(.*)/', $un[0], $matches)) {
                $ulist = explode(',', $matches[2]);
                $conv[$matches[1]]= trim($ulist[0]);
            }
        }
    }
    if (count($conv) > 0) {
        foreach (array_keys($conv) as $k) {
            $units .= '<unit label="'.$conv[$k].'"/>';
        }
    }
    return $units;
}

/**
 * Create an array of the query results that were searched for. The result array
 * is an associative array.
 *
 * @global SMWLanguage $smwgQDefaultLimit
 * @param  array(SMWQMQueryMetadata)
 * @return array(result)
 */
function qiMergeQueryMetadataResults($queryMetadataResults, $result) {
    global $smwgQDefaultLimit;
    for ($i = 0; $i < count($queryMetadataResults); $i++) {
        $queryString = $queryMetadataResults[$i]->queryString;
        if (is_array($queryMetadataResults[$i]->propertyPrintRequests)) {
            $keys = implode('|?', array_keys($queryMetadataResults[$i]->propertyPrintRequests));
            $queryString .= '|?'.$keys;
        }
        $queryString.= '|format='.$queryMetadataResults[$i]->queryPrinter;
        if ($queryMetadataResults[$i]->limit &&
            $queryMetadataResults[$i]->limit != $smwgQDefaultLimit)
            $queryString .= '|limit='.$queryMetadataResults[$i]->limit;
        if ($queryMetadataResults[$i]->offset)
                $queryString .= '|offset='.$queryMetadataResults[$i]->offset;
        if ($queryMetadataResults[$i]->queryName)
            $queryString .= '|queryname='.$queryMetadataResults[$i]->queryName;

        // check if the result exists already in the list
        for ($k = 0; $k < count($result); $k++) {
            if ($result[$k]['name'] == $queryMetadataResults[$i]->queryName &&
                $result[$k]['page'] == $queryMetadataResults[$i]->usedInArticle &&
                $result[$k]['name'] == $queryString)
                    continue 2;
        }

        $result[] = array(
            'name' => $queryMetadataResults[$i]->queryName,
            'format' => $queryMetadataResults[$i]->queryPrinter,
            'page' => $queryMetadataResults[$i]->usedInArticle,
            'query' => $queryString,
        );
    }
    return $result;
}

//Function to parse the http auth header
function qiParseHttpDigest($digest) {
    //Protect against missing data
    $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);

    $data = array();
    $parts = explode(", ", $digest);

    foreach ($parts as $element) {
		$bits = explode("=", $element);
		$data[$bits[0]] = str_replace('"','', $bits[1]);

		unset($needed_parts[$bits[0]]);
    }
    return $needed_parts ? false : $data;
}
