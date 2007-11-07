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
var AdvancedAnnotation = Class.create();

AdvancedAnnotation.prototype = {

	initialize: function() {
		this.anchorNode = null;
		this.annotatedNode = null;
		this.annoOffset = null;
		this.annoSelection = null;
		this.selectedText = '';
		this.wikiTextParser = null;
		this.loadWikiText();
		
		this.wtoAnchors = 0;
		
		
	},
	
	onMouseUp: function() {
		var annoSelection = this.getSel();
		if (annoSelection != '') {
			this.selectedText = annoSelection.toString();
			this.anchorNode = annoSelection.anchorNode;
			this.annotatedNode = this.anchorNode.parentNode;
			this.annoOffset = annoSelection.anchorOffset;
			
			var parentNode = $(this.annotatedNode);
			var anchor = $(parentNode).previous('a');
			while (!anchor || anchor.getAttribute('type') != 'wikiTextOffset') {
				parentNode = parentNode.up();
				if (parentNode) {
					anchor = parentNode.previous('a');
				} else {
					break;
				}
			}
			if (anchor) {
				// find this and the next anchor in the array of stored anchors
				var anchorIdx = -1;
				for (var i = 0; i < this.wtoAnchors.length; i++) {
					if (this.wtoAnchors[i] == anchor) {
						anchorIdx = i;
						break;
					}
				}
				if (anchorIdx >= 0) {
					var start = anchor.getAttribute('name');
					var end = (anchorIdx+1 < this.wtoAnchors.length)
								? this.wtoAnchors[anchorIdx+1].getAttribute('name')
								: -1;
					var res = this.wikiTextParser.findText(text, start, end);
					if (res != true) {
						alert(res);
					}
				}
			}
		}
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
/*
 				var allDesc = content.descendants();
				smwhgAdvancedAnnotation.wtoAnchors = new Array();
				for (var i = 0; i < allDesc.length; ++i) {
					var elem = allDesc[i];
					if (elem.tagName == 'A' && $(elem).getAttribute('type') == 'wikiTextOffset') {
						smwhgAdvancedAnnotation.wtoAnchors.push($(elem));
					}
				}		
*/		
		}, 2);
	}
	
};

var smwhgAdvancedAnnotation = null;
Event.observe(window, 'load', AdvancedAnnotation.create());
