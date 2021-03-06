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

/**
 * Ultrapedia provenance rating
 *
 * @file
 * @ingroup Ultrapedia
 * @author: Stephan Robotta
 */

// no valid entry point for the extension
if ( !defined( 'MEDIAWIKI' ) ) die;

// Version number of the extension
define('SMW_UP_RATING_VERSION', '1.1');

$wgExtensionCredits['other'][] = array(
    'name' => 'SMW Ultrapedia Rating',
	'version'=>SMW_UP_RATING_VERSION,
	'author'=>"Maintained by [http://smwplus.com ontoprise GmbH].", 
	'url' => 'http://sourceforge.net/projects/halo-extension/',
    'description' => 'Rate data and send feedback to UP'.
        'and Wikipedia for improvements'
);

global $wgServer, $wgScriptPath;

/**
 * Set here the namespace index from where the two additional namespaces will
 * be added to your wiki. If you don't set any value here, the next available
 * index will be used. The index number must be above 110. All below 100 is for
 * Mediawiki itself reserved and between 100 and 110 the Semantic MediaWiki uses
 * these values.
 * All rating commentis within UP get added in this extra namespace.
 */
$uprgNamespaceIndex = null;

/**
 * The popup will be displayed in a certain size. If the content doesn't fit
 * inside, scroll bars will be added to the content window. Define here the
 * width and height of the popup window. Values must be integers (unit will be
 * pixels)
 */
$uprgPopupWidth = 400;
$uprgPopupHeight = 460;

/**
 * Define here the Ultrapedia URL for the API access. This is usually the
 * server where this wiki is running on. Therefore it's set to the variables
 * of $wgServer and $wgScriptPath. However this can be changed in the
 * LocalSettings.php if the ultrpedia server is not this wiki. 
 */
$uprgUltrapediaAPI=$wgServer.$wgScriptPath.'/api.php';

/**
 * Define here the Wikipedia URL for the API access. This is not set by
 * default and and must be defined in the LocalSettings.php. For testing
 * purposes you can define a separate URL. All comments that usually will be
 * added to the articles talk page will then be stored in this testwiki and
 * not in Wikipedia itself. 
 */
$uprgWikipediaAPI='';

// SMW is needed, check if it's installed and quit, if this is not the case
if (!defined("SMW_VERSION")) {
	trigger_error("Semantic MediaWiki is required but not installed.");
	die();
}

// webserver path to extension
define('SMW_UP_RATING_PATH', $wgScriptPath.'/extensions/SMWUserManual');

// namespace name for user rating articles
define('SMW_UP_RATING_NSNAME', 'Rating');
define('SMW_UP_RATING_NSNAME_TALK', 'Rating_talk');
// properties used on Rating: pages
define('SMW_UP_RATING_PROP_REFPAGE', 'Referring page');
define('SMW_UP_RATING_PROP_REFSEC', 'Referring section');
define('SMW_UP_RATING_PROP_TABLE', 'Table');
define('SMW_UP_RATING_PROP_CELL', 'Cell');
define('SMW_UP_RATING_PROP_RATING', 'Rating');

// register Ajax functions (these are below in this file)
$wgUseAjax = true;
$wgAjaxExportList[] = 'wfUpGetTableRating';
$wgAjaxExportList[] = 'wfUpGetCellRating';

/**
 * enable the user manual extension but here add the setup function to the
 * extension function hooks only. Once Mediawiki is completely setup, the
 * functions in this array will be launched which also sets up our extension
 * 
 * @global Array $wgExtensionFunctions
 */
function enableUpRating() {
    global $wgExtensionFunctions;
    $wgExtensionFunctions[] = 'setupUpRating';
    initUpRatingNamespaces();
}

/**
 * initializing of the Ultrapedia Rating functionality
 *
 * @global Array $wgHooks
 */
function setupUpRating() {
    global $wgHooks;

    wfProfileIn(__FUNCTION__);

    $wgHooks['BeforePageDisplay'][]='uprfAddHtml2Page';
    $wgHooks['ParserAfterTidy'][] = 'uprfCreateLinks';

    // language
    require_once(dirname(__FILE__).'/../languages/SMW_UpRating.php');
    // MW API forwarder 
    require_once(dirname(__FILE__).'/SMW_MwApiForward.php');
    wfProfileOut(__FUNCTION__);
}

/**
 * Extract namespace initialization out of setup function. This must be done
 * at include time, otherwise the namespaces are not known to the Mediawiki API.
 *
 * @global Array $wgExtraNamespaces
 * @global Array $wgNamespaceAliases
 * @global Array $smwgNamespacesWithSemanticLinks
 * @global int $uprgNamespaceIndex
 */
function initUpRatingNamespaces() {
    global $wgExtraNamespaces, $wgNamespaceAliases,
           $smwgNamespacesWithSemanticLinks, $uprgNamespaceIndex;

    if ($uprgNamespaceIndex == null) {
        $nsKeys = array_keys($wgExtraNamespaces);
        rsort($nsKeys);
        $uprgNamespaceIndex = array_shift($nsKeys) + 1;
        if ($uprgNamespaceIndex % 2)
            $uprgNamespaceIndex++;
    }

    // define the ns constants
    define('NS_UP_RATING', $uprgNamespaceIndex);
    define('NS_UP_RATING_TALK', $uprgNamespaceIndex + 1);

    // add the custom ns to the extra namespace array
    $wgExtraNamespaces[NS_UP_RATING] = SMW_UP_RATING_NSNAME;
    $wgExtraNamespaces[NS_UP_RATING_TALK] = SMW_UP_RATING_NSNAME_TALK;
    $wgNamespaceAliases[SMW_UP_RATING_NSNAME]= NS_UP_RATING;
    $wgNamespaceAliases[SMW_UP_RATING_NSNAME_TALK]= NS_UP_RATING_TALK;

    // add the normal UserManual ns to the semantic links array
    $smwgNamespacesWithSemanticLinks[NS_UP_RATING] = true;
    $smwgNamespacesWithSemanticLinks[NS_UP_RATING_TALK] = false;

}


/**
 * add the javasript links to the html header of the page and add the help
 * link to the page content, so that the help link is displayed and the popup
 * with help message appears.
 */
function uprfAddHtml2Page(&$out) {
    global $uprgPopupWidth, $uprgPopupHeight, $wgLanguageCode, 
           $uprgUltrapediaAPI, $uprgWikipediaAPI;

    // dimension of popup
    $out->addScript('
        <script type="text/javascript">/*<![CDATA[*/
            var uprgPopupWidth = '.$uprgPopupWidth.'
            var uprgPopupHeight = '.$uprgPopupHeight.'
            var uprgUltrapediaAPI= "'.$uprgUltrapediaAPI.'"
            var uprgWikipediaAPI = "'.$uprgWikipediaAPI.'"
            var uprgRatingNamespace = "'.SMW_UP_RATING_NSNAME.'"
            var uprgPropertyReferingPage = "'.SMW_UP_RATING_PROP_REFPAGE.'"
            var uprgPropertyReferingSection = "'.SMW_UP_RATING_PROP_REFSEC.'"
            var uprgPropertyTable= "'.SMW_UP_RATING_PROP_TABLE.'"
            var uprgPropertyCell= "'.SMW_UP_RATING_PROP_CELL.'"
            var uprgPropertyRating= "'.SMW_UP_RATING_PROP_RATING.'"
        /*]]>*/</script>
    ');
    
    // determine language file
    $jsfname = '/scripts/language/up_'.strtolower($wgLanguageCode).'.js';
    if (!file_exists(dirname(__FILE__).'/..'.$jsfname))
        $jsfname = '/scripts/languages/up_en.js';

    // include popup logic and libraries
    $out->addScript('
            <script type="text/javascript">/*<![CDATA[*/
                var DND_POPUP_DIR = "'.SMW_UP_RATING_PATH.'";
            /*]]>*/</script>
            <script type="text/javascript" src="'. SMW_UP_RATING_PATH .  '/scripts/DndPopup.js"></script>
            <script type="text/javascript" src="'. SMW_UP_RATING_PATH .  '/scripts/mwapi.js"></script>
            <script type="text/javascript" src="'. SMW_UP_RATING_PATH . $jsfname.'"></script>
            <script type="text/javascript" src="'. SMW_UP_RATING_PATH .  '/scripts/up.js"></script>
    ');

    $out->addLink(array(
        'rel'   => 'stylesheet',
        'type'  => 'text/css',
        'media' => 'screen, projection',
        'href'  => SMW_UP_RATING_PATH . '/skins/usermanual_up.css'
    ));

    return true;
}

function uprfCreateLinks(&$parser, &$text) {
    $img = ' <img src="'.SMW_UP_RATING_PATH.'/skins/note_green.png" style="cursor: pointer;" onclick="uprgPopup.cellRating(this, \'$1\')" />';
    $text = preg_replace('/UpRatingCell___(.*?)___lleCgnitaRpU/', $img, $text);
    $link = '<a href="#" onclick="uprgPopup.tableRating($1); return false">'.wfMsg('smw_upr_rate_table_link')."</a>";
    $text = preg_replace('/UpRatingTable___(\d+)___elbaTgnitaRpU/', $link, $text);

    return true;
}

function wfUpGetTableRating() {
    $params = func_get_args();
    if (count($params) != 2) return;
    $result = wfUpFetchRatingData($params[0], $params[1]);
    return json_encode(array('html' => $result));
}
function wfUpGetCellRating() {
    $params = func_get_args();
    if (count($params) != 3) return;
    $result = wfUpFetchRatingData($params[0], $params[1], $params[2]);
    return json_encode(array('html' => $result));
}

function wfUpFetchRatingData($page, $table, $cell='') {
    global $wgParser, $wgTitle, $wgUser, $wgLang;
    $query= '[['.SMW_UP_RATING_NSNAME.':+]]'
           .'[['.SMW_UP_RATING_PROP_REFPAGE.'::'.$page.']]'
           .'[['.SMW_UP_RATING_PROP_TABLE.'::'.$table.']]';
    if (strlen($cell) > 0)
        $query.='[['.SMW_UP_RATING_PROP_CELL.'::'.$cell.']]';
    $printout= array(
        new SMWPrintRequest(SMWPrintRequest::PRINT_PROP, "", SMWPropertyValue::makeUserProperty(SMW_UP_RATING_PROP_RATING)),
        new SMWPrintRequest(SMWPrintRequest::PRINT_PROP, "", SMWPropertyValue::makeUserProperty(SMW_UP_RATING_PROP_CELL))
    );
    // run the query now
	$fixparams = array(
            "format" => "table",
            "link" => "none"
	);
    $result = SMWQueryProcessor::getResultFromQueryString($query, $fixparams, $printout, SMW_OUTPUT_WIKI);
    if (strlen($result) == 0)
        return;
    // take the returned html and parse it to create the triplet of page, rating and cell
    $domDocument = new DOMDocument();
    $success = $domDocument->loadXML($result);
    $domXPath = new DOMXPath($domDocument);
    $nodes = $domXPath->query('//td');
    $pagesTable=array();
    $pagesCell=array();
    $page=array();
    $cnt = 0;
    foreach ($nodes AS $node) {
        $page[] = trim($node->nodeValue);
        if ($cnt == 2) {
            $cnt = 0;
            if (strlen($page[2]) == 0)
                $pagesTable[]= $page;
            else
                $pagesCell[]= $page;
            $page = array();
        }
        else $cnt++;
    }
    // merge comments for complete table and for data cells
    $pages = array_merge($pagesTable, $pagesCell);

    // create a parser object and use the options from wgUser
    if (is_object($wgParser)) $psr =& $wgParser; else $psr = new Parser;
    $opt = ParserOptions::newFromUser($wgUser);
    // flush result and save here the complete html that will be returned
    $result= '';
    
    // The headline above the comments. If the popup asks for no cell in
    // specific, then we have comments for the table followed by comments for
    // inividual data cells.
    $com4table = count($pagesTable);
    if ( strlen($cell) == 0 && $com4table > 0) {
        $result.= $com4table == 1
            ? wfMsg('smw_upr_comm_for_table')
            : sprintf(wfMsg('smw_upr_comms_for_table'), $com4table);
        $result.= "<br/>";
    }
    // otherwise popup asks for comments on individual data cells only
    else if ( strlen($cell) > 0 ) {
        $result.= count($pagesCell) == 1
            ? wfMsg('smw_upr_comm_for_cell')
            : sprintf(wfMsg('smw_upr_comms_for_cell'), count($pagesCell));
        $result.= "<br/>";
    }

    // iterate over the triples from above (page, Rating and cell)
    for ($i = 0; $i < count($pages); $i++) {
        // the first value is the page name incl NS prefix, remove the NS
        $page = substr($pages[$i][0], strlen(SMW_UP_RATING_NSNAME) + 1);
        // create title object, and parse article content
        $t = Title::newFromText($page, NS_UP_RATING);
        if (!$t) continue;
        $a = new Article($t);
        if (!$a) continue;
        $out = $psr->parse($a->getRawText(), $wgTitle, $opt, true, true);
        // get user that has been the last editor of the text, this is
        // usually the one that created the comment and timestamp of article
        $usrAndTime= $a->getUserText().', '.$wgLang->timeanddate($a->getTimestamp(), true);
        // get rating property
        $rating= wfMsg('smw_upr_data').':<i>'
                . ((strlen($pages[$i][2])>0) ? $pages[$i][2] : wfMsg('smw_upr_complete_table'))
                . '</i> | <i>'
                . ((strtolower($pages[$i][1]) == "false") ? wfMsg('smw_upr_data_invalid') : wfMsg('smw_upr_data_correct'))
                . '</i>';
        // if the popup asks for comments on the complete table, then we must check
        // if here we are getting to the comments for individual cells and add the headline
        if (strlen($cell) == 0 && $i == $com4table) {
            $result .= count($pagesCell) == 1
                ? wfMsg('smw_upr_comm_for_data')
                : sprintf(wfMsg('smw_upr_comms_for_data'), count($pagesCell));
            $result.= "<br/>";
        }
        // build complete comment box
        $result.= '<div class="uprComment"><b>'.$usrAndTime.'</b>'
                . '<span>'.$rating.'</span>'
                . '<br/>'.strip_tags($out->getText())."</div>\n";
    }
    return $result;
}
?>
