/*  Copyright 2008, ontoprise GmbH
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
*   Contains general GUI functions
*/
var GeneralGUI = Class.create();
GeneralGUI.prototype = {
    initialize: function() {
        this.closedContainers = GeneralBrowserTools.getCookieObject("smwNavigationContainers");
        if (this.closedContainers == null) this.closedContainers = new Object();
    },
    
    switchVisibilityWithState: function(id) {
    	if ($(id).visible()) {
    		this.closedContainers[id] = true;
    	} else {
    		this.closedContainers[id] = false;
    	}
    	GeneralBrowserTools.setCookieObject("smwNavigationContainers", this.closedContainers);
    	this.switchVisibility(id);
    },
    
    update: function() {
    	for (var id in this.closedContainers) {
    		if (this.closedContainers[id] == true) {
    			this.switchVisibility(id);
    		}
    	}
    },
    
    switchVisibility: function(container) {
        var visible = $(container).visible();
        if ( visible ) {    
            $(container).hide();
        } else {
            $(container).show();
        }
    }
   
}
var smwhg_generalGUI = new GeneralGUI();
Event.observe(window, 'load', smwhg_generalGUI.update.bind(smwhg_generalGUI));