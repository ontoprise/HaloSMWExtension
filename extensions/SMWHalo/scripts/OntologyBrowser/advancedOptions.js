/*  Copyright 2010, ontoprise GmbH
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
*/

/**
 * @file
 * @ingroup OntologyBrowser
 * @author Thomas Schweitzer
 */

var OBAdvancedOptions = Class.create();

/**
 * The class OBAdvancedOptions stores all advanced options for the ontology browser
 * and provides the callbacks for user interface.
 */
OBAdvancedOptions.prototype = {

	/**
	 * Constructor of class OBAdvancedOptions
	 */
	initialize: function() {
		this.dataSource = "";
	},
	
	/**
	 * Returns the name of the currently selected data source.
	 */
	getDataSource: function() {
		return this.dataSource;
	},
	
	/**
	 *Sets the name of the current data source.
	 */
	setDataSource: function(dataSource) {
		this.dataSource = dataSource;
	},
	
	/**
	 * Initializes the callbacks for the UI after the window was loaded.
	 */
	onLoad: function(event){
		var dss = $('dataSourceSelector');
		if (dss == null) {
			return;
		}
		this.dataSource = dss.options[dss.selectedIndex].text;
		
		Event.observe('dataSourceSelector', 'change',
						function(event) {
							var obj = $('dataSourceSelector');
							var value = obj.options[obj.selectedIndex].text;
							obAdvancedOptions.setDataSource(value);
							resetOntologyBrowser();
						});
		
		Event.observe('aoFoldIcon', 'click',
				function(event) {
					var obj = $('aoFoldIcon');
					var classes = obj.classNames().toArray();
					var addClass    = 'aoFoldOpen';
					var removeClass = 'aoFoldClosed';
					
					if (classes.indexOf('aoFoldOpen') != -1) {
						// The option box is open => close it
						addClass    = 'aoFoldClosed';
						removeClass = 'aoFoldOpen';
						doOpen      = false;
						$('aoContent').hide();
					} else {
						$('aoContent').show();
					}
					obj.addClassName(addClass);
					obj.removeClassName(removeClass);
				});

		$('aoContent').hide();
	}
}
var obAdvancedOptions = new OBAdvancedOptions();
Event.observe(window, 'load', obAdvancedOptions.onLoad.bindAsEventListener(obAdvancedOptions));

