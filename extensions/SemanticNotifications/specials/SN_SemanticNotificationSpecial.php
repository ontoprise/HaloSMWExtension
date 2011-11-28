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
 * @ingroup SemanticNotifications_Special
 *
 * A special page for defining and managing semantic notifications.
 *
 *
 * @author Thomas Schweitzer
 */

if (!defined('MEDIAWIKI')) die();


global $IP;
require_once( $IP . "/includes/SpecialPage.php" );


/*
 * Standard class that is resopnsible for the creation of the Special Page
 */
class SemanticNotificationSpecial extends SpecialPage {

	public function __construct() {
		parent::__construct('SemanticNotifications');
	}
	
	/**
	 * Overloaded function that is resopnsible for the creation of the Special Page
	 */
	public function execute($par) {

		global $wgRequest, $wgOut, $wgUser, $wgScript,$sngScriptPath;
		
		wfLoadExtensionMessages('SemanticNotification');
		
		$wgOut->setPageTitle(wfMsg('sn_special_page'));
		$imagepath = $sngScriptPath . '/skins/SemanticNotifications/images';
		
		// Development
//		$wgOut->addScript("<script type='text/javascript' src='$sngScriptPath/scripts/steal/steal.js?sngui,development'> </script>");

		// Production
		$wgOut->addScript("<script type='text/javascript' src='$sngScriptPath/scripts/steal/steal.js?sngui,production'> </script>");
		
		$wgOut->addHTML("<div id='sn-main-div' style='overflow:hidden'/>");
		
		return;
	}

}

?>
