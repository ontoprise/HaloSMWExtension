<?php
 /*
 * Created on 14.05.2007
 *
 * Author: kai
 */
 
require_once("GraphEdge.php"); 
require_once("ConsistencyHelper.php"); 
 
 // default cardinalities 
 define('CARDINALITY_MIN',0);
 define('CARDINALITY_UNLIMITED', 2147483647); // MAXINT
 
  
 class PropertyCoVarianceDetector {
 	
 	// delegate for basic helper methods
 	private $consistencyHelper;
 	private $bot;
 	// inheritance graphs.
 	private $categoryGraph;
 	private $propertyGraph;
 	
	
	/**
	 * Creates a PropertyCoVarianceDetector
	 */
 	public function PropertyCoVarianceDetector(& $bot) {
 		$this->bot = $bot;
 		$this->consistencyHelper = new ConsistencyHelper();
 		$this->categoryGraph = $this->consistencyHelper->getCategoryInheritanceGraph();
 		$this->propertyGraph = $this->consistencyHelper->getPropertyInheritanceGraph();
 		
 	}
 	
 	/**
 	 * Checks co-variance of all subattributes.
 	 * 
 	 * @return A log indicating inconsistencies for every subattribute definition. (wiki-markup)
 	 */
 	public function checkPropertyGraphForCovariance() {
 		global $smwgContLang;
 		$numLog = 0;
 		$namespaces = $smwgContLang->getNamespaceArray();
 		$completeLog =  "";
 		$attributes = $this->consistencyHelper->getPages(array(SMW_NS_PROPERTY));
 		$cnt = 0;
 		$work = count($attributes);
 		print "\n";
 		$this->bot->addSubTask(count($attributes));
 		foreach($attributes as $a) {
 			$this->bot->worked(1);
 			$log = ""; 
 			$cnt++;
 			if ($cnt % 10 == 1 || $cnt == $work) { 
 				print "\x08\x08\x08\x08".number_format($cnt/$work*100, 0)."% ";
 			}
 			if ($numLog > MAX_LOG_LENGTH) {
 				print (" limit of consistency issues reached. Break. ");
 				return $completeLog;
 			}
 			if ($this->consistencyHelper->domainHintRelation->equals($a) 
 					|| $this->consistencyHelper->rangeHintRelation->equals($a)
 					|| $this->consistencyHelper->minCard->equals($a) 
 					|| $this->consistencyHelper->maxCard->equals($a)
 					|| $this->consistencyHelper->inverseOf->equals($a) 
 					|| $this->consistencyHelper->equalTo->equals($a)  ) {
 						// ignore builtin properties
 						continue;
 			}
  			
 			$this->checkMinCardinality($a, $log, $numLog);
 			$this->checkMaxCardinality($a, $log, $numLog);
 			$this->checkDomainCovariance($a, $log, $numLog);
 			$this->checkTypeEquality($a, $log, $numLog);
 			$this->checkRangeCovariance($a, $log, $numLog);
 			$this->checkSymTransCovariance($a, $log, $numLog);
 			
 			$completeLog .= $log;
 		}
 		return $completeLog;
 	}
 	
 	/** 		
 	 * Check min cardinality for co-variance
  	 */
 	private function checkMinCardinality($a, & $log, & $numLog) {
 		
  			global $smwgContLang;
  			$namespaces = $smwgContLang->getNamespaceArray();
 			$minCard = smwfGetStore()->getPropertyValues($a, $this->consistencyHelper->minCard);
 		
 			if (!empty($minCard)) {
 				// otherwise check min cardinality of parent for co-variance.
 				
 				// check for doubles
 				if (count($minCard) > 1) {
 					$log .= wfMsg('smw_gard_doublemincard', $a->getText(), $minCard[0]->getXSDValue(), $namespaces[$a->getNamespace()])."\n\n";
 					$numLog++;
 				}
 				
 				// check for correct value
 				if ($this->consistencyHelper->isCardinalityValue($minCard[0]->getXSDValue()) !== true) {
 					$log .= wfMsg('smw_gard_wrongcardvalue', $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 					$numLog++;
 				}
 				// read min cards
 				
 				$minCardValue = $minCard[0]->getXSDValue() + 0;
 				$minCardValueOfParent = $this->consistencyHelper->getMinCardinalityOfSuperProperty($this->propertyGraph, $a);
 				
 				$minCardCOVTest = $this->checkMinCardinalityForCovariance($minCardValue, $minCardValueOfParent);
 				if ($minCardCOVTest !== true) {
 					$log .= wfMsg($minCardCOVTest, $a->getText(), $namespaces[$a->getNamespace()])."\n\n"; 
 				}	
 			} else {
 				// check with default min card (CARDINALITY_MIN)
 				
 				$minCardValue = CARDINALITY_MIN;
 				$minCardValueOfParent = $this->consistencyHelper->getMinCardinalityOfSuperProperty($this->propertyGraph, $a);
 			
 				$minCardCOVTest = $this->checkMinCardinalityForCovariance($minCardValue, $minCardValueOfParent);
 				if ($minCardCOVTest !== true) {
 					$log .= wfMsg($minCardCOVTest, $a->getText(), $namespaces[$a->getNamespace()])."\n\n"; 
 				}	
 			}
 	}
 	
 	/** 		
 	 * Check max cardinality for co-variance
  	 */
 	private function checkMaxCardinality($a, & $log, & $numLog) {
 		
 			global $smwgContLang;
  			$namespaces = $smwgContLang->getNamespaceArray();
 			$maxCard = smwfGetStore()->getPropertyValues($a, $this->consistencyHelper->maxCard);
 			
 			if (!empty($maxCard)) {
 				// check for doubles
 				if (count($maxCard) > 1) {
 					$log .= wfMsg('smw_gard_doublemincard', $a->getText(), $maxCard[0]->getXSDValue(), $namespaces[$a->getNamespace()])."\n\n";
 					$numLog++;
 				}
 				// check for correct value
 				if ($this->consistencyHelper->isCardinalityValue($maxCard[0]->getXSDValue()) !== true) {
 					$log .= wfMsg('smw_gard_wrongcardvalue', $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 					$numLog++;
 				}
 				// check for co-variance with parent
 				
 				$maxCardValue = $maxCard[0]->getXSDValue() + 0;
 				$maxCardValueOfParent = $this->consistencyHelper->getMaxCardinalityOfSuperProperty($this->propertyGraph, $a);
 				
 				$maxCardCOVTest = $this->checkMaxCardinalityForCovariance($maxCardValue, $maxCardValueOfParent);
 				if ($maxCardCOVTest !== true) {
 					$log .= wfMsg($maxCardCOVTest, $a->getText(), $namespaces[$a->getNamespace()])."\n\n"; 
 				}	
 			} else {
 				// check with default max card (CARDINALITY_UNLIMITED)
 				
 				$maxCardValue = CARDINALITY_UNLIMITED;
 				$maxCardValueOfParent = $this->consistencyHelper->getMaxCardinalityOfSuperProperty($this->propertyGraph, $a);
 				
 				$maxCardCOVTest = $this->checkMaxCardinalityForCovariance($maxCardValue, $maxCardValueOfParent);
 				if ($maxCardCOVTest !== true) {
 					$log .= wfMsg($maxCardCOVTest, $a->getText(), $namespaces[$a->getNamespace()])."\n\n"; 
 				}	
 			}	
 	}
 	
 	/** 		
 	 * Check domain co-variance
  	 */
 	private function checkDomainCovariance($a, & $log, & $numLog) {
 		global $smwgContLang;
  		$namespaces = $smwgContLang->getNamespaceArray();
  		
 			$domainCategories = smwfGetStore()->getPropertyValues($a, $this->consistencyHelper->domainHintRelation);
 			if (empty($domainCategories)) {
 				$log .= wfMsg('smw_gard_domain_not_defined', $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 				$numLog++;
 			} else {
 				// get domain of parent
 				$domainCategoriesOfSuperProperty = $this->consistencyHelper->getDomainsOfSuperProperty($this->propertyGraph, $a);
 				$covariant = true;
 				foreach($domainCategoriesOfSuperProperty as $domainSuperCat) {
 					$valid = false;
 					foreach($domainCategories as $domainCat) { 
 						if ($this->consistencyHelper->checkForPath($this->categoryGraph, $domainCat->getTitle()->getArticleID(), $domainSuperCat->getTitle()->getArticleID())) {
 							$valid = true;
 						}
 					}
 					$covariant = $covariant && $valid;
 				}
 				if (!$covariant && count($domainCategoriesOfSuperProperty) > 0) {
 					$log .= wfMsg('smw_gard_domains_not_covariant', $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 					$numLog++;
 				}
 				
 			}
 	}
 	
 	/** 		
 	 *  Check type equality
  	 */
 	private function checkTypeEquality($a, & $log, & $numLog) {
 		global $smwgContLang;
  		$namespaces = $smwgContLang->getNamespaceArray();
  		
 			$types = smwfGetStore()->getSpecialValues($a, SMW_SP_HAS_TYPE);
 			if (empty($types)) {
 				//$log .= wfMsg('smw_gard_types_is_not_defined', $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 				//$numLog++;
 			} else {
 				if (count($types) > 1) {
 					$log .= wfMsg('smw_gard_more_than_one_type', $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 					$numLog++;
 				}
 				$typesOfSuperAttribute = $this->consistencyHelper->getTypeOfSuperProperty($this->propertyGraph, $a);
 				$valid = false;
 				// only check first 'has type' value, because if more exist, it will be indicated anyway. 
 				$smwFirstTypeValue = count($typesOfSuperAttribute) > 0 ? $typesOfSuperAttribute[0] : null;
 				if ($smwFirstTypeValue != null && $smwFirstTypeValue instanceof SMWTypesValue) { 
 						if ($smwFirstTypeValue->getXSDValue() == $types[0]->getXSDValue()) {
 							$valid = true;
 						}
 				}
 				if (!$valid && $smwFirstTypeValue != null) {
 					$log .= wfMsg('smw_gard_types_not_covariant', $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 					$numLog++;
 				}
 			}
 	}
 	
 	/** 		
 	 *  Check range co-variance 
 	 *	n-ary attributes may defines ranges which hold for the
 	 *	first wikipage parameter of the attributes. This algorithm
 	 *	simply checks if all ranges a co-variant.
 	 *	If no range is defined, then the type must not contain a wikipage. 
  	 */
 	private function checkRangeCovariance($a, & $log, & $numLog) {
 		global $smwgContLang;
  		$namespaces = $smwgContLang->getNamespaceArray();
  		
 			$rangeCategories = smwfGetStore()->getPropertyValues($a, $this->consistencyHelper->rangeHintRelation);
 			if (empty($rangeCategories)) {
 				// range categories may be empty if types contain no wikipage
 				if (!empty($types)) {
 					// check for wikipage should be more "nice"
 					foreach($types as $type) { 
 						if ($type instanceof SMWTypesValue && in_array("Page", $type->getTypeLabels())) {
 							$log .= wfMsg('smw_gard_range_not_defined', $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 							$numLog++;
 							break;
 						}
 					}
 				}
 			} else { 
 				// get ranges of parent
 				$rangeCategoriesOfSuperProperty = $this->consistencyHelper->getRangesOfSuperProperty($this->propertyGraph, $a);
 				
 				$covariant = true;
 				// check if range categories are sub categories of the range of the super properties 
 				foreach($rangeCategoriesOfSuperProperty as $rangeSuperCat) {
 					$valid = false;
 					foreach($rangeCategories as $rangeCat) { 
 						if ($this->consistencyHelper->checkForPath($this->categoryGraph, $rangeCat->getArticleID(), $rangeSuperCat->getArticleID())) {
 							$valid = true;
 						}
 					}
 					$covariant = $covariant && $valid;
 				}
 				if (!$covariant) {
 					$log .= wfMsg('smw_gard_ranges_not_covariant', $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 					$numLog++;
 				}
 			}
 			
 	}
 	
 	/** 		
 	 * Check symetry and transitivity co-variance
  	 */
 	private function checkSymTransCovariance($a, & $log, & $numLog) {
 		global $smwgContLang;
  		$namespaces = $smwgContLang->getNamespaceArray();
  		
 			$categoriesOfRelation = $this->consistencyHelper->getCategoriesForInstance($a);
 			$categoriesOfSuperRelation = $this->consistencyHelper->getCategoriesOfSuperProperty($this->categoryGraph, $a);
 			
 			$transOfRelation = $this->isTitleInArray($this->consistencyHelper->transitiveCat, $categoriesOfRelation);
 			$transOfSuperRelation = $this->isTitleInArray($this->consistencyHelper->transitiveCat, $categoriesOfSuperRelation);
 			
 			if (($transOfRelation && !$transOfSuperRelation) || (!$transOfRelation && $transOfSuperRelation)) {  
 				$log .= wfMsg('smw_gard_trans_not_covariant', $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 				$numLog++;
 			}
 			
 			$symOfRelation = $this->isTitleInArray($this->consistencyHelper->symetricalCat, $categoriesOfRelation);
 			$symOfSuperRelation = $this->isTitleInArray($this->consistencyHelper->symetricalCat, $categoriesOfSuperRelation);
 			
 			if (($symOfRelation && !$symOfSuperRelation) || (!$symOfRelation && $symOfSuperRelation)) {
 				$log .= wfMsg('smw_gard_symetry_not_covariant', $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 				$numLog++;
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
 			return ('smw_gard_maxcard_not_null');
 		}
 		
 			
 		if ($superattr_max != UNLIMITED) {
 			if ($attr_max > $superattr_max) {
 				return ('smw_gard_maxcard_not_covariant');
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
 			return ('smw_gard_mincard_not_below_null');
 		} 
 		
 		if ($attr_min < $superattr_min) {
 			return ('smw_gard_mincard_not_covariant');
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
 }
 
 
?>
