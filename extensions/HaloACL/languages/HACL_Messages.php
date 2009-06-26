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
	'hacl_unknown_user'				=> 'The user "$1" is unknown.',
	'hacl_unknown_group'			=> 'The group "$1" is unknown.',
	'hacl_missing_parameter'		=> 'The parameter "$1" is missing.',
	'hacl_missing_parameter_values' => 'There are no valid values for parameter "$1".',
	'hacl_invalid_predefined_right' => 'A rights template with the name "$1" does not exist or it contains no valid rights definition.',
	'hacl_invalid_action'			=> '"$1" is an invalid value for an action.',
	'hacl_too_many_categories'		=> 'This article belongs to too many categories ($1). Please remove all but one!',
	'hacl_wrong_namespace'			=> 'Articles with rights or group definitions must belong to the namespace "ACL".',
	'hacl_group_must_have_members'  => 'A group must have at least one member (group or user).',
	'hacl_group_must_have_managers' => 'A group must have at least one manager (group or user).',
	'hacl_invalid_parser_function'	=> 'You must not use the function "#$1" in this article.',
	'hacl_right_must_have_rights'   => 'A right or security descriptor must contain rights or reference other rights.',
	'hacl_right_must_have_managers' => 'A right or security descriptor must have at least one manager (group or user).',
	'hacl_whitelist_must_have_pages' => 'The whitelist is empty. Please add pages .',
	'hacl_add_to_group_cat'			=> 'The article contains functions for the definition of a group. This has no effect unless you add the category "[[Category:ACL/Group]]".',
	'hacl_add_to_right_cat'			=> 'The article contains functions for the definition of a security descriptor or a right template. This has no effect unless you add the category "[[Category:ACL/ACL]]" or "[[Category:ACL/Right]]".',
	'hacl_add_to_whitelist'			=> 'The article contains functions for the definition of a whitelist. This function can only be used in the article "ACL:Whitelist".',
	'hacl_pf_rights_title'			=> "===Right(s): $1===\n",
	'hacl_pf_right_managers_title'	=> "===Right managers===\n",
	'hacl_pf_predefined_rights_title' => "===Right templates===\n",
	'hacl_pf_whitelist_title' 		=> "===Whitelist===\n",
	'hacl_pf_group_managers_title'	=> "===Group managers===\n",
	'hacl_pf_group_members_title'	=> "===Group members===\n",
	'hacl_assigned_user'			=> 'Assigned users: ',
	'hacl_assigned_groups'			=> 'Assigned groups:',
	'hacl_user_member'				=> 'Users who are member of this group:',
	'hacl_group_member'				=> 'Groups who are member of this group:',
	'hacl_description'				=> 'Description:',
	'hacl_error'					=> 'Errors:',
	'hacl_consistency_errors'		=> '<h2>Errors in ACL definition</h2>',
	'hacl_definitions_will_not_be_saved' => '(Due to the following errors, the definitions in this article are not saved and have no effect.)',
	'hacl_errors_in_definition'		=> 'The definitions in this article are erroneous. Please see the details below!',
	'hacl_anonymous_users'			=> 'anonymous users',
	'hacl_registered_users'			=> 'registered users',
	'hacl_acl_element_not_in_db'	=> 'There is no entry in the ACL database for this article. Presumably it was deleted and restored. Please store it and all articles which use it again.',
	'hacl_whitelist_mismatch'		=> 'The whitelist in this article contains articles that do not exist. Please remove them and save the whitelist again.',

	/* Messages for semantic protection (properties etc.) */

	'hacl_sp_query_modified'		=> "- The query was modified because it contains protected properties.\n",
	'hacl_sp_empty_query'			=> "- Your query consists only of protected properties. It was not executed.\n",
	'hacl_sp_results_removed'		=> "- Because of access restrictions some results were removed.\n",

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
	'hacl_unknown_user'				=> 'Der Benutzer "$1" ist unbekannt.',
	'hacl_unknown_group'			=> 'Die Gruppe "$1" ist unbekannt.',
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
	'hacl_whitelist_must_have_pages' => 'Die Weiße Liste ist leer. Bitte fügen Sie Seiten hinzu.',
	'hacl_add_to_group_cat'			=> 'Der Artikel enthält Funktionen zur Definition von Gruppen. Dies wird nur berücksichtigt wenn Sie "[[Kategorie:Rechte/Gruppe]]" hinzufügen.',
	'hacl_add_to_right_cat'			=> 'Der Artikel enthält Funktionen zur Definition von Rechten oder Sicherheitsbeschreibungen. Dies wird nur berücksichtigt wenn Sie "[[Kategorie:Rechte/Recht]]" oder "[[Kategorie:Rechte/Sicherheitsbeschreibung]]" hinzufügen.',
	'hacl_add_to_whitelist'			=> 'Der Artikel enthält Funktionen zur Definition einer "Weißen Liste". Diese Funktion darf nur im Artikel "Rechte:Weiße Liste" benutzt werden.',
	'hacl_pf_rights_title'			=> "===Recht(e): $1===\n",
	'hacl_pf_right_managers_title'	=> "===Rechteverwalter===\n",
	'hacl_pf_predefined_rights_title' => "===Rechtevorlagen===\n",
	'hacl_pf_whitelist_title' 		=> "===Weiße Liste===\n",
	'hacl_pf_group_managers_title'	=> "===Gruppenverwalter===\n",
	'hacl_pf_group_members_title'	=> "===Gruppenmitglieder===\n",
	'hacl_assigned_user'			=> 'Zugewiesene Benutzer: ',
	'hacl_assigned_groups'			=> 'Zugewiesene Gruppen:',
	'hacl_user_member'				=> 'Benutzer, die Mitglied dieser Gruppe sind:',
	'hacl_group_member'				=> 'Gruppen, die Mitglied dieser Gruppe sind:',
	'hacl_description'				=> 'Beschreibung:',
	'hacl_error'					=> 'Fehler:',
	'hacl_consistency_errors'		=> '<h2>Fehler in der Rechtedefinition</h2>',
	'hacl_definitions_will_not_be_saved' => '(Wegen der folgenden Fehler werden die Definitionen dieses Artikel nicht gespeichert und haben keine Auswirkungen.)',
	'hacl_errors_in_definition'		=> 'Die Definitionen in diesem Artikel sind fehlerhaft. Bitte schauen Sie sich die folgenden Details an!',
	'hacl_anonymous_users'			=> 'anonyme Benutzer',
	'hacl_registered_users'			=> 'registrierte Benutzer',
	'hacl_acl_element_not_in_db'	=> 'Zu diesem Artikel gibt es keinen Eintrag in der Rechtedatenbank. Vermutlich wurde er gelöscht und wiederhergestellt. Bitte speichern Sie ihn und alle Artikel die ihn verwenden neu.',
	'hacl_whitelist_mismatch'		=> 'Die "Weiße Liste" in diesem Artikel enthält Artikel, die nicht existieren. Bitte entfernen Sie diese und speichern Sie die "Weiße Liste" erneut.' ,

	/* Messages for semantic protection (properties etc.) */

	'hacl_sp_query_modified'		=> "- Ihre Anfrage wurde modifiziert, das sie geschützte Attribute enthält.\n",
	'hacl_sp_empty_query'			=> "- Ihre Anfrage besteht nur aus geschützten Attributen und konnte deshalb nicht ausgeführt werden.of protected properties.\n",
	'hacl_sp_results_removed'		=> "- Wegen Zugriffbeschränkungen wurden einige Resultate entfernt.\n",

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

