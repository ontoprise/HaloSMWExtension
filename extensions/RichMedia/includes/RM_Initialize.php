<?php
/*  Copyright 2010, ontoprise GmbH
*  This file is part of the RichMedia-Extension.
*
*   The RichMedia-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The RichMedia-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @file
 * @ingroup RichMedia
 * This file contains methods for initializing the Rich Media extension.
 * 
 * @author Benjamin Langguth
 */

/**
 * This group contains all parts of the Rich Media extension.
 * @defgroup RichMedia
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the RichMedia extension. It is not a valid entry point.\n" );
}

define('SMW_RM_VERSION', '{{$VERSION}}-for-SMW-1.4.x');

global $smwgRMIP, $wgHooks; 
$smwgRMIP = $IP . '/extensions/RichMedia';
$smwgRMScriptPath = $wgScriptPath . '/extensions/RichMedia';

include_once('RM_AdditionalMIMETypes.php');
global $smwgRMFormByNamespace;

$smwgRMFormByNamespace = array(
	NS_IMAGE => 'RMImage',
	NS_PDF => 'RMPdf',
	NS_DOCUMENT => 'RMDocument',
	NS_AUDIO => 'RMAudio',
	NS_VIDEO => 'RMVideo',
	NS_ICAL => 'RMICalendar',
	NS_VCARD => 'RMVCard',
	'RMUpload' => 'RMUpload'
);

// Conversion of documents (PDF, MS Office)
global $smwgEnableUploadConverter;
if ($smwgEnableUploadConverter) {
	global $wgExtensionMessagesFiles, $wgAutoloadClasses;
	require_once("$smwgRMIP/specials/SMWUploadConverter/SpecialPurposeParserFunctions/UC_EmailIDExtractor.php");
	$wgAutoloadClasses['UploadConverter'] = $smwgRMIP . '/specials/SMWUploadConverter/SMW_UploadConverter.php';
	$wgExtensionMessagesFiles['UploadConverter'] = $smwgRMIP . '/specials/SMWUploadConverter/SMW_UploadConverterMessages.php';

	$wgHooks['UploadComplete'][] = 'UploadConverter::convertUpload';
}

/**
 * Configures Rich Media Extension for initialization.
 *   If you have installed SMWHalo and want to use its autocompletion features 
 *   in the provided forms then this function must be called *AFTER* SMWHalo is intialized.
 * 
 * @return bool
 */
function enableRichMediaExtension() {
	
	if( !defined( 'SF_VERSION' ) ) {
		die( "The extension 'Rich Media' requires the extension 'Semantic Forms'.\n".
			"Please read 'extensions/RichMedia/INSTALL' for further information.\n");
	}

	//tell SMW to call this function during initialization
	global $wgExtensionFunctions, $smwgRMIP, $wgHooks, $wgAutoloadClasses, $wgSpecialPages, $smwgEnableRichMedia;

	// clean possibility to disable the extension without any warning/errors
	// and let other extensions know about Rich Media
	$smwgEnableRichMedia = true;
	$wgExtensionFunctions[] = 'smwfRMSetupExtension';

	$wgHooks['ParserBeforeStrip'][] = 'smwfRegisterRMForm';

	//that's a tricky workaround.
	// see: http://www.mediawiki.org/wiki/Manual:Tag_extensions#How_can_I_avoid_modification_of_my_extension.27s_HTML_output.3F for more infos
	$wgHooks['ParserBeforeStrip'][] = 'smwfRegisterRMLink';
	$wgHooks['ParserAfterTidy'][] = 'RMForm::createRichMediaLinkAfterTidy';
	
	$wgHooks['ParserBeforeStrip'][] = 'smwfRegisterRMEmbedWindowLink';

	//Add a hook to initialise the magic word for the {{#rmf:}} Syntax Parser
	$wgHooks['LanguageGetMagic'][] = 'RMFormUsage_Magic';
	//Add a hook to initialise the magic word for the {{#rml:}} Syntax Parser
	$wgHooks['LanguageGetMagic'][] = 'RMLinkUsage_Magic';
	//Add a hook to initialise the magic word for the {{#rmew:}} Syntax Parser
	$wgHooks['LanguageGetMagic'][] = 'RMEmbedWindowLinkUsage_Magic';
	//Add a hook to initialise the magic word for the additional image attribute preview
	$wgHooks['LanguageGetMagic'][] = 'RMImagePreviewUsage_Magic';
	// workaround: because the necessary scripts has been only loaded by the parser function, when action=purge.
	
	
	//Change the image links
	$wgHooks['LinkBegin'][] = 'RMLinkBegin';
	$wgHooks['LinkEnd'][] = 'RMLinkEnd';
	
	//EmbedWindow
	$wgSpecialPages['EmbedWindow'] = 'RMEmbedWindow';
	$wgAutoloadClasses['RMEmbedWindow'] = $smwgRMIP . '/specials/RM_EmbedWindow.php';
	
	// register AC icons
	$wgHooks['smwhACNamespaceMappings'][] = 'smwfRMRegisterAutocompletionIcons';
	
	global $smgJSLibs; $smgJSLibs[] = 'prototype';

	return true;
}

/**
 * Intializes Rich Media Extension.
 * Called from SMW during initialization.
 * 
 * @return bool
 */
function smwfRMSetupExtension() {
	global $wgHooks, $wgExtensionCredits, $wgAutoloadClasses, $wgSpecialPages; 
	global $smwgRMIP, $wgSpecialPageGroups, $wgRequest, $wgContLang;

	smwfRMInitMessages();

	$wgAutoloadClasses['RMForm'] = $smwgRMIP . '/includes/RM_Form.php';	
	$wgHooks['BeforePageDisplay'][] = 'smwRMFormAddHTMLHeader';	
	// Register Credits
	$wgExtensionCredits['parserhook'][]=array('name'=>'Rich&nbsp;Media&nbsp;Extension', 'version'=>SMW_RM_VERSION,
		'author'=>"Benjamin&nbsp;Langguth, Sascha&nbsp;Wagner and Daniel&nbsp;Hansch. Maintained by [http://www.ontoprise.de Ontoprise].", 
		'url'=>'https://sourceforge.net/projects/halo-extension', 
		'description' => 'The Rich Media Extension provides an ontology to allow easy handling of media such as documents, images, doc, pdf etc. The ontology comprises templates and forms and examples. It enhances a one-click media upload of files and enables annotation of media in a simple way.');
	
	return true;
}

function smwfRegisterRMForm( &$parser ) {
	
	$parser->setFunctionHook( 'RMFormUsage', 'smwfProcessRMFormParserFunction' );

	return true; // always return true, in order not to stop MW's hook processing!	
}

function smwfRegisterRMLink( &$parser ) {
	
	$parser->setFunctionHook( 'RMLinkUsage', 'smwfProcessRMLinkParserFunction' );

	return true; // always return true, in order not to stop MW's hook processing!	
}
function smwfRegisterRMEmbedWindowLink( &$parser ) {
	
	$parser->setFunctionHook( 'RMEmbedWindowLinkUsage', 'smwfProcessRMEmbedWindowLinkParserFunction' );

	return true; // always return true, in order not to stop MW's hook processing!	
}

/**
 * The {{#rmf }} parser function processing part.
 */
function smwfProcessRMFormParserFunction(&$parser) {
	$params = func_get_args();
	array_shift( $params ); // we already know the $parser ...

	return RMForm::createRichMediaForm($parser, $params);
}

/**
 * The {{#rml }} parser function processing part.
 */
function smwfProcessRMLinkParserFunction(&$parser) {
	$params = func_get_args();
	array_shift( $params ); // we already know the $parser ...

	return RMForm::createRichMediaLink($parser, $params);
}

/**
 * The {{#rmew }} parser function processing part.
 */
function smwfProcessRMEmbedWindowLinkParserFunction(&$parser) {
	$params = func_get_args();
	array_shift( $params ); // we already know the $parser ...
	return RMForm::createRichMediaEmbedWindowLink($parser, $params);
}

/**
 * Hookfunction that changes the Namespace for the appropriate image links.
 * Called by Hook 'LinkBegin'.
 * @param $this
 * @param $target
 * @param $text
 * @param $customAttribs
 * @param $query
 * @param $options
 * @param $ret
 * @return boolean
 */
function RMLinkBegin($this, $target, &$text, &$customAttribs, &$query, &$options, &$ret) {

	global $wgNamespaceByExtension,$wgCanonicalNamespaceNames;
	$ext = explode( '.', $target->mTextform );
	array_shift( $ext );
	if( count( $ext ) ) {
		$finalExt = $ext[count( $ext ) - 1];
	} else {
		$finalExt = '';
	}
	$ns = NS_FILE;
	if ( isset( $finalExt ) ) {
		if ( isset( $wgNamespaceByExtension[$finalExt] ) ) {
			$ns = $wgNamespaceByExtension[$finalExt];
			//Just change it for NS_FILE... not for specialpages etc
			if ($target->mNamespace == NS_FILE)
				$target->mNamespace = $ns;
		}
	}
	if ($target->mPrefixedText)
		$target->mPrefixedText = str_replace('File:',$wgCanonicalNamespaceNames[$ns].":",$target->mPrefixedText);
	if($text)
		$text = str_replace('File:',$wgCanonicalNamespaceNames[$ns].":",$text);
	//$result = str_replace('File:',$wgCanonicalNamespaceNames[$ns],$customAttribs['title']);
	
	return true;
}

/**
 * Hook function that changes all links that point to Special:Upload with Special:UploadWindow
 * and uses an overlay window for the upload process.
 * Called by 'LinkEnd'.
 * 
 * @param $skin
 * @param $target
 * @param $options
 * @param $text
 * @param $attribs
 * @param $ret
 * @return boolean
 */
function RMLinkEnd($skin, $target, $options, &$text, &$attribs, &$ret) {
	
	global $wgRMImagePreview;
	$temp_var = $target->getNamespace();
	RMNamespace::isImage( $temp_var, $rMresult );
	if ( $rMresult ) {
		if ( $wgRMImagePreview ) {
			$linkID = $target->getPrefixedText() . rand(0, 1024);
			$queryString = "target=".urlencode($target->getPrefixedText());
			$embedWindowPage = SpecialPage::getPage('EmbedWindow');
			$embedWindowUrl = $embedWindowPage->getTitle()->getFullURL($queryString);
			$attribs['rev'] = 'height:500 width:700';
			$attribs['rel'] = 'iframe';
			$attribs['id'] = $linkID;
			$attribs['href'] = $embedWindowUrl;
			$attribs['onclick'] = "fb.loadAnchor($('$linkID'));return false;";
		}
	}
	//Change Special:Upload to Special:UploadWindow
	if ( $target->getPrefixedText() == 'Special:Upload' && $target->getNamespace() == NS_SPECIAL ) {
		$uploadWindowPage = SpecialPage::getPage('UploadWindow');
		$queryString = "wpIgnoreWarning=true";
		$uploadWindowUrl = $uploadWindowPage->getTitle()->getLocalURL($queryString);
		$attribs['rev'] = 'height:660 width:600';
		$attribs['rel'] = 'iframe';
		$attribs['href'] = $uploadWindowUrl;
		$attribs['title'] = $uploadWindowPage->getTitle()->getPrefixedText();
		$attribs['id'] = 'upload_window';
		$attribs['onclick'] = "fb.loadAnchor($('upload_window'));return false;";
	}
	
	return true;
}

function RMFormUsage_Magic(&$magicWords, $langCode){
	$magicWords['RMFormUsage'] = array( 0, 'rmf' );
	return true;
}

function RMLinkUsage_Magic(&$magicWords, $langCode){
	$magicWords['RMLinkUsage'] = array( 0, 'rml' );
	return true;
}

function RMEmbedWindowLinkUsage_Magic(&$magicWords, $langCode){
	$magicWords['RMEmbedWindowLinkUsage'] = array( 0, 'rmew' );
	return true;
}

function RMImagePreviewUsage_Magic(&$magicWords, $langCode){
	$magicWords['img_nopreview'] = array( 0, 'nopreview' );
	return true;
}

/**
 * Extends the HTML header with the required css and javascript files.
 * 
 * @param OutputPage $out
 * @return boolean
 */
function smwRMFormAddHTMLHeader(&$out){
	global $smwgRMScriptPath, $sfgScriptPath;
	
	static $rmScriptLoaded = false;
	
	if(!$rmScriptLoaded){
		
		# Prototype needed!
		
		
		$out->addScript('<script type="text/javascript" src="'.$smwgRMScriptPath. '/scripts/richmedia.js"></script>');
		# Floatbox needed!
		//$out->addScript('<script type="text/javascript" src="'.$sfgScriptPath .  '/libs/floatbox.js"></script>');
		
	
		#Floatbox css file:
//		$out->addLink(array(
//			'rel'   => 'stylesheet',
//			'type'  => 'text/css',
//			'media' => 'screen, projection',
//			'href'  => $sfgScriptPath . '/skins/floatbox.css'
//		));
		$out->addLink(array(
			'rel'   => 'stylesheet',
			'type'  => 'text/css',
			'media' => 'screen, projection',
			'href'  => $smwgRMScriptPath . '/skins/richmedia.css'
		));

		$rmScriptLoaded = true;
	}
	return true;
}


#TODO: international content messages! 
function smwfRMInitMessages() {
	global $smwgRMMessagesInitialized;
	if (isset($smwgRMMessagesInitialized)) return; // prevent double init
	
	smwfRMInitUserMessages(); // lazy init for ajax calls
	
	$smwgRMMessagesInitialized = true;
}

/**
 * Registers SMW Rich Media Content messages.
 */
function smwfRMInitContentLanguage($langcode) {
	global $smwgRMIP, $smwgRMContLang;
	if (!empty($smwgRMContLang)) { return; }

	$smwContLangClass = 'SMW_RMLanguage' . str_replace( '-', '_', ucfirst( $langcode ) );

	if (file_exists($smwgRMIP . '/languages/'. $smwContLangClass . '.php')) {
		include_once( $smwgRMIP . '/languages/'. $smwContLangClass . '.php' );
	}

	// fallback if language not supported
	if ( !class_exists($smwContLangClass)) {
		include_once($smwgRMIP . '/languages/SMW_RMLanguageEn.php');
		$smwContLangClass = 'SMW_RMLanguageEn';
	}
	$smwgRMContLang = new $smwContLangClass();
}

/**
 * Registers Rich Media extension User messages.
 */
function smwfRMInitUserMessages() {
	global $wgMessageCache, $smwgRMContLang, $wgLanguageCode;
	smwfRMInitContentLanguage($wgLanguageCode);

	global $smwgRMIP, $smwgRMLang;
	if (!empty($smwgRMLang)) { return; }
	global $wgMessageCache, $wgLang;
	$smwLangClass = 'SMW_RMLanguage' . str_replace( '-', '_', ucfirst( $wgLang->getCode() ) );

	if (file_exists($smwgRMIP . '/languages/'. $smwLangClass . '.php')) {
		include_once( $smwgRMIP . '/languages/'. $smwLangClass . '.php' );
	}
	// fallback if language not supported
	if ( !class_exists($smwLangClass)) {
		global $smwgRMContLang;
		$smwgRMLang = $smwgRMContLang;
	} else {
		$smwgRMLang = new $smwLangClass();
	}

	$wgMessageCache->addMessages($smwgRMLang->getUserMsgArray(), $wgLang->getCode());
}

/**
 * Add appropriate JS language script
 */
function smwfRMAddJSLanguageScripts(& $jsm, $mode = "all", $namespace = -1, $pages = array()) {
	global $wgLanguageCode, $smwgRMScriptPath, $wgUser;
	
	// content language file
	$lng = '/scripts/Language/SMWRM_Language';
	
	$jsm->addScriptIf($smwgRMScriptPath . $lng.".js", $mode, $namespace, $pages);
	
	if (!empty($wgLanguageCode)) {
		$lng .= ucfirst($wgLanguageCode).'.js';
		if (file_exists($smwgRMScriptPath . $lng)) {
			$jsm->addScriptIf($smwgRMScriptPath . $lng, $mode, $namespace, $pages);
		} else {
			$jsm->addScriptIf($smwgRMScriptPath . '/scripts/Language/SMWRM_LanguageEn.js', $mode, $namespace, $pages);
		}
	} else {
		$jsm->addScriptIf($smwgRMScriptPath . '/scripts/Language/SMWRM_LanguageEn.js', $mode, $namespace, $pages);
	}

	// user language file
	$lng = '/scripts/Language/SMWRM_Language';
	if (isset($wgUser)) {
		$lng .= "User".ucfirst($wgUser->getOption('language')).'.js';
		if (file_exists($smwgRMScriptPath . $lng)) {
			$jsm->addScriptIf($smwgRMScriptPath . $lng, $mode, $namespace, $pages);
		} else {
			$jsm->addScriptIf($smwgRMScriptPath . '/scripts/Language/SMWRM_LanguageUserEn.js', $mode, $namespace, $pages);
		}
	} else {
		$jsm->addScriptIf($smwgRMScriptPath . '/scripts/Language/SMWRM_LanguageUserEn.js', $mode, $namespace, $pages);
	}
}

/**
 * Registers the autocompletion icons of the Rich Media namespace for the SMWHaloAutocompletion.
 * 
 * @param array $namespaceMappings
 * @return boolean
 */
function smwfRMRegisterAutocompletionIcons(& $namespaceMappings) {

	$namespaceMappings[NS_PDF]="/extensions/RichMedia/skins/pdf.gif";
	$namespaceMappings[NS_DOCUMENT]="/extensions/RichMedia/skins/document.gif";
	$namespaceMappings[NS_AUDIO]= "/extensions/RichMedia/skins/music.gif";
	$namespaceMappings[NS_VIDEO]="/extensions/RichMedia/skins/video.gif";
	$namespaceMappings[NS_VCARD]= "/extensions/RichMedia/skins/vcard.gif";
	$namespaceMappings[NS_ICAL]= "/extensions/RichMedia/skins/icalendar.gif";
	return true;
}