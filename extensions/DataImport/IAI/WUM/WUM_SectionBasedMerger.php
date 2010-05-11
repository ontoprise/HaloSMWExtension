<?php

/**
 * @file
  * @ingroup DIWUM
  * 
  * @author Ingo Steinbauer
 */

/**
 * This class implements the Section based Merge Approach
 * 
 * @author Ingo Steinbauer
 *
 */

//todo: Add ingroup statements
class WUMSectionBasedMerger {
	
	static private $instance = null;

	/**
	 * singleton
	 * 
	 * @return WUMSectionBasedMerger
	 */
	public static function getInstance(){
		if(self::$instance == null){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * This method does the section based merging.
	 * 
	 * @param unknown_type $originalWPText
	 * @param unknown_type $newWPText
	 * @param unknown_type $currentUPText
	 * @param unknown_type $alreadyMergedText
	 * @param unknown_type $mergeFaults
	 * @return unknown_type
	 */
	public function merge($originalWPText, $newWPText, $currentUPText, 
			$alreadyMergedText, $mergeFaults){
		
		
		wfProfileIn('WUMSectionBasedMerger->merge');
				
		//compute the section tree for all three text versions
		$originalWPSections = $this->getSectionTree($originalWPText);
		$newWPSections = $this->getSectionTree($newWPText);
		$currentUPSections = $this->getSectionTree($currentUPText);

		//compute a mapping between the upSectionTree and the original WPSectionTree,
		//a mapping between the originalWPSectionTree and the newWPSectionTree
		//and finally one between the currentUPSectionTree and the newWPSectionTree
		$sourceMappings = $this->getSectionMappings($originalWPSections, $currentUPSections);
		$targetMappings = $this->getSectionMappings($newWPSections, $originalWPSections);
		$combinedMapping = $this->combineSectionMappings($sourceMappings, $targetMappings);

		//first compute in which order the sections between currentUP and newWP have to be merged
		//and then add position information to each mergeInstruction. The positions are required to
		//find out to which section to add a merge fault
		$mergeOrder = $this->getMergeOrder($combinedMapping);
		$mergeOrder = $this->addBorderPositionsToMergeOrder($mergeOrder, $currentUPText, 0);
		
		//find the section in which a merge fault occured
		$mergeFaults = $this->findPatchFaultsTargetSection($mergeOrder, $mergeFaults);
		
		//finally append merge faults to the corresponding sections
		$alreadyMergedText = $this->appendPatchesToSections($alreadyMergedText, $mergeFaults);

		wfProfileOut('WUMSectionBasedMerger->merge');
		
		return $alreadyMergedText;
	}
	
	/**
	 * This function combutes a section tree for a given text
	 * 
	 * @param unknown_string $text
	 * @return array
	 */
	private function getSectionTree($text){
		global $wgParser;
		$popts = new ParserOptions();
		$wgParser->mOptions =  $popts;
	
		//get the dom tree from the preprocessor
		$root = $wgParser->getPreprocessor()->preprocessToObj($text);
		$node = $root->getFirstChild();
	
		$sections = array();
	
		//initialize a dummy root node - it will not be added to the final section tree
		$lastSection = array();
		$lastSection["level"] = -1;
		$lastSection["predecessor"] = -1;
		$lastSection["id"] = 0;
	
		$counter = 1;
		while ( $node ) {
			$nodeType = $node->getName();
			if($nodeType == "h"){ //this is a section
				$headingData = $node->splitHeading();
				$newSection = array();
				$newSection["level"] = $headingData["level"];
				$newSection["id"] = $counter;
				$newSection["title"] = $node->getFirstChild()->__toString();
	
				//find the predecessor of the current section
				$search = true;
				$whileCounter = 0;
				while($search){
					$whileCounter += 1;
					if($lastSection["level"] == -1){
						$newSection["predecessor"] = -1;
						$search = false;
					} else if ($lastSection["level"] < $newSection["level"]){
						$newSection["predecessor"] = $lastSection["id"];
						$search = false;
					} else if ($lastSection["level"] == $newSection["level"]){
						$newSection["predecessor"] = $lastSection["predecessor"];
						$search = false;
					} else if ($lastSection["predecessor"] == -1){
						$newSection["predecessor"] = -1;
						$search = false;
					} else {
						//access id of predecessor minus one becuase ids are always arry index plus one
						$lastSection = $sections[$lastSection["predecessor"]-1];
					}
				}
	
				$counter += 1;
				$lastSection = $newSection;
				$sections[] = $newSection;
			}
			$node = $node->getNextSibling();
		}
	
		return $sections;
	}
	
	/**
	 * Computes a mapping between the sections of two text versions
	 * 
	 * @param array $originalSections
	 * @param array $newSections
	 * @return array
	 */
	private function getSectionMappings($originalSections, $newSections){
		$mappedSections = array();
		for($i=0; $i < count($newSections); $i++){
			if($newSections[$i]["predecessor"] == -1){
				$mappedSections = array_merge($mappedSections,
					$this->getSubSectionMappings($newSections[$i], -1, $newSections, $originalSections));
			}
		}
		return $mappedSections;
	}
	
	
	/**
	 * This is a helper method for the getSectionMappings method
	 * 
	 * @param unknown_type $currentSection
	 * @param unknown_type $originalPredecessorId
	 * @param unknown_type $newSections
	 * @param unknown_type $originalSections
	 * @param unknown_type $mapp
	 * @return unknown_type
	 */
	private function getSubSectionMappings($currentSection, $originalPredecessorId, $newSections, $originalSections, $mapp = true){
		$originalSubSections = $this->getSubSections($originalPredecessorId, $originalSections);
		$mappedSections = array();
		$mappedto = false;
		if($mapp){
			for($i=0; $i < count($originalSubSections); $i++){
				if($currentSection["level"] == $originalSubSections[$i]["level"]
				&& $currentSection["title"] == $originalSubSections[$i]["title"]){
					$currentSection["mappedto"] = $originalSubSections[$i]["id"];
					$mappedSections[] = $currentSection;
					$mappedto = true;
					$newSubSections = $this->getSubSections($currentSection["id"], $newSections);
					for($k=0; $k < count($newSubSections); $k++){
						$mappedSections = array_merge($mappedSections,
							$this->getSubSectionMappings($newSubSections[$k], $originalSubSections[$i]["id"], $newSections, $originalSections));
					}
					break;
				}
			}
		}
		return $mappedSections;
	}
	
	/**
	 * Helper method for the getSubSectionMappings method
	 * 
	 * @param unknown_type $currentPredecessorId
	 * @param unknown_type $sections
	 * @return unknown_type
	 */
	private function getSubSections($currentPredecessorId, $sections){
		$subSections = array();
		for($i=0; $i < count($sections); $i++){
			if($currentPredecessorId == $sections[$i]["predecessor"]){
				$subSections[] = $sections[$i];
			}
		}
		return $subSections;
	}
	
	/**
	 * This method combines two section tree mappings
	 * 
	 * @param unknown_type $sourceSections
	 * @param unknown_type $targetSections
	 * @return unknown_type
	 */
	private function combineSectionMappings($sourceSections, $targetSections){
		$combinedSectionMappings = array();
		for($i = 0; $i < count($sourceSections); $i++){
			for($k = 0; $k < count($targetSections); $k++){
				if($sourceSections[$i]["mappedto"] == $targetSections[$k]["id"]){
					$sourceSections[$i]["patchtarget"] = $targetSections[$k]["mappedto"];
					$combinedSectionMappings[] = $sourceSections[$i];
				}
			}
		}
		return $combinedSectionMappings;
	}
	
	/**
	 * Computes in which order to merge the currentUP and newWP sections
	 * 
	 * @param $mergedSections
	 * @param $predecessorId
	 * @return unknown_type
	 */
	private function getMergeOrder($mergedSections, $predecessorId = -1){
		$mergeOrder = array();
	
		for($i =0; $i < count($mergedSections); $i++){
			if($mergedSections[$i]["predecessor"] == $predecessorId){
				$mergeOrder[$mergedSections[$i]["id"]] =
					array("mappedto" => $mergedSections[$i]["mappedto"],
						"patchtarget" => $mergedSections[$i]["patchtarget"],
						"subtests" => $this->getMergeOrder($mergedSections, $mergedSections[$i]["id"]));
			}
		}
		return $mergeOrder;
	}
	
	/**
	 * Adds information about section start positions and section lengths to merge instructions
	 * 
	 * @param unknown_type $mergeOrder
	 * @param unknown_type $text
	 * @param unknown_type $sectionOffset
	 * @param unknown_type $startOffset
	 * @return unknown
	 */
	private function addBorderPositionsToMergeOrder($mergeOrder, $text, $sectionOffset=0, $startOffset=0){
		global $wgParser;
		foreach($mergeOrder as $sectionId => $mergeInstruction){
			$sectionText = $wgParser->getSection($text, $sectionId - $sectionOffset);
			$mergeInstruction['upStart'] = strpos($text, $sectionText) + $startOffset;
			$mergeInstruction['upLength'] = strlen($sectionText);
			$mergeInstruction['subtests'] = 
			 	$this->addBorderPositionsToMergeOrder($mergeInstruction['subtests'], $sectionText, $sectionId-1, $mergeInstruction['upStart']);
			 $mergeOrder[$sectionId] = $mergeInstruction;
		}
		return $mergeOrder;
	}
	
	/**
	 * Find out which merge fault must be appended to which section
	 * 
	 * @param unknown_type $mergeOrder
	 * @param unknown_type $patches
	 * @return unknown
	 */
	private function findPatchFaultsTargetSection($mergeOrder, $patches){
		for($i=0; $i < count($patches); $i++){
			$myMergeOrder = $mergeOrder;
			$patches[$i]->section = -1;
			$found = true;
			while(count($myMergeOrder) > 0 && $found){
				$found = false;
				foreach($myMergeOrder as $mergeInstruction){
					if($patches[$i]->start2 > $mergeInstruction['upStart'] && 
							$patches[$i]->start2 < $mergeInstruction['upStart'] + $mergeInstruction['upLength']){
						$patches[$i]->section = $mergeInstruction['patchtarget'];
						$myMergeOrder = $mergeInstruction['subtests'];
						$found = true;
						break;			
					}
				}
			}
		}
		
		return $patches;
	}
	
	/**
	 * This method appends a pretty printed version of a patch fault to the corresponding section
	 * 
	 * @param unknown_type $text
	 * @param unknown_type $patches
	 * @return unknown_type
	 */
	private function appendPatchesToSections($text, $patches){
		global $wgParser;
		foreach ($patches as $patch){
			if($patch->section == -1){
				$patchText = WUMThreeWayBasedMerger::getInstance()->getPatchText($patch);
				$patchText = str_replace("=","//--\\", $patchText);
				$text .= "\n\n".$patchText;
			} else {
				$sectionText = $wgParser->getSection($text, $patch->section);
				$patchText = WUMThreeWayBasedMerger::getInstance()->getPatchText($patch);
				$patchText = str_replace("=","//--\\", $patchText);
				$sectionText .= "\n\n".$patchText;
				$text = $wgParser->replaceSection($text, $patch->section, $sectionText);
			}
		}
		return $text;
	}

	
}
