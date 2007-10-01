<?php
/*
 * Created on 13.03.2007
 *
 * Author: KK
 */
 
 require_once(dirname(__FILE__) . "/../SMW_GardeningBot.php");
 require_once("GraphCycleDetector.php");
 require_once("PropertyCoVarianceDetector.php");
 require_once("AnnotationLevelConsistency.php");
 require_once("InverseEqualityConsistency.php");
 
 
 
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
 		if ($log != '') {
 			
 			$this->globalLog .= $log;
 			echo "Abort here, because cycles were detected.\n";
 			return $this->globalLog; // end here
 		}
		
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
 			$log .= "== ".wfMsg('smw_gard_errortype_inverse_relations')." ==\n".$cir."----\n";
 		}
 		$cer = $ier->checkEqualToRelations();
 		if ($cer != '') {
 			$log .= "== ".wfMsg('smw_gard_errortype_equality')." ==\n".$cer."----\n";
 		}
 		return $log;
 	}
 	
 	
 }
 
 
  // instantiate it once.
 new ConsistencyBot();
?>
