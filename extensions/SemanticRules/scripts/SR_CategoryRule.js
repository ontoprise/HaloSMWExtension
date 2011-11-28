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

var CategoryRule = Class.create();

/**
 * @file
 * @ingroup SRRuleTypes
 * 
 * @author Kai K�hn
 */
 		

CategoryRule.prototype = {


/**
 * Constructor
 * @param ruleName of type string
 * 		Name of the rule
 * @param ruleType of type string
 * 		Type of the rule e.g. Calculation, Property Chaining, Deduction, Mapping
 */
initialize: function(ruleName, ruleType) {
	this.ruleName = ruleName;
	this.ruleType = ruleType;
	smwhgCreateDefinitionRule = this;
	this.numParts  = 0; // The number of parts the  rule consists of.
	this.variables = 1; // number of variables
	this.pendingIndicator = null;
	this.annotation = null;
	
},

/**
 * Creates the initial user interface of the simple rules editor.
 */
createRule: function() {
	// hide the wiki text editor
	var bodyContent = skin == 'ontoskin3' ? $('content') : $('bodyContent');
	bodyContent.hide();
	var html;
	
	var headText = this.createHeadHTML(1, wgCanonicalNamespace, wgTitle);
	
	var catOrProp = gsrLanguage.getMessage('SR_MCATPROP');
	catOrProp = catOrProp.replace(/\$1/g, '<a href="javascript:smwhgCreateDefinitionRule.memberOfCategory()">');
	catOrProp = catOrProp.replace(/\$2/g, '</a>');
	catOrProp = catOrProp.replace(/\$3/g, '<a href="javascript:smwhgCreateDefinitionRule.memberOfProperty()">');
	catOrProp = catOrProp.replace(/\$4/g, '</a>');
	catOrProp = 
'				<div id="bodyPart0">' +
					catOrProp +
'				</div>';
				
	html = this.getHTMLRuleFramework(headText, catOrProp);

	new Insertion.After(bodyContent, html);
	
	Event.observe('sr-save-rule-btn', 'click', 
			      smwhgCreateDefinitionRule.saveRule.bindAsEventListener(smwhgCreateDefinitionRule));
	if ($('sr-head-value-selector')) {			      
		Event.observe('sr-head-value-selector', 'change', 
				      smwhgCreateDefinitionRule.propValueChanged.bindAsEventListener());
	}
	$('sr-save-rule-btn').disable();
	
},

/**
 * Creates the HTML for the head of a rule for categories or properties. The 
 * type of the head (category or property) depends on the namespace of the
 * current page.
 * 
 * @param varIdx of type string
 * 		Index of the variable in the head
 * @param catOrProp of type string
 * 		The language dependent name for categories or properties
 * @param title of type string
 * 		Name of the category or property
 */
createHeadHTML: function(varIdx, catOrProp, title, propValue, propIsVariable) {
	if (wgNamespaceNumber == 14) {
		// Head for categories
		var headText = gsrLanguage.getMessage('SR_CAT_HEAD_TEXT');
		headText = headText.replace(/\$1/g, '<span class="rules-variable">X<sub>'+varIdx+'</sub> </span>');
		headText = headText.replace(/\$2/g, catOrProp);
		headText = headText.replace(/\$3/g, '<span class="rules-category">' + title + '</span>');
		return headText;
	} else if (wgNamespaceNumber == 102) {
		// Head for properties
		var headText = gsrLanguage.getMessage('SR_PROP_HEAD_TEXT');
		headText = headText.replace(/\$1/g, '<span class="rules-variable">X<sub>'+varIdx+'</sub> </span>');
		headText = headText.replace(/\$2/g, '<span class="rules-category">' + title + '</span>');
		var propHTML =
			'&nbsp;' +
			this.createVariableSelector("sr-head-value-selector", gsrLanguage.getMessage('SR_SIMPLE_VALUE'),"X2") +
			'&nbsp;' +
			'<input type="text" value="" id="sr-prop-head-value" style="display:none" class="wickEnabled" constraints="all"/>' +
			'&nbsp;';		
		headText = headText.replace(/\$3/g, propHTML);
		this.variables = 2;
		return headText;
		
	}	
},

/**
 * Returns the HTML structure of the rule interface consisting of a head, body
 * and preview part.
 * 
 * @param headText of type string
 * 		HTML-content of the head part
 * @param bodyText of type string
 * 		HTML-content of the body part
 */
getHTMLRuleFramework: function(headText, bodyText) {	
	var derive = gsrLanguage.getMessage('SR_DERIVE_BY');
	derive = derive.replace(/\$1/g, wgCanonicalNamespace);
	derive = derive.replace(/\$2/g, '<span class="rules-category">'+wgTitle+'</span>');
	html = 
'<div id="createRuleContent" class="rules-complete-content">' +
	derive +
'	<div id="headBodyDiv" style="padding-top:5px">' +
'		<div id="headDiv" class="rules-frame" style="border-bottom:0px">' +
'			<div id="headTitle" class="rules-title">' +
				gsrLanguage.getMessage('SR_HEAD') +
'			</div>' +
'			<div id="headContent" class="rules-content">' +
				headText +
'			</div>' +
'		</div>' +
'		<div id="bodyDiv" class="rules-frame">' +
'			<div id="bodyTitle" class="rules-title">' +
				gsrLanguage.getMessage('SR_BODY') +
'			</div>' +
'			<div id="ruleBodyContent" class="rules-content">' +
				bodyText +
'			</div>' +
'		</div>' +
'		<div style="height:20px"></div>' +
'		<div id="implicationsDiv" class="rules-frame">' +
'			<div id="implicationsTitle" class="rules-title" style="width:auto;">' +
				gsrLanguage.getMessage('SR_RULE_IMPLIES') +
'			</div>' +
'			<div id="implicationsContent" class="rules-content">' +
'			</div>' +
'		</div>' +
'	</div>' +
'	<div style="height:20px"></div>' +
'   <input type="submit" accesskey="s" value="' +
		gsrLanguage.getMessage('SR_SAVE_RULE') +
		'" name="sr-save-rule-btn" id="sr-save-rule-btn"/>' +
'</div>';

	return html;
},

/**
 * Edits the rule with the given rule text.
 * 
 * @param rule of type WtpRule
 * 		The annotation object of the rule
 */
editRule: function(ruleAnnotation) {
	
	function ajaxResponseParseRule(request) {
		this.hidePendingIndicator();			
		if (request.status == 200) {
			// success
			var xml = request.responseText;
			if (xml == 'false') {
				//TODO
				return;
			}
			// create the user interface
			this.createUIForRule(xml);
		} else {
			alert(request.responseText);
		}
	};

	this.showPendingIndicator($('rule-name'));
	this.annotation = ruleAnnotation;
	var ruleText = ruleAnnotation.getRuleText();
	sajax_do_call('smwf_sr_ParseRule', 
	          [this.ruleName, ruleText], 
	          ajaxResponseParseRule.bind(this));
	
},


/**
 * Cancels editing or creating the rule. Closes the rule edit part of the UI and
 * reopens the wiki text edit part.
 *  
 */
cancel: function() {
	var bodyContent = skin == 'ontoskin3' ? $('content') : $('bodyContent');
	bodyContent.show();
	if ($('createRuleContent')) {
		$('createRuleContent').remove();
	}
		
},

/**
 * Creates the user interface for the rule that is given in the XML format.
 * 
 * @param ruleXML of type string
 * 		Description of the rule in XML
 */
createUIForRule: function(ruleXML) {
	// hide the wiki text editor
	var bodyContent = $('content');
	bodyContent.hide();
	
	var rule = GeneralXMLTools.createDocumentFromString(ruleXML);
	
	var head = rule.getElementsByTagName("head")[0].childNodes;
	var body = rule.getElementsByTagName("body")[0].childNodes;
	
	this.variables = 1;
	
	var headHTML = '';
	for (var i = 0, n = head.length; i < n; i++) {
		var headLit = head[i]; 
		if (headLit.nodeType == 1) {
			// skip text nodes
			headHTML += this.getHTMLForLiteral(headLit, true, 888888);
		}
	}
	
	var bodyHTML = '';
	this.numParts = 0;
	for (var i = 0, n = body.length; i < n; i++) {
		var bodyLit = body[i]; 
		if (bodyLit.nodeType == 1) {
			// skip text nodes
			bodyHTML += this.getHTMLForLiteral(bodyLit, false, this.numParts);
	    	bodyHTML +=
				'<div id="AND' + this.numParts + '">' +
					'<b>' +
					gsrLanguage.getMessage('SR_AND') +
					'</b>' +
				'</div>';
		    this.numParts++;
		}
	}
	var catOrProp = gsrLanguage.getMessage('SR_MCATPROP');
	catOrProp = catOrProp.replace(/\$1/g, '<a href="javascript:smwhgCreateDefinitionRule.memberOfCategory()">');
	catOrProp = catOrProp.replace(/\$2/g, '</a>');
	catOrProp = catOrProp.replace(/\$3/g, '<a href="javascript:smwhgCreateDefinitionRule.memberOfProperty()">');
	catOrProp = catOrProp.replace(/\$4/g, '</a>');
	bodyHTML += 
		'<div id="bodyPart'+this.numParts+'">' +
			catOrProp +
		'</div>';
	
	html = this.getHTMLRuleFramework(headHTML, bodyHTML);

	new Insertion.After(bodyContent, html);
	
	Event.observe('sr-save-rule-btn', 'click', 
			      smwhgCreateDefinitionRule.saveRule.bindAsEventListener(smwhgCreateDefinitionRule));
	if ($('sr-head-value-selector')) {			      
		Event.observe('sr-head-value-selector', 'change', 
				      smwhgCreateDefinitionRule.propValueChanged.bindAsEventListener());
	}
		
},

/**
 * Assembles the HTML for a literal of a rule. The literal is passed as a DOM
 * node.
 * 
 * @param literal of type DOMnode
 * 		Literal of a rule (a category or a property)
 * @param isHead of type bool
 * 		If <true>, HTML code the head of the rule generated.
 *
 * @return string
 * 		HTML code that represents the literal
 * 
 */
getHTMLForLiteral: function(literal, isHead, partID) {
	var html = '';
	switch (literal.tagName) {
		case 'category':
			var catName = literal.getElementsByTagName('name')[0].firstChild.nodeValue;
			var subject = literal.getElementsByTagName('subject')[0].firstChild.nodeValue;
			var varIdx = subject.match(/X(\d+)/);
			if (varIdx[1]*1 > this.variables) {
				this.variables = varIdx[1]*1;
			}

			if (isHead) {
				html = this.createHeadHTML(varIdx[1], wgCanonicalNamespace, catName);
			} else {
				html =	
	'<div id="bodyPart' + partID + '">' +
	gsrLanguage.getMessage('SR_ALL_ARTICLES') +
		'&nbsp;' +
		'<span id="var_' + partID + '" ' +
			'varname="' + subject + '" ' +
			'class="rules-variable">' +
		'X<sub>' + varIdx[1] + '</sub>' + 
		'</span>&nbsp;' +
		gsrLanguage.getMessage('SR_BELONG_TO_CAT') +
		'&nbsp;' +
		'<span id="cat_' + partID + '" ' +
			'catname="' + escape(catName) + '" ' +
			'class="rules-category">' +
		catName +
		'</span>&nbsp;' +
		'<span id="buttons_' + partID + '">' +
			'<a href="javascript:smwhgCreateDefinitionRule.editCategoryCondition(' + partID + ')">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/edit.gif"/>' +
			'</a>' + ( !isHead ? 
			'<a href="javascript:smwhgCreateDefinitionRule.removeCondition(' + partID + ')">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/delete.png"/>' +
			'</a>' : '' )+
		'</span>' + 		
	'</div>';
			}
			break;
		case 'property':
			var propName = literal.getElementsByTagName('name')[0].firstChild.nodeValue;
			var subject = literal.getElementsByTagName('subject')[0].firstChild.nodeValue;
			var subjIdx = subject.match(/X(\d+)/);
			if (subjIdx[1]*1 > this.variables) {
				this.variables = subjIdx[1]*1;
			}
			
			var valueHTML;
			var variable = literal.getElementsByTagName('variable');
			if (variable.length) {
				variable = variable[0].firstChild.nodeValue;
				var varIdx = variable.match(/X(\d+)/);
				if (varIdx[1]*1 > this.variables) {
					this.variables = varIdx[1]*1;
				}
				valueHTML = '<span class="rules-variable" ' + 
								'id="value_' + partID + '" ' +
								'propvalue="' + escape(variable) + '"' +
								'proptype="variable"' +
								'>' +
								'X<sub>' + varIdx[1] + '</sub>' + 
							'</span>';
			}
			var value = literal.getElementsByTagName('value');
			if (value.length) {
				var operand = value[0].getAttribute("operand");
				value = value[0].firstChild.nodeValue;
				var operandText = operand == null || operand == '=' ? '' : 'operand="'+operand+'"';
				var valueInfo = 
				valueHTML = '<span class="rules-category"' + 
								'id="value_' + partID + '" ' +
								'propvalue="' + escape(value) + '"' +
								'proptype="value" ' + operandText + 							
								'>' +
								value + 
							'</span>';
			}
			
			var html =	
'<div id="bodyPart' + partID + '">' +
	gsrLanguage.getMessage('SR_ALL_ARTICLES') +
		'&nbsp;' +
		'<span id="var_' + partID + '" ' +
			'varname="' + subject + '" ' +
			'class="rules-variable">' +
			'X<sub>' + subjIdx[1] + '</sub>' + 
		'</span>&nbsp;' +
		gsrLanguage.getMessage('SR_HAVE_PROP') +
		'&nbsp;' +
		'<span id="prop_' + partID + '" ' +
			'propname="' + propName + '" ' +
			'class="rules-category">' +
		propName +
		'</span>&nbsp;' +
		gsrLanguage.getMessage('SR_WITH_VALUE') +
		'&nbsp;' +
		valueHTML + 
		'&nbsp;' +
		'<span id="buttons_' + partID + '">' +
			'<a href="javascript:smwhgCreateDefinitionRule.editPropertyCondition(' + partID + ')">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/edit.gif"/>' +
			'</a>' + (!isHead ? 
			'<a href="javascript:smwhgCreateDefinitionRule.removeCondition(' + partID + ')">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/delete.png"/>' +
			'</a>' : '') +
		'</span>' +
'</div>';
			
			break;
	}
	return html;
},

/**
 * Creates the section of the user interface where a category condition can be
 * defined.
 */
memberOfCategory: function() {
	
	$('sr-save-rule-btn').disable();
	
	var id = 'bodyPart'+this.numParts;
	var currPart = $('bodyPart'+this.numParts);
	
	var html;
	html = 
'<div id="bodyPart' + this.numParts + '">' +
	gsrLanguage.getMessage('SR_ALL_ARTICLES') +
		'&nbsp;' +
		this.createVariableSelector('sr-variable-selector',null,"X1") +
		'&nbsp;' +
		gsrLanguage.getMessage('SR_BELONG_TO_CAT') +
		'&nbsp;' +
		'<input type="text" value="" id="sr-cat-name" class="wickEnabled" constraints="namespace: '+SMW_CATEGORY_NS+'"/>' +
		'&nbsp;' +
		'<a href="javascript:smwhgCreateDefinitionRule.showCategoryCondition(' + this.numParts + ',false)">' +
			'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/add.png"/>' +
		'</a>' +
'</div>';

	currPart.replace(html);
},

/**
 * Replaces the display of a category condition by the editable user interface.
 * 
 * @param partID of type int
 * 		Index of the part of the rule.
 */
editCategoryCondition: function(partID) {
	
	$('ruleBodyContent').hide();
	$('sr-save-rule-btn').disable();
	
	this.showButtons(false);
	
	var elem = $('var_'+partID);
	if (elem) {
		var val = elem.readAttribute('varname');
		elem.replace(this.createVariableSelector('sr-variable-selector', null, val));
	}
	elem = $('cat_'+partID);
	if (elem) {
		var val = unescape(elem.readAttribute('catname'));
		elem.replace('<input type="text" value="" id="sr-cat-name" class="wickEnabled" constraints="namespace: 14"/>');
		$("sr-cat-name").value = val;
	}
	elem = $('buttons_'+partID);
	if (elem) {
		new Insertion.After(elem,
			'<a href="javascript:smwhgCreateDefinitionRule.showCategoryCondition('+ partID +',true)">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/add.png"/>' +
			'</a>');
		elem.remove();
	}

	$('ruleBodyContent').show();
},

/**
 * After a category condition has been defined, it is displayed in a simplified
 * format without input fields etc. The section for defining the next condition
 * is added if <update> is 'false'. 
 * 
 * @param partID of type int
 * 		ID of the part where the category condition is added
 * @param update of type bool
 * 		If <true>, the current part is updated. The next condition will not be 
 * 		appended.
 */
showCategoryCondition: function(partID, update) {
	var category = $('sr-cat-name').value;
	if (category.length == 0) {
		return;
	}
	
	$('ruleBodyContent').hide();
	$('sr-save-rule-btn').enable();
	
	var variable = $('sr-variable-selector');
	variable = variable.options[variable.selectedIndex].text;
	
	var varIdx = variable.substr(1) * 1;
	
	if (varIdx > this.variables) {
		this.variables = varIdx;
	}
	
	var id = 'bodyPart'+partID;
	var currPart = $('bodyPart'+partID);

	var catOrProp = gsrLanguage.getMessage('SR_MCATPROP');
	catOrProp = catOrProp.replace(/\$1/g, '<a href="javascript:smwhgCreateDefinitionRule.memberOfCategory()">');
	catOrProp = catOrProp.replace(/\$2/g, '</a>');
	catOrProp = catOrProp.replace(/\$3/g, '<a href="javascript:smwhgCreateDefinitionRule.memberOfProperty()">');
	catOrProp = catOrProp.replace(/\$4/g, '</a>');

	var html;

	html =	
'<div id="bodyPart' + partID + '">' +
	gsrLanguage.getMessage('SR_ALL_ARTICLES') +
		'&nbsp;' +
		'<span id="var_' + partID + '" ' +
			'varname="' + variable + '" ' +
			'class="rules-variable">' +
		'X<sub>' + varIdx + '</sub>' + 
		'</span>&nbsp;' +
		gsrLanguage.getMessage('SR_BELONG_TO_CAT') +
		'&nbsp;' +
		'<span id="cat_' + partID + '" ' +
			'catname="' + escape(category) + '" ' +
			'class="rules-category">' +
		category +
		'</span>&nbsp;' +
		'<span id="buttons_' + partID + '">' +
			'<a href="javascript:smwhgCreateDefinitionRule.editCategoryCondition(' + partID + ')">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/edit.gif"/>' +
			'</a>' +
			'<a href="javascript:smwhgCreateDefinitionRule.removeCondition(' + partID + ')">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/delete.png"/>' +
			'</a>' +
		'</span>' + 		
'</div>';

	if (!update) {
		html +=
'<div id="AND' + partID + '">' +
	'<b>' +
	gsrLanguage.getMessage('SR_AND') +
	'</b>' +
'</div>';

		++this.numParts;
		html +=
'<div id="bodyPart' + this.numParts + '">' +
	catOrProp +
'</div>';
	}
	
	currPart.replace(html);
	
	this.showButtons(true);
	$('ruleBodyContent').show();
	
},

/**
 * Creates the section of the user interface where a property condition can be
 * defined.
 */
memberOfProperty: function() {
	
	$('sr-save-rule-btn').disable();
	
	var id = 'bodyPart'+this.numParts;
	var currPart = $('bodyPart'+this.numParts);
	
	var html;
	html = 
'<div id="bodyPart' + this.numParts + '">' +
	gsrLanguage.getMessage('SR_ALL_ARTICLES') +
		'&nbsp;' +
		this.createVariableSelector("sr-variable-selector", null, "X1") +
		'&nbsp;' +
		gsrLanguage.getMessage('SR_HAVE_PROP') +
		'&nbsp;' +
		'<input type="text" value="" id="sr-prop-name" class="wickEnabled" constraints="namespace:'+SMW_PROPERTY_NS+'"/>' +
		'&nbsp;' +
		gsrLanguage.getMessage('SR_WITH_VALUE') +
		'&nbsp;' +
		this.createVariableSelector("sr-value-selector", gsrLanguage.getMessage('SR_SIMPLE_VALUE'),"X1") +
		'&nbsp;' +
		this.createOperatorSelector("sr-op-selector") +
		'&nbsp;' +
		'<input type="text" value="" id="sr-prop-value" style="display:none" />' +
		'&nbsp;' +
		
		'<a href="javascript:smwhgCreateDefinitionRule.showPropertyCondition('+this.numParts+',false)">' +
			'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/add.png"/>' +
		'</a>' +
'</div>';

	currPart.replace(html);
	
	Event.observe('sr-value-selector', 'change', 
			      smwhgCreateDefinitionRule.propValueChanged.bindAsEventListener());
	
},

/**
 * Replaces the display of a property condition by the editable user interface.
 * 
 * @param partID of type int
 * 		Index of the part of the rule.
 */
editPropertyCondition: function(partID) {
	
	$('ruleBodyContent').hide();
	$('sr-save-rule-btn').disable();
	this.showButtons(false);
	
	var elem = $('var_'+partID);
	if (elem) {
		var val = elem.readAttribute('varname');
		elem.replace(this.createVariableSelector('sr-variable-selector', null, val));
	}
	elem = $('prop_'+partID);
	if (elem) {
		var val = unescape(elem.readAttribute('propname'));
		elem.replace('<input type="text" value="" id="sr-prop-name" class="wickEnabled" constraints="namespace:'+SMW_PROPERTY_NS+'"/>');
		$("sr-prop-name").value = val;
	}
	elem = $('value_'+partID);
	if (elem) {
		var val = unescape(elem.readAttribute('propvalue'));
		var type = unescape(elem.readAttribute('proptype'));
		var operand = unescape(elem.readAttribute('operand'));
		var select = (type == 'variable') ? val : gsrLanguage.getMessage('SR_SIMPLE_VALUE');
		var html = this.createVariableSelector("sr-value-selector", 
		                                       gsrLanguage.getMessage('SR_SIMPLE_VALUE'),select);
		
		html += this.createOperatorSelector("sr-op-selector", operand, true);
		html += '<input type="text" value="" id="sr-prop-value" style="display:none" class="wickEnabled" constraints="all"/>';
		elem.replace(html);
		if (type == 'value') {
			$("sr-prop-value").value = val;
			$("sr-prop-value").show();
		}
		Event.observe('sr-value-selector', 'change', 
				      smwhgCreateDefinitionRule.propValueChanged.bindAsEventListener());
		
	}
	
	elem = $('buttons_'+partID);
	if (elem) {
		new Insertion.After(elem,
			'<a href="javascript:smwhgCreateDefinitionRule.showPropertyCondition('+ partID +',true)">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/add.png"/>' +
			'</a>');
		elem.remove();
	}
	$('ruleBodyContent').show();
	
},

/**
 * This event function is called, when the value of the value selector for
 * property values has been changed.
 * If the value of a property is a variable, the input field for simple values
 * is hidden, otherwise it is shown. 
 * 
 * @param s selectorID
 * 		DOM-ID of the selector
 */
 propValueChanged: function(event) {
 	var val = event.target.options[event.target.selectedIndex].text;

 	var input = (event.target.id == 'sr-value-selector') 
	 				? $('sr-prop-value')
	 				: $('sr-prop-head-value');
	var opselector = $('sr-op-selector');
 	if (val == gsrLanguage.getMessage('SR_SIMPLE_VALUE')) {
 		input.show();
 		opselector.show();
 	} else {
 		input.hide();
 		opselector.hide();
 	}
 },

/**
 * After a property condition has been defined, it is displayed in a simplified
 * format without input fields etc. The section for defining the next condition
 * is added if <update> is 'false'. 
 * 
 * @param partID of type int
 * 		ID of the part where the category condition is added
 * @param update of type bool
 * 		If <true>, the current part is updated. The next condition will not be 
 * 		appended.
 */
 showPropertyCondition: function(partID, update) {
	var variable = $('sr-variable-selector')
	variable = variable.options[variable.selectedIndex].text;
	
	var property = $('sr-prop-name').value;
	var vsv = $('sr-value-selector');
	vsv = vsv.options[vsv.selectedIndex].text;
	
	var valueIsVariable = vsv != gsrLanguage.getMessage('SR_SIMPLE_VALUE');
	var value = (valueIsVariable) ? vsv 
	                              : $('sr-prop-value').value;

	if (property.length == 0) {
		// The name of the property must not be empty
		return;
	}
	if (!valueIsVariable && value.length == 0) {
		// The value of the property must not be empty if it is not a variable
		return;
	}
	$('ruleBodyContent').hide();
	$('sr-save-rule-btn').enable();

	var varIdx = variable.substr(1) * 1;
	
	if (varIdx > this.variables) {
		this.variables = varIdx;
	}
	
	var id = 'bodyPart'+partID;
	var currPart = $('bodyPart'+partID);

	var catOrProp = gsrLanguage.getMessage('SR_MCATPROP');
	catOrProp = catOrProp.replace(/\$1/g, '<a href="javascript:smwhgCreateDefinitionRule.memberOfCategory()">');
	catOrProp = catOrProp.replace(/\$2/g, '</a>');
	catOrProp = catOrProp.replace(/\$3/g, '<a href="javascript:smwhgCreateDefinitionRule.memberOfProperty()">');
	catOrProp = catOrProp.replace(/\$4/g, '</a>');

	var valueHTML;
	var escapedValue = escape(value);
	
	var opSelector = $('sr-op-selector');
	var selectedIndex = opSelector.selectedIndex;
	var operator = opSelector.options[selectedIndex];
	var operand = "operand=\""+opSelector.options[selectedIndex].value+"\"";
	
	var valueInfo = 'id="value_' + partID + '" ' +
			'propvalue="' + escapedValue + '"' +
			'proptype="' + (valueIsVariable ? 'variable"' : 'value"');
	
	if (valueIsVariable) {
		var vi = value.substr(1) * 1;
		valueHTML = '<span class="rules-variable" ' + valueInfo + '>' +
						'X<sub>' + vi + '</sub>' + 
					'</span>';
		if (vi > this.variables) {
			this.variables = vi;
		}
					
	} else {
		valueHTML = '<span class="rules-category"' + valueInfo + " "+operand+'>' +
						value + 
					'</span>';
	}
					
	var html;

	html =	
'<div id="bodyPart' + partID + '">' +
	gsrLanguage.getMessage('SR_ALL_ARTICLES') +
		'&nbsp;' +
		'<span id="var_' + partID + '" ' +
			'varname="' + variable + '" ' +
			'class="rules-variable">' +
			'X<sub>' + varIdx + '</sub>' + 
		'</span>&nbsp;' +
		gsrLanguage.getMessage('SR_HAVE_PROP') +
		'&nbsp;' +
		'<span id="prop_' + partID + '" ' +
			'propname="' + property + '" ' +
			'class="rules-category">' +
		property +
		'</span>&nbsp;' +
		gsrLanguage.getMessage('SR_WITH_VALUE') +
		'&nbsp;' +
		valueHTML + 
		'&nbsp;' +
		'<span id="buttons_' + partID + '">' +
			'<a href="javascript:smwhgCreateDefinitionRule.editPropertyCondition(' + partID + ')">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/edit.gif"/>' +
			'</a>' + (partID != '888888' ? // ie. not is head
			'<a href="javascript:smwhgCreateDefinitionRule.removeCondition(' + partID + ')">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/delete.png"/>' +
			'</a>' : '' ) +
		'</span>' +
'</div>';

	if (!update) {
		html +=
'<div id="AND' + partID + '">' +
	'<b>' +
	gsrLanguage.getMessage('SR_AND') +
	'</b>' +
'</div>';

		++this.numParts;
	html +=
'<div id="bodyPart' + this.numParts + '">' +
	catOrProp +
'</div>';
	}			
	
	currPart.replace(html);
	
	this.showButtons(true);
	$('ruleBodyContent').show();
	
},

/**
 * Retrieves the conditions from the user interface, creates a rule and saves it.
 */
saveRule: function(event) {

	function ajaxResponseAddRule(request) {
		this.hidePendingIndicator();			
		if (request.status == 200) {
			// success

			if ($('rule-name')) {
				this.ruleName = $('rule-name').value;
			}			
			
			var ruleText = 
				"\n\n" +
				'<rule  ' +
				      'name="' + this.ruleName + '" ' +
				      'type="' + this.ruleType + '">' + "\n" +
				request.responseText +
				"\n</rule>\n";
			 	
			// hide the rule editor GUI
			$('createRuleContent').remove();
			
			// show normal wiki text editor GUI
			$('content').show();
			
			if (this.annotation) {
				// update an existing annotation
				this.annotation.replaceAnnotation(ruleText); 
			} else {
				// append the text to the edit field
                if (gEditInterface)
                    gEditInterface.setValue(gEditInterface.getValue() + ruleText);
                else {
                    var ei = new SMWEditInterface();
                    ei.setValue(ei.getValue() + ruleText);
                }
			}
			ruleToolBar.fillList(true);
						 	
		} else {
		}
	};

	var xml = this.serializeRule();

	this.showPendingIndicator($('sr-save-rule-btn'));
	
	sajax_do_call('smwf_sr_AddRule', 
	          [this.ruleName, xml], 
	          ajaxResponseAddRule.bind(this));
	
},

/**
 * Removes the condition with the given ID.
 * 
 * @param partID of type int
 * 		Index of the condition to be removed
 */
 removeCondition: function(partID) {
 	if ($('bodyPart'+partID)) {
 		$('bodyPart'+partID).remove();
 	}
 	if ($('AND'+partID)) {
 		$('AND'+partID).remove();
 	}
 },

/**
 * Creates the HTML-code for the variable selector.
 * 
 * @param id of type string
 * 		ID of the selector
 * @param option of type string
 * 		One additional option that is appended
 * @param select of type string
 * 		If this item occurrs in the list of options, it will be selected.
 */
createVariableSelector: function(id, option, select) {
	var html =
		'<select id ="' + id + '">';
	
	for (var i = 1; i <= this.variables + 1; ++i) {
		var variable = 'X'+i;
		if (select == variable) {
			variable = 'selected="selected" value="'+variable+'">' + variable;
		} else {
			variable = '>' + variable;
		}
		html += '<option ' + variable + '</option>';
	}
	if (option != undefined && option != null) {
		html += (select == option) 
					? '<option selected="selected" value="'+option+'">' + option + '</option>'
					: '<option value="'+option+'">' + option + '</option>';
	}	
	html +=		
		'</select>';
		
	return html;
	
},


createOperatorSelector: function(id, defaultValue, visible) {
	
	var visibilityAtt = '';
	if (!visible) {
		visibilityAtt = 'style="display: none;"';
	}
	var html =
		'<select id ="' + id + '" '+visibilityAtt+'>';
	if (defaultValue == 'eql' || defaultValue == null) {
		html += '<option selected="selected" value="eql">=</option>';
	} else {
		html += '<option value="eql">=</option>';
	}
	
	if (defaultValue == 'lt') {
		html += '<option selected="selected" value="lt">&lt;</option>';
	} else {
		html += '<option value="lt">&lt;</option>';
	}
	
	if (defaultValue == 'lte') {
		html += '<option selected="selected" value="lte">&lt;=</option>';
	} else {
		html += '<option value="lte">&lt;=</option>';
	}
	
	if (defaultValue == 'gt') {
		html += '<option selected="selected" value="gt">&gt;</option>';
	} else {
		html += '<option value="gt">&gt;</option>';
	}
	
	if (defaultValue == 'gte') {
		html += '<option selected="selected" value="gte">&gt;=</option>';
	} else {
		html += '<option value="gte">&gt;=</option>';
	}

	html +=		
		'</select>';
	return html;
},

/**
 * Shows or hides the edit and delete buttons to the right of a condition.
 * 
 * @param show of type bool
 * 		If <true>, buttons are shown, otherwise they are hidden.
 */
showButtons: function(show) {
	for (var i = 0; i < this.numParts; ++i) {
		var b = $('buttons_'+i);
		if (b) {
			if (show) {
				b.show();
			} else {
				b.hide();
			}
		}
	}
},

/*
 * Shows the pending indicator on the element with the DOM-ID <onElement>
 * 
 * @param onElement of type string
 * 			DOM-ID if the element over which the indicator appears
 */
showPendingIndicator: function(onElement) {
	this.hidePendingIndicator();
	this.pendingIndicator = new OBPendingIndicator($(onElement));
	this.pendingIndicator.show();
},

/*
 * Hides the pending indicator.
 */
hidePendingIndicator: function() {
	if (this.pendingIndicator != null) {
		this.pendingIndicator.hide();
		this.pendingIndicator = null;
	}
},

/**
 * Creates an XML representation of the current rule.
 */
serializeRule: function() {
	
	var xml;
	
	var title = wgTitle.replace(/ /g,'_');
	
	xml = '<?xml version="1.0" encoding="UTF-8"?>' +
		  '<SimpleRule>';
	
	// serialize head
	xml += '<head>';
	if (wgNamespaceNumber == 14) {
		// create a category rule
		xml += '<category>' +
				'<name>' +
					title +
				'</name>' +
				'<subject>X1</subject>' +
				'</category>';
	} else if (wgNamespaceNumber == 102) {
		// create a property rule
		xml += '<property>' +
				'<subject>X1</subject>' +
				'<name>' +
					title +
				'</name>';
		
		var isVariable = false;
		var value = '';
		var val = $('value_888888');
		if (val) {
			isVariable = (val.readAttribute('proptype') == 'variable');
			value = val.readAttribute('propvalue');
		} else {
			var val = $('sr-head-value-selector').value;
			isVariable = (val != gsrLanguage.getMessage('SR_SIMPLE_VALUE'));
			value = isVariable 
						? val
						: $('sr-prop-head-value').value;
		}
		
		if (isVariable) {
			xml += '<variable>'+value+'</variable>';
	 	} else {
	 		
		 	xml += '<value>'+value+'</value>';
	 	}
		xml += '</property>';
		
	}
	
	xml += '</head>';
		  
	// serialize body
	xml += '<body>';
	for (var i = 0; i < this.numParts; ++i) {
		var subject = $('var_'+i);
		if (!subject) {
			// a gap in the rule parts
			continue;
		}
		subject = '<subject>'+subject.readAttribute('varname')+'</subject>';
		var cat = $('cat_'+i);
		if (cat) {
			var catName = unescape(cat.readAttribute('catname'));
			catName = catName.replace(/ /g, '_');
			// variable belongs to a category
			xml += '<category>' +
					'<name>' +
						catName +
					'</name>' +
					subject +
					'</category>';
		}
		var prop = $('prop_'+i);
		if (prop) {
			// variable belongs to a property
			xml += '<property>' +
					subject +
					'<name>' +
						unescape(prop.readAttribute('propname')).replace(/ /g, '_') +
					'</name>';
			var value = $('value_'+i);
			if (value.readAttribute('proptype') == 'variable') {
				xml += '<variable>'+value.readAttribute('propvalue')+'</variable>';
			} else {
				var operand = value.readAttribute('operand');
				var operandText = operand == '=' || operand == null ? 'operand="eql"' : 'operand="'+operand+'"';
				xml += '<value '+operandText+'>'+value.readAttribute('propvalue')+'</value>';
			}
			xml += '</property>';
		}
	}
		  
	xml += '</body></SimpleRule>';
	
	return xml;
	
},

/**
 * Checks if the rule that is currently presented in the UI is valid i.e. if
 * all variables are transitively connected to X1 via a property.
 * 
 */
checkRule: function() {
	
	var variables = []; // Array of all variables in the rule
	var connections = []; // Array of all connected variables e.g. [[X1, X2], [X2, X3]]
	for (var i = 0; i < this.numParts; ++i) {
		var subject = $('var_'+i);
		if (!subject) {
			// a gap in the rule parts
			continue;
		}
		subject = subject.readAttribute('varname');
		if (variables.indexOf(subject) == -1) {
			variables.push(subject);
		}
		var prop = $('prop_'+i);
		if (prop) {
			// variable belongs to a property
			var value = $('value_'+i);
			if (value.readAttribute('proptype') == 'variable') {
				var object = value.readAttribute('propvalue');
				connections.push([subject, object]);
			}
		}
	}
	
	// build transitive connections
	while (true) {
		var connAdded = false;
		for (var i = 0, n = connections.length; i < n; ++i) {
			var currSubj = connections[i][0];
			
			//TODOs
		}
	}
}



};// End of Class

window.smwhgCreateDefinitionRule = null;
