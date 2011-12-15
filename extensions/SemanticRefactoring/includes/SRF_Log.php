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
	private $mArgs;

	public function __construct($operation, $affectedTitle, $wikitext = "", $args = array()) {
		$this->mOperation = $operation;
		$this->mAffectedTitle = $affectedTitle;
		$this->mWikitext = $wikitext;
		$this->mArgs = $args;
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

	public function setWikiText($wikitext) {
		$this->mWikitext = $wikitext;
	}

	public function asWikiText() {
		$text = $this->mOperation;
		for($i = 0, $n=count($this->mArgs); $i < $n; $i++) {
			$arg = $this->mArgs[$i];
			if ($arg instanceof Title) $repl = self::titleAsWikiText($arg); else $repl = $arg;
			$text = str_replace('$'.($i+1),$repl, $text);
		}
		$text = str_replace('$title', self::titleAsWikiText($this->mAffectedTitle), $text);
		return $text;
	}

	public static function asWikiTextFromLogMessages(array $logMessages) {
		$wikitext = "";
		foreach($logMessages as $prefixedText => $set) {
			$title = Title::newFromText($prefixedText);
			$wikitext .= "\n*".self::titleAsWikiText($title);
			foreach($set as $l) {
				$wikitext .= "\n**".$l->asWikiText();
			}
		}
		return $wikitext;
	}

	public static function titleAsWikiText($title) {
		if ($title->getNamespace() == NS_CATEGORY) {
			return "[[:".$title->getPrefixedText()."]]";
		}
		return "[[".$title->getPrefixedText()."]]";
	}
}