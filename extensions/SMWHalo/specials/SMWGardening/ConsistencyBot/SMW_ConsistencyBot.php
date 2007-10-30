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
 
 
 class ConsistencyBot extends GardeningBot {
 	
 	
 	
 	function ConsistencyBot() {
 		parent::GardeningBot("smw_consistencybot");
 		
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
 				
 		$this->setNumberOfTasks(7); // 7 single tasks
 		
 		// Schema level checks
 		// first, check if there are cycles in the inheritance graphs
 		echo "Checking for cycles in inheritance graphs...";
 		$this->checkInheritanceCycles();
 		echo "done!\n\n";
 		
		
		echo "Checking property co-variance...";
        $this->checkPropertyCovariance($delay);
        echo "done!\n\n";
                    
 		echo "Checking for consistency of inverse and equality relations...";
 		$this->checkInverseEqualityRelations();
 		echo "done!\n\n";
 		
 	
 		// Annotation level checks
 		echo "Checking annotation level...";
        $this->checkAnnotationLevel($delay);
 	    echo "done!\n\n";
         
 		return NULL;
 		
 	}
 	
 	private function checkInheritanceCycles() {
 		
 		$gcd = new GraphCycleDetector($this);
 		$gcd->getAllCategoryCycles("== ".wfMsg('smw_gard_errortype_categorygraph_contains_cycles')." ==\n\n");
 		$gcd->getAllPropertyCycles("== ".wfMsg('smw_gard_errortype_propertygraph_contains_cycles')." ==\n\n");
 			
 	}
 	
 	 	
 	private function checkPropertyCovariance($delay) {
 		
 		$pcd = new PropertyCoVarianceDetector($this, $delay);
 		$agc .= $pcd->checkPropertyGraphForCovariance();
 		
 		
 	}
 	
 	private function checkAnnotationLevel($delay) {
 		
 		$alc = new AnnotationLevelConsistency($this, $delay);
 		$aac .= $alc->checkPropertyAnnotations();
 		$acc .= $alc->checkAnnotationCardinalities();
 		
 		
 	}
 	
 	
 	
 	private function checkInverseEqualityRelations() {
 		
 		$ier = new InverseEqualityConsistency($this);
 		$cir = $ier->checkInverseRelations();
 		$cer = $ier->checkEqualToRelations();
 		
 	}
 	
 	
 }
 
 
  // instantiate it once.
 new ConsistencyBot();
 
 // covariance issues
 define('SMW_GARDISSUE_DOMAINS_NOT_COVARIANT', 101);
 define('SMW_GARDISSUE_RANGES_NOT_COVARIANT', 102);
 define('SMW_GARDISSUE_TYPES_NOT_COVARIANT', 103);
 define('SMW_GARDISSUE_MINCARD_NOT_COVARIANT', 104);
 define('SMW_GARDISSUE_MAXCARD_NOT_COVARIANT', 105);
 define('SMW_GARDISSUE_SYMETRY_NOT_COVARIANT1', 106);
 define('SMW_GARDISSUE_TRANSITIVITY_NOT_COVARIANT1', 107);
 define('SMW_GARDISSUE_SYMETRY_NOT_COVARIANT2', 108);
 define('SMW_GARDISSUE_TRANSITIVITY_NOT_COVARIANT2', 109);
 // ...
 // not defined issues
 define('SMW_GARDISSUE_DOMAINS_NOT_DEFINED', 201);
 define('SMW_GARDISSUE_RANGES_NOT_DEFINED', 202);
 define('SMW_GARDISSUE_TYPES_NOT_DEFINED', 203);
 
 
 // missing / doubles issues
 define('SMW_GARDISSUE_DOUBLE_TYPE', 301);
 define('SMW_GARDISSUE_DOUBLE_MAX_CARD', 302);
 define('SMW_GARDISSUE_DOUBLE_MIN_CARD', 303);
 define('SMW_GARD_ISSUE_MISSING_PARAM', 304);

 
 // wrong value / entity issues
 define('SMW_GARDISSUE_MAXCARD_NOT_NULL', 401);
 define('SMW_GARDISSUE_MINCARD_BELOW_NULL', 402);
 define('SMW_GARDISSUE_WRONG_CARD_VALUE', 403);
 define('SMW_GARDISSUE_WRONG_TARGET_VALUE', 404);
 define('SMW_GARDISSUE_WRONG_DOMAIN_VALUE', 405);
 define('SMW_GARDISSUE_WRONG_CARD', 406);
 
 // incompatible entity issues
 define('SMW_GARD_ISSUE_DOMAIN_NOT_RANGE', 501);
 define('SMW_GARD_ISSUE_INCOMPATIBLE_ENTITY', 502);
 define('SMW_GARD_ISSUE_INCOMPATIBLE_TYPE', 503);
 
 // others
define('SMW_GARD_ISSUE_CYCLE', 601);

 class ConsistencyBotIssue extends GardeningIssue {
 	
 	public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value) {
 		parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value);
 	}
 	
 	protected function getTextualRepresenation(& $skin) {
		switch($this->gi_type) {
			case SMW_GARDISSUE_DOMAINS_NOT_COVARIANT: 
				return wfMsg('smw_gardissue_domains_not_covariant', $skin->makeLinkObj($this->t1));
			case SMW_GARDISSUE_RANGES_NOT_COVARIANT: 
				return wfMsg('smw_gardissue_ranges_not_covariant', $skin->makeLinkObj($this->t1));
			case SMW_GARDISSUE_TYPES_NOT_COVARIANT: 
				return wfMsg('smw_gardissue_types_not_covariant', $skin->makeLinkObj($this->t1));
			case SMW_GARDISSUE_MINCARD_NOT_COVARIANT: 
				return wfMsg('smw_gardissue_mincard_not_covariant', $skin->makeLinkObj($this->t1));
			case SMW_GARDISSUE_MAXCARD_NOT_COVARIANT: 
				return wfMsg('smw_gardissue_maxcard_not_covariant', $skin->makeLinkObj($this->t1));
			case SMW_GARDISSUE_SYMETRY_NOT_COVARIANT1: 
				return wfMsg('smw_gardissue_symetry_not_covariant1', $skin->makeLinkObj($this->t1));
			case SMW_GARDISSUE_TRANSITIVITY_NOT_COVARIANT1: 
				return wfMsg('smw_gardissue_transitivity_not_covariant1', $skin->makeLinkObj($this->t1));
			case SMW_GARDISSUE_SYMETRY_NOT_COVARIANT2: 
				return wfMsg('smw_gardissue_symetry_not_covariant2', $skin->makeLinkObj($this->t1));
			case SMW_GARDISSUE_TRANSITIVITY_NOT_COVARIANT2: 
				return wfMsg('smw_gardissue_transitivity_not_covariant2', $skin->makeLinkObj($this->t1));
				
			case SMW_GARDISSUE_DOMAINS_NOT_DEFINED: 
				return wfMsg('smw_gardissue_domains_not_defined', $skin->makeLinkObj($this->t1));
			case SMW_GARDISSUE_RANGES_NOT_DEFINED: 
				return wfMsg('smw_gardissue_ranges_not_defined', $skin->makeLinkObj($this->t1));
			case SMW_GARDISSUE_TYPES_NOT_DEFINED: 
				return wfMsg('smw_gardissue_types_not_defined', $skin->makeLinkObj($this->t1));
			case SMW_GARDISSUE_CATEGORY_NOT_DEFINED: 
				return wfMsg('smw_gardissue_category_not_defined', $skin->makeLinkObj($this->t1));
			case SMW_GARDISSUE_PROPERTY_NOT_DEFINED: 
				return wfMsg('smw_gardissue_property_not_defined', $skin->makeLinkObj($this->t1));
			case SMW_GARDISSUE_TARGET_NOT_DEFINED: 
				return wfMsg('smw_gardissue_target_not_defined', $skin->makeLinkObj($this->t1));
			
			case SMW_GARDISSUE_DOUBLE_TYPE: 
				return wfMsg('smw_gardissue_double_type', $skin->makeLinkObj($this->t1), $this->value);
			case SMW_GARDISSUE_DOUBLE_MAX_CARD: 
				return wfMsg('smw_gardissue_double_max_card', $skin->makeLinkObj($this->t1), $this->value);
			case SMW_GARDISSUE_DOUBLE_MIN_CARD: 
				return wfMsg('smw_gardissue_double_min_card', $skin->makeLinkObj($this->t1), $this->value);
			case SMW_GARD_ISSUE_MISSING_PARAM: 
				return wfMsg('smw_gard_issue_missing_param',$skin->makeLinkObj($this->t1), $skin->makeLinkObj($this->t2), $this->value);
			case SMW_GARDISSUE_INSTANCE_WITHOUT_CAT: 
				return wfMsg('smw_gardissue_instance_without_cat', $skin->makeLinkObj($this->t1));
				
			case SMW_GARDISSUE_MAXCARD_NOT_NULL: 
				return wfMsg('smw_gardissue_maxcard_not_null', $skin->makeLinkObj($this->t1));
			case SMW_GARDISSUE_MINCARD_BELOW_NULL: 
				return wfMsg('smw_gardissue_mincard_below_null', $skin->makeLinkObj($this->t1));
			case SMW_GARDISSUE_WRONG_CARD_VALUE: 
				return wfMsg('smw_gardissue_wrong_card_value', $skin->makeLinkObj($this->t1));
			case SMW_GARDISSUE_WRONG_TARGET_VALUE: 
				return wfMsg('smw_gardissue_wrong_target_value', $this->t1->getText()/*$skin->makeLinkObj($this->t1)*/, $skin->makeLinkObj($this->t2));
			case SMW_GARDISSUE_WRONG_DOMAIN_VALUE: 
				return wfMsg('smw_gardissue_wrong_domain_value', $this->t1->getText()/*$skin->makeLinkObj($this->t1)*/, $skin->makeLinkObj($this->t2));
			case SMW_GARDISSUE_WRONG_CARD: 
				return wfMsg('smw_gardissue_wrong_card', $this->t1->getText()/*$skin->makeLinkObj($this->t1)*/, $skin->makeLinkObj($this->t2));
				
			case SMW_GARD_ISSUE_DOMAIN_NOT_RANGE: 
				return wfMsg('smw_gard_issue_domain_not_range',$this->t1->getText() /*$skin->makeLinkObj($this->t1)*/, $skin->makeLinkObj($this->t2));
			case SMW_GARD_ISSUE_INCOMPATIBLE_ENTITY: 
				return wfMsg('smw_gard_issue_incompatible_entity', $this->t1->getText()/*$skin->makeLinkObj($this->t1)*/, $skin->makeLinkObj($this->t2));
			case SMW_GARD_ISSUE_INCOMPATIBLE_TYPE: 
				return wfMsg('smw_gard_issue_incompatible_type',$this->t1->getText() /*$skin->makeLinkObj($this->t1)*/, $skin->makeLinkObj($this->t2));
						
			case SMW_GARD_ISSUE_CYCLE:
				return wfMsg('smw_gard_issue_cycle',  $this->explodeTitlesToLinkObjs($skin, $this->value));
				
			default: NULL;	
		}
	}
 }
 
 class ConsistencyBotFilter extends GardeningIssueFilter {
 	 	
 	
 	public function __construct() {
 		$this->gi_issue_classes = array(wfMsg('smw_gardissue_class_all'),
 				 wfMsg('smw_gardissue_class_covariance'),
 				 wfMsg('smw_gardissue_class_undefined'),
 				 wfMsg('smw_gardissue_class_missdouble'),
 				 wfMsg('smw_gardissue_class_wrongvalue'),
 				 wfMsg('smw_gardissue_class_incomp'),
 				 wfMsg('smw_gardissue_class_cycles'));
 	}
 	
 	public function getUserFilterControls($specialAttPage, $request) {
		return ' Match:<input name="matchString" type="text" class="wickEnabled"/>';
	}
	
	public function linkUserParameters(& $wgRequest) {
		return array('matchString' => $wgRequest->getVal('matchString'));
	}
	
	public function getData($options, $request) {
		$matchString = $request->getVal('matchString');
		if ($matchString == NULL || $matchString == '') {
			return parent::getData($options, $request);
		} else {
			$options->addStringCondition($matchString, SMW_STRCOND_MID);
			return parent::getData($options, $request);
		}
	}
 }
?>
