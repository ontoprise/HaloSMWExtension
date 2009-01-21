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

var CombinedSearchContributor = Class.create();
CombinedSearchContributor.prototype = {
	initialize: function() {
		// create a query placeHolder for potential ask-queries
		this.queryPlaceholder = document.createElement("div");
		this.queryPlaceholder.setAttribute("id", "queryPlaceholder");
		this.queryPlaceholder.innerHTML = gLanguage.getMessage('ADD_COMB_SEARCH_RES');
		this.pendingElement = null;
		this.tripleSearchPendingElement = null;
	},

	/**
	 * Register the contribuor and puts a button in the semantic toolbar.
	 */
	registerContributor: function() {
		if (!stb_control.isToolbarAvailable()) return;
		if (wgCanonicalSpecialPageName != 'Search' || wgCanonicalNamespace != 'Special') {
			// do only register on Special:Search
			return;
		}

		// register CS container
		this.comsrchontainer = stb_control.createDivContainer(COMBINEDSEARCHCONTAINER, 0);
		this.comsrchontainer.setHeadline(gLanguage.getMessage('COMBINED_SEARCH'));

		this.comsrchontainer.setContent('<div id="csFoundEntities"></div>');
		this.comsrchontainer.contentChanged();

		// register content function and notify about initial update

		var searchTerm = GeneralBrowserTools.getURLParameter("search");

		// do combined search and populate ST tab.
		if ($('stb_cont8-headline') == null) return;
		$("bodyContent").insertBefore(this.queryPlaceholder, $("bodyContent").firstChild);
		this.pendingElement = new OBPendingIndicator($('stb_cont8-headline'));
		this.tripleSearchPendingElement = new OBPendingIndicator($('queryPlaceholder'));
		if (searchTerm != undefined && searchTerm.strip() != '') {
			this.pendingElement.show();
			sajax_do_call('smwf_cs_Dispatcher', [searchTerm], this.smwfCombinedSearchCallback.bind(this, "csFoundEntities"));
			this.tripleSearchPendingElement.show();
			sajax_do_call('smwf_cs_SearchForTriples', [searchTerm], this.smwfTripleSearchCallback.bind(this, "queryPlaceholder"));
		}

		// add query placeholder
	},
	
	smwfTripleSearchCallback: function(containerID, request) {
		this.tripleSearchPendingElement.hide();
		$(containerID).innerHTML = request.responseText;
	},

	smwfCombinedSearchCallback: function(containerID, request) {
		this.pendingElement.hide();
		$(containerID).innerHTML = request.responseText;
		this.comsrchontainer.contentChanged();
	},

	searchForAttributeValues: function(parts) {
		this.pendingElement.show($('cbsrch'));
		sajax_do_call('smwf_cs_AskForAttributeValues', [parts], this.smwfCombinedSearchCallback.bind(this, "queryPlaceholder"));
	},
	
	/**
	 * Navigates to OntologyBrowser
	 * 
	 * @param pageName name of page (URI encoded)
	 * @param pageNS namespace
	 * @param last part of path to OntologyBrowser (name of special page)
	 */
	navigateToOB: function(pageName, pageNS, ontoBrowserPath) {
		queryStr = "?entitytitle="+pageName+(pageNS != "" ? "&ns="+pageNS : "");
		var path = wgArticlePath.replace(/\$1/, ontoBrowserPath);
		smwhgLogger.log(pageName, "CS", "entity_opened_in_ob")
		window.open(wgServer + path + queryStr, "");
	},
	
	/**
	 * Navigates to Page
	 * 
	 * @param pageName name of page (URI encoded)
	 * @param pageNS namespace
	
	 */
	navigateToEntity: function(pageName, pageNS) {
		var path = wgArticlePath.replace(/\$1/, pageNS+":"+pageName);
		smwhgLogger.log(pageName, "CS", "entity_opened")
		window.open(wgServer + path, "");
	},
	
	/**
	 * Navigates to Page in edit mode
	 * 
	 * @param pageName name of page (URI encoded)
	 * @param pageNS namespace
	 */
	navigateToEdit: function(pageName, pageNS) {
		queryStr = "?action=edit";
		var path = wgArticlePath.replace(/\$1/, pageNS+":"+pageName);
		smwhgLogger.log(pageName, "CS", "entity_opened_to_edit");
		window.open(wgServer + path + queryStr, "");
	}

}

// create instance of contributor and register on load event so that the complete document is available
// when registerContributor is executed.
var csContributor = new CombinedSearchContributor();
var csLoadObserver = csContributor.registerContributor.bind(csContributor);
Event.observe(window, 'load', csLoadObserver);


