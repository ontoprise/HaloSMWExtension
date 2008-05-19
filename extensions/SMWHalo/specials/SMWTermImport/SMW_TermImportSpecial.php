<?php
/*  Copyright 2008, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * A special page for the import of terms into the wiki.
 *
 *
 * @author Thomas Schweitzer
 */

 if (!defined('MEDIAWIKI')) die();



global $IP;
require_once( $IP . "/includes/SpecialPage.php" );


/*
 * Standard class that is resopnsible for the creation of the Special Page
 */
class SMWTermImportSpecial extends SpecialPage {
	public function __construct() {
		parent::__construct('TermImport');
	}
	/**
	 * Overloaded function that is resopnsible for the creation of the Special Page
	 */
	public function execute() {

		global $wgRequest, $wgOut;

		$wgOut->setPageTitle(wfMsg('smw_ti_termimport'));

		$html = '';
//---- Start: TEST for Import ---- 	
	global $smwgHaloIP;
	require_once($smwgHaloIP . '/specials/SMWTermImport/SMW_WIL.php');
	$wil = new WIL();
	$tlModules = $wil->getTLModules();
	$html .= "<br /><br />===TL Modules===<br />";
	$html .= $tlModules;
	
	$res = $wil->connectTL("ConnectLocal", $tlModules);
	$dalModules = $wil->getDALModules();

	$html .= "<br /><br />===DAL Modules===<br />";
	$html .= $dalModules;
	$res = $wil->connectDAL("ReadCSV", $dalModules);
	
	$source = $wil->getSourceSpecification();
	$source = str_replace(
	            '</filename>', 
	            'C:\\Programme\\MediaWiki\\HaloSMWExtension\\extensions\\SMWHalo\\specials\\SMWTermImport\\DAL\\articles.csv</filename>',
	            $source);
	$html .= "<br /><br />===Source Specification===<br />";
	$html .= $source;
	
	$importSets = $wil->getImportSets($source);
	$html .= "<br /><br />===Import Sets===<br />";
	$html .= $importSets;
	
	require_once($smwgHaloIP . '/includes/SMW_XMLParser.php');
	$p = new XMLParser($importSets);
	$p->parse();
//	$p->removeAllParentElements('NAME', 'Bio');
	$impSet = $p->serialize();
	$properties = $wil->getProperties($source, $impSet);
	$html .= "<br /><br />===Properties===<br />";
	$html .= $properties;
	
	
	$ip = 
		'<?xml version="1.0"?>'."\n".
		'<InputPolicy xmlns="http://www.ontoprise.de/smwplus#">'."\n".
    	'<terms>'."\n".
        '	<regex>.*</regex>'."\n".
        '	<term>Cell</term>'."\n".
        '	<term>Fox</term>'."\n".
    	'</terms>'."\n".
    	'<properties>'."\n".
       	'	<property>articleName</property>'."\n".
       	'	<property>Content</property>'."\n".
       	'	<property>author</property>'."\n".
    	'</properties>'."\n".
		'</InputPolicy>'."\n";
	
	$terms = $wil->getTermList($source, $impSet, $ip);
	$html .= "<br /><br />===List of Terms===<br />";
	$html .= $terms;

	$moduleConfig =
		'<?xml version="1.0"?>'."\n".
		'<ModuleConfiguration xmlns="http://www.ontoprise.de/smwplus#">'."\n".
		'  <TLModules>'."\n".
		'    <Module>'."\n".
		'        <id>ConnectLocal</id>'."\n".
		'    </Module>'."\n".
		'  </TLModules >'."\n".
		'  <DALModules>'."\n".
		'    <Module>'."\n".
		'        <id>ReadCSV</id>'."\n".
		'    </Module>'."\n".
		'  </DALModules >'."\n".
		'</ModuleConfiguration>';
	
	$mappingPolicy =
		'<?xml version="1.0"?>'."\n".
		'<MappingPolicy xmlns="http://www.ontoprise.de/smwplus#">'."\n".
    	'	<page>TermImportMapping</page>'."\n".
		'</MappingPolicy >';
	
	$conflictPolicy =
		'<?xml version="1.0"?>'."\n".
		'<ConflictPolicy xmlns="http://www.ontoprise.de/smwplus#">'."\n".
    	'	<overwriteExistingTerms>true</overwriteExistingTerms>'."\n".
		'</ConflictPolicy >';
		
	$terms = $wil->importTerms($moduleConfig, $source, $impSet, $ip, 
	                           $mappingPolicy, $conflictPolicy);
	$html .= "<br /><br />===Terms===<br />";
	$html .= $terms;
	
	//TEST import
/*
 	$settings = "\n<ImportSettings>\n"
				.$moduleConfig."\n"
				.$source."\n"
				.$importSets."\n"
				.$ip."\n"
				.$mappingPolicy."\n"
				.$conflictPolicy."\n"
				."</ImportSettings>";
	$settings = '<?xml version="1.0"?>'.
				str_replace('<?xml version="1.0"?>', "", $settings);
				
	global $smwgHaloIP;
	require_once("$smwgHaloIP/specials/SMWTermImport/SMW_TermImportBot.php");
	$tib = new TermImportBot();
	$html .= $tib->importTerms($settings);
*/	

	//TEST import
//---- End: TEST for Import ---- 		 
		$wgOut->addHTML($html);
	}

}

?>