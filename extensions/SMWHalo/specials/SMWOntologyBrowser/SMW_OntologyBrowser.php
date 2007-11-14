<?php
/*
 * Created on 01.03.2007
 * Author: KK
 * 
 */
 if (!defined('MEDIAWIKI')) die();


global $IP;
require_once( "$IP/includes/SpecialPage.php" );

define('SMW_OB_COMMAND_ADDSUBCATEGORY', 1);
define('SMW_OB_COMMAND_ADDSUBCATEGORY_SAMELEVEL', 2);
define('SMW_OB_COMMAND_CATEGORY_RENAME', 3);

define('SMW_OB_COMMAND_ADDSUBPROPERTY', 4);
define('SMW_OB_COMMAND_ADDSUBPROPERTY_SAMELEVEL', 5);
define('SMW_OB_COMMAND_PROPERTY_RENAME', 6);

// standard functions for creating a new special
//function doSMW_OntologyBrowser() {
//		SMW_OntologyBrowser::execute();
//}
	
//SpecialPage::addPage( new SpecialPage(wfMsg('ontologybrowser'),'',true,'doSMW_OntologyBrowser',false) );


class SMW_OntologyBrowser extends SpecialPage {
	
	public function __construct() {
		parent::__construct('OntologyBrowser');
	}
	public function execute() {
		global $wgRequest, $wgOut, $wgScriptPath, $wgUser;
		//$skin = $wgUser->getSkin();
		$wgOut->setPageTitle(wfMsg('ontologybrowser'));
		/*STARTLOG*/
		if ($wgRequest->getVal('src') == 'toolbar') { 
    			smwLog("","OB","opened_from_menu");
		} else if ($wgRequest->getVal('entitytitle') != '') { 
			    $ns = $wgRequest->getVal('ns') == '' ? '' : $wgRequest->getVal('ns').":";
    			smwLog($ns.$wgRequest->getVal('entitytitle'),"Factbox","open_in_OB");
		} else {
				smwLog("","OB","opened");
		}
		/*ENDLOG*/
		$showMenuBar = in_array("gardener", $wgUser->getGroups()) || in_array("sysop", $wgUser->getGroups());
		// display query browser
		$spectitle = Title::makeTitle( NS_SPECIAL, wfMsg('ontologybrowser') );		
	
		$html = "<span id=\"OBHelp\">".wfMsg('smw_ob_help')."</span><br>";
		$html .= "<span style=\"background-color:#F8FAAA;\">".wfMsg('smw_ac_hint') . "</span>\n";
		$html .= "<br><input type=\"text\" size=\"32\" id=\"FilterBrowserInput\" name=\"prefix\" class=\"wickEnabled\"/>";
	
		$html .= "<button type=\"button\" name=\"filterBrowsing\" onclick=\"globalActionListener.filterBrowsing(event, true)\">".wfMsg('smw_ob_filterbrowsing')."</button>";
		$html .= "<button type=\"button\" name=\"refresh\" onclick=\"globalActionListener.reset(event)\">".wfMsg('smw_ob_reset')."</button>";
		$html .= "<button type=\"button\" style=\"margin-left:10px;\" id=\"hideInstancesButton\" name=\"hideInstances\" onclick=\"instanceActionListener.toggleInstanceBox(event)\">".wfMsg('smw_ob_hideinstances')."</button>";
		$html .= "<div id=\"ontologybrowser\">
		
				
		<!-- Categore Tree hook -->	" .
		"<div id=\"treeContainer\"><span class=\"OB-header\">	
			<img src=\"$wgScriptPath/extensions/SMWHalo/skins/concept.gif\"></img><a class=\"selectedSwitch\" id=\"categoryTreeSwitch\" onclick=\"globalActionListener.switchTreeComponent(event,'categoryTree')\" style=\"margin-left:2px;\">".wfMsg('smw_ob_categoryTree')."|</a>
			<img src=\"$wgScriptPath/extensions/SMWHalo/skins/property.gif\"></img><a id=\"propertyTreeSwitch\" onclick=\"globalActionListener.switchTreeComponent(event,'propertyTree')\" style=\"margin-left:2px;\">".wfMsg('smw_ob_attributeTree')."</a>
			</span>".($showMenuBar ? "
			<span class=\"menuBar\" id=\"menuBarConceptTree\"><a style=\"margin-left:5px;\" onclick=\"categoryActionListener.showSubMenu(".SMW_OB_COMMAND_ADDSUBCATEGORY.")\">".wfMsg('smw_ob_cmd_createsubcategory')."</a> | <a onclick=\"categoryActionListener.showSubMenu(".SMW_OB_COMMAND_ADDSUBCATEGORY_SAMELEVEL.")\">".wfMsg('smw_ob_cmd_createsubcategorysamelevel')."</a> | <a onclick=\"categoryActionListener.showSubMenu(".SMW_OB_COMMAND_CATEGORY_RENAME.")\">".wfMsg('smw_ob_cmd_renamecategory')."</a><div id=\"categoryTreeMenu\"></div></span>
			<span style=\"display:none;\" class=\"menuBar\" id=\"menuBarPropertyTree\"><a style=\"margin-left:5px;\" onclick=\"propertyActionListener.showSubMenu(".SMW_OB_COMMAND_ADDSUBPROPERTY.")\">".wfMsg('smw_ob_cmd_createsubproperty')."</a> | <a onclick=\"propertyActionListener.showSubMenu(".SMW_OB_COMMAND_ADDSUBPROPERTY_SAMELEVEL.")\">".wfMsg('smw_ob_cmd_createsubpropertysamelevel')."</a> | <a onclick=\"propertyActionListener.showSubMenu(".SMW_OB_COMMAND_PROPERTY_RENAME.")\">".wfMsg('smw_ob_cmd_renameproperty')."</a><div id=\"propertyTreeMenu\"></div></span>
			" : "")."				
		   <div id=\"categoryTree\" class=\"categoryTreeColors\">
		   </div>		
		   <div id=\"propertyTree\" style=\"display:none\" class=\"propertyTreeListColors\">
		   </div>
		   <span class=\"OB-filters\" style=\"margin-left:5px;margin-top:5px;\"><span style=\"float:left;margin-right:5px;margin-top:5px;\">".wfMsg('smw_ob_filter')."</span><input type=\"text\" name=\"categoryFilter\" style=\"display: block; width: 60%; float:left;margin-top:5px;\"><button type=\"button\" name=\"filterCategories\" onclick=\"globalActionListener.filterTree(event)\" style=\"margin-top:5px;\">".wfMsg('smw_ob_filter')."</button></span>
		</div>
		<!-- Attribute Tree hook -->
				
		<div id=\"leftArrow\" class=\"pfeil\">
			<img style=\"cursor: pointer;\" src=\"$wgScriptPath/extensions/SMWHalo/skins/OntologyBrowser/images/bigarrow.gif\" onclick=\"globalActionListener.toogleCatInstArrow(event)\" />
		</div>
				
		<!-- Instance List hook -->	
		<div id=\"instanceContainer\">
		  <span class=\"OB-header\"><img src=\"$wgScriptPath/extensions/SMWHalo/skins/instance.gif\"></img> ".wfMsg('smw_ob_instanceList')."</span>
		  ".($showMenuBar ? "<span class=\"menuBar\" id=\"menuBarInstance\"><a style=\"margin-left:5px;\" onclick=\"alert('Not implemented yet.')\">".wfMsg('smw_ob_cmd_renameinstance')."</a> | <a onclick=\"alert('Not implemented yet.')\">".wfMsg('smw_ob_cmd_deleteinstance')."</a></span>" : "")."			
		  <div id=\"instanceList\" class=\"instanceListColors\">
		  </div>
		  <span class=\"OB-filters\" style=\"margin-left:5px;\"><span style=\"float:left;margin-right:5px;margin-top:5px;\">".wfMsg('smw_ob_filter')."</span><input type=\"text\" name=\"instanceFilter\" style=\"display: block; width: 60%; float:left;margin-top:5px;\"><button type=\"button\" name=\"filterInstances\" onclick=\"globalActionListener.filterInstances(event)\" style=\"margin-top:5px;\">".wfMsg('smw_ob_filter')."</button></span>
		</div>
			
		<div id=\"rightArrow\" class=\"pfeil\">
			<img style=\"cursor: pointer;\" src=\"$wgScriptPath/extensions/SMWHalo/skins/OntologyBrowser/images/bigarrow.gif\" onclick=\"globalActionListener.toogleInstPropArrow(event)\" />
		</div>
				
		<!-- Relation/Attribute Annotation level hook -->
		<div id=\"relattributesContainer\"><span class=\"OB-header\">
			<span style=\"float:left\"><img src=\"$wgScriptPath/extensions/SMWHalo/skins/property.gif\"></img> ".wfMsg('smw_ob_att')."</span><span id=\"relattValues\" style=\"float:right;text-align:right;\">".wfMsg('smw_ob_relattValues')."</span><span id=\"relattRangeType\" style=\"float:right;text-align:right;display:none;\">".wfMsg('smw_ob_relattRangeType')."</span></span>
			".($showMenuBar ? "<span class=\"menuBar\" id=\"menuBarProperties\"><a style=\"margin-left:5px;\" onclick=\"alert('Not implemented yet.')\">".wfMsg('smw_ob_cmd_addpropertytodomain')."<span id=\"currentSelectedCategory\">...</span></a></span>" : "" )."	
			<div id=\"relattributes\" class=\"propertyTreeListColors\"></div>
			<span class=\"OB-filters\" style=\"margin-left:5px;\"><span style=\"float:left;margin-right:5px;margin-top:5px;\">".wfMsg('smw_ob_filter')."</span><input type=\"text\" size=\"22\" name=\"propertyFilter\" style=\"display: block; width: 60%; float:left;margin-top:5px;\"><button type=\"button\" name=\"filterProperties\" onclick=\"globalActionListener.filterProperties(event)\" style=\"margin-top:5px;\">".wfMsg('smw_ob_filter')."</button></span>		
		</div>		
		<div id=\"OB-filters\">
			" .
			"" .
			"
		</div>" .
		"<div id=\"OB-footer\">".wfMsg('smw_ob_footer')."
			
		</div>
		</div>
		";
		$wgOut->addHTML($html);
	}

}
?>
