<?php

$wgFileExtensions = array_merge($wgFileExtensions, array(
	'pdf', 'doc', 'ac3', 'avi', 'mp3', 'ogg', 'mpg', 'mpeg',
 	'mov', 'wmv', 'ppt', 'pps', 'odt', 'ods', 'odp', 'odg', 'odf', 'sxw', 'zip',
 	'rar', 'xls', 'docx', 'xlsx', 'pptx', 'ics', 'vcf'));
// other possible extensions: ('pwz', 'ppz', 'pot' (draft))(Powerpoint)
// 'rtf', 'mp2', 'ott' + 'stw' (OpenOffice drafts)
// odt, ods, odp, odg, odf, sxw are OpenOffice-extensions 

if (!defined('NS_DOCUMENT')) define('NS_DOCUMENT', 120);
if (!defined('NS_DOCUMENT_TALK')) define('NS_DOCUMENT_TALK', 121);
if (!defined('NS_AUDIO')) define('NS_AUDIO', 122);
if (!defined('NS_AUDIO_TALK')) define('NS_AUDIO_TALK', 123);
if (!defined('NS_VIDEO')) define('NS_VIDEO', 124);
if (!defined('NS_VIDEO_TALK')) define('NS_VIDEO_TALK', 125);
if (!defined('NS_PDF')) define('NS_PDF', 126);
if (!defined('NS_PDF_TALK')) define('NS_PDF_TALK', 127);
if (!defined('NS_ICAL')) define('NS_ICAL', 128);
if (!defined('NS_ICAL_TALK')) define('NS_ICAL_TALK', 129);
if (!defined('NS_VCARD')) define('NS_VCARD', 130);
if (!defined('NS_VCARD_TALK')) define('NS_VCARD_TALK', 131);

global $wgExtraNamespaces;
$wgExtraNamespaces = $wgExtraNamespaces +
	array(NS_DOCUMENT => 'Document',
		NS_DOCUMENT_TALK => 'Document_talk',
		NS_AUDIO => 'Audio',
		NS_AUDIO_TALK => 'Audio_talk',
		NS_VIDEO => 'Video',
		NS_VIDEO_TALK => 'Video_talk',
		NS_PDF => 'Pdf',
		NS_PDF_TALK => 'Pdf_talk',
		NS_ICAL => 'ICalendar',
		NS_ICAL_TALK => 'ICalendar_talk',
		NS_VCARD => 'VCard',
		NS_VCARD_TALK => 'VCard_talk',
	);
global $wgRichMediaNamespaceAliases; 
$wgRichMediaNamespaceAliases = array('Document' => NS_DOCUMENT,
		'Document_talk' => NS_DOCUMENT_TALK,
		'Audio' => NS_AUDIO,
		'Audio_talk' => NS_AUDIO_TALK,
		'Video' => NS_VIDEO,
		'Video_talk' => NS_VIDEO_TALK,
		'Pdf' => NS_PDF,
		'Pdf_talk' => NS_PDF_TALK,
		'ICalendar' => NS_ICAL,
		'ICalendar_talk' => NS_ICAL_TALK,
		'VCard' => NS_VCARD,
		'VCard_talk' => NS_VCARD_TALK
	); 
	
global $wgNamespaceAliases;
$wgNamespaceAliases = array_merge($wgNamespaceAliases, $wgRichMediaNamespaceAliases); 
	

#used to determine which form to choose
global $wgNamespaceByExtension;
$wgNamespaceByExtension = array(
	'png' => NS_IMAGE,
	'gif' => NS_IMAGE,
	'jpg' => NS_IMAGE,
	'jpeg' => NS_IMAGE,
	'pdf' => NS_PDF, 
	'doc' => NS_DOCUMENT,
	'ac3' => NS_AUDIO,
	'avi' => NS_VIDEO,
	'mp3' => NS_AUDIO,
	'ogg' => NS_AUDIO,
	'mpg' => NS_VIDEO,
	'mpeg' => NS_VIDEO,
	'mov' => NS_VIDEO,
	'wmv' => NS_VIDEO,
	'ppt' => NS_DOCUMENT,
	'pps' => NS_DOCUMENT,
	'odt' => NS_DOCUMENT,
	'ods' => NS_DOCUMENT,
	'odp' => NS_DOCUMENT,
	'odg' => NS_DOCUMENT,
	'odf' => NS_DOCUMENT,
	'sxw' => NS_DOCUMENT,
	'svg' => NS_IMAGE,
	'owl' => NS_IMAGE,
	'zip' => NS_IMAGE,
	'rar' => NS_IMAGE,
	'xls' => NS_DOCUMENT,
	'docx' => NS_DOCUMENT,
	'xlsx' => NS_DOCUMENT,
	'pptx' => NS_DOCUMENT,
	'ics' => NS_ICAL,
	'vcf' => NS_VCARD
);
	
//We want semantic data in this namespaces!
global $smwgNamespacesWithSemanticLinks;
$smwgNamespacesWithSemanticLinks = $smwgNamespacesWithSemanticLinks + 
	array( 
		NS_DOCUMENT => true,
		NS_AUDIO => true,
		NS_VIDEO => true,
		NS_PDF	=> true,
		NS_ICAL	=> true,
		NS_VCARD	=> true
	);

$wgHooks['CheckNamespaceForImage'][] = 'RMNamespace::isImage';

class RMNamespace { 
	
	/**
 	* Is the namespace one of the (new) image-namespaces?
 	* created for AdditionalMIMETypes
 	*
 	* @param int $index
 	* @return bool
 	*/

	public static function isImage( &$index, &$rMresult ) {
		$rMresult = false;
		$rMresult |= ( $index == NS_FILE || $index == NS_IMAGE || $index == NS_DOCUMENT ||
			$index == NS_PDF || $index == NS_AUDIO || $index == NS_VIDEO
			|| $index == NS_ICAL || $index == NS_VCARD );
		return true;
	}
}
?>