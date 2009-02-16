<?php
// register ajax calls

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
		global $smwgDIIP, $wgOut, $wgRequest, $wgScriptPath;
		require_once($smwgDIIP . '/specials/TermImport/SMW_WIL.php');
		
		$wil = new WIL();
		$tlModules = $wil->getTLModules();
		
		$t = Title::newFromText('super123');
		if( !$t->exists() ){
			$msg = wfMsg('smw_ti_mappingPageNotExist');
		}
		$html = "<div id=\"summary\"></div>" .
				"<div id=\"top-container\">" .
					"<div style=\"margin-bottom:10px;\">".wfMsg('smw_ti_welcome')."</div>" .
					"<div><div id=\"tl-content\">TLM:" .
						"<div id=\"tlid\">" . $this->getTLIDs($tlModules) . "</div>" . 
						"<div id=\"tldesc\">" . "Info: " . "</div></div>" .
						"<div class=\"arrow\"><img src=\"$wgScriptPath/extensions/SMWHalo/skins/TermImport/images/arrow.gif\"/></div>".
					"</div>";
					
		$html .= "<div><div id=\"dal-content\">DAM:" .
				 	"<div id=\"dalid\">" . "<div class=\"myinfo\">" . wfMsg('smw_ti_firstselectTLM') . "</div>" . "</div>" .
				 	"<div id=\"daldesc\">" . "</div></div>" .
					"<div class=\"arrow\"><img src=\"$wgScriptPath/extensions/SMWHalo/skins/TermImport/images/arrow.gif\"/></div>".
				 "</div>";
		
		$html .= "<div id=\"source-spec\">" . 
				"<table height=\"200px\"><tr><td valign=\"middle\"><i>".wfMsg('smw_ti_selectDAM')."</i></td></tr></table>".
				"</div>";
		$html .= "</div>"; //top-container
		
		$html .= "<div id=\"bottom-container\">" .
					"<div id=\"extras\">" .
							"<div id=\"extras-left\">" .
								"<div id=\"importset\">" . wfMsg('smw_ti_selectImport') .
									"<select name=\"importset\" id=\"importset-input-field\" size=\"1\" onchange=\"termImportPage.importSetChanged(event, this)\"></select>" .
									"<br><br>" .
								"</div>" . //importset
								"<div id=\"policy\">" .
									"<div id=\"policy-input\">" .
										"<table><tr><td>" .wfMsg('smw_ti_define_inputpolicy')."</td>" . 
										"<td><input name=\"policy\" id=\"policy-input-field\" type=\"text\" size=\"20\"></td>" .
										"<td><img style=\"cursor: pointer;\" onclick=\"termImportPage.getPolicy(event, this)\" src=\"$wgScriptPath/extensions/SMWHalo/skins/TermImport/images/Add.png\" /></td></tr>" . 
										"<tr><td align=\"left\"><b><i>Info:</i></b></td><td align=\"right\"><input type=\"radio\" name=\"policy_type\" value=\"regex\" checked><span style=\"color:#900000;\"><u>RegEx</u></span><input type=\"radio\" name=\"policy_type\" value=\"term\">Term</span></td><td><img style=\"cursor: pointer;\" onclick=\"termImportPage.deletePolicy(event, this)\" src=\"$wgScriptPath/extensions/SMWHalo/skins/TermImport/images/Delete-silk.png\" /></td></tr></table>" . 
										"<i>" . wfMsg('smw_ti_inputpolicy') . "</i>" . 
									"</div>" .	
									"<select id=\"policy-textarea\" name=\"policy-out\" size=\"7\" multiple>" .  
									"</select><div id=\"hidden_pol_type\"></div>" .
								"</div>" . //policy
								"<div id=\"mapping\">" .
									"<table>
										<tr><td><br><br>".wfMsg('smw_ti_mappingPage')."<br></td></tr>" .
										"<tr><td><input name=\"mapping\" id=\"mapping-input-field\" type=\"text\" size=\"20\" onKeyPress=\"termImportPage.changeBackground(event, this)\">&nbsp&nbsp
										<a onClick=\"termImportPage.viewMappingArticle(event,this)\">" . wfMsg('smw_ti_viewMappingPage') . "</a>&nbsp&nbsp
										<a onClick=\"termImportPage.editMappingArticle(event,this)\">" . wfMsg('smw_ti_editMappingPage') . "</td></tr>
									</table>" .
								"</div>" . //mapping
								"<div id=\"conflict\">" .
									"<br><br>".wfMsg('smw_ti_conflictpolicy')."&nbsp;" .
									"<select name=\"conflict\" id=\"conflict-input-field\">" .
										"<option>overwrite</option>" .
										"<option>preserve current versions</option>" .
									"</select>" .
								"</div>" . //conflict
								
							"</div>" . //extras-left
							"<div id=\"extras-right\">" .
								"<div id=\"attrib-articles\">" .
									"<div id=\"attrib\"></div>" .
									"<div id=\"articles\"></div>" .
								"</div>" . //attrib-articles
							"</div>" . //extras-right
					"</div>". //extras
					"<div id=\"extras-bottom\" align=\"center\"></div>";
		
		$html .= "</div>"; //bottom-container
		
		$wgOut->addHTML($html);		
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
							$givenInputPol, $mappingPage, $givenConflictPol = true, $runBot) {
	
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
	$importSets = $p->serialize();
	
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
		
		$terms = $wil->importTerms($moduleConfig, $source_result, $importSets, $givenInputPol,
					$mappingPolicy, $conflictPolicy);
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
?>