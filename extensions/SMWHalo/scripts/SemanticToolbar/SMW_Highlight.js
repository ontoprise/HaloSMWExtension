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