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
var wgLanguageStrings = {
	'MUST_NOT_BE_EMPTY'       : '(e)This input field must not be empty.',
	'VALUE_IMPROVES_QUALITY'  : '(i)A value in this input field improves the qualitiy of the knowledge base.',
	'SELECTION_MUST_NOT_BE_EMPTY' : '(e)The selection must not be empty!',
	'INVALID_FORMAT_OF_VALUE' : '(e)The value has an invalid format.',
	'INVALID_VALUES'          : 'Invalid values.',
	'NAME'                    : 'Name:',
	'ENTER_NAME'              : 'Please enter a name.',
	'ADD'                     : 'Add',
	'INVALID_VALUES'          : 'Invalid values.',
	'CANCEL'                  : 'Cancel',
	'CREATE'                  : 'Create',
	'ANNOTATE'                : 'Annotate',
	'SUB_SUPER'               : 'Sub/Super',
	'MHAS_PART'               : 'Has part',
	'INVALID_NAME'            : 'Invalid name.',
	'CHANGE'                  : 'Change',
	'DELETE'                  : 'Delete',
	'INPUT_BOX_EMPTY'         : 'Error! Input box is empty.',
	'ERR_QUERY_EXISTS_ARTICLE' : 'Error while querying existence of article <$-page>.',
	'CREATE_PROP_FOR_CAT'     : 'This property was created for category <$cat>. Please enter meaningful content.',
	'NOT_A_CATEGORY'          : 'The current article is not a category.',
	'CREATE_CATEGORY'         : 'This category has been created but not edited. Please enter meaningful content.',
	'CREATE_SUPER_CATEGORY'   : 'This category has been created as super-category but not edited. Please enter meaningful content.',
	'CREATE_SUB_CATEGORY'     : 'This category has been created as sub-category but not edited. Please enter meaningful content.',
	'NOT_A_PROPERTY'          : 'The current article is not a property.',
	'CREATE_PROPERTY'         : 'This property has been created but not edited. Please enter meaningful content.',
	'CREATE_SUB_PROPERTY'     : 'This article has been created as sub-property. Please enter meaningful content.',
	'CREATE_SUPER_PROPERTY'   : 'This article has been created as super-property. Please enter meaningful content.',
	'ERROR_CREATING_ARTICLE'  : "Error while creating article.",
	'UNMATCHED_BRACKETS'      : 'Warning! The article contains syntax errors ("]]" missing)',
	'MAX_CARD_MUST_NOT_BE_0'  : "(e)Max. cardinality must not be 0 or less!",
	'SPECIFY_CARDINALITY'     : "(e)Please specify this cardinality!",
	'MIN_CARD_INVALID'        : "(e)Min. cardinality must be smaller than max. cardinality!",
	'DEFAULT_ROOT_CONCEPT'	  : "Default Root Concept",

	// Namespaces
	'NS_SPECIAL' 			  : 'Special',

	// Relation toolbar
	'ANNOTATE_PROPERTY'       : 'Annotate a property.',
	'PAGE'                    : 'Page:',
	'ANNO_PAGE_VALUE'         : 'Annotated page/value',
	'SHOW'                    : 'Show:',
	'DEFINE_SUB_SUPER_PROPERTY' : 'Define a sub- or super-property.',
	'CREATE_NEW_PROPERTY'     : 'Create a new property.',
	'ENTER_DOMAIN'            : 'Enter a domain.',
	'ENTER_RANGE'             : 'Enter a range.',
	'ENTER_TYPE'              : 'Select a type.',
	'PROP_HAS_PART'           : 'Property:has part', // name of the has-part property
	'HAS_PART'                : 'has part',
	'PROP_HBSU'               : 'Property:has basic structural unit', // name of the property
	'HBSU'                    : 'has basic structural unit',
	'DEFINE_PART_OF'          : 'Define a part-of relation.',
	'OBJECT'                  : 'Object:',
	'RENAME_ALL_IN_ARTICLE'   : 'Rename all in this article.',
	'CHANGE_PROPERTY'         : 'Change a property.',
	'PROPERTIES'              : 'Properties',
	'NO_OBJECT_FOR_POR'       : 'No object for part-of relation given.',
	'RETRIEVE_SCHEMA_DATA'    : 'Failed to retrieve schema Data!',

	// Property properties toolbar
	'PROPERTY_DOES_NOT_EXIST' : '(w)This property does not exist.',
	'PROPERTY_ALREADY_EXISTS' : '(w)This property already exists.',
	'CREATE_SUPER_PROPERTY'   : 'Create "$-title" and make "$t" super-property of "$-title"',
	'CREATE_SUB_PROPERTY'     : 'Create "$-title" and make "$t" sub-property of "$-title"',
	'MAKE_SUPER_PROPERTY'     : 'Make "$t" super-property of "$-title"',
	'MAKE_SUB_PROPERTY'       : 'Make "$t" sub-property of "$-title"',
	'ADD_TYPE'                : 'Add type',
	'ADD_RANGE'               : 'Add range',
	'DOMAIN'                  : 'Domain:',
	'RANGE'                   : 'Range:',
	'INVERSE_OF'              : 'Inverse of:',
	'MIN_CARD'                : 'Min. card.:',
	'MAX_CARD'                : 'Max. card.:',
	'TRANSITIVE'              : 'Transitive',
	'SYMMETRIC'               : 'Symmetric',
	'RETRIEVING_DATATYPES'    : 'Retrieving data types...',
	'TYPE'                    : 'Type:', //also used as namespace identifier with colon
	'PROPERTY_PROPERTIES'     : "Property Properties",
	'CATEGORY'                : "Category:",	//also used as namespace identifier with colon
	'PROPERTY'                : "Property:",	//also used as namespace identifier with colon
	'TEMPLATE'                : "Template:",	//also used as namespace identifier with colon
	'TYPE_PAGE'               : "Type:Page",	// type identifier
	'PAGE_TYPE'               : "page",		// name of the page data type
	'NARY_TYPE'               : "n-ary",       // name of the n-ary data type

	// Category toolbar
	'ANNOTATE_CATEGORY'       : 'Annotate a category.',
	'CATEGORY_DOES_NOT_EXIST' : '(w)This category does not exists.',
	'CATEGORY_ALREADY_EXISTS' : '(w)This category already exists.',
	'CREATE_SUPER_CATEGORY'   : 'Create "$-title" and make "$t" super-category of "$-title"',
	'CREATE_SUB_CATEGORY'     : 'Create "$-title" and make "$t" sub-category of "$-title"',
	'MAKE_SUPER_CATEGORY'     : 'Make "$t" super-category of "$-title"',
	'MAKE_SUB_CATEGORY'       : 'Make "$t" sub-category of "$-title"',
	'DEFINE_SUB_SUPER_CAT'    : 'Define a sub- or super-category.',
	'CREATE_SUB'              : 'Create sub',
	'CREATE_SUPER'            : 'Create super',
	'CREATE_NEW_CATEGORY'     : 'Create a new category.',
	'CHANGE_ANNO_OF_CAT'      : 'Change the annotation of a category.',
	'CATEGORIES'              : 'Categories',

	// Autocompletion
	'AUTOCOMPLETION_HINT'     : 'Dr&uuml;cken Sie Ctrl+Alt+Space um die Auto-completion zu benutzen. (Ctrl+Space im IE)',
	'AC_CLICK_TO_DRAG'        : 'Auto-Completion - Hier zum Verschieben klicken',

	// Combined search
	'ADD_COMB_SEARCH_RES'     : 'Zus&auml;tzliche Ergebnisse der Combined-Search.',
	'COMBINED_SEARCH'         : 'Combined-Search',

	'INVALID_GARDENING_ACCESS' : 'Sie d&uuml;rfen Gardening Bots nicht abbrechen. Das d&uuml;rfen nur Sysops und Gardener.',
	'GARDENING_LOG_COLLAPSE_ALL' : 'Alles einklappen',
	'GARDENING_LOG_EXPAND_ALL'   : 'Alles ausklappen',
	
	// Ontology browser
	'OB_ID'					  : 'OntologyBrowser',
	'ONTOLOGY_BROWSER'        : 'Ontology Browser',
	'PROPERTY_NS_WOC'         : 'Property', // Property namespace without colon
	'RELATION_NS_WOC'         : 'Relation', // Relation namespace without colon
	'CATEGORY_NS_WOC'         : 'Kategorie', // Category namespace without colon
	'KS_NOT_SUPPORTED'        : 'Konqueror/Safari werden momentan nicht unterst&uuml;tzt.',
	'SHOW_INSTANCES'          : 'Zeige Instanzen',
	'HIDE_INSTANCES'          : 'Verstecke Instanzen',
	'ENTER_MORE_LETTERS'      : "Bitte geben Sie mindestens zwei Buchstaben ein. Sonst erhalten Sie wahrscheinlich zu viele Ergebnisse.",
	'MARK_A_WORD'             : 'Selektieren Sie etwas...',
	'OPEN_IN_OB'              : 'Im Ontology Browser &ouml;ffnen',
	'OB_CREATE'	  			  : 'Erzeugen',
	'OB_RENAME'	  			  : 'Umbenennen',
	'OB_DELETE'	  			  : 'L&oumlschen',
	'OB_TITLE_EXISTS'		  : 'Seite existiert bereits',
	'OB_ENTER_TITLE'		  : 'Seitennamen eingeben',
	'OB_SELECT_CATEGORY'	  : 'Erst Kategorie auswählen',
	'OB_SELECT_PROPERTY'	  : 'Erst Property auswählen',
	'OB_SELECT_INSTANCE'	  : 'Erst Instanz auswählen',

	// Query Interface
	'QUERY_INTERFACE'         : 'Query Interface',
	'QI_MAIN_QUERY_NAME'	  : 'Hauptquery',
	'QI_ARTICLE_TITLE'        : 'Artikel',
	'QI_EMPTY_QUERY'       	  : 'Ihr Query ist leer.',
	'QI_INSTANCE'       	  : 'Instanz:',
	'QI_PROPERTYNAME'         : 'Propertyname:',
	'QI_SHOW_PROPERTY'        : 'In Ergebnissen zeigen:',
	'QI_USE_SUBQUERY'         : 'Subquery einf&uuml;gen',
	'QI_PAGE'				  : 'Page', // has to be the same as the Type:Page in your language
	'QI_OR'        			  : 'oder',
	'QI_ENTER_CATEGORY'       : 'Bitte geben Sie eine Kategorie ein',
	'QI_ENTER_INSTANCE'       : 'Bitte geben Sie eine Instanz ein',
	'QI_ENTER_PROPERTY_NAME'  : 'Bitte geben Sie einen Propertynamen ein',
	'QI_CLIPBOARD_SUCCESS'    : 'Der Query wurde in Ihre Zwischenablage kopiert',
	'QI_CLIPBOARD_FAIL'    	  : "Ihr Browser erlaubt keinen Zugriff auf die Zwischenablage\nDer Query konnte nicht in Ihre Zwischenablage kopiert werden.",
	'QI_SUBQUERY'    	  	  : "Subquery",
	'QI_CATEGORIES'    	  	  : " Kategorien:",
	'QI_INSTANCES'    	  	  : " Instanzen:",
	'QI_QUERY_EXISTS'		  : "Ein Query mit diesem Namen existiert bereits. Bitte w&auml;hlen sie einen neuen Namen.",
	'QI_QUERY_SAVED'		  : "Ihr Query wurde erfolgreich gespeichert",
	'QI_SAVE_ERROR'		  	  : "Ein unbekannter Fehler ist aufgetreten. Ihr Query konnte nicht gespeichert werden.",

	//Syntax Highlighting editor
	'SHE_new_document'			  : "neues leeres Dokument",
	'SHE_search_button'			  : "suchen und ersetzen",
	'SHE_search_command'		  : "suche n&auml;chsten / &ouml;ffne Suchfeld",
	'SHE_search'				  : "suche",
	'SHE_replace'				  : "ersetze",
	'SHE_replace_command'		  : "ersetze / &ouml;ffne Suchfeld",
	'SHE_find_next'				  : "finde n&auml;chsten",
	'SHE_replace_all'			  : "ersetze alle Treffer",
	'SHE_reg_exp'				  : "regul&auml;re Ausdr&uuml;cke",
	'SHE_match_case'			  : "passt auf den Begriff<br />",
	'SHE_not_found'				  : "Nicht gefunden.",
	'SHE_occurrence_replaced'	  : "Die Vorkommen wurden ersetzt.",
	'SHE_search_field_empty'	  : "leeres Suchfeld",
	'SHE_restart_search_at_begin' : "Ende des zu durchsuchenden Bereiches erreicht. Es wird die Suche von Anfang an fortgesetzt.",
	'SHE_move_popup'			  : "Suchfenster bewegen",
	'SHE_font_size'				  : "--Schriftgr&ouml;&szlig;e--",
	'SHE_go_to_line'			  : "gehe zu Zeile",
	'SHE_go_to_line_prompt'		  : "gehe zu Zeilennummmer:",
	'SHE_undo'					  : "r&uuml;ckg&auml;ngig machen",
	'SHE_redo'					  : "wiederherstellen",
	'SHE_change_smooth_selection' : "aktiviere/deaktiviere einige Features (weniger Bildschirmnutzung aber mehr CPU-Belastung)",
	'SHE_highlight'				  : "Syntax Highlighting an- und ausschalten",
	'SHE_reset_highlight'		  : "Highlighting zur&uuml;cksetzen (falls mit Text nicht konform)",
	'SHE_help'					  : "&uuml;ber",
	'SHE_save'					  : "sichern",
	'SHE_load'					  : "&ouml;ffnen",
	'SHE_line_abbr'				  : "Ln",
	'SHE_char_abbr'				  : "Ch",
	'SHE_position'				  : "Position",
	'SHE_total'					  : "Gesamt",
	'SHE_close_popup'			  : "Popup schlie&szligen",
	'SHE_shortcuts'				  : "Shortcuts",
	'SHE_add_tab'				  : "Tab zum Text hinzuf&uuml;gen",
	'SHE_remove_tab'			  : "Tab aus Text entfernen",
	'SHE_about_notice'			  : "Bemerkung: Syntax Highlighting ist nur f&uuml;r kurze Texte",
	'SHE_toggle'				  : "Syntax Highlighting an- und ausschalten",
	'SHE_accesskey'				  : "Accesskey",
	'SHE_tab'					  : "Tab",
	'SHE_shift'					  : "Shift",
	'SHE_ctrl'					  : "Ctrl",
	'SHE_esc'					  : "Esc",
	'SHE_processing'			  : "In Bearbeitung...",
	'SHE_fullscreen'			  : "fullscreen"
};
