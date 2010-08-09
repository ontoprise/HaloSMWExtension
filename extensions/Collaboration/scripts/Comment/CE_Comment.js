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
		var cf = $('collabComForm');
		cf.disable();
		
		//2. and add pending indicator
		
		if (this.pendingIndicatorCF == null) {
			this.pendingIndicatorCF = new CPendingIndicator($('collabComFormTextarea'));
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
		var textArea = ($('collabComFormTextarea').value)? $('collabComFormTextarea').value: '';
		//remove leading and trailing whitespaces
		textArea = textArea.strip();
		if(textArea.blank() || this.textareaIsDefault) {
			this.pendingIndicatorCF.hide();
			$('collabComFormMessage').setAttribute('class', 'failure');
			$('collabComFormMessage').innerHTML = 'You didn\'t enter a valid comment.';
			//enable form again
			$('collabComForm').enable();
			return false;
		}
		//remove script tags
		textArea = textArea.stripScripts();
		//escape html chars
		textArea = textArea.escapeHTML();
		//property & template cleaning:
		textArea = textArea.replace(/:/g, '&#58;');
		textArea = textArea.replace(/\{/g, '&#123;');
		textArea = textArea.replace(/\{/g, '&#123;');
		textArea = textArea.replace(/\[/g, '&#91;');
		textArea = textArea.replace(/\]/g, '&#93;');
		textArea = textArea.replace(/\//g, '&#47;');
		textArea = textArea.replace(/\\/g, '&#92;');
		//TODO: wgUserName is null, when not logged in!
		var userNameString = '';
		if( wgUserName != null && ceUserNS != null ) {
			userNameString = '|CommentPerson=' + ceUserNS + ':' + wgUserName;
		} else {
			userNameString = '|CommentPerson=';
		}

		var pageContent = '{{Comment' +
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
		var resultDOM = this.XMLResult = CollaborationXMLTools.createDocumentFromString(request.responseText);	
		//alert(resultDOM);
		
		var valueEl = resultDOM.getElementsByTagName('value')[0];
		
		var htmlmsg = resultDOM.getElementsByTagName('message')[0].firstChild.nodeValue;
		
		if ( valueEl.nodeType == 1 ) {
			var valueCode = valueEl.firstChild.nodeValue
			if ( valueCode == 0 ){
				//fine.
				//reset, hide and enable form again
				this.pendingIndicatorCF.hide();
				$('collabComForm').reset();
				$('collabComForm').hide();
				$('collabComForm').enable();

				$('collabComFormMessage').show();
				$('collabComFormMessage').setAttribute('class', 'success');
				$('collabComFormMessage').innerHTML = htmlmsg + ' Page is reloading...';
				//add pending span
				var pendingSpan = new Element('span', 
						{ 'id' : 'collabComFormPending' }
				);
				$('collabComFormMessage').appendChild(pendingSpan);
				if (this.pendingIndicatorMsg == null) {
					this.pendingIndicatorMsg = new CPendingIndicator($('collabComFormPending'));
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
				$('collabComFormMessage').setAttribute('class', 'failure');
				$('collabComFormMessage').innerHTML = htmlmsg;
				$('collabComFormMessage').show();
				//reset and enable form again
				//$('collabComForm').reset();
				$('collabComForm').enable();
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
			var oldhtmlid = 'collabComFormRating' + String(this.ratingValue + 2);
			$(oldhtmlid).src = $(oldhtmlid).src.replace(/active/g, 'inactive');
			this.ratingValue = null;
		}
		$('collabComForm').reset();
	},
	
	/**
	 * onClick event function for textarea
	 */
	selectTextarea: function() {
		//check if we still have the form default in here
		if (this.textareaIsDefault) {
			$('collabComFormTextarea').activate();
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
			//deselect...
			var oldhtmlid = 'collabComFormRating' + String(this.ratingValue + 2);
			$(oldhtmlid).src = $(oldhtmlid).src.replace(/_active/g, '_inactive');
			this.ratingValue = null;
			return true;
		}

		if ( this.ratingValue != null ) {
			// sthg has been selected before. reset icon.
			// collabComFormRatingX with X = ratingValue +2;
			var oldhtmlid = 'collabComFormRating' + String(this.ratingValue + 2);
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
	}
}


//Singleton of this class

var ceCommentForm = new CECommentForm();

/**
 * This class has been ported from the generalTools.js of SMWHalo
 * to remove the dependency.
 */
var CPendingIndicator = Class.create();
CPendingIndicator.prototype = {
	initialize: function(container) {
		this.container = container;
		this.pendingIndicator = document.createElement("img");
		Element.addClassName(this.pendingIndicator, "obpendingElement");
		this.pendingIndicator.setAttribute("src", 
			wgServer + wgScriptPath + "/extensions/Collaboration/skins/Comment/icons/ajax-loader.gif");
		this.contentElement = null;
	},
	
	/**
	 * Shows pending indicator relative to given container or relative to initial container
	 * if container is not specified.
	 */
	show: function(container, alignment) {
		
		//check if the content element is there
		if($("content") == null){
			return;
		}
		
		var alignOffset = 0;
		if (alignment != undefined) {
			switch(alignment) {
				case "right": { 
					if (!container) { 
						alignOffset = $(this.container).offsetWidth - 16;
					} else {
						alignOffset = $(container).offsetWidth - 16;
					}
					
					break;
				}
				case "left": break;
			}
		}
			
		//if not already done, append the indicator to the content element so it can become visible
		if(this.contentElement == null) {
				this.contentElement = $("content");
				this.contentElement.appendChild(this.pendingIndicator);
		}
		if (!container) {
			this.pendingIndicator.style.left = (alignOffset + Position.cumulativeOffset(this.container)[0]-Position.realOffset(this.container)[0])+"px";
			this.pendingIndicator.style.top = (Position.cumulativeOffset(this.container)[1]-Position.realOffset(this.container)[1]+this.container.scrollTop)+"px";
		} else {
			this.pendingIndicator.style.left = (alignOffset + Position.cumulativeOffset($(container))[0]-Position.realOffset($(container))[0])+"px";
			this.pendingIndicator.style.top = (Position.cumulativeOffset($(container))[1]-Position.realOffset($(container))[1]+$(container).scrollTop)+"px";
		}
		// hmm, why does Element.show(...) not work here?
		this.pendingIndicator.style.display="block";
		this.pendingIndicator.style.visibility="visible";

	},
	
	/**
	 * Shows the pending indicator on the specified <element>. This works also
	 * in popup panels with a defined z-index.
	 */
	showOn: function(element) {
		container = element.offsetParent;
		$(container).insert({top: this.pendingIndicator});
		var pOff = $(element).positionedOffset();
		this.pendingIndicator.style.left = pOff[0]+"px";
		this.pendingIndicator.style.top  = pOff[1]+"px";
		this.pendingIndicator.style.display="block";
		this.pendingIndicator.style.visibility="visible";
		this.pendingIndicator.style.position = "absolute";
		
	},
	
	hide: function() {
		Element.hide(this.pendingIndicator);
	},

	remove: function() {
		Element.remove(this.pendingIndicator);
	}
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


Event.observe(window, 'load', function() {
	if( typeof( cegUserIsSysop ) != "undefined" && cegUserIsSysop != null && cegUserIsSysop != false) {

		var resultComments = $$('.collabComResInfo');

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
					'class' : 'plainlinks'
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
