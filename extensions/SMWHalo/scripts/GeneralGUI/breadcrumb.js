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
 * 
 * @file
 * @ingroup SMWHaloMiscellaneous
 * 
 * @defgroup SMWHaloMiscellaneous SMWHalo miscellaneous components
 * @ingroup SMWHalo
 * 
 * @author Kai K�hn, Robert Ulrich
 * 
 * Breadcrumb is a tool which displays the last 5 (default) visited pages as a queue.
 * 
 * It uses a DIV element with id 'breadcrumb' to add its list content. If this is not
 * available it displays nothing.
 */
var Breadcrumb = Class.create();
Breadcrumb.prototype = {
	initialize: function(lengthOfBreadcrumb) {
		//set the maximum count of elements in the breadcumbs
		this.lengthOfBreadcrumb = lengthOfBreadcrumb;
	},

	update: function() {
		//Read breadcrumbs from cookie
		var breadcrumb = GeneralBrowserTools.getCookie("breadcrumb");
		var breadcrumbArray;
		//get the querystring without the title=$foo, since pagename is handled different
		var currenturlquerystring = this.removeTitleFromQuery(document.location.search);

		try{
			breadcrumbArray = breadcrumb.evalJSON(true);
		} catch(err) {
			breadcrumbArray = null;
		}

		if (breadcrumbArray == null) {
			//Initialize Array with first breadcrumb entry using json
			breadcrumbArray = [
			{
				"pageName": wgPageName,
				"queryString": currenturlquerystring
			}
			];
		} else {
			//get breadcrumbs from cookie string and add new title
			//Add new entry, if pagename is different
			if (breadcrumbArray[breadcrumbArray.length-1].pageName != wgPageName) {
				breadcrumbArray.push(
				{
					"pageName": wgPageName,
					"queryString": currenturlquerystring
				}
				);
			//Overwrite last entry, if pagename is the same but querystring is different
			//this prevents the breadcrumb from showing only one page with different querystrings
			//trade off is that only the last action on the specific page is shown
			} else if(breadcrumbArray[breadcrumbArray.length-1].pageName == wgPageName
				&& breadcrumbArray[breadcrumbArray.length-1].queryString != currenturlquerystring ) {
				breadcrumbArray[breadcrumbArray.length-1]={
					"pageName": wgPageName,
					"queryString": currenturlquerystring
				};
			}

			//cut down breadcrumbs to maximum length
			if (breadcrumbArray.length > this.lengthOfBreadcrumb) {
				breadcrumbArray.shift();
			}   
		}
		// serialize breadcrumb to JSON and (re-)set cookie
		document.cookie = "breadcrumb="+breadcrumbArray.toJSON()+"; path="+wgScript;
		this.pasteInHTML(breadcrumbArray);
	},
    
	pasteInHTML: function(breadcrumbArray) {
		var	html = "",
			IndexCr = 0,
			crumbs = [], 
			breadcrumb = breadcrumbArray[breadcrumbArray.length - 1],
			title = breadcrumb.pageName.split(":"),
			currentCrumb = title.length == 2 ? title[1] : title[0],
			firstHeading = $('firstHeading'),
			bc_div = $('breadcrumb'),
			first = true;

		currentCrumb = currentCrumb.replace(/_/g, " ");

		for (var index = 0, len = breadcrumbArray.length; index < len; ++index) {
			var breadcrumb = breadcrumbArray[index];
			// remove namespace and replace underscore by whitespace
			var title = breadcrumb.pageName.split(":");
			var show = title.length == 2 ? title[1] : title[0];
			show = show.replace(/_/g, " ");
            
			// add item
			var encURI = encodeURIComponent(breadcrumb.pageName);
			if (wgArticlePath.indexOf('?title=') != -1) {
				encURI = encURI.replace(/%3A/g, ":"); // do not encode colon
				var articlePath = wgArticlePath.replace("$1", encURI) + breadcrumb.queryString;
			} else {
				encURI = encURI.replace(/%2F/g, "/"); // do not encode slash
				encURI = encURI.replace(/%3A/g, ":"); // do not encode colon
				var articlePath = wgArticlePath.replace("$1", encURI) + breadcrumb.queryString;
			}
			//add all previous visited pages as link
			var duplicated = false; 
			crumbs[IndexCr] = show;
			for (var index = 0; index < IndexCr; ++index) {
				if(crumbs[index] == show){
					duplicated = true;
				}
			}
			IndexCr++;
			if (index < len -1 && show != currentCrumb && duplicated == false) {
				html += !first ? '<span class="smwh_breadcrumb_sep">|</span>' : '';
				html += '<a href="'+wgServer+articlePath+'">'+show+'</a>';
				first = false;
			}
			// add current page as normal text if not already visible as first heading
			if( index == len -1 && !firstHeading ) {
				html += !first ? '<span class="smwh_breadcrumb_sep">|</span>' : '';
				html += '<span id="smwh_breadcrumb_currentpage">'+show+'</span>';
				first = false;
			}
		}

		//Check if there's no breadcrumb div
		if ( bc_div == null){
			//if so, check if there's a firstHeading
			if( firstHeading != null){
				//Add breadcrumb div before Heading
				firstHeading.insert({
					before: "<div id='breadcrumb'/>"
				});
				bc_div = $('breadcrumb');
			}
		}
		//verify that the div exists
		if (bc_div != null) {
			bc_div.innerHTML = html;
		}
	},

	//return the querystring without the title=$foo
	removeTitleFromQuery: function(querystring){
		if(querystring != null && querystring !=undefined){
			//remove title=$foobar& from querystring
			querystring = querystring.replace(/title=(.*?)&/i,"");
			//if title= is the only query and so the regex above doesn't match, remove it completely
			querystring = querystring.replace(/\?title=(.*?)$/i,"");
		} else {
			querystring = "";
		}
		return querystring.replace('/title=(.*?)&/i',"");
	}
}
var smwhg_breadcrumb = new Breadcrumb(5);
Event.observe(window, 'load', smwhg_breadcrumb.update.bind(smwhg_breadcrumb));
