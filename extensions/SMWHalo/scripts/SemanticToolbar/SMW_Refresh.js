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

	},

	//Registers event
	register: function(event){
		if(wgAction == "edit"
		   && stb_control.isToolbarAvailable()){
			Event.observe('wpTextbox1', 'change' ,this.changed.bind(this));
			Event.observe('wpTextbox1', 'keyup' ,this.setUserIsTyping.bind(this));
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
				this.contentChanged = false;
				this.refreshToolBar();
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

		if(window.propToolBar){
			propToolBar.createContent();
		}

		// Check for syntax errors in the wiki text
		var saveButton = $('wpSave');
		if (saveButton) {
			if (!this.wtp) {
				this.wtp = new WikiTextParser();
			}
			this.wtp.initialize();
			this.wtp.parseAnnotations();
			var error = this.wtp.getError();
			if (error == WTP_NO_ERROR) {
				saveButton.enable();
				if ($('wpSaveWarning')) {
					$('wpSaveWarning').remove();
				}
			} else {
				if (!$('wpSaveWarning')){
					saveButton.disable();
					new Insertion.Before(saveButton,
						'<div id="wpSaveWarning" ' +
						  'style="background-color:#ee0000;' +
								 'color:white;' +
								 'font-weight:bold;' +
								 'text-align:left;">' +
								 gLanguage.getMessage('UNMATCHED_BRACKETS')+'</div>');
				}
			}
			if (gEditInterface == null) {
				gEditInterface = new SMWEditInterface();
			}
			gEditInterface.focus();
		}

	}
}

var refreshSTB = new RefreshSemanticToolBar();
Event.observe(window, 'load', refreshSTB.register.bindAsEventListener(refreshSTB));