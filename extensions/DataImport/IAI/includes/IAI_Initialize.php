<?php
/*  Copyright 2009, ontoprise GmbH
*  This file is part of the Interwiki-Article-Import module (IAI) of the 
*  Data-Import-Extension.
*
*   The Data-Import-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Data-Import-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @file
  * @ingroup DIInterWikiArticleImport
  * 
  * This is the main entry file for the Interwiki-Article-Import module.
 * It contains mainly constants for the configuration of the extension. This 
 * file has to be included in LocalSettings.php to enable the module. The 
 * constants defined here can be overwritten in LocalSettings.php. After that
 * the function enableIAI() must be called.
 * 
 * @author Thomas Schweitzer
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the IAI extension. It is not a valid entry point.\n" );
}

define('IAI_VERSION', '0.1');


###
# This is the path to your installation of IAI as seen on your
# local filesystem. Used against some PHP file path issues.
##
$iaigIP = $IP . '/extensions/DataImport/IAI';
##

###
# This is the path to your installation of IAI as seen from the
# web. Change it if required ($wgScriptPath is the path to the base directory
# of your wiki). No final slash.
##
$iaigScriptPath = $wgScriptPath . '/extensions/DataImport/IAI';

# load global functions
require_once('IAI_GlobalFunctions.php');

###
# If you already have custom namespaces on your site, insert
#    $iaigNamespaceIndex = ???;
# into your LocalSettings.php *before* including this file. The number ??? must
# be the smallest even namespace number that is not in use yet. However, it
# must not be smaller than 100.
# IAI stores reports in this namespace.
##
iaifInitNamespaces();

###
# URL of the Mediawiki-API of the source wiki e.g. "http://en.wikipedia.org/w/"
# for the english wikipedia. This API will be used by the IAI_ImportBot when
# articles are updated.
#
##
//$iaigWikiApi = "http://en.wikipedia.org/w/";
$iaigWikiApi = "http://localhost/develwiki/";

###
# If an article is changed with a Mediawiki API call, IAI can update the dependencies
# of that article with the IAI_ImportBot. Set this variable to <true> to enable
# this feature. The gardening extension must be enabled in this case.
#
##
$iaigUpdateDependenciesAfterAPIedit = true;

###
# IAI needs a folder for writing temporary files when importing images. 
# In this variable, you can specify a folder with write access.
#
$iaigTempDir = "";

