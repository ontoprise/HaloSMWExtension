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

// $Id$

/**
 * @see http://wiki.apache.org/solr/SolJSON#JSON_specific_parameters
 * @class Manager
 * @augments AjaxSolr.AbstractManager
 */
AjaxSolr.FSManager = AjaxSolr.AbstractManager.extend(/** @lends AjaxSolr.Manager.prototype */
{
	retryWithoutHighlight: false,
	
	executeRequest: function(servlet){
		var self = this;
		if (this.proxyUrl) {
			jQuery.post(this.proxyUrl, 
						{ query: this.store.string()	}, 
						function(data){
							self.handleResponse(data);
						}, 'json');
		} else {
			var url = this.solrUrl + servlet + '?' + this.store.string() + '&wt=json&json.wrf=?';
			
			jQuery.jsonp({
				url: url,
				pageCache: false,
				error: function(xOptions, textStatus){
					self.handleErrorResponse();
				},
				success: function(json, textStatus){
					self.handleResponse(json);
					self.resetErrorState();
				}
			});
			
		}
	},
	
	/**
	 * This function is called when a server error for an ajax request occurred.
	 * SOLR sends an error "500 - maxClauseCount is set to 1024" if the wildcard
	 * expansion of a query generates too many clauses. 
	 * Highlighting snippets is often one of the causes for this problem. The query
	 * is sent again to SOLR with highlighting switched off. If this still fails,
	 * all widget that have the method "requestFailed" are notified of the error.
	 * 
	 */
	handleErrorResponse: function () {
		if (!this.retryWithoutHighlight) {
			// The first request failed => try again without highlighting
			this.setErrorState();
			this.doRequest(0);
		} else {
			// Second try failed too => notify all widgets.
			for (var widgetId in this.widgets) {
				if (typeof this.widgets[widgetId].requestFailed === 'function') {
					this.widgets[widgetId].requestFailed();
				}
			}
			this.resetErrorState();
		}
	},
	
	/**
	 * Clear the error state. Highlighting is switched on again.
	 */
	resetErrorState: function () {
		this.retryWithoutHighlight = false;
		this.store.addByValue('hl', true);
	},
	
	/**
	 * Set the error state and switch off highlighting for the second try.
	 */
	setErrorState: function () {
		this.retryWithoutHighlight = true;
		this.store.addByValue('hl', false);
	}
	
});
