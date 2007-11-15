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
		this.anchorNode = null;
		this.annotatedNode = null;
		this.annoOffset = null;
		this.annoSelection = null;
		this.selectedText = '';
		
		// The wiki text parser manages the wiki text and adds annotations 
		this.wikiTextParser = null;
		
		// Load the wiki text for the current page and store it in the parser.
		this.loadWikiText();
		
		// Array of wiki text offset anchors. 
		this.wtoAnchors = 0;
		
	},
	
	/**
	 * This method is called, when the mouse button is released. The current
	 * selection is retrieved and used as annotation.
	 */
	onMouseUp: function() {
		var annoSelection = this.getSel();
		if (annoSelection != '') {
			// store details of the selection
			this.selectedText = annoSelection.toString();
			//trim selection
			this.selectedText = this.selectedText.replace(/^\s*(.*)\s*$/,'$1');
			this.anchorNode = annoSelection.anchorNode;
//			this.annotatedNode = this.anchorNode.parentNode;
			this.annotatedNode = this.anchorNode;
			this.annoOffset = annoSelection.anchorOffset;
			
			this.performAnnotation();
		}
	},
	
	/**
	 * Tries to find the current selection in the wiki text. If successful, the
	 * corresponding wiki text is augmented with an annotation.
	 */
	performAnnotation: function() {
		var anchor = null;
		var firstAnchor = null;
		var secondAnchor = null;
		
		firstAnchor = this.searchBackwards(this.annotatedNode, this.searchWtoAnchor.bind(this));
		secondAnchor = this.searchForward(this.annotatedNode, this.searchWtoAnchor.bind(this));
		// Check if the marked text is part of a template
		var template = this.searchBackwards(this.annotatedNode, this.searchTemplate.bind(this));
		if (template && $(template).getAttribute('type') == "template"){
			msg = gLanguage.getMessage('WTP_NOT_IN_TEMPLATE');
			msg = msg.replace(/\$1/g, this.selectedText);
			
			alert(msg);
			alert("Name of template: "+ $(template).getAttribute('tmplname'));
			return;
		}
		if (firstAnchor) {
			var start = firstAnchor.getAttribute('name')*1;
			var end = (secondAnchor != null)
						? secondAnchor.getAttribute('name')*1
						: -1;
		alert("Searching between:"+start+","+end+":\n"+this.wikiTextParser.text.substring(start,end));
			var res = this.wikiTextParser.findText(this.selectedText, start, end);
			if (res != true) {
				alert(res+"\n"+this.wikiTextParser.text.substring(start,end));
			} else {

				var result = this.wikiTextParser.addAnnotation('[[annotation::'+this.selectedText+']]');
				alert("Added at: "+result.toString());
				if (result) {
					// update anchors
					var offset = result[2] - (result[1]-result[0]);
					this.newWikiTextOffset = offset;
					this.searchForward(firstAnchor, this.updateAnchors.bind(this));

					// mark selection as annotated
					var node = this.annotatedNode.parentNode;
					var origText = node.innerHTML;
					var newText = origText.replace(this.selectedText, '<span class="aam_new_anno_prop_highlight">'+this.selectedText+"</span>");
					node.innerHTML = newText;
					
					catToolBar.fillList(true);
					relToolBar.fillList(true);
				}
				
			}

		} else {
			alert("No corresponding wiki text found.");
		}
	
	},
	
	searchTemplate: function(node) {
		if (node.tagName == 'A' 
		    && (node.type == "template" || node.type == "templateend")) {
			return node;
		} 
	},
	
	searchWtoAnchor: function(node) {
		if (node.tagName == 'A' && node.type == "wikiTextOffset") {
			return node;
		} 
	},
	
	updateAnchors: function(node) {
		if (node.tagName == 'A' && node.type == "wikiTextOffset") {
			var val = node.getAttribute('name')*1;
			node.setAttribute('name', val+this.newWikiTextOffset);
		} 
	},
	
	/**
	 * Searches recursively backwards from the given node <startNode> to the top
	 * of the document. The document order is traversed in reverse order, visiting
	 * all nodes.
	 */
	searchBackwards: function(startNode, cbFnc, diveDeeper) {
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
				var result = this.searchBackwards(node.lastChild, cbFnc, true);
				if (result) {
					return result;
				}
			}
			var result = cbFnc(node);
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
				var result = this.searchBackwards(node, cbFnc);
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
	 * 		returnValue function(DomNode node)
	 * @param boolean diveDeeper
	 * 		Only uses internally. Don't specify this value.
	 */
	searchForward: function(startNode, cbFnc, diveDeeper) {
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
				var result = this.searchForward(node.firstChild, cbFnc, true);
				if (result) {
					return result;
				}
			}
			var result = cbFnc(node);
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
				var result = this.searchForward(node, cbFnc);
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

};// End of Class

AdvancedAnnotation.create = function() {
	if (wgAction == "annotate") {
		smwhgAdvancedAnnotation = new AdvancedAnnotation();
			new PeriodicalExecuter(function(pe) {
				var content = $('content');
				Event.observe(content, 'mouseup', 
				              smwhgAdvancedAnnotation.onMouseUp.bindAsEventListener(smwhgAdvancedAnnotation));
				pe.stop();
				
				// retrieve all anchors of type "wikiTextOffset"
				smwhgAdvancedAnnotation.wtoAnchors = $$('#content a[type="wikiTextOffset"]');
		}, 2);
	}
	
};

var smwhgAdvancedAnnotation = null;
Event.observe(window, 'load', AdvancedAnnotation.create);
