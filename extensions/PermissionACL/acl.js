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
* 
*  @author: kai
*/

var ACL = Class.create();
ACL.prototype = {
	initialize: function() {
		 // do nothing
	},
	
	/**
	 * Exchanges the selected row with the row above it
	 */
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
	
	/**
	 * Exchanges the selected row with the row below it
	 */
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
	
	/**
	 * Updates ACLs.php file
	 */
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
	
	/**
	 * Removes the selected rule
	 */
	removeRule: function() {
		var selectedRow = this.getSelectedRow();
		if (selectedRow != null) {
			var row = selectedRow.parentNode.parentNode;
			var tablebody = row.parentNode;
			tablebody.removeChild(row);
		}
	},
	
	/**
	 * Adds a new rule
	 */
	addRule: function() {
		var rule = this.getNewRule();
		var tbody = $('permissions').firstChild.firstChild;
		var newrow = tbody.appendChild(document.createElement("tr"));
		$(newrow).replace('<tr>' +
							'<td><input type=\"radio\" name=\"select\" value=\"\"/></td>' +
							'<td>'+(rule['group'] == null ? "-" : rule['group']) +'</td>' +
							'<td>'+(rule['user'] == null ? "-" : rule['user'])+'</td>' +
							'<td>'+(rule['namespaces'] == null ? "-" : rule['namespaces'])+'</td>' +
							'<td>'+(rule['category'] == null ? "-" : rule['category'])+'</td>' +
							'<td>'+(rule['page'] == null ? "-" : rule['page'])+'</td>' +
							'<td value="'+rule['action']+'">'+gLanguage.getMessage('smw_acl_'+rule['action'])+'</td>' +
							'<td value="'+rule['operation']+'">'+gLanguage.getMessage('smw_acl_'+rule['operation'])+'</td></tr>');
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
	 * Builds Rule objects from HTML Table
	 * @private
	 */
	getRules: function() {
		var rules = new Array();
		var header = $('permissions').firstChild.firstChild.firstChild;
		var row = header.nextSibling;
		while(row != null) {
			var group = row.firstChild.nextSibling.innerHTML.split(",");
			var user = row.firstChild.nextSibling.nextSibling.innerHTML.split(",");
			var namespaces = row.firstChild.nextSibling.nextSibling.nextSibling.innerHTML.split(",");
			var category = row.firstChild.nextSibling.nextSibling.nextSibling.nextSibling.innerHTML.split(",");
			var page = row.firstChild.nextSibling.nextSibling.nextSibling.nextSibling.nextSibling.innerHTML.split(",");
			var action = row.firstChild.nextSibling.nextSibling.nextSibling.nextSibling.nextSibling.nextSibling.getAttribute("value").split(",");
			var operation = row.firstChild.nextSibling.nextSibling.nextSibling.nextSibling.nextSibling.nextSibling.nextSibling.getAttribute("value");
			rules.push(new Rule(group == '-' ? null : group, 
								user == '-' ? null : user, 
								namespaces == '-' ? null : namespaces, 
								category == '-' ? null : category, 
								page == '-' ? null : page, 
								action, 
								operation));
			row = row.nextSibling;
		}
		
		return rules;
	},
	
	/**
	 * Reads a new rule from the INPUT elements
	 */
	getNewRule: function() {
		
		var selectedIndex = $('group').selectedIndex;
		var group, user;
		if (selectedIndex == 0) {
			group = null
            user = $('userconstraint').value;
    	} else {
    		group = $('group').options[selectedIndex].innerHTML;
    		user = null;
    	}
    	
		var selectedIndex = $('namespaces').selectedIndex;
		var namespaces, category, page;
		if (selectedIndex == 0) {
		  category = $('categoryconstraint').value;
		  namepsaces = null;	
		  page = null;
		} else if (selectedIndex == 1) {
		  category = null;
          namepsaces = null;    
          page = $('pageconstraint').value;
		} else {
		  category = null;
		  page = null;
          namespaces = $('namespaces').options[selectedIndex].innerHTML;
		}
		
		var selectedIndex = $('action').selectedIndex;
		var action = $('action').options[selectedIndex].value;
		
		var selectedIndex = $('operation').selectedIndex;
		var operation = $('operation').options[selectedIndex].value;
		
		return new Rule(group, user, namespaces, category, page, action, operation);
	},
	
	/**
	 * Initialize some action listeners for INPUT elements
	 */
	initializeListeners: function() {
		Event.observe($('group'), 'change', this.groupSelected.bind(this));
		Event.observe($('namespaces'), 'change', this.namespaceSelected.bind(this));
	},
	
	/**
	 * Called when group has been selected
	 */
	groupSelected: function() {
		var selectedIndex = $('group').selectedIndex;
        if (selectedIndex == 0) Form.Element.enable($('userconstraint')); else Form.Element.disable($('userconstraint'));
	},
	
	/**
	 * Called when a namespace has been selected
	 */
	namespaceSelected: function() {
		var selectedIndex = $('namespaces').selectedIndex;
        if (selectedIndex == 0) {
        	Form.Element.enable($('categoryconstraint'));
        	Form.Element.disable($('pageconstraint'));
        } else if (selectedIndex == 1) {
            Form.Element.enable($('pageconstraint'));
            Form.Element.disable($('categoryconstraint'));
        } else {
        	Form.Element.disable($('pageconstraint'));
        	Form.Element.disable($('categoryconstraint'));
        }
	}
}

/**
 * General ACL rule object
 */
var Rule = Class.create();
Rule.prototype = {
	initialize: function(group, user, namespaces, category, page, action, operation) {
		 this['group'] = group;
		 this['namespaces'] = namespaces;
		 this['action'] = action;
		 this['operation'] = operation;
		 
		 this['user'] = user;
		 this['page'] = page;
		 this['category'] = category;
	}
}

var acl = new ACL();
Event.observe(window, 'load', acl.initializeListeners.bind(acl));
	 