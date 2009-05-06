/*  Copyright 2007, ontoprise GmbH
*   Author: Kai Kühn
*   This file is part of the halo-Extension.
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
*/

var FindWork = Class.create();
FindWork.prototype = {
	initialize: function() {
		 // do nothing
	},
	
		
	toggle: function(id) {
		var div = $(id);
		if (div.visible()) div.hide(); else div.show();
	},
	
	toggleAll: function() {
		this.showAll = !this.showAll;
		var showAll = this.showAll;
		var divs = $$('.findWorkDetails');
		divs.each(function(d) { if (showAll) d.show(); else d.hide(); });
		$('showall').innerHTML = showAll ? gLanguage.getMessage('GARDENING_LOG_COLLAPSE_ALL') : gLanguage.getMessage('GARDENING_LOG_EXPAND_ALL'); 
	}
}

var findwork = new FindWork();
