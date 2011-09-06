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

if (typeof window.FacetedSearch == "undefined") {
//	Define the FacetedSearch module	
	window.FacetedSearch = { 
			classes : {}
	};
}

/**
 * @class LinkCurrentSearchWidget
 * This widget displays a link that contains all parameters that represent the
 * current search. The user can copy this link to store it for later use.
 * 
 */
(function ($) {
	
FacetedSearch.classes.LinkCurrentSearchWidget = AjaxSolr.AbstractWidget.extend({

	/**
	 * This function is called before a request is sent to the SOLR manager. 
	 * At this time the manager's store contains all SOLR parameters that can be
	 * serialized as a string that can be used in a URL.
	 * 
	 */
	beforeRequest: function() {
		var currentSearch = this.manager.store.string();
		$(this.target).empty();
		var link = wgServer + wgScript + '/' + wgPageName + '?' + 
		           'fssearch=' + currentSearch;
		$(this.target)
				.append(AjaxSolr.theme('currentSearch', link));
	}
	
});

})(jQuery);
