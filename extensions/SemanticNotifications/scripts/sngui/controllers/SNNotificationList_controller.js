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
 * @class SNGui.Controllers.NotificationList
 * 
 * This class controls the content of the notification list.
 * Note: The events for editing and deleting notifications are handled by
 * the SNPageState controller.
 */
$.Controller.extend('SNGui.Controllers.NotificationList',
	/* @Static */
	{
		
	},
	/* @Prototype */
	{
		/**
		 * Initializes the controller.
		 * It retrieves the list of all notifications of the current user.
		 * 
		 * @param {Object} el
		 * @param {Object} options
		 */
		init: function (el, options) {
			this.getNotifications(el);
		},
		
		/**
		 * Retrieves all notifications of the current user.
		 * @param {Object} el
		 */
		getNotifications: function (el) {
			SNGui.Model.Notification.findAll({}, this.callback('showNotifications'));
		},
		
		/**
		 * Checks if a notification with the given name exists.
		 * 
		 * @param {string} name
		 * 		Name of the notification that is searched.
		 * @return {bool}
		 * 		true, if there is a notification with the given name
		 * 		false, otherwise
		 */
		existsNotification: function (name) {
			var found = false;
			$(".notification").each(function (){
				var $this = $(this);
				var notification = $this.model();
				if (notification.getLabel() === name) {
					found = true;
				}
			});
			return found;
		},
		
		/**
		 * Shows the list of notifications of the current user.
		 * @param {Array} notifications 
		 * 		An array of notification objects
		 */
		showNotifications: function(notifications) {
			$('#sn-notifications-list')
				.html(this.view('//sngui/views/snmain/SNNotifications.ejs',
				                { notifications: notifications } ));
		},
		
		/**
		 * This event handler is called when a notification instance is modified.
		 * If a notification is added, updated or removed, the list of notifications
		 * will be updated.
		 * @param {Object} reason
		 * @param {Object} notification
		 */
		"notification.* subscribe" : function(reason, notification) {
			if (reason === "notification.created" 
			    || reason === "notification.updated"
				|| reason === "notification.destroyed") {
				this.getNotifications(this.element);			
			}
  		},
		
		".sn-delete-notification click" : function (element, event) {
			var notification = element.closest('.notification').model();
			var name = notification.getLabel();
			
			// Does the user really want to delete the notification?
			var lang = SNGui.Model.Language.getInstance();
			var msg = lang['sn_delete'];
			msg = msg.replace(/\$1/g, name);
			
			if (!confirm(msg)) {
				return;
			}
			
			element.parent().throbber();
			SNGui.Model.Notification.destroy(notification, function (){
					element.throbber(false);
					notification.publish("destroyed");
				},
				function (){
					element.throbber(false);
				});
 		
		}
	});
});

