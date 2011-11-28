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
 * @file
 * @ingroup SMWHaloSemanticToolbar
 * @author Thomas Schweitzer
 */

var createLinkList = function() {
	sajax_do_call('smwf_tb_getLinks', [wgArticleId], addLinks);
}
    
function smw_links_callme(){
	var url = location.href;
	var redlink = url.indexOf('redlink=1');
	if (redlink !== -1) {
		// This is a redlink page => don't show the link container
		return;
	}
	if( (wgAction == "edit" || wgAction == 'formedit' || wgAction == 'submit' ||
             wgCanonicalSpecialPageName == 'AddData' || wgCanonicalSpecialPageName == 'EditData' ||
             wgCanonicalSpecialPageName == 'FormEdit')
	   && (typeof stb_control != 'undefined' && stb_control.isToolbarAvailable())){
		var _linksHaveBeenAdded = false;
		editcontainer = stb_control.createDivContainer(EDITCONTAINER, 1);
		
		// KK: checks if link tab is open and ask for links if necessary
		var stbpreftab = GeneralBrowserTools.getCookie("stbpreftab")
		if (stbpreftab) {
			if (stbpreftab.split(",")[0] == '0') {
				createLinkList();
                _linksHaveBeenAdded = true;
			}
		}
		
		// KK: called when the user switches the tab.
		editcontainer.showTabEvent = function(tabnum) {
			if (tabnum == 1 && !_linksHaveBeenAdded) {
				createLinkList();
				_linksHaveBeenAdded = true;
			}
		}
		
	}
}

function addLinks(request){
	if (request.responseText!=''){
		editcontainer.setContent(request.responseText);
		editcontainer.contentChanged();
	} else {
		editcontainer.setContent("<p>There are no links on this page.</p>");
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

//Event.observe(window, 'load', smw_links_callme);
stb_control.registerToolbox(smw_links_callme);

