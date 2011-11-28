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
		$pcreator = SMWDIProperty::newFromUserLabel('___CREA');
		$pcreationDate = SMWDIProperty::newFromUserLabel('___CREADT');
		$diCreator = null;
		$diCreationDate = null;
		if ($title->getPreviousRevisionID($thisRevId) === false) {
			// Article is about to be created
			// => add creator and creation date
			$userTitle = Title::newFromText($user->getUserPage()->getFullText());
			$diCreator = new SMWDIWikiPage($userTitle->getDBkey(), $userTitle->getNamespace(),"");
			$date = getdate(wfTimestamp(TS_UNIX,$article->getTimestamp()));
			$diCreationDate = new SMWDITime(SMWDITime::CM_GREGORIAN, $date['year'],$date['mon'],$date['mday'],$date['hours'],$date['minutes'],$date['seconds']);
		} else {
			// Article already exists but creation properties do not change
			// => get them from the first revision of the article
			$firstRev = $article->getTitle()->getFirstRevision();
			$cuser = User::newFromId($firstRev->getUser(Revision::RAW));
			$userTitle = Title::newFromText($cuser->getUserPage()->getFullText());
            $diCreator = new SMWDIWikiPage($userTitle->getDBkey(), $userTitle->getNamespace(),"");
			$date = getdate(wfTimestamp(TS_UNIX,$firstRev->getTimestamp()));
            $diCreationDate = new SMWDITime(SMWDITime::CM_GREGORIAN, $date['year'],$date['mon'],$date['mday'],$date['hours'],$date['minutes'],$date['seconds']);
		}
		if ($diCreator) {
			$semdata->addPropertyObjectValue($pcreator, $diCreator);
		}

		if ($diCreationDate) {
			$semdata->addPropertyObjectValue($pcreationDate, $diCreationDate);
		}

		// store who modified the article
		$pmod = SMWDIProperty::newFromUserLabel('___MOD');
		$userTitle = Title::newFromText($user->getUserPage()->getFullText());
		$di = new SMWDIWikiPage($userTitle->getDBkey(), $userTitle->getNamespace(), "");
		$semdata->addPropertyObjectValue($pmod,$di);

		return true;
	}

	//--- Private methods ---
}
