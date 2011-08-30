/*
 *  Copyright (c) 2011, MediaEvent Services GmbH & Co. KG
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */
/**
 * @fileOverview Main R2Redit class
 * @author Christian Becker
 */
(function($){
	
	/**
	 * Embeds an R2Redit instance in a DOM container
	 * @param container	jQuery element to host the table / editor
	 * @param options
	 * 		sourceUrl	URL to load mapping from
	 *		basePath	Base path to R2Redit
	 * 		rdfSource	RDF/XML or TTL source (as an alternative to specifying sourceURL)
	 * 		title		Editor title to use
	 * 		onCommit	Callback handler to save mapping
	 *		serialize	If true, the mappings will be passed to onCommit as serialized TTL,
	 *					otherwise the rdfStore will be passed
	 */
	$.r2rEditor = function(container, options) {
		var base = this;
		base.container = container;
		base.options = options;
		
		/**
		 * Initialization
		 */
		base.init = function() {
			if (base.options.sourceUrl) {
				$.r2rUI.showProgress();
				$.ajax({
					url: base.options.sourceUrl,
					dataType:'text',
					success: function(data) {
						try {
							base.startFromData(data);
						} catch (err) {
							$.r2rUI.showError("R2Redit error", err);
						}
			   		},
			        error: function(jqXHR, textStatus, err) {
						$.r2rUI.showError("Unable to load mapping", err);        	
			        },
			        complete: function() {
      					$.r2rUI.hideProgress();
			        }
				});
			} else {
				base.startFromData(base.options.rdfSource);
			}
		};
		
		/**
		 * Actual initialization
		 */
		base.startFromData = function(rdfSource) {
			base.rdfStore = $.r2rUtils.loadRDF(rdfSource);
			base.mappingTable = new $.r2rEditorMappingTable(base.container, base.options, base.rdfStore, base.onCommit);
		};
		
		base.onCommit = function(rdfStore) {
			if (base.options.onCommit) {
				base.options.onCommit(base.options.serialize ? rdfStore.databank.dump({format:'text/turtle', serialize: true, indent: true}) : rdfStore);
			}
		}
		
		base.remove = function() {
			if (base.mappingTable) {
				base.mappingTable.remove();
			}
		}
						
        base.init();
        return base;
	};
})(jQuery);