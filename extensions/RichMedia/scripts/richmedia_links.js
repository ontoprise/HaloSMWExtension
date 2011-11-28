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
			'hideOnContentClick' : true,
			'scrolling' : 'auto'
		});
	});

	// links
	jQuery("a.rmAlink").live('click', function(){
		jQuery.fancybox({
			'href' : jQuery(this).attr('href'),
			'width' : '75%',
			'height' : '75%',
			'autoScale' : true,
			'autoDimensions' : true,
			'transitionIn' : 'none',
			'transitionOut' : 'none',
			'type' : 'iframe',
			'overlayColor' : '#222',
			'overlayOpacity' : '0.8',
			'hideOnContentClick' : true,
			'scrolling' : 'auto'
		});
		return false;
	});
});
