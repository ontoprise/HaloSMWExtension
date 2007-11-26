<?php
/*
 * Created on 26.11.2007
 *
 * Author: kai
 */
 class SMWFindWork extends SpecialPage {
	
	public function __construct() {
		parent::__construct('FindWork');
	}
	
	public function execute() {
		global $wgRequest, $wgOut;
		$specialAttPage = Title::newFromText('FindWork', NS_SPECIAL);
		$wgOut->setPageTitle(wfMsg('findwork'));
		
	}
 }
?>
