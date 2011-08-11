/*  Copyright 2007, ontoprise GmbH
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
*/

/**
 * @file
 * @ingroup SMWHaloAAM
 * 
 * @author Thomas Schweitzer
 * 
 * @class GardeningHints
 * This class provides a container for gardening hints in semantic toolbar 
 * (in the Advanced Annotation and Edit Mode).
 * 
 */
var GardeningHints = Class.create();

GardeningHints.prototype = {

initialize: function() {
	this.toolbarContainer = null;
},

showToolbar: function() {
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
},

createContainer: function(event){
	if ((wgAction == "edit" || wgAction == "annotate" || wgAction == "formedit" || wgAction == "submit" ||
             wgCanonicalSpecialPageName == 'AddData' || wgCanonicalSpecialPageName == 'EditData' ||
             wgCanonicalSpecialPageName == 'FormEdit')
	     && typeof stb_control != 'undefined' && stb_control.isToolbarAvailable()){
		this.gardeningHintContainer = stb_control.createDivContainer(ANNOTATIONHINTCONTAINER,0);
		this.showToolbar();
	}
},

createContent: function(request) {
	
	var tb = this.createToolbar("");
	var html = '';
	if (request.status == 200 
	   && request.responseText != "smwf_ga_GetGardeningIssues: invalid title specified.") {
		var hints = GeneralXMLTools.createDocumentFromString(request.responseText);
		if (hints.documentElement) {
			for (var b = 0, bn = hints.documentElement.childNodes.length; b < bn; b++) {
				// iterate over bots
				var bot = hints.documentElement.childNodes[b];
							
				var n = bot.childNodes.length;
				if (n > 0) {						
	//				html += '<b>' + bot.getAttribute("title") + '</b>';
					html += '<ul>';
					for (var i = 0; i < n; i++) {
						// iterate over the bot's issues
						var issue = bot.childNodes[i];
						html += '<li>' + (issue.textContent?issue.textContent:issue.text) + '</li>';
					}
					html += '</ul>';
				}
			}
		}
	}	
	if (!html) {
		// no hints found
		html = tb.createText('ah-status-msg', gLanguage.getMessage('AH_NO_HINTS'), '', true); 
	}
	tb.append(html);

	tb.finishCreation();
	
	this.gardeningHintContainer.contentChanged();
},

/**
 * Creates a new toolbar for the gardening hints container.
 * Further elements can be added to the toolbar. Call <finishCreation> after the
 * last element has been added.
 * 
 * @param string attributes
 * 		Attributes for the new container
 * @return 
 * 		A new toolbar container
 */
createToolbar: function(attributes) {
	if (this.toolbarContainer) {
		this.toolbarContainer.release();
	}
	
	this.toolbarContainer = new ContainerToolBar('annotationhint-content',1000,this.gardeningHintContainer);
	var tb = this.toolbarContainer;
	tb.createContainerBody(attributes);
	return tb;
}

};// End of Class

var smwhgGardeningHints = new GardeningHints();
if (typeof FCKeditor == 'undefined')
    Event.observe(window, 'load', smwhgGardeningHints.createContainer.bindAsEventListener(smwhgGardeningHints));
