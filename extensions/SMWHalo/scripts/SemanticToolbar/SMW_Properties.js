/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @ingroup SMWHaloSemanticToolbar
 * @author Thomas Schweitzer
 */

var SMW_PRP_ALL_VALID =	
	'smwAllValid="allValid ' +
 		'? (show:prop-confirm, hide:prop-invalid) ' +
 		': (show:prop-invalid, hide:prop-confirm)"';
 		
 		
var SMW_PRP_CHECK_MAX_CARD =
	'smwValid="propToolBar.checkMaxCard"';

var SMW_PRP_CARD_EMPTY =
	'smwCheckEmpty="empty' +
		'? (call:propToolBar.cardinalityChanged) ' +
		': ()"';
	
var SMW_PRP_CARDINALITY_CHANGED =
	'smwChanged="(call:propToolBar.cardinalityChanged)"';

var SMW_PRP_VALID_CATEGORY_NAME =
	'smwValidValue="^[^<>\|!&$%&=\?]{1,255}$: valid ' +
		'? (color: white, hideMessage, valid:true) ' +
	 	': (color: red, showMessage:CATEGORY_NAME_TOO_LONG, valid:false)" ';

var SMW_PRP_CHECK_CATEGORY = 
	'smwCheckType="category: exists ' +
		'? (color: lightgreen, hideMessage, valid:true) ' +
	 	': (color: orange, showMessage:CATEGORY_DOES_NOT_EXIST, valid:true)" ';

var SMW_PRP_CHECK_PROPERTY = 
	'smwCheckType="property: exists ' +
		'? (color: lightgreen, hideMessage, valid:true) ' +
	 	': (color: orange, showMessage:PROPERTY_DOES_NOT_EXIST, valid:true)" ';

var SMW_PRP_VALID_PROPERTY_NAME =
	'smwValidValue="^[^<>\|!&$%&=\?]{1,255}$: valid ' +
		'? (color: white, hideMessage, valid:true) ' +
	 	': (color: red, showMessage:PROPERTY_NAME_TOO_LONG, valid:false)" ';

var SMW_PRP_VALID_FIELD_PROPERTY_NAME =
	'smwValidValue="^[^<>\|!&$%&=\?]{1,255}$: valid ' +
		'? (hideMessage, call:propToolBar.recordFieldChanged) ' +
	 	': (color: red, showMessage:PROPERTY_NAME_TOO_LONG, valid:false)" ';

//var positionFixed = (typeof FCKeditor != 'undefined' || typeof CKEDITOR !=  'undefined') ? 'position="fixed"' : ''
var positionFixed = ' position="fixed"';
var SMW_PRP_HINT_CATEGORY =
	'constraints = "namespace:' + SMW_CATEGORY_NS + '" ' + positionFixed;

var SMW_PRP_HINT_PROPERTY =
	'constraints = "namespace:'+ SMW_PROPERTY_NS + '" ' + positionFixed;
	
var SMW_PRP_CHECK_FIELD_EMPTY = 
	'smwCheckEmpty="empty' +
		'? (color:red, showMessage:MUST_NOT_BE_EMPTY, valid:false, call:propToolBar.recordFieldChanged) ' +
		': (color:white, hideMessage)"';
		
var SMW_PRP_CHECK_EMPTY_DOMAIN_WIE =   // WIE = Warning if empty but still valid
	'smwCheckEmpty="empty' +
		'? (color:orange, showMessage:VALUE_IMPROVES_QUALITY, call:propToolBar.domainChanged) ' +
		': (color:white, hideMessage, call:propToolBar.domainChanged)"';

var SMW_PRP_CHECK_EMPTY_RANGE_WIE =   // WIE = Warning if empty but still valid
	'smwCheckEmpty="empty' +
		'? (color:orange, showMessage:VALUE_IMPROVES_QUALITY, call:propToolBar.rangeChanged) ' +
		': (color:white, hideMessage, call:propToolBar.rangeChanged)"';

var SMW_PRP_CHECK_INVERSE_EMPTY_VIE = // valid if empty
	'smwCheckEmpty="empty' +
		'? (color:white, hideMessage, valid:true, call:propToolBar.inverseChanged) ' +
		': (call:propToolBar.inverseChanged)"';
		
var SMW_PRP_NO_EMPTY_SELECTION =
	'smwCheckEmpty="empty' +
	'? (color:red, showMessage:SELECTION_MUST_NOT_BE_EMPTY, valid:false) ' +
	': (color:white, hideMessage, valid:true)"';

var SMW_PRP_TYPE_CHANGED =
	'smwChanged="(call:propToolBar.propTypeChanged)"';
	
var PRP_APPLY_LINK =
	[['propToolBar.apply()', 'Apply', 'prop-confirm', gLanguage.getMessage('INVALID_VALUES'), 'prop-invalid'],
	 ['propToolBar.cancel()', gLanguage.getMessage('CANCEL')]
	];

window.PropertiesToolBar = Class.create();

PropertiesToolBar.prototype = {

initialize: function() {
	//Reference
	this.genTB = new GenericToolBar();
	this.toolbarContainer = null;
	this.pendingIndicator = null;
	this.hasDuplicates = false;
	this.propertyDefinition = null;  // The current definition of the property
	this.uiElements = {}; // This object serves as map from groups of UI elements
						  // to an array of element IDs
},

showToolbar: function(request){
	if (this.propertiescontainer == null) {
		return;
	}
	this.propertiescontainer.setHeadline(gLanguage.getMessage('PROPERTY_PROPERTIES'));
	this.wtp = new WikiTextParser();
	this.om = new OntologyModifier();
	
	this.createContent();
	
},

initToolbox: function(event){
	
	if( (wgAction == "edit" || wgAction == 'formedit' || wgAction == 'submit' ||
             wgCanonicalSpecialPageName == 'AddData' || wgCanonicalSpecialPageName == 'EditData' ||
             wgCanonicalSpecialPageName == 'FormEdit' )
	   && (wgNamespaceNumber == 100 || wgNamespaceNumber == 102
	       || (typeof sfgTargetNamespaceNumber != 'undefined' && sfgTargetNamespaceNumber == 102))
	   && stb_control.isToolbarAvailable()){
		this.propertiescontainer = stb_control.createDivContainer(PROPERTIESCONTAINER, 0);

		// Events can not be registered in onLoad => make a timeout
		setTimeout("propToolBar.showToolbar();",1);	
	}	
},

/**
 * Creates the content of the Property Properties container. 
 */
createContent: function() {

	if (this.propertiescontainer == null) {
		return;
	}
	this.wtp.initialize();
	var propertyDefinition = new PropertyDefinition();
	propertyDefinition.updateFromWikiText(this.wtp);
	// Check if some property characteristic are given several times
	var doubleDefinition = propertyDefinition.findDoubleDefinitions();

	if (doubleDefinition !== false) {
		this.showErrorMsg(doubleDefinition);
		this.hasDuplicates = true;
		return;
	}
	
	// Check if the property definition has changed since the last time.
	var changed = (this.propertyDefinition === null) 
					? true
					: !propertyDefinition.equals(this.propertyDefinition);
	this.propertyDefinition = propertyDefinition;
	                    
	changed |= this.hasDuplicates; // Duplicates have been removed
	this.hasDuplicates = false;
	
	if (!changed) {
		// nothing changed
		return;
	}
	// Refresh the container content with the current property definition
	this.refreshContainer();
	
	//Sets Focus on first Element
//	setTimeout("$('prp-domain').focus();",50);
    
},

/**
 * Shows the an error message which uses the complete Properties toolbox.
 * @param {String} errorMsg
 * 		An HTML formatted string with the error message.
 */
showErrorMsg: function (errorMsg) {
	if (this.toolbarContainer) {
		this.toolbarContainer.release();
	}
	this.toolbarContainer = new ContainerToolBar('properties-content',800,this.propertiescontainer);
	this.toolbarContainer.createContainerBody(SMW_PRP_ALL_VALID);
	this.toolbarContainer.append(errorMsg);
	this.toolbarContainer.finishCreation();
	
},

/**
 * Refreshed the content of the toolbar container based on the current
 * property definition.
 * 
 */
refreshContainer: function () {
	if (this.toolbarContainer) {
		this.toolbarContainer.release();
	}
	this.toolbarContainer = new ContainerToolBar('properties-content',800,this.propertiescontainer);
	var tb = this.toolbarContainer;
	tb.createContainerBody(SMW_PRP_ALL_VALID);
	
	// Reset the map of UI IDs
	this.uiElements = {};
	
	this.showAllDomainsAndRanges();
	this.showType();
	if (this.propertyDefinition.isRecord()) {
		// show the record's fields
		this.showAllFields();
	} else if (this.propertyDefinition.isRelation()) {
		// Property is of type page
		this.showInverse();
		this.showTransSymm();
		this.showTSFeatures();
	}
	this.showCardinality();
	this.showActions();
	
	tb.finishCreation();
	gSTBEventActions.initialCheck($("properties-content-box"));
},

/**
 * Renders all domains and ranges of the current property definition in the object's
 * toolbar.
 *  
 */
showAllDomainsAndRanges: function () {
	var type = this.propertyDefinition.getType();
	var domainsAndRanges = this.propertyDefinition.getDomainsAndRanges();
	
	var tb = this.toolbarContainer;
	// Show a link for creating new domains and ranges and a horizontal line
	var linkSpec = [['propToolBar.addDomainRange()',
					 gLanguage.getMessage('ADD_DOMAIN_RANGE'), 
					 'prp-add-dom-ran-lnk']];

	tb.append(tb.createLink('prp-add-domran-link', linkSpec, '', true));
	tb.append(tb.createHorizontalLine('prp-dr-top-sep', '', true));
	
	if (domainsAndRanges === null) {
		return;
	}
	
	// Show all domains and ranges
	for (var i = 0; i < domainsAndRanges.length; ++i) {
		this.showDomainAndRange(i);
	}

},

/**
 * Adds the UI elements for ONE domain and range specification.
 * All created UI elements are stored in this.uiElements with the index 
 * "domainRange{drIndex}" e.g. domainRange1
 * 
 * @param {int} drIndex
 * 		Index of the specification
 */
showDomainAndRange: function (drIndex) {
	var uiids = [];
	var insAfter = "";
	var tb = this.toolbarContainer;
	var isRelation = this.propertyDefinition.isRelation();
	var domainsAndRanges = this.propertyDefinition.getDomainsAndRanges();

	if (domainsAndRanges === null 
		|| drIndex >= domainsAndRanges.length 
		|| typeof domainsAndRanges[drIndex] === 'undefined') {
		return;
	}
	
	// Find the correct place to insert the elements
	// find the first valid insertion slot going backwards from the given
	// index
	var prevIdx = drIndex;
	while (prevIdx >= 0) {
		if (tb.existsElement('prp-dr-' + prevIdx + '-sep')) {
			break;
		}
		--prevIdx;
	}
	insAfter = (prevIdx < 0) ? 'prp-dr-top-sep' : 'prp-dr-' + prevIdx + '-sep';

	// Show domain
	var domID = 'prp-domain-' + drIndex;
	var html = tb.createInput(
			domID, gLanguage.getMessage('DOMAIN'), '', '',
	        SMW_PRP_CHECK_CATEGORY + 
	        SMW_PRP_VALID_CATEGORY_NAME +
	        SMW_PRP_CHECK_EMPTY_DOMAIN_WIE + 
	        SMW_PRP_HINT_CATEGORY,
	        true);
	                         
	html += tb.createText(domID + '-msg', '', '' , true);
	
	uiids.push(domID);
	uiids.push(domID + '-msg');
	
	// Show range
	var rangeID = 'prp-range-' + drIndex;
	html += tb.createInput(
			rangeID, gLanguage.getMessage('RANGE'), '', '',
			SMW_PRP_CHECK_CATEGORY + 
			SMW_PRP_VALID_CATEGORY_NAME +
			SMW_PRP_CHECK_EMPTY_RANGE_WIE +
			SMW_PRP_HINT_CATEGORY,
			isRelation);
	html += tb.createText(rangeID + '-msg', '', '' , isRelation);

	uiids.push(rangeID);
	uiids.push(rangeID + '-msg');

	// Show delete link and horizontal line
	var linkSpec = [['propToolBar.removeDomainRange('+drIndex+')',
					 gLanguage.getMessage('REMOVE_DOMAIN_RANGE'), 
					 'prp-remove-dom-ran-lnk'+drIndex]];

	html += tb.createLink('prp-remove-domran-link-'+drIndex, linkSpec, '', true);
	html += tb.createHorizontalLine('prp-dr-' + drIndex + '-sep', '', true);
	
	uiids.push('prp-remove-domran-link-'+drIndex);
	uiids.push('prp-dr-' + drIndex + '-sep');

	tb.insert(insAfter, html);
	tb.setInputValue(rangeID, domainsAndRanges[drIndex].range);	                         
	tb.setInputValue(domID, domainsAndRanges[drIndex].domain);	                         
	
	this.uiElements['domainRange'+drIndex] = uiids;
	
},

/**
 * Renders the type of the current property definition in the object's toolbar.
 *  
 */
showType: function () {
	var type = this.propertyDefinition.getType();
	var tb = this.toolbarContainer;
	tb.append(this.createTypeSelector("prp-type", type,
	                                  SMW_PRP_NO_EMPTY_SELECTION+
	                                  SMW_PRP_TYPE_CHANGED));
},

/**
 * Renders the cardinality of the current property definition in the object's toolbar.
 */
showCardinality: function () {
	var minCard = this.propertyDefinition.getMinCard();
	var maxCard = this.propertyDefinition.getMaxCard();
	var tb = this.toolbarContainer;
	tb.append(
		tb.createInput(
			'prp-min-card', 
			gLanguage.getMessage('PC_MIN_CARD'), '', '', 
			SMW_PRP_CHECK_MAX_CARD +
			SMW_PRP_CARD_EMPTY +
			SMW_PRP_CARDINALITY_CHANGED, 
			true, false));
	tb.setInputValue('prp-min-card',minCard);	                         
	tb.append(tb.createText('prp-min-card-msg', '', '' , true));
	
	tb.append(
		tb.createInput(
			'prp-max-card', 
			gLanguage.getMessage('PC_MAX_CARD'), '', '', 
			SMW_PRP_CHECK_MAX_CARD +
			SMW_PRP_CARD_EMPTY +
			SMW_PRP_CARDINALITY_CHANGED, 
			true, false));
	tb.setInputValue('prp-max-card',maxCard);	                         
	tb.append(tb.createText('prp-max-card-msg', '', '' , true));
},

/**
 * Renders the inverse property of the current property definition in the object's toolbar.
 */
showInverse: function () {
	var inverse = this.propertyDefinition.getInverse();
	var tb = this.toolbarContainer;
	tb.append(tb.createInput('prp-inverse-of', gLanguage.getMessage('INVERSE_OF'), '', '',
	                         SMW_PRP_CHECK_PROPERTY +
	                         SMW_PRP_VALID_PROPERTY_NAME +
	                         SMW_PRP_HINT_PROPERTY+
	                         SMW_PRP_CHECK_INVERSE_EMPTY_VIE,
	                         true));
	tb.setInputValue('prp-inverse-of',inverse);	                         
	                         
	tb.append(tb.createText('prp-inverse-of-msg', '', '' , true));
	
},

/**
 * Renders the transitive and symmetric properties of the current property 
 * definition in the object's toolbar.
 */
showTransSymm: function () {
	var t = this.propertyDefinition.isTransitive();
	var s = this.propertyDefinition.isSymmetric();
	var tb = this.toolbarContainer;
	tb.append(tb.createCheckBox('prp-transitive', '', [gLanguage.getMessage('TRANSITIVE')], [t ? 0 : -1], 'name="transitive"', true));
	tb.append(tb.createCheckBox('prp-symmetric', '', [gLanguage.getMessage('SYMMETRIC')], [s ? 0 : -1], 'name="symmetric"', true));
	
},

/**
 * Renders the record fields of the current property definition in the object's 
 * toolbar.
 */
showAllFields: function () {
	var tb = this.toolbarContainer;
	var fields = this.propertyDefinition.getFields();
	
	if (fields !== null) {
		// Show all fields
		for (var i = 0; i < fields.length; ++i) {
			this.showField(i);
		}
	} else {
		// Add a default input field for the first property of the record
		this.addRecordField();
	}
	
	// Show a link for creating a new record field
	var linkSpec = [['propToolBar.addRecordField()',
					 gLanguage.getMessage('ADD_RECORD_FIELD'), 
					 'prp-add-record-field-lnk']];

	tb.append(tb.createLink('prp-add-rf-link', linkSpec, '', true));
	
},

/**
 * Adds the UI elements for ONE record field.
 * All created UI elements are stored in this.uiElements with the index 
 * "recordField{fieldIndex}" e.g. recordField1
 * 
 * @param {int} fieldIndex
 * 		Index of the field
 */
showField: function (fieldIndex) {
	var uiids = [];
	var insAfter = "";
	var tb = this.toolbarContainer;
	var fields = this.propertyDefinition.getFields();
	if (fields === null 
		|| fieldIndex >= fields.length 
		|| typeof fields[fieldIndex] === 'undefined') {
		return;
	}
	
	// Find the correct place to insert the elements.
	// Find the first valid insertion slot going backwards from the given
	// index
	var prevIdx = fieldIndex;
	while (prevIdx >= 0) {
		if (tb.existsElement('prp-field-' + prevIdx + '-msg')) {
			break;
		}
		--prevIdx;
	}
	// The first record field will be inserted after the type selector
	insAfter = (prevIdx < 0) ? 'prp-type' : 'prp-field-' + prevIdx + '-msg';

	// Generate the HTML for the field
	var fieldID = 'prp-field-' + fieldIndex;
	var html = tb.createInput(
				fieldID, 
				gLanguage.getMessage('PC_HAS_FIELDS'), '', 
				"propToolBar.removeRecordField('" + fieldID + "')",
				SMW_PRP_VALID_FIELD_PROPERTY_NAME +
				SMW_PRP_HINT_PROPERTY +
				SMW_PRP_CHECK_FIELD_EMPTY, true);
	html += tb.createText(fieldID + '-msg', '', '' , true);
	
	uiids.push(fieldID);
	uiids.push(fieldID + '-msg');

	tb.insert(insAfter, html);
	tb.setInputValue(fieldID, fields[fieldIndex]);
	
	this.uiElements['recordField'+fieldIndex] = uiids;
	
},


/**
 * Renders information about the capabilities of the connected triple store.
 */
showTSFeatures: function () {
	
	var tb = this.toolbarContainer;
	tb.append(tb.createText('prp-no_ts_reasoning-msg', '', '' , false));

	sajax_do_call('smwf_tb_getTripleStoreStatus', [], showTriplestoreFeatures.bind(this));
	
	// Callback function for the triple store features
	function showTriplestoreFeatures(request) {
		if (request.status != 200) {
			return;
		}

		if (request.responseText == 'false') {
			msg = [gLanguage.getMessage('PC_INVERSE'), 
				   gLanguage.getMessage('PC_TRANSITIVE'),
				   gLanguage.getMessage('PC_SYMMETRICAL')];
		} else {
			var tsFeatures = request.responseText.evalJSON();
			var msg = [];
			if (tsFeatures.features.indexOf('INVERSE') == -1) {
				msg.push(gLanguage.getMessage('PC_INVERSE'));
			}
			if (tsFeatures.features.indexOf('TRANSITIVE') == -1) {
				msg.push(gLanguage.getMessage('PC_TRANSITIVE'));
			}
			if (tsFeatures.features.indexOf('SYMETRICAL') == -1) {
				msg.push(gLanguage.getMessage('PC_SYMMETRICAL'));
			}
		}
		if (msg.size() == 0) {
			msg = ''; 
		} else {
			if (msg.size() == 3) {
				msg = msg[0]+', '+msg[1]+' '+gLanguage.getMessage('PC_AND')+' '+msg[2];
			} else if (msg.size() == 2) {
				msg = msg[0]+' '+gLanguage.getMessage('PC_AND')+' '+msg[1];
			} else if (msg.size() == 1) {
				msg = msg[0];
			}
			msg = gLanguage.getMessage('PC_UNSUPPORTED').replace(/\$1/g, msg);
		} 
		var msgElem = $('prp-no_ts_reasoning-msg');
		if (msgElem) {
			var tbc = smw_ctbHandler.findContainer(msgElem);
			var visible = tbc.isVisible(msgElem.id);
			tbc.replace(msgElem.id,
			            tbc.createText(msgElem.id, msg, '' , true));
		 	tbc.show(msgElem.id, msg.length>0);
		}
		
	}

},

/**
 * Renders UI elements for interactions.
 */
showActions: function() {
	var tb = this.toolbarContainer;

	tb.append(tb.createLink('prp-links', PRP_APPLY_LINK, '', true));
	
},

/**
 * This function is called when the user changed a domain value
 * 
 * @param {String} id
 * 		DOM-ID of the changed input field
 */
domainChanged: function (id) {
	var idx = id.substr('prp-domain-'.length);
	var domain = $(id);
	domain = domain.value;
	this.propertyDefinition.setDomain(domain, parseInt(idx));
},

/**
 * This function is called when the user changed a range value
 * 
 * @param {String} id
 * 		DOM-ID of the changed input field
 */
rangeChanged: function (id) {
	var idx = id.substr('prp-range-'.length);
	var range = $(id);
	range = range.value;
	this.propertyDefinition.setRange(range, parseInt(idx));
},

/**
 * This function is called when the user changed a record field value
 * 
 * @param {String} id
 * 		DOM-ID of the changed input field
 */
recordFieldChanged: function (id) {
	var idx = id.substr('prp-field-'.length);
	var field = $(id);
	field = field.value;
	idx = parseInt(idx);
	this.propertyDefinition.setField(field, idx);
	this.checkRecordFieldDuplicate(id);
},

/**
 * Checks if a property appears in several times in the record.
 * @param {string} id
 */
checkRecordFieldDuplicate: function (id) {
	// Check if properties appear several times
	var tb = this.toolbarContainer;
	// find all input fields for record fields
	var fieldInputs = tb.getElementsWithID(new RegExp(/^prp-field-\d*$/));
	var map = {};
	// Create a map from property names to the corresponding input fields
	for (var i = 0, len = fieldInputs.length; i < len; ++i) {
		var val = fieldInputs[i].value;
		if (val) {
			if (map[val]) {
				map[val].push(fieldInputs[i]);
			} else {
				map[val] = [fieldInputs[i]]; 
			}
		}
	}
	// Set the background color of input fields with duplicate property names 
	for (var field in map) {
		if (map[field].length > 1) {
			// Found an array of duplicates
			for (var i = 0, len = map[field].length; i < len; ++i) {
				var elem = map[field][i];
				gSTBEventActions.performSingleAction('color', 'orange', elem);
				gSTBEventActions.performSingleAction('showmessage', 'DUPLICATE_RECORD_FIELD', elem);
			}
		} else {
			// Found unique property name. Show its current state
			var elem = map[field][0];
			gSTBEventActions.handleSchemaCheck('property', 
					'property: exists ' +
					'? (color: lightgreen, hideMessage, valid:true) ' +
 					': (color: orange, showMessage:PROPERTY_DOES_NOT_EXIST, valid:true)',
					elem);
		}
	}
	return true;
},

/**
 * This function is called when the user changed the cardinality
 * 
 * @param {String} id
 * 		DOM-ID of the changed input field
 */
cardinalityChanged: function (id) {
	var maco = $('prp-max-card');
	var maxCard = parseInt(maco.value);
	var mico =  $('prp-min-card');
	var minCard = parseInt(mico.value);
	if (isNaN(minCard)) {
		minCard = null;
	}
	if (isNaN(maxCard)) {
		maxCard = null;
	}
	
	this.propertyDefinition.setMinCardinality(minCard);
	this.propertyDefinition.setMaxCardinality(maxCard);
},

/**
 * This function is called when the user changed the inverse relation
 * 
 * @param {String} id
 * 		DOM-ID of the changed input field
 */
inverseChanged: function (id) {
	var inverse = $(id).value;
	this.propertyDefinition.setInverse(inverse);
},

/**
 * Adds a new domain and range specification. New input fields are added and
 * the property definition is updated.
 */
addDomainRange: function () {
	this.propertyDefinition.addDomainRange("", "");
	var dr = this.propertyDefinition.getDomainsAndRanges();
	this.showDomainAndRange(dr.length-1);
	this.toolbarContainer.finishCreation();
	gSTBEventActions.initialCheck($("properties-content-box"));
},

checkMaxCard: function(domID) {
	var maco = $('prp-max-card');
	var maxCard = maco.value;
	var mico =  $('prp-min-card');
	var minCard = mico.value;
		
	gSTBEventActions.performSingleAction('color', 'white', mico);
	gSTBEventActions.performSingleAction('hidemessage', null, mico);
	gSTBEventActions.performSingleAction('color', 'white', maco);
	gSTBEventActions.performSingleAction('hidemessage', null, maco);

	if (!maxCard && ! minCard) {
		// neither max. nor min. card. are given
		return true;
	}
	var result = true;
	if (minCard != '') {
		minCard = minCard.match(/^\d+$/);
		if (!minCard) {
			gSTBEventActions.performSingleAction('color', 'red', mico);
			gSTBEventActions.performSingleAction('showmessage', 'INVALID_FORMAT_OF_VALUE', mico);
			result = false;
		} else {
			minCard = minCard * 1;
			gSTBEventActions.performSingleAction('color', 'lightgreen', mico);
			gSTBEventActions.performSingleAction('hidemessage', '', mico);
		}
	}
	if (maxCard != '') {
		maxCard = maxCard.match(/^\d+$/);
		if (!maxCard) {
			gSTBEventActions.performSingleAction('color', 'red', maco);
			gSTBEventActions.performSingleAction('showmessage', 'INVALID_FORMAT_OF_VALUE', maco);
			result = false;
		} else {
			maxCard = maxCard * 1;
			// maxCard must not be 0
			if (maxCard == 0) {
				gSTBEventActions.performSingleAction('color', 'red', maco);
				gSTBEventActions.performSingleAction('showmessage', 'MAX_CARD_MUST_NOT_BE_0', maco);
				result = false;
			} else {
				gSTBEventActions.performSingleAction('color', 'lightgreen', maco);
				gSTBEventActions.performSingleAction('hidemessage', '', maco);
			}
		}
	}
	if (!result) {
		return false;
	}
	
	if (typeof(maxCard) == 'number' && typeof(minCard) == 'string') {
		//maxCard given, minCard not
		gSTBEventActions.performSingleAction('color', 'white', mico);
		gSTBEventActions.performSingleAction('showmessage', 'ASSUME_CARDINALITY_0', mico);
		return true;
	}
	if (typeof(maxCard) == 'string' && typeof(minCard) == 'number') {
		//minCard given, maxCard not
		gSTBEventActions.performSingleAction('color', 'white', maco);
		gSTBEventActions.performSingleAction('showmessage', 'ASSUME_CARDINALITY_INF', maco);
		return true;
	}

	if (!result) {
		return false;
	}	
	
	// maxCard and minCard given => min must be smaller than max
	if (minCard > maxCard) {
		gSTBEventActions.performSingleAction('color', 'red', mico);
		gSTBEventActions.performSingleAction('showmessage', 'MIN_CARD_INVALID', mico);
		return false;
	}
		
	return true;
	
},


propTypeChanged: function(target) {
	var target = $(target);
	var pageType = target[target.selectedIndex].text;
	this.propertyDefinition.setType(pageType);
	this.refreshContainer();
},

/**
 * Adds a new field to the list of fields in a record. The field is initially
 * empty.
 */
addRecordField: function() {
	this.propertyDefinition.addField("");
	var fields = this.propertyDefinition.getFields();
	this.showField(fields.length-1);
	this.toolbarContainer.finishCreation();

	gSTBEventActions.initialCheck($("properties-content-box"));
},

/**
 * Removes the record field with the given HTML ID.
 * 
 * @param {String} id
 * 		HTML-ID of the record field
 */
removeRecordField: function(id) {
	var tb = this.toolbarContainer;
	// remove all UI elements for this record field
	// parse the index from the id
	var idx = id.match(/prp-field-(\d*)/);
	if (idx && idx.length === 2) {
		idx = idx[1];
		tb.remove(this.uiElements['recordField'+idx]);
		delete this.uiElements['recordField'+idx];
		
		// remove the values from the propertyDefinition
		this.propertyDefinition.removeField(idx);
		
		tb.finishCreation();
		this.checkRecordFieldDuplicate(id);
		gSTBEventActions.initialCheck($("properties-content-box"));
	}
},


/**
 * Removes the domain and range definition with the given index.
 * 
 * @param {int} index
 * 		0-based index of the definition
 */
removeDomainRange: function(index) {
	var tb = this.toolbarContainer;
	// remove all UI elements for this domainRange spec
	tb.remove(this.uiElements['domainRange'+index]);
	delete this.uiElements['domainRange'+index];
	
	// remove the values from the propertyDefinition
	this.propertyDefinition.removeDomainRange(index);
	
	tb.finishCreation();
	gSTBEventActions.initialCheck($("properties-content-box"));
},

/**
 * Creates a dropdown for selecting the type of the property.
 * @param {String} id
 * 		ID of the HTML element
 * @param {String} type
 * 		The currently selected property type
 * @param {String} attributes
 * 		Further attributes for the HTML-element
 */
createTypeSelector: function(id, type, attributes) {
	var that = this;
	// This function is called when the ajax call that retrieves all data types
	// returns.
	var createTypeSelectorDropdown = function () {
		var typesAndSelection = that.getTypesAndSelectionIndex(type);
		var allTypes = typesAndSelection[0];
		var selIdx = typesAndSelection[1];
		
		var selection = $(id);
		if (selection) {
			selection.length = allTypes.length;

			for (var i = 0; i < allTypes.length; i++) {
				var isSelected = i == selIdx;
				selection.options[i] = new Option(allTypes[i], allTypes[i], isSelected, isSelected);
			}
			if (selIdx == -1) {
				// Unknown type
				selection.options[i] = new Option(type, type, true, true);
				selIdx = allTypes.length;
			}
			
			gSTBEventActions.initialCheck(selection.up());
		}
		that.toolbarContainer.finishCreation();
	}
	
	// Fetch all available data types
	var sel = [[gLanguage.getMessage('RETRIEVING_DATATYPES')],0];
	if (gDataTypes.getBuiltinTypes() == null) {
		// types are not available yet
		gDataTypes.refresh(createTypeSelectorDropdown);
	} else {
		sel = this.getTypesAndSelectionIndex(type);
	}
	if (!attributes) {
		attributes = "";
	}
	
	var dropDown = this.toolbarContainer.createDropDown(id, gLanguage.getMessage('TYPE'), sel[0], "", sel[1], attributes + ' name="' + name +'"', true);
	dropDown += this.toolbarContainer.createText(id + '-msg', '', '' , true);
	
	return dropDown;
},

/**
 * Retrieves a list of all builtin types and finds the index of
 * "type" in this list.
 * 
 * @param [String} type
 * 		This type is searched among all available types.
 * @return {array(array(string), int)}
 * 	The inner array contains the names of all types and the integer is the index
 *  of "type" in this list. If "type" is not part of the list, the index -1 is 
 *  returned.
 */
getTypesAndSelectionIndex: function(type) {
		
	if (type) {
		type = type.toLowerCase();
	}
	var allTypes = gDataTypes.getBuiltinTypes();
	var selIdx = -1;
	
	// Search for the current type in the array of all types
	for (var i = 0; i < allTypes.length; i++) {
		var lcTypeName = allTypes[i].toLowerCase();
		if (type == lcTypeName) {
			selIdx = i;
			break;
		}
	}
	return [allTypes, selIdx];
},

cancel: function(){
	this.toolbarContainer.hideSandglass();
	this.createContent();
},

apply: function() {
	// Retrieve the transitivity and symmetry from the UI. The model is not
	// notified of their change.
	var transitive = $("prp-transitive") || false;
	if (transitive) {
		transitive = transitive.down('input').checked;
	}
	var symmetric = $("prp-symmetric") || false;
	if (symmetric) {
		symmetric = symmetric.down('input').checked;
	}
	this.propertyDefinition.setTransitive(transitive);
	this.propertyDefinition.setSymmetric(symmetric);
	
	// Update the wiki text according to the current property definition.
	this.propertyDefinition.updateWikiText(this.wtp);
	/*STARTLOG*/
    smwhgLogger.log(wgTitle,"STB-PropertyProperties","property_properties_changed");
	/*ENDLOG*/
},

refreshOtherTabs: function () {
	relToolBar.fillList();
	catToolBar.fillList();
}
};// End of Class


/**
 * class PropertyDefinition
 * This class contains the current definition of a property in the wikitext
 * editor.
 */
var PropertyDefinition = Class.create();

PropertyDefinition.prototype = {

	/**
	 * Initialize the property definition.
	 */
	initialize: function() {
		// These fields define a property. All values are the output of the 
		// wikitext parser i.e. arrays of WtpRelation or WtpCategory objects.
		// Fields for all relevant annotations
		this.typeAnno    = null;
		this.fieldsAnno  = null;
		this.domainAndRangeAnno  = null;
		this.maxCardAnno = null;
		this.minCardAnno = null;
		this.inverseAnno = null;
		this.transitiveAnno = null;
		this.symmetricAnno = null;
		
		// Fields for the actual values of annotations
		this.type    = null;
		this.fields  = null;
		this.domainAndRange  = null;
		this.maxCard = null;
		this.minCard = null;
		this.inverse = null;
		this.mIsTransitive = null;
		this.mIsSymmetric = null;
		
		// Properties derived from the annotated values 
		this.mIsRecord = null;
		this.mIsRelation = null;	
	},
	
	/**
	 * @return {String}
	 * 	Returns the name of the annotated type without namespace. 
	 *  If no type is define in the wiki text, that page type is returned as
	 *  default.
	 */
	getType: function() {
		return this.type;
	},
	
	/**
	 * @return {bool}
	 * 		true, if the property is a relation i.e. if it is of type Page or
	 *		      if no type is specified at all.
	 */
	isRelation: function() {
		return this.mIsRelation;
	},
	
	/**
	 * @return {bool}
	 * 		true, if the property's type is Record.
	 */
	isRecord: function() {
		return this.mIsRecord;
	},
	
	/**
	 * @return {bool}
	 * 		true, if the property is symmetric relation
	 */
	isSymmetric: function() {
		return this.mIsSymmetric;
	},
	
	/**
	 * @return {bool}
	 * 		true, if the property is transitive a relation
	 */
	isTransitive: function() {
		return this.mIsTransitive;
	},
	
	/**
	 * A property definition can have several domains and ranges.
	 * 
	 * @return {array}
	 * 		An array of objects with the fields "domain" and "range". Their values
	 * 		might be empty strings. The category namespace is stripped.
	 * 		If no domain and range is specified, {null} is returned. 
	 */
	getDomainsAndRanges: function () {
		return this.domainAndRange;
	},
	
	/**
	 * @return {integer} or null
	 * 	Returns the minimal cardinality of the property or null, if it is not
	 *  defined.
	 */
	getMinCard: function () {
		return this.minCard;
	},
	
	/**
	 * @return {integer} or null
	 * 	Returns the maximal cardinality of the property or null, if it is not
	 *  defined.
	 */
	getMaxCard: function () {
		return this.maxCard;
	},
	
	/**
	 * @return {String} or null
	 * 	Returns the name of the inverse property of this property or null, if it
	 *  is not defined.
	 */
	getInverse: function () {
		return this.inverse;
	},
	
	/**
	 * @return {array} or null
	 * 	Returns an array of names of properties that define a record or null, if it
	 *  is not defined.
	 */
	getFields: function () {
		return this.fields;
	},

	/**
	 * Sets the type of the property definition.
	 * 
	 * @param {String} type
	 * 		The type must be given without the Type-namespace. The first letter
	 * 		will be converted to lower case.
	 */
	setType: function (type) {
		this.type = type.charAt(0).toLowerCase() + type.substring(1);
		// determine derived values
		this.mIsRecord = type.toLowerCase() === gLanguage.getMessage('TYPE_RECORD').toLowerCase();
		this.mIsRelation = type.toLowerCase() === gLanguage.getMessage('TYPE_PAGE_WONS').toLowerCase();
	},

	/**
	 * Sets the name of the domain at the given index
	 * @param {String} domain
	 * @param {int} idx
	 */
	setDomain: function (domain, idx) {
		if (this.domainAndRange === null || idx >= this.domainAndRange.length) {
			return;
		}
		this.domainAndRange[idx].domain = domain;
	},
	
	/**
	 * Sets the name of the range at the given index
	 * @param {String} range
	 * @param {int} idx
	 */
	setRange: function (range, idx) {
		if (this.domainAndRange === null || idx >= this.domainAndRange.length) {
			return;
		}
		this.domainAndRange[idx].range = range;
	},
	
	/**
	 * Sets the name of the field property at the given index
	 * @param {String} field
	 * 		Name of the property
	 * @param {int} idx
	 */
	setField: function (field, idx) {
		if (this.fields === null || idx >= this.fields.length) {
			return;
		}
		this.fields[idx] = field;
	},
	
	/**
	 * Sets the minimal cardinality of the property definition
	 * @param {int} minCard
	 */
	setMinCardinality: function (minCard) {
		this.minCard = minCard;
	},
	
	/**
	 * Sets the maximal cardinality of the property definition
	 * @param {int} maxCard
	 */
	setMaxCardinality: function (maxCard) {
		this.maxCard = maxCard;
	},
	
	/**
	 * Sets the transitivity of the property definition
	 * @param {bool} transitive
	 */
	setTransitive: function (transitive) {
		this.mIsTransitive = transitive;
	},
	
	/**
	 * Sets the symmetry of the property definition
	 * @param {bool} symmetric
	 */
	setSymmetric: function (symmetric) {
		this.mIsSymmetric = symmetric;
	},
	
	/**
	 * Sets the name of the inverse relation
	 * @param {String} inverse
	 * 		Name of the inverse relation
	 */
	setInverse: function (inverse) {
		this.inverse = inverse;
	},
	
	/**
	 * @public
	 * Adds a domain and range specification.
	 * @param {String} domain
	 * @param {String} range
	 */
	addDomainRange: function(domain, range) {
		if (this.domainAndRange === null) {
			this.domainAndRange = [];
		}
		this.domainAndRange.push({domain: domain, range: range});
	},
	
	/**
	 * @public
	 * Adds a new field to a record.
	 * @param {String} fieldProperty
	 * 		Name of the property that is added as a field.
	 */
	addField: function(fieldProperty) {
		if (this.fields === null) {
			this.fields = [];
		}
		this.fields.push(fieldProperty);
	},
	
	/**
	 * @public 
	 * 
	 * Deletes the domain/range values at the given "index". This change happens
	 * only in the model (i.e. this.domainAndRange) and not in the annotations
	 * The array of domains and ranges may contain gaps after this operation.
	 * 
	 * @param {int} index
	 * 		Index of the domain/range specification to delete
	 */
	removeDomainRange: function (index) {
		if (this.domainAndRange === null) {
			return;
		}
		if (index >= 0 && index < this.domainAndRange.length) {
			delete this.domainAndRange[index];
		}
	},
	
	/**
	 * @public 
	 * 
	 * Deletes the field value at the given "index". This change happens
	 * only in the model (i.e. this.fields) and not in the annotations.
	 * The array of fields may contain gaps after this operation.
	 * 
	 * @param {int} index
	 * 		Index of the field value to delete
	 */
	removeField: function (index) {
		if (this.fields === null) {
			return;
		}
		if (index >= 0 && index < this.fields.length) {
			delete this.fields[index];
		}
	},
	
	/**
	 * @private
	 * 
	 * Updates the property definition from the current content of the wiki text
	 * editor. There is no check for consistency in this method.
	 * The annotation objects and their actual values are stored.
	 * 
	 * @param {WikiTextParser} wtp
	 */
	updateFromWikiText: function(wtp) {
		this.storeAnnotations(wtp);
		this.evaluateAnnotations();
	},
	
	/**
	 * @private
	 * Stores all annotation objects in fields of this object. The annotations
	 * are retrieved from the wiki text parser.
	 * 
	 * @param {WikiTextParser} wtp
	 */
	storeAnnotations: function (wtp) {
		this.typeAnno    = wtp.getRelation(gLanguage.getMessage('HAS_TYPE'));
		this.fieldsAnno  = wtp.getRelation(gLanguage.getMessage('HAS_FIELDS'));
		this.domainAndRangeAnno  = wtp.getRelation(gLanguage.getMessage('DOMAIN_HINT'));
		this.maxCardAnno = wtp.getRelation(gLanguage.getMessage('MAX_CARDINALITY'));
		this.minCardAnno = wtp.getRelation(gLanguage.getMessage('MIN_CARDINALITY'));
		this.inverseAnno = wtp.getRelation(gLanguage.getMessage('IS_INVERSE_OF'));
		  
		this.transitiveAnno = wtp.getCategory(gLanguage.getMessage('TRANSITIVE_RELATION'));
		this.symmetricAnno = wtp.getCategory(gLanguage.getMessage('SYMMETRICAL_RELATION'));
	},
	
	/**
	 * Evaluates the annotations that were retrieved in function storeAnnotations
	 * and stores the actual values that are relevant for the property definition.
	 */
	evaluateAnnotations: function () {
		// The type is stored as string with lower case first character. 
		// The default is "page"
		if (this.typeAnno) {
			this.type = this.typeAnno[0].getValue();
			// remove the prefix "Type:" and lower the case of the first character
			var typeNs = gLanguage.getMessage('TYPE_NS');
			var l = 0;
			if (this.type.indexOf(typeNs) === 0) {
				l = typeNs.length;
			}
			this.type = this.type.charAt(l).toLowerCase() + this.type.substring(l+1);
		} else {
			this.type = gLanguage.getMessage('TYPE_PAGE_WONS');
			this.type = this.type.charAt(0).toLowerCase() + this.type.substring(1);
		}

		// Domain and range may be defined several times. They are stored as
		// array of objects with members "domain" and "range"
		if (this.domainAndRangeAnno === null) {
			this.domainAndRange = null;
		} else {
			this.domainAndRange = [];
			var catNS = gLanguage.getMessage('CATEGORY_NS');
			for (var i = 0; i < this.domainAndRangeAnno.length; ++i) {
				var domRan = this.domainAndRangeAnno[i].getSplitValues();
				// Trim the values
				var dom = domRan.length >= 1 
							? domRan[0].replace(/^\s*(.*?)\s*$/, "$1")
							: '';
				var ran = domRan.length == 2 
							? domRan[1].replace(/^\s*(.*?)\s*$/, "$1")
							: '';
				
				// Strip the category-keyword
				if (dom.indexOf(catNS) === 0) {
					dom = dom.substring(catNS.length);
				}
				if (ran.indexOf(catNS) === 0) {
					ran = ran.substring(catNS.length);
				}
				this.domainAndRange.push({
					domain: dom,
					range: ran
				});
			}
		}
		
		// Fields of a record. They are stored in an array of strings. null
		// if no fields are specified.
		if (this.fieldsAnno === null) {
			this.fields = null;
		} else {
			this.fields = this.fieldsAnno[0].getSplitValues();
		}
		
		// Cardinality is stored as number. If not defined, the values are null
		this.minCard = this.minCardAnno === null 
						? null
						: parseInt(this.minCardAnno[0].getValue());
		this.maxCard = this.maxCardAnno === null 
						? null
						: parseInt(this.maxCardAnno[0].getValue());
		
		// The inverse property is stored as string or is null. The namespace
		// is removed
		this.inverse = this.inverseAnno === null 
						? null
						: this.inverseAnno[0].getValue();
		if (this.inverse && 
			this.inverse.indexOf(gLanguage.getMessage('PROPERTY_NS')) === 0) {
			this.inverse = this.inverse.substring(gLanguage.getMessage('PROPERTY_NS').length);
		}
						
		// Symmetric and transitive are boolean property. Their default value
		// is false.
		this.mIsSymmetric = this.symmetricAnno !== null;
		this.mIsTransitive = this.transitiveAnno !== null;
		
		// determine derived values
		this.mIsRecord = this.type.toLowerCase() === gLanguage.getMessage('TYPE_RECORD').toLowerCase();
		this.mIsRelation = this.type.toLowerCase() === gLanguage.getMessage('TYPE_PAGE_WONS').toLowerCase();
		
	},
	
	
	
	/**
	 * @public
	 * 
	 * Checks if the current property definition contains double definitions.
	 * The fields of this object must be up to date. If not, call updateFromWikiText
	 * first.
	 * 
	 * @return {String} or {bool}
	 * 		Returns a string as error message or boolean false if there is no
	 * 		double definition.
	 */
	findDoubleDefinitions: function() {
		var duplicatesFound = false;
		var doubleDefinition = gLanguage.getMessage('PC_DUPLICATE') + "<ul>";
		
		if (this.typeAnno && this.typeAnno.length > 1) {
			doubleDefinition += "<li><tt>"+gLanguage.getMessage('PC_HAS_TYPE')+"<tt></li>";
			duplicatesFound = true;
		}
		if (this.fieldsAnno && this.fieldsAnno.length > 1) {
			doubleDefinition += "<li><tt>"+gLanguage.getMessage('PC_HAS_FIELDS')+"<tt></li>";
			duplicatesFound = true;
		}
		if (this.maxCardAnno && this.maxCardAnno.length > 1) {
			doubleDefinition += "<li><tt>"+gLanguage.getMessage('MAX_CARDINALITY')+"<tt></li>";
			duplicatesFound = true;
		}
		if (this.minCardAnno && this.minCardAnno.length > 1) {
			doubleDefinition += "<li><tt>"+gLanguage.getMessage('MIN_CARDINALITY')+"<tt></li>";
			duplicatesFound = true;
		}
		if (this.inverseAnno && this.inverseAnno.length > 1) {
			doubleDefinition += "<li><tt>"+gLanguage.getMessage('PC_INVERSE_OF')+"<tt></li>";
			duplicatesFound = true;
		}
		doubleDefinition += "</ul>";
		
		return duplicatesFound ? doubleDefinition : false;
	},
	
	/**
	 * Checks if this property definition is equal to the given property definition
	 * pd.
	 * @param {PropertyDefinition} pd
	 * 		The property definition to compare.
	 * @return {bool}
	 * 		true, if both definitions are equal
	 * 		false otherwise
	 */
	equals: function (pd) {
		var equal = this.type === pd.type &&
					this.maxCard === pd.maxCard &&
					this.minCard === pd.minCard &&
					this.inverse === pd.inverse &&
					this.mIsTransitive === pd.mIsTransitive &&
					this.mIsSymmetric === pd.mIsSymmetric;
					
		if (!equal) {
			return false;
		}					
		
		// Compare all fields
		if (this.fields !== null && pd.fields !== null) {
			if (this.fields.length !== pd.fields.length) {
				return false;
			}
			for (var i = 0; i < this.fields.length; ++i) {
				// The array may contain gaps
				if (typeof this.fields[i] !== typeof pd.fields[i]) {
					return false;		
				}
				
				if (this.fields[i] !== pd.fields[i]) {
					return false;
				}
			}
		} else if (this.fields !== null || pd.fields !== null) {
			// Not equal if at least one field is not null
			return false;
		}
		
		// Compare all domains and ranges
		if (this.domainAndRange !== null && pd.domainAndRange !== null) {
			if (this.domainAndRange.length !== pd.domainAndRange.length) {
				return false;
			}
			for (var i = 0; i < this.domainAndRange.length; ++i) {
				// The array may contain gaps
				if (typeof this.domainAndRange[i] !== typeof pd.domainAndRange[i] ||
					typeof this.domainAndRange[i] !== typeof pd.domainAndRange[i]) {
					return false;		
				}
				if (this.domainAndRange[i].domain !== pd.domainAndRange[i].domain ||
					this.domainAndRange[i].range !== pd.domainAndRange[i].range) {
					return false;
				}
			}
		} else if (this.domainAndRange !== null || pd.domainAndRange !== null) {
			// Not equal if at least one domainAndRange is not null
			return false;
		}
		return true;
		
	},
	
	/**
	 * @public 
	 * The user changes the model of the property definition by interaction
	 * with the UI. This function writes these changes back as annotations into 
	 * the wiki text.
	 * @param WikiTextParser wtp
	 */
	updateWikiText: function (wtp) {
		wtp.initialize();
		this.storeAnnotations(wtp);
		this.updateTypeAnnotation(wtp);
		this.updateDomainAndRangeAnnotation(wtp);
		this.updateCardinalityAnnotation(wtp);
		this.updateRelationAnnotation(wtp);
		this.updateRecordAnnotation(wtp);

       	// if we are in the FCKeditor, we now flush the outputbuffer
        if (gEditInterface &&
			typeof FCKeditor != 'undefined' &&
			typeof CKEDITOR != 'undefined') {
			gEditInterface.flushOutputBuffer();
		}

		// Update the internal representation of the property based on the new 
		// wiki text
		this.updateFromWikiText(wtp);
	}, 
	
	/**
	 * Updates the type annotation in the wiki text.
	 * @param WikiTextParser wtp
	 */
	updateTypeAnnotation: function (wtp) {
		var type = this.type.charAt(0).toUpperCase() + this.type.substring(1);
		if (this.typeAnno) {
			// Update an existing annotation
			this.typeAnno[0].update(gLanguage.getMessage('HAS_TYPE'), type, " ");
		} else {
			// Create a new annotation
			wtp.addRelation(gLanguage.getMessage('HAS_TYPE'), type, " ", true);
		}
	},
	
	/**
	 * Updates the domain and range annotations in the wiki text.
	 * @param WikiTextParser wtp
	 */
	updateDomainAndRangeAnnotation: function (wtp) {
		var catNS = gLanguage.getMessage('CATEGORY_NS');
		if (this.domainAndRange) {
			// Update existing domain and range annotations
			for (var i = 0; i < this.domainAndRange.length; ++i) {
				// The member domainAndRange may contain gaps because the user
				// deleted the corresponding settings
				if (typeof this.domainAndRange[i] === 'undefined') {
					// the annotation was deleted
					if (this.domainAndRangeAnno
						&& this.domainAndRangeAnno.length > i) {
						this.domainAndRangeAnno[i].remove("");
					}
					continue;
				}
				// Process the current domain/range spec
				var domain = this.domainAndRange[i].domain
								? catNS + this.domainAndRange[i].domain
								: "";
				var range = this.mIsRelation && this.domainAndRange[i].range
								? catNS + this.domainAndRange[i].range
								: "";
				var anno = domain + ';' + range;
				if (this.domainAndRangeAnno 
					&& this.domainAndRangeAnno.length > i) {
					// update an existing annotation
					this.domainAndRangeAnno[i].update(gLanguage.getMessage('DOMAIN_HINT') , anno, " ");
				} else {
					// Create a new domain/range annotation
					wtp.addRelation(gLanguage.getMessage('DOMAIN_HINT'), anno, " ", true);
				}
			}
			// Delete obsolete annotations
			for (; this.domainAndRangeAnno && i < this.domainAndRangeAnno.length; ++i) {
				this.domainAndRangeAnno[i].remove("");
			}
		}
	},
	
	/**
	 * Updates the domain and range annotations in the wiki text.
	 * @param WikiTextParser wtp
	 */
	updateCardinalityAnnotation: function (wtp) {
		if (this.minCard) {
			// User specified a value
			if (this.minCardAnno) {
				// Update existing annotation
				this.minCardAnno[0].update(gLanguage.getMessage('MIN_CARDINALITY'), this.minCard, " ");
			} else {
				// Create a new annotation
				wtp.addRelation(gLanguage.getMessage('MIN_CARDINALITY'), this.minCard, " ", true);
			}
		} else if (this.minCardAnno) {
			// User deleted an existing value => remove the old annotation
			this.minCardAnno[0].remove("");
		}

		if (this.maxCard) {
			// User specified a value
			if (this.maxCardAnno) {
				// Update existing annotation
				this.maxCardAnno[0].update(gLanguage.getMessage('MAX_CARDINALITY'), this.maxCard, " ");
			} else {
				// Create a new annotation
				wtp.addRelation(gLanguage.getMessage('MAX_CARDINALITY'), this.maxCard, " ", true);
			}
		} else if (this.maxCardAnno) {
			// User deleted an existing value => remove the old annotation
			this.maxCardAnno[0].remove("");
		}

	},
	
	/**
	 * Updates the relation specific annotations in the wiki text i.e.
	 * - inverse relation
	 * - symmetry
	 * - transitivity
	 * 
	 * @param WikiTextParser wtp
	 */
	updateRelationAnnotation: function (wtp) {
		// Annotation for inverses
		if ((!this.mIsRelation || !this.inverse) && this.inverseAnno) {
			// The inverse annotation is no longer valid => delete it
			this.inverseAnno[0].remove('');
		} else if (this.mIsRelation && this.inverse) {
			// Inverse relation specified for this relation
			var anno = gLanguage.getMessage('PROPERTY_NS') + this.inverse;
			if (this.inverseAnno) {
				// update existing annotation
				this.inverseAnno[0].update(gLanguage.getMessage('IS_INVERSE_OF'), anno, " ");
			} else {
				// add a new annotation
				wtp.addRelation(gLanguage.getMessage('IS_INVERSE_OF'), anno, " ", true);
			}
		}
		
		// Symmetry annotation
		if ((!this.mIsRelation || !this.mIsSymmetric) && this.symmetricAnno) {
			// The symmetry annotation is no longer valid => delete it
			this.symmetricAnno.remove('');
		} else if (this.mIsRelation && this.mIsSymmetric) {
			// Symmetry specified for this relation
			if (!this.symmetricAnno) {
				// add a new annotation
				wtp.addCategory(gLanguage.getMessage('SYMMETRICAL_RELATION'), true);
			}
		}

		// Transitivity annotation
		if ((!this.mIsRelation || !this.mIsTransitive) && this.transitiveAnno) {
			// The transitivity annotation is no longer valid => delete it
			this.transitiveAnno.remove('');
		} else if (this.mIsRelation && this.mIsTransitive) {
			// Transitivity specified for this relation
			if (!this.transitiveAnno) {
				// add a new annotation
				wtp.addCategory(gLanguage.getMessage('TRANSITIVE_RELATION'), true);
			}
		}

	},
	
	/**
	 * Updates the record specific annotations in the wiki text.
	 * 
	 * @param WikiTextParser wtp
	 */
	updateRecordAnnotation: function (wtp) {
		if (this.mIsRecord) {
			// User defined a record
			var anno = '';
			for (var i = 0; i <= this.fields.length; ++i) {
				if (typeof this.fields[i] !== 'undefined') {
					anno += this.fields[i] + ';';
				}
			}
			// Remove the trailing semicolon
			anno = anno.substr(0, anno.length-1);
			
			if (this.fieldsAnno) {
				// Update existing annotation
				this.fieldsAnno[0].update(gLanguage.getMessage('HAS_FIELDS'), anno, " ");
			} else {
				// Create a new annotation
				wtp.addRelation(gLanguage.getMessage('HAS_FIELDS'), anno, " ", true);
			}
		} else if (this.fieldsAnno && this.fieldsAnno.length > 0) {
			// User deleted an existing value => remove the old annotation
			this.fieldsAnno[0].remove("");
		}

	}
	
	
}; // End of class

window.propToolBar = new PropertiesToolBar();
stb_control.registerToolbox(propToolBar);
