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
*/

/**
 * @file
 * @ingroup SMWHaloAAM
 * 
 * @author Thomas Schweitzer
 * 
 * @class AnnotationHints
 * This class provides a container for hints and error messages in the semantic
 * toolbar (in the Advanced Annotation Mode).
 * 
 */
var AnnotationHints = Class.create();

AnnotationHints.prototype = {

initialize: function() {

},

showMessageAndWikiText: function(message, wikiText, x, y) {
	this.contextMenu = new ContextMenuFramework();
	
	var tb = new ContainerToolBar('annotationhints-content', 1000, 
	                              this.contextMenu);
	tb.createContainerBody('', 'ANNOTATIONHINT', 
	                       gLanguage.getMessage('ANNOTATION_ERRORS'));

	var m = message.stripScripts();
	if (m != message) {
		m = message.replace(/<\/?b>/g,'');
		m = m.escapeHTML();
	} 
	var w = wikiText.stripScripts();
	if (w != wikiText) {
		w = wikiText.replace(/<\/?b>/g,'');
		w = w.escapeHTML();
	} 
	tb.append(tb.createText('ah-error-msg', m, '', true));
	tb.append(tb.createText('ah-wikitext-msg', w, '' , true));

	tb.finishCreation();
	
	this.contextMenu.setPosition(x,y);
	this.contextMenu.showMenu();
	
	document.onkeyup = function(e) {
		if (!e) {
			return;
		}
		var key = e.which || e.keyCode;
		if (key == Event.KEY_ESC) {
			smwhgAnnotationHints.hideHints();
		}
	}
	
},

hideHints: function() {
	if (this.contextMenu) {
		this.contextMenu.remove();
	}
}

};// End of Class

var smwhgAnnotationHints = new AnnotationHints();


