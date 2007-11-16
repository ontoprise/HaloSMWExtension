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

var SMW_OB_COMMAND_INSTANCE_DELETE = 7;
var SMW_OB_COMMAND_INSTANCE_RENAME = 8;

var SMW_OB_COMMAND_ADD_SCHEMAPROPERTY = 9;

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
	},
	
	renameInstance: function(selectedTitle, newTitle) {
		
	}
}

var OBInputFieldValidator = Class.create();
OBInputFieldValidator.prototype = {
	initialize: function(id, isValid, enable_fnc, validate_fnc, reset_fnc, cancel_fnc) {
		this.id = id;
		this.validate_fnc = validate_fnc;
		this.enable_fnc = enable_fnc;
		this.reset_fnc = reset_fnc;
		this.cancel_fnc = cancel_fnc;
		
		this.keyListener = null;
		this.istyping = false;
		this.timerdisabled = true;
		
		this.isValid = isValid;
		
		if ($(this.id) != null) this.registerListeners();
	},
	
	OBInputFieldValidator: function(id, isValid, enable_fnc, validate_fnc, reset_fnc, cancel_fnc) {
		this.id = id;
		this.enable_fnc = enable_fnc;
		this.validate_fnc = validate_fnc;
		this.reset_fnc = reset_fnc;
		this.cancel_fnc = cancel_fnc;
		
		this.keyListener = null;
		this.istyping = false;
		this.timerdisabled = true;
		
		this.isValid = isValid;
		
		if ($(this.id) != null) this.registerListeners();
	},
	
	registerListeners: function() {
		var e = $(this.id);
		this.keyListener = this.onKey.bindAsEventListener(this);
		Event.observe(e, "keyup",  this.keyListener);
		Event.observe(e, "keydown",  this.keyListener);
	},
	
	deregisterListeners: function() {
		var e = $(this.id);
		Event.stopObserving(e, "keyup", this.keyListener);
		Event.stopObserving(e, "keydown", this.keyListener);
	},
	
	onKey: function(event) {
			
		this.istyping = true;
		
		if (event.keyCode == 27) {
			this.cancel_fnc(); // close when ESCAPE is pressed.
			return;
		}
		if (this.timerdisabled) {
			this.reset_fnc(this.id);
			this.timedCallback(this.validate.bind(this));
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
			fnc(this.id);
			this.timerdisabled = true;
			
		}
	},
	
	validate: function() {
		this.isValid = this.validate_fnc(this.id);
		if (this.isValid !== null) {
			this.enable_fnc(this.isValid, this.id);
		}
	}
	
}

var OBInputTitleValidator = Class.create();
OBInputTitleValidator.prototype = Object.extend(new OBInputFieldValidator(), {
	initialize: function(id, enable_fnc, reset_fnc, cancel_fnc) {
		this.OBInputFieldValidator(id, false, enable_fnc, this._checkIfArticleExists.bind(this), reset_fnc, cancel_fnc);
		
	},
	
	/**
	 * @private 
	 * 
	 * Checks if article exists and enables/disables command.
	 */
	_checkIfArticleExists: function(id) {
		function ajaxResponseExistsArticle (id, request) {
			pendingElement.hide();
			var answer = request.responseText;
			var regex = /(true|false)/;
			var parts = answer.match(regex);
			
			if (parts == null) {
				// call fails for some reason. Do nothing!
				this.isValid = false;
				this.enable_fnc( false, id);
				return;
			} else if (parts[0] == 'true') {
				// article exists -> MUST NOT exist
				this.isValid = false;
				this.enable_fnc(false, id);
				return;
			} else {
				this.isValid=true;
				this.enable_fnc(true, id);
				
			}
		};
		var pendingElement = new OBPendingIndicator();
		var pageName = $F(this.id);
		if (pageName == '') {
			this.enable_fnc(false, this.id);
			return;
		}
		pendingElement.show(this.id)
		sajax_do_call('smwfExistsArticle', 
		              [pageName], 
		              ajaxResponseExistsArticle.bind(this, this.id));
		return null;
	},
	
});

var OBOntologyGUITools = Class.create();
OBOntologyGUITools.prototype = { 
	initialize: function(id, objectname) {
		this.OBOntologyGUITools(id, objectname);
	},
	
	OBOntologyGUITools: function(id, objectname) {
		this.id = id;
		this.objectname = objectname;
				
		this.commandID = null;
		this.selectedTitle = null;
		
		this.envContainerID = null;
		this.oldHeight = 0;
		
		this.menuOpened = false;
	},
	/**
	 * @public
	 * 
	 * Shows subview.
	 * @param commandID command to execute.
	 * @param node selected node.
	 */
	showContent: function(commandID, selectedTitle, envContainerID) {
		if (this.menuOpened) {
			this._cancel();
		}
		this.commandID = commandID;
		this.selectedTitle = selectedTitle;
		this.envContainerID = envContainerID;
		$(this.id).replace('<div id="'+this.id+'">' +
						this.getUserDefinedControls() +
						'<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">'+gLanguage.getMessage('OB_ENTER_TITLE')+'</span> | ' +
						'<a onclick="'+this.objectname+'._cancel()">'+gLanguage.getMessage('CANCEL')+'</a>' +
					  '</div>');
					  
		// adjust parent container size
		this.oldHeight = $(envContainerID).getHeight();
		this.adjustSize();
		this.setValidators();
		this.setFocus();
		selectionProvider.addListener(this, OB_SELECTIONLISTENER);
		this.menuOpened = true;		
	},
	
	adjustSize: function() {
		var menuBarHeight = $(this.id).getHeight();
		var newHeight = (this.oldHeight-menuBarHeight-5)+"px";
		$(this.envContainerID).setStyle({ height: newHeight});
		
	},
	/**
	 * @abstract
	 */
	selectionChanged: function(id, title, ns, node) {
		
	},
	/**
	 * @public
	 * 
	 * Close subview
	 */
	_cancel: function() {
		
		
		// deregister listeners
		
		selectionProvider.removeListener(this, OB_SELECTIONLISTENER);
		
		// reset height
		var newHeight = (this.oldHeight-2)+"px";
		$(this.envContainerID).setStyle({ height: newHeight});
		
		// remove DIV content
		$(this.id).replace('<div id="'+this.id+'">');
		this.menuOpened = false;
	},
	
		
	
	
	
	enableCommand: function(b, errorMessage) {
		if (b) {
			$(this.id+'_apply_ontologytools').replace('<a style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools" onclick="'+this.objectname+'.doCommand()">'+gLanguage.getMessage(this.getCommandText())+'</a>');
		} else {
			$(this.id+'_apply_ontologytools').replace('<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">'+gLanguage.getMessage(errorMessage)+'</span>');
		}
	},
	
	enable: function(b, id) {
		var bg_color = b ? '#0F0' : $F(id) == '' ? '#FFF' : '#FFF';
	
		this.enableCommand(b, b ?  this.getCommandText() : $F(id) == '' ? 'OB_ENTER_TITLE': 'OB_TITLE_EXISTS');
		$(id).setStyle({
			backgroundColor: bg_color
		});
		
	},
	
	reset: function(id) {
		this.enableCommand(false, 'OB_ENTER_TITLE');
		$(id).setStyle({
				backgroundColor: '#FFF'
		});
	}
		
}

var OBCatgeoryGUITools = Class.create();
OBCatgeoryGUITools.prototype = Object.extend(new OBOntologyGUITools(), {
	initialize: function(id, objectname) {
		this.OBOntologyGUITools(id, objectname);
		this.ontologyTools = new OBOntologyTools();
		this.titleInputValidator = null;
	},
	
	selectionChanged: function(id, title, ns, node) {
		if (ns == SMW_CATEGORY_NS) {
			this.selectedTitle = title;
		}
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
	
	getUserDefinedControls: function() {
		return '<input style="display:block; width:60%; float:left" id="'+this.id+'_input_ontologytools" type="text"/>';
	},
	
	setValidators: function() {
		this.titleInputValidator = new OBInputTitleValidator(this.id+'_input_ontologytools', this.enable.bind(this), this.reset.bind(this), this.cancel.bind(this));
			
	},
	
	setFocus: function() {
		$(this.id+'_input_ontologytools').focus();	
	},
	
	cancel: function() {
		this.titleInputValidator.deregisterListeners();
		this._cancel();
	}
});

var OBPropertyGUITools = Class.create();
OBPropertyGUITools.prototype = Object.extend(new OBOntologyGUITools(), {
	initialize: function(id, objectname) {
		this.OBOntologyGUITools(id, objectname);
		this.ontologyTools = new OBOntologyTools();
	},
	
	selectionChanged: function(id, title, ns, node) {
		if (ns == SMW_PROPERTY_NS) {
			this.selectedTitle = title;
		}
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
			case SMW_OB_COMMAND_SUBPROPERTY_RENAME: return 'OB_RENAME';
			case SMW_OB_COMMAND_ADDSUBPROPERTY_SAMELEVEL: // fall through
			case SMW_OB_COMMAND_ADDSUBPROPERTY: return 'OB_CREATE';
			
			default: return 'Unknown command';
		}
		
	},
	
	getUserDefinedControls: function() {
		return '<input style="display:block; width:60%; float:left" id="'+this.id+'_input_ontologytools" type="text"/>';
	},
	
	setValidators: function() {
		this.titleInputValidator = new OBInputTitleValidator(this.id+'_input_ontologytools', this.enable.bind(this),  this.reset.bind(this), this.cancel.bind(this));
			
	},
	
	setFocus: function() {
		$(this.id+'_input_ontologytools').focus();	
	},
	
	cancel: function() {
		this.titleInputValidator.deregisterListeners();
		this._cancel();
	}
});

var OBInstanceGUITools = Class.create();
OBInstanceGUITools.prototype = Object.extend(new OBOntologyGUITools(), {
	initialize: function(id, objectname) {
		this.OBOntologyGUITools(id, objectname);
		this.ontologyTools = new OBOntologyTools();
	},
	
	selectionChanged: function(id, title, ns, node) {
		if (ns == SMW_INSTANCE_NS) {
			this.selectedTitle = title;
		}
	},
	
	doCommand: function() {
		switch(this.commandID) {
			
			case SMW_OB_COMMAND_INSTANCE_RENAME: {
				this.ontologyTools.renameInstance($F(this.id+'_input_ontologytools'), this.selectedTitle);
				break;
			}
			
			default: alert('Unknown command!');
		}
	},
	
	getCommandText: function() {
		switch(this.commandID) {
			
			case SMW_OB_COMMAND_INSTANCE_RENAME: return 'OB_RENAME';
			
			
			default: return 'Unknown command';
		}
		
	},
	
	getUserDefinedControls: function() {
		return '<input style="display:block; width:60%; float:left" id="'+this.id+'_input_ontologytools" type="text"/>';
	},
	
	setValidators: function() {
		this.titleInputValidator = new OBInputTitleValidator(this.id+'_input_ontologytools', this.enable.bind(this),  this.reset.bind(this), this.cancel.bind(this));
			
	},
	
	setFocus: function() {
		$(this.id+'_input_ontologytools').focus();	
	},
	
	cancel: function() {
		this.titleInputValidator.deregisterListeners();
		this._cancel();
	}
});

var OBSchemaPropertyGUITools = Class.create();
OBSchemaPropertyGUITools.prototype = Object.extend(new OBOntologyGUITools(), {
	initialize: function(id, objectname) {
		this.OBOntologyGUITools(id, objectname);
		this.ontologyTools = new OBOntologyTools();
		this.maxCardValidator = null;
		this.minCardValidator = null;
		this.rangeValidators = [];
		
		this.builtinTypes = null;
		this.count = 0;
	},
	
	selectionChanged: function(id, title, ns, node) {
		if (ns == SMW_CATEGORY_NS) {
			this.selectedTitle = title;
		}
	},
	
	doCommand: function() {
		switch(this.commandID) {
			
			case SMW_OB_COMMAND_ADD_SCHEMAPROPERTY: {
				this.ontologyTools.addSchemaProperty($F(this.id+'_input_ontologytools'), this.selectedTitle);
				break;
			}
			
			default: alert('Unknown command!');
		}
	},
	
	getCommandText: function() {
		switch(this.commandID) {
			
			case SMW_OB_COMMAND_ADD_SCHEMAPROPERTY: return 'OB_CREATE';
			
			
			default: return 'Unknown command';
		}
		
	},
	
	getUserDefinedControls: function() {
		return '<table style="background-color: inherit"><tr>' +
						'<td width="50px;">Title</td>' +
						'<td><input id="'+this.id+'_input_ontologytools" type="text"/></td>' +
					'</tr>' +
					'<tr>' +
						'<td width="50px;">Min Card</td>' +
						'<td><input id="'+this.id+'_minCard_ontologytools" type="text" size="5"/></td>' +
					'</tr>' +
					'<tr>' +
						'<td width="50px;">Max Card</td>' +
						'<td><input id="'+this.id+'_maxCard_ontologytools" type="text" size="5"/></td>' +
					'</tr>' +
				'</table>' +
				'<table id="typesAndRanges" style="background-color: inherit"></table>' +
				'<table style="background-color: inherit">' +
					'<tr>' +
						'<td><a onclick="'+this.objectname+'.addType()">Add type</a></td>' +
						'<td><a onclick="'+this.objectname+'.addRange()">Add range</a></td>' +
					'</tr>' +
				'</table>';
	},
	
	setValidators: function() {
		var enable_fnc = this.enable.bind(this);
		var reset_fnc = this.reset.bind(this);
		var cancel_fnc = this.cancel.bind(this);
		this.titleInputValidator = new OBInputTitleValidator(this.id+'_input_ontologytools', enable_fnc, reset_fnc, cancel_fnc);
		this.maxCardValidator = new OBInputFieldValidator(this.id+'_maxCard_ontologytools', true,  enable_fnc, this.checkMaxCard.bind(this), reset_fnc, cancel_fnc);
		this.minCardValidator = new OBInputFieldValidator(this.id+'_minCard_ontologytools', true,  enable_fnc, this.checkMinCard.bind(this), reset_fnc, cancel_fnc);
		this.requestTypes();
		this.rangeValidators = [];
	},
	
	checkMaxCard: function() {
		var maxCard = $F(this.id+'_maxCard_ontologytools');
		var valid = maxCard == '' || (maxCard.match(/^\d+$/) != null && parseInt(maxCard) > 0) ;
		return valid;
	},
	
	checkMinCard: function() {
		var minCard = $F(this.id+'_minCard_ontologytools');
		var valid = minCard == '' || (minCard.match(/^\d+$/) != null && parseInt(minCard) >= 0) ;
		return valid;
	},
	
	
	
	setFocus: function() {
		$(this.id+'_input_ontologytools').focus();	
	},
	
	cancel: function() {
		this.titleInputValidator.deregisterListeners();
		this.maxCardValidator.deregisterListeners();
		this.minCardValidator.deregisterListeners();
		this._cancel();
	},
	
	enable: function(b, id) {
		var bg_color = b ? '#0F0' : '#F00';
	
		$(id).setStyle({
			backgroundColor: bg_color
		});
		
		this.enableCommand(this.allIsValid(), this.getCommandText());
		
	},
	
	reset: function(id) {
		this.enableCommand(false, 'OB_CREATE');
		$(id).setStyle({
				backgroundColor: '#FFF'
		});
	},
	
	allIsValid: function() {
		var valid =  this.titleInputValidator.isValid && this.maxCardValidator.isValid &&  this.minCardValidator.isValid;
		this.rangeValidators.each(function(e) { if (e!=null) valid &= e.isValid });
		return valid;
	},
	
	requestTypes: function() {
		if (this.builtinTypes != null) {
			this.addType();
			return;
		}
		
		function fillTypesCallback(request) {
			this.builtinTypes = request.responseText.split(",");
			this.addType();
		}
		
		sajax_do_call('smwfGetBuiltinDatatypes', 
		              [], 
		              fillTypesCallback.bind(this));	
	},
	
	newTypeInputBox: function() {
		var toReplace = '<select id="typeRange'+this.count+'_ontologytools" name="types'+this.count+'">';
		for(var i = 1; i < this.builtinTypes.length; i++) {
			toReplace += '<option>'+this.builtinTypes[i]+'</option>';
		}
		toReplace += '</select><a onclick="'+this.objectname+'.removeTypeOrRange(\'typeRange'+this.count+'_ontologytools\', false)"> Remove</a>';
	
		return toReplace;
	},
	
	newRangeInputBox: function() {
		var toReplace = '<input type="text" id="typeRange'+this.count+'_ontologytools"/>';
		toReplace += '<a onclick="'+this.objectname+'.removeTypeOrRange(\'typeRange'+this.count+'_ontologytools\', true)"> Remove</a>';
		return toReplace;
	},
	
	addType: function() {
		if (this.builtinTypes == null) {
			return;
		}
		// tbody already in DOM?
		var addTo = $('typesAndRanges').firstChild == null ? $('typesAndRanges') : $('typesAndRanges').firstChild;
		var toReplace = $(addTo.appendChild(document.createElement("tr")));
		toReplace.replace('<tr><td width="50px;">Type </td><td>'+this.newTypeInputBox()+'</td></tr>');
		
		this.count++;
		this.adjustSize();
	},
	
	addRange: function() {
		// tbody already in DOM?
		var addTo = $('typesAndRanges').firstChild == null ? $('typesAndRanges') : $('typesAndRanges').firstChild;
		
		// create dummy element and replace afterwards
		var toReplace = $(addTo.appendChild(document.createElement("tr")));
		toReplace.replace('<tr><td width="50px;">Range </td><td>'+this.newRangeInputBox()+'</td></tr>');
		
		var enable_fnc = this.enable.bind(this);
		var reset_fnc = this.reset.bind(this);
		var cancel_fnc = this.cancel.bind(this);
		this.rangeValidators[this.count] = (new OBInputTitleValidator('typeRange'+this.count+'_ontologytools', enable_fnc, reset_fnc, cancel_fnc));
		this.enable(false, 'typeRange'+this.count+'_ontologytools');
		
		this.count++;
		this.adjustSize();
	},
	
	removeTypeOrRange: function(id, isRange) {
		
		if (isRange) {		
			// deregisterValidator
			var match = /typeRange(\d+)/;
			var num = match.exec(id)[1];
			this.rangeValidators[num].deregisterListeners();
			this.rangeValidators[num] = null;
		}
		
		var row = $(id);
		while(row.parentNode.getAttribute('id') != 'typesAndRanges') row = row.parentNode;
		// row is tbody element
		row.removeChild($(id).parentNode.parentNode);
		
		this.adjustSize();
	}
});

var obCategoryMenuProvider = new OBCatgeoryGUITools('categoryTreeMenu', 'obCategoryMenuProvider');
var obPropertyMenuProvider = new OBPropertyGUITools('propertyTreeMenu', 'obPropertyMenuProvider');
var obInstanceMenuProvider = new OBInstanceGUITools('instanceListMenu', 'obInstanceMenuProvider');
var obSchemaPropertiesMenuProvider = new OBSchemaPropertyGUITools('schemaPropertiesMenu', 'obSchemaPropertiesMenuProvider');