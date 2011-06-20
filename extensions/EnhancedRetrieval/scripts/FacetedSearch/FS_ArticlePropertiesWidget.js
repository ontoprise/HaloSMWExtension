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
 * @class ArticlePropertiesWidget
 * This widget displays the properties of an article in the result view.
 * 
 */
(function ($) {
	
FacetedSearch.classes.ArticlePropertiesWidget = AjaxSolr.AbstractWidget.extend({

	/**
	 * Sets the target of this widget
	 * @param {String} target
	 * 		ID of the target in the DOM
	 */
	setTarget: function (target) {
		this.target = target;	
	},
	
	/**
	 * This function is called when a request to the SOLR manager returns data.
	 * The data contains the property values (i.e. relations and attributes)
	 * of an article.
	 * 
	 */
	afterRequest: function () {
		var doc = this.manager.response.response.docs[0];
		// Show the cluster title
		$(this.target)
			.siblings('.xfsResultTable')
			.append(AjaxSolr.theme('articleProperties', doc));
	}
});

})(jQuery);
