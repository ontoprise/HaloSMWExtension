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
    'smw_wysiwyg' => 'WYSIWYG',
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
	'smw_ob_undefined_type' => '*undefined type*',
	'smw_ob_hideinstances' => 'Hide Instances',

	'smw_ob_hasnumofsubcategories' => 'Number of subcategories',
	'smw_ob_hasnumofinstances' => 'Number of instances',
	'smw_ob_hasnumofproperties' => 'Number of properties',
	'smw_ob_hasnumofpropusages' => 'Property is annotated $1 times',
	'smw_ob_hasnumoftargets' => 'Instance is linked $1 times.',
	'smw_ob_hasnumoftempuages' => 'Template is used $1 times',

	/* Commands for ontology browser */
	'smw_ob_cmd_createsubcategory' => 'Add subcategory',
	'smw_ob_cmd_createsubcategorysamelevel' => 'Add category on same level',
	'smw_ob_cmd_renamecategory' => 'Rename',
	'smw_ob_cmd_createsubproperty' => 'Add subproperty',
	'smw_ob_cmd_createsubpropertysamelevel' => 'Add property on same level',
	'smw_ob_cmd_renameproperty' => 'Rename',
	'smw_ob_cmd_renameinstance' => 'Rename instance',
	'smw_ob_cmd_deleteinstance' => 'Delete instance',
	'smw_ob_cmd_addpropertytodomain' => 'Add property to domain: ',


	/* Messages for Gardening */
	'gardening' => 'Gardening', // name of special page 'Gardening'
	'gardeninglog' => 'GardeningLog', // name of special page 'GardeningLog'
	'smw_gard_param_replaceredirects' => 'Replace redirects',
	'smw_gardening_log_cat' => 'GardeningLog',
	'smw_gardeninglogs_docu' => 'This page provides access to logs created by the Gardening Bots. You can filter them in various ways.',
	'smw_gardening_log_exp' => 'This is the Gardening log category',
	'smw_gardeninglog_link' => 'Also take a look at $1 special page for further logs.',
	'smw_gard_welcome' => 'This is the Gardening toolbox.It provides some tools which helps to keep the wiki knowledgebase clean and consistent.',
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
	'smw_termimportbot' => 'Import terms of a vocabulary',
	
	/* Messages for Gardening Bot: ImportOntology Bot*/
	'smw_gard_import_choosefile' => 'The following $1 files are available.',
	'smw_gard_import_addfiles' => 'Add $2 files by using $1.',
	'smw_gard_import_nofiles' => 'No files of type $1 are available',

	/* Messages for Gardening Bot: ConsistencyBot */
	'smw_gard_consistency_docu'  => 'The consistency bot checks for cycles in the taxonomy and properties without domain and range. It also checks the correct usage of properties according to domain and range information as well as cardinality errors.',
	'smw_gard_no_errors' => 'Congratulations! The wiki is consistent.',
	'smw_gard_issue_local' => 'this article',


	'smw_gardissue_domains_not_covariant' => 'Please match the domain $2 of $1 to the domain category of its super property (or a subcategory).',
	'smw_gardissue_domains_not_defined' => 'Please define the domain of $1.',
	'smw_gardissue_ranges_not_covariant' => 'Please match the range $2 of $1 to a subcategory of the ranges of its super property.',
	'smw_gardissue_domains_and_ranges_not_defined' => 'Please define the domain and/or range of $1.',
	'smw_gardissue_ranges_not_defined' => 'Please define the range of $1',
	'smw_gardissue_types_not_covariant' => 'Please match the datatype of $1 to the datatype of its superproperty.',
	'smw_gardissue_types_not_defined' => 'The datatype of $1 is not defined. Please specify explicitly. Type:page is taken as default.',
	'smw_gardissue_double_type' => 'Please chose the right datatype for $1, right now it has $2 "has type" annotations.',
	'smw_gardissue_mincard_not_covariant' => 'Please specify a minimum cardinality for $1 that is higher or equal than that of its superproperty.',
	'smw_gardissue_maxcard_not_covariant' => 'Please specify a maximum cardinality for  $1 that is lower or equal than that of its superproperty.',
	'smw_gardissue_maxcard_not_null' => 'Please define a maximum cardinality of $1 above 0.',
	'smw_gardissue_mincard_below_null' => 'Please define a positive minimum cardinality of $1.',
	'smw_gardissue_symetry_not_covariant1' => 'The super property of $1 must also be symetrical.',
	'smw_gardissue_symetry_not_covariant2' => '$1 must be symetrical according to its super property.',
	'smw_gardissue_transitivity_not_covariant1' => 'The super property of $1 must also be transitive.',
	'smw_gardissue_transitivity_not_covariant2' => '$1 must be transitive according to its superproperty.',
	'smw_gardissue_double_max_card' => 'Please specify only one max cardinality for $1. Taking first value, which is $2.',
	'smw_gardissue_double_min_card' => 'Please specify only one min cardinality for $1. Taking first value, which is $2.',
	'smw_gardissue_wrong_mincard_value' => 'Please correct the min cardinality for $1 . Will be interpreted as 0.',
	'smw_gardissue_wrong_maxcard_value' => 'Please correct the max cardinality for $1 . It should be a positive integer or *. Will be interpreted as 0.',
	'smw_gard_issue_missing_param' => 'Missing parameter $3 in n-ary property $2 in $1.',

	'smw_gard_issue_domain_not_range' => 'Domain of $1 does not match range of inverse property $2.',
	'smw_gardissue_wrong_target_value' => '$1 may not use property $2 with the value $3, according to property definition.',
	'smw_gardissue_wrong_domain_value' => '$1 may not use property $2, according to property definition.',
	'smw_gardissue_too_low_card' => 'Please add $3 more annotation(s) of $2 (or its subproperties) to $1.',
    'smw_gardissue_missing_annotations' => 'Missing annotations. Please add $3 more annotation(s) of $2 (or its subproperties) to $1.',
	'smw_gardissue_too_high_card' => 'Please remove $3 annotation(s) of $2 (or its subproperties) from $1.',
	'smw_gardissue_wrong_unit' => 'Please correct wrong unit $3 for property $2 in $1.',
	'smw_gard_issue_incompatible_entity' => 'The entity $1 is incompatible to $2. Please check that they are in the same namespace.',
	'smw_gard_issue_incompatible_type' => 'The property $1 has an incompatible datatype to $2. They should be the same but are different.',
	'smw_gard_issue_incompatible_supertypes' => 'The property $1 has superproperties with different datatypes. Please check that they are the same.',

	'smw_gard_issue_cycle' => 'Cycle at: $1',
	'smw_gard_issue_contains_further_problems' => 'Contains further problems below',

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
	'smw_gard_remove_undefined_categories' => 'Remove annotations of undefined categories',

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
	'smw_gardissue_notannotated_page' => '$1 has no annotations',

	/* Anomalies */
	'smw_gard_anomaly_checknumbersubcat' => 'Check number of sub categories',
	'smw_gard_anomaly_checkcatleaves' => 'Check for category leafs',
	'smw_gard_anomaly_restrictcat' => 'Restrict to categories (separated by ;)',
	'smw_gard_anomaly_deletecatleaves' => 'Delete category leaves',
	'smw_gard_anomaly_docu' => 'This bot identifies  Category leafs (Categories that contain neither subcategories nor instances) and Subcategory number anomalies (Categories with only one or more than eight subcategories).',
	'smw_gard_anomalylog' => 'The anomaly bot removed the following pages',


	'smw_gard_all_category_leaves_deleted' => 'All Category leaves were removed.',
	'smw_gard_was_leaf_of' => 'was leaf of',
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

	/*Message for ExportOntologyBot*/
	'smw_exportontologybot' => 'Export ontology',	
	'smw_gard_export_docu' => 'This bot exports the wiki ontology in the OWL format.',
	'smw_gard_export_enterpath' => 'Export file/path',
	'smw_gard_export_onlyschema' => 'Export only schema',
	'smw_gard_export_ns' => 'Export to namespace',
	'smw_gard_export_download' => 'Export was successful! Click $1 to download the wiki export as OWL file.',
	'smw_gard_export_here' => 'here',

	/*Message for TemplateMateriazerBot*/
	'smw_gard_templatemat_docu' => 'This bot updates the wikipages which use templates that got new annotations to includes these annotations in the results of an ASK query.',
	'smw_gard_templatemat_applytotouched' => 'Apply only to touched templates',
	'smw_gardissue_updatearticle' => 'Article $1 was newly parsed.',

	/* Messages for the TermImportBot */
	'smw_gard_termimportbothelp' => 'This bot imports an external vocabulary.',

    'smw_checkrefintegritybot' => "Check referential integrity",
    'smw_gard_checkrefint_docu' => "This bot checks the integrity of links to external resources.",
    'smw_gardissue_resource_not_exists' => "<a href=\"$1\">$2</a> does not exist.",
    'smw_gardissue_resource_moved_permanantly' => "<a href=\"$1\">$2</a> was moved permanantly.",
    'smw_gardissue_resource_not_accessible' => "<a href=\"$1\">$2</a> is not accessible for some reason.",
    'smw_gardissue_thisresource' => "This resource",

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
	'smw_qi_ask' => '&lt;ask&gt; syntax',
	'smw_qi_parserask' => '{{#ask syntax',

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

	/* Annotation */
 	'smw_annotation_tab' => 'annotate',
	'smw_annotating'     => 'Annotating $1',
	'annotatethispage'   => 'Annotate this page',


	/* Refactor preview */
 	'refactorstatistics' => 'Refactor Statistics',
 	'smw_ob_link_stats' => 'Open refactor statistics',

	/* SMWFindWork */
 	'findwork' => 'Find work',
 	'smw_findwork_docu' => 'This page suggests articles that are somehow problematic but which you might enjoy editing/correcting.',
 	'smw_findwork_user_not_loggedin' => 'You are NOT logged in. It\'s possible to use the page anonymously, but it is far better to be logged in.', 	
 	'smw_findwork_header' => 'The suggested articles will reflect your interests based on your edit history. If you don\'t know what to choose, click on $1. The system will then select something for you.<br /><br />If you want to work on a more specific issue you can choose one of the following: ',
 	'smw_findwork_rateannotations' => '<h2>Evaluate annotations</h2>You can help to enhance the quality of this wiki by evaluating the quality of the following statements. Are the following statements correct?<br><br>',
 	'smw_findwork_yes' => 'Correct.',
 	'smw_findwork_no' => 'Wrong.',
 	'smw_findwork_dontknow' => 'Don\'t know.',
 	'smw_findwork_sendratings' => 'Send evaluations',
 	'smw_findwork_getsomework' => 'Give me some Work!',
 	'smw_findwork_show_details' => 'Show details',
 	'smw_findwork_heresomework' => 'Here\'s some work',

 	'smw_findwork_select' => 'Select',
 	'smw_findwork_generalconsistencyissues' => 'General consistency problems',
 	'smw_findwork_missingannotations' => 'Missing Annotations',
 	'smw_findwork_nodomainandrange' => 'Properties without type/domain',
 	'smw_findwork_instwithoutcat' => 'Instances without category',
 	'smw_findwork_categoryleaf' => 'Category leafs',
 	'smw_findwork_subcategoryanomaly' => 'Subcategory anomalies',
 	'smw_findwork_undefinedcategory' => 'Undefined categories',
 	'smw_findwork_undefinedproperty' => 'Undefined properties',
 	'smw_findwork_lowratedannotations' => 'Pages with low rated annotations',


	/* Gardening Issue Highlighting in Inline Queries */
	'smw_iqgi_missing' => 'missing',
	'smw_iqgi_wrongunit' => 'wrong unit',

	
	/* Messages of the Thesaurus Import */
	'smw_ti_welcome' => 'Please select a transport layer module (TLM) and then an data access module (DAM):',
	'smw_ti_selectDAM' => 'Please select a DAM',
	'smw_ti_firstselectTLM' => 'first select TLM',
	'smw_ti_selectImport' => 'Please choose one of the available import sets:&nbsp;&nbsp;',
	'smw_ti_inputpolicy' => 'With the input policy, you can define which information should be importet, using regular expressions. 
							For example, use "Em*" in order to import only data sets that start with "Em". 
							If no input policy is chosen, all data will be imported.',
	'smw_ti_define_inputpolicy' => 'Please define an input policy:',
	'smw_ti_mappingPage' => 'Please enter the name of the article that does contain the mapping policies:',
	'smw_ti_viewMappingPage' => 'View',
	'smw_ti_editMappingPage' => 'Edit',
	'smw_ti_conflictpolicy' => 'Please define a conflict policy. The conflict policy defines what happens if articles are imported that already exist in this wiki:',
		
	'smw_ti_succ_connected' => 'Successfully connected to "$1".',
	'smw_ti_class_not_found' => 'Class "$1" not found.',
	'smw_ti_no_tl_module_spec' => 'Could not find specification for TL module with ID "$1".',
	'smw_ti_xml_error' => 'XML error: $1 at line $2',
	'smw_ti_filename'  => 'Filename:',
	'smw_ti_fileerror' => 'The file "$1" does not exist or is empty.',
	'smw_ti_no_article_names' => 'There are no article names in the specified data source.',
	'smw_ti_termimport' => 'Import vocabulary',
	'termimport' => 'Import vocabulary',
	'smw_ti_botstarted' => 'The bot for the import of vocabulary was successfully started.',
	'smw_ti_botnotstarted' => 'The bot for the import of vocabulary could not be started.',
	'smw_ti_couldnotwritesettings' => 'Could not write the settings for the vocabulary import bot.',
	'smw_ti_missing_articlename' => 'An article can not be created as the "articleName" is missing in the term\'s description.',
	'smw_ti_invalid_articlename' => 'The article name "$1" is invalid.',
	'smw_ti_articleNotUpdated' => 'The existing article "$1" was not overwritten with a new version.',
	'smw_ti_creationComment' => 'This article was created/updated by the vocabulary import framework.',
	'smw_ti_creationFailed' => 'The article "$1" could not be created or updated.',
	'smw_ti_missing_mp' => 'The mapping policy is missing.',
	'smw_ti_import_error' => 'Import error',
	'smw_ti_added_article' => '$1 was added to the wiki.',
	'smw_ti_updated_article' => '$1 was updated.',
	'smw_ti_import_errors' => 'Some terms were not properly imported. Please see the Gardening Log!',
	'smw_ti_import_successful' => 'All terms were successfully imported.',

	'smw_gardissue_ti_class_added_article' => 'Imported articles',
	'smw_gardissue_ti_class_updated_article' => 'Updated articles',
	'smw_gardissue_ti_class_system_error' => 'Import system errors',
	'smw_gardissue_ti_class_update_skipped' => 'Skipped updates',

	/* Messages for the wiki web services */
	'smw_wws_articles_header' => 'Pages using the web service "$1"',
	'smw_wws_properties_header' => 'Properties that are set by "$1"',
	'smw_wws_articlecount' => '<p>Showing $1 pages using this web service.</p>',
	'smw_wws_propertyarticlecount' => '<p>Showing $1 properties that get their value from this web service.</p>',
	'smw_wws_invalid_wwsd' => 'The Wiki Web Service Definition is invalid or does not exist.',
	'smw_wws_wwsd_element_missing' => 'The element "$1" is missing in the Wiki Web Service Definition.',
	'smw_wws_wwsd_attribute_missing' => 'The attribute "$1" is missing in element "$2" of the Wiki Web Service Definition.',
	'smw_wws_too_many_wwsd_elements' => 'The element "$1" appears several times in the Wiki Web Service Definition.',
	'smw_wws_wwsd_needs_namespace' => 'Please note: Wiki web service definitions are only considered in articles with namespace "WebService"!',
	'smw_wws_wwsd_errors' => 'The Wiki Web Service Definition is erroneous:',
	'smw_wws_invalid_protocol' => 'The protocol specified in the Wiki Web Service Definition is not supported.',
	'smw_wws_invalid_operation' => 'The operation "$1" is not provided by the web service.',
	'smw_wws_parameter_without_name' => 'A parameter of the Wiki Web Service Definition has no name.',
	'smw_wws_parameter_without_path' => 'The attribute "path" of the parameter "$1" is missing.',
	'smw_wws_duplicate_parameter' => 'The parameter "$1" appears several times.',
	'smw_wwsd_undefined_param' => 'The operation needs the parameter "$1". Please define an alias.',
	'smw_wwsd_obsolete_param' => 'The parameter "$1" is defined but not used by the operation. You can remove it.',
	'smw_wwsd_overflow' => 'The structure "$1" may be continued endlessly. Parameters of this type are not supported by the Wiki Web Service Extension.',
	'smw_wws_result_without_name' => 'A result of the Wiki Web Service Definition has no name.',
	'smw_wws_result_part_without_name' => 'The result "$1" contains a part without name.',
	'smw_wws_result_part_without_path' => 'The attribute "path" of the part "$1" of the result "$2" is missing.',
	'smw_wws_duplicate_result_part' => 'The part "$1" appears several times in the result "$2".',
	'smw_wws_duplicate_result' => 'The result "$1" appears several times.',
	'smw_wwsd_undefined_result' => 'The path of the result "$1" can not be found in the result of the service.',
	'smw_wsuse_wrong_parameter' => 'The parameter "$1" does not exist in the Wiki Web Service Definition.',
	'smw_wsuse_parameter_missing' => 'The parameter "$1" is not optional and no default value was provided by the Wiki Web Service Definition.',
	'smw_wsuse_wrong_resultpart' => 'The result-part "$1" does not exist in the Wiki Web Service Definition.',
	'smw_wsuse_wwsd_not_existing' => 'A Wiki Web Service Definition with the name "$1" does not exist.',
	'smw_wsuse_wwsd_error' => 'The usage of the Web Service was erroneous:',
	'smw_wsuse_getresult_error' => 'An error occured while calling the WebService.',
	'smw_wsuse_old_cacheentry' => ' Therefore an outdated cache entry is used instead of a current WebService result.',
	'smw_wsuse_prop_error' => 'It is not allowed to use more than one result part as a value for a property',
	'webservicerepository' => 'WebService Repository',
	'smw_wws_select_without_object' => 'The attribute "object" of the select "$1" of the result "$2" is missing.',
	'smw_wws_select_without_value' => 'The attribute "value" of the select "$1" of the result "$2" is missing.',
	'smw_wws_duplicate_select' => 'The select "$1" appears several times in the result "$2".',
	'smw_wws_need_confirmation' => 'The WWSD for this WebService has to be confirmed by an administrator before it can be used again',
	'definewebservice' => 'Define WebService',
	'smw_wws_s1-help' => 'Please enter the URI of a WebService. The URI must direct towards the WSDL (Web Service Description Language) file of the WebService. If you do not have an URI yet, you can search for different WebService providers on the internet. Please take a look at Web Service Search Engines in order to find what you are looking for.',
	'smw_wws_s2-help' => 'Each WebService may provide different methods which in turn deliver different types of data. The WebService support in this wiki does allow to collect results from only one method of the WebService. If you would like to gather results from different methods of the same WebService, you have to create one WebService definition for each method.',
	'smw_wws_s3-help' => 'todo: write help message',
	'smw_wws_s4-help' => 'todo: write help message',
	'smw_wws_s5-help' => 'The update policies define after which a value delivered by a WebService will be updated. The display policy will be relevant whenever a value is displayed in an article, whereas the query policy is only relevant for semantic queries. The delay value allows to give a delay (in seconds) that has to be considered between two calls to a WebService.',
	'smw_wws_s6-help' => 'In order to use this WebService you need to give it a name. Please use a meaningful name that makes it easy for other users to recognize the purpose of this WebService.',
	'smw_wws_s1-menue' => '1. Specify URI',
	'smw_wws_s2-menue' => '2. Select Method',
	'smw_wws_s3-menue' => '3. Define Parameters',
	'smw_wws_s4-menue' => '4. Define Aliases',
	'smw_wws_s5-menue' => '5. Define Update Policy',
	'smw_wws_s6-menue' => '6. Choose Name',
	'smw_wws_s1-intro' => '1. Please enter the URI of the WebService: ',
	'smw_wws_s2-intro' => '2. Please select one of the following methods provided by the WebService: ',
	'smw_wws_s3-intro' => '3. The method asks for the following parameters.',
	'smw_wws_s4-intro' => '4. Please provide an alias for each result that is delivered by the WebService. This alias will be used whenever you include this WebService. Please use short but distinctive aliases or use the generate function.',
	'smw_wws_s5-intro' => '5. Please define the update policies for this WebService.',
	'smw_wws_s6-intro' => '6. Please enter a name for this WebService: ',
	'smw_wws_s7-intro-pt1' => 'Your WebService ',
	'smw_wws_s7-intro-pt2' => ' has been successfully created. In order to include this WebService into a page, please use the following syntax:',
	'smw_wws_s7-intro-pt3' => 'Your WebService will from now on be available in the list of available WebServices.',
	'smw_wws_s1-error' => 'It was not possible to connect to the specified URI. Please change the URI or try again.',
	'smw_wws_s2a-error' => 'It was not possible to connect to the specified URI. Please change the URI or try again.',
	'smw_wws_s2b-error' => 'The parameter definition of this method contains a recursive definition. Please choose another WebService or another method',
	'smw_wws_s3-error' => 'It was not possible to connect to the specified URI. Please change the URI or try again.',
	'smw_wws_s4-error' => 'todo: write error message',
	'smw_wws_s5-error' => 'todo: write error message',
	'smw_wws_s6-error' => 'You have to specify a name for the WWSD before it is possible to proceed.',
	'smw_wws_s6-error2' => 'An article with the given name allready exists. Please choose another name and try again.',
	'smw_wscachebot' => 'Clean the WebService-Cache',
	'smw_ws_cachebothelp' => 'This bot removes cached WebService results, that are outdated.',
	'smw_ws_cachbot_log' => 'Outdated cache entries for this WebService were removed.',
	'smw_wsupdatebot' => 'Update the WebService-cache',
	'smw_ws_updatebothelp' => 'This bot updates the WebService-cache.',
	'smw_ws_updatebot_log' => 'Cache entries for this WebService were updated.',
	'smw_ws_updatebot_callerror' => 'An error occured while updating the cache entries of this WebService',
	'smw_ws_updatebot_confirmation' => 'It was not possible to update results of this WebService due to a missing confirmation of the WWSD',
	
    'smw_deletepage_nolinks' => 'There are no links to this page!',
    'smw_deletepage_linkstopage'=> 'Pages with links to that page',
    'smw_deletepage_prev' => 'Prev',
    'smw_deletepage_next' => 'Next',

	// Glossary
	'smw_gloss_no_description' => '"$1" is annotated as glossary term but has no description yet.',
	'smw_gard_glossarybothelp' => 'This bot highlights all terms that belong to the glossary in all articles that belong to the category "ShowGlossary".',
	'smw_glossarybot' => 'Update glossary highlighting',
	'smw_gloss_annotated_glossary' => 'Glossary terms found and annotated in article "$1".',
	
	

	

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
	SMW_SSP_IS_EQUAL_TO => 'Is equal to',
	SMW_SSP_GLOSSARY => 'glossary'
	);

	var $smwSpecialCategories = array (
	SMW_SC_TRANSITIVE_RELATIONS => 'Transitive properties',
	SMW_SC_SYMMETRICAL_RELATIONS => 'Symmetrical properties'
	);

	var $smwHaloDatatypes = array(
	'smw_hdt_chemical_formula' => 'Chemical formula',
	'smw_hdt_chemical_equation' => 'Chemical equation',
	'smw_hdt_mathematical_equation' => 'Mathematical equation',
	'smw_hdt_glossary' => 'Glossary Term',
	);

	protected $smwHaloNamespaces = array(
	SMW_NS_WEB_SERVICE       => 'WebService',
	SMW_NS_WEB_SERVICE_TALK  => 'WebService_talk'
	);

	protected $smwHaloNamespaceAliases = array(
	'WebService'       => SMW_NS_WEB_SERVICE,
	'WebService_talk'  => SMW_NS_WEB_SERVICE_TALK 
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


