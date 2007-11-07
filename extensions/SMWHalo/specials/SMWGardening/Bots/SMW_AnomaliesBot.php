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
 	
 	function AnomaliesBot() {
 		parent::GardeningBot("smw_anomaliesbot");
 		$this->globalLog = "== ".wfMsg('smw_gard_anomalylog')."! ==\n\n";
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
 		
       	$catNS = $wgLang->getNsText(NS_CATEGORY);
 		
 		if (array_key_exists('CATEGORY_LEAF_ANOMALY', $paramArray)) {  
 			echo "Checking for category leafs...\n";
 			if ($paramArray['CATEGORY_RESTRICTION'] == '') {
       			$categoryLeaves = $this->getCategoryLeafs();
       	
       			
       			foreach($categoryLeaves as $cl) {
       				$gi_store->addGardeningIssueAboutArticle($this->id, SMW_GARDISSUE_CATEGORY_LEAF, $cl);
       			
       				echo $catNS.":".$cl->getText()."\n";
       			} 
 			} else {
 				$categories = explode(";", urldecode($paramArray['CATEGORY_RESTRICTION']));
 				$categoryLeaves = array();
 				
 				foreach($categories as $c) {
 					$categoryDB = str_replace(" ", "_", trim($c));
 					$categoryTitle = Title::newFromText($categoryDB, NS_CATEGORY);
 					$categoryLeaves = $this->getCategoryLeafs($categoryTitle);
 				
 					foreach($categoryLeaves as $cl) {
 						$gi_store->addGardeningIssueAboutArticle($this->id, SMW_GARDISSUE_CATEGORY_LEAF, $cl);
       				
       					echo $catNS.":".$cl->getText()."\n";
       				} 
 				}
       			
 			}		
       	 echo "done!\n";
 	   		
 		}
       	
       	if (array_key_exists('CATEGORY_NUMBER_ANOMALY', $paramArray)) {  	
       		echo "\nChecking for number anomalies...\n";
       		if ($paramArray['CATEGORY_RESTRICTION'] == '') {
       			
        		$subCatAnomalies = $this->getCategoryAnomalies();
       			
       	
       			
       			foreach($subCatAnomalies as $a) {
       				list($title, $subCatNum) = $a;
       				$gi_store->addGardeningIssueAboutValue($this->id, SMW_GARDISSUE_SUBCATEGORY_ANOMALY, $title, $subCatNum);
       				
       				echo $catNS.":".$title->getText()." has $subCatNum ".($subCatNum == 1 ? "subcategory" : "subcategories").".\n";
       			} 
       		} else {
       			
       			$categories = explode(";", urldecode($paramArray['CATEGORY_RESTRICTION']));
 				$categoryLeaves = array();
 			
 				foreach($categories as $c) {
 					$categoryDB = str_replace(" ", "_", trim($c));
 					$categoryTitle = Title::newFromText($categoryDB, NS_CATEGORY);
 					$subCatAnomalies = $this->getCategoryAnomalies($categoryTitle);
 					foreach($subCatAnomalies as $a) {
       					list($title, $subCatNum) = $a;
       					$gi_store->addGardeningIssueAboutValue($this->id, SMW_GARDISSUE_SUBCATEGORY_ANOMALY, $title, $subCatNum);
       				
       					echo $catNS.":".$title->getText()." has $subCatNum ".($subCatNum == 1 ? "subcategory" : "subcategories").".\n";
       				} 
 				}
       		}
       		echo "done!\n";
       	}
       	
       	if (array_key_exists('CATEGORY_LEAF_DELETE', $paramArray)) {
       		echo "\nRemoving category leaves...\n";
       		if ($paramArray['CATEGORY_RESTRICTION'] == '') {
        		$this->removeCategoryLeaves();
       			$this->globalLog .= "\n".wfMsg('smw_gard_all_category_leaves_deleted');
       		} else {
       			$categories = explode(";", urldecode($paramArray['CATEGORY_RESTRICTION']));
       			foreach($categories as $c) {
       				$categoryDB = str_replace(" ", "_", trim($c));
       				$categoryTitle = Title::newFromText($categoryDB, NS_CATEGORY);
       				$this->removeCategoryLeaves($categoryTitle);
           			$this->globalLog .= "\n".wfMsg('smw_gard_category_leaves_deleted', $catNS, $c);
       			}
       		}
       		echo "done!\n";
       	}
 		return $this->globalLog;
 		
 	}
 	
 	/**
 	 * Returns all categories which have neither instances nor subcategories.
 	 * 
 	 * @param $category as strings (dbkey)
 	 */
 	private function getCategoryLeafs($category = NULL) {
 		$db =& wfGetDB( DB_MASTER );
 		$mw_page = $db->tableName('page');
	 	$categorylinks = $db->tableName('categorylinks');
 		$result = array();
		if ($category == NULL) { 
			$sql = 'SELECT page_title FROM '.$mw_page.' p LEFT JOIN '.$categorylinks.' c ON p.page_title = c.cl_to WHERE cl_from IS NULL AND page_namespace = '.NS_CATEGORY. ' LIMIT '.MAX_LOG_LENGTH;
	               
			$res = $db->query($sql);
		
			$result = array();
			if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
				
					$result[] = Title::newFromText($row->page_title, NS_CATEGORY);
				
				}
			}
			$db->freeResult($res);
		} else {
			
				
				$subCats = $this->getSubCategories($category);
								
				$sql = 'SELECT page_title FROM '.$mw_page.' p LEFT JOIN '.$categorylinks.' c ON p.page_title = c.cl_to WHERE cl_from IS NULL AND page_title = '.$db->addQuotes($category->getDBkey()).' AND page_namespace = '.NS_CATEGORY. ' LIMIT '.MAX_LOG_LENGTH;
	                
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
 	private function getCategoryAnomalies($category = NULL) {
 		$db =& wfGetDB( DB_MASTER );
 		$mw_page = $db->tableName('page');
	 	$categorylinks = $db->tableName('categorylinks');
		$result = array();

		if ($category == NULL) {  		
			$sql = 'SELECT COUNT(cl_from) AS subCatNum, cl_to FROM '.$mw_page.' p, '.$categorylinks.' c WHERE cl_from = page_id AND page_namespace = '.NS_CATEGORY.' GROUP BY cl_to HAVING (COUNT(cl_from) < '.MIN_SUBCATEGORY_NUM.' OR COUNT(cl_from) > '.MAX_SUBCATEGORY_NUM.') LIMIT '.MAX_LOG_LENGTH;
		               
			$res = $db->query($sql);
		
			if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
				
					$result[] = array(Title::newFromText($row->cl_to, NS_CATEGORY), $row->subCatNum);
					
				}
			}
		
			$db->freeResult($res);
		} else {
			
				
				$subCats = $this->getSubCategories($category);
				
				// select all subcategories which have the subcategory-anomaly
				$sql = 'SELECT COUNT(cl_from) AS subCatNum, cl_to FROM '.$mw_page.' p, '.$categorylinks.' c WHERE cl_from = page_id AND page_namespace = '.NS_CATEGORY.' AND cl_to = '.$db->addQuotes($category->getDBkey()).' GROUP BY cl_to HAVING (COUNT(cl_from) < '.MIN_SUBCATEGORY_NUM.' OR COUNT(cl_from) > '.MAX_SUBCATEGORY_NUM.') LIMIT '.MAX_LOG_LENGTH;
		               
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
 	private function removeCategoryLeaves($category = NULL) {
 		$db =& wfGetDB( DB_MASTER );
 		$mw_page = $db->tableName('page');
	 	$categorylinks = $db->tableName('categorylinks');
 		if ($category == NULL) {  		
 				
		$sql = 'SELECT page_title FROM '.$mw_page.' p LEFT JOIN '.$categorylinks.' c ON p.page_title = c.cl_to WHERE cl_from IS NULL AND page_namespace = '.NS_CATEGORY;
	               
		$res = $db->query($sql);
		
		
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$categoryTitle = Title::newFromText($row->page_title, NS_CATEGORY);
				$categoryArticle = new Article($categoryTitle);
				$categoryArticle->doDeleteArticle(wfMsg('smw_gard_category_leaf_deleted', $row->page_title));
			}
		}
		
		$db->freeResult($res);
 		} else {
 								
				$subCats = $this->getSubCategories($category);
								
				$sql = 'SELECT page_title FROM '.$mw_page.' p LEFT JOIN '.$categorylinks.' c ON p.page_title = c.cl_to WHERE cl_from IS NULL AND page_title = '.$db->addQuotes($category->getDBkey()).' AND page_namespace = '.NS_CATEGORY. ' LIMIT '.MAX_LOG_LENGTH;
	                
				$res = $db->query($sql);
		
				$result = array();
				if($db->numRows( $res ) > 0) {
					while($row = $db->fetchObject($res)) {
						$categoryTitle = Title::newFromText($row->page_title, NS_CATEGORY);
						$categoryArticle = new Article($categoryTitle);
						$categoryArticle->doDeleteArticle(wfMsg('smw_gard_category_leaf_deleted', $categoryTitle->getText()));
					}
				}
				$db->freeResult($res);
				
				foreach($subCats as $subCat) { 
					$this->removeCategoryLeaves($subCat);
				}
 		}
		
 	}
 	
 	private function getNamespaceText($page) {
 		global $smwgContLang, $wgLang;
 		$nsArray = $smwgContLang->getNamespaces();
 		if ($page->getNamespace() == NS_TEMPLATE || $page->getNamespace() == NS_CATEGORY) {
 			$ns = $wgLang->getNsText($page->getNamespace());
 				} else { 
 			$ns = $page->getNamespace() != NS_MAIN ? $nsArray[$page->getNamespace()] : "";
 		}
 		return $ns;
 	}
 	
 	private function getDirectSubCategories(Title $categoryTitle, $requestoptions = NULL) {
		$result = "";
		$db =& wfGetDB( DB_MASTER );
		$sql = 'page_namespace=' . NS_CATEGORY .
			   ' AND cl_to =' . $db->addQuotes($categoryTitle->getDBkey()) . ' AND cl_from = page_id';

		$res = $db->select(  array($db->tableName('page'), $db->tableName('categorylinks')), 
		                    'page_title',
		                    $sql, 'SMW::getDirectSubCategories', NULL );
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->page_title, NS_CATEGORY);
			}
		}
		$db->freeResult($res);
		return $result;
	}
	
	private function getSubCategories(Title $category) {
		$subCategories = $this->getDirectSubCategories($category);
		$result = array();
		foreach($subCategories as $subCat) {
			$result = array_merge($result, $this->getDirectSubCategories($subCat));
		}
		return array_merge($result, $subCategories);
	}
 }
 
 new AnomaliesBot();
 define('SMW_ANOMALY_BOT_BASE', 600);
 define('SMW_GARDISSUE_CATEGORY_LEAF', SMW_ANOMALY_BOT_BASE * 100 + 1);
 define('SMW_GARDISSUE_SUBCATEGORY_ANOMALY', SMW_ANOMALY_BOT_BASE * 100 + 2);

 
 class AnomaliesBotIssue extends GardeningIssue {
 	
 	public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value) {
 		parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value);
 	}
 	
 	protected function getTextualRepresenation(& $skin) {
 		if ($this->t1 == "__error__") $text1 = $this->t1; else $text1 = "'".$this->t1->getText()."'";
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
	
	
	public function getData($options, $request) {
		return parent::getData($options, $request);
	}
 }
?>