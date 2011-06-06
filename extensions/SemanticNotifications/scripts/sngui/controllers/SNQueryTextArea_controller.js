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

$.Controller.extend('SNGui.Controllers.QueryTextArea',
	/* @Static */
	{
		
	},
	/* @Prototype */
	{
		init: function (el, options) {
			var userData = SNGui.Model.UserData.getInstance();
			var lang = SNGui.Model.Language.getInstance()
			var warning = null;
			if (!userData.isLoggedIn) {
				warning = lang['sn_not_logged_in'];
			} else if (!userData.isEmailConfirmed) {
				warning = lang['sn_no_email'];
			}
			if (warning) {
				$(el).html('//sngui/views/snmain/SNWarning.ejs', { msg: warning });
			} else {
				$(el).html('//sngui/views/snmain/SNQueryTextArea.ejs', {});
			}
		}
	}
);

});

