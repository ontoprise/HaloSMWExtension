<?php
/*  Copyright 2010, ontoprise GmbH
*  This file is part of the Collaboration-Extension.
*
*   The Collaboration-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Collaboration-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Maintenance script for converting existing comments
 * created by Extension:Comment to Collaboration Extension comments.
 * 
 * @author Benjamin Langguth
 * Date: 2010-01-13
 * 
 */


if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";
$dir = dirname(__FILE__);
$cegIP = "$dir/../../Collaboration";

require_once("$cegIP/includes/CE_GlobalFunctions.php");

echo "Converting comments for Collaboration...\n";
$dbr =& wfGetDB( DB_SLAVE );
$tb_msg = $dbr->tableName("smwh_comments");
$sql = "SELECT `msg_user`,`msg_msg` ,`msg_date`,`msg_id`,`msg_title` FROM $tb_msg ORDER BY `msg_date` DESC";
$res = $dbr->query($sql);

while ($comrow = $dbr->fetchObject($res)) {
	$comment_msg = $comrow->msg_msg;
	//$comment_msg = preg_replace('/[\r\n|\n|\r]/',"<br/>",$comment_msg);
	$comment_title = $comrow->msg_title;
	$comment_id = $comrow->msg_id;
	# replace whitespace with "T"
	$comment_date = str_replace('', 'T', $comrow->msg_date);
	$comment_date_for_page_name = str_replace(array('T', ':', '-', ' '), '', $comrow->msg_date);
	$comment_user = $comrow->msg_user;
	// wikiusers are stored in following format:
	// {<Username<}
	// so remove these special chars
	$comment_user = str_replace(array('{','<','>','}'), '', $comment_user, $found);
	if ($found && $found > 0)
		$comment_user = 'User:' . $comment_user;
	
	$page_name = $comment_title . '_' . $comment_date_for_page_name; 
	$page_content = '{{Comment|' .
		'CommentPerson=' . $comment_user .
		'|CommentRelatedArticle=' . $comment_title .
		'|CommentDatetime=' . $comment_date .
		'|CommentContent=' . $comment_msg . 
	'|}}';

	$title = Title::newFromText($page_name);
	//needed to get Names for Namespace
	wfLoadExtensionMessages('Collaboration');
	//setup namespaces
	cefInitNamespaces();
	$titleNSfixed = Title::makeTitle(CE_COMMENT_NS, $title);
	$article = new Article($titleNSfixed);
	echo "Creating comment article: " . $titleNSfixed->getFullText() . "\n";
	if (!$article->exists()) {
		echo "Creating comment article: " . $titleNSfixed->getFullText() . "\n";
		$return = $article->doEdit( $page_content, 'Comment created via Collaboration Extension convert script.' );
		if ($return->isGood())
			echo " => Comment article successfully created. \n";
	} else {
		echo " => Comment article already exists. \n";
	}
}

echo "\nConverting done. Exiting\n";
