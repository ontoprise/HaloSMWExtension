/*  Copyright 2008, ontoprise GmbH
*  This file is part of CollapsingForms patch for SemanticForms.
*
*   CollapsingForms is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   CollapsingForms is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
* 
*   Contains collapsing functions for semantic forms
*/
var CollapsingForm = Class.create();
CollapsingForm.prototype = {
	initialize: function() {
		this.closedContainers = GeneralCookieTools.getCookieObject("CollapsingForm");
		if (this.closedContainers == null) this.closedContainers = new Object();
	},

	switchVisibilityWithImg: function(id) {
		if ($(id).visible()) {
			this.closedContainers[id] = false;
			closedimg = "<img id=\"" + id + "_img\" onmouseout=\"(src='"+ wgScriptPath + "/extensions/SemanticForms/skins" + "/plus.gif')\" onmouseover=\"(src='"+ wgScriptPath + "/extensions/SemanticForms/skins" + "/plus-act.gif')\" src=\""+ wgScriptPath + "/extensions/SemanticForms/skins" + "/plus.gif\"/>";
			$(id+"_img").replace(closedimg);
		} else {
			this.closedContainers[id] = true;
			openedimg = "<img id=\"" + id + "_img\" onmouseout=\"(src='" + wgScriptPath + "/extensions/SemanticForms/skins" + "/minus.gif')\" onmouseover=\"(src='"+ wgScriptPath + "/extensions/SemanticForms/skins" + "/minus-act.gif')\" src=\""+ wgScriptPath + "/extensions/SemanticForms/skins" + "/minus.gif\"/>";
			$(id+"_img").replace(openedimg)
		}
		GeneralCookieTools.setCookieObject("CollapsingForm", this.closedContainers);
		this.switchVisibility(id);
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

//-- cookie functions for collapsing forms -- //
// taken out of the generalTools.js from SMWHalo 

GeneralCookieTools = new Object();

/**
 * Returns the cookie value for the given key
 */
GeneralCookieTools.getCookie = function (name) {
	var value=null;
	if(document.cookie != "") {
		var kk=document.cookie.indexOf(name+"=");
		if(kk >= 0) {
			kk=kk+name.length+1;
			var ll=document.cookie.indexOf(";", kk);
			if(ll < 0)ll=document.cookie.length;
			value=document.cookie.substring(kk, ll);
			value=unescape(value); 
		}
	}
	return value;
}

GeneralCookieTools.setCookieObject = function(key, object) {
	var json = Object.toJSON(object);
	document.cookie = key+"="+json; 
}

GeneralCookieTools.getCookieObject = function(key) {
	var json = GeneralCookieTools.getCookie(key);
	var res;
	try {
		res = json.evalJSON(false);
	}
	catch (e) {
		return null;
	}
	return res;
}

var smwCollapsingForm = new CollapsingForm();