<?php
/**
 * @file
 * @ingroup LinkedData_Language
 */

/*  Copyright 2010, ontoprise GmbH
*   This file is part of the LinkedData-Extension.
*
*   The LinkedData-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The LinkedData-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
 * Internationalization file for TSC
 *
 */

$messages = array();

/** 
 * English
 */
$messages['en'] = array(
    
							
	//--- TSC source definition---
	'tsc_lsdparser_expected_at_least'			=> 'The parameter "$2" must occur at least $1 time(s).',
	'tsc_lsdparser_expected_exactly'			=> 'The parameter "$2" must occur exactly $1 time(s).',
	'tsc_lsdparser_expected_between'			=> 'The parameter "$3" must occur between $1 and $2 time(s).',
	'tsc_lsdparser_title'						=> 'Data source definition for',
	'tsc_lsdparser_success'						=> 'The Linked Data source definition was parsed successfully. The following values will be stored:',							
	'tsc_lsdparser_failed'						=> 'The definition of the data source is faulty. It will not be saved.',			
	'tsc_lsdparser_error_intro'					=> 'The following errors were found in the definition of the data source:',				
							
	'tsc_lsd_id'						=> "ID",
	'tsc_lsd_changefreq'				=> "Change frequency",
	'tsc_lsd_datadumplocation'			=> "Data dump location",
	'tsc_lsd_description'				=> "Description",
	'tsc_lsd_homepage'					=> "Homepage",
	'tsc_lsd_label'						=> "Label",
	'tsc_lsd_lastmod'					=> "Last modification",
	'tsc_lsd_linkeddataprefix'			=> "Linked data prefix",
	'tsc_lsd_sampleuri'					=> "Sample URI",
	'tsc_lsd_sparqlendpointlocation'	=> "SPARQL endpoint location",
	'tsc_lsd_sparqlgraphname'			=> "SPARQL graph name",
	'tsc_lsd_sparqlgraphpattern'		=> "SPARQL graph pattern",
	'tsc_lsd_uriregexpattern'			=> "URI regex pattern",
	'tsc_lsd_vocabulary'				=> "Vocabulary",
	'tsc_lsd_predicatetocrawl'			=> "Predicate to crawl",
        'tsc_lsd_levelstocrawl'			=> "Levels to crawl",

	//--- TSC special pages ---
	'tscsources'	=> 'LOD sources',
	
    //--- TSC Source editor
	'tsc_sp_source_label' => 'Name',
	'tsc_sp_source_source' => 'Data source',
	'tsc_sp_source_lastimport' => 'Last import',
	'tsc_sp_source_changefreq' => 'Change frequency',
	'tsc_sp_source_import' => 'Import',
	'tsc_sp_source_reimport' => '(Re-)Import',
	'tsc_sp_source_update' => 'Update',
        'tsc_sp_source_options' => 'Options',
        'tsc_sp_schema_translation' => 'R2R',
        'tsc_sp_identity_resolution' => 'Silk',
    	'tsc_sp_isimported' => 'Imported',
	'tsc_sp_statusmsg' => 'Status message',

 // Triple Store Admin
    'tsa' => 'Triple store administration',
    'tsc_advertisment' => "'''This special page helps you to administrate the Wiki to triplestore connection.'''<br><br>''You have no triplestore attached to this Wiki.''<br><br>You make this Wiki smarter by connecting a TripleStore to it!<br><br>Connecting the ontoprise products '''TripleStoreConnector Basic''' (free) or '''TripleStoreConnector Professional''' ultimately leads to getting better search results and to making use of data which lives outside this Wiki. <br><br> '''[http://smwforum.ontoprise.com/smwforum/index.php/List_of_Extensions/Triple_store_connector Click here to read what your benefits are and to download a TripleStore!]'''",
    'smw_tsa_welcome' => 'This special page helps you to administrate the wiki/triplestore connection.',
    'smw_tsa_couldnotconnect' => 'Could not connect to a triple store.',
    'smw_tsa_notinitalized' => 'Your wiki is not initialized at the triplestore.',
    'smw_tsa_waitsoemtime'=> 'Please wait a few seconds and then follow this link.',
    'smw_tsa_wikiconfigured' => 'Your wiki is properly connected with the triplestore at $1',
    'smw_tsa_initialize' => 'Initialize',
    'smw_tsa_reinitialize' => 'Re-Initialize',
    'smw_tsa_pressthebutton' => 'Please press the button below.',
    'smw_tsa_addtoconfig' => 'Please add the following lines in your LocalSettings.php and check if the triplestore connector is running.',
    'smw_tsa_addtoconfig2' => 'Note that the call to enableSMWHalo does not have parameters anymore.',
    'smw_tsa_addtoconfig3' => 'If you set a wiki graph ($smwgHaloTripleStoreGraph) make sure it is a valid one and it does not contain a hash (#).',
    'smw_tsa_addtoconfig4' => 'If this does not help, please check out the online-help in the $1.',
    'smw_tsa_driverinfo' => 'Driver information',
    'smw_tsa_status' => 'Status',
    'smw_tsa_rulesupport'=> 'The triplestore driver supports rules, so you should add <pre>$smwgHaloEnableObjectLogicRules=true;</pre> to your LocalSettings.php. Otherwise rules will not work.',
    'smw_tsa_norulesupport'=> 'The triplestore driver does not supports rules, although they are activated in the wiki. Please remove <pre>$smwgHaloEnableObjectLogicRules=true;</pre> from your LocalSettings.php. Otherwise you may get obscure errors.',
    'smw_tsa_tscinfo' => 'Triplestore Connector information',
    'smw_tsa_tscversion' => 'TSC Version',
    'smw_ts_notconnected' => 'TSC not accessible. Check server: $1',
    'asktsc' => 'Ask triplestore',
    'smw_tsc_query_not_allowed' => 'Empty query not allowed when querying TSC.',
    'smw_tsa_loadgraphs'=> 'Loaded graphs',
    'smw_tsa_autoloadfolder'=> 'Auto-load folder',
    'smw_tsa_tscparameters'=> 'TSC parameters',
    'smw_tsa_synccommands'=> 'Synchronization commands',

    // --- Derived facts---
    'tsc_derivedfacts_request_failed' => 'Request for derived facts failed.',
    'smw_df_derived_facts_about' => 'Derived facts about $1',
    'smw_df_static_tab'          => 'Static facts',
    'smw_df_derived_tab'         => 'Derived facts',
    'smw_df_static_facts_about'  => 'Static facts about this article',
    'smw_df_derived_facts_about' => 'Derived facts about this article',
    'smw_df_loading_df'          => 'Loading derived facts...',
    'smw_df_invalid_title'       => 'Invalid article. No derived facts available.',
    'smw_df_no_df_found'         => 'No derived facts found for this article.',
    'smw_df_tsc_advertisment'    => "''You have no triplestore attached to this Wiki.''\n\nYou make this Wiki smarter by connecting a TripleStore to it! Connecting the ontoprise products '''TripleStoreConnector Basic''' (free) or '''TripleStoreConnector Professional''' ultimately leads to getting better search results and to making use of data which lives outside this Wiki.\nClick here to read what your benefits are and to download a [http://smwforum.ontoprise.com/smwforum/index.php/List_of_Extensions/Triple_store_connector TripleStore]!",
    
	
    //--- Non-existing pages ---
    'lod_nep_link'          => 'Create the article <b>$1</b> with the content displayed below.',
    													
);

/** 
 * German
 */
$messages['de'] = array(
   
							
	//--- TSC source definition---
	'tsc_lsdparser_expected_at_least'			=> 'Der Parameter "$2" muss mindestens $1 mal vorkommen.',
	'tsc_lsdparser_expected_exactly'			=> 'Der Parameter "$2" muss genau $1 mal vorkommen.',
	'tsc_lsdparser_expected_between'			=> 'Der Parameter "$3" muss zwischen $1 und $2 mal vorkommen.',
	'tsc_lsdparser_title'						=> 'Datenquellendefinition für',
	'tsc_lsdparser_success'						=> 'Die Linked-Data-Quelldefinition wurde erfolgreich gelesen. Die folgenden Werte werden gespeichert:',
	'tsc_lsdparser_failed'						=> 'Die Definition der Datenquelle ist fehlerhaft. Sie wird nicht gespeichert.',			
	'tsc_lsdparser_error_intro'					=> 'Die folgenden Fehler wurden in der Definition der Datenquelle gefunden:',				

	'tsc_lsd_id'						=> "ID",
	'tsc_lsd_changefreq'				=> "Änderungsrate",
	'tsc_lsd_datadumplocation'			=> "Adresse für Datendumps",
	'tsc_lsd_description'				=> "Beschreibung",
	'tsc_lsd_homepage'					=> "Homepage",
	'tsc_lsd_label'						=> "Bezeichnung",
	'tsc_lsd_lastmod'					=> "Letzte Änderung",
	'tsc_lsd_linkeddataprefix'			=> "Linked data Präfix",
	'tsc_lsd_sampleuri'					=> "Beispiel-URI",
	'tsc_lsd_sparqlendpointlocation'	=> "SPARQL-Endpunktadresse",
	'tsc_lsd_sparqlgraphname'			=> "SPARQL-Graphname",
	'tsc_lsd_sparqlgraphpattern'		=> "SPARQL-Graphpattern",
	'tsc_lsd_uriregexpattern'			=> "URI Regex-Muster",
	'tsc_lsd_vocabulary'				=> "Vokabular",
	'tsc_lsd_predicatetocrawl'			=> "Beim Crawling zu folgendes Prädikat",

	//--- TSC special pages ---
    'tscsources'    => 'LOD-Quellen',
	
	'tsc_sp_source_label' => 'Name',
    'tsc_sp_source_source' => 'Datenquelle',
    'tsc_sp_source_lastimport' => 'Letzter Import',
    'tsc_sp_source_changefreq' => 'Änderungsfrequenz',
	'tsc_sp_source_import' => 'Importiere',
    'tsc_sp_source_reimport' => '(Re-)Importiere',
    'tsc_sp_source_update' => 'Aktualisiere',
	'tsc_sp_isimported' => 'Ist importiert',
	'tsc_sp_statusmsg' => 'Statusnachricht',

    // Triple Store Admin
    'tsa' => 'Triplestore Administration',
    'tsc_advertisment' => "'''Diese Spezialseite hilft bei der Administration eines angeschlossenen Triplestores.'''<br><br>''Derzeit ist kein Triplestore am Wiki angeschlossen.''<br><br>Sie können das Wiki intelligenter machen indem sie einen Triplestore anschließen.<br><br> '''[http://smwforum.ontoprise.com/smwforum/index.php/List_of_Extensions/Triple_store_connector Klicken Sie hier um mehr über die Vorteile zu erfahren!]'''",
    'smw_tsa_welcome' => 'Diese Spezialseite hilft ihnen die Wiki/Triplestore Verbindung zu konfiguieren.',
    'smw_tsa_couldnotconnect' => 'Kann keine Verbindung zu einem Triple store aufbauen.',
    'smw_tsa_notinitalized' => 'Das Wiki ist nicht mit einem Triple store verbunden.',
    'smw_tsa_waitsoemtime'=> 'Bitte warten Sie einige Sekunden und klicken dann auf diesen Link.',
    'smw_tsa_wikiconfigured' => 'Das Wiki ist mit dem Triple store an $1 verbunden.',
    'smw_tsa_initialize' => 'Initialisieren',
    'smw_tsa_reinitialize' => 'Re-Initialisieren',
    'smw_tsa_pressthebutton' => 'Bitte den Knopf unten drücken.',
    'smw_tsa_addtoconfig' => 'Bitte fügen Sie folgende Zeilen in die LocalSettings.php ein und prüfen Sie ob der Triple store connector läuft.',
    'smw_tsa_addtoconfig2' => 'Stellen Sie sicher, dass der Aufruf von enableSMWHalo keine Parameter mehr enthält. ',
    'smw_tsa_addtoconfig3' => 'Stellen Sie ebenso sicher, dass die Graph-URL ($smwgHaloTripleStoreGraph) valide ist. Sie darf keinen Hash (#) enthalten aber sonst frei gewählt werden.',
    'smw_tsa_addtoconfig4' => 'Falls das nicht funktioniert, schauen Sie hier: $1.',

    'smw_tsa_driverinfo' => 'Treiberinformation',
    'smw_tsa_status' => 'Status',
    'smw_tsa_rulesupport'=> 'Der Triplestore-Treiber unterstützt Regeln, deshalb sollten Sie <pre>$smwgHaloEnableObjectLogicRules=true;</pre> in ihrer LocalSettings.php aktivieren. Andernfalls werden Regeln nicht funktionieren.',
    'smw_tsa_norulesupport'=> 'Der Triplestore-Treiber unterstützt keine Regeln, obwohl sie im Wiki aktiviert sind. Bitte entfernen Sie <pre>$smwgHaloEnableObjectLogicRules=true;</pre> aus ihrer LocalSettings.php. Andernfalls könnten Sie seltsame Fehlermeldungen erhalten.',

    'smw_tsa_tscinfo' => 'Triplestore Connector information',
    'smw_tsa_tscversion' => 'TSC Version',

    'smw_tsa_loadgraphs'=> 'Geladene Graphen',
    'smw_tsa_autoloadfolder'=> 'Automatisch geladenes Verzeichnis',
    'smw_tsa_tscparameters'=> 'TSC-Parameter',
    'smw_tsa_synccommands'=> 'Synchronisationskommandos',
    
    // --- Derived facts---
    'tsc_derivedfacts_request_failed' => 'Anfrage fehlgeschlagen',
    'smw_df_derived_facts_about' => 'Abgeleitete Fakten über $1',
    'smw_df_static_tab'          => 'Statische Fakten',
    'smw_df_derived_tab'         => 'Abgeleitete Fakten',
    'smw_df_static_facts_about'  => 'Statische Fakten über diesen Artikel',
    'smw_df_derived_facts_about' => 'Abgeleitete Fakten über diesen Artikel',
    'smw_df_loading_df'          => 'Abgeleitete Fakten werden geladen ...',
    'smw_df_invalid_title'       => 'Ungültiger Artikel. Es sind keine abgeleiteten Fakten verfügbar.',
    'smw_df_no_df_found'         => 'Es wurden keine abgeleiteten Fakten für diesen Artikel gefunden.',
    'smw_df_tsc_advertisment'    => "''Sie haben keinen Triplestore an diesem Wiki angeschlossen.''\n\nConnecting the ontoprise products '''TripleStoreConnector Basic''' (free) or '''TripleStoreConnector Professional''' ultimately leads to getting better search results and to making use of data which lives outside this Wiki.\nClick here to read what your benefits are and to download a [http://smwforum.ontoprise.com/smwforum/index.php/List_of_Extensions/Triple_store_connector TripleStore]!",
    
    //--- Non-existing pages ---
    'lod_nep_link'          => 'Den Artikel <b>$1</b> mit dem unten dargestellten Inhalt erzeugen.',
    
);

/** 
 * German (formal)
 */
$messages['de-formal'] = array_merge($messages['de'], array(
   // messages to overwrite in $messages['de']    
));




