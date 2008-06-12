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
	},

	showToolbar: function() {
/*
 		this.gardeningHintContainer.setHeadline(gLanguage.getMessage('ANNOTATION_HINTS'));
		var container = this;
		var hintsLoaded = false;
		container.gardeningHintContainer.showContainerEvent = function() {
			if (hintsLoaded) return;
			if (!container.gardeningHintContainer.isVisible()) return;
			sajax_do_call('smwf_ga_GetGardeningIssues', 
	                  [['smw_consistencybot', 'smw_undefinedentitiesbot', 'smw_missingannotationsbot'], '', '', wgPageName, ''], 
	                  container.createContent.bind(container));
	        hintsLoaded = true;
		}
		this.gardeningHintContainer.setVisibility(false);
		this.gardeningHintContainer.contentChanged();
*/ 
	},
	
	addNotification: function() {
		window.console.log('Button clicked');
	}

}

SemanticNotifications.create = function() {
	smwhgSemanticNotifications = new SemanticNotifications();
	var addNotification = $('sn-add-notification');
	Event.observe(addNotification, 'click', 
			      smwhgSemanticNotifications.addNotification.bindAsEventListener(smwhgSemanticNotifications));
	
}

var smwhgSemanticNotifications = null;
Event.observe(window, 'load', SemanticNotifications.create);
