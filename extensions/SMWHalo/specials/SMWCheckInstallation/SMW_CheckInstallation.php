<?php 
/**
 * @file
 * @ingroup SMWCheckInstallation
 * 
 * @defgroup SMWCheckInstallation 
 * @ingroup SMWHaloSpecials
 * 
 * @author Kai Kühn
 */
if (!defined('MEDIAWIKI')) die();

global $IP;
require_once( $IP . "/includes/SpecialPage.php" );

/*
 * Standard class that is resopnsible for the creation of the Special Page
 */
class SMWCheckInstallation extends SpecialPage {
	public function __construct() {
		parent::__construct('CheckInstallation');
	}
	/*
	 * Overloaded function that is responsible for the creation of the Special Page
	 */
	public function execute($par) {
		global $wgOut;
		$wgOut->setPageTitle(wfMsg('checkinstallation'));
		//TODO: add code for starting checkInstallation.php
		$wgOut->addHTML("TODO: Implement it");
	}
}
