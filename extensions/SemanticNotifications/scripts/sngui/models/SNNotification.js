/**
 * @file
 * @ingroup SemanticNotifications
 */

/*  Copyright 2011, ontoprise GmbH
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
steal(function($){

/**
 * @class SNGui.Model.Notification
 * This class represents the definition of a notification.
 */
$.Model.extend('SNGui.Model.Notification',
	/* @Static */
	{
  		findAll : function(notification, success, error) {
			var url = wgScriptPath + "/index.php?action=ajax";
			$.ajax({
				type: 'POST',
				url: url,
				success: success,
				dataType: 'json notification.models',
				data:
					{
						"rs" : "snf_sn_GetAllNotifications",
						"rsargs[]": wgUserName
					},
				error: error
			});						
		},

		findOne : function(notification, success, error) {
			var url = wgScriptPath + "/index.php?action=ajax";
			$.ajax({
				type: 'POST',
				url: url,
				success: success,
				dataType: 'json notification.model',
				data:
					{
						"rs" : "snf_sn_GetNotification",
						"rsargs[]": [
							notification.label,
							notification.user
							]
					},
				error: error
			});						
		},
				  
		create: function(notification, success, error){
			var url = wgScriptPath + "/index.php?action=ajax";
			$.ajax({
				type: 'POST',
				url: url,
				success: success,
				data: {"rs" : "snf_sn_AddNotification",
						"rsargs[]":[
							notification.label,
							notification.user,
							notification.queryText,
							notification.updateInterval
						]
					},
				error: error
			});						

		},
				  
		destroy: function(notification, success, error) {
			var url = wgScriptPath + "/index.php?action=ajax";
			$.ajax({
				type: 'POST',
				url: url,
				success: success,
				data: {
						"rs" : "snf_sn_DeleteNotification",
						"rsargs[]": [
							notification.label,
							notification.user
							]
					},
				error: error
			});						
		}		  
	},
	/* @Prototype */
	{
		/**
		 * Constructor function of this class.
		 * This class is normally instantiated with an Ajax call.
		 * The members in this class are:
		 * {string} user: Name of the user who owns the notification
		 * {string} label: Name of the notification
		 * {string} queryText: The content of the query
		 * {int} updateInterval: Update interval in minutes.
		 * [int} minInterval : Minimal allowed update interval for this user
		 * {int} id: ID for identifying instances of this model that are attached
		 * 			 to DOM elements.
		 * 
		 */
		init: function () {
			this.attr('user', this.user || wgUserName);
			this.attr('label', this.label || "");
			this.attr('queryText', this.queryText || "");
			this.attr('minInterval', this.minInterval || 60 * 24);
			this.attr('updateInterval', this.updateInterval || this.minInterval);
			this.attr('id', this.id || null);
		},
		
		/**
		 * Returns the query text of this notification.
		 * @return {string}
		 * 		The query text or an empty string if the query text is not defined.
		 */
		getQueryText: function () {
			return this.queryText || "";
		},
		
		/**
		 * Returns the name of this notification.
		 * @return {string}
		 * 		The name or an empty string if the name is not defined.
		 */
		getLabel: function () {
			return this.label || "";
		},

		/**
		 * Returns the update interval of this notification.
		 * @return {int}
		 * 		The update interval or undefined if it is not defined.
		 */
		getUpdateInterval: function () {
			return this.updateInterval || "";
		},
		
		/**
		 * Returns the query text without ask tags
		 */
		getStrippedQueryText: function() {
			var query = this.getQueryText();
			query = query.replace(/^\s*<ask.*?>\s*(.*?)\s*<\/ask>\s*$/m,"$1");
			
			// strip {{ask#
			var p = query.indexOf('{{#ask:');
			if (p >= 0) {
				query = query.substr(p+7);
				p = query.lastIndexOf('|}}');
				if (p == -1) {
					p = query.lastIndexOf('}}');
				}
				if (p >= 0) {
					query = query.substring(0, p);
				}
			}
			query = query.replace(/\s*(\|\s*[^\?]{2}.*?)[\n|\|]/g,'');
			query = query.replace(/\n/g, ' ');
			return query;
			
		},
		
		/**
		 * Returns the minimal update interval of this notification.
		 */
		getMinInterval : function () {
			return this.minInterval;
		},
		
		/**
		 * Checks if this instance represents a valid notification i.e.
		 * * there must be some query text
		 * * the update interval is not too small
		 */
		isValid: function () {
			return !isNaN(this.updateInterval)
			       && this.updateInterval >= this.minInterval
				   && this.queryText.length > 0
				   && this.label.length > 0;
		}

	}
);

});

