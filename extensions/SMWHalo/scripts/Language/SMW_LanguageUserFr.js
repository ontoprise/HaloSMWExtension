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
*  @file
* 
*  @ingroup SMWHaloLanguage
*/

var wgUserLanguageStrings = {
    'MUST_NOT_BE_EMPTY'       : '(e)Ce champ ne doit pas être vide.',
    'VALUE_IMPROVES_QUALITY'  : '(i)Une valeur dans ce champ améliore la qualité de la base de connaissance.',
    'SELECTION_MUST_NOT_BE_EMPTY' : '(e)La sélection ne doit pas être vide !',
    'INVALID_FORMAT_OF_VALUE' : '(e)Le format de la valeur est invalide.',
    'INVALID_VALUES'          : 'Valeurs invalides.',
    'NAME'                    : 'Nom:',
    'ENTER_NAME'              : 'Veuillez saisir un nom.',
    'ADD'                     : 'Ajouter',
    'INVALID_VALUES'          : 'Valeurs invalides.',
    'CANCEL'                  : 'Annuler',
    'CREATE'                  : 'Créer',
    'EDIT'                    : 'Editer',
    'ANNOTATE'                : 'Annoter',
    'SUB_SUPER'               : 'Sous/Super',
    'INVALID_NAME'            : 'Nom invalide.',
    'CHANGE'                  : 'Modifier',
    'DELETE'                  : 'Supprimer',
    'INPUT_BOX_EMPTY'         : 'Erreur! Le champ est vide.',
    'ERR_QUERY_EXISTS_ARTICLE' : 'Une erreur est survenue lors de la vérification de l\'existence de l\'article <$-page>.',
    'CREATE_PROP_FOR_CAT'     : 'Cette propriété a été créée pour la catégorie <$cat>. Veuillez saisir un contenu significatif.',
    'NOT_A_CATEGORY'          : 'L\'article courant n\'est pas une catégorie.',
    'CREATE_CATEGORY'         : 'Cette catégorie a été créée mais n\'a pas été éditée. Veuillez saisir un contenu significatif.',
    'CREATE_SUPER_CATEGORY'   : 'Cette catégorie a été créée en tant que sur-catégorie mais n\'a pas été éditée. Veuillez saisir un contenu significatif.',
    'CREATE_SUB_CATEGORY'     : 'Cette catégorie a été créée en tant que sous-catégorie mais n\'a pas été éditée. Veuillez saisir un contenu significatif.',
    'NOT_A_PROPERTY'          : 'L\'article courant n\'est pas une propriété.',
    'CREATE_PROPERTY'         : 'Cette propriété a été créée mais n\'a pas été éditée. Veuillez saisir un contenu significatif.',
    'CREATE_SUB_PROPERTY'     : 'Cet article a été créé en tant que sous-propriété. Veuillez saisir un contenu significatif.',
    'CREATE_SUPER_PROPERTY'   : 'Cet article a été créé en tant que sur-propriété. Veuillez saisir un contenu significatif.',
    'ERROR_CREATING_ARTICLE'  : "Une erreur est survenue lors de la création de l\'article.",
    'ERROR_EDITING_ARTICLE'   : "Une erreur est survenue lors de l\'édition de l\'article.",
    'UNMATCHED_BRACKETS'      : 'Attention! L\'article contient des erreurs de syntaxe ("]]" manquant)',
    'MAX_CARD_MUST_NOT_BE_0'  : "(e)La cardinalité maximale ne doit pas être inférieur ou égale Ã  0 !",
    'SPECIFY_CARDINALITY'     : "(e)Veuillez spécifier cette cardinalité !",
    'MIN_CARD_INVALID'        : "(e)La cardinalité minimale doit être inférieure Ã  la cardinalité maximale !",
    'ASSUME_CARDINALITY_0'    : "(i) La cardinalité minimale est supposée être 0.",
    'ASSUME_CARDINALITY_INF'  : "(i) La cardinalité maximale est supposée être infinite.",

    // Namespaces
    'NS_SPECIAL'              : 'Spécial',

    // Relation toolbar
    'ANNOTATE_PROPERTY'       : 'Annoter une propriété.',
    'PAGE'                    : 'Page:',
    'ANNO_PAGE_VALUE'         : 'Page/Valeur annotée',
    'SHOW'                    : 'Afficher:',
    'DEFINE_SUB_SUPER_PROPERTY' : 'Définir une sous- ou super-propriété.',
    'CREATE_NEW_PROPERTY'     : 'Créer une nouvelle propriété.',
    'ENTER_DOMAIN'            : 'Saisir un domaine.',
    'ENTER_RANGE'             : 'Saisir un champ de valeurs.',
    'ENTER_TYPE'              : 'Sélectionner un type.',
    'RENAME_ALL_IN_ARTICLE'   : 'Tout renommer dans cet article.',
    'CHANGE_PROPERTY'         : 'Modifier une propriété.',
    'PROPERTIES'              : 'Propriétés',
    'RETRIEVE_SCHEMA_DATA'    : 'Echec lors de la récupération du schéma de données !',
    'RECPROP'                 : "Proprieté recommandé",

    // Property characteristics toolbar
    'PROPERTY_DOES_NOT_EXIST' : '(w)Cette propriété n\'existe pas.',
    'PROPERTY_ALREADY_EXISTS' : '(w)Cette propriété existe déjÃ .',
    'PROPERTY_NAME_TOO_LONG'  : '(e)Le nom de cette propriété est trop long ou contient des caractÃ¨res invalides.',
    'PROPERTY_VALUE_TOO_LONG' : '(w)Cette valeur est trÃ¨s longue. Elle sera sauvegardée parmi les propriétés de type "Type:Text".',
    'PROPERTY_ACCESS_DENIED'  : '(e)You are not authorized to annotate this property.', 
    'PROPERTY_ACCESS_DENIED_TT': 'You are not authorized to annotate this property.',   
    'CANT_SAVE_FORBIDDEN_PROPERTIES': 'The article contains write protected properties and can not be saved.',
    'CREATE_SUPER_PROPERTY'   : 'Créer "$-title" et faire de "$sftt" une sur-propriété de "$-title"',
    'CREATE_SUB_PROPERTY'     : 'Créer "$-title" et faire de "$sftt" une sous-propriété de "$-title"',
    'MAKE_SUPER_PROPERTY'     : 'Faire de "$sftt" une sur-propriété de  "$-title"',
    'MAKE_SUB_PROPERTY'       : 'Faire de "$sftt" une sous-propriété de "$-title"',
    'ADD_TYPE'                : 'Type',
    'ADD_RANGE'               : 'Champ de valeurs',
    'DOMAIN'                  : 'Domaine:',
    'RANGE'                   : 'Champ de valeurs:',
    'INVERSE_OF'              : 'Inverse de:',
    'Mandatory'               : 'Obligatoire:',
    'TRANSITIVE'              : 'Transitif',
    'SYMMETRIC'               : 'Symétrique',
    'RETRIEVING_DATATYPES'    : 'Récupération des types de données...',
    'NARY_ADD_TYPES'          : '(e) Veuillez ajouter des types ou des champs de valeurs',
    
    'PROPERTY_PROPERTIES'     : "Charactéristiques de la propriété",
    
    
    'PAGE_TYPE'               : "page",     // name of the page data type
    'NARY_TYPE'               : "arité n",       // name of the n-ary data type
    'SPECIFY_PROPERTY'        : "Spécifier cette porpriété.",
    'PC_DUPLICATE'            : "Au moins une propriété est spécifiée plusieurs fois. Veuillez supprimer les doublons.",
    'PC_HAS_TYPE'             : "A pour type", 
	'PC_HAS_FIELDS'			  : "has fields", //TODO: translate
    'PC_MAX_CARD'             : "A pour cardinalité max",
    'PC_MIN_CARD'             : "A pour cardinalité min",
    'PC_INVERSE_OF'           : "Est l\'inverse de", 
    'PC_INVERSE'              : "inverse", 
    'PC_TRANSITIVE'           : "transitif", 
    'PC_SYMMETRICAL'          : "symétrique", 
    'PC_AND'                  : "et", 
    'PC_UNSUPPORTED'          : "Ce wiki ne supporte pas les propriétés de $1.",
    

    // Category toolbar
    'ANNOTATE_CATEGORY'       : 'Annoter une catégorie.',
    'CATEGORY_DOES_NOT_EXIST' : '(w)Cette catégorie n\'existe pas.',
    'CATEGORY_ALREADY_EXISTS' : '(w)Cette catégorie existe déjà .',
    'CATEGORY_NAME_TOO_LONG'  : '(e)Le nom de cette catégorie est trop long ou contient des caractères invalides.',
    'CREATE_SUPER_CATEGORY'   : 'Créer "$-title" et faire de "$sftt" une sur-catégorie de "$-title"',
    'CREATE_SUB_CATEGORY'     : 'Créer "$-title" et faire de "$sftt" une sous-catégorie de "$-title"',
    'MAKE_SUPER_CATEGORY'     : 'Faire de "$sftt" une sur-catégorie de  "$-title"',
    'MAKE_SUB_CATEGORY'       : 'Faire de "$sftt" une sous-catégorie de  "$-title"',
    'DEFINE_SUB_SUPER_CAT'    : 'Définir une sous- ou sur-catégorie.',
    'CREATE_SUB'              : 'Créer une sous',
    'CREATE_SUPER'            : 'Créer une sur',
    'CREATE_NEW_CATEGORY'     : 'Créer une nouvelle catégorie.',
    'CHANGE_ANNO_OF_CAT'      : 'Changer l\'annotation d\'une catégorie.',
    'CATEGORIES'              : 'Catégories',
    'ADD_AND_CREATE_CAT'      : 'Ajouter et créer',
    'CATEGORY_ALREADY_ANNOTATED': '(w)Cette catégorie est déjà  annotée.',
    
    // Annotation hints
    'ANNOTATION_HINTS'        : 'Indications d\'annotation',
    'ANNOTATION_ERRORS'       : 'Erreurs d\'annotation',
    'AH_NO_HINTS'             : '(i)Aucune indication pour cet article.',
    'AH_SAVE_COMMENT'         : 'Les annotations ont été ajoutées dans le Mode d\'Annotation Avancé.',
    'AAM_SAVE_ANNOTATIONS'    : 'Voulez-vous enregistrer les annotations de la session courante ?',
    'CAN_NOT_ANNOTATE_SELECTION' : 'Il est impossible d\'annoter la sélection. Elle contient déjà  des annotations ou des paragraphes ou finit dans un lien.',
    'AAM_DELETE_ANNOTATIONS'  : 'àtes-vous sà»rs de vouloir supprimer cette annotation?',
    
    // Save annotations
    'SA_SAVE_ANNOTATION_HINTS': "N\'oubliez pas d\'enregistrer votre travail!",
    'SA_SAVE_ANNOTATIONS'     : 'Enregistrer les annotations',
    'SA_SAVE_ANNOTATIONS_AND_EXIT' : 'Enregistrer & quitter',
    'SA_ANNOTATIONS_SAVED'    : '(i) Les annotations ont été enregistrées avec succès.',
    'SA_SAVING_ANNOTATIONS_FAILED' : '(e) Une erreur est survenue lors de l\'enregistrement des annotations.',
    'SA_SAVING_ANNOTATIONS'   : '(i) Enregistrement des annotations en cours...',
    
    // Queries in STB
    'QUERY_HINTS'			  : 'Inline queries',
    'NO_QUERIES_FOUND'		  : '(i) No queries found in this article',

    // Autocompletion
    'AUTOCOMPLETION_HINT'     : 'Appuyez sur Ctrl+Alt+Espace pour utiliser l\'autocomplétion. (Ctrl+Espace in IE)',
    'WW_AUTOCOMPLETION_HINT'  : 'L\'éditeur WYSIWYG ("What you see is what you get", i.e. "Ce que vous voyez est ce que vous obtenez") supporte l\'autocomplétion pour wiki text soulement.',
    'AC_CLICK_TO_DRAG'        : 'Autocomplétion',
    'AC_MORE_RESULTS_AVAILABLE' : 'Too much results found...',
    'AC_MORE_RESULTS_TOOLTIP' : 'Too many results. Please expand your search term to get less results.',
    'AC_NO_RESULTS': 'No results',
    'AC_ALL' : 'Auto-completion for all pages',
	'AC_QUERY' : 'ASK-Query',
	'AC_SCHEMA_PROPERTY_DOMAIN' : 'All properties with domain: ',
	'AC_SCHEMA_PROPERTY_RANGE_INSTANCE' : 'All properties which have a range-instance of: ',
	'AC_DOMAINLESS_PROPERTY' : 'All properties without domain',
	'AC_ANNOTATION_PROPERTY' : 'Properties which are used on pages of category: ',
	'AC_ANNOTATION_VALUE' : 'All properties with value annotated: ',
	'AC_INSTANCE_PROPERTY_RANGE' : 'All instances which are member of range: ',
	'AC_NAMESPACE' : 'All pages in namespace(s): ',
	'AC_LEXICAL' : 'All pages containing: ',
	'AC_SCHEMA_PROPERTY_TYPE' : 'All properties of type: ',
	'AC_ASF' : 'TODO: ask ingo about this',
	'AC_FROM_BUNDLE' : 'Pages coming from bundle: ',
    
    // Combined search
    'ADD_COMB_SEARCH_RES'     : 'Résultats additionnels de la recherche combinée.',
    'COMBINED_SEARCH'         : 'Recherche combinée',

    'INVALID_GARDENING_ACCESS' : 'Vous n\'êtes pas autorisés à  annuler bots. Seuls sysops et gardeners y sont autorisés.',
    'GARDENING_LOG_COLLAPSE_ALL' : 'Tout réduire',
    'GARDENING_LOG_EXPAND_ALL'   : 'Tout agrandir',
    'BOT_WAS_STARTED'           : 'Le bot a été démarré!',
    
    // Ontology browser
    'OB_ID'                   : 'NavigateurOntologies',
    'ONTOLOGY_BROWSER'        : 'Navigateur d\'ontologies',
    
    'KS_NOT_SUPPORTED'        : 'Konqueror n\'est actuellement pas supporté!',
    'SHOW_INSTANCES'          : 'Afficher les instances',
    'HIDE_INSTANCES'          : 'Cacher les instances',
    'ENTER_MORE_LETTERS'      : "Veuillez saisir au moins deux lettres. Sinon vous allez obtenir trop de résultats.",
    'MARK_A_WORD'             : 'Marquer un mot...',
    'OPEN_IN_OB'              : 'Ouvrir dans le navigateur d\'ontologies',
	'OPEN_IN_OB_NEW_TAB'      : '... nouvel tabulation',
    'OB_CREATE'               : 'Créer',
    'OB_RENAME'               : 'Renommer',
    'OB_DELETE'               : 'Supprimer',
    'OB_PREVIEW'              : 'Aperçu',
    'OB_TITLE_EXISTS'         : 'Cet �l�ment existe!',
    'OB_TITLE_NOTEXISTS'	  : 'Cet �l�ment n\'existe pas!',
    'OB_ENTER_TITLE'          : 'Saisir le titre',
    'OB_SELECT_CATEGORY'      : 'Sélectionner la catégorie en premier',
    'OB_SELECT_PROPERTY'      : 'Sélectionner la propriété en premier',
    'OB_SELECT_INSTANCE'      : 'Sélectionner l\'instance en premier',
    'OB_WRONG_MAXCARD'        : 'Cardinalité max invalide',
    'OB_WRONG_MINCARD'        : 'Cardinalité min invalide',
    'OB_CONFIRM_INSTANCE_DELETION' : 'àtes-vous sà»rs de vouloir supprimer cet article?',
    'SMW_OB_OPEN'             : '(ouvrir)',
    'SMW_OB_EDIT'             : '(éditer)',
    'SMW_OB_ADDSOME'          : '(ajouter)',
    'OB_CONTAINS_FURTHER_PROBLEMS' : 'Contient des problèmes supplémentaires',
    'SMW_OB_MODIFIED'         : 'La page a été modifiée. Les problèmes suivants de Gardening auraient déjà  dà»s être résolus:',
    
    // Ontology Browser metadata
	'SMW_OB_META_PROPERTY'	  : 'Meta property',
	'SMW_OB_META_PROPERTY_VALUE' : 'Value',
	'SMW_OB_META_COMMAND_SHOW'  : 'Show metadata',
	'SMW_OB_META_COMMAND_RATE'  : 'Rate this fact',
	
	// metaproperties
	'SMW_OB_META_SWP2_AUTHORITY'   : 'Authority',
	'SMW_OB_META_SWP2_KEYINFO'   : 'Key info',
	'SMW_OB_META_SWP2_SIGNATURE'   : 'Signature',
	'SMW_OB_META_SWP2_SIGNATURE_METHOD'   : 'Signature method',
	'SMW_OB_META_SWP2_VALID_FROM'   : 'Valid from',
	'SMW_OB_META_SWP2_VALID_UNTIL'   : 'Valid until',
	
	'SMW_OB_META_DATA_DUMP_LOCATION_FROM'   : 'Dump location',
	'SMW_OB_META_HOMEPAGE_FROM'   : 'Homepage',
	'SMW_OB_META_SAMPLE_URI_FROM'   : 'Sample URI',
	'SMW_OB_META_SPARQL_ENDPOINT_LOCATION_FROM'   : 'SPARQL Endpoint',
	'SMW_OB_META_DATASOURCE_VOCABULARY_FROM'   : 'Vocabulary',
	'SMW_OB_META_DATASOURCE_ID_FROM'   : 'ID',
	'SMW_OB_META_DATASOURCE_CHANGEFREQ_FROM'   : 'Change frequency',
	'SMW_OB_META_DATASOURCE_DESCRIPTION_FROM'   : 'Description',
	'SMW_OB_META_DATASOURCE_LABEL_FROM'   : 'Label',
	'SMW_OB_META_DATASOURCE_LASTMOD_FROM'   : 'Last change',
	'SMW_OB_META_DATASOURCE_LINKEDDATA_PREFIX_FROM'   : 'LinkedData prefix',
	'SMW_OB_META_DATASOURCE_URIREGEXPATTERN_FROM'   : 'URI pattern',

	'SMW_OB_META_DATA_DUMP_LOCATION_TO'   : 'Dump location',
	'SMW_OB_META_HOMEPAGE_TO'   : 'Homepage',
	'SMW_OB_META_SAMPLE_URI_TO'   : 'Sample URI',
	'SMW_OB_META_SPARQL_ENDPOINT_LOCATION_TO'   : 'SPARQL Endpoint',
	'SMW_OB_META_DATASOURCE_VOCABULARY_TO'   : 'Vocabulary',
	'SMW_OB_META_DATASOURCE_ID_TO'   : 'ID',
	'SMW_OB_META_DATASOURCE_CHANGEFREQ_TO'   : 'Change frequency',
	'SMW_OB_META_DATASOURCE_DESCRIPTION_TO'   : 'Description',
	'SMW_OB_META_DATASOURCE_LABEL_TO'   : 'Label',
	'SMW_OB_META_DATASOURCE_LASTMOD_TO'   : 'Last change',
	'SMW_OB_META_DATASOURCE_LINKEDDATA_PREFIX_TO'   : 'LinkedData prefix',
	'SMW_OB_META_DATASOURCE_URIREGEXPATTERN_TO'   : 'URI pattern',
	
	'SMW_OB_META_IMPORT_GRAPH_CREATED'   : 'Graph was created on',
	'SMW_OB_META_IMPORT_GRAPH_REVISION_NO'   : 'Revision number',
	'SMW_OB_META_IMPORT_GRAPH_LAST_CHANGED_BY'   : 'Last change',
	'SMW_OB_META_RATING_VALUE'   : 'Rating value',
	'SMW_OB_META_RATING_USER'   : 'Rated from user',
	'SMW_OB_META_RATING_CREATED'   : 'Rating was created on',
	'SMW_OB_META_RATING_ASSESSMENT'   : 'Assessment',
	

    // Query Interface
    'QUERY_INTERFACE'         : 'Interface de requêtes',
    'QI_MAIN_QUERY_NAME'      : 'Requête principale',
    'QI_ARTICLE_TITLE'        : 'Titre de l\'article',
    'QI_EMPTY_QUERY'          : 'Votre requête est vide.',
    'QI_INSTANCE'             : 'Instance:',
    'QI_PROPERTYNAME'         : 'Nom de la propriété:',
    'QI_PROPERTYVALUE'        : 'Valeur de la propriété:',
    'QI_SHOW_PROPERTY'        : 'Afficher dans les résultats:',
    'QI_PROPERTY_MUST_BE_SET' : 'La valeur doit être fixée:',
    'QI_USE_SUBQUERY'         : 'Insérer une sous-requête',
    'QI_PAGE'                 : 'Page',
    'QI_OR'                   : 'ou',
    'QI_ENTER_CATEGORY'       : 'Veuillez saisir une catégorie',
    'QI_ENTER_INSTANCE'       : 'Veuillez saisir une instance',
    'QI_ENTER_PROPERTY_NAME'  : 'Veuillez saisir un nom de propriété',
    'QI_CLIPBOARD_SUCCESS'    : 'La requête a été copiée avec succès dans votre presse-papier',
    'QI_CLIPBOARD_FAIL'       : 'Votre navigateur n\'autorise pas l\'accès au presse-papier.\nLa requête ne peut pas être copiée dans votre presse-papier.\nVeuillez utiliser la fonction "Afficher la requête dans son intégralité" et copiez la requête manuellement.',
    'QI_SUBQUERY'             : "Sous-requête",
    'QI_CATEGORIES'           : " Catégories:",
    'QI_INSTANCES'            : " Instances:",
    'QI_QUERY_EXISTS'         : "Une requête portant se nom existe déjà . Veuillez choisir un autre nom.",
    'QI_QUERY_SAVED'          : "Votre requête a été enregistrée avec succès.",
    'QI_SAVE_ERROR'           : "Une erreur inconnue est survenue. Votre requête ne peut pas être enregistrée.",
    'QI_EMPTY_TEMPLATE'       : "Afin d'utiliser le format 'template', vous devez saisir un nom de template.",
    'QI_SPECIAL_QP_PARAMS'    : 'Special parameters of',
    'QI_START_CREATING_QUERY' : 'Click on<ul><li>Add Category</li><li>Add Property</li><li>Add Instance</li></ul>to create a new query.',
    'QI_BC_ADD_CATEGORY'      : 'Add category',
    'QI_BC_ADD_PROPERTY'      : 'Add property',
    'QI_BC_ADD_INSTANCE'      : 'Add instance',
    'QI_BC_EDIT_CATEGORY'     : 'Edit category',
    'QI_BC_EDIT_PROPERTY'     : 'Edit property',
    'QI_BC_EDIT_INSTANCE'     : 'Edit instance',
    'QI_BC_ADD_OTHER_CATEGORY': 'Add another category (OR)',
    'QI_BC_ADD_OTHER_INSTANCE': 'Add another instance (OR)',
    'QI_DC_ADD_OTHER_RESTRICT': 'Add another restriction (OR)',
    'QI_CAT_ADDED_SUCCESSFUL' : 'Category successfully added to the query',
    'QI_PROP_ADDED_SUCCESSFUL': 'Property successfully added to the query',
    'QI_INST_ADDED_SUCCESSFUL': 'Instance successfully added to the query',
    'QI_ADD_PROPERTY_CHAIN'   : 'Add another property to property chain',
    'QI_CREATE_PROPERTY_CHAIN': 'Create a property chain by adding another property',
    'QI_PROP_VALUES_RESTRICT' : 'Restriction',
    'QI_SPECIFIC_VALUE'       : 'Specific value',
    'QI_NONE'                 : 'None',
    'QI_PROPERTY_TYPE'        : 'Type',
    'QI_PROPERTY_RANGE'       : 'Range',
    'QI_COLUMN_LABEL'         : 'Column label',
    'QI_SHOWUNIT'             : 'Unit',
    'QI_EQUAL'                : 'equal',
    'QI_LT'                   : 'less',
    'QI_GT'                   : 'greater',
    'QI_NOT'                  : 'not',
    'QI_LIKE'                 : 'like',
    'QI BUTTON_ADD'           : 'Add',
    'QI_BUTTON_UPDATE'        : 'Update',
    'QI_ALL_VALUES'           : 'all values',
    'QI_SUBQUERY_TEXT'        : 'A new empty subquery will be created after you press the "Add" or "Update" button, where you can define specific restrictions.',
    'QI_TT_SHOW_IN_RES'       : 'The property value will be shown in the result.',
    'QI_TT_MUST_BE_SET'       : 'Only results where the property value is  set will included in the query.',
    'QI_TT_NO_RESTRICTION'    : 'No property value restrictions. All property values will be included in the query.',
    'QI_TT_VALUE_RESTRICTION' : 'Property values must fulfil specifc criterias.',
    'QI_TT_SUBQUERY'          : 'Restrictions for the property values will be defined in an  extra query.',
    'QI_TT_ADD_CHAIN'         : 'You can construct a chain of properties to easy connect properties which can be linked together. For example, you can create a property chain with the property &quot;Located In&quot; and &quot;Member of&quot;, where the property value is restricted to &quot;EU&quot;, to find things which are located in something which is a member of the EU.',
    'QI_QP_PARAM_intro'       : 'header',
    'QI_QP_PARAM_outro'       : 'footer',
    'QI_NOT_SPECIFIED'        : 'Not specified',
    'QI_NO_QUERIES_FOUND'     : 'Your search did not match any queries in the wiki',
    'QI_SPARQL_NOT_SUPPORTED' : 'SPARQL queries cannot be edited in Query Interface.',

    
    // Find work
    'FW_SEND_ANNOTATIONS'     : 'Merci d\'évaluer les annotations, ',
    'FW_MY_FRIEND'            : 'mon ami!',
    
    // Wiki text parser
    'WTP_TEXT_NOT_FOUND'          : "'$1' n'a pas été trouvé dans le texte wiki.",
    'WTP_NOT_IN_NOWIKI'           : "'$1' est une partie de la section &lt;nowiki&gt;.\nCeci ne peut donc pas être annoté.",
    'WTP_NOT_IN_TEMPLATE'         : "'$1' est une partie d'un template.\nCeci ne peut donc pas être annoté.",
    'WTP_NOT_IN_ANNOTATION'       : "'$1' est une partie d'une annotation.\nCeci ne peut donc pas être annoté.",
    'WTP_NOT_IN_QUERY'            : "'$1' est une partie d'une requête.\nCeci ne peut donc pas être annoté.",
        'WTP_NOT_IN_TAG'                  : "'$1' est entre d'un tag $2.\nCeci ne peut donc pas être annoté.",
    'WTP_NOT_IN_PREFORMATTED'     : "'$1' est une partie d'un texte préformaté.\nCeci ne peut donc pas être annoté.",
    'WTP_SELECTION_OVER_FORMATS'  : "Â´La sélection comprend différents formats:\n$1",
    
    // ACL extension
    'smw_acl_*' : '*',
    'smw_acl_read' : 'lire',
    'smw_acl_edit' : 'éditer',
    'smw_acl_create' : 'créer',
    'smw_acl_move' : 'déplacer',
    'smw_acl_permit' : 'autoriser',
    'smw_acl_deny' : 'interdire',
    'smw_acl_create_denied' : 'Il vous est interdit de créer l\'article "$1".',
    'smw_acl_edit_denied' : 'Il vous est interdit d\'éditer l\'article "$1".',
    'smw_acl_delete_denied' : 'Il vous est interdit de supprimer l\'article "$1".',
    
    // Rule toolbar
    'RULE_RULES'        : 'Règles',
    'RULE_CREATE'       : 'Créer une nouvelle règle.',
    'RULE_EDIT'         : 'Editer une règle.',
    'RULE_NAME_TOO_LONG': '(e)Le nom de cette règle est trop long ou contient des caractères invalides.',
    'RULE_TYPE'         : 'Type de la règle:',
    'RULE_TYPE_DEFINITION'    : 'Définition',
    'RULE_TYPE_PROP_CHAINING' : 'Lien entre les propriété',
    'RULE_TYPE_CALCULATION'   : 'Calcul',
    
    // Treeview
    'smw_stv_browse' : 'naviguer',
    
    // former content
    'PROPERTY_NS_WOC'         : 'Propriété', // Property namespace without colon
    'RELATION_NS_WOC'         : 'Relation', // Relation namespace without colon
    'CATEGORY_NS_WOC'         : 'Catégorie', // Category namespace without colon
    
    'CATEGORY'                : "Catégorie:",
    'PROPERTY'                : "Propriété:",
    'TEMPLATE'                : "Template:",    
    'TYPE'                    : 'Type:',

    // Simple rules
    
    'SR_DERIVE_BY'      : 'Dériver $1 $2 par une règle complexe',
    'SR_HEAD'           : 'En-tête',
    'SR_BODY'           : 'Corp',
    'SR_CAT_HEAD_TEXT'  : 'Tous les articles $1 appartennant à  $2 $3 sont définis par',
    'SR_PROP_HEAD_TEXT' : 'Tous les articles $1 ont pour propriété $2 avec la valeur $3, si',
    'SR_MCATPROP'       : 'Etant membre d\'une certaine $1catégorie$2 ou $3propriété$4',
    'SR_RULE_IMPLIES'   : 'Cette règle implique ce qui suit :',
    'SR_SAVE_RULE'      : 'Générer une règle',
    'SR_ALL_ARTICLES'   : 'Tous les articles',
    'SR_BELONG_TO_CAT'  : 'appartiennent à  la catégorie',
    'SR_AND'            : 'ET',
    'SR_HAVE_PROP'      : 'ont la propriété',
    'SR_WITH_VALUE'     : 'avec la valeur',
    'SR_SIMPLE_VALUE'   : 'une certaine valeur',
    
    'SR_ENTER_FORMULA'  : 'Veuillez saisir une formule pour calculer la valeur de "$1"',
    'SR_SUBMIT'         : 'Soumettre...',
    'SR_SPECIFY_VARIABLES' : 'Veuillez spécifier les valeurs des variables suivantes dans votre formule :',
    'SR_DERIVED_FACTS'  : 'Faits actuellement dérivés (l\'actualisation peut prendre du temps)',
    'SR_SYNTAX_CHECKED' : '(syntaxe vérifiée)',
    'SR_EDIT_FORMULA'   : 'éditer la formule',
    'SR_NO_VARIABLE'    : 'La formaule ne comporte aucune variable. De telles formules sont dépourvues de sens.',
    'SR_IS_A'           : 'est un/une',
    'SR_PROPERTY_VALUE' : 'valeur de propriété',
    'SR_ABSOLUTE_TERM'  : 'terme absolu',
    'SR_ENTER_VALUE'    : 'Saisir une valeur...',
    'SR_ENTER_PROPERTY' : 'Saisir une propriété...',
    
    'SR_OP_HELP_ENTER'  : 'Saisir une formule mathématique en utilisant les opérateurs suivants :',
    'SR_OP_ADDITION'    : 'Addition',
    'SR_OP_SQUARE_ROOT' : 'Racine carrée',
    'SR_OP_SUBTRACTION' : 'Soustraction',
    'SR_OP_EXPONENTIATE': 'Exponentielle',
    'SR_OP_MULTIPLY'    : 'Multiplication',
    'SR_OP_SINE'        : 'Sinus',
    'SR_OP_DIVIDE'      : 'Division',
    'SR_OP_COSINE'      : 'Cosinus',
    'SR_OP_MODULO'      : 'Modulo',
    'SR_OP_TANGENT'     : 'Tangente',

    //Term Import
    'smw_ti_sourceinfo'     : 'L\'information suivante est nécessaire afin de démarrer l\'importation',
    'smw_ti_source'         : 'Source',
    'smw_ti_edit'           : 'éditer',
    'smw_ti_attributes'     : '<b>Attributs disponibles dans cette source de données</b><br/>Les attributs suivants peuvent être extraits de la source de données définie :',
    'smw_ti_articles1'      : '<b>Articles à  importer à  partir de cette source de données</b><br/>Les articles suivants',
    'smw_ti_noa'            : 'nomArticle',
    'smw_ti_articles2'      : ' vont être générés dans le wiki:',

    'PC_enter_prop'     : 'Saisir une propriété',
    'PC_headline'       : 'La valeur de la propriété $1 de $2 est $3, si',
    'PC_DERIVE_BY'      : 'Dériver la propriété $1 par une règle liant les propriétés',
    
    'smw_wwsu_addwscall'            :   'Ajouter un appel de service web',

	// Semantic Toolbar General
	'STB_LINKS'		: 'Links to other pages',
	'STB_TOOLS'		: 'Semantic toolbar', 
	'STB_FACTS'		: 'Facts about this Article',
	'STB_ANNOTATION_HELP' 	: 'Semantic toolbar' 
    
};
