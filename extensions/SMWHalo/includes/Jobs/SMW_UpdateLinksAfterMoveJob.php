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
 * @file
 * @ingroup SMWHaloJobs
 *
 * @author Kai K�hn
 */

/* The SMW_UpdateLinksAfterMoveJob
 *
 * After a page X has been moved to Y all links on pages linking
 * to X are updated accordingly to the following pattern:
 *
 * Links to X:
 * [[X]] 						-> [[Y|X]]
 * [[X|alternative text]]]		-> [[Y|alternative text]]
 *
 * Relations to X:
 * [[m::X]]						-> [[m::Y|X]]
 * [[m::X|alternative text]]  	-> [[m::Y|alternative text]] *
 *
 * @author Daniel M. Herzig
 *
 */

global $IP;
require_once ("$IP/includes/job/JobQueue.php");

class SMW_UpdateLinksAfterMoveJob extends Job {

	protected $updatetitle, $newtitle, $oldtitle;

	//Constructor
	function __construct(&$uptitle, $params, $id = 0) {

		$this->updatetitle = $uptitle;
		$this->oldtitle = $params[0];
		$this->newtitle = $params[1];

		parent :: __construct(get_class($this), $uptitle, $params, $id);

	}

	/**
	 * Run method
	 * @return boolean success
	 */
	function run() {
		global $wgParser;

		$linkCache = & LinkCache :: singleton();
		$linkCache->clear();

		$article = new Article($this->updatetitle);
		$latestrevision = Revision :: newFromTitle($this->updatetitle);


		if ( !$latestrevision ) {
			$this->error = "SMW_UpdateLinksAfterMoveJob: Article not found " . $this->updatetitle->getPrefixedDBkey() . " ";
			wfDebug($this->error);
			return false;
		}

		$oldtext = $latestrevision->getRawText();

        $newtext = $this->modifyPageContent($oldtext);

		// save and parse article
		$summary = 'Link(s) to ' . $this->newtitle . ' updated after page move by SMW_UpdateLinksAfterMoveJob. ' . $this->oldtitle . ' has been moved to ' . $this->newtitle;
		$article->doEdit($newtext, $summary, EDIT_FORCE_BOT);


		$options = new ParserOptions;
		$wgParser->parse($newtext, $this->updatetitle, $options, true, true, $latestrevision->getId());


		return true;
	}
	
	public function modifyPageContent($oldtext) {
		//Page X moved to Y
		// Links changed accordingly:

		// [[X]]            -> [[Y|X]]
		$search[0] = '(\[\[(\s*)' . $this->oldtitle . '(\s*)\]\])';
		$replace[0] = '[[${1}' . $this->newtitle . '${2}|'.$this->oldtitle.']]';

		// [[X|blabla]]]    -> [[Y|blabla]]
		$search[1] = '(\[\[(\s*)' . $this->oldtitle . '(\s*)\|([^]]*)?\]\])';
		$replace[1] = '[[${1}' . $this->newtitle . '${2}|${3}]]';



		// pattern to get all anntations (including n-aries!)
		$semanticLinkPattern = '/\[\[' .    // Beginning of the link
                        '([^]:]+):[:=]' .   // Property name
                        '(' .
                        '(?:[^|\[\]] |' .   // either normal text (without |, [ or ])
                        '\[\[[^]]*\]\] |' . // or a [[link]]
                        '\[[^]]*\]' .       // or a [external link]
                        ')*)' .             // all this zero or more times
                        '(\|[^]]*)?' .      // Display text (like "text" in [[link|text]]), optional
                        '\]\]' .            // End of link
                        '/x';               // ignore whitespaces


		// search object links
		preg_match_all($semanticLinkPattern, $oldtext, $matches);


		// identify object links to oldtitle
		// save the index and the (changed) link
		$indicesToReplace = array();
		for($i= 0, $n = count($matches[2]); $i < $n; $i++) {
			$updated = false;

			$frgs = explode(";", $matches[2][$i]);
			for($j = 0, $m = count($frgs); $j < $m; $j++) {
				if (trim($frgs[$j]) == $this->oldtitle) {
					$frgs[$j] = $this->newtitle;
					$updated = true;
				}
			}

			if ($updated) {
				if ($frgs === false) {
					$indicesToReplace[$i] = "";
				} else {
					$indicesToReplace[$i] = (count($frgs) == 1) ? trim($frgs[0]) : trim(implode("; ", $frgs));
				}
			}
		}

		// replace object links
		$newtext = $oldtext;
		foreach($indicesToReplace as $i => $l) {
			$newtext = preg_replace('(\[\['.$matches[1][$i].':[:=]'.$matches[2][$i].$matches[3][$i].'\]\])', '[['.$matches[1][$i].'::'.$l.$matches[3][$i].']]', $newtext);
		}
        $newtext = preg_replace($search, $replace, $newtext);
		return $newtext;
	}
}

