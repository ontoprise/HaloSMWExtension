<?php
/*
 * Created on 26.11.2007
 *
 * Author: kai
 */
 
if (!defined('MEDIAWIKI')) die();

define('SMW_FINDWORK_NUMBEROF_RATINGS', 5); // will be doubled (rated and unrated)
 
global $smwgHaloIP;
include_once( "$smwgHaloIP/specials/SMWGardening/SMW_GardeningBot.php" );


function smwfDoSpecialFindWorkPage() {
	wfProfileIn('smwfDoSpecialFindWorkPage (SMW Halo)');
	list( $limit, $offset ) = wfCheckLimits();
	$rep = new SMWFindWork();
	$result = $rep->doQuery( $offset, $limit );
	wfProfileOut('smwfDoSpecialFindWorkPage (SMW Halo)');
	return $result;
}
 class SMWFindWork extends SMWQueryPage {
	
	private $workFields;
	private $store;
	
	public function __construct() {
		global $smwgDefaultStore, $smwgHaloIP, $wgUser;
		switch ($smwgDefaultStore) {
			case (SMW_STORE_TESTING):
				$this->store = null; // not implemented yet
				trigger_error('Testing stores not implemented for HALO extension.');
			break;
			case (SMW_STORE_MWDB): default:
				require_once($smwgHaloIP . '/specials/SMWFindWork/SMW_SuggestStatisticsSQL.php');
				$this->store = new SMWSuggestStatisticsSQL();
			break;
		}
		
		$this->workFields = array(  wfMsg('smw_findwork_select')."...", 
								 	wfMsg('smw_findwork_generalconsistencyissues'),
								 	wfMsg('smw_findwork_missingannotations'),
								 	wfMsg('smw_findwork_nodomainandrange'), 
								 	wfMsg('smw_findwork_instwithoutcat'), 
								 	wfMsg('smw_findwork_categoryleaf'),
								 	wfMsg('smw_findwork_subcategoryanomaly'), 
								 	wfMsg('smw_findwork_undefinedcategory'),
								 	wfMsg('smw_findwork_undefinedproperty'), 
								 	wfMsg('smw_findwork_lowratedannotations'));
	}
	
		
	
	function getName() {
		return "FindWork";
	}

	function isExpensive() {
		return false;
	}

	function isSyndicated() { return false; }

	function getPageHeader() {
		global $wgRequest, $wgUser;
		
		$field_val = $wgRequest->getVal("field") != NULL ? intval($wgRequest->getVal("field")) : 0;
		$html = "";
		if ($wgUser->isAnon()) {
			$html .= "<p class=\"warning\">".wfMsg('smw_findwork_user_not_loggedin')."</p>";
		}
		$html .= '<p>' . wfMsg('smw_findwork_docu') . "</p>\n";
		$specialPage = Title::newFromText($this->getName(), NS_SPECIAL);
		$html .= "<form action=\"".$specialPage->getFullURL()."\">";
		$html .= wfMsg('smw_findwork_header', "<input  name=\"gswButton\" type=\"submit\" value=\"".wfMsg('smw_findwork_getsomework')."\"/>");
				
		$html .= "<select name=\"field\">";
		
		$i = 0;
		foreach($this->workFields as $field) {
			if ($i == $field_val) {
		 		$html .= "<option value=\"".$i."\" selected=\"selected\">".$field."</option>";
			} else {
				$html .= "<option value=\"".$i."\">".$field."</option>";
			}
			$i++;
		}
 		$html .= "</select>" .
 				"<input name=\"goButton\" type=\"submit\" value=\"Go\"/></form>";	
 		if ($wgRequest->getVal("gswButton") == NULL) {
 			if ($field_val !== 0) $html .= '<h2>' . $this->workFields[$field_val] . "</h2>\n";
 			
 		} else {
			$html .= "<h2>".wfMsg('smw_findwork_heresomework')."</h2>\n";
 		}
		return $html;
	}
	
	private function getPageBottom() {
		
		$html = wfMsg('smw_findwork_rateannotations');
		$html .= '<form id="ratingform"><table id="rateannotations" border="0" cellspacing="0" rowspacing="0">';
		
		// get some rated and unrated annotations
		$annotations = smwfGetSemanticStore()->getAnnotationsForRating(SMW_FINDWORK_NUMBEROF_RATINGS, true);
		$annotations = array_merge(smwfGetSemanticStore()->getAnnotationsForRating(SMW_FINDWORK_NUMBEROF_RATINGS, false), $annotations);
		$i = 0;
		foreach($annotations as $a) {
			$html .= '<tr id="annotation'.$i.'">';
			$html .= '<td>'.str_replace("_", " ", $a[0]).'</td>';
			$html .= '<td>'.str_replace("_", " ", $a[1]).'</td>';
			$html .= '<td>'.str_replace("_", " ", $a[2]).'</td>';
			$html .= '<td class="ratesection"><input type="radio" name="rating'.$i.'" value="1" class="yes">'.wfMsg('smw_findwork_yes').'</input>' .
						  '<input type="radio" name="rating'.$i.'" value="-1" class="no">'.wfMsg('smw_findwork_no').'</input>' .
						  '<input type="radio" name="rating'.$i.'" value="0" checked="checked" class="dontknow">'.wfMsg('smw_findwork_dontknow').'</input>' .
					 '</td>';
			$html .= '</tr>';
			$i++;
		}
		$html .= '</table></form>';
		$html .= '<br><input type="button" name="rate" id="sendbutton" value="'.wfMsg('smw_findwork_sendratings').'" onclick="findwork.sendRatings()"/>';
		return $html;
	}
	
	function doQuery( $offset, $limit, $shownavigation=true ) {
		global $wgRequest, $wgOut;
		if ($wgRequest->getVal('limit') == NULL) $limit = 20;
		parent::doQuery($offset, $limit, $shownavigation);
		$wgOut->addHTML($this->getPageBottom());
	}
	
	function linkParameters() {
		global $wgRequest;
		$field = $wgRequest->getVal("field") == NULL ? '' : $wgRequest->getVal("field");
		$gswButton = $wgRequest->getVal("gswButton") == NULL ? '' : $wgRequest->getVal("gswButton");
		$goButton = $wgRequest->getVal("goButton") == NULL ? '' : $wgRequest->getVal("goButton");
		return array('field' => $field, 'gswButton' => $gswButton, 'goButton' => $goButton);
	}
	
	function sortDescending() {
		return false;
	}

	function formatResult( $skin, $result ) {
		global $wgRequest;
		$field = $wgRequest->getVal("field");
	    if ($result instanceof Title) {
	    	
	    		// default display	
	    		$gardeningLog = Title::newFromText("GardeningLog", NS_SPECIAL);
	    		list($bot, $type, $class) = $this->getBotClassAndType($field);
	    		return $skin->makeLinkObj($result).
					' <a class="navigationLink" href="'.$gardeningLog->getFullURL().'?bot='.$bot.'&class=0&pageTitle='.urlencode($result->getPrefixedText()).'">('.wfMsg('smw_findwork_show_details').')</a>';
	    	
	    	
	    } else {
	    	global $wgServer, $wgScriptPath;
	    	if ($field == 9) { 
	    		// special case for low rate annoations 
	    		list($subject, $property, $object) = $result;
	    		$subjectEscaped = htmlspecialchars($subject->getDBkey());
	    		$objectValue = $object instanceof Title ? $skin->makeLinkObj($object) : $object;
	    		return $skin->makeLinkObj($subject).'<img class="clickable" src="'.$wgServer.$wgScriptPath.'/extensions/SMWHalo/skins/info.gif" onclick="findwork.toggle(\''.$subjectEscaped.'\')"/>' .
	    				'<div class="findWorkDetails" style="display:none;" id="'.$subjectEscaped.'">'.$skin->makeLinkObj($property).' with value '.$objectValue.'</div>';
	    	} 
	    }
	    // if no title: return a helpful error message
	    return '__undefined_object__: "'.$result.'" of class: '.get_class($result);
	}

	function getResults($options) {
		global $wgRequest, $wgUser;
		$loggedIn = $wgUser != NULL && $wgUser->isLoggedIn();
		$somework = $wgRequest->getVal("gswButton");
		$go = $wgRequest->getVal("goButton");
		$field = $wgRequest->getVal("field");
		
		$results = array();
		if ($somework != NULL) {
			// show arbitrary work. Consider edit history if user is logged in
			$results = $this->store->getLastEditedPages(NULL, NULL, NULL, $loggedIn ? $wgUser->getName() : NULL, $options);
			if (count($results) == 0) {
				$results = $this->store->getLastEditedPages(NULL, NULL, NULL, NULL, $options);
			}
			return $results;
		} else if ($go != NULL) {
			list($botID, $gi_class, $gi_type) = $this->getBotClassAndType($field);
			$username = $loggedIn ? $wgUser->getName() : NULL;
			switch($field) {
				case 0: break;
				case 1: // fall through
				case 2: // fall through
				case 3: // fall through
				case 4: // fall through
				case 5: // fall through
				case 6: { // show some work of given type. Consider edit history if user is logged in
							$results = $this->store->getLastEditedPages($botID, $gi_class, $gi_type, $username, $options);
							if (count($results) == 0) {
								// show some work of given type. Consider categories of articles of edit history. 
								$results = $this->store->getLastEditedPagesOfSameCategory($botID, $gi_class, $gi_type, $username, $options);
								if (count($results) == 0) {
									 // show some work. Very unspecific
									 $results = $this->store->getLastEditedPages(NULL, NULL, NULL, NULL, $options);
								}
							}	
				 			break;
						}
				case 7: { $results = $this->store->getLastEditPagesOfUndefinedCategories($username, $options);
					 	  if (count($results) == 0) {
							 // show some work. Very unspecific
							 $results = $this->store->getLastEditedPages(NULL, NULL, SMW_GARDISSUE_CATEGORY_UNDEFINED, NULL, $options);
						  } 
						  	break;
						 }
				case 8: { $results = $this->store->getLastEditPagesOfUndefinedProperties($username, $options);
					 	  if (count($results) == 0) {
							 // show some work. Very unspecific
							 $results = $this->store->getLastEditedPages(NULL, NULL, SMW_GARDISSUE_PROPERTY_UNDEFINED, NULL, $options);
						  } 
						  	break;
						 }
				case 9: {
							$results = $this->store->getLowRatedAnnotations($username, $options);
							if (count($results) == 0) {
							 	// show some work. Very unspecific
							 	$results = $this->store->getLowRatedAnnotations(NULL, $options);
						  	} 
						  	break;
						}
			}
			
		}
		return $results;
	}
	
	private function getBotClassAndType($field) {
		switch($field) {
			case 0: return array(NULL, NULL, NULL);
			case 1: return array('smw_consistencybot', NULL, NULL);
			case 2: return array('smw_consistencybot', NULL, SMW_GARDISSUE_TOO_LOW_CARD);
			case 3: return array('smw_consistencybot', SMW_CONSISTENCY_BOT_BASE + 1, NULL);  // SMW_CONSISTENCY_BOT_BASE + 1 is group of undefined domains/ranges/types		
			case 4: return array('smw_undefinedentitiesbot', NULL, SMW_GARDISSUE_INSTANCE_WITHOUT_CAT);
			case 5: return array('smw_anomaliesbot', NULL, SMW_GARDISSUE_CATEGORY_LEAF);
			case 6: return array('smw_anomaliesbot', NULL, SMW_GARDISSUE_SUBCATEGORY_ANOMALY);
			case 7: return array('smw_undefinedentitiesbot', NULL, SMW_GARDISSUE_CATEGORY_UNDEFINED);
			case 8: return array('smw_undefinedentitiesbot', NULL, SMW_GARDISSUE_PROPERTY_UNDEFINED);	
			case 9: return array(NULL, NULL, NULL);
		}
	}
 }
?>
