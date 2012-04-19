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

/**
 * @file
*  @ingroup SMWHaloLanguage
 * @author Thomas Schweitzer
 */

global $smwgHaloIP;
include_once($smwgHaloIP . '/languages/SMW_HaloLanguage.php');

class SMW_HaloLanguageEn extends SMW_HaloLanguage {

	protected $smwContentMessages = array(
	
	// Messages for AC (auto-completion) 
	'smw_ac_datetime_proposal'=>'<Month> <day>, <year>|<year>-<month>-<day>',
    'smw_ac_geocoord_proposal'=>'<latitude>° N, <longitude>° W|<latitude>, <longitude>',
	'smw_ac_email_proposal'=>'somebody@somewhere.com',
	'smw_ac_temperature_proposal'=>'<number> K, <number> °C, <number> °F, <number> °R',
	'smw_ac_telephone_proposal'=>'tel:+1-201-555-0123',
	'smw_ac_category_has_icon' => 'Category has icon'

	
	);


	protected $smwUserMessages = array(
	
	// Messages for AC (auto-completion)
	'tog-autotriggering' => 'Manual auto-completion',
    'smw_ac_typehint'=> 'Type: $1',
    'smw_ac_typerangehint'=> 'Type: $1 | Range: $2',
	
	// Messages for STB
	'smw_help_askown' => 'Ask your own question',
	'smw_help_askownttip' => 'Add your own question to the wiki helppages where it can be answered by other users',
	'smw_help_pageexists' => "This question is already in our helpsystem.\nClick 'more' to see all questions.",
	'smw_help_error' => "Oops. An error seems to have occured.\nYour question could not be added to the system. Sorry.",
	'smw_help_question_added' => "Your question has been added to our help system\nand can now be answered by other wiki users.",
    'smw_viewinOB' => 'Open in Data Explorer',
    'smw_wysiwyg' => 'WYSIWYG',

	// General messages
	'specialpages-group-smwplus_group' => 'SMW+',
	'smw_devel_warning' => 'This feature is currently under development, and might not be fully functional. Backup your data before using it.',
    'smw_sparql_disabled'=> 'No SPARQL support enabled.',
	
	// Messages for pages of types, relations, and attributes
	'smw_relation_header' => 'Pages using the property "$1"',
	'smw_subproperty_header' => 'Sub-properties of "$1"',
	'smw_subpropertyarticlecount' => '<p>Showing $1 sub-properties.</p>',

	// Messages for category pages
	'smw_category_schemainfo' => 'Schema information for category "$1"',
	'smw_category_properties' => 'Properties',
	'smw_category_properties_range' => 'Properties whose range is "$1"',

	'smw_category_askforallinstances' => 'Ask for all instances of "$1" and for all instances of its subcategories',
	'smw_category_queries' => 'Queries for categories',

	'smw_category_nrna' => 'Pages with wrongly assigned domain "$1".',
	'smw_category_nrna_expl' => 'These page have a domain hint but they are not a property.',
	'smw_category_nrna_range' => 'Pages with wrongly assigned range "$1".',
	'smw_category_nrna_range_expl' => 'These page have a range hint but they are not a property.',
	
	// Messages for Special:Properties 
	'properties' => 'Properties',
	'smw_properties_docu' => 'The following properties exist in the wiki.',
	'smw_attr_type_join' => ' with $1',
	'smw_properties_sortalpha' => 'Sort alphabetically',
	'smw_properties_sortmoddate' => 'Sort by modification date',
	'smw_properties_sorttyperange' => 'Sort by type/range',

	'smw_properties_sortdatatype' => 'Datatype properties',
	'smw_properties_sortwikipage' => 'Wikipage properties',
	'smw_properties_sortnary' => 'Record properties',
	

	// Messages for Special:DataExplorer
	'dataexplorer' => 'DataExplorer',
	'smw_ac_hint' => 'Press Ctrl+Alt+Space to use auto-completion. (Ctrl+Space in IE)',
	'smw_ob_categoryTree' => 'Category Tree',
	'smw_ob_attributeTree' => 'Property Tree',

	'smw_ob_instanceList' => 'Instances',
       
	'smw_ob_att' => 'Properties',
	'smw_ob_relattValues' => 'Values',
	'smw_ob_relattRangeType' => 'Type/Range',
	'smw_ob_filter' => 'Filter',
	'smw_ob_filterbrowsing' => 'Find',
	'smw_ob_reset' => 'Reset',
	'smw_ob_cardinality' => 'Cardinality',
	'smw_ob_transsym' => 'Transitivity/Symetry',
	'smw_ob_footer' => '',
	'smw_ob_no_categories' => 'No categories available.',
	'smw_ob_no_instances' => 'No instances available.',
	'smw_ob_no_attributes' => 'No attributes available.',
	'smw_ob_no_relations' => 'No relations available.',
	'smw_ob_no_annotations' => 'No annotations available.',
	'smw_ob_no_properties' => 'No properties available.',
	'smw_ob_help' => 'The Data Explorer lets you navigate through the ontology to easily find
and identify items in the wiki. Use the Filter Mechanism at the upper left
to search for specific entities in the ontology and the filters below each
column to narrow down the given results.
Initially the flow of browsing is left to right. You can flip the flow by
clicking the big arrows between the columns.',
	'smw_ob_undefined_type' => '*undefined range*',
	'smw_ob_hideinstances' => 'Hide Instances',
    'smw_ob_onlyDirect' => 'show inherited properties',
	'smw_ob_onlyAssertedCategories' => 'show instances with annotated categories only',
	'smw_ob_showRange' => 'show properties with selected category as range',
	'smw_ob_hasnumofsubcategories' => 'Number of subcategories',
	'smw_ob_hasnumofinstances' => 'Number of instances',
	'smw_ob_hasnumofproperties' => 'Number of properties',
	'smw_ob_hasnumofpropusages' => 'Property is annotated $1 times',
	'smw_ob_hasnumoftargets' => 'Instance is linked $1 times.',
	'smw_ob_hasnumoftempuages' => 'Template is used $1 times',
    'smw_ob_invalidtitle' => '!!!invalid title!!!',

	// Commands for Data Explorer
	'smw_ob_cmd_createsubcategory' => 'Add subcategory',
	'smw_ob_cmd_createsubcategorysamelevel' => 'Add category',
	'smw_ob_cmd_editcategory' => 'Edit Category',
	'smw_ob_cmd_renamecategory' => 'Rename',
	'smw_ob_cmd_createsubproperty' => 'Add property',
	'smw_ob_cmd_editproperty' => 'Edit property',
	'smw_ob_cmd_createsubpropertysamelevel' => 'Add property',
	'smw_ob_cmd_renameproperty' => 'Rename',
	'smw_ob_cmd_renameinstance' => 'Rename',
	'smw_ob_cmd_editinstance'  => 'Edit instance',
	'smw_ob_cmd_deleteinstance' => 'Delete instance',
	'smw_ob_cmd_createinstance' => 'Create instance',
	'smw_ob_cmd_addpropertytodomain' => 'Add property to domain: ',
	'smw_ob_cmd_deletecategory' => 'Delete category',
	'smw_ob_cmd_deleteproperty' => 'Delete property',
	
	// Advanced options in the Data Explorer 
	'smw_ob_source_wiki' => "-Wiki- (all bundles)" ,
	'smw_ob_advanced_options' => "Advanced options" ,
	'smw_ob_select_datasource' => "Select the data source to browse:" ,
	'smw_ob_select_bundle' => "Select the bundle to browse:" ,
	'smw_ob_select_multiple' => "To select <b>multiple</b> data sources hold down <b>CTRL</b> and select the items with a <b>mouse click</b>.",
	'smw_ob_ts_not_connected' => "No triple store found. Please ask your wiki administrator!",
		
	// Messages for Query Interface
	'queryinterface' => 'Query Interface',
	'smw_queryinterface' => 'Query Interface',
	'smw_qi_add_category' => 'Add Category',
	'smw_qi_add_instance' => 'Add Instance',
	'smw_qi_add_property' => 'Add Property',
	'smw_qi_add' => 'Add',
	'smw_qi_confirm' => 'OK',
	'smw_qi_cancel' => 'Cancel',
	'smw_qi_delete' => 'Delete',
	'smw_qi_close' => 'Close',
    'smw_qi_update' => 'Update',
    'smw_qi_discard_changes' => 'Discard changes',
	'smw_qi_usetriplestore' => 'Include inferred results via triple store',
	'smw_qi_preview' => 'Preview Results',
    'smw_qi_fullpreview' => 'Show full result',
	'smw_qi_no_preview' => 'No preview available yet',
	'smw_qi_clipboard' => 'Copy to clipboard',
	'smw_qi_reset' => 'Reset Query',
	'smw_qi_reset_confirm' => 'Do you really want to reset your query?',
	'smw_qi_querytree_heading' => 'Query Tree Navigation',
	'smw_qi_main_query_name' => 'Main',
    'smw_qi_section_option' => 'Query Options',
    'smw_qi_section_definition' => 'Query definition',
    'smw_qi_section_result' => 'Result',
	'smw_qi_preview_result' => 'Result Preview',	
	'smw_qi_layout_manager' => 'Result Format',
	'smw_qi_table_column_preview' => 'Table Column Preview',
	'smw_qi_article_title' => 'Article title',
	'smw_qi_load' => 'Load Query',
	'smw_qi_save' => 'Save Query',
	'smw_qi_close_preview' => 'Close Preview',
	'smw_qi_querySaved' => 'New query saved by QueryInterface',
	'smw_qi_exportXLS' => 'Export results to Excel',
	'smw_qi_showAsk' => 'Show full query',
	'smw_qi_ask' => '&lt;ask&gt; syntax',
	'smw_qi_parserask' => '{{#ask syntax',
    'smw_qi_queryastree' => 'Query Outline',
    'smw_qi_queryastext' => 'Query as text',
    'smw_qi_querysource' => 'Query Code',
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
    'smw_qi_tpee_header' => 'Select trust policy for your query results',
    'smw_qi_tpee_none' => 'Don\'t use any trust policy',
    'smw_qi_dstpee_selector_0' => 'Select from Datasource',
    'smw_qi_dstpee_selector_1' => 'Select by Trust Policy',
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
      'smw_qi_parserFunc' => 'Parser Function',
      'smw_qi_property' => 'Property',
      'smw_qi_value' => 'Value',
      'smw_qi_source' => 'Source',
      'smw_qi_graph' => 'Graph',

	// Tooltips for Query Interface
	'smw_qi_tt_addCategory' => 'By adding a category, only articles of this category are included',
	'smw_qi_tt_addInstance' => 'By adding an instance, only a single article is included',
	'smw_qi_tt_addProperty' => 'By adding a property, you can either list all results or search for specific values',
	'smw_qi_tt_tcp' => 'The Table Column Preview gives you a preview of the columns which will appear in the result table',
	'smw_qi_tt_prp' => 'The Result Preview immediately displays the resultset of the query',	
	'smw_qi_tt_qlm' => 'The Query Layout Manager enables you to define the layout of the query results',
    'smw_qi_tt_qdef' => 'Define your query',
    'smw_qi_tt_previewres' => 'Preview query results and format query',
    'smw_qi_tt_update' => 'Update the result preview of the query',
	'smw_qi_tt_preview' => 'Shows a preview of your query results including the layout settings',
    'smw_qi_tt_fullpreview' => 'Shows a full preview of all your query results including the layout settings',
	'smw_qi_tt_clipboard' => 'Copies your query text to the clipboard so it can easily be inserted into an article',
	'smw_qi_tt_showAsk' => 'This will output the full ask query',
	'smw_qi_tt_reset' => 'Resets the entire query',
	'smw_qi_tt_format' => 'Output format of your query',
	'smw_qi_tt_link' => 'Defines which parts of the result table will appear as links',
	'smw_qi_tt_intro' => 'Text that is prepended to the query results',
    'smw_qi_tt_outro' => 'Text that is appended to the query results',
	'smw_qi_tt_sort' => 'Column which will be used for sorting',
	'smw_qi_tt_limit' => 'Maximum number of results shown',
    'smw_qi_tt_offset' => 'Offset number from where to start display results',
	'smw_qi_tt_mainlabel' => 'Name of the first column',
	'smw_qi_tt_order' => 'Ascending or descending order',
	'smw_qi_tt_headers' => 'Show the headers of the table or not',
	'smw_qi_tt_default' => 'Text that will be shown if there are no results',
    'smw_qi_tt_treeview' => 'Display your query in a tree',
    'smw_qi_tt_textview' => 'Describe the query in stylized English',
    'smw_qi_tt_option' => 'Define general settings for how the query is executed',
    'smw_qi_tt_maintab_query' => 'Create a new query',
    'smw_qi_tt_maintab_load' => 'Load an existing query',
 'smw_qi_tt_addSubject' => 'Add Subject',
      'smw_qi_tt_delete' => 'Delete selected tree node',
      'smw_qi_tt_cancel' => 'Cancel all changes, return to the starting point',
      'smw_qi_tt_parserFunc' => 'View mediawiki parser function for current query',

	'smw_wikiedit_tab' => 'Edit with wikitext editor',
	// Annotation 
 	'smw_annotation_tab' => 'annotate',
	'smw_annotating'     => 'Annotating $1',
	'annotatethispage'   => 'Annotate this page',
   	
	// Special:SMWHaloAdmin
	'smwhaloadmin' => 'SMWHalo Administration',
	'smw_haloadmin_databaseinit' => 'Database initialization',
	'smw_haloadmin_description' => 'This special helps you during installation and upgrade of SMWHalo.', 
	'smw_haloadmin_databaseinit_description' => 'The below function ensures that your database is set up properly. Pressing "Initialize" will upgrade the database schema and install some required wiki pages for the use of semantic data. Alternatively you can use the maintenance script SMW_setup.php which is located in the SMWHalo maintenance folder.',
	'smw_haloadmin_ok' => 'SMWHalo extension is correctly installed.',
	'smw_predefined_props' => 'This is the predefined property "$1"',
    'smw_predefined_cats' => 'This is the predefined category "$1"',
	
	
	// Ontoskin
	'smw_search_this_wiki' => 'Search this wiki',
	'smw_last_visited' => 'Last visited:',
	'smw_pagecreation' => 'Created by $1 on $2, at $3',
	'smw_start_discussion' => 'Start $1',
	'smw_back_to_article' => 'back to article',
	'more_functions' => 'More',
	'smw_treeviewleft' => 'Open treeview to the left side',
	'smw_treeviewright' => 'Open treeview to the right side',
	
	// Querylist Special Page
	'querylist' => "Saved Queries",
	
	// Tabular Forms
	'smw_tf_paramdesc_add'		=> 'User is allowed to add new instances to the result',
	'smw_tf_paramdesc_delete'	=> 'User is allowed to delete instances from the result',
	'smw_tf_paramdesc_filtering'	=> 'Enable filtering.',
	'smw_tf_paramdesc_expertmode'	=> 'Disable view mode.',
	
	'tabf_load_msg' => "Tabular forms is loading.",

	'tabf_add_label' => "Add instance",
	'tabf_refresh_label' => "Refresh",
	'tabf_save_label' => "Apply changes",
	
	'tabf_status_unchanged' => "This instance has not yet been modified.",
	'tabf_status_notexist_create' => "This instance does not exist. It will be created.",
	'tabf_status_notexist' => "This instance does not exist.",
	'tabf_status_readprotected' => "This instance is read protected.",
	'tabf_status_writeprotected' => "This instance is write protected.",
	'tabf_status_delete' => "This instance will be deleted.",
	'tabf_status_modified' => "This instance has been modified.",
	'tabf_status_saved' => "This instance has been saved successfully.",
	'tabf_status_pending' => "Applying changes.",
	
	'tabf_response_deleted' => "This instance has been deleted in the meantime.",
	'tabf_response_modified' => "This instance has been modified in the meantime.",
	'tabf_response_readprotected' => "This instance has been made read-protected in the meantime.",
	'tabf_response_writeprotected' => "This instance has been made write-protected in the meantime.",
	'tabf_response_invalidname' => "This instance has an invalid name.",
	'tabf_response_created' => "This instance has been created in the meantime.",
	'tabf_response_nocreatepermission' => "You do not have the permission to create this instance.",
	'tabf_response_nodeletepermission' => "You do not have the permission to delete this instance.",
	
	'tabf_update_warning' => "Some errors occured while applying changes. Please have a look at the status icons.",
	
	'tabf_instancename_blank' => "Instance names cannot be blank.",
	'tabf_instancename_invalid' => "'$1' is not a valid instance name.",
	'tabf_instancename_exists' => "'$1' already exists.",
	'tabf_instancename_permission_error' => "You don't have the permission to create '$1'.",
	'tabf_annotationnamme_invalid' => "'$1' has an invalid value: The value '$2' of property '$3' is not of type $4.",
	
	'tabf_lost_reason_EQ' => "is equal to '$1'",
	'tabf_lost_reason_NEQ' => "is unequal to '$1'",
	'tabf_lost_reason_LEQ' => "is smaller or equal to '$1'",
	'tabf_lost_reason_GEQ' => "is greater or equal to '$1'",
	'tabf_lost_reason_LESS' => "is smaller than '$1'",
	'tabf_lost_reason_GRTR' => "is greater than '$1'",
	'tabf_lost_reason_EXISTS' => "is a valid annotation value",
	'tabf_lost_reason_introTS' => "'<span class=\"tabf_nin\">$1</span>', because none of the values of the '$2'  annotation ",
	
	'tabf_parameter_write_protected_desc' => "Write protected annotations",
	'tabf_parameter_instance_preload_desc' => "Instance name preload value",
	
	'tabf_ns_header_show' => "Show System Notifications",
	'tabf_ns_header_hide' => "Hide System Notifications",
	'tabf_ns_warning_invalid_instance_name' => "Changes currently cannot be applied, because some new instance names are erronious:",
	'tabf_ns_warning_invalid_value' => "The following annotation values are invalid:",
	'tabf_ns_warning_lost_instance_otf' => "The following instances may not be included in the query result anymore after applying changes::",
	'tabf_ns_warning_lost_instance' => "The following instances are now not part of the query result anymore",
	'tabf_ns_warning_save_error' => "The following instances could not be saved since they have been modified by someone else in the meantime.",
	'tabf_ns_warning_add_disabled' => "The 'Add instance' button was disabled by the system. Please provide a preload value for the following properties, mark them as write-protected, or use them as printout statements in order to enable 'Add instance' button again:",
	'tabf_ns_warning_by_system' => "Notifications and warnings from the query processor:",
	
	'tabf_nc_icon_title_lost_instance' => "Instance may not be included in the query result anymore after applying changes.",
	'tabf_nc_icon_title_invalid_value' => "Some annotation values of this instance are invalid.",
	'tabf_nc_icon_title_save_error' => "Changes for this instance could not be applied because it has been modified by someone else in the meantime.'",
	
	'tabf_paging_previous' => 'Previous',
	'tabf_paging_next' => 'Next',
	'tabf_paging_results' => 'Results',

	'tabf_filter_placeholder' => "Filter...",
	
	//--- fancy table result printer
	'ftrp_warning' => "Found invalid replace statements with unknown properties:",
	);


	protected $smwSpecialProperties = array(
		//always start upper-case
		"___cfsi" => array('_siu', 'Corresponds to SI'),
		"___CREA" => array('_wpg', 'Creator'),
		"___CREADT" => array('_dat', 'Creation date'),
		"___MOD" => array('_wpg', 'Last modified by')
	);


	var $smwSpecialSchemaProperties = array (
	SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT => 'Has domain and range',
	SMW_SSP_HAS_DOMAIN => 'Has domain',
    SMW_SSP_HAS_RANGE => 'Has range',
	SMW_SSP_HAS_MAX_CARD => 'Has max cardinality',
	SMW_SSP_HAS_MIN_CARD => 'Has min cardinality',
	SMW_SSP_IS_INVERSE_OF => 'Is inverse of',
	SMW_SSP_IS_EQUAL_TO => 'Is equal to',
	SMW_SSP_ONTOLOGY_URI => 'Ontology URI'
	);

	var $smwSpecialCategories = array (
	SMW_SC_TRANSITIVE_RELATIONS => 'Transitive properties',
	SMW_SC_SYMMETRICAL_RELATIONS => 'Symmetrical properties'
	);

	var $smwHaloDatatypes = array(
	'smw_hdt_chemical_formula' => 'Chemical formula',
	'smw_hdt_chemical_equation' => 'Chemical equation',
	'smw_hdt_mathematical_equation' => 'Mathematical equation',
	'smw_integration_link' => 'Integration link'
	);

	protected $smwHaloNamespaces = array(
	);

	protected $smwHaloNamespaceAliases = array(
	);

	/**
	 * Function that returns the namespace identifiers. This is probably obsolete!
	 */
	public function getNamespaceArray() {
		return array(
		SMW_NS_RELATION       => 'Relation',
		SMW_NS_RELATION_TALK  => 'Relation_talk',
		SMW_NS_PROPERTY       => 'Property',
		SMW_NS_PROPERTY_TALK  => 'Property_talk',
		SMW_NS_TYPE           => 'Type', // @deprecated
		SMW_NS_TYPE_TALK      => 'Type_talk' // @deprecated
		);
	}


}


