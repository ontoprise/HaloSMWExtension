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
*   Author: Benjamin Langguth, Ingo Steinbauer
*   This file is part of the Data Import-Extension.
*/

var $ = $P;

var TermImportPage = Class.create();

TermImportPage.prototype = {
	initialize: function() {
		this.currentSelectedDAM = null;
		/*if (wgCanonicalSpecialPageName != 'Gardening') return;*/
	},
	
	/**
	 * Formats the selected DAM entry correctly when mouseout
	 */
	showRightDAM: function(e, node){
		if (this.currentSelectedDAM!=node) {
			Element.removeClassName(node,'entry-over');
			Element.addClassName(node,'entry');
		}else{
			Element.removeClassName(node,'entry-over');
			Element.addClassName(node,'entry-active');
		}
	},
	
	/*
	 * function for getting all DAMs for the chosen TLM
	 */
	getDAL: function(e, node, dalID) {
		if (this.currentSelectedDAM) {
			Element.removeClassName(this.currentSelectedDAM,'entry-active');
			Element.addClassName(this.currentSelectedDAM,'entry');
		}
		Element.removeClassName(node,'entry');
		Element.addClassName(node, 'entry-active');
		this.currentSelectedDAM = node;
		this.currentSelectedDAM.dalID = dalID;
		
		if (this.pendingIndicatorDALDesc == null && this.pendingIndicatorSourceSpec == null) {
			this.pendingIndicatorDALDesc = new OBPendingIndicator($('daldesc'));
			this.pendingIndicatorSourceSpec = new OBPendingIndicator($('source-spec'));
		}
		this.pendingIndicatorDALDesc.show();
		this.pendingIndicatorSourceSpec.show();
		sajax_do_call('dif_ti_connectDAM', [dalID , '', '', '', '', '','', '', 0], this.getDALCallback.bind(this, dalID));
	},
	
	/*
	 *  Callback function for getting all DAMs for the chosen TLM
	 */
	getDALCallback: function(dalID, request){
		this.pendingIndicatorDALDesc.hide();
		this.pendingIndicatorSourceSpec.hide();
		
		//DOM object and XML parsing...
		var result = request.responseText;
		var list = GeneralXMLTools.createDocumentFromString(result);
		
		//get all DALModules from the list
		var damDesc = list.getElementsByTagName("damdescription")[0].firstChild.nodeValue;
		$('daldesc').innerHTML = damDesc;

		//create the right input-div
		this.createDataSourceWidget (
				list.getElementsByTagName("DataSource")[0].childNodes, dalID);
	},
	
	createDataSourceWidget : function(datasources, dalID) {
		response = diLanguage.getMessage('smw_ti_sourceinfo')
				+ "<br><br><Table>";
				//+ diLanguage.getMessage('smw_ti_source') + "&nbsp;";

		var fieldnumber = 0;
		for ( var i = 0, n = datasources.length; i < n; i++) {
			// get one of the datasources
			var datasource = datasources[i];
			
			if (datasource.nodeType == 1) {
				var tag = datasource.tagName;
				
				if (datasource.getAttribute('display')) {
					var attrib_display = datasource.getAttribute('display');
				}
				if (datasource.getAttribute('type')) {
					var attrib_type = datasource.getAttribute('type');
				}
				var attrib_class = '';
				if (datasource.getAttribute('class')) {
					attrib_class = datasource.getAttribute('class');
				}
				if (attrib_display) {
					var size = "25";
					var rows = "5";
					
					if(datasource.getAttribute('rows')){
						rows = datasource.getAttribute('rows');
					}
					
					response += "<tr><td style=\"vertical-align:top\">"
						+ attrib_display
						+ "</td><td style=\"vertical-align:top\">";
					
					if (attrib_type == "file") {
						response += "<input name=\"source\" id=\""
								+ attrib_display
								+ "\" class=\"inputfield " + attrib_class + "\" type=\"file\" maxlength=\"100\" value=\""
								+ datasource.textContent + "\"/>" + "</td></tr>";
					} else if(attrib_type == "checkbox"){
						response += "<input name=\"source\" id=\""
							+ attrib_display
							+ "\" class=\"inputfield\" type=\"" + attrib_type + "\" style=\"width:auto;margin:0;\" checked=\""
							+ datasource.textContent + "\"/></td></tr>";
					} else if (attrib_type == "textarea") {
						response += "<textarea name=\"source\" type=\"text\" id=\""
							+ attrib_display
							+ "\" class=\"inputfield " + attrib_class + "\" rows=\"" + rows + "\" value=\""
							+ datasource.textContent + "\">" + datasource.textContent + "</textarea>" + "</td></tr>";
					} else { //type is text
						response += "<input name=\"source\" id=\""
								+ attrib_display+ "\" class=\"" + attrib_class + " ";
						if(datasource.getAttribute('autocomplete')){
							var constraint = datasource.getAttribute('autocomplete');
							response += " wickEnabled\" constraints=\"" + constraint + "\" ";
						} else {
							response += "\" ";
						}
					
						if(datasource.firstChild){
							if(datasource.firstChild.nodeValue){
								response += " type=\"" + attrib_type + "\" size=\"" + size + "\" maxlength=\"100\" value=\""
									+ datasource.firstChild.nodeValue + "\"/></td></tr>";
							} else {
								response += " type=\"" + attrib_type + "\" maxlength=\"100\" value=\""
								+ "\"/></td></tr>";
							}
						} else {
							response += " type=\"" + attrib_type + "\" maxlength=\"100\" value=\""
							+ "\"/></td></tr>";
						}
					}
					response += "<input type=\"hidden\" id=\"tag_"
							+ attrib_display + "\" value=\"" + tag + "\"/>";
				}
			}
		}
		
		response += "</table><br><button id=\"submitSource\" type=\"button\" name=\"run\" " +
				"onclick=\"termImportPage.getSource(event, this,"
				+ "'" + dalID + "')\">Next step</button>";
		
		// fade in the source specification
		$('source-spec').innerHTML = response;
	},
	
	getSource: function(e, node, dalID) {
		dalID = this.currentSelectedDAM.dalID;
		this.dalId = dalID;
		
		
		if (this.pendingIndicatorImportset == null) {
			this.pendingIndicatorImportset = new OBPendingIndicator($('importset'));
		}
		
		var source = document.getElementsByName("source");
		var sourcearray = new Array();
		var tag_array = new Array();
		//XML structure for the DataSource
		var dataSource = '';
		var topcontainer = "<table id=\"sumtable\"><tr><td class=\"abstand\">DAM: <b>" + dalID + "</b></td><td><ul>";
			
		for (var i = 0, n = source.length; i < n; i++) {
			//new workaround... https://bugzilla.mozilla.org/show_bug.cgi?id=143220#c41
			if (document.getElementById(source[i].id).files) {
				//ffx3 - try to have access to full path
				try {
					netscape.security.PrivilegeManager.enablePrivilege( 'UniversalFileRead' );
				}
				catch (e){
					alert('Unable to access local files due to browser security settings. ' +
							'To overcome this, follow these steps: (1) Enter "about:config" in the URL field; ' +
							'(2) Right click and select New->Boolean; (3) Enter "signed.applets.codebase_principal_support" ' +
							'(without the quotes) as a new preference name; (4) Click OK and try loading the file again.');
	    			return;
				}
			}
			if($(source[i].id).type == "text" || $(source[i].id).type == "undefined" || $(source[i].id).type == "textarea"){
				dataSource += source[i].id;
				sourcearray[i] = document.getElementById(source[i].id).value;
			} else if(document.getElementById(source[i].id).type == "checkbox"){
				sourcearray[i] = document.getElementById(source[i].id).checked;
			}
				
			if (sourcearray[i] && sourcearray[i] != '') {					
				//create XML doc
				tag_array[i] = document.getElementById("tag_" + source[i].id);
				if(typeof(sourcearray[i]) == 'string'){
					sourcearray[i] = sourcearray[i].replace(/>/g, "&gt;");
					sourcearray[i] = sourcearray[i].replace(/</g, "&lt;");
				}
				dataSource += "<" + tag_array[i].value + ">" + sourcearray[i] + "</" + tag_array[i].value + ">";
			
				//change the top-container
				var display = source[i].id;
				//.charAt(0).toUpperCase()+source[i].substr(1 ,source[i].id.value.length);
				topcontainer += "<li>" + display + "&nbsp;<b>" +sourcearray[i] + "</b></li>";
			}
		}
		topcontainer += "</ul></td><td class=\"abstand\"><a style=\"cursor: pointer;\"" +
				" onClick=\"termImportPage.getTopContainer(event, this)\">" + diLanguage.getMessage('smw_ti_edit') + "</a></td></tr></table>";


		try {
			var error_message = "<table id=\"sumtable\"><tr><td class=\"abstand\">" + 
				list.getElementsByTagName("message")[0].firstChild.nodeValue + "</td>" +
				"<td class=\"abstand\"><a style=\"cursor: pointer;\" onClick=\"termImportPage.getTopContainer(event, this)\">" + 
				diLanguage.getMessage('smw_ti_edit') + "</a></td></tr></table>";
			$('summary').style.display = "inline";
			$('summary').innerHTML = error_message;
		
			$('top-container').style.display = "none";
			$('bottom-container').style.display = "none";
			
			$('summary').style.display = "inline";
			$('summary').innerHTML = topcontainer;
		} catch (e) {
			
		}
				
		$("menue-step1").setAttribute("class", "TodoMenueStep");
		$("menue-step1").style.cursor = "pointer";
		$("menue-step1").setAttribute("onclick", 
				"termImportPage.getTopContainer(event, this)");
		$("menue-step2").setAttribute("class", "ActualMenueStep");
		
		$('top-container').style.display = "none";
		
		dataSource = "<DataSource xmlns=\"http://www.ontoprise.de/smwplus#\">" 
			+ dataSource + "</DataSource>";
		$("loading-container").style.display ="inline";

		sajax_do_call('dif_ti_connectDAM', [dalID , dataSource, '', '', '','', '', '', 0], this.getSourceCallback.bind(this, dalID));
	},
	
	/*
	 * Callback function for the source specification
	 */
	getSourceCallback: function(dalID, request) {
		$("loading-container").style.display ="none";
		if(this.pendingIndicator != null){
			this.pendingIndicatorImportset.hide();
		}
		
		var result = request.responseText;
		result = result.substr(result.indexOf('--##starttf##--') + 15, result.indexOf('--##endtf##--') - result.indexOf('--##starttf##--') - 15); 
		result = jQuery.parseJSON(result);
		
		if(result['success']) {
			$('extras').style.display = "inline";
			if(result['importSets'].length > 0){
				$('importset').style.display = "";
			} else {
				$('importset').style.display = "none";
			}
			if (Prototype.Browser.IE) {
				//innerHTML can't be used because of Bug: http://support.microsoft.com/default.aspx?scid=kb;en-us;276228
				$('importset-input-field').outerHTML = "<select name=\"importset\" id=\"importset-input-field\" size=\"1\" onchange=\"termImportPage.importSetChanged(event, this)\">" + 
					result['importSets'] + "</select>";
			} else {
				$('importset-input-field').innerHTML = result['importSets'];
			}
			
			$('extras-right').style.display = "inline";
			$('attrib').innerHTML = result['properties'];
			$('article_table').innerHTML = result['terms'];
			$('article-count').innerHTML = result['termsCount'];
			$('extras-bottom').style.display = "inline";
			
			$('extras-bottom').innerHTML = 
				"<input type=\"button\" onClick=\"termImportPage.getTopContainer(event, this)\""
				+ " value=\""+diLanguage.getMessage('smw_ti_prev-step')+"\"/>&nbsp;&nbsp;";
			
			$('extras-bottom').innerHTML += 
				"<input type=\"button\" onClick=\"termImportPage.importItNow(event, this,'" + dalID +"', true)\""
				+ " value=\""+diLanguage.getMessage('smw_ti_save')+"\"/>&nbsp;&nbsp;";
			
			$('extras-bottom').innerHTML += 
				"<input type=\"button\" onClick=\"termImportPage.importItNow(event, this,'" + dalID +"', false)\""
				+ " value=\"" +diLanguage.getMessage('smw_ti_execute') + "\"/><br/><br/>";
		
			if (Prototype.Browser.IE) {
				//innerHTML can't be used because of Bug: http://support.microsoft.com/default.aspx?scid=kb;en-us;276228
				$('policy-textarea').outerHTML = "<select id=\"policy-textarea\" name=\"policy-out\" size=\"7\" multiple></select>";
			} else {
				$('policy-textarea').innerHTML = '';
			}
			$('policy-input-field').value = '';
			$('template-input-field').value = '';
			$('categories-input-field').value = '';
			$('delimiter-input-field').value = ',';
		
			if(this.dalId != null){
				this.fillTermImportPage();
			}
		} else {
			var error_message = "<br/><br/><span id=\"sumtable\">" + 
				result['msg'] + "</span><br/><br/>"; 
			error_message += "<input type=\"button\" onClick=\"termImportPage.getTopContainer(event, this)\""
				+ " value=\""+diLanguage.getMessage('smw_ti_prev-step')+"\"/>";
			$('summary').style.display = "block";
			$('summary').innerHTML = error_message;
		
			$('top-container').style.display = "none";
			$('extras').style.display = "none";
		}
		
	},
	
	/*
	 * hides the summary div and shows (again) the select boxes for the
	 * transport layer module (TLM) and the data access module (DAM) and the source specification fields
	 */
	getTopContainer: function(e, node) {
		$('summary').style.display = "none";		
		$('top-container').style.display = "";
		$('extras').style.display = "none";
		$('extras-bottom').style.display = "none";
		
		var dalId = dalID = this.currentSelectedDAM.dalID;
		
		$("menue-step2").setAttribute("class", "TodoMenueStep");
		$("menue-step2").style.cursor = "pointer";
		$("menue-step2").setAttribute("onclick", 
					"termImportPage.getSource(event, this,\"" + dalId + "\")");
		$("menue-step1").setAttribute("class", "ActualMenueStep");
		
		this.dalId = dalId;
		
		var result = this.getImportCredentials(e, node, this.dalId, false);

		this.dataSource = escape(result.dataSource);
		this.importSet = result.importSetName;

		var inputPolicy = GeneralXMLTools.createDocumentFromString(result.inputPolicy);
		this.regex = this.implodeElements(inputPolicy.getElementsByTagName("regex"));
		this.terms = this.implodeElements(inputPolicy.getElementsByTagName("term"));
		this.properties = this.implodeElements(inputPolicy.getElementsByTagName("property"));
		this.templateName = result.template;
		this.delimiter = result.delimiter;
		this.extraCategories = result.extraCategories;
		this.conflictPolicy = result.conflictPol;
		this.termImportName = result.termImportName;
		this.updatePolicy = result.updatePolicy;
	},
	
	implodeElements : function(obj){
		var rString = "";
		var first = true;
		for(var i=0; i < obj.length; i++){
			if(obj[i].firstChild == null){
				break;
			}
			if(!first){
				rString += ",";
			}
			rString += obj[i].firstChild.nodeValue;
			first = false;
		}
		return rString;
	},
	
	importSetChanged: function(e, node) {
		var hasIn
		var dalid = this.currentSelectedDAM.dalID;

		this.refreshPreview(e, node, dalid);
	},
	
	/*
	 * adds the new entry from the policy field to the list
	 */
	getPolicy: function(e, node){
		try {
			// get the old and the new policies		
			var policy_selects = document.getElementById('policy-textarea').getElementsByTagName('option');
			var newpolicy = document.getElementById('policy-input-field').value;
			// get the type of the new policy
			var new_policy_type = document.getElementsByName('policy_type');
			for (var i = 0, n = new_policy_type.length; i < n; i++) {
				if (new_policy_type[i].checked) {
					var my_policy_type = new_policy_type[i].value;
				}
			}
			var response = '';
			var hidden_response = '';
			for (var i = 0, n = policy_selects.length; i < n; i++) {
				var policy_select = policy_selects[i];
				//could be an empty string so that firstChild.nodeValue can't exist!
				if (policy_select.firstChild) {
					var policy_type = document.getElementById("pol-type_" + policy_select.firstChild.nodeValue);
					if (policy_type.value == 'term'){
						response += "<option name='policy-select'>" + policy_select.firstChild.nodeValue + "</option>";
					}
					else {
						response += "<option name='policy-select' style=\"color:#900000\; text-decoration:underline;\">" + policy_select.firstChild.nodeValue + "</option>";
					}
					hidden_response += "<input type=\"hidden\" id=\"pol-type_" + policy_select.firstChild.nodeValue + "\" value=\"" + policy_type.value + "\"/>";
				}
			}
		}
		catch(e) {
			try {
				var error_message = "<table id=\"sumtable\"><tr><td class=\"abstand\">" + 
					list.getElementsByTagName("message")[0].firstChild.nodeValue + "</td>" +
					"<td class=\"abstand\"><a style=\"cursor: pointer;\" onClick=\"termImportPage.getTopContainer(event, this)\">" + 
					diLanguage.getMessage('smw_ti_edit') + "</a></td></tr></table>";
				$('summary').style.display = "inline";
				$('summary').innerHTML = error_message;
		
				$('top-container').style.display = "none";
				$('bottom-container').style.display = "none";				
			} catch (e) {
				
			}
			return;
		}
		if (my_policy_type == 'term'){
			response += "<option name='policy-select'>" + newpolicy + "</option>";
		}
		else {
			response += "<option name='policy-select' style=\"color:#900000; text-decoration:underline;\">" + newpolicy + "</option>";
		}
		hidden_response += "<input type=\"hidden\" id=\"pol-type_" + newpolicy + "\" value=\"" + my_policy_type + "\"/>";
		
		if (Prototype.Browser.IE) {
			//innerHTML can't be used because of Bug: http://support.microsoft.com/default.aspx?scid=kb;en-us;276228
			$('policy-textarea').outerHTML = "<select id=\"policy-textarea\" name=\"policy-out\" size=\"7\" multiple>" + 
				response + "</select>";
		}
		else {
			$('policy-textarea').innerHTML = response;
		}
		$('hidden_pol_type').innerHTML = hidden_response;
		$('policy-input-field').value = "";
		
		var dalid = this.currentSelectedDAM.dalID;
		
		this.refreshPreview(e, node,dalid);
	},
	
	/*
	 * deletes the selected policy entries from the list
	 */
	deletePolicy: function(e, node) {
		
		try {
			//this doesn't work in IE...
			//var policy_selects = document.getElementsByName('policy-select');
			//this works:
			var policy_selects = document.getElementById('policy-textarea').getElementsByTagName('option');
			var response = '';
			var hidden_response = '';
			for (var i = 0, n = policy_selects.length; i < n; i++) {
				var policy_select = policy_selects[i];
				if(policy_select.selected == false) {
					var policy_type = document.getElementById("pol-type_" + policy_select.firstChild.nodeValue);
					if (policy_type.value == 'term'){
						response += "<option name='policy-select'>" + policy_select.firstChild.nodeValue + "</option>";
					}
					else {
						response += "<option name='policy-select' style=\"color:#900000\; text-decoration:underline;\">" + policy_select.firstChild.nodeValue + "</option>";
					}
					hidden_response += "<input type=\"hidden\" id=\"pol-type_" + policy_select.firstChild.nodeValue + "\" value=\"" + policy_type.value + "\"/>";
				}
			}
		}
		catch(e){
			try {
			var error_message = "<table id=\"sumtable\"><tr><td class=\"abstand\">" + 
				list.getElementsByTagName("message")[0].firstChild.nodeValue + "</td>" +
				"<td class=\"abstand\"><a style=\"cursor: pointer;\" onClick=\"termImportPage.getTopContainer(event, this)\">" + 
				diLanguage.getMessage('smw_ti_edit') + "</a></td></tr></table>";
			$('summary').style.display = "inline";
			$('summary').innerHTML = error_message;
		
			$('top-container').style.display = "none";
			$('bottom-container').style.display = "none";				
			} catch (e) {
				
			}
			return;	
		}
		//response += "<option name='policy-select'>" + newpolicy + "</option>";
		if (Prototype.Browser.IE) {
			//innerHTML can't be used because of Bug: http://support.microsoft.com/default.aspx?scid=kb;en-us;276228
			$('policy-textarea').outerHTML = "<select id=\"policy-textarea\" name=\"policy-out\" size=\"7\" multiple>" + 
				response + "</select>";
		}
		else {
			$('policy-textarea').innerHTML = response;
		}
		$('hidden_pol_type').innerHTML = hidden_response;
		$('policy-input-field').value = "";

		var dalid = this.currentSelectedDAM.dalID;
		
		this.refreshPreview(e, node, dalid);
	},
	
	/*
	 * Refresh Button of properties table or article preview is clicked so, refresh them...
	 */
	refreshPreview: function(e, node, dalID) {
		if (this.pendingIndicatorArticles == null) {
			this.pendingIndicatorArticles = new OBPendingIndicator($('article_table'));
		}
		this.pendingIndicatorArticles.show();
		
		var result = this.getImportCredentials(e, node, dalID, false);
		
		sajax_do_call('dif_ti_connectDAM', [dalID , result.dataSource, result.importSetName, 
			result.inputPolicy, result.template, result.extraCategories, result.delimiter, result.conflictPol, 0], 
		    this.refreshPreviewCallback.bind(this, dalID));
	},
	
	refreshPreviewCallback: function(dalID, request){
		//refresh the article preview!!!
		this.pendingIndicatorArticles.hide();
	
		var result = request.responseText;
		result = result.substr(result.indexOf('--##starttf##--') + 15, result.indexOf('--##endtf##--') - result.indexOf('--##starttf##--') - 15); 
		result = jQuery.parseJSON(result);
		
		if(result['success']) {
			$('article_table').innerHTML = result['terms'];
			$('article-count').innerHTML = result['termsCount'];
		} else {
			//updating preview failed somehow
			
			var error_message = "<br/><br/><span id=\"sumtable\">" + 
				result['msg'] + "</span><br/><br/>"; 
			error_message += "<input type=\"button\" onClick=\"termImportPage.getTopContainer(event, this)\""
				+ " value=\""+diLanguage.getMessage('smw_ti_prev-step')+"\"/>";
			$('summary').style.display = "block";
			$('summary').innerHTML = error_message;
		
			$('top-container').style.display = "none";
			$('extras').style.display = "none";
		}
		
		
	},
	
	/*
	 * Do the import!
	 */
	importItNow: function(e, node, dalID, createOnly){
		var result = termImportPage.getImportCredentials(e, node, dalID, true);
		
		if(result == null){
			return;
		} else {
			$("extras-bottom").style.display = "none";
			$("loading-bottom-container").style.display = "inline";
			
			sajax_do_call('dif_ti_connectDAM', [dalID , result.dataSource, result.importSetName, 
			                                    result.inputPolicy, result.template, result.extraCategories, result.delimiter, result.conflictPol, 1, result.termImportName, result.updatePolicy, this.editTermImport, createOnly]
			                                    , this.importItNowCallback.bind(this, dalID, createOnly));
		}
	},
	
	getImportCredentials: function(e, node, dalID, commit){
		var result = new Object();
		
		//DataSource
		var source = document.getElementsByName("source");
		var sourcearray = new Array();
		var tag_array = new Array();
		var dataSource = '';
		for (var i = 0, n = source.length; i < n; i++) {
			//new workaround... https://bugzilla.mozilla.org/show_bug.cgi?id=143220#c41
			if (document.getElementById(source[i].id).files) {
				//ffx3 - try to have access to full path
				try {
					netscape.security.PrivilegeManager.enablePrivilege( 'UniversalFileRead' );
				}
				catch (e){
					alert('Unable to access local files due to browser security settings. ' +
							'To overcome this, follow these steps: (1) Enter "about:config" in the URL field; ' +
							'(2) Right click and select New->Boolean; (3) Enter "signed.applets.codebase_principal_support" ' +
							'(without the quotes) as a new preference name; (4) Click OK and try loading the file again.');
	    			return;
				}
			}
			sourcearray[i] = document.getElementById(source[i].id).value;
			if (sourcearray[i] && sourcearray[i] != '') {
				tag_array[i] = document.getElementById("tag_" + source[i].id).value;
				if(typeof(sourcearray[i]) == 'string'){
					sourcearray[i] = sourcearray[i].replace(/>/g, "&gt;");
					sourcearray[i] = sourcearray[i].replace(/</g, "&lt;");
				}
				dataSource += "<" + tag_array[i] + ">" + sourcearray[i] + "</" + tag_array[i] + ">";
			}
		}
		result.dataSource = "<DataSource xmlns=\"http://www.ontoprise.de/smwplus#\">" + dataSource + "</DataSource>";
			
		//gets the selected import set 
		result.importSetName = document.getElementById('importset-input-field').value;
			
		//input policy
		var policy_selects = document.getElementById('policy-textarea').getElementsByTagName('option');	
		if (policy_selects.length > 0) {
			var inputPolicy = '<?xml version="1.0"?>'+"\n"+
				'<InputPolicy xmlns="http://www.ontoprise.de/smwplus#">'+"\n"+
   					'<terms>'+"\n";
   			//get the terms...
			for(var i = 0, n = policy_selects.length; i < n; i++) {
   					var policy_type = document.getElementById("pol-type_" + policy_selects[i].firstChild.nodeValue);
   					inputPolicy += '<' + policy_type.value + '>' + 
    							policy_selects[i].firstChild.nodeValue + 
    							'</' + policy_type.value + '>'+"\n";
    		}
    		inputPolicy +='</terms>'+"\n"+
   	 			'<properties>'+"\n";
		}else{
			var inputPolicy = '<?xml version="1.0"?>'+"\n"+
				'<InputPolicy xmlns="http://www.ontoprise.de/smwplus#">'+"\n"+
   		 		'<terms>'+"\n"+
   		 		 '	<regex></regex>'+"\n"+
   		 		'	<term></term>'+"\n"+
   		 		'</terms>'+"\n"+
   	 			'<properties>'+"\n";
		}
		//and now the properties...
		if(document.getElementById('attrib_table')){
			var properties = document.getElementById('attrib_table').getElementsByTagName('td');
			for (var i = 0, n = properties.length; i < n; i++) {
				// get one of the importsets
				var property = properties[i]; 
				if(property.nodeType == 1) {
					if ( property.firstChild.nodeValue ){
						var checkboxes = document.getElementsByName('checked_properties');
						for (var j = 0, m = checkboxes.length; j < m; j++) {
							if(property.firstChild.nodeValue == checkboxes[j].value){
								if(checkboxes[j].checked){
									inputPolicy += '	<property>'+ property.firstChild.nodeValue + '</property>'+"\n";
									break;
								}
							}	
						}						
					}	
				}	
			}
		}
   	 	inputPolicy += '</properties>'+"\n"+
			'</InputPolicy>'+"\n";
		result.inputPolicy = inputPolicy;
   	 	
		//template name
		if($('creationpattern-checkbox').checked){
			result.template = document.getElementById('template-input-field').value;
		} else {
			result.template = '';
		}
		
		//extra category annotations
		result.extraCategories = document.getElementById('categories-input-field').value;
			
		//delimiter
		result.delimiter = document.getElementById('delimiter-input-field').value;
			
		//conflict policy
		//var conflict = document.getElementById('conflict-input-field').options[document.getElementById('conflict-input-field').selectedIndex].text;
		var optionIndex = document.getElementById('conflict-input-field').selectedIndex; 
		if(optionIndex == -1){
			optionIndex = 0;
		}
		var conflict = document.getElementById('conflict-input-field').childNodes[optionIndex].firstChild.nodeValue;
		if( conflict == 'overwrite') {
			var conflictPol = true;
		} else {
			var conflictPol = false;
		}
		result.conflictPol = conflictPol;
		
		//term import name
		result.termImportName = document.getElementById('ti-name-input-field').value;

		if(result.termImportName == '' && commit){
			//do not import without a term import name!
			$('ti-name-input-field').style.backgroundColor = "red";
			return ;
		}
			
		var updatePolicy = 0;
		if($('update-policy-checkbox').checked){
			if($("ti-update-policy-input-field").value != ""){
				if(parseInt($("ti-update-policy-input-field").value)
						!= $("ti-update-policy-input-field").value-0 && commit){
					$("ti-update-policy-input-field").style.backgroundColor = "red";
					return;
				}
			}
			updatePolicy = $("ti-update-policy-input-field").value;
		}
		result.updatePolicy = updatePolicy;
		
		return result;
	},
	
	importItNowCallback: function(dalID, createOnly, request){
		$("extras-bottom").style.display = "inline";
		$("loading-bottom-container").style.display = "none";
		
		var result = request.responseText;
		result = result.substr(result.indexOf('--##starttf##--') + 15, result.indexOf('--##endtf##--') - result.indexOf('--##starttf##--') - 15); 
		result = jQuery.parseJSON(result);
		
		if(result['success']) {
			var message = '<br><b>' + result['msg'] + '</b><br/>';
			$('extras-bottom').innerHTML = message;
		} else {
			$('ti-name-input-field').style.backgroundColor = "red";
			alert(result['msg']);
		}
	},
	
	changeBackground: function(e, node) {
		node.style.backgroundColor = "white";
	},
	
	editTermImportDefinition : function(){
		var editDataSpan = $('editDataSpan');
		
		if(editDataSpan == null){
			this.editTermImport = false;
			return;
		}
		this.editTermImport = true;
		
		this.dalId = $('dalId-ed').firstChild.nodeValue;
		this.dataSource = unescape($('dataSource-ed').firstChild.nodeValue);
		if($('importSet-ed').firstChild != null){
			this.importSet = $('importSet-ed').firstChild.nodeValue;
		} else {
			this.importSet = "ALL";
		}
		this.regex = "";
		if($('regex-ed').firstChild != null){
			this.regex = $('regex-ed').firstChild.nodeValue;
		}
		this.terms = "";
		if($('terms-ed').firstChild != null){
			this.terms = $('terms-ed').firstChild.nodeValue;
		}
		this.properties = $('properties-ed').firstChild.nodeValue;
		
		this.conflictPolicy = $('conflictPolicy-ed').firstChild.nodeValue;
		this.termImportName = $('termImportName-ed').firstChild.nodeValue;
		this.updatePolicy = $('updatePolicy-ed').firstChild.nodeValue;

		if($('templateName-ed').firstChild != null){
			this.templateName = $('templateName-ed').firstChild.nodeValue;
		}
		this.delimiter = $('delimiter-ed').firstChild.nodeValue;
		if($('extraCategories-ed').firstChild != null){
			this.extraCategories = $('extraCategories-ed').firstChild.nodeValue;
		}
		
		if($(this.dalId)){
			Element.addClassName($(this.dalId),'entry-active');
			this.currentSelectedDAM = $(this.dalId).cloneNode(true);
			this.currentSelectedDAM.dalID = this.dalId;
			$('daldesc').innerHTML = "<b>Info: </b>" + $('dal-desc').firstChild.nodeValue;
		} else {
			alert("The Data Access Module " + this.dalId + " is not available.");
			return;
		}
				
		//data source
		dataSource = GeneralXMLTools.createDocumentFromString(this.dataSource);
		dataSource = dataSource.getElementsByTagName("DataSource")[0].childNodes;
		this.createDataSourceWidget(dataSource, this.dalId);
		
		this.getSource(null, null, this.dalId);
	},
	
	fillTermImportPage : function(){
		//select import set
		$('importset-input-field').value = this.importSet;
		
		//add import policies
		if(this.regex){
			if (this.regex.length > 0) {
				regex = this.regex.split(",");
				for ( var i = 0; i < regex.length; i++) {
					var option = document.createElement("option");
					option.setAttribute("style",
						"color: rgb(144, 0, 0); text-decoration: underline;");
					option.setAttribute("name", "policy-select");
					option.appendChild(document.createTextNode(regex[i]));
					$('policy-textarea').appendChild(option);

					var input = document.createElement('input');
					input.setAttribute("id", "pol-type_" + regex[i]);
					input.setAttribute("type", 'hidden');
					input.setAttribute("value", "regex");
					$('hidden_pol_type').appendChild(input);
				}
			}
		}
		
		if(this.terms){
			if (this.terms.length > 0) {
				terms = this.terms.split(",");
				for ( var i = 0; i < terms.length; i++) {
					var option = document.createElement("option");
					option.setAttribute("style",
					"color: rgb(144, 0, 0); text-decoration: underline;");
					option.setAttribute("name", "policy-select");
					option.appendChild(document.createTextNode(terms[i]));
					$('policy-textarea').appendChild(option);

					var input = document.createElement('input');
					input.setAttribute("id", "pol-type_" + terms[i]);
					input.setAttribute("type", 'hidden');
					input.setAttribute("value", "term");
					$('hidden_pol_type').appendChild(input);
				}
			}
		}
		
		//properties
		if(this.properties){
			properties = this.properties.split(",");
		} else {
			properties = "";
		}
		for(var i=0; i < $('attrib_table').firstChild.childNodes.length; i++){
			var value = $('attrib_table').firstChild.childNodes[i].firstChild.firstChild.value;
			for(var k=0; k < properties.length; k++){
				if(value == properties[k]){
					properties.splice(k, 1);
					$('attrib_table').firstChild.childNodes[i].firstChild.firstChild.checked = true;
				}
			}
		}
		
		if(properties.length > 0){
			var message = "The attributes ";
			for(var k=0; k < properties.length; k++){
				if(k > 0){
					message += ", ";
				}
				message += properties[k];
			}
			message += " are not available.";
			alert(message);
		}
		
		//creationpattern
		if(this.templateName){
			$('template-input-field').value = this.templateName;
			
			if(this.templateName.length > 0){
				$('creationpattern-checkbox').checked = true;
				$('delimiter').style.display = "";
			} else {
				$('delimiter').style.display = "none";
			}
		}
		if(this.delimiter){
			$('delimiter-input-field').value = this.delimiter;
		}
		if(this.extraCategories){
			$('categories-input-field').value = this.extraCategories;
		}
		
		if(this.conflictPolicy){
			$('conflict-input-field').value = this.conflictPolicy;
		} 
		if(this.termImportName){
			$('ti-name-input-field').value = this.termImportName;
		} else {
			$('ti-name-input-field').value = "";
		}
			
		if(this.updatePolicy){	
			if(this.updatePolicy != '0' && this.updatePolicy != ''){
				$('update-policy-checkbox').checked = true;
				$("ti-update-policy-input-field").value = this.updatePolicy;
			}
		}
		
		var dalId = this.currentSelectedDAM.dalID;
		this.refreshPreview(null, null, this.dalId);
	},
	
	displayHelp : function(id) {
		$("help" + id).style.display = "";
		$("help-img" + id).getAttributeNode("onclick").nodeValue = "termImportPage.hideHelp("
				+ id + ")";
	},

	hideHelp : function(id) {
		$("help" + id).style.display = "none";
		$("help-img" + id).getAttributeNode("onclick").nodeValue = "termImportPage.displayHelp("
				+ id + ")";
	},
	
	showOrHideDelimiterInput : function(event){
		if(Event.element(event).value == "template"){
			$('delimiter').style.display = "";
		} else {
			$('delimiter').style.display = "none";
		}
	}
}


var termImportPage = new TermImportPage();
window.termImportPage = termImportPage;

Event.observe(window, 'load', termImportPage.editTermImportDefinition
	.bindAsEventListener(termImportPage));
