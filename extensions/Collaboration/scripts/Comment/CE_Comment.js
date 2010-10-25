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
		var cf = $jq('#collabComForm');
		$jq('#collabComFormTextarea').attr('disabled', 'disabled');
		$jq('#collabComFormSubmitbuttonID').attr('disabled', 'disabled');
		$jq('#collabComFormResetbuttonID').attr('disabled', 'disabled');
		
		//2. and add pending indicator
		if (this.pendingIndicatorCF == null) {
			this.pendingIndicatorCF = new CPendingIndicator($jq('#collabComFormTextarea'));
		}
		this.pendingIndicatorCF.show();

		/* form params */
		var now = new Date();
		var pageName = wgPageName + '_' + now.getTime();
		
		//rating
		var ratingString = '';

		if ( this.ratingValue != null) {
			ratingString = '|CommentRating=' + this.ratingValue;
		}

		// textarea
		var textArea = ($jq('#collabComFormTextarea').val())? $jq('#collabComFormTextarea').val(): '';
		if(textArea.length==0 || this.textareaIsDefault) {
			this.pendingIndicatorCF.hide();
			$jq('#collabComFormMessage').attr('class', 'failure');
			$jq('#collabComFormMessage').html(ceLanguage.getMessage('ce_invalid'));
			$jq('#collabComFormMessage').show('slow');
			// enable form again
			$jq('#collabComFormTextarea').attr('disabled', false);
			$jq('#collabComFormSubmitbuttonID').attr('disabled', false);
			$jq('#collabComFormResetbuttonID').attr('disabled', false);
			return false;
		} else {
			// hide possibly shown message div
			$jq('#collabComFormMessage').hide('slow');
		}
		// escape html chars
		textArea.replace(/&/g,'&amp;');
		textArea.replace(/</g,'&lt;');
		textArea.replace(/>/g,'&gt;');
		textArea = this.textEncode(textArea);
		var userNameString = '';
		if( wgUserName != null && ceUserNS != null ) {
			userNameString = '|CommentPerson=' + ceUserNS + ':' + wgUserName;
		} else {
			userNameString = '|CommentPerson=';
		}

		var relatedCommentString = '';
		if(this.replyCommentName != null) {
			relatedCommentString = '|CommentRelatedComment=' + this.replyCommentName;
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
		$jq('#collabComFormTextarea').removeAttr('disabled');
		$jq('#collabComFormSubmitbuttonID').removeAttr('disabled');
		$jq('#collabComFormResetbuttonID').removeAttr('disabled');
		var comMessage = $jq('#collabComFormMessage');
		comMessage.show();
		if ( valueEl.nodeType == 1 ) {
			var valueCode = valueEl.firstChild.nodeValue
			if ( valueCode == 0 ){
				//fine.
				//reset, hide and enable form again
				comMessage.attr('class', 'success');
				comMessage.html(htmlmsg + ceLanguage.getMessage('ce_reload'));
				//add pending span
				var pendingSpan = document.createElement('span');
				$jq(pendingSpan).attr('id', 'collabComFormPending');
				comMessage.append(pendingSpan);
				if (this.pendingIndicatorMsg == null) {
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
			} else if ( valueCode == 1 || valueCode == 2 ) {
				//error, article already exists or permisson denied.
				this.pendingIndicatorCF.hide();
				$jq('#collabComFormMessage').attr('class', 'failure');
				comMessage.html(htmlmsg);
			} else {
				// sthg's really gone wrong
			}
		}
		return false;
	};

	/**
	 * This function deletes a single comment page.
	 * @param pageName
	 */
	this.deleteComment = function(pageName, container) {
		this.overlayName = container;
		// add pending indicator
		var comEl = $jq('#' + container);
		$jq('.ceOverlayDetails', comEl).html(ceLanguage.getMessage('ce_deleting'));
		var pendingSpan = document.createElement('span');
		$jq(pendingSpan).attr('id', 'collabComDelPending')
		$jq('.ceOverlayDetails', comEl).append(pendingSpan);
		if (this.pendingIndicatorDel == null) {
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
		if ( valueEl.nodeType == 1 ) {
			var valueCode = valueEl.firstChild.nodeValue
			this.pendingIndicatorDel.hide();
			if ( valueCode == 0 ){
				//fine.
				$jq('.ceOverlayDetails', $jq('#' + this.overlayName)).html(htmlmsg);
				$jq('.ceOverlayDeleteButtonDiv', $jq('#' + this.overlayName)).toggle();
				// change cancel button to close button
				$jq('.ceOverlayCancelButtonDiv', $jq('#' + this.overlayName)).css('float', 'none');
				$jq('.ceOverlayCancelButtonDiv', $jq('#' + this.overlayName)).css('padding', '0');
				$jq('.ceOverlayCancelButton', $jq('#' + this.overlayName)).val(ceLanguage.getMessage('ce_close_button'));
				$jq('.ceOverlayCancelButtonDiv', $jq('#' + this.overlayName)).css('text-align', 'center');
				$jq('.ceOverlayCancelButton', $jq('#' + this.overlayName)).bind('click', function(){
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
				});
				return true;
			} else if ( valueCode == 1 || valueCode == 2 ) {
				//error, article already exists or permisson denied.
				$jq('.ceOverlayDetails', $jq('#' + this.overlayName)).html(htmlmsg);
				return false;
			} else {
				//sthg's really gone wrong
				return false;
			}
		}
		return false;
	};

	/**
	 * "edit comment" was clicked. Provide the comment form.
	 */
	this.editCommentForm = function(pageName) {
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
		// rating only if rating is enabled
		if( typeof(wgCEEnableRating) !== "undefined" ) {
			var ratingIconSrc = $jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResRatingIcon img').attr('src');
			var ratingExistent = null;
			var editRatingValue = null;
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
			var ratingSpan = document.createElement('span');
			$jq(ratingSpan).addClass('collabComFormGrey');
			$jq(ratingSpan).append($jq(ratingTextOpt));

			var ratingDiv = document.createElement('div');
			$jq(ratingDiv).attr('id', 'collabComEditFormRating');
			$jq(ratingDiv).append($jq(ratingText));
			$jq(ratingDiv).append($jq(ratingSpan));
			$jq(ratingDiv).append($jq(ratingTextOpt2));

			var ratingIcons = document.createElement('span');
			$jq(ratingIcons).attr('id', 'collabComEditFormRadioButtons');

			var ratingIcon1 = document.createElement('img');
			$jq(ratingIcon1).addClass('collabComEditFormRatingImg');
			$jq(ratingIcon1).attr('id', 'collabComEditFormRating1');
			$jq(ratingIcon1).bind('click', function() {
				ceCommentForm.switchEditRating('#collabComEditFormRating1',-1);
			});
			$jq(ratingIcon1).attr('src', cegScriptPath + '/skins/Comment/icons/bad_inactive.png');

			var ratingIcon2 = document.createElement('img');
			$jq(ratingIcon2).addClass('collabComEditFormRatingImg');
			$jq(ratingIcon2).attr('id', 'collabComEditFormRating2');
			$jq(ratingIcon2).bind('click', function() {
				ceCommentForm.switchEditRating('#collabComEditFormRating2', 0);
			});
			$jq(ratingIcon2).attr('src', cegScriptPath + '/skins/Comment/icons/neutral_inactive.png');

			var ratingIcon3 = document.createElement('img');
			$jq(ratingIcon3).addClass('collabComEditFormRatingImg');
			$jq(ratingIcon3).attr('id', 'collabComEditFormRating3');
			$jq(ratingIcon3).bind('click', function() {
				ceCommentForm.switchEditRating('#collabComEditFormRating3', 1);
			});
			$jq(ratingIcon3).attr('src', cegScriptPath + '/skins/Comment/icons/good_inactive.png');

			$jq(ratingIcons).append(ratingIcon1);
			$jq(ratingIcons).append(ratingIcon2);
			$jq(ratingIcons).append(ratingIcon3);
			$jq(ratingDiv).append($jq(ratingIcons));
		} // end rating

		// textarea
		var form = document.createElement('textarea');
		$jq(form).attr('id', 'collabComEditFormTextarea');
		$jq(form).attr('rows', '5');
		$jq(form).val(content);

		//buttons
		var submitButton = document.createElement('input');
		$jq(submitButton).attr('id', 'collabComEditFormSubmit');
		$jq(submitButton).attr('type', 'button');
		$jq(submitButton).attr('value', ceLanguage.getMessage('ce_edit_button'));
		$jq(submitButton).bind('click', function() {
			ceCommentForm.editExistingComment();
		});

		var cancelSpan = document.createElement('span');
		$jq(cancelSpan).attr('id', 'collabComEditFormCancel');
		$jq(cancelSpan).css({'display':'inline','cursor':'pointer','color':'blue'});
		$jq(cancelSpan).bind('click', function() {
			ceCommentForm.cancelCommentEditForm(pageName);
		});
		var cancelText = document.createTextNode(' | Cancel');
		$jq(cancelSpan).append($jq(cancelText));

		// message div
		var msgDiv = document.createElement('div');
		$jq(msgDiv).attr('id', 'collabComEditFormMessage');
		$jq(msgDiv).css('display', 'none');

		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResText').html('');
		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResText').append(ratingDiv);
		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResText').append(form);
		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResText').append(submitButton);
		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResText').append(cancelSpan);
		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResText').append(msgDiv);

		if(ratingExistent) {
			ceCommentForm.switchEditRating('#collabComEditFormRating' + (parseInt(editRatingValue) + 2), editRatingValue);
		}
		$jq('#' + pageName.replace(/(:|\.)/g,'\\$1') + ' .collabComResText').toggle();
		return true;
	};
	
	/**
	 * "edit comment" was clicked. Provide the comment form.
	 */
	this.editExistingComment = function() {
		//1. disable form tools
		$jq('#collabComEditFormTextarea').attr('disabled', 'disabled');
		$jq('#collabComEditFormSubmit').attr('disabled', 'disabled');
		$jq('#collabComEditFormCancel').hide();

		//2. and add pending indicator
		if (this.pendingIndicatorEF == null) {
			this.pendingIndicatorEF = new CPendingIndicator($jq('#collabComEditFormTextarea'));
		}
		this.pendingIndicatorEF.show();

		/* form params */
		//rating
		var ratingString = '';
		if ( this.editRatingValue != null) {
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
			var commentPerson = commentPerson.split(':');
			commentPerson = commentPerson.pop();
			commentPerson = '|CommentPerson=' + ceUserNS + ':' + commentPerson;
		}
		var relatedComment = '';
		if(this.editCommentRelatedComment != null && this.editCommentRelatedComment != '') {
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
		if ( valueEl.nodeType == 1 ) {
			var valueCode = valueEl.firstChild.nodeValue
			var comEditMessage = document.createElement('div');
			$jq(comEditMessage).attr('id', 'collabComEditFormMessage');
			$jq('#' + this.editCommentName.replace(/(:|\.)/g,'\\$1')).before($jq(comEditMessage));
			if ( valueCode == 0 ){
				//fine.
				//reset, hide and enable form again
				$jq(comEditMessage).show();
				$jq(comEditMessage).attr('class', 'success');
				$jq(comEditMessage).html(htmlmsg + ceLanguage.getMessage('ce_reload'));
				//add pending span
				var pendingSpan = document.createElement('span');
				$jq(pendingSpan).attr('id', 'collabComEditFormPending');
				$jq(comEditMessage).append(pendingSpan);
				if (this.pendingIndicatorMsg == null) {
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
			} else if ( valueCode == 1 || valueCode == 2 ) {
				//error, article already exists or permisson denied.
				$jq(comEditMessage).html(htmlmsg);
			} else {
				//sthg's really gone wrong
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
		return true;
	};

	/**
	 * 
	 */
	this.replyCommentForm = function(pageName) {
		this.replyCommentName = pageName;
		var container = $jq('#' + pageName.replace(/(:|\.)/g,'\\$1'));
		var commentForm = $jq('#collabComForm');
		$jq('#collabComFormResetbuttonID').bind('click', function(){
			commentForm.hide();
			commentForm.css( 'marginLeft', '');
			($jq('#collabComFormHeader')).append(commentForm);
		});
		var resMargin = container.css('margin-left');
		if ( typeof( resMargin ) != "undefined" ) {
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
		// two leading spaces are interpreted as pre-tag 
		text = text.replace(/ /g, '&nbsp;');
		text = text.replace(/(\r\n|\r|\n)/g, '<br />');
		return text;
	};

	/**
	 * Function to decode again.
	 */
	this.textDecode = function(text) {
		// two leading spaces are interpreted as pre-tag 
		text = text.replace(/&nbsp;/g, ' ');
		text = text.replace(/<br>|<br \/>/g, '\n');
		// property & template cleaning:
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
		if (this.ratingValue != null) {
			var oldhtmlid = '#collabComFormRating' + String(this.ratingValue + 2);
			$jq(oldhtmlid).attr('src', $jq(oldhtmlid).attr('src').replace(/_active/g, '_inactive'));
			this.ratingValue = null;
		}
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
		var ratingImg = cegScriptPath + '/skins/Comment/icons/';
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
		var ratingImg = cegScriptPath + '/skins/Comment/icons/';
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
		if( commentResults.css('display') == 'block' ) {
			newComToggleText = ceLanguage.getMessage('ce_com_show');
		} else {
			newComToggleText = ceLanguage.getMessage('ce_com_hide');
		}
		comToggle.html(' | ' + newComToggleText);
		commentResults.toggle("slow");
		return true;
	};

	/**
	 * fired when the "View" select box has changed
	 * Determines which view is requested and calls the appropriate function
	 */
	this.toggleView = function() {
		var newView = $jq('#collabComFormView option:selected').val();
		if(newView == this.currentView) {
			return true;
		} else {
			if(newView == 0) {
				this.showThreaded()
			} else if (newView == 1) {
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
			$jq('collabComReply', resCom).bind('click', function() {
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
			// name of actual comment
			var resComName = resComInfo.html();
			var superComInfo = $jq('.collabComResInfoSuper', resCom);
			// name of the comment, the actual comment is related to (if there's one)
			var superComName = superComInfo.html();
			if(typeof( superComName ) != "undefined" && 
					superComName != null && superComName != false && superComName != '') {
				var resMargin = $jq('#' + superComName.replace(/(:|\.)/g, '\\$1')).css('margin-left');
				var newMargin = "30";
				if ( typeof( resMargin ) != "undefined" ) {
					newMargin = (parseInt(resMargin) + 30);
				}
				// check if there are "child" comments
				var name = ceCommentForm.getLastChildComment(superComName);
				if(name != superComName) {
					// child found. add behind.
					$jq('#' + name.replace(/(:|\.)/g, '\\$1')).after($jq(resCom))	
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
		var meinz = $jq('#' + commentName.replace(/(:|\.)/g, '\\$1')).nextAll();
		var childComments = $jq('.comRearranged').filter(function(index) {
			var indSuperComName = $jq('.collabComResInfoSuper',this);
			return indSuperComName.html() == commentName;
		})
		if(childComments.length > 0){
			lastChildComment = childComments[childComments.length-1];
			return this.getLastChildComment($jq(lastChildComment).attr('id'));
		} else {
			return commentName;
		}
	}

	/**
	 * Creates the complete overlay structure and returns it.
	 */
	this.createOverlay = function(num, pageName) {
		var overlayName = 'overlay_' + num;
		// divs
		var overlayDivEl = document.createElement('div');
		$jq(overlayDivEl).addClass('ceOverlay');
		$jq(overlayDivEl).attr('id', overlayName);

		var overlayDivDetailsEl = document.createElement('div');
		$jq(overlayDivDetailsEl).addClass('ceOverlayDetails');
		$jq(overlayDivEl).append($jq(overlayDivDetailsEl));
		var overlayDivContent = document.createTextNode(ceLanguage.getMessage('ce_delete'));
		$jq(overlayDivDetailsEl).append($jq(overlayDivContent));

		// cancel button
		var cancelButtonDiv = document.createElement('div');
		$jq(cancelButtonDiv).addClass('ceOverlayCancelButtonDiv');
		$jq(cancelButtonDiv).bind('click', function() {
			$jq('#' + pageName.replace(/(:|\.)/g,'\\\\$1')).css('background-color', '');
		});
		var cancelButton = document.createElement('input');
		$jq(cancelButton).attr('type', 'button');
		$jq(cancelButton).addClass('ceOverlayCancelButton close');
		$jq(cancelButton).attr('value', ceLanguage.getMessage('ce_cancel_button'));
		$jq(cancelButtonDiv).append($jq(cancelButton));
		$jq(overlayDivEl).append($jq(cancelButtonDiv));

		// delete button
		var deleteButtonDiv = document.createElement('div');
		$jq(deleteButtonDiv).addClass('ceOverlayDeleteButtonDiv');
		$jq(deleteButtonDiv).bind('click', function() {
			ceCommentForm.deleteComment(escape(pageName), overlayName);
		});
		var deleteButton = document.createElement('input');
		$jq(deleteButton).attr('type', 'button');
		$jq(deleteButton).addClass('ceOverlayDeleteButton');
		$jq(deleteButton).attr('value', ceLanguage.getMessage('ce_delete_button'));
		$jq(deleteButtonDiv).append($jq(deleteButton));
		$jq(overlayDivEl).append($jq(deleteButtonDiv));
		return overlayDivEl;
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
		if($jq("#content") == null){
			return;
		}

		var alignOffset = 0;
		if (alignment != undefined) {
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
		if(this.contentElement == null) {
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

/**
 * This function takes care about missing event handlers.
 * (It hasn't been possible to add this in the Template itself)
 * It also creates Edit and Delete links for users that own the appropriate right.
 * The current DOM structure is saved to be reused in "flat view".
 */
$jq(document).ready(
	function(){
		// add JS to header items
		$jq('#collabComToggle').bind('click', function() {
			ceCommentForm.toggleComments();
		});
		$jq('#collabComToggle').attr('title', ceLanguage.getMessage('ce_com_toggle_tooltip'));
		$jq('#collabComFormToggle').bind('click', function() {
			$jq('#collabComForm').toggle('slow');
		});
		$jq('#collabComFormToggle').attr('title', ceLanguage.getMessage('ce_form_toggle_tooltip'));
		// "change view" functionality
		var viewSpan = document.createElement('span');
		$jq(viewSpan).html(' | View: ');
		$jq(viewSpan).attr('id', 'collabComFormView');
		var selectEl = document.createElement('select');
		$jq(selectEl).bind('change', function() {
			ceCommentForm.toggleView();
		});
		
		try {
			selectEl.add(new Option('Threaded', 0, true, true), null); // standards compliant; doesn't work in IE
		}
		catch(ex) {
			selectEl.add(new Option('Threaded', 0, true, true)); // IE only
		}
		try {
			selectEl.add(new Option('Flat', 1), null); // standards compliant; doesn't work in IE
		}
		catch(ex) {
			selectEl.add(new Option('Flat', 1)); // IE only
		}
		$jq(viewSpan).append($jq(selectEl));
		$jq('#collabComFormToggle').after($jq(viewSpan));
		ceCommentForm.currentView = 0;
		// format comments
		var resultComments = $jq('.collabComRes');
		$jq.each(resultComments, function(i, resCom ){
			var resComInfo = $jq('.collabComResInfo', resCom);
			// name of actual comment
			var resComName = resComInfo.html();
			var commentPerson= $jq('.collabComResUsername > a', resCom).html();
			if(!commentPerson) {
				commentPerson = '';
			} else {
				var commentPerson = commentPerson.split(':');
				commentPerson = commentPerson.pop();
			}
			if( (typeof( cegUserIsSysop ) != "undefined" && cegUserIsSysop != null && cegUserIsSysop != false) ||
					(wgUserName != null && commentPerson == wgUserName) ) {
				//Overlay for deleting comments
				var overlayDiv = ceCommentForm.createOverlay(i, resComName);
				var divEl = document.createElement('div');
				$jq(divEl).css({'display' : 'inline', 'cursor' : 'pointer', 'color':'blue'});
				$jq(divEl).attr('title', ceLanguage.getMessage('ce_delete_title'));
				$jq(divEl).attr('rel', '#overlay_' + i);
				$jq(divEl).attr('id' , 'ceDel' + escape(resComName));
				$jq(divEl).addClass('collabComDel');
				$jq(divEl).bind('click', function() {
					$jq('#' + resComName.replace(/(:|\.)/g,'\\$1')).addClass('collabComDelSelected');
				});
				var delImgEl = document.createElement('img');
				$jq(delImgEl).attr('src', cegScriptPath + '/skins/Comment/icons/Delete_button.png')
				$jq(delImgEl).addClass('collabComDeleteImg');
				$jq(divEl).append($jq(delImgEl));
				$jq('.collabComResDate', resCom).after(divEl);
				$jq('#collabComResults').after(overlayDiv);

				// reply
				var divEl = document.createElement('div');
				$jq(divEl).attr('title', ceLanguage.getMessage('ce_reply_title'));
				$jq(divEl).addClass('collabComReply');
				$jq(divEl).bind('click', function() {
					ceCommentForm.replyCommentForm(resComName);
				});
				$jq(divEl).html('Reply');
				var replyImgEl = document.createElement('img');
				$jq(replyImgEl).attr('src', cegScriptPath + '/skins/Comment/icons/Reply_Comment.png')
				$jq(replyImgEl).addClass('collabComReplyImg');
				$jq(divEl).append($jq(replyImgEl));
				$jq('.collabComResText', resCom).after(divEl);

				// edit
				var divEl = document.createElement('div');
				$jq(divEl).css({'display' : 'inline', 'cursor' : 'pointer', 'color' : 'blue'});
				$jq(divEl).attr('title', ceLanguage.getMessage('ce_edit_title'));
				$jq(divEl).addClass('collabComEdit');
				$jq(divEl).bind('click', function() {
					ceCommentForm.editCommentForm(resComName);
				});
				var imgEl = document.createElement('img');
				$jq(imgEl).attr('src', cegScriptPath + '/skins/Comment/icons/Edit_button2.png')
				$jq(imgEl).addClass('collabComEditImg');
				$jq(divEl).append($jq(imgEl));
				$jq('.collabComResDate', resCom).after(divEl);
			}
		});
		//clone actual structure without events (bind them again later)
		ceCommentForm.savedStructure = $jq('#collabComResults').clone();
		ceCommentForm.showThreaded();
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