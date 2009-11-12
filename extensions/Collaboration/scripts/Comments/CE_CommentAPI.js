/*  Copyright 2009, ontoprise GmbH
*  This file is part of the Collaboration-Extension.
*
*   The Collaboration-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Collaboration-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


var CECommentAPI = Class.create();

/**
 * This class provides language dependent strings for an identifier.
 * 
 */
CECommentAPI.prototype = {

	/**
	 * @public
	 * 
	 * Constructor.
	 */
	initialize: function() {
	},

	/**
	 * This function calls php via ajax (XML_HTTPRequest).
	 * @param:
	 * 	wikiurl: url to target wiki excl. wikipath
	 * 	wikipath: wiki script path
	 * 	pagexml: The page name and its content in xml
	 * 	username: This is the wiki user name which is used to create article in the target wiki 
	 * 	password: The user password for user name in target wiki
	 * 	domain: if needed for login with username and password in target wiki (e.g. LDAP)
	 * @return
	 * 	xml 
	 */
	createNewPage: function(wikiurl, wikipath, pagexml, username, password, domain) {

		return "xml";
	},
	
	createNewPageCallback: function() {

		return "xml";
	},
	
}

//Singleton of this class

var ceCommentAPI = new CECommentAPI();