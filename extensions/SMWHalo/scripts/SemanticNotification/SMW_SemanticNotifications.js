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
		$('sn-notification-name').disable();
		this.enable('sn-add-notification', false);
		this.notifications = [];	// The names of all existing notifications
		this.queryLen = 0;
		this.queryEdited = false;
		this.minInterval = 1000;
		this.initialName = $('sn-notification-name').value;
		this.previewOK = false;
	},

	/**
	 * Key-up and blur callback for the query text area. If the query text has 
	 * been changed the input field for the name of the notification and the 'Add' 
	 * button are disabled.
	 */	
	queryChanged: function(event) {
		var key = event.which || event.keyCode;
		
		var len = $('sn-querytext').value.length;
		if (len != this.queryLen) {
			$('sn-notification-name').disable();
			this.enable('sn-add-notification', false);
			$('sn-querytext').focus();
			this.queryEdited = true;
			this.queryLen = len;
		}
		
	},
	
	/**
	 * Key-up and blur callback for the query text area. If the query text has 
	 * been changed the input field for the name of the notification and the 'Add' 
	 * button are disabled.
	 */	
	nameChanged: function(event) {
		var key = event.which || event.keyCode;
		var name = $('sn-notification-name').value.replace(/^\s*(.*?)\s*$/,"$1");
		
		if (name.length == 0) {
			// no name given
			this.enable('sn-add-notification', false);
			$('sn-notification-name').focus();
		} else {
			this.enable('sn-add-notification', this.previewOK);
		}
		
	},
	
	
	/**
	 * The user has changed the update interval. Check if the value is valid.
	 */
	updateIntervalChanged: function(event) {
		var val = $('sn-update-interval').value;
		val = parseInt(val); 
		if (isNaN(val) || val < this.minInterval) {
			var msg = gLanguage.getMessage('SMW_SN_INVALID_UPDATE_INTERVAL');
			msg = msg.replace(/\$1/g, this.minInterval);
			alert(msg);
			val = this.minInterval;
		}
		$('sn-update-interval').value = val;
	},
	
	/**
	 * Adds the query to the semantic notifications of the current user.
	 */
	addNotification: function() {
		
		function ajaxResponseAddNotification(request) {
			this.hidePendingIndicator();			
			if (request.status == 200) {
				// success
				if (request.responseText.indexOf("true") >= 0) {
					this.getAllNotifications();
					// disable button and name input
					$('sn-notification-name').disable();
					this.enable('sn-add-notification', false);

					// Clear input box for query text
					$('sn-querytext').value = '';
					$('sn-notification-name').value = '';
					$('sn-previewbox').innerHTML = '';
					
					this.queryEdited = false;
				} else {
					alert(request.responseText);
				}
			} else {
			}
		};
		
	 	var e = $('sn-add-notification');
	 	var cls = e.className;
	 	if (cls.indexOf('btndisabled') >= 0) {
	 		// Button is disabled
	 		return;
	 	}

		var name =  $('sn-notification-name').value.replace(/^\s*(.*?)\s*$/,"$1");
		
		// does the name already exist?
		if (this.notifications.indexOf(name) >= 0) {
			var msg = gLanguage.getMessage('SN_OVERWRITE_EXISTING');
			msg = msg.replace(/\$1/g, name);
			
			if (!confirm(msg)) {
				return;
			}
		}
		var query = $('sn-querytext').value;
		this.showPendingIndicator(e);
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
				var pos = request.responseText.indexOf(',');
				success = request.responseText.substring(0, pos);
				var res = request.responseText.substr(pos+1);
				$('sn-previewbox').innerHTML = res;
				if (success.indexOf('true')>= 0) {
					this.previewOK = true;
					$('sn-notification-name').enable();
					$('sn-notification-name').focus();
					if ($('sn-notification-name').value == this.initialName) {
						$('sn-notification-name').value = '';
						this.enable('sn-add-notification', false);
					} else {
						this.enable('sn-add-notification', true);
					}
				}
			} else {
				$('sn-notification-name').disable();
				this.enable('sn-add-notification', false);
				this.previewOK = false;
			}
		};

	 	var e = $('sn-show-preview-btn');
	 	var cls = e.className;
	 	if (cls.indexOf('btndisabled') >= 0) {
	 		// Button is disabled
	 		return;
	 	}

		this.showPendingIndicator(e);
		var query = $('sn-querytext').value;
		query = this.stripQuery(query);
		sajax_do_call('smwf_sn_ShowPreview', 
                      [query], 
                      ajaxResponseShowPreview.bind(this));
		
	},
	
	/**
	 * Opens the query interface in another tab
	 */
	openQueryInterface: function(element) {
		var qiPage = element.target.readAttribute('specialpage');
		qiPage = unescape(qiPage);
		location.href = qiPage;
//		window.open(qiPage, '_blank');
	},
	
	/**
	 * Retrieves a list of all notifications of the current users and displays
	 * them in the box 'My Notifications'. 
	 */
	getAllNotifications: function() {
		function ajaxResponseGetAllNotifications(request) {
			this.notifications.clear();
			if (request.status == 200) {
				var notifications = request.responseText.split(",");
				var html = '<table class="sn-my-notifications-table">';
				html += '<colgroup>'
						+ '<col width="80%" span="1">'
						+ '<col width="10%" span="2">'
						+ '</colgroup>';
				for (var i = 0; i < notifications.length; ++i) {
					// trim
  					n = notifications[i].replace(/^\s*(.*?)\s*$/,"$1");
  					if (n == '') { 
  						continue;
  					}
  					this.notifications.push(n);
  					html += "<tr><td>"+n+"</td>";
  					html += '<td><a href="javascript:smwhgSemanticNotifications.editNotification(\''+n+'\')">';
  					html += '<img src="'+wgScriptPath+'/extensions/SMWHalo/skins/edit.gif" /></a></td>'; 
  					html += '<td><a href="javascript:smwhgSemanticNotifications.deleteNotification(\''+n+'\')">';
  					html += '<img src="'+wgScriptPath+'/extensions/SMWHalo/skins/delete.png" /></a></td>'; 
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
	 * @param string notification
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
				$('sn-previewbox').innerHTML = '';
				$('sn-querytext').focus();
				this.queryLen = query.length;
				this.queryChanged = false;
			} else {
			}
		};

		if (this.queryEdited) {
			if (!confirm('The current query has been edited but not saved. Do you really want to edit another notification?')) {
				return;
			}
		}

		this.showPendingIndicator($('sn-notifications-list'));
		sajax_do_call('smwf_sn_GetNotification', 
                      [notification, wgUserName], 
                      ajaxResponseEditNotification.bind(this));
		
	},

	/**
	 * Deletes the given notification in the wiki's database.
	 * 
	 * @param string notification
	 * 		Name of the notification
	 */
	deleteNotification: function(notification) {
		function ajaxResponseDeleteNotification(request) {
			this.hidePendingIndicator();			
			if (request.status == 200) {
				if (request.responseText.indexOf("true") >= 0) {
					this.getAllNotifications();
				}
			} else {
			}
		};
		
		var msg = gLanguage.getMessage('SN_DELETE');
		msg = msg.replace(/\$1/g, notification);
		
		if (!confirm(msg)) {
			return;
		}
		

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
	},

	/**
	 * 
	 */
	 enable: function(element, enable) {
	 	var e = $(element);
	 	var cls = e.className;
	 	var start = cls.indexOf('btndisabled');
	 	if (enable) {
	 		if (start >= 0) {
	 			e.className = cls.substring(0, start) + cls.substring(start+11);
	 		}
	 	} else {
	 		if (start == -1) {
	 			e.className = cls + " btndisabled";
	 		}
	 	} 
	 },
	 
	 /**
	  * Sets the css-class 'btnhov' for the button under the mouse cursor.
	  */
	 btnMouseOver: function(element) {
	 	var e = element.target;
	 	var cls = e.className;
	 	var start = cls.indexOf('btnhov');
 		if (start == -1) {
 			e.className = cls + " btnhov";
 		}
	 },

	 /**
	  * Removes the css-class 'btnhov' from the button that the mouse cursor
	  * just left.
	  */
	 btnMouseOut: function(element) {
	 	var e = element.target;
	 	var cls = e.className;
	 	var start = cls.indexOf('btnhov');
 		if (start >= 0) {
 			e.className = cls.substring(0, start) + cls.substring(start+6);
 		}
	 },
	
	/**
	 * Removes the ask tags from a query
	 */
	stripQuery: function(query) {
		query = query.replace(/^\s*<ask.*?>\s*(.*?)\s*<\/ask>\s*$/m,"$1");
		
		// strip {{ask#
		var p = query.indexOf('{{#ask:');
		if (p >= 0) {
			query = query.substr(p+7);
			p = query.indexOf('|');
			if (p >= 0) {
				query = query.substring(0, p);
			} else {
				p = query.lastIndexOf('}}');
				if (p >= 0) {
					query = query.substring(0, p);
				}
			}
		}
		
		return query;
		
	}

}

SemanticNotifications.create = function() {
	// Check, if semantic notifications are enabled (user logged in with valid 
	// email address). If not, the complete UI is disabled.
	var qt = $('sn-querytext');
	var enabled = (qt != null) && qt.readAttribute('snenabled');
	if (enabled == 'true') {
		// enable the user interface
		smwhgSemanticNotifications = new SemanticNotifications();
		smwhgSemanticNotifications.minInterval = $('sn-update-interval').value;
		
		var addNotification = $('sn-add-notification');
		Event.observe(addNotification, 'click', 
				      smwhgSemanticNotifications.addNotification.bindAsEventListener(smwhgSemanticNotifications));
		Event.observe(addNotification, 'mouseover', 
				      smwhgSemanticNotifications.btnMouseOver.bindAsEventListener(smwhgSemanticNotifications));
		Event.observe(addNotification, 'mouseout', 
				      smwhgSemanticNotifications.btnMouseOut.bindAsEventListener(smwhgSemanticNotifications));

		var showPreview = $('sn-show-preview-btn');
		Event.observe(showPreview, 'click', 
				      smwhgSemanticNotifications.showPreview.bindAsEventListener(smwhgSemanticNotifications));
		Event.observe(showPreview, 'mouseover', 
				      smwhgSemanticNotifications.btnMouseOver.bindAsEventListener(smwhgSemanticNotifications));
		Event.observe(showPreview, 'mouseout', 
				      smwhgSemanticNotifications.btnMouseOut.bindAsEventListener(smwhgSemanticNotifications));

		var queryInterface = $('sn-query-interface-btn');
		Event.observe(queryInterface, 'click', 
				      smwhgSemanticNotifications.openQueryInterface.bindAsEventListener(smwhgSemanticNotifications));
		Event.observe(queryInterface, 'mouseover', 
				      smwhgSemanticNotifications.btnMouseOver.bindAsEventListener(smwhgSemanticNotifications));
		Event.observe(queryInterface, 'mouseout', 
				      smwhgSemanticNotifications.btnMouseOut.bindAsEventListener(smwhgSemanticNotifications));

		Event.observe('sn-querytext', 'keyup', 
		              smwhgSemanticNotifications.queryChanged.bindAsEventListener(smwhgSemanticNotifications));
		Event.observe('sn-querytext', 'blur', 
		              smwhgSemanticNotifications.queryChanged.bindAsEventListener(smwhgSemanticNotifications));

		Event.observe('sn-notification-name', 'keyup', 
		              smwhgSemanticNotifications.nameChanged.bindAsEventListener(smwhgSemanticNotifications));
		Event.observe('sn-notification-name', 'blur', 
		              smwhgSemanticNotifications.nameChanged.bindAsEventListener(smwhgSemanticNotifications));

		Event.observe('sn-update-interval', 'blur', 
		              smwhgSemanticNotifications.updateIntervalChanged.bindAsEventListener(smwhgSemanticNotifications));
	
		// read a query of the query interface from the cookie
		var query = document.cookie;
		var start = query.indexOf('NOTIFICATION_QUERY=<snq>');
		var end = query.indexOf('</snq>');
		if (start >= 0 && end >= 0) {
			// Query found
			// remove the query from the cookie
			document.cookie = 'NOTIFICATION_QUERY=<snq></snq>;';
			query = query.substring(start+24, end);
			qt.value = query;
			
			this.queryEdited = true;
			this.queryLen = query.length;
		}
		
		smwhgSemanticNotifications.getAllNotifications();
		$('sn-querytext').focus();
	}	
}

var smwhgSemanticNotifications = null;
Event.observe(window, 'load', SemanticNotifications.create);
