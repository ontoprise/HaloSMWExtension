<?php
/*
 URLArguments.php
 Defines a new parser function:

 {{#arg:name}} Returns the value of the given URL argument.  Can also be called
               with a default value, which is returned if the given argument is
               undefined or blank: {{#arg:name|default}}
 
 Author: Thomas Schweitzer
         based on: Algorithm [http://meta.wikimedia.org/wiki/User:Algorithm]
 Version 1.0 (10/3/09)
*/
 
$wgExtensionFunctions[] = 'wfURLArguments';
$wgExtensionCredits['parserhook'][] = array(
	'name' => 'URL Arguments',
	'version' => '1.0',
	'url' => '',
	'author' => 'Thomas Schweitzer',   
	'description' => 'Defines a new parser function that retrieves arguments from the URL.'
);
 
$wgHooks['LanguageGetMagic'][] = 'wfURLArgumentsLanguageGetMagic';
 
function wfURLArguments() {
	global $wgParser, $wgExtURLArguments;
 
	$wgExtURLArguments = new ExtURLArguments();
 
	$wgParser->setFunctionHook( 'arg', array( &$wgExtURLArguments, 'arg' ) );
}
 
function wfURLArgumentsLanguageGetMagic( &$magicWords, $langCode ) {
	switch ( $langCode ) {
	default:
		$magicWords['arg']    = array( 0, 'arg' );
	}
	return true;
}
 
class ExtURLArguments {
 
	function arg( &$parser, $name = '', $default = '' ) {
		global $wgRequest;
		$parser->disableCache();
		return $wgRequest->getVal($name, $default);
	}
}
?>
