<?php
/*
 * Created on 26.11.2007
 *
 * Author: kai
 */
 
if (!defined('MEDIAWIKI')) die();

define('SMW_FINDWORK_NUMBEROF_RATINGS', 5); // will be doubled (rated and unrated)
 
global $sgagIP;
include_once( "$sgagIP/includes/SGA_GardeningBot.php" );
include_once( "$sgagIP/specials/FindWork/SGA_SuggestStatistics.php" );

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
		$this->store = SMWSuggestStatistics::getStore();
		
		$this->workFields = array(  wfMsg('smw_findwork_select')."...", 
								 	wfMsg('smw_findwork_generalconsistencyissues'),
								 	wfMsg('smw_findwork_missingannotations'),
								 	wfMsg('smw_findwork_nodomainandrange'), 
								 	wfMsg('smw_findwork_instwithoutcat'), 
								 	wfMsg('smw_findwork_categoryleaf'),
								 	wfMsg('smw_findwork_subcategoryanomaly'), 
								 	wfMsg('smw_findwork_undefinedcategory'),
								 	wfMsg('smw_findwork_undefinedproperty'));
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
		$html .= '<input type="hidden" name="title" value="' . $specialPage->getPrefixedText() . '"/>';
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
	
	
	
	function doQuery( $offset, $limit, $shownavigation=true ) {
		global $wgRequest, $wgOut;
		if ($wgRequest->getVal('limit') == NULL) $limit = 20;
		parent::doQuery($offset, $limit, $shownavigation);
		
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
					' <a class="navigationLink" href="'.$gardeningLog->getFullURL('bot='.$bot.'&class=0&pageTitle='.urlencode($result->getPrefixedText())).'">('.wfMsg('smw_findwork_show_details').')</a>';
	    	
	    	
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

