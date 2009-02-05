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

/*
	Cross-Browser Split 0.2.1
	By Steven Levithan <http://stevenlevithan.com>
	MIT license
*/

var nativeSplit = nativeSplit || String.prototype.split;

String.prototype.split = function (s /* separator */, limit) {
	// If separator is not a regex, use the native split method
	if (!(s instanceof RegExp))
		return nativeSplit.apply(this, arguments);

	/* Behavior for limit: If it's...
	 - Undefined: No limit
	 - NaN or zero: Return an empty array
	 - A positive number: Use limit after dropping any decimal
	 - A negative number: No limit
	 - Other: Type-convert, then use the above rules */
	if (limit === undefined || +limit < 0) {
		limit = false;
	} else {
		limit = Math.floor(+limit);
		if (!limit)
			return [];
	}

	var	flags = (s.global ? "g" : "") + (s.ignoreCase ? "i" : "") + (s.multiline ? "m" : ""),
		s2 = new RegExp("^" + s.source + "$", flags),
		output = [],
		lastLastIndex = 0,
		i = 0,
		match;

	if (!s.global)
		s = new RegExp(s.source, "g" + flags);

	while ((!limit || i++ <= limit) && (match = s.exec(this))) {
		var zeroLengthMatch = !match[0].length;

		// Fix IE's infinite-loop-resistant but incorrect lastIndex
		if (zeroLengthMatch && s.lastIndex > match.index)
			s.lastIndex = match.index; // The same as s.lastIndex--

		if (s.lastIndex > lastLastIndex) {
			// Fix browsers whose exec methods don't consistently return undefined for non-participating capturing groups
			if (match.length > 1) {
				match[0].replace(s2, function () {
					for (var j = 1; j < arguments.length - 2; j++) {
						if (arguments[j] === undefined)
							match[j] = undefined;
					}
				});
			}

			output = output.concat(this.slice(lastLastIndex, match.index), (match.index === this.length ? [] : match.slice(1)));
			lastLastIndex = s.lastIndex;
		}

		if (zeroLengthMatch)
			s.lastIndex++;
	}

	return (lastLastIndex === this.length) ?
		(s.test("") ? output : output.concat("")) :
		(limit      ? output : output.concat(this.slice(lastLastIndex)));
};

/**
 * WikiTextParser.js
 *
 * Class for parsing annotations in wiki text.
 *
 * @author Thomas Schweitzer
 */

var WTP_NO_ERROR = 0;
var WTP_UNMATCHED_BRACKETS = 1;

var WTP_WIKITEXT_MODE = 1;
var WTP_EDITAREA_MODE = 2;

/**
 * Class for parsing WikiText. It extracts annotations in double brackets [[...]]
 * and recognizes relations, categories and links.
 *
 * You can
 * - retrieve a list of relations, categories and links as objects
 *   (see Annotation.js)
 * - change annotations in the wiki text (via the returned objects)
 * - add annotations
 * - remove annotations.
 */
var WikiTextParser = Class.create();

var gEditInterface = null;

WikiTextParser.prototype = {
	/**
	 * @public
	 *
	 * Constructor. If no wiki text is given, the text from the textarea of the 
	 * edit page is stored. The text is parsed and the list of of annotations is 
	 * initialized.
	 * This function may be called several times. The first invocation defines
	 * the source on which the parser operates (the parser's mode): given wiki #
	 * text or content of the edit area. The mode does not change, even if this
	 * function is called without wikiText parameter. 
	 * 
	 * @param string wikiText 
	 *		If not <null>, this wiki text is parsed and used for further 
	 * 		operations. Otherwise the text from the edit area is retrieved.
	 *               
	 */
	initialize: function(wikiText) {
		if (wikiText == "") {
			// Empty strings are treated as false => make a non-emty string
			wikiText = " ";
		}
		if (this.parserMode == WTP_WIKITEXT_MODE) {
			// Parser mode is 'wiki text' => do not release the current text
			if (!wikiText) {
				wikiText = this.text;
			}
		}
		if (!wikiText || this.parserMode == WTP_EDITAREA_MODE) {
			// no wiki text => retrieve from text area.
			var txtarea;
			if (document.editform) {
				txtarea = document.editform.wpTextbox1;
			} else {
				// some alternate form? take the first one we can find
				var areas = document.getElementsByTagName('textarea');
				txtarea = areas[0];
			}
	
			if (gEditInterface == null) {
				gEditInterface = new SMWEditInterface();
			}
			this.editInterface = gEditInterface;
			this.text = this.editInterface.getValue();
			this.parserMode = WTP_EDITAREA_MODE;
		} else if (!this.parserMode) {
			this.editInterface = null;
			this.text = wikiText;
			this.parserMode = WTP_WIKITEXT_MODE;
			this.wtsStart = -1; // start of internal wiki text selection
			this.wtsEnd   = -1  // end of internal wiki text selection
			
		}
		if (!this.textChangedHooks) {
			// Array of hooks that are called when the wiki text has been changed
			this.textChangedHooks = new Array(); 
			// Array of hooks that are called when a category has been added
			this.categoryAddedHooks = new Array();
			// Array of hooks that are called when a relation has been added
			this.relationAddedHooks = new Array();
			// Array of hooks that are called when an annotation has been removed
			this.annotationRemovedHooks = new Array();
		}
		
		this.relations  = null;
		this.categories  = null;
		this.links  = null;
		this.rules  = null;
		this.error = WTP_NO_ERROR;
	},
	
	/**
	 * @public
	 * 
	 * Returns the error state of the last parsing process.
	 * 
	 * @return int error
	 * 			WTP_NO_ERROR - no error
	 * 			WTP_UNMATCHED_BRACKETS - Unmatched brackets [[ or ]]
	 */
	getError: function() {
		return this.error;
	},

	/**
	 * @public
	 *
	 * Returns the wiki text from the edit box of the edit page.
	 *
	 * @return string Text from the edit box.
	 */
	getWikiText: function() {
		return this.text;
	},

	/**
	 * @public
	 *
	 * Returns the relations with the given name or null if it is not present.
	 *
	 * @return array(WtpRelation) An array of relation definitions.
	 */
	getRelation: function(name) {
		if (this.relations == null) {
			this.parseAnnotations();
		}
		var matching = new Array();

		for (var i = 0, num = this.relations.length; i < num; ++i) {
			var rel = this.relations[i];
			if (this.equalWikiName(rel.getName(), name)) {
				matching.push(rel);
			}
		}
		return matching.length == 0 ? null : matching;
	},


	/**
	 * @public
	 *
	 * Returns an array that contains the relations, that are annotated in
	 * the current wiki text. Relations within templates are not considered.
	 *
	 * @return array(WtpRelation) An array of relation definitions.
	 */
	getRelations: function() {
		if (this.relations == null) {
			this.parseAnnotations();
		}

		return this.relations;
	},

	/**
	 * @public
	 *
	 * Returns an array that contains the categories, that are annotated in
	 * the current wiki text. Categories within templates are not considered.
	 *
	 * @return array(WtpCategory) An array of category definitions.
	 */
	getCategories: function() {
		if (this.categories == null) {
			this.parseAnnotations();
		}

		return this.categories;
	},

	/**
	 * @public
	 *
	 * Returns the category with the given name or null if it is not present.
	 *
	 * @return WtpCategory The requested category or null.
	 */
	getCategory: function(name) {
		if (this.categories == null) {
			this.parseAnnotations();
		}

		for (var i = 0, num = this.categories.length; i < num; ++i) {
			var cat = this.categories[i];
			if (this.equalWikiName(cat.getName(), name)) {
				return cat;
			}
		}
		return null;
	},


	/**
	 * @public
	 *
	 * Returns an array that contains the links to wiki articles, that are
	 * annotated in the current wiki text. Links within templates are not
	 * considered.
	 *
	 * @return array(WtpLink) An array of link definitions.
	 */
	getLinks: function() {
		if (this.links == null) {
			this.parseAnnotations();
		}

		return this.links;
	},

	/**
	 * @public
	 *
	 * Returns the rule with the given name or null if it is not present.
	 *
	 * @return WtpRule The rule's definitions.
	 */
	getRule: function(name) {
		if (this.rules == null) {
			this.parseAnnotations();
		}
		var matching = new Array();

		for (var i = 0, num = this.rules.length; i < num; ++i) {
			var rule = this.rules[i];
			if (this.equalWikiName(rule.getName(), name)) {
				return rule;
			}
		}
		return null;
	},


	/**
	 * @public
	 *
	 * Returns an array that contains the rules, that are annotated in
	 * the current wiki text. Rules within templates are not considered.
	 *
	 * @return array(WtpRule) An array of rule definitions.
	 */
	getRules: function() {
		if (this.rules == null) {
			this.parseAnnotations();
		}

		return this.rules;
	},


	addTextChangedHook: function(hookFnc) {
		this.textChangedHooks.push(hookFnc);
	},
	
	addCategoryAddedHook: function(hookFnc) {
		this.categoryAddedHooks.push(hookFnc);
	},
	
	addRelationAddedHook: function(hookFnc) {
		this.relationAddedHooks.push(hookFnc);
	},
	
	addAnnotationRemovedHook: function(hookFnc) {
		this.annotationRemovedHooks.push(hookFnc);
	},
	
	/**
	 * @public
	 *
	 * Adds a relation at the current cursor position or replaces the selection
	 * in the text editor.
	 *
	 * @param string name Name of the relation.
	 * @param string value Value of the relation.
	 * @param string representation Representation of the annotation
	 * @param bool append If <true>, the annotation is appended at the very end.
	 *
	 * IMPORTANT!!
	 * After that all parsed data is invalidated as the text might be severely
	 * modified. References to relations etc. are no longer valid. This object
	 * (the wiki text parser) can still be used but the methods <getRelations()>
	 * etc. must be called anew.
	 *
	 */
	 addRelation : function(name, value, representation, append) {
	 	var anno = "[[" + name + "::" + value;
	 	if (representation && value != representation) {
	 		anno += "|" + representation;
	 	}
	 	anno += "]]";
	 	var posInfo = this.addAnnotation(anno, append);
	 	for (var i = 0; i < this.relationAddedHooks.size(); ++i) {
	 		this.relationAddedHooks[i](posInfo[0], posInfo[0] + posInfo[2], name);
	 	}
	 },

	/**
	 * @public
	 *
	 * Adds a category at the current cursor position or replaces the selection
	 * in the text editor.
	 *
	 * @param string name Name of the category.
	 * @param bool append If <true>, the annotation is appended at the very end.
	 *
	 * IMPORTANT!!
	 * After that all parsed data is invalidated as the text might be severely
	 * modified. References to relations etc. are no longer valid. This object
	 * (the wiki text parser) can still be used but the methods <getRelations()>
	 * etc. must be called anew.
	 *
	 */
	 addCategory : function(name, append) {
	 	var anno = "[["+gLanguage.getMessage('CATEGORY_NS') + name;
	 	anno += "]]";
	 	var posInfo = this.addAnnotation(anno, append);
	 	for (var i = 0; i < this.categoryAddedHooks.size(); ++i) {
	 		this.categoryAddedHooks[i](posInfo[0], posInfo[0] + posInfo[2], name);
	 	}
	 },

	/**
	 * @public
	 *
	 * Adds a link at the current cursor position or replaces the selection
	 * in the text editor.
	 *
	 * @param string link The name of the article that is linked.
	 * @param string representation Representation of the annotation
	 * @param bool append If <true>, the annotation is appended at the very end.
	 *
	 * IMPORTANT!!
	 * After that all parsed data is invalidated as the text might be severely
	 * modified. References to relations etc. are no longer valid. This object
	 * (the wiki text parser) can still be used but the methods <getRelations()>
	 * etc. must be called anew.
	 *
	 */
	 addLink : function(link, representation, append) {
	 	var anno = "[[" + link;
	 	if (representation) {
	 		anno += "|" + representation;
	 	}
	 	anno += "]]";
	 	this.addAnnotation(anno, append);
	 },

	/**
	 * @public
	 *
	 * Replaces the annotation described by <annoObj> with the text of
	 * <newAnnotation>. The wiki text is changed and updated in the text area.
	 *
	 * @param WtpAnnotation annoObj Description of the annotation.
	 * @param string newAnnotation New text of the annotation.
	 */
	replaceAnnotation: function(annoObj, newAnnotation) {
		var startText = this.text.substring(0,annoObj.getStart());
		var endText = this.text.substr(annoObj.getEnd());
		var diffLen = newAnnotation.length - annoObj.getAnnotation().length;

		// construct the new wiki text
		this.text = startText + newAnnotation + endText;
		if (this.editInterface) {
			this.editInterface.setValue(this.text);
		}

		var result = [annoObj.getStart(), annoObj.getEnd(), newAnnotation.length];
		for (var i = 0; i < this.textChangedHooks.size(); ++i) {
			this.textChangedHooks[i](result);
		}
		
		// all following annotations have moved => update their location
		this.updateAnnotationPositions(annoObj.getStart(), diffLen);
		
		
	},

	/**
	 * @public
	 * 
	 * Returns the text that is currently selected in the wiki text editor.
	 *
	 * @param boolean trim
	 * 			If <true>, spaces that surround the selection are skipped and
	 * 			the complete annotation including brackets is selected.
	 * @return string Currently selected text.
	 */
	getSelection: function(trim) {
		var text = "";
		if (this.editInterface) {
			trim = true;
			var text = this.editInterface.getSelectedText();
			if (trim == true && text && text.length > 0) {
				var regex = /^(\s*(\[\[)?)\s*(.*?)\s*((\]\])?\s*)$/;
				var parts = text.match(regex);
				if (parts) {
					var rng = this.editInterface.selectCompleteAnnotation();
					return parts[3];
				}
			}
		} else {
			// wiki text mode
			if (this.wtsStart >= 0 && this.wtsEnd >= 0) {
				text = this.text.substring(this.wtsStart, this.wtsEnd);
			}
		}
		return text;
	},

	/**
	 * @public
	 * 
	 * Selects the text in the wiki text editor between the positions <start>
	 * and <end>.
	 *
	 * @param int start
	 * 			0-based start index of the selection
	 * @param int end
	 * 			0-based end index of the selection
	 *
	 */
	setSelection: function(start, end) {
		if (this.editInterface) {
			this.editInterface.setSelectionRange(start, end);
		} else {
			this.wtsStart = start;
			this.wtsEnd = end;
		}
	},

	/**
	 * @public
	 * 
	 * Searches in the given range of the wiki text for the given text. If it is
	 * found, the corresponding section is internally marked as selected and <true>
	 * is returned. Otherwise a reason for the failed search is returned.
	 * 
	 * This function is applied to the wiki text only i.e. it does not use the 
	 * edit area and its content in the edit mode.
	 * 
	 * @param string text
	 * 		This text will be searched in the wiki text
	 * @param int start
	 * 		0-based start index of the range where the search happens
	 * @param int end
	 * 		0-based end index of the range where the search happens. If end==-1,
	 * 		the search runs till the end of the text
	 * @param array<string> context
	 * 		The context of the text i.e. some words before and after
	 * @return
	 * 		boolean <true>, if the text was found
	 * 		string reason why the search failed otherwise
	 */
	findText: function(text, start, end, context) {

		this.wtsStart = -1;
		this.wtsEnd   = -1;
		
		if (end == -1) {
			end = this.text.length;
		}
		
		var withContext = text;
		var preContext = "";
		if (typeof(context) == "object") {
			preContext = context[0] + context[1];
			withContext = preContext + text + context[2] + context[3]; 
		}
		// try a simple search
//		var pos = this.text.indexOf(text, start);
		var pos = this.text.indexOf(withContext, start);
		
		if (pos >= 0 && pos < end) {
			this.wtsStart = pos + preContext.length;
			this.wtsEnd = this.wtsStart + text.length;
			return true;
		}
		
		// consider bold ''' and italic '' formatting instructions
		// Mapping from pure text to wiki text - Example:
		// this is '''bold''' text:&nbsp;space
		// this is bold text: space
		// 012345678911111111112222
		//           01234567890123
		// 012345671111112222233333
		//         1234890123401234
		// 0=>0, 8=>11, 12=>18, 19=>30
		
		var wikitext = this.text.substring(start,end);
		var pureText = '';
		var pti = 0; // Index in pure text
		var wti = 0; // Index in wiki text
		var map = new Array(); // Map from pure text indices to wiki text indices
		var parts = wikitext.split(/('{2,})|(&nbsp;)|(\[\[.*?\]\])|(\[http.*?\])|(\s+)/);
		parts = parts.compact();
		var openApos = 0; // number of opening apostrophes (max 5)
		
		// Rules for finding bold and italic formatting instructions
		var rules = [
			[0,'a',5,3,2],
			[2,'a',3],
			[3,'c',3],
			[3,'a',2],
			[5,'c',5,3,2],
			[3,'c',3,2],
			[2,'c',2]
		];
		var closingRulesStart = 4;
		
		// Count all available apostrophes
		var numApos = 0;
		for (var i = 0; i < parts.length; ++i) {
			if (parts[i].charAt(0) == "'") {
				numApos += parts[i].length;
			}
		}
		
		var lastWasSpace = false;
		for (var i = 0; i < parts.length; ++i) {
			var part = parts[i];
			if (part.length == 0) {
				continue;
			}
			
			if (part.charAt(0) == "'") {
				// a sequence of at least 2 apostrophes
				var num = part.length;
				var rulesStart = 0;
				if (openApos+num > numApos) {
					rulesStart = closingRulesStart;
				}
				numApos -= num;
				var ruleApplied = false;
				for (var r = rulesStart; r < rules.length && !ruleApplied; ++r) {
					var rule = rules[r];
					var writeApos = 0;
					if (openApos == rule[0]) {
						// number of open apostrophes matches the rule
						for (var j = 2; j < rule.length; ++j) {
							if (num >= rule[j]) {
								ruleApplied = true;
								if (rule[1] == 'a') {
									//add opening apostrophes
									openApos += rule[j];
								} else if (rule[1] == 'c') {
									//closing apostrophes
									openApos -= rule[j];
								}
								writeApos = num-rule[j];
								if (writeApos != 0) {
									// write remaining apostrophes to pure text
									map.push([pti,wti+writeApos,openApos]);
									pti += writeApos;
									while (writeApos-- > 0) {
										pureText += "'";
									}
									lastWasSpace = false;
								}
								break;
							}
						}
					} 
				}
			} else if (link = part.match(/\[\[(.*?)(\|.*?)?\]\]/)) {
				var pt = link[2]; // Representation
				if (!pt) {
					pt = link[1]; // link
				}
				pureText += pt;
				map.push([pti,wti,openApos]);
				pti += pt.length;
				lastWasSpace = false;
			} else if (part.match(/\s+/) || part == '&nbsp;') {
				if (!lastWasSpace) {
					pureText += ' ';
					map.push([pti,wti+part.length-1,openApos]);
					pti++;
				}
				lastWasSpace = true;
			} else if (part.charAt(0) == '[') {
				
			} else {
				// normal text
				pureText += part;
				map.push([pti,wti,openApos]);
				pti += part.length;
				lastWasSpace = false;
			}
			wti += part.length;
			
		}
		
		// find the selection in the pure text
		pos = pureText.indexOf(withContext);
		if (pos == -1) {
			pos = pureText.indexOf(text);
		} else {
			pos += preContext.length;
		}
		if (pos == -1) {
			// text not found
			var msg = gLanguage.getMessage('WTP_TEXT_NOT_FOUND');
			msg = msg.replace(/\$1/g, '<b>'+text+'</b>');
			return msg;
		}
		
		// find the start and end indices in the wiki text with the map from
		// pure text indices to wiki text indices.
		var wtStart = -1;
		var wtEnd = -1;
		var startLevel = 0;
		var endLevel = 0;
		var endMapIdx = -1;
		pos += text.length;
		for (var i = map.length-1; i >= 0; --i) {
			if (pos >= map[i][0]) {
				if (wtEnd == -1) {
					wtEnd = map[i][1] + (pos - map[i][0]);
					endLevel = map[i][2];
					endMapIdx = i;
					pos -= text.length;
					++i;
				} else {
					wtStart = map[i][1] + (pos - map[i][0]);
					startLevel = map[i][2];
					if (startLevel != endLevel) {
						// text across different formats
						if (pos == map[i][0]) {
							// maybe we are at the first character of a 
							// bold/italic section
							if (i-1 >= 0 
							    && map[i-1][2] == endLevel 
							    && wikitext.charAt(map[i][1]-1) == "'") {
								wtStart = map[i-1][1] + (pos - map[i-1][0]);
								startLevel = map[i-1][2];
							} else if (i == 0 && endLevel == 0) {
								// selection starts at the very beginning which
								// is formated bold/italic and ends in normal text
								wtStart = 0;
								startLevel = endLevel;
							}
						}
						if (startLevel != endLevel) {
							if (pos+text.length == map[endMapIdx][0]) {
								// maybe we are at the last character of a 
								// bold/italic section
								if (endMapIdx > 0 && map[endMapIdx-1][2] == startLevel) {
									wtEnd -= startLevel;
									endLevel = startLevel;
								}
							} else if (pos+text.length == pureText.length 
									   && startLevel == 0) {
								// Selection ends at the very end
								wtEnd = wikitext.length;
								endLevel = startLevel;
							}
						}
						
					}
					break;
				}
			}
		}
		if (startLevel != endLevel) {
			var msg = gLanguage.getMessage('WTP_SELECTION_OVER_FORMATS');
			msg = msg.replace(/\$1/g, '<b>'+text+'</b>');
			return msg;
		}
		this.wtsStart = wtStart + start;
		this.wtsEnd = wtEnd + start;
//		var wikiText = this.text.substring(this.wtsStart, this.wtsEnd);
//		return "Matching text:<br><b>"+wikitext+"</b><br><b>"+pureText+"</b><br><b>"+wikiText+"</b>";
		return true;
		
	},	 

	/**
	 * @private
	 *
	 * Parses the content of the edit box and retrieves relations, 
	 * categories and links. These are stored in internal arrays.
	 *
	 * <nowiki>, <pre> and <ask>-sections are ignored.
	 */
	parseAnnotations: function() {

		this.relations  = new Array();
		this.categories = new Array();
		this.links      = new Array();
		this.rules      = new Array();
		this.error = WTP_NO_ERROR;

		// Parsing-States
		// 0 - find [[, <nowiki>, <pre> or <ask>
		// 1 - find [[ or ]]
		// 2 - find <nowiki> or </nowiki>
		// 3 - find <ask> or </ask>
		// 4 - find {{#ask:
		// 5 - find <pre> or </pre>
		// 6 - find <rule or </rule>
		var state = 0;
		var bracketCount = 0; // Number of open brackets "[["
		var askCount = 0;  	  // Number of open <ask>-statements
		var currentPos = 0;   // Starting index for next search
		var bracketStart = -1;
		var parsing = true;
		while (parsing) {
			switch (state) {
				case 0:
					// Search for "[[", "<nowiki>", <pre>, <rule or <ask
					var findings = this.findFirstOf(currentPos, ["[[", "<nowiki>", "<pre>", "<ask", "<rule", "{{#ask:"]);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+1;
					bracketStart = -1;
					if (findings[1] == "[[") {
						// opening bracket found
						bracketStart = findings[0];
						bracketCount++;
						state = 1;
					} else if (findings[1] == "<nowiki>") {
						state = 2;
					} else if (findings[1] == "<pre>") {
						state = 5;
					} else if (findings[1] == "<rule") {
						state = 6;
					} else if (findings[1] == "<ask") {
						askCount++;
						state = 3;
					} else if (findings[1] == "{{#ask:") {
						state = 4;
					}
					break;
				case 1:
					// we are within an annotation => search for [[ or ]]
					var findings = this.findFirstOf(currentPos, ["[[", "]]"]);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+2;
					if (findings[1] == "[[") {
						// [[ found
						bracketCount++;
					} else {
						// ]] found
						bracketCount--;
						if (bracketCount == 0) {
							// all opening brackets are closed
							var anno = this.createAnnotation(this.text.substring(bracketStart, findings[0]+2),
							                                 bracketStart, findings[0]+2);
							if (anno) {
								if (anno instanceof WtpRelation) {
									this.relations.push(anno);
								} else if (anno instanceof WtpCategory) {
									this.categories.push(anno);
								} else if (anno instanceof WtpLink) {
									this.links.push(anno);
								}
							}
							state = 0;
						}
					}
					break;
				case 2:
					// we are within a <nowiki>-block
					// => search for </nowiki>
					var findings = this.findFirstOf(currentPos, ["</nowiki>"]);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+7;
					// opening <nowiki> is closed
					state = 0;
					break;
				case 3:
					// we are within an <ask>-block
					// => search for <ask> or </ask>
					var findings = this.findFirstOf(currentPos, ["</ask>", "<ask"]);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+4;
					if (findings[1] == "<ask") {
						// <ask> found
						askCount++;
					} else {
						// </ask> found
						askCount--;
						if (askCount == 0) {
							// all opening <ask>s are closed
							state = 0;
						}
					}
					break;
				case 4:
					// we are within an {{#ask:-template
					var pos = this.parseAskTemplate(currentPos);
					currentPos = (pos == -1) ? currentPos+7 : pos;
					state = 0;
					break;
				case 5:
					// we are within a <pre>-block
					// => search for </pre>
					var findings = this.findFirstOf(currentPos, ["</pre>"]);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+4;
					// opening <pre> is closed
					state = 0;
					break;
				case 6:
					// we are within a <rule>-block
					// => search for </rule>
					var findings = this.findFirstOf(currentPos, ["</rule>"]);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					var start = currentPos-1;
					var end = findings[0]+7;
					var rule = this.parseRule(this.text.substring(start, end), start, end);
					if (rule != null) {
						this.rules.push(rule);
					}
					currentPos = end;
					// opening <rule> is closed
					state = 0;
					break;
			}
		}
		if (bracketCount != 0) {
			this.error = WTP_UNMATCHED_BRACKETS;
		}
	},

	/**
	 * @private
	 *
	 * Parses an ask-template until its end starting at position <currentPos>
	 * in the wikitext. The position after the template is returned.
	 * 
	 * @param int currentPos
	 * 		Start position in the wikitext (right after the opening '{{#ask:'
	 * 
	 * @return int 
	 * 		The position after the closing '}}' or -1, if parsing fails due to
	 * 		syntax error.
	 */
	parseAskTemplate : function(currentPos) {
		var parserTable = new Object();
		parserTable['ask'] = ["{{#ask:", "{{{", "{{", "}}"];
		parserTable['tparam'] = ["}}}"];
		parserTable['tmplt'] = ["{{#ask:", "{{{", "}}"];
		
		var actionTable = new Object();
		actionTable['ask'] = new Object();
		actionTable['ask']["{{#ask:"] = ["push", "ask"];
		actionTable['ask']["{{"]      = ["push", "tmplt"];
		actionTable['ask']["{{{"]     = ["push", "tparam"];
		actionTable['ask']["}}"]      = ["pop"];
		
		actionTable['tparam'] = new Object();
		actionTable['tparam']["}}}"] = ["pop"];
		
		actionTable['tmplt'] = new Object();
		actionTable['tmplt']["{{#ask:"] = ["push", "ask"];
		actionTable['tmplt']["{{{"]     = ["push", "tparam"];
		actionTable['tmplt']["}}"]      = ["pop"];
		
		var stack = new Array();
		stack.push('ask'); // the first opening ask is already parsed
		while (stack.size() > 0) {
			var ct = stack[stack.size()-1];
			var findings = this.findFirstOf(currentPos, parserTable[ct]);
			if (findings[1] == null) {
				// nothing found
				return -1;
			}
			
			var action = actionTable[ct];
			if (!action) {
				return -1;
			}
			action = action[findings[1]];
			if (!action) {
				return -1;
			}
			if (action[0] === 'push') {
				stack.push(action[1]);
			} else if (action[0] === 'pop') {
				stack.pop();
			}
			currentPos = findings[0]+ findings[1].length;
		}
		return currentPos;
	},
	
	/**
	 * @private
	 * 
	 * Parses the rule that is given in <ruleTxt>.
	 * 
	 * @param string ruleTxt
	 * 		Definition of the rule
	 * @param int start
	 * 		Start index of the rule in the wiki text
	 * @param int ent
	 * 		End index of the rule in the wiki text
	 * 
	 * @return WtpRule
	 * 		A rule object or <null> if parsing failed.
	 * 
	 */
	 parseRule: function(ruleTxt, start, end) {
		var hl = ruleTxt.match(/.*hostlanguage\s*=\s*"(.*?)"/);
		var rulename = ruleTxt.match(/.*name\s*=\s*"(.*?)"/);
		var type = ruleTxt.match(/.*type\s*=\s*"(.*?)"/);
		var rule = ruleTxt.match(/<rule(?:.|\s)*?>((.|\s)*?)<\/rule>/m);
		
		if (hl && rulename && type && rule) {
			hl = hl[1];
			rulename = rulename[1];
			type = type[1];
			rule = rule[1];
			return new WtpRule(ruleTxt, start, end, this, rulename, hl, type, rule);
		} else {
			return null;
		} 
		
	 },
	
	/**
	 * @private
	 *
	 * Analyzes an annotation and classifies it as relation, category
	 * or link to other articles. Corresponding objects (WtpRelation,
	 * WtpCategory and WtpLink) are created.
	 * TODO: I18N required for categories
	 *
	 * @param string annotation The complete annotation including the surrounding
	 *                          brackets e.g. [[attr:=1|one]]
	 * @param int start Start position of the annotation in the wiki text
	 * @param int end   End position of the annotation in the wiki text
	 *
	 * @return array(WtpAnnotation) An array of annotation definitions.
	 */
	createAnnotation : function(annotation, start, end) {
		var relRE  = /\[\[\s*(:?)([^:]*)(::|:=)([\s\S\n\r]*)\]\]/;
		var catNS = gLanguage.getMessage('CATEGORY_NS');
		catNS = '['+catNS.charAt(0).toLowerCase() +
		        '|'+catNS.charAt(0).toUpperCase() +
		        ']'+catNS.substring(1);
		var catRE = '\\[\\[\\s*'+catNS+'([\\s\\S\\n\\r]*)\\]\\]';
		catRE = new RegExp(catRE);
//		var catRE  = /\[\[\s*[C|c]ategory:([\s\S\n\r]*)\]\]/;

		var relation = annotation.match(relRE);
		if (relation) {
			// found a relation
			// strip whitespaces from relation name
			var relName = relation[2].match(/[\s\n\r]*(.*)[\s\n\r]*/);
			var valRep = this.getValueAndRepresentation(relation[4]);
			return new WtpRelation(annotation, start, end, this, relation[1],
			                       relName[1], valRep[0], valRep[1]);
		}

		var category = annotation.match(catRE);
		if (category) {
			// found a category
			// strip whitespaces from category name
			var catName = category[1].match(/[\s\n\r]*(.*)[\s\n\r]*/);
			var valRep = this.getValueAndRepresentation(catName[1]);
			return new WtpCategory(annotation, start, end, this, "", // category[1], ignore prefix
			                       valRep[0], valRep[1]);
		}

		// annotation is a link
		var linkName = annotation.match(/\[\[[\s\n\r]*((.|\n)*)[\s\n\r]*\]\]/);
		var valRep = this.getValueAndRepresentation(linkName[1]);
		return new WtpLink(annotation, start, end, this, null,
		                   valRep[0], valRep[1]);

		return null;
	},

	/**
	 * @private
	 *
	 * If something has been replaced in the wiki text, the positions of all
	 * annotations following annotations has to be updated.
	 *
	 * @param int start All annotations starting after this index are moved.
	 *                 (The annotation starting at <start> is NOT moved.)
	 * @param int offset This offset is added to the position of the annotations.
	 */
	updateAnnotationPositions : function(start, offset) {
		if (offset == 0) {
			return;
		}
		var i;
		for (i = 0, len = this.relations.length; i < len; i++) {
			this.relations[i].move(offset, start);
		}
		for (i = 0, len = this.categories.length; i < len; i++) {
			this.categories[i].move(offset, start);
		}
		for (i = 0, len = this.links.length; i < len; i++) {
			this.links[i].move(offset, start);
		}
	},

	/**
	 * @private
	 *
	 * Adds the annotation to the wiki text. If some text is selected, it is
	 * replaced by the annotation. If <append> is <true>, the text is appended.
	 * Otherwise it is inserted at the cursor position.
	 *
	 * IMPORTANT!!
	 * After that all parsed data is invalidated as the text might be severely
	 * modified. References to relations etc. are no longer valid. This object
	 * (the wiki text parser) can still be used but the methods <getRelations()>
	 * etc. must be called anew.
	 *
	 * @param string annotation Annotation that is added to the wiki text.
	 * @param bool append If <true>, the annotation is appended at the very end.
	 * 
	 * @return 
	 * 		boolean false, if the text has been replaced in the edit area
	 * 		array<int>[3], if text was replaced in the wiki text
	 * 			[0]: start index of replacement in original text
	 * 			[1]: end index of replacement in original text
	 * 			[2]: length of inserted text 
	 * 
	 */
	addAnnotation : function(annotation, append) {
		var result = false;
		if (append) {
			if (this.editInterface) {
				this.editInterface.setValue(this.editInterface.getValue() + annotation);
			} else {
				result = [this.text.length, this.text.length, annotation.length];
				this.text += annotation
			}
		} else {
			result = this.replaceText(annotation);
		}
		// invalidate all parsed data
		this.initialize(this.text);
		return result;
	},

	/**
	 *
	 * @private
	 *
	 * Removes the annotation from the internal arrays. The hooks for removed
	 * annotations are called.
	 *
	 * @param WtpAnnotation annotation The annotation that is removed.
	 *
	 */
	removeAnnotation: function(annotation) {
		var annoArray = null;
		if (annotation instanceof WtpRelation) {
			annoArray = this.relations;
		} else if (annotation instanceof WtpCategory) {
			annoArray = this.categories;
		} else if (annotation instanceof WtpLink) {
			annoArray = this.links;
		} else {
			return;
		}

		for (var i = 0, len = annoArray.length; i < len; i++) {
			if (annoArray[i] == annotation) {
				annoArray.splice(i, 1);
				break;
			}
		}
		for (var i = 0; i < this.annotationRemovedHooks.size(); ++i) {
	 		this.annotationRemovedHooks[i](annotation);
	 	}
		
	},

	/**
	 * @private
	 *
	 * Finds the first occurrence of one of the search strings in the current
	 * wiki text or in <findIn>.
	 *
	 * <searchStrings> is an array of strings. This function finds out, which
	 * of these strings appears first in the wiki text or in <findIn>, starting
	 * at position <startPos>.
	 *
	 * @param int startPos Position where the search starts in the wiki text or
	 *                     <findIn>
	 * @param array(string) searchStrings Array of strings that are searched.
	 * @param string findIn If <null> the search strings are searched in the
	 *               current wiki text otherwise in <findIn>
	 *
	 * @return [int pos, string found] The position <pos> of the first occurrence
	 *              of the string <found>.
	 */
	findFirstOf : function(startPos, searchStrings, findIn) {

		var firstPos = -1;
		var firstMatch = null;

		for (var i = 0, len = searchStrings.length; i < len; ++i) {
			var ss = searchStrings[i];
			var pos = findIn ? findIn.indexOf(ss, startPos)
			                 : this.text.indexOf(ss, startPos);
			if (pos != -1 && (pos < firstPos || firstPos == -1)) {
				firstPos = pos;
				firstMatch = ss;
			}
		}

		return [firstPos, firstMatch];

	},


	/**
	 * @private
	 *
	 * The value in an annotation can consist of the actual value and the user
	 * representation. This functions splits and returns both.
	 *
	 * @param string valrep Contains a value and an optional representation
	 *                      e.g. "3.141|about 3"
	 * @return [string value, string representation]
	 *                 value: the extracted value
	 *                 representation: the extracted representation or <null>
	 */
	getValueAndRepresentation: function(valrep) {
		// Parsing-States
		// 0 - find [[, {{ or |
		// 1 - find [[ or ]]
		// 2 - find {{ or }}
		var state = 0;
		var bracketCount = 0; // Number of open brackets "[["
		var curlyCount = 0;   // Number of open brackets "{{"
		var currentPos = 0;   // Starting index for next search
		var parsing = true;
		while (parsing) {
			switch (state) {
				case 0:
					// Search for "[[", "{{" or |
					var findings = this.findFirstOf(currentPos, ["[[", "{{", "|"], valrep);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+1;
					if (findings[1] == "[[") {
						// opening bracket found
						bracketCount++;
						state = 1;
					} else if (findings[1] == "{{") {
						// opening curly bracket found
						curlyCount++;
						state = 2;
					} else {
						// | found
						if (bracketCount == 0) {
							var val = valrep.substring(0, findings[0]);
							var rep = valrep.substring(findings[0]+1);
							return [val, rep];
						}
					}
					break;
				case 1:
					// we are within an annotation => search for [[ or ]]
					var findings = this.findFirstOf(currentPos, ["[[", "]]"], valrep);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+2;
					if (findings[1] == "[[") {
						// [[ found
						bracketCount++;
					} else {
						// ]] found
						bracketCount--;
						if (bracketCount == 0) {
							state = 0;
						}
					}
					break;
				case 2:
					// we are within a template => search for {{ or }}
					var findings = this.findFirstOf(currentPos, ["{{", "}}"], valrep);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+2;
					if (findings[1] == "{{") {
						// {{ found
						curlyCount++;
					} else {
						// }} found
						curlyCount--;
						if (curlyCount == 0) {
							state = 0;
						}
					}
					break;
			}
		}
		return [valrep, null];
	},


	/**
	 * Inserts a text at the cursor or replaces the current selection. This applies
	 * also, if only the wiki text if given without an edit area.
	 *
	 * @param string text The text that is inserted.
	 * @return 
	 * 		boolean false, if the text has been replaced in the edit area
	 * 		array<int>[3], if text was replaced in the wiki text
	 * 			[0]: start index of replacement in original text
	 * 			[1]: end index of replacement in original text
	 * 			[2]: length of inserted text 
	 *
	 */
	replaceText : function(text)  {
		if (this.editInterface) {
			this.editInterface.setSelectedText(text);
		} else if (this.wtsStart >= 0) {
			this.text = this.text.substring(0, this.wtsStart)
			            + text
			            + this.text.substring(this.wtsEnd);
			var result = [this.wtsStart, this.wtsEnd, text.length];
			for (var i = 0; i < this.textChangedHooks.size(); ++i) {
				this.textChangedHooks[i](result);
			}
			this.wtsStart = -1;			 
			this.wtsEnd   = -1;
			return result;
		}
		return false;
	},

	/**
	 * Checks if two names are equal with respect to the wiki rule i.e. the
	 * first character is case insensitive, the rest is.
	 *
	 * @param string name1 The first name to compare
	 * @param string name2 The second name to compare
	 *
	 * @return bool <true> is the names are equal, <false> otherwise.
	 */
	equalWikiName : function(name1, name2) {
		if (name1.substring(1) == name2.substring(1)) {
			if (name1.charAt(0).toLowerCase() == name2.charAt(0).toLowerCase()) {
				return true;
			}
		}
		return false;
	}
};
