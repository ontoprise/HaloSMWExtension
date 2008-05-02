<?php
/*
 * SMW_LocalGardeningJob.php
 * 
 * This job is triggered whenever a page was saved or removed. 
 *
 * @author kai
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) {
  die( "This file is part of the SMWHalo Extension. It is not a valid entry point.\n" );
}
global $IP;
require_once( "$IP/includes/JobQueue.php" );


class SMW_LocalGardeningJob extends Job {
    		
	// action which was done (save, remove)
	public $action;
	
	// pages which are relevant for consistency checking
	public $pagesToCheck;
	
	// need not to be serialized (how to do this?)
	public $categoryGraph = NULL;
	public $propertyGraph = NULL;
	public $annot_checker = NULL;
	public $cov_checker = NULL;
	
	/**
	 * Creates a LocalGardeningJob
	 *
	 * @param Title $title
	 * @param string $action (save, remove)
	 */
    function __construct(Title $title, $action) {
        wfDebug(__METHOD__." ".get_class($this)." \r\n");
        parent::__construct( get_class($this), $title);
       
        $this->action = $action;
        $this->pagesToCheck = $this->selectPages($title);
      
    }
    
    /**
     * Selects pages which are relevant in any manner for $action on $title. 
     *
     * @return Array of Title
     */
    private function selectPages(Title $title) {
    	switch($title->getNamespace()) {
    		case SMW_NS_PROPERTY: return PageSelector::getPagesForPropertySave($title); break;
    		case NS_CATEGORY: return PageSelector::getPagesForCategorySave($title); break;
    		case NS_MAIN: return PageSelector::getPagesForInstanceSave($title); break;
    		case SMW_NS_TYPE: return array(); break;
    	}
    	return NULL;
    }
    
   
    private function checkCategoryChange(Title $property) {
    	$gi_store = SMWGardeningIssuesAccess::getGardeningIssuesAccess();
        
    	$subjects = smwfGetStore()->getAllPropertySubjects($property);
    	foreach($subjects as $s) {
    		$gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARDISSUE_WRONG_DOMAIN_VALUE, NULL,$s, $property);
    		$gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARDISSUE_WRONG_TARGET_VALUE, NULL,$s, $property);
    		$gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARD_ISSUE_MISSING_PARAM, NULL, $s,$property);
       	}
      	$this->annot_checker->checkPropertyAnnotations($subjects, $property);
    }
    
    private function checkPropertyChange(Title $property) {
    	$gi_store = SMWGardeningIssuesAccess::getGardeningIssuesAccess();

    	// clear all issues which will be checked
    	$gi_store->clearGardeningIssues('smw_consistencybot', NULL, $property);
        $subjects = smwfGetStore()->getAllPropertySubjects($property);
        foreach($subjects as $s) {
            $gi_store->clearGardeningIssues('smw_consistencybot', NULL, SMW_CONSISTENCY_BOT_BASE, $property);
            $gi_store->clearGardeningIssues('smw_consistencybot', NULL, SMW_CONSISTENCY_BOT_BASE + 1, $property);
            $gi_store->clearGardeningIssues('smw_consistencybot', NULL, SMW_CONSISTENCY_BOT_BASE + 2, $property);
            
            $gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARDISSUE_MAXCARD_NOT_NULL, NULL, $property);
            $gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARDISSUE_MINCARD_BELOW_NULL, NULL, $property);
            $gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARDISSUE_WRONG_MINCARD_VALUE, NULL,$property);
            $gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARDISSUE_WRONG_MAXCARD_VALUE,NULL, $property);
            
            $gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARDISSUE_WRONG_TARGET_VALUE, NULL,$s, $property);
            $gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARDISSUE_WRONG_DOMAIN_VALUE,NULL, $s, $property);
            $gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARDISSUE_TOO_LOW_CARD,NULL, $s, $property);
            $gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARDISSUE_TOO_HIGH_CARD,NULL, $s, $property);
            $gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARDISSUE_WRONG_UNIT, NULL,$s, $property);
            $gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARD_ISSUE_MISSING_PARAM, NULL, $s, $property);
            
        }
        $gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARDISSUE_MISSING_ANNOTATIONS, NULL, $property);
        
    	// covariance check
    	$this->cov_checker->checkPropertyForCovariance($property);
                
    	// domain/range check
        $this->annot_checker->checkPropertyAnnotations($subjects, $property);
        
        // cardinality check
        $this->annot_checker->checkAnnotationCardinalities($property);
        
        //TODO: unit checks?
    }
    
    private function checkInstanceChange(array & $domainProperties) {
    	if ($this->title == NULL) return;
    	$gi_store = SMWGardeningIssuesAccess::getGardeningIssuesAccess();
    	
    	// clear issues to check again
    	$gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARDISSUE_WRONG_DOMAIN_VALUE, NULL,$instance);
        $gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARDISSUE_WRONG_TARGET_VALUE, NULL,$instance);
        $gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARD_ISSUE_MISSING_PARAM, NULL,$instance);
        $gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARDISSUE_TOO_LOW_CARD, NULL,$instance);
        $gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARDISSUE_TOO_HIGH_CARD, NULL,$instance);
        $gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARDISSUE_MISSING_ANNOTATIONS, NULL,$instance);
        $gi_store->clearGardeningIssues('smw_consistencybot', SMW_GARDISSUE_WRONG_UNIT, NULL,$instance);
        
    	$instance = $this->title;
    	$subjects[] = $instance;
        $properties = smwfGetStore()->getProperties($instance);
               
        // domain/range check
        foreach($properties as $property) {
            $this->annot_checker->checkPropertyAnnotations($subjects, $property);
            
        }
        
        // cardinality check
        $this->annot_checker->checkAnnotationCardinalitiesForInstance($instance, $domainProperties);
        
        // units check
        $this->annot_checker->checkUnitForInstance($instance);
    }
    
    private function checkTypeChange(Title $type) {
    	if ($type == NULL) return;
    	//TODO: delete unit issues
        foreach($properties as $property) {
            $this->annot_checker->checkUnits($type);
        }
    }
    /**
     * Run a SMW_SemanticUpdate job
     * @return boolean success
     */
    function run() {
        wfDebug(__METHOD__);
        wfProfileIn( __METHOD__ );
        
        // import necessary Gardening scripts
        global $smwgHaloIP;
        require_once( $smwgHaloIP . "/specials/SMWGardening/SMW_GardeningIssues.php");
        require_once( $smwgHaloIP . "/specials/SMWGardening/ConsistencyBot/SMW_ConsistencyBot.php");
        
        // initialize consistency checkers
        global $registeredBots;
        $cc_store = ConsitencyBotStorage::getConsistencyStorage();
        $this->categoryGraph = $cc_store->getCategoryInheritanceGraph();
        $this->propertyGraph = $cc_store->getPropertyInheritanceGraph();
        $cc_bot = $registeredBots['smw_consistencybot'];
        $this->annot_checker = new AnnotationLevelConsistency($cc_bot, 0, $this->categoryGraph, $this->propertyGraph, true);
        $this->cov_checker = new PropertyCoVarianceDetector($cc_bot, 0, $this->categoryGraph, $this->propertyGraph, true);

       
        switch($this->title->getNamespace()) {
        	case NS_CATEGORY: foreach($this->pagesToCheck as $p) {
        		                    
						            print "Checking annotations of '".$p."'...";
						            $this->checkCategoryChange(Title::newFromText($p, SMW_NS_PROPERTY));
						            print "done.\n";
						      } break;
        	case SMW_NS_PROPERTY: foreach($this->pagesToCheck as $p) {
                                    print "Checking annotations and covariance of '".$p."'...";
                                    
                                    $this->checkPropertyChange(Title::newFromText($p, SMW_NS_PROPERTY));
                                    print "done.\n";
                              } break;	
        	case NS_MAIN:           print "Checking annotations of '".$this->title."'...";
                                    $this->checkInstanceChange(TitleHelper::string2Title($this->pagesToCheck));
                                    print "done.\n";
                                break;
        	case SMW_NS_TYPE:       print "Checking annotations of '".$this->title."'...";
                                    $this->checkTypeChange($this->title);
                                    print "done.\n";
                                break;
        }
        
        wfProfileOut( __METHOD__ );
        return true;
    }
}

class PageSelector {
	
	/**
	 * Returns properties whose annotations must be checked when a category has been saved.
	 *
	 * @param Title $title
	 * @return Array of Title
	 */
	static function getPagesForCategorySave(Title $title) {
		$properties = array();
		$allSuperCategories = array();
		$visitedNodes = array();
		PageSelector::getAllSuperCategories($title, $allSuperCategories, $visitedNodes);
		foreach($allSuperCategories as $c) {
	       	$domainProperties = smwfGetSemanticStore()->getPropertiesWithDomain($c);
			$rangeProperties = smwfGetSemanticStore()->getPropertiesWithRange($c);
			$properties = array_merge($properties, $domainProperties, $rangeProperties);
		}
		return TitleHelper::title2string(PageSelector::makeTitlesUnique($properties));
	}
	
	/**
	 * Returns properties whose annotations must be checked when a property has been saved.
	 *
	 * @param Title $title
	 * @return Array of Title
	 */
	static function getPagesForPropertySave(Title $title) {
		$allSubProperties = array();
		$visitedNodes = array();
		PageSelector::getAllSubProperties($title, $allSubProperties, $visitedNodes);
		return TitleHelper::title2string(PageSelector::makeTitlesUnique($allSubProperties));
	}
	
	static function getPagesForInstanceSave(Title $title) {
		$properties = array();
        $allSuperCategories = array();
        $visitedNodes = array();
        $directCats = smwfGetSemanticStore()->getCategoriesForInstance($title);
        foreach($directCats as $dc) {
	        PageSelector::getAllSuperCategories($dc, $allSuperCategories, $visitedNodes);
	        foreach($allSuperCategories as $c) {
	            $domainProperties = smwfGetSemanticStore()->getPropertiesWithDomain($c);
	            $properties = array_merge($properties, $domainProperties);
	        }
        }
        return TitleHelper::title2string(PageSelector::makeTitlesUnique($properties));
	}
	
	// Note: may contain duplicates. cycle safe.
	private static function getAllSuperCategories(Title $category, & $results, & $visitedNodes) {
	
		$store = smwfGetSemanticStore();
		$superCategories = $store->getDirectSuperCategories($category);
		foreach($superCategories as $sc) {
			if (in_array($sc->getDBkey(), $visitedNodes)) continue;
			array_push($visitedNodes, $sc->getDBkey());
			PageSelector::getAllSuperCategories($sc, $results, $visitedNodes);
		}
    	$results[] = $category;
    	array_pop($visitedNodes);
    	
	}
	
	// Note: may contain duplicates. cycle safe.
	private static function getAllSubProperties(Title $property, & $results, & $visitedNodes) {
        
        $store = smwfGetSemanticStore();
        $superProperties = $store->getDirectSubProperties($property);
        foreach($superProperties as $sp) {
            if (in_array($sp->getDBkey(), $visitedNodes)) continue;
            array_push($visitedNodes, $sp->getDBkey());
            PageSelector::getAllSuperCategories($sp, $results, $visitedNodes);
        }
        $results[] = $property;
        array_pop($visitedNodes);
       
    }
    
    /**
     * Returns an array of prefixed title names which contains every title only once. 
     *
     * @param array Title $titles
     * @return array of prefixed title names
     */
    private static function makeTitlesUnique(array & $titles) {
    	$uniques = array();
    	for ($i = 0, $n = count($titles); $i < $n; $i++) {
	    	for ($j = 0, $n = count($titles); $j < $n-1; $j++) {
	            if (strcmp($titles[$j+1]->getDBkey(), $titles[$j]->getDBkey()) < 0) {
	            	$help = $titles[$j+1];
	            	$titles[$j+1] = $titles[$j];
	            	$titles[$j] = $help;
	            }
	        }
    	}
    	$newTitle = NULL;
    	foreach($titles as $t) {
    		if ($newTitle == NULL || $newTitle->getDBkey() != $t->getDBkey()) {
    			$uniques[] = $t;
    			$newTitle = $t;
    		}
    	}
    	return $uniques;
    }
    
    
}

class TitleHelper {
    public static function string2Title(array & $titlenames) {
        $results = array();
        foreach($titlenames as $t) {
            $results[] = Title::newFromText($t);
        }
        return $results;
    }
    
    public static function title2string(array & $titlenames) {
    	$results = array();
        foreach($titlenames as $t) {
            $results[] = $t->getPrefixedText();
        }
        return $results;
    }
}
?>
