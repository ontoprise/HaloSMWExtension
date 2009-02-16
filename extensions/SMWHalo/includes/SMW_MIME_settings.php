<?php
// begin AdditionalMIMETypes:
$wgFileExtensions = array_merge($wgFileExtensions, array(
	'pdf', 'doc', 'ac3', 'avi', 'mp3', 'ogg', 'mpg', 'mpeg',
 	'mov', 'wmv', 'ppt', 'pps', 'odt', 'ods', 'odp', 'odg', 'odf', 'sxw'));
// other possible extensions: ('pwz', 'ppz', 'pot'(Vorlage))(Powerpoint), xls?
// 'rtf', 'mp2', 'ott' + 'stw' (OpenOfficeTextvorlage)
// odt, ods, odp, odg, odf, sxw are OpenOffice-extensions 

define('NS_DOCUMENT', 120);
define('NS_DOCUMENT_TALK', 121);
define('NS_AUDIO', 122);
define('NS_AUDIO_TALK', 123);
define('NS_VIDEO', 124);
define('NS_VIDEO_TALK', 125);
define('NS_PDF', 126);
define('NS_PDF_TALK', 127);

$wgExtraNamespaces = $wgExtraNamespaces +
	array(NS_DOCUMENT => 'Document',
	    NS_DOCUMENT_TALK => 'Document_talk',
	    NS_AUDIO => 'Audio',
	    NS_AUDIO_TALK => 'Audio_talk',
	    NS_VIDEO => 'Video',
	    NS_VIDEO_TALK => 'Video_talk',
	    NS_PDF => 'Pdf',
	    NS_PDF_TALK => 'Pdf_talk',
	    );

$wgNamespaceAliases = $wgNamespaceAliases + 
	array('Document' => NS_DOCUMENT,
		'Document_talk' => NS_DOCUMENT_TALK,
	  	'Audio' => NS_AUDIO,
		'Audio_talk' => NS_AUDIO_TALK,
	    'Video' => NS_VIDEO,
		'Video_talk' => NS_VIDEO_TALK,
	    'Pdf' => NS_PDF,
		'Pdf_talk' => NS_PDF_TALK
	    );
      
global $wgNamespaceByExtension;
$wgNamespaceByExtension =
	array('png' => NS_IMAGE,
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
		'sxw' => NS_DOCUMENT
	);
// end AdditionalMIMETypes
?>