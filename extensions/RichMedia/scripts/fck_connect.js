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

/*
  This file tries to combine the RichMedia extension with the FCK Editor.
  If an media file (image, pdf or any other) is uploaded, and this dialog
  was caled within the FCK Editor, then a link or image must be added in
  the editor window. This tries this function to achieve.
  If the RichMedia Upload was not called within the FCK editor, the variable
  oEditor will not contain an existing FCK editors instance. Then the iframe
  will closes itself and the original page will be reloaded. 
 */

window.saveRichMediaData = function(mediaTitle, mediaLink, formInputID) {
	// get FCK editor instance
	var inFormEdit = false;
	var richEditorType;
	// just return if a formInputID is set
	// this means that the upload was started out of an uploadable form field
	if ( formInputID !== '' ) {
		return;
	}
	try {
		// Semantic Forms: either we are in formedit or we add/edit a page via Special:AddData/EditData/CreateForm/FormEdit
		if (window.top.wgAction == "formedit" || window.top.wgPageName == 'Special:AddData' 
			|| window.top.wgPageName == 'Special:EditData' || window.top.wgPageName == 'Special:FormEdit'
				|| window.top.wgPageName == 'Special:CreateForm') {
			oEditor = window.top.FCKeditorAPI.GetInstance('free_text');
		} else {
			// normal WYSIWYG edit
			oEditor = window.top.FCKeditorAPI.GetInstance('wpTextbox1');
		}
		richEditorType = 'fck';
	} catch(err) {
		// try CKEditor
		try {
			oEditor = window.top.wgCKeditorInstance;
			richEditorType = 'cke';
		} catch(err) {
			return;
		}
	} finally {
		if (richEditorType == 'fck' && typeof(oEditor) !== 'undefined') { // FCK
			document.write( '<script src="' + oEditor.Config['BasePath'] + 'dialog/common/fck_dialog_common.js" type="text/javascript"><\/script>' ) ;

			var oElement;	// selected element, if any
			var oNew;		// new created element by upload

			// check if an image is selected
			oElement = oEditor.Selection.GetSelectedElement();
			if ( oElement && oElement.tagName == 'IMG' && oElement.getAttribute('alt').substring(0, 6) == "Image:") {
				// ok
			}
			else { // check if we are inside a link, replace the link, even if it is not a media link
				oElement = oEditor.Selection.MoveToAncestorNode( 'A' );
				if (oElement) {
					oEditor.Selection.SelectNode( oElement );
				} else {
					oElement = null;
				}
			}

			// create new Element from uploaded file
			var ns = mediaTitle.substring(0, mediaTitle.indexOf(':'));
			if (ns == "Image") { // create an image for all images
				oNew = oEditor.EditorDocument.createElement( 'IMG' );
				oNew.setAttribute('alt', mediaTitle);
				oNew.setAttribute('_fck_mw_filename', mediaTitle.replace(/^[^:].*:(.*)/, '\$1').replace('_', ' '));
				oNew.setAttribute('src', mediaLink);
				oNew.setAttribute('_fcksavedurl', mediaLink);
			}
			else { // other media (ns != Image:) will be created as a link
				//var basename = mediaTitle.replace(/^[^:].*:(.*)/, '\$1');
				var basename = mediaTitle.substring(mediaTitle.indexOf(':') + 1);
				var ns = mediaTitle.substring(0, mediaTitle.indexOf(':'))
				oNew = oEditor.EditorDocument.createElement( 'A' );
				oNew.className = 'internal';
				oNew.setAttribute('title', basename);
				oNew.setAttribute('_fck_mw_type', ns);
				oNew.setAttribute('_fck_mw_filename', basename);
				oNew.setAttribute('_fcksavedurl', mediaTitle);
				oNew.setAttribute('href', mediaTitle);
				oNew.innerHTML = mediaTitle;
			}

			// if an element was selected and is from the type Image or Media, then oElement is set
			// and the selected element will be replaced by the new created element in the editor content
			if ( oElement ) {
				oElement.parentNode.insertBefore( oNew, oElement ) ;
				oElement.parentNode.removeChild( oElement ) ;
			}
			// otherwise insert new element into editor content
			else { 
				oNew = oEditor.InsertElement( oNew ) ;
			}

			return true;
		}
		if ( richEditorType == 'cke' && typeof(oEditor) !== 'undefined' ) { // CK
			// CKeditor
			// check if an image is selected
			var sel = oEditor.getSelection();
			if ( sel ) {
				oElement = sel.getSelectedElement();
			}
			if ( oElement && oElement.is('img') &&  !oElement.getAttribute( '_cke_realelement' )) {
				// ok
			}
			else { // check if we are inside a link, replace the link, even if it is not a media link
				if ( sel ) {
					oElement = sel.getStartElement();
				}
				if ( oElement && oElement.is( 'a' ) ) {
					sel.selectElement( oElement );
				} else {
					oElement = null;
				}
			}

			// create new Element from uploaded file
			var ns = mediaTitle.substring(0, mediaTitle.indexOf(':'));
			if (ns == "File" || ns == "Image") { // create an image for all images
				oNew = oEditor.document.createElement( 'img' );
				oNew.setAttribute('alt', mediaTitle);
				oNew.setAttribute('_cke_mw_filename', mediaTitle.replace(/^[^:].*:(.*)/, '\$1').replace('_', ' '));
				oNew.setAttribute('src', mediaLink);
			} else {
				// other media (ns != File:) will be created as a link
				//var basename = mediaTitle.replace(/^[^:].*:(.*)/, '\$1');
				var basename = mediaTitle.substring(mediaTitle.indexOf(':') + 1);
				var ns = mediaTitle.substring(0, mediaTitle.indexOf(':'))
				oNew = oEditor.document.createElement( 'a' );
				oNew.addClass('internal');
				oNew.setAttribute('title', mediaTitle);
				oNew.setAttribute('_cke_mw_type', ns);
				oNew.setAttribute('_cke_mw_filename', basename);
				oNew.setAttribute('href', mediaTitle);
				oNew.setAttribute('_cke_saved_href', basename);
				oNew.$.innerHTML = mediaTitle;
			}

			// if an element was selected and is from the type Image or Media, then oElement is set
			// and the selected element will be replaced by the new created element in the editor content
			if ( oElement ) {
				oNew.replace( oElement );
				oEditor.getSelection().selectElement( oNew );
			} else {
				// otherwise insert new element into editor content
				oEditor.insertElement( oNew ) ;
			}

			return true;
		}
	}
}
