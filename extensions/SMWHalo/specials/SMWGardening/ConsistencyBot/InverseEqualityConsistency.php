<?php
/*
 * Created on 29.05.2007
 *
 * Author: kai
 */
 
 class InverseEqualityConsistency {
 	
 	
 	private $bot;
 	private $gi_store;
 	
 	public function InverseEqualityConsistency(& $bot) {
 		$this->bot = $bot;
 		$this->gi_store = SMWGardening::getGardeningIssuesAccess();
 	}
 	
 	
 	public function checkInverseRelations() {
 		global $smwgContLang;
 		
 		$inverseRelations = $this->getInverseRelations();
 	
 		$work = count($inverseRelations);
 		$cnt = 0;
 		print "\n";
 		$this->bot->addSubTask(count($inverseRelations));
 		foreach($inverseRelations as $r) {
 			$this->bot->worked(1);
 			$cnt++;
 			if ($cnt % 10 == 1 || $cnt == $work) { 
 				print "\x08\x08\x08\x08".number_format($cnt/$work*100, 0)."% ";
 			}
 			
 			list($s, $t) = $r;
 			$domainAndRangeOfSource = smwfGetStore()->getPropertyValues($s, smwfGetSemanticStore()->domainRangeHintRelation);
 			$domainAndRangeOfTarget = smwfGetStore()->getPropertyValues($t, smwfGetSemanticStore()->domainRangeHintRelation);
 			
 			if (count($domainAndRangeOfSource) != 1) {
 				
 				$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_DOMAINS_AND_RANGES_NOT_DEFINED, $s);
 				
 				continue;
 			}
 			if (count($domainAndRangeOfTarget) != 1) {
 				
 				$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_DOMAINS_AND_RANGES_NOT_DEFINED, $t);
 				
 				continue;
 			}
 		 			
 			if (!$domainAndRangeOfSource->getDVs[0]->getTitle()->equals($domainAndRangeOfTarget->getDVs[1]->getTitle())) {
 			
 				$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARD_ISSUE_DOMAIN_NOT_RANGE, $s, $t);
 				
 			} else if (!$domainAndRangeOfSource->getDVs[1]->getTitle()->equals($domainAndRangeOfTarget->getDVs[0]->getTitle())) {
 				
 				$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARD_ISSUE_DOMAIN_NOT_RANGE, $s, $t);
 			}
 		}
 		return ;
 	}
 	
 	public function checkEqualToRelations() {
 		$equalToRelations = $this->getEqualToRelations();
 		
 		$this->bot->addSubTask(count($equalToRelations));
 		foreach($equalToRelations as $r) {
 			$this->bot->worked(1);
 			list($s, $t) = $r;
 			if ($s->getNamespace() != $t->getNamespace()) {
 				// equality of incompatible entities
 				$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARD_ISSUE_INCOMPATIBLE_ENTITY, $s, $t);
 				
 				continue;
 			} else if ($s->getNamespace() == SMW_NS_PROPERTY) {
 				$s_type = smwfGetStore()->getSpecialValues($s, SMW_SP_HAS_TYPE);
 				$t_type = smwfGetStore()->getSpecialValues($t, SMW_SP_HAS_TYPE);
 				if (count($s_type) == 0 && count($t_type) == 0) {
 					// both have wiki page type. this is ok.
 					continue;
 				}
 				if (count($s_type) > 0 && count($t_type) > 0) {
 					if ($s_type[0]->getXSDValue() != $t_type[0]->getXSDValue()) {
 						$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARD_ISSUE_INCOMPATIBLE_TYPE, $s, $t);
 						
 					}
 				}
 				//TODO: check compatibility of domains/ranges/cardinality
 			} 
 			
 			
 		}
 		return '';
 	}
 	
 	private function getInverseRelations() {
 		$db =& wfGetDB( DB_MASTER );
		$sql = 'relation_title = '.$db->addQuotes(smwfGetSemanticStore()->inverseOf->getDBkey()); 
		
		$res = $db->select(  array($db->tableName('smw_relations')), 
		                    array('subject_title', 'object_title'),
		                    $sql, 'SMW::getInverseRelations', NULL);
		                    
		
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = array(Title::newFromText($row->subject_title, SMW_NS_PROPERTY),  Title::newFromText($row->object_title, SMW_NS_PROPERTY));
			}
		}
		
		$db->freeResult($res);
		
		return $result;
 	}
 	
 	private function getEqualToRelations() {
 		//TODO: read partitions of redirects
 		$db =& wfGetDB( DB_MASTER );
		$sql = 'rd_from = page_id'; 
		
		$res = $db->select(  array($db->tableName('redirect'), $db->tableName('page')), 
		                    array('rd_namespace','rd_title', 'page_namespace', 'page_title'),
		                    $sql, 'SMW::getInverseRelations', NULL);
		                    
		
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = array(Title::newFromText($row->rd_title, $row->rd_namespace), Title::newFromText($row->page_title, $row->page_namespace));
			}
		}
		
		$db->freeResult($res);
		
		return $result;
 	}
 	
 	
 }
?>
