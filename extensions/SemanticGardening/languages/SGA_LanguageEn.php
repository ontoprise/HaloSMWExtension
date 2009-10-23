<?php
/**
 * @author: Kai K�hn
 * 
 * Created on: 27.01.2009
 *
 */
class SGA_LanguageEn {
    
    public $contentMessages = array();
    
    public $userMessages = array (
       /* Messages for Gardening */
    'gardening' => 'Gardening', // name of special page 'Gardening'
    'gardeninglog' => 'GardeningLog', // name of special page 'GardeningLog'
    'smw_gard_param_replaceredirects' => 'Replace redirects',
    'smw_gardening_log_cat' => 'GardeningLog',
    'smw_gardeninglogs_docu' => 'This page provides access to logs created by the Gardening Bots. You can filter them in various ways.',
    'smw_gardening_log_exp' => 'This is the Gardening log category',
    'smw_gardeninglog_link' => 'Also take a look at $1 special page for further logs.',
    'smw_gard_welcome' => 'This is the Gardening toolbox.It provides some tools which helps to keep the wiki knowledgebase clean and consistent. <br/>To inspect the results of previous runs
of the tools click on this link: $1.',
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
    'smw_gard_ontology_id' => 'Ontology ID',

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
    'smw_gard_restricttocategory' => 'Restrict to categories (separated by comma)',
    'smw_gardissue_notannotated_page' => '$1 has no annotations',

    /* Anomalies */
    'smw_gard_anomaly_checknumbersubcat' => 'Check number of sub categories',
    'smw_gard_anomaly_checkcatleaves' => 'Check for category leafs',
    'smw_gard_anomaly_restrictcat' => 'Restrict to categories (separated by :)',
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
    
    /*Message for ImportOntologyBot*/
    'smw_gard_import_docu' => 'Imports an OWL file.',
    'smw_gard_import_uselabels' => 'Use labels',

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
    
    /*Message for Check referential integrity bot*/
      'smw_checkrefintegritybot' => "Check referential integrity",
    'smw_gard_checkrefint_docu' => "This bot checks the integrity of links to external resources.",
    'smw_gardissue_resource_not_exists' => '<a href=\"$1\">$2</a> does not exist.',
    'smw_gardissue_resource_moved_permanantly' => '<a href=\"$1\">$2</a> was moved permanantly.',
    'smw_gardissue_resource_not_accessible' => '<a href=\"$1\">$2</a> is not accessible for some reason.',
    'smw_gardissue_thisresource' => "This resource",
    
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
    'smw_findwork_undefinedproperty' => 'Undefined properties'
   
        
    );
}
