<?php

global $IP;
require_once( $IP . "/includes/SpecialPage.php" );

global $smwgHaloIP;
include_once($smwgHaloIP . '/languages/SMW_HaloLanguage.php');

//todo: describe
class SMWDefineWebServiceSpecial extends SpecialPage {

	//todo: describe
	public function __construct() {
		parent::__construct('DefineWebService');
	}
	
	//todo: describe
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
		$html .= "<input id=\"step1-uri\" type=\"text\" size=\"50\" maxlength=\"300\" value=\"http://localhost/halowiki/index.php?action=get_wsdl\"/>";

		$html .= "<img onclick=\"webServiceSpecial.processStep1()\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= "<br>";
		$html .= "</div>";
		

		//2. Specify method
		$html .= "<div id=\"step2\" class=\"StepDiv\" style=\"display: none\">";
		$html .= "<img class=\"Marker\" id=\"step2-img\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= wfMsg("smw_wws_s2-intro");;
		$html .= "<select id=\"step2-methods\" size=\"1\">";
		$html .= "</select>";
		$html .= "<img onclick=\"webServiceSpecial.processStep2()\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= "<br>";
		$html .= "</div>";
		
		//3. Define Parameters
		$html .= "<div id=\"step3\" class=\"StepDiv\" style=\"display: none\">";
		$html .= "<img class=\"Marker\" id=\"step3-img\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= wfMsg("smw_wws_s3-intro");
		$html .= "<table id=\"step3-parameters\"><tr><th>Path:</th><th>Alias: <span style=\"cursor: pointer\" onclick=\"webServiceSpecial.generateParameterAliases()\">generate</span></th><th>Optional:</th><th>Default value:</th><th></th></tr></table>";

		$html .= "<img id=\"step3-ok\" onclick=\"webServiceSpecial.processStep3()\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= "</div>";
		

		// 4. Define Results
		$html .= "<div id=\"step4\" class=\"StepDiv\" style=\"display: none\">";
		$html .= "<img id=\"step4-img\" class=\"Marker\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= wfMsg("smw_wws_s4-intro");
			
		$html .= "<table id=\"step4-results\"><tr><th></th><th>Path:</th><th>Alias: <span style=\"cursor: pointer\" onclick=\"webServiceSpecial.generateResultAliases()\">generate</span></th></tr></table>";

		$html .= "<img id=\"step4-ok\" onclick=\"webServiceSpecial.processStep4()\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= "</div>";
		

		// 5. Define updatae policy
		$html .= "<div id=\"step5\" class=\"StepDiv\" style=\"display: none\" >";
		$html .= "<img id=\"step5-img\" class=\"Marker\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= wfMsg("smw_wws_s5-intro");
		
		$html .= "<table id=\"step5-policies\">";
		
		$html .= "<tr><td><span class=\"OuterLeftIndent\">Display policy: </span></td>";
		$html .= "<td><input id=\"step5-display-once\" checked=\"true\" type=\"radio\" name=\"step5-display\" value=\"once\">Once</input>";
		$html .= "<input id=\"step5-display-max\" type=\"radio\" name=\"step5-display\" value=\"\">MaxAge</input>";

		$html .= "<input type=\"text\" id=\"step5-display-days\" text\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> days </span>";
		$html .= "<input type=\"text\" id=\"step5-display-hours\" text\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> hours </span>";
		$html .= "<input type=\"text\" id=\"step5-display-minutes\" text\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> minutes </span>";
		$html .= "</td></tr>";

		$html .= "<tr><td><span class=\"OuterLeftIndent\">Query policy: </span></td>";
		$html .= "<td><input id=\"step5-query-once\" checked=\"true\" type=\"radio\" name=\"step5-query\" value=\"once\">Once</input>";
		$html .= "<input id=\"step5-query-max\" type=\"radio\" name=\"step5-query\" value=\"\">MaxAge</input>";

		$html .= "<input type=\"text\" id=\"step5-query-days\" text\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> days </span>";
		$html .= "<input type=\"text\" id=\"step5-query-hours\" text\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> hours </span>";
		$html .= "<input type=\"text\" id=\"step5-query-minutes\" text\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> minutes </span>";
		$html .= "</td></tr>";
		
		$html .= "<tr><td></td>";
		$html .= "<td><span> Delay value (seconds): </span>";
		$html .= "<input type=\"text\" id=\"step5-delay\" text\" size=\"7\" maxlength=\"10\" />";
		$html .= "</td></tr></table>";
		
		$html .= "<span class=\"OuterLeftIndent\"> Span of life (in days): </span>";
		$html .= "<input type=\"text\" id=\"step5-spanoflife\" text\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> Expires after update: </span>";
		$html .= "<td><input id=\"step5-expires-yes\" checked=\"true\" type=\"radio\" name=\"step5-expires\" value=\"once\">Yes</input>";
		$html .= "<input id=\"step5-expires-no\" type=\"radio\" name=\"step5-expires\" value=\"\">No</input>";
		$html .= "<img onclick=\"webServiceSpecial.processStep5()\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= "</div>";
//
		// 6. Specify name
		$html .= "<div id=\"step6\" class=\"StepDiv\" style=\"display: none\">";
		$html .= "<img id=\"step6-img\" class=\"Marker\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= wfMsg("smw_wws_s6-intro");
		$html .= "<input id=\"step6-name\" type=\"text\" size=\"50\" maxlength=\"300\"/>";
		$html .= "Save and Finish";
		$html .= "<img onclick=\"webServiceSpecial.processStep6()\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= "<br>";
		$html .= "</div>";
		
		
		//7. show #ws-usage
		$html .= "<div id=\"step7\" style=\"display: none\">";
		$html .= "<span>Your WebService \"";
		$html .= "<span id=\"step7-name\"></span>";
		$html .= "\" has been successfully created. In order to include this WebService into a page, please use the following syntax:</span>"; 
		$html .= "<br><br>";
		$url = Title::makeTitleSafe(NS_SPECIAL, wfMsg('webservicerepository'))->getInternalURL();
		$html .= "<div id=\"step7-container\"></div>";
		$html .= "<br><br>";
		$html .= "<span>Your WebService will from now on be available in <a href=\"".$url."\">the list of available WebServices.</a></span>";
		$html .= "<img onclick=\"webServiceSpecial.processStep7()\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
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