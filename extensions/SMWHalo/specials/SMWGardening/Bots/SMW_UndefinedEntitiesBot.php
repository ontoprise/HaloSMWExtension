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
 		$param1 = new GardeningParamBoolean('REMOVE_UNDEFINED_CATEGORIES', wfMsg('smw_gard_remove_undefined_categories'), SMW_GARD_PARAM_OPTIONAL, false );
 		return array($param1);
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
 		
 	   	echo "\n";
        $ued = new UndefinedEntitiesDetector($this->id);
 		
 		$removeCategoryAnnotations = array_key_exists('REMOVE_UNDEFINED_CATEGORIES', $paramArray);
 		echo "\ncheck for undefiend categories...";
 		$ued->checkForUndefinedCategories($delay, $removeCategoryAnnotations);
 		echo "done!";
 		
 		echo "\ncheck for undefined properties...";
 		$ued->checkForUndefinedProperties();
 		echo "done!";
 		
 		echo "\ncheck for undefined relation targets...";
 		$ued->checkForUndefinedRelationTargets();
 		echo "done!";
 		
 		echo "\ncheck for instances without categories...";
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
 	
 	public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified) {
 		parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified);
 	}
 	
 	protected function getTextualRepresenation(& $skin, $text1, $text2) {
 	
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
 	private $store;
 	private $bot_id;
 	
 	public function __construct($bot_id) {
 		$this->gi_store = SMWGardening::getGardeningIssuesAccess();
 		$this->bot_id = $bot_id;
 		$this->store = $this->getUndefinedEntitiesStorage();
 	}
 	
 	public function checkForUndefinedProperties() {
 		 	
 		$undefindProperties = $this->store->getUndefinedProperties();
 		foreach($undefindProperties as $p) {
 			$articles = $this->store->getArticlesUsingProperty($p, 10);
 			foreach($articles as $a) { 
 				$this->gi_store->addGardeningIssueAboutArticles($this->bot_id, SMW_GARDISSUE_PROPERTY_UNDEFINED, $p, $a);
 			}
 		}
 		
 	}
 	
 	public function checkForUndefinedCategories($delay, $removeCategoryAnnotations) {
 		
 		$undefindCategories = $this->store->getUndefinedCategories();
 		if ($removeCategoryAnnotations) {
 			foreach($undefindCategories as $c) {
 				if ($delay > 0) usleep($delay*1000);
 				// get all articles using this category
 				$articlesUsingCategory = $this->store->getArticlesUsingCategory($c, 0);
 				foreach($articlesUsingCategory as $t) {
 					$this->removeCategoryLink($t, $c);
 					echo "\n - Remove category link to ".$c->getPrefixedText()." from ".$t->getPrefixedText();
 				}
 				echo "\n";
 			}
 		} else {
	 		foreach($undefindCategories as $c) {
	 			$articles = $this->store->getArticlesUsingCategory($c, 10);
	 			foreach($articles as $a) { 
	 				$this->gi_store->addGardeningIssueAboutArticles($this->bot_id, SMW_GARDISSUE_CATEGORY_UNDEFINED, $c, $a);
	 			}
	 		}
 		}
 	}
 	
 	private function removeCategoryLink($title, $category) {
 		$rev = Revision::newFromTitle($title);
 		$a = new Article($title);
 		if ($rev == NULL || $a == NULL) return;
 		$text = $rev->getText();
 		$newText = preg_replace("/\[\[\s*".$category->getNsText()."\s*:\s*".preg_quote($category->getText())."\s*\]\]/i", "", $text);
 		$a->doEdit($newText, $rev->getComment(), EDIT_UPDATE);
 	}
 	
 	public function checkForUndefinedRelationTargets() {
 		
 		$undefindRelationTargets = $this->store->getUndefinedRelationTargets();
 		foreach($undefindRelationTargets as $t) {
 			$articles = $this->store->getRelationsUsingTarget($t, 10);
 		
 			foreach($articles as $a) { 
 				$this->gi_store->addGardeningIssueAboutArticles($this->bot_id, SMW_GARDISSUE_RELATIONTARGET_UNDEFINED, $t, $a);
 			}
 			
 			
 		}
 		
 	}
 	
 	public function checkForInstancesWithoutCategory() {
 		
 		$instancesWithoutCategory = $this->store->getInstancesWithoutCategory();
 		foreach($instancesWithoutCategory as $i) {
 			$this->gi_store->addGardeningIssueAboutArticle($this->bot_id, SMW_GARDISSUE_INSTANCE_WITHOUT_CAT, $i);
 			
 		}
 		
 	}
 	
 	private function getUndefinedEntitiesStorage() {
 		global $smwgHaloIP;
		if ($this->store == NULL) {
			global $smwgDefaultStore;
			switch ($smwgDefaultStore) {
				case (SMW_STORE_TESTING):
					$this->store = null; // not implemented yet
					trigger_error('Testing store not implemented for HALO extension.');
				break;
				case (SMW_STORE_MWDB): default:
					
					$this->store = new UndefinedEntitiesStorageSQL();
				break;
			}
		}
		return $this->store;
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
		$gis = $gi_store->getGardeningIssues('smw_undefinedentitiesbot', NULL, $gi_class, $title, SMW_GARDENINGLOG_SORTFORTITLE, NULL);
		$gic[] = new GardeningIssueContainer($title, $gis);
		
		
		return $gic;
	}
 }
 
 abstract class UndefinedEntitiesStorage {
 	
 	/**
 	 * Returns undefined properties
 	 * 
 	 * @return array of Title
 	 */
 	public abstract function getUndefinedProperties();
 	
 	/**
 	 * Returns undefined categories
 	 * 
 	 * @return array of Title
 	 */
 	public abstract function getUndefinedCategories();
 	
 	/**
 	 * Returns undefined relation targets (binary or n-ary)
 	 * 
 	 * @return array of Title
 	 */
 	public abstract function getUndefinedRelationTargets();
 	
 	/**
 	 * Returns all instances without any categories
 	 * 
 	 * @return array of Title
 	 */
 	public abstract function getInstancesWithoutCategory();
 	
 	/**
 	 * Returns all articles which uses a given property
 	 * 
 	 * @param $property Title
 	 * @param $limit Limit of max results (or 0 if unlimited)
 	 * @return array of Title
 	 */
 	public abstract function getArticlesUsingProperty($property, $limit = 0);
 	
 	
 	/**
 	 * Returns all articles which uses a given category
 	 * 
 	 * @param $category Title
 	 * @param $limit Limit of max results (or 0 if unlimited)
 	 * @return array of Title
 	 */
 	public abstract function getArticlesUsingCategory($category, $limit = 0);
 	
 	/**
 	 * Returns all relations (binary or n-ary) which uses a given target instance
 	 * 
 	 * @param $target Title
 	 * @param $limit Limit of max results (or 0 if unlimited)
 	 * @return array of Title
 	 */
 	public abstract function getRelationsUsingTarget($target, $limit = 0); 
 	
 }
 
 class UndefinedEntitiesStorageSQL extends UndefinedEntitiesStorage {
 	public function getUndefinedProperties() {
 		
 		
		$db =& wfGetDB( DB_MASTER );

		// read attributes		                    
		$res = $db->query('SELECT DISTINCT attribute_title FROM smw_attributes a LEFT JOIN page p ON a.attribute_title=p.page_title AND p.page_namespace = '.SMW_NS_PROPERTY.' WHERE p.page_title IS NULL');
	
		// read binary relations  		
		$res2 = $db->query('SELECT DISTINCT relation_title FROM smw_relations r LEFT JOIN page p ON r.relation_title=p.page_title AND p.page_namespace = '.SMW_NS_PROPERTY.' WHERE p.page_title IS NULL');
		       
		// read n-ary relations  
		$res3 = $db->query('SELECT DISTINCT attribute_title FROM smw_nary r LEFT JOIN page p ON r.attribute_title=p.page_title AND p.page_namespace = '.SMW_NS_PROPERTY.' WHERE p.page_title IS NULL');
		
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
 	
 	public function getArticlesUsingProperty($property, $limit = 0) {
 		$db =& wfGetDB( DB_MASTER );

	    $limitConstraint =  $limit == 0 ? ''  : 'LIMIT '.$limit;                 
		$res = $db->query('SELECT DISTINCT subject_title, subject_namespace FROM smw_attributes WHERE attribute_title = '.$db->addQuotes($property->getDBkey()).' UNION DISTINCT ' .
				'SELECT DISTINCT subject_title, subject_namespace FROM smw_relations WHERE relation_title = '.$db->addQuotes($property->getDBkey()).' UNION DISTINCT ' .
				'SELECT DISTINCT subject_title, subject_namespace FROM smw_nary_attributes a JOIN smw_nary n ON n.subject_id = a.subject_id WHERE n.attribute_title = '.$db->addQuotes($property->getDBkey()).' UNION DISTINCT ' .
				'SELECT DISTINCT subject_title, subject_namespace FROM smw_nary_relations r JOIN smw_nary n ON n.subject_id = r.subject_id WHERE n.attribute_title = '.$db->addQuotes($property->getDBkey()).$limitConstraint);
	
	
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->subject_title, $row->subject_namespace);
				
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	public function getUndefinedCategories() {
 		$db =& wfGetDB( DB_MASTER );
 		
		// inner query: not as fast as a LEFT JOIN
		/*$sql = 'cl_to NOT IN (SELECT page_title FROM page WHERE page_title = cl_to)'; 
		
		$res = $db->select(  array($db->tableName('categorylinks')), 
		                    array('cl_to'),
		                    $sql, 'SMW::getUndefinedCategories', NULL);*/
		                    
		$res = $db->query('SELECT DISTINCT cl_to FROM categorylinks c LEFT JOIN page p ON c.cl_to=p.page_title AND p.page_namespace = '.NS_CATEGORY.' WHERE p.page_title IS NULL');
		                    
		
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				
				$result[] = Title::newFromText($row->cl_to, NS_CATEGORY);
				
			}
		}
		
		$db->freeResult($res);
		return $result;
 	}
 	
 	public function getArticlesUsingCategory($category, $limit = 0) {
 		$db =& wfGetDB( DB_MASTER );

	    $limitConstraint =  $limit == 0 ? ''  : 'LIMIT '.$limit;            
		$res = $db->query('SELECT page_title, page_namespace FROM page,categorylinks WHERE page_namespace = '.NS_MAIN.' AND page_id = cl_from AND cl_to = '.$db->addQuotes($category->getDBkey()).' '.$limitConstraint);
	
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->page_title, $row->page_namespace);
				
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	public function getUndefinedRelationTargets() {
 		$db =& wfGetDB( DB_MASTER );
 	
 		// inner query: not as fast as a LEFT JOIN
		/*$sql = 'object_title NOT IN (SELECT page_title FROM page WHERE page_title = object_title)'; 
		
		$res = $db->select(  array($db->tableName('smw_relations')), 
		                    array('object_title'),
		                    $sql, 'SMW::getUndefinedRelationTargets', NULL);*/
		
		$res = $db->query('SELECT DISTINCT object_title FROM smw_relations r LEFT JOIN page p ON r.object_title=p.page_title AND p.page_namespace = '.NS_MAIN.' WHERE p.page_title IS NULL');
		
		$res2 = $db->query('SELECT DISTINCT object_title FROM smw_nary_relations r LEFT JOIN page p ON r.object_title=p.page_title AND p.page_namespace = '.NS_MAIN.' WHERE p.page_title IS NULL');
		
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
 	
 	public function getRelationsUsingTarget($target, $limit = 0) {
 		$db =& wfGetDB( DB_MASTER );

	    $limitConstraint =  $limit == 0 ? ''  : 'LIMIT '.$limit;                 
		$res = $db->query('SELECT DISTINCT relation_title FROM smw_relations WHERE object_title = '.$db->addQuotes($target->getDBkey()).' ' .
				'UNION SELECT DISTINCT attribute_title FROM smw_nary n, smw_nary_relations r WHERE n.subject_id = r.subject_id AND r.object_title =  '.$db->addQuotes($target->getDBkey()).' '.$limitConstraint);
	
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = Title::newFromText($row->relation_title, SMW_NS_PROPERTY);
				
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	public function getInstancesWithoutCategory() {
 		$db =& wfGetDB( DB_MASTER );
 		
		// inner query: not as fast as a LEFT JOIN
		/*$sql = 'page_namespace = '.NS_MAIN.' AND page_id NOT IN (SELECT cl_from FROM categorylinks WHERE page_id = cl_from)'; 
		
		$res = $db->select(  array($db->tableName('page')), 
		                    array('page_title'),
		                    $sql, 'SMW::getInstancesWithoutCategory', NULL);*/
		
		$res = $db->query('SELECT DISTINCT page_title FROM page p LEFT JOIN categorylinks c ON c.cl_from=p.page_id AND p.page_namespace = '.NS_MAIN.' WHERE c.cl_from IS NULL');
		                    
		
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
?>
