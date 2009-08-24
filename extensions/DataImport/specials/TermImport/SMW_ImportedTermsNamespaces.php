<?php

global $smwgWWSNamespaceIndex, $wgContLang;

if (!defined('NS_TI_EMAIL')) define('NS_TI_EMAIL', $smwgWWSNamespaceIndex+20);
if (!defined('NS_TI_EMAIL_TALK')) define('NS_TI_EMAIL_TALK', $smwgWWSNamespaceIndex+21);
if (!defined('NS_TI_VCARD')) define('NS_TI_VCARD', $smwgWWSNamespaceIndex+22);
if (!defined('NS_TI_VCARD_TALK')) define('NS_TI_VCARD_TALK', $smwgWWSNamespaceIndex+23);
if (!defined('NS_TI_ICALENDAR')) define('NS_TI_ICALENDAR', $smwgWWSNamespaceIndex+24);
if (!defined('NS_TI_ICALENDAR_TALK')) define('NS_TI_ICALENDAR_TALK', $smwgWWSNamespaceIndex+25);


global $wgExtraNamespaces;
$wgExtraNamespaces = $wgExtraNamespaces +
	array(NS_TI_EMAIL => 'E-mail',
	    NS_TI_EMAIL_TALK => 'E-mail_talk',
	    NS_TI_VCARD => 'VCard',
	    NS_TI_VCARD_TALK => 'VCard_talk',
	    NS_TI_ICALENDAR => 'ICalendar',
	    NS_TI_ICALENDAR_TALK => 'ICalendar_talk',
	    );
	    
global $wgNamespaceAliases;
$wgNamespaceAliases = $wgNamespaceAliases + 
	array('E-mail' => NS_TI_EMAIL,
		'E-mail_talk' => NS_TI_EMAIL_TALK,
	  	'VCard' => NS_TI_VCARD,
		'VCard_talk' => NS_TI_VCARD_TALK,
	    'ICalendar' => NS_TI_ICALENDAR,
		'ICalendar_talk' => NS_TI_ICALENDAR_TALK,
	    'Pdf' => NS_PDF,
		'Pdf_talk' => NS_PDF_TALK
	    );
      
//We want semantic data in this namespaces!
global $smwgNamespacesWithSemanticLinks;
$smwgNamespacesWithSemanticLinks = $smwgNamespacesWithSemanticLinks + 
	array( 
		NS_TI_EMAIL => true,
	 	NS_TI_VCARD => true,
	    NS_TI_ICALENDAR => true,
	);

$wgContLang->fixUpSettings();
	
?>