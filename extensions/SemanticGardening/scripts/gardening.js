/*  Copyright 2007, ontoprise GmbH
*   Author: Kai Kühn
*   This file is part of the halo-Extension.
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

var SMW_AJAX_GARDLOG = 2;

var GardeningPage = Class.create();
GardeningPage.prototype = {
	initialize: function() {
		this.currentSelectedBot = null;
		if (wgCanonicalSpecialPageName != 'Gardening') return;
		Event.observe(window, 'load', function() {
			this.currentSelectedBot = $('gardening-tools').firstChild; 
		});
		// refresh Gardening log table every 40 seconds.
		new PeriodicalExecuter(this.getGardeningLog.bind(this), 40);
		this.pendeningIndicator = null;
	},

  /**
   * Called when a bot is run.
   */	
  run: function(e) {
	
	var gardeningParamForm = $("gardeningParamForm");
	var params = Form.serialize(gardeningParamForm);
	params = params.replace(/&/g, ","); // replace & by , because command interpreter (cmd.exe) does not like & as parameter
	// clear errorTexts
	$$('span.errorText').each(function(e) { e.innerHTML = ''; });
	
	
	function callBackOnRunBot(request) {
		
		var splitText = request.responseText.split(":");
		
		// check for errors
		if (splitText.length == 3 && splitText[0].indexOf('ERROR') != -1) {
			// ERROR response: [0] == ERROR, [1] == ID of DOM element, [2] == message
			// check if the id denotes an parameter error (element starting with errorOf_)
			var errorSpan = $("errorOf_"+splitText[1]);
			if (errorSpan == null) {
				// if not it is a general error.
				errorSpan = $('gardening-tooldetails-content');
			}
			// paste error message and highlight it.
			errorSpan.innerHTML = "\t" + splitText[2];
			//Effect.Pulsate(errorSpan);
			var runButton = $('runBotButton');
			if (runButton != null) runButton.removeAttribute("disabled");
			return;
		}
		$('gardening-tooldetails-content').innerHTML = gLanguage.getMessage('BOT_WAS_STARTED');
		$('gardening-runningbots').innerHTML = request.responseText;
	}
	sajax_do_call('smwf_ga_LaunchGardeningBot', [this.currentSelectedBot.getAttribute('id'), params, null, null], callBackOnRunBot);
	
	// disable button to prevent continuous executing
	$('runBotButton').setAttribute("disabled","disabled");
  },
  
  cancel: function(event, taskid) {
  	function callBackOnCancelBot(request) {
  		$('gardening-runningbots').innerHTML = request.responseText;
  	}
  	
  	if (wgUserGroups.indexOf("sysop") != -1 || wgUserGroups.indexOf("gardener") != -1) {
  		sajax_do_call('smwf_ga_CancelGardeningBot', [taskid, null, null], callBackOnCancelBot);
  	} else {
  		alert(gLanguage.getMessage('INVALID_GARDENING_ACCESS'));
  	}
  	
  },


	/**
	 * Requests parameters for the given bot and paste them as HTML in the
	 * gardening-tooldetails-content.
	 */
 	showParams: function(e, node, botID) {
		
		if (this.currentSelectedBot) {
			Element.removeClassName(this.currentSelectedBot,'entry-active');
			Element.addClassName(this.currentSelectedBot,'entry');
		}
		Element.removeClassName(node,'entry'); //.removeClassName('entry');
		Element.addClassName(node, 'entry-active');
		this.currentSelectedBot = node;
		if (this.pendingIndicator == null) {
			this.pendingIndicator = new OBPendingIndicator($('gardening-tooldetails-content'));
		}
		this.pendingIndicator.show();
		sajax_do_call('smwf_ga_GetBotParameters', [botID], this.showParamsCallback.bind(this));
	},
	
	showParamsCallback: function(request) {
		this.pendingIndicator.hide();
		autoCompleter.deregisterAllInputs();
		$('gardening-tooldetails-content').innerHTML = request.responseText;
		autoCompleter.registerAllInputs();
	},
	
	/**
	 * Formats the selected bot entry correctly when mouseout
	 */
 	showRightClass: function(e, node, botID) {
		
		if (this.currentSelectedBot!=node) {
			Element.removeClassName(node,'entry-over');
			Element.addClassName(node,'entry');
		}else{
			Element.removeClassName(node,'entry-over');
			Element.addClassName(node,'entry-active');
		}
	},

	/**
	 * Requests gardening log as HTML and pastes it in the log element
	 */
	getGardeningLog: function() {
		var gardeningLogElement = $('gardening-runningbots');
		if (gardeningLogElement) {
			ajaxRequestManager.stopCalls(SMW_AJAX_GARDLOG);
			sajax_do_call('smwf_ga_GetGardeningLog', [], gardeningLogElement, SMW_AJAX_GARDLOG);
		}
	}

} 

var gardeningPage = new GardeningPage();


// Gardening Log special page

var GardeningLogPage = Class.create();
GardeningLogPage.prototype = {
	
	initialize: function() {
		if (wgCanonicalSpecialPageName != 'GardeningLog') return;
		this.pendingIndicator = new OBPendingIndicator();
		this.showAll = false;
	},
	
	selectBot: function(event) {
		var selectTag = Event.element(event);
		this.pendingIndicator.show($('issueClasses'));
		var selectedIndex = selectTag.selectedIndex;
		var bot_id = selectTag.options[selectedIndex].value;
		sajax_do_call('smwf_ga_GetGardeningIssueClasses', [bot_id], this.changeIssueClassesContent.bind(this));
	},
	
	changeIssueClassesContent: function(request) {
		var selectElement = $('issueClasses');
		this.pendingIndicator.hide();
		if (selectElement != null) selectElement.replace(request.responseText);
	},
	
	toggle: function(id) {
		var div = $(id);
		if (div.visible()) div.hide(); else div.show();
	},
	
	toggleAll: function() {
		this.showAll = !this.showAll;
		var showAll = this.showAll;
		var divs = $$('.gardeningLogPageBox');
		divs.each(function(d) { if (showAll) d.show(); else d.hide(); });
		$('showall').innerHTML = showAll ? gLanguage.getMessage('GARDENING_LOG_COLLAPSE_ALL') : gLanguage.getMessage('GARDENING_LOG_EXPAND_ALL'); 
	}
}

var gardeningLogPage = new GardeningLogPage();
