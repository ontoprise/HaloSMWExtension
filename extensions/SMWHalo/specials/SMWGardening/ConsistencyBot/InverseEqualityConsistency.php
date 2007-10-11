<?php
/*
 * Created on 29.05.2007
 *
 * Author: kai
 */
 
 class InverseEqualityConsistency {
 	
 	private $consistencyHelper;
 	private $bot;
 	
 	public function InverseEqualityConsistency(& $bot) {
 		$this->bot = $bot;
 		$this->consistencyHelper = new ConsistencyHelper();
 	}
 	
 	
 	public function checkInverseRelations() {
 		global $smwgContLang;
 		$namespaces = $smwgContLang->getNamespaces();
 		$inverseRelations = $this->getInverseRelations();
 		$log = "";
 		$numLog = 0;
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
 			if ($numLog > MAX_LOG_LENGTH) { print (" limit of consistency issues reached. Break. "); return $log; }
 			list($s, $t) = $r;
 			$domainOfSource = smwfGetStore()->getPropertyValues($s, $this->consistencyHelper->domainHintRelation);
 			$domainOfTagert = smwfGetStore()->getPropertyValues($t, $this->consistencyHelper->domainHintRelation);
 			$rangeOfSource = smwfGetStore()->getPropertyValues($s, $this->consistencyHelper->rangeHintRelation);
 			$rangeOfTarget = smwfGetStore()->getPropertyValues($t, $this->consistencyHelper->rangeHintRelation);
 			
 			if (count($domainOfSource) != 1) {
 				$log .= wfMsg('smw_gard_domain_not_defined', $s->getText(), $namespaces[SMW_NS_PROPERTY])."\n\n";
 				$numLog++;
 				continue;
 			}
 			if (count($domainOfTagert) != 1) {
 				$log .= wfMsg('smw_gard_domain_not_defined', $t->getText(), $namespaces[SMW_NS_PROPERTY])."\n\n";
 				$numLog++;
 				continue;
 			}
 			if (count($rangeOfSource) != 1) {
 				$log .= wfMsg('smw_gard_range_not_defined', $s->getText(), $namespaces[SMW_NS_PROPERTY])."\n\n";
 				$numLog++;
 				continue;
 			}
 			if (count($rangeOfTarget) != 1) {
 				$log .= wfMsg('smw_gard_range_not_defined', $t->getText(), $namespaces[SMW_NS_PROPERTY])."\n\n";
 				$numLog++;
 				continue;
 			}
 			
 			if (!$domainOfSource[0]->getTitle()->equals($rangeOfTarget[0]->getTitle())) {
 				$log .= wfMsg('smw_gard_domain_is_not_range', $s->getText(), $s->getNsText(), $t->getText(), $t->getNsText())."\n\n";
 				$numLog++;
 			} else if (!$domainOfTagert[0]->getTitle()->equals($rangeOfSource[0]->getTitle())) {
 				$log .= wfMsg('smw_gard_domain_is_not_range', $s->getText(), $s->getNsText(), $t->getText(), $t->getNsText())."\n\n";
 				$numLog++;
 				
 			}
 		}
 		return $log;
 	}
 	
 	public function checkEqualToRelations() {
 		$equalToRelations = $this->getEqualToRelations();
 		$log = "";
 		$this->bot->addSubTask(count($equalToRelations));
 		foreach($equalToRelations as $r) {
 			$this->bot->worked(1);
 			list($s, $t) = $r;
 			if ($s->getNamespace() != $t->getNamespace()) {
 				// equality of incompatible entities
 				$log .= wfMsg('smw_gard_incomp_entities_equal', $s->getText(), $s->getNsText(), $t->getText(), $t->getNsText())."\n\n";
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
 						$log .= wfMsg('smw_gard_incomp_entities_equal2', $s->getText(), $s->getNsText(), $t->getText(), $t->getNsText())."\n\n";
 					}
 				}
 				//TODO: check compatibility of domains/ranges/cardinality
 			} 
 			
 			
 		}
 		return $log;
 	}
 	
 	private function getInverseRelations() {
 		$db =& wfGetDB( DB_MASTER );
		$sql = 'relation_title = '.$db->addQuotes($this->consistencyHelper->inverseOf->getDBkey()); 
		
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
