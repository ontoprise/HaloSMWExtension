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
 * @author Markus Krötzsch
 */

global $smwgHaloIP;
include_once($smwgHaloIP . '/languages/SMW_HaloLanguage.php');

class SMW_HaloLanguageEn extends SMW_HaloLanguage {

protected $smwContentMessages = array(

	'smw_viewinOB' => 'Open in OntologyBrowser',

	'smw_att_head' => 'Attribute values',
	'smw_rel_head' => 'Relations to other pages',
	'smw_predefined_props' => 'This is the predefined property "$1"',
	'smw_predefined_cats' => 'This is the predefined category "$1"',

	'smw_noattribspecial' => 'Special property "$1" is not an attribute (use "::" instead of ":=").',
	'smw_notype' => 'No type defined for attribute.',
	/*Messages for Autocompletion*/
	'tog-autotriggering' => 'Auto-triggered auto-completion',


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
	'smw_help_question_added' => "Your question has been added to our help system\nand can now be answered by other wiki users."

);


protected $smwUserMessages = array(
	'smw_devel_warning' => 'This feature is currently under development, and might not be fully functional. Backup your data before using it.',
	// Messages for pages of types, relations, and attributes

	'smw_relation_header' => 'Pages using the property "$1"',
	'smw_relationarticlecount' => '<p>Showing $1 pages using this property.</p>',
	'smw_subproperty_header' => 'Sub-properties of "$1"',
	'smw_subpropertyarticlecount' => '<p>Showing $1 sub-properties.</p>',

	// Messages for category pages
	'smw_category_schemainfo' => 'Schema information for category "$1"',
	'smw_category_properties' => 'Properties',
	'smw_category_properties_range' => 'Properties whose range is "$1"',
	'smw_category_attributes' => 'Attributes',
	'smw_category_askforallinstances' => 'Ask for all instances of "$1" and for all instances of its subcategories',
	'smw_category_queries' => 'Queries for categories',
	'smw_category_attributes_range' => 'Attributes whose range is "$1".',
	'smw_category_attributes_range_expl' => '(Error! Attributes must not have a category as range!)',
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
	'smw_ob_undefined_type' => '*undefined type*',
	'smw_ob_hideinstances' => 'Hide Instances',
	
	/* Commands for ontology browser */
	'smw_ob_cmd_createsubcategory' => 'Add subcategory',
	'smw_ob_cmd_createsubcategorysamelevel' => 'Add subcategory on same level',
	'smw_ob_cmd_renamecategory' => 'Rename',
	'smw_ob_cmd_createsubproperty' => 'Add subproperty',
	'smw_ob_cmd_createsubpropertysamelevel' => 'Add subproperty on same level',
	'smw_ob_cmd_renameproperty' => 'Rename',
	'smw_ob_cmd_renameinstance' => 'Rename instance',
	'smw_ob_cmd_deleteinstance' => 'Delete instance',
	'smw_ob_cmd_addpropertytodomain' => 'Add property to domain: ',
	
	
	/* Messages for Gardening */
	'gardening' => 'Gardening', // name of special page 'Gardening'
	'gardeninglog' => 'GardeningLog', // name of special page 'GardeningLog'
	
	'smw_gardening_log_cat' => 'GardeningLog',
	'smw_gardeninglogs_docu' => 'This page provides access to logs created by the Gardening Bots. You can filter them in various ways.',
	'smw_gardening_log_exp' => 'This is the Gardening log category',
	'smw_gardeninglog_link' => 'Also take a look at $1 special page for further logs.',
	'smw_gard_welcome' => 'This is the Gardening toolbox.It provides some tools which helps to keep the wiki content clean and consistent.',
	'smw_gard_notools' => 'If you do not see any tools here, you might not be logged in or you do not have any rights to use gardening tools.',
	'smw_no_gard_log' => 'No Gardening log',
	'smw_gard_abortbot' => 'Cancel Bot',
	'smw_gard_unknown_bot' => 'Unknown Bot',
	'smw_gard_no_permission' => 'You do not have the permission to use this bot.',
	'smw_gard_missing_parameter' => 'Missing parameter',
	'smw_gard_missing_selection' => 'Missing selection',
	'smw_unknown_value' => 'Unknown value',
	'smw_out_of_range' => 'Out of range',
	'smw_gard_value_not_numeric' => 'Value must be a number',
	'smw_gard_choose_bot' => 'Choose a gardening action from the left.',
	'smw_templatematerializerbot' => 'Materialize template content',
	'smw_consistencybot' => 'Check wiki consistency',
	'smw_similaritybot' => 'Find similar entities',
	'smw_undefinedentitiesbot' => 'Find undefined entities',
	'smw_missingannotationsbot' => 'Find pages without annotations',
	'smw_anomaliesbot' => 'Find anomalies',
	'smw_renamingbot' => 'Rename page',
	'smw_importontologybot' => 'Import an ontology',
	'smw_gardissue_class_all' => 'All',
	
	/* Messages for Gardening Bot: ImportOntology Bot*/
	'smw_gard_import_choosefile' => 'The following $1 files are available.',
	'smw_gard_import_addfiles' => 'Add $2 files by using $1.',
	'smw_gard_import_nofiles' => 'No files of type $1 are available',
	
	/* Messages for Gardening Bot: ConsistencyBot */
	'smw_gard_consistency_docu'  => 'The consistency bot checks for cycles in the taxonomy and properties without domain and range. It also checks the correct usage of properties according to domain and range information as well as cardinality errors.',
	'smw_gard_no_errors' => 'Congratulations! The wiki is consistent.',
	
	'smw_gardissue_domains_not_covariant' => 'Domain $2 of $1 must be a subcategory of the domains of its super property.',
	'smw_gardissue_domains_not_defined' => 'Domain of $1 is not defined.',
	'smw_gardissue_ranges_not_covariant' => 'Range $2 of $1 must be a subcategory of the ranges of its super property.',
	'smw_gardissue_domains_and_ranges_not_defined' => 'Domain and Range of $1 is not defined.',
	'smw_gardissue_types_not_covariant' => 'Type of $1 must be equal to type of its superproperty.',
	'smw_gardissue_types_not_defined' => 'Warning: Type of $1 is not defined. Is Type:Page intended? Please specify explicitly.',
	'smw_gardissue_double_type' => '$1 has more than one type defined: $2 "has type" annotations.',
	'smw_gardissue_mincard_not_covariant' => 'Minimum cardinality of $1 is lower than at super property of it.',
	'smw_gardissue_maxcard_not_covariant' => 'Maximum cardinality of $1 is higher than at super property of it.',
	'smw_gardissue_maxcard_not_null' => 'Maximum cardinality of $1 must not be 0.',
	'smw_gardissue_mincard_below_null' => 'Minimum cardinality of $1 must not be below 0.',
	'smw_gardissue_symetry_not_covariant1' => 'Super Property $1 must also be symetrical',
	'smw_gardissue_symetry_not_covariant2' => '$1 must also be symetrical',
	'smw_gardissue_transitivity_not_covariant1' => 'Super Property of $1 must also be transitive',
	'smw_gardissue_transitivity_not_covariant2' => '$1 must also be transitive',
	'smw_gardissue_double_max_card' => 'Warning: More than one max cardinality at property $1 detected. Taking first value, which is $2.',
	'smw_gardissue_double_min_card' => 'Warning: More than one min cardinality at property $1 detected. Taking first value, which is $2.',
	'smw_gardissue_wrong_mincard_value' => 'Warning: Min Cardinality for $1 has wrong value. Will be interpreted as 0.',
	'smw_gardissue_wrong_maxcard_value' => 'Warning: Max Cardinality for $1 has wrong value. Must be a positive integer or *. Will be interpreted as 0.',
	'smw_gard_issue_missing_param' => 'Warning: Missing parameter $3 in n-ary property $2 in article $1.',

	'smw_gard_issue_domain_not_range' => 'Domain of $1 does not match range of inverse property $2.',
	'smw_gardissue_wrong_target_value' => 'Article $1 uses property $2 with an instance which is member of wrong category: $3.',
	'smw_gardissue_wrong_domain_value' => 'Article $1 has wrong domain category when using the property $2.',
	'smw_gardissue_too_low_card' => 'Article $1 uses property $2 too less.',
	'smw_gardissue_too_high_card' => 'Article $1 uses property $2 too often.',
	'smw_gardissue_wrong_unit' => 'Article $1 uses property $2 with wrong unit: "$3".',
	'smw_gard_issue_incompatible_entity' => 'The entity $1 is incompatible to $2',
	'smw_gard_issue_incompatible_type' => 'The property $1 has an incompatible type to $2',
	'smw_gard_issue_incompatible_supertypes' => 'The property $1 has superproperties with incompatible types.',
	
	'smw_gard_issue_cycle' => 'Cycle at: $1',
	
	'smw_gardissue_class_covariance' => 'Covariance problems',
	'smw_gardissue_class_undefined' => 'Incomplete schema',
	'smw_gardissue_class_missdouble' => 'Doubles',
	'smw_gardissue_class_wrongvalue' => 'Wrong/missing values',
	'smw_gardissue_class_incomp' => 'Incompatible entities',
	'smw_gardissue_class_cycles' => 'Cycles',

	/* SimilarityBot*/
	'smw_gard_degreeofsimilarity' => 'Edit distance limit',
	'smw_gard_similarityscore' => 'Similarity Score (No. of similarities)',
	'smw_gard_limitofresults' => 'Number of results',
	'smw_gard_limitofsim' => 'Show only entities which appear to be',
	'smw_gard_similarityterm' => 'Search for entities similar to the following term (may be empty)',
	'smw_gard_similaritybothelp' => 'This is the bot to identify entities in the knowledgebase that could potentially be unified. If you enter a term, the system will try to find entities which are similar to that. If you do not enter a term, the system will find possibly redundant entities.',
	
	'smw_gardissue_similar_schema_entity' => '$1 and $2 are lexically similar.',
	'smw_gardissue_similar_annotation' => '$3 contain two very similar annotations: $1 and $2',
	'smw_gardissue_similar_term' => '$1 is similar to term $2',
	'smw_gardissue_share_categories' => '$1 and $2 share the following categories: $3',
	'smw_gardissue_share_domains' => '$1 and $2 share the following domains: $3',
	'smw_gardissue_share_ranges' =>  '$1 and $2 share the following ranges: $3',
	'smw_gardissue_share_types' => '$1 and $2 share the following types: $3',
	'smw_gardissue_distinctby_prefix' => '$1 and $2 are distinct by common prefix or suffix',
	
	'smw_gardissue_class_similarschema' => 'Similar schema elements',
	'smw_gardissue_class_similarannotations' => 'Similar annotations',

	/*Undefined entities bot */
	'smw_gard_undefinedentities_docu' => 'The undefined entities bot searches for categories and properties that are used within the wiki but not defined, as well as instances that have no category.',
	'smw_gardissue_property_undefined' => '$1 used on : $2',
	'smw_gardissue_category_undefined' => '$1 used on: $2',
	'smw_gardissue_relationtarget_undefined' => '$1 undefined when used with: $2',
	'smw_gardissue_instance_without_cat' => '$1 is not member of a category',
	
	'smw_gardissue_class_undef_categories' => 'Undefined categories',
	'smw_gardissue_class_undef_properties' => 'Undefined properties',
	'smw_gardissue_class_undef_relationtargets' => 'Undefined relation targets',
	'smw_gardissue_class_instances_without_cat' => 'Instances without category',


	/* Missing annotations */
	'smw_gard_missingannot_docu' => 'This bot identifies pages in the Wiki that are not annotated.',
	'smw_gard_missingannot_titlecontaining' => '(Optional) Only Pages with a title containing',
	'smw_gard_missingannot_restricttocategory' => 'Restrict to categories',
	'smw_gardissue_notannotated_page' => 'Article $1 has no annotations',

	/* Anomalies */
	'smw_gard_anomaly_checknumbersubcat' => 'Check number of sub categories',
	'smw_gard_anomaly_checkcatleaves' => 'Check for category leafs',
	'smw_gard_anomaly_restrictcat' => 'Restrict to categories (separated by ;)',
	'smw_gard_anomaly_deletecatleaves' => 'Delete category leaves',
	'smw_gard_anomaly_docu' => 'This bot identifies  Category leafs (Categories that contain neither subcategories nor instances) and Subcategory number anomalies (Categories with only one or more than eight subcategories).',
	'smw_gard_anomalylog' => 'The following is a list of current anomalies in the wiki:',

	
	'smw_gard_all_category_leaves_deleted' => 'All Category leaves were removed.',
	'smw_gard_category_leaves_deleted' => 'Category leaves below [[$1:$2]] were removed.',
	'smw_gard_category_leaf_deleted' => '$1 was a category leaf. Removed by anomaly bot.',
	'smw_gardissue_category_leaf' => '$1 is a category leaf.',
	'smw_gardissue_subcategory_anomaly' => '$1 has $2 subcategories.',
	
	'smw_gardissue_class_category_leaves' => 'Category leaves',
	'smw_gardissue_class_number_anomalies' => 'Subcategory anomaly',
	
	
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

	/*Message for ImportOntologyBot*/
	'smw_gard_import_docu' => 'Imports an OWL file.',

	/*Message for TemplateMateriazerBot*/
	'smw_gard_templatemat_docu' => 'This bot updates the wikipages which use templates that got new annotations to includes these annotations in the results of an ASK query.',
	'smw_gard_templatemat_applytotouched' => 'Apply only to touched templates',
	'smw_gardissue_updatearticle' => 'Article $1 was newly parsed.',

	/*Messages for ContextSensitiveHelp*/
	'contextsensitivehelp' => 'Context Sensitive Help',
	'smw_contextsensitivehelp' => 'Context Sensitive Help',
	'smw_csh_newquestion' => 'This is a new help question. Click to answer it!',
	'smw_csh_refine_search_info' => 'You can refine your search according to the page type and/or the action you would like to know more about:',
	'smw_csh_page_type' => 'Page type',
	'smw_csh_action' => 'Action',
	'smw_csh_ns_main' => 'Main (Regular Wiki Page)',
	'smw_csh_all' => 'ALL',
	'smw_csh_search_special_help' => 'You can also search for help on the special features of this wiki:',
	'smw_csh_show_special_help' => 'Search for help on:',
	'smw_csh_categories' => 'Categories',
	'smw_csh_properties' => 'Properties',
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
	'smw_qi_preview' => 'Preview Results',
	'smw_qi_no_preview' => 'No preview available yet',
	'smw_qi_clipboard' => 'Copy to clipboard',
	'smw_qi_reset' => 'Reset Query',
	'smw_qi_reset_confirm' => 'Do you really want to reset your query?',
	'smw_qi_querytree_heading' => 'Query Tree Navigation',
	'smw_qi_main_query_name' => 'Main',
	'smw_qi_layout_manager' => 'Query Layout Manager',
	'smw_qi_table_column_preview' => 'Table Column Preview',
	'smw_qi_article_title' => 'Article title',
	'smw_qi_load' => 'Load Query',
	'smw_qi_save' => 'Save Query',
	'smw_qi_close_preview' => 'Close Preview',
	'smw_qi_querySaved' => 'New query saved by QueryInterface',
	'smw_qi_exportXLS' => 'Export results to Excel',
	'smw_qi_showAsk' => 'Show full query',

	/*Tooltips for Query Interface*/
	'smw_qi_tt_addCategory' => 'By adding a category, only articles of this category are included',
	'smw_qi_tt_addInstance' => 'By adding an instance, only a single article is included',
	'smw_qi_tt_addProperty' => 'By adding a property, you can either list all results or search for specific values',
	'smw_qi_tt_tcp' => 'The Table Column Preview gives you a preview of the columns which will appear in the result table',
	'smw_qi_tt_qlm' => 'The Query Layout Manager enables you to define the layout of the query results',
	'smw_qi_tt_preview' => 'Shows a full preview of your query results including the layout settings',
	'smw_qi_tt_clipboard' => 'Copies your query text to the clipboard so it can easily be inserted into an article',
	'smw_qi_tt_showAsk' => 'This will output the full ask query',
	'smw_qi_tt_reset' => 'Resets the entire query',
	'smw_qi_tt_format' => 'Output format of your query',
	'smw_qi_tt_link' => 'Defines which parts of the result table will appear as links',
	'smw_qi_tt_intro' => 'Text that is prepended to the query results',
	'smw_qi_tt_sort' => 'Column which will be used for sorting',
	'smw_qi_tt_limit' => 'Maximum number of results shown',
	'smw_qi_tt_mainlabel' => 'Name of the first column',
	'smw_qi_tt_order' => 'Ascending or descending order',
	'smw_qi_tt_headers' => 'Show the headers of the table or not',
	'smw_qi_tt_default' => 'Text that will be shown if there are no results',
	
	/* Annotationtab */
 	'smw_annotation_tab' => 'annotate'
);


protected $smwSpecialProperties = array(
	//always start upper-case
	SMW_SP_CONVERSION_FACTOR_SI => 'Corresponds to SI'
);


var $smwSpecialSchemaProperties = array (
	SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT => 'Has domain and range',
	SMW_SSP_HAS_MAX_CARD => 'Has max cardinality',
	SMW_SSP_HAS_MIN_CARD => 'Has min cardinality',
	SMW_SSP_IS_INVERSE_OF => 'Is inverse of',
	SMW_SSP_IS_EQUAL_TO => 'Is equal to'
	);

var $smwSpecialCategories = array (
	SMW_SC_TRANSITIVE_RELATIONS => 'Transitive relations',
	SMW_SC_SYMMETRICAL_RELATIONS => 'Symmetrical relations'
);

var $smwHaloDatatypes = array(
	'smw_hdt_chemical_formula' => 'Chemical formula',
	'smw_hdt_chemical_equation' => 'Chemical equation',
	'smw_hdt_mathematical_equation' => 'Mathematical equation',
);

	/**
	 * Function that returns the namespace identifiers.
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


