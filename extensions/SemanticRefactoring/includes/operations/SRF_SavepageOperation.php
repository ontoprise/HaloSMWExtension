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
class SRFSavepageOperation extends SRFInstanceLevelOperation {


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
		$logMessages[$title->getPrefixedText()][] = new SRFLog("Touched '$1'", $title, "", array($title));
		return $wikitext;
	}



	public function storeArticle($title, $wikitext, $comment) {

		$article = new Article($title);
		// will return warning that nothing changed, nevertheless
		$status = $article->doEdit($wikitext, $article->getComment());

		return Status::newGood();
	}
}