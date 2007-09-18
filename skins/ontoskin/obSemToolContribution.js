/*
 * obSemToolContribution.js
 * Author: KK
 * Ontoprise 2007
 *
 * Contributions from OntologyBrowser for Semantic Toolbar
 */


var OBSemanticToolbarContributor = Class.create();
OBSemanticToolbarContributor.prototype = {
	initialize: function() {

		this.textArea = null; // will be initialized properly in registerContributor method.
		this.l1 = this.selectionListener.bindAsEventListener(this);
		this.l2 = this.selectionListener.bindAsEventListener(this);
		this.l3 = this.selectionListener.bindAsEventListener(this);
	},

	/**
	 * Register the contributor and puts a button in the semantic toolbar.
	 */
	registerContributor: function() {
		this.comsrchontainer = stb_control.createDivContainer(CBSRCHCONTAINER, 0);
		this.comsrchontainer.setHeadline("OntologyBrowser");

		this.comsrchontainer.setContent('<button type="button" disabled="true" id="openEntityInOB" name="navigateToOB" onclick="obContributor.navigateToOB(event)">Mark a word...</button>');
		this.comsrchontainer.contentChanged();

		// register standard wiki edit textarea (advanced editor registers by itself)
		this.activateTextArea("wpTextbox1");

	},


	activateTextArea: function(id) {
		if (this.textArea) {
			Event.stopObserving(this.textArea, 'select', this.l1);
			Event.stopObserving(this.textArea, 'mouseup', this.l2);
			Event.stopObserving(this.textArea, 'keyup', this.l3);
		}
		this.textArea = $(id);
		if (this.textArea) {
			Event.observe(this.textArea, 'select', this.l1);
			Event.observe(this.textArea, 'mouseup', this.l2);
			Event.observe(this.textArea, 'keyup', this.l3);
			// intially disabled
			if ($("openEntityInOB") != null) Field.disable("openEntityInOB");
		}
	},

	/**
	 * Called when the selection changes
	 */
	selectionListener: function(event) {
		if ($("openEntityInOB") == null) return;
		if (!GeneralBrowserTools.isTextSelected(this.textArea)) {
			// unselected
			Field.disable("openEntityInOB");
			$("openEntityInOB").innerHTML = "" + "Mark a word...";
			this.textArea.focus();
		} else {
			// selected
			Field.enable("openEntityInOB");
			$("openEntityInOB").innerHTML = "" + "Open in OB";
			this.textArea.focus();
		}
	},

	/**
	 * Navigates to the OntologyBrowser with ns and title
	 */
	navigateToOB: function(event) {
		var selectedText = GeneralBrowserTools.getSelectedText(this.textArea);
		if (selectedText == '') {
			return;
		}
		var localURL = selectedText.split(":");
		if (localURL.length == 1) {
			// no namespace
			var queryString = 'searchTerm='+localURL[0];
		} else {
			var queryString = 'ns='+localURL[0]+'&title='+localURL[1];
		}
		var ontoBrowserSpecialPage = wgArticlePath.replace(/\$1/, 'Special:OntologyBrowser?'+queryString);
		window.open(wgServer + ontoBrowserSpecialPage, "");
	}


}

// create instance of contributor and register on load event so that the complete document is available
// when registerContributor is executed.
var obContributor = new OBSemanticToolbarContributor();
Event.observe(window, 'load', obContributor.registerContributor.bind(obContributor));


