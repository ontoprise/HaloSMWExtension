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
	'AUTOCOMPLETION_HINT'     : 'Press Ctrl+Alt+Space to use auto-completion. (Ctrl+Space in IE)',
	'AC_CLICK_TO_DRAG'        : 'Auto-Completion - Click here to drag',

	// Combined search
	'ADD_COMB_SEARCH_RES'     : 'Additional Combined Search results.',
	'COMBINED_SEARCH'         : 'Combined Search',

	'INVALID_GARDENING_ACCESS' : 'You are not allowed to cancel bots. Only sysops and gardeners can do so.',
	// Ontology browser
	'OB_ID'					  : 'OntologyBrowser',
	'ONTOLOGY_BROWSER'        : 'Ontology Browser',
	'PROPERTY_NS_WOC'         : 'Property', // Property namespace without colon
	'RELATION_NS_WOC'         : 'Relation', // Relation namespace without colon
	'CATEGORY_NS_WOC'         : 'Category', // Category namespace without colon
	'KS_NOT_SUPPORTED'        : 'Konqueror/Safari are not supported currently!',
	'SHOW_INSTANCES'          : 'Show instances',
	'HIDE_INSTANCES'          : 'Hide instances',
	'ENTER_MORE_LETTERS'      : "Please enter at least two letters. Otherwise you will receive simply too much results.",
	'MARK_A_WORD'             : 'Mark a word...',
	'OPEN_IN_OB'              : 'Open in Ontology Browser',

	// Query Interface
	'QUERY_INTERFACE'         : 'Query Interface',
	'QI_MAIN_QUERY_NAME'	  : 'Main',
	'QI_ARTICLE_TITLE'        : 'Article title',
	'QI_EMPTY_QUERY'       	  : 'Your Query is empty.',
	'QI_INSTANCE'       	  : 'Instance:',
	'QI_PROPERTYNAME'         : 'Property name:',
	'QI_SHOW_PROPERTY'        : 'Show in results:',
	'QI_USE_SUBQUERY'         : 'Use subquery',
	'QI_PAGE'				  : 'Page',
	'QI_OR'        			  : 'or',
	'QI_ENTER_CATEGORY'       : 'Please enter a category',
	'QI_ENTER_INSTANCE'       : 'Please enter an instance',
	'QI_ENTER_PROPERTY_NAME'  : 'Please enter a property name',
	'QI_CLIPBOARD_SUCCESS'    : 'The query text was successfully copied to your clipboard',
	'QI_CLIPBOARD_FAIL'    	  : "Your browser does not allow clipboard access.\nThe query text could not be copied to your clipboard.",
	'QI_SUBQUERY'    	  	  : "Subquery",
	'QI_CATEGORIES'    	  	  : " Categories:",
	'QI_INSTANCES'    	  	  : " Instances:",
	'QI_QUERY_EXISTS'		  : "A query with this name already exists. Please choose another name.",
	'QI_QUERY_SAVED'		  : "Your query has been successfully saved.",
	'QI_SAVE_ERROR'		  	  : "An unknown error occured. Your query could not be saved.",

	//Syntax Highlighting editor
	'SHE_new_document'			  : "new empty document",
	'SHE_search_button'			  : "search and replace",
	'SHE_search_command'		  : "search next / open search area",
	'SHE_search'				  : "search",
	'SHE_replace'				  : "replace",
	'SHE_replace_command'		  : "replace / open search area",
	'SHE_find_next'				  : "find next",
	'SHE_replace_all'			  : "replace all",
	'SHE_reg_exp'				  : "regular expressions",
	'SHE_match_case'			  : "match case",
	'SHE_not_found'				  : "not found.",
	'SHE_occurrence_replaced'	  : "occurences replaced.",
	'SHE_search_field_empty'	  : "Search field empty",
	'SHE_restart_search_at_begin' : "End of area reached. Restart at begin.",
	'SHE_move_popup'			  : "move search popup",
	'SHE_font_size'				  : "--Font size--",
	'SHE_go_to_line'			  : "go to line",
	'SHE_go_to_line_prompt'		  : "go to line number':",
	'SHE_undo'					  : "undo",
	'SHE_redo'					  : "redo",
	'SHE_change_smooth_selection' : "enable/disable some display features (smarter display but more CPU charge)",
	'SHE_highlight'				  : "toggle syntax highlight on/off",
	'SHE_reset_highlight'		  : "reset highlight (if desyncronized from text)",
	'SHE_help'					  : "about",
	'SHE_save'					  : "save",
	'SHE_load'					  : "load",
	'SHE_line_abbr'				  : "Ln",
	'SHE_char_abbr'				  : "Ch",
	'SHE_position'				  : "Position",
	'SHE_total'					  : "Total",
	'SHE_close_popup'			  : "close popup",
	'SHE_shortcuts'				  : "Shortcuts",
	'SHE_add_tab'				  : "add tabulation to text",
	'SHE_remove_tab'			  : "remove tabulation to text",
	'SHE_about_notice'			  : "Notice': syntax highlight function is only for small text",
	'SHE_toggle'				  : "Switch between syntax highlighted and standard editor",
	'SHE_accesskey'				  : "Accesskey",
	'SHE_tab'					  : "Tab",
	'SHE_shift'					  : "Shift",
	'SHE_ctrl'					  : "Ctrl",
	'SHE_esc'					  : "Esc",
	'SHE_processing'			  : "Processing...",
	'SHE_fullscreen'			  : "fullscreen"
};
