/*
* This interface provides basic functionality for the wiki edit mode.
* As the syntax highlighted EditArea can be switched off, programs do
* not know if they have to use the access methods of a html textarea
* or of the JS EditArea. This interface provides basic methods for
* textareas / EditArea and decides which ones to use.
* The abstraction works for both Mozilla and IE browsers.
* @author Markus Nitsche, 2007
*/

/**
 * @file
 * @ingroup SMWHaloSemanticToolbar
 * @author Markus Nitsche
 */

var editAreaName = "wpTextbox1";

var SMWEditInterface = Class.create();
SMWEditInterface.prototype ={

	initialize: function() {
		this.editAreaName = "wpTextbox1";
		// IE loses the selection in the text area if it loses the focus
		// the current selection (range) has to be stored for later operations
		this.currentRange = null;

	},

	focus: function(){
		if ( $(editAreaName) && $(editAreaName).getStyle('display')!='none'){
			$(editAreaName).focus();
		} 
	},

	setSelectionRange: function(start, end){
		if ( $(editAreaName) && $(editAreaName).getStyle('display')!='none'){
			SMWEditArea = $(editAreaName);
			if (document.selection  && !is_gecko) {
				var rng = SMWEditArea.createTextRange();
				var text = rng.text;
				var offset = 0;
				for (var i = 0; i < start; i++) {
					if (text.charAt(i) == '\n') {
						offset++;
					}
				}
				rng.collapse();
				rng.moveStart('character',start-offset);
				rng.moveEnd('character',end-start);
				rng.select();
				rng.scrollIntoView();
			} else  {
				// Mozilla
				SMWEditArea.selectionStart = start;
				SMWEditArea.selectionEnd = end;
				SMWEditArea.caretPos = start;
			}
		} 
	},

	/*
	 * If the current selection is within an annotation (i.e. within [[...]])
	 * and only spaces are between the selection an the brackets,
	 * the selection is enlarged to comprise the brackets. 
	 * Otherwise the selection is trimmed i.e. spaces at the beginning and
	 * the end are skipped.
	 */
	selectCompleteAnnotation: function(){
		if ( $(editAreaName) && $(editAreaName).getStyle('display')!='none'){
			SMWEditArea = $(editAreaName);
			var found = false;
			if (document.selection  && !is_gecko) {
				var rng = document.selection.createRange();
				var moved = 1;
				rng.moveStart('character',-1);
				while (rng.text.charAt(0) == ' '
				       && rng.moveStart('character',-1) != 0) {
					moved++;
				}
				while (rng.text.charAt(0) == '['
				       && rng.moveStart('character',-1) != 0) {
					moved++;
					found = true;
				}
				if (found) {
					// brackets found => move the start of the selection					
					rng.moveStart('character', 1);
				} else {
					// skip all spaces at the beginning of the selection
					rng.moveStart('character', moved);
					while (rng.text.charAt(0) == ' '
						   && rng.moveStart('character',1) != 0) {
					}
				}

				found = false;
				moved = 1;
				rng.moveEnd('character',1);
				while (rng.text.charAt(rng.text.length-1) == ' '
				       && rng.moveEnd('character',1) != 0) {
					moved++;
				}
				while (rng.text.charAt(rng.text.length-1) == ']'
				       && rng.moveEnd('character',1) != 0) {
					moved++;
					found = true;
				}
				if (found) {
					// brackets found => move the end of the selection					
					rng.moveEnd('character', -1);
				} else {
					// skip all spaces at the end of the selection
					rng.moveEnd('character', -moved);
					while (rng.text.charAt(rng.text.length-1) == ' '
						   && rng.moveEnd('character',-1) != 0) {
					}
				}
				this.currentRange = rng.duplicate();
				rng.select();
			} else  {
				// Search for opening brackets at the beginning of the selection
				var start = SMWEditArea.selectionStart-1;
				while (start >= 0 && SMWEditArea.value.charAt(start) == ' ') {
					--start;
				}
				while (start >= 0 && SMWEditArea.value.charAt(start) == '[') {
					--start;
					found = true;
				}
				start++;
				if (!found) {
					// no brackets found => skip all spaces at the beginning
					start = SMWEditArea.selectionStart;
					while (start < SMWEditArea.value.length
					       && SMWEditArea.value.charAt(start) == ' ') {
						++start;
					}
				}
				found = false;
				// Search for closing brackets at the end of the selection
				var end = SMWEditArea.selectionEnd;
				while (end < SMWEditArea.value.length
				       && SMWEditArea.value.charAt(end) == ' ') {
					++end;
				}
				while (end < SMWEditArea.value.length
				       && SMWEditArea.value.charAt(end) == ']') {
					++end;
					found = true;
				}
				if (!found) {
					// no brackets found => skip all spaces at the end
					end = SMWEditArea.selectionEnd-1;
					while (end >= 0 && SMWEditArea.value.charAt(end) == ' ') {
						--end;
					}
					++end;
				}
				this.setSelectionRange(start,end);
			}
		}
	},
	
	getSelectedText: function(){
		if ( $(editAreaName) && $(editAreaName).getStyle('display')!='none'){
			SMWEditArea = $(editAreaName);
			if (document.selection  && !is_gecko) {
				// IE - store the current range
				var range = document.selection.createRange();
				var theSelection = range.text;
				if (theSelection != "") {
					this.currentRange = range;
				}
				return theSelection;
			} else if(SMWEditArea.selectionStart || SMWEditArea.selectionStart == '0') {
				// Mozilla
				var startPos = SMWEditArea.selectionStart;
				var endPos = SMWEditArea.selectionEnd;
				if (endPos != startPos) {
					return (SMWEditArea.value).substring(startPos, endPos);
				}
				return "";
			}
		} 
		return "";
	},

	setSelectedText: function(text){
		if ( $(editAreaName) && $(editAreaName).getStyle('display')!='none'){
			SMWEditArea = $(editAreaName);
			if (document.selection  && !is_gecko) {
				// IE
				var theSelection = document.selection.createRange().text;
				if (theSelection == "" && this.currentRange) {
					// currently nothing is selected, but a range has been
					// stored => select the former range
					this.currentRange.select();
				}
				theSelection = document.selection.createRange().text;
				theSelection=text;
				SMWEditArea.focus();
				if (theSelection.charAt(theSelection.length - 1) == " ") { // exclude ending space char, if any
					theSelection = theSelection.substring(0, theSelection.length - 1);
					document.selection.createRange().text = theSelection + " ";
				} else {
					document.selection.createRange().text = theSelection;
				}
			} else if(SMWEditArea.selectionStart || SMWEditArea.selectionStart == '0') {
				// Mozilla
				var replaced = false;
				var startPos = SMWEditArea.selectionStart;
				var endPos = SMWEditArea.selectionEnd;
				if (endPos-startPos) {
					replaced = true;
				}
				var scrollTop = SMWEditArea.scrollTop;
				var theSelection = (SMWEditArea.value).substring(startPos, endPos);
		//		if (!myText) {
				var myText=text;
		//		}
				var subst;
				if (myText.charAt(myText.length - 1) == " ") { // exclude ending space char, if any
					subst = myText.substring(0, (myText.length - 1)) + " ";
				} else {
					subst = myText;
				}
				SMWEditArea.value = SMWEditArea.value.substring(0, startPos) + subst +
					SMWEditArea.value.substring(endPos, SMWEditArea.value.length);
				SMWEditArea.focus();
				//set new selection
				SMWEditArea.selectionStart = startPos;
				SMWEditArea.selectionEnd = startPos + myText.length;
				SMWEditArea.scrollTop = scrollTop;
			// All other browsers get no toolbar.
			// There was previously support for a crippled "help"
			// bar, but that caused more problems than it solved.
			}
			// reposition cursor if possible
			if (SMWEditArea.createTextRange) {
				SMWEditArea.caretPos = document.selection.createRange().duplicate();
			}
		} 
	},

	getValue: function(){
		if ( $(editAreaName) && $(editAreaName).getStyle('display')!='none')
			return $(editAreaName).value;
		return "";
	},

	setValue: function(text){
		if ( $(editAreaName) && $(editAreaName).getStyle('display')!='none')
			$(editAreaName).value = text;
	},

	getTextBeforeCursor: function() {
		if ( $(editAreaName) && $(editAreaName).getStyle('display')!='none'){
	        if (OB_bd.isIE) {
				var selection_range = document.selection.createRange();

				var selection_rangeWhole = document.selection.createRange();
				selection_rangeWhole.moveToElementText(this.siw.inputBox);

				selection_range.setEndPoint("StartToStart", selection_rangeWhole);
				return selection_range.text;
			} else if (OB_bd.isGecko) {
				var start = this.siw.inputBox.selectionStart;
				return this.siw.inputBox.value.substring(0, start);
			}
		} 
                // cannot return anything
                return "";
        },

        // only needed in the FCKeditor, therefore these are empty here
        setOutputBuffer: function() {},
        flushOutputBuffer: function() {}
};