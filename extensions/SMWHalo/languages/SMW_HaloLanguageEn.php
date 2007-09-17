<?php
/**
 * @author Markus Krötzsch
 */

global $smwgHaloIP;
include_once($smwgHaloIP . '/languages/SMW_HaloLanguage.php');

class SMW_HaloLanguageEn extends SMW_HaloLanguage {

protected $smwContentMessages = array(
	'smw_edithelp' => 'Editing help on relations and attributes',
	'smw_helppage' => 'Relation',
	'smw_viewasrdf' => 'RDF feed',
	'smw_viewinOB' => 'Open in OntologyBrowser',
	'smw_finallistconjunct' => ', and', //used in "A, B, and C"
	'smw_factbox_head' => 'Facts about $1',
	'smw_att_head' => 'Attribute values',
	'smw_rel_head' => 'Relations to other pages',
	'smw_spec_head' => 'Special properties',
	'smw_predefined_props' => 'This is the predefined property "$1"',
	'smw_predefined_cats' => 'This is the predefined category "$1"',
	// URIs that should not be used in objects in cases where users can provide URIs
	'smw_uri_blacklist' => " http://www.w3.org/1999/02/22-rdf-syntax-ns#\n http://www.w3.org/2000/01/rdf-schema#\n http://www.w3.org/2002/07/owl#",
	'smw_baduri' => 'Sorry, URIs from the range "$1" are not available in this place.',
	// Messages and strings for inline queries
	'smw_iq_disabled' => "<span class='smwwarning'>Sorry. Inline queries have been disabled for this wiki.</span>",
	'smw_iq_moreresults' => '&hellip; further results',
	'smw_iq_nojs' => 'Use a JavaScript-enabled browser to view this element, or directly <a href="$1">browse the result list</a>.',
	// Messages and strings for ontology resued (import)
	'smw_unknown_importns' => 'Import functions are not avalable for namespace "$1".',
	'smw_nonright_importtype' => '$1 can only be used for pages with namespace "$2".',
	'smw_wrong_importtype' => '$1 can not be used for pages in the namespace "$2".',
	'smw_no_importelement' => 'Element "$1" not available for import.',
	// Messages and strings for basic datatype processing
	'smw_decseparator' => '.',
	'smw_kiloseparator' => ',',
	'smw_unknowntype' => 'Unsupported type "$1" defined for attribute.',
	'smw_noattribspecial' => 'Special property "$1" is not an attribute (use "::" instead of ":=").',
	'smw_notype' => 'No type defined for attribute.',
	'smw_manytypes' => 'More than one type defined for attribute.',
	'smw_emptystring' => 'Empty strings are not accepted.',
	'smw_maxstring' => 'String representation $1 is too long for this site.',
	'smw_nopossiblevalues' => 'Possible values for this attribute are not enumerated.',
	'smw_notinenum' => '"$1" is not in the list of possible values ($2) for this attribute.',
	'smw_noboolean' => '"$1" is not recognized as a boolean (true/false) value.',
	'smw_true_words' => 't,yes,y',	// comma-separated synonyms for boolean TRUE besides 'true' and '1'
	'smw_false_words' => 'f,no,n',	// comma-separated synonyms for boolean FALSE besides 'false' and '0'
	'smw_nointeger' => '"$1" is no integer number.',
	'smw_nofloat' => '"$1" is no floating point number.',
	'smw_infinite' => 'Numbers as long as "$1" are not supported on this site.',
	'smw_infinite_unit' => 'Conversion into unit "$1" resulted in a number that is too long for this site.',
	// Currently unused, floats silently store units.  'smw_unexpectedunit' => 'this attribute supports no unit conversion',
	'smw_unsupportedprefix' => 'Prefixes for numbers ("$1") are not supported.',
	'smw_unsupportedunit' => 'Unit conversion for unit "$1" not supported.',
	// Messages for geo coordinates parsing
	'smw_err_latitude' => 'Values for latitude (N, S) must be within 0 and 90, and "$1" does not fulfill this condition.',
	'smw_err_longitude' => 'Values for longitude (E, W) must be within 0 and 180, and "$1" does not fulfill this condition.',
	'smw_err_noDirection' => 'Something is wrong with the given value "$1".',
	'smw_err_parsingLatLong' => 'Something is wrong with the given value "$1" &ndash; we expect a value like "1°2′3.4′′ W" at this place.',
	'smw_err_wrongSyntax' => 'Something is wrong with the given value "$1" &ndash; we expect a value like "1°2′3.4′′ W, 5°6′7.8′′ N" at this place.',
	'smw_err_sepSyntax' => 'The given value "$1" seems to be right, but values for latitude and longitude should be seperated by "," or ";".',
	'smw_err_notBothGiven' => 'Please specify a valid value for both longitude (E, W) <it>and</it> latitude (N, S) &ndash; at least one is missing.',
	// additionals ...
	'smw_label_latitude' => 'Latitude:',
	'smw_label_longitude' => 'Longitude:',
	'smw_abb_north' => 'N',
	'smw_abb_east' => 'E',
	'smw_abb_south' => 'S',
	'smw_abb_west' => 'W',
	// some links for online maps; can be translated to different language versions of services, but need not
	'smw_service_online_maps' => " find&nbsp;maps|http://tools.wikimedia.de/~magnus/geo/geohack.php?params=\$9_\$7_\$10_\$8\n Google&nbsp;maps|http://maps.google.com/maps?ll=\$11\$9,\$12\$10&spn=0.1,0.1&t=k\n Mapquest|http://www.mapquest.com/maps/map.adp?searchtype=address&formtype=latlong&latlongtype=degrees&latdeg=\$11\$1&latmin=\$3&latsec=\$5&longdeg=\$12\$2&longmin=\$4&longsec=\$6&zoom=6",
	// Messages for datetime parsing
	'smw_nodatetime' => 'The date "$1" was not understood (support for dates is still experimental).',
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
	'smw_type_header' => 'Attributes of type "$1"',
	'smw_typearticlecount' => 'Showing $1 attributes using this type.',
	'smw_attribute_header' => 'Pages using the property "$1"',
	'smw_attributearticlecount' => '<p>Showing $1 pages using this property.</p>',
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

	// Messages for Export RDF Special
	'exportrdf' => 'Export pages to RDF', //name of this special
	'smw_exportrdf_docu' => '<p>This page allows you to obtain data from a page in RDF format. To export pages, enter the titles in the text box below, one title per line.</p>',
	'smw_exportrdf_recursive' => 'Recursively export all related pages. Note that the result could be large!',
	'smw_exportrdf_backlinks' => 'Also export all pages that refer to the exported pages. Generates browsable RDF.',
	'smw_exportrdf_all' => 'Export all semantic data',
	'smw_exportrdf_lastdate' => 'Date of last export. Pages not changed since that time will be filtered out',	
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
	// Messages for Properties Special
	'properties' => 'Properties',
	'smw_properties_docu' => 'The following properties are used in the wiki.',
	'smw_property_template' => '$1 of type $2 ($3)', // <propname> of type <type> (<count>)
	'smw_propertylackspage' => 'All properties should be described by a page!',
	'smw_propertylackstype' => 'No type was specified for this property (assuming type $1 for now).',
	'smw_propertyhardlyused' => 'This property is hardly used within the wiki!',
	// Messages for Unused Properties Special
	'unusedproperties' => 'Unused Properties',
	'smw_unusedproperties_docu' => 'The following properties exist although no other page makes use of them.',
	'smw_unusedproperty_template' => '$1 of type $2', // <propname> of type <type>
	// Messages for Wanted Properties Special
	'wantedproperties' => 'Wanted Properties',
	'smw_wantedproperties_docu' => 'The following properties are used in the wiki but do not yet have a page for describing them.',
	'smw_wantedproperty_template' => '$1 ($2 uses)', // <propname> (<count> uses)
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
	// Messages for the refresh button
	'tooltip-purge' => 'Click here to refresh all queries and templates on this page',
	'purge' => 'Refresh',
	// Messages for Import Ontology Special
	'ontologyimport' => 'Import ontology',
	'smw_oi_docu' => 'This special page allows to import ontologies. The ontologies have to follow a certain format, specified at the <a href="http://wiki.ontoworld.org/index.php/Help:Ontology_import">ontology import help page</a>.',
	'smw_oi_action' => 'Import',
	'smw_oi_test' => 'Test',
	'smw_oi_import' => 'Import',
	'smw_oi_return' => 'Return to <a href="$1">Special:OntologyImport</a>.',
	'smw_oi_noontology' => 'No ontology supplied, or could not load ontology.',
	'smw_oi_select' => 'Please select the statements to import, and then click the import button.',
	'smw_oi_textforall' => 'Header text to add to all imports (may be empty):',
	'smw_oi_selectall' => 'Select or unselect all statements',
	'smw_oi_statementsabout' => 'Statements about',
	'smw_oi_mapto' => 'Map entity to',
	'smw_oi_comment' => 'Add the following text:',
	'smw_oi_thisissubcategoryof' => 'A subcategory of',
	'smw_oi_thishascategory' => 'Is part of',
	'smw_oi_importedfromontology' => 'Import from ontology',
	// Messages for (data)Types Special
	'types' => 'Types',
	'smw_types_docu' => 'The following is a list of all datatypes that can be assigned to attributes. Each datatype has a page where additional information can be provided.',
	'smw_types_units' => 'Standard unit: $1; supported units: $2',
	'smw_types_builtin' => 'Built-in types',
	/*Messages for ExtendedStatistics Special*/
	'extendedstatistics' => 'Extended Statistics',
	'smw_extstats_general' => 'General Statistics',
	'smw_extstats_totalp' => 'Total number of pages:',
	'smw_extstats_totalv' => 'Total number of views:',
	'smw_extstats_totalpe' => 'Total number of page edits:',
	'smw_extstats_totali' => 'Total number of images:',
	'smw_extstats_totalu' => 'Total number of users:',
	'smw_extstats_totalc' => 'Total number of categories:',
	'smw_extstats_totalci' => 'Total number of instances with at least one category:',
	'smw_extstats_avginstancecat' => 'Average number of instances per category<br /> (instances can have more than one category):',
	'smw_extstats_totalr' => 'Total number of relations:',
	'smw_extstats_totalri' => 'Total number of relation instances:',
	'smw_extstats_totalra' => 'Average number of instances per relation:',
	'smw_extstats_totalpr' => 'Total number of pages about relations:',
	'smw_extstats_totala' => 'Total number of attributes:',
	'smw_extstats_totalai' => 'Total number of attribute instances:',
	'smw_extstats_totalaa' => 'Average number of instances per attribute:',
	/*Messages for Flawed Attributes Special --disabled--*/
	'flawedattributes' => 'Flawed Attributes',
	'smw_fattributes' => 'The pages listed below have an incorrectly defined attribute. The number of incorrect attributes is given in the brackets.',
	// Name of the URI Resolver Special (no content)
	'uriresolver' => 'URI Resolver',
	'smw_uri_doc' => '<p>The URI resolver implements the <a href="http://www.w3.org/2001/tag/issues.html#httpRange-14">W3C TAG finding on httpRange-14</a>. It takes care that humans don\'t turn into websites.</p>',
	// Messages for ask Special
	'ask' => 'Semantic search',
	'smw_ask_docu' => '<p>Search by entering a query into the search field below. Further information is given on the <a href="$1">help page for semantic search</a>.</p>',
	'smw_ask_doculink' => 'Semantic search',
	'smw_ask_sortby' => 'Sort by column',
	'smw_ask_ascorder' => 'Ascending',
	'smw_ask_descorder' => 'Descending',
	'smw_ask_submit' => 'Find results',
	// Messages for the search by property special
	'searchbyproperty' => 'Search by property',
	'smw_sbv_docu' => '<p>Search for all pages that have a given property and value.</p>',
	'smw_sbv_noproperty' => '<p>Please enter a property.</p>',
	'smw_sbv_novalue' => '<p>Please enter a valid value for the property, or view all property values for "$1."</p>',
	'smw_sbv_displayresult' => 'A list of all pages that have property "$1" with value "$2"',
	'smw_sbv_property' => 'Property',
	'smw_sbv_value' => 'Value',
	'smw_sbv_submit' => 'Find results',
	// Messages for the browsing special
	'browse' => 'Browse wiki',
	'smw_browse_article' => 'Enter the name of the page to start browsing from.',
	'smw_browse_go' => 'Go',
	'smw_browse_more' => '&hellip;',
	// Messages for the page property special
	'pageproperty' => 'Page property search',
	'smw_pp_docu' => 'Search for all the fillers of a property on a given page. Please enter both a page and a property.',
	'smw_pp_from' => 'From page',
	'smw_pp_type' => 'Property',
	'smw_pp_submit' => 'Find results',
	// Generic messages for result navigation in all kinds of search pages
	'smw_result_prev' => 'Previous',
	'smw_result_next' => 'Next',
	'smw_result_results' => 'Results',
	'smw_result_noresults' => 'Sorry, no results.',
/*Messages for OntologyBrowser*/
	'ontologybrowser' => 'OntologyBrowser',
	'smw_ac_hint' => 'Press Ctrl+Alt+Space to use auto-completion. (Ctrl+Space in IE)',
	'smw_ob_categoryTree' => 'Category Tree',
	'smw_ob_attributeTree' => 'Property Tree',
	
	'smw_ob_instanceList' => 'Instances',
	'smw_ob_rel' => 'Relations',
	'smw_ob_att' => 'Attributes',
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
	/* Messages for Gardening */
	'gardening' => 'Gardening',
	'smw_gardening' => 'Gardening',
	'smw_gardening_log' => 'GardeningLog',
	'smw_gardening_log_exp' => 'This is the Gardening log category',
	'smw_gard_welcome' => 'This is the Gardening toolbox.It provides some tools which helps to keep the wiki content clean and consistent.',
	'smw_gard_notools' => 'If you do not see any tools here, you might not be logged in or you do not have any rights to use gardening tools.',
	'smw_no_gard_log' => 'No Gardening log',
	'smw_gard_abortbot' => 'Cancel Bot',
	'smw_gard_unknown_bot' => 'Unknown Bot',
	'smw_gard_no_permission' => 'You do not have the permission to use this bot.',
	'smw_gard_missing_parameter' => 'Missing parameter',
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

	/* Messages for Gardening Bot: ConsistencyBot */
	'smw_gard_consistency_docu'  => 'The consistency bot checks for cycles in the taxonomy and properties without domain and range. It also checks the correct usage of properties according to domain and range information as well as cardinality errors.',
	'smw_gard_domains_not_covariant' => 'Domain of [[$2:$1]] must be a subcategory of the domains of its super property.',
	'smw_gard_domain_not_defined' => 'Domain of [[$2:$1]] is not defined.',
	'smw_gard_ranges_not_covariant' => 'Range of [[$2:$1]] must be a subcategory of the ranges of its super property.',
	'smw_gard_range_not_defined' => 'Range of [[$2:$1]] is not defined.',
	'smw_gard_types_not_covariant' => 'Type of [[$2:$1]] must be equal to type of its superproperty.',
	'smw_gard_types_is_not_defined' => 'Type of [[$2:$1]] is not defined.',
	'smw_gard_more_than_one_type' => 'More than one type defined.',
	'smw_gard_mincard_not_covariant' => 'Minimum cardinality of [[$2:$1]] is lower than at super property of it.',
	'smw_gard_maxcard_not_covariant' => 'Maximum cardinality of [[$2:$1]] is higher than at super property of it.',
	'smw_gard_maxcard_not_null' => 'Maximum cardinality of [[$2:$1]] must not be 0.',
	'smw_gard_mincard_not_below_null' => 'Minimum cardinality of [[$2:$1]] must not be below 0.',
	'smw_gard_symetry_not_covariant' => 'Property [[$2:$1]] must also be symetrical',
	'smw_gard_trans_not_covariant' => 'Property [[$2:$1]] must also be transitive',
	'smw_gard_doublemaxcard' => 'Warning: More than one max cardinality at property [[$3:$1]] detected. Taking first value, which is $2.',
	'smw_gard_doublemincard' => 'Warning: More than one min cardinality at property [[$3:$1]] detected. Taking first value, which is $2.',
	'smw_gard_wrongcardvalue' => 'Warning: Cardinality for [[$2:$1]] has wrong value. Must be a positive integer or *. Will be interpreted as 0.',
	'smw_gard_missing_param' => 'Warning: Missing parameter $4 in n-ary property [[$3:$2]] in article [[$1]].',

	'smw_gard_domain_is_not_range' => 'Domain of [[$2:$1]] does not match range of inverse property [[$2:$2]].',
	'smw_gard_wrong_target' => 'Target [[$1]] is member of wrong range category when using the property [[$3:$2]].',
	'smw_gard_wrong_domain' => 'Article [[$1]] has wrong domain category when using the property [[$3:$2]].',
	'smw_gard_incorrect_cardinality' => 'Article [[$1]] uses property [[$3:$2]] too often or too less.',
	'smw_gard_no_errors' => 'Congratulations! The wiki is consistent.',
	'smw_gard_incomp_entities_equal' => 'The entity [[$2:$1]] is incompatible to [[$4:$3]]',

	'smw_gard_errortype_categorygraph_contains_cycles' => 'The category graph contains cycles',
	'smw_gard_errortype_propertygraph_contains_cycles' => 'The property graph contains cycles',
	
	'smw_gard_errortype_relation_problems' => 'Property schema problems',
	'smw_gard_errortype_attribute_problems' => 'Property schema problems',
	'smw_gard_errortype_inconsistent_relation_annotations' => 'Inconsistent property annotations',
	'smw_gard_errortype_inconsistent_attribute_annotations' => 'Inconsistent property annotations',
	'smw_gard_errortype_inconsistent_cardinalities' => 'Inconsistent number of annotations',
	'smw_gard_errortype_inverse_relations' => 'Inconsistent inverse properties',
	'smw_gard_errortype_equality' => 'Inconsistent equalities',

	/* SimilarityBot*/
	'smw_gard_sharecategories' => 'They share the following categories',
	'smw_gard_sharedomains' => 'They share the following domain categories',
	'smw_gard_shareranges' => 'They share the following range categories',
	'smw_gard_disticntbycommonfix' => 'Distinct by common prefix or suffix',
	'smw_gard_sharetypes' => 'They share the following types',
	'smw_gard_issimilarto' => 'is similar to',
	'smw_gard_degreeofsimilarity' => 'Edit distance limit',
	'smw_gard_similarityscore' => 'Similarity Score (No. of similarities)',
	'smw_gard_limitofresults' => 'Number of results',
	'smw_gard_limitofsim' => 'Show only entities which appear to be',
	'smw_gard_similarityterm' => 'Search for entities similar to the following term (may be empty)',
	'smw_gard_similaritybothelp' => 'This is the bot to identify entities in the knowledgebase that could potentially be unified. If you enter a term, the system will try to find entities which are similar to that. If you do not enter a term, the system will find possibly redundant entities.',
	'smw_gard_similarannotation' => '$1 of article $2 may be intended as annotation of $3',
	
	/*Undefined entities bot */
	'smw_gard_undefinedentities_docu' => 'The undefined entities bot searches for categories and properties that are used within the wiki but not defined, as well as instances that have no category.',
	'smw_gard_property_undefined' => '[[$2:$1]] used on : $3',
	'smw_gard_category_undefined' => '[[:Category:$1]] used on: $2',
	'smw_gard_relationtarget_undefined' => '[[$1]] undefined when used with: $2',
	'smw_gard_instances_without_category' => '[[$1]]',

	'smw_gard_errortype_undefined_categories' => 'Undefined categories (remove annotation or define)',
	'smw_gard_errortype_undefined_properties' => 'Undefined properties (remove annotation or define)',
	'smw_gard_errortype_undefined_relationtargets' => 'Undefined property targets',
	'smw_gard_errortype_instances_without_category' => 'Instances without category (add them to a category)',

	/* Missing annotations */
	'smw_gard_missingannot_docu' => 'This bot identifies pages in the Wiki that are not annotated.',
	'smw_gard_missingannot_titlecontaining' => '(Optional) Only Pages with a title containing',
	'smw_gard_missingannot_restricttocategory' => 'Restrict to categories',
	
	/* Anomalies */
	'smw_gard_anomaly_checknumbersubcat' => 'Check number of sub categories',
	'smw_gard_anomaly_checkcatleaves' => 'Check for category leafs',
	'smw_gard_anomaly_restrictcat' => 'Restrict to categories (separated by ;)',
	'smw_gard_anomaly_deletecatleaves' => 'Delete category leaves',
	'smw_gard_anomaly_docu' => 'This bot identifies  Category leafs (Categories that contain neither subcategories nor instances) and Subcategory number anomalies (Categories with only one or more than eight subcategories).',
	'smw_gard_anomalylog' => 'The following is a list of current anomalies in the wiki:',
	'smw_gard_category_leafs' => 'Category leafs (Categories that contain neither subcategories nor instances)',
	'smw_gard_subcategory_number_anomalies' => 'Subcategory number anomalies (Categories with only one or more than eight subcategories)',
	'smw_gard_subcategory' => 'subcategory',
	'smw_gard_subcategories' => 'subcategories',
	'smw_gard_all_category_leaves_deleted' => 'All Category leaves were removed.',
	'smw_gard_category_leaves_deleted' => 'Category leaves below [[$1:$2]] were removed.',
	'smw_gard_category_leaf_deleted' => '$1 was a category leaf. Removed by anomaly bot.',

	/* Combined Search*/
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
	'smw_gard_import_locationowl' => 'Location of OWL file',
	
	/*Message for TemplateMaterializerBot*/
	'smw_gard_templatemat_docu' => 'This bot updates the wikipages which use templates that got new annotations to includes these annotations in the results of an ASK query.',
	'smw_gard_templatemat_applytotouched' => 'Apply only to touched templates',
	
	/*Messages for ContextSensitiveHelp*/
	'contextsensitivehelp' => 'Context Sensitive Help',
	'smw_contextsensitivehelp' => 'Context Sensitive Help',
	'smw_csh_newquestion' => 'This is a new help question. Click to answer it!',

	/*Messages for Query Interface*/
	'queryinterface' => 'Query Interface',
	'smw_queryinterface' => 'Query Interface'
);

protected $smwDatatypeLabels = array(
	'smw_wikipage' => 'Page', // name of page datatype
	'smw_string' => 'String',  // name of the string type
	'smw_text' => 'Text',  // name of the text type
	'smw_enum' => 'Enumeration',  // name of the enum type
	'smw_bool' => 'Boolean',  // name of the boolean type
	'smw_int' => 'Integer',  // name of the int type
	'smw_float' => 'Float',  // name of the floating point type
	'smw_length' => 'Length',  // name of the length type
	'smw_area' => 'Area',  // name of the area type
	'smw_geolength' => 'Geographic length',  // OBSOLETE name of the geolength type
	'smw_geoarea' => 'Geographic area',  // OBSOLETE name of the geoarea type
	'smw_geocoordinate' => 'Geographic coordinate', // name of the geocoord type
	'smw_mass' => 'Mass',  // name of the mass type
	'smw_time' => 'Time',  // name of the time (duration) type
	'smw_temperature' => 'Temperature',  // name of the temperature type
	'smw_datetime' => 'Date',  // name of the datetime (calendar) type
	'smw_email' => 'Email',  // name of the email (URI) type
	'smw_url' => 'URL',  // name of the URL type (string datatype property)
	'smw_uri' => 'URI',  // name of the URI type (object property)
	'smw_annouri' => 'Annotation URI',  // name of the annotation URI type (annotation property),
	'smw_chemicalformula' => 'Chemical formula', // name of the type for chemical formulas
	'smw_chemicalequation' => 'Chemical equation', // name of the type for chemical equations
	'smw_mathematicalequation' => 'Mathematical equation' // name of the type for mathematical equations
	
);

protected $smwSpecialProperties = array(
	//always start upper-case
	SMW_SP_HAS_TYPE  => 'Has type',
	SMW_SP_HAS_URI   => 'Equivalent URI',
	SMW_SP_SUBPROPERTY_OF => 'Subproperty of',
	SMW_SP_MAIN_DISPLAY_UNIT => 'Main display unit',
	SMW_SP_DISPLAY_UNIT => 'Display unit',
	SMW_SP_IMPORTED_FROM => 'Imported from',
	SMW_SP_CONVERSION_FACTOR => 'Corresponds to',
	SMW_SP_CONVERSION_FACTOR_SI => 'Corresponds to SI',
	SMW_SP_SERVICE_LINK => 'Provides service',
	SMW_SP_POSSIBLE_VALUE => 'Allows value'
);


var $smwSpecialSchemaProperties = array (
	SMW_SSP_HAS_DOMAIN_HINT => 'Has domain hint',
	SMW_SSP_HAS_RANGE_HINT  => 'Has range hint',
	SMW_SSP_HAS_MAX_CARD => 'Has max cardinality',
	SMW_SSP_HAS_MIN_CARD => 'Has min cardinality',
	SMW_SSP_IS_INVERSE_OF => 'Is inverse of',
	SMW_SSP_IS_EQUAL_TO => 'Is equal to'
	);

var $smwSpecialCategories = array (
		SMW_SC_TRANSITIVE_RELATIONS => 'Transitive relations',
		SMW_SC_SYMMETRICAL_RELATIONS => 'Symmetrical relations'
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


