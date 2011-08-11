<?php

/**
 * @file
 * @ingroup SMWHalo
 * @ingroup SMWHaloSemanticToolbar
 * Basic functions which provide content for the semantic toolbar
 * 
 * @author Markus Nitsche
 */
global $wgAjaxExportList;


$wgAjaxExportList[] = 'smwf_tb_GetHelp';
$wgAjaxExportList[] = 'smwf_tb_getLinks';
$wgAjaxExportList[] = 'smwf_tb_GetBuiltinDatatypes';
$wgAjaxExportList[] = 'smwf_tb_GetUserDatatypes';
$wgAjaxExportList[] = 'smwf_tb_AskQuestion';
$wgAjaxExportList[] = 'smwf_tb_getTripleStoreStatus';

/**
 * This function will load context sensitive help from the language help files
 * and return an html-string which is then shown in the semantic toolbar
 * @param $namespace namespace of current page
 * @param $action action of current page
 * @return $html html-string containing help
 */
function smwf_tb_GetHelp($namespace, $action){
	global $wgScriptPath, $smwgHaloScriptPath, $smwgAllowNewHelpQuestions;
	$html = '';
	$results = false;

	$tb_store = SMWToolbarStorage::getToolbarStorage();
	$helppages = $tb_store->getHelppages($namespace, $action);
	$discourseState = $namespace . ":" . $action;

	foreach($helppages as $id){
		$questions = $tb_store->getQuestions($id);
		//get questions

		if(is_array($questions)){
			$results = true;
			$wikiTitle = Title::newFromText($questions[0], NS_HELP);

			if($questions[2] == wfMsg('smw_csh_newquestion')){
				$html .= '<a href="' . $wikiTitle->getFullURL();
				$html .= '?action=edit" class="new';
				$html .= '" ';
				$html .= 'title="' . $questions[2] . '" target="_new" onClick="return helplog(\'' . $questions[1] . '\', \'edit\')">' . $questions[1] . '?</a>';
				$html .= '<br />';
			}
			else {
				$html .= '<a href="' . $wikiTitle->getFullURL();
				$html .= '" ';
				$html .= 'title="' . $questions[2] . '" target="_new" onClick="return helplog(\'' . $questions[1] . '\', \'view\')">' . $questions[1] . '?</a>';
				$html .= '<br />';
			}
		}
	}
	if(!$results){
		$html .= wfMsg('smw_csh_nohelp') . '<br/>';
	}
	$specialTitle = Title::newFromText('ContextSensitiveHelp', NS_SPECIAL);
	$html .= '<div id="morehelp"><a href="' . $specialTitle->getFullURL() . '?restriction=all&ds=' . $discourseState .'" target="_new">(more)</a></div><br/>';

	if ($smwgAllowNewHelpQuestions){
		$html .= '<a href="javascript:void(0)" onclick="$(\'askHelp\').show()">Ask your own question</a><br/>';
		$html .= '<div id="askHelp" style="display:none"><input id="question" name="question" type="text" size="20" onKeyPress="return submitenter(this,event)"/>';
		$html .= '<img id="questionLoaderIcon" src="' . $smwgHaloScriptPath . '/skins/ajax-loader.gif"/><br/>';
		$html .= '<a href="javascript:void(0)" onclick="askQuestion()">Send</a>&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="$(\'askHelp\').hide()">Cancel</a></div>';
	}
	return $html;
}

/**
 * function smwfAskQuestion
 * creates a new help page. The contents are made up of the users question
 * and some automatically generated content.
 * Every question is encoded with a discourseState. For more information, see
 * specials/SMWHelpSpecials/SMWHelpSpecial.php
 * @param $namespace canonical namespace name of current article
 * @param $action current action of the user
 * @param $question question entered by the user
 */
function smwf_tb_AskQuestion($namespace, $action, $question){
	if($question == ""){
		return "Sorry, you have not entered a question.";
	}
	//Replace '?' at the end and leading or ending whitespaces
	$question = str_replace('?', '', $question);
	$question = preg_replace('/^\s*(.*?)\s*$/', '$1', $question);

	$wgTitle = Title::newFromText( $question . "?", NS_HELP );
	if ( !$wgTitle ) {
		return wfMsg('smw_help_error');
	}
	if($wgTitle->exists()){
		return wfMsg('smw_help_pageexists');
	}


	/*STARTLOG*/
	$logmsg = "Added question '$question'";
	smwLog($logmsg, "CSH", "help_addednew", wfTimestampNow());
	/*ENDLOG*/


	$discourseState = "$namespace:$action";

	$articleContent = "[[question:=$question]]?\n\n";
	$articleContent .= "[[discourseState:=$discourseState]]\n\n";
	$articleContent .= "[[description:=" . wfMsg('smw_csh_newquestion') . "]]";

	//Create a new wikipage in the Help namespace

	$wgArticle = new Article( $wgTitle );
	$success = $wgArticle->doEdit( $articleContent, "New help question added", EDIT_NEW);
	if($success){
		return wfMsg('smw_help_question_added');
	}
	else {
		return wfMsg('smw_help_error');
	}
}

/**
 * This function extracts all links contained in one wikipage from the
 * database. It then returns an html-string containing all links to
 * pages of the default namespace
 * @param $articleId ID of current article
 * @return $html htmlstring containing all links
 */
function smwf_tb_getLinks($articleId){
	global $wgArticlePath, $smwgHaloScriptPath;
	$linksExist = false;
	$html = '<div id="edit">' .
			'Filter:&nbsp;<input name="filter" size="15" id="linkfilter" onkeyup="filter(this, \'linktable\', 0)" type="text">' .
			'&nbsp;<a href="javascript:update()"><img src="' . $smwgHaloScriptPath . '/skins/redcross.gif"/></a>' .
			'<hr/><table class="linktable" id="linktable">';
	$dbr =& wfGetDB( DB_SLAVE );
	$url = str_replace('$1','',$wgArticlePath);
	$url = preg_replace('/\/$/', '', $url);
	$res = $dbr->select( $dbr->tableName('pagelinks'),
		'*',
	array('pl_from=' . $articleId, 'pl_namespace=0'));

	if ($dbr->numRows($res) > 0){
		$linksExist = true;
		while ( $row = $dbr->fetchObject( $res ) ) {
			$title = Title::newFromText($row->pl_title);
			$linktitle = str_replace("_", " ", $row->pl_title);
			$linktitle = implode("-<br/>", str_split($linktitle, 17));
			if ($title->exists()){
				$html .= '<tr class="linktable-row"><td><a href="' . $url . '/' . $row->pl_title . '" onClick="return linklog(\'' . $row->pl_title . '\', \'view\')">' . $linktitle . '</a></td>';
			}
			else {
				$html .= '<tr class="linktable-row"><td><a class="new" href="' . $url . '/' . $row->pl_title . '" onClick="return linklog(\'' . $row->pl_title . '\', \'view\')">' . $linktitle . '</a></td>';
			}
			$html .= '<td align="right" valign="bottom">(<a href="' . $url . '?title=' . $row->pl_title . '&action=edit" onClick="return linklog(\'' . $row->pl_title . '\', \'edit\')">edit</a>)</td></tr>';
		}
		$dbr->freeResult( $res );
	}

	$html .= '</table></div>';
	return $linksExist ? $html : '';
}

/**
 * function smwfGetBuiltinDatatypes
 * This function returns a comma separated list of all builtin data types
 */
function smwf_tb_GetBuiltinDatatypes(){
	global $smwgHaloContLang;
	$result = "Builtin types:";

	$types = SMWDataValueFactory::getKnownTypeLabels();
	asort($types);

	// Ignore all special properties
	$sp = $smwgHaloContLang->getSpecialPropertyLabels();
	$types = array_diff($types, $sp);
	foreach($types as $key => $type) {
		$result .= ",".$type;
	}
	return $result;
}

/**
 * function smwfGetUserDatatypes
 * This function returns a comma separated list of all user defined data types
 */
function smwf_tb_GetUserDatatypes(){
	$result = "User defined types:";

	$db =& wfGetDB( DB_SLAVE );

	$NStype = SMW_NS_TYPE;
	$page = $db->tableName( 'page' );
	$sql = "SELECT 'Types' as type,
	{$NStype} as namespace,
	page_title as title,
	page_title as value,
	1 as count
	FROM $page
	WHERE page_namespace = $NStype";

	$res = $db->query($sql);

	// Builtin types may appear in the list of user types (if there is an
	// article for them). They have to be removed from the user types
	$builtinTypes = SMWDataValueFactory::getKnownTypeLabels();

	$userTypes = array();

	if ($db->numRows($res) > 0) {
		while($row = $db->fetchObject($res)) {
			$userTypes[] = str_replace("_", " ", $row->title);
		}
	}
	$db->freeResult($res);

	$userTypes = array_diff($userTypes, $builtinTypes);

	foreach($userTypes as $key => $type) {
		$result .= ",".$type;
	}

	return $result;
}

function smwf_tb_getTripleStoreStatus() {
	global $smwgTripleStoreGraph;
	$con = TSConnection::getConnector();
	try {
		$con->connect();
		$statusInfo = $con->getStatus($smwgTripleStoreGraph);
		$response = new AjaxResponse(json_encode($statusInfo));
        $response->setContentType( "application/json" );
        $response->setResponseCode(200);
        $con->disconnect();
	} catch(Exception $e) {
		$response = new AjaxResponse($e->getMessage());
        $response->setContentType( "application/text" );
        $response->setResponseCode(500);
	}
	return $response;
}

abstract class SMWToolbarStorage {

	private static $INSTANCE = NULL;

	public static function getToolbarStorage() {
		global $smwgBaseStore;
		if (self::$INSTANCE == NULL) {
			switch ($smwgBaseStore) {
				
				case ('SMWHaloStore2'): default:
					self::$INSTANCE = new SMWToolbarStorageSQL2();
					break;
				case ('SMWHaloStore'): default:
					self::$INSTANCE = new SMWToolbarStorageSQL();
					break;
			}
		}
		return self::$INSTANCE;
	}

	/**
	 * TODO: Write documentation
	 */
	public abstract function  getHelppages($namespace, $action);

	public abstract function getQuestions($id);
}

class SMWToolbarStorageSQL extends SMWToolbarStorage {
	public function getHelppages($namespace, $action) {
		$helppages = array();
		$discourseState = mysql_real_escape_string($namespace) . ":" . mysql_real_escape_string($action);
		$dbr =& wfGetDB( DB_SLAVE );
		$smw_attributes = $dbr->tableName('smw_attributes');
		$res = $dbr->query('SELECT * FROM '.$smw_attributes.' WHERE attribute_title = "DiscourseState" AND value_xsd= "' . $discourseState . '" AND subject_namespace = "' . NS_HELP . '" ORDER BY RAND() LIMIT 5');

		while ($row = $dbr->fetchObject( $res )) {
			$helppages[] = $row->subject_id;
		}
		$dbr->freeResult( $res );
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

class SMWToolbarStorageSQL2 extends SMWToolbarStorageSQL {
	public function getHelppages($namespace, $action) {
		$helppages = array();
		$db =& wfGetDB( DB_SLAVE );
		$namespace = mysql_real_escape_string($namespace);
		$action = mysql_real_escape_string($action);
		
		$discourseState1 =  $namespace. ":" . $action;
		$discourseState2 =  "ALL:" . $action;
		$discourseState3 =  $namespace. ":ALL";
		$discourseState4 =  "ALL:ALL";
		
		$smw_ids = $db->tableName('smw_ids');
		$smw_atts2 = $db->tableName('smw_atts2');
		$page = $db->tableName('page');

		$discourseStateID = $db->selectRow($smw_ids, array('smw_id'), array('smw_title'=>'DiscourseState', 'smw_namespace' => SMW_NS_PROPERTY));
		if ($discourseStateID != null) {
			$discourseStateID = $discourseStateID->smw_id;
		} else return array();

		$res = $db->query('SELECT page_id FROM '.$smw_atts2.
        ' JOIN '.$smw_ids.' ON smw_id = s_id '.
        ' JOIN '.$page.' ON page_title = smw_title AND page_namespace = smw_namespace '.
        ' WHERE p_id = '.$discourseStateID. ' AND '.
        '(UPPER('.DBHelper::convertColumn('value_xsd').') = UPPER(' . $db->addQuotes($discourseState1) . ') '.
        'OR UPPER('.DBHelper::convertColumn('value_xsd').') = UPPER(' . $db->addQuotes($discourseState2) . ') '.
        'OR UPPER('.DBHelper::convertColumn('value_xsd').') = UPPER(' . $db->addQuotes($discourseState3) . ') '.
        'OR UPPER('.DBHelper::convertColumn('value_xsd').') = UPPER(' . $db->addQuotes($discourseState4) . ')) '.
        'AND page_namespace = ' . NS_HELP . ' ORDER BY RAND() LIMIT 5');

		while ($row = $db->fetchObject( $res )) {
			$helppages[] = $row->page_id;
		}
		$db->freeResult( $res );
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


