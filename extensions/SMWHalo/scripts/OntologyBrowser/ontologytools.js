/*  Copyright 2007, ontoprise GmbH
 *   Author: Kai K�hn
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

/**
 * @file
 * @ingroup SMWHaloSpecials
 * @ingroup SMWHaloOntologyBrowser
 * 
 * @author Kai K�hn
 */

// commandIDs
var SMW_OB_COMMAND_ADDSUBCATEGORY = 1;
var SMW_OB_COMMAND_ADDSUBCATEGORY_SAMELEVEL = 2;
var SMW_OB_COMMAND_SUBCATEGORY_RENAME = 3;

var SMW_OB_COMMAND_ADDSUBPROPERTY = 4;
var SMW_OB_COMMAND_ADDSUBPROPERTY_SAMELEVEL = 5;
var SMW_OB_COMMAND_SUBPROPERTY_RENAME = 6;

var SMW_OB_COMMAND_INSTANCE_DELETE = 7;
var SMW_OB_COMMAND_INSTANCE_CREATE = 10;
var SMW_OB_COMMAND_INSTANCE_RENAME = 8;

var SMW_OB_COMMAND_ADD_SCHEMAPROPERTY = 9;

// Event types
window.OB_SELECTIONLISTENER = 'selectionChanged';
window.OB_SELECTEDTRIPLELISTENER = 'selectedTripleChanged';
window.OB_TREETABCHANGELISTENER = 'treeTabChanged';
window.OB_BEFOREREFRESHLISTENER = 'beforeRefresh';
window.OB_REFRESHLISTENER = 'refresh';
window.OB_FILTERTREE = 'filterTree';
window.OB_FILTERBROWSING = 'filterBrowsing';
window.OB_RESET = 'reset';

/**
 * Event Provider. Supports following events:
 * 
 * 1. selectionChanged 2. refresh
 */
var OBEventProvider = Class.create();
OBEventProvider.prototype = {
	initialize : function() {
		this.listeners = new Array();
	},

	/**
	 * @public
	 * 
	 * Adds a listener.
	 * 
	 * @param listener
	 * @param type
	 */
	addListener : function(listener, type) {
		if (this.listeners[type] == null) {
			this.listeners[type] = new Array();
		}
		if (typeof (listener[type] == 'function')) {
			this.listeners[type].push(listener);
		}
	},

	/**
	 * @public
	 * 
	 * Removes a listener.
	 * 
	 * @param listener
	 * @param type
	 */
	removeListener : function(listener, type) {
		if (this.listeners[type] == null)
			return;
		this.listeners[type] = this.listeners[type].without(listener);
	},

	/**
	 * @public
	 * 
	 * Fires selectionChanged event. The listener method must have the name
	 * 'selectionChanged' with the following signature:
	 * 
	 * @param id
	 *            ID of selected element in DOM/XML tree.
	 * @param title
	 *            Title of selected element
	 * @param ns
	 *            namespace
	 * @param node
	 *            in HTML DOM tree.
	 * 
	 */
	fireSelectionChanged : function(id, title, ns, node) {
		if (!this.listeners[OB_SELECTIONLISTENER])
			return;
		this.listeners[OB_SELECTIONLISTENER].each(function(l) {
			l.selectionChanged(id, title, ns, node);
		});
	},
	
	/**
	 * Fires selectedTripleChanged event. The listener method must have the name
	 * 'selectedTripleChanged' with the following signature:
	 * 
	 * @param s subject URI
	 * @param p predicate URI
	 * @param o object URI or literal
	 */
	fireSelectedTripleChanged: function(s,p,o) {
		if (!this.listeners[OB_SELECTEDTRIPLELISTENER])
			return;
		this.listeners[OB_SELECTEDTRIPLELISTENER].each(function(l) {
			l.selectedTripleChanged(s,p,o);
		});
	},

	/**
	 * @public
	 * 
	 * Fires treeTabChanged event. The listener method must have the name
	 * 'treeTabChanged' with the following signature:
	 * 
	 * @param tabname
	 */
	fireTreeTabChanged : function(tabname) {
		if (!this.listeners[OB_TREETABCHANGELISTENER])
			return;
		this.listeners[OB_TREETABCHANGELISTENER].each(function(l) {
			l.treeTabChanged(tabname);
		});
	},

	/**
	 * @public
	 * 
	 * Fires filtertree event. The listener method must have the name
	 * 'filterTree' with the following signature:
	 * 
	 * @param treename
	 * @param filter
	 */
	fireFilterTree : function(tabname, filter) {
		if (!this.listeners[OB_FILTERTREE])
			return;
		this.listeners[OB_FILTERTREE].each(function(l) {
			l.treeFilterTree(tabname, filter);
		});
	},

	/**
	 * @public
	 * 
	 * Fires filterbrowsing event. The listener method must have the name
	 * 'filterBrowsing' with the following signature:
	 * 
	 * @param filter
	 * 
	 */
	fireFilterBrowsing : function(tabname, filter) {
		if (!this.listeners[OB_FILTERBROWSING])
			return;
		this.listeners[OB_FILTERBROWSING].each(function(l) {
			l.filterBrowsing(tabname, filter);
		});
	},

	/**
	 * @public
	 * 
	 * Fires reset event. The listener method must have the name 'reset' with
	 * the following signature:
	 * 
	 * @param tabname
	 * 
	 */
	fireReset : function(tabname) {
		if (!this.listeners[OB_RESET])
			return;
		this.listeners[OB_RESET].each(function(l) {
			l.reset(tabname);
		});
	},

	/**
	 * @public
	 * 
	 * Fires refresh event. The listener method must have the name 'refresh'
	 */
	fireRefresh : function() {
		if (!this.listeners[OB_REFRESHLISTENER])
			return;
		this.listeners[OB_REFRESHLISTENER].each(function(l) {
			l.refresh();
		});
	},

	fireBeforeRefresh : function() {
		if (!this.listeners[OB_BEFOREREFRESHLISTENER])
			return;
		this.listeners[OB_BEFOREREFRESHLISTENER].each(function(l) {
			l.beforeRefresh();
		});
	}
}

// create instance of event provider
window.selectionProvider = new OBEventProvider();

/**
 * Class which allows modification of wiki articles via AJAX calls.
 */
var OBArticleCreator = Class.create();
OBArticleCreator.prototype = {
	initialize : function() {
		this.pendingIndicator = new OBPendingIndicator();
	},

	/**
	 * @public
	 * 
	 * Creates an article
	 * 
	 * @param title
	 *            Title of article
	 * @param content
	 *            Text which is used when article is created
	 * @param optionalText
	 *            Text which is appended when article already exists.
	 * @param creationComment
	 *            comment
	 * @param callback
	 *            Function called when creation has finished successfully.
	 * @param node
	 *            HTML node used for displaying a pending indicator.
	 */
		createArticle : function(title, content, optionalText, creationComment,
			callback, node) {

		function ajaxResponseCreateArticle(request) {
			this.pendingIndicator.hide();
			if (request.status != 200) {
				alert(gLanguage.getMessage('ERROR_CREATING_ARTICLE'));
				return;
			}

			var answer = request.responseText;
			var regex = /(true|false),(true|denied|false),(.*)/;
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
			} else {
				if (created == 'denied') {
					var msg = gLanguage.getMessage('smw_acl_create_denied')
							.replace(/\$1/g, title);
					alert(msg);
				} else {
					alert(gLanguage.getMessage('ERROR_CREATING_ARTICLE'));
				}
			}
		}
		this.pendingIndicator.show(node);
		sajax_do_call('smwf_om_CreateArticle', [ title, wgUserName, content,
				optionalText, creationComment ], ajaxResponseCreateArticle
				.bind(this));

	},

	/**
	 * @public
	 * 
	 * Deletes an article
	 * 
	 * @param title
	 *            Title of article
	 * @param reason
	 *            reason
	 * @param callback
	 *            Function called when creation has finished successfully.
	 * @param node
	 *            HTML node used for displaying a pending indicator.
	 */
	deleteArticle : function(title, reason, callback, node) {

		function ajaxResponseDeleteArticle(request) {
			this.pendingIndicator.hide();
			if (request.status != 200) {
				alert(gLanguage.getMessage('ERROR_DELETING_ARTICLE'));
				return;
			}

			if (request.responseText.indexOf('true') != -1) {
				callback();
			} else {
				if (request.responseText.indexOf('denied') != -1) {
					var msg = gLanguage.getMessage('smw_acl_delete_denied')
							.replace(/\$1/g, title);
					alert(msg);
				} else {
					alert(gLanguage.getMessage('ERROR_DELETING_ARTICLE'));
				}
			}
		}

		this.pendingIndicator.show(node);
		sajax_do_call('smwf_om_DeleteArticle', [ title, wgUserName, reason ],
				ajaxResponseDeleteArticle.bind(this));
	},

	/**
	 * @public
	 * 
	 * Renames an article
	 * 
	 * @param oldTitle
	 *            Old title of article
	 * @param newTitle
	 *            New title of article
	 * @param reason
	 *            string
	 * @param callback
	 *            Function called when creation has finished successfully.
	 * @param node
	 *            HTML node used for displaying a pending indicator.
	 */
	renameArticle : function(oldTitle, newTitle, reason, callback, node) {

		function ajaxResponseRenameArticle(request) {
			this.pendingIndicator.hide();
			if (request.status != 200) {
				alert(gLanguage.getMessage('ERROR_RENAMING_ARTICLE'));
				return;
			}
			if (request.responseText.indexOf('true') != -1) {
				callback();
			} else {
				if (request.responseText.indexOf('denied') != -1) {
					var msg = gLanguage.getMessage('smw_acl_delete_denied')
							.replace(/\$1/g, oldTitle);
					alert(msg);
				} else {
					alert(gLanguage.getMessage('ERROR_RENAMING_ARTICLE'));
				}
			}

		}

		this.pendingIndicator.show(node);
		sajax_do_call('smwf_om_RenameArticle', [ oldTitle, newTitle, reason,
				wgUserName ], ajaxResponseRenameArticle.bind(this));
	},
	
		renameArticle1 : function(oldTitle, newTitle, reason, callback, node) {

		function ajaxResponseRenameArticle(request) {
			this.pendingIndicator.hide();
			// if (request.status != 200) {
				// alert(gLanguage.getMessage('ERROR_RENAMING_ARTICLE'));
				// return;
			// }
			if (request.responseText.indexOf('true') != -1) {
				callback();
			} else {
				// if (request.responseText.indexOf('denied') != -1) {
					// var msg = gLanguage.getMessage('smw_acl_delete_denied')
							// .replace(/\$1/g, oldTitle);
					// alert(msg);
				// } else {
					// alert(gLanguage.getMessage('ERROR_RENAMING_ARTICLE'));
				// }
			}
                 schemaEditPropertyListener.reloadProperties();
		   
		}

		this.pendingIndicator.show(node);
		sajax_do_call('smwf_om_RenameArticle', [ oldTitle, newTitle, reason,
				wgUserName ], ajaxResponseRenameArticle.bind(this));
	},
	
	
/**
	 * @public
	 * 
	 * Edit article's properties
	 * 
	 * @param oldType
	 *            Old type of article
	 * @param newType
	 *            New type of article
	 
	 * @param reason
	 *            string
	 * @param callback
	 *            Function called when creation has finished successfully.
	 * @param node
	 *            HTML node used for displaying a pending indicator.
	 */
	editArticle : function(title, newType, newCard, newRange, oldType, oldCard, oldRange, category, propertyID,reason, callback) {
    		
		 function callback() {
                    schemaEditPropertyListener.reloadProperties();		 
		 }

		sajax_do_call('smwf_om_EditProperty', [title, newType, newCard, newRange, oldType, oldCard, oldRange, category, propertyID,
				wgUserName ], callback.bind(this));
	},
/**
	 * @public
	 * 
	 * Renames a type of a property
	 * 
	 * @param oldType
	 *            Old Type of property
	 * @param newType
	 *            New type of property
	 * @param reason
	 *            string
	 * @param callback
	 *            Function called when change has finished successfully.
	 * @param node
	 *            HTML node used for displaying a pending indicator.
	 */
	renamePropertyType : function(propertyName, newType, reason, callback, node) {

		function ajaxResponseRenameTypeProperty(request) {
			this.pendingIndicator.hide();
			if (request.status != 200) {
				alert(gLanguage.getMessage('ERROR_RENAMING_ARTICLE'));
				return;
			}
			if (request.responseText.indexOf('true') != -1) {
				callback();
			} else {
				if (request.responseText.indexOf('denied') != -1) {
					var msg = gLanguage.getMessage('smw_acl_delete_denied')
							.replace(/\$1/g, oldTitle);
					alert(msg);
				} else {
					alert(gLanguage.getMessage('ERROR_RENAMING_ARTICLE'));
				}
			}

		}

		this.pendingIndicator.show(node);
		sajax_do_call('smwf_om_RenameTypeProperty', [propertyName, newType, reason,
				wgUserName ], ajaxResponseRenameTypeProperty.bind(this));
	},	
	
	moveCategory : function(draggedCategory, oldSuperCategory,
			newSuperCategory, callback, node) {
		function ajaxResponseMoveCategory(request) {
			this.pendingIndicator.hide();
			if (request.status != 200) {
				alert(gLanguage.getMessage('ERROR_MOVING_CATEGORY'));
				return;
			}
			if (request.responseText.indexOf('true') == -1) {
				alert('Some error occured on category dragging!');
				return;
			}
			if (request.responseText.indexOf('true') != -1) {
				callback();
			} else {
				alert(gLanguage.getMessage('ERROR_MOVING_CATEGORY'));
			}

		}

		this.pendingIndicator.show(node);
		sajax_do_call('smwf_om_MoveCategory', [ draggedCategory,
				oldSuperCategory, newSuperCategory ], ajaxResponseMoveCategory
				.bind(this));
	},

	moveProperty : function(draggedProperty, oldSuperProperty,
			newSuperProperty, callback, node) {
		function ajaxResponseMoveProperty(request) {
			this.pendingIndicator.hide();
			if (request.status != 200) {
				alert(gLanguage.getMessage('ERROR_MOVING_PROPERTY'));
				return;
			}
			if (request.responseText.indexOf('true') == -1) {
				alert('Some error occured on property dragging!');
				return;
			}
			if (request.responseText.indexOf('true') != -1) {
				callback();
			} else {
				alert(gLanguage.getMessage('ERROR_MOVING_PROPERTY'));
			}

		}

		this.pendingIndicator.show(node);
		sajax_do_call('smwf_om_MoveProperty', [ draggedProperty,
				oldSuperProperty, newSuperProperty ], ajaxResponseMoveProperty
				.bind(this));
	}

}
var articleCreator = new OBArticleCreator();

/**
 * Modifies the wiki ontology and internal OB model.
 */
var OBOntologyModifier = Class.create();
OBOntologyModifier.prototype = {
	initialize : function() {
		this.date = new Date();
		this.count = 0;
	},

	/**
	 * @public
	 * 
	 * Adds a new subcategory
	 * 
	 * @param subCategoryTitle
	 *            Title of new subcategory (must not exist!)
	 * @param superCategoryTitle
	 *            Title of supercategory
	 * @param superCategoryID
	 *            ID of supercategory in OB data model (XML)
	 */
	addSubcategory : function(subCategoryTitle, superCategoryTitle,
			superCategoryID) {
		function callback() {
			var subCategoryXML = GeneralXMLTools.createDocumentFromString(this
					.createCategoryNode(subCategoryTitle));
			this.insertCategoryNode(superCategoryID, subCategoryXML);
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedCategoryTree,
					$('categoryTree'), true);

			selectionProvider.fireSelectionChanged(superCategoryID,
					superCategoryTitle, SMW_CATEGORY_NS, $(superCategoryID))
			selectionProvider.fireRefresh();
		}
		articleCreator.createArticle(gLanguage.getMessage('CATEGORY_NS')
				+ subCategoryTitle, "[[" + gLanguage.getMessage('CATEGORY_NS')
				+ superCategoryTitle + "]]", '', gLanguage
				.getMessage('CREATE_SUB_CATEGORY'), callback.bind(this),
				$(superCategoryID));
	},

	/**
	 * @public
	 * 
	 * Adds a new category as sibling of another.
	 * 
	 * @param newCategoryTitle
	 *            Title of new category (must not exist!)
	 * @param siblingCategoryTitle
	 *            Title of sibling
	 * @param sibligCategoryID
	 *            ID of siblig category in OB data model (XML)
	 */
	addSubcategoryOnSameLevel : function(newCategoryTitle,
			siblingCategoryTitle, sibligCategoryID) {
		function callback() {
			var newCategoryXML = GeneralXMLTools.createDocumentFromString(this
					.createCategoryNode(newCategoryTitle));
			var superCategoryID = GeneralXMLTools.getNodeById(
					dataAccess.OB_cachedCategoryTree, sibligCategoryID).parentNode
					.getAttribute('id');
			this.insertCategoryNode(superCategoryID, newCategoryXML);
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedCategoryTree,
					$('categoryTree'), true);

			selectionProvider.fireSelectionChanged(sibligCategoryID,
					siblingCategoryTitle, SMW_CATEGORY_NS, $(sibligCategoryID))
			selectionProvider.fireRefresh();
		}
		var superCategoryTitle = GeneralXMLTools.getNodeById(
				dataAccess.OB_cachedCategoryTree, sibligCategoryID).parentNode
				.getAttribute('title');
		var content = superCategoryTitle != null ? "[["
				+ gLanguage.getMessage('CATEGORY_NS') + superCategoryTitle
				+ "]]" : "";
		articleCreator.createArticle(gLanguage.getMessage('CATEGORY_NS')
				+ newCategoryTitle, content, '', gLanguage
				.getMessage('CREATE_SUB_CATEGORY'), callback.bind(this),
				$(sibligCategoryID));
	},

	/**
	 * @public
	 * 
	 * Renames a category.
	 * 
	 * @param newCategoryTitle
	 *            New category title
	 * @param categoryTitle
	 *            Old category title
	 * @param categoryID
	 *            ID of category in OB data model (XML)
	 */
	renameCategory : function(newCategoryTitle, categoryTitle, categoryID) {
		function callback() {
			this.renameCategoryNode(categoryID, newCategoryTitle);
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedCategoryTree,
					$('categoryTree'), true);

			selectionProvider.fireSelectionChanged(categoryID,
					newCategoryTitle, SMW_CATEGORY_NS, $(categoryID))
			selectionProvider.fireRefresh();
		}
		articleCreator.renameArticle(gLanguage.getMessage('CATEGORY_NS')
				+ categoryTitle, gLanguage.getMessage('CATEGORY_NS')
				+ newCategoryTitle, "OB", callback.bind(this), $(categoryID));
	},

	/**
	 * Move category so that draggedCategoryID is a new subcategory of
	 * droppedCategoryID
	 * 
	 * @param draggedCategoryID
	 *            ID of category which is moved.
	 * @param droppedCategoryID
	 *            ID of new supercategory of draggedCategory.
	 */
	moveCategory : function(draggedCategoryID, droppedCategoryID) {

		var from_cache = GeneralXMLTools.getNodeById(
				dataAccess.OB_cachedCategoryTree, draggedCategoryID);
		// categoryTreeSwitch allows dropping on root level
		var to_cache = droppedCategoryID == 'categoryTreeSwitch' ? dataAccess.OB_cachedCategoryTree.documentElement
				: GeneralXMLTools.getNodeById(dataAccess.OB_cachedCategoryTree,
						droppedCategoryID);

		var from = GeneralXMLTools.getNodeById(
				dataAccess.OB_currentlyDisplayedTree, draggedCategoryID);
		var to = droppedCategoryID == 'categoryTreeSwitch' ? dataAccess.OB_currentlyDisplayedTree.documentElement
				: GeneralXMLTools
						.getNodeById(dataAccess.OB_currentlyDisplayedTree,
								droppedCategoryID);

		var draggedCategory = from_cache.getAttribute('title');
		var oldSuperCategory = from_cache.parentNode.getAttribute('title');
		var newSuperCategory = to_cache.getAttribute('title');

		function callback() {
			// only move subtree, if it has already been requested.
			// If expanded is true, it must have been requested. Otherwise it
			// may have been requested but is now collapsed. Then it contains
			// child elements
			if (to_cache.getAttribute('expanded') == 'true'
					|| GeneralXMLTools.hasChildNodesWithTag(to_cache,
							'conceptTreeElement')) {
				to_cache.removeAttribute("isLeaf");
				to.removeAttribute("isLeaf");
				GeneralXMLTools.importNode(to_cache, from_cache, true);
				GeneralXMLTools.importNode(to, from, true);
			}

			from.parentNode.removeChild(from);
			from_cache.parentNode.removeChild(from_cache);

			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedCategoryTree,
					$('categoryTree'), true);

			selectionProvider.fireSelectionChanged(null, null, SMW_CATEGORY_NS,
					null);
			selectionProvider.fireRefresh();
		}
		articleCreator.moveCategory(draggedCategory, oldSuperCategory,
				newSuperCategory, callback.bind(this), $('categoryTree'));
	},

	/**
	 * Move property so that draggedPropertyID is a new subproperty of
	 * droppedPropertyID
	 * 
	 * @param draggedPropertyID
	 *            ID of property which is moved.
	 * @param droppedPropertyID
	 *            ID of new superproperty of draggedProperty.
	 */
	moveProperty : function(draggedPropertyID, droppedPropertyID) {

		var from_cache = GeneralXMLTools.getNodeById(
				dataAccess.OB_cachedPropertyTree, draggedPropertyID);
		var to_cache = droppedPropertyID == 'propertyTreeSwitch' ? dataAccess.OB_cachedPropertyTree.documentElement
				: GeneralXMLTools.getNodeById(dataAccess.OB_cachedPropertyTree,
						droppedPropertyID);

		var from = GeneralXMLTools.getNodeById(
				dataAccess.OB_currentlyDisplayedTree, draggedPropertyID);
		var to = droppedPropertyID == 'propertyTreeSwitch' ? dataAccess.OB_currentlyDisplayedTree.documentElement
				: GeneralXMLTools
						.getNodeById(dataAccess.OB_currentlyDisplayedTree,
								droppedPropertyID);

		var draggedProperty = from_cache.getAttribute('title');
		var oldSuperProperty = from_cache.parentNode.getAttribute('title');
		var newSuperProperty = to_cache.getAttribute('title');

		function callback() {

			// make target property no non-leaf node
			to_cache.removeAttribute("isLeaf");
			to.removeAttribute("isLeaf");

			// import property subtree
			GeneralXMLTools.importNode(to_cache, from_cache, true);
			GeneralXMLTools.importNode(to, from, true);

			// remove subtree from source
			var fromParent = from.parentNode;
			fromParent.removeChild(from);
			var from_cacheParent = from_cache.parentNode;
			from_cacheParent.removeChild(from_cache);

			// make source property parent to leaf if necessary
			if (!GeneralXMLTools.hasChildNodesWithTag(fromParent,
					'propertyTreeElement')) {
				fromParent.setAttribute("isLeaf", "true");
				from_cacheParent.setAttribute("isLeaf", "true");
			}

			// refresh view
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedPropertyTree,
					$('propertyTree'), true);

			selectionProvider.fireSelectionChanged(null, null, SMW_PROPERTY_NS,
					null);
			selectionProvider.fireRefresh();
		}

		articleCreator.moveProperty(draggedProperty, oldSuperProperty,
				newSuperProperty, callback.bind(this), $('propertyTree'));
	},

	/**
	 * @public
	 * 
	 * Adds a new subproperty
	 * 
	 * @param subPropertyTitle
	 *            Title of new subproperty (must not exist!)
	 * @param superPropertyTitle
	 *            Title of superproperty
	 * @param superPropertyID
	 *            ID of superproperty in OB data model (XML)
	 */
	addSubproperty : function(subPropertyTitle, superPropertyTitle,
			superPropertyID) {
		function callback() {
			var subPropertyXML = GeneralXMLTools.createDocumentFromString(this
					.createPropertyNode(subPropertyTitle));
			this.insertPropertyNode(superPropertyID, subPropertyXML);
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedPropertyTree,
					$('propertyTree'), true);

			selectionProvider.fireSelectionChanged(superPropertyID,
					superPropertyTitle, SMW_PROPERTY_NS, $(superPropertyID))
			selectionProvider.fireRefresh();
		}
		articleCreator.createArticle(gLanguage.getMessage('PROPERTY_NS')
				+ subPropertyTitle, '', "\n[[_SUBP::"
				+ gLanguage.getMessage('PROPERTY_NS') + superPropertyTitle
				+ "]]", gLanguage.getMessage('CREATE_SUB_PROPERTY'), callback
				.bind(this), $(superPropertyID));
	},

	/**
	 * @public
	 * 
	 * Adds a new property as sibling of another.
	 * 
	 * @param newPropertyTitle
	 *            Title of new property (must not exist!)
	 * @param siblingPropertyTitle
	 *            Title of sibling
	 * @param sibligPropertyID
	 *            ID of siblig property in OB data model (XML)
	 */
	addSubpropertyOnSameLevel : function(newPropertyTitle,
			siblingPropertyTitle, sibligPropertyID) {
		function callback() {
			var subPropertyXML = GeneralXMLTools.createDocumentFromString(this
					.createPropertyNode(newPropertyTitle));
			var superPropertyID = GeneralXMLTools.getNodeById(
					dataAccess.OB_cachedPropertyTree, sibligPropertyID).parentNode
					.getAttribute('id');
			this.insertPropertyNode(superPropertyID, subPropertyXML);
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedPropertyTree,
					$('propertyTree'), true);

			selectionProvider.fireSelectionChanged(sibligPropertyID,
					siblingPropertyTitle, SMW_PROPERTY_NS, $(sibligPropertyID))
			selectionProvider.fireRefresh();
		}

		var superPropertyTitle = GeneralXMLTools.getNodeById(
				dataAccess.OB_cachedPropertyTree, sibligPropertyID).parentNode
				.getAttribute('title');
		var content = superPropertyTitle != null ? "\n[[_SUBP::"
				+ gLanguage.getMessage('PROPERTY_NS') + superPropertyTitle
				+ "]]" : "";
		articleCreator.createArticle(gLanguage.getMessage('PROPERTY_NS')
				+ newPropertyTitle, '', content, gLanguage
				.getMessage('CREATE_SUB_PROPERTY'), callback.bind(this),
				$(sibligPropertyID));
	},

	/**
	 * @public
	 * 
	 * Renames a property.
	 * 
	 * @param newPropertyTitle
	 *            New property title
	 * @param oldPropertyTitle
	 *            Old property title
	 * @param propertyID
	 *            ID of property in OB data model (XML)
	 */
	renameProperty : function(newPropertyTitle, oldPropertyTitle, propertyID) {
		function callback() {
			this.renamePropertyNode(propertyID, newPropertyTitle);
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedPropertyTree,
					$('propertyTree'), true);

			selectionProvider.fireSelectionChanged(propertyID,
					newPropertyTitle, SMW_PROPERTY_NS, $(propertyID))
			selectionProvider.fireRefresh();
		}
		articleCreator.renameArticle(gLanguage.getMessage('PROPERTY_NS')
				+ oldPropertyTitle, gLanguage.getMessage('PROPERTY_NS')
				+ newPropertyTitle, "OB", callback.bind(this), $(propertyID));
	},
	/**
	 * @public
	 * 
	 * Renames a property.
	 * 
	 * @param newPropertyTitle
	 *            New property title
	 * @param oldPropertyTitle
	 *            Old property title
	 * @param propertyID
	 *            ID of property in OB data model (XML)
	 */
	renameProperty1 : function(newPropertyTitle, oldPropertyTitle, propertyID) {
		function callback() {
			// this.renamePropertyNode(propertyID, newPropertyTitle);
			// selectionProvider.fireBeforeRefresh();
			// transformer.transformXMLToHTML(dataAccess.OB_cachedPropertyTree,
					// $('propertyTree'), true);

			// selectionProvider.fireSelectionChanged(propertyID,
					// newPropertyTitle, SMW_PROPERTY_NS, $(propertyID))
			// selectionProvider.fireRefresh();
		}
		articleCreator.renameArticle1(gLanguage.getMessage('PROPERTY_NS')
				+ oldPropertyTitle, gLanguage.getMessage('PROPERTY_NS')
				+ newPropertyTitle, "OB", callback.bind(this), $(propertyID));
	},
	
	
	/**
	 * @public
	 * 
	 * Edit property's properties.
	 * 
	 * @param newType, oldType
	 *            New property type, old type
	 * @param propertyTitle
	 *            property title
	 * @param newCard, oldCard
	 *            New cardinality, old cardinality
	 * @param newRange, oldRange
	 *            new Range, new Range
	 * @param propertyID
	 *            ID of property in OB data model (XML)
	 */
	editProperties : function(propertyTitle, newType, newCard, newRange, oldType, oldCard, oldRange, category, propertyID) {
		function callback() {
		
		}
		articleCreator.editArticle(gLanguage.getMessage('PROPERTY_NS')
				+ propertyTitle, newType, newCard, newRange, oldType, oldCard, oldRange, category, propertyID, "OB", callback.bind(this));		
	},

	/**
	 * @public
	 * 
	 * Adds a new property with schema information.
	 * 
	 * @param propertyTitle
	 *            Title of property
	 * @param minCard
	 *            Minimum cardinality
	 * @param maxCard
	 *            Maximum cardinality
	 * @param rangeOrTypes
	 *            Array of range categories or types.
	 * @param builtinTypes
	 *            Array of all existing builtin types.
	 * @param domainCategoryTitle
	 *            Title of domain category
	 * @param domainCategoryID
	 *            ID of domain category in OB data model (XML)
	 */
	addSchemaProperty : function(propertyTitle, minCard, maxCard, rangeOrTypes,
			builtinTypes, domainCategoryTitle, domainCategoryID) {
		function callback() {
			var newPropertyXML = GeneralXMLTools.createDocumentFromString(this
					.createSchemaProperty(propertyTitle, minCard, maxCard,
							rangeOrTypes, builtinTypes, domainCategoryTitle,
							domainCategoryID));
			dataAccess.OB_cachedProperties.documentElement
					.removeAttribute('isEmpty');
			dataAccess.OB_cachedProperties.documentElement
					.removeAttribute('textToDisplay');
			GeneralXMLTools.importNode(
					dataAccess.OB_cachedProperties.documentElement,
					newPropertyXML.documentElement, true);
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedProperties,
					$('relattributes'), true);

			selectionProvider.fireRefresh();
		}

		var content = maxCard != '' ? "\n[[SMW_SSP_HAS_MAX_CARD::" + maxCard
				+ "]]" : "";
		content += minCard != '' ? "\n[[SMW_SSP_HAS_MIN_CARD::" + minCard
				+ "]]" : "";

		var rangeTypeStr = "";
		var rangeCategories = new Array();
		for ( var i = 0, n = rangeOrTypes.length; i < n; i++) {
			if (builtinTypes.indexOf(rangeOrTypes[i]) != -1) {
				// is type
				rangeTypeStr += gLanguage.getMessage('TYPE_NS')
						+ rangeOrTypes[i] + (i == n - 1 ? "" : ";");
			} else {
				rangeTypeStr += gLanguage.getMessage('TYPE_PAGE')
						+ (i == n - 1 ? "" : ";");
				rangeCategories.push(rangeOrTypes[i]);
			}
		}
		if (rangeOrTypes.length > 1) {
			content += "\n[[_TYPE::_rec]]";
			content += "\n[[_LIST::" + rangeTypeStr + "]]";
		} else {
			content += "\n[[_TYPE::" + rangeTypeStr + "]]";
		}
		rangeCategories.each(function(c) {
			content += "\n[[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT::"
					+ gLanguage.getMessage('CATEGORY_NS') + domainCategoryTitle
					+ "; " + gLanguage.getMessage('CATEGORY_NS') + c + "]]"
		});
		if (rangeCategories.length == 0) {
			content += "\n[[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT::"
					+ gLanguage.getMessage('CATEGORY_NS') + domainCategoryTitle
					+ "]]";
		}
		articleCreator.createArticle(gLanguage.getMessage('PROPERTY_NS')
				+ propertyTitle, '', content, gLanguage
				.getMessage('CREATE_PROPERTY'), callback.bind(this),
				$(domainCategoryID));
	},
	

	/**
	 * @public
	 * 
	 * Renames an instance.
	 * 
	 * @param newInstanceTitle
	 * @param oldInstanceTitle
	 * @param instanceID
	 *            ID of instance node in OB data model (XML)
	 */
	renameInstance : function(newInstanceTitle, oldInstanceTitle, instanceID) {
		function callback() {
			this.renameInstanceNode(newInstanceTitle, instanceID);
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedInstances,
					$('instanceList'), true);

			selectionProvider.fireSelectionChanged(instanceID,
					newInstanceTitle, SMW_INSTANCE_NS, $(instanceID))
			selectionProvider.fireRefresh();
		}
		articleCreator.renameArticle(oldInstanceTitle, newInstanceTitle, "OB",
				callback.bind(this), $(instanceID));
	},

	/**
	 * @public
	 * 
	 * Deletes an instance.
	 * 
	 * @param instanceTitle
	 * @param instanceID
	 *            ID of instance node in OB data model (XML)
	 */
	deleteInstance : function(instanceTitle, instanceID) {
		function callback() {
			this.deleteInstanceNode(instanceID);
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedInstances,
					$('instanceList'), true);

			selectionProvider.fireSelectionChanged(null, null, SMW_INSTANCE_NS,
					null)
			selectionProvider.fireRefresh();
		}
		articleCreator.deleteArticle(instanceTitle, "OB", callback.bind(this),
				$(instanceID));
	},
	
	/**
	 * @public
	 * 
	 * Creates an instance.
	 * 
	 * @param instanceTitle
	 * @param instanceID
	 *            ID of instance node in OB data model (XML)
	 */
	createInstance : function(instanceTitle, categoryTitle, categoryID) {
		function callback() {
			this.addInstanceNode(instanceTitle, categoryTitle);
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedInstances,
					$('instanceList'), true);

			selectionProvider.fireSelectionChanged(null, null, SMW_INSTANCE_NS,
					null)
			selectionProvider.fireRefresh();
		}
		articleCreator.createArticle(instanceTitle, "[[" + gLanguage.getMessage('CATEGORY_NS')
				+ categoryTitle + "]]", '', gLanguage
				.getMessage('CREATE_OB_ARTICLE'), callback.bind(this),
				$(categoryID));
	},

	/**
	 * @private
	 * 
	 * Creates a conceptTreeElement for internal OB data model (XML)
	 */
	createCategoryNode : function(subCategoryTitle) {
		this.count++;
		var categoryTitle_esc = encodeURIComponent(subCategoryTitle);
		categoryTitle_esc = categoryTitle_esc.replace(/%2F/g, "/");
		return '<conceptTreeElement title_url="' + categoryTitle_esc
				+ '" title="' + subCategoryTitle + '" id="ID_'
				+ (this.date.getTime() + this.count)
				+ '" isLeaf="true" expanded="true"/>';
	},
	
	/**
	 * @private
	 * 
	 * Creates a conceptTreeElement for internal OB data model (XML)
	 */
	createInstanceNode : function(instanceTitle, categoryTitle) {
		this.count++;
		var instanceTitle_esc = encodeURIComponent(instanceTitle);
		instanceTitle_esc = instanceTitle_esc.replace(/%2F/g, "/");
		var categoryTitle_esc = encodeURIComponent(categoryTitle);
		categoryTitle_esc = categoryTitle_esc.replace(/%2F/g, "/");
		var localURL = GeneralTools.makeWikiURL(instanceTitle);
		var uri = GeneralTools.makeTSCURI(instanceTitle);
		var uri_att = uri != false ? 'uri="'+uri+'"' : '';
		if (uri != false) localURL += "?"+'uri='+uri;
		return '<instance '+uri_att+' title_url="' + instanceTitle_esc
				+ '" namespace= "" localurl="'+localURL+'" title="' + instanceTitle
				+ '"  id="ID_'
				+ (this.date.getTime() + this.count)
				+ '"  />';
	},

	/**
	 * @private
	 * 
	 * Creates a propertyTreeElement for internal OB data model (XML)
	 */
	createPropertyNode : function(subPropertyTitle) {
		this.count++;
		var propertyTitle_esc = encodeURIComponent(subPropertyTitle);
		propertyTitle_esc = propertyTitle_esc.replace(/%2F/g, "/");
		return '<propertyTreeElement title_url="' + propertyTitle_esc
				+ '" title="' + subPropertyTitle + '" id="ID_'
				+ (this.date.getTime() + this.count)
				+ '" isLeaf="true" expanded="true"/>';
	},

	/**
	 * @private
	 * 
	 * Creates a property element for internal OB data model (XML)
	 */
	createSchemaProperty : function(propertyTitle, minCard, maxCard,
			typeRanges, builtinTypes, selectedTitle, selectedID) {
		this.count++;
		rangeTypes = "";
		for ( var i = 0, n = typeRanges.length; i < n; i++) {
			if (builtinTypes.indexOf(typeRanges[i]) != -1) {
				// is type
				rangeTypes += '<rangeType>' + typeRanges[i] + '</rangeType>';
			} else {
				rangeTypes += '<rangeType>' + gLanguage.getMessage('TYPE_PAGE') + '</rangeType>';
			}
		}
		minCard = minCard == '' ? '0' : minCard;
		maxCard = maxCard == '' ? '*' : maxCard;
		var propertyTitle_esc = encodeURIComponent(propertyTitle);
		propertyTitle_esc = propertyTitle_esc.replace(/%2F/g, "/");

		return '<property title_url="' + propertyTitle_esc + '" title="'
				+ propertyTitle + '" minCard="' + minCard + '" maxCard="'
				+ maxCard + '">' + rangeTypes + '</property>';
	},

	/**
	 * @private
	 * 
	 * Renames an instance node in internal OB data model (XML)
	 */
	renameInstanceNode : function(newInstanceTitle, instanceID) {
		var instanceNode = GeneralXMLTools.getNodeById(
				dataAccess.OB_cachedInstances, instanceID);
		instanceNode.removeAttribute("title");
		instanceNode.removeAttribute("namespace");
		var newTitleAndNamespace = newInstanceTitle.split(":");
		if (newTitleAndNamespace.length == 2) {
			instanceNode.setAttribute("title", newTitleAndNamespace[1]);
			instanceNode.setAttribute("namespace", newTitleAndNamespace[0]);
		} else {
			instanceNode.setAttribute("title", newInstanceTitle);
			instanceNode.setAttribute("namespace", "");
		}
	},

	/**
	 * @private
	 * 
	 * Deletes an instance node in internal OB data model (XML)
	 */
	deleteInstanceNode : function(instanceID) {
		var instanceNode = GeneralXMLTools.getNodeById(
				dataAccess.OB_cachedInstances, instanceID);
		instanceNode.parentNode.removeChild(instanceNode);
	},
	
	/**
	 * @private
	 * 
	 * Adds an instance node in internal OB data model (XML)
	 */
	addInstanceNode : function(instanceTitle, categoryTitle) {
		
		var newInstanceXML = GeneralXMLTools.createDocumentFromString(this
				.createInstanceNode(instanceTitle, categoryTitle));
		
		// just in case the instance list is currently empty
		dataAccess.OB_cachedInstances.documentElement.removeAttribute("textToDisplay");
		dataAccess.OB_cachedInstances.documentElement.removeAttribute("isEmpty");
		
		// insert in cache and displayed tree
		GeneralXMLTools.importNode(dataAccess.OB_cachedInstances.documentElement,
				newInstanceXML.documentElement, true);
		
	},

	/**
	 * @private
	 * 
	 * Inserts a category node in internal OB data model (XML) as subnode of
	 * another category node.
	 * 
	 */
	insertCategoryNode : function(superCategoryID, subCategoryXML) {
		var superCategoryNodeCached = superCategoryID != null ? GeneralXMLTools
				.getNodeById(dataAccess.OB_cachedCategoryTree, superCategoryID)
				: dataAccess.OB_cachedCategoryTree.documentElement;
		var superCategoryNodeDisplayed = superCategoryID != null ? GeneralXMLTools
				.getNodeById(dataAccess.OB_currentlyDisplayedTree,
						superCategoryID)
				: dataAccess.OB_currentlyDisplayedTree.documentElement;

		// make sure that supercategory is no leaf anymore and set it to
		// expanded now.
		superCategoryNodeCached.removeAttribute("isLeaf");
		superCategoryNodeCached.setAttribute("expanded", "true");
		superCategoryNodeDisplayed.removeAttribute("isLeaf");
		superCategoryNodeDisplayed.setAttribute("expanded", "true");

		// insert in cache and displayed tree
		GeneralXMLTools.importNode(superCategoryNodeCached,
				subCategoryXML.documentElement, true);
		GeneralXMLTools.importNode(superCategoryNodeDisplayed,
				subCategoryXML.documentElement, true);
	},

	/**
	 * @private
	 * 
	 * Inserts a property node in internal OB data model (XML) as subnode of
	 * another category node.
	 * 
	 */
	insertPropertyNode : function(superpropertyID, subpropertyXML) {
		var superpropertyNodeCached = superpropertyID != null ? GeneralXMLTools
				.getNodeById(dataAccess.OB_cachedPropertyTree, superpropertyID)
				: dataAccess.OB_cachedPropertyTree.documentElement;
		var superpropertyNodeDisplayed = superpropertyID != null ? GeneralXMLTools
				.getNodeById(dataAccess.OB_currentlyDisplayedTree,
						superpropertyID)
				: dataAccess.OB_currentlyDisplayedTree.documentElement;

		// make sure that superproperty is no leaf anymore and set it to
		// expanded now.
		superpropertyNodeCached.removeAttribute("isLeaf");
		superpropertyNodeCached.setAttribute("expanded", "true");
		superpropertyNodeDisplayed.removeAttribute("isLeaf");
		superpropertyNodeDisplayed.setAttribute("expanded", "true");

		// insert in cache and displayed tree
		GeneralXMLTools.importNode(superpropertyNodeCached,
				subpropertyXML.documentElement, true);
		GeneralXMLTools.importNode(superpropertyNodeDisplayed,
				subpropertyXML.documentElement, true);
	},

	/**
	 * @private
	 * 
	 * Renames a category node in internal OB data model (XML)
	 * 
	 */
	renameCategoryNode : function(categoryID, newCategoryTitle) {
		var categoryNodeCached = GeneralXMLTools.getNodeById(
				dataAccess.OB_cachedCategoryTree, categoryID);
		var categoryNodeDisplayed = GeneralXMLTools.getNodeById(
				dataAccess.OB_currentlyDisplayedTree, categoryID);
		categoryNodeCached.removeAttribute("title");
		categoryNodeDisplayed.removeAttribute("title");
		categoryNodeCached.setAttribute("title", newCategoryTitle); // TODO:
																	// escape
		categoryNodeDisplayed.setAttribute("title", newCategoryTitle);

	},

	/**
	 * @private
	 * 
	 * Renames a property node in internal OB data model (XML)
	 * 
	 */
	renamePropertyNode : function(propertyID, newPropertyTitle) {
		var propertyNodeCached = GeneralXMLTools.getNodeById(
				dataAccess.OB_cachedPropertyTree, propertyID);
		var propertyNodeDisplayed = GeneralXMLTools.getNodeById(
				dataAccess.OB_currentlyDisplayedTree, propertyID);
		propertyNodeCached.removeAttribute("title");
		propertyNodeDisplayed.removeAttribute("title");
		propertyNodeCached.setAttribute("title", newPropertyTitle); // TODO:
																	// escape
		propertyNodeDisplayed.setAttribute("title", newPropertyTitle);
	},
	
	/**
	 * @private
	 * 
	 * Renames a property's type node in internal OB data model (XML)
	 * 
	 */
	renamePropertyTypeNode : function(propertyID, newPropertyType) {
		var propertyNodeCached = GeneralXMLTools.getNodeById(
				dataAccess.OB_cachedPropertyTree, propertyID);
		var propertyNodeDisplayed = GeneralXMLTools.getNodeById(
				dataAccess.OB_currentlyDisplayedTree, propertyID);
		propertyNodeCached.removeAttribute("type");
		propertyNodeDisplayed.removeAttribute("type");
		propertyNodeCached.setAttribute("type", newPropertyType); // TODO:
																	// escape
		propertyNodeDisplayed.setAttribute("type", newPropertyType);
	}
}

// global object for ontology modification
var ontologyTools = new OBOntologyModifier();

/**
 * Input field validator. Provides an automatic validation triggering after the
 * user finished typing.
 */
var OBInputFieldValidator = Class.create();
OBInputFieldValidator.prototype = {

	/**
	 * @public Constructor
	 * 
	 * @param id
	 *            ID of INPUT field
	 * @param isValid
	 *            Flag if the input field is initially valid.
	 * @param control
	 *            Control object (derived from OBOntologySubMenu)
	 * @param validate_fnc
	 *            Validation function
	 */
	initialize : function(id, isValid, control, validate_fnc) {
		this.id = id;
		this.validate_fnc = validate_fnc;
		this.control = control;

		this.keyListener = null;
		this.blurListener = null;
		this.istyping = false;
		this.timerRegistered = false;

		this.isValid = isValid;
		this.lastValidation = null;

		if ($(this.id) != null)
			this.registerListeners();
	},

	OBInputFieldValidator : function(id, isValid, control, validate_fnc) {
		this.id = id;
		this.validate_fnc = validate_fnc;
		this.control = control;

		this.keyListener = null;
		this.blurListener = null;
		this.istyping = false;
		this.timerRegistered = false;

		this.isValid = isValid;
		this.lastValidation = null;

		if ($(this.id) != null)
			this.registerListeners();
	},

	/**
	 * @private Registers some listeners on the INPUT field.
	 */
	registerListeners : function() {
		var e = $(this.id);
		this.keyListener = this.onKeyEvent.bindAsEventListener(this);
		this.blurListener = this.onBlurEvent.bindAsEventListener(this);
		Event.observe(e, "input", this.keyListener);
		Event.observe(e, "keyup", this.keyListener);
		Event.observe(e, "keydown", this.keyListener);
		Event.observe(e, "blur", this.blurListener);
	},

	/**
	 * @private De-registers the listeners.
	 */
	deregisterListeners : function() {
		var e = $(this.id);
		if (e == null)
			return;
		Event.stopObserving(e, "input", this.keyListener);
		Event.stopObserving(e, "keyup", this.keyListener);
		Event.stopObserving(e, "keydown", this.keyListener);
		Event.stopObserving(e, "blur", this.blurListener);
	},

	/**
	 * @private
	 * 
	 * Triggers a timer which starts validation when a certain time has elapsed
	 * after the last key was pressed by the user.
	 */
	onKeyEvent : function(event) {

		this.istyping = true;

		/*
		 * if (event.keyCode == 27) { // ESCAPE was pressed, so close submenu.
		 * this.control.cancel(); return; }
		 */

		if (event.keyCode == 9 || event.ctrlKey || event.altKey
				|| event.keyCode == 18 || event.keyCode == 17) {
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

		if (!this.timerRegistered) {
			this.control.reset(this.id);
			this.timedCallback(this.validate.bind(this));
			this.timerRegistered = true;
		}

	},

	/**
	 * Validate on blur if content has changed since last validation
	 */
	onBlurEvent : function(event) {
		if (this.lastValidation != null && this.lastValidation != $F(this.id)) {
			this.validate();
		}
	},
	/**
	 * @private
	 * 
	 * Callback which calls itsself after timeout, if typing continues.
	 */
	timedCallback : function(fnc) {
		if (this.istyping) {
			this.istyping = false;
			var cb = this.timedCallback.bind(this, fnc);
			setTimeout(cb, 1000);
		} else {
			fnc(this.id);
			this.timerRegistered = false;
			this.istyping = false;
		}
	},

	/**
	 * @private
	 * 
	 * Calls validation function and control.enable, if validation has a defined
	 * value (true/false)
	 */
	validate : function() {
		this.lastValidation = $F(this.id);
		this.isValid = this.validate_fnc(this.id);
		if (this.isValid !== null) {
			this.control.enable(this.isValid, this.id);
		}
	}

}

/**
 * Validates changed title.
 * 
 */
var OBChangedTitleValidator = Class.create();
OBChangedTitleValidator.prototype = Object.extend(new OBInputFieldValidator(), {

/**
	 * @public Constructor
	 * 
	 * @param id
	 *            ID of INPUT element
	 * @param ns
	 *            namespace for which existance is tested.
	 * @param mustExist
	 *            If true, existance is validated. Otherwise non-existance.
	 * @param control
	 *            Control object (derived from OBOntologySubMenu)
	 */
	initialize : function(id, ns, mustExist, control) {
		this.OBInputFieldValidator(id, false, control,
				this._checkIfArticleExists.bind(this));
		this.ns = ns;
		this.mustExist = mustExist;
		this.pendingElement = new OBPendingIndicator();
		this.hintDIV = document.createElement("div");
		$(id).parentNode.appendChild(this.hintDIV);
	},

	/**
	 * @private
	 * 
	 * Checks if article exists and enables/disables command.
	 */
	_checkIfArticleExists : function(id) {
		function ajaxResponseExistsArticle(id, request) {
			this.pendingElement.hide();
			var answer = request.responseText;
			var regex = /(true|false)/;
			var parts = answer.match(regex);
	
			this.hintDIV.innerHTML = "";
			isChanged = false;
			if(cardChanged == true || typeChanged == true || rangeChanged == true){
			  isChanged = true;
			}
	// check if title got empty in the meantime
	if ($F(id) == '') {
		this.control.enable(false, id);
		return;
	}
	if (parts == null) {
		// call fails for some reason. Do nothing!
		this.isValid = false;
		this.control.enable(false, id);
		return;
	} else if (parts[0] == 'true') {
		if(titleChanged == true){
		  this.isValid = false;
		  this.control.enable(false, id);
		  this.hintDIV.innerHTML = gLanguage.getMessage('OB_TITLE_EXISTS');		
		}  
        if(titleChanged == false){		
		 if(isChanged == true){
		    this.isValid = true;
		    this.control.enable(true, id);
		   }
		}
		 return;
	} else {
		 this.isValid = true;
		 this.control.enable(true, id);
	}
}
;

var pageName = $F(this.id);
if (pageName == '') {
	this.control.enable(false, this.id);
	return;
}
this.pendingElement.show(this.id)
var pageNameWithNS = this.ns == '' ? pageName : this.ns + ":" + pageName;
sajax_do_call('smwf_om_ExistsArticleIgnoreRedirect', [ pageNameWithNS ],
		ajaxResponseExistsArticle.bind(this, this.id));
return null;
}


});

/**
 * Validates if a title exists (or does not exist).
 * 
 */
var OBInputTitleValidator = Class.create();
OBInputTitleValidator.prototype = Object.extend(new OBInputFieldValidator(), {

	/**
	 * @public Constructor
	 * 
	 * @param id
	 *            ID of INPUT element
	 * @param ns
	 *            namespace for which existance is tested.
	 * @param mustExist
	 *            If true, existance is validated. Otherwise non-existance.
	 * @param control
	 *            Control object (derived from OBOntologySubMenu)
	 */
	initialize : function(id, ns, mustExist, control) {
		this.OBInputFieldValidator(id, false, control,
				this._checkIfArticleExists.bind(this));
		this.ns = ns;
		this.mustExist = mustExist;
		this.pendingElement = new OBPendingIndicator();
		this.hintDIV = document.createElement("div");
		$(id).parentNode.appendChild(this.hintDIV);
	},

	/**
	 * @private
	 * 
	 * Checks if article exists and enables/disables command.
	 */
	_checkIfArticleExists : function(id) {
		function ajaxResponseExistsArticle(id, request) {
			this.pendingElement.hide();
			var answer = request.responseText;
			var regex = /(true|false)/;
			var parts = answer.match(regex);
	        this.titleChanged = true;
			this.hintDIV.innerHTML = "";
	// check if title got empty in the meantime
	if ($F(id) == '') {
		this.control.enable(false, id);
		return;
	}
	if (parts == null) {
		// call fails for some reason. Do nothing!
		this.isValid = false;
		this.control.enable(false, id);
		return;
	} else if (parts[0] == 'true') {
		// article exists -> MUST NOT exist
		this.isValid = this.mustExist;
		this.control.enable(this.mustExist, id);
		if (!this.isValid) {
			this.hintDIV.innerHTML = gLanguage.getMessage('OB_TITLE_EXISTS');
		}
		return;
	} else {
		this.isValid = !this.mustExist;
		this.control.enable(!this.mustExist, id);
		if (!this.isValid) {
			this.hintDIV.innerHTML = gLanguage.getMessage('OB_TITLE_NOTEXISTS');
		}
	}
}
;

var pageName = $F(this.id);
if (pageName == '') {
	this.control.enable(false, this.id);
	return;
}
this.pendingElement.show(this.id)
var pageNameWithNS = this.ns == '' ? pageName : this.ns + ":" + pageName;
sajax_do_call('smwf_om_ExistsArticleIgnoreRedirect', [ pageNameWithNS ],
		ajaxResponseExistsArticle.bind(this, this.id));
return null;
}

});


/**
 * Base class for OntologyBrowser submenu GUI elements.
 */
var OBOntologySubMenu = Class.create();
OBOntologySubMenu.prototype = {

	/**
	 * @param id
	 *            ID of DIV element containing the menu
	 * @param objectname
	 *            Name of JS object (in order to refer in HTML links to it)
	 */
	initialize : function(id, objectname) {
		this.OBOntologySubMenu(id, objectname);
	},

	OBOntologySubMenu : function(id, objectname) {
		this.id = id;
		this.objectname = objectname;

		this.commandID = null;

		this.envContainerID = null;
		this.oldHeight = 0;

		this.menuOpened = false;

	},
	/**
	 * @public
	 * 
	 * Shows menu subview.
	 * 
	 * @param commandID
	 *            command to execute.
	 * @param envContainerID
	 *            ID of container which contains the menu.
	 */
	showContent : function(commandID, envContainerID) {

	    //if(){
		//this.showContentProperty.editCancel();
		//}
		if (this.menuOpened) {
			this._cancel();
		}
		this.commandID = commandID;
		this.envContainerID = envContainerID;
		$(this.id).replace(this.getUserDefinedControls());

		// adjust parent container size
	this.oldHeight = $(envContainerID).getHeight();
	this.adjustSize();
	this.setValidators();
	this.setFocus();

	this.menuOpened = true;
},

showContentProperty : function(commandID, envContainerID, propertyName,minCard,type) {

        //.cancel();       
		if (this.menuOpened) {
			this._cancel();
		}
		this.commandID = commandID;
		this.envContainerID = envContainerID;
		this.propertyName  = propertyName;
		this.propertyMinCard = minCard;
		this.propertyType = type;
		
		var c = 0;
		for ( var i = 1; i < this.builtinTypes.length; i++) {
			if(this.builtinTypes[i]== this.propertyType){
			 c++;							 
               }
			}			
		$(this.id).replace(this.getUserDefinedControls());
		if (c == 0) {
		 this.isRange = true;
         this.showRange(true);			 
		} 
        titleChanged = false;
		typeChanged = false;
		cardChanged = false;
		rangeChanged = false;
		
		// adjust parent container size
	this.oldHeight = $(envContainerID).getHeight();
	this.adjustSize();
	this.setTitleValidators();
	this.setFocus();
	//this.menuOpened = true;
},

/**
 * @public
 * 
 * Adjusts size if menu is modified.
 */
adjustSize : function() {
	var menuBarHeight = $(this.id).getHeight();
	var newHeight = (this.oldHeight - menuBarHeight - 2) + "px";
	$(this.envContainerID).setStyle( {
		height : newHeight
	});

},

/**
 * @public
 * 
 * Close subview
 */
_cancel : function() {

	// reset height
	var newHeight = (this.oldHeight - 2) + "px";
	$(this.envContainerID).setStyle( {
		height : newHeight
	});

	// remove DIV content
	$(this.id).replace('<div id="' + this.id + '">');
	this.menuOpened = false;
},

/**
 * @abstract
 * 
 * Set validators for input fields.
 */
setValidators : function() {

},



/**
 * @abstract
 * 
 * Returns HTML string with user defined content of th submenu.
 */
getUserDefinedControls : function() {

},

/**
 * @abstract
 * 
 * Executes a command
 */
doCommand : function() {

},
/**
 * @abstract
 * 
 * Enables or disables a INPUT field and (not necessarily) the command button.
 */
enable : function(b, id) {
	// no impl
},

/**
 * @abstract
 * 
 * Resets a INPUT field and disables (not necessarily) the command button.
 */
reset : function(id) {
	// no impl
}

}

/**
 * CategoryTree submenu
 */
var OBCatgeorySubMenu = Class.create();
OBCatgeorySubMenu.prototype = Object
		.extend(
				new OBOntologySubMenu(),
				{
					initialize : function(id, objectname) {
						this.OBOntologySubMenu(id, objectname);

						this.titleInputValidator = null;
						this.selectedTitle = null;
						this.selectedID = null;

						selectionProvider.addListener(this,
								OB_SELECTIONLISTENER);
					},

					selectionChanged : function(id, title, ns, node) {
						if (ns == SMW_CATEGORY_NS) {
							this.selectedTitle = title;
							this.selectedID = id;

						}
					},

					doCommand : function() {
						switch (this.commandID) {
						case SMW_OB_COMMAND_ADDSUBCATEGORY: {
							ontologyTools.addSubcategory(
									$F(this.id + '_input_ontologytools'),
									this.selectedTitle, this.selectedID);
							this.cancel();
							break;
						}
						case SMW_OB_COMMAND_ADDSUBCATEGORY_SAMELEVEL: {
							ontologyTools.addSubcategoryOnSameLevel(
									$F(this.id + '_input_ontologytools'),
									this.selectedTitle, this.selectedID);
							this.cancel();
							break;
						}
						case SMW_OB_COMMAND_SUBCATEGORY_RENAME: {
							ontologyTools.renameCategory(
									$F(this.id + '_input_ontologytools'),
									this.selectedTitle, this.selectedID);
							this.cancel();
							break;
						}
						default:
							alert('Unknown command!');
						}
					},

					getCommandText : function() {
						switch (this.commandID) {
						case SMW_OB_COMMAND_SUBCATEGORY_RENAME:
							return 'OB_RENAME';
						case SMW_OB_COMMAND_ADDSUBCATEGORY_SAMELEVEL: // fall
																		// through
						case SMW_OB_COMMAND_ADDSUBCATEGORY:
							return 'OB_CREATE';

						default:
							return 'Unknown command';
						}

					},

					getUserDefinedControls : function() {
						var titlevalue = this.commandID == SMW_OB_COMMAND_SUBCATEGORY_RENAME ? this.selectedTitle
								.replace(/_/g, " ")
								: '';
						return '<div id="'
								+ this.id
								+ '">'
								+ '<div style="display: block; height: 22px;">'
								+ '<input style="display:block; width:45%; float:left" id="'
								+ this.id
								+ '_input_ontologytools" type="text" value="'
								+ titlevalue
								+ '"/>'
								+ '<span style="margin-left: 10px;" id="'
								+ this.id
								+ '_apply_ontologytools">'
								+ gLanguage.getMessage('OB_ENTER_TITLE')
								+ '</span> | '
								+ '<a onclick="'
								+ this.objectname
								+ '.cancel()">'
								+ gLanguage.getMessage('CANCEL')
								+ '</a>'
								+ (this.commandID == SMW_OB_COMMAND_SUBCATEGORY_RENAME ? ' | <a onclick="'
										+ this.objectname
										+ '.preview()" id="'
										+ this.id
										+ '_preview_ontologytools">'
										+ gLanguage.getMessage('OB_PREVIEW')
										+ '</a>'
										: '') + '</div>'
								+ '<div id="preview_category_tree"/></div>';
					},

					setValidators : function() {
						this.titleInputValidator = new OBInputTitleValidator(
								this.id + '_input_ontologytools', gLanguage
										.getMessage('CATEGORY_NS_WOC'), false,
								this);

					},

					setFocus : function() {
						$(this.id + '_input_ontologytools').focus();
					},

					cancel : function() {
						this.titleInputValidator.deregisterListeners();
						this._cancel();
					},

					/**
					 * @public
					 * 
					 * Do preview
					 */
					preview : function() {
						var pendingElement = new OBPendingIndicator();
						pendingElement.show($('preview_category_tree'));
						sajax_do_call('smwf_ob_PreviewRefactoring', [
								this.selectedTitle, SMW_CATEGORY_NS ],
								this.pastePreview.bind(this, pendingElement));
					},

					/**
					 * @private
					 * 
					 * Pastes preview data
					 */
					pastePreview : function(pendingElement, request) {
						pendingElement.hide();
						var table = '<table border="0" class="menuBarcategoryTree">' + request.responseText + '</table>';
						$('preview_category_tree').innerHTML = table;
						this.adjustSize();
					},

					/**
					 * @private
					 * 
					 * Replaces the command button with an enabled/disabled
					 * version.
					 * 
					 * @param b
					 *            enable/disable
					 * @param errorMessage
					 *            message string defined in SMW_LanguageXX.js
					 */
					enableCommand : function(b, errorMessage) {
						if (b) {
							$(this.id + '_apply_ontologytools')
									.replace(
											'<a style="margin-left: 10px;" id="'
													+ this.id
													+ '_apply_ontologytools" '
													+ 'onclick="'
													+ this.objectname
													+ '.doCommand()">'
													+ gLanguage.getMessage(this
															.getCommandText())
													+ '</a>');
						} else {
							$(this.id + '_apply_ontologytools').replace(
									'<span style="margin-left: 10px;" id="'
											+ this.id
											+ '_apply_ontologytools">'
											+ gLanguage
													.getMessage(errorMessage)
											+ '</span>');
						}
					},

					/**
					 * @public
					 * 
					 * Enables or disables an INPUT field and enables or
					 * disables command button.
					 * 
					 * @param enabled/disable
					 * @param id
					 *            ID of input field
					 */
					enable : function(b, id) {
						var bg_color = b ? '#0F0' : $F(id) == '' ? '#FFF'
								: '#F00';

						this.enableCommand(b, b ? this.getCommandText()
								: $F(id) == '' ? 'OB_ENTER_TITLE'
										: 'OB_TITLE_EXISTS');
						$(id).setStyle( {
							backgroundColor : bg_color
						});

					},
					
					

					/**
					 * Resets an input field and disables the command button.
					 * 
					 * @param id
					 *            ID of input field
					 */
					reset : function(id) {
						this.enableCommand(false, 'OB_ENTER_TITLE');
						$(id).setStyle( {
							backgroundColor : '#FFF'
						});
					}
				});

/**
 * PropertyTree submenu
 */
var OBPropertySubMenu = Class.create();
OBPropertySubMenu.prototype = Object
		.extend(
				new OBOntologySubMenu(),
				{
					initialize : function(id, objectname) {
						this.OBOntologySubMenu(id, objectname);
						this.selectedTitle = null;
						this.selectedID = null;
						selectionProvider.addListener(this,
								OB_SELECTIONLISTENER);
					},

					selectionChanged : function(id, title, ns, node) {
						if (ns == SMW_PROPERTY_NS) {
							this.selectedTitle = title;
							this.selectedID = id;
						}
					},

					doCommand : function() {
						switch (this.commandID) {
						case SMW_OB_COMMAND_ADDSUBPROPERTY: {
							ontologyTools.addSubproperty(
									$F(this.id + '_input_ontologytools'),
									this.selectedTitle, this.selectedID);
							this.cancel();
							break;
						}
						case SMW_OB_COMMAND_ADDSUBPROPERTY_SAMELEVEL: {
							ontologyTools.addSubpropertyOnSameLevel(
									$F(this.id + '_input_ontologytools'),
									this.selectedTitle, this.selectedID);
							this.cancel();
							break;
						}
						case SMW_OB_COMMAND_SUBPROPERTY_RENAME: {
							ontologyTools.renameProperty(
									$F(this.id + '_input_ontologytools'),
									this.selectedTitle, this.selectedID);
							this.cancel();
							break;
						}
						default:
							alert('Unknown command!');
						}
					},

					getCommandText : function() {
						switch (this.commandID) {
						case SMW_OB_COMMAND_SUBPROPERTY_RENAME:
							return 'OB_RENAME';
						case SMW_OB_COMMAND_ADDSUBPROPERTY_SAMELEVEL: // fall
																		// through
						case SMW_OB_COMMAND_ADDSUBPROPERTY:
							return 'OB_CREATE';

						default:
							return 'Unknown command';
						}

					},

					getUserDefinedControls : function() {
						var titlevalue = this.commandID == SMW_OB_COMMAND_SUBPROPERTY_RENAME ? this.selectedTitle
								.replace(/_/g, " ")
								: '';
						return '<div id="'
								+ this.id
								+ '">'
								+ '<div style="display: block; height: 22px;">'
								+ '<input style="display:block; width:45%; float:left" id="'
								+ this.id
								+ '_input_ontologytools" type="text" value="'
								+ titlevalue
								+ '"/>'
								+ '<span style="margin-left: 10px;" id="'
								+ this.id
								+ '_apply_ontologytools">'
								+ gLanguage.getMessage('OB_ENTER_TITLE')
								+ '</span> | '
								+ '<a onclick="'
								+ this.objectname
								+ '.cancel()">'
								+ gLanguage.getMessage('CANCEL')
								+ '</a>'
								+ (this.commandID == SMW_OB_COMMAND_SUBPROPERTY_RENAME ? ' | <a onclick="'
										+ this.objectname
										+ '.preview()" id="'
										+ this.id
										+ '_preview_ontologytools">'
										+ gLanguage.getMessage('OB_PREVIEW')
										+ '</a>'
										: '') + '</div>'
								+ '<div id="preview_property_tree"/></div>';
					},

					setValidators : function() {
						this.titleInputValidator = new OBInputTitleValidator(
								this.id + '_input_ontologytools', gLanguage
										.getMessage('PROPERTY_NS_WOC'), false,
								this);

					},

					setFocus : function() {
						$(this.id + '_input_ontologytools').focus();
					},

					cancel : function() {
						this.titleInputValidator.deregisterListeners();

						this._cancel();
					},

					preview : function() {
						sajax_do_call('smwf_ob_PreviewRefactoring', [
								this.selectedTitle, SMW_PROPERTY_NS ],
								this.pastePreview.bind(this));
					},

					pastePreview : function(request) {
						var table = '<table border="0" class="menuBarpropertyTree">' + request.responseText + '</table>';
						$('preview_property_tree').innerHTML = table;
						this.adjustSize();
					},

					/**
					 * @private
					 * 
					 * Replaces the command button with an enabled/disabled
					 * version.
					 * 
					 * @param b
					 *            enable/disable
					 * @param errorMessage
					 *            message string defined in SMW_LanguageXX.js
					 */
					enableCommand : function(b, errorMessage) {
						if (b) {
							$(this.id + '_apply_ontologytools')
									.replace(
											'<a style="margin-left: 10px;" id="'
													+ this.id
													+ '_apply_ontologytools" onclick="'
													+ this.objectname
													+ '.doCommand()">'
													+ gLanguage.getMessage(this
															.getCommandText())
													+ '</a>');
						} else {
							$(this.id + '_apply_ontologytools').replace(
									'<span style="margin-left: 10px;" id="'
											+ this.id
											+ '_apply_ontologytools">'
											+ gLanguage
													.getMessage(errorMessage)
											+ '</span>');
						}
					},

					/**
					 * @public
					 * 
					 * Enables or disables an INPUT field and enables or
					 * disables command button.
					 * 
					 * @param enabled/disable
					 * @param id
					 *            ID of input field
					 */
					enable : function(b, id) {
						var bg_color = b ? '#0F0' : $F(id) == '' ? '#FFF'
								: '#F00';

						this.enableCommand(b, b ? this.getCommandText()
								: $F(id) == '' ? 'OB_ENTER_TITLE'
										: 'OB_TITLE_EXISTS');
						$(id).setStyle( {
							backgroundColor : bg_color
						});

					},

					/**
					 * Resets the input field and disables the command button.
					 * 
					 * @param id
					 *            ID of input field
					 */
					reset : function(id) {
						this.enableCommand(false, 'OB_ENTER_TITLE');
						$(id).setStyle( {
							backgroundColor : '#FFF'
						});
					}
				});

/**
 * Instance list submenu
 */
var OBInstanceSubMenu = Class.create();
OBInstanceSubMenu.prototype = Object
		.extend(
				new OBOntologySubMenu(),
				{
					initialize : function(id, objectname) {
						this.OBOntologySubMenu(id, objectname);

						this.selectedTitle = null;
						this.selectedID = null;
						selectionProvider.addListener(this,
								OB_SELECTIONLISTENER);
					},

					selectionChanged : function(id, title, ns, node) {
						if (ns == SMW_INSTANCE_NS) {
							this.selectedTitle = title;
							this.selectedID = id;
						} else if (ns == SMW_CATEGORY_NS) {
							this.selectedCategoryTitle = title;
							this.selectedCategoryID = id;
						}
					},

					doCommand : function(directCommandID) {
						var commandID = directCommandID ? directCommandID
								: this.commandID
						switch (commandID) {

						case SMW_OB_COMMAND_INSTANCE_RENAME: {
							ontologyTools.renameInstance(
									$F(this.id + '_input_ontologytools'),
									this.selectedTitle, this.selectedID);
							this.cancel();
							break;
						}
						case SMW_OB_COMMAND_INSTANCE_DELETE: {
							ontologyTools.deleteInstance(this.selectedTitle,
									this.selectedID);
							this.cancel();
							break;
						}
						case SMW_OB_COMMAND_INSTANCE_CREATE: {
							ontologyTools.createInstance($F(this.id + '_input_ontologytools'),
									this.selectedCategoryTitle,
									this.selectedCategoryID);
							this.cancel();
							break;
						}
						default:
							alert('Unknown command!');
						}
					},

					getCommandText : function() {
						switch (this.commandID) {

						case SMW_OB_COMMAND_INSTANCE_RENAME:
							return 'OB_RENAME';
						case SMW_OB_COMMAND_INSTANCE_CREATE:
							return 'OB_CREATE';
						default:
							return 'Unknown command';
						}

					},

					getUserDefinedControls : function() {

						var html = "";
						if (this.commandID == SMW_OB_COMMAND_INSTANCE_RENAME) {
							var titlevalue = this.selectedTitle.replace(/_/g, " ");
							html += '<div id="'
									+ this.id
									+ '">'
									+ '<div style="display: block; height: 22px;">'
									+ '<input style="display:block; width:45%; float:left" id="'
									+ this.id
									+ '_input_ontologytools" type="text" value="'
									+ titlevalue + '"/>'
									+ '<span style="margin-left: 10px;" id="'
									+ this.id + '_apply_ontologytools">'
									+ gLanguage.getMessage('OB_ENTER_TITLE')
									+ '</span> | ' + '<a onclick="'
									+ this.objectname + '.cancel()">'
									+ gLanguage.getMessage('CANCEL')
									+ '</a> | ' + '<a onclick="'
									+ this.objectname + '.preview()" id="'
									+ this.id + '_preview_ontologytools">'
									+ gLanguage.getMessage('OB_PREVIEW')
									+ '</a>' + '</div>'
									+ '<div id="preview_instance_list"/></div>';
						} else if (this.commandID == SMW_OB_COMMAND_INSTANCE_DELETE) {
							var titlevalue = this.selectedTitle.replace(/_/g, " ");

							html += '<div id="'
									+ this.id
									+ '">'
									+ '<div style="display: block; height: 22px;">'
									+ '<input style="display:block; width:45%; float:left" id="'
									+ this.id
									+ '_input_ontologytools" disabled="true" type="text" value="'
									+ titlevalue + '"/>' + '<a onclick="'
									+ this.objectname + '.doCommand()">'
									+ gLanguage.getMessage('DELETE')
									+ '</a> |  | ' + '<a onclick="'
									+ this.objectname + '.cancel()">'
									+ gLanguage.getMessage('CANCEL')
									+ '</a> | ' + '<a onclick="'
									+ this.objectname + '.preview()" id="'
									+ this.id + '_preview_ontologytools">'
									+ gLanguage.getMessage('OB_PREVIEW')
									+ '</a>' + '</div>'
									+ '<div id="preview_instance_list"/></div>';
						}  else if (this.commandID == SMW_OB_COMMAND_INSTANCE_CREATE) {
							var titlevalue = this.selectedCategoryTitle.replace(/_/g, " ");

							html += '<div id="'
								+ this.id
								+ '">'
								+ '<div style="display: block; height: 22px;">'
								+ '<input style="display:block; width:45%; float:left" id="'
								+ this.id
								+ '_input_ontologytools" type="text" value="'
								 + '"/>'
								+ '<span style="margin-left: 10px;" id="'
								+ this.id + '_apply_ontologytools">'
								+ gLanguage.getMessage('OB_ENTER_TITLE')
								+ '</span> | ' + '<a onclick="'
								+ this.objectname + '.cancel()">'
								+ gLanguage.getMessage('CANCEL')
								+ '</a> <div id="preview_instance_list"/></div>';
					}
						return html;
					},

					setValidators : function() {
						this.titleInputValidator = new OBInputTitleValidator(
								this.id + '_input_ontologytools', '', false,
								this);

					},

					setFocus : function() {
						$(this.id + '_input_ontologytools').focus();
					},

					cancel : function() {

						this.titleInputValidator.deregisterListeners();
						this._cancel();

					},

					preview : function() {
						sajax_do_call('smwf_ob_PreviewRefactoring', [
								this.selectedTitle, SMW_INSTANCE_NS ],
								this.pastePreview.bind(this));
					},

					pastePreview : function(request) {
						var table = '<table border="0" class="menuBarInstance">' + request.responseText + '</table>';
						$('preview_instance_list').innerHTML = table;
						this.adjustSize();
					},

					/**
					 * @private
					 * 
					 * Replaces the command button with an enabled/disabled
					 * version.
					 * 
					 * @param b
					 *            enable/disable
					 * @param errorMessage
					 *            message string defined in SMW_LanguageXX.js
					 */
					enableCommand : function(b, errorMessage) {
						if (b) {
							$(this.id + '_apply_ontologytools')
									.replace(
											'<a style="margin-left: 10px;" id="'
													+ this.id
													+ '_apply_ontologytools" onclick="'
													+ this.objectname
													+ '.doCommand()">'
													+ gLanguage.getMessage(this
															.getCommandText())
													+ '</a>');
						} else {
							$(this.id + '_apply_ontologytools').replace(
									'<span style="margin-left: 10px;" id="'
											+ this.id
											+ '_apply_ontologytools">'
											+ gLanguage
													.getMessage(errorMessage)
											+ '</span>');
						}
					},

					/**
					 * @public
					 * 
					 * Enables or disables an INPUT field and enables or
					 * disables command button.
					 * 
					 * @param enabled/disable
					 * @param id
					 *            ID of input field
					 */
					enable : function(b, id) {
						var bg_color = b ? '#0F0' : $F(id) == '' ? '#FFF'
								: '#F00';

						this.enableCommand(b, b ? this.getCommandText()
								: $F(id) == '' ? 'OB_ENTER_TITLE'
										: 'OB_TITLE_EXISTS');
						$(id).setStyle( {
							backgroundColor : bg_color
						});
					},
					
					
					

					/**
					 * Resets the input field and disables the command button.
					 * 
					 * @param id
					 *            ID of input field
					 */
					reset : function(id) {
						this.enableCommand(false, 'OB_ENTER_TITLE');
						$(id).setStyle( {
							backgroundColor : '#FFF'
						});
					}
				});

/**
 * Schema Property submenu
 */
var OBSchemaPropertySubMenu = Class.create();
OBSchemaPropertySubMenu.prototype = Object
		.extend(
				new OBOntologySubMenu(),
				{
					initialize : function(id, objectname) {
                        this.MandatoryChecked = false;
                        this.pageselected = false;
						this.OBOntologySubMenu(id, objectname);

						this.selectedTitle = null;
						this.selectedID = null;

                        this.maxCardValidator = null;
						this.minCardValidator = null;
				

						this.builtinTypes = [];
						this.count = 1;

						selectionProvider.addListener(this,
								OB_SELECTIONLISTENER);
						this.requestTypes();
					},

					selectionChanged : function(id, title, ns, node) {
						if (ns == SMW_CATEGORY_NS) {
							this.selectedTitle = title;
							this.selectedID = id;
						}
					},

					doCommand : function() {
						switch (this.commandID) {

						case SMW_OB_COMMAND_ADD_SCHEMAPROPERTY: {
							var propertyTitle = $F(this.id + '_propertytitle_ontologytools');                          
						    if(this.MandatoryChecked == true){
							var minCard = '1';
							var maxCard = '';						
							}
							if(this.MandatoryChecked == false){
							 var minCard = '0';
							 var maxCard = '';						
							}
							this.MandatoryChecked = false;
                            var rangeOrTypes1 = [];
							var rangeOrTypes = [];

						
								if ($('typeRange1_ontologytools') != null) {
									rangeOrTypes
											.push($F('typeRange1_ontologytools'));
								}
								
								if(this.pageselected == true){
								  if ($('typeRange2_ontologytools') != null) {							  
									  rangeOrTypes1
											  .push($F('typeRange2_ontologytools'));
									  if(rangeOrTypes1 != ''){
									      rangeOrTypes
											  .push($F('typeRange2_ontologytools'));
                                         }									  
								  }
                                }
                           
							ontologyTools.addSchemaProperty(propertyTitle,
									minCard, maxCard, rangeOrTypes,
									this.builtinTypes, this.selectedTitle,
									this.selectedID);
							this.cancel();
							break;
						}

						default:
							alert('Unknown command!');
						}
					},


					getCommandText : function() {
						switch (this.commandID) {

						case SMW_OB_COMMAND_ADD_SCHEMAPROPERTY:
							return 'OB_CREATE';

						default:
							return 'Unknown command';
						}

					},
                    
					getUserDefinedControls : function() {
					    var typebox = this.newTypeInputBox();
						var rangebox = this.newRangeInputBox();
						return  '<div id="'
								+ this.id
								+ '">'
								+ '<table class="menuBarProperties"><tr>'
								+ '<td width="60px;">'
								+ gLanguage.getMessage('NAME')
								+ '</td>'
								+ '<td><input id="'
								+ this.id
								+ '_propertytitle_ontologytools" type="text" tabIndex="101"/></td>'
								+ '</tr>'
								+ '<tr>'
								+ '<td>'
								+ gLanguage.getMessage('Mandatory')
								+ '</td>'
								+ '<td><input id="'
								+ this.id
								+ '_minCard_ontologytools" onclick="'
								+ this.objectname
			                    + '.Mandatory(this)" type="checkbox" name="Mandatory" size="5" tabIndex="102"/></td>'
								+ '</tr>'
								+ '</table>'
								+ '</table>'
								+ '<table class="menuBarProperties" id="typesAndRanges"></table>'
								+ '<table class="menuBarProperties">' + '<tr>'
								+ '<td width="60px;">'
								+ gLanguage.getMessage('ADD_TYPE')
								+ '</td><td>'+ typebox + '</td></tr>' + '<tr><td width="60px;">'
								+ gLanguage.getMessage('ADD_RANGE')
								+ '</td><td>'+ rangebox +'</td>' + '</tr>' + '</table>'
								+ '<span style="margin-left: 10px;" id="'
								+ this.id + '_apply_ontologytools">'
								+ gLanguage.getMessage('OB_ENTER_TITLE')
								+ '</span> | ' + '<a onclick="'
								+ this.objectname + '.cancel()">'
								+ gLanguage.getMessage('CANCEL') + '</a>'
								+ '</div>'; 

					},
					
				
					Mandatory : function(el){
					  if (el.checked == true){
					  this.MandatoryChecked = true;
					  }
					},

					setValidators : function() {
					    var c = this.count+1;
						this.titleInputValidator = new OBInputTitleValidator(
								this.id + '_propertytitle_ontologytools',
								gLanguage.getMessage('PROPERTY_NS_WOC'), false,
								this);					
					},

					/**
					 * @private
					 * 
					 * Check if max cardinality is an integer > 0
					 */
					checkMaxCard : function() {
						var maxCard = $F(this.id + '_maxCard_ontologytools');
						var valid = maxCard == ''
								|| (maxCard.match(/^\d+$/) != null && parseInt(maxCard) > 0);
						return valid;
					},

					/**
					 * @private
					 * 
					 * Check if min cardinality is an integer >= 0
					 */
					checkMinCard : function() {
						var minCard = $F(this.id + '_minCard_ontologytools');
						var valid = minCard == ''
								|| (minCard.match(/^\d+$/) != null && parseInt(minCard) >= 0);
						return valid;
					},

					setFocus : function() {
						$(this.id + '_propertytitle_ontologytools').focus();
					},

					cancel : function() {
						this.titleInputValidator.deregisterListeners();
			
						this._cancel();
					},

					enable : function(b, id) {
						var bg_color = b ? '#0F0' : $F(id) == '' ? '#FFF'
								: '#F00';

						$(id).setStyle( {
							backgroundColor : bg_color
						});

						this.enableCommand(this.allIsValid(), this
								.getCommandText());

					},

					reset : function(id) {
						this.enableCommand(false, 'OB_CREATE');
						$(id).setStyle( {
							backgroundColor : '#FFF'
						});
					},

					/**
					 * @private
					 * 
					 * Replaces the command button with an enabled/disabled
					 * version.
					 * 
					 * @param b
					 *            enable/disable
					 * @param errorMessage
					 *            message string defined in SMW_LanguageXX.js
					 */
					enableCommand : function(b, errorMessage) {
						if (b) {
							$(this.id + '_apply_ontologytools')
									.replace(
											'<a style="margin-left: 10px;" id="'
													+ this.id
													+ '_apply_ontologytools" onclick="'
													+ this.objectname
													+ '.doCommand()">'
													+ gLanguage.getMessage(this
															.getCommandText())
													+ '</a>');
						} else {
							// $(this.id + '_apply_ontologytools').replace(
									// '<span style="margin-left: 10px;" id="'
											// + this.id
											// + '_apply_ontologytools">'
											// + gLanguage
													// .getMessage(errorMessage)
											// + '</span>');
						}
					},

					/**
					 * @abstract
					 * 
					 * Checks if a INPUTs are valid
					 * 
					 * @return true/false
					 */
					allIsValid : function() {
						var valid = this.titleInputValidator.isValid;

						return valid;
					},

					/**
					 * @private
					 * 
					 * Requests builtin types from wiki via AJAX call.
					 */
					requestTypes : function() {

						function fillBuiltinTypesCallback(request) {
							this.builtinTypes = this.builtinTypes
									.concat(request.responseText.split(","));
							GeneralBrowserTools.setCookieObject("smwh_builtinTypes", this.builtinTypes);
						}

						function fillUserTypesCallback(request) {
							var userTypes = request.responseText.split(",");
							// remove first element
							userTypes.shift();
							this.builtinTypes = this.builtinTypes
									.concat(userTypes);
						}
						
							this.builtinTypes = GeneralBrowserTools.getCookieObject("smwh_builtinTypes");
							if (this.builtinTypes == null) {
								this.builtinTypes = new Array();
								sajax_do_call('smwf_tb_GetBuiltinDatatypes', 
								              [], 
								              fillBuiltinTypesCallback.bind(this));
							}
						
						
						sajax_do_call('smwf_tb_GetUserDatatypes', [],
								fillUserTypesCallback.bind(this));
					},
					
                    onchangeTypeSelector: function(event) {
						var value = $F(event.currentTarget);
						if (value.toLowerCase() == gLanguage.getMessage('PAGE_TYPE')) {
							$('typeRange2_ontologytools').enable();
							$('typeRange2_ontologytools').setStyle( {backgroundColor : '#fff'});
							this.pageselected = true;
						} else {
						    $('typeRange2_ontologytools').value = "";							
							$('typeRange2_ontologytools').setStyle( {backgroundColor : '#aaa'});
							$('typeRange2_ontologytools').disable();
							this.pageselected = false;
						}						
					},					

					
					/**
					 * @private
					 * 
					 * Creates new type selection box
					 * 
					 * @return HTML
					 */
					newTypeInputBox : function() {
					   
						var toReplace = '<select id="typeRange' + this.count
								+ '_ontologytools" name="types' + this.count
								+ '" onchange="obSchemaPropertiesMenuProvider.onchangeTypeSelector(event)" tabIndex="103">';
		
						for ( var i = 1; i < this.builtinTypes.length; i++) {
							toReplace += '<option>' + this.builtinTypes[i] + '</option>';
						}
						toReplace += '</select>';
						
						return toReplace;
					},

			
					
					
					/**
					 * @private
					 * 
					 * Creates new range category selection box with
					 * auto-completion.
					 * 
					 * @return HTML
					 */					 

					newRangeInputBox : function() {
					    var c = this.count +1 ;
						var toReplace = '<input class="wickEnabled" constraints="namespace: 14" disabled="false" type="text" id="typeRange'
						        + c
								+ '_ontologytools" tabIndex="104"/>';
						return toReplace;
					},

					/**
					 * @private
					 * 
					 * Adds additional type selection box in typesRange
					 * container.
					 */
					addType : function() {
						if (this.builtinTypes == null) {
							return;
						}
						// tbody already in DOM?
					var addTo = $('typesAndRanges').firstChild == null ? $('typesAndRanges')
							: $('typesAndRanges').firstChild;
					var toReplace = $(addTo.appendChild(document
							.createElement("tr")));
					toReplace
							.replace('<tr><td width="60px;">Type </td><td>' + this
									.newTypeInputBox() + '</td></tr>');

					this.count++;
					this.adjustSize();
				},		
					
				/**
				 * @private
				 * 
				 * Adds additional range category selection box in typesRange
				 * container.
				 */
				addRange : function() {
					// tbody already in DOM?
					var addTo = $('typesAndRanges').firstChild == null ? $('typesAndRanges')
							: $('typesAndRanges').firstChild;

					autoCompleter.deregisterAllInputs();
					// create dummy element and replace afterwards
					var toReplace = $(addTo.appendChild(document
							.createElement("tr")));
					toReplace
							.replace('<tr><td width="60px;">'+gLanguage.getMessage('CATEGORY_NS_WOC')+' </td><td>' + this
									.newRangeInputBox() + '</td></tr>');
					autoCompleter.registerAllInputs();

					
					 this.enable(true,
							 'typeRange' + this.count + '_ontologytools');

					this.count++;
					this.adjustSize();
				},

				/**
				 * @private
				 * 
				 * Removes type or range category selection box from typesRange
				 * container.
				 */
				removeTypeOrRange : function(id, isRange) {

					if (isRange) {
						// deregisterValidator
					var match = /typeRange(\d+)/;
					var num = match.exec(id)[1];
				
				}

				var row = $(id);
				while (row.parentNode.getAttribute('id') != 'typesAndRanges')
					row = row.parentNode;
				// row is tbody element
				row.removeChild($(id).parentNode.parentNode);

				this.enableCommand(this.allIsValid(), this.getCommandText());
				this.adjustSize();
			}
				});

var obCategoryMenuProvider = new OBCatgeorySubMenu('categoryTreeMenu',
		'obCategoryMenuProvider');
var obPropertyMenuProvider = new OBPropertySubMenu('propertyTreeMenu',
		'obPropertyMenuProvider');
var obInstanceMenuProvider = new OBInstanceSubMenu('instanceListMenu',
		'obInstanceMenuProvider');
var obSchemaPropertiesMenuProvider = new OBSchemaPropertySubMenu(
		'schemaPropertiesMenu', 'obSchemaPropertiesMenuProvider');
		
/**
 * Edit Property submenu
 */		
var OBEditPropertySubMenu = Class.create();
OBEditPropertySubMenu.prototype = Object
		.extend(
				new OBOntologySubMenu(),
				{
					initialize : function(id, objectname) {
                        var propertyName = '';
                        var propertyType = '';
                        var propertyRange = '';
                        var propertyMandatory = '';
                        var propertyMinCard = '';
                        var isRange = false;
                        var titleChanged = false;
                        var cardChanged = false;
                        var typeChanged = false;
                        var rangeChanged = false;
                        var typeOrRange = '';
                        var isChanged = false;
                        var newTitle = '';
                        var newCard = '';
                        var newType = '';
                        var newRange = '';
						this.OBOntologySubMenu(id, objectname);

						this.selectedTitle = null;
						this.selectedID = null;

                        this.maxCardValidator = null;
						this.minCardValidator = null;
						this.rangeValidator = [];

						this.builtinTypes = [];
						this.count = 1;

						selectionProvider.addListener(this,
								OB_SELECTIONLISTENER);
						this.requestTypes();
					},

					selectionChanged : function(id, title, ns, node) {
						if (ns == SMW_CATEGORY_NS) {
							this.selectedTitle = title;
							this.selectedID = id;
						}
					},

					doCommand : function() {
					       var category = obInstanceMenuProvider.selectedCategoryTitle;
						   var title = this.propertyName;
						   var newTitle = this.newTitle;
						   var reason = 'Other reason';
						   var oldType = this.typeOrRange;
						   var oldCard = this.propertyMinCard; 
						   
						   if(this.propertyRange != null){
						     var oldRange = this.propertyRange;
						   }else{
						     var oldRange = '';
						   }
						   var Card = this.propertyMinCard;
						   var Type = this.typeOrRange;
						   
						   if(this.propertyRange != null){
						     var Range = this.propertyRange;
						   }else{
						     var Range = '';
						   }
						   if(typeChanged == true){
						    Type = this.newType;
						   }
						   if(Type != 'Page'){
						    Range = '';
						   }
						   
						   if(cardChanged == true){
						     Card = this.newCard;
						   }
						   
						   if(rangeChanged == true){
						     Range = this.newRange;
						   }
						   
                           //saves changes
						     if(cardChanged == true || typeChanged == true || rangeChanged == true){   							 
							 ontologyTools.editProperties(title, Type, Card, Range, oldType, oldCard, oldRange, category, this.selectedID);
							  typeChanged = false;
							  rangeChanged = false;
							  cardChanged = false;
							}	
						     if(titleChanged == true){
							 ontologyTools.renameProperty1(newTitle,title, this.selectedID);
							 titleChanged = false;
							 }	
                                                this.cancel();							 
					},


					getCommandText : function() {
						switch (this.commandID) {

						case SMW_OB_COMMAND_ADD_SCHEMAPROPERTY:
							return 'OB_CREATE';

						default:
							return 'OB_SAVE_CHANGES';
						}
					},
                    
					getUserDefinedControls : function() {
					   var propMandatory = '';
					   if(this.propertyMinCard == '0'){
					     propMandatory = '';
						 propertyMandatory = false;
					   }else{
					    propMandatory = 'checked="true"';
						propertyMandatory = true;
					   }
					    var propertyName = this.propertyName;
					    var typebox = this.newTypeInputBox();
						var rangebox = this.newRangeInputBox();
						return  '<div id="'
								+ this.id
								+ '">'
								+ '<span style="margin-left: 10px;" id="'
								+ this.id + '_ontologytools">'
								+ gLanguage.getMessage('EditProperty')
								+ '</span>'
								+ '<table class="menuBarProperties"><tr>'						
								+ '<td width="60px;">'
								+ gLanguage.getMessage('NAME')
								+ '</td>'
								+ '<td><input id="'
								+ this.id
								+ '_propertytitle_ontologytools" type="text" value="'
								+ propertyName 
								+ '" onKeyup="obEditPropertiesMenuProvider.onchangeTitleSelector(event)" " tabIndex="101"/></td>'
								+ '</tr>'
								+ '<tr>'
								+ '<td>'
								+ gLanguage.getMessage('Mandatory')
								+ '</td>'
								+ '<td><input id="'
								+ this.id
								+ '_minCard_ontologytools" onclick="'
								+ this.objectname
			                    + '.Mandatory(this)" type="checkbox"'
								+ propMandatory
								+ ' name="Mandatory" size="5"'
        						+ 'tabIndex="102"/></td>'
								+ '</tr>'
								+ '</table>'
								+ '</table>'
								+ '<table class="menuBarProperties" id="typesAndRanges"></table>'
								+ '<table class="menuBarProperties">' + '<tr>'
								+ '<td width="60px;">'
								+ gLanguage.getMessage('ADD_TYPE')
								+ '</td><td>'+ typebox + '</td></tr>' + '<tr><td width="60px;">'
								+ gLanguage.getMessage('ADD_RANGE')
								+ '</td><td>'+ rangebox +'</td>' + '</tr>' + '</table>'
								+ '<span style="margin-left: 10px;" id="'
								+ this.id + '_apply_ontologytools">'
								+ gLanguage.getMessage('OB_SAVE_CHANGES')
								+ '</span> | ' + '<a onclick="'
								+ this.objectname + '.cancel()">'
								+ gLanguage.getMessage('CANCEL') + '</a>'
								+ '</div>'; 
					},

					
					Mandatory : function(el){					  
					  if (el.checked == true){
					  MandatoryChecked = true;
					  this.newCard = '1';
					  }
					  if (el.checked == false){
					   MandatoryChecked = false;
					   this.newCard = '0';
					  }
					  
					   // checks if current cardinality and stored card. are different and enable saving changes
					   if(MandatoryChecked != propertyMandatory){
							 cardChanged = true;						 
                          } else {
							 cardChanged = false;
						  }    
						// enables/disables command  
					      if(cardChanged == true){
						       //enable "save changes"												 
							 this.enableCommand(true, 'OB_SAVE_CHANGES');							 
                          } 
						  if(cardChanged == false){
						     this.enableCommand(false, 'OB_SAVE_CHANGES');
						  }                     
					},

					onchangeTitleSelector: function(event){
					var value = $F(event.currentTarget);
					if(value != this.propertyName){
							 titleChanged = true;	
                             this.newTitle = value;							 
                          } else {
							 titleChanged = false;
						  }    
						// enables/disables command  
					   if(cardChanged == false && typeChanged == false && rangeChanged == false){
					      if(titleChanged == true){
						       //enable "save changes"												 
							 this.enableCommand(true, 'OB_SAVE_CHANGES');							 
                          } else {
						     this.enableCommand(false, 'OB_SAVE_CHANGES');
						  }                     
					    }
					},
					
					setValidators : function() {
						this.titleInputValidator = new OBInputTitleValidator(
								this.id + '_propertytitle_ontologytools',
								gLanguage.getMessage('PROPERTY_NS_WOC'), false,
								this);								
					},
					
					setTitleValidators : function() {					
						this.changedtitleInputValidator = new OBChangedTitleValidator(
								this.id + '_propertytitle_ontologytools',
								gLanguage.getMessage('PROPERTY_NS_WOC'), false,
								this);								
					},					
					
					/**
					 * @private
					 * 
					 * Check if max cardinality is an integer > 0
					 */
					checkMaxCard : function() {
						var maxCard = $F(this.id + '_maxCard_ontologytools');
						var valid = maxCard == ''
								|| (maxCard.match(/^\d+$/) != null && parseInt(maxCard) > 0);
						return valid;
					},

					/**
					 * @private
					 * 
					 * Check if min cardinality is an integer >= 0
					 */
					checkMinCard : function() {
						var minCard = $F(this.id + '_minCard_ontologytools');
						var valid = minCard == ''
								|| (minCard.match(/^\d+$/) != null && parseInt(minCard) >= 0);
						return valid;
					},

					setFocus : function() {
						$(this.id + '_propertytitle_ontologytools').focus();
					},

					cancel : function() {

						this._cancel();
					},

					enable : function(b, id) {
					   	if(titleChanged == false){				   
						var bg_color = '#FFF';
                           }else{
						   var bg_color = b ? '#0F0' : $F(id) == '' ? '#FFF'
								: '#F00';
						   }
						$(id).setStyle( {
							backgroundColor : bg_color
						});

						this.enableCommand(this.allIsValid(), this
								.getCommandText());
		
					},

					reset : function(id) {
						this.enableCommand(false, 'OB_CREATE');
						$(id).setStyle( {
							backgroundColor : '#FFF'
						});
					},

					/**
					 * @private
					 * 
					 * Replaces the command button with an enabled/disabled
					 * version.
					 * 
					 * @param b
					 *            enable/disable
					 * @param errorMessage
					 *            message string defined in SMW_LanguageXX.js
					 */
					enableCommand : function(b, errorMessage) {
						if (b) {
							$(this.id + '_apply_ontologytools')
									.replace(
											'<a style="margin-left: 10px;" id="'
													+ this.id
													+ '_apply_ontologytools" onclick="'
													+ this.objectname
													+ '.doCommand()">'
													+ gLanguage.getMessage(this
															.getCommandText())
													+ '</a>');
						} else {
							$(this.id + '_apply_ontologytools').replace(
									'<span style="margin-left: 10px;" id="'
											+ this.id
											+ '_apply_ontologytools">'
											+ gLanguage
													.getMessage(errorMessage)
											+ '</span>');
						}
					},

					/**
					 * @abstract
					 * 
					 * Checks if a INPUTs are valid
					 * 
					 * @return true/false
					 */
					allIsValid : function() {
						var valid = this.changedtitleInputValidator.isValid;
						return valid;
					},

					/**
					 * @private
					 * 
					 * Requests builtin types from wiki via AJAX call.
					 */
					requestTypes : function() {

						function fillBuiltinTypesCallback(request) {
							this.builtinTypes = this.builtinTypes
									.concat(request.responseText.split(","));
							GeneralBrowserTools.setCookieObject("smwh_builtinTypes", this.builtinTypes);
						}

						function fillUserTypesCallback(request) {
							var userTypes = request.responseText.split(",");
							// remove first element
							userTypes.shift();
							this.builtinTypes = this.builtinTypes
									.concat(userTypes);
						}
						
							this.builtinTypes = GeneralBrowserTools.getCookieObject("smwh_builtinTypes");
							if (this.builtinTypes == null) {
								this.builtinTypes = new Array();
								sajax_do_call('smwf_tb_GetBuiltinDatatypes', 
								              [], 
								              fillBuiltinTypesCallback.bind(this));
							}
						
						
						sajax_do_call('smwf_tb_GetUserDatatypes', [],
								fillUserTypesCallback.bind(this));
					},
					
					/**
					 * Checks if there is any change on type input and enables/disables the command 
					 */
                    onchangeTypeSelector: function(event) {
						var value = $F(event.currentTarget);
						this.newType = value;
						if (value.toLowerCase() == gLanguage.getMessage('PAGE_TYPE')) {
							$('typeRange2_ontologytools').enable();
							$('typeRange2_ontologytools').setStyle( {backgroundColor : '#fff'});
                            pageselected = true;						
						} else {
						    $('typeRange2_ontologytools').value = "";							
							$('typeRange2_ontologytools').setStyle( {backgroundColor : '#aaa'});
							$('typeRange2_ontologytools').disable();
							pageselected = false;
						}	
                        //checks if there is a change on the property's type
						if (value != this.propertyType){
							 typeChanged = true;							 
                           }
						   if (value == this.propertyType){
							 typeChanged = false;
						 }
						 
						 // enables/disables command
						if(titleChanged == false && cardChanged == false && rangeChanged == false){
						   //this.enableCommand(false, 'OB_SAVE_CHANGES');
						   if (typeChanged == true){
						   //enable save changes
						     this.enableCommand(true, 'OB_SAVE_CHANGES');						 
                           }
						   if (typeChanged == false){
						     this.enableCommand(false, 'OB_SAVE_CHANGES');
						 }
                        }						 
					},					

					showRange: function(isRange){
					 if(isRange == true){
					  $('typeRange2_ontologytools').enable();
					  $('typeRange2_ontologytools').setStyle( {backgroundColor : '#fff'});
					  $('typeRange2_ontologytools').value = this.propertyRange;
                      pageselected = true;
					 }
					},
					
					/**
					 * @private
					 * 
					 * Creates new type selection box
					 * 
					 * @return HTML
					 */					 
					 
					newTypeInputBox : function() {
						this.typeOrRange = this.propertyType;
                        var c = 0;
						// checks if typeOrRange is a type or a range
						for ( var i = 1; i < this.builtinTypes.length; i++) {
						    if(this.builtinTypes[i]== this.propertyType){
							 c++;							 
                            }
						 }
						 if(c==0){
						  this.propertyRange = this.propertyType;
						  this.newRange = this.propertyRange;
						  this.typeOrRange = 'Page';
						 }
						var toReplace = '<select id="typeRange' + this.count
								+ '_ontologytools" name="types' + this.count
								+ '" onchange="obEditPropertiesMenuProvider.onchangeTypeSelector(event)" tabIndex="103">';

		                toReplace += '<option>'+ this.typeOrRange +'</option>';
						 for ( var i = 1; i < this.builtinTypes.length; i++) {
						    if(this.builtinTypes[i]!= this.typeOrRange){
							 toReplace += '<option>' + this.builtinTypes[i] + '</option>';
                            }
						 }
						toReplace += '</select>';
						
						return toReplace;
					},			
					
					/**
					 * Checks if there is any change on range input and enables/disables the command 
					 */	
					onchangeRangeSelector : function(event){
					  var value = $F(event.currentTarget);					  
					   //check for changes
					  	if (value != this.propertyRange) {						 
							 rangeChanged = true;	
                             this.newRange = value;							 
                         } else {
							 rangeChanged = false;
						 }
						 if(value == ''){
						     rangeChanged = true;
							 this.newRange = value;
					    }
					  
					  // enables/disables command						 
					  
						 if (rangeChanged == true) {						 
							 this.enableCommand(true, 'OB_SAVE_CHANGES');							 
                         } else {
						     this.enableCommand(false, 'OB_SAVE_CHANGES');
						 }
						
					},
					
					/**
					 * @private
					 * 
					 * Creates new range category selection box with
					 * auto-completion.
					 * 
					 * @return HTML
					 */					 

					newRangeInputBox : function() {
					    var c = this.count +1 ;
						var toReplace = '<input class="wickEnabled" constraints="namespace: 14" disabled="false" type="text" id="typeRange'
						        + c
								+ '_ontologytools"' + this.count
								+ '" onKeyup="obEditPropertiesMenuProvider.onchangeRangeSelector(event)" tabIndex="104"/>';
						return toReplace;
					},

					/**
					 * @private
					 * 
					 * Adds additional type selection box in typesRange
					 * container.
					 */
					addType : function() {
						if (this.builtinTypes == null) {
							return;
						}
						// tbody already in DOM?
					var addTo = $('typesAndRanges').firstChild == null ? $('typesAndRanges')
							: $('typesAndRanges').firstChild;
					var toReplace = $(addTo.appendChild(document
							.createElement("tr")));
					toReplace
							.replace('<tr><td width="60px;">Type </td><td>' + this
									.newTypeInputBox() + '</td></tr>');

					this.count++;
					this.adjustSize();
				},		
					
				/**
				 * @private
				 * 
				 * Adds additional range category selection box in typesRange
				 * container.
				 */
				addRange : function() {
					// tbody already in DOM?
					var addTo = $('typesAndRanges').firstChild == null ? $('typesAndRanges')
							: $('typesAndRanges').firstChild;

					autoCompleter.deregisterAllInputs();
					// create dummy element and replace afterwards
					var toReplace = $(addTo.appendChild(document
							.createElement("tr")));
					toReplace
							.replace('<tr><td width="60px;">'+gLanguage.getMessage('CATEGORY_NS_WOC')+' </td><td>' + this
									.newRangeInputBox() + '</td></tr>');
					autoCompleter.registerAllInputs();

					this.rangeValidator[this.count] = (new OBInputTitleValidator(
							'typeRange' + this.count + '_ontologytools',
							gLanguage.getMessage('CATEGORY_NS_WOC'), false, this));
					 this.enable(true,
							 'typeRange' + this.count + '_ontologytools');
                    
					this.count++;
					this.adjustSize();
				},

				/**
				 * @private
				 * 
				 * Removes type or range category selection box from typesRange
				 * container.
				 */
				removeTypeOrRange : function(id, isRange) {

					if (isRange) {
						// deregisterValidator
					var match = /typeRange(\d+)/;
					var num = match.exec(id)[1];
					this.rangeValidator[num].deregisterListeners();
					this.rangeValidator[num] = null;
				}

				var row = $(id);
				while (row.parentNode.getAttribute('id') != 'typesAndRanges')
					row = row.parentNode;
				// row is tbody element
				row.removeChild($(id).parentNode.parentNode);

				this.enableCommand(this.allIsValid(), this.getCommandText());
				this.adjustSize();
			}
				});
				
var obEditPropertiesMenuProvider = new OBEditPropertySubMenu(
		'schemaPropertiesMenu', 'obEditPropertiesMenuProvider');		