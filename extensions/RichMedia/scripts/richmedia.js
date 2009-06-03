/*  Copyright 2008-2009, ontoprise GmbH
*   Author: Benjamin Langguth
*   This file is part of the Rich Media-Extension.
*
*   The Rich Media-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Rich Media-Extension is distributed in the hope that it will be useful,
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
		
		//validate the form fields!
		var error = validate_all();

		if (!error) {
			return false;
		}
		
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
		return true;
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
  		sourceForms.each(function(sourceForm) { 
			sourceForm.getElements().each(function(formControl) { 
				clone = formControl.cloneNode(true);
				
				/* if we find an id that contains the string 'day'
				 * we search for the according year and month, build up a new date string 
				 * and append this to the destForm
				 */
				if (clone.id.indexOf('day') > -1) {
					var date_id = clone.id.replace('_day', ''); 
					var dateStringValue = $(date_id + '_year').value + 
						'/' + $(date_id + '_month').value + '/' + clone.value;
					var dateStringName = clone.name.replace('[day]', '');
					var el = new Element('input', {
						'type' : 'hidden',
						'name' : dateStringName,
						'value' : dateStringValue } )
						destForm.appendChild(el);
				}
				//We do nothing with 'year' and 'month' because it's already done in 'day'
				else if ( (clone.id.indexOf('year') > -1 ) || ( clone.id.indexOf('month') > -1) )  {
					//do nothing
				}
				// just clone and hide everything else here 
				else {
					clone.id = '';
					clone.hide();  
					destForm.appendChild(clone);
				}
	 		}); 
  		});
		return true;
	},
	
	/*
	 * Adds the destination file to the link
	 */
	addWpDestFile: function() {
			//var myWpDestFile = $('myWpDestFile').value;
			var myLink = $('link_id');
			//var myHref = myLink.href;
			//myLink.href = myHref+"&wpDestFile="+myWpDestFile;
			fb.loadAnchor(myLink);
			return true;
	}
});

//------ Classes -----------

var richMediaPage = new RichMediaPage();