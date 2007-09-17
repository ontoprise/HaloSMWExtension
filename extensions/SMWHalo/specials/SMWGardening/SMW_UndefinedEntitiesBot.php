<?php
/*
 * Created on 13.06.2007
 *
 * Author: kai
 */
 require_once("SMW_GardeningBot.php");
 
 

 
 class UndefinedEntitiesBot extends GardeningBot {
 	
 	// global log which contains wiki-markup
 	private $globalLog;
 	
 	function UndefinedEntitiesBot() {
 		parent::GardeningBot("smw_undefinedentitiesbot");
 		$this->globalLog = "== The following entities are used in the wiki but not defined: ==\n\n";
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
        $log = $this->checkForUndefinedEntities();
        echo "done!\n\n";
         if ($log != '') {
        	$errors = true;
        	$this->globalLog .=  $log;
        }
        
 		
 		return $this->globalLog;
 		
 	}
 	
 	 	
 	private function checkForUndefinedEntities() {
 		$log = "";
 		$ued = new UndefinedEntitiesDetector();
 		$unc .= $ued->checkForUndefinedCategories();
 		if ($unc != '') {
 			$log .= "== ".wfMsg('smw_gard_errortype_undefined_categories')." ==\n".$unc."----\n";
 		}
 		$unp .= $ued->checkForUndefinedProperties();
 		if ($unp != '') {
 			$log .= "== ".wfMsg('smw_gard_errortype_undefined_properties')." ==\n".$unp."----\n";
 		}
 		$unrt .= $ued->checkForUndefinedRelationTargets();
 		if ($unrt != '') {
 			$log .= "== ".wfMsg('smw_gard_errortype_undefined_relationtargets')." ==\n".$unrt."----\n";
 		}
 		$iwc .= $ued->checkForInstancesWithoutCategory();
 		if ($iwc != '') {
 			$log .= "== ".wfMsg('smw_gard_errortype_instances_without_category')." ==\n".$iwc."----\n";
 		}
 		return $log;
 	}
 	
 	
 	
 	
 }
 
 
  // instantiate it once.
 new UndefinedEntitiesBot();
 
 class UndefinedEntitiesDetector {
 	
 	
 	
 	public function checkForUndefinedProperties() {
 		global $smwgContLang;
 		$namespaces = $smwgContLang->getNamespaceArray();
 		$log = "";
 		$undefindProperties = $this->getUndefinedProperties();
 		foreach($undefindProperties as $p) {
 			$articles = $this->getArticlesUsingProperty($p);
 			$usedIn = "(";
 			for($i = 0, $n = count($articles); $i < $n; $i++) { 
 				$ns = $this->getNamespaceText($articles[$i]) != '' ? $this->getNamespaceText($articles[$i]).":" : "";
 				if ($i < $n-1) { 
 					$usedIn .= "[[$ns".$articles[$i]->getText()."]],";
 				} else {
 					$usedIn .= "[[$ns".$articles[$i]->getText()."]]";
 				}
 			}
 			$usedIn .= ")";
 			$log .= wfMsg('smw_gard_property_undefined', $p->getText(), $namespaces[$p->getNamespace()], $usedIn)."\n\n";
 		}
 		return $log;
 	}
 	
 	public function checkForUndefinedCategories() {
 		$log = "";
 		$undefindCategories = $this->getUndefinedCategories();
 		foreach($undefindCategories as $c) {
 			$articles = $this->getArticlesUsingCategory($c);
 			$usedIn = "(";
 			for($i = 0, $n = count($articles); $i < $n; $i++) { 
 				$ns = $this->getNamespaceText($articles[$i]) != '' ? $this->getNamespaceText($articles[$i]).":" : "";
 				if ($i < $n-1) { 
 					$usedIn .= "[[$ns".$articles[$i]->getText()."]],";
 				} else {
 					$usedIn .= "[[$ns".$articles[$i]->getText()."]]";
 				}
 			}
 			$usedIn .= ")";
 			$log .= wfMsg('smw_gard_category_undefined', $c->getText(), $usedIn)."\n\n";
 		}
 		return $log;
 	}
 	
 	public function checkForUndefinedRelationTargets() {
 		$log = "";
 		$undefindRelationTargets = $this->getUndefinedRelationTargets();
 		foreach($undefindRelationTargets as $t) {
 			$articles = $this->getRelationsUsingTarget($t);
 			$usedIn = "(";
 			for($i = 0, $n = count($articles); $i < $n; $i++) { 
 				$ns = $this->getNamespaceText($articles[$i]) != '' ? $this->getNamespaceText($articles[$i]).":" : "";
 				if ($i < $n-1) { 
 					$usedIn .= "[[$ns".$articles[$i]->getText()."]],";
 				} else {
 					$usedIn .= "[[$ns".$articles[$i]->getText()."]]";
 				}
 			}
 			$usedIn .= ")";
 			$log .= wfMsg('smw_gard_relationtarget_undefined', $t->getText(), $usedIn)."\n\n";
 		}
 		return $log;
 	}
 	
 	public function checkForInstancesWithoutCategory() {
 		$log = "";
 		$instancesWithoutCategory = $this->getInstancesWithoutCategory();
 		foreach($instancesWithoutCategory as $i) {
 			$log .= wfMsg('smw_gard_instances_without_category', $i->getText())."\n\n";
 		}
 		return $log;
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
 	
 	private function getNamespaceText($page) {
 		global $smwgContLang, $wgLang;
 		$nsArray = $smwgContLang->getNamespaceArray();
 		if ($page->getNamespace() == NS_TEMPLATE || $page->getNamespace() == NS_CATEGORY) {
 			$ns = $wgLang->getNsText($page->getNamespace());
 				} else { 
 			$ns = $page->getNamespace() != NS_MAIN ? $nsArray[$page->getNamespace()] : "";
 		}
 		return $ns;
 	}
 }
?>
