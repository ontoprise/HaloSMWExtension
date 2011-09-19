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