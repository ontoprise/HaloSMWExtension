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
 * @class FacetWidget
 * This class handles the facet fields.
 * 
 */
FacetedSearch.classes.FacetWidget = AjaxSolr.AbstractFacetWidget.extend({
	
	//--- Constants ---
	GROUP_SIZE: 10,

	//--- Members ---
	// {string} The theme that is used for rendering the facets
	mFacetTheme: 'facet',
	
	// {bool} If true, all selected facets are hidden by this widget
	mHideSelectedFacet: true,
	
	// {array object} Array of all facet objects that are displayed. They are
	// stored because the facets are only shown on demand
	mFacetItems: [],
	
	// {int} Index of the facet group that is currently being displayed
	mCurrentGroup : 0,
	
	// {bool} If true, the facets are decorated with the delete icon
	mRemoveFacet: false,
	
	// {Function} The click handler for facets can be overwritten
	mClickHandler: null,
	
	//--- Getters/Setters
	
	setFacetTheme: function (facetTheme) {
		this.mFacetTheme = facetTheme;
	},
	setHideSelectedFacet: function (hideFacet) {
		this.mHideSelectedFacet = hideFacet;
	},
	setRemoveFacet: function (removeFacet) {
		this.mRemoveFacet = removeFacet;
	},
	setClickHandler: function (clickHandler) {
		this.mClickHandler = clickHandler;
	},
	
	//--- Methods ---
	afterRequest: function () {
		if (this.noRender) {
			// This widget does not render anything (due to unification of 
			// attributes and relations in one widget.)
			return;
		}
		
		this.retrieveFacetItems();
		this.showFacetsForGroup(0);
		
	},
	
	/**
	 * Retrieves and stores all facet items of the last request.
	 */
	retrieveFacetItems: function () {
		var $ = jQuery;
		
		if (this.fields === undefined) {
			this.fields = [this.field];
		}
		
		var fq = this.manager.store.values('fq');

		this.mFacetItems = [];
		for (var i = 0; i < this.fields.length; i++) {
			var field = this.fields[i];
			if (this.manager.response.facet_counts.facet_fields[field] === undefined) {
				continue;
			}
			for (var facet in this.manager.response.facet_counts.facet_fields[field]) {
				var count = parseInt(this.manager.response.facet_counts.facet_fields[field][facet]);
				
				if (this.mHideSelectedFacet) {
					// Do not show facets that are selected 
					var fullName = field + ':' + facet;
					if ($.inArray(fullName, fq) >= 0) {
						continue;
					}
				}
				this.mFacetItems.push({
					field: field,
					facet: facet,
					count: count
				});
			}
		}
				
		this.mFacetItems.sort(function(a, b) {
			return a.count > b.count ? -1 : 1;
		});
			
	},
	
	/**
	 * Not all facet items are shown at once. They are unfolded in groups with
	 * GROUP_SIZE elements. 
	 * This function shows the elements of the group with the given index.
	 * @param {int} group
	 * 		Index of the group to show
	 * @return {bool}
	 * 		true, if there are further groups to display
	 * 		false, if this was the last group
	 */
	showFacetsForGroup : function (group) {
		var $ = jQuery;
		var target = $(this.target);
		
		this.mCurrentGroup = group;
		
		if (group === 0) {
			// Clear the target for the first group of items
			target.html('<div/>');
		}
		
		var contentDiv = target.find(':first');
		
		if (this.mFacetItems.length == 0) {
			target.html(AjaxSolr.theme('no_items_found'));
			return false;
		}
		
		var html = "";
		var start = group * this.GROUP_SIZE;
		var end   = Math.min((group+1)*this.GROUP_SIZE, this.mFacetItems.length);
		// All items are enclosed in a group div
		var groupDiv = $('<div group="' + group + '" />');
		contentDiv.append(groupDiv); 
		for (var i = start, l = end; i < l; i++) {
			var facet = this.mFacetItems[i].facet;
			var clickHandler = this.mClickHandler;
			if (!clickHandler) {
				var widget = this;
				if (this.mFacetItems[i].field !== this.field) {
					// Unify handling of attributes and relations
					widget = FacetedSearch.singleton.FacetedSearchInstance.getRelationWidget();
				}
				clickHandler = widget.facetClickHandler(facet);
			}
			
			var entry = AjaxSolr.theme(this.mFacetTheme, facet, 
						               this.mFacetItems[i].count, 
									   clickHandler, 
									   FacetedSearch.classes.ClusterWidget.showPropertyDetailsHandler, 
									   this.mRemoveFacet);			
			groupDiv.append(entry);
		}
		
		// Show the "more | less" links if needed
		if (group === 0 && this.mFacetItems.length > this.GROUP_SIZE) {
			target.append(AjaxSolr.theme('moreLessLink', 
			                             this.moreClickHandler(),
										 this.lessClickHandler()));
		}
		
		// Return if there are more groups to show
		return this.mFacetItems.length > (this.mCurrentGroup+1) * this.GROUP_SIZE;
	},
	
	
	init: function () {
	},
	
	/**
	 * Click handler for the "more" link.
	 */
	moreClickHandler: function(){
		var self = this;
		return function() {
			var $ = jQuery;
			var nextGroup = self.mCurrentGroup+1;
			var moreAvailable = self.mFacetItems.length > (nextGroup+1) * self.GROUP_SIZE;
			
			// Check if the html of the next group is already present
			var target = $(self.target);
			var nextGroupDiv = target.find('[group=' + nextGroup + ']');
			if (nextGroupDiv.length === 1) {
				// Group already exists => show it
				nextGroupDiv.show();
				self.mCurrentGroup++;
			} else {
				// Generate the content for the next group
				self.showFacetsForGroup(nextGroup);
			}
			var morePresent = true;
			if (!moreAvailable) {
				// Hide the link "more" and the following separator "|"
				$(this).hide();
				$(this).next().hide();
				morePresent = false;
			}
			if (self.mCurrentGroup > 0) {
				// Show the link "less" and the preceding separator "|"
				var less = $(this).parent().children('a.xfsFLess');
				less.show();
				if (morePresent) {
					less.prev().show();
				}
			}
			
			return false;
		}
	},
	
	/**
	 * Click handler for the "less" link.
	 */
	lessClickHandler: function(){
		var self = this;
		return function() {
			var $ = jQuery;
			
			// Get the html of the current group
			var target = $(self.target);
			var groupDiv = target.find('[group=' + self.mCurrentGroup + ']');
			if (groupDiv.length === 1) {
				// Hide the content of the current group
				groupDiv.hide();
				self.mCurrentGroup--;
			}

			var lessPresent = true;
			if (self.mCurrentGroup === 0) {
				// Hide the link "less" and the preceding separator "|"
				$(this).hide();
				$(this).prev().hide();
				lessPresent = false;
			}
			// Show the "more" link
			$(this).parent().children('a.xfsFMore').show();
			if (lessPresent) {
				$(this).prev().show();
			}
			return false;
		}
	},
	
	/**
	 * Click handler for the given facet.
	 * The facet is marked as expanded facet for the UI and the click handler of
	 * the super class is called.
	 * @param {String} facet
	 * 		Name of the facet.
	 */	
	facetClickHandler: function (facet) {
		var superClickHandler = this.clickHandler(facet);
		return function () {
			FacetedSearch.singleton.FacetedSearchInstance.addExpandedFacet(facet);
			superClickHandler();
		}
	}
	
});

