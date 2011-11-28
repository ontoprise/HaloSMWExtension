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
 * @author Kai Kühn
 *
 */
class SMWHaloAdmin extends SpecialPage {


	public function __construct() {
		parent::__construct('SMWHaloAdmin', 'delete');
	}

	public function execute($par) {
		global $wgRequest, $wgOut, $smwgMessageBroker, $smwgHaloWebserviceEndpoint, $wgUser, $smwgHaloEnableObjectLogicRules;
		$wgOut->setPageTitle(wfMsg('smwhaloadmin'));
		$adminPage = Title::newFromText("SMWHaloAdmin", NS_SPECIAL);
		
		$html = "<h1>".wfMsg('smwhaloadmin')."</h1>";
		$html .= wfMsg('smw_haloadmin_description');
        $html .= "<h2>".wfMsg('smw_haloadmin_databaseinit')."</h2>";
        $html .= wfMsg('smw_haloadmin_databaseinit_description');
        
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
			$html .= "<form><input name=\"init\" type=\"submit\" value=\"".wfMsg('smw_tsa_reinitialize')."\"/><input name=\"title\" type=\"hidden\" value=\"".$adminPage->getPrefixedDBkey()."\"/></form>";
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

