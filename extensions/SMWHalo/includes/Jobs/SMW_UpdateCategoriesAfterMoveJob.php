<?php


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
		
		$article = new Article($this->updatetitle);
		$latestrevision = Revision :: newFromTitle($this->updatetitle);

		if ( !$latestrevision ) {
			$this->error = "SMW_UpdateCategoriesAfterMoveJob: Article not found " . $this->updatetitle->getPrefixedDBkey() . " ";
			wfDebug($this->error);
			return false;
		}
		
		$oldtext = $latestrevision->getRawText();

		//Category X moved to Y
		// Links changed accordingly:

		$cat = $wgContLang->getNsText(NS_CATEGORY);
		
		// [[Category:X]]  -> [[Category:Y]]
		$search[0] = '(\[\[(\s*)' . $cat . '(\s*):(\s*)' . $this->oldtitle . '(\s*)\]\])';
		$replace[0] = '[[${1}' . $cat . '${2}:${3}' . $this->newtitle . '${4}]]';

		// [[Category:X|m]]  -> [[Category:Y|m]]
		$search[1] = '(\[\[(\s*)' . $cat . '(\s*):(\s*)' . $this->oldtitle . '(\s*)\|([^]]*)\]\])';
		$replace[1] = '[[${1}' . $cat . '${2}:${3}' . $this->newtitle . '${4}|${5}]]';

		$newtext = preg_replace($search, $replace, $oldtext);
			
		$summary = 'Link(s) to ' . $this->newtitle . ' updated after page move by SMW_UpdateCategoriesAfterMoveJob. ' . $this->oldtitle . ' has been moved to ' . $this->newtitle;
		$article->doEdit($newtext, $summary, EDIT_FORCE_BOT);
		
		$options = new ParserOptions;
		$wgParser->parse($newtext, $this->updatetitle, $options, true, true, $latestrevision->getId());

		return true;
	}
}
?>
