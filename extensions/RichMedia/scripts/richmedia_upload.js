/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

jQuery(document).ready(function() {
	// remove the upload button
	jQuery("input.mw-htmlform-submit").remove();

	//add functionality to (type=submit)
	jQuery("#mw-upload-form").submit(function() {
		richMediaPage.doUpload("#mw-upload-form");
	});
	// add collapsing functionality for legends
	// upload form 
	rmAddCollapsingLegend("mw-htmlform-source");
	// description
	rmAddCollapsingLegend("mw-htmlform-description");
	// options
	rmAddCollapsingLegend("mw-htmlform-options");
});

function rmAddCollapsingLegend(htmlid){
	var obj = jQuery('table#' + htmlid);
	var objLegend = obj.parent().find('legend');
	var objImg = document.createElement('img');
	jQuery(objImg).attr('src', wgScriptPath + '/extensions/SemanticForms/skins/minus.gif');
	jQuery(objImg).hover(function() {
		jQuery(objImg).attr('src', wgServer + wgScriptPath + '/extensions/SemanticForms/skins/minus-act.gif');
	}, function() {
		jQuery(objImg).attr('src', wgServer + wgScriptPath + '/extensions/SemanticForms/skins/minus.gif');
	});

	jQuery(objImg).attr('id', htmlid + '_img');
	jQuery(objLegend).bind('click', function() {
		smwCollapsingForm.switchVisibilityWithImg(htmlid);
	});
	jQuery(objLegend).html('&nbsp;' + jQuery(objLegend).html());
	jQuery(objLegend).prepend(objImg);
	return true;
}
