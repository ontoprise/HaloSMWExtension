<?php

/**
 * @file
 * @ingroup DITermImport
 * 
 * @author Thomas Schweitzer
 */

global $smwgWWSNamespaceIndex;

if (!defined('NS_TI_EMAIL')) define('NS_TI_EMAIL', $smwgWWSNamespaceIndex+20);
if (!defined('NS_TI_EMAIL_TALK')) define('NS_TI_EMAIL_TALK', $smwgWWSNamespaceIndex+21);


function diRegisterTermImportNamespaces(){
	global $wgExtraNamespaces, $wgContLang;
	$wgExtraNamespaces = $wgExtraNamespaces +
		array(NS_TI_EMAIL => 'E-mail',
	    	NS_TI_EMAIL_TALK => 'E-mail_talk',
	    );
	    
	global $wgNamespaceAliases;
	$wgNamespaceAliases = $wgNamespaceAliases + 
		array('E-mail' => NS_TI_EMAIL,
			'E-mail_talk' => NS_TI_EMAIL_TALK,
	    	);
      
	//We want semantic data in this namespaces!
	global $smwgNamespacesWithSemanticLinks;
	$smwgNamespacesWithSemanticLinks = $smwgNamespacesWithSemanticLinks + 
		array( 
			NS_TI_EMAIL => true,
		);

	//$wgContLang->fixUpSettings();
}