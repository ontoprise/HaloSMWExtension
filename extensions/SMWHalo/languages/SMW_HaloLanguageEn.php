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
 * @file
*  @ingroup SMWHaloLanguage
 * @author Ontoprise
 */

global $smwgHaloIP;
include_once($smwgHaloIP . '/languages/SMW_HaloLanguage.php');

class SMW_HaloLanguageEn extends SMW_HaloLanguage {

	protected $smwContentMessages = array(
    
	'smw_derived_property'  => 'This is a derived property.',
	'smw_sparql_disabled'=> 'No SPARQL support enabled.',
	'smw_viewinOB' => 'Open in OntologyBrowser',
    'smw_wysiwyg' => 'WYSIWYG',
	'smw_att_head' => 'Attribute values',
	'smw_rel_head' => 'Relations to other pages',
	'smw_predefined_props' => 'This is the predefined property "$1"',
	'smw_predefined_cats' => 'This is the predefined category "$1"',

	'smw_noattribspecial' => 'Special property "$1" is not an attribute (use "::" instead of ":=").',
	'smw_notype' => 'No type defined for attribute.',
	/*Messages for Autocompletion*/
	'tog-autotriggering' => 'Auto-triggered auto-completion',
    'smw_ac_typehint'=> 'Type: $1',
    'smw_ac_typerangehint'=> 'Type: $1 | Range: $2',

	// Messages for SI unit parsing
	'smw_no_si_unit' => 'No unit given in SI representation. ',
	'smw_too_many_slashes' => 'Too many slashes in SI representation. ',
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
	'smw_csh_icon_tooltip' => 'Click here if you need help or if you want to send feeback to the SMW+ developers.'
	);


	protected $smwUserMessages = array(
	'specialpages-group-smwplus_group' => 'Semantic Mediawiki+',
	'smw_devel_warning' => 'This feature is currently under development, and might not be fully functional. Backup your data before using it.',
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


	'smw_exportrdf_all' => 'Export all semantic data',

	// Messages for Search Triple Special
	'searchtriple' => 'Simple semantic search', //name of this special
	'smw_searchtriple_docu' => "<p>Fill in either the upper or lower row of the input form to search for relations or attributes, respectively. Some of the fields can be left empty to obtain more results. However, if an attribute value is given, the attribute name must be specified as well. As usual, attribute values can be entered with a unit of measurement.</p>\n\n<p>Be aware that you must press the right button to obtain results. Just pressing <i>Return</i> might not trigger the search you wanted.</p>",
	'smw_searchtriple_subject' => 'Subject page:',
	'smw_searchtriple_relation' => 'Relation name:',
	'smw_searchtriple_attribute' => 'Attribute name:',
	'smw_searchtriple_object' => 'Object page:',
	'smw_searchtriple_attvalue' => 'Attribute value:',
	'smw_searchtriple_searchrel' => 'Search Relations',
	'smw_searchtriple_searchatt' => 'Search Attributes',
	'smw_searchtriple_resultrel' => 'Search results (relations)',
	'smw_searchtriple_resultatt' => 'Search results (attributes)',

	// Messages for Relations Special
	'relations' => 'Relations',
	'smw_relations_docu' => 'The following relations exist in the wiki.',
	// Messages for WantedRelations Special
	'wantedrelations' => 'Wanted relations',
	'smw_wanted_relations' => 'The following relations do not have an explanatory page yet, though they are already used to describe other pages.',
	// Messages for Properties Special
	'properties' => 'Properties',
	'smw_properties_docu' => 'The following properties exist in the wiki.',
	'smw_attr_type_join' => ' with $1',
	'smw_properties_sortalpha' => 'Sort alphabetically',
	'smw_properties_sortmoddate' => 'Sort by modification date',
	'smw_properties_sorttyperange' => 'Sort by type/range',

	'smw_properties_sortdatatype' => 'Datatype properties',
	'smw_properties_sortwikipage' => 'Wikipage properties',
	'smw_properties_sortnary' => 'N-ary properties',
	// Messages for Unused Relations Special
	'unusedrelations' => 'Unused relations',
	'smw_unusedrelations_docu' => 'The following relation pages exist although no other page makes use of them.',
	// Messages for Unused Attributes Special
	'unusedattributes' => 'Unused attributes',
	'smw_unusedattributes_docu' => 'The following attribute pages exist although no other page makes use of them.',


	/*Messages for OntologyBrowser*/
	'ontologybrowser' => 'OntologyBrowser',
	'smw_ac_hint' => 'Press Ctrl+Alt+Space to use auto-completion. (Ctrl+Space in IE)',
	'smw_ob_categoryTree' => 'Category Tree',
	'smw_ob_attributeTree' => 'Property Tree',

	'smw_ob_instanceList' => 'Instances',

	'smw_ob_att' => 'Properties',
	'smw_ob_relattValues' => 'Values',
	'smw_ob_relattRangeType' => 'Type/Range',
	'smw_ob_filter' => 'Filter',
	'smw_ob_filterbrowsing' => 'Filter Browsing',
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
	'smw_ob_help' => 'The ontology browser lets you navigate through the ontology to easily find
and identify items in the wiki. Use the Filter Mechanism at the upper left
to search for specific entities in the ontology and the filters below each
column to narrow down the given results.
Initially the flow of browsing is left to right. You can flip the flow by
clicking the big arrows between the columns.',
	'smw_ob_undefined_type' => '*undefined range*',
	'smw_ob_hideinstances' => 'Hide Instances',
    'smw_ob_onlyDirect' => 'no inferred',
	'smw_ob_showRange' => 'properties of range',
	'smw_ob_hasnumofsubcategories' => 'Number of subcategories',
	'smw_ob_hasnumofinstances' => 'Number of instances',
	'smw_ob_hasnumofproperties' => 'Number of properties',
	'smw_ob_hasnumofpropusages' => 'Property is annotated $1 times',
	'smw_ob_hasnumoftargets' => 'Instance is linked $1 times.',
	'smw_ob_hasnumoftempuages' => 'Template is used $1 times',
    'smw_ob_invalidtitle' => '!!!invalid title!!!',

	/* Commands for ontology browser */
	'smw_ob_cmd_createsubcategory' => 'Add subcategory',
	'smw_ob_cmd_createsubcategorysamelevel' => 'Add cat. on same level',
	'smw_ob_cmd_renamecategory' => 'Rename',
	'smw_ob_cmd_createsubproperty' => 'Add subproperty',
	'smw_ob_cmd_createsubpropertysamelevel' => 'Add prop. on same level',
	'smw_ob_cmd_renameproperty' => 'Rename',
	'smw_ob_cmd_renameinstance' => 'Rename instance',
	'smw_ob_cmd_deleteinstance' => 'Delete instance',
	'smw_ob_cmd_addpropertytodomain' => 'Add property to domain: ',
	
	/* Advanced options in the ontology browser */
	'smw_ob_source_wiki' => "-Wiki-" ,
	'smw_ob_advanced_options' => "Advanced options" ,
	'smw_ob_select_datasource' => "Select the data source to browse:" ,
	'smw_ob_select_multiple' => "To select <b>multiple</b> data sources hold down <b>CTRL</b> and select the items with a <b>mouse click</b>.",
	'smw_ob_ts_not_connected' => "No triple store found. Please ask your wiki administrator!",
	

	/* Combined Search*/
	'smw_combined_search' => 'Combined Search',
	'smw_cs_entities_found' => 'The following entities were found in the wiki ontology:',
	'smw_cs_attributevalues_found' => 'The following instances contain property values matching your search:',
	'smw_cs_aksfor_allinstances_with_annotation' => 'Ask for all instances of \'$1\' which have an annotation of \'$2\'',
	'smw_cs_askfor_foundproperties_and_values' => 'Ask instance \'$1\' for all properties found.',
	'smw_cs_ask'=> 'Show',
	'smw_cs_noresults' => 'Sorry, there are no entities in the ontology matching your search term.',
	'smw_cs_searchforattributevalues' => 'Search for property values that match your search',
	'smw_cs_instances' => 'Articles',
	'smw_cs_properties' => 'Properties',
	'smw_cs_values' => 'Values',
	'smw_cs_openpage' => 'Open page',
	'smw_cs_openpage_in_ob' => 'Open in Ontology Browser',
	'smw_cs_openpage_in_editmode' => 'Edit page',
	'smw_cs_no_triples_found' => 'No triples found!',

	'smw_autogen_mail' => 'This is an automatically generated email. Do not reply!',

	

	/*Messages for ContextSensitiveHelp*/
	'contextsensitivehelp' => 'Context Sensitive Help',
	'smw_contextsensitivehelp' => 'Context Sensitive Help',
	'smw_csh_newquestion' => 'This is a new help question. Click to answer it!',
	'smw_csh_nohelp' => 'No relevant help questions have been added to the system yet.',
	'smw_csh_refine_search_info' => 'You can refine your search according to the page type and/or the action you would like to know more about:',
	'smw_csh_page_type' => 'Page type',
	'smw_csh_action' => 'Action',
	'smw_csh_ns_main' => 'Main (Regular Wiki Page)',
	'smw_csh_all' => 'ALL',
	'smw_csh_search_special_help' => 'You can also search for help on the special features of this wiki:',
	'smw_csh_show_special_help' => 'Search for help on:',
	'smw_csh_categories' => 'Categories',
	'smw_csh_properties' => 'Properties',
	'smw_csh_mediawiki' => 'MediaWiki Help',
	/* Messages for the CSH discourse state. Do NOT edit or translate these
	 * otherwise CSH will NOT work correctly anymore
	 */
	'smw_csh_ds_ontologybrowser' => 'OntologyBrowser',
	'smw_csh_ds_queryinterface' => 'QueryInterface',
	'smw_csh_ds_combinedsearch' => 'Search',

	/*Messages for Query Interface*/
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
    'smw_qi_section_definition' => 'Query definition',
    'smw_qi_section_result' => 'Result',
	'smw_qi_preview_result' => 'Result Preview',	
	'smw_qi_layout_manager' => 'Format Query',
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
    'smw_qi_queryastree' => 'Query as tree',
    'smw_qi_queryastext' => 'Query as text',
    'smw_qi_querysource' => 'Query source',
    'smw_qi_printout_err1' => 'The selected format for the query result needs at least one additional property that is shown in the result.',
    'smw_qi_printout_err2' => 'The selected format for the query result needs at least one property of the type date that is shown in the result.',
    'smw_qi_printout_err3' => 'The selected format for the query result needs at least one property of a numeric type that is shown in the result.',
    'smw_qi_printout_err4' => 'Your query did not return any results.',

	/*Tooltips for Query Interface*/
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
	'smw_qi_tt_mainlabel' => 'Name of the first column',
	'smw_qi_tt_order' => 'Ascending or descending order',
	'smw_qi_tt_headers' => 'Show the headers of the table or not',
	'smw_qi_tt_default' => 'Text that will be shown if there are no results',
    'smw_qi_tt_treeview' => 'Display your query in a tree',
    'smw_qi_tt_textview' => 'Describe the query in stylized English',

	/* Annotation */
 	'smw_annotation_tab' => 'annotate',
	'smw_annotating'     => 'Annotating $1',
	'annotatethispage'   => 'Annotate this page',


	/* Refactor preview */
 	'refactorstatistics' => 'Refactor Statistics',
 	'smw_ob_link_stats' => 'Open refactor statistics',

	


	/* Gardening Issue Highlighting in Inline Queries */
	'smw_iqgi_missing' => 'missing',
	'smw_iqgi_wrongunit' => 'wrong unit',

	'smw_deletepage_nolinks' => 'There are no links to this page!',
    'smw_deletepage_linkstopage'=> 'Pages with links to that page',
    'smw_deletepage_prev' => 'Prev',
    'smw_deletepage_next' => 'Next',
	
    // Triple Store Admin
    'tsa' => 'Triple store administration',
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
	'asktsc' => 'Ask triplestore',
	
	
	// Derived facts
	'smw_df_derived_facts_about' => 'Derived facts about $1',
	'smw_df_static_tab'			 => 'Static facts',
	'smw_df_derived_tab'		 => 'Derived facts',
	'smw_df_static_facts_about'  => 'Static facts about this article',
	'smw_df_derived_facts_about' => 'Derived facts about this article',
	'smw_df_loading_df'			 => 'Loading derived facts...',
	'smw_df_invalid_title'		 => 'Invalid article. No derived facts available.',
	'smw_df_no_df_found'		 => 'No derived facts found for this article.',
	
    //skin
    'smw_search_this_wiki' => 'Search this wiki',
    'more_functions' => 'More',
    'smw_treeviewleft' => 'Open treeview to the left side',
    'smw_treeviewright' => 'Open treeview to the right side',
	
	// Geo coord data type
	'semanticmaps_lonely_unit'     => 'No number found before the symbol "$1".', // $1 is something like Â°
	'semanticmaps_bad_latlong'     => 'Latitude and longitude must be given only once, and with valid coordinates.',
	'semanticmaps_abb_north'       => 'N',
	'semanticmaps_abb_east'        => 'E',
	'semanticmaps_abb_south'       => 'S',
	'semanticmaps_abb_west'        => 'W',
	'semanticmaps_label_latitude'  => 'Latitude:',
	'semanticmaps_label_longitude' => 'Longitude:'

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
	SMW_SSP_HAS_MAX_CARD => 'Has max cardinality',
	SMW_SSP_HAS_MIN_CARD => 'Has min cardinality',
	SMW_SSP_IS_INVERSE_OF => 'Is inverse of',
	SMW_SSP_IS_EQUAL_TO => 'Is equal to'
	);

	var $smwSpecialCategories = array (
	SMW_SC_TRANSITIVE_RELATIONS => 'Transitive properties',
	SMW_SC_SYMMETRICAL_RELATIONS => 'Symmetrical properties'
	);

	var $smwHaloDatatypes = array(
	'smw_hdt_chemical_formula' => 'Chemical formula',
	'smw_hdt_chemical_equation' => 'Chemical equation',
	'smw_hdt_mathematical_equation' => 'Mathematical equation'
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
		SMW_NS_TYPE           => 'Type',
		SMW_NS_TYPE_TALK      => 'Type_talk'
		);
	}


}


