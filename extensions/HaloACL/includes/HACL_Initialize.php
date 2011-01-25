<?php
/**
 * @file
 * @ingroup HaloACL
 */

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

define('HACL_HALOACL_VERSION', '{{$VERSION}} [B{{$BUILDNUMBER}}]');

define('HACL_STORE_SQL', 'HaclStoreSQL');
define('HACL_STORE_LDAP', 'HaclStoreLDAP');



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
$haclgEnableTitleCheck = false;

###
# This flag applies to articles that have or inherit no security descriptor.
#
# true
#    If this value is <true>, all articles that have no security descriptor are 
#    fully accessible for HaloACL. Other extensions or $wgGroupPermissions can
#	 still prohibit access. 
#    Remember that security descriptor are also inherited via categories or 
#    namespaces. 
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
# - HACL_STORE_LDAP (use this if you want to see LDAP groups in your wiki)
##
$haclgBaseStore = HACL_STORE_SQL;

###
# If LDAP is enabled for HaloACL (see $haclgBaseStore) you can choose if LDAP
# groups may be members of HaloACL groups. 
# If this variable <true>, your users can add LDAP groups to their own HaloACL
# groups. 
# If it is <false> the set of LDAP and HaloACL groups remains completely separated.
#
# NOTE: HaloACL groups can never be members of LDAP groups as this would undermine
#       security restrictions that are granted only for LDAP groups. The members
#       of the HaloACL group would inherit the rights of the LDAP groups.
##
$haclgAllowLDAPGroupMembers = true;

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
#$haclgNewUserTemplate = "ACL:Template/NewUserTemplate";

###
# These are the names of the master templates that are installed as quick access
# rights templates for new users.
# Every user can add right templates to his own quick access list. In addition
# the system adds the rights that are specified in this array to every user's
# quick access list when he logs in for the first time. 
# The given master templates are copied to the user's own right space defined
# by the naming convention "ACL:Right/<username>/<Right name>". 
# The master templates must follow the naming convention "ACL:Template/QARMT/<Right name>".
# (Please note the "ACL:Template" depends on the content language, i.e. in german
# it will be "Rechte:Vorlage/QARMT/<Right name>".)
# Example for user "Thomas":
# The template "ACL:Template/QARMT/Private use" will be copied to 
# "ACL:Right/Thomas/Private use". 
# The master templates is are normal security descriptors that can contain the 
# variable "{{{user}}}" that will be replaced by the user's name. 
/*
$haclgDefaultQuickAccessRightMasterTemplates = array(
	"ACL:Template/QARMT/Private use",
	"ACL:Template/QARMT/Public read",
	"ACL:Template/QARMT/Public form edit",
	"ACL:Template/QARMT/Public edit",
	"ACL:Template/QARMT/Public full access",
);
*/

##
# If $haclgEvaluatorLog is <true>, you can specify the URL-parameter "hacllog=true".
# In this case HaloACL echos the reason why actions are permitted or prohibited.
#
$haclgEvaluatorLog = false;

##
# This key is used for protected properties in Semantic Forms. SF has to embed
# all values of input fields into the HTML of the form, even if fields are protected
# and not visible to the user (i.e. user has no right to read.) The values of
# all protected fields are encrypted with the given key.
# YOU SHOULD CHANGE THIS KEY AND KEEP IT SECRET. 
$haclgEncryptionKey = "Es war einmal ein Hase.";


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

###
# 
# If $haclgUseFeaturesForGroupPermissions is <true> the features for 
# "Global Permissions" that are defined in $haclgFeature will be used in the
# GUI on "Special:HaloACL". The default values in $haclgFeature will overwrite
# other conflicting settings in $wgGroupPermissions for all anonymous and 
# registered users.
# If you just want to use $wgGroupPermissions with HaloACL and no GUI support
# set $haclgUseFeaturesForGroupPermissions=false.
#
$haclgUseFeaturesForGroupPermissions = true;

// Definition of features in the "Global Permissions" tab of Special:HaloACL
// Do not remove the surrounding if-condition!

$haclgFeature = array();
if ($haclgUseFeaturesForGroupPermissions === true) {
	$haclgFeature['read']['systemfeatures'] = "read";
	$haclgFeature['read']['name'] = "Read";
	$haclgFeature['read']['description'] = "This is the feature for reading articles.";
	$haclgFeature['read']['permissibleBy'] = "admin"; // The other alternative would be "all"
	$haclgFeature['read']['default'] = "permit"; // The other alternative would be "deny"
	
	$haclgFeature['upload']['systemfeatures'] = "upload|reupload|reupload-own|reupload-shared|upload_by_url";
	$haclgFeature['upload']['name'] = "Upload";
	$haclgFeature['upload']['description'] = "This is the feature for uploading files into the wiki.";
	$haclgFeature['upload']['permissibleBy'] = "admin"; // The other alternative would be "all"
	$haclgFeature['upload']['default'] = "deny"; // The other alternative would be "deny"
	
	$haclgFeature['edit']['systemfeatures'] = "edit|formedit|annotate|wysiwyg|createpage|delete|rollback|createtalk|move|movefile|move-subpages|move-rootuserpages|editprotected";
	$haclgFeature['edit']['name'] = "Edit";
	$haclgFeature['edit']['description'] = "This is the feature for editing articles.";
	$haclgFeature['edit']['permissibleBy'] = "admin"; // The other alternative would be "all"
	$haclgFeature['edit']['default'] = "deny"; // The other alternative would be "deny"
	
	$haclgFeature['createaccount']['systemfeatures'] = "createaccount";
	$haclgFeature['createaccount']['name'] = "Create account";
	$haclgFeature['createaccount']['description'] = "This is the feature for creating user accounts.";
	$haclgFeature['createaccount']['permissibleBy'] = "admin"; // The other alternative would be "all"
	$haclgFeature['createaccount']['default'] = "permit"; // The other alternative would be "deny"
	
	$haclgFeature['manage']['systemfeatures'] = "import|importupload|ontologyediting|bigdelete|deletedhistory|undelete|browsearchive|mergehistory|protect|block|blockemail|hideuser|userrights|userrights-interwiki|markbotedits|patrol|editinterface|editusercssjs|suppressrevision|deleterevision|gardening";
	$haclgFeature['manage']['name'] = "Management";
	$haclgFeature['manage']['description'] = "This is the feature for managing wiki articles.";
	$haclgFeature['manage']['permissibleBy'] = "admin"; // The other alternative would be "all"
	$haclgFeature['manage']['default'] = "deny"; // The other alternative would be "deny"
	
	$haclgFeature['administrate']['systemfeatures'] = "siteadmin|trackback|unwatchedpages";
	$haclgFeature['administrate']['name'] = "Administration";
	$haclgFeature['administrate']['description'] = "This is the feature for administrating the wiki.";
	$haclgFeature['administrate']['permissibleBy'] = "admin"; // The other alternative would be "all"
	$haclgFeature['administrate']['default'] = "deny"; // The other alternative would be "deny"
	
	$haclgFeature['technical']['systemfeatures'] = "purge|minoredit|nominornewtalk|noratelimit|ipblock-exempt|proxyunbannable|autopatrol|apihighlimits|writeapi|suppressredirect|autoconfirmed|emailconfirmed";
	$haclgFeature['technical']['name'] = "Technical";
	$haclgFeature['technical']['description'] = "This is the feature for technical issues.";
	$haclgFeature['technical']['permissibleBy'] = "admin"; // The other alternative would be "all"
	$haclgFeature['technical']['default'] = "deny"; // The other alternative would be "deny"
}


$haclgDynamicSD = array(
    array(
        "user"     => "#",
        "category" => "Project",
        "sd"       => "ACL:Right/SDForNewProject",
        "allowUnauthorizedSDChange" => false
    )
);


$wgGroupPermissions['*']['propertyread'] = true;
$wgGroupPermissions['*']['propertyformedit'] = true;
$wgGroupPermissions['*']['propertyedit'] = true;
$wgGroupPermissions['*']['formedit'] = true;
$wgGroupPermissions['*']['annotate'] = true;
$wgGroupPermissions['*']['wysiwyg'] = true;

// add rights that are newly available with the haloACL
$wgAvailableRights[] = 'propertyread';
$wgAvailableRights[] = 'propertyformedit';
$wgAvailableRights[] = 'propertyedit';

// The logout page must always be accessible
$wgWhitelistRead[] = "Special:UserLogout";

// Tell the script manager, that we need prototype
global $smgJSLibs; 
$smgJSLibs[] = 'prototype';
$smgJSLibs[] = 'jquery'; 
$smgJSLibs[] = 'json'; 
