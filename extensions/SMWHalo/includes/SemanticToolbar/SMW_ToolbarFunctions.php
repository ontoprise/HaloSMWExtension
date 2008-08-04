<?php

/**
 * Basic functions which provide content for the semantic toolbar
 * @author Markus Nitsche
 */
global $wgAjaxExportList;


$wgAjaxExportList[] = 'smwf_tb_GetHelp';
$wgAjaxExportList[] = 'smwf_tb_getLinks';
$wgAjaxExportList[] = 'smwf_tb_GetBuiltinDatatypes';
$wgAjaxExportList[] = 'smwf_tb_GetUserDatatypes';
$wgAjaxExportList[] = 'smwf_tb_AskQuestion';

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
	$helppages = array();
	$results = false;
	$discourseState = mysql_real_escape_string($namespace) . ":" . mysql_real_escape_string($action);
	$dbr =& wfGetDB( DB_SLAVE );
	$smw_attributes = $dbr->tableName('smw_attributes');
	$res = $dbr->query('SELECT * FROM '.$smw_attributes.' WHERE attribute_title = "DiscourseState" AND value_xsd= "' . $discourseState . '" AND subject_namespace = "' . NS_HELP . '" ORDER BY RAND() LIMIT 5');

	while ($row = $dbr->fetchObject( $res )) {
		$helppages[] = $row->subject_id;
	}
	$dbr->freeResult( $res );

	foreach($helppages as $id){
		$question = '';
		$description = '';
		$title = '';
		
		//get questions
		$res = $dbr->select( $dbr->tableName('smw_attributes'),
			'*',
			array('subject_id =' . $id, 'attribute_title="Question"'));

		if ($dbr->numRows($res) > 0){
			$results = true;
			$row = $dbr->fetchObject( $res );
			$title = $row->subject_title;
			$question = htmlspecialchars($row->value_xsd);
			$dbr->freeResult( $res );
			
			// get descriptions
			$res = $dbr->select( $dbr->tableName('smw_longstrings'),
				'*',
				array('subject_id =' . $id, 'attribute_title="Description"'));
	
			if ($dbr->numRows($res) > 0){
				$row = $dbr->fetchObject( $res );
				$description = htmlspecialchars($row->value_blob);
			}
		}
		$dbr->freeResult( $res );
		
		if($results){
			$wikiTitle = Title::newFromText($title, NS_HELP);
	
			if($description == wfMsg('smw_csh_newquestion')){
				$html .= '<a href="' . $wikiTitle->getFullURL();
				$html .= '?action=edit" class="new';
				$html .= '" ';
				$html .= 'title="' . $description . '" target="_new" onClick="return helplog(\'' . $question . '\', \'edit\')">' . $question . '?</a>';
				$html .= '<br />';
			}
			else {
				$html .= '<a href="' . $wikiTitle->getFullURL();
				$html .= '" ';
				$html .= 'title="' . $description . '" target="_new" onClick="return helplog(\'' . $question . '\', \'view\')">' . $question . '?</a>';
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
		$html .= '<img id="questionLoaderIcon" style="display:none; margin-bottom:3px; margin-left:3px;" src="' . $smwgHaloScriptPath . '/skins/ajax-loader.gif"/><br/>';
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
				$html .= '<tr class="linktable-row"><td><a href="' . $url . '/' . $row->pl_title . '" target="_new" onClick="return linklog(\'' . $row->pl_title . '\', \'view\')">' . $linktitle . '</a></td>';
			}
			else {
				$html .= '<tr class="linktable-row"><td><a class="new" href="' . $url . '/' . $row->pl_title . '" target="_new" onClick="return linklog(\'' . $row->pl_title . '\', \'view\')">' . $linktitle . '</a></td>';
			}
			$html .= '<td align="right" valign="bottom">(<a href="' . $url . '?title=' . $row->pl_title . '&action=edit" target="_new" onClick="return linklog(\'' . $row->pl_title . '\', \'edit\')">edit</a>)</td></tr>';
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


?>
