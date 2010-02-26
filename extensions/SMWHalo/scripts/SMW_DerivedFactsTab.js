/**
 * @file
 * @ingroup SMWHaloTripleStore
 * @author Kai Kühn
 * 
 * Context sensitive help for SMW+
 *
 * It uses a DIV element with id 'smw_csh' to add a help label. If this is not
 * available the DIV will be created as the first child of the 'innercontent'
 * div, which means, it appears between the tab section and the main head line.
 */
var SMW_DerivedFactsTab = Class.create();
SMW_DerivedFactsTab.prototype = {
	initialize: function(label) {
		this.mActiveTab = 1;
		this.sandglass = new OBPendingIndicator();
		this.mDFLoaded = false;
	},

	init:function() {
		if ($('dftTab1') && $('dftTab2')) {
			Event.observe('dftTab1', 'click', this.activateTab1.bindAsEventListener(this));
			
			Event.observe('dftTab2', 'click', this.activateTab2.bindAsEventListener(this));		
		}
	},
	
	activateTab1: function() {
		if (this.mActiveTab == 1) {
			// Tab is already active => return
			return;
		}
		this.mActiveTab = 1;
		$('dftTab2Content').hide();
		$('dftTab1Content').show();
		$('dftTab1').addClassName('dftTabActive');
		$('dftTab1').removeClassName('dftTabInactive');
		$('dftTab2').addClassName('dftTabInactive');
		$('dftTab2').removeClassName('dftTabActive');
	},
	
	activateTab2: function() {
		if (this.mActiveTab == 2) {
			// Tab is already active => return
			return;
		}
		this.mActiveTab = 2;
		$('dftTab1Content').hide();
		$('dftTab2Content').show();
		$('dftTab2').addClassName('dftTabActive');
		$('dftTab2').removeClassName('dftTabInactive');
		$('dftTab1').addClassName('dftTabInactive');
		$('dftTab1').removeClassName('dftTabActive');
		
		if (!this.mDFLoaded) {
			this.sandglass.show('dftTab2Content');
			
			// Get derived facts via ajax
			sajax_do_call('smwf_om_GetDerivedFacts',
			              [wgPageName],
			              this.getDerivedFacts.bind(this));
		}		
	},
	
	/**
	 * Callback function that gets the results of the request for derived facts
	 * of the current article.
	 */
	getDerivedFacts: function(request) {
		this.sandglass.hide();
	
		if (request.status != 200) {
			// No derived facts returned
			$('dftTab2ContentInnerDiv').replace('<div id="dftTab2ContentInnerDiv" style="padding: 20px;">'+gLanguage.getMessage('DF_REQUEST_FAILED')+'</div>');
			return;
		}
	
		var derivedFacts = request.responseText;
		$('dftTab2ContentInnerDiv').replace('<div id="dftTab2ContentInnerDiv">'+request.responseText+'</div>');
		this.mDFLoaded = true;
	}
}

var smwDft = new SMW_DerivedFactsTab();

Event.observe(window, 'load', smwDft.init.bindAsEventListener(smwDft));

