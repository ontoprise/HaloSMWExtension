<?php
/*
 * Copyright (C) Vulcan Inc.
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

/**
 * HashingFunctions.php
 * Defines a new parser function:
 * 
 * {{#md5:name}} Returns the md5 hash of the given value.  Can also be called
 *               with a default value, which is returned if the given argument is
 *               undefined or blank: {{#md5sum:name|default}}
 * {{#rot13:name}} Returns a string where all letters in the alphabet are md5 hash of the given value.  Can also be called
 *                 with a default value, which is returned if the given argument is
 *                 undefined or blank: {{#md5sum:name|default}}
 * 
 * Author: Stephan Robotta
 * Version 1.0 (06/05/2009)
 */

$wgExtensionFunctions[] = 'wfHashingFunctions';
$wgExtensionCredits['parserhook'][] = array(
	'name' => 'Hashing Functions',
	'version' => '1.0',
	'url' => '',
	'author' => 'Maintained by [http://smwplus.com ontoprise GmbH].',   
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
		return (strlen($value) > 0) ? md5($value) : $default;
	}
	function rot13( &$parser, $value = '', $default = '' ) {
		return (strlen($value) > 0) ? str_rot13($value) : $default;
	}

}
