<?php
/**
 * @file
 * @ingroup SMWHaloJobs
 * 
 * @defgroup SMWHaloJobs SMWHalo jobs
 * @ingroup SMWHalo
 * 
 * @author Kai Kühn
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
require_once ("$IP/includes/JobQueue.php");

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
		global $wgParser, $wgContLang;

		$linkCache = & LinkCache :: singleton();
		$linkCache->clear();
		smwLog("start", "RF", "category refactoring");
		$article = new Article($this->updatetitle);
		$latestrevision = Revision :: newFromTitle($this->updatetitle);
		smwLog("oldtitle: ".$this->oldtitle, "RF", "category refactoring");
		smwLog("newtitle: ".$this->newtitle, "RF", "category refactoring");
		
		if ( !$latestrevision ) {
			$this->error = "SMW_UpdateCategoriesAfterMoveJob: Article not found " . $this->updatetitle->getPrefixedDBkey() . " ";
			wfDebug($this->error);
			return false;
		}
		
		$oldtext = $latestrevision->getRawText();

		//Category X moved to Y
		// Links changed accordingly:

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
		
		$newtext = preg_replace($search, $replace, $oldtext);
			
		$summary = 'Link(s) to ' . $this->newtitle . ' updated after page move by SMW_UpdateCategoriesAfterMoveJob. ' . $this->oldtitle . ' has been moved to ' . $this->newtitle;
		$article->doEdit($newtext, $summary, EDIT_FORCE_BOT);
		smwLog("finished editing article", "RF", "category refactoring");
		
		$options = new ParserOptions;
		$wgParser->parse($newtext, $this->updatetitle, $options, true, true, $latestrevision->getId());
		smwLog("finished parsing semantic data", "RF", "category refactoring");
		
		return true;
	}
}

