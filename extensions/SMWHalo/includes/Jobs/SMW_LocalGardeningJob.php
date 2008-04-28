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
    
	// page which was saved or removed
	public $title;
	
	// action which was done (save, remove)
	public $action;
	
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
        $this->title = $title;
        $this->action = $action;
    }
    
    /**
     * Selects pages which are relevant in any manner for $action on $title. 
     *
     * @return Array of Title
     */
    private function selectPages() {
    	switch($this->title->getNamespace()) {
    		case SMW_NS_PROPERTY: return PageSelector::getPagesForPropertySave($this->title); break;
    		case NS_CATEGORY: return PageSelector::getPagesForCategorySave($this->title); break;
    		case NS_MAIN: return $this->action == "save" ? $this->title : NULL; break;
    		case SMW_NS_TYPE: return $this->action == "save" ? $this->title : NULL; break;
    	}
    	return NULL;
    }
    
   
    private function checkInstancesUsingProperty(Title $property) {
    
    	$subjects = smwfGetStore()->getAllPropertySubjects($property);
      	$this->annot_checker->checkPropertyAnnotations($subjects, $property);
    }
    
    private function checkPropertyCovariance(Title $property) {
    	$this->cov_checker->checkPropertyForCovariance($property);
    }
    
    private function checkInstance(Title $instance) {
    	if ($instance == NULL) return;
    	$subjects[] = $instance;
        $properties = smwfGetStore()->getProperties($instance);
        
        foreach($properties as $property) {
            $this->annot_checker->checkPropertyAnnotations($subjects, $property);
        }
    }
    
    private function checkType(Title $type) {
    	if ($type == NULL) return;
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
         
        // select page which need to be processed
        $pagestocheck = $this->selectPages();
        
        switch($this->title->getNamespace()) {
        	case NS_CATEGORY: foreach($pagestocheck as $p) {
						            print "Checking annotations of '".$p->getText()."'...";
						            $this->checkInstancesUsingProperty($p);
						            print "done.\n";
						      } break;
        	case SMW_NS_PROPERTY: foreach($pagestocheck as $p) {
                                    print "Checking annotations and covariance of '".$p->getText()."'...";
                                    $this->checkInstancesUsingProperty($p);
                                    $this->checkPropertyCovariance($p);
                                    print "done.\n";
                              } break;	
        	case NS_MAIN:           print "Checking annotations of '".$this->title->getText()."'...";
                                    $this->checkInstance($this->title);
                                    print "done.\n";
                                break;
        	case SMW_NS_TYPE:       print "Checking annotations of '".$this->title->getText()."'...";
                                    $this->checkType($this->title);
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
		return PageSelector::makeTitlesUnique($properties);
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
		return PageSelector::makeTitlesUnique($allSubProperties);
	}
	
	// Note: may contain duplicates. cycle safe.
	static function getAllSuperCategories(Title $category, & $results, & $visitedNodes) {
	
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
	static function getAllSubProperties(Title $property, & $results, & $visitedNodes) {
        
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
     * Returns an array of Title which contains every title only once.
     *
     * @param array $titles
     * @return array of Title
     */
    static function makeTitlesUnique(array & $titles) {
    	$uniques = array();
    	for ($i = 0, $n = count($titles); $i < $n; $i++) {
	    	for ($j = 0, $n = count($titles); $j < $n-1; $j++) {
	            if (strcmp($titles[$j+1]->getDBkey(), $titles[$j]->getDBkey() < 0)) {
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
?>
