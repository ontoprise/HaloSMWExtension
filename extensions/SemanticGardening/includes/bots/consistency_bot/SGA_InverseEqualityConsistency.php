<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @ingroup ConsistencyBot
 * 
 * @author Kai K�hn
 * 
 * Created on 29.05.2007
 *
 */
 
 class InverseEqualityConsistency {
 	
 	
 	private $bot;
 	private $delay;
 	private $gi_store;
 	private $cc_store;
 	
 	public function InverseEqualityConsistency(& $bot, $delay) {
 		$this->bot = $bot;
 		$this->delay = $delay;
 		$this->gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
 		$this->cc_store = ConsitencyBotStorage::getConsistencyStorage();
 	}
 	
 	
 	public function checkInverseRelations() {
 		 		
 		print "\n";
 		$inverseRelations = $this->cc_store->getInverseRelations();
 		$totalWork = count($inverseRelations);
 		$this->bot->addSubTask($totalWork);
 		
 		foreach($inverseRelations as $r) {
 			
 			if ($this->delay > 0) {
 				if ($this->bot->isAborted()) break;
 				usleep($this->delay);
 			}
 			
 			$this->bot->worked(1);
 			$workDone = $this->bot->getCurrentWorkDone();
 			if ($workDone % 10 == 1 || $workDone == $totalWork) GardeningBot::printProgress($workDone/$totalWork);
 			
 			
 			list($s, $t) = $r;
 			$domainAndRangeOfSource = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle($s), 
 				SMWDIProperty::newFromUserLabel(SMWHaloPredefinedPages::$HAS_DOMAIN_AND_RANGE->getText()));
 			$domainAndRangeOfTarget = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle($t), 
 				SMWDIProperty::newFromUserLabel(SMWHaloPredefinedPages::$HAS_DOMAIN_AND_RANGE->getText()));
 			
 			if (count($domainAndRangeOfSource) == 0) {
 				continue;
 			}
 			if (count($domainAndRangeOfTarget) == 0) {
 				continue;
 			}
 		 	
 			$domain_source = $domainAndRangeOfSource[0]->getSemanticData()->getPropertyValues(
 		 		SMWDIProperty::newFromUserLabel('Has domain'));
 		 	$range_target = $domainAndRangeOfTarget[0]->getSemanticData()->getPropertyValues(
 		 		SMWDIProperty::newFromUserLabel('Has range'));
 		 	
 		 	if (count($domain_source) > 0 && count($range_target) > 0 && $domain_source[0] != NULL && $range_target[0] != NULL) {
 		 		if (!$domain_source[0]->getTitle()->equals($range_target[0]->getTitle())) {
 					$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARD_ISSUE_DOMAIN_NOT_RANGE, $s, $t);
 				
 				} 
 		 	}
 		 	
 		 	$domain_target = $domainAndRangeOfTarget[0]->getSemanticData()->getPropertyValues(
 		 		SMWDIProperty::newFromUserLabel('Has domain'));
 		 	$range_source = $domainAndRangeOfSource[0]->getSemanticData()->getPropertyValues(
 		 		SMWDIProperty::newFromUserLabel('Has range'));
 		 	
 		 	if (count($domain_target) > 0 && count($range_source) > 0 && $domain_target[0] != NULL && $range_source[0] != NULL) {
 		 		if (!$domain_target[0]->getTitle()->equals($range_source[0]->getTitle())) {
 					$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARD_ISSUE_DOMAIN_NOT_RANGE, $t, $s);
 				
 				} 
 		 	}  
 		 	
 			
 			
 			
 		}
 		
 	}
 	
 	public function checkEqualToRelations() {
 		$equalToRelations = $this->cc_store->getEqualToRelations();
 		$hasTypeDV = SMWPropertyValue::makeProperty("_TYPE");
 		$this->bot->addSubTask(count($equalToRelations));
 		foreach($equalToRelations as $r) {
 			$this->bot->worked(1);
 			list($s, $t) = $r;
 			if ($s->getNamespace() != $t->getNamespace()) {
 				// equality of incompatible entities
 				$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARD_ISSUE_INCOMPATIBLE_ENTITY, $s, $t);
 				
 				continue;
 			} 
 			
 			
 		}
 		return '';
 	}
 	
 	
 	
 	
 }

