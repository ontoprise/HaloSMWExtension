<?php

/**
 * @file
  * @ingroup DIWUM
  * 
  * @author Ingo Steinbauer
 */

global $smwgDIIP;
require_once ("$smwgDIIP/IAI/WUM/WUM_Diff_Match_Patch.php");

/**
 * This is a wrapper for the diff_match_patch class, which
 * replaces some of ist methods, because they do not work
 * like expected.
 *
 * @author Ingo Steinbauer
 *
 */
class WUMDiffMatchPatchWrapper {

	static private $instance = null;

	/**
	 * singleton
	 *
	 * @return WUMDiffMatchPatchWrapper
	 */
	public static function getInstance(){
		if(self::$instance == null){
			self::$instance = new self();
		}
		return self::$instance;
	}

	//the original diff_match_patch class
	private $dmp = null;

	public $Patch_Margin = 40;
	public $Match_Distance = 500;

	/**
	 * Initializes the original diff_match_patch class.
	 */
	public function __construct(){
		$this->dmp = new diff_match_patch();
	}

	/**
	 * Create diffs
	 *
	 * @param unknown_type $text1
	 * @param unknown_type $text2
	 * @param unknown_type $checklines
	 * @return unknown_type
	 */
	public function diff_main($text1, $text2, $checklines = false){
		return $this->dmp->diff_main($text1, $text2, false);
	}

	/**
	 * create patches
	 *
	 * @param unknown_type $diffs
	 * @return unknown_type
	 */
	public function patch_make($diffs, $text){
		$patch_margin = $this->Patch_Margin;

		$explicitContributions = array();
		$offset = 0;
		while(strpos($text, WUM_TAG_OPEN) !== false){
			$ec['start'] = $offset + strpos($text, WUM_TAG_OPEN);
			$text = substr($text, strpos($text, WUM_TAG_OPEN) + strlen(WUM_TAG_OPEN));
			if(strpos($text, WUM_TAG_CLOSE) !== false){
				$ec['end'] = 
					$offset = strpos($text, WUM_TAG_CLOSE) + strlen(WUM_TAG_CLOSE) + $ec['start'] + strlen(WUM_TAG_OPEN);
				$text = substr($text, strpos($text, WUM_TAG_CLOSE) + strlen(WUM_TAG_CLOSE));
				$explicitContributions[] = $ec;
			} else {
				break;
			}
		}
			
		
		//create initial patches and make sure that each patch starts and ends with an equality diff where possible
		//equality diffs are shortened to the patch_margin where possible
		//we do not yet deal with too short equality diffs
		$patches = array();
		$patch = new patch_obj();
		$firstDiffEqual = true;
		$start = 0;
		$startNext = 0;
		$length = 0;
		for($i=0; $i < count($diffs); $i++){
			if($diffs[$i][0] == DIFF_EQUAL){
				if($firstDiffEqual){
					$patch->diffs[] = $diffs[$i];
					$firstDiffEqual = false;
					$startNext += strlen($diffs[$i][1]);
					$length += strlen($diffs[$i][1]);
				} else {
					$patch->diffs[] = $diffs[$i];
					$patch->start1 = $start;
					$patch->start2 = $start;
					$start = $startNext;
					$length += strlen($diffs[$i][1]);
					$patch->length2 = $length;
					$length = strlen($diffs[$i][1]);;
					$startNext += strlen($diffs[$i][1]);
					$patches[] = $patch;
					$patch = new patch_obj();
					$patch->diffs[] = $diffs[$i];
				}
			} else if($diffs[$i][0] == DIFF_INSERT){
				$patch->diffs[] = $diffs[$i];
				$startNext += strlen($diffs[$i][1]);
				$length += strlen($diffs[$i][1]);
				$firstDiffEqual = false;
			} else if($diffs[$i][0] == DIFF_DELETE){
				$patch->diffs[] = $diffs[$i];
				//because patches are applied one after another
				//$startNext -= strlen($diffs[$i][1]);
				$firstDiffEqual = false;
			}
		}
		
		//deal with the last patch, which might not end with an equality diff
		if(count($patch->diffs) > 1){
			$patch->start1 = $start;
			$patch->start2 = $start;
			$patch->length2 = $length;
			$patches[] = $patch;
		} else if(count($patch->diffs) == 1){
			if($patch->diffs[0][0] == DIFF_INSERT || $patch->diffs[0][0] == DIFF_DELETE){
				$patch->start1 = $start;
				$patch->start2 = $start;
				$patch->length2 = $length;
				$patches[] = $patch;
			}
		}
		
		foreach($explicitContributions as $ec){
			$combinePatches = array();
			$processedPatchesPrefix = array();
			$processedPatchesSuffix = array();
			for($i=0; $i < count($patches); $i++){
				if($patches[$i]->start2 <= $ec['start'] && $patches[$i]->start2 + $patches[$i]->length2 > $ec['start']){
					$combinePatches[] = $patches[$i];
				} else if($patches[$i]->start2 >= $ec['start'] && $patches[$i]->start2 < $ec['end']){
					$combinePatches[] = $patches[$i];
				} else if($patches[$i]->start2 < $ec['start']){
					$processedPatchesPrefix[] = $patches[$i];
				} else if($patches[$i]->start2 > $ec['start']){
					$processedPatchesSuffix[] = $patches[$i];
				}
			}
				
			$patch = new patch_obj();
			$insert = $delete = '';
			
			for($k = 0; $k < count($combinePatches); $k++){
				for($j=0; $j<count($combinePatches[$k]->diffs); $j++){
					if($combinePatches[$k]->diffs[$j][0] == DIFF_INSERT){
						$patch->length2 += strlen($combinePatches[$k]->diffs[$j][1]);
						$insert .= $combinePatches[$k]->diffs[$j][1];
					} else if($combinePatches[$k]->diffs[$j][0] == DIFF_DELETE){
						$delete .= $combinePatches[$k]->diffs[$j][1];
					} else if($combinePatches[$k]->diffs[$j][0] == DIFF_EQUAL && $j > 0){
						$delete .= $combinePatches[$k]->diffs[$j][1];
						$insert .= $combinePatches[$k]->diffs[$j][1];
						$patch->length2 += strlen($combinePatches[$k]->diffs[$j][1]);
					}
				}
			}
			$patch->start1 = $combinePatches[0]->start1;
			$patch->start2 = $combinePatches[0]->start2;
			$lastEqual = $combinePatches[count($combinePatches)-1]
			->diffs[count($combinePatches[count($combinePatches)-1]->diffs)-1][1];

				
			$patch->length2 -= strlen($lastEqual);
			$patch->length2 += strlen($combinePatches[0]->diffs[0][1]);
			$insert = substr($insert, 0, strlen($insert)- strlen($lastEqual));
			$delete = substr($delete, 0, strlen($delete)- strlen($lastEqual));
				
			$patch->diffs[] = array(0 => DIFF_EQUAL,1 => $combinePatches[0]->diffs[0][1]);
			$patch->diffs[] = array(0 => DIFF_DELETE,1 => $delete);
			$patch->diffs[] = array(0 => DIFF_INSERT,1 => $insert);
			$patch->diffs[] = array(0 => DIFF_EQUAL,1 => $lastEqual);

			$patches = $processedPatchesPrefix;
			$patches[] = $patch;
			for($k=0; $k<count($processedPatchesSuffix);$k++){
				$patches[] = $processedPatchesSuffix[$k];
			}
		}
		
		$processedPatches = array();
		for($i=0; $i < count($patches); $i++){
			$patch = $this->patch_deep_copy($patches[$i]);
			if($patch->diffs[0][0] == DIFF_EQUAL && strlen($patch->diffs[0][1]) < $patch_margin){
				$k = $i;
				$text = $patch->diffs[0][1];
				while($k != 0){
					$k-=1;
					$previousPatch = $patches[$k];
					for($l=count($previousPatch->diffs)-2; $l >= 0; $l--){
						if($previousPatch->diffs[$l][0] == DIFF_EQUAL || $previousPatch->diffs[$l][0] == DIFF_INSERT){
							$text = $previousPatch->diffs[$l][1].$text;
						}
					}

					if(strlen($text) > $patch_margin){
						break;
					}
				}
				if(Strlen($text) > $patch_margin){
					$patch->diffs[0][1] = substr($text, strlen($text) - $patch_margin);
				} else {
					$patch->diffs[0][1] = $text;
				}
			} else if($patch->diffs[0][0] == DIFF_EQUAL){
				$patch->diffs[0][1] = substr($patch->diffs[0][1], strlen($patch->diffs[0][1]) - $patch_margin);
					
			}

			if($patch->diffs[count($patch->diffs)-1][0] == DIFF_EQUAL && strlen($patch->diffs[count($patch->diffs)-1][1]) < $patch_margin){
				$k = $i;
				$text = $patch->diffs[count($patch->diffs)-1][1];
				while($k != count($patches)-1){
					$k+=1;
					$nextPatch = $patches[$k];
					for($l=1; $l < count($nextPatch->diffs); $l++){
						if($nextPatch->diffs[$l][0] == DIFF_EQUAL || $nextPatch->diffs[$l][0] == DIFF_DELETE){
							$text .= $nextPatch->diffs[$l][1];
						}
					}

					if(strlen($text) > $patch_margin){
						break;
					}
				}
				if(Strlen($text) > $patch_margin){
					$patch->diffs[count($patch->diffs)-1][1] = substr($text, 0, $patch_margin);
				} else {
					$patch->diffs[count($patch->diffs)-1][1] = $text;
				}
			} else if($patch->diffs[count($patch->diffs)-1][0] == DIFF_EQUAL){
				$patch->diffs[count($patch->diffs)-1][1] =
				substr($patch->diffs[count($patch->diffs)-1][1], 0, $patch_margin);
			}

			if($patch->diffs[0][0] == DIFF_EQUAL){
				$patch->start1 += strlen($patches[$i]->diffs[0][1]) - strlen($patch->diffs[0][1]);
				$patch->start2 += strlen($patches[$i]->diffs[0][1]) - strlen($patch->diffs[0][1]);
			}

			$processedPatches[] = $patch;
		}
		
		$patches = $processedPatches;
		
		for($i=0; $i < count($patches); $i++){
			$length1 = 0;
			$length2 = 0;
			for($k=0; $k < count($patches[$i]->diffs); $k++){
				if($patches[$i]->diffs[$k][0] == DIFF_EQUAL || $patches[$i]->diffs[$k][0] == DIFF_DELETE){
					$length1 += strlen($patches[$i]->diffs[$k][1]);
				}
				if($patches[$i]->diffs[$k][0] == DIFF_EQUAL || $patches[$i]->diffs[$k][0] == DIFF_INSERT){
					$length2 += strlen($patches[$i]->diffs[$k][1]);
				}
			}
			$patches[$i]->length1 = $length1;
			$patches[$i]->length2 = $length2;
		}

		return $patches;
	}

	/**
	 * create a copy of a patch object
	 *
	 * @param $patch
	 * @return unknown_type
	 */
	private function patch_deep_copy($patch){
		$newPatch = new patch_obj();

		for($i = 0; $i < count($patch->diffs); $i++){
			$newPatch->diffs[] = $patch->diffs[$i];
		}

		$newPatch->start1 = $patch->start1;
		$newPatch->start2 = $patch->start2;
		$newPatch->length1 = $patch->length1;
		$newPatch->length2 = $patch->length2;

		return $newPatch;
	}

	/**
	 * apply patches
	 *
	 * @param unknown_type $text
	 * @param unknown_type $patches
	 * @return unknown_type
	 */
	function patch_apply($text, $patches){
		$offset = 0;
		$overallResult = array(0 => '', 1 => array());
		for($i=0; $i<count($patches); $i++){
			$result = $this->patch_doApply($text, $patches[$i], $offset);
			$text = $result[0];
			$overallResult[1][] = $result[1];
			if(!$result[1]){
				$offset += $patches[$i]->length1 - $patches[$i]->length2;
			}
		}

		$overallResult[0] = $text;
		return $overallResult;
	}

	/**
	 * helper method for the patch_apply method
	 *
	 * @param unknown_type $text
	 * @param unknown_type $patch
	 * @param unknown_type $offset
	 * @return unknown_type
	 */
	function patch_DoApply($text, $patch, $offset){
		$match_distance = $this->Match_Distance;

		$start = $patch->start1 - $match_distance + $offset;
		if($start < 0) $start = 0;

		$end = $patch->start1 + $patch->length1 + $match_distance + $offset;
		if($end > strlen($text)) $end = strlen($text);

		$subText = substr($text, $start, $end-$start);

		$pattern = "";
		for($i = 0; $i < count($patch->diffs); $i++){
			if($patch->diffs[$i][0] == DIFF_EQUAL || $patch->diffs[$i][0] == DIFF_DELETE){
				$pattern .= $patch->diffs[$i][1];
			}
		}

		$startSubString = strpos($subText, $pattern);

		if($startSubString != strrpos($subText, $pattern) || $startSubString === false){
			//no exact match
			return array(0 => $text, 1 => false);
		}

		$replacement = "";
		for($i = 0; $i < count($patch->diffs); $i++){
			if($patch->diffs[$i][0] == DIFF_EQUAL || $patch->diffs[$i][0] == DIFF_INSERT){
				$replacement .= $patch->diffs[$i][1];
			}
		}

		$patchText = WUMThreeWayBasedMerger::getInstance()->getPatchText($patch);
		$replacement = str_replace($patchText,
		str_replace("=", "//--\\", $patchText), $replacement);

		$text = substr($text, 0, $start + $startSubString).$replacement.substr($text, $start + $startSubString + $patch->length1);

		return array(0 => $text, 1 => true);
	}
}