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

var ACL = Class.create();
ACL.prototype = {
	initialize: function() {
		 // do nothing
	},
	
	up: function() {
		var selectedRow = this.getSelectedRow();
		if (selectedRow != null) {
			var row = selectedRow.parentNode.parentNode;
			var tablebody = row.parentNode;
			var predecessor = row.previousSibling;
			if (predecessor.previousSibling == null) return;
			tablebody.removeChild(row);
			tablebody.insertBefore(row, predecessor);
			row.firstChild.firstChild.checked = true; // for IE
	}
	},
	
	down: function() {
		var selectedRow = this.getSelectedRow();
		if (selectedRow != null) {
			var row = selectedRow.parentNode.parentNode;
			var tablebody = row.parentNode;
			var successor = row.nextSibling;
			if (successor == null) return;
			tablebody.removeChild(row);
			this.insertAfter(tablebody, row, successor);
			row.firstChild.firstChild.checked = true; // for IE
		}
	},
	
	update: function() {
		var acl_rules = this.getRules();
		var whitelist = $('whitelist').value;
		var superusers = $('superusers').value;
		if (acl_rules.length == 0) {
			alert("empty rules set should bot be updated.");
			return;
		}
		sajax_do_call('smwf_al_updateACLs', [acl_rules.toJSON(), whitelist, superusers], this.updateDone.bind(this));
	},
	
	updateDone: function(request) {
		if (request.status != 200) {
			alert("Something went wrong, try again.");
			return;
		}
		alert("ACLs have been updated.");
	},
	
	removeRule: function() {
		var selectedRow = this.getSelectedRow();
		if (selectedRow != null) {
			var row = selectedRow.parentNode.parentNode;
			var tablebody = row.parentNode;
			tablebody.removeChild(row);
		}
	},
	
	addRule: function() {
		var rule = this.getNewRule();
		var tbody = $('permissions').firstChild.firstChild;
		var newrow = tbody.appendChild(document.createElement("tr"));
		$(newrow).replace('<tr>' +
							'<td><input type=\"radio\" name=\"select\" value=\"\"/></td>' +
							'<td>'+rule['group']+'</td>' +
							'<td>'+rule['namespaces']+'</td>' +
							'<td>'+rule['action']+'</td>' +
							'<td>'+rule['operation']+'</td></tr>');
	},
	
	/**
	 * @private
	 */
	getSelectedRow: function() {
		var selectedRow = null;
		var tablerows = $('permissions').getInputs('radio', 'select');
		for(var i = 0; i < tablerows.length; i++) {
			if (tablerows[i].checked) {
				selectedRow = tablerows[i];
				break;
			}
		}
		return selectedRow;
	},
	
	/** 
	 * @private 
	 * 
	 */
	insertAfter: function(tbody, node, next) {
		next = next.nextSibling;
		if (next != null) {
			tbody.insertBefore(node, next);
		} else {
			tbody.appendChild(node);
		}
	},
	
	/** 
	 * @private
	 */
	getRules: function() {
		var rules = new Array();
		var header = $('permissions').firstChild.firstChild.firstChild;
		var row = header.nextSibling;
		while(row != null) {
			var group = row.firstChild.nextSibling.innerHTML.split(",");
			var namespaces = row.firstChild.nextSibling.nextSibling.innerHTML.split(",");
			var action = row.firstChild.nextSibling.nextSibling.nextSibling.innerHTML.split(",");
			var operation = row.firstChild.nextSibling.nextSibling.nextSibling.nextSibling.innerHTML;
			rules.push(new Rule(group, namespaces, action, operation));
			row = row.nextSibling;
		}
		
		return rules;
	},
	
	getNewRule: function() {
		
		var selectedIndex = $('group').selectedIndex;
		var group = $('group').options[selectedIndex].innerHTML;
		
		var selectedIndex = $('namespaces').selectedIndex;
		var namespaces = $('namespaces').options[selectedIndex].innerHTML;
		
		var selectedIndex = $('action').selectedIndex;
		var action = $('action').options[selectedIndex].innerHTML;
		
		var selectedIndex = $('operation').selectedIndex;
		var operation = $('operation').options[selectedIndex].innerHTML;
		
		return new Rule(group, namespaces, action, operation);
	}
}

var Rule = Class.create();
Rule.prototype = {
	initialize: function(group, namespaces, action, operation) {
		 this['group'] = group;
		 this['namespaces'] = namespaces;
		 this['action'] = action;
		 this['operation'] = operation;
	}
}

var acl = new ACL();

	 