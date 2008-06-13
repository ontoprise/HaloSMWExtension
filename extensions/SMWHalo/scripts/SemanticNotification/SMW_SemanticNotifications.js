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
* 
* @author Thomas Schweitzer
*/

/**
 * @class SemanticNotifications
 * This class handles the events on Special:SemanticNotifications 
 * 
 */
var SemanticNotifications = Class.create();

SemanticNotifications.prototype = {

	initialize: function() {
		this.pendingIndicator = null;
	},
	
	/**
	 * Adds the query to the semantic notifications of the current user.
	 */
	addNotification: function() {
		
		function ajaxResponseAddNotification(request) {
			this.hidePendingIndicator();			
			if (request.status == 200) {
				// success
				if (request.responseText == 'true') {
					this.getAllNotifications();
				} else {
					alert(request.responseText);
				}
			} else {
			}
		};

		this.showPendingIndicator($('sn-add-notification'));
		var query = $('sn-querytext').value;
		var name =  $('sn-notification-name').value;
		var ui = $('sn-update-interval').value;
		sajax_do_call('smwf_sn_AddNotification', 
                      [name, wgUserName, query, ui], 
                      ajaxResponseAddNotification.bind(this));
		
	},

	/**
	 * Shows a preview of the result set for the current query.
	 */
	showPreview: function() {

		function ajaxResponseShowPreview(request) {
			this.hidePendingIndicator();			
			if (request.status == 200) {
				$('sn-previewbox').innerHTML = request.responseText;
			} else {
			}
		};

		this.showPendingIndicator($('sn-show-preview-link'));
		var query = $('sn-querytext').value;
		sajax_do_call('smwf_sn_ShowPreview', 
                      [query], 
                      ajaxResponseShowPreview.bind(this));
		
	},
	
	/**
	 * Retrieves a list of all notifications of the current users and displays
	 * them in the box 'My Notifications'. 
	 */
	getAllNotifications: function() {
		function ajaxResponseGetAllNotifications(request) {
			if (request.status == 200) {
				var notifications = request.responseText.split(",");
				var html = '<table class="sn-my-notifications-table">';
				html += '<colgroup>'
						+ '<col width="80%" span="1">'
						+ '<col width="10%" span="2">'
						+ '</colgroup>';	
				for (var i = 0; i < notifications.length; ++i) {
					// trim
  					$n = notifications[i].replace(/^\s*(.*?)\s*$/,"$1");
  					html += "<tr><td>"+$n+"</td>";
  					html += '<td><a href="javascript:smwhgSemanticNotifications.editNotification(\''+$n+'\')">';
  					html += '<img src="/develwiki/extensions/SMWHalo/skins/edit.gif" /></a></td>'; 
  					html += '<td><a href="javascript:smwhgSemanticNotifications.deleteNotification(\''+$n+'\')">';
  					html += '<img src="/develwiki/extensions/SMWHalo/skins/delete.png" /></a></td>'; 
					html += "</tr>";
				}
				html += "</table>";
				
				$('sn-notifications-list').innerHTML = html;
			} else {
			}
		};
		sajax_do_call('smwf_sn_GetAllNotifications', 
                      [wgUserName], 
                      ajaxResponseGetAllNotifications.bind(this));
		
	},
	
	/**
	 * Retrieves the definition of the given notification and displays the 
	 * values for editing.
	 * 
	 * @param string $notification
	 * 		Name of the notification
	 */
	editNotification: function(notification) {

		function ajaxResponseEditNotification(request) {
			this.hidePendingIndicator();			
			if (request.status == 200) {
				var notification = GeneralXMLTools.createDocumentFromString(request.responseText);
		
				var name  = notification.documentElement.getElementsByTagName("name")[0].firstChild.data;
				var query = notification.documentElement.getElementsByTagName("query")[0].firstChild.data;
				var ui    = notification.documentElement.getElementsByTagName("updateInterval")[0].firstChild.data;
				$('sn-notification-name').value = name;
				$('sn-querytext').value = query;
				$('sn-update-interval').value = ui;
			} else {
			}
		};

		this.showPendingIndicator($('sn-notifications-list'));
		sajax_do_call('smwf_sn_GetNotification', 
                      [notification, wgUserName], 
                      ajaxResponseEditNotification.bind(this));
		
	},

	/**
	 * Deletes the given notification in the wiki's database.
	 * 
	 * @param string $notification
	 * 		Name of the notification
	 */
	deleteNotification: function(notification) {
		function ajaxResponseDeleteNotification(request) {
			this.hidePendingIndicator();			
			if (request.status == 200) {
				if (request.responseText == "true") {
					this.getAllNotifications();
				}
			} else {
			}
		};

		this.showPendingIndicator($('sn-notifications-list'));
		sajax_do_call('smwf_sn_DeleteNotification', 
                      [notification, wgUserName], 
                      ajaxResponseDeleteNotification.bind(this));
		
	},
	
	/*
	 * Shows the pending indicator on the element with the DOM-ID <onElement>
	 * 
	 * @param string onElement
	 * 			DOM-ID if the element over which the indicator appears
	 */
	showPendingIndicator: function(onElement) {
		this.hidePendingIndicator();
		this.pendingIndicator = new OBPendingIndicator($(onElement));
		this.pendingIndicator.show();
	},

	/*
	 * Hides the pending indicator.
	 */
	hidePendingIndicator: function() {
		if (this.pendingIndicator != null) {
			this.pendingIndicator.hide();
			this.pendingIndicator = null;
		}
	}

}

SemanticNotifications.create = function() {
	smwhgSemanticNotifications = new SemanticNotifications();
	var addNotification = $('sn-add-notification');
	Event.observe(addNotification, 'click', 
			      smwhgSemanticNotifications.addNotification.bindAsEventListener(smwhgSemanticNotifications));
	var showPreview = $('sn-show-preview-link');
	Event.observe(showPreview, 'click', 
			      smwhgSemanticNotifications.showPreview.bindAsEventListener(smwhgSemanticNotifications));

	smwhgSemanticNotifications.getAllNotifications();		      
	
}

var smwhgSemanticNotifications = null;
Event.observe(window, 'load', SemanticNotifications.create);
