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
var CalculationRule = Class.create();

 		

CalculationRule.prototype = {


/**
 * Constructor
 * @param string ruleName
 * 		Name of the rule
 * @param string ruleType
 * 		Type of the rule e.g. Calculation, Property Chaining, Deduction, Mapping
 */
initialize: function(ruleName, ruleType) {
	this.ruleName = ruleName;
	this.ruleType = ruleType;
	smwhgCreateCalculationRule = this;
	this.pendingIndicator = null;
	this.variableSpec = '';
	this.annotation = null;
	
},

/**
 * @public
 * 
 * Creates the initial user interface of the calculation rules editor.
 * 
 * @param string ruleText
 * 		If this parameter is defined, an existing rule will be edited otherwise
 * 		a new rule will be created.
 */
editRule: function(ruleAnnotation) {
	
	if (ruleAnnotation == undefined) {
		this.createUI();
	} else {
		// parse the rule text
		this.annotation = ruleAnnotation;
		var ruleText = ruleAnnotation.getAnnotation();
		var rule = FormulaRuleParser.parseRule(ruleText);
		this.createUI(rule);
		$('variablesDiv').show();
	}
	
},


/**
 * Cancels editing or creating the rule. Closes the rule edit part of the UI and
 * reopens the wiki text edit part.
 *  
 */
cancel: function() {
	
	$('bodyContent').show();
	if ($('createRuleContent')) {
		$('createRuleContent').remove();
	}
		
},

/**
 * @private
 * 
 * Creates the initial user interface of the calculation rules editor.
 * 
 * @param FormulaRule parsedRule
 * 		If this parameter is defined, it contains a representation of the parsed
 * 		rule that defines the content of the GUI.
 */
createUI: function(parsedRule) {
	// hide the wiki text editor
	var bodyContent = $('bodyContent');
	bodyContent.hide();
	var html;
					
	html = this.getHTMLRuleFramework(parsedRule);

	new Insertion.After(bodyContent, html);
	
	var opHelp = this.operatorHelpHTML();
	if (!$('sr-calc-op-help')) {
		new Insertion.After('contenttabposdiv', opHelp);
	}	
	
	Event.observe('sr-save-rule-btn', 'click', 
			      smwhgCreateCalculationRule.saveRule.bindAsEventListener(smwhgCreateCalculationRule));
	
},


/**
 * Returns the HTML structure of the rule interface consisting of the formula 
 * part, the variable definition area and the preview area.
 * 
 * @param FormulaRule parsedRule
 * 		If defined, it contains the parsed representation of the rule for the
 * 		initial GUI.
 */
getHTMLRuleFramework: function(parsedRule) {	
	var derive = gLanguage.getMessage('SR_DERIVE_BY');
	derive = derive.replace(/\$1/g, wgCanonicalNamespace);
	derive = derive.replace(/\$2/g, '<span class="rules-category">'+wgTitle+'</span>');
	
	var defFormulaHTML   = (parsedRule == undefined)
								? this.defineFormulaHTML(parsedRule)
								: this.confirmedFormulaHTML(parsedRule);
	var defVariablesHTML = this.defineVariablesHTML(parsedRule);
	var previewHTML      = this.previewHTML();
	
	html = 
'<div id="createRuleContent" class="rules-complete-content">' +
'	<div id="headBodyDiv" style="padding-top:5px">' +
		defFormulaHTML +
		defVariablesHTML +
		previewHTML +
'	   <input type="submit" accesskey="s" value="' +
			gLanguage.getMessage('SR_SAVE_RULE') +
'			" name="sr-save-rule-btn" id="sr-save-rule-btn"/>' +
'	</div>' +
'</div>';
	return html;
},

/**
 * @private
 * 
 * This function returns the HTML of the upper part of the GUI where the formula
 * is entered. This part allows editing the formula. 
 * 
 * @param FormulaRule parsedRule
 * 		If defined, it contains the parsed representation of the rule for the
 * 		initial GUI.
 */
defineFormulaHTML: function(parsedRule) {
	
	var formulaResult = wgTitle;
	var initialFormula = (parsedRule == undefined) ? '' : parsedRule.getFormula();
	var enterFormula = gLanguage.getMessage('SR_ENTER_FORMULA');
	enterFormula = enterFormula.replace(/\$1/g, formulaResult);
	 
	var html = 
'<div id="formulaDiv" class="rules-frame">' +
'	<div id="formulaIntro" class="rules-content">' +
		enterFormula +
'	</div>' +
'	<div id="formulaInput" class="rules-content">' +
		formulaResult + '&nbsp;=&nbsp;' + 
'		<input type="text" style="width:60%" ' +
'				value="'+initialFormula+'" id="sr-formula" />' +
'		&nbsp;' +
'		<img id="sr-op-help-img"' +
'			 src="' + wgScriptPath + '/extensions/SMWHalo/skins/help.gif"' +
'			 onmouseover="smwhgCreateCalculationRule.showOpHelp(true)"' +
'			 onmouseout="smwhgCreateCalculationRule.showOpHelp(false)"' +
'		/>' +
'	</div>' +
'	<div id="formulaErrorMsgDiv" class="rules-content rules-err-msg" style="display:none">' +
'		<span id="formulaErrorMsg" />' +
'	</div>' +
'	<div id="formulaSubmit" class="rules-content">' +
'		<a href="javascript:smwhgCreateCalculationRule.submitFormula()">' +
			gLanguage.getMessage('SR_SUBMIT') +
'		</a>' +
'	</div>' +
'</div>';

	return html;
},

/**
 * @private
 * 
 * This function returns the HTML of the upper part of the GUI where the formula
 * has already been confirmed. This part no longer allows editing the formula. 
 * 
 * @param FormulaRule parsedRule
 * 		Contains the parsed representation of the rule with the definition of
 * 		the formula.
 */
confirmedFormulaHTML: function(parsedRule) {
	
	var formulaResult = wgTitle;
	var initialFormula = parsedRule.getFormula();
	var syntaxChecked = gLanguage.getMessage('SR_SYNTAX_CHECKED');
	var edit = gLanguage.getMessage('SR_EDIT_FORMULA');
	 
	var html = 
'<div id="confirmedFormulaDiv" class="rules-frame" style="border-bottom:0px">' +
'	<div id="confFormulaInput" class="rules-content">' +
		formulaResult + '&nbsp;=&nbsp;' + 
'		<input type="text" style="width:50%"' +
'			value="'+initialFormula+'" id="sr-formula" readonly="readonly" />' +
'		&nbsp;' +
'		<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/checkmark.png"/>' +
		syntaxChecked +
'		&nbsp;' +
'		<span style="position:absolute; right:1em">' +		
'			<a href="javascript:smwhgCreateCalculationRule.editFormula()">' +
				edit +
'			</a>' +
'		</span>' +
'	</div>' +
'</div>';

	return html;
},

/**
 * @private
 * 
 * This function returns the HTML of the middle part of the GUI where the variables
 * are specified. This part is initially invisible.
 * 
 * @param FormulaRule parsedRule
 * 		If defined, it contains the parsed representation of the rule for the
 * 		initial GUI.
 */
defineVariablesHTML: function(parsedRule) {
	var specifyVariables = gLanguage.getMessage('SR_SPECIFY_VARIABLES');

	var html =
'<div id="variablesDiv" class="rules-frame" style="display:none">' +
'	<div id="variableIntro" class="rules-content">' +
		specifyVariables +
'	</div>' +
'	<div id="variableInput">' +
	this.allVariableSpecificationsHTML(parsedRule) +
'	</div>' +
'</div>';
	
	return html;
},

/**
 * @private
 * 
 * This function returns the HTML for the specification of all variables in
 * the given rule.
 * 
 * @param FormulaRule parsedRule
 * 		Contains the parsed representation of the rule for the
 * 		initial GUI.
 * 
 * @return string
 * 		HTML for all variable specifications
 */
allVariableSpecificationsHTML: function(parsedRule) {
	
	if (parsedRule == undefined) {
		return "";
	}
	var variables = parsedRule.getVariables();
	
	var html =
		'<div class="rules-content">' +
		'	<table style="overflow:hidden; border-color:#aaaaaa" rules="groups" cellpadding="10">';
			
	for (var i = 0, n = variables.length; i < n; ++i) {
		v = variables[i];
		html += this.variableSpecificationHTML(v.name, v.type, v.value);
	}
	
	html += '</table></div>';
	
	return html;
}, 

/**
 * @private
 * 
 * This function returns the HTML for the specification of one variable.
 * 
 * @param string variable
 * 		The name of the variable
 * @param string type
 * 		The type of the variable (i.e. 'prop' or 'const')
 * @param string value
 * 		Depending in the type this is the name of the property or the value
 * 		of the constant.
 * 
 * @return string
 * 		The HTML that allows editing the variable's specification.
 * 
 */
 variableSpecificationHTML: function(variable, type, value) {
 	var varDef = '<span class="calc-rule-variable">'+variable+ '</span>' +
 	             " " + gLanguage.getMessage('SR_IS_A');
 	var propValue = gLanguage.getMessage('SR_PROPERTY_VALUE');
 	var absTerm   = gLanguage.getMessage('SR_ABSOLUTE_TERM');
 	var radioName = 'sr-radio-'+variable;
 	
 	// Initialize variables for type 'prop'
 	var propChecked = 'checked="checked"';
 	var termChecked = '';
 	var propInputVisible = '';
 	var termInputVisible = 'style="display:none"';
 	var property = value;
 	var term = gLanguage.getMessage('SR_ENTER_VALUE');
 	
 	if (type == 'const') {
 		// Change variables for type 'const'
	 	propChecked = '';
	 	termChecked = 'checked="checked"';
	 	propInputVisible = 'style="display:none"';
	 	termInputVisible = '';
	 	property = gLanguage.getMessage('SR_ENTER_PROPERTY');
	 	term = value;
 	}
 	
 	if (value == undefined || value == '') {
 		property = gLanguage.getMessage('SR_ENTER_PROPERTY');
 		term = gLanguage.getMessage('SR_ENTER_VALUE');
 	}
 	
 	var html =
'	<tbody>' +
'		<tr>' +
'			<td>' + varDef + '</td>' +
'			<td>' +
'				<input type="radio" ' +
'                      id="sr-radio-prop-'+variable+'"' +
'                      name="'+radioName+'"'+
'                      onchange="smwhgCreateCalculationRule.radioChanged(this.id)"' +
'					   varname="'+variable+'"'+
'					   value="property" '+propChecked+'>' + 
				propValue + 
           '</td>' +
'			<td><input type="text" value="'+property+'"'+
'					   id="sr-input-prop-'+variable+'"' +
					   propInputVisible +
'					   class="wickEnabled" ' +
'					   onfocus="smwhgCreateCalculationRule.inputFocus(this)"' +
'					   typeHint="'+SMW_PROPERTY_NS+'">' +
           '</td>' +
'		</tr>' +
'		<tr>' +
'			<td></td>' +
'			<td>' +
'				<input type="radio" ' +
'                      id="sr-radio-term-'+variable+'"' +
'                      name="'+radioName+'"'+
'                      onchange="smwhgCreateCalculationRule.radioChanged(this.id)"'+
'					   varname="'+variable+'"'+
'                      value="term" '+termChecked+'>' + 
				absTerm + 
			'</td>' +
'			<td><input type="text" value="'+term+'"'+
					   termInputVisible +
'					   onfocus="smwhgCreateCalculationRule.inputFocus(this)"' +
'					   id="sr-input-term-'+variable+'"' +
'				>' +
           '</td>' +
'		</tr>' +
'	</tbody>';

	return html;
},

/**
 * Returns the HTML of the help box that shows all available operators.
 */
operatorHelpHTML: function() {
	
	var html =
'<div id="sr-calc-op-help" style="position:absolute; top:100px; left:100px; z-index:2; display:none; ">' +
'<table id="sr-calc-op-help-table" border="1" rules="groups">' +
'  <thead class="sr-calc-op-help-table-head">' +
'    <tr>' +
'      <th colspan="4">' + gLanguage.getMessage("SR_OP_HELP_ENTER") + '</th>' +
'    </tr>' +
'  </thead>' +
'  <tbody>' +
'    <tr>' +
'      <td>+</td><td>' + gLanguage.getMessage("SR_OP_ADDITION") + '</td>' +
'      <td>sqrt()</td><td>' + gLanguage.getMessage("SR_OP_SQUARE_ROOT") + '</td>' +
'    </tr>' +
'    <tr>' +
'      <td>-</td><td>' + gLanguage.getMessage("SR_OP_SUBTRACTION") + '</td>' +
'      <td>^</td><td>' + gLanguage.getMessage("SR_OP_EXPONENTIATE") + '</td>' +
'    </tr>' + 
'    <tr>' +
'      <td>*</td><td>' + gLanguage.getMessage("SR_OP_MULTIPLY") + '</td>' +
'      <td>sin()</td><td>' + gLanguage.getMessage("SR_OP_SINE") + '</td>' +
'    </tr>' +
'    <tr>' +
'      <td>/</td><td>' + gLanguage.getMessage("SR_OP_DIVIDE") + '</td>' +
'      <td>cos()</td><td>' + gLanguage.getMessage("SR_OP_COSINE") + '</td>' +
'    </tr>' +
'    <tr>' + 
'      <td>%</td><td>' + gLanguage.getMessage("SR_OP_MODULO") + '</td>' +
'      <td>tan()</td><td>' + gLanguage.getMessage("SR_OP_TANGENT") + '</td>' +
'    </tr>' +
'  </tbody>' +
'</table>' +
'</div>';	 
	return html;
},


/**
 * Callback function for the focus event of the input fields of the variable 
 * specification. If the field contains the initial text like "Enter a property...",
 * the input field is cleared.
 *   
 */
inputFocus: function(object) {
	if (object.value == gLanguage.getMessage('SR_ENTER_VALUE')
	    || object.value == gLanguage.getMessage('SR_ENTER_PROPERTY')) {
		object.value = '';
	}
},

/**
 * @private
 * 
 * This function returns the HTML of the lower part of the GUI where the 
 * preview for the rule is rendered.
 * 
 */
previewHTML: function() {
	
	var html =
'<div id="implicationsDiv" class="rules-frame" style="display:none">' +
'	<div id="implicationsTitle" class="rules-title" style="width:auto;">' +
		gLanguage.getMessage('SR_DERIVED_FACTS') +
'	</div>' +
'	<div id="implicationsContent" class="rules-content">' +
'	</div>' +
'</div>';

	return html;
	
},

/**
 * @public
 * 
 * Callback function for the "Submit..." link. The input area of the formula
 * is replaced with the confirmed formula if the formula is syntactically 
 * correct. The definition area for variables is opened.
 */
submitFormula: function() {
	if ($('formulaDiv')) {
		var formula = $('sr-formula').value;
		this.checkFormula(formula);
	}	
},

/**
 * Closes the variables section and reopens the formula editor.
 */
editFormula: function() {
	$('variablesDiv').hide();
	$('variableInput').innerHTML = "";
	var html = this.defineFormulaHTML(new FormulaRule($('sr-formula').value));
	$('confirmedFormulaDiv').replace(html);
},

/**
 * @public
 * 
 * Shows or hides the operator help box.
 * 
 * @param bool doShow
 * 		
 */
showOpHelp: function(doShow) {
	var help = $('sr-calc-op-help');
	if ($('sr-calc-op-help')) {
		var helpImg = $('sr-op-help-img');
		var l = ""+(helpImg.x-help.getWidth()+20)+"px";
		var t = ""+(helpImg.y + 45)+"px";
		help.setStyle({left:l, top:t});
		if (doShow) {
			$('sr-calc-op-help').show();
		} else {
			$('sr-calc-op-help').hide();
		}
	}
},

/**
 * The radio button in a variable specification has been changed. The corresponding
 * input field is shown and the other is hidden. 
 */
radioChanged: function(radioID) {
	var inputID = radioID.replace(/-radio-/,'-input-');
	var radio = $(radioID);
	$(inputID).show();
	$(inputID).focus();
	
	if ($(inputID).value == '') {
		// The input field is empty => show the request to enter something
		$(inputID).value = (inputID.indexOf('-input-prop-') > 0)
				? gLanguage.getMessage('SR_ENTER_PROPERTY')
				: gLanguage.getMessage('SR_ENTER_VALUE');
		$(inputID).select();
	}
		
	if (inputID.indexOf('-input-prop-') > 0) {
		inputID = inputID.replace(/-input-prop-/,'-input-term-');
	} else {
		inputID = inputID.replace(/-input-term-/,'-input-prop-');
	}
	$(inputID).hide();
	
},

/**
 * @private
 * 
 * Checks if the formula is syntactically correct. If it is, the part for specifying
 * variables is opened, otherwise an error message is shown. 
 * 
 * @param string formula
 * 		The formula to be checked
 */
checkFormula: function(formula) {
	
	function ajaxResponseParseFormula(request) {
		this.hidePendingIndicator();			
		if (request.status == 200) {
			// success
			var result = request.responseText;
			var variables = result.split(',');
			if (variables[0] == 'error') {
				// The formula is erroneous
				$('formulaErrorMsg').innerHTML = result.substr(6);
				$('formulaErrorMsgDiv').show();
			} else if (variables.size() == 2 && variables[1] == '') {
				// There is no variable in the formula
				$('formulaErrorMsg').innerHTML = gLanguage.getMessage('SR_NO_VARIABLE');
				$('formulaErrorMsgDiv').show();
			} else {
				var rule = new FormulaRule(formula);
				var varArray = new Array();
				for (var i = 1; i < variables.size(); ++i) {
					varArray.push({name: variables[i]});
				}
				rule.setVariables(varArray);
				confFormula = this.confirmedFormulaHTML(rule);
				$('formulaDiv').replace(confFormula);
				
				var varSpec = this.allVariableSpecificationsHTML(rule);
				$('variableInput').innerHTML = varSpec;
				$('variablesDiv').show();
			}
		} else {
		}
	};

	this.showPendingIndicator($('sr-formula'));
	
	sajax_do_call('smwf_sr_ParseFormula', 
	          [formula], 
	          ajaxResponseParseFormula.bind(this));

	return false;
},

/**
 * @private
 * 
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
				'<rule hostlanguage="f-logic" ' +
				      'name="'+this.ruleName+'" ' +
				      'type="' + this.ruleType + '" ' +
				      'formula="'+$('sr-formula').value+'" ' +
				      'variableSpec="'+this.variableSpec+'">' + "\n" +
				request.responseText +
				"\n</rule>\n";
			 	
			// hide the rule editor GUI
			$('createRuleContent').remove();
			
			// show normal wiki text editor GUI
			$('bodyContent').show();
			
			if (this.annotation) {
				// update an existing annotation
				this.annotation.replaceAnnotation(ruleText); 
			} else {
				// append the text to the edit field
				var ei = new SMWEditInterface();
				ei.setValue(ei.getValue() + ruleText);
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
 * @private
 * 
 * Shows the pending indicator on the element with the DOM-ID <onElement>
 * 
 * @param string onElement
 * 			DOM-ID if the element over which the indicator appears
 */
showPendingIndicator: function(onElement) {
	this.hidePendingIndicator();
	this.pendingIndicator = new OBPendingIndicator($(onElement));
	this.pendingIndicator.show();
},

/**
 * @private
 * 
 * Hides the pending indicator.
 */
hidePendingIndicator: function() {
	if (this.pendingIndicator != null) {
		this.pendingIndicator.hide();
		this.pendingIndicator = null;
	}
},

/**
 * @private 
 * 
 * Creates an XML representation of the current rule.
 */
serializeRule: function() {
	
	var xml;
	
	this.variableSpec = '';
	xml = '<?xml version="1.0" encoding="UTF-8"?>' +
		  '<SimpleRule>';
		  
	// serialize property and formula
	xml += '<formula>' +
				'<property>'+wgTitle.replace(/ /g,'_')+'</property>' +
				'<expr>' +
					$('sr-formula').value +
				'</expr>';
			  
	// serialize variables
	var vi = $('variableInput');
	var radios = vi.getElementsBySelector('[type="radio"]');
	for (var i = 0, n = radios.size(); i < n; ++i) {
		var r = radios[i];
		if (r.checked) {
			xml += '<variable>';
			var varname = r.readAttribute('varname');
			xml += '<name>'+varname+'</name>';
			this.variableSpec += varname + '#';
			var inputId = r.id.replace(/-radio-/, '-input-');
			var value = $(inputId).value;
			if (r.id.indexOf('-radio-prop-') > 0) {
				xml += '<property>' + value + '</property>';
				this.variableSpec += 'prop#' + value + ';';
			} else {
				xml += '<constant>' + value + '</constant>';
				this.variableSpec += 'const#' + value + ';';
			}
			xml += '</variable>';
		}
	}
	xml += '</formula></SimpleRule>';
	
	return xml;
	
}

};// End of Class

var FormulaRule = Class.create();

FormulaRule.prototype = {
	
/**
 * Constructor
 * @param string formula
 * 		The formula
 */
initialize: function(formula) {
	this.formula = formula;
	this.variables = null;
},

getFormula: function() {
	return this.formula;
},

/**
 * Sets the variables of the rule. 
 * 
 * @param array<{name,type,value}> variables
 * 		An array of variable definitions. A definition is an object with the
 * 		fields name, type and value.
 */
setVariables: function(variables) {
	this.variables = variables;
},

getVariables: function() {
	return this.variables;
}

}; // End of class FormulaRule

var FormulaRuleParser = {
	
/**
 * A calculation rule in the wiki text starts with the rule element (<rule ...>).
 * This element has a formula- and a variableSpec-attribute. These attributes
 * are used to create a formula rule object of type (FormulaRule).
 * 
 * @param string ruleText
 * 		The text of the rule beginning with the <rule> element
 * 
 * @return bool/FormulaRule
 * 		false, if the rule element does not contain a valid formula of variables or a
 * 		FormulaRule, if the specification is correct.
 */	
parseRule: function(ruleText) {
	var ruleSpec = ruleText.match(/(<rule.*?>)/)
	if (ruleSpec.size() != 2) {
		return false;
	}
	ruleSpec = ruleSpec[1];
	
	var formula = ruleSpec.match(/formula\s*=\s*\"(.*?)\"/);
	if (formula.size() != 2) {
		return false;
	}
	formula = formula[1];
	
	var rule = new FormulaRule(formula);
	
	var variables = ruleSpec.match(/variableSpec\s*=\s*\"(.*?)\"/);
	if (variables.size() != 2) {
		return false;
	}
	variables = variables[1];
	varArray = new Array();

	var vars = variables.split(/;/);
	
	for (var i = 0, n = vars.size(); i < n; ++i) {
		var varspec = vars[i];
		var parts = varspec.split(/#/);
		if (parts.size() == 3) {
			var v = {
				name: parts[0],
				type: parts[1],
				value: parts[2]
			}
			varArray.push(v);
		}
	}
	rule.setVariables(varArray);
	
	return rule;
}
	
};


//  
var smwhgCreateCalculationRule = null;
