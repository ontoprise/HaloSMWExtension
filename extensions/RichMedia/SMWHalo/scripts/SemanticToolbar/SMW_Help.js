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
/**
 * @file
 * @ingroup SMWHaloSemanticToolbar
 * @author Thomas Schweitzer
 */
if (typeof FCKeditor == 'undefined')
    Event.observe(window, 'load', smw_help_callme);

var smw_help_getNamespace = function() {
	var ns = wgNamespaceNumber==0?"Main":wgCanonicalNamespace ;
    if (wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "Search"){
        ns = "Search";
    }
    else if (wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "QueryInterface"){
        ns = "QueryInterface";
    }
    else if (wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "Gardening"){
        ns = "Gardening";
    }
    else if (wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "GardeningLog"){
        ns = "Gardening";
    }
    else if (wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "OntologyBrowser"){
        ns = "OntologyBrowser";
    }
    return ns;
}
var initHelp = function(){
    return; // disable the help container always, it's obsolete
	var ns = smw_help_getNamespace();
	sajax_do_call('smwf_tb_GetHelp', [ns , wgAction], displayHelp.bind(this));
	
}

function smw_help_callme(){
    return; // disable the help container always, it's obsolete
	var ns = smw_help_getNamespace();
	if((wgAction == "edit" || wgAction == "annotate" || wgAction == 'formedit' || wgAction == 'submit'
	    || wgCanonicalSpecialPageName == "Search" || wgCanonicalSpecialPageName == 'AddData'
	    || wgCanonicalSpecialPageName == 'EditData' || wgCanonicalSpecialPageName == 'FormEdit')
	   && stb_control.isToolbarAvailable()){
		helpcontainer = stb_control.createDivContainer(HELPCONTAINER, 0);
		helpcontainer.setHeadline('<img src="'+wgScriptPath+'/extensions/SMWHalo/skins/help.gif"/> ' + gLanguage.getMessage('Help'));
		
		// KK: initalize help only when Help container is open.
		var helpLoaded = false;
		helpcontainer.showContainerEvent = function() {
			if (!helpcontainer.isVisible()) return;
			if (helpLoaded) return;
					   
		    sajax_do_call('smwf_tb_GetHelp', [ns , wgAction], displayHelp.bind(this));
		    helpLoaded = true;
		}
		
		displayHelp();	
			
		
	}
}

function displayHelp(request){
	
	if (!request) {
		helpcontainer.setHeadline = ' ';
		helpcontainer.contentChanged();
		return;
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
	var ns = smw_help_getNamespace();
	sajax_do_call('smwf_tb_AskQuestion', [ns , wgAction, $('question').value], hideQuestionForm.bind(this));
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