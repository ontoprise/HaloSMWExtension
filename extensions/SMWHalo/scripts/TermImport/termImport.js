/*  Copyright 2008, ontoprise GmbH
*   Author: Benjamin Langguth
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

var TermImportPage = Class.create();

TermImportPage.prototype = {
	initialize: function() {
	
		/*if (wgCanonicalSpecialPageName != 'Gardening') return;*/
	},
	
	/**
	 * Request the chosen TL module and paste TL description and DAL IDs
	 * in the tl-desc respectively dal-id
	 */
	connectTL: function(e, node, tlID) {
		Element.removeClassName(node,'entry');
		Element.addClassName(node, 'entry-active');
		
		if (this.pendingIndicator == null) {
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
		 				 "onMouseOut=\"\" onClick=\"termImportPage.getDAL(event, this, '" + dalid + "', '" + tlID + "')\"><a>" + dalid + "</a></div>";
				}	
			}	
		}
		if ( response ) {
			$('dalid').innerHTML = response;			
		}
	},
	
	/*
	 * function for getting all DAMs for the chosen TLM
	 */
	getDAL: function(e, node, dalID, tlID) {
		Element.removeClassName(node,'entry');
		Element.addClassName(node, 'entry-active');
		
		if (this.pendingIndicator == null) {
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
//		var datasources = list.getElementsByTagName("DataSource");
		var datasources = list.getElementsByTagName("DataSource")[0].childNodes;
		response = "<i>The following Information is needed in order to start the Import</i><br><br><form id=\"source\"><Table>Source&nbsp;";
		
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
						response += "<input type=\"hidden\" id=\"tag_"+ attrib_display +"\" value=\""+tag+"\"/>"						
					}					
				//}	
			}		
		}
		response += "</table><br><button id=\"submitSource\" type=\"button\" name=\"run\" onclick=\"termImportPage.getSource(event, this,'" +tlID+ "','" + dalID +"')\">Submit</button></form>";
		//fade in the source specification
		$('source-spec').innerHTML = response;
	},
	
	getSource: function(e, node, tlID, dalID) {
				
		if (this.pendingIndicator == null) {
			this.pendingIndicatorImportset = new OBPendingIndicator($('importset'));
		}
		this.pendingIndicatorImportset.show();
		
		try {
			var source = document.getElementsByName("source");
			var sourcearray = new Array();
			var tag_array = new Array();
			//XML structure for the DataSource
			var dataSource = '';
			var topcontainer = "<table cellspacing=\"10\"><tr><td>TLM: " + tlID + "</td><td>DAM: " + dalID + "</td><td><ul>";
			for (var i = 0, n = source.length; i < n; i++) {
				sourcearray[i] = document.getElementById(source[i].id).value;
				if (sourcearray[i] && sourcearray[i] != '') {
					//create XML doc
					tag_array[i] = document.getElementById("tag_" + source[i].id).value;
					
					dataSource += "<" + tag_array[i] + ">" + sourcearray[i] + "</" + tag_array[i] + ">";
					
					//change the top-container
					var display = source[i].id;
					//.charAt(0).toUpperCase()+source[i].substr(1 ,source[i].id.value.length);
					topcontainer += "<li>" + display + "&nbsp;" +sourcearray[i] + "</li>";
				}
			}
			topcontainer += "</ul></td><td><a style=\"cursor: pointer;\"" +
					" onClick=\"termImportPage.getTopContainer(event, this)\">edit</a></td></tr></table>";
		}
		catch(e) {
			//TODO: errorhandling
		}
		
		$('summary').style.display = "inline";
		$('summary').innerHTML = topcontainer;
		
		$('top-container').style.display = "none";
				
		dataSource = "<DataSource>" + dataSource + "</DataSource>";
		
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
			var import_response='';
			
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
			var property_response = "The following attributes can be extracted from data source defined:";
			
			/*		"<a onClick=\"termImportPage.refreshPreview(event, this,'" +tlID+ "','" + dalID +"')\">" + 
												"<img align=\"right\" src=\""+wgScriptPath+"/extensions/SMWHalo/skins/TermImport/images/Cog_add.png\"></a><br/><br/>";*/
												
			property_response += '<table id=\"attrib_table\" class=\'mytable\'>';
			
			for (var i = 0, n = properties.length; i < n; i++) {
				// get one of the importsets
				var property = properties[i]; 
				if(property.nodeType == 1) {
					//find the name Obj of the 
					var property_name_obj = property.getElementsByTagName('name');
					if ( property_name_obj ){
						var property_name = property_name_obj[0].firstChild.nodeValue;
						// add importset item to the list
						property_response += "<tr><td class=\"mytd\">" + property_name + "</td></tr>";
					}	
				}	
			}
			property_response += "</table>";
		
			var terms = list.getElementsByTagName("terms")[0].childNodes;
			var article_response = '<table id=\"article_table\" class=\'mytable\'>';
			var article_count = 0;
			for (var i = 0, n = terms.length; i < n; i++) {
			// get one of the importsets
				var term = terms[i]; 
				if(term.nodeType == 1) {
					//find the name Obj of the 
					if ( term.firstChild ){
						var article_name = term.firstChild.nodeValue;
						// add article name to the table
						article_response += "<tr><td class=\"mytd\">" + article_name + "</td>";
						article_count++;
					}	
				}	
			}
			article_response = "The following " + article_count + " articles will be generated in the wiki:" +
					"<a onClick=\"termImportPage.refreshPreview(event, this,'" +tlID+ "','" + dalID +"')\">" + 
					"<img align=\"right\" src=\""+wgScriptPath+"/extensions/SMWHalo/skins/TermImport/images/Cog_add.png\"></a><br/><br/>"
					+ article_response + "</table>";
			
		}
		catch(e){
			
		}
		if (import_response) {
			$('extras').style.display = "inline";
			if (Prototype.Browser.IE) {
				//innerHTML can't be used because of Bug: http://support.microsoft.com/default.aspx?scid=kb;en-us;276228
				$('importset-input-field').outerHTML = "<select name=\"importset\" id=\"importset-input-field\" size=\"1\">" + 
					import_response + "</select>";
			}
			else {
				$('importset-input-field').innerHTML = import_response;
			}
			$('extras-bottom').innerHTML = "<a onClick=\"termImportPage.importItNow(event, this,'" +tlID+ "','" + dalID +"')\"><b><br>Click to start import</b>" + 
											"<img src=\""+wgScriptPath+"/extensions/SMWHalo/skins/TermImport/images/Accept.png\"></a>";
		}		
		if (property_response) {
			$('extras-right').style.display = "inline";
			$('attrib').innerHTML = property_response;
			$('articles').innerHTML = article_response;
		}
	},
	
	/*
	 * hides the summary div and shows (again) the select boxes for the
	 * transport layer module (TLM) and the data access module (DAM) and the source specification fields
	 */
	getTopContainer: function(e,node) {
		$('summary').style.display = "none";		
		$('top-container').style.display = "inline";
		$('extras').style.display = "none";
		$('extras-bottom').style.display = "none";
	},
	
	/*
	 * adds the new entry from the policy field to the list
	 */
	getPolicy: function(e, node){
		try {
			//var policy_selects = document.getElementsByName('policy-select');		
			var policy_selects = document.getElementById('policy-textarea').getElementsByTagName('option');
			var newpolicy = document.getElementById('policy-input-field').value;
			var response = '';
			for (var i = 0, n = policy_selects.length; i < n; i++) {
				var policy_select = policy_selects[i];
				//could be an empty string so that firstChild.nodeValue can't exist!
				if (policy_select.firstChild) {
					response += "<option name='policy-select'>" + policy_select.firstChild.nodeValue + "</option>";
				}
			}
		}
		catch(e) {
			
		}
		response += "<option name='policy-select'>" + newpolicy + "</option>";
		if (Prototype.Browser.IE) {
			//innerHTML can't be used because of Bug: http://support.microsoft.com/default.aspx?scid=kb;en-us;276228
			$('policy-textarea').outerHTML = "<select id=\"policy-textarea\" name=\"policy-out\" size=\"7\" multiple>" + 
				response + "</select>";
		}
		else {
			$('policy-textarea').innerHTML = response;
		}
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
			for (var i = 0, n = policy_selects.length; i < n; i++) {
				var policy_select = policy_selects[i];
				if(policy_select.selected == false) {
					response += "<option name='policy-select'>" + policy_select.firstChild.nodeValue + "</option>";
				}
			}
		}
		catch(e){
			
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
		
		if (this.pendingIndicator == null) {
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
				sourcearray[i] = document.getElementById(source[i].id).value;
				if (sourcearray[i] && sourcearray[i] != '') {
					tag_array[i] = document.getElementById("tag_" + source[i].id).value;
					
					dataSource += "<" + tag_array[i] + ">" + sourcearray[i] + "</" + tag_array[i] + ">";
				}
			}
		
			dataSource = "<DataSource>" + dataSource + "</DataSource>";
		
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
    	
   		 		for(var i = 0, n = policy_selects.length; i < n; i++) {
    				inputPolicy += '<regex>' + policy_selects[i].firstChild.nodeValue + '</regex>'+"\n";
    			}
				inputPolicy +='<term></term>'+
					'</terms>'+"\n"+
					'<properties>'+"\n"+
					'</properties>'+"\n"+
					'	<property>articleName</property>'+"\n"+
					'</InputPolicy>'+"\n";
			}
			else {
				var inputPolicy = '';
			}
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
		}
			sajax_do_call('smwf_ti_connectTL', [tlID, dalID , dataSource, importSetName, inputPolicy, mappingPage, conflictPol, 0], this.refreshPreviewCallback.bind(this, tlID, dalID));
	},
	
	refreshPreviewCallback: function(tlID, dalID, request){
		//can Properties change?!?
		
		
		//refresh the article preview!!!
		this.pendingIndicatorArticles.hide();
		
		var result = request.responseText;
		var list = GeneralXMLTools.createDocumentFromString(result);
		
				
		try {
			var terms = list.getElementsByTagName("terms")[0].childNodes;
			var article_response = '<table id=\"article_table\" class=\'mytable\'>';
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
							article_response += "<tr><td class=\"mytd\">" + article_name + "</td>";
							article_count++;
						}
					}	
				}	
			}
			article_response = "The following " + article_count + " articles will be generated in the wiki:" +
					"<a onClick=\"termImportPage.refreshPreview(event, this,'" +tlID+ "','" + dalID +"')\">" + 
					"<img align=\"right\" src=\""+wgScriptPath+"/extensions/SMWHalo/skins/TermImport/images/Cog_add.png\"></a><br/><br/>" + 
					article_response + "</table>";
			
		}
		catch(e){
			
		}		
		$('articles').innerHTML = article_response;
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
				sourcearray[i] = document.getElementById(source[i].id).value;
				if (sourcearray[i] && sourcearray[i] != '') {
					tag_array[i] = document.getElementById("tag_" + source[i].id).value;
					
					dataSource += "<" + tag_array[i] + ">" + sourcearray[i] + "</" + tag_array[i] + ">";
				}
			}
			dataSource = "<DataSource>" + dataSource + "</DataSource>";
			
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
    	
    			for(var i = 0, n = policy_selects.length; i < n; i++) {
    				inputPolicy += '<regex>' + policy_selects[i].firstChild.nodeValue + '</regex>'+"\n";
    			}
   	 			inputPolicy +='<term></term>'+
   	 				'</terms>'+"\n"+
   	 				'<properties>'+"\n"+
   	 				'	<property>articleName</property>'+"\n"+
   	 				'</properties>'+"\n"+
					'</InputPolicy>'+"\n";
			}
			else {
				var inputPolicy = '';
			}
			//mapping policy
			var mappingPage = document.getElementById('mapping-input-field').value;
			
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
		}
		sajax_do_call('smwf_ti_connectTL', [tlID, dalID , dataSource, importSetName, inputPolicy, 'TestPage', conflictPol, 1], this.importItNowCallback.bind(this, tlID, dalID));
		
		//redirect to GardeningPage???
				
	},
	importItNowCallback: function(tlID, dalID, request){
		var text = 'succesfully started this fucking bot!!!';
		
		var message= '';
		try {
			var result = request.responseText;
			var list = GeneralXMLTools.createDocumentFromString(result);
		

			message = list.getElementsByTagName("message")[0].firstChild.nodeValue;
		}
		catch(e){
			
		}
		var path = wgArticlePath.replace(/\$1/, "Special:Gardening");
		message += '<br>See <a href=\"' +path+ '\">Gardening page</a> for details';
		
		$('extras-bottom').innerHTML = message;
	}
}
 // ----- Classes -----------

var termImportPage = new TermImportPage();
