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
var RelationToolBar = Class.create();

var SMW_REL_VALID_PROPERTY_NAME =
	'smwValidValue="^[^<>\\|&$\\/=\\?\\{\\}\\[\\]]{1,255}$: valid ' +
		'? (color: white, hideMessage, valid:true) ' +
	 	': (color: red, showMessage:PROPERTY_NAME_TOO_LONG, valid:false)" ';

var SMW_REL_VALID_PROPERTY_VALUE =
	'smwValidValue="^.{1,255}$: valid ' +
		'? (color: white, hideMessage, valid:true) ' +
	 	': (color: orange, showMessage:PROPERTY_VALUE_TOO_LONG, valid:true)" ';

var SMW_REL_CHECK_PROPERTY = 
	'smwCheckType="property: exists ' +
		'? (color: lightgreen, hideMessage, valid:true) ' +
	 	': (color: orange, showMessage:PROPERTY_DOES_NOT_EXIST, valid:true)" ';

var SMW_REL_CHECK_PROPERTY_ACCESS = 
	'smwAccessControl="property: propertyedit ' +
		'? (color: lightgreen, hideMessage, valid:true) ' +
	 	': (color: red, showMessage:PROPERTY_ACCESS_DENIED, valid:false)" ';

var SMW_REL_CHECK_PROPERTY_UPDATE_SCHEMA = 
	'smwCheckType="property: exists ' +
		'? (color: lightgreen, hideMessage, valid:true, call:relToolBar.updateSchema, call:relToolBar.updateInstanceTypeHint) ' +
	 	': (color: orange, showMessage:PROPERTY_DOES_NOT_EXIST, valid:true, call:relToolBar.resetInstanceTypeHint)" ';

var SMW_REL_SUB_SUPER_CHECK_PROPERTY = 
	'smwCheckType="property: exists ' +
		'? (color: lightgreen, hideMessage, valid:true, attribute:propExists=true) ' +
	 	': (color: orange, hideMessage, valid:true, attribute:propExists=false)" ';

var SMW_REL_CHECK_PROPERTY_IIE = // Invalid if exists
	'smwCheckType="property: exists ' +
		'? (color: red, showMessage:PROPERTY_ALREADY_EXISTS, valid:false) ' +
	 	': (color: lightgreen, hideMessage, valid:true)" ';

var SMW_REL_VALID_CATEGORY_NAME =
	'smwValidValue="^[^<>\\|!&$%&\\/=\\?]{1,255}$: valid ' +
		'? (color: white, hideMessage, valid:true) ' +
	 	': (color: red, showMessage:CATEGORY_NAME_TOO_LONG, valid:false)" ';

var SMW_REL_CHECK_CATEGORY = 
	'smwCheckType="category: exists ' +
		'? (color: lightgreen, hideMessage, valid:true) ' +
	 	': (color: orange, showMessage:CATEGORY_DOES_NOT_EXIST, valid:true)" ';

var SMW_REL_CHECK_EMPTY = 
	'smwCheckEmpty="empty' +
		'? (color:red, showMessage:MUST_NOT_BE_EMPTY, valid:false) ' +
		': (color:white, hideMessage)"';

var SMW_REL_CHECK_EMPTY_NEV =   // NEV = Not Empty Valid i.e. valid if not empty
	'smwCheckEmpty="empty' +
		'? (color:red, showMessage:MUST_NOT_BE_EMPTY, valid:false, call:relToolBar.updateTypeHint) ' +
		': (color:white, hideMessage, valid:true, call:relToolBar.updateTypeHint)"';

var SMW_REL_CHECK_EMPTY_WIE =   // WIE = Warning if empty but still valid
	'smwCheckEmpty="empty' +
		'? (color:orange, showMessage:VALUE_IMPROVES_QUALITY) ' +
		': (color:white, hideMessage)"';

var SMW_REL_NO_EMPTY_SELECTION =
	'smwCheckEmpty="empty' +
	'? (color:red, showMessage:SELECTION_MUST_NOT_BE_EMPTY, valid:false) ' +
	': (color:white, hideMessage, valid:true)"';		

var SMW_REL_ALL_VALID =	
	'smwAllValid="allValid ' +
 		'? (show:rel-confirm, hide:rel-invalid) ' +
 		': (show:rel-invalid, hide:rel-confirm)"';

var SMW_REL_SUB_SUPER_ALL_VALID =	
	'smwAllValid="allValid ' +
 		'? (call:relToolBar.createSubSuperLinks) ' +
 		': (call:relToolBar.createSubSuperLinks)"';
 		
var positionFixed = (wgAction == 'annotate' || typeof FCKeditor != 'undefined' || typeof CKEDITOR != 'undefined') ? '" position="fixed"' : ''

var SMW_REL_HINT_CATEGORY =
	'constraints = "namespace:' + SMW_CATEGORY_NS + '"' + positionFixed;

var SMW_REL_HINT_PROPERTY =
	'constraints = "namespace:' + SMW_PROPERTY_NS + '"' + positionFixed;

var SMW_REL_HINT_INSTANCE =
	'constraints = "namespace:' + SMW_INSTANCE_NS + '"' + positionFixed;

var SMW_REL_TYPE_CHANGED =
	'smwChanged="(call:relToolBar.relTypeChanged)"';

RelationToolBar.prototype = {

initialize: function() {
	this.genTB = new GenericToolBar();
	this.toolbarContainer = null;
	this.showList = true;
	this.currentAction = "";
	this.relationsForAccessCheck = "";
	this.relationsForExistenceCheck = "";
},

showToolbar: function(){
	this.relationcontainer.setHeadline(gLanguage.getMessage('PROPERTIES'));
	if (wgAction == 'edit' || wgAction == 'formedit' || wgAction == 'submit' ||
            wgCanonicalSpecialPageName == 'AddData' || wgCanonicalSpecialPageName == 'EditData' ||
            wgCanonicalSpecialPageName == 'FormEdit' ) {
            // Create a wiki text parser for the edit mode. In annotation mode,
            // the mode's own parser is used.
            this.wtp = new WikiTextParser();
	}
	this.om = new OntologyModifier();
	this.fillList(true);
},

callme: function(event){
	if((wgAction == "edit" || wgAction == "annotate" || wgAction == 'formedit' || wgAction == 'submit' ||
            wgCanonicalSpecialPageName == 'AddData' || wgCanonicalSpecialPageName == 'EditData' ||
            wgCanonicalSpecialPageName == 'FormEdit' )
	    && typeof stb_control != 'undefined' && stb_control.isToolbarAvailable()){
		this.relationcontainer = stb_control.createDivContainer(RELATIONCONTAINER, 0);
		this.showToolbar();		
	}
},

fillList: function(forceShowList) {

	if (forceShowList == true) {
		this.showList = true;
	}
	if (!this.showList) {
		return;
	}
	if (this.wtp) {
		this.wtp.initialize();
		var relations = this.wtp.getRelations();
		var rels = '';
		var recRels = window.catToolBar.recommendedRels !== undefined ? 
			window.catToolBar.recommendedRels.clone() : new Array();
		for (var i = 0; i < relations.length; ++i) {
			rels += gLanguage.getMessage('PROPERTY_NS') + relations[i].getName()+',';
			// check if recommended relation is already set
			if( recRels !== undefined ) {
				for (var j = 0; j < recRels.length; ++j) {
					if( recRels[i] !== 'undefined' &&
						relations[i].getName().toLowerCase() === recRels[j].getName().toLowerCase()){
						// Annotaion already made. Ignore this recommendation
						recRels.splice( j, 1 );
					}
				}
			}
		}
		rels = rels.substr(0, rels.length-1);
		if (rels.length > 0 && rels != this.relationsForAccessCheck) {
			// Check if properties are protected by access control
			this.relationsForAccessCheck = rels;
			sajax_do_call('smwf_om_userCanMultiple',
			              [rels, "propertyedit"],
			              checkPropertyEditCallback.bind(this),
			              relations);
		}

		if (this.propertyRights
			&& this.propertyRights.length == relations.length) {
			for (var i = 0; i < relations.length; ++i) {
				relations[i].accessAllowed = this.propertyRights[i][1];
			}
		}

		if (rels.length > 0 && rels != this.relationsForExistenceCheck) {
			// Check if properties are already defined
			this.relationsForExistenceCheck = rels;
			sajax_do_call('smwf_om_ExistsArticleMultiple',
			              [rels],
			              checkPropertyExistCallback.bind(this),
			              relations);
		}
		if (this.propertyExists
			&& this.propertyExists.length == relations.length) {
			for (var i = 0; i < relations.length; ++i) {
				relations[i].exists = this.propertyExists[i][1];
			}
		}

		this.relationcontainer.setContent(
			this.genTB.createList(relations,"relation")
			+ this.genTB.createList(recRels, "rec-relation"));
		this.relationcontainer.contentChanged();
	}
	
	/**
	 * Closure:
	 * Callback function that gets the results of the access check for properties.
	 */
	function checkPropertyEditCallback(request) {
		
	
		if (request.status != 200) {
			// call for schema data failed, do nothing.
			return;
		}
	
		var rights = request.responseText.evalJSON(true);
		this.propertyRights = rights;

		var containsForbiddenProperties = false;
		for (var i = 0; i < relations.length; ++i) {
			relations[i].accessAllowed = rights[i][1];
			if (rights[i][1] == "false") {
				containsForbiddenProperties = true;
			}
		}
		
		refreshSTB.containsForbiddenProperties = containsForbiddenProperties;
		this.relationcontainer.setContent(this.genTB.createList(relations,"relation"));
		this.relationcontainer.contentChanged();
		refreshSTB.refreshToolBar();
		
	};

	/**
	 * Closure:
	 * Callback function that gets the results of the check for existence of properties.
	 */
	function checkPropertyExistCallback(request) {
	
		if (request.status != 200) {
			// call for schema data failed, do nothing.
			return;
		}
	
		var existence = request.responseText.evalJSON(true);
		this.propertyExists = existence;

		for (var i = 0; i < relations.length; ++i) {
			relations[i].exists = existence[i][1];
		}
		this.relationcontainer.setContent(this.genTB.createList(relations,"relation"));
		this.relationcontainer.contentChanged();
		refreshSTB.refreshToolBar();
	};
	
},

/**
 * @public 
 * 
 * Sets the wiki text parser <wtp>.
 * @param WikiTextParser wtp 
 * 		The parser that is used for this toolbar container.	
 * 
 */
setWikiTextParser: function(wtp) {
	this.wtp = wtp;
},

cancel: function(){
	
	/*STARTLOG*/
    smwhgLogger.log("","STB-Properties",this.currentAction+"_canceled");
	/*ENDLOG*/
	this.currentAction = "";

        this.toolbarContainer.hideSandglass();
        this.toolbarContainer.release();
        this.toolbarContainer = null;
	this.fillList(true);
},

/**
 * Creates a new toolbar for the relation container with the standard menu.
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
	
	this.toolbarContainer = new ContainerToolBar('relation-content',700,this.relationcontainer);
	var tb = this.toolbarContainer;
	tb.createContainerBody(attributes);
	return tb;
},

/**
 * Creates the content of a <contextMenuContainer> for annotating a property.
 * 
 * @param ContextMenuFramework contextMenuContainer
 * 		The container of the context menu.
 * @param string value (optional)
 * 		The default value for the property. If it is not given, the current 
 * 		selection of the wiki text parser is used.
 * @param string repr (optional)
 * 		The default representation for the property. If it is not given, the current 
 * 		selection of the wiki text parser is used.
 */
createContextMenu: function(contextMenuContainer, value, repr, name) {
	if (this.toolbarContainer) {
		this.toolbarContainer.release();
	}
	this.toolbarContainer = new ContainerToolBar('relation-content',500,contextMenuContainer);
	var tb = this.toolbarContainer;
	tb.createContainerBody(SMW_REL_ALL_VALID, RELATIONCONTAINER, gLanguage.getMessage('SPECIFY_PROPERTY'));

    this.wtp.initialize();
	this.currentAction = "annotate";

	var valueEditable = false;
	if (!value) {
		value = this.wtp.getSelection(true);
		repr = value;
		//replace newlines by spaces
		value = value.replace(/\n/g,' ');
		value = value.replace(/'''''/g,''); // replace bold&italic
		value = value.replace(/'''/g,'');   // replace bold
		value = value.replace(/''/g,'');    // replace italic
		
		valueEditable = true;
	}
	
	/*STARTLOG*/
    smwhgLogger.log(value,"AAM-Properties","annotate_clicked");
	/*ENDLOG*/
	
	tb.append(tb.createInput('rel-name', gLanguage.getMessage('PROPERTY'), '', '',
	                         SMW_REL_CHECK_PROPERTY_UPDATE_SCHEMA +
	                         SMW_REL_CHECK_PROPERTY_ACCESS +
	                         SMW_REL_CHECK_EMPTY +
	                         SMW_REL_VALID_PROPERTY_NAME +
	                         SMW_REL_HINT_PROPERTY,
	                         true));
	tb.setInputValue('rel-name', (name) ? name : '');
	tb.append(tb.createText('rel-name-msg', gLanguage.getMessage('ENTER_NAME'), '' , true));
	
	tb.append(tb.createInput('rel-value-0', gLanguage.getMessage('PAGE'), '', '',
							 SMW_REL_CHECK_EMPTY_NEV + 
							 SMW_REL_HINT_INSTANCE +
							 SMW_REL_VALID_PROPERTY_VALUE,
	                         true));
	tb.setInputValue('rel-value-0', value);
		                         
	tb.append(tb.createText('rel-value-0-msg', gLanguage.getMessage('ANNO_PAGE_VALUE'), '' , true));
	
	tb.append(tb.createInput('rel-show', gLanguage.getMessage('SHOW'), '', '', '', true));
	tb.setInputValue('rel-show', repr);

        // cancel and delete links dont work yet, disable it at the moment and change the link message for the addItem()

        // the property is selected and therefore exists already, get index of property in page
        /* var selindex = (name) ? this.wtp.getRelationIndex(name, value) : -1; */
        // idx != -1 -> property found, show change and delete links
        /*
        if (selindex != -1) {
		var links = [['relToolBar.changeItem('+selindex+')',gLanguage.getMessage('CHANGE'), 'rel-confirm',
		                                                    gLanguage.getMessage('INVALID_VALUES'), 'rel-invalid'],
					 ['relToolBar.deleteItem(' + selindex +')', gLanguage.getMessage('DELETE')],
					 ['relToolBar.cancel()', gLanguage.getMessage('CANCEL')]
					];
        // Property name is not set or index was not found -> new annotation. Show the link add only
        } else { */
            var links = [['relToolBar.addItem()',
                          (name) ? gLanguage.getMessage('CHANGE') : gLanguage.getMessage('ADD'), 'rel-confirm',
                          gLanguage.getMessage('INVALID_VALUES'), 'rel-invalid']];

        //}
	
	tb.append(tb.createLink('rel-links', links, '', true));
				
	tb.finishCreation();
	
	if (wgAction == 'annotate') {
		$('rel-show').disable();
		if (!valueEditable) {
			$('rel-value-0').disable();
		}
	}
	
//	$('relation-content-table-rel-show').hide();
	gSTBEventActions.initialCheck($("relation-content-box"));
	
	//Sets Focus on first Element
	setTimeout("if ($('rel-name')) $('rel-name').focus();",250);
	
},

addItem: function() {
	this.wtp.initialize();
	var name = $("rel-name").value;
	var value = this.getRelationValue();
	var text = $("rel-show").value;
	/*STARTLOG*/
    smwhgLogger.log(name+':'+value,"STB-Properties","annotate_added");
	/*ENDLOG*/
	//Check if Inputbox is empty
	if (name=="" || name == null ){
		alert(gLanguage.getMessage('INPUT_BOX_EMPTY'));
		return;
	}
	this.wtp.addRelation(name, value, text);
	this.fillList(true);
},

getRelationValue: function() {
	var i = 0;
	var value = "";
	while($("rel-value-"+i) != null) {
		value += $("rel-value-"+i).value + ";"
		i++;
	}
	value = value.substr(0, value.length-1); // remove last semicolon
	return value;
},

newItem: function() {
    this.wtp.initialize();
	this.showList = false;
	this.currentAction = "annotate";

	var selection = this.wtp.getSelection(true);
	/*STARTLOG*/
    smwhgLogger.log(selection,"STB-Properties","annotate_clicked");
	/*ENDLOG*/
	
	var tb = this.createToolbar(SMW_REL_ALL_VALID);	
	tb.append(tb.createText('rel-help_msg', gLanguage.getMessage('ANNOTATE_PROPERTY'), '' , true));
	tb.append(tb.createInput('rel-name', gLanguage.getMessage('PROPERTY'), '', '',
	                         SMW_REL_CHECK_PROPERTY_UPDATE_SCHEMA +
	                         SMW_REL_CHECK_PROPERTY_ACCESS +
	                         SMW_REL_CHECK_EMPTY +
	                         SMW_REL_VALID_PROPERTY_NAME +
	                         SMW_REL_HINT_PROPERTY,
	                         true));
	tb.setInputValue('rel-name','');
	tb.append(tb.createText('rel-name-msg', gLanguage.getMessage('ENTER_NAME'), '' , true));
	tb.append(tb.createInput('rel-value-0', gLanguage.getMessage('PAGE'), '', '', 
							 SMW_REL_CHECK_EMPTY_NEV +
							 SMW_REL_HINT_INSTANCE +
							 SMW_REL_VALID_PROPERTY_VALUE,
	                         true));
	tb.setInputValue('rel-value-0', selection);	                         
	                         
	tb.append(tb.createText('rel-value-0-msg', gLanguage.getMessage('ANNO_PAGE_VALUE'), '' , true));
	
	tb.append(tb.createInput('rel-show', gLanguage.getMessage('SHOW'), '', '', '', true));
	tb.setInputValue('rel-show','');
	
	var links = [['relToolBar.addItem()',gLanguage.getMessage('ADD'), 'rel-confirm', gLanguage.getMessage('INVALID_VALUES'), 'rel-invalid'],
				 ['relToolBar.cancel()', gLanguage.getMessage('CANCEL')]
				];
	
	tb.append(tb.createLink('rel-links', links, '', true));
				
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));
	
	//Sets Focus on first Element
	setTimeout("$('rel-name').focus();",50);
},

/* new function (derived from newItem) to add recommended properties */
recProp: function(propName) {
	this.wtp.initialize();
	this.showList = false;
	this.currentAction = "annotate";

//	var selection = this.wtp.getSelection(true);
	/*STARTLOG*/
	smwhgLogger.log(propName,"STB-Properties","rec_prop_clicked");
	/*ENDLOG*/
	
	var tb = this.createToolbar(SMW_REL_ALL_VALID);	
	tb.append(tb.createText('rel-help_msg', gLanguage.getMessage('ANNOTATE_PROPERTY'), '' , true));
	tb.append(tb.createInput('rel-name', gLanguage.getMessage('PROPERTY'), propName, '',
	                         SMW_REL_CHECK_PROPERTY_UPDATE_SCHEMA +
	                         SMW_REL_CHECK_PROPERTY_ACCESS +
	                         SMW_REL_CHECK_EMPTY +
	                         SMW_REL_VALID_PROPERTY_NAME +
	                         SMW_REL_HINT_PROPERTY,
	                         true));
	tb.setInputValue('rel-name', propName);
	tb.append(tb.createText('rel-name-msg', gLanguage.getMessage('ENTER_NAME'), '' , true));
	tb.append(tb.createInput('rel-value-0', gLanguage.getMessage('PAGE'), '', '', 
							 SMW_REL_CHECK_EMPTY_NEV +
							 SMW_REL_HINT_INSTANCE +
							 SMW_REL_VALID_PROPERTY_VALUE,
	                         true));
//	tb.setInputValue('rel-value-0', selection);
	                         
	tb.append(tb.createText('rel-value-0-msg', gLanguage.getMessage('ANNO_PAGE_VALUE'), '' , true));
	
	tb.append(tb.createInput('rel-show', gLanguage.getMessage('SHOW'), '', '', '', true));
	tb.setInputValue('rel-show','');
	
	var links = [['relToolBar.addItem()',gLanguage.getMessage('ADD'), 'rel-confirm', gLanguage.getMessage('INVALID_VALUES'), 'rel-invalid'],
				 ['relToolBar.cancel()', gLanguage.getMessage('CANCEL')]
				];
	
	tb.append(tb.createLink('rel-links', links, '', true));

	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));
	
	//Sets Focus on first Element
	setTimeout("$('rel-value-0').focus();",50);
},

updateSchema: function(elementID) {
	relToolBar.toolbarContainer.showSandglass(elementID);
	sajax_do_call('smwf_om_RelationSchemaData',
	              [$('rel-name').value],
	              relToolBar.updateNewItem.bind(relToolBar));
},

updateNewItem: function(request) {
	
	relToolBar.toolbarContainer.hideSandglass();
	if (request.status != 200) {
		// call for schema data failed, do nothing.
		return;
	}

	// defaults
	var arity = 2;
	var parameterNames = ["Page"];

	if (request.responseText != 'noSchemaData') {
		//TODO: activate annotate button
		var schemaData = GeneralXMLTools.createDocumentFromString(request.responseText);

		// read arity and parameter names
		a = parseInt(schemaData.documentElement.getAttribute("arity"));
		if (a > 0) {
			arity = a;
			parameterNames = [];
			for (var i = 0, n = schemaData.documentElement.childNodes.length; i < n; i++) {
				parameterNames.push(schemaData.documentElement.childNodes[i].getAttribute("name"));
			}
		}
	}
	// build new INPUT tags
	var selection = this.wtp.getSelection(true);
	var tb = this.toolbarContainer;
	
	// remove old input fields	
	var i = 0;
	var removeElements = new Array();
	var found = true;
	var oldValues = [];
	while (found) {
		found = false;
		var elem = $('rel-value-'+i);
		if (elem) {
			oldValues.push($('rel-value-'+i).value);
			removeElements.push('rel-value-'+i);
			found = true;
		}
		elem = $('rel-value-'+i+'-msg');
		if (elem) {
			removeElements.push('rel-value-'+i+'-msg');
			found = true;
		}
		++i;
	}
	tb.remove(removeElements);
	
	// create new input fields
	for (var i = 0; i < arity-1; i++) {
		insertAfter = (i==0) 
			? ($('rel-replace-all') 
				? 'rel-replace-all'
				: 'rel-name-msg' )
			: 'rel-value-'+(i-1)+'-msg';
		var value = (i == 0)
			? ((oldValues.length > 0)
				? oldValues[0]
				: selection)
			: ((oldValues.length > i)
				? oldValues[i]
				: '');
		var hint = SMW_REL_HINT_INSTANCE; //(parameterNames[i] == "Page" ? SMW_REL_HINT_INSTANCE : "");
		var pageIdx = parameterNames[i].indexOf("|Page");
		if (i == 0 &&  pageIdx > 0) {
			parameterNames[i] = parameterNames[i].substr(0, pageIdx);
			var relation = $('rel-name');
			hint = 'namespace:' + SMW_INSTANCE_NS;
			if (relation.value.length > 0) { 
				if (relation.value == gLanguage.getMessage('SUBPROPERTY_OF', 'cont')) {
					hint = 'namespace:' + SMW_PROPERTY_NS;
				} else {
					hint = 'instance-property-range:'+gLanguage.getMessage('PROPERTY_NS')+relation.value +
							'| ' + hint;
				}
			}
			hint = 'constraints="'+hint+'"';
		}
		tb.insert(insertAfter,
				  tb.createInput('rel-value-'+ i, parameterNames[i], '', '', 
								 SMW_REL_CHECK_EMPTY_NEV +
							     SMW_REL_VALID_PROPERTY_VALUE + 
								 hint,
		                         true));
//		console.log("updateNewItem: "+hint);
		                         
		tb.setInputValue('rel-value-'+ i, value);    
		                         
		tb.insert('rel-value-'+ i,
				  tb.createText('rel-value-'+i+'-msg', gLanguage.getMessage('ANNO_PAGE_VALUE'), '' , true));
		selection = "";
	}
	
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));
},

CreateSubSup: function() {

	this.showList = false;
	this.currentAction = "sub/super-category";

	this.wtp.initialize();
	var selection = this.wtp.getSelection(true);
	/*STARTLOG*/
    smwhgLogger.log(selection,"STB-Properties","sub/super-property_clicked");
	/*ENDLOG*/

	var tb = this.createToolbar(SMW_REL_SUB_SUPER_ALL_VALID);	
	tb.append(tb.createText('rel-help-msg', gLanguage.getMessage('DEFINE_SUB_SUPER_PROPERTY'), '' , true));
	tb.append(tb.createInput('rel-subsuper', 
							 gLanguage.getMessage('PROPERTY'), '', '',
	                         SMW_REL_SUB_SUPER_CHECK_PROPERTY +
	                         SMW_REL_CHECK_EMPTY +
	                         SMW_REL_VALID_PROPERTY_NAME +
	                         SMW_REL_HINT_PROPERTY,
	                         true));
	tb.setInputValue('rel-subsuper', selection);	                         
	tb.append(tb.createText('rel-subsuper-msg', gLanguage.getMessage('ENTER_NAME'), '' , true));
	
	tb.append(tb.createLink('rel-make-sub-link', 
	                        [['relToolBar.createSubItem()', gLanguage.getMessage('CREATE_SUB'), 'rel-make-sub']], 
	                        '', false));
	tb.append(tb.createLink('rel-make-super-link', 
	                        [['relToolBar.createSuperItem()', gLanguage.getMessage('CREATE_SUPER'), 'rel-make-super']],
	                        '', false));
	
	var links = [['relToolBar.cancel()', gLanguage.getMessage('CANCEL')]];
	tb.append(tb.createLink('rel-links', links, '', true));
				
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));
    
	//Sets Focus on first Element
	setTimeout("$('rel-subsuper').focus();",50);
},

createSubSuperLinks: function(elementID) {
	
	var exists = $("rel-subsuper").getAttribute("propExists");
	exists = (exists && exists == 'true');
	var tb = this.toolbarContainer;
	
	var title = $("rel-subsuper").value;
	
	if (title == '') {
		$('rel-make-sub').hide();
		$('rel-make-super').hide();
		return;
	}
	
	var superContent;
	var sub;
	if (!exists) {
		sub = gLanguage.getMessage('CREATE_SUB_PROPERTY');
		superContent = gLanguage.getMessage('CREATE_SUPER_PROPERTY');
	} else {
		sub = gLanguage.getMessage('MAKE_SUB_PROPERTY');
		superContent = gLanguage.getMessage('MAKE_SUPER_PROPERTY');
	}
	sub = sub.replace(/\$-title/g, title);
	superContent = superContent.replace(/\$-title/g, title);			                          
	if($('rel-make-sub').innerHTML != sub){
		var lnk = tb.createLink('rel-make-sub-link', 
								[['relToolBar.createSuperItem('+ (exists ? 'false' : 'true') + ')', sub, 'rel-make-sub']],
								'', true);
		tb.replace('rel-make-sub-link', lnk);
		lnk = tb.createLink('rel-make-super-link', 
							[['relToolBar.createSubItem()', superContent, 'rel-make-super']],
							'', true);
		tb.replace('rel-make-super-link', lnk);
	}
},
	
createSubItem: function(openTargetArticle) {
	
	if (openTargetArticle == undefined) {
		openTargetArticle = false;
	}
	var name = $("rel-subsuper").value;
	/*STARTLOG*/
    smwhgLogger.log(wgTitle+":"+name,"STB-Properties","sub-property_created");
	/*ENDLOG*/
	//Check if Inputbox is empty
	if(name=="" || name == null ){
		alert(gLanguage.getMessage('INPUT_BOX_EMPTY'));
		return;
	}
 	this.om.createSubProperty(name, "", openTargetArticle);
 	this.fillList(true);
},

createSuperItem: function(openTargetArticle) {
	if (openTargetArticle == undefined) {
		openTargetArticle = false;
	}
	var name = $("rel-subsuper").value;
	/*STARTLOG*/
    smwhgLogger.log(name+":"+wgTitle,"STB-Properties","super-property_created");
	/*ENDLOG*/
	//Check if Inputbox is empty
	if(name=="" || name == null ){
		alert(gLanguage.getMessage('INPUT_BOX_EMPTY'));
		return;
	}

 	this.om.createSuperProperty(name, "", openTargetArticle, this.wtp);
 	this.fillList(true);
},

/**
 * Sets the auto completion type hint of the relation name field depending on the
 * value of the element with ID <elementID>.
 * The following formats are supported:
 * - Dates (yyyy-mm-dd and dd-mm-yyyy, separator can be "-" , "/" and ".")
 * - Email addresses
 * - Numerical values with units of measurement
 * - Floats, integers
 * - Instances that belong to a category.
 * If no properties with these restrictions are found, all properties that match
 * a part of the entered property name are listed.  
 */
updateTypeHint: function(elementID) {
	var elem = $(elementID);
	var value = elem.value;
	var relation = $('rel-name');
	
	var hint = 'namespace:'+SMW_PROPERTY_NS;
	
	// Date: yyyy-mm-dd
	var date = value.match(/\d{1,5}[- \/.](0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])/);
	if (!date) {
		// Date: dd-mm-yyyy
		date = value.match(/(0[1-9]|[12][0-9]|3[01])[- \/.](0[1-9]|1[012])[- \/.]\d{1,5}/);
	} 
	var email = value.match(/^([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})$/i);
	var numeric = value.match(/([+-]?\d*(\.\d+([eE][+-]?\d*)?)?)\s*(.*)/);
	
	if (date) {
//		hint = '_dat;'+SMW_PROPERTY_NS;
		hint = 'schema-property-type:_dat|namespace:'+SMW_PROPERTY_NS;
	} else if (email) {
		hint = 'schema-property-type:_ema|namespace:'+SMW_PROPERTY_NS;
	} else if (numeric) {
		var number = numeric[1];
		var unit = numeric[4];
		var mantissa = numeric[2];
		if (number && unit) {
			var c = unit.charCodeAt(0);
			if (unit === "K" || unit === '°C' || unit === '°F' ||
				(c == 176 && unit.length == 2 && 
				 (unit.charAt(1) == 'C' || unit.charAt(1) == 'F'))) {
				hint = "schema-property-type:_tem|namespace:"+SMW_PROPERTY_NS;
			} else {
				hint = 'schema-property-type:'+unit+'|namespace:'+SMW_PROPERTY_NS;
			}
		} else if (number && mantissa) {
			hint = 'schema-property-type:_flt|namespace:'+SMW_PROPERTY_NS;
		} else if (number) {
			hint = 'schema-property-type:_num,_int,_flt|namespace:'+SMW_PROPERTY_NS;
		} else if (unit) {
			hint = 'schema-property-type:'+unit+'|namespace:'+SMW_PROPERTY_NS;
		}
	}
	
	// Prefer properties that belong to the categories that are currently annotated
	var categories = this.wtp.getCategories();
	var numCats = categories.length;
	if (numCats > 0) {
		var cats = "";
		var catNs = gLanguage.getMessage('CATEGORY_NS');
		for (var i = 0; i < numCats; ++i) {
			cats += 'schema-property-domain:'+catNs+categories[i].getName() + '|';
		}
		hint = cats + hint;
	}
	relation.setAttribute('constraints', hint);
//	console.log("updateTypeHint: "+hint);
	
},

updateInstanceTypeHint: function(elementID) {
	var relation = $('rel-name');
	var instance = $('rel-value-0');
	
	var hint = 'namespace:' + SMW_INSTANCE_NS;
	if (relation.value.length > 0) {
		if (relation.value == gLanguage.getMessage('SUBPROPERTY_OF', 'cont')) {
			hint = 'namespace:' + SMW_PROPERTY_NS;
		} else {
			hint = 'instance-property-range:'+gLanguage.getMessage('PROPERTY_NS')+relation.value +
					'| ' + hint;
		}
	}
	instance.setAttribute('constraints', hint);
//	console.log("updateInstanceTypeHint: "+hint);
	
},

resetInstanceTypeHint: function(elementID) {
	var instance = $('rel-value-0');
	var hint = 'namespace:' + SMW_INSTANCE_NS;
	instance.setAttribute('constraints', hint);
//	console.log("resetInstanceTypeHint: "+hint);
},

newRelation: function() {
    gDataTypes.refresh();
    
	this.showList = false;
	this.currentAction = "create";
	
    this.wtp.initialize();
	var selection = this.wtp.getSelection(true);
   
	/*STARTLOG*/
    smwhgLogger.log(selection,"STB-Properties","create_clicked");
	/*ENDLOG*/

	var domain = (wgNamespaceNumber == 14)
					? wgTitle  // current page is a category
					: "";
	var tb = this.createToolbar(SMW_REL_ALL_VALID);	
	tb.append(tb.createText('rel-help-msg', gLanguage.getMessage('CREATE_NEW_PROPERTY'), '' , true));
	tb.append(tb.createInput('rel-name', 
							 gLanguage.getMessage('PROPERTY'), '', '',
	                         SMW_REL_CHECK_PROPERTY_IIE +
	                         SMW_REL_CHECK_EMPTY+
	                         SMW_REL_VALID_PROPERTY_NAME +
	                         SMW_REL_HINT_PROPERTY,
	                         true));
	tb.setInputValue('rel-name', selection);	                         
	tb.append(tb.createText('rel-name-msg', gLanguage.getMessage('ENTER_NAME'), '' , true));
	
	tb.append(tb.createInput('rel-domain', gLanguage.getMessage('DOMAIN'), '', '', 
						     SMW_REL_CHECK_CATEGORY +
						     SMW_REL_VALID_CATEGORY_NAME + 
						     SMW_REL_CHECK_EMPTY_WIE +
						     SMW_REL_HINT_CATEGORY,
	                         true));
	tb.setInputValue('rel-domain', domain);	                         
	tb.append(tb.createText('rel-domain-msg', gLanguage.getMessage('ENTER_DOMAIN'), '' , true));

	this.addTypeInput();
		
	var links = [['relToolBar.addTypeInput()', gLanguage.getMessage('ADD_TYPE')]];
	tb.append(tb.createLink('rel-add-links', links, '', true));		
			
	links = [['relToolBar.createNewRelation()',
			  gLanguage.getMessage('CREATE'), 'rel-confirm', 
			  gLanguage.getMessage('INVALID_VALUES'), 'rel-invalid'],
			 ['relToolBar.cancel()', gLanguage.getMessage('CANCEL')]
			];
	tb.append(tb.createLink('rel-links', links, '', true));
	
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));
	

	//Sets Focus on first Element
	setTimeout("$('rel-name').focus();",50);

},

addTypeInput:function() {
	var i = 0;
	while($('rel-range-'+i) != null) {
		i++;
	}
	var tb = this.toolbarContainer;
	var insertAfter = (i==0) ? 'rel-domain-msg' 
							 : $('rel-range-'+(i-1)+'-msg') 
							 	? 'rel-range-'+(i-1)+'-msg'
							 	: 'rel-range-'+(i-1);
	
	var datatypes = this.getDatatypeOptions();
	var page = gLanguage.getMessage('TYPE_PAGE_WONS');
	var pIdx = datatypes.indexOf(page);
	tb.insert(insertAfter,
			  tb.createDropDown('rel-type-'+i, gLanguage.getMessage('TYPE'), 
	                            this.getDatatypeOptions(), 
	                            "relToolBar.removeType('rel-type-"+i+"')",
	                            pIdx, 
	                            SMW_REL_NO_EMPTY_SELECTION +
	                            SMW_REL_TYPE_CHANGED, true));
	var msgID = 'rel-type-'+i+'-msg';                           
	tb.insert('rel-type-'+i,
	          tb.createText(msgID, gLanguage.getMessage('ENTER_TYPE'), '' , true));

	tb.insert(msgID,
			  tb.createInput('rel-range-'+i, gLanguage.getMessage('RANGE'), '', '',
						     SMW_REL_CHECK_CATEGORY + SMW_REL_CHECK_EMPTY_WIE +
						     SMW_REL_VALID_CATEGORY_NAME + SMW_REL_HINT_CATEGORY,
	                         true));
	tb.setInputValue('rel-range-'+i, '');
	tb.insert('rel-range-'+i,
	          tb.createText('rel-range-'+i+'-msg', gLanguage.getMessage('ENTER_RANGE'), '' , true));
	          
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));
},

getDatatypeOptions: function() {
	var options = new Array();
	var builtinTypes = gDataTypes.getBuiltinTypes();
	var userTypes    = gDataTypes.getUserDefinedTypes();
	options = builtinTypes.concat([""], userTypes);
	return options;
},

removeType: function(id) {
	var typeInput = $(id);
	if (typeInput != null) {
		var tb = this.toolbarContainer;
		var rowsAfterRemoved = typeInput.parentNode.parentNode.nextSibling;

		// get ID of range input to be removed.
		var idOfValueInput = typeInput.getAttribute('id');
		var i = parseInt(idOfValueInput.substr(idOfValueInput.length-1, idOfValueInput.length));

		// remove it
		tb.remove(id);
		if ($(id+'-msg')) {
			tb.remove(id+'-msg');
		}
		var rid = id.replace(/type/, 'range');
		tb.remove(rid);
		if ($(rid+'-msg')) {
			tb.remove(rid+'-msg');
		}
		
		// remove gap from IDs
		id = idOfValueInput.substr(0, idOfValueInput.length-1);
		var obj;
		while ((obj = $(id + ++i))) {
			// is there a delete-button
			var delBtn = obj.up().up().down('a');
			if (delBtn) {
				var action = delBtn.getAttribute("href");
				var regex = new RegExp(id+i);
				action = action.replace(regex, id+(i-1));
				delBtn.setAttribute("href", action);
			}
			tb.changeID(obj, id + (i-1));
			if ((obj = $(id + i + '-msg'))) {
				tb.changeID(obj, id + (i-1) + '-msg');
			}
			var rid = id.replace(/type/, 'range');
			obj = $(rid + i);
			tb.changeID(obj, rid + (i-1));
			if ((obj = $(rid + i + '-msg'))) {
				tb.changeID(obj, rid + (i-1) + '-msg');
			}
			
		}
		tb.finishCreation();
		gSTBEventActions.initialCheck($("relation-content-box"));

	}

},

relTypeChanged: function(target) {
	var target = $(target);
	
	var typeIdx = target.id.substring(9);
	var rangeId = "rel-range-"+typeIdx;
	
	var attrType = target[target.selectedIndex].text;
	
	var isPage = attrType == gLanguage.getMessage('TYPE_PAGE_WONS');
	var tb = relToolBar.toolbarContainer;
	tb.show(rangeId, isPage);
	if (!isPage) {
		tb.show(rangeId+'-msg', false);
	}
	gSTBEventActions.initialCheck($("relation-content-box"));
	
},

createNewRelation: function() {
	var relName = $("rel-name").value;
	//Check if Inputbox is empty
	if(relName=="" || relName == null ){
		alert(gLanguage.getMessage('INPUT_BOX_EMPTY'));
		return;
    }
	// Create an ontology modifier instance
	var i = 0;

	// get all ranges and types
	var rangesAndTypes = new Array();
	while($('rel-type-'+i) != null) {
		var obj = $('rel-type-'+i);
		var value = obj.options[obj.selectedIndex].text;
		if (value != gLanguage.getMessage('TYPE_PAGE_WONS')) {
			rangesAndTypes.push(gLanguage.getMessage('TYPE_NS')+value); // add as type
		} else {
			var range = $('rel-range-'+i).value;
			rangesAndTypes.push((range && range != '')
									? gLanguage.getMessage('CATEGORY_NS')+range 	// add as category
			                        : "");
		}
		i++;
	}
	/*STARTLOG*/
	var signature = "";
	for (i = 0; i < rangesAndTypes.length; i++) {
		signature += (rangesAndTypes[i] != '') ? rangesAndTypes[i] : gLanguage.getMessage('TYPE_PAGE');
		if (i < rangesAndTypes.length-1) {
			signature += ', ';
		}
	}
    smwhgLogger.log(relName+":"+signature,"STB-Properties","create_added");
	/*ENDLOG*/

	this.om.createRelation(relName,
					       gLanguage.getMessage('CREATE_PROPERTY'),
	                       $("rel-domain").value, rangesAndTypes);
	//show list
	this.fillList(true);
},


changeItem: function(selindex) {
	this.wtp.initialize();
	//Get new values
	var relName = $("rel-name").value;
	var value = this.getRelationValue();
	var text = $("rel-show").value;

   	//Check if Inputbox is empty
	if(relName=="" || relName == null ){
		alert(gLanguage.getMessage('INPUT_BOX_EMPTY'));
		return;
	}

        //Get relations
        var annotatedElements = this.wtp.getRelations();

	if ((selindex!=null) && ( selindex >=0) && (selindex <= annotatedElements.length)  ){
		var relation = annotatedElements[selindex];
		/*STARTLOG*/
		var oldName = relation.getName();
		var oldValues = relation.getValue();
	    smwhgLogger.log(oldName+":"+oldValues+"->"+relName+":"+value,"STB-Properties","edit_annotation_change");
		/*ENDLOG*/
		if ($("rel-replace-all") && $("rel-replace-all").down('input').checked == true) {
			// rename all occurrences of the relation
			var relations = this.wtp.getRelation(relation.getName());
			for (var i = 0, len = relations.length; i < len; i++) {
				relations[i].rename(relName);
			}
		}
 		//change relation
 		if (relName == gLanguage.getMessage('SUBPROPERTY_OF', 'cont')) {
 			// Property is "Subproperty of" 
 			// => check if the value has the property namespace
 			var propNs = gLanguage.getMessage('PROPERTY_NS', 'cont');
 			if (value.indexOf(propNs) != 0) {
 				value = propNs + value;
 			}
 		}
		relation.update(relName, value, text);
	}

	//show list
	this.fillList(true);
},

deleteItem: function(selindex) {
	this.wtp.initialize();
	//Get relations
	var annotatedElements = this.wtp.getRelations();

	//delete relation
	if (   (selindex!=null)
	    && (selindex >=0)
	    && (selindex <= annotatedElements.length)  ){
		var anno = annotatedElements[selindex];
		var replText = (anno.getRepresentation() != "")
		               ? anno.getRepresentation()
		               : (anno.getValue() != ""
		                  ? anno.getValue()
		                  : "");
		/*STARTLOG*/
	    smwhgLogger.log(anno.getName()+":"+anno.getValue(),"STB-Properties","edit_annotation_delete");
		/*ENDLOG*/
		anno.remove(replText);
	}
	//show list
	this.fillList(true);
},

getselectedItem: function(selindex) {
	this.wtp.initialize();
    var renameAll = "";

	var annotatedElements = this.wtp.getRelations();
	if (   selindex == null
	    || selindex < 0
	    || selindex >= annotatedElements.length) {
		// Invalid index
		return;
	}
	this.showList = false;
	this.currentAction = "editannotation";
	
	var relation = annotatedElements[selindex];
	
	/*STARTLOG*/
    smwhgLogger.log(relation.getName()+":"+relation.getValue(),"STB-Properties","editannotation_clicked");
	/*ENDLOG*/
	
	var tb = this.createToolbar(SMW_REL_ALL_VALID);

	var relations = this.wtp.getRelation(relation.getName());
	if (relations.length > 1) {
	    renameAll = tb.createCheckBox('rel-replace-all', '', [gLanguage.getMessage('RENAME_ALL_IN_ARTICLE')], [], '', true);
	}

	function getSchemaCallback(request) {
		tb.hideSandglass();
		if (request.status != 200) {
			// call for schema data failed, do nothing.
			alert(gLanguage.getMessage('RETRIEVE_SCHEMA_DATA'));
			return;
		}

		var parameterNames = [];

		if (request.responseText != 'noSchemaData') {

			var schemaData = GeneralXMLTools.createDocumentFromString(request.responseText);
			if (schemaData.documentElement.tagName != 'parsererror') {
				// read parameter names
				for (var i = 0, n = schemaData.documentElement.childNodes.length; i < n; i++) {
					parameterNames.push(schemaData.documentElement.childNodes[i].getAttribute("name"));
				}
			}
		}
		if (parameterNames.size() == 0) {
			// schema data could not be retrieved for some reason (property may 
			// not yet exist). Show "Value" as default.
			for (var i = 0; i < relation.getArity()-1; i++) {
		 		parameterNames.push("Value");
			}
		}

		var valueInputs = new Array();
		var inputNames = new Array();
		for (var i = 0; i < relation.getArity()-1; i++) {
			var parName = (parameterNames.length > i) 
							? parameterNames[i]
							: "Page";
			var typeCheck = 'smwCheckType="' + 
			                parName.toLowerCase() + 
			                ': valid' +
	 						'? (color: lightgreen, hideMessage, valid:true)' +
			                ': (color: red, showMessage:INVALID_FORMAT_OF_VALUE, valid:false)" ';

			var obj = tb.createInput('rel-value-'+i, parName, 
									 relation.getSplitValues()[i], '', 
									 typeCheck +
							 		 SMW_REL_VALID_PROPERTY_VALUE +
									 (parName == "Page" ? SMW_REL_HINT_INSTANCE : "") ,true);

			valueInputs.push(obj);
			obj = tb.createText('rel-value-'+i+'-msg', '', '', true);
			valueInputs.push(obj);
			inputNames.push(['rel-value-'+i,relation.getSplitValues()[i]]);
		}
		tb.append(tb.createInput('rel-name', 
								 gLanguage.getMessage('PROPERTY'), '', '', 
								 SMW_REL_CHECK_PROPERTY_UPDATE_SCHEMA +
		                         SMW_REL_CHECK_PROPERTY_ACCESS +
		 						 SMW_REL_CHECK_EMPTY +
		                         SMW_REL_VALID_PROPERTY_NAME +
		 						 SMW_REL_HINT_PROPERTY,
		 						 true));
		tb.setInputValue('rel-name', relation.getName());	                         
		 						 
		tb.append(tb.createText('rel-name-msg', '', '' , true));
		if (renameAll !='') {
			tb.append(renameAll);
		}
		tb.append(valueInputs);
		for (var i = 0; i < inputNames.length; i++) {
			tb.setInputValue(inputNames[i][0],inputNames[i][1]);
		}
		
		// In the Advanced Annotation Mode the representation can not be changed
		var repr = relation.getRepresentation(); 
		if (wgAction == 'annotate') {
			if (repr == '') {
				// embrace further values
				var values = relation.getSplitValues();
				repr = values[0];
				if (values.size() > 1) {
					repr += ' (';
					for (var i = 1; i < values.size(); ++i) {
						repr += values[i];
						if (i < values.size()-1) {
							repr += ","
						}
					}
					repr += ')';
				}
			}
		}
		tb.append(tb.createInput('rel-show', gLanguage.getMessage('SHOW'), repr, '', '', true));
		tb.setInputValue('rel-show', repr);	                         

		var links = [['relToolBar.changeItem('+selindex+')',gLanguage.getMessage('CHANGE'), 'rel-confirm', 
		                                                    gLanguage.getMessage('INVALID_VALUES'), 'rel-invalid'],
					 ['relToolBar.deleteItem(' + selindex +')', gLanguage.getMessage('DELETE')],
					 ['relToolBar.cancel()', gLanguage.getMessage('CANCEL')]
					];
		tb.append(tb.createLink('rel-links', links, '', true));
		
		tb.finishCreation();
		if (wgAction == 'annotate') {
			$('rel-show').disable();
			$('rel-value-0').disable();
		}
		gSTBEventActions.initialCheck($("relation-content-box"));

		//Sets Focus on first Element
		setTimeout("$('rel-name').focus();",50);
	}
	tb.append(tb.createText('rel-help-msg', gLanguage.getMessage('CHANGE_PROPERTY'), '' , true));
	if(relation.getName().strip()!=""){
		this.toolbarContainer.showSandglass('rel-help-msg');
		sajax_do_call('smwf_om_RelationSchemaData', [relation.getName()], getSchemaCallback.bind(this));
	}
}

};// End of Class

var relToolBar = new RelationToolBar();
if (typeof FCKeditor == 'undefined')
    Event.observe(window, 'load', relToolBar.callme.bindAsEventListener(relToolBar));

