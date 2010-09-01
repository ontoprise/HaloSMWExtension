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
 *  * 
 */
function CECommentForm() {

	// Variables
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
		
		// textarea
		var textArea = ($jq('#collabComFormTextarea').val())? $jq('#collabComFormTextarea').val(): '';
		if(textArea.blank() || this.textareaIsDefault) {
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
		// remove script tags
		textArea = textArea.stripScripts();
		// escape html chars
		textArea = textArea.escapeHTML();
		textArea = this.textEncode(textArea);
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
	};

	/**
	 * The callback function for createNewPage
	 * @param request
	 */
	this.processFormCallback = function(request){

		//alert(request.responseText);
		var resultDOM = this.XMLResult = CollaborationXMLTools.createDocumentFromString(request.responseText);	
		//alert(resultDOM);
		
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
				comMessage.get(0).innerHTML = htmlmsg + ceLanguage.getMessage('ce_reload');
				//add pending span
				var pendingSpan = new Element('span', 
						{ 'id' : 'collabComFormPending' }
				);
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
				comMessage.get(0).innerHTML = htmlmsg;
			} else {
				//sthg's really gone wrong
			}
		}

		return false;
	};

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
	this.createNewPage = function(wikiurl, wikipath, pageName, pageContent,
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
	};

	/**
	 * The callback function for createNewPage
	 */
	this.createNewPageCallback = function(request){
		return request.responseText;
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

	this.formReset = function() {
		this.textareaIsDefault = true;
		
		if (this.ratingValue != null) {
			var oldhtmlid = '#collabComFormRating' + String(this.ratingValue + 2);
			$jq(oldhtmlid).src = $jq(oldhtmlid).src.replace(/active/g, 'inactive');
			this.ratingValue = null;
		}
		$jq('#collabComForm').reset();
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

		if ( this.ratingValue == ratingValue ) {
			//deselect...
			var oldhtmlid = '#collabComFormRating' + String(this.ratingValue + 2);
			$jq(oldhtmlid).attr('src', $jq(oldhtmlid).attr('src').replace(/_active/g, '_inactive'));
			this.ratingValue = null;
			return true;
		}

		if ( this.ratingValue != null ) {
			// sthg has been selected before. reset icon.
			// collabComFormRatingX with X = ratingValue +2;
			var oldhtmlid = '#collabComFormRating' + String(this.ratingValue + 2);
			$jq(oldhtmlid).attr('src', $jq(oldhtmlid).attr('src').replace(/active/g, 'inactive'));
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
	}
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
		Element.addClassName(this.pendingIndicator, "cependingElement");
		this.pendingIndicator.setAttribute("src", 
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
		Element.hide(this.pendingIndicator);
	};

	this.remove = function() {
		Element.remove(this.pendingIndicator);
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

//Add delete links if page is loaded
$jq(document).ready(
	function(){
		// add JS to header items
		$jq('#collabComToggle').attr('onClick', 'ceCommentForm.toggleComments();');
		$jq('#collabComToggle').attr('title', ceLanguage.getMessage('ce_com_toggle_tooltip'));
		$jq('#collabComFormToggle').attr('onClick', '$jq(\'#collabComForm\').toggle(\'slow\');');
		$jq('#collabComFormToggle').attr('title', ceLanguage.getMessage('ce_form_toggle_tooltip'));
	}
);