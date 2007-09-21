Event.observe(window, 'load', smw_links_callme);

var createLinkList = function() {
	sajax_do_call('getLinks', [wgArticleId], addLinks);
}

function smw_links_callme(){
	if(wgAction == "edit"
	   && stb_control.isToolbarAvailable()){
		editcontainer = stb_control.createDivContainer(EDITCONTAINER, 1);
		createLinkList();
	}
}

function addLinks(request){
	if (request.responseText!=''){
		editcontainer.setContent(request.responseText);
		editcontainer.contentChanged();
	}
}

function filter (term, _id, cellNr){
	var suche = term.value.toLowerCase();
	var table = document.getElementById(_id);
	var ele;
	for (var r = 0; r < table.rows.length; r++){
		ele = table.rows[r].cells[cellNr].innerHTML.replace(/<[^>]+>/g,"");
		if (ele.toLowerCase().indexOf(suche)>=0 )
			table.rows[r].style.display = '';
		else table.rows[r].style.display = 'none';
	}
}

function update(){
	$("linkfilter").value = "";
	filter($("linkfilter"), "linktable", 0);
}

function linklog(link, action){
	/*STARTLOG*/
	if(window.smwhgLogger){
		var logmsg = "Opened Page " + link + " with action " + action;
	    smwhgLogger.log(logmsg,"info","link_opened");
	}
	/*ENDLOG*/
	return true;
}