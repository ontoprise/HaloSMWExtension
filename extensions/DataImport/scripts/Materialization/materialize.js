/*   The Data Import-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Data Import-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http:// www.gnu.org/licenses/>.
 */

/**
 * This file provides a small utility method for the
 *  materialize parser function.
 * 
 * @author Ingo Steinbauer
 * 
 */

var Materialize = Class.create();
Materialize.prototype = {
	
	initialize: function() {
	},
	
	/*
	 * replaces "#materialize" by "subst#materialize" in the editor
	 * also renders ##mcoll## and ##mcolr## as { respectively }
	 */
	callme: function() {
		if (wgAction == "edit"){
			var text = $("wpTextbox1").value;
			text = text.replace(/{{#materialize:/g, "{{subst:#materialize:");
			text = text.replace(/##mcoll##/g, "{");
			text = text.replace(/##mcolr##/g, "}");
			text = text.replace(/##pipe##/g, "|");
			$("wpTextbox1").value = text;
		}
	}
}

		
materialize = new Materialize();
Event.observe(window, 'load', materialize.callme.bindAsEventListener(materialize));