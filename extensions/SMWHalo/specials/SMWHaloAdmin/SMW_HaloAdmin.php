<?php
/**
 * Created on 12.03.2007
 *
 * @file
 * @ingroup SMWHaloSpecials
 * @ingroup SMWHaloAdmin
 *
 * @author Kai Kühn
 */
if (!defined('MEDIAWIKI')) die();

global $IP;
require_once( "$IP/includes/SpecialPage.php" );

/**
 * SMWHalo Administration page
 *
 * @author Kai Kühn / ontoprise / 2011
 *
 */
class SMWHaloAdmin extends SpecialPage {


	public function __construct() {
		parent::__construct('SMWHaloAdmin', 'delete');
	}

	public function execute($par) {
		global $wgRequest, $wgOut, $smwgMessageBroker, $smwgWebserviceEndpoint, $wgUser, $smwgEnableObjectLogicRules;
		$wgOut->setPageTitle(wfMsg('smwhaloadmin'));
		$adminPage = Title::newFromText("SMWHaloAdmin", NS_SPECIAL);
		$html = wfMsg('smw_haloadmin_description');

		if ($wgRequest->getVal('init') != NULL) {
			$wgOut->disable(); // raw output
			ob_start();
			print "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"  \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\" dir=\"ltr\">\n<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /><title>Setting up Storage for Semantic MediaWiki</title></head><body><p><pre>";
			header( "Content-type: text/html; charset=UTF-8" );
			$result = 	smwfGetSemanticStore()->setup(true);

			print '</pre></p>';
			if ( $result === true ) {
				print '<p><b>' . wfMsg( 'smw_smwadmin_setupsuccess' ) . "</b></p>\n";
			}
			$returntitle = SpecialPage::getTitleFor( 'SMWAdmin' );
			print '<p> ' . wfMsg( 'smw_smwadmin_return', '<a href="' . htmlspecialchars( $adminPage->getFullURL() ) . '">Special:SMWHaloAdmin</a>' ) . "</p>\n";
			print '</body></html>';
			ob_flush();
			flush();
			return;

		}
		$messages= array();
		if (smwfGetSemanticStore()->isInitialized($messages)) {
			wfMsg('smw_haloadmin_alreadyinitialized');
			$html .= "<br><br><form><input name=\"init\" type=\"submit\" value=\"".wfMsg('smw_tsa_reinitialize')."\"/><input name=\"title\" type=\"hidden\" value=\"".$adminPage->getPrefixedDBkey()."\"/></form>";
			$html .= "<h2>".wfMsg('smw_tsa_status')."</h2>";
			$html .= wfMsg('smw_haloadmin_ok');
		}else {
				
			$html .= "<br><br><form><input name=\"init\" type=\"submit\" value=\"".wfMsg('smw_tsa_initialize')."\"/><input name=\"title\" type=\"hidden\" value=\"".$adminPage->getPrefixedDBkey()."\"/></form>";
			$html .= "<h2>".wfMsg('smw_tsa_status')."</h2>";
			foreach($messages as $m) {
				$html .= "<br>".$m;
			}
		}

		$wgOut->addHTML($html);
	}


}

