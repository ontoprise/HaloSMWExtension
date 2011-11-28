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
 * @ingroup SemanticNotifications
 * 
 * @author Thomas Schweitzer
 */
steal(function($){

/**
 * @class SNGui.Controllers.PageState
 * 
 * This class controls the state of the UI based on the user data (logged in,
 * email confirmed, etc). Some fields and buttons are disabled if the user data
 * is invalid.
 */
$.Controller.extend('SNGui.Controllers.PageState',
	/* @Static */
	{
		
	},
	/* @Prototype */
	{
		/**
		 * Initializes the controller.
		 * Sets the initial state of the user interface.
		 * 
		 * @param {Object} el
		 * @param {Object} options
		 */
		init: function (el, options) {
			
			// The notification that is currently being edited.
			this.mCurrentNotification = null;
			this.mPreviewOK = false;
			if (this.initUI(el)) {
				// Editing is allowed
				// => create an initial notification for editing
				var qt = this.getQueryFromQueryInterface();
				this.editNotification(new SNGui.Model.Notification({queryText:qt}));
				this.updateUI();
			}
		},
		
		getQueryFromQueryInterface: function () {
			// read a query of the query interface from the cookie
			var query = document.cookie;
			var queryText = "";
			var start = query.indexOf('NOTIFICATION_QUERY=<snq>');
			var end = query.indexOf('</snq>');
			if (start >= 0 && end >= 0) {
				// Query found
				// remove the query from the cookie
				document.cookie = 'NOTIFICATION_QUERY=<snq></snq>;';
				query = query.substring(start+24, end);
				// format the query nicely
				query = query.replace(/\]\]\[\[/g, "]]\n[[");
				query = query.replace(/>\[\[/g, ">\n[[");
				query = query.replace(/\]\]</g, "]]\n<");
				query = query.replace(/([^\|]{1})\|{1}(?!\|)/g, "$1\n|");
				queryText = query;
			}
			return queryText;
		},
		
		/**
		 * Initializes the states of the UI elements. If no user is logged in or
		 * if a user does not have a confirmed email, all UI elements are disabled.
		 * @param {Object} el
		 * @return {bool}
		 * 		Returns true, if the user can add/edit notifications
		 */
		initUI: function (el) {
			var userData = SNGui.Model.UserData.getInstance();
			var enable = userData.isLoggedIn && userData.isEmailConfirmed;

			if (enable) {
				// Enable all input fields and buttons
				$(':input').removeAttr('disabled');
			} else {
				// Disable all input fields and buttons
				$(':input').attr('disabled', 'disabled');
			}
			this.enableButtons('#sn-query-interface-btn', enable);
			this.enableButtons('#sn-show-preview-btn', enable);
			this.enableButtons('#sn-add-notification', enable);
			
			return enable;
		},
		
		/**
		 * Updates the UI based on the state of the current nofication.
		 */
		updateUI: function () {
			var notification = this.mCurrentNotification;
			// Show the values of the notification in the UI
			var qt = notification.getQueryText();
			$('#sn-querytext').val(qt);
			
			var name = notification.getLabel();
			var hasName = true;
			if (name.empty()) {
				// No name given => show the hint to enter a name
				if (!this.mPreviewOK) {
					hasName = false;
					var lang = SNGui.Model.Language.getInstance();
					name = lang['sn_special8'];
				}
			}
			
			// Show the name of the notification
			$('#sn-notification-name').val(name);
			
			// Show the update interval
			$('#sn-update-interval').val(notification.getUpdateInterval());
			
			// Clear the preview box
			if (!this.mPreviewOK) {
				$('#sn-previewbox').html("");
			}
			
			// Enable or disable the Preview button
			this.enableButtons('#sn-show-preview-btn', qt.length > 0);
			
			// Enable or disable the input field for the name of the notification
			if (this.mPreviewOK) {
				$('#sn-notification-name').removeAttr('disabled');
			} else {
				$('#sn-notification-name').attr('disabled', 'disabled');
			}
			
			// If the definition of the notification did not change with respect
			// to its version in the server's DB, the "Add notification" button 
			// is disabled.
			this.enableButtons('#sn-add-notification', 
			                   notification.isDirty() 
							   && notification.isValid()
							   && this.mPreviewOK);
			
		},
		
		/**
		 * This function is called when a notification instance was loaded for
		 * editing.
		 * It prepares the UI for editing the notification.
		 * 
		 * @param {SNNotification} notification
		 */
		notificationLoaded: function (notification) {
			$('#sn-notifications-list').throbber(false);
			this.editNotification(notification);
			this.updateUI();
		},
		
		/**
		 * This function prepares the given notification for editing in the UI.
		 * The notification will become the "current" notification and backup is
		 * created.
		 * 
		 * @param {Object} notification
		 * 		This notification will be edited.
		 */
		editNotification: function (notification) {
			this.mPreviewOK = false;
			this.mCurrentNotification = notification;
			this.mCurrentNotification.backup();
			
		},
		
		".btn mouseover" : function(element, event) {
			if (!element.hasClass('btndisabled')) {
				element.addClass('btnhov');
			}
		},
		".btn mouseout" : function(element, event) {
			if (!element.hasClass('btndisabled')) {
				element.removeClass('btnhov');
			}
		},
		
		/**
		 * Event callback for clicking the button "Query Interface".
		 * It opens the Query Interface special page.
		 * @param {Object} element
		 * @param {Object} event
		 */
		"#sn-query-interface-btn click" : function (element, event) {
			var qiPage = element.attr('specialpage');
			qiPage = unescape(qiPage);
			location.href = qiPage;
		},
		
		/**
		 * Event callback for clicking the button "Show preview".
		 * It shows the preview of the query result in the preview area.
		 * @param {Object} element
		 * @param {Object} event
		 */
		"#sn-show-preview-btn click" : function (element, event) {
			if (element.hasClass('btndisabled')) {
				// Button is disabled
				return;
			}

			$("#sn-previewbox").throbber();
			var query = this.mCurrentNotification.getStrippedQueryText();
			query += ",format=table|limit=50|order=ascending|merge=false";
			
			var self = this;
			var url = wgServer + wgScriptPath + 
			          "/index.php?action=ajax" +
					  "&rs=smwf_qi_QIAccess" +
					  "&rsargs[]=getQueryResult" +
					  "&rsargs[]="+query;
			$.ajax({
				url: url,
				type: 'get',
				dataType: 'html',
				success: function (result) {
					$('#sn-previewbox').html(result);
					element.throbber(false);
					self.mPreviewOK = true;
					self.updateUI();
				},
				error: function () {
					element.throbber(false);
					self.mPreviewOK = false;
					alert("ajax error");
				}
			});
		},
		
		/**
		 * This event handler is called when the edit button for a notification
		 * in the list of notifications is clicked. 
		 * The selected element will be loaded for editing in the UI.
		 * 
		 * @param {Object} element
		 * @param {Object} event
		 */
		".sn-edit-notification click" : function (element, event) {
			element.parent().throbber();
			
			var notification = element.closest('.notification').model();
			var request = {
				user  : notification.user, 
				label : notification.label
			};
			// Get the full specification of the notification
			SNGui.Model.Notification.findOne(request, this.callback('notificationLoaded'));
		},
		
		/**
		 * This event handler is called when the "Add notification" button is clicked. 
		 * The notification that is currently being edited will be saved.
		 * 
		 * @param {Object} element
		 * @param {Object} event
		 */
		"#sn-add-notification click" : function (element, event) {
			if (element.hasClass('btndisabled')) {
				// Button is disabled
				return;
			}
			
			// Does a notification with the same name already exist?
			var name = this.mCurrentNotification.getLabel();
			if ($("#sn-notifications-list")
					.controller()
					.existsNotification(name)) {
				var lang = SNGui.Model.Language.getInstance();
				var msg = lang['sn_overwrite_existing'];
				msg = msg.replace(/\$1/g, name);
				
				if (!confirm(msg)) {
					return;
				}
			}
			
			element.throbber();

			var self = this;
			this.mCurrentNotification.Class.create(this.mCurrentNotification,
				// Success function
				function (responseText, textStatus, jqXHR) {
					element.throbber(false);
					if (responseText !== "true") {
						// The notification could not be added
						alert(responseText);
					} else {
						// Success => Start editing a new notification
						self.mCurrentNotification.publish("created");
						self.updateUI();
					}
				},
				// Error function
				function (data, textStatus, jqXHR) {
					element.throbber(false);
					alert("Ajax request failed.");
				}
			);
		},
		
		/**
		 * This event handler is called when the query text is changed.
		 * @param {Object} element
		 * @param {Object} event
		 */
		"#sn-querytext keyup" : function (element, event) {
			var qt = $(element).val();
			this.mCurrentNotification.attr('queryText', qt);
			this.mCurrentNotification.publish("currentNotificationChanged");
		},
		
		/**
		 * This event handler is called when the update interval is changed.
		 * @param {Object} element
		 * @param {Object} event
		 */
		"#sn-update-interval blur" : function (element, event) {
			var interval = parseInt($(element).val());
			var minInterval = this.mCurrentNotification.getMinInterval();
			// Is the update interval valid?
			if (isNaN(interval) || interval < minInterval) {
				var lang = SNGui.Model.Language.getInstance();
				var msg = lang['sn_invalid_update_interval'];
				msg = msg.replace(/\$1/g, minInterval);
				alert(msg);
				interval = minInterval;
				// Show the update interval
				$('#sn-update-interval').val(interval.toString());
			}
			
			this.mCurrentNotification.attr('updateInterval', interval);
			this.mCurrentNotification.publish("currentNotificationChanged");
			
		},
		
		/**
		 * This handler is called when the name of the notification is changed.
		 * @param {Object} element
		 * @param {Object} event
		 */
		"#sn-notification-name keyup" : function (element, event) {
			var name = element.val();
			this.mCurrentNotification.attr('label', name);
			this.mCurrentNotification.publish("currentNotificationChanged");
		},
		
		"notification.currentNotificationChanged subscribe" : function(called, notification) {
			this.updateUI();
  		},
		
		/**
		 * Enables or disables the buttons in the UI.
		 * @param {string} selector
		 * 		A jQuery selector string for the buttons to en-/disable
		 * @param {bool} enable
		 * 		true: enable
		 * 		false: disable
		 */
		enableButtons : function (selector, enable) {
			if (enable) {
				$(selector).removeClass('btndisabled');
			} else {
				$(selector).addClass('btndisabled');
			}
		}
		
	}
);

});

