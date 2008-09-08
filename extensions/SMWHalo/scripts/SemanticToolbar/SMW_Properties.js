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
var DOMAIN_HINT = "has domain and range";
var RANGE_HINT  = "has domain and range";
var HAS_TYPE = "has type";
var MAX_CARDINALITY = "Has max cardinality";
var MIN_CARDINALITY = "Has min cardinality";
var INVERSE_OF = "Is inverse of";
var TRANSITIVE_RELATION = "Transitive properties";
var SYMMETRICAL_RELATION = "Symmetrical properties";

var SMW_PRP_ALL_VALID =	
	'smwAllValid="allValid ' +
 		'? (show:prop-confirm, hide:prop-invalid) ' +
 		': (show:prop-invalid, hide:prop-confirm)"';
 		
 		
var SMW_PRP_CHECK_MAX_CARD =
	'smwValid="propToolBar.checkMaxCard"';

var SMW_PRP_VALID_CATEGORY_NAME =
	'smwValidValue="^[^<>\|!&$%&\/=\?]{1,255}$: valid ' +
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
	'smwValidValue="^[^<>\|!&$%&\/=\?]{1,255}$: valid ' +
		'? (color: white, hideMessage, valid:true) ' +
	 	': (color: red, showMessage:PROPERTY_NAME_TOO_LONG, valid:false)" ';


var SMW_PRP_HINT_CATEGORY =
	'typeHint = "' + SMW_CATEGORY_NS + '" ';

var SMW_PRP_HINT_PROPERTY =
	'typeHint="'+ SMW_PROPERTY_NS + '" ';
	
var SMW_PRP_CHECK_EMPTY = 
	'smwCheckEmpty="empty' +
		'? (color:red, showMessage:MUST_NOT_BE_EMPTY, valid:false) ' +
		': (color:white, hideMessage)"';
		
var SMW_PRP_CHECK_EMPTY_WIE =   // WIE = Warning if empty but still valid
	'smwCheckEmpty="empty' +
		'? (color:orange, showMessage:VALUE_IMPROVES_QUALITY) ' +
		': (color:white, hideMessage)"';

var SMW_PRP_CHECK_EMPTY_VIE = // valid if empty
	'smwCheckEmpty="empty' +
		'? (color:white, hideMessage, valid:true) ' +
		': ()"';
		
var SMW_PRP_NO_EMPTY_SELECTION =
	'smwCheckEmpty="empty' +
	'? (color:red, showMessage:SELECTION_MUST_NOT_BE_EMPTY, valid:false) ' +
	': (color:white, hideMessage, valid:true)"';

var SMW_PRP_TYPE_CHANGED =
	'smwChanged="(call:propToolBar.propTypeChanged)"';
	

var PRP_NARY_CHANGE_LINKS = [['propToolBar.addType()',gLanguage.getMessage('ADD_TYPE'), 'prp-add-type-lnk']];
		
var PRP_APPLY_LINK =
	[['propToolBar.apply()', 'Apply', 'prop-confirm', gLanguage.getMessage('INVALID_VALUES'), 'prop-invalid'],
	 ['propToolBar.cancel()', gLanguage.getMessage('CANCEL')]
	];

var PropertiesToolBar = Class.create();

PropertiesToolBar.prototype = {

initialize: function() {
	//Reference
	this.genTB = new GenericToolBar();
	this.toolbarContainer = null;
	this.pendingIndicator = null;
	this.isRelation = true;
	this.numOfParams = 0;	// number of relation parameters (for n-aries) 
	this.prpNAry = 0;		// DOM-ID-Index for relation parameters
	this.hasDuplicates = false;
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

callme: function(event){
	
	if(wgAction == "edit" 
	   && (wgNamespaceNumber == 100 || wgNamespaceNumber == 102)
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
	
	var type    = this.wtp.getRelation(HAS_TYPE);
	var domain  = this.wtp.getRelation(DOMAIN_HINT);
	var range   = this.wtp.getRelation(RANGE_HINT);
	var maxCard = this.wtp.getRelation(MAX_CARDINALITY);
	var minCard = this.wtp.getRelation(MIN_CARDINALITY);
	var inverse = this.wtp.getRelation(INVERSE_OF);
	  
	var transitive = this.wtp.getCategory(TRANSITIVE_RELATION);
	var symmetric = this.wtp.getCategory(SYMMETRICAL_RELATION);

	// Check if some property characteristic are given several times
	var duplicatesFound = false;
	var doubleDefinition = gLanguage.getMessage('PC_DUPLICATE') + "<ul>";
	
	if (type && type.size() > 1) {
		doubleDefinition += "<li><tt>"+gLanguage.getMessage('PC_HAS_TYPE')+"<tt></li>";
		duplicatesFound = true;
	}
	if (maxCard && maxCard.size() > 1) {
		doubleDefinition += "<li><tt>"+gLanguage.getMessage('PC_MAX_CARD')+"<tt></li>";
		duplicatesFound = true;
	}
	if (minCard && minCard.size() > 1) {
		doubleDefinition += "<li><tt>"+gLanguage.getMessage('PC_MIN_CARD')+"<tt></li>";
		duplicatesFound = true;
	}
	if (inverse && inverse.size() > 1) {
		doubleDefinition += "<li><tt>"+gLanguage.getMessage('PC_INVERSE_OF')+"<tt></li>";
		duplicatesFound = true;
	}
	doubleDefinition += "</ul>";
	
	if (duplicatesFound) {
		if (this.toolbarContainer) {
			this.toolbarContainer.release();
		}
		this.toolbarContainer = new ContainerToolBar('properties-content',800,this.propertiescontainer);
		this.toolbarContainer.createContainerBody(SMW_PRP_ALL_VALID);
		this.toolbarContainer.append(doubleDefinition);
		this.toolbarContainer.finishCreation();
		this.hasDuplicates = true;
		return;
	}
	
	var changed = this.hasAnnotationChanged(
						[type, domain, range, maxCard, minCard, inverse], 
	                    [transitive, symmetric]);
	                    
	changed |= this.hasDuplicates; // Duplicates have been removed
	this.hasDuplicates = false;
	
	if (!changed) {
		// nothing changed
		return;
	}
		
	if (this.toolbarContainer) {
		this.toolbarContainer.release();
	}
	this.toolbarContainer = new ContainerToolBar('properties-content',800,this.propertiescontainer);
	var tb = this.toolbarContainer;
	tb.createContainerBody(SMW_PRP_ALL_VALID);
		
	if (type) {
		type = type[0].getValue();
		// remove the prefix "Type:" and lower the case of the first character
		var typeNs = gLanguage.getMessage('TYPE_NS');
		var l = typeNs.length;
		type = type.charAt(l).toLowerCase() + type.substring(l+1);
	} else {
		type = gLanguage.getMessage('TYPE_PAGE_WONS');
		type = type.charAt(0).toLowerCase() + type.substring(1);
	}
	this.isRelation = (type.toLowerCase() == gLanguage.getMessage('TYPE_PAGE_WONS').toLowerCase());
	
	if (domain == null) {
		domain = "";
	} else {
		domain = domain[0].getSplitValues()[0];
		// trim
		domain = domain.replace(/^\s*(.*?)\s*$/,"$1");
		if (domain.indexOf(gLanguage.getMessage('CATEGORY_NS')) == 0) {
			// Strip the category-keyword
			domain = domain.substring(9);
		}
	}
	if (range == null) {
		range = "";
	} else {
		if (range[0].getSplitValues()[1]) {
			range = range[0].getSplitValues()[1];
			// trim
			range = range.replace(/^\s*(.*?)\s*$/,"$1");
			if (range.indexOf(gLanguage.getMessage('CATEGORY_NS')) == 0) {
				range = range.substring(9);
			}
		} else {
			//range = range[0].getValue();
			range = "";
		}
	}
	if (maxCard == null) {
		maxCard = "";
	} else {
		maxCard = maxCard[0].getValue();
	}
	if (minCard == null) {
		minCard = "";
	} else {
		minCard = minCard[0].getValue();
	}
	if (inverse == null) {
		inverse = "";
	} else {
		inverse = inverse[0].getValue();
		if (inverse.indexOf(gLanguage.getMessage('PROPERTY_NS')) == 0) {
			inverse = inverse.substring(9);
		}
	}
	transitive = (transitive != null) ? "checked" : "";
	symmetric = (symmetric != null) ? "checked" : "";

	var tb = this.toolbarContainer;
	tb.append(tb.createInput('prp-domain', gLanguage.getMessage('DOMAIN'), '', '',
	                         SMW_PRP_CHECK_CATEGORY + 
	                         SMW_PRP_VALID_CATEGORY_NAME +
	                         SMW_PRP_CHECK_EMPTY_WIE + 
	                         SMW_PRP_HINT_CATEGORY,
	                         true));
	tb.setInputValue('prp-domain',domain);	                         
	                         
	tb.append(tb.createText('prp-domain-msg', '', '' , true));
	
	this.prpNAry = 0;
	this.numOfParams = 0;
	var types = this.wtp.getRelation(HAS_TYPE);
	if (types) {
		types = types[0];
		types = types.getSplitValues();
	} else {
		// no type definition given => default is Type:Page
		types = [gLanguage.getMessage("TYPE_PAGE")];
	}

	var ranges = this.wtp.getRelation(RANGE_HINT);
	
	var rc = 0;
	for (var i = 0, num = types.length; i < num; ++i) {
		
		var t = types[i];
		if (t.indexOf(gLanguage.getMessage('TYPE_NS')) == 0) {
			t = t.substring(gLanguage.getMessage('TYPE_NS').length);
		}	
		tb.append(this.createTypeSelector("prp-type-" + i, 
		                                  "prpNaryType"+i, t,
		                                  "propToolBar.removeType('prp-type-" + i + "')",
		                                  SMW_PRP_NO_EMPTY_SELECTION+
		                                  SMW_PRP_TYPE_CHANGED));
		var r = "";
		var isPage = false;
		if (types[i] == gLanguage.getMessage('TYPE_PAGE')) {
			if (ranges && rc < ranges.length) {
				r = ranges[rc++].getSplitValues()[1];
			}
			// trim
			r = r.replace(/^\s*(.*?)\s*$/,"$1");
			
			if (r.indexOf(gLanguage.getMessage('CATEGORY_NS')) == 0) {
				r = r.substring(gLanguage.getMessage('CATEGORY_NS').length);
			}
			isPage = true;
		}
		tb.append(tb.createInput('prp-range-' + i, gLanguage.getMessage('RANGE'), 
								 '', '',
                     			 SMW_PRP_CHECK_CATEGORY + 
                     			 SMW_PRP_VALID_CATEGORY_NAME +
                     			 SMW_PRP_CHECK_EMPTY_WIE +
	                 			 SMW_PRP_HINT_CATEGORY,
                     			 isPage));
		tb.setInputValue('prp-range-' + i, r);	                         
		tb.append(tb.createText('prp-range-' + i + '-msg', '', '' , isPage));
                    			 
		this.prpNAry++;
		this.numOfParams++;
	}

	tb.append(tb.createInput('prp-inverse-of', gLanguage.getMessage('INVERSE_OF'), '', '',
	                         SMW_PRP_CHECK_PROPERTY +
	                         SMW_PRP_VALID_PROPERTY_NAME +
	                         SMW_PRP_HINT_PROPERTY+
	                         SMW_PRP_CHECK_EMPTY_VIE,
	                         true));
	tb.setInputValue('prp-inverse-of',inverse);	                         
	                         
	tb.append(tb.createText('prp-inverse-of-msg', '', '' , true));

	tb.append(tb.createInput('prp-min-card', gLanguage.getMessage('MIN_CARD'), '', '', 
	                         SMW_PRP_CHECK_MAX_CARD, true, false));
	tb.setInputValue('prp-min-card',minCard);	                         
	                         
	tb.append(tb.createText('prp-min-card-msg', '', '' , true));
	tb.append(tb.createInput('prp-max-card', gLanguage.getMessage('MAX_CARD'), '', '', 
	                         SMW_PRP_CHECK_MAX_CARD, true, false));
	tb.setInputValue('prp-max-card',maxCard);	                         
	tb.append(tb.createText('prp-max-card-msg', '', '' , true));
	tb.append(tb.createCheckBox('prp-transitive', '', [gLanguage.getMessage('TRANSITIVE')], [transitive == 'checked' ? 0 : -1], 'name="transitive"', true));
	tb.append(tb.createCheckBox('prp-symmetric', '', [gLanguage.getMessage('SYMMETRIC')], [symmetric == 'checked' ? 0 : -1], 'name="symmetric"', true));

	
	tb.append(tb.createLink('prp-change-links', PRP_NARY_CHANGE_LINKS, '', true));
	tb.append(tb.createLink('prp-links', PRP_APPLY_LINK, '', true));
				
	tb.finishCreation();
	this.enableWidgets();
	gSTBEventActions.initialCheck($("properties-content-box"));
	//Sets Focus on first Element
//	setTimeout("$('prp-domain').focus();",50);
    
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

hasAnnotationChanged: function(relations, categories) {
	var changed = false;
	if (!this.relValues) {
		changed = true;
		this.relValues = new Array(relations.length);
		this.catValues = new Array(categories.length);
	}
	
	// check properties that are defined as relation
	for (var i = 0; i < relations.length; i++) {
		if (!relations[i] && this.relValues[i]) {
			// annotation has been removed
			changed = true;
			this.relValues[i] = null;
		} else if (relations[i]) {
			// there is an annotation
			var value = relations[i][0].annotation;
			if (this.relValues[i] != value) {
				// and it has changed
				this.relValues[i] = value;
				changed = true;
			}
		}
	}
	// check properties that are defined as category
	for (var i = 0; i < categories.length; i++) {
		if (!categories[i] && this.catValues[i]) {
			// annotation has been removed
			changed = true;
			this.catValues[i] = false;
		} else if (categories[i] && !this.catValues[i]) {
			// annotation has been added
			this.catValues[i] = true;
			changed = true;
		}
	}
	return changed;
},

propTypeChanged: function(target) {
	var target = $(target);
	
	var typeIdx = target.id.substring(9);
	var rangeId = "prp-range-"+typeIdx;
	
	var attrType = target[target.selectedIndex].text;
	
	var isPage = attrType == gLanguage.getMessage('TYPE_PAGE_WONS');
	var tb = propToolBar.toolbarContainer;
	tb.show(rangeId, isPage);
	if (!isPage) {
		tb.show(rangeId+'-msg', false);
	}
	
	this.isRelation = (this.numOfParams == 1) ? isPage : false;  
	gSTBEventActions.initialCheck($("properties-content-box"));
	this.enableWidgets();

},


addType: function() {
	var tb = this.toolbarContainer;
	var insertAfter = (this.prpNAry==0) ? 'prp-domain-msg' 
							 : $('prp-range-'+(this.prpNAry-1)+'-msg') 
							 	? 'prp-range-'+(this.prpNAry-1)+'-msg'
							 	: 'prp-range-'+(this.prpNAry-1);
	
	
	this.toolbarContainer.insert(insertAfter,
			  this.createTypeSelector("prp-type-" + this.prpNAry, 
	                                  "prpNaryType"+this.prpNAry, 
	                                  gLanguage.getMessage('TYPE_PAGE_WONS'),
	                                  "propToolBar.removeType('prp-type-" + this.prpNAry + "')",
	                                  SMW_PRP_NO_EMPTY_SELECTION+
	                                  SMW_PRP_TYPE_CHANGED));

	tb.insert("prp-type-" + this.prpNAry,
			  tb.createInput('prp-range-' + this.prpNAry, gLanguage.getMessage('RANGE'), 
			  				 "", '',
                 			 SMW_PRP_CHECK_CATEGORY +
                 			 SMW_PRP_VALID_CATEGORY_NAME + 
                 			 SMW_PRP_CHECK_EMPTY_WIE +
                 			 SMW_PRP_HINT_CATEGORY,
                 			 true));
    tb.setInputValue('prp-range-' + this.prpNAry,'');
	tb.insert('prp-range-' + this.prpNAry,
	          tb.createText('prp-range-' + this.prpNAry + '-msg', '', '' , true));

	this.prpNAry++;
	this.numOfParams++;
	this.toolbarContainer.finishCreation();
	this.enableWidgets();
	gSTBEventActions.initialCheck($("properties-content-box"));
		
},

removeType: function(domID) {
	
	this.toolbarContainer.remove(domID)
	this.toolbarContainer.remove(domID+'-msg');
	domID = domID.replace(/type/, 'range');
	this.toolbarContainer.remove(domID)
	this.toolbarContainer.remove(domID+'-msg');
	
	this.numOfParams--;
	if (domID == 'prp-range-'+(this.prpNAry-1)) {
		while (this.prpNAry > 0) {
			--this.prpNAry;
			if ($('prp-type-'+ this.prpNAry)) {
				this.prpNAry++;
				break;
			}
		}
	}
	if (this.numOfParams == 1) {
		var selector = $('prp-type-'+(this.prpNAry*1-1));
		var type = selector[selector.selectedIndex].text;
		this.isRelation = type == gLanguage.getMessage('TYPE_PAGE_WONS');
	}
	this.toolbarContainer.finishCreation();
	this.enableWidgets();
	gSTBEventActions.initialCheck($("properties-content-box"));
},

createTypeSelector: function(id, name, type, deleteAction, attributes) {
	var closure = function() {
		
		var origTypeString = type;
		if (type) {
			type = type.toLowerCase();
			if (type.indexOf(';') > 0) {
				type = 'n-ary';
			}
		}
		var typeFound = false;
		var builtinTypes = gDataTypes.getBuiltinTypes();
		var userTypes = gDataTypes.getUserDefinedTypes();
		var allTypes = builtinTypes.concat([""], userTypes);
		
		var selection = $(id);
		if (selection) {
			selection.length = allTypes.length;
		}
		var selIdx = -1;
		
//		var sel = "";
		for (var i = 0; i < allTypes.length; i++) {
			var lcTypeName = allTypes[i].toLowerCase();
			if (type == lcTypeName) {
//				sel += '<option selected="">' + allTypes[i] + '</option>';
				typeFound = true;
				if (selection) {
					selection.options[i] = new Option(allTypes[i], allTypes[i], true, true);
				}
				selIdx = i;
			} else {
//				sel += '<option>' + allTypes[i] + '</option>';
				if (selection) {
					selection.options[i] = new Option(allTypes[i], allTypes[i], false, false);
				}
			}
		}
		if (type && !typeFound) {
			if (selection) {
				selection.options[i] = new Option(origTypeString, origTypeString, true, true);
			}
			selIdx = allTypes.length;
			allTypes[allTypes.length] = origTypeString;
//			sel += '<option selected="">' + origTypeString + '</option>';
		}
		
		if ($(id)) {
			gSTBEventActions.initialCheck($(id).up());
		}
		propToolBar.toolbarContainer.finishCreation();
		return [allTypes, selIdx];
	};
	
	var sel = [[gLanguage.getMessage('RETRIEVING_DATATYPES')],0];
	if (gDataTypes.getUserDefinedTypes() == null 
	    || gDataTypes.getBuiltinTypes() == null) {
		// types are not available yet
		gDataTypes.refresh(closure);
	} else {
		sel = closure();
	}
	if (!deleteAction) {
		deleteAction = "";
	}
	if (!attributes) {
		attributes = "";
	}
	
	var dropDown = this.toolbarContainer.createDropDown(id, gLanguage.getMessage('TYPE'), sel[0], deleteAction, sel[1], attributes + ' name="' + name +'"', true);
	dropDown += this.toolbarContainer.createText(id + '-msg', '', '' , true);
	
	return dropDown;
},

enableWidgets: function() {
	var tb = propToolBar.toolbarContainer;

	var isnary = propToolBar.numOfParams > 1;
	
	tb.show("prp-inverse-of", propToolBar.isRelation && !isnary);
	tb.show("prp-transitive", propToolBar.isRelation && !isnary);
	tb.show("prp-symmetric", propToolBar.isRelation && !isnary);
	
	tb.show('prp-min-card', !isnary);
	tb.show('prp-max-card', !isnary);
},

cancel: function(){
	this.toolbarContainer.hideSandglass();
	this.relValues = null;
	this.catValues = null;
	this.createContent();
},

apply: function() {
	this.wtp.initialize();
	var domain   = $("prp-domain").value;
	var inverse  = this.isRelation ? $("prp-inverse-of").value : null;
	var minCard  = this.isNAry ? null : $("prp-min-card").value;
	var maxCard  = this.isNAry ? null : $("prp-max-card").value;
	var transitive = this.isRelation ? $("prp-transitive") : null;
	var symmetric  = this.isRelation ? $("prp-symmetric") : null;

	domain   = (domain   != null && domain   != "") ? gLanguage.getMessage('CATEGORY_NS')+domain : null;
	inverse  = (inverse  != null && inverse  != "") ? gLanguage.getMessage('PROPERTY_NS')+inverse : null;
	minCard  = (minCard  != null && minCard  != "") ? minCard : null;
	maxCard  = (maxCard  != null && maxCard  != "") ? maxCard : null;

	var domainRangeAnno = this.wtp.getRelation(DOMAIN_HINT);
	var maxCardAnno = this.wtp.getRelation(MAX_CARDINALITY);
	var minCardAnno = this.wtp.getRelation(MIN_CARDINALITY);
	var inverseAnno = this.wtp.getRelation(INVERSE_OF);
	  
	var transitiveAnno = this.wtp.getCategory(TRANSITIVE_RELATION);
	var symmetricAnno = this.wtp.getCategory(SYMMETRICAL_RELATION);
	
	
	// change existing annotations
	if (maxCardAnno != null) {
		if (maxCard == null) {
			maxCardAnno[0].remove("");
		} else {
			maxCardAnno[0].changeValue(maxCard);
		}
	} 
	if (minCardAnno != null) {
		if (minCard == null) {
			minCardAnno[0].remove("");
		} else {
			minCardAnno[0].changeValue(minCard);
		}
	}
	if (inverseAnno != null) {
		if (inverse == null) {
			inverseAnno[0].remove("");
		} else {
			inverseAnno[0].changeValue(inverse);
		}
	}
	if (transitiveAnno != null && (transitive == null || !transitive.down('input').checked)) {
		transitiveAnno.remove("");
	}
	if (symmetricAnno != null && (symmetric == null || !symmetric.down('input').checked)) {
		symmetricAnno.remove("");
	}
	
	// append new annotations
	if (maxCardAnno == null && maxCard != null) {
		this.wtp.addRelation(MAX_CARDINALITY, maxCard, null, true);
	}
	if (minCardAnno == null && minCard != null) {
		this.wtp.addRelation(MIN_CARDINALITY, minCard, null, true);
	}
	if (inverseAnno == null && inverse != null) {
		this.wtp.addRelation(INVERSE_OF, inverse, null, true);
	}
	if (transitive != null && transitive.down('input').checked && transitiveAnno == null) {
		this.wtp.addCategory(TRANSITIVE_RELATION, true);
	}
	if (symmetric != null && symmetric.down('input').checked && symmetricAnno == null) {
		this.wtp.addCategory(SYMMETRICAL_RELATION, true);
	}
	
	// Handle the definition of (n-ary) relations
	// First, remove all domain/range hints
	rangeAnno = this.wtp.getRelation(RANGE_HINT);
	if (rangeAnno) {
		for (var i = 0, num = rangeAnno.length; i < num; i++) {
			rangeAnno[i].remove("");
		}
	}
	
	// Create new domain/range hints.
	var typeString = "";
	for (var i = 0; i < this.prpNAry; i++) {
		var obj = $('prp-type-'+i);
		if (obj) {
			var type = obj[obj.selectedIndex].text;
			if (type.toLowerCase() == gLanguage.getMessage('TYPE_PAGE_WONS').toLowerCase()) {
				// Page found
				var range = $('prp-range-'+i).value;
				var r = (range == '') ? '' : gLanguage.getMessage('CATEGORY_NS')+range;
				r = ((domain == null) ? "" : domain) + "; " + r;
				typeString += gLanguage.getMessage('TYPE_PAGE')+';';
				this.wtp.addRelation(RANGE_HINT, r, null, true);
			} else {
				// type is not Page
				typeString += gLanguage.getMessage('TYPE_NS') + type + ";";
			}
		}
	}
	
	// add the (n-ary) type definition
	attrTypeAnno = this.wtp.getRelation(HAS_TYPE);
	if (typeString != "") {
		// remove final semi-colon
		typeString = typeString.substring(0, typeString.length-1);
		if (attrTypeAnno != null) {
			attrTypeAnno[0].changeValue(typeString);
		} else {			
			this.wtp.addRelation(HAS_TYPE, typeString, null, true);
		}
	} else {
		attrTypeAnno[0].remove("");
	}
	
	this.createContent();
	this.refreshOtherTabs();
	
	/*STARTLOG*/
    smwhgLogger.log(wgTitle,"STB-PropertyProperties","property_properties_changed");
	/*ENDLOG*/
	
},

refreshOtherTabs: function () {
	relToolBar.fillList();
	catToolBar.fillList();
}
};// End of Class

var propToolBar = new PropertiesToolBar();
Event.observe(window, 'load', propToolBar.callme.bindAsEventListener(propToolBar));
