<?php
$messages = array();

$messages['en'] = array(
    'smw_refactoringbot' => 'Refactoring bot',
    'sref_start_operation' => 'Start refactoring',
    'sref_warning_no_gardening' => 'You can not run gardening bots due to missing rights! You need the "gardening" right.',

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
    'sref_removeQueriesWithCategories_help' => 'Removes all queries which contain the selected category. Does not delete the article which contains the query.',
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
    'semanticrefactoring' => 'Semantic Refactoring',
    'sref_specialrefactor_description' => 'Semantic Refactoring allows the user to manipulate large amounts of wiki annotations with one command. 
                                            This is, for example, necessary if you want to replace a property by another in all annotations where it appears. Another 
                                            example would be to remove all uses of a particular category from all pages. For a detailed overview of the possibilities, please
                                            take a look at $1.',
    'sref_enter_query' => 'Enter a query to select an instance set',
    'sref_run_query' => 'Run query',
    'sref_open_qi' => 'Open query interface',
    'sref_clear_query' => 'Clear',
    'sref_selectall' => 'Select all',
    'sref_deselectall' => 'Deselect all',
    'sref_select_instanceset'=> 'Select instance set',
    'sref_choose_commands' => 'Choose command',
    'sref_running_operations' => 'Running operations',
    'sref_more_results' => 'More results available',
    'sref_next_page' => 'next',
    'sref_prev_page' => 'prev',
    'sref_page' => 'Page',
    'sref_add_command' => 'add command',
    'sref_remove_command' => 'remove command',
    'sref_no_instances_selected' => 'No instances selected',

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
	'sref_touch'=> 'Touch',
	'sref_touchall'=> 'all',

    'sref_comment' => 'Comment',
    'sref_log' => 'Log',
	'sref_starttime' => 'Start-time',
	'sref_endtime' => 'End-time',
	'sref_progress' => 'Progress',
	'sref_status' => 'Status',
    'sref_finished' => 'finished',
    'sref_running' => 'running',
    
    'sref_comment_touchpages' => 'Page was touch (ie. saved without changes)',
    'sref_comment_renameinstance' => 'Rename instance $1 to $2',
    'sref_comment_renameproperty' => 'Rename property $1 to $2',
    'sref_comment_renamecategory' => 'Rename category $1 to $2',
    'sref_comment_deleteproperty' => 'Delete property $1',
    'sref_comment_deletecategory' => 'Delete category $1',
    'sref_comment_addcategory' => 'Add category $1',
    'sref_comment_removecategory' => 'Remove category $1',
    'sref_comment_replacecategory' => 'Replace category $1 by $2',
    'sref_comment_addannotation' => 'Add annotation $1::$2',
    'sref_comment_removeannotation' => 'Remove annotation $1::$2',
    'sref_comment_replaceannotation' => 'Replace annotation $1::$2 by $1::$3',
    'sref_comment_setvalueofannotation' => 'Set value of $1::$2',
    'sref_comment_addvalueoftemplate' => 'Add value to $1: $2=3',
    'sref_comment_setvalueoftemplate' => 'Set value of $1: $2=$3',
    'sref_comment_replacetemplatevalue' => 'Replace template value $1: $2=$3 by $2=$4',
    'sref_comment_renametemplateparameter' => 'Replace template parameter $1: $2 by $3',

    'sref_help_touchpages' => 'Touch pages (ie. save without changes)',
    'sref_help_addcategory' => 'Add category as new annotation',
    'sref_help_removecategory' => 'Remove an existing category annotation',
    'sref_help_replacecategory' => 'Replace an category annotation by another',
    'sref_help_addannotation' => 'Add new annotation',
    'sref_help_removeannotation' => 'Remove existing annotation',
    'sref_help_replaceannotation' => 'Replace annotation by another',
    'sref_help_setvalueofannotation' => 'Set new value for an existing annotation.',
    'sref_help_addvalueoftemplate' => 'Add new value of a template parameter',
    'sref_help_setvalueoftemplate' => 'Set new value of a template parameter',
    'sref_help_replacetemplatevalue' => 'Replace a value of a template parameter by another',
    'sref_help_renametemplateparameter' => 'Rename a template parameter'
);

/**
 * German (Deutsch)
 */
$messages['de'] = array(
 'smw_refactoringbot' => 'Refactoring bot',
    'sref_start_operation' => 'Starte Refactoring',
    'sref_warning_no_gardening' => 'Sie können aufgrund fehlender Rechte keine Gardening bots ausführen! Sie benötigen das "gardening" Recht.',
    
    /*rename*/
    'sref_rename_instance' => 'Instanz umbenennen',
    'sref_rename_instance_help' => 'Benennt die Instanzseite selbst um.',
    'rename_property' => 'Property umbenennen',
    'rename_property_help' => 'Benennt die Propertyseite selbst um.',
    'rename_category' => 'Kategorie umbenennen',
    'rename_category_help' => 'Benennt die Kategorieseite selbst um.',
    'rename_annotations' => 'Alle Vorkommen umbenennen',
    'rename_annotations_help' => 'Benenne alle Vorkommen in Annotationen und Queries um.',

    /*delete*/
    'sref_deleteCategory' => 'Lösche Kategorie',
    'sref_deleteCategory_help' => 'Lösche den ausgewählten Kategorie Artikel',
    'sref_removeInstances' => 'Lösche alle Instanzen',
    'sref_removeInstances_help' => 'Lösche alle Artikel die Instanzen der ausgewählten Kategorie sind aber keine Artikel der Unterkategorien.',
    'sref_removeCategoryAnnotations' => 'Entferne alle Vorkommen dieser Kategorie',
    'sref_removeCategoryAnnotations_help' => 'Entferne alle Kategorieannotationen der ausgewählten Kategorie aber lösche nicht die Artikel die sie enthalten.',
    'sref_removePropertyWithDomain' => 'Lösche alle Properties dieser Domäne',
    'sref_removePropertyWithDomain_help' => 'Lösche alle Property Artikel die die ausgewählte Kategorie als Domäne nutzen.',
    'sref_removeQueriesWithCategories' => 'Entferne alle Queries die diese Kategorie enthalten',
    'sref_removeQueriesWithCategories_help' => 'Entferne alle Queries die diese Kategorie enthalten. Löscht nicht den Artikel der die Query enthält.',
    'sref_includeSubcategories' => 'Schließe Unterkategorien mit ein',
    'sref_includeSubcategories_help' => 'Wendet die ausgewählte Operation auf alle Unterkategorien der ausgewählten Kategorie an. Dies findet rekursiv statt, das heißt es werden nicht-direkte Unterkategorien mit einbezogen',
    
    'sref_deleteProperty' => 'Lösche Property',
    'sref_deleteProperty_help' =>  'Lösche den ausgewählten Property Artikel.',
    'sref_removeInstancesUsingProperty' => 'Lösche alle Instanzen die diese Property benutzen.',
    'sref_removeInstancesUsingProperty_help' =>  'Lösche alle Artikel die diese Property auf irgendeine Weiße nutzen. (d.h. als Annotation oder in einer Query)',
    'sref_removePropertyAnnotations' => 'Entferne alle Annotation dieser Property.',
    'sref_removePropertyAnnotations_help' =>  'Entferne alle Annotation dieser Property von allen Artikeln. Löscht die Artikel nicht.',
    'sref_removeQueriesWithProperties' => 'Entferne alle Queries mit dieser Property',
    'sref_removeQueriesWithProperties_help' =>  'Entferne alle Queries mit diesem Property als Einschränkung oder Ausgabe. Löscht die Artikel nicht.',
    'sref_includeSubproperties' => 'Schließe Unter-Properties mit ein',
    'sref_includeSubproperties_help' =>  'Wendet die ausgewählten Operationen auf alle Unter-Properties des ausgewählten Properties an. Dies findet rekursiv statt, das heißt es werden nicht-direkte Unter-Properties mit einbezogen',


    /* errors */
    'sref_not_allowed_botstart' => 'Es ist ihnen nicht gestattet den Refactoring bot zu starten.',
    'sref_no_sufficient_rights' => 'unzureichende Rechte',
    'sref_article_changed' => 'nichts ausgeführt. Artikel wurde in der Zwischenzeit verändert.',
    'sref_do_not_change_gardeninglog' => 'Verändere keine GardeningLog Seite',
    
    /* special pages */
    'semanticrefactoring' => 'Semantic Refactoring',
   'sref_specialrefactor_description' => 'Semantic Refactoring erlaubt es viele Annotationen gleichzeitig im Wiki mit einem einzelnen Kommando zu manipulieren. 
                                            Das ist beispielsweise dann sinvoll, wenn man ein Property durch ein anderes Property ersetzen will. Es wird
                                            dann an allen Stellen in einem Schritt geändert, wofür man normalerweise alle Seiten manuell ändern und 
                                            neu speichern müsste. Für einen detailierten Überblick über das SemanticRefactoring, schauen Sie bitte im $1.',
    'sref_enter_query' => 'Geben sie eine Query an um eine Instanzmenge auszuwählen',
    'sref_run_query' => 'Query ausführen',
    'sref_clear_query' => 'Clear',
    'sref_selectall' => 'Alle auswählen',
    'sref_deselectall' => 'Alle abwählen',
    'sref_open_qi' => 'Query Interface öffnen',
    'sref_select_instanceset'=> 'Instanzmenge auswählen',
    'sref_choose_commands' => 'Befehl auswählen',
    'sref_running_operations' => 'Laufende Operationen',
    'sref_more_results' => 'Weitere Ergebnisse verfügbar',
    'sref_next_page' => 'nächste',
    'sref_prev_page' => 'vorherige',
    'sref_page' => 'Seite',
    'sref_add_command' => 'Kommando hinzufügen',
    'sref_remove_command' => 'Kommando entfernen',
    'sref_no_instances_selected' => 'Keine Instanz ausgewählt',

    'sref_add' => 'füge hinzu',
    'sref_remove' => 'entferne',
    'sref_replace' => 'ersetze',
    'sref_setvalue' => 'setze Wert',
    'sref_rename' => 'benenne um',

    'sref_category' => 'Kategorie',
    'sref_old_category' => 'Alte Kategorie',
    'sref_new_category' => 'Neue Kategorie',
    'sref_annotationproperty' => 'Annotation/Property',
    'sref_property' => 'Property',
    'sref_template' => 'Template',
    'sref_parameter' => 'Parameter',
    'sref_old_parameter' => 'Alter Parameter',
    'sref_new_parameter' => 'Neuer Parameter',
    'sref_value' => 'Wert',
    'sref_old_value' => 'Alter Wert',
    'sref_new_value' => 'Neuer Wert',
    'sref_touch'=> 'Anfassen',
    'sref_touchall'=> 'alle',

    'sref_comment' => 'Kommentar',
    'sref_log' => 'Log',
    'sref_starttime' => 'Startzeit',
    'sref_endtime' => 'Endzeit',
    'sref_progress' => 'Fortschritt',
    'sref_status' => 'Status',
    'sref_finished' => 'abgeschlossen',
    'sref_running' => 'running',
    
    'sref_comment_touchpages' => 'Seite wurde angefasst, d.h. ohne Änderung neu gespeichert.',
    'sref_comment_renameinstance' => 'Benenne Instanz $1 in $2 um',
    'sref_comment_renameproperty' => 'Benenne Property $1 in $2 um',
    'sref_comment_renamecategory' => 'Benenne Kategorie $1 in $2 um',
    'sref_comment_deleteproperty' => 'Lösche Property $1',
    'sref_comment_deletecategory' => 'Lösche Kategorie $1',
    'sref_comment_addcategory' => 'Füge Kategorie $1 hinzu',
    'sref_comment_removecategory' => 'Entferne Kategorie $1',
    'sref_comment_replacecategory' => 'Ersetze Kategorie $1 durch $2',
    'sref_comment_addannotation' => 'Füge Annotation $1::$2 hinzu',
    'sref_comment_removeannotation' => 'Entferne Annotation $1::$2',
    'sref_comment_replaceannotation' => 'Ersetze Annotation $1::$2 durch $1::$3',
    'sref_comment_setvalueofannotation' => 'Setze den Wert von $1::$2',
    'sref_comment_addvalueoftemplate' => 'Füge Templatewert hinzu $1: $2=3',
    'sref_comment_setvalueoftemplate' => 'Setze den Wert von $1: $2=$3',
    'sref_comment_replacetemplatevalue' => 'Ersetze Templatewerte $1: $2=$3 by $2=$4',
    'sref_comment_renametemplateparameter' => 'Ersetze Templateparameter $1: $2 by $3',
    
    'sref_help_touchpages' => 'Fasse Seite an, d.h. speichere ohne Änderung.',
    'sref_help_addcategory' => 'Fügt Kategorie-Annotation hinzu',
    'sref_help_removecategory' => 'Entfernt Kategorie-Annotation',
    'sref_help_replacecategory' => 'Ersetzt Kategorie-Annotation durch eine andere',
    'sref_help_addannotation' => 'Fügt neue Annotation hinzu',
    'sref_help_removeannotation' => 'Entfernt annotation',
    'sref_help_replaceannotation' => 'Ersetzt Annotation durch eine andere Annotation',
    'sref_help_setvalueofannotation' => 'Setzt neuen Wert für bestehende Annotationen',
    'sref_help_addvalueoftemplate' => 'Fügt neuen Wert für Templateparameter hinzu',
    'sref_help_setvalueoftemplate' => 'Setzt neue Wert für Templateparameter',
    'sref_help_replacetemplatevalue' => 'Ersetzt Wert für Templateparameter durch anderen',
    'sref_help_renametemplateparameter' => 'Benennt einen Templateparameter um'
   
);

/**
 * Formal German (Deutsch, Sie-Form)
 */
$messages['de-formal'] = $messages['de'];