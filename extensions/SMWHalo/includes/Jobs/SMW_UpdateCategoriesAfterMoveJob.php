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
 * @defgroup SMWHaloJobs SMWHalo jobs
 * @ingroup SMWHalo
 *
 * @author Kai K�hn
 */

/* The SMW_UpdateCategoriesAfterMoveJob
 *
 * After a category X has been moved to Y all category links
 * of type X should be updated to be of type Y, i.e.
 *
 * [[Category:X]]						-> [[Category:Y]]
 * [[Category:X|alternative text]]  	-> [[Category:Y|alternative text]]
 *
 * @author Daniel M. Herzig
 * @author Denny Vrandecic
 *
 */

global $IP;
require_once ("$IP/includes/job/JobQueue.php");

class SMW_UpdateCategoriesAfterMoveJob extends Job {

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
			$this->error = "SMW_UpdateCategoriesAfterMoveJob: Article not found " . $this->updatetitle->getPrefixedDBkey() . " ";
			wfDebug($this->error);
			return false;
		}

		$oldtext = $latestrevision->getRawText();

		$newtext = $this->modifyPageContent($oldtext);
			
		$summary = 'Link(s) to ' . $this->newtitle . ' updated after page move by SMW_UpdateCategoriesAfterMoveJob. ' . $this->oldtitle . ' has been moved to ' . $this->newtitle;
		$article->doEdit($newtext, $summary, EDIT_FORCE_BOT);


		$options = new ParserOptions;
		$wgParser->parse($newtext, $this->updatetitle, $options, true, true, $latestrevision->getId());


		return true;
	}

	public function modifyPageContent($text) {
		//Category X moved to Y
		// Links changed accordingly:
		global $wgContLang;
		$cat = $wgContLang->getNsText(NS_CATEGORY);
		$catlcfirst = strtolower(substr($cat, 0, 1)) . substr($cat, 1);

		$oldtitlelcfirst = strtolower(substr($this->oldtitle, 0, 1)) . substr($this->oldtitle, 1);

		// [[[C|c]ategory:[S|s]omeCategory]]  -> [[[C|c]ategory:[S|s]omeOtherCategory]]
		$search[0] = '(\[\[(\s*)' . $cat . '(\s*):(\s*)' . $this->oldtitle . '(\s*)\]\])';
		$replace[0] = '[[${1}' . $cat . '${2}:${3}' . $this->newtitle . '${4}]]';

		$search[1] = '(\[\[(\s*)' . $catlcfirst . '(\s*):(\s*)' . $this->oldtitle . '(\s*)\]\])';
		$replace[1] = '[[${1}' . $catlcfirst . '${2}:${3}' . $this->newtitle . '${4}]]';

		$search[2] = '(\[\[(\s*)' . $cat . '(\s*):(\s*)' . $oldtitlelcfirst . '(\s*)\]\])';
		$replace[2] = '[[${1}' . $cat . '${2}:${3}' . $this->newtitle . '${4}]]';

		$search[3] = '(\[\[(\s*)' . $catlcfirst . '(\s*):(\s*)' . $oldtitlelcfirst . '(\s*)\]\])';
		$replace[3] = '[[${1}' . $catlcfirst . '${2}:${3}' . $this->newtitle . '${4}]]';

		// [[[C|c]ategory:[S|s]omeCategory | m]]  -> [[[C|c]ategory:[S|s]omeOtherCategory | m ]]
		$search[4] = '(\[\[(\s*)' . $cat . '(\s*):(\s*)' . $this->oldtitle . '(\s*)\|([^]]*)\]\])';
		$replace[4] = '[[${1}' . $cat . '${2}:${3}' . $this->newtitle . '${4}|${5}]]';

		$search[5] = '(\[\[(\s*)' . $catlcfirst . '(\s*):(\s*)' . $this->oldtitle . '(\s*)\|([^]]*)\]\])';
		$replace[5] = '[[${1}' . $catlcfirst . '${2}:${3}' . $this->newtitle . '${4}|${5}]]';

		$search[6] = '(\[\[(\s*)' . $cat . '(\s*):(\s*)' . $oldtitlelcfirst . '(\s*)\|([^]]*)\]\])';
		$replace[6] = '[[${1}' . $cat . '${2}:${3}' . $this->newtitle . '${4}|${5}]]';

		$search[7] = '(\[\[(\s*)' . $catlcfirst . '(\s*):(\s*)' . $oldtitlelcfirst . '(\s*)\|([^]]*)\]\])';
		$replace[7] = '[[${1}' . $catlcfirst . '${2}:${3}' . $this->newtitle . '${4}|${5}]]';

		return preg_replace($search, $replace, $text);
	}
}

