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
			sajax_do_call('smwfCSDispatcher', [searchTerm], this.smwfCombinedSearchCallback.bind(this, "csFoundEntities"));
			this.tripleSearchPendingElement.show();
			sajax_do_call('smwfCSSearchForTriples', [searchTerm], this.smwfTripleSearchCallback.bind(this, "queryPlaceholder"));
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
		sajax_do_call('smwfCSAskForAttributeValues', [parts], this.smwfCombinedSearchCallback.bind(this, "queryPlaceholder"));
	}

}

// create instance of contributor and register on load event so that the complete document is available
// when registerContributor is executed.
var csContributor = new CombinedSearchContributor();
Event.observe(window, 'load', csContributor.registerContributor.bind(csContributor));


