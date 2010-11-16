/**   The Data Import-Extension is free software; you can redistribute it and/or modify
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
 *   along with this program.  If not, see <http:// www.gnu.org/licenses/>.
 */

/**
 * This file adds a container for inserting a web service into an article to the
 * semantic toolbar
 * 
 * @author Ingo Steinbauer
 * 
 */

var UseWebService = Class.create();

UseWebService.prototype = {
		
	initialize : function() {
	},

	processStep1 : function() {
		this.showPendingIndicator("step1-go");

		var ws = $("step1-webservice").value;
		sajax_do_call("smwf_wsu_processStep1", [ ws ],
				this.processStep1CallBack.bind(this));
	},

	processStep1CallBack : function(request) {
		var parameters = request.responseText.split(";");
		
		this.webService = $("step1-webservice").value; 
		$("step1-webservice").setAttribute("onchange" , "useWSSpecial.confirmWSChange()");
		
		this.protocol = parameters[0];
		
		parameters.shift();
		parameters.pop();
		
		$("step1-go-img").style.display = "none";
		$("step2-go-img").style.display = "";

		$("step2").style.display = "";
		$("step3").style.display = "none";
		$("step4").style.display = "none";
		$("step5").style.display = "none";
		
		if($("step6")){
			$("step6").style.display = "none";
		}

		$("menue-step1").className = "DoneMenueStep";
		$("menue-step2").className = "ActualMenueStep";
		$("menue-step3").className = "TodoMenueStep";
		$("menue-step4").className = "TodoMenueStep";
		$("menue-step5").className = "TodoMenueStep";
		
		if($("step6")){
			$("menue-step5").className = "TodoMenueStep";
		}

		this.hideHelpAll();

		var tempHead = $("step2-parameters").childNodes[0].childNodes[0]
				.cloneNode(true);
		var tempTable = $("step2-parameters").childNodes[0].cloneNode(false);
		$("step2-parameters").removeChild($("step2-parameters").childNodes[0]);
		$("step2-parameters").appendChild(tempTable);
		$("step2-parameters").childNodes[0].appendChild(tempHead);

		//to detect if the use all checkbox should be displayed
		var optionalParameterExists = false;
		
		//the default values features are only displayed if this is not a LD WS
		if(this.protocol == 'LinkedData'){
			$("step2-parameters").childNodes[0].childNodes[0].childNodes[3].style.display = 'none';
		} else {
			$("step2-parameters").childNodes[0].childNodes[0].childNodes[3].style.display = '';
		}
		
		for ( var i = 0; i < parameters.length; i += 3) {
			var row = document.createElement("tr");

			var td = document.createElement("td");
			var input = document.createElement("span");
			var text = document.createTextNode(parameters[i]);
			input.appendChild(text);
			td.appendChild(input);
			row.appendChild(td);

			td = document.createElement("td");
			td.style.textAlign = "right";
			input = document.createElement("input");
			input.type = "checkbox";
			if (parameters[i + 1] == "false" || parameters[i + 1] == "") {
				input.checked = true;
				input.disabled = "true";
				td.appendChild(input);
				
			} else {
				optionalParameterExists = true;
				input.checked = false;
				td.appendChild(input);
			}
			row.appendChild(td);

			td = document.createElement("td");
			input = document.createElement("input");
			input.size = "70";
			td.appendChild(input);
			row.appendChild(td);

			// the default value features are only displayed
			// if this is not a LD WS
			if(this.protocol != 'LinkedData'){
				td = document.createElement("td");
				input = document.createElement("input");
				input.type = "checkbox";
				input.style.marginRight = "8px";
				if(parameters[i + 2] != ""){
					td.appendChild(input);
					input = document.createElement("span");
					text = document.createTextNode(parameters[i + 2]);
					input.appendChild(text);
					td.appendChild(input);
				} else {
					input.checked = false;
					input.disabled = true;
					td.appendChild(input);
					
					input = document.createElement("input");
					input.value = "Not available";
					input.disabled = true;
					input.style.borderWidth = "0px";
					td.appendChild(input);
				}
				row.appendChild(td);
			}
			$("step2-parameters").childNodes[0].appendChild(row);
		}
		
		if(optionalParameterExists){
			$("step2-use-label").style.visibility = "visible";
		}
		
		this.hidePendingIndicator();
		
		if(parameters.length == 0){
			$("step2-noparameters").style.display = "";
			$("step2-parameters").style.display = "none";
			this.processStep2();
		} else {
			$("step2-noparameters").style.display = "none";
			$("step2-parameters").style.display = "";
		}
		
		if(this.protocol == "LinkedData"){
			$("step2-ld-help").style.display = "";
		} else {
			$("step2-ld-help").style.display = "none";
		}
	},

	processStep2 : function() {
		this.showPendingIndicator("step2-go");

		var ws = $("step1-webservice").value;
		sajax_do_call("smwf_wsu_processStep2", [ ws ],
				this.processStep2CallBack.bind(this));
	},

	processStep2CallBack : function(request) {
		var results = request.responseText.split(";");
		results.pop();

		$("step2-go-img").style.display = "none";
		$("step3-go-img").style.display = "";

		$("step3").style.display = "";

		$("menue-step2").className = "DoneMenueStep";
		$("menue-step3").className = "ActualMenueStep";

		this.hideHelpAll();

		var tempHead = $("step3-results").childNodes[0].childNodes[0]
				.cloneNode(true);
		var tempTable = $("step3-results").childNodes[0].cloneNode(false);
		$("step3-results").removeChild($("step3-results").childNodes[0]);
		$("step3-results").appendChild(tempTable);
		$("step3-results").childNodes[0].appendChild(tempHead);

		for ( var i = 0; i < results.length; i++) {
			var row = document.createElement("tr");

			var td = document.createElement("td");
			var input = document.createElement("span");
			var text = document.createTextNode(results[i]);
			input.appendChild(text);
			td.appendChild(input);
			row.appendChild(td);

			td = document.createElement("td");
			td.style.textAlign = "right";
			$("step3-use").checked = true;
			input = document.createElement("input");
			input.type = "checkbox";
			input.checked = true;
			
			if(window.addEventListener){
				input.addEventListener("change", useWSSpecial.displaySortDetails, false);
			} else {
				input.attachEvent("onchange", useWSSpecial.displaySortDetails, false);
			}
			td.appendChild(input);
			row.appendChild(td);
			
			td = document.createElement("td");
			input = document.createElement("input");
			td.appendChild(input);
			
			row.appendChild(td);

			$("step3-results").childNodes[0].appendChild(row);
		}

		this.hidePendingIndicator();
		
		if(results.length == 0){
			$("step3-results").style.display = "none";
			$("step3-noresults").style.display = "";
			this.processStep3();
		} else {
			$("step3-results").style.display = "";
			$("step3-noresults").style.display = "none";
		}
		
		if(this.protocol == "LinkedData"){
			$("step3-ld-help").style.display = "";
		} else {
			$("step3-ld-help").style.display = "none";
		}
	},

	processStep3 : function() {
		$("step3-go-img").style.display = "none";
		$("step4-go-img").style.display = "";

		$("step4").style.display = "";

		$("menue-step3").className = "DoneMenueStep";
		$("menue-step4").className = "ActualMenueStep";
		
		$("step4-template").value = "";
		useWSSpecial.displaySortDetails();
		
		this.hideHelpAll();
	},
	
	processStep6 : function() {
		if($('menue-step6').style.display == 'none'){
			$("step6").style.display = "none";
			useWSSpecial.processStep4();
		} else {
			$("step4-go-img").style.display = "none";
			$("step6-go-img").style.display = "";
	
			$("step6").style.display = "";
	
			$("menue-step4").className = "DoneMenueStep";
			$("menue-step6").className = "ActualMenueStep";
			
			this.hideHelpAll();
		}
	},

	processStep4 : function() {
		$("menue-step4").className = "DoneMenueStep";
		$("step4-go-img").style.display = "none";
		
		if($("step6-go-img")){		
			$("step6-go-img").style.display = "none";
			$("menue-step6").className = "DoneMenueStep";
		}
		
		$("step5").firstChild.firstChild.nodeValue = $("menue-step5").firstChild.nodeValue;
		
		$("step5-preview").style.display = "none";
			

		$("step5").style.display = "";

		$("menue-step5").className = "ActualMenueStep";

		this.hideHelpAll();

		if(typeof FCK == 'undefined'){
			var params = document.URL.split("?");
			if(params[1] != null){
				params = params[1].split("&");
			} else {
				params = "";
			}
		
			this.url = "";
			for ( var i = 0; i < params.length; i++) {
				if (params[i].indexOf("url") == 0) {
					this.url = unescape(params[i].substr(params[i].indexOf("=") + 1));
				} else if (params[i].indexOf("wsSynS=") == 0) {
					wsSynS = params[i];
				} else if (params[i].indexOf("wsSynE=") == 0) {
					wsSynE = params[i];
				}
			}
		
			this.title = "";

			if (this.url.length > 0) {
				params = this.url.split("&");
				for(i=0; i < params.length; i++){
					if(params[i].indexOf("?title=") > 0){
						var title = params[i].substring(params[i].indexOf("?title=")+7);
						this.title = title;
					}
				}
				this.url += "&" + wsSynS;
				this.url += "&" + wsSynE;
			
				var title = this.url.substr(this.url.indexOf("title=") + 6);
				title = title.substr(0, title.indexOf("&"));
				title = title.replace(/_/g, " ");
				$("step5-add").value = diLanguage.getMessage('smw_wwsu_addcall') + title;
				$("step5-add").style.display = "";
			}
		} else {
			window.parent.SetOkButton(true);
			$('copyWSButton').style.display = "none";
			$('displayWSButton').style.display = "none";
		}
	},

	updateStep4Widgets : function() {
		if ($("step4-format").value in { 'list':1, 'ul':1, 'ol':1, 'template':1, 'transposed':1 }) {
			$("step4-template-container").style.display = "";
		} else {
			$("step4-template-container").style.display = "none";
		} 
	},

	showPendingIndicator : function(onElement) {
		this.hidePendingIndicator();
		$(onElement + "-img").style.visibility = "hidden";
		this.pendingIndicator = new OBPendingIndicator($(onElement));
		this.pendingIndicator.show();
		this.pendingIndicator.onElement = onElement;
	},

	hidePendingIndicator : function() {
		if (this.pendingIndicator != null) {
			$(this.pendingIndicator.onElement + "-img").style.visibility = "visible";
			this.pendingIndicator.hide();
			this.pendingIndicator = null;
		}
	},

	displayHelp : function(id) {
		$("step" + id + "-help").style.display = "";
		$("step" + id + "-help-img").getAttributeNode("onclick").nodeValue 
			= "useWSSpecial.hideHelp(" + id + ")";
	},

	hideHelp : function(id) {
		if($("step" + id + "-help")){
			$("step" + id + "-help").style.display = "none";
			$("step" + id + "-help-img").getAttributeNode("onclick").nodeValue = 
				"useWSSpecial.displayHelp("+id+")";
		}
	},

	hideHelpAll : function() {
		for ( var i = 1; i < 7; i++) {
			this.hideHelp(i);
		}
		if(typeof FCK != 'undefined'){
			window.parent.SetOkButton(false);
		}
	},

	addToArticle : function() {
		this.url += "&wsSyn=" + escape(this.createWSSyn());
		window.location.href = this.url;
	},
	
	createWSSyn : function(){
		var wsSyn = "{{#ws:" + $("step1-webservice").value;
		var parameters = $("step2-parameters").childNodes[0];
		for ( var i = 1; i < parameters.childNodes.length; i++) {
			if (parameters.childNodes[i].childNodes[1].childNodes[0].checked) {
				wsSyn += "\n| "
					+ parameters.childNodes[i].childNodes[0].childNodes[0].childNodes[0].nodeValue;
				if(parameters.childNodes[i].childNodes[3] != undefined && parameters.childNodes[i].childNodes[3].childNodes[0].checked){
					wsSyn += " = "
						+ parameters.childNodes[i].childNodes[3].childNodes[1].childNodes[0].nodeValue;
				} else {
					wsSyn += " = "
						+ parameters.childNodes[i].childNodes[2].childNodes[0].value;
				}
			}
		}

		var results = $("step3-results").childNodes[0];
		for ( var i = 1; i < results.childNodes.length; i++) {
			if (results.childNodes[i].childNodes[1].childNodes[0].checked) {
				wsSyn += "\n| ?"
						+ results.childNodes[i].childNodes[0].childNodes[0].childNodes[0].nodeValue;
				
				if(results.childNodes[i].childNodes[2].childNodes[0].value.length > 0){
					wsSyn += " = " + results.childNodes[i].childNodes[2].childNodes[0].value;
				}
			}
		}
		
		wsSyn += "\n| _format=" + $("step4-format").value;
		if($("step4-template-container").style.display != "none" 
				&& $("step4-template").value != ""){
			wsSyn += "\n| _template=" + $("step4-template").value;
		}
		
		if($('menue-step6')){
			if($('menue-step6').style.display != "none"){
				if($('step6-triplify').checked){
					wsSyn += "\n| _triplify";
				}
				if($('step6-display-subjects').checked){
					wsSyn += "\n| _displayTripleSubjects=" + $('step6-subject-alias').value;
				}
			}
		}
		
		if($("step4-sort-checkbox").checked){
			wsSyn += "\n| _sort=" + $("step4-sort-column").value;
			var order = 'asc';
			if($("step4-sort-order").value == $("step4-sort-order").childNodes[1].value) order='desc';
			wsSyn += "\n| _order=" + order;
		}
		
		wsSyn += "\n| _limit=" + $("step4-limit").value;
		wsSyn += "\n| _offset=" + $("step4-offset").value;
		
		wsSyn += "\n}}\n";
		
		return wsSyn;
	},
	
	getPreview : function() {
		this.showPendingIndicator("step5-preview-button");

		sajax_do_call("smwf_wsu_getPreview", [ this.title, this.createWSSyn() ],
				this.getPreviewCallBack.bind(this));
	},

	getPreviewCallBack : function(request) {
		$("step5-preview").style.display = "";
		var response = request.responseText
		response = response.replace(/warning.png/g, "<b>" + diLanguage.getMessage('smw_wwsu_warning') + "</b>");
		$("step5-preview").innerHTML = response;
		$("step5-preview").style.display = "";
		this.hidePendingIndicator();
	},
	
	confirmWSChange : function(){
		var checked = confirm(diLanguage.getMessage('smw_wwsu_confirm'));
		if(checked){
			useWSSpecial.processStep1();
			useWSSpecial.displayTriplificationOptions();
		} else {
			$("step1-webservice").value = this.webService;
		}
	},
	
	useParameters : function() {
		var checked = false;
		if ($("step2-use").checked) {
			checked = true;
		}
		
		var parameters = $("step2-parameters").childNodes[0];
		for ( var i = 1; i < parameters.childNodes.length; i++) {
			if(!parameters.childNodes[i].childNodes[1].childNodes[0].disabled){
				parameters.childNodes[i].childNodes[1].childNodes[0].checked = checked;
			}
		}
	},

	useResults : function() {
		var checked = false;
		if ($("step3-use").checked) {
			checked = true;
		}

		var results = $("step3-results").childNodes[0];
		for ( var i = 1; i < results.childNodes.length; i++) {
			results.childNodes[i].childNodes[1].childNodes[0].checked = checked;
		}
	},
	
	displayWSSyntax : function (){
		$("step5-preview").style.display = "";
		$("step5-preview").innerHTML = this.createWSSyn().replace(/\n/g, "<br/>");	
	},
	
	copyToClipBoard : function (){
		var text = this.createWSSyn();
		if (window.clipboardData){ //IE
			window.clipboardData.setData("Text", text);
			alert(diLanguage.getMessage('smw_wwsu_clipboard_success'));
		}
	  	else if (window.netscape) {
			try {
				netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');
				var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
				if (!clip){
					alert(diLanguage.getMessage('smw_wwsu_clipboard_fail'));
					return;
				}
				
				var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
				if (!trans){
					alert(diLanguage.getMessage('smw_wwsu_clipboard_fail'));
					return;
				}
				
				trans.addDataFlavor('text/unicode');
				var str = new Object();
				var len = new Object();
				var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
				str.data=this.createWSSyn();
				trans.setTransferData("text/unicode",str,this.createWSSyn().length*2);
				var clipid=Components.interfaces.nsIClipboard;
				if (!clip){
					alert(diLanguage.getMessage('smw_wwsu_clipboard_fail'));
					return;
				} 
				
				clip.setData(trans,null,clipid.kGlobalClipboard);
				alert(dLanguage.getMessage('smw_wwsu_clipboard_success'));
			}
			catch (e) {
				alert(diLanguage.getMessage('smw_wwsu_clipboard_fail'));
			}
		}
		else{
			alert(diLanguage.getMessage('smw_wwsu_clipboard_success'));
		}
	},
	
	displayTriplificationSubjectAlias : function(){
		if($('step6-display-subjects').checked){
			$('step6-subject-alias-container').style.display = "";
		} else {
			$('step6-subject-alias-container').style.display = "none";
		}
	},
	
	displayTriplificationOptions : function(){
		if($("step1-webservice").childNodes[$("step1-webservice").selectedIndex].className == "triplifyable"){
			$("menue-step6").style.display = "";
			$("menue-step5").firstChild.nodeValue = $("menue-step5").firstChild.nodeValue.replace(/5./g, "6.");
		} else {
			$("menue-step6").style.display = "none";
			$("menue-step5").firstChild.nodeValue = $("menue-step5").firstChild.nodeValue.replace(/6./g, "5.");
		}
	},
	
	displaySortDetails : function(){
		if($("step4-sort-checkbox").checked){
			
			while ( $("step4-sort-column").childNodes.length > 0) {
				var node = $("step4-sort-column").childNodes[0];
				$("step4-sort-column").removeChild(node);
			}
			
			var results = $("step3-results").childNodes[0]; 
			for ( var i = 1; i < results.childNodes.length; i++) {
				if (results.childNodes[i].childNodes[1].childNodes[0].checked) {
					var name = results.childNodes[i].childNodes[0].childNodes[0].childNodes[0].nodeValue;
					name = name.substr(name.indexOf('.') + 1);
					var option = document.createElement("option");
					var textNode = document.createTextNode(name);
					option.appendChild(textNode);
					option.value = name;
					$("step4-sort-column").appendChild(option);	
					
				}
			}
			
			$("step4-sort-details").style.display = "";
		} else {
			$("step4-sort-details").style.display = "none";
		}
	}
};

var useWSSpecial = new UseWebService();

