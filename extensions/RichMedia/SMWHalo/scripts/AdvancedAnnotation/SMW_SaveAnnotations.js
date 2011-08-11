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
 * @author Thomas Schweitzer
 *  
 * @class SaveAnnotations
 * This class provides a container for the save hint ("Don't forget to save your
 * work") in semantic toolbar (in the Advanced Annotation Mode).
 * 
 */
var SaveAnnotations = Class.create();

SaveAnnotations.prototype = {

initialize: function() {
	this.toolbarContainer = null;
	this.exitPage = false;
},

showToolbar: function(request){
	this.savehintcontainer.setHeadline(gLanguage.getMessage('SA_SAVE_ANNOTATION_HINTS'));
	this.createContent();
},

createContainer: function(event){
	if (wgAction == "annotate"
	    && stb_control.isToolbarAvailable()){
		this.savehintcontainer = stb_control.createDivContainer(SAVEANNOTATIONSCONTAINER,0);
		this.showToolbar();
	}
},

createContent: function() {
	
	var tb = this.createToolbar("");
	tb.append(tb.createText('sa-save-msg', '', '', true));
	var html = '<table border="0" class= "saveannotations-innertable"><tr><td>';
	html += tb.createButton('ah-savewikitext-btn',
							  gLanguage.getMessage('SA_SAVE_ANNOTATIONS'), 
							  'smwhgAdvancedAnnotation.saveAnnotations(false)', 
							  '' , true);
	html += "</td><td>";							  
	html += tb.createButton('ah-savewikitext-and-exit-btn',
							  gLanguage.getMessage('SA_SAVE_ANNOTATIONS_AND_EXIT'), 
							  'smwhgAdvancedAnnotation.saveAnnotations(true)', 
							  '' , true);						   
	html += "</td></tr></table>";							  

	tb.append(html);
	
	tb.finishCreation();
	
	this.savehintcontainer.contentChanged();
	$('ah-savewikitext-btn').disable();
	$('ah-savewikitext-and-exit-btn').disable();
},

savingAnnotations: function(exit) {
	
	var msg = gLanguage.getMessage('SA_SAVING_ANNOTATIONS');
	
	var tb = this.toolbarContainer;
	
	var sm = tb.createText('sa-save-msg', msg, '', true);
	tb.replace('sa-save-msg', sm);
	$('saveannotations-content-table-sa-save-msg').show();
	$('ah-savewikitext-btn').disable();
	$('ah-savewikitext-and-exit-btn').disable();
	this.exitPage = exit;
	
},

annotationsSaved: function(success) {
	
	var msg = (success) 
				? gLanguage.getMessage('SA_ANNOTATIONS_SAVED')
				: gLanguage.getMessage('SA_SAVING_ANNOTATIONS_FAILED');
	
	var tb = this.toolbarContainer;
	
	var sm = tb.createText('sa-save-msg', msg, '', true);
	tb.replace('sa-save-msg', sm);
	$('saveannotations-content-table-sa-save-msg').show();
	if (success) {
		$('ah-savewikitext-btn').disable();
		$('ah-savewikitext-and-exit-btn').disable();
		if (this.exitPage) {
			location.href=wgServer+wgScript+"/"+wgPageName;
		}
	}
},

markDirty: function() {
	var tb = this.toolbarContainer;
	
	$('saveannotations-content-table-sa-save-msg').hide();
	$('ah-savewikitext-btn').enable();
	$('ah-savewikitext-and-exit-btn').enable();
	
},

/**
 * Creates a new toolbar for the save annotations container.
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
	
	this.toolbarContainer = new ContainerToolBar('saveannotations-content',900,this.savehintcontainer);
	var tb = this.toolbarContainer;
	tb.createContainerBody(attributes);
	return tb;
}

};// End of Class

var smwhgSaveAnnotations = new SaveAnnotations();
if (typeof FCKeditor == 'undefined')
    Event.observe(window, 'load', smwhgSaveAnnotations.createContainer.bindAsEventListener(smwhgSaveAnnotations));


