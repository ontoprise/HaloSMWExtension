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

// fetch the Query Interface by calling the URL http://host/wiki/Special:QueryInterface
// This is implemented in a file at the SMW Query Interface as a ajax function. The best
// solution would be just to do an Ajax call and receive the HTML of the Query Interface.
// However this would be one call to the ajax function and another call to the QI URL.
// Before we just included the ajax function and fetched the page with one HTTP request
// only but this doesn't work well, when the language is different than en. Therefore we
// must also do an ajax call to the function for loading the QI.
// FIXME: make one http request only.

$wgServer = "http";
// check for SSL
if (isset($_SERVER['SCRIPT_URI']) && strtolower(substr($_SERVER['SCRIPT_URI'], 4, 1)) == "s" || 
    isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
    $wgServer.= "s";
// add hostname and build correct path, assuming that the QI can be accessed as a special page
$wgServer.= "://".$_SERVER['HTTP_HOST'];

$wgScript = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], 'extensions/FCKeditor/plugins/'))."index.php";

// check if the file with the ajax function exisis
$QIAjaxFuncFile = '../../../../SMWHalo/specials/SMWQueryInterface/SMW_QIAjaxAccess.php';
if (!file_exists($QIAjaxFuncFile))
	$page = "Error: SMWHalo seems not to be installed. Please install the SMWHalo extension to be able to use the Query Interface.";
else {
    include_once('../../../FCKeditorHttpRequest.php');
    $params = 'action=ajax&rs=smwf_qi_getPage';
    list ($httpErr, $page) = fckHttpRequest($wgServer, $wgScript, $params);

    if ($page === false || ($httpErr != 200 && $httpErr > 0))
        $page = "Error: SMWHalo seems not to be installed. Please install the SMWHalo extension to be able to use the Query Interface.<br/>HTTP Error code $httpErr";
}

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
	$newPage= str_replace('<body', jsData()."\n<body", $page);

	// suround QI code by div with id = divQiGui for managing the tabs
	$newPage= preg_replace('/<body[^>]*>/', '$0<div id="divQiGui">', $newPage);
	$newPage= str_replace('</body>', "</div>\n</body>", $newPage);

	// add div container for raw query source code at the end
	$newPage= str_replace('</body>', getDivQiSrc()."\n</body>", $newPage);
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

'.($error
? ''  // dont set any tabs on error
: '
// Set the dialog tabs.
window.parent.AddTab( \'GUI\', FCKLang.wikiDlgQueryInterface || \'Query Interface\' ) ;
window.parent.AddTab( \'SRC\', FCKLang.wikiDlgQIsource ||  \'Query sourcecode\' ) ;

function OnDialogTabChange( tabCode ) {
	ShowE(\'divQiGui\' , ( tabCode == \'GUI\' ) ) ;
	ShowE(\'divQiSrc\' , ( tabCode == \'SRC\' ) ) ;
	if (tabCode == \'GUI\') {
		initialize_qi_from_querystring( GetE(\'xAskQueryRaw\').value.replace(/fckLR/g,\'\').replace( /&quot;/g, \'"\'));
	}
	else {
		ask = qihelper.getFullParserAsk();
   		ask = ask.replace(/\]\]\[\[/g, "]]\n[[");
		ask = ask.replace(/>\[\[/g, ">\n[[");
		ask = ask.replace(/\]\]</g, "]]\n<");
    	ask = ask.replace(/([^\|]{1})\|{1}(?!\|)/g, "$1\n|");
    	GetE(\'xAskQueryRaw\').value = ask;	
	}
}
').'

// Get the selected query embed (if available).
var oFakeImage = FCK.Selection.GetSelectedElement();
var oAskSpan;

if ( oFakeImage ) {
	if ( oFakeImage.tagName == \'IMG\' && oFakeImage.getAttribute(\'_fck_mw_askquery\') )
		oAskSpan = FCK.GetRealElement( oFakeImage ) ;
	else
		oFakeImage = null ;
}

window.onload = function() {
	// Translate the dialog box texts.
	oEditor.FCKLanguageManager.TranslatePage(document) ;

	// Activate the "OK" button.
	window.parent.SetOkButton( '.($error ? 'false' : 'true').' ) ;
	window.parent.SetAutoSize( false ) ;
	
	// load selected query if any
	LoadSelection();
}

function LoadSelection() {
	
	if ( !oAskSpan ) return ;

	var inputText = FCKTools.HTMLDecode(oAskSpan.innerHTML);
	initialize_qi_from_querystring(inputText.replace(/fckLR/g,\'\').replace( /&quot;/g, \'"\'));
	GetE(\'xAskQueryRaw\').value = inputText.replace(/fckLR/g,\'\r\n\').replace( /&quot;/g, \'"\');
	//window.parent.SetSelectedTab(\'SRC\');
}

function InsertDataInTextarea(ask) {
        var myArea = FCK.EditingArea.Textarea;

        if ( oEditor.FCKBrowserInfo.IsIE ) {
            if (document.selection) {
                // The current selection
                var range = document.selection.createRange();
                // Well use this as a "dummy"
                var stored_range = range.duplicate();
                // Select all text
                stored_range.moveToElementText( myArea );
                // Now move "dummy" end point to end point of original range
                stored_range.setEndPoint( \'EndToEnd\', range );
                // Now we can calculate start and end points
                myArea.selectionStart = stored_range.text.length - range.text.length;
            }
        }
        if (myArea.selectionStart != undefined) {
            var before = myArea.value.substr(0, myArea.selectionStart);
            var after = myArea.value.substr(myArea.selectionStart);
            myArea.value = before + ask + after;
        }
}

// the OK button was hit
function Ok(enterHit) {
	var ask;

	/*op-patch|SR|2009-06-12|FCK mediawiki plugin QI popup|param enterHit added|start*/
	// with the patch in fckdialog.html pevent that hitting ENTER closes the window 
	if (enterHit) return false;

	// use ask query from field in source code tab if active
	if (GetE(\'divQiSrc\').style.display.indexOf("none") == -1) {
		ask = FCKTools.HTMLEncode(GetE(\'xAskQueryRaw\').value.Trim().replace(/(\r\n|\n)/g, \'fckLR\')).replace( /"/g, \'&quot;\' ) ;

		if ( !( /^{{#ask:[\s\S]+}}$/.test( ask ) ) ) {
			alert( FCKLang.wikiDlgQIsyntaxErr || \'Ask query must start with {{#ask: and end with }}. Please check it.\' ) ;
			return false ;
		}
	}
	// the GUI tab is active
	else {
		ask = qihelper.getFullParserAsk();
   		ask = ask.replace(/\]\]\[\[/g, "]]\n[[");
		ask = ask.replace(/>\[\[/g, ">\n[[");
		ask = ask.replace(/\]\]</g, "]]\n<");
    	ask = ask.replace(/([^\|]{1})\|{1}(?!\|)/g, "$1\n|");
	}

	if ( !oAskSpan ) {
                // if we are in source code mode, insert the query at the
                // current cursor position
                if (FCK.EditMode == oEditor.FCK_EDITMODE_SOURCE) {
                     InsertDataInTextarea(ask);
                     return true;
                }
		oAskSpan = FCK.EditorDocument.createElement( \'SPAN\' ) ;
		oAskSpan.className = \'fck_mw_askquery\' ;
	}
	
	oAskSpan.innerHTML = ask.replace(/\n/g, \'fckLR\');

	if ( !oFakeImage ) {
		oFakeImage	= oEditor.FCKDocumentProcessor_CreateFakeImage( \'FCK__SMWask\', oAskSpan ) ;
		oFakeImage.setAttribute(\'_fck_mw_askquery\', \'true\', 0 ) ;
		oFakeImage	= FCK.InsertElement( oFakeImage ) ;
	}

	return true;
}
</script>';
}

/**
 * return div element for the tab "Query source code". This is where the plain
 * wiki text of the query is displayed if a query is edited. 
 */
function getDivQiSrc() {
	return '<div id="divQiSrc" style="display: none;">
		<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
			<tr>
				<td>
					<span fcklang="wikiDlgQIrawdef">Ask Query raw definition (from {{#ask: to }})</span><br />
				</td>
			</tr>
			<tr>
				<td height="100%">
					<textarea id="xAskQueryRaw" style="width: 100%; height: 100%; font-family: Monospace"
						cols="50" rows="10" wrap="off"></textarea>
				</td>
			</tr>
		</table>
	</div>
	';
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
