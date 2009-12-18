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
		this.ratingValue = null;
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

		/* form params */
		//date
		var now = new Date();
		Element.extend(now);
		var nowJSON = now.toJSON();
		
		var pageName = wgPageName + '_' + now.getTime();
		
		//rating
		var ratingString = '';

		if ( this.ratingValue != null) {
			ratingString = '|CommentRating=' + this.ratingValue;
		}
		
		//textarea
		var textArea = ($('ce-cf-textarea').value)? $('ce-cf-textarea').value: '';
		//remove leading and trailing whitespaces
		textArea = textArea.strip();
		if(textArea.blank() || this.textareaIsDefault) {
			this.pendingIndicatorCF.hide();
			$('ce-cf-message').setAttribute('class', 'ce-cf-failure-message');
			$('ce-cf-message').innerHTML = 'You didn\'t enter a valid comment.';
			//enable form again
			$('ce-cf').enable();
			return false;
		}
		//remove script tags
		textArea = textArea.stripScripts();
		//escape html chars
		textArea = textArea.escapeHTML();
		//property & template cleaning:
		textArea = textArea.replace(/::/g, '_');
		textArea = textArea.replace(/\[/g, '_');
		textArea = textArea.replace(/\]/g, '_');
		textArea = textArea.replace(/\{/g, '_');
		textArea = textArea.replace(/\}/g, ':');
		
		
		//TODO: wgUserName is null, when not logged in!
		var userNameString = '';
		if( wgUserName != null && ceUserNS != null )
			userNameString = 'CommentPerson=' + ceUserNS + ':' + wgUserName;

		var pageContent = '{{Comment|' +
			userNameString +
			'|CommentRelatedArticle=' + wgPageName +
			ratingString  +
			'|CommentDatetime=' + nowJSON.substring(1, nowJSON.length-2) +
			'|CommentContent=' + textArea + '|}}';

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
	createNewPage: function(wikiurl, wikipath, pageName, pageContent,
			userName, userPassword, domain) {

		if(this.internalCall) {
			sajax_do_call('cef_comment_createNewPage', 
				[wikiurl, wikipath, pageName, pageContent, userName, userPassword, domain],
				this.processFormCallback.bind(this));
		} else {
			sajax_do_call('cef_comment_createNewPage', 
				[wikiurl, wikipath, pageName, pageContent, userName, userPassword, domain],
				this.createNewPageCallback.bind(this));
		}
		this.runCount++;
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
		
		var valueEl = resultDOM.getElementsByTagName('value')[0];
		
		var htmlmsg = resultDOM.getElementsByTagName('message')[0].firstChild.nodeValue;
		
		if(valueEl.nodeType == 1) {
			var valueCode = valueEl.firstChild.nodeValue
			if(valueCode == 0){
				//fine.
				this.pendingIndicatorCF.hide();
				$('ce-cf-message').setAttribute('class', 'ce-cf-success-message');
				$('ce-cf-message').innerHTML = htmlmsg + ' Page is reloading...';
				var pending = new OBPendingIndicator($('ce-cf-message'));
				pending.show();
				$('ce-cf-message').show();
				//reset and hide form again
				$('ce-cf').reset();
				$('ce-cf').hide();
				//maybe better to do a page reload with action=purge!?!
				var winSearch = window.location.search; 
				if ( winSearch.indexOf('action=purge') ) {
					window.location.href=window.location.href;
				} else {
					if ( winSearch.indexOf('?') ) {
						window.location.href = window.location.href.concat('&action=purge');
					} else {
						window.location.href = window.location.href.concat('?action=purge');
					}
				}
			} else if( valueCode == 1 || valueCode == 2 ) {
				//error, article already exists or permisson denied.
				this.pendingIndicatorCF.hide();
				$('ce-cf-message').setAttribute('class', 'ce-cf-failure-message');
				$('ce-cf-message').innerHTML = htmlmsg;
				$('ce-cf-message').show();
				//reset and enable form again
				//$('ce-cf').reset();
				$('ce-cf').enable();
			} else {
				//sthg's really gone wrong
			}
		}

		return false;
	},
	
	/*helper functions*/
	
	formReset:function() {
		this.textareaIsDefault = true;
		
		if (this.ratingValue != null) {
			var oldhtmlid = 'ce-cf-rating' + String(this.ratingValue + 2);
			$(oldhtmlid).src = $(oldhtmlid).src.replace(/active/g, 'inactive');
			this.ratingValue = null;
		}
		$('ce-cf').reset();
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
	 * switch for rating
	 */
	switchRating: function( htmlid, ratingValue ) {
		
		var ratingHTML = $(htmlid);
		var ratingImg = cegScriptPath + '/skins/Comment/icons/';
		
		if ( this.ratingValue == ratingValue ) {
			return true;
		}

		if ( this.ratingValue != null ) {
			// sthg has been selected before. reset icon.
			// ce-cf-ratingX with X = ratingValue +2;
			var oldhtmlid = 'ce-cf-rating' + String(this.ratingValue + 2);
			$(oldhtmlid).src = $(oldhtmlid).src.replace(/active/g, 'inactive');
		}
		switch (ratingValue) {
			case -1 : ratingHTML.src = ratingImg + 'bad_active.png';
				break;
			case 0 : ratingHTML.src = ratingImg + 'neutral_active.png';
				break;
			case 1 : ratingHTML.src = ratingImg + 'good_active.png';
				break;
		}
		this.ratingValue = ratingValue;
	},
}

Event.observe(window, 'load', function() {
	if( typeof( cegUserIsSysop ) != "undefined" && cegUserIsSysop != null && cegUserIsSysop != false) {

		var resultComments = $$('.ce-result-info');

		if ( resultComments != null ) {
			resultComments.each( function( resCom, index ){

				var resComName = resCom.innerHTML;

				var imgEl = new Element('img', {
					'src' : cegScriptPath + '/skins/Comment/icons/smw_plus_delete_icon_16x16.png',
					'style' : 'float:none;padding-left:5px;vertical-align:bottom'
				} );
				var aEl = new Element('a', {
					'rel' : 'nofollow',
					'title' : 'Delete this comment',
					'class' : 'external text',
					'href' : wgServer + wgScript + '/' + escape(resComName) + '?action=delete'
				} );
				var divEl = new Element('div', {
					'style' : 'display:inline',
					'title' : 'Delete this comment',
					'class' : 'plainlinks',
				} );

				aEl.appendChild(imgEl);
				divEl.appendChild(aEl);

				// Firefox considers the whitespace between element nodes
				// to be text nodes (whereas IE does not)
				resComSiblings = resCom.nextSiblings();
				var i = 0;
				do {
					resComSib = resComSiblings[i++];
				} while( resComSib && resComSib.nodeType !== 1 ); // 1 == Node.ELEMENT_NODE
				if( resComSib )
					resComSib.appendChild(divEl);
			});
		}
	}
});

// Singleton of this class

var ceCommentForm = new CECommentForm();