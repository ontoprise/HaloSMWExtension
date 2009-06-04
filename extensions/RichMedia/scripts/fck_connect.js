/*
  This file tries to combine the RichMedia extension with the FCK Editor.
  If an media file (image, pdf or any other) is uploaded, and this dialog
  was caled within the FCK Editor, then a link or image must be added in
  the editor window. This tries this function to achieve.
  If the RichMedia Upload was not called within the FCK editor, the variable
  oEditor will not contain an existing FCK editors instance. Then the iframe
  will closes itself and the original page will be reloaded. 
*/

function saveRichMediaData(mediaTitle, mediaLink) {
	var oEditor = window.parent.FCKeditorAPI.GetInstance('wpTextbox1');
	
	// we didn't come from the FCK Editor, then reload the main page and quit.
	if (! oEditor) {
		parent.fb.loadPageOnClose ='self';
		return;
	}

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
		if (oElement)
			oEditor.Selection.SelectNode( oElement );
		else oElement = null;
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
	else { // other media (ns = Media:) will be created as a link
		var basename = mediaTitle.replace(/^[^:].*:(.*)/, '\$1');
		oNew = oEditor.EditorDocument.createElement( 'A' );
		oNew.className = 'internal';
		oNew.setAttribute('title', basename);
		oNew.setAttribute('_fck_mw_type', 'media');
		oNew.setAttribute('_fck_mw_filename', basename);
		oNew.setAttribute('_fcksavedurl', basename);
		oNew.setAttribute('href', basename);
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