<?php
// register ajax calls

/**
 * @file
 * @ingroup DITermImport
 * 
 * @author Thomas Schweitzer
 */

global $wgAjaxExportList;
$wgAjaxExportList[] = 'smwf_ti_connectTL';

class CL {

	//--- Public methods ---

	/**
	 * Constructor of class CL.
	 *
	 */
	function __construct() {
	}

	public function execute() {
		global $smwgDIIP, $wgOut, $wgRequest, $wgScriptPath, $smwgDIScriptPath;
		require_once($smwgDIIP . '/specials/TermImport/SMW_WIL.php');

		$wil = new WIL();
		$tlModules = $wil->getTLModules();

		$html = "<div id=\"menue\">";
		$html .= "<div id=\"breadcrumb-menue\" class=\"BreadCrumpContainer\">";
		$html .= "<span id=\"menue-step1\" class=\"ActualMenueStep\">".wfMsg('smw_ti_menuestep1')."</span><span class=\"HeadlineDelimiter\"></span>";
		$html .= "<span id=\"menue-step2\" class=\"TodoMenueStep\">".wfMsg('smw_ti_menuestep2')."</span>";
		$html .= "</div></div>";
		
		$html .= "<div id=\"summary\"></div>" .
				"<div id=\"top-container\">" .
					"<div><div id=\"tl-content\"><b>". wfMsg('smw_ti_tl-heading') ."</b>" .
						"<div id=\"tlid\">" . $this->getTLIDs($tlModules) . "</div>" . 
						"<div id=\"tldesc\">" .  "</div></div>" .
						"<div class=\"arrow\"><img src=\"$wgScriptPath/extensions/DataImport/skins/TermImport/images/arrow.png\"/></div>".
					"</div>";
			
		$html .= "<div><div id=\"dal-content\"><b>".wfMsg('smw_ti_dam-heading')."</b>" .
				 	"<div id=\"dalid\">" . "<div class=\"myinfo\"><i>" . wfMsg('smw_ti_firstselectTLM') . "</i></div>" . "</div>" .
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

	/*
	 *
	 * lists all available TL modules
	 *
	 */
	public function getTLIDs( $tlModules ){
		global $smwgDIIP;
		require_once($smwgDIIP . '/specials/TermImport/SMW_XMLParser.php');
		$p = new XMLParser($tlModules);
		$result = $p->parse();
		if ($result == TRUE) {
			$tlmodules = $p->getElement(array('TLModules'));
			$count = count($tlmodules['TLMODULES'][0]['value']['MODULE']);
			for($i = 0; $i < $count; $i++) {
				$tlid = $tlmodules['TLMODULES'][0]['value']['MODULE'][$i]['value']['ID'][0]['value'];
				$html = "<div class=\"entry\" onMouseOver=\"this.className='entry-over';\"" .
		 				 " onMouseOut=\"termImportPage.showRightTLM(event, this, '$tlid')\" onClick=\"termImportPage.connectTL(event, this, '$tlid')\">" .							
						"<a>" . $tlid . "</a>" . "</div>";
			}
		}
		return $html;
	}
}

//AJAX Calls
/**
 *
 * @param $tlID the ID of the Transport Layer
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
function smwf_ti_connectTL($tlID, $dalID , $source_input, $givenImportSetName,
		$givenInputPol, $mappingPage, $givenConflictPol = true,
		$runBot, $termImportName = null, $updatePolicy = "", $edit = false,
		$createOnly = false) {

	global $smwgDIIP, $wgOut;
	require_once($smwgDIIP . '/specials/TermImport/SMW_WIL.php');
	require_once($smwgDIIP . '/specials/TermImport/SMW_XMLParser.php');
	$wil = new WIL();
	$tlModules = $wil->getTLModules();

	//TODO Errorhandling!!!

	if(!$tlID) {
		// Error,keine TLID angegeben!!!
	}

	$res = $wil->connectTL( $tlID , $tlModules );

	$dalModules = $wil->getDALModules();

	//return if no dalID is given
	if ( !$dalID ) {
		$tlModules =  str_replace( "<?xml version=\"1.0\"?>", "", $tlModules );
		$dalModules =  str_replace( "<?xml version=\"1.0\"?>", "", $dalModules );
		return '<result>' . $tlModules . $dalModules . '</result>';
	}

	$res = $wil->connectDAL($dalID, $dalModules);
	$source = $wil->getSourceSpecification();

	// return if no source is given
	if ( (!$source_input || $source_input == '') && $res ) {
		$tlModules =  str_replace( "<?xml version=\"1.0\"?>", "", $tlModules );
		$dalModules =  str_replace( "<?xml version=\"1.0\"?>", "", $dalModules );
		$source =  str_replace( "<?xml version=\"1.0\"?>", "", $source );
		return '<result>' . $tlModules . $dalModules . $source . '</result>';
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

	$importSets = $wil->getImportSets($source_result);
	$p = new XMLParser($importSets);
	$result = $p->parse();
	
	if ($result == TRUE && $givenImportSetName && $givenImportSetName != '' && $givenImportSetName != 'ALL') {
		$p->removeAllParentElements('NAME', $givenImportSetName);
	}
	
	if ($result == TRUE && $givenImportSetName && $givenImportSetName == 'ALL') {
		$importSets = 
			'<?xml version="1.0"?>'
			.'<IMPORTSETS XMLNS="http://www.ontoprise.de/smwplus#">'
			.'</IMPORTSETS>';
	} else {	
			$importSets = $p->serialize();
	}
	
	$properties = $wil->getProperties($source_result, $importSets);

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
	$terms = $wil->getTermList($source_result, $importSets, $givenInputPol);

	if ( $runBot == 0 ) {
		// prepare XML strings for return...
		$xmlResult = '<result>' . $tlModules . $dalModules . $source_result . $importSets . $properties . $terms . '</result>';
		$xmlResult = '<?xml version="1.0"?>'.
		str_replace('<?xml version="1.0"?>', "", $xmlResult);

		return $xmlResult;
	}
	elseif ( $runBot == 1 ){
		//do the Import!
		$title = Title::newFromText($mappingPage);
		if( !$title->exists() ) {
			return '<?xml version="1.0"?>
	 			<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">
	 		    <value>falseMap</value>
	 		    <message>' . wfMsg('smw_ti_nomappingpage') . '</message>
	 			</ReturnValue >';
		}
		$moduleConfig =
			'<?xml version="1.0"?>'."\n".
			'<ModuleConfiguration xmlns="http://www.ontoprise.de/smwplus#">'."\n".
			'  <TLModules>'."\n".
			'    <Module>'."\n".
			'        <id>' . $tlID . '</id>'."\n".
			'    </Module>'."\n".
			'  </TLModules >'."\n".
			'  <DALModules>'."\n".
			'    <Module>'."\n".
			'        <id>' . $dalID .'</id>'."\n".
			'    </Module>'."\n".
			'  </DALModules >'."\n".
			'</ModuleConfiguration>';
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
		else {
			//Error!no conflict policy given... is it possible with <select>?!?
		}

		//todo: error handling
		if($updatePolicy == 0 || $updatePolicy == ""){
			$updatePolicy = "<once/>";
		} else {
			$updatePolicy = "<maxAge value=\"".$updatePolicy."\"/>";
		}
		$updatePolicy = '<?xml version="1.0"?>'."\n".
			'<UpdatePolicy xmlns="http://www.ontoprise.de/smwplus#">'."\n".$updatePolicy
		.'</UpdatePolicy>';


		$articleCreated = smwf_ti_createTIArticle($moduleConfig, $source_result, $mappingPolicy, $conflictPolicy,
			$givenInputPol, $importSets, $termImportName, $updatePolicy, $edit);

		if($articleCreated !== true){
			return $articleCreated;
		} else if ($createOnly != "false"){
			return '<?xml version="1.0"?>
	 			<ReturnValue xmlns="http://www.ontoprise.de/smwplus#">
	 		    <value>articleCreated</value>
	 		    <message>' . $termImportName . '</message>
	 			</ReturnValue >';
		}

		$terms = $wil->importTerms($moduleConfig, $source_result, $importSets, $givenInputPol,
		$mappingPolicy, $conflictPolicy, $termImportName);
		if ( $terms == false) {
			//error while running bot
		}
		else {
			return $terms;
		}
	}
	else {
		// error, $runBot neither 0 nor 1
	}
	return null;
}

function smwf_ti_createTIArticle($moduleConfig, $sourceConfig, $mappingConfig, $conflictConfig,
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