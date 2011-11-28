<?php
/*
 * Copyright (C) ontoprise GmbH
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

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
	'url' => 'http://smwforum.ontoprise.com/smwforum/index.php/Help:Application_Programming_extension',
            'author'=>"Maintained by [http://smwplus.com ontoprise GmbH].", 
			'description' => 'Defines the new parser function "arg" that retrieves arguments from the URL of the current article. These values can be used in the wikitext of the article.'
);
 
$wgHooks['LanguageGetMagic'][]  = 'wfURLArgumentsLanguageGetMagic';
$wgHooks['PageRenderingHash'][] = 'wfURLArgumentsPageRenderingHash';

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

/**
 * The hash for the page cache depends on the URL arguments.
 *
 * @param string $hash
 * 		A reference to the hash. The URL arguments are appended to this hash.
 *
 */
function wfURLArgumentsPageRenderingHash($hash) {

	global $wgRequest;
	//$wgRequest->getValues();
	$urlArgs = $_GET;
	ksort($urlArgs);
	$hash .= "!args=";
	$ignoreArgs = array("action", "submit", "title");
    foreach ($urlArgs as $key => $value) {
    	if (!in_array($key, $ignoreArgs)) {
			$hash .= "$key+$value+";
    	}
    }

    return true;
}

 
class ExtURLArguments {
 
	function arg( &$parser, $name = '', $default = '' ) {
//		global $wgRequest;
//		return $wgRequest->getVal($name, $default);
		return (isset($_GET[$name])) ? $_GET[$name] : $default;		
	}
}
