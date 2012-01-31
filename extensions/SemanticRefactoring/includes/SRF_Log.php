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

define ('SREF_LOG_STATUS_INFO', 0);
define ('SREF_LOG_STATUS_WARN', 1);

class SRFLog {

	private $mOperation;
	private $mAffectedTitle;
	private $mWikitext;
	private $mArgs;
	private $mType;

	public function __construct($operation, $affectedTitle, $wikitext = "", $args = array()) {
		$this->mOperation = $operation;
		$this->mAffectedTitle = $affectedTitle;
		$this->mWikitext = $wikitext;
		$this->mArgs = $args;
		$this->mType = SREF_LOG_STATUS_INFO;
	}
	
	public function setLogType($type) {
		$this->mType = $type;
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
		if ($this->mType == SREF_LOG_STATUS_WARN) {
			return '<span style="color:#ff0000">'.$text.'</span>';
		}
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
	
	private static function getHistoryLink($title) {
		return "[".$title->getFullURL(array('action' => 'history'))." History]";
	}

	public static function titleAsWikiText($title) {
		if ($title->getNamespace() == NS_CATEGORY) {
			return "[[:".$title->getPrefixedText()."]]";
		}
		return "[[".$title->getPrefixedText()."]]";
	}
}