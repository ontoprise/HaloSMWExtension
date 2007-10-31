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
 define('SMW_GARDISSUE_RANGES_NOT_DEFINED', (SMW_CONSISTENCY_BOT_BASE+1) * 100 + 2);
 define('SMW_GARDISSUE_TYPES_NOT_DEFINED', (SMW_CONSISTENCY_BOT_BASE+1) * 100 + 3);
 
 
 // missing / doubles issues
 define('SMW_GARDISSUE_DOUBLE_TYPE', (SMW_CONSISTENCY_BOT_BASE+2) * 100 + 1);
 define('SMW_GARDISSUE_DOUBLE_MAX_CARD', (SMW_CONSISTENCY_BOT_BASE+2) * 100 + 2);
 define('SMW_GARDISSUE_DOUBLE_MIN_CARD', (SMW_CONSISTENCY_BOT_BASE+2) * 100 + 3);
 define('SMW_GARD_ISSUE_MISSING_PARAM', (SMW_CONSISTENCY_BOT_BASE+2) * 100 + 4);

 
 // wrong value / entity issues
 define('SMW_GARDISSUE_MAXCARD_NOT_NULL', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 1);
 define('SMW_GARDISSUE_MINCARD_BELOW_NULL', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 2);
 define('SMW_GARDISSUE_WRONG_CARD_VALUE', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 3);
 define('SMW_GARDISSUE_WRONG_TARGET_VALUE', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 4);
 define('SMW_GARDISSUE_WRONG_DOMAIN_VALUE', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 5);
 define('SMW_GARDISSUE_WRONG_CARD', (SMW_CONSISTENCY_BOT_BASE+3) * 100 + 6);
 
 // incompatible entity issues
 define('SMW_GARD_ISSUE_DOMAIN_NOT_RANGE', (SMW_CONSISTENCY_BOT_BASE+4) * 100 + 1);
 define('SMW_GARD_ISSUE_INCOMPATIBLE_ENTITY', (SMW_CONSISTENCY_BOT_BASE+4) * 100 + 2);
 define('SMW_GARD_ISSUE_INCOMPATIBLE_TYPE', (SMW_CONSISTENCY_BOT_BASE+4) * 100 + 3);
 
 // others
define('SMW_GARD_ISSUE_CYCLE', (SMW_CONSISTENCY_BOT_BASE+5) * 100 + 1);

 class ConsistencyBotIssue extends GardeningIssue {
 	
 	public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value) {
 		parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value);
 	}
 	
 	protected function getTextualRepresenation(& $skin) {
 		if ($this->t1 == "__error__") $text1 = $this->t1; else $text1 = "'".$this->t1->getText()."'";
		switch($this->gi_type) {
			case SMW_GARDISSUE_DOMAINS_NOT_COVARIANT: 
				return wfMsg('smw_gardissue_domains_not_covariant', $text1);
			case SMW_GARDISSUE_RANGES_NOT_COVARIANT: 
				return wfMsg('smw_gardissue_ranges_not_covariant', $text1);
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
			case SMW_GARDISSUE_TYPES_NOT_DEFINED: 
				return wfMsg('smw_gardissue_types_not_defined', $text1);
			case SMW_GARDISSUE_CATEGORY_NOT_DEFINED: 
				return wfMsg('smw_gardissue_category_not_defined', $text1);
			case SMW_GARDISSUE_PROPERTY_NOT_DEFINED: 
				return wfMsg('smw_gardissue_property_not_defined', $text1);
			case SMW_GARDISSUE_TARGET_NOT_DEFINED: 
				return wfMsg('smw_gardissue_target_not_defined', $text1);
			
			case SMW_GARDISSUE_DOUBLE_TYPE: 
				return wfMsg('smw_gardissue_double_type', $text1, $this->value);
			case SMW_GARDISSUE_DOUBLE_MAX_CARD: 
				return wfMsg('smw_gardissue_double_max_card', $text1, $this->value);
			case SMW_GARDISSUE_DOUBLE_MIN_CARD: 
				return wfMsg('smw_gardissue_double_min_card', $text1, $this->value);
			case SMW_GARD_ISSUE_MISSING_PARAM: 
				return wfMsg('smw_gard_issue_missing_param',$text1, $skin->makeLinkObj($this->t2), $this->value);
			case SMW_GARDISSUE_INSTANCE_WITHOUT_CAT: 
				return wfMsg('smw_gardissue_instance_without_cat', $text1);
				
			case SMW_GARDISSUE_MAXCARD_NOT_NULL: 
				return wfMsg('smw_gardissue_maxcard_not_null', $text1);
			case SMW_GARDISSUE_MINCARD_BELOW_NULL: 
				return wfMsg('smw_gardissue_mincard_below_null', $text1);
			case SMW_GARDISSUE_WRONG_CARD_VALUE: 
				return wfMsg('smw_gardissue_wrong_card_value', $text1);
			case SMW_GARDISSUE_WRONG_TARGET_VALUE: 
				return wfMsg('smw_gardissue_wrong_target_value', $text1, $skin->makeLinkObj($this->t2));
			case SMW_GARDISSUE_WRONG_DOMAIN_VALUE: 
				return wfMsg('smw_gardissue_wrong_domain_value', $text1, $skin->makeLinkObj($this->t2));
			case SMW_GARDISSUE_WRONG_CARD: 
				return wfMsg('smw_gardissue_wrong_card', $text1, $skin->makeLinkObj($this->t2));
				
			case SMW_GARD_ISSUE_DOMAIN_NOT_RANGE: 
				return wfMsg('smw_gard_issue_domain_not_range', $text1, $skin->makeLinkObj($this->t2));
			case SMW_GARD_ISSUE_INCOMPATIBLE_ENTITY: 
				return wfMsg('smw_gard_issue_incompatible_entity', $text1, $skin->makeLinkObj($this->t2));
			case SMW_GARD_ISSUE_INCOMPATIBLE_TYPE: 
				return wfMsg('smw_gard_issue_incompatible_type',$text1, $skin->makeLinkObj($this->t2));
						
			case SMW_GARD_ISSUE_CYCLE:
				return wfMsg('smw_gard_issue_cycle',  $this->explodeTitlesToLinkObjs($skin, $this->value));
				
			default: NULL;	
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
		return ' Match:<input name="matchString" type="text" class="wickEnabled" value="'.$matchString.'"/>';
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
