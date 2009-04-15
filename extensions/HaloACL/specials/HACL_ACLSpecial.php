<?php
/*  Copyright 2009, ontoprise GmbH
* 
*   This file is part of the HaloACL-Extension.
*
*   The HaloACL-Extension is free software; you can redistribute 
*   it and/or modify it under the terms of the GNU General Public License as 
*   published by the Free Software Foundation; either version 3 of the License, 
*   or (at your option) any later version.
*
*   The HaloACL-Extension is distributed in the hope that it will 
*   be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * A special page for defining and managing Access Control Lists.
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
class HaloACLSpecial extends SpecialPage {

	public function __construct() {
		parent::__construct('HaloACL');
	}
	
	/**
	 * Overloaded function that is resopnsible for the creation of the Special Page
	 */
	public function execute() {

		global $wgOut, $wgRequest, $wgLang;
		
		wfLoadExtensionMessages('HaloACL');
		$wgOut->setPageTitle(wfMsg('hacl_special_page'));
		
		$action = $wgRequest->getText('action');
		if ($action == "initHaloACLDB") {
			// Initialize the database tables for HaloACL and show the 
			// results on the special page.
			global $haclgIP, $wgOut;
			require_once("$haclgIP/includes/HACL_Storage.php");

			$wgOut->disable(); // raw output
			ob_start();
			print "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"  \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\" dir=\"ltr\">\n<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /><title>Setting up Storage for Semantic MediaWiki</title></head><body><p><pre>";
			header( "Content-type: text/html; charset=UTF-8" );
			print "Initializing the HaloACL database...";
			$result = HACLStorage::getDatabase()->initDatabaseTables();
			print '</pre></p>';
			if ($result === true) {
				print '<p><b>' . wfMsg('hacl_db_setupsuccess') . "</b></p>\n";
			}
			$returntitle = Title::makeTitle(NS_SPECIAL, 'HaloACL');
			$special = $wgLang->getNamespaces();
			$special = $special[NS_SPECIAL];
			print '<p> ' . wfMsg('hacl_haloacl_return'). '<a href="' . 
			      htmlspecialchars($returntitle->getFullURL()) . 
			      '">'.$special.":".wfMsg('hacl_special_page')."</a></p>\n";
			print '</body></html>';
			ob_flush();
			flush();
			return;
			
		} if ($action == "HaloACLTest") {
			self::test();
		} else {
			$ttInitializeDatabase = wfMsg('hacl_tt_initializeDatabase');
			$initializeDatabase   = wfMsg('hacl_initializeDatabase');
			$html = <<<HTML
<form name="initHaloACLDB" action="" method="POST">
<input type="hidden" name="action" value="initHaloACLDB" />
<input type="submit" value="$initializeDatabase"/>
</form>

<form name="HaloACLTest" action="" method="POST">
<input type="hidden" name="action" value="HaloACLTest" />
<input type="submit" value="Test"/>
</form>
HTML;
//			  <button id="hacl-initdb-btn" style="float:left;"
//			          onmouseover="Tip('$ttInitializeDatabase')">
//	      	     $initializeDatabase
//			  </button>
			$wgOut->addHTML($html);
		}
	}
	
	/**
	 * Function for testing new stuff
	 *
	 */
	private function test() {
		try {
			$group1 = new HACLGroup(null, "Group1", 
			                       array(2, "Group1"),
			                       array(3, "WikiSysop"));
			$group1->save();
			$group2 = new HACLGroup(null, "Group2", 
			                       array(2, "Group2"),
			                       array(3, "WikiSysop"));
			$group2->save();
			$group3 = new HACLGroup(null, "Group3", null, array("WikiSysop"));
			$group3->save();
			$group4 = new HACLGroup(null, "Group4", null, array("WikiSysop"));
			$group4->save();
			$group5 = new HACLGroup(null, "Group5", null, array("WikiSysop"));
			$group5->save();
			$group6 = new HACLGroup(null, "Group6", null, array("WikiSysop"));
			$group6->save();
			$group7 = new HACLGroup(null, "Group7", null, array("WikiSysop"));
			$group7->save();
			$group8 = new HACLGroup(null, "Group8", null, array("WikiSysop"));
			$group8->save();
			$group9 = new HACLGroup(null, "Group9", null, array("WikiSysop"));
			$group9->save();
			$group10 = new HACLGroup(null, "Group10", array("Group1"), array("WikiSysop"));
			$group10->save();
			
			$group1->addGroup($group2);
			$group1->addGroup($group3);
			
			$group2->addUser('Thomas');
			$group2->addGroup($group4);

			$group3->addGroup($group6);

			$group4->addGroup($group5);
			$group4->addGroup($group2);

			$group5->addUser("Thomas");

			$group6->addGroup($group2);
			$group6->addGroup($group7);
			
			$group7->addUser("Thomas");

			$group8->addGroup($group6);
			
			$group9->addGroup($group4);
			
			$group10->addGroup($group3, "Thomas");
			
			$group1->hasUserMember("WikiSysop", true);
			
//			$group->hasGroupMember("Group1", true);
//			$group->hasGroupMember("Group2", true);
//			$group->hasUserMember("Thomas", true);
//			$group2->hasUserMember("WikiSysop", true);
			
//			var_dump($group->getUsers(HACLGroup::ID));
//			var_dump($group->getUsers(HACLGroup::NAME));
//			var_dump($group->getUsers(HACLGroup::OBJECT));
//
//			var_dump($group->getGroups(HACLGroup::ID));
//			var_dump($group->getGroups(HACLGroup::NAME));
//			var_dump($group->getGroups(HACLGroup::OBJECT));
			
			$group1->removeUser('Thomas');
			$group1->removeGroup($group2);

//			var_dump($group->getUsers(HACLGroup::ID));
//			var_dump($group->getUsers(HACLGroup::NAME));
//			var_dump($group->getUsers(HACLGroup::OBJECT));
//			
//			var_dump($group->getGroups(HACLGroup::ID));
//			var_dump($group->getGroups(HACLGroup::NAME));
//			var_dump($group->getGroups(HACLGroup::OBJECT));
			
			$group1 = HACLGroup::newFromName("Group1");
		} catch (Exception $e) {
			print($e->getMessage());
		}
	}

}

?>