/*  Copyright 2009, ontoprise GmbH
*  This file is part of the Collaboration-Extension.
*
*   The Collaboration-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The Collaboration-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

var CECommentForm = Class.create();

/**
 * This class provides language dependent strings for an identifier.
 * 
 */
CECommentForm.prototype = {
		
	/**
	 * @public
	 * 
	 * Constructor.
	 */
	initialize: function() {
		//do nothing special here
	},

	/**
	 * The processForm function takes care about the html input form
	 * located in the wiki article to enter article comments.
	 * 
	 * It gets the values from all fields and parses them into a xml doc
	 * specified in design document.
	 * 
	 * @return XML
	 * 	A wiki article represented in xml
	 */
	processForm: function() {

		//1. disable form
		
		//2. and add pending indicator
		$('ce_cf_submitbuttonID').hide();
		
		/*if (this.pendingIndicatorCF == null) {
			this.pendingIndicatorCF = new OBPendingIndicator($('ce_cf_textarea'));
		}
		this.pendingIndicatorCF.show();*/
		
		var pageName = wgPageName + "_" + new Date().getTime();
		var pageContent = "{{Comment|Commenter=" + $('ce_cf_user_field').value + 
			"|CommentRelatedArticle=" + wgPageName +
			"|CommentRating=" + "true"/*$('ce_cf_user_rating').value*/ +
			"|CommentContent=" + $('ce_cf_textarea').value +
			"|}}";

		pageName = escape(pageName);
		pageContent = escape(pageContent);
		
		
		
		sajax_do_call('cef_comment_createNewPage', 
				['', '', pageName, pageContent,'','',''], this.processFormCallback.bind(this));
		
				
		return false;
	},
	
	processFormCallback: function(request){
		alert (request.responseText);
		
		//... wait ...
		
		//6. if exists-failure, try once again with new timestamp and goto 4.

		//7. show msg (success or failure) instead of form and return true

	}

}

// Singleton of this class

var ceCommentForm = new CECommentForm();