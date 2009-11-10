<?php
/**
 * SMW context sensitive help
 *
 * @author: Stephan Robotta / ontoprise / 2009
 */

// no valid entry point for the extension
if ( !defined( 'MEDIAWIKI' ) ) die;

// Version number of the extension
define('SMW_USER_MANUAL_VERSION', '1.0');

$wgExtensionCredits['other'][] = array(
    'name' => 'SMW User Manual v'.SMW_USER_MANUAL_VERSION,
    'author' => 'Ontoprise',
    'url' => 'http://sourceforge.net/projects/halo-extension/',
    'description' => 'A context sensitive help for SemanticMediaWiki, '.
        'and other semantic extensions. View online documentation in the '.
        '[http://smwforum.ontoprise.com/smwforum SMW+ User Forum].'
);

/**
 * Here you can define a custom namespace name for the context sensitive
 * help articles. This is done, so that no interference with any existing
 * articles might occur.
 * If you don't define anything, the default "UserManual" is used, which
 * is lso language dependent.
 */
$umegNamespace = null;

/**
 * Set here the namespace index from where the two additional namespaces will
 * be added to your wiki. If you don't set any value here, the next available
 * index will be used. The index number must be above 110. All below 100 is for
 * Mediawiki itself reserved and between 100 and 110 the Semantic MediaWiki uses
 * these values.
 */
$umegNamespaceIndex = null;

/**
 * When a help text pops up, the user can send a rating. This rating will be send
 * annonymously back to the SMW+ forum (http://smwforum.ontoprise.com) together
 * with some information about your wiki. All data is treated anonymously and
 * there will be no chance to track back the rating to your wiki. We collect this
 * information to know where the help is used and what users think about the help
 * text. Also bugreports can be send to SMW+.
 * If you not wish, that your wiki sends back any information to the SMW+ forum
 * please set this variable to false. This also makes sence, if you running a
 * cooperate wiki which has no access to the Internet.
 * The default value will be true, i.e. send user rating and bug reports to SMW+
 */
$umegSendFeedbackToSMWplus = true;

/**
 * Trigger here the comment function for the help articles. Feedback from the
 * users helps us to improve the content of the articles. When sending comments
 * these will be stored in the SMW forum (http://smwforum.ontoprise.com) and can
 * be viewed by any visitor of the page. Each comment is flagged with the IP
 * address of the commiter and the time stamp when the comment was send.
 * If you want your users to participate in the community, set the value to true.
 * The default is false, so that no comments will be send.
 */
$umegSendCommentsToSMWplus = false;

/**
 * If the extension is loaded a help link will be displayed. This is done always
 * even if there are no help articles in the wiki, the link is not much of a
 * help. Setting this variable to true will check if there articles and only
 * then the help link will be displayed. This requires a semantic query upon
 * each request. To avoid it, the default value is false.
 */
$umegCheckArticlesOnStartup = false;

/**
 * The popup will be displayed in a certain size. If the content doesn't fit
 * inside, scroll bars will be added to the content window. Define here the
 * width and height of the popup window. Values must be integers (unit will be
 * pixels)
 */
$umegPopupWidth = 400;
$umegPopupHeight = 460;

// SMW is needed, check if it's installed and quit, if this is not the case
if (!defined("SMW_VERSION")) {
	trigger_error("Semantic MediaWiki is required but not installed.");
	die();
}

// define the SMW forum URL to the API to send rating and feedback to the smw forum.
define('SMW_FORUM_API', 'http://smwforum.ontoprise.com/smwforum/api.php');
// define the SMW forum URL to get a normal page (needed to fetch the CSH article list)
define('SMW_FORUM_URL', 'http://smwforum.ontoprise.com/smwforum/index.php');
// Context sensitive help articles are fetched from the SMW forum by this query
define('SMW_FORUM_QUERY_CSH', '[[Category:Context sensitive help article]]');
// Porperty for discourse state
define('SMW_UME_PROPERTY_DISCOURSE_STATE', 'UME discourse state');
// Property for link to real help article
define('SMW_UME_PROPERTY_LINK', 'UME link');
// webserver path to extension
global $wgScriptPath;
define('SMW_UME_PATH', $wgScriptPath.'/extensions/SMWUserManual/');

// require additional files beloning to this extension
require_once(dirname(__FILE__).'/SMW_AjaxAccess.php');

/**
 * enable the user manual extension but here add the setup function to the
 * extension function hooks only. Once Mediawiki is completely setup, the
 * functions in this array will be launched which also sets up our extension
 * 
 * @global Array $wgExtensionFunctions
 */
function enableSMWUserManual() {
    global $wgExtensionFunctions;
    $wgExtensionFunctions[] = 'setupSMWUserManual';
}

/**
 * initializing of the SMW User Manual extension
 *
 * @global Array $wgExtraNamespaces
 * @global Array $smwgNamespacesWithSemanticLinks
 * @global int $umegNamespaceIndex
 * @global Object $umeLang
 * @global string $umegNamespace
 * @global Boolean $umegSendCommentsToSMWplus
 * @global Array $wgHooks
 */
function setupSMWUserManual() {
    global $wgExtraNamespaces, $smwgNamespacesWithSemanticLinks,
        $umegNamespaceIndex, $umeLang, $umegNamespace,
        $umegCheckArticlesOnStartup, $wgHooks;

    wfProfileIn('setupSMWUserManual');

    if ($umegNamespaceIndex == null) {
        $nsKeys = array_keys($wgExtraNamespaces);
        rsort($nsKeys);
        $umegNamespaceIndex = array_shift($nsKeys) + 1;
    }
    // define the ns constants
    define('SMW_NS_USER_MANUAL', $umegNamespaceIndex);
    define('SMW_NS_USER_MANUAL_TALK', $umegNamespaceIndex + 1);

    // init language for name of the new ns
    umefInitLanguage();

    // add the custom ns to the extra namespace array
    if ($umegNamespace == null) {
        $umegNamespace = $umeLang->getNsText(SMW_NS_USER_MANUAL);
        $wgExtraNamespaces[SMW_NS_USER_MANUAL] = $umeLang->getNsText(SMW_NS_USER_MANUAL);
        $wgExtraNamespaces[SMW_NS_USER_MANUAL_TALK] = $umeLang->getNsText(SMW_NS_USER_MANUAL_TALK);
    }
    else {
        $wgExtraNamespaces[SMW_NS_USER_MANUAL] = $umegNamespace;
        $wgExtraNamespaces[SMW_NS_USER_MANUAL_TALK] = $umegNamespace.$umeLang->getTalkSuffix();
    }

    // add the normal UserManual ns to the semantic links array
    $smwgNamespacesWithSemanticLinks[SMW_NS_USER_MANUAL] = true;
    $smwgNamespacesWithSemanticLinks[SMW_NS_USER_MANUAL_TALK] = false;

    // check articles on startup? and add parser hook and javascript for help link
    if ($umegCheckArticlesOnStartup) {
        $res = umefGetHelpArticlePageCount();
        if ($res > 0)
            $wgHooks['BeforePageDisplay'][]='umefAddHtml2Page';
    }
    else
        $wgHooks['BeforePageDisplay'][]='umefAddHtml2Page';
        
    wfProfileOut('setupSMWUserManual');
}


/**
 * add the javasript links to the html header of the page and add the help
 * link to the page content, so that the help link is displayed and the popup
 * with help message appears.
 */
function umefAddHtml2Page(&$out) {
    global $umegSendFeedbackToSMWplus, $umegSendCommentsToSMWplus,
           $umegPopupWidth, $umegPopupHeight;
    $out->addScript('
            <script type="text/javascript" src="'. SMW_UME_PATH .  'scripts/smwCSH.js"></script>');
    if ($umegSendFeedbackToSMWplus) {
        $out->addScript('
            <script type="text/javascript">/*<![CDATA[*/
                var umegSmwForumUrl = "'.SMW_FORUM_URL.'";
                var umegSendCommentsToSMWplus = '.($umegSendCommentsToSMWplus ? 'true' : 'false').'
                var umegPopupWidth = '.$umegPopupWidth.'
                var umegPopupHeight = '.$umegPopupHeight.'
            /*]]>*/</script>
        ');
        $out->addLink(array(
            'rel'   => 'stylesheet',
            'type'  => 'text/css',
            'media' => 'screen, projection',
            'href'  => SMW_UME_PATH . 'skins/csh.css'
        ));

    }
    $out->addHTML(umefDivBox().'
        <script type="text/javascript">/*<![CDATA[*/
        var smwCsh = new SMW_UserManual_CSH("'.wfMsg('smw_ume_help_link').'");
        /*]]>*/</script>
    ');
    return true;
}

/**
 * init language for User Manual extension
 *
 * @global string $wgLanguageCode
 * @global SMW_UMLanguageEn $umeLang (or any other available language class)
 * @global Object $wgMessageCache
 */
function umefInitLanguage() {
    global $wgLanguageCode, $umeLang, $wgMessageCache;
    wfProfileIn('umefInitLanguage');

    $className = 'SMW_UMLanguage'.str_replace('-', '_', ucfirst($wgLanguageCode));
    $langFile = dirname(__FILE__).'/../languages/'.$className.'.php';
    if (file_exists($langFile))
        require_once($langFile);
    else {
        require_once(dirname(__FILE__).'/../languages/UserManualLanguageEn.php');
        $className = 'SMW_UMLanguageEn';
    }

    $umeLang = new $className;

    $wgMessageCache->addMessages($umeLang->getTexts(), $wgLanguageCode);
    wfProfileOut('umefInitLanguage');
}

/**
 * Check if ther are any help articles in the wiki
 *
 * @global string $umegNamespace
 * @return int number of articles
 */
function umefGetHelpArticlePageCount() {
    global $umegNamespace;

    wfProfileIn('umefGetHelpArticlePageCount');
    // check if there are CSH article, these must be in our
    // namsespace and the property UME disource state must be set
    $query = '[['.$umegNamespace.':+]][['.SMW_UME_PROPERTY_DISCOURSE_STATE.'::+]]';
	$fixparams = array( "format" => "count" );
    $result = SMWQueryProcessor::getResultFromQueryString($query, $fixparams, array(), SMW_OUTPUT_WIKI);
    wfProfileOut('umefGetHelpArticlePageCount');
    return intval($result);
}

function umefDivBox() {
    global $umegPopupWidth, $umegPopupHeight;
    $closeImage = SMW_UME_PATH.'skins/close.gif';
    $loadImage= SMW_UME_PATH.'skins/load.gif';
    return '<div id="smw_csh_popup" style="position:fixed;width:'.$umegPopupWidth.'px;height:'.$umegPopupHeight.'px;left:250px;top:150px;visibility:hidden">
            <table border="0" style="width:100%; height:100%" bgcolor="#000080" cellspacing="0" cellpadding="2">
            <tr><td width="100%">
            <table style="border:0px; width:100%; height:100%;" cellspacing="0" cellpadding="0">
            <tr>
            <td id="smw_csh_dragbar" style="cursor:move" width="100%">
            <ilayer width="100%" onSelectStart="return false">
            <layer width="100%">
            <font color="#FFFFFF">'.wfMsg('smw_ume_box_headline').'</font>
            </layer></ilayer></td>
            <td style="cursor:hand; cursor:pointer; vertical-align:middle"><a onclick="smwCsh.closeBox();return false" href="#"><img src="'.$closeImage.'" border="0"></a></td>
            </tr>
            <tr style="width:100%; height:100%;">
            <td bgcolor="#CBCBCB" style="width:100%; height:100%; padding:4px; vertical-align:top" colspan="2">
            <table style="width:100%; height:100%; border-collapse:collapse;empty-cells:show">
            <tr>
            <td class="cshTabSpacer">&nbsp;&nbsp;</td><td class="cshTabActive" onclick="smwCsh.switchTab(this);">'.wfMsg('smw_ume_tab_help').'</td>
            <td class="cshTabSpacer">&nbsp;&nbsp;</td><td class="cshTabInactive" onclick="smwCsh.switchTab(this);">'.wfMsg('smw_ume_tab_feedback').'</td>
            <td class="cshTabSpacer" width="100%"></td>
            </tr>
            <tr><td colspan="5" style="width:100%; height:100%" class="cshTabCont">
            <span>
            <span class="cshHeadline">'.wfMsg('smw_ume_cpt_headline_1').'</span>
            <div id="smw_csh_selection">
            <span style="display:block; text-align:center;"><img src="'.$loadImage.'" alt="load"/></span>
            </div>
            <hr style="height: 2px; dashed;" />
            <div id="smw_csh_content_head"></div>
            <div id="smw_csh_content"></div>
            <div id="smw_csh_link_to_smw"></div>
            '.umefDivBoxRating().'
            </span>
            <span style="display:none">
            '.umefDivBoxFeedback().'
            </span>
            </td></tr></table>
            </td></tr>
            </table></td></tr></table></div>';
}
function umefDivBoxRating() {
    global $umegSendFeedbackToSMWplus, $umegSendCommentsToSMWplus;
    if (!$umegSendFeedbackToSMWplus) return '';
    $imgPath = SMW_UME_PATH.'skins/';
    return '<div id="smw_csh_rating"><span onclick="smwCsh.openRatingBox()" style="cursor:pointer;cursor:hand"><img src="'.$imgPath.'right.png"/>
            '.wfMsg('smw_ume_did_it_help').'</span>
            <input type="radio" name="smw_csh_did_it_help" value="1" onchange="smwCsh.openRatingBox()"/>'.wfMsg('smw_ume_yes').'
            <input type="radio" name="smw_csh_did_it_help" value="0" onchange="smwCsh.openRatingBox()"/>'.wfMsg('smw_ume_no').'
            <div id="smw_csh_rating_box" style="display:none">
            <textarea width="100%" rows="3"></textarea>
            <input type="submit" value="'.wfMsg('smw_ume_reset').'" onclick="smwCsh.resetRating()">
            <input type="submit" value="'.wfMsg('smw_ume_submit_feedback').'" onclick="smwCsh.sendRating()" style="text-align:right">
            </div>
            </div>
    ';
}

function umefDivBoxFeedback() {
    global $umegSendCommentsToSMWplus;
    $imgPath = SMW_UME_PATH.'skins/';
    if (!$umegSendCommentsToSMWplus) return '';
    return '<div id="smw_csh_feedback">
            <span class="cshHeadline">'.wfMsg('smw_ume_cpt_headline_2').'</span>
            <table class="cshFeedbackFrame">
            <tr onclick="smwCsh.openCommentBox(this)"><td>
            <img src="'.$imgPath.'right.png"/>
            '.wfMsg('smw_ume_ask_your_own_q').'
            <img src="'.$imgPath.'question.png" align="right"/>
            </td></tr></table>
            <table class="cshFeedbackFrame">
            <tr onclick="smwCsh.openCommentBox(this)"><td>
            <img src="'.$imgPath.'right.png"/>
            '.wfMsg('smw_ume_add_comment').'
            <img src="'.$imgPath.'comment.png" align="right"/>
            </td></tr></table>
            <table class="cshFeedbackFrame">
            <tr onclick="smwCsh.openCommentBox(this)"><td>
            <img src="'.$imgPath.'right.png"/>
            '.wfMsg('smw_ume_bug_discovered').'
            <img src="'.$imgPath.'bug.png" align="right"/>
            </td></tr></table>
            </div>
    ';
}

?>
