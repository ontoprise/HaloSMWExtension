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
 	private $cc_store;
 	private $delay; 
 	
 	// Category/Property Graph. It is cached for the whole consistency checks.
 	private $categoryGraph;
 	private $propertyGraph;
 	
 	// GardeningIssue store
 	private $gi_store;
 	
 	// Important: Attribute values (primitives) are always syntactically 
 	// correct when they are in the database. So only relations
 	// will be checked.
 	
 	public function AnnotationLevelConsistency(& $bot, $delay) {
 		$this->bot = $bot;
 		$this->delay = $delay;
 		$this->cc_store = $bot->getConsistencyStorage();
 		
 		$this->categoryGraph = $this->cc_store->getCategoryInheritanceGraph();
 		$this->propertyGraph = $this->cc_store->getPropertyInheritanceGraph();
 		$this->gi_store = SMWGardening::getGardeningIssuesAccess();
 	}
 	/**
 	 * Checks if property annotations uses schema consistent values
 	 */
 	public function checkAllPropertyAnnotations() {
 		global $smwgContLang;
 	 		
 		$properties = smwfGetSemanticStore()->getPages(array(SMW_NS_PROPERTY));
 		
 		$work = count($properties);
 		$cnt = 0;
 		print "\n";
 		$this->bot->addSubTask(count($properties));
 		foreach($properties as $p) {
 			if ($this->delay > 0) {
 				usleep($this->delay);
 			}
 			$this->bot->worked(1);
 			$cnt++;
 			if ($cnt % 10 == 1 || $cnt == $work) { 
 				print "\x08\x08\x08\x08".number_format($cnt/$work*100, 0)."% ";
 			}
 			
 			if (smwfGetSemanticStore()->domainRangeHintRelation->equals($p) 
 					|| smwfGetSemanticStore()->minCard->equals($p) 
 					|| smwfGetSemanticStore()->maxCard->equals($p)
 					|| smwfGetSemanticStore()->inverseOf->equals($p)) {
 						// ignore builtin properties
 						continue;
 			}
 			
 			// get annotation subjects for the property.
 			$allPropertySubjects = smwfGetStore()->getAllPropertySubjects($p);
 			
 			$this->checkPropertyAnnotations($allPropertySubjects, $p);
 			
 		}
 		
 	}
 	
 	private function checkPropertyAnnotations(& $subjects, $property) {
 		// get domain and range categories of property
 			$domainRangeAnnotations = smwfGetStore()->getPropertyValues($property, smwfGetSemanticStore()->domainRangeHintRelation);
 			
 			
 			if (empty($domainRangeAnnotations)) {
 				// if there are no range categories defined, try to find a super relation with defined range categories
 				$domainRangeAnnotations = $this->cc_store->getDomainsAndRangesOfSuperProperty($this->propertyGraph, $property);
 			}
 			
 			if (empty($domainRangeAnnotations)) {
 				// if it's still empty, there's no domain or range defined at all. In this case, simply skip it in order not to pollute the consistency log.
 				return;
 			}
 			
 			 			
 			// iterate over all property subjects
 			foreach($subjects as $subject) { 
 				
 				if ($subject == null) {
 					continue;
 				}
 				
 				$categoriesOfSubject = smwfGetSemanticStore()->getCategoriesForInstance($subject);
 				
 				list($domain_cov_results, $domainCorrect) = $this->checkDomain($categoriesOfSubject, $domainRangeAnnotations);
				if (!$domainCorrect) {
					$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARDISSUE_WRONG_DOMAIN_VALUE, $subject, $property );
					
				}
				
 				// get property value for a given instance
 				$relationTargets = smwfGetStore()->getPropertyValues($subject, $property);
 				
 				foreach($relationTargets as $target) {
 					
 					// decide which type and do consistency checks
 					if ($target instanceof SMWWikiPageValue) {  // binary relation 
 						$rd_target = smwfGetSemanticStore()->getRedirectTarget($target->getTitle());
 						if (!$rd_target->exists()) continue;
	 					$categoriesOfObject = smwfGetSemanticStore()->getCategoriesForInstance($rd_target);
	 					if ($domainCorrect) {
 							$rangeCorrect = $this->checkRange($domain_cov_results, $categoriesOfObject, $domainRangeAnnotations);
	 					} else {
	 						$rangeCorrect = $this->checkRange(NULL, $categoriesOfObject, $domainRangeAnnotations);
	 					}
 						if (!$rangeCorrect) {
 							$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARDISSUE_WRONG_TARGET_VALUE, $subject, $property, $rd_target != NULL ? $rd_target->getDBkey() : NULL);
 						}	 						
 						
 					} else if ($target instanceof SMWNAryValue) { // n-ary relation
 						
 								$explodedValues = $target->getDVs();
 								$explodedTypes = explode(";", $target->getDVTypeIDs());
 								//print_r($explodedTypes);
 								//get all range instances and check if their categories are subcategories of the range categories.
 								for($i = 0, $n = count($explodedTypes); $i < $n; $i++) {
 									if ($explodedValues[$i] == NULL) {
 										$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARD_ISSUE_MISSING_PARAM, $subject, $property, $i);
 										
 									} else {
 										
 										if ($explodedValues[$i]->getTypeID() == '_wpg') { 
 											$rd_target = smwfGetSemanticStore()->getRedirectTarget($explodedValues[$i]->getTitle());
 											if (!$rd_target->exists()) continue;
 											$categoriesOfObject = smwfGetSemanticStore()->getCategoriesForInstance($rd_target);
					 						if ($domainCorrect) {
					 							$rangeCorrect = $this->checkRange($domain_cov_results, $categoriesOfObject, $domainRangeAnnotations);
						 					} else {
						 						$rangeCorrect = $this->checkRange(NULL, $categoriesOfObject, $domainRangeAnnotations);
						 					}
					 						if (!$rangeCorrect) {
					 							$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARDISSUE_WRONG_TARGET_VALUE, $subject, $property, $rd_target != NULL ? $rd_target->getDBkey() : NULL);
					 						}	
 										}
 									}
 								}
 						} else {
 							// Normally, one would check attribute values here, but they are always correctly validated during SAVE.
 							// Otherwise the annotation would not appear in the database. *Exception*: wrong units
 					
 														
					 		break; // always break the loop, because an attribute annotation is representative for all others.
 						}
 					
 									
 				} 
 			}
 	}
 	
 	/**
 	 * Checks weather subject and object matches a domain/range pair.
 	 * 
 	 * @param subject Title
 	 * @param object Title
 	 * @param $domainRange SMWNaryValue
 	 */
 	private function checkRange($domain_cov_results, $categoriesOfObject, $domainRange) {
 		 		
 	
 		$result = false;
 		for($i = 0, $n = count($domainRange); $i < $n; $i++) {
 			if ($domain_cov_results != NULL && !$domain_cov_results[$i]) continue;
 			$domRanVal = $domainRange[$i]; 
 			$rangeCorrect = false;
 			$dvs = $domRanVal->getDVs();
 				
 			$rangeCat  = $dvs[1] != NULL ? $dvs[1]->getTitle() : NULL;
 			
 			
 			if ($rangeCat == NULL) {
 				$rangeCorrect = true;
 			}
 			if ($rangeCat != NULL) {
 				// check range
 				
 				foreach($categoriesOfObject as $coo) {
 					$rangeCorrect |= (GraphHelper::checkForPath($this->categoryGraph, $coo->getArticleID(), $rangeCat->getArticleID()));
 					if ($rangeCorrect) break;
 				}
 			}
 		
 			$result |= $rangeCorrect;
 		}
 		return $result;
 	}
 	
 	/**
 	 * Checks weather subject matches a domain/range pair.
 	 */
 	private function checkDomain($categoriesOfSubject, $domainRange) {
 	 		
 		$results = array();
 		$oneDomainCorrect = false;
 		foreach($domainRange as $domRanVal) { 
 			$domainCorrect = false;
 		
 			$dvs = $domRanVal->getDVs();
 			$domainCat = $dvs[0] != NULL ? $dvs[0]->getTitle() : NULL;	
 		
 			if ($domainCat == NULL) {
 				$domainCorrect = true;
 			} else {
 				//check domain
 				
 				foreach($categoriesOfSubject as $coi) {
 					$domainCorrect |= (GraphHelper::checkForPath($this->categoryGraph, $coi->getArticleID(), $domainCat->getArticleID()));
 					if ($domainCorrect) break;
 				}
 			}
 			$results[] = $domainCorrect;
 			$oneDomainCorrect |= $domainCorrect;
 		}
 		return array($results, $oneDomainCorrect);
 	}
 	
 	
 	/**
 	 * Checks if number of property appearances in articles are schema-consistent.
 	 */
 	public function checkAnnotationCardinalities() {
 		global $smwgContLang;
 		
 		// get all properties
 		$properties = smwfGetSemanticStore()->getPages(array(SMW_NS_PROPERTY));
 		$work = count($properties);
 		$cnt = 0;
 		print "\n";
 		$this->bot->addSubTask(count($properties));
 		foreach($properties as $a) {
 			if ($this->delay > 0) {
 				usleep($this->delay);
 			}
 			$this->bot->worked(1);
 			$cnt++;
 			if ($cnt % 10 == 1 || $cnt == $work) { 
 				print "\x08\x08\x08\x08".number_format($cnt/$work*100, 0)."% ";
 			}
 			
 			// ignore builtin properties
 			if (smwfGetSemanticStore()->minCard->equals($a) 
 					|| smwfGetSemanticStore()->maxCard->equals($a)
 					|| smwfGetSemanticStore()->domainRangeHintRelation->equals($a) 
 					|| smwfGetSemanticStore()->inverseOf->equals($a)) {
 						continue;
 			}
 			
 			// get minimum cardinality
 			$minCardArray = smwfGetStore()->getPropertyValues($a, smwfGetSemanticStore()->minCard);
 			
 			if (empty($minCardArray)) {
 				// if it does not exist, get minimum cardinality from superproperty
 				$minCards = CARDINALITY_MIN;
 			} else {
 				// assume there's only one defined. If not it will be found in co-variance checker anyway
 				$minCards = $minCardArray[0]->getXSDValue() + 0;
 			}
 			
 			// get maximum cardinality
 			$maxCardsArray = smwfGetStore()->getPropertyValues($a, smwfGetSemanticStore()->maxCard);
 			
 			if (empty($maxCardsArray)) {
 				// if it does not exist, get maximum cardinality from superproperty
 				$maxCards = CARDINALITY_UNLIMITED;
 				
 			} else {
 				// assume there's only one defined. If not it will be found in co-variance checker anyway
 				$maxCards = $maxCardsArray[0]->getXSDValue() + 0;
 			}
 			
 			if ($minCards == CARDINALITY_MIN && $maxCards == CARDINALITY_UNLIMITED) {
 				// default case: no check needed, so skip it.
 				continue;
 			}
 			
 			// get all instances which have instantiated properties (including subproperties) of $a
 			// and the number of these annotations for each for instance
 			$result = $this->cc_store->getNumberOfPropertyInstantiations($a);
 			
 			// compare to minCard and maxCard
 			foreach($result as $r) {
 				list($subject, $numOfInstProps) = $r;
 			
	 			// compare number of appearance with defined cardinality
		 		if ($numOfInstProps < $minCards) {
		 			if (!$this->gi_store->existsGardeningIssue($this->bot->getBotID(), SMW_GARDISSUE_TOO_LOW_CARD, NULL, $subject, $a)) {
		 				
		 				$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARDISSUE_TOO_LOW_CARD, $subject, $a, $numOfInstProps);
		 			}
				} 
				if ($numOfInstProps > $maxCards) {
					if (!$this->gi_store->existsGardeningIssue($this->bot->getBotID(), SMW_GARDISSUE_TOO_HIGH_CARD, NULL, $subject, $a)) {
						
						$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARDISSUE_TOO_HIGH_CARD, $subject, $a, $numOfInstProps);
					}
				}			
 			}
 			
 			
 			// special case: If minCard > CARDINALITY_MIN (=0), it may happen that an instance does not have a single property instantiation although it should.
 			// it will not be found with 'getNumberOfPropertyInstantiations' method
 			
 			if ($minCards == CARDINALITY_MIN) {
 				$minCards = $this->cc_store->getMinCardinalityOfSuperProperty($this->propertyGraph, $a);
 				if ($minCards == CARDINALITY_MIN) continue;
 			}
 			 
 			// get domains
 			$domainRangeAnnotations = smwfGetStore()->getPropertyValues($a, smwfGetSemanticStore()->domainRangeHintRelation);
 			 			
 			if (empty($domainRangeAnnotations)) {
 				// if there are no domain categories defined, this check can not be applied.
 				continue;
 			}
		 	 		
 			foreach($domainRangeAnnotations as $domRan) {
 				$dvs = $domRan->getDVs();
 				if ($dvs[0] == NULL) continue; // ignore annotations with missing domain
 				$domainCategory = $dvs[0]->getTitle();
 				$instances = smwfGetSemanticStore()->getInstances($domainCategory);
 				
 				foreach($instances as $subject) { // check indirect instances
 					if ($subject[0] == null) {
 						continue;
	 				}
	 				
	 				if (count(smwfGetSemanticStore()->getDirectSubProperties($a)) == 0) {
	 					$num = count(smwfGetStore()->getPropertyValues($subject[0], $a));
	 				} else {
		 				// get number of appearances for a subject and a property (including its subproperties)
		 				$num = $this->cc_store->getNumberOfPropertyInstantiationForInstance($a, $subject[0]);
	 				}					
	 				// compare number of appearance with defined cardinality
	 				if ($num == 0) {
	 					if (!$this->gi_store->existsGardeningIssue($this->bot->getBotID(), SMW_GARDISSUE_TOO_LOW_CARD, NULL, $subject[0], $a)) {
	 						
	 						$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARDISSUE_TOO_LOW_CARD, $subject[0], $a, $num);
	 					}
					} 
				
 				}
 			}
 						
 		}
  		
 	}
 	
 	/**
 	 * Checks if all annotations with units have proper units (such defined by 'corresponds to' relations).
 	 */
 	public function checkUnits() {
 		// check attribute annotation cardinalities
 		$types = smwfGetSemanticStore()->getPages(array(SMW_NS_TYPE));
 		$this->bot->addSubTask(count($types));
 		foreach($types as $type) {
 			if ($this->delay > 0) {
 				usleep($this->delay);
 			}
 			$this->bot->worked(1);
 		
 			$units = smwfGetSemanticStore()->getDistinctUnits($type);
 			$conversion_factors = smwfGetStore()->getSpecialValues($type, SMW_SP_CONVERSION_FACTOR);
 			$si_conversion_factors = smwfGetStore()->getSpecialValues($type, SMW_SP_CONVERSION_FACTOR_SI);
 			
 			foreach($units as $u) {
 				
	 			$correct_unit = false;
 				if ($u == NULL) continue;
 				foreach($conversion_factors as $c) {
 					$correct_unit |= preg_match("/(([+-]?\d*(\.\d+([eE][+-]?\d*)?)?)\s+)?".preg_quote($u,"/").'(,|$)/', $c) > 0;
 				}
 				foreach($si_conversion_factors as $c) {
 					$correct_unit |= preg_match("/(([+-]?\d*(\.\d+([eE][+-]?\d*)?)?)\s+)?".preg_quote($u,"/").'(,|$)/', $c) > 0;
 				}
 			
	 			if (!$correct_unit) {
	 				
	 				$annotations = smwfGetSemanticStore()->getAnnotationsWithUnit($type, $u);
	 			
	 				foreach($annotations as $a) {
	 					$this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARDISSUE_WRONG_UNIT, $a[0], $a[1], $u);
	 				}
	 			}
 			}
 		}
 	}
 	
 	 	
 }
?>
