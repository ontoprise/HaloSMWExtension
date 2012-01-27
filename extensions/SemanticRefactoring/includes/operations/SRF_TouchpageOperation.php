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
class SRFTouchpageOperation extends SRFRefactoringOperation {

	private $instanceSet;



	public function __construct($instanceSet) {
		parent::__construct();
		foreach($instanceSet as $i) {
			$this->instanceSet[] = Title::newFromText($i);
		}
			
	}

	public function queryAffectedPages() {
		return $this->instanceSet;
	}

	public function getWork() {
		return count($this->instanceSet);
	}

	public function changeContent($title, $wikitext, & $logMessages) {
		// run ArticleSave hooks
		global $wgUser;
		$a = new Article($title);
		$rev = Revision::newFromTitle($title);
		$user = $wgUser;
		$text = $wikitext;
		$summary = $rev->getComment($audience);
		$flags = 0;

		 
		if ( !wfRunHooks( 'ArticleSave', array( &$a, &$user, &$text, &$summary,
		$flags & EDIT_MINOR, null, null, &$flags, &$status ) ) ) {
			if ( $status->isOK() ) {
				$status->fatal( 'edit-hook-aborted' );
			}
		}



		$logMessages[$title->getPrefixedText()][] = new SRFLog("Touched '$1'", $title, "", array($title));
		return $wikitext;
	}

	public function refactor($save = true, & $logMessages) {
		return;
	}

	public function storeArticle($title, $wikitext, $comment) {
		// do not store because nothing changed.
		return Status::newGood();
	}
}