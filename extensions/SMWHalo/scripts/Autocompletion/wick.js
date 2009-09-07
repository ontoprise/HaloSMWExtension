 /*
 WICK: Web Input Completion Kit
 http://wick.sourceforge.net/
 Copyright (c) 2004, Christopher T. Holland
 All rights reserved.
 
 Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 
 Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 Neither the name of the Christopher T. Holland, nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
 THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 
 Modified by Ontoprise GmbH 2007 (KK)
 
 */


 // namespace constants MW / SMW
var SMW_CATEGORY_NS = 14;
var SMW_PROPERTY_NS = 102;
var SMW_INSTANCE_NS = 0;
var SMW_TEMPLATE_NS = 10;
var SMW_TYPE_NS = 104;
var SMW_HELP_NS = 12;
var SMW_IMAGE_NS = 6;
var SMW_USER_NS = 2
var SMW_FORM_NS = 106;

// special 
var SMW_ENUM_POSSIBLE_VALUE_OR_UNIT = 500;

// time intervals for triggering
var SMW_AC_MANUAL_TRIGGERING_TIME = 500;
var SMW_AC_AUTO_TRIGGERING_TIME = 800;

var SMW_AJAX_AC = 1;

function autoCompletionsOptions(request) { 
    autoCompleter.autoTriggering = request.responseText.indexOf('auto') != -1; 
    document.cookie = "AC_mode="+request.responseText+";path="+wgScriptPath+"/;" 
}

/**
 * Namespace registry. Allows other extension to register their own namespaces.
 * 
 */
var ACNamespaceRegistry = Class.create();
ACNamespaceRegistry.prototype = {
	initialize: function() {
		this.imageregistry = new Object();
		
		// register default namespaces of MW and SMW
		this.registerNamespace(SMW_CATEGORY_NS, "/extensions/SMWHalo/skins/concept.gif");
		this.registerNamespace(SMW_PROPERTY_NS, "/extensions/SMWHalo/skins/property.gif");
		this.registerNamespace(SMW_INSTANCE_NS, "/extensions/SMWHalo/skins/instance.gif");
		this.registerNamespace(SMW_TEMPLATE_NS, "/extensions/SMWHalo/skins/template.gif");
		this.registerNamespace(SMW_TYPE_NS, "/extensions/SMWHalo/skins/type.gif");
		this.registerNamespace(SMW_HELP_NS, "/extensions/SMWHalo/skins/help.gif");
		this.registerNamespace(SMW_HELP_NS, "/extensions/SMWHalo/skins/image.gif");
		this.registerNamespace(SMW_USER_NS, "/extensions/SMWHalo/skins/user.gif");
		this.registerNamespace(SMW_ENUM_POSSIBLE_VALUE_OR_UNIT, "/extensions/SMWHalo/skins/enum.gif");
		
		//XXX: this should not be defined here but in the SemanticForms extension
		this.registerNamespace(SMW_FORM_NS, "/extensions/SMWHalo/skins/form.gif");
		                     
	},
	
	/**
	 * Registers a new namespace with its image and namespace prefix.
	 * 
	 * @param nsIndex Namespace index
	 * @param imgPath Image path, relative to MW root
	 * @param namespacePrefix Content language constant referring to the namespace prefix with colon.
	 */
	registerNamespace: function(nsIndex, imgPath) {
		this.imageregistry[nsIndex] = imgPath;
	},
	
	getImgPath: function(nsIndex) {
		return this.imageregistry[nsIndex];
	}
}

acNamespaceRegistry = new ACNamespaceRegistry();

var AutoCompleter = Class.create();
AutoCompleter.prototype = {
    initialize: function() {
        
          // current input box of last AC request
        this.currentInputBox;

              
        // constraints
        this.constraints;

         // current userInput of last AC request
        this.userInputToMatch = null;

         // current user context of last AC request
        this.userContext = null;
                
         // returned matches of last AC request
        this.collection = [];

         //used to ignore pending AJAX calls when a term has been inserted
        this.ignorePending = false;

         // regex which matches the user input which is used to query the database
        this.articleRegEx = /((([\w\d])+\:)?([\w\d][\w\d\.\(\)\-\s]*)|(([\w\d])+\:))$/;

         // timer which triggers ajax call
        this.timer = null;

         // flag for auto/manual mode
        this.autoTriggering = false;

         // all input boxes with class="wickEnabled" (NOT textareas)
        this.allInputs = null;
        this.textAreas = null;

         // global floater object
        this.siw = null;

         // flag if left mouse button is pressed
        this.mousePressed = false;

         // counter for number of registered floaters 
        this.AC_idCounter = 0;

        // Position data of Floater
        this.AC_yDiff = 0;
        this.AC_xDiff = 0;
        
        this.AC_userDefinedY = 0;
        this.AC_userDefinedX = 0;
        
        // indicates if the mouse has been moved since last AC request
        this.notMoved = false;
        
        this.currentIESelection = null;
         // Get preference options
        var AC_mode = GeneralBrowserTools.getCookie("AC_mode");
        if (AC_mode == null) {
            sajax_do_call('smwf_ac_AutoCompletionOptions', [], autoCompletionsOptions);
        } else {
            this.autoTriggering = (AC_mode == 'auto');
        }
    },

     /* Cancels event propagation */
    freezeEvent: function(e) {
        if (e.preventDefault) e.preventDefault();

        e.returnValue = false;
        e.cancelBubble = true;

        if (e.stopPropagation) e.stopPropagation();

        return false;
    },  //this.freezeEvent
    isWithinNode: function(e, i, c, t, obj) {
        var answer = false;
        var te = e;

        while (te && !answer) {
            if ((te.id && (te.id == i)) || (te.className && (te.className == i + "Class"))
                || (!t && c && te.className && (te.className == c))
                || (!t && c && te.className && (te.className.indexOf(c) != -1))
                || (t && te.tagName && (te.tagName.toLowerCase() == t)) || (obj && (te == obj))) {
                answer = te;
            } else {
                te = te.parentNode;
            }
        }

        return te;
    },                      //this.isWithinNode
    isWithinNodeSimple: function(node, idOfNodeToFind) {
    	
    	if (!node || node == null) return false; 
    	while(node != document) {
    		var id = node.getAttribute("id");
    		if (id && id != null && id.indexOf(idOfNodeToFind) >= 0) break;
    		node = node.parentNode;
    	}
    	
    	return node != document;
    },
    getEventElement: function(e) { return (e.srcElement ? e.srcElement : (e.target ? e.target : e.currentTarget));
                         },  //this.getEventElement()
    findElementPosX: function(obj) {
        var curleft = 0;

        if (obj.offsetParent) {
            while (obj.offsetParent) {
                curleft += obj.offsetLeft;
                obj = obj.offsetParent;
            }
        }  //if offsetParent exists
        else if (obj.x) curleft += obj.x

        return curleft;
    },  //this.findElementPosX
    findElementPosY: function(obj) {
        var curtop = 0;

        if (obj.offsetParent) {
            while (obj.offsetParent) {
                curtop += obj.offsetTop;
                obj = obj.offsetParent;
            }
        }  //if offsetParent exists
        else if (obj.y) curtop += obj.y

        return curtop;
    },  //this.findElementPosY
    handleKeyPress: function(event) {
        var e = GeneralTools.getEvent(event);
        var eL = this.getEventElement(e);

        var upEl = eL.className.indexOf("wickEnabled") >= 0 ? eL : undefined;
        
        var kc = e["keyCode"];
        var isFloaterVisible = (this.siw && this.siw.floater.style.visibility == 'visible');
        
        // remember old cursor position (only IE)
        if (OB_bd.isIE) this.currentIESelection = document.selection.createRange();
        if (isFloaterVisible && this.siw && ((kc == 13) || (kc == 9))) {
            this.siw.selectingSomething = true;

            if (OB_bd.isSafari) this.siw.inputBox.blur();  //hack to "wake up" safari

            this.siw.inputBox.focus();
            this.hideSmartInputFloater();
        } else if (upEl && (kc != 38) && (kc != 40) && (kc != 37) && (kc != 39) && (kc != 13) && (kc != 27)) {
            if (!this.siw || (this.siw && !this.siw.selectingSomething)) {
              if ((e["ctrlKey"] && (kc == 32)) || isFloaterVisible) {
                if (OB_bd.isIE && !isFloaterVisible && !e["altKey"]) {
                    // only relevant to IE. removes the whitespace which is pasted when pressing Ctrl+Space
                    var userInput = this.getUserInputToMatch();
                    var selection_range = document.selection.createRange();
                    selection_range.moveStart("character", -userInput.length-1);
                    selection_range.text = userInput.substr(0, userInput.length-1);
                    selection_range.collapse(false);
                }
                if (!this.siw) this.siw = new SmartInputWindow();
                this.siw.inputBox = upEl;
                this.currentInputBox = upEl;
               

                // get constraint 
                this.constraints = this.siw.inputBox.getAttribute("constraints") == null ? "" : this.siw.inputBox.getAttribute("constraints");

                     // Ctrl+Alt+Space was pressed
                     // get user input which is to be matched
                     // MUST be global because of setTimeout function
                    this.userInputToMatch = this.getUserInputToMatch();

                    if (this.userInputToMatch.length >= 0) {
                         // get user context (used for semantic AC)
                         // MUST be global because of setTimeout function
                        this.userContext = this.getUserContext();

                         // Call for autocompletion

                        if (this.timer) {
                            window.clearTimeout(this.timer);
                        }

                         // runs AC after 900ms have elapsed. That means user can enter several chars 
                         // without causing a AJAX call after each, but only after the last.
                        this.timer = window.setTimeout(
                                         "autoCompleter.timedAC(autoCompleter.userInputToMatch, autoCompleter.userContext, autoCompleter.currentInputBox, autoCompleter.constraints)",
                                         SMW_AC_MANUAL_TRIGGERING_TIME);
                    } else {
                         // if userinputToMatch is empty --> hide floater
                        this.hideSmartInputFloater();
                        return;
                    }
                 // uncomment the following else statement to activate auto-triggering
                } else if (this.autoTriggering) {
                    if (kc==17 || kc==18) return; //ignore Ctrt/Alt when pressed without any key
                    if (!this.siw) this.siw = new SmartInputWindow();
                    this.siw.inputBox = upEl;
                    this.currentInputBox = upEl;
                                   
                    // get constraints
                    this.constraints = this.siw.inputBox.getAttribute("constraints") == null ? "" : this.siw.inputBox.getAttribute("constraints");
                    
                    if (GeneralBrowserTools.isTextSelected(this.siw.inputBox)) {
                         // do not trigger auto AC when something is selected.
                        this.hideSmartInputFloater();
                        return;
                    }

                    this.userContext = this.getUserContext();

                     // test if userContext is [[ or {{ and not an attribute value and do a AC request when at least one char is entered
                     // if inputBox is no TEXTAREA, no context must be given
                    if ((this.userContext.match(/^\[\[/) || this.userContext.match(/^\{\{/) || this.constraints != '') /*&& !this.userContext.match(/:=/)*/) {
                        this.userInputToMatch = this.getUserInputToMatch();

                        if (this.userInputToMatch.length >= 1) {
                            if (this.timer) {
                                window.clearTimeout(this.timer);
                            }

                             // runs AC after 900ms have elapsed. That means user can enter several chars 
                             // without causing a AJAX call after each, but only after the last.
                            this.timer = window.setTimeout(
                                             "autoCompleter.timedAC(autoCompleter.userInputToMatch, autoCompleter.userContext, autoCompleter.currentInputBox, autoCompleter.constraints)",
                                             SMW_AC_AUTO_TRIGGERING_TIME);
                        } else {
                             // if userinputToMatch is empty --> hide floater
                            this.hideSmartInputFloater();
                            return;
                        }
                    } else {
                         // if user context is not [[ --> hide floater
                        this.siw.inputBox.focus();
                        this.hideSmartInputFloater();
                        return;
                    }
                }
            }
        } else if (kc == 27) { // escape pressed -> hide floater
             this.hideSmartInputFloater();
             this.freezeEvent(e);
              this.resetCursorinIE();
        } else if (this.siw && this.siw.inputBox) {
             // do not switch focus when user is in searchbox
            if (eL != null && eL.tagName == 'HTML' && isFloaterVisible) {
                this.siw.inputBox.focus();  //kinda part of the hack.
            }
        }
    },  //handleKeyPress()

     // used to run AC after a certain peroid of time has elapsed
    timedAC: function(userInputToMatch, userContext, inputBox, constraints) {
        function userInputToMatchResult(request) {
            this.hidePendingAJAXIndicator();

             // if there are pending calls right after the user inserted a term, ignore them.
            if (this.ignorePending) {
             return;
            }

             // if something went wrong, abort here and hide floater    
            if (request.status != 200) {
                 //alert("Error: " + request.status + " " + request.statusText + ": " + request.responseText);
                this.hideSmartInputFloater();
                return;
            }

             // stop processing and hide floater if no result
            if (request.responseText.indexOf('noResult') != -1) {
                this.hideSmartInputFloater();
                return;
            }

             // getResult string (xml), parse it and transform it into an array of MatchItems
            var result = request.responseText;
            this.collection = this.getMatchItems(request.responseText);

             // add it it cache if it has at least one result
            if (this.collection.length > 0) {
                AC_matchCache.addLookup(userContext + userInputToMatch, this.collection, constraints);
            }
            
             // process match results
            this.processSmartInput(inputBox, userInputToMatch);
        }
        this.notMoved = true;
        this.ignorePending = false;

         // check if AC result for current user input is in cache
        var cacheResult = AC_matchCache.getLookup(userContext + userInputToMatch, constraints);
        if (cacheResult == null) {  // if no request it
            if (userInputToMatch == null) return;

            this.showPendingAJAXIndicator(inputBox);
            this.resetCursorinIE();
    
            sajax_do_call('smwf_ac_AutoCompletionDispatcher', [
                wgTitle,
                userInputToMatch,
                userContext,
                constraints
            ], userInputToMatchResult.bind(this), SMW_AJAX_AC);
        } else {  // if yes, use it from cache.
            this.collection = cacheResult;
            this.processSmartInput(inputBox, userInputToMatch);
        }
    },

     /*
     * Callback function with autocompletion candidates
     */
     //userInputToMatchResult

    handleKeyDown: function(event) {
        var e = GeneralTools.getEvent(event);
        var eL = this.getEventElement(e);
        
        if (this.siw && (kc = e["keyCode"])) {

            if (kc == 40 && this.siw.floater.style.visibility == 'visible') {
                this.siw.selectingSomething = true;
                this.freezeEvent(e);

                //if (OB_bd.isGecko) this.siw.inputBox.blur();  /* Gecko hack */

                this.selectNextSmartInputMatchItem();
            } else if (kc == 38 && this.siw.floater.style.visibility == 'visible') {
                this.siw.selectingSomething = true;
                this.freezeEvent(e);

                //if (OB_bd.isGecko) this.siw.inputBox.blur();

                this.selectPreviousSmartInputMatchItem();
            } else if ((kc == 13) && this.siw.floater.style.visibility == 'visible') { // enter
                this.siw.selectingSomething = true;
                this.activateCurrentSmartInputMatch();
                this.hideSmartInputFloater();
                this.freezeEvent(e);
            } else if (kc == 9) { // tab
                 ajaxRequestManager.stopCalls(SMW_AJAX_AC, this.hidePendingAJAXIndicator);
            	 this.hideSmartInputFloater();
            } else if (kc == 27) {
                ajaxRequestManager.stopCalls(SMW_AJAX_AC, this.hidePendingAJAXIndicator);
                smwhgLogger.log("", "AC", "close_without_selection");
                this.hideSmartInputFloater();
                this.freezeEvent(e);
                this.resetCursorinIE();
            } else {
                this.siw.selectingSomething = false;
            }
        }
    },  //handleKeyDown()
    handleFocus: function(event) {
     // do nothing
    },  //handleFocus()
    handleBlur: function(event) {
        var e = GeneralTools.getEvent(event);
        var eL = this.getEventElement(e);

        if (blurEl = this.isWithinNode(eL, null, "wickEnabled", null, null)) {
            if (this.siw && !this.siw.selectingSomething) this.hideSmartInputFloater();
        }
        if (this.timer) {
            window.clearTimeout(this.timer);
        }
        ajaxRequestManager.stopCalls(SMW_AJAX_AC, this.hidePendingAJAXIndicator);
    },  //handleBlur()
    handleClick: function(event) {
        var e2 = GeneralTools.getEvent(event);
        var eL2 = this.getEventElement(e2);
        this.mousePressed = false;

        if (this.siw && this.siw.selectingSomething) {
            this.resetCursorinIE();
            this.selectFromMouseClick();
            
        }
    },  //handleClick()
    handleMouseOver: function(event) {
        if (this.notMoved) return;
        var e = GeneralTools.getEvent(event);
        var eL = this.getEventElement(e);

        if (this.siw && (mEl = this.isWithinNode(eL, null, "matchedSmartInputItem", null, null))) {
            this.siw.selectingSomething = true;
            this.selectFromMouseOver(mEl);
        } else if (this.isWithinNode(eL, null, "siwCredit", null, null)) {
            this.siw.selectingSomething = true;
        } else if (this.siw) {
            this.siw.selectingSomething = false;
        }
    },  //handleMouseOver
    handleMouseDown: function(event) {
    	
        var e = GeneralTools.getEvent(event);
        var eL = this.getEventElement(e);
         //if (e["ctrlKey"]) {
         //}
        var elementClicked = Event.element(event);

        if (this.siw && elementClicked
            && (Element.hasClassName(elementClicked, "MWFloaterContentHeader")
                   || (Element.hasClassName(elementClicked.parentNode, "MWFloaterContentHeader")))) {
            this.mousePressed = true;
            var x = this.findElementPosX(this.siw.inputBox);
            var y = this.findElementPosY(this.siw.inputBox);
            this.AC_yDiff = (e.pageY - y) - parseInt(this.siw.floater.style.top);
            this.AC_xDiff = (e.pageX - x) - parseInt(this.siw.floater.style.left);
        } else if (!this.isWithinNodeSimple(elementClicked, "smartInputFloaterContent")
        && !this.isWithinNodeSimple(elementClicked, "MWFloater0")){
            this.hideSmartInputFloater();        	
        }
    },
    handleMouseMove: function(event) {
        this.notMoved = false;
        if (OB_bd.isIE) return;
        var e = GeneralTools.getEvent(event);
        var eL = this.getEventElement(e);

        if (this.mousePressed && this.siw) {
            var x = this.findElementPosX(this.siw.inputBox);
            var y = this.findElementPosY(this.siw.inputBox);

            this.siw.floater.style.top = (e.pageY - y - this.AC_yDiff) + "px";
            this.siw.floater.style.left = (e.pageX - x - this.AC_xDiff) + "px";
            this.AC_userDefinedY = (e.pageY - y - this.AC_yDiff);
            this.AC_userDefinedX = (e.pageX - x - this.AC_xDiff);
            document.cookie = "this.AC_userDefinedX=" + this.AC_userDefinedX;
            document.cookie = "this.AC_userDefinedY=" + this.AC_userDefinedY;
        }
    },
    showSmartInputFloater: function() {
        if (!this.siw.floater.style.display || (this.siw.floater.style.display == "none")) {
            if (!this.siw.customFloater) {
                var x = Position.cumulativeOffset(this.siw.inputBox)[0];
                var y = Position.cumulativeOffset(this.siw.inputBox)[1] + this.siw.inputBox.offsetHeight;

                 //hack: browser-specific adjustments.
                if (!OB_bd.isGecko && !OB_bd.isIE) x += 8;

                if (!OB_bd.isGecko && !OB_bd.isIE) y += 10;
                
                // read position flag and set it: fixed and absolute is possible
                var posStyle = this.currentInputBox != null ? this.currentInputBox.getAttribute("position") : null;
                if (posStyle == null || posStyle == 'absolute') {
                    Element.setStyle(this.siw.floater, { position: 'absolute'});
                    x = x - Position.page($("globalWrapper"))[0] - Position.realOffset($("globalWrapper"))[0];
                    y = y;
                } else if (posStyle == 'fixed') {
                    Element.setStyle(this.siw.floater, { position: 'fixed'});
                                        
                }
                
                // read alignment flag and set position accordingly
                var alignment = this.currentInputBox != null ? this.currentInputBox.getAttribute("alignfloater") : null;
                var globalWrapper = $("globalWrapper");
                if (alignment == null || alignment == 'left') {
                    this.siw.floater.style.left = x + "px";
                    this.siw.floater.style.top = y + "px";
                } else {
                    this.siw.floater.style.right = (globalWrapper.offsetWidth - x - this.currentInputBox.offsetWidth) + "px";
                    this.siw.floater.style.top = y + "px";
                }
            } else {
                if (!this.siw.inputBox) return;
                 //you may
                 //do additional things for your custom floater
                 //beyond setting display and visibility
                var advancedEditor = $('edit_area_toggle_checkbox_wpTextbox1') ? $('edit_area_toggle_checkbox_wpTextbox1').checked : false;
                 // Browser dependant! only IE ------------------------
                 
                 // the following does not work with different skins - deactivated
                /*if (OB_bd.isIE && this.siw.inputBox.tagName == 'TEXTAREA') {
                    // put floater at cursor position
                    // method to calculate floater pos is slightly different in advanced editor
                   
                    var textarea = advancedEditor ? $('frame_wpTextbox1') : this.siw.inputBox;
                    var posY = this.findElementPosY(textarea);
                    var posX = Position.page(textarea)[0];//this.findElementPosX(textarea);
                    alert(posY +" : "+ posX);
                    textarea.focus();
                    var textScrollTop = textarea.scrollTop;
                    var documentScrollPos = document.documentElement.scrollTop;
                    // var selection_range = document.selection.createRange().duplicate();
                    var selection_range = this.currentIESelection;
                    selection_range.collapse(true);
                    
                    if (advancedEditor) {
                        var iFrameOfAdvEditor = document.getElementById('frame_wpTextbox1');
                        this.siw.floater.style.left = (parseInt(iFrameOfAdvEditor.style.width) - 360) + "px";
                        this.siw.floater.style.top = (parseInt(iFrameOfAdvEditor.style.height) - 160) + "px";
                    }  else {                 
                        this.siw.floater.style.left = selection_range.boundingLeft - posX;
                        this.siw.floater.style.top = selection_range.boundingTop + documentScrollPos + textScrollTop - 20;
                        this.siw.floater.style.height = 25 * Math.min(this.collection.length, this.siw.MAX_MATCHES) + 20;
                        var left = selection_range.boundingLeft - posX;
                        alert("Left:"+left);
                    }
                 // only IE -------------------------

                }*/

                if ((OB_bd.isGecko || OB_bd.isIE) && this.siw.inputBox.tagName == 'TEXTAREA') {
                     //TODO: remove the absolute values to the width/height specified in css

                    var x = GeneralBrowserTools.getCookie("this.AC_userDefinedX");
                    var y = GeneralBrowserTools.getCookie("this.AC_userDefinedY");

                    
                    if (x != null && y != null) { // If position cookie defined, use it. 
                        this.siw.floater.style.left = x + "px";
                        this.siw.floater.style.top = y + "px";
                    } else { // Otherwise use standard position: Left bottom corner.
                        this.siw.floater.style.left = (this.siw.inputBox.offsetWidth - 360) + "px";
                        this.siw.floater.style.top = (this.siw.inputBox.offsetHeight - 160) + "px";
                    }
                }
            }

            this.siw.floater.style.display = "block";
            this.siw.floater.style.visibility = "visible";
            this.resetCursorinIE();
        }
    },  //this.showSmartInputFloater()
    
    /**
     * Resets cursor and sets scroll pos to cursor pos. (in IE)
     */
    resetCursorinIE: function() {
        if (!OB_bd.isIE) return;
        this.currentIESelection.scrollIntoView(true);
        this.currentIESelection.collapse(false);
        this.currentIESelection.select();
    },
     /**
     * Shows small graphic indicating an AJAX call.
     */
    showPendingAJAXIndicator: function(inputBox) {
        var pending = $("pendingAjaxIndicator");

        if (!this.siw) this.siw = new SmartInputWindow();
        var advancedEditor = $('edit_area_toggle_checkbox_wpTextbox1') ? $('edit_area_toggle_checkbox_wpTextbox1').checked : false;
        var iFrameOfAdvEditor = document.getElementById('frame_wpTextbox1');
        
         // Browser dependant! only IE ------------------------
        if (OB_bd.isIE && inputBox.tagName == 'TEXTAREA') {
             // put floater at cursor position
            var posY = this.findElementPosY(inputBox);
            var posX = this.findElementPosX(inputBox);

            inputBox.focus();
            var textScrollTop = inputBox.scrollTop;
            var documentScrollPos = document.documentElement.scrollTop;
            var selection_range = document.selection.createRange().duplicate();
            selection_range.collapse(true);
            
            if (advancedEditor) {
                pending.style.left = (this.findElementPosX(iFrameOfAdvEditor) + parseInt(iFrameOfAdvEditor.style.width) - 360) + "px";
                pending.style.top = (this.findElementPosY(iFrameOfAdvEditor) + parseInt(iFrameOfAdvEditor.style.height) - 160) + "px";
            } else {
                pending.style.left = selection_range.boundingLeft - posX
                pending.style.top = selection_range.boundingTop + documentScrollPos + textScrollTop - 20;
            }
         // only IE -------------------------

        }

        if (OB_bd.isGecko && inputBox.tagName == 'TEXTAREA') {
             //TODO: remove the absolute values to the width/height specified in css
            var x = GeneralBrowserTools.getCookie("this.AC_userDefinedX");
            var y = GeneralBrowserTools.getCookie("this.AC_userDefinedY");

            if (x != null && y != null) {
                
                var posY = this.findElementPosY(advancedEditor ? iFrameOfAdvEditor : inputBox);
                var posX = this.findElementPosX(advancedEditor ? iFrameOfAdvEditor : inputBox);

                pending.style.left = (parseInt(x) + posX) + "px";
                pending.style.top = (parseInt(y) + posY) + "px";
            } else {
                if (advancedEditor) {
                    pending.style.left = (this.findElementPosX(iFrameOfAdvEditor) + parseInt(iFrameOfAdvEditor.style.width) - 360) + "px";
                    pending.style.top = (this.findElementPosY(iFrameOfAdvEditor) + parseInt(iFrameOfAdvEditor.style.height) - 160) + "px";
                } else {
                    pending.style.left = (this.findElementPosX(inputBox) + inputBox.offsetWidth - 360) + "px";
                    pending.style.top = (this.findElementPosY(inputBox) + inputBox.offsetHeight - 160) + "px";
                }
            }
        }
        
        // set pending indicator for input field
        if (inputBox.tagName != 'TEXTAREA') {
            pending.style.left = (Position.cumulativeOffset(inputBox)[0]) + "px";
            pending.style.top = (Position.cumulativeOffset(inputBox)[1]) + "px";
        }

        pending.style.display = "block";
        pending.style.visibility = "visible";
    },  //showPendingElement()

     /**
     * Hides graphic indicating an AJAX call.
     */
    hidePendingAJAXIndicator: function() {
        var pending = $("pendingAjaxIndicator");
        pending.style.display = "none";
        pending.style.visibility = "hidden";
    },
    hideSmartInputFloater: function() {
        if (this.siw) {
            this.siw.floater.style.display = "none";
            this.siw.floater.style.visibility = "hidden";
            this.siw = null;
        }  //this.siw exists
    },    //this.hideSmartInputFloater
    processSmartInput: function(inputBox, userInput) {
         // stop if floater is not set
        if (!this.siw) return;

        var classData = inputBox.className.split(" ");
        var siwDirectives = null;

        for (i = 0; (!siwDirectives && classData[i]); i++) {
            if (classData[i].indexOf("wickEnabled") != -1) siwDirectives = classData[i];
        }

        if (siwDirectives && (siwDirectives.indexOf(":") != -1)) {
            this.siw.customFloater = true;
            var newFloaterId = siwDirectives.split(":")[1];
            this.siw.floater = document.getElementById(newFloaterId);
            this.siw.floaterContent = this.siw.floater.getElementsByTagName("div")[OB_bd.isGecko ? 1 : 0];
        }

        this.setSmartInputData(userInput);

        //if (this.siw.matchCollection && (this.siw.matchCollection.length > 0)) this.selectSmartInputMatchItem(0);

        var content1 = this.getSmartInputBoxContent();

        if (content1) {
            this.modifySmartInputBoxContent(content1);
            this.showSmartInputFloater();

            if (OB_bd.isIE) {
                 //adjust size according to numbe of results in IE
                this.siw.floater.style.height = 25 * Math.min(this.collection.length, this.siw.MAX_MATCHES) + 20;
                this.siw.floater.firstChild.style.height
                    = 25 * Math.min(this.collection.length, this.siw.MAX_MATCHES) + 20;
            }
        } else this.hideSmartInputFloater();
    },                                                                                                 //this.processSmartInput()
    simplify: function(s) { 
        var nopipe = s.indexOf("|") != -1 ? s.substring(0, s.indexOf("|")).strip() : s; // strip everthing after a pipe
        return nopipe.replace(/^[ \s\f\t\n\r]+/, '').replace(/[ \s\f\t\n\r]+$/, ''); 
    },  //this.simplify

     /*
     * Returns user input, i.e. all text left from the cursor which may belong
     * to an article title.
     */
    getUserInputToMatch: function() {
        if (!this.siw) return "";

         // be sure that this.siw is set
        if (this.siw.inputBox.tagName == 'TEXTAREA') {
            var textBeforeCursor = this.getTextBeforeCursor();

            var userInputToMatch = textBeforeCursor.match(this.articleRegEx);
             // hack: category: is replaced because in this case category is not a namespace
            return userInputToMatch ? userInputToMatch[0].replace(/\s/, "_").replace(/category\:/i, "") : "";
        } else {
             // do default

            a = this.siw.inputBox.value;
            fields = this.siw.inputBox.value.split(",");

            if (fields.length > 0) a = fields[fields.length - 1];

            return a.strip();
        }
    },  //this.getUserInputToMatch

     /*
     * Returns user context, i.e. all text left from user input to match until
     * 2 brackets are reached.  ([[)
     */
    getUserContext: function() {
        if (this.siw != null && this.siw.inputBox != null && this.siw.inputBox.tagName == 'TEXTAREA') {
            var textBeforeCursor = this.getTextBeforeCursor();

            var userContextStart = Math.max(textBeforeCursor.lastIndexOf("[["), textBeforeCursor.lastIndexOf("{{"));
            var closingSemTag = Math.max(textBeforeCursor.lastIndexOf("]]"), textBeforeCursor.lastIndexOf("}}"));

            if (userContextStart != -1 && userContextStart > closingSemTag) {
                var userInputToMatch = this.getUserInputToMatch();

                if (userInputToMatch != null) {
                    var lengthOfContext = textBeforeCursor.length - userInputToMatch.length;
                    return textBeforeCursor.substring(userContextStart, lengthOfContext);
                }
            }

            return "";
        } else {
            return "";
        }
    },


     /*
    * Returns all text left from cursor.
    */
    getTextBeforeCursor: function() {
        if (OB_bd.isIE) {
        //  debugger;
        /*  var advancedEditor = $('edit_area_toggle_checkbox_wpTextbox1') ? $('edit_area_toggle_checkbox_wpTextbox1').checked : false;
            if (advancedEditor) {
                var textbeforeCursor = editAreaLoader.getValue("wpTextbox1").substring(0, editAreaLoader.getSelectionRange("wpTextbox1")["start"]);
                return textbeforeCursor;
            } else {*/

            this.siw.inputBox.focus();
            var selection_range = document.selection.createRange();
            var selection_rangeWhole = document.selection.createRange();
            selection_rangeWhole.moveToElementText(this.siw.inputBox);

            selection_range.setEndPoint("StartToStart", selection_rangeWhole);
            
            return selection_range.text;
        //  }
        } else if (OB_bd.isGecko) {
            var start = this.siw.inputBox.selectionStart;
            return this.siw.inputBox.value.substring(0, start);
        }

         // cannot return anything 
        return "";
    },
    
    /*
    * Returns all text right from cursor.
    */
    getTextAfterCursor: function() {
        if (OB_bd.isIE) {
            var selection_range = document.selection.createRange();

            var selection_rangeWhole = document.selection.createRange();
            selection_rangeWhole.moveToElementText(this.siw.inputBox);

            selection_range.setEndPoint("EndToEnd", selection_rangeWhole);
            return selection_range.text;
        } else if (OB_bd.isGecko) {
            var start = this.siw.inputBox.selectionStart;
            return this.siw.inputBox.value.substring(start);
        }

         // cannot return anything 
        return "";
    },
    
    getUserInputBase: function() {
        var s = this.siw.inputBox.value;
        var lastComma = s.lastIndexOf(",");
        return s.substr(0, lastComma+1);
    },  //this.getUserInputBase()
    highlightMatches: function(userInput) {
        var userInput = this.simplify(userInput);
        userInput = userInput.replace(/\s/, "_");

        if (this.siw) this.siw.matchCollection = new Array();

        var pointerToCollectionToUse = this.collection;

        var re1m = new RegExp("([ \"\>\<\-]*)(" + userInput + ")", "i");
        var re2m = new RegExp("([ \"\>\<\-]+)(" + userInput + ")", "i");
        var re1 = new RegExp("([ \"\}\{\-]*)(" + userInput + ")", "gi");
        var re2 = new RegExp("([ \"\}\{\-]+)(" + userInput + ")", "gi");
        var reMeasure = new RegExp("(([+-]?\d*(\.\d+([eE][+-]?\d*)?)?)\s+)?(.*)", "gi");
        
        for (i = 0, j = 0; (i < pointerToCollectionToUse.length); i++) {
            var displayMatches = (j < this.siw.MAX_MATCHES);
            var entry = pointerToCollectionToUse[i];
            var mEntry = this.simplify(entry.getText()+entry.getExtraContent());

            if ((mEntry.indexOf(userInput) == 0)) {
                userInput = userInput.replace(/\>/gi, '\\}').replace(/\< ?/gi, '\\{');
                re = new RegExp("(" + userInput + ")", "i");

                if (displayMatches) {
                    this.siw.matchCollection[j]
                        = new SmartInputMatch(entry.getText()+entry.getExtraContent(),
                              mEntry.replace(/\>/gi, '}').replace(/\< ?/gi, '{').replace(re, "<b>$1</b>").replace(/_/g, ' '),
                              entry.getType(), entry.getNsText(), entry.isInferred());
                }

                j++;
            } else if (mEntry.match(re1m) || mEntry.match(re2m)) {
                if (displayMatches) {
                    this.siw.matchCollection[j] = new SmartInputMatch(entry.getText()+entry.getExtraContent(),
                                                      mEntry.replace(/\>/gi, '}').replace(/\</gi, '{').replace(re1,
                                                          "$1<b>$2</b>").replace(re2, "$1<b>$2</b>").replace(/_/g, ' '), entry.getType(), entry.getNsText(), entry.isInferred());
                }

                j++;
            } else if (mEntry.match(reMeasure)) {
                if (displayMatches) {
                    this.siw.matchCollection[j] = new SmartInputMatch(entry.getText()+entry.getExtraContent(),
                                                      mEntry.replace(/\>/gi, '}').replace(/\</gi, '{').replace(re1,
                                                          "$1<b>$2</b>").replace(re2, "$1<b>$2</b>").replace(/_/g, ' '), entry.getType(), entry.getNsText(), entry.isInferred());
                }

                j++;
            }
        }  //loop thru this.collection
    },    //this.highlightMatches
    setSmartInputData: function(orgUserInput) {
        if (this.siw) {
            var userInput = orgUserInput.toLowerCase().replace(/[\r\n\t\f\s]+/gi, ' ').replace(/^ +/gi, '').replace(
                                / +$/gi, '').replace(/ +/gi, ' ').replace(/\\/gi, '').replace(/\[/gi, '').replace(
                                /\(/gi, '\\(').replace(/\./gi, '\.').replace(/\?/gi, '').replace(/\)/gi, '\\)');

            if (userInput != null && (userInput != '"')) {
                this.highlightMatches(userInput);
            }  //if userinput not blank and is meaningful
            else {
                this.siw.matchCollection = null;
            }
        }  //this.siw exists ... uhmkaaayyyyy
    },    //this.setSmartInputData
    getSmartInputBoxContent: function() {
        var a = null;

        if (this.siw && this.siw.matchCollection && (this.siw.matchCollection.length > 0)) {
            a = '';

            for (i = 0; i < this.siw.matchCollection.length; i++) {
                selectedString = this.siw.matchCollection[i].isSelected ? ' selectedSmartInputItem' : '';
                selectedString += this.siw.matchCollection[i].isInferred ? ' inferredSmartInputItem' : '';
               
                var id = ("selected" + i);
                a += '<p id="' + id + '" class="matchedSmartInputItem' + selectedString + '">'
                    + this.siw.matchCollection[i].getImageTag()
                    + "\t" + this.siw.matchCollection[i].value.replace(/\{ */gi, "&lt;").replace(/\} */gi, "&gt;")
                    + '</p>';
            }  //
        }     //this.siw exists

        return a;
    },        //this.getSmartInputBoxContent
    modifySmartInputBoxContent: function(content) {
         //todo: remove credits 'cuz no one gives a shit ;] - done
        this.siw.floaterContent.innerHTML = '<div id="smartInputResults">' + content + (this.siw.showCredit
                                                                                           ? ('<p class="siwCredit">Powered By: <a target="PhrawgBlog" href="http://chrisholland.blogspot.com/?from=smartinput&ref='
                                                                                                 + escape(
                                                                                                       location.href)
                                                                                                 + '">Chris Holland</a></p>')
                                                                                           : '') + '</div>';
        this.siw.matchListDisplay = document.getElementById("smartInputResults");

        if (OB_bd.isGecko) {
            this.scrollToSelectedItem();
        }
    },  //this.modifySmartInputBoxContent()


     /*
     * Scrolls to the selected item in matching box.
     */
    scrollToSelectedItem: function() {
        for (i = 0; i < this.siw.matchCollection.length; i++) {
            if (this.siw.matchCollection[i].isSelected) {
                var selElement = document.getElementById("selected" + i);
                selElement.scrollIntoView(false);
                return;
            }
        }
    },  //this.scrollToSelectedItem
    selectFromMouseOver: function(o) {
        var currentIndex = this.getCurrentlySelectedSmartInputItem();

        if (currentIndex != null) this.deSelectSmartInputMatchItem(currentIndex);

        var newIndex = this.getIndexFromElement(o);
        this.selectSmartInputMatchItem(newIndex);
        this.modifySmartInputBoxContent(this.getSmartInputBoxContent());
    },  //this.selectFromMouseOver
    selectFromMouseClick: function() {
        this.activateCurrentSmartInputMatch();
         //this.siw.inputBox.focus();
        this.siw.inputBox.focus();
        this.siw.inputBox.blur();
        this.hideSmartInputFloater();
    },  //this.selectFromMouseClick
    getIndexFromElement: function(o) {
        var index = 0;

        while (o = o.previousSibling) {
            index++;
        }  //

        return index;
    },    //this.getIndexFromElement
    getCurrentlySelectedSmartInputItem: function() {
        var answer = null;

        if (!this.siw.matchCollection) return;

        for (i = 0; ((i < this.siw.matchCollection.length) && !answer); i++) {
            if (this.siw.matchCollection[i].isSelected) answer = i;
        }  //

        return answer;
    },    //this.getCurrentlySelectedSmartInputItem
    selectSmartInputMatchItem: function(index) {
        if (!this.siw.matchCollection) return;

        this.siw.matchCollection[index].isSelected = true;
    },  //this.selectSmartInputMatchItem()
    deSelectSmartInputMatchItem: function(index) {
        if (!this.siw.matchCollection) return;

        this.siw.matchCollection[index].isSelected = false;
    },  //this.deSelectSmartInputMatchItem()
    selectNextSmartInputMatchItem: function() {
        if (!this.siw.matchCollection) return;

        currentIndex = this.getCurrentlySelectedSmartInputItem();

        if (currentIndex != null) {
            this.deSelectSmartInputMatchItem(currentIndex);

            if ((currentIndex + 1) < this.siw.matchCollection.length) this.selectSmartInputMatchItem(currentIndex + 1);
            else this.selectSmartInputMatchItem(0);
        } else {
            this.selectSmartInputMatchItem(0);
        }

        this.modifySmartInputBoxContent(this.getSmartInputBoxContent());
    },  //this.selectNextSmartInputMatchItem
    selectPreviousSmartInputMatchItem: function() {
        if (!this.siw.matchCollection) return;

        var currentIndex = this.getCurrentlySelectedSmartInputItem();

        if (currentIndex != null) {
            this.deSelectSmartInputMatchItem(currentIndex);

            if ((currentIndex - 1) >= 0) this.selectSmartInputMatchItem(currentIndex - 1);
            else this.selectSmartInputMatchItem(this.siw.matchCollection.length - 1);
        } else {
            this.selectSmartInputMatchItem(this.siw.matchCollection.length - 1);
        }

        this.modifySmartInputBoxContent(this.getSmartInputBoxContent());
    },  //this.selectPreviousSmartInputMatchItem

     /*
     * Pastes the selected item as text in input box.
     */
    activateCurrentSmartInputMatch: function() {
        var baseValue = this.getUserInputBase();

        if ((selIndex = this.getCurrentlySelectedSmartInputItem()) != null) {
            addedValue = this.siw.matchCollection[selIndex].cleanValue;
            this.insertTerm(addedValue, baseValue, this.siw.matchCollection[selIndex]);
            this.ignorePending = true;
        } else {
            smwhgLogger.log("", "AC", "close_without_selection");
        }
    },  //this.activateCurrentSmartInputMatch
    insertTerm: function(addedValue, baseValue, entry) {
    	var type = entry.getType();
    	var nsText = entry.getNsText();
         // replace underscore with blank
        addedValue = addedValue.replace(/_/g, " ");
        
        var userContext = this.getUserContext();

        if (this.siw.customFloater) {
            if ((userContext.match(/:=/) || userContext.match(/::/) || userContext.match(/category:/i)) 
                && !this.getTextAfterCursor().match(/^(\s|\r|\n)*\]\]|^(\s|\r|\n)*\||^(\s|\r|\n)*;/)) {
                addedValue += "]]";
            } else if (type == SMW_PROPERTY_NS) {
                addedValue += "::";
            } else if (type == SMW_INSTANCE_NS) {
                if (!userContext.match(/|(\s|\r|\n)*$/)) { 
                    addedValue += "]]"; // add only if instance is no template parameter
                }
             }else if (addedValue.match(/category/i)) {
                addedValue += ":";
            }
        }
        
        
        if (OB_bd.isIE && this.siw.inputBox.tagName == 'TEXTAREA') {
            this.siw.inputBox.focus();
            
            // set old cursor position
            this.currentIESelection.collapse(false);
            this.currentIESelection.select();
            var userInput = this.getUserInputToMatch();
            
            if (type == SMW_ENUM_POSSIBLE_VALUE_OR_UNIT) {
                userInput = this.removeNumberFromMeasure(userInput);
            }
             // get TextRanges with text before and after user input
             // which is to be matched.
             // e.g. [[category:De]] would return:
             // range1 = [[category:
             // range2 = ]]      
            
            var pasteNS = this.siw.inputBox != null ? this.siw.inputBox.getAttribute("pasteNS") : null;
            var nsPrefix = pasteNS && nsText != null && nsText != '' ? nsText + ":"  : ""; 
            var selection_range = document.selection.createRange();
            selection_range.moveStart("character", -userInput.length);
            selection_range.text = nsPrefix+addedValue;
            selection_range.collapse(false);
            selection_range.select();
                        
            if (typeof(refreshSTB) != "undefined") refreshSTB.changed();
           
            // log
            smwhgLogger.log(userInput+addedValue, "AC", "close_with_selection");
        } else if (OB_bd.isGecko && this.siw.inputBox.tagName == 'TEXTAREA') {
            var userInput = this.getUserInputToMatch();
            
            if (type == SMW_ENUM_POSSIBLE_VALUE_OR_UNIT) {
                userInput = this.removeNumberFromMeasure(userInput);
            }
             // save scroll position
            var scrollTop = this.siw.inputBox.scrollTop;

             // get text before and after user input which is to be matched.
            var start = this.siw.inputBox.selectionStart;
            var pre = this.siw.inputBox.value.substring(0, start - userInput.length);
            var suf = this.siw.inputBox.value.substring(start);

             // insert text
            var pasteNS = this.siw.inputBox != null ? this.siw.inputBox.getAttribute("pasteNS") : null;
            var nsPrefix = pasteNS && nsText != null && nsText != '' ? nsText + ":"  : ""; 
            var theString = pre + nsPrefix + addedValue + suf;
            this.siw.inputBox.value = theString;

             // set the cursor behind the inserted text
            this.siw.inputBox.selectionStart = start + (nsPrefix.length+addedValue.length) - userInput.length;
            this.siw.inputBox.selectionEnd = start + (nsPrefix.length+addedValue.length) - userInput.length;

             // set old scroll position
            this.siw.inputBox.scrollTop = scrollTop;
            
            if (typeof(refreshSTB) != "undefined") refreshSTB.changed();
            // log
            smwhgLogger.log(userInput+addedValue, "AC", "close_with_selection");
        } else {
            var pasteNS = this.currentInputBox != null ? this.currentInputBox.getAttribute("pasteNS") : null;
            var nsPrefix = pasteNS && nsText != null && nsText != '' ? nsText + ":"  : ""; 
            var theString = (baseValue ? baseValue : "") + nsPrefix + addedValue;
         
            this.siw.inputBox.value = theString;
            smwhgLogger.log(theString, "AC", "close_with_selection");
        }
        
    },
    
    /**
     *  Checks if added value has the form of a measure (= number + unit)
     *  If that is the case, remove number from userinput
     */
    removeNumberFromMeasure: function(measure) {
        var result = measure;
        
        var matches = result.match(/[+-]?\d+(\.\d+([eE][+-]?\d*)?)?_+/gi);
        if (matches) {
            result = result.substr(matches[0].length);
        }
        return result;
    },


    /**
     * Initial registration of TEXTAREAs and INPUTs for AC.
     * where className contains 'wickEnabled'
     */
    registerSmartInputListeners: function() {

         // use AC for all inputs.
        var inputs = document.getElementsByTagName("input");

         // use AC only for specified textareas, otherwise uncomment (*) 
        var texts = Array();
         // (*) texts = document.getElementsByTagName("textarea");
        texts[0] = document.getElementById("wpTextbox1");

         // ----------------------------------------------------------

        AC_matchCache = new MatchCache();
        
        // register inputs
        this.registerAllInputs();
        
        // register textareas
        this.textAreas = new Array();
        var y = 0;
         // copy all wickEnabled textareas
        if (texts) {
            while (texts[y]) {
                this.textAreas.push(texts[y]);
                this.createEmbeddingContainer(texts[y]);
                y++;
            }  //
        }

       

         // creates the floater and adds it to content DIV
        var contentElement = document.getElementById("globalWrapper");
        contentElement.appendChild(this.createFloater());
        var pending = this.createPendingAJAXIndicator();
        contentElement.appendChild(pending);

        this.siw = null;

         // register events
        Event.observe(document, "keydown", this.handleKeyDown.bindAsEventListener(this), false);
        Event.observe(document, "keyup", this.handleKeyPress.bindAsEventListener(this), false);
        Event.observe(document, "mouseup", this.handleClick.bindAsEventListener(this), false);
        Event.observe(document, "mousemove", this.handleMouseMove.bindAsEventListener(this), false);

        if (OB_bd.isGecko || OB_bd.isIE) {
             // needed for draggable floater in FF
             // needed for hiding floater when clicking outside
            Event.observe(document, "mousedown", this.handleMouseDown.bindAsEventListener(this), false);
        }

        Event.observe(document, "mouseover", this.handleMouseOver.bindAsEventListener(this), false);
    },  //registerSmartInputListeners

    /**
     * Register all INPUT tags on page.
     */
    registerAllInputs: function() {
        
        var inputs = document.getElementsByTagName("input");
        this.allInputs = new Array();
        var x = 0;
        var z = 0;
        var c = null;
         // copy all wickEnabled inputs
        if (inputs) {
            while (inputs[x]) {
                if ((c = inputs[x].className) && (c.indexOf("wickEnabled") != -1)) {
                    this.allInputs[z] = new Array();
                    this.allInputs[z][0] = inputs[x];
                    z++;
                }

                x++;
            }  //
        }
         for (i = 0; i < this.allInputs.length; i++) {
            if ((c = this.allInputs[i][0].className) && (c.indexOf("wickEnabled") != -1)) {
                this.allInputs[i][0].setAttribute("autocomplete", "OFF");
                this.allInputs[i][1] = this.handleBlur.bindAsEventListener(this);
                Event.observe(this.allInputs[i][0], "blur",  this.allInputs[i][1]);
            }
        }  //loop thru inputs
    },
    
    /**
     * Deregister all INPUT tags on page.
     */
    deregisterAllInputs: function() {
        if (this.allInputs != null) {
             for (i = 0; i < this.allInputs.length; i++) {
                Event.stopObserving(this.allInputs[i][0], "blur",  this.allInputs[i][1]);
             }  //loop thru inputs
        }
    },
    /**
     * Register an additional textarea in another iframe for Auto-Completion
     * 
     * @param textAreaID TextArea which will be registered. 
     * @param iFrame One of window.frames[ID]. 
     */
    registerTextArea: function(textAreaID, iFrame) {
    
        if (iFrame && textAreaID) {
            var textArea = iFrame.document.getElementById(textAreaID);
            if (textArea) {
                if (this.textAreas.indexOf(textArea) != -1) {
                    return; // do not register twice
                }
                this.textAreas.push(textArea);
                
                var iFrameDocument = iFrame.document;
                // register events
                Event.observe(iFrameDocument, "keydown", this.handleKeyDown.bindAsEventListener(this), false);
                Event.observe(iFrameDocument, "keyup", this.handleKeyPress.bindAsEventListener(this), false);
                Event.observe(iFrameDocument, "mouseup", this.handleClick.bindAsEventListener(this), false);

                if (OB_bd.isGecko) {    
                     // needed for draggable floater in FF
                    Event.observe(iFrameDocument, "mousedown", this.handleMouseDown.bindAsEventListener(this), false);
                    Event.observe(iFrameDocument, "mousemove", this.handleMouseMove.bindAsEventListener(this), false);
                }

                Event.observe(iFrameDocument, "mouseover", this.handleMouseOver.bindAsEventListener(this), false);
            }
        }
       
    },
     // ------- Create HTML containers and elements --------------

     /*
     * creates the embedding container for textareas
     */
    createEmbeddingContainer: function(textarea) {
        var container = document.createElement("div");
        container.setAttribute("style", "position:relative;text-align:left");

        var mwFloater = document.createElement("div");
        mwFloater.setAttribute("id", "MWFloater" + this.AC_idCounter);
        Element.addClassName(mwFloater, "MWFloater");
        var mwContent = document.createElement("div");
        Element.addClassName(mwContent, "MWFloaterContent");

        if (OB_bd.isGecko) {
             // show dragging information in Gecko Browsers
            var mwContentHeader = document.createElement("div");
            Element.addClassName(mwContentHeader, "MWFloaterContentHeader");

            var textinHeader = document.createElement("span");
             //textinHeader.setAttribute("src", wgServer + wgScriptPath + "/extensions/SMWHalo/skins/Autocompletion/clicktodrag.gif");
            textinHeader.setAttribute("style", "margin-left:5px;");
            textinHeader.innerHTML = gLanguage.getMessage('AC_CLICK_TO_DRAG');

            var cross = document.createElement("img");
            Element.addClassName(cross, "closeFloater");
            cross.setAttribute("src",
                wgServer + wgScriptPath + "/extensions/SMWHalo/skins/Autocompletion/close.gif");
            cross.setAttribute("onclick", "javascript:autoCompleter.hideSmartInputFloater()");
            cross.setAttribute("style", "margin-left:4px;margin-bottom:3px;");

            mwContentHeader.appendChild(cross);
            mwContentHeader.appendChild(textinHeader);
            mwFloater.appendChild(mwContentHeader);
        }

        container.appendChild(mwFloater);
        mwFloater.appendChild(mwContent);

        var parent = textarea.parentNode;
        var f = parent.replaceChild(container, textarea);

        Element.addClassName(f, "wickEnabled:MWFloater" + this.AC_idCounter);
        container.appendChild(f);
        
        var acMessage = document.createElement("div");
        Element.addClassName(acMessage, "acMessage");
        if (GeneralBrowserTools.getURLParameter("mode") != 'wysiwyg') {
            acMessage.innerHTML = gLanguage.getMessage('AUTOCOMPLETION_HINT');
        } else {
            acMessage.innerHTML = gLanguage.getMessage('WW_AUTOCOMPLETION_HINT');
        }
        container.appendChild(acMessage);
        this.AC_idCounter++;
    },

     /*
     * Creates the floater 
     */
    createFloater: function() {
        var tableElement = document.createElement("table");
        var tbodyElement = document.createElement("tbody");
        tableElement.setAttribute("id", "smartInputFloater");
        Element.addClassName(tableElement, "floater");
        tableElement.setAttribute("cellpadding", "0");
        tableElement.setAttribute("cellspacing", "0");

        var trElement = document.createElement("tr");
        var tdElement = document.createElement("td");
        tdElement.setAttribute("id", "smartInputFloaterContent");
        tdElement.setAttribute("nowrap", "nowrap");

        trElement.appendChild(tdElement);
        tbodyElement.appendChild(trElement);
        tableElement.appendChild(tbodyElement);
        return tableElement;
    },

     /**
     * Creates element indicating pending AJAX calls.
     */
    createPendingAJAXIndicator: function() {
        var pending = document.createElement("img");
        Element.addClassName(pending, "pendingElement");
        pending.setAttribute("src",
            wgServer + wgScriptPath + "/extensions/SMWHalo/skins/Autocompletion/pending.gif");
        pending.setAttribute("id", "pendingAjaxIndicator");
        return pending;
    },

     /**
     * Parse the 
     */
    getMatchItems: function(xml) {
        var list = GeneralXMLTools.createDocumentFromString(xml);
        var children = list.firstChild.childNodes;
        var collection = new Array();

        for (var i = 0, n = children.length; i < n; i++) {
        	var content = children[i].firstChild.nodeValue;
        	var type = parseInt(children[i].getAttribute("type"));
        	var inferred = children[i].getAttribute("inferred") == "true";
        	var nsText = children[i].getAttribute("nsText");
        	var extraContentTextNode = children[i].firstChild.nextSibling.firstChild;
        	var extraContent = extraContentTextNode != null ? extraContentTextNode.nodeValue : "";
            collection[i] = new MatchItem(content, type, nsText, inferred, extraContent);
        }

        return collection;
    }
}


 // ----- Classes -----------

function MatchItem(text, type, nsText, inferred, extraContent) {
    var _text = text;
    var _type = type;
    var _nsText = nsText;
    var _inferred = inferred;
    var _extraContent = extraContent;

    this.getText = function() { return _text; }
    this.getType = function() { return _type; }
    this.getNsText = function() { return _nsText; }
    this.isInferred = function() { return _inferred; }
    this.getExtraContent = function() { return _extraContent; }
}

function SmartInputWindow() {
    this.customFloater = false;
    this.floater = document.getElementById("smartInputFloater");
    this.floaterContent = document.getElementById("smartInputFloaterContent");
    this.selectedSmartInputItem = null;
    this.MAX_MATCHES = 15;
    this.showCredit = false;
}  //SmartInputWindow Object

function SmartInputMatch(cleanValue, value, type, nsText, inferred) {
    this.cleanValue = cleanValue;
    this.value = value;
    this.isSelected = false;
    this.isInferred = inferred;
    var _type = type;
    var _nsText = nsText;
    
    /**
     * Shows namespace icon or namespace as text.
     * In case of primitive values neither of that.
     */
    this.getImageTag = function() {
    	var imgPath = acNamespaceRegistry.getImgPath(_type);
    	var namespaceText = _nsText != null ? _nsText+":" : "";
    	return imgPath ? "<img src=\"" + wgServer + wgScriptPath
                +imgPath+"\">" : namespaceText;
    }

    this.getType = function() { return _type; }
    this.getNsText = function() { return _nsText }
}  //SmartInputMatch

 /**
  * Cache to hold previous AC requests.
  */
function MatchCache() {

     // general cache for edit mode as associative array
    var generalCache = $H({ });
    
   
    var nextToReplace = 0;

     // maximum number of cache entries
    var MAX_CACHE = 10;

     //TODO: would be nice to implement a better cache replace strategy
    this.addLookup = function(matchText, matches, constraints) {
        if (matchText == "" || matchText == null) return;
        
     
            // use general cache
            if (generalCache.keys().length == MAX_CACHE) {
                generalCache.remove(generalCache.keys()[nextToReplace]);
                nextToReplace++;

                if (nextToReplace == MAX_CACHE) {
                  nextToReplace = 0;
                }
            }

            generalCache[matchText+constraints] = matches;
       
      
    }

    this.getLookup = function(matchText, constraints) {
    
            // use general cache
            if (generalCache[matchText+constraints] && typeof(generalCache[matchText+constraints]) == 'object') {
                return generalCache[matchText+constraints];
            }
       

        return null;  // lookup failed
    }
}


 // main program
 // create global AutoCompleter object:
autoCompleter = new AutoCompleter();
 // Initialize after complete document has been loaded
Event.observe(window, 'load', autoCompleter.registerSmartInputListeners.bind(autoCompleter));

