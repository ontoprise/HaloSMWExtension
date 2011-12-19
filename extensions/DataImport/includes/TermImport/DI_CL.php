<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */
/**
 * @file
 * @ingroup DITermImport
 * 
 * @author Thomas Schweitzer
 */

// register ajax calls
global $wgAjaxExportList;
$wgAjaxExportList[] = 'dif_ti_connectDAM';

class DICL {

	public function execute() {
		global $wgOut, $wgRequest, $wgScriptPath, $smwgDIScriptPath;
		
		$html = "<div id=\"menue\">";
		$html .= "<div id=\"breadcrumb-menue\" class=\"BreadCrumpContainer\">";
		$html .= "<span id=\"menue-step1\" class=\"ActualMenueStep\">".wfMsg('smw_ti_menuestep1')."</span><span class=\"HeadlineDelimiter\"></span>";
		$html .= "<span id=\"menue-step2\" class=\"TodoMenueStep\">".wfMsg('smw_ti_menuestep2')."</span>";
		$html .= "</div></div>";
		
		$html .= "<div id=\"summary\"></div>";
				
		$html .= "<div id=\"top-container\">";
		
		$damsHTML = DIDAMRegistry::getDAMsHTML();
		$html .= "<div><div id=\"dal-content\"><b>".wfMsg('smw_ti_dam-heading')."</b>" .
				 	"<div id=\"dalid\">" . $damsHTML."</div>" .
				 	"<div id=\"daldesc\">" . "</div></div>" .
					"<div class=\"arrow\"><img src=\"$wgScriptPath/extensions/DataImport/skins/TermImport/images/arrow.png\"/></div>".
				 "</div>";

		$html .= "<div id=\"source-spec\"><b>".wfMsg('smw_ti_module-data-heading')."</b>" .
				"<table height=\"200px\"><tr><td valign=\"top\"><i>".wfMsg('smw_ti_selectDAM')."</i></td></tr></table>".
				"</div>";
		$html .= "</div>"; //top-container
		
		$html .= "<div style=\"display: none\" id=\"loading-container\">";
		$html .= "<br/><br/>";
		$html .= "<b>Loading... </b>";
		$html .= "<img src=\"".$smwgDIScriptPath."/skins/TermImport/images/ajax-loader.gif\"/>";
		$html .= "</div>";

		$html .= "<div id=\"bottom-container\">" .
					"<div id=\"extras\">" .
							"<div id=\"extras-left\">" .
								"<div id=\"importset\"><div class=\"input-field-heading\">" 
								. wfMsg('smw_ti_selectImport-heading') .
								"<img id=\"help-img1\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"termImportPage.displayHelp(1)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>".
								"</div>".
								"<div id=\"help1\" class=\"TIHelpMessage\" style=\"display: none\">".
								"<span>".wfMsg('smw_ti_help')."</span> ".
								wfMsg('smw_ti_selectImport-help')."</div>".
								wfMsg('smw_ti_selectImport-label') .								
								"<select name=\"importset\" id=\"importset-input-field\" size=\"1\" onchange=\"termImportPage.importSetChanged(event, this)\"></select>" .
									"<br><br>" .
								"</div>" . //importset
								"<br/><br/><div id=\"policy\">".
								 	"<div id=\"policy-input\">" .
										"<div class=\"input-field-heading\">" 
										. wfMsg('smw_ti_inputpolicy-heading') .
										"<img id=\"help-img2\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"termImportPage.displayHelp(2)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>".
										"</div>".
										"<div id=\"help2\" class=\"TIHelpMessage\" style=\"display: none\">".
										"<span>".wfMsg('smw_ti_help')."</span> ".
										wfMsg('smw_ti_inputpolicy-help')."</div>".
										"<span>" .wfMsg('smw_ti_inputpolicy-label')."</span>" . 
										"<input type=\"radio\" name=\"policy_type\" value=\"regex\" checked><span>RegEx</span><input type=\"radio\" name=\"policy_type\" value=\"term\">Term</span>".
										"&nbsp;&nbsp;<input name=\"policy\" id=\"policy-input-field\" type=\"text\" size=\"20\">" .
										"&nbsp;&nbsp;<img onclick=\"termImportPage.getPolicy(event, this)\" src=\"$wgScriptPath/extensions/DataImport/skins/TermImport/images/Add.png\" />".
										"<br/><br/><div>".wfMsg('smw_ti_inputpolicy-defined').
										"<img onclick=\"termImportPage.deletePolicy(event, this)\" src=\"$wgScriptPath/extensions/DataImport/skins/TermImport/images/Delete-silk.png\" /></div>".
									"</div>" .	
									"<select id=\"policy-textarea\" name=\"policy-out\" size=\"3\" multiple>" .  
									"</select><br><br>".
									"<div id=\"hidden_pol_type\"></div>" .
								"<br><br></div>" . //policy
								"<div id=\"creation-pattern\">" .
									"<br/><br/><div class=\"input-field-heading\">".
									wfMsg('smw_ti_creation-pattern-heading').
									"<img id=\"help-img3\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"termImportPage.displayHelp(3)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>".
									"</div>".
									"<div id=\"help3\" class=\"TIHelpMessage\" style=\"display: none\">".
									"<span >".wfMsg('smw_ti_help')."</span> ".
									wfMsg('smw_ti_creation-pattern-help')."</div>".
									"<input type=\"radio\" name=\"creation-pattern\" 
										value=\"annotations\" checked onchange=\"termImportPage.showOrHideDelimiterInput(event)\"><span>".
										wfMsg('smw_ti_creation-pattern-label-1')."</span>".
									"<input id=\"creationpattern-checkbox\" type=\"radio\" name=\"creation-pattern\" value=\"template\" onchange=\"termImportPage.showOrHideDelimiterInput(event)\">".
										wfMsg('smw_ti_creation-pattern-label-2').":</span>".
									"&nbsp;&nbsp;".
									"<input name=\"template\" id=\"template-input-field\" type=\"text\"
										class=\"wickEnabled\" constraints=\"namespace: ".NS_CATEGORY."\" 
										size=\"20\" onKeyPress=\"termImportPage.changeBackground(event, this)\"/>".
								"</div>" . //template
								"<div id=\"delimiter\" style=\"display: none\">" .
									"<br/><br/><div class=\"input-field-heading\">".
									wfMsg('smw_ti_delimiter-heading').
									"<img id=\"help-img-10\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"termImportPage.displayHelp('10')\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>".
									"</div>".
									"<div id=\"help10\" class=\"TIHelpMessage\" style=\"display: none\">".
									"<span >".wfMsg('smw_ti_help')."</span> ".
									wfMsg('smw_ti_delimiter-help')."</div>".
									wfMsg('smw_ti_delimitery-label').
									"<input name=\"delimiter\" id=\"delimiter-input-field\" type=\"text\"
										size=\"5\" onKeyPress=\"termImportPage.changeBackground(event, this)\"/>".
								"</div>" . //delimiter
								"<div id=\"categories\">" .
									"<br/><br/><div class=\"input-field-heading\">".
									wfMsg('smw_ti_category-heading').
									"<img id=\"help-img-9\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"termImportPage.displayHelp('9')\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>".
									"</div>".
									"<div id=\"help9\" class=\"TIHelpMessage\" style=\"display: none\">".
									"<span >".wfMsg('smw_ti_help')."</span> ".
									wfMsg('smw_ti_category-help')."</div>".
									wfMsg('smw_ti_category-label').
									"<input name=\"category\" id=\"categories-input-field\" type=\"text\"
										class=\"wickEnabled\" constraints=\"namespace: ".NS_CATEGORY."\" 
										size=\"20\" onKeyPress=\"termImportPage.changeBackground(event, this)\"/>".
								"</div>" . //category	
								"<div id=\"conflict\">" .
									"<br/><br/><div class=\"input-field-heading\">".
									wfMsg('smw_ti_conflictpolicy-heading').
									"<img id=\"help-img4\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"termImportPage.displayHelp(4)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>".
									"</div>".
									"<div id=\"help4\" class=\"TIHelpMessage\" style=\"display: none\">".
									"<span>".wfMsg('smw_ti_help')."</span> ".
									wfMsg('smw_ti_conflictpolicy-help')."</div>".
									wfMsg('smw_ti_conflictpolicy-label').
									//todo: language
									"<select name=\"conflict\" id=\"conflict-input-field\">" .
										//todo: compute this dynamically according to the available cps 
										"<option value=\"overwrite\">overwrite</option>" .
										"<option value=\"ignore\">preserve current versions</option>" .
										"<option value=\"append some\" style=\"display:none\">append selected values</option>" .
									"</select>" .
								"</div>" . //conflict
								"<div id=\"ti-update-policy\">" .
									"<br/><br/><div class=\"input-field-heading\">".
									wfMsg('smw_ti_update_policy-heading')."&nbsp;" .
									"<img id=\"help-img5\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"termImportPage.displayHelp(5)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>".
									"</div>".
									"<div id=\"help5\" class=\"TIHelpMessage\" style=\"display: none\">".
									"<span>".wfMsg('smw_ti_help')."</span> ".
									wfMsg('smw_ti_update_policy-help')."</div>".
									"<input type=\"radio\" name=\"update_policy_type\" value=\"once\" checked><span>Once</span>".
									"<input id=\"update-policy-checkbox\" type=\"radio\" name=\"update_policy_type\" value=\"maxage\">Max age:</span>".
									"&nbsp;&nbsp;<input id=\"ti-update-policy-input-field\" onKeyPress=\"termImportPage.changeBackground(event, this)\" size=\"10\"/>&nbsp;in minutes" .
									"</div>" . 
									"<div id=\"ti-name\">" .
									"<br><br><div class=\"input-field-heading\">".
									wfMsg('smw_ti_ti_name-heading')."&nbsp;" .
									"<img id=\"help-img6\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"termImportPage.displayHelp(6)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>".
									"</div>".
									"<div id=\"help6\" class=\"TIHelpMessage\" style=\"display: none\">".
									"<span>".wfMsg('smw_ti_help')."</span> ".
									wfMsg('smw_ti_ti_name-help')."</div>".
									wfMsg('smw_ti_ti_name-label').
									"<input id=\"ti-name-input-field\" onKeyPress=\"termImportPage.changeBackground(event, this)\"/>" .
								"<br/><br/></div>" . //ti name
							"</div>" . //extras-left
							"<div id=\"extras-right\">" .
								"<div class=\"input-field-heading\">".
								wfMsg('smw_ti_properties-heading').
								"<img id=\"help-img7\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"termImportPage.displayHelp(7)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>".
								"</div>".
								"<div id=\"help7\" class=\"TIHelpMessage\" style=\"display: none\">".
								"<span>".wfMsg('smw_ti_help')."</span> ".
								wfMsg('smw_ti_properties-help')."</div>".
								wfMsg('smw_ti_properties-label').
								"<div id=\"attrib-articles\">" .
									"<div id=\"attrib\"></div>" .
									"<div id=\"articles\">".
										"<div class=\"input-field-heading\">".
										wfMsg('smw_ti_articles-heading')."&nbsp;" .
										"<img id=\"help-img8\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"termImportPage.displayHelp(8)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>".
										"</div>".
										"<div id=\"help8\" class=\"TIHelpMessage\" style=\"display: none\">".
										"<span>".wfMsg('smw_ti_help')."</span> ".
										wfMsg('smw_ti_articles-help')."</div>".
										wfMsg('smw_ti_articles-label1').
										"<span id=\"article-count\"></span>".
										wfMsg('smw_ti_articles-label2').
										"<div id=\"article_table\" class=\"scrolling\"></div></div>" .
								"</div>" . //attrib-articles
							"</div>" . //extras-right
					"</div>". //extras
					"<div id=\"extras-bottom\" align=\"left\"></div>".
					"<div style=\"display: none\" id=\"loading-bottom-container\">".
					"<b>Loading... </b>".
					"<img src=\"".$smwgDIScriptPath."/skins/TermImport/images/ajax-loader.gif\"/><br/><br/>".
					"</div>";
										

		$html .= "</div>"; //bottom-container
		
		$termImportName = $wgRequest->getVal( 'tiname' );
		if($termImportName != null){
			$html .= $this->embedEditTermImportData($termImportName);
		}

		$wgOut->addHTML($html);
	}

	/*
	 * creates the embedded html spans for the edit term import gui
	 */
	private function embedEditTermImportData($termImportName){
		$html = '<span id="editDataSpan" style="display: none">';
		
		$xmlString = smwf_om_GetWikiText('TermImport:'.$termImportName);
		
		$start = strpos($xmlString, "<ImportSettings>");
		$end = strpos($xmlString, "</ImportSettings>") + 17 - $start;
		$xmlString = substr($xmlString, $start, $end);
		
		$simpleXMLElement = new SimpleXMLElement($xmlString);
		
		$damId = $simpleXMLElement->xpath("//DALModules/Module/id/text()");
		if(@strlen(trim(''.$damId[0])) == 0){
			$damId = $simpleXMLElement->xpath("//DALModule/id/text()");
		}
		$damId = ''.$damId[0]; 
		$html .= '<span id="dalId-ed">'.$damId.'</span>';
		
		$dataSource = $simpleXMLElement->xpath("//DataSource");
		
		
		//compute complete data source
		$dataSourceDef = DIDAMRegistry::getDAM($damId)->getSourceSpecification();
		$dataSourceDef = new SimpleXMLElement($dataSourceDef);
		foreach ($dataSource[0]->children() as $child) {
			$tag = $child->getName();
			$value = (string)$child;
			$dataSourceDef->$tag = $value;
		}
		$dataSource = $dataSourceDef->asXML();
		
		$html .= '<span id="dataSource-ed">'.rawurlencode($dataSource).'</span>';
		
		$importSet = $simpleXMLElement->xpath("//ImportSets/ImportSet/Name/text()");
		@ $html .= '<span id="importSet-ed">'.$importSet[0].'</span>';
		
		$regex = $simpleXMLElement->xpath("//InputPolicy/terms/regex/text()");
		$regex = trim(implode(",", $regex));
		$html .= '<span id="regex-ed">'.$regex.'</span>';
		
		$terms = $simpleXMLElement->xpath("//InputPolicy/terms/term/text()");
		$terms = trim(implode(",", $terms));
		$html .= '<span id="terms-ed">'.$terms.'</span>';
		
		$properties = $simpleXMLElement->xpath("//InputPolicy/properties/property/text()");
		$properties = implode(",", $properties);
		$html .= '<span id="properties-ed">'.$properties.'</span>';
		
		$template = $simpleXMLElement->xpath("//CreationPattern/TemplateName/text()");
		@$template = ''.$template[0];
		$html .= '<span id="templateName-ed">'.$template.'</span>';
		
		$delimiter = $simpleXMLElement->xpath("//CreationPattern/Delimiter/text()");
		@$delimiter = ''.$delimiter[0];
		if($delimiter == '') $delimiter = ',';
		$html .= '<span id="delimiter-ed">'.$delimiter.'</span>';
		
		$extraCategories = $simpleXMLElement->xpath("//CreationPattern/ExtraCategories/text()");
		@$extraCategories = ''.$extraCategories[0];
		$html .= '<span id="extraCategories-ed">'.$extraCategories.'</span>';
		
		$conflictPolicy = $simpleXMLElement->xpath("//ConflictPolicy/Name/text()");
		$html .= '<span id="conflictPolicy-ed">'.$conflictPolicy.'</span>';

		$html .= '<span id="termImportName-ed">'.$termImportName.'</span>';
		
		$updatePolicy = $simpleXMLElement->xpath("//UpdatePolicy/maxAge/@value");
		$updatePolicy = $updatePolicy ? $updatePolicy[0] : "0"; 
		$html .= '<span id="updatePolicy-ed">'.$updatePolicy.'</span>';
		
		$damDescription = DIDAMRegistry::getDAMDesc($damId);
		$html .= '<span id="dal-desc">'.$damDescription.'</span>';
		
		return $html;	
	}
	
	
	/**
	 * Imports the vocabulary according to the given policies. The content of the
	 * wiki is updated. This method starts a bot with the following parameters:
	 *
	 */
	public static function importTerms($termImportName, $async = true) {
		if($async){
			//Called by Term Import Special Page
		  	$param = "termImportName=".$termImportName;
			$taskID = GardeningBot::runBot('smw_termimportbot', $param);
			if(!is_int($taskID)) {
				$msg = wfMsg('smw_ti_botnotstarted');
				return $msg;
			} else {
				return true;
			}
		} else {
			$param = array("termImportName" => $termImportName);
			global $registeredBots;
			$bot = $registeredBots['smw_termimportbot'];
			$taskID = SGAGardeningLog::getGardeningLogAccess()->addGardeningTask('smw_termimportbot');
			$log = $bot->run($param, false, 0);
			$logPageTitle = $log[1];
			$log = $log[0];
			SGAGardeningLog::getGardeningLogAccess()->markGardeningTaskAsFinished(
				$taskID, $log, $logPageTitle);
			return $log;
		}
	}
	
	public static function createTIArticle($damID, $sourceConfig, $conflictPolicy, $inputConfig, $importSetName, 
			$updatePolicy, $templateName, $extraCategories, $delimiter, $termImportName, $edit) {
	
		$title = Title::newFromText(''.$termImportName, SMW_NS_TERM_IMPORT);
		if($title->exists() && $edit == "false") {
			return wfMsg('smw_ti_def_allready_exists');
		}
		
		$moduleConfig =
			'<ModuleConfiguration>'."\n".
			'<DALModule>'."\n".
			'<id>' . $damID .'</id>'."\n".
			'</DALModule >'."\n".
			'</ModuleConfiguration>';
		
		$sourceConfig = str_replace('<?xml version="1.0"?>',"",$sourceConfig);
		$sourceConfig = str_replace(' xmlns="http://www.ontoprise.de/smwplus#"',"",$sourceConfig);
		$sourceConfig = trim($sourceConfig);
		
		$conflictPolicy =
			'<ConflictPolicy>'."\n".
    		'	<Name>' . $conflictPolicy . '</Name>'."\n".
			'</ConflictPolicy >';
		
		$inputConfig = str_replace('<?xml version="1.0"?>',"",$inputConfig);
		$inputConfig = str_replace(' xmlns="http://www.ontoprise.de/smwplus#"',"",$inputConfig);
		$inputConfig = trim($inputConfig);
		
		$importSetConfig =
			'<ImportSets>'."\n".
   	 		'	<ImportSet>'."\n".
			'		<Name>'.$importSetName.'</Name>'."\n".
			'	</ImportSet>'."\n".
			'</ImportSets>';
			
		if($updatePolicy == 0 || $updatePolicy == ""){
			$updatePolicy = "<once/>";
		} else {
			$updatePolicy = "<maxAge value=\"".$updatePolicy."\"/>";
		}
		$updatePolicy = 
			'<UpdatePolicy>'."\n".
			$updatePolicy
			.'</UpdatePolicy>';

		$innerCreationPatternText = '';
		if(strlen(trim($templateName)) > 0){
			$innerCreationPatternText = 
				'<UseTemplate>'.
				'true'.
				'</UseTemplate>'."\n";
			
			$innerCreationPatternText .= 
				'<TemplateName>'.
				$templateName.
				'</TemplateName>'."\n";
			
			$innerCreationPatternText .=
				'<Delimiter>'.
				$delimiter.
				'</Delimiter>'."\n";
		} else {
			$innerCreationPatternText = 
				'<UseTemplate>'.
				'false'.
				'</UseTemplate>'."\n";
		}
		
		if(strlen(trim($extraCategories)) > 0){
			$extraCategories = 
				'<ExtraCategories>'.
				$extraCategories.
				'</ExtraCategories>'."\n";	
		} 
		
		$creationPattern =
			'<CreationPattern>'."\n".
			$innerCreationPatternText.
			$extraCategories.
			'</CreationPattern>'."\n";
			
		$tiConfig =" <ImportSettings>". 
			$moduleConfig.$sourceConfig.
			$conflictPolicy.$inputConfig.$importSetConfig.$updatePolicy.
			$creationPattern.
			"</ImportSettings>";
	
		//pretty print
		$tiConfig = str_replace('></', '>#escth#</', $tiConfig);
		$xml = explode("\n", preg_replace('/>\s*</', ">\n<", $tiConfig));
		$tiConfig = implode("\n", $xml);
		$tiConfig = str_replace('>#escth#</', '></', $tiConfig);
		
		$articleContent = $tiConfig;
	
		$articleContent .= "\n==== Last runs of this Term Import ====\n";
		$articleContent .= "{{#ask: [[belongsToTermImportWithLabel::".$termImportName."]]"
			."\n| format=ul | limit=10 | sort=hasImportDate | order=descending}}";
		$articleContent .= "\n[[Category:TermImport]]";
		
		$result = smwf_om_EditArticle('TermImport:'.$termImportName, 'TermImportBot', $articleContent, '');
		$temp = $result;
		$result = explode(",", $result);
		$result = trim($result[0]);
		
		if($result !== "true"){
			return wfMsg('smw_ti_def_not_creatable');
		}
		
		smwf_om_TouchArticle("TermImport:".$termImportName);
		return true;
	}
}

/**
 *
 * @param $dalID
 * @param $source_input an XML structure of the given source inputs
 * @param $givenImportSetName the given import set name (String)
 * @param $givenInputPol an XML structure with the given input policy
 * @param $templateName (string) the name of the template if a template should be used as creation pattern
 * @param $extraCategoriees (string) coma separated list of extra category annotations
 * @param $delimiter (string) delimiter that will be used to separate multiple values 
 * @param $givenConflictPol Boolean: overwrite=true, preserve=false
 * @param $runBot run the bot???
 * @param $temImportName (string) name of the term import article
 * @param $updatePolicy (string) how often in minutes should this term import be updated
 * @param $edit : is this a new term import or is an existing one edited
 *
 * @return $result an XML structure
 */
function dif_ti_connectDAM($damID , $source_input, $givenImportSetName,
		$givenInputPol, $templateName, $extraCategories, $delimiter, $givenConflictPol = overwrite,
		$runBot, $termImportName = null, $updatePolicy = "", $edit = false,
		$createOnly = false) {

	global $wgOut;	
	
	$dam = DIDAMRegistry::getDAM($damID);
	
	$damDescription = DIDAMRegistry::getDAMDesc($damID);
	$source = $dam->getSourceSpecification();

	//User has not yet provided the source specification
	if (!$source_input || $source_input == '') {
		$source =  str_replace( "<?xml version=\"1.0\"?>", "", $source );
		return '<result>'.$source.'<damdescription>'.$damDescription .'</damdescription></result>';
	}

	if(isset($source_input)){
		$source_xml = new SimpleXMLElement($source_input);
		$source_xml_original = new SimpleXMLElement($source);

		$source_result = '<DataSource>'."\n";
		foreach ($source_xml_original->children() as $second_gen) {
			$tag = $second_gen->getName();
			$source_result .= '<'.$tag.'><![CDATA[';
			if(!is_null($source_xml->$tag) && strlen(trim($source_xml->$tag)) > 0 ){
				$source_result .= trim($source_xml->$tag);
			}
			$source_result .= ']]></'.$tag.'>'."\n";
		}
		$source_result .= '</DataSource>'."\n";
		
		echo($source_result);
	}

	$importSets = $dam->getImportSets($source_result);
	if(!is_array($importSets)){
		$result = array('success' => false, 
			'msg' => $importSets);
		$result = json_encode($result);
		return '--##starttf##--' . $result . '--##endtf##--';
	} else {
		$importSetsHTML= "";
		if(count($importSets) > 0){
			$importSetsHTML .= "<option value=''></option>";
			foreach($importSets as $iS){
				$importSetsHTML .=
					"<option value='".$iS."'>".$iS."</option>"; 
			}
		} 
	}			
	
	$properties = $dam->getProperties($source_result, $givenImportSetName);
	if(!is_array($properties)){
		$result = array('success' => false, 
			'msg' => $properties);
		$result = json_encode($result);
		return '--##starttf##--' . $result . '--##endtf##--';
	} else {
		$propertiesHTML = "<div class='scrolling'><table id='attrib_table' class='mytable'>";
		foreach($properties as $pN){
			$disabled = '';
			if(strtolower($pN) == 'articlename'){
				$disabled = 'disabled';
			}
			
			$propertiesHTML .= "<tr><td class='mytd' style='width:10px'><input type='checkbox' name='checked_properties' value='".
				$pN."' checked='true'  ".$disabled."></td><td class='mytd'>".$pN."</td></tr>";
		}
		$propertiesHTML .= '</table></div>';
	}
	

	if (!$givenInputPol || $givenInputPol == '') {
		// no input policy defined, create an empty one for getting the term-information
		$givenInputPol =
				'<?xml version="1.0"?>'."\n".
				'<InputPolicy xmlns="http://www.ontoprise.de/smwplus#">'."\n".
    			'<terms>'."\n".
    		    '	<regex></regex>'."\n".
    		    '	<term></term>'."\n".
   			 	'</terms>'."\n".
    			'<properties>'."\n".
       			'	<property>articleName</property>'."\n".
    			'</properties>'."\n".
				'</InputPolicy>'."\n";
	}
	
	if ( $runBot == 0 ) {
		//return the terms list
		
		$terms = $dam->getTermList($source_result, $givenImportSetName, $givenInputPol);
		if(!($terms instanceof DITermCollection)){
			$result = array('success' => false, 
				'msg' => $terms);
			$result = json_encode($result);
			return '--##starttf##--' . $result . '--##endtf##--';
		} else {
			$terms = $terms->getTerms();
			$termsCount = count($terms);
		
			$termsHTML = '<table class=\'mytable\'>';
			foreach($terms as $term){
				$termsHTML .= "<tr><td class=\"mytd\">".$term->getArticleName()."</td></tr>";
			}
			
			$termsHTML .= '</table>';
			$result = array('success' => true, 'importSets' => $importSetsHTML,
				'properties' => $propertiesHTML, 'terms' => $termsHTML, 'termsCount' => $termsCount);
			$result = json_encode($result);

			return '--##starttf##--' . $result . '--##endtf##--';
		}
	} elseif ( $runBot == 1 ){
		//do the Import!

		$articleCreated = DICL::createTIArticle($damID, $source_result, $givenConflictPol, $givenInputPol, $givenImportSetName, 
			$updatePolicy, $templateName, $extraCategories, $delimiter, $termImportName, $edit);

		if($articleCreated !== true){
			$result = array('success' => false, 
				'msg' => $articleCreated);
			$result = json_encode($result);
			return '--##starttf##--' . $result . '--##endtf##--';
		} else if ($createOnly != "false"){
			$linker = new Linker();
			$link = $linker->makeLink(
				Title::newFromText(''.$termImportName, SMW_NS_TERM_IMPORT)->getFullText(), ''.$termImportName);
			$result = array('success' => true, 
				'msg' => '<br><b>'.wfMsg('smw_ti_definition_saved_successfully', $link).'<br/></b><br/>');
			$result = json_encode($result);
			return '--##starttf##--' . $result . '--##endtf##--';
		}

		$result = DICL::importTerms($termImportName);
		if (is_string($result)) {
			$result = array('success' => false, 
				'msg' => $result);
			$result = json_encode($result);
			return '--##starttf##--' . $result . '--##endtf##--';
		} else {
			$linker = new Linker();
			$link = $linker->makeLink(
				Title::newFromText(''.$termImportName, SMW_NS_TERM_IMPORT)->getFullText(), ''.$termImportName);
			$msg = '<br><b>'.wfMsg('smw_ti_definition_saved_successfully', $link).'<br/></b><br/>';
			
			$link = $linker->makeLink(
				Title::newFromText('Gardening', NS_SPECIAL)->getFullText());
			$msg .= '<b>'.wfMsg('smw_ti_started_successfully', $link).'</b><br/><br/>';
			
			$result = array('success' => true, 
				'msg' => $msg);
			$result = json_encode($result);
			return '--##starttf##--' . $result . '--##endtf##--';
		}
	}
}

