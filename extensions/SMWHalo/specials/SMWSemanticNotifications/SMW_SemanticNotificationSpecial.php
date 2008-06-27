<?php
/*  Copyright 2008, ontoprise GmbH
 *  This file is part of the halo-Extension.
 *
 *   The halo-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The halo-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
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
class SMWSemanticNotificationSpecial extends SpecialPage {

	public function __construct() {
		parent::__construct('SemanticNotifications');
	}
	
	/**
	 * Overloaded function that is resopnsible for the creation of the Special Page
	 */
	public function execute() {

		global $wgRequest, $wgOut, $wgUser, $wgScript,$smwgHaloScriptPath;

		$wgOut->setPageTitle(wfMsg('smw_sn_special_page'));
		$imagepath = $smwgHaloScriptPath . '/skins/SemanticNotifications/images';
		
		$initialContent = "";
		$snEnabled = 'true';
		// Check, if a user is logged in
		if (!$wgUser->isLoggedIn()) {
			$initialContent = wfMsg('smw_sn_not_logged_in',
									SpecialPage::getTitleFor('Userlogin')->getFullURL());
			$snEnabled = 'false';
		} else {
			// Check, if the user has a valid email address
			$email = $wgUser->isEmailConfirmed();
			if (!$email) {
				$initialContent = wfMsg('smw_sn_no_email',
										SpecialPage::getTitleFor('Preferences')->getFullURL(),
				                        wfMsg('mypreferences'));
				$snEnabled = 'false';
			}
		}
		$queryInterfaceLink = ' specialpage="'.urlencode(SpecialPage::getTitleFor('QueryInterface')->getFullURL()).'"';
		
		$ttAdd = wfMsg('smw_sn_tt_addNotification');
		$ttPreview = wfMsg('smw_sn_tt_showPreview');
		$ttQI = wfMsg('smw_sn_tt_openQueryInterface');
		$txtSn1 = wfMsg('smw_sn_special1');
		$txtSn2 = wfMsg('smw_sn_special2');
		$txtSn3 = wfMsg('smw_sn_special3');
		$txtSn4 = wfMsg('smw_sn_special4');
		$txtSn5 = wfMsg('smw_sn_special5');
		$txtSn6 = wfMsg('smw_sn_special6');
		$txtSn7 = wfMsg('smw_sn_special7');
		$txtSn8 = wfMsg('smw_sn_special8');
		$txtSn9 = wfMsg('smw_sn_special9');
		$txtSn10 = wfMsg('smw_sn_special10');
		
		$disabled = ($snEnabled == 'true') ? "" : 'disabled=""';
		$textArea = ($snEnabled == 'true') 
			? 
<<<HTML
      <textarea class="sn-query-text" id="sn-querytext" snEnabled="$snEnabled"
                style="width:79%; height:130px; position:relative;"></textarea>
HTML
		    :
<<<HTML
      <div class="sn-warning-msg" id="sn-querytext" snEnabled="$snEnabled"
                style="width:79%; height:130px; float:left; ">$initialContent</div>
HTML;

        $minInterval = 7;
       	$limits = SemanticNotificationManager::getUserLimitations($wgUser->getName());
       	if (is_array($limits)) {
       		$minInterval = $limits['min interval'];
       	}
		    
		$html = '';

		$html = wfMsg('smw_sn_explanation');
		$html .= <<<HTML
    <div class="sn-outerdiv" style="position:relative; overflow:hidden; height:600px; top:20px;">
	  <div id="querypreview" style="float:left; width:79%">
	      <div id="sn-querybox" style="float:left; width:100%;">
		      <div id="sn-querydesc" style="width:20%; float:left; overflow:hidden; top:32px; left:16px; ">
			      <div class="sn-labels" id="sn-enter-query-txt" style="width:100%; float:left; overflow:hidden;">
			      	$txtSn1 
			      </div>
				  <button class="btn" id="sn-query-interface-btn" style="float:left;" 
				          $queryInterfaceLink
				          onmouseover="Tip('$ttQI')">
				    $txtSn2
				  </button>
			  </div>
		      $textArea
	      </div>
	      <div id="sn-separator" style="width:100%; height:10px; float:left"></div>
	      <div id="sn-preview" style="float:left; width:100%;">
	      	<div id="sn-preview-linkbox" style="float:left; width:20%;">
			  <button class="btn" id="sn-show-preview-btn" style="float:left;"
			          onmouseover="Tip('$ttPreview')">
	      	     $txtSn3
			  </button>
		    </div>
	        <div class="sn-previewbox" id="sn-previewbox" style="width:79%; height:320px; position:relative;"></div>
	        <div id="sn-footer" style="width:79%; float:right; overflow:hidden;">
			  $txtSn4
	          <div class="sn_labels" id="sn-enter-updateinterval-txt" style="overflow:hidden; float:left;">
			      	$txtSn5&nbsp; 
			    </div>
			    <input $disabled type="text" value="$minInterval" id="sn-update-interval" style=" float:left; width:10%; overflow:hidden;" />
		        <div class="sn_labels" id="sn-enter-updateinterval-days" style="overflow:hidden; float:left;">
			      	 &nbsp;$txtSn6
			    </div>
	      		<div style="width:100%; height:10px; float:left"></div>
			    <div class="sn_labels" id="sn-enter-name-txt" style="overflow:hidden; float:left;">
			      	$txtSn7&nbsp;
			    </div>
			    <input $disabled name="sn-notification-name" type="text" value="$txtSn8" id="sn-notification-name" style=" float:left; width:40%; overflow:hidden;"/>
			    <button class="btn" id="sn-add-notification" style="float:right;"
			            onmouseover="Tip('$ttAdd')">
			    	<img src="$imagepath/add.png"/>&nbsp;
			    	$txtSn9
			    </button>
	        </div>
	  	</div>
      </div>
      <div class="sn-my-notificationbox" id="sn-my-notifications-box" style="width:20%; height:462px; float:right; top:32px; left:544px; ">
        <div id="mynottitle" class="sn-my-notificationstitle" style="width:100%; height:20px; position:relative; overflow:hidden;">
          $txtSn10
        </div>
        <div id="sn-notifications-list" class="sn-my-notifications-list" style="position:relative;">
        </div>
      </div>
    </div>
	<script type="text/javascript" src="$smwgHaloScriptPath/scripts/QueryInterface/qi_tooltip.js"></script>
    
HTML;

		$wgOut->addHTML($html);
	}

}

?>