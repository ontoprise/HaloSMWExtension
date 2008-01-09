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
* 
* @author Thomas Schweitzer
*/
//Constants
var AA_RELATION = 0;
var AA_CATEGORY = 1;
	
var AdvancedAnnotation = Class.create();

/**
 * This class handles selections in the rendered wiki page. It loads the 
 * corresponding wiki text from the server and tries to match HTML and wiki text.
 * Annotations can be added to the wiki text and are highlighted in the rendered
 * page.
 */
AdvancedAnnotation.prototype = {
	
	
	/**
	 * Initializes an instance of this class.
	 */
	initialize: function() {
		// Selection information
		this.annotatedNode = null;
		this.focusNode = null;
		this.annoOffset = null;
		this.selectedText = '';
		
		// The wiki text parser manages the wiki text and adds annotations 
		this.wikiTextParser = null;
		
		this.om = new OntologyModifier();
		this.om.addEditArticleHook(this.annotationsSaved.bind(this));
		
		// Load the wiki text for the current page and store it in the parser.
		this.loadWikiText();
		this.annoCount = 10000;
		this.annotationsChanged = false;
		
		this.contextMenu = null;
		
		// Invalidate the HTML-cache for this article
		this.om.touchArticle(wgPageName);
		
	},
	
	/**
	 * This method is called, when the mouse button is released. The current
	 * selection is retrieved and used as annotation. Only events in div#bodyContent 
	 * are processed
	 */
	onMouseUp: function(event) {
		smwhgAnnotationHints.hideHints();
		this.hideToolbar();
		
		// Check if the event occurred in div#bodyContent
		var target = event.target;
		while (target) {
			if (target.id && target.id == 'bodyContent') {
				break;
			}
			target = target.up('div');
		}
		if (!target) {
			// event was outside of div#bodyContent
			return;
		}
		var annoSelection = this.getSel();
		if (annoSelection.anchorNode == null) {
			// nothing selected
			annoSelection = null;
		}
		this.selection = annoSelection;
		
		var cba = this.canBeAnnotated(annoSelection);
			
		if (annoSelection && annoSelection != '' && !cba) {
			// a non empty selection can not be annotated
			smwhgAnnotationHints.showMessageAndWikiText(
				gLanguage.getMessage('CAN_NOT_ANNOTATE_SELECTION'), "", 
				event.clientX, event.clientY);
		}
				
		if (cba && annoSelection != '') {
			// store details of the selection
			this.selectedText = annoSelection.toString();
			//trim selection
			this.selectedText = this.selectedText.replace(/^\s*(.*?)\s*$/,'$1');
			this.annotatedNode = annoSelection.anchorNode;
			this.annoOffset    = annoSelection.anchorOffset;
			this.focusNode   = annoSelection.focusNode;
			this.focusOffset = annoSelection.focusOffset;
			
			this.performAnnotation(event);
		}
	},
	
	/*
	 * Callback for key-up events. 
	 * When the ESC-key is released, the context menu is hidden.
	 * 
	 * @param event 
	 * 			The key-up event.
	 */
	onKeyUp: function(event){
		
		var key = event.which || event.keyCode;
		if (key == Event.KEY_ESC) {
			this.hideToolbar();
		}
	},
	
	/**
	 * Checks if the <selection> can be annotated, as far as this can be decided
	 * on the HTML level. This is the case, if it does not contain an annotation
	 * or a paragraph.
	 * 
	 * @param selection
	 * 			The selection may contain several nodes, starting at the
	 * 			anchorNode and ending at the focusNode. All nodes between these
	 * 			are analysed.
	 * 
	 * @return boolean
	 * 		<false>, if a span with type 'annotationHighlight' or a paragraph 
	 * 		         is among the selected nodes.
	 * 		<true>, otherwise
	 */
	canBeAnnotated: function(selection) {
		
		if (!selection) {
			return false;
		}
		var anchorNode = selection.anchorNode;
		var focusNode = selection.focusNode;
		
		var an = anchorNode;
		if (!an) {
			return false;
		}
		if (!$(an).up) {
			an = an.parentNode;
		}
		if ($(an).getAttribute('type') === "annotationHighlight") {
			return false;
		} else {
			var annoHighlight = $(an).up('span[type="annotationHighlight"]');
			if (annoHighlight) {
				return false;
			}
		}
	
		var fn = focusNode;
		if (!$(fn).up) {
			fn = fn.parentNode;
		}
		if ($(fn).getAttribute('type') === "annotationHighlight") {
			return false;
		} else {
			var annoHighlight = $(fn).up('span[type="annotationHighlight"]');
			if (annoHighlight) {
				return false;
			}
		}
	
		if (anchorNode !== focusNode) {
			var next = this.searchForward(anchorNode, this.searchSelectionEnd.bind(this));
			var prev = this.searchBackwards(anchorNode, this.searchSelectionEnd.bind(this));
			if (next !== focusNode && prev !== focusNode) {
				return false;
			}
		}
		
		return true;
	},
	
	/**
	 * Tries to find the current selection in the wiki text. If successful, the
	 * corresponding wiki text is augmented with an annotation.
	 * 
	 * @param event
	 * 		The mouse up event
	 */
	performAnnotation: function(event) {
		var anchor = null;
		var firstAnchor = null;
		var secondAnchor = null;
				
		firstAnchor = this.searchBackwards(this.annotatedNode, 
										   this.searchWtoAnchor.bind(this));
		secondAnchor = this.searchForward(this.focusNode, 
										  this.searchWtoAnchor.bind(this));

		if (firstAnchor) {
			var start = firstAnchor.getAttribute('name')*1;
			var end = (secondAnchor != null)
						? secondAnchor.getAttribute('name')*1
						: -1;
			// The selection must not contain invalid nodes like pre, nowiki etc.
			var invalid = this.searchInvalidNode(firstAnchor);
			if (!invalid && this.annotatedNode != this.focusNode) {
				// the selection spans several nodes
				invalid = this.searchForward(firstAnchor, 
										     this.searchInvalidNode.bind(this),
										     secondAnchor);
			}
			if (invalid && invalid !== true) {
				// an invalid node has been found.
				var obj = invalid.getAttribute('obj');
				var msgId = "This selection can not be annotated.";
				switch (obj) {
					case 'nowiki': msgId = 'WTP_NOT_IN_NOWIKI'; break;
					case 'template': msgId = 'WTP_NOT_IN_TEMPLATE'; break;
					case 'annotation': msgId = 'WTP_NOT_IN_ANNOTATION'; break;
					case 'ask': msgId = 'WTP_NOT_IN_QUERY'; break;
					case 'pre': msgId = 'WTP_NOT_IN_PREFORMATTED'; break;
				}
				msg = gLanguage.getMessage(msgId);
				msg = msg.replace(/\$1/g, this.selectedText);
				smwhgAnnotationHints.showMessageAndWikiText("(e)"+msg,
															this.wikiTextParser.text.substring(start,end),
															event.clientX, event.clientY);

				this.toolbarEnableAnnotation(false);
				return;
			}										     
			
			var res = this.wikiTextParser.findText(this.selectedText, start, end);
			if (res != true) {
				this.toolbarEnableAnnotation(true);
				smwhgAnnotationHints.showMessageAndWikiText("(e)"+res,
															this.wikiTextParser.text.substring(start,end),
															event.clientX, event.clientY);
			} else {
				this.toolbarEnableAnnotation(false);
/*
				smwhgAnnotationHints.showMessageAndWikiText(
					"(i)Wikitext found for selection:<br><b>"+this.selectedText+"</b>",
					this.wikiTextParser.text.substring(start,end),
					event.clientX, event.clientY);
*/					
				// Show toolbar at the cursor position
				this.annotateWithToolbar(event);

			}
		} else {
			this.toolbarEnableAnnotation(false);
			smwhgAnnotationHints.showMessageAndWikiText("(e)No wiki text found for selection:",
			                                            "<b>"+this.selectedText+"</b>",
														event.clientX, event.clientY);
		}
	
	},
	
	/**
	 * Enables or disables the annotation actions in the semantic toolbar.
	 * 
	 * @param boolean enable
	 * 		true  => enable actions
	 * 		false => disable actions
	 */
	toolbarEnableAnnotation: function(enable) {
		catToolBar.enableAnnotation(enable);
	},
	
	/**
	 * Displays the semantic toolbar at the cursor position and shows the
	 * dialogs for annotating categories or properties.
	 * 
	 * @param event
	 * 		The event contains the coordinates for the position of the toolbar.
	 * 
	 */
	annotateWithToolbar: function(event) {
		if (!this.contextMenu) {
			this.contextMenu = new ContextMenuFramework();
		}
		relToolBar.createContextMenu(this.contextMenu);
		catToolBar.createContextMenu(this.contextMenu);
		this.contextMenu.setPosition(event.clientX, event.clientY);
		this.contextMenu.showMenu();
	},
	
	/**
	 * Hides the toolbar if annotation has been cancelled.
	 */
	hideToolbar: function() {
		if (this.contextMenu) {
			this.contextMenu.remove();
			this.contextMenu = null;
		}
		this.toolbarEnableAnnotation(true);
		this.annotatedNode = null;
		this.wikiTextParser.setSelection(-1, -1);
	},
	
	searchWtoAnchor: function(node, parameters) {
		if (node.tagName == 'A' 
		    && node.type == "wikiTextOffset"
		    && node.getAttribute('annoType') != 'category') {
			return node;
		} 
	},
	
	searchSelectionEnd: function(node, parameters) {
		if (node.tagName == 'P') {
			// end search at paragraphs
			return true;
		}
		if (node === this.selection.focusNode) {
			return node;
		} else if (node.getAttribute && 
				   node.getAttribute('type') === 'annotationHighlight') {
			return node;
		}
	},
	
	/**
	 * Visits all nodes between the first and the second anchor of the selection.
	 * The selection must not span invalid nodes i.e. nowiki, pre, ask, template, 
	 * annotations. If such a node is found, it is returned. Otherwise the search
	 * is terminated with the result <true>.
	 * 
	 * @param DomNode node
	 * 		The node that is currently visited
	 * @param DomNode secondAnchor
	 * 		The search end, if this node is reached.
	 * @return DomNode or boolean
	 * 		The invalid DOM-node or <true>, if the secondAnchor has been reached.
	 */
	searchInvalidNode: function(node, secondAnchor) {
		if (node === secondAnchor) {
			return true;
		}
		if (node.tagName == 'A' 
		    && node.type == "wikiTextOffset") {
			var obj = node.getAttribute('obj');
			if (obj === 'pre'
//				|| obj === 'annotation'
			    || obj === 'ask'
			    || obj === 'nowiki'
//			    || obj === 'newline'
			    || obj === 'template') {
				return node;
			}
		}
		
	},
			
	/**
	 * Searches recursively backwards from the given node <startNode> to the top
	 * of the document. The document order is traversed in reverse order, visiting
	 * all nodes.
	 * 
	 * @param DomNode startNode
	 * 		Traversal starts at this node. The callback is not called for it.
	 * @param function cbFnc
	 * 		This callback function is called at each node. Traversal stops,
	 * 		if it returns a value. Signature:
	 * 		returnValue function(DomNode node, Object parameters)
	 * @param object parameters
	 * 		This can be any object. It is passed as second parameter to the 
	 * 		callback function <cbFnc>
	 * @param boolean diveDeeper
	 * 		Only uses internally. Don't specify this value.
	 */
	searchBackwards: function(startNode, cbFnc, parameters, diveDeeper) {
		var node = startNode;
		if (!diveDeeper) {
			// go to the previous sibling or the sibling of a parent node
			while (node) {
				if (node.previousSibling) {
					node = node.previousSibling;
					break;
				}
				node = node.parentNode;
			}
		}	
		while (node) {
			// process all siblings and their children
			if (node.lastChild) {
				var result = this.searchBackwards(node.lastChild, cbFnc, parameters, true);
				if (result) {
					return result;
				}
			}
			var result = cbFnc(node, parameters);
			if (result) {
				return result;
			} 
			if (node.previousSibling) {
				node = node.previousSibling;
			} else {
				break;
			}
		}
		if (!diveDeeper && node) {
			node = node.parentNode;
			if (node) {
				var result = this.searchBackwards(node, cbFnc, parameters);
				if (result) {
					return result;
				}
			}
		}
		return null;
		
	},
	

	/**
	 * Searches recursively forward from the given node <startNode> to the end
	 * of the document. The document order is traversed in normal order, visiting
	 * all nodes.
	 * 
	 * @param DomNode startNode
	 * 		Traversal starts at this node. The callback is not called for it.
	 * @param function cbFnc
	 * 		This callback function is called at each node. Traversal stops,
	 * 		if it returns a value. Signature:
	 * 		returnValue function(DomNode node, Object parameters)
	 * @param object parameters
	 * 		This can be any object. It is passed as second parameter to the 
	 * 		callback function <cbFnc>
	 * @param boolean diveDeeper
	 * 		Only uses internally. Don't specify this value.
	 */
	searchForward: function(startNode, cbFnc, parameters, diveDeeper) {
		var node = startNode;
		if (!diveDeeper) {
			// go to the next sibling or the sibling of a parent node
			while (node) {
				if (node.nextSibling) {
					node = node.nextSibling;
					break;
				}
				node = node.parentNode;
			}
		}	
		while (node) {
			// process all siblings and their children
			if (node.firstChild) {
				var result = this.searchForward(node.firstChild, cbFnc, parameters, true);
				if (result) {
					return result;
				}
			}
			var result = cbFnc(node, parameters);
			if (result) {
				return result;
			} 
			if (node.nextSibling) {
				node = node.nextSibling;
			} else {
				break;
			}
		}
		if (!diveDeeper && node) {
			node = node.parentNode;
			if (node) {
				var result = this.searchForward(node, cbFnc, parameters);
				if (result) {
					return result;
				}
			}
		}
		return null;
		
	},
	
	/**
	 * Gets the current selection from the browser.
	 */
	getSel: function() {
		var txt = '';
		if (window.getSelection) {
			txt = window.getSelection();
		} else if (document.getSelection) {
			txt = document.getSelection();
		} else if (document.selection) {
			txt = document.selection.createRange().text;
		}
		return txt;
	},
		
	/**
	 * @public
	 * 
	 * Loads the current wiki text via an ajax call. The wiki text is stored in
	 * the wiki text parser <this.wikiTextParser>.
	 * 
	 */
	loadWikiText : function() {
		function ajaxResponseLoadWikiText(request) {
			if (request.status == 200) {
				// success => store wikitext
				this.wikiTextParser = new WikiTextParser(request.responseText);
				this.wikiTextParser.addTextChangedHook(this.updateAnchors.bind(this));
				this.wikiTextParser.addCategoryAddedHook(this.categoryAdded.bind(this));
				this.wikiTextParser.addRelationAddedHook(this.relationAdded.bind(this));
				this.wikiTextParser.addAnnotationRemovedHook(this.annotationRemoved.bind(this));
				catToolBar.setWikiTextParser(this.wikiTextParser);
				relToolBar.setWikiTextParser(this.wikiTextParser);
				catToolBar.fillList(true);
				relToolBar.fillList(true);
			} else {
				this.wikiTextParser = null;
			}
		};
		
		sajax_do_call('smwfGetWikiText', 
		              [wgPageName], 
		              ajaxResponseLoadWikiText.bind(this));
		              
		              
	},
	
	/**
	 * This function is a hook for the wiki text parser. It is called after a
	 * category has been added to the wiki text.
	 * The currently selected text is highlighted with a background specific for
	 * categories. The selection is reset.
	 * 
	 * @param int startPos
	 * 		Start position of the new annotation
	 * @param int endPos
	 * 		End position of the new annotation
	 * @param string name
	 * 		Name of the new category.
	 */
	categoryAdded: function(startPos, endPos, name) {
		this.highlightSelection(AA_CATEGORY, 'aam_new_category_highlight', startPos, endPos);
		catToolBar.fillList();
		smwhgSaveAnnotations.markDirty();
		this.annotationsChanged = true;
		if (this.contextMenu) {
			this.hideToolbar();
		}
	},
	
	/**
	 * This function is a hook for the wiki text parser. It is called after a
	 * relation has been added to the wiki text.
	 * The currently selected text is highlighted with a background specific for
	 * relations. The selection is reset.
	 * 
	 * @param int startPos
	 * 		Start position of the new annotation
	 * @param int endPos
	 * 		End position of the new annotation
	 * @param string name
	 * 		Name of the new relation.
	 */
	relationAdded: function(startPos, endPos, name) {
		if (this.annotationProposal) {
			this.markProposal(AA_RELATION, 'aam_new_anno_prop_highlight');
			this.annotationProposal = null;
		} else {
//			this.markSelection(AA_RELATION, 'aam_new_anno_prop_highlight', startPos, endPos);
			this.highlightSelection(AA_RELATION, 'aam_new_anno_prop_highlight', startPos, endPos);
		}
		relToolBar.fillList();
		smwhgSaveAnnotations.markDirty();
		this.annotationsChanged = true;
		this.hideToolbar();
	},
	
	
	/**
	 * This function is a hook for the wiki text parser. It is called after an
	 * annotation has been removed from the wiki text.
	 * The highlight for the annotation in the rendered article is removed.
	 * 
	 * @param WtpAnnotation annotation
	 * 		The annotation that is removed.
	 */
	annotationRemoved: function(annotation) {
		this.removeAnnotationHighlight(annotation);
		smwhgSaveAnnotations.markDirty();
		this.annotationsChanged = true;
	},

	/**
	 * Embraces the currently selected text with a <span> tag with the css style
	 * <cssClass>.
	 * @param int type
	 * 		The selection is either AA_RELATION or AA_CATEGORY
	 * @param string cssClass
	 * 		Name of the css style that is added as class to the <span> tag.
	 * @param int startPos
	 * 		Wikitextoffset of the new annotation's start that has been created 
	 * 		for the	selection.
	 * @param int endPos
	 * 		Wikitextoffset of the new annotation's end.
	 */
/*	markSelection: function(type, cssClass, startPos, endPos) {
		if (!this.annotatedNode || this.selectedText === "") {
			return;
		}
		
		var imgPath = wgScriptPath + "/extensions/SMWHalo/skins/Annotation/images/"
		var annoDeco =
			'<a href="javascript:AdvancedAnnotation.smwhfEditAnno('+this.annoCount+')">'+
			((type == AA_RELATION) 
				? '<img src="' + imgPath + 'edit.gif"/>'
				: "" ) +
			'</a>' +
			'<span id="anno' + this.annoCount +
				'" class="' +cssClass +
				'" type="annotationHighlight">' +
				this.selectedText +
			'</span>'+
			'<a href="javascript:AdvancedAnnotation.smwhfDeleteAnno('+this.annoCount+')">'+
   			'<img src="' + imgPath + 'delete.png"/></a>';
   		
   		// add a wrapper span
   		if (this.selectedText.length <= 20) {
			annoDeco = '<span id="anno'+this.annoCount+'w" style="white-space:nowrap">'+
						annoDeco +
						'</span>';
   		} else {
			annoDeco = '<span id="anno'+this.annoCount+'w">'+
						annoDeco +
						'</span>';   			
   		}
   		
   		var annoType = (type == AA_RELATION) 
   						? 'annoType="relation"'
   						: 'annoType="category"';

		// add wiki text offset anchors around the highlight   						
   		annoDeco = '<a type="wikiTextOffset" name="'+startPos+'" '+annoType+'></a>' 
   		           + annoDeco
   		           + '<a type="wikiTextOffset" name="'+endPos+'" '+annoType+'></a>';
   		
		var parentNode = this.annotatedNode.parentNode;
		var node = this.annotatedNode; // node is probably a text node 
		var origText = node.textContent;
		if (origText.indexOf(this.selectedText) < 0) {
			// node is not a text node i.e. it does not contain the selected
			// text => the parent node should contain the selection
			node = parentNode;
			origText = node.innerHTML;
			var newText = origText.replace(this.selectedText, annoDeco);
			node.innerHTML = newText;
		} else {
			// find the selected text in the text node after the position specified
			// by the selection
			var newText = origText.substring(0, this.annoOffset);
			newText += origText.substring(this.annoOffset)
			                   .replace(this.selectedText, annoDeco);
			// create a DOM structure for the text that is now surrounded by a <span>
			newText = Object.toHTML(newText);
			var range = parentNode.ownerDocument.createRange();
			range.selectNode(parentNode);
			newText.evalScripts.bind(newText).defer();
			newText = range.createContextualFragment(newText.stripScripts());
			// replace the original text node with the highlighted node
			parentNode.replaceChild(newText, node);
		}
		// reset selection information
		this.resetSelection();
		this.annoCount++;
	},
*/	
	/**
	 * Resets the stored selection information.
	 */
	resetSelection: function() {
		this.selection = null;
		this.annotatedNode = null;
		this.focusNode = null;
		this.annoOffset = 0;
		this.focusOffset = 0;
	},
	
	/**
	 * Embraces the currently selected text with a <span> tag with the css style
	 * <cssClass>.
	 * @param int type
	 * 		The selection is either AA_RELATION or AA_CATEGORY
	 * @param string cssClass
	 * 		Name of the css style that is added as class to the <span> tag.
	 * @param int startPos
	 * 		Wikitextoffset of the new annotation's start that has been created 
	 * 		for the	selection.
	 * @param int endPos
	 * 		Wikitextoffset of the new annotation's end.
	 */
	highlightSelection: function(type, cssClass, startPos, endPos) {

		if (!this.annotatedNode || this.selectedText === "") {
			return;
		}
		
		var imgPath = wgScriptPath + "/extensions/SMWHalo/skins/Annotation/images/"
		var annoDecoStart =
			'<a href="javascript:AdvancedAnnotation.smwhfEditAnno('+this.annoCount+')">'+
			((type == AA_RELATION) 
				? '<img src="' + imgPath + 'edit.gif"/>'
				: "" ) +
			'</a>' +
			'<span id="anno' + this.annoCount +
				'" class="' +cssClass +
				'" type="annotationHighlight">';
		var annoDecoEnd =
			'</span>'+
			'<a href="javascript:AdvancedAnnotation.smwhfDeleteAnno('+this.annoCount+')">'+
   			'<img src="' + imgPath + 'delete.png"/></a>';
   		
   		// add a wrapper span
   		if (this.selectedText.length <= 20) {
			annoDecoStart = '<span id="anno'+this.annoCount+'w" style="white-space:nowrap">'+
						annoDecoStart;
			annoDecoEnd += '</span>';
   		} else {
			annoDecoStart = '<span id="anno'+this.annoCount+'w">'+
						annoDecoStart;
			annoDecoEnd += '</span>';
   		}
   		
   		var annoType = (type == AA_RELATION) 
   						? 'annoType="relation"'
   						: 'annoType="category"';

		// add wiki text offset anchors around the highlight   						
   		annoDecoStart = '<a type="wikiTextOffset" name="'+startPos+'" '+annoType+'></a>' 
   		                + annoDecoStart;
		annoDecoEnd += '<a type="wikiTextOffset" name="'+endPos+'" '+annoType+'></a>';

		var first = this.annotatedNode;
		var second = this.focusNode;
		var foff = this.annoOffset;
		var soff = this.focusOffset;
		
		var t = second.textContent;
		t = t.substring(0, soff) + '###end###' + t.substring(soff);
		second.textContent = t;
		
		t = first.textContent;
		t = t.substring(0, foff) + '###start###' + t.substring(foff);
		first.textContent = t;

		var p1 = first.parentNode;
		var p2 = second.parentNode;
		var html1 = p1.innerHTML;
		html1 = html1.replace(/###start###/, annoDecoStart);
		html1 = html1.replace(/###end###/, annoDecoEnd);
		if (p1 === p2) {
			p1.innerHTML = html1;
		} else {
			// The first and the last node of the selection are different
			var html2 = p2.innerHTML;
			// a selection might start within a bold or italic node and end
			// somewhere else => create the span outside the formatted node.
			html2 = html2.replace(/(<b><i>|<i><b>|<i>|<b>)###start###/, '###start###$1');
			html2 = html2.replace(/###start###/, annoDecoStart);
			html2 = html2.replace(/###end###/, annoDecoEnd);
			p1.innerHTML = html1;
			p2.innerHTML = html2;
		}
		
		// reset the current selection
		this.resetSelection();
		
		// The highlighted section may contain annotation proposal => hide them
		var wrapperSpan = $("anno"+this.annoCount+"w");
		
		var proposals = wrapperSpan.descendants();
		for (var i = 0; i < proposals.length; ++i) {
			var p = proposals[i];
			if (p.id.match(/anno\d*w/)) {
				this.hideProposal(p);
			}
		} 
		this.annoCount++;
	},

	
	/**
	 * An annotation proposal is highlighted with a green border and a "+"-icon.
	 * This highlight is replaced by the normal highlight of annotations.
	 * 
	 * @param int type
	 * 		The selection is either AA_RELATION or AA_CATEGORY
	 * @param string cssClass
	 * 		Name of the css style that is added as class to the <span> tag.
	 */
	markProposal: function(type, cssClass) {
		if (!this.annotationProposal) {
			return;
		}
		var text = this.annotationProposal.textContent;
		
		var wrapper = this.annotationProposal;
		wrapper.id = 'anno'+this.annoCount+'w';
		if (text.length < 20) {
			wrapper.setStyle("white-space:nowrap");
		}
		if (type == AA_RELATION) {
			var imgPath = wgScriptPath + "/extensions/SMWHalo/skins/Annotation/images/"
			$(wrapper.down('a'))
				.replace('<a href="javascript:AdvancedAnnotation.smwhfEditAnno('+this.annoCount+')">'+
						 '<img src="' + imgPath + 'edit.gif"/>' +
						 '</a>')
		} else {
			$(wrapper.down('a')).remove();
		}
		
		var innerSpan = $(wrapper.down('span'));
		innerSpan.className = cssClass;
		innerSpan.id = 'anno' + this.annoCount;
		
		Insertion.Bottom(wrapper, 
			'<a href="javascript:AdvancedAnnotation.smwhfDeleteAnno('+this.annoCount+')">'+
   			'<img src="' + imgPath + 'delete.png"/></a>'
		);
		this.annoCount++;
		
	},
	
	/**
	 * Hides a proposal.
	 * Proposals are highlighted with a green border and a (+)-button. All this
	 * is contained in a <span> that surrounds the actual text. This method hides 
	 * the proposal visually, without deleting the <span> etc. Thus it can be
	 * restored later.
	 * 
	 * @param DomNode wrapperSpan
	 * 		This DOM node is the wrapper <span> around the proprosed text. 
	 */
	hideProposal: function(wrapperSpan) {
		var img = wrapperSpan.down('img');
		if (img) {
			img.hide();
		}
		var span = wrapperSpan.down('span');
		if (span) {
			span.className = '';
		}
	},
		
	/**
	 * This function is a hook for changed text in the wiki text parser. 
	 * It updates the anchors with the wiki text offsets in the DOM after text
	 * has been added or removed.
	 * If a property-annotation has been changed, it gets the highlight style
	 * of a new annotation. 
	 * 
	 * @param array<int>[3] textModifications
	 * 			[0]: start index of replacement in original text
	 * 			[1]: end index of replacement in original text
	 * 			[2]: length of inserted text 
	 */
	updateAnchors: function(textModifications) {
								
//		alert("Added at: "+textModifications.toString());
		if (textModifications) {
			// update anchors
			var start = textModifications[0];
			var end = textModifications[1];
			var len = textModifications[2];
			
			var offset = len - (end-start);
			// get all anchors of type "wikiTextOffset"			
			var anchors = $('bodyContent').getElementsBySelector('a[type="wikiTextOffset"]')
			for (var i = 0; i < anchors.size(); ++i) {
				var val = anchors[i].getAttribute('name')*1;
				if (val > start) {
					anchors[i].setAttribute('name', val+offset);
				}
			}
			
			// If an annotation has been modified, its highlighting should reflect 
			// the change i.e. the class of the surrounding span has to be changed.
			var anchor = $('bodyContent').getElementsBySelector('a[name="'+start+'"]');
			if (anchor.size() == 1) {
				// anchor with wiki text offset found. A span follows the anchor
				var wrapperSpan = anchor[0].next('span');
				if (wrapperSpan) {
					// The wrapper contains a span with the actual highlight
					var span = wrapperSpan.down('span');
					if (span) {
						var highlightClass = span.getAttribute('class');
						if (highlightClass == 'aam_prop_highlight') {
							span.setAttribute('class', 'aam_new_anno_prop_highlight');
						}
					}
				}
			}
			
			smwhgSaveAnnotations.markDirty();
			this.annotationsChanged = true;
		}
	},
	
	/**
	 * @private
	 * 
	 * Deletes an annotation. The <span> that highlights the text and annotation in 
	 * the wiki text are removed.
	 * 
	 * @param int id
	 * 		Each <span> has a unique id that is composed of "anno" and this counter.
	 * 
	 */
	deleteAnnotation: function(id) {
		var annoDescr = this.findAnnotationWithId(id);
		if (!annoDescr) {
			return;
		}
		var anno = annoDescr[0];
		var type = annoDescr[2];
		
		// Remove the annotation from the wiki text
		// => the highlight will be removed in the hook function 
		//    <removeAnnotationHighlight>
		var value = "";
		
		if (anno.getRepresentation().length != 0) {
			value = anno.getRepresentation();
		} else if (anno.getValue) {
			value = anno.getValue();
		}
		anno.remove(value);
		
		if (type && type == 'category') {
			catToolBar.fillList();
		} else {
			relToolBar.fillList();
		}
	},
	
	/**
	 * @private
	 * 
	 * Edits an annotation. The <span> that highlights the text has an <id> that
	 * is used to find the corresponding annotation in the wiki text.
	 * 
	 * @param int id
	 * 		Each <span> has a unique id that is composed of "anno" and this counter.
	 * 
	 */
	editAnnotation: function(id) {
		var annoDescr = this.findAnnotationWithId(id);
		if (!annoDescr) {
			return;
		}
		var anno = annoDescr[0];
		var index = annoDescr[1];
		var type = annoDescr[2];
		
		relToolBar.getselectedItem(index);
	},
	
	/**
	 * The system highlight annotation proposals with a green border. This 
	 * function is called to annotate the proposal with the id <id>.
	 * 
	 * 
	 */
	annotateProposal: function(id) {
		smwhgAnnotationHints.hideHints();
		
		var annoDescr = this.findAnnotationWithId(id);
		if (!annoDescr) {
			return;
		}

		var wrapper = $('anno'+id+'w');
		this.annotationProposal = wrapper;
		
		var anno = annoDescr[0];
		// The selection of the wiki text parser will be replaced by the annotation
		this.wikiTextParser.setSelection(anno.getStart(), anno.getEnd());
		// open property context menu
		if (this.contextMenu) {
			this.contextMenu.remove;
		}
		this.contextMenu = new ContextMenuFramework();
		var annoName = anno.getRepresentation();
		if (!annoName) {
			annoName = anno.getName();
		}
		relToolBar.createContextMenu(this.contextMenu, annoName);

 		var vo = wrapper.viewportOffset();
		this.contextMenu.setPosition(vo[0], vo[1]+20);
		this.contextMenu.showMenu();

	},
	
	/**
	 * @private
	 * 
	 * Tries to find an annotation by an id. The <span> that highlights the text
	 * in the article has an <id> that is used to find the corresponding 
	 * annotation in the wiki text.
	 * 
	 * @param int id
	 * 		Each <span> has a unique id that is composed of "anno" and this counter.
	 * 
	 * @return Array<WtpAnnotation, int, String>[annotation, index, type]
	 * 			annotation: The annotation which is managed by the WikiTextParser
	 * 			index: Index of the annotation in the array of annotations in the
	 * 				   WikiTextParser
	 * 			type: Type of the annotation i.e. 'category' or 'relation'
	 * 		  or <null>, if the annotation could not be found
	 * 
	 */
	findAnnotationWithId: function(id) {
		// The highlighted text is embedded in a span with the given id
		var wrapper = $('anno'+id+'w');
		if (!wrapper) {
			alert("Corresponding annotation not found.");
			return null;
		}
		// There is a wiki text offset anchor before the wrapper span.
		var wtoAnchor = wrapper.previous('a[type="wikiTextOffset"]');
		var annotationStart = wtoAnchor.getAttribute("name")*1;
		var type = wtoAnchor.getAttribute("annoType");
		// Remove the annotation from the wiki text
		var annotations = (type && type == 'category')
							? this.wikiTextParser.getCategories()
							: this.wikiTextParser.getRelations();
		for (var i = 0; i < annotations.length; ++i) {
			var anno = annotations[i];
			if (anno.getStart() == annotationStart) {
				return [anno, i, type];
			}
		}
		// Nothing found among categories or relations
		// => search among links
		var annotations = this.wikiTextParser.getLinks();
		for (var i = 0; i < annotations.length; ++i) {
			var anno = annotations[i];
			if (anno.getStart() == annotationStart) {
				return [anno, i, 'link'];
			}
		}
		return null;		
	},
	
	/**
	 * @private
	 * 
	 * Removes the highlight of an annotation in the rendered article that 
	 * corresponds to the <annotation> of the wiki text parser.
	 * 
	 * @param WtpAnnotation annotation
	 * 		The highlight for this annotation is removed.
	 */
	removeAnnotationHighlight: function(annotation) {
		var start = annotation.getStart();
		
		// find the anchor that marks the start of the annotation
		var wtoAnchor = $('bodyContent').down('a[name="'+start+'"]');
		if (!wtoAnchor) {
			alert("Anchor for annotation not found.")
			return;
		}
		// there must be a wrappper span for the annotation's highlight after the anchor
		var wrapper = wtoAnchor.next("span");
		if (!wrapper) {
			// no wrapper found => wiki text led to empty HTML. This can happen 
			// for category annotations
//			return alert("Corresponding annotation not found.");
			return;
		}
		// There is always the highlighting span within the wrapper span.
		var span = wrapper.down('span');
		
		var htmlContent = "";
		var content = "";
///		var link = span.down();
//		if (link && link.tagName == 'A') {
			// the span contains a link => remove the link as well
//			htmlContent = link.innerHTML
//			content = link.textContent;
//		} else {
			htmlContent = span.innerHTML
//			content = span.textContent;
//		}
		
		// There is a wiki text offset anchor after the wrapper span.
		var nextWtoAnchor = wtoAnchor.next('a[type="wikiTextOffset"]');
		
		// replace the wrapper by the content i.e. create normal text
		wrapper.replace(htmlContent);
		
		// remove the wiki text offset anchor around the annotation
		if (wtoAnchor.getAttribute("name") != "0") {
			// do not remove the very first anchor
			wtoAnchor.remove();
		}
		nextWtoAnchor.remove();
		
	},
	
	/**
	 * Saves the annotations of the current session.
	 * 
	 */
	saveAnnotations: function() {
		this.om.editArticle(wgTitle, this.wikiTextParser.getWikiText(),
							gLanguage.getMessage('AH_SAVE_COMMENT'), false);
		smwhgSaveAnnotations.savingAnnotations();
	},
	
	/**
	 * @private
	 * 
	 * This hook function is called when the ajax call for saving the annotations
	 * returns (see <saveAnnotations>).
	 * 
	 * @param boolean success
	 * 		 <true> if the article was successfully edited
	 * @param boolean created
	 * 		<true> if the article has been created
	 * @param string title
	 * 		Title of the article		
	 */
	annotationsSaved: function(success, created, title) {
					
		smwhgSaveAnnotations.annotationsSaved(success);
		
		if (success === true) {
			this.annotationsChanged = false;
		} else {
			smwhgSaveAnnotations.markDirty();
		}
		
	}
	
};// End of Class

AdvancedAnnotation.create = function() {
	if (wgAction == "annotate") {
		smwhgAdvancedAnnotation = new AdvancedAnnotation();
		new PeriodicalExecuter(function(pe) {
			var content = $('content');
			Event.observe(content, 'mouseup', 
			              smwhgAdvancedAnnotation.onMouseUp.bindAsEventListener(smwhgAdvancedAnnotation));
			Event.observe('globalWrapper', 'keyup', 
			              smwhgAdvancedAnnotation.onKeyUp.bindAsEventListener(smwhgAdvancedAnnotation));
						              
			pe.stop();
		}, 2);
	}
	
};

/**
 * This function is called when the page is closed. If the annotations have been
 * changed, the user is asked, if he wants to save the changes.
 */
AdvancedAnnotation.unload = function() {
	if (wgAction == "annotate" && smwhgAdvancedAnnotation.annotationsChanged === true) {
		var save = confirm(gLanguage.getMessage('AAM_SAVE_ANNOTATIONS'));
		if (save === true) {
			smwhgAdvancedAnnotation.saveAnnotations();
		}
	}
	
};

/**
 * Edits an annotation. The <span> that highlights the text has an <id> that
 * is used to find the corresponding annotation in the wiki text.
 * 
 * @param int id
 * 		Each <span> has a unique id that is composed of "anno" and this counter.
 * 
 */
AdvancedAnnotation.smwhfEditAnno = function(id) {
	smwhgAdvancedAnnotation.editAnnotation(id);
};

/**
 * Deletes an annotation. The <span> that highlights the text and annotation in 
 * the wiki text are removed.
 * 
 * @param int id
 * 		Each <span> has a unique id that is composed of "anno" and this counter.
 * 
 */
AdvancedAnnotation.smwhfDeleteAnno = function(id) {
	var del = confirm(gLanguage.getMessage('AAM_DELETE_ANNOTATIONS'));
	if (del === true) {
		smwhgAdvancedAnnotation.deleteAnnotation(id);
	}
	
};

AdvancedAnnotation.smwhfEditLink = function(id) {
	smwhgAdvancedAnnotation.annotateProposal(id);
};


var smwhgAdvancedAnnotation = null;
Event.observe(window, 'load', AdvancedAnnotation.create);
Event.observe(window, 'unload', AdvancedAnnotation.unload);

