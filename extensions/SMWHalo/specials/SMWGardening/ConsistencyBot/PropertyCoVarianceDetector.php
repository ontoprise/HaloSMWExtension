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
 			
 			if (smwfGetSemanticStore()->domainHintRelation->equals($a) 
 					|| smwfGetSemanticStore()->rangeHintRelation->equals($a)
 					|| smwfGetSemanticStore()->minCard->equals($a) 
 					|| smwfGetSemanticStore()->maxCard->equals($a)
 					|| smwfGetSemanticStore()->inverseOf->equals($a) ) {
 						// ignore builtin properties
 						continue;
 			}
  			
 			$this->checkMinCardinality($a);
 			$this->checkMaxCardinality($a);
 			$this->checkDomainCovariance($a);
 			$this->checkTypeEquality($a);
 			$this->checkRangeCovariance($a);
 			$this->checkSymTransCovariance($a);
 			
 			
 		}
 		return '';
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
 	private function checkDomainCovariance($a) {
 		global $smwgContLang;
  		  		
 			$domainCategories = smwfGetStore()->getPropertyValues($a, smwfGetSemanticStore()->domainHintRelation);
 			if (empty($domainCategories)) {
 				
 				$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_DOMAINS_NOT_DEFINED, $a);
 				
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
 					
 					$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_DOMAINS_NOT_COVARIANT, $a);
 					
 				}
 				
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
 	 *  Check range co-variance 
 	 *	n-ary attributes may defines ranges which hold for the
 	 *	first wikipage parameter of the attributes. This algorithm
 	 *	simply checks if all ranges a co-variant.
 	 *	If no range is defined, then the type must not contain a wikipage. 
  	 */
 	private function checkRangeCovariance($a) {
 		global $smwgContLang;
  		  		
 			$rangeCategories = smwfGetStore()->getPropertyValues($a, smwfGetSemanticStore()->rangeHintRelation);
 			if (empty($rangeCategories)) {
 				$types = smwfGetStore()->getSpecialValues($a, SMW_SP_HAS_TYPE);
 				// range categories may be empty if types contain no wikipage
 				if (!empty($types)) {
 					
 					foreach($types as $type) { 
 						if ($type instanceof SMWTypesValue && $type->getTypeID() == '_wpg') {
 							
 							$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_RANGES_NOT_DEFINED, $a);
 							
 							break;
 						}
 					}
 				} else { // types empty -> default is Page
 				
 					$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_RANGES_NOT_DEFINED, $a);
 					
 					
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
 				
 					$this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_RANGES_NOT_COVARIANT, $a);
 					
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
