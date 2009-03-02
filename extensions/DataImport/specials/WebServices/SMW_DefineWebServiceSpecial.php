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
 * This is responsible for the special page define webservice
 *
 * @author Ingo Steinbauer
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) die;
global $IP;
require_once( $IP . "/includes/SpecialPage.php" );

global $smwgDIIP, $smwgDIScriptPath;
include_once($smwgDIIP . '/languages/SMW_DILanguage.php');

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

		global $smwgDIIP, $smwgDIScriptPath;

		$wwsdId = $wgRequest->getVal( 'wwsdId' );
		$editwwsd = false;
		if ( !is_null( $wwsdId ) ) {
			$editwwsd = true;
			$wwsd = WebService::newFromID($wwsdId);
		}

		$html = "";

		//0. menue
		$html .= "<div id=\"menue\">";

		$fClass = " class=\"ActualMenueStep\ ";
		$rClass = "";
		if($editwwsd){
			$fClass = "";
			$rClass = " class=\"DoneMenueStep\" ";
		}
		$html .= "<span id=\"menue\">";
		$html .= "<span id=\"menue-step1\" ".$fClass.$rClass.": bold\">".wfMsg("smw_wws_s1-menue")."<span class=\"HeadlineDelimiter\"></span></span>";
		$html .= "<span id=\"menue-step2\" ".$rClass.">".wfMsg("smw_wws_s2-menue")."<span class=\"HeadlineDelimiter\"></span></span>";
		$html .= "<span id=\"menue-step3\" ".$rClass.">".wfMsg("smw_wws_s3-menue")."<span class=\"HeadlineDelimiter\"></span></span>";
		$html .= "<span id=\"menue-step4\"".$rClass.">".wfMsg("smw_wws_s4-menue")."<span class=\"HeadlineDelimiter\"></span></span>";
		$html .= "<span id=\"menue-step5\"".$rClass.">".wfMsg("smw_wws_s5-menue")."<span class=\"HeadlineDelimiter\"></span></span>";
		$html .= "<span id=\"menue-step6\"".$rClass.">".wfMsg("smw_wws_s6-menue")."</span>";
		$html .= "</span>";



		$visible = "display:none";

		$soap = "";
		$rest = " checked=\"true\" ";

		$uri = "";

		$authVisibility = " display:none ";
		$auth = "";
		$noauth = " checked=\"true\" ";
		$username = "";
		$password = "";
		
		$method = "";
		
		$displayOnce = "checked=\"true\"";
		$displayMax = "";
		$displayMinutes = "";
		$queryOnce = "checked=\"true\"";
		$queryMax = "";
		$queryMinutes = "";
		$delayValue = "";
		$spanOfLife = "";
		$expires = "";
		$expiresno = " checked=\"true\" ";
		
		$name = "";
		
		if($editwwsd){
			$visible = "";

			if($wwsd->getProtocol() == "SOAP"){
				$soap = " checked=\"true\" ";
				$rest = "";
			}
			$uri = $wwsd->getURI();
			$username = $wwsd->getAuthenticationLogin();
			if(strlen($username) > 0){
				$auth = " checked=\"true\" ";
				$noauth = "";
				$authVisibility = "";
			}
			$password = $wwsd->getAuthenticationPassword();

			$method = "<option>".$wwsd->getMethod()."</option>";
		
			if($wwsd->getDisplayPolicy > 0){
				$displayOnce = "";
				$displayMax = "checked=\"true\"";
				$displayMinutes = " value=\"".$wwsd->getDisplayPolicy()."\"";
			}
			
			if($wwsd->getQueryPolicy() > 0){
				$queryOnce = "";
				$queryMax = "checked=\"true\"";
				$queryMinutes = " value=\"".$wwsd->getQueryPolicy()."\"";
			}
			
			if($wwsd->getUpdateDelay() > 0){
				$delayValue = " value=\"".$wwsd->getUpdateDelay()."\"";;
			}
			
			if($wwsd->getSpanOfLife() > 0){
				$spanOfLife = "value=\"".$wwsd->getSpanOfLife()."\"";
			}
			
			if($wwsd->doesExpireAfterUpdate()){
				$expires = " checked=\"true\" ";
				$expiresno = "";
			}
			
			$name = "value=\"".substr($wwsd->getName(), 11)."\"";
		}

		// 1. Specify URI
		$html .= "<br>";
		$html .= "<div id=\"step1\" class=\"StepDiv\" style=\"display: block\">";
		$html .= "<p class=\"step-headline\">".wfMsg("smw_wws_s1-intro");
		$html .= "<img id=\"step1-help-img\" class=\"help-image\" onclick=\"webServiceSpecial.displayHelp(1)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";
			
		//todo: use language file
		$html .= "Specify protocol: ";
		$html .= "<td><input id=\"step1-protocol-soap\" ".$soap."type=\"radio\" name=\"step1-protocol\" value=\"soap\">SOAP</input>";
		$html .= "<td><input id=\"step1-protocol-rest\" ".$rest." type=\"radio\" name=\"step1-protocol\" value=\"rest\">REST</input>";
			
		$html .= "<br/>";
		$html .= wfMsg("smw_wws_s1-uri");
		$html .= "<input id=\"step1-uri\" type=\"text\" onkeypress=\"webServiceSpecial.checkEnterKey(event, 'step1')\" size=\"100\" maxlength=\"500\" value=\"".$uri."\"/>";
			
		$html .= "<br/>Authentication required? ";
		$html .= "<td><input id=\"step1-auth-yes\" ".$auth." onfocus=\"webServiceSpecial.showAuthenticationBox('yes')\" type=\"radio\" name=\"step1-auth\" value=\"yes\">yes</input>";
		$html .= "<td><input id=\"step1-auth-no\" ".$noauth." onfocus=\"webServiceSpecial.showAuthenticationBox('no')\" type=\"radio\" name=\"step1-auth\" value=\"no\">no</input>";
			
		$html .= "<span id=\"step1-auth-box\" style=\"".$authVisibility."\">";
		$html .= "<br/>Username: ";
		$html .= "<input id=\"step1-username\" type=\"text\" size=\"30\" maxlength=\"100\" value=\"\"/>";
		$html .= "Password: ";
		$html .= "<input id=\"step1-password\" type=\"password\" size=\"30\" maxlength=\"100\" value=\"\"/>";
		$html .= "</span>";
			
		$html .= "<div id=\"step1-help\" style=\"display:none\">".wfMsg("smw_wws_s1-help")."</div>";

		$html .= "<br/>";
		$html .= "<span id=\"step1-go\" class=\"OKButton\">";
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step1-go-img\" value=\"".wfMsg("smw_wsgui_nextbutton")."\" onclick=\"webServiceSpecial.processStep1()\">";
		$html .= "</span>";

		//todo: edit gui anpassen
		$html .= "</div>";



		//2. Specify method
		$html .= "<div id=\"step2\" class=\"StepDiv\" style=\"".$visible."\">";
			
		$html .= "<p class=\"step-headline\">".wfMsg("smw_wws_s2-intro");
		$html .= "<img id=\"step2-help-img\" class=\"help-image\" onclick=\"webServiceSpecial.displayHelp(2)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";
		$html .= wfMsg("smw_wws_s2-method");
			
		$html .= "<select id=\"step2-methods\" size=\"1\">";
		$html .= $method;
		$html .= "</select>";
			
		$html .= "<div id=\"step2-help\" style=\"display:none\">".wfMsg("smw_wws_s2-help")."</div>";
			
		$html .= "<br/>";
		$html .= "<span id=\"step2-go\" class=\"OKButton\">";
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step2-go-img\" value=\"".wfMsg("smw_wsgui_nextbutton")."\" onclick=\"webServiceSpecial.processStep2()\">";
		$html .= "</span>";
			
		$html .= "</div>";

		//3. Define Parameters
		$html .= "<div id=\"step3\" class=\"StepDiv\" style=\"".$visible."\">";

		$html .= "<p class=\"step-headline\">".wfMsg("smw_wws_s3-intro");
		$html .= "<img id=\"step3-help-img\" class=\"help-image\" onclick=\"webServiceSpecial.displayHelp(3)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";

		$html .= "<div id=\"step3-duplicates\" style=\"display:none\"><img src=\"".$smwgDIScriptPath."/skins/webservices/warning.png\"></img>";
		$html .= wfMsg("smw_wws_duplicate");
		$html .= "</div>";
		$html .= "<table id=\"step3-parameters\"><tr><th>Path:</th><th>Use:</th><th>Alias: <span style=\"cursor: pointer\" onclick=\"webServiceSpecial.generateParameterAliases(true)\"><img src=\"".$smwgDIScriptPath."/skins/webservices/Pencil_go.png\"</img></span></th><th>Optional:</th><th>Default value:</th><th></th></tr></table>";

		$html .= "<div id=\"step3-help\" style=\"display:none\">".wfMsg("smw_wws_s3-help")."</div>";

		$html .= "<br/>";
		$html .= "<span id=\"step3-go\" class=\"OKButton\">";
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step3-go-img\" value=\"".wfMsg("smw_wsgui_nextbutton")."\" onclick=\"webServiceSpecial.processStep3()\">";
		$html .= "</span>";

		$html .= "</div>";

		// 4. Define Results
		$html .= "<div id=\"step4\" class=\"StepDiv\" style=\"".$visible."\">";

		$html .= "<p class=\"step-headline\">".wfMsg("smw_wws_s4-intro");
		$html .= "<img id=\"step4-help-img\" class=\"help-image\" onclick=\"webServiceSpecial.displayHelp(4)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";

		$html .= "<div id=\"step4-duplicates\" style=\"display:none\"><img src=\"".$smwgDIScriptPath."/skins/webservices/warning.png\"></img>";
		$html .= wfMsg("smw_wws_duplicate");
		$html .= "</div>";
		$html .= "<table id=\"step4-results\"><tr><th>Path:</th><th>Use:</th><th>Alias: <span style=\"cursor: pointer\" onclick=\"webServiceSpecial.generateResultAliases(true)\"><img src=\"".$smwgDIScriptPath."/skins/webservices/Pencil_go.png\"</img></span></th></tr></table>";

		$html .= "<div id=\"step4-help\" style=\"display:none\">".wfMsg("smw_wws_s4-help")."</div>";

		$html .= "<br/>";
		$html .= "<span id=\"step4-go\" class=\"OKButton\">";
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step4-go-img\" value=\"".wfMsg("smw_wsgui_nextbutton")."\" onclick=\"webServiceSpecial.processStep4()\">";
		$html .= "</span>";

		$html .= "</div>";
		
		// 5. Define updatae policy
		$html .= "<div id=\"step5\" class=\"StepDiv\" style=\"".$visible."\" >";
		$html .= "<p class=\"step-headline\">".wfMsg("smw_wws_s5-intro");
		$html .= "<img id=\"step5-help-img\" class=\"help-image\" onclick=\"webServiceSpecial.displayHelp(5)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";

		$html .= "<table id=\"step5-policies\">";

		$html .= "<tr><td><span>Display policy: </span></td>";
		$html .= "<td><input id=\"step5-display-once\" ".$displayOnce." onfocus=\"webServiceSpecial.selectRadioOnce('step5-display-once')\" type=\"radio\" name=\"step5-display\" value=\"once\">Once</input>";
		$html .= "<span><input id=\"step5-display-max\" ".$displayMax." type=\"radio\" name=\"step5-display\" value=\"\">MaxAge</input></span></td>";

		$html .= "<td><input type=\"text\" id=\"step5-display-days\" onfocus=\"webServiceSpecial.selectRadio('step5-display-max')\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> days </span>";
		$html .= "<input type=\"text\" id=\"step5-display-hours\" onfocus=\"webServiceSpecial.selectRadio('step5-display-max')\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> hours </span>";
		$html .= "<input type=\"text\" id=\"step5-display-minutes\" onfocus=\"webServiceSpecial.selectRadio('step5-display-max')\" size=\"7\" maxlength=\"10\" ".$displayMinutes."/>";
		$html .= "<span> minutes </span>";
		$html .= "</td></tr>";

		$html .= "<tr><td><span>Query policy: </span></td>";
		$html .= "<td><input id=\"step5-query-once\" ".$queryOnce." onfocus=\"webServiceSpecial.selectRadioOnce('step5-query-once')\" type=\"radio\" name=\"step5-query\" value=\"once\">Once</input>";
		$html .= "<span><input id=\"step5-query-max\" ".$queryMax." type=\"radio\" name=\"step5-query\" value=\"\">MaxAge</input></span></td>";

		$html .= "<td><input type=\"text\" id=\"step5-query-days\" onfocus=\"webServiceSpecial.selectRadio('step5-query-max')\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span> days </span>";
		$html .= "<input type=\"text\" id=\"step5-query-hours\" size=\"7\" onfocus=\"webServiceSpecial.selectRadio('step5-query-max')\" maxlength=\"10\" />";
		$html .= "<span> hours </span>";
		$html .= "<input type=\"text\" id=\"step5-query-minutes\" size=\"7\" onfocus=\"webServiceSpecial.selectRadio('step5-query-max')\" maxlength=\"10\" ".$queryMinutes."/>";
		$html .= "<span> minutes </span>";
		$html .= "</td></tr>";

		$html .= "<tr><td></td>";
		$html .= "<td><span> Delay value (seconds): </span></td>";
		$html .= "<td><input type=\"text\" id=\"step5-delay\" size=\"7\" maxlength=\"10\" ".$delayValue."/>";
		$html .= "</td></tr>";

		$html .= "<tr></tr>";

		$html .= "<tr><td><span> Span of life (in days): </span></td>";
		$html .= "<td><input type=\"text\" id=\"step5-spanoflife\" text\" size=\"7\" maxlength=\"10\" ".$spanOfLife."/></td>";
		$html .= "<td><span> Expires after update: </span>";
		$html .= "<input id=\"step5-expires-yes\" ".$expires." type=\"radio\" name=\"step5-expires\" value=\"once\">Yes</input>";
		$html .= "<input id=\"step5-expires-no\" ".$expiresno." type=\"radio\" name=\"step5-expires\" value=\"\">No</input>";

		$html .= "</td></tr></table>";

		$html .= "<div id=\"step5-help\" style=\"display:none\">".wfMsg("smw_wws_s5-help")."</div>";

		$html .= "<br/>";
		$html .= "<span id=\"step5-go\" class=\"OKButton\">";
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step5-go-img\" value=\"".wfMsg("smw_wsgui_nextbutton")."\" onclick=\"webServiceSpecial.processStep5()\">";
		$html .= "</span>";

		$html .= "</div>";
		
		// 6. Specify name
		$html .= "<div id=\"step6\" class=\"StepDiv\" style=\"".$visible."\">";
		$html .= "<p class=\"step-headline\">".wfMsg("smw_wws_s6-intro");
		$html .= "<img id=\"step6-help-img\" class=\"help-image\" onclick=\"webServiceSpecial.displayHelp(6)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";

		$html .= wfMsg("smw_wws_s6-name");
		$html .= "<input id=\"step6-name\" type=\"text\" onkeypress=\"webServiceSpecial.checkEnterKey(event, 'step6')\" size=\"50\" maxlength=\"300\" ".$name."/>";

		$html .= "<div id=\"step6-help\" style=\"display:none\">".wfMsg("smw_wws_s6-help")."</div>";

		$html .= "<br/>";
		$html .= "<span id=\"step6-go\" class=\"OKButton\">";
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step6-go-img\" value=\"".wfMsg("smw_wsgui_savebutton")."\" onclick=\"webServiceSpecial.processStep6()\">";
		$html .= "</span>";

		$html .= "</div>";
		
		//todo:use language file
		//7. show #ws-usage
		$html .= "<div id=\"step7\" style=\"display: none\">";
		$html .= "<span>Your WebService \"";
		$html .= "<span id=\"step7-name\"></span>";
		$html .= "\" has been successfully created. In order to include this WebService into a page, please use the following syntax:</span>";
		$html .= "<br><br>";
		$url = Title::makeTitleSafe(NS_SPECIAL, "webservicerepository")->getInternalURL();
		$html .= "<div id=\"step7-container\"></div>";
		$html .= "<br><br>";
		$html .= "<span>Your WebService will from now on be available in <a href=\"".$url."\">the list of available WebServices.</a> You can now go on and define another WWSD.</span>";
		//$html .= "<img onclick=\"webServiceSpecial.processStep7()\" src=\"".$smwgDIScriptPath."/skins/webservices/Control_play.png\" class=\"OKButton\"></img>";
		$html .= "<br/><input type=\"button\" class=\"OKButton\" id=\"step7-go-img\" value=\"New\" onclick=\"webServiceSpecial.processStep7()\">";
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
		$html .= "<div id=\"step6c-error\" style=\"display: none\">".wfMsg("smw_wws_s6-error3")."</div>";
		$html .= "</div>";

		if($editwwsd){
			$html .= $this->getMergedParameters($wwsd, false);
			$html .= $this->getMergedParameters($wwsd, true);
		}

		$wgOut->addHTML($html);
	}

	private function getMergedParameters($wwsd, $result){
		$wsClient = DefineWebServiceSpecialAjaxAccess::createWSClient($wwsd->getURI());

		if($result){
			$wwsdParameters = new SimpleXMLElement($wwsd->getResult());
		} else {
			$wwsdParameters = new SimpleXMLElement("<p>".$wwsd->getParameters()."</p>");
		}
		
		$wwsdParameters = $wwsdParameters->children();

		$rawParameters = $wsClient->getOperation($wwsd->getMethod());

		$wsdlParameters = array();
		if($result){
			$wsdlParameters = DefineWebServiceSpecialAjaxAccess::getFlatParameters($wwsd->getURI(), $wsClient,"", $rawParameters[0]);
		} else {
			//todo: handle no params
			$numParam = count($rawParameters);
			for ($i = 1; $i < $numParam; $i++) {
				$pName = $rawParameters[$i][0];
				$pType = $rawParameters[$i][1];
				$tempFlat = DefineWebServiceSpecialAjaxAccess::getFlatParameters($wwsd->getURI(), $wsClient, $pName, $pType);
				$wsdlParameters = array_merge($wsdlParameters , $tempFlat);
			}
		}

		$mergedParameters = array();


		//todo: handle overflows
		foreach($wsdlParameters as $wsdlParameter){
			$wsdlParameterSteps = explode(".", $wsdlParameter);

			$matchedPath = "";
			$a = array();
			for($i=0; $i < count($wwsdParameters); $i++){
				$wwsdParameterSteps = explode(".", $wwsdParameters[$i]["path"]);
				if(count($wsdlParameterSteps) != count($wwsdParameterSteps)){
					continue;
				}

				for($k=0; $k < count($wsdlParameterSteps); $k++){
					$dupPos = strpos($wsdlParameterSteps[$k], "##duplicate");
					$overflowPos = strpos($wsdlParameterSteps[$k], "##overflow");
					$bracketPos = strpos($wwsdParameterSteps[$k], "[");

					$wwsdParameterStep = $wwsdParameterSteps[$k];
					if($bracketPos){
						$wwsdParameterStep = substr($wwsdParameterStep, 0, $bracketPos);
					}
					if(strpos($wsdlParameterSteps[$k], $wwsdParameterStep) === 0){
						$matchedPath .= ".".$wwsdParameterSteps[$k];
						if($overflowPos){
							$matchedPath .= "##overflow##";
						}
						if($dupPos){
							$matchedPath .= "##duplicate";
						}
					} else {
						$matchedPath = "";
						break;
					}
				}
				if(strlen($matchedPath) > 0){
					$a["name"] = $wwsdParameters[$i]["name"]."";
					$a["path"] = substr($matchedPath, 1);
					if(!$result){
						$a["defaultValue"] = $wwsdParameters[$i]["defaultValue"]."";
						$a["optional"] = $wwsdParameters[$i]["optional"]."";
						if(strlen($a["optional"]) == 0){
							$a["optional"] = "##";
						}
						if(strlen($a["defaultValue"]) == 0){
							$a["defaultValue"] = "##";
						}
					}
					unset($wwsdParameters[$i]);
					break;
				}
			}
			if(strlen($matchedPath) == 0){
				$a["path"] = $wsdlParameter;
				$a["name"] = "##";
				if(!$result){
					$a["optional"] = "##";
					$a["defaultValue"] = "##";
				}
			}
			$mergedParameters[$a["path"]] = $a;
		}

		foreach($wwsdParameters as $wsParameter){
			$o = array();
			$o["name"] = $wsParameter["name"]."";
			$o["path"] = $wsParameter["path"]."";
			if(!$result){
				$o["defaultValue"] = $wsParameter["defaultValue"]."";
				$o["optional"] = $wsParameter["optional"]."";
				if(strlen($o["optional"]) == 0){
					$o["optional"] = "##";
				}
				if(strlen($o["defaultValue"]) == 0){
					$o["defaultValue"] = "##";
				}
			}
			$mergedParameters[$o["path"]] = $o;
		}
		ksort($mergedParameters);


		$html = "";
		if($result){
			$html .= "<span id=\"editresults\" style=\"display: none\">";
		} else {
			$html .= "<span id=\"editparameters\" style=\"display: none\">";
		}

		foreach($mergedParameters as $mergedParameter){
			$html .= $mergedParameter["name"].";";
			$html .= $mergedParameter["path"].";";
			if(!$result){
				$html .= $mergedParameter["optional"].";";
				$html .= $mergedParameter["defaultValue"].";";
			}
		}
		$html .= "</span>";
		return $html;
	}
}
?>