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
;( function( $ ) {


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
	// edit mode
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
		$( '#collabComForm *:input' ).attr( 'disabled', 'disabled' );

		//2. and add pending indicator
		if ( typeof this.pendingIndicatorCF === 'undefined' 
			|| this.pendingIndicatorCF === null ) 
		{
			this.pendingIndicatorCF = new CPendingIndicator( $( '#collabComFormTextarea' ) );
		}
		this.pendingIndicatorCF.show();

		/* form params */
		var now = new Date();
		var pageName = mw.config.get( 'wgPageName' ) + '_' + now.getTime();

		//rating
		var ratingString = '';
		if ( this.ratingValue !== null ) {
			ratingString = '|CommentRating=' + this.ratingValue;
		}

		// textarea
		var textArea = ( $( '#collabComFormTextarea' ).val())? $( '#collabComFormTextarea' ).val(): '';
		if ( textArea.length === 0 || this.textareaIsDefault ) {
			this.pendingIndicatorCF.hide();
			$( '#collabComFormMessage' ).attr( 'class', 'failure' );
			$( '#collabComFormMessage' ).html( ceLanguage.getMessage( 'ce_invalid' ) );
			$( '#collabComFormMessage' ).show( 'slow' );
			// enable form again
			$( '#collabComForm *:input' ).removeAttr( 'disabled' );
			return false;
		} else {
			// hide possibly shown message div
			$( '#collabComFormMessage' ).hide( 'slow' );
		}
		// escape html chars
		textArea = textArea.replace( /&/g, '&amp;' );
		textArea = textArea.replace( /</g, '&lt;' );
		textArea = textArea.replace( />/g, '&gt;' );
		textArea = this.textEncode( textArea );
		// file attachments: process the content from the input field
		var fileAttach = ( $( '#collabComFormFileAttach' ).val())? $( '#collabComFormFileAttach' ).val(): '';
		var fileAttachString = '|AttachedArticles=' + fileAttach;

		var userNameString = '';
		if ( wgUserName !== null && wgCEUserNS !== null ) {
			userNameString = '|CommentPerson=' + wgCEUserNS + ':' + wgUserName;
		} else {
			userNameString = '|CommentPerson=';
		}

		var relatedCommentString = '';
		if ( this.replyCommentName !== null ) {
			relatedCommentString = '|CommentRelatedComment=' + this.replyCommentName;
		}

		var pageContent = '{{Comment' +
			userNameString +
			'|CommentRelatedArticle=' + mw.config.get( 'wgPageName' ) +
			ratingString  +
			relatedCommentString +
			'|CommentDatetime=##DATE##' +
			'|CommentContent=' + textArea +
			fileAttachString + '|}}';

		this.currentPageName = escape( pageName );
		this.currentPageContent = escape( pageContent );

		sajax_do_call( 'cef_comment_createNewPage',
			[this.currentPageName, this.currentPageContent],
			this.processFormCallback.bindToFunction( this )
		);
		return false;
	};

	/**
	 * The callback function for createNewPage
	 * @param request
	 */
	this.processFormCallback = function( request ) {
		var resultDOM = this.XMLResult = CollaborationXMLTools.createDocumentFromString( request.responseText );	
		var valueEl = resultDOM.getElementsByTagName( 'value' )[0];
		var htmlmsg = resultDOM.getElementsByTagName( 'message' )[0].firstChild.nodeValue;

		this.pendingIndicatorCF.hide();
		$( '#collabComForm' ).get(0).reset();
		$( '#collabComForm' ).hide();
		$( '#collabComForm *:input' ).removeAttr( 'disabled' );
		var comMessage = $( '#collabComFormMessage' );
		comMessage.show();
		if ( valueEl.nodeType === 1 ) {
			var valueCode = valueEl.firstChild.nodeValue;
			if ( valueCode === '0' ) {
				comMessage.attr( 'class', 'success' )
					.html( htmlmsg + ceLanguage.getMessage( 'ce_reload' ) );
				// add pending span
				$( '<span>', {
					'id' : 'collabComFormPending',
					'text' : ' '
				}).appendTo( comMessage );
				if ( typeof this.pendingIndicatorMsg === 'undefined' 
					|| this.pendingIndicatorMsg === null)
				{
					this.pendingIndicatorMsg = new CPendingIndicator( $( '#collabComFormPending' ) );
				}
				this.pendingIndicatorMsg.show();
				//to do a page reload with action=purge
				var winSearch = window.location.search; 
				if ( winSearch.indexOf( 'action=purge' ) !== -1 ) {
					window.location.reload();
				} else {
					if ( winSearch.indexOf( '?' ) !== -1 ) {
						window.location.href = window.location.href.concat( '&action=purge' );
					} else {
						window.location.href = window.location.href.concat( '?action=purge' );
					}
				}
				return true;
			} else if ( valueCode === '1' || valueCode === '2' ) {
				//error, article already exists or permisson denied.
				this.pendingIndicatorCF.hide();
				comMessage.attr( 'class', 'failure' ).html( htmlmsg );
			}
		}
		return false;
	};

	/**
	 * Delete comment page(s).
	 * 
	 * @param pageName
	 * @param container
	 */
	this.deleteComment = function( pageName, container ) {
		var	deletingMsg = '',
			commentsToDelete = $( '#' + pageName + ' > .collabComPlain' ).html(),
			fullDelete = false,
			comEl = $( '#' + container ),
			hasReplies = false,
			replyComment;
		this.overlayName = container;

		$( '*:input', comEl ).attr( 'disabled', 'disabled' );
		if ( $( '.ceOverlayFullDeleteCheckbox:checked', comEl ).length === 1 ) {
			fullDelete = true;
			deletingMsg = ceLanguage.getMessage( 'ce_full_deleting' );
			hasReplies = true;
			replyComment = $( '#' + pageName ).next( '.comRearranged' );
			while( hasReplies ) {
				if ( replyComment.length === 0) {
					hasReplies = false;
				} else {
					commentsToDelete += ', ' + $( '.collabComPlain', replyComment ).html();
				}
				replyComment = replyComment.next( '.comRearranged' )
			}
			//console.log(commentsToDelete);
		} else {
			deletingMsg = ceLanguage.getMessage( 'ce_deleting' )
		}
		$( '.ceOverlayDetails', comEl ).html( deletingMsg );
		// add pending indicator
		var pendingSpan = $( '<span>', {
			'id' : 'collabComDelPending',
			'text' : ' '
		})
		$( '.ceOverlayDetails', comEl ).append( pendingSpan );
		if ( typeof this.pendingIndicatorDel === 'undefined'
			|| this.pendingIndicatorDel === null )
		{
			this.pendingIndicatorDel = new CPendingIndicator( $( '#collabComDelPending' ) );
		}
		this.pendingIndicatorDel.show();
		if ( fullDelete ) {
			sajax_do_call( 'cef_comment_fullDeleteComments', 
				[escape( commentsToDelete )],
				this.deleteCommentCallback.bindToFunction(this)
			);
		} else {
			sajax_do_call( 'cef_comment_deleteComment', 
				[escape( commentsToDelete )],
				this.deleteCommentCallback.bindToFunction(this)
			);
		}
	};

	/**
	 * The callback function for deleteComment.
	 */
	this.deleteCommentCallback = function( request ) {
		var resultDOM = this.XMLResult = CollaborationXMLTools.createDocumentFromString( request.responseText );	
		var valueEl = resultDOM.getElementsByTagName( 'value' )[0];
		var htmlmsg = resultDOM.getElementsByTagName( 'message' )[0].firstChild.nodeValue;
		var page = resultDOM.getElementsByTagName( 'article' )[0].firstChild.nodeValue;
		var divId = '#' + page;
		var comEditMessage = $( '<div>', {
			'id' : 'collabComEditFormMessage'
		});
		$( divId ).before( comEditMessage );
		if ( valueEl.nodeType === 1 ) {
			var valueCode = valueEl.firstChild.nodeValue;
			this.pendingIndicatorDel.hide();
			if ( valueCode === '0' ) {
				//fine.
				htmlmsg += ceLanguage.getMessage( 'ce_reload' );
				// close overlay -> just click the button
				$( '#' + this.overlayName ).hide();
				comEditMessage.addClass( 'success' );
				comEditMessage.html( htmlmsg );
				var pendingSpan = $( '<span>', {
					'id' : 'collabComDelPending',
					'text' : ' '
				}).appendTo( comEditMessage );
				if ( typeof this.pendingIndicatorDel2 === 'undefined' 
					|| this.pendingIndicatorDel2 === null)
				{
					this.pendingIndicatorDel2 = new CPendingIndicator( pendingSpan );
				}
				this.pendingIndicatorDel2.show();
				// do a page reload with action=purge
				var winSearch = window.location.search; 
				if ( winSearch.indexOf('action=purge') !== -1 ) {
					window.location.reload();
				} else {
					if ( winSearch.indexOf('?') !== -1 ) {
						window.location.href = window.location.href.concat('&action=purge');
					} else {
						window.location.href = window.location.href.concat('?action=purge');
					}
				}
				return true;
			} else if ( valueCode === '1' || valueCode === '2' ) {
				// error, article already exists or permisson denied.
				comEditMessage.addClass( 'failure' );
				comEditMessage.html( htmlmsg );
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
	this.editCommentForm = function( pageName ) {
		var ratingDiv, elemSelector, container, content, ratingIconSrc,
			editRatingValue, ratingExistent, ratingText, ratingTextOpt,
			ratingTextOpt2, ratingSpan, ratingIcons, tmp,
			textarea, fileAttachField, buttonBox, msgDiv, fileAttachSpan = '';
		this.editCommentName = pageName;
		elemSelector = '#' + pageName;
		this.editCommentRelatedComment = $( elemSelector +
			' .collabComResInfoSuper' ).html();
		if ( this.editMode ) {
			//already editing. cancel first!
			return false;
		}
		this.editMode = true;

		// create a new form and set all values
		container = $( elemSelector );
		content = $( elemSelector + ' .collabComResText' ).html();
		container.css( 'background-color', '#F2F2F2' );
		this.savedCommentContent = content;
		content = this.textDecode( content );
		$( elemSelector + ' .collabComResText' ).toggle();
		$( elemSelector + ' .collabComResRight' ).css(
			'width', '85%'
		);
		// rating only if rating is enabled
		if ( typeof wgCEEnableRating !== 'undefined' ) {
			ratingIconSrc = $( elemSelector
				+ ' .collabComResRatingIcon img' ).attr( 'src' );
			if ( ratingIconSrc ) {
				if ( ratingIconSrc.match( 'Bad' ) ) {
					editRatingValue = -1;
					ratingExistent = true;
				}
				if ( ratingIconSrc.match( 'Neutral' ) ) {
					editRatingValue = 0;
					ratingExistent = true;
				}
				if ( ratingIconSrc.match( 'Good' ) ) {
					editRatingValue = 1;
					ratingExistent = true;
				}
			}
			// rating things
			ratingText = document.createTextNode( ceLanguage.getMessage( 'ce_edit_rating_text' ) );
			ratingTextOpt = document.createTextNode( ceLanguage.getMessage( 'ce_edit_rating_text2' ) );
			ratingTextOpt2 = document.createTextNode( ':' );
			ratingSpan = $( '<span>', {
				'class' : 'collabComFormGrey'
			});
			ratingSpan.append( $( ratingTextOpt ) );

			ratingDiv = $( '<div>', {
				'id' : 'collabComEditFormRating'
			});
			ratingDiv.append( $( ratingText ) );
			ratingDiv.append( ratingSpan );
			ratingDiv.append( $( ratingTextOpt2 ) );

			ratingIcons = $( '<span>', {
				'id' : 'collabComEditFormRadioButtons'
			});

			$( '<img>', {
				'id' : 'collabComEditFormRating1',
				'class' : 'collabComEditFormRatingImg',
				'src' : wgCEScriptPath + '/skins/Comment/icons/bad_inactive.png',
				'click' :  function() {
					ceCommentForm.switchEditRating( '#collabComEditFormRating1', -1 );
				}
			}).appendTo( ratingIcons );
			$( '<img>', {
				'id' : 'collabComEditFormRating2',
				'class' : 'collabComEditFormRatingImg',
				'src' : wgCEScriptPath + '/skins/Comment/icons/neutral_inactive.png',
				'click' :  function() {
					ceCommentForm.switchEditRating( '#collabComEditFormRating2', 0 );
				}
			}).appendTo( ratingIcons );
			$( '<img>', {
				'id' : 'collabComEditFormRating3',
				'class' : 'collabComEditFormRatingImg',
				'src' : wgCEScriptPath + '/skins/Comment/icons/good_inactive.png',
				'click' :  function() {
					ceCommentForm.switchEditRating( '#collabComEditFormRating3', 1 );
				}
			}).appendTo( ratingIcons );
			ratingDiv.append( ratingIcons );
		} // end rating

		// textarea
		textarea = $( '<textarea>', {
			'id' : 'collabComEditFormTextarea',
			'rows' : '5',
			'value' : content
		})

		// file attachments: create input element
		if ( typeof wgCEEnableAttachments !== undefined ) {
			tmp = document.createElement( 'input' );
			tmp.setAttribute( 'type', 'text' );
			fileAttachField = $( tmp ).addClass( 'wickEnabled' )
				.attr( { 'pastens' : 'true', 'id' : 'collabComEditFormFileAttach' } )
				.val( $( elemSelector + ' .collabComResFileAttachSaved' ).html() );
			if ( typeof wgCEEditUploadURL !== 'undefined' ) {
				fileAttachSpan = $( '<span>', {
					'id' : 'collabComEditFormFileAttachLink'
				});
				$( '<a>', {
					'id' : 'collabComEditFormFileAttach',
					'class' : 'rmAlink',
					'title' : 'Upload file',
					'href' : wgCEEditUploadURL,
					'text' : 'Upload file'
				}).appendTo( fileAttachSpan );
			}
		}

		//buttons
		buttonBox = $( '<div>' );
		tmp = document.createElement( 'input' );
		tmp.setAttribute( 'type', 'button' );
		$( tmp ).attr( 'id', 'collabComEditFormSubmit' )
			.val( ceLanguage.getMessage( 'ce_edit_button' ) )
			.click( function() {
				ceCommentForm.editExistingComment();
			})
			.appendTo( buttonBox );

		$( '<span>', {
			'id' : 'collabComEditFormCancel',
			'text' : ' | ' + ceLanguage.getMessage( 'ce_cancel_button' ),
			'click' : function() {
				ceCommentForm.cancelCommentEditForm( pageName );
			}
		}).appendTo( buttonBox )

		// message div
		msgDiv = $( '<div>', {
			'id' : 'collabComEditFormMessage',
			'style': 'display: none'
		})

		$( elemSelector + ' .collabComResText' ).html( '' );
		if ( typeof ratingDiv !== 'undefined' && ratingDiv !== null ) {
			$( elemSelector + ' .collabComResText' ).append( ratingDiv );
		}
		$( elemSelector + ' .collabComResText' ).append( textarea );
		$( elemSelector + ' .collabComResText' ).append( fileAttachField );
		$( elemSelector + ' .collabComResText' ).append( fileAttachSpan );
		$( elemSelector + ' .collabComResText' ).append( buttonBox );
		
		$( elemSelector + ' .collabComResText' ).append( msgDiv );

		if ( ratingExistent ) {
			ceCommentForm.switchEditRating( '#collabComEditFormRating' 
				+ (parseInt(editRatingValue) + 2), editRatingValue
			);
		}
		$( elemSelector + ' .collabComEditCancel' ).show();
		$( elemSelector + ' .collabComResText' ).show();
		$( elemSelector + ' .collabComEdit' ).hide();
		$( elemSelector + ' .collabComReply' ).hide();
		return true;
	};
	
	/**
	 * "edit comment" was clicked. Provide the comment form.
	 */
	this.editExistingComment = function() {
		//1. disable form tools
		$( '#' + this.editCommentName + ' *:input').attr( 'disabled', 'disabled' );

		//2. and add pending indicator
		if ( typeof this.pendingIndicatorEF === 'undefined' 
			|| this.pendingIndicatorEF === null )
		{
			this.pendingIndicatorEF = new CPendingIndicator( $( '#collabComEditFormTextarea' ) );
		}
		this.pendingIndicatorEF.show();

		/* form params */
		//rating
		var ratingString = '';
		if ( this.editRatingValue !== null ) {
			ratingString = '|CommentRating=' + this.editRatingValue;
		}

		// textarea
		var textArea = ( $( '#collabComEditFormTextarea' ).val() )?
			$( '#collabComEditFormTextarea' ).val() : '';
		// escape html chars
		textArea.replace( /&/g, '&amp;' );
		textArea.replace( /</g, '&lt;' );
		textArea.replace( />/g, '&gt;' );
		textArea = this.textEncode( textArea );
		// change the comment person?
		var commentPerson= $( '.collabComResUsername > a',
			$( '#' + this.editCommentName ) ).html();
		if ( !commentPerson ) {
			commentPerson = '';
		} else {
			commentPerson = commentPerson.split( ':' );
			commentPerson = commentPerson.pop();
			commentPerson = '|CommentPerson=' + wgCEUserNS + ':' + commentPerson;
		}
		var relatedComment = '';
		if ( this.editCommentRelatedComment !== null
			&& this.editCommentRelatedComment !== '' )
		{
			relatedComment = '|CommentRelatedComment=' + this.editCommentRelatedComment;
		}
		var editorString = '';
		if ( mw.config.get( 'wgUserName' ) !== null && wgCEUserNS !== null ) {
			editorString = '|CommentLastEditor=' + wgCEUserNS + ':' + mw.config.get( 'wgUserName' );
		} else {
			editorString = '|CommentLastEditor=';
		}
		// file attachments: process the content from the input field
		var fileAttach = ( $( '#collabComEditFormFileAttach' ).val())? $( '#collabComEditFormFileAttach' ).val(): '';
		var fileAttachString = '|AttachedArticles=' + fileAttach;

		var pageContent = '{{Comment' +
			commentPerson +
			'|CommentRelatedArticle=' + mw.config.get( 'wgPageName' ) +
			ratingString  +
			'|CommentDatetime=##DATE##'+
			'|CommentContent=' + textArea + 
			relatedComment + 
			editorString + 
			fileAttachString + '|}}';
		this.currentPageName = escape(
			$( '.collabComPlain', $( '#' + this.editCommentName ) ).html()
		);
		this.currentPageContent = escape( pageContent );
		//do ajax call
		sajax_do_call( 'cef_comment_editPage',
			[this.currentPageName, this.currentPageContent],
			this.editExistingCommentCallback.bindToFunction( this )
		);
		return true;
	};

	/**
	 * 
	 * @param: request
	 */
	this.editExistingCommentCallback = function( request ) {
		var elemSelector = '#' + this.editCommentName;
		var resultDOM = this.XMLResult = CollaborationXMLTools.createDocumentFromString( request.responseText );	
		var valueEl = resultDOM.getElementsByTagName( 'value' )[0];
		var htmlmsg = resultDOM.getElementsByTagName( 'message' )[0].firstChild.nodeValue;

		this.pendingIndicatorEF.hide();
		if ( valueEl.nodeType === 1 ) {
			var valueCode = valueEl.firstChild.nodeValue;
			var comEditMessage = $( '<div>', {
				'id' : 'collabComEditFormMessage'
			})
			$( elemSelector ).before( comEditMessage );
			if ( valueCode === '0' ) {
				//fine - reset, hide and enable form again
				comEditMessage.show();
				comEditMessage.addClass( 'success' );
				comEditMessage.html( htmlmsg + ceLanguage.getMessage( 'ce_reload' ) );
				//add pending span
				$( '<span>', {
					'id' : 'collabComEditFormPending',
					'text' :' '
				}).appendTo( comEditMessage )
				if ( typeof this.pendingIndicatorMsg === 'undefined' 
					|| this.pendingIndicatorMsg === null )
				{
					this.pendingIndicatorMsg = new CPendingIndicator( $( '#collabComEditFormPending' ) );
				}
				this.pendingIndicatorMsg.show();
				// do a page reload with action=purge
				var winSearch = window.location.search; 
				if ( winSearch.indexOf( 'action=purge' ) !== -1 ) {
					window.location.reload();
				} else {
					if ( winSearch.indexOf( '?' ) !== -1 ) {
						window.location.href = window.location.href.concat( '&action=purge' );
					} else {
						window.location.href = window.location.href.concat( '?action=purge' );
					}
				}
				return true;
			} else if ( valueCode === '1' || valueCode === '2' ) {
				//error, article already exists or permisson denied.
				comEditMessage.addClass( 'failure' );
				comEditMessage.html( htmlmsg );
				$( elemSelector + ' *:input' ).removeAttr( 'disabled' );
			}
		}
		return false;
	};

	/**
	 * 
	 */
	this.cancelCommentEditForm = function( pageName ) {
		this.editMode = false;
		this.editCommentName = null;
		this.editCommentRelatedComment = null;
		this.editRatingValue = null;
		var elemSelector = '#' + pageName;
		$( elemSelector ).css( 'background-color', '' );
		$( elemSelector + ' .collabComResText' ).toggle();
		$( elemSelector + ' .collabComResText' ).html(this.savedCommentContent);
		$( elemSelector + ' .collabComResText' ).toggle();
		$( elemSelector + ' .collabComResRight' ).css( 'width', '' );
		$( elemSelector + ' .collabComEditCancel' ).hide();
		$( elemSelector + ' .collabComEdit' ).toggle();
		$( elemSelector + ' .collabComReply' ).show();
		return true;
	};

	/**
	 * 
	 */
	this.replyCommentForm = function( pageName ) {
		var container = $( '#' + pageName );
		this.replyCommentName = $( '.collabComPlain', container ).html();
		var commentForm = $( '#collabComForm' );
		$( '#collabComFormResetbuttonID' ).bind( 'click', function() {
			commentForm.hide();
			commentForm.css( 'marginLeft', '' );
			$( '#collabComFormHeader' ).append( commentForm );
		});
		var resMargin = container.css( 'margin-left' );
		if ( typeof resMargin !== 'undefined' ) {
			var newMargin = ( parseInt( resMargin ) + 30 );
			commentForm.css( 'marginLeft', newMargin );
		} else {
			commentForm.css( 'marginLeft', '30px' );
		}
		container.after( commentForm );
		commentForm.show();
	};

	/*helper functions*/

	/**
	 * Function to do all necessary encodings
	 * to make sure that comments are displayed 
	 * excactly the same as in the form
	 */
	this.textEncode = function( text ) {
		// property & template cleaning:
		text = text.replace( /:/g, '&#58;' );
		text = text.replace( /\{/g, '&#123;' );
		text = text.replace( /\{/g, '&#123;' );
		text = text.replace( /\[/g, '&#91;' );
		text = text.replace( /\]/g, '&#93;' );
		text = text.replace( /\//g, '&#47;' );
		text = text.replace( /\\/g, '&#92;' );
		text = text.replace( /(\r\n|\r|\n)/g, '<br />' );
		//replace the leading whitespace with html entity for every line
		var textLines = text.split( '<br />' );
		for ( var i=0; i <= textLines.length-1; i++ ) {
			textLines[i] = textLines[i].replace( /^\s/, '&nbsp;' );
		}
		text = textLines.join( '<br />' );
		return text;
	};

	/**
	 * Function to decode again.
	 */
	this.textDecode = function( text ) {
		text = text.replace( /<br\/>|<br \/>|<br>/gi, '\n' );
		// property & template cleaning:
		text = text.replace( /&nbsp;/g, ' ' );
		text = text.replace( /&#58;/g, ':' );
		text = text.replace( /&#123;/g, '}' );
		text = text.replace( /&#123;/g, '{' );
		text = text.replace( /&#91;/g, '[' );
		text = text.replace( /&#93;/g, ']' );
		text = text.replace( /&#47;/g, '/' );
		text = text.replace( /&#92;/g, '\\' );
		return text;
	};

	/**
	 * 
	 */
	this.formReset = function() {
		this.textareaIsDefault = true;
		this.replyCommentName = null;
		if (this.ratingValue !== null) {
			var oldhtmlid = '#collabComFormRating' + String( this.ratingValue + 2 );
			$( oldhtmlid ).attr( 'src', $( oldhtmlid ).attr(
				'src' ).replace( /_active/g, '_inactive' )
			);
			this.ratingValue = null;
		}
		$( '#collabComForm' ).get( 0 ).reset();
		$( '#collabComForm' ).toggle( 'slow' );
	};

	/**
	 * onClick event function for textarea
	 */
	this.selectTextarea = function() {
		//check if we still have the form default in here
		if ( this.textareaIsDefault ) {
			$( '#collabComFormTextarea' ).select();
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
		var ratingHTML = $( htmlid );
		var ratingImg = wgCEScriptPath + '/skins/Comment/icons/';
		var oldhtmlid = '#collabComFormRating' + String( this.ratingValue + 2 );
		$( oldhtmlid ).attr( 'src', $( oldhtmlid ).attr(
			'src' ).replace( /_active/g, '_inactive' )
		);
		if ( this.ratingValue == ratingValue ) {
			// deselect...
			this.ratingValue = null;
			return true;
		}
		switch ( ratingValue ) {
			case -1 :ratingHTML.attr( 'src', ratingImg + 'bad_active.png' );
				break;
			case 0 :ratingHTML.attr( 'src', ratingImg + 'neutral_active.png' );
				break;
			case 1 :ratingHTML.attr( 'src', ratingImg + 'good_active.png' );
				break;
		}
		this.ratingValue = ratingValue;
		return true;
	};

	/**
	 * switch for rating (in edit mode)
	 */
	this.switchEditRating = function( htmlid, ratingValue ) {
		var ratingHTML = $( htmlid );
		var ratingImg = wgCEScriptPath + '/skins/Comment/icons/';
		var oldhtmlid = '#collabComEditFormRating' + String( this.editRatingValue + 2 );
		$( oldhtmlid ).attr( 'src', $( oldhtmlid ).attr(
			'src' ).replace( /_active/g, '_inactive' )
		);
		if ( this.editRatingValue == ratingValue ) {
			// deselect...
			this.editRatingValue = null;
			return true;
		}
		switch ( ratingValue ) {
			case -1 :ratingHTML.attr( 'src', ratingImg + 'bad_active.png' );
				break;
			case 0 :ratingHTML.attr( 'src', ratingImg + 'neutral_active.png' );
				break;
			case 1 :ratingHTML.attr( 'src', ratingImg + 'good_active.png' );
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
		var comToggle = $( '#collabComToggle' );
		var commentResults = $( '#collabComResults' );
		var newComToggleText = '';
		if ( commentResults.css( 'display' ) === 'block' ) {
			newComToggleText = ceLanguage.getMessage( 'ce_com_show' );
		} else {
			newComToggleText = ceLanguage.getMessage( 'ce_com_hide' );
		}
		comToggle.html( ' | ' + newComToggleText );
		commentResults.toggle( 'slow' );
		//hide "Add" and "View"
		$( '#collabComFormToggle, #collabComViewToggle, #collabComFileToggle' ).toggle();
		return true;
	};

	/**
	 * fired when the "View" select box has changed
	 * Determines which view is requested and calls the appropriate function
	 */
	this.toggleView = function() {
		var newView = parseInt( $( '#collabComViewToggle option:selected' ).val() );
		if ( newView === this.currentView ) {
			return true;
		} else {
			if ( newView === 0 ) {
				this.showThreaded();
			} else if ( newView === 1 ) {
				this.showFlat();
			}
		}
		return true;
	};

	/**
	 *  This functions either toggles one given or all file attachments for comments
	 *  depending on the parameter commentID.
	 *  
	 * @param commentID: the HTML id of the comment to toggle
	 * @return true
	 */
	this.toggleFileAttachment = function( commentID ) {
		if ( typeof commentID !== 'string' || commentID === '' ) {
			$( '.collabComResFileAttach' ).toggle( 'slow' );
		} else {
			$( ' .collanComResFileAttach', $( '#' + commentID ) ).toggle( 'slow' );
		}
		return true;
	};

	/**
	 * Provides functionality to display the comments in flat mode
	 * without indention. This is the normal behaviour of the template result printer
	 * so we can use the cloned and stored version and just rebind the events.
	 */
	this.showFlat = function() {
		// cancel edit action first
		if ( this.editMode ) {
			this.cancelCommentEditForm( this.editCommentName );
		}
		$( '#collabComResults' ).html( $( this.savedStructure.html() ) );
		// rebind events
		var resultComments = $( '.collabComRes' );
		$.each( resultComments, function( i, resCom ) {
			var resComID = $( resCom ).attr( 'id' );
			// deletion
			$( '.collabComDel', resCom ).bind( 'click', function() {
				$( '#' + resComID ).css(
					'background-color', '#FAFAD2'
				);
			});
			// edit
			$( '.collabComEdit', resCom ).bind( 'click', function() {
				ceCommentForm.editCommentForm( resComID );
			});
			// reply
			$( '.collabComReply', resCom ).bind( 'click', function() {
				ceCommentForm.replyCommentForm( resComID );
			});
		});
		this.currentView = 1;
		// overlays
		this.bindOverlays();
		if ( $( '#collabComFileToggle > input' ).attr( 'checked' ) === false
			&& $( '.collabComResFileAttach:first' ).filter( ':visible' ) )
		{
			$( '.collabComResFileAttach' ).hide();
		}
		return true;
	};

	/**
	 * Provides functionality to display comments in threaded mode.
	 * Every comment that is a reply to another comment is indented and inserted
	 * at the specific position.
	 */
	this.showThreaded = function() {
		// format comments
		$( '.collabComRes' ).each( function( i, resCom ) {
			var superComID = $( '.collabComResInfoSuper', resCom ).html();
			if ( superComID ) {
				var resMargin = $( '#' + superComID ).css( 'margin-left' );
				var newMargin = '30';
				if ( typeof resMargin !== 'undefined' ) {
					newMargin = ( parseInt( resMargin ) + 30 );
				}
				// check if there are "child" comments
				var name = ceCommentForm.getLastChildComment( superComID );
				if ( name !== superComID ) {
					// child found. add behind.
					$( '#' + name ).after( $( resCom ) );
				} else {
					// no child found
					$( '#' + superComID ).after( $( resCom ) );
				}
				$( resCom ).css( 'margin-left', newMargin + 'px' );
				$( resCom ).addClass( 'comRearranged' );
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
	this.getLastChildComment = function( commentID ) {
		var lastChildComment;
		var childComments = $( '.comRearranged' ).filter( function() {
			var indSuperComID = $( '.collabComResInfoSuper', this );
			return indSuperComID.html() == commentID;
		});
		if ( childComments.length > 0 ) {
			lastChildComment = childComments[childComments.length-1];
			return this.getLastChildComment( $( lastChildComment ).attr( 'id' ) );
		} else {
			return commentID;
		}
	};
	
	/**
	 * This function determines the number of comments, number of ratings 
	 * and the average rating value and stores them in object variables.
	 */
	this.setCommentQuantities = function() {
		this.numOfComments = $( '.collabComRes' ).length;
		this.numOfRatings = $( '.collabComResRatingIcon' ).length;
		if ( this.numOfRatings === 0 ) {
			return true;
		}
		var avgRating = 0;
		$( '.collabComResRatingIcon' ).each( function() {
			if ( $( 'img', this ).attr( 'src' ).indexOf( 'Bad' ) >=0 ) {
				avgRating--;
			} else if ( $( 'img', this ).attr( 'src' ).indexOf( 'Good' ) >=0 ) {
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
		//var comHeader = $('.collabComInternHeader');
		this.setCommentQuantities();
		var expandedHead = this.addHeaderText();
		if ( expandedHead === true ) {
			this.addCommentToggler();
			if ( typeof wgCECommentsDisabled === 'undefined'
				|| wgCECommentsDisabled === false )
			{
				this.addFormToggler( true ); //remove header
			}
			this.addHeaderView();
			this.addFileToggler();
			this.addHeaderRating();
		}
		return true;
	};
	
	/**
	 * Wrapper function for the header
	 */
	this.addHeaderText = function() {
		if ( this.numOfComments > 0 ) {
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
	 * 
	 * @return false
	 */
	this.addDefaultHeaderText = function() {
		this.addFormToggler( false );
		$( '#collabComFormToggle' ).css( 'font-weight', 'bold');
		return false;
	};

	/**
	 * The extended header text also contains the number of comments.
	 * Sthg like "Comments (2)"
	 * @returns true
	 */
	this.addExtendedHeaderText = function() {
		$( '<span>', {
			'class' : 'collabComInternComment',
			'text' : ceLanguage.getMessage( 'ce_com_ext_header' ) +
				' (' + this.numOfComments + ')'
		}).appendTo( '.collabComInternHeader' );
		return true;
	};

	/**
	 * This function adds the toggle element for the comments.
	 */
	this.addCommentToggler = function() {
		$( '<span>', {
			'id' : 'collabComToggle',
			'title' : ceLanguage.getMessage( 'ce_com_toggle_tooltip' ),
			'text' : ' | ' + ceLanguage.getMessage( 'ce_com_hide' ),
			'click': function(){
				ceCommentForm.toggleComments();
			}
		}).appendTo( '.collabComInternHeader' );
		return true;
	};
	
	/**
	 * This function adds the toggle element for the comment form.
	 * 
	 * @param {boolean} withPipe Indicates if the Text should be extended with a leading pipe symbol
	 */
	this.addFormToggler = function( withPipe ) {
		var toggleSpan ='';
		if ( typeof wgCEUserCanEdit !== 'undefined' && wgCEUserCanEdit === false ) {
			toggleSpan = $( '<span>', {
				'id' : 'collabComFormToggle',
				'style' : 'color: grey; cursor: default;',
				'title' : ceLanguage.getMessage( 'ce_form_toggle_no_edit_tooltip' ),
				'text' : ( withPipe? ' | ' : ' ' ) + ceLanguage.getMessage( 'ce_com_default_header' )
			});
		} else {
			toggleSpan = $( '<span>', {
				'id' : 'collabComFormToggle',
				'title' : ceLanguage.getMessage( 'ce_form_toggle_tooltip' ),
				'text' : ( withPipe? ' | ' : ' ' ) + ceLanguage.getMessage( 'ce_com_default_header' ),
				'click' : function() {
					$( '#collabComForm' ).toggle( 'slow' );
				}
			});
		}
		$( '.collabComInternHeader' ).append( $( toggleSpan ) );
		return true;
	};

	/**
	 * Creates the "change view" select box element.
	 * 
	 * @return true
	 */
	this.addHeaderView = function() {
		// "change view" functionality
		var viewSpan = $( '<span>', {
			'id' : 'collabComViewToggle',
			'text' : ' | ' + ceLanguage.getMessage( 'ce_com_view' ) + ': '
		});

		var selectEl = $( '<select>', {
			'change' : function() {
				ceCommentForm.toggleView();
			}
		});
		try {
			selectEl.get(0).add( new Option(
				ceLanguage.getMessage( 'ce_com_view_threaded' ),
				0, true, true), null
			); // standards compliant; doesn't work in IE
		} catch(ex) {
			selectEl.get(0).add( new Option(
				ceLanguage.getMessage( 'ce_com_view_threaded' ),
				0, true, true)
			); // IE only
		}
		try {
			selectEl.get(0).add( new Option(
				ceLanguage.getMessage( 'ce_com_view_flat' ), 1 ), null
			); // standards compliant; doesn't work in IE
		} catch(ex) {
			selectEl.get(0).add( new Option(
				ceLanguage.getMessage( 'ce_com_view_flat' ), 1 )
			); // IE only
		}
		$( viewSpan ).append( $( selectEl ) );
		$( '.collabComInternHeader' ).append( $( viewSpan ) );
		this.currentView = 0;
		return true;
	};

	/**
	 * Creates the HTML to change the display of file attachments and attaches it to the header item.
	 *
	 * @return boolean
	 */
	this.addFileToggler = function() {
		// we can skip this if there are no file attachments at all.
		if ( $('.collabComResFileAttach').length === 0 ) {
			return false;
		}
		var fileSpan = $( '<span>', {
			'id' : 'collabComFileToggle',
			'text' : ' | ' + ceLanguage.getMessage( 'ce_com_file_toggle' ) + ': '
		})
		var checked = null;
		if ( $('.collabComResFileAttach:first').filter(':visible') ) {
			checked = ['checked', 'checked'];
		}
		var tmp = document.createElement( 'input' );
		tmp.setAttribute( 'type', 'checkbox' );
		$( tmp ).attr( 'ckecked', checked )
			.change( function() {
				ceCommentForm.toggleFileAttachment();
			})
			.appendTo( $( fileSpan ) );
		$( '.collabComInternHeader' ).append( $( fileSpan ) );
		return true;
	};

	/**
	 * Adds the text and icon for the average rating.
	 */
	this.addHeaderRating = function() {
		if ( this.numOfRatings > 0 ) {
			var ratingSpan = $( '<span>', {
				'class' : 'collabComInternAvg',
				'text' : ceLanguage.getMessage( 'ce_com_rating_text' ) + ' ' + 
					this.numOfRatings + ' ' + ceLanguage.getMessage( 'ce_com_rating_text2' )
			});

			var ratingIconDiv = $( '<div>', {
				'class' : 'collabComInternRatingIcon'
			});
			var ratingIcon = $( '<img>' );
			var ratingIconSrc = wgCEScriptPath + '/skins/Comment/icons/';
			if ( this.averageRating < -0.33 ) {
				$( ratingIcon ).attr( 'src', ratingIconSrc + 'bad_active.png' );
			} else if ( this.averageRating >= -0.33 && this.averageRating <= 0.33 ) {
				$( ratingIcon ).attr( 'src', ratingIconSrc + 'neutral_active.png' );
			} else if ( this.averageRating > 0.33 ) {
				$( ratingIcon ).attr( 'src', ratingIconSrc + 'good_active.png' );
			}
			$( ratingIconDiv ).append( $( ratingIcon ) );
			$( ratingSpan ).append( $( ratingIconDiv ) );
			$( '.collabComInternHeader' ).append( $( ratingSpan ) );
		}
		return true;
	};

	/**
	 * Creates the complete overlay structure and returns it.
	 */
	this.createOverlay = function( num, pageName ) {
		var overlayName = 'overlay_' + num,
			tmp,
			overlayDivEl,
			overlayFullDeleteDiv,
			overlayFullDeleteDivContent;
		// divs
		overlayDivEl = $( '<div>', {
			'id' : overlayName,
			'class' : 'ceOverlay'
		});
		$( '<div>', {
			'class' : 'ceOverlayDetails',
			'text' : ceLanguage.getMessage( 'ce_delete' )
		}).appendTo( overlayDivEl );
		overlayFullDeleteDiv = $( '<div>', {
			'class' : 'ceOverlayFullDeleteDiv'
		}).appendTo( overlayDivEl );
		tmp = document.createElement( 'input' );
		tmp.setAttribute( 'type', 'checkbox' );
		$( tmp ).addClass( 'ceOverlayFullDeleteCheckbox')
			.attr( 'name', 'ceFullDelete' )
			.appendTo( overlayFullDeleteDiv );
		overlayFullDeleteDivContent = document.createTextNode(
			ceLanguage.getMessage( 'ce_full_delete' ) 
		);
		overlayFullDeleteDiv.append( overlayFullDeleteDivContent );

		// cancel button
		var cancelButtonDiv = $( '<div>', {
			'class' : 'ceOverlayCancelButtonDiv',
			'click' : function() {
				$( '#' + pageName ).css( 'background-color', '' );
			}
		});
		tmp = document.createElement( 'input' );
		tmp.setAttribute( 'type', 'button' );
		$( tmp ).addClass( 'ceOverlayCancelButton close' )
			.val( ceLanguage.getMessage( 'ce_cancel_button' ) )
			.appendTo( cancelButtonDiv );
		overlayDivEl.append( cancelButtonDiv );

		// delete button
		var deleteButtonDiv = $( '<div>', {
			'class' : 'ceOverlayDeleteButtonDiv',
			'click' : function() {
			ceCommentForm.deleteComment( escape( pageName ), overlayName );
			}
		});
		tmp = document.createElement( 'input' );
		tmp.setAttribute( 'type', 'button' );
		$( tmp ).addClass( 'ceOverlayDeleteButton' )
			.val( ceLanguage.getMessage( 'ce_delete_button' ) )
			.appendTo( deleteButtonDiv );
		overlayDivEl.append( deleteButtonDiv );
		return overlayDivEl;
	};
	
	/**
	 *
	 */
	this.bindOverlays = function() {
		var overlays = $('.collabComDel');
		$.each( overlays, function( i, overlay ) {
			var overlayID = $(overlay).attr('id');
			$(overlay).overlay({
				api: true,
				// when overlay is closed, remove color highlighting
				onClose: function() {
					$( '.collabComRes' ).removeClass( 'collabComDelSelected' );
				},
				onBeforeLoad: function() {
					ceCommentForm.controlFullDeleteOptions( 
						overlay, i, overlayID.replace ('ceDel', '' )
					);
				}
			});
		});
	}

	/**
	 *
	 */
	this.controlFullDeleteOptions = function( overlay, overlayNum, commentID ) {
		if ( typeof wgCEEnableFullDeletion !== 'undefined' && this.currentView === 0
			&& ( typeof wgCEUserIsSysop !== 'undefined'
			&& wgCEUserIsSysop !== null && wgCEUserIsSysop !== false ) )
		{
			var moooo = $( '.ceOverlayFullDeleteDiv', $( '#overlay_' + overlayNum ) ).show();
		} else {
			$( '.ceOverlayFullDeleteDiv', $( '#overlay_' + overlayNum ) ).hide();
		}
		return true;
	}
	
	/**
	 * This function preloads images to prevent 'loading gap'.
	 */
	this.preloadImages = function() {
		var preloadImages = new Array();
		preloadImages[0] = wgCEScriptPath + '/skins/Comment/icons/good_active.png';
		preloadImages[1] = wgCEScriptPath + '/skins/Comment/icons/neutral_active.png';
		preloadImages[2] = wgCEScriptPath + '/skins/Comment/icons/bad_active.png';
		preloadImages[3] = wgCEScriptPath + '/skins/Comment/icons/Edit_button2_Active.png';
		preloadImages[4] = wgCEScriptPath + '/skins/Comment/icons/DeletedComment.png';
		for ( i = 0; i < preloadImages.length; i++ ) {
			var preloadImage = new Image();
			preloadImage.src = preloadImages[i];
		}
	};
}
//Set global variable for accessing comment form functions
var ceCommentForm;

//Initialize Comment functions if page is loaded
$(document).ready(
	function() {
		ceCommentForm = new CECommentForm();
	}
);

/**
 * This function takes care about missing event handlers.
 * (It hasn't been possible to add this in the Template itself)
 * It also creates Edit and Delete links for users that own the appropriate right.
 * The current DOM structure is saved to be reused in "flat view".
 */
$(document).ready(
	function() {
		var	domElement,
			collabFormExists = $( '#collabComForm' ).length > 0 ? true : false;
		if ( collabFormExists ) {
			ceCommentForm.preloadImages();
		}
		// format comments
		var resultComments = $( '.collabComRes' );
		var overlayID = 0;
		$.each( resultComments, function( i, resCom ) {
			var resComID = $( resCom ).attr( 'id' );
			var resComDeleted = $( '.collabComResDeletion', resCom );
			if ( resComDeleted.html() === 'true' ) {
				$( '.collabComResText', resCom ).addClass( 'collabComDeleted' );
				$( '.collabComResPerson img', resCom ).attr(
					'src', wgCEScriptPath + '/skins/Comment/icons/DeletedComment.png'
				);
				$( '.collabComResRating', resCom ).remove();
				// this comment has been marked as deleted -> step out
				return true;
			}
			var resComEdit = $( '.collabComEditInfo', resCom );
			if ( resComEdit ) {
				$( '.collabComEditorPre', resCom ).html(
					ceLanguage.getMessage( 'ce_edit_intro' )
				);
				$( '.collabComEditDatePre', resCom ).html(
					ceLanguage.getMessage( 'ce_edit_date_intro' )
				);
			}
			var commentPerson= $( '.collabComResUsername > a', resCom ).html();
			if ( !commentPerson ) {
				commentPerson = '';
			} else {
				commentPerson = commentPerson.split( ':' );
				commentPerson = commentPerson.pop();
			}
			if ( ( typeof wgCEUserIsSysop !== 'undefined'
				&& wgCEUserIsSysop !== null && wgCEUserIsSysop !== false )
				|| (mw.config.get( 'wgUserName' ) !== null
				&& commentPerson === mw.config.get( 'wgUserName' ) ) )
			{
				//Overlay for deleting comments
				var overlayDiv = ceCommentForm.createOverlay( overlayID, resComID );
				domElement = $( '<span>', {
					'id' : 'ceDel' + resComID,
					'class' : 'collabComDel',
					'title' : ceLanguage.getMessage( 'ce_delete_title' ),
					'rel' : '#overlay_' + overlayID++,
					'click' : function() {
						$( '#' + resComID ).addClass( 'collabComDelSelected' );
					}
				});
				$( '<img>', {
					'class' : 'collabComDeleteImg',
					'src' : wgCEScriptPath + '/skins/Comment/icons/Delete_button.png'
				}).appendTo( domElement );
				$( '.collabComResDate', resCom ).after( domElement );
				$( '#collabComResults' ).after( overlayDiv );

				if ( typeof wgCECommentsDisabled === 'undefined'
					|| wgCECommentsDisabled === false )
				{
					// edit
					domElement = $( '<span>', {
						'class' : 'collabComEdit',
						'title' : ceLanguage.getMessage( 'ce_edit_title' ),
						'click' : function() {
							ceCommentForm.editCommentForm( resComID );
						}
					});
					$( '<img>', {
						'class' : 'collabComEditImg',
						'src' : wgCEScriptPath + '/skins/Comment/icons/Edit_button2.png'
					}).appendTo( domElement );
					$( '.collabComResDate', resCom ).after( domElement );
					// cancel edit
					domElement = $( '<span>', {
						'class' : 'collabComEditCancel',
						'title' : ceLanguage.getMessage( 'ce_edit_cancel_title' ),
						'click' : function() {
							ceCommentForm.cancelCommentEditForm( resComID );
						}
					});
					$( '<img>', {
						'class' : 'collabComEditCancelImg',
						'src' : wgCEScriptPath + '/skins/Comment/icons/Edit_button2_Active.png'
					}).appendTo( domElement );
					$( '.collabComResDate', resCom ).after( domElement );
					domElement.hide();
				}
			}
			if ( ( typeof wgCECommentsDisabled === 'undefined' 
				|| wgCECommentsDisabled === false )
				&& typeof wgCEUserCanEdit === 'undefined' )
			{
				// reply
				domElement = $( '<span>', {
					'class' : 'collabComReply',
					'title' : ceLanguage.getMessage( 'ce_reply_title' ),
					'text' : ceLanguage.getMessage( 'ce_com_reply' ),
					'click' : function() {
						ceCommentForm.replyCommentForm( resComID );
					}
				}).insertAfter( $( '.collabComResText', resCom ) );

				$( '<img>', {
					'class' : 'collabComReplyImg',
					'src' : wgCEScriptPath + '/skins/Comment/icons/Reply_Comment.png'
				}).appendTo( domElement );
			}
			return true;
		});
		if ( collabFormExists ) {
			// build header
			ceCommentForm.buildHeader();
			//clone actual structure without events (bind them again later)
			ceCommentForm.savedStructure = $( '#collabComResults' ).clone();
			ceCommentForm.showThreaded();
			// toggle one time if there are comments available
			if ( resultComments.length !== 0 ) {
				var comToggle = $( '#collabComToggle' );
				var commentResults = $( '#collabComResults' );
				var newComToggleText = '';
				// handling default visibillity of comments
				if ( !wgCEShowCommentsExpanded ) {
					newComToggleText = ceLanguage.getMessage( 'ce_com_show' );
					commentResults.hide();
					//hide "Add" and "View"
					$( '#collabComFormToggle, #collabComViewToggle, #collabComFileToggle' )
						.toggle();
				} else {
					newComToggleText = ceLanguage.getMessage( 'ce_com_hide' );
					commentResults.show();
				}
				comToggle.html( ' | ' + newComToggleText );
				
			}
		}
	}
);

$(document).ready(
	function() {
		// did not work in the same ready function
		ceCommentForm.bindOverlays();
	}
);

/**
 * Function binding
 */
Function.prototype.bindToFunction = function( context ) {
	var func = this;
	return function() {
		return func.apply( context, arguments );
	};
};

/**
 * This class has been ported from the generalTools.js of SMWHalo
 * to remove the dependency.
 */
function CPendingIndicator( container ) {
	this.constructor = function( container ) {
		this.container = container;
		this.pendingIndicator = document.createElement( 'img' );
		$( this.pendingIndicator ).addClass( 'cependingElement' );
		$( this.pendingIndicator ).attr(
			'src', mw.config.get( 'wgServer' ) + mw.config.get( 'wgScriptPath' ) +
			'/extensions/Collaboration/skins/Comment/icons/ajax-loader.gif'
		);
		this.contentElement = null;
	};

	/**
	 * Shows pending indicator relative to given container or relative to initial container
	 * if container is not specified.
	 */
	this.show = function( container, alignment ) {
		//check if the content element is there
		if ( $( '#content' ) === null ) {
			return;
		}

		var alignOffset = 0;
		if ( typeof alignment !== 'undefined' ) {
			switch( alignment ) {
				case 'right': {
					if ( !container ) { 
						alignOffset = $( this.container ).offsetWidth - 16;
					} else {
						alignOffset = $( container ).offsetWidth - 16;
					}
					break;
				}
				case 'left':
					break;
			}
		}
			
		//if not already done, append the indicator to the content element so it can become visible
		if ( this.contentElement === null ) {
				this.contentElement = $( '#content' );
				this.contentElement.append( this.pendingIndicator );
		}
		if ( !container ) {
			var offSet = this.container.offset();
			this.pendingIndicator.style.left = ( alignOffset + offSet.left ) + 'px';
			this.pendingIndicator.style.top = ( offSet.top - $( document ).scrollTop() ) + 'px';
		} else {
			var offSet = $( '#container' ).offset();
			this.pendingIndicator.style.left = alignOffset + offSet.left + 'px';
			this.pendingIndicator.style.top = ( offSet.top - $( document ).scrollTop() ) + 'px';
		}
		// hmm, why does Element.show(...) not work here?
		this.pendingIndicator.style.display = 'block';
		this.pendingIndicator.style.visibility = 'visible';
	};
	
	/**
	 * Shows the pending indicator on the specified <element>. This works also
	 * in popup panels with a defined z-index.
	 */
	this.showOn = function( element ) {
		container = element.offsetParent;
		$( container ).insert( {top: this.pendingIndicator} );
		var pOff = $( element ).positionedOffset();
		this.pendingIndicator.style.left = pOff[0] + 'px';
		this.pendingIndicator.style.top  = pOff[1] + 'px';
		this.pendingIndicator.style.display = 'block';
		this.pendingIndicator.style.visibility = 'visible';
		this.pendingIndicator.style.position = 'absolute';
	};
	
	this.hide = function() {
		$( this.pendingIndicator ).hide();
	};

	this.remove = function() {
		$( this.pendingIndicator ).remove();
	};
	
	// Execute initialize on object creation
	this.constructor( container );
}

/*
 * Browser tools
 */
function CollaborationBrowserDetectLite() {

	var ua = navigator.userAgent.toLowerCase();

	// browser name
	this.isGecko     = ( ua.indexOf( 'gecko' ) != -1 ) || ( ua.indexOf( 'safari' ) != -1 ); // include Safari in isGecko
	this.isMozilla   = ( this.isGecko && ua.indexOf( 'gecko/' ) + 14 == ua.length );
	this.isNS        = ( ( this.isGecko ) ? ( ua.indexOf( 'netscape' ) != -1 ) : ( ( ua.indexOf( 'mozilla' ) != -1 ) && ( ua.indexOf( 'spoofer' ) == -1) && ( ua.indexOf( 'compatible' ) == -1 ) && ( ua.indexOf( 'opera' ) == -1 ) && ( ua.indexOf( 'webtv' ) == -1 ) && ( ua.indexOf( 'hotjava' ) == -1 ) ) );
	this.isIE        = ( ( ua.indexOf( 'msie' ) != -1 ) && ( ua.indexOf( 'opera' ) == -1 ) && ( ua.indexOf( 'webtv' ) == -1 ) );
	this.isOpera     = ( ua.indexOf( 'opera' ) != -1 );
	this.isSafari    = ( ua.indexOf( 'safari' ) != -1 );
	this.isKonqueror = ( ua.indexOf( 'konqueror' ) != -1 );
	this.isIcab      = ( ua.indexOf( 'icab') != -1 );
	this.isAol       = ( ua.indexOf( 'aol' ) != -1 );
	this.isWebtv     = ( ua.indexOf( 'webtv') != -1 );
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
CollaborationXMLTools.createDocumentFromString = function( xmlText ) {
	// create empty treeview
	if ( C_bd.isGeckoOrOpera ) {
		var parser = new DOMParser();
		var xmlDoc = parser.parseFromString( xmlText, 'text/xml' );
	} else if ( C_bd.isIE ) {
		var xmlDoc = new ActiveXObject( 'Microsoft.XMLDOM' )
		xmlDoc.async = 'false';
		xmlDoc.loadXML( xmlText );
	}
	return xmlDoc;
}
})( jQuery );