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
			$html .= "<span id=\"menue-step1\" style=\"font-weight: bold\">1. Specify URI -</span>";
			$html .= "<span id=\"menue-step2\" >2. Select Method -</span>";
			$html .= "<span id=\"menue-step3\" >3. Define Parameters -</span>";
			$html .= "<span id=\"menue-step4\">4. Define Aliases -</span>";
			$html .= "<span id=\"menue-step5\">5. Define Update Policy -</span>";
			$html .= "<span id=\"menue-step6\">6. Choose Name</span>";
		$html .= "</div>";
		
		
		// 1. Specify URI
		$html .= "<br>";
		$html .= "<div id=\"step1\">";
			$html .= "Please enter the URI of the WebService: ";
			$html .= "<input id=\"step1-uri\" type=\"text\" size=\"50\" maxlength=\"300\" value=\"http://localhost/halowiki/index.php?action=get_wsdl\"/>";

				// Specify URI Error
				// $html .= "It was not possible to connect to the WebService. Please enter a new URI or try again.";
			
				$html .= "<img onclick=\"webServiceSpecial.processStep1()\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\"></img>";
		$html .= "</div>";
		$html .= "<br>";

		//2. Specify method
		$html .= "<div id=\"step2\" style=\"visibility: hidden\">";
			$html .= "Please select one of the following methods provided by the WebService: ";
			$html .= "<select id=\"step2-methods\" size=\"1\">";
			$html .= "</select>";
			$html .= "<img onclick=\"webServiceSpecial.processStep2()\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\"></img>";
		$html .= "</div>";
		$html .= "<br>";

		//3. Define Parameters
		$html .= "<div id=\"step3\" style=\"visibility: hidden\">";
			$html .= "The method asks for the following parameters.  ";
			$html .= "<table id=\"step3-parameters\"><tr><th>Path:</th><th>Alias:</th><th>Optional:</th><th>Default value:</th></tr></table>";
		
			$html .= "<img onclick=\"webServiceSpecial.processStep3()\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\"></img>";
		$html .= "</div>";
		$html .= "<br>";

		// 4. Define Results
		$html .= "<div id=\"step4\" style=\"visibility: hidden\">";
			$html .= "Please provide an alias for each result that is delivered by the WebService. ";
			$html .= "This alias will be used whenever you include this WebService. ";
			$html .= "Please use short but distinctive aliases or use the generate function.";
			
			$html .= "<table id=\"step4-results\"><tr><th>Path:</th><th>Alias:</th></tr></table>";

			$html .= "<img onclick=\"webServiceSpecial.processStep4()\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\"></img>";
		$html .= "</div>";
		$html .= "<br>";

		// 5. Define updatae policy
		$html .= "<div id=\"step5\" style=\"visibility: hidden\" >";
			$html .= "Please define the update policies for this WebService.";
			$html .= "<br>";
			
			$html .= "<span style=\"padding-left: 40px\">Display policy: </span>";
			$html .= "<input id=\"step4-display-once\" type=\"radio\" name=\"step5-display\" value=\"once\">Once</input>";
			$html .= "<input id=\"step4-display-max\" type=\"radio\" name=\"step5-display\" value=\"\">MaxAge</input>";

			$html .= "<input type=\"text\" id=\"step5-display-days\" text\" size=\"7\" maxlength=\"10\" />";
			$html .= "<span> days </span>";
			$html .= "<input type=\"text\" id=\"step5-display-hours\" text\" size=\"7\" maxlength=\"10\" />";
			$html .= "<span> hours </span>";
			$html .= "<input type=\"text\" id=\"step5-display-minutes\" text\" size=\"7\" maxlength=\"10\" />";
			$html .= "<span> minutes </span>";
			$html .= "<br>";
		
			$html .= "<span style=\"padding-left: 40px\">Query policy: </span>";
			$html .= "<input id=\"step4-query-once\" type=\"radio\" name=\"step5-query\" value=\"once\">Once</input>";
			$html .= "<input id=\"step4-query-max\" type=\"radio\" name=\"step5-query\" value=\"\">MaxAge</input>";

			$html .= "<input type=\"text\" id=\"step5-query-days\" text\" size=\"7\" maxlength=\"10\" />";
			$html .= "<span> days </span>";
			$html .= "<input type=\"text\" id=\"step5-query-hours\" text\" size=\"7\" maxlength=\"10\" />";
			$html .= "<span> hours </span>";
			$html .= "<input type=\"text\" id=\"step5-query-minutes\" text\" size=\"7\" maxlength=\"10\" />";
			$html .= "<span> minutes </span>";

			$html .= "<img onclick=\"webServiceSpecial.processStep5()\" src=\"".$smwgHaloScriptPath."/skins/webservices/pfeil_rechts.gif\"></img>";
		$html .= "</div>";
		
		$wgOut->addHTML($html);
	}
}

?>