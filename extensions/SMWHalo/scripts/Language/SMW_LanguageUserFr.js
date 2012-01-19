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
*  @file
* 
*  @ingroup SMWHaloLanguage
*/

window.wgUserLanguageStrings = {
	'MUST_NOT_BE_EMPTY'       : '(e)Ce champ ne doit pas être vide.',
	'VALUE_IMPROVES_QUALITY'  : '(i)Une valeur dans ce champ améliore la qualité de la base de connaissance.',
	'SELECTION_MUST_NOT_BE_EMPTY' : '(e)La sélection ne doit pas être vide !',
	'INVALID_FORMAT_OF_VALUE' : '(e)Le format de la valeur est invalide.',
	'INVALID_VALUES'          : 'Valeurs invalides.',
	'EditProperty'            : 'Modification attribut :',
	'NAME'                    : 'Nom:',
	'SUBCATEGORYOF'           : 'Sous-catégorie de: (séparés par des virgules)',
	'ANNOTATED_CATEGORIES'     : 'Catégories annotées: (séparés par des virgules)',
	'AddCategory'             : 'Ajouter',
	'ENTER_NAME'              : 'Veuillez saisir un nom.',
	'ADD'                     : 'Ajouter',
	'INVALID_VALUES'          : 'Valeurs invalides.',
	'CANCEL'                  : 'Annuler',
	'CREATE'                  : 'Créer',
	'EDIT'                    : 'Modifier',
	'ANNOTATE'                : 'Annoter',
	'SUB_SUPER'               : 'Sous/Sur',
	'INVALID_NAME'            : 'Nom invalide.',
	'CHANGE'                  : 'Modifier',
	'DELETE'                  : 'Supprimer',
	'INPUT_BOX_EMPTY'         : 'Erreur ! La zone de saisie est vide.',
	'ERR_QUERY_EXISTS_ARTICLE' : 'Une erreur est survenue lors de la vérification de l\'existence de l\'article <$-page>.',
	'CREATE_PROP_FOR_CAT'     : 'Cet attribut a été créé pour la catégorie <$cat>. Veuillez saisir un contenu significatif.',
	'NOT_A_CATEGORY'          : 'L\'article courant n\'est pas une catégorie.',
	'CREATE_CATEGORY'         : 'Cette catégorie a été créée mais n\'a pas été éditée. Veuillez saisir un contenu significatif.',
	'CREATE_SUPER_CATEGORY'   : 'Cette catégorie a été créée en tant que sur-catégorie mais n\'a pas été éditée. Veuillez saisir un contenu significatif.',
	'CREATE_SUB_CATEGORY'     : 'Cette catégorie a été créée en tant que sous-catégorie mais n\'a pas été éditée. Veuillez saisir un contenu significatif.',
	'NOT_A_PROPERTY'          : 'L\'article courant n\'est pas un attribut.',
	'CREATE_PROPERTY'         : 'Cet attribut a été créé mais n\'a pas été éditée. Veuillez saisir un contenu significatif.',
	'CREATE_SUB_PROPERTY'     : 'Cet article a été créé en tant que sous-attribut. Veuillez saisir un contenu significatif.',
	'CREATE_SUPER_PROPERTY'   : 'Cet article a été créé en tant que sur-attribut. Veuillez saisir un contenu significatif.',
	'ERROR_CREATING_ARTICLE'  : "Une erreur est survenue lors de la création de l\'article.",
	'ERROR_EDITING_ARTICLE'   : "Une erreur est survenue lors de l\'édition de l\'article.",
	'UNMATCHED_BRACKETS'      : 'Attention! L\'article contient des erreurs de syntaxe ("]]" manquant)',
	'MAX_CARD_MUST_NOT_BE_0'  : "(e)La cardinalité maximale ne doit pas être inférieur ou égale à 0 !",
	'SPECIFY_CARDINALITY'     : "(e)Veuillez spécifier cette cardinalité !",
	'MIN_CARD_INVALID'        : "(e)La cardinalité minimale doit être inférieure à la cardinalité maximale !",
	'ASSUME_CARDINALITY_0'    : "(i) La cardinalité minimale est supposée être 0.",
	'ASSUME_CARDINALITY_INF'  : "(i) La cardinalité maximale est supposée être infinie.",

	// Namespaces
	'NS_SPECIAL' 			  : 'Special',

	// Relation toolbar
	'ANNOTATE_PROPERTY'       : 'Annoter un attribut.',
	'PAGE'                    : 'Page:',
	'ANNO_PAGE_VALUE'         : 'Page annotée/valeur',
	'SHOW'                    : 'Afficher:',
	'DEFINE_SUB_SUPER_PROPERTY' : 'Définir un sous-attribut ou un sur-attribut.',
	'CREATE_NEW_PROPERTY'     : 'Créer un nouvel attribut.',
	'ENTER_DOMAIN'            : 'Saisir un domaine.',
	'ENTER_RANGE'             : 'Saisir un champ de valeurs.',
	'ENTER_TYPE'              : 'Sélectionner un type.',
	'RENAME_ALL_IN_ARTICLE'   : 'Tout renommer dans cet article.',
	'CHANGE_PROPERTY'         : 'Modifier un attribut.',
	'PROPERTIES'              : 'Attributs',
	'RETRIEVE_SCHEMA_DATA'    : 'Echec lors de la récupération du schéma de données !',
	'RECPROP'                 : "Proprietés recommandées",
	

	// Property characteristics toolbar
	'PROPERTY_DOES_NOT_EXIST' : '(w)Cet attribut n\'existe pas.',
	'PROPERTY_ALREADY_EXISTS' : '(w)Cet attribut existe déjà.',
	'PROPERTY_NAME_TOO_LONG'  : '(e)Le nom de cet attribut est trop long ou contient des caractères invalides.',
	'PROPERTY_VALUE_TOO_LONG' : '(w)Cette valeur est très longue. Elle sera sauvegardée parmi les attributs de type "Type:Texte".',
	'PROPERTY_ACCESS_DENIED'  : '(e)Vous n\'êtes pas autorisé à annoter cet attribut.',	
	'PROPERTY_ACCESS_DENIED_TT': 'Vous n\'êtes pas autorisé à annoter cet attribut.',	
	'CANT_SAVE_FORBIDDEN_PROPERTIES': 'Cet article contient des attributs protégés en écriture et ne peut pas être sauvegardé.',
	'CREATE_SUPER_PROPERTY'   : 'Créer "$-title" et faire de "$sftt" une sur-attribut de "$-title"',
	'CREATE_SUB_PROPERTY'     : 'Créer "$-title" et faire de "$sftt" une sous-attribut de "$-title"',
	'MAKE_SUPER_PROPERTY'     : 'Faire de "$sftt" une sur-attribut de "$-title"',
	'MAKE_SUB_PROPERTY'       : 'Faire de "$sftt" une sous-attribut de "$-title"',
	'ADD_RECORD_FIELD'        : 'Ajouter un attribut à l\'enregistrement',
	'ADD_DOMAIN_RANGE'        : 'Ajouter un domaine et un champ de valeurs',
	'DOMAIN'                  : 'Domaine:',
	'RANGE'                   : 'Champ de valeurs:',
	'INVERSE_OF'              : 'Inverse de:',
	'Mandatory'               : 'Obligatoire:',
	'TRANSITIVE'              : 'Transitif',
	'SYMMETRIC'               : 'Symétrique',
	'RETRIEVING_DATATYPES'    : 'Récupération des types de données...',
	'NARY_ADD_TYPES'		  : '(e) Veuillez ajouter des types ou des champs de valeurs',
	'REMOVE_DOMAIN_RANGE'	  : 'Supprimer ce domaine et ce champ de valeurs',
	'DUPLICATE_RECORD_FIELD'  : '(w)Cet attribut apparaît plusieurs fois dans l\'enregistrement. L\'ordre des valeurs annotées ne sera pas déterministe.',
	
	'PROPERTY_PROPERTIES'     : "Caractéristiques de l'attribut",
	
	
	'PAGE_TYPE'               : "page",		// name of the page data type
	'NARY_TYPE'               : "arité n",       // name of the n-ary data type
	'SPECIFY_PROPERTY'		  : "Annoter un attribut.",
	'PC_DUPLICATE'			  : "Au moins un attribut est spécifiée plusieurs fois. Veuillez supprimer les doublons",
	'PC_HAS_TYPE'			  : "A le type", 
	'PC_HAS_FIELDS'			  : "A pour attributs", 
	'PC_MIN_CARD'			  : "A pour cardinalité maximum",
	'PC_MAX_CARD'			  : "A pour cardinalité minimum",
	'PC_INVERSE_OF'			  : "Est l'inverse de", 
	'PC_INVERSE'			  : "inverse", 
	'PC_TRANSITIVE'			  : "transitif", 
	'PC_SYMMETRICAL'		  : "symétrique", 
	'PC_AND'			 	  : "et", 
	'PC_UNSUPPORTED'		  : "Ce wiki ne supporte pas les attributs de $1.",
	

	// Category toolbar
	'ANNOTATE_CATEGORY'       : 'Annoter une catégorie',
	'CATEGORY_DOES_NOT_EXIST' : '(w)Cette catégorie n\'existe pas.',
	'CATEGORY_ALREADY_EXISTS' : '(w)Cette catégorie existe déjà.',
	'CATEGORY_NAME_TOO_LONG'  : '(e)Le nom de cette catégorie est trop long ou contient des caractères invalides.',
	'CREATE_SUPER_CATEGORY'   : 'Créer "$-title" et faire de "$sftt" une sur-catégorie de "$-title"',
	'CREATE_SUB_CATEGORY'     : 'Créer "$-title" et faire de "$sftt" une sous-catégorie de "$-title"',
	'MAKE_SUPER_CATEGORY'     : 'Faire de "$sftt" une sur-catégorie de "$-title"',
	'MAKE_SUB_CATEGORY'       : 'Faire de "$sftt" une sous-catégorie de "$-title"',
	'DEFINE_SUB_SUPER_CAT'    : 'Définir une sous-catégorie ou sur-catégorie.',
	'CREATE_SUB'              : 'Créer une sous',
	'CREATE_SUPER'            : 'Créer une sur',
	'CREATE_NEW_CATEGORY'     : 'Créer une nouvelle catégorie.',
	'CHANGE_ANNO_OF_CAT'      : 'Changer l\'annotation d\'une catégorie.',
	'CATEGORIES'              : 'Catégories',
	'ADD_AND_CREATE_CAT'      : 'Ajouter et créer',
	'CATEGORY_ALREADY_ANNOTATED': '(w)Cette catégorie est déjà annotée.',

	// Annotation hints
	'ANNOTATION_HINTS'        : 'Indications d\'annotation',
	'ANNOTATION_ERRORS'       : 'Erreurs d\'annotation',
	'AH_NO_HINTS'			  : '(i)Aucune indication pour cet article.',
	'AH_SAVE_COMMENT'		  : 'Les annotations ont été ajoutées dans le mode d\'annotation avancé.',
	'AAM_SAVE_ANNOTATIONS' 	  : 'Voulez-vous enregistrer les annotations de la session courante ?',
	'CAN_NOT_ANNOTATE_SELECTION' : 'Il est impossible d\'annoter la sélection. Elle contient déjà des annotations ou des paragraphes ou finit par un lien.',
	'AAM_DELETE_ANNOTATIONS'  : '�`tes-vous sûr de vouloir supprimer cette annotation ?',
	
	// Save annotations
	'SA_SAVE_ANNOTATION_HINTS': "N\'oubliez pas de sauvegarder votre travail !",
	'SA_SAVE_ANNOTATIONS'	  : 'Sauvegarder les annotations',
	'SA_SAVE_ANNOTATIONS_AND_EXIT' : 'Sauvegarder & quitter',
	'SA_ANNOTATIONS_SAVED'	  : '(i) Les annotations ont été sauvegardées avec succès.',
	'SA_SAVING_ANNOTATIONS_FAILED' : '(e) Une erreur est survenue lors de la sauvegarde des annotations.',
	'SA_SAVING_ANNOTATIONS'   : '(i) Sauvegarde des annotations en cours...',

	// Queries in STB
	'QUERY_HINTS'			  : 'Requêtes en ligne',
	'NO_QUERIES_FOUND'		  : '(i) Pas de requêtes trouvées dans cet article',

	// Autocompletion
	'AUTOCOMPLETION_HINT'     : 'Appuyez sur les touches Ctrl+Alt+Espace pour utiliser l\'auto-complétion. (Ctrl+Espace sous IE)',
	'WW_AUTOCOMPLETION_HINT'  : '- pris en charge uniquement dans le mode wiki texte',
	'AC_CLICK_TO_DRAG'        : 'Auto-complétion',
	'AC_MORE_RESULTS_AVAILABLE' : 'Trop de résultats...',
	'AC_MORE_RESULTS_TOOLTIP' : 'Trop de résultats. Veuillez élargir votre terme de recherche pour obtenir moins de résultats.',
	'AC_NO_RESULTS': 'Pas de résultats',
	'AC_ALL' : 'Auto-complétion pour toutes les pages',
	'AC_QUERY' : 'Requête-ASK',
	'AC_SCHEMA_PROPERTY_DOMAIN' : 'Tous les attributs avec le domaine: ',
	'AC_SCHEMA_PROPERTY_RANGE_INSTANCE' : 'Tous les attributs qui ont un champ de valeurs d\'instance de: ',
	'AC_DOMAINLESS_PROPERTY' : 'Tous les attributs sans domaine',
	'AC_ANNOTATION_PROPERTY' : 'Les attributs qui sont utilisés sur les pages de catégorie: ',
	'AC_ANNOTATION_VALUE' : 'Tous les attributs avec des valeurs annotées: ',
	'AC_INSTANCE_PROPERTY_RANGE' : 'Toutes les instances qui sont membres de champs de valeurs: ',
	'AC_NAMESPACE' : 'Toutes les pages dans les espaces de noms: ',
	'AC_LEXICAL' : 'Toutes les pages contenant: ',
	'AC_SCHEMA_PROPERTY_TYPE' : 'Tous les attributs de type: ',
	'AC_ASF' : 'Catégories pour lesquelles des formulaires sémantiques automatiques peuvent être créés',
	'AC_FROM_BUNDLE' : 'Pages en provenance de bundle: ',
	

	// Combined search
	'ADD_COMB_SEARCH_RES'     : 'Résultats additionnels de la recherche combinée.',
	'COMBINED_SEARCH'         : 'Recherche combinée',

	'INVALID_GARDENING_ACCESS' : 'Vous n\'êtes pas autorisés à annuler les robots. Seuls les administrateurs et les gardeners y sont autorisés.',
	'GARDENING_LOG_COLLAPSE_ALL' : 'Tout réduire',
	'GARDENING_LOG_EXPAND_ALL'   : 'Tout développer',
	'BOT_WAS_STARTED'			: 'Le robot a été démarré !',
	
	// Data Explorer
	'OB_ID'					  : 'DataExplorer',
	'ONTOLOGY_BROWSER'        : 'Explorateur de données',
	
	'KS_NOT_SUPPORTED'        : 'Konqueror n\'est pas actuellement supporté !',
	'SHOW_INSTANCES'          : 'Afficher les instances',
	'HIDE_INSTANCES'          : 'Cacher les instances',
	'ENTER_MORE_LETTERS'      : "Veuillez saisir au moins deux lettres. Sinon vous allez obtenir trop de résultats.",
	'MARK_A_WORD'             : 'Marquer un mot...',
	'OPEN_IN_OB'              : 'Ouvrir dans l\'explorateur de données',
	'OPEN_IN_OB_NEW_TAB'      : '... nouvel onglet',
	'OB_CREATE'	  			  : 'Créer',
	'OB_RENAME'	  			  : 'Renommer',
	'OB_DELETE'	  			  : 'Supprimer',
	'OB_PREVIEW' 			  : 'Aperçu',
	'OB_TITLE_EXISTS'		  : 'Cet élément existe !',
	'OB_CATEGORY_EXISTS'              : 'n\'existe pas !',
	'OB_SUP_NOT_VALID'                : 'ne peut pas être une sous-catégorie de:',
	'OB_TITLE_NOTEXISTS'		  : 'Cet élément n\'existe pas !',
	'OB_ENTER_TITLE'		  : 'Saisir le titre',
	'OB_SAVE_CHANGES'         : 'Sauvegarder les modifications',
	'SAVE_CHANGES'            : 'Sauvegarder',
	'OB_ENTER_RANGE'          : 'Saisir un champ de valeurs',
	'OB_SELECT_CATEGORY'	  : 'Sélectionner la catégorie en premier',
	'OB_SELECT_PROPERTY'	  : 'Sélectionner l\'attribut en premier',
	'OB_SELECT_INSTANCE'	  : 'Sélectionner l\'instance en premier',
	'OB_WRONG_MAXCARD'		  : 'Cardinalité maximum invalide',
	'OB_WRONG_MINCARD'		  : 'Cardinalité minimum invalide',
	'OB_CONFIRM_INSTANCE_DELETION' : '�`tes-vous sûr de vouloir supprimer cet article ?',
	'SMW_OB_OPEN' 			  : 'ouvrir',
	'SMW_OB_EDIT' 			  : 'modifier',
	'SMW_OB_DELETE'                   : 'supprimer',
	'SMW_OB_ADDSOME'		  : '(ajouter quelques)',
	'OB_CONTAINS_FURTHER_PROBLEMS' : 'Contient des problèmes supplémentaires',
	'SMW_OB_MODIFIED'		  : 'La page a été modifiée. Les problèmes suivants de Gardening auraient déjà dûs être résolus:',
	'ADD_ANNOTATION'          : 'Ajouter Annotation',
	'ADD_TYPE'          	  : 'Définir le type',
	'ADD_RANGE'          	  : 'Définir le champ de valeurs',
	'SUBPROPERTYOF'           : 'Sous-attribut de: (séparés par des virgules)',
	'ADDPROPERTY' 			  : 'Ajouter',	
	
	'ERROR_RENAMING_ARTICLE'  : 'Erreur durant le renommage',
	'OB_RENAME_WARNING' 	  : 'Les opérations de renommage ne touchent en aucune façon les données d\'instance.\nCela signifie que les requêtes effectuées avec le TSC ne pourront plus fonctionner comme prévu après cette opération.\nVoulez-vous quand même continuer ?',
	
	// Data Explorer metadata
	'SMW_OB_META_PROPERTY'	  : 'Meta-attribut',
	'SMW_OB_META_PROPERTY_VALUE' : 'Valeur',
	'SMW_OB_META_COMMAND_SHOW'  : 'Afficher les les méta-données',
	'SMW_OB_META_COMMAND_RATE'  : '�0valuer ce fait',
	
	// metaproperties
	'SMW_OB_META_SWP2_AUTHORITY'   : 'Autorité',
	'SMW_OB_META_SWP2_KEYINFO'   : 'Information sur la clé',
	'SMW_OB_META_SWP2_SIGNATURE'   : 'Signature',
	'SMW_OB_META_SWP2_SIGNATURE_METHOD'   : 'Méthode de signature',
	'SMW_OB_META_SWP2_VALID_FROM'   : 'Valide à partir du',
	'SMW_OB_META_SWP2_VALID_UNTIL'   : 'Valide jusqu\'au',
	
	'SMW_OB_META_DATA_DUMP_LOCATION_FROM'   : 'Emplacement de vidage du dump',
	'SMW_OB_META_HOMEPAGE_FROM'   : 'Page d\'accueil',
	'SMW_OB_META_SAMPLE_URI_FROM'   : 'Exemple d\'URI',
	'SMW_OB_META_SPARQL_ENDPOINT_LOCATION_FROM'   : 'Point final SPARQL',
	'SMW_OB_META_DATASOURCE_VOCABULARY_FROM'   : 'Vocabulaire',
	'SMW_OB_META_DATASOURCE_ID_FROM'   : 'ID',
	'SMW_OB_META_DATASOURCE_CHANGEFREQ_FROM'   : 'Fréquence de rafraichissement',
	'SMW_OB_META_DATASOURCE_DESCRIPTION_FROM'   : 'Description',
	'SMW_OB_META_DATASOURCE_LABEL_FROM'   : '�0tiquette',
	'SMW_OB_META_DATASOURCE_LASTMOD_FROM'   : 'Dernier changement',
	'SMW_OB_META_DATASOURCE_LINKEDDATA_PREFIX_FROM'   : 'Préfixe des données liées',
	'SMW_OB_META_DATASOURCE_URIREGEXPATTERN_FROM'   : 'Schéma d\'URI',

	'SMW_OB_META_DATA_DUMP_LOCATION_TO'   : 'Emplacement de vidage du dump',
	'SMW_OB_META_HOMEPAGE_TO'   : 'Page d\'accueil',
	'SMW_OB_META_SAMPLE_URI_TO'   : 'Exemple d\'URI',
	'SMW_OB_META_SPARQL_ENDPOINT_LOCATION_TO'   : 'Point final SPARQL',
	'SMW_OB_META_DATASOURCE_VOCABULARY_TO'   : 'Vocabulaire',
	'SMW_OB_META_DATASOURCE_ID_TO'   : 'ID',
	'SMW_OB_META_DATASOURCE_CHANGEFREQ_TO'   : 'Fréquence de rafraichissement',
	'SMW_OB_META_DATASOURCE_DESCRIPTION_TO'   : 'Description',
	'SMW_OB_META_DATASOURCE_LABEL_TO'   : '�0tiquette',
	'SMW_OB_META_DATASOURCE_LASTMOD_TO'   : 'Dernier changement',
	'SMW_OB_META_DATASOURCE_LINKEDDATA_PREFIX_TO'   : 'Préfixe des données liées',
	'SMW_OB_META_DATASOURCE_URIREGEXPATTERN_TO'   : 'Schéma d\'URI',
	
	'SMW_OB_META_IMPORT_GRAPH_CREATED'   : 'Le graphique a été créé le',
	'SMW_OB_META_IMPORT_GRAPH_REVISION_NO'   : 'Numéro de révision',
	'SMW_OB_META_IMPORT_GRAPH_LAST_CHANGED_BY'   : 'Dernier changement',
	'SMW_OB_META_RATING_VALUE'   : 'Valeur de l\'évaluation',
	'SMW_OB_META_RATING_USER'   : '�0valué par l\'utilisateur',
	'SMW_OB_META_RATING_CREATED'   : 'L\'évaluation a été créé le',
	'SMW_OB_META_RATING_ASSESSMENT'   : '�0valuation',
	

	// Query Interface
	'QUERY_INTERFACE'         : 'Interface de requêtes',
	'QI_MAIN_QUERY_NAME'	  : 'Requête principale',
	'QI_ARTICLE_TITLE'        : 'Titre de l\'article',
	'QI_EMPTY_QUERY'       	  : 'Votre requête est vide.',
	'QI_INSTANCE'       	  : 'Instance:',
	'QI_PROPERTYNAME'         : 'Nom de l\'attribut:',
    'QI_PROPERTYVALUE'        : 'Valeur de l\'attribut:',
	'QI_SHOW_PROPERTY'        : 'Afficher dans les résultats',
	'QI_PROPERTY_MUST_BE_SET' : 'La valeur doit être définie',
	'QI_USE_SUBQUERY'         : 'Insérer une sous-requête',
	'QI_PAGE'				  : 'Page',
	'QI_OR'        			  : 'ou',
	'QI_ENTER_CATEGORY'       : 'Veuillez saisir une catégorie',
	'QI_ENTER_INSTANCE'       : 'Veuillez saisir une instance',
	'QI_ENTER_PROPERTY_NAME'  : 'Veuillez saisir un nom d\'attribut',
	'QI_CLIPBOARD_SUCCESS'    : 'La requête a été copiée avec succès dans votre presse-papier',
	'QI_CLIPBOARD_FAIL'    	  : 'Votre navigateur n\'autorise pas l\'accès au presse-papier.\nLa requête texte ne peut pas être copiée dans votre presse-papier.\nVeuillez utiliser la fonction "Afficher l\'intégralité de la requête" et ensuite copier manuellement la requête.',
	'QI_SUBQUERY'    	  	  : "Sous-requête",
	'QI_CATEGORIES'    	  	  : " Catégories:",
	'QI_INSTANCES'    	  	  : " Instances:",
	'QI_QUERY_EXISTS'		  : "Une requête portant se nom existe déjà Veuillez choisir un autre nom.",
	'QI_QUERY_SAVED'		  : "Votre requête a été sauvegardée avec succès.",
	'QI_SAVE_ERROR'		  	  : "Une erreur inconnue est survenue. Votre requête ne peut pas être sauvegardée.",
	'QI_EMPTY_TEMPLATE'		  : "Afin d'utiliser le format 'modèle', vous devez saisir un nom de modèle.",
	'QI_SPECIAL_QP_PARAMS'    : 'Options pour',
    'QI_START_CREATING_QUERY' : 'Cliquez sur<ul><li>Ajouter Catégorie</li><li>Ajouter Propriéte</li><li>Ajouter Instance</li></ul>pour commencer à créer votre requête.',
    'QI_BC_ADD_CATEGORY'      : 'Ajouter catégorie',
    'QI_BC_ADD_PROPERTY'      : 'Ajouter attribut',
    'QI_BC_ADD_INSTANCE'      : 'Ajouter instance',
    'QI_BC_EDIT_CATEGORY'     : 'Modifier catégorie',
    'QI_BC_EDIT_PROPERTY'     : 'Modifier attribut',
    'QI_BC_EDIT_INSTANCE'     : 'Modifier instance',
    'QI_BC_ADD_OTHER_CATEGORY': 'Ajouter une autre catégorie (OU)',
    'QI_BC_ADD_OTHER_INSTANCE': 'Ajouter une autre instance (OU)',
    'QI_DC_ADD_OTHER_RESTRICT': 'Ajouter une autre restriction (OR)',
    'QI_CAT_ADDED_SUCCESSFUL' : 'Catégorie ajouté avec succès à la requête',
    'QI_PROP_ADDED_SUCCESSFUL': 'Attribut ajouté avec succès à la requête',
    'QI_INST_ADDED_SUCCESSFUL': 'Instance ajouté avec succès à la requête',
    'QI_CREATE_PROPERTY_CHAIN': 'Créer une chaîne d\'attributs en ajoutant un autre attribut',
    'QI_ADD_PROPERTY_CHAIN'   : 'Ajouter un autre attribut à la chaîne d\'attributs',
    'QI_PROP_VALUES_RESTRICT' : 'Restriction',
    'QI_SPECIFIC_VALUE'       : 'Valeur spécifique',
    'QI_NONE'                 : 'Aucun',
    'QI_PROPERTY_TYPE'        : 'Type',
    'QI_PROPERTY_RANGE'       : 'Champ de valeurs',
    'QI_COLUMN_LABEL'         : '�0tiquette de colonne',
    'QI_SHOWUNIT'             : 'Unité',
    'QI_EQUAL'                : 'égal',
    'QI_LT'                   : 'moins',
    'QI_GT'                   : 'plus grand',
    'QI_NOT'                  : 'pas',
    'QI_LIKE'                 : 'comme',
    'QI_BUTTON_ADD'           : 'Ajouter',
    'QI_BUTTON_UPDATE'        : 'Actualiser',
    'QI_ALL_VALUES'           : 'toutes les valeurs',
    'QI_SUBQUERY_TEXT'        : 'Une nouvelle sous-requête vide sera créé après avoir appuyé sur le bouton "Ajouter" ou "Actualiser". Vous pourrez ensuite y définir des restrictions spécifiques.',
    'QI_TT_SHOW_IN_RES'       : 'La valeur de l\'attribut sera affichée dans le résultat.',
    'QI_TT_MUST_BE_SET'       : 'Seuls les résultats où les valeur des attributs sont définies seront inclus dans la requête.',
    'QI_TT_NO_RESTRICTION'    : 'Aucune restriction sur la valeur des attributs. Toutes les valeurs des attributs seront incluses dans la requête.',
    'QI_TT_VALUE_RESTRICTION' : 'Les valeurs des attributs doivent remplir des critères spécifiques.',
    'QI_TT_SUBQUERY'          : 'Les restrictions pour les valeurs des attributs seront définies dans une requête supplémentaire.',
    'QI_TT_ADD_CHAIN'         : 'Vous pouvez construire une chaîne d\'attributs pour connecter facilement les attributs qui peuvent être liés ensemble. Par exemple, vous pouvez créer une chaîne d\'attributs avec les attributs &quot;Situé dans&quot; et &quot;Membre de&quot;, où la valeur du deuxième attribut est limité à &quot;UE&quot;, pour trouver tout ce qui est situé dans quelque chose qui est membre de l\'UE.',
    'QI_QP_PARAM_intro'       : 'en-tête',
    'QI_QP_PARAM_outro'       : 'pied de page',
    'QI_NOT_SPECIFIED'        : 'Non spécifié',
    'QI_NO_QUERIES_FOUND'     : 'Votre recherche ne correspond à aucune requêtes dans le wiki',
    'QI_SPARQL_NOT_SUPPORTED' : 'Les requêtes SPARQL ne peuvent pas être édité dans l\'interface de requêtes.',
	
	// Find work
	'FW_SEND_ANNOTATIONS'	  : 'Merci d\'évaluer les annotations, ',
	'FW_MY_FRIEND'	  		  : 'mon ami !',
	
	// Wiki text parser
	'WTP_TEXT_NOT_FOUND'		  : "'$1' n'a pas été trouvé dans le texte wiki.",
	'WTP_NOT_IN_NOWIKI'			  : "'$1' est une partie de la section &lt;nowiki&gt;.\nCeci ne peut donc pas être annoté.",
	'WTP_NOT_IN_TEMPLATE'		  : "'$1' est une partie d'un modèle.\nCeci ne peut donc pas être annoté.",
	'WTP_NOT_IN_ANNOTATION'		  : "'$1' est une partie d'une annotation.\nCeci ne peut donc pas être annoté.",
	'WTP_NOT_IN_QUERY'            : "'$1' est une partie d'une requête.\nCeci ne peut donc pas être annoté.",
        'WTP_NOT_IN_TAG'                  : "'$1' est à l\'intérieur d'un tag $2.\nCeci ne peut donc pas être annoté.",
	'WTP_NOT_IN_PREFORMATTED'	  : "'$1' est une partie d'un texte préformaté.\nCeci ne peut donc pas être annoté.",
	'WTP_SELECTION_OVER_FORMATS'  : "La sélection comprend différents formats:\n$1",
	
	// ACL extension
	'smw_acl_*' : '*',
	'smw_acl_read' : 'lire',
	'smw_acl_edit' : 'modifier',
	'smw_acl_create' : 'créer',
	'smw_acl_move' : 'déplacer',
	'smw_acl_permit' : 'autoriser',
	'smw_acl_deny' : 'interdire',
	'smw_acl_create_denied' : 'Vous n\'êtes pas autorisé à créer l\'article "$1".',
	'smw_acl_edit_denied' : 'Vous n\'êtes pas autorisé à modifier l\'article "$1".',
	'smw_acl_delete_denied' : 'Vous n\'êtes pas autorisé à supprimer l\'article "$1".',
	
	
	// Treeview
    'smw_stv_browse' : 'browse',
    
	// former content
	'PROPERTY_NS_WOC'         : 'Attribut', // Property namespace without colon
	'RELATION_NS_WOC'         : 'Relation', // Relation namespace without colon
	'CATEGORY_NS_WOC'         : 'Catégorie', // Category namespace without colon
	'PROPERTY_TYPE'           : 'Type',
	
	'CATEGORY'                : "Catégorie:",
	'PROPERTY'                : "Attribut:",
	'TEMPLATE'                : "Modèle:",	
	'TYPE'                    : 'Type:',

	
	
	'smw_wwsu_addwscall'			:	'Ajouter un appel de service web',
	'smw_wwsu_headline'			:	'Service web',
	'Help'			:	'Aide',
	
	// Derived facts
	'DF_REQUEST_FAILED' : 'Erreur ! Impossible de retrouver les faits dérivés.',
	
	// Semantic Toolbar General
	'STB_LINKS'				: 'Liens vers d\'autres pages',
	'STB_TOOLS'				: 'Outils sémantiques', 
	'STB_FACTS'				: 'Faits concernant cet article',
	'STB_ANNOTATION_HELP' 	: 'Outils sémantiques',
	'STB_TITLE'				: 'Barre d\'outils SMW+'
};