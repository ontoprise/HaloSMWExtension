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

/**
 * @class PagerWidget
 * This class provides the pager functionality. The difference to the super class
 * implementation is that the pager is not displayed if the query and facet 
 * selection is empty.
 * 
 */
FacetedSearch.classes.PagerWidget = AjaxSolr.PagerWidget.extend({
	
	superAfterRequest: null,
	
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
		
		this.superAfterRequest();
		
	},
	
	init: function(){
		this.superAfterRequest = this.afterRequest;
		this.afterRequest = this.fpwAfterRequest;
	},
	
	
});

