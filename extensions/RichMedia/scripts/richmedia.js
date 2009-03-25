/*  Copyright 2008-2009, ontoprise GmbH
*   Author: Benjamin Langguth
*   This file is part of the Data Import-Extension.
*
*   The Data Import-Extension is free software; you can redistribute it and/or modify
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
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

function rm_getInputs(){
	//validate the form fields!
	var error = validate_all();

	if (!error) {
		return false;
	}
	//SemanticForms are always in a table called 'createbox'
	var semanticFormTable = document.getElementsByName('createbox');
	//get all <input...> in this table
	var inputs = semanticFormTable[0].getElementsByTagName('input');
	
	//2-dim array for saving the SemanticForm
	var sf_array = new Array(2);
	
	var form = document.forms['upload_form'];
	
	for (var i = 0, n = inputs.length; i < n; i++) {
		var input = inputs[i]; 
		
		if(input.nodeType == 1) {
			var el = document.createElement("input");
   			el.type = "hidden";
   			el.name = input.name;
   			el.value = input.value;
   			form.appendChild(el);
		}	
	}
	//get all <select...> in this table
	var selects = semanticFormTable[0].getElementsByTagName('select');
				
	for (var i = 0, n = selects.length; i < n; i++) {
		var select = selects[i]; 
		
		if(select.nodeType == 1) {
			var el = document.createElement("input");
   			el.type = "hidden";
   			el.name = select.name;
   			el.value = select.value;
   			form.appendChild(el);
		}	
	}
	//set this because the source is a query, not a page!!!
	var el = document.createElement("input");
   	el.type = "hidden";
   	el.name = "query";
   	el.value = "true";
   	form.appendChild(el); 	
   	
   	
   	
	//submit the upload form
	document.upload_form.wpUpload.click();
	
	//document.my_newlink.click();
	//document.close();
    //document.open();
    //document.write('<P>');
}