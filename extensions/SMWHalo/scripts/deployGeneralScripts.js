
/* The MIT License
*
* Copyright (c) <year> <copyright holders>
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*/


/*  Copyright 2007, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


/* WICK License

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 
Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
Neither the name of the Christopher T. Holland, nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/
 
// slider.js
// under MIT-License
// script.aculo.us slider.js v1.7.0, Fri Jan 19 19:16:36 CET 2007

// Copyright (c) 2005, 2006 Marty Haught, Thomas Fuchs 
//
// script.aculo.us is freely distributable under the terms of an MIT-style license.
// For details, see the script.aculo.us web site: http://script.aculo.us/

if(!Control) var Control = {};
Control.Slider = Class.create();

// options:
//  axis: 'vertical', or 'horizontal' (default)
//
// callbacks:
//  onChange(value)
//  onSlide(value)
Control.Slider.prototype = {
  initialize: function(handle, track, options) {
    var slider = this;
    
    if(handle instanceof Array) {
      this.handles = handle.collect( function(e) { return $(e) });
    } else {
      this.handles = [$(handle)];
    }
    
    this.track   = $(track);
    this.options = options || {};

    this.axis      = this.options.axis || 'horizontal';
    this.increment = this.options.increment || 1;
    this.step      = parseInt(this.options.step || '1');
    this.range     = this.options.range || $R(0,1);
    
    this.value     = 0; // assure backwards compat
    this.values    = this.handles.map( function() { return 0 });
    this.spans     = this.options.spans ? this.options.spans.map(function(s){ return $(s) }) : false;
    this.options.startSpan = $(this.options.startSpan || null);
    this.options.endSpan   = $(this.options.endSpan || null);

    this.restricted = this.options.restricted || false;

    this.maximum   = this.options.maximum || this.range.end;
    this.minimum   = this.options.minimum || this.range.start;

    // Will be used to align the handle onto the track, if necessary
    this.alignX = parseInt(this.options.alignX || '0');
    this.alignY = parseInt(this.options.alignY || '0');
    
    this.trackLength = this.maximumOffset() - this.minimumOffset();

    this.handleLength = this.isVertical() ? 
      (this.handles[0].offsetHeight != 0 ? 
        this.handles[0].offsetHeight : this.handles[0].style.height.replace(/px$/,"")) : 
      (this.handles[0].offsetWidth != 0 ? this.handles[0].offsetWidth : 
        this.handles[0].style.width.replace(/px$/,""));

    this.active   = false;
    this.dragging = false;
    this.disabled = false;

    if(this.options.disabled) this.setDisabled();

    // Allowed values array
    this.allowedValues = this.options.values ? this.options.values.sortBy(Prototype.K) : false;
    if(this.allowedValues) {
      this.minimum = this.allowedValues.min();
      this.maximum = this.allowedValues.max();
    }

    this.eventMouseDown = this.startDrag.bindAsEventListener(this);
    this.eventMouseUp   = this.endDrag.bindAsEventListener(this);
    this.eventMouseMove = this.update.bindAsEventListener(this);

    // Initialize handles in reverse (make sure first handle is active)
    this.handles.each( function(h,i) {
      i = slider.handles.length-1-i;
      slider.setValue(parseFloat(
        (slider.options.sliderValue instanceof Array ? 
          slider.options.sliderValue[i] : slider.options.sliderValue) || 
         slider.range.start), i);
      Element.makePositioned(h); // fix IE
      Event.observe(h, "mousedown", slider.eventMouseDown);
    });
    
    Event.observe(this.track, "mousedown", this.eventMouseDown);
    Event.observe(document, "mouseup", this.eventMouseUp);
    Event.observe(document, "mousemove", this.eventMouseMove);
    
    this.initialized = true;
  },
  dispose: function() {
    var slider = this;    
    Event.stopObserving(this.track, "mousedown", this.eventMouseDown);
    Event.stopObserving(document, "mouseup", this.eventMouseUp);
    Event.stopObserving(document, "mousemove", this.eventMouseMove);
    this.handles.each( function(h) {
      Event.stopObserving(h, "mousedown", slider.eventMouseDown);
    });
  },
  setDisabled: function(){
    this.disabled = true;
  },
  setEnabled: function(){
    this.disabled = false;
  },  
  getNearestValue: function(value){
    if(this.allowedValues){
      if(value >= this.allowedValues.max()) return(this.allowedValues.max());
      if(value <= this.allowedValues.min()) return(this.allowedValues.min());
      
      var offset = Math.abs(this.allowedValues[0] - value);
      var newValue = this.allowedValues[0];
      this.allowedValues.each( function(v) {
        var currentOffset = Math.abs(v - value);
        if(currentOffset <= offset){
          newValue = v;
          offset = currentOffset;
        } 
      });
      return newValue;
    }
    if(value > this.range.end) return this.range.end;
    if(value < this.range.start) return this.range.start;
    return value;
  },
  setValue: function(sliderValue, handleIdx){
    if(!this.active) {
      this.activeHandleIdx = handleIdx || 0;
      this.activeHandle    = this.handles[this.activeHandleIdx];
      this.updateStyles();
    }
    handleIdx = handleIdx || this.activeHandleIdx || 0;
    if(this.initialized && this.restricted) {
      if((handleIdx>0) && (sliderValue<this.values[handleIdx-1]))
        sliderValue = this.values[handleIdx-1];
      if((handleIdx < (this.handles.length-1)) && (sliderValue>this.values[handleIdx+1]))
        sliderValue = this.values[handleIdx+1];
    }
    sliderValue = this.getNearestValue(sliderValue);
    this.values[handleIdx] = sliderValue;
    this.value = this.values[0]; // assure backwards compat
    
    this.handles[handleIdx].style[this.isVertical() ? 'top' : 'left'] = 
      this.translateToPx(sliderValue);
    
    this.drawSpans();
    if(!this.dragging || !this.event) this.updateFinished();
  },
  setValueBy: function(delta, handleIdx) {
    this.setValue(this.values[handleIdx || this.activeHandleIdx || 0] + delta, 
      handleIdx || this.activeHandleIdx || 0);
  },
  translateToPx: function(value) {
    return Math.round(
      ((this.trackLength-this.handleLength)/(this.range.end-this.range.start)) * 
      (value - this.range.start)) + "px";
  },
  translateToValue: function(offset) {
    return ((offset/(this.trackLength-this.handleLength) * 
      (this.range.end-this.range.start)) + this.range.start);
  },
  getRange: function(range) {
    var v = this.values.sortBy(Prototype.K); 
    range = range || 0;
    return $R(v[range],v[range+1]);
  },
  minimumOffset: function(){
    return(this.isVertical() ? this.alignY : this.alignX);
  },
  maximumOffset: function(){
    return(this.isVertical() ? 
      (this.track.offsetHeight != 0 ? this.track.offsetHeight :
        this.track.style.height.replace(/px$/,"")) - this.alignY : 
      (this.track.offsetWidth != 0 ? this.track.offsetWidth : 
        this.track.style.width.replace(/px$/,"")) - this.alignY);
  },  
  isVertical:  function(){
    return (this.axis == 'vertical');
  },
  drawSpans: function() {
    var slider = this;
    if(this.spans)
      $R(0, this.spans.length-1).each(function(r) { slider.setSpan(slider.spans[r], slider.getRange(r)) });
    if(this.options.startSpan)
      this.setSpan(this.options.startSpan,
        $R(0, this.values.length>1 ? this.getRange(0).min() : this.value ));
    if(this.options.endSpan)
      this.setSpan(this.options.endSpan, 
        $R(this.values.length>1 ? this.getRange(this.spans.length-1).max() : this.value, this.maximum));
  },
  setSpan: function(span, range) {
    if(this.isVertical()) {
      span.style.top = this.translateToPx(range.start);
      span.style.height = this.translateToPx(range.end - range.start + this.range.start);
    } else {
      span.style.left = this.translateToPx(range.start);
      span.style.width = this.translateToPx(range.end - range.start + this.range.start);
    }
  },
  updateStyles: function() {
    this.handles.each( function(h){ Element.removeClassName(h, 'selected') });
    Element.addClassName(this.activeHandle, 'selected');
  },
  startDrag: function(event) {
    if(Event.isLeftClick(event)) {
      if(!this.disabled){
        this.active = true;
        
        var handle = Event.element(event);
        var pointer  = [Event.pointerX(event), Event.pointerY(event)];
        var track = handle;
        if(track==this.track) {
          var offsets  = Position.cumulativeOffset(this.track); 
          this.event = event;
          this.setValue(this.translateToValue( 
           (this.isVertical() ? pointer[1]-offsets[1] : pointer[0]-offsets[0])-(this.handleLength/2)
          ));
          var offsets  = Position.cumulativeOffset(this.activeHandle);
          this.offsetX = (pointer[0] - offsets[0]);
          this.offsetY = (pointer[1] - offsets[1]);
        } else {
          // find the handle (prevents issues with Safari)
          while((this.handles.indexOf(handle) == -1) && handle.parentNode) 
            handle = handle.parentNode;
            
          if(this.handles.indexOf(handle)!=-1) {
            this.activeHandle    = handle;
            this.activeHandleIdx = this.handles.indexOf(this.activeHandle);
            this.updateStyles();
            
            var offsets  = Position.cumulativeOffset(this.activeHandle);
            this.offsetX = (pointer[0] - offsets[0]);
            this.offsetY = (pointer[1] - offsets[1]);
          }
        }
      }
      Event.stop(event);
    }
  },
  update: function(event) {
   if(this.active) {
      if(!this.dragging) this.dragging = true;
      this.draw(event);
      // fix AppleWebKit rendering
      if(navigator.appVersion.indexOf('AppleWebKit')>0) window.scrollBy(0,0);
      Event.stop(event);
   }
  },
  draw: function(event) {
    var pointer = [Event.pointerX(event), Event.pointerY(event)];
    var offsets = Position.cumulativeOffset(this.track);
    pointer[0] -= this.offsetX + offsets[0];
    pointer[1] -= this.offsetY + offsets[1];
    this.event = event;
    this.setValue(this.translateToValue( this.isVertical() ? pointer[1] : pointer[0] ));
    if(this.initialized && this.options.onSlide)
      this.options.onSlide(this.values.length>1 ? this.values : this.value, this);
  },
  endDrag: function(event) {
    if(this.active && this.dragging) {
      this.finishDrag(event, true);
      Event.stop(event);
    }
    this.active = false;
    this.dragging = false;
  },  
  finishDrag: function(event, success) {
    this.active = false;
    this.dragging = false;
    this.updateFinished();
  },
  updateFinished: function() {
    if(this.initialized && this.options.onChange) 
      this.options.onChange(this.values.length>1 ? this.values : this.value, this);
    this.event = null;
  }
}

// STB_Framework.js
// under GPL-License
var ToolbarFramework = Class.create();

var HELPCONTAINER = 9; // contains help
var FACTCONTAINER = 0; // contains already annotated facts
var EDITCONTAINER = 1; // contains Linklist
var TYPECONTAINER = 2; // contains datatype selector on attribute pages
var CATEGORYCONTAINER = 3; // contains categories
var ATTRIBUTECONTAINER = 4; // contains attrributes
var RELATIONCONTAINER = 5; // contains relations
var PROPERTIESCONTAINER = 6; // contains the properties of attributes and relations
var CBSRCHCONTAINER = 7; // contains combined search functions
var COMBINEDSEARCHCONTAINER = 8;
var DBGCONTAINER = 10; // contains debug information

ToolbarFramework.prototype = {

	/**
	 * @public
	 *
	 * Constructor.
	 */

	stbconstructor : function() {
		if (this.isToolbarAvailable()) {

			// get existing cookies
			this.getCookieTab();

			// get initial tab from cookie!
			if (this.cookiePrefTab != null) {
				for (var i=0; i<this.cookiePrefTab.length; i++) {
					if (this.cookiePrefTab[i] == 1) {
						this.curtabShown = i;
					}
				}
			} else {
				this.curtabShown = 0;
			}

			this.var_onto.innerHTML += "<div id=\"tabcontainer\"></div>";
			this.var_onto.innerHTML += "<div id=\"activetabcontainer\"></div>";
			this.var_onto.innerHTML += "<div id=\"semtoolbar\"></div>";

			// create empty container (to preserve order of containers)

			this.var_stb = $("semtoolbar");
			if (this.var_stb) {
				for(var i=0;i<=10;i++) {
					this.var_stb.innerHTML += "<div id=\"stb_cont"+i+"-headline\" class=\"generic_headline\"></div>";
					this.var_stb.innerHTML += "<div id=\"stb_cont"+i+"-content\" class=\"generic_content\"></div>";
					$("stb_cont"+i+"-headline").hide();
					$("stb_cont"+i+"-content").hide();
				}
			}
		}
	},

	isToolbarAvailable: function () {
		if ($("ontomenuanchor") != null) {
			this.var_onto = $("ontomenuanchor");
			return true;
		}
		return false;
	},

	initialize: function() {
		this.contarray = new Array();
		// tab array - how many tabs are there and which one is active?
		this.tabarray = new Array();
		this.tabnames = new Array("Tools", "Links to Other Pages", "Facts about this Article");
	},

	// create a new div container
	createDivContainer : function(contnum, tabnr) {
		// check if we need to add a new tab
		if (this.tabarray[tabnr] == null) {
			if (this.curtabShown == tabnr) {
				this.tabarray[tabnr] = 1;
			} else {
				this.tabarray[tabnr] = 0;
			}
			if (this.tabarray.length > 1) {
				this.createTabHeader();
			}
		}
		this.contarray[contnum] = new DivContainer();
		this.contarray[contnum].createContainer(contnum, tabnr);

		if (contnum == HELPCONTAINER) {
			if (this.cookieHelpTab != null) {
				this.contarray[contnum].setVisibility(this.cookieHelpTab);
			} else {
				this.contarray[contnum].setVisibility(0);
			}
		} else {
			this.contarray[contnum].setVisibility(1);
		}

		// return newly created div container
		return this.contarray[contnum];
	},

	showSemanticToolbarContainer : function(container) {
		if (container != null) {
			if (this.contarray[container].getTab() == this.curtabShown) {
				if (this.contarray[container].headline != null) {
					$("stb_cont"+container+"-headline").show();
					document.getElementById("stb_cont" + container + "-link").className='minusplus';
				}
				if (this.contarray[container].isVisible()) {
					$("stb_cont"+container+"-content").show();
				} else {
					$("stb_cont"+container+"-content").hide();
					document.getElementById("stb_cont" + container + "-link").className='plusminus';
				}
			}
		} else {
			for(var i=0;i<this.contarray.length;i++) {
				if (this.contarray[i] && this.contarray[i].getTab() == this.curtabShown) {
					if (this.contarray[i].headline != null) {
						$("stb_cont"+i+"-headline").show();
						document.getElementById("stb_cont" + i + "-link").className='minusplus';
					}
					if (this.contarray[i].isVisible()) {
						$("stb_cont"+i+"-content").show();
					} else {
						$("stb_cont"+i+"-content").hide();
						document.getElementById("stb_cont" + i + "-link").className='plusminus';
					}
				}
			}
		}
	},

	// refresh content of container
	contentChanged : function(contnum) {
		// probably show container
		this.showSemanticToolbarContainer(contnum);
		// probably resize toolbar
		this.resizeToolbar();

	},

	notify : function(container) {
	},

	getDivContainer : function() {
	},

	createTabHeader : function() {
		// is there more than one tab?! -> display inactive containers
		var tabHeader = "";
		if (this.tabarray.length > 1) {
			for (var i = 0; i < (this.tabarray.length); i++)
			{
				if (this.curtabShown != i) {
					tabHeader += "<div id=\"expandable\" style=\"cursor:pointer;cursor:hand;\" onclick=stb_control.switchTab("+i+")><img src=\"" + wgScriptPath + "/skins/ontoskin/plus.gif\" onmouseover=\"(src='" + wgScriptPath + "/skins/ontoskin/plus-act.gif')\" onmouseout=\"(src='" + wgScriptPath + "/skins/ontoskin/plus.gif')\"></div><div id=\"tab_"+i+"\" style=\"cursor:pointer;cursor:hand;\" onclick=stb_control.switchTab("+i+")>"+this.tabnames[i]+"</div>";
				} else {
					$("activetabcontainer").update("<div id=\"expandable\"><img src=\"" + wgScriptPath + "/skins/ontoskin/minus.gif\"></div><div id=\"tab_"+i+"\">"+this.tabnames[i]+"</div>");
				}
			}
		}
		$("tabcontainer").update(tabHeader);
	},

	switchTab: function(tabnr) {
		// hide current containers in current tab
		this.hideSemanticToolbarContainerTab(tabnr);

		// set current tab to clicked one
		this.tabarray[this.curtabShown] = 0;
		this.tabarray[tabnr] = 1;
		this.curtabShown = tabnr;
		// change tab header and show new containers in tab
		this.createTabHeader();
		// display all containers in current tab
		this.showSemanticToolbarContainer();
		this.resizeToolbar();
		this.setCookie(this.tabarray);
	},

	hideSemanticToolbarContainerTab : function(tabnr) {
		if (tabnr != null) {
			for(var i=0;i<this.contarray.length;i++) {
				if (this.contarray[i] && this.contarray[i].getTab() == this.curtabShown) {
					$("stb_cont"+i+"-headline").hide();
					$("stb_cont"+i+"-content").hide();
				}
			}
		}
	},

	resizeToolbar : function() {
		// max. usable height for toolbar
		var maxUsableHeight = this.getWindowHeight() - 150;
		if ($('activetabcontainer')) {
			maxUsableHeight -= ($('tabcontainer').scrollHeight + 10 + $('activetabcontainer').scrollHeight);
		}
		// calculate height of containers:
		this.countNumOfDisplayedContainers();
		var neededHeight = this.calculateNeededHeightOfContainers();
		if (this.contarray[HELPCONTAINER] != null && this.contarray[HELPCONTAINER].isVisible()) {
			maxUsableHeight -= this.contarray[HELPCONTAINER].getNeededHeight();
		}

		if (neededHeight >= maxUsableHeight) {
			var j = this.numOfVisibleContainers;
			maxUsableHeight -= j*22;	// substract headers

			// only one container is there -> set to maxUsableHeight!
			if ((this.numOfContainers-1) == 0) {
				if (neededHeight > maxUsableHeight) {
					for(var i=0;i<this.contarray.length;i++) {
						if (this.contarray[i] && this.contarray[i].getTab() == this.curtabShown && this.contarray[i].getContainerNr() != HELPCONTAINER) {
							this.contarray[i].setContentStyle({maxHeight: maxUsableHeight + 'px'});
						}
					}
				}
			// more containers are there!
			} else {
				for(var i=0;i<this.contarray.length;i++) {
					if (this.contarray[i] && this.contarray[i].getTab() == this.curtabShown && this.contarray[i].getContainerNr() != HELPCONTAINER && this.contarray[i].isVisible()) {
						if (this.contarray[i].getNeededHeight() < maxUsableHeight/this.numOfVisibleContainers) {
							this.contarray[i].setContentStyle({maxHeight: this.contarray[i].getNeededHeight() + 'px'});
							maxUsableHeight -= this.contarray[i].getNeededHeight();
						} else {
							this.contarray[i].setContentStyle({maxHeight: maxUsableHeight/(this.numOfVisibleContainers) + 'px'});
						}
					}
				}
			}
		// stb fits into available free space
		} else {
			for(var i=0;i<this.contarray.length;i++) {
				if (this.contarray[i] && this.contarray[i].getTab() == this.curtabShown && this.contarray[i].getContainerNr() != HELPCONTAINER) {
					this.contarray[i].setContentStyle({maxHeight: ''});
				}
			}
		}
	},

	calculateNeededHeightOfContainers : function() {
		var j = 0;
		for(var i=0;i<this.contarray.length;i++) {
			if (this.contarray[i] && this.contarray[i].getTab() == this.curtabShown && this.contarray[i].isVisible()) {
				j += this.contarray[i].getNeededHeight();
			}
		}
		return j;
	},

	countNumOfDisplayedContainers : function () {
		var j = 0;
		var d = 0;
		if (this.contarray) {
			for(var i=0;i<this.contarray.length;i++) {
				if (this.contarray[i] && this.contarray[i].getTab() == this.curtabShown) {
					j++;
					if (this.contarray[i].isVisible()) {
						d++;
					}
				}
			}
		}
		this.numOfContainers = j;
		this.numOfVisibleContainers = d;
	},

	getWindowHeight : function() {
	    if (window.innerHeight) {
	        return window.innerHeight;
	    } else {
			//Common for IE
	        if (window.document.documentElement && window.document.documentElement.clientHeight) {
	            return window.document.documentElement.clientHeight;
	        } else {
				//Fallback solution for IE, does not always return usable values
				if (document.body && document.body.offsetHeight) {
		       		return win.document.body.offsetHeight;
		        }
			return 0;
			}
	    }
	},

	getCookieTab : function() {
		var cookie = document.cookie;
		var length = cookie.length-1;
		if (cookie.charAt(length) != ";")
			cookie += ";";
		var a = cookie.split(";");

		// walk through cookies...
		for (var i=0; i<a.length; i++) {
			var cookiename = this.trim(a[i].substring(0, a[i].search('=')));
			var cookievalue = a[i].substring(a[i].search('=')+1,a[i].length);
			if (cookiename == "stbpreftab") {
				var cookievalue = cookievalue.split(",");
				var retval = new Array();
				for (var j =0; j<cookievalue.length;j++) {
					retval[j] = parseInt(cookievalue[j]);
				}
				this.cookiePrefTab = retval;
			} else if (cookiename == "stbprefhelp") {
				this.cookieHelpTab = parseInt(cookievalue);
			}
		}
	},

	trim : function(string) {
		return string.replace(/(^\s+|\s+$)/g, "");
	},

	setCookie : function(curtabpos) {

		var a = new Date();
		a = new Date(a.getTime() +1000*60*60*24*365);
		var implode = '';
		var first = true;
		for (var i=0; i<curtabpos.length; i++) {
			if (first == true)
				first = false;
			else
				implode += ",";
			implode += curtabpos[i];
		}

		document.cookie = 'stbpreftab='+implode+'; expires='+a.toGMTString()+';';
	},

	setHelpCookie : function(helpshown) {

		var a = new Date();
		a = new Date(a.getTime() +1000*60*60*24*365);

		document.cookie = 'stbprefhelp='+helpshown+'; expires='+a.toGMTString()+';';
	}
}

var stb_control = new ToolbarFramework();

Event.observe(window, 'load', stb_control.stbconstructor.bindAsEventListener(stb_control));
Event.observe(window, 'resize', stb_control.resizeToolbar.bindAsEventListener(stb_control));

/* Resizing SemToolBar  using scriptacolus slider */
var Slider = Class.create();
Slider.prototype = {

	initialize: function() {
	},
	
	activateResizing: function() {
	//Check if semtoolbar is available
	if(!stb_control.isToolbarAvailable()) return;
	//Load image to the slider div
	$('slider').innerHTML = '<img id="sliderHandle" src="' + 
			wgScriptPath + 
			'/extensions/SMWHalo/skins/slider.gif"/>';
	   //create slider		 	 
	   this.sliderObj = new Control.Slider('sliderHandle','slider',{
	   	  //axis:'vertical',
	      sliderValue:0.7,
	      minimum:0.5,
	      maximum:0.75,
	      //range: $R(0.5,0.75),
	      onSlide: this.slide,
	      onChange: this.slide
	   });
	},
	
	//Checks for min max and sets the content and the semtoolbar to the correct width
	slide: function(v)
	      {
	      	var leftmin = 0.25; // range 0 - 1
	   		var rightmin = 0.20; // range 0 - 1
	   		
	      	 if( v < leftmin){
	      	 	smwhg_slider.sliderObj.setValue(leftmin);
	      	 	return;
	      	 }
	      	 
	      	 if( v > 1- rightmin){
	      	 	smwhg_slider.sliderObj.setValue(1 - rightmin);
	      	 	return;
	      	 }
	      	 
	  
	 		//the 5% missing are for the slider itself
	         var currLeftDiv = 100*v;
	         var currRightDiv = 95 - currLeftDiv;
	         
	         $('innercontent').style.width = currLeftDiv + "%";
	         $('ontomenuanchor').style.width = currRightDiv + "%";
	         
	         if(window.editArea){
	         	editArea.update_size();
	         }
	         
	         
	 }
}
var smwhg_slider = new Slider();
Event.observe(window, 'load', smwhg_slider.activateResizing.bind(smwhg_slider));


// STB_Divcontainer.js
// under GPL-License
var DivContainer = Class.create();

DivContainer.prototype = {


	/**
	 * @public
	 *
	 * Constructor. set container number and tab number.
	 */
	initialize: function() {
		this.visibility = true;
	},

	createContainer: function(contnum, tabnr) {
		this.contnum = contnum;
		this.tabnr = tabnr;
	},

	/**
	 * fire content changed event to notify the framework
	 */
	contentChanged : function() {
		stb_control.contentChanged(this.getContainerNr());
	},

	// tab
	setTab : function(tabnr) {
		this.tabnr = tabnr;
	},

	getTab : function() {
		return this.tabnr;
	},

	setContainerNr : function(contnum) {
		this.contnum = contnum;
	},

	getContainerNr : function() {
		return this.contnum;
	},

	setVisibility : function(visibility) {
		this.visibility = visibility;
	},

	isVisible : function() {
		return this.visibility;
	},

	setHeadline : function(headline) {
		this.headline = headline;
		$("stb_cont"+this.getContainerNr()+"-headline").update("<div style=\"cursor:pointer;cursor:hand;\" onclick=\"stb_control.contarray["+this.getContainerNr()+"].switchVisibility()\"><a id=\"stb_cont" + this.getContainerNr() + "-link\" class=\"minusplus\" href=\"javascript:void(0)\">&nbsp;</a>" + headline);
	},

	setContent : function(content) {
		this.content = content;
		$("stb_cont"+this.getContainerNr()+"-content").update(content);
	},

	setContentStyle : function(style) {
		$("stb_cont"+this.getContainerNr()+"-content").setStyle(style);
	},

	switchVisibility : function(container) {
		if (this.isVisible()) {
			if (this.getContainerNr() == HELPCONTAINER) {
				stb_control.setHelpCookie(0);
			}
			this.setVisibility(0);
		} else {
			if (this.getContainerNr() == HELPCONTAINER) {
				stb_control.setHelpCookie(1);
			}
			this.setVisibility(1);
		}
		// inform framework to hide
		stb_control.contentChanged(this.getContainerNr());
	},

	getVisibleHeight : function() {
		return $('stb_cont'+this.getContainerNr()+"-content").offsetHeight;
	},

	getNeededHeight : function() {
		return $('stb_cont'+this.getContainerNr()+"-content").scrollHeight;
	}
}


// wick.js
// under WICK-License
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


 // namespace constants
var SMW_CATEGORY_NS = 14;
var SMW_PROPERTY_NS = 102;
var SMW_INSTANCE_NS = 0;
var SMW_TEMPLATE_NS = 10;
var SMW_TYPE_NS = 104;

// time intervals for triggering
var SMW_AC_MANUAL_TRIGGERING_TIME = 500;
var SMW_AC_AUTO_TRIGGERING_TIME = 800;

var SMW_AJAX_AC = 1;

function autoCompletionsOptions(request) { 
	autoCompleter.autoTriggering = request.responseText.indexOf('auto') != -1; 
	document.cookie = "AC_mode="+request.responseText+";path="+wgScriptPath+"/;" 
}

var AutoCompleter = Class.create();
AutoCompleter.prototype = {
    initialize: function() {
    	
    	  // current input box of last AC request
        this.currentInputBox;

         // type hint (for INPUTs)
        this.typeHint;

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
			sajax_do_call('smwfAutoCompletionOptions', [], autoCompletionsOptions);
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
                 // get type hint 
                this.typeHint = this.siw.inputBox.getAttribute("typeHint");


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
                                         "autoCompleter.timedAC(autoCompleter.userInputToMatch, autoCompleter.userContext, autoCompleter.currentInputBox, autoCompleter.typeHint)",
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
                	 // get type hint 
                	this.typeHint = this.siw.inputBox.getAttribute("typeHint");
                
                    if (GeneralBrowserTools.isTextSelected(this.siw.inputBox)) {
                         // do not trigger auto AC when something is selected.
                        this.hideSmartInputFloater();
                        return;
                    }

                    this.userContext = this.getUserContext();

                     // test if userContext is [[ or {{ and not an attribute value and do a AC request when at least one char is entered
                     // if inputBox is no TEXTAREA, no context must be given
                    if ((this.userContext.match(/^\[\[/) || this.userContext.match(/^\{\{/) || this.siw.inputBox.tagName != 'TEXTAREA') /*&& !this.userContext.match(/:=/)*/) {
                        this.userInputToMatch = this.getUserInputToMatch();

                        if (this.userInputToMatch.length >= 1) {
                            if (this.timer) {
                                window.clearTimeout(this.timer);
                            }

                             // runs AC after 900ms have elapsed. That means user can enter several chars 
                             // without causing a AJAX call after each, but only after the last.
                            this.timer = window.setTimeout(
                                             "autoCompleter.timedAC(autoCompleter.userInputToMatch, autoCompleter.userContext, autoCompleter.currentInputBox, autoCompleter.typeHint)",
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
        } else if (this.siw && this.siw.inputBox) {
             // do not switch focus when user is in searchbox
            if (eL != null && eL.tagName == 'HTML' && isFloaterVisible) {
                this.siw.inputBox.focus();  //kinda part of the hack.
            }
        }
    },  //handleKeyPress()

     // used to run AC after a certain peroid of time has elapsed
    timedAC: function(userInputToMatch, userContext, inputBox, typeHint) {
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
                AC_matchCache.addLookup(userContext + userInputToMatch, this.collection, typeHint);
            }
			
             // process match results
            this.processSmartInput(inputBox, userInputToMatch);
        }
		this.notMoved = true;
        this.ignorePending = false;

         // check if AC result for current user input is in cache
        var cacheResult = AC_matchCache.getLookup(userContext + userInputToMatch, typeHint);

        if (cacheResult == null) {  // if no request it
            if (userInputToMatch == null) return;

            this.showPendingAJAXIndicator(inputBox);
            sajax_do_call('smwfAutoCompletionDispatcher', [
                wgTitle,
                userInputToMatch,
                userContext,
                typeHint
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
            } else if (((kc == 13) || (kc == 9)) && this.siw.floater.style.visibility == 'visible') {
                this.siw.selectingSomething = true;
                this.activateCurrentSmartInputMatch();
                this.hideSmartInputFloater();
                this.freezeEvent(e);
            } else if (kc == 27) {
            	ajaxRequestManager.stopCalls(SMW_AJAX_AC, this.hidePendingAJAXIndicator);
            	smwhgLogger.log("", "AC", "close_without_selection");
                this.hideSmartInputFloater();
                this.freezeEvent(e);
                
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
                var x = this.findElementPosX(this.siw.inputBox);
                var y = this.findElementPosY(this.siw.inputBox) + this.siw.inputBox.offsetHeight;

                 //hack: browser-specific adjustments.
                if (!OB_bd.isGecko && !OB_bd.isIE) x += 8;

                if (!OB_bd.isGecko && !OB_bd.isIE) y += 10;
				
				// read position flag and set it: fixed and absolute is possible
				var posStyle = this.currentInputBox != null ? this.currentInputBox.getAttribute("position") : null;
				if (posStyle == null || posStyle == 'absolute') {
					Element.setStyle(this.siw.floater, { position: 'absolute'});
					x = x - Position.page($("globalWrapper"))[0] - Position.realOffset($("globalWrapper"))[0];
                	y = y - Position.page($("globalWrapper"))[1] - Position.realOffset($("globalWrapper"))[1];
				} else if (posStyle == 'fixed') {
                	Element.setStyle(this.siw.floater, { position: 'fixed'});
                	                	
				}
				
				// read alignment flag and set position accordingly
				var alignment = this.currentInputBox != null ? this.currentInputBox.getAttribute("alignfloater") : null;
				if (alignment == null || alignment == 'left') {
                	this.siw.floater.style.left = x + "px";
                	this.siw.floater.style.top = y + "px";
				} else {
					var globalWrapperWidth = $("globalWrapper");
					this.siw.floater.style.right = (globalWrapperWidth.offsetWidth - x - this.currentInputBox.offsetWidth) + "px";
                	this.siw.floater.style.top = y + "px";
				}
            } else {
            	if (!this.siw.inputBox) return;
                 //you may
                 //do additional things for your custom floater
                 //beyond setting display and visibility
                var advancedEditor = $('edit_area_toggle_checkbox_wpTextbox1') ? $('edit_area_toggle_checkbox_wpTextbox1').checked : false;
                 // Browser dependant! only IE ------------------------
                if (OB_bd.isIE && this.siw.inputBox.tagName == 'TEXTAREA') {
                    // put floater at cursor position
                    // method to calculate floater pos is slightly different in advanced editor
                   

                    var posY = this.findElementPosY(advancedEditor ? $('frame_wpTextbox1') : this.siw.inputBox);
                    var posX = this.findElementPosX(advancedEditor ? $('frame_wpTextbox1') : this.siw.inputBox);
					
                    this.siw.inputBox.focus();
                    var textScrollTop = this.siw.inputBox.scrollTop;
                    var documentScrollPos = document.documentElement.scrollTop;
                    var selection_range = document.selection.createRange().duplicate();
                    selection_range.collapse(true);
                                        
                    this.siw.floater.style.left = selection_range.boundingLeft + (advancedEditor ? 0 : -posX);
                    this.siw.floater.style.top = selection_range.boundingTop + documentScrollPos + textScrollTop - 20 + (advancedEditor ? posY : 0);
                    this.siw.floater.style.height = 25 * Math.min(this.collection.length, this.siw.MAX_MATCHES) + 20;
                 // only IE -------------------------

                }

                if (OB_bd.isGecko && this.siw.inputBox.tagName == 'TEXTAREA') {
                     //TODO: remove the absolute values to the width/height specified in css

                    var x = GeneralBrowserTools.getCookie("this.AC_userDefinedX");
                    var y = GeneralBrowserTools.getCookie("this.AC_userDefinedY");

					
                    if (x != null && y != null) { // If position cookie defined, use it. 
                        this.siw.floater.style.left = x + "px";
                        this.siw.floater.style.top = y + "px";
                    } else { // Otherwise use standard position: Left bottom corner.
                    	if (advancedEditor) {
                    		var iFrameOfAdvEditor = document.getElementById('frame_wpTextbox1');
                    		this.siw.floater.style.left = (parseInt(iFrameOfAdvEditor.style.width) - 360) + "px";
                       		this.siw.floater.style.top = (parseInt(iFrameOfAdvEditor.style.height) - 160) + "px";
                    	} else {
                    		this.siw.floater.style.left = (this.siw.inputBox.offsetWidth - 360) + "px";
                       		this.siw.floater.style.top = (this.siw.inputBox.offsetHeight - 160) + "px";
                    	}
                    	
                       
                    }
                }
            }

            this.siw.floater.style.display = "block";
            this.siw.floater.style.visibility = "visible";
        }
    },  //this.showSmartInputFloater()


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
            pending.style.left = selection_range.boundingLeft - posX
            pending.style.top = selection_range.boundingTop + documentScrollPos + textScrollTop - 20;

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
        	pending.style.left = (this.findElementPosX(inputBox)) + "px";
            pending.style.top = (this.findElementPosY(inputBox)) + "px";
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
            fields = this.siw.inputBox.value.split(";");

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
        //	debugger;
        /*	var advancedEditor = $('edit_area_toggle_checkbox_wpTextbox1') ? $('edit_area_toggle_checkbox_wpTextbox1').checked : false;
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
        //	}
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
       	var lastComma = s.lastIndexOf(";");
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

        for (i = 0, j = 0; (i < pointerToCollectionToUse.length); i++) {
            var displayMatches = (j < this.siw.MAX_MATCHES);
            var entry = pointerToCollectionToUse[i];
            var mEntry = this.simplify(entry.getText());

            if ((mEntry.indexOf(userInput) == 0)) {
                userInput = userInput.replace(/\>/gi, '\\}').replace(/\< ?/gi, '\\{');
                re = new RegExp("(" + userInput + ")", "i");

                if (displayMatches) {
                    this.siw.matchCollection[j]
                        = new SmartInputMatch(entry.getText(),
                              mEntry.replace(/\>/gi, '}').replace(/\< ?/gi, '{').replace(re, "<b>$1</b>"),
                              entry.getType());
                }

                j++;
            } else if (mEntry.match(re1m) || mEntry.match(re2m)) {
                if (displayMatches) {
                    this.siw.matchCollection[j] = new SmartInputMatch(entry.getText(),
                                                      mEntry.replace(/\>/gi, '}').replace(/\</gi, '{').replace(re1,
                                                          "$1<b>$2</b>").replace(re2, "$1<b>$2</b>").replace(/_/g, ' '), entry.getType());
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
            this.insertTerm(addedValue, baseValue, this.siw.matchCollection[selIndex].getType());
            this.ignorePending = true;
        } else {
        	smwhgLogger.log("", "AC", "close_without_selection");
        }
    },  //this.activateCurrentSmartInputMatch
    insertTerm: function(addedValue, baseValue, type) {
         // replace underscore with blank
        addedValue = addedValue.replace(/_/g, " ");
        var userContext = this.getUserContext();

        if (this.siw.customFloater) {
            if ((userContext.match(/:=/) || userContext.match(/::/) || userContext.match(/category:/i)) && !this.getTextAfterCursor().match(/^\s*\]\]|^\s*\||^\s*;/)) {
                addedValue += "]]";
            } else if (type == SMW_PROPERTY_NS) {
                addedValue += ":=";
            } else if (type == SMW_INSTANCE_NS) {
            	addedValue += "]]";
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

             // get TextRanges with text before and after user input
             // which is to be matched.
             // e.g. [[category:De]] would return:
             // range1 = [[category:
             // range2 = ]]      
			
            var selection_range = document.selection.createRange();
            selection_range.moveStart("character", -userInput.length);
            selection_range.text = addedValue;
            selection_range.collapse(false);
            
            // log
            smwhgLogger.log(userInput+addedValue, "AC", "close_with_selection");
        } else if (OB_bd.isGecko && this.siw.inputBox.tagName == 'TEXTAREA') {
            var userInput = this.getUserInputToMatch();

             // save scroll position
            var scrollTop = this.siw.inputBox.scrollTop;

             // get text before and after user input which is to be matched.
            var start = this.siw.inputBox.selectionStart;
            var pre = this.siw.inputBox.value.substring(0, start - userInput.length);
            var suf = this.siw.inputBox.value.substring(start);

             // insert text
            var theString = pre + addedValue + suf;
            this.siw.inputBox.value = theString;

             // set the cursor behind the inserted text
            this.siw.inputBox.selectionStart = start + addedValue.length - userInput.length;
            this.siw.inputBox.selectionEnd = start + addedValue.length - userInput.length;

             // set old scroll position
            this.siw.inputBox.scrollTop = scrollTop;
            
            // log
            smwhgLogger.log(userInput+addedValue, "AC", "close_with_selection");
        } else {
        	var pasteNS = this.currentInputBox != null ? this.currentInputBox.getAttribute("pasteNS") : null;
            var theString = (baseValue ? baseValue : "") + addedValue;
        	if (pasteNS != null) {
        		switch(type) {
        			
        			case SMW_PROPERTY_NS: theString = gLanguage.getMessage('PROPERTY')+theString; break;
        			case SMW_CATEGORY_NS: theString = gLanguage.getMessage('CATEGORY')+theString; break;
        			case SMW_TEMPLATE_NS: theString = gLanguage.getMessage('TEMPLATE')+theString; break;
        			case SMW_TYPE_NS: theString = gLanguage.getMessage('TYPE')+theString; break;
        		}
        	}
            this.siw.inputBox.value = theString;
            smwhgLogger.log(theString, "AC", "close_with_selection");
        }
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

        if (OB_bd.isGecko) {
             // needed for draggable floater in FF
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
        var acMessage = document.createElement("span");
        Element.addClassName(acMessage, "acMessage");
        acMessage.innerHTML = gLanguage.getMessage('AUTOCOMPLETION_HINT');
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
            collection[i] = new MatchItem(children[i].firstChild.nodeValue, parseInt(children[i].getAttribute("type")));
        }

        return collection;
    }
}


 // ----- Classes -----------

function MatchItem(text, type) {
    var _text = text;
    var _type = type;

    this.getText = function() { return _text; }
    this.getType = function() { return _type; }
}

function SmartInputWindow() {
    this.customFloater = false;
    this.floater = document.getElementById("smartInputFloater");
    this.floaterContent = document.getElementById("smartInputFloaterContent");
    this.selectedSmartInputItem = null;
    this.MAX_MATCHES = 15;
    this.showCredit = false;
}  //SmartInputWindow Object

function SmartInputMatch(cleanValue, value, type) {
    this.cleanValue = cleanValue;
    this.value = value;
    this.isSelected = false;

    var _type = type;
    this.getImageTag = function() {
        if (_type == SMW_INSTANCE_NS) {
            return "<img src=\"" + wgServer + wgScriptPath
                + "/extensions/SMWHalo/skins/instance.gif\">";
        } else if (_type == SMW_CATEGORY_NS) {
            return "<img src=\"" + wgServer + wgScriptPath
                + "/extensions/SMWHalo/skins/concept.gif\">";
        } else if (_type == SMW_PROPERTY_NS) {
            return "<img src=\"" + wgServer + wgScriptPath
                + "/extensions/SMWHalo/skins/property.gif\">";
        } else if (_type == SMW_TEMPLATE_NS) {
            return "<img src=\"" + wgServer + wgScriptPath
                + "/extensions/SMWHalo/skins/template.gif\">";
        } else if (_type == SMW_TYPE_NS) {
            return "<img src=\"" + wgServer + wgScriptPath
                + "/extensions/SMWHalo/skins/template.gif\">"; // FIXME: separate icon for TYPE namespace
        }

        return "";  // do not return a tag, if type is unknown.
    }

    this.getType = function() { return _type; }
}  //SmartInputMatch

 /**
  * Cache to hold previous AC requests.
  */
function MatchCache() {

     // general cache for edit mode as associative array
    var generalCache = $H({ });
    
    // special caches for type filtered auto-completion (typeHint)
    // MUST use numbers instead of constants here
    var typeFilteredCache = $H({ 14:$H({ }), 102:$H({ }), 100:$H({ }), 0:$H({ }), 10:$H({ }) });
    var nextToReplace = 0;

     // maximum number of cache entries
    var MAX_CACHE = 10;

     //TODO: would be nice to implement a better cache replace strategy
    this.addLookup = function(matchText, matches, typeHint) {
        if (matchText == "" || matchText == null) return;
		
		if (typeHint == null) {
			// use general cache
			if (generalCache.keys().length == MAX_CACHE) {
            	generalCache.remove(generalCache.keys()[nextToReplace]);
            	nextToReplace++;

            	if (nextToReplace == MAX_CACHE) {
              	  nextToReplace = 0;
            	}
       	 	}

        	generalCache[matchText] = matches;
		} else {
			// use typeFiltered cache
			var cache = typeFilteredCache[parseInt(typeHint)];
			if (!cache) return;
			if (cache.keys().length == MAX_CACHE) {
            	cache.remove(cache.keys()[nextToReplace]);
            	nextToReplace++;

            	if (nextToReplace == MAX_CACHE) {
              	  nextToReplace = 0;
            	}
       	 	}

        	cache[matchText] = matches;
		}
      
    }

    this.getLookup = function(matchText, typeHint) {
    	if (typeHint == null) {
    		// use general cache
        	if (generalCache[matchText]) {
           	 	return generalCache[matchText];
        	}
    	} else {
    		// use typeFiltered cache
    		var cache = typeFilteredCache[parseInt(typeHint)];
			if (!cache) return null;
			return cache[matchText];
    	}

        return null;  // lookup failed
    }
}


 // main program
 // create global AutoCompleter object:
autoCompleter = new AutoCompleter();
 // Initialize after complete document has been loaded
Event.observe(window, 'load', autoCompleter.registerSmartInputListeners.bind(autoCompleter));



// SMW_Help.js
// under GPL-License
Event.observe(window, 'load', smw_help_callme);

var initHelp = function(){
	var ns = wgNamespaceNumber==0?"Main":wgCanonicalNamespace ;
	if (wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "Search"){
		ns = "Search";
	}
	sajax_do_call('smwfGetHelp', [ns , wgAction], displayHelp);
}

function smw_help_callme(){
	if((wgAction == "edit"
	    || wgCanonicalSpecialPageName == "Search")
	   && stb_control.isToolbarAvailable()){
		helpcontainer = stb_control.createDivContainer(HELPCONTAINER, 0);
		helpcontainer.setHeadline('<img src="'+wgScriptPath+'/extensions/SMWHalo/skins/help.gif"/> Help');
		initHelp();
	}
}

function displayHelp(request){
	if (request.responseText!=''){
		helpcontainer.setContent(request.responseText);
	}
	else {
		helpcontainer.setHeadline = ' ';
	}
	helpcontainer.contentChanged();
}

function askQuestion(){
	$('questionLoaderIcon').show();
	var ns = wgNamespaceNumber==0?"Main":wgCanonicalNamespace ;
	if (wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "Search"){
		ns = "Search";
	}
	sajax_do_call('smwfAskQuestion', [ns , wgAction, $('question').value], hideQuestionForm);
}

function hideQuestionForm(request){
	$('questionLoaderIcon').hide();
	$('askHelp').hide();
	alert(request.responseText);
}

function submitenter(myfield,e) {
	var keycode;
	if (window.event){
		keycode = window.event.keyCode;
	}
	else if (e) {
		keycode = e.which;
	}
	else {
		return true;
	}

	if (keycode == 13){
		askQuestion();
		return false;
	}
	else {
	   return true;
	}
}

function helplog(question, action){
	/*STARTLOG*/
	if(window.smwhgLogger){
		var logmsg = "Opened Help Page " + question + " with action " + action;
	    smwhgLogger.log(logmsg,"info","help_clickedtopic");
	}
	/*ENDLOG*/
	return true;
}

// SMW_Links.js
// under GPL-License
Event.observe(window, 'load', smw_links_callme);

var createLinkList = function() {
	sajax_do_call('getLinks', [wgArticleId], addLinks);
}

function smw_links_callme(){
	if(wgAction == "edit"
	   && stb_control.isToolbarAvailable()){
		editcontainer = stb_control.createDivContainer(EDITCONTAINER, 1);
		createLinkList();
	}
}

function addLinks(request){
	if (request.responseText!=''){
		editcontainer.setContent(request.responseText);
		editcontainer.contentChanged();
	}
}

function filter (term, _id, cellNr){
	var suche = term.value.toLowerCase();
	var table = document.getElementById(_id);
	var ele;
	for (var r = 0; r < table.rows.length; r++){
		ele = table.rows[r].cells[cellNr].innerHTML.replace(/<[^>]+>/g,"");
		if (ele.toLowerCase().indexOf(suche)>=0 )
			table.rows[r].style.display = '';
		else table.rows[r].style.display = 'none';
	}
}

function update(){
	$("linkfilter").value = "";
	filter($("linkfilter"), "linktable", 0);
}

function linklog(link, action){
	/*STARTLOG*/
	if(window.smwhgLogger){
		var logmsg = "Opened Page " + link + " with action " + action;
	    smwhgLogger.log(logmsg,"info","link_opened");
	}
	/*ENDLOG*/
	return true;
}

// Annotation.js
// under GPL-License
/**
 * Annotations.js 
 * 
 * Classes for the representation of annotations.
 * 
 * @author Thomas Schweitzer
 */
 
/**
 * Base class for annotations. It stores
 * - the text of the annotation
 * - start and end position of the string in the wiki text
 * - a reference to the wiki text parser.
 */
var WtpAnnotation = Class.create();


WtpAnnotation.prototype = {
	/**
	 * @public
	 * @see constructor of WtpAnnotation
	 */
	initialize: function(annotation, start, end, wtp, prefix) {
		this.WtpAnnotation(annotation, start, end, wtp);
	},
	
	/**
	 * @private - called by <initialize>
	 * 
	 * Constructor.
	 * 
	 * @param string annotation The complete annotation e.g. [[attr:=3.141|about three]]
	 * @param int start Start position of the annotation in the wiki text.
	 * @param int end End position of the annotation in the wiki text.
	 * @param int wtp Reference to the wiki text parser.
	 * @param string prefix An optional prefix (e.g. a colon) before the actual
	 *                      annotation.
	 * 
	 */
	WtpAnnotation : function(annotation, start, end, wtp, prefix) {
		this.annotation = annotation;
		this.start = start;
		this.end = end;
		this.wikiTextParser = wtp;
		this.prefix = prefix ? prefix : "";
		this.name = null;
		this.representation = null;
	},
	
	/** @return The complete text of this annotation */
	getAnnotation : function() {
		return this.annotation;
	},
	

	/** @return The name of this annotation */
	getName : function() {
		return this.name;
	},
	
	/** @return The name of this annotation */
	getRepresentation : function() {
		//Fix for IE which interprets null as "null"
		if( this.representation == null){
			return "";	
		} else {
			return this.representation;
		}
	},
	
	/** @return Start position of the annotation in the wiki text. */
	getStart : function() {
		return this.start;
	},
	
	/** @return End position of the annotation in the wiki text. */
	getEnd : function() {
		return this.end;
	},

	/** @return The prefix of this annotation. This can be a colon like in 
	 *          [[:Category:foo]]
	 */
	getPrefix : function() {
		return this.prefix;
	},
	
	/**
	 * Selects this annotation in the wiki text.
	 */
	select: function() {
		this.wikiTextParser.setSelection(this.start, this.end);
	},
	
	/**
	 * @private
	 * 
	 * Replaces an annotation in the wiki text.
	 * 
	 * @param newAnnotation Text of the new annotation
	 */
	replaceAnnotation : function(newAnnotation) {
		this.wikiTextParser.replaceAnnotation(this, newAnnotation);
		var oldLen = this.annotation.length;
		var newLen = newAnnotation.length;
		this.end += newLen - oldLen;
		this.annotation = newAnnotation;
	},
		
	
	/**
	 * @private
	 * 
	 * Each annotation stores its position in the wiki text. If the wiki text
	 * is changed before the annotation, the position has to be updated.
	 * 
	 * This function does not change the wiki text in any way.
	 * 
	 * @param int offset This offset is added to the start and end position of 
	 *                   this annotation.
	 *
	 * @param int start The annotation if moved, if it starts AFTER (not at) this
	 *                  position. 
	 * 
	 */	
	move : function(offset, start) {
		if (this.start > start) {
			this.start += offset;
			this.end += offset;
		}
	},
	
	/**
	 * @public
	 * Removes this annotation from the wiki text. After this operation,
	 * this instance of WtpAnnotation is no longer valid.
	 * 
	 * @param string replacementText Text that replaces the annotation. Can
	 *               be <null> or empty.
	 */
	remove : function(replacementText) {
		this.replaceAnnotation(replacementText);
		this.wikiTextParser.removeAnnotation(this);
//		delete this;  -- does not work in IE
	}
};

/**
 * Class for relations - derived from WtpAnnotation
 * 
 * Stores
 * - the name of the relation
 * - the relation's value and
 * - the user representation.
 * 
 */
var WtpRelation = Class.create();
WtpRelation.prototype = Object.extend(new WtpAnnotation(), {
	
	/**
	 * @public
	 * @see constructor of WtpRelation
	 */
	initialize: function(annotation, start, end, wtp, prefix,
	                     relationName, relationValue, representation) {
		this.WtpAnnotation(annotation, start, end, wtp, prefix);
		this.WtpRelation(relationName, relationValue, representation);
	},

	/**
	 * @private - called by <initialize>
	 * 
	 * Constructor.
	 * 
	 * @param string relationName  The name of the relation
	 * @param string relationValue The value of the relation
	 * @param string representation The user representation
	 * 
	 */
	WtpRelation: function(relationName, relationValue, representation) {
		this.name = relationName;
		this.value = relationValue;
		this.representation = representation;
		this.splitValues = this.value.split(";");
		this.arity = this.splitValues.length + 1; // subject is also part of arity, thus (+1)
	},
	
	/** @return The value of this relation */
	getValue : function() {
		return this.value;
	},
	
	getSplitValues: function() {
		return this.splitValues;
	},
	
	getArity: function() {
		return this.arity;
	},
	
	/**
	 * @public
	 * 
	 * Renames the relation in the wiki text. The definition of the relation
	 * is not changed.
	 * 
	 * @param string newRelationName New name of the relation.
	 */
	rename: function(newRelationName) {
		var newAnnotation = "[[" + this.prefix + newRelationName + ":=" + this.value;
		if (this.representation) {
			newAnnotation += "|" + this.representation;
		}
		newAnnotation += "]]";
		this.name = newRelationName;
		this.replaceAnnotation(newAnnotation);
	},
	
	/**
	 * @public
	 * 
	 * Changes the value of the relation in the wiki text.
	 * 
	 * @param string newValue New value of the relation.
	 */
	changeValue: function(newValue) {
		var newAnnotation = "[[" + this.prefix + this.name + ":=" + newValue;
		if (this.representation) {
			newAnnotation += "|" + this.representation;
		}
		newAnnotation += "]]";
		this.value = newValue;
		this.replaceAnnotation(newAnnotation);
	},
	
	/**
	 * Replaces user representation of an annotation in the wiki text.
	 * 
	 * @param string newRepresentation New representation. Can be <null> or 
	 *               empty string.
	 */
	changeRepresentation : function(newRepresentation) {
		var newAnnotation = "[[" + this.prefix + this.name + ":=" + this.value;
		if (newRepresentation && newRepresentation != "") {
			newAnnotation += "|" + newRepresentation;
		}
		newAnnotation += "]]";
		this.representation = newRepresentation;
		this.replaceAnnotation(newAnnotation);
	},
	
	
	/**
	 * Returns a printable representation of the object.
	 */
	inspect: function() {
		var content = "Annotation: " + this.annotation + "<br />" +
					  "Name : " + this.name + "<br />" +
		              "Value: " + this.value + "<br />" +
		              "Rep. : " + this.representation + "<br />" +
		              "Start: " + this.start + "<br />" +
		              "End  : " + this.end + "<br />";
		
		return content;
	}
	
});


/**
 * Class for categories - derived from WtpAnnotation
 * 
 * Stores
 * - the name of the category
 * - the user representation.
 * 
 */
var WtpCategory = Class.create();
WtpCategory.prototype = Object.extend(new WtpAnnotation(), {
	
	/**
	 * @public
	 * @see constructor of WtpCategory
	 */
	initialize: function(annotation, start, end, wtp, prefix,
	                     categoryName, representation) {
		this.WtpAnnotation(annotation, start, end, wtp, prefix);
		this.WtpCategory(categoryName, representation);
	},

	/**
	 * @private - called by <initialize>
	 * 
	 * Constructor.
	 * 
	 * @param string categoryName  The name of the category
	 * @param string representation The user representation
	 * 
	 */
	WtpCategory: function(categoryName, representation) {
		this.name = categoryName;
		this.representation = representation;
	},

	/**
	 * @public
	 * 
	 * Renames the category in the wiki text. The definition of the category
	 * is not changed.
	 * 
	 * @param string newCategoryName New name of the category.
	 */
	changeCategory: function(newCategoryName) {
		var newAnnotation = "[[" + this.prefix + gLanguage.getMessage('CATEGORY') + newCategoryName;
		if (this.representation) {
			newAnnotation += "|" + this.representation;
		}
		newAnnotation += "]]";
		this.name = newCategoryName;
		this.replaceAnnotation(newAnnotation);
	},
	
	/**
	 * Replaces user representation of an annotation in the wiki text.
	 * 
	 * @param string newRepresentation New representation. Can be <null> or 
	 *               empty string.
	 */
	changeRepresentation : function(newRepresentation) {
		var newAnnotation = "[[" + this.prefix + gLanguage.getMessage('CATEGORY') + this.name;
		if (newRepresentation && newRepresentation != "") {
			newAnnotation += "|" + newRepresentation;
		}
		newAnnotation += "]]";
		this.representation = newRepresentation;
		this.replaceAnnotation(newAnnotation);
	},
	
	
	/**
	 * Returns a printable representation of the object.
	 */
	inspect: function() {
		var content = "Annotation: " + this.annotation + "<br />" +
					  "Name : " + this.name + "<br />" +
		              "Rep. : " + this.representation + "<br />" +
		              "Start: " + this.start + "<br />" +
		              "End  : " + this.end + "<br />";
		
		return content;
	}
	
});

/**
 * Class for links to other wiki articles - derived from WtpAnnotation
 * 
 * Stores
 * - the name linked article
 * - the user representation.
 * 
 */
var WtpLink = Class.create();
WtpLink.prototype = Object.extend(new WtpAnnotation(), {
	
	/**
	 * @public
	 * @see constructor of WtpLink
	 */
	initialize: function(annotation, start, end, wtp, prefix,
	                     link, representation) {
		this.WtpAnnotation(annotation, start, end, wtp, prefix);
		this.WtpLink(link, representation);
	},

	/**
	 * @private - called by <initialize>
	 * 
	 * Constructor.
	 * 
	 * @param string link  The content of the link
	 * @param string representation The user representation
	 * 
	 */
	WtpLink: function(link, representation) {
		this.name = link;
		this.representation = representation;
	},

	/**
	 * @public
	 * 
	 * Replaces a link in the wiki text.
	 * 
	 * @param string newLink The new link.
	 */
	changeLink: function(newLink) {
		var newAnnotation = "[[" + this.prefix + newLink;
		if (this.representation) {
			newAnnotation += "|" + this.representation;
		}
		newAnnotation += "]]";
		this.name = newLink;
		this.replaceAnnotation(newAnnotation);
	},

	/**
	 * Replaces user representation of an annotation in the wiki text.
	 * 
	 * @param string newRepresentation New representation. Can be <null> or 
	 *               empty string.
	 */
	changeRepresentation : function(newRepresentation) {
		var newAnnotation = "[[" + this.prefix + this.name;
		if (newRepresentation && newRepresentation != "") {
			newAnnotation += "|" + newRepresentation;
		}
		newAnnotation += "]]";
		this.representation = newRepresentation;
		this.replaceAnnotation(newAnnotation);
	},
	
	
	/**
	 * Returns a printable representation of the object.
	 */
	inspect: function() {
		var content = "Annotation: " + this.annotation + "<br />" +
					  "Name : " + this.name + "<br />" +
		              "Rep. : " + this.representation + "<br />" +
		              "Start: " + this.start + "<br />" +
		              "End  : " + this.end + "<br />";
		
		return content;
	}
	
});



// WikiTextParser.js
// under GPL-License
/**
 * WikiTextParser.js
 *
 * Class for parsing annotations in wiki text.
 *
 * @author Thomas Schweitzer
 */

var WTP_NO_ERROR = 0;
var WTP_UNMATCHED_BRACKETS = 1;

/**
 * Class for parsing WikiText. It extracts annotations in double brackets [[...]]
 * and recognizes relations, categories and links.
 *
 * You can
 * - retrieve a list of relations, categories and links as objects
 *   (see Annotation.js)
 * - change annotations in the wiki text (via the returned objects)
 * - add annotations
 * - remove annotations.
 */
var WikiTextParser = Class.create();

var gEditInterface = null;

WikiTextParser.prototype = {
	/**
	 * @public
	 *
	 * Constructor. Stores the textarea from the edit page and initializes the
	 * lists of annotations.
	 */
	initialize: function() {
		var txtarea;
		if (document.editform) {
			txtarea = document.editform.wpTextbox1;
		} else {
			// some alternate form? take the first one we can find
			var areas = document.getElementsByTagName('textarea');
			txtarea = areas[0];
		}

		this.textarea = txtarea;
		//this.text = this.textarea.value;
		if (gEditInterface == null) {
			gEditInterface = new SMWEditInterface();
		}
		this.editInterface = gEditInterface;
//		this.editInterface.initialize();
		this.text = this.editInterface.getValue();

		this.relations  = null;
		this.categories  = null;
		this.links  = null;
		this.error = WTP_NO_ERROR;
	},
	
	/**
	 * @public
	 * 
	 * Returns the error state of the last parsing process.
	 * 
	 * @return int error
	 * 			WTP_NO_ERROR - no error
	 * 			WTP_UNMATCHED_BRACKETS - Unmatched brackets [[ or ]]
	 */
	getError: function() {
		return this.error;
	},

	/**
	 * @puplic
	 *
	 * Returns the wiki text from the edit box of the edit page.
	 *
	 * @return string Text from the edit box.
	 */
	getWikiText: function() {
		//return this.editInterface.getValue();
		return this.text;
	},

	/**
	 * @public
	 *
	 * Returns the relations with the given name or null if it is not present.
	 *
	 * @return array(WtpRelation) An array of relation definitions.
	 */
	getRelation: function(name) {
		if (this.relations == null) {
			this.parseAnnotations();
		}
		var matching = new Array();

		for (var i = 0, num = this.relations.length; i < num; ++i) {
			var rel = this.relations[i];
			if (this.equalWikiName(rel.getName(), name)) {
				matching.push(rel);
			}
		}
		return matching.length == 0 ? null : matching;
	},


	/**
	 * @public
	 *
	 * Returns an array that contains the relations, that are annotated in
	 * the current wiki text. Relations within templates are not considered.
	 *
	 * @return array(WtpRelation) An array of relation definitions.
	 */
	getRelations: function() {
		if (this.relations == null) {
			this.parseAnnotations();
		}

		return this.relations;
	},

	/**
	 * @public
	 *
	 * Returns an array that contains the categories, that are annotated in
	 * the current wiki text. Categories within templates are not considered.
	 *
	 * @return array(WtpCategory) An array of category definitions.
	 */
	getCategories: function() {
		if (this.categories == null) {
			this.parseAnnotations();
		}

		return this.categories;
	},

	/**
	 * @public
	 *
	 * Returns the category with the given name or null if it is not present.
	 *
	 * @return WtpCategory The requested category or null.
	 */
	getCategory: function(name) {
		if (this.categories == null) {
			this.parseAnnotations();
		}

		for (var i = 0, num = this.categories.length; i < num; ++i) {
			var cat = this.categories[i];
			if (this.equalWikiName(cat.getName(), name)) {
				return cat;
			}
		}
		return null;
	},


	/**
	 * @public
	 *
	 * Returns an array that contains the links to wiki articles, that are
	 * annotated in the current wiki text. Links within templates are not
	 * considered.
	 *
	 * @return array(WtpLink) An array of link definitions.
	 */
	getLinks: function() {
		if (this.links == null) {
			this.parseAnnotations();
		}

		return this.links;
	},

	/**
	 * @public
	 *
	 * Adds a relation at the current cursor position or replaces the selection
	 * in the text editor.
	 *
	 * @param string name Name of the relation.
	 * @param string value Value of the relation.
	 * @param string representation Representation of the annotation
	 * @param bool append If <true>, the annotation is appended at the very end.
	 *
	 * IMPORTANT!!
	 * After that all parsed data is invalidated as the text might be severely
	 * modified. References to relations etc. are no longer valid. This object
	 * (the wiki text parser) can still be used but the methods <getRelations()>
	 * etc. must be called anew.
	 *
	 */
	 addRelation : function(name, value, representation, append) {
	 	var anno = "[[" + name + ":=" + value;
	 	if (representation) {
	 		anno += "|" + representation;
	 	}
	 	anno += "]]";
	 	this.addAnnotation(anno, append);
	 },

	/**
	 * @public
	 *
	 * Adds a category at the current cursor position or replaces the selection
	 * in the text editor.
	 *
	 * @param string name Name of the category.
	 * @param bool append If <true>, the annotation is appended at the very end.
	 *
	 * IMPORTANT!!
	 * After that all parsed data is invalidated as the text might be severely
	 * modified. References to relations etc. are no longer valid. This object
	 * (the wiki text parser) can still be used but the methods <getRelations()>
	 * etc. must be called anew.
	 *
	 */
	 addCategory : function(name, append) {
	 	var anno = "[["+gLanguage.getMessage('CATEGORY') + name;
	 	anno += "]]";
	 	this.addAnnotation(anno, append);
	 },

	/**
	 * @public
	 *
	 * Adds a link at the current cursor position or replaces the selection
	 * in the text editor.
	 *
	 * @param string link The name of the article that is linked.
	 * @param string representation Representation of the annotation
	 * @param bool append If <true>, the annotation is appended at the very end.
	 *
	 * IMPORTANT!!
	 * After that all parsed data is invalidated as the text might be severely
	 * modified. References to relations etc. are no longer valid. This object
	 * (the wiki text parser) can still be used but the methods <getRelations()>
	 * etc. must be called anew.
	 *
	 */
	 addLink : function(link, representation, append) {
	 	var anno = "[[" + link;
	 	if (representation) {
	 		anno += "|" + representation;
	 	}
	 	anno += "]]";
	 	this.addAnnotation(anno, append);
	 },

	/**
	 * @public
	 *
	 * Replaces the annotation described by <annoObj> with the text of
	 * <newAnnotation>. The wiki text is changed and updated in the text area.
	 *
	 * @param WtpAnnotation annoObj Description of the annotation.
	 * @param string newAnnotation New text of the annotation.
	 */
	replaceAnnotation: function(annoObj, newAnnotation) {
		//this.text = this.editInterface.getValue();
		var startText = this.text.substring(0,annoObj.getStart());
		var endText = this.text.substr(annoObj.getEnd());
		var diffLen = newAnnotation.length - annoObj.getAnnotation().length;

		// construct the new wiki text
		this.text = startText + newAnnotation + endText;
		//this.textarea.value = this.text;
		this.editInterface.setValue(this.text);

		// all following annotations have moved => update their location
		this.updateAnnotationPositions(annoObj.getStart(), diffLen);
	},

	/**
	 * Returns the text that is currently selected in the wiki text editor.
	 *
	 * @return string Currently selected text.
	 */
	getSelection: function() {
		return this.editInterface.getSelectedText();
	},

	/**
	 * Selects the text in the wiki text editor between the positions <start>
	 * and <end>.
	 *
	 * @param int start
	 * 			0-based start index of the selection
	 * @param int end
	 * 			0-based end index of the selection
	 *
	 */
	setSelection: function(start, end) {
		this.editInterface.setSelectionRange(start, end);
	},

	/**
	 * @private
	 *
	 * Parses the content of the edit box and retrieves relations, 
	 * categories and links. These are stored in internal arrays.
	 *
	 * <nowiki> and <ask>-sections are ignored.
	 */
	parseAnnotations: function() {

		this.relations  = new Array();
		this.categories = new Array();
		this.links      = new Array();
		this.error = WTP_NO_ERROR;

		// Parsing-States
		// 0 - find [[, <nowiki> or <ask>
		// 1 - find [[ or ]]
		// 2 - find <nowiki> or </nowiki>
		// 3 - find <ask> or </ask>
		var state = 0;
		var bracketCount = 0; // Number of open brackets "[["
		var nowikiCount = 0;  // Number of open <nowiki>-statements
		var askCount = 0;  	  // Number of open <ask>-statements
		var currentPos = 0;   // Starting index for next search
		var bracketStart = -1;
		var parsing = true;
		while (parsing) {
			switch (state) {
				case 0:
					// Search for "[[", "<nowiki>" or <ask>
					var findings = this.findFirstOf(currentPos, ["[[", "<nowiki>", "<ask"]);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+1;
					if (findings[1] == "[[") {
						// opening bracket found
						bracketStart = findings[0];
						bracketCount++;
						state = 1;
					} else if (findings[1] == "<nowiki>") {
						// <nowiki> found
						bracketStart = -1;
						nowikiCount++;
						state = 2;
					} else {
						// <ask> found
						bracketStart = -1;
						askCount++;
						state = 3;
					}
					break;
				case 1:
					// we are within an annotation => search for [[ or ]]
					var findings = this.findFirstOf(currentPos, ["[[", "]]"]);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+2;
					if (findings[1] == "[[") {
						// [[ found
						bracketCount++;
					} else {
						// ]] found
						bracketCount--;
						if (bracketCount == 0) {
							// all opening brackets are closed
							var anno = this.createAnnotation(this.text.substring(bracketStart, findings[0]+2),
							                                 bracketStart, findings[0]+2);
							if (anno) {
								if (anno instanceof WtpRelation) {
									this.relations.push(anno);
								} else if (anno instanceof WtpCategory) {
									this.categories.push(anno);
								} else if (anno instanceof WtpLink) {
									this.links.push(anno);
								}
							}
							state = 0;
						}
					}
					break;
				case 2:
					// we are within a <nowiki>-block
					// => search for <nowiki> or </nowiki>
					var findings = this.findFirstOf(currentPos, ["</nowiki>", "<nowiki>"]);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+7;
					if (findings[1] == "<nowiki>") {
						// <nowiki> found
						nowikiCount++;
					} else {
						// </nowiki> found
						nowikiCount--;
						if (nowikiCount == 0) {
							// all opening <nowiki>s are closed
							state = 0;
						}
					}
					break;
				case 3:
					// we are within an <ask>-block
					// => search for <ask> or </ask>
					var findings = this.findFirstOf(currentPos, ["</ask>", "<ask"]);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+4;
					if (findings[1] == "<ask") {
						// <ask> found
						askCount++;
					} else {
						// </ask> found
						askCount--;
						if (askCount == 0) {
							// all opening <ask>s are closed
							state = 0;
						}
					}
					break;
			}
		}
		if (bracketCount != 0) {
			this.error = WTP_UNMATCHED_BRACKETS;
		}
	},

	/**
	 * @private
	 *
	 * Analyzes an annotation and classifies it as relation, category
	 * or link to other articles. Corresponding objects (WtpRelation,
	 * WtpCategory and WtpLink) are created.
	 * TODO: I18N required for categories
	 *
	 * @param string annotation The complete annotation including the surrounding
	 *                          brackets e.g. [[attr:=1|one]]
	 * @param int start Start position of the annotation in the wiki text
	 * @param int end   End position of the annotation in the wiki text
	 *
	 * @return array(WtpAnnotation) An array of annotation definitions.
	 */
	createAnnotation : function(annotation, start, end) {
		var relRE  = /\[\[\s*(:?)([^:]*)(::|:=)([\s\S\n\r]*)\]\]/;
		var catRE  = /\[\[\s*[C|c]ategory:([\s\S\n\r]*)\]\]/;

		var relation = annotation.match(relRE);
		if (relation) {
			// found a relation
			// strip whitespaces from relation name
			var relName = relation[2].match(/[\s\n\r]*(.*)[\s\n\r]*/);
			var valRep = this.getValueAndRepresentation(relation[4]);
			return new WtpRelation(annotation, start, end, this, relation[1],
			                       relName[1], valRep[0], valRep[1]);
		}

		var category = annotation.match(catRE);
		if (category) {
			// found a category
			// strip whitespaces from category name
			var catName = category[1].match(/[\s\n\r]*(.*)[\s\n\r]*/);
			var valRep = this.getValueAndRepresentation(catName[1]);
			return new WtpCategory(annotation, start, end, this, "", // category[1], ignore prefix
			                       valRep[0], valRep[1]);
		}

		// annotation is a link
		var linkName = annotation.match(/\[\[[\s\n\r]*((.|\n)*)[\s\n\r]*\]\]/);
		var valRep = this.getValueAndRepresentation(linkName[1]);
		return new WtpLink(annotation, start, end, this, null,
		                   valRep[0], valRep[1]);

		return null;
	},

	/**
	 * @private
	 *
	 * If something has been replaced in the wiki text, the positions of all
	 * annotations following annotations has to be updated.
	 *
	 * @param int start All annotations starting after this index are moved.
	 *                 (The annotation starting at <start> is NOT moved.)
	 * @param int offset This offset is added to the position of the annotations.
	 */
	updateAnnotationPositions : function(start, offset) {
		if (offset == 0) {
			return;
		}
		var i;
		for (i = 0, len = this.relations.length; i < len; i++) {
			this.relations[i].move(offset, start);
		}
		for (i = 0, len = this.categories.length; i < len; i++) {
			this.categories[i].move(offset, start);
		}
		for (i = 0, len = this.links.length; i < len; i++) {
			this.links[i].move(offset, start);
		}
	},

	/**
	 * @private
	 *
	 * Adds the annotation to the wiki text. If some text is selected, it is
	 * replaced by the annotation. If <append> is <true>, the text is appended.
	 * Otherwise it is inserted at the cursor position.
	 *
	 * IMPORTANT!!
	 * After that all parsed data is invalidated as the text might be severely
	 * modified. References to relations etc. are no longer valid. This object
	 * (the wiki text parser) can still be used but the methods <getRelations()>
	 * etc. must be called anew.
	 *
	 * @param string annotation Annotation that is added to the wiki text.
	 * @param bool append If <true>, the annotation is appended at the very end.
	 */
	addAnnotation : function(annotation, append) {
		if (append) {
			//this.textarea.value = this.textarea.value + annotation;
			this.editInterface.setValue(this.editInterface.getValue() + annotation);
		} else {
			this.replaceText(annotation);
		}
		// invalidate all parsed data
		this.initialize();
	},

	/**
	 *
	 * @private
	 *
	 * Removes the annotation from the internal arrays.
	 *
	 * @param WtpAnnotation annotation The annotation that is removed.
	 *
	 */
	removeAnnotation: function(annotation) {
		var annoArray = null;
		if (annotation instanceof WtpRelation) {
			annoArray = this.relations;
		} else if (annotation instanceof WtpCategory) {
			annoArray = this.categories;
		} else if (annotation instanceof WtpLink) {
			annoArray = this.links;
		} else {
			return;
		}

		for (var i = 0, len = annoArray.length; i < len; i++) {
			if (annoArray[i] == annotation) {
				annoArray.splice(i, 1);
				break;
			}
		}
	},

	/**
	 * @private
	 *
	 * Finds the first occurrence of one of the search strings in the current
	 * wiki text or in <findIn>.
	 *
	 * <searchStrings> is an array of strings. This function finds out, which
	 * of these strings appears first in the wiki text or in <findIn>, starting
	 * at position <startPos>.
	 *
	 * @param int startPos Position where the search starts in the wiki text or
	 *                     <findIn>
	 * @param array(string) searchStrings Array of strings that are searched.
	 * @param string findIn If <null> the search strings are searched in the
	 *               current wiki text otherwise in <findIn>
	 *
	 * @return [int pos, string found] The position <pos> of the first occurrence
	 *              of the string <found>.
	 */
	findFirstOf : function(startPos, searchStrings, findIn) {

		var firstPos = -1;
		var firstMatch = null;

		for (var i = 0, len = searchStrings.length; i < len; ++i) {
			var ss = searchStrings[i];
			var pos = findIn ? findIn.indexOf(ss, startPos)
			                 : this.text.indexOf(ss, startPos);
			if (pos != -1 && (pos < firstPos || firstPos == -1)) {
				firstPos = pos;
				firstMatch = ss;
			}
		}

		return [firstPos, firstMatch];

	},


	/**
	 * @private
	 *
	 * The value in an annotation can consist of the actual value and the user
	 * representation. This functions splits and returns both.
	 *
	 * @param string valrep Contains a value and an optional representation
	 *                      e.g. "3.141|about 3"
	 * @return [string value, string representation]
	 *                 value: the extracted value
	 *                 representation: the extracted representation or <null>
	 */
	getValueAndRepresentation: function(valrep) {
		// Parsing-States
		// 0 - find [[, {{ or |
		// 1 - find [[ or ]]
		// 2 - find {{ or }}
		var state = 0;
		var bracketCount = 0; // Number of open brackets "[["
		var curlyCount = 0;   // Number of open brackets "{{"
		var currentPos = 0;   // Starting index for next search
		var parsing = true;
		while (parsing) {
			switch (state) {
				case 0:
					// Search for "[[", "{{" or |
					var findings = this.findFirstOf(currentPos, ["[[", "{{", "|"], valrep);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+1;
					if (findings[1] == "[[") {
						// opening bracket found
						bracketCount++;
						state = 1;
					} else if (findings[1] == "{{") {
						// opening curly bracket found
						curlyCount++;
						state = 2;
					} else {
						// | found
						if (bracketCount == 0) {
							var val = valrep.substring(0, findings[0]);
							var rep = valrep.substring(findings[0]+1);
							return [val, rep];
						}
					}
					break;
				case 1:
					// we are within an annotation => search for [[ or ]]
					var findings = this.findFirstOf(currentPos, ["[[", "]]"], valrep);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+2;
					if (findings[1] == "[[") {
						// [[ found
						bracketCount++;
					} else {
						// ]] found
						bracketCount--;
						if (bracketCount == 0) {
							state = 0;
						}
					}
					break;
				case 2:
					// we are within a template => search for {{ or }}
					var findings = this.findFirstOf(currentPos, ["{{", "}}"], valrep);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+2;
					if (findings[1] == "{{") {
						// {{ found
						curlyCount++;
					} else {
						// }} found
						curlyCount--;
						if (curlyCount == 0) {
							state = 0;
						}
					}
					break;
			}
		}
		return [valrep, null];
	},


	/**
	 * Inserts a text at the cursor or replaces the current selection.
	 *
	 * @param string text The text that is inserted.
	 *
	 */
	replaceText : function(text)  {
		this.editInterface.setSelectedText(text);
	},

	/**
	 * Checks if two names are equal with respect to the wiki rule i.e. the
	 * first character is case insensitive, the rest is.
	 *
	 * @param string name1 The first name to compare
	 * @param string name2 The second name to compare
	 *
	 * @return bool <true> is the names are equal, <false> otherwise.
	 */
	equalWikiName : function(name1, name2) {
		if (name1.substring(1) == name2.substring(1)) {
			if (name1.charAt(0).toLowerCase() == name2.charAt(0).toLowerCase()) {
				return true;
			}
		}
		return false;
	}
};


// SMW_Ontology.js
// under GPL-License
/**
* SMW_Ontology.js
* 
* Helper functions for the creation/modification of ontologies.
* 
* @author Thomas Schweitzer
*
*/

var OntologyModifier = Class.create();

/**
 * Class for modifying the ontology. It supports
 * - creating new articles
 * - creating sub-attributes and sub-relations of the current article
 * - creating super-attributes and super-relations of the current article
 * 
 */
OntologyModifier.prototype = {

	/**
	 * @public
	 * 
	 * Constructor.
	 */
	initialize: function() {
		this.redirect = false;
	},


	/**
	 * @public
	 * 
	 * Checks if an article exists in the wiki. This is an asynchronous ajax call.
	 * When the result is returned, the function <callback> will be called.
	 * 
	 * @param string title 
	 * 			Full title of the article.
	 * @param function callback
	 * 			This function will be called, when the ajax call returns. Its
	 * 			signature must be:
	 * 			callback(string title, bool articleExists)
	 * @param string title
	 * 			Title of the Page without Namespace  
	 * @param string optparam
	 * 			An optional parameter which will be passed through to the
	 *  		callbackfunktion 
	 * @param string domElementID
	 * 			Id of the DOM element that started the query. Will be passed
	 * 			through to the callbackfunktion.
	 */
	existsArticle : function(pageName, callback, title, optparam, domElementID) {
		function ajaxResponseExistsArticle(request) {
			var answer = request.responseText;
			var regex = /(true|false)/;
			var parts = answer.match(regex);
			
			if (parts == null) {
				var errMsg = gLanguage.getMessage('ERR_QUERY_EXISTS_ARTICLE');
				errMsg = errMsg.replace(/\$-page/g, pageName);
				alert(errMsg);
				return;
			}
			callback(pageName, parts[1] == 'true' ? true : false, title, optparam, domElementID);
			
		};
		
		sajax_do_call('smwfExistsArticle', 
		              [pageName], 
		              ajaxResponseExistsArticle.bind(this));
		              
		              
	},

	/**
	 * @public
	 * 
	 * Creates a new article in the wiki or appends some text if it already 
	 * exists.
	 * 
	 * @param string title 
	 * 			Title of the article.
	 * @param string initialContent 
	 * 			Initial content of the article. This is only set, if the article
	 * 			is newly created.
	 * @param string optionalText 
	 * 			This text is appended to the article, if it is not already part
	 * 			of it. The text may contain variables of the PHP-language files 
	 * 			that are replaced by their representation.
	 * @param string creationComment
	 * 			This text describes why the article has been created. 
	 * @param bool redirect If <true>, the system asks the user, if he he wants 
	 * 			to be redirected to the new article after its creation.
	 */
	createArticle : function(title, content, optionalText, creationComment,
	                         redirect) {
		this.redirect = redirect;
		sajax_do_call('smwfCreateArticle', 
		              [title, content, optionalText, creationComment], 
		              this.ajaxResponseCreateArticle.bind(this));
		              
	},
	
	/**
	 * @public
	 * 
	 * Creates a new article that defines an attribute.
	 * 
	 * @param string title 
	 * 			Name of the new article/attribute without the namespace.
	 * @param string initialContent
	 * 			Initial content of the article.
	 * @param string domain
	 * 			Domain of the attribute.
	 * @param string type
	 * 			Type of the attribute.
	 */
	createAttribute : function(title, initialContent, domain, type) {
		var schema = "";
		if (domain != null && domain != "") {
			schema += "\n[[SMW_SSP_HAS_DOMAIN_HINT::"+gLanguage.getMessage('CATEGORY')+domain+"]]";
		}
		if (type != null && type != "") {
			schema += "\n[[SMW_SP_HAS_TYPE::"+gLanguage.getMessage('TYPE')+type+"]]";
		}
		this.createArticle(gLanguage.getMessage('PROPERTY')+title, 
						   initialContent, schema,
						   "Create a property for category " + domain, true);
	},
	
	/**
	 * @public
	 * 
	 * Creates a new article that defines a relation.
	 * 
	 * @param string title 
	 * 			Name of the new article/relation without the namespace.
	 * @param string initialContent
	 * 			Initial content of the article.
	 * @param string domain
	 * 			Domain of the relation.
	 * @param string range
	 * 			Range of the relation.
	 */
	createRelation : function(title, initialContent, domain, ranges) {
		var schema = "";
		if (domain != null && domain != "") {
			schema += "\n[[SMW_SSP_HAS_DOMAIN_HINT::"+gLanguage.getMessage('CATEGORY')+domain+"]]";
		}
		if (ranges != null) {
			if (ranges.length == 1) { // normal binary relation
					if (ranges[0].indexOf(gLanguage.getMessage('TYPE')) == 0) {
						schema += "\n[[SMW_SP_HAS_TYPE::"+ranges[0]+"]]";
					} else {
						schema += "\n[[SMW_SSP_HAS_RANGE_HINT::"+ranges[0]+"]]";
					}
			 	
			} else if (ranges.length > 1) { // n-ary relation
				var rangeStr = "\n[[SMW_SP_HAS_TYPE:="
				for(var i = 0, n = ranges.length; i < n; i++) {
					if (ranges[i].indexOf(gLanguage.getMessage('TYPE')) == 0) {
						if (i < n-1) rangeStr += ranges[i]+";"; else rangeStr += ranges[i];
					} else {
						if (i < n-1) {
							 rangeStr += gLanguage.getMessage('TYPE_PAGE')+";"; 
						} else {
							rangeStr += gLanguage.getMessage('TYPE_PAGE');
						}
						schema += "\n[[SMW_SSP_HAS_RANGE_HINT::"+ranges[i]+"]]";
					}
			 	}
			 	schema += rangeStr+"]]";
			} 
		}
		
		this.createArticle(gLanguage.getMessage('PROPERTY')+title, 
						   initialContent, schema,
						   gLanguage.getMessage('CREATE_PROP_FOR_CAT').replace(/\$cat/g, domain),
						   true);
	},
	
	/**
	 * @public
	 * 
	 * Creates a new article that defines a category.
	 * 
	 * @param string title 
	 * 			Name of the new article/category without the namespace.
	 * @param string initialContent
	 * 			Initial content of the article.
	 */
	createCategory : function(title, initialContent) {
		this.createArticle(gLanguage.getMessage('CATEGORY')+title, 
						   initialContent, "",
						   gLanguage.getMessage('CREATE_CATEGORY'), true);
	},
	
	/**
	 * @public
	 * 
	 * Creates a sub-property of the current article, which must be an attribute
	 * or a relation. If not, an alert box is presented.
	 * 
	 * @param string title 
	 * 			Name of the new article (sub-property) without the namespace.
	 * @param string initialContent
	 * 			Initial content of the article for the sub-property.
	 * @param boolean openNewArticle
	 * 			If <true> or not specified, the newly created article is opened
	 *          in a new tab.
	 */
	createSubProperty : function(title, initialContent, openNewArticle) {
		if (openNewArticle == undefined) {
			openNewArticle = true;
		}
		var schemaProp = this.getSchemaProperties();
		if (   wgNamespaceNumber == 102    // SMW_NS_PROPERTY
		    || wgNamespaceNumber == 100) { // SMW_NS_RELATION
			this.createArticle(gLanguage.getMessage('PROPERTY')+title, 
							 initialContent, 
							 schemaProp + 
							 "\n[[SMW_SP_SUBPROPERTY_OF::"+wgPageName+"]]",
							 gLanguage.getMessage('CREATE_SUB_PROPERTY'), 
							 openNewArticle);
			
		} else {
			alert(gLanguage.getMessage('NOT_A_PROPERTY'))
		}
		
	},
	
	/**
	 * @public
	 * 
	 * Creates a super-property of the current article, which must be an attribute
	 * or a relation. If not, an alert box is presented. The current article is 
	 * augmented with the corresponding annotation.
	 * 
	 * @param string title 
	 * 			Name of the new article (super-property) without the namespace.
	 * @param string initialContent
	 * 			Initial content of the article for the super-property.
	 * @param boolean openNewArticle
	 * 			If <true> or not specified, the newly created article is opened
	 *          in a new tab.
	 */
	createSuperProperty : function(title, initialContent, openNewArticle) {
		if (openNewArticle == undefined) {
			openNewArticle = true;
		}
		var schemaProp = this.getSchemaProperties();
		var wtp = new WikiTextParser();
		if (   wgNamespaceNumber == 102 // SMW_NS_PROPERTY
		    || wgNamespaceNumber == 100) {  // SMW_NS_RELATION
			this.createArticle(gLanguage.getMessage('PROPERTY')+title, 
							 initialContent, 
							 schemaProp,
							 gLanguage.getMessage('CREATE_SUPER_PROPERTY'), 
							 openNewArticle);
							 
			// append the sub-property annotation to the current article
			wtp.addRelation("subproperty of", gLanguage.getMessage('PROPERTY')+title, "", true);
			
		} else {
			alert(gLanguage.getMessage('NOT_A_PROPERTY'));
		}
				
	},
	
	/**
	 * @public
	 * 
	 * Creates a super-category of the current article, which must be category. 
	 * If not, an alert box is presented. The current article is 
	 * augmented with the corresponding annotation.
	 * 
	 * @param string title 
	 * 			Name of the new article (super-category) without the namespace.
	 * @param string initialContent
	 * 			Initial content of the article for the super-category.
	 * @param boolean openNewArticle
	 * 			If <true> or not specified, the newly created article is opened
	 *          in a new tab.
	 */
	createSuperCategory : function(title, initialContent, openNewArticle) {
		if (openNewArticle == undefined) {
			openNewArticle = true;
		}
		var wtp = new WikiTextParser();
		if (wgNamespaceNumber == 14) {
			this.createArticle(gLanguage.getMessage('CATEGORY')+title, initialContent, "",
							   gLanguage.getMessage('CREATE_SUPER_CATEGORY'), 
							   openNewArticle);
							 
			// append the sub-category annotation to the current article
			wtp.addCategory(title, "", true);
			
		} else {
			alert(gLanguage.getMessage('NOT_A_CATEGORY'))
		}
				
	},
	
	/**
	 * @public
	 * 
	 * Creates a sub-category of the current article, which must be category. 
	 * If not, an alert box is presented. The new article is 
	 * augmented with the corresponding annotation.
	 * 
	 * @param string title 
	 * 			Name of the new article (sub-category) without the namespace.
	 * @param string initialContent
	 * 			Initial content of the article for the sub-category.
	 */
	createSubCategory : function(title, initialContent) {
		if (wgNamespaceNumber == 14) {
			this.createArticle(gLanguage.getMessage('CATEGORY')+title, initialContent, 
			                   "[["+gLanguage.getMessage('CATEGORY')+wgTitle+"]]",
							   gLanguage.getMessage('CREATE_SUB_CATEGORY'), true);			
		} else {
			alert(gLanguage.getMessage('NOT_A_CATEGORY'))
		}
				
	},
	
	
	/**
	 * @private
	 * 
	 * Retrieves all relevant schema properties of the current article and
	 * collects all their wiki text representations in one string. 
	 * 
	 * @return string A string with all schema properties of the current article.
	 */
	getSchemaProperties : function() {
		var wtp = new WikiTextParser();
		var props = new Array();
		props.push(wtp.getRelation("has type"));
		props.push(wtp.getRelation("Has domain hint"));
		props.push(wtp.getRelation("Has range hint"));
		props.push(wtp.getRelation("Has max cardinality"));
		props.push(wtp.getRelation("Has min cardinality"));
		
		var schemaAnnotations = "";
		for (var typeIdx = 0, nt = props.length; typeIdx < nt; ++typeIdx) {
			var type = props[typeIdx];
			if (type != null) {
				for (var annoIdx = 0, na = type.length; annoIdx < na; ++annoIdx) {
					var anno = type[annoIdx];
					schemaAnnotations += anno.getAnnotation() + "\n";
				}
			}
		}
		var transitive = wtp.getCategory("Transitive relations");
		var symmetric = wtp.getCategory("Symmetrical relations");
		
		if (transitive) {
			schemaAnnotations += transitive.getAnnotation() + "\n";
		}
		if (symmetric) {
			schemaAnnotations += symmetric.getAnnotation() + "\n";
		}

		return schemaAnnotations;
	},

	/**
	 * This function is called when the ajax request for the creation of a new
	 * article returns. The answer has the following format:
	 * bool, bool, string
	 * - The first boolean signals success (true) of the operation.
	 * - The second boolean signals that a new article has been created (true), or
	 *   that it already existed(false).
	 * - The string contains the name of the (new) article.
	 * 
	 * @param request Created by the framework. Contains the ajay request and
	 *                its result.
	 * 
	 */
	ajaxResponseCreateArticle: function(request) {
		if (request.status != 200) {
			alert(gLanguage.getMessage('ERROR_CREATING_ARTICLE'));
			return;
		}
		
		var answer = request.responseText;
		var regex = /(true|false),(true|false),(.*)/;
		var parts = answer.match(regex);
		
		if (parts == null) {
			alert(gLanguage.getMessage('ERROR_CREATING_ARTICLE'));
			return;
		}
		
		var success = parts[1];
		var created = parts[2];
		var title = parts[3];
		
		if (success == "true") {
			if (this.redirect) {
				// open the new article in another tab.
				window.open("index.php?title="+title,"_blank");
			}
		}
	}
}


// SMW_DataTypes.js
// under GPL-License
/**
* SMW_DataTypes.js
* 
* Helper functions for retrieving the data types that are currently provided
* by the wiki. The types are retrieved by an ajax call an stored for quick access.
* The list of types can be refreshed.
* 
* There is a singleton instance of this class that is initialized at startup:
* gDataTypes
* 
* @author Thomas Schweitzer
*
*/

var DataTypes = Class.create();

/**
 * Class for retrieving and storing the wiki's data types.
 * 
 */
DataTypes.prototype = {

	/**
	 * @public
	 * 
	 * Constructor.
	 */
	initialize: function() {
		this.builtinTypes = null;
		this.userTypes = null;
		this.callback = new Array();
		this.refresh();
		this.refreshPending = false;
		
	},

	/**
	 * Returns the array of builtin types.
	 * 
	 * @return array<string> 
	 * 			List of builtin types or <null> if there was no answer from the 
	 * 			server yet.
	 *         
	 */
	getBuiltinTypes: function() {
		return this.builtinTypes;
	},
	
	/**
	 * Returns the array of user defined types.
	 * 
	 * @return array<string> 
	 * 			List of user defined types or <null> if there was no answer from
	 * 			the server yet.
	 *         
	 */
	getUserDefinedTypes: function() {
		return this.userTypes;
	},
	
	/**
	 * @public
	 * 
	 * Makes a new request for the current data types. It will take a 
	 * while until they are available.
	 * 
	 */
	refresh: function(callback) {
		if (callback) {
			this.callback.push(callback);
		}
		this.userUpdated    = false;
		this.builtinUpdated = false;
		if (!this.refreshPending) {
			this.refreshPending = true;
			sajax_do_call('smwfGetUserDatatypes', 
			              [], 
			              this.ajaxResponseGetDatatypes.bind(this));
			sajax_do_call('smwfGetBuiltinDatatypes', 
			              [], 
			              this.ajaxResponseGetDatatypes.bind(this));
		}

	},
	
	/**
	 * @private
	 * 
	 * This function is called when the ajax call returns. The data types
	 * are stored in the internal arrays.
	 */
	ajaxResponseGetDatatypes: function(request) {
		if (request.status != 200) {
			// request failed
			return;
		}
		var types = request.responseText.split(",");

		if (types[0].indexOf("User defined types") >= 0) {
			this.userUpdated = true;
			// received user defined types
			this.userTypes = new Array(types.length-1);
			for (var i = 1, len = types.length; i < len; ++i) {
				this.userTypes[i-1] = types[i];
			}
		} else {
			// received builtin types
			this.builtinUpdated = true;
			this.builtinTypes = new Array(types.length-1);
			for (var i = 1, len = types.length; i < len; ++i) {
				this.builtinTypes[i-1] = types[i];
			}
		}
		if (this.userUpdated && this.builtinUpdated) {
			// If there are articles for builtin types, these types appear as
			// builtin and as user defined types => remove them from the list
			// of user defined types.
			var userTypes = new Array();
			for (var u = 0; u < this.userTypes.length; u++) {
				var found = false;
				for (var b = 0; b < this.builtinTypes.length; b++) {
					if (this.userTypes[u] == this.builtinTypes[b]) {
						found = true;
						break;
					}
				}
				if (!found) {
					userTypes.push(this.userTypes[u]);
				}
			}
			this.userTypes = userTypes;
			
			for (var i = 0; i < this.callback.length; ++i) {
				this.callback[i]();
			}
			this.callback.clear();
			this.refreshPending = false;
		}
	}

}

var gDataTypes = new DataTypes();


// SMW_GenericToolbarFunctions.js
// under GPL-License
var GenericToolBar = Class.create();

GenericToolBar.prototype = {

initialize: function() {

},

createList: function(list,id) {
	var len = list == null ? 0 : list.length;
	var divlist = "";
	switch (id) {
		case "category":
			divlist ="<div id=\"" + id +"-tools\">" +
					"<a href=\"javascript:catToolBar.newItem()\" class=\"menulink\">"+gLanguage.getMessage('ANNOTATE')+"</a>" +
					"<a href=\"javascript:catToolBar.newCategory()\" class=\"menulink\">"+gLanguage.getMessage('CREATE')+"</a>";
			if (wgNamespaceNumber == 14) {
				divlist += "<a href=\"javascript:catToolBar.CreateSubSup()\" class=\"menulink\">"+gLanguage.getMessage('SUB_SUPER')+"</a>";
			}
			divlist += "</div>";
	 		break;
		case "relation":
	  			divlist ="<div id=\"" + id +"-tools\">" +
	  					 "<a href=\"javascript:relToolBar.newItem()\" class=\"menulink\">"+gLanguage.getMessage('ANNOTATE')+"</a>" +
					 "<a href=\"javascript:relToolBar.newRelation()\" class=\"menulink\">"+gLanguage.getMessage('CREATE')+"</a>";
				//regex for checking attribute namespace. 
				//since there's no special namespace number anymore since atr and rel are united 
				var attrregex =	new RegExp("Attribute:.*");
				if (wgNamespaceNumber == 100 || wgNamespaceNumber == 102  || attrregex.exec(wgPageName) != null) {
					divlist += "<a href=\"javascript:relToolBar.CreateSubSup()\" class=\"menulink\">"+gLanguage.getMessage('SUB_SUPER')+"</a>";
				}
	  			divlist += "<a href=\"javascript:relToolBar.newPart()\" class=\"menulink\">"+gLanguage.getMessage('MHAS_PART')+"</a>";
	  			divlist += "</div>";
	  		break;
	}
  	divlist += "<div id=\"" + id +"-itemlist\"><table id=\"" + id +"-table\">";

	var path = wgArticlePath;
	var dollarPos = path.indexOf('$1');
	if (dollarPos > 0) {
		path = path.substring(0, dollarPos);
	}

  	for (var i = 0; i < len; i++) {
  		var rowSpan = "";
  		var firstValue = "";
  		var multiValue = ""; // for n-ary relations
  		var value = "";
  		var prefix = "";
  		
		switch (id)	{
			case "category":
	  			fn = "catToolBar.getselectedItem(" + i + ")";
	  			firstValue = list[i].getValue ? list[i].getValue(): "";
	  			prefix = gLanguage.getMessage('CATEGORY');
	 			 break
			case "relation":
	  			fn = "relToolBar.getselectedItem(" + i + ")";
	  			prefix = gLanguage.getMessage('PROPERTY');
	  		
	  			var rowSpan = 'rowspan="'+(list[i].getArity()-1)+'"';
	  			var values = list[i].getSplitValues();
	  			firstValue = values[0];
	  			var valueLink;

				valueLink = '<span title="' + firstValue + '">' + firstValue + '<span>';
				firstValue = valueLink;

	  			// HTML of parameter rows (except first)
	  			for (var j = 1, n = list[i].getArity()-1; j < n; j++) {
					valueLink = 
					'<span title="' + values[j] + '">' + values[j] +
				    '</span>';
//						values[j];
					multiValue += 
						"<tr>" +
							"<td class=\"" + id + "-col2\">" + 
							valueLink + 
							" </td>" +
						"</tr>";
	  			}
  			break
		}
		
		//Checks if getValue exists if no it's an Category what allows longer text
		var shortName = list[i].getValue ? list[i].getName() : list[i].getName();
		var elemName;
		//Construct the link
		elemName = '<a href="'+wgServer+path+prefix+list[i].getName();
		elemName += '" target="blank" title="' + shortName +'">' + shortName + '</a>';
		divlist += 	"<tr>" +
				"<td "+rowSpan+" class=\"" + id + "-col1\">" + 
					elemName + 
				" </td>" +
				"<td class=\"" + id + "-col2\">" + firstValue + " </td>" + // first value row
		           	"<td "+rowSpan+" class=\"" + id + "-col3\">" +
		           	'<a href=\"javascript:' + fn + '">' +
		           	'<img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/edit.gif"/></a>' +
		           
		           	'</tr>' + multiValue; // all other value rows
  	}
  	divlist += "</table></div>";
  	return divlist;
},

cutdowntosize: function(word, size /*, Optional: maxrows */ ){
	return word;
	var result;
	var subparts= new Array();
	var from;
	var to;
	
	arguments.length == 3 ? maxrows = arguments[2] : maxrows = 0;
	
	//Check in how many parts with full size will the string be divided
	var partscount = parseInt(word.length / size);
	//if theres a rest
	if((word.length % size) != 0){
	 partscount++;
	}
	for(var part=0; part < partscount; part++){
		//Calculate boundaries of the substring to get
	   	from = ((part)*size);
	   	to = (((part)*size)+(size));
	   	//Check if stringlength is exceeded
	   	if(to>word.length){
	   		to=word.length;
	   	};
	   	//Check if maximum rows are exceeded 
	   	if(maxrows!=0 && maxrows == part +1 ){
	   		//Add '...' to the last substring, which will be shown, without exceeding sizelength
	   		if((to-from)<size-3){ 
	   			subparts[part] = word.substring(from,to) + "...";
	   		} else {
	   			subparts[part] = word.substring(from, from+size-3) + "...";
	   		}
	   		break;
	   	} else {
	   	    subparts[part] = word.substring(from,to);	
	   	}
	}
	//Build result with linebreakingcharactes
	result = subparts[0].replace(/\s/g, '&nbsp;');;
	for(var part=1; part < subparts.length; part++){
		result += "<br>" + subparts[part].replace(/\s/g, '&nbsp;');
	}
	return (result ? result : "");
},


/**
 * This function triggers an blur event for the given element
 * so checks will run if element was automaticall filled with marked text
 * This is a _quick_ and _dirty_ implementation, which should be replaced 
 * with redesign 
 */
triggerEvent: function(element){
	if(element){
		element.focus();
		element.blur();
		element.focus();
	}
	
}

};//CLASS END



/*
 * The EventManager allows to observe events using prototype 
 * and stopobserving all previously registered events, with one call.   
 * Usage:
 *  
 *  Register:
 * 	this.eventManager = new EventManager();
 * 	this.eventManager.registerEvent('wpTextbox1','keyup',this.showAlert.bind(this));
 * 
 * 	Deregister:
 * 	this.eventManager.deregisterAllEvents();
 * 
 */
var EventManager = Class.create();

EventManager.prototype = {
	
	initialize: function() {
		this.eventlist = new Array();
	},
	
	registerEvent: function(element, eventName, handler) {
		var event = new Array(element,eventName,handler);
		this.eventlist.push(event);
		Event.observe(element,eventName,handler);
	},
	
	deregisterAllEvents: function() {
		this.eventlist.each( 
			this.stopEvent
		);
		this.eventlist = new Array();
		
	},
	
	stopEvent: function(item) {
		if (item == null) {
			return;
		}
		var obj = $(item[0]);
		if (!obj) {
			return;
		}
		
		Event.stopObserving(item[0],item[1],item[2])
	},
	
	deregisterEventsFromItem: function(itemID) {
		for(var i = 0; i < this.eventlist.length; i++) { 
				if (this.eventlist[i] != null && this.eventlist[i][0] == itemID) {
					this.stopEvent(this.eventlist[i]);
					this.eventlist[i] = null;
				}		
		} 
	
	}
	
};
	
var EventActions = Class.create();
EventActions.prototype = {
	
	initialize: function() {		
	},
	
	eventActions: function() {
		this.istyping = false;
		this.registered = false
	},
	
	setIsTyping: function(bool) {
		this.istyping = bool;
	},
	
	getIsTyping: function() {
		return this.istyping;
	},
	
	isEmpty: function(element){
		if(element.getValue().strip()!='' && element.getValue()!= null ){
			return false;
		} else {
			return true;
		}
	},
	
	targetelement: function(event) {
		return (event.srcElement ? event.srcElement : (event.target ? event.target : event.currentTarget));
	},
	
	timedcallback: function(fnc) {
		if(!this.registered){
			this.registered = true;
			var cb = this.callback.bind(this,fnc);
			setTimeout(cb,500);
		} 
	},
	
	callback: function(fnc){
		if(this.istyping){
			this.istyping = false;
			var cb = this.callback.bind(this,fnc);
			setTimeout(cb,500);
		} else {	
			fnc();
			this.registered = false;
			this.istyping = false;
		}
	} 
	

}

/**
 * The class STBEventActions contains the essential event callbacks for input
 * fields in the semantic toolbar. Their behaviour can be controlled by special
 * attributes. Thus checks for emptyness and correct syntax can be defined. The 
 * checks are performed during key-up and blur events.
 * 
 * There is a singleton for this class: gSTBEventActions. It should be used for 
 * registering the event callbacks.
 */
var STBEventActions = Class.create();
STBEventActions.prototype = Object.extend(new EventActions(),{

	/*
	 * Initializes this object.
	 */
	initialize: function() {
		this.om = new OntologyModifier();
		// As actions for key-up events are delayed, the last event is stored.
		this.keyUpEvent = null;
		this.pendingIndicator = null;
	},

	/*
	 * Callback for key-up events. It starts a timer that calls <delayedKeyUp>
	 * when the user finishes typing.
	 * 
	 * @param event 
	 * 			The key-up event.
	 */
	onKeyUp: function(event){
		
		this.setIsTyping(true);
		var key = event.which || event.keyCode;
		if (key == Event.KEY_RETURN) {
			// set focus on next element in tab order
			var elem = $(event.target);
			if (elem.type == 'a') {
				// found a link
				return true;
			}
			// find the next element in the tab order
			var tabIndex = elem.getAttribute("tabIndex");
			if (!tabIndex) {
				return false;
			}
			tabIndex = tabIndex*1 + 1;
			var div = elem.up('div');
			var children = div.descendants();
			for (var i = 0; i < children.length; ++i) {
				var child = children[i];
				var ti = child.getAttribute("tabIndex");
				if (ti && ti*1 == tabIndex) {
					if (child.disabled == true 
					    || !child.visible()) {
						tabIndex++;
					} else {
						child.focus();
						break;
					}
				}
			}
			return false;
		}		
		
		this.keyUpEvent = event;
		this.timedcallback(this.delayedKeyUp.bind(this));
		
	},
	
	/*
	 * Callback for blur events. 
	 * Checks if input fields are empty and performs the specified syntax checks
	 * if not.
	 * 
	 * @param event 
	 * 			The blur event.
	 */
	onBlur: function(event) {
		var target = $(event.target);

		var oldValue = target.getAttribute("smwOldValue");
		if (oldValue && oldValue == target.value) {
			// content if input field did not change => return
			return;
		}
		target.setAttribute("smwOldValue", target.value);
		
		if (this.checkIfEmpty(target) == false) {
			this.handleCheck(target);
		}
		this.doFinalCheck(target);
		
		
	},
	
	/*
	 * Callback for click events. 
	 * 
	 * 
	 * @param event 
	 * 			The click event.
	 */
	onClick: function(event) {
		var target = $(event.target);
		if (target.type == 'radio') {
			// a radio button has been clicked.
			this.doFinalCheck(target);
		}
	},

	/*
	 * Callback for change events. 
	 * 
	 * 
	 * @param event 
	 * 			The change event.
	 */
	onChange: function(event) {
		var target = $(event.target);
		if (this.checkIfEmpty(target) == false) {
			this.handleCheck(target);
		}
		this.handleChange(target);
		this.doFinalCheck(target);
	},
	
	/*
	 * @public
	 * After a container has been created and filled with values, an initial
	 * check on all input elements can be started.
	 * 
	 * @param Object target
	 * 			DIV-container that contains the input elements to check.
	 */
	initialCheck: function(target) {

		var children = target.descendants();
		
		var elem;
		for (var i = 0, len = children.length; i < len; ++i) {
			elem = children[i];
			var oldValue = elem.getAttribute("smwOldValue");
			if (!oldValue || oldValue != elem.value) {
				// content if input field did change => perform check

				if (this.checkIfEmpty(elem) == false) {
					this.handleCheck(elem);
				}
				elem.setAttribute("smwOldValue", elem.value);
				
			}
		}
		this.doFinalCheck(elem);
		
	},
	
	/*
	 * This callback for key-up events is called after the user has finished 
	 * typing. The last key-up event is stored in <this.keyUpEvent>.
	 * This method checks if the input field is empty and performs the specified
	 * syntax checks if not.
	 */
	delayedKeyUp: function() {
		var target = $(this.keyUpEvent.target);
		var oldValue = target.getAttribute("smwOldValue");
		if (oldValue && oldValue == target.value) {
			// content if input field did not change => return
			return;
		}
		target.setAttribute("smwOldValue", target.value);
		if (this.checkIfEmpty(target) == false) {
			this.handleCheck(target);
		}
		this.doFinalCheck(target);
	},
	
	/*
	 * Checks if the target input field is empty. If actions are tied to this 
	 * check, they are performed.
	 * Example for the HTML-attribute:
	 * smwCheckEmpty="empty ? (color:white) : (color:red)"
	 * 
	 * @param Object target
	 * 			The input element that is checked for emptyness
	 * @return
	 * 			<true> if the input field is empty,
	 * 			<false> otherwise
	 */
	checkIfEmpty: function(target) {
		var value = target.value;
		if (target.type == 'select-one') {
			value = target.options[target.selectedIndex].text;
		}
		var empty = value == "";
		var cie = target.getAttribute("smwCheckEmpty");
		if (!cie) {
			return empty;
		}
		var actions = this.parseConditional("empty", cie);
		if (actions) {
			this.performActions(empty ? actions[0] : actions[1], 
			                    target);
		}
		return empty;
	},
	
	/*
	 * This method handles type checks. Valid type identifiers are:
	 * - regex (valid)
	 * - integer (valid)
	 * - float (valid)
	 * - category (exists)
	 * - property (exists)
	 * (The names in brackets are the identifiers of the conditional that follows
	 * the type.)
	 * Examples:
	 *   smwCheckType="regex=^\\d+$: valid ? (color:white) : (color:red)"
	 *   smwCheckType="integer: valid ? (color:white) : (color:red)"
	 *   smwCheckType="category: exists ? (color:white) : (color:red)"
	 * 
	 * @param Object target
	 * 			The target element (an input field)
	 */
	handleCheck: function(target) {
		var check = target.getAttribute("smwCheckType");
		if (!check)	{
			return;
		}
		var type = check;
		var actions = "";
		var pos = check.indexOf(":");
		if (pos != -1) {
			type = check.substring(0, pos);
			actions = check.substring(pos+1);
		}
		type = type.toLowerCase();
		if (type.indexOf("regex") == 0) {
			// Handle type checks with regular expressions
			var regexStr = check.match(/regex\s*=\s*(.*?):\s*valid\s*\?/);
			if (regexStr) {
				var regex = new RegExp(regexStr[1]);
				pos = check.search(/:\s*valid\s*\?/);
				actions = check.substring(pos+1);
				this.checkWithRegEx(target.value, regex, actions, target);
			}
			
		} else {
			switch (type) {
				case 'integer':
					this.checkWithRegEx(target.value, /^\d+$/, actions, target);
					break;
				case 'float':
					this.checkWithRegEx(target.value, 
					                    /^[+-]?\d+(\.\d+)?([Ee][+-]?\d+)?$/,
					                    actions, target);
					break;
				case 'category':
				case 'property':
					this.handleSchemaCheck(type, check, target);
					break;
			}
		}
	},

	/**
	 * Handles a change on the DOM-element <target>.
	 * 
	 * @param Object target
	 * 			The target of the change event
	 * 
	 */
	handleChange: function(target) {
		var changeActions = target.getAttribute("smwChanged");
		if (!changeActions)	{
			return;
		}
		changeActions = changeActions.match(/\s*\((.*?)\)\s*$/);
		if (changeActions) {
			this.performActions(changeActions[1], target);
		}
		
	},
	
	/*
	 * Performs a final check on all input fields of the <div> that is the
	 * parent of the DOM-element <target>. 
	 * 
	 * @param Object target
	 * 			The HTML-element that was a target of an event e.g.
	 * 			an input field. The final check actions of its DIV-parent are 
	 * 			processed.
	 */
	doFinalCheck: function(target) {
		var parentDiv = target.up('div');
		if (!parentDiv) {
			return;
		}
		
		var allValidCndtl = parentDiv.getAttribute("smwAllValid");
		if (allValidCndtl) {
			var children = parentDiv.descendants();
			
			var allValid = true;
			for (var i = 0, len = children.length; i < len; ++i) {
				var elem = children[i];
				var valid = elem.getAttribute("smwValid");
				if (valid) {
					if (valid == "false") {
						allValid = false;
						break;
					} else if (valid != "true") {
						// call a function
						valid = eval(valid+'("'+elem.id+'")');
						if (!valid) {
							allValid = false;
							break;
						}
					}
				}
			}
			
			var c = this.parseConditional("allValid", allValidCndtl);
			this.performActions(allValid ? c[0] : c[1], parentDiv);
		}
	},

	/*
	 * Checks if <value> matches the regular expression <regex>. Corresponding
	 * actions which are specified in the <conditional> are performed on the 
	 * <target>.
	 * 
	 * @param string value
	 * 			This value is parsed with the regular expression
	 * @param RegExp regex
	 * 			The regular expression that is applied to the value. 
	 * @param string conditional
	 * 			This conditional contains lists of actions for the cases that
	 * 			the reg. exp. matches or not. The name of the conditional must
	 * 			be "valid". (e.g. valid ? (...) : (...) )
	 * @param Object target
	 * 			The target (an input field) for which the actions
	 * 			are performed.
	 */
	checkWithRegEx: function(value, regex, conditional, target) {
		var valid = value.match(regex);
		var c = this.parseConditional("valid", conditional);
		this.performActions(valid ? c[0] : c[1], target);
	},
	
	/*
	 * This method checks if an article for a category or a property exists.
	 * It shows the pending indicator and starts an ajax call that calls back in 
	 * function <ajaxCbSchemaCheck>.
	 * 
	 * @param string type
	 * 			Must be one of "category" or "property"
	 * @param string check
	 * 			The complete specification of the check that is performed i.e.
	 * 			the content of the attribute "smwCheckType".
	 * @param Object target
	 * 			The target i.e. an input field
	 */
	handleSchemaCheck: function(type, check, target) {
		var value = target.value;
		var checkName;
		switch (type) {
			case 'category':
				checkName = gLanguage.getMessage('CATEGORY')+value;
				break;
			case 'property':
				checkName = gLanguage.getMessage('PROPERTY')+value;
				break;
		}
		if (checkName) {
			this.showPendingIndicator(target);
			this.om.existsArticle(checkName, 
			                      this.ajaxCbSchemaCheck.bind(this), 
			                      value, [type, check], target.id);							
		}		
	},
	
	/*
	 * This method is a callback of the ajax-call that checks if an article exists.
	 * Depending on the existence of the article, actions specified in a conditional
	 * are performed.
	 * 
	 * @param string pageName
	 * 			Complete name of the page whose existence is checked.
	 * @param boolean exists
	 * 			<true> if the article exists
	 * 			<false> otherwise
	 * @param string title
	 * 			Content of the input field that was checked.
	 * @param array<string> param
	 * 			[0]: Type ("category" or "property")
	 * 			[1]: The complete specification of the check that was performed 
	 * 			     i.e. the content of the attribute "smwCheckType".
	 * @param string elementID
	 * 			DOM-ID of the input element for which the check was performed
	 */
	ajaxCbSchemaCheck: function(pageName, exists, title, param, elementID) {
		
		this.hidePendingIndicator();
		var check = param[1];
		var pos = check.indexOf(":");
		if (pos != -1) {
			var conditional = check.substring(pos+1);		
			var actions = this.parseConditional("exists", conditional);
			if (actions) {
				this.performActions(exists ? actions[0] : actions[1], $(elementID))
			}
		}
		this.doFinalCheck($(elementID));
	},
	
	/*
	 * Parses a conditional and returns the actions for the positive and negative 
	 * cases.
	 * A conditions has a name, followed by "?", a list of actions if the condition
	 * holds, a colon and a list of actions if the condition fails.
	 * Example:
	 *   exists ? (color:orange, show:linkID) : (color:white, hide:linkID)
	 * 
	 * @param string name
	 * 			Name of the conditions e.g. "exists" or "valid"
	 * @param string conditional
	 * 			This conditional is parsed and split in its positive and negative
	 * 			part.
	 * @return array<string>
	 * 			[0]: The positive part of the conditional
	 * 			[1]: The negative part of the conditional
	 * 			<null> is returned if the conditional has syntax errors
	 * 
	 */
	parseConditional: function(name, conditional) {
		var regex = new RegExp("\\s*"+name+"\\s*\\?\\s*\\(([^)]*)\\)\\s*:\\s*\\(([^)]*)\\)");
		var parts = conditional.match(regex);
		if (parts) {
			return [parts[1], parts[2]];
		}
		return null;
	},
	
	/*
	 * Performs all actions that are given in a comma separated list.
	 * 
	 * @param string actions
	 * 			The comma separated list of actions
	 * @param Onject element
	 * 			The input field for which the actions are performed
	 */
	performActions: function(actions, element) {
		
		// Actions are comma separated
		var allActions = actions.split(",");
		
		for (var i = 0, len = allActions.length; i < len; i++) {
			// actions and their parameters are separated by colons
			var actionAndParam = allActions[i].split(":");
			var act = "";
			var param = "";
			if (actionAndParam.length > 0) {
				act = actionAndParam[0].match(/^\s*(.*?)\s*$/);
				if (act) {
					act = act[1];
				}
			}			
			if (actionAndParam.length > 1) {
				param = actionAndParam[1].match(/^\s*(.*?)\s*$/);
				if (param) {
					param = param[1];
				}
			}			
			this.performSingleAction(act.toLowerCase(), param, element);
			
		}
		
	},
	
	/*
	 * Performs a single action.
	 * 
	 * @param string action
	 * 			Name of the action e.g. color, show, hide, call, showmessage
	 * @param string parameter
	 * 			Parameter for the action
	 * @param Object element
	 * 			The input field for which the action is performed
	 * 
	 */
	performSingleAction: function(action, parameter, element) {
		switch (action) {
			case 'color':
				element.setStyle({ background:parameter});
				break;
			case 'show':
				var tbc = smw_ctbHandler.findContainer(parameter);
				tbc.show(parameter, true);
				break;
			case 'hide':
				var tbc = smw_ctbHandler.findContainer(parameter);
				tbc.show(parameter, false);
				break;
			case 'call':
				eval(parameter+'("'+element.id+'")');
				break;
			case 'showmessage':
				var msgElem = $(element.id+'-msg');
				if (msgElem) {
					var msg = gLanguage.getMessage(parameter);
					var value = element.value;
					msg = msg.replace(/\$c/g,value);
					var tbc = smw_ctbHandler.findContainer(msgElem);
					tbc.replace(msgElem.id,
					            tbc.createText(msgElem.id, msg, '' , true));
					tbc.show(msgElem.id, true);
				}
				break;
			case 'hidemessage':
				var msgElem = $(element.id+'-msg');
				if (msgElem) {
					var tbc = smw_ctbHandler.findContainer(msgElem.id);
					tbc.show(msgElem.id, false);
				}
				break;
			case 'valid':
				element.setAttribute("smwValid", parameter);
				break;
			case 'attribute':
				var attrValue = parameter.split("=");
				if (attrValue && attrValue.length == 2) {
					element.setAttribute(attrValue[0], attrValue[1]);
				}
				break;
		}
		
	},
	
	/*
	 * Shows the pending indicator on the element with the DOM-ID <onElement>
	 * 
	 * @param string onElement
	 * 			DOM-ID if the element over which the indicator appears
	 */
	showPendingIndicator: function(onElement) {
		this.hidePendingIndicator();
		this.pendingIndicator = new OBPendingIndicator($(onElement));
		this.pendingIndicator.show();
	},

	/*
	 * Hides the pending indicator.
	 */
	hidePendingIndicator: function() {
		if (this.pendingIndicator != null) {
			this.pendingIndicator.hide();
			this.pendingIndicator = null;
		}
}
	
});

/*
 * The singleton instance of the semantic toolbar event action handler.
 */
var gSTBEventActions = new STBEventActions();



// SMW_Container.js
// under GPL-License
/**
 *  framework for menu container handling of STB++
 */

var ContainerToolBar = Class.create();
ContainerToolBar.prototype = {

/**
 * Constructor
 * 
 *  * @param string id
 * 		name of the menucontainerframework (e.g. 'rel' for Relation)
 *  * @param integer tabindex
 * 		first tabindex to start with
 *  * @param object frameworkcontainer
 * 		frameworkcontainer which the items will be added to 
 */
initialize: function(id,tabindex, frameworkcontainer) {
	//tabindex to start with when creating elements
	this.id = id;
	this.startindex = tabindex;
	this.lastindex = tabindex;  
	//header / containerlist / footer
	this.cointainerlist = new Array();
	//container in the framework, where to add the menu
	this.frameworkcontainer = frameworkcontainer;
	//TODO: FIX: new throws error if nothing is present  
	this.sandglass = new OBPendingIndicator();
	this.eventManager = new EventManager();
	//Register this object add the ctbHandler
	if(smw_ctbHandler) {
		smw_ctbHandler.addContainer(this.id,this);
	}
	
},


//Sand glass eventuell in die generelle bla auslagern
/**
 * @public
 * 
 * Shows the sand glass at the given element
 * 
 * @param object element
 * 		the element the sand glass will be shown at
 */
showSandglass: function(element){
	this.sandglass.hide();
	this.sandglass.show(element);
},

/**
 * @public
 * 
 * Hide the sand glass 
 * 
 */
hideSandglass: function(){
		this.sandglass.hide();
},

/**
 * @public
 * 
 * Creates the header and footer for this framework and adds it to the framework container
 * 
 * @param string attributes
 * 		Attributes for the new <div>-element
 */
createContainerBody: function(attributes){
	//defines header and footer
	var header = '<div id="' + this.id + '-box" '+attributes+'>';
	var footer = '</div>';
	//Adds the body to the stbframework
	this.frameworkcontainer.setContent(header + footer);
	this.frameworkcontainer.contentChanged();
	//for testing:
	//setTimeout(this.foo.bind(this),3000);
},



/**
 * @public
 * 
 * Creates an Input Element
 * 
 * @param string id
 * 		id of the element
 * @param string description
 * 		name of the element which will be shown 
 * @para string initialContent
 * 		The initial content of the input field.
 * @param string deleteCallback
 * 		A function. If not empty, a delete button will be added and the function
 * 		will be called if the button is pressed.
 * @param string attributes
 *		attributes which will be passed to the specific element
 * @param boolean visibility
 * 		if false the element will be collapsed initially
 */
 
createInput: function(id, description, initialContent, deleteCallback, attributes ,visibility){
	
	var containercontent = '<table class="stb-table stb-input-table ' + this.id + '-table ' + this.id + '-input-table"' +
			(visibility ? '' : 'style="display:none;"')  + 'id="' + this.id + '-table-' + id +'">' +
 			'<tr>' +
 				//Name of Inputfield
 				'<td class="stb-input-col1 ' + this.id + '-input-col1">' + description + '</td>' +
 				//Inputfield 
 				'<td class="stb-input-col2 ' + this.id + '-input-col2">';

 	if (deleteCallback){
		//if deletable change classes and add button			
		containercontent += 
			'<input class="wickEnabled stb-delinput ' + this.id + '-delinput" ' +
            'id="'+ id + '" ' +
            attributes + 
            ' type="text" ' +
            ' alignfloater="right" ' +
            'value="' + initialContent + '" '+
            'tabindex="'+ this.lastindex++ +'" />'+
            '</td>'+ 
            '<td class="stb-input-col3 ' + this.id + '-input-col3">' +
			'<a href="javascript:' + deleteCallback + '">' +
			'<img src="' + 
			wgScriptPath + 
			'/extensions/SMWHalo/skins/redcross.gif"/>';				 	
	} else {
		containercontent += 
			'<input class="wickEnabled stb-input ' + this.id + '-input" ' +
			'id="'+ id + '" '+
			attributes + 
			' type="text" ' +
            ' alignfloater="right" ' +
            'value="' + initialContent + '" '+
			'tabindex="'+ this.lastindex++ +'" />';
	}
	containercontent += '</td>' +
 			'</tr>' +
 			'</table>';
			
	return containercontent;
},


/**
 * @public
 * 
 * Creates an dropdown element
 * 
 * @param string id
 * 		id of the element
 * @param string description
 * 		name of the element which will be shown
 * @param array<string> options
 * 		array of strings representing the options of the dropdown box
 * @param string deleteCallback
 * 		A function. If not empty, a delete button will be added and the function
 * 		will be called if the button is pressed.
 * @param integer selecteditem
 * 		gives the number if the item in the options array which will be preselected 
 * @param string attributes
 *		attributes which will be passed to the specific element
 * @param boolean visibility
 * 		if false the element will be collapsed initially
 */
createDropDown: function(id, description, options, deleteCallback, selecteditem, attributes, visibility){
	
	//Select header
	var containercontent = '<table class="stb-table stb-select-table ' + this.id + '-table ' + this.id + '-select-table"' +
			(visibility ? '' : 'style="display:none;"')  + 'id="' + this.id + '-table-' + id +'">' +
 			'<tr>' +
 				//Name of Selectbox
 				'<td class="stb-select-col1 ' + this.id + '-select-col1">' +
				description +
				'</td>' +
 				//Selectbox
 				'<td class="stb-select-col2 ' + this.id + '-select-col2">';
	//if deletable change classes
	if(deleteCallback){
		containercontent += '<select class="stb-delselect ' + this.id + '-delselect" id="' + id + '"  ' + attributes + ' tabindex="'+ this.lastindex++ +'">';
		 	
	} else {
 		containercontent += '<select class="stb-select ' + this.id + '-select" id="' + id + '"  ' + attributes + ' tabindex="'+ this.lastindex++ +'">';
 	}
	//Generate Options from the aray				
	for( var i = 0; i < options.length; i++ ){
		if(i!=selecteditem){
			//Normal option
			containercontent += '<option>' + options[i] + '</option>'
		} else {
			//Preselected Option
			containercontent += '<option selected="selected">' + options[i] + '</option>'
		}
	}
	containercontent += '</select>';
	//if deletable add button
	if(deleteCallback){
		containercontent += '</td>';
		containercontent += '<td class="stb-select-col3 ' + this.id + '-select-col3">';;
		containercontent += '<a href="javascript:' + deleteCallback + '"><img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/redcross.gif"/>';				 	
	}
	//Select footer
	containercontent += '</td>' +
 			'</tr>' +
 			'</table>';
 			
 	return containercontent;

},

/**
 * @public
 * 
 * Creates an radiobutton element
 * 
 * @param string id
 * 		id of the element
 * @param string description
 * 		name of the element which will be shown
 * @param array[] options
 * 		array of strings representing the options of the radiobuttons
 * @param integer selecteditem
 * 		gives the number if the item in the options array which will be preselected 
 * @param string attributes
 *		attributes which will be passed to the specific element
 * @param boolean visibility
 * 		if false the element will be collapsed initially
 */
createRadio: function(id, description, options, selecteditem, attributes, visibility){
	
	//Radio header
	var containercontent = '<table class="stb-table stb-radio-table ' + this.id + '-table ' + this.id + '-radio-table"' +
			(visibility ? '' : 'style="display:none;"')  + 'id="' + this.id + '-table-' + id +'">' +
 			'<tr>' +
 				//Name of Radiobuttons
 				'<td class="stb-radio-col1 ' + this.id + '-input-radio1">' +
				description +
				'</td>' +
			'</tr><tr>'+
 				//Radiobuttons
 				'<td class="stb-radio-col2 ' + this.id + '-radio-col2">' +
					'<form class="stb-radio ' + this.id + '-radio" id="'+ id +'"  ' + attributes + ' tabindex="'+ this.lastindex++ +'">';
	
	//Generate Options from the aray				
	for( var i = 0; i < options.length; i++ ){
		if(i!=selecteditem){
			//Normal option
			containercontent += '<input type="radio" name="' + id +'" value="' + options[i] + '">' + options[i] + '<br>'
		} else {
			//Preselected Option
			containercontent += '<input type="radio" name="' + id + '" value="' + options[i] + '" checked="checked">' + options[i] + '</br>'
		}
	}
	
	//Radio footer
	containercontent += '</form>' +
 				'</td>' +
 			'</tr>' +
 			'</table>';
 			
 	return containercontent;

},


/**
 * @public
 * 
 * Creates an checkbox element
 * 
 * @param string id
 * 		id of the element
 * @param string description
 * 		name of the element which will be shown
 * @param array[] options
 * 		array of strings representing the options of the checkboxes
 * @param array selecteditems
 * 		array of integers representing the preselected items of the checkboxes
 * @param string attributes
 *		attributes which will be passed to the specific element
 * @param boolean visibility
 * 		if false the element will be collapsed initially
 */
createCheckBox: function(id, description, options, selecteditems, attributes, visibility){
	
	//checkbox header
	var containercontent = '<table class="stb-table stb-checkbox-table ' + this.id + '-table ' + this.id + '-checkbox-table"' +
			(visibility ? '' : 'style="display:none;"')  + 'id="' + this.id + '-table-' + id +'">' +
 			'<tr>' +
 				//Name of checkboxes
 				'<td class="stb-checkbox-col1 ' + this.id + '-checkbox-col1">' +
				description +
				'</td>' +
			'</tr><tr>'+
 				//checkboxes
 				'<td class="stb-checkbox-col2 ' + this.id + '-checkbox-col2">' +
					'<form class="stb-checkbox ' + this.id + '-checkbox" id="' + id +'"  ' + attributes + '>';
	
	//Generate Options from the aray				
	for( var i = 0; i < options.length; i++ ){
		if(!this.isInArray(i,selecteditems)){
			//Normal option
			containercontent += '<input type="checkbox" ' +
					                    'name="' + id + '"' + 
					                    ' tabindex="' + this.lastindex++ + '"' +
					                    ' value="' + options[i] + '">' + options[i] + '<br>'
		} else {
			//Preselected Option
			containercontent += '<input type="checkbox" ' +
					                   'name="' + id + '"' +
					                   ' tabindex="' + this.lastindex++ + '" ' +
					                   ' value="' + options[i] + '" checked="checked">' + options[i] + '<br>'
		}
	}
	
	//Radio footer
	containercontent += '</form>' +
 				'</td>' +
 			'</tr>' +
 			'</table>';
 			
 	return containercontent;

},

/**
 * @private 
 * 
 * Checks if the given item is in the given array
 * 
 * @param item
 * 		the item which will be searched in the array
 * @param array
 * 		array the item will be searched in
 */
isInArray: function(item ,array){
	for(var j = 0; j< array.length; j++ ){
		if(item==array[j]) return true;
	}
	return false;
},

/**
 * @public
 * 
 * Creates an text element
 * 
 * @param string id
 * 		id of the element
 * @param string description
 * 		name of the element which will be shown
 * @param string attributes
 *		attributes which will be passed to the specific element
 * @param boolean visibility
 * 		if false the element will be collapsed initially
 */
createText: function(id, description, attributes ,visibility){
		
		var imgtag = '';
		//will look for the proper imagetag within the description
		//i: info w: warning e: error
		var imgregex = /(\([iwe]\))(.*)/;
		var regexresult;
		if(regexresult = imgregex.exec(description)) {
			//select the icon which will be shown in front of the text
			switch (regexresult[1])
			{
				case (image = '(i)'):
		  			//Info Icon
					imgtag = '<img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/info.gif"/>';
		  			break
				case (image = '(w)'):
					//TODO: Error Icon should be replaced by a prober one
		  			imgtag = '<img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/warning.png"/>';
		  			break
				case (image = '(e)'):
					//TODO: Error Icon should be replaced by a prober one
		  			imgtag = '<img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/delete_icon.png"/>';
		  			break
				default:
					imgtag = '';
			}
			description = regexresult[2];
		}
		
		var containercontent = '<table class="stb-table stb-text-table ' + this.id + '-table ' + this.id + '-text-table"' +
			(visibility ? '' : 'style="display:none;"')  + 'id="' + this.id + '-table-' + id +'">' +
 			'<tr>' +
 				//Text
 				'<td class="stb-text-col1 ' + this.id + '-text-col1">' + imgtag +
				'&#32<span class="stb-text ' + this.id + '-radio" id="'+ id +'" id="'+ id + '" '+ attributes +'>' + description + '</span>' + 
				'</td>' +
 			'</tr>' +
 			'</table>';
			
	return containercontent;

},


/**
 * @public
 * 
 * Creates an link element
 * 
 * @param string id
 * 		id of the element
 * @param array[] functions
 *  	array of arrays describing the functions
 *  	elements are two dimensional arrays 
 * 		array[0] function which will be called
 * 		array[1] string representing the function in the htmlpage
 * 		array[2] string representing the id of the function in the htmlpage (optional)
 * 		array[3] string representing the alternativ text in the htmlpage if function is disabled (optional)
 * 		array[4] string representing the id of the alternative text in the htmlpage (optional)
 * 		Example: [['alert(\'f1\')','function1'],['alert(\'f2\')','function2'],['alert(\'f3\')','function3']
 * @param string attributes
 * 		not used yet
 *		attributes which will be passed to the specific element
 * @param boolean visibility
 * 		if false the element will be collapsed initially
 */
createLink: function(id, functions, attributes ,visibility){
	
	//function list header
	var containercontent = '<table class="stb-table stb-link-table ' + this.id + '-table ' + this.id + '-link-table"' +
			(visibility ? '' : 'style="display:none;"')  + 'id="' + this.id + '-table-' + id +'">';
			
	//select layout or more precisely how much columns
	switch(functions.length){
		case 1: var tablelayout = 1; break;
		case 2: var tablelayout = 2; break;
		//looks better with 2+2 than 3+1
		case 4: var tablelayout = 2; break;
		//all other cases choose 3 columns
		default: var tablelayout = 3;
	}
	
	var i = 0;
	//Generate columns
	//Schema for generating class names
	//1. class ln-dt-$numberofcolumns
	//2. class $containername-ln-dt-$numberofcolumns
	//3. class $elementid-ln-dt-$numberofcolumns
	for(var row = 0; row*tablelayout < functions.length; row++ ){
		containercontent += '<tr class=" ln-tr-' + tablelayout + ' '+ this.id +'-ln-tr-'+ tablelayout +' '+ id +'-ln-tr-'+ tablelayout +'">';
		//Generate rows
		//Schema for generating class names
		//1. class ln-dt-$numberofcolumns-$speficcolumnoftd
		//2. class $containername-ln-dt-$numberofcolumns-$speficcolumnoftd
		//3. class $elementid-ln-dt-$numberofcolumns-$speficcolumnoftd  
		for(var column=0; column<tablelayout; column++){
			containercontent += '<td class=" ln-td-' + tablelayout + '-' + column +' '+ this.id +'-ln-td-'+ tablelayout + '-' + column +' '+ id +'-ln-td-'+ tablelayout + '-' + column +'">';
			//GenerateLink
			if(i<functions.length){
				switch (functions[i].length) {
					case 2 :
						//Function with displayed text
		  				containercontent += '<a tabindex="'+ this.lastindex+++'" + href="javascript:' + functions[i][0] + '">'+ functions[i][1]+'&#32</a>';
		  				break;
					case 3 :
						//Function with id and displayed text
					  	containercontent += '<a tabindex="'+ this.lastindex+++'" + id="' + functions[i][2] + '" href="javascript:' + functions[i][0] + '">'+ functions[i][1]+'&#32</a>';
		  				break;
					case 5 :
						//Function with id and displayed text
					  	containercontent += '<a tabindex="'+ this.lastindex+++'" + id="' + functions[i][2] + '" href="javascript:' + functions[i][0] + '">'+ functions[i][1]+'&#32</a>';
						//altnative text with id, which will be shown if functions is disabled 
						containercontent += '<span id="' + functions[i][4] + '" style="display: none;">' + functions[i][3] + '</span>'
		  				break;		
					default:
		  				//do nothing
					}
				//select next function by increasing index 
				i++;				
			}
			containercontent += '</td>';
		}
		containercontent += '</tr>';
	}
	
	//deprecated functions
	//for(var i = 0; i< functions.length; i++ ){	
	//}
				
 	//function list footer
 	containercontent += '</table>';
			
	return containercontent;
	
},

/**
 * Changes the ID of the DOM-element <obj> to <newID>. The IDs of <obj>'s parents
 * are updated accordingly to maintain consistent names.
 * 
 * @param Object obj
 * 		The object that gets a new ID
 * @param string newID
 * 		The new ID of the object
 */
changeID: function(obj, newID) {
	
	var oldID = obj.id;
	var table = $(this.id + '-table-' + oldID);
	if (table) {
		table.id = this.id + '-table-' + newID;
	}
	obj.id = newID;
},

/**
 * @public
 * 		removes an element from the dom-tree
 * @param object element
 * 		element to remove
 */
remove: function(element){
	if(element instanceof Array){
		//Add elements
		for(var i = 0; i< element.length; i++ ){
			$(this.id+'-table-'+element[i]).remove();
			this.eventManager.deregisterEventsFromItem(element[i]);
		}
	} else {
		$(this.id+'-table-'+element).remove();
		this.eventManager.deregisterEventsFromItem(element);
	}	
	this.rebuildTabindex($(this.id + '-box'));
	autoCompleter.deregisterAllInputs();		
	autoCompleter.registerAllInputs();	
},

/**
 * @public Checks all child nodes for the attribute tabindex and rebuilt the correct order
 * 
 * @param object rootnode 
 * 		element which child elements will be updated
 */
rebuildTabindex: function(rootnode){
		//Check
		if(rootnode == null) return;
		//reset last index
		this.lastindex = this.startindex;
		//Get childs
		var elements = rootnode.descendants();
		//update each child
		elements.each(this.updateTabindex.bind(this));
		
},


/**
 * @private Checks element for the attribute tabindex and sets it to a correct value
 * 
 * @param object element
 * 		the element which will be updated 
 */
updateTabindex: function(element){
	//Check if tabindex is set, if yes update it
	if(element.readAttribute('tabindex')!= null && element.readAttribute('tabindex')!= 0){
		element.writeAttribute('tabindex',this.lastindex++);
	}	
},



/**
 * @public appends container to the menu
 * 
 * @param string content
 * 		array of strings
 * 		the html code of the element(s) which will be added
 */
append: function(content){
	if(content instanceof Array){
		//Add elements
		for(var i = 0; i< content.length; i++ ){
			new Insertion.Bottom($(this.id + '-box'), content[i]);
		}
	} else {
		new Insertion.Bottom($(this.id + '-box'), content);
	}
},

/**
 * @public insert new element to the menu after the given element
 * 
 * @param string id
 * 		the name of the element, after which the new one will be added
 * @param string content
 * 		the html code which will be added
 */
insert: function(id, content){
	if(content instanceof Array){
		//Add elements
		for(var i = 0; i< content.length; i++ ){
			new Insertion.After($(this.id + '-table-' + id), content[i]);
		}
	} else {
		new Insertion.After($(this.id + '-table-' + id), content);
	}
	
},
/**
 * @public replace an element with a new one
 * 
 * @param string id
 * 		the name of the element which will be replaced
 * @param string content
 * 		the html code which will be added afterwards
 */
replace: function(id, content){
    $(this.id + '-table-' + id).replace(content);
},
/**
 * @public shows or hide an element
 * 
 * @param string id
 * 		the name of the element, which will be shown or hidden
 * @param boolean visibility
 * 		true for show, false for hide 
 */
show: function(id, visibility){
	var obj = $(this.id + '-table-' + id);
	if (!obj) {
		obj = $(id);
	}
	if (obj) {
		if (visibility) {
			obj.show();
		} else {
			obj.hide();
		}
	}
	 
},

/**
 * This function must be called after the last element has been added or after
 * the container has been modified.
 */
finishCreation: function() {
	this.eventManager.deregisterAllEvents();
	var desc = $(this.id+'-box').descendants();
	for (var i = 0, len = desc.length; i < len; i++) {
		var elem = desc[i];
		if (elem.type == 'text') {
			this.eventManager.registerEvent(elem,'blur',gSTBEventActions.onBlur.bindAsEventListener(gSTBEventActions));
			this.eventManager.registerEvent(elem,'keyup',gSTBEventActions.onKeyUp.bindAsEventListener(gSTBEventActions));
		} else if (elem.type == 'radio') {
			this.eventManager.registerEvent(elem,'click',gSTBEventActions.onClick.bindAsEventListener(gSTBEventActions));
			this.eventManager.registerEvent(elem,'keyup',gSTBEventActions.onKeyUp.bindAsEventListener(gSTBEventActions));
		} else if (elem.type == 'select-one'){
			this.eventManager.registerEvent(elem,'change',gSTBEventActions.onChange.bindAsEventListener(gSTBEventActions));
			this.eventManager.registerEvent(elem,'keyup',gSTBEventActions.onKeyUp.bindAsEventListener(gSTBEventActions));
		} else if (elem.type == 'checkbox'){
			this.eventManager.registerEvent(elem,'keyup',gSTBEventActions.onKeyUp.bindAsEventListener(gSTBEventActions));
		}
	}
	// install the standard event handlers on all input fields
	autoCompleter.deregisterAllInputs();		
	autoCompleter.registerAllInputs();
	this.frameworkcontainer.contentChanged();		
	this.rebuildTabindex($(this.id + '-box'));
},

/**
 * Deregisters all events and the autocompleter from all input fields.
 */
release: function() {
	this.eventManager.deregisterAllEvents();
	autoCompleter.deregisterAllInputs();		
	autoCompleter.registerAllInputs();		
},


/**
 * @public this is just a test function adding some sample boxes
 * 
 * @param none
 */
foo: function(){
	this.createContainerBody('');
	this.showSandglass($(this.id + '-box'));
	this.append(this.createInput(700,'Test','', 'alert(\'loeschmich\')','',true));
	this.append(this.createText(701,'Test','',true));
	this.append(this.createDropDown(702,'Test',['Opt1','Opt2','Opt3'],'alert(\'loeschmich\')',2,'',true));
	this.insert('702',this.createRadio(703,'Test',['val1','val2','val3'],2,'',true));
	this.append(this.createCheckBox(704,'Test',['val1','val2','val3','val4'],[1,3],'',true));
	this.append(this.createLink(705,[['smwhgLogger.log(\'Testlog\',\'error\',\'log\');','Log']],'',true));
	this.append(this.createLink(706,[['alert(\'f1\')','function1'],['alert(\'f2\')','function2','fid2']],'',true));
	this.append(this.createLink(707,[['alert(\'f1\')','function1'],['alert(\'f2\')','function2','fid2'],['alert(\'f3\')','function3','fid3','alt-f3','faltid3']],'',true));
	this.append(this.createLink(708,[['alert(\'f1\')','function1'],['alert(\'f2\')','function2','fid2'],['alert(\'f3\')','function3','fid3','alt-f3','faltid3'],['alert(\'f4\')','function4']],'',true));
	this.append(this.createLink(709,[['alert(\'f1\')','function1'],['alert(\'f2\')','function2','fid2'],['alert(\'f3\')','function3','fid3','alt-f3','faltid3'],['alert(\'f4\')','function5'],['alert(\'f5\')','function5']],'',true));
	this.rebuildTabindex($(this.id + '-box'));
	//this.remove(['703','704']);
	this.hideSandglass();
	//$('faltid3').show();
	//$('faltid3').hide();
	this.showSandglass($(this.id + '-box'));
	ctbHandler = new CTBHandler();
	ctbHandler.addContainer('category',this);
	var obj = smw_ctbHandler.findContainer('703');
	obj.replace('701',obj.createText(701,'(e) Testreplace','',true))
	this.hideSandglass();

}


};// End of Class ContainerToolBar

/**
 *  Handler which allows to register Containerobjects and regain them by an elementid
 */
var CTBHandler = Class.create();
CTBHandler.prototype = {
	
/**
 * Constructor
 */
initialize: function() {
	this.containerlist = new Array();
},

/**
 * @public registers an given containerobj with the containerid as index 
 * 
 * @param String containerid
 * 	ContainerId
 * @param String containerobj
 * 	ContainerObject
 */
addContainer: function(containerid,containerobj){
	var pos = this.posInArray(containerid);
	
	if(pos<0){
		this.containerlist.push([containerid,containerobj])
	} else {
		this.containerlist[pos] = [containerid,containerobj];	
	}
},

/**
 * @private 
 * 
 * Checks if the given containerid is in the given array
 * 
 * @param containerid
 * 		the containerid which will be searched in the array
 */
posInArray: function(containerid){
	for(var j = 0; j< this.containerlist.length; j++ ){
		if(containerid==this.containerlist[j][0]) return j;
	}
	return -1;
},

/**
 * @public 
 * 
 * @param 
 * 	
 * 	
 */
findContainer: function(elementid){
	//Get list with all ancestors
	var ancestorlist = $(elementid).ancestors();
	//Look for an ancestor with a if that probably represents the containerbody-div
	for(var j = 0; j< ancestorlist.length; j++ ){
		//Read id
		var elementid = ancestorlist[j].readAttribute('id');
		//RegEx to isolate the containerid
		var regexsearch = /(.*)-box/g
		var regexresult;
		if(regexresult = regexsearch.exec(elementid)) {
			//Look if the containerid is registered and return the object  
		 	var pos = this.posInArray(regexresult[1]);
			if(pos>=0) return this.containerlist[pos][1];
		}
	}
	//Nothing found :(
	return false;
}

} //End of Class CTBHandler

//Global ctbHandler where all container framework objects register themself
var smw_ctbHandler = new CTBHandler();



//Test in CatContainer

/*
setTimeout(function() { 
	//categorycontainer = new divContainer(CATEGORYCONTAINER);
	var conToolbar = new ContainerToolBar('category',900,catToolBar.categorycontainer);
	Event.observe(window, 'load', conToolbar.createContainerBody.bindAsEventListener(conToolbar));
	setTimeout(conToolbar.foo.bind(conToolbar),1000);
},3000);
*/

// SMW_Category.js
// under GPL-License
var SMW_CAT_CHECK_CATEGORY = 
	'smwCheckType="category: exists ' +
		'? (color: lightgreen, hideMessage, valid:true) ' +
	 	': (color: orange, showMessage:CATEGORY_DOES_NOT_EXIST, valid:true)" ';

var SMW_CAT_CHECK_CATEGORY_IIE = // Invalid if exists
	'smwCheckType="category:exists ' +
		'? (color: red, showMessage:CATEGORY_ALREADY_EXISTS, valid:false) ' +
	 	': (color: lightgreen, hideMessage, valid:true)" ';

var SMW_CAT_CHECK_EMPTY = 
	'smwCheckEmpty="empty' +
		'? (color:red, showMessage:MUST_NOT_BE_EMPTY, valid:false) ' +
		': (color:white, hideMessage)"';

var SMW_CAT_ALL_VALID =	
	'smwAllValid="allValid ' +
 		'? (show:cat-confirm, hide:cat-invalid) ' +
 		': (show:cat-invalid, hide:cat-confirm)"';

var SMW_CAT_HINT_CATEGORY =
	'typeHint = "14" ';

var SMW_CAT_SUB_SUPER_CHECK_CATEGORY = 
	'smwCheckType="category: exists ' +
		'? (color: lightgreen, hideMessage, valid:true, attribute:catExists=true) ' +
	 	': (color: orange, hideMessage, valid:true, attribute:catExists=false)" ';

var SMW_CAT_SUB_SUPER_ALL_VALID =	
	'smwAllValid="allValid ' +
 		'? (call:catToolBar.createSubSuperLinks) ' +
 		': (call:catToolBar.createSubSuperLinks)"';
 		

var CategoryToolBar = Class.create();

CategoryToolBar.prototype = {

initialize: function() {
    //Reference
    this.genTB = new GenericToolBar();
	this.toolbarContainer = null;
	this.showList = true;
	this.currentAction = "";

},

showToolbar: function(request){
	this.categorycontainer.setHeadline(gLanguage.getMessage('CATEGORIES'));
	this.wtp = new WikiTextParser();
	this.om = new OntologyModifier();
	this.fillList(true);
},

callme: function(event){
	if(wgAction == "edit" && stb_control.isToolbarAvailable()){
		this.categorycontainer = stb_control.createDivContainer(CATEGORYCONTAINER,0);
		this.showToolbar();
	}
},

fillList: function(forceShowList) {
	if (forceShowList == true) {
		this.showList = true;
	}
	if (!this.showList) {
		return;
	}
	this.wtp.initialize();
	this.categorycontainer.setContent(this.genTB.createList(this.wtp.getCategories(),"category"));
	this.categorycontainer.contentChanged();
},

cancel: function(){
	/*STARTLOG*/
    smwhgLogger.log("","STB-Categories",this.currentAction+"_canceled");
	/*ENDLOG*/
	this.currentAction = "";
	this.toolbarContainer.hideSandglass();
	this.toolbarContainer.release();
	this.toolbarContainer = null;
	this.fillList(true);
},

/**
 * Creates a new toolbar for the category container with the standard menu.
 * Further elements can be added to the toolbar. Call <finishCreation> after the
 * last element has been added.
 * 
 * @param string attributes
 * 		Attributes for the new container
 * @return 
 * 		A new toolbar container
 */
createToolbar: function(attributes) {
	if (this.toolbarContainer) {
		this.toolbarContainer.release();
	}
	
	this.toolbarContainer = new ContainerToolBar('category-content',600,this.categorycontainer);
	var tb = this.toolbarContainer;
	tb.createContainerBody(attributes);
	return tb;
},


addItem: function() {
	/*STARTLOG*/
    smwhgLogger.log($("cat-name").value,"STB-Categories","annotate_added");
	/*ENDLOG*/
	this.wtp.initialize();
	var name = $("cat-name").value;
	this.wtp.addCategory(name, true);
	this.fillList(true);
},

newItem: function() {
	var html;
	
	this.showList = false;
	this.currentAction = "annotate";
	
    this.wtp.initialize();
	var selection = this.wtp.getSelection();
	
	/*STARTLOG*/
    smwhgLogger.log(selection,"STB-Categories","annotate_clicked");
	/*ENDLOG*/

	var tb = this.createToolbar(SMW_CAT_ALL_VALID);	
	tb.append(tb.createText('cat-help-msg', 
	                        gLanguage.getMessage('ANNOTATE_CATEGORY'),
	                        '' , true));
	tb.append(tb.createInput('cat-name', 
							 gLanguage.getMessage('CATEGORY'), selection, '',
	                         SMW_CAT_CHECK_CATEGORY +
	                         SMW_CAT_CHECK_EMPTY +
	                         SMW_CAT_HINT_CATEGORY,
	                         true));
	tb.append(tb.createText('cat-name-msg', 
							gLanguage.getMessage('ENTER_NAME'), '' , true));
	var links = [['catToolBar.addItem()',gLanguage.getMessage('ADD'), 'cat-confirm',
	                                     gLanguage.getMessage('INVALID_VALUES'), 'cat-invalid'],
				 ['catToolBar.cancel()', gLanguage.getMessage('CANCEL')]
				];
	tb.append(tb.createLink('cat-links', links, '', true));
				
	tb.finishCreation();
	gSTBEventActions.initialCheck($("category-content-box"));
	//Sets Focus on first Element
	setTimeout("$('cat-name').focus();",50);
},


CreateSubSup: function() {
    var html;

	this.currentAction = "sub/super-category";
	this.showList = false;
	
    this.wtp.initialize();
	var selection = this.wtp.getSelection();
	
	/*STARTLOG*/
    smwhgLogger.log(selection,"STB-Categories","sub/super-category_clicked");
	/*ENDLOG*/

	var tb = this.createToolbar(SMW_CAT_SUB_SUPER_ALL_VALID);	
	tb.append(tb.createText('cat-help-msg', gLanguage.getMessage('DEFINE_SUB_SUPER_CAT'), '' , true));
	tb.append(tb.createInput('cat-subsuper', gLanguage.getMessage('CATEGORY'),
	                         selection, '',
	                         SMW_CAT_SUB_SUPER_CHECK_CATEGORY +
	                         SMW_CAT_CHECK_EMPTY +
	                         SMW_CAT_HINT_CATEGORY,
	                         true));
	tb.append(tb.createText('cat-subsuper-msg', gLanguage.getMessage('ENTER_NAME'), '' , true));
	
	tb.append(tb.createLink('cat-make-sub-link', 
	                        [['catToolBar.createSubItem()', gLanguage.getMessage('CREATE_SUB'), 'cat-make-sub']], 
	                        '', false));
	tb.append(tb.createLink('cat-make-super-link', 
	                        [['catToolBar.createSuperItem()', gLanguage.getMessage('CREATE_SUPER'), 'cat-make-super']],
	                        '', false));
	
	var links = [['catToolBar.cancel()', gLanguage.getMessage('CANCEL')]];
	tb.append(tb.createLink('cat-links', links, '', true));
				
	tb.finishCreation();
	gSTBEventActions.initialCheck($("category-content-box"));

	//Sets Focus on first Element
	setTimeout("$('cat-subsuper').focus();",50);
},

createSubSuperLinks: function(elementID) {
	
	var exists = $("cat-subsuper").getAttribute("catExists");
	exists = (exists && exists == 'true');
	var tb = this.toolbarContainer;
	
	var title = $("cat-subsuper").value;
	
	if (title == '') {
		$('cat-make-sub').hide();
		$('cat-make-super').hide();
		return;
	}
	
	var superContent;
	var sub;
	if (!exists) {
		sub = gLanguage.getMessage('CREATE_SUB_CATEGORY');
		superContent = gLanguage.getMessage('CREATE_SUPER_CATEGORY');
	} else {
		sub = gLanguage.getMessage('MAKE_SUB_CATEGORY');
		superContent = gLanguage.getMessage('MAKE_SUPER_CATEGORY');
	}
	sub = sub.replace(/\$-title/g, title);
	superContent = superContent.replace(/\$-title/g, title);			                          
	if($('cat-make-sub').innerHTML != sub){
		var lnk = tb.createLink('cat-make-sub-link', 
								[['catToolBar.createSuperItem('+(exists?'false':'true')+')', sub, 'cat-make-sub']],
								'', true);
		tb.replace('cat-make-sub-link', lnk);
		lnk = tb.createLink('cat-make-super-link', 
							[['catToolBar.createSubItem()', superContent, 'cat-make-super']],
							'', true);
		tb.replace('cat-make-super-link', lnk);
	}
},

createSubItem: function() {
	var name = $("cat-subsuper").value;
	/*STARTLOG*/
    smwhgLogger.log(wgTitle+":"+name,"STB-Categories","sub-category_created");
	/*ENDLOG*/
	//Check if Inputbox is empty
	if(name=="" || name == null ){
		alert(gLanguage.getMessage('INPUT_BOX_EMPTY'));
		return;
	}
 	this.om.createSubCategory(name, "");
 	this.fillList(true);
},

createSuperItem: function(openTargetArticle) {
	if (openTargetArticle == undefined) {
		openTargetArticle = true;
	}
	var name = $("cat-subsuper").value;
	/*STARTLOG*/
    smwhgLogger.log(name+":"+wgTitle,"STB-Categories","super-category_created");
	/*ENDLOG*/
	//Check if Inputbox is empty
	if(name=="" || name == null ){
		alert(gLanguage.getMessage('INPUT_BOX_EMPTY'));
		return;
	}
 	this.om.createSuperCategory(name, "", openTargetArticle);
 	this.fillList(true);
},


changeItem: function(selindex) {
	this.wtp.initialize();
	//Get new values
	var name = $("cat-name").value;
	//Get category
	var annotatedElements = this.wtp.getCategories();
	//change category
	if(   (selindex!=null) 
	   && ( selindex >=0) 
	   && (selindex <= annotatedElements.length)  ){
		/*STARTLOG*/
		var oldName = annotatedElements[selindex].getName();
	    smwhgLogger.log(oldName+"->"+name,"STB-Categories","edit_category_change");
		/*ENDLOG*/
		annotatedElements[selindex].changeCategory(name);
	}
	
	//show list
	this.fillList(true);
},

deleteItem: function(selindex) {
	this.wtp.initialize();
	//Get relations
	var annotatedElements = this.wtp.getCategories();

	//delete category
	if (   (selindex!=null)
	    && (selindex >=0)
	    && (selindex <= annotatedElements.length)  ){
		var anno = annotatedElements[selindex];
		/*STARTLOG*/
	    smwhgLogger.log(anno.getName(),"STB-Categories","edit_category_delete");
		/*ENDLOG*/
		anno.remove("");
	}
	//show list
	this.fillList(true);
},


newCategory: function() {

    var html;
    
	this.currentAction = "create";
	this.showList = false;
 
    this.wtp.initialize();
	var selection = this.wtp.getSelection();
   
	/*STARTLOG*/
    smwhgLogger.log(selection,"STB-Categories","create_clicked");
	/*ENDLOG*/
    
	var tb = this.createToolbar(SMW_CAT_ALL_VALID);	
	tb.append(tb.createText('cat-help-msg', gLanguage.getMessage('CREATE_NEW_CATEGORY'), '' , true));
	tb.append(tb.createInput('cat-name', gLanguage.getMessage('CATEGORY'), 
							 selection, '',
	                         SMW_CAT_CHECK_CATEGORY_IIE+SMW_CAT_CHECK_EMPTY,
	                         true));
	tb.append(tb.createText('cat-name-msg', gLanguage.getMessage('ENTER_NAME'), '' , true));
		
	var links = [['catToolBar.createNewCategory()',gLanguage.getMessage('CREATE'), 'cat-confirm', 
	                                               gLanguage.getMessage('INVALID_NAME'), 'cat-invalid'],
				 ['catToolBar.cancel()', gLanguage.getMessage('CANCEL')]
				];
	tb.append(tb.createLink('cat-links', links, '', true));
				
	tb.finishCreation();
	gSTBEventActions.initialCheck($("category-content-box"));
	//Sets Focus on first Element
	setTimeout("$('cat-name').focus();",50);
},

createNewCategory: function() {
	var catName = $("cat-name").value;
	/*STARTLOG*/
    smwhgLogger.log(catName,"STB-Categories","create_added");
	/*ENDLOG*/
	// Create an ontology modifier instance
	this.om.createCategory(catName, "");

	//Adds annotation of the newly created Category to the actual editbox
	this.wtp.initialize();
	this.wtp.addCategory(catName, true);

	//show list
	this.fillList(true);

},

getselectedItem: function(selindex) {
	this.wtp.initialize();
	var annotatedElements = this.wtp.getCategories();
	if (   selindex == null
	    || selindex < 0
	    || selindex >= annotatedElements.length) {
		// Invalid index
		return;
	}

	this.currentAction = "edit_category";
	this.showList = false;

	/*STARTLOG*/
    smwhgLogger.log(annotatedElements[selindex].getName(),"STB-Categories","edit_category_clicked");
	/*ENDLOG*/
	
	var tb = this.createToolbar(SMW_CAT_ALL_VALID);	
	tb.append(tb.createText('cat-help-msg', gLanguage.getMessage('CHANGE_ANNO_OF_CAT'), '' , true));
	
	tb.append(tb.createInput('cat-name', gLanguage.getMessage('CATEGORY'), annotatedElements[selindex].getName(), '',
	                         SMW_CAT_CHECK_CATEGORY +
	                         SMW_CAT_CHECK_EMPTY +
	                         SMW_CAT_HINT_CATEGORY,
	                         true));
	tb.append(tb.createText('cat-name-msg', gLanguage.getMessage('ENTER_NAME'), '' , true));
		
	var links = [['catToolBar.changeItem(' + selindex +')', gLanguage.getMessage('CHANGE'), 'cat-confirm', 
	                                                        gLanguage.getMessage('INVALID_NAME'), 'cat-invalid'],
				 ['catToolBar.deleteItem(' + selindex +')', gLanguage.getMessage('DELETE')],
				 ['catToolBar.cancel()', gLanguage.getMessage('CANCEL')]
				];
	tb.append(tb.createLink('cat-links', links, '', true));
				
	tb.finishCreation();
	gSTBEventActions.initialCheck($("category-content-box"));
	annotatedElements[selindex].select();
	//Sets Focus on first Element
	setTimeout("$('cat-name').focus();",50);
}
};// End of Class

var catToolBar = new CategoryToolBar();
Event.observe(window, 'load', catToolBar.callme.bindAsEventListener(catToolBar));




// SMW_Relation.js
// under GPL-License
var RelationToolBar = Class.create();

var SMW_REL_CHECK_PROPERTY = 
	'smwCheckType="property: exists ' +
		'? (color: lightgreen, hideMessage, valid:true) ' +
	 	': (color: orange, showMessage:PROPERTY_DOES_NOT_EXIST, valid:true)" ';

var SMW_REL_CHECK_PROPERTY_UPDATE_SCHEMA = 
	'smwCheckType="property: exists ' +
		'? (color: lightgreen, hideMessage, valid:true, call:relToolBar.updateSchema) ' +
	 	': (color: orange, showMessage:PROPERTY_DOES_NOT_EXIST, valid:true)" ';

var SMW_REL_SUB_SUPER_CHECK_PROPERTY = 
	'smwCheckType="property: exists ' +
		'? (color: lightgreen, hideMessage, valid:true, attribute:propExists=true) ' +
	 	': (color: orange, hideMessage, valid:true, attribute:propExists=false)" ';

var SMW_REL_CHECK_PROPERTY_IIE = // Invalid if exists
	'smwCheckType="property: exists ' +
		'? (color: red, showMessage:PROPERTY_ALREADY_EXISTS, valid:false) ' +
	 	': (color: lightgreen, hideMessage, valid:true)" ';

var SMW_REL_CHECK_CATEGORY = 
	'smwCheckType="category: exists ' +
		'? (color: lightgreen, hideMessage, valid:true) ' +
	 	': (color: orange, showMessage:CATEGORY_DOES_NOT_EXIST, valid:true)" ';

var SMW_REL_CHECK_EMPTY = 
	'smwCheckEmpty="empty' +
		'? (color:red, showMessage:MUST_NOT_BE_EMPTY, valid:false) ' +
		': (color:white, hideMessage)"';

var SMW_REL_CHECK_EMPTY_NEV =   // NEV = Not Empty Valid i.e. valid if not empty
	'smwCheckEmpty="empty' +
		'? (color:red, showMessage:MUST_NOT_BE_EMPTY, valid:false) ' +
		': (color:white, hideMessage, valid:true)"';

var SMW_REL_CHECK_EMPTY_WIE =   // WIE = Warning if empty but still valid
	'smwCheckEmpty="empty' +
		'? (color:orange, showMessage:VALUE_IMPROVES_QUALITY) ' +
		': (color:white, hideMessage)"';

var SMW_REL_NO_EMPTY_SELECTION =
	'smwCheckEmpty="empty' +
	'? (color:red, showMessage:SELECTION_MUST_NOT_BE_EMPTY, valid:false) ' +
	': (color:white, hideMessage, valid:true)"';		

var SMW_REL_ALL_VALID =	
	'smwAllValid="allValid ' +
 		'? (show:rel-confirm, hide:rel-invalid) ' +
 		': (show:rel-invalid, hide:rel-confirm)"';

var SMW_REL_SUB_SUPER_ALL_VALID =	
	'smwAllValid="allValid ' +
 		'? (call:relToolBar.createSubSuperLinks) ' +
 		': (call:relToolBar.createSubSuperLinks)"';
 		
var SMW_REL_CHECK_PART_OF_RADIO =
	'smwValid="relToolBar.checkPartOfRadio"';

var SMW_REL_HINT_CATEGORY =
	'typeHint = "14" ';

var SMW_REL_HINT_PROPERTY =
	'typeHint="102" ';
	

RelationToolBar.prototype = {

initialize: function() {
	this.genTB = new GenericToolBar();
	this.toolbarContainer = null;
	this.showList = true;
	this.currentAction = "";
},

showToolbar: function(){
	this.relationcontainer.setHeadline(gLanguage.getMessage('PROPERTIES'));
	this.wtp = new WikiTextParser();
	this.om = new OntologyModifier();
	this.fillList(true);

},

callme: function(event){
	if(wgAction == "edit"
	    && stb_control.isToolbarAvailable()){
		this.relationcontainer = stb_control.createDivContainer(RELATIONCONTAINER, 0);
		this.showToolbar();		
	}
},

fillList: function(forceShowList) {

	if (forceShowList == true) {
		this.showList = true;
	}
	if (!this.showList) {
		return;
	}
	this.wtp.initialize();
	this.relationcontainer.setContent(this.genTB.createList(this.wtp.getRelations(),"relation"));
	this.relationcontainer.contentChanged();
},

cancel: function(){
	
	/*STARTLOG*/
    smwhgLogger.log("","STB-Properties",this.currentAction+"_canceled");
	/*ENDLOG*/
	this.currentAction = "";
	
	this.toolbarContainer.hideSandglass();
	this.toolbarContainer.release();
	this.toolbarContainer = null;
	this.fillList(true);
},

/**
 * Creates a new toolbar for the relation container with the standard menu.
 * Further elements can be added to the toolbar. Call <finishCreation> after the
 * last element has been added.
 * 
 * @param string attributes
 * 		Attributes for the new container
 * @return 
 * 		A new toolbar container
 */
createToolbar: function(attributes) {
	if (this.toolbarContainer) {
		this.toolbarContainer.release();
	}
	
	this.toolbarContainer = new ContainerToolBar('relation-content',700,this.relationcontainer);
	var tb = this.toolbarContainer;
	tb.createContainerBody(attributes);
	return tb;
},

addItem: function() {
	this.wtp.initialize();
	var name = $("rel-name").value;
	var value = this.getRelationValue();
	var text = $("rel-show").value;
	/*STARTLOG*/
    smwhgLogger.log(name+':'+value,"STB-Properties","annotate_added");
	/*ENDLOG*/
	//Check if Inputbox is empty
	if (name=="" || name == null ){
		alert(gLanguage.getMessage('INPUT_BOX_EMPTY'));
		return;
	}
	this.wtp.addRelation(name, value, text);
	this.fillList(true);
},

getRelationValue: function() {
	var i = 0;
	var value = "";
	while($("rel-value-"+i) != null) {
		value += $("rel-value-"+i).value + ";"
		i++;
	}
	value = value.substr(0, value.length-1); // remove last semicolon
	return value;
},

newItem: function() {
    var html;
    this.wtp.initialize();
	this.showList = false;
	this.currentAction = "annotate";

	var selection = this.wtp.getSelection();
	/*STARTLOG*/
    smwhgLogger.log(selection,"STB-Properties","annotate_clicked");
	/*ENDLOG*/
	
	var tb = this.createToolbar(SMW_REL_ALL_VALID);	
	tb.append(tb.createText('rel-help_msg', gLanguage.getMessage('ANNOTATE_PROPERTY'), '' , true));
	tb.append(tb.createInput('rel-name', gLanguage.getMessage('PROPERTY'), '', '',
	                         SMW_REL_CHECK_PROPERTY_UPDATE_SCHEMA +
	                         SMW_REL_CHECK_EMPTY +
	                         SMW_REL_HINT_PROPERTY,
	                         true));
	tb.append(tb.createText('rel-name-msg', gLanguage.getMessage('ENTER_NAME'), '' , true));
	tb.append(tb.createInput('rel-value-0', gLanguage.getMessage('PAGE'), selection, '', 
							 SMW_REL_CHECK_EMPTY_NEV,
	                         true));
	tb.append(tb.createText('rel-value-0-msg', gLanguage.getMessage('ANNO_PAGE_VALUE'), '' , true));
	tb.append(tb.createInput('rel-show', gLanguage.getMessage('SHOW'), '', '', '', true));
	var links = [['relToolBar.addItem()',gLanguage.getMessage('ADD'), 'rel-confirm', gLanguage.getMessage('INVALID_VALUES'), 'rel-invalid'],
				 ['relToolBar.cancel()', gLanguage.getMessage('CANCEL')]
				];
	tb.append(tb.createLink('rel-links', links, '', true));
				
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));
	
	//Sets Focus on first Element
	setTimeout("$('rel-name').focus();",50);
},

updateSchema: function(elementID) {
	relToolBar.toolbarContainer.showSandglass(elementID);
	sajax_do_call('smwfRelationSchemaData',
	              [$('rel-name').value],
	              relToolBar.updateNewItem.bind(relToolBar));
},

updateNewItem: function(request) {
	
	relToolBar.toolbarContainer.hideSandglass();
	if (request.status != 200) {
		// call for schema data failed, do nothing.
		return;
	}

	// defaults
	var arity = 2;
	var parameterNames = ["Page"];

	if (request.responseText != 'noSchemaData') {
		//TODO: activate annotate button
		var schemaData = GeneralXMLTools.createDocumentFromString(request.responseText);

		// read arity and parameter names
		arity = parseInt(schemaData.documentElement.getAttribute("arity"));
		parameterNames = [];
		for (var i = 0, n = schemaData.documentElement.childNodes.length; i < n; i++) {
			parameterNames.push(schemaData.documentElement.childNodes[i].getAttribute("name"));
		}
	}
	// build new INPUT tags
	var selection = this.wtp.getSelection();
	var tb = this.toolbarContainer;
	
	// remove old input fields	
	var i = 0;
	var removeElements = new Array();
	var found = true;
	var oldValues = [];
	while (found) {
		found = false;
		var elem = $('rel-value-'+i);
		if (elem) {
			oldValues.push($('rel-value-'+i).value);
			removeElements.push('rel-value-'+i);
			found = true;
		}
		elem = $('rel-value-'+i+'-msg');
		if (elem) {
			removeElements.push('rel-value-'+i+'-msg');
			found = true;
		}
		++i;
	}
	tb.remove(removeElements);
	
	// create new input fields
	for (var i = 0; i < arity-1; i++) {
		insertAfter = (i==0) 
			? ($('rel-replace-all') 
				? 'rel-replace-all'
				: 'rel-name-msg' )
			: 'rel-value-'+(i-1)+'-msg';
		var value = (i == 0)
			? ((oldValues.length > 0)
				? oldValues[0]
				: selection)
			: ((oldValues.length > i)
				? oldValues[i]
				: '');
		tb.insert(insertAfter,
				  tb.createInput('rel-value-'+ i, parameterNames[i], value, '', 
								 SMW_REL_CHECK_EMPTY_NEV,
		                         true));
		tb.insert('rel-value-'+ i,
				  tb.createText('rel-value-'+i+'-msg', gLanguage.getMessage('ANNO_PAGE_VALUE'), '' , true));
		selection = "";
	}
	
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));
},

CreateSubSup: function() {
    var html;

	this.showList = false;
	this.currentAction = "sub/super-category";

	this.wtp.initialize();
	var selection = this.wtp.getSelection();
	/*STARTLOG*/
    smwhgLogger.log(selection,"STB-Properties","sub/super-property_clicked");
	/*ENDLOG*/

	var tb = this.createToolbar(SMW_REL_SUB_SUPER_ALL_VALID);	
	tb.append(tb.createText('rel-help-msg', gLanguage.getMessage('DEFINE_SUB_SUPER_PROPERTY'), '' , true));
	tb.append(tb.createInput('rel-subsuper', gLanguage.getMessage('PROPERTY'), selection, '',
	                         SMW_REL_SUB_SUPER_CHECK_PROPERTY+SMW_REL_CHECK_EMPTY,
	                         true));
	tb.append(tb.createText('rel-subsuper-msg', gLanguage.getMessage('ENTER_NAME'), '' , true));
	
	tb.append(tb.createLink('rel-make-sub-link', 
	                        [['relToolBar.createSubItem()', gLanguage.getMessage('CREATE_SUB'), 'rel-make-sub']], 
	                        '', false));
	tb.append(tb.createLink('rel-make-super-link', 
	                        [['relToolBar.createSuperItem()', gLanguage.getMessage('CREATE_SUPER'), 'rel-make-super']],
	                        '', false));
	
	var links = [['relToolBar.cancel()', gLanguage.getMessage('CANCEL')]];
	tb.append(tb.createLink('rel-links', links, '', true));
				
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));
    
	//Sets Focus on first Element
	setTimeout("$('rel-subsuper').focus();",50);
},

createSubSuperLinks: function(elementID) {
	
	var exists = $("rel-subsuper").getAttribute("propExists");
	exists = (exists && exists == 'true');
	var tb = this.toolbarContainer;
	
	var title = $("rel-subsuper").value;
	
	if (title == '') {
		$('rel-make-sub').hide();
		$('rel-make-super').hide();
		return;
	}
	
	var superContent;
	var sub;
	if (!exists) {
		sub = gLanguage.getMessage('CREATE_SUB_PROPERTY');
		superContent = gLanguage.getMessage('CREATE_SUPER_PROPERTY');
	} else {
		sub = gLanguage.getMessage('MAKE_SUB_PROPERTY');
		superContent = gLanguage.getMessage('MAKE_SUPER_PROPERTY');
	}
	sub = sub.replace(/\$-title/g, title);
	superContent = superContent.replace(/\$-title/g, title);			                          
	if($('rel-make-sub').innerHTML != sub){
		var lnk = tb.createLink('rel-make-sub-link', 
								[['relToolBar.createSuperItem('+ (exists ? 'false' : 'true') + ')', sub, 'rel-make-sub']],
								'', true);
		tb.replace('rel-make-sub-link', lnk);
		lnk = tb.createLink('rel-make-super-link', 
							[['relToolBar.createSubItem()', superContent, 'rel-make-super']],
							'', true);
		tb.replace('rel-make-super-link', lnk);
	}
},
	
createSubItem: function(openTargetArticle) {
	
	if (openTargetArticle == undefined) {
		openTargetArticle = true;
	}
	var name = $("rel-subsuper").value;
	/*STARTLOG*/
    smwhgLogger.log(wgTitle+":"+name,"STB-Properties","sub-property_created");
	/*ENDLOG*/
	//Check if Inputbox is empty
	if(name=="" || name == null ){
		alert(gLanguage.getMessage('INPUT_BOX_EMPTY'));
		return;
	}
 	this.om.createSubProperty(name, "", openTargetArticle);
 	this.fillList(true);
},

createSuperItem: function(openTargetArticle) {
	if (openTargetArticle == undefined) {
		openTargetArticle = true;
	}
	var name = $("rel-subsuper").value;
	/*STARTLOG*/
    smwhgLogger.log(name+":"+wgTitle,"STB-Properties","super-property_created");
	/*ENDLOG*/
	//Check if Inputbox is empty
	if(name=="" || name == null ){
		alert(gLanguage.getMessage('INPUT_BOX_EMPTY'));
		return;
	}

 	this.om.createSuperProperty(name, "", openTargetArticle);
 	this.fillList(true);
},

newRelation: function() {
    var html;
    gDataTypes.refresh();
    
	this.showList = false;
	this.currentAction = "create";
	
    this.wtp.initialize();
	var selection = this.wtp.getSelection();
   
	/*STARTLOG*/
    smwhgLogger.log(selection,"STB-Properties","create_clicked");
	/*ENDLOG*/

	var domain = (wgNamespaceNumber == 14)
					? wgTitle  // current page is a category
					: "";
	var tb = this.createToolbar(SMW_REL_ALL_VALID);	
	tb.append(tb.createText('rel-help-msg', gLanguage.getMessage('CREATE_NEW_PROPERTY'), '' , true));
	tb.append(tb.createInput('rel-name', gLanguage.getMessage('PROPERTY'), selection, '',
	                         SMW_REL_CHECK_PROPERTY_IIE+SMW_REL_CHECK_EMPTY,
	                         true));
	tb.append(tb.createText('rel-name-msg', gLanguage.getMessage('ENTER_NAME'), '' , true));
	
	tb.append(tb.createInput('rel-domain', gLanguage.getMessage('DOMAIN'), '', '', 
						     SMW_REL_CHECK_CATEGORY + SMW_REL_CHECK_EMPTY_WIE,
	                         true));
	tb.append(tb.createText('rel-domain-msg', gLanguage.getMessage('ENTER_DOMAIN'), '' , true));
	
	tb.append(tb.createInput('rel-range-0', gLanguage.getMessage('RANGE'), '', 
							 "relToolBar.removeRangeOrType('rel-range-0')", 
						     SMW_REL_CHECK_CATEGORY + SMW_REL_CHECK_EMPTY_WIE,
	                         true));
	tb.append(tb.createText('rel-range-0-msg', gLanguage.getMessage('ENTER_RANGE'), '' , true));
	
	var links = [['relToolBar.createNewRelation()',gLanguage.getMessage('CREATE'), 'rel-confirm', gLanguage.getMessage('INVALID_VALUES'), 'rel-invalid'],
				 ['relToolBar.cancel()', gLanguage.getMessage('CANCEL')]
				];
	tb.append(tb.createLink('rel-links', links, '', true));
	
	links = [['relToolBar.addRangeInput()',gLanguage.getMessage('ADD_RANGE')],
			 ['relToolBar.addTypeInput()', gLanguage.getMessage('ADD_TYPE')]
			];
	tb.append(tb.createLink('rel-add-links', links, '', true));		
			
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));
	

	//Sets Focus on first Element
	setTimeout("$('rel-name').focus();",50);

},

addRangeInput:function() {
	var i = 0;
	while($('rel-range-'+i) != null) {
		i++;
	}
	var tb = this.toolbarContainer;
	var insertAfter = (i==0) ? 'rel-domain-msg' 
							 : $('rel-range-'+(i-1)+'-msg') 
							 	? 'rel-range-'+(i-1)+'-msg'
							 	: 'rel-range-'+(i-1);
	
	tb.insert(insertAfter,
			  tb.createInput('rel-range-'+i, gLanguage.getMessage('RANGE'), '', 
                             "relToolBar.removeRangeOrType('rel-range-"+i+"')",
						     SMW_REL_CHECK_CATEGORY + SMW_REL_CHECK_EMPTY_WIE,
	                         true));
	tb.insert('rel-range-'+i,
	          tb.createText('rel-range-'+i+'-msg', gLanguage.getMessage('ENTER_RANGE'), '' , true));
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));
},

addTypeInput:function() {
	var i = 0;
	while($('rel-range-'+i) != null) {
		i++;
	}
	var tb = this.toolbarContainer;
	var insertAfter = (i==0) ? 'rel-domain-msg' 
							 : $('rel-range-'+(i-1)+'-msg') 
							 	? 'rel-range-'+(i-1)+'-msg'
							 	: 'rel-range-'+(i-1);
	
	tb.insert(insertAfter,
			  tb.createDropDown('rel-range-'+i, gLanguage.getMessage('TYPE'), 
	                            this.getDatatypeOptions(), 
	                            "relToolBar.removeRangeOrType('rel-range-"+i+"')",
	                            0, 
	                            'isAttributeType="true" ' + 
	                            SMW_REL_NO_EMPTY_SELECTION, true));
	tb.insert('rel-range-'+i,
	          tb.createText('rel-range-'+i+'-msg', gLanguage.getMessage('ENTER_TYPE'), '' , true));
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));
},

getDatatypeOptions: function() {
	var options = new Array();
	var builtinTypes = gDataTypes.getBuiltinTypes();
	var userTypes    = gDataTypes.getUserDefinedTypes();
	options = builtinTypes.concat([""], userTypes);
	return options;
},

removeRangeOrType: function(id) {
	var rangeOrTypeInput = $(id);
	if (rangeOrTypeInput != null) {
		var tb = this.toolbarContainer;
		var rowsAfterRemoved = rangeOrTypeInput.parentNode.parentNode.nextSibling;

		// get ID of range input to be removed.
		var idOfValueInput = rangeOrTypeInput.getAttribute('id');
		var i = parseInt(idOfValueInput.substr(idOfValueInput.length-1, idOfValueInput.length));

		// remove it
		tb.remove(id);
		if ($(id+'-msg')) {
			tb.remove(id+'-msg');
		}
		
		// remove gap from IDs
		id = idOfValueInput.substr(0, idOfValueInput.length-1);
		var obj;
		while ((obj = $(id + ++i))) {
			// is there a delete-button
			var delBtn = obj.up().down('a');
			if (delBtn) {
				var action = delBtn.getAttribute("href");
				var regex = new RegExp(id+i);
				action = action.replace(regex, id+(i-1));
				delBtn.setAttribute("href", action);
			}
			tb.changeID(obj, id + (i-1));
			if ((obj = $(id + i + '-msg'))) {
				tb.changeID(obj, id + (i-1) + '-msg');
			}
		}
		tb.finishCreation();
		gSTBEventActions.initialCheck($("relation-content-box"));

	}

},

createNewRelation: function() {
	var relName = $("rel-name").value;
	//Check if Inputbox is empty
	if(relName=="" || relName == null ){
		alert(gLanguage.getMessage('INPUT_BOX_EMPTY'));
		return;
    }
	// Create an ontology modifier instance
	var i = 0;

	// get all ranges and types
	var rangesAndTypes = new Array();
	while($('rel-range-'+i) != null) {
		if ($('rel-range-'+i).getAttribute("isAttributeType") == "true") {
			rangesAndTypes.push(gLanguage.getMessage('TYPE')+$('rel-range-'+i).value); // add as type
		} else {
			rangesAndTypes.push(gLanguage.getMessage('CATEGORY')+$('rel-range-'+i).value);	// add as category
		}
		i++;
	}
	/*STARTLOG*/
	var signature = "";
	for (i = 0; i < rangesAndTypes.length; i++) {
		signature += rangesAndTypes[i];
		if (i < rangesAndTypes.length-1) {
			signature += ', ';
		}
	}
    smwhgLogger.log(relName+":"+signature,"STB-Properties","create_added");
	/*ENDLOG*/

	this.om.createRelation(relName,
					       gLanguage.getMessage('CREATE_PROPERTY'),
	                       $("rel-domain").value, rangesAndTypes);
	//show list
	this.fillList(true);
},


changeItem: function(selindex) {
	this.wtp.initialize();
	//Get new values
	var relName = $("rel-name").value;
	var value = this.getRelationValue();
	var text = $("rel-show").value;

   	//Check if Inputbox is empty
	if(relName=="" || relName == null ){
		alert(gLanguage.getMessage('INPUT_BOX_EMPTY'));
		return;
	}

   //Get relations
   var annotatedElements = this.wtp.getRelations();

	if ((selindex!=null) && ( selindex >=0) && (selindex <= annotatedElements.length)  ){
		var relation = annotatedElements[selindex];
		/*STARTLOG*/
		var oldName = relation.getName();
		var oldValues = relation.getValue();
	    smwhgLogger.log(oldName+":"+oldValues+"->"+relName+":"+value,"STB-Properties","edit_annotation_change");
		/*ENDLOG*/
		if ($("rel-replace-all") && $("rel-replace-all").down('input').checked == true) {
			// rename all occurrences of the relation
			var relations = this.wtp.getRelation(relation.getName());
			for (var i = 0, len = relations.length; i < len; i++) {
				relations[i].rename(relName);
			}
			editAreaLoader.execCommand(editAreaName, "resync_highlight(true)");
		}
 		//change relation
		relation.rename(relName);
		relation.changeValue(value);
		relation.changeRepresentation(text);
		
   }

	//show list
	this.fillList(true);
},

deleteItem: function(selindex) {
	this.wtp.initialize();
	//Get relations
	var annotatedElements = this.wtp.getRelations();

	//delete relation
	if (   (selindex!=null)
	    && (selindex >=0)
	    && (selindex <= annotatedElements.length)  ){
		var anno = annotatedElements[selindex];
		var replText = (anno.getRepresentation() != "")
		               ? anno.getRepresentation()
		               : (anno.getValue() != ""
		                  ? anno.getValue()
		                  : "");
		/*STARTLOG*/
	    smwhgLogger.log(anno.getName()+":"+anno.getValue(),"STB-Properties","edit_annotation_delete");
		/*ENDLOG*/
		anno.remove(replText);
	}
	//show list
	this.fillList(true);
},

newPart: function() {
    var html;
    this.wtp.initialize();
    var selection = this.wtp.getSelection();

	/*STARTLOG*/
    smwhgLogger.log(selection,"STB-Properties","haspart_clicked");
	/*ENDLOG*/

	this.showList = false;
	this.currentAction = "haspart";

	var path = wgArticlePath;
	var dollarPos = path.indexOf('$1');
	if (dollarPos > 0) {
		path = path.substring(0, dollarPos);
	}
	var poLink = "<a href='"+wgServer+path+gLanguage.getMessage('PROP_HAS_PART')+ "' " +
			     "target='blank'> "+gLanguage.getMessage('HAS_PART')+"</a>";
	var bsuLink = "<a href='"+wgServer+path+gLanguage.getMessage('PROP_HBSU')+"' " +
			      "target='blank'> "+gLanguage.getMessage('HBSU')+"</a>";

	var tb = this.createToolbar(SMW_REL_ALL_VALID);	
	tb.append(tb.createText('rel-help-msg', gLanguage.getMessage('DEFINE_PART_OF'), '' , true));
	tb.append(tb.createText('rel-help-msg', wgTitle, '' , true));
	tb.append(tb.createRadio('rel-partof', '', [poLink, bsuLink], -1, 
							 SMW_REL_CHECK_PART_OF_RADIO, true));
	
	tb.append(tb.createInput('rel-name', gLanguage.getMessage('OBJECT'), selection, '',
	                         SMW_REL_CHECK_EMPTY_NEV,
	                         true));
	tb.append(tb.createText('rel-name-msg', '', '' , true));
	tb.append(tb.createInput('rel-show', gLanguage.getMessage('SHOW'), '', '', '', true));
	var links = [['relToolBar.addPartOfRelation()',gLanguage.getMessage('ADD'), 'rel-confirm', 
	                                               gLanguage.getMessage('INVALID_VALUES'), 'rel-invalid'],
				 ['relToolBar.cancel()', gLanguage.getMessage('CANCEL')]
				];
	tb.append(tb.createLink('rel-links', links, '', true));
				
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));

	//Sets Focus on first Element
	setTimeout("$('rel-partof').focus();",50);
},

checkPartOfRadio: function(element) {
	var element = $(element).elements["rel-partof"];
	if (element[0].checked == true || element[1].checked == true) {
		return true;
	}
	return false;
},

addPartOfRelation: function() {
	var element = $('rel-partof').elements["rel-partof"];
	var poType = "";
	if (element[0].checked == true) {
		poType = gLanguage.getMessage('HAS_PART');
	} else if (element[1].checked == true) {
		poType = gLanguage.getMessage('HBSU');
	}

	var obj = $("rel-name").value;
	/*STARTLOG*/
    smwhgLogger.log(poType+":"+obj,"STB-Properties","haspart_added");
	/*ENDLOG*/
	if (obj == "") {
		alert(gLanguage.getMessage('NO_OBJECT_FOR_POR'));
	}
	var show = $("rel-show").value;

	this.wtp.initialize();
	this.wtp.addRelation(poType, obj, show, false);
	this.fillList(true);
},

getselectedItem: function(selindex) {
	this.wtp.initialize();
	var html;
    var renameAll = "";

	var annotatedElements = this.wtp.getRelations();
	if (   selindex == null
	    || selindex < 0
	    || selindex >= annotatedElements.length) {
		// Invalid index
		return;
	}
	this.showList = false;
	this.currentAction = "editannotation";
	
	var relation = annotatedElements[selindex];
	
	/*STARTLOG*/
    smwhgLogger.log(relation.getName()+":"+relation.getValue(),"STB-Properties","editannotation_clicked");
	/*ENDLOG*/
	
	var tb = this.createToolbar(SMW_REL_ALL_VALID);

	var relations = this.wtp.getRelation(relation.getName());
	if (relations.length > 1) {
	    renameAll = tb.createCheckBox('rel-replace-all', '', [gLanguage.getMessage('RENAME_ALL_IN_ARTICLE')], [], '', true);
	}

	function getSchemaCallback(request) {
		tb.hideSandglass();
		if (request.status != 200) {
			// call for schema data failed, do nothing.
			alert(gLanguage.getMessage('RETRIEVE_SCHEMA_DATA'));
			return;
		}

		var parameterNames = [];

		if (request.responseText != 'noSchemaData') {

			var schemaData = GeneralXMLTools.createDocumentFromString(request.responseText);

			// read parameter names
			parameterNames = [];
			for (var i = 0, n = schemaData.documentElement.childNodes.length; i < n; i++) {
				parameterNames.push(schemaData.documentElement.childNodes[i].getAttribute("name"));
			}
		} else { // schema data could not be retrieved for some reason (property may not yet exist). Show "Value" as default.
			for (var i = 0; i < relation.getArity()-1; i++) {
		 		parameterNames.push("Value");
			}
		}

		var valueInputs = new Array();
		for (var i = 0; i < relation.getArity()-1; i++) {
			var parName = (parameterNames.length > i) 
							? parameterNames[i]
							: "Page";
			var typeCheck = 'smwCheckType="' + 
			                parName.toLowerCase() + 
			                ': valid' +
	 						'? (color: lightgreen, hideMessage, valid:true)' +
			                ': (color: red, showMessage:INVALID_FORMAT_OF_VALUE, valid:false)" ';

			var obj = tb.createInput('rel-value-'+i, parName, 
									 relation.getSplitValues()[i], '', 
									 typeCheck ,true);
			valueInputs.push(obj);
			obj = tb.createText('rel-value-'+i+'-msg', '', '', true);
			valueInputs.push(obj);
		}
		tb.append(tb.createInput('rel-name', gLanguage.getMessage('PROPERTY'), relation.getName(), '', 
								 SMW_REL_CHECK_PROPERTY_UPDATE_SCHEMA +
		 						 SMW_REL_CHECK_EMPTY,
		 						 true));
		tb.append(tb.createText('rel-name-msg', '', '' , true));
		if (renameAll !='') {
			tb.append(renameAll);
		}
		tb.append(valueInputs);
		tb.append(tb.createInput('rel-show', gLanguage.getMessage('SHOW'), relation.getRepresentation(), '', '', true));

		var links = [['relToolBar.changeItem('+selindex+')',gLanguage.getMessage('CHANGE'), 'rel-confirm', 
		                                                    gLanguage.getMessage('INVALID_VALUES'), 'rel-invalid'],
					 ['relToolBar.deleteItem(' + selindex +')', gLanguage.getMessage('DELETE')],
					 ['relToolBar.cancel()', gLanguage.getMessage('CANCEL')]
					];
		tb.append(tb.createLink('rel-links', links, '', true));
		
		tb.finishCreation();
		gSTBEventActions.initialCheck($("relation-content-box"));

		//Sets Focus on first Element
		setTimeout("$('rel-name').focus();",50);
	}
	tb.append(tb.createText('rel-help-msg', gLanguage.getMessage('CHANGE_PROPERTY'), '' , true));
	if(relation.getName().strip()!=""){
		this.toolbarContainer.showSandglass('rel-help-msg');
		sajax_do_call('smwfRelationSchemaData', [relation.getName()], getSchemaCallback.bind(this));
	}
}

};// End of Class

var relToolBar = new RelationToolBar();
Event.observe(window, 'load', relToolBar.callme.bindAsEventListener(relToolBar));



// SMW_Properties.js
// under GPL-License
var DOMAIN_HINT = "Has domain hint";
var RANGE_HINT = "Has range hint";
var HAS_TYPE = "has type";
var MAX_CARDINALITY = "Has max cardinality";
var MIN_CARDINALITY = "Has min cardinality";
var INVERSE_OF = "Is inverse of";
var TRANSITIVE_RELATION = "Transitive relations";
var SYMMETRICAL_RELATION = "Symmetrical relations";

var SMW_PRP_ALL_VALID =	
	'smwAllValid="allValid ' +
 		'? (show:prop-confirm, hide:prop-invalid) ' +
 		': (show:prop-invalid, hide:prop-confirm)"';

var SMW_PRP_CHECK_CATEGORY = 
	'smwCheckType="category: exists ' +
		'? (color: lightgreen, hideMessage, valid:true) ' +
	 	': (color: orange, showMessage:CATEGORY_DOES_NOT_EXIST, valid:true)" ';

var SMW_PRP_CHECK_PROPERTY = 
	'smwCheckType="property: exists ' +
		'? (color: lightgreen, hideMessage, valid:true) ' +
	 	': (color: orange, showMessage:PROPERTY_DOES_NOT_EXIST, valid:true)" ';

var SMW_PRP_CHECK_INTEGER =
	'smwCheckType="integer: valid ' +
		'? (color: lightgreen, hideMessage, valid:true) ' +
	 	': (color: red, showMessage:INVALID_FORMAT_OF_VALUE, valid:false)" ';

var SMW_PRP_HINT_CATEGORY =
	'typeHint = "14" ';

var SMW_PRP_HINT_PROPERTY =
	'typeHint="102" ';
	
var SMW_PRP_CHECK_EMPTY = 
	'smwCheckEmpty="empty' +
		'? (color:red, showMessage:MUST_NOT_BE_EMPTY, valid:false) ' +
		': (color:white, hideMessage)"';
		
var SMW_PRP_CHECK_EMPTY_WIE =   // WIE = Warning if empty but still valid
	'smwCheckEmpty="empty' +
		'? (color:orange, showMessage:VALUE_IMPROVES_QUALITY) ' +
		': (color:white, hideMessage)"';

var SMW_PRP_CHECK_EMPTY_VIE = // valid if empty
	'smwCheckEmpty="empty' +
		'? (color:white, hideMessage, valid:true) ' +
		': ()"';
		
var SMW_PRP_NO_EMPTY_SELECTION =
	'smwCheckEmpty="empty' +
	'? (color:red, showMessage:SELECTION_MUST_NOT_BE_EMPTY, valid:false) ' +
	': (color:white, hideMessage, valid:true)"';		

var PRP_NARY_CHANGE_LINKS = [['propToolBar.addType()',gLanguage.getMessage('ADD_TYPE'), 'prp-add-type-lnk'],
				 			 ['propToolBar.addRange()', gLanguage.getMessage('ADD_RANGE'), 'prp-add-range-lnk']];
		
var PRP_APPLY_LINK =
	[['propToolBar.apply()', 'Apply', 'prop-confirm', gLanguage.getMessage('INVALID_VALUES'), 'prop-invalid']];

var PropertiesToolBar = Class.create();

PropertiesToolBar.prototype = {

initialize: function() {
	//Reference
	this.genTB = new GenericToolBar();
	this.toolbarContainer = null;
	this.pendingIndicator = null;
	this.isRelation = true;
	this.isNAry = false;
	this.numOfParams = 0;
	this.prpNAry = 0;
},

showToolbar: function(request){
	if (this.propertiescontainer == null) {
		return;
	}
	this.propertiescontainer.setHeadline(gLanguage.getMessage('PROPERTY_PROPERTIES'));
	this.wtp = new WikiTextParser();
	this.om = new OntologyModifier();
	
	this.createContent();
	
},

callme: function(event){
	
	if(wgAction == "edit" 
	   && (wgNamespaceNumber == 100 || wgNamespaceNumber == 102)
	   && stb_control.isToolbarAvailable()){
		this.propertiescontainer = stb_control.createDivContainer(PROPERTIESCONTAINER, 0);

		// Events can not be registered in onLoad => make a timeout
		setTimeout("propToolBar.showToolbar();",1);	
	}	
},

/**
 * Creates the content of the Property Properties container. 
 */
createContent: function() {
	if (this.propertiescontainer == null) {
		return;
	}
	this.wtp.initialize();
	
	var type    = this.wtp.getRelation(HAS_TYPE);
	var domain  = this.wtp.getRelation(DOMAIN_HINT);
	var range   = this.wtp.getRelation(RANGE_HINT);
	var maxCard = this.wtp.getRelation(MAX_CARDINALITY);
	var minCard = this.wtp.getRelation(MIN_CARDINALITY);
	var inverse = this.wtp.getRelation(INVERSE_OF);
	  
	var transitive = this.wtp.getCategory(TRANSITIVE_RELATION);
	var symmetric = this.wtp.getCategory(SYMMETRICAL_RELATION);
	
	var changed = this.hasAnnotationChanged(
						[type, domain, range, maxCard, minCard, inverse], 
	                    [transitive, symmetric]);
	
	if (!changed) {
		// nothing changed
		return;
	}
		
	if (this.toolbarContainer) {
		this.toolbarContainer.release();
	}
	this.toolbarContainer = new ContainerToolBar('properties-content',800,this.propertiescontainer);
	var tb = this.toolbarContainer;
	tb.createContainerBody(SMW_PRP_ALL_VALID);
	
	if (type) {
		type = type[0].getValue();
		// remove the prefix "Type:" and lower the case of the first character
		type = type.charAt(5).toLowerCase() + type.substring(6);
	
	} else {
		type = "page";
	}
	this.isRelation = (type == "page");
	
	if (domain == null) {
		domain = "";
	} else {
		domain = domain[0].getValue();
		if (domain.indexOf(gLanguage.getMessage('CATEGORY')) == 0) {
			// Strip the category-keyword
			domain = domain.substring(9);
		}
	}
	if (range == null) {
		range = "";
	} else {
		range = range[0].getValue();
		if (range.indexOf(gLanguage.getMessage('CATEGORY')) == 0) {
			range = range.substring(9);
		}
	}
	if (maxCard == null) {
		maxCard = "";
	} else {
		maxCard = maxCard[0].getValue();
	}
	if (minCard == null) {
		minCard = "";
	} else {
		minCard = minCard[0].getValue();
	}
	if (inverse == null) {
		inverse = "";
	} else {
		inverse = inverse[0].getValue();
		if (inverse.indexOf(gLanguage.getMessage('PROPERTY')) == 0) {
			inverse = inverse.substring(9);
		}
	}
	transitive = (transitive != null) ? "checked" : "";
	symmetric = (symmetric != null) ? "checked" : "";

	var tb = this.toolbarContainer;
	tb.append(tb.createInput('prp-domain', gLanguage.getMessage('DOMAIN'), domain, '',
	                         SMW_PRP_CHECK_CATEGORY + 
	                         SMW_PRP_CHECK_EMPTY_WIE + 
	                         SMW_PRP_HINT_CATEGORY,
	                         true));
	tb.append(tb.createText('prp-domain-msg', '', '' , true));

	tb.append(tb.createInput('prp-range', gLanguage.getMessage('RANGE'), range, '',
	                         SMW_PRP_CHECK_CATEGORY + 
	                         SMW_PRP_CHECK_EMPTY_WIE + 
	                         SMW_PRP_HINT_CATEGORY,
	                         true));
	tb.append(tb.createText('prp-range-msg', '', '' , true));

	tb.append(tb.createInput('prp-inverse-of', gLanguage.getMessage('INVERSE_OF'), inverse, '',
	                         SMW_PRP_CHECK_PROPERTY + 
	                         SMW_PRP_HINT_PROPERTY+
	                         SMW_PRP_CHECK_EMPTY_VIE,
	                         true));
	tb.append(tb.createText('prp-inverse-of-msg', '', '' , true));

	tb.append(this.createTypeSelector("prp-attr-type", "prpSelection", false, 
									  type, '', 
									  'smwChanged="(call:propToolBar.attrTypeChanged,call:propToolBar.enableWidgets)"' +
									  SMW_PRP_NO_EMPTY_SELECTION));
	tb.append(tb.createInput('prp-min-card', gLanguage.getMessage('MIN_CARD'), minCard, '',
	                         SMW_PRP_CHECK_INTEGER + 
	                         SMW_PRP_CHECK_EMPTY_VIE,
	                         true));
	tb.append(tb.createText('prp-min-card-msg', '', '' , true));
	tb.append(tb.createInput('prp-max-card', gLanguage.getMessage('MAX_CARD'), maxCard, '',
	                         SMW_PRP_CHECK_INTEGER +
	                         SMW_PRP_CHECK_EMPTY_VIE,
	                         true));
	tb.append(tb.createText('prp-max-card-msg', '', '' , true));
	tb.append(tb.createCheckBox('prp-transitive', '', [gLanguage.getMessage('TRANSITIVE')], [transitive == 'checked' ? 0 : -1], 'name="transitive"', true));
	tb.append(tb.createCheckBox('prp-symmetric', '', [gLanguage.getMessage('SYMMETRIC')], [symmetric == 'checked' ? 0 : -1], 'name="symmetric"', true));

	this.prpNAry = 0;
	this.numOfParams = 0;
	this.isNAry = false;
	var types = this.wtp.getRelation(HAS_TYPE);
	if (types) {
		types = types[0];
		this.isNAry = (type.indexOf(';') > 0);
	}
	
	if (this.isNAry) {
		types = types.getSplitValues();
	
		var ranges = this.wtp.getRelation(RANGE_HINT);
		
		var rc = 0;
		for (var i = 0, num = types.length; i < num; ++i) {
			if (types[i] == gLanguage.getMessage('TYPE_PAGE')) {
				var r = "";
				if (ranges && rc < ranges.length) {
					r = ranges[rc++].getValue();
				}
				if (r.indexOf(gLanguage.getMessage('CATEGORY')) == 0) {
					r = r.substring(9);
				}
				tb.append(tb.createInput('prp-nary-' + i, gLanguage.getMessage('RANGE'), r, 
				                         'propToolBar.removeRangeOrType(\'prp-nary-' + i + '\')',
	                         			 SMW_PRP_CHECK_CATEGORY + 
	                         			 SMW_PRP_CHECK_EMPTY +
			                 			 SMW_PRP_HINT_CATEGORY,
	                         			 true));
				tb.append(tb.createText('prp-nary-' + i + '-msg', '', '' , true));
				this.prpNAry++;
				this.numOfParams++;
			} else {
				var t = types[i];
				if (t.indexOf(gLanguage.getMessage('TYPE')) == 0) {
					t = t.substring(5);
					tb.append(this.createTypeSelector("prp-nary-" + i, 
					                                  "prpNaryType"+i, true, t,
					                                  "propToolBar.removeRangeOrType('prp-nary-" + i + "')",
					                                  SMW_PRP_NO_EMPTY_SELECTION));
					
					this.prpNAry++;
					this.numOfParams++;
				}
			}
		}
	}
	
	tb.append(tb.createLink('prp-change-links', PRP_NARY_CHANGE_LINKS, '', true));
	tb.append(tb.createLink('prp-links', PRP_APPLY_LINK, '', true));
				
	tb.finishCreation();
	this.enableWidgets();
	gSTBEventActions.initialCheck($("properties-content-box"));
	//Sets Focus on first Element
	setTimeout("$('prp-domain').focus();",50);
    
},

hasAnnotationChanged: function(relations, categories) {
	var changed = false;
	if (!this.relValues) {
		changed = true;
		this.relValues = new Array(relations.length);
		this.catValues = new Array(categories.length);
	}
	
	// check properties that are defined as relation
	for (var i = 0; i < relations.length; i++) {
		if (!relations[i] && this.relValues[i]) {
			// annotation has been removed
			changed = true;
			this.relValues[i] = null;
		} else if (relations[i]) {
			// there is an annotation
			var value = relations[i][0].annotation;
			if (this.relValues[i] != value) {
				// and it has changed
				this.relValues[i] = value;
				changed = true;
			}
		}
	}
	// check properties that are defined as category
	for (var i = 0; i < categories.length; i++) {
		if (!categories[i] && this.catValues[i]) {
			// annotation has been removed
			changed = true;
			this.catValues[i] = false;
		} else if (categories[i] && !this.catValues[i]) {
			// annotation has been added
			this.catValues[i] = true;
			changed = true;
		}
	}
	return changed;
},

addType: function() {
	var insertAfter = (this.numOfParams == 0) 
						? 'prp-symmetric'
						: "prp-nary-" + (this.prpNAry-1) + '-msg';
	this.toolbarContainer.insert(insertAfter,
			  this.createTypeSelector("prp-nary-" + this.prpNAry, 
	                                  "prpNaryType"+this.prpNAry, true, "",
	                                  "propToolBar.removeRangeOrType('prp-nary-" + this.prpNAry + "')",
	                                  SMW_PRP_NO_EMPTY_SELECTION));
	this.prpNAry++;
	this.numOfParams++;
	this.toolbarContainer.finishCreation();
	this.enableWidgets();
	gSTBEventActions.initialCheck($("properties-content-box"));
		
},

addRange: function() {
	var insertAfter = (this.numOfParams == 0) 
						? 'prp-symmetric'
						: "prp-nary-" + (this.prpNAry-1) + '-msg';
	var tb = this.toolbarContainer;
	tb.insert(insertAfter,
			  tb.createInput('prp-nary-' + this.prpNAry, gLanguage.getMessage('RANGE'), "", 
	                         'propToolBar.removeRangeOrType(\'prp-nary-' + this.prpNAry + '\')',
                 			 SMW_PRP_CHECK_CATEGORY + 
                 			 SMW_PRP_CHECK_EMPTY +
                 			 SMW_PRP_HINT_CATEGORY,
                 			 true));
	tb.insert('prp-nary-' + this.prpNAry,
	          tb.createText('prp-nary-' + this.prpNAry + '-msg', '', '' , true));

	this.prpNAry++;
	this.numOfParams++;
	this.toolbarContainer.finishCreation();
	this.enableWidgets();
	gSTBEventActions.initialCheck($("properties-content-box"));
	
},

removeRangeOrType: function(domID) {
	
	this.toolbarContainer.remove(domID)
	this.toolbarContainer.remove(domID+'-msg');
	this.numOfParams--;
	if (domID == 'prp-nary-'+(this.prpNAry-1)) {
		while (this.prpNAry > 0) {
			--this.prpNAry;
			if ($('prp-nary-'+ this.prpNAry)) {
				this.prpNAry++;
				break;
			}
		}
	}
	if (this.numOfParams == 0) {
		this.prpNAry = 0;
		this.isRelation = true;
		this.isNAry = false;
		var selector = $('prp-attr-type');
		var options = selector.options;
		for (var i = 0; i < options.length; i++) {
			if (options[i].value == 'page') {
				selector.selectedIndex = i;
				break;
			}
		}
		this.enableWidgets();
	}
	this.toolbarContainer.finishCreation();
	this.enableWidgets();
	gSTBEventActions.initialCheck($("properties-content-box"));
},

/**
 * This method is called, when the type of the property has been changed. It
 * sets the flag <isNAry>.
 * @param string target
 * 		ID of the element, on which the change event occurred.
 */
attrTypeChanged: function(target) {
	target = $(target);
	if (target.id == 'prp-attr-type') {
		this.isNAry = target.value == 'n-ary';
		this.isRelation = target.value == 'page';
	}
},

createTypeSelector: function(id, name, onlyTypes, type, deleteAction, attributes) {
	var closure = function() {
		
		var origTypeString = type;
		if (type) {
			type = type.toLowerCase();
			if (type.indexOf(';') > 0) {
				type = 'n-ary';
			}
		}
		var typeFound = false;
		var builtinTypes = gDataTypes.getBuiltinTypes();
		var userTypes = gDataTypes.getUserDefinedTypes();
		var allTypes = builtinTypes.concat([""],
											onlyTypes ? [] 
											          : [gLanguage.getMessage('PAGE_TYPE'), 
											             gLanguage.getMessage('NARY_TYPE'),""],
											userTypes);
		
		var selection = $(id);
		if (selection) {
			selection.length = allTypes.length;
		}
		var selIdx = -1;
		
//		var sel = "";
		for (var i = 0; i < allTypes.length; i++) {
			var lcTypeName = allTypes[i].toLowerCase();
			if (type == lcTypeName) {
//				sel += '<option selected="">' + allTypes[i] + '</option>';
				typeFound = true;
				if (selection) {
					selection.options[i] = new Option(allTypes[i], allTypes[i], true, true);
				}
				selIdx = i;
			} else {
//				sel += '<option>' + allTypes[i] + '</option>';
				if (selection) {
					selection.options[i] = new Option(allTypes[i], allTypes[i], false, false);
				}
			}
		}
		if (type && type != gLanguage.getMessage('NARY_TYPE') && !typeFound) {
			if (selection) {
				selection.options[i] = new Option(origTypeString, origTypeString, true, true);
			}
			selIdx = allTypes.length;
			allTypes[allTypes.length] = origTypeString;
//			sel += '<option selected="">' + origTypeString + '</option>';
		}
		
//		if ($(id)) {
//			$(id).innerHTML = sel;
//		}
		gSTBEventActions.initialCheck($(id).up());
		propToolBar.toolbarContainer.finishCreation();
		return [allTypes, selIdx];
	};
	var sel = [[gLanguage.getMessage('RETRIEVING_DATATYPES')],0];
	if (gDataTypes.getUserDefinedTypes() == null 
	    || gDataTypes.getBuiltinTypes() == null
	    || !$(id)) {
		// types are not available yet
		gDataTypes.refresh(closure);
	} else {
		sel = closure();
	}
	if (!deleteAction) {
		deleteAction = "";
	}
	if (!attributes) {
		attributes = "";
	}
	
	var dropDown = this.toolbarContainer.createDropDown(id, gLanguage.getMessage('TYPE'), sel[0], deleteAction, sel[1], attributes + ' name="' + name +'"', true);
	dropDown += this.toolbarContainer.createText(id + '-msg', '', '' , true);
	
	return dropDown;
},

enableWidgets: function() {
	var tb = propToolBar.toolbarContainer;
	if (propToolBar.isRelation && !propToolBar.isNAry) {
		$("prp-range").enable();
		$("prp-inverse-of").enable();
		$("prp-transitive").enable();
		$("prp-symmetric").enable();
	} else {
		$("prp-range").disable();
		$("prp-inverse-of").disable();
		$("prp-transitive").disable();
		$("prp-symmetric").disable();
	}
	
	if (propToolBar.isNAry) {
		$('prp-add-type-lnk').show();
		$('prp-add-range-lnk').show();
		$('prp-min-card').disable();
		$('prp-max-card').disable();
	} else {
		$('prp-add-type-lnk').hide();
		$('prp-add-range-lnk').hide();
		$('prp-min-card').enable();
		$('prp-max-card').enable();
	}
	
	for (var i = 0; i < propToolBar.prpNAry; i++) {
		var obj = $('prp-nary-'+i);
		if (obj) {
			if (propToolBar.isNAry) {
				obj.enable();
			} else {
				obj.disable();
			}	
		}
	}
	
},

cancel: function(){
	this.toolbarContainer.hideSandglass();
	this.createContent();
},

apply: function() {
	this.wtp.initialize();
	var domain   = $("prp-domain").value;
	var range    = this.isRelation ? $("prp-range").value : null;
	var attrType = $("prp-attr-type").value;
	var inverse  = this.isRelation ? $("prp-inverse-of").value : null;
	var minCard  = this.isNAry ? null : $("prp-min-card").value;
	var maxCard  = this.isNAry ? null : $("prp-max-card").value;
	var transitive = this.isRelation ? $("prp-transitive") : null;
	var symmetric  = this.isRelation ? $("prp-symmetric") : null;

	domain   = (domain   != null && domain   != "") ? gLanguage.getMessage('CATEGORY')+domain : null;
	range    = (range    != null && range    != "") ? gLanguage.getMessage('CATEGORY')+range : null;
	attrType = (attrType != null && attrType != "") ? gLanguage.getMessage('TYPE')+attrType : null;
	inverse  = (inverse  != null && inverse  != "") ? gLanguage.getMessage('PROPERTY')+inverse : null;
	minCard  = (minCard  != null && minCard  != "") ? minCard : null;
	maxCard  = (maxCard  != null && maxCard  != "") ? maxCard : null;

	var domainAnno = this.wtp.getRelation(DOMAIN_HINT);
	var rangeAnno = this.wtp.getRelation(RANGE_HINT);
	var attrTypeAnno = this.wtp.getRelation(HAS_TYPE);
	var maxCardAnno = this.wtp.getRelation(MAX_CARDINALITY);
	var minCardAnno = this.wtp.getRelation(MIN_CARDINALITY);
	var inverseAnno = this.wtp.getRelation(INVERSE_OF);
	  
	var transitiveAnno = this.wtp.getCategory(TRANSITIVE_RELATION);
	var symmetricAnno = this.wtp.getCategory(SYMMETRICAL_RELATION);
	
	
	// change existing annotations
	if (domainAnno != null) {
		if (domain == null) {
			domainAnno[0].remove("");
		} else {
			domainAnno[0].changeValue(domain);
		}
	}
	if (rangeAnno != null) {
		if (range == null) {
			rangeAnno[0].remove("");
		} else {
			rangeAnno[0].changeValue(range);
		}
	} 
	if (attrTypeAnno != null) {
		if (attrType == null) {
			attrTypeAnno[0].remove("");
		} else {
			attrTypeAnno[0].changeValue(attrType);
		}
	} 
	if (maxCardAnno != null) {
		if (maxCard == null) {
			maxCardAnno[0].remove("");
		} else {
			maxCardAnno[0].changeValue(maxCard);
		}
	} 
	if (minCardAnno != null) {
		if (minCard == null) {
			minCardAnno[0].remove("");
		} else {
			minCardAnno[0].changeValue(minCard);
		}
	}
	if (inverseAnno != null) {
		if (inverse == null) {
			inverseAnno[0].remove("");
		} else {
			inverseAnno[0].changeValue(inverse);
		}
	}
	if (transitiveAnno != null && (transitive == null || !transitive.down('input').checked)) {
		transitiveAnno.remove("");
	}
	if (symmetricAnno != null && (symmetric == null || !symmetric.down('input').checked)) {
		symmetricAnno.remove("");
	}
	
	// append new annotations
	if (domainAnno == null && domain != null) {
		this.wtp.addRelation(DOMAIN_HINT, domain, null, true);
	} 
	if (rangeAnno == null && range != null) {
		this.wtp.addRelation(RANGE_HINT, range, null, true);
	}
	if (attrTypeAnno == null && attrType != null) {
		this.wtp.addRelation(HAS_TYPE, attrType, null, true);
	}
	if (maxCardAnno == null && maxCard != null) {
		this.wtp.addRelation(MAX_CARDINALITY, maxCard, null, true);
	}
	if (minCardAnno == null && minCard != null) {
		this.wtp.addRelation(MIN_CARDINALITY, minCard, null, true);
	}
	if (inverseAnno == null && inverse != null) {
		this.wtp.addRelation(INVERSE_OF, inverse, null, true);
	}
	if (transitive != null && transitive.down('input').checked && transitiveAnno == null) {
		this.wtp.addCategory(TRANSITIVE_RELATION, true);
	}
	if (symmetric != null && symmetric.down('input').checked && symmetricAnno == null) {
		this.wtp.addCategory(SYMMETRICAL_RELATION, true);
	}
	
	if (this.isNAry) {
		// Handle the definition of n-ary relations
		// First, remove all range hints
		rangeAnno = this.wtp.getRelation(RANGE_HINT);
		if (rangeAnno) {
			for (var i = 0, num = rangeAnno.length; i < num; i++) {
				rangeAnno[i].remove("");
			}
		}
		
		// Create new range hints.
		var typeString = "";
		for (var i = 0; i < this.prpNAry; i++) {
			var obj = $('prp-nary-'+i);
			if (obj) {
				if (obj.tagName && obj.tagName == "SELECT") {
					// Type found
					typeString += gLanguage.getMessage('TYPE') + obj.value + ";";
				} else {
					// Page found
					var r = gLanguage.getMessage('CATEGORY')+obj.value;
					typeString += gLanguage.getMessage('TYPE_PAGE')+';';
					this.wtp.addRelation(RANGE_HINT, r, null, true);
				}
			}
		}
		
		// add the n-ary type definition
		if (typeString != "") {
			// remove final semi-colon
			typeString = typeString.substring(0, typeString.length-1);
			attrTypeAnno = this.wtp.getRelation(HAS_TYPE);
			if (attrTypeAnno != null) {
				attrTypeAnno[0].changeValue(typeString);
			} else {			
				this.wtp.addRelation(HAS_TYPE, typeString, null, true);
			}
		}
	}
	editAreaLoader.execCommand(editAreaName, "resync_highlight(true)");
	
	this.createContent();
	this.refreshOtherTabs();
	
	/*STARTLOG*/
    smwhgLogger.log(wgTitle,"STB-PropertyProperties","property_properties_changed");
	/*ENDLOG*/
	
},

refreshOtherTabs: function () {
	relToolBar.fillList();
	catToolBar.fillList();
}
};// End of Class

var propToolBar = new PropertiesToolBar();
Event.observe(window, 'load', propToolBar.callme.bindAsEventListener(propToolBar));


// SMW_Refresh.js
// under GPL-License
var RefreshSemanticToolBar = Class.create();

RefreshSemanticToolBar.prototype = {
	
	//Constructor
	initialize: function() {
		this.userIsTyping = false;
		this.contentChanged = false;
		this.wtp = null;
		
	},
	
	//Registers event 
	register: function(event){
		if(wgAction == "edit"
		   && stb_control.isToolbarAvailable()){
			Event.observe('wpTextbox1', 'change' ,this.changed.bind(this));
			Event.observe('wpTextbox1', 'keypress' ,this.setUserIsTyping.bind(this));
			this.registerTimer();
			this.editboxtext = "";
			
		}
	},
	
	changed: function() {
		this.contentChanged = true;
	},
	
	//Checks if user is typing, content has changed and refreshes the toolbar
	refresh: function(){
		if (this.userIsTyping){
			this.contentChanged = true;
			this.userIsTyping = false;
		} else if (this.contentChanged) {
			this.contentChanged = false;
			this.refreshToolBar();
		}
	},

	//registers automatic refresh
	registerTimer: function(){
		this.periodicalTimer = new PeriodicalExecuter(this.refresh.bind(this), 3);		
	},
	
	//deregisters automatic refresh
	deregisterTimer: function(){
		this.periodicalTime ? this.periodicalTimer.stop() : "";
	},
	
	setUserIsTyping: function(){
		this.userIsTyping = true;
	},
	
	//Refresh the Toolbar
	refreshToolBar: function() {
		if(window.catToolBar){
			catToolBar.fillList()
		}
		if(window.relToolBar){
			relToolBar.fillList()
		}
		   
		if(window.propToolBar){
			propToolBar.createContent();
		}
		
		// Check for syntax errors in the wiki text
		var saveButton = $('wpSave');	
		if (saveButton) {
			if (!this.wtp) {
				this.wtp = new WikiTextParser();
			}
			this.wtp.initialize();
			this.wtp.parseAnnotations();
			var error = this.wtp.getError();
			if (error == WTP_NO_ERROR) {
				saveButton.enable();
				if ($('wpSaveWarning')) {
					$('wpSaveWarning').remove();
				}
			} else {
				if (!$('wpSaveWarning')){
					saveButton.disable();
					new Insertion.Before(saveButton, 
						'<div id="wpSaveWarning" ' +
						  'style="background-color:#ee0000;' +
								 'color:white;' +
								 'font-weight:bold;' +
								 'text-align:left;">' +
								 gLanguage.getMessage('UNMATCHED_BRACKETS')+'</div>');
				}
			}
		}
		
	}
}

var refreshSTB = new RefreshSemanticToolBar();
Event.observe(window, 'load', refreshSTB.register.bindAsEventListener(refreshSTB));

// SMW_FactboxType.js
// under GPL-License
function factboxTypeChanged(select, title){
		$('typeloader').show();
		var type = select.options[select.options.selectedIndex].value;
		sajax_do_call('smwgNewAttributeWithType', [title, type], refreshAfterTypeChange);
}

function refreshAfterTypeChange(request){
	window.location.href=location.href;
}

// CombinedSearch.js
// under GPL-License
var CombinedSearchContributor = Class.create();
CombinedSearchContributor.prototype = {
	initialize: function() {
		// create a query placeHolder for potential ask-queries
		this.queryPlaceholder = document.createElement("div");
		this.queryPlaceholder.setAttribute("id", "queryPlaceholder");
		this.queryPlaceholder.innerHTML = gLanguage.getMessage('ADD_COMB_SEARCH_RES');
		this.pendingElement = null;
		this.tripleSearchPendingElement = null;
	},

	/**
	 * Register the contribuor and puts a button in the semantic toolbar.
	 */
	registerContributor: function() {
		if (!stb_control.isToolbarAvailable()) return;
		if (wgCanonicalSpecialPageName != 'Search' || wgCanonicalNamespace != 'Special') {
			// do only register on Special:Search
			return;
		}

		// register CS container
		this.comsrchontainer = stb_control.createDivContainer(COMBINEDSEARCHCONTAINER, 0);
		this.comsrchontainer.setHeadline(gLanguage.getMessage('COMBINED_SEARCH'));

		this.comsrchontainer.setContent('<div id="csFoundEntities"></div>');
		this.comsrchontainer.contentChanged();

		// register content function and notify about initial update

		var searchTerm = GeneralBrowserTools.getURLParameter("search");

		// do combined search and populate ST tab.
		if ($('stb_cont8-headline') == null) return;
		$("bodyContent").insertBefore(this.queryPlaceholder, $("bodyContent").firstChild);
		this.pendingElement = new OBPendingIndicator($('stb_cont8-headline'));
		this.tripleSearchPendingElement = new OBPendingIndicator($('queryPlaceholder'));
		if (searchTerm != undefined && searchTerm.strip() != '') {
			this.pendingElement.show();
			sajax_do_call('smwfCSDispatcher', [searchTerm], this.smwfCombinedSearchCallback.bind(this, "csFoundEntities"));
			this.tripleSearchPendingElement.show();
			sajax_do_call('smwfCSSearchForTriples', [searchTerm], this.smwfTripleSearchCallback.bind(this, "queryPlaceholder"));
		}

		// add query placeholder
	},
	
	smwfTripleSearchCallback: function(containerID, request) {
		this.tripleSearchPendingElement.hide();
		$(containerID).innerHTML = request.responseText;
	},

	smwfCombinedSearchCallback: function(containerID, request) {
		this.pendingElement.hide();
		$(containerID).innerHTML = request.responseText;
		this.comsrchontainer.contentChanged();
	},

	searchForAttributeValues: function(parts) {
		this.pendingElement.show($('cbsrch'));
		sajax_do_call('smwfCSAskForAttributeValues', [parts], this.smwfCombinedSearchCallback.bind(this, "queryPlaceholder"));
	},
	
	/**
	 * Navigates to OntologyBrowser
	 * 
	 * @param pageName name of page (URI encoded)
	 * @param pageNS namespace
	 * @param last part of path to OntologyBrowser (name of special page)
	 */
	navigateToOB: function(pageName, pageNS, ontoBrowserPath) {
		queryStr = "?entitytitle="+pageName+(pageNS != "" ? "&ns="+pageNS : "");
		var path = wgArticlePath.replace(/\$1/, ontoBrowserPath);
		smwhgLogger.log(pageName, "CS", "entity_opened_in_ob")
		window.open(wgServer + path + queryStr, "");
	},
	
	/**
	 * Navigates to Page
	 * 
	 * @param pageName name of page (URI encoded)
	 * @param pageNS namespace
	
	 */
	navigateToEntity: function(pageName, pageNS) {
		var path = wgArticlePath.replace(/\$1/, pageNS+":"+pageName);
		smwhgLogger.log(pageName, "CS", "entity_opened")
		window.open(wgServer + path, "");
	},
	
	/**
	 * Navigates to Page in edit mode
	 * 
	 * @param pageName name of page (URI encoded)
	 * @param pageNS namespace
	 */
	navigateToEdit: function(pageName, pageNS) {
		queryStr = "?action=edit";
		var path = wgArticlePath.replace(/\$1/, pageNS+":"+pageName);
		smwhgLogger.log(pageName, "CS", "entity_opened_to_edit");
		window.open(wgServer + path + queryStr, "");
	}

}

// create instance of contributor and register on load event so that the complete document is available
// when registerContributor is executed.
var csContributor = new CombinedSearchContributor();
Event.observe(window, 'load', csContributor.registerContributor.bind(csContributor));




// obSemToolContribution.js
// under GPL-License
/*
 * obSemToolContribution.js
 * Author: KK
 * Ontoprise 2007
 *
 * Contributions from OntologyBrowser for Semantic Toolbar
 */


var OBSemanticToolbarContributor = Class.create();
OBSemanticToolbarContributor.prototype = {
	initialize: function() {

		this.textArea = null; // will be initialized properly in registerContributor method.
		this.l1 = this.selectionListener.bindAsEventListener(this);
		this.l2 = this.selectionListener.bindAsEventListener(this);
		this.l3 = this.selectionListener.bindAsEventListener(this);
	},

	/**
	 * Register the contributor and puts a button in the semantic toolbar.
	 */
	registerContributor: function() {
		if (!stb_control.isToolbarAvailable() || wgAction != 'edit') return;
		this.comsrchontainer = stb_control.createDivContainer(CBSRCHCONTAINER, 0);
		this.comsrchontainer.setHeadline(gLanguage.getMessage('ONTOLOGY_BROWSER'));

		this.comsrchontainer.setContent(
			'<button type="button" disabled="true" ' +
			'id="openEntityInOB" name="navigateToOB" ' +
			'onclick="obContributor.navigateToOB(event, \''+gLanguage.getMessage('NS_SPECIAL')+":"+gLanguage.getMessage('OB_ID')+'\')">' +
			gLanguage.getMessage('MARK_A_WORD') +
			'</button>');
		this.comsrchontainer.contentChanged();

		// register standard wiki edit textarea (advanced editor registers by itself)
		this.activateTextArea("wpTextbox1");

	},


	activateTextArea: function(id) {
		if (this.textArea) {
			Event.stopObserving(this.textArea, 'select', this.l1);
			Event.stopObserving(this.textArea, 'mouseup', this.l2);
			Event.stopObserving(this.textArea, 'keyup', this.l3);
		}
		this.textArea = $(id);
		if (this.textArea) {
			Event.observe(this.textArea, 'select', this.l1);
			Event.observe(this.textArea, 'mouseup', this.l2);
			Event.observe(this.textArea, 'keyup', this.l3);
			// intially disabled
			if ($("openEntityInOB") != null) Field.disable("openEntityInOB");
		}
	},

	/**
	 * Called when the selection changes
	 */
	selectionListener: function(event) {
		if ($("openEntityInOB") == null) return;
		//if (!GeneralBrowserTools.isTextSelected(this.textArea)) {
		if (gEditInterface.getSelectedText().length == 0){
			// unselected
			Field.disable("openEntityInOB");
			$("openEntityInOB").innerHTML = "" + gLanguage.getMessage('MARK_A_WORD');
			this.textArea.focus();
		} else {
			// selected
			Field.enable("openEntityInOB");
			$("openEntityInOB").innerHTML = "" + gLanguage.getMessage('OPEN_IN_OB');
			this.textArea.focus();
		}
	},

	/**
	 * Navigates to the OntologyBrowser with ns and title
	 */
	navigateToOB: function(event, path) {
		//var selectedText = GeneralBrowserTools.getSelectedText(this.textArea);
		var selectedText = gEditInterface.getSelectedText();
		if (selectedText == '') {
			return;
		}
		var localURL = selectedText.split(":");
		if (localURL.length == 1) {
			// no namespace
			var queryString = 'searchTerm='+localURL[0];
		} else {
			var queryString = 'ns='+localURL[0]+'&title='+localURL[1];
		}
		
		smwhgLogger.log(selectedText, "STB-OB", "clicked");
		var ontoBrowserSpecialPage = wgArticlePath.replace(/\$1/, path+'?'+queryString);
		window.open(wgServer + ontoBrowserSpecialPage, "");
	}


}

// create instance of contributor and register on load event so that the complete document is available
// when registerContributor is executed.
var obContributor = new OBSemanticToolbarContributor();
Event.observe(window, 'load', obContributor.registerContributor.bind(obContributor));




