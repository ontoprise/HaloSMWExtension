/**
 * WikiTextParser.js
 *
 * Class for parsing annotations in wiki text.
 *
 * @author Thomas Schweitzer
 */

var WTP_NO_ERROR = 0;
var WTP_UNMATCHED_BRACKETS = 1;

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
	 * Constructor. Stores the textarea from the edit page and initializes the
	 * lists of annotations.
	 */
	initialize: function() {
		var txtarea;
		if (document.editform) {
			txtarea = document.editform.wpTextbox1;
		} else {
			// some alternate form? take the first one we can find
			var areas = document.getElementsByTagName('textarea');
			txtarea = areas[0];
		}

		this.textarea = txtarea;
		//this.text = this.textarea.value;
		if (gEditInterface == null) {
			gEditInterface = new SMWEditInterface();
		}
		this.editInterface = gEditInterface;
//		this.editInterface.initialize();
		this.text = this.editInterface.getValue();

		this.relations  = null;
		this.categories  = null;
		this.links  = null;
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
	 * @puplic
	 *
	 * Returns the wiki text from the edit box of the edit page.
	 *
	 * @return string Text from the edit box.
	 */
	getWikiText: function() {
		//return this.editInterface.getValue();
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
	 	var anno = "[[" + name + ":=" + value;
	 	if (representation) {
	 		anno += "|" + representation;
	 	}
	 	anno += "]]";
	 	this.addAnnotation(anno, append);
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
	 	var anno = "[["+gLanguage.getMessage('CATEGORY') + name;
	 	anno += "]]";
	 	this.addAnnotation(anno, append);
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
		//this.text = this.editInterface.getValue();
		var startText = this.text.substring(0,annoObj.getStart());
		var endText = this.text.substr(annoObj.getEnd());
		var diffLen = newAnnotation.length - annoObj.getAnnotation().length;

		// construct the new wiki text
		this.text = startText + newAnnotation + endText;
		//this.textarea.value = this.text;
		this.editInterface.setValue(this.text);

		// all following annotations have moved => update their location
		this.updateAnnotationPositions(annoObj.getStart(), diffLen);
	},

	/**
	 * Returns the text that is currently selected in the wiki text editor.
	 *
	 * @return string Currently selected text.
	 */
	getSelection: function() {
		return this.editInterface.getSelectedText();
	},

	/**
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
		this.editInterface.setSelectionRange(start, end);
	},

	/**
	 * @private
	 *
	 * Parses the content of the edit box and retrieves relations, 
	 * categories and links. These are stored in internal arrays.
	 *
	 * <nowiki> and <ask>-sections are ignored.
	 */
	parseAnnotations: function() {

		this.relations  = new Array();
		this.categories = new Array();
		this.links      = new Array();
		this.error = WTP_NO_ERROR;

		// Parsing-States
		// 0 - find [[, <nowiki> or <ask>
		// 1 - find [[ or ]]
		// 2 - find <nowiki> or </nowiki>
		// 3 - find <ask> or </ask>
		var state = 0;
		var bracketCount = 0; // Number of open brackets "[["
		var nowikiCount = 0;  // Number of open <nowiki>-statements
		var askCount = 0;  	  // Number of open <ask>-statements
		var currentPos = 0;   // Starting index for next search
		var bracketStart = -1;
		var parsing = true;
		while (parsing) {
			switch (state) {
				case 0:
					// Search for "[[", "<nowiki>" or <ask>
					var findings = this.findFirstOf(currentPos, ["[[", "<nowiki>", "<ask"]);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+1;
					if (findings[1] == "[[") {
						// opening bracket found
						bracketStart = findings[0];
						bracketCount++;
						state = 1;
					} else if (findings[1] == "<nowiki>") {
						// <nowiki> found
						bracketStart = -1;
						nowikiCount++;
						state = 2;
					} else {
						// <ask> found
						bracketStart = -1;
						askCount++;
						state = 3;
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
					// => search for <nowiki> or </nowiki>
					var findings = this.findFirstOf(currentPos, ["</nowiki>", "<nowiki>"]);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+7;
					if (findings[1] == "<nowiki>") {
						// <nowiki> found
						nowikiCount++;
					} else {
						// </nowiki> found
						nowikiCount--;
						if (nowikiCount == 0) {
							// all opening <nowiki>s are closed
							state = 0;
						}
					}
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
			}
		}
		if (bracketCount != 0) {
			this.error = WTP_UNMATCHED_BRACKETS;
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
		var catRE  = /\[\[\s*[C|c]ategory:([\s\S\n\r]*)\]\]/;

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
	 */
	addAnnotation : function(annotation, append) {
		if (append) {
			//this.textarea.value = this.textarea.value + annotation;
			this.editInterface.setValue(this.editInterface.getValue() + annotation);
		} else {
			this.replaceText(annotation);
		}
		// invalidate all parsed data
		this.initialize();
	},

	/**
	 *
	 * @private
	 *
	 * Removes the annotation from the internal arrays.
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
	 * Inserts a text at the cursor or replaces the current selection.
	 *
	 * @param string text The text that is inserted.
	 *
	 */
	replaceText : function(text)  {
		this.editInterface.setSelectedText(text);
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
