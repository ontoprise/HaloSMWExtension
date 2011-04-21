<?php
/**
 * @file
 * @ingroup SemanticNotifications
 */

/*  Copyright 2009, ontoprise GmbH
* 
*   This file is part of the SemanticNotifications-Extension.
*
*   The SemanticNotifications-Extension is free software; you can redistribute 
*   it and/or modify it under the terms of the GNU General Public License as 
*   published by the Free Software Foundation; either version 3 of the License, 
*   or (at your option) any later version.
*
*   The SemanticNotifications-Extension is distributed in the hope that it will 
*   be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
if ( !defined( 'MEDIAWIKI' ) ) die;
global $sngIP, $sgagIP, $wgExtensionMessagesFiles;
require_once("$sgagIP/includes/SGA_GardeningBot.php");
require_once("$sgagIP/includes/SGA_GardeningIssues.php");
require_once("$sgagIP/includes/SGA_ParameterObjects.php");

require_once("$sngIP/includes/SN_SemanticNotification.php");

$wgExtensionMessagesFiles['SemanticNotification'] = $sngIP . '/includes/SN_SemanticNotificationMessages.php';
wfLoadExtensionMessages('SemanticNotification');

/**
 * This bot processes the semantic notifications.
 *
 */
class SemanticNotificationBot extends GardeningBot {


	function __construct() {
		parent::GardeningBot("sn_semanticnotificationbot");
	}

	public function getHelpText() {
		return wfMsg('sn_gard_semanticnotificationhelp');
	}

	public function getLabel() {
		return wfMsg($this->id);
	}
    
    public function getImageDirectory() {
        return 'extensions/SemanticNotifications/skins';
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
		global $wgUser;
		echo "...started!\n";
		$result = "";

		// Make sure the the triple store is asked for SPARQL queries although
		// the bot is running in maintenance mode.
		define('SMWH_FORCE_TS_UPDATE', true);
		
		$log = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
		
		$notifications = SemanticNotificationManager::getAllNotifications();
		
		$this->setNumberOfTasks(1);
		$this->addSubTask(count($notifications));
		foreach ($notifications as $n) {
			$sn = SemanticNotification::newFromName($n[1], $n[0]);
			echo $n[1]." ".$sn->getUserName();
			$wgUser = User::newFromName($sn->getUserName());
			$ts = $sn->getTimestamp();
			preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/",$ts, $t);
			$lastUpdate = mktime($t[4],$t[5],$t[6],$t[2],$t[3],$t[1]);
			$now = wfTimestampNow();
			preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/",$now, $n);
			$now = mktime($n[4],$n[5],$n[6],$n[2],$n[3],$n[1]);
			
			$diff = $now - $lastUpdate;
			$ui = $sn->getUpdateInterval();
			// Update interval is given in minutes = 60 seconds
			$skipped = false;
			$notificationSent = false;
			if ($diff >= $ui * 60) {
				$notificationSent = $sn->sendNotificationMessage();
				$sn->store();
			} else {
				$skipped = true;
			}	
			
			$this->worked(1);

			$log->addGardeningIssueAboutValue(
				$this->id, 
				$skipped 
					? SMW_GARDISSUE_SKIPPED_NOTIFICATION
					: $notificationSent
						? SMW_GARDISSUE_PROCESSED_NOTIFICATION_SENT
						: SMW_GARDISSUE_PROCESSED_NOTIFICATION, 
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
define('SMW_GARDISSUE_SKIPPED_NOTIFICATION',   SMW_SEMANTIC_NOTIFICATION_BOT_BASE * 100 + 2);
define('SMW_GARDISSUE_PROCESSED_NOTIFICATION_SENT', SMW_SEMANTIC_NOTIFICATION_BOT_BASE * 100 + 3);

class SemanticNotificationBotIssue extends GardeningIssue {

	public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified) {
		parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified);
	}

	protected function getTextualRepresenation(& $skin, $text1, $text2, $local = false) {
		switch($this->gi_type) {
			case SMW_GARDISSUE_PROCESSED_NOTIFICATION:
				return wfMsg('sn_processed_notification', $text1, $this->value);
			case SMW_GARDISSUE_PROCESSED_NOTIFICATION_SENT:
				return wfMsg('sn_processed_notification_sent', $text1, $this->value);
			case SMW_GARDISSUE_SKIPPED_NOTIFICATION:
				return wfMsg('sn_skipped_notification', $text1, $this->value);
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


		$gi_store = SMWGardeningIssuesAccess::getGardeningIssuesAccess();

		$gic = array();
		$gis = $gi_store->getGardeningIssues('sn_semanticnotificationbot', NULL, $gi_class, $title, SMW_GARDENINGLOG_SORTFORTITLE, NULL);
		$gic[] = new GardeningIssueContainer($title, $gis);

		return $gic;
	}
}

?>
