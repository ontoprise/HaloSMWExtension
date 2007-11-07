<?php
 /*
 * Created on 14.05.2007
 *
 * Author: kai
 */
 
global $smwgHaloIP;
require_once("GraphEdge.php"); 
require_once("$smwgHaloIP/includes/SMW_GraphHelper.php"); 
 
 // default cardinalities 
 define('CARDINALITY_MIN',0);
 define('CARDINALITY_UNLIMITED', 2147483647); // MAXINT
 
  
 class PropertyCoVarianceDetector {
 	 	
 	// reference to bot
 	private $bot;
 	
 	// delay
 	private $delay;
 	
 	// inheritance graphs.
 	private $categoryGraph;
 	private $propertyGraph;
 	
 	// GardeningIssue store
 	private $gi_store;
 	
	
	/**
	 * Creates a PropertyCoVarianceDetector
	 */
 	public function PropertyCoVarianceDetector(& $bot, $delay) {
 		$this->bot = $bot;
 		$this->delay = $delay;
 	
 		$this->categoryGraph = smwfGetSemanticStore()->getCategoryInheritanceGraph();
 		$this->propertyGraph = smwfGetSemanticStore()->getPropertyInheritanceGraph();
 		$this->gi_store = SMWGardening::getGardeningIssuesAccess();
 	}
 	
 	/**
 	 * Checks co-variance of all subattributes.
 	 * 
 	 * @return A log indicating inconsistencies for every subattribute definition. (wiki-markup)
 	 */
 	public function checkPropertyGraphForCovariance() {
 		global $smwgContLang;
 		 		
 		$attributes = smwfGetSemanticStore()->getPages(array(SMW_NS_PROPERTY));
 		$cnt = 0;
 		$work = count($attributes);
 		print "\n";
 		$this->bot->addSubTask(count($attributes));
 		foreach($attributes as $a) {
 			if ($this->delay > 0) {
 				usleep($this->delay);
 			}
 			$this->bot->worked(1);
 			
 			$cnt++;
 			if ($cnt % 10 == 1 || $cnt == $work) { 
 				print "\x08\x08\x08\x08".number_format($cnt/$work*100, 0)."% ";
 			}
 			
 			if (smwfGetSemanticStore()->domainRangeHintRelation->equals($a) 
 					
 					|| smwfGetSemanticStore()->minCard->equals($a) 
 					|| smwfGetSemanticStore()->maxCard->equals($a)
 					|| smwfGetSemanticStore()->inverseOf->equals($a) ) {
 						// ignore builtin properties
 						continue;
 			}
  			
 			$this->checkMinCardinality($a);
 			$this->checkMaxCardinality($a);
 			$this->checkDomainAndRangeCovariance($a);
 			$this->checkTypeEquality($a);
 			//$this->checkRangeCovariance($a);
 			$this->checkSymTransCovariance($a);
 			
 			
 		}
 		
 	}
 	
 	/** 		
 	 * Check min cardinality for co-variance
  	 */
 	private function checkMinCardinality($a) {
 		
  			global $smwgContLang;
  			
 			$minCard = smwfGetStore()->getPropertyValues($a, smwfGetSemanticStore()->minCard);
 		
 			if (!empty($minCard)) {
 				// otherwise check min cardinality of parent for co-variance.
 				
 				// check for doubles
 				if (count($minCard) > 1) {
 					
 					$this->gi_store->addGardeningIssueAboutValue($this->bot->getBotID(), SMW_GARDISSUE_DOUBLE_MIN_CARD, $a, count($minCard));
 					
 				}
 				
 				// check for correct value
 				if ($this->isCardinalityValue($minCard[0]->getXSDValue()) !== true) {
 					
 					$this->gi_store->addGardeningIssueAboutValue($this->bot->getBotID(), SMW_GARDISSUE_WRONG_CARD_VALUE, $a, $minCard[0]->getXSDValue());
 					
 				}
 				// read min cards
 				
 				$minCardValue = $minCard[0]->getXSDValue() + 0;
 				$minCardValueOfParent = smwfGetSemanticStore()->getMinCardinalityOfSuperProperty($this->propertyGraph, $a);
 				
 				$minCardCOVTest = $this->checkMinCardinalityForCovariance($minCardValue, $minCardValueOfParent);
 				if ($minCardCOVTest !== true) {
 					 
 					$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), constant($minCardCOVTest), $a);
 				}	
 			} else {
 				// check with default min card (CARDINALITY_MIN)
 				
 				$minCardValue = CARDINALITY_MIN;
 				$minCardValueOfParent = smwfGetSemanticStore()->getMinCardinalityOfSuperProperty($this->propertyGraph, $a);
 			
 				$minCardCOVTest = $this->checkMinCardinalityForCovariance($minCardValue, $minCardValueOfParent);
 				if ($minCardCOVTest !== true) {
 					 
 					$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), constant($minCardCOVTest), $a);
 				}	
 			}
 	}
 	
 	/** 		
 	 * Check max cardinality for co-variance
  	 */
 	private function checkMaxCardinality($a) {
 		
 			global $smwgContLang;
  			
 			$maxCard = smwfGetStore()->getPropertyValues($a, smwfGetSemanticStore()->maxCard);
 			
 			if (!empty($maxCard)) {
 				// check for doubles
 				if (count($maxCard) > 1) {
 				
 					$this->gi_store->addGardeningIssueAboutValue($this->bot->getBotID(), SMW_GARDISSUE_DOUBLE_MAX_CARD, $a, count($maxCard));
 					
 				}
 				// check for correct value
 				if ($this->isCardinalityValue($maxCard[0]->getXSDValue()) !== true && $maxCard[0]->getXSDValue() != '*') {
 					$this->gi_store->addGardeningIssueAboutValue($this->bot->getBotID(), SMW_GARDISSUE_WRONG_CARD_VALUE, $a, $maxCard[0]->getXSDValue());
 					
 					
 				}
 				// check for co-variance with parent
 				
 				$maxCardValue = $maxCard[0]->getXSDValue() == '*' ? CARDINALITY_UNLIMITED : $maxCard[0]->getXSDValue() + 0;
 				$maxCardValueOfParent = smwfGetSemanticStore()->getMaxCardinalityOfSuperProperty($this->propertyGraph, $a);
 				
 				$maxCardCOVTest = $this->checkMaxCardinalityForCovariance($maxCardValue, $maxCardValueOfParent);
 				if ($maxCardCOVTest !== true) {
 					$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), constant($maxCardCOVTest), $a);
 					 
 				}	
 			} else {
 				// check with default max card (CARDINALITY_UNLIMITED)
 				
 				$maxCardValue = CARDINALITY_UNLIMITED;
 				$maxCardValueOfParent = smwfGetSemanticStore()->getMaxCardinalityOfSuperProperty($this->propertyGraph, $a);
 				
 				$maxCardCOVTest = $this->checkMaxCardinalityForCovariance($maxCardValue, $maxCardValueOfParent);
 				if ($maxCardCOVTest !== true) {
 					$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), constant($maxCardCOVTest), $a);
 					 
 				}	
 			}	
 	}
 	
 	/** 		
 	 * Check domain co-variance
  	 */
 	private function checkDomainAndRangeCovariance($p) {
 		$type = smwfGetStore()->getSpecialValues($p, SMW_SP_HAS_TYPE);
 		
 		if (count($type) > 0) {
 			if (count($type) == 0 || $type[0]->getXSDValue() == '_wpg' || $type[0]->getXSDValue() == '__nry') {
 				// default property (type wikipage), explicitly defined wikipage or nary property
 				$res = $this->isDomainRangeCovariant($p);
 				if ($res === true) return;
 				foreach($res as $cov) {
 					if (!$cov[0]) {
 						// log domain cov error for annot
 						$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_DOMAINS_NOT_COVARIANT, $p);
 					}
 					if (!$cov[1]) {
 						// log range cov error for annot
 						$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_RANGES_NOT_COVARIANT, $p);
 					}
 				}
 			} else {
 				// attribute
 				$res = $this->isDomainRangeCovariant($p, true);
 				if ($res === true) return;
 				foreach($res as $cov) {
 					if (!$cov[0]) {
 						// log domain cov error for annot
 						$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_DOMAINS_NOT_COVARIANT, $p);
 					}
 					
 				}
 			}
 		} 
	
 	}
 	
 	private function isDomainRangeCovariant($p, $isAttribute = false) {
 		
 		$domainRangeAnnotations = smwfGetStore()->getPropertyValues($p, smwfGetSemanticStore()->domainRangeHintRelation);
 		
 			if (empty($domainRangeAnnotations)) {
 				
 				if ($isAttribute) {
 					$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_DOMAINS_NOT_DEFINED, $p);
 				} else {
 					$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_DOMAINS_AND_RANGES_NOT_DEFINED, $p);
 				}
 				return true;
 			} else {
 				$domainRangeAnnotationsOfSuperProperty = smwfGetSemanticStore()->getDomainsAndRangesOfSuperProperty($this->propertyGraph, $p);
 				
 				if (empty($domainRangeAnnotationsOfSuperProperty)) {
 					return true;
 				}
 				$results = array();
 				foreach($domainRangeAnnotations as $dra) {
 					$current = array(false, false, $dra);
 					$domRanVal = $dra->getDVs();
		 			$domainCat = $domRanVal[0] != NULL ? $domRanVal[0]->getTitle() : NULL;	
		 			$rangeCat  = $domRanVal[1] != NULL ? $domRanVal[1]->getTitle() : NULL;
 					 
	 				foreach($domainRangeAnnotationsOfSuperProperty as $drosp) {
	 					$domRanValOfSuperProperty = $drosp->getDVs();
	 					$domainCatOfSuperProperty = $domRanValOfSuperProperty[0] != NULL ? $domRanValOfSuperProperty[0]->getTitle() : NULL;	
	 					$rangeCatOfSuperProperty  = $domRanValOfSuperProperty[1] != NULL ? $domRanValOfSuperProperty[1]->getTitle() : NULL;
	 					if ($domainCat != NULL && $domainCatOfSuperProperty != NULL) {
 				 
		 					$domainCovariant = $rangeCovariant = false;
	 						
	 						$domainCovariant = GraphHelper::checkForPath($this->categoryGraph, $domainCat->getArticleID(), $domainCatOfSuperProperty->getArticleID()); 
	 						
		 					if (!$isAttribute && ($rangeCat != NULL && $rangeCatOfSuperProperty != NULL)) {
			 					$rangeCovariant = (GraphHelper::checkForPath($this->categoryGraph, $rangeCat->getArticleID(), $rangeCatOfSuperProperty->getArticleID()));
			 				
		 					}
		 					
		 					// add new co-variance tuple if it is better matching domain and range.
		 					$current_score = ($current[0] ? 1 : 0) + ($current[1] ? 1 : 0);
		 					$new_score = ($domainCovariant ? 1 : 0) + ($rangeCovariant ? 1 : 0);
		 					$current = ($new_score > $current_score) ? array($domainCovariant, $rangeCovariant, $current[2]) : $current;
		 					if ($domainCovariant && $rangeCovariant) {
		 						break; // stop if both are covariant
		 					}
		 					
	 					} else if (!$isAttribute && ($rangeCat != NULL && $rangeCatOfSuperProperty != NULL)){
 						
 							$rangeCovariant = (GraphHelper::checkForPath($this->categoryGraph, $rangeCat->getArticleID(), $rangeCatOfSuperProperty->getArticleID()));
	 						if ($rangeCovariant) {
	 							$current = array(true, true, $dra);
	 							break; // stop if both are covariant
	 						}
		 					
 						
 						
 						} else {
 							$current = array(true, true, $dra);
 							break; // stop if both are covariant
 						}
 				}
 				$results[] = $current;
 			}
 			return $results;
 		}
 	}
 	
 	
 	
 	/** 		
 	 *  Check type equality
  	 */
 	private function checkTypeEquality($a) {
 		global $smwgContLang;
  		
  		
 			$types = smwfGetStore()->getSpecialValues($a, SMW_SP_HAS_TYPE);
 			if (empty($types)) {
 				//$log .= wfMsg('smw_gard_types_is_not_defined', $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 				//
 			} else {
 				if (count($types) > 1) {
 					
 					$this->gi_store->addGardeningIssueAboutValue($this->bot->getBotID(), SMW_GARDISSUE_DOUBLE_TYPE, $a, count($types));
 					
 				}
 				$typesOfSuperAttribute = smwfGetSemanticStore()->getTypeOfSuperProperty($this->propertyGraph, $a);
 				$pathExists = false;
 				// only check first 'has type' value, because if more exist, it will be indicated anyway. 
 				$smwFirstTypeValue = count($typesOfSuperAttribute) > 0 ? $typesOfSuperAttribute[0] : null;
 				if ($smwFirstTypeValue != null && $smwFirstTypeValue instanceof SMWTypesValue) { 
 						if ($smwFirstTypeValue->getXSDValue() == $types[0]->getXSDValue()) {
 							$pathExists = true;
 						}
 				}
 				if (!$pathExists && $smwFirstTypeValue != null) {
 					
 					$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_TYPES_NOT_COVARIANT, $a);
 					
 				}
 			}
 	}
 	
 	
 	
 	/** 		
 	 * Check symetry and transitivity co-variance
  	 */
 	private function checkSymTransCovariance($a) {
 		global $smwgContLang;
  		
  			
  			
  			if (count(smwfGetSemanticStore()->getDirectSuperProperties($a)) == 0) {
 				return; // $a has no superproperty
 			}
 			
 			$categoriesOfRelation = smwfGetSemanticStore()->getCategoriesForInstance($a);
 			$categoriesOfSuperRelation = smwfGetSemanticStore()->getCategoriesOfSuperProperty($this->propertyGraph, $a);
 			 			
 			$transOfRelation = $this->isTitleInArray(smwfGetSemanticStore()->transitiveCat, $categoriesOfRelation);
 			$transOfSuperRelation = $this->isTitleInArray(smwfGetSemanticStore()->transitiveCat, $categoriesOfSuperRelation);
 			
 			 			
 			if (($transOfRelation && !$transOfSuperRelation)) {  
 				
 				$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_TRANSITIVITY_NOT_COVARIANT1, $a);
 				
 			} else if ((!$transOfRelation && $transOfSuperRelation)) {
 				
 				
 				$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_TRANSITIVITY_NOT_COVARIANT2, $a);
 				
 			}
 			
 			$symOfRelation = $this->isTitleInArray(smwfGetSemanticStore()->symetricalCat, $categoriesOfRelation);
 			$symOfSuperRelation = $this->isTitleInArray(smwfGetSemanticStore()->symetricalCat, $categoriesOfSuperRelation);
 			
 			if (($symOfRelation && !$symOfSuperRelation)) {
 				
 				$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_SYMETRY_NOT_COVARIANT1, $a);
 				
 			} else if ((!$symOfRelation && $symOfSuperRelation)) {
 			
 				$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_SYMETRY_NOT_COVARIANT2, $a);
 				
 			}
 	}
 	
 	/**
 	 * Checks if max cardinality of an attribute and its superattribute is co-variant
 	 * 
 	 * @param $attr_max cardinality of subattribute (String)
 	 * @param $superattr_max cardinality of superattribute (String)
 	 * 
 	 * @return true, if co-variant. Otherwise errorMessage ID
 	 */
 	private function checkMaxCardinalityForCovariance($attr_max, $superattr_max) {
 		 		
 		if ($attr_max == 0) {
 			return ('SMW_GARDISSUE_MAXCARD_NOT_NULL');
 		}
 		
 			
 		if ($superattr_max != CARDINALITY_UNLIMITED) {
 			if ($attr_max > $superattr_max) {
 				return ('SMW_GARDISSUE_MAXCARD_NOT_COVARIANT');
 			}
 		}
 		return true;
 	}
 	
 	/**
 	 * Checks if min cardinality of an attribute and its superattribute is co-variant.
 	 * 
 	 * @param $attr_min cardinality of subattribute (String)
 	 * @param $superattr_min cardinality of superattribute (String)
 	 * 
 	 * @return true, if co-variant. Otherwise errorMessage ID
 	 */
 	private function checkMinCardinalityForCovariance($attr_min, $superattr_min) {
 			
 		if ($attr_min < 0) {
 			return ('SMW_GARDISSUE_MINCARD_BELOW_NULL');
 		} 
 		
 		if ($attr_min < $superattr_min) {
 			return ('SMW_GARDISSUE_MINCARD_NOT_COVARIANT');
 		}
 		
 		return true;
 	}
 	
 	private function isTitleInArray($title, $titleSet) {
 		
 		foreach($titleSet as $t) {
 			if ($t->equals($title)) {
 				return true;
 			}
 		}
 		return false;
 	}
 	
 	/**
 	 * Checks if $s is a positive integer or 0.
 	 */
 	private function isCardinalityValue($s) {
 		// card must be either an integer >= 0 
		return preg_match('/^\d+$/', trim($s)) > 0;
 	}
 }
 
 
?>
