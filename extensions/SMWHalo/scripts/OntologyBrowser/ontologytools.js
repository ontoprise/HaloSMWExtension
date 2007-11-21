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

var OBArticleCreator = Class.create();
OBArticleCreator.prototype = { 
	initialize: function() {
		this.pendingIndicator = new OBPendingIndicator();
	},
	
	createArticle : function(title, content, optionalText, creationComment,
	                         callback, node) {
		
		function ajaxResponseCreateArticle(request) {
			this.pendingIndicator.hide();
			if (request.status != 200) {
				alert(gLanguage.getMessage('ERROR_CREATING_ARTICLE'));
				return;
			}
			
			var answer = request.responseText;
			var regex = /(true|false),(true|false),(.*)/;
			var parts = answer.match(regex);
			
			if (parts == null) {
				alert(gLanguage.getMessage('ERROR_CREATING_ARTICLE'));
				return;
			}
			
			var success = parts[1];
			var created = parts[2];
			var title = parts[3];
			
			if (success == "true") {
				callback(success, created, title);
			} 
		}
		this.pendingIndicator.show(node);
		sajax_do_call('smwfCreateArticle', 
		              [title, content, optionalText, creationComment], 
		              ajaxResponseCreateArticle.bind(this));
		              
	},
	
	deleteArticle: function(title, reason, callback, node) {
		
		function ajaxResponseDeleteArticle(request) {
			this.pendingIndicator.hide();
			if (request.status != 200) {
				alert(gLanguage.getMessage('ERROR_DELETING_ARTICLE'));
				return;
			}
					
			callback();
			
		}
		
		this.pendingIndicator.show(node);
		sajax_do_call('smwfDeleteArticle', 
		              [title, reason], 
		              ajaxResponseDeleteArticle.bind(this));
	},
	
	renameArticle: function(oldTitle, newTitle, reason, callback, node) {
		
		function ajaxResponseRenameArticle(request) {
			this.pendingIndicator.hide();
			if (request.status != 200) {
				alert(gLanguage.getMessage('ERROR_RENAMING_ARTICLE'));
				return;
			}
					
			callback();
			
		}
		
		this.pendingIndicator.show(node);
		sajax_do_call('smwfRenameArticle', 
		              [oldTitle, newTitle, reason], 
		              ajaxResponseRenameArticle.bind(this));
	}
	
}
var articleCreator = new OBArticleCreator();

var OBOntologyModifier = Class.create();
OBOntologyModifier.prototype = { 
	initialize: function() {
		this.date = new Date();
		this.count = 0;
	},
	
	addSubcategory: function(subCategoryTitle, superCategoryTitle, superCategoryID) {
		function callback() {
			var subCategoryXML = GeneralXMLTools.createDocumentFromString(this.createCategoryNode(subCategoryTitle));
			this.insertCategoryNode(superCategoryID, subCategoryXML);
			transformer.transformXMLToHTML(dataAccess.OB_cachedCategoryTree, $('categoryTree'), true);
			
			selectionProvider.fireSelectionChanged(superCategoryID, superCategoryTitle, SMW_CATEGORY_NS, $(superCategoryID))
			selectionProvider.fireRefresh();
		}
		articleCreator.createArticle(gLanguage.getMessage('CATEGORY')+subCategoryTitle,  
			                   "[["+gLanguage.getMessage('CATEGORY')+superCategoryTitle+"]]", '',
							   gLanguage.getMessage('CREATE_SUB_CATEGORY'), callback.bind(this), $(superCategoryID));
	},
	
	addSubcategoryOnSameLevel: function(newCategoryTitle, siblingCategoryTitle, sibligCategoryID) {
		function callback() {
			var newCategoryXML = GeneralXMLTools.createDocumentFromString(this.createCategoryNode(newCategoryTitle));
			var superCategoryID = GeneralXMLTools.getNodeById(dataAccess.OB_cachedCategoryTree, sibligCategoryID).parentNode.getAttribute('id');
			this.insertCategoryNode(superCategoryID, newCategoryXML);
			transformer.transformXMLToHTML(dataAccess.OB_cachedCategoryTree, $('categoryTree'), true);
			
			selectionProvider.fireSelectionChanged(sibligCategoryID, siblingCategoryTitle, SMW_CATEGORY_NS, $(sibligCategoryID))
			selectionProvider.fireRefresh();
		}
		var superCategoryTitle = GeneralXMLTools.getNodeById(dataAccess.OB_cachedCategoryTree, sibligCategoryID).parentNode.getAttribute('title');
		var content = superCategoryTitle != null ? "[["+gLanguage.getMessage('CATEGORY')+superCategoryTitle+"]]" : "";
		articleCreator.createArticle(gLanguage.getMessage('CATEGORY')+subCategoryTitle, content, '',
							   gLanguage.getMessage('CREATE_SUB_CATEGORY'), callback.bind(this), $(superCategoryID));
	},
	
	renameCategory: function(newCategoryTitle, categoryTitle, categoryID) {
		function callback() {
			this.renameCategoryNode(categoryID, newCategoryTitle);
			transformer.transformXMLToHTML(dataAccess.OB_cachedCategoryTree, $('categoryTree'), true);
			
			selectionProvider.fireSelectionChanged(categoryID, categoryTitle, SMW_CATEGORY_NS, $(categoryID))
			selectionProvider.fireRefresh();
		}
		articleCreator.renameArticle(gLanguage.getMessage('CATEGORY')+categoryTitle, gLanguage.getMessage('CATEGORY')+newCategoryTitle, "OB", callback.bind(this), $(categoryID));
	},
	
	addSubproperty: function(subPropertyTitle, superPropertyTitle, superPropertyID) {
		function callback() {
			var subPropertyXML = GeneralXMLTools.createDocumentFromString(this.createPropertyNode(subPropertyTitle));
			this.insertPropertyNode(superPropertyID, subPropertyXML);
			transformer.transformXMLToHTML(dataAccess.OB_cachedPropertyTree, $('propertyTree'), true);
			
			selectionProvider.fireSelectionChanged(superPropertyID, superPropertyTitle, SMW_PROPERTY_NS, $(superPropertyID))
			selectionProvider.fireRefresh();
		}
		articleCreator.createArticle(gLanguage.getMessage('PROPERTY')+subPropertyTitle, '',   
			                    "\n[[SMW_SP_SUBPROPERTY_OF::"+gLanguage.getMessage('PROPERTY')+superPropertyTitle+"]]",
							 gLanguage.getMessage('CREATE_SUB_PROPERTY'), callback.bind(this), $(superPropertyID));
	},
	
	addSubpropertyOnSameLevel: function(newPropertyTitle, siblingPropertyTitle, sibligPropertyID) {
		function callback() {
			var subPropertyXML = GeneralXMLTools.createDocumentFromString(this.createPropertyNode(newPropertyTitle));
			var superPropertyID = GeneralXMLTools.getNodeById(dataAccess.OB_cachedPropertyTree, sibligPropertyID).parentNode.getAttribute('id');
			this.insertPropertyNode(superPropertyID, subPropertyXML);
			transformer.transformXMLToHTML(dataAccess.OB_cachedPropertyTree, $('propertyTree'), true);
		
			selectionProvider.fireSelectionChanged(sibligPropertyID, siblingPropertyTitle, SMW_PROPERTY_NS, $(sibligPropertyID))
			selectionProvider.fireRefresh();
		}
		
		var superPropertyTitle = GeneralXMLTools.getNodeById(dataAccess.OB_cachedPropertyTree, sibligPropertyID).parentNode.getAttribute('title');
		var content = superPropertyTitle != null ? "\n[[SMW_SP_SUBPROPERTY_OF::"+gLanguage.getMessage('PROPERTY')+superPropertyTitle+"]]" : "";
		articleCreator.createArticle(gLanguage.getMessage('PROPERTY')+subPropertyTitle, '',   
			                   content,
							 gLanguage.getMessage('CREATE_SUB_PROPERTY'), callback.bind(this), $(superPropertyID));
	},
	
	renameProperty: function(newPropertyTitle, oldPropertyTitle, propertyID) {
		function callback() {
			this.renamePropertyNode(propertyID, newPropertyTitle);
			transformer.transformXMLToHTML(dataAccess.OB_cachedPropertyTree, $('propertyTree'), true);
			
			selectionProvider.fireSelectionChanged(propertyID, newPropertyTitle, SMW_PROPERTY_NS, $(propertyID))
			selectionProvider.fireRefresh();
		}
		articleCreator.renameArticle(gLanguage.getMessage('PROPERTY')+oldPropertyTitle, gLanguage.getMessage('PROPERTY')+newPropertyTitle, "OB", callback.bind(this), $(propertyID));
	},
	
	addSchemaProperty: function(propertyTitle, minCard, maxCard, rangeOrTypes, builtinTypes, selectedTitle, selectedID) {
		function callback() {
			var newPropertyXML = GeneralXMLTools.createDocumentFromString(this.createSchemaProperty(propertyTitle, minCard, maxCard, rangeOrTypes, builtinTypes, selectedTitle, selectedID));
			dataAccess.OB_cachedProperties.documentElement.removeAttribute('isEmpty');
			dataAccess.OB_cachedProperties.documentElement.removeAttribute('textToDisplay');
			GeneralXMLTools.importNode(dataAccess.OB_cachedProperties.documentElement, newPropertyXML.documentElement, true);
			transformer.transformXMLToHTML(dataAccess.OB_cachedProperties, $('relattributes'), true);
					
			selectionProvider.fireRefresh();
		}
		
		var content = maxCard != '' ? "\n[[SMW_SSP_HAS_MAX_CARD::"+maxCard+"]]" : "";
		content += minCard != '' ? "\n[[SMW_SSP_HAS_MIN_CARD::"+minCard+"]]" : "";
		
		var rangeTypeStr = "";
		var rangeCategories = new Array();
		for(var i = 0, n = rangeOrTypes.length; i < n; i++) {
			if (builtinTypes.indexOf(rangeOrTypes[i]) != -1) {
				// is type
				rangeTypeStr += gLanguage.getMessage('TYPE')+rangeOrTypes[i]+(i == n-1 ? "" : ";");
			} else {
				rangeTypeStr += gLanguage.getMessage('TYPE_PAGE')+(i == n-1 ? "" : ";");
				rangeCategories.push(rangeOrTypes[i]);
			}
		}
		content += "\n[[SMW_SP_HAS_TYPE::"+rangeTypeStr+"]]";
		rangeCategories.each(function(c) { content += "\n[[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT::"+gLanguage.getMessage('CATEGORY')+selectedTitle+"; "+gLanguage.getMessage('CATEGORY')+c+"]]" });
		if (rangeCategories.length == 0) {
			content += "\n[[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT::"+gLanguage.getMessage('CATEGORY')+selectedTitle+"]]";
		}
		articleCreator.createArticle(gLanguage.getMessage('PROPERTY')+propertyTitle, '',   
			                   content,
							 gLanguage.getMessage('CREATE_PROPERTY'), callback.bind(this), $(selectedID));
	},
	
	renameInstance: function(newInstanceTitle, oldInstanceTitle, instanceID) {
		function callback() {
			this.renameInstanceNode(newInstanceTitle, instanceID);
			transformer.transformXMLToHTML(dataAccess.OB_cachedInstances, $('instanceList'), true);
			
			selectionProvider.fireSelectionChanged(instanceID, newInstanceTitle, SMW_INSTANCE_NS, $(instanceID))
			selectionProvider.fireRefresh();
		}
		articleCreator.renameArticle(oldInstanceTitle, newInstanceTitle, "OB", callback.bind(this), $(instanceID));
	},
	
	deleteInstance: function(instanceTitle, instanceID) {
		function callback() {
			this.deleteInstanceNode(instanceID);
			transformer.transformXMLToHTML(dataAccess.OB_cachedInstances, $('instanceList'), true);
			
			selectionProvider.fireSelectionChanged(null, null, SMW_INSTANCE_NS, null)
			selectionProvider.fireRefresh();
		}
		articleCreator.deleteArticle(instanceTitle, "OB", callback.bind(this), $(instanceID));
	},
	
	createCategoryNode: function(subCategoryTitle) {
		this.count++;
		return '<conceptTreeElement title="'+subCategoryTitle+'" id="ID_'+(this.date.getTime()+this.count)+'" isLeaf="true" expanded="true"/>';
	},
	
	createPropertyNode: function(subPropertyTitle) {
		this.count++;
		return '<propertyTreeElement title="'+subPropertyTitle+'" id="ID_'+(this.date.getTime()+this.count)+'" isLeaf="true" expanded="true"/>';
	},
	
	createSchemaProperty: function(propertyTitle, minCard, maxCard, typeRanges, builtinTypes, selectedTitle, selectedID) {
		this.count++;
		rangeTypes = "";
		for(var i = 0, n = typeRanges.length; i < n; i++) {
			if (builtinTypes.indexOf(typeRanges[i]) != -1) {
				// is type
				rangeTypes += '<rangeType>'+typeRanges[i]+'</rangeType>';
			} else {
				rangeTypes += '<rangeType>'+gLanguage.getMessage('TYPE_PAGE')+'</rangeType>';
			}
		}
		minCard = minCard == '' ? '0' : minCard;
		maxCard = maxCard == '' ? '*' : maxCard;
		return '<property title="'+propertyTitle+'" minCard="'+minCard+'" maxCard="'+maxCard+'">'+rangeTypes+'</property>';
	},
	
	renameInstanceNode: function(newInstanceTitle, instanceID) {
		var instanceNode = GeneralXMLTools.getNodeById(dataAccess.OB_cachedInstances, instanceID);
		instanceNode.removeAttribute("title");
		instanceNode.setAttribute("title", newInstanceTitle);
	},
	
	deleteInstanceNode: function(instanceID) {
		var instanceNode = GeneralXMLTools.getNodeById(dataAccess.OB_cachedInstances, instanceID);
		instanceNode.parentNode.removeChild(instanceNode);
	},
	
	insertCategoryNode: function(superCategoryID, subCategoryXML) {
		var superCategoryNodeCached = superCategoryID != null ? GeneralXMLTools.getNodeById(dataAccess.OB_cachedCategoryTree, superCategoryID) : dataAccess.OB_cachedCategoryTree.documentElement;
		var superCategoryNodeDisplayed = superCategoryID != null ? GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, superCategoryID) : dataAccess.OB_currentlyDisplayedTree.documentElement;
		
		// make sure that supercategory is no leaf anymore and set it to expanded now.
		superCategoryNodeCached.removeAttribute("isLeaf");
		superCategoryNodeCached.setAttribute("expanded", "true");
		superCategoryNodeDisplayed.removeAttribute("isLeaf");
		superCategoryNodeDisplayed.setAttribute("expanded", "true");
		
		// insert in cache and displayed tree
		GeneralXMLTools.importNode(superCategoryNodeCached, subCategoryXML.documentElement, true);
		GeneralXMLTools.importNode(superCategoryNodeDisplayed, subCategoryXML.documentElement, true);
	},
	
	insertPropertyNode: function(superpropertyID, subpropertyXML) {
		var superpropertyNodeCached = superpropertyID != null ? GeneralXMLTools.getNodeById(dataAccess.OB_cachedPropertyTree, superpropertyID) : dataAccess.OB_cachedPropertyTree.documentElement;
		var superpropertyNodeDisplayed = superpropertyID != null ? GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, superpropertyID) : dataAccess.OB_currentlyDisplayedTree.documentElement;
		
		// make sure that superproperty is no leaf anymore and set it to expanded now.
		superpropertyNodeCached.removeAttribute("isLeaf");
		superpropertyNodeCached.setAttribute("expanded", "true");
		superpropertyNodeDisplayed.removeAttribute("isLeaf");
		superpropertyNodeDisplayed.setAttribute("expanded", "true");
		
		// insert in cache and displayed tree
		GeneralXMLTools.importNode(superpropertyNodeCached, subpropertyXML.documentElement, true);
		GeneralXMLTools.importNode(superpropertyNodeDisplayed, subpropertyXML.documentElement, true);
	},
	
	renameCategoryNode: function(categoryID, newCategoryTitle) {
		var categoryNodeCached = GeneralXMLTools.getNodeById(dataAccess.OB_cachedCategoryTree, categoryID);
		var categoryNodeDisplayed = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, categoryID);
		categoryNodeCached.removeAttribute("title");
		categoryNodeDisplayed.removeAttribute("title");
		categoryNodeCached.setAttribute("title", newCategoryTitle); //TODO: escape
		categoryNodeDisplayed.setAttribute("title", newCategoryTitle);
	
	},
	
	renamePropertyNode: function(propertyID, newPropertyTitle) {
		var propertyNodeCached = GeneralXMLTools.getNodeById(dataAccess.OB_cachedPropertyTree, propertyID);
		var propertyNodeDisplayed = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, propertyID);
		propertyNodeCached.removeAttribute("title");
		propertyNodeDisplayed.removeAttribute("title");
		propertyNodeCached.setAttribute("title", newPropertyTitle); //TODO: escape
		propertyNodeDisplayed.setAttribute("title", newPropertyTitle);
	}
}

// global object for ontology modification
var ontologyTools = new OBOntologyModifier();

var OBInputFieldValidator = Class.create();
OBInputFieldValidator.prototype = {
	initialize: function(id, isValid, control, validate_fnc) {
		this.id = id;
		this.validate_fnc = validate_fnc;
		this.control = control;
		
		this.keyListener = null;
		this.blurListener = null;
		this.istyping = false;
		this.timerdisabled = true;
		
		this.isValid = isValid;
		
		if ($(this.id) != null) this.registerListeners();
	},
	
	OBInputFieldValidator: function(id, isValid, control, validate_fnc) {
		this.id = id;
		this.validate_fnc = validate_fnc;
		this.control = control;
		
		this.keyListener = null;
		this.istyping = false;
		this.timerdisabled = true;
		
		this.isValid = isValid;
		this.lastValidation = null;
		
		if ($(this.id) != null) this.registerListeners();
	},
	
	registerListeners: function() {
		var e = $(this.id);
		this.keyListener = this.onKeyEvent.bindAsEventListener(this);
		this.blurListener = this.onBlurEvent.bindAsEventListener(this);
		Event.observe(e, "keyup",  this.keyListener);
		Event.observe(e, "keydown",  this.keyListener);
		Event.observe(e, "blur",  this.blurListener);
	},
	
	deregisterListeners: function() {
		var e = $(this.id);
		Event.stopObserving(e, "keyup", this.keyListener);
		Event.stopObserving(e, "keydown", this.keyListener);
		Event.stopObserving(e, "blur", this.blurListener);
	},
	
	onKeyEvent: function(event) {
			
		this.istyping = true;
		
		/*if (event.keyCode == 27) {
			// ESCAPE was pressed, so close submenu.
			this.control.cancel(); 
			return;
		}*/
		
		if (event.keyCode == 9 || event.ctrlKey || event.altKey || event.keyCode == 18 || event.keyCode == 17 ) {
			// TAB, CONTROL OR ALT was pressed, do nothing
			return;
		}
		
		if ((event.ctrlKey || event.altKey) && event.keyCode == 32) {
			// autoCompletion request, do nothing
			return;
		}
		
		if (event.keyCode >= 37 && event.keyCode <= 40) {
			// cursor keys, do nothing
			return;
		}
		
		if (this.timerdisabled) {
			this.control.reset(this.id);
			this.timedCallback(this.validate.bind(this));
			this.timerdisabled = false;
		}
		
	},
	
	/**
	 * Validate on blur if content has changed since last validation
	 */
	onBlurEvent: function(event) {
		if (this.lastValidation != $F(this.id)) {
			this.validate();
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
		this.lastValidation = $F(this.id);
		this.isValid = this.validate_fnc(this.id);
		if (this.isValid !== null) {
			this.control.enable(this.isValid, this.id);
		}
	}
	
}

var OBInputTitleValidator = Class.create();
OBInputTitleValidator.prototype = Object.extend(new OBInputFieldValidator(), {
	initialize: function(id, ns, mustExist, control) {
		this.OBInputFieldValidator(id, false, control, this._checkIfArticleExists.bind(this));
		this.ns = ns;
		this.mustExist = mustExist;
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
				this.control.enable( false, id);
				return;
			} else if (parts[0] == 'true') {
				// article exists -> MUST NOT exist
				this.isValid = this.mustExist;
				this.control.enable(this.mustExist, id);
				return;
			} else {
				this.isValid=!this.mustExist;
				this.control.enable(!this.mustExist, id);
				
			}
		};
		var pendingElement = new OBPendingIndicator();
		var pageName = $F(this.id);
		if (pageName == '') {
			this.control.enable(false, this.id);
			return;
		}
		pendingElement.show(this.id)
		var pageNameWithNS = this.ns == '' ? pageName : this.ns+":"+pageName;
		sajax_do_call('smwfExistsArticle', 
		              [pageNameWithNS], 
		              ajaxResponseExistsArticle.bind(this, this.id));
		return null;
	}
	
		
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
		var bg_color = b ? '#0F0' : $F(id) == '' ? '#FFF' : '#F00';
	
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
	
		this.titleInputValidator = null;
		this.selectedID = null;
	
		selectionProvider.addListener(this, OB_SELECTIONLISTENER);
	},
	
	selectionChanged: function(id, title, ns, node) {
		if (ns == SMW_CATEGORY_NS) {
			this.selectedTitle = title;
			this.selectedID = id;
			
		}
	},
	
	doCommand: function() {
		switch(this.commandID) {
			case SMW_OB_COMMAND_ADDSUBCATEGORY: {
				ontologyTools.addSubcategory($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
				this.cancel();
				break;
			}
			case SMW_OB_COMMAND_ADDSUBCATEGORY_SAMELEVEL: {
				ontologyTools.addSubcategoryOnSameLevel($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
				this.cancel();
				break;
			}
			case SMW_OB_COMMAND_ADDSUBCATEGORY_RENAME: {
				ontologyTools.renameCategory($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
				this.cancel();
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
		this.titleInputValidator = new OBInputTitleValidator(this.id+'_input_ontologytools', gLanguage.getMessage('CATEGORY_NS_WOC'), false, this);
			
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
		this.selectedTitle = null;
		this.selectedID = null;
		selectionProvider.addListener(this, OB_SELECTIONLISTENER);
	},
	
	selectionChanged: function(id, title, ns, node) {
		if (ns == SMW_PROPERTY_NS) {
			this.selectedTitle = title;
			this.selectedID = id;
		}
	},
	
	doCommand: function() {
		switch(this.commandID) {
			case SMW_OB_COMMAND_ADDSUBPROPERTY: {
				ontologyTools.addSubproperty($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
				this.cancel();
				break;
			}
			case SMW_OB_COMMAND_ADDSUBPROPERTY_SAMELEVEL: {
				ontologyTools.addSubpropertyOnSameLevel($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
				this.cancel();
				break;
			}
			case SMW_OB_COMMAND_SUBPROPERTY_RENAME: {
				ontologyTools.renameProperty($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
				this.cancel();
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
		this.titleInputValidator = new OBInputTitleValidator(this.id+'_input_ontologytools', gLanguage.getMessage('PROPERTY_NS_WOC'), false, this);
			
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
			
		this.selectedTitle = null;
		this.selectedID = null;
		selectionProvider.addListener(this, OB_SELECTIONLISTENER);
	},
	
	selectionChanged: function(id, title, ns, node) {
		if (ns == SMW_INSTANCE_NS) {
			this.selectedTitle = title;
			this.selectedID = id;
		}
	},
	
	doCommand: function(directCommandID) {
		var commandID = directCommandID ? directCommandID : this.commandID
		switch(commandID) {
			
			case SMW_OB_COMMAND_INSTANCE_RENAME: {
				ontologyTools.renameInstance($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
				this.cancel();
				break;
			}
			case SMW_OB_COMMAND_INSTANCE_DELETE: {
				ontologyTools.deleteInstance(this.selectedTitle, this.selectedID);
				this.cancel();
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
		this.titleInputValidator = new OBInputTitleValidator(this.id+'_input_ontologytools', '', false, this);
			
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
	
		
		this.selectedTitle = null;
		this.selectedID = null;
		
		this.maxCardValidator = null;
		this.minCardValidator = null;
		this.rangeValidators = [];
		
		this.builtinTypes = null;
		this.count = 0;
		
		selectionProvider.addListener(this, OB_SELECTIONLISTENER);
		this.requestTypes();
	},
	
	selectionChanged: function(id, title, ns, node) {
		if (ns == SMW_CATEGORY_NS) {
			this.selectedTitle = title;
			this.selectedID = id;
		}
	},
	
	doCommand: function() {
		switch(this.commandID) {
			
			case SMW_OB_COMMAND_ADD_SCHEMAPROPERTY: {
				var propertyTitle = $F(this.id+'_propertytitle_ontologytools');
				var minCard = $F(this.id+'_minCard_ontologytools');
				var maxCard = $F(this.id+'_maxCard_ontologytools');
				var rangeOrTypes = [];
				for (var i = 0; i < this.count; i++) {
					if ($('typeRange'+i+'_ontologytools') != null) {
						rangeOrTypes.push($F('typeRange'+i+'_ontologytools'));
					}
				}
				ontologyTools.addSchemaProperty(propertyTitle, minCard, maxCard, rangeOrTypes, this.builtinTypes, this.selectedTitle, this.selectedID);
				this.cancel();
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
						'<td width="60px;">'+gLanguage.getMessage('NAME')+'</td>' +
						'<td><input id="'+this.id+'_propertytitle_ontologytools" type="text" tabIndex="101"/></td>' +
					'</tr>' +
					'<tr>' +
						'<td width="60px;">'+gLanguage.getMessage('MIN_CARD')+'</td>' +
						'<td><input id="'+this.id+'_minCard_ontologytools" type="text" size="5" tabIndex="102"/></td>' +
					'</tr>' +
					'<tr>' +
						'<td width="60px;">'+gLanguage.getMessage('MAX_CARD')+'</td>' +
						'<td><input id="'+this.id+'_maxCard_ontologytools" type="text" size="5" tabIndex="103"/></td>' +
					'</tr>' +
				'</table>' +
				'<table id="typesAndRanges" style="background-color: inherit"></table>' +
				'<table style="background-color: inherit">' +
					'<tr>' +
						'<td><a onclick="'+this.objectname+'.addType()">'+gLanguage.getMessage('ADD_TYPE')+'</a></td>' +
						'<td><a onclick="'+this.objectname+'.addRange()">'+gLanguage.getMessage('ADD_RANGE')+'</a></td>' +
					'</tr>' +
				'</table>';
	},
	
	setValidators: function() {
		this.titleInputValidator = new OBInputTitleValidator(this.id+'_propertytitle_ontologytools', gLanguage.getMessage('PROPERTY_NS_WOC'), false, this);
		this.maxCardValidator = new OBInputFieldValidator(this.id+'_maxCard_ontologytools', true, this, this.checkMaxCard.bind(this));
		this.minCardValidator = new OBInputFieldValidator(this.id+'_minCard_ontologytools', true, this, this.checkMinCard.bind(this));
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
		$(this.id+'_propertytitle_ontologytools').focus();	
	},
	
	cancel: function() {
		this.titleInputValidator.deregisterListeners();
		this.maxCardValidator.deregisterListeners();
		this.minCardValidator.deregisterListeners();
		this.rangeValidators.each(function(e) { if (e!=null) e.deregisterListeners() });
		
		this._cancel();
	},
	
	enable: function(b, id) {
		var bg_color = b ? '#0F0' : $F(id) == '' ? '#FFF' : '#F00';
	
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
				
		function fillTypesCallback(request) {
			this.builtinTypes = request.responseText.split(",");
			
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
		toReplace += '</select><img src="'+wgServer+wgScriptPath+'/extensions/SMWHalo/skins/redcross.gif" onclick="'+this.objectname+'.removeTypeOrRange(\'typeRange'+this.count+'_ontologytools\', false)"/>';
	
		return toReplace;
	},
	
	newRangeInputBox: function() {
		var toReplace = '<input class="wickEnabled" typeHint="14" type="text" id="typeRange'+this.count+'_ontologytools" tabIndex="'+(this.count+104)+'"/>';
		toReplace += '<img src="'+wgServer+wgScriptPath+'/extensions/SMWHalo/skins/redcross.gif" onclick="'+this.objectname+'.removeTypeOrRange(\'typeRange'+this.count+'_ontologytools\', true)"/>';
		return toReplace;
	},
	
	addType: function() {
		if (this.builtinTypes == null) {
			return;
		}
		// tbody already in DOM?
		var addTo = $('typesAndRanges').firstChild == null ? $('typesAndRanges') : $('typesAndRanges').firstChild;
		var toReplace = $(addTo.appendChild(document.createElement("tr")));
		toReplace.replace('<tr><td width="60px;">Type </td><td>'+this.newTypeInputBox()+'</td></tr>');
		
		this.count++;
		this.adjustSize();
	},
	
	addRange: function() {
		// tbody already in DOM?
		var addTo = $('typesAndRanges').firstChild == null ? $('typesAndRanges') : $('typesAndRanges').firstChild;
		
		autoCompleter.deregisterAllInputs();
		// create dummy element and replace afterwards
		var toReplace = $(addTo.appendChild(document.createElement("tr")));
		toReplace.replace('<tr><td width="60px;">Range </td><td>'+this.newRangeInputBox()+'</td></tr>');
		autoCompleter.registerAllInputs();
		
		this.rangeValidators[this.count] = (new OBInputTitleValidator('typeRange'+this.count+'_ontologytools', gLanguage.getMessage('CATEGORY_NS_WOC'), true, this));
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
		
		this.enableCommand(this.allIsValid(), this.getCommandText());
		this.adjustSize();
	}
});

var obCategoryMenuProvider = new OBCatgeoryGUITools('categoryTreeMenu', 'obCategoryMenuProvider');
var obPropertyMenuProvider = new OBPropertyGUITools('propertyTreeMenu', 'obPropertyMenuProvider');
var obInstanceMenuProvider = new OBInstanceGUITools('instanceListMenu', 'obInstanceMenuProvider');
var obSchemaPropertiesMenuProvider = new OBSchemaPropertyGUITools('schemaPropertiesMenu', 'obSchemaPropertiesMenuProvider');