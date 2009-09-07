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

if (typeof FCKeditor == 'undefined')
    Event.observe(window, 'load', smw_links_callme);


var createLinkList = function() {
	sajax_do_call('smwf_tb_getLinks', [wgArticleId], addLinks);
}


    
    
function smw_links_callme(){
	if( (wgAction == "edit" || wgAction == 'formedit' || wgAction == 'submit' )
	   && stb_control.isToolbarAvailable()){
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