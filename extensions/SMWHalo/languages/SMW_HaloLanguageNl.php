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
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
* 
*/
/**
 * @file
*  @ingroup SMWHaloLanguage
 * @author Ontoprise
 */

global $smwgHaloIP;
include_once($smwgHaloIP . '/languages/SMW_HaloLanguage.php');

class SMW_HaloLanguageNl extends SMW_HaloLanguage {

protected $smwContentMessages = array(

	'smw_viewinOB' => 'Openen in de ontology-browser',

	'smw_att_head' => 'Attribuut-waarden',
	'smw_rel_head' => 'Relaties met andere pagina´s',
	'smw_predefined_props' => 'Dit is de voorgedefinieerde eigenschap "$1"',
	'smw_predefined_cats' => 'Dit is de voorgedefinieerde categorie "$1"',

	'smw_noattribspecial' => 'De speciale eigenschap "$1" is geen attribuut (gebruik "::" in plaats van ":=").',
	'smw_notype' => 'Er is geen type gedefinieerd voor dit attribuut.',
	/*Messages for Autocompletion*/
	'tog-autotriggering' => 'Manuelle auto-completion',
    'smw_ac_typehint'=> 'Type: $1',
    'smw_ac_typerangehint'=> 'Type: $1 | Range: $2',

	// Messages for SI unit parsing
	'smw_no_si_unit' => 'Geen eenheid in de SI voorstelling. ',
	'smw_too_many_slashes' => 'Teveel slashes in de SI voorstelling. ',
	'smw_too_many_asterisks' => '"$1" bevat teveel *\'s. ',
	'smw_denominator_is_1' => "De noemer mag niet 1 zijn.",
	'smw_no_si_unit_remains' => "Er is geen SI unit resterend na optimalisatie.",
	'smw_invalid_format_of_si_unit' => 'Ongeldig formaat van SI eenheid: $1 ',
	// Messages for the chemistry parsers
	'smw_not_a_chem_element' => '"$1" is geen chemisch element.',
	'smw_no_molecule' => 'Er is geen molecule in de chemische formule "$1".',
	'smw_chem_unmatched_brackets' => 'Het aantal openingshaakjes komt niet overeen met de sluitende haakjes in "$1".',
	'smw_chem_syntax_error' => 'De chemische formule "$1" is syntactisch niet juist.',
	'smw_no_chemical_equation' => '"$1" is geen chemische gelijkheid.',
	'smw_no_alternating_formula' => 'Er is een ontbrekende of onnodige operator in "$1".',
	'smw_too_many_elems_for_isotope' => 'Een isotoop kan slechts één element hebben. Daarom werd een molecule aangemaakt: "$1".',
	// Messages for attribute pages
	'smw_attribute_has_type' => 'Dit attribuut heeft het datatype ',
	// Messages for help
	'smw_help_askown' => 'Stel je eigen vraag',
	'smw_help_askownttip' => 'Voeg je eigen vragen toe aan de wiki help-pagina´s waar ze beantwoord kunnen worden door andere gebruikers',
	'smw_help_pageexists' => "Deze vraag is reeds in ons helpsysteem.\nKies 'more' om alle vragen te zien.",
	'smw_help_error' => "Oeps. Er blijkt een fout gebeurd te zijn.\nJe vraag kon niet toegevoegd worden aan het systeem. Sorry.",
	'smw_help_question_added' => "Je vraag werd toegevoegd aan ons helpsysteem\nen kan nu beantwoord worden door andere wiki gebruikers.",
    // Messages for CSH
    'smw_csh_icon_tooltip' => 'Click here if you need help or if you want to send feeback to the SMW+ developers.'
);


protected $smwUserMessages = array(
    'specialpages-group-smwplus_group' => 'Semantic Mediawiki+',
	'smw_devel_warning' => 'Dit feature wordt momenteel ontwikkeld, en is mogelijk niet volledig functioneel. Sla je gegevens op vooraleer je het gebruikt.',
	// Messages for pages of types, relations, and attributes

	'smw_relation_header' => 'Pagina´s met de eigenschap "$1"',
	'smw_subproperty_header' => 'Sub-eigenschappen van "$1"',
	'smw_subpropertyarticlecount' => '<p>Toont $1 sub-eigenschappen.</p>',

	// Messages for category pages
	'smw_category_schemainfo' => 'Schema informatie voor categorie "$1"',
	'smw_category_properties' => 'Eigenschappen',
	'smw_category_properties_range' => 'Eigenschappen met waardenbereik "$1"',
	
	'smw_category_askforallinstances' => 'Vraag alle entiteiten van "$1" en alle entiteiten von de sub-categorieën',
	'smw_category_queries' => 'Queries voor categorieën',
	
	'smw_category_nrna' => 'Pagina´s met verkeerd toegekend domein "$1".',
	'smw_category_nrna_expl' => 'Deze pagina´s hebben een domein maar zijn geen eigenschap.',
	'smw_category_nrna_range' => 'Pagina´s met verkeerd toegekende waardenbereik "$1".',
	'smw_category_nrna_range_expl' => 'Deze pagina´s hebben een waardenbereik maar zijn geen eigenschap.',


	'smw_exportrdf_all' => 'Alle semantische gegevens exporteren',

	// Messages for Search Triple Special
	'searchtriple' => 'Eenvoudige semantische zoekfunctie', //name of this special
	'smw_searchtriple_docu' => "<p>Vul de bovenste of onderste lijn van het invulformulier in om te zoeken naar respectievelijk relaties of attributen. 
	Sommige velden kunnen leeg gelaten worden of meerdere resultaten bevatten. Maar indien een attribuut-waarde gegeven is, moet de attribuut-naam ook ingevuld worden. Zoals gewoonlijk, kunnen attribuut-waarden ingevuld worden met een maateenheid. </p>\n\n<p>
Druk de rechter knop om resultaten te verkrijgen. Enkel <i>Return</i> drukken geeft eventueel niet de zoekfunctie weer die gewenst is.</p>",
	'smw_searchtriple_subject' => 'Onderwerp pagina:',
	'smw_searchtriple_relation' => 'Relatie-naam:',
	'smw_searchtriple_attribute' => 'Attribuut-naam:',
	'smw_searchtriple_object' => 'Object pagina:',
	'smw_searchtriple_attvalue' => 'Attribuut-waarde:',
	'smw_searchtriple_searchrel' => 'Zoek naar relaties',
	'smw_searchtriple_searchatt' => 'Zoek naar attributen',
	'smw_searchtriple_resultrel' => 'Zoekresultaten (relaties)',
	'smw_searchtriple_resultatt' => 'Zoekresultaten (attributen)',

	// Messages for Relations Special
	'relations' => 'Relaties',
	'smw_relations_docu' => 'De volgende relaties zijn aanwezig in de wiki.',
	// Messages for WantedRelations Special
	'wantedrelations' => 'Gewenste relaties',
	'smw_wanted_relations' => 'De volgende relaties hebben nog geen pagina met uitleg, alhoewel ze reeds gebruikt worden om andere pagina´s te beschrijven.',
	// Messages for Properties Special
	'properties' => 'Eigenschappen',
	'smw_properties_docu' => 'De volgende eigenschappen zijn aanwezig in de wiki.',
	'smw_attr_type_join' => ' met $1',
	'smw_properties_sortalpha' => 'Alfabetisch sorteren',
	'smw_properties_sortmoddate' => 'Sorteren op basis van aanpassingsdatum',
	'smw_properties_sorttyperange' => 'Sorteren op basis van type/waardenbereik',

	'smw_properties_sortdatatype' => 'Datatype eigenschappen',
	'smw_properties_sortwikipage' => 'Wikipagina eigenschappen',
	'smw_properties_sortnary' => 'N-tellige eigenschappen',
	// Messages for Unused Relations Special
	'unusedrelations' => 'Ongebruikte relaties',
	'smw_unusedrelations_docu' => 'De volgende relatie-pagina´s bestaan alhoewel geen enkele andere pagina er gebruik van maakt.',
	// Messages for Unused Attributes Special
	'unusedattributes' => 'Ongebruikte attributen',
	'smw_unusedattributes_docu' => 'De volgende attribuut-pagina´s bestaan alhoewel geen enkele andere pagina er gebruik van maakt.',


/*Messages for OntologyBrowser*/
	'ontologybrowser' => 'Ontologie-browser',
	'smw_ac_hint' => 'Druk Ctrl+Alt+Space voor auto-completion. (Ctrl+Space in IE)',
	'smw_ob_categoryTree' => 'Categorieënboom',
	'smw_ob_attributeTree' => 'Eigenschappenboom',

	'smw_ob_instanceList' => 'Entiteiten',
	
	'smw_ob_att' => 'Eigenschappen',
	'smw_ob_relattValues' => 'Waarden',
	'smw_ob_relattRangeType' => 'Type/waardenbereik',
	'smw_ob_filter' => 'Filter',
	'smw_ob_filterbrowsing' => 'Filteren',
	'smw_ob_reset' => 'Reset',
	'smw_ob_cardinality' => 'Kardinaliteit',
	'smw_ob_transsym' => 'Transitiviteit/symmetrie',
	'smw_ob_footer' => '',
	'smw_ob_no_categories' => 'Geen categorieën beschikbaar.',
	'smw_ob_no_instances' => 'Geen entiteiten beschikbaar.',
	'smw_ob_no_attributes' => 'Geen attributen beschikbaar.',
	'smw_ob_no_relations' => 'Geen relaties beschikbaar.',
	'smw_ob_no_annotations' => 'Geen aantekeningen beschikbaar.',
	'smw_ob_no_properties' => 'Geen eigenschappen beschikbaar.',
	'smw_ob_help' => 'De ontologie-browser maakt het mogelijk om doorheen de ontologie te navigeren en zo gemakkelijk items in de wiki te vinden en te identificeren. Gebruik het filter mechanisme	links bovenaan om naar specifieke elementen in de ontologie te zoeken. De filters beneden elke kolom laten toe om de zoekresultaten te beperken. Bij het begin gebeurt het browsen van links naar rechts. 	Deze richting kan veranderd worden door op de grote pijlen tussen de kolommen te klikken.',
	'smw_ob_undefined_type' => '*ongedefinieerd range*',
	'smw_ob_hideinstances' => 'Entiteiten verbergen',
    'smw_ob_onlyDirect' => 'no inferred',
	'smw_ob_showRange' => 'properties of range',
	'smw_ob_hasnumofsubcategories' => 'Aantal subcategorieën',
	'smw_ob_hasnumofinstances' => 'Aantal entiteiten',
	'smw_ob_hasnumofproperties' => 'Aantal eigenschappen',
	'smw_ob_hasnumofpropusages' => 'Deze eigenschap is $1 maal geannoteerd',
	'smw_ob_hasnumoftargets' => 'Deze entiteit is $1 maal gelinkt.',
	'smw_ob_hasnumoftempuages' => 'Deze template is $1 maal gebruikt',
	'smw_ob_invalidtitle' => '!!!invalid title!!!',

	/* Commands for ontology browser */
	'smw_ob_cmd_createsubcategory' => 'Subcategorie toevoegen',
	'smw_ob_cmd_createsubcategorysamelevel' => 'Categorie op hetzelfde niveau toevoegen',
	'smw_ob_cmd_renamecategory' => 'Hernoemen',
	'smw_ob_cmd_createsubproperty' => 'Subeigenschap toevoegen',
	'smw_ob_cmd_createsubpropertysamelevel' => 'Eigenschap op hetzelfde niveau toevoegen',
	'smw_ob_cmd_renameproperty' => 'Hernoemen',
	'smw_ob_cmd_renameinstance' => 'Entiteit hernoemen',
	'smw_ob_cmd_deleteinstance' => 'Entiteit verwijderen',
	'smw_ob_cmd_addpropertytodomain' => 'Eigenschappen toevoegen aan domein: ',

//TODO: Translate strings
	/* Advanced options in the ontology browser */
	'smw_ob_source_wiki' => "-Wiki-" ,
	'smw_ob_advanced_options' => "Advanced options" ,
	'smw_ob_select_datasource' => "Select the data source to browse:" ,
	'smw_ob_select_multiple' => "To select <b>multiple</b> data sources hold down <b>CTRL</b> and select the items with a <b>mouse click</b>.",
	'smw_ob_ts_not_connected' => "No triple store found. Please ask your wiki administrator!",

	/* Combined Search*/
	'smw_combined_search' => 'Gecombineerde zoekfunctie',
	'smw_cs_entities_found' => 'De volgende elementen werden in de wiki ontologie gevonden:',
	'smw_cs_attributevalues_found' => 'De volgende entiteiten bevatten eigenschapwaarden die overeenkomen met je zoekaanvraag:',
	'smw_cs_aksfor_allinstances_with_annotation' => 'Geef alle entiteiten van \'$1\' die een aantekening van \'$2\' hebben',
	'smw_cs_askfor_foundproperties_and_values' => 'Geef entiteit \'$1\' voor alle gevonden eigenschappen.',
	'smw_cs_ask'=> 'Tonen',
	'smw_cs_noresults' => 'Sorry, er zijn geen elementen in de ontologie die overeenkomen met de opgegeven zoekterm.',
	'smw_cs_searchforattributevalues' => 'Zoek eigenschapwaarden die overeenkomen met de opgegeven zoekaanvraag',
	'smw_cs_instances' => 'Artikels',
	'smw_cs_properties' => 'Eigenschappen',
	'smw_cs_values' => 'Waarden',
	'smw_cs_openpage' => 'Pagina openen',
	'smw_cs_openpage_in_ob' => 'In de ontologie-browser openen',
	'smw_cs_openpage_in_editmode' => 'Pagina aanpassen',
	'smw_cs_no_triples_found' => 'Geen triples gevonden!',
	'smw_autogen_mail' => 'Deze email werd automatisch gegenereerd. Gelieve niet te antwoorden op deze email!',

	

	/*Messages for ContextSensitiveHelp*/
	'contextsensitivehelp' => 'Kontext-gevoelige helpfunctie',
	'smw_contextsensitivehelp' => 'Kontext-gevoelige helpfunctie',
	'smw_csh_newquestion' => 'Dit is een nieuwe helpvraag. Klik om ze te beantwoorden!',
	'smw_csh_nohelp' => 'Er werden nog geen relevante helpvragen toegevoegd aan het systeem.',
	'smw_csh_refine_search_info' => 'Je kan je zoekfunctie verfijnen wat betreft het paginatype en/of de actie waarover je meer wil weten:',
	'smw_csh_page_type' => 'Pagina-type',
	'smw_csh_action' => 'Actie',
	'smw_csh_ns_main' => 'Main (algemene wiki pagina)',
	'smw_csh_all' => 'Alle',
	'smw_csh_search_special_help' => 'Je kan ook hulp zoeken voor de speciale features van deze wiki:',
	'smw_csh_show_special_help' => 'Zoek hulp voor:',
	'smw_csh_categories' => 'Categorieën',
	'smw_csh_properties' => 'Eigenschappen',
	/* Messages for the CSH discourse state. Do NOT edit or translate these
	 * otherwise CSH will NOT work correctly anymore
	 */
	'smw_csh_ds_ontologybrowser' => 'Ontologie-browser',
	'smw_csh_ds_queryinterface' => 'Query-interface',
	'smw_csh_ds_combinedsearch' => 'Zoek',

	/*Messages for Query Interface*/
	'queryinterface' => 'Query-interface',
	'smw_queryinterface' => 'Query-interface',
	'smw_qi_add_category' => 'Categorie toevoegen',
	'smw_qi_add_instance' => 'Entiteit toevoegen',
	'smw_qi_add_property' => 'Eigenschap toevoegen',
	'smw_qi_add' => 'Toevoegen',
	'smw_qi_confirm' => 'OK',
	'smw_qi_cancel' => 'Annuleren',
	'smw_qi_delete' => 'Verwijderen',
	'smw_qi_close' => 'Sluiten',
	'smw_qi_preview' => 'Resultaten vooraf bekijken',
    'smw_qi_fullpreview' => 'Show full result',
	'smw_qi_no_preview' => 'Er is nog geen preview beschikbaar',
	'smw_qi_clipboard' => 'Kopiëren op het clipboard',
	'smw_qi_reset' => 'Query terugzetten',
	'smw_qi_reset_confirm' => 'Ben je zeker dat je de query terug wil zetten?',
	'smw_qi_querytree_heading' => 'Queryboom navigatie',
    'smw_qi_update' => 'Update',
    'smw_qi_discard_changes' => 'Discard changes',
    'smw_qi_usetriplestore' => 'Use triple store',
	'smw_qi_main_query_name' => 'Main',
    'smw_qi_section_option' => 'Query options',
    'smw_qi_section_definition' => 'Query Definition',
    'smw_qi_section_result' => 'Result',
	'smw_qi_preview_result' => 'Result Preview',
	'smw_qi_layout_manager' => 'Format Query',
	'smw_qi_table_column_preview' => 'Tabelkolom preview',
	'smw_qi_article_title' => 'Artikel titel',
	'smw_qi_load' => 'Query laden',
	'smw_qi_save' => 'Query opslaan',
	'smw_qi_close_preview' => 'Preview sluiten',
	'smw_qi_querySaved' => 'Nieuwe query opgeslaan door de query-interface',
	'smw_qi_exportXLS' => 'Resultaten exporteren naar Excel',
	'smw_qi_showAsk' => 'Volledige query tonen',
	'smw_qi_ask' => '&lt;ask&gt; syntax',
	'smw_qi_parserask' => '{{#ask syntax',
    'smw_qi_queryastree' => 'Query as tree',
    'smw_qi_queryastext' => 'Query as text',
    'smw_qi_querysource' => 'Query source',
    'smw_qi_queryname' => 'Query name',
    'smw_qi_printout_err1' => 'The selected format for the query result needs at least one additional property than is shown in the result.',
    'smw_qi_printout_err2' => 'The selected format for the query result needs at least one property of the type date than is shown in the result.',
    'smw_qi_printout_err3' => 'The selected format for the query result needs at least one property of a numeric type than is shown in the result.',
    'smw_qi_printout_err4' => 'Your query did not return any results.',
    'smw_qi_printout_err4_lod' => 'Please check if you selected the correct data source.',
    'smw_qi_printout_notavailable' => 'The result of this query printer cannot be displayed in the query interface.',
    'smw_qi_datasource_select_header' => 'Select data source to query (hold CTRL to select multiple items)',
    'smw_qi_showdatarating' => 'Enable user ratings',
    'smw_qi_showmetadata' => 'Show meta information of data',
    'smw_qi_showdatasource' => 'Show data source information only',
    'smw_qi_maintab_query' => 'Create query',
    'smw_qi_maintab_load' => 'Load query',
    'smw_qi_load_criteria' => 'Search or Find (existing) queries with the following condition:',
    'smw_qi_load_selection_*' => 'query contains',
    'smw_qi_load_selection_i' => 'article name',
    'smw_qi_load_selection_q' => 'query name',
    'smw_qi_load_selection_p' => 'used property',
    'smw_qi_load_selection_c' => 'used category',
    'smw_qi_load_selection_r' => 'used query printer',
    'smw_qi_load_selection_s' => 'used printout',
    'smw_qi_button_search' => 'Search',
    'smw_qi_button_load' => 'Load selected query',
    'smw_qi_queryloaded_dlg' => 'Your query has been loaded into the Query Interface',
    'smw_qi_link_reset_search' => 'Reset search',
    'smw_qi_loader_result' => 'Result',
    'smw_qi_loader_qname' => 'Query-Name',
    'smw_qi_loader_qprinter' => 'Result Format',
    'smw_qi_loader_qpage' => 'Used in article',

	/*Tooltips for Query Interface*/
	'smw_qi_tt_addCategory' => 'Bij het toevoegen van een categorie, worden enkel artikels van deze categorie toegevoegd',
	'smw_qi_tt_addInstance' => 'Bij het toevoegen van een entiteit, wordt slechts één enkel artikel toegevoegd',
	'smw_qi_tt_addProperty' => 'Bij het toevoegen van een eigenschap, kan je ofwel alle resultaten tonen, ofwel specifieke waarden zoeken',
	'smw_qi_tt_tcp' => 'The tabelkolom preview geeft een preview van de kolommen die in de resulterende tabel voorkomen',
	'smw_qi_tt_prp' => 'De resultaten-preview geeft reeds een volledig beeld van de resultaten terwijl de query nog samengesteld wordt',
	'smw_qi_tt_qlm' => 'The querylayout manager laat toe om de layout van de queryresultaten te definiëren',
    'smw_qi_tt_qdef' => 'Define your query',
    'smw_qi_tt_previewres' => 'Preview query results and format query',
    'smw_qi_tt_update' => 'Update the result preview of the query',
	'smw_qi_tt_preview' => 'Geeft een preview van de queryresultaten, inclusief de layout settings',
    'smw_qi_tt_fullpreview' => 'Geeft een volledige preview van de queryresultaten, inclusief de layout settings',
	'smw_qi_tt_clipboard' => 'Kopieert je query tekst op het clipboard zodat het gemakkelijk in een artikel toegevoegd kan worden',
	'smw_qi_tt_showAsk' => 'Dit geeft de volledige ask query uit',
	'smw_qi_tt_reset' => 'Zet de volledige query terug',
	'smw_qi_tt_format' => 'Output formaat van je query',
	'smw_qi_tt_link' => 'Definieert welke delen van de resultatentabel zullen verschijnen als links',
	'smw_qi_tt_intro' => 'Tekst die vooraan aan de queryresultaten toegevoegd wordt',
    'smw_qi_tt_outro' => 'Tekst die achteraan aan de queryresultaten toegevoegd wordt',
	'smw_qi_tt_sort' => 'Kolom die zal gebruikt worden om te sorteren',
	'smw_qi_tt_limit' => 'Maximum aantal getoonde resultaten',
	'smw_qi_tt_mainlabel' => 'Naam van de eerste kolom',
	'smw_qi_tt_order' => 'Stijgende of dalende volgorde',
	'smw_qi_tt_headers' => 'De kop van de tabel tonen of niet',
	'smw_qi_tt_default' => 'Tekst die getoond wordt indien er geen resultaten zijn',
    'smw_qi_tt_treeview' => 'Display your query in a tree',
    'smw_qi_tt_textview' => 'Describe the query in stylized English',
    'smw_qi_tt_option' => 'Define general settings for how the query is executed',
    'smw_qi_tt_maintab_query' => 'Create a new query',
    'smw_qi_tt_maintab_load' => 'Load an existing query',
	
	/* Annotation */
 	'smw_annotation_tab' => 'aantekeningen maken',
	'smw_annotating'     => '$1 aantekenen',
	'annotatethispage'   => 'Deze pagina annoteren',

 	
 	/* Refactor preview */
 	'refactorstatistics' => 'Refactor statistieken',
 	'smw_ob_link_stats' => 'Refactor statistieken openen',
 	
 	
 	
 	
 	/* Gardening Issue Highlighting in Inline Queries */
	'smw_iqgi_missing' => 'ontbrekend',
	'smw_iqgi_wrongunit' => 'verkeerde eenheid',
	
	/* ACL */
	'acl' => 'Access Control Lists',
	'acl_welcome' => 'This special page allows to configure the Access Control Lists. They are used to restrict access to the wiki.',
	'smw_acl_rules' => 'Rules',
	'smw_acl_up' => 'up',
	'smw_acl_down' => 'down',
	'smw_acl_groups' => 'Groups',
	'smw_acl_namespaces' => 'Namespaces',
	'smw_acl_actions' => 'Actions',
	'smw_acl_permission' => 'Permission',
	'smw_acl_whitelist' => 'Whitelist (comma separated with namespace)',
	'smw_acl_superusers' => 'Superusers (comma separated)',
	'smw_acl_update' => 'Update rules',
	'smw_acl_remove' => 'Remove rule',
	'smw_acl_newrule' => 'New rule',
	'smw_acl_addrule' => 'Add rule',

	'smw_acl_*' => '*',
	'smw_acl_read' => 'read',
	'smw_acl_edit' => 'edit',
	'smw_acl_create' => 'create',
	'smw_acl_move' => 'move',
	'smw_acl_permit' => 'permit',
	'smw_acl_deny' => 'deny',
	
	// Simple Rules formula parser
	'smw_srf_expected_factor' => 'Expected a function, variable, constant or braces near $1',
	'smw_srf_expected_comma' => 'Expected a comma near $1',
	'smw_srf_expected_(' => 'Expected an opening brace near $1',
	'smw_srf_expected_)' => 'Expected a closing brace near $1',
	'smw_srf_expected_parameter' => 'Expected a parameter near $1',
	'smw_srf_missing_operator' => 'Expected an operator near $1',

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
    'smw_tsa_addtoconfig2' => 'Make sure that the triplestore driver is activated. If necessary change enableSMWHalo to',
    'smw_tsa_addtoconfig3' => 'Also make sure that the graph URL (last parameter of enableSMWHalo) is a valid one and it does not contain a hash (#).',
	'smw_tsa_addtoconfig4' => 'If this does not help, please check out the online-help in the $1.',
    'smw_tsa_driverinfo' => 'Driver information',
    'smw_tsa_status' => 'Status',
    'smw_tsa_rulesupport'=> 'The triplestore driver supports rules, so you should add <pre>$smwgEnableObjectLogicRules=true;</pre> to your LocalSettings.php. Otherwise rules will not work.',
    'smw_tsa_norulesupport'=> 'The triplestore driver does not supports rules, although they are activated in the wiki. Please remove <pre>$smwgEnableObjectLogicRules=true;</pre> from your LocalSettings.php. Otherwise you may get obscure errors.',
    'smw_tsa_tscinfo' => 'Triplestore Connector information',
    'smw_tsa_tscversion' => 'TSC Version',
    'smw_ts_notconnected' => 'TSC not accessible. Check server: $1',
'smw_tsc_query_not_allowed' => 'Empty query not allowed when querying TSC.',

	// Derived facts
	'smw_df_derived_facts_about' => 'Afgeleide feiten $1',
    'smw_df_tsc_advertisment'    => "''You have no triplestore attached to this Wiki.''\n\nYou make this Wiki smarter by connecting a TripleStore to it! Connecting the ontoprise products '''TripleStoreConnector Basic''' (free) or '''TripleStoreConnector Professional''' ultimately leads to getting better search results and to making use of data which lives outside this Wiki.\nClick here to read what your benefits are and to download a [http://smwforum.ontoprise.com/smwforum/index.php/List_of_Extensions/Triple_store_connector TripleStore]!",
        //skin
        'smw_search_this_wiki' => 'Search this wiki',
        'more_functions' => 'more',
        'smw_treeviewleft' => 'Open treeview to the left side',
        'smw_treeviewright' => 'Open treeview to the right side'



);


protected $smwSpecialProperties = array(
	//always start upper-case
	"___cfsi" => array('_siu', 'Komt overeen met SI'),
	"___CREA" => array('_wpg', 'Creator'),
	"___CREADT" => array('_dat', 'Creation date'),
	"___MOD" => array('_wpg', 'Last modified by')
);


var $smwSpecialSchemaProperties = array (
	SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT => 'Heeft domein en waardenbereik',
	SMW_SSP_HAS_MAX_CARD => 'Heeft max kardinaliteit',
	SMW_SSP_HAS_MIN_CARD => 'Heeft min kardinaliteit',
	SMW_SSP_IS_INVERSE_OF => 'Is het omgekeerde van',
	SMW_SSP_IS_EQUAL_TO => 'Is gelijk aan'
);

var $smwSpecialCategories = array (
	SMW_SC_TRANSITIVE_RELATIONS => 'Transitieve eigenschappen',
	SMW_SC_SYMMETRICAL_RELATIONS => 'Symmetrische eigenschappen'
);

var $smwHaloDatatypes = array(
	'smw_hdt_chemical_formula' => 'Chemische formule',
	'smw_hdt_chemical_equation' => 'Chemische gelijkheid',
	'smw_hdt_mathematical_equation' => 'Wiskundige gelijkheid',
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
			SMW_NS_RELATION       => 'Relatie',
			SMW_NS_RELATION_TALK  => 'Relatie_talk',
			SMW_NS_PROPERTY       => 'Eigenschap',
			SMW_NS_PROPERTY_TALK  => 'Eigenschap_talk',
			SMW_NS_TYPE           => 'Type',
			SMW_NS_TYPE_TALK      => 'Type_talk'
		);
	}


}


