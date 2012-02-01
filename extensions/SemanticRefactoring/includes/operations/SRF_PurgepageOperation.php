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
class SRFPurgepageOperation extends SRFInstanceLevelOperation {


	public function __construct($instanceSet) {
		parent::__construct();
	}

	public function queryAffectedPages() {
		return $this->instanceSet;
	}

	public function getWork() {
		return count($this->instanceSet);
	}

	public function preview() {
		return array('sref_changedpage' => $this->getWork());
	}

	public function applyOperation($title, $wikitext, & $logMessages) {
		$logMessages[$title->getPrefixedText()][] = new SRFLog("Purged '$1'", $title, "", array($title));
		return $wikitext;
	}



	public function storeArticle($title, $wikitext, $comment) {
		// do not store because nothing changed.
		// only purge
		global $wgUser;
		$a = new Article($title);
		$a->doPurge();

		return Status::newGood();;
	}
}