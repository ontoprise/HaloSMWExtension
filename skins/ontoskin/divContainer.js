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
var divcontainerarray = new Array();

var HELPCONTAINER = 0; // contains help
var FACTCONTAINER = 1; // contains already annotated facts
var EDITCONTAINER = 2; // contains Linklist
var TYPECONTAINER = 3; // contains datatype selector on attribute pages
var CATEGORYCONTAINER = 4; // contains categories
var ATTRIBUTECONTAINER = 5; // contains attrributes
var RELATIONCONTAINER = 6; // contains relations
var PROPERTIESCONTAINER = 7; // contains the properties of attributes and relations
var CBSRCHCONTAINER = 8; // contains combined search functions
var COMBINEDSEARCHCONTAINER = 9;
var DBGCONTAINER = 99; // contains debug information

function divContainer(container, headline, content)
{
	this.container = container;
	this.headline = headline;
	this.content = content;
	this.ishidden = false;
	divcontainerarray[container] = this;
}

function factContainer(container, headline, content, attributes, relations, categories)
{
	this.container = container;
	this.headline = headline;
	this.content = content;
	this.attributes = attributes;
	this.relations = relations;
	this.categories = categories;
	this.ishidden = false;
	divcontainerarray[container] = this;
}

function removeContainer(container)
{
	divcontainerarray[container] = "";
}
