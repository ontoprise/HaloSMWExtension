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
var RelationToolBar = Class.create();

var SMW_REL_CHECK_PROPERTY = 
	'smwCheckType="property: exists ' +
		'? (color: lightgreen, hideMessage, valid:true) ' +
	 	': (color: orange, showMessage:PROPERTY_DOES_NOT_EXIST, valid:true)" ';

var SMW_REL_CHECK_PROPERTY_UPDATE_SCHEMA = 
	'smwCheckType="property: exists ' +
		'? (color: lightgreen, hideMessage, valid:true, call:relToolBar.updateSchema) ' +
	 	': (color: orange, showMessage:PROPERTY_DOES_NOT_EXIST, valid:true)" ';

var SMW_REL_SUB_SUPER_CHECK_PROPERTY = 
	'smwCheckType="property: exists ' +
		'? (color: lightgreen, hideMessage, valid:true, attribute:propExists=true) ' +
	 	': (color: orange, hideMessage, valid:true, attribute:propExists=false)" ';

var SMW_REL_CHECK_PROPERTY_IIE = // Invalid if exists
	'smwCheckType="property: exists ' +
		'? (color: red, showMessage:PROPERTY_ALREADY_EXISTS, valid:false) ' +
	 	': (color: lightgreen, hideMessage, valid:true)" ';

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
		'? (color:red, showMessage:MUST_NOT_BE_EMPTY, valid:false) ' +
		': (color:white, hideMessage, valid:true)"';

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
 		
var SMW_REL_CHECK_PART_OF_RADIO =
	'smwValid="relToolBar.checkPartOfRadio"';

var SMW_REL_HINT_CATEGORY =
	'typeHint = "' + SMW_CATEGORY_NS + '" ';

var SMW_REL_HINT_PROPERTY =
	'typeHint="'+ SMW_PROPERTY_NS + '" ';

var SMW_REL_HINT_INSTANCE =
	'typeHint="'+ SMW_INSTANCE_NS + '" ';
	

RelationToolBar.prototype = {

initialize: function() {
	this.genTB = new GenericToolBar();
	this.toolbarContainer = null;
	this.showList = true;
	this.currentAction = "";
},

showToolbar: function(){
	this.relationcontainer.setHeadline(gLanguage.getMessage('PROPERTIES'));
	this.wtp = new WikiTextParser();
	this.om = new OntologyModifier();
	this.fillList(true);

},

callme: function(event){
	if(wgAction == "edit"
	    && stb_control.isToolbarAvailable()){
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
	this.wtp.initialize();
	this.relationcontainer.setContent(this.genTB.createList(this.wtp.getRelations(),"relation"));
	this.relationcontainer.contentChanged();
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
    var html;
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
	                         SMW_REL_CHECK_EMPTY +
	                         SMW_REL_HINT_PROPERTY,
	                         true));
	tb.append(tb.createText('rel-name-msg', gLanguage.getMessage('ENTER_NAME'), '' , true));
	tb.append(tb.createInput('rel-value-0', gLanguage.getMessage('PAGE'), selection, '', 
							 SMW_REL_CHECK_EMPTY_NEV + SMW_REL_HINT_INSTANCE,
	                         true));
	tb.append(tb.createText('rel-value-0-msg', gLanguage.getMessage('ANNO_PAGE_VALUE'), '' , true));
	tb.append(tb.createInput('rel-show', gLanguage.getMessage('SHOW'), '', '', '', true));
	var links = [['relToolBar.addItem()',gLanguage.getMessage('ADD'), 'rel-confirm', gLanguage.getMessage('INVALID_VALUES'), 'rel-invalid'],
				 ['relToolBar.cancel()', gLanguage.getMessage('CANCEL')]
				];
	tb.append(tb.createLink('rel-links', links, '', true));
				
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));
	
	//Sets Focus on first Element
	setTimeout("$('rel-name').focus();",50);
},

updateSchema: function(elementID) {
	relToolBar.toolbarContainer.showSandglass(elementID);
	sajax_do_call('smwfRelationSchemaData',
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
		arity = parseInt(schemaData.documentElement.getAttribute("arity"));
		parameterNames = [];
		for (var i = 0, n = schemaData.documentElement.childNodes.length; i < n; i++) {
			parameterNames.push(schemaData.documentElement.childNodes[i].getAttribute("name"));
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
		tb.insert(insertAfter,
				  tb.createInput('rel-value-'+ i, parameterNames[i], value, '', 
								 SMW_REL_CHECK_EMPTY_NEV + 
								 (parameterNames[i] == "Page" ? SMW_REL_HINT_INSTANCE : ""),
		                         true));
		tb.insert('rel-value-'+ i,
				  tb.createText('rel-value-'+i+'-msg', gLanguage.getMessage('ANNO_PAGE_VALUE'), '' , true));
		selection = "";
	}
	
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));
},

CreateSubSup: function() {
    var html;

	this.showList = false;
	this.currentAction = "sub/super-category";

	this.wtp.initialize();
	var selection = this.wtp.getSelection(true);
	/*STARTLOG*/
    smwhgLogger.log(selection,"STB-Properties","sub/super-property_clicked");
	/*ENDLOG*/

	var tb = this.createToolbar(SMW_REL_SUB_SUPER_ALL_VALID);	
	tb.append(tb.createText('rel-help-msg', gLanguage.getMessage('DEFINE_SUB_SUPER_PROPERTY'), '' , true));
	tb.append(tb.createInput('rel-subsuper', gLanguage.getMessage('PROPERTY'), selection, '',
	                         SMW_REL_SUB_SUPER_CHECK_PROPERTY+SMW_REL_CHECK_EMPTY+
	                         SMW_REL_HINT_PROPERTY,
	                         true));
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
		openTargetArticle = true;
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
		openTargetArticle = true;
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

 	this.om.createSuperProperty(name, "", openTargetArticle);
 	this.fillList(true);
},

newRelation: function() {
    var html;
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
	tb.append(tb.createInput('rel-name', gLanguage.getMessage('PROPERTY'), selection, '',
	                         SMW_REL_CHECK_PROPERTY_IIE+SMW_REL_CHECK_EMPTY+
	                         SMW_REL_HINT_PROPERTY,
	                         true));
	tb.append(tb.createText('rel-name-msg', gLanguage.getMessage('ENTER_NAME'), '' , true));
	
	tb.append(tb.createInput('rel-domain', gLanguage.getMessage('DOMAIN'), domain, '', 
						     SMW_REL_CHECK_CATEGORY + SMW_REL_CHECK_EMPTY_WIE +
						     SMW_REL_HINT_CATEGORY,
	                         true));
	tb.append(tb.createText('rel-domain-msg', gLanguage.getMessage('ENTER_DOMAIN'), '' , true));
	
	tb.append(tb.createInput('rel-range-0', gLanguage.getMessage('RANGE'), '', 
							 "relToolBar.removeRangeOrType('rel-range-0')", 
						     SMW_REL_CHECK_CATEGORY + SMW_REL_CHECK_EMPTY_WIE +
						     SMW_REL_HINT_CATEGORY,
	                         true));
	tb.append(tb.createText('rel-range-0-msg', gLanguage.getMessage('ENTER_RANGE'), '' , true));
	
	var links = [['relToolBar.createNewRelation()',gLanguage.getMessage('CREATE'), 'rel-confirm', gLanguage.getMessage('INVALID_VALUES'), 'rel-invalid'],
				 ['relToolBar.cancel()', gLanguage.getMessage('CANCEL')]
				];
	tb.append(tb.createLink('rel-links', links, '', true));
	
	links = [['relToolBar.addRangeInput()',gLanguage.getMessage('ADD_RANGE')],
			 ['relToolBar.addTypeInput()', gLanguage.getMessage('ADD_TYPE')]
			];
	tb.append(tb.createLink('rel-add-links', links, '', true));		
			
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));
	

	//Sets Focus on first Element
	setTimeout("$('rel-name').focus();",50);

},

addRangeInput:function() {
	var i = 0;
	while($('rel-range-'+i) != null) {
		i++;
	}
	var tb = this.toolbarContainer;
	var insertAfter = (i==0) ? 'rel-domain-msg' 
							 : $('rel-range-'+(i-1)+'-msg') 
							 	? 'rel-range-'+(i-1)+'-msg'
							 	: 'rel-range-'+(i-1);
	
	tb.insert(insertAfter,
			  tb.createInput('rel-range-'+i, gLanguage.getMessage('RANGE'), '', 
                             "relToolBar.removeRangeOrType('rel-range-"+i+"')",
						     SMW_REL_CHECK_CATEGORY + SMW_REL_CHECK_EMPTY_WIE +
						     SMW_REL_HINT_CATEGORY,
	                         true));
	tb.insert('rel-range-'+i,
	          tb.createText('rel-range-'+i+'-msg', gLanguage.getMessage('ENTER_RANGE'), '' , true));
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));
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
	
	tb.insert(insertAfter,
			  tb.createDropDown('rel-range-'+i, gLanguage.getMessage('TYPE'), 
	                            this.getDatatypeOptions(), 
	                            "relToolBar.removeRangeOrType('rel-range-"+i+"')",
	                            0, 
	                            'isAttributeType="true" ' + 
	                            SMW_REL_NO_EMPTY_SELECTION, true));
	tb.insert('rel-range-'+i,
	          tb.createText('rel-range-'+i+'-msg', gLanguage.getMessage('ENTER_TYPE'), '' , true));
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

removeRangeOrType: function(id) {
	var rangeOrTypeInput = $(id);
	if (rangeOrTypeInput != null) {
		var tb = this.toolbarContainer;
		var rowsAfterRemoved = rangeOrTypeInput.parentNode.parentNode.nextSibling;

		// get ID of range input to be removed.
		var idOfValueInput = rangeOrTypeInput.getAttribute('id');
		var i = parseInt(idOfValueInput.substr(idOfValueInput.length-1, idOfValueInput.length));

		// remove it
		tb.remove(id);
		if ($(id+'-msg')) {
			tb.remove(id+'-msg');
		}
		
		// remove gap from IDs
		id = idOfValueInput.substr(0, idOfValueInput.length-1);
		var obj;
		while ((obj = $(id + ++i))) {
			// is there a delete-button
			var delBtn = obj.up().down('a');
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
		}
		tb.finishCreation();
		gSTBEventActions.initialCheck($("relation-content-box"));

	}

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
	while($('rel-range-'+i) != null) {
		if ($('rel-range-'+i).getAttribute("isAttributeType") == "true") {
			rangesAndTypes.push(gLanguage.getMessage('TYPE')+$('rel-range-'+i).value); // add as type
		} else {
			var range = $('rel-range-'+i).value;
			rangesAndTypes.push(range ? gLanguage.getMessage('CATEGORY')+range 	// add as category
			                          : "");
		}
		i++;
	}
	/*STARTLOG*/
	var signature = "";
	for (i = 0; i < rangesAndTypes.length; i++) {
		signature += (rangesAndTypes[i]) ? rangesAndTypes[i] : "Type:Page";
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
			editAreaLoader.execCommand(editAreaName, "resync_highlight(true)");
		}
 		//change relation
		relation.rename(relName);
		relation.changeValue(value);
		relation.changeRepresentation(text);
		
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

newPart: function() {
    var html;
    this.wtp.initialize();
    var selection = this.wtp.getSelection(true);

	/*STARTLOG*/
    smwhgLogger.log(selection,"STB-Properties","haspart_clicked");
	/*ENDLOG*/

	this.showList = false;
	this.currentAction = "haspart";

	var path = wgArticlePath;
	var dollarPos = path.indexOf('$1');
	if (dollarPos > 0) {
		path = path.substring(0, dollarPos);
	}
	var poLink = "<a href='"+wgServer+path+gLanguage.getMessage('PROP_HAS_PART')+ "' " +
			     "target='blank'> "+gLanguage.getMessage('HAS_PART')+"</a>";
	var bsuLink = "<a href='"+wgServer+path+gLanguage.getMessage('PROP_HBSU')+"' " +
			      "target='blank'> "+gLanguage.getMessage('HBSU')+"</a>";

	var tb = this.createToolbar(SMW_REL_ALL_VALID);	
	tb.append(tb.createText('rel-help-msg', gLanguage.getMessage('DEFINE_PART_OF'), '' , true));
	tb.append(tb.createText('rel-help-msg', wgTitle, '' , true));
	tb.append(tb.createRadio('rel-partof', '', [poLink, bsuLink], -1, 
							 SMW_REL_CHECK_PART_OF_RADIO, true));
	
	tb.append(tb.createInput('rel-name', gLanguage.getMessage('OBJECT'), selection, '',
	                         SMW_REL_CHECK_EMPTY_NEV + SMW_REL_HINT_INSTANCE,
	                         true));
	tb.append(tb.createText('rel-name-msg', '', '' , true));
	tb.append(tb.createInput('rel-show', gLanguage.getMessage('SHOW'), '', '', '', true));
	var links = [['relToolBar.addPartOfRelation()',gLanguage.getMessage('ADD'), 'rel-confirm', 
	                                               gLanguage.getMessage('INVALID_VALUES'), 'rel-invalid'],
				 ['relToolBar.cancel()', gLanguage.getMessage('CANCEL')]
				];
	tb.append(tb.createLink('rel-links', links, '', true));
				
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));

	//Sets Focus on first Element
	setTimeout("$('rel-partof').focus();",50);
},

checkPartOfRadio: function(element) {
	var element = $(element).elements["rel-partof"];
	if (element[0].checked == true || element[1].checked == true) {
		return true;
	}
	return false;
},

addPartOfRelation: function() {
	var element = $('rel-partof').elements["rel-partof"];
	var poType = "";
	if (element[0].checked == true) {
		poType = gLanguage.getMessage('HAS_PART');
	} else if (element[1].checked == true) {
		poType = gLanguage.getMessage('HBSU');
	}

	var obj = $("rel-name").value;
	/*STARTLOG*/
    smwhgLogger.log(poType+":"+obj,"STB-Properties","haspart_added");
	/*ENDLOG*/
	if (obj == "") {
		alert(gLanguage.getMessage('NO_OBJECT_FOR_POR'));
	}
	var show = $("rel-show").value;

	this.wtp.initialize();
	this.wtp.addRelation(poType, obj, show, false);
	this.fillList(true);
},

getselectedItem: function(selindex) {
	this.wtp.initialize();
	var html;
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

			// read parameter names
			parameterNames = [];
			for (var i = 0, n = schemaData.documentElement.childNodes.length; i < n; i++) {
				parameterNames.push(schemaData.documentElement.childNodes[i].getAttribute("name"));
			}
		} else { // schema data could not be retrieved for some reason (property may not yet exist). Show "Value" as default.
			for (var i = 0; i < relation.getArity()-1; i++) {
		 		parameterNames.push("Value");
			}
		}

		var valueInputs = new Array();
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
									 (parName == "Page" ? SMW_REL_HINT_INSTANCE : "") ,true);
			valueInputs.push(obj);
			obj = tb.createText('rel-value-'+i+'-msg', '', '', true);
			valueInputs.push(obj);
		}
		tb.append(tb.createInput('rel-name', gLanguage.getMessage('PROPERTY'), relation.getName(), '', 
								 SMW_REL_CHECK_PROPERTY_UPDATE_SCHEMA +
		 						 SMW_REL_CHECK_EMPTY +SMW_REL_HINT_PROPERTY,
		 						 true));
		tb.append(tb.createText('rel-name-msg', '', '' , true));
		if (renameAll !='') {
			tb.append(renameAll);
		}
		tb.append(valueInputs);
		tb.append(tb.createInput('rel-show', gLanguage.getMessage('SHOW'), relation.getRepresentation(), '', '', true));

		var links = [['relToolBar.changeItem('+selindex+')',gLanguage.getMessage('CHANGE'), 'rel-confirm', 
		                                                    gLanguage.getMessage('INVALID_VALUES'), 'rel-invalid'],
					 ['relToolBar.deleteItem(' + selindex +')', gLanguage.getMessage('DELETE')],
					 ['relToolBar.cancel()', gLanguage.getMessage('CANCEL')]
					];
		tb.append(tb.createLink('rel-links', links, '', true));
		
		tb.finishCreation();
		gSTBEventActions.initialCheck($("relation-content-box"));

		//Sets Focus on first Element
		setTimeout("$('rel-name').focus();",50);
	}
	tb.append(tb.createText('rel-help-msg', gLanguage.getMessage('CHANGE_PROPERTY'), '' , true));
	if(relation.getName().strip()!=""){
		this.toolbarContainer.showSandglass('rel-help-msg');
		sajax_do_call('smwfRelationSchemaData', [relation.getName()], getSchemaCallback.bind(this));
	}
}

};// End of Class

var relToolBar = new RelationToolBar();
Event.observe(window, 'load', relToolBar.callme.bindAsEventListener(relToolBar));

