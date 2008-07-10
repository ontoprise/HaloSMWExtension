<?php
/*
 * Created on 13.03.2007
 *
 * Author: KK
 */
 
 global $smwgHaloIP;
 require_once("GraphCycleDetector.php");
 require_once("PropertyCoVarianceDetector.php");
 require_once("AnnotationLevelConsistency.php");
 require_once("InverseEqualityConsistency.php");
 require_once( $smwgHaloIP . '/specials/SMWGardening/SMW_GardeningBot.php');
 require_once( $smwgHaloIP . '/specials/SMWGardening/SMW_GardeningIssues.php');
 require_once("$smwgHaloIP/specials/SMWGardening/SMW_ParameterObjects.php");
 

 
 class ConsistencyBot extends GardeningBot {
 	
 	 	 	
 	function ConsistencyBot() {
 		parent::GardeningBot("smw_consistencybot");
 		$this->store = ConsitencyBotStorage::getConsistencyStorage();
 	}
 	
 	public function getHelpText() {
 		return wfMsg('smw_gard_consistency_docu');
 	}
 	
 	public function getLabel() {
 		return wfMsg($this->id);
 	}
 	
 	public function allowedForUserGroups() {
 		return array(SMW_GARD_GARDENERS, SMW_GARD_SYSOPS);
 	}
 	
 	/**
 	 * Returns an array mapping parameter IDs to parameter objects
 	 */
 	public function createParameters() {
 		//$param1 = new GardeningParamBoolean('CONSISTENCY_BOT_REPLACE_REDIRECTS', wfMsg('smw_gard_param_replaceredirects'), SMW_GARD_PARAM_OPTIONAL, false );
 		return array();
 	}
 	
 	/**
 	 * Do consistency checks and return a log as wiki markup.
 	 * Do not use echo when it is not running asynchronously.
 	 */
 	public function run($paramArray, $isAsync, $delay) {
 		
 		if (!$isAsync) {
 			echo 'ConsistencyChecks should not be run synchronously! Abort bot.';
 			return;
 		}
 		echo $this->getBotID()." started!\n";
 				
 		$this->setNumberOfTasks(9); // 8 single tasks
 		
 		// get inheritance graphs
 		$categoryGraph = $this->store->getCategoryInheritanceGraph();
 		$propertyGraph = $this->store->getPropertyInheritanceGraph();
 		
 		// Replace redirect annotations
 		//if (array_key_exists('CONSISTENCY_BOT_REPLACE_REDIRECTS', $paramArray)) {
 		smwfGetSemanticStore()->replaceRedirectAnnotations(true);
 		//}
 		
 		// Schema level checks
 		// first, check if there are cycles in the inheritance graphs
 		echo "Checking for cycles in inheritance graphs...";
 		$this->checkInheritanceCycles($categoryGraph, $propertyGraph);
 		echo "done!\n\n";
 		
		
		echo "Checking property co-variance...";
        $this->checkPropertyCovariance($delay, $categoryGraph, $propertyGraph);
        echo "done!\n\n";
                    
 		echo "Checking inverse and equality relations...";
 		$this->checkInverseEqualityRelations($delay);
 		echo "done!\n\n";
 		
 		// Annotation level checks
 		echo "Checking annotation level...";
        $this->checkAnnotationLevel($delay, $categoryGraph, $propertyGraph);
 	    echo "done!\n\n";
 	    
 	    // propagate issues
 	    echo "Propagating issues...";
 		SMWGardeningIssuesAccess::getGardeningIssuesAccess()->generatePropagationIssuesForCategories($this->id, SMW_GARDISSUE_CONSISTENCY_PROPAGATION);
 		echo "done!\n\n";
         
 		return NULL;
 		
 	}
 	
 	private function checkInheritanceCycles(& $categoryGraph, & $propertyGraph) {
 		
 		$gcd = new GraphCycleDetector($this);
 		$gcd->getAllCategoryCycles($categoryGraph);
 		$gcd->getAllPropertyCycles($propertyGraph);
 			
 	}
 	
 	 	
 	private function checkPropertyCovariance($delay, & $categoryGraph, & $propertyGraph) {
 		
 		$pcd = new PropertyCoVarianceDetector($this, $delay, $categoryGraph, $propertyGraph, true);
 		$pcd->checkPropertyGraphForCovariance();
 		
 		
 	}
 	
 	private function checkAnnotationLevel($delay, & $categoryGraph, & $propertyGraph) {
 		
 		$alc = new AnnotationLevelConsistency($this, $delay, $categoryGraph, $propertyGraph, true);
 		$alc->checkAllPropertyAnnotations();
 		$alc->checkAllAnnotationCardinalities();
 		$alc->checkAllUnits();
 		
 	}
 	
 	
 	
 	private function checkInverseEqualityRelations($delay) {
 		
 		$ier = new InverseEqualityConsistency($this, $delay);
 		$cir = $ier->checkInverseRelations();
 		$cer = $ier->checkEqualToRelations();
 		
 	}
 	
 	
 	 	
 }
 
 
  // instantiate it once.
 new ConsistencyBot();
 
 define('SMW_CONSISTENCY_BOT_BASE', 100);
 // covariance issues
 define('SMW_GARDISSUE_DOMAINS_NOT_COVARIANT', SMW_CONSISTENCY_BOT_BASE * 100 + 1);
 define('SMW_GARDISSUE_RANGES_NOT_COVARIANT', SMW_CONSISTENCY_BOT_BASE * 100 + 2);
 define('SMW_GARDISSUE_TYPES_NOT_COVARIANT', SMW_CONSISTENCY_BOT_BASE * 100 + 3);
 define('SMW_GARDISSUE_MINCARD_NOT_COVARIANT', SMW_CONSISTENCY_BOT_BASE * 100 + 4);
 define('SMW_GARDISSUE_MAXCARD_NOT_COVARIANT', SMW_CONSISTENCY_BOT_BASE * 100 + 5);
 define('SMW_GARDISSUE_SYMETRY_NOT_COVARIANT1', SMW_CONSISTENCY_BOT_BASE * 100 + 6);
 define('SMW_GARDISSUE_TRANSITIVITY_NOT_COVARIANT1', SMW_CONSISTENCY_BOT_BASE * 100 + 7);
 define('SMW_GARDISSUE_SYMETRY_NOT_COVARIANT2', SMW_CONSISTENCY_BOT_BASE * 100 + 8);
 define('SMW_GARDISSUE_TRANSITIVITY_NOT_COVARIANT2', SMW_CONSISTENCY_BOT_BASE * 100 + 9);
 // ...
 // not defined issues
 define('SMW_GARDISSUE_DOMAINS_NOT_DEFINED', (SMW_CONSISTENCY_BOT_BASE+1) * 100 + 1);
 define('SMW_GARDISSUE_DOMAINS_AND_RANGES_NOT_DEFINED', (SMW_CONSISTENCY_BOT_BASE+1) * 100 + 2);
 define('SMW_GARDISSUE_RANGES_NOT_DEFINED', (SMW_CONSISTENCY_BOT_BASE+1) * 100 + 4);
 define('SMW_GARDISSUE_TYPES_NOT_DEFINED', (SMW_CONSISTENCY_BOT_BASE+1) * 100 + 5);
 
 
 // doubles issues
 define('SMW_GARDISSUE_DOUBLE_TYPE', (SMW_CONSISTENCY_BOT_BASE+2) * 100 + 1);
 define('SMW_GARDISSUE_DOUBLE_MAX_CARD', (SMW_CONSISTENCY_BOT_BASE+2) * 100 + 2);
 define('SMW_GARDISSUE_DOUBLE_MIN_CARD', (SMW_CONSISTENCY_BOT_BASE+2) * 100 + 3);

 
 // wrong/missing values / entity issues
 define('SMW_GARDISSUE_MAXCARD_NOT_NULL', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 1);
 define('SMW_GARDISSUE_MINCARD_BELOW_NULL', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 2);
 define('SMW_GARDISSUE_WRONG_MINCARD_VALUE', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 3);
 define('SMW_GARDISSUE_WRONG_MAXCARD_VALUE', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 4);
 define('SMW_GARDISSUE_WRONG_TARGET_VALUE', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 5);
 define('SMW_GARDISSUE_WRONG_DOMAIN_VALUE', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 6);
 define('SMW_GARDISSUE_TOO_LOW_CARD', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 7);
 define('SMW_GARDISSUE_TOO_HIGH_CARD', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 8);
 define('SMW_GARDISSUE_WRONG_UNIT', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 9);
 define('SMW_GARD_ISSUE_MISSING_PARAM', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 10);
 define('SMW_GARDISSUE_MISSING_ANNOTATIONS', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 11);
 
 // incompatible entity issues
 define('SMW_GARD_ISSUE_DOMAIN_NOT_RANGE', (SMW_CONSISTENCY_BOT_BASE+4) * 100 + 1);
 define('SMW_GARD_ISSUE_INCOMPATIBLE_ENTITY', (SMW_CONSISTENCY_BOT_BASE+4) * 100 + 2);
 define('SMW_GARD_ISSUE_INCOMPATIBLE_TYPE', (SMW_CONSISTENCY_BOT_BASE+4) * 100 + 3);
 define('SMW_GARD_ISSUE_INCOMPATIBLE_SUPERTYPES', (SMW_CONSISTENCY_BOT_BASE+4) * 100 + 4 );
 
 // others
define('SMW_GARD_ISSUE_CYCLE', (SMW_CONSISTENCY_BOT_BASE+5) * 100 + 1);

// issues with type > 100000 are not displayed textually in GardeningLog
define('SMW_GARDISSUE_CONSISTENCY_PROPAGATION', 1000 * 100 + 1);

 class ConsistencyBotIssue extends GardeningIssue {
 	
 	public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified) {
 		parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified);
 	}
 	
 	protected function getTextualRepresenation(& $skin, $text1, $text2, $local = false) {
 		$text1 = $local ? wfMsg('smw_gard_issue_local') : $text1;
 		// show title2 as link if $skin is defined
 		$text2 = $skin != NULL ? $skin->makeLinkObj($this->t2) : $text2;
 		switch($this->gi_type) {
			case SMW_GARDISSUE_DOMAINS_NOT_COVARIANT: 
				return wfMsg('smw_gardissue_domains_not_covariant', $text1, $text2);
			case SMW_GARDISSUE_RANGES_NOT_COVARIANT: 
				return wfMsg('smw_gardissue_ranges_not_covariant', $text1, $text2);
			case SMW_GARDISSUE_TYPES_NOT_COVARIANT: 
				return wfMsg('smw_gardissue_types_not_covariant', $text1);
			case SMW_GARDISSUE_MINCARD_NOT_COVARIANT: 
				return wfMsg('smw_gardissue_mincard_not_covariant', $text1);
			case SMW_GARDISSUE_MAXCARD_NOT_COVARIANT: 
				return wfMsg('smw_gardissue_maxcard_not_covariant', $text1);
			case SMW_GARDISSUE_SYMETRY_NOT_COVARIANT1: 
				return wfMsg('smw_gardissue_symetry_not_covariant1', $text1);
			case SMW_GARDISSUE_TRANSITIVITY_NOT_COVARIANT1: 
				return wfMsg('smw_gardissue_transitivity_not_covariant1', $text1);
			case SMW_GARDISSUE_SYMETRY_NOT_COVARIANT2: 
				return wfMsg('smw_gardissue_symetry_not_covariant2', $text1);
			case SMW_GARDISSUE_TRANSITIVITY_NOT_COVARIANT2: 
				return wfMsg('smw_gardissue_transitivity_not_covariant2', $text1);
				
			case SMW_GARDISSUE_DOMAINS_NOT_DEFINED: 
				return wfMsg('smw_gardissue_domains_not_defined', $text1);
			case SMW_GARDISSUE_RANGES_NOT_DEFINED: 
				return wfMsg('smw_gardissue_ranges_not_defined', $text1);
			case SMW_GARDISSUE_DOMAINS_AND_RANGES_NOT_DEFINED: 
				return wfMsg('smw_gardissue_domains_and_ranges_not_defined', $text1);
			case SMW_GARDISSUE_TYPES_NOT_DEFINED: 
				return wfMsg('smw_gardissue_types_not_defined', $text1);
			
						
			case SMW_GARDISSUE_DOUBLE_TYPE: 
				return wfMsg('smw_gardissue_double_type', $text1, $this->value);
			case SMW_GARDISSUE_DOUBLE_MAX_CARD: 
				return wfMsg('smw_gardissue_double_max_card', $text1, $this->value);
			case SMW_GARDISSUE_DOUBLE_MIN_CARD: 
				return wfMsg('smw_gardissue_double_min_card', $text1, $this->value);
			case SMW_GARD_ISSUE_MISSING_PARAM: 
				return wfMsg('smw_gard_issue_missing_param',$text1, $text2, $this->value);
			case SMW_GARDISSUE_INSTANCE_WITHOUT_CAT: 
				return wfMsg('smw_gardissue_instance_without_cat', $text1);
				
			case SMW_GARDISSUE_MAXCARD_NOT_NULL: 
				return wfMsg('smw_gardissue_maxcard_not_null', $text1);
			case SMW_GARDISSUE_MINCARD_BELOW_NULL: 
				return wfMsg('smw_gardissue_mincard_below_null', $text1);
			case SMW_GARDISSUE_WRONG_MINCARD_VALUE: 
				return wfMsg('smw_gardissue_wrong_mincard_value', $text1);
			case SMW_GARDISSUE_WRONG_MAXCARD_VALUE: 
				return wfMsg('smw_gardissue_wrong_maxcard_value', $text1);
			case SMW_GARDISSUE_WRONG_TARGET_VALUE: 
				return wfMsg('smw_gardissue_wrong_target_value', $text1, $text2,  $skin != NULL ? $this->explodeTitlesToLinkObjs($skin, $this->value) : $this->value);
			case SMW_GARDISSUE_WRONG_DOMAIN_VALUE: 
				return wfMsg('smw_gardissue_wrong_domain_value', $text1, $text2);
			case SMW_GARDISSUE_TOO_LOW_CARD: 
				return wfMsg('smw_gardissue_too_low_card', $text1, $text2, $this->value);
			case SMW_GARDISSUE_MISSING_ANNOTATIONS:
				return wfMsg('smw_gardissue_missing_annotations', $text1, $text2, $this->value);
			case SMW_GARDISSUE_TOO_HIGH_CARD: 
				return wfMsg('smw_gardissue_too_high_card', $text1, $text2, $this->value);
			case SMW_GARDISSUE_WRONG_UNIT: 
				return wfMsg('smw_gardissue_wrong_unit', $text1, $text2, $this->value);
				
			case SMW_GARD_ISSUE_DOMAIN_NOT_RANGE: 
				return wfMsg('smw_gard_issue_domain_not_range', $text1, $text2);
			case SMW_GARD_ISSUE_INCOMPATIBLE_ENTITY: 
				return wfMsg('smw_gard_issue_incompatible_entity', $text1, $text2);
			case SMW_GARD_ISSUE_INCOMPATIBLE_TYPE: 
				return wfMsg('smw_gard_issue_incompatible_type',$text1, $text2);
			case SMW_GARD_ISSUE_INCOMPATIBLE_SUPERTYPES:
				return wfMsg('smw_gard_issue_incompatible_supertypes',$text1, $this->value);	
			case SMW_GARD_ISSUE_CYCLE:
				return wfMsg('smw_gard_issue_cycle',  $skin != NULL ? $this->explodeTitlesToLinkObjs($skin, $this->value) : $this->value);
			case SMW_GARDISSUE_CONSISTENCY_PROPAGATION:
				return wfMsg('smw_gard_issue_contains_further_problems');
				
			default: return "Unknown issue!"; // should not happen	
		}
	}
 }
 
 class ConsistencyBotFilter extends GardeningIssueFilter {
 	 	
 	
 	public function __construct() {
 		parent::__construct(SMW_CONSISTENCY_BOT_BASE);
 		$this->gi_issue_classes = array(wfMsg('smw_gardissue_class_all'),
 				 wfMsg('smw_gardissue_class_covariance'),
 				 wfMsg('smw_gardissue_class_undefined'),
 				 wfMsg('smw_gardissue_class_missdouble'),
 				 wfMsg('smw_gardissue_class_wrongvalue'),
 				 wfMsg('smw_gardissue_class_incomp'),
 				 wfMsg('smw_gardissue_class_cycles'));
 	}
 	
 	public function getUserFilterControls($specialAttPage, $request) {
 		$matchString = $request != NULL && $request->getVal('matchString') != NULL ? $request->getVal('matchString') : "";
		return ' Contains:<input name="matchString" type="text" class="wickEnabled" value="'.$matchString.'"/>';
	}
	
	public function linkUserParameters(& $wgRequest) {
		return array('matchString' => $wgRequest->getVal('matchString'), 'pageTitle' => $wgRequest->getVal('pageTitle'));
	}
	
	public function getData($options, $request) {
		$matchString = $request->getVal('matchString');
		$pageTitle = $request->getVal('pageTitle');
		
		if ($pageTitle != NULL) {
			// show only issue of *ONE* title
			return $this->getGardeningIssueContainerForTitle($options, $request, Title::newFromText(urldecode($pageTitle)));
		}
		if ($matchString != NULL && $matchString != '') {
			// show all issues of title which match
			$options->addStringCondition($matchString, SMW_STRCOND_MID);
			return $this->getGardeningIssueContainer($options, $request);
		} else {
			// default
			return $this->getGardeningIssueContainer($options, $request);
		}
	}
	
	private function getGardeningIssueContainer($options, $request) {
				
		$gi_class = $request->getVal('class') == 0 ? NULL : $request->getVal('class') + $this->base - 1;
		
		
		$gi_store = SMWGardeningIssuesAccess::getGardeningIssuesAccess();
		
		$gic = array();
		
		// get issues of the given class. If no class is specified, ignore propagation issues.
		$titles = $gi_store->getDistinctTitles('smw_consistencybot', NULL, $gi_class != NULL ? $gi_class : -GardeningIssue::getClass(SMW_GARDISSUE_CONSISTENCY_PROPAGATION), SMW_GARDENINGLOG_SORTFORTITLE, $options);
		foreach($titles as $t) {
			$gis = $gi_store->getGardeningIssues('smw_consistencybot', NULL, $gi_class, $t, SMW_GARDENINGLOG_SORTFORTITLE, NULL);
			$gic[] = new GardeningIssueContainer($t, $gis);
		}
		
		return $gic;
	}
	
	/**
	 * Returns array of ONE GardeningIssueContainer for a specific title 
	 */
	private function getGardeningIssueContainerForTitle($options, $request, $title) {
		$gi_class = $request->getVal('class') == 0 ? NULL : $request->getVal('class') + $this->base - 1;
		
		
		$gi_store = SMWGardeningIssuesAccess::getGardeningIssuesAccess();
		
		$gic = array();
		$gis = $gi_store->getGardeningIssues('smw_consistencybot', NULL, $gi_class, $title, SMW_GARDENINGLOG_SORTFORTITLE, NULL);
		$gic[] = new GardeningIssueContainer($title, $gis);
		
		
		return $gic;
	}
 }
 
 abstract class ConsitencyBotStorage {
 	
 	private static $store = NULL;
 	/* 
 	 * Note: 
 	 * 		
 	 *   Most of the following methods require a reference to a complete inheritance graph in memory. 
	 *	 They are intended to be used thousands of times in a row, since it is a complex
	 *	 task to load and prepare a complete inheritance graph for pathfinding at maximum speed. 
	 *	 So if you just need for instance a domain of _one_ super property, do this manually. 
	 * 
	 */
	 	
 	
 	/**
 	 * Returns the domain and ranges of the first super property which has defined some.
 	 * 
 	 * @param & $inheritance graph Reference to array of GraphEdge objects.
 	 * @param $a Property
 	 */ 	
 	public abstract function getDomainsAndRangesOfSuperProperty(& $inheritanceGraph, $p);
 	
 	/**
 	 * Determines minimum cardinality of an attribute,
 	 * which may be inherited.
 	 * 
 	 * @param & $inheritance graph Reference to array of GraphEdge objects.
 	 * @param $a Property
 	 */
 	public abstract function getMinCardinalityOfSuperProperty(& $inheritanceGraph, $a);
 	
 	/**
 	 * Determines minimum cardinality of an attribute,
 	 * which may be inherited.
 	 * 
 	 * @param & $inheritance graph Reference to array of GraphEdge objects.
 	 * @param $a Property
 	 */
 	public abstract function getMaxCardinalityOfSuperProperty(& $inheritanceGraph, $a);
 	
 	/**
 	 * Returns type of superproperty
 	 * 
 	 * @param & $inheritance graph Reference to array of GraphEdge objects.
 	 * @param $a Property
 	 */
 	public abstract function getTypeOfSuperProperty(& $inheritanceGraph, $a);
 	
 	/**
 	 * Returns categories of super property
 	 * 
 	 * @param & $inheritance graph Reference to array of GraphEdge objects.
 	 * @param $a Property
 	 */
 	public abstract function getCategoriesOfSuperProperty(& $inheritanceGraph, $a);
 	
 	/**
 	 * Returns a sorted array of (category,supercategory) page_id tuples
 	 * representing an category inheritance graph. 
 	 * 
 	 * @return array of GraphEdge objects;
 	 */
 	public abstract function getCategoryInheritanceGraph();
 	
 	/**
 	 * Returns a sorted array of (attribute,superattribute) page_id tuples
 	 * representing an attribute inheritance graph. 
 	 * 
 	 *  @return array of GraphEdge objects;
 	 */
 	public abstract function getPropertyInheritanceGraph();
 	
 	public abstract function getInverseRelations();
 	
 	public abstract function getEqualToRelations();
 	
 	/**
 	 * Returns number of property instantiations for each instance, which has 
 	 * at least one instantiation of $property or one of its subproperties.
 	 * 
 	 * @return array of tuples (Title instance, Integer frequency)
 	 */
 	public abstract function getNumberOfPropertyInstantiations($property);
 	
 	/**
 	 * Returns number of property instantiations of $property or one of its 
 	 * subproperties for the given instances.
 	 * 
 	 * @return array of tuples (Title instance, Integer frequency)
 	 */
 	public abstract function getMissingPropertyInstantiations($property, $instances);
 	
    public static function getConsistencyStorage() {
        global $smwgHaloIP;
        if (self::$store == NULL) {
            global $smwgDefaultStore;
            switch ($smwgDefaultStore) {
                case (SMW_STORE_TESTING):
                    self::$store = null; // not implemented yet
                    trigger_error('Testing store not implemented for HALO extension.');
                break;
                case (SMW_STORE_MWDB): default:
                    
                    self::$store = new ConsistencyBotStorageSQL();
                break;
            }
        }
        return self::$store;
    }
 }
 
 class ConsistencyBotStorageSQL extends ConsitencyBotStorage {
 	public function getDomainsAndRangesOfSuperProperty(& $inheritanceGraph, $p) {
 		$visitedNodes = array();
 		return $this->_getDomainsAndRangesOfSuperProperty($inheritanceGraph, $p, $visitedNodes);
 		
 	}
 	
 	private function _getDomainsAndRangesOfSuperProperty(& $inheritanceGraph, $p, & $visitedNodes) {
 		$results = array();
 		$propertyID = $p->getArticleID();
 		array_push($visitedNodes, $propertyID);
 		$superProperties = GraphHelper::searchInSortedGraph($inheritanceGraph, $propertyID);
 		if ($superProperties == null) return $results;
 		foreach($superProperties as $sp) {
 			$spTitle = Title::newFromID($sp->to);
 			$domainRangeCategories = smwfGetStore()->getPropertyValues($spTitle, smwfGetSemanticStore()->domainRangeHintRelation);
 			if (count($domainRangeCategories) > 0) {
 				return $domainRangeCategories;
 			} else {
 				if (!in_array($sp->to, $visitedNodes)) {
	 				$results = array_merge($results, $this->_getDomainsAndRangesOfSuperProperty($inheritanceGraph, $spTitle, $visitedNodes));
 				} 
 			}
 			
 		} 
 		array_pop($visitedNodes);
 		return $results;
 	}
 	
 
	public function getMinCardinalityOfSuperProperty(& $inheritanceGraph, $a) {
 		$visitedNodes = array();
 		$minCards = $this->_getMinCardinalityOfSuperProperty($inheritanceGraph, $a, $visitedNodes);
 		return max($minCards); // return highest min cardinality
 	}
 	
 	private function _getMinCardinalityOfSuperProperty(& $inheritanceGraph, $a, & $visitedNodes) {
 		$results = array(CARDINALITY_MIN);
 		$attributeID = $a->getArticleID();
 		array_push($visitedNodes, $attributeID);
 		$superAttributes = GraphHelper::searchInSortedGraph($inheritanceGraph, $attributeID);
 		if ($superAttributes == null) return $results;
 		foreach($superAttributes as $sa) {
 			$saTitle = Title::newFromID($sa->to);
 			$minCards = smwfGetStore()->getPropertyValues($saTitle, smwfGetSemanticStore()->minCard);
 			if (count($minCards) > 0) {
 				
 				return array($minCards[0]->getXSDValue() + 0);
 			} else {
 				if (!in_array($sa->to, $visitedNodes)) {
	 				$results = array_merge($results, $this->_getMinCardinalityOfSuperProperty($inheritanceGraph, $saTitle, $visitedNodes));
 				} 
 			}
 			
 		} 
		array_pop($visitedNodes);
 		return $results;
 	}
 	
 	
 	public function getMaxCardinalityOfSuperProperty(& $inheritanceGraph, $a) {
 		$visitedNodes = array();
 		$maxCards = $this->_getMaxCardinalityOfSuperProperty($inheritanceGraph, $a, $visitedNodes);
 		return min($maxCards); // return smallest max cardinality
 	}
 	
 	private function _getMaxCardinalityOfSuperProperty(& $inheritanceGraph, $a, & $visitedNodes) {
 		$results = array(CARDINALITY_UNLIMITED);
 		$attributeID = $a->getArticleID();
 		array_push($visitedNodes, $attributeID);
 		$superAttributes = GraphHelper::searchInSortedGraph($inheritanceGraph, $attributeID);
 		if ($superAttributes == null) return $results;
 		foreach($superAttributes as $sa) {
 			$saTitle = Title::newFromID($sa->to);
 			$maxCards = smwfGetStore()->getPropertyValues($saTitle, smwfGetSemanticStore()->maxCard);
 			if (count($maxCards) > 0) {
 				
 				return array($maxCards[0]->getXSDValue() + 0);
 			} else {
 				if (!in_array($sa->to, $visitedNodes)) {
	 				$results = array_merge($results, $this->_getMaxCardinalityOfSuperProperty($inheritanceGraph, $saTitle, $visitedNodes));
 				} 
 			}
 			
 		}
 		array_pop($visitedNodes);
 		return $results;
 	}
 	
 	
	
	public function getTypeOfSuperProperty(& $inheritanceGraph, $a) {
 		$visitedNodes = array();
 		return $this->_getTypeOfSuperProperty($inheritanceGraph, $a, $visitedNodes);
 		
 	}
 	
 	private function _getTypeOfSuperProperty(& $inheritanceGraph, $a, & $visitedNodes) {
 		$results = array();
 		$attributeID = $a->getArticleID();
 		array_push($visitedNodes, $attributeID);
 		$superAttributes = GraphHelper::searchInSortedGraph($inheritanceGraph, $attributeID);
 		if ($superAttributes == null) return $results;
 		foreach($superAttributes as $sa) {
 			$saTitle = Title::newFromID($sa->to);
 			$types = smwfGetStore()->getSpecialValues($saTitle, SMW_SP_HAS_TYPE);
 			if (count($types) > 0) {
 				return $types;
 			} else {
 				if (!in_array($sa->to, $visitedNodes)) {
	 				$results = array_merge($results, $this->_getTypeOfSuperProperty($inheritanceGraph, $saTitle, $visitedNodes));
 				} 
 			}
 			
 		}
 		array_pop($visitedNodes);
 		return $results;
 	}
 	
 	
 	public function getCategoriesOfSuperProperty(& $inheritanceGraph, $a) {
 		$visitedNodes = array();
 		return $this->_getCategoriesOfSuperProperty($inheritanceGraph, $a, $visitedNodes);
 	}
 	
 	private function _getCategoriesOfSuperProperty(& $inheritanceGraph, $a, & $visitedNodes) {
 		$results = array();
 		$attributeID = $a->getArticleID();
 		array_push($visitedNodes, $attributeID);
 		$superAttributes = GraphHelper::searchInSortedGraph($inheritanceGraph, $attributeID);
 		if ($superAttributes == null) return $results;
 		foreach($superAttributes as $sa) {
 			$saTitle = Title::newFromID($sa->to);
 			$categories = smwfGetSemanticStore()->getCategoriesForInstance($saTitle);
 			if (count($categories) > 0) {
 				return $categories;
 			} else {
 				if (!in_array($sa->to, $visitedNodes)) {
	 				$results = array_merge($results, $this->_getCategoriesOfSuperProperty($inheritanceGraph, $saTitle, $visitedNodes));
 				} 
 			}
 			
 		} 
 		array_pop($visitedNodes);
 		return $results;
 	}
 	
 	
 	public function getCategoryInheritanceGraph() {
 		$result = "";
		$db =& wfGetDB( DB_SLAVE );
		$sql = 'page_namespace=' . NS_CATEGORY .
			   ' AND cl_to = page_title';
		$sql_options = array();
		$sql_options['ORDER BY'] = 'cl_from';
		$res = $db->select(  array($db->tableName('page'), $db->tableName('categorylinks')), 
		                    array('cl_from','page_id', 'page_title'),
		                    $sql, 'SMW::getCategoryInheritanceGraph', $sql_options);
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = new GraphEdge($row->cl_from, $row->page_id);
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	
 	public function getPropertyInheritanceGraph() {
 		global $smwgContLang;
  		$namespaces = $smwgContLang->getNamespaces();
 		$result = "";
		$db =& wfGetDB( DB_SLAVE );
		$smw_subprops = $db->tableName('smw_subprops');
		 $res = $db->query('SELECT p1.page_id AS sub, p2.page_id AS sup FROM '.$smw_subprops.', page p1, page p2 WHERE p1.page_namespace = '.SMW_NS_PROPERTY.
							' AND p2.page_namespace = '.SMW_NS_PROPERTY.' AND p1.page_title = subject_title AND p2.page_title = object_title ORDER BY p1.page_id');
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = new GraphEdge($row->sub, $row->sup);
			}
		}
		$db->freeResult($res);
		return $result;
 	}
 	
 	public function getInverseRelations($requestoptions = NULL) {
 		$db =& wfGetDB( DB_SLAVE );
		$sql = 'relation_title = '.$db->addQuotes(smwfGetSemanticStore()->inverseOf->getDBkey()); 
		
		$res = $db->select(  array($db->tableName('smw_relations')), 
		                    array('subject_title', 'object_title'),
		                    $sql, 'SMW::getInverseRelations', DBHelper::getSQLOptions($requestoptions));
		                    
		
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = array(Title::newFromText($row->subject_title, SMW_NS_PROPERTY),  Title::newFromText($row->object_title, SMW_NS_PROPERTY));
			}
		}
		
		$db->freeResult($res);
		
		return $result;
 	}
 	
 	public function getEqualToRelations($requestoptions = NULL) {
 		//TODO: read partitions of redirects
 		$db =& wfGetDB( DB_SLAVE );
		$sql = 'rd_from = page_id'; 
		
		$res = $db->select(  array($db->tableName('redirect'), $db->tableName('page')), 
		                    array('rd_namespace','rd_title', 'page_namespace', 'page_title'),
		                    $sql, 'SMW::getEqualToRelations', DBHelper::getSQLOptions($requestoptions));
		                    
		
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$result[] = array(Title::newFromText($row->rd_title, $row->rd_namespace), Title::newFromText($row->page_title, $row->page_namespace));
			}
		}
		
		$db->freeResult($res);
		
		return $result;
 	}
 	
 	public function getNumberOfPropertyInstantiations($property) {
 		
		global $smwgDefaultCollation;
		$db =& wfGetDB( DB_SLAVE );
		$smw_attributes = $db->tableName('smw_attributes');
		$smw_relations = $db->tableName('smw_relations');
		$smw_nary = $db->tableName('smw_nary');
		$smw_subprops = $db->tableName('smw_subprops');
	
		if (!isset($smwgDefaultCollation)) {
			$collation = '';
		} else {
			$collation = 'COLLATE '.$smwgDefaultCollation;
		}
		// create virtual tables
		$db->query( 'CREATE TEMPORARY TABLE smw_cc_propertyinst (instance VARCHAR(255) '.$collation.', namespace INTEGER, property VARCHAR(255) '.$collation.', num INTEGER(8))
		            TYPE=MEMORY', 'SMW::getNumberOfPropertyInstantiations' );
		
		$db->query( 'CREATE TEMPORARY TABLE smw_cc_properties_sub (property VARCHAR(255) '.$collation.' NOT NULL)
		            TYPE=MEMORY', 'SMW::getNumberOfPropertyInstantiations' );
		$db->query( 'CREATE TEMPORARY TABLE smw_cc_properties_super (property VARCHAR(255) '.$collation.' NOT NULL)
		            TYPE=MEMORY', 'SMW::getNumberOfPropertyInstantiations' );
		
		// initialize with direct property instantiations
				           
		$db->query('INSERT INTO smw_cc_propertyinst ' .
				'(SELECT subject_title AS instance, subject_namespace AS namespace, attribute_title AS property, COUNT(subject_title) AS num FROM '.$smw_attributes.' WHERE attribute_title = '.$db->addQuotes($property->getDBkey()).' GROUP BY instance) ');
		$db->query('INSERT INTO smw_cc_propertyinst ' .
				'(SELECT subject_title AS instance, subject_namespace AS namespace, relation_title AS property, COUNT(subject_title) AS num FROM '.$smw_relations.' WHERE relation_title = '.$db->addQuotes($property->getDBkey()).' GROUP BY instance) ');
		$db->query('INSERT INTO smw_cc_propertyinst ' .
				'(SELECT subject_title AS instance, subject_namespace AS namespace, attribute_title AS property, COUNT(subject_title) AS num FROM '.$smw_nary.' WHERE attribute_title = '.$db->addQuotes($property->getDBkey()).' GROUP BY instance)');
						
	
		$db->query('INSERT INTO smw_cc_properties_super VALUES ('.$db->addQuotes($property->getDBkey()).')');
		
		$maxDepth = SMW_MAX_CATEGORY_GRAPH_DEPTH;
		// maximum iteration length is maximum property tree depth.
		do  {
			$maxDepth--;
			
			// get next subproperty level
			$db->query('INSERT INTO smw_cc_properties_sub (SELECT DISTINCT subject_title AS property FROM '.$smw_subprops.' WHERE object_title IN (SELECT * FROM smw_cc_properties_super) AND subject_title NOT IN (SELECT property FROM smw_cc_propertyinst))');
			
			// insert number of instantiated properties of current property level level
			$db->query('INSERT INTO smw_cc_propertyinst ' .
				'(SELECT subject_title AS instance, subject_namespace AS namespace, attribute_title AS property, COUNT(subject_title) AS num FROM '.$smw_attributes.' WHERE attribute_title IN (SELECT * FROM smw_cc_properties_sub) GROUP BY instance) ');
			$db->query('INSERT INTO smw_cc_propertyinst ' .
					'(SELECT subject_title AS instance, subject_namespace AS namespace, relation_title AS property, COUNT(subject_title) AS num FROM '.$smw_relations.' WHERE relation_title IN (SELECT * FROM smw_cc_properties_sub) GROUP BY instance)');
			$db->query('INSERT INTO smw_cc_propertyinst ' .
					'(SELECT subject_title AS instance, subject_namespace AS namespace, attribute_title AS property, COUNT(subject_title) AS num FROM '.$smw_nary.' WHERE attribute_title IN (SELECT * FROM smw_cc_properties_sub) GROUP BY instance) ');
				
			
			// copy subcatgegories to supercategories of next iteration
			$db->query('DELETE FROM smw_cc_properties_super');
			$db->query('INSERT INTO smw_cc_properties_super (SELECT * FROM smw_cc_properties_sub)');
			
			// check if there was least one more subcategory. If not, all instances were found.
			$res = $db->query('SELECT COUNT(property) AS numOfSubProps FROM smw_cc_properties_super');
			$numOfSubProps = $db->fetchObject($res)->numOfSubProps;
			$db->freeResult($res);
			
			$db->query('DELETE FROM smw_cc_properties_sub');
			
		} while ($numOfSubProps > 0 && $maxDepth > 0);
		
		$res = $db->query('SELECT instance, namespace, SUM(num) AS numOfInstProps FROM smw_cc_propertyinst GROUP BY instance');
		
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				
				$result[] = array(Title::newFromText($row->instance, $row->namespace), $row->numOfInstProps);
			}
		}
		
		$db->freeResult($res);
		
		$db->query('DROP TABLE smw_cc_properties_super');
		$db->query('DROP TABLE smw_cc_properties_sub');
		$db->query('DROP TABLE smw_cc_propertyinst');
		
		return $result;
 	}
 	
 	public function getMissingPropertyInstantiations($property, $instances) {
 		global $smwgDefaultCollation;
		$db =& wfGetDB( DB_SLAVE );
		$smw_attributes = $db->tableName('smw_attributes');
		$smw_relations = $db->tableName('smw_relations');
		$smw_nary = $db->tableName('smw_nary');
		$smw_subprops = $db->tableName('smw_subprops');
	
		if (!isset($smwgDefaultCollation)) {
			$collation = '';
		} else {
			$collation = 'COLLATE '.$smwgDefaultCollation;
		}
		// create virtual tables
		$db->query( 'CREATE TEMPORARY TABLE smw_cc_propertyinst (id INTEGER(8))
		            TYPE=MEMORY', 'SMW::getNumberOfPropertyInstantiations' );
		$db->query( 'CREATE TEMPORARY TABLE smw_cc_allinst (id INTEGER(8), namespace INTEGER, instance VARCHAR(255) '.$collation.')
		            TYPE=MEMORY', 'SMW::getNumberOfPropertyInstantiations' );
		            
		$db->query( 'CREATE TEMPORARY TABLE smw_cc_properties_sub (property VARCHAR(255) '.$collation.' NOT NULL)
		            TYPE=MEMORY', 'SMW::getNumberOfPropertyInstantiations' );
		$db->query( 'CREATE TEMPORARY TABLE smw_cc_properties_super (property VARCHAR(255) '.$collation.' NOT NULL)
		            TYPE=MEMORY', 'SMW::getNumberOfPropertyInstantiations' );
		
		$db->query('INSERT INTO smw_cc_properties_super VALUES ('.$db->addQuotes($property->getDBkey()).')');
		
		// initialize with direct property instantiations
		foreach($instances as $i) {
			if ($i == NULL) continue;
			// insert ID of instances
			list($instance, $category) = $i;
			$db->query('INSERT INTO smw_cc_allinst VALUES ('.$instance->getArticleID().', '.$instance->getNamespace().' , '.$db->addQuotes($instance->getDBkey()).')');
		}    
				
		$db->query('INSERT INTO smw_cc_propertyinst ' .
				'(SELECT subject_id AS id FROM '.$smw_attributes.' WHERE subject_id IN (SELECT id FROM smw_cc_allinst) AND attribute_title IN (SELECT * FROM smw_cc_properties_super) GROUP BY subject_id)');
		$db->query('INSERT INTO smw_cc_propertyinst ' .
				'(SELECT subject_id AS id FROM '.$smw_relations.' WHERE subject_id IN (SELECT id FROM smw_cc_allinst) AND relation_title IN (SELECT * FROM smw_cc_properties_super) GROUP BY subject_id) ');
		$db->query('INSERT INTO smw_cc_propertyinst ' .
				'(SELECT subject_id AS id FROM '.$smw_nary.' WHERE subject_id IN (SELECT id FROM smw_cc_allinst) AND attribute_title IN (SELECT * FROM smw_cc_properties_super) GROUP BY subject_id) ');
			
		
		$maxDepth = SMW_MAX_CATEGORY_GRAPH_DEPTH;
		// maximum iteration length is maximum property tree depth.
		do  {
			$maxDepth--;
			
			// get next subproperty level
			$db->query('INSERT INTO smw_cc_properties_sub (SELECT DISTINCT subject_title AS property FROM '.$smw_subprops.' WHERE object_title IN (SELECT * FROM smw_cc_properties_super)  AND subject_title NOT IN (SELECT property FROM smw_cc_propertyinst))');
			
			
			// insert number of instantiated properties of current property level level
			$db->query('INSERT INTO smw_cc_propertyinst ' .
				'(SELECT subject_id AS id FROM '.$smw_attributes.' WHERE subject_id IN (SELECT id FROM smw_cc_allinst) AND attribute_title IN (SELECT * FROM smw_cc_properties_sub) GROUP BY subject_id)');
			$db->query('INSERT INTO smw_cc_propertyinst ' .
				'(SELECT subject_id AS id FROM '.$smw_relations.' WHERE subject_id IN (SELECT id FROM smw_cc_allinst) AND relation_title IN (SELECT * FROM smw_cc_properties_sub) GROUP BY subject_id) ');
			$db->query('INSERT INTO smw_cc_propertyinst ' .
				'(SELECT subject_id AS id FROM '.$smw_nary.' WHERE subject_id IN (SELECT id FROM smw_cc_allinst) AND attribute_title IN (SELECT * FROM smw_cc_properties_sub) GROUP BY subject_id) ');
			
			
			// copy subcatgegories to supercategories of next iteration
			$db->query('DELETE FROM smw_cc_properties_super');
			$db->query('INSERT INTO smw_cc_properties_super (SELECT * FROM smw_cc_properties_sub)');
			
			// check if there was least one more subcategory. If not, all instances were found.
			$res = $db->query('SELECT COUNT(property) AS numOfSubProps FROM smw_cc_properties_super');
			$numOfSubProps = $db->fetchObject($res)->numOfSubProps;
			$db->freeResult($res);
			
			$db->query('DELETE FROM smw_cc_properties_sub');
			
		} while ($numOfSubProps > 0 && $maxDepth > 0);
		
		
		
		$res = $db->query('SELECT DISTINCT allinst1.instance, allinst1.namespace FROM smw_cc_allinst allinst1 LEFT JOIN smw_cc_propertyinst allinst2 ON allinst1.id = allinst2.id WHERE allinst2.id IS NULL');
		
		$result = array();
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				
				$result[] = Title::newFromText($row->instance, $row->namespace);
			}
		}
				
		$db->freeResult($res);
		
		$db->query('DROP TABLE smw_cc_properties_super');
		$db->query('DROP TABLE smw_cc_properties_sub');
		$db->query('DROP TABLE smw_cc_allinst');
		$db->query('DROP TABLE smw_cc_propertyinst');
		
		return $result;
 	}
 }
?>
