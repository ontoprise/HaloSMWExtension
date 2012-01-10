<?php
$messages = array();

$messages['en'] = array(
    'smw_refactoringbot' => 'Refactoring bot',
    'sref_start_operation' => 'Start refactoring',

    /*rename*/
    'sref_rename_instance' => 'Rename instance',
    'sref_rename_instance_help' => 'Renames the instance page itself.',
    'sref_rename_property' => 'Rename property',
    'sref_rename_property_help' => 'Renames the property page itself.',
    'sref_rename_category' => 'Rename category',
    'sref_rename_category_help' => 'Renames category page itself.',
    'sref_rename_annotations' => 'Rename all occurences',
    'sref_rename_annotations_help' => 'Rename all occurences in annotations and queries.',

    /*delete*/
    'sref_deleteCategory' => 'Delete category',
    'sref_deleteCategory_help' => 'Deletes the selected category article.',
    'sref_removeInstances' => 'Delete all instances',
    'sref_removeInstances_help' => 'Deletes all articles which are instances of the selected category but not articles of sub-categories.',
    'sref_removeCategoryAnnotations' => 'Remove all occurences of this category',
    'sref_removeCategoryAnnotations_help' => 'Removes all category annotations of the selected category but does not delete the article which it contains.',
    'sref_removePropertyWithDomain' => 'Delete all properties with this domain',
    'sref_removePropertyWithDomain_help' => 'Deletes all property articles which use the selected category as domain.',
    'sref_removeQueriesWithCategories' => 'Remove all queries containing this category',
    'sref_removeQueries_help' => 'Removes all queries which contain the selected category. Does not delete the article which contains the query.',
    'sref_includeSubcategories' => 'Include sub-categories',
    'sref_includeSubcategories_help' => 'Applies the selected operations to all sub-categories of the selected category. This happens recursively, ie. it includes the non-direct sub-categories',
    
    'sref_deleteProperty' => 'Delete property',
    'sref_deleteProperty_help' =>  'Deletes the selected property article.',
	'sref_removeInstancesUsingProperty' => 'Delete all instances using this property.',
    'sref_removeInstancesUsingProperty_help' =>  'Deletes all articles which use this property in any way. (e.g. as an annotation or in a query)',
	'sref_removePropertyAnnotations' => 'Remove all occurences of this property',
    'sref_removePropertyAnnotations_help' =>  'Removes all annotations of this property from all articles. Does not delete the articles.',
	'sref_removeQueriesWithProperties' => 'Remove all queries containing this category',
    'sref_removeQueriesWithProperties_help' =>  'Removes all queries which contain this property as constraint or printout. Does not delete the articles.',
    'sref_includeSubproperties' => 'Include sub-properties',
    'sref_includeSubproperties_help' =>  'Applies the selected operations to all sub-properties of the selected property. This happens recursively, ie. it includes the non-direct sub-properties',

    /* errors */
    'sref_not_allowed_botstart' => 'You are not allowed to start the refactoring bot.',
    'sref_no_sufficient_rights' => 'no sufficient rights',
	'sref_article_changed' => 'nothing done. article was changed in the meantime.',
	'sref_do_not_change_gardeninglog' => 'do not change a GardeningLog page',
	
	/* special pages */
    'srefrefactor' => 'Semantic Refactoring',
    'sref_specialrefactor_description' => 'Semantic Refactoring allows the user to manipulate large amounts of wiki annotations with one command. 
                                            This is, for example, necessary if you want to replace a property by another in all annotations where it appears. Another 
                                            example would be to remove all uses of a particular category from all pages. For a detailed overview of the possibilities, please
                                            take a look at $1.',
    'sref_enter_query' => 'Enter a query to select an instance set',
    'sref_run_query' => 'Run query',
    'sref_open_qi' => 'Open query interface',
    'sref_clear_query' => 'Clear',
    'sref_select_instanceset'=> 'Select instance set',
    'sref_choose_commands' => 'Choose command',
    'sref_running_operations' => 'Running operations',

    'sref_add' => 'add',
    'sref_remove' => 'remove',
    'sref_replace' => 'replace',
    'sref_setvalue' => 'set value',
    'sref_rename' => 'rename',

    'sref_category' => 'Category',
    'sref_old_category' => 'Old category', 
    'sref_new_category' => 'New category',
    'sref_annotationproperty' => 'Annotation/Property',
    'sref_property' => 'Property',
    'sref_template' => 'Template',
    'sref_parameter' => 'Parameter',
    'sref_old_parameter' => 'Old parameter',
    'sref_new_parameter' => 'New parameter',
    'sref_value' => 'Value',
    'sref_old_value' => 'Old value',
    'sref_new_value' => 'New value',

    'sref_comment' => 'Comment',
	'sref_starttime' => 'Start-time',
	'sref_endtime' => 'End-time',
	'sref_progress' => 'Progress',
	'sref_status' => 'Status',
    'sref_finished' => 'finished',
    'sref_running' => 'running',

    'sref_comment_addcategory' => 'Add category $1',
    'sref_comment_removecategory' => 'Remove category $1',
    'sref_comment_replacecategory' => 'Replace category $1 by $2'
);

/**
 * German (Deutsch)
 */
$messages['de'] = array(
    'smw_refactoringbot' => 'Refactoring bot',
    'sref_start_operation' => 'Starte Refactoring',
    
    /*rename*/
    'sref_rename_instance' => 'Rename instance',
    'sref_rename_instance_help' => 'Renames the instance page itself.',
    'rename_property' => 'Rename property',
    'rename_property_help' => 'Renames the property page itself.',
    'rename_category' => 'Rename category',
    'rename_category_help' => 'Renames category page itself.',
    'rename_annotations' => 'Rename all occurences',
    'rename_annotations_help' => 'Rename all occurences in annotations and queries.',

    /*delete*/
    'sref_deleteCategory' => 'Delete category',
    'sref_deleteCategory_help' => 'Deletes the selected category article.',
    'sref_removeInstances' => 'Delete all instances',
    'sref_removeInstances_help' => 'Deletes all articles which are instances of the selected category but not articles of sub-categories.',
    'sref_removeCategoryAnnotations' => 'Remove all occurences of this category',
    'sref_removeCategoryAnnotations_help' => 'Removes all category annotations of the selected category but does not delete the article which it contains.',
    'sref_removePropertyWithDomain' => 'Delete all properties with this domain',
    'sref_removePropertyWithDomain_help' => 'Deletes all property articles which use the selected category as domain.',
    'sref_removeQueriesWithCategories' => 'Remove all queries containing this category',
    'sref_removeQueries_help' => 'Removes all queries which contain the selected category. Does not delete the article which contains the query.',
    'sref_includeSubcategories' => 'Include sub-categories',
    'sref_includeSubcategories_help' => 'Applies the selected operations to all sub-categories of the selected category. This happens recursively, ie. it includes the non-direct sub-categories',
    
    'sref_deleteProperty' => 'Delete property',
    'sref_deleteProperty_help' =>  'Deletes the selected property article.',
	'sref_removeInstancesUsingProperty' => 'Delete all instances using this property.',
    'sref_removeInstancesUsingProperty_help' =>  'Deletes all articles which use this property in any way. (e.g. as an annotation or in a query)',
	'sref_removePropertyAnnotations' => 'Remove all occurences of this property',
    'sref_removePropertyAnnotations_help' =>  'Removes all annotations of this property from all articles. Does not delete the articles.',
	'sref_removeQueriesWithProperties' => 'Remove all queries containing this category',
    'sref_removeQueriesWithProperties_help' =>  'Removes all queries which contain this property as constraint or printout. Does not delete the articles.',
    'sref_includeSubproperties' => 'Include sub-properties',
    'sref_includeSubproperties_help' =>  'Applies the selected operations to all sub-properties of the selected property. This happens recursively, ie. it includes the non-direct sub-properties',


    /* errors */
    'sref_not_allowed_botstart' => 'You are not allowed to start the refactoring bot.',
    'sref_no_sufficient_rights' => 'no sufficient rights',
    'sref_article_changed' => 'nothing done. article was changed in the meantime.',
    'sref_do_not_change_gardeninglog' => 'do not change a GardeningLog page',
    
    /* special pages */
    'srefrefactor' => 'Semantic Refactoring',
   'sref_specialrefactor_description' => 'Semantic Refactoring erlaubt es viele Annotationen gleichzeitig im Wiki mit einem einzelnen Kommando zu manipulieren. 
                                            Das ist beispielsweise dann sinvoll, wenn man ein Property durch ein anderes Property ersetzen will. Es wird
                                            dann an allen Stellen in einem Schritt geändert, wofür man normalerweise alle Seiten manuell ändern und 
                                            neu speichern müsste. Für einen detailierten Überblick über das SemanticRefactoring, schauen Sie bitte im $1.',
    'sref_enter_query' => 'Enter a query to select an instance set',
    'sref_run_query' => 'Run query',
    'sref_clear_query' => 'Clear',
    'sref_open_qi' => 'Open query interface',
    'sref_select_instanceset'=> 'Select instance set',
    'sref_choose_commands' => 'Choose command',
    'sref_running_operations' => 'Running operations',

    'sref_add' => 'add',
    'sref_remove' => 'remove',
    'sref_replace' => 'replace',
    'sref_setvalue' => 'set value',
    'sref_rename' => 'rename',

    'sref_category' => 'Category',
    'sref_old_category' => 'Old category',
    'sref_new_category' => 'New category',
    'sref_annotationproperty' => 'Annotation/Property',
    'sref_property' => 'Property',
    'sref_template' => 'Template',
    'sref_parameter' => 'Parameter',
    'sref_old_parameter' => 'Old parameter',
    'sref_new_parameter' => 'New parameter',
    'sref_value' => 'Value',
    'sref_old_value' => 'Old value',
    'sref_new_value' => 'New value',

    'sref_comment' => 'Comment',
    'sref_starttime' => 'Start-time',
    'sref_endtime' => 'End-time',
    'sref_progress' => 'Progress',
    'sref_status' => 'Status',
    'sref_finished' => 'finished',
    'sref_running' => 'running',

    'sref_comment_addcategory' => 'Add category $1',
    'sref_comment_removecategory' => 'Remove category $1',
    'sref_comment_replacecategory' => 'Replace category $1 by $2'
);

/**
 * Formal German (Deutsch, Sie-Form)
 */
$messages['de-formal'] = $messages['de'];