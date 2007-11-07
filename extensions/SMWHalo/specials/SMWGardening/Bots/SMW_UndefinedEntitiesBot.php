<?php
/*
 * Created on 13.06.2007
 *
 * Author: kai
 */
 global $smwgHaloIP;
 require_once("$smwgHaloIP/specials/SMWGardening/SMW_GardeningBot.php");
 require_once("$smwgHaloIP/specials/SMWGardening/SMW_ParameterObjects.php");
 

 
 class UndefinedEntitiesBot extends GardeningBot {
 	 	
 	
 	
 	function UndefinedEntitiesBot() {
 		parent::GardeningBot("smw_undefinedentitiesbot");
 		
 	}
 	
 	public function getHelpText() {
 		return wfMsg('smw_gard_undefinedentities_docu');
 	}
 	
 	public function getLabel() {
 		return wfMsg($this->id);
 	}
 	
 	public function allowedForUserGroups() {
 		return array(SMW_GARD_GARDENERS, SMW_GARD_SYSOPS, SMW_GARD_ALL_USERS);
 	}
 	
 	/**
 	 * Returns an array mapping parameter IDs to parameter objects
 	 */
 	public function createParameters() {
 		return array();
 	}
 	
 	/**
 	 * Do consistency checks and return a log as wiki markup.
 	 * Do not use echo when it is not running asynchronously.
 	 */
 	public function run($paramArray, $isAsync, $delay) {
 		
 		if (!$isAsync) {
 			echo 'Undefined entities bot should not be run synchronously! Abort bot.';
 			return;
 		}
 		echo $this->getBotID()." started!\n";
 		
 	   	echo "Checking for undefined entities...";
        $ued = new UndefinedEntitiesDetector($this->id);
 		
 		$ued->checkForUndefinedCategories();
 		$ued->checkForUndefinedProperties();
 		$ued->checkForUndefinedRelationTargets();
 		$ued->checkForInstancesWithoutCategory();
        echo "done!\n\n";
          
 		
 		return '';
 		
 	}
 	
 	
 }
 
 
  // instantiate it once.
 new UndefinedEntitiesBot();
 define('SMW_UNDEFINED_ENTITIES_BOT_BASE', 300);
 define('SMW_GARDISSUE_INSTANCE_WITHOUT_CAT', SMW_UNDEFINED_ENTITIES_BOT_BASE * 100 + 1);
 define('SMW_GARDISSUE_PROPERTY_UNDEFINED', (SMW_UNDEFINED_ENTITIES_BOT_BASE+1) * 100 + 2);
 define('SMW_GARDISSUE_CATEGORY_UNDEFINED', (SMW_UNDEFINED_ENTITIES_BOT_BASE+2) * 100 + 3);
 define('SMW_GARDISSUE_RELATIONTARGET_UNDEFINED', (SMW_UNDEFINED_ENTITIES_BOT_BASE+3) * 100 + 4);
  
 class UndefinedEntitiesBotIssue extends GardeningIssue {
 	
 	public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value) {
 		parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value);
 	}
 	
 	protected function getTextualRepresenation(& $skin) {
 		if ($this->t1 == "__error__") $text1 = $this->t1; else $text1 = "'".$this->t1->getText()."'";
		switch($this->gi_type) {
			case SMW_GARDISSUE_INSTANCE_WITHOUT_CAT:
				return wfMsg('smw_gardissue_instance_without_cat', $text1, $skin->makeLinkObj($this->t2));
			case SMW_GARDISSUE_PROPERTY_UNDEFINED:
				return wfMsg('smw_gardissue_property_undefined', $text1, $skin->makeLinkObj($this->t2));
			case SMW_GARDISSUE_CATEGORY_UNDEFINED:
				return wfMsg('smw_gardissue_category_undefined', $text1, $skin->makeLinkObj($this->t2));
			case SMW_GARDISSUE_RELATIONTARGET_UNDEFINED:
				return wfMsg('smw_gardissue_relationtarget_undefined', $text1, $skin->makeLinkObj($this->t2));
		}
 	}
 }
 
 class UndefinedEntitiesDetector {
 	
 	private $gi_store;
 	private $bot_id;
 	
 	public function __construct($bot_id) {
 		$this->gi_store = SMWGardening::getGardeningIssuesAccess();
 		$this->bot_id = $bot_id;
 	}
 	
 	public function checkForUndefinedProperties() {
 		 	
 		$undefindProperties = $this->getUndefinedProperties();
 		foreach($undefindProperties as $p) {
 			$articles = $this->getArticlesUsingProperty($p);
 			foreach($articles as $a) { 
 				$this->gi_store->addGardeningIssueAboutArticles($this->bot_id, SMW_GARDISSUE_PROPERTY_UNDEFINED, $p, $a);
 			}
 		}
 		
 	}
 	
 	public function checkForUndefinedCategories() {
 		
 		$undefindCategories = $this->getUndefinedCategories();
 		foreach($undefindCategories as $c) {
 			$articles = $this->getArticlesUsingCategory($c);
 			foreach($articles as $a) { 
 				$this->gi_store->addGardeningIssueAboutArticles($this->bot_id, SMW_GARDISSUE_CATEGORY_UNDEFINED, $c, $a);
 			}
 		}
 		
 	}
 	
 	public function checkForUndefinedRelationTargets() {
 		
 		$undefindRelationTargets = $this->getUndefinedRelationTargets();
 		foreach($undefindRelationTargets as $t) {
 			$articles = $this->getRelationsUsingTarget($t);
 		
 			foreach($articles as $a) { 
 				$this->gi_store->addGardeningIssueAboutArticles($this->bot_id, SMW_GARDISSUE_RELATIONTARGET_UNDEFINED, $t, $a);
 			}
 			
 			
 		}
 		
 	}
 	
 	public function checkForInstancesWithoutCategory() {
 		
 		$instancesWithoutCategory = $this->getInstancesWithoutCategory();
 		foreach($instancesWithoutCategory as $i) {
 			$this->gi_store->addGardeningIssueAboutArticle($this->bot_id, SMW_GARDISSUE_INSTANCE_WITHOUT_CAT, $i);
 			
 		}
 		
 	}
 	
 	private function getUndefinedProperties() {
 		
 		
		$db =& wfGetDB( DB_MASTER );

		// read attributes		                    
		$res = $db->query('SELECT DISTINCT attribute_title FROM smw_attributes a LEFT JOIN page p ON a.attribute_title=p.page_title WHERE p.page_title IS NULL LIMIT '.MAX_LOG_LENGTH);
	
		// read binary relations  		
		$res2 = $db->query('SELECT DISTINCT relation_title FROM smw_relations r LEFT JOIN page p ON r.relation_title=p.page_title WHERE p.page_title IS NULL LIMIT '.MAX_LOG_LENGTH);
		       
		// read n-ary relations  
		$res3 = $db->query('SELECT DISTINCT attribute_title FROM smw_nary r LEFT JOIN page p ON r.attribute_title=p.page_title WHERE p.page_title IS NULL LIMIT '.MAX_LOG_LENGTH);
		
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->attribute_title, SMW_NS_PROPERTY);
				
			}
		}
		if($db->numRows( $res2 ) > 0) {
			while($row = $db->fetchObject($res2)) {
				$result[] = Title::newFromText($row->relation_title, SMW_NS_PROPERTY);
				
			}
		}
		
		if($db->numRows( $res3 ) > 0) {
			while($row = $db->fetchObject($res3)) {
				$result[] = Title::newFromText($row->attribute_title, SMW_NS_PROPERTY);
				
			}
		}
		$db->freeResult($res);
		$db->freeResult($res2);
		$db->freeResult($res3);
		return $result;
 	}
 	
 	private function getArticlesUsingProperty($property) {
 		$db =& wfGetDB( DB_MASTER );

	                  
		$res = $db->query('SELECT DISTINCT subject_title, subject_namespace FROM smw_attributes WHERE attribute_title = '.$db->addQuotes($property->getDBkey()).' UNION ' .
				'SELECT DISTINCT subject_title, subject_namespace FROM smw_relations WHERE relation_title = '.$db->addQuotes($property->getDBkey()).' LIMIT 10');
	
	
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->subject_title, $row->subject_namespace);
				
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	private function getUndefinedCategories() {
 		$db =& wfGetDB( DB_MASTER );
 		
		// inner query: not as fast as a LEFT JOIN
		/*$sql = 'cl_to NOT IN (SELECT page_title FROM page WHERE page_title = cl_to)'; 
		
		$res = $db->select(  array($db->tableName('categorylinks')), 
		                    array('cl_to'),
		                    $sql, 'SMW::getUndefinedCategories', NULL);*/
		                    
		$res = $db->query('SELECT DISTINCT cl_to FROM categorylinks c LEFT JOIN page p ON c.cl_to=p.page_title WHERE p.page_title IS NULL LIMIT '.MAX_LOG_LENGTH);
		                    
		
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				
				$result[] = Title::newFromText($row->cl_to, NS_CATEGORY);
				
			}
		}
		
		$db->freeResult($res);
		return $result;
 	}
 	
 	private function getArticlesUsingCategory($category) {
 		$db =& wfGetDB( DB_MASTER );

	                  
		$res = $db->query('SELECT page_title, page_namespace FROM page,categorylinks WHERE page_id = cl_from AND cl_to = '.$db->addQuotes($category->getDBkey()).' LIMIT 10');
	
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->page_title, $row->page_namespace);
				
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	private function getUndefinedRelationTargets() {
 		$db =& wfGetDB( DB_MASTER );
 	
 		// inner query: not as fast as a LEFT JOIN
		/*$sql = 'object_title NOT IN (SELECT page_title FROM page WHERE page_title = object_title)'; 
		
		$res = $db->select(  array($db->tableName('smw_relations')), 
		                    array('object_title'),
		                    $sql, 'SMW::getUndefinedRelationTargets', NULL);*/
		
		$res = $db->query('SELECT DISTINCT object_title FROM smw_relations r LEFT JOIN page p ON r.object_title=p.page_title WHERE p.page_title IS NULL LIMIT '.MAX_LOG_LENGTH);
		
		$res2 = $db->query('SELECT DISTINCT object_title FROM smw_nary_relations r LEFT JOIN page p ON r.object_title=p.page_title WHERE p.page_title IS NULL LIMIT '.MAX_LOG_LENGTH);
		
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
			
				$result[] = Title::newFromText($row->object_title);
				
			}
		}
		
		if($db->numRows( $res2 ) > 0) {
			while($row = $db->fetchObject($res2)) {
			
				$result[] = Title::newFromText($row->object_title);
				
			}
		}
		
		$db->freeResult($res);
		
		return $result;
 	}
 	
 	private function getRelationsUsingTarget($target) {
 		$db =& wfGetDB( DB_MASTER );

	                  
		$res = $db->query('SELECT DISTINCT relation_title FROM smw_relations WHERE object_title = '.$db->addQuotes($target->getDBkey()).' ' .
				'UNION SELECT DISTINCT attribute_title FROM smw_nary n, smw_nary_relations r WHERE n.subject_id = r.subject_id AND r.object_title =  '.$db->addQuotes($target->getDBkey()).' LIMIT 10');
	
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->relation_title, SMW_NS_PROPERTY);
				
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	private function getInstancesWithoutCategory() {
 		$db =& wfGetDB( DB_MASTER );
 		
		// inner query: not as fast as a LEFT JOIN
		/*$sql = 'page_namespace = '.NS_MAIN.' AND page_id NOT IN (SELECT cl_from FROM categorylinks WHERE page_id = cl_from)'; 
		
		$res = $db->select(  array($db->tableName('page')), 
		                    array('page_title'),
		                    $sql, 'SMW::getInstancesWithoutCategory', NULL);*/
		
		$res = $db->query('SELECT DISTINCT page_title FROM page p LEFT JOIN categorylinks c ON c.cl_from=p.page_id WHERE c.cl_from IS NULL AND p.page_namespace = '.NS_MAIN.' LIMIT '.MAX_LOG_LENGTH);
		                    
		
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
 
 class UndefinedEntitiesBotFilter extends GardeningIssueFilter {
 	 	
 	
 	public function __construct() {
 		parent::__construct(SMW_UNDEFINED_ENTITIES_BOT_BASE);
 		$this->gi_issue_classes = array(wfMsg('smw_gardissue_class_all'),
						wfMsg('smw_gardissue_class_instances_without_cat'), 
						wfMsg('smw_gardissue_class_undef_properties'), 
						wfMsg('smw_gardissue_class_undef_categories'), 
						wfMsg('smw_gardissue_class_undef_relationtargets'));
 	}
 	
 	public function getUserFilterControls($specialAttPage, $request) {
		return '';
	}
	
	
	public function getData($options, $request) {
		
		return parent::getData($options, $request);
	}
 }
?>
