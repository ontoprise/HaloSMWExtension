/*  Copyright 2011, ontoprise GmbH
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

/**
 * The RichMediaPage "class"
 * 
 */
function RichMediaPage() {
	
	// indicates if the current form is already submitted
	this.formSubmitted = false;

	/**
	 * Handles the upload process.#
	 * 
	 * @param: destFormName 
	 *		jquery selector string
	 */
	this.doUpload = function( destFormName ) {
		var	error,
			sForm,
			destForm,
			el;

		if( this.formSubmitted ) {
			return true;
		}
		if( typeof destFormName === 'undefined' ){
			destFormName = '#mw-upload-form';
		}
		// validate the form fields!
		error = validateAll();
		if ( !error ) {
			return false;
		}
		sForm = jQuery( '.createbox' );
		destForm = jQuery( destFormName );
		//merge SemanticForm into UploadForm
		this.mergeFormsToForm( [sForm], destForm );

		el = document.createElement( 'input' );
		jQuery( el ).attr( 'type', 'hidden' );
		jQuery( el ).attr( 'name', 'query');
		jQuery( el ).val( 'true');
		destForm.append( jQuery( el ) );

		el = document.createElement( 'input' );
		jQuery( el ).attr( 'type', 'hidden' );
		jQuery( el ).attr( 'name', 'action');
		jQuery( el ).val( 'submit');
		destForm.append( jQuery( el ) );

		el = document.createElement( 'input' );
		jQuery( el ).attr( 'type', 'hidden' );
		jQuery( el ).attr( 'name', 'wpUpload');
		jQuery( el ).val( 'true');
		destForm.append( jQuery( el ) );

		destForm.attr( "method", "POST" );
		this.formSubmitted = true;
		destForm.submit();
		return true;
	};
	
	/**
	* Merges an array of jQuery objects unvisible into one destination form.
	* 
	* @param: sourceForms
	*		array of jQuery objects
	* @param: destForm
	*		jQuery object as destination form
	*/ 
	this.mergeFormsToForm = function( sourceForms, destForm ) {
		var	clonedFormContent,
			el;
		for( var i = 0; i < sourceForms.length; i++ ) {
			clonedFormContent = jQuery( '*', sourceForms[i] ).filter(":input");
			jQuery.each( clonedFormContent, function( j, el ) {
				var jQEl = jQuery( el )
				var clonedElement = jQEl.clone();
				if ( el.type === 'textarea' ) { 
					/* Fix for Firefox (values of textareas are just ignored) */ 
					clonedElement.val( jQEl.val() );
				}
				// Just clone and hide everything
				// Fix: #10678; just clone nodes which have a value 
				if ( clonedElement.val().length > 0 ) {
					clonedElement.removeAttr( 'id' );
					clonedElement.hide();
					destForm.append( clonedElement );
				}
			});
		}
		return true;
	};

	/**
	 * A warning appeared and the user pressed 'Re-upload' or 'Save file'.
	 * Copy the SF
	 */
	this.copyToUploadWarning = function() {
		var	sForm = jQuery( '.form.createbox' ), //array
			destForm = jQuery( '#uploadwarning' );
		richMediaPage.mergeFormsToForm( [sForm], destForm );
	};
}

// Set global variable for accessing Rich Media functions
var richMediaPage;

// Initialize Rich Media functions if page is loaded
jQuery( document ).ready(
	function() {
		richMediaPage = new RichMediaPage();
	}
);