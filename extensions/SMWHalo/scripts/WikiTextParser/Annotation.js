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
 * @file
 * @ingroup SMWHaloAAM
 * 
 * Annotations.js 
 * Classes for the representation of annotations.
 * 
 * @author Thomas Schweitzer
 */
 
/**
 * Base class for annotations. It stores
 * - the text of the annotation
 * - start and end position of the string in the wiki text
 * - a reference to the wiki text parser.
 */
var WtpAnnotation = Class.create();


WtpAnnotation.prototype = {
	/**
	 * @public
	 * @see constructor of WtpAnnotation
	 */
	initialize: function(annotation, start, end, wtp, prefix) {
		this.WtpAnnotation(annotation, start, end, wtp);
	},
	
	/**
	 * @private - called by <initialize>
	 * 
	 * Constructor.
	 * 
	 * @param string annotation The complete annotation e.g. [[attr:=3.141|about three]]
	 * @param int start Start position of the annotation in the wiki text.
	 * @param int end End position of the annotation in the wiki text.
	 * @param int wtp Reference to the wiki text parser.
	 * @param string prefix An optional prefix (e.g. a colon) before the actual
	 *                      annotation.
	 * 
	 */
	WtpAnnotation : function(annotation, start, end, wtp, prefix) {
		this.annotation = annotation;
		this.start = start;
		this.end = end;
		this.wikiTextParser = wtp;
		this.prefix = prefix ? prefix : "";
		this.name = null;
		this.representation = null;
	},
	
	/** @return The complete text of this annotation */
	getAnnotation : function() {
		return this.annotation;
	},
	

	/** @return The name of this annotation */
	getName : function() {
		return this.name;
	},
	
	/** @return The name of this annotation */
	getRepresentation : function() {
		//Fix for IE which interprets null as "null"
		if( this.representation == null){
			return "";	
		} else {
			return this.representation;
		}
	},
	
	/** @return Start position of the annotation in the wiki text. */
	getStart : function() {
		return this.start;
	},
	
	/** @return End position of the annotation in the wiki text. */
	getEnd : function() {
		return this.end;
	},

	/** @return The prefix of this annotation. This can be a colon like in 
	 *          [[:Category:foo]]
	 */
	getPrefix : function() {
		return this.prefix;
	},
	
	/**
	 * Selects this annotation in the wiki text.
	 */
	select: function() {
		this.wikiTextParser.setSelection(this.start, this.end);
	},
	
	/**
	 * @private
	 * 
	 * Replaces an annotation in the wiki text.
	 * 
	 * @param newAnnotation Text of the new annotation
	 */
	replaceAnnotation : function(newAnnotation) {
		this.wikiTextParser.replaceAnnotation(this, newAnnotation);
		var oldLen = this.annotation.length;
		var newLen = newAnnotation.length;
		this.end += newLen - oldLen;
		this.annotation = newAnnotation;
	},
		
	
	/**
	 * @private
	 * 
	 * Each annotation stores its position in the wiki text. If the wiki text
	 * is changed before the annotation, the position has to be updated.
	 * 
	 * This function does not change the wiki text in any way.
	 * 
	 * @param int offset This offset is added to the start and end position of 
	 *                   this annotation.
	 *
	 * @param int start The annotation if moved, if it starts AFTER (not at) this
	 *                  position. 
	 * 
	 */	
	move : function(offset, start) {
		if (this.start > start) {
			this.start += offset;
			this.end += offset;
		}
	},
	
	/**
	 * @public
	 * Removes this annotation from the wiki text. After this operation,
	 * this instance of WtpAnnotation is no longer valid.
	 * 
	 * @param string replacementText Text that replaces the annotation. Can
	 *               be <null> or empty.
	 */
	remove : function(replacementText) {
		this.replaceAnnotation(replacementText);
		this.wikiTextParser.removeAnnotation(this);
//		delete this;  -- does not work in IE
	}
};

/**
 * Class for relations - derived from WtpAnnotation
 * 
 * Stores
 * - the name of the relation
 * - the relation's value and
 * - the user representation.
 * 
 */
var WtpRelation = Class.create();
WtpRelation.prototype = Object.extend(new WtpAnnotation(), {
	
	/**
	 * @public
	 * @see constructor of WtpRelation
	 */
	initialize: function(annotation, start, end, wtp, prefix,
	                     relationName, relationValue, representation) {
		this.WtpAnnotation(annotation, start, end, wtp, prefix);
		this.WtpRelation(relationName, relationValue, representation);
	},

	/**
	 * @private - called by <initialize>
	 * 
	 * Constructor.
	 * 
	 * @param string relationName  The name of the relation
	 * @param string relationValue The value of the relation
	 * @param string representation The user representation
	 * 
	 */
	WtpRelation: function(relationName, relationValue, representation) {
		this.name = relationName;
		this.value = relationValue;
		this.representation = representation;
		this.splitValues = this.splitValues(this.value);
		this.arity = this.splitValues.length + 1; // subject is also part of arity, thus (+1)
	},
	
	/** @return The value of this relation */
	getValue : function() {
		return this.value;
	},
	
	getSplitValues: function() {
		return this.splitValues;
	},
	
	getArity: function() {
		return this.arity;
	},
	
	/**
	 * @public
	 * 
	 * Renames the relation in the wiki text. The definition of the relation
	 * is not changed.
	 * 
	 * @param string newRelationName New name of the relation.
	 */
	rename: function(newRelationName) {
		var newAnnotation = "[[" + this.prefix + newRelationName + "::" + this.value;
		if (this.representation) {
			newAnnotation += "|" + this.representation;
		}
		newAnnotation += "]]";
		this.name = newRelationName;
		this.replaceAnnotation(newAnnotation);
	},
	
	/**
	 * @public
	 * 
	 * Changes the value of the relation in the wiki text.
	 * 
	 * @param string newValue New value of the relation.
	 */
	changeValue: function(newValue) {
		var newAnnotation = "[[" + this.prefix + this.name + "::" + newValue;
		if (this.representation && newValue != this.representation) {
			newAnnotation += "|" + this.representation;
		}
		newAnnotation += "]]";
		this.value = newValue;
		this.replaceAnnotation(newAnnotation);
	},
	
	/**
	 * Replaces user representation of an annotation in the wiki text.
	 * 
	 * @param string newRepresentation New representation. Can be <null> or 
	 *               empty string.
	 */
	changeRepresentation : function(newRepresentation) {
		var newAnnotation = "[[" + this.prefix + this.name + "::" + this.value;
		if (newRepresentation && newRepresentation != "" 
		    && newRepresentation != this.value) {
			newAnnotation += "|" + newRepresentation;
		}
		newAnnotation += "]]";
		this.representation = newRepresentation;
		this.replaceAnnotation(newAnnotation);
	},

	/**
	 * Replaces name, value and representation of an annotation in the wiki text.
         *
  	 * @param string name New name of property. Can be <null> or empty string.
	 * @param string value New value of property. Can be <null> or empty string.
	 * @param string representation New representation of property. Can be <null> or empty string.
	 */
	update : function(name, value, representation) {
		var newAnnotation = "[[" + this.prefix + name + "::" + value;
		if (representation && representation != ""
		    && representation != value) {
			newAnnotation += "|" + representation;
		}
		newAnnotation += "]]";
                this.name = name;
                this.value = value;
		this.representation = representation;
		this.replaceAnnotation(newAnnotation);
	},

	/**
	 * @private
	 * 
	 * Splits the (n-ary) values of a relation at semicolons and takes care of
	 * HTML-entities like &auml;
	 * 
	 * @param string value
	 * 		The value(s) of the relation
	 * 
	 */
	splitValues: function(value) {
		var values = [];
		var start = 0;
		var htmlEntity = '';
		for (var i = 0, n = value.length; i < n; ++i) {
			var ch = value.charAt(i);
			
			if (ch == '&') {
				// maybe a html entity starts
				htmlEntity = '&';
			} else if (ch == ';') {
				var split = false;
				if (htmlEntity != '') {
					// maybe a html entity ends
					htmlEntity += ';';
					var ch = htmlEntity.unescapeHTML();
					if (ch == htmlEntity) {
						// no html entity found
						// => values must be split
						split = true;;
					}
					htmlEntity = '';
				} else {
					// no html entity => values must be split
					split = true;;
				}
				if (split) {				
					values.push(value.substring(start, i));
					start = i + 1;
				}
			} else if (htmlEntity != '') {
				htmlEntity += ch;
			}
		}
		values.push(value.substring(start, i));
		return values;
	},
	
	/**
	 * Returns a printable representation of the object.
	 */
	inspect: function() {
		var content = "Annotation: " + this.annotation + "<br />" +
					  "Name : " + this.name + "<br />" +
		              "Value: " + this.value + "<br />" +
		              "Rep. : " + this.representation + "<br />" +
		              "Start: " + this.start + "<br />" +
		              "End  : " + this.end + "<br />";
		
		return content;
	}
	
});


/**
 * Class for categories - derived from WtpAnnotation
 * 
 * Stores
 * - the name of the category
 * - the user representation.
 * 
 */
var WtpCategory = Class.create();
WtpCategory.prototype = Object.extend(new WtpAnnotation(), {
	
	/**
	 * @public
	 * @see constructor of WtpCategory
	 */
	initialize: function(annotation, start, end, wtp, prefix,
	                     categoryName, representation) {
		this.WtpAnnotation(annotation, start, end, wtp, prefix);
		this.WtpCategory(categoryName, representation);
	},

	/**
	 * @private - called by <initialize>
	 * 
	 * Constructor.
	 * 
	 * @param string categoryName  The name of the category
	 * @param string representation The user representation
	 * 
	 */
	WtpCategory: function(categoryName, representation) {
		this.name = categoryName;
		this.representation = representation;
	},

	/**
	 * @public
	 * 
	 * Renames the category in the wiki text. The definition of the category
	 * is not changed.
	 * 
	 * @param string newCategoryName New name of the category.
	 */
	changeCategory: function(newCategoryName) {
		var newAnnotation = "[[" + this.prefix + gLanguage.getMessage('CATEGORY_NS') + newCategoryName;
		if (this.representation) {
			newAnnotation += "|" + this.representation;
		}
		newAnnotation += "]]";
		this.name = newCategoryName;
		this.replaceAnnotation(newAnnotation);
	},
	
	/**
	 * Replaces user representation of an annotation in the wiki text.
	 * 
	 * @param string newRepresentation New representation. Can be <null> or 
	 *               empty string.
	 */
	changeRepresentation : function(newRepresentation) {
		var newAnnotation = "[[" + this.prefix + gLanguage.getMessage('CATEGORY_NS') + this.name;
		if (newRepresentation && newRepresentation != "") {
			newAnnotation += "|" + newRepresentation;
		}
		newAnnotation += "]]";
		this.representation = newRepresentation;
		this.replaceAnnotation(newAnnotation);
	},
	
	
	/**
	 * Returns a printable representation of the object.
	 */
	inspect: function() {
		var content = "Annotation: " + this.annotation + "<br />" +
					  "Name : " + this.name + "<br />" +
		              "Rep. : " + this.representation + "<br />" +
		              "Start: " + this.start + "<br />" +
		              "End  : " + this.end + "<br />";
		
		return content;
	}
	
});

/**
 * Class for links to other wiki articles - derived from WtpAnnotation
 * 
 * Stores
 * - the name linked article
 * - the user representation.
 * 
 */
var WtpLink = Class.create();
WtpLink.prototype = Object.extend(new WtpAnnotation(), {
	
	/**
	 * @public
	 * @see constructor of WtpLink
	 */
	initialize: function(annotation, start, end, wtp, prefix,
	                     link, representation) {
		this.WtpAnnotation(annotation, start, end, wtp, prefix);
		this.WtpLink(link, representation);
	},

	/**
	 * @private - called by <initialize>
	 * 
	 * Constructor.
	 * 
	 * @param string link  The content of the link
	 * @param string representation The user representation
	 * 
	 */
	WtpLink: function(link, representation) {
		this.name = link;
		this.representation = representation;
	},

	/**
	 * @public
	 * 
	 * Replaces a link in the wiki text.
	 * 
	 * @param string newLink The new link.
	 */
	changeLink: function(newLink) {
		var newAnnotation = "[[" + this.prefix + newLink;
		if (this.representation) {
			newAnnotation += "|" + this.representation;
		}
		newAnnotation += "]]";
		this.name = newLink;
		this.replaceAnnotation(newAnnotation);
	},

	/**
	 * Replaces user representation of an annotation in the wiki text.
	 * 
	 * @param string newRepresentation New representation. Can be <null> or 
	 *               empty string.
	 */
	changeRepresentation : function(newRepresentation) {
		var newAnnotation = "[[" + this.prefix + this.name;
		if (newRepresentation && newRepresentation != "") {
			newAnnotation += "|" + newRepresentation;
		}
		newAnnotation += "]]";
		this.representation = newRepresentation;
		this.replaceAnnotation(newAnnotation);
	},
	
	
	/**
	 * Returns a printable representation of the object.
	 */
	inspect: function() {
		var content = "Annotation: " + this.annotation + "<br />" +
					  "Name : " + this.name + "<br />" +
		              "Rep. : " + this.representation + "<br />" +
		              "Start: " + this.start + "<br />" +
		              "End  : " + this.end + "<br />";
		
		return content;
	}
	
});

/**
 * Class for simples rules - derived from WtpAnnotation
 * 
 * Stores
 * - the name of the rule
 * - the host language
 * - the type of the rule
 * - the text of the rule
 * 
 */
var WtpRule = Class.create();
WtpRule.prototype = Object.extend(new WtpAnnotation(), {
	
	/**
	 * @public
	 * @see constructor of WtpRule
	 */
	initialize: function(annotation, start, end, wtp, 
	                     name, hostlanguage, type, ruleText) {
		this.WtpAnnotation(annotation, start, end, wtp, "");
		this.WtpRule(name, hostlanguage, type, ruleText);
	},

	/**
	 * @private - called by <initialize>
	 * 
	 * Constructor.
	 * 
	 * @param string name
	 * 		Name of the rule
	 * @param string hostlanguage
	 * 		Host language e.g. FLogic
	 * @param string type
	 * 		Type of the rule e.g. Definition, Calculation
	 * @param string ruleText
	 * 		Text of the rule
	 * 
	 */
	WtpRule: function(name, hostlanguage, type, ruleText) {
		this.name = name;
		this.hostlanguage = hostlanguage;
		this.type = type;
		this.ruleText = ruleText;
	},

	/**
	 * @public
	 * 
	 * Replaces a rule in the wiki text.
	 * 
	 * @param string newRule The complete definition of the new rule
	 */
	changeRule: function(newRule) {
		this.replaceAnnotation(newRule);
	},
	
	/**
	 * @public
	 * 
	 * @return string
	 * 		Returns the text of the rule e.g. the FLogic.
	 */
	getRuleText: function() {
		return this.ruleText;
	}
	
	
});

/**
 * Class for queries - derived from WtpAnnotation
 * 
 * Stores
 * - the name of the query
 * - the pure query content (without parser funtion and brackets)
 * 
 */
var WtpQuery = Class.create();
WtpQuery.prototype = Object.extend(new WtpAnnotation(), {
	
	/**
	 * @public
	 * @see constructor of WtpQuery
	 */
	initialize: function(annotation, start, end, wtp, 
			name, queryText) {
		this.WtpAnnotation(annotation, start, end, wtp, "");
		this.WtpQuery(name, queryText);
	},

	/**
	 * @private - called by <initialize>
	 * 
	 * Constructor.
	 * 
	 * @param string name
	 * 		Name of the query
	 * @param string queryText
	 * 		Text of the query
	 */
	WtpQuery: function(name, queryText) {
		this.name = name;
		this.queryText = queryText;
	},
	
	/**
	 * @public
	 * 
	 * @return string
	 * 		Returns the pure content of the query
	 */
	getQueryText: function() {
		return this.queryText;
	},
	

	/**
	 * @public
	 * 
	 * Replaces a query in the wiki text.
	 * 
	 * @param string newQuery The complete definition of the new query
	 */
	changeQuery: function(newQuery) {
		this.replaceAnnotation(newQuery);
	}
});

