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
 	
 	
 	// Important: Attribute values (primitives) are always syntactically 
 	// correct when they are in the database. So only relations
 	// will be checked.
 	
 	public function AnnotationLevelConsistency(& $bot, $delay) {
 		$this->bot = $bot;
 		$this->delay = $delay;
 		
 		$this->categoryGraph = smwfGetSemanticStore()->getCategoryInheritanceGraph();
 		$this->propertyGraph = smwfGetSemanticStore()->getPropertyInheritanceGraph();
 		
 	}
 	/**
 	 * Checks if property annotations uses schema consistent values
 	 */
 	public function checkPropertyAnnotations() {
 		global $smwgContLang;
 		$namespaces = $smwgContLang->getNamespaces();
 		$log = "";
 		$properties = smwfGetSemanticStore()->getPages(array(SMW_NS_PROPERTY));
 		$numLog = 0;
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
 			if ($numLog > MAX_LOG_LENGTH) { print (" limit of consistency issues reached. Break. "); return $log; }
 			if (smwfGetSemanticStore()->domainHintRelation->equals($r) 
 					|| smwfGetSemanticStore()->rangeHintRelation->equals($r)
 					|| smwfGetSemanticStore()->minCard->equals($r) 
 					|| smwfGetSemanticStore()->maxCard->equals($r)
 					|| smwfGetSemanticStore()->inverseOf->equals($r)) {
 						// ignore builtin properties
 						continue;
 			}
 			
 			// get domain and range categories of property
 			$rangeCategories = smwfGetStore()->getPropertyValues($r, smwfGetSemanticStore()->rangeHintRelation);
 			$domainCategories = smwfGetStore()->getPropertyValues($r, smwfGetSemanticStore()->domainHintRelation);
 			
 			if (empty($rangeCategories)) {
 				// if there are no range categories defined, try to find a super relation with defined range categories
 				$rangeCategories = smwfGetSemanticStore()->getRangesOfSuperProperty($this->propertyGraph, $r);
 			}
 			if (empty($domainCategories)) {
 				// if there are no domain categories defined, try to find a super relation with defined range categories
 				$domainCategories = smwfGetSemanticStore()->getDomainsOfSuperProperty($this->propertyGraph, $r);
 			}
 			// get annotation subjects for the property.
 			$allRelationSubjects = smwfGetStore()->getAllPropertySubjects($r);
 			
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
 						$categoriesOfInstance = smwfGetSemanticStore()->getCategoriesForInstance($target->getTitle());
 						$isValid = false;
 						foreach($rangeCategories as $ranCts) { 
 							foreach($categoriesOfInstance as $artCats) { 
 								
 								if (GraphHelper::checkForPath($this->categoryGraph, $artCats->getArticleID(), $ranCts->getArticleID())) {
 									$isValid = true;
 								}
 							}
 						}
 						if (!$isValid && !empty($categoriesOfInstance) && !empty($rangeCategories)) {
 							$log.=wfMsg('smw_gard_wrong_target', $target->getText(), $r->getText(), $namespaces[$r->getNamespace()])."\n\n";
 							$numLog++;
 						} 
 						/*if (!$target->getTitle()->exists()) {
 							$log.=wfMsg('smw_gard_relationtarget_undefined', $target->getText(), "[[".$namespaces[$r->getNamespace()].":".$r->getText()."]]")."\n\n";
 							$numLog++;
 						}*/
 					} else if ($target instanceof SMWNAryValue) { // n-ary relation
 						
 								$explodedValues = $target->getDVs();
 								$explodedTypes = explode(";", $target->getDVTypeIDs());
 								//print_r($explodedTypes);
 								//get all range instances and check if their categories are subcategories of the range categories.
 								for($i = 0, $n = count($explodedTypes); $i < $n; $i++) {
 									if ($explodedValues[$i] == NULL) {
 										$log.=wfMsg('smw_gard_missing_param', $subject->getText(), $r->getText(), $namespaces[$r->getNamespace()], $i)."\n\n";
 										$numLog++;
 									} else {
 										
 										if ($explodedTypes[$i] == 'Page') { //TODO: externalize or use constant
 											
 											$categoriesOfInstance = smwfGetSemanticStore()->getCategoriesForInstance($explodedValues[$i]->getTitle());
 											
 											$isValid = false;
 											foreach($rangeCategories as $ranCats) { 
 												foreach($categoriesOfInstance as $artCats) { 
 						   							if (GraphHelper::checkForPath($this->categoryGraph, $artCats->getArticleID(), $ranCats->getArticleID())) {
 														$isValid = true;
 													}
 												}
 											}
 											if (!$isValid && !empty($rangeCategories) && !empty($categoriesOfInstance)) {
 												$log.=wfMsg('smw_gard_wrong_range', $subject->getText(), $a->getText(), $namespaces[$r->getNamespace()])."\n\n";
 												$numLog++;
 											}
 										}
 									}
 								}
 							}
 					
 					// Normally, one would check attribute values here, but they are always correctly validated during SAVE.
 					// Otherwise the annotation would not appear in the database.
 					
 					// check if each subject is member of at least one domain category.
 					$domainCategoryIDs = smwfGetSemanticStore()->getCategoriesForInstance($subject);
 					$isValid = false;
 					foreach($domainCategories as $domCats) { 
 						foreach($domainCategoryIDs as $artCats) { 
 								
 							if (GraphHelper::checkForPath($this->categoryGraph, $artCats->getArticleID(), $domCats->getArticleID())) {
 								$isValid = true;
 							}
 						}
 					}
 					if (!$isValid && !empty($domainCategoryIDs) && !empty($domainCategories)) {
 						
 						$log.=wfMsg('smw_gard_wrong_domain', $subject->getText(), $r->getText(),$namespaces[$r->getNamespace()])."\n\n";
 						$numLog++;
 					}
 					
 				} 
 			}
 		}
 		return $log;
 	}
 	
 	
 	/**
 	 * Checks if property annotation cardinalities are schema-consistent.
 	 */
 	public function checkAnnotationCardinalities() {
 		global $smwgContLang;
 		$namespaces = $smwgContLang->getNamespaces();
 		$log = "";
 		$numLog = 0;
 		// check attribute annotation cardinalities
 		$properties = smwfGetSemanticStore()->getPages(array(SMW_NS_PROPERTY));
 		$this->bot->addSubTask(count($properties));
 		foreach($properties as $a) {
 			if ($this->delay > 0) {
 				usleep($this->delay);
 			}
 			$this->bot->worked(1);
 			if ($numLog > MAX_LOG_LENGTH) { return $log; }
 			if (smwfGetSemanticStore()->minCard->equals($a) 
 					|| smwfGetSemanticStore()->maxCard->equals($a)
 					|| smwfGetSemanticStore()->domainHintRelation->equals($a) 
 					|| smwfGetSemanticStore()->rangeHintRelation->equals($a)
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
 						$log.=wfMsg('smw_gard_incorrect_cardinality', $subject->getText(), $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 						$numLog++;
 				}
 			
 			}
 			
 			
 		}
 		
 		return $log;
 	}
 	
 	
	
 }
?>
