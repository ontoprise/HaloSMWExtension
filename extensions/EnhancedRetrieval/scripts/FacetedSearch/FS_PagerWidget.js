/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @ingroup FacetedSearchScripts
 * @author: Thomas Schweitzer
 */

if (typeof window.FacetedSearch == "undefined") {
//	Define the FacetedSearch module	
	window.FacetedSearch = { 
			classes : {}
	};
}

/**
 * @class PagerWidget
 * This class provides the pager functionality. The difference to the super class
 * implementation is that the pager is not displayed if the query and facet 
 * selection is empty.
 * 
 */
FacetedSearch.classes.PagerWidget = AjaxSolr.PagerWidget.extend({
	
	superAfterRequest: null,
	superClickHandler: null,
	
	// {array object} Array of all known page ranges. For each page index that
	// is displayed in the UI the actual range of SOLR documents is stored. 
	// Example: The document range for page 1 is 0 to 9, for page 2 it is 10 to 19.
	// Due to access control that filters documents the ranges may become not
	// evenly distributed. I.e. the range for the first ten valid documents may 
	// be 0 to 17.
	// The objects stored as range have the fields "startIdx" and "endIdx".
	mPageRanges: [],
	
	// {boolean} The ranges have to be reset for new queries but not if the user
	// switches to another page.
	mDoResetRanges: true,
	
	/**
	 * This function is called before a request is sent to SOLR. 
	 * The page ranges are reset if a "new" query is sent.
	 */
	beforeRequest: function () {
		var searchOffset =  this.manager.store.get('start').val();
		if (searchOffset === 0 && this.mDoResetRanges) {
			this.mPageRanges = [];
		}
		this.mDoResetRanges = true;
	},

	fpwAfterRequest: function() {
		var $ = jQuery;
		
		// Check if the query and the facet selection are empty. If this is the
		// case, no results and no pager are displayed.
		var query = this.manager.store.values('q');
		var emptyQuery = true;
		if (query.length == 1) {
			query = query[0];	
			emptyQuery = query.match(/^.*:\*$/) !== null;
		}
		var facetQuery = this.manager.store.values('fq');
		if (emptyQuery && facetQuery.length == 0) {
			// No query present => hide pager
		    $(this.target).empty();
			$('#pager-header').empty();
			return;
		} 
		
		// Check if the result contains document ranges. In this case access
		// control is active and the ranges are not simple multiples of 10.
		var approx = false;
		if (typeof this.manager.response.documentIndices !== 'undefined') {
			approx = true; // Numbers shown in the UI are only approximations
			// Insert the range information
			var range = {
					'startIdx'  : this.manager.response.documentIndices.startDocIdx,
					'endIdx'	: this.manager.response.documentIndices.nextDocIdx-1
			}
			
			// Normally ranges just have to be appended
			var numRanges = this.mPageRanges.length; 
			if (numRanges === 0 
				|| range.startIdx > this.mPageRanges[numRanges-1].startIdx) {
				this.mPageRanges.push(range);
				++numRanges;
			}
		} else {
			// No access control. The standard behaviour of the pager can be used.
			this.superAfterRequest();
			return;
		}
		
	    var start = parseInt(this.manager.response.responseHeader.params.start || 0);
	    var total = parseInt(this.manager.response.response.numFound);
	    
	    // Find the current page by looking for the start and sum up the number
	    // of filtered pages
	    this.currentPage = 1;
	    var filtered = 0;
	    var numDocsInResult = this.manager.response.response.docs.length;
	    for (var i = 0; i < numRanges; ++i) {
	    	if (this.mPageRanges[i].startIdx === start) {
	    		this.currentPage = i+1;
	    	}
	    	var end = Math.min(this.mPageRanges[i].endIdx, total);
	    	filtered += end + 1 - this.mPageRanges[i].startIdx;
	    	if (this.mPageRanges[i].endIdx <= total) {
	    		filtered -= 10;
	    	} else {
	    		filtered -= numDocsInResult + 1;
	    		// Now we've got the exact number of results
	    		approx = false;
	    	}
	    }
	    // The number of actual total pages is unknown from the start. We can
	    // only determine this value when the last page is reached.
	    if (this.mPageRanges[numRanges-1].endIdx > total) {
	    	// The current page contains the last result
	    	this.totalPages = numRanges;
	    } else {
	    	// There will at least be one next page
	    	this.totalPages = Math.max(this.currentPage + 1, this.mPageRanges.length);
	    }

	    $(this.target).empty();

	    
	    this.renderLinks(this.windowedLinks());
	    
	    var perPage = this.manager.response.response.docs.length;
	    var offset = (this.currentPage-1) * 10;
	    var dispTotal = total - filtered;
	    this.renderHeader(perPage, offset, dispTotal, approx);
		
	},
	
	/**
	 * This function is called when an ajax request fails e.g. because of a server
	 * error. In this case the pager is hidden.
	 * 
	 */
	requestFailed: function () {
		var $ = jQuery;
		$('#pager-header').empty();
		$(this.target).empty();
	},

	  /**
	   * @param {Number} page A page number.
	   * @returns {Function} The click handler for the page link.
	   */
	fpwClickHandler: function (page) {
		var self = this;
		return function () {
			var next = (page - 1) * (self.manager.response.responseHeader.params.rows || 10);
			if (self.mPageRanges.length > 0) {
				// variable ranges due to access control
				if (page > self.mPageRanges.length) {
					// The next range is still unknown 
					// => continue after the last range
					next = self.mPageRanges[page-2].endIdx + 1;
				} else {
					// Jump to an existing range
					next = self.mPageRanges[page-1].startIdx;
				}
			}
			self.manager.store.get('start').val(next);
			self.mDoResetRanges = false;
			self.manager.doRequest();
			return false;
		}
	},
	
	
	init: function(){
		// Overwrite methods of super class
		this.superAfterRequest = this.afterRequest;
		this.afterRequest = this.fpwAfterRequest;
		
		this.superClickHandler = this.clickHandler;
		this.clickHandler = this.fpwClickHandler;
		
	},
	
	
});

