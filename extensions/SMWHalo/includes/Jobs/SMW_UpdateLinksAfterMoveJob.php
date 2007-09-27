<?php


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
require_once ("$IP/includes/JobQueue.php");

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

		//Page X moved to Y
		// Links changed accordingly:

		// [[X]] 			-> [[Y|X]]
		$search[0] = '(\[\[(\s*)' . $this->oldtitle . '(\s*)\]\])';
		$replace[0] = '[[${1}' . $this->newtitle . '${2}|'.$this->oldtitle.']]';

		// [[X|blabla]]]	-> [[Y|blabla]]
		$search[1] = '(\[\[(\s*)' . $this->oldtitle . '(\s*)\|([^]]*)?\]\])';
		$replace[1] = '[[${1}' . $this->newtitle . '${2}|${3}]]';

		// [[m::X]]			-> [[m::Y|X]]
		$search[2] = '(\[\[(([^:][^]]*)::)+(\s*)' . $this->oldtitle . '(\s*)\]\])';
		$replace[2] = '[[${1}${3}' . $this->newtitle . '${4}|'.$this->oldtitle.']]';

		// [[m::X|blabla]]  -> [[m::Y|blabla]]
		$search[3] = '(\[\[(([^:][^]]*)::)+(\s*)' . $this->oldtitle . '(\s*)\|([^]]*)\]\])';
		$replace[3] = '[[${1}${3}' . $this->newtitle . '${4}|${5}]]';

		$newtext = preg_replace($search, $replace, $oldtext);
		$summary = 'Link(s) to ' . $this->newtitle . ' updated after page move by SMW_UpdateLinksAfterMoveJob. ' . $this->oldtitle . ' has been moved to ' . $this->newtitle;
		$article->doEdit($newtext, $summary, EDIT_FORCE_BOT);

		$options = new ParserOptions;
		$wgParser->parse($newtext, $this->updatetitle, $options, true, true, $latestrevision->getId());

		return true;
	}
}
?>
