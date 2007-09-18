var RefreshSemanticToolBar = Class.create();

RefreshSemanticToolBar.prototype = {
	
	//Constructor
	initialize: function() {
		this.userIsTyping = false;
		this.contentChanged = false;
		this.wtp = null;
		
	},
	
	//Registers event 
	register: function(event){
		if(wgAction == "edit"
		   && stb_control.isToolbarAvailable()){
			Event.observe('wpTextbox1', 'change' ,this.changed.bind(this));
			Event.observe('wpTextbox1', 'keypress' ,this.setUserIsTyping.bind(this));
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
			this.contentChanged = false;
			this.refreshToolBar();
		}
	},

	//registers automatic refresh
	registerTimer: function(){
		this.periodicalTimer = new PeriodicalExecuter(this.refresh.bind(this), 3);		
	},
	
	//deregisters automatic refresh
	deregisterTimer: function(){
		this.periodicalTime ? this.periodicalTimer.stop() : "";
	},
	
	setUserIsTyping: function(){
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
		}
		
	}
}

var refreshSTB = new RefreshSemanticToolBar();
Event.observe(window, 'load', refreshSTB.register.bindAsEventListener(refreshSTB));