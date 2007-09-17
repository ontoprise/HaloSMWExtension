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
