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

steal.plugins(	
	'jquery/controller',			// a widget factory
	'jquery/controller/subscribe',	// subscribe to OpenAjax.hub
	'jquery/view/ejs',				// client side templates
	'jquery/controller/view',		// lookup views with the controller's name
	'jquery/model',					// Ajax wrappers
	'jquery/model/backup',			// Backup of model instances
//	'jquery/dom/fixture',			// simulated Ajax requests
	'jquery/dom/form_params',		// form data helper
	'jquery/throbber')
	
	.css('sngui')	// loads styles

	.resources()					// 3rd party script's (like jQueryUI), in resources folder

	// loads files in models folder
	.models('SNLanguage',
	        'SNUserData',
			'SNNotification')						 

	// loads files in controllers folder
	.controllers('SNMain', 
	             'SNQueryTextArea',
				 'SNPageState',
				 'SNNotificationList')					

	// adds views to be added to build
	.views('//sngui/views/snmain/SNMain.ejs',
	       '//sngui/views/snmain/SNNotification.ejs',
	       '//sngui/views/snmain/SNQueryTextArea.ejs',
		   '//sngui/views/snmain/SNWarning.ejs');						
