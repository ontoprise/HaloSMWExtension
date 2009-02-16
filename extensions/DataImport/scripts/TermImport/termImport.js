/*  Copyright 2008-2009, ontoprise GmbH
*   Author: Benjamin Langguth
*   This file is part of the Data Import-Extension.
*
*   The Data Import-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Data Import-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

var TermImportPage = Class.create();

TermImportPage.prototype = {
	initialize: function() {
		this.currentSelectedTLM = null;
		this.currentSelectedDAM = null;
		/*if (wgCanonicalSpecialPageName != 'Gardening') return;*/
	},
	
	/**
	 * Formats the selected TLM entry correctly when mouseout
	 */
	showRightTLM: function(e, node, tlID){
		if (this.currentSelectedTLM!=node) {
			Element.removeClassName(node,'entry-over');
			Element.addClassName(node,'entry');
		}else{
			Element.removeClassName(node,'entry-over');
			Element.addClassName(node,'entry-active');
		}
	},
	
	/**
	 * Request the chosen TL module and paste TL description and DAL IDs
	 * in the tl-desc respectively dal-id
	 */
	connectTL: function(e, node, tlID) {
		if (this.currentSelectedTLM) {
			Element.removeClassName(this.currentSelectedTLM,'entry-active');
			Element.addClassName(this.currentSelectedTLM,'entry');
		}
		Element.removeClassName(node, 'entry');
		Element.addClassName(node, 'entry-active');
		this.currentSelectedTLM = node;		
		if (this.pendingIndicatorTL == null && this.pendingIndicatorDAL == null) {
			this.pendingIndicatorTL = new OBPendingIndicator($('tldesc'));
			this.pendingIndicatorDAL = new OBPendingIndicator($('dalid'));
		}
		this.pendingIndicatorTL.show();
		this.pendingIndicatorDAL.show();
		sajax_do_call('smwf_ti_connectTL', [tlID, '', '', '', '', '', '', 0], this.connectTLCallback.bind(this, tlID));
	},
	
	/*
	 * Callback function for connectTL
	 */
	connectTLCallback: function(tlID, request) {
		this.pendingIndicatorTL.hide();
		this.pendingIndicatorDAL.hide();
		
		//DOM object and XML parsing...
		var result = request.responseText;
		var list = GeneralXMLTools.createDocumentFromString(request.responseText);
		
		//get all TLModules from the list
		var tlmodules = list.getElementsByTagName("TLModules")[0].childNodes;
		var response = '';
		for (var i = 0, n = tlmodules.length; i < n; i++) {
			//get on of the tlmodules
			var tlmodule = tlmodules[i]; 
			if(tlmodule.nodeType == 1) {
				//find the id of the tlmodule
				var found_tl_id = tlmodule.getElementsByTagName('id');
				//var tl_class = tlmodule.getElementsByTagName('class');
				//var tl_file = tlmodule.getElementsByTagName('file');
				//find the desc
				var tl_desc = tlmodule.getElementsByTagName('desc');
				//check if found ID matches the given one
				if (found_tl_id && found_tl_id[0].firstChild.nodeValue == tlID){
					// yes, add the description to the response var.
					response += "Info: "+tl_desc[0].firstChild.nodeValue;
				}	
			}	
		}
		if ( response ) {
			$('tldesc').innerHTML = response;
		}
		
     	// get all DALModules from the list 
		var dalmodules = list.getElementsByTagName("DALModules")[0].childNodes;
		// reset response var.
		response = '';
		
		for (var i = 0, n = dalmodules.length; i < n; i++) {
			//get one of the dalmodules
			var dalmodule = dalmodules[i];
			if(dalmodule.nodeType == 1) {
				var dalid_obj = dalmodule.getElementsByTagName("id");
				if (dalid_obj) {
					//get the nodeValue
					var dalid = dalid_obj[0].firstChild.nodeValue;
					response += "<div class=\"entry\" onMouseOver=\"this.className='entry-over';\"" +
		 				 "onMouseOut=\"termImportPage.showRightDAM(event, this, '$tlid')\" onClick=\"termImportPage.getDAL(event, this, '" + dalid + "', '" + tlID + "')\"><a>" + dalid + "</a></div>";
				}	
			}	
		}
		if ( response ) {
			$('dalid').innerHTML = response;			
		}
	},
	
	/**
	 * Formats the selected DAM entry correctly when mouseout
	 */
	showRightDAM: function(e, node, tlID){
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
	getDAL: function(e, node, dalID, tlID) {
		if (this.currentSelectedDAM) {
			Element.removeClassName(this.currentSelectedDAM,'entry-active');
			Element.addClassName(this.currentSelectedDAM,'entry');
		}
		Element.removeClassName(node,'entry');
		Element.addClassName(node, 'entry-active');
		this.currentSelectedDAM = node;
		if (this.pendingIndicatorDALDesc == null && this.pendingIndicatorSourceSpec == null) {
			this.pendingIndicatorDALDesc = new OBPendingIndicator($('daldesc'));
			this.pendingIndicatorSourceSpec = new OBPendingIndicator($('source-spec'));
		}
		this.pendingIndicatorDALDesc.show();
		this.pendingIndicatorSourceSpec.show();
		sajax_do_call('smwf_ti_connectTL', [tlID, dalID , '', '', '', '', '', 0], this.getDALCallback.bind(this, tlID, dalID));
	},
	
	/*
	 *  Callback function for getting all DAMs for the chosen TLM
	 */
	getDALCallback: function(tlID, dalID, request){
		this.pendingIndicatorDALDesc.hide();
		this.pendingIndicatorSourceSpec.hide();
		
		//DOM object and XML parsing...
		var result = request.responseText;
		var list = GeneralXMLTools.createDocumentFromString(result);
		
		//get all DALModules from the list
		var dalmodules = list.getElementsByTagName("DALModules")[0].childNodes;
		var response = '';
		//get id and desc of every dalmodule and compare to the given one
		for (var i = 0, n = dalmodules.length; i < n; i++) {
			// get one of the dalmodules (shortcut)
			var dalmodule = dalmodules[i]; 
			if(dalmodule.nodeType == 1) {
				//find the id Obj of the dalmodule
				var dalid_obj = dalmodule.getElementsByTagName('id');
				//var dal_class = tlmodule.getElementsByTagName('class');
				//var dal_file = tlmodule.getElementsByTagName('file');
				//find the desc
				var dal_desc = dalmodule.getElementsByTagName('desc');
				//check if found ID matches the given one
				if ( dalid_obj && dalid_obj[0].firstChild.nodeValue == dalID){
					// yes, add the description to the response var.
					response += "Info: " + dal_desc[0].firstChild.nodeValue;
				}	
			}		
		}
		if ( response ) {
			$('daldesc').innerHTML = response;
		}
		
		//create the right input-div
		var datasources = list.getElementsByTagName("DataSource")[0].childNodes;
		response = "<i>" + diLanguage.getMessage('smw_ti_sourceinfo') + "</i><br><br><form id=\"source\"><Table>" +
					diLanguage.getMessage('smw_ti_source') + "&nbsp;";
		
		var fieldnumber = 0;
		for (var i = 0, n = datasources.length; i < n; i++) {
			// get one of the datasources
			var datasource = datasources[i]; 
			if(datasource.nodeType == 1) {
				//
				//if ( datasource.hasAttribute ) {
					
					//TagName bekommen
					var tag = datasource.tagName;
				
					if ( datasource.getAttribute('display') ){
						var attrib_display = datasource.getAttribute('display');
					}
					if ( datasource.getAttribute('type') ) {
						var attrib_type = datasource.getAttribute('type');
					}
					if ( attrib_display ) {
						//check type
						if ( attrib_type == "file" ) {
							response += "<tr><td>" + attrib_display + "</td><td><input name=\"source\" id=\"" + 
										attrib_display + "\" class=\"inputfield\" type=\"file\" size=\"25\" maxlength=\"100\" value=\"" + 
										datasource.textContent + "\">" + "</td></tr>";
						}
						else {
							response += "<tr><td>" + attrib_display + "</td><td><input name=\"source\" id=\"" + 
							attrib_display+"\" class=\"inputfield\" type=\"text\" size=\"25\" maxlength=\"100\" value=\"" + datasource.textContent + "\"></td></tr>";
						}
						response += "<input type=\"hidden\" id=\"tag_"+ attrib_display +"\" value=\""+tag+"\"/>";						
					}					
				//}	
			}		
		}
		response += "</table><br><button id=\"submitSource\" type=\"button\" name=\"run\" onclick=\"termImportPage.getSource(event, this,'" +tlID+ "','" + dalID +"')\">Submit</button></form>";
		//fade in the source specification
		$('source-spec').innerHTML = response;
	},
	
	getSource: function(e, node, tlID, dalID) {
		if (this.pendingIndicatorImportset == null) {
			this.pendingIndicatorImportset = new OBPendingIndicator($('importset'));
		}
		this.pendingIndicatorImportset.show();
		
		try {
			var source = document.getElementsByName("source");
			var sourcearray = new Array();
			var tag_array = new Array();
			//XML structure for the DataSource
			var dataSource = '';
			var topcontainer = "<table id=\"sumtable\"><tr><td class=\"abstand\">TLM: <b>" + tlID + "</b></td><td class=\"abstand\">DAM: <b>" + dalID + "</b></td><td><ul>";
			
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
					//create XML doc
					tag_array[i] = document.getElementById("tag_" + source[i].id);
					
					dataSource += "<" + tag_array[i].value + ">" + sourcearray[i] + "</" + tag_array[i].value + ">";
			
					//change the top-container
					var display = source[i].id;
					//.charAt(0).toUpperCase()+source[i].substr(1 ,source[i].id.value.length);
					topcontainer += "<li>" + display + "&nbsp;<b>" +sourcearray[i] + "</b></li>";
				}
			}
			topcontainer += "</ul></td><td class=\"abstand\"><a style=\"cursor: pointer;\"" +
					" onClick=\"termImportPage.getTopContainer(event, this)\">" + diLanguage.getMessage('smw_ti_edit') + "</a></td></tr></table>";
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
				// TODO: handle exception
			}
		}
		
		$('summary').style.display = "inline";
		$('summary').innerHTML = topcontainer;
		
		$('top-container').style.display = "none";
				
		dataSource = "<DataSource xmlns=\"http://www.ontoprise.de/smwplus#\">" + dataSource + "</DataSource>";
		
		sajax_do_call('smwf_ti_connectTL', [tlID, dalID , dataSource, '', '', '', '', 0], this.getSourceCallback.bind(this, tlID, dalID));
	},
	
	/*
	 * Callback function for the source specification
	 */
	getSourceCallback: function(tlID, dalID, request) {
		
		this.pendingIndicatorImportset.hide();
		
		var result = request.responseText;
		var list = GeneralXMLTools.createDocumentFromString(result);
		
		try {
			//why is ImportSet in Uppercases???
			var importsets = list.getElementsByTagName("IMPORTSETS")[0].childNodes;
			var import_response="<option value='ALL' selected>ALL</option>";
			for (var i = 0, n = importsets.length; i < n; i++) {
				// get one of the importsets
				var importset = importsets[i]; 
				if(importset.nodeType == 1) {
					//find the name Obj of the 
					var import_name_obj = importset.getElementsByTagName('NAME');
					if ( import_name_obj ){
						var import_name= import_name_obj[0].firstChild.nodeValue;
						// add importset item to the list
						import_response += "<option value='" + import_name + "'>" + import_name + "</option>";
					}	
				}	
			}
			//show properties on the right side
			var properties = list.getElementsByTagName("Properties")[0].childNodes;
			var property_response = diLanguage.getMessage('smw_ti_attributes');
												
			property_response += '<div class=\"scrolling\"><table id=\"attrib_table\" class=\'mytable\'>';
			
			for (var i = 0, n = properties.length; i < n; i++) {
				// get one of the importsets
				var property = properties[i]; 
				if(property.nodeType == 1) {
					//find the name Obj of the 
					var property_name_obj = property.getElementsByTagName('name');
					if ( property_name_obj[0].firstChild ){
						if( property_name_obj[0].firstChild.nodeValue != '') {
							var property_name = property_name_obj[0].firstChild.nodeValue;
							// add importset item to the list
							if (property_name == diLanguage.getMessage('smw_ti_noa')){
								property_response += "<tr><td class=\"mytd\" style=\"width:10px\"><input type=\"checkbox\" name=\"checked_properties\" value=\""+
									property_name + "\" disabled checked></td><td class=\"mytd\">"+ property_name + "</td></tr>";
							}
							else {
								property_response += "<tr><td class=\"mytd\" style=\"width:10px\"><input type=\"checkbox\" name=\"checked_properties\" value=\""+
									property_name + "\"></td><td class=\"mytd\">" + property_name + "</td></tr>";
							}
						}
					}	
				}	
			}
			property_response += "</table></div>";
		
			var terms = list.getElementsByTagName("terms")[0].childNodes;
			var article_response = '<div id=\"article_table\" class=\"scrolling\"><table class=\'mytable\'>';
			var article_count = 0;
			for (var i = 0, n = terms.length; i < n; i++) {
			// get one of the importsets
				var term = terms[i]; 
				if(term.nodeType == 1) {
					//find the name Obj of the 
					if ( term.firstChild ){
						var article_name = term.firstChild.nodeValue;
						// add article name to the table
						article_response += "<tr><td class=\"mytd\">" + article_name + "</td></tr>";
						article_count++;
					}
				}
			}
			article_response = "<div id=\"article_intro\"><table><tr><td>" + diLanguage.getMessage('smw_ti_articles1') + 
					article_count + diLanguage.getMessage('smw_ti_articles2') + "</td></tr></table></div>" + 
					article_response + "</table></div>";
		}
		catch(e){
			//doesn't work in IE,so put it in a try-block
			try {
				var test = list.getElementsByTagName("message");
				var error_message = "<table id=\"sumtable\"><tr><td class=\"abstand\">" + 
					list.getElementsByTagName("message")[0].firstChild.nodeValue + "</td>" +
					"<td class=\"abstand\"><a style=\"cursor: pointer;\" onClick=\"termImportPage.getTopContainer(event, this)\">" + 
					diLanguage.getMessage('smw_ti_edit') + "</a></td></tr></table>";
				$('summary').style.display = "inline";
				$('summary').innerHTML = error_message;
			
				$('top-container').style.display = "none";
				$('extras').style.display = "none";				
			} catch (e) {
				// TODO: handle exception
			}
			return;
		}
		if (import_response) {
			$('extras').style.display = "inline";
			if (Prototype.Browser.IE) {
				//innerHTML can't be used because of Bug: http://support.microsoft.com/default.aspx?scid=kb;en-us;276228
				$('importset-input-field').outerHTML = "<select name=\"importset\" id=\"importset-input-field\" size=\"1\" onchange=\"termImportPage.importSetChanged(event, this)\">" + 
					import_response + "</select>";
			}
			else {
				$('importset-input-field').innerHTML = import_response;
			}
		}		
		if (property_response) {
			$('extras-right').style.display = "inline";
			$('attrib').innerHTML = property_response;
			$('articles').innerHTML = article_response;
			$('extras-bottom').style.display = "inline";
			$('extras-bottom').innerHTML = "<a onClick=\"termImportPage.importItNow(event, this,'" +tlID+ "','" + dalID +"')\"><b><br>Click to start import</b>" + 
				"<img src=\""+wgScriptPath+"/extensions/DataImport/skins/TermImport/images/Accept.png\"></a>";
		}
		if (Prototype.Browser.IE) {
			//innerHTML can't be used because of Bug: http://support.microsoft.com/default.aspx?scid=kb;en-us;276228
			$('policy-textarea').outerHTML = "<select id=\"policy-textarea\" name=\"policy-out\" size=\"7\" multiple></select>";
		}
		else {
			$('policy-textarea').innerHTML = '';
		}
		$('policy-input-field').value = '';
		$('mapping-input-field').value = '';
		
	},
	
	/*
	 * hides the summary div and shows (again) the select boxes for the
	 * transport layer module (TLM) and the data access module (DAM) and the source specification fields
	 */
	getTopContainer: function(e, node) {
		$('summary').style.display = "none";		
		$('top-container').style.display = "inline";
		$('extras').style.display = "none";
		$('extras-bottom').style.display = "none";
	},
	
	importSetChanged: function(e, node) {
		var hasInnerText =
		(this.currentSelectedTLM.innerText != undefined) ? true : false;
			var elem = this.currentSelectedTLM;
			var elem2 = this.currentSelectedDAM;

		if(!hasInnerText){
    		var tlid = elem.textContent;
    		var dalid = elem2.textContent;
		} else{
    		var tlid = elem.innerText;
    		var dalid = elem2.innerText;
		}
		this.refreshPreview(e, node, tlid, dalid);
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
				// TODO: handle exception
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
		
		var hasInnerText =
		(this.currentSelectedTLM.innerText != undefined) ? true : false;
			var elem = this.currentSelectedTLM;
			var elem2 = this.currentSelectedDAM;

		if(!hasInnerText){
    		var tlid = elem.textContent;
    		var dalid = elem2.textContent;
		} else{
    		var tlid = elem.innerText;
    		var dalid = elem2.innerText;
		}
		this.refreshPreview(e, node,tlid,dalid);
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
				// TODO: handle exception
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
		var hasInnerText =
		(this.currentSelectedTLM.innerText != undefined) ? true : false;
			var elem = this.currentSelectedTLM;
			var elem2 = this.currentSelectedDAM;

		if(!hasInnerText){
    		var tlid = elem.textContent;
    		var dalid = elem2.textContent;
		} else{
    		var tlid = elem.innerText;
    		var dalid = elem2.innerText;
		}
		this.refreshPreview(e, node, tlid, dalid);
	},
	
	/*
	 * redirects to the entered mapping article
	 */
	viewMappingArticle: function(e, node) {
		var mappingPage = document.getElementById('mapping-input-field').value;
		var path = wgArticlePath.replace(/\$1/, mappingPage);
		window.open(wgServer + path, "");
	},
	
	/*
	 * redirects to the edit page of the entered article
	 */
	editMappingArticle: function(e,node) {
		var mappingPage = document.getElementById('mapping-input-field').value;
		queryStr = "?action=edit";
		var path = wgArticlePath.replace(/\$1/, mappingPage);
		window.open(wgServer + path + queryStr, "");
	},

	/*
	 * Refresh Button of properties table or article preview is clicked so, refresh them...
	 */
	refreshPreview: function(e, node, tlID, dalID) {
		
		if (this.pendingIndicatorArticles == null) {
			this.pendingIndicatorArticles = new OBPendingIndicator($('article_table'));
		}
		this.pendingIndicatorArticles.show();
		
		//DataSource
		try {
			var source = document.getElementsByName("source");
			var sourcearray = new Array();
			var tag_array = new Array();
			//XML structure for the DataSource
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
					
					dataSource += "<" + tag_array[i] + ">" + sourcearray[i] + "</" + tag_array[i] + ">";
				}
			}
		
			dataSource = "<DataSource xmlns=\"http://www.ontoprise.de/smwplus#\">" + dataSource + "</DataSource>";
		
			//gets the selected import set 
			var importSetName = document.getElementById('importset-input-field').value;
		
			//input policy
			//this doesn't work in IE...
			//var policy_selects = document.getElementsByName('policy-select');
			//this works:	
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
   	 		inputPolicy += '</properties>'+"\n"+
				'</InputPolicy>'+"\n";
			//mapping policy
			var mappingPage = document.getElementById('mapping-input-field').value;
		
			//conflict policy
			if(document.getElementById('conflict-input-field').value == 'overwrite') {
				var conflictPol = true;
			}
			else {
				var conflictPol = false;
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
				// TODO: handle exception
			}
			return;
		}
			sajax_do_call('smwf_ti_connectTL', [tlID, dalID , dataSource, importSetName, inputPolicy, mappingPage, conflictPol, 0], this.refreshPreviewCallback.bind(this, tlID, dalID));
	},
	
	refreshPreviewCallback: function(tlID, dalID, request){
		
		//refresh the article preview!!!
		this.pendingIndicatorArticles.hide();
		
		var result = request.responseText;
		var list = GeneralXMLTools.createDocumentFromString(result);
		
				
		try {
			var terms = list.getElementsByTagName("terms")[0].childNodes;
			//var article_response = '<table id=\"article_table\" class=\'mytable\'>';
			var article_response = '<table class=\"mytable\">';
			var article_intro = '';
			var article_count = 0;
			for (var i = 0, n = terms.length; i < n; i++) {
			// get one of the importsets
				var term = terms[i]; 
				if(term.nodeType == 1) {
					//find the name Obj of the 
					if ( term.firstChild ){
						var article_name = term.firstChild.nodeValue;
						if ( article_name ){
							// add article name to the table
							article_response += "<tr><td class=\"mytd\">" + article_name + "</td></tr>";
							article_count++;
						}
					}	
				}	
			}
			article_response += '</table>';
			article_intro = "<table><tr><td>" + diLanguage.getMessage('smw_ti_articles1') + 
					article_count + diLanguage.getMessage('smw_ti_articles2') + "</td></tr></table>";			
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
				// TODO: handle exception
			}
			return;
		}		
		$('article_intro').innerHTML = article_intro;
		$('article_table').innerHTML = article_response;
	},
	
	/*
	 * Do the import!
	 */
	importItNow: function(e, node, tlID, dalID){
				
		//DataSource
		try {
			var source = document.getElementsByName("source");
			var sourcearray = new Array();
			var tag_array = new Array();
			//XML structure for the DataSource
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
					
					dataSource += "<" + tag_array[i] + ">" + sourcearray[i] + "</" + tag_array[i] + ">";
				}
			}
			dataSource = "<DataSource xmlns=\"http://www.ontoprise.de/smwplus#\">" + dataSource + "</DataSource>";
			
			//gets the selected import set 
			var importSetName = document.getElementById('importset-input-field').value;
			
			//input policy
			//this doesn't work in IE...
			//var policy_selects = document.getElementsByName('policy-select');
			//this works:
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
   	 		inputPolicy += '</properties>'+"\n"+
				'</InputPolicy>'+"\n";
			//mapping policy
			var mappingPage = document.getElementById('mapping-input-field').value;
			if(mappingPage == ''){
				//do not import without a mapping page!
				$('mapping-input-field').style.backgroundColor = "red";
				return;
			}
			var re = /\w+/g;
			if(mappingPage.length > 0){
   				// min. one other char than a whitespace
   				if(re.test(mappingPage) != true) {
   					$('mapping-input-field').style.backgroundColor = "red";
   					return;	
   				}
			} 
			//conflict policy
			var conflict = document.getElementById('conflict-input-field').options[document.getElementById('conflict-input-field').selectedIndex].text;
			if( conflict == 'overwrite') {
				var conflictPol = true;
			}
			else {
				var conflictPol = false;
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
				// TODO: handle exception
			}
			return;
		}
		sajax_do_call('smwf_ti_connectTL', [tlID, dalID , dataSource, importSetName, inputPolicy, mappingPage, conflictPol, 1], this.importItNowCallback.bind(this, tlID, dalID));				
	},
	importItNowCallback: function(tlID, dalID, request){
		var message= '';
		try {
			var result = request.responseText;
			var list = GeneralXMLTools.createDocumentFromString(result);
		
			message = list.getElementsByTagName("message")[0].firstChild.nodeValue;
		}
		catch(e){
			
		}
		try {
			var result = request.responseText;
			var list = GeneralXMLTools.createDocumentFromString(result);
			
			var value = list.getElementsByTagName("value")[0].firstChild.nodeValue;
			message = list.getElementsByTagName("message")[0].firstChild.nodeValue;
			if(value == "falseMap") {
				$('mapping-input-field').style.backgroundColor = "red";
				alert(message);
				return;
			}
			
		} catch (e) {
			// TODO: handle exception
		}
		var path = wgArticlePath.replace(/\$1/, "Special:GardeningLog?bot=smw_termimportbot&class=0");
		message += '<br>See <a href=\"' +path+ '\">Gardening page</a> for details';
		
		$('extras-bottom').innerHTML = message;
	},
	changeBackground: function(e, node) {
		$('mapping-input-field').style.backgroundColor = "white";
	}
}
 // ----- Classes -----------

var termImportPage = new TermImportPage();
