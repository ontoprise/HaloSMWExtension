<?php
/*
 * Created on 08.11.2007
 *
 * Converts GardeningIssues to XML in order to display it in OntologyBrowser
 *
 * Author: kai
 */
 
 class SMWOntologyBrowserErrorHighlighting {
 	

 	/**
 	 * Converts Gardening Issue objects to XML.
 	 * XML output contains:
 	 * 	- type of gardening issue (attribute)
 	 *  - value of gardening issue (attribute)
 	 *  - textual representation as HTML (node content)
 	 * 
 	 * @param array & issues GardeningIssues (GI)
 	 * 
 	 * @return XML string
 	 */	
 	public static function getGardeningIssuesAsXML(array & $issues) {
 	
 		$errorTags = "";
 		foreach($issues as $i) {
 			 			
 			if ($i->getTitle1() !== NULL) {
 				$value = ""; //$i->getValue() != NULL ? "value=\"".$i->getValue()."\"" : "";
 				$htmlRepresentation = $i->getRepresentation();
 				$errorTags .= "<gi type=\"".$i->getType()."\" $value><![CDATA[$htmlRepresentation]]></gi>";
 			}
 		}
 		
 		return $errorTags !== "" ? "<gissues>".$errorTags."</gissues>" : "";
 	}
 	
 	/**
 	 * Returns Gardening issues objects referring to annotations to XML.
 	 * GIs must be of one of the types: SMW_GARDISSUE_WRONG_UNIT, SMW_GARD_ISSUE_MISSING_PARAM
 	 * SMW_GARDISSUE_WRONG_TARGET_VALUE, SMW_GARDISSUE_TOO_HIGH_CARD, SMW_GARDISSUE_TOO_LOW_CARD
 	 * 
 	 * @param array & $issues
 	 * @param $value of annotation
 	 * 
 	 * @return XML string
 	 */
 	public static function getAnnotationIssuesAsXML(array & $issues, $value) {
 		
 		$errorTags = "";
 		foreach($issues as $i) {
 			switch($i->getType()) {
 				case SMW_GARDISSUE_WRONG_UNIT: { // highlight if unit matches the GI
 					if (stripos($value->getUnit(), $i->getValue()) !== false) {
 						$value = ""; //$i->getValue() != NULL ? "value=\"".$i->getValue()."\"" : "";
 						$htmlRepresentation = $i->getRepresentation();
 						$errorTags .= "<gi type=\"".$i->getType()."\" $value><![CDATA[$htmlRepresentation]]></gi>";
 					} 
 					break;
 				}
 				case SMW_GARD_ISSUE_MISSING_PARAM: {
 					$dvs = $value->getDVs(); // highlight if missing container matches the GI 
 					if ($dvs[$i->getValue()] == NULL) {
 						$value = ""; //$i->getValue() != NULL ? "value=\"".$i->getValue()."\"" : "";
 						$htmlRepresentation = $i->getRepresentation();
 						$errorTags .= "<gi type=\"".$i->getType()."\" $value><![CDATA[$htmlRepresentation]]></gi>";
 					}
 					break;
 				}
 				case SMW_GARDISSUE_WRONG_TARGET_VALUE: { // highlight if wrong target matches the GI
 					if ($value->getTitle()->getDBkey() == $i->getValue()) {
 						$value = ""; //$i->getValue() != NULL ? "value=\"".$i->getValue()."\"" : "";
 						$htmlRepresentation = $i->getRepresentation();
 						$errorTags .= "<gi type=\"".$i->getType()."\" $value><![CDATA[$htmlRepresentation]]></gi>";
 					}
 					break;
 				}
 				
 				case SMW_GARDISSUE_TOO_HIGH_CARD: // fall through
 				case SMW_GARDISSUE_TOO_LOW_CARD: { // highlight always
 					$value = $i->getValue() != NULL ? "value=\"".$i->getValue()."\"" : "";
 					$htmlRepresentation = $i->getRepresentation();
 					$errorTags .= "<gi type=\"".$i->getType()."\" $value><![CDATA[$htmlRepresentation]]></gi>";
 				}
 				break;
 			} 			
 			
 		}
 		
 		return $errorTags !== "" ? "<gissues>".$errorTags."</gissues>" : "";
 	}
 	
 	/**
 	 * Shows missing annotations, i.e. annotations which have a min cardinality of > 0, but do not appear one
 	 * single time in an article. It's an exception because this is the only case where an gardening issue is about something 
 	 * which does not exist.
 	 * 
 	 * @param $issues Gardening issues containing SMW_GARDISSUE_TOO_LOW_CARD with value == 0 (not only)
 	 * 
 	 * @return XML string
 	 */
 	public static function getMissingAnnotations(array & $issues) {
 		$id = uniqid (rand());
 		$count = 0;
 		$missingAnnotations = "";
 		foreach($issues as $i) {
 			// title2 contains the not annotated property in this case
 			if ($i->getType() == SMW_GARDISSUE_TOO_LOW_CARD && intval($i->getValue()) == 0 && $i->getTitle2() !== NULL) {
 				$title = htmlspecialchars($i->getTitle2()->getDBkey()); 
 				
		 		$errorTags = "";
		 		
		 		$value = ""; //$i->getValue() != NULL ? "value=\"".$i->getValue()."\"" : "";
		 		$htmlRepresentation = $i->getRepresentation();
		 		$errorTags .= "<gi type=\"".$i->getType()."\" $value><![CDATA[$htmlRepresentation]]></gi>";
		 				 		
		 		$missingAnnotations .= "<annotation title=\"".$title."\" img=\"property.gif\" id=\"ID_".$id.$count."\"><param>Missing</param><gissues>$errorTags</gissues></annotation>";
 				$count++;
 			}
 		}
 		return $missingAnnotations;
 	}
 }
?>
