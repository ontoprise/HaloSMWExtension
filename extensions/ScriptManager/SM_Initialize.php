<?php

/*  Copyright 2009, ontoprise GmbH
 *  This file is part of the Collaboration-Extension.
 *
 *   The Collaboration-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Collaboration-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Script extension which manages the including of common JS script libraries.
 *
 * @author Kai Kühn
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the Script Manager extension. It is not a valid entry point.\n" );
}

define('SM_VERSION', '0.1');

global $wgExtensionFunctions;
$wgExtensionFunctions[] = 'smgSetupExtension';

function smgSetupExtension() {
	global $wgHooks;
	$wgHooks['BeforePageDisplay'][]='smfAddHTMLHeader';
}

function smfAddHTMLHeader(& $out) {
	global $smgJSLibs, $wgScriptPath;
	$smgSMPath = $wgScriptPath . '/extensions/ScriptManager';
	if (!is_array($smgJSLibs)) return true;
	$smgJSLibs = array_unique($smgJSLibs);
	$smgJSLibs = smfSortScripts($smgJSLibs);
	foreach($smgJSLibs as $lib_id) {
		
		switch($lib_id) {
			case 'prototype':
				$out->addScript("<script type=\"text/javascript\" src=\"". "$smgSMPath/scripts/prototype.js\" id=\"Prototype_script_inclusion\"></script>");
				break;
			case 'jquery':
				$out->addScript("<script type=\"text/javascript\" src=\"". "$smgSMPath/scripts/jquery.js\"></script>");
				break;
		}
	}
	return true;
}

function smfSortScripts($smgJSLibs) {
	$newList = array();
	if (in_array('jquery', $smgJSLibs)) {
		$newList[] = 'jquery';
	}
	foreach($smgJSLibs as $lib) {
		if ($lib != 'jquery') {
			$newList[] = $lib;
		}
	}
	return $newList;
}