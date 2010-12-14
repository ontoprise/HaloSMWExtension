/* 
 * These functions take care for opening "rmlinks" and "rmAlinks" in the fancy box.
 */
jQuery(document).ready(function() {
	//buttons
	jQuery("input.rmlink").live('click', function(){
		jQuery.fancybox({
			'href': wgRMUploadUrl,
			'width'		: '75%',
			'height'	: '75%',
			'autoScale'	: false,
			'transitionIn'	: 'none',
			'transitionOut'	: 'none',
			'type'		: 'iframe',
			'overlayColor'  : '#222',
			'overlayOpacity' : '0.8',
			'hideOnContentClick' : true
		});
	});

	// links
	var test = jQuery("a.rmAlink");
	jQuery("a.rmAlink").live('click', function(){
		jQuery.fancybox({
			'href' : jQuery(this).attr('href'),
			'width'		: '75%',
			'height'	: '75%',
			'autoScale'	: false,
			'transitionIn'	: 'none',
			'transitionOut'	: 'none',
			'type'		: 'iframe',
			'overlayColor'  : '#222',
			'overlayOpacity' : '0.8',
			'hideOnContentClick' : true
		});
		return false;
	});
});