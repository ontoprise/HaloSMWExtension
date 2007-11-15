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

var SMW_OB_COMMAND_ADDSUBCATEGORY = 1;
var SMW_OB_COMMAND_ADDSUBCATEGORY_SAMELEVEL = 2;
var SMW_OB_COMMAND_ADDSUBCATEGORY_RENAME = 3;

var SMW_OB_COMMAND_ADDSUBPROPERTY = 4;
var SMW_OB_COMMAND_ADDSUBPROPERTY_SAMELEVEL = 5;
var SMW_OB_COMMAND_SUBPROPERTY_RENAME = 6;

var OB_SELECTIONLISTENER = 'selectionChanged';
var OB_REFRESHLISTENER = 'refresh';

var OBEventProvider = Class.create();
OBEventProvider.prototype = {
	initialize: function() {
		this.listeners = new Array();
	},
	
	addListener: function(listener, type) {
		if (this.listeners[type] == null) {
			this.listeners[type] = new Array();
		} 
		if (typeof(listener[type] == 'function')) { 
			this.listeners[type].push(listener);
		}
	},
	
	removeListener: function(listener, type) {
		if (this.listeners[type] == null) return;
		this.listeners[type] = this.listeners[type].without(listener);
	},
	
	fireSelectionChanged: function(id, title, ns, node) {
		this.listeners[OB_SELECTIONLISTENER].each(function (l) { 
			l.selectionChanged(id, title, ns, node);
		});
	},
	
	fireRefresh: function() {
		this.listeners[OB_REFRESHLISTENER].each(function (l) { 
			l.refresh();
		});
	}
}	

// create instance of event provider
var selectionProvider = new OBEventProvider();	

var OBOntologyTools = Class.create();
OBOntologyTools.prototype = { 
	initialize: function() {
		
	},
	
	addSubcategory: function(subCategoryTitle, superCategoryNode) {
		alert(subCategoryTitle+":"+superCategoryNode);
	}
}

var OBOntologyGUITools = Class.create();
OBOntologyGUITools.prototype = { 
	initialize: function(id, objectname) {
		this.OBOntologyGUITools(id, objectname);
	},
	
	OBOntologyGUITools: function(id, objectname) {
		this.id = id;
		this.objectname = objectname;
		
		this.istyping = false;
		this.timerdisabled = true;
		
		this.commandID = null;
		this.selectedTitle = null;
		
		this.keyListener = null;
	},
	/**
	 * @public
	 * 
	 * Shows subview.
	 * @param commandID command to execute.
	 * @param node selected node.
	 */
	showTitleInput: function(commandID, selectedTitle) {
		this.commandID = commandID;
		this.selectedTitle = selectedTitle;
		$(this.id).replace('<div id="'+this.id+'">' +
						'<input style="display:block; width:60%; float:left" id="'+this.id+'_input_ontologytools" type="text"/>' +
						'<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">'+gLanguage.getMessage('OB_ENTER_TITLE')+'</span> | ' +
						'<a onclick="'+this.objectname+'.cancel()">'+gLanguage.getMessage('CANCEL')+'</a>' +
					  '</div>');
		this.registerListeners();	
		this.setUserDefinedControls();
		$(this.id+'_input_ontologytools').focus();	
		selectionProvider.addListener(this, OB_SELECTIONLISTENER);
				
	},
	
	selectionChanged: function(id, title, ns, node) {
		if (ns == SMW_CATEGORY_NS) {
			this.selectedTitle = title;
		}
	},
	/**
	 * @public
	 * 
	 * Close subview
	 */
	cancel: function() {
		var e = $(this.id+'_input_ontologytools');
		Event.stopObserving(e, "keyup", this.keyListener);
		Event.stopObserving(e, "keydown", this.keyListener);
		selectionProvider.removeListener(this, OB_SELECTIONLISTENER);
		$(this.id).replace('<div id="'+this.id+'">');
	},
	
	/**
	 * @abstract 
	 * @public
	 */
	doCommand: function() {
		alert('DO NOT CALL doCommand ! This abstract method need an implementation!');
	},
	
	/**
	 * @abstract
	 * @public 
	 */
	getCommandText: function() {
		alert('DO NOT CALL getCommandText ! This abstract method need an implementation!');
	},
	
	/**
	 * @private
	 */
	registerListeners: function() {
		var e = $(this.id+'_input_ontologytools');
		this.keyListener = this.onKey.bindAsEventListener(this);
		Event.observe(e, "keyup",  this.keyListener);
		Event.observe(e, "keydown",  this.keyListener);
	},
	
	/**
	 * @private
	 */
	onKey: function(event) {
			
		this.istyping = true;
		
		if (event.keyCode == 27) {
			this.cancel(); // close when ESCAPE is pressed.
			return;
		}
		if (this.timerdisabled) {
			this.resetCommand();
			this.timedCallback(this.checkIfArticleExists.bind(this));
			this.timerdisabled = false;
		}
		
	},
	
	/**
	 * @private
	 */
	timedCallback: function(fnc){
		if(this.istyping){
			this.istyping = false;
			var cb = this.timedCallback.bind(this, fnc);
			setTimeout(cb, 1200);
		} else {	
			fnc();
			this.timerdisabled = true;
			
		}
	},
	
	/**
	 * @private 
	 * 
	 * Checks if article exists and enables/disables command.
	 */
	checkIfArticleExists: function() {
		function ajaxResponseExistsArticle (id, request) {
			pendingElement.hide();
			var answer = request.responseText;
			var regex = /(true|false)/;
			var parts = answer.match(regex);
			
			if (parts == null) {
				// call fails for some reason. Do nothing!
				this.enableCommand(false);
				return;
			} else if (parts[0] == 'true') {
				// article exists -> MUST NOT exist
				this.enableCommand(false);
				return;
			} else {
				this.enableCommand(true);
				
			}
		};
		var pendingElement = new OBPendingIndicator();
		var pageName = $F(this.id+'_input_ontologytools');
		if (pageName == '') {
			this.resetCommand();
			return;
		}
		pendingElement.show(this.id+'_input_ontologytools')
		sajax_do_call('smwfExistsArticle', 
		              [pageName], 
		              ajaxResponseExistsArticle.bind(this, this.id+'_input_ontologytools'));
	},
	
	/**
	 * @private
	 * 
	 * Enables or disables command button.
	 * 
	 * @param b True, if command should be activated, false if title already exists.
	 */
	enableCommand: function(b) {
		
		if (b) {
			$(this.id+'_apply_ontologytools').replace('<a style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools" onclick="'+this.objectname+'.doCommand()">'+gLanguage.getMessage(this.getCommandText())+'</a>');
			$(this.id+'_input_ontologytools').setStyle({
				backgroundColor: '#0F0'
			});
		} else {
			$(this.id+'_apply_ontologytools').replace('<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">'+gLanguage.getMessage('OB_TITLE_EXISTS')+'</span>');
			$(this.id+'_input_ontologytools').setStyle({
				backgroundColor: '#F00'
			});
		}
	},
	
	/**
	 * @private
	 * 
	 * Resets command button to default text, when no text is entered at all.
	 */
	resetCommand: function() {
		$(this.id+'_apply_ontologytools').replace('<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">'+gLanguage.getMessage('OB_ENTER_TITLE')+'</span>');
			$(this.id+'_input_ontologytools').setStyle({
				backgroundColor: '#FFF'
			});
	}
	
	
}

var OBCatgeoryGUITools = Class.create();
OBCatgeoryGUITools.prototype = Object.extend(new OBOntologyGUITools(), {
	initialize: function(id, objectname) {
		this.OBOntologyGUITools(id, objectname);
		this.ontologyTools = new OBOntologyTools();
	},
	
	doCommand: function() {
		switch(this.commandID) {
			case SMW_OB_COMMAND_ADDSUBCATEGORY: {
				this.ontologyTools.addSubcategory($F(this.id+'_input_ontologytools'), this.selectedTitle);
				break;
			}
			case SMW_OB_COMMAND_ADDSUBCATEGORY_SAMELEVEL: {
				this.ontologyTools.addSubcategorySameLevel($F(this.id+'_input_ontologytools'), this.selectedTitle);
				break;
			}
			case SMW_OB_COMMAND_ADDSUBCATEGORY_RENAME: {
				this.ontologyTools.addRenameCategory($F(this.id+'_input_ontologytools'), this.selectedTitle);
				break;
			}
			default: alert('Unknown command!');
		}
	},
	
	getCommandText: function() {
		switch(this.commandID) {
			case SMW_OB_COMMAND_ADDSUBCATEGORY_RENAME: return 'OB_RENAME';
			case SMW_OB_COMMAND_ADDSUBCATEGORY_SAMELEVEL: // fall through
			case SMW_OB_COMMAND_ADDSUBCATEGORY: return 'OB_CREATE';
			
			default: return 'Unknown command';
		}
		
	},
	
	setUserDefinedControls: function() {
		
	}
});

var OBPropertyGUITools = Class.create();
OBPropertyGUITools.prototype = Object.extend(new OBOntologyGUITools(), {
	initialize: function(id, objectname) {
		this.OBOntologyGUITools(id, objectname);
		this.ontologyTools = new OBOntologyTools();
	},
	
	doCommand: function() {
		switch(this.commandID) {
			case SMW_OB_COMMAND_ADDSUBPROPERTY: {
				this.ontologyTools.addSubproperty($F(this.id+'_input_ontologytools'), this.selectedTitle);
				break;
			}
			case SMW_OB_COMMAND_ADDSUBPROPERTY_SAMELEVEL: {
				this.ontologyTools.addSubpropertySameLevel($F(this.id+'_input_ontologytools'), this.selectedTitle);
				break;
			}
			case SMW_OB_COMMAND_SUBPROPERTY_RENAME: {
				this.ontologyTools.addRenameProperty($F(this.id+'_input_ontologytools'), this.selectedTitle);
				break;
			}
			default: alert('Unknown command!');
		}
	},
	
	getCommandText: function() {
		switch(this.commandID) {
			case SMW_OB_COMMAND_ADDSUBPROPERTY: return 'OB_RENAME';
			case SMW_OB_COMMAND_ADDSUBPROPERTY_SAMELEVEL: // fall through
			case SMW_OB_COMMAND_SUBPROPERTY_RENAME: return 'OB_CREATE';
			
			default: return 'Unknown command';
		}
		
	},
	
	setUserDefinedControls: function() {
		
	}
});

var obCategoryMenuProvider = new OBCatgeoryGUITools('categoryTreeMenu', 'obCategoryMenuProvider');
var obPropertyMenuProvider = new OBPropertyGUITools('propertyTreeMenu', 'obPropertyMenuProvider');