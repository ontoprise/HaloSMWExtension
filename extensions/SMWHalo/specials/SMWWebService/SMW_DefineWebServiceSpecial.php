<?php

/*  Copyright 2008, ontoprise GmbH
 *  This file is part of the halo-Extension.
 *
 *   The halo-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The halo-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * This is responsible for the special page define webservice
 *
 * @author Ingo Steinbauer
 *
 */

global $IP;
require_once( $IP . "/includes/SpecialPage.php" );

global $smwgHaloIP;
include_once($smwgHaloIP . '/languages/SMW_HaloLanguage.php');

class SMWDefineWebServiceSpecial extends SpecialPage {


	public function __construct() {
		parent::__construct('DefineWebService');
	}

	/**
	 * This method constructs the special page for defining webservices
	 *
	 */
	public function execute() {
		global $wgRequest, $wgOut;

		$wgOut->setPageTitle("Define Web Service");

		global $smwgHaloScriptPath;

		$html = "";

		//0. menue
		$html .= "<div id=\"menue\">";
		$html .= "<span id=\"menue-step1\" style=\"font-weight: bold\">".wfMsg("smw_wws_s1-menue")."<span class=\"HeadlineDelimiter\"></span></span>";
		$html .= "<span id=\"menue-step2\" >".wfMsg("smw_wws_s2-menue")."<span class=\"HeadlineDelimiter\"></span></span>";
		$html .= "<span id=\"menue-step3\" >".wfMsg("smw_wws_s3-menue")."<span class=\"HeadlineDelimiter\"></span></span>";
		$html .= "<span id=\"menue-step4\">".wfMsg("smw_wws_s4-menue")."<span class=\"HeadlineDelimiter\"></span></span>";
		$html .= "<span id=\"menue-step5\">".wfMsg("smw_wws_s5-menue")."<span class=\"HeadlineDelimiter\"></span></span>";
		$html .= "<span id=\"menue-step6\">".wfMsg("smw_wws_s6-menue")."</span>";
		$html .= "</div>";


		// 1. Specify URI
		$html .= "<br>";
		$html .= "<div id=\"step1\" class=\"StepDiv\" style=\"display: block\">";
		$html .= "<img id=\"step1-img\" class=\"Marker\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= wfMsg("smw_wws_s1-intro");
		$html .= "<input id=\"step1-uri\" type=\"text\" onkeypress=\"webServiceSpecial.checkEnterKey(event, 'step1')\" size=\"50\" maxlength=\"300\" value=\"\"/>";
		$html .= "<sup style=\"color: darkred\"><b>*</b></sup>";
		
		$html .= "<span id=\"step1-go\" class=\"OKButton\">";
		$html .= "<img id=\"step1-go-img\" onclick=\"webServiceSpecial.processStep1()\" src=\"".$smwgHaloScriptPath."/skins/webservices/Control_play.png\" ></img>";
		$html .= "</span>";
		$html .= "<br>";
		$html .= "</div>";


		//2. Specify method
		$html .= "<div id=\"step2\" class=\"StepDiv\" style=\"display: none\">";
		$html .= "<img class=\"Marker\" id=\"step2-img\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= wfMsg("smw_wws_s2-intro");;
		$html .= "<select id=\"step2-methods\" size=\"1\">";
		$html .= "</select>";
		$html .= "<span id=\"step2-go\" class=\"OKButton\">";
		$html .= "<img id=\"step2-go-img\" onclick=\"webServiceSpecial.processStep2()\" src=\"".$smwgHaloScriptPath."/skins/webservices/Control_play.png\" ></img>";
		$html .= "</span>";
		$html .= "<br>";
		$html .= "</div>";

		//3. Define Parameters
		$html .= "<div id=\"step3\" class=\"StepDiv\" style=\"display: none\">";
		$html .= "<img class=\"Marker\" id=\"step3-img\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= wfMsg("smw_wws_s3-intro");
		$html .= "<table id=\"step3-parameters\"><tr><th>Path:</th><th>Alias: <span style=\"cursor: pointer\" onclick=\"webServiceSpecial.generateParameterAliases()\"><img src=\"".$smwgHaloScriptPath."/skins/webservices/Pencil_go.png\"</img></span></th><th>Optional:</th><th>Default value:</th><th></th></tr></table>";

		$html .= "<span id=\"step3-go\" class=\"OKButton\">";
		$html .= "<img id=\"step3-go-img\" onclick=\"webServiceSpecial.processStep3()\" src=\"".$smwgHaloScriptPath."/skins/webservices/Control_play.png\" ></img>";
		$html .= "</span>";
		$html .= "</div>";


		// 4. Define Results
		$html .= "<div id=\"step4\" class=\"StepDiv\" style=\"display: none\">";
		$html .= "<img id=\"step4-img\" class=\"Marker\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= wfMsg("smw_wws_s4-intro");
			
		$html .= "<table id=\"step4-results\"><tr><th>Path:</th><th>Alias: <span style=\"cursor: pointer\" onclick=\"webServiceSpecial.generateResultAliases()\"><img src=\"".$smwgHaloScriptPath."/skins/webservices/Pencil_go.png\"</img></span></th></tr></table>";

		$html .= "<span id=\"step4-go\" class=\"OKButton\">";
		$html .= "<img id=\"step4-go-img\" onclick=\"webServiceSpecial.processStep4()\" src=\"".$smwgHaloScriptPath."/skins/webservices/Control_play.png\" ></img>";
		$html .= "</span>";
		$html .= "</div>";


		// 5. Define updatae policy
		$html .= "<div id=\"step5\" class=\"StepDiv\" style=\"display: none\" >";
		$html .= "<img id=\"step5-img\" class=\"Marker\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= wfMsg("smw_wws_s5-intro");

		$html .= "<table id=\"step5-policies\">";

		$html .= "<tr><td><span class=\"OuterLeftIndent\">Display policy: </span></td>";
		$html .= "<td><input id=\"step5-display-once\" checked=\"true\" onfocus=\"webServiceSpecial.selectRadioOnce('step5-display-once')\" type=\"radio\" name=\"step5-display\" value=\"once\">Once</input>";
		$html .= "<span class=\"OuterLeftIndent\"><input id=\"step5-display-max\" type=\"radio\" name=\"step5-display\" value=\"\">MaxAge</input></span></td>";

		$html .= "<td><input type=\"text\" id=\"step5-display-days\" onfocus=\"webServiceSpecial.selectRadio('step5-display-max')\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> days </span>";
		$html .= "<input type=\"text\" id=\"step5-display-hours\" onfocus=\"webServiceSpecial.selectRadio('step5-display-max')\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> hours </span>";
		$html .= "<input type=\"text\" id=\"step5-display-minutes\" onfocus=\"webServiceSpecial.selectRadio('step5-display-max')\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> minutes </span>";
		$html .= "</td></tr>";

		$html .= "<tr><td><span class=\"OuterLeftIndent\">Query policy: </span></td>";
		$html .= "<td><input id=\"step5-query-once\" checked=\"true\" onfocus=\"webServiceSpecial.selectRadioOnce('step5-query-once')\" type=\"radio\" name=\"step5-query\" value=\"once\">Once</input>";
		$html .= "<span class=\"OuterLeftIndent\"><input id=\"step5-query-max\" type=\"radio\" name=\"step5-query\" value=\"\">MaxAge</input></span></td>";

		$html .= "<td><input type=\"text\" id=\"step5-query-days\" onfocus=\"webServiceSpecial.selectRadio('step5-query-max')\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> days </span>";
		$html .= "<input type=\"text\" id=\"step5-query-hours\" size=\"7\" onfocus=\"webServiceSpecial.selectRadio('step5-query-max')\" maxlength=\"10\" />";
		$html .= "<span> hours </span>";
		$html .= "<input type=\"text\" id=\"step5-query-minutes\" size=\"7\" onfocus=\"webServiceSpecial.selectRadio('step5-query-max')\" maxlength=\"10\" />";
		$html .= "<span> minutes </span>";
		$html .= "</td></tr>";

		$html .= "<tr><td></td>";
		$html .= "<td><span> Delay value (seconds): </span></td>";
		$html .= "<td><input type=\"text\" id=\"step5-delay\" size=\"7\" maxlength=\"10\" />";
		$html .= "</td></tr>";

		$html .= "<tr></tr>";
		
		$html .= "<tr><td><span class=\"OuterLeftIndent\"> Span of life (in days): </span></td>";
		$html .= "<td><input type=\"text\" id=\"step5-spanoflife\" text\" size=\"7\" maxlength=\"10\" /></td>";
		$html .= "<td><span> Expires after update: </span>";
		$html .= "<input id=\"step5-expires-yes\" checked=\"true\" type=\"radio\" name=\"step5-expires\" value=\"once\">Yes</input>";
		$html .= "<input id=\"step5-expires-no\" type=\"radio\" name=\"step5-expires\" value=\"\">No</input>";
		$html .= "<span id=\"step5-go\" class=\"OKButton\">";
		$html .= "<img id=\"step5-go-img\" onclick=\"webServiceSpecial.processStep5()\" src=\"".$smwgHaloScriptPath."/skins/webservices/Control_play.png\" ></img>";
		$html .= "</span>";
		$html .= "</td></tr></table>";
		$html .= "</div>";
		//
		// 6. Specify name
		$html .= "<div id=\"step6\" class=\"StepDiv\" style=\"display: none\">";
		$html .= "<img id=\"step6-img\" class=\"Marker\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= wfMsg("smw_wws_s6-intro");
		$html .= "<input id=\"step6-name\" type=\"text\" onkeypress=\"webServiceSpecial.checkEnterKey(event, 'step6')\" size=\"50\" maxlength=\"300\"/>";
		$html .= "<sup style=\"color: darkred\"><b>*</b></sup>";
		$html .= "<br>";
		$html .= "Save and Finish";
		$html .= "<span id=\"step6-go\" class=\"OKButton\">";
		$html .= "<img id=\"step6-go-img\" onclick=\"webServiceSpecial.processStep6()\" src=\"".$smwgHaloScriptPath."/skins/webservices/Control_play.png\" ></img>";
		$html .= "</span>";
		$html .= "<br>";
		$html .= "</div>";


		//7. show #ws-usage
		$html .= "<div id=\"step7\" style=\"display: none\">";
		$html .= "<span>Your WebService \"";
		$html .= "<span id=\"step7-name\"></span>";
		$html .= "\" has been successfully created. In order to include this WebService into a page, please use the following syntax:</span>";
		$html .= "<br><br>";
		$url = Title::makeTitleSafe(NS_SPECIAL, "webservicerepository")->getInternalURL();
		$html .= "<div id=\"step7-container\"></div>";
		$html .= "<br><br>";
		$html .= "<span>Your WebService will from now on be available in <a href=\"".$url."\">the list of available WebServices.</a></span>";
		$html .= "<img onclick=\"webServiceSpecial.processStep7()\" src=\"".$smwgHaloScriptPath."/skins/webservices/Control_play.png\" class=\"OKButton\"></img>";
		$html .= "</div>";



		//errors
		$html .= "<div id=\"errors\" class=\"StepDiv\" style=\"display: none\">";
		$html .= "<h2>Error</h2>";
		$html .= "<div id=\"step1-error\" style=\"display: none\">".wfMsg("smw_wws_s1-error")."</div>";
		$html .= "<div id=\"step2a-error\" style=\"display: none\">".wfMsg("smw_wws_s2a-error")."</div>";
		$html .= "<div id=\"step2b-error\" style=\"display: none\">".wfMsg("smw_wws_s2b-error")."</div>";
		$html .= "<div id=\"step3-error\" style=\"display: none\">".wfMsg("smw_wws_s3-error")."</div>";
		$html .= "<div id=\"step4-error\" style=\"display: none\">".wfMsg("smw_wws_s4-error")."</div>";
		$html .= "<div id=\"step5-error\" style=\"display: none\">".wfMsg("smw_wws_s5-error")."</div>";
		$html .= "<div id=\"step6-error\" style=\"display: none\">".wfMsg("smw_wws_s6-error")."</div>";
		$html .= "<div id=\"step6b-error\" style=\"display: none\">".wfMsg("smw_wws_s6-error2")."</div>";
		$html .= "</div>";



		// Help
		$html .= "<div id=\"help\" class=\"HelpDiv\">";
		$html .= "<h2>Help</h2>";
		$html .= "<div id=\"step1-help\">".wfMsg("smw_wws_s1-help")."</div>";
		$html .= "<div id=\"step2-help\" style=\"display: none\">".wfMsg("smw_wws_s2-help")."</div>";
		$html .= "<div id=\"step3-help\" style=\"display: none\">".wfMsg("smw_wws_s3-help")."</div>";
		$html .= "<div id=\"step4-help\" style=\"display: none\">".wfMsg("smw_wws_s4-help")."</div>";
		$html .= "<div id=\"step5-help\" style=\"display: none\">".wfMsg("smw_wws_s5-help")."</div>";
		$html .= "<div id=\"step6-help\" style=\"display: none\">".wfMsg("smw_wws_s6-help")."</div>";
		$html .= "</div>";



		$wgOut->addHTML($html);
	}
}
?>