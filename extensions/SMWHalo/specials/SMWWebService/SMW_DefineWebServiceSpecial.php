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

		$html = "";

		// 1. Specify URI
		$html .= "<div id=\"step1\">";
		$html .= "Please enter the URI of the WebService: ";
		$html .= "<input id=\"step1-uri\" type=\"text\" size=\"50\" maxlength=\"300\" value=\"http://localhost/halowiki/index.php?action=get_wsdl\">";

		// 	Specify URI Error
		$html .= "<div id=\"step1-error\">";
		$html .= "It was not possible to connect to the WebService. Please enter a new URI or try again.";
			
		$html .= "<span onclick=\"webServiceSpecial.processStep1()\">Ok</div>";
		$html .= "</div>";

		$html .= "</div>";
		$html .= "<br>";


		//2. Specify method
		$html .= "<div id=\"step2\">";
		$html .= "Please select one of the following methods provided by the WebService: ";
		$html .= "<select id=\"step2-methods\" size=\"1\">";
		$html .= "</select>";
			
		$html .= "<span onclick=\"webServiceSpecial.processStep2()\">Ok</div>";
		$html .= "</div>";
		$html .= "<br>";


		//3. Define Parameters
		$html .= "<div id=\"step3\">";
		$html .= "The method asks for the following parameters.  ";
			
		$html .= "<div id=\"step3-parameters\"></div>";
		
		$html .= "<span onclick=\"webServiceSpecial.processStep3()\">Ok</div>";	
		$html .= "</div>";
		$html .= "<br>";



		$wgOut->addHTML($html);
	}
}

?>