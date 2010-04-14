<?php
/*  Copyright 2009, ontoprise GmbH
* 
*  This file is part of the Interwiki-Article-Import-module in the Data-Import-Extension.
*
*   The DataImport-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The DataImport-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
* 
*/

/**
 * @file
  * @ingroup DIInterWikiArticleImport
  * 
  * @author Thomas Schweitzer
 */

if ( !defined( 'MEDIAWIKI' ) ) die;
global $sgagIP;
require_once("$sgagIP/includes/SGA_GardeningBot.php");
require_once("$sgagIP/includes/SGA_GardeningIssues.php");
require_once("$sgagIP/includes/SGA_ParameterObjects.php");


/**
 * This bot processes the updates for imported articles.
 *
 */
class IAIImportBot extends GardeningBot {


	function __construct() {
		parent::GardeningBot("iai_importbot");
	}

	public function getHelpText() {
		return wfMsg('iai_gard_importhelp');
	}

	public function getLabel() {
		return wfMsg($this->id);
	}
    
    public function getImageDirectory() {
        return 'extensions/DataImport/IAI/skins';
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

		var_dump($paramArray);
		$result = "";

global $iaigIP;
$iaigLog = fopen("$iaigIP/IAIBot.log", "a");
fprintf($iaigLog, "Import Bot started.\n");
		
		$log = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
				
		$this->setNumberOfTasks(1);
		// Two sub-tasks: update template, update images
		$this->addSubTask(2);
		
		global $iaigWikiApi;
		$ai = new IAIArticleImporter($iaigWikiApi);
		try {
			echo "Importing from $iaigWikiApi\n";
			
			// Import templates of article
			$article = $paramArray["article"];
			
			echo "Start report\n";
			$ai->startReport();
			
			echo "Import templates\n";
			$ai->importTemplates(array($article));
			$this->worked(1);
			$log->addGardeningIssueAboutValue(
					$this->id, IAI_IMPORTBOT_PROCESSED_TASK, 
					Title::newFromText($article), "Templates");
			
			echo "import Images\n";
					
			// Import images of article
			$ai->importImagesForArticle(array($article));
			
			$log->addGardeningIssueAboutValue(
					$this->id, IAI_IMPORTBOT_PROCESSED_TASK, 
					Title::newFromText($article), "Images");
					
			echo "Create report\n";
								
			$this->worked(1);
		} catch (Exception $e) {
			echo "Caught an exception: \n".$e->getMessage();
		}
		$report = $ai->createReport(true);

		$log->addGardeningIssueAboutArticle(
				$this->id, IAI_IMPORTBOT_REPORT, 
				Title::newFromText($report));
		
		echo "...done.\n";

fclose($iaigLog);		
		return $result;

	}
	
	
}

// Create one instance to register the bot.
new IAIImportBot();

define('IAI_IMPORT_BOT_BASE', 2500);
define('IAI_IMPORTBOT_PROCESSED_TASK', IAI_IMPORT_BOT_BASE * 100 + 1);
define('IAI_IMPORTBOT_REPORT', IAI_IMPORT_BOT_BASE * 100 + 2);

class IAIImportBotIssue extends GardeningIssue {

	public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified) {
		parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified);
	}

	protected function getTextualRepresenation(& $skin, $text1, $text2, $local = false) {
		switch($this->gi_type) {
			case IAI_IMPORTBOT_PROCESSED_TASK:
				return wfMsg('iai_processed_task', $text1, $this->value);
			case IAI_IMPORTBOT_REPORT:
				return wfMsg('iai_importbot_report', $text1);
				
			default: return NULL;
				
		}
	}
}

class IAIImportBotFilter extends GardeningIssueFilter {

	public function __construct() {
		parent::__construct(IAI_IMPORT_BOT_BASE);
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
		$gis = $gi_store->getGardeningIssues('iai_importbot', NULL, $gi_class, $title, SMW_GARDENINGLOG_SORTFORTITLE, NULL);
		$gic[] = new GardeningIssueContainer($title, $gis);

		return $gic;
	}
}