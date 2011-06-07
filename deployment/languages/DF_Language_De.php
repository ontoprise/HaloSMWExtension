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
 * @author: Kai Kühn / ontoprise / 2009
 *
 */
class DF_Language_De extends DF_Language {
	protected $language_constants = array(
	'df_ontologyversion' => 'Ontologieversion',
	'df_partofbundle' => 'Teil des Pakets',
	'df_contenthash' => 'Inhaltshash',
	'df_dependencies'=> 'Abhängigkeit',
    'df_instdir' => 'Installationsverzeichnis',
    'df_ontologyvendor' => 'Anbieter',
    'df_description' => 'Beschreibung',
	'df_contentbundle' => 'Content bundle',
    'df_ontologyuri' => 'Ontologie URI',
	
	'checkinstallation' => 'Prüfe Installation',
	'category' => 'Kategorie',
	'is_inverse_of' => 'Ist invers zu',
    'has_domain_and_range' => 'Hat Domain und Range',
	'imported_from'=>'Importiert aus',
	
	// user
    'checkinstallation' => 'Prüfe Installation',
    'df_checkforupdates' => 'Prüfe auf Updates',
	'df_updatesavailable' => 'Updates verfügbar!',
    'df_updateforextensions' => 'Es gibt Updates für folgende Extensions:',
    'df_noupdatesfound' => 'Keine Updates gefunden!',
    'df_installationpath_heading' => "Installationsverzeichnis des Deployment-Frameworks",
       
    'df_warn' => 'WARNUNG',
    'df_error' => 'FEHLER',
    'df_fatal' => 'FATALER FEHLER',
    'df_failed' => 'FEHLGESCHLAGEN',
    'df_ok' => 'OK',
	
	// webadmin
	'df_webadmin_updatesavailable' => 'Updates verfügbar! Machen Sie ein ',
	'df_webadmin_extension' => 'Extension',
    'df_webadmin_description' => 'Beschreibung',
    'df_webadmin_action' => 'Aktion',
	'df_webadmin_install' => 'Installieren',
    'df_webadmin_deinstall' => 'De-Installieren',
	'df_webadmin_update' => 'Aktualisieren',
    'df_webadmin_remove' => 'Datei entfernen',
	
	'df_webadmin_statustab' => 'Status',
    'df_webadmin_searchtab' => 'Suche',
    'df_webadmin_bundledetailtab' => 'Bundle-Details',
    'df_webadmin_maintenacetab' => 'Wartung',
	'df_webadmin_uploadtab' => 'Hochladen',
	'df_webadmin_settingstab' => 'Einstellungen',
	
	'df_webadmin_restorepoint' => 'Rücksetzpunkt',
	'df_webadmin_creationdate' => 'Datum der Erzeugung',
	'df_webadmin_version' => 'Version',
	'df_webadmin_upload' => 'Hochladen'
    
	);
}
