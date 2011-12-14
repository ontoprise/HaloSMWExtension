<?php
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
 * @file
 * @ingroup TreeView_Language
 *
 * This file contains the user language messages.
 * 
 * @author Thomas Schweitzer
 * Date: 02.12.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the TreeView extension. It is not a valid entry point.\n" );
}

$messages = array();

/** 
 * English
 */
$messages['en'] = array(
	'tv_treeview'			=> "Treeview",
	'tv_default_root_name'	=> "Root",
	'tv_missing_tree_level'	=> "***Warning: Missing label for this tree level***",
	'tv_missing_tree'		=> "*** Warning: No tree structure found! ***",
	'tv_define_tree'		=> "Define a tree for this search",
	'tv_hide_define_tree'	=> "Hide tree definition",
	'tv_hide_treeview_toolbox' => "Hide tree definition toolbox",
	'tv_property'			=> "Property:",
	'tv_property_help'		=> "Enter the property that defines the hierarchy of the tree!",
	'tv_property_apply'		=> "Apply",
	'tv_show_parser_function' => "Show the parser function",
	'tv_hide_parser_function' => "Hide the parser function",
	'tv_parser_function' 		=> "Parser function for this search",
	'tv_copy_parser_function'	=> "You can copy this wikitext into an article to generate the same tree.",
	
	// Client side language strings
	'tv_treepf_template' => <<<TEMPLATE
{{#tree:
*{{#generateTree:
property=$1
|rootlabel=Enter name of root node here
|solrquery=$2 
}}
}}
TEMPLATE

);

/** 
 * German
 */
$messages['de'] = array(
	'tv_treeview'		=> "Baumansicht",
	'tv_default_root_name' => "Wurzel",
	'tv_missing_tree_level'	=> "***Warnung: Fehlender Name für diese Baumstufe***",
	'tv_missing_tree'		=> "*** Warnung: Es wurde keine Baumstruktur gefunden! ***",
	'tv_define_tree'		=> "Definieren Sie einen Baum zu dieser Suche",
	'tv_hide_define_tree'	=> "Baumdefinition ausblenden",
	'tv_hide_treeview_toolbox' => "Baumdefinitionswerkzeug ausblenden",
	'tv_property'			=> "Attribut:",
	'tv_property_help'		=> "Geben Sie das Attribut ein, das die Hierarchie des Baumes definiert!",
	'tv_property_apply'		=> "Aktualisieren",
	'tv_show_parser_function' => "Parserfunktion zeigen",
	'tv_hide_parser_function' => "Parserfunktion ausblenden",
	'tv_parser_function' 	=> "Parserfunktion für diese Suche",
	'tv_copy_parser_function'	=> "Sie können diesen Wikitext in einen Artikel kopieren um dort den gleichen Baum zu erzeugen.",

	// Client side language strings
	'tv_treepf_template' => <<<TEMPLATE
{{#baum:
*{{#erzeugeBaum:
attribut=$1
|wurzelname=Wurzelname hier eingeben
|solrquery=$2 
}}
}}
TEMPLATE
);


