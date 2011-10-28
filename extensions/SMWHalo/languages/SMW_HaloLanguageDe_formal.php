<?php
/*  Copyright 2007, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
*
*  @file
*  @ingroup SMWHaloLanguage
*  @author Ontoprise
*/

global $smwgHaloIP;
include_once($smwgHaloIP . '/languages/SMW_HaloLanguage.php');

class SMW_HaloLanguageDe_formal extends SMW_HaloLanguage {

protected $smwContentMessages = array(
    
    'smw_derived_property'  => 'Das ist ein abgeleitetes Property.',
    'smw_sparql_disabled'=> 'Keine SPARQL-Unterstützung aktiviert.',
    'smw_viewinOB' => 'Im Ontology-Browser öffnen',
    'smw_wysiwyg' => 'WYSIWYG',

    'smw_att_head' => 'Attribute',
    'smw_rel_head' => 'Relationen zu anderen Seiten',
    'smw_spec_head' => 'Spezielle Eigenschaften',
    'smw_predefined_props' => 'Das ist das vordefinierte Attribut "$1"',
    'smw_predefined_cats' => 'Das ist die vordefinierte Kategorie "$1"',

    'smw_noattribspecial' => 'Die spezielle Eigenschaft „$1“ ist kein Attribut (bitte „::“ anstelle von „:=“ verwenden).',
    'smw_notype' => 'Dem Attribut wurde kein Datentyp zugewiesen.',

    /*Messages for Autocompletion*/
    'tog-autotriggering' => 'Manuelle auto-completion',
    'smw_ac_typehint'=> 'Typ: $1',
    'smw_ac_typerangehint'=> 'Typ: $1 | Range: $2',
    'smw_ac_datetime_proposal'=>'<Monat> <Tag>, <Jahr>|<Tag>-<Monat>-<Jahr>',
    'smw_ac_geocoord_proposal'=>'<Breitengrad>° N, <Längengrad>° W|<Breitengrad>, <Längengrad>',
    'smw_ac_email_proposal'=>'irgendwer@irgendwo.com',
    'smw_ac_temperature_proposal'=>'<Zahl> K, <Zahl> °C, <Zahl> °F, <Zahl> °R',
    'smw_ac_telephone_proposal'=>'tel:+49-721-5453334',
    'smw_ac_category_has_icon' => 'Kategorie hat Bild',
    'smw_ac_tls' => 'Liste von Typen',

    // Messages for SI unit parsing
    'smw_no_si_unit' => 'Einheit nicht in SI-Representation. ',
    'smw_too_many_slashes' => 'Zu viele Slashes in SI-Representation. ',
    'smw_too_many_asterisks' => '"$1" contains several *\'s in sequence. ',
    'smw_denominator_is_1' => "The denominator must not be 1.",
    'smw_no_si_unit_remains' => "There remains no SI unit after optimization.",
    'smw_invalid_format_of_si_unit' => 'Invalid format of SI unit: $1 ',
    // Messages for the chemistry parsers
    'smw_not_a_chem_element' => '"$1" is not a chemical element.',
    'smw_no_molecule' => 'There is no molecule in the chemical formula "$1".',
    'smw_chem_unmatched_brackets' => 'The number of opening brackets does not match the closing ones in "$1".',
    'smw_chem_syntax_error' => 'Syntax error in chemical formula "$1".',
    'smw_no_chemical_equation' => '"$1" is not a chemical equation.',
    'smw_no_alternating_formula' => 'There is a missing or needless operator in "$1".',
    'smw_too_many_elems_for_isotope' => 'Only one element can be given for an isotope. A molecule was provided instead: "$1".',
    // Messages for attribute pages
    'smw_attribute_has_type' => 'This attribute has the datatype ',
    // Messages for help
    'smw_help_askown' => 'Ask your own question',
    'smw_help_askownttip' => 'Add your own question to the wiki helppages where it can be answered by other users',
    'smw_help_pageexists' => "This question is already in our helpsystem.\nClick 'more' to see all questions.",
    'smw_help_error' => "Oops. An error seems to have occured.\nYour question could not be added to the system. Sorry.",
    'smw_help_question_added' => "Your question has been added to our help system\nand can now be answered by other wiki users.",
    // Messages for CSH
    'smw_csh_icon_tooltip' => 'Klicken Sie hier für Hilfe und wenn Sie Feedback zu den SMW+ Entwicklern senden möchten.'
);

protected $smwUserMessages = array(
    'specialpages-group-smwplus_group' => 'SMW+',
    'smw_devel_warning' => 'Diese Funktion befindet sich zur Zeit in Entwicklung und ist eventuell noch nicht voll einsatzfähig. Eventuell ist es ratsam, den Inhalt des Wikis vor der Benutzung dieser Funktion zu sichern.',

    'smw_relation_header' => 'Seiten mit der Relation „$1“',
    
    'smw_subproperty_header' => 'Sub-Attribute von "$1"',
    'smw_subpropertyarticlecount' => '<p>Zeige $1 Sub-Attribute.</p>',
    
    /*Messages for category pages*/
    'smw_category_schemainfo' => 'Schema-Information für Kategorie "$1"',
    'smw_category_properties' => 'Attribute',
    'smw_category_properties_range' => 'Attribute mit Range: "$1"',
    
    'smw_category_askforallinstances' => 'Frag nach allen direkten und indirekten Instanzen von "$1"',
    'smw_category_queries' => 'Queries für Kategorien',
    
    'smw_category_nrna' => 'Seiten mit falsch zugewiesener Domäne "$1".',
    'smw_category_nrna_expl' => 'Diese Seite hat eine Domäne, ist aber kein Attribut',
    'smw_category_nrna_range' => 'Seiten mit falsch zugewiesener Range "$1".',
    'smw_category_nrna_range_expl' => 'Diese Seite hat eine Range, ist aber kein Attribut',
    
    'smw_exportrdf_all' => 'Exportiere alle semantischen Daten',
    
    /*Messages for Search Triple Special*/
    'searchtriple' => 'Einfache semantische Suche', //name of this special
    'smw_searchtriple_header' => '<h1>Suche nach Relationen und Attributen</h1>',
    'smw_searchtriple_docu' => "<p>Benutzen Sie die Eingabemaske um nach Seiten mit bestimmten Eigenschaften zu suchen. Die obere Zeile dient der Suche nach Relationen, die untere der Suche nach Attributen. Sie können beliebige Felder leer lassen, um nach allen möglichen Belegungen zu suchen. Lediglich bei der Eingabe von Attributwerten (mit den entsprechenden Maßeinheiten) verlangt die Angabe des gewünschten Attributes.</p>\n\n<p>Beachten Sie, dass es zwei Suchknöpfe gibt. Bei Druck der Eingabetaste wird vielleicht nicht die gewünschte Suche durchgeführt.</p>",
    'smw_searchtriple_subject' => 'Seitenname (Subjekt):',
    'smw_searchtriple_relation' => 'Name der Relation:',
    'smw_searchtriple_attribute' => 'Name des Attributs:',
    'smw_searchtriple_object' => 'Seintenname (Objekt):',
    'smw_searchtriple_attvalue' => 'Wert des Attributs:',
    'smw_searchtriple_searchrel' => 'Suche nach Relationen',
    'smw_searchtriple_searchatt' => 'Suche nach Attributen',
    'smw_searchtriple_resultrel' => 'Suchergebnisse (Relationen)',
    'smw_searchtriple_resultatt' => 'Suchergebnisse (Attribute)',
    /*Messages for Relation Special*/
    'relations' => 'Relationen',
    'smw_relations_docu' => 'In diesem Wiki gibt es die folgenden Relationen:',
    /*Messages for WantedRelations*/
    'wantedrelations' => 'Gewünschte Relationen',
    'smw_wanted_relations' => 'Folgende Relationen haben bisher keine erläuterende Seite, obwohl sie bereits für die Beschreibung anderer Seiten verwendet werden.',
    /*Messages for Attribute Special*/
    'properties' => 'Attribute',
    'smw_properties_docu' => 'In diesem Wiki gibt es die folgenden Attribute:',
    'smw_attr_type_join' => ' mit $1',
    'smw_properties_sortalpha' => 'Sortiere alphabetisch',
    'smw_properties_sortmoddate' => 'Sortiere nach Änderungsdatum',
    'smw_properties_sorttyperange' => 'Sortiere nach Typ/Range',

    'smw_properties_sortdatatype' => 'Datatype properties',
    'smw_properties_sortwikipage' => 'Wikipage properties',
    'smw_properties_sortnary' => 'Record properties',
    /*Messages for Unused Relations Special*/
    'unusedrelations' => 'Verwaiste Relationen',
    'smw_unusedrelations_docu' => 'Die folgenden Relationenseiten existieren, obwohl sie nicht verwendet werden.',
    /*Messages for Unused Attributes Special*/
    'unusedattributes' => 'Verwaiste Attribute',
    'smw_unusedattributes_docu' => 'Die folgenden Attributseiten existieren, obwohl sie nicht verwendet werden.',

    /*Messages for DataExplorer*/
    'dataexplorer' => 'DataExplorer',
    'smw_ac_hint' => 'Drücken Sie Ctrl+Alt+Space für die Auto-Vervollständigung. (Ctrl+Space im IE)',
    'smw_ob_categoryTree' => 'Kategorie-Baum',
    'smw_ob_attributeTree' => 'Attribut-Baum',

    'smw_ob_instanceList' => 'Instanzen',
    'smw_ob_rel' => 'Relationen',
    'smw_ob_att' => 'Attribute',
    'smw_ob_relattValues' => 'Werte',
    'smw_ob_relattRangeType' => 'Wertebereich',
    'smw_ob_filter' => 'Filter',
    'smw_ob_filterbrowsing' => 'Filtere',
    'smw_ob_reset' => 'Zurücksetzen',
    'smw_ob_cardinality' => 'Kardinalität',
    'smw_ob_transsym' => 'Transitivität/Symmetrie',
    'smw_ob_footer' => '',
    'smw_ob_no_categories' => 'Keine Kategorien verfügbar.',
    'smw_ob_no_instances' => 'Keine Instanzen verfügbar.',
    'smw_ob_no_attributes' => 'Keine Attribute verfügbar.',
    'smw_ob_no_relations' => 'Keine Relationen verfügbar.',
    'smw_ob_no_annotations' => 'Keine Annotation verfügbar.',
    'smw_ob_no_properties' => 'Keine Attribute verfügbar.',
    'smw_ob_help' => 'Der Ontology-Browser hilft ihnen sich im Wiki zurechtzufinden, das Schema zu untersuchen und ganz allgemein Seiten zu finden.
            Benutzen Sie den Filter-Mechanismus oben links um bestimmte Elemente der Ontologie zu finden und schränken sie das Ergebnis mit Hilfe der
            Filter unter jeder Spalte ein. Der Selektionsfluss ist anfangs von rechts nach links. Sie können die Pfeile durch Anklicken aber umdrehen.',

    'smw_ob_undefined_type' => '*keine Range definiert*',
    'smw_ob_hideinstances' => 'Verstecke Instanzen',
    'smw_ob_onlyDirect' => 'geerbten Eigenschaften anzeigen',   
    'smw_ob_onlyAssertedCategories' => 'nur explizite Kategorien anzeigen',
    'smw_ob_showRange' => 'zeige Eigenschaften der ausgewählten Kategorie',
    'smw_ob_hasnumofsubcategories' => 'Anzahl Unterkategorien',
    'smw_ob_hasnumofinstances' => 'Anzahl Instanzen',
    'smw_ob_hasnumofproperties' => 'Anzahl Attribute',
    'smw_ob_hasnumofpropusages' => 'Attribut wurd $1-mal annotiert.',
    'smw_ob_hasnumoftargets' => 'Instanz wurde $1-mal verlinkt.',
    'smw_ob_hasnumoftempuages' => 'Template wurde $1-mal benutzt.',
    'smw_ob_invalidtitle' => '!!!fehlerhafter Titel!!!',
    
    /* Commands for Data Explorer */
    'smw_ob_cmd_createsubcategory' => 'Subkategorie hinzufügen',
    'smw_ob_cmd_createsubcategorysamelevel' => 'Kategorie hinzufügen',
    'smw_ob_cmd_renamecategory' => 'Umbenennen',
    'smw_ob_cmd_createsubproperty' => 'Property hinzufügen',
    'smw_ob_cmd_editproperty' => 'Editiere Property',
    'smw_ob_cmd_createsubpropertysamelevel' => 'Subproperty hinzufügen',
    'smw_ob_cmd_renameproperty' => 'Umbenennen',
    'smw_ob_cmd_renameinstance' => 'Umbenennen',
    'smw_ob_cmd_deleteinstance' => 'Instanz löschen',
    'smw_ob_cmd_createinstance' => 'Instanz erzeugen',
    'smw_ob_cmd_addpropertytodomain' => 'Attribut hinzufügen zur Kategorie: ',
    
    /* Advanced options in the Data Explorer */
    'smw_ob_source_wiki' => "-Wiki- (alle Bundles)" ,
    'smw_ob_advanced_options' => "Einstellungen" ,
    'smw_ob_select_datasource' => "Zu durchsuchende Datenquellen:" ,
    'smw_ob_select_bundle' => "Zu durchsuchendes Bundle" ,
    'smw_ob_select_multiple' => "Sie können <b>mehrere</b> Datenquellen auswählen, indem Sie <b>STRG</b> gedückt halten und die Einträge anklicken.",
    'smw_ob_ts_not_connected' => "Es wurde kein Triple Store gefunden. Bitte fragen Sie Ihren Wikiadministrator!",


    /* Combined Search*/
    'smw_combined_search' => 'Combined Search',
    'smw_cs_entities_found' => 'Die folgenden Elemente wurden in der Ontologie gefunden:',
    'smw_cs_attributevalues_found' => 'Die folgenden Instanzen enthalten Attribut-Werte die ihrer Suche entsprechen.',
    'smw_cs_aksfor_allinstances_with_annotation' => 'Frage nach allen Instanzen von \'$1\' die einen Annoatation von \'$2\' haben.',
    'smw_cs_askfor_foundproperties_and_values' => 'Frage Instanz \'$1\' nach allen gefunden Attribute.',
    'smw_cs_ask'=> 'Zeige',
    'smw_cs_noresults' => 'Kein Element der Ontologie entspricht ihren Suchwörtern',
    'smw_cs_searchforattributevalues' => 'Suche nach Attributwerten, die ihren Suchwörtern entsprechen',
    'smw_cs_instances' => 'Artikel',
    'smw_cs_properties' => 'Attribute',
    'smw_cs_values' => 'Werte',
    'smw_cs_openpage' => 'Öffne Seite',
    'smw_cs_openpage_in_ob' => 'Öffne Seite im Data Explorer',
    'smw_cs_openpage_in_editmode' => 'Editiere Seite',
    'smw_cs_no_triples_found' => 'Keine Tripel gefunden!',

    'smw_autogen_mail' => 'Das ist eine automatisch generierte E-Mail. Nicht antworten!',
    

    /*Messages for ContextSensitiveHelp*/
    'contextsensitivehelp' => 'Kontext Sensitive Hilfe',
    'smw_contextsensitivehelp' => 'Kontext Sensitive Hilfe',
    'smw_csh_newquestion' => 'Dies ist eine neue Hilfeseite. Klicken Sie, um sie zu beantworten!',
    'smw_csh_nohelp' => 'Dem System wurden noch keine relevanten Hilfeseiten hinzugefügt.',
    'smw_csh_refine_search_info' => 'Sie k&ouml;nnen Ihre Suche nach Hilfe durch die Angabe eines Seitentyps und/oder einer Aktion weiter verfeinern:',
    'smw_csh_page_type' => 'Typ der Seite',
    'smw_csh_action' => 'Aktion',
    'smw_csh_ns_main' => 'Main (Standard Wikiartikel)',
    'smw_csh_all' => 'ALLE',
    'smw_csh_search_special_help' => 'Sie k&ouml;nnen auch nach Hilfe zu bestimmten Funktionen des Wikis suchen:',
    'smw_csh_show_special_help' => 'Suche nach Hilfe &uuml;ber:',
    'smw_csh_categories' => 'Kategorien',
    'smw_csh_properties' => 'Attribute',
    'smw_csh_mediawiki' => 'MediaWiki Hilfe',
    /* Messages for the CSH discourse state. Do NOT edit or translate these
     * otherwise CSH will NOT work correctly anymore
     */
    'smw_csh_ds_ontologybrowser' => 'DataExplorer',
    'smw_csh_ds_queryinterface' => 'QueryInterface',
    'smw_csh_ds_combinedsearch' => 'Search',

    /*Messages for Query Interface*/
    'queryinterface' => 'Query Interface',
    'smw_queryinterface' => 'Query Interface',
    'smw_qi_add_category' => 'Kategorie hinzuf&uuml;gen',
    'smw_qi_add_instance' => 'Instanz hinzuf&uuml;gen',
    'smw_qi_add_property' => 'Attribut hinzuf&uuml;gen',
    'smw_qi_add' => 'Hinzuf&uuml;gen',
    'smw_qi_confirm' => 'OK',
    'smw_qi_cancel' => 'Abbrechen',
    'smw_qi_delete' => 'L&ouml;schen',
    'smw_qi_close' => 'Schlie&szlig;en',
    'smw_qi_update' => 'Aktualisieren',
    'smw_qi_discard_changes' => '&Auml;nderungen verwerfen',
    'smw_qi_preview' => 'Vorschau',
    'smw_qi_fullpreview' => 'zeige vollst&auml;ndiges Ergebnis',
    'smw_qi_no_preview' => 'Noch keine Vorschau verf&uuml;gbar',
    'smw_qi_clipboard' => 'In die Zwischenablage kopieren',
    'smw_qi_reset' => 'Query zur&uuml;cksetzen',
    'smw_qi_usetriplestore' => 'Mit inferierten Ergebnissen aus dem triple store',
    'smw_qi_reset_confirm' => 'M&ouml;chten Sie Ihren Query wirklich zur&uuml;ck setzen?',
    'smw_qi_querytree_heading' => 'Query Baumnavigation',
    'smw_qi_main_query_name' => 'Hauptquery',
    'smw_qi_section_option' => 'Query Einstellungen',
    'smw_qi_section_definition' => 'Query Definition',
    'smw_qi_section_result' => 'Ergebnis',
    'smw_qi_preview_result' => 'Result Preview',
    'smw_qi_layout_manager' => 'Formatiere Query',
    'smw_qi_table_column_preview' => 'Vorschau der Tabellenspalten',
    'smw_qi_article_title' => 'Artikel',
    'smw_qi_load' => 'Query laden',
    'smw_qi_save' => 'Query speichern',
    'smw_qi_close_preview' => 'Vorschau schlie&szlig;en',
    'smw_qi_querySaved' => 'Der Query wurde erfolgreich gespeichert',
    'smw_qi_exportXLS' => 'Ergebnisse nach Excel exportieren',
    'smw_qi_showAsk' => 'Kompletten Query anzeigen',
    'smw_qi_ask' => '&lt;ask&gt; syntax',
    'smw_qi_parserask' => '{{#ask syntax',
    'smw_qi_queryastree' => 'Query als Baum',
    'smw_qi_queryastext' => 'Query als Text',
    'smw_qi_querysource' => 'Query Wikitext',
    'smw_qi_queryname' => 'Query Name',
    'smw_qi_printout_err1' => 'Das ausgew&auml;hlte Format f&uuml; das Ergebnis ben&ouml;tigt mindestens ein weiteres Attribut, dessen Werte im Ergebnis ausgegeben werden.',
    'smw_qi_printout_err2' => 'Das ausgew&auml;hlte Format f&uuml; das Ergebnis ben&ouml;tigt mindestens ein Attribut des Typs Datum, dessen Werte im Ergebnis ausgegeben werden.',
    'smw_qi_printout_err3' => 'Das ausgew&auml;hlte Format f&uuml; das Ergebnis ben&ouml;tigt mindestens ein weiteres nummerisches Attribut, dessen Werte im Ergebnis ausgegeben werden.',
    'smw_qi_printout_err4' => 'F&uuml;r diesen Query gibt es keine Ergebnisse.',
    'smw_qi_printout_err4_lod' => 'Bitte pr&uuml;fen Sie die ausgew&auml;hlte Datenquelle.',
    'smw_qi_printout_notavailable' => 'Das Ergebnis kann f&uuml;r dieses Format nicht im Query Interface angezeigt werden.',
    'smw_qi_datasource_select_header' => 'W&auml;hle eine Datenquelle (dr&uuml;cke STRG um mehrere Quellen auszuw&auml;hlen)',
    'smw_qi_showdatarating' => 'Aktiviere die Bewertung der Daten',
    'smw_qi_showmetadata' => 'Zeige Metainformationen zu den Daten',
    'smw_qi_showdatasource' => 'Zeige nur Informationen zur Datenquelle',
    'smw_qi_maintab_query' => 'Neue Query',
    'smw_qi_maintab_load' => 'Lade Query',
    'smw_qi_load_criteria' => 'Suche und finde (existierende) Queries mit den folgenden Bedingungen:',
    'smw_qi_load_selection_*' => 'Query beinhaltet',
    'smw_qi_load_selection_i' => 'Artikel Name',
    'smw_qi_load_selection_q' => 'Query Name',
    'smw_qi_load_selection_p' => 'benutztes Attribut',
    'smw_qi_load_selection_c' => 'benutzte Kategorie',
    'smw_qi_load_selection_r' => 'Format des Ergebnis',
    'smw_qi_load_selection_s' => 'Attribut im Ergebnis',
    'smw_qi_button_search' => 'Suche',
    'smw_qi_button_load' => 'Lade ausgew&auml;hlte Query',
    'smw_qi_queryloaded_dlg' => 'Ihre Query wurde in das Query Interface geladen.',
    'smw_qi_link_reset_search' => 'Suche zur&uuml;cksetzen',
    'smw_qi_loader_result' => 'Ergebis',
    'smw_qi_loader_qname' => 'Query-Name',
    'smw_qi_loader_qprinter' => 'Ergebis Format',
    'smw_qi_loader_qpage' => 'aus Artikel',
    'smw_qi_tpee_header' => 'W&auml;hle trust policy f&uuml;r die Query Ergebnisse',
    'smw_qi_tpee_none' => 'keine trust policy verwenden',
    'smw_qi_dstpee_selector_0' => 'W&auml;hle Datenquellen',
    'smw_qi_dstpee_selector_1' => 'W&auml;hle Trust Policy',
    'smw_qi_switch_to_sparql' => 'Switch to SPARQL',
   'smw_qi_add_subject' => 'Add Subject',
      'smw_qi_category_name' => 'Category name',
      'smw_qi_add_another_category' => 'Add another category (OR)',
      'smw_qi_subject_name' => 'Subject name',
      'smw_qi_column_label' => 'Column label',
     'smw_qi_add_and_filter' => 'Add new filter',
      'smw_qi_filters' => 'Filters',
      'smw_qi_show_in_results' => 'Show in results',
      'smw_qi_property_name' => 'Property name',
      'smw_qi_value_must_be_set' => 'Value must be set',
      'smw_qi_value_name' => 'Value name',

    /*Tooltips for Query Interface*/
    'smw_qi_tt_addCategory' => 'Indem man eine Kategorie hinzuf&uuml;gt, werden nur Artikel aus dieser Kategorie ber&uuml;cksichtigt',
    'smw_qi_tt_addInstance' => 'Indem man ein Instanz hinzuf&uuml;gt, wird nur der ensprechende Artikel ber&uuml;cksichtigt',
    'smw_qi_tt_addProperty' => 'Indem man ein Attribut hinzuf&uuml;gt, kann man sich die Werte dieses Attribute anzeigen lassen oder erlaubte Werte vorgeben',
    'smw_qi_tt_tcp' => 'Die Vorschau der Tabellenspalten zeigt, aus welchen Spalten die Ergebnistabelle bestehen wird',
    'smw_qi_tt_prp' => 'Die Result Preview zeigt Ergebnisse der Abfrage w&auml;hrend diese entsteht',
    'smw_qi_tt_qlm' => 'Der Query Layout Manager erlaubt es, das Ausgabeformat Ihres Queries anzupassen',
    'smw_qi_tt_qdef' => 'Definiere die Query',
    'smw_qi_tt_previewres' => 'Vorschau und Formatierung der Ergebnisse',
    'smw_qi_tt_update' => 'Aktualisiert die Ergebnisse in der Vorschau',
    'smw_qi_tt_preview' => 'Anzeigen einer Vorschau der Ergebnisse, einschlie&szlig;lich der Layouteinstellungen',
    'smw_qi_tt_fullpreview' => 'Anzeigen einer vollst&auml;ndigen Vorschau aller Ergebnisse, einschlie&szlig;lich der Layouteinstellungen',
    'smw_qi_tt_clipboard' => 'Kopiert den Query in die Zwischenablage, so dass dieser einfach in einen Artikel eingef&uuml;gt werden kann',
    'smw_qi_tt_showAsk' => 'Anzeigen des kompletten Ask-Queries',
    'smw_qi_tt_reset' => 'Zur&uuml;cksetzen des gesamten Queries',
    'smw_qi_tt_format' => 'Ausgabeformat des Queries',
    'smw_qi_tt_link' => 'Bestimmt, welche Teile der Ergebnistabelle als Link dargestellt werden',
    'smw_qi_tt_intro' => 'Text, der vor den Queryergebnissen ausgegeben wird',
    'smw_qi_tt_outro' => 'Text, der hinter den Queryergebnissen ausgegeben wird',
    'smw_qi_tt_sort' => 'Spalte, nach welcher die Sortierung erfolgt',
    'smw_qi_tt_limit' => 'Maximale Anzahl der angezeigten Ergebnisse',
    'smw_qi_tt_offset' => 'Offset Wert für Ergebnisse, die gezeigt werden sollen',
    'smw_qi_tt_mainlabel' => '&uuml;berschrift der ersten Spalte',
    'smw_qi_tt_order' => 'Auf- oder absteigende Sortierung',
    'smw_qi_tt_headers' => 'Tabellen&uuml;berschriften anzeigen oder nicht',
    'smw_qi_tt_default' => 'Text, der ausgegeben wird, falls keine Ergebnisse existieren',
    'smw_qi_tt_treeview' => 'Zeige die Query in einem Baum',
    'smw_qi_tt_textview' => 'Beschreibe die Query als Freitext',
    'smw_qi_tt_option' => 'Definieren allgemeiner Einstellungen zum Ausf&uuml;hren der Query',
    'smw_qi_tt_maintab_query' => 'Erstellen einer neuen Query',
    'smw_qi_tt_maintab_load' => 'Laden einer im Wiki existierenden Query',
    'smw_qi_tt_addSubject' => 'Add Subject',
      'smw_qi_tt_delete' => 'Delete selected tree node',
      'smw_qi_tt_cancel' => 'Cancel all changes, return to the starting point',

    /* Annotation */
    'smw_annotation_tab' => 'Seite annotieren',
    'smw_annotating'     => 'Annotiere $1',
    'annotatethispage'   => 'Annotiere diese Seite',

    /* Refactor preview */
    'refactorstatistics' => 'Refactor Statistics',
    'smw_ob_link_stats' => '&Ouml;ffne refactor statistics',
    
    
        
    /* Gardening Issue Highlighting in Inline Queries */
    'smw_iqgi_missing' => 'fehlt',
    'smw_iqgi_wrongunit' => 'falsche Einheit',
    
    

    // SMWHaloAdmin
    'smwhaloadmin' => 'SMWHalo Administration',
    'smw_haloadmin_databaseinit' => 'Databank-Initialisierung',
    'smw_haloadmin_description' => 'Diese Spezialseite unterstützt Sie während der Installation und Aktualisierung von SMWHalo.',
    'smw_haloadmin_databaseinit_description' => 'Die folgende Funktion gewährleistet dass die Datenbank korrekt eingerichtet ist. Klicken Sie "Initialisieren" um das Datenbankschema zu aktualisieren und um benötigte Wiki-Seiten für die Metadaten-Nutzung zu erstellen.   Alternativ können Sie auch das Wartungsskript SMW_setup.php ausführen, dieses finden Sie im Ordner SMWHalo maintenance. ',
    'smw_haloadmin_ok' => 'Die SMWHalo-Extension ist korrekt eingerichtet.',
    
    'smw_ts_notconnected' => 'TSC nicht erreichbar. Prüfe Server unter: $1',
    'asktsc' => 'Ask triplestore',
    'smw_tsc_query_not_allowed' => 'Leere Query nicht erlaubt.',

    
    //skin
    'smw_search_this_wiki' => 'Wiki durchsuchen',
    'smw_last_visited' => 'Zuletzt besucht:',
    'smw_pagecreation' => 'Erstellt von $1 am $2, um $3 Uhr',
    'smw_start_discussion' => 'Starte $1',
    'more_functions' => 'Mehr',
    'smw_treeviewleft' => 'Treeview auf der linken Seite anzeigen',
    'smw_treeviewright' => 'Treeview auf der rechten Seite anzeigen',

// Geo coord data type
    'semanticmaps_lonely_unit'     => 'Keine Nummer vor dem "$1" Symbol gefunden.', // $1 is something like Â°
    'semanticmaps_bad_latlong'     => 'Breitengrad und L&auml;ngengrad d&uuml;rfen nur einmal und mit korrekten Koordinaten angegeben werden',
    'semanticmaps_abb_north'       => 'N',
    'semanticmaps_abb_east'        => 'O',
    'semanticmaps_abb_south'       => 'S',
    'semanticmaps_abb_west'        => 'W',
    'semanticmaps_label_latitude'  => 'Latitude:',
    'semanticmaps_label_longitude' => 'Longitude:',

    
    
    // Tabular Forms
    'smw_tf_paramdesc_add'      => 'Benutzer darf neue Instanzen zum Ergebnis hinzufügen',
    'smw_tf_paramdesc_delete'   => 'Benutzer darf Instanzen aus dem Ergebnis löschen',
    'smw_tf_paramdesc_use_silent_annotation' => "Tabular Forms benutzt das Silent Annotation Template zum Erstellen neuer Annotationen",


    //Querylist Special Page
    'querylist' => "Gespeicherte Queries",

    //tabular forms
    'tabf_load_msg' => "Tabular forms wird geladen.",

    'tabf_add_label' => "instanz hinzufügen",
    'tabf_refresh_label' => "Aktualisieren",
    'tabf_save_label' => "Änderungen übernehmen",
    
    'tabf_status_unchanged' => "Diese Instanz wurde noch nicht editiert.",
    'tabf_status_notexist_create' => "Diese Instanz existiert noch nicht und wird erzeugt.",
    'tabf_status_notexist' => "Diese Instanz existiert nicht.",
    'tabf_status_readprotected' => "Diese Instanz ist lesegeschützt.",
    'tabf_status_writeprotected' => "Diese Instanz ist schreibgeschützt.",
    'tabf_status_delete' => "Diese Instanz wird gelöscht.",
    'tabf_status_modified' => "Diese Instanz wirde geändert.",
    'tabf_status_saved' => "Diese Instanz wurde erfolgreich gespeichert.",
    'tabf_status_pending' => "Änderungen werden übernommen.",
    
    'tabf_response_deleted' => "Diese Instanz wurde in der Zwischenzeit gelöscht.",
    'tabf_response_modified' => "Diese Instanz wurde in der Zwischenzeit geändert.",
    'tabf_response_readprotected' => "Diese Instanz wurde in der Zwischenzeit lesegeschützt.",
    'tabf_response_writeprotected' => "Diese Instanz wurde in der Zwischenzeit schreibgeschützt.",
    'tabf_response_invalidname' => "Diese Instanz hat einen ungültigen Namen.",
    'tabf_response_created' => "Diese Instanz wurde in der Zwischenzeit erstellt.",
    'tabf_response_nocreatepermission' => "Sie besitzen nicht das Recht, diese Instanz zu erzeugen.",
    'tabf_response_nodeletepermission' => "Sie besitzen nicht das Recht, diese Instanz zu löschen.",

    'tabf_update_warning' => "Beim Anwenden der Änderungen sind Fehler aufgetreten. Bitte werfen sie einen Blick in die Status-Icons.",

    'tabf_instancename_blank' => "Instanznamen d&uumlrfen nicht leer sein..",
    'tabf_instancename_invalid' => "'$1' ist kein valider Instanzname..",
    'tabf_instancename_exists' => "'$1' existiert bereits.",
    'tabf_instancename_permission_error' => "Ihnen fehlt die Berechtigung zum Erstellen des Artikels '$1'.",
    'tabf_annotationnamme_invalid' => "'$1' besitzt einen invaliden Annotations-Wert.: Der Wert '$2' des Properties '$3' ist nicht vom Typ $4.",
    
    'tabf_lost_reason_EQ' => "gleich '$1' ist",
    'tabf_lost_reason_NEQ' => "ungleich '$1' ist",
    'tabf_lost_reason_LEQ' => "kleiner oder gleich '$1' ist",
    'tabf_lost_reason_GEQ' => "gr&ouml;sser oder gleich '$1' ist",
    'tabf_lost_reason_LESS' => "kleiner als '$1' ist",
    'tabf_lost_reason_GRTR' => "gr&ouml;sser als '$1' ist",
    'tabf_lost_reason_EXISTS' => "valide ist",
    'tabf_lost_reason_introTS' => "'<span class=\"tabf_nin\">$1</span>', da keiner der Annotations-Werte  ",
    
    'tabf_parameter_write_protected_desc' => "Schreibgesch&uuml;tzte Annotationen",
    'tabf_parameter_instance_preload_desc' => "Preload Wert f&uuml;r Instanznamen.",
    
    'tabf_ns_header' => "Systembenachrichtigungen",
    'tabf_ns_warning_invalid_instance_name' => "&Auml;nderungen k&ouml;nnen gerade nicht gespeichert werden, da einige der neuen Instanznahmen fehlerhaft sind.",
    'tabf_ns_warning_invalid_value' => "Die folgenden Annotations-Werte sind invalide.",
    'tabf_ns_warning_lost_instance_otf' => "die folgenden Instanzen k&ouml;nnten nach dem Speichern nicht mehr Teil des Anfrageergebnisses sein.",
    'tabf_ns_warning_lost_instance' => "Die folgenden Instanzen sind nicht mehr im Anfrageergebnis enthalten.",
    'tabf_ns_warning_save_error' => "Die folgenden Instanzen konnten nicht gespeichert werden, da sie in der Zwischenzeit von jemand anderen ge&auml;ndert wurden.",
    'tabf_ns_warning_add_disabled' => "Der Button zum Hinzuf&uuml;gen von Instanzen musste deaktiviert werden. Bitte markieren die folgen Properties entweder als schreibgesch&uuml;tzt, geben sie einen Preload #wert an oder zeigen sie das Property in der Tabelle an, um den Button wieder zu aktivieren.",
    'tabf_ns_warning_by_system' => "Hinweise und Warnungen des Anfrageprozessors.:",
    
    'tabf_nc_icon_title_lost_instance' => "diese Instanz k&ouml;nnte nach dem Speichern nicht mehr im Anfrageergebnis enthalten sein..",
    'tabf_nc_icon_title_invalid_value' => "einige Annotations-Werte dieser Instanz sind invalide. ",
    'tabf_nc_icon_title_save_error' => "&Auml;nderungen an dieser Instanz konnten nicht gespeichert werden, da sie in der Zwischenzeit von jemand anderem ge&auml;ndert wurde.",
    
    //--- fancy table result printer
    'ftrp_warning' => "Es wurden ungültige replace-Anweisungen mit unbekannten Attributen gefunden:",

);

protected $smwSpecialProperties = array(
    //always start upper-case
    "___cfsi" => array('_siu', 'Entspricht SI'),
    "___CREA" => array('_wpg', 'Erzeuger'),
    "___CREADT" => array('_dat', 'Erzeugt am'),
    "___MOD" => array('_wpg', 'Zuletzt modifiziert von')
);


var $smwSpecialSchemaProperties = array (
    SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT  => 'Hat Domain und Range',
    SMW_SSP_HAS_DOMAIN => 'Hat Domain',
    SMW_SSP_HAS_RANGE => 'Hat Range',
    SMW_SSP_HAS_MAX_CARD => 'Hat max Kardinalität',
    SMW_SSP_HAS_MIN_CARD => 'Hat min Kardinalität',
    SMW_SSP_IS_INVERSE_OF => 'Ist invers zu',
    SMW_SSP_IS_EQUAL_TO => 'Ist gleich zu',
    SMW_SSP_ONTOLOGY_URI => 'Ontologie URI'
    );

var $smwSpecialCategories = array (
    SMW_SC_TRANSITIVE_RELATIONS => 'Transitive Attribute',
    SMW_SC_SYMMETRICAL_RELATIONS => 'Symmetrische Attribute'
);


var $smwHaloDatatypes = array(
    'smw_hdt_chemical_formula' => 'Chemische Formel',
    'smw_hdt_chemical_equation' => 'Chemische Gleichung',
    'smw_hdt_mathematical_equation' => 'Mathematische Gleichung',
    'smw_integration_link' => 'Integrations-Link'
);

protected $smwHaloNamespaces = array(
);

protected $smwHaloNamespaceAliases = array(
);


    /**
     * Function that returns the namespace identifiers.
     */
    public function getNamespaceArray() {
        return array(
            SMW_NS_RELATION       => "Relation",
            SMW_NS_RELATION_TALK  => "Relation_Diskussion",
            SMW_NS_PROPERTY       => "Eigenschaft",
            SMW_NS_PROPERTY_TALK  => "Eigenschaft_Diskussion",
            SMW_NS_TYPE           => "Datentyp", // @deprecated
            SMW_NS_TYPE_TALK      => "Datentyp_Diskussion" // @deprecated
        );
    }

}


