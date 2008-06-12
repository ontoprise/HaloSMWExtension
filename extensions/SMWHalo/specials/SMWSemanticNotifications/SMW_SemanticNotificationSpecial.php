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

		global $wgRequest, $wgOut;

		$wgOut->setPageTitle(wfMsg('smw_sn_special_page'));

		$html = '';

//---Test start		
		$html = wfMsg('smw_sn_explanation');
		$html .= <<<HTML
    <div class="Panel1_nm" id="Panel1" style="position:relative; overflow:hidden; height:580px; top:20px;">
	  <div id="querypreview" style="float:left; width:79%">
	      <div id="sn-querybox" style="float:left; width:100%;">
		      <div id="sn-querydesc" style="width:20%; float:left; overflow:hidden; top:32px; left:16px; ">
			      <div class="sn-labels" id="sn-enter-query-txt" style="width:100%; float:left; overflow:hidden;">
			      	Enter query (you can use either ask or SPAQRL) or use the 
			      </div>
			      <div class="sn-button-link" id="sn-query-interface-link" style="float:left;">
			      	QueryInterface
			      </div>
			  </div>
		      <textarea name="sn-querytext" class="sn-query-text" id="sn-querytext" style="width:79%; height:130px; position:relative;"></textarea>
	      </div>
	      <div id="sn-separator" style="width:100%; height:10px; float:left"></div>
	      <div id="sn-previewbox" style="float:left; width:100%;">
	      	<div id="sn-preview-linkbox" style="float:left; width:20%;">
		      <div class="sn-button-link" id="sn-show-preview-link" style="float:left;">
		      	Show Preview
		      </div>
		    </div>
	        <div class="previewbox_nm" id="sn-div-previewbox" style="width:79%; height:320px; position:relative; overflow:hidden;"></div>
	        <div id="sn-footer" style="width:79%; float:right; overflow:hidden;">
		        Notifications for your query will be gathered over a certain time span before the notification mail is sent. Please enter how
		        often you would like to receive this notification. If there were no changes, no notification will be sent.
			    <div class="sn_labels" id="sn-enter-name-txt" style="overflow:hidden; float:left;">
			      	Enter a name for your notification:
			    </div>
			    <input name="sn-notification-name" type="text" value="Please check the preview first." id="sn-notification-name" style=" float:left; width:40%; overflow:hidden; top:472px; left:296px; "/>
			    <input type="button" value="Add notification" id="sn-add-notification" style="float:right;"/>
	        </div>
	  	</div>
      </div>
      <div class="notificationbox_nm" id="my_not_outer" style="width:20%; height:462px; float:right; overflow:hidden; top:32px; left:544px; ">
        <div id="mynottitle" style="width:100%; height:32px; position:relative; overflow:hidden; top:-1px; left:-1px; ">
          <div class="mynotificationstitle_nm" id="my_not_title" style="width:100%; position:relative; overflow:hidden; top:0px; left:0px; ">My notifications</div>
        </div>
        <div id="my_not_inner" style="width:100%; height:416px; position:relative; overflow:hidden; top:39px; left:7px; ">
          <div id="Label3" style="width:100%; position:relative; overflow:hidden; top:8px; left:8px; ">Label3</div>
        </div>
      </div>
    </div>
HTML;
		
//---Test end		
		
		$wgOut->addHTML($html);
	}

}

?>