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
 * Internationalization file for LinkedData
 *
 */

$messages = array();

/** 
 * English
 */
$messages['en'] = array(
    //--- Mapping ---
	'lod_mapping_tag_ns'	 => 'The tags <r2rMapping> and <silkMapping> are only evaluated in the namespace "Mapping".',
    'lod_no_mapping_in_ns'   => 'Articles in the namespace "Mapping" are supposed to contain mappings for linked data sources. You can add mapping descriptions in the tags &lt;r2rMapping&gt; or &lt;silkMapping&gt;.',
	'lod_saving_mapping_failed' => '<b>The following mapping could not be saved:</b>',
	'lod_mapping_invalid_mintNS' => '<b>The given mintNamespace was not understood and the default Wiki namespace will be taken.</b>',
	'lod_mapping_invalid_mintLP' => "<b>The mintLabelPredicate '$1' is not a valid URI or uses an unknown Namespace prefix and will be ignored.<b>",
	
	
	//--- Meta-data printer ---
	'lod_mdp_no_printer'	=> 'The requested meta-data printer for format <i>$1</i> was not found.',
	'lod_mdp_no_metadata'	=> 'There is no meta-data for this value.',
	'lod_mdp_property'		=> 'Property',
	'lod_mdp_value'			=> 'Value',
	'lod_mdp_table_title'	=> 'Meta-data for this data value',
	'lod_mdp_xsl_missing_meta_data_stylesheet'
							=> 'Please specify a stylesheet in the query with the parameter <tt>metadatastylesheet</tt>!',
	'lod_mdp_xsl_missing_article' 
							=> 'The article "$1" that was specified in the query parameter <tt>metadatastylesheet</tt> does not exist!',
	'lod_mdp_xsl_missing_pre' 
							=> 'The XSL in the article "$1" must be embedded in <pre>-tags!',

	//--- Labels for meta-data properties ---							
	'lod_mdpt_swp2_authority'					=> 'Data source',
	'lod_mdpt_swp2_authority_id'				=> 'Data source',
	'lod_mdpt_swp2_keyinfo'						=> 'swp2_keyinfo',
	'lod_mdpt_swp2_signature'					=> 'swp2_signature',
	'lod_mdpt_swp2_signature_method'			=> 'swp2_signature_method',
	'lod_mdpt_swp2_valid_from'					=> 'swp2_valid_from',
	'lod_mdpt_swp2_valid_until'					=> 'swp2_valid_until',
	'lod_mdpt_data_dump_location_from'			=> 'Location of the RDF data dump',
	'lod_mdpt_homepage_from'					=> 'Homepage of the data source',
	'lod_mdpt_sample_uri_from'					=> 'Representative sample URI for crawling',
	'lod_mdpt_sparql_endpoint_location_from'	=> 'SPARQL protocol endpoint',
	'lod_mdpt_datasource_vocabulary_from'		=> 'Vocabulary used in the dataset',
	'lod_mdpt_datasource_id_from'				=> 'Short name',
	'lod_mdpt_datasource_changefreq_from'		=> 'Change frequency',
	'lod_mdpt_datasource_description_from'		=> 'Description',
	'lod_mdpt_datasource_label_from'			=> 'Name',
	'lod_mdpt_datasource_lastmod_from'			=> 'Last modification',
	'lod_mdpt_datasource_linkeddata_prefix_from'=> 'URI prefix',
	'lod_mdpt_datasource_uriregexpattern_from'	=> 'URI regex',
	'lod_mdpt_data_dump_location_to'			=> 'data_dump_location_to',
	'lod_mdpt_homepage_to'						=> 'homepage_to',
	'lod_mdpt_sample_uri_to'					=> 'sample_uri_to',
	'lod_mdpt_sparql_endpoint_location_to'		=> 'sparql_endpoint_location_to',
	'lod_mdpt_datasource_vocabulary_to'			=> 'datasource_vocabulary_to',
	'lod_mdpt_datasource_id_to'					=> 'datasource_id_to',
	'lod_mdpt_datasource_changefreq_to'			=> 'datasource_changefreq_to',
	'lod_mdpt_datasource_description_to'		=> 'datasource_description_to',
	'lod_mdpt_datasource_label_to'				=> 'datasource_label_to',
	'lod_mdpt_datasource_lastmod_to'			=> 'datasource_lastmod_to',
	'lod_mdpt_datasource_linkeddata_prefix_to'	=> 'datasource_linkeddata_prefix_to',
	'lod_mdpt_datasource_uriregexpattern_to'	=> 'datasource_uriregexpattern_to',
	'lod_mdpt_import_graph_created'				=> 'Import date',
	'lod_mdpt_import_graph_revision_no'			=> 'import_graph_revision_no',
	'lod_mdpt_import_graph_last_changed_by'		=> 'import_graph_last_changed_by',
	'lod_mdpt_rating_value'						=> 'rating_value',
	'lod_mdpt_rating_user'						=> 'rating_user',
	'lod_mdpt_rating_created'					=> 'rating_created',
	'lod_mdpt_rating_assessment'				=> 'rating_assessment',
							
	//--- LOD source definition---
	'lod_lsdparser_expected_at_least'			=> 'The parameter "$2" must occur at least $1 time(s).',
	'lod_lsdparser_expected_exactly'			=> 'The parameter "$2" must occur exactly $1 time(s).',
	'lod_lsdparser_expected_between'			=> 'The parameter "$3" must occur between $1 and $2 time(s).',
	'lod_lsdparser_title'						=> 'Data source definition for',
	'lod_lsdparser_success'						=> 'The Linked Data source definition was parsed successfully. The following values will be stored:',							
	'lod_lsdparser_failed'						=> 'The definition of the data source is faulty. It will not be saved.',			
	'lod_lsdparser_error_intro'					=> 'The following errors were found in the definition of the data source:',				
							
	'lod_lsd_id'						=> "ID",
	'lod_lsd_changefreq'				=> "Change frequency",
	'lod_lsd_datadumplocation'			=> "Data dump location",
	'lod_lsd_description'				=> "Description",
	'lod_lsd_homepage'					=> "Homepage",
	'lod_lsd_label'						=> "Label",
	'lod_lsd_lastmod'					=> "Last modification",
	'lod_lsd_linkeddataprefix'			=> "Linked data prefix",
	'lod_lsd_sampleuri'					=> "Sample URI",
	'lod_lsd_sparqlendpointlocation'	=> "SPARQL endpoint location",
	'lod_lsd_sparqlgraphname'			=> "SPARQL graph name",
	'lod_lsd_sparqlgraphpattern'		=> "SPARQL graph pattern",
	'lod_lsd_uriregexpattern'			=> "URI regex pattern",
	'lod_lsd_vocabulary'				=> "Vocabulary",
	'lod_lsd_predicatetocrawl'			=> "Predicate to crawl",

	//--- LOD special pages ---
	'lodsources'	=> 'LOD sources',
	'specialpages-group-lod_group' => 'Linked Data extension',
    //--- LOD Source editor
	'lod_sp_source_label' => 'Name',
	'lod_sp_source_source' => 'Data source',
	'lod_sp_source_lastimport' => 'Last import',
	'lod_sp_source_changefreq' => 'Change frequency',
	'lod_sp_source_import' => 'Import',
	'lod_sp_source_reimport' => '(Re-)Import',
	'lod_sp_source_update' => 'Update',
	'lod_sp_isimported' => 'Imported',
	'lod_sp_statusmsg' => 'Status message',
    //--- LOD Policy editor
	'lodtrust'	=> 'LOD Trust Policies',
    'lod_sp_policy_label' => 'ID',
	'lod_sp_policy_description' => 'Policy',
    'lod_sp_policy_action_edit' => 'Edit',
    'lod_sp_policy_action_duplicate' => 'Duplicate',
    'lod_sp_policy_action_remove' => 'Remove',
    'lod_sp_policy_action_new' => 'New policy',
    'lod_sp_policy_action_edit_par' => 'Edit parameter',
    'lod_sp_policy_action_remove_par' => 'Remove parameter',
    'lod_sp_policy_action_define_par' => 'Define parameter',
    'lod_sp_policy_action_new_par' => 'New parameter',
    'lod_sp_policy_action_cancel_edit' => 'Cancel editing',
    
    //--- LOD LinkSpecs editor
    'lodlinkspecs'	=> 'LOD Link Specification',

    //--- LOD Mapping editor
    'lodmappings'	=> 'LOD R2R Mappings',

	//--- LOD rating ---
	'lod_rt_heading'			=> 'Selected value:',
	'lod_rt_flagged_correct'	=> 'In your opinion the flagged fact is <b>correct</b>.',
	'lod_rt_flagged_wrong'		=> 'In your opinion the flagged fact is <b>wrong</b>.',
	'lod_rt_enter_comment'		=> 'Enter your comment:',
	'lod_rt_bt_save'			=> 'Save',
	'lod_rt_bt_cancel'			=> 'Cancel',
	'lod_rt_click_flag'			=> 'Click a flag to rate a fact',
	'lod_rt_pathways'			=> 'Reasons to get this value:',
	'lod_rt_rating_statistics'	=> 'Rating statistics: ',
	'lod_rt_user_comments'		=> 'Other comments:',
	'lod_rt_rate_correct'		=> 'Click, to mark this fact as correct.',
	'lod_rt_rate_wrong'			=> 'Click, to mark this fact as wrong.',
	'lod_rt_rated_correct'		=> 'You marked this fact as being correct.',
	'lod_rt_rated_wrong'		=> 'You marked this fact as being wrong.',
	'lod_rt_value_may_differ'	=> 'The representation of the actual value may differ from the presented result.',
	'lod_rt_rate_related'		=> 'Show more facts',
	'lod_rt_hide_related'		=> 'Hide more facts',	
	'lod_rt_show_comments'		=> 'Show comments',
	'lod_rt_hide_comments'		=> 'Hide comments',
	'lod_rt_click_for_comments' => 'Click to view comments.',
	'lod_rt_rating_saved'		=> 'Your rating was successfully saved. Thanks!'
														
);

/** 
 * German
 */
$messages['de'] = array(
    //--- Mapping ---
	'lod_mapping_tag_ns'	 => 'Die Tags <r2rZuordnung> und <silkZuordnung> werden nur im Namensraum "Mapping" ausgewertet.',
    'lod_no_mapping_in_ns'   => 'Artikel im Namensraum "Zuordnung" sollten Mappings für Linked Data Quellen beinhalten. Sie können Mapping-Beschreibungen in den Tags &lt;r2rZuordnung&gt; oder &lt;silkZuordnung&gt;einfügen.',
	'lod_saving_mapping_failed' => '<b>Die folgende Zuordnung konnte nicht gespeichert werden:</b>',
	'lod_mapping_invalid_mintNS' => '<b>Der angegebene mintNamespace wurde nicht verstanden und wird ignoriert. Stattdessen wird der Standard Wiki Namespace verwendet.</b>',
	'lod_mapping_invalid_mintLP' => "<b>Das mintLabelPredicate '$1' iist keine valide URI oder benutzt einen unbekannten Namespace Prefix und wird ignoriert.<b>",

	
	//--- Meta-data printer ---
	'lod_mdp_no_printer'	=> 'Die gewünschte Metadatenausgabe <i>$1</i> wurde nicht gefunden.',
	'lod_mdp_no_metadata'	=> 'Zu diesem Wert gibt es keine Metadaten.',
	'lod_mdp_property'		=> 'Eigenschaft',
	'lod_mdp_value'			=> 'Wert',
	'lod_mdp_table_title'	=> 'Metadaten zu diesem Wert',
	'lod_mdp_xsl_missing_meta_data_stylesheet'
							=> 'Bitte geben Sie in der Query ein Stylesheet im Parameter <tt>metadatastylesheet</tt> an!',
	'lod_mdp_xsl_missing_article' 
							=> 'Der Artikel "$1", der im Queryparameter <tt>metadatastylesheet</tt> angegeben wurde existiert nicht!',
	'lod_mdp_xsl_missing_pre' 
							=> 'Das XSL im Artikel "$1" muss in <pre>-Tags eingebettet werden!',
							
	//--- Labels for meta-data properties ---							
	'lod_mdpt_swp2_authority'					=> 'Datenquelle',
	'lod_mdpt_swp2_authority_id'				=> 'Datenquelle',
	'lod_mdpt_swp2_keyinfo'						=> 'swp2_keyinfo',
	'lod_mdpt_swp2_signature'					=> 'swp2_signature',
	'lod_mdpt_swp2_signature_method'			=> 'swp2_signature_method',
	'lod_mdpt_swp2_valid_from'					=> 'swp2_valid_from',
	'lod_mdpt_swp2_valid_until'					=> 'swp2_valid_until',
	'lod_mdpt_data_dump_location_from'			=> 'Ort des RDF Datendumps',
	'lod_mdpt_homepage_from'					=> 'Homepage der Datenquelle',
	'lod_mdpt_sample_uri_from'					=> 'Repräsentative Beispiel-URI',
	'lod_mdpt_sparql_endpoint_location_from'	=> 'SPARQL-Protokoll Endpunkt',
	'lod_mdpt_datasource_vocabulary_from'		=> 'Benutztes Vokabular',
	'lod_mdpt_datasource_id_from'				=> 'Kurzname',
	'lod_mdpt_datasource_changefreq_from'		=> 'Änderungsfrequenz',
	'lod_mdpt_datasource_description_from'		=> 'Beschreibung',
	'lod_mdpt_datasource_label_from'			=> 'Name',
	'lod_mdpt_datasource_lastmod_from'			=> 'Letzte Änderung',
	'lod_mdpt_datasource_linkeddata_prefix_from'=> 'URI-Präfix',
	'lod_mdpt_datasource_uriregexpattern_from'	=> 'URI regulärer Ausdruck',
	'lod_mdpt_data_dump_location_to'			=> 'data_dump_location_to',
	'lod_mdpt_homepage_to'						=> 'homepage_to',
	'lod_mdpt_sample_uri_to'					=> 'sample_uri_to',
	'lod_mdpt_sparql_endpoint_location_to'		=> 'sparql_endpoint_location_to',
	'lod_mdpt_datasource_vocabulary_to'			=> 'datasource_vocabulary_to',
	'lod_mdpt_datasource_id_to'					=> 'datasource_id_to',
	'lod_mdpt_datasource_changefreq_to'			=> 'datasource_changefreq_to',
	'lod_mdpt_datasource_description_to'		=> 'datasource_description_to',
	'lod_mdpt_datasource_label_to'				=> 'datasource_label_to',
	'lod_mdpt_datasource_lastmod_to'			=> 'datasource_lastmod_to',
	'lod_mdpt_datasource_linkeddata_prefix_to'	=> 'datasource_linkeddata_prefix_to',
	'lod_mdpt_datasource_uriregexpattern_to'	=> 'datasource_uriregexpattern_to',
	'lod_mdpt_import_graph_created'				=> 'Importdatum',
	'lod_mdpt_import_graph_revision_no'			=> 'import_graph_revision_no',
	'lod_mdpt_import_graph_last_changed_by'		=> 'import_graph_last_changed_by',
	'lod_mdpt_rating_value'						=> 'rating_value',
	'lod_mdpt_rating_user'						=> 'rating_user',
	'lod_mdpt_rating_created'					=> 'rating_created',
	'lod_mdpt_rating_assessment'				=> 'rating_assessment',
							
	//--- LOD source definition---
	'lod_lsdparser_expected_at_least'			=> 'Der Parameter "$2" muss mindestens $1 mal vorkommen.',
	'lod_lsdparser_expected_exactly'			=> 'Der Parameter "$2" muss genau $1 mal vorkommen.',
	'lod_lsdparser_expected_between'			=> 'Der Parameter "$3" muss zwischen $1 und $2 mal vorkommen.',
	'lod_lsdparser_title'						=> 'Datenquellendefinition für',
	'lod_lsdparser_success'						=> 'Die Linked-Data-Quelldefinition wurde erfolgreich gelesen. Die folgenden Werte werden gespeichert:',
	'lod_lsdparser_failed'						=> 'Die Definition der Datenquelle ist fehlerhaft. Sie wird nicht gespeichert.',			
	'lod_lsdparser_error_intro'					=> 'Die folgenden Fehler wurden in der Definition der Datenquelle gefunden:',				

	'lod_lsd_id'						=> "ID",
	'lod_lsd_changefreq'				=> "Änderungsrate",
	'lod_lsd_datadumplocation'			=> "Adresse für Datendumps",
	'lod_lsd_description'				=> "Beschreibung",
	'lod_lsd_homepage'					=> "Homepage",
	'lod_lsd_label'						=> "Bezeichnung",
	'lod_lsd_lastmod'					=> "Letzte Änderung",
	'lod_lsd_linkeddataprefix'			=> "Linked data Präfix",
	'lod_lsd_sampleuri'					=> "Beispiel-URI",
	'lod_lsd_sparqlendpointlocation'	=> "SPARQL-Endpunktadresse",
	'lod_lsd_sparqlgraphname'			=> "SPARQL-Graphname",
	'lod_lsd_sparqlgraphpattern'		=> "SPARQL-Graphpattern",
	'lod_lsd_uriregexpattern'			=> "URI Regex-Muster",
	'lod_lsd_vocabulary'				=> "Vokabular",
	'lod_lsd_predicatetocrawl'			=> "Beim Crawling zu folgendes Prädikat",

	//--- LOD special pages ---
    'lodsources'    => 'LOD-Quellen',
	'specialpages-group-lod_group' => 'Linked Data extension',
	'lod_sp_source_label' => 'Name',
    'lod_sp_source_source' => 'Datenquelle',
    'lod_sp_source_lastimport' => 'Letzter Import',
    'lod_sp_source_changefreq' => 'Änderungsfrequenz',
	'lod_sp_source_import' => 'Importiere',
    'lod_sp_source_reimport' => '(Re-)Importiere',
    'lod_sp_source_update' => 'Aktualisiere',
	'lod_sp_isimported' => 'Ist importiert',
	'lod_sp_statusmsg' => 'Statusnachricht',
    //--- LOD Policy editor
	'lodtrust'	=> 'LOD Trust Policies',
    'lod_sp_policy_label' => 'ID',
	'lod_sp_policy_description' => 'Policy',
    'lod_sp_policy_action_edit' => 'Bearbeiten',
    'lod_sp_policy_action_duplicate' => 'Duplizieren',
    'lod_sp_policy_action_remove' => 'Löschen',
    'lod_sp_policy_action_new' => 'Neu',
    'lod_sp_policy_action_edit_par' => 'Parameter bearbeiten',
    'lod_sp_policy_action_remove_par' => 'Parameter löschen',
    'lod_sp_policy_action_define_par' => 'Parameter definieren',
    'lod_sp_policy_action_new_par' => 'Neuer Parameter',
    'lod_sp_policy_action_cancel_edit' => 'Abbrechen',
								
	//--- LOD rating ---
	'lod_rt_heading'			=> 'Ausgewählter Wert:',
	'lod_rt_flagged_correct'	=> 'Sie meinen das geflaggte Faktum sei <b>korrekt</b>.',
	'lod_rt_flagged_wrong'		=> 'Sie meinen das geflaggte Faktum sei <b>falsch</b>.',
	'lod_rt_enter_comment'		=> 'Geben Sie Ihren Kommentar ein:',
	'lod_rt_bt_save'			=> 'Speichern',
	'lod_rt_bt_cancel'			=> 'Abbrechen',
	'lod_rt_click_flag'			=> 'Klicken Sie auf ein Fähnchen um das Faktum zu bewerten.',
	'lod_rt_pathways'			=> 'Gründe für diesen Wert:',
	'lod_rt_rating_statistics'	=> 'Bewertungsstatistik: ',
	'lod_rt_user_comments'		=> 'Andere Kommentare:',
	'lod_rt_rate_correct'		=> 'Klicken, um dieses Faktum als korrekt zu bewerten.',
	'lod_rt_rate_wrong'			=> 'Klicken, um dieses Faktum als falsch zu bewerten.',
	'lod_rt_rated_correct'		=> 'Sie haben das Faktum als korrekt bewertet.',
	'lod_rt_rated_wrong'		=> 'Sie haben das Faktum als falsch bewertet.',
	'lod_rt_value_may_differ'	=> 'Die Repräsentation des tatsächlichen Wertes kann vom gezeigten Wert abweichen.',
	'lod_rt_rate_related'		=> 'Weitere Fakten anzeigen',
	'lod_rt_hide_related'		=> 'Weitere Fakten ausblenden',	
	'lod_rt_show_comments'		=> 'Kommentare anzeigen',
	'lod_rt_hide_comments'		=> 'Kommentare ausblenden',
	'lod_rt_click_for_comments' => 'Klicken, um Kommentare anzuzeigen.',
	'lod_rt_rating_saved'		=> 'Ihre Bewertung wurde erfolgreich gespeichert. Vielen Dank!'
);
