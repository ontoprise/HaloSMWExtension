<?php
/*  Copyright 2008, ontoprise GmbH
 *  This file is part of the halo-Extension.
 *
 *   The halo-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The halo-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  Author: Thomas Schweitzer
 */
if ( !defined( 'MEDIAWIKI' ) ) die;
if ( !defined('SGA_GARDENING_EXTENSION_VERSION')) {
	trigger_error("Semantic Notification requires Semantic Gardening extension. Please install.");
	die();
}
global $sgagIP, $wgExtensionMessagesFiles;
require_once("$sgagIP/includes/SGA_GardeningBot.php");
require_once("$sgagIP/includes/SGA_GardeningIssues.php");
require_once("$sgagIP/includes/SGA_ParameterObjects.php");

require_once("$smwgHaloIP/specials/SMWSemanticNotifications/SMW_SemanticNotification.php");

$wgExtensionMessagesFiles['SemanticNotification'] = $smwgHaloIP . '/specials/SMWSemanticNotifications/SMW_SemanticNotificationMessages.php';
wfLoadExtensionMessages('SemanticNotification');

/**
 * This bot processes the semantic notifications.
 *
 */
class SemanticNotificationBot extends GardeningBot {


	function __construct() {
		parent::GardeningBot("smw_semanticnotificationbot");
	}

	public function getHelpText() {
		return wfMsg('smw_gard_semanticnotificationhelp');
	}

	public function getLabel() {
		return wfMsg($this->id);
	}

	public function allowedForUserGroups() {
		return array(SMW_GARD_GARDENERS, SMW_GARD_SYSOPS);
	}

	/**
	 * Returns an array of parameter objects
	 */
	public function createParameters() {
	
		$params = array();
		
		return $params;
	}

	/**
	 * This method is called by the bot framework. 
	 */
	public function run($paramArray, $isAsync, $delay) {
		echo "...started!\n";
		$result = "";

		$log = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		
		$notifications = SemanticNotificationManager::getAllNotifications();
		
		$this->setNumberOfTasks(1);
		$this->addSubTask(count($notifications));
		
		foreach ($notifications as $n) {
			$sn = SemanticNotification::newFromName($n[1], $n[0]);
			echo $n[1]." ".$sn->getUserName();
			$ts = $sn->getTimestamp();
			preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/",$ts, $t);
			$lastUpdate = mktime($t[4],$t[5],$t[6],$t[2],$t[3],$t[1]);
			$diff = time() - $lastUpdate;
			$ui = $sn->getUpdateInterval();
			if ($diff < $ui * (24*60*60)) {
				echo "...skipped\n";
				// update interval not elapsed
				continue;
			}
			
			$sn->sendNotificationMessage();
			$sn->store();
			
			$this->worked(1);

			$log->addGardeningIssueAboutValue(
				$this->id, SMW_GARDISSUE_PROCESSED_NOTIFICATION, 
				Title::newFromText($sn->getName()), $sn->getUserName());
			echo "...done.\n";
			
		}
	
		return $result;

	}
	
	
}

// Create one instance to register the bot.
new SemanticNotificationBot();

define('SMW_SEMANTIC_NOTIFICATION_BOT_BASE', 2500);
define('SMW_GARDISSUE_PROCESSED_NOTIFICATION', SMW_SEMANTIC_NOTIFICATION_BOT_BASE * 100 + 1);

class SemanticNotificationBotIssue extends GardeningIssue {

	public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified) {
		parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified);
	}

	protected function getTextualRepresenation(& $skin, $text1, $text2, $local = false) {
		switch($this->gi_type) {
			case SMW_GARDISSUE_PROCESSED_NOTIFICATION:
				return wfMsg('smw_sn_processed_notification', $text1, $this->value);
			default: return NULL;
				
		}
	}
}

class SemanticNotificationBotFilter extends GardeningIssueFilter {

	public function __construct() {
		parent::__construct(SMW_SEMANTIC_NOTIFICATION_BOT_BASE);
		$this->gi_issue_classes = array(wfMsg('smw_gardissue_class_all')); 
	}

	public function getUserFilterControls($specialAttPage, $request) {
		return '';
	}

	public function linkUserParameters(& $wgRequest) {
		return array('pageTitle' => $wgRequest->getVal('pageTitle'));
	}

	public function getData($options, $request) {
		$pageTitle = $request->getVal('pageTitle');
		if ($pageTitle != NULL) {
			// show only issue of *ONE* title
			return $this->getGardeningIssueContainerForTitle($options, $request, Title::newFromText(urldecode($pageTitle)));
		} else return parent::getData($options, $request);
	}

	private function getGardeningIssueContainerForTitle($options, $request, $title) {
		$gi_class = $request->getVal('class') == 0 ? NULL : $request->getVal('class') + $this->base - 1;


		$gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();

		$gic = array();
		$gis = $gi_store->getGardeningIssues('smw_semanticnotificationbot', NULL, $gi_class, $title, SMW_GARDENINGLOG_SORTFORTITLE, NULL);
		$gic[] = new GardeningIssueContainer($title, $gis);

		return $gic;
	}
}

?>
