<?php
/**
 * Created on 01.03.2007
 * 
 * @file
 * @ingroup SMWHaloOntologyBrowser
 * 
 * @defgroup SMWHaloOntologyBrowser SMWHalo Ontology Browser
 * @ingroup SMWHaloSpecials
 * 
 * @author Kai K�hn
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

define('SMW_OB_COMMAND_INSTANCE_DELETE', 7);
define('SMW_OB_COMMAND_INSTANCE_RENAME', 8);

define('SMW_OB_COMMAND_ADD_SCHEMAPROPERTY', 9);

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
		$showMenuBar = $wgUser->isAllowed("ontologyediting");
		// display query browser
		//$spectitle = Title::makeTitle( NS_SPECIAL, wfMsg('ontologybrowser') );	
		$refactorstatstitle = Title::makeTitle( NS_SPECIAL, "RefactorStatistics" );
					
	    // add another container
	    $treeContainer = "";
	    $menu = "";
	    $switch = "";
		wfRunHooks('smw_ob_add', array(& $treeContainer, & $boxContainer, & $menu, & $switch));
		
		$html = "<span id=\"OBHelp\">".wfMsg('smw_ob_help')."</span><br>";
		$html .= "<span id=\"OBHint\">".wfMsg('smw_ac_hint') . "</span>\n";
		$html .= "<br><input type=\"text\" size=\"32\" id=\"FilterBrowserInput\" name=\"prefix\" class=\"wickEnabled\" constraints=\"all\"/>";
	
		$html .= "<button type=\"button\" id=\"filterBrowseButton\" name=\"filterBrowsing\" onclick=\"globalActionListener.filterBrowsing(event, true)\">".wfMsg('smw_ob_filterbrowsing')."</button>";
		$html .= "<button type=\"button\" name=\"refresh\" onclick=\"globalActionListener.reset(event)\">".wfMsg('smw_ob_reset')."</button>";
		$html .= "<button type=\"button\" id=\"hideInstancesButton\" name=\"hideInstances\" onclick=\"instanceActionListener.toggleInstanceBox(event)\">".wfMsg('smw_ob_hideinstances')."</button>";//  <a href=\"".$refactorstatstitle->getFullURL()."\">".wfMsg('smw_ob_link_stats')."</a>";
		$html .= "<span id=\"propertyRangeSpan\"><input type=\"checkbox\" id=\"directPropertySwitch\"/>".wfMsg('smw_ob_onlyDirect')."</input><input type=\"checkbox\" id=\"showForRange\"/>".wfMsg('smw_ob_showRange')."</input></span>";
		$html .= "<div id=\"ontologybrowser\">";

//TODO: Add the following code to a hook. However, the problem is, that 
// advancedOption.js should also be moved to the Linked Data Extension, but it
// is used for getting parameters for the ajax calls to smwf_ob_OntologyBrowserAccess().	
		if (defined('LOD_LINKEDDATA_VERSION')) {
			$ids = LODAdministrationStore::getInstance()->getAllSourceDefinitionIDs();
			$sourceOptions = "";
			foreach ($ids as $sourceID) {
				$sourceOptions .= "<option>$sourceID</option>";
			}
			$advancedOptions  = wfMsg("smw_ob_advanced_options");
			$fromWiki         = wfMsg("smw_ob_source_wiki");
			$selectDatasource = wfMsg("smw_ob_select_datasource");
			$html .= <<<TEXT
<div id="advancedOptions" class="advancedOptions">
	<div id="aoFoldIcon" class="aoFoldClosed"> </div>
	<b>$advancedOptions</b>
	<div id="aoContent" class="aoContent">
		<div><b>$selectDatasource</b></div>
		<select id="dataSourceSelector" name="DataSource" size="5" multiple="multiple">
			<option>$fromWiki</option>
			$sourceOptions
		</select>
	</div>
</div>
TEXT;
		}
		$html .= "		
		<!-- Categore Tree hook -->	" .
		"<div id=\"treeContainer\"><span class=\"OB-header\">	
			<img src=\"$wgScriptPath/extensions/SMWHalo/skins/concept.gif\" style=\"margin-bottom: -1px\"></img><a class=\"selectedSwitch treeSwitch\" id=\"categoryTreeSwitch\" onclick=\"globalActionListener.switchTreeComponent(event,'categoryTree')\">".wfMsg('smw_ob_categoryTree')."</a>
			<img src=\"$wgScriptPath/extensions/SMWHalo/skins/property.gif\" style=\"margin-bottom: -1px\"></img><a class=\"treeSwitch\" id=\"propertyTreeSwitch\" onclick=\"globalActionListener.switchTreeComponent(event,'propertyTree')\">".wfMsg('smw_ob_attributeTree')."</a>";
		    $html .= $switch;
		    
		$html .= "</span>";
		if ($showMenuBar) 
		{  
			$html .= "<span class=\"menuBar menuBarcategoryTree\" id=\"menuBarcategoryTree\"><a onclick=\"categoryActionListener.showSubMenu(".SMW_OB_COMMAND_ADDSUBCATEGORY.")\">".wfMsg('smw_ob_cmd_createsubcategory')."</a> | <a onclick=\"categoryActionListener.showSubMenu(".SMW_OB_COMMAND_ADDSUBCATEGORY_SAMELEVEL.")\">".wfMsg('smw_ob_cmd_createsubcategorysamelevel')."</a> | <a onclick=\"categoryActionListener.showSubMenu(".SMW_OB_COMMAND_CATEGORY_RENAME.")\">".wfMsg('smw_ob_cmd_renamecategory')."</a><div id=\"categoryTreeMenu\"></div></span>
			<span style=\"display:none;\" class=\"menuBar menuBarpropertyTree\" id=\"menuBarpropertyTree\"><a onclick=\"propertyActionListener.showSubMenu(".SMW_OB_COMMAND_ADDSUBPROPERTY.")\">".wfMsg('smw_ob_cmd_createsubproperty')."</a> | <a onclick=\"propertyActionListener.showSubMenu(".SMW_OB_COMMAND_ADDSUBPROPERTY_SAMELEVEL.")\">".wfMsg('smw_ob_cmd_createsubpropertysamelevel')."</a> | <a onclick=\"propertyActionListener.showSubMenu(".SMW_OB_COMMAND_PROPERTY_RENAME.")\">".wfMsg('smw_ob_cmd_renameproperty')."</a><div id=\"propertyTreeMenu\"></div></span>";
			$html .= $menu;
		}

		// add containers
	    $html .= "<div id=\"categoryTree\" class=\"categoryTreeColors treeContainer\">
		   </div>		
		   <div id=\"propertyTree\" style=\"display:none\" class=\"propertyTreeListColors treeContainer\">
		   </div>";
		$html .= $treeContainer;
		
		$html .= "<span class=\"OB-filters\"><span>".wfMsg('smw_ob_filter')."</span><input type=\"text\" id=\"treeFilter\"><button type=\"button\" name=\"filterCategories\" onclick=\"globalActionListener.filterTree(event)\">".wfMsg('smw_ob_filter')."</button></span>
		</div>
		
		<!-- Attribute Tree hook -->
				
		<div id=\"leftArrow\" class=\"pfeil\">
			<img src=\"$wgScriptPath/extensions/SMWHalo/skins/OntologyBrowser/images/bigarrow.gif\" onclick=\"globalActionListener.toogleCatInstArrow(event)\" />
		</div>";

		$html .= $boxContainer;
		
		$html .= "<!-- Instance List hook -->	
		<div id=\"instanceContainer\">
		  <span class=\"OB-header\"><img style=\"margin-bottom: -3px\" src=\"$wgScriptPath/extensions/SMWHalo/skins/instance.gif\"></img> ".wfMsg('smw_ob_instanceList')."</span>
		  ".($showMenuBar ? "<span class=\"menuBar menuBarInstance\" id=\"menuBarInstance\"><a onclick=\"instanceActionListener.showSubMenu(".SMW_OB_COMMAND_INSTANCE_RENAME.")\">".wfMsg('smw_ob_cmd_renameinstance')."</a> | <a onclick=\"instanceActionListener.showSubMenu(".SMW_OB_COMMAND_INSTANCE_DELETE.")\">".wfMsg('smw_ob_cmd_deleteinstance')."</a><div id=\"instanceListMenu\"></div></span>" : "")."			
		  <div id=\"instanceList\" class=\"instanceListColors\">
		  </div>
		  <span class=\"OB-filters\"><span>".wfMsg('smw_ob_filter')."</span><input type=\"text\" id=\"instanceFilter\"><button type=\"button\" name=\"filterInstances\" onclick=\"globalActionListener.filterInstances(event)\">".wfMsg('smw_ob_filter')."</button></span>
		</div>
			
		<div id=\"rightArrow\" class=\"pfeil\">
			<img src=\"$wgScriptPath/extensions/SMWHalo/skins/OntologyBrowser/images/bigarrow.gif\" onclick=\"globalActionListener.toogleInstPropArrow(event)\" />
		</div>
				
		<!-- Relation/Attribute Annotation level hook -->
		<div id=\"relattributesContainer\"><span class=\"OB-header\">
			<span><img style=\"margin-bottom: -3px\" src=\"$wgScriptPath/extensions/SMWHalo/skins/property.gif\"></img> ".wfMsg('smw_ob_att')."</span>
			<span id=\"relattValues\">".wfMsg('smw_ob_relattValues')."</span><span id=\"relattRangeType\" style=\"display:none;\">".wfMsg('smw_ob_relattRangeType')."</span></span>
			".($showMenuBar ? "<span class=\"menuBar menuBarProperties\" id=\"menuBarProperties\"><a onclick=\"schemaActionPropertyListener.showSubMenu(".SMW_OB_COMMAND_ADD_SCHEMAPROPERTY.")\">".wfMsg('smw_ob_cmd_addpropertytodomain')."<span id=\"currentSelectedCategory\">...</span></a><div id=\"schemaPropertiesMenu\"></div></span>" : "" )."	
			<div id=\"relattributes\" class=\"propertyTreeListColors\"></div>
			<span class=\"OB-filters\"><span>".wfMsg('smw_ob_filter')."</span><input type=\"text\" size=\"22\" id=\"propertyFilter\"><button type=\"button\" name=\"filterProperties\" onclick=\"globalActionListener.filterProperties(event)\">".wfMsg('smw_ob_filter')."</button></span>		
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

