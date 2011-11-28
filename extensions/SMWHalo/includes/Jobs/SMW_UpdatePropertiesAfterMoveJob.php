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

/* The SMW_UpdatePropertiesAfterMoveJob
 *
 * After a property X has been moved to Y all typed links
 * of type X should be updated to be of type Y, i.e.
 *
 * [[X::m]]						-> [[Y::m]]
 * [[X::m|alternative text]]  	-> [[Y::m|alternative text]] *
 *
 * @author Daniel M. Herzig
 * @author Denny Vrandecic
 *
 */

global $IP;
require_once ("$IP/includes/job/JobQueue.php");

class SMW_UpdatePropertiesAfterMoveJob extends Job {

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
			$this->error = "SMW_UpdatePropertiesAfterMoveJob: Article not found " . $this->updatetitle->getPrefixedDBkey() . " ";
			wfDebug($this->error);
			return false;
		}

		$oldtext = $latestrevision->getRawText();
		$newtext = $this->modifyPageContent($oldtext);
		$summary = 'Link(s) to ' . $this->newtitle . ' updated after page move by SMW_UpdatePropertiesAfterMoveJob. ' . $this->oldtitle . ' has been moved to ' . $this->newtitle;
		$article->doEdit($newtext, $summary, EDIT_FORCE_BOT);

		$options = new ParserOptions;
		$wgParser->parse($newtext, $this->updatetitle, $options, true, true, $latestrevision->getId());

		return true;
	}

	public function modifyPageContent($oldtext) {
		//Page X moved to Y
		// Links changed accordingly:

		// [[X::m]]  -> [[Y::m]]
		$search[0] = '(\[\[(\s*)' . $this->oldtitle . '(\s*)::([^]]*)\]\])';
		$replace[0] = '[[${1}' . $this->newtitle . '${2}::${3}]]';

		// [[X:=m]]  -> [[Y:=m]]
		$search[1] = '(\[\[(\s*)' . $this->oldtitle . '(\s*):=([^]]*)\]\])';
		$replace[1] = '[[${1}' . $this->newtitle . '${2}:=${3}]]';

		// TODO check if the wiki is case sensitive on the first letter
		// This is not the case for the Halo wikis
		$oldtitlelcfirst = strtolower(substr($this->oldtitle, 0, 1)) . substr($this->oldtitle, 1);

		// [[x::m]]  -> [[Y::m]]
		$search[2] = '(\[\[(\s*)' . $oldtitlelcfirst . '(\s*)::([^]]*)\]\])';
		$replace[2] = '[[${1}' . $this->newtitle . '${2}::${3}]]';

		// [[x:=m]]  -> [[Y:=m]]
		$search[3] = '(\[\[(\s*)' . $oldtitlelcfirst . '(\s*):=([^]]*)\]\])';
		$replace[3] = '[[${1}' . $this->newtitle . '${2}:=${3}]]';

		$newtext = preg_replace($search, $replace, $oldtext);
		return $newtext;
	}
}

