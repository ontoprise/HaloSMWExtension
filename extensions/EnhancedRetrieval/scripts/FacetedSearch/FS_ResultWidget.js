/*  Copyright 2011, ontoprise GmbH
 *  This file is part of the FacetedSearch-Extension.
 *
 *   The FacetedSearch-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The FacetedSearch-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup FacetedSearchScripts
 * @author: Thomas Schweitzer
 */

if (typeof FacetedSearch == "undefined") {
//	Define the FacetedSearch module	
	var FacetedSearch = { 
			classes : {}
	};
}

(function ($) {
FacetedSearch.classes.ResultWidget = AjaxSolr.AbstractWidget.extend({

	// AjaxSolr.Manager - The manager from the AjaxSolr library. It is used for
	// retrieving the properties of an article
	mAjaxSolrManager: null,
		
	beforeRequest: function () {
//		$(this.target).html($('<img/>').attr('src', 'images/ajax-loader.gif'));
	},

	facetLinks: function (facet_field, facet_values) {
		var links = [];
		if (facet_values) {
			for (var i = 0, l = facet_values.length; i < l; i++) {
				links.push(AjaxSolr.theme('facet_link', facet_values[i], this.facetHandler(facet_field, facet_values[i])));
			}
		}
		return links;
	},
	
	facetHandler: function (facet_field, facet_value) {
		var self = this;
		return function () {
			self.manager.store.remove('fq');
			self.manager.store.addByValue('fq', facet_field + ':' + facet_value);
			self.manager.doRequest(0);
			return false;
		};
	},
	
	afterRequest: function () {
		$(this.target).empty();
		var query = this.manager.store.values('q');
		var emptyQuery = true;
		if (query.length == 1) {
			query = query[0];	
			emptyQuery = query.match(/^.*:\*$/) !== null;
		}
		var facetQuery = this.manager.store.values('fq');
		if (emptyQuery && facetQuery.length == 0) {
			// No query present => hide results and show a message
			$(this.target).append(AjaxSolr.theme('emptyQuery'));
			return;
		} 

		// Add all results
		var docIdField = FacetedSearch.singleton.FacetedSearchInstance.DOCUMENT_ID;
		var highlightField = FacetedSearch.singleton.FacetedSearchInstance.HIGHLIGHT_FIELD;
		for (var i = 0, l = this.manager.response.response.docs.length; i < l; i++) {
			var doc = this.manager.response.response.docs[i];
			// Attach this result widget instance to the doc
			doc.resultWidget = this;
			var highlight = "";
			if (!emptyQuery) {
				// Get the highlight information
				highlight = this.manager.response.highlighting[doc[docIdField]];
				highlight = highlight[highlightField][0];
				highlight = AjaxSolr.theme('highlight', highlight);
			}			
			$(this.target).append(AjaxSolr.theme('article', doc, 
												 AjaxSolr.theme('data', doc),
												 highlight,
												 this.showPropertiesHandler
												 ));
		}
	},

	/**
	 * Initializes this object.
	 * Creates a new AjaxSolrManager.
	 */
	init: function () {
		
		this.mAjaxSolrManager = new AjaxSolr.Manager({
			solrUrl : wgFSSolrURL
		});
		this.mAjaxSolrManager.init();
		this.mArticlePropertiesWidget = new FacetedSearch.classes.ArticlePropertiesWidget({
			id: 'fsArticleProperties'
		});
		this.mAjaxSolrManager.addWidget(this.mArticlePropertiesWidget);
		
		
		var lang = FacetedSearch.singleton.Language;
		$('a.xfsMore').live('click', function() {
			if ($(this).prev('span.xfsToggle').is(':visible')) {
				$(this).prev('span.xfsToggle').hide();
				$(this).text(lang.getMessage('more'));
			} else {
				$(this).prev('span.xfsToggle').show();
				$(this).text(lang.getMessage('less'));
			}
			return false;
		});
	},
	
	/**
	 * Callback for the "show" link that shows the property details of an article.
	 * A new SOLR query for the properties of an article is started.
	 */
	showPropertiesHandler: function () {
		// "this" is now the clicked element and not the result widget
		var $this= $(this);
		var lang = FacetedSearch.singleton.Language;
		
		// Check if the table with property values is already present
		var table = $this.parent().find('table');
		if (table.length === 0) {
			$this.text(lang.getMessage('hide'));
			
			var docData = $this.data('documentData');
			var resultWidget = docData.resultWidget;
		
			resultWidget.retrieveDocumentProperties(docData, this);
			return false;		
		}
		
		if (table.is(':visible')) {
			table.hide();
			$this.text(lang.getMessage('show'));
		} else {
			table.show();
			$this.text(lang.getMessage('hide'));
		}
		
		return false;
	},
	
	/**
	 * Makes a SOLR request for the property values of the given document
	 * @param {Object} docData
	 * 		The SOLR document whose properties are retrieved.
	 * @param {Object} domElement
	 * 		The DOM element that has to be extented with the property values
	 */
	retrieveDocumentProperties: function (docData, domElement) {
		var fs = FacetedSearch.singleton.FacetedSearchInstance;
		var asm = this.mAjaxSolrManager;
		
		// Reinitialize the manager's store
		asm.setStore(new AjaxSolr.ParameterStore());
		asm.store.init();
		
		asm.store.addByValue('json.nl', 'map');
		
		var fields = [];
		// add all relation fields
		if (docData[fs.RELATION_FIELD]) {
			fields = fields.concat(docData[fs.RELATION_FIELD]);
		}
		// add all attribute fields
		if (docData[fs.ATTRIBUTE_FIELD]) {
			fields = fields.concat(docData[fs.ATTRIBUTE_FIELD]);
		}
		asm.store.addByValue('fl', fields);
		var query = fs.DOCUMENT_ID + ':' + docData.id;
		asm.store.addByValue('q', query);
		
		this.mArticlePropertiesWidget.setTarget(domElement);
		asm.doRequest(0);
		
	}
	
});

})(jQuery);
