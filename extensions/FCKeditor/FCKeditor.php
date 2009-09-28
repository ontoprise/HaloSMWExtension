<?php

# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
	echo <<<HEREDOC
To install my extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/FCKeditor/FCKeditor.php" );
HEREDOC;
	exit( 1 );
}

# quit when comming from the command line, i.e. no User Agent send
if (isset($_SERVER) && !in_array('HTTP_USER_AGENT', array_keys($_SERVER)))
	return;
if (isset($HTTP_SERVER_VARS) && !in_array('HTTP_USER_AGENT', array_keys($HTTP_SERVER_VARS)))
    return;

/*
This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

require_once $IP . "/includes/GlobalFunctions.php";
require_once $IP . "/includes/parser/ParserOptions.php";
require_once $IP . "/includes/EditPage.php";

if (version_compare("1.12", $wgVersion, ">")) {
    require_once $IP . "/includes/parser/Parser_OldPP.php";
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "mw12/FCKeditorParser_OldPP.body.php";
}
else {
    require_once $IP . "/includes/parser/Parser.php";
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "FCKeditorParser.body.php";
}

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "FCKeditorSajax.body.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "FCKeditorParserOptions.body.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "FCKeditorSkin.body.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "FCKeditorEditPage.body.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "FCKeditor.body.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "fckeditor" . DIRECTORY_SEPARATOR . "fckeditor.php";

if (empty ($wgFCKEditorExtDir)) {
    $wgFCKEditorExtDir = "extensions/FCKeditor" ;
}
if (empty ($wgFCKEditorDir)) {
    $wgFCKEditorDir = "extensions/FCKeditor/fckeditor" ;
}
if (empty ($wgFCKEditorToolbarSet)) {
    $wgFCKEditorToolbarSet = "Wiki" ;
}
if (empty ($wgFCKEditorHeight)) {
    $wgFCKEditorHeight = "0" ; // "0" for automatic ("300" minimum).
}

/**
 * Enable use of AJAX features.
 */
$wgUseAjax = true;
$wgAjaxExportList[] = 'wfSajaxSearchImageFCKeditor';
$wgAjaxExportList[] = 'wfSajaxSearchArticleFCKeditor';
$wgAjaxExportList[] = 'wfSajaxWikiToHTML';
$wgAjaxExportList[] = 'wfSajaxGetImageUrl';
$wgAjaxExportList[] = 'wfSajaxGetMathUrl';
$wgAjaxExportList[] = 'wfSajaxSearchTemplateFCKeditor';
$wgAjaxExportList[] = 'wfSajaxSearchSpecialTagFCKeditor';
$wgAjaxExportList[] = 'wfSajaxTemplateListFCKeditor';
$wgAjaxExportList[] = 'wfSajaxFormForTemplateFCKeditor';

$wgExtensionCredits['other'][] = array(
"name" => "FCKeditor extension",
"author" => "[http://ckeditor.com FCKeditor] (inspired by the code written by Mafs [http://www.mediawiki.org/wiki/Extension:FCKeditor_%28by_Mafs%29]) extended by [http://www.ontoprise.de Ontoprise]",
"version" => 'fckeditor/mw-extension version $Rev$ 2008, FCK 2.6.4 Build 21629',
"url" => "http://meta.wikimedia.org/wiki/FCKeditor",
"description" => "FCKeditor extension"
);

$fckeditor = new FCKeditor("fake");
$wgFCKEditorIsCompatible = $fckeditor->IsCompatible();

$oFCKeditorExtension = new FCKeditor_MediaWiki();
$oFCKeditorExtension->registerHooks();

// load Special pages for Template picker (plugin mediawiki)
// if Semantic Forms have been installed
// *Note* This only works if the FCKeditor.php is included in the LocalSettings.php
// directly. The SMWHalo extension has a SMW_WYSIWYG.php that's included, which
// includes this file only if the editor is really required (i.e. on action =edit
// or action=formedit). The files below are used in the template picker of the
// FCKeditor but run in an iframe without the FCKeditor instance itself. Therefore
// these are not included here.
global $sfgFormPrinter;
if ($sfgFormPrinter) {
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "specials" . DIRECTORY_SEPARATOR . "SF_AddDataEmbedded.php";
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "specials" . DIRECTORY_SEPARATOR . "SF_EditDataEmbedded.php";
}







