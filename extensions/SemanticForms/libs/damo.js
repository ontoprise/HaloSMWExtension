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


/*
var DaMOPage = Class.create();

DaMOPage.prototype = {
	initialize: function() {
	},
}
 // ----- Classes -----------

var daMOPage = new DaMOPage();*/

function damo_getInputs(){
	//validate the form fields!
	var error = validate_all();

	if (!error) {
		return false;
	}
	//SemanticForms are always in a table called 'createbox'
	var semanticFormTable = document.getElementsByName('createbox');
	//get all <tr> in this table
	var trs = semanticFormTable[0].getElementsByTagName('tr');
	
	//2-dim array for saving the SemanticForm
	var sf_array = new Array(2);
	
	var form = document.forms['upload_form'];
	
	for (var i = 0, n = trs.length; i < n; i++) {
		var tr = trs[i]; 
		
		if(tr.nodeType == 1) {
			if ( tr.firstChild.nodeValue ){
				//get the input field
				var input = tr.getElementsByTagName('input');
						
				//save id, name and value of this field
				sf_array[i] = new Array(3);						
				sf_array[i][0] = input[0].id;
				sf_array[i][1] = input[0].name;
				sf_array[i][2] = input[0].value;
				
				//var split_form_field = sf_array[i][1].replace(/]/g,"").split("[");
				
				var el = document.createElement("input");
   				el.type = "hidden";
   				el.name = sf_array[i][1];
   				el.value = sf_array[i][2];
   				form.appendChild(el);
							
			}	
		}	
	}
		
	//simulate a submitted SF
   	var el = document.createElement("input");
   	el.type = "hidden";
   	el.name = "wpSave";
   	el.value = "true";
   	form.appendChild(el);
				
	//set this because the source is a query, not a page!!!
	var el = document.createElement("input");
   	el.type = "hidden";
   	el.name = "query";
   	el.value = "true";
   	form.appendChild(el);
 
	//submit the upload form
	document.upload_form.wpUpload.click();
}