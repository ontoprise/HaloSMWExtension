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
$url = "http";
// check for SSL
if (isset($_SERVER['SCRIPT_URI']) && strtolower(substr($_SERVER['SCRIPT_URI'], 4, 1)) == "s" || 
    isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
	$url.= "s";
// add hostname and build correct path, assuming that the QI can be accessed as a special page
$url.= "://".$_SERVER['HTTP_HOST'].
       substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], 'extensions/FCKeditor/plugins/')).
       "index.php/Special:QueryInterface";
// save the source code of the above URL in $page 
$page = "";

$fp = fopen($url, "r");
// this happens at any error (also if the URL can be called but a 404 is returned)
if (!$fp) dieNice("SMWHalo seems not to be installed. Please install the SMWHalo extension to be able to use the Query Interface.");

// fetch data from URL into $page
while ($data = fgets($fp, 4096))
  $page .= $data;
fclose($fp);

// create the new source code, by removing the wiki stuff,
// keep the header (because of all css and javascripts) and the main content part only
$newPage = "";
copyData($page, $newPage, '<body');
$newPage.= '<body style="background-image:none; background-color: #ffffff;">';
copyData($page, $newPage, "<!-- start content -->", false);
copyData($page, $newPage, "<!-- end content -->");
$newPage.="</body></html>";

// remove the Switch to Semantic Notification button, incase it's there
$newPage= preg_replace('/<button id="qi-insert-notification-btn"([^>]*)>(.*?)<\/button>/m', '', $newPage);

// add the FCK specific Javascript above the body tag
$newPage= str_replace('<body', jsData()."\n<body", $newPage);

// suround QI code by div with id = divQiGui for managing the tabs
$newPage= str_replace('<body', '<div id="divQiGui">'."\n<body", $newPage);
$newPage= str_replace('</body>', "</div>\n</body>", $newPage);

// add div container for raw query source code at the end
$newPage= str_replace('</body>', getDivQiSrc()."\n</body>", $newPage);


// output the page
echo $newPage;


/**
 * return Javacript, that needs to be placed in the header and that handles the communication
 * with the FCKEditor 
 */
function jsData() {
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

// Set the dialog tabs.
window.parent.AddTab( \'GUI\', \'Query Interface\' ) ;
window.parent.AddTab( \'SRC\', \'Query sourcecode\' ) ;

function OnDialogTabChange( tabCode ) {
	ShowE(\'divQiGui\' , ( tabCode == \'GUI\' ) ) ;
	ShowE(\'divQiSrc\' , ( tabCode == \'SRC\' ) ) ;
}

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
	window.parent.SetOkButton( true ) ;
	window.parent.SetAutoSize( true ) ;
	
	// load selected query if any
	LoadSelection();
}

function LoadSelection() {
	
	if ( !oAskSpan ) return ;

	var inputText = FCKTools.HTMLDecode(oAskSpan.innerHTML);
	GetE(\'xAskQueryRaw\').value = inputText.replace(/fckLR/g,\'\r\n\').replace( /&quot;/g, \'"\');
	window.parent.SetSelectedTab(\'SRC\');
}


// the OK button was hit
function Ok() {
	var ask;

	// use ask query from field in source code tab if active
	if (GetE(\'divQiSrc\').style.display.indexOf("none") == -1) {
		ask = FCKTools.HTMLEncode(GetE(\'xAskQueryRaw\').value.Trim().replace(/(\r\n|\n)/g, \'fckLR\')).replace( /"/g, \'&quot;\' ) ;

		if ( !( /^{{#ask:[\s\S]+}}$/.test( ask ) ) ) {
			alert( \'Ask query must start with {{#ask: and end with }}. Please check it.\' ) ;
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
		oAskSpan = FCK.EditorDocument.createElement( \'SPAN\' ) ;
		oAskSpan.className = \'_fck_mw_askquery\' ;
	}
	
	oAskSpan.innerHTML = ask;

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
					<span>Ask Query raw definition (from {{#ask: to }})</span><br />
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
 * copy data from page to newPage by defing a pattern, up to where
 * the string is copied from the begining. If $copy is set to false
 * the data will be deleted from $page without copying it to $newPage 
 */
function copyData(&$page, &$newPage, $pattern, $copy= true) {
	$pos = strpos($page, $pattern);
	if ($pos === false) return;
	if ($copy) {
		$newPage.= substr($page, 0, $pos);
	}
	$page = substr($page, $pos -1); 
}

/**
 * send a nice message to the user, in case the Query Interface was not
 * called successfully.
 */
function dieNice($msg) {

  echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
    <html>
      <head>
	    <title>Query Interface</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
      </head>
      <body style="overflow: hidden">'.$msg.'</body>
    </html>';
  exit(1);
}
?>
