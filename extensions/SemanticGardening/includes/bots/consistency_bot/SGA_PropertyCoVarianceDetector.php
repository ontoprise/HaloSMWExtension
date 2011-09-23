isDomainRangeCovariant<?php
/**
 * @file
 * @ingroup ConsistencyBot
 * 
 * @author Kai K�hn
 * 
 * Created on 14.05.2007
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

global $sgagIP;
require_once("SGA_GraphEdge.php");
require_once("$sgagIP/includes/SGA_GraphHelper.php");




class PropertyCoVarianceDetector {

    // reference to bot
    private $bot;

    // number of properties to load at once.
    private $limit;

    // ConsistencyChecker store
    private $cc_store;

    // delay
    private $delay;

    // inheritance graphs.
    private $categoryGraph;
    private $propertyGraph;

    // GardeningIssue store
    private $gi_store;

    private $verbose = false;

    /**
     * Creates a PropertyCoVarianceDetector
     */
    public function PropertyCoVarianceDetector(& $bot, $delay, & $categoryGraph, & $propertyGraph, $verbose = false) {
        $this->bot = $bot;
        $this->cc_store = ConsitencyBotStorage::getConsistencyStorage();
        $this->delay = $delay;

        $this->categoryGraph = $categoryGraph;
        $this->propertyGraph = $propertyGraph;
        $this->gi_store = SGAGardeningIssuesAccess::getGardeningIssuesAccess();
            
        $this->verbose = $verbose;
        $this->limit = 100;
    }

    /**
     * Checks co-variance of all subattributes.
     *
     * @return A log indicating inconsistencies for every subattribute definition. (wiki-markup)
     */
    public function checkPropertyGraphForCovariance() {

        $requestoptions = new SMWRequestOptions();
        $requestoptions->limit = $this->limit;
        $requestoptions->offset = 0;
        $totalWork = smwfGetSemanticStore()->getNumber(SMW_NS_PROPERTY);
        if ($this->verbose) $this->bot->addSubTask($totalWork);

        do {
            $properties = smwfGetSemanticStore()->getPages(array(SMW_NS_PROPERTY), $requestoptions);
                
            foreach($properties as $a) {

                if ($this->verbose && $this->bot->isAborted()) break;
                usleep($this->delay);

                if ($this->verbose) {
                    $this->bot->worked(1);
                    $workDone = $this->bot->getCurrentWorkDone();
                    if ($workDone % 10 == 1 || $workDone == $totalWork) {
                        GardeningBot::printProgress($workDone/$totalWork);
                    }
                }
				
                if (SMWHaloPredefinedPages::$HAS_DOMAIN_AND_RANGE->equals($a)
						|| SMWHaloPredefinedPages::$HAS_MIN_CARDINALITY->equals($a)
                		|| SMWHaloPredefinedPages::$HAS_MAX_CARDINALITY->equals($a)
                		|| SMWHaloPredefinedPages::$IS_INVERSE_OF->equals($a) ) {
                    // ignore builtin properties
                    continue;
                }
                    

                $this->checkPropertyForCovariance($a);

            }
            $requestoptions->offset += $this->limit;
        } while(count($properties) == $this->limit);
    }

    public function checkPropertyForCovariance($p) {
    	
        $this->checkMinCardinality($p);
       
        $this->checkMaxCardinality($p);
    
        $this->checkDomainAndRangeCovariance($p);
        
        $this->checkTypeEquality($p);
       
        
    }

    /**
     * Check min cardinality for co-variance
     */
    private function checkMinCardinality($a) {


        
     
        $prop = SMWDIProperty::newFromUserLabel(SMWHaloPredefinedPages::$HAS_MIN_CARDINALITY->getText());
    	$minCard = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle($a), $prop);
      
        if (!empty($minCard)) {
            // otherwise check min cardinality of parent for co-variance.

            // check for doubles
            if (count($minCard) > 1) {
                $firstMinCard = GardeningBot::getXSDValue(reset($minCard));
                $this->gi_store->addGardeningIssueAboutValue($this->bot->getBotID(), SMW_GARDISSUE_DOUBLE_MIN_CARD, $a, $firstMinCard);

            }

            // check for correct value
            $minCardDV = reset($minCard);
            if ($this->isCardinalityValue(GardeningBot::getXSDValue($minCardDV)) !== true) {

                $this->gi_store->addGardeningIssueAboutValue($this->bot->getBotID(), SMW_GARDISSUE_WRONG_MINCARD_VALUE, $a, GardeningBot::getXSDValue($minCardDV));

            }
            // read min cards

            $minCardValue = intval(GardeningBot::getXSDValue($minCardDV));
            $minCardValueOfParent = $this->cc_store->getMinCardinalityOfSuperProperty($this->propertyGraph, $a);

            $minCardCOVTest = $this->checkMinCardinalityForCovariance($minCardValue, $minCardValueOfParent);
            if ($minCardCOVTest !== true) {

                $this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), constant($minCardCOVTest), $a);
            }
        } else {
            // check with default min card (CARDINALITY_MIN)

            $minCardValue = CARDINALITY_MIN;
            $minCardValueOfParent = $this->cc_store->getMinCardinalityOfSuperProperty($this->propertyGraph, $a);

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


        
        $maxCard = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle($a), 
        	SMWDIProperty::newFromUserLabel(SMWHaloPredefinedPages::$HAS_MAX_CARDINALITY->getText()));

         if (!empty($maxCard)) {
            // check for doubles
            if (count($maxCard) > 1) {
                $firstMaxCard = GardeningBot::getXSDValue(reset($maxCard));
                $this->gi_store->addGardeningIssueAboutValue($this->bot->getBotID(), SMW_GARDISSUE_DOUBLE_MAX_CARD, $a, $firstMaxCard);

            }
            // check for correct value format
            $maxCardDV = reset($maxCard);
            if ($this->isCardinalityValue(GardeningBot::getXSDValue($maxCardDV)) !== true) {
                $this->gi_store->addGardeningIssueAboutValue($this->bot->getBotID(), SMW_GARDISSUE_WRONG_MAXCARD_VALUE, $a, GardeningBot::getXSDValue($maxCardDV));

            }
            // check for co-variance with parent

            $maxCardValue = intval(GardeningBot::getXSDValue($maxCardDV));
            $maxCardValueOfParent = $this->cc_store->getMaxCardinalityOfSuperProperty($this->propertyGraph, $a);

            $maxCardCOVTest = $this->checkMaxCardinalityForCovariance($maxCardValue, $maxCardValueOfParent);
            if ($maxCardCOVTest !== true) {
                $this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), constant($maxCardCOVTest), $a);

            }
        } else {
            // check with default max card (CARDINALITY_UNLIMITED)

            $maxCardValue = CARDINALITY_UNLIMITED;
            $maxCardValueOfParent = $this->cc_store->getMaxCardinalityOfSuperProperty($this->propertyGraph, $a);

            $maxCardCOVTest = $this->checkMaxCardinalityForCovariance($maxCardValue, $maxCardValueOfParent);
            if ($maxCardCOVTest !== true) {
                $this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), constant($maxCardCOVTest), $a);

            }
        }
    }

    /**
     * Check domain co-variance. Does also check if there are domains and/or range defined at all.
     */
    private function checkDomainAndRangeCovariance($p) {
    	$hasTypeDV = SMWPropertyValue::makeProperty("_TYPE");
        $type = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle($p), 
        	SMWDIProperty::newFromUserLabel($hasTypeDV->getText()));
      
        $firstType = reset($type);
    	$hasTypePage = false;
        if (count($type) == 0){
        	$hasTypePage = true;
        } else {
        	$typeId = GardeningBot::getXSDValue($firstType);
        	$typeId = substr($typeId, strlen($typeId) - 4);
        	if($typeId == '_wpg' || $typeId == '_nry'){
        		$hasTypePage = true;
        	} 
        }
		if($hasTypePage) {
			
			// default property (type wikipage), explicitly defined wikipage or nary property
            $domainRangeAnnotations = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle($p), 
            	SMWDIProperty::newFromUserLabel(SMWHaloPredefinedPages::$HAS_DOMAIN_AND_RANGE->getText()));

			if (empty($domainRangeAnnotations)) {
                $this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_DOMAINS_AND_RANGES_NOT_DEFINED, $p);
                return;
            }

            if (!$this->containsDomain($domainRangeAnnotations)) {
                // no domain
                $this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_DOMAINS_NOT_DEFINED, $p);
            }

            if (count($type) > 0 
            		&& substr(GardeningBot::getXSDValue($firstType), strlen(GardeningBot::getXSDValue($firstType)) - 5) == '__nry') {
                // n-ary relation
                // only complain about missing range if it contains at least one Type:Page
                if ($this->containsPageType($firstType) && !$this->containsRange($domainRangeAnnotations)) {
                    // no range
                    $this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_RANGES_NOT_DEFINED, $p);
                }
            } else {
                // binary relation
                if (!$this->containsRange($domainRangeAnnotations)) {
                    // no range
                    $this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_RANGES_NOT_DEFINED, $p);
                }
            }

            $res = $this->isDomainRangeCovariant($p, $domainRangeAnnotations);
            
        	if ($res === true) return;
            foreach($res as $cov) {
                $domain = $cov[2]->getSemanticData()->getPropertyValues(
                	SMWDIProperty::newFromUserLabel('Has domain'));
                $range = $cov[2]->getSemanticData()->getPropertyValues(
                	SMWDIProperty::newFromUserLabel('Has range'));
                if (!$cov[0] ) {
                	// log domain cov error for annot
                    $this->gi_store->addGardeningIssueAboutArticles(
                    	$this->bot->getBotID(), SMW_GARDISSUE_DOMAINS_NOT_COVARIANT, $p, $domain[0]->getTitle());
                }
                if (!$cov[1]) {
                    // log range cov error for annot
                    $this->gi_store->addGardeningIssueAboutArticles($this->bot->getBotID(), SMW_GARDISSUE_RANGES_NOT_COVARIANT, $p, $range[0]->getTitle());
                }
            }
        } else {
            // attribute
            $domainRangeAnnotations = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle($p), 
            	SMWDIProperty::newFromUserLabel(SMWHaloPredefinedPages::$HAS_DOMAIN_AND_RANGE->getText()));

            if (empty($domainRangeAnnotations)) {
                $this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_DOMAINS_NOT_DEFINED, $p);
                return;
            }

            if (!$this->containsDomain($domainRangeAnnotations)) {
                // no domain
                $this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_DOMAINS_NOT_DEFINED, $p);
            }

            $res = $this->isDomainRangeCovariant($p, $domainRangeAnnotations, true);
            
        	if ($res === true) return;
            foreach($res as $cov) {
                $domain = $cov[2]->getSemanticData()->getPropertyValues(SMWDIProperty::newFromUserLabel('Has domain'));
                $domain = $domain[0];
                if (!$cov[0]) {
                    // log domain cov error for annot
                    $this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_DOMAINS_NOT_COVARIANT, $p, $domain->getTitle());
                }

            }
        }


    }

    private function isDomainRangeCovariant($p, $domainRangeAnnotations, $isAttribute = false) {

        $domainRangeAnnotationsOfSuperProperty = $this->cc_store->getDomainsAndRangesOfSuperProperty($this->propertyGraph, $p);

        if (empty($domainRangeAnnotationsOfSuperProperty)) {
            return true;
        }
        
    	$results = array();

        foreach($domainRangeAnnotations as $dra) {
            $current = array(false, false, $dra);
            $domain = $dra->getSemanticData()->getPropertyValues(
            	SMWDIProperty::newFromUserLabel('Has domain'));
            $range = $dra->getSemanticData()->getPropertyValues(
            	SMWDIProperty::newFromUserLabel('Has range'));
            $domainCat =  count($domain) > 0 ? $domain[0]->getTitle() : NULL;
            $rangeCat =  count($range) > 0 ? $range[0]->getTitle() : NULL;

        	foreach($domainRangeAnnotationsOfSuperProperty as $drosp) {
                $domainOfSuperProperty = $drosp->getSemanticData()->getPropertyValues(
            		SMWDIProperty::newFromUserLabel('Has domain'));
            	$rangeOfSuperProperty = $drosp->getSemanticData()->getPropertyValues(
            		SMWDIProperty::newFromUserLabel('Has range'));
            	$domainCatOfSuperProperty =  count($domainOfSuperProperty) > 0 ? $domainOfSuperProperty[0]->getTitle() : NULL;
            	$rangeCatOfSuperProperty =  count($rangeOfSuperProperty) > 0 ? $rangeOfSuperProperty[0]->getTitle() : NULL;
            	
            	if ($domainCat != NULL && $domainCatOfSuperProperty != NULL) {


                    $domainCovariant = GraphHelper::checkForPath($this->categoryGraph, $domainCat->getArticleID(), $domainCatOfSuperProperty->getArticleID());

            		if (!$isAttribute && ($rangeCat != NULL && $rangeCatOfSuperProperty != NULL)) {
                        $rangeCovariant = (GraphHelper::checkForPath($this->categoryGraph, $rangeCat->getArticleID(), $rangeCatOfSuperProperty->getArticleID()));

                    } else {
                        $rangeCovariant = true;
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



    /**
     *  Check type equality
     */
    private function checkTypeEquality($a) {
        global $smwgContLang;
        
        $hasTypeDV = SMWPropertyValue::makeProperty("_TYPE");
        $types = smwfGetStore()->getPropertyValues(SMWDIWikiPage::newFromTitle($a), 
        	$hasTypeDV->getDataItem());
        if (empty($types)) {
            $this->gi_store->addGardeningIssueAboutArticle($this->bot->getBotID(), SMW_GARDISSUE_TYPES_NOT_DEFINED, $a, count($types));
        } else {
            if (count($types) > 1) {
				$this->gi_store->addGardeningIssueAboutValue($this->bot->getBotID(), SMW_GARDISSUE_DOUBLE_TYPE, $a, count($types));
                return;
            }
            
            $typesOfSuperAttribute = $this->cc_store->getTypeOfSuperProperty($this->propertyGraph, $a);
            $typesAsString = array();
            foreach($typesOfSuperAttribute as $t) {
                $typesAsString[] = GardeningBot::getXSDValue($t);
            }
            $typesAsString = array_unique($typesAsString);
            if (count($typesAsString) > 1) {
                $this->gi_store->addGardeningIssueAboutValue($this->bot->getBotID(), SMW_GARD_ISSUE_INCOMPATIBLE_SUPERTYPES, $a, count($typesAsString));
                return;
            }
            $covariant = false;
            // only check first 'has type' value, because if more exist, it will be indicated anyway.
            $firstType = count($typesOfSuperAttribute) > 0 ? reset($typesOfSuperAttribute) : null;
            if ($firstType != null && $firstType instanceof SMWTypesValue) {
                if (GardeningBot::getXSDValue($firstType) == GardeningBot::getXSDValue(reset($types))) {
                    $covariant = true;
                }
            }
            if (!$covariant && $firstType != null) {
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
        $categoriesOfSuperRelation = $this->cc_store->getCategoriesOfSuperProperty($this->propertyGraph, $a);

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
     * Returns true if $domainRangeAnnotations contains at least one domain category.
     * Otherwise false.
     */
    private function containsDomain($domainRangeAnnotations) {
        foreach($domainRangeAnnotations as $dra) {
            $semData = $dra->getSemanticData();
            $domainAnnotations = $semData->getPropertyValues(
            	SMWDIProperty::newFromUserLabel('Has domain')); 
        	if(count($domainAnnotations) > 0) return true;
        }
        return false;
    }

    /**
     * Returns true if $domainRangeAnnotations contains at least one range category.
     * Otherwise false.
     */
    private function containsRange($domainRangeAnnotations) {
         foreach($domainRangeAnnotations as $dra) {
            $semData = $dra->getSemanticData();
            $rangeAnnotations = $semData->getPropertyValues(
            	SMWDIProperty::newFromUserLabel('Has range')); 
        	if(count($rangeAnnotations) > 0) return true;
        }
        return false;
    }

    /**
     * Returns true if $typeContainer contains at least one Type:Page
     */
    private function containsPageType($typeContainer) {
        $containsPageType = false;
        foreach($typeContainer as $t) {
            $t = $dra->getDVs();
            if($t->getID() == '_wpg') $containsPageType |= true;
        }
        return $containsPageType;
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



