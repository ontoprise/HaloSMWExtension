<?php
/*
 * Created on 24.03.2009
 *
 * Author: Benjamin
 */

//this extension does only work if the Halo extension is enabled
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the RichMedia extension. It is not a valid entry point.\n" );
}
if ( !defined( 'SMW_HALO_VERSION' ) )
    die("The RichMedia extension requires the Halo extension, which seems not to be installed.");

define('SMW_RM_VERSION', '1.3-for-SMW-1.4.x');

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
 * (Must be called *AFTER* SMWHalo is intialized.)
 */
function enableRichMediaExtension() {
	
	if( !defined( 'SF_VERSION' ) ) {
		die( "The extension 'Rich Media' requires the extension 'Semantic Forms'.\n".
			"Please read 'extensions/RichMedia/INSTALL' for further information.\n");
	}

	//tell SMW to call this function during initialization
	global $wgExtensionFunctions, $smwgRMIP, $wgHooks, $wgAutoloadClasses, $wgSpecialPages, $smwgEnableRichMedia;

	//TODO: clean possibility to disable the extension without any warning/errors
	$smwgEnableRichMedia = true;
	$wgExtensionFunctions[] = 'smwfRMSetupExtension';
	
	$wgHooks['ParserBeforeStrip'][] = 'smwfRegisterRMForm';
	
	//that#s a tricky workaround.
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
}

/**
 * Intializes Rich Media Extension.
 * Called from SMW during initialization.
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

function RMLinkEnd($skin, $target, $options, &$text, &$attribs, &$ret) {
	
	global $wgRMImagePreview;
	RMNamespace::isImage( $target->getNamespace(), $rMresult );
	if ( $rMresult ) {
		if ( $wgRMImagePreview ) {
			$queryString = "target=".urlencode($target->getPrefixedText());
			$embedWindowPage = SpecialPage::getPage('EmbedWindow');
			$embedWindowUrl = $embedWindowPage->getTitle()->getFullURL($queryString);
			$attribs['rev'] = 'height:500 width:700';
			$attribs['rel'] = 'iframe';
			$attribs['href'] = $embedWindowUrl;
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

function smwRMFormAddHTMLHeader(&$out){
	global $smwgRMScriptPath, $sfgScriptPath;
	
	static $rmScriptLoaded = false;
	
	if(!$rmScriptLoaded){
		
		# Prototype needed!
		if (!defined('SMW_HALO_VERSION')) {
		  $out->addScript('<script type="text/javascript" src="'.$smwgRMScriptPath .  '/scripts/prototype.js"/>');
		}
		
		$out->addScript('<script type="text/javascript" src="'.$smwgRMScriptPath. '/scripts/richmedia.js"/>');
		# Floatbox needed!
		$out->addScript('<script type="text/javascript" src="'.$sfgScriptPath .  '/libs/floatbox.js"/>');
		
	
		#Floatbox css file:
		 $out->addLink(array(
                    'rel'   => 'stylesheet',
                    'type'  => 'text/css',
                    'media' => 'screen, projection',
                    'href'  => $sfgScriptPath . '/skins/floatbox.css'
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
function smwfRMRegisterAutocompletionIcons(& $namespaceMappings) {

	$namespaceMappings[NS_PDF]="/extensions/RichMedia/skins/pdf.gif";
	$namespaceMappings[NS_DOCUMENT]="/extensions/RichMedia/skins/document.gif";
	$namespaceMappings[NS_AUDIO]= "/extensions/RichMedia/skins/music.gif";
	$namespaceMappings[NS_VIDEO]="/extensions/RichMedia/skins/video.gif";
	$namespaceMappings[NS_VCARD]= "/extensions/RichMedia/skins/vcard.gif";
	$namespaceMappings[NS_ICAL]= "/extensions/RichMedia/skins/icalendar.gif";
	//$namespaceMappings[NS_IMAGE]= "/skins/common/images/icons/smw_plus_icalendar_icon_16x16.gif.gif";
	return true;
}

?>