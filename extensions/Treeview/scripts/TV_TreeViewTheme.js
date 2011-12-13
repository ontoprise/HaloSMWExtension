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
 * @ingroup TreeViewScripts
 * @author: Thomas Schweitzer
 */

if (typeof TreeView == "undefined") {
// Define the TreeView module	
	TreeView = { 
		classes : {}
	};
}
if (typeof TreeView.classes == "undefined") {
	TreeView.classes = {};
}

/**
 * @class TreeViewTheme
 * This is the main class of the TreeView. It initalizes all treeview that
 * are present on the page.
 */
TreeView.classes.TreeViewTheme = function() {
	var $ = jQuery;
	
	//--- Private members ---

	// The instance of this object
	var that = {};

	/**
	 * This function initializes the themes for the TreeView
	 */
	function construct() {
		
		// Theme for the {{#tree}} parser function
		AjaxSolr.theme.prototype.treeParserFunction = function(wikitext) {
			wikitext = wikitext.replace(/\n/g, '<br />');
			return wikitext;
		};
	};
	
	construct();	

}

TreeView.classes.TreeViewTheme();
