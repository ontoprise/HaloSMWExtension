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
	public function execute() {
		global $wgRequest, $wgOut, $smwgDIIP;

		require_once($smwgDIIP . '/specials/WebServices/SMW_WSStorage.php');
		$webServices = WSStorage::getDatabase()->getWebServices();
		ksort($webServices);
		$ws = "";
		foreach($webServices as $w){
			$ws .= "<option>".substr($w->getName(),11, strlen($w->getName()))."</option>";
		}


		//todo:use language file
		$wgOut->setPageTitle("Use Web Service");


		global $smwgDIIP, $smwgDIScriptPath;

		$html = "";
		$html .= "<div id=\"menue\" class=\"BreadCrumpContainer\">";
		$html .= "<span id=\"menue-step1\" class=\"ActualMenueStep\">1. Choose web service<span class=\"HeadlineDelimiter\"></span></span>";
		$html .= "<span id=\"menue-step2\" class=\"TodoMenueStep\">2. Define parameters<span class=\"HeadlineDelimiter\"></span></span>";
		$html .= "<span id=\"menue-step3\" class=\"TodoMenueStep\">3. Choose result parts<span class=\"HeadlineDelimiter\"></span></span>";
		$html .= "<span id=\"menue-step4\" class=\"TodoMenueStep\">4. Choose output format<span class=\"HeadlineDelimiter\"></span></span>";
		$html .= "<span id=\"menue-step5\" class=\"TodoMenueStep\">5. Result</span>";
		$html .= "</div>";


		// 1. Choose web service
		$html .= "<br>";
		$html .= "<div id=\"step1\" class=\"StepDiv\" style=\"display: block\">";
		$html .= "<p id=\"step1-head\" class=\"step-headline\">1. Choose web service";
		$html .= "<img id=\"step1-help-img\" class=\"help-image\" onclick=\"useWSSpecial.displayHelp(1)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";
			
		$html .= "<p>Available web services: ";
		$html .= "<select id=\"step1-webservice\" size=\"1\">";
		$html .= $ws."</select>";
		$html .= "</p>";
			
		$html .= "<div id=\"step1-help\" style=\"display:none\">".wfMsg("smw_wsuse_s1-help")."</div>";

		$html .= "<br/>";
		$html .= "<span id=\"step1-go\" class=\"OKButton\">";
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step1-go-img\" value=\"".wfMsg("smw_wsgui_nextbutton")."\" onclick=\"useWSSpecial.processStep1()\">";
		$html .= "</span>";

		$html .= "</div>";



		//2. Define parameters
		$html .= "<div id=\"step2\" class=\"StepDiv\" style=\"display: none\">";

		$html .= "<p class=\"step-headline\">2. Define parameters";
		$html .= "<img id=\"step2-help-img\" class=\"help-image\" onclick=\"useWSSpecial.displayHelp(2)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";

		$html .= "<table id=\"step2-parameters\"><tr><th>Alias:</th><th>Use: <input title=\"".wfMsg("smw_wws_selectall-tooltip")."\" type=\"checkbox\" style=\"text-align: right\" id=\"step2-use\" onclick=\"useWSSpecial.useParameters()\"/></th><th>Value:</th><th>Use default value:</th></tr></table>";
		$html .= "<div id=\"step2-noparameters\">This web service does not require any parameters.</div>";
		
		$html .= "<div id=\"step2-help\" style=\"display:none\">".wfMsg("smw_wsuse_s2-help")."</div>";

		$html .= "<br/>";
		$html .= "<span id=\"step2-go\" class=\"OKButton\">";
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step2-go-img\" value=\"".wfMsg("smw_wsgui_nextbutton")."\" onclick=\"useWSSpecial.processStep2()\">";
		$html .= "</span>";

		$html .= "</div>";

		//3. Choose result parts
		$html .= "<div id=\"step3\" class=\"StepDiv\" style=\"display: none\">";

		$html .= "<p class=\"step-headline\">3. Choose result parts";
		$html .= "<img id=\"step3-help-img\" class=\"help-image\" onclick=\"useWSSpecial.displayHelp(3)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";

		$html .= "<table id=\"step3-results\"><tr><th>Alias:</th><th>Use: <input title=\"".wfMsg("smw_wws_selectall-tooltip")."\" type=\"checkbox\" style=\"text-align: right\" id=\"step3-use\" onclick=\"useWSSpecial.useResults()\"/></th></tr></table>";
		$html .= "<div id=\"step3-noresults\">This web service does not provide any result parts.</div>";

		$html .= "<div id=\"step3-help\" style=\"display:none\">".wfMsg("smw_wsuse_s3-help")."</div>";

		$html .= "<br/>";
		$html .= "<span id=\"step3-go\" class=\"OKButton\">";
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step3-go-img\" value=\"".wfMsg("smw_wsgui_nextbutton")."\" onclick=\"useWSSpecial.processStep3()\">";
		$html .= "</span>";

		$html .= "</div>";

		// 4. Choose output format
		$html .= "<br>";
		$html .= "<div id=\"step4\" class=\"StepDiv\" style=\"display: none\">";
		$html .= "<p class=\"step-headline\">4. Choose output format";
		$html .= "<img id=\"step4-help-img\" class=\"help-image\" onclick=\"useWSSpecial.displayHelp(4)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";
			
		$html .= "<p>Available formats: ";
		$html .= "<select id=\"step4-format\" size=\"1\" onchange=\"useWSSpecial.updateStep4Widgets()\">";
		$html .= "<option>list</option>";
		$html .= "<option>ol</option>";
		$html .= "<option>ul</option>";
		$html .= "<option>table</option>";
		$html .= "<option>template</option>";
		$html .= "<option>tixml</option>";
		$html .= "<option>transposed</option>";
		$html .= "</select>";
		$html .= "</p>";

		$html .= "<p id=\"step4-template-container\">Template: ";
		$html .= "<input id=\"step4-template\"></input> ";
		$html .= "</p>";

		//todo:separator???

		$html .= "<div id=\"step4-help\" style=\"display:none\">".wfMsg("smw_wsuse_s4-help")."</div>";

		$html .= "<br/>";
		$html .= "<span id=\"step4-go\" class=\"OKButton\">";
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step4-go-img\" value=\"".wfMsg("smw_wsgui_nextbutton")."\" onclick=\"useWSSpecial.processStep4()\">";
		$html .= "</span>";

		$html .= "</div>";


		$html .= "<br>";
		$html .= "<div id=\"step5\" class=\"StepDiv\" style=\"display: none\">";
		$html .= "<p class=\"step-headline\">5. Result";
		$html .= "<img id=\"step5-help-img\" class=\"help-image\" onclick=\"useWSSpecial.displayHelp(5)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";
			
		$html .= "<p>";
		$html .= "<span id=\"step5-preview-button\"><input type=\"button\" onclick=\"useWSSpecial.getPreview()\" value=\"Display preview\" id=\"step5-preview-button-img\"></input></span>";
		$html .= "<input type=\"button\" onclick=\"useWSSpecial.displayWSSyntax()\" value=\"Display #ws-syntax\"></input>";
		$html .= "<input type=\"button\" onclick=\"useWSSpecial.addToArticle()\" value=\"Add call to <articlename>\" id=\"step5-add\" style=\"display: none\"></input>";
		$html .= "</p>";

		$html .= "<div id=\"step5-help\" style=\"display:none\">".wfMsg("smw_wsuse_s5-help")."</div>";

		$html .= "<div id=\"step5-preview\" style=\"display: none; border-width: 2px; border-style: solid; border-color: #5d5d5d\"></div>";

		$html .= "</div>";

		$wgOut->addHTML($html);
	}
}
?>