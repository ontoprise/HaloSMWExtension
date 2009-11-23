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
		// save the current comment for beaing able to repost on failure
		this.internalCall = null;
		this.runCount = 0;
		this.XMLResult = null;
		this.currentWikiurl = null;
		this.currentWikiPath = null;
		this.currentPageName = null;
		this.currentPageContent = null;
		this.currentUserName = null;
		this.currentUserPassword = null;
		this.currentDomain = null;
	},

	/**
	 * The processForm function takes care about the html input form
	 * located in the wiki article to enter article comments.
	 * 
	 * It gets the values from all fields and parses them into a string to form a template.
	 * 
	 */
	processForm: function() {

		//1. disable form
		var cf = $('ce_cf');
		cf.disable();
		
		//2. and add pending indicator
		
		if (this.pendingIndicatorCF == null) {
			this.pendingIndicatorCF = new OBPendingIndicator($('ce_cf_textarea'));
		}
		this.pendingIndicatorCF.show();

		//form params
		//TODO: the rating!
		var now = new Date();
		Element.extend(now);
		var nowJSON = now.toJSON();
		
		var pageName = wgPageName + "_" + now.getTime();
		var pageContent = "{{Comment|CommentPerson=" + $('ce_cf_user_field').value.strip() + 
			"|CommentRelatedArticle=" + wgPageName +
			"|CommentRating=" + "true"/*$('ce_cf_user_rating').value*/ +
			"|CommentDatetime=" + nowJSON.substring(1, nowJSON.length-2) +
			"|CommentContent=" + $('ce_cf_textarea').value.strip() + "|}}";

		this.currentPageName = escape(pageName);
		this.currentPageContent = escape(pageContent);

		//internal call -> other callBack for external. 
		this.runCount += 1;
		this.internalCall = true;
		this.createNewPage('', '', this.currentPageName, this.currentPageContent, '', '', '');

		return false;
	},
	
	/**
	 * This function takes care of the ajax call.
	 * @param wikiurl
	 * @param wikipath
	 * @param pageName
	 * @param pageContent
	 * @param userName
	 * @param userPassword
	 * @param domain
	 * @return from callback
	 */
	createNewPage: function(wikiurl, wikipath, pageName, pageContent, userName, userPassword, domain) {

		if(this.internalCall) {
			sajax_do_call('cef_comment_createNewPage', 
				[wikiurl, wikipath, pageName, pageContent, userName, userPassword, domain],
				this.processFormCallback.bind(this));
		} else {
			sajax_do_call('cef_comment_createNewPage', 
				[wikiurl, wikipath, pageName, pageContent, userName, userPassword, domain],
				this.createNewPageCallback.bind(this));
		}
	},
	
	/**
	 * The callback function for createNewPage
	 */
	createNewPageCallback: function(request){

		return request.responseText;
	},
	
	/**
	 * The callback function for createNewPage
	 * @param request
	 */
	processFormCallback: function(request){

		//alert(request.responseText);
		var resultDOM = this.XMLResult = GeneralXMLTools.createDocumentFromString(request.responseText);	
		//alert(resultDOM);
		
		var valueEl = resultDOM.getElementsByTagName("value")[0];
		
		var htmlmsg = resultDOM.getElementsByTagName("message")[0].firstChild.nodeValue;
		
		if(valueEl.nodeType == 1) {
			var valueCode = valueEl.firstChild.nodeValue
			if(valueCode == 0){
				//fine.
				this.pendingIndicatorCF.hide();
				$('ce_cf_message').setAttribute("class", "ce_cf_failure_message");
				$('ce_cf_message').innerHTML = htmlmsg;
				//reset and enable form again
				$('ce_cf').reset();
				$('ce_cf').enable();
			}else if(valueCode == 1) {
				//error!
				if(this.runCount <=1) {
					//run once again with new time
					var now = new Date();
					var newPageName = wgPageName + "_" + now.getTime();
					this.currentPageName = newPageName;

					this.createNewPage(this.currentWikiurl, this.currentWikiPath,
						this.currentPageName, this.currentPageContent, this.currentUserName,
						this.currentUserPassword, this.currentDomain);					
				}else{
					//second run and failure again. so show message
					this.pendingIndicatorCF.hide();
					$('ce_cf_message').setAttribute("class", "ce_cf_success_message");
					$('ce_cf_message').innerHTML = htmlmsg;
					//reset and enable form again
					$('ce_cf').reset();
					$('ce_cf').enable();
				}
			}
		}

		return false;
	},

}

// Singleton of this class

var ceCommentForm = new CECommentForm();