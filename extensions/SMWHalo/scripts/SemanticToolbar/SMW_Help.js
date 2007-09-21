Event.observe(window, 'load', smw_help_callme);

var initHelp = function(){
	var ns = wgNamespaceNumber==0?"Main":wgCanonicalNamespace ;
	if (wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "Search"){
		ns = "Search";
	}
	sajax_do_call('smwfGetHelp', [ns , wgAction], displayHelp);
}

function smw_help_callme(){
	if((wgAction == "edit"
	    || wgCanonicalSpecialPageName == "Search")
	   && stb_control.isToolbarAvailable()){
		helpcontainer = stb_control.createDivContainer(HELPCONTAINER, 0);
		helpcontainer.setHeadline('<img src="'+wgScriptPath+'/extensions/SMWHalo/skins/help.gif"/> Help');
		initHelp();
	}
}

function displayHelp(request){
	if (request.responseText!=''){
		helpcontainer.setContent(request.responseText);
	}
	else {
		helpcontainer.setHeadline = ' ';
	}
	helpcontainer.contentChanged();
}

function askQuestion(){
	$('questionLoaderIcon').show();
	var ns = wgNamespaceNumber==0?"Main":wgCanonicalNamespace ;
	if (wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "Search"){
		ns = "Search";
	}
	sajax_do_call('smwfAskQuestion', [ns , wgAction, $('question').value, wgUserName, wgTitle  ], hideQuestionForm);
}

function hideQuestionForm(request){
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
	if(smwhgLogger){
		var logmsg = "Opened Help Page " + question + " with action " + action;
	    smwhgLogger.log(logmsg,"info","help_clickedtopic");
	}
	/*ENDLOG*/
	return true;
}