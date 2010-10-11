jQuery(document).ready(function() {
	//add functionality to (type=submit)
	jQuery("#mw-upload-form").submit(function() {
		richMediaPage.doUpload()
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
	jQuery(objImg).attr('src', '/trunk/extensions/SemanticForms/skins/plus.gif');
	jQuery(objImg).hover(function() {
		jQuery(objImg).attr('src','/trunk/extensions/SemanticForms/skins/plus-act.gif');
	}, function() {
		jQuery(objImg).attr('src','/trunk/extensions/SemanticForms/skins/plus.gif');
	});

	jQuery(objImg).attr('id', htmlid + '_img');
	jQuery(objLegend).bind('click', function() {
		smwCollapsingForm.switchVisibilityWithImg(htmlid);
	});
	jQuery(objLegend).html('&nbsp;' + jQuery(objLegend).html());
	jQuery(objLegend).prepend(objImg);
	return true;
}