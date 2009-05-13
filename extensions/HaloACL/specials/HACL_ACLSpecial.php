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
	// used for identify the chosen tab
	// the four main tabs
	const CREATE_ACL = 1;
	const MANAGE_ACLS = 2;
	const MANAGE_USER_AND_GROUPS = 3;
	const MANAGE_WHITELISTS = 4;
	// sub tabs
	const CREATE_STANDARD_ACL = 1;
	const CREATE_ACL_TEMPLATE = 2;
	const CREATE_ACL_DEFAULT_USER_TEMPLATE = 3;
	
	const MANAGE_ALL_ACLS = 1;
	const MANAGE_OWN_DEFAULT_USER_TEMPLATE = 2;
	
	public function __construct() {
		parent::__construct('HaloACL');
	}
	
	/**
	 * Overloaded function that is responsible for the creation of the Special Page
	 */
	public function execute() {

		global $wgOut, $wgRequest, $wgLang;
		
		wfLoadExtensionMessages('HaloACL');
		$wgOut->setPageTitle(wfMsg('hacl_special_page'));
		$this->createTabs();
	}
	
	private function createTabs(){
		global $wgOut;	
		//TODO: determine the choosen tab/subtab, perhaps with parameters like ...?tab=1&subtab=3 get them with wgRequest and set the visibility
		$html = <<<HTML
<div class=haclContainer>
	<div id="haclTabs">
		<ul>
			<li id="haclTabHeader1"><a href onclick="haloACLSpecialPage.toggleTab(1,4);return false;" href="#"><span>Create ACL</span></a></li>
			<li id="haclTabHeader2"><a href onclick="haloACLSpecialPage.toggleTab(2,4);return false;" href="#"><span>Manage ACLs</span></a></li>
			<li id="haclTabHeader3"><a href onclick="haloACLSpecialPage.toggleTab(3,4);return false;" href="#"><span>Manage User</span></a></li>
			<li id="haclTabHeader4"><a href onclick="haloACLSpecialPage.toggleTab(4,4);return false;" href="#"><span>Manage Whitelists</span></a></li>
		</ul>
	</div><!--End of haclTabs-->
	<div id="haclTabsContent">
		<div id="haclTabContent1" class="haclTabContent">
			<div id="haclSubTabsbla">
				<ul>
					<li id="haclSubTabHeader1"><a href onclick="haloACLSpecialPage.toggleSubTab(1,3);return false;" href="#"><span>Create standard ACL</span></a></li>
					<li id="haclSubTabHeader2"><a href onclick="haloACLSpecialPage.toggleSubTab(2,3);return false;" href="#"><span>Create ACL template</span></a></li>
					<li id="haclSubTabHeader3"><a href onclick="haloACLSpecialPage.toggleSubTab(3,3);return false;" href="#"><span>Create ACL default user template</span></a></li>
				</ul>
			</div><!--End of haclSubTabs -->
			{$this->createDefaultUserTemplateHTML()}
		</div><!-- End Of haclTabsContent1 -->
		
		<div id="haclTabContent2" class="haclTabContent" style="display:none;">
			<div class="subTabs">bla</div>
			Second Tab Content goes here<br/>
		</div><!-- End Of haclTabsContent2 -->

		<div id="haclTabContent3" class="haclTabContent" style="display:none;">
			Third Tab Content goes here<br/>
		</div><!-- End Of haclTabsContent3 -->

		<div id="haclTabContent4" class="haclTabContent" style="display:none;">
			{$this->createWhitelistHTML()}
		</div><!-- End Of haclTabsContent4 -->
	</div><!--End of haclTabContent-->
</div><!--End of haclContainer-->
HTML;

		$wgOut->addHTML($html);
	}
	/**
	 * Creates HTML for the 'Create standard ACL' page
	 *
	 * @return string
	 */
	private function createStandardACLHTML() {
		//TODO: check this again! Do we need different language calls for 'standard acl' and 'DUT'?
		$createStACLHeadline = wfMsg('hacl_create_acl_dut_headline');
		$createStACLInfo = wfMsg('hacl_create_acl_dut_info');
		$createStACLGeneralHeader = wfMsg('hacl_create_acl_dut_general');
		$createStACLGeneralDefine = wfMsg('hacl_create_acl_dut_general_definefor');
		$createStACLGeneralDefinePrivate = wfMsg('hacl_create_acl_dut_general_private_use');
		$createStACLGeneralDefineAll = wfMsg('hacl_create_acl_dut_general_all');
		$createStACLGeneralDefineSpecific = wfMsg('hacl_create_acl_dut_general_specific');
		$createStACLRightsHeader = wfMsg('hacl_create_acl_dut_rights');

		$createStACLRightsButtonCreate = wfMsg('hacl_create_acl_dut_button_create_right');
		$createStACLRightsButtonAddTemplate = wfMsg('hacl_create_acl_dut_button_add_template');
		$createStACLRightsLegend = wfMsg('hacl_create_acl_dut_new_right_legend');
		$createStACLRightsLegendSaved = wfMsg('hacl_create_acl_dut_new_right_legend_status_saved');
		//TODO: this needs to be variable!
		//$createStACLRightsLegendNotSaved = wfMsg('hacl_create_acl_dut_new_right_legend_status_notsaved');
		$createStACLRightsName = wfMsg('hacl_create_acl_dut_new_right_name');
		$createStACLRightsDefaultName = wfMsg('hacl_create_acl_dut_new_right_defaultname');
		$createStACLRights = wfMsg('hacl_create_acl_dut_new_right_rights');
		$createStACLRightsFullAccess = wfMsg('hacl_create_acl_dut_new_right_fullaccess');
		$createStACLRightsRead = wfMsg('hacl_create_acl_dut_new_right_read');
		$createStACLRightsEWF = wfMsg('hacl_create_acl_dut_new_right_ewf');
		$createStACLRightsEdit = wfMsg('hacl_create_acl_dut_new_right_edit');
		$createStACLRightsCreate = wfMsg('hacl_create_acl_dut_new_right_create');
		$createStACLRightsMove = wfMsg('hacl_create_acl_dut_new_right_move');
		$createStACLRightsDelete = wfMsg('hacl_create_acl_dut_new_right_delete');
		$createStACLRightsAnnotate = wfMsg('hacl_create_acl_dut_new_right_annotate');
		
		$html = <<<HTML
<div id=haclCreateStACLContainer>
	<p class="haclHeadline">{$createStACLHeadline}</p>
	<p class="haclInfo">{$createStACLInfo}</p>
	<div id="haclCreateStACLGeneralContainer">
		<div id="haclCreateStACLGeneralHeader">{$createStACLGeneralHeader}</div>
		<table>
			<tr>
				<td>{$createStACLGeneralDefine}</td>
				<td><input type="checkbox" checked="checked">{$createStACLGeneralDefinePrivate}</input></td>
				<td><input type="checkbox" checked="checked">{$createStACLGeneralDefineAll}</input></td>
				<td><input type="checkbox" checked="checked">{$createStACLGeneralDefineSpecific}</input></td>
			</tr>
		</table>
	</div><!-- End of haclCreateStACLGeneralContainer -->
	<div id="haclCreateStACLRightsContainer">
		<div id="haclCreateDutRightsHeader">{$createStACLRightsHeader}</div>
		<input class="haclCreateRightButton" type="button" value="{$createStACLRightsButtonCreate}" />
		<input class="haclCreateRightButton" type="button" value="{$createStACLRightsButtonAddTemplate}" />
		<div class="haclCreateDutRight">
			<fieldset>
				<legend class="haclCreateDutLegend"><span class="haclCreateDutRightLegend">{$createStACLRightsLegend}</span>
					<span class="haclCreateDutLegendControl">{$createStACLRightsLegendSaved}+ICON</span>
				</legend>
				<table>
				<tr>
					<td>{$createStACLRightsName}</td><td><input type="text" size="30" value="{$createStACLRightsDefaultName}"></input></td>
				</tr>
				<tr>
					<td>{$createStACLRights}</td>
					<td>
						<table>
							<tr>
								<td><input type="checkbox" name="full_access" value="full_access">{$createStACLRightsFullAccess}</input></td>
								<td><input type="checkbox" name="read" value="full_access">{$createStACLRightsRead}</input></td>
								<td><input type="checkbox" name="edit_with_forms" value="full_access">{$createStACLRightsEWF}</input></td>
								<td><input type="checkbox" name="edit" value="full_access">{$createStACLRightsEdit}</input></td
							</tr>
							<tr>
								<td><input type="checkbox" name="create" value="full_access">{$createStACLRightsCreate}</input></td>
								<td><input type="checkbox" name="move" value="full_access">{$createStACLRightsMove}</input></td>
								<td><input type="checkbox" name="delete" value="full_access">{$createStACLRightsDelete}</input></td>
								<td><input type="checkbox" name="annotate" value="full_access">{$createStACLRightsAnnotate}</input></td>
							</tr>
						</table>
					</td>
				</tr>
				</table>
				<div class="drawLine">&nbsp;</div>
				<div class="haclCreateDutUserContainer">
					<div class="haclCreateDutUserTabs">
						<ul>
							<li>1</li>
							<li>2</li>
						</ul>
					</div><!-- End Of haclCreateDutUserTabs -->
				</div><!-- End Of haclCreateDutUserContainer -->
			</fieldset>
		</div><!-- End Of haclDutRight -->
	</div><!-- End of haclCreateDutRightsContainer -->
	<div class="drawLine">&nbsp;</div>
</div><!-- End of haclCreateDutContainer -->
HTML;
		
		return $html;
	}
	
	/**
	 * Creates HTML for the 'Create ACL default user template' page
	 *
	 */
	private function createDefaultUserTemplateHTML() {
		$dutHeadline = wfMsg('hacl_create_acl_dut_headline');
		$dutInfo = wfMsg('hacl_create_acl_dut_info');
		$dutGeneralHeader = wfMsg('hacl_create_acl_dut_general');
		$dutGeneralDefine = wfMsg('hacl_create_acl_dut_general_definefor');
		$dutGeneralDefinePrivate = wfMsg('hacl_create_acl_dut_general_private_use');
		$dutGeneralDefineAll = wfMsg('hacl_create_acl_dut_general_all');
		$dutGeneralDefineSpecific = wfMsg('hacl_create_acl_dut_general_specific');
		$dutRightsHeader = wfMsg('hacl_create_acl_dut_rights');

		$dutRightsButtonCreate = wfMsg('hacl_create_acl_dut_button_create_right');
		$dutRightsButtonAddTemplate = wfMsg('hacl_create_acl_dut_button_add_template');
		$dutRightsLegend = wfMsg('hacl_create_acl_dut_new_right_legend');
		$dutRightsLegendSaved = wfMsg('hacl_create_acl_dut_new_right_legend_status_saved');
		//$dutRightsLegendNotSaved = wfMsg('hacl_create_acl_dut_new_right_legend_status_notsaved');
		$dutRightsName = wfMsg('hacl_create_acl_dut_new_right_name');
		$dutRightsDefaultName = wfMsg('hacl_create_acl_dut_new_right_defaultname');
		$dutRights = wfMsg('hacl_create_acl_dut_new_right_rights');
		$dutRightsFullAccess = wfMsg('hacl_create_acl_dut_new_right_fullaccess');
		$dutRightsRead = wfMsg('hacl_create_acl_dut_new_right_read');
		$dutRightsEWF = wfMsg('hacl_create_acl_dut_new_right_ewf');
		$dutRightsEdit = wfMsg('hacl_create_acl_dut_new_right_edit');
		$dutRightsCreate = wfMsg('hacl_create_acl_dut_new_right_create');
		$dutRightsMove = wfMsg('hacl_create_acl_dut_new_right_move');
		$dutRightsDelete = wfMsg('hacl_create_acl_dut_new_right_delete');
		$dutRightsAnnotate = wfMsg('hacl_create_acl_dut_new_right_annotate');

		
		$html = <<<HTML
<div id=haclCreateDutContainer>
	<p class="haclHeadline">{$dutHeadline}</p>
	<p class="haclInfo">{$dutInfo}</p>
	<div id="haclCreateDutGeneralContainer">
		<div class="haclHeader">{$dutGeneralHeader}</div>
		<table>
			<tr>
				<td>{$dutGeneralDefine}</td>
				<td><input type="checkbox" checked="checked">{$dutGeneralDefinePrivate}</input></td>
				<td><input type="checkbox" checked="checked">{$dutGeneralDefineAll}</input></td>
				<td><input type="checkbox" checked="checked">{$dutGeneralDefineSpecific}</input></td>
			</tr>
		</table>
	</div><!-- End of haclCreateDutGeneralContainer -->
	<div id="haclCreateDutRightsContainer">
		<div class="haclHeader">{$dutRightsHeader}</div>
		<input class="haclCreateRightButton" type="button" value="{$dutRightsButtonCreate}" />
		<input class="haclCreateRightButton" type="button" value="{$dutRightsButtonAddTemplate}" />
		<div class="haclCreateDutRight">
			<fieldset>
				<legend class="haclCreateDutLegend"><span class="haclCreateDutRightLegend">{$dutRightsLegend}</span>
					<span class="haclCreateDutLegendControl">{$dutRightsLegendSaved}+ICON</span>
				</legend>
				<table>
				<tr>
					<td>{$dutRightsName}</td><td><input type="text" size="30" value="{$dutRightsDefaultName}"></input></td>
				</tr>
				<tr>
					<td>{$dutRights}</td>
					<td>
						<table>
							<tr>
								<td><input type="checkbox" name="full_access" value="full_access">{$dutRightsFullAccess}</input></td>
								<td><input type="checkbox" name="read" value="full_access">{$dutRightsRead}</input></td>
								<td><input type="checkbox" name="edit_with_forms" value="full_access">{$dutRightsEWF}</input></td>
								<td><input type="checkbox" name="edit" value="full_access">{$dutRightsEdit}</input></td
							</tr>
							<tr>
								<td><input type="checkbox" name="create" value="full_access">{$dutRightsCreate}</input></td>
								<td><input type="checkbox" name="move" value="full_access">{$dutRightsMove}</input></td>
								<td><input type="checkbox" name="delete" value="full_access">{$dutRightsDelete}</input></td>
								<td><input type="checkbox" name="annotate" value="full_access">{$dutRightsAnnotate}</input></td>
							</tr>
						</table>
					</td>
				</tr>
				</table>
				<div class="drawLine">&nbsp;</div>
				<div class="haclCreateDutUserContainer">
					<div class="haclCreateDutUserTabs">
						<ul>
							<li>1</li>
							<li>2</li>
						</ul>
					</div><!-- End Of haclCreateDutUserTabs -->
				</div><!-- End Of haclCreateDutUserContainer -->
			</fieldset>
		</div><!-- End Of haclDutRight -->
	</div><!-- End of haclCreateDutRightsContainer -->
	<div class="drawLine">&nbsp;</div>
</div><!-- End of haclCreateDutContainer -->
HTML;
		return $html;
	}
	
	/**
	 * Creates HTML for Whitelist page
	 *
	 * @return string 
	 */
	private function createWhitelistHTML() {
		$wLHeadline = wfMsg('hacl_whitelist_headline');
		$wLInfo = wfMsg('hacl_whitelist_info');
		$wLFilter = wfMsg('hacl_whitelist_filter');
		$wLPageSetHeader = wfMsg('hacl_whitelist_pageset_header');
		$wLPageName = wfMsg('hacl_whitelist_pagename');
		$wlAddButton = wfMsg('hacl_whitelist_addbutton');
		$html = <<<HTML
<div id="haclWhitelistContainer">
	<p id="haclWhitelistHeadline">{$wLHeadline}</p>
	<p id="haclWhitelistInfo">{$wLInfo}</p>
	<div id="haclWhitelistFilter">
		<span class="marginToRight">{$wLFilter}</span><input type="text" length="20">
	</div>
	<div id="haclWhitelistPageSetContainer">
		<div id="haclWhitelistPageSetHeader">{$wLPageSetHeader}</div>
		<div id="haclWhitelistPageSet">
			<ul>
				<li><a href onclick="haloACLSpecialPage.toggleTab(1,4);return false;" href="#"><span>Mainpage</span></a></li>
			</ul>
		</div><!-- End of haclWhitelistPageSet -->
	</div><!-- End of haclWhitelistPageSetContainer -->
	<!-- <div class="drawLine">&nbsp;</div> -->
	<div id="haclWhitelistAddPageContainer">
		<span id="haclWhitelistPageName">{$wLPageName}</span>
		<input id="haclWhitelistPageInput" type="text" size="30"><input type="button" value="{$wlAddButton}">
	</div>
</div><!-- End of haclWhitelistContainer -->
HTML;
		return $html;
	}


	/**
	 * Determines if the content for the current tab should be visible or not
	 * 
	 * @param int $tabIndex
	 * 		HaloACLSpecial::CREATE_ACL
	 *
	 * @return boolean
	 * 		visible or not
	 */
	private function enableTabContent($tabIndex, $subTabIndex = 0) {
		$html = '';
		switch ($tabIndex) {
			case self::CREATE_ACL:
				switch ($subTabIndex) {
					case self::CREATE_STANDARD_ACL:
						break;
					case self::CREATE_ACL_TEMPLATE:
						break;
					case self::CREATE_ACL_DEFAULT_USER_TEMPLATE:
						$html = $this->createDefaultUserTemplateHTML();
						break;
					default: 
						break;	
				}
				//break;
			case self::MANAGE_ACLS:
				switch ($subTabIndex) {
					case self::MANAGE_ALL_ACLS:
						break;
					default:
						break;
				}
				break;
			case self::MANAGE_USER_AND_GROUPS:
				switch ( $subTabIndex ) {
					case 0:
						break;
					default:
						break;
				}
				break;
			case self::MANAGE_WHITELISTS:
				$html = $this->createWhitelistHTML();
				break;
			default:
				//error!
				break;
		}

		return $html;
	}
	private function testPage(){
		global $wgOut, $wgRequest, $wgLang;
		
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
		global $haclgIP;
		require_once "$haclgIP/tests/testcases/TestDatabase.php";
		$tc = new TestDatabase();
		$tc->runTest();
	}

}

//AJAX calls
//TODO: put them in seperate file???
function blabla(){
	
	return 'okay';
}

?>