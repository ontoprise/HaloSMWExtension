<?php
/*
 * Created on 18.06.2007
 *
 * Author: kai
 */
 global $smwgHaloIP;
 require_once("$smwgHaloIP/specials/SMWGardening/SMW_GardeningBot.php");
 require_once("$smwgHaloIP/specials/SMWGardening/SMW_ParameterObjects.php");
 
 // maximum number of subcategories not regarded as anomaly
 define('MAX_SUBCATEGORY_NUM', 8);
 // minimum number of subcategories not regarded as anomaly
 define('MIN_SUBCATEGORY_NUM', 2);
 
 /**
  * Bot is used to find ontology anomalies
  */
 class AnomaliesBot extends GardeningBot {
 	
 	// global log which contains wiki-markup
 	private $globalLog;
 	private $store;
 	
 	function AnomaliesBot() {
 		parent::GardeningBot("smw_anomaliesbot");
 		$this->globalLog = "== ".wfMsg('smw_gard_anomalylog')."! ==\n\n";
 		$this->store = $this->getAnomalyStore();
 	}
 	
 	public function getHelpText() {
 		return wfMsg('smw_gard_anomaly_docu');
 	}
 	
 	public function getLabel() {
 		return wfMsg($this->id);
 	}
 	
 	public function allowedForUserGroups() {
 		return array(SMW_GARD_GARDENERS, SMW_GARD_SYSOPS, SMW_GARD_ALL_USERS);
 	}
 	
 	/**
 	 * Returns an array of parameter objects
 	 */
 	public function createParameters() {
 		global $wgUser;
 		$params = array();
 		$params[] = new GardeningParamBoolean('CATEGORY_NUMBER_ANOMALY', wfMsg('smw_gard_anomaly_checknumbersubcat'), SMW_GARD_PARAM_OPTIONAL, true);
 		$params[] = new GardeningParamBoolean('CATEGORY_LEAF_ANOMALY', wfMsg('smw_gard_anomaly_checkcatleaves'), SMW_GARD_PARAM_OPTIONAL, true);
 		$resParam = new GardeningParamTitle('CATEGORY_RESTRICTION', wfMsg('smw_gard_anomaly_restrictcat'), SMW_GARD_PARAM_OPTIONAL);
 		$resParam->setAutoCompletion(true);
 		$resParam->setTypeHint(NS_CATEGORY);
 		$params[] = $resParam;
 		// for GARDENERS and SYSOPS, deletion of leaf categories is possible.
 		$userGroups = $wgUser->getGroups();
 		if (in_array('gardener', $userGroups) || in_array('sysop', $userGroups)) { // why do the constants SMW_GARD_SYSOP, SMW_GARD_GARDENERS not work here?
 			$params[] = new GardeningParamBoolean('CATEGORY_LEAF_DELETE', wfMsg('smw_gard_anomaly_deletecatleaves'), SMW_GARD_PARAM_OPTIONAL, false);
 		}
 		return $params;
 	}
 	
 	/**
 	 * Do bot work and return a log as wiki markup.
 	 * Do not use echo when it is not running asynchronously.
 	 */
 	public function run($paramArray, $isAsync, $delay) {
 		global $wgLang;
 		$gi_store = SMWGardening::getGardeningIssuesAccess();
 		if (!$isAsync) {
 			echo 'Missing annotations bot should not be run synchronously! Abort bot.'; // do not externalize
 			return;
 		}
 		echo $this->getBotID()." started!\n";
 		
       		
 		if (array_key_exists('CATEGORY_LEAF_ANOMALY', $paramArray)) {  
 			echo "Checking for category leafs...\n";
 			if ($paramArray['CATEGORY_RESTRICTION'] == '') {
       			$categoryLeaves = $this->store->getCategoryLeafs();
       	
       			
       			foreach($categoryLeaves as $cl) {
       				$gi_store->addGardeningIssueAboutArticle($this->id, SMW_GARDISSUE_CATEGORY_LEAF, $cl);
       			
       				echo $cl->getPrefixedText()."\n";
       			} 
 			} else {
 				
 				$categories = explode(";", $paramArray['CATEGORY_RESTRICTION']);
 				$categoryLeaves = array();
 				
 				foreach($categories as $c) {
 					$categoryDB = str_replace(" ", "_", trim($c));
 					$categoryTitle = Title::newFromText($categoryDB, NS_CATEGORY);
 					
 					$categoryLeaves = $this->store->getCategoryLeafs($categoryTitle);
 				
 					foreach($categoryLeaves as $cl) {
 						// maybe it exists already
 						if (!$gi_store->existsGardeningIssue($this->id, SMW_GARDISSUE_CATEGORY_LEAF, NULL, $cl)) {
 							$gi_store->addGardeningIssueAboutArticle($this->id, SMW_GARDISSUE_CATEGORY_LEAF, $cl);
	       					echo $cl->getPrefixedText()."\n";
 						}
       				} 
 				}
       			
 			}		
       	 echo "done!\n";
 	   		
 		}
       	
       	if (array_key_exists('CATEGORY_NUMBER_ANOMALY', $paramArray)) {  	
       		echo "\nChecking for number anomalies...\n";
       		if ($paramArray['CATEGORY_RESTRICTION'] == '') {
       			
        		$subCatAnomalies = $this->store->getCategoryAnomalies();
       			
       	
       			
       			foreach($subCatAnomalies as $a) {
       				list($title, $subCatNum) = $a;
       				$gi_store->addGardeningIssueAboutValue($this->id, SMW_GARDISSUE_SUBCATEGORY_ANOMALY, $title, $subCatNum);
       				
       				echo $title->getPrefixedText()." has $subCatNum ".($subCatNum == 1 ? "subcategory" : "subcategories").".\n";
       			} 
       		} else {
       			
       			$categories = explode(";", urldecode($paramArray['CATEGORY_RESTRICTION']));
 				$categoryLeaves = array();
 			
 				foreach($categories as $c) {
 					$categoryDB = str_replace(" ", "_", trim($c));
 					$categoryTitle = Title::newFromText($categoryDB, NS_CATEGORY);
 					$subCatAnomalies = $this->store->getCategoryAnomalies($categoryTitle);
 					foreach($subCatAnomalies as $a) {
       					list($title, $subCatNum) = $a;
       					if (!$gi_store->existsGardeningIssue($this->id, SMW_GARDISSUE_SUBCATEGORY_ANOMALY, NULL, $title)) {
	       					$gi_store->addGardeningIssueAboutValue($this->id, SMW_GARDISSUE_SUBCATEGORY_ANOMALY, $title, $subCatNum);
	       					echo $title->getPrefixedText()." has $subCatNum ".($subCatNum == 1 ? "subcategory" : "subcategories").".\n";
       					}
       				} 
 				}
       		}
       		echo "done!\n";
       	}
       	
       	if (array_key_exists('CATEGORY_LEAF_DELETE', $paramArray)) {
       		echo "\nRemoving category leaves...\n";
       		if ($paramArray['CATEGORY_RESTRICTION'] == '') {
       			$deletedCategories = array();
       			$visitedNodes = array();
        		$this->store->removeCategoryLeaves(NULL, $deletedCategories, $visitedNodes);
        		
       			foreach($deletedCategories as $dc) {
       				list($cat, $superCats) = $dc;
       				$leafOf = count($superCats) > 0 ? "(".wfMsg('smw_gard_was_leaf_of')." [[:".$superCats[0]->getPrefixedText()."]])" : "";
       				$this->globalLog .= "\n*[[:".$cat->getPrefixedText()."]] " . $leafOf;
 	          	}
       		} else {
       			$categories = explode(";", $paramArray['CATEGORY_RESTRICTION']);
       			print_r($categories);
       			foreach($categories as $c) {
       				$deletedCategories = array();
       				$visitedNodes = array();
       				$categoryDB = str_replace(" ", "_", trim($c));
       				$categoryTitle = Title::newFromText($categoryDB, NS_CATEGORY);
       				$this->store->removeCategoryLeaves($categoryTitle, $deletedCategories, $visitedNodes);
       				foreach($deletedCategories as $dc) {
       					list($cat, $superCats) = $dc;
       					$leafOf = count($superCats) > 0 ? "(".wfMsg('smw_gard_was_leaf_of')." [[:".$superCats[0]->getPrefixedText()."]])" : "";
       					$this->globalLog .= "\n*[[:".$cat->getPrefixedText()."]] " . $leafOf;
 	     			}
       			}
       		}
       		echo "done!\n";
       	}
 		return $this->globalLog;
 		
 	}
 	
 	private function getAnomalyStore() {
 		global $smwgHaloIP;
		if ($this->store == NULL) {
			global $smwgDefaultStore;
			switch ($smwgDefaultStore) {
				case (SMW_STORE_TESTING):
					$this->store = null; // not implemented yet
					trigger_error('Testing store not implemented for HALO extension.');
				break;
				case (SMW_STORE_MWDB): default:
					
					$this->store = new AnomalyStorageSQL();
				break;
			}
		}
		return $this->store;
 	}
 		
 	
 }
 
 new AnomaliesBot();
 define('SMW_ANOMALY_BOT_BASE', 600);
 define('SMW_GARDISSUE_CATEGORY_LEAF', SMW_ANOMALY_BOT_BASE * 100 + 1);
 define('SMW_GARDISSUE_SUBCATEGORY_ANOMALY', (SMW_ANOMALY_BOT_BASE+1) * 100 + 1);

 
 class AnomaliesBotIssue extends GardeningIssue {
 	
 	public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified) {
 		parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified);
 	}
 	
 	protected function getTextualRepresenation(& $skin, $text1, $text2, $local = false) {
 		$text1 = $local ? wfMsg('smw_gard_issue_local') : $text1;
		switch($this->gi_type) {
			case SMW_GARDISSUE_CATEGORY_LEAF:
				return wfMsg('smw_gardissue_category_leaf', $text1);
			case SMW_GARDISSUE_SUBCATEGORY_ANOMALY:
				return wfMsg('smw_gardissue_subcategory_anomaly', $text1, $this->value);
			default: return NULL;
			
		}
 	}
 }
 
 class AnomaliesBotFilter extends GardeningIssueFilter {
 	 	
 	
 	public function __construct() {
 		parent::__construct(SMW_ANOMALY_BOT_BASE);
 		$this->gi_issue_classes = array(wfMsg('smw_gardissue_class_all'), wfMsg('smw_gardissue_class_category_leaves'), wfMsg('smw_gardissue_class_number_anomalies'));
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
		
		
		$gi_store = SMWGardening::getGardeningIssuesAccess();
		
		$gic = array();
		$gis = $gi_store->getGardeningIssues('smw_anomaliesbot', NULL, $gi_class, $title, SMW_GARDENINGLOG_SORTFORTITLE, NULL);
		$gic[] = new GardeningIssueContainer($title, $gis);
		
		
		return $gic;
	}
 }
 
 abstract class AnomalyStorage {
 	public abstract function getCategoryLeafs($category = NULL);
 	public abstract function getCategoryAnomalies($category = NULL);
 	public abstract function removeCategoryLeaves($category = NULL, array & $deletedCategories, array & $visitedNodes);
 }
 
 class AnomalyStorageSQL extends AnomalyStorage {
 	/**
 	 * Returns all categories which have neither instances nor subcategories.
 	 * 
 	 * @param $category as strings (dbkey)
 	 */
 	public function getCategoryLeafs($category = NULL) {
 		$db =& wfGetDB( DB_MASTER );
 		$mw_page = $db->tableName('page');
	 	$categorylinks = $db->tableName('categorylinks');
 		$result = array();
		if ($category == NULL) { 
			$sql = 'SELECT page_title FROM '.$mw_page.' p LEFT JOIN '.$categorylinks.' c ON p.page_title = c.cl_to WHERE cl_from IS NULL AND page_namespace = '.NS_CATEGORY;
	               
			$res = $db->query($sql);
		
			$result = array();
			if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
				
					$result[] = Title::newFromText($row->page_title, NS_CATEGORY);
				
				}
			}
			$db->freeResult($res);
		} else {
			
				
				$subCats = smwfGetSemanticStore()->getSubCategories($category);
								
				$sql = 'SELECT page_title FROM '.$mw_page.' p LEFT JOIN '.$categorylinks.' c ON p.page_title = c.cl_to WHERE cl_from IS NULL AND page_title = '.$db->addQuotes($category->getDBkey()).' AND page_namespace = '.NS_CATEGORY;
	                
				$res = $db->query($sql);
		
				$result = array();
				if($db->numRows( $res ) > 0) {
					while($row = $db->fetchObject($res)) {
						$result[] = Title::newFromText($row->page_title, NS_CATEGORY);
					}
				}
				$db->freeResult($res);
				
				foreach($subCats as $subCat) { 
					$result = array_merge($this->getCategoryLeafs($subCat), $result);
				}
			
		}
		
		return $result;
 	}
 	
 	/**
 	 * Returns all categories which have less than MIN_SUBCATEGORY_NUM and more than MAX_SUBCATEGORY_NUM subcategories.
 	 */
 	public function getCategoryAnomalies($category = NULL) {
 		$db =& wfGetDB( DB_MASTER );
 		$mw_page = $db->tableName('page');
	 	$categorylinks = $db->tableName('categorylinks');
		$result = array();

		if ($category == NULL) {  		
			$sql = 'SELECT COUNT(cl_from) AS subCatNum, cl_to FROM '.$mw_page.' p, '.$categorylinks.' c WHERE cl_from = page_id AND page_namespace = '.NS_CATEGORY.' GROUP BY cl_to HAVING (COUNT(cl_from) < '.MIN_SUBCATEGORY_NUM.' OR COUNT(cl_from) > '.MAX_SUBCATEGORY_NUM.') ';
		               
			$res = $db->query($sql);
		
			if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
				
					$result[] = array(Title::newFromText($row->cl_to, NS_CATEGORY), $row->subCatNum);
					
				}
			}
		
			$db->freeResult($res);
		} else {
			
				
				$subCats = smwfGetSemanticStore()->getSubCategories($category);
				
				// select all subcategories which have the subcategory-anomaly
				$sql = 'SELECT COUNT(cl_from) AS subCatNum, cl_to FROM '.$mw_page.' p, '.$categorylinks.' c WHERE cl_from = page_id AND page_namespace = '.NS_CATEGORY.' AND cl_to = '.$db->addQuotes($category->getDBkey()).' GROUP BY cl_to HAVING (COUNT(cl_from) < '.MIN_SUBCATEGORY_NUM.' OR COUNT(cl_from) > '.MAX_SUBCATEGORY_NUM.') ';
		               
				$res = $db->query($sql);
		
				if($db->numRows( $res ) > 0) {
					while($row = $db->fetchObject($res)) {
			
						$result[] = array(Title::newFromText($row->cl_to, NS_CATEGORY), $row->subCatNum);
						
					}
				}
		
				$db->freeResult($res);
				
				// check for anomaly in all subcategories
				foreach($subCats as $subCat) { 
					$result = array_merge($this->getCategoryAnomalies($subCat), $result);
				}
			
		}
		return $result;
 	}
 	
 	/**
 	 * Removes all category leaves
 	 */
 	public function removeCategoryLeaves($category = NULL, array & $deletedCategories, array & $visitedNodes) {
 		$db =& wfGetDB( DB_MASTER );
 		$mw_page = $db->tableName('page');
	 	$categorylinks = $db->tableName('categorylinks');
 		if ($category == NULL) {  		
 				
		$sql = 'SELECT DISTINCT page_title FROM '.$mw_page.' p LEFT JOIN '.$categorylinks.' c ON p.page_title = c.cl_to WHERE cl_from IS NULL AND page_namespace = '.NS_CATEGORY. ' ORDER BY page_title';
	               
		$res = $db->query($sql);
		
		
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$categoryTitle = Title::newFromText($row->page_title, NS_CATEGORY);
				$superCatgeories = smwfGetSemanticStore()->getDirectSuperCategories($categoryTitle);
				$deletedCategories[] = array($categoryTitle, $superCatgeories);
				$categoryArticle = new Article($categoryTitle);
				$categoryArticle->doDeleteArticle(wfMsg('smw_gard_category_leaf_deleted', $row->page_title));
			}
		}
		
		$db->freeResult($res);
 		} else {
 				
 				if (in_array($category->getDBkey(), $visitedNodes)) {
 					array_pop($visitedNodes);
 					return;
 				}
 				array_push($visitedNodes, $category->getDBkey());
 								
				$subCats = smwfGetSemanticStore()->getSubCategories($category);
								
				$sql = 'SELECT DISTINCT page_title FROM '.$mw_page.' p LEFT JOIN '.$categorylinks.' c ON p.page_title = c.cl_to WHERE cl_from IS NULL AND page_title = '.$db->addQuotes($category->getDBkey()).' AND page_namespace = '.NS_CATEGORY;
	                
				$res = $db->query($sql);
		
				$result = array();
				if($db->numRows( $res ) > 0) {
					while($row = $db->fetchObject($res)) {
						$categoryTitle = Title::newFromText($row->page_title, NS_CATEGORY);
						$superCatgeories = smwfGetSemanticStore()->getDirectSuperCategories($categoryTitle);
						$deletedCategories[] = array($categoryTitle, $superCatgeories);
						$categoryArticle = new Article($categoryTitle);
						$categoryArticle->doDeleteArticle(wfMsg('smw_gard_category_leaf_deleted', $categoryTitle->getText()));
					}
				}
				$db->freeResult($res);
				
				foreach($subCats as $subCat) { 
					$this->removeCategoryLeaves($subCat, $deletedCategories, $visitedNodes);
				}
 		}
		array_pop($visitedNodes);
 	}
 }
?>