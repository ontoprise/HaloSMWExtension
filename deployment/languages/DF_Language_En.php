<?php
/*  Copyright 2009, ontoprise GmbH
 *
 *   The deployment tool is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The deployment tool is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
require_once('DF_Language.php');
/**
 * Language abstraction.
 *
 * @author: Kai K�hn / ontoprise / 2009
 *
 */
class DF_Language_En extends DF_Language {
	protected $language_constants = array(

	// content
    'df_ontologyversion' => 'Ontology version',
	'df_patchlevel' => 'Patchlevel',
    'df_partofbundle' => 'Part of bundle',
   
	'df_dependencies'=> 'Dependency',
	'df_instdir' => 'Installation dir',
	
	'df_rationale' => 'Rationale',
	'df_maintainer' => 'Maintainer',
    'df_vendor' => 'Vendor',
    'df_helpurl' => 'Help Url',
    'df_license' => 'License',
	'df_contentbundle' => 'Content bundle',
	'df_ontologyuri' => 'Ontology URI',
	'df_usesprefix' => 'Bundle uses namespace prefix',
	'df_mwextension' => 'Mwextension',
	'df_minversion' => 'Minversion',
	'df_maxversion' => 'Maxversion',
	
	'category' => 'Category',
	'is_inverse_of' => 'Is inverse of',
	'has_domain_and_range' => 'Has domain and range',
	'imported_from'=>'Imported from',
	
	'df_namespace_mappings_page' => 'NamespaceMappings',

	// user
	'checkinstallation' => 'Check Installation',
	'df_checkforupdates' => 'Check for updates',
	'df_updatesavailable' => 'Updates available!',
	'df_updateforextensions' => 'There are updates for the following extensions:',
	'df_noupdatesfound' => 'No updates found!',
	'df_installationpath_heading' => "Installation path of Wiki Administration Tool",
	
	'df_warn' => 'WARN',
	'df_error' => 'ERROR',
	'df_fatal' => 'FATAL',
	'df_failed' => 'FAILED',
	'df_ok' => 'OK',
	
	// webadmin
	'df_webadmin' => 'Wiki Administration Tool',
	'df_username' => 'Username',
	'df_password' => 'Password',
	'df_login' => 'Login',
	'df_linktowiki' => 'Go to wiki',
	'df_logout' => 'Logout',
	'df_webadmin_login_failed' => 'Login failed',
	'df_webadmin_findall' => 'Find all',
	'df_webadmin_about' => 'About',
	'df_webadmin_status_text' => 'This table shows all extensions and content bundles that are currently installed in this wiki.',
	'df_webadmin_updatesavailable' => 'Updates available! Do a ',
	'df_webadmin_globalupdate' => 'Global update',
	'df_webadmin_extension' => 'Extension',
	'df_webadmin_description' => 'Description',
	'df_webadmin_action' => 'Action',
	'df_webadmin_install' => 'Install',
	'df_webadmin_deinstall' => 'Uninstall',
	'df_webadmin_update' => 'Update',
	'df_webadmin_checkdependency' => 'Check dependencies',
	'df_webadmin_remove' => 'Remove file',
	'df_webadmin_addrepository' => 'Add repository',
	'df_webadmin_removerepository' => 'Remove repository',
	
	'df_webadmin_statustab' => 'Status',
	'df_webadmin_searchtab' => 'Find extensions',
	'df_webadmin_maintenacetab' => 'System Restore',
	'df_webadmin_uploadtab' => 'Upload local bundles/ontologies',
	'df_webadmin_settingstab' => 'Repositories',
	'df_webadmin_localsettingstab' => 'LocalSettings',
	'df_webadmin_serverstab' => 'Servers',
	
	'df_webadmin_restorepoint' => 'Restore point',
	'df_webadmin_creationdate' => 'Creation date',
	'df_webadmin_version' => 'Version',
	'df_webadmin_upload' => 'Upload',
	'df_webadmin_restore' => 'Restore',
	'df_webadmin_removerestore' => 'Remove',
	'df_webadmin_file' => 'File',
	'df_webadmin_nothingfound' => 'No matching bundles for <b>"$1"</b> found!',
	'df_webadmin_searchinfoifnothingfound' => 'To browse the Ontoprise repository click here: ',
	'df_webadmin_norestorepoints' => 'No restore points found.',
	'df_webadmin_nouploadedfiles' => 'No uploaded files found.',
	
	'df_webadmin_maintenancetext' => 'The system restore tab allows creating and restoring restore points. Restore points are snapshots of a wiki installation (including database content).',
	'df_webadmin_settingstext' =>  'The repositories tab allows adding or removing repositories. A repository is denoted by its URL.',
	'df_restore_warning' => 'Restoring will REPLACE both the wiki setup and its ENTIRE contents by a previous version. YOU WILL LOOSE YOUR CURRENT WIKI CONTENT!. Do you wish to continue?',
	'df_remove_restore_warning' => 'Restore point will be REMOVED!',
	'df_uninstall_warning' => 'The following extensions will be uninstalled. Are you sure?',
	'df_globalupdate_warning' => 'Perform global update?',
	'df_inspectextension_heading' => 'Inspect extension',
	'df_select_extension' => 'Select extension',
	'df_webadmin_localsettings_description' => "Edit an extensions's section in LocalSettings.php. Don't forget to save your changes. If you edit LocalSettings.php manually, make sure that you <b>do not remove</b> the extension-tags in LocalSettings.php. Otherwise this view gets messed up.",
	
	'df_webadmin_configureservers' => 'Here you can start/stop the servers of your wiki installation',
	'df_webadmin_process_runs' => 'running',
	'df_webadmin_process_doesnot_run' => 'not running',
	'df_webadmin_server_execute' => 'Execute',
	'df_webadmin_server_start' => 'start',
	'df_webadmin_server_end' => 'stop',
	'df_webadmin_refresh' => 'refresh',
	
	'df_webadmin_upload_message' => 'Here you can upload bundles ($1) or ontology files ($2)'
	);

}
