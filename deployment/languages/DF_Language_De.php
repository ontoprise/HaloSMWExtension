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
 * @author: Kai Kühn
 *
 */
class DF_Language_De extends DF_Language {
	protected $language_constants = array(
	'df_ontologyversion' => 'Ontologieversion',
	'df_patchlevel' => 'Patchlevel',
	'df_partofbundle' => 'Teil des Pakets',
	
	'df_dependencies'=> 'Abhängigkeit',
    'df_instdir' => 'Installationsverzeichnis',

    'df_rationale' => 'Beschreibung',
	'df_maintainer' => 'Entwickler',
    'df_vendor' => 'Anbieter',
    'df_helpurl' => 'Hilfs-URL',
    'df_license' => 'Lizenz',
	'df_contentbundle' => 'Content bundle',
    'df_ontologyuri' => 'Ontologie URI',
	
	'df_mwextension' => 'Mwextension',
	'df_minversion' => 'Minversion',
    'df_maxversion' => 'Maxversion',
    'df_isoptional' => 'Istoptional',
	
	'category' => 'Kategorie',
	'is_inverse_of' => 'Ist invers zu',
    'has_domain_and_range' => 'Hat Domain und Range',
	'imported_from'=>'Importiert aus',

	'df_namespace_mappings_page' => 'NamespaceMappings',

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
	
	'block_nsmappings_page_title' => 'Fehler beim Speichern der Namespace mappings',
    'block_nsmappings_error' => 'Es gibt doppelte Präfixe oder doppelte URIs',

	// webadmin
	'df_webadmin' => 'Wiki Administration Tool',
	'df_username' => 'Benutzername',
	'df_password' => 'Passwort',
	'df_login' => 'Einloggen',
	'df_linktowiki' => 'Gehe zum Wiki',
    'df_logout' => 'Ausloggen',
	'df_webadmin_login_failed' => 'Login fehlgeschlagen',
	'df_webadmin_findall' => 'Suche all',
	'df_webadmin_installall' => 'Install selected',
	'df_webadmin_about' => 'Über',
	'df_webadmin_status_text' => 'Diese Tabelle zeigt all Pakete und Ontologien, die derzeit in ihrem Wiki installiert sind.',
	'df_webadmin_updatesavailable' => 'Updates verfügbar! Machen Sie ein ',
	'df_webadmin_globalupdate' => 'Globales Update',
	'df_webadmin_extension' => 'Extension',
    'df_webadmin_description' => 'Beschreibung',
    'df_webadmin_action' => 'Aktion',
	'df_webadmin_install' => 'Installieren',
    'df_webadmin_deinstall' => 'De-Installieren',
	'df_webadmin_update' => 'Aktualisieren',
	'df_webadmin_checkdependency' => 'Prüfe auf Abhängigkeiten',
    'df_webadmin_remove' => 'Datei entfernen',
	'df_webadmin_addrepository' => 'Repository hinzufügen',
	'df_webadmin_removerepository' => 'Repository löschen',

	'df_webadmin_statustab' => 'Status',
    'df_webadmin_searchtab' => 'Suche Extensions',
    'df_webadmin_maintenacetab' => 'Systemwiederherstellung',
	'df_webadmin_uploadtab' => 'Hochladen von lokalen Bundles/Ontologien',
	'df_webadmin_settingstab' => 'Registrierte Paketquellen',
	'df_webadmin_localsettingstab' => 'LocalSettings',
	'df_webadmin_serverstab' => 'Server',
	'df_webadmin_logtab' => 'Logs',
	'df_webadmin_profilertab' => 'Profiler',
	'df_webadmin_contentbundletab' => 'Content bundles',
	
	'df_webadmin_restorepoint' => 'Rücksetzpunkt',
	'df_webadmin_creationdate' => 'Datum der Erzeugung',
	'df_webadmin_version' => 'Version',
	'df_webadmin_upload' => 'Hochladen',
	'df_webadmin_restore' => 'Wiederherstellen',
    'df_webadmin_removerestore' => 'Lösche',
	'df_webadmin_nothingfound' => 'Keine passenden Pakete für <b>"$1"</b> gefunden!',
	'df_webadmin_searchinfoifnothingfound' => 'Um das Ontoprise-Repository zu browsen klicken Sie hier: ',
	'df_webadmin_norestorepoints' => 'Keine Wiederherstellungspunkte gefundend.',
	'df_webadmin_nouploadedfiles' => 'Keine Dateien gefunden.',

	'df_webadmin_maintenancetext' => 'Dieser Tab erlaubt das Erzeugen und Wiederherstellen von Wiederherstellungspunkt. Wiederherstellungspunkte speichern den Zustand einer Wiki-Installation inkl. Daten..',
	'df_webadmin_settingstext' =>  'Dieser Tab erlaubt das Hinzufügen und Löschen von Repository-URLs.',
	'df_restore_warning' => 'Ein Rücksetzen bewirkt sowohl die Zurücksetzung der Installation wie auch des Inhalts des Wikis. Wollen Sie fortfahren?',
	'df_remove_restore_warning' => 'Wiederherstellungspunkt wird gelöscht!',
    'df_uninstall_warning' => 'Die folg. Extensions werden de-installiert. Wollen Sie fortfahren?',
    'df_globalupdate_warning' => 'Global Update durchführen?',
	'df_checkextension_heading' => 'Extension-Details',
	'df_select_extension' => 'Wähle Extension',
	'df_webadmin_localsettings_description' => "Editieren die Konfiguration einer Extension in LocalSettings.php. Nicht vergessen die Änderungen zu speichern. Wenn Sie die Datei LocalSettings.php in irgendeiner Form manuell editieren, verändern Sie nicht die Extension-Tags, sonst geht diese Ansicht kaputt.",
	'df_webadmin_logtab_description' => 'Diese Seite zeigt Links zu Log-Dateien vorheriger Operationen.',
	'df_webadmin_profilertab_description' => 'Diese Seite erlaubt Zugriff auf den internen MW profiler',
	'df_webadmin_contentbundletab_description' => 'Here you can export and download bundles as zip files. To see a list of bundles you need SMWHalo installed.',
    'df_webadmin_contentbundle_nosmwhalo' => 'SMWHalo not available, ie. querying for content bundles not possible. Please enter the bundle name manually.',
    'df_webadmin_querying_contentbundle_failed' => 'Querying content bundles failed.',
    'df_webadmin_export_bundle' => 'Exportiere Bundle',
	
	'df_webadmin_configureservers' => 'Hier können Sie die Server ihrer Wiki-Installation neu starten oder stoppen. Bitte warten Sie ein paar Sekunden bis WAT den Status neu geladen hat.',
	'df_webadmin_process_runs' => 'läuft',
    'df_webadmin_process_doesnot_run' => 'läuft nicht',
	'df_webadmin_process_unknown' => 'lädt neu...',
	'df_webadmin_server_execute' => 'Ausführen',
    'df_webadmin_server_start' => 'start',
    'df_webadmin_server_end' => 'stop',
	'df_webadmin_refresh' => 'Aktualisieren',
	'df_webadmin_download_profilinglog' => 'Herunterladen',
	'df_webadmin_filter' => 'Filter',
	'df_webadmin_finalize_message' => 'Es gibt nicht initialisierte Extensions. Klicken sie hier um die Initialisierung zu starten.',
	'df_webadmin_finalize' => 'Finalisieren',
	'df_webadmin_upload_message' => 'Hier können Sie Bundles ($1) und Ontologie-Dateien ($2) hochladen',
	'df_webadmin_clearlog' => 'Lösche Log',
	'df_webadmin_loglink' => 'Link',
	'df_webadmin_logdate' => 'Datum der Operation',
	'df_loglink_hint' => 'Öffnet neues Tab',
	'df_webadmin_contentbundle_file' => 'Bundle',
    'df_webadmin_contentbundle_creationdate' => 'Erzeugt am',
    'df_webadmin_contentbundle_nobundles' => 'Noch keine Bundles exportiert.',
	
	'df_webadmin_profilertab_requests' => 'Gemessene HTTP Anfragen',
	
	'df_webadmin_newreleaseavailable' => 'Neues Release verfügbar! Sehen Sie in der $1 nach.',
    'df_webadmin_repository_link' => 'Repository-Liste',
	
	'df_webadmin_watsettingstab' => 'Einstellungen',
    'df_webadmin_watsettingstab_description' => 'Allgemeine Einstellungen von WAT',
	
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
    'smw_importontologybot' => 'Importiere eine Ontologie',
    'smw_gard_import_choosefile' => 'Die folgenden Datei-Typen $1 sind erlaubt.',
    'smw_gard_import_addfiles' => 'Füge $2-Dateien hinzu per $1.',
    'smw_gard_import_nofiles' => 'Keine Dateien des Typs $1 verfügbar.',
    'smw_gard_import_docu' => 'Importiert eine Ontologie-Datei.',
    'smw_df_missing' => 'Um die Gardening Bots nutzen zu können müssen Sie das Wiki admin tool installieren! <br/> Folgen Sie dem Link für weitere Informationen: '
    
	);
}
