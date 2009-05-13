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
 * Internationalization file for Halo ACL
 *
 */

$messages = array();

/** 
 * English
 */
$messages['en'] = array(
	/* general/maintenance messages */
	'haloacl' 			=> 'HaloACL',
	'hacl_special_page' => 'HaloACL',  // Name of the special page for administration
	'specialpages-group-hacl_group'	=> 'HaloACL',
	'hacl_tt_initializeDatabase'	=> 'Initialize or update the database tables for HaloACL.',
	'hacl_initializeDatabase'		=> 'Initialize database',
	'hacl_db_setupsuccess'			=> 'Setting up the database for HaloACL was successful.',
	'hacl_haloacl_return'			=> 'Return to ',
	/* Messages for Special:ACL */
	'hacl_tab_create_acl' => 'Create ACL',
	'hacl_tab_manage_acls' => 'Manage ACLs',
	'hacl_tab_manage_user' => 'Manage User',
	'hacl_tab_manage_whitelist' => 'Manage Whitelists',
	/* Messages for 'Create ACL' tab */
	'hacl_create_acl_subtab1' => 'Create standard ACL',
	'hacl_create_acl_subtab2' => 'Create ACL template',
	'hacl_create_acl_subtab3' => 'Create ACL default user template',
	/* Messages for sub tab 'Create standard ACL' ("csa") */

	/* Messages for sub tab 'Create ACL template' ("cat") */

	/* Messages for sub tab 'Create ACL default user template ("dut")' */
	'hacl_create_acl_dut_headline' => 'Create Access Control List (ACL) default user template',
	'hacl_create_acl_dut_info' => 'In this tab you can create your own ACL default user template.<br/>New articles are automatically protected by this temklate',

	'hacl_create_acl_dut_general' => '1. General',
	'hacl_create_acl_dut_general_definefor' => 'Define for:',
	'hacl_create_acl_dut_general_private_use' => 'Private use',
	'hacl_create_acl_dut_general_all' => 'All users',
	'hacl_create_acl_dut_general_specific' => 'Specific user and/or groups of users',

	'hacl_create_acl_dut_rights' => '2. Rights',
	'hacl_create_acl_dut_button_create_right' => 'Create right',
	'hacl_create_acl_dut_button_add_template' => 'Add right template',
	'hacl_create_acl_dut_new_right_legend' => 'Right: New right $1',
	'hacl_create_acl_dut_new_right_legend_status_saved' => '[saved]',
	'hacl_create_acl_dut_new_right_legend_status_notsaved' => '[Not saved]',
	'hacl_create_acl_dut_new_right_name' => 'Name:',
	'hacl_create_acl_dut_new_right_defaultname' => 'new right:',
	'hacl_create_acl_dut_new_right_rights' => 'Rights:',
	#TODO: can we get this from s.w. else???
	'hacl_create_acl_dut_new_right_fullaccess' => 'Full access',
	'hacl_create_acl_dut_new_right_read' => 'read',
	'hacl_create_acl_dut_new_right_ewf' => 'edit with form',
	'hacl_create_acl_dut_new_right_edit' => 'edit',
	'hacl_create_acl_dut_new_right_create' => 'create',
	'hacl_create_acl_dut_new_right_move' => 'move',
	'hacl_create_acl_dut_new_right_delete' => 'delete',
	'hacl_create_acl_dut_new_right_annotate' => 'annotate',
	'hacl_create_acl_dut_new_right_select_user' => 'Select users/groups',
	'hacl_create_acl_dut_new_right_assigned_user' => 'Assigned users/groups',
	'hacl_create_acl_dut_new_right_column_users' => 'Groups and users',
	'hacl_create_acl_dut_new_right_column_filter' => 'Filter',
	'hacl_create_acl_dut_new_right_column_user' => 'User',
	'hacl_create_acl_dut_new_right_desc' => 'Description:',
	'hacl_create_acl_dut_new_right_button_save' => 'Save',
	'hacl_create_acl_dut_button_next' => 'Next',

	'hacl_create_acl_dut_mod' => '3. Modification rights',
	'hacl_create_acl_dut_mod_info' => 'If you like you can specify who can modify this Access Control List.<br/>Please note: By default the creator of a right has all rights (~"Owner right").',
	'hacl_create_acl_dut_mod_legend' => 'Modification rights',

	'hacl_create_acl_dut_save' => '4. Save Access Control List',
	'hacl_create_acl_dut_save_info' => 'The system automatically generates a name for the system.',
	'hacl_create_acl_dut_save_name' => 'ACL Name:',
	'hacl_create_acl_dut_save' => '',

	'hacl_create_acl_dut_save_button_discard' => 'Discard ACL',
	'hacl_create_acl_dut_save_button_save' => 'Save ACL',


	/* Messages for 'Manage ACLs' tab */
	'hacl_manage_acls_subtab1' => 'Manage all ACLs',
	'hacl_manage_acls_subtab2' => 'Manage own default user template',
	/* Messages for 'Manage User' tab */
	/* Messages for 'Manage Whitelist' tab */
	'hacl_whitelist_headline' => 'Manage Whitelists',
	'hacl_whitelist_info' => 'In this tab you can edit and create whitelists.',
	'hacl_whitelist_filter' => 'Filter:',
	'hacl_whitelist_pageset_header' => 'Page',
	'hacl_whitelist_pagename' => 'Page-Name:',
	'hacl_whitelist_addbutton' => 'Add Page',
	

);

/** 
 * German
 */
$messages['de'] = array(
	/* general/maintenance messages */
	'haloacl' 			=> 'HaloACL',
	'hacl_special_page' => 'HaloACL',   // Name of the special page for administration
	'specialpages-group-hacl_group'	=> 'HaloACL',
	'hacl_tt_initializeDatabase'	=> 'Initialisiert oder aktualisiert die Datenbanktabellen für HaloACL.',
	'hacl_initializeDatabase'		=> 'Datenbank initialisieren',
	'hacl_db_setupsuccess'			=> 'Die Datenbank für HaloACL wurde erfolgreich erstellt.',
	'hacl_haloacl_return'			=> 'Zurück zu ',
	/* Messages for 'Create ACL' tab */
	/* Messages for 'Manage ACLs' tab */
	/* Messages for 'Manage User' tab */
	/* Messages for 'Manage Whitelists' tab */
	'hacl_whitelists_headline' => '',
	'hacl_whitelists_info' => '',
	'hacl_whitelists_filter' => '',
	'hacl_whitelists_tabheader' => '',
	'hacl_whitelists_addlink' => '',
	'hacl_whitelists_pagename' => '',
	'hacl_whitelists_addbutton' => '',
);