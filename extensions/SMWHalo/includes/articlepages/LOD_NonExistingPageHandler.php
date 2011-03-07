<?php
/**
 * @file
 * @ingroup LinkedData
 */

/*  Copyright 2010, ontoprise GmbH
 *  This file is part of the LinkedData-Extension.
 *
 *   The LinkedData-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The LinkedData-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * This file contains the class LODNonExistingPageHandler
 *
 * @author Thomas Schweitzer
 * Date: 10.09.2010
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

//--- Includes ---
global $lodgIP;
//require_once("$lodgIP/...");

global $smwgHaloIP;
$wgAutoloadClasses['TSHelper'] = $smwgHaloIP . '/includes/storage/SMW_TS_Helper.php';


/**
 * This class handles the content creation for non-existing pages.
 *
 * @author Thomas Schweitzer
 *
 */
class  LODNonExistingPageHandler  {

	//--- Public methods ---

	/**
	 * This method is called when a new article object is created. It can decide
	 * which sub-class of Article is created.
	 * If the article does not exist yet, an instance of LODNonExistingPage
	 * is returned.
	 *
	 * @param Title $title
	 * 		The title is the basis for the article
	 * @param Article $article
	 * 		This variable is modified if the article does not exist, the action
	 * 		is "view" and the title is part of the request.
	 *
	 */
	public static function onArticleFromTitle(Title &$title, &$article) {
		global $wgRequest, $mediaWiki;

		$action = $wgRequest->getVal('action', 'view');
		$isView = $action === 'view';
		$isRedlink = $wgRequest->getVal('redlink', '') === '1';
		$uri = $wgRequest->getVal('uri', '');

		if (!$title->exists()
		&& ($isView || $isRedlink)
		&& $wgRequest->getVal('title') === $title->getPrefixedDBkey()) {
			$article = new LODNonExistingPage($title, $uri);
			// Overwrite the edit mode in case of a redlink
			if ($action === 'edit') {
				$action = 'view';
				$mediaWiki->setVal('action', $action);
			}
		}

		return true;
	}

	/**
	 * Called when edit page for new article is shown. If the request contains
	 * the parameter 'preloadNEP' with value <true>, the edit field is filled
	 * with the content that is displayed for non-empty pages.
	 *
	 * @param string $text
	 * @param Title $title
	 */
	public static function onEditFormPreloadText(&$text, Title &$title) {
		global $wgRequest;
		if ($wgRequest->getVal('preloadNEP') === 'true') {
			$text = LODNonExistingPage::getContentOfNEP(new Article($title));
			$uri = $wgRequest->getVal('uri', '');
			if ($uri != '') {
				// if URI is set add an ontology URI link
				global $smwgHaloContLang;
				$ssp = $smwgHaloContLang->getSpecialSchemaPropertyArray();
				$ontologyURIProperty = $ssp[SMW_SSP_ONTOLOGY_URI];
				$text = "[[$ontologyURIProperty::$uri |]]\n\n".$text;
			}
		}
		return true;
	}

	//--- Private methods ---

}
