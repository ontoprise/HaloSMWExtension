<?php

/**
 * @file
  * @ingroup DIWUM
  * 
  * @author Ingo Steinbauer
 */

global $smwgDIIP;
require_once ("$smwgDIIP/IAI/WUM/WUM_DiffMatchPatchWrapper.php");

/**
 * This class is responsible for applying three way merge algorithms.
 *
 * @author Ingo Steinbauer
 *
 */
class WUMThreeWayBasedMerger {

	static private $instance = null;

	/**
	 * singleton
	 *
	 * @return WUMThreeWayBasedMerger
	 */
	public static function getInstance(){
		if(self::$instance == null){
			self::$instance = new self();
		}
		return self::$instance;
	}

	private $textMargin = "%%&&##%%&&##%%&&##";

	/**
	 * Creates patches between the originalWPText and the currentUPText and applies them
	 * on the newWPText. Returns the merged text as well as merge faults, which occured
	 * during the merge process
	 *
	 * @param string $originalWPText
	 * @param string $newWPText
	 * @param string $currentUPText
	 * @return array
	 */
	public function merge($originalWPText, $newWPText, $currentUPText){
		wfProfileIn('WUMThreewayBasedMerger->merge');
		$originalWPText = $this->appendTextMargins($originalWPText);
		$newWPText = $this->appendTextMargins($newWPText);
		$currentUPText = $this->appendTextMargins($currentUPText);

		$patches = $this->createPatches($originalWPText, $currentUPText);
		
		$result = $this->applyPatches($patches, $newWPText);

		$result['mergedText'] = $this->removetextMargins($result['mergedText']);
		
		wfProfileOut('WUMThreewayBasedMerger->merge');
		return $result;
	}

	private function appendTextMargins($text){
		return $this->textMargin.$text.$this->textMargin;
	}

	private function removeTextMargins($text){
		return substr($text, strlen($this->textMargin), strlen($text) - 2*strlen($this->textMargin));
	}

	/**
	 * Create patches between two text versions
	 *
	 * @param string $original
	 * @param string $modified
	 * @return array
	 */
	private function createPatches($original, $modified){
		global $wumPatchMargin; 
		
		$dmp = WUMDiffMatchPatchWrapper::getInstance();
		$dmp->Patch_Margin = $wumPatchMargin; 
		$diffs = $dmp->diff_main($original, $modified);


		$patches = $dmp->patch_make($diffs, $modified);
		
		return $patches;
	}

	/**
	 * Apply patches and compute merge faults
	 *
	 * @param array $patches
	 * @param string $text
	 * @return array
	 */
	private function applyPatches($patches, $text){
		$dmp = WUMDiffMatchPatchWrapper::getInstance();
		global $wumMatchDistance;
		$dmp->Match_Distance = $wumMatchDistance;

		$result = $dmp->patch_apply($text, $patches);

		$mergeFaults = array();
		for($i=0; $i < count($result[1]); $i++){
			if(!$result[1][$i]){
				$mergeFaults[] = $patches[$i];
			}
		}

		return array('mergedText' => $result[0], 'mergeFaults' => $mergeFaults);
	}

	/**
	 * section based merging requires that patch results do not create new sections
	 * equality symbols are therefore replaced by a special character signature
	 * this must be redone before finally finishing the merge process
	 *
	 * @param string $text
	 * @return string
	 */
	public function finalizeMergeResult($text){
		return str_replace('//--\\', '=', $text);
	}

	/**
	 * pretty prints the text, which is added by a patch
	 *
	 * @param unknown_type $patch
	 * @return unknown_type
	 */
	public function getPatchText($patch){
		$delete = $insert = $equal = "";
		for($i=0; $i < count($patch->diffs); $i++){
			if($patch->diffs[$i][0] == DIFF_EQUAL){
				$equal = 	$patch->diffs[$i][1];
			} else if($patch->diffs[$i][0] == DIFF_INSERT){
				$insert = $patch->diffs[$i][1];

				if(strpos($insert, WUM_TAG_OPEN) !== false && strpos($insert, WUM_TAG_CLOSE) !== false){
					$insert = substr($insert, strpos($insert, WUM_TAG_OPEN), strrpos($insert, WUM_TAG_CLOSE) 
						- strpos($insert, WUM_TAG_OPEN) + strlen(WUM_TAG_CLOSE));
				} else if(substr($insert, 0,1) != "\n"){
					if(substr($insert, strlen($insert)-1) == substr($equal, strlen($equal)-1)){
						$insertTemp = $insert;
						while(strlen($insertTemp) > 0 && strlen($equal) > 0){
							if(substr($insertTemp, 0,1) == "\n" || substr($equal, strlen($equal)-1) == "\n"){
								$insert = $insertTemp;
								break;
							} else if(substr($insertTemp, strlen($insertTemp)-1) == substr($equal, strlen($equal)-1)){
								$insertTemp = substr($insertTemp, strlen($insertTemp)-1).substr($insertTemp, 0, strlen($insertTemp)-1);
								$equal = substr($equal, 0, strlen($equal)-1);
							} else {
								break;
							}
						}
					} else {
						//does not work very well
						//f(strpos($insert, "\n") > 0){
						//	$insert = substr($insert, strpos($insert, "\n")).substr($insert, 0, strpos($insert, "\n"));
						//}
					}
				}
				//if(strpos($insert, "lts 4") > 0) $insert();
				break;
			}
		}
		return $insert;
	}

}