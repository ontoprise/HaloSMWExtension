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
*/

/**
 * @file
 * @ingroup SMWHaloSemanticToolbar
 * @author Thomas Schweitzer
 */

var REFRESH_DELAY = 0.5; // Refresh delay is 500 ms
var RefreshSemanticToolBar = Class.create();

RefreshSemanticToolBar.prototype = {

	//Constructor
	initialize: function() {
		this.userIsTyping = false;
		this.lastKeypress = 0;	// Timestamp of last keypress event
		this.timeOffset = 0;
		this.contentChanged = false;
		this.wtp = null;
		this.containsForbiddenProperties = false;

	},

	//Registers event
	initToolbox: function(event){
		if( (wgAction == "edit" || wgAction == 'formedit' || wgAction == 'submit' ||
                     wgCanonicalSpecialPageName == 'AddData' || wgCanonicalSpecialPageName == 'EditData' ||
                     wgCanonicalSpecialPageName == 'FormEdit' )
		   && typeof stb_control != 'undefined' && stb_control.isToolbarAvailable()){
            var txtarea = $('wpTextbox1') || $('free_text');
            Event.observe(txtarea, 'change' ,this.changed.bind(this));
            Event.observe(txtarea, 'keyup' ,this.setUserIsTyping.bind(this));
			this.registerTimer();
			this.editboxtext = "";

		}
	},

	changed: function() {
		this.contentChanged = true;
	},

	//Checks if user is typing, content has changed and refreshes the toolbar
	refresh: function(){
		if (this.userIsTyping){
			this.contentChanged = true;
			this.userIsTyping = false;
		} else if (this.contentChanged) {
			var t = new Date().getTime() - this.timeOffset;
			var dt = (this.lastKeypress != 0)
						? t - this.lastKeypress
						: 0;
			if (dt > REFRESH_DELAY*1000) {
				// Get the element that is currently focussed as focus may change
				// during refresh of toolbar
				var currentFocus = document.activeElement;
				this.contentChanged = false;
				this.refreshToolBar();
				if (currentFocus) {
					// Restore the focus
					currentFocus.focus();
				}
			}
		}
	},

	//registers automatic refresh
	registerTimer: function(){
		this.periodicalTimer = new PeriodicalExecuter(this.refresh.bind(this), REFRESH_DELAY);
	},

	setUserIsTyping: function(event){
		if (typeof(event) == "undefined"  || !event.timeStamp) {
			this.lastKeypress = new Date().getTime();
		} else {
			this.lastKeypress = event.timeStamp;
		}
		if (this.timeOffset == 0) {
			this.timeOffset = new Date().getTime() - this.lastKeypress;
		}
		this.userIsTyping = true;
	},

	//Refresh the Toolbar
	refreshToolBar: function() {
		if(window.catToolBar){
			catToolBar.fillList()
		}
		if(window.relToolBar){
			relToolBar.fillList()
		}
		if(window.ruleToolBar){
			ruleToolBar.fillList()
		}

		if(window.propToolBar){
			propToolBar.createContent();
		}

		if(window.smwhgASKQuery){
			smwhgASKQuery.fillList();
		}

		// Check for syntax errors in the wiki text
		var saveButton = $('wpSave');
		if (saveButton) {
			// Check if the wikitext contains forbidden properties
			if (this.containsForbiddenProperties &&
			    !$('wpForbiddenProperties')) {
				new Insertion.Before(saveButton,
					'<div id="wpForbiddenProperties" ' +
					  'style="background-color:#ee0000;' +
							 'color:white;' +
							 'font-weight:bold;' +
							 'text-align:left;">' +
							 gLanguage.getMessage('CANT_SAVE_FORBIDDEN_PROPERTIES')+'</div>');
			} else if (!this.containsForbiddenProperties &&
			           $('wpForbiddenProperties')){
				$('wpForbiddenProperties').remove();
			}
			
			
			// Check if the wikitext contains syntax errors
			if (!this.wtp) {
				this.wtp = new WikiTextParser();
			}
			this.wtp.initialize();
			this.wtp.parseAnnotations();
			var error = this.wtp.getError();
			if (error == WTP_NO_ERROR) {
				if ($('wpSaveWarning')) {
					$('wpSaveWarning').remove();
				}
			} else {
				if (!$('wpSaveWarning')){
					new Insertion.Before(saveButton,
						'<div id="wpSaveWarning" ' +
						  'style="background-color:#ee0000;' +
								 'color:white;' +
								 'font-weight:bold;' +
								 'text-align:left;">' +
								 gLanguage.getMessage('UNMATCHED_BRACKETS')+'</div>');
				}
			}
			
			if (this.containsForbiddenProperties || error != WTP_NO_ERROR) {
				saveButton.disable();
			} else {
				saveButton.enable();
			}				
			
			
			if (!gEditInterface) {
				gEditInterface = new SMWEditInterface();
			}
			gEditInterface.focus();
		}

	}
}

window.refreshSTB = new RefreshSemanticToolBar();
stb_control.registerToolbox(refreshSTB);
