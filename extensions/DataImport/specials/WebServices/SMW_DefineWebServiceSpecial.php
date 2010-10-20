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
	public function execute($par) {
		global $wgRequest, $wgOut;

		$wgOut->setPageTitle(wfMsg("definewebservice"));

		global $smwgDIIP, $smwgDIScriptPath;

		$wwsdId = $wgRequest->getVal( 'wwsdId' );
		$editwwsd = false;
		if ( !is_null( $wwsdId ) ) {
			$editwwsd = true;
			$wwsd = WebService::newFromID($wwsdId);
		}

		$html = "";
		
		//ac needs to know property ns id
		$html .= '<span style="display: none" id="di_ns_id">'.SMW_NS_PROPERTY.'</span>';

		//0. menue
		$html .= "<div id=\"menue\">";

		$fClass = " class=\"ActualMenueStep\" ";
		$rClass = " class=\"TodoMenueStep\" ";
		if($editwwsd){
			$fClass = " class=\"DoneMenueStep\" ";
			$rClass = " class=\"DoneMenueStep\" ";
		}
		$html .= "<div id=\"breadcrumb-menue\" class=\"BreadCrumpContainer\">";
		$html .= "<span id=\"menue-step1\" ".$fClass.">".wfMsg("smw_wws_s1-menue")."</span><span class=\"HeadlineDelimiter\"></span>";
		$html .= "<span id=\"menue-step2\" ".$rClass.">".wfMsg("smw_wws_s2-menue")."</span><span class=\"HeadlineDelimiter\" id=\"menue-step2-delimiter\"></span>";
		$html .= "<span id=\"menue-step3\" ".$rClass.">".wfMsg("smw_wws_s3-menue")."</span><span class=\"HeadlineDelimiter\" id=\"menue-step3-delimiter\"></span>";
		$html .= "<span id=\"menue-step4\"".$rClass.">".wfMsg("smw_wws_s4-menue")."</span><span class=\"HeadlineDelimiter\"></span>";
		$html .= "<span id=\"menue-step5\"".$rClass.">".wfMsg("smw_wws_s5-menue")."</span><span class=\"HeadlineDelimiter\"></span>";
		$html .= "<span id=\"menue-step6\"".$rClass.">".wfMsg("smw_wws_s6-menue")."</span>";
		$html .= "</div></div>";



		$visible = "display:none";
		$showButton = "";

		$soap = "";
		$rest = " checked=\"true\" ";
		$ld = "";

		$uri = "";

		$authVisibility = " display:none ";
		$auth = "";
		$noauth = " checked=\"true\" ";
		$username = "";
		$password = "";

		$method = "";
		
		$subjectCreationPattern = "";
		$subjectCreationPatternVisibility = "none";
		$subjectCreationCheckboxChecked = '';

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
			$showButton = "display: none";

			if(strtolower($wwsd->getProtocol()) == "soap"){
				$soap = " checked=\"true\" ";
				$rest = "";
			} else if(strtolower($wwsd->getProtocol()) == "linkeddata"){
				$ld = " checked=\"true\" ";
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
			
			$ts = $wwsd->getTriplificationSubject(); 
			if(strlen($ts) > 0){
				$subjectCreationPattern = $ts;
				$subjectCreationPatternVisibility = "";
				$subjectCreationCheckboxChecked = ' checked="true" ';
			}

			if($wwsd->getDisplayPolicy() > 0){
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
		$html .= "<p id=\"step1-head\" class=\"step-headline\">".wfMsg("smw_wws_s1-intro");
		$html .= "<img id=\"step1-help-img\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"webServiceSpecial.displayHelp(1)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";
			
		$html .= "<div>".wfMsg("smw_wws_spec_protocol");
		$html .= "<input id=\"step1-protocol-rest\" ".$rest." type=\"radio\" name=\"step1-protocol\" value=\"rest\" onclick=\"webServiceSpecial.updateBreadCrump(event)\">REST</input>";
		$html .= "<input id=\"step1-protocol-ld\" ".$ld." type=\"radio\" name=\"step1-protocol\" value=\"ld\" onclick=\"webServiceSpecial.updateBreadCrump(event)\">LinkedData</input>";
		$html .= "<input id=\"step1-protocol-soap\" ".$soap."type=\"radio\" name=\"step1-protocol\" value=\"soap\" onclick=\"webServiceSpecial.updateBreadCrump(event)\">SOAP</input></div>";
		
		$html .= "<div>".wfMsg("smw_wws_s1-uri");
		$html .= "<input id=\"step1-uri\" type=\"text\" onkeypress=\"webServiceSpecial.checkEnterKey(event, 'step1')\" size=\"100\" maxlength=\"500\" value=\"".$uri."\"/></div>";
			
		$html .= "<div>".wfMsg('smw_wws_spec_auth');
		$html .= "<input id=\"step1-auth-yes\" ".$auth." onfocus=\"webServiceSpecial.showAuthenticationBox('Yes')\" type=\"radio\" name=\"step1-auth\" value=\"yes\">".wfMsg('smw_wws_yes')."</input>";
		$html .= "<input id=\"step1-auth-no\" ".$noauth." onfocus=\"webServiceSpecial.showAuthenticationBox('no')\" type=\"radio\" name=\"step1-auth\" value=\"no\">".wfMsg('smw_wws_no')."</input></div>";
			
		$html .= "<span id=\"step1-auth-box\" style=\"".$authVisibility."\">";
		$html .= wfMsg('smw_wws_username');
		$html .= "<input id=\"step1-username\" type=\"text\" size=\"30\" maxlength=\"100\" value=\"".$username."\"/>";
		$html .= wfMsg('smw_wws_password');
		$html .= "<input id=\"step1-password\" type=\"password\" size=\"30\" maxlength=\"100\" value=\"".$password."\"/>";
		$html .= "</span>";
			
		$html .= "<div style=\"display:none\" class=\"WSHLPMSG\" id=\"step1-help\" >".wfMsg("smw_wws_s1-help")."</div>";

		$html .= "<div id=\"step1-go\" class=\"OKButton\">";
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step1-go-img\" value=\"".wfMsg("smw_wsgui_nextbutton")."\" onclick=\"webServiceSpecial.processStep1()\" style=\"".$showButton."\">";
		$html .= "</div>";

		$html .= "</div>";



		//2. Specify method
		$html .= "<div id=\"step2\" class=\"StepDiv\" style=\"".$visible."\">";
			
		$html .= "<p class=\"step-headline\">".wfMsg("smw_wws_s2-intro");
		$html .= "<img id=\"step2-help-img\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"webServiceSpecial.displayHelp(2)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";
		$html .= "<div>".wfMsg("smw_wws_s2-method");
			
		$html .= "<select id=\"step2-methods\" size=\"1\">";
		$html .= $method;
		$html .= "</select></div>";
			
		$html .= "<div id=\"step2-help\" class=\"WSHLPMSG\" style=\"display:none\">".wfMsg("smw_wws_s2-help")."</div>";
		$html .= "<div id=\"step2-rest-help\" style=\"display:none\">".wfMsg("smw_wws_s2-REST-help")."</div>";
			
		$html .= "<div id=\"step2-go\" class=\"OKButton\">";
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step2-go-img\" value=\"".wfMsg("smw_wsgui_nextbutton")."\" style=\"".$showButton."\" onclick=\"webServiceSpecial.processStep2()\">";
		$html .= "</div>";
			
		$html .= "</div>";

		//3. Define Parameters
		$html .= "<div id=\"step3\" class=\"StepDiv\" style=\"".$visible."\">";

		$html .= "<p class=\"step-headline\">".wfMsg("smw_wws_s3-intro");
		$html .= "<img id=\"step3-help-img\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"webServiceSpecial.displayHelp(3)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";

		$html .= "<div id=\"step3-duplicates\" style=\"display:none\"><img src=\"".$smwgDIScriptPath."/skins/webservices/warning.png\"></img>";
		$html .= wfMsg("smw_wws_duplicate");
		$html .= "</div>";

		$html .= "<div id=\"step3-rest-intro\" style=\"display:none\"></div>";

		$html .= "<table id=\"step3-parameters\"><tr><th>".wfMsg('smw_wws_path')."</th><th>".wfMsg('smw_wws_use')."<span><input title=\"".wfMsg("smw_wws_selectall-tooltip")."\" type=\"checkbox\" id=\"step3-use\"/></span></th><th>".wfMsg('smw_wws_alias')."<span class=\"alias-generate\"><img title=\"".wfMsg("smw_wws_autogenerate-alias-tooltip-parameter")."\" id=\"step-3-alias-generate-button\" src=\"".$smwgDIScriptPath."/skins/webservices/Pencil_grey.png\"></img></span></th><th>".wfMsg('smw_wws_optional')."</th><th>".wfMsg('smw_wws_defaultvalue')."</th><th></th></tr></table>";
		
		$html .= '<a id="step3-add-rest-parameter" style="display: none" href="javascript:webServiceSpecial.appendRESTParameter()">'.wfMsg('smw_wws_s3_add_another_parameter').'</a>';

		$html .= "<div id=\"step3-help\" class=\"WSHLPMSG\" style=\"display:none\">".wfMsg("smw_wws_s3-help", $smwgDIScriptPath."/skins/webservices/Pencil_go.png")."</div>";
		$html .= "<div id=\"step3-rest-help\" class=\"WSHLPMSG\" style=\"display:none\">".wfMsg("smw_wws_s3-REST-help")."</div>";

		$html .= "<div id=\"step3-go\" class=\"OKButton\">";
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step3-go-img\" style=\"".$showButton."\" value=\"".wfMsg("smw_wsgui_nextbutton")."\" onclick=\"webServiceSpecial.processStep3()\">";
		$html .= "</div>";

		$html .= "</div>";
		
		// 4. Define Results
		$html .= "<div id=\"step4\" class=\"StepDiv\" style=\"".$visible."\">";

		$html .= "<p class=\"step-headline\">".wfMsg("smw_wws_s4-intro");
		$html .= "<img id=\"step4-help-img\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"webServiceSpecial.displayHelp(4)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";

		$html .= "<div id=\"step4-duplicates\" style=\"display:none\">";
		$html .= wfMsg("smw_wws_duplicate");
		$html .= "</div>";

		$html .= "<div id=\"step4-rest-intro\" style=\"display:none\"></div>";

		$html .= "<table id=\"step4-results\"><tr><th>".wfMsg('smw_wws_path')."</th><th>".wfMsg('smw_wws_use')."<span onclick=\"webServiceSpecial.useResults()\"><input title=\"".wfMsg("smw_wws_selectall-tooltip")."\" type=\"checkbox\" id=\"step4-use\"/></span></th><th>".wfMsg('smw_wws_alias')."<span class=\"alias-generate\" onclick=\"webServiceSpecial.generateResultAliases(true)\"><img id=\"step-4-alias-generate-button\" title=\"".wfMsg("smw_wws_autogenerate-alias-tooltip-resultpart")."\" src=\"".$smwgDIScriptPath."/skins/webservices/Pencil_grey.png\"></img></span></th><th>".wfMsg('smw_wws_format')."</th><th>".wfMsg('smw_wws_path')."</th><th></th></tr></table>";
		
		$html .= '<a id="step4-add-rest-result-part" style="display: none" href="javascript:webServiceSpecial.appendRESTResultPart()">'.wfMsg('smw_wws_s4_add_rest_result_part').'</a>';
		
		//Add button for displaying the namespace prefix table
		$html .= '<div id="step4-nss-header" style="display: none; cursor: pointer">';
		$html .= '<img id="step4-display-nss" src="'.$smwgDIScriptPath.'/skins/webservices/right.png"></img>';
		$html .= '<span>'.wfMsg('smw_wws_add_prefixes').'</span>';
		$html .= '</div>';
		
		//Add table for defining namespace prefixes
		$html .= "<table id=\"step4-nss\" style=\"padding-top:20px; display: none\"><tr><th>".wfMsg('smw_wws_nss_prefix')."</th><th>".wfMsg('smw_wws_nss_url')."</th><th></th></tr></table>";

		$html .= '<a id="step4-add-nsp" style="display: none" href="javascript:webServiceSpecial.appendNSPrefix()">'.wfMsg('smw_wws_s4_add_ns_prefix').'</a>';
		
		//for triplification
		$displayTriplificationContainer = ' style="display: none" ';
		if(defined( 'LOD_LINKEDDATA_VERSION')){
			$displayTriplificationContainer = '';
		}
		
		$html .= '<table id="step4-enable-triplification" '.$displayTriplificationContainer.'>';
		
		$html .= '<tr>';
		$html .= '<td onclick="webServiceSpecial.displaySubjectCreationPattern()"><input id="step4-enable-triplification-checkbox" type="checkbox" '.$subjectCreationCheckboxChecked.'/></td>';
		$html .= '<td><span>'.wfMsg('smw_wws_enable_triplification').'</span></td>';
		$html .= '<td></td>';
		$html .= "</tr>";
		
		$triplificationDetailsVisible = strpos($subjectCreationCheckboxChecked, 'true') ? '' : ' style="display: none" ';
		$html .= '<tr id="step4-enable-triplification-details" '.$triplificationDetailsVisible.'>';
		$html .= '<td/>';
		$html .= '<td id="step4-enable-triplification-scp-label"><span>'.wfMsg('smw_wws_enable_triplification-intro').'</span></td>';
		$html .= '<td>';
		$html .= '<textarea id="step4-enable-triplification-input" rows="3" cols="64" onblur="webServiceSpecial.setSubjectCreationPatternCursorPos()" value="'.$subjectCreationPattern.'">'.$subjectCreationPattern.'</textarea><br/>';
		$html .= '<a id="step4-add-alias-to-input" href="javascript:void(0)" onclick="webServiceSpecial.initSubjectCreationPatternAliasClick()">'.wfMsg('smw_wws_enable_triplification-scp-add').'</a><br/>';
		$html .= '<span id="step4-add-alias-to-input-stop-text" style="display: none">'.wfMsg('smw_wws_enable_triplification-scp-stop-add').'</span>';
		$html .= '<span id="step4-add-alias-to-input-note" style="display:none">'.wfMsg('smw_wws_enable_triplification-scp-add-note').'</span>';
		$html .= '</td>';
		$html .= "</tr>";
		
		$html .= '</table>';
		
		$triplificationHelp = "";
		if(defined( 'LOD_LINKEDDATA_VERSION')){
			$triplificationHelp = wfMsg("smw_wws_s4-help-triplification");
		}
		
		$html .= "<div id=\"step4-help\" class=\"WSHLPMSG\" style=\"display:none\">".wfMsg("smw_wws_s4-help").$triplificationHelp."</div>";
		$html .= "<div id=\"step4-rest-help\" class=\"WSHLPMSG\" style=\"display:none\">".wfMsg("smw_wws_s4-REST-help").$triplificationHelp."</div>";
		$html .= "<div id=\"step4-ld-help\" class=\"WSHLPMSG\" style=\"display:none\">".wfMsg("smw_wws_s4-LD-help").$triplificationHelp."</div>";

		$html .= "<div id=\"step4-go\" class=\"OKButton\">";
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step4-go-img\" style=\"".$showButton."\" value=\"".wfMsg("smw_wsgui_nextbutton")."\" onclick=\"webServiceSpecial.processStep4()\">";
		$html .= "</div>";

		$html .= "</div>";

		// 5. Define updatae policy
		$html .= "<div id=\"step5\" class=\"StepDiv\" style=\"".$visible."\" >";
		$html .= "<p class=\"step-headline\">".wfMsg("smw_wws_s5-intro");
		$html .= "<img id=\"step5-help-img\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"webServiceSpecial.displayHelp(5)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";

		$html .= "<table id=\"step5-policies\">";

		$html .= "<tr><td><span>Display policy: </span></td>";
		$html .= "<td><input id=\"step5-display-once\" ".$displayOnce." onfocus=\"webServiceSpecial.selectRadioOnce('step5-display-once')\" type=\"radio\" name=\"step5-display\" value=\"once\">Once</input>";
		$html .= "<span><input id=\"step5-display-max\" ".$displayMax." type=\"radio\" name=\"step5-display\" value=\"\">Max age</input></span></td>";

		$html .= "<td><input type=\"text\" id=\"step5-display-days\" onfocus=\"webServiceSpecial.selectRadio('step5-display-max')\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span>".wfMsg('smw_wws_days')."</span>";
		$html .= "<input type=\"text\" id=\"step5-display-hours\" onfocus=\"webServiceSpecial.selectRadio('step5-display-max')\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span>".wfMsg('smw_wws_hours')."</span>";
		$html .= "<input type=\"text\" id=\"step5-display-minutes\" onfocus=\"webServiceSpecial.selectRadio('step5-display-max')\" size=\"7\" maxlength=\"10\" ".$displayMinutes."/>";
		$html .= "<span>".wfMsg('smw_wws_minutes')."</span>";
		$html .= "</td></tr>";

		$html .= "<tr><td><span>Query policy: </span></td>";
		$html .= "<td><input id=\"step5-query-once\" ".$queryOnce." onfocus=\"webServiceSpecial.selectRadioOnce('step5-query-once')\" type=\"radio\" name=\"step5-query\" value=\"once\">Once</input>";
		$html .= "<span><input id=\"step5-query-max\" ".$queryMax." type=\"radio\" name=\"step5-query\" value=\"\">Max age</input></span></td>";

		$html .= "<td><input type=\"text\" id=\"step5-query-days\" onfocus=\"webServiceSpecial.selectRadio('step5-query-max')\" size=\"7\" maxlength=\"10\" />";
		$html .= "<span>".wfMsg('smw_wws_days')."</span>";
		$html .= "<input type=\"text\" id=\"step5-query-hours\" size=\"7\" onfocus=\"webServiceSpecial.selectRadio('step5-query-max')\" maxlength=\"10\" />";
		$html .= "<span>".wfMsg('smw_wws_hours')."</span>";
		$html .= "<input type=\"text\" id=\"step5-query-minutes\" size=\"7\" onfocus=\"webServiceSpecial.selectRadio('step5-query-max')\" maxlength=\"10\" ".$queryMinutes."/>";
		$html .= "<span>".wfMsg('smw_wws_minutes')."</span>";
		$html .= "</td></tr>";

		$html .= "<tr><td></td>";
		$html .= "<td><span> Delay value (".wfMsg('smw_wws_inseconds')."): </span></td>";
		$html .= "<td><input type=\"text\" id=\"step5-delay\" size=\"7\" maxlength=\"10\" ".$delayValue."/>";
		$html .= "</td></tr>";

		$html .= "<tr></tr>";

		$html .= "<tr><td><span> Span of life (".wfMsg('smw_wws_indays')."): </span></td>";
		$html .= "<td><input type=\"text\" id=\"step5-spanoflife\" size=\"7\" maxlength=\"10\" ".$spanOfLife."/></td>";
		$html .= "<td><span> Expires after update: </span>";
		$html .= "<input id=\"step5-expires-yes\" ".$expires." type=\"radio\" name=\"step5-expires\" value=\"once\">".wfMsg('smw_wws_yes')."</input>";
		$html .= "<input id=\"step5-expires-no\" ".$expiresno." type=\"radio\" name=\"step5-expires\" value=\"\">".wfMsg('smw_wws_no')."</input>";

		$html .= "</td></tr></table>";

		$html .= "<div id=\"step5-help\" class=\"WSHLPMSG\" style=\"display:none\">".wfMsg("smw_wws_s5-help")."</div>";

		$html .= "<div id=\"step5-go\" class=\"OKButton\">";
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step5-go-img\" style=\"".$showButton."\" value=\"".wfMsg("smw_wsgui_nextbutton")."\" onclick=\"webServiceSpecial.processStep5()\">";
		$html .= "</div>";

		$html .= "</div>";

		// 6. Specify name
		$html .= "<div id=\"step6\" class=\"StepDiv\" style=\"".$visible."\">";
		$html .= "<p class=\"step-headline\">".wfMsg("smw_wws_s6-intro");
		$html .= "<img id=\"step6-help-img\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"webServiceSpecial.displayHelp(6)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>";
		$html .= "</p>";

		$html .= wfMsg("smw_wws_s6-name");
		$html .= "<input id=\"step6-name\" type=\"text\" onkeypress=\"webServiceSpecial.checkEnterKey(event, 'step6')\" size=\"50\" maxlength=\"300\" ".$name."/>";

		$html .= "<div id=\"step6-help\" class=\"WSHLPMSG\" style=\"display:none\">".wfMsg("smw_wws_s6-help")."</div>";

		$html .= "<div id=\"step6-go\" class=\"OKButton\">";
		$html .= "<input type=\"button\" class=\"OKButton\" id=\"step6-go-img\" value=\"".wfMsg("smw_wsgui_savebutton")."\" onclick=\"webServiceSpecial.processStep6()\">";
		$html .= "</div>";

		$html .= "</div>";

		//7. show #ws-usage
		$html .= "<div id=\"step7\" style=\"display: none\">";
		$html .= "<span>".wfMsg('smw_wws_yourws')."\"";
		$html .= "<span id=\"step7-name\"></span>";
		$html .= "\"".wfMsg('smw_wws_succ_created');
		$url = Title::makeTitleSafe(NS_SPECIAL, "dataimportrepository")->getInternalURL();
		//$html .= "<div id=\"step7-container\"></div>";
		$html .= "<a href=\"".$url."\">".wfMsg('smw_wws_succ_created-3')."</a>".wfMsg('smw_wws_succ_created-4')."</span>";
		//$html .= "<img onclick=\"webServiceSpecial.processStep7()\" src=\"".$smwgDIScriptPath."/skins/webservices/Control_play.png\" class=\"OKButton\"></img>";
		$html .= "<br/><input type=\"button\" class=\"OKButton\" id=\"step7-go-img\" value=\"".wfMsg('smw_wws_new')."\" onclick=\"webServiceSpecial.processStep7()\"/>";
		$html .= "</div>";


		//errors
		$html .= "<div id=\"errors\" class=\"StepDiv\" style=\"display: none;\">";
		$html .= "<h2>".wfMsg('smw_wws_error_headline')."</h2>";
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
			if(strtolower($wwsd->getProtocol()) == "soap"){
				$html .= $this->getMergedParameters($wwsd, false);
				$html .= $this->getMergedParameters($wwsd, true);
			} else if(strtolower($wwsd->getProtocol()) == "rest"){
				$html .= $this->getRESTMergedParameters($wwsd, false, "rest");
				$html .= $this->getRESTMergedParameters($wwsd, true, "rest");
			} else if(strtolower($wwsd->getProtocol()) == "linkeddata"){
				$html .= $this->getRESTMergedParameters($wwsd, false, "ld");
				$html .= $this->getRESTMergedParameters($wwsd, true, "ld");
			}
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
			$wsdlParameters = WebService::flattenParam("", $rawParameters[0], $wsClient);
		} else {
			//todo: handle no params
			$numParam = count($rawParameters);
			for ($i = 1; $i < $numParam; $i++) {
				$pName = $rawParameters[$i][0];
				$pType = $rawParameters[$i][1];
				$tempFlat = WebService::flattenParam($pName, $pType, $wsClient);
				$wsdlParameters = array_merge($wsdlParameters , $tempFlat);
			}
		}

		$mergedParameters = array();
		$unsetwwsdParameters = array();

		//todo: handle overflows
		foreach($wsdlParameters as $wsdlParameter){
			if(!$result){
				$wsdlParameter = substr($wsdlParameter, 1);
			}
				
			$wsdlParameterSteps = explode("/", $wsdlParameter);

			$found = false;

			foreach($wwsdParameters as $key => $wwsdParameter){
				if(!is_null($wwsdParameter['subject'])){
					continue;
				}
				
				if(!$result){
					$matchedPath = "/";
				} else {
					$matchedPath = "//";
				}
				$wwsdParameterSteps = explode("/", $wwsdParameter["path"]);

				if(count($wsdlParameterSteps) != count($wwsdParameterSteps)){
					continue;
				}

				for($k=0; $k < count($wsdlParameterSteps); $k++){
					if($wsdlParameterSteps[$k] == ""
					&& $wwsdParameterSteps[$k] == ""){
						continue;
					}

					$dupPos = strpos($wsdlParameterSteps[$k], "##duplicate");
					$overflowPos = strpos($wsdlParameterSteps[$k], "##overflow");
					//$bracketPos = strpos($wwsdParameterSteps[$k], "[");

					$wwsdParameterStep = $wwsdParameterSteps[$k];

					//if($bracketPos > 0){
					//	$wwsdParameterStep = substr($wwsdParameterStep, 0, $bracketPos);
					//	$dupPos = $dupPos."-";
					//}
					if(@ strpos($wsdlParameterSteps[$k], $wwsdParameterStep) === 0){
						$matchedPath .= "/".$wwsdParameterSteps[$k];
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
				if(strlen($matchedPath) > 0 && $matchedPath != "//"){
					$found = true;
					$a = array();
					$a["name"] = $wwsdParameter["name"]."";
					$a["path"] = substr($matchedPath, 1);
					if(!$result){
						$a["defaultValue"] = $wwsdParameter["defaultValue"]."";
						$a["optional"] = $wwsdParameter["optional"]."";
						if(strlen($a["optional"]."") == 0){
							$a["optional"] = "##";
						}
						if(strlen($a["defaultValue"]."") == 0){
							$a["defaultValue"] = "##";
						}
						$subParameter = $this->getSubParameter($wwsdParameter);
						if(strlen($subParameter) > 0){
							$a["subParameter"] = $subParameter;
						} else {
							$a["subParameter"] = "##";
						}
						$mergedParameters[$a["path"]] = $a;
					} else {
						$a["xpath"] = $wwsdParameter["xpath"]."";
						$a["json"] = $wwsdParameter["json"]."";
						if(strlen($a["xpath"]."") > 0 || strlen($a["json"]."") > 0){
							if(!array_key_exists($a["path"]."####",$mergedParameters)){
								$found = false;
							}
						}
						if(strlen($a["xpath"]."") == 0){
							$a["xpath"] = "##";
						}
						if(strlen($a["json"]."") == 0){
							$a["json"] = "##";
						}
						$mergedParameters[$a["path"].$a["json"].$a["xpath"]] = $a;
					}
					$unsetwwsdParameters[$wwsdParameter["path"].""] = true;
					continue;
				}
			}
			if(!$found){
				$a = array();
				$a["path"] = $wsdlParameter;
				$a["name"] = "##";
				if(!$result){
					$a["optional"] = "##";
					$a["defaultValue"] = "##";
					$a["subParameter"] = "##";
					
					$mergedParameters[$a["path"]] = $a;
				} else {
					$a["xpath"] = "##";
					$a["json"] = "##";
					$mergedParameters[$a["path"].$a["json"].$a["xpath"]] = $a;
				}
			}
		}

		foreach($wwsdParameters as $wsParameter){
			//do not deal with triplification instruction
			if(!is_null($wwsdParameter['subject'])){
					continue;
			}
			if(!array_key_exists($wsParameter["path"]."", $unsetwwsdParameters)){
				$o = array();
				$o["name"] = $wsParameter["name"]."";
				if($result){
					$o["path"] = "##unmatched".$wsParameter["path"]."";
				} else {
					$o["path"] = $wsParameter["path"]."";
				}

				if(!$result){
					$o["defaultValue"] = $wsParameter["defaultValue"]."";
					$o["optional"] = $wsParameter["optional"]."";
					if(strlen($o["optional"]."") == 0){
						$o["optional"] = "##";
					}
					if(strlen($o["defaultValue"]."") == 0){
						$o["defaultValue"] = "##";
					}
					$subParameter = $this->getSubParameter($wsParameter);
					if(strlen($subParameter) > 0){
						$a["subParameter"] = $subParameter;
					} else {
						$a["subParameter"] = "##";
					}
					$mergedParameters[$o["path"]] = $o;
				} else {
					$o["json"] = $wsParameter["json"]."";
					$o["xpath"] = $wsParameter["xpath"]."";
					if(strlen($o["json"]."") == 0){
						$o["json"] = "##";
					}
					if(strlen($o["xpath"]."") == 0){
						$o["xpath"] = "##";
					}
					$mergedParameters[$o["path"].$o["json"].$o["xpath"]] = $o;
				}
			}
		}
		ksort($mergedParameters);



		$html = "";
		if($result){
			$html .= "<span id=\"editresults\" style=\"display: none\">";
		} else {
			$html .= "<span id=\"editparameters\" style=\"display: none\">";
		}

		$html .= "soap;";
		foreach($mergedParameters as $mergedParameter){
			$html .= $mergedParameter["name"].";";
			// remove parameter paths that start with "//" 
			
			$html .= $mergedParameter["path"].";";
			if(!$result){
				if(array_key_exists("optional", $mergedParameter)){
					$html .= $mergedParameter["optional"].";";
				} else {
					$html .= "##;";
				}
				if(array_key_exists("defaultValue", $mergedParameter)){
					$html .= $mergedParameter["defaultValue"].";";
				} else {
					$html .= "##;";
				}
				if(array_key_exists("subParameter", $mergedParameter)){
					$html .= $mergedParameter["subParameter"].";";
				} else {
					$html .= "##;";
				}
				
			} else {
				$html .= $mergedParameter["xpath"].";";
				$html .= $mergedParameter["json"].";";
			}
		}

		$html .= "</span>";
		return $html;
	}

	private function getRESTMergedParameters($wwsd, $result, $protocol){
		if($result){
			$wwsdParameters = new SimpleXMLElement($wwsd->getResult());
		} else {
			$wwsdParameters = new SimpleXMLElement("<p>".$wwsd->getParameters()."</p>");
		}

		$wwsdParameters = $wwsdParameters->children();

		$html = "";
		if($result){
			$html .= "<span id=\"editresults\" style=\"display:none\">";
			$prefixes = "<span id=\"editprefixes\" style=\"display: none\">";
		} else {
			$html .= "<span id=\"editparameters\" style=\"display:none\">";
		}

		if($protocol == "rest"){
			$html .= "rest;";
		} else {
			$html .= "ld;";
		}
		foreach($wwsdParameters as $key => $wwsdParameter){
			if(!$result){
				$html .= $wwsdParameter["path"].";";
				$html .= $wwsdParameter["name"].";";
				if(strlen($wwsdParameter["optional"]."") > 0){
					$html .= $wwsdParameter["optional"].";";
				} else {
					$html .= "##;";
				}
				if(strlen($wwsdParameter["defaultValue"]."") > 0){
					$html .= $wwsdParameter["defaultValue"].";";
				} else {
					$html .= "##;";
				}
				$subParameter = $this->getSubParameter($wwsdParameter);
				if(strlen($subParameter) > 0){
					$html .= $subParameter.";";
				} else {
					$html .= "##;";
				}
			} else {
				//ignore special purpose result parts for ld wss
				if($protocol != 'ld' || 
						((''.$wwsdParameter["property"] != DI_ALL_SUBJECTS || ''.$wwsdParameter["name"] != DI_ALL_SUBJECTS_ALIAS)
						&& ($protocol != 'ld' || ''.$wwsdParameter["property"] != DI_ALL_PROPERTIES || ''.$wwsdParameter["name"] != DI_ALL_PROPERTIES_ALIAS)
						&& ($protocol != 'ld' || ''.$wwsdParameter["property"] != DI_ALL_OBJECTS || ''.$wwsdParameter["name"] != DI_ALL_OBJECTS_ALIAS))){
					if(strlen(''.$wwsdParameter['name']) > 0){
						$html .= $wwsdParameter["name"].";";
						if(strlen($wwsdParameter["xpath"]."") > 0){
							$html .= "xpath;";
							$html .= $wwsdParameter["xpath"].";";
						} else if(strlen($wwsdParameter["json"]."") > 0){
							$html .= "json;";
							$html .= $wwsdParameter["json"].";";
						} else if(strlen($wwsdParameter["property"]."") > 0){
							$html .= "property;";
							$html .= $wwsdParameter["property"].";";
						} else {
							$html .= "##;";
							$html .= "##;";
						}
					} else if (strlen("".$wwsdParameter['prefix']) > 0){
						$prefixes .= $wwsdParameter['prefix'].";";
						$prefixes .= $wwsdParameter['uri'].";";
					}
				}
			}
		}
		
		$html .= "</span>";
		
		if($result){
			$html .= $prefixes."</span>";
		}
		
		return $html;
	}

	private function getSubParameter($parameter){
		$parameterXML = $parameter->asXML();
		$subParameterXML = substr($parameterXML, 
			strpos($parameterXML, ">") + 1, 
			strrpos($parameterXML, "</") - strpos($parameterXML, ">") - 1);
		return rawurlencode($subParameterXML);
	}
}