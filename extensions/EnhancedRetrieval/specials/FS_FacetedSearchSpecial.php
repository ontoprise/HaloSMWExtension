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
$dir = dirname(__FILE__).'/';
require_once( $dir."../includes/FacetedSearch/FS_Settings.php" );


/*
 * Standard class that is responsible for the creation of the Special Page
 */
class FSFacetedSearchSpecial extends SpecialPage {
	
	//--- Constants ---
	
	const SPECIAL_PAGE_HTML = '
<div id="wrapper"> 
	<div class="facets">
		<div>
			<span class="xfsComponentHeader">{{fs_selected}}</span>
			<div id="selection">
			</div>
		</div>
		<hr class="xfsSeparatorLine">
		<span class="xfsComponentHeader">{{fs_available_facets}}</span>
		<div>
			<span class="xfsFacetHeader">{{fs_categories}}</span>
			<div id="field_categories">
			</div>
		</div> 
		<div>
			<span class="xfsFacetHeader">{{fs_properties}}</span>
			<div id="field_properties">
			</div>
		</div> 
	</div>
	<div class="results" id="results">
		<div id="field_namespaces" class="xfsNamespaces">
		</div>
		<div class="search">
			<input type="text" id="query" name="query" value="{{searchTerm}}" />
			<input type="button" id="search_button" name="search" value="{{fs_search}}" />
			<span class="xfsSortOrder">
				{{fs_sort_by}}
				<select id="search_order" name="search_order" size="1">
					<option value="relevance">{{fs_relevance}}</option>
					<option value="newest" selected="selected">{{fs_newest_date_first}}</option>
					<option value="oldest">{{fs_oldest_date_first}}</option>
					<option value="ascending">{{fs_title_ascending}}</option>
					<option value="descending">{{fs_title_descending}}</option>
				</select>
			</span>
			<div id="create_article"/>
		</div>
		<hr class="xfsSeparatorLine">
		<div id="navigation">
			<div id="pager-header"></div>
		</div>
		<div id="docs">
			{{fs_search_results}}
		</div>
		<div id="xfsFooter">
			<ul id="pager"></ul>
		</div>
	</div>
</div>
<div class="xfsCurrentSearchLink">
	<hr class="xfsSeparatorLine">
	<div id="current_search_link"></div>
</div>
';

    public function __construct() {
//        parent::__construct('FacetedSearch');
		parent::__construct('Search');
		global $wgHooks;
		$wgHooks['MakeGlobalVariablesScript'][] = "FSFacetedSearchSpecial::addJavaScriptVariables";
    }

    /**
     * Overloaded function that is responsible for the creation of the Special Page
     */
    public function execute($par) {
	
		global $wgOut, $wgRequest;
		
		$wgOut->setPageTitle(wfMsg('fs_title'));
		
		$search = str_replace( "\n", " ", $wgRequest->getText( 'search', '' ) );
		if ($search === wfMsg('smw_search_this_wiki')) {
			// If the help text of the search field is passed, assume an empty 
			// search string
			$search = '';
		}
		
		$restrict = $wgRequest->getText( 'restrict', '' );
		$specialPageTitle = $wgRequest->getText( 'title', '' );
		$t = Title::newFromText( $search );

		$fulltext = $wgRequest->getVal( 'fulltext', '' );
		$fulltext_x = $wgRequest->getVal( 'fulltext_x', '' );
		if ($fulltext == NULL && $fulltext_x == NULL) {
			
			# If the string cannot be used to create a title
			if(!is_null( $t ) ){

				# If there's an exact or very near match, jump right there.
				$t = SearchEngine::getNearMatch( $search );
				if( !is_null( $t ) ) {
					$wgOut->redirect( $t->getFullURL() );
					return;
				}

				# If just the case is wrong, jump right there.
//				$t = USStore::getStore()->getSingleTitle($search);
//				if (!is_null( $t ) ) {
//					$wgOut->redirect( $t->getFullURL() );
//					return;
//				}
			}
		}
        
		// Insert the search term into the input field of the UI
		$html = self::SPECIAL_PAGE_HTML;
		$html = str_replace('{{searchTerm}}', htmlspecialchars($search), $html);
		
		$wgOut->addHTML($this->replaceLanguageStrings($html));
		$wgOut->addModules('ext.facetedSearch.special');
    }

	/**
	 * Add a global JavaScript variable for the SOLR URL.
	 * @param $vars
	 * 		This array of global variables is enhanced with "wgFSSolrURL"
	 * 		and "wgFSCreateNewPageLink"
	 */
	public static function addJavaScriptVariables(&$vars) {
		global $fsgFacetedSearchConfig, $fsgCreateNewPageLink;
		$solrURL = "http://".$fsgFacetedSearchConfig['host']
		           .':'
		           .$fsgFacetedSearchConfig['port']
		           .'/solr/';
		
		$vars['wgFSSolrURL'] = $solrURL;
		$vars['wgFSCreateNewPageLink'] = $fsgCreateNewPageLink;
		
		return true;
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

