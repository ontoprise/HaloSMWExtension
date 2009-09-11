<?php
/**
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Link dialog window.
 */

// fetch the Use Web Service GUI by calling the URL http://host/wiki/Special:UseWebService
global $wgServer, $wgScript;
if( !defined( 'MEDIAWIKI' ) ) define('MEDIAWIKI', "fake");
if (is_null($wgServer)) {
	$wgServer = "http";
	// check for SSL
	if (isset($_SERVER['SCRIPT_URI']) && strtolower(substr($_SERVER['SCRIPT_URI'], 4, 1)) == "s" || 
    	isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
		$wgServer.= "s";
	// add hostname and build correct path, assuming that the QI can be accessed as a special page
	$wgServer.= "://".$_SERVER['HTTP_HOST'];
}
if (is_null($wgScript)) {
    $wgScript = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], 'extensions/FCKeditor/plugins/')).
       "index.php";
}
// now actually include the file with the ajax function
$UWSAjaxFuncFile = '../../../../DataImport/specials/WebServices/SMW_UseWebServiceAjaxAccess.php';
if (!file_exists($UWSAjaxFuncFile))
	dieNice("The Data Import extension seems not to be installed. Please install this extension to be able to add Web Service calls.");
include_once($UWSAjaxFuncFile);

// save the source code of the above URL in $page 
$page = smwf_uws_getPage();

// check if an error occured, the the returned string starts with "Error: " 
if (substr($page, 0, 7) == "Error: ") {
	$page = "<h2>Error</h2>".substr($page, 6);
	$page = getErrorPage($page);
	// add the FCK specific Javascript above the body tag, set error flag
	// that prevents to display the Ok button
	$newPage= str_replace('<body', jsData(true)."\n<body", $page);
}
else {
	// add the FCK specific Javascript above the body tag
	$newPage= str_replace('<body', jsData(false)."\n<body", $page);

	// suround UWS code by div with id = divUWSGui for managing the tabs
	$newPage= str_replace('<body', '<div id="divUWSGui">'."\n<body", $newPage);
	$newPage= str_replace('</body>', "</div>\n</body>", $newPage);
}

// output the page
echo $newPage;


/**
 * return Javacript, that needs to be placed in the header and that handles the communication
 * with the FCKEditor
 *  
 * @param  boolean error default false
 * @return string FCK javascript that must be inserted before the <body>
 */
function jsData($error = false) {
	return '<script type="text/javascript">

var oEditor		= window.parent.InnerDialogLoaded() ;
var FCK			= oEditor.FCK ;
var FCKLang		= oEditor.FCKLang ;
var FCKConfig	= oEditor.FCKConfig ;
var FCKRegexLib	= oEditor.FCKRegexLib ;
var FCKTools	= oEditor.FCKTools ;

document.write( \'<script src="\' + FCKConfig.BasePath + \'dialog/common/fck_dialog_common.js" type="text/javascript"><\/script>\' ) ;

	</script>
	<script type="text/javascript">


// Get the selected query embed (if available).
var oFakeImage = FCK.Selection.GetSelectedElement();
var oWSSpan;

if ( oFakeImage ) {
	//if ( oFakeImage.tagName == \'IMG\' && oFakeImage.getAttribute(\'_fck_mw_askquery\') )
	//	oWSSpan = FCK.GetRealElement( oFakeImage ) ;
	//else
		oFakeImage = null ;
}

window.onload = function() {
	// Translate the dialog box texts.
	oEditor.FCKLanguageManager.TranslatePage(document) ;

	// load selected query if any
	LoadSelection();
}

function LoadSelection() {
	
}

// the OK button was hit
function Ok(enterHit) {
	/*op-patch|SR|2009-06-12|FCK mediawiki plugin QI popup|param enterHit added|start*/
	// with the patch in fckdialog.html pevent that hitting ENTER closes the window 
	if (enterHit) return false;
	
	if ( FCK.EditMode == 0 ) {
		if ( !oWSSpan ) {
			oWSSpan = FCK.EditorDocument.createElement( \'SPAN\' ) ;
			oWSSpan.className = \'fck_mw_template\' ;
		}
		oWSSpan.innerHTML = useWSSpecial.createWSSyn();
	
		if ( !oFakeImage ) {
			//todo: add my own image
			oFakeImage	= oEditor.FCKDocumentProcessor_CreateFakeImage( \'FCK__MWTemplate\', oWSSpan ) ;
			oFakeImage.setAttribute(\'_fck_mw_template\', \'true\', 0 ) ;
			oFakeImage	= FCK.InsertElement( oFakeImage ) ;
		}
	} else {
		var start = FCK.EditingArea.Textarea.selectionStart;
		var end = FCK.EditingArea.Textarea.selectionEnd;
		var content = FCK.EditingArea.Textarea.value;
		content = content.substr(0, start) + useWSSpecial.createWSSyn()
			 + content.substr(end);
		FCK.EditingArea.Textarea.value = content;
	}	
	
	return true;
}
</script>';
}

/**
 * send a nice message to the user, in case the Query Interface was not
 * called successfully.
 */
function getErrorPage($msg) {
  return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
    <html>
      <head>
	    <title>Query Interface</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
      </head>
      <body>'.$msg.'</body>
    </html>';
}
?>
