<?php
/*
 HashingFunctions.php
 Defines a new parser function:

 {{#md5:name}} Returns the md5 hash of the given value.  Can also be called
               with a default value, which is returned if the given argument is
               undefined or blank: {{#md5sum:name|default}}
 {{#rot13:name}} Returns a string where all letters in the alphabet are md5 hash of the given value.  Can also be called
                 with a default value, which is returned if the given argument is
                 undefined or blank: {{#md5sum:name|default}}
 
 Author: Ontoprise
 Version 1.0 (06/05/2009)
*/
 
$wgExtensionFunctions[] = 'wfHashingFunctions';
$wgExtensionCredits['parserhook'][] = array(
	'name' => 'Hashing Functions',
	'version' => '1.0',
	'url' => '',
	'author' => 'Ontoprise',   
	'description' => 'Defines a new parser function that creates a hash from a given string. '.
                     'Currently md5 hashes and rot13 are supported.'
);
 
$wgHooks['LanguageGetMagic'][] = 'wfHashingFunctionsLanguageGetMagic';
 
function wfHashingFunctions() {
	global $wgParser, $wgHashingFunctions;
 
	$wgHashingFunctions = new HashingFunctions();
 
	$wgParser->setFunctionHook( 'md5', array( &$wgHashingFunctions, 'md5' ) );
	$wgParser->setFunctionHook( 'rot13', array( &$wgHashingFunctions, 'rot13' ) );
}
 
function wfHashingFunctionsLanguageGetMagic( &$magicWords, $langCode ) {
	switch ( $langCode ) {
	default:
		$magicWords['md5']    = array( 0, 'md5' );
		$magicWords['rot13']    = array( 0, 'rot13' );
	}
	return true;
}
 
class HashingFunctions {
 
	function md5( &$parser, $value = '', $default = '' ) {
		$parser->disableCache();
		return (strlen($value) > 0) ? md5($value) : $default;
	}
	function rot13( &$parser, $value = '', $default = '' ) {
		$parser->disableCache();
		return (strlen($value) > 0) ? str_rot13($value) : $default;
	}

}