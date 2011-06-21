<?php

/**
 * @file
 * @ingroup CEComment
 * 
 * CE_CommentSpecial - comment management.
 *
 * @addtogroup SpecialPage
 *
 * @author Benjamin Langguth
 */

//@TODO: license & modify query...

if (!defined('MEDIAWIKI')) die();

global $IP;
require_once( $IP . "/includes/SpecialPage.php" );

class CECommentSpecial extends SpecialPage {

	public function __construct() {
		parent::__construct('Collaboration');
	}


	function execute($query) {
		global $wgRequest, $wgOut, $wgScriptPath, $wgUser, $wgParser, $wgTitle, $smwIP;

		$wgOut->setPageTitle(wfMsg('collaboration'));

		$introText = wfMsg('ce_sp_intro');
		$wgOut->addHTML($introText);
		$queryText = '{{#ask: [[Comment:+]]
  | mainlabel = Comment
  | ?Has comment person = Person
  | ?Has comment date = Date
  | ?Has comment text = Content
  | ?Has comment rating = Rating
  | ?Comment was deleted = Deleted?
  | ?Belongs to article = Related article
  | ?Belongs to comment = Related comment
  | sort = Has comment date
  | order = desc
  | limit = 200
  | searchlabel = <<...further comments>>
  | default=No comments existent in this wiki.
}}';
		
		$popt = new ParserOptions();
		$popt->setEditSection(false);
		
		$pout = $wgParser->parse($queryText, $wgTitle, $popt);
		$result = $pout->getText();
		$wgOut->addHTML($result);
	}
}