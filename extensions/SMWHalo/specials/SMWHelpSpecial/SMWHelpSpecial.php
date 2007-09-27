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
	$helppages = array();
	$help = array();

	$dbr =& wfGetDB( DB_SLAVE );
	$res = $dbr->select( $dbr->tableName('smw_attributes'),
		'*',
		'attribute_title = "DiscourseState"');

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

	//now go through all pages and create the links
	foreach($helppages as $id){
		$question = '';
		$description = '';
		$title = '';
		$res = $dbr->select( $dbr->tableName('smw_attributes'),
			'*',
			'subject_id =' . $id);

		if ($dbr->numRows($res) > 0){
			while ( $row = $dbr->fetchObject( $res ) ) {
				$title = $row->subject_title;
				if ($row->attribute_title == "Question"){
					$question = $row->value_xsd;
				}
				else if ($row->attribute_title == "Description"){
					$description = $row->value_xsd;
				}
			}
			$dbr->freeResult( $res );
		}
		$wikiTitle = Title::newFromText($title, NS_HELP);

		$link = '<a id="' . $question . '"href="' . $wikiTitle->getFullURL();
		if($description == wfMsg(smw_csh_newquestion)){
			$link .= '?action=edit" class="new';
		}
		$link .= '" ';
		$link .= 'title="' . $description . '">' . $question . '?</a><br/>';
		array_push($help, $link); // saved in an array first so they can be alphabetically ordered later (id)
	}
	if (sizeof($helppages)==0){
		$html = "Sorry, there are no questions in this section yet.<br/>";
	}
	else {
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
	global $wgCanonicalNamespaceNames;
	$actualNamespaces = $wgCanonicalNamespaceNames;
	$specialTitle = Title::newFromText('ContextSensitiveHelp', NS_SPECIAL);

	// Add 'ALL' and NS_MAIN. All is only used for this special page. NS_MAIN does not have a canonical name
	array_push($actualNamespaces, 'Regular Wiki Page'); // =NS_MAIN
	array_push($actualNamespaces, 'Search'); // =NS_MAIN
	array_push($actualNamespaces, 'ALL');
	asort($actualNamespaces);

	//all relevant actions
	$actions = array('ALL', 'delete', 'edit', 'history', 'move', 'view');

	$html = '<form action="' . $specialTitle->getFullURL() . '" method="get">You can refine your search according to the page type and/or the action you would like to know more about:<br/><br/>';
	$html .= 'Page Type:&nbsp;';
	$html .= '<select name="helpns" size="1">';
	foreach($actualNamespaces as $id => $name){
		if($name == 'Regular Wiki Page'){
			$html.= '<option value="Main">' . $name . '</option>'; //discourseState is encoded with 'Main', not with 'Regular wiki page'
		}
		else {
			$html.= '<option>' . $name . '</option>';
		}
	}
	$html .= '</select>';
	$html .= '&nbsp;&nbsp;&nbsp;Action:&nbsp;';
	$html .= '<select name="helpaction" size="1">';

	foreach($actions as $action){
		$html.= '<option>' . $action . '</option>';
	}
	$html .= '</select><br/>';
	$html .= '<input type="submit" value=" Go "></form><br/>';
	return $html;
}


?>