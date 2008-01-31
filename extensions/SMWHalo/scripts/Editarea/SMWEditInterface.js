/*
* This interface provides basic functionality for the wiki edit mode.
* As the syntax highlighted EditArea can be switched off, programs do
* not know if they have to use the access methods of a html textarea
* or of the JS EditArea. This interface provides basic methods for
* textareas / EditArea and decides which ones to use.
* The abstraction works for both Mozilla and IE browsers.
* @author Markus Nitsche, 2007
*/


var editAreaName = "wpTextbox1";

if((wgAction == "edit") && skin == "ontoskin"){
	if(getEditorCookie() == "on")
		editAreaLoader.init({id : "wpTextbox1", syntax: "wiki", start_highlight: true, plugins: "SMW", allow_resize: "no", toolbar: "bold, italic, intlink, extlink, heading, img, media, formula, nowiki, signature, line, |, undo, redo, |, change_smooth_selection, highlight, reset_highlight, |, help", replace_tab_by_spaces: "0", EA_toggle_on_callback: "toggleEAOn", EA_toggle_off_callback: "toggleEAOff"});
	else //display:later
		editAreaLoader.init({id : "wpTextbox1", syntax: "wiki", start_highlight: true, plugins: "SMW", allow_resize: "no", toolbar: "bold, italic, intlink, extlink, heading, img, media, formula, nowiki, signature, line, |, undo, redo, |, change_smooth_selection, highlight, reset_highlight, |, help", replace_tab_by_spaces: "0", EA_toggle_on_callback: "toggleEAOn", EA_toggle_off_callback: "toggleEAOff", display: "later"});
}

function trim(string) {
	return string.replace(/(^\s+|\s+$)/g, "");
}

function changeEdit(){
	$("wpTextbox1").value = editAreaLoader.getValue(editAreaName);
}

function toggleEAOn(id){
	document.getElementById("toolbar").style.display = "none";
	addSpacesForDisplay();
}

function toggleEAOff(id){
	document.getElementById("toolbar").style.display = "";
}

/*
* There is a display error in IE: the last 3-5 characters are not shown.
* Therefore the longest line will be extended by some whitespaces so all
* characters are shown. Ugly but it works.
*/
function addSpacesForDisplay(){
	if (navigator.appName == "Microsoft Internet Explorer" && editAreaLoader.getValue(editAreaName) != ""){
		var lines = editAreaLoader.getValue(editAreaName).split("\n");
		var max = 0;
		var theLine = 0;
		var text = "";
		for(var i=0; i<lines.length; i++){
			if(lines[i].length > max){
				max = lines[i].length;
				theLine = i;
			}
		}
		for(var i=0; i<lines.length; i++){
			if(i == theLine){
				lines[i] = lines[i].substring(0, lines[i].length-2);
				text = text + lines[i] + "         " + "\n";
			}
			else {
				text = text + lines[i];
			}
		}
		editAreaLoader.setValue(editAreaName, text)
	}
}

/*
* Get the cookie that saves the state of the advanced editor, which
* is "on" or "off". If the cookie is not set, "off" is standard.
* The cookie is set in the method userToggle() in edit_area_loader.js
*/
function getEditorCookie() {
	var cookie = document.cookie;
	var length = cookie.length-1;
	if (cookie.charAt(length) != ";")
		cookie += ";";
	var a = cookie.split(";");

	// walk through cookies...
	for (var i=0; i<a.length; i++) {
		var cookiename = trim(a[i].substring(0, a[i].search('=')));
		var cookievalue = a[i].substring(a[i].search('=')+1,a[i].length);
		if (cookiename == "smwUseAdvancedEditor") {
			return cookievalue;
		}
	}
	return "on";
}




//editAreaLoader.execCommand("wpTextbox1", "update_size();");

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
		} else if (OB_bd.isGecko){
			editAreaLoader.execCommand(this.editAreaName, "focus();");
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
		} else {
			editAreaLoader.setSelectionRange(editAreaName, start, end);
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
				setSelectionRange(SMWEditArea,start,end);
			}
		} else {
			editAreaLoader.selectCompleteAnnotation(editAreaName);
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
		} else {
			if(editAreaLoader.getSelectedText(editAreaName) != ""){
				this.currentRange = editAreaLoader.getSelectionRange(editAreaName);
			}
			return editAreaLoader.getSelectedText(editAreaName);
		}
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
		} else {
			if(editAreaLoader.getSelectedText(editAreaName) == "" && this.currentRange){
				editAreaLoader.setSelectionRange(editAreaName, this.currentRange["start"], this.currentRange["end"]);
			}
			editAreaLoader.setSelectedText(editAreaName, text);
		}
	},

	getValue: function(){
		if ( $(editAreaName) && $(editAreaName).getStyle('display')!='none')
			return $(editAreaName).value;
		else
			return editAreaLoader.getValue(editAreaName);
	},

	setValue: function(text){
		if ( $(editAreaName) && $(editAreaName).getStyle('display')!='none')
			$(editAreaName).value = text;
		else
			editAreaLoader.setValue(editAreaName, text);
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
		} else {
			return editAreaLoader.getValue(editAreaName).substring(0, editAreaLoader.getSelectionRange(editAreaName)["start"]);

		}
         // cannot return anything
        return "";
    }
};