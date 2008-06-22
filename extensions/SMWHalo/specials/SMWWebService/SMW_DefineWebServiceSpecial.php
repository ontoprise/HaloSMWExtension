<?php

global $IP;
require_once( $IP . "/includes/SpecialPage.php" );

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
		$html .= "<span id=\"menue-step1\" style=\"font-weight: bold\">1. Specify URI <span class=\"HeadlineDelimiter\"></span></span>";
		$html .= "<span id=\"menue-step2\" >2. Select Method <span class=\"HeadlineDelimiter\"></span></span>";
		$html .= "<span id=\"menue-step3\" >3. Define Parameters <span class=\"HeadlineDelimiter\"></span></span>";
		$html .= "<span id=\"menue-step4\">4. Define Aliases <span class=\"HeadlineDelimiter\"></span></span>";
		$html .= "<span id=\"menue-step5\">5. Define Update Policy <span class=\"HeadlineDelimiter\"></span></span>";
		$html .= "<span id=\"menue-step6\">6. Choose Name</span>";
		$html .= "</div>";


		// 1. Specify URI
		$html .= "<br>";
		$html .= "<div id=\"step1\" style=\"display: block\">";
		$html .= "<img id=\"step1-img\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= "Please enter the URI of the WebService: ";
		$html .= "<input id=\"step1-uri\" type=\"text\" size=\"50\" maxlength=\"300\" value=\"http://localhost/halowiki/index.php?action=get_wsdl\"/>";

		// Specify URI Error
		// $html .= "It was not possible to connect to the WebService. Please enter a new URI or try again.";
			
		$html .= "<img onclick=\"webServiceSpecial.processStep1()\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= "</div>";
		$html .= "<br>";

		//2. Specify method
		$html .= "<div id=\"step2\" style=\"display: none\">";
		$html .= "<img id=\"step2-img\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= "Please select one of the following methods provided by the WebService: ";
		$html .= "<select id=\"step2-methods\" size=\"1\">";
		$html .= "</select>";
		$html .= "<img onclick=\"webServiceSpecial.processStep2()\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= "</div>";
		$html .= "<br>";

		//3. Define Parameters
		$html .= "<div id=\"step3\" style=\"display: none\">";
		$html .= "<img id=\"step3-img\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= "The method asks for the following parameters.  ";
		$html .= "<table id=\"step3-parameters\"><tr><th>Path:</th><th>Alias: <span style=\"cursor: pointer\" onclick=\"webServiceSpecial.generateParameterAliases()\">generate</span></th><th>Optional:</th><th>Default value:</th><th></th></tr></table>";

		$html .= "<img id=\"step3-ok\" onclick=\"webServiceSpecial.processStep3()\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= "</div>";
		$html .= "<br>";

		// 4. Define Results
		$html .= "<div id=\"step4\" style=\"display: none\">";
		$html .= "<img id=\"step4-img\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= "Please provide an alias for each result that is delivered by the WebService. ";
		$html .= "This alias will be used whenever you include this WebService. ";
		$html .= "Please use short but distinctive aliases or use the generate function.";
			
		$html .= "<table id=\"step4-results\"><tr><th></th><th>Path:</th><th>Alias: <span style=\"cursor: pointer\" onclick=\"webServiceSpecial.generateResultAliases()\">generate</span></th></tr></table>";

		$html .= "<img id=\"step4-ok\" onclick=\"webServiceSpecial.processStep4()\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= "</div>";
		$html .= "<br>";

		// 5. Define updatae policy
		$html .= "<div id=\"step5\" style=\"display: none\" >";
		$html .= "<img id=\"step5-img\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= "Please define the update policies for this WebService.";
		$html .= "<br>";
			
		$html .= "<span class=\"OuterLeftIndent\">Display policy: </span>";
		$html .= "<input id=\"step4-display-once\" checked=\"true\" type=\"radio\" name=\"step5-display\" value=\"once\">Once</input>";
		$html .= "<input id=\"step4-display-max\" type=\"radio\" name=\"step5-display\" value=\"\">MaxAge</input>";

		$html .= "<input type=\"text\" id=\"step5-display-days\" text\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> days </span>";
		$html .= "<input type=\"text\" id=\"step5-display-hours\" text\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> hours </span>";
		$html .= "<input type=\"text\" id=\"step5-display-minutes\" text\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> minutes </span>";
		$html .= "<br>";

		$html .= "<span class=\"OuterLeftIndent\">Query policy: </span>";
		$html .= "<input id=\"step4-query-once\" checked=\"true\" type=\"radio\" name=\"step5-query\" value=\"once\">Once</input>";
		$html .= "<input id=\"step4-query-max\" type=\"radio\" name=\"step5-query\" value=\"\">MaxAge</input>";

		$html .= "<input type=\"text\" id=\"step5-query-days\" text\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> days </span>";
		$html .= "<input type=\"text\" id=\"step5-query-hours\" text\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> hours </span>";
		$html .= "<input type=\"text\" id=\"step5-query-minutes\" text\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> minutes </span>";

		$html .= "<img onclick=\"webServiceSpecial.processStep5()\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= "</div>";

		// 6. Specify name
		$html .= "<br>";
		$html .= "<div id=\"step6\" style=\"display: none\">";
		$html .= "<img id=\"step6-img\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		$html .= "Please enter a name for this WebService: ";
		$html .= "<input id=\"step6-name\" type=\"text\" size=\"50\" maxlength=\"300\"/>";
		$html .= "Save and Finish";
		$html .= "<img onclick=\"webServiceSpecial.processStep6()\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\" class=\"OKButton\"></img>";
		
		$html .= "</div>";
		$html .= "<br>";
		
		// Help
		$html .= "<br>";
		$html .= "<div id=\"help\" style=\"visibility: visible\">";
		$html .= "<h2>Help</h2>";
		$html .= "<div id=\"step1-help\">Please enter the URI of a WebService. The URI must direct towards the WSDL (Web Service Description Language) file of the WebService. If you do not have an URI yet, you can search for different WebService providers on the internet. Please take a look at Web Service Search Engines in order to find what you are looking for.</div>";
		$html .= "<div id=\"step2-help\" style=\"display: none\">Each WebService may provide different methods which in turn deliver different types of data. The WebService support in this wiki does allow to collect results from only one method of the WebService. If you would like to gather results from different methods of the same WebService, you have to create one WebService definition for each method.</div>";
		$html .= "<div id=\"step3-help\" style=\"display: none\">todo: write help message</div>";
		$html .= "<div id=\"step4-help\" style=\"display: none\">todo: write help message</div>";
		$html .= "<div id=\"step5-help\" style=\"display: none\">The update policies define after which a value delivered by a WebService will be updated. The display policy will be relevant whenever a value is displayed in an article, whereas the query policy is only relevant for semantic queries. The delay value allows to give a delay (in seconds) that has to be considered between two calls to a WebService.</div>";
		$html .= "<div id=\"step6-help\" style=\"display: none\">In order to use this WebService you need to give it a name. Please use a meaningful name that makes it easy for other users to recognize the purpose of this WebService.</div>";
		$html .= "</div>";
		$html .= "<br>";
		
		$wgOut->addHTML($html);
	}
}

?>