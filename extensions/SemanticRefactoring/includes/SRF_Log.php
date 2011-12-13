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
class SRFLog {

	private $mOperation;
	private $mAffectedTitle;
	private $mWikitext;

	public function __construct($operation, $affectedTitle, $wikitext = "") {
		$this->mOperation = $operation;
		$this->mAffectedTitle = $affectedTitle;
		$this->mWikitext = $wikitext;
	}

	public function getOperation() {
		return $this->mOperation;
	}

	public function getAffectedTitle() {
		return $this->mAffectedTitle;
	}

	public function getWikiText() {
		return $this->mWikitext;
	}

	public function asWikiText() {
		return $this->mOperation . " " . self::titleAsWikiText($this->mAffectedTitle);
	}

	public static function setAsWikitext(array $logs) {
		$wikitext = "";
		foreach($logs as $l) {
			$wikitext .= "\n*".$l->asWikiText();
		}
	}

	private static function titleAsWikiText($title) {
		if ($title->getNamespace() == NS_CATEGORY) {
			return "[[:".$title->getPrefixedText()."]]";
		}
		return "[[".$title->getPrefixedText()."]]";
	}
}