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

require_once('DF_Language.php');
/**
 * Language abstraction.
 *
 * @author: Kai K�hn
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
	
	'df_mwextension' => 'Mwextension',
	'df_minversion' => 'Minversion',
	'df_maxversion' => 'Maxversion',
	'df_isoptional' => 'Isoptional',
	
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
	
	'block_nsmappings_page_title' => 'Error on saving namespace mappings',
	'block_nsmappings_error' => 'There are double prefixes or URIs. Check the list. No prefix or URI may be used twice.',
	
	// webadmin
	'df_webadmin' => 'Wiki Administration Tool',
	'df_username' => 'Username',
	'df_password' => 'Password',
	'df_login' => 'Login',
	'df_linktowiki' => 'Go to wiki',
	'df_logout' => 'Logout',
	'df_webadmin_login_failed' => 'Login failed',
	'df_webadmin_findall' => 'Find all',
	'df_webadmin_installall' => 'Install selected',
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
	'df_webadmin_addrepository' => 'Register a new repository',
	'df_webadmin_removerepository' => 'Unregister selected repository',
	
	'df_webadmin_statustab' => 'Status',
	'df_webadmin_searchtab' => 'Find extensions',
	'df_webadmin_maintenacetab' => 'System Restore',
	'df_webadmin_uploadtab' => 'Upload local bundles/ontologies',
	'df_webadmin_settingstab' => 'Registered repositories',
	'df_webadmin_localsettingstab' => 'LocalSettings',
	'df_webadmin_serverstab' => 'Servers',
	'df_webadmin_logtab' => 'Logs',
	'df_webadmin_profilertab' => 'Profiler',
	'df_webadmin_contentbundletab' => 'Content bundles',
	
	'df_webadmin_restorepoint' => 'Restore point',
	'df_webadmin_creationdate' => 'Creation date',
	'df_webadmin_version' => 'Version',
	'df_webadmin_upload' => 'Upload',
	'df_webadmin_restore' => 'Restore',
	'df_webadmin_removerestore' => 'Remove',
	'df_webadmin_file' => 'File',
	'df_webadmin_nothingfound' => 'No matching bundles for <b>"$1"</b> found!',
	'df_webadmin_searchinfoifnothingfound' => 'To browse the Ontoprise repositories click here: ',
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
	'df_webadmin_logtab_description' => 'This page lists the log files from previous operations.',
	'df_webadmin_profilertab_description' => 'This page allows to use the internal MW profiler. This tool is intended for finding performance bottlenecks in the MW software and its extensions.',
	'df_webadmin_contentbundletab_description' => 'Here you can export and download bundles as zip files. To see a list of bundles you need SMWHalo installed.',
	'df_webadmin_contentbundle_nosmwhalo' => 'SMWHalo not available, ie. querying for content bundles not possible. Please enter the bundle name manually.',
	'df_webadmin_querying_contentbundle_failed' => 'Querying content bundles failed.',
	'df_webadmin_export_bundle' => 'Export bundle',
	
		
	'df_webadmin_configureservers' => 'Here you can start/stop the servers of your wiki installation. Please wait a few seconds until the state is refreshed.',
	'df_webadmin_process_runs' => 'running',
	'df_webadmin_process_doesnot_run' => 'not running',
	'df_webadmin_process_unknown' => 'refreshing...',
	'df_webadmin_server_execute' => 'Execute',
	'df_webadmin_server_start' => 'start',
	'df_webadmin_server_end' => 'stop',
	'df_webadmin_refresh' => 'Refresh',
	'df_webadmin_download_profilinglog' => 'download profiling log',
	'df_webadmin_filter' => 'Filter',
	'df_webadmin_finalize_message' => 'There are non-initialized extensions. Click here to start finalization.',
	'df_webadmin_finalize' => 'finalize',
	'df_webadmin_clearlog' => 'Clear log',
	'df_webadmin_loglink' => 'Link',
	'df_webadmin_logdate' => 'Date of operation',
	'df_loglink_hint' => 'Opens a new tab',
	'df_webadmin_contentbundle_file' => 'Bundle',
	'df_webadmin_contentbundle_creationdate' => 'Creation date',
	'df_webadmin_contentbundle_nobundles' => 'No bundles exported.',
	
	'df_webadmin_profilertab_requests' => 'Profiled HTTP requests',
			
	'df_webadmin_upload_message' => 'Here you can upload bundles ($1) or ontology files ($2)',
	
	'df_webadmin_newreleaseavailable' => 'New release available! Check the $1',
    'df_webadmin_repository_link' => 'repository list',
	
	'df_webadmin_watsettingstab' => 'Settings',
	'df_webadmin_watsettingstab_description' => 'Here you can configure general settings for the WAT administration tool. The settings are kept persistent. Hover over the settings to get an additional explanation.',
	
	'df_webadmin_watsettings_bundleimport' => 'Bundle import',
	'df_webadmin_watsettings_installation' => 'Installation',
	'df_webadmin_watsettings_restore_points' => 'Restore points',
	'df_webadmin_watsettings_ontologyimport' => 'Ontology import',
	'df_watsettings_overwrite_always' => 'Always overwrite user-modified pages',
	'df_watsettings_overwrite_always_help' => 'This settings forces WAT to *always* overwrite a wiki page with the version contained in a bundle that is installed or updated. Otherwise pages modified by the user are skipped.',
	'df_watsettings_merge_with_other_bundle' => 'Always merge pages of other bundles',
	'df_watsettings_merge_with_other_bundle_help' => 'Merge an already existing ontology page with the same page of another ontology. Otherwise the page is skipped.',
	'df_watsettings_apply_patches' => 'Apply (partially) failed patches',
	'df_watsettings_apply_patches_help' => 'Also apply patches if they could not be applied completely. It is rarely the case that a patch which was not properly applied affects the whole system. In most cases only a particular feature is broken.',
	'df_watsettings_install_optionals' => 'Install optional extensions as well',
	'df_watsettings_install_optionals_help' => 'Some extensions have optional "dependencies". They are rather installation suggestions than dependencies. They are *NOT* needed for proper functionality.',
	'df_watsettings_deinstall_dependant' => 'De-install automatically (super-)extensions which are dependant to the one which is de-installed.',
	'df_watsettings_deinstall_dependant_help' => 'Be careful: This settings removes everything which depends on the extension to be removed. For example: You have SMW, SMWHalo and SF installed. If you de-install SMW, SMWHalo and SF would be also removed. Otherwise there would be an error.',
	'df_watsettings_create_restorepoints' => 'Always create restore point on install/de-install and update operations',
	'df_watsettings_create_restorepoints_help' => 'Create restore points on any operation that is going to change the wiki. Will increase the required installation time.',
	'df_watsettings_hidden_annotations' => 'Create hidden annotations on ontology import',
	'df_watsettings_hidden_annotations_help' => 'Semantic data of imported ontologies can be displayed on the rendered page or not.',
	'df_watsettings_use_namespaces' => 'Use namespace prefixes for imported ontologies',
	'df_watsettings_use_namespaces_help' => 'Every page imported from an ontology will get a namespace prefix separated with a slash, e.g. MDM/Category:Person. This is not changeable at the moment.',
		
	
     /*Message for ImportOntologyBot*/
    'smw_gard_import_docu' => 'Imports an ontology file.',
    'smw_gard_import_choosefile' => 'The following $1 files are available.',
    'smw_gard_import_addfiles' => 'Add $2 files by using $1.',
    'smw_gard_import_nofiles' => 'No files of type $1 are available',
    'smw_df_missing' => 'To use the import bot you have to install the Wiki admin tool',
    'smw_importontologybot' => 'Import an ontology'
    
    
	);

}
