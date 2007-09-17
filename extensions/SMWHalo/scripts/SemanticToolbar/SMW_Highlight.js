Event.observe(window, 'load', callme);

function callme(){
	Event.observe(document.getElementById("bodyContent"), 'mouseup', mouseUp);
}

function getSelText()
{
	var txt = '';
	if (window.getSelection){
		txt = window.getSelection();
	}
	else if (document.getSelection) {
		txt = document.getSelection();
	}
	else if (document.selection) {
		txt = document.selection.createRange().text;
	}
	return txt;
}

function mouseUp(){
	var txt = getSelText();
	if(txt != ''){
		sajax_do_call('checkSelection', [wgArticleId, txt], respondToSelection);
	}
}

function respondToSelection(request){

	var results = request.responseText.split("::");
	if(results[0] == 1){
		if (results[1] == "attribute"){
			alert("Attribute:\nvalue -> "+results[2] +"\nunit -> "+results[3]);
		}
		else if (results[1] == "relation"){
			alert("Relation:\nname -> "+results[2]);
		}
	}
	else {
		//nothing found
		alert("Couldn't determine '"+results[2]+"'");
	}
		
}