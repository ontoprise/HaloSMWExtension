/**
 * @file
 * @ingroup SemanticNotifications
 */

/*  Copyright 2011, ontoprise GmbH
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
steal(function($){

$.Controller.extend('SNGui.Controllers.MainController',
	/* @Static */
	{
		onDocument: true
	},
	/* @Prototype */
	{
	   /**
		* Load the HTML of the whole page. It is configured with the current
		* user language.
		*/
		"{window} load": function() {
			// Load the user data first and then create the rest of the page
			var userData = SNGui.Model.UserData.findAll();
			var language = SNGui.Model.Language.findAll();
			$.when(userData, language)
			 .done(function(userDataResponse, languageResponse) {
				$('#sn-main-div')
					.html('//sngui/views/snmain/SNMain.ejs', languageResponse[0])
					// Attach SNGui.Controllers.PageState to the main div
					.sn_gui_page_state();
			});
		}
	}
);

});

