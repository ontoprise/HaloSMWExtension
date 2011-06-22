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
    'df_partofbundle' => 'Part of bundle',
    'df_contenthash' => 'Content hash',
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
	'df_installationpath_heading' => "Installation path of deployment framework",
	
	'df_warn' => 'WARN',
	'df_error' => 'ERROR',
	'df_fatal' => 'FATAL',
	'df_failed' => 'FAILED',
	'df_ok' => 'OK',
	
	// webadmin
	'df_webadmin' => 'Deployment Framework WebAdmin',
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
	'df_webadmin_remove' => 'Remove file',
	
	'df_webadmin_statustab' => 'Status',
	'df_webadmin_searchtab' => 'Find extensions',
	'df_webadmin_maintenacetab' => 'System Restore',
	'df_webadmin_uploadtab' => 'Upload local bundles/ontologies',
	'df_webadmin_settingstab' => 'Repositories',
	'df_webadmin_localsettingstab' => 'LocalSettings',
	
	'df_webadmin_restorepoint' => 'Restore point',
	'df_webadmin_creationdate' => 'Creation date',
	'df_webadmin_version' => 'Version',
	'df_webadmin_upload' => 'Upload',
	'df_webadmin_file' => 'File',
	'df_webadmin_nothingfound' => 'No matching bundles for <b>"{{search-value}}"</b> found!',
	'df_webadmin_searchinfoifnothingfound' => 'To browse the Ontoprise repository click here: ',
	'df_webadmin_norestorepoints' => 'No restore points found.',
	'df_webadmin_nouploadedfiles' => 'No uploaded files found.',
	
	'df_restore_warning' => 'Restoring will roll back both the wiki setup and its entire contents to a previous point. Do you wish to continue?',
	'df_uninstall_warning' => 'The following extensions will be uninstalled. Are you sure?',
	'df_globalupdate_warning' => 'Perform global update?',
	'df_inspectextension_heading' => 'Inspect extension',
	'df_select_extension' => 'Select extension',
	'df_webadmin_localsettings_description' => "Edit an extensions's section in LocalSettings.php. Don't forget to save your changes."
	
	);

}
