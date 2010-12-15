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

/**
 * The CommentForm "class"
 * 
 */
function CECommentForm() {

	// Variables
	this.numOfComments = 0;
	this.numOfRatings = 0;
	this.averageRating = 0;
	this.textareaIsDefault = true;
	this.ratingValue = null;
	this.internalCall = null;
	this.XMLResult = null;
	this.currentWikiurl = null;
	this.currentWikiPath = null;
	this.currentPageName = null;
	this.currentPageContent = null;
	this.currentUserName = null;
	this.currentUserPassword = null;
	this.currentDomain = null;
	this.overlayName = null;
	this.savedStructure = null;
	this.currentView = null;
	this.replyCommentName = null;
	/* edit mode */
	this.savedCommentContent = null;
	this.editCommentName = null;
	this.editCommentRelatedComment = null;
	this.editMode = false;
	this.editRatingValue = null;

	/**
	 * The processForm function takes care about the html input form
	 * located in the wiki article to enter article comments.
	 * 
	 * It gets the values from all fields and parses them into a string to form a template.
	 * 
	 */
	this.processForm = function() {
		//1. disable form
		$jq('#collabComForm *:input').attr('disabled', 'disabled');

		//2. and add pending indicator
		if ( typeof(this.pendingIndicatorCF) === 'undefined' || this.pendingIndicatorCF === null ) {
			this.pendingIndicatorCF = new CPendingIndicator($jq('#collabComFormTextarea'));
		}
		this.pendingIndicatorCF.show();

		/* form params */
		var now = new Date();
		var pageName = wgPageName + '_' + now.getTime();
		
		//rating
		var ratingString = '';
		if ( this.ratingValue !== null ) {
			ratingString = '|CommentRating=' + this.ratingValue;
		}

		// textarea
		var textArea = ($jq('#collabComFormTextarea').val())? $jq('#collabComFormTextarea').val(): '';
		if( textArea.length === 0 || this.textareaIsDefault ) {
			this.pendingIndicatorCF.hide();
			$jq('#collabComFormMessage').attr('class', 'failure');
			$jq('#collabComFormMessage').html(ceLanguage.getMessage('ce_invalid'));
			$jq('#collabComFormMessage').show('slow');
			// enable form again
			$jq('#collabComForm *:input').removeAttr('disabled');
			return false;
		} else {
			// hide possibly shown message div
			$jq('#collabComFormMessage').hide('slow');
		}
		// escape html chars
		textArea = textArea.replace(/&/g,'&amp;');
		textArea = textArea.replace(/</g,'&lt;');
		textArea = textArea.replace(/>/g,'&gt;');
		textArea = this.textEncode(textArea);
		var userNameString = '';
		if( wgUserName !== null && wgCEUserNS !== null ) {
			userNameString = '|CommentPerson=' + wgCEUserNS + ':' + wgUserName;
		} else {
			userNameString = '|CommentPerson=';
		}

		var relatedCommentString = '';
		if( this.replyCommentName !== null ) {
			relatedCommentString = '|CommentRelatedComment=' + escape(this.replyCommentName);
		}

		var pageContent = '{{Comment' +
			userNameString +
			'|CommentRelatedArticle=' + wgPageName +
			ratingString  +
			relatedCommentString +
			'|CommentDatetime=##DATE##' +
			'|CommentContent=' + textArea + '|}}';

		this.currentPageName = escape(pageName);
		this.currentPageContent = escape(pageContent);

		sajax_do_call('cef_comment_createNewPage',
			[this.currentPageName, this.currentPageContent],
			this.processFormCallback.bindToFunction(this)
		);
		return false;
	};

	/**
	 * The callback function for createNewPage
	 * @param request
	 */
	this.processFormCallback = function(request){
		var resultDOM = this.XMLResult = CollaborationXMLTools.createDocumentFromString(request.responseText);	
		var valueEl = resultDOM.getElementsByTagName('value')[0];
		var htmlmsg = resultDOM.getElementsByTagName('message')[0].firstChild.nodeValue;

		this.pendingIndicatorCF.hide();
		$jq('#collabComForm').get(0).reset();
		$jq('#collabComForm').hide();
		$jq('#collabComForm *:input').removeAttr('disabled');
		var comMessage = $jq('#collabComFormMessage');
		comMessage.show();
		if ( valueEl.nodeType === 1 ) {
			var valueCode = valueEl.firstChild.nodeValue;
			if ( valueCode === '0' ){
				//fine.
				comMessage.attr('class', 'success');
				comMessage.html(htmlmsg + ceLanguage.getMessage('ce_reload'));
				//add pending span
				var pendingSpan = this.createDOMElement('span', 'collabComFormPending');
				comMessage.append(pendingSpan);
				if (typeof(this.pendingIndicatorMsg) === 'undefined' || this.pendingIndicatorMsg === null) {
					this.pendingIndicatorMsg = new CPendingIndicator($jq('#collabComFormPending'));
				}
				this.pendingIndicatorMsg.show();
				//to do a page reload with action=purge
				var winSearch = window.location.search; 
				if ( winSearch.indexOf('action=purge') != -1 ) {
					window.location.reload();
				} else {
					if ( winSearch.indexOf('?') != -1 ) {
						window.location.href = window.location.href.concat('&action=purge');
					} else {
						window.location.href = window.location.href.concat('?action=purge');
					}
				}
				return true;
			} else if ( valueCode === '1' || valueCode === '2' ) {
				//error, article already exists or permisson denied.
				this.pendingIndicatorCF.hide();
				$jq('#collabComFormMessage').attr('class', 'failure');
				comMessage.html(htmlmsg);
			}
		}
		return false;
	};

	/**
	 * This function deletes a single comment page.
	 * @param pageName
	 * @param container
	 */
	this.deleteComment = function(pageName, container) {
		this.overlayName = container;
		// add pending indicator
		var comEl = $jq('#' + container);
		$jq('.ceOverlayDetails', comEl).html(ceLanguage.getMessage('ce_deleting'));
		var pendingSpan = this.createDOMElement('span', 'collabComDelPending');
		$jq('.ceOverlayDetails', comEl).append(pendingSpan);
		if (typeof(this.pendingIndicatorDel) === 'undefined' || this.pendingIndicatorDel === null) {
			this.pendingIndicatorDel = new CPendingIndicator($jq('#collabComDelPending'));
		}
		this.pendingIndicatorDel.show();
		sajax_do_call('cef_comment_deleteComment', 
			[pageName], this.deleteCommentCallback.bindToFunction(this)
		);
	};

	/**
	 * The callback function for deleteComment.
	 */
	this.deleteCommentCallback = function(request) {
		var resultDOM = this.XMLResult = CollaborationXMLTools.createDocumentFromString(request.responseText);	
		var valueEl = resultDOM.getElementsByTagName('value')[0];
		var htmlmsg = resultDOM.getElementsByTagName('message')[0].firstChild.nodeValue;

		var page = resultDOM.getElementsByTagName('article')[0].firstChild.nodeValue;
		var divId = '#' + page;
		var comEditMessage = this.createDOMElement('div', 'collabComEditFormMessage');
		$jq(divId).before($jq(comEditMessage));
		if ( valueEl.nodeType === 1 ) {
			var valueCode = valueEl.firstChild.nodeValue;
			this.pendingIndicatorDel.hide();
			if ( valueCode === '0' ){
				//fine.
				htmlmsg += ceLanguage.getMessage('ce_reload');
				// close overlay -> just click the button
				$jq('#' + this.overlayName).find('.close').click();
				$jq(comEditMessage).addClass('success');
				$jq(comEditMessage).html(htmlmsg);
				var pendingSpan = this.createDOMElement('span', 'collabComDelPending');
				$jq(comEditMessage).append(pendingSpan);
				if (typeof(this.pendingIndicatorDel2) === 'undefined' || this.pendingIndicatorDel2 === null) {
					this.pendingIndicatorDel2 = new CPendingIndicator($jq('#collabComDelPending'));
				}
				this.pendingIndicatorDel2.show();
				// do a page reload with action=purge
				var winSearch = window.location.search; 
				if ( winSearch.indexOf('action=purge') != -1 ) {
					window.location.reload();
				} else {
					if ( winSearch.indexOf('?') != -1 ) {
						window.location.href = window.location.href.concat('&action=purge');
					} else {
						window.location.href = window.location.href.concat('?action=purge');
					}
				}
				return true;
			} else if ( valueCode === '1' || valueCode === '2' ) {
				// error, article already exists or permisson denied.
				$jq(comEditMessage).addClass('failure');
				$jq(comEditMessage).html(htmlmsg);
				return false;
			} else {
				// sthg's really gone wrong
				return false;
			}
		}
		return false;
	};

	/**
	 * "edit comment" was clicked. Provide the comment form.
	 */
	this.editCommentForm = function(pageName) {
		var ratingDiv, ratingExistent, editRatingValue;
		this.editCommentName = pageName;
		this.editCommentRelatedComment = $jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResInfoSuper').html();
		if (this.editMode) {
			//already editing. cancel first!
			return false;
		}
		this.editMode = true;

		// create a new form and set all values
		var container = $jq('#' + pageName.replace(/(:|\.)/g,'\\$1'));
		var content = $jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResText').html();
		container.css('background-color', '#F2F2F2');
		this.savedCommentContent = content;
		content = this.textDecode(content);
		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResText').toggle();
		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResRight').css(
			'width', '85%'
		);
		// rating only if rating is enabled
		if( typeof(wgCEEnableRating) !== 'undefined' ) {
			var ratingIconSrc = $jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResRatingIcon img').attr('src');
			if(ratingIconSrc) {
				if (ratingIconSrc.match('Bad')) {
					editRatingValue = -1;
					ratingExistent = true;
				}
				if(ratingIconSrc.match('Neutral')) {
					editRatingValue = 0;
					ratingExistent = true;
				}
				if(ratingIconSrc.match('Good')) {
					editRatingValue = 1;
					ratingExistent = true;
				}
			}
			// rating things
			var ratingText = document.createTextNode(ceLanguage.getMessage('ce_edit_rating_text'));
			var ratingTextOpt = document.createTextNode(ceLanguage.getMessage('ce_edit_rating_text2'));
			var ratingTextOpt2 = document.createTextNode(':');
			var ratingSpan = this.createDOMElement('span', null, ['collabComFormGrey']);
			$jq(ratingSpan).append($jq(ratingTextOpt));

			ratingDiv = this.createDOMElement('div', 'collabComEditFormRating');
			$jq(ratingDiv).append($jq(ratingText));
			$jq(ratingDiv).append($jq(ratingSpan));
			$jq(ratingDiv).append($jq(ratingTextOpt2));

			var ratingIcons = this.createDOMElement('span', 'collabComEditFormRadionButtons');

			var ratingIcon1 = this.createDOMElement('img',
					'collabComEditFormRating1',
					['collabComEditFormRatingImg'],
					[['src', wgCEScriptPath + '/skins/Comment/icons/bad_inactive.png']]);
			$jq(ratingIcon1).bind('click', function() {
				ceCommentForm.switchEditRating('#collabComEditFormRating1',-1);
			});

			var ratingIcon2 = this.createDOMElement('img',
					'collabComEditFormRating2',
					['collabComEditFormRatingImg'],
					[['src', wgCEScriptPath + '/skins/Comment/icons/neutral_inactive.png']]);
			$jq(ratingIcon2).bind('click', function() {
				ceCommentForm.switchEditRating('#collabComEditFormRating2', 0);
			});

			var ratingIcon3 = this.createDOMElement('img',
					'collabComEditFormRating3',
					['collabComEditFormRatingImg'],
					[['src', wgCEScriptPath + '/skins/Comment/icons/good_inactive.png']]);
			$jq(ratingIcon3).bind('click', function() {
				ceCommentForm.switchEditRating('#collabComEditFormRating3', 1);
			});

			$jq(ratingIcons).append(ratingIcon1);
			$jq(ratingIcons).append(ratingIcon2);
			$jq(ratingIcons).append(ratingIcon3);
			$jq(ratingDiv).append($jq(ratingIcons));
		} // end rating

		// textarea
		var textarea = this.createDOMElement('textarea',
				'collabComEditFormTextarea',
				null, [['rows', '5']], null, content);

		//buttons
		var submitButton = this.createDOMElement('input',
				'collabComEditFormSubmit', null, [['type', 'button']],
				null,  ceLanguage.getMessage('ce_edit_button'));
		$jq(submitButton).bind('click', function() {
			ceCommentForm.editExistingComment();
		});

		var cancelSpan = this.createDOMElement('span', 'collabComEditFormCancel');
		$jq(cancelSpan).bind('click', function() {
			ceCommentForm.cancelCommentEditForm(pageName);
		});
		var cancelText = document.createTextNode(' | ' + ceLanguage.getMessage('ce_cancel_button'));
		$jq(cancelSpan).append($jq(cancelText));

		// message div
		var msgDiv = this.createDOMElement('div', 'collabComEditFormMessage');
		$jq(msgDiv).css('display', 'none');

		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResText').html('');
		if( typeof(ratingDiv) !== 'undefined' && ratingDiv !== null ) {
			$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResText').append(ratingDiv);
		}
		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResText').append(textarea);
		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResText').append(submitButton);
		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResText').append(cancelSpan);
		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResText').append(msgDiv);

		if(ratingExistent) {
			ceCommentForm.switchEditRating('#collabComEditFormRating' + (parseInt(editRatingValue) + 2), editRatingValue);
		}
		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResText').toggle();
		$jq('.collabComReply', $jq('#' + pageName.replace(/(:|\.)/g,'\\$1'))).hide();
		return true;
	};
	
	/**
	 * "edit comment" was clicked. Provide the comment form.
	 */
	this.editExistingComment = function() {
		//1. disable form tools
		$jq('#' + this.editCommentName.replace(/(:|\.)/g,'\\$1') + ' *:input').attr('disabled', 'disabled');

		//2. and add pending indicator
		if (typeof(this.pendingIndicatorEF) === 'undefined' || this.pendingIndicatorEF === null) {
			this.pendingIndicatorEF = new CPendingIndicator($jq('#collabComEditFormTextarea'));
		}
		this.pendingIndicatorEF.show();

		/* form params */
		//rating
		var ratingString = '';
		if ( this.editRatingValue !== null) {
			ratingString = '|CommentRating=' + this.editRatingValue;
		}

		// textarea
		var textArea = ($jq('#collabComEditFormTextarea').val())? $jq('#collabComEditFormTextarea').val(): '';
		// escape html chars
		textArea.replace(/&/g,'&amp;');
		textArea.replace(/</g,'&lt;');
		textArea.replace(/>/g,'&gt;');
		textArea = this.textEncode(textArea);
		// change the comment person?
		var commentPerson= $jq('.collabComResUsername > a', $jq('#' + this.editCommentName.replace(/(:|\.)/g,'\\$1'))).html();
		if(!commentPerson) {
			commentPerson = '';
		} else {
			commentPerson = commentPerson.split(':');
			commentPerson = commentPerson.pop();
			commentPerson = '|CommentPerson=' + wgCEUserNS + ':' + commentPerson;
		}
		var relatedComment = '';
		if(this.editCommentRelatedComment !== null && this.editCommentRelatedComment !== '') {
			relatedComment = '|CommentRelatedComment=' + this.editCommentRelatedComment;
		}
		var pageContent = '{{Comment' +
			commentPerson +
			'|CommentRelatedArticle=' + wgPageName +
			ratingString  +
			'|CommentDatetime=##DATE##'+
			'|CommentContent=' + textArea + 
			relatedComment + '|}}';
		this.currentPageName = escape(this.editCommentName);
		this.currentPageContent = escape(pageContent);
		//do ajax call
		sajax_do_call('cef_comment_editPage', 
			[this.currentPageName, this.currentPageContent],
			this.editExistingCommentCallback.bindToFunction(this)
		);
		return true;
	};

	/**
	 * 
	 * @param: request
	 */
	this.editExistingCommentCallback = function(request) {
		var resultDOM = this.XMLResult = CollaborationXMLTools.createDocumentFromString(request.responseText);	
		var valueEl = resultDOM.getElementsByTagName('value')[0];
		var htmlmsg = resultDOM.getElementsByTagName('message')[0].firstChild.nodeValue;

		this.pendingIndicatorEF.hide();
		if ( valueEl.nodeType === 1 ) {
			var valueCode = valueEl.firstChild.nodeValue;
			var comEditMessage = this.createDOMElement('div', 'collabComEditFormMessage');
			$jq('#' + this.editCommentName.replace(/(:|\.)/g,'\\$1')).before($jq(comEditMessage));
			if ( valueCode === '0' ){
				//fine.
				//reset, hide and enable form again
				$jq(comEditMessage).show();
				$jq(comEditMessage).addClass('success');
				$jq(comEditMessage).html(htmlmsg + ceLanguage.getMessage('ce_reload'));
				//add pending span
				var pendingSpan = this.createDOMElement('span', 'collabComEditFormPending');
				$jq(comEditMessage).append(pendingSpan);
				if (typeof(this.pendingIndicatorMsg) === 'undefined' || this.pendingIndicatorMsg === null) {
					this.pendingIndicatorMsg = new CPendingIndicator($jq('#collabComEditFormPending'));
				}
				this.pendingIndicatorMsg.show();
				// do a page reload with action=purge
				var winSearch = window.location.search; 
				if ( winSearch.indexOf('action=purge') != -1 ) {
					window.location.reload();
				} else {
					if ( winSearch.indexOf('?') != -1 ) {
						window.location.href = window.location.href.concat('&action=purge');
					} else {
						window.location.href = window.location.href.concat('?action=purge');
					}
				}
				return true;
			} else if ( valueCode === '1' || valueCode === '2' ) {
				//error, article already exists or permisson denied.
				$jq(comEditMessage).addClass('failure');
				$jq(comEditMessage).html(htmlmsg);
				$jq('#' + this.editCommentName.replace(/(:|\.)/g,'\\$1') + ' *:input').removeAttr('disabled');
			}
		}
		return false;
	};

	/**
	 * 
	 */
	this.cancelCommentEditForm = function(pageName) {
		this.editMode = false;
		this.editCommentName = null;
		this.editCommentRelatedComment = null;
		this.editRatingValue = null;
		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1')).css('background-color', '');
		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResText').toggle();
		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResText').html(this.savedCommentContent);
		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResText').toggle();
		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResRight').css(
				'width', ''
		);
		$jq('.collabComReply', $jq('#' + pageName.replace(/(:|\.)/g,'\\$1'))).show();
		return true;
	};

	/**
	 * 
	 */
	this.replyCommentForm = function(pageName) {
		this.replyCommentName = ceLanguage.getMessage('COMMENT_NS') + pageName;
		var container = $jq('#' + pageName.replace(/(:|\.)/g,'\\$1'));
		var commentForm = $jq('#collabComForm');
		$jq('#collabComFormResetbuttonID').bind('click', function(){
			commentForm.hide();
			commentForm.css( 'marginLeft', '');
			($jq('#collabComFormHeader')).append(commentForm);
		});
		var resMargin = container.css('margin-left');
		if ( typeof( resMargin ) != 'undefined' ) {
			var newMargin = (parseInt(resMargin) + 30);
			commentForm.css( 'marginLeft', newMargin);
		} else {
			commentForm.css('marginLeft', "30px");
		}
		container.after(commentForm);
		commentForm.show();

	};

	/*helper functions*/

	/**
	 * Function to do all necessary encodings
	 * to make sure that comments are displayed 
	 * excactly the same as in the form
	 */
	this.textEncode = function(text) {
		// property & template cleaning:
		text = text.replace(/:/g, '&#58;');
		text = text.replace(/\{/g, '&#123;');
		text = text.replace(/\{/g, '&#123;');
		text = text.replace(/\[/g, '&#91;');
		text = text.replace(/\]/g, '&#93;');
		text = text.replace(/\//g, '&#47;');
		text = text.replace(/\\/g, '&#92;');
		text = text.replace(/(\r\n|\r|\n)/g, '<br />');
		//replace the leading whitespace with html entity for every line
		var textLines = text.split('<br />');
		for( var i=0; i <= textLines.length-1; i++ ){
			textLines[i] = textLines[i].replace(/^\s/,'&nbsp;');
		}
		text = textLines.join('<br />');
		return text;
	};

	/**
	 * Function to decode again.
	 */
	this.textDecode = function(text) {
		text = text.replace(/<br\/>|<br \/>|<br>/g, '\n');
		// property & template cleaning:
		text = text.replace(/&nbsp;/g, ' ');
		text = text.replace(/&#58;/g, ':');
		text = text.replace(/&#123;/g,'}');
		text = text.replace(/&#123;/g, '{');
		text = text.replace(/&#91;/g, '[');
		text = text.replace(/&#93;/g, ']');
		text = text.replace(/&#47;/g, '/');
		text = text.replace(/&#92;/g, '\\');
		return text;
	};

	/**
	 * 
	 */
	this.formReset = function() {
		this.textareaIsDefault = true;
		this.replyCommentName = null;
		if (this.ratingValue !== null) {
			var oldhtmlid = '#collabComFormRating' + String(this.ratingValue + 2);
			$jq(oldhtmlid).attr('src', $jq(oldhtmlid).attr('src').replace(/_active/g, '_inactive'));
			this.ratingValue = null;
		}
		$jq('#collabComForm').get(0).reset();
		$jq('#collabComForm').toggle('slow');
	};

	/**
	 * onClick event function for textarea
	 */
	this.selectTextarea = function() {
		//check if we still have the form default in here
		if (this.textareaIsDefault) {
			$jq('#collabComFormTextarea').select();
		}
	};

	/**
	 * Disable the onClick function for textarea
	 */
	this.textareaKeyPressed = function() {
		this.textareaIsDefault = false;
	};

	/**
	 * switch for rating
	 */
	this.switchRating = function( htmlid, ratingValue ) {
		var ratingHTML = $jq(htmlid);
		var ratingImg = wgCEScriptPath + '/skins/Comment/icons/';
		var oldhtmlid = '#collabComFormRating' + String(this.ratingValue + 2);
		$jq(oldhtmlid).attr('src', $jq(oldhtmlid).attr('src').replace(/_active/g, '_inactive'));
		if ( this.ratingValue == ratingValue ) {
			// deselect...
			this.ratingValue = null;
			return true;
		}
		switch (ratingValue) {
			case -1 : ratingHTML.attr('src', ratingImg + 'bad_active.png');
				break;
			case 0 : ratingHTML.attr('src', ratingImg + 'neutral_active.png');
				break;
			case 1 : ratingHTML.attr('src', ratingImg + 'good_active.png');
				break;
		}
		this.ratingValue = ratingValue;
	};

	/**
	 * switch for rating (in edit mode)
	 */
	this.switchEditRating = function( htmlid, ratingValue ) {
		var ratingHTML = $jq(htmlid);
		var ratingImg = wgCEScriptPath + '/skins/Comment/icons/';
		var oldhtmlid = '#collabComEditFormRating' + String(this.editRatingValue + 2);
		$jq(oldhtmlid).attr('src', $jq(oldhtmlid).attr('src').replace(/_active/g, '_inactive'));
		if ( this.editRatingValue == ratingValue ) {
			// deselect...
			this.editRatingValue = null;
			return true;
		}
		switch (ratingValue) {
			case -1 : ratingHTML.attr('src', ratingImg + 'bad_active.png');
				break;
			case 0 : ratingHTML.attr('src', ratingImg + 'neutral_active.png');
				break;
			case 1 : ratingHTML.attr('src', ratingImg + 'good_active.png');
				break;
		}
		this.editRatingValue = ratingValue;
		return true;
	};

	/**
	 * This functions toggles the comments and 
	 * sets the corresponding text in the comment header
	 */
	this.toggleComments = function() {
		var comToggle = $jq('#collabComToggle');
		var commentResults = $jq('#collabComResults');
		var newComToggleText = '';
		if( commentResults.css('display') === 'block' ) {
			newComToggleText = ceLanguage.getMessage('ce_com_show');
		} else {
			newComToggleText = ceLanguage.getMessage('ce_com_hide');
		}
		comToggle.html(' | ' + newComToggleText);
		commentResults.toggle("slow");
		//hide "Add" and "View"
		$jq('#collabComFormToggle').toggle();
		$jq('#collabComViewToggle').toggle();
		return true;
	};

	/**
	 * fired when the "View" select box has changed
	 * Determines which view is requested and calls the appropriate function
	 */
	this.toggleView = function() {
		var newView = parseInt($jq('#collabComViewToggle option:selected').val());
		if(newView === this.currentView) {
			return true;
		} else {
			if(newView === 0) {
				this.showThreaded();
			} else if (newView === 1) {
				this.showFlat();
			}
		}
	};

	/**
	 * Provides functionality to display the comments in flat mode
	 * without indention. This is the normal behaviour of the template result printer
	 * so we can use the cloned and stored version and just rebind the events.
	 */
	this.showFlat = function() {
		$jq('#collabComResults').html($jq(this.savedStructure.html()));
		// rebind events
		var resultComments = $jq('.collabComRes');
		$jq.each(resultComments, function(i, resCom ){
			var resComInfo = $jq('.collabComResInfo', resCom);
			// name of actual comment
			var resComName = resComInfo.html();
			// deletion
			$jq('.collabComDel', resCom).bind('click', function() {
				$jq('#' + resComName.replace(/(:|\.)/g,'\\$1')).css('background-color', '#FAFAD2');
			});
			// edit
			$jq('.collabComEdit', resCom).bind('click', function() {
				ceCommentForm.editCommentForm(resComName);
			});
			// reply
			$jq('.collabComReply', resCom).bind('click', function() {
				ceCommentForm.replyCommentForm(resComName);
			});
		});
		// overlays
		$jq("div[rel]").overlay({
			api: true,
			// when overlay is closed, remove color highlighting
			onClose: function() {
				$jq('.collabComRes').removeClass('collabComDelSelected');
			}
		});
		this.currentView = 1;
		return true;
	};

	/**
	 * Provides functionality to display comments in threaded mode.
	 * Every comment that is a reply to another comment is indented and inserted
	 * at the specific position.
	 */
	this.showThreaded = function() {
		// format comments
		$jq('.collabComRes').each( function(i, resCom ){
			var resComInfo = $jq('.collabComResInfo', resCom);
			var superComInfo = $jq('.collabComResInfoSuper', resCom);
			// name of the comment, the actual comment is related to (if there's one)
			var superComName = superComInfo.html();
			if(typeof( superComName ) !== 'undefined' && 
					superComName !== null && superComName !== false && superComName !== '') {
				var resMargin = $jq('#' + superComName.replace(/(:|\.)/g, '\\$1')).css('margin-left');
				var newMargin = "30";
				if ( typeof( resMargin ) !== 'undefined' ) {
					newMargin = (parseInt(resMargin) + 30);
				}
				// check if there are "child" comments
				var name = ceCommentForm.getLastChildComment(superComName);
				if(name !== superComName) {
					// child found. add behind.
					$jq('#' + name.replace(/(:|\.)/g, '\\$1')).after($jq(resCom));
				} else {
					// no child found
					$jq('#' + superComName.replace(/(:|\.)/g, '\\$1')).after($jq(resCom));
				}
				$jq(resCom).css('margin-left', newMargin + "px");
				$jq(resCom).addClass('comRearranged');
			}
		});
		this.currentView = 0;
		return true;
	};

	/**
	 * This function returns the "last child" of a given comment.
	 * This is not actually a real child because all comments are siblings.
	 * It's more like the deepest related comment.
	 */
	this.getLastChildComment = function(commentName) {
		var childComments = $jq('.comRearranged').filter(function(index) {
			var indSuperComName = $jq('.collabComResInfoSuper',this);
			return indSuperComName.html() == commentName;
		});
		if(childComments.length > 0){
			lastChildComment = childComments[childComments.length-1];
			return this.getLastChildComment($jq(lastChildComment).attr('id'));
		} else {
			return commentName;
		}
	};
	
	/**
	 * This function determines the number of comments, number of ratings 
	 * and the average rating value and stores them in object variables.
	 */
	this.setCommentQuantities = function() {
		this.numOfComments = $jq('.collabComRes').length;
		this.numOfRatings = $jq('.collabComResRatingIcon').length;
		if( this.numOfRatings === 0) {
			return true;
		}
		var avgRating = 0;
		$jq('.collabComResRatingIcon').each( function(){
			if( $jq('img', this).attr('src').indexOf("Bad") >=0 ) {
				avgRating--;
			} else if ($jq('img', this).attr('src').indexOf("Good") >=0 ) {
				avgRating++;
			}
		});
		this.averageRating = avgRating / this.numOfRatings;
		return true;
	};

	/**
	 * Builds the header for handling comments 
	 */
	this.buildHeader = function() {
		//var comHeader = $jq('.collabComInternHeader');
		this.setCommentQuantities();
		var expandedHead = this.addHeaderText();
		if(expandedHead === true) {
			this.addCommentToggler();
			if( typeof(wgCECommentsDisabled) === 'undefined' || wgCECommentsDisabled === false ) {
				this.addFormToggler(true); //remove header
			}
			this.addHeaderView();
			this.addHeaderRating();
		}
		return true;
	};
	
	/**
	 * Wrapper function for the header
	 */
	this.addHeaderText = function() {
		if(this.numOfComments > 0) {
			// set expanded header
			return this.addExtendedHeaderText();
		} else {
			// set default header
			return this.addDefaultHeaderText();
		}
	};

	/**
	 * Adds the default header text and makes it bold.
	 * This text is normally sthg like "Add comment"
	 * @returns false
	 */
	this.addDefaultHeaderText = function() {
		this.addFormToggler(false);
		$jq('#collabComFormToggle').css('font-weight','bold');
		return false;
	};

	/**
	 * The extended header text also contains the number of comments.
	 * Sthg like "Comments (2)"
	 * @returns true
	 */
	this.addExtendedHeaderText = function() {
		var headerText = ceLanguage.getMessage('ce_com_ext_header');
		headerText = headerText + ' (' + this.numOfComments + ')';
		var headerSpan = this.createDOMElement('span',
				null, ['collabComInternComment'], null, headerText);
		$jq('.collabComInternHeader').append($jq(headerSpan));
		return true;
	};

	/**
	 * This function adds the toggle element for the comments.
	 */
	this.addCommentToggler = function() {
		var toggleSpan = this.createDOMElement('span', 
				'collabComToggle', 
				null,
				[['title', ceLanguage.getMessage('ce_com_toggle_tooltip')]],
				' | ' + ceLanguage.getMessage('ce_com_hide'));
		$jq(toggleSpan).bind('click', function() {
			ceCommentForm.toggleComments();
		});
		$jq('.collabComInternHeader').append($jq(toggleSpan));
		return true;
	};

	/**
	 * This function adds the toggle element for the comment form.
	 * @param withPipe: Indicates if the Text should be extended with a leading pipe symbol
	 */
	this.addFormToggler = function(withPipe) {
		var toggleSpan = this.createDOMElement('span',
				'collabComFormToggle',
				null,
				[['id', 'collabComFormToggle']],
				(withPipe? ' | ' : ' ') + ceLanguage.getMessage('ce_com_default_header'));
		$jq(toggleSpan).bind('click', function() {
			$jq('#collabComForm').toggle('slow');
		});
		$jq('.collabComInternHeader').append($jq(toggleSpan));
		return true;
	};

	/**
	 * Creates the "change view" select box element.
	 */
	this.addHeaderView = function() {
		// "change view" functionality
		var viewSpan = this.createDOMElement('span',
				'collabComViewToggle',
				null,
				null,
				' | ' + ceLanguage.getMessage('ce_com_view') + ': ');

		var selectEl = this.createDOMElement('select');
		$jq(selectEl).bind('change', function() {
			ceCommentForm.toggleView();
		});
		try {
			selectEl.add(new Option(ceLanguage.getMessage('ce_com_view_threaded'), 0, true, true), null); // standards compliant; doesn't work in IE
		}
		catch(ex) {
			selectEl.add(new Option(ceLanguage.getMessage('ce_com_view_threaded'), 0, true, true)); // IE only
		}
		try {
			selectEl.add(new Option(ceLanguage.getMessage('ce_com_view_flat'), 1), null); // standards compliant; doesn't work in IE
		}
		catch(ex) {
			selectEl.add(new Option(ceLanguage.getMessage('ce_com_view_flat'), 1)); // IE only
		}
		$jq(viewSpan).append($jq(selectEl));
		$jq('.collabComInternHeader').append($jq(viewSpan));
		this.currentView = 0;
		return true;
	};

	/**
	 * Adds the text and icon for the average rating.
	 */
	this.addHeaderRating = function() {
		if( this.numOfRatings > 0 ) {
			var ratingSpan = this.createDOMElement('span',
					null,
					['collabComInternAvg'],
					null,
					ceLanguage.getMessage('ce_com_rating_text') + ' ' + 
					this.numOfRatings + ' ' + ceLanguage.getMessage('ce_com_rating_text2'));

			var ratingIconDiv = this.createDOMElement('div', null, ['collabComInternRatingIcon']);
			
			var ratingIcon = this.createDOMElement('img');
			var ratingIconSrc = wgCEScriptPath + '/skins/Comment/icons/';
			if(this.averageRating < -0.33) {
				$jq(ratingIcon).attr('src', ratingIconSrc + 'bad_active.png');
			} else if(this.averageRating >= -0.33 && this.averageRating <= 0.33 ) {
				$jq(ratingIcon).attr('src', ratingIconSrc + 'neutral_active.png');
			} else if(this.averageRating > 0.33) {
				$jq(ratingIcon).attr('src', ratingIconSrc + 'good_active.png');
			}
			$jq(ratingIconDiv).append($jq(ratingIcon));
			$jq(ratingSpan).append($jq(ratingIconDiv));
			$jq('.collabComInternHeader').append($jq(ratingSpan));
		}
		return true;
	};

	/**
	 * Creates the complete overlay structure and returns it.
	 */
	this.createOverlay = function(num, pageName) {
		var overlayName = 'overlay_' + num;
		// divs
		var overlayDivEl = this.createDOMElement('div', overlayName, ['ceOverlay']);
		var overlayDivDetailsEl = this.createDOMElement('div', null, ['ceOverlayDetails']);
		$jq(overlayDivEl).append($jq(overlayDivDetailsEl));
		var overlayDivContent = document.createTextNode(ceLanguage.getMessage('ce_delete'));
		$jq(overlayDivDetailsEl).append($jq(overlayDivContent));

		// cancel button
		var cancelButtonDiv = this.createDOMElement('div', null, ['ceOverlayCancelButtonDiv']);
		$jq(cancelButtonDiv).bind('click', function() {
			$jq('#' + pageName.replace(/(:|\.)/g,'\\\\$1')).css('background-color', '');
		});
		var cancelButton = this.createDOMElement('input',
				null,
				['ceOverlayCancelButton', 'close'],
				[['type', 'button']],
				null,
				ceLanguage.getMessage('ce_cancel_button'));
		$jq(cancelButtonDiv).append($jq(cancelButton));
		$jq(overlayDivEl).append($jq(cancelButtonDiv));

		// delete button
		var deleteButtonDiv = this.createDOMElement('div',
				null, ['ceOverlayDeleteButtonDiv']);
		$jq(deleteButtonDiv).bind('click', function() {
			ceCommentForm.deleteComment(escape(pageName), overlayName);
		});
		var deleteButton = this.createDOMElement('input',
				null,
				['ceOverlayDeleteButton'],
				[['type', 'button']],
				null,
				ceLanguage.getMessage('ce_delete_button'));
		$jq(deleteButtonDiv).append($jq(deleteButton));
		$jq(overlayDivEl).append($jq(deleteButtonDiv));
		return overlayDivEl;
	};

	/**
	 * MW 1.16.x comes with jQuery version 1.3.
	 * This framework is missing the fancy way
	 * of adding new DOM elements as described in 
	 * http://api.jquery.com/jQuery/#jQuery2.
	 * So this function takes care about this.
	 * 
	 * @param tag string
	 * @param id string
	 * @param classes Array
	 * @param attribs multi-dim Array
	 * @param htmlcontent string
	 * @param value string
	 * 
	 * @return DOM Element or false if no tag has been passed.
	 **/
	this.createDOMElement = function(tag, id, classes, attribs, htmlcontent, value) {
		if( tag === null || typeof( tag ) !== 'string' ) {
			return false;
		}
		var el = document.createElement(tag);
		if( id !== null && typeof( id ) === 'string' ) {
			$jq(el).attr('id', id);
		}
		if( classes !== null && typeof( classes ) === 'object' ) {
			$jq(classes).each(function(){
				$jq(el).addClass(this);
			});
		}
		if( attribs !== null && typeof( attribs ) === 'object' ) {
			$jq(attribs).each(function(){
				$jq(el).attr(this[0], this[1]);
			});
		}
		if( htmlcontent !== null && typeof( htmlcontent ) === 'string' ) {
			$jq(el).html(htmlcontent);
		}
		if( value !== null && typeof( value ) === 'string') {
			$jq(el).val(value);
		}
		return el;
	};
}

//Set global variable for accessing comment form functions
var ceCommentForm;

//Initialize Comment functions if page is loaded
$jq(document).ready(
	function(){
		ceCommentForm = new CECommentForm();
	}
);

/**
 * This function takes care about missing event handlers.
 * (It hasn't been possible to add this in the Template itself)
 * It also creates Edit and Delete links for users that own the appropriate right.
 * The current DOM structure is saved to be reused in "flat view".
 */
$jq(document).ready(
	function(){
		// build header
		ceCommentForm.buildHeader();
		// format comments
		var resultComments = $jq('.collabComRes');
		$jq.each(resultComments, function(i, resCom ){
			var resComInfo = $jq('.collabComResInfo', resCom);
			// name of actual comment
			var resComName = resComInfo.html();
			var resComDeleted = $jq('.collabComResDeletion', resCom);
			if( resComDeleted.html() === 'true' ) {
				$jq('.collabComResText', resCom).addClass('collabComDeleted');
				// this comment has been marked as deleted -> step out
				return true;
			}
			var commentPerson= $jq('.collabComResUsername > a', resCom).html();
			if(!commentPerson) {
				commentPerson = '';
			} else {
				commentPerson = commentPerson.split(':');
				commentPerson = commentPerson.pop();
			}
			if( (typeof( wgCEUserIsSysop ) !== 'undefined' && wgCEUserIsSysop !== null &&
					wgCEUserIsSysop !== false) || (wgUserName !== null && commentPerson == wgUserName) ) {
				//Overlay for deleting comments
				var overlayDiv = ceCommentForm.createOverlay(i, resComName);
				var divEl = ceCommentForm.createDOMElement('div',
						'ceDel' + escape(resComName),
						['collabComDel'],
						[['title', ceLanguage.getMessage('ce_delete_title')],['rel', '#overlay_' + i]]);
				$jq(divEl).bind('click', function() {
					$jq('#' + resComName.replace(/(:|\.)/g,'\\$1')).addClass('collabComDelSelected');
				});
				var delImgEl = ceCommentForm.createDOMElement('img',
						null,
						['collabComDeleteImg'],
						[['src', wgCEScriptPath + '/skins/Comment/icons/Delete_button.png']]);
				$jq(divEl).append($jq(delImgEl));
				$jq('.collabComResDate', resCom).after(divEl);
				$jq('#collabComResults').after(overlayDiv);

				if( typeof(wgCECommentsDisabled) === 'undefined' || wgCECommentsDisabled === false ) {
					// edit
					var divEl = ceCommentForm.createDOMElement('div',
							null,
							['collabComEdit'],
							[['title', ceLanguage.getMessage('ce_edit_title')]]);
					$jq(divEl).bind('click', function() {
						ceCommentForm.editCommentForm(resComName);
					});
					var imgEl = ceCommentForm.createDOMElement('img',
							null,
							['collabComEditImg'],
							[['src', wgCEScriptPath + '/skins/Comment/icons/Edit_button2.png']]);
					$jq(divEl).append($jq(imgEl));
					$jq('.collabComResDate', resCom).after(divEl);
				}
			}
			if( typeof(wgCECommentsDisabled) === 'undefined' || wgCECommentsDisabled === false ) {
				// reply
				var divEl = ceCommentForm.createDOMElement('span',
						null,
						['collabComReply'],
						[['title', ceLanguage.getMessage('ce_reply_title')]], ceLanguage.getMessage('ce_com_reply'));
				$jq(divEl).bind('click', function() {
					ceCommentForm.replyCommentForm(resComName);
				});
				
				var replyImgEl = ceCommentForm.createDOMElement('img',
						null,
						['collabComReplyImg'],
						[['src', wgCEScriptPath + '/skins/Comment/icons/Reply_Comment.png']]);
				$jq(divEl).append($jq(replyImgEl));
				$jq('.collabComResText', resCom).after(divEl);
			}
		});
		//clone actual structure without events (bind them again later)
		ceCommentForm.savedStructure = $jq('#collabComResults').clone();
		ceCommentForm.showThreaded();
		// toggle one time if there are comments available
		if (resultComments.length !== 0) {
			var comToggle = $jq('#collabComToggle');
			var commentResults = $jq('#collabComResults');
			var newComToggleText = '';
			if( commentResults.css('display') === 'block' ) {
				newComToggleText = ceLanguage.getMessage('ce_com_hide');
			} else {
				newComToggleText = ceLanguage.getMessage('ce_com_show');
			}
			comToggle.html(' | ' + newComToggleText);
			commentResults.hide();
			//hide "Add" and "View"
			$jq('#collabComFormToggle').toggle();
			$jq('#collabComViewToggle').toggle();
		}
	}
);

$jq(document).ready(
	function(){
		// did not work in the same ready function
		$jq("div[rel]").overlay({
			api: true,
			// when overlay is closed, remove color highlighting
			onClose: function() {
				$jq('.collabComRes').removeClass('collabComDelSelected');
			}
		});
	}
);

/**
 * Function binding
 */
Function.prototype.bindToFunction = function(context){
	var func = this;
	return function(){
		return func.apply(context, arguments);
	};
};


/**
 * This class has been ported from the generalTools.js of SMWHalo
 * to remove the dependency.
 */
function CPendingIndicator(container) {
	this.constructor = function(container) {
		this.container = container;
		this.pendingIndicator = document.createElement("img");
		$jq(this.pendingIndicator).addClass("cependingElement");
		$jq(this.pendingIndicator).attr("src", 
			wgServer + wgScriptPath + "/extensions/Collaboration/skins/Comment/icons/ajax-loader.gif");
		this.contentElement = null;
	};

	/**
	 * Shows pending indicator relative to given container or relative to initial container
	 * if container is not specified.
	 */
	this.show = function(container, alignment) {
		//check if the content element is there
		if($jq("#content") === null){
			return;
		}

		var alignOffset = 0;
		if (typeof(alignment) !== 'undefined') {
			switch(alignment) {
				case "right": { 
					if (!container) { 
						alignOffset = $jq(this.container).offsetWidth - 16;
					} else {
						alignOffset = $jq(container).offsetWidth - 16;
					}
					break;
				}
				case "left": break;
			}
		}
			
		//if not already done, append the indicator to the content element so it can become visible
		if(this.contentElement === null) {
				this.contentElement = $jq("#content");
				this.contentElement.append(this.pendingIndicator);
		}
		if (!container) {
			var offSet = this.container.offset();
			this.pendingIndicator.style.left = (alignOffset + offSet.left) + "px";
			this.pendingIndicator.style.top = (offSet.top - $jq(document).scrollTop()) + "px";
		} else {
			var offSet = $jq('#container').offset();
			this.pendingIndicator.style.left = alignOffset + offSet.left + "px";
			this.pendingIndicator.style.top = (offSet.top - $jq(document).scrollTop()) + "px";
		}
		// hmm, why does Element.show(...) not work here?
		this.pendingIndicator.style.display="block";
		this.pendingIndicator.style.visibility="visible";
	};
	
	/**
	 * Shows the pending indicator on the specified <element>. This works also
	 * in popup panels with a defined z-index.
	 */
	this.showOn = function(element) {
		container = element.offsetParent;
		$jq(container).insert({top: this.pendingIndicator});
		var pOff = $jq(element).positionedOffset();
		this.pendingIndicator.style.left = pOff[0]+"px";
		this.pendingIndicator.style.top  = pOff[1]+"px";
		this.pendingIndicator.style.display="block";
		this.pendingIndicator.style.visibility="visible";
		this.pendingIndicator.style.position = "absolute";
	};
	
	this.hide = function() {
		$jq(this.pendingIndicator).hide();
	};

	this.remove = function() {
		$jq(this.pendingIndicator).remove();
	};
	
	// Execute initialize on object creation
	this.constructor(container);
}

/*
 * Browser tools
 */
function CollaborationBrowserDetectLite() {

	var ua = navigator.userAgent.toLowerCase();

	// browser name
	this.isGecko     = (ua.indexOf('gecko') != -1) || (ua.indexOf("safari") != -1); // include Safari in isGecko
	this.isMozilla   = (this.isGecko && ua.indexOf("gecko/") + 14 == ua.length);
	this.isNS        = ( (this.isGecko) ? (ua.indexOf('netscape') != -1) : ( (ua.indexOf('mozilla') != -1) && (ua.indexOf('spoofer') == -1) && (ua.indexOf('compatible') == -1) && (ua.indexOf('opera') == -1) && (ua.indexOf('webtv') == -1) && (ua.indexOf('hotjava') == -1) ) );
	this.isIE        = ( (ua.indexOf("msie") != -1) && (ua.indexOf("opera") == -1) && (ua.indexOf("webtv") == -1) );
	this.isOpera     = (ua.indexOf("opera") != -1);
	this.isSafari    = (ua.indexOf("safari") != -1);
	this.isKonqueror = (ua.indexOf("konqueror") != -1);
	this.isIcab      = (ua.indexOf("icab") != -1);
	this.isAol       = (ua.indexOf("aol") != -1);
	this.isWebtv     = (ua.indexOf("webtv") != -1);
	this.isGeckoOrOpera = this.isGecko || this.isOpera;
	this.isGeckoOrSafari = this.isGecko || this.isSafari;
}
//one global instance of Collaboration Browser detector 
var C_bd = new CollaborationBrowserDetectLite();

/*
 * XML Tools
 */
CollaborationXMLTools = new Object();

/**
 * Creates an XML document from string
 */
CollaborationXMLTools.createDocumentFromString = function (xmlText) {
	// create empty treeview
	if (C_bd.isGeckoOrOpera) {
		var parser=new DOMParser();
		var xmlDoc=parser.parseFromString(xmlText,"text/xml");
	} else if (C_bd.isIE) {
		var xmlDoc = new ActiveXObject("Microsoft.XMLDOM") 
		xmlDoc.async="false"; 
		xmlDoc.loadXML(xmlText);   
	}
	return xmlDoc;
}
