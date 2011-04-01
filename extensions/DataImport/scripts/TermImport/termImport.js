/**
*   Author: Benjamin Langguth, Ingo Steinbauer
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
					response += "<b>Info: </b>"+tl_desc[0].firstChild.nodeValue;
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
					response += "<b>Info: </b>" + dal_desc[0].firstChild.nodeValue;
				}	
			}		
		}
		if ( response ) {
			$('daldesc').innerHTML = response;
		}
		
		//create the right input-div
		this.createDataSourceWidget (
				list.getElementsByTagName("DataSource")[0].childNodes, tlID, dalID);
	},
	
	createDataSourceWidget : function(datasources, tlID, dalID) {
		response = diLanguage.getMessage('smw_ti_sourceinfo')
				+ "<br><br><Table>";
				//+ diLanguage.getMessage('smw_ti_source') + "&nbsp;";

		var fieldnumber = 0;
		for ( var i = 0, n = datasources.length; i < n; i++) {
			// get one of the datasources
			var datasource = datasources[i];
			
			if (datasource.nodeType == 1) {
				// TagName bekommen
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
					
					if (attrib_type == "file") {
						response += "<tr><td>"
								+ attrib_display
								+ "</td><td><input name=\"source\" id=\""
								+ attrib_display
								+ "\" class=\"inputfield " + attrib_class + "\" type=\"file\" maxlength=\"100\" value=\""
								+ datasource.textContent + "\"/>" + "</td></tr>";
					} else if(attrib_type == "checkbox"){
						response += "<tr><td>"
							+ attrib_display
							+ "</td><td><input name=\"source\" id=\""
							+ attrib_display
							+ "\" class=\"inputfield\" type=\"" + attrib_type + "\" style=\"width:auto;margin:0;\" checked=\""
							+ datasource.textContent + "\"/></td></tr>";
					} else if (attrib_type == "textarea") {
						response += "<tr><td style=\"vertical-align:top\">"
							+ attrib_display
							+ "</td><td><textarea name=\"source\" type=\"text\" id=\""
							+ attrib_display
							+ "\" class=\"inputfield " + attrib_class + "\" rows=\"" + rows + "\" value=\""
							+ datasource.textContent + "\">" + datasource.textContent + "</textarea>" + "</td></tr>";
					} else {
						//original class was inputfield
						response += "<tr><td >"
								+ attrib_display
								+ "</td><td><input name=\"source\" id=\""
								+ attrib_display+ "\" class=\"" + attrib_class + "\"";
						if(datasource.getAttribute('autocomplete')){
							response += " class=\"wickEnabled\" typeHint=\"0\" ";
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
				// }
			}
		}
		
		response += "</table><br><button id=\"submitSource\" type=\"button\" name=\"run\" " +
				"onclick=\"termImportPage.getSource(event, this,'"
				+ tlID + "','" + dalID + "')\">Next step</button>";
		// fade in the source specification
		$('source-spec').innerHTML = response;
	},
	
	getSource: function(e, node, tlID, dalID) {
		tlID = this.currentSelectedTLM.firstChild.firstChild.nodeValue;
		dalID = this.currentSelectedDAM.firstChild.firstChild.nodeValue;
		
		this.tlId = tlID;
		this.dalId = dalID;
		
		if (this.pendingIndicatorImportset == null) {
			this.pendingIndicatorImportset = new OBPendingIndicator($('importset'));
		}
		
		//try {
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
		//}
		//catch(e) {
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
				// TODO: handle exception
			}
		//}
		
		//$('summary').style.display = "inline";
		//$('summary').innerHTML = topcontainer;
		
		$("menue-step1").setAttribute("class", "TodoMenueStep");
		$("menue-step1").style.cursor = "pointer";
		$("menue-step1").setAttribute("onclick", 
				"termImportPage.getTopContainer(event, this)");
		$("menue-step2").setAttribute("class", "ActualMenueStep");
		
		$('top-container').style.display = "none";
		
		dataSource = "<DataSource xmlns=\"http://www.ontoprise.de/smwplus#\">" 
			+ dataSource + "</DataSource>";
		$("loading-container").style.display ="inline";
		
		sajax_do_call('smwf_ti_connectTL', [tlID, dalID , dataSource, '', '', '', '', 0], this.getSourceCallback.bind(this, tlID, dalID));
	},
	
	/*
	 * Callback function for the source specification
	 */
	getSourceCallback: function(tlID, dalID, request) {
		$("loading-container").style.display ="none";
		if(this.pendingIndicator != null){
			this.pendingIndicatorImportset.hide();
		}
		
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
			//var property_response = diLanguage.getMessage('smw_ti_attributes-heading');
			
			var property_response = '<div class=\"scrolling\"><table id=\"attrib_table\" class=\'mytable\'>';
			
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
									property_name + "\" checked=\"true\"/></td><td class=\"mytd\">" + property_name + "</td></tr>";
							}
						}
					}	
				}	
			}
			property_response += "</table></div>";
		
			var terms = list.getElementsByTagName("terms")[0].childNodes;
			var article_response = '<table class=\'mytable\'>';
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
			article_response += "</table>";
		}
		catch(e){
			//doesn't work in IE,so put it in a try-block
			try {
				var test = list.getElementsByTagName("message");
				var error_message = "<br/><br/><span id=\"sumtable\">" + 
					list.getElementsByTagName("message")[0].firstChild.nodeValue + "</span><br/><br/>"; 
				error_message += "<input type=\"button\" onClick=\"termImportPage.getTopContainer(event, this)\""
					+ " value=\""+diLanguage.getMessage('smw_ti_prev-step')+"\"/>";
				$('summary').style.display = "block";
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
			$('article_table').innerHTML = article_response;
			$('article-count').innerHTML = article_count;
			$('extras-bottom').style.display = "inline";
			
			$('extras-bottom').innerHTML = 
				"<input type=\"button\" onClick=\"termImportPage.getTopContainer(event, this)\""
				+ " value=\""+diLanguage.getMessage('smw_ti_prev-step')+"\"/>&nbsp;&nbsp;";
			
			$('extras-bottom').innerHTML += 
				"<input type=\"button\" onClick=\"termImportPage.importItNow(event, this,'" +tlID+ "','" + dalID +"', true)\""
				+ " value=\""+diLanguage.getMessage('smw_ti_save')+"\"/>&nbsp;&nbsp;";
			
			$('extras-bottom').innerHTML += 
				"<input type=\"button\" onClick=\"termImportPage.importItNow(event, this,'" +tlID+ "','" + dalID +"', false)\""
				+ " value=\"" +diLanguage.getMessage('smw_ti_execute') + "\"/><br/><br/>";
			
			
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
		
		if(this.tlId != null){
			this.fillTermImportPage();
		}
	},
	
	/*
	 * hides the summary div and shows (again) the select boxes for the
	 * transport layer module (TLM) and the data access module (DAM) and the source specification fields
	 */
	getTopContainer: function(e, node) {
		// var goon = confirm("goon");
		// if(!goon){
		//	return;
		// }
		
		$('summary').style.display = "none";		
		$('top-container').style.display = "";
		$('extras').style.display = "none";
		$('extras-bottom').style.display = "none";
		
		var tlId = this.currentSelectedTLM.firstChild.firstChild.nodeValue;
		var dalId = this.currentSelectedDAM.firstChild.firstChild.nodeValue;
		
		$("menue-step2").setAttribute("class", "TodoMenueStep");
		$("menue-step2").style.cursor = "pointer";
		$("menue-step2").setAttribute("onclick", 
					"termImportPage.getSource(event, this,\"" + tlId + "\", \"" + dalId + "\")");
		$("menue-step1").setAttribute("class", "ActualMenueStep");
		
		this.tlId = this.currentSelectedTLM.firstChild.firstChild.nodeValue;
		this.dalId = this.currentSelectedDAM.firstChild.firstChild.nodeValue;
		
		var result = this.getImportCredentials(e, node, this.tlId, this.dalId, false);

		this.dataSource = escape(result[0]);
		this.importSet = result[1];

		var inputPolicy = GeneralXMLTools.createDocumentFromString(result[2]);
		this.regex = this.implodeElements(inputPolicy.getElementsByTagName("regex"));
		this.terms = this.implodeElements(inputPolicy.getElementsByTagName("term"));
		this.properties = this.implodeElements(inputPolicy.getElementsByTagName("property"));
		this.mappingPolicy = result[3];
		this.conflictPolicy = result[4];
		this.termImportName = result[5];
		this.updatePolicy = result[6];
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
			var article_response = '<table class=\"mytable\">';
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
		$('article_table').innerHTML = article_response;
		$('article-count').innerHTML = article_count;
	},
	
	/*
	 * Do the import!
	 */
	importItNow: function(e, node, tlID, dalID, createOnly){
		var result = termImportPage.getImportCredentials(e, node, tlID, dalID, true);
		if(result == null){
			return;
		} else {
			var dataSource = result[0];  
			var importSetName= result[1];
			var inputPolicy = result[2]; 
			var mappingPage = result[3]; 
			var conflictPol = result[4];
			var termImportName = result[5];
			var updatePolicy = result[6];
			var edit = this.editTermImport;
			
			$("extras-bottom").style.display = "none";
			$("loading-bottom-container").style.display = "inline";
			sajax_do_call('smwf_ti_connectTL', [tlID, dalID , dataSource, importSetName, 
			                                    inputPolicy, mappingPage, conflictPol, 1, termImportName, updatePolicy, edit, createOnly]
			                                    , this.importItNowCallback.bind(this, tlID, dalID, createOnly));
		}
	},
	
	getImportCredentials: function(e, node, tlID, dalID, commit){
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
					if(typeof(sourcearray[i]) == 'string'){
						sourcearray[i] = sourcearray[i].replace(/>/g, "&gt;");
						sourcearray[i] = sourcearray[i].replace(/</g, "&lt;");
					}
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
			//mapping policy
			var mappingPage = document.getElementById('mapping-input-field').value;
			if(mappingPage == '' && commit){
				//do not import without a mapping page!
				$('mapping-input-field').style.backgroundColor = "red";
				return;
			}
			
			//this code does only work once
			// var re = /\w+/g;
			// if(mappingPage.length > 0){
   			//	// min. one other char than a whitespace
   			//	if(re.test(mappingPage) != true && commit) {
   			//		$('mapping-input-field').style.backgroundColor = "red";
   			//		return ;	
   			//	}
			// } 
			
			//conflict policy
			//var conflict = document.getElementById('conflict-input-field').options[document.getElementById('conflict-input-field').selectedIndex].text;
			var optionIndex = document.getElementById('conflict-input-field').selectedIndex; 
			if(optionIndex == -1){
				optionIndex = 0;
			}
			var conflict = document.getElementById('conflict-input-field').childNodes[optionIndex].firstChild.nodeValue;
			if( conflict == 'overwrite') {
				var conflictPol = true;
			}
			else {
				var conflictPol = false;
			}
			//term import name
			var termImportName = document.getElementById('ti-name-input-field').value;
			if(termImportName == '' && commit){
				//do not import without a term import name!
				$('ti-name-input-field').style.backgroundColor = "red";
				return ;
			}
			
			//this code does only work once
			// if(termImportName.length > 0){
			// // min. one other char than a whitespace
			// if(re.test(termImportName) != true && commit) {
			// $('ti-name-input-field').style.backgroundColor = "red";
			// return ;
			//   				}
			//			}
			
			//update policy todo:make integer check
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
		} catch(e) {
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
		
		var result = new Array();
		result[0] = dataSource;  
		result[1] = importSetName; 
		result[2] = inputPolicy; 
		result[3] = mappingPage; 
		result[4] = conflictPol;
		result[5] = termImportName;
		result[6] = updatePolicy;
		return result;
	},
	
	importItNowCallback: function(tlID, dalID, createOnly, request){
		$("extras-bottom").style.display = "inline";
		$("loading-bottom-container").style.display = "none";
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
			} else if(value == "falseTIN") {
				$('ti-name-input-field').style.backgroundColor = "red";
				alert(message);
				return;
			} else if(value == "articleCreated") {
				var path = wgArticlePath.replace(/\$1/, "TermImport:" + message);
				message = '<br><b>The Term Import definition <a href=\"' +path+ '\">' + message  + '</a> was created successfully.<br/></b><br/>';
				
				$('extras-bottom').innerHTML = message;
				return;
			}
			
		} catch (e) {
			// TODO: handle exception
		}
		var path = wgArticlePath.replace(/\$1/, "Special:Gardening");
		message += '<br>See <a href=\"' +path+ '\">Gardening page</a> for details<br/><br/>';
		
		$('extras-bottom').innerHTML = "<b>"+message+"</b>";
	},
	changeBackground: function(e, node) {
		// $('mapping-input-field').style.backgroundColor = "white";
		node.style.backgroundColor = "white";
	},
	
	editTermImportDefinition : function(){
		var editDataSpan = $('editDataSpan');
		
		if(editDataSpan == null){
			this.editTermImport = false;
			return;
		}
		this.editTermImport = true;
		
		this.tlId = $('tlId-ed').firstChild.nodeValue;
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
		this.mappingPolicy = $('mappingPolicy-ed').firstChild.nodeValue;
		this.conflictPolicy = $('conflictPolicy-ed').firstChild.nodeValue;
		this.termImportName = $('termImportName-ed').firstChild.nodeValue;
		this.updatePolicy = $('updatePolicy-ed').firstChild.nodeValue;
		
		//transport layer	
		var found = false;
		for (var i=0; i < $('tlid').childNodes.length; i++){
			if($('tlid').childNodes[i].firstChild.firstChild.nodeValue == this.tlId){
				this.currentSelectedTLM = $('tlid').childNodes[i];
				Element.addClassName(this.currentSelectedTLM,'entry-active');
				$('tldesc').innerHTML = "<b>Info: </b>"+$('tl-desc').firstChild.nodeValue;
				found = true;
			}
		}
		
		if(!found){
			alert("The Transport Layer Module " + this.tlId + " is not available.");
			return;
		}
		
		//data import layer
		var dals = $('dalIds').firstChild.nodeValue.split(',');
		$('dalid').innerHTML = "";
		found = false;
		for(var i=0; i < dals.length; i++){		
			if(dals[i] != ""){
				$('dalid').innerHTML += "<div class=\"entry\" onMouseOver=\"this.className='entry-over';\""
					+ "onMouseOut=\"termImportPage.showRightDAM(event, this, '$tlid')\" "
					+ "onClick=\"termImportPage.getDAL(event, this, '" 
					+ dals[i] + "', '" + this.tlId + "')\"><a>" + dals[i] + "</a></div>";
				if(dals[i] == this.dalId){
					this.currentSelectedDAM = $('dalid').childNodes[i].cloneNode(true);
					found = true;
				}
			}
		}
		
		if(!found){
			alert("The Data Access Module " + this.dalId + " is not available.");
			return;
		}
		
		Element.addClassName(this.currentSelectedDAM,'entry-active');
		$('daldesc').innerHTML = "<b>Info: </b>" + $('dal-desc').firstChild.nodeValue;
		
		//data source
		dataSource = GeneralXMLTools.createDocumentFromString(this.dataSource);
		dataSource = dataSource.getElementsByTagName("DataSource")[0].childNodes;
		this.createDataSourceWidget(dataSource, this.tlId, this.dalId);
		
		this.getSource(null, null, this.tlId, this.dalId);
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
		
		if(this.mappingPolicy){
			$('mapping-input-field').value = this.mappingPolicy;
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
		
		var tlId = this.currentSelectedTLM.firstChild.firstChild.nodeValue;
		var dalId = this.currentSelectedDAM.firstChild.firstChild.nodeValue;
		this.refreshPreview(null, null, this.tlId, this.dalId);
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
	}
}
 // ----- Classes -----------

var termImportPage = new TermImportPage();

Event.observe(window, 'load', termImportPage.editTermImportDefinition
	.bindAsEventListener(termImportPage));