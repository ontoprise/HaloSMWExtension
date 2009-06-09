<?php
/*  Copyright 2007, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install my extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/MyExtension/MyExtension.php" );
EOT;
        exit( 1 );
}

//Register Specialpage
//$wgExtensionCredits['specialpage'][] = array(
//	'name' => 'Comment',
//	'author' => 'Robert Ulrich',
//	'url' => 'www.ontoprise.de',
//	'description' => 'Extension to dynamically resize the treeview in the menu',
//	'version' => '0.0.1'
//);
 
//
$wgHooks['UserToggles'][] = 'smwhg_TreeviewResizeAddPreferences';
function smwhg_TreeviewResizeAddPreferences(&$extraToggles) {
	global $wgMessageCache;
    $extraToggles[]= 'treeviewresize';
    $wgMessageCache->addMessage('tog-treeviewresize', 'Auto-resized treeview');
    return true;
}

//Add javascript to the outputpage

$wgHooks['BeforePageDisplay'][] = 'smwhg_TreeviewResizeAddScripts';
//$wgHooks['OutputPageBeforeHTML'][] = 'smwhg_TreeviewResizeAddScripts';
function smwhg_TreeviewResizeAddScripts(&$out, &$text){
		Global $wgScriptPath, $wgUser;
		//Exit if user option is not set
		if($wgUser->getOption('treeviewresize') != '0') {
        	//$text .= "<script src=\"$wgScriptPath/extensions/TreeviewResize/TreeviewResize.js\" type=\"text/javascript\"></script>";
			$out->addScript("<script src=\"$wgScriptPath/extensions/Treeview/TreeviewResize.js\" type=\"text/javascript\"></script>");
		}
        return true;
}