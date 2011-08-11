<?php
/*  Copyright 2011, ontoprise GmbH
 *  This file is part of the SMWHalo extension of the Enhanced Retrieval Extension.
 *
 *   The SMWHalo extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The SMWHalo extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Adds the handling for the additional builtin properties Creation date, Creator
 * and Last modified by
 *
 * @author Thomas Schweitzer
 * Date: 06.05.2011
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the Enhanced Retrieval Extension extension. It is not a valid entry point.\n" );
}

//--- Includes ---

/**
 * This class contains the method for adding/changing the builtin properties
 * when a new revision of an article is saved.
 *
 * @author Thomas Schweitzer
 *
 */
class SMWArticleBuiltinProperties  {

	//--- Constants ---

	//--- Private fields ---

	/**
	 * The class can not be instantiated
	 */
	private function __construct() {
	}


	//--- getter/setter ---

	//--- Public methods ---

	/**
	 * This method is called when a new revision of an article is created.
	 * It adds or changes the builtin properties for this article.
	 *
	 * @param $article
	 * @param $rev
	 * @param $baseID
	 * @param $user
	 */
	public static function onNewRevisionFromEditComplete($article, $rev, $baseID, $user) {
		// Do we have to constrain it to submission?		if ($wgRequest->getVal('action') == 'submit') {
		// I guess action is not 'submit' when this is called from a job
		
		global $wgContLang, $smwgContLang;

		if (($article->mPreparedEdit) && ($article->mPreparedEdit->output instanceof ParserOutput)) {
			$output = $article->mPreparedEdit->output;
			$title = $article->getTitle();

			if ( !isset( $title ) ) {
				return true; // nothing we can do
			}
			if ( !isset( $output->mSMWData ) ) { // no data container yet, make one
				$output->mSMWData = new SMWSemanticData( SMWWikiPageValue::makePageFromTitle( $title ) );
			}

			$semdata = $output->mSMWData;
		} else { // give up, just keep the old data
			return true;
		}

		$thisRevId = $rev->getId();
		$pcreator = SMWPropertyValue::makeProperty('___CREA');
		$pcreationDate = SMWPropertyValue::makeProperty('___CREADT');
		$dvCreator = null;
		$dvCreationDate = null;
		if ($title->getPreviousRevisionID($thisRevId) === false) {
			// Article is about to be created
			// => add creator and creation date
			$dvCreator = SMWDataValueFactory::newPropertyObjectValue($pcreator, $user->getUserPage()->getFullText());
			$dvCreationDate = SMWDataValueFactory::newPropertyObjectValue($pcreationDate, $wgContLang->sprintfDate('d M Y G:i:s',$article->getTimestamp()));
		} else {
			// Article already exists but creation properties do not change
			// => get them from the first revision of the article
			$firstRev = $article->getTitle()->getFirstRevision();
			$cuser = User::newFromId($firstRev->getUser(Revision::RAW));
			$dvCreator = SMWDataValueFactory::newPropertyObjectValue($pcreator, $cuser->getUserPage()->getFullText());
			$dvCreationDate = SMWDataValueFactory::newPropertyObjectValue($pcreationDate, $wgContLang->sprintfDate('d M Y G:i:s',$firstRev->getTimestamp()));
		}
		if ($dvCreator) {
			$semdata->addPropertyObjectValue($pcreator, $dvCreator);
		}

		if ($dvCreationDate) {
			$semdata->addPropertyObjectValue($pcreationDate, $dvCreationDate);
		}

		// store who modified the article
		$pmod = SMWPropertyValue::makeProperty('___MOD');
		$dv = SMWDataValueFactory::newPropertyObjectValue($pmod,  $user->getUserPage()->getFullText());
		$semdata->addPropertyObjectValue($pmod,$dv);

		return true;
	}

	//--- Private methods ---
}