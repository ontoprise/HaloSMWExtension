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
		this.textareaIsDefault = true;
		this.usernameIsDefault = true;
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
		var cf = $('ce-cf');
		cf.disable();
		
		//2. and add pending indicator
		
		if (this.pendingIndicatorCF == null) {
			this.pendingIndicatorCF = new OBPendingIndicator($('ce-cf-textarea'));
		}
		this.pendingIndicatorCF.show();

		//form params
		var now = new Date();
		Element.extend(now);
		var nowJSON = now.toJSON();
		
		var pageName = wgPageName + "_" + now.getTime();
		
		//rating things
		var ratingValue = '';
		var ratingGrp = document['forms']['ce-cf']['rating'];
		for( i = 0; i < ratingGrp.length; i++){
			if (ratingGrp[i].checked == true) {
				ratingValue = ratingGrp[i].value;
			}
		}
		
		//textarea

		var textArea = ($('ce-cf-textarea').value)? $('ce-cf-textarea').value: '';
		//remove leading and trailing whitespaces
		textArea = textArea.strip();
		if(textArea.blank() || this.textareaIsDefault) {
			this.pendingIndicatorCF.hide();
			$('ce-cf-message').setAttribute("class", "ce-cf-success-message");
			$('ce-cf-message').innerHTML = 'You didn\'t enter a valid comment.';
			//reset and enable form again
			//$('ce-cf').reset();
			$('ce-cf').enable();
			return false;
		}
		//remove script tags
		textArea = textArea.stripScripts();
		//escape html chars
		textArea = textArea.escapeHTML();

		//TODO: wgUserName is null, when not logged in!
		
		var pageContent = "{{Comment|CommentPerson=" + wgUserName+ 
			"|CommentRelatedArticle=" + wgPageName +
			"|CommentRating=" + ratingValue +
			"|CommentDatetime=" + nowJSON.substring(1, nowJSON.length-2) +
			"|CommentContent=" + textArea + "|}}";

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
				$('ce-cf-message').setAttribute("class", "ce-cf-success-message");
				$('ce-cf-message').innerHTML = htmlmsg;
				//reset and enable form again
				$('ce-cf').reset();
				$('ce-cf').enable();
			}else if(valueCode == 1) {
				//error!
				//following doesn't work. He's running and running and running...:
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
					$('ce-cf-message').setAttribute("class", "ce-cf-failure-message");
					$('ce-cf-message').innerHTML = htmlmsg;
					//reset and enable form again
					$('ce-cf').reset();
					$('ce-cf').enable();
				}
			}
		}

		return false;
	},
	
	/*helper functions*/
	
	formReset:function() {
		this.textareaIsDefault = true;
		this.usernameIsDefault = true;
	},
	
	/**
	 * onClick event function for textarea
	 */
	selectTextarea: function() {
		//check if we still have the form default in here
		if (this.textareaIsDefault) {
			$('ce-cf-textarea').activate();
		}
	},
	
	/**
	 * Disable the onClick function for textarea
	 */
	textareaKeyPressed:function() {
		this.textareaIsDefault = false;
	},
	
	/**
	 * onClick event function for username input
	 */
	selectUsernameInput: function() {
		//check if we still have the form default in here
		if (this.usernameIsDefault) {
			$('ce-cf-user-field').activate();
		}
	},
	
	/**
	 * Disable the onClick function for username input
	 */
	usernameInputKeyPressed:function() {
		this.usernameIsDefault = false;
	},
}

// Singleton of this class

var ceCommentForm = new CECommentForm();