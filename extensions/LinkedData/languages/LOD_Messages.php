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
    'lod_mapping_tag_ns'	 => 'The tag <mapping> is only evaluated in the namespace "Mapping".',
    'lod_no_mapping_in_ns'   => 'Articles in the namespace "Mapping" are supposed to contain mappings for linked data sources. You can add mapping descriptions in the tag &lt;mapping&gt;.',
	'lod_saving_mapping_failed' => '<b>The following mapping could not be saved:</b>',
	'lod_nep_link'			=> 'Create the article <b>$1</b> with the content displayed below.',
	'lod_mdp_no_printer'	=> 'The requested meta-data printer for format <i>$1</i> was not found.',
	'lod_mdp_no_metadata'	=> 'There is no neta-data for this value.',
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
);

/** 
 * German
 */
$messages['de'] = array(
    'lod_mapping_tag_ns'	 => 'Das Tag <zuordnung> wird nur im Namensraum "Mapping" ausgewertet.',
    'lod_no_mapping_in_ns'   => 'Artikel im Namensraum "Mapping" sollten Mappings für Linked Data Quellen beinhalten. Sie können Mapping-Beschreibungen im Tag &lt;zuordnung&gt; einfügen.',
	'lod_saving_mapping_failed' => '<b>Die folgende Zuordnung konnte nicht gespeichert werden:</b>',
	'lod_nep_link'			=> 'Den Artikel <b>$1</b> mit dem unten dargestellten Inhalt erzeugen.',
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
							);
