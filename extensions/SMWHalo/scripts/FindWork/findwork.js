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
	
	sendRatings: function() {
		var i = 0;
		var result = [];
		var annotation = $('annotation'+i);
		while (annotation != null) {
			var subject = annotation.firstChild.textContent.replace(/\s/g, "_");
			var predicate = annotation.firstChild.nextSibling.textContent.replace(/\s/g, "_");
			var objectOrValue = annotation.firstChild.nextSibling.nextSibling.textContent.replace(/\s/g, "_");
			var buttons = $('ratingform').getInputs('radio', 'rating'+i);
			var rating = this.getValueOfChecked(buttons);
			if (rating != 0) result.push([subject, predicate, objectOrValue, rating]);
			annotation = $('annotation' + (++i));
		}
		sajax_do_call('smwfSendAnnotationRatings', [result.toJSON()], this.printThankYou.bind(this));
	},
	
	getValueOfChecked: function(buttons) {
		for(var i = 0; i < buttons.length; i++) {
			if (buttons[i].checked) return buttons[i].defaultValue;
		}
	},
	
	printThankYou: function(request) {
		if (request.status != 200) {
			alert('Something went wrong! Please try again!');
			return;
		}
		alert('Thank you for rating annotations, ' + (wgUserName ? wgUserName : "my friend") + "!");
		// disable button to prevent repeatedly rating
		$('sendbutton').setAttribute("disabled", "disabled");
	}
}

var findwork = new FindWork();