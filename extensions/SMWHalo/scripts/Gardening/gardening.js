/*
 * gardening.js
 * 
 * Author: KK
 * Ontoprise 2007
 * 
 * Gardening Special page script
 */
var SMW_AJAX_GARDLOG = 2;

var GardeningPage = Class.create();
GardeningPage.prototype = {
	initialize: function() {
		this.currentSelectedBot = null;
		Event.observe(window, 'load', function() {this.currentSelectedBot = $('gardening-tools').firstChild; });
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
	Element.getElementsByClassName(gardeningParamForm, "errorText").each(function(e) { e.innerHTML = ''; });
	
	function callBackOnRunBot(request) {
		
		var splitText = request.responseText.split(":");
		
		// check for errors
		if (splitText.length == 3 && splitText[0].indexOf('ERROR') != -1) {
			// ERROR response: [0] == ERROR, [1] == ID of DOM element, [2] == message
			// check if the id denotes an parameter error (element starting with errorOf_)
			var errorSpan = $("errorOf_"+splitText[1]);
			if (errorSpan == null) {
				// if not it is a general error.
				errorSpan = $(splitText[1]);
			}
			// paste error message and highlight it.
			errorSpan.innerHTML = "\t" + splitText[2];
			//Effect.Pulsate(errorSpan);
			return;
		}
		$('gardening-runningbots').innerHTML = request.responseText;
	}
	sajax_do_call('smwfLaunchGardeningBot', [this.currentSelectedBot.getAttribute('id'), params], callBackOnRunBot);
  },
  
  cancel: function(event, taskid) {
  	function callBackOnCancelBot(request) {
  		$('gardening-runningbots').innerHTML = request.responseText;
  	}
  	
  	if (wgUserGroups.indexOf("sysop") != -1 || wgUserGroups.indexOf("gardener") != -1) {
  		sajax_do_call('smwfCancelGardeningBot', [taskid], callBackOnCancelBot);
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
		sajax_do_call('smwfGetBotParameters', [botID], this.showParamsCallback.bind(this));
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
			sajax_do_call('smwfGetGardeningLog', [], gardeningLogElement, SMW_AJAX_GARDLOG);
		}
	}

} 

var gardeningPage = new GardeningPage();
