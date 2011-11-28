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
$wgAjaxExportList[] = 'smwf_ti_connectDAM';

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
									"</select><div id=\"hidden_pol_type\"></div>" .
								"</div>" . //policy
								"<div id=\"mapping\">" .
									"<br/><br/><div class=\"input-field-heading\">".
									wfMsg('smw_ti_mappingPage-heading').
									"<img id=\"help-img3\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"termImportPage.displayHelp(3)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>".
									"</div>".
									"<div id=\"help3\" class=\"TIHelpMessage\" style=\"display: none\">".
									"<span >".wfMsg('smw_ti_help')."</span> ".
									wfMsg('smw_ti_mappingPage-help')."</div>".
									wfMsg('smw_ti_mappingPage-label').
									"<input name=\"mapping\" id=\"mapping-input-field\" type=\"text\"
									class=\"wickEnabled\" typeHint=\"0\" 
									size=\"20\" onKeyPress=\"termImportPage.changeBackground(event, this)\"/>&nbsp&nbsp
										<a onClick=\"termImportPage.viewMappingArticle(event,this)\">" . wfMsg('smw_ti_viewMappingPage') . "</a>&nbsp&nbsp
										<a onClick=\"termImportPage.editMappingArticle(event,this)\">" . wfMsg('smw_ti_editMappingPage') ."</a>".
								"</div>" . //mapping
								"<div id=\"conflict\">" .
									"<br/><br/><div class=\"input-field-heading\">".
									wfMsg('smw_ti_conflictpolicy-heading').
									"<img id=\"help-img4\" title=\"".wfMsg("smw_wws_help-button-tooltip")."\" class=\"help-image\" onclick=\"termImportPage.displayHelp(4)\" src=\"".$smwgDIScriptPath."/skins/webservices/help.gif\"></img>".
									"</div>".
									"<div id=\"help4\" class=\"TIHelpMessage\" style=\"display: none\">".
									"<span>".wfMsg('smw_ti_help')."</span> ".
									wfMsg('smw_ti_conflictpolicy-help')."</div>".
									wfMsg('smw_ti_conflictpolicy-label').
									"<select name=\"conflict\" id=\"conflict-input-field\">" .
										"<option>overwrite</option>" .
										"<option>preserve current versions</option>" .
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
								"</div>" . //ti name
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
		//todo: deal with this
		$html = '<span id="editDataSpan" style="display: none">';
		
		$xmlString = smwf_om_GetWikiText('TermImport:'.$termImportName);
		
		$start = strpos($xmlString, "<ImportSettings>");
		$end = strpos($xmlString, "</ImportSettings>") + 17 - $start;
		$xmlString = substr($xmlString, $start, $end);
		$simpleXMLElement = new SimpleXMLElement($xmlString);
		
		$tlId = $simpleXMLElement->xpath("//TLModules/Module/id/text()");
		$html .= '<span id="tlId-ed">'.$tlId[0].'</span>';
		
		$dalId = $simpleXMLElement->xpath("//DALModules/Module/id/text()");
		$html .= '<span id="dalId-ed">'.$dalId[0].'</span>';
		
		$dataSource = $simpleXMLElement->xpath("//DataSource");
		$html .= '<span id="dataSource-ed">'.rawurlencode($dataSource[0]->asXML()).'</span>';
		
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
		
		$mappingPolicy = $simpleXMLElement->xpath("//MappingPolicy/page/text()");
		$html .= '<span id="mappingPolicy-ed">'.$mappingPolicy[0].'</span>';
		
		$conflictPolicy = $simpleXMLElement->xpath("//ConflictPolicy/overwriteExistingTerms/text()");
		$conflictPolicy = $conflictPolicy[0] == "true" ? "overwrite" : "preserve current versions"; 
		$html .= '<span id="conflictPolicy-ed">'.$conflictPolicy.'</span>';

				$html .= '<span id="termImportName-ed">'.$termImportName.'</span>';
		
		$updatePolicy = $simpleXMLElement->xpath("//UpdatePolicy/maxAge/@value");
		$updatePolicy = $updatePolicy ? $updatePolicy[0] : "0"; 
		$html .= '<span id="updatePolicy-ed">'.$updatePolicy.'</span>';
		
		$xmlString = @ smwf_ti_connectTL($tlId[0]);
		$xmlString = str_replace('xmlns="http://www.ontoprise.de/smwplus#"'
				, "", $xmlString);
		$simpleXMLElement = new SimpleXMLElement($xmlString);
		$tlDesc = $simpleXMLElement->xpath("//TLModules/Module/desc/text()");
		$html .= '<span id="tl-desc">'.$tlDesc[0].'</span>';
		
		$dalDesc = $simpleXMLElement->xpath("//Module[./id/text() = '".$dalId[0]."']/desc/text()");
		$html .= '<span id="dal-desc">'.$dalDesc[0].'</span>';
		
		$dals = $simpleXMLElement->xpath("//DALModules/Module/id/text()");
		if($dals != null){
			$dals = implode(",", $dals);
			$html .= '<span id="dalIds">'.$dals.'</span>';
		} 
		
		$html .= "</span>";
		
		return $html;	
	}
	
	
	/**
	 * Imports the vocabulary according to the given policies. The content of the
	 * wiki is updated. This method starts a bot with the following parameters:
	 *
	 */
	public static function importTerms($termImportName, $async = true) {
		$result = "true";
		$msg = "";

		if($async){
		  	$param = "termImportName=".$termImportName;
			$taskID = GardeningBot::runBot('smw_termimportbot', $param);
			if (is_int($taskID)) {
				$msg = wfMsg('smw_ti_botstarted');
			} else {
				$msg = wfMsg('smw_ti_botnotstarted');
				$result = false;
			}
			
			return '<?xml version="1.0"?>'."\n".
	 			'<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">'."\n".
	 			"	<value>$result</value>\n".
	 			"	<message>$msg</message>\n".
	 			'</ReturnValue>';
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
	
	public static function createTIArticle($moduleConfig, $sourceConfig, $mappingConfig, $conflictConfig,
			$inputConfig, $importSetConfig, $termImportName, $updatePolicy, $edit) {
	
		$title = Title::newFromText("TermImport:".$termImportName);
		if($title->exists() && $edit == "false") {
			return '<?xml version="1.0"?>
		 			<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">
		 		    <value>falseTIN</value>
		 		    <message>' . wfMsg('smw_ti_def_allready_exists') . '</message>
		 			</ReturnValue >';
		}
		
		$moduleConfig = str_replace('<?xml version="1.0"?>',"",$moduleConfig);
		$moduleConfig = str_replace(' xmlns="http://www.ontoprise.de/smwplus#"',"",$moduleConfig);
		$moduleConfig = trim($moduleConfig);
	
		$sourceConfig = str_replace('<?xml version="1.0"?>',"",$sourceConfig);
		$sourceConfig = str_replace(' xmlns="http://www.ontoprise.de/smwplus#"',"",$sourceConfig);
		$sourceConfig = trim($sourceConfig);
	
		$mappingConfig = str_replace('<?xml version="1.0"?>',"",$mappingConfig);
		$mappingConfig = str_replace(' xmlns="http://www.ontoprise.de/smwplus#"',"",$mappingConfig);
		$mappingConfig = trim($mappingConfig);
	
		$conflictConfig = str_replace('<?xml version="1.0"?>',"",$conflictConfig);
		$conflictConfig = str_replace(' xmlns="http://www.ontoprise.de/smwplus#"',"",$conflictConfig);
		$conflictConfig = trim($conflictConfig);
	
		$inputConfig = str_replace('<?xml version="1.0"?>',"",$inputConfig);
		$inputConfig = str_replace(' xmlns="http://www.ontoprise.de/smwplus#"',"",$inputConfig);
		$inputConfig = trim($inputConfig);
	
		$updatePolicy = str_replace('<?xml version="1.0"?>',"",$updatePolicy);
		$updatePolicy = str_replace(' xmlns="http://www.ontoprise.de/smwplus#"',"",$updatePolicy);
		$updatePolicy = trim($updatePolicy);
	
		echo('<pre>'.print_r($importSetConfig,true).'</pre>');
		
		$importSetConfig = str_replace('<?xml version="1.0"?>',"",$importSetConfig);
		$importSetConfig = str_replace(' XMLNS="http://www.ontoprise.de/smwplus#"',"",$importSetConfig);
		$importSetConfig = str_replace('IMPORTSETS>',"ImportSets>",$importSetConfig);
		$importSetConfig = str_replace('IMPORTSET>',"ImportSet>",$importSetConfig);
		$importSetConfig = str_replace('NAME>',"Name>",$importSetConfig);
		$importSetConfig = trim($importSetConfig);
	
		$tiConfig = "<ImportSettings>".$moduleConfig.$sourceConfig.
		$mappingConfig.$conflictConfig.$inputConfig.$importSetConfig.$updatePolicy
		."</ImportSettings>";
	
		//pretty print
		$xml = explode("\n", preg_replace('/>\s*</', ">\n<", $tiConfig));
		$tiConfig = implode("\n", $xml);
	
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
			return '<?xml version="1.0"?>
		 			<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">
		 		    <value>falseTIN</value>
		 		    <message>' . wfMsg('smw_ti_def_not_creatable') . '</message>
		 			</ReturnValue >';
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
 * @param $mappingPage The name of the mapping article
 * @param $givenConflictPol Boolean: overwrite=true, preserve=false
 * @param $runBot run the bot???
 *
 * @return $result an XML structure
 */
function smwf_ti_connectDAM($damID , $source_input, $givenImportSetName,
		$givenInputPol, $mappingPage, $givenConflictPol = true,
		$runBot, $termImportName = null, $updatePolicy = "", $edit = false,
		$createOnly = false) {

	global $wgOut;	
	
	$dam = DIDAMRegistry::getDAM($damID);
	//todo: add error handling if dam does not exist
	
	$damDescription = DIDAMRegistry::getDAMDesc($damID);
	$source = $dam->getSourceSpecification();

	//User has not yet provided the source specification
	if (!$source_input || $source_input == '') {
		$source =  str_replace( "<?xml version=\"1.0\"?>", "", $source );
		return '<result>'.$source.'<damdescription>'.$damDescription .'</damdescription></result>';
	}

	if(isset($source_input)){
		$source_xml = new SimpleXMLElement($source_input);
		$source_xml_alt = new SimpleXMLElement($source);
			
		foreach ($source_xml->children() as $second_gen) {
			$tag = $second_gen->getName();
			$value = (string) $second_gen;
			$result = $source_xml_alt->xpath($tag);

			//Change the old tag
			$source_xml_alt->$tag = $value;
		}
		//get the xml-string
		$source_result = $source_xml_alt->asXML();
	}

	$importSets = $dam->getImportSets($source_result);
	if(!is_array($importSets)){
		//todo: error occured
		error();
	} else {
		$importSetsHTML= "";
		if(count($importSets) > 0){
			foreach($importSets as $iS){
				$importSetsHTML .=
					"<option value='".$iS."'>".$iS."</option>"; 
			}
		} 
	}			
	
	$properties = $dam->getProperties($source_result, $givenImportSetName);
	if(!is_array($properties)){
		//todo: error occured
	} else {
		$propertiesHTML = "<div class='scrolling'><table id='attrib_table' class='mytable'>";
		foreach($properties as $pN => $dC){
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
			//todo: error occured
		} else {
			$terms = $terms->getTerms();
			$termsCount = count($terms);
		
			$termsHTML = '<table class=\'mytable\'>';
			foreach($terms as $term){
				$termsHTML .= "<tr><td class=\"mytd\">".$term->getArticleName()."</td></tr>";
			}
			$termsHTML .= '</table>';
		}
		
		$result = array('success' => true, 'importSets' => $importSetsHTML,
			'properties' => $propertiesHTML, 'terms' => $termsHTML, 'termsCount' => $termsCount);
		$result = json_encode($result);

		return '--##starttf##--' . $result . '--##endtf##--';
	} elseif ( $runBot == 1 ){
		//do the Import!
		
		$moduleConfig =
			'<?xml version="1.0"?>'."\n".
			'<ModuleConfiguration xmlns="http://www.ontoprise.de/smwplus#">'."\n".
			'  <DALModules>'."\n".
			'    <Module>'."\n".
			'        <id>' . $damID .'</id>'."\n".
			'    </Module>'."\n".
			'  </DALModules >'."\n".
			'</ModuleConfiguration>';
		
		//todo: only if there is a given import set name in place
		$importSetConf =
			'<?xml version="1.0"?>'."\n".
			'<ImportSets xmlns="http://www.ontoprise.de/smwplus#">'."\n".
   	 		'	<ImportSet>'."\n".
			'		<Name>'.$givenImportSetName.'</Name>'."\n".
			'	</ImportSet>'."\n".
			'</ImportSets>';
			
		
		//todo: only add if there is a mapping policy in place
		$mappingPolicy =
			'<?xml version="1.0"?>'."\n".
			'<MappingPolicy xmlns="http://www.ontoprise.de/smwplus#">'."\n".
   	 		'	<page>' . $mappingPage . '</page>'."\n".
			'</MappingPolicy >';

		if($givenConflictPol && $givenConflictPol != '') {
			$conflictPolicy =
				'<?xml version="1.0"?>'."\n".
				'<ConflictPolicy xmlns="http://www.ontoprise.de/smwplus#">'."\n".
    			'	<overwriteExistingTerms>' . $givenConflictPol . '</overwriteExistingTerms>'."\n".
				'</ConflictPolicy >';
		}
		
		if($updatePolicy == 0 || $updatePolicy == ""){
			$updatePolicy = "<once/>";
		} else {
			$updatePolicy = "<maxAge value=\"".$updatePolicy."\"/>";
		}
		$updatePolicy = '<?xml version="1.0"?>'."\n".
			'<UpdatePolicy xmlns="http://www.ontoprise.de/smwplus#">'."\n".$updatePolicy
		.'</UpdatePolicy>';

		$articleCreated = DICL::createTIArticle($moduleConfig, $source_result, $mappingPolicy, $conflictPolicy,
			$givenInputPol, $importSetConf, $termImportName, $updatePolicy, $edit);

		if($articleCreated !== true){
			//todo: return json
			return $articleCreated;
		} else if ($createOnly != "false"){
			//todo: return json
			return '<?xml version="1.0"?>
	 			<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">
	 		    <value>articleCreated</value>
	 		    <message>' . $termImportName . '</message>
	 			</ReturnValue >';
		}

		$result = DICL::importTerms($termImportName);
		if ( $result == false) {
			//error while running bot
		} else {
			//todo: return JSON
			return $result;
		}
	}
	
	return null;
}

