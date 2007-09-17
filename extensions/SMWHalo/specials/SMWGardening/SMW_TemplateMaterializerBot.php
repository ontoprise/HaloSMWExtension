<?php
/*
 * Created on 13.04.2007
 *
 * Author: KK
 */
 require_once("SMW_GardeningBot.php");
 require_once("SMW_ParameterObjects.php");
 
 // Parameters of bot
 

 
 class TemplateMaterializerBot extends GardeningBot {
 	
 	function TemplateMaterializerBot() {
 		parent::GardeningBot('smw_templatematerializerbot');
 		
 	}
 	
 	public function getHelpText() {
 		return wfMsg('smw_gard_templatemat_docu');
 	}
 	
 	public function getLabel() {
 		return wfMsg($this->id);
 	}
 	
 	public function allowedForUserGroups() {
 		return array(SMW_GARD_GARDENERS, SMW_GARD_SYSOPS);
 	}
 	
 	/**
 	 * Returns an array mapping parameter IDs to parameter objects
 	 */
 	public function createParameters() {
 		
 		$p1 = new GardeningParamBoolean('APPLY_TO_TOUCHED_TEMPLATES', wfMsg('smw_gard_templatemat_applytotouched'), SMW_GARD_PARAM_OPTIONAL, true);
 		$params = array($p1);
 		return $params;
 	}
 	
 	/**
 	 * Do template materialization and return a log as wiki markup.
 	 * Do not use echo when it is NOT running asynchronously.
 	 */
 	public function run($paramArray, $isAsync, $delay) {
 		
 		if ($isAsync) { 
 			echo "...started!\n";
 			echo array_key_exists('APPLY_TO_TOUCHED_TEMPLATES', $paramArray) ? "Incremental update\n" : "Full materialization\n";
 			echo "------------------------------------------------\n";
 		}
 		
 		// get timestamp of last template materialization operation
 		$lastTemplateMaterialization = GardeningLog::getLastFinishedGardeningTask($this->id);
 		
 		// if not null, incremental update is possible to be configured by user
 		if ($lastTemplateMaterialization != NULL) {
 			$lastTemplateMaterialization = array_key_exists('APPLY_TO_TOUCHED_TEMPLATES', $paramArray) ? $lastTemplateMaterialization : NULL;
 		}
 		
 		// get all pages using 'dirty' templates 
 		$pageTitles = $this->getPagesUsingTemplates(NULL, $lastTemplateMaterialization);
 		$log = "";
 		$this->setNumberOfTasks(1);
 		$this->addSubTask(count($pageTitles));
 		// update them and write log
 		foreach($pageTitles as $pt) {
 			$this->worked(1);
 			if ($isAsync) echo "Updating ".$pt->getDBkey()."... ";
 			$article = new Article($pt);
 			$article->doEdit($article->getContent(), $article->getComment(), EDIT_UPDATE);
 			if ($isAsync) echo " done!\n";
 			$log .= "*Updating ".$pt->getDBkey()."... done!\n";
 			if ($delay > 0) {
 				usleep($delay);
 			}
 		}	
 		
		return $log;
 	}
 	
 	/**
 	 * Returns all pages using templates which have been altered after some point in time.
 	 * 
 	 * @param $templateTitle Consider only this template, otherwise all.
 	 * @param $touchedAfter Consider only templates touched after this time, othwerwise all.
 	 * 
 	 * @return array of Title
 	 */
 	private function getPagesUsingTemplates($templateTitle = NULL, $touchedAfter = NULL) {
		$result = "";
		$db =& wfGetDB( DB_MASTER );
		if ($templateTitle != NULL) { 
			$sql = 'page_id=tl_from AND tl_title = '. $db->addQuotes($templateTitle->getDBkey());
		} else {
			$sql = 'page_id=tl_from';
		}
		
		if ($touchedAfter != NULL) {
			$sql .= ' AND tl_title IN (SELECT page_title FROM page WHERE page_touched > '. $db->addQuotes($touchedAfter).' AND page_namespace='.NS_TEMPLATE.')';
		}       

		$res = $db->select( array($db->tableName('templatelinks'),$db->tableName('page')) , 
		                    'DISTINCT page_title',
		                    $sql, 'SMW::getPagesUsingTemplates', NULL );
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->page_title);
			}
		}
		$db->freeResult($res);
		return $result;
	}
 }
 
 // instantiate once.
 new TemplateMaterializerBot();
?>
