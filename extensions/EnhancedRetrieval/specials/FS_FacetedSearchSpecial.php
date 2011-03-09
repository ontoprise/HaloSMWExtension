<?php
/**
 * @file
 * @ingroup FS_Special
 */

/*  Copyright 2011, ontoprise GmbH
* 
*   This file is part of the EnhancedRetrieval-Extension.
*
*   The EnhancedRetrieval-Extension is free software; you can redistribute 
*   it and/or modify it under the terms of the GNU General Public License as 
*   published by the Free Software Foundation; either version 3 of the License, 
*   or (at your option) any later version.
*
*   The EnhancedRetrieval-Extension is distributed in the hope that it will 
*   be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * A special page for doing a faceted search on the semantic data in the wiki.
 *
 *
 * @author Thomas Schweitzer
 */

if (!defined('MEDIAWIKI')) die();


global $IP;
require_once( $IP . "/includes/SpecialPage.php" );

/*
 * Standard class that is responsible for the creation of the Special Page
 */
class FSFacetedSearchSpecial extends SpecialPage {
	
	//--- Constants ---
	
	const SPECIAL_PAGE_HTML = <<<HTML
<div id="wrapper"> 
	<div id="header">
		<h1>{{fs_title}}</h1>
	</div>

	<div class="facets">
		<div class="search">
			<h2>{{fs_search}}</h2>
	        <input type="text" id="query" name="query"/>
		</div>
		<div>
			{{fs_categories}}
			<div id="field_smwh_categories">
			</div>
		</div> 
		<div>
			{{fs_properties}}
			<div id="field_smwh_attributes">
			</div>
			<div id="field_smwh_properties">
			</div>
		</div> 
	</div>
	<div class="results" id="results">
		<div id="navigation">
			<ul id="pager"></ul>
	
			<div id="pager-header">
			</div>
		</div>
		<div id="docs">
			{{fs_search_results}}
		</div>
	</div>
</div>
HTML;

    public function __construct() {
        parent::__construct('FacetedSearch');
    }

    /**
     * Overloaded function that is responsible for the creation of the Special Page
     */
    public function execute($par) {

        global $wgOut, $wgRequest, $wgLang,$wgUser;

		$wgOut->addHTML($this->replaceLanguageStrings(self::SPECIAL_PAGE_HTML));
		
		global $fsgScriptPath;
            
		$wgOut->addScript("<script type=\"text/javascript\" src=\"". $fsgScriptPath .  "/scripts/ajax-solr/lib/core/Core.js\"></script>");        
		$wgOut->addScript("<script type=\"text/javascript\" src=\"". $fsgScriptPath .  "/scripts/ajax-solr/lib/core/AbstractManager.js\"></script>");        
		$wgOut->addScript("<script type=\"text/javascript\" src=\"". $fsgScriptPath .  "/scripts/ajax-solr/lib/core/AbstractManager.js\"></script>");        
		$wgOut->addScript("<script type=\"text/javascript\" src=\"". $fsgScriptPath .  "/scripts/ajax-solr/lib/managers/Manager.jquery.js\"></script>");        
		$wgOut->addScript("<script type=\"text/javascript\" src=\"". $fsgScriptPath .  "/scripts/ajax-solr/lib/core/Parameter.js\"></script>");        
		$wgOut->addScript("<script type=\"text/javascript\" src=\"". $fsgScriptPath .  "/scripts/ajax-solr/lib/core/ParameterStore.js\"></script>");        
		$wgOut->addScript("<script type=\"text/javascript\" src=\"". $fsgScriptPath .  "/scripts/ajax-solr/lib/core/AbstractWidget.js\"></script>");        
		$wgOut->addScript("<script type=\"text/javascript\" src=\"". $fsgScriptPath .  "/scripts/ajax-solr/lib/core/AbstractFacetWidget.js\"></script>");        
		$wgOut->addScript("<script type=\"text/javascript\" src=\"". $fsgScriptPath .  "/scripts/ajax-solr/lib/core/ParameterStore.js\"></script>");        
//		$wgOut->addScript("<script type=\"text/javascript\" src=\"". $fsgScriptPath .  "/scripts/ajax-solr/lib/helpers/jquery/ajaxsolr.theme.js\"></script>");        
		
		$wgOut->addScript("<script type=\"text/javascript\" src=\"". $fsgScriptPath .  "/scripts/FacetedSearch/FS_Theme.js\"></script>");        
		$wgOut->addScript("<script type=\"text/javascript\" src=\"". $fsgScriptPath .  "/scripts/FacetedSearch/FS_ResultWidget.js\"></script>");        
		$wgOut->addScript("<script type=\"text/javascript\" src=\"". $fsgScriptPath .  "/scripts/FacetedSearch/FS_FacetWidget.js\"></script>");        
		$wgOut->addScript("<script type=\"text/javascript\" src=\"". $fsgScriptPath .  "/scripts/FacetedSearch/FS_FacetedSearch.js\"></script>");

		$wgOut->addStyle($fsgScriptPath . '/skin/faceted_search.css', 'screen, projection');
		
        global $smgJSLibs;
        $smgJSLibs[] = 'jquery';

    }
    
	/**
	 * Language dependent identifiers in $text that have the format {{identifier}}
	 * are replaced by the string that corresponds to the identifier.
	 * 
	 * @param string $text
	 * 		Text with language identifiers
	 * @return string
	 * 		Text with replaced language identifiers.
	 */
	private static function replaceLanguageStrings($text) {
		// Find all identifiers
		$numMatches = preg_match_all("/(\{\{(.*?)\}\})/", $text, $identifiers);
		if ($numMatches === 0) {
			return $text;
		}

		// Get all language strings
		$langStrings = array();
		foreach ($identifiers[2] as $id) {
			$langStrings[] = wfMsg($id);
		}
		
		// Replace all language identifiers
		$text = str_replace($identifiers[1], $langStrings, $text);
		return $text;
	}
    

}
