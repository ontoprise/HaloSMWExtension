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
 	
 	// global log which contains wiki-markup
 	private $globalLog;
 	
 	function ConsistencyBot() {
 		parent::GardeningBot("smw_consistencybot");
 		$this->globalLog = "== This is the consistency log! ==\n\n";
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
 		$errors = false;
 		// Schema level checks
 		// first, check if there are cycles in the inheritance graphs
 		echo "Checking for cycles in inheritance graphs...";
 		$log = $this->checkInheritanceCycles();
 		echo "done!\n\n";
 		$this->globalLog .= $log;
 		/*if ($log != '') {
 			
 			$this->globalLog .= $log;
 			echo "Abort here, because cycles were detected.\n";
 			return $this->globalLog; // end here
 		}*/
		
		echo "Checking property co-variance...";
        $log = $this->checkPropertyCovariance($delay);
        echo "done!\n\n";
        if ($log != '') {
        	$errors = true;
        	$this->globalLog .=  $log;
        }
        
               
 		 echo "Checking for consistency of inverse and equality relations...";
 		 $log = $this->checkInverseEqualityRelations();
 		 echo "done!\n\n";
 		  if ($log != '') {
        	$errors = true;
        	$this->globalLog .=  $log;
        }
 	
 		// Annotation level checks
 		 echo "Checking annotation level...";
         $log = $this->checkAnnotationLevel($delay);
 		 echo "done!\n\n";
          if ($log != '') {
        	$errors = true;
        	$this->globalLog .=  $log;
        }
        
        if (!$errors) {
        	return wfMsg('smw_gard_no_errors');
        }
        
 		// print the log for debugging
 		// echo $this->globalLog;
 		return $this->globalLog;
 		
 	}
 	
 	private function checkInheritanceCycles() {
 		$log = "";
 		$gcd = new GraphCycleDetector($this);
 		
 		$log .=  $gcd->getAllCategoryCycles("== ".wfMsg('smw_gard_errortype_categorygraph_contains_cycles')." ==\n\n");
 		$log .= $gcd->getAllPropertyCycles("== ".wfMsg('smw_gard_errortype_propertygraph_contains_cycles')." ==\n\n");
 		 		
 		if ($log != '') {
 			$log .= "----\n";
 		}
 		return $log;
 	}
 	
 	 	
 	private function checkPropertyCovariance($delay) {
 		$log = "";
 		$pcd = new PropertyCoVarianceDetector($this, $delay);
 		/*$rgc .= $pcd->checkRelationGraphForCovariance();
 		if ($rgc != '') {
 			$log .= "== ".wfMsg('smw_gard_errortype_relation_problems')." ==\n".$rgc."----\n";
 		}*/
 		$agc .= $pcd->checkPropertyGraphForCovariance();
 		if ($agc != '') {
 			$log .= "== ".wfMsg('smw_gard_errortype_attribute_problems')." ==\n".$agc."----\n";
 		}
 		return $log;
 	}
 	
 	private function checkAnnotationLevel($delay) {
 		$log = "";
 		$alc = new AnnotationLevelConsistency($this, $delay);
 		
 		$aac .= $alc->checkPropertyAnnotations();
 		if ($aac != '') {
 			$log .= "== ".wfMsg('smw_gard_errortype_inconsistent_attribute_annotations')." ==\n".$aac."----\n";
 		}
 		$acc .= $alc->checkAnnotationCardinalities();
 		if ($acc != '') {
 			$log .= "== ".wfMsg('smw_gard_errortype_inconsistent_cardinalities')." ==\n".$acc."----\n";
 		}
 		return $log;
 	}
 	
 	
 	
 	private function checkInverseEqualityRelations() {
 		$log = "";
 		$ier = new InverseEqualityConsistency($this);
 		$cir = $ier->checkInverseRelations();
 		if ($cir != '') {
 			$log .= "== ".wfMsg('smw_gard_errortype_inverse_relations')." ==\n".$cir."\n----\n";
 		}
 		$cer = $ier->checkEqualToRelations();
 		if ($cer != '') {
 			$log .= "== ".wfMsg('smw_gard_errortype_equality')." ==\n".$cer."\n----\n";
 		}
 		return $log;
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
define('SMW_GARD_ISSUE_PART_OF_CYCLE', 601);

 class ConsistencyBotIssue extends GardeningIssue {
 	
 	public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value) {
 		parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value);
 	}
 	
 	public function getTextualRepresenation(& $skin) {
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
				return wfMsg('smw_gardissue_wrong_target_value', $skin->makeLinkObj($this->t1), $skin->makeLinkObj($this->t2));
			case SMW_GARDISSUE_WRONG_DOMAIN_VALUE: 
				return wfMsg('smw_gardissue_wrong_domain_value', $skin->makeLinkObj($this->t1), $skin->makeLinkObj($this->t2));
			case SMW_GARDISSUE_WRONG_CARD: 
				return wfMsg('smw_gardissue_wrong_card', $skin->makeLinkObj($this->t1), $skin->makeLinkObj($this->t2));
				
			case SMW_GARD_ISSUE_DOMAIN_NOT_RANGE: 
				return wfMsg('smw_gard_issue_domain_not_range', $skin->makeLinkObj($this->t1), $skin->makeLinkObj($this->t2));
			case SMW_GARD_ISSUE_INCOMPATIBLE_ENTITY: 
				return wfMsg('smw_gard_issue_incompatible_entity', $skin->makeLinkObj($this->t1), $skin->makeLinkObj($this->t2));
			case SMW_GARD_ISSUE_INCOMPATIBLE_TYPE: 
				return wfMsg('smw_gard_issue_incompatible_type', $skin->makeLinkObj($this->t1), $skin->makeLinkObj($this->t2));
			//TODO: complete it	
			default: NULL;	
		}
	}
 }
?>
