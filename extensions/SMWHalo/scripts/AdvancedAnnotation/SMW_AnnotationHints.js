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

/**
 * @class AnnotationHints
 * This class provides a container for hints and error messages in the semantic
 * toolbar (in the Advanced Annotation Mode).
 * 
 */
var AnnotationHints = Class.create();

AnnotationHints.prototype = {

initialize: function() {
    //Reference
    this.genTB = new GenericToolBar();
	this.toolbarContainer = null;
	this.showList = true;

},

showToolbar: function(request){
	this.annohintcontainer.setHeadline(gLanguage.getMessage('ANNOTATION_HINTS'));
	this.createContent();
},

callme: function(event){
	if ((wgAction == "edit" || wgAction == "annotate")
	    && stb_control.isToolbarAvailable()){
		this.annohintcontainer = stb_control.createDivContainer(ANNOTATIONHINTCONTAINER,0);
		this.showToolbar();
	}
},

createContent: function() {
	
	var tb = this.createToolbar("");	
	tb.append(tb.createText('ah-error-msg', 
	                        '(i)Infos for the annotation mode.',
	                        '' , true));
	tb.append(tb.createText('ah-wikitext-msg', '', '' , true));

	tb.append(tb.createButton('ah-savewikitext-btn',
							  gLanguage.getMessage('AH_SAVE_ANNOTATIONS'), 
							  'smwhgAdvancedAnnotation.saveAnnotations()', 
							  '' , true));
	
	tb.finishCreation();
	
	this.annohintcontainer.contentChanged();
	$('ah-savewikitext-btn').disable();
},

showMessageAndWikiText: function(message, wikiText) {
	var msg = this.toolbarContainer.createText('ah-error-msg',message, '', true);
	var wt = this.toolbarContainer.createText('ah-wikitext-msg',wikiText, '', true);
	this.toolbarContainer.replace('ah-error-msg', msg);
	this.toolbarContainer.replace('ah-wikitext-msg', wt);
},

/**
 * Creates a new toolbar for the annotation hint container.
 * Further elements can be added to the toolbar. Call <finishCreation> after the
 * last element has been added.
 * 
 * @param string attributes
 * 		Attributes for the new container
 * @return 
 * 		A new toolbar container
 */
createToolbar: function(attributes) {
	if (this.toolbarContainer) {
		this.toolbarContainer.release();
	}
	
	this.toolbarContainer = new ContainerToolBar('annotationhint-content',900,this.annohintcontainer);
	var tb = this.toolbarContainer;
	tb.createContainerBody(attributes);
	return tb;
}

};// End of Class

var smwhgAnnotationHints = new AnnotationHints();
Event.observe(window, 'load', smwhgAnnotationHints.callme.bindAsEventListener(smwhgAnnotationHints));


