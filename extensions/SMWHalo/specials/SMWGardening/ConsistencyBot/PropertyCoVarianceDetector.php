<?php
 /*
 * Created on 14.05.2007
 *
 * Author: kai
 */
 
require_once("GraphEdge.php"); 
global $smwgHaloIP;
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
 	
	
	/**
	 * Creates a PropertyCoVarianceDetector
	 */
 	public function PropertyCoVarianceDetector(& $bot, $delay) {
 		$this->bot = $bot;
 		$this->delay = $delay;
 	
 		$this->categoryGraph = smwfGetSemanticStore()->getCategoryInheritanceGraph();
 		$this->propertyGraph = smwfGetSemanticStore()->getPropertyInheritanceGraph();
 		
 	}
 	
 	/**
 	 * Checks co-variance of all subattributes.
 	 * 
 	 * @return A log indicating inconsistencies for every subattribute definition. (wiki-markup)
 	 */
 	public function checkPropertyGraphForCovariance() {
 		global $smwgContLang;
 		$numLog = 0;
 		$namespaces = $smwgContLang->getNamespaces();
 		$completeLog =  "";
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
 			$log = ""; 
 			$cnt++;
 			if ($cnt % 10 == 1 || $cnt == $work) { 
 				print "\x08\x08\x08\x08".number_format($cnt/$work*100, 0)."% ";
 			}
 			if ($numLog > MAX_LOG_LENGTH) {
 				print (" limit of consistency issues reached. Break. ");
 				return $completeLog;
 			}
 			if (smwfGetSemanticStore()->domainHintRelation->equals($a) 
 					|| smwfGetSemanticStore()->rangeHintRelation->equals($a)
 					|| smwfGetSemanticStore()->minCard->equals($a) 
 					|| smwfGetSemanticStore()->maxCard->equals($a)
 					|| smwfGetSemanticStore()->inverseOf->equals($a) ) {
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
  			$namespaces = $smwgContLang->getNamespaces();
 			$minCard = smwfGetStore()->getPropertyValues($a, smwfGetSemanticStore()->minCard);
 		
 			if (!empty($minCard)) {
 				// otherwise check min cardinality of parent for co-variance.
 				
 				// check for doubles
 				if (count($minCard) > 1) {
 					$log .= wfMsg('smw_gard_doublemincard', $a->getText(), $minCard[0]->getXSDValue(), $namespaces[$a->getNamespace()])."\n\n";
 					$numLog++;
 				}
 				
 				// check for correct value
 				if ($this->isCardinalityValue($minCard[0]->getXSDValue()) !== true) {
 					$log .= wfMsg('smw_gard_wrongcardvalue', $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 					$numLog++;
 				}
 				// read min cards
 				
 				$minCardValue = $minCard[0]->getXSDValue() + 0;
 				$minCardValueOfParent = smwfGetSemanticStore()->getMinCardinalityOfSuperProperty($this->propertyGraph, $a);
 				
 				$minCardCOVTest = $this->checkMinCardinalityForCovariance($minCardValue, $minCardValueOfParent);
 				if ($minCardCOVTest !== true) {
 					$log .= wfMsg($minCardCOVTest, $a->getText(), $namespaces[$a->getNamespace()])."\n\n"; 
 				}	
 			} else {
 				// check with default min card (CARDINALITY_MIN)
 				
 				$minCardValue = CARDINALITY_MIN;
 				$minCardValueOfParent = smwfGetSemanticStore()->getMinCardinalityOfSuperProperty($this->propertyGraph, $a);
 			
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
  			$namespaces = $smwgContLang->getNamespaces();
 			$maxCard = smwfGetStore()->getPropertyValues($a, smwfGetSemanticStore()->maxCard);
 			
 			if (!empty($maxCard)) {
 				// check for doubles
 				if (count($maxCard) > 1) {
 					$log .= wfMsg('smw_gard_doublemincard', $a->getText(), $maxCard[0]->getXSDValue(), $namespaces[$a->getNamespace()])."\n\n";
 					$numLog++;
 				}
 				// check for correct value
 				if ($this->isCardinalityValue($maxCard[0]->getXSDValue()) !== true && $maxCard[0]->getXSDValue() != '*') {
 					$log .= wfMsg('smw_gard_wrongcardvalue', $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 					$numLog++;
 				}
 				// check for co-variance with parent
 				
 				$maxCardValue = $maxCard[0]->getXSDValue() == '*' ? CARDINALITY_UNLIMITED : $maxCard[0]->getXSDValue() + 0;
 				$maxCardValueOfParent = smwfGetSemanticStore()->getMaxCardinalityOfSuperProperty($this->propertyGraph, $a);
 				
 				$maxCardCOVTest = $this->checkMaxCardinalityForCovariance($maxCardValue, $maxCardValueOfParent);
 				if ($maxCardCOVTest !== true) {
 					$log .= wfMsg($maxCardCOVTest, $a->getText(), $namespaces[$a->getNamespace()])."\n\n"; 
 				}	
 			} else {
 				// check with default max card (CARDINALITY_UNLIMITED)
 				
 				$maxCardValue = CARDINALITY_UNLIMITED;
 				$maxCardValueOfParent = smwfGetSemanticStore()->getMaxCardinalityOfSuperProperty($this->propertyGraph, $a);
 				
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
  		$namespaces = $smwgContLang->getNamespaces();
  		
 			$domainCategories = smwfGetStore()->getPropertyValues($a, smwfGetSemanticStore()->domainHintRelation);
 			if (empty($domainCategories)) {
 				$log .= wfMsg('smw_gard_domain_not_defined', $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 				$numLog++;
 			} else {
 				// get domain of parent
 				$domainCategoriesOfSuperProperty = smwfGetSemanticStore()->getDomainsOfSuperProperty($this->propertyGraph, $a);
 				$covariant = true;
 				foreach($domainCategoriesOfSuperProperty as $domainSuperCat) {
 					$pathExists = false;
 					foreach($domainCategories as $domainCat) { 
 						if (GraphHelper::checkForPath($this->categoryGraph, $domainCat->getTitle()->getArticleID(), $domainSuperCat->getTitle()->getArticleID())) {
 							$pathExists = true;
 						}
 					}
 					$covariant = $covariant && $pathExists;
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
  		$namespaces = $smwgContLang->getNamespaces();
  		
 			$types = smwfGetStore()->getSpecialValues($a, SMW_SP_HAS_TYPE);
 			if (empty($types)) {
 				//$log .= wfMsg('smw_gard_types_is_not_defined', $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 				//$numLog++;
 			} else {
 				if (count($types) > 1) {
 					$log .= wfMsg('smw_gard_more_than_one_type', $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 					$numLog++;
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
  		$namespaces = $smwgContLang->getNamespaces();
  		
 			$rangeCategories = smwfGetStore()->getPropertyValues($a, smwfGetSemanticStore()->rangeHintRelation);
 			if (empty($rangeCategories)) {
 				$types = smwfGetStore()->getSpecialValues($a, SMW_SP_HAS_TYPE);
 				// range categories may be empty if types contain no wikipage
 				if (!empty($types)) {
 					
 					foreach($types as $type) { 
 						if ($type instanceof SMWTypesValue && $type->getTypeID() == '_wpg') {
 							$log .= wfMsg('smw_gard_range_not_defined', $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 							$numLog++;
 							break;
 						}
 					}
 				} else { // types empty -> default is Page
 					$log .= wfMsg('smw_gard_range_not_defined', $a->getText(), $namespaces[$a->getNamespace()])."\n\n";
 					$numLog++;
 					
 				}
 			} else { 
 				// get ranges of parent
 				$rangeCategoriesOfSuperProperty = smwfGetSemanticStore()->getRangesOfSuperProperty($this->propertyGraph, $a);
 				
 				$covariant = true;
 				// check if range categories are sub categories of the range of the super properties 
 				foreach($rangeCategoriesOfSuperProperty as $rangeSuperCat) {
 					$pathExists = false;
 					foreach($rangeCategories as $rangeCat) { 
 						if (GraphHelper::checkForPath($this->categoryGraph, $rangeCat->getArticleID(), $rangeSuperCat->getArticleID())) {
 							$pathExists = true;
 						}
 					}
 					$covariant = $covariant && $pathExists;
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
  		$namespaces = $smwgContLang->getNamespaces();
  			
  			
  			if (count(smwfGetSemanticStore()->getDirectSuperProperties($a)) == 0) {
 				return; // $a has no superproperty
 			}
 			
 			$categoriesOfRelation = smwfGetSemanticStore()->getCategoriesForInstance($a);
 			$categoriesOfSuperRelation = smwfGetSemanticStore()->getCategoriesOfSuperProperty($this->propertyGraph, $a);
 			 			
 			$transOfRelation = $this->isTitleInArray(smwfGetSemanticStore()->transitiveCat, $categoriesOfRelation);
 			$transOfSuperRelation = $this->isTitleInArray(smwfGetSemanticStore()->transitiveCat, $categoriesOfSuperRelation);
 			
 			 			
 			if (($transOfRelation && !$transOfSuperRelation)) {  
 				$log .= wfMsg('smw_gard_trans_not_covariant1', $a->getText(), $a->getNsText())."\n\n";
 				$numLog++;
 			} else if ((!$transOfRelation && $transOfSuperRelation)) {
 				
 				$log .= wfMsg('smw_gard_trans_not_covariant2', $a->getText(), $a->getNsText())."\n\n";
 				$numLog++;
 			}
 			
 			$symOfRelation = $this->isTitleInArray(smwfGetSemanticStore()->symetricalCat, $categoriesOfRelation);
 			$symOfSuperRelation = $this->isTitleInArray(smwfGetSemanticStore()->symetricalCat, $categoriesOfSuperRelation);
 			
 			if (($symOfRelation && !$symOfSuperRelation)) {
 				$log .= wfMsg('smw_gard_symetry_not_covariant1', $a->getText(), $a->getNsText())."\n\n";
 				$numLog++;
 			} else if ((!$symOfRelation && $symOfSuperRelation)) {
 				$log .= wfMsg('smw_gard_symetry_not_covariant2', $a->getText(), $a->getNsText())."\n\n";
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
 		
 			
 		if ($superattr_max != CARDINALITY_UNLIMITED) {
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
 	
 	/**
 	 * Checks if $s is a positive integer or 0.
 	 */
 	private function isCardinalityValue($s) {
 		// card must be either an integer >= 0 
		return preg_match('/^\d+$/', trim($s)) > 0;
 	}
 }
 
 
?>
