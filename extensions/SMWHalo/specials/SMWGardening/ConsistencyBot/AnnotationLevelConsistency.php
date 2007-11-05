<?php
/*
 * Created on 23.05.2007
 *
 * Author: kai
 */
global $smwgHaloIP;
require_once("GraphEdge.php"); 
require_once("$smwgHaloIP/includes/SMW_GraphHelper.php"); 

 class AnnotationLevelConsistency {
 	 	
 	private $bot;
 	private $delay; 
 	
 	// Category Graph. It is cached for the whole consistency checks.
 	private $categoryGraph;
 	private $propertyGraph;
 	private $gi_store;
 	
 	// Important: Attribute values (primitives) are always syntactically 
 	// correct when they are in the database. So only relations
 	// will be checked.
 	
 	public function AnnotationLevelConsistency(& $bot, $delay) {
 		$this->bot = $bot;
 		$this->delay = $delay;
 		
 		$this->categoryGraph = smwfGetSemanticStore()->getCategoryInheritanceGraph();
 		$this->propertyGraph = smwfGetSemanticStore()->getPropertyInheritanceGraph();
 		$this->gi_store = SMWGardening::getGardeningIssuesAccess();
 	}
 	/**
 	 * Checks if property annotations uses schema consistent values
 	 */
 	public function checkPropertyAnnotations() {
 		global $smwgContLang;
 	 		
 		$properties = smwfGetSemanticStore()->getPages(array(SMW_NS_PROPERTY));
 		
 		$work = count($properties);
 		$cnt = 0;
 		print "\n";
 		$this->bot->addSubTask(count($properties));
 		foreach($properties as $r) {
 			if ($this->delay > 0) {
 				usleep($this->delay);
 			}
 			$this->bot->worked(1);
 			$cnt++;
 			if ($cnt % 10 == 1 || $cnt == $work) { 
 				print "\x08\x08\x08\x08".number_format($cnt/$work*100, 0)."% ";
 			}
 			
 			if (smwfGetSemanticStore()->domainRangeHintRelation->equals($r) 
 					|| smwfGetSemanticStore()->minCard->equals($r) 
 					|| smwfGetSemanticStore()->maxCard->equals($r)
 					|| smwfGetSemanticStore()->inverseOf->equals($r)) {
 						// ignore builtin properties
 						continue;
 			}
 			
 			// get domain and range categories of property
 			$domainRangeAnnotations = smwfGetStore()->getPropertyValues($r, smwfGetSemanticStore()->domainRangeHintRelation);
 			
 			
 			if (empty($domainRangeAnnotations)) {
 				// if there are no range categories defined, try to find a super relation with defined range categories
 				$domainRangeAnnotations = smwfGetSemanticStore()->getDomainsAndRangesOfSuperProperty($this->propertyGraph, $r);
 			}
 			
 			// get annotation subjects for the property.
 			$allRelationSubjects = smwfGetStore()->getAllPropertySubjects($r);
 			
 			// check domain only once.
 			$domainChecked = false;
 			
 			// iterate over all property subjects
 			foreach($allRelationSubjects as $subject) { 
 				
 				if ($subject == null) {
 					continue;
 				}
 				// get property value for a given instance
 				$relationTargets = smwfGetStore()->getPropertyValues($subject, $r);
 				
 				foreach($relationTargets as $target) {
 					
 					// decide which type and do consistency checks
 					if ($target instanceof SMWWikiPageValue) {  // binary relation 
 						list($domainCorrect, $rangeCorrect) = $this->checkDomainAndRange($subject, $target->getTitle(), $domainRangeAnnotations, $domainChecked);
 						$domainChecked = true;
 						if (!$domainCorrect) {
 							$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARDISSUE_WRONG_DOMAIN_VALUE, $subject, $r);
 						} 
 						if (!$rangeCorrect){
 							$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARDISSUE_WRONG_TARGET_VALUE, $target->getTitle(), $r);
 						}
 						
 					} else if ($target instanceof SMWNAryValue) { // n-ary relation
 						
 								$explodedValues = $target->getDVs();
 								$explodedTypes = explode(";", $target->getDVTypeIDs());
 								//print_r($explodedTypes);
 								//get all range instances and check if their categories are subcategories of the range categories.
 								for($i = 0, $n = count($explodedTypes); $i < $n; $i++) {
 									if ($explodedValues[$i] == NULL) {
 										$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARD_ISSUE_MISSING_PARAM, $subject, $r, $i);
 										
 									} else {
 										
 										if ($explodedValues[$i]->getTypeID() == '_wpg') { //TODO: TEST THIS!!!
 											list($domainCorrect, $rangeCorrect) = $this->checkDomainAndRange($subject, $explodedValues[$i]->getTitle(), $domainRangeAnnotations, $domainChecked);
 											$domainChecked = true; 											
 											if (!$domainCorrect) {
					 							$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARDISSUE_WRONG_DOMAIN_VALUE, $subject, $r);
					 						 					
					 						} 
					 						if (!$rangeCorrect){
					 							$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARDISSUE_WRONG_TARGET_VALUE, $target->getTitle(), $r);
					 						}
 										}
 									}
 								}
 						} else {
 							// Normally, one would check attribute values here, but they are always correctly validated during SAVE.
 							// Otherwise the annotation would not appear in the database.
 					
 							// check if each subject is member of at least one domain category.
 							if ($domainChecked) break;
 							list($domainCorrect, $rangeCorrect) = $this->checkDomainAndRange($subject, NULL, $domainRangeAnnotations);
 							$domainChecked = true;
 							
 							if (!$domainCorrect) {
 								$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARDISSUE_WRONG_DOMAIN_VALUE, $subject, $r);
 								break;
 							}
 						}
 					
 									
 				} 
 			}
 		}
 		
 	}
 	
 	private function checkDomainAndRange($subject, $object, $domainRange, $domainChecked = false) {
 		$categoriesOfObject = $object != NULL ? smwfGetSemanticStore()->getCategoriesForInstance($object) : array();
 		if ($domainChecked) $categoriesOfSubject = smwfGetSemanticStore()->getCategoriesForInstance($subject);
 		
 		$rangeCorrect = false;
 		$domainCorrect = false;
 		foreach($domainRange as $domRanVal) { 
 			$dvs = $domRanVal->getDVs();
 			$domainCat = $dvs[0] != NULL ? $dvs[0]->getTitle() : NULL;	
 			$rangeCat  = $dvs[1] != NULL ? $dvs[1]->getTitle() : NULL;
 			if ($domainChecked) $domainCat = NULL;
 			if ($domainCat != NULL && $rangeCat != NULL) {
	 			foreach($categoriesOfObject as $coo) { 
	 					// check domain and range
	 					if (GraphHelper::checkForPath($this->categoryGraph, $coo->getArticleID(), $rangeCat->getArticleID())) {
	 						$rangeCorrect = true;
	 						foreach($categoriesOfSubject as $cos) {
	 							$domainCorrect |= (GraphHelper::checkForPath($this->categoryGraph, $cos->getArticleID(), $domainCat->getArticleID()));
	 							if ($domainCorrect) break;
		 					}
	 					}
	 			}	
 			} else if ($domainCat != NULL){
 					//check domain
 					foreach($categoriesOfSubject as $coi) {
 						$domainCorrect |= (GraphHelper::checkForPath($this->categoryGraph, $coi->getArticleID(), $domainCat->getArticleID()));
 						if ($domainCorrect) break;
 					}
 				} else if ($rangeCat != NULL) {
 					// check range
 						foreach($categoriesOfObject as $coo) {
 							$rangeCorrect |= (GraphHelper::checkForPath($this->categoryGraph, $coo->getArticleID(), $rangeCat->getArticleID()));
 							if ($rangeCorrect) break;
 						}
 				}
 				
 			}
 		return array($domainCorrect, $rangeCorrect);
 	}
 	
 	
 	/**
 	 * Checks if property annotation cardinalities are schema-consistent.
 	 */
 	public function checkAnnotationCardinalities() {
 		global $smwgContLang;
 		
 		// check attribute annotation cardinalities
 		$properties = smwfGetSemanticStore()->getPages(array(SMW_NS_PROPERTY));
 		$this->bot->addSubTask(count($properties));
 		foreach($properties as $a) {
 			if ($this->delay > 0) {
 				usleep($this->delay);
 			}
 			$this->bot->worked(1);
 			
 			if (smwfGetSemanticStore()->minCard->equals($a) 
 					|| smwfGetSemanticStore()->maxCard->equals($a)
 					|| smwfGetSemanticStore()->domainRangeHintRelation->equals($a) 
 					|| smwfGetSemanticStore()->inverseOf->equals($a)) {
 						// ignore 'min cardinality' and 'max cardinality'
 						continue;
 			}
 			
 			$minCardArray = smwfGetStore()->getPropertyValues($a, smwfGetSemanticStore()->minCard);
 			
 			if (empty($minCardArray)) {
 			
 				$minCards = smwfGetSemanticStore()->getMinCardinalityOfSuperProperty($this->propertyGraph, $a);
 			} else {
 				$minCards = $minCardArray[0]->getXSDValue() + 0;
 				
 			}
 			
 			$maxCardsArray = smwfGetStore()->getPropertyValues($a, smwfGetSemanticStore()->maxCard);
 			
 			if (empty($maxCardsArray)) {
 				
 				$maxCards = smwfGetSemanticStore()->getMaxCardinalityOfSuperProperty($this->propertyGraph, $a);
 				
 			} else {
 				$maxCards = $maxCardsArray[0]->getXSDValue() == '*' ? CARDINALITY_UNLIMITED : $maxCardsArray[0]->getXSDValue() + 0;
 				
 			}
 			
 			
 			$allAttributeSubjects = smwfGetStore()->getAllPropertySubjects($a);
 			foreach($allAttributeSubjects as $subject) {
 				
 				if ($subject == null) {
 					continue;
 				}
 				
 				$allAttributeForSubject = smwfGetStore()->getPropertyValues($subject, $a);
 				$num = count($allAttributeForSubject);
 				
 				if ($num < $minCards || $num > $maxCards) {
 					$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARDISSUE_WRONG_CARD, $subject, $a);
				}
 			
 			}
 			
 			
 		}
 		
 		return '';
 	}
 	
 	
	
 }
?>
