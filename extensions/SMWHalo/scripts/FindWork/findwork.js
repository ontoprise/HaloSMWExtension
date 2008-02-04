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
			var subject;
			var predicate;
			var objectOrValue;
			
			if (OB_bd.isGecko) {
				subject = annotation.firstChild.textContent;
				predicate = annotation.firstChild.nextSibling.textContent;
				objectOrValue = annotation.firstChild.nextSibling.nextSibling.textContent;
			} else if (OB_bd.isIE) {
				subject = annotation.firstChild.innerText;
				predicate = annotation.firstChild.nextSibling.innerText;
				objectOrValue = annotation.firstChild.nextSibling.nextSibling.innerText;
			}
			
			// replace whitespaces by underscores
			subject = subject.replace(/\s/g, "_");
			predicate = predicate.replace(/\s/g, "_");
			objectOrValue = objectOrValue.replace(/\s/g, "_");
			
			var buttons = $('ratingform').getInputs('radio', 'rating'+i);
			var rating = this.getValueOfChecked(buttons);
			if (rating != 0) result.push([subject, predicate, objectOrValue, rating]);
			annotation = $('annotation' + (++i));
		}
		sajax_do_call('smwf_fw_SendAnnotationRatings', [result.toJSON()], this.printThankYou.bind(this));
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
		alert(gLanguage.getMessage('FW_SEND_ANNOTATIONS') + (wgUserName ? wgUserName : gLanguage.getMessage('FW_MY_FRIEND')));
		// disable button to prevent repeated rating
		$('sendbutton').setAttribute("disabled", "disabled");
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
Event.observe(window, 'load', function() {
	// unset correct/wrong button to dont know
	$$('input.yes').each(function(s) {
		 s.checked = false;
	});
	$$('input.no').each(function(s) { 
		 s.checked = false;
	});
	$$('input.dontknow').each(function(s) {
		  s.checked = true;
	});
});