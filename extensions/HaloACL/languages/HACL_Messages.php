<?php
/**
 * @file
 * @ingroup HaloACL_Language
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
 * Internationalization file for Halo ACL
 *
 */

$messages = array();

/** 
 * English
 */
$messages['en'] = array(
	// Messages: Reasons for Access Denied
	'hacl_ad_create_namespace'		=> "You are not allowed to create articles in the namespace '$1'.\n".
					 				   "(The permission was denied by extension HaloACL.)",
	'hacl_ad_access_denied'			=> "You are not allowed to perform the requested action.\n".
					 				   "(The permission was denied by extension HaloACL.)",
	/* general/maintenance messages */
    'haloacl' 			=> 'HaloACL',
    'hacl_special_page' => 'HaloACL',  // Name of the special page for administration
    'specialpages-group-hacl_group'	=> 'Halo Access Control List',
    'hacl_tt_initializeDatabase'	=> 'Initialize or update the database tables for HaloACL.',
    'hacl_initializeDatabase'		=> 'Initialize database',
    'hacl_db_setupsuccess'			=> 'Setting up the database for HaloACL was successful.',
    'hacl_haloacl_return'			=> 'Return to ',
    'hacl_unknown_user'				=> 'The user "$1" is unknown.',
    'hacl_unknown_group'			=> 'The group "$1" is unknown.',
    'hacl_group_overloaded'			=> 'The group "$1" is defined in the wiki and on the LDAP server. The LDAP definition will be used.',
    'hacl_missing_parameter'		=> 'The parameter "$1" is missing.',
    'hacl_missing_parameter_values' => 'There are no valid values for parameter "$1".',
    'hacl_invalid_predefined_right' => 'A rights template with the name "$1" does not exist or it contains no valid rights definition.',
    'hacl_invalid_action'			=> '"$1" is an invalid value for an action.',
    'hacl_too_many_categories'		=> 'This article belongs to multiple categories ($1). Please remove it from them and leave it in only one category!',
    'hacl_wrong_namespace'			=> 'Articles with rights or group definitions must belong to the namespace "ACL".',
    'hacl_group_must_have_members'  => 'A group must have at least one member (group or user).',
    'hacl_group_must_have_managers' => 'A group must have at least one manager (group or user).',
    'hacl_invalid_parser_function'	=> 'The use of the "#$1" function in this article is not allowed.',
    'hacl_right_must_have_rights'   => 'A right or security descriptor must contain rights or reference other rights.',
    'hacl_right_must_have_managers' => 'A right or security descriptor must have at least one manager (group or user).',
    'hacl_whitelist_must_have_pages' => 'The whitelist is empty. Please add pages .',
    'hacl_add_to_group_cat'			=>'This article contains functions that define a group. They will not be taken into effect until you add the category "[[Category:ACL/Group]]".',   
	'hacl_add_to_right_cat'			=> 'This article contains the functions that define a security descriptor or a right template. They will not be taken to effect until you add the category "[[Category:ACL/ACL]]" or "[[Category:ACL/Right]]".',
    'hacl_add_to_whitelist'			=> 'The article contains functions that define a whitelist. This function may only be used in the "ACL:Whitelist" article.',
    'hacl_pf_rightname_title'		=> "===$1===\n",
    'hacl_pf_rights_title'			=> "===Right(s): $1===\n",
    'hacl_pf_rights'				=> ":;Right(s):\n:: $1\n",
    'hacl_pf_right_managers_title'	=> "===Right managers===\n",
    'hacl_pf_predefined_rights_title' => "===Right templates===\n",
    'hacl_pf_whitelist_title' 		=> "===Whitelist===\n",
    'hacl_pf_group_managers_title'	=> "===Group managers===\n",
    'hacl_pf_group_members_title'	=> "===Group members===\n",
    'hacl_assigned_user'			=> 'Assigned users: ',
    'hacl_assigned_groups'			=> 'Assigned groups:',
    'hacl_dynamic_assignees'		=> 'Dynamic assignees',
    'hacl_dyn_assigned_queries'		=> 'Queries for dynamic assignees:',
    'hacl_dyn_assigned_groups'		=> 'Dynamically assigned groups:',
    'hacl_dyn_assigned_users'		=> 'Dynamically assigned users:',
    'hacl_dynamic_members'			=> 'Dynamic members',
    'hacl_dyn_member_queries'		=> 'Queries for dynamic members:',
    'hacl_dyn_member_groups'		=> 'Dynamic group members:',
    'hacl_dyn_member_users'			=> 'Dynamic user members:',
	'hacl_save_group_settings'		=> 'Save group settings',
	'hacl_save_group_settings_first'=> 'Please save the group settings first!',
	'hacl_user_member'				=> 'Users who are member of this group:',
    'hacl_group_member'				=> 'Groups who are member of this group:',
    'hacl_description'				=> 'Description:',
    'hacl_error'					=> 'Errors:',
    'hacl_warning'					=> 'Warnings:',
    'hacl_consistency_errors'		=> '<h2>There are errors in ACL definition</h2>',
    'hacl_definitions_will_not_be_saved' => '(The definitions in this article will not be saved and they will not be taken to effect due to the following errors.)',
	'hacl_will_not_work_as_expected'=> '(Because of the following warnings, the definition will not work as expected.)',
    'hacl_errors_in_definition'		=> 'The definitions in this article have errors. Please refer to the details below!',
    'hacl_anonymous_users'			=> 'anonymous users',
    'hacl_registered_users'			=> 'registered users',
    'hacl_acl_element_not_in_db'	=> 'No entry has been made in the ACL database about this article. It may have been deleted and restored. Please store it again with all the articles that use it ',
    'hacl_whitelist_mismatch'		=> 'The whitelist in this article contains nonexistent articles. Please remove them and save the whitelist again.',
    'hacl_unprotectable_namespace'  => 'This namespace cannot be protected. Please contact the wiki administrator.',

	/* Messages for semantic protection (properties etc.) */

    'hacl_sp_query_modified'		=> "- The query was modified because it contains protected properties.\n",
    'hacl_sp_empty_query'			=> '- Your query consists only of protected properties or has variables at property position. It was not executed. The system administrator can disable the protetction of properties by setting $haclgProtectProperties = false; in the LocalSettings.php'."\n",
    'hacl_sp_results_removed'		=> "- Some results were removed due to access restrictions.\n",
    'hacl_sp_cant_save_article'		=> "'''The article contains the following protected properties:'''\n$1'''You do not have the permission to set their values. Please remove these properties and save again.'''",
	'hacl_protected_property_error' => "The value of a protected property is hidden.",

	/* Messages for Special:ACL */
    'hacl_tab_create_acl' => 'Create ACL',
    'hacl_tab_manage_acls' => 'Manage ACLs',
    'hacl_tab_manage_user' => 'Manage Groups',
    'hacl_tab_manage_whitelist' => 'Manage Whitelist',

	/* Messages for 'Create ACL' tab */
    'hacl_create_acl_subtab1' => 'Create standard ACL',
    'hacl_create_acl_subtab2' => 'Create ACL template',
    'hacl_create_acl_subtab3' => 'Create ACL default user template',
	/* Messages for sub tab 'Create standard ACL' ("csa") */


	/* Messages for sub tab 'Create ACL template' ("cat") */

	/* Messages for sub tab 'Create ACL default user template ("dut")' */
    'hacl_create_acl_dut_headline' => 'Create Access Control List (ACL) default user template',
    'hacl_create_acl_dut_info' => 'You can create your own ACL default user template in this tab.<br />New articles will be protected by this template automatically',

    'hacl_create_acl_dut_general' => '1. General',
    'hacl_create_acl_dut_general_definefor' => 'Define for:',
    'hacl_create_acl_dut_general_private_use' => 'Me',
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
    'hacl_create_acl_dut_mod_info' => 'Define the Access Control List modification rights.',
    'hacl_create_acl_dut_mod_legend' => 'Modification rights',

    'hacl_create_acl_dut_save' => '4. Save Access Control List',
    'hacl_create_acl_dut_save_info' => 'The system will automatically generate a system name.',
    'hacl_create_acl_dut_save_name' => 'ACL Name:',
    'hacl_create_acl_dut_save' => '',

    'hacl_create_acl_dut_save_button_discard' => 'Discard ACL',
    'hacl_create_acl_dut_save_button_save' => 'Save ACL',


	/* Messages for 'Manage ACLs' tab */
    'hacl_manage_acls_subtab1' => 'Manage all ACLs',
    'hacl_manage_acls_subtab2' => 'Manage own default user template',
	/* Messages for 'Manage User' tab */
	/* Messages for 'Manage Whitelist' tab */
    'hacl_whitelist_headline' => 'Manage Whitelist',
    'hacl_whitelist_info' => 'You may edit and create the Whitelist in this tab',
    'hacl_whitelist_filter' => 'Filter',
    'hacl_whitelist_pageset_header' => 'Page',
    'hacl_whitelist_pagename' => 'Page-Name:',
    'hacl_whitelist_addbutton' => 'Add page',
    'hacl_whitelist_pageremoved' => 'Page removed from whitelist.',


    'hacl_createRightContent_help' => 'Click on <strong>&quot;Create right&quot;</strong> if you want to create a new right.<br /><br />Note: <br />You may create multiple rights e.g.:<br /> Right1 = Read only for User1 + Right 2 = Full access for User2 etc.<br /><br /> Choose <strong>&quot;Add right template&quot;</strong> if you wish to select a predefined ACL.',

    'hacl_haloacl_tab_section_header_title' =>  'Rights',

    'hacl_createModificationRightContent_help' => 'You may now choose other groups and/or users who you wish to grant permission to maintain and modify this ACL. <br/><br/>Note:<br/>The current user is granted the modification rights by default.',
    'hacl_haloacl_tab_section_header_mod_title' => 'Modification Rights',
    'hacl_haloacl_mod_1' => 'Expand the box below, if you like to allow other users or groups to modify this access control list.',
    'hacl_haloacl_mod_2' => 'Attention: You do not have this ACL’s modification rights at this time!',
    'hacl_haloacl_mod_3' => 'Please select at least one group or user who has the permission to modify this ACL',

    'hacl_createSaveContent_1' => 'Save ACL',
    'hacl_createSaveContent_2' => 'ACL name:',
    'hacl_createSaveContent_3' => 'ACL saved',
    'hacl_createSaveContent_4' => 'An error occured when saving the ACL',

    'hacl_createManageACLContent_2' => 'You have edit rights to all the ACLs in this tab.',
    'hacl_createManageACLContent_1' => 'Manage existing ACLs',

    'hacl_createManageUserTemplateContent_1' => 'Manage your own Default ACL User Template',

    'hacl_createGeneralContent_1' => 'You can create an Access Control List (ACL) in the following four steps. Click the help icon at any time to get help about each step.',
    'hacl_createGeneralContent_2' => 'General',
    'hacl_createGeneralContent_3' => 'Protect:',
    'hacl_createGeneralContent_4' => 'Page',
    'hacl_createGeneralContent_5' => 'Property',
    'hacl_createGeneralContent_6' => 'Pages&nbsp;in&nbsp;Namespace',
    'hacl_createGeneralContent_7' => 'Pages&nbsp;in&nbsp;Category',
    'hacl_createGeneralContent_8' => 'Name:',
    'hacl_createGeneralContent_9' => 'Define for:',
    'hacl_createGeneralContent_10' => 'Me',
    'hacl_createGeneralContent_11' => 'Individual user and/or groups of user',
    'hacl_createGeneralContent_12' => 'All Users',
    'hacl_createGeneralContent_13' => 'All Registered Users',
    'hacl_createGeneralContent_14' => 'All Anonymous Users',
    'hacl_createGeneralContent_message1' => 'Please set what you would like to protect. ',
    'hacl_createGeneralContent_message2' => 'Please set a name. ',
    'hacl_createGeneralContent_message3' => 'Please set for whom the acl is defined for .',
    'hacl_createGeneralContent_message4' => 'Some data missing',

    'hacl_createACLContent_1' => '<strong>General:</strong><br />An Access Control List (ACL) is a page which is mapped to the element that  you wish to protect. Lets say that you want to protect the page &quot;MyNotes&quot;. A new page called &quot;ACL:Page/MyNotes&quot; containing this articles access control list will be created.<br /> The ACL article always consists of:<br /> - the type you wish to protect e.g. page, property etc<br /> - the right settings e.g. read access for userX<br /> - the definitions about the  persons who have the ACLs modification rights<br /><br /> <strong>Protect:</strong><br />Choose the type you wish to protect :<br />- Page<br />- Property<br />- All pages in a category<br />- All pages in a namespace <br /><br /> <strong>Name:</strong><br /> Enter the name or use the autocomplete feature to specify the item that you wish to protect. <br /><br />Note:<br />If you came from a page, you will find the name of the page in the text entry box.<br /><br />',
    'hacl_createACLContent_2' => 'General',

    'hacl_createACLTemplateContent_1' => '<strong>General:</strong><br />An Access Control List template is a predefined ACL. Once you create a template, you can assign this template to any type of element you wish to protect e.g. page, property, etc. You may additionally use the ACL templates in your quick access list on every page. You just need to select the templates you want to have in the quick access list from the manage quick access ACL tab. <br /><br /><strong>Name:</strong><br /> Enter the name of the template you want to create.<br /><br />',
    'hacl_createACLTemplateContent_2' => 'General',

    'hacl_createUserTemplateContent_1' => '<strong>General:</strong><br />A default ACL user template is an Access Control List which will be used as your default ACL whenever you create new pages within the wiki. You are free to change the access right state from your default ACL template to unprotected at anytime.<br /><br />',
    'hacl_createUserTemplateContent_2' => 'General',

    'hacl_manageUserGroupPanel_1' => 'Name:',

    'hacl_rightsPanel_1' => 'Name:',
    'hacl_rightsPanel_2' => 'Rights:',
    'hacl_rightsPanel_3' => 'Right description:',
    'hacl_rightsPanel_4' => 'Autogenerate description text:',
    'hacl_rightsPanel_5' => 'on',
    'hacl_rightsPanel_6' => 'off',
    'hacl_rightsPanel_7' => 'autogenerated',
    'hacl_rightsPanel_8' => 'Delete right',
    'hacl_rightsPanel_9' => 'Reset right',
    'hacl_rightsPanel_10' => 'Save right',
    'hacl_rightsPanel_11' => 'Modification Rights',
    'hacl_rightsPanel_12' => 'modification rights',
    'hacl_rightsPanel_13' => 'Private right for user',

    'hacl_rightsPanel_right_fullaccess' => 'Full access',
    'hacl_rightsPanel_right_read' => 'Read',
    'hacl_rightsPanel_right_edit' => 'Edit',
    'hacl_rightsPanel_right_editfromform' => 'Edit with form',
    'hacl_rightsPanel_right_WYSIWYG' => 'WYSIWYG',
    'hacl_rightsPanel_right_create' => 'Create',
    'hacl_rightsPanel_right_move' => 'Move',
    'hacl_rightsPanel_right_delete' => 'Delete',
    'hacl_rightsPanel_right_annotate' => 'Annotate',

    'hacl_rightsPanel_allUsersRegistered' => 'all users registered',
    'hacl_rightsPanel_allAnonymousUsers' => 'all anonymous users',
    'hacl_rightsPanel_allUsers' => 'all users',


    'hacl_rightPanelSelectDeselectTab_1' => 'Groups and Users',
    'hacl_rightPanelSelectDeselectTab_2' => 'Filter in groups:',
    'hacl_rightPanelSelectDeselectTab_3' => 'Users',
    'hacl_rightPanelSelectDeselectTab_4' => 'User',
    'hacl_rightPanelSelectDeselectTab_5' => 'Filter:',

    'hacl_rightList_All' => 'All',
    'hacl_rightList_StandardACLs' => 'Standard ACLs',
    'hacl_rightList_Page' => 'Page',
    'hacl_rightList_Category' => 'Category',
    'hacl_rightList_Property' => 'Property',
    'hacl_rightList_Namespace' => 'Namespace',
    'hacl_rightList_ACLtemplates' => 'ACL templates',
    'hacl_rightList_Defaultusertemplates' => 'Default user templates',

    'hacl_rightList_1' => 'Existing ACLs',

    'hacl_SDRightsPanelContainer_1' => 'Editing:',
    'hacl_SDRightsPanelContainer_2' => 'Delete right',
    'hacl_SDRightsPanelContainer_3' => 'Discard Changes',
    'hacl_SDRightsPanelContainer_4' => 'Save ACL',

    'hacl_RightsContainer_1' => '[ACL templates]',
    'hacl_RightsContainer_2' => 'Use selected template',

    'hacl_saveTempGroup_1' => 'group saved',

    'hacl_deleteSecurityDescriptor_1' => 'Right successfully deleted.',

    'hacl_manageUser_1' => 'Manage ACL group and user',
    'hacl_manageUser_2' => 'This tab lets you create, edit and delete ACL groups. An ACL group is a collection of users. This group may also include other user groups.<br /> You can use these groups to easily assign rights to a specific set of users whenever you create an ACL.',
    'hacl_manageUser_3' => 'Add new group',
    'hacl_manageUser_4' => 'Add subgroup',
    'hacl_manageUser_5' => 'Add subgroup on same level',
    'hacl_manageUser_6' => 'Existing groups',
    'hacl_manageUser_7' => 'Delete selected',
    'hacl_manageUser_8' => 'ACL Group Explorer',
    'hacl_manageUser_9' => 'Editing',
    'hacl_manageUser_10' => 'Save group',

    'hacl_whitelist_1' => 'Manage whitelisted pages',
    'hacl_whitelist_2' => 'This tab lets you create and delete whitelist entries.',
    'hacl_whitelist_3' => 'Whitelisted Pages',
    'hacl_whitelist_4' => 'Add page to whitelist:',

    'hacl_deleteGroup_1' => 'The marked items have been deleted successfully.',

    'hacl_quickACL_1' => 'Manage quick access ACLs',
    'hacl_quickACL_2' => 'This tab has a list of all the ACL templates that you can use in your quick access list. This list defines the ACL´s that will be in the dropdown box that is on the top of every page in the edit or creation mode. You may select up to 15 ACL templates.',
    'hacl_quickACL_3' => 'Quick access ACLs',
    'hacl_quickACL_4' => 'QuickACL saved',

    'hacl_general_nextStep' => 'Next Step',
    'hacl_nothing_deleted' => 'No elements have been deleted',
    'hacl_quickacl_limit' => 'Only 15 templates are allowed in the QuickAccessList.',
    'hacl_nodefusertpl'=>"no default template for user",
    'hacl_nodefusertpl_link'=>"click here to create",
    'hacl_showing_text'=>"Showing",
    'hacl_showing_elements_text'=>"element(s)",
    'hacl_selected'=>"Selected",

    'hacl_discard_changes' => "Discard changes",
    'hacl_save_acl' => "Save ACL",
    'hacl_create_right' => "Create right",
    'hacl_add_template' => "Add template",
    'hacl_groupsettings' => "Group settings",
	'hacl_popup_invalid_no_group_members' => "Please select at least one user or group!",

    'hacl_saved' => "Saved",
    'hacl_notsaved' => "Not saved",
    'hacl_default' => "Default",

    'hacl_tooltip_enternameforexisting' => "Enter a name of an existing item you want to protect",
    'hacl_tooltip_eneternamefortemplate' => "Enter a name for the template",

    'hacl_tooltip_clickto_delete_right'=> "Click here to delete the right",
    'hacl_tooltip_clickto_reset_right'=> "Click here to reset the right",
    'hacl_tooltip_clickto_save_right'=> "Click here to save the right",
    'hacl_tooltip_clickto_save_modright'=> "Click here to save the modificationright",

	'hacl_root_group' => "Groups",

    'hacl_delete_selected' => "Delete selected",
    'hacl_select' => "Select",

    'hacl_deletetplfromacl' => "Delete template from ACL",
    'hacl_addtpltoacl' => "Add template to ACL",

	'hacl_tpl_already_exists' => "The template already exists",
    'hacl_setexisting_name' => "Please enter a name of an existing element",
    'hacl_already_protected' => "The element is already protected. Please go to ManageACLs to change the ACL.",
    'hacl_already_protected_by_ns_or_cat' => "The element is already protected by a category or a namespace. You are not entitled to add a new right.",
	'hacl_showacls' => "Show ACLs",
    'hacl_groupdescription'=> 'Group description',
    'hacl_advancedToolbarTooltip'=>'Click here to open advanced access rights definition in a new tab',
    'hacl_reset_groupsettings'=>'Reset group settings',
    'hacl_createSavehelpopup1' => 'The ACL name is autogenerated. Please click Save ACL to save the ACL.',
    'hacl_help_popup'=>'Help',
    'hacl_jumptoarticle'=>"Jump to article.",
    'hacl_no_groups_or_users' => "<h4>&nbsp;&nbsp;No groups or users have been selected.</h4><h4>&nbsp;&nbsp;Please select a group or an user.</h4>",
    'hacl_protected_label'=>'protected',
    'hacl_unprotected_label'=>'unprotected',
    'hacl_delete_link_header' => 'Delete',

	//--- Messages for Manage Groups ---
	'hacl_group_exists'  => "The group $1 already exists. (Its type is $2.)\n You can not create two groups with the same name.",
	'hacl_group_no_name' => "You entered no group name. A name is required to create a new group.",

	//--- Messages for global permissions ---
	'hacl_gp_ge_group'		=> "Group",
	'hacl_gp_ge_info'		=> "Info",
	'hacl_gp_ge_permission'	=> "Permission",
	'hacl_gp_group_filter'	=> "Filter:",
	'hacl_gp_intro'			=> "In this Tab you can define global permissions for HaloACL groups.<br />".
							   "These permissions affect features of the whole system and not only certain content.",
	'hacl_gp_lgr_intro'		=> "You can find a list of all groups with their permissions here: ",
	'hacl_gp_listgrouprights'
							=> "List of group permissions",
	'hacl_gp_permission'	=> "Permission:",
	'hacl_gp_set_permission'=> "Set permission ",
	'hacl_gp_select_permission' => "Please select the permission you want to assign to groups.",
	'hacl_gp_default'			=> "The default setting for all users is: ",
	'hacl_gp_permit'			=> "permitted",
	'hacl_gp_deny'				=> "denied",
	'hacl_gp_comprises_features'=> "This permission comprises the following system features:",
	'hacl_gp_discard'			=> "Discard changes",
	'hacl_gp_save'				=> "Save global permissions",
	'hacl_gp_hint'				=> "Hint:",
	'hacl_gp_check_default'		=> "Apply default settings",
	'hacl_gp_check_permit'		=> "Grant permission",
	'hacl_gp_check_deny'		=> "Deny permission",
	'hacl_gp_all_users'			=> "All users",
	'hacl_gp_registered_users'	=> "Registered users",
	'hacl_gp_permissions_saved' => "The permissions were successfully saved.",
	'hacl_gp_has_permissions'	=> "This group has the following permission(s):",
	'hacl_gp_no_features_for_user'
								=> "We are sorry.<br />You must be an administrator or bureaucrat to edit global permission.",
	'hacl_gp_no_features_defined'
								=> "No features are defined for global permissions. <br />".
								   "Please edit <tt>/extensions/HaloACL/includes/HACL_Initialize.php</tt>: <br />".
								   "Set <br /> <tt>\$haclgUseFeaturesForGroupPermissions = true;</tt> <br />".
								   "and define features in <tt>\$haclgFeature</tt>."

);

/** 
 * German
 */
$messages['de'] = array(
	'hacl_ad_create_namespace'		=> "Sie sind nicht berechtigt, Artikel im Namensraum '$1' zu erzeugen.\n".
					 				   "(Das Recht wurde durch die Erweiterung HaloACL entzogen.)",
	'hacl_ad_access_denied'			=> "Sie sind nicht berechtigt, die angeforderte Operation durchzuführen..\n".
					 				   "(Das Recht wurde durch die Erweiterung HaloACL entzogen.)",


	/* general/maintenance messages */
    'haloacl' 			=> 'HaloACL',
    'hacl_special_page' => 'HaloACL',   // Name of the special page for administration
    'specialpages-group-hacl_group'	=> 'Halo Zugriffskontrolle',
    'hacl_tt_initializeDatabase'	=> 'Initialisiert oder aktualisiert die Datenbanktabellen für HaloACL.',
    'hacl_initializeDatabase'		=> 'Datenbank initialisieren',
    'hacl_db_setupsuccess'			=> 'Die Datenbank für HaloACL wurde erfolgreich erstellt.',
    'hacl_haloacl_return'			=> 'Zurück zu ',
    'hacl_unknown_user'				=> 'Der Benutzer "$1" ist unbekannt.',
    'hacl_unknown_group'			=> 'Die Gruppe "$1" ist unbekannt.',
    'hacl_group_overloaded'			=> 'Die Gruppe "$1" ist im Wiki und auf dem LDAP-Server definiert. Die LDAP-Definition wird verwendet werden.',
	'hacl_missing_parameter'		=> 'Der Parameter "$1" fehlt.',
    'hacl_missing_parameter_values' => 'Der Parameter "$1" hat keine gültigen Werte.',
    'hacl_invalid_predefined_right' => 'Es existiert keine Rechtevorlage mit dem Namen "$1" oder sie enthält keine gültige Rechtedefinition.',
    'hacl_invalid_action'			=> '"$1" ist ein ungültiger Wert für eine Aktion.',
    'hacl_too_many_categories'		=> 'Dieser Artikel gehört zu vielen Kategorien an ($1). Bitte entfernen Sie alle bis auf eine!',
    'hacl_wrong_namespace'			=> 'Artikel mit Rechte- oder Gruppendefinitionen müssen zum Namensraum "Rechte" gehören.',
    'hacl_group_must_have_members'  => 'Eine Gruppe muss mindestens ein Mitglied haben (Gruppe oder Benutzer).',
    'hacl_group_must_have_managers' => 'Eine Gruppe muss mindestens einen Verwalter haben (Gruppe oder Benutzer).',
    'hacl_invalid_parser_function'	=> 'Sie dürfen die Funktion "#$1" in diesem Artikel nicht verwenden.',
    'hacl_right_must_have_rights'   => 'Ein Recht oder eine Sicherheitsbeschreibung müssen Rechte oder Verweise auf Rechte enthalten.',
    'hacl_right_must_have_managers' => 'Ein Recht oder eine Sicherheitsbeschreibung müssen mindestens einen Verwalter haben (Gruppe oder Benutzer).',
    'hacl_whitelist_must_have_pages' => 'Die Positivliste ist leer. Bitte fügen Sie Seiten hinzu.',
    'hacl_add_to_group_cat'			=> 'Der Artikel enthält Funktionen zur Definition von Gruppen. Dies wird nur berücksichtigt wenn Sie "[[Kategorie:Rechte/Gruppe]]" hinzufügen.',
    'hacl_add_to_right_cat'			=> 'Der Artikel enthält Funktionen zur Definition von Rechten oder Sicherheitsbeschreibungen. Dies wird nur berücksichtigt wenn Sie "[[Kategorie:Rechte/Recht]]" oder "[[Kategorie:Rechte/Sicherheitsbeschreibung]]" hinzufügen.',
    'hacl_add_to_whitelist'			=> 'Der Artikel enthält Funktionen zur Definition einer "Positivliste". Diese Funktion darf nur im Artikel "Rechte:Positivliste" benutzt werden.',
    'hacl_pf_rightname_title'		=> "===$1===\n",
    'hacl_pf_rights_title'			=> "===Recht(e): $1===\n",
    'hacl_pf_rights'				=> ":;Recht(e):\n:: $1\n",
    'hacl_pf_right_managers_title'	=> "===Rechteverwalter===\n",
    'hacl_pf_predefined_rights_title' => "===Rechtevorlagen===\n",
    'hacl_pf_whitelist_title' 		=> "===Positivliste===\n",
    'hacl_pf_group_managers_title'	=> "===Gruppenverwalter===\n",
    'hacl_pf_group_members_title'	=> "===Gruppenmitglieder===\n",
    'hacl_assigned_user'			=> 'Zugewiesene Benutzer: ',
    'hacl_assigned_groups'			=> 'Zugewiesene Gruppen:',
    'hacl_dynamic_assignees'		=> 'Dynamisch Zugewiesene',
    'hacl_dyn_assigned_queries'		=> 'Anfragen für dynamisch Zugewiesene:',
    'hacl_dyn_assigned_groups'		=> 'Dynamisch zugewiesene Gruppen:',
    'hacl_dyn_assigned_users'		=> 'Dynamisch zugewiesene Benutzer:',
    'hacl_dynamic_members'			=> 'Dynamische Mitglieder',
    'hacl_dyn_member_queries'		=> 'Anfragen für dynamische Mitglieder:',
    'hacl_dyn_member_groups'		=> 'Dynamische Mitgliedsgruppen:',
    'hacl_dyn_member_users'			=> 'Dynamische Benutzermitglieder:',
	'hacl_save_group_settings'		=> 'Gruppeneinstellungen zwischenspeichern',
	'hacl_save_group_settings_first'=> 'Bitte speichern Sie zuerst die Gruppeneinstellungen!',
	'hacl_user_member'				=> 'Benutzer, die Mitglied dieser Gruppe sind:',
    'hacl_group_member'				=> 'Gruppen, die Mitglied dieser Gruppe sind:',
    'hacl_description'				=> 'Beschreibung:',
    'hacl_error'					=> 'Fehler:',
    'hacl_warning'					=> 'Warnungen:',
    'hacl_consistency_errors'		=> '<h2>Fehler in der Rechtedefinition</h2>',
    'hacl_definitions_will_not_be_saved' => '(Wegen der folgenden Fehler werden die Definitionen dieses Artikel nicht gespeichert und haben keine Auswirkungen.)',
	'hacl_will_not_work_as_expected'=> '(Wegen der folgenden Warnungen wird die Definition nicht wie erwartet angewendet.)',
    'hacl_errors_in_definition'		=> 'Die Definitionen in diesem Artikel sind fehlerhaft. Bitte schauen Sie sich die folgenden Details an!',
    'hacl_anonymous_users'			=> 'anonyme Benutzer',
    'hacl_registered_users'			=> 'registrierte Benutzer',
    'hacl_acl_element_not_in_db'	=> 'Zu diesem Artikel gibt es keinen Eintrag in der Rechtedatenbank. Vermutlich wurde er gelöscht und wiederhergestellt. Bitte speichern Sie ihn und alle Artikel die ihn verwenden neu.',
    'hacl_whitelist_mismatch'		=> 'Die "Positivliste" in diesem Artikel enthält Artikel, die nicht existieren. Bitte entfernen Sie diese und speichern Sie die "Positivliste" erneut.' ,
    'hacl_unprotectable_namespace'  => 'Dieser Namensraum kann nicht geschützt werden. Bitte fragen Sie Ihren Wikiadministrator.',

	/* Messages for semantic protection (properties etc.) */

    'hacl_sp_query_modified'		=> "- Ihre Anfrage wurde modifiziert, das sie geschützte Attribute enthält.\n",
    'hacl_sp_empty_query'			=> '- Ihre Anfrage besteht nur aus geschützten Attributen oder hat Variablen an Attributpositionen und konnte deshalb nicht ausgeführt werden. Systemadministratoren können geschützte Attribute abschalten, indem sie $haclgProtectProperties = false; in LocalSettings.php setzen.'."\n",
    'hacl_sp_results_removed'		=> "- Wegen Zugriffbeschränkungen wurden einige Resultate entfernt.\n",
    'hacl_sp_cant_save_article'		=> "'''Der Artikel enthält die folgenden geschützten Attribute:'''\n$1'''Sie haben nicht die Berechtigung, deren Werte zu setzen. Bitte entfernen Sie die Attribute und speichern Sie erneut.'''",
	'hacl_protected_property_error'	=> "Der Wert eines geschützten Attributs wird nicht angezeigt.",

	/* Messages for Special:ACL */
    'hacl_tab_create_acl' => 'Recht erzeugen',
    'hacl_tab_manage_acls' => 'Rechte verwalten',
    'hacl_tab_manage_user' => 'Gruppen verwalten',
    'hacl_tab_manage_whitelist' => 'Positivliste verwalten',

	/* Messages for 'Create ACL' tab */
    'hacl_create_acl_subtab1' => 'Standardrecht erzeugen',
    'hacl_create_acl_subtab2' => 'Rechtevorlage erzeugen',
    'hacl_create_acl_subtab3' => 'Persönliche Standardrechtevorlage erzeugen',
	/* Messages for sub tab 'Create standard ACL' ("csa") */


	/* Messages for sub tab 'Create ACL template' ("cat") */

	/* Messages for sub tab 'Create ACL default user template ("dut")' */
    'hacl_create_acl_dut_headline' => 'Standardrechtevorlage für Benutzer erzeugen',
    'hacl_create_acl_dut_info' => 'Auf dieser Seite können Sie Ihre persönliche Standardrechtevorlage erzeugen.<br/>Ihre neuen Artikel werden automatisch mit diesen Rechten geschützt.',

    'hacl_create_acl_dut_general' => '1. Allgemein',
    'hacl_create_acl_dut_general_definefor' => 'Definiere für:',
    'hacl_create_acl_dut_general_private_use' => 'Mich',
    'hacl_create_acl_dut_general_all' => 'Alle Benutzer',
    'hacl_create_acl_dut_general_specific' => 'Bestimmer Benutzer und/oder Gruppen',

    'hacl_create_acl_dut_rights' => '2. Rechte',
    'hacl_create_acl_dut_button_create_right' => 'Recht erzeugen',
    'hacl_create_acl_dut_button_add_template' => 'Rechtevorlage hinzufügen',
    'hacl_create_acl_dut_new_right_legend' => 'Recht: Neues Recht $1',
    'hacl_create_acl_dut_new_right_legend_status_saved' => '[gespeichert]',
    'hacl_create_acl_dut_new_right_legend_status_notsaved' => '[nicht gespeichert]',
    'hacl_create_acl_dut_new_right_name' => 'Name:',
    'hacl_create_acl_dut_new_right_defaultname' => 'Neues Recht:',
    'hacl_create_acl_dut_new_right_rights' => 'Rechte:',
    #TODO: can we get this from s.w. else???
    'hacl_create_acl_dut_new_right_fullaccess' => 'Vollzugriff',
    'hacl_create_acl_dut_new_right_read' => 'lesen',
    'hacl_create_acl_dut_new_right_ewf' => 'mit Formular editieren',
    'hacl_create_acl_dut_new_right_edit' => 'editieren',
    'hacl_create_acl_dut_new_right_create' => 'erzeugen',
    'hacl_create_acl_dut_new_right_move' => 'verschieben',
    'hacl_create_acl_dut_new_right_delete' => 'löschen',
    'hacl_create_acl_dut_new_right_annotate' => 'annotieren',
    'hacl_create_acl_dut_new_right_select_user' => 'Benutzer/Gruppen auswählen',
    'hacl_create_acl_dut_new_right_assigned_user' => 'Zugewiesene Benutzer/Gruppen',
    'hacl_create_acl_dut_new_right_column_users' => 'Gruppen und Benutzer',
    'hacl_create_acl_dut_new_right_column_filter' => 'Filter',
    'hacl_create_acl_dut_new_right_column_user' => 'Benutzer',
    'hacl_create_acl_dut_new_right_desc' => 'Beschreibung:',
    'hacl_create_acl_dut_new_right_button_save' => 'Speichern',
    'hacl_create_acl_dut_button_next' => 'Weiter',

    'hacl_create_acl_dut_mod' => '3. Modifizierungsrechte',
    'hacl_create_acl_dut_mod_info' => 'Sie können spezifizieren, wer diese Rechte verändern darf.<br/>Beachten Sie: Standardmäßig hat der Erzeuger der Rechtedefinition alle Rechte (~"Besitzerrecht").',
    'hacl_create_acl_dut_mod_legend' => 'Modifizierungsrechte',

    'hacl_create_acl_dut_save' => '4. Rechtedefinition speichern',
    'hacl_create_acl_dut_save_info' => 'Das System generiert automatisch einen Namen für die Rechte.',
    'hacl_create_acl_dut_save_name' => 'Rechtename:',
    'hacl_create_acl_dut_save' => '',

    'hacl_create_acl_dut_save_button_discard' => 'Rechte verwerfen',
    'hacl_create_acl_dut_save_button_save' => 'Rechte speichern',

	/* Messages for 'Manage ACLs' tab */
	/* Messages for 'Manage User' tab */
	/* Messages for 'Manage Whitelists' tab */
    'hacl_whitelist_headline' => 'Positivliste verwalten',
    'hacl_whitelist_info' => 'Auf dieser Seite können Sie die Positivliste erzeugen und bearbeiten.',
    'hacl_whitelist_filter' => 'Filter',
    'hacl_whitelist_pageset_header' => 'Seite',
    'hacl_whitelist_pagename' => 'Seitenname',
    'hacl_whitelist_addbutton' => 'Seite hinzufügen',
    'hacl_whitelist_pageremoved' => 'Die Seite wurde aus der Positivliste entfernt.',


    'hacl_createRightContent_help' => 'Klicken Sie auf <strong>Recht erstellen</strong> um ein neues Recht zu erstellen. Sie können beliebige viele Rechte erstellen (bspw.: ein Recht welches Vollzugriff für bestimmte Nutzer erlaubt sowie ein Recht welches lediglich Lesezugriff für alle Nutzer realisiert).<br /><br />Wählen Sie <strong>Template hinzufügen</strong> wenn Sie vordefinierte ACL templates in in Ihre Rechtedefinition einbinden möchten.',
    'hacl_haloacl_tab_section_header_title' =>  'Rechte',

    'hacl_createModificationRightContent_help' => 'Wählen Sie die Gruppen/Nutzer, welche diese Access Control List editieren können.',
    'hacl_haloacl_tab_section_header_mod_title' => 'Modifikationsrechte',
    'hacl_haloacl_mod_1' => 'Klappen Sie die Box unten auf, wenn Sie anderen Nutzern oder Gruppen die Modifikation dieser ACL erlauben wollen.',
    'hacl_haloacl_mod_2' => 'Vorsicht. Momentan sind Sie selbst weder direkt, noch über eine Gruppe im Modifikationsrecht eingeschlossen!',
    'hacl_haloacl_mod_3' => 'Bitte wählen Sie mindestens eine Gruppe oder einen User in den Modification Rights aus',

    'hacl_createSaveContent_1' => 'ACL speichern',
    'hacl_createSaveContent_2' => 'ACL Name:',
    'hacl_createSaveContent_3' => 'ACL gespeichert',
    'hacl_createSaveContent_4' => 'Beim speichern des ACL trat ein Fehler auf',

    'hacl_createManageACLContent_2' => 'Hier können Sie vorhandene ACLs editieren und löschen.',
    'hacl_createManageACLContent_1' => 'ACLs verwalten',

    'hacl_createManageUserTemplateContent_1' => 'Eigenes Default User Template verwalten',

    'hacl_createGeneralContent_1' => 'Um eine Access Control List zu erzeugen, führen Sie die folgenden vier Schritte durch. <br />Sie können in jedem Schritt auf das Hilfe-Icon klicken um Hilfe zu erhalten.',
    'hacl_createGeneralContent_2' => 'Allgemein',
    'hacl_createGeneralContent_3' => 'Zu schützen:',
    'hacl_createGeneralContent_4' => 'Seite',
    'hacl_createGeneralContent_5' => 'Attribut',
    'hacl_createGeneralContent_6' => 'Seiten in Namensraum',
    'hacl_createGeneralContent_7' => 'Seiten in Kategorie',
    'hacl_createGeneralContent_8' => 'Name:',
    'hacl_createGeneralContent_9' => 'Festlegen für:',
    'hacl_createGeneralContent_10' => 'Mich',
    'hacl_createGeneralContent_11' => 'Individuelle Nutzer und/oder Nutzergruppen',
    'hacl_createGeneralContent_12' => 'Alle Nutzer',
    'hacl_createGeneralContent_13' => 'Alle registrierten Nutzer',
    'hacl_createGeneralContent_14' => 'Alle anonymen Nutzer',
    'hacl_createGeneralContent_message1' => 'Bitte wählen Sie den Typ des zu schützenden Elements. ',
    'hacl_createGeneralContent_message2' => 'Bitte legen Sie einen Namen fest. ',
    'hacl_createGeneralContent_message3' => 'Bitte definieren Sie, für wen das ACL gilt. ',
    'hacl_createGeneralContent_message4' => 'Daten reichen nicht aus',

    'hacl_createACLContent_1' => '<strong>Zu schützen:</strong><br />Bitte wählen Sie hier, was Sie schützen möchten (eine einzelne Seite/Attribut oder alle Seiten in einem Namensraum/Kategorie).<br /><br /><strong>Name:</strong><br />Geben Sie den Namen des Elementes ein, welches Sie schützen möchten.<br /><br />',
    'hacl_createACLContent_2' => 'Allgemein',

    'hacl_createACLTemplateContent_1' => '<strong>Name:</strong><br />Bitte geben Sie einen Namen f&uuml;r das zu erstellende Template ein.<br /><br />',
    'hacl_createACLTemplateContent_2' => 'Allgemein',

    'hacl_createUserTemplateContent_1' => 'Erstellen Sie hier Ihr standard Nutzer Template. Alle von Ihnen erstellten Seiten werden danach automatisch mit diesem Template geschützt. Sie können jedoch jederzeit den Status einer Seite wieder auf -ungeschützt- oder andere ACL´s setzen.',
    'hacl_createUserTemplateContent_2' => 'Allgemein',

    'hacl_manageUserGroupPanel_1' => 'Name:',

    'hacl_rightsPanel_1' => 'Name:',
    'hacl_rightsPanel_2' => 'Rechte:',
    'hacl_rightsPanel_3' => 'Beschreibung:',
    'hacl_rightsPanel_4' => 'Beschreibung automatisch generieren:',
    'hacl_rightsPanel_5' => 'Ja',
    'hacl_rightsPanel_6' => 'Nein',
    'hacl_rightsPanel_7' => 'automatisch generiert',
    'hacl_rightsPanel_8' => 'Recht löschen',
    'hacl_rightsPanel_9' => 'Recht zurücksetzen',
    'hacl_rightsPanel_10' => 'Recht zwischenspeichern',
    'hacl_rightsPanel_11' => 'Modifikationsrechte',
    'hacl_rightsPanel_12' => 'Modifikationsrechte',
    'hacl_rightsPanel_13' => 'Privates Recht für Nutzer',

    'hacl_rightsPanel_right_fullaccess' => 'Voller Zugriff',
    'hacl_rightsPanel_right_read' => 'Lesen',
    'hacl_rightsPanel_right_edit' => 'Editieren',
    'hacl_rightsPanel_right_editfromform' => 'Edit mit Form',
    'hacl_rightsPanel_right_WYSIWYG' => 'WYSIWYG',
    'hacl_rightsPanel_right_create' => 'Erzeugen',
    'hacl_rightsPanel_right_move' => 'Verschieben',
    'hacl_rightsPanel_right_delete' => 'Löschen',
    'hacl_rightsPanel_right_annotate' => 'Annotieren',

    'hacl_rightsPanel_allUsersRegistered' => 'alle registrierten Nutzer',
    'hacl_rightsPanel_allAnonymousUsers' => 'alle anonymen Nutzer',
    'hacl_rightsPanel_allUsers' => 'alle Nutzer',


    'hacl_rightPanelSelectDeselectTab_1' => 'Gruppen und Nutzer',
    'hacl_rightPanelSelectDeselectTab_2' => 'Gruppenfilter:',
    'hacl_rightPanelSelectDeselectTab_3' => 'Nutzer',
    'hacl_rightPanelSelectDeselectTab_4' => 'Nutzer',
    'hacl_rightPanelSelectDeselectTab_5' => 'Filter:',

    'hacl_rightList_All' => 'Alle',
    'hacl_rightList_StandardACLs' => 'Standard ACLs',
    'hacl_rightList_Page' => 'Seite',
    'hacl_rightList_Category' => 'Kategorie',
    'hacl_rightList_Property' => 'Eigenschaft',
    'hacl_rightList_Namespace' => 'Namensraum',
    'hacl_rightList_ACLtemplates' => 'ACL Templates',
    'hacl_rightList_Defaultusertemplates' => 'Standard Nutzer Templates',

    'hacl_rightList_1' => 'Vorhandene ACLs',

    'hacl_SDRightsPanelContainer_1' => 'Editieren:',
    'hacl_SDRightsPanelContainer_2' => 'Recht löschen',
    'hacl_SDRightsPanelContainer_3' => 'Änderungen verwerfen',
    'hacl_SDRightsPanelContainer_4' => 'Recht speichern',

    'hacl_RightsContainer_1' => 'Auswählen...',
    'hacl_RightsContainer_2' => 'Markierte Templates auswählen',

    'hacl_saveTempGroup_1' => 'Gruppen gespeichert',

    'hacl_deleteSecurityDescriptor_1' => 'Das Recht wurde erfolgreicht gelöscht.',

    'hacl_manageUser_1' => 'ACL Gruppen und Nutzer verwalten',
    'hacl_manageUser_2' => 'Hier können ACL Gruppen angelegt, editiert und gelöscht werden.',
    'hacl_manageUser_3' => 'Neue Gruppe hinzufügen',
    'hacl_manageUser_4' => 'Untergruppe hinzufügen',
    'hacl_manageUser_5' => 'Untergruppe auf gleicher Ebene hinzufügen',
    'hacl_manageUser_6' => 'Vorhandene Gruppen',
    'hacl_manageUser_7' => 'Ausgewählte Elemente löschen',
    'hacl_manageUser_8' => 'ACL Gruppen Explorer',
    'hacl_manageUser_9' => 'Editieren',
    'hacl_manageUser_10' => 'Gruppe speichern',

    'hacl_whitelist_1' => 'Positivliste verwalten',
    'hacl_whitelist_2' => 'Hier können Artikel zur Positivliste hinzugefügt und gelöscht werden.',
    'hacl_whitelist_3' => 'Positivliste',
    'hacl_whitelist_4' => 'Artikel zur Positivliste hinzufügen:',

    'hacl_deleteGroup_1' => 'Die markierten Einträge wurden erfolgreich gelöscht',

    'hacl_quickACL_1' => 'ACL Favoriten verwalten',
    'hacl_quickACL_2' => 'Hier können ACLs zu Ihren Favoriten hinzugefügt oder von diesen entfernt werden.',
    'hacl_quickACL_3' => 'ACL Favoriten',
    'hacl_quickACL_4' => 'Favoriten wurden gespeichert.',

    'hacl_general_nextStep' => 'Nächster Schritt',
    'hacl_nothing_deleted' => 'Es wurden keine Elemente gelöscht',
    'hacl_quickacl_limit' => 'Es sind maximal 15 Templates in der Schnellauswahl erlaubt.',
    'hacl_nodefusertpl' => "Es existiert kein Standard Nutzer Template",
    'hacl_nodefusertpl_link' => "Es existiert kein Standard Nutzer Template",
    'hacl_showing_text' => "Zeige",
    'hacl_showing_elements_text' => "Element(e)",
    'hacl_selected' => "Ausgew&auml;hlt",

    'hacl_discard_changes' => "&Auml;nderungen verwerfen",
    'hacl_save_acl' => "ACL speichern",
    'hacl_create_right' => "Recht erstellen",
    'hacl_add_template' => "Template hinzuf&uuml;gen",
    'hacl_groupsettings' => "Gruppeneinstellungen",
	'hacl_popup_invalid_no_group_members' => "Bitte wählen Sie mindestens einen Benutzer oder eine Gruppe aus!",

    'hacl_saved' => "Gespeichert",
    'hacl_notsaved' => "Nicht gespeichert",
    'hacl_default' => "Standard",

    'hacl_tooltip_enternameforexisting' => "Bitte den Name eines existierenden Elements angeben",
    'hacl_tooltip_eneternamefortemplate' => "Bitte einen Name f&uuml;r das Template angeben",

    'hacl_tooltip_clickto_delete_right'=> "Hier klicken um das Recht zu l&ouml;schen",
    'hacl_tooltip_clickto_reset_right'=> "Hier klicken um das Recht zu zur&uuml;ckzusetzen",
    'hacl_tooltip_clickto_save_right'=> "Hier klicken um das Recht zu speichern",
    'hacl_tooltip_clickto_save_modright'=> "Hier klicken um das Modifikationsrecht zu speichern",

	'hacl_root_group' => "Gruppen",

    'hacl_delete_selected' => "Ausgew&auml;hlte l&ouml;schen",
    'hacl_select' => "<span style='margin-right:0px'>Ausw.</span>",

    'hacl_deletetplfromacl' => "Template von ACL entfernen",
    'hacl_addtpltoacl' => "Template zu ACL hinzuf&uuml;gen",

    'hacl_tpl_already_exists' => "Ein Template mit diesem Name existiert bereits",
    'hacl_setexisting_name' => "Bitte einen Namen eines existierenden Elements eingeben",
    'hacl_already_protected' => "Das Element ist bereits gesch&uuml;tzt. Zum &Auml;ndern bitte in die Rechteverwaltung wechseln. ",
    'hacl_already_protected_by_ns_or_cat' => "Das Element ist bereits durch eine Kategorie oder einen Namensraum geschützt. Sie sind nicht berechtigt, neue Rechte hinzuzufügen.",
    'hacl_showacls' => "Zeige ACLs",
    'hacl_groupdescription'=> 'Gruppenbeschreibung',
    'hacl_advancedToolbarTooltip' => 'Hier klicken um erweiterte Rechtedefinitionen in einem neuen Tab zu öffnen',
    'hacl_reset_groupsettings' => 'Gruppeneinstellungen zur&uuml;cksetzen',
    'hacl_createSavehelpopup1' =>'Der ACL-Name wurde automatisch generiert. <br /> Klicken Sie auf Save ACL klicken, um diese Access Control List zu speichern.',
    'hacl_help_popup' => 'Hilfe',
    'hacl_jumptoarticle' => "Zum Artikel springen.",
    'hacl_no_groups_or_users' => "<h4>&nbsp;&nbsp;Keine Gruppe oder Benutzer gewählt..</h4><h4>&nbsp;&nbsp;Bitte wählen Sie eine Gruppe oder einen Benutzer.</h4>",
    'hacl_protected_label' => 'geschützt',
    'hacl_unprotected_label' => 'ungeschützt',
    'hacl_delete_link_header' => '<span style="margin-right:-12px">Löschen</span>',

//--- Messages for Manage Groups ---
	'hacl_group_exists'  => "Die Gruppe $1 existiert bereits. (Ihr Typ ist $2.)\n Sie können nicht zwei Gruppen mit dem selben Namen erzeugen.",
	'hacl_group_no_name' => "Sie haben keinen Gruppennamen eingegeben. Dieser Name ist zwingend erforderlich.",

	//--- Messages for global permissions ---
	'hacl_gp_ge_group'		=> "Gruppe",
	'hacl_gp_ge_info'		=> "Info",
	'hacl_gp_ge_permission'	=> "Erlaubnis",
	'hacl_gp_group_filter'	=> "Filter:",
	'hacl_gp_intro'			=> "In diesem Tab können Sie globale Berechtigungen für HaloACL-Gruppen festlegen.<br />".
							   "Diese Berechtigungen beziehen sich auf Funktionen des gesamten Systems und nicht nur auf bestimmte Inhalte.",
	'hacl_gp_lgr_intro'		=> "Hier finden Sie eine Liste alle Gruppen mit ihren Berechtigungen: ",
	'hacl_gp_listgrouprights'
							=> "Liste Gruppenberechtigungen",
	'hacl_gp_permission'	=> "Berechtigung:",
	'hacl_gp_set_permission'=> "Setze Berechtigung ",
	'hacl_gp_select_permission' => "Bitte wählen Sie die Berechtigung aus, die sie Gruppen zuweisen möchten.",
	'hacl_gp_default'			=> "Die Standardeinstellung für alle Benutzer ist: ",
	'hacl_gp_permit'			=> "erlaubt",
	'hacl_gp_deny'				=> "verweigert",
	'hacl_gp_comprises_features'=> "Diese Berechtigung setzt sich aus folgenden Systemfunktionen zusammen:",
	'hacl_gp_discard'			=> "Änderungen verwerfen",
	'hacl_gp_save'				=> "Globale Berechtigungen speichern",
	'hacl_gp_hint'				=> "Hinweis:",
	'hacl_gp_check_default'		=> "Standardeinstellung verwenden",
	'hacl_gp_check_permit'		=> "Berechtigung geben",
	'hacl_gp_check_deny'		=> "Berechtigung verweigern",
	'hacl_gp_all_users'			=> "Alle Benutzer",
	'hacl_gp_registered_users'	=> "Registrierte Benutzer",
	'hacl_gp_permissions_saved' => "Die Berechtigungen wurden erfolgreich gespeichert.",
	'hacl_gp_has_permissions'	=> "Die Gruppe hat die folgende(n) Berechtigung(en):",
	'hacl_gp_no_features_for_user'
								=> "Es tut uns leid.<br />Sie müssen ein Administrator oder Bürokrat sein um globale Berechtigungen ändern zu können.",
	'hacl_gp_no_features_defined'
								=> "Es sind keine Funktionen für globale Berechtigungen definiert. <br />".
								   "Bitte editieren Sie <tt>/extensions/HaloACL/includes/HACL_Initialize.php</tt>: <br />".
								   "Setzen Sie <br /> <tt>\$haclgUseFeaturesForGroupPermissions = true;</tt> <br />".
								   "und definierten Sie die Funktionen in <tt>\$haclgFeature</tt>."
								
);
