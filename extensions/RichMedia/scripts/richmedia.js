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

var RichMediaPage = Class.create({
	initialize: function() {
		// do nothing special.
	},
		
	/**
	 * do the upload.
	 */
	doUpload: function() {
		
		var sForm = $$('form.createbox')[0]; //array
		var destForm = $('upload');

		//merge SemanticForm into UploadForm
		var result = richMediaPage.mergeFormsToForm([sForm], destForm);

		var el = new Element('input', {
	   				'type' : 'hidden', 
	   				'name' : 'query', 
	   				'value' : 'true'} )
	   	destForm.appendChild(el);

	   	var el = new Element('input', {
	   				'type' : 'hidden', 
	   				'name' : 'action', 
	   				'value' : 'submit'} )
	   	destForm.appendChild(el);
	   	
		destForm.submit();
	},
	
	/**
	 * A warning appeared and the user pressed 'Re-upload' or 'Save file'.
	 * Copy the SF
	 */
	copyToUploadWarning: function() {
		var sForm = $$('form.createbox')[0]; //array
		var destForm = $('uploadwarning');
		
		var result = richMediaPage.mergeFormsToForm([sForm], destForm);
	},
		
	/**
	* merges an array of source forms entries hidden into one destination form 
	*/ 
	mergeFormsToForm: function(sourceForms, destForm) {  
  		var clone; 
  		sourceForms.each(function(sourceForms) { 
			$(sourceForms).getElements().each(function(formControl) { 
				clone = formControl.cloneNode(true); 
				clone.id = '';
				clone.hide();  
				destForm.appendChild(clone);
				//TODO: create a valid field for dates (day, month, year) -> date
				
	 		}); 
  		}); 
		return true;
	},
	
	/*
	 * Adds the destination file to the link
	 */
	addWpDestFile: function() {
			var myWpDestFile = $('myWpDestFile').value;
			var myLink = $('link_id');
			var myHref = myLink.href;
			myLink.href = myHref+"&wpDestFile="+myWpDestFile;
			fb.loadAnchor(myLink);
			return true;
	}
});

//------ Classes -----------

var richMediaPage = new RichMediaPage();