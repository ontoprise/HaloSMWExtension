<?php

/*  Copyright 2008, ontoprise GmbH
 *  This file is part of the Data Import-Extension.
 *
 *   The Data Import-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Data Import-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup DIWebServices
 * 
 * @author Ingo Steinbauer
 */

/**
 * This class is responsible for the special page use webservice
 *
 * @author Ingo Steinbauer
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) die;
global $IP;
require_once( $IP . "/includes/SpecialPage.php" );

global $smwgDIIP, $smwgDIScriptPath;
include_once($smwgDIIP . '/languages/SMW_DILanguage.php');

class SMWUseWebServiceSpecial extends SpecialPage {

	public function __construct() {
		parent::__construct('UseWebService');
	}

	/**
	 * This method constructs the special page for defining webservices
	 *
	 */
	public function execute($par) {
		global $wgRequest, $wgOut, $smwgDIIP;

		require_once($smwgDIIP . '/specials/WebServices/SMW_WSStorage.php');
		$webServices = WSStorage::getDatabase()->getWebServices();
		ksort($webServices);
		$ws = "";
		
		//todo:use language file
		$wgOut->setPageTitle("Use Web Service");
		
		if(count($webServices) == 0){
			$url = Title::makeTitleSafe(NS_SPECIAL, "DefineWebService")->getInternalURL()."?wwsdId=".$this->getTitle()->getArticleID();$url = Title::makeTitleSafe(NS_SPECIAL, "DefineWebService")->getInternalURL();
			$html = "No Wiki Web Service Definitions are available yet. Please go to the special page <a href=\"".$url."\">Special:DefineWebService</a> and define some first.";
			$wgOut->addHTML($html);
			return;
		}
		
		$first = true;
		$firstTriplifyable = ' style="display: none" ';
		$step5BCLabel = str_replace("6.", "5.", wfMsg('smw_wwsu_menue-s5'));
		foreach($webServices as $w){
			if(strlen($w->getTriplificationSubject()) > 0 && defined( 'LOD_LINKEDDATA_VERSION')){
				$triplifyable = ' class="triplifyable" ' ;
				if($first){
					$firstTriplifyable = '';
					$step5BCLabel = str_replace("5.", "6.", wfMsg('smw_wwsu_menue-s5'));
				}
			} else {
				$triplifyable = ' class="not triplifyable" ' ;
			}
			$first = false;
			$ws .= "<option ".$triplifyable." value=\"".substr($w->getName(),11, strlen($w->getName()))."\">".substr($w->getName(),11, strlen($w->getName()))."</option>";
		}

		global $smwgDIIP, $smwgDIScriptPath;

		$html = "";
		$html .= "<div id=\"menue\" class=\"BreadCrumpContainer\">";
		$html .= "<span id=\"menue-step1\" class=\"ActualMenueStep\">".wfMsg('smw_wwsu_menue-s1')."</span><span class=\"HeadlineDelimiter\"></span>";
		$html .= "<span id=\"menue-step2\" class=\"TodoMenueStep\">".wfMsg('smw_wwsu_menue-s2')."</span><span class=\"HeadlineDelimiter\"></span>";
		$html .= "<span id=\"menue-step3\" class=\"TodoMenueStep\">".wfMsg('smw_wwsu_menue-s3')."</span><span class=\"HeadlineDelimiter\"></span>";
		$html .= "<span id=\"menue-step4\" class=\"TodoMenueStep\">".wfMsg('smw_wwsu_menue-s4')."</span><span class=\"HeadlineDelimiter\"></span>";
		if(defined( 'LOD_LINKEDDATA_VERSION')){
			$html .= "<span id=\"menue-step6\" ".$firstTriplifyable." class=\"TodoMenueStep\">".wfMsg('smw_wwsu_menue-s6')."</span><span class=\"HeadlineDelimiter\"></span>";
			$html .= "<span id=\"menue-step5\" class=\"TodoMenueStep\">".$step5BCLabel."</span>";
		} else {
			$html .= "<span id=\"menue-step5\" class=\"TodoMenueStep\">".str_replace("6.", "5.", wfMsg('smw_wwsu_menue-s5'))."</span>";
		}
		$html .= "</div>";


		// 1. Choose web service
		$html .= "<br>";
		$html .= "<div id=\"step1\" class=\"StepDiv\" style=\"display: block\">";
		$html .= "<p id=\"step1-head\" class=\"step-headline\">".wfMsg('smw_wwsu_menue-s1');
		$html .= "<img id=\"step1-help-img\" class=\"help-image\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" onclick=\"useWSSpecial.displayHelp(1)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";
			
		$html .= "<p>"	.wfMsg('smw_wwsu_availablews');
		$html .= "<select id=\"step1-webservice\" size=\"1\" onchange=\"useWSSpecial.displayTriplificationOptions()\">";
		$html .= $ws."</select>";
		$html .= "</p>";
			
		$html .= "<div id=\"step1-help\" class=\"WSHLPMSG\" style=\"display:none\">".wfMsg("smw_wsuse_s1-help")."</div>";

		$html .= "<br/>";
		$html .= "<span id=\"step1-go\" class=\"OKButton\">";
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step1-go-img\" value=\"".wfMsg("smw_wsgui_nextbutton")."\" onclick=\"useWSSpecial.processStep1()\">";
		$html .= "</span>";

		$html .= "</div>";



		//2. Define parameters
		$html .= "<div id=\"step2\" class=\"StepDiv\" style=\"display: none\">";

		$html .= "<p class=\"step-headline\">".wfMsg('smw_wwsu_menue-s2');
		$html .= "<img id=\"step2-help-img\" class=\"help-image\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" onclick=\"useWSSpecial.displayHelp(2)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";

		$html .= "<table id=\"step2-parameters\"><tr><th>".wfMsg('smw_wwsu_alias')."</th><th id=\"step2-use-label\" style=\"visibility: hidden\">".wfMsg('smw_wwsu_use')."<span onclick=\"useWSSpecial.useParameters()\"><input title=\"".wfMsg("smw_wws_selectall-tooltip")."\" type=\"checkbox\" style=\"text-align: right\" id=\"step2-use\"/></span></th><th>".wfMsg('smw_wwsu_value')."</th><th>".wfMsg('smw_wwsu_defaultvalue')."</th></tr></table>";
		$html .= "<div id=\"step2-noparameters\">".wfMsg("smw_wwsu_noparameters")."</div>";
		
		$html .= "<div id=\"step2-help\" class=\"WSHLPMSG\" style=\"display:none\">".wfMsg("smw_wsuse_s2-help")."</div>";

		$html .= "<br/>";
		$html .= "<span id=\"step2-go\" class=\"OKButton\">";
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step2-go-img\" value=\"".wfMsg("smw_wsgui_nextbutton")."\" onclick=\"useWSSpecial.processStep2()\">";
		$html .= "</span>";

		$html .= "</div>";

		//3. Choose result parts
		$html .= "<div id=\"step3\" class=\"StepDiv\" style=\"display: none\">";

		$html .= "<p class=\"step-headline\">".wfMsg('smw_wwsu_menue-s3');
		$html .= "<img id=\"step3-help-img\" class=\"help-image\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" onclick=\"useWSSpecial.displayHelp(3)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";

		$html .= "<table id=\"step3-results\"><tr><th>".wfMsg('smw_wwsu_alias')."</th><th>".wfMsg('smw_wwsu_use')."<span onclick=\"useWSSpecial.useResults()\"><input title=\"".wfMsg("smw_wws_selectall-tooltip")."\" type=\"checkbox\" style=\"text-align: right\" id=\"step3-use\"/></span></th><th>".wfMsg('smw_wwsu_label')."</th</tr></table>";
		$html .= "<div id=\"step3-noresults\">".wfMsg('smw_wwsu_noresults')."</div>";

		$html .= "<div id=\"step3-help\" class=\"WSHLPMSG\" style=\"display:none\">".wfMsg("smw_wsuse_s3-help")."</div>";

		$html .= "<br/>";
		$html .= "<span id=\"step3-go\" class=\"OKButton\">";
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step3-go-img\" value=\"".wfMsg("smw_wsgui_nextbutton")."\" onclick=\"useWSSpecial.processStep3()\">";
		$html .= "</span>";

		$html .= "</div>";

		// 4. Choose output format
		$html .= "<br>";
		$html .= "<div id=\"step4\" class=\"StepDiv\" style=\"display: none\">";
		$html .= "<p class=\"step-headline\">".wfMsg('smw_wwsu_menue-s4');
		$html .= "<img id=\"step4-help-img\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"useWSSpecial.displayHelp(4)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";
			
		// format selector
		$html .= "<p>".wfMsg('smw_wwsu_availableformats');
		$html .= "<select id=\"step4-format\" size=\"1\" onchange=\"useWSSpecial.updateStep4Widgets()\">";
		$html .= "<option value=\"list\">list</option>";
		$html .= "<option value=\"ol\">ol</option>";
		$html .= "<option value=\"ul\">ul</option>";
		$html .= "<option value=\"table\">table</option>";
		$html .= "<option value=\"broadtable\">broadtable</option>";
		$html .= "<option value=\"count\">count</option>";
		
		$html .= "<option value=\"ofc-pie\">ofc-pie</option>";
		$html .= "<option value=\"ofc-bar\">ofc-bar</option>";
		$html .= "<option value=\"ofc-bar-3d\">ofc-bar-3d</option>";
		$html .= "<option value=\"ofc-line\">ofc-line</option>";
		
		$html .= "<option value=\"sum\">sum</option>";
		$html .= "<option value=\"average\">average</option>";
		$html .= "<option value=\"min\">min</option>";
		$html .= "<option value=\"max\">max</option>";
		
		$html .= "<option value=\"simpletable\">simpletable</option>";
		$html .= "<option value=\"template\">template</option>";
		$html .= "<option value=\"tixml\">tixml</option>";
		$html .= "<option value=\"transposed\">transposed</option>";
		$html .= "<option value=\"csv\">csv</option>";
		$html .= "</select>";
		$html .= "</p>";
		
		//sorting
		$html .= '<p>'.wfMsg('smw_wwsu_sort');
		$html .= '<input id="step4-sort-checkbox" type="checkbox" onchange="useWSSpecial.displaySortDetails()"/>';
		$html .= '<span id="step4-sort-details" style="display: none">';
		$html .= '<span class="step4-format-labels">'.wfMsg('smw_wwsu_sort_by').'</span>';
		$html .= '<select id="step4-sort-column"></select>';
		$html .= '<span class="step4-format-labels">'.wfMsg('smw_wwsu_sort_order').'</span>';
		$html .= '<select id="step4-sort-order">';
		$html .= "<option value=\"".wfMsg('smw_wwsu_sort_order_asc')."\">".wfMsg('smw_wwsu_sort_order_asc')."</option>";
		$html .= "<option value=\"".wfMsg('smw_wwsu_sort_order_desc')."\">".wfMsg('smw_wwsu_sort_order_desc')."</option>";
		$html .= '</select>';
		$html .= "</sption>";
		$html .= '</p>';
		
		//template
		$html .= "<p id=\"step4-template-container\">Template: ";
		$html .= "<input class=\"wickEnabled\" constraints = \"namespace: ".NS_TEMPLATE."\" id=\"step4-template\"></input> ";
		$html .= "</p>";
		
		//limit
		global $smwgQMaxInlineLimit;
		$html .= '<p>'.wfMsg('smw_wwsu_limit');
		$html .= '<input id="step4-limit" value="'.$smwgQMaxInlineLimit.'" size="3"/>';
		$html .= '<span class="step4-format-labels">'.wfMsg('smw_wwsu_offset').'</span>';
		$html .= '<input id="step4-offset" value="0" size="3"/>';
		$html .= '</p>';

		$html .= "<div id=\"step4-help\" class=\"WSHLPMSG\" style=\"display:none\">".wfMsg("smw_wsuse_s4-help")."</div>";

		$html .= "<br/>";
		$html .= "<span id=\"step4-go\" class=\"OKButton\">";
		if(defined( 'LOD_LINKEDDATA_VERSION')){
			$s4OnClick = 'useWSSpecial.processStep6()';
		} else {
			$s4OnClick = 'useWSSpecial.processStep4()';
		}
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step4-go-img\" value=\"".wfMsg("smw_wsgui_nextbutton")."\" onclick=\"".$s4OnClick."\">";
		$html .= "</span>";

		$html .= "</div>";

		
		
		//step 4.5 aka 6 Triplification
		if(defined( 'LOD_LINKEDDATA_VERSION')){
			$html .= "<div id=\"step6\" class=\"StepDiv\" style=\"display: none\">";
			$html .= "<br>";
			$html .= "<p class=\"step-headline\">".wfMsg('smw_wwsu_menue-s6');
			$html .= "<img id=\"step6-help-img\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"useWSSpecial.displayHelp(6)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
			$html .= "</p>";
	
			//$html .= '<p id="step6-missing-subjects">'.wfMsg('smw_wwsu_triplify_impossible').'</p>';
			
			$html .= '<span id="step6-triplification-container">';
			
			$html .= "<p>".wfMsg('smw_wwsu_triplify');
			$html .= "<input type=\"checkbox\" id=\"step6-triplify\" selected=\"false\"/>";
			$html .= "</p>";
	
			$html .= "<p>".wfMsg('smw_wwsu_triplify_subject_display');
			$html .= "<input type=\"checkbox\" id=\"step6-display-subjects\" selected=\"false\" onchange=\"useWSSpecial.displayTriplificationSubjectAlias()\"/>";
			$html .= '<span id="step6-subject-alias-container" style="display: none">';
			$html .= '<span>'.wfMsg('smw_wwsu_triplify_subject_alias').'</span>';
			$html .= "<input id=\"step6-subject-alias\" value=\"".wfMsg('smw_wwsu_triplify_subject_alias_value')."\"></input> ";
			$html .= "</span>";
			$html .= "</p>";
			
			$html .= "</span>";
	
			$html .= "<div id=\"step6-help\" class=\"WSHLPMSG\" style=\"display:none\">".wfMsg("smw_wsuse_s6-help")."</div>";

			$html .= "<br/>";
			$html .= "<span id=\"step6-go\" class=\"OKButton\">";
			$html .= "<input type=\"button\" class=\"OKButton\" id=\"step6-go-img\" value=\"".wfMsg("smw_wsgui_nextbutton")."\" onclick=\"useWSSpecial.processStep4()\">";
			$html .= "</span>";
			
			$html .= "</div>";
		}
		
		//step 5
		$html .= "<br>";
		$html .= "<div id=\"step5\" class=\"StepDiv\" style=\"display: none\">";
		$html .= "<p class=\"step-headline\">".wfMsg('smw_wwsu_menue-s5');
		$html .= "<img id=\"step5-help-img\" class=\"help-image\" onclick=\"useWSSpecial.displayHelp(5)\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";
			
		$html .= "<p>";
		$html .= "<span id=\"step5-preview-button\"><input type=\"button\" onclick=\"useWSSpecial.getPreview()\" value=\"".wfMsg('smw_wwsu_displaypreview')."\" id=\"step5-preview-button-img\"></input></span>";
		$html .= "<input id=\"displayWSButton\" type=\"button\" onclick=\"useWSSpecial.displayWSSyntax()\" value=\"".wfMsg('smw_wwsu_displaywssyntax')."\"></input>";
		$html .= "<input id=\"copyWSButton\" type=\"button\" onclick=\"useWSSpecial.copyToClipBoard()\" value=\"".wfMsg('smw_wwsu_copytoclipboard')."\"></input>";
		$html .= "<input type=\"button\" onclick=\"useWSSpecial.addToArticle()\" value=\"".wfMsg('smw_wwsu_addcall')."\" id=\"step5-add\" style=\"display: none\"></input>";
		$html .= "</p>";

		$html .= "<div id=\"step5-help\" class=\"WSHLPMSG\" style=\"display:none\">".wfMsg("smw_wsuse_s5-help")."</div>";

		$html .= "<div id=\"step5-preview\" style=\"display: none; border-width: 2px; border-style: solid; border-color: #5d5d5d\"></div>";

		$html .= "</div>";

		$wgOut->addHTML($html);
	}
}