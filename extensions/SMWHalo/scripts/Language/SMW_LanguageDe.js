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
	'MUST_NOT_BE_EMPTY'       : '(e)Dieses Eingabefeld darf nicht leer sein.',
	'VALUE_IMPROVES_QUALITY'  : '(i)Ein Wert in diesem Eingabefeld verbessert die Qualit&auml;t der Wissensbasis.',
	'SELECTION_MUST_NOT_BE_EMPTY' : '(e)Die Auswahl darf nicht leer sein!',
	'INVALID_FORMAT_OF_VALUE' : '(e)Der Wert hat ein ung&uuml;ltiges Format.',
	'INVALID_VALUES'          : 'Ung&uuml;ltige Werte.',
	'NAME'                    : 'Name:',
	'ENTER_NAME'              : 'Bitte Name eingeben.',
	'ADD'                     : 'Hinzuf&uuml;gen',
	'CANCEL'                  : 'Abbrechen',
	'CREATE'                  : 'Erzeugen',
	'ANNOTATE'                : 'Annotieren',
	'SUB_SUPER'               : 'Sub/Super',
	'MHAS_PART'               : 'Hat Teil',
	'INVALID_NAME'            : 'Ung&uuml;ltiger Name.',
	'CHANGE'                  : '&Auml;ndern',
	'DELETE'                  : 'L&ouml;schen',
	'INPUT_BOX_EMPTY'         : 'Fehler! Das Eingabefeld ist leer.',
	'ERR_QUERY_EXISTS_ARTICLE' : 'Fehler bei der Existenzabfrage des Artikels <$-page>.',
	'CREATE_PROP_FOR_CAT'     : 'Dieses Attribut wurde f&uuml;r die Kategorie <$cat> erzeugt. Bitte geben Sie sinnvollen Inhalt ein.',
	'NOT_A_CATEGORY'          : 'Der aktuelle Artikel ist keine Kategorie.',
	'CREATE_CATEGORY'         : 'Diese Kategorie wurde erzeugt aber nicht editiert. Bitte geben Sie sinnvollen Inhalt ein.',
	'CREATE_SUPER_CATEGORY'   : 'Diese Kategorie wurde als Superkategorie erzeugt aber nicht editiert. Bitte geben Sie sinnvollen Inhalt ein.',
	'CREATE_SUB_CATEGORY'     : 'Diese Kategorie wurde als Subkategorie erzeugt aber nicht editiert. Bitte geben Sie sinnvollen Inhalt ein.',
	'NOT_A_PROPERTY'          : 'Der aktuelle Artikel ist kein Attribut.',
	'CREATE_PROPERTY'         : 'Dieses Attribut wurde erzeugt aber nicht editiert. Bitte geben Sie sinnvollen Inhalt ein.',
	'CREATE_SUB_PROPERTY'     : 'Dieser Artikel wurde als Sub-Attribut erzeugt aber nicht editiert. Bitte geben Sie sinnvollen Inhalt ein.',
	'CREATE_SUPER_PROPERTY'   : 'Dieser Artikel wurde als Super-Attribut erzeugt aber nicht editiert. Bitte geben Sie sinnvollen Inhalt ein.',
	'ERROR_CREATING_ARTICLE'  : "Fehler beim Erzeugen des Artikels.",
	'ERROR_EDITING_ARTICLE'   : "Fehler beim Editieren des Artikels.",
	'UNMATCHED_BRACKETS'      : 'Warnung! Dieser Artikel ist syntaktisch nicht korrekt ("]]" fehlen)',
	'MAX_CARD_MUST_NOT_BE_0'  : "(e)Max. Kardinalit&auml;t darf nicht 0 oder kleiner sein.",
	'SPECIFY_CARDINALITY'     : "(e)Bitte geben Sie eine Kardinalit&auml;t ein!",
	'MIN_CARD_INVALID'        : "(e)Min. Kardinalität darf nicht kleiner als die max. Kardinalität sein!",
	'ASSUME_CARDINALITY_0'    : "(i) Die min. Kardinalität wird als 0 angenommen.",
	'ASSUME_CARDINALITY_INF'  : "(i) Max. Kardinalität wird als ∞ angenommen.",

	// Namespaces
	'NS_SPECIAL' 			  : 'Special',

	// Relation toolbar
	'ANNOTATE_PROPERTY'       : 'Annotatieren Sie ein Attribut.',
	'PAGE'                    : 'Seite:',
	'ANNO_PAGE_VALUE'         : 'Annotierte Seite/Wert',
	'SHOW'                    : 'Zeige:',
	'DEFINE_SUB_SUPER_PROPERTY' : 'Definieren Sie ein Sub-/Super-Attribut.',
	'CREATE_NEW_PROPERTY'     : 'Erzeugen Sie ein neues Attribut.',
	'ENTER_DOMAIN'            : 'Geben Sie einen Domain ein..',
	'ENTER_RANGE'             : 'Geben Sie eine Range ein.',
	'ENTER_TYPE'              : 'Wählen Sie einen Typ.',
	'PROP_HAS_PART'           : 'Attribut:has part', // name of the has-part property
	'HAS_PART'                : 'has part',
	'PROP_HBSU'               : 'Attribut:has basic structural unit', // name of the property
	'HBSU'                    : 'has basic structural unit',
	'DEFINE_PART_OF'          : 'Definieren Sie eine Teil-von Relation.',
	'OBJECT'                  : 'Objekt:',
	'RENAME_ALL_IN_ARTICLE'   : 'Alle im Artikel umbenennen.',
	'CHANGE_PROPERTY'         : 'Ändern Sie ein Attribut.',
	'PROPERTIES'              : 'Properties',
	'NO_OBJECT_FOR_POR'       : 'Kein Objekt für die Teil-von Relation gegeben.',
	'RETRIEVE_SCHEMA_DATA'    : 'Die Schema-Daten konnten nicht ermittelt werden!',

	// Property properties toolbar
	'PROPERTY_DOES_NOT_EXIST' : '(w)Dieses Attribut existiert nicht.',
	'PROPERTY_ALREADY_EXISTS' : '(w)Dieses Attribut existiert bereits.',
	'PROPERTY_NAME_TOO_LONG'  : '(e)Der Name des Attributs ist zu lang oder enthält ungültige Zeichen.',
	'PROPERTY_VALUE_TOO_LONG' : '(w)Dieser Wert ist sehr lang. Er kann nur in Attributs mit dem Typ "Typ:Text" gespeichert werden.',
	'CREATE_SUPER_PROPERTY'   : 'Erzeuge "$-title" und mache "$t" Super-Attribut von "$-title"',
	'CREATE_SUB_PROPERTY'     : 'Erzeuge "$-title" und mache "$t" Sub-Attribut von "$-title"',
	'MAKE_SUPER_PROPERTY'     : 'Mache "$t" Super-Attribut von "$-title"',
	'MAKE_SUB_PROPERTY'       : 'Mache "$t" Sub-Attribut von "$-title"',
	'ADD_TYPE'                : 'Typ hinzufügen',
	'ADD_RANGE'               : 'Range hinzufügen',
	'DOMAIN'                  : 'Domain:',
	'RANGE'                   : 'Range:',
	'INVERSE_OF'              : 'Inverse von:',
	'MIN_CARD'                : 'Min. Kardinalität:',
	'MAX_CARD'                : 'Max. Kardinalität:',
	'TRANSITIVE'              : 'Transitiv',
	'SYMMETRIC'               : 'Symmetrisch',
	'RETRIEVING_DATATYPES'    : 'Ermittele Datentypen...',
	'TYPE'                    : 'Typ:', //also used as namespace identifier with colon
	'PROPERTY_PROPERTIES'     : "Attribut Characteristik",
	'CATEGORY'                : "Kategorie:",	//also used as namespace identifier with colon
	'PROPERTY'                : "Attribut:",	//also used as namespace identifier with colon
	'TEMPLATE'                : "Vorlage:",	//also used as namespace identifier with colon
	'TYPE_PAGE'               : "Typ:Seite",	// type identifier
	'PAGE_TYPE'               : "page",		// name of the page data type
	'NARY_TYPE'               : "n-ary",       // name of the n-ary data type
	'SPECIFY_PROPERTY'		  : "Spezifizieren Sie dieses Attribut.",

	// Category toolbar
	'ANNOTATE_CATEGORY'       : 'Annotieren Sie eine Kategorie.',
	'CATEGORY_DOES_NOT_EXIST' : '(w)Diese Kategorie existiert nicht.',
	'CATEGORY_ALREADY_EXISTS' : '(w)Diese Kategorie existiert bereits.',
	'CATEGORY_NAME_TOO_LONG'  : '(e)Der Name dieser Kategorie ist zu lang oder enthält ungültige Zeichen.',
	'CREATE_SUPER_CATEGORY'   : 'Erzeuge "$-title" und mache "$t" Super-Kategorie von "$-title"',
	'CREATE_SUB_CATEGORY'     : 'Erzeuge "$-title" und mache "$t" Sub-Kategorie von "$-title"',
	'MAKE_SUPER_CATEGORY'     : 'Mache "$t" Super-Kategorie von "$-title"',
	'MAKE_SUB_CATEGORY'       : 'Mache "$t" Super-Kategorie von "$-title"',
	'DEFINE_SUB_SUPER_CAT'    : 'Definieren Sie eine Sub- oder Super-Kategorie.',
	'CREATE_SUB'              : 'Erzeuge Sub',
	'CREATE_SUPER'            : 'Erzeuge Super',
	'CREATE_NEW_CATEGORY'     : 'Erzeugen Sie eine neue Kategorie',
	'CHANGE_ANNO_OF_CAT'      : 'Ändern Sie die Annotation einer Kategorie',
	'CATEGORIES'              : 'Kategorien',
	'ADD_AND_CREATE_CAT'      : 'Hinzufügen und erzeugen',
	'CATEGORY_ALREADY_ANNOTATED': '(w)Diese Kategorie ist bereits annotiert.',

	// Annotation hints
	'ANNOTATION_HINTS'        : 'Annotations Hinweise',
	'ANNOTATION_ERRORS'       : 'Annotationsfehler',
	'AH_NO_HINTS'			  : '(i)Keine Hinweise für diesen Artikel.',
	'AH_SAVE_COMMENT'		  : 'Annotationen wurden im Advanced Annotation Mode hinzugefügt.',
	'AAM_SAVE_ANNOTATIONS' 	  : 'Möchten Sie die Annotationen der aktuellen Sitzung speichern?',
	'CAN_NOT_ANNOTATE_SELECTION' : 'Sie können die Auswahl nicht annotieren. Sie enthält bereits Annotationen oder Abschnitte oder endet in einem Link.',
	'AAM_DELETE_ANNOTATIONS'  : 'Möchten Sie diese Annotation wirklich löschen?',
	
	// Save annotations
	'SA_SAVE_ANNOTATION_HINTS': "Vergessen Sie nicht Ihre Arbeit zu speichern!",
	'SA_SAVE_ANNOTATIONS'	  : 'Speichere Annotationen',
	'SA_SAVE_ANNOTATIONS_AND_EXIT' : 'Speichern & verlassen',
	'SA_ANNOTATIONS_SAVED'	  : '(i) Die Annotationen wurden gespeichert.',
	'SA_SAVING_ANNOTATIONS_FAILED' : '(e) Ein Fehler trat beim Speichern der Annotationen auf.',
	'SA_SAVING_ANNOTATIONS'   : '(i) Speichere Annotationen...',

	// Autocompletion
	'AUTOCOMPLETION_HINT'     : 'Dr&uuml;cken Sie Ctrl+Alt+Space um die Auto-completion zu benutzen. (Ctrl+Space im IE)',
	'AC_CLICK_TO_DRAG'        : 'Auto-Completion - Hier zum Verschieben klicken',

	// Combined search
	'ADD_COMB_SEARCH_RES'     : 'Zus&auml;tzliche Ergebnisse der Combined-Search.',
	'COMBINED_SEARCH'         : 'Combined-Search',

	'INVALID_GARDENING_ACCESS' : 'Sie d&uuml;rfen Gardening Bots nicht abbrechen. Das d&uuml;rfen nur Sysops und Gardener.',
	'GARDENING_LOG_COLLAPSE_ALL' : 'Alles einklappen',
	'GARDENING_LOG_EXPAND_ALL'   : 'Alles ausklappen',
	'BOT_WAS_STARTED'			: 'Der Bot wurde gestartet.',
	
	// Ontology browser
	'OB_ID'					  : 'OntologyBrowser',
	'ONTOLOGY_BROWSER'        : 'Ontology Browser',
	'PROPERTY_NS_WOC'         : 'Attribut', // Property namespace without colon
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
	'OB_DELETE'	  			  : 'L&ouml;schen',
	'OB_PREVIEW' 			  : 'Preview',
	'OB_TITLE_EXISTS'		  : 'Seite existiert bereits',
	'OB_ENTER_TITLE'		  : 'Seitennamen eingeben',
	'OB_SELECT_CATEGORY'	  : 'Erst Kategorie auswählen',
	'OB_SELECT_PROPERTY'	  : 'Erst Property auswählen',
	'OB_SELECT_INSTANCE'	  : 'Erst Instanz auswählen',
	'OB_WRONG_MAXCARD'		  : 'Falsche Max-Kardinalit&auml;t',
	'OB_WRONG_MINCARD'		  : 'Falsche Min-Kardinalit&auml;t',
	'OB_CONFIRM_INSTANCE_DELETION' : 'Wollen Sie den Artikel wirklich l&ouml;schen?',
	'SMW_OB_OPEN' 			  : '(&Ouml;ffne)',
	'SMW_OB_EDIT' 		  	  : '(Editiere)',
	'SMW_OB_ADDSOME'		  : '(F&uuml;ge hinzu)',
	'OB_CONTAINS_FURTHER_PROBLEMS' : 'Contains further problems',
	'SMW_OB_MODIFIED'		  : 'Artikel wurde gespeichert. Das Problem wurde möglicherweise bereits behoben.',

	// Find work
	'FW_SEND_ANNOTATIONS'	  : 'Danke für das Bewerten der Annotationen, ',
	'FW_MY_FRIEND'	  		  : 'mein Freund!',
	
	// Query Interface
	'QUERY_INTERFACE'         : 'Query Interface',
	'QI_MAIN_QUERY_NAME'	  : 'Hauptquery',
	'QI_ARTICLE_TITLE'        : 'Artikel',
	'QI_EMPTY_QUERY'       	  : 'Ihr Query ist leer.',
	'QI_INSTANCE'       	  : 'Instanz:',
	'QI_PROPERTYNAME'         : 'Attributname:',
	'QI_SHOW_PROPERTY'        : 'In Ergebnissen zeigen:',
	'QI_PROPERTY_MUST_BE_SET' : 'Wert muss gesetzt sein:',
	'QI_USE_SUBQUERY'         : 'Subquery einf&uuml;gen',
	'QI_PAGE'				  : 'Page', // has to be the same as the Type:Page in your language
	'QI_OR'        			  : 'oder',
	'QI_ENTER_CATEGORY'       : 'Bitte geben Sie eine Kategorie ein',
	'QI_ENTER_INSTANCE'       : 'Bitte geben Sie eine Instanz ein',
	'QI_ENTER_PROPERTY_NAME'  : 'Bitte geben Sie einen Attributnamen ein',
	'QI_CLIPBOARD_SUCCESS'    : 'Der Query wurde in Ihre Zwischenablage kopiert',
	'QI_CLIPBOARD_FAIL'    	  : 'Ihr Browser erlaubt keinen Zugriff auf die Zwischenablage\nDer Query konnte nicht in Ihre Zwischenablage kopiert werden.\n Bitte verwenden Sie die Funktion "Kompletten Query anzeigen" und kopieren Sie den Query manuell.',
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
	'SHE_fullscreen'			  : "fullscreen",
	
	// Wiki text parser
	'WTP_TEXT_NOT_FOUND'		  : "Konnte '$1' nicht im Wikitext finden.",
	'WTP_NOT_IN_NOWIKI'			  : "'$1' ist Teil eines &lt;nowiki&gt;-Abschnitts.\nEr kann nicht annotiert werden.",
	'WTP_NOT_IN_TEMPLATE'		  : "'$1' ist Teil einer Vorlage.\nEr kann nicht annotiert werden.",
	'WTP_NOT_IN_ANNOTATION'		  : "'$1' ist Teil einer Annotation.\nEr kann nicht annotiert werden.",
	'WTP_NOT_IN_QUERY'            : "'$1' ist Teil einer Query.\nEr kann nicht annotiert werden.",
	'WTP_NOT_IN_PREFORMATTED'	  : "'$1' ist Teil eines vorformatierten Textes.\nEr kann nicht annotiert werden.",
	'WTP_SELECTION_OVER_FORMATS'  : "Die Auswahl erstreckt sich über verschiedene Formate:\n$1"
	
};
