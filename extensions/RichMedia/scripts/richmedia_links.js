/* mooo */
jQuery(document).ready(function() {
	//buttons
	jQuery("input.rmlink").click(function() {
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
	jQuery("a.rmAlink").fancybox({
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