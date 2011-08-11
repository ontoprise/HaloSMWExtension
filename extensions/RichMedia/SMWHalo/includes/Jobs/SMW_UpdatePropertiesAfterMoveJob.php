<?php
/**
 * @file
 * @ingroup SMWHaloJobs
 *  
 * @author Kai Kühn
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
require_once ("$IP/includes/JobQueue.php");

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
		smwLog("start", "RF", "property refactoring");
		$article = new Article($this->updatetitle);
		$latestrevision = Revision :: newFromTitle($this->updatetitle);
		smwLog("oldtitle: ".$this->oldtitle, "RF", "property refactoring");
		smwLog("newtitle: ".$this->newtitle, "RF", "property refactoring");
		
		if ( !$latestrevision ) {
			$this->error = "SMW_UpdatePropertiesAfterMoveJob: Article not found " . $this->updatetitle->getPrefixedDBkey() . " ";
			wfDebug($this->error);
			return false;
		}

		$oldtext = $latestrevision->getRawText();

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
		$summary = 'Link(s) to ' . $this->newtitle . ' updated after page move by SMW_UpdatePropertiesAfterMoveJob. ' . $this->oldtitle . ' has been moved to ' . $this->newtitle;
		$article->doEdit($newtext, $summary, EDIT_FORCE_BOT);
		smwLog("finished editing article", "RF", "property refactoring");
		$options = new ParserOptions;
		$wgParser->parse($newtext, $this->updatetitle, $options, true, true, $latestrevision->getId());
		smwLog("finished parsing semantic data", "RF", "property refactoring");
		return true;
	}
}

