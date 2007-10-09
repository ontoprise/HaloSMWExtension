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
Event.observe(window, 'load', smw_help_callme);

var initHelp = function(){
	var ns = wgNamespaceNumber==0?"Main":wgCanonicalNamespace ;
	if (wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "Search"){
		ns = "Search";
	}
	if (wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "QueryInterface"){
		ns = "QueryInterface";
	}
	sajax_do_call('smwfGetHelp', [ns , wgAction], displayHelp.bind(this));
}

function smw_help_callme(){
	if((wgAction == "edit"
	    || wgCanonicalSpecialPageName == "Search")
	   && stb_control.isToolbarAvailable()){
		helpcontainer = stb_control.createDivContainer(HELPCONTAINER, 0);
		helpcontainer.setHeadline('<img src="'+wgScriptPath+'/extensions/SMWHalo/skins/help.gif"/> Help');
		initHelp();
	}
	else if(wgCanonicalSpecialPageName == "QueryInterface"){
		initHelp();
	}
}

function displayHelp(request){
	//No SemTB in QI, therefore special treatment
	if(wgCanonicalSpecialPageName == "QueryInterface"){
		if ( request.responseText != '' ){
			$('qi-help-content').innerHTML = request.responseText;
		}
	}
	else { //SemTB available
		if (request.responseText!=''){
			helpcontainer.setContent(request.responseText);
		}
		else {
			helpcontainer.setHeadline = ' ';
		}
		helpcontainer.contentChanged();
	}
}

function askQuestion(){
	$('questionLoaderIcon').show();
	var ns = wgNamespaceNumber==0?"Main":wgCanonicalNamespace ;
	if (wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "Search"){
		ns = "Search";
	}
	if (wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "QueryInterface"){
		ns = "QueryInterface";
	}
	sajax_do_call('smwfAskQuestion', [ns , wgAction, $('question').value], hideQuestionForm.bind(this));
}

function hideQuestionForm(request){
	initHelp();
	$('questionLoaderIcon').hide();
	$('askHelp').hide();
	alert(request.responseText);
}

function submitenter(myfield,e) {
	var keycode;
	if (window.event){
		keycode = window.event.keyCode;
	}
	else if (e) {
		keycode = e.which;
	}
	else {
		return true;
	}

	if (keycode == 13){
		askQuestion();
		return false;
	}
	else {
	   return true;
	}
}

function helplog(question, action){
	/*STARTLOG*/
	if(window.smwhgLogger){
		var logmsg = "Opened Help Page " + question + " with action " + action;
	    smwhgLogger.log(logmsg,"CSH","help_clickedtopic");
	}
	/*ENDLOG*/
	return true;
}