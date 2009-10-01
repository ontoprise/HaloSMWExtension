<?php
/*  Copyright 2009, ontoprise GmbH
*  This file is part of the HaloACL-Extension.
*
*   The HaloACL-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The HaloACL-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This is the main entry file for the Halo-Access-Control-List extension.
 * It contains mainly constants for the configuration of the extension. This 
 * file has to be included in LocalSettings.php to enable the extension. The 
 * constants defined here can be overwritten in LocalSettings.php. After that
 * the function enableHaloACL() must be called.
 * 
 * @author Thomas Schweitzer
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the HaloACL extension. It is not a valid entry point.\n" );
}

define('HACL_HALOACL_VERSION', '1.0');

define('HACL_STORE_SQL', 'HaclStoreSQL');
// constant for special schema properties


###
# This is the path to your installation of HaloACL as seen on your
# local filesystem. Used against some PHP file path issues.
##
$haclgIP = $IP . '/extensions/HaloACL';
##

###
# This is the path to your installation of HaloACL as seen from the
# web. Change it if required ($wgScriptPath is the path to the base directory
# of your wiki). No final slash.
##
$haclgHaloScriptPath = $wgScriptPath . '/extensions/HaloACL';

###
# Set this variable to false to disable the patch that checks all titles
# for accessibility. Unfortunately, the Title-object does not check if an article
# can be accessed. A patch adds this functionality and checks every title that is 
# created. If a title can not be accessed, a replacement title called "Permission
# denied" is returned. This is the best and securest way of protecting an article,
# however, it slows down things a bit.
##
$haclgEnableTitleCheck = true;

###
# true
#    If this value is <true>, all articles that have no security descriptor are 
#    fully accessible. Remember that security descriptor are also inherited via 
#    categories or namespaces. 
# false
#    If it is <false>, no access is granted at all. Only the latest author of an 
#    article can create a security descriptor. 
$haclgOpenWikiAccess = true;

###
# true
#    If this value is <true>, semantic properties can be protected.  
# false
#    If it is <false>, semantic properties are not protected even if they have 
#	 security descriptors.  
$haclgProtectProperties = true;

###
# By design several databases can be connected to HaloACL. (However, in the first
# version there is only an implementation for MySQL.) With this variable you can
# specify which store will actually be used.
# Possible values:
# - HACL_STORE_SQL
##
$haclgBaseStore = HACL_STORE_SQL;

###
# This array contains the names of all namespaces that can not be protected by
# HaloACL. This bears the risk that users can block all articles of a namespace 
# if it has no security descriptor yet. 
# On the other hand, if each namespace would have a security descriptor, then
# all authorized users for that namespace will be able to access all articles
# in that namespace, even if security descriptors for individual articles define
# another set authorized users.
# The name of the main namespace is 'Main'.
$haclgUnprotectableNamespaces = array('Main');

###
# This is the name of the master template that is used as default rights template
# for new users.
# Every user can define his own default rights for new pages. He does this in a
# security descriptor with the naming convention "ACL:Template/<username>". The 
# content of this article is assigned to security descriptors that are automatically
# generated for new pages. 
# However, for new users there is no default template. With this setting you can
# specify a master template (a name of an article) that is used to create a 
# default template for new users.
# The master template is a normal security descriptor that can contain the 
# variable "{{{user}}}" that will be replaced by the user's name. 
//$haclgNewUserTemplate = "ACL:Template/NewUserTemplate";

##
# This is an array of right templates that are added to the quick access list of 
# every user who logs in to the system.
$haclgDefaultQuickAccessRights = array(
//	"ACL:Right/Thomas (Private)"
);

# load global functions
require_once('HACL_GlobalFunctions.php');

###
# If you already have custom namespaces on your site, insert
#    $haclgNamespaceIndex = ???;
# into your LocalSettings.php *before* including this file. The number ??? must
# be the smallest even namespace number that is not in use yet. However, it
# must not be smaller than 100.
##
haclfInitNamespaces();

// mediawiki-groups that may access whitelists
global $haclWhitelistGroups;
$haclWhitelistGroups = array('sysop','bureaucrat');

// mediawiki-groups that may access other user template
// mediawiki-groups that may access whitelists
global $haclCrossTemplateAccess;
$haclCrossTemplateAccess = array('sysop','bureaucrat');

$wgGroupPermissions['*']['propertyread'] = true;
$wgGroupPermissions['*']['propertyformedit'] = true;
$wgGroupPermissions['*']['propertyedit'] = true;
$wgGroupPermissions['*']['formedit'] = true;
$wgGroupPermissions['*']['annotate'] = true;

#include our ajax_connecotr
require_once('HACL_GenericPanel.php');
require_once('HACL_AjaxConnector.php');
require_once('HACL_helpPopup.php');
require_once('HACL_Toolbar.php');


