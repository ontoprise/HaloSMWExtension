<?php
/**
 * A special page for the context sensitive help system of Semantic MediaWiki
 *
 * The special page can be called with or without parameters. If there are no paramteres,
 * it will simply show a list of all available help pages. If there are paramters, they
 * will serve in order to narrow down results. All help pages are categorized with a
 * discourse state which consists of the namespace and the action which belong to the
 * question. A question can have more than one discourse states as some questions might
 * appear in different situations.
 * Users can also add their own new help questions. The discourse state will then be generated
 * automatically. This function is integrated in the Semantic Toolbar and
 * SMW_ToolbarFunctions.php and is only availabe if the 'ontoskin'-skin is enabled.
 *
 * @author Markus Nitsche
 */

 if (!defined('MEDIAWIKI')) die();



global $IP;
require_once( $IP . "/includes/SpecialPage.php" );

/*// standard functions for creating a new special page
function doSMWHelpSpecial()  {
        SMWHelpSpecial::execute();
}

SpecialPage::addPage( new SpecialPage('ContextSensitiveHelp','',true,'doSMWHelpSpecial',false)) ;
*/

/*
 * Standard class that is resopnsible for the creation of the Special Page
 */
class SMWHelpSpecial extends SpecialPage {
    public function __construct() {
        parent::__construct('ContextSensitiveHelp');
    }
/*
 * Overloaded function that is resopnsible for the creation of the Special Page
 */
    public function execute() {

        global $wgRequest, $wgOut;

        $wgOut->setPageTitle(wfMsg('contextsensitivehelp'));
        /*
         * First, get all parameters. If a user came here using a link from the
         * Semantic Toolbar, there will be a paramter restriction and discourseState.
         * If the user comes from the same page (that is, when he refined his search),
         * helpns and helpaction are used
         */
        $restriction = $wgRequest->getVal( 'restriction' );
        $discourseState = $wgRequest->getVal( 'ds' );
        $ns = $wgRequest->getVal( 'helpns' );
        $action = $wgRequest->getVal( 'helpaction' );

        if($ns != '' && $action != '') { //User came from the same page. Check what he looks for
            $discourseState = "$ns:$action";
            if ($ns == 'ALL' && $action == 'ALL'){
                $restriction = 'none';
            }
            else if ($ns == 'ALL') {
                $restriction = 'action';
            }
            else if ($action == 'ALL') {
                $restriction = 'ns';
            }
            else {
                $restriction = 'all';
            }
        }

        $html = "<h2>Looking for help?</h2>";
        $html .= createHelpSelector();

        if($restriction != '' && $discourseState != ''){ //User came rather from the same page or from outside.Parameters are given, so a restriction exists
            $values = split(":", $discourseState);
            switch($restriction){
                case "none":
                    $html .= "<h2>Here you can see all help questions that are currently in the system</h2>";
                    break;
                case "all":
                    $html .= "<h2>Here you can see all help questions that belong to action " . $values[1] . " and the namespace " . $values[0] . "</h2>";
                    break;
                case "ns":
                    $html .= "<h2>Here you can see all help questions that belong to the namespace " . $values[0] . "</h2>";
                    $discourseState = $values[0];
                    break;
                case "action":
                    $html .= "<h2>Here you can see all help questions that belong to action " . $values[1] . "</h2>";
                    $discourseState = $values[1];
                    break;
                default:
                    break;
            }
            $html .= getHelpByRestriction($restriction, $discourseState);
            if ($restriction != 'none'){
                $specialTitle = Title::newFromText('ContextSensitiveHelp', NS_SPECIAL);
                $html .= '<br/><a href="' . $specialTitle->getFullURL() . '">Need more help?</a><br/>';
            }
        }
        else { // no restriction. show all help pages
            $html .= "<h2>Here you can see all help questions that are currently in the system</h2>";
            $html .= getHelpByRestriction('none', '');
        }
        $wgOut->addHTML($html);
    }

}
/**
 * function getHelpByRestriction
 * Looks up help pages in the database. Relevant pages are selected by a restriction
 * and a paramter
 * @param $restriction Which part of the discourse state is relevant? Namespace, action, both or none
 * @param $param If there is a restriction, this will contain the discourseState or its relevant part
 * @return $html the html string containing all links to relevant helppages
 */
function getHelpByRestriction($restriction, $param){
    $help = array();
    $html = '';
    
    $hs_storage = HelpSpecialStorage::getHelpSpecialStorage();
    $helppages = $hs_storage->getHelppagesByRestriction($restriction, $param);

    foreach($helppages as $id){
        $questions = $hs_storage->getQuestions($id);
        //get questions
        
        if(is_array($questions)){
            $results = true;
            $wikiTitle = Title::newFromText($questions[0], NS_HELP);
            if($wikiTitle instanceof Title && $wikiTitle->exists()){
                $link = '<a id="' . $questions[1] . '"href="' . $wikiTitle->getFullURL();
                if($questions[2] == wfMsg('smw_csh_newquestion')){
                    $link .= '?action=edit" class="new';
                }
                $link .= '" ';
                $link .= 'title="' . $questions[2] . '">' . $questions[1] . '?</a><br/>';
                array_push($help, $link); // saved in an array first so they can be alphabetically ordered later (id)
            }
        }
    }
    if (sizeof($help)==0){
            $html = "Sorry, there are no questions in this section yet.<br/>";
    }
    else {
        $help = array_unique($help);
        asort($help);
        
        foreach($help as $link){
            $html .= $link;
        }
    }
    return $html;
}

/**
 * function createHelpSelector
 * A simple function which creates a selector with which users can refine their search
 * for help pages.
 * @return $html html of selector
 */
function createHelpSelector(){
    global $wgCanonicalNamespaceNames, $smwgContLang;
    $actualNamespaces = $wgCanonicalNamespaceNames;
    $specialTitle = Title::newFromText('ContextSensitiveHelp', NS_SPECIAL);

    // Add 'ALL' and NS_MAIN. All is only used for this special page. NS_MAIN does not have a canonical name
    array_push($actualNamespaces, wfmsg('smw_csh_ns_main')); // =NS_MAIN
    array_push($actualNamespaces, wfmsg('smw_csh_all'));
    asort($actualNamespaces);

    //all relevant actions
    $actions = array('ALL', 'view', 'edit', 'move', 'delete', 'history');

    $html = '<form action="' . $specialTitle->getFullURL() . '" method="get">' . wfmsg('smw_csh_refine_search_info');
    $html .= '<input type="hidden" name="title" value="' . $specialTitle->getPrefixedText() . '"/>';
    $html .= '<blockquote>' . wfmsg('smw_csh_page_type') . '&nbsp;';
    $html .= '<select name="helpns" size="1" style="vertical-align:middle;">';
    foreach($actualNamespaces as $id => $name){
        if($name == wfmsg('smw_csh_ns_main')){
            $html.= '<option value="Main">' . $name . '</option>'; //discourseState is encoded with 'Main', not with 'Regular wiki page'
        }
        else {
            $html.= '<option>' . $name . '</option>';
        }
    }
    $html .= '</select>';
    $html .= '&nbsp;&nbsp;&nbsp;' . wfmsg('smw_csh_action') . ':&nbsp;';
    $html .= '<select name="helpaction" size="1" style="vertical-align:middle;">';

    foreach($actions as $action){
        $html.= '<option>' . $action . '</option>';
    }
    $html .= '</select>&nbsp;&nbsp;&nbsp;';
    $html .= '<input type="submit" value=" Go "></blockquote></form><br/>';

//------------------------------------------------------------

    $smwns = $smwgContLang->getNamespaces();
    $specials = array( $wgCanonicalNamespaceNames[NS_CATEGORY], $smwns[SMW_NS_PROPERTY], wfmsg('smw_csh_mediawiki'), wfmsg('smw_contextsensitivehelp'), wfmsg('smw_queryinterface'), wfmsg('ontologybrowser'), wfmsg('smw_combined_search'), );

    $html .= '<form action="' . $specialTitle->getFullURL() . '" method="get">' . wfmsg('smw_csh_search_special_help');
    $html .= '<blockquote>' . wfmsg('smw_csh_show_special_help') . '&nbsp;';
    $html .= '<select name="helpns" size="1" style="vertical-align:middle;">';
    $html .= '<option value="' . $wgCanonicalNamespaceNames[NS_CATEGORY] . '">' . wfmsg('smw_csh_categories') . '</option>';
    $html .= '<option value="' . $smwns[SMW_NS_PROPERTY] . '">' . wfmsg('smw_csh_properties') . '</option>';
    $html .= '<option value="mediawiki">' . wfmsg('smw_csh_mediawiki') . '</option>';
    $html .= '<option value="' . wfmsg('smw_csh_ds_queryinterface') . '">' . wfmsg('smw_queryinterface') . '</option>';
    $html .= '<option value="' . wfmsg('smw_csh_ds_ontologybrowser') . '">' . wfmsg('ontologybrowser') . '</option>';
    $html .= '<option value="' . wfmsg('smw_csh_ds_combinedsearch') . '">' . wfmsg('smw_combined_search') . '</option>';
    $html .= '</select>';
    $html .= '<input type="hidden" name="helpaction" value="ALL"/>';
    $html .= '&nbsp;&nbsp;&nbsp;<input type="submit" value=" Go "></blockquote></form><br/>';

    return $html;
}

abstract class HelpSpecialStorage {
    private static $INSTANCE = NULL;
    
    public static function getHelpSpecialStorage() {
        global $smwgBaseStore;
        if (self::$INSTANCE == NULL) {
            switch ($smwgBaseStore) {
                case (SMW_STORE_TESTING):
                    self::$INSTANCE = NULL; // not implemented yet
                    trigger_error('Testing stores not implemented for HALO extension.');
                break;
                case ('SMWHaloStore2'): default:
                    self::$INSTANCE = new HelpSpecialStorageSQL2();
                break;
                case ('SMWHaloStore'): default:
                    self::$INSTANCE = new HelpSpecialStorageSQL();
                break;
            }
        }
        return self::$INSTANCE;
    }
    
    /**
     * TODO: Write documentation
     */
    public abstract function getHelppagesByRestriction($restriction, $param);
    public abstract function getQuestions($id);
}

class HelpSpecialStorageSQL extends HelpSpecialStorage {
    public function getHelppagesByRestriction($restriction, $param) {
        $helppages = array();
        $dbr =& wfGetDB( DB_SLAVE );
        if($restriction == "ns" && $param == "mediawiki"){
            $res = $dbr->query('SELECT * FROM smw_attributes WHERE attribute_title = "HelppageComponent" AND subject_namespace = "' . NS_HELP . '"');
            while ( $row = $dbr->fetchObject( $res ) ) {
                if ($row->value_xsd == "MediaWiki")
                    $helppages[] = $row->subject_id;
            }
        } else {
        $res = $dbr->query('SELECT * FROM smw_attributes WHERE attribute_title = "DiscourseState" AND subject_namespace = "' . NS_HELP . '"');
    
        //First, find all pages that are relevant and save their id
        if ($dbr->numRows($res) > 0){
            switch($restriction) {
                case "none":
                    while ( $row = $dbr->fetchObject( $res ) ) {
                        $helppages[] = $row->subject_id;
                    }
                    break;
                case "all":
                    while ( $row = $dbr->fetchObject( $res ) ) {
                        if ($row->value_xsd == $param){
                            $helppages[] = $row->subject_id;
                        }
                    }
                    break;
                case "ns":
                    while ( $row = $dbr->fetchObject( $res ) ) {
                        if (strpos($row->value_xsd, $param)===0){
                            $helppages[] = $row->subject_id;
                        }
                    }
                    break;
                case "action":
                    while ( $row = $dbr->fetchObject( $res ) ) {
                        if (strpos($row->value_xsd, $param)!==false){
                            $helppages[] = $row->subject_id;
                        }
                    }
                    break;
            }
            $dbr->freeResult( $res );
        }
        }
        return $helppages;
    }
    public function getQuestions($id){
        $dbr =& wfGetDB( DB_SLAVE );
        $res = $dbr->select( $dbr->tableName('smw_attributes'),
            '*',
            array('subject_id =' . $id, 'attribute_title="Question"'));
        if ($dbr->numRows($res) > 0){
            $results = true;
            $row = $dbr->fetchObject( $res );
            $title = $row->subject_title;
            $question = htmlspecialchars($row->value_xsd);
            $dbr->freeResult( $res );
            
            $res = $dbr->select( $dbr->tableName('smw_longstrings'),
                '*',
                array('subject_id =' . $id, 'attribute_title="Description"'));
            
            $description = '';
            
            if ($dbr->numRows($res) > 0){
                $row = $dbr->fetchObject( $res );
                $description = htmlspecialchars($row->value_blob);
            }
            $dbr->freeResult( $res );
            return array($title, $question, $description);
        }
        $dbr->freeResult( $res );
        return 0;
    }
}

class HelpSpecialStorageSQL2 extends HelpSpecialStorageSQL {
public function getHelppagesByRestriction($restriction, $param) {
        $helppages = array();
        $db =& wfGetDB( DB_SLAVE );
        
        $smw_ids = $db->tableName('smw_ids');
        $smw_atts2 = $db->tableName('smw_atts2');     
        $page = $db->tableName('page');
        
                
        if($restriction == "ns" && $param == "mediawiki"){
            
            $discourseStateID = $db->selectRow($smw_ids, array('smw_id'), array('smw_title'=>'HelppageComponent', 'smw_namespace' => SMW_NS_PROPERTY));
            if ($discourseStateID != null) {
                $discourseStateID = $discourseStateID->smw_id;
            } else return array();
            
            $res = $db->query('SELECT page_id, value_xsd FROM '.$smw_atts2.
            ' JOIN '.$smw_ids.' ON smw_id = s_id '.
            ' JOIN '.$page.' ON page_title = smw_title AND page_namespace = smw_namespace '.
            ' WHERE p_id = '.$discourseStateID. ' AND page_namespace = ' . NS_HELP);
        
            while ( $row = $db->fetchObject( $res ) ) {
                if ($row->value_xsd == "MediaWiki")
                    $helppages[] = $row->page_id;
            }
        } else {
            $discourseStateID = $db->selectRow($smw_ids, array('smw_id'), array('smw_title'=>'DiscourseState', 'smw_namespace' => SMW_NS_PROPERTY));
            if ($discourseStateID != null) {
                $discourseStateID = $discourseStateID->smw_id;
            } else return array();
           
            $res = $db->query('SELECT page_id, value_xsd FROM '.$smw_atts2.
                ' JOIN '.$smw_ids.' ON smw_id = s_id '.
                ' JOIN '.$page.' ON page_title = smw_title AND page_namespace = smw_namespace '.
                ' WHERE p_id = '.$discourseStateID. ' AND page_namespace = ' . NS_HELP);
        
    
            //First, find all pages that are relevant and save their id
            if ($db->numRows($res) > 0){
                switch($restriction) {
                    case "none":
                        while ( $row = $db->fetchObject( $res ) ) {
                            $helppages[] = $row->page_id;
                        }
                        break;
                    case "all":
                        while ( $row = $db->fetchObject( $res ) ) {
                            if ($row->value_xsd == $param){
                                $helppages[] = $row->page_id;
                            }
                        }
                        break;
                    case "ns":
                        while ( $row = $db->fetchObject( $res ) ) {
                            if (strpos($row->value_xsd, $param)===0){
                                $helppages[] = $row->page_id;
                            }
                        }
                        break;
                    case "action":
                        while ( $row = $db->fetchObject( $res ) ) {
                            if (strpos($row->value_xsd, $param)!==false){
                                $helppages[] = $row->page_id;
                            }
                        }
                        break;
                }
                $db->freeResult( $res );
            }
        }
        return $helppages;
    }
    
    public function getQuestions($id){
        $db =& wfGetDB( DB_SLAVE );
        
        $smw_ids = $db->tableName('smw_ids');
        $smw_atts2 = $db->tableName('smw_atts2');     
        $smw_text2 = $db->tableName('smw_text2');
        $page = $db->tableName('page');
      
        $res = $db->query('SELECT i.smw_title AS subject_title, value_xsd FROM '.$smw_ids.' i '.
                            ' JOIN '.$smw_atts2.' ON i.smw_id = s_id '.
                            ' JOIN '.$smw_ids.' i2 ON i2.smw_id = p_id '.
                            ' JOIN '.$page.' p ON p.page_title = i.smw_title AND p.page_namespace = i.smw_namespace'.
                            ' WHERE p.page_id = '.$id.' AND i2.smw_title = "Question" AND i2.smw_namespace = '.SMW_NS_PROPERTY);
        
        if ($db->numRows($res) > 0){
            $results = true;
            $row = $db->fetchObject( $res );
            $title = $row->subject_title;
            $question = htmlspecialchars($row->value_xsd);
            $db->freeResult( $res );
            
            $res = $db->query('SELECT value_blob FROM '.$smw_text2.' JOIN '.$smw_ids.' ON smw_id = p_id '.
                               ' JOIN '.$page.' ON page_title = smw_title AND page_namespace = smw_namespace'.
                               ' WHERE page_id = '.$id.' AND smw_title = "Description" AND smw_namespace = '.SMW_NS_PROPERTY);
            
            $description = '';
            
            if ($db->numRows($res) > 0){
                $row = $db->fetchObject( $res );
                $description = htmlspecialchars($row->value_blob);
            }
            $db->freeResult( $res );
            return array($title, $question, $description);
        }
        $db->freeResult( $res );
        return 0;
    }
}
