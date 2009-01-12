
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


/* Wlater Zorn Tooltip License

This notice must be untouched at all times.

wz_tooltip.js	 v. 4.12

The latest version is available at
http://www.walterzorn.com
or http://www.devira.com
or http://www.walterzorn.de

Copyright (c) 2002-2007 Walter Zorn. All rights reserved.
Created 1.12.2002 by Walter Zorn (Web: http://www.walterzorn.com )
Last modified: 13.7.2007

Easy-to-use cross-browser tooltips.
Just include the script at the beginning of the <body> section, and invoke
Tip('Tooltip text') from within the desired HTML onmouseover eventhandlers.
No container DIV, no onmouseouts required.
By default, width of tooltips is automatically adapted to content.
Is even capable of dynamically converting arbitrary HTML elements to tooltips
by calling TagToTip('ID_of_HTML_element_to_be_converted') instead of Tip(),
which means you can put important, search-engine-relevant stuff into tooltips.
Appearance of tooltips can be individually configured
via commands passed to Tip() or TagToTip().

Tab Width: 4
LICENSE: LGPL

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License (LGPL) as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

For more details on the GNU Lesser General Public License,
see http://www.gnu.org/copyleft/lesser.html
*/


/*
SMWHalo/skins/QueryInterface/Images/add.png and
SMWHalo/skins/QueryInterface/Images/delete.png

are taken from the Silk Icon Set 1.3

Silk icon set 1.3
_________________________________________
Mark James
http://www.famfamfam.com/lab/icons/silk/
_________________________________________

This work is licensed under a
Creative Commons Attribution 2.5 License.
[ http://creativecommons.org/licenses/by/2.5/ ]

This means you may use it for any purpose,
and make any changes you like.
All I ask is that you include a link back
to this page in your credits.

Are you using this icon set? Send me an email
(including a link or picture if available) to
mjames@gmail.com

Any other questions about this icon set please
contact mjames@gmail.com

*/

/*
SMWHalo/skins/QueryInterface/Images/subquery.png

is part of the Nuvola Icon Set available on
[ http://www.icon-king.com/v2/goodies.php ]
and released under a LGPL License

TITLE:	NUVOLA ICON THEME for KDE 3.x
AUTHOR:	David Vignoni | ICON KING
SITE:	http://www.icon-king.com
MAILING LIST: http://mail.icon-king.com/mailman/listinfo/nuvola_icon-king.com

Copyright (c)  2003-2004  David Vignoni.

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation,
version 2.1 of the License.
This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.
*/
// effects.js
// under MIT-License; Copyright (c) 2005, 2006 Thomas Fuchs
// script.aculo.us effects.js v1.8.0, Tue Nov 06 15:01:40 +0300 2007

// Copyright (c) 2005-2007 Thomas Fuchs (http://script.aculo.us, http://mir.aculo.us)
// Contributors:
//  Justin Palmer (http://encytemedia.com/)
//  Mark Pilgrim (http://diveintomark.org/)
//  Martin Bialasinki
// 
// script.aculo.us is freely distributable under the terms of an MIT-style license.
// For details, see the script.aculo.us web site: http://script.aculo.us/ 

// converts rgb() and #xxx to #xxxxxx format,  
// returns self (or first argument) if not convertable  
String.prototype.parseColor = function() {  
  var color = '#';
  if (this.slice(0,4) == 'rgb(') {  
    var cols = this.slice(4,this.length-1).split(',');  
    var i=0; do { color += parseInt(cols[i]).toColorPart() } while (++i<3);  
  } else {  
    if (this.slice(0,1) == '#') {  
      if (this.length==4) for(var i=1;i<4;i++) color += (this.charAt(i) + this.charAt(i)).toLowerCase();  
      if (this.length==7) color = this.toLowerCase();  
    }  
  }  
  return (color.length==7 ? color : (arguments[0] || this));  
};

/*--------------------------------------------------------------------------*/

Element.collectTextNodes = function(element) {  
  return $A($(element).childNodes).collect( function(node) {
    return (node.nodeType==3 ? node.nodeValue : 
      (node.hasChildNodes() ? Element.collectTextNodes(node) : ''));
  }).flatten().join('');
};

Element.collectTextNodesIgnoreClass = function(element, className) {  
  return $A($(element).childNodes).collect( function(node) {
    return (node.nodeType==3 ? node.nodeValue : 
      ((node.hasChildNodes() && !Element.hasClassName(node,className)) ? 
        Element.collectTextNodesIgnoreClass(node, className) : ''));
  }).flatten().join('');
};

Element.setContentZoom = function(element, percent) {
  element = $(element);  
  element.setStyle({fontSize: (percent/100) + 'em'});   
  if (Prototype.Browser.WebKit) window.scrollBy(0,0);
  return element;
};

Element.getInlineOpacity = function(element){
  return $(element).style.opacity || '';
};

Element.forceRerendering = function(element) {
  try {
    element = $(element);
    var n = document.createTextNode(' ');
    element.appendChild(n);
    element.removeChild(n);
  } catch(e) { }
};

/*--------------------------------------------------------------------------*/

var Effect = {
  _elementDoesNotExistError: {
    name: 'ElementDoesNotExistError',
    message: 'The specified DOM element does not exist, but is required for this effect to operate'
  },
  Transitions: {
    linear: Prototype.K,
    sinoidal: function(pos) {
      return (-Math.cos(pos*Math.PI)/2) + 0.5;
    },
    reverse: function(pos) {
      return 1-pos;
    },
    flicker: function(pos) {
      var pos = ((-Math.cos(pos*Math.PI)/4) + 0.75) + Math.random()/4;
      return pos > 1 ? 1 : pos;
    },
    wobble: function(pos) {
      return (-Math.cos(pos*Math.PI*(9*pos))/2) + 0.5;
    },
    pulse: function(pos, pulses) { 
      pulses = pulses || 5; 
      return (
        ((pos % (1/pulses)) * pulses).round() == 0 ? 
              ((pos * pulses * 2) - (pos * pulses * 2).floor()) : 
          1 - ((pos * pulses * 2) - (pos * pulses * 2).floor())
        );
    },
    spring: function(pos) { 
      return 1 - (Math.cos(pos * 4.5 * Math.PI) * Math.exp(-pos * 6)); 
    },
    none: function(pos) {
      return 0;
    },
    full: function(pos) {
      return 1;
    }
  },
  DefaultOptions: {
    duration:   1.0,   // seconds
    fps:        100,   // 100= assume 66fps max.
    sync:       false, // true for combining
    from:       0.0,
    to:         1.0,
    delay:      0.0,
    queue:      'parallel'
  },
  tagifyText: function(element) {
    var tagifyStyle = 'position:relative';
    if (Prototype.Browser.IE) tagifyStyle += ';zoom:1';
    
    element = $(element);
    $A(element.childNodes).each( function(child) {
      if (child.nodeType==3) {
        child.nodeValue.toArray().each( function(character) {
          element.insertBefore(
            new Element('span', {style: tagifyStyle}).update(
              character == ' ' ? String.fromCharCode(160) : character), 
              child);
        });
        Element.remove(child);
      }
    });
  },
  multiple: function(element, effect) {
    var elements;
    if (((typeof element == 'object') || 
        Object.isFunction(element)) && 
       (element.length))
      elements = element;
    else
      elements = $(element).childNodes;
      
    var options = Object.extend({
      speed: 0.1,
      delay: 0.0
    }, arguments[2] || { });
    var masterDelay = options.delay;

    $A(elements).each( function(element, index) {
      new effect(element, Object.extend(options, { delay: index * options.speed + masterDelay }));
    });
  },
  PAIRS: {
    'slide':  ['SlideDown','SlideUp'],
    'blind':  ['BlindDown','BlindUp'],
    'appear': ['Appear','Fade']
  },
  toggle: function(element, effect) {
    element = $(element);
    effect = (effect || 'appear').toLowerCase();
    var options = Object.extend({
      queue: { position:'end', scope:(element.id || 'global'), limit: 1 }
    }, arguments[2] || { });
    Effect[element.visible() ? 
      Effect.PAIRS[effect][1] : Effect.PAIRS[effect][0]](element, options);
  }
};

Effect.DefaultOptions.transition = Effect.Transitions.sinoidal;

/* ------------- core effects ------------- */

Effect.ScopedQueue = Class.create(Enumerable, {
  initialize: function() {
    this.effects  = [];
    this.interval = null;    
  },
  _each: function(iterator) {
    this.effects._each(iterator);
  },
  add: function(effect) {
    var timestamp = new Date().getTime();
    
    var position = Object.isString(effect.options.queue) ? 
      effect.options.queue : effect.options.queue.position;
    
    switch(position) {
      case 'front':
        // move unstarted effects after this effect  
        this.effects.findAll(function(e){ return e.state=='idle' }).each( function(e) {
            e.startOn  += effect.finishOn;
            e.finishOn += effect.finishOn;
          });
        break;
      case 'with-last':
        timestamp = this.effects.pluck('startOn').max() || timestamp;
        break;
      case 'end':
        // start effect after last queued effect has finished
        timestamp = this.effects.pluck('finishOn').max() || timestamp;
        break;
    }
    
    effect.startOn  += timestamp;
    effect.finishOn += timestamp;

    if (!effect.options.queue.limit || (this.effects.length < effect.options.queue.limit))
      this.effects.push(effect);
    
    if (!this.interval)
      this.interval = setInterval(this.loop.bind(this), 15);
  },
  remove: function(effect) {
    this.effects = this.effects.reject(function(e) { return e==effect });
    if (this.effects.length == 0) {
      clearInterval(this.interval);
      this.interval = null;
    }
  },
  loop: function() {
    var timePos = new Date().getTime();
    for(var i=0, len=this.effects.length;i<len;i++) 
      this.effects[i] && this.effects[i].loop(timePos);
  }
});

Effect.Queues = {
  instances: $H(),
  get: function(queueName) {
    if (!Object.isString(queueName)) return queueName;
    
    return this.instances.get(queueName) ||
      this.instances.set(queueName, new Effect.ScopedQueue());
  }
};
Effect.Queue = Effect.Queues.get('global');

Effect.Base = Class.create({
  position: null,
  start: function(options) {
    function codeForEvent(options,eventName){
      return (
        (options[eventName+'Internal'] ? 'this.options.'+eventName+'Internal(this);' : '') +
        (options[eventName] ? 'this.options.'+eventName+'(this);' : '')
      );
    }
    if (options && options.transition === false) options.transition = Effect.Transitions.linear;
    this.options      = Object.extend(Object.extend({ },Effect.DefaultOptions), options || { });
    this.currentFrame = 0;
    this.state        = 'idle';
    this.startOn      = this.options.delay*1000;
    this.finishOn     = this.startOn+(this.options.duration*1000);
    this.fromToDelta  = this.options.to-this.options.from;
    this.totalTime    = this.finishOn-this.startOn;
    this.totalFrames  = this.options.fps*this.options.duration;
    
    eval('this.render = function(pos){ '+
      'if (this.state=="idle"){this.state="running";'+
      codeForEvent(this.options,'beforeSetup')+
      (this.setup ? 'this.setup();':'')+ 
      codeForEvent(this.options,'afterSetup')+
      '};if (this.state=="running"){'+
      'pos=this.options.transition(pos)*'+this.fromToDelta+'+'+this.options.from+';'+
      'this.position=pos;'+
      codeForEvent(this.options,'beforeUpdate')+
      (this.update ? 'this.update(pos);':'')+
      codeForEvent(this.options,'afterUpdate')+
      '}}');
    
    this.event('beforeStart');
    if (!this.options.sync)
      Effect.Queues.get(Object.isString(this.options.queue) ? 
        'global' : this.options.queue.scope).add(this);
  },
  loop: function(timePos) {
    if (timePos >= this.startOn) {
      if (timePos >= this.finishOn) {
        this.render(1.0);
        this.cancel();
        this.event('beforeFinish');
        if (this.finish) this.finish(); 
        this.event('afterFinish');
        return;  
      }
      var pos   = (timePos - this.startOn) / this.totalTime,
          frame = (pos * this.totalFrames).round();
      if (frame > this.currentFrame) {
        this.render(pos);
        this.currentFrame = frame;
      }
    }
  },
  cancel: function() {
    if (!this.options.sync)
      Effect.Queues.get(Object.isString(this.options.queue) ? 
        'global' : this.options.queue.scope).remove(this);
    this.state = 'finished';
  },
  event: function(eventName) {
    if (this.options[eventName + 'Internal']) this.options[eventName + 'Internal'](this);
    if (this.options[eventName]) this.options[eventName](this);
  },
  inspect: function() {
    var data = $H();
    for(property in this)
      if (!Object.isFunction(this[property])) data.set(property, this[property]);
    return '#<Effect:' + data.inspect() + ',options:' + $H(this.options).inspect() + '>';
  }
});

Effect.Parallel = Class.create(Effect.Base, {
  initialize: function(effects) {
    this.effects = effects || [];
    this.start(arguments[1]);
  },
  update: function(position) {
    this.effects.invoke('render', position);
  },
  finish: function(position) {
    this.effects.each( function(effect) {
      effect.render(1.0);
      effect.cancel();
      effect.event('beforeFinish');
      if (effect.finish) effect.finish(position);
      effect.event('afterFinish');
    });
  }
});

Effect.Tween = Class.create(Effect.Base, {
  initialize: function(object, from, to) {
    object = Object.isString(object) ? $(object) : object;
    var args = $A(arguments), method = args.last(), 
      options = args.length == 5 ? args[3] : null;
    this.method = Object.isFunction(method) ? method.bind(object) :
      Object.isFunction(object[method]) ? object[method].bind(object) : 
      function(value) { object[method] = value };
    this.start(Object.extend({ from: from, to: to }, options || { }));
  },
  update: function(position) {
    this.method(position);
  }
});

Effect.Event = Class.create(Effect.Base, {
  initialize: function() {
    this.start(Object.extend({ duration: 0 }, arguments[0] || { }));
  },
  update: Prototype.emptyFunction
});

Effect.Opacity = Class.create(Effect.Base, {
  initialize: function(element) {
    this.element = $(element);
    if (!this.element) throw(Effect._elementDoesNotExistError);
    // make this work on IE on elements without 'layout'
    if (Prototype.Browser.IE && (!this.element.currentStyle.hasLayout))
      this.element.setStyle({zoom: 1});
    var options = Object.extend({
      from: this.element.getOpacity() || 0.0,
      to:   1.0
    }, arguments[1] || { });
    this.start(options);
  },
  update: function(position) {
    this.element.setOpacity(position);
  }
});

Effect.Move = Class.create(Effect.Base, {
  initialize: function(element) {
    this.element = $(element);
    if (!this.element) throw(Effect._elementDoesNotExistError);
    var options = Object.extend({
      x:    0,
      y:    0,
      mode: 'relative'
    }, arguments[1] || { });
    this.start(options);
  },
  setup: function() {
    this.element.makePositioned();
    this.originalLeft = parseFloat(this.element.getStyle('left') || '0');
    this.originalTop  = parseFloat(this.element.getStyle('top')  || '0');
    if (this.options.mode == 'absolute') {
      this.options.x = this.options.x - this.originalLeft;
      this.options.y = this.options.y - this.originalTop;
    }
  },
  update: function(position) {
    this.element.setStyle({
      left: (this.options.x  * position + this.originalLeft).round() + 'px',
      top:  (this.options.y  * position + this.originalTop).round()  + 'px'
    });
  }
});

// for backwards compatibility
Effect.MoveBy = function(element, toTop, toLeft) {
  return new Effect.Move(element, 
    Object.extend({ x: toLeft, y: toTop }, arguments[3] || { }));
};

Effect.Scale = Class.create(Effect.Base, {
  initialize: function(element, percent) {
    this.element = $(element);
    if (!this.element) throw(Effect._elementDoesNotExistError);
    var options = Object.extend({
      scaleX: true,
      scaleY: true,
      scaleContent: true,
      scaleFromCenter: false,
      scaleMode: 'box',        // 'box' or 'contents' or { } with provided values
      scaleFrom: 100.0,
      scaleTo:   percent
    }, arguments[2] || { });
    this.start(options);
  },
  setup: function() {
    this.restoreAfterFinish = this.options.restoreAfterFinish || false;
    this.elementPositioning = this.element.getStyle('position');
    
    this.originalStyle = { };
    ['top','left','width','height','fontSize'].each( function(k) {
      this.originalStyle[k] = this.element.style[k];
    }.bind(this));
      
    this.originalTop  = this.element.offsetTop;
    this.originalLeft = this.element.offsetLeft;
    
    var fontSize = this.element.getStyle('font-size') || '100%';
    ['em','px','%','pt'].each( function(fontSizeType) {
      if (fontSize.indexOf(fontSizeType)>0) {
        this.fontSize     = parseFloat(fontSize);
        this.fontSizeType = fontSizeType;
      }
    }.bind(this));
    
    this.factor = (this.options.scaleTo - this.options.scaleFrom)/100;
    
    this.dims = null;
    if (this.options.scaleMode=='box')
      this.dims = [this.element.offsetHeight, this.element.offsetWidth];
    if (/^content/.test(this.options.scaleMode))
      this.dims = [this.element.scrollHeight, this.element.scrollWidth];
    if (!this.dims)
      this.dims = [this.options.scaleMode.originalHeight,
                   this.options.scaleMode.originalWidth];
  },
  update: function(position) {
    var currentScale = (this.options.scaleFrom/100.0) + (this.factor * position);
    if (this.options.scaleContent && this.fontSize)
      this.element.setStyle({fontSize: this.fontSize * currentScale + this.fontSizeType });
    this.setDimensions(this.dims[0] * currentScale, this.dims[1] * currentScale);
  },
  finish: function(position) {
    if (this.restoreAfterFinish) this.element.setStyle(this.originalStyle);
  },
  setDimensions: function(height, width) {
    var d = { };
    if (this.options.scaleX) d.width = width.round() + 'px';
    if (this.options.scaleY) d.height = height.round() + 'px';
    if (this.options.scaleFromCenter) {
      var topd  = (height - this.dims[0])/2;
      var leftd = (width  - this.dims[1])/2;
      if (this.elementPositioning == 'absolute') {
        if (this.options.scaleY) d.top = this.originalTop-topd + 'px';
        if (this.options.scaleX) d.left = this.originalLeft-leftd + 'px';
      } else {
        if (this.options.scaleY) d.top = -topd + 'px';
        if (this.options.scaleX) d.left = -leftd + 'px';
      }
    }
    this.element.setStyle(d);
  }
});

Effect.Highlight = Class.create(Effect.Base, {
  initialize: function(element) {
    this.element = $(element);
    if (!this.element) throw(Effect._elementDoesNotExistError);
    var options = Object.extend({ startcolor: '#ffff99' }, arguments[1] || { });
    this.start(options);
  },
  setup: function() {
    // Prevent executing on elements not in the layout flow
    if (this.element.getStyle('display')=='none') { this.cancel(); return; }
    // Disable background image during the effect
    this.oldStyle = { };
    if (!this.options.keepBackgroundImage) {
      this.oldStyle.backgroundImage = this.element.getStyle('background-image');
      this.element.setStyle({backgroundImage: 'none'});
    }
    if (!this.options.endcolor)
      this.options.endcolor = this.element.getStyle('background-color').parseColor('#ffffff');
    if (!this.options.restorecolor)
      this.options.restorecolor = this.element.getStyle('background-color');
    // init color calculations
    this._base  = $R(0,2).map(function(i){ return parseInt(this.options.startcolor.slice(i*2+1,i*2+3),16) }.bind(this));
    this._delta = $R(0,2).map(function(i){ return parseInt(this.options.endcolor.slice(i*2+1,i*2+3),16)-this._base[i] }.bind(this));
  },
  update: function(position) {
    this.element.setStyle({backgroundColor: $R(0,2).inject('#',function(m,v,i){
      return m+((this._base[i]+(this._delta[i]*position)).round().toColorPart()); }.bind(this)) });
  },
  finish: function() {
    this.element.setStyle(Object.extend(this.oldStyle, {
      backgroundColor: this.options.restorecolor
    }));
  }
});

Effect.ScrollTo = function(element) {
  var options = arguments[1] || { },
    scrollOffsets = document.viewport.getScrollOffsets(),
    elementOffsets = $(element).cumulativeOffset(),
    max = (window.height || document.body.scrollHeight) - document.viewport.getHeight();  

  if (options.offset) elementOffsets[1] += options.offset;

  return new Effect.Tween(null,
    scrollOffsets.top,
    elementOffsets[1] > max ? max : elementOffsets[1],
    options,
    function(p){ scrollTo(scrollOffsets.left, p.round()) }
  );
};

/* ------------- combination effects ------------- */

Effect.Fade = function(element) {
  element = $(element);
  var oldOpacity = element.getInlineOpacity();
  var options = Object.extend({
    from: element.getOpacity() || 1.0,
    to:   0.0,
    afterFinishInternal: function(effect) { 
      if (effect.options.to!=0) return;
      effect.element.hide().setStyle({opacity: oldOpacity}); 
    }
  }, arguments[1] || { });
  return new Effect.Opacity(element,options);
};

Effect.Appear = function(element) {
  element = $(element);
  var options = Object.extend({
  from: (element.getStyle('display') == 'none' ? 0.0 : element.getOpacity() || 0.0),
  to:   1.0,
  // force Safari to render floated elements properly
  afterFinishInternal: function(effect) {
    effect.element.forceRerendering();
  },
  beforeSetup: function(effect) {
    effect.element.setOpacity(effect.options.from).show(); 
  }}, arguments[1] || { });
  return new Effect.Opacity(element,options);
};

Effect.Puff = function(element) {
  element = $(element);
  var oldStyle = { 
    opacity: element.getInlineOpacity(), 
    position: element.getStyle('position'),
    top:  element.style.top,
    left: element.style.left,
    width: element.style.width,
    height: element.style.height
  };
  return new Effect.Parallel(
   [ new Effect.Scale(element, 200, 
      { sync: true, scaleFromCenter: true, scaleContent: true, restoreAfterFinish: true }), 
     new Effect.Opacity(element, { sync: true, to: 0.0 } ) ], 
     Object.extend({ duration: 1.0, 
      beforeSetupInternal: function(effect) {
        Position.absolutize(effect.effects[0].element)
      },
      afterFinishInternal: function(effect) {
         effect.effects[0].element.hide().setStyle(oldStyle); }
     }, arguments[1] || { })
   );
};

Effect.BlindUp = function(element) {
  element = $(element);
  element.makeClipping();
  return new Effect.Scale(element, 0,
    Object.extend({ scaleContent: false, 
      scaleX: false, 
      restoreAfterFinish: true,
      afterFinishInternal: function(effect) {
        effect.element.hide().undoClipping();
      } 
    }, arguments[1] || { })
  );
};

Effect.BlindDown = function(element) {
  element = $(element);
  var elementDimensions = element.getDimensions();
  return new Effect.Scale(element, 100, Object.extend({ 
    scaleContent: false, 
    scaleX: false,
    scaleFrom: 0,
    scaleMode: {originalHeight: elementDimensions.height, originalWidth: elementDimensions.width},
    restoreAfterFinish: true,
    afterSetup: function(effect) {
      effect.element.makeClipping().setStyle({height: '0px'}).show(); 
    },  
    afterFinishInternal: function(effect) {
      effect.element.undoClipping();
    }
  }, arguments[1] || { }));
};

Effect.SwitchOff = function(element) {
  element = $(element);
  var oldOpacity = element.getInlineOpacity();
  return new Effect.Appear(element, Object.extend({
    duration: 0.4,
    from: 0,
    transition: Effect.Transitions.flicker,
    afterFinishInternal: function(effect) {
      new Effect.Scale(effect.element, 1, { 
        duration: 0.3, scaleFromCenter: true,
        scaleX: false, scaleContent: false, restoreAfterFinish: true,
        beforeSetup: function(effect) { 
          effect.element.makePositioned().makeClipping();
        },
        afterFinishInternal: function(effect) {
          effect.element.hide().undoClipping().undoPositioned().setStyle({opacity: oldOpacity});
        }
      })
    }
  }, arguments[1] || { }));
};

Effect.DropOut = function(element) {
  element = $(element);
  var oldStyle = {
    top: element.getStyle('top'),
    left: element.getStyle('left'),
    opacity: element.getInlineOpacity() };
  return new Effect.Parallel(
    [ new Effect.Move(element, {x: 0, y: 100, sync: true }), 
      new Effect.Opacity(element, { sync: true, to: 0.0 }) ],
    Object.extend(
      { duration: 0.5,
        beforeSetup: function(effect) {
          effect.effects[0].element.makePositioned(); 
        },
        afterFinishInternal: function(effect) {
          effect.effects[0].element.hide().undoPositioned().setStyle(oldStyle);
        } 
      }, arguments[1] || { }));
};

Effect.Shake = function(element) {
  element = $(element);
  var options = Object.extend({
    distance: 20,
    duration: 0.5
  }, arguments[1] || {});
  var distance = parseFloat(options.distance);
  var split = parseFloat(options.duration) / 10.0;
  var oldStyle = {
    top: element.getStyle('top'),
    left: element.getStyle('left') };
    return new Effect.Move(element,
      { x:  distance, y: 0, duration: split, afterFinishInternal: function(effect) {
    new Effect.Move(effect.element,
      { x: -distance*2, y: 0, duration: split*2,  afterFinishInternal: function(effect) {
    new Effect.Move(effect.element,
      { x:  distance*2, y: 0, duration: split*2,  afterFinishInternal: function(effect) {
    new Effect.Move(effect.element,
      { x: -distance*2, y: 0, duration: split*2,  afterFinishInternal: function(effect) {
    new Effect.Move(effect.element,
      { x:  distance*2, y: 0, duration: split*2,  afterFinishInternal: function(effect) {
    new Effect.Move(effect.element,
      { x: -distance, y: 0, duration: split, afterFinishInternal: function(effect) {
        effect.element.undoPositioned().setStyle(oldStyle);
  }}) }}) }}) }}) }}) }});
};

Effect.SlideDown = function(element) {
  element = $(element).cleanWhitespace();
  // SlideDown need to have the content of the element wrapped in a container element with fixed height!
  var oldInnerBottom = element.down().getStyle('bottom');
  var elementDimensions = element.getDimensions();
  return new Effect.Scale(element, 100, Object.extend({ 
    scaleContent: false, 
    scaleX: false, 
    scaleFrom: window.opera ? 0 : 1,
    scaleMode: {originalHeight: elementDimensions.height, originalWidth: elementDimensions.width},
    restoreAfterFinish: true,
    afterSetup: function(effect) {
      effect.element.makePositioned();
      effect.element.down().makePositioned();
      if (window.opera) effect.element.setStyle({top: ''});
      effect.element.makeClipping().setStyle({height: '0px'}).show(); 
    },
    afterUpdateInternal: function(effect) {
      effect.element.down().setStyle({bottom:
        (effect.dims[0] - effect.element.clientHeight) + 'px' }); 
    },
    afterFinishInternal: function(effect) {
      effect.element.undoClipping().undoPositioned();
      effect.element.down().undoPositioned().setStyle({bottom: oldInnerBottom}); }
    }, arguments[1] || { })
  );
};

Effect.SlideUp = function(element) {
  element = $(element).cleanWhitespace();
  var oldInnerBottom = element.down().getStyle('bottom');
  var elementDimensions = element.getDimensions();
  return new Effect.Scale(element, window.opera ? 0 : 1,
   Object.extend({ scaleContent: false, 
    scaleX: false, 
    scaleMode: 'box',
    scaleFrom: 100,
    scaleMode: {originalHeight: elementDimensions.height, originalWidth: elementDimensions.width},
    restoreAfterFinish: true,
    afterSetup: function(effect) {
      effect.element.makePositioned();
      effect.element.down().makePositioned();
      if (window.opera) effect.element.setStyle({top: ''});
      effect.element.makeClipping().show();
    },  
    afterUpdateInternal: function(effect) {
      effect.element.down().setStyle({bottom:
        (effect.dims[0] - effect.element.clientHeight) + 'px' });
    },
    afterFinishInternal: function(effect) {
      effect.element.hide().undoClipping().undoPositioned();
      effect.element.down().undoPositioned().setStyle({bottom: oldInnerBottom});
    }
   }, arguments[1] || { })
  );
};

// Bug in opera makes the TD containing this element expand for a instance after finish 
Effect.Squish = function(element) {
  return new Effect.Scale(element, window.opera ? 1 : 0, { 
    restoreAfterFinish: true,
    beforeSetup: function(effect) {
      effect.element.makeClipping(); 
    },  
    afterFinishInternal: function(effect) {
      effect.element.hide().undoClipping(); 
    }
  });
};

Effect.Grow = function(element) {
  element = $(element);
  var options = Object.extend({
    direction: 'center',
    moveTransition: Effect.Transitions.sinoidal,
    scaleTransition: Effect.Transitions.sinoidal,
    opacityTransition: Effect.Transitions.full
  }, arguments[1] || { });
  var oldStyle = {
    top: element.style.top,
    left: element.style.left,
    height: element.style.height,
    width: element.style.width,
    opacity: element.getInlineOpacity() };

  var dims = element.getDimensions();    
  var initialMoveX, initialMoveY;
  var moveX, moveY;
  
  switch (options.direction) {
    case 'top-left':
      initialMoveX = initialMoveY = moveX = moveY = 0; 
      break;
    case 'top-right':
      initialMoveX = dims.width;
      initialMoveY = moveY = 0;
      moveX = -dims.width;
      break;
    case 'bottom-left':
      initialMoveX = moveX = 0;
      initialMoveY = dims.height;
      moveY = -dims.height;
      break;
    case 'bottom-right':
      initialMoveX = dims.width;
      initialMoveY = dims.height;
      moveX = -dims.width;
      moveY = -dims.height;
      break;
    case 'center':
      initialMoveX = dims.width / 2;
      initialMoveY = dims.height / 2;
      moveX = -dims.width / 2;
      moveY = -dims.height / 2;
      break;
  }
  
  return new Effect.Move(element, {
    x: initialMoveX,
    y: initialMoveY,
    duration: 0.01, 
    beforeSetup: function(effect) {
      effect.element.hide().makeClipping().makePositioned();
    },
    afterFinishInternal: function(effect) {
      new Effect.Parallel(
        [ new Effect.Opacity(effect.element, { sync: true, to: 1.0, from: 0.0, transition: options.opacityTransition }),
          new Effect.Move(effect.element, { x: moveX, y: moveY, sync: true, transition: options.moveTransition }),
          new Effect.Scale(effect.element, 100, {
            scaleMode: { originalHeight: dims.height, originalWidth: dims.width }, 
            sync: true, scaleFrom: window.opera ? 1 : 0, transition: options.scaleTransition, restoreAfterFinish: true})
        ], Object.extend({
             beforeSetup: function(effect) {
               effect.effects[0].element.setStyle({height: '0px'}).show(); 
             },
             afterFinishInternal: function(effect) {
               effect.effects[0].element.undoClipping().undoPositioned().setStyle(oldStyle); 
             }
           }, options)
      )
    }
  });
};

Effect.Shrink = function(element) {
  element = $(element);
  var options = Object.extend({
    direction: 'center',
    moveTransition: Effect.Transitions.sinoidal,
    scaleTransition: Effect.Transitions.sinoidal,
    opacityTransition: Effect.Transitions.none
  }, arguments[1] || { });
  var oldStyle = {
    top: element.style.top,
    left: element.style.left,
    height: element.style.height,
    width: element.style.width,
    opacity: element.getInlineOpacity() };

  var dims = element.getDimensions();
  var moveX, moveY;
  
  switch (options.direction) {
    case 'top-left':
      moveX = moveY = 0;
      break;
    case 'top-right':
      moveX = dims.width;
      moveY = 0;
      break;
    case 'bottom-left':
      moveX = 0;
      moveY = dims.height;
      break;
    case 'bottom-right':
      moveX = dims.width;
      moveY = dims.height;
      break;
    case 'center':  
      moveX = dims.width / 2;
      moveY = dims.height / 2;
      break;
  }
  
  return new Effect.Parallel(
    [ new Effect.Opacity(element, { sync: true, to: 0.0, from: 1.0, transition: options.opacityTransition }),
      new Effect.Scale(element, window.opera ? 1 : 0, { sync: true, transition: options.scaleTransition, restoreAfterFinish: true}),
      new Effect.Move(element, { x: moveX, y: moveY, sync: true, transition: options.moveTransition })
    ], Object.extend({            
         beforeStartInternal: function(effect) {
           effect.effects[0].element.makePositioned().makeClipping(); 
         },
         afterFinishInternal: function(effect) {
           effect.effects[0].element.hide().undoClipping().undoPositioned().setStyle(oldStyle); }
       }, options)
  );
};

Effect.Pulsate = function(element) {
  element = $(element);
  var options    = arguments[1] || { };
  var oldOpacity = element.getInlineOpacity();
  var transition = options.transition || Effect.Transitions.sinoidal;
  var reverser   = function(pos){ return transition(1-Effect.Transitions.pulse(pos, options.pulses)) };
  reverser.bind(transition);
  return new Effect.Opacity(element, 
    Object.extend(Object.extend({  duration: 2.0, from: 0,
      afterFinishInternal: function(effect) { effect.element.setStyle({opacity: oldOpacity}); }
    }, options), {transition: reverser}));
};

Effect.Fold = function(element) {
  element = $(element);
  var oldStyle = {
    top: element.style.top,
    left: element.style.left,
    width: element.style.width,
    height: element.style.height };
  element.makeClipping();
  return new Effect.Scale(element, 5, Object.extend({   
    scaleContent: false,
    scaleX: false,
    afterFinishInternal: function(effect) {
    new Effect.Scale(element, 1, { 
      scaleContent: false, 
      scaleY: false,
      afterFinishInternal: function(effect) {
        effect.element.hide().undoClipping().setStyle(oldStyle);
      } });
  }}, arguments[1] || { }));
};

Effect.Morph = Class.create(Effect.Base, {
  initialize: function(element) {
    this.element = $(element);
    if (!this.element) throw(Effect._elementDoesNotExistError);
    var options = Object.extend({
      style: { }
    }, arguments[1] || { });
    
    if (!Object.isString(options.style)) this.style = $H(options.style);
    else {
      if (options.style.include(':'))
        this.style = options.style.parseStyle();
      else {
        this.element.addClassName(options.style);
        this.style = $H(this.element.getStyles());
        this.element.removeClassName(options.style);
        var css = this.element.getStyles();
        this.style = this.style.reject(function(style) {
          return style.value == css[style.key];
        });
        options.afterFinishInternal = function(effect) {
          effect.element.addClassName(effect.options.style);
          effect.transforms.each(function(transform) {
            effect.element.style[transform.style] = '';
          });
        }
      }
    }
    this.start(options);
  },
  
  setup: function(){
    function parseColor(color){
      if (!color || ['rgba(0, 0, 0, 0)','transparent'].include(color)) color = '#ffffff';
      color = color.parseColor();
      return $R(0,2).map(function(i){
        return parseInt( color.slice(i*2+1,i*2+3), 16 ) 
      });
    }
    this.transforms = this.style.map(function(pair){
      var property = pair[0], value = pair[1], unit = null;

      if (value.parseColor('#zzzzzz') != '#zzzzzz') {
        value = value.parseColor();
        unit  = 'color';
      } else if (property == 'opacity') {
        value = parseFloat(value);
        if (Prototype.Browser.IE && (!this.element.currentStyle.hasLayout))
          this.element.setStyle({zoom: 1});
      } else if (Element.CSS_LENGTH.test(value)) {
          var components = value.match(/^([\+\-]?[0-9\.]+)(.*)$/);
          value = parseFloat(components[1]);
          unit = (components.length == 3) ? components[2] : null;
      }

      var originalValue = this.element.getStyle(property);
      return { 
        style: property.camelize(), 
        originalValue: unit=='color' ? parseColor(originalValue) : parseFloat(originalValue || 0), 
        targetValue: unit=='color' ? parseColor(value) : value,
        unit: unit
      };
    }.bind(this)).reject(function(transform){
      return (
        (transform.originalValue == transform.targetValue) ||
        (
          transform.unit != 'color' &&
          (isNaN(transform.originalValue) || isNaN(transform.targetValue))
        )
      )
    });
  },
  update: function(position) {
    var style = { }, transform, i = this.transforms.length;
    while(i--)
      style[(transform = this.transforms[i]).style] = 
        transform.unit=='color' ? '#'+
          (Math.round(transform.originalValue[0]+
            (transform.targetValue[0]-transform.originalValue[0])*position)).toColorPart() +
          (Math.round(transform.originalValue[1]+
            (transform.targetValue[1]-transform.originalValue[1])*position)).toColorPart() +
          (Math.round(transform.originalValue[2]+
            (transform.targetValue[2]-transform.originalValue[2])*position)).toColorPart() :
        (transform.originalValue +
          (transform.targetValue - transform.originalValue) * position).toFixed(3) + 
            (transform.unit === null ? '' : transform.unit);
    this.element.setStyle(style, true);
  }
});

Effect.Transform = Class.create({
  initialize: function(tracks){
    this.tracks  = [];
    this.options = arguments[1] || { };
    this.addTracks(tracks);
  },
  addTracks: function(tracks){
    tracks.each(function(track){
      track = $H(track);
      var data = track.values().first();
      this.tracks.push($H({
        ids:     track.keys().first(),
        effect:  Effect.Morph,
        options: { style: data }
      }));
    }.bind(this));
    return this;
  },
  play: function(){
    return new Effect.Parallel(
      this.tracks.map(function(track){
        var ids = track.get('ids'), effect = track.get('effect'), options = track.get('options');
        var elements = [$(ids) || $$(ids)].flatten();
        return elements.map(function(e){ return new effect(e, Object.extend({ sync:true }, options)) });
      }).flatten(),
      this.options
    );
  }
});

Element.CSS_PROPERTIES = $w(
  'backgroundColor backgroundPosition borderBottomColor borderBottomStyle ' + 
  'borderBottomWidth borderLeftColor borderLeftStyle borderLeftWidth ' +
  'borderRightColor borderRightStyle borderRightWidth borderSpacing ' +
  'borderTopColor borderTopStyle borderTopWidth bottom clip color ' +
  'fontSize fontWeight height left letterSpacing lineHeight ' +
  'marginBottom marginLeft marginRight marginTop markerOffset maxHeight '+
  'maxWidth minHeight minWidth opacity outlineColor outlineOffset ' +
  'outlineWidth paddingBottom paddingLeft paddingRight paddingTop ' +
  'right textIndent top width wordSpacing zIndex');
  
Element.CSS_LENGTH = /^(([\+\-]?[0-9\.]+)(em|ex|px|in|cm|mm|pt|pc|\%))|0$/;

String.__parseStyleElement = document.createElement('div');
String.prototype.parseStyle = function(){
  var style, styleRules = $H();
  if (Prototype.Browser.WebKit)
    style = new Element('div',{style:this}).style;
  else {
    String.__parseStyleElement.innerHTML = '<div style="' + this + '"></div>';
    style = String.__parseStyleElement.childNodes[0].style;
  }
  
  Element.CSS_PROPERTIES.each(function(property){
    if (style[property]) styleRules.set(property, style[property]); 
  });
  
  if (Prototype.Browser.IE && this.include('opacity'))
    styleRules.set('opacity', this.match(/opacity:\s*((?:0|1)?(?:\.\d*)?)/)[1]);

  return styleRules;
};

if (document.defaultView && document.defaultView.getComputedStyle) {
  Element.getStyles = function(element) {
    var css = document.defaultView.getComputedStyle($(element), null);
    return Element.CSS_PROPERTIES.inject({ }, function(styles, property) {
      styles[property] = css[property];
      return styles;
    });
  };
} else {
  Element.getStyles = function(element) {
    element = $(element);
    var css = element.currentStyle, styles;
    styles = Element.CSS_PROPERTIES.inject({ }, function(hash, property) {
      hash.set(property, css[property]);
      return hash;
    });
    if (!styles.opacity) styles.set('opacity', element.getOpacity());
    return styles;
  };
};

Effect.Methods = {
  morph: function(element, style) {
    element = $(element);
    new Effect.Morph(element, Object.extend({ style: style }, arguments[2] || { }));
    return element;
  },
  visualEffect: function(element, effect, options) {
    element = $(element)
    var s = effect.dasherize().camelize(), klass = s.charAt(0).toUpperCase() + s.substring(1);
    new Effect[klass](element, options);
    return element;
  },
  highlight: function(element, options) {
    element = $(element);
    new Effect.Highlight(element, options);
    return element;
  }
};

$w('fade appear grow shrink fold blindUp blindDown slideUp slideDown '+
  'pulsate shake puff squish switchOff dropOut').each(
  function(effect) { 
    Effect.Methods[effect] = function(element, options){
      element = $(element);
      Effect[effect.charAt(0).toUpperCase() + effect.substring(1)](element, options);
      return element;
    }
  }
);

$w('getInlineOpacity forceRerendering setContentZoom collectTextNodes collectTextNodesIgnoreClass getStyles').each( 
  function(f) { Effect.Methods[f] = Element[f]; }
);

Element.addMethods(Effect.Methods);


// ontologytools.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
/*  Copyright 2007, ontoprise GmbH
*   Author: Kai Kühn
*   This file is part of the halo-Extension.
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


// commandIDs
var SMW_OB_COMMAND_ADDSUBCATEGORY = 1;
var SMW_OB_COMMAND_ADDSUBCATEGORY_SAMELEVEL = 2;
var SMW_OB_COMMAND_SUBCATEGORY_RENAME = 3;

var SMW_OB_COMMAND_ADDSUBPROPERTY = 4;
var SMW_OB_COMMAND_ADDSUBPROPERTY_SAMELEVEL = 5;
var SMW_OB_COMMAND_SUBPROPERTY_RENAME = 6;

var SMW_OB_COMMAND_INSTANCE_DELETE = 7;
var SMW_OB_COMMAND_INSTANCE_RENAME = 8;

var SMW_OB_COMMAND_ADD_SCHEMAPROPERTY = 9;


// Event types
var OB_SELECTIONLISTENER = 'selectionChanged';
var OB_BEFOREREFRESHLISTENER = 'beforeRefresh';
var OB_REFRESHLISTENER = 'refresh';

/**
 * Event Provider. Supports following events:
 * 
 *  1. selectionChanged
 *  2. refresh
 */
var OBEventProvider = Class.create();
OBEventProvider.prototype = {
    initialize: function() {
        this.listeners = new Array();
    },
    
    /**
     * @public
     * 
     * Adds a listener.
     * 
     * @param listener 
     * @param type
     */
    addListener: function(listener, type) {
        if (this.listeners[type] == null) {
            this.listeners[type] = new Array();
        } 
        if (typeof(listener[type] == 'function')) { 
            this.listeners[type].push(listener);
        }
    },
    
    /**
     * @public
     * 
     * Removes a listener.
     * 
     * @param listener 
     * @param type
     */
    removeListener: function(listener, type) {
        if (this.listeners[type] == null) return;
        this.listeners[type] = this.listeners[type].without(listener);
    },
    
    /**
     * @public
     * 
     * Fires selectionChanged event. The listener method 
     * must have the name 'selectionChanged' with the following
     * signature:
     * 
     * @param id ID of selected element in DOM/XML tree.
     * @param title Title of selected element
     * @param ns namespace
     * @param node in HTML DOM tree.
     */
    fireSelectionChanged: function(id, title, ns, node) {
        this.listeners[OB_SELECTIONLISTENER].each(function (l) { 
            l.selectionChanged(id, title, ns, node);
        });
    },
    
    /**
     * @public
     * 
     * Fires refresh event. The listener method 
     * must have the name 'refresh'
     */
    fireRefresh: function() {
        this.listeners[OB_REFRESHLISTENER].each(function (l) { 
            l.refresh();
        });
    },
    
    fireBeforeRefresh: function() {
        this.listeners[OB_BEFOREREFRESHLISTENER].each(function (l) { 
            l.beforeRefresh();
        });
    }
}   

// create instance of event provider
var selectionProvider = new OBEventProvider();  

/**
 * Class which allows modification of wiki articles
 * via AJAX calls.
 */
var OBArticleCreator = Class.create();
OBArticleCreator.prototype = { 
    initialize: function() {
        this.pendingIndicator = new OBPendingIndicator();
    },
    
    /**
     * @public
     * 
     * Creates an article
     * 
     * @param title Title of article
     * @param content Text which is used when article is created
     * @param optionalText Text which is appended when article already exists.
     * @param creationComment comment
     * @param callback Function called when creation has finished successfully.
     * @param node HTML node used for displaying a pending indicator.
     */
    createArticle : function(title, content, optionalText, creationComment,
                             callback, node) {
        
        function ajaxResponseCreateArticle(request) {
            this.pendingIndicator.hide();
            if (request.status != 200) {
                alert(gLanguage.getMessage('ERROR_CREATING_ARTICLE'));
                return;
            }
            
            var answer = request.responseText;
            var regex = /(true|false),(true|denied|false),(.*)/;
            var parts = answer.match(regex);
            
            if (parts == null) {
                alert(gLanguage.getMessage('ERROR_CREATING_ARTICLE'));
                return;
            }
            
            var success = parts[1];
            var created = parts[2];
            var title = parts[3];
            
            if (success == "true") {
                callback(success, created, title);
            } else {
                if (created == 'denied') {
                    var msg = gLanguage.getMessage('smw_acl_create_denied').replace(/\$1/g, title);
                    alert(msg);
                } else {                
                    alert(gLanguage.getMessage('ERROR_CREATING_ARTICLE'));
                }
            }
        }
        this.pendingIndicator.show(node);
        sajax_do_call('smwf_om_CreateArticle', 
                      [title, wgUserName, content, optionalText, creationComment], 
                      ajaxResponseCreateArticle.bind(this));
                      
    },
    
    /**
     * @public
     * 
     * Deletes an article
     * 
     * @param title Title of article
     * @param reason reason
     * @param callback Function called when creation has finished successfully.
     * @param node HTML node used for displaying a pending indicator.
     */
    deleteArticle: function(title, reason, callback, node) {
        
        function ajaxResponseDeleteArticle(request) {
            this.pendingIndicator.hide();
            if (request.status != 200) {
                alert(gLanguage.getMessage('ERROR_DELETING_ARTICLE'));
                return;
            }
                    
            if (request.responseText.indexOf('true') != -1) {       
                callback();
            } else {
                if (request.responseText.indexOf('denied') != -1) {
                    var msg = gLanguage.getMessage('smw_acl_delete_denied').replace(/\$1/g, title);
                    alert(msg);
                } else {                
                    alert(gLanguage.getMessage('ERROR_DELETING_ARTICLE'));
                }
            }
            
        }
        
        this.pendingIndicator.show(node);
        sajax_do_call('smwf_om_DeleteArticle', 
                      [title, wgUserName, reason], 
                      ajaxResponseDeleteArticle.bind(this));
    },
    
    /**
     * @public
     * 
     * Renames an article
     * 
     * @param oldTitle Old title of article
     * @param newTitle New title of article
     * @param reason string
     * @param callback Function called when creation has finished successfully.
     * @param node HTML node used for displaying a pending indicator.
     */
    renameArticle: function(oldTitle, newTitle, reason, callback, node) {
        
        function ajaxResponseRenameArticle(request) {
            this.pendingIndicator.hide();
            if (request.status != 200) {
                alert(gLanguage.getMessage('ERROR_RENAMING_ARTICLE'));
                return;
            }
            if (request.responseText.indexOf('true') != -1) {       
                callback();
            } else {
                if (request.responseText.indexOf('denied') != -1) {
                    var msg = gLanguage.getMessage('smw_acl_delete_denied').replace(/\$1/g, oldTitle);
                    alert(msg);
                } else {                
                    alert(gLanguage.getMessage('ERROR_RENAMING_ARTICLE'));
                }
            }
            
        }
        
        this.pendingIndicator.show(node);
        sajax_do_call('smwf_om_RenameArticle', 
                      [oldTitle, newTitle, reason, wgUserName], 
                      ajaxResponseRenameArticle.bind(this));
    },
    
    moveCategory: function(draggedCategory, oldSuperCategory, newSuperCategory, callback, node) {
        function ajaxResponseMoveCategory(request) {
            this.pendingIndicator.hide();
            if (request.status != 200) {
                alert(gLanguage.getMessage('ERROR_MOVING_CATEGORY'));
                return;
            }
            if (request.responseText.indexOf('true') == -1) {
                alert('Some error occured on category dragging!');
                return;
            }       
            if (request.responseText.indexOf('true') != -1) {       
                callback();
            } else {
                alert(gLanguage.getMessage('ERROR_MOVING_CATEGORY'));
            }
            
        }
        
        this.pendingIndicator.show(node);
        sajax_do_call('smwf_om_MoveCategory', 
                      [draggedCategory, oldSuperCategory, newSuperCategory], 
                      ajaxResponseMoveCategory.bind(this));
    },
    
    moveProperty: function(draggedProperty, oldSuperProperty, newSuperProperty, callback, node) {
        function ajaxResponseMoveProperty(request) {
            this.pendingIndicator.hide();
            if (request.status != 200) {
                alert(gLanguage.getMessage('ERROR_MOVING_PROPERTY'));
                return;
            }
            if (request.responseText.indexOf('true') == -1) {
                alert('Some error occured on property dragging!');
                return;
            }       
            if (request.responseText.indexOf('true') != -1) {       
                callback();
            } else {
                alert(gLanguage.getMessage('ERROR_MOVING_PROPERTY'));
            }
            
        }
        
        this.pendingIndicator.show(node);
        sajax_do_call('smwf_om_MoveProperty', 
                      [draggedProperty, oldSuperProperty, newSuperProperty], 
                      ajaxResponseMoveProperty.bind(this));
    }
    
}
var articleCreator = new OBArticleCreator();

/**
 * Modifies the wiki ontology and internal OB model.
 */
var OBOntologyModifier = Class.create();
OBOntologyModifier.prototype = { 
    initialize: function() {
        this.date = new Date();
        this.count = 0;
    },
    
    /**
     * @public
     * 
     * Adds a new subcategory
     * 
     * @param subCategoryTitle Title of new subcategory (must not exist!)
     * @param superCategoryTitle Title of supercategory
     * @param superCategoryID ID of supercategory in OB data model (XML)
     */
    addSubcategory: function(subCategoryTitle, superCategoryTitle, superCategoryID) {
        function callback() {
            var subCategoryXML = GeneralXMLTools.createDocumentFromString(this.createCategoryNode(subCategoryTitle));
            this.insertCategoryNode(superCategoryID, subCategoryXML);
            selectionProvider.fireBeforeRefresh();
            transformer.transformXMLToHTML(dataAccess.OB_cachedCategoryTree, $('categoryTree'), true);
            
            selectionProvider.fireSelectionChanged(superCategoryID, superCategoryTitle, SMW_CATEGORY_NS, $(superCategoryID))
            selectionProvider.fireRefresh();
        }
        articleCreator.createArticle(gLanguage.getMessage('CATEGORY_NS')+subCategoryTitle,  
                               "[["+gLanguage.getMessage('CATEGORY_NS')+superCategoryTitle+"]]", '',
                               gLanguage.getMessage('CREATE_SUB_CATEGORY'), callback.bind(this), $(superCategoryID));
    },
    
    /**
     * @public
     * 
     * Adds a new category as sibling of another.
     * 
     * @param newCategoryTitle Title of new category (must not exist!)
     * @param siblingCategoryTitle Title of sibling
     * @param sibligCategoryID ID of siblig category in OB data model (XML)
     */
    addSubcategoryOnSameLevel: function(newCategoryTitle, siblingCategoryTitle, sibligCategoryID) {
        function callback() {
            var newCategoryXML = GeneralXMLTools.createDocumentFromString(this.createCategoryNode(newCategoryTitle));
            var superCategoryID = GeneralXMLTools.getNodeById(dataAccess.OB_cachedCategoryTree, sibligCategoryID).parentNode.getAttribute('id');
            this.insertCategoryNode(superCategoryID, newCategoryXML);
            selectionProvider.fireBeforeRefresh();
            transformer.transformXMLToHTML(dataAccess.OB_cachedCategoryTree, $('categoryTree'), true);
            
            selectionProvider.fireSelectionChanged(sibligCategoryID, siblingCategoryTitle, SMW_CATEGORY_NS, $(sibligCategoryID))
            selectionProvider.fireRefresh();
        }
        var superCategoryTitle = GeneralXMLTools.getNodeById(dataAccess.OB_cachedCategoryTree, sibligCategoryID).parentNode.getAttribute('title');
        var content = superCategoryTitle != null ? "[["+gLanguage.getMessage('CATEGORY_NS')+superCategoryTitle+"]]" : "";
        articleCreator.createArticle(gLanguage.getMessage('CATEGORY_NS')+newCategoryTitle, content, '',
                               gLanguage.getMessage('CREATE_SUB_CATEGORY'), callback.bind(this), $(sibligCategoryID));
    },
    
    /**
     * @public
     * 
     * Renames a category.
     * 
     * @param newCategoryTitle New category title
     * @param categoryTitle Old category title
     * @param categoryID ID of category in OB data model (XML)
     */
    renameCategory: function(newCategoryTitle, categoryTitle, categoryID) {
        function callback() {
            this.renameCategoryNode(categoryID, newCategoryTitle);
            selectionProvider.fireBeforeRefresh();
            transformer.transformXMLToHTML(dataAccess.OB_cachedCategoryTree, $('categoryTree'), true);
            
            selectionProvider.fireSelectionChanged(categoryID, newCategoryTitle, SMW_CATEGORY_NS, $(categoryID))
            selectionProvider.fireRefresh();
        }
        articleCreator.renameArticle(gLanguage.getMessage('CATEGORY_NS')+categoryTitle, gLanguage.getMessage('CATEGORY_NS')+newCategoryTitle, "OB", callback.bind(this), $(categoryID));
    },
    
    /**
     * Move category so that draggedCategoryID is a new subcategory of droppedCategoryID
     * 
     * @param draggedCategoryID ID of category which is moved.
     * @param droppedCategoryID ID of new supercategory of draggedCategory.
     */
    moveCategory: function(draggedCategoryID, droppedCategoryID) {
        
        var from_cache = GeneralXMLTools.getNodeById(dataAccess.OB_cachedCategoryTree, draggedCategoryID);
        // categoryTreeSwitch allows dropping on root level
        var to_cache = droppedCategoryID == 'categoryTreeSwitch' ? dataAccess.OB_cachedCategoryTree.documentElement : GeneralXMLTools.getNodeById(dataAccess.OB_cachedCategoryTree, droppedCategoryID);
        
        var from = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, draggedCategoryID);
        var to = droppedCategoryID == 'categoryTreeSwitch' ? dataAccess.OB_currentlyDisplayedTree.documentElement : GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, droppedCategoryID);
        
        var draggedCategory = from_cache.getAttribute('title');
        var oldSuperCategory = from_cache.parentNode.getAttribute('title');
        var newSuperCategory = to_cache.getAttribute('title');
        
                
        function callback() {
            // only move subtree, if it has already been requested.
            // If expanded is true, it must have been requested. Otherwise it may have been requested but is now collapsed. Then it contains child elements
            if (to_cache.getAttribute('expanded') == 'true' || GeneralXMLTools.hasChildNodesWithTag(to_cache, 'conceptTreeElement')) { 
                to_cache.removeAttribute("isLeaf");
                to.removeAttribute("isLeaf");
                GeneralXMLTools.importNode(to_cache, from_cache, true);
                GeneralXMLTools.importNode(to, from, true);
            }
            
            from.parentNode.removeChild(from);
            from_cache.parentNode.removeChild(from_cache);
            
            selectionProvider.fireBeforeRefresh();
            transformer.transformXMLToHTML(dataAccess.OB_cachedCategoryTree, $('categoryTree'), true);
            
            selectionProvider.fireSelectionChanged(null, null, SMW_CATEGORY_NS, null);
            selectionProvider.fireRefresh();
        }
        articleCreator.moveCategory(draggedCategory, oldSuperCategory, newSuperCategory, callback.bind(this), $('categoryTree'));
    },
    
    /**
     * Move property so that draggedPropertyID is a new subproperty of droppedPropertyID
     * 
     * @param draggedPropertyID ID of property which is moved.
     * @param droppedPropertyID ID of new superproperty of draggedProperty.
     */
    moveProperty: function(draggedPropertyID, droppedPropertyID) {
        
        var from_cache = GeneralXMLTools.getNodeById(dataAccess.OB_cachedPropertyTree, draggedPropertyID);
        var to_cache = droppedPropertyID == 'propertyTreeSwitch' ? dataAccess.OB_cachedPropertyTree.documentElement : GeneralXMLTools.getNodeById(dataAccess.OB_cachedPropertyTree, droppedPropertyID);
        
        var from = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, draggedPropertyID);
        var to = droppedPropertyID == 'propertyTreeSwitch' ? dataAccess.OB_currentlyDisplayedTree.documentElement : GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, droppedPropertyID);
        
        var draggedProperty = from_cache.getAttribute('title');
        var oldSuperProperty = from_cache.parentNode.getAttribute('title');
        var newSuperProperty = to_cache.getAttribute('title');
        
        function callback() {
            // only move subtree, if it has already been requested 
            // If expanded is true, it must have been requested. Otherwise it may have been requested but is now collapsed. Then it contains child elements
            if (to_cache.getAttribute('expanded') == 'true' || GeneralXMLTools.hasChildNodesWithTag(to_cache, 'propertyTreeElement')) { 
                to_cache.removeAttribute("isLeaf");
                to.removeAttribute("isLeaf");
                GeneralXMLTools.importNode(to_cache, from_cache, true);
                GeneralXMLTools.importNode(to, from, true);
            }
            
            from.parentNode.removeChild(from);
            from_cache.parentNode.removeChild(from_cache);
            
            selectionProvider.fireBeforeRefresh();
            transformer.transformXMLToHTML(dataAccess.OB_cachedPropertyTree, $('propertyTree'), true);
            
            selectionProvider.fireSelectionChanged(null, null, SMW_PROPERTY_NS, null);
            selectionProvider.fireRefresh();
        }
        articleCreator.moveProperty(draggedProperty, oldSuperProperty, newSuperProperty, callback.bind(this), $('propertyTree'));
    },
    
    /**
     * @public
     * 
     * Adds a new subproperty
     * 
     * @param subPropertyTitle Title of new subproperty (must not exist!)
     * @param superPropertyTitle Title of superproperty
     * @param superPropertyID ID of superproperty in OB data model (XML)
     */
    addSubproperty: function(subPropertyTitle, superPropertyTitle, superPropertyID) {
        function callback() {
            var subPropertyXML = GeneralXMLTools.createDocumentFromString(this.createPropertyNode(subPropertyTitle));
            this.insertPropertyNode(superPropertyID, subPropertyXML);
            selectionProvider.fireBeforeRefresh();
            transformer.transformXMLToHTML(dataAccess.OB_cachedPropertyTree, $('propertyTree'), true);
            
            selectionProvider.fireSelectionChanged(superPropertyID, superPropertyTitle, SMW_PROPERTY_NS, $(superPropertyID))
            selectionProvider.fireRefresh();
        }
        articleCreator.createArticle(gLanguage.getMessage('PROPERTY_NS')+subPropertyTitle, '',   
                                "\n[[_SUBP::"+gLanguage.getMessage('PROPERTY_NS')+superPropertyTitle+"]]",
                             gLanguage.getMessage('CREATE_SUB_PROPERTY'), callback.bind(this), $(superPropertyID));
    },
    
    /**
     * @public
     * 
     * Adds a new property as sibling of another.
     * 
     * @param newPropertyTitle Title of new property (must not exist!)
     * @param siblingPropertyTitle Title of sibling
     * @param sibligPropertyID ID of siblig property in OB data model (XML)
     */
    addSubpropertyOnSameLevel: function(newPropertyTitle, siblingPropertyTitle, sibligPropertyID) {
        function callback() {
            var subPropertyXML = GeneralXMLTools.createDocumentFromString(this.createPropertyNode(newPropertyTitle));
            var superPropertyID = GeneralXMLTools.getNodeById(dataAccess.OB_cachedPropertyTree, sibligPropertyID).parentNode.getAttribute('id');
            this.insertPropertyNode(superPropertyID, subPropertyXML);
            selectionProvider.fireBeforeRefresh();
            transformer.transformXMLToHTML(dataAccess.OB_cachedPropertyTree, $('propertyTree'), true);
        
            selectionProvider.fireSelectionChanged(sibligPropertyID, siblingPropertyTitle, SMW_PROPERTY_NS, $(sibligPropertyID))
            selectionProvider.fireRefresh();
        }
        
        var superPropertyTitle = GeneralXMLTools.getNodeById(dataAccess.OB_cachedPropertyTree, sibligPropertyID).parentNode.getAttribute('title');
        var content = superPropertyTitle != null ? "\n[[_SUBP::"+gLanguage.getMessage('PROPERTY_NS')+superPropertyTitle+"]]" : "";
        articleCreator.createArticle(gLanguage.getMessage('PROPERTY_NS')+newPropertyTitle, '',   
                               content,
                             gLanguage.getMessage('CREATE_SUB_PROPERTY'), callback.bind(this), $(sibligPropertyID));
    },
    
    /**
     * @public
     * 
     * Renames a property.
     * 
     * @param newPropertyTitle New property title
     * @param oldPropertyTitle Old property title
     * @param propertyID ID of property in OB data model (XML)
     */
    renameProperty: function(newPropertyTitle, oldPropertyTitle, propertyID) {
        function callback() {
            this.renamePropertyNode(propertyID, newPropertyTitle);
            selectionProvider.fireBeforeRefresh();
            transformer.transformXMLToHTML(dataAccess.OB_cachedPropertyTree, $('propertyTree'), true);
            
            selectionProvider.fireSelectionChanged(propertyID, newPropertyTitle, SMW_PROPERTY_NS, $(propertyID))
            selectionProvider.fireRefresh();
        }
        articleCreator.renameArticle(gLanguage.getMessage('PROPERTY_NS')+oldPropertyTitle, gLanguage.getMessage('PROPERTY_NS')+newPropertyTitle, "OB", callback.bind(this), $(propertyID));
    },
    
    /**
     * @public
     * 
     * Adds a new property with schema information.
     * 
     * @param propertyTitle Title of property
     * @param minCard Minimum cardinality
     * @param maxCard Maximum cardinality
     * @param rangeOrTypes Array of range categories or types.
     * @param builtinTypes Array of all existing builtin types.
     * @param domainCategoryTitle Title of domain category
     * @param domainCategoryID ID of domain category in OB data model (XML)
     */
    addSchemaProperty: function(propertyTitle, minCard, maxCard, rangeOrTypes, builtinTypes, domainCategoryTitle, domainCategoryID) {
        function callback() {
            var newPropertyXML = GeneralXMLTools.createDocumentFromString(this.createSchemaProperty(propertyTitle, minCard, maxCard, rangeOrTypes, builtinTypes, domainCategoryTitle, domainCategoryID));
            dataAccess.OB_cachedProperties.documentElement.removeAttribute('isEmpty');
            dataAccess.OB_cachedProperties.documentElement.removeAttribute('textToDisplay');
            GeneralXMLTools.importNode(dataAccess.OB_cachedProperties.documentElement, newPropertyXML.documentElement, true);
            selectionProvider.fireBeforeRefresh();
            transformer.transformXMLToHTML(dataAccess.OB_cachedProperties, $('relattributes'), true);
                    
            selectionProvider.fireRefresh();
        }
        
        var content = maxCard != '' ? "\n[[SMW_SSP_HAS_MAX_CARD::"+maxCard+"]]" : "";
        content += minCard != '' ? "\n[[SMW_SSP_HAS_MIN_CARD::"+minCard+"]]" : "";
        
        var rangeTypeStr = "";
        var rangeCategories = new Array();
        for(var i = 0, n = rangeOrTypes.length; i < n; i++) {
            if (builtinTypes.indexOf(rangeOrTypes[i]) != -1) {
                // is type
                rangeTypeStr += gLanguage.getMessage('TYPE_NS')+rangeOrTypes[i]+(i == n-1 ? "" : ";");
            } else {
                rangeTypeStr += gLanguage.getMessage('TYPE_PAGE')+(i == n-1 ? "" : ";");
                rangeCategories.push(rangeOrTypes[i]);
            }
        }
        content += "\n[[_TYPE::"+rangeTypeStr+"]]";
        rangeCategories.each(function(c) { content += "\n[[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT::"+gLanguage.getMessage('CATEGORY_NS')+domainCategoryTitle+"; "+gLanguage.getMessage('CATEGORY_NS')+c+"]]" });
        if (rangeCategories.length == 0) {
            content += "\n[[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT::"+gLanguage.getMessage('CATEGORY_NS')+domainCategoryTitle+"]]";
        }
        articleCreator.createArticle(gLanguage.getMessage('PROPERTY_NS')+propertyTitle, '',   
                               content,
                             gLanguage.getMessage('CREATE_PROPERTY'), callback.bind(this), $(domainCategoryID));
    },
    
    /**
     * @public
     * 
     * Renames an instance.
     * 
     * @param newInstanceTitle
     * @param oldInstanceTitle
     * @param instanceID ID of instance node in OB data model (XML)
     */
    renameInstance: function(newInstanceTitle, oldInstanceTitle, instanceID) {
        function callback() {
            this.renameInstanceNode(newInstanceTitle, instanceID);
            selectionProvider.fireBeforeRefresh();
            transformer.transformXMLToHTML(dataAccess.OB_cachedInstances, $('instanceList'), true);
            
            selectionProvider.fireSelectionChanged(instanceID, newInstanceTitle, SMW_INSTANCE_NS, $(instanceID))
            selectionProvider.fireRefresh();
        }
        articleCreator.renameArticle(oldInstanceTitle, newInstanceTitle, "OB", callback.bind(this), $(instanceID));
    },
    
    /**
     * @public
     * 
     * Deletes an instance.
     * 
     * @param instanceTitle
     * @param instanceID ID of instance node in OB data model (XML)
     */
    deleteInstance: function(instanceTitle, instanceID) {
        function callback() {
            this.deleteInstanceNode(instanceID);
            selectionProvider.fireBeforeRefresh();
            transformer.transformXMLToHTML(dataAccess.OB_cachedInstances, $('instanceList'), true);
            
            selectionProvider.fireSelectionChanged(null, null, SMW_INSTANCE_NS, null)
            selectionProvider.fireRefresh();
        }
        articleCreator.deleteArticle(instanceTitle, "OB", callback.bind(this), $(instanceID));
    },
    
    /**
     * @private 
     * 
     * Creates a conceptTreeElement for internal OB data model (XML)
     */
    createCategoryNode: function(subCategoryTitle) {
        this.count++;
        var categoryTitle_esc = encodeURIComponent(subCategoryTitle);
        categoryTitle_esc = categoryTitle_esc.replace(/%2F/g, "/");
        return '<conceptTreeElement title_url="'+categoryTitle_esc+'" title="'+subCategoryTitle+'" id="ID_'+(this.date.getTime()+this.count)+'" isLeaf="true" expanded="true"/>';
    },
    
    /**
     * @private 
     * 
     * Creates a propertyTreeElement for internal OB data model (XML)
     */
    createPropertyNode: function(subPropertyTitle) {
        this.count++;
        var propertyTitle_esc = encodeURIComponent(subPropertyTitle);
        propertyTitle_esc = propertyTitle_esc.replace(/%2F/g, "/");
        return '<propertyTreeElement title_url="'+propertyTitle_esc+'" title="'+subPropertyTitle+'" id="ID_'+(this.date.getTime()+this.count)+'" isLeaf="true" expanded="true"/>';
    },
    
    /**
     * @private 
     * 
     * Creates a property element for internal OB data model (XML)
     */
    createSchemaProperty: function(propertyTitle, minCard, maxCard, typeRanges, builtinTypes, selectedTitle, selectedID) {
        this.count++;
        rangeTypes = "";
        for(var i = 0, n = typeRanges.length; i < n; i++) {
            if (builtinTypes.indexOf(typeRanges[i]) != -1) {
                // is type
                rangeTypes += '<rangeType>'+typeRanges[i]+'</rangeType>';
            } else {
                rangeTypes += '<rangeType>'+gLanguage.getMessage('TYPE_PAGE')+'</rangeType>';
            }
        }
        minCard = minCard == '' ? '0' : minCard;
        maxCard = maxCard == '' ? '*' : maxCard;
        var propertyTitle_esc = encodeURIComponent(propertyTitle);
        propertyTitle_esc = propertyTitle_esc.replace(/%2F/g, "/");
        return '<property title_url="'+propertyTitle_esc+'" title="'+propertyTitle+'" minCard="'+minCard+'" maxCard="'+maxCard+'">'+rangeTypes+'</property>';
    },
    
    /**
     * @private 
     * 
     * Renames an instance node in internal OB data model (XML)
     */
    renameInstanceNode: function(newInstanceTitle, instanceID) {
        var instanceNode = GeneralXMLTools.getNodeById(dataAccess.OB_cachedInstances, instanceID);
        instanceNode.removeAttribute("title");
        instanceNode.setAttribute("title", newInstanceTitle);
    },
    
    
    /**
     * @private 
     * 
     * Deletes an instance node in internal OB data model (XML)
     */
    deleteInstanceNode: function(instanceID) {
        var instanceNode = GeneralXMLTools.getNodeById(dataAccess.OB_cachedInstances, instanceID);
        instanceNode.parentNode.removeChild(instanceNode);
    },
    
    /**
     * @private 
     * 
     * Inserts a category node in internal OB data model (XML) as subnode of another category node.
     *
     */
    insertCategoryNode: function(superCategoryID, subCategoryXML) {
        var superCategoryNodeCached = superCategoryID != null ? GeneralXMLTools.getNodeById(dataAccess.OB_cachedCategoryTree, superCategoryID) : dataAccess.OB_cachedCategoryTree.documentElement;
        var superCategoryNodeDisplayed = superCategoryID != null ? GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, superCategoryID) : dataAccess.OB_currentlyDisplayedTree.documentElement;
        
        // make sure that supercategory is no leaf anymore and set it to expanded now.
        superCategoryNodeCached.removeAttribute("isLeaf");
        superCategoryNodeCached.setAttribute("expanded", "true");
        superCategoryNodeDisplayed.removeAttribute("isLeaf");
        superCategoryNodeDisplayed.setAttribute("expanded", "true");
        
        // insert in cache and displayed tree
        GeneralXMLTools.importNode(superCategoryNodeCached, subCategoryXML.documentElement, true);
        GeneralXMLTools.importNode(superCategoryNodeDisplayed, subCategoryXML.documentElement, true);
    },
    
    /**
     * @private 
     * 
     * Inserts a property node in internal OB data model (XML) as subnode of another category node.
     *
     */
    insertPropertyNode: function(superpropertyID, subpropertyXML) {
        var superpropertyNodeCached = superpropertyID != null ? GeneralXMLTools.getNodeById(dataAccess.OB_cachedPropertyTree, superpropertyID) : dataAccess.OB_cachedPropertyTree.documentElement;
        var superpropertyNodeDisplayed = superpropertyID != null ? GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, superpropertyID) : dataAccess.OB_currentlyDisplayedTree.documentElement;
        
        // make sure that superproperty is no leaf anymore and set it to expanded now.
        superpropertyNodeCached.removeAttribute("isLeaf");
        superpropertyNodeCached.setAttribute("expanded", "true");
        superpropertyNodeDisplayed.removeAttribute("isLeaf");
        superpropertyNodeDisplayed.setAttribute("expanded", "true");
        
        // insert in cache and displayed tree
        GeneralXMLTools.importNode(superpropertyNodeCached, subpropertyXML.documentElement, true);
        GeneralXMLTools.importNode(superpropertyNodeDisplayed, subpropertyXML.documentElement, true);
    },
    
    /**
     * @private 
     * 
     * Renames a category node in internal OB data model (XML)
     *
     */
    renameCategoryNode: function(categoryID, newCategoryTitle) {
        var categoryNodeCached = GeneralXMLTools.getNodeById(dataAccess.OB_cachedCategoryTree, categoryID);
        var categoryNodeDisplayed = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, categoryID);
        categoryNodeCached.removeAttribute("title");
        categoryNodeDisplayed.removeAttribute("title");
        categoryNodeCached.setAttribute("title", newCategoryTitle); //TODO: escape
        categoryNodeDisplayed.setAttribute("title", newCategoryTitle);
    
    },
    
    /**
     * @private 
     * 
     * Renames a property node in internal OB data model (XML)
     *
     */
    renamePropertyNode: function(propertyID, newPropertyTitle) {
        var propertyNodeCached = GeneralXMLTools.getNodeById(dataAccess.OB_cachedPropertyTree, propertyID);
        var propertyNodeDisplayed = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, propertyID);
        propertyNodeCached.removeAttribute("title");
        propertyNodeDisplayed.removeAttribute("title");
        propertyNodeCached.setAttribute("title", newPropertyTitle); //TODO: escape
        propertyNodeDisplayed.setAttribute("title", newPropertyTitle);
    }
}

// global object for ontology modification
var ontologyTools = new OBOntologyModifier();

/**
 * Input field validator. Provides an automatic validation 
 * triggering after the user finished typing.
 */
var OBInputFieldValidator = Class.create();
OBInputFieldValidator.prototype = {
    
    /**
     * @public
     * Constructor
     * 
     * @param id ID of INPUT field
     * @param isValid Flag if the input field is initially valid.
     * @param control Control object (derived from OBOntologySubMenu)
     * @param validate_fnc Validation function
     */
    initialize: function(id, isValid, control, validate_fnc) {
        this.id = id;
        this.validate_fnc = validate_fnc;
        this.control = control;
        
        this.keyListener = null;
        this.blurListener = null;
        this.istyping = false;
        this.timerRegistered = false;
        
        this.isValid = isValid;
        this.lastValidation = null;
        
        if ($(this.id) != null) this.registerListeners();
    },
    
    OBInputFieldValidator: function(id, isValid, control, validate_fnc) {
        this.id = id;
        this.validate_fnc = validate_fnc;
        this.control = control;
        
        this.keyListener = null;
        this.blurListener = null;
        this.istyping = false;
        this.timerRegistered = false;
        
        this.isValid = isValid;
        this.lastValidation = null;
        
        if ($(this.id) != null) this.registerListeners();
    },
    
    /**
     * @private 
     * Registers some listeners on the INPUT field.
     */
    registerListeners: function() {
        var e = $(this.id);
        this.keyListener = this.onKeyEvent.bindAsEventListener(this);
        this.blurListener = this.onBlurEvent.bindAsEventListener(this);
        Event.observe(e, "keyup",  this.keyListener);
        Event.observe(e, "keydown",  this.keyListener);
        Event.observe(e, "blur",  this.blurListener);
    },
    
    /**
     * @private 
     * De-registers the listeners.
     */
    deregisterListeners: function() {
        var e = $(this.id);
        if (e == null) return;
        Event.stopObserving(e, "keyup", this.keyListener);
        Event.stopObserving(e, "keydown", this.keyListener);
        Event.stopObserving(e, "blur", this.blurListener);
    },
    
    /**
     * @private 
     * 
     * Triggers a timer which starts validation
     * when a certain time has elapsed after the last key
     * was pressed by the user.
     */
    onKeyEvent: function(event) {
            
        this.istyping = true;
        
        /*if (event.keyCode == 27) {
            // ESCAPE was pressed, so close submenu.
            this.control.cancel(); 
            return;
        }*/
        
        if (event.keyCode == 9 || event.ctrlKey || event.altKey || event.keyCode == 18 || event.keyCode == 17 ) {
            // TAB, CONTROL OR ALT was pressed, do nothing
            return;
        }
        
        if ((event.ctrlKey || event.altKey) && event.keyCode == 32) {
            // autoCompletion request, do nothing
            return;
        }
        
        if (event.keyCode >= 37 && event.keyCode <= 40) {
            // cursor keys, do nothing
            return;
        }
        
        if (!this.timerRegistered) {
            this.control.reset(this.id);
            this.timedCallback(this.validate.bind(this));
            this.timerRegistered = true;
        }
        
    },
    
    /**
     * Validate on blur if content has changed since last validation
     */
    onBlurEvent: function(event) {
        if (this.lastValidation != null && this.lastValidation != $F(this.id)) {
            this.validate();
        }
    },
    /**
     * @private
     * 
     * Callback which calls itsself after timeout, if typing continues.
     */
    timedCallback: function(fnc){
        if(this.istyping){
            this.istyping = false;
            var cb = this.timedCallback.bind(this, fnc);
            setTimeout(cb, 1000);
        } else {    
            fnc(this.id);
            this.timerRegistered = false;
            this.istyping = false;
        }
    },
    
    /**
     * @private
     * 
     * Calls validation function and control.enable,
     * if validation has a defined value (true/false)
     */
    validate: function() {
        this.lastValidation = $F(this.id);
        this.isValid = this.validate_fnc(this.id);
        if (this.isValid !== null) {
            this.control.enable(this.isValid, this.id);
        }
    }
    
}

/**
 * Validates if a title exists (or does not exist).
 * 
 */
var OBInputTitleValidator = Class.create();
OBInputTitleValidator.prototype = Object.extend(new OBInputFieldValidator(), {
    
    /**
     * @public
     * Constructor
     * 
     * @param id ID of INPUT element
     * @param ns namespace for which existance is tested.
     * @param mustExist If true, existance is validated. Otherwise non-existance.
     * @param control Control object (derived from OBOntologySubMenu)
     */
    initialize: function(id, ns, mustExist, control) {
        this.OBInputFieldValidator(id, false, control, this._checkIfArticleExists.bind(this));
        this.ns = ns;
        this.mustExist = mustExist;
        this.pendingElement = new OBPendingIndicator();
    },
    
    /**
     * @private 
     * 
     * Checks if article exists and enables/disables command.
     */
    _checkIfArticleExists: function(id) {
        function ajaxResponseExistsArticle (id, request) {
            this.pendingElement.hide();
            var answer = request.responseText;
            var regex = /(true|false)/;
            var parts = answer.match(regex);
            
            // check if title got empty in the meantime
            if ($F(id) == '') {
                this.control.enable(false, id);
                return;
            }
            if (parts == null) {
                // call fails for some reason. Do nothing!
                this.isValid = false;
                this.control.enable( false, id);
                return;
            } else if (parts[0] == 'true') {
                // article exists -> MUST NOT exist
                this.isValid = this.mustExist;
                this.control.enable(this.mustExist, id);
                return;
            } else {
                this.isValid=!this.mustExist;
                this.control.enable(!this.mustExist, id);
                
            }
        };
        
        var pageName = $F(this.id);
        if (pageName == '') {
            this.control.enable(false, this.id);
            return;
        }
        this.pendingElement.show(this.id)
        var pageNameWithNS = this.ns == '' ? pageName : this.ns+":"+pageName;
        sajax_do_call('smwf_om_ExistsArticleIgnoreRedirect', 
                      [pageNameWithNS], 
                      ajaxResponseExistsArticle.bind(this, this.id));
        return null;
    }
    
        
});

/**
 * Base class for OntologyBrowser submenu GUI elements.
 */
var OBOntologySubMenu = Class.create();
OBOntologySubMenu.prototype = { 
    
    /**
     * @param id ID of DIV element containing the menu
     * @param objectname Name of JS object (in order to refer in HTML links to it)
     */
    initialize: function(id, objectname) {
        this.OBOntologySubMenu(id, objectname);
    },
    
    OBOntologySubMenu: function(id, objectname) {
        this.id = id;
        this.objectname = objectname;
                
        this.commandID = null;
        
        
        this.envContainerID = null;
        this.oldHeight = 0;
        
        this.menuOpened = false;
        
        
    },
    /**
     * @public
     * 
     * Shows menu subview.
     * 
     * @param commandID command to execute.
     * @param envContainerID ID of container which contains the menu.
     */
    showContent: function(commandID, envContainerID) {
        if (this.menuOpened) {
            this._cancel();
        }
        this.commandID = commandID;
        this.envContainerID = envContainerID;
        $(this.id).replace(this.getUserDefinedControls());
                      
        // adjust parent container size
        this.oldHeight = $(envContainerID).getHeight();
        this.adjustSize();
        this.setValidators();
        this.setFocus();
        
        this.menuOpened = true;     
    },
    
    /**
     * @public
     * 
     * Adjusts size if menu is modified.
     */
    adjustSize: function() {
        var menuBarHeight = $(this.id).getHeight();
        var newHeight = (this.oldHeight-menuBarHeight-2)+"px";
        $(this.envContainerID).setStyle({ height: newHeight});
        
    },
    
    
    /**
     * @public
     * 
     * Close subview
     */
    _cancel: function() {
            
        // reset height
        var newHeight = (this.oldHeight-2)+"px";
        $(this.envContainerID).setStyle({ height: newHeight});
        
        // remove DIV content
        $(this.id).replace('<div id="'+this.id+'">');
        this.menuOpened = false;
    },
    
    /**
     * @abstract
     * 
     * Set validators for input fields.
     */
    setValidators: function() {
        
    },
    
    /**
     * @abstract 
     * 
     * Returns HTML string with user defined content of th submenu.
     */
    getUserDefinedControls: function() {
        
    },
    
    /**
     * @abstract 
     * 
     * Executes a command
     */
    doCommand: function() {
        
    },
    /**
     * @abstract
     * 
     * Enables or disables a INPUT field and (not necessarily) the command button. 
     */
    enable: function(b, id) {
        // no impl
    },
    
    /**
     * @abstract
     * 
     * Resets a INPUT field and disables (not necessarily) the command button.
     */
    reset: function(id) {
        // no impl
    }
            
}

/**
 * CategoryTree submenu
 */
var OBCatgeorySubMenu = Class.create();
OBCatgeorySubMenu.prototype = Object.extend(new OBOntologySubMenu(), {
    initialize: function(id, objectname) {
        this.OBOntologySubMenu(id, objectname);
    
        this.titleInputValidator = null;
        this.selectedTitle = null;
        this.selectedID = null;
    
        selectionProvider.addListener(this, OB_SELECTIONLISTENER);
    },
    
    selectionChanged: function(id, title, ns, node) {
        if (ns == SMW_CATEGORY_NS) {
            this.selectedTitle = title;
            this.selectedID = id;
            
        }
    },
    
    doCommand: function() {
        switch(this.commandID) {
            case SMW_OB_COMMAND_ADDSUBCATEGORY: {
                ontologyTools.addSubcategory($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
                this.cancel();
                break;
            }
            case SMW_OB_COMMAND_ADDSUBCATEGORY_SAMELEVEL: {
                ontologyTools.addSubcategoryOnSameLevel($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
                this.cancel();
                break;
            }
            case SMW_OB_COMMAND_SUBCATEGORY_RENAME: {
                ontologyTools.renameCategory($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
                this.cancel();
                break;
            }
            default: alert('Unknown command!');
        }
    },
    
    getCommandText: function() {
        switch(this.commandID) {
            case SMW_OB_COMMAND_SUBCATEGORY_RENAME: return 'OB_RENAME';
            case SMW_OB_COMMAND_ADDSUBCATEGORY_SAMELEVEL: // fall through
            case SMW_OB_COMMAND_ADDSUBCATEGORY: return 'OB_CREATE';
            
            default: return 'Unknown command';
        }
        
    },
    
    getUserDefinedControls: function() {
        var titlevalue = this.commandID == SMW_OB_COMMAND_SUBCATEGORY_RENAME ? this.selectedTitle.replace(/_/g, " ") : '';
        return '<div id="'+this.id+'">' +
                    '<div style="display: block; height: 22px;">' +
                    '<input style="display:block; width:45%; float:left" id="'+this.id+'_input_ontologytools" type="text" value="'+titlevalue+'"/>' +
                    '<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">'+gLanguage.getMessage('OB_ENTER_TITLE')+'</span> | ' +
                    '<a onclick="'+this.objectname+'.cancel()">'+gLanguage.getMessage('CANCEL')+'</a>' +
                    (this.commandID == SMW_OB_COMMAND_SUBCATEGORY_RENAME ? ' | <a onclick="'+this.objectname+'.preview()" id="'+this.id+'_preview_ontologytools">'+gLanguage.getMessage('OB_PREVIEW')+'</a>' : '') +
                    '</div>' +
                '<div id="preview_category_tree"/></div>';
    },
    
    setValidators: function() {
        this.titleInputValidator = new OBInputTitleValidator(this.id+'_input_ontologytools', gLanguage.getMessage('CATEGORY_NS_WOC'), false, this);
            
    },
    
    setFocus: function() {
        $(this.id+'_input_ontologytools').focus();  
    },
    
    cancel: function() {
        this.titleInputValidator.deregisterListeners();
        this._cancel();
    },
    
    /**
     * @public
     * 
     * Do preview 
     */
    preview: function() {
        var pendingElement = new OBPendingIndicator();
        pendingElement.show($('preview_category_tree'));
        sajax_do_call('smwf_ob_PreviewRefactoring', [this.selectedTitle, SMW_CATEGORY_NS], this.pastePreview.bind(this, pendingElement));
    },
    
    /**
     * @private
     * 
     * Pastes preview data
     */
    pastePreview: function(pendingElement, request) {
        pendingElement.hide();
        var table = '<table border="0" class="menuBarConceptTree">'+request.responseText+'</table>';
        $('preview_category_tree').innerHTML = table;
        this.adjustSize();
    },
    
    /**
     * @private 
     * 
     * Replaces the command button with an enabled/disabled version.
     * 
     * @param b enable/disable
     * @param errorMessage message string defined in SMW_LanguageXX.js
     */
    enableCommand: function(b, errorMessage) {
        if (b) {
            $(this.id+'_apply_ontologytools').replace('<a style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools" ' +
                        'onclick="'+this.objectname+'.doCommand()">'+gLanguage.getMessage(this.getCommandText())+'</a>');
        } else {
            $(this.id+'_apply_ontologytools').replace('<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">' + 
                        gLanguage.getMessage(errorMessage)+'</span>');
        }
    },
    
    /**
     * @public
     * 
     * Enables or disables an INPUT field and enables or disables command button.
     * 
     * @param enabled/disable
     * @param id ID of input field
     */
    enable: function(b, id) {
        var bg_color = b ? '#0F0' : $F(id) == '' ? '#FFF' : '#F00';
    
        this.enableCommand(b, b ?  this.getCommandText() : $F(id) == '' ? 'OB_ENTER_TITLE': 'OB_TITLE_EXISTS');
        $(id).setStyle({
            backgroundColor: bg_color
        });
        
    },
    
    /**
     * Resets an input field and disables the command button. 
     * 
     * @param id ID of input field
     */
    reset: function(id) {
        this.enableCommand(false, 'OB_ENTER_TITLE');
        $(id).setStyle({
                backgroundColor: '#FFF'
        });
    }
});


/**
 * PropertyTree submenu
 */
var OBPropertySubMenu = Class.create();
OBPropertySubMenu.prototype = Object.extend(new OBOntologySubMenu(), {
    initialize: function(id, objectname) {
        this.OBOntologySubMenu(id, objectname);
        this.selectedTitle = null;
        this.selectedID = null;
        selectionProvider.addListener(this, OB_SELECTIONLISTENER);
    },
    
    selectionChanged: function(id, title, ns, node) {
        if (ns == SMW_PROPERTY_NS) {
            this.selectedTitle = title;
            this.selectedID = id;
        }
    },
    
    doCommand: function() {
        switch(this.commandID) {
            case SMW_OB_COMMAND_ADDSUBPROPERTY: {
                ontologyTools.addSubproperty($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
                this.cancel();
                break;
            }
            case SMW_OB_COMMAND_ADDSUBPROPERTY_SAMELEVEL: {
                ontologyTools.addSubpropertyOnSameLevel($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
                this.cancel();
                break;
            }
            case SMW_OB_COMMAND_SUBPROPERTY_RENAME: {
                ontologyTools.renameProperty($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
                this.cancel();
                break;
            }
            default: alert('Unknown command!');
        }
    },
    
    getCommandText: function() {
        switch(this.commandID) {
            case SMW_OB_COMMAND_SUBPROPERTY_RENAME: return 'OB_RENAME';
            case SMW_OB_COMMAND_ADDSUBPROPERTY_SAMELEVEL: // fall through
            case SMW_OB_COMMAND_ADDSUBPROPERTY: return 'OB_CREATE';
            
            default: return 'Unknown command';
        }
        
    },
    
    getUserDefinedControls: function() {
        var titlevalue = this.commandID == SMW_OB_COMMAND_SUBPROPERTY_RENAME ? this.selectedTitle.replace(/_/g, " ") : '';
        return '<div id="'+this.id+'">' +
                    '<div style="display: block; height: 22px;">' +
                    '<input style="display:block; width:45%; float:left" id="'+this.id+'_input_ontologytools" type="text" value="'+titlevalue+'"/>' +
                    '<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">'+gLanguage.getMessage('OB_ENTER_TITLE')+'</span> | ' +
                    '<a onclick="'+this.objectname+'.cancel()">'+gLanguage.getMessage('CANCEL')+'</a>' +
                    (this.commandID == SMW_OB_COMMAND_SUBPROPERTY_RENAME ? ' | <a onclick="'+this.objectname+'.preview()" id="'+this.id+'_preview_ontologytools">'+gLanguage.getMessage('OB_PREVIEW')+'</a>' : '') +
                '</div>' +  '<div id="preview_property_tree"/></div>';
    },
    
    setValidators: function() {
        this.titleInputValidator = new OBInputTitleValidator(this.id+'_input_ontologytools', gLanguage.getMessage('PROPERTY_NS_WOC'), false, this);
            
    },
    
    setFocus: function() {
        $(this.id+'_input_ontologytools').focus();  
    },
    
    cancel: function() {
        this.titleInputValidator.deregisterListeners();
        
        this._cancel();
    },
    
    preview: function() {
        sajax_do_call('smwf_ob_PreviewRefactoring', [this.selectedTitle, SMW_PROPERTY_NS], this.pastePreview.bind(this));
    },
    
    pastePreview: function(request) {
        var table = '<table border="0" class="menuBarPropertyTree">'+request.responseText+'</table>';
        $('preview_property_tree').innerHTML = table;
        this.adjustSize();
    },
    
    /**
     * @private 
     * 
     * Replaces the command button with an enabled/disabled version.
     * 
     * @param b enable/disable
     * @param errorMessage message string defined in SMW_LanguageXX.js
     */
    enableCommand: function(b, errorMessage) {
        if (b) {
            $(this.id+'_apply_ontologytools').replace('<a style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools" onclick="'+this.objectname+'.doCommand()">'+gLanguage.getMessage(this.getCommandText())+'</a>');
        } else {
            $(this.id+'_apply_ontologytools').replace('<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">'+gLanguage.getMessage(errorMessage)+'</span>');
        }
    },
    
    /**
     * @public
     * 
     * Enables or disables an INPUT field and enables or disables command button.
     * 
     * @param enabled/disable
     * @param id ID of input field
     */
    enable: function(b, id) {
        var bg_color = b ? '#0F0' : $F(id) == '' ? '#FFF' : '#F00';
    
        this.enableCommand(b, b ?  this.getCommandText() : $F(id) == '' ? 'OB_ENTER_TITLE': 'OB_TITLE_EXISTS');
        $(id).setStyle({
            backgroundColor: bg_color
        });
        
    },
    
    /**
     * Resets the input field and disables the command button. 
     * 
     * @param id ID of input field
     */
    reset: function(id) {
        this.enableCommand(false, 'OB_ENTER_TITLE');
        $(id).setStyle({
                backgroundColor: '#FFF'
        });
    }
});

/**
 * Instance list submenu
 */
var OBInstanceSubMenu = Class.create();
OBInstanceSubMenu.prototype = Object.extend(new OBOntologySubMenu(), {
    initialize: function(id, objectname) {
        this.OBOntologySubMenu(id, objectname);
            
        this.selectedTitle = null;
        this.selectedID = null;
        selectionProvider.addListener(this, OB_SELECTIONLISTENER);
    },
    
    selectionChanged: function(id, title, ns, node) {
        if (ns == SMW_INSTANCE_NS) {
            this.selectedTitle = title;
            this.selectedID = id;
        }
    },
    
    doCommand: function(directCommandID) {
        var commandID = directCommandID ? directCommandID : this.commandID
        switch(commandID) {
            
            case SMW_OB_COMMAND_INSTANCE_RENAME: {
                ontologyTools.renameInstance($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
                this.cancel();
                break;
            }
            case SMW_OB_COMMAND_INSTANCE_DELETE: {
                ontologyTools.deleteInstance(this.selectedTitle, this.selectedID);
                this.cancel();
                break;
            }
            default: alert('Unknown command!');
        }
    },
    
    getCommandText: function() {
        switch(this.commandID) {
            
            case SMW_OB_COMMAND_INSTANCE_RENAME: return 'OB_RENAME';
            default: return 'Unknown command';
        }
        
    },
    
    getUserDefinedControls: function() {
        var titlevalue = this.commandID == SMW_OB_COMMAND_INSTANCE_RENAME ? this.selectedTitle.replace(/_/g, " ") : '';
        return '<div id="'+this.id+'">' +
                    '<div style="display: block; height: 22px;">' +
                    '<input style="display:block; width:45%; float:left" id="'+this.id+'_input_ontologytools" type="text" value="'+titlevalue+'"/>' +
                    '<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">'+gLanguage.getMessage('OB_ENTER_TITLE')+'</span> | ' +
                    '<a onclick="'+this.objectname+'.cancel()">'+gLanguage.getMessage('CANCEL')+'</a> | ' +
                    '<a onclick="'+this.objectname+'.preview()" id="'+this.id+'_preview_ontologytools">'+gLanguage.getMessage('OB_PREVIEW')+'</a>' +
                '</div>' +  '<div id="preview_instance_list"/></div>';
    },
    
    setValidators: function() {
        this.titleInputValidator = new OBInputTitleValidator(this.id+'_input_ontologytools', '', false, this);
            
    },
    
    setFocus: function() {
        $(this.id+'_input_ontologytools').focus();  
    },
    
    cancel: function() {
        if (this.commandID == SMW_OB_COMMAND_INSTANCE_RENAME) {
            this.titleInputValidator.deregisterListeners();
            this._cancel();
        }
    },
    
    preview: function() {
        sajax_do_call('smwf_ob_PreviewRefactoring', [this.selectedTitle, SMW_INSTANCE_NS], this.pastePreview.bind(this));
    },
    
    pastePreview: function(request) {
        var table = '<table border="0" class="menuBarInstance">'+request.responseText+'</table>';
        $('preview_instance_list').innerHTML = table;
        this.adjustSize();
    },
    
    /**
     * @private 
     * 
     * Replaces the command button with an enabled/disabled version.
     * 
     * @param b enable/disable
     * @param errorMessage message string defined in SMW_LanguageXX.js
     */
    enableCommand: function(b, errorMessage) {
        if (b) {
            $(this.id+'_apply_ontologytools').replace('<a style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools" onclick="'+this.objectname+'.doCommand()">'+gLanguage.getMessage(this.getCommandText())+'</a>');
        } else {
            $(this.id+'_apply_ontologytools').replace('<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">'+gLanguage.getMessage(errorMessage)+'</span>');
        }
    },
    
    /**
     * @public
     * 
     * Enables or disables an INPUT field and enables or disables command button.
     * 
     * @param enabled/disable
     * @param id ID of input field
     */
    enable: function(b, id) {
        var bg_color = b ? '#0F0' : $F(id) == '' ? '#FFF' : '#F00';
    
        this.enableCommand(b, b ?  this.getCommandText() : $F(id) == '' ? 'OB_ENTER_TITLE': 'OB_TITLE_EXISTS');
        $(id).setStyle({
            backgroundColor: bg_color
        });
        
    },
    
    /**
     * Resets the input field and disables the command button. 
     * 
     * @param id ID of input field
     */
    reset: function(id) {
        this.enableCommand(false, 'OB_ENTER_TITLE');
        $(id).setStyle({
                backgroundColor: '#FFF'
        });
    }
});

/**
 * Schema Property submenu
 */
var OBSchemaPropertySubMenu = Class.create();
OBSchemaPropertySubMenu.prototype = Object.extend(new OBOntologySubMenu(), {
    initialize: function(id, objectname) {
        this.OBOntologySubMenu(id, objectname);
    
        
        this.selectedTitle = null;
        this.selectedID = null;
        
        this.maxCardValidator = null;
        this.minCardValidator = null;
        this.rangeValidators = [];
        
        this.builtinTypes = [];
        this.count = 0;
        
        selectionProvider.addListener(this, OB_SELECTIONLISTENER);
        this.requestTypes();
    },
    
    selectionChanged: function(id, title, ns, node) {
        if (ns == SMW_CATEGORY_NS) {
            this.selectedTitle = title;
            this.selectedID = id;
        }
    },
    
    doCommand: function() {
        switch(this.commandID) {
            
            case SMW_OB_COMMAND_ADD_SCHEMAPROPERTY: {
                var propertyTitle = $F(this.id+'_propertytitle_ontologytools');
                var minCard = $F(this.id+'_minCard_ontologytools');
                var maxCard = $F(this.id+'_maxCard_ontologytools');
                var rangeOrTypes = [];
                for (var i = 0; i < this.count; i++) {
                    if ($('typeRange'+i+'_ontologytools') != null) {
                        rangeOrTypes.push($F('typeRange'+i+'_ontologytools'));
                    }
                }
                ontologyTools.addSchemaProperty(propertyTitle, minCard, maxCard, rangeOrTypes, this.builtinTypes, this.selectedTitle, this.selectedID);
                this.cancel();
                break;
            }
            
            default: alert('Unknown command!');
        }
    },
    
    getCommandText: function() {
        switch(this.commandID) {
            
            case SMW_OB_COMMAND_ADD_SCHEMAPROPERTY: return 'OB_CREATE';
            
            
            default: return 'Unknown command';
        }
        
    },
    
    getUserDefinedControls: function() {
        return '<div id="'+this.id+'">' +
                     '<table class="menuBarProperties"><tr>' +
                        '<td width="60px;">'+gLanguage.getMessage('NAME')+'</td>' +
                        '<td><input id="'+this.id+'_propertytitle_ontologytools" type="text" tabIndex="101"/></td>' +
                    '</tr>' +
                    '<tr>' +
                        '<td width="60px;">'+gLanguage.getMessage('MIN_CARD')+'</td>' +
                        '<td><input id="'+this.id+'_minCard_ontologytools" type="text" size="5" tabIndex="102"/></td>' +
                    '</tr>' +
                    '<tr>' +
                        '<td width="60px;">'+gLanguage.getMessage('MAX_CARD')+'</td>' +
                        '<td><input id="'+this.id+'_maxCard_ontologytools" type="text" size="5" tabIndex="103"/></td>' +
                    '</tr>' +
                '</table>' +
                '<table class="menuBarProperties" id="typesAndRanges"></table>' +
                '<table class="menuBarProperties">' +
                    '<tr>' +
                        '<td><a onclick="'+this.objectname+'.addType()">'+gLanguage.getMessage('ADD_TYPE')+'</a></td>' +
                        '<td><a onclick="'+this.objectname+'.addRange()">'+gLanguage.getMessage('ADD_RANGE')+'</a></td>' +
                    '</tr>' +
                '</table>' + '<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">'+gLanguage.getMessage('OB_ENTER_TITLE')+'</span> | ' +
                    '<a onclick="'+this.objectname+'.cancel()">'+gLanguage.getMessage('CANCEL')+'</a>' +
                '</div>';
    },
    
    setValidators: function() {
        this.titleInputValidator = new OBInputTitleValidator(this.id+'_propertytitle_ontologytools', gLanguage.getMessage('PROPERTY_NS_WOC'), false, this);
        this.maxCardValidator = new OBInputFieldValidator(this.id+'_maxCard_ontologytools', true, this, this.checkMaxCard.bind(this));
        this.minCardValidator = new OBInputFieldValidator(this.id+'_minCard_ontologytools', true, this, this.checkMinCard.bind(this));
        this.rangeValidators = [];
    },
    
    /**
     * @private
     * 
     * Check if max cardinality is an integer > 0
     */
    checkMaxCard: function() {
        var maxCard = $F(this.id+'_maxCard_ontologytools');
        var valid = maxCard == '' || (maxCard.match(/^\d+$/) != null && parseInt(maxCard) > 0) ;
        return valid;
    },
    
    /**
     * @private
     * 
     * Check if min cardinality is an integer >= 0
     */
    checkMinCard: function() {
        var minCard = $F(this.id+'_minCard_ontologytools');
        var valid = minCard == '' || (minCard.match(/^\d+$/) != null && parseInt(minCard) >= 0) ;
        return valid;
    },
    
    
    
    setFocus: function() {
        $(this.id+'_propertytitle_ontologytools').focus();  
    },
    
    cancel: function() {
        this.titleInputValidator.deregisterListeners();
        this.maxCardValidator.deregisterListeners();
        this.minCardValidator.deregisterListeners();
        this.rangeValidators.each(function(e) { if (e!=null) e.deregisterListeners() });
        
        this._cancel();
    },
    
    enable: function(b, id) {
        var bg_color = b ? '#0F0' : $F(id) == '' ? '#FFF' : '#F00';
    
        $(id).setStyle({
            backgroundColor: bg_color
        });
        
        this.enableCommand(this.allIsValid(), this.getCommandText());
        
    },
    
    reset: function(id) {
        this.enableCommand(false, 'OB_CREATE');
        $(id).setStyle({
                backgroundColor: '#FFF'
        });
    },
    
    /**
     * @private 
     * 
     * Replaces the command button with an enabled/disabled version.
     * 
     * @param b enable/disable
     * @param errorMessage message string defined in SMW_LanguageXX.js
     */
    enableCommand: function(b, errorMessage) {
        if (b) {
            $(this.id+'_apply_ontologytools').replace('<a style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools" onclick="'+this.objectname+'.doCommand()">'+gLanguage.getMessage(this.getCommandText())+'</a>');
        } else {
            $(this.id+'_apply_ontologytools').replace('<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">'+gLanguage.getMessage(errorMessage)+'</span>');
        }
    },
    
    /**
     * @abstract
     * 
     * Checks if a INPUTs are valid
     * 
     * @return true/false
     */
    allIsValid: function() {
        var valid =  this.titleInputValidator.isValid && this.maxCardValidator.isValid &&  this.minCardValidator.isValid;
        this.rangeValidators.each(function(e) { if (e!=null) valid &= e.isValid });
        return valid;
    },
    
    /**
     * @private
     * 
     * Requests builtin types from wiki via AJAX call.
     */
    requestTypes: function() {
                
        function fillBuiltinTypesCallback(request) {
            this.builtinTypes = this.builtinTypes.concat(request.responseText.split(","));
            
        }
        
        function fillUserTypesCallback(request) {
            var userTypes = request.responseText.split(",");
            // remove first element
            userTypes.shift();
            this.builtinTypes = this.builtinTypes.concat(userTypes);
        }
        
        sajax_do_call('smwf_tb_GetBuiltinDatatypes', 
                      [], 
                      fillBuiltinTypesCallback.bind(this)); 
        sajax_do_call('smwf_tb_GetUserDatatypes', 
                      [], 
                      fillUserTypesCallback.bind(this));    
    },
    
    /**
     * @private
     * 
     * Creates new type selection box
     * 
     * @return HTML
     */
    newTypeInputBox: function() {
        var toReplace = '<select id="typeRange'+this.count+'_ontologytools" name="types'+this.count+'">';
        for(var i = 1; i < this.builtinTypes.length; i++) {
            toReplace += '<option>'+this.builtinTypes[i]+'</option>';
        }
        toReplace += '</select><img style="cursor: pointer; cursor: hand;" src="'+wgServer+wgScriptPath+'/extensions/SMWHalo/skins/redcross.gif" onclick="'+this.objectname+'.removeTypeOrRange(\'typeRange'+this.count+'_ontologytools\', false)"/>';
    
        return toReplace;
    },
    
    /**
     * @private
     * 
     * Creates new range category selection box with auto-completion.
     * 
     * @return HTML
     */
    newRangeInputBox: function() {
        var toReplace = '<input class="wickEnabled" typeHint="14" type="text" id="typeRange'+this.count+'_ontologytools" tabIndex="'+(this.count+104)+'"/>';
        toReplace += '<img style="cursor: pointer; cursor: hand;" src="'+wgServer+wgScriptPath+'/extensions/SMWHalo/skins/redcross.gif" onclick="'+this.objectname+'.removeTypeOrRange(\'typeRange'+this.count+'_ontologytools\', true)"/>';
        return toReplace;
    },
    
    /**
     * @private 
     * 
     * Adds additional type selection box in typesRange container.
     */
    addType: function() {
        if (this.builtinTypes == null) {
            return;
        }
        // tbody already in DOM?
        var addTo = $('typesAndRanges').firstChild == null ? $('typesAndRanges') : $('typesAndRanges').firstChild;
        var toReplace = $(addTo.appendChild(document.createElement("tr")));
        toReplace.replace('<tr><td width="60px;">Type </td><td>'+this.newTypeInputBox()+'</td></tr>');
        
        this.count++;
        this.adjustSize();
    },
    
    /**
     * @private 
     * 
     * Adds additional range category selection box in typesRange container.
     */
    addRange: function() {
        // tbody already in DOM?
        var addTo = $('typesAndRanges').firstChild == null ? $('typesAndRanges') : $('typesAndRanges').firstChild;
        
        autoCompleter.deregisterAllInputs();
        // create dummy element and replace afterwards
        var toReplace = $(addTo.appendChild(document.createElement("tr")));
        toReplace.replace('<tr><td width="60px;">Range </td><td>'+this.newRangeInputBox()+'</td></tr>');
        autoCompleter.registerAllInputs();
        
        this.rangeValidators[this.count] = (new OBInputTitleValidator('typeRange'+this.count+'_ontologytools', gLanguage.getMessage('CATEGORY_NS_WOC'), true, this));
        this.enable(false, 'typeRange'+this.count+'_ontologytools');
        
        this.count++;
        this.adjustSize();
    },
    
    /**
     * @private 
     * 
     * Removes type or range category selection box from typesRange container.
     */
    removeTypeOrRange: function(id, isRange) {
        
        if (isRange) {      
            // deregisterValidator
            var match = /typeRange(\d+)/;
            var num = match.exec(id)[1];
            this.rangeValidators[num].deregisterListeners();
            this.rangeValidators[num] = null;
        }
        
        var row = $(id);
        while(row.parentNode.getAttribute('id') != 'typesAndRanges') row = row.parentNode;
        // row is tbody element
        row.removeChild($(id).parentNode.parentNode);
        
        this.enableCommand(this.allIsValid(), this.getCommandText());
        this.adjustSize();
    }
});

var obCategoryMenuProvider = new OBCatgeorySubMenu('categoryTreeMenu', 'obCategoryMenuProvider');
var obPropertyMenuProvider = new OBPropertySubMenu('propertyTreeMenu', 'obPropertyMenuProvider');
var obInstanceMenuProvider = new OBInstanceSubMenu('instanceListMenu', 'obInstanceMenuProvider');
var obSchemaPropertiesMenuProvider = new OBSchemaPropertySubMenu('schemaPropertiesMenu', 'obSchemaPropertiesMenuProvider');

// treeview.js
// under GPL-License; (c) 2003-2004 Jean-Michel Garnier (garnierjm@yahoo.fr)

/***************************************************************
*  Copyright notice
*
*  (c) 2003-2004 Jean-Michel Garnier (garnierjm@yahoo.fr)
*  All rights reserved
*
*  This script is part of the phpXplorer project. The phpXplorer project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt distributed with these scripts.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

// ---------------------------------------------------------------------------

// --- Name:    Easy DHTML Treeview                                         --

// --- Original idea by : D.D. de Kerf                  --

// --- Updated by Jean-Michel Garnier, garnierjm@yahoo.fr                   --

// ---------------------------------------------------------------------------

/*
 * Heaviliy modified by Ontoprise 2007
 * Updated by KK
 */
 

var TreeTransformer = Class.create();
TreeTransformer.prototype = {
	initialize: function() {
		
		// initialize root nodes after document has been loaded
		Event.observe(window, 'load', this.initializeTree.bindAsEventListener(this));
	}, 
	initializeTree: function () {
	
	// deactivate not supported browsers
	if (OB_bd.isKonqueror) {
		alert(gLanguage.getMessage('KS_NOT_SUPPORTED'));
		return;
	}
	
	if (OB_bd.isGeckoOrOpera) {
		this.OB_xsltProcessor_gecko = new XSLTProcessor();

  	// Load the xsl file using synchronous (third param is set to false) XMLHttpRequest
  	var myXMLHTTPRequest = new XMLHttpRequest();
  	myXMLHTTPRequest.open("GET", wgServer + wgScriptPath + "/extensions/SMWHalo/skins/OntologyBrowser/treeview.xslt", false);
 		myXMLHTTPRequest.send(null);


  	var xslRef = GeneralXMLTools.createDocumentFromString(myXMLHTTPRequest.responseText);
 
  	// Finally import the .xsl
  	this.OB_xsltProcessor_gecko.importStylesheet(xslRef);
  	this.OB_xsltProcessor_gecko.setParameter(null, "param-img-directory", wgServer + wgScriptPath + "/extensions/SMWHalo/skins/OntologyBrowser/images/");
	this.OB_xsltProcessor_gecko.setParameter(null, "param-wiki-path", wgServer + wgArticlePath );
	this.OB_xsltProcessor_gecko.setParameter(null, "param-ns-concept", gLanguage.getMessage('CATEGORY_NS_WOC','cont'));
	this.OB_xsltProcessor_gecko.setParameter(null, "param-ns-property", gLanguage.getMessage('PROPERTY_NS_WOC','cont'));
  } else if (OB_bd.isIE) {
  
    // create MSIE DOM object
  	var xsl = new ActiveXObject("MSXML2.FreeThreadedDOMDocument");
		xsl.async = false;
		
		// load stylesheet
		xsl.load(wgServer + wgScriptPath + "/extensions/SMWHalo/skins/OntologyBrowser/treeview.xslt");
		
		// create XSLT Processor
		var template = new ActiveXObject("MSXML2.XSLTemplate");
		template.stylesheet = xsl;
		this.OB_xsltProcessor_ie = template.createProcessor();
		this.OB_xsltProcessor_ie.addParameter("param-img-directory", wgServer + wgScriptPath + "/extensions/SMWHalo/skins/OntologyBrowser/images/");
		this.OB_xsltProcessor_ie.addParameter("param-wiki-path", wgServer + wgArticlePath);
		this.OB_xsltProcessor_ie.addParameter("param-ns-concept", gLanguage.getMessage('CATEGORY_NS_WOC','cont'));
		this.OB_xsltProcessor_ie.addParameter("param-ns-property", gLanguage.getMessage('PROPERTY_NS_WOC','cont'));
  }
  
  // call initialize hook
  dataAccess = new OBDataAccess();
  dataAccess.initializeTree(null);
  
  // initialize event listener for FilterBrowser
  var filterBrowserInput = $("FilterBrowserInput");
  Event.observe(filterBrowserInput, "keyup", globalActionListener.filterBrowsing.bindAsEventListener(globalActionListener, false));
},




/*
 Transforms a AJAX request xml output using the predefined stylesheet
  (treeview.xslt) and adds under the given node.
*/
transformResultToHTML: function (request, node, level) {
	if ( request.status != 200 ) {
    		alert("Error: " + request.status + " " + request.statusText + ": " + request.responseText);
				return;
			}
	
  		if (request.responseText == '') {
  			// result is empty;
  		  return;
  		}
  		// parse xml and transform it to HTML
  		if (OB_bd.isGecko) {
  			var parser=new DOMParser();
  			var xmlDoc=parser.parseFromString(request.responseText,"text/xml");
  			this.transformXMLToHTML(xmlDoc, node, level ? level : false);
  			return xmlDoc;
  		} else if (OB_bd.isIE) {
  			var myDocument = new ActiveXObject("Microsoft.XMLDOM") 
    		myDocument.async="false"; 
    		myDocument.loadXML(request.responseText);   
    		this.transformXMLToHTML(myDocument, node, level ? level : false);
    		return myDocument;
  		}
},

/*
 xmlDoc: document to transform
 node: HTML node to add transformed document to
 level: true = root level, otherwise false (only relevant for tree transformations)
*/
transformXMLToHTML: function (xmlDoc, node, level) {
	if (OB_bd.isGecko) {
		// set startDepth parameter. start on root level or below?
 		this.OB_xsltProcessor_gecko.setParameter(null, "startDepth", level ? 1 : 2);
 		
 		// transform, remove all existing and add new generated nodes
  	 	var fragment = this.OB_xsltProcessor_gecko.transformToFragment(xmlDoc, document);
  	 	GeneralXMLTools.removeAllChildNodes(node); 
  	 	node.appendChild(fragment);
  	 	
  	 	// translate XSLT output
  	 	var languageNodes = GeneralXMLTools.getNodeByText(document, '{{');
	    var regex = new RegExp("\{\{(\\w+)\}\}");
		languageNodes.each(function(n) { 
			var vars;
			var text = n.textContent;
			while (vars = regex.exec(text)) { 
				text = text.replace(new RegExp('\{\{'+vars[1]+'\}\}', "g"), gLanguage.getMessage(vars[1]));
			}
			n.textContent = text;
   		});
  		
	} else if (OB_bd.isIE) {
      // set startDepth parameter. start on root level or below?		
	  this.OB_xsltProcessor_ie.addParameter("startDepth", level ? 1 : 2);
	  
      // transform and overwrite with new generated nodes
	  this.OB_xsltProcessor_ie.input = xmlDoc;
      this.OB_xsltProcessor_ie.transform();
      
      // important to prevent memory leaks in IE
      for (var i = 0, n = node.childNodes.length; i < n; i++) {
      	GeneralBrowserTools.purge(node.childNodes[i]);
      }
      
      // translate XSLT output
      var translatedOutput = this.OB_xsltProcessor_ie.output;
      var regex = new RegExp("\{\{(\\w+)\}\}");
  	  var vars;
	  while (vars = regex.exec(translatedOutput)) { 
			translatedOutput = translatedOutput.replace(new RegExp('\{\{'+vars[1]+'\}\}', "g"), gLanguage.getMessage(vars[1]));
	  }
  	  // insert HTML
      node.innerHTML = translatedOutput;
    
  }
}
};

 
// one global tree transformer
var transformer = new TreeTransformer();



// ---------------------------------------------------------------------------











// treeviewActions.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
/*  Copyright 2007, ontoprise GmbH
*   Author: Kai Kühn
*   This file is part of the halo-Extension.
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

/*
 * TreeView actions 
 *  
 * One listener object for each type entity in each container.
 */


/** 
 * Global selection flow arrow states
 * 0 = left to right
 * 1 = right to left
 */
var OB_LEFT_ARROW = 0;
var OB_RIGHT_ARROW = 0;





// Logging on close does not work, because window shuts down. What to do?
//window.onbeforeunload = function() { smwhgLogger.log("", "OB","close"); };
/**
 * 'Abstract' base class for OntologyBrowser trees
 * 
 * Features:
 * 
 * 1. Expansion and collapsing of nodes
 * 2. Reload of tree partitions (i.e. a segment of a tree level)
 * 3. Filtering of nodes on root level.
 * 4. Filtering of nodes showing their place in the hierarchy.
 * 
 */
var OBTreeActionListener = Class.create();
OBTreeActionListener.prototype = {
	initialize: function() {
		this.OB_currentFilter = null;
		
	},
	
	/**
	 * @abstract
	 * 
	 * Will be implemented in subclasses.
	 */
	selectionChanged: function(id, title, ns, node) {
		
	},
	/**
	 * @protected
	 * 
	 * Toggles a tree node expansion.
	 * 
	 * @param event Event which triggered expansion (normally onClick).
	 * @param node Node on which event was triggered.
	 * @param tree Cached tree to update.
	 * @param accessFunc Function which returns children needed for expansion. It has the following signature:
	 * 						accessFunc(xmlNodeID, xmlNodeName, callbackOnExpandForAjax, callBackForCache);
	 * 
	 * @return
	 */
	_toggleExpand: function (event, node, tree, accessFunc) {
	
	// stop event propagation in Gecko and IE
	Event.stop(event);
    // Get the next tag (read the HTML source)
	var nextDIV = node.nextSibling;
	
 	// find the next DIV
	while(nextDIV.nodeName != "DIV") {
		nextDIV = nextDIV.nextSibling;
	}

	// Unfold the branch if it isn't visible
	if (nextDIV.style.display == 'none') {

		// Change the image (if there is an image)
		if (node.childNodes.length > 0) {
			if (node.childNodes.item(0).nodeName == "IMG") {
				node.childNodes.item(0).src = GeneralTools.getImgDirectory(node.childNodes.item(0).src) + "minus.gif";
			}
		}
		
		// get name of category which is about to be expanded
		var xmlNodeName = node.getAttribute("title");
		var xmlNodeID = node.getAttribute("id");
		
		
 		function callbackOnExpandForAjax(request) {
 			OB_tree_pendingIndicator.hide();
	  		var parentNode = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree.firstChild, xmlNodeID);
	  		var parentNodeInCache = GeneralXMLTools.getNodeById(tree.firstChild, xmlNodeID);
	    	if (request.responseText.indexOf('noResult') != -1) {
	    		// hide expand button if category has no subcategories and mark as leaf
	    		node.childNodes.item(0).style.visibility = 'hidden';
	    		parentNode.setAttribute("isLeaf", "true");
	    		parentNodeInCache.setAttribute("isLeaf", "true");
	    		
	    		return;
	    	}
	    	selectionProvider.fireBeforeRefresh();
	  		var subTree = transformer.transformResultToHTML(request,nextDIV);
	  		selectionProvider.fireRefresh();
	  		GeneralXMLTools.importSubtree(parentNode, subTree.firstChild);
	  		GeneralXMLTools.importSubtree(parentNodeInCache, subTree.firstChild);
	  	}
	  	
	  	function callBackForCache(xmlDoc) {
	  		transformer.transformXMLToHTML(xmlDoc, nextDIV, false);
			Element.show(nextDIV);
	  	}
	  
	  // if category has no child nodes, they will be requested
	  if (!nextDIV.hasChildNodes()) {
	  	//call subtree hook
	  	OB_tree_pendingIndicator.show(globalActionListener.activeTreeName);
	  	accessFunc(xmlNodeID, xmlNodeName, callbackOnExpandForAjax, callBackForCache);
	  	
	  }
	
		
		Element.show(nextDIV);
		var parentNode = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree.firstChild, xmlNodeID);
		parentNode.setAttribute("expanded", "true");
		
		var parentNodeInCache = GeneralXMLTools.getNodeById(tree.firstChild, xmlNodeID);
		parentNodeInCache.setAttribute("expanded", "true");
	}

	// Collapse the branch if it IS visible
	else {

		
		Element.hide(nextDIV);
		// Change the image (if there is an image)
		if (node.childNodes.length > 0) {
			if (node.childNodes.item(0).nodeName == "IMG") {
  				node.childNodes.item(0).src = GeneralTools.getImgDirectory(node.childNodes.item(0).src) + "plus.gif";
			}
			var xmlNodeName = node.getAttribute("title");
			var xmlNodeID = node.getAttribute("id");
			
			var parentNode = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree.firstChild, xmlNodeID);
			parentNode.setAttribute("expanded","false");
			
			var parentNodeInCache = GeneralXMLTools.getNodeById(tree.firstChild, xmlNodeID);
			parentNodeInCache.setAttribute("expanded", "false");
		}
		
	}
 	},
 	
 	/**
 	 * @protected
 	 * 
 	 * Requests the next partition of a tree level.
 	 * 
 	 * @param e Event which triggered selection
 	 * @param partitionNodeHTML Selected partition node in DOM.
 	 * @param tree XML Tree associated with selection
 	 * @param accessFunc Function to obtain next partition
 	 * @param treeName Tree ID to update (categoryTree/propertyTree)
 	 * @param calledOnFinish Function which is called when tree has been updated.
 	 */
  _selectNextPartition: function (e, partitionNodeHTML, tree, accessFunc, treeName, calledOnFinish) {
	
	function selectNextPartitionCallback (request) {
		//TODO: check if empty and do nothing in this case.
		OB_tree_pendingIndicator.hide();
		var xmlFragmentForCache = GeneralXMLTools.createDocumentFromString(request.responseText);
		var xmlFragmentForDisplayTree = GeneralXMLTools.createDocumentFromString(request.responseText);
		
		// is it on the root level or not?
		var isRootLevel = parentOfChildrenToReplaceInCache.tagName == 'result';
		
		// determine HTML node to replace
		var htmlNodeToReplace;
		if (isRootLevel) {
			htmlNodeToReplace = document.getElementById(treeName);
			// adjust xml structure, i.e. replace whole tree
			tree = xmlFragmentForCache;
			dataAccess.OB_currentlyDisplayedTree = xmlFragmentForDisplayTree;
		} else {
			// get element node with children to replace
			// one of nextSiblings is DIV element
			htmlNodeToReplace = GeneralBrowserTools.nextDIV(document.getElementById(idOfChildrenToReplace));
			
			// adjust XML structure
			GeneralXMLTools.removeAllChildNodes(parentOfChildrenToReplaceInCache);
			GeneralXMLTools.importSubtree(parentOfChildrenToReplaceInCache, xmlFragmentForCache.firstChild);
			
			GeneralXMLTools.removeAllChildNodes(parentOfChildrenToReplace);
			GeneralXMLTools.importSubtree(parentOfChildrenToReplace, xmlFragmentForDisplayTree.firstChild);
		}
		// transform structure to HTML
		selectionProvider.fireBeforeRefresh();
		transformer.transformXMLToHTML(xmlFragmentForDisplayTree, htmlNodeToReplace, isRootLevel);
		selectionProvider.fireRefresh();
		calledOnFinish(tree);
	}		
	// Identify partition node in XML
	var id = partitionNodeHTML.getAttribute("id");
	var partition = partitionNodeHTML.getAttribute("partitionnum");
	var partitionNodeInCache = GeneralXMLTools.getNodeById(tree, id);
	var partitionNode = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, id);
	
	// Identify parent of partition node
	var parentOfChildrenToReplaceInCache = partitionNodeInCache.parentNode;
	var parentOfChildrenToReplace = partitionNode.parentNode;
	var idOfChildrenToReplace = parentOfChildrenToReplace.getAttribute("id");
	
	// ask for next partition
	
	partition++;

	
	var isRootLevel = parentOfChildrenToReplace.tagName == 'result';
	OB_tree_pendingIndicator.show(globalActionListener.activeTreeName);
	accessFunc(isRootLevel, partition, parentOfChildrenToReplace.getAttribute("title"), selectNextPartitionCallback);
	
	
	},
	
	/**
	 * @protected
	 * 
 	 * Requests the previous partition of a tree level.
 	 * 
 	 * @param e Event which triggered selection
 	 * @param partitionNodeHTML Selected partition node in DOM.
 	 * @param tree XML Tree associated with selection
 	 * @param accessFunc Function to obtain next partition
 	 * @param treeName Tree ID to update (categoryTree/propertyTree)
 	 * @param calledOnFinish Function which is called when tree has been updated.
 	 */
	_selectPreviousPartition: function (e, partitionNodeHTML, tree, accessFunc, treeName, calledOnFinish) {
	
	function selectPreviousPartitionCallback (request) {
		//TODO: check if empty and do nothing in this case.
		OB_tree_pendingIndicator.hide();
		var xmlFragmentForCache = GeneralXMLTools.createDocumentFromString(request.responseText);
		var xmlFragmentForDisplayTree = GeneralXMLTools.createDocumentFromString(request.responseText);
		
		// is it on the root level or not?
		var isRootLevel = parentOfChildrenToReplaceInCache.tagName == 'result';
		
		// determine HTML node to replace
		var htmlNodeToReplace;
		if (isRootLevel) {
			htmlNodeToReplace = document.getElementById(treeName);
			// adjust xml structure, i.e. replace whole tree
			tree = xmlFragmentForCache;
			dataAccess.OB_currentlyDisplayedTree = xmlFragmentForDisplayTree;
		} else {
			// get element node with children to replace
			// nextSibling is DIV element
			htmlNodeToReplace = GeneralBrowserTools.nextDIV(document.getElementById(idOfChildrenToReplace));
			
			// adjust XML structure
			GeneralXMLTools.removeAllChildNodes(parentOfChildrenToReplaceInCache);
			GeneralXMLTools.importSubtree(parentOfChildrenToReplaceInCache, xmlFragmentForCache.firstChild);
			
			GeneralXMLTools.removeAllChildNodes(parentOfChildrenToReplace);
			GeneralXMLTools.importSubtree(parentOfChildrenToReplace, xmlFragmentForDisplayTree.firstChild);
		}
		// transform structure to HTML
		selectionProvider.fireBeforeRefresh();
		transformer.transformXMLToHTML(xmlFragmentForDisplayTree, htmlNodeToReplace, isRootLevel);
		selectionProvider.fireRefresh();
		calledOnFinish(tree);
	}	
	// Identify partition node in XML
	var id = partitionNodeHTML.getAttribute("id");
	var partition = partitionNodeHTML.getAttribute("partitionnum");
	var partitionNodeInCache = GeneralXMLTools.getNodeById(tree, id);
	var partitionNode = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, id);
	
	// Identify parent of partition node
	var parentOfChildrenToReplaceInCache = partitionNodeInCache.parentNode;
	var parentOfChildrenToReplace = partitionNode.parentNode;
	var idOfChildrenToReplace = parentOfChildrenToReplace.getAttribute("id");
	
	// ask for previous partition, stop if already 0
	if (partition == 0) {return;}
	partition--;
	
	
	var isRootLevel = parentOfChildrenToReplace.tagName == 'result';
	OB_tree_pendingIndicator.show(globalActionListener.activeTreeName);
	accessFunc(isRootLevel, partition, parentOfChildrenToReplace.getAttribute("title"), selectPreviousPartitionCallback);
	
  },
  
   /**
    * @protected
    * 
 	* Filter tree to match given term(s)
 	* 
 	* @param e Event
 	* @param tree XML Tree to filter
 	* @param treeName Tree ID
 	* @param filterStr Whitespace separated filter string.
 	*/
  _filterTree: function (e, tree, treeName, filterStr) {
    var xmlDoc = GeneralXMLTools.createTreeViewDocument();
   
   	var nodesFound = new Array();
   	
   	// generate filters
   	var regex = new Array();
    var filterTerms = GeneralTools.splitSearchTerm(filterStr);
    for(var i = 0, n = filterTerms.length; i < n; i++) {
    	try {
	   	 	regex[i] = new RegExp(filterTerms[i],"i");
	   	} catch(e) {
    		// happens when RegExp is invalid. Just do nothing in this case
    		return;
    	}
    }
   	this._filterTree_(nodesFound, tree.firstChild, 0, regex);
   
   	for (var i = 0; i < nodesFound.length; i++) {
   		 var branch = GeneralXMLTools.getAllParents(nodesFound[i]);
   		 GeneralXMLTools.addBranch(xmlDoc.firstChild, branch);
   	}
   	// transform xml and add to category tree DIV 
   	var rootElement = document.getElementById(treeName);
   	selectionProvider.fireBeforeRefresh();
   	transformer.transformXMLToHTML(xmlDoc, rootElement, true);
   	selectionProvider.fireRefresh();
   	if (treeName == 'categoryTree') { 
   		selectionProvider.fireSelectionChanged(null, null, SMW_CATEGORY_NS, null);
   	} else if (treeName == 'propertyTree') {
   		selectionProvider.fireSelectionChanged(null, null, SMW_PROPERTY_NS, null);
   	}
   	dataAccess.OB_currentlyDisplayedTree = xmlDoc;
},

  /**
   * @private
   * 
   * Selects all nodes whose title attribute match the given regex.
   * 
   * @param nodesFound Empty array which takes the returned nodes
   * @param node Node to start with.
   * @param count internal index for node array (starts with 0)
   * @param regex The regular expression 
   */
  _filterTree_: function (nodesFound, node, count, regex) {

	var children = node.childNodes;
	
	if (children) {
   	  for (var i = 0; i < children.length; i++) {
   	  		if (children[i].tagName == 'gissues') continue;
   	    	count = this._filterTree_(nodesFound, children[i], count, regex);
    	
      }
	}
	var title = node.getAttribute("title");
    if (title != null && GeneralTools.matchArrayOfRegExp(title, regex)) {
    	nodesFound[count] = node;
		count++;
    	
	}
	
	return count;
  },
  
  _filterRootLevel: function (e, tree, treeName) {
   if (OB_bd.isIE && e.type != 'click' && e.keyCode != 13) {
   	return;
   }
   if (OB_bd.isGeckoOrOpera && e.type != 'click' && e.which != 13) {
   	return;
   }
   
   xmlDoc = GeneralXMLTools.createTreeViewDocument();
   
   var inputs = document.getElementsByTagName("input");
   this.OB_currentFilter = inputs[0].value;
   //iterate all root categories identifying those which match user input prefix  
   var rootCats = tree.firstChild.childNodes;	
   for (var i = 0; i < rootCats.length; i++) {
   	 	
   	 if (rootCats[i].getAttribute("title")) {
   	 	// filter root nodes which have a title
   	 	if (rootCats[i].getAttribute("title").indexOf(inputs[0].value) != -1) {
   	 		if (rootCats[i].childNodes.length > 0) rootCats[i].setAttribute("expanded", "true");
   	 		
   	 		// add matching root category nodes
   	 		if (OB_bd.isGeckoOrOpera) {
   	 			xmlDoc.firstChild.appendChild(document.importNode(rootCats[i], true));
   	 		} else if (OB_bd.isIE) {
   	 			xmlDoc.firstChild.appendChild(rootCats[i].cloneNode(true));
   	 		}
   	 	}
   	 } else {
   	 	// copy all other nodes
   	 	if (OB_bd.isGeckoOrOpera) {
   	 			xmlDoc.firstChild.appendChild(document.importNode(rootCats[i], true));
   	 		} else if (OB_bd.isIE) {
   	 			xmlDoc.firstChild.appendChild(rootCats[i].cloneNode(true));
   	 		}
   	 }
   }
   
   // transform xml and add to category tree DIV 
   var rootElement = document.getElementById(treeName);
   transformer.transformXMLToHTML(xmlDoc, rootElement, true);
   dataAccess.OB_currentlyDisplayedTree = xmlDoc;
  }
  
  
}

/**
 * Action Listener for categories
 */
var OBCategoryTreeActionListener = Class.create();
OBCategoryTreeActionListener.prototype = Object.extend(new OBTreeActionListener(), {
	initialize: function() {
		
		this.selectedCategory = null;
		this.selectedCategoryID = null;
		this.oldSelectedNode = null;
		this.draggableCategories = [];
		selectionProvider.addListener(this, OB_SELECTIONLISTENER);
		selectionProvider.addListener(this, OB_REFRESHLISTENER);
		selectionProvider.addListener(this, OB_BEFOREREFRESHLISTENER);
		
		this.ignoreNextSelection = false;
		Draggables.addObserver(this);
		Droppables.add('categoryTreeSwitch', {accept:'concept', hoverclass:'dragHover', onDrop:this.onDrop.bind(this)}); 
	},
	
	toggleExpand: function (event, node, folderCode) {
		this._toggleExpand(event, node, dataAccess.OB_cachedCategoryTree, dataAccess.getCategorySubTree.bind(dataAccess));
	},


	navigateToEntity: function(event, node, categoryName, editmode) {
		smwhgLogger.log(categoryName, "OB","inspect_entity");
		GeneralBrowserTools.navigateToPage(gLanguage.getMessage('CATEGORY_NS_WOC'), categoryName, editmode);
	},
	
	selectionChanged: function(id, title, ns, node) {
		if (ns == SMW_CATEGORY_NS) {
			
			this.selectedCategory = title;
			this.selectedCategoryID = id;
			this.oldSelectedNode = GeneralBrowserTools.toggleHighlighting(this.oldSelectedNode, node);
		}
	},
	
	beforeRefresh: function() {
		if (wgUserGroups == null || (wgUserGroups.indexOf('sysop') == -1 && wgUserGroups.indexOf('gardener') == -1)) {
			
			return;
		}
		if (OB_bd.isIE) {
			return; // no DnD in IE
		}
		this.draggableCategories.each(function(c) { 
			c.destroy();
			
		});
		$$('a.concept').each(function(c) { 
			Droppables.remove(c.getAttribute('id'));
		});
		this.draggableCategories = [];
	},
	
	refresh: function() {
		if (wgUserGroups == null || (wgUserGroups.indexOf('sysop') == -1 && wgUserGroups.indexOf('gardener') == -1)) {
			// do not allow dragging, when user is no sysop or gardener
			return;
		}
		if (OB_bd.isIE) {
			return; // do not activate DnD in IE, because scriptaculous is very buggy here
		}
		function addDragAndDrop(c) { 
			var d = new Draggable(c.getAttribute('id'), {revert:true, ghosting:true}); 
			this.draggableCategories.push(d); 
			Droppables.add(c.getAttribute('id'), {accept:'concept', hoverclass:'dragHover', onDrop:onDrop_bind}); 
		}
		var addDragAndDrop_bind = addDragAndDrop.bind(this);
		var onDrop_bind = this.onDrop.bind(this);
		$$('a.concept').each(addDragAndDrop_bind);
		
	},
	
	onStart: function(eventName, draggable, event) {
		if (draggable.element.hasClassName('concept')) {
			this.ignoreNextSelection = true;
		}
	},
	
	onDrop: function(dragElement, dropElement, event) {
		var draggedCategoryID = dragElement.getAttribute('id');
		var droppedCategoryID = dropElement.getAttribute('id');
		//alert('Dropped on: '+droppedCategoryID+" from: "+draggedCategoryID);
		ontologyTools.moveCategory(draggedCategoryID, droppedCategoryID);
	},
	
	showSubMenu: function(commandID) {
		if (this.selectedCategory == null) {
			alert(gLanguage.getMessage('OB_SELECT_CATEGORY'));
			return;
		}
		
		obCategoryMenuProvider.showContent(commandID, 'categoryTree');
	},
	
	
// ---- Selection methods. Called when the entity is selected ---------------------

/**
 * @public
 * 
 * Called when a category has been selected. Do also expand the 
 * category tree if necessary.
 * 
 * @param event Event
 * @param node selected HTML node
 * @param categoryID unique ID of category
 * @param categoryName Title of category
 */
select: function (event, node, categoryID, categoryName) {
	if (this.ignoreNextSelection && OB_bd.isGecko) {
		this.ignoreNextSelection = false;
		return;
	}
	var e = GeneralTools.getEvent(event);
	
	// if Ctrl is pressed: navigation mode
	if (e["ctrlKey"]) {
		GeneralBrowserTools.navigateToPage(gLanguage.getMessage('CATEGORY_NS_WOC'), categoryName);
	} else {
	
	
	var nextDIV = node.nextSibling;
	
 	// find the next DIV
	while(nextDIV.nodeName != "DIV") {
		nextDIV = nextDIV.nextSibling;
	}
	
	// fire selection event
	selectionProvider.fireSelectionChanged(categoryID, categoryName, SMW_CATEGORY_NS, node);
	
	// check if node is already expanded and expand it if not
	if (!nextDIV.hasChildNodes() || nextDIV.style.display == 'none') {
		this.toggleExpand(event, node, categoryID);
	}
		
	var instanceDIV = document.getElementById("instanceList");
	var relattDIV = document.getElementById("relattributes");
	
	
	// adjust relatt table headings
	if (!$("relattRangeType").visible()) {
		$("relattRangeType").show();
		$("relattValues").hide();
	}
	
	smwhgLogger.log(categoryName, "OB","clicked");
	
	// callback for instances of a category
	function callbackOnCategorySelect(request) {
		OB_instance_pendingIndicator.hide();
	  	if (instanceDIV.firstChild) {
	  			GeneralBrowserTools.purge(instanceDIV.firstChild);
				instanceDIV.removeChild(instanceDIV.firstChild);
		}
		
		var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
		dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
		selectionProvider.fireBeforeRefresh();
	  	transformer.transformResultToHTML(request,instanceDIV, true);
	  	selectionProvider.fireRefresh();
	  	// de-select instance list
	  	selectionProvider.fireSelectionChanged(null, null, SMW_INSTANCE_NS, null);
	 }
	 
	 // callback for properties of a category
	 function callbackOnCategorySelect2(request) {
	 	OB_relatt_pendingIndicator.hide();
	  	if (relattDIV.firstChild) {
	  			GeneralBrowserTools.purge(relattDIV.firstChild);
				relattDIV.removeChild(relattDIV.firstChild);
		}
		var xmlFragmentPropertyList = GeneralXMLTools.createDocumentFromString(request.responseText);
		dataAccess.OB_cachedProperties = xmlFragmentPropertyList;
		selectionProvider.fireBeforeRefresh();
	  	transformer.transformResultToHTML(request,relattDIV);
	  	selectionProvider.fireRefresh();
	  	selectionProvider.fireSelectionChanged(null, null, SMW_PROPERTY_NS, null);
	 }
	 
	 
	 if (OB_LEFT_ARROW == 0) {
	 	OB_instance_pendingIndicator.show();
	 	dataAccess.getInstances(categoryName, 0, callbackOnCategorySelect);
	 }
	 if (OB_RIGHT_ARROW == 0) {
	 	OB_relatt_pendingIndicator.show();
	 	var onlyDirect = $('directPropertySwitch').checked;
	 	dataAccess.getProperties(categoryName, onlyDirect, callbackOnCategorySelect2);
	 }
	
	}
},


selectNextPartition: function (e, htmlNode) {
	
	function calledOnFinish(tree) {
		dataAccess.OB_cachedCategoryTree = tree;
		selectionProvider.fireSelectionChanged(null, null, SMW_CATEGORY_NS, null);
	}
	this._selectNextPartition(e, htmlNode, dataAccess.OB_cachedCategoryTree, dataAccess.getCategoryPartition.bind(dataAccess), "categoryTree", calledOnFinish);
	
},

selectPreviousPartition: function (e, htmlNode) {
	
	function calledOnFinish(tree) {
		dataAccess.OB_cachedCategoryTree = tree;
		selectionProvider.fireSelectionChanged(null, null, SMW_CATEGORY_NS, null);
	}
	this._selectPreviousPartition(e, htmlNode, dataAccess.OB_cachedCategoryTree, dataAccess.getCategoryPartition.bind(dataAccess), "categoryTree", calledOnFinish);

}



});


var OBInstanceActionListener = Class.create();
OBInstanceActionListener.prototype = {
	initialize: function() {
		
		this.selectedInstance = null;
		this.oldSelectedInstance = null;
		selectionProvider.addListener(this, OB_SELECTIONLISTENER);
	},
	
	navigateToEntity: function(event, node, instanceName, editmode) {
		smwhgLogger.log(instanceName, "OB","inspect_entity");
		GeneralBrowserTools.navigateToPage(null, instanceName, editmode);
		
	},
	
	selectionChanged: function(id, title, ns, node) {
		if (ns == SMW_INSTANCE_NS) {
			this.selectedInstance = title;
			this.oldSelectedInstance = GeneralBrowserTools.toggleHighlighting(this.oldSelectedInstance, node);

		}
	},
	
	showSubMenu: function(commandID) {
		if (this.selectedInstance == null) {
			alert(gLanguage.getMessage('OB_SELECT_INSTANCE'));
			return;
		}
		if (commandID == SMW_OB_COMMAND_INSTANCE_DELETE) {
			var doDelete = confirm(gLanguage.getMessage('OB_CONFIRM_INSTANCE_DELETION'));
			if (doDelete) obInstanceMenuProvider.doCommand(commandID);
			return;
		}
		obInstanceMenuProvider.showContent(commandID,  'instanceList');
	},
	/**
	 * Called when a supercategory of an instance is selected.
	 */
	showSuperCategory: function(event, node, categoryName) {
		function filterBrowsingCategoryCallback(request) {
	 	var categoryDIV = $("categoryTree");
	 	if (categoryDIV.firstChild) {
	 		GeneralBrowserTools.purge(categoryDIV.firstChild);
			categoryDIV.removeChild(categoryDIV.firstChild);
		}
	  	dataAccess.OB_cachedCategoryTree = GeneralXMLTools.createDocumentFromString(request.responseText);
  		dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(request.responseText, categoryDIV);
	 }
     globalActionListener.switchTreeComponent(null, 'categoryTree', true);
	 sajax_do_call('smwf_ob_OntologyBrowserAccess', ['filterBrowse',"category,"+categoryName], filterBrowsingCategoryCallback);
   	
	},
	
	selectInstance: function (event, node, id, instanceName) {
	
	var e = GeneralTools.getEvent(event);
	
	// if Ctrl is pressed: navigation mode
	if (e["ctrlKey"]) {
		GeneralBrowserTools.navigateToPage(null, instanceName);
	} else {
		// adjust relatt table headings
		if (!$("relattValues").visible()) {
			$("relattValues").show();
			$("relattRangeType").hide();
		}
		
		var relattDIV = $("relattributes");
		var categoryDIV = $('categoryTree');
		
		
		selectionProvider.fireSelectionChanged(id, instanceName, SMW_INSTANCE_NS, node);
		smwhgLogger.log(instanceName, "OB","clicked");
		
		function callbackOnInstanceSelectToRight(request) {
		OB_relatt_pendingIndicator.hide();
	  	if (relattDIV.firstChild) {
	  		  	GeneralBrowserTools.purge(relattDIV.firstChild);
				relattDIV.removeChild(relattDIV.firstChild);
			}
			var xmlFragmentPropertyList = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_cachedProperties = xmlFragmentPropertyList;
			selectionProvider.fireBeforeRefresh();
	  		transformer.transformResultToHTML(request,relattDIV);
	  		if (OB_bd.isGecko) {
	  			// FF needs repasting for chemical formulas and equations because FF's XSLT processor does not know 'disable-output-encoding' switch. IE does.
	  			// thus, repaste markup on all elements marked with a 'chemFoEq' attribute
	  			GeneralBrowserTools.repasteMarkup("chemFoEq");
	  		}
	  		selectionProvider.fireRefresh();
	  		selectionProvider.fireSelectionChanged(null, null, SMW_PROPERTY_NS, null);
	  	}
	  	
	  	function callbackOnInstanceSelectToLeft (request) {
	  		OB_tree_pendingIndicator.hide();
	  		if (categoryDIV.firstChild) {
	  		  	GeneralBrowserTools.purge(categoryDIV.firstChild);
				categoryDIV.removeChild(categoryDIV.firstChild);
			}
			dataAccess.OB_cachedCategoryTree = GeneralXMLTools.createDocumentFromString(request.responseText);
			selectionProvider.fireBeforeRefresh();
			dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(request.responseText, categoryDIV);
			selectionProvider.fireRefresh();
			selectionProvider.fireSelectionChanged(null, null, SMW_CATEGORY_NS, null);
	  	}
	  	
	  	
	  	
	  	if (OB_RIGHT_ARROW == 0) {
	  		OB_relatt_pendingIndicator.show();
		 	sajax_do_call('smwf_ob_OntologyBrowserAccess', ['getAnnotations',instanceName], callbackOnInstanceSelectToRight);
	  	} 
	  	if (OB_LEFT_ARROW == 1) {
	  		OB_tree_pendingIndicator.show();
	  		sajax_do_call('smwf_ob_OntologyBrowserAccess', ['getCategoryForInstance',instanceName], callbackOnInstanceSelectToLeft);
	  	}
	
		}
	},
	
	selectNextPartition: function (e, htmlNode) {
			
		var partition = htmlNode.getAttribute("partitionnum");
		partition++;
		OB_instance_pendingIndicator.show();
		if (globalActionListener.activeTreeName == 'categoryTree') {
			dataAccess.getInstances(categoryActionListener.selectedCategory, partition, this.selectPartitionCallback.bind(this));
		} else if (globalActionListener.activeTreeName == 'propertyTree') {
			dataAccess.getInstancesUsingProperty(propertyActionListener.selectedProperty, partition, this.selectPartitionCallback.bind(this));
		} 
	},
	
	selectPreviousPartition: function (e, htmlNode) {
		
		var partition = htmlNode.getAttribute("partitionnum");
		partition--;
		OB_instance_pendingIndicator.show();
		if (globalActionListener.activeTreeName == 'categoryTree') {
			dataAccess.getInstances(categoryActionListener.selectedCategory, partition, this.selectPartitionCallback.bind(this));
		} else if (globalActionListener.activeTreeName == 'propertyTree') {
			dataAccess.getInstancesUsingProperty(propertyActionListener.selectedProperty, partition, this.selectPartitionCallback.bind(this));
		} 
	},
	
	selectPartitionCallback: function (request) {
			OB_instance_pendingIndicator.hide();
			var instanceListNode = $("instanceList");			
			GeneralXMLTools.removeAllChildNodes(instanceListNode);
			var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(xmlFragmentInstanceList, instanceListNode, true);
			selectionProvider.fireSelectionChanged(null, null, SMW_INSTANCE_NS, null);
			selectionProvider.fireRefresh();
			instanceListNode.scrollTop = 0;
	},
	/*
 	* Hides/Shows instance box
 	*/
	toggleInstanceBox: function (event) {
		if ($("instanceContainer").visible()) {
			$("hideInstancesButton").innerHTML = gLanguage.getMessage('SHOW_INSTANCES');
			Effect.Fold("instanceContainer");
			Effect.Fold($("leftArrow"));
		} else {
			new Effect.Grow('instanceContainer');
			$("hideInstancesButton").innerHTML = gLanguage.getMessage('HIDE_INSTANCES');
			new Effect.Grow($("leftArrow"));
		}
	}
	
}

/**
 * Action Listener for attributes in the attribute tree
 */
var OBPropertyTreeActionListener = Class.create();
OBPropertyTreeActionListener.prototype = Object.extend(new OBTreeActionListener() , {
  initialize: function() {
			
		this.selectedProperty = null;
		this.selectedPropertyID = null;
		this.oldSelectedProperty = null;
		selectionProvider.addListener(this, OB_SELECTIONLISTENER);
		selectionProvider.addListener(this, OB_REFRESHLISTENER);
		selectionProvider.addListener(this, OB_BEFOREREFRESHLISTENER);
		
		
		Draggables.addObserver(this);
		this.draggableProperties = [];
		Droppables.add('propertyTreeSwitch', {accept:'property', hoverclass:'dragHover', onDrop:this.onDrop.bind(this)}); 
	},
	
	navigateToEntity: function(event, node, propertyName, editmode) {
		smwhgLogger.log(propertyName, "OB","inspect_entity");
		GeneralBrowserTools.navigateToPage(gLanguage.getMessage('PROPERTY_NS_WOC'), propertyName, editmode);
	},
	
	selectionChanged: function(id, title, ns, node) {
		if (ns == SMW_PROPERTY_NS) {
			this.selectedProperty = title;
			this.selectedPropertyID = id;
			this.oldSelectedProperty = GeneralBrowserTools.toggleHighlighting(this.oldSelectedProperty, node);
		}
	},
	
	beforeRefresh: function() {
		if (wgUserGroups == null || (wgUserGroups.indexOf('sysop') == -1 && wgUserGroups.indexOf('gardener') == -1)) {
			
			return;
		}
		if (OB_bd.isIE) {
			return; // no DnD in IE
		}
		this.draggableProperties.each(function(c) { 
			c.destroy();
			
		});
		$$('a.property').each(function(c) { 
			Droppables.remove(c.getAttribute('id'));
		});
		this.draggableProperties = [];
	},
	
	refresh: function() {
		if (wgUserGroups == null || (wgUserGroups.indexOf('sysop') == -1 && wgUserGroups.indexOf('gardener') == -1)) {
			// do not allow dragging, when user is no sysop or gardener
			return;
		}
		if (OB_bd.isIE) {
			return; // do not activate DnD in IE, because scriptaculous is very buggy here
		}
		function addDragAndDrop(c) { 
			var d = new Draggable(c.getAttribute('id'), {revert:true, ghosting:true}); 
			this.draggableProperties.push(d); 
			Droppables.add(c.getAttribute('id'), {accept:'property', hoverclass:'dragHover', onDrop:onDrop_bind}); 
		}
		var addDragAndDrop_bind = addDragAndDrop.bind(this);
		var onDrop_bind = this.onDrop.bind(this);
		$$('a.property').each(addDragAndDrop_bind);
		
	},
	
	onStart: function(eventName, draggable, event) {
		
	},
	
	onDrop: function(dragElement, dropElement, event) {
		var draggedPropertyID = dragElement.getAttribute('id');
		var droppedPropertyID = dropElement.getAttribute('id');
		//alert('Dropped on: '+droppedPropertyID+" from: "+draggedPropertyID);
		ontologyTools.moveProperty(draggedPropertyID, droppedPropertyID);
		
	},
	
	showSubMenu: function(commandID) {
		if (this.selectedProperty == null) {
			alert(gLanguage.getMessage('OB_SELECT_PROPERTY'));
			return;
		}
		obPropertyMenuProvider.showContent(commandID, 'propertyTree');
	},
	
  select: function (event, node, propertyID, propertyName) {
  		
  		var e = GeneralTools.getEvent(event);
	
		// if Ctrl is pressed: navigation mode
		if (e["ctrlKey"]) {
			GeneralBrowserTools.navigateToPage(gLanguage.getMessage('PROPERTY_NS_WOC'), propertyName);
		} else {
		
		var nextDIV = node.nextSibling;
	
 		// find the next DIV
		while(nextDIV.nodeName != "DIV") {
		nextDIV = nextDIV.nextSibling;
		}
		// check if node is already expanded and expand it if not
		if (!nextDIV.hasChildNodes()  || nextDIV.style.display == 'none') {
			this.toggleExpand(event, node, propertyID);
		}
		
		var instanceDIV = document.getElementById("instanceList");
		var relattDIV = $("relattributes");
		
		
		// fire selection event
		selectionProvider.fireSelectionChanged(propertyID, propertyName, SMW_PROPERTY_NS, node);
		
		smwhgLogger.log(propertyName, "OB","clicked");	
	
		function callbackOnPropertySelect(request) {
			OB_instance_pendingIndicator.hide();
	  		if (instanceDIV.firstChild) {
	  			 	GeneralBrowserTools.purge(instanceDIV.firstChild);
					instanceDIV.removeChild(instanceDIV.firstChild);
			}
			var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
			selectionProvider.fireBeforeRefresh();
	 	 	transformer.transformResultToHTML(request,instanceDIV, true);
	 	 	selectionProvider.fireRefresh();
		}
		
		function callbackOnPropertySelect2(request) {
		 	OB_relatt_pendingIndicator.hide();
		  	if (relattDIV.firstChild) {
		  			GeneralBrowserTools.purge(relattDIV.firstChild);
					relattDIV.removeChild(relattDIV.firstChild);
			}
			var xmlFragmentPropertyList = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_cachedProperties = xmlFragmentPropertyList;
			selectionProvider.fireBeforeRefresh();
		  	transformer.transformResultToHTML(request,relattDIV);
		  	selectionProvider.fireRefresh();
		  	
	 	}
	 	
	 	 if (OB_LEFT_ARROW == 0) {
		     OB_instance_pendingIndicator.show();
		 	 dataAccess.getInstancesUsingProperty(propertyName, 0, callbackOnPropertySelect);
		 	 selectionProvider.fireSelectionChanged(null, null, SMW_INSTANCE_NS, null);
	 	 }
	 	 if (OB_RIGHT_ARROW == 0) {
	 		OB_relatt_pendingIndicator.show();
	 		sajax_do_call('smwf_ob_OntologyBrowserAccess', ['getAnnotations',gLanguage.getMessage('PROPERTY_NS')+propertyName], callbackOnPropertySelect2);
	 	 }
		}
	},
	
  toggleExpand: function (event, node, folderCode) {
  	this._toggleExpand(event, node, dataAccess.OB_cachedPropertyTree, dataAccess.getPropertySubTree.bind(dataAccess));
  },
  selectNextPartition: function (e, htmlNode) {
	function calledOnFinish(tree) {
			dataAccess.OB_cachedPropertyTree = tree;
			selectionProvider.fireSelectionChanged(null, null, SMW_PROPERTY_NS, null);
			$('propertyTree').scrollTop = 0;
		}
		this._selectNextPartition(e, htmlNode, dataAccess.OB_cachedPropertyTree, dataAccess.getPropertyPartition.bind(dataAccess), "propertyTree", calledOnFinish);

	},

	selectPreviousPartition: function (e, htmlNode) {
	 function calledOnFinish(tree) {
			dataAccess.OB_cachedPropertyTree = tree;
			selectionProvider.fireSelectionChanged(null, null, SMW_PROPERTY_NS, null);
			$('propertyTree').scrollTop = 0;
		}
		this._selectPreviousPartition(e, htmlNode, dataAccess.OB_cachedPropertyTree, dataAccess.getPropertyPartition.bind(dataAccess), "propertyTree", calledOnFinish);
	
	}
	
});


/**
 * Action Listener for attribute and relation annotations
 */
var OBAnnotationActionListener = Class.create();
OBAnnotationActionListener.prototype = {
	initialize: function() {
		//empty
		
	},
	
	navigateToTarget: function(event, node, targetInstance) {
		GeneralBrowserTools.navigateToPage(null, targetInstance);
	},
	
	selectProperty: function(event, node, propertyName) {
		// delegate to schemaPropertyListener
		schemaActionPropertyListener.selectProperty(event, node, propertyName);
	}
	
}

/**
 * Action Listener for schema properties, i.e. attributes and relations
 * on schema level
 */
var OBSchemaPropertyActionListener = Class.create();
OBSchemaPropertyActionListener.prototype = {
	initialize: function() {
		this.selectedCategory = null; // initially none is selected
		this.oldSelectedProperty = null;
		selectionProvider.addListener(this, OB_SELECTIONLISTENER);
	},
	
	selectionChanged: function(id, title, ns, node) {
		if (ns == SMW_CATEGORY_NS) {
			this.selectedCategory = title;
			var anchor = $('currentSelectedCategory');
			if (anchor != null) {
				if (title == null) { 
					anchor.innerHTML = '...';
				} else {
					anchor.innerHTML = "'"+title+"'";
				}
			}
		} else if (ns == SMW_PROPERTY_NS){
			this.oldSelectedProperty = GeneralBrowserTools.toggleHighlighting(this.oldSelectedProperty, node);
		}
	},
	
	showSubMenu: function(commandID) {
		if (this.selectedCategory == null) {
			alert(gLanguage.getMessage('OB_SELECT_CATEGORY'));
			return;
		}
		obSchemaPropertiesMenuProvider.showContent(commandID, 'relattributes');
	},
	
	navigateToEntity: function(event, node, attributeName, editmode) {
		smwhgLogger.log(attributeName, "OB","inspect_entity");
		GeneralBrowserTools.navigateToPage(gLanguage.getMessage('PROPERTY_NS_WOC'), attributeName, editmode);
	},
	
		
	selectProperty: function(event, node, attributeName) {
		var categoryDIV = $("categoryTree");
		var instanceDIV = $("instanceList");
		
		
		selectionProvider.fireSelectionChanged(null, attributeName, SMW_PROPERTY_NS, node);
		smwhgLogger.log(attributeName, "OB","clicked");	
		
		function callbackOnPropertySelectForCategory (request) {
			OB_tree_pendingIndicator.hide();
	  		if (categoryDIV.firstChild) {
	  		  	GeneralBrowserTools.purge(categoryDIV.firstChild);
				categoryDIV.removeChild(categoryDIV.firstChild);
			}
			dataAccess.OB_cachedCategoryTree = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(request.responseText, categoryDIV);
			selectionProvider.fireSelectionChanged(null, null, SMW_CATEGORY_NS, null);
	  	}
	  	
	  	function callbackOnPropertySelectForInstance (request) {
	  		OB_instance_pendingIndicator.hide();
	  		if (instanceDIV.firstChild) {
	  			GeneralBrowserTools.purge(instanceDIV.firstChild);
				instanceDIV.removeChild(instanceDIV.firstChild);
			}
		
			var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
			selectionProvider.fireBeforeRefresh();
	  		transformer.transformResultToHTML(request,instanceDIV, true);
	  		selectionProvider.fireRefresh();
	  		selectionProvider.fireSelectionChanged(null, null, SMW_INSTANCE_NS, null);
	  	}
		// if Ctrl is pressed: navigation mode
		if (event["ctrlKey"]) {
			GeneralBrowserTools.navigateToPage(gLanguage.getMessage('PROPERTY_NS_WOC'), attributeName);
		} else {
			if (OB_LEFT_ARROW == 1) {
				OB_tree_pendingIndicator.show();
				sajax_do_call('smwf_ob_OntologyBrowserAccess', ['getCategoryForProperty',attributeName], callbackOnPropertySelectForCategory);
			}
			if (OB_RIGHT_ARROW == 1) {
				 OB_instance_pendingIndicator.show();
				
				 dataAccess.getInstancesUsingProperty(attributeName, 0, callbackOnPropertySelectForInstance);
			}
		}
	},
	
	
	selectRangeInstance: function(event, node, categoryName) {
		if (event["ctrlKey"]) {
			GeneralBrowserTools.navigateToPage(gLanguage.getMessage('CATEGORY_NS_WOC'), categoryName);
		}
	}
}

/**
 * Action Listener for global Ontology Browser events, e.g. switch tree
 */
var OBGlobalActionListener = Class.create();
OBGlobalActionListener.prototype = {
	initialize: function() {
		this.activeTreeName = 'categoryTree';
		
		new Form.Element.Observer($("treeFilter"), 0.5, this.filterTree.bindAsEventListener(this));
        new Form.Element.Observer($("instanceFilter"), 0.5, this.filterInstances.bindAsEventListener(this));
        new Form.Element.Observer($("propertyFilter"), 0.5, this.filterProperties.bindAsEventListener(this));
		
		// make sure that OntologyBrowser Filter search gets focus if a key is pressed
		Event.observe(document, 'keydown', function(event) { 
			if (event.target.id == 'searchInput' || event.target.id.indexOf('ontologytools') != -1) return;
			if (event.target.parentNode != document && $(event.target.parentNode).hasClassName('OB-filters')) return;
			$('FilterBrowserInput').focus() 
		});
		
		selectionProvider.addListener(this, OB_REFRESHLISTENER);
	},
	
	refresh: function() {
		_smw_hideAllTooltips();
		// re-initialize tooltips when content has changed.
		smw_tooltipInit();
	},
	
	
	/*
	 * Switches to the given tree.
 	*/
	switchTreeComponent: function (event, showWhichTree, noInitialize) {
		if ($("categoryTree").visible() && showWhichTree != 'categoryTree') {
			$("categoryTree").hide();
			$(showWhichTree).show();
			$(showWhichTree+"Switch").addClassName("selectedSwitch");
			$("categoryTreeSwitch").removeClassName("selectedSwitch");
			
			if ($("menuBarConceptTree") != null && $("menuBarPropertyTree")) {
				// MenuBar may not be visible
				$("menuBarConceptTree").hide();
				$("menuBarPropertyTree").show();
			}
			
		} else if ($("propertyTree").visible() && showWhichTree != 'propertyTree') {
			$("propertyTree").hide();
			$(showWhichTree).show();
			$(showWhichTree+"Switch").addClassName("selectedSwitch");
			$("propertyTreeSwitch").removeClassName("selectedSwitch");
			
			if ($("menuBarConceptTree") != null && $("menuBarPropertyTree")) {
				// MenuBar may not be visible
				$("menuBarPropertyTree").hide();
				$("menuBarConceptTree").show();
			}
		}
		
		this.activeTreeName = showWhichTree;
		
		if (!noInitialize) {
			if (showWhichTree == 'categoryTree') {
				dataAccess.initializeRootCategories(0);
				
			} else if (showWhichTree == 'propertyTree') {
				dataAccess.initializeRootProperties(0);
				
			} 
		}
		
		
	},
	
	/**
	 * Global filter event listener. 
	 * Filters the currently visible tree. 
	 * 
	 * @param event
	 */
	filterTree: function(event) {
		
		// reads filter string
		
		var filter = $F('treeFilter');
		var tree;
		var actionListener;
		
		// decide which tree is active and
		// set actionListener for that tree
		if (this.activeTreeName == 'categoryTree') {
			actionListener = categoryActionListener;
			tree = dataAccess.OB_cachedCategoryTree;
			if (filter == "") { //special case empty filter, just copy
				dataAccess.initializeRootCategories(0);
				selectionProvider.fireBeforeRefresh();
				transformer.transformXMLToHTML(dataAccess.OB_currentlyDisplayedTree, $(this.activeTreeName), true);
				selectionProvider.fireRefresh();
				selectionProvider.fireSelectionChanged(null, null, SMW_CATEGORY_NS, null);
				return;
			}	
		} else if (this.activeTreeName == 'propertyTree') {
			actionListener = propertyActionListener;	
			tree = dataAccess.OB_cachedPropertyTree;
			if (filter == "") {
				dataAccess.initializeRootProperties(0);
				selectionProvider.fireBeforeRefresh();
				transformer.transformXMLToHTML(dataAccess.OB_currentlyDisplayedTree, $(this.activeTreeName), true);
				selectionProvider.fireRefresh();
				selectionProvider.fireSelectionChanged(null, null, SMW_PROPERTY_NS, null);
				return;
			}
		}  
		// filter tree
		actionListener._filterTree(event, tree, this.activeTreeName, filter);
		
		
	},
	
	/**
	 * Filters instances currently visible. 
	 */
	filterInstances: function(event) {
		if (dataAccess.OB_cachedInstances == null) {
			return;
		}
		
		var filter = $F('instanceFilter');
		
		var regex = new Array();
    	var filterTerms = GeneralTools.splitSearchTerm(filter);
    	for(var i = 0, n = filterTerms.length; i < n; i++) {
    		try{
	   		 regex[i] = new RegExp(filterTerms[i],"i");
    		} catch(e) {
    			return;
    		}
    	}
    		
		var nodesFound = GeneralXMLTools.createDocumentFromString("<instanceList/>");
		var instanceList = dataAccess.OB_cachedInstances.firstChild;
		for (var i = 0, n = instanceList.childNodes.length; i < n; i++) {
			var inst = instanceList.childNodes[i];
			var	title = inst.getAttribute("title");
			if (title && GeneralTools.matchArrayOfRegExp(title, regex)) {
				GeneralXMLTools.importNode(nodesFound.firstChild, inst, true);
			}
			if (inst.tagName == 'instancePartition') {
				GeneralXMLTools.importNode(nodesFound.firstChild, inst, true);
			}
		}
		selectionProvider.fireBeforeRefresh();
		transformer.transformXMLToHTML(nodesFound, $("instanceList"), true); 
		selectionProvider.fireRefresh();
		selectionProvider.fireSelectionChanged(null, null, SMW_INSTANCE_NS, null);
	},
	
	/**
	 * Filters properties currently visible.
	 */
	filterProperties: function(event) {
		if (dataAccess.OB_cachedProperties == null) {
			return;
		}
		
		var filter = $F('propertyFilter');
		
		var regex = new Array();
    	var filterTerms = GeneralTools.splitSearchTerm(filter);
    	for(var i = 0, n = filterTerms.length; i < n; i++) {
    		try {
	   		 regex[i] = new RegExp(filterTerms[i],"i");
    		} catch(e) {
    			return;
    		}
    	}
    	
		var tagName = dataAccess.OB_cachedProperties.firstChild.tagName;
		var nodesFound = GeneralXMLTools.createDocumentFromString("<"+tagName+"/>");
		var propertyList = dataAccess.OB_cachedProperties.firstChild;
		for (var i = 0, n = propertyList.childNodes.length; i < n; i++) {
			var property = propertyList.childNodes[i];
			var	title = property.getAttribute("title");
			if (title && GeneralTools.matchArrayOfRegExp(title, regex)) {
				GeneralXMLTools.importNode(nodesFound.firstChild, property, true);
			}
			if (property.tagName == 'propertyPartition') {
				GeneralXMLTools.importNode(nodesFound.firstChild, property, true);
			}
		}
		selectionProvider.fireBeforeRefresh();
		transformer.transformXMLToHTML(nodesFound, $("relattributes"), true); 
		selectionProvider.fireRefresh();
		selectionProvider.fireSelectionChanged(null, null, SMW_PROPERTY_NS, null);
		GeneralBrowserTools.repasteMarkup("chemFoEq");
	},
	
	/**
	 * @deprecated
	 * not used any more
	 */
	filterRoot: function(event) {
		var actionListener;
		var tree;
		if (this.activeTreeName == 'categoryTree') {
			actionListener = categoryActionListener;
			tree = dataAccess.OB_cachedCategoryTree;	
		} else if (this.activeTreeName == 'propertyTree') {
			actionListener = propertyActionListener;	
			tree = dataAccess.OB_cachedPropertyTree;
		}  
		actionListener._filterRootLevel(event, tree, this.activeTreeName);
	},
	
	/**
	 * Filters database wide. Categories, instances, properties
	 * 
	 * @param event
	 * @param force Filters in any case, otherwise only if enter is pressed in given event.
	 */
	filterBrowsing: function(event, force) {
		
	 function filterBrowsingCategoryCallback(request) {
	 	OB_tree_pendingIndicator.hide();
	 	var categoryDIV = $("categoryTree");
	 	if (categoryDIV.firstChild) {
	 		GeneralBrowserTools.purge(categoryDIV.firstChild);
			categoryDIV.removeChild(categoryDIV.firstChild);
		}
	  	dataAccess.OB_cachedCategoryTree = GeneralXMLTools.createDocumentFromString(request.responseText);
  		dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(request.responseText, categoryDIV);
  		selectionProvider.fireSelectionChanged(null, null, SMW_CATEGORY_NS, null);
	 }
	 
	  function filterBrowsingAttributeCallback(request) {
	 	OB_tree_pendingIndicator.hide();
	 	var attributeDIV = $("propertyTree");
	 	if (attributeDIV.firstChild) {
	 		GeneralBrowserTools.purge(attributeDIV.firstChild);
			attributeDIV.removeChild(attributeDIV.firstChild);
		}
	  	dataAccess.OB_cachedPropertyTree = GeneralXMLTools.createDocumentFromString(request.responseText);
  		dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(request.responseText, attributeDIV);
  		selectionProvider.fireSelectionChanged(null, null, SMW_PROPERTY_NS, null);
	 }
	 
	 
	 
	 function filterBrowsingInstanceCallback(request) {
	 	OB_instance_pendingIndicator.hide();
	 	var instanceDIV = $("instanceList");
	 	if (instanceDIV.firstChild) {
	 		GeneralBrowserTools.purge(instanceDIV.firstChild);
			instanceDIV.removeChild(instanceDIV.firstChild);
		}
		var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
		dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
		selectionProvider.fireBeforeRefresh();
	  	transformer.transformResultToHTML(request,instanceDIV, true);
	  	selectionProvider.fireRefresh();
	  	selectionProvider.fireSelectionChanged(null, null, SMW_INSTANCE_NS, null);
	 }
	 
	 function filterBrowsingPropertyCallback(request) {
	 	OB_relatt_pendingIndicator.hide();
	 	var propertyDIV = $("relattributes");
	 	if (propertyDIV.firstChild) {
	 		GeneralBrowserTools.purge(propertyDIV.firstChild);
			propertyDIV.removeChild(propertyDIV.firstChild);
		}
		var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
		dataAccess.OB_cachedProperties = xmlFragmentInstanceList;
		selectionProvider.fireBeforeRefresh();
	  	transformer.transformResultToHTML(request,propertyDIV, true);
	  	selectionProvider.fireRefresh();
	  	selectionProvider.fireSelectionChanged(null, null, SMW_PROPERTY_NS, null);
	 }
	 
	 if (!force && event["keyCode"] != 13 ) {
	 	return;
	 }
	 var filterBrowserInput = $("FilterBrowserInput");
     var hint = filterBrowserInput.value;
	 
	 if (hint.length <= 1) {
	 	alert(gLanguage.getMessage('ENTER_MORE_LETTERS'));
	 	return;
	 }
	 if (this.activeTreeName == 'categoryTree') {
	 	 OB_tree_pendingIndicator.show(this.activeTreeName);
		 sajax_do_call('smwf_ob_OntologyBrowserAccess', ['filterBrowse',"category,"+hint], filterBrowsingCategoryCallback);
	 }  else if (this.activeTreeName == 'propertyTree') {
	 	 OB_tree_pendingIndicator.show(this.activeTreeName);
         sajax_do_call('smwf_ob_OntologyBrowserAccess', ['filterBrowse',"propertyTree,"+hint], filterBrowsingAttributeCallback);
	 } 
	  OB_instance_pendingIndicator.show();
	  OB_relatt_pendingIndicator.show();
	  sajax_do_call('smwf_ob_OntologyBrowserAccess', ['filterBrowse',"instance,"+hint], filterBrowsingInstanceCallback);	
	  sajax_do_call('smwf_ob_OntologyBrowserAccess', ['filterBrowse',"property,"+hint], filterBrowsingPropertyCallback);
	 
	},
	
	/**
	 * Sets back tree view and clear search field.
	 */
	reset: function(event) {
		if (this.activeTreeName == 'categoryTree') {
			dataAccess.initializeRootCategories(0, true);
		} else if (this.activeTreeName == 'propertyTree') {
			dataAccess.initializeRootProperties(0, true);

		} 
		// clear input field
		var inputs = document.getElementsByTagName("input");
		inputs[0].value = "";
	},
	
	/**
	 * Toggles left arrow
	 */
	toogleCatInstArrow: function(event) {
		var img = Event.element(event);
		smwhgLogger.log("", "OB","flipflow_left");
		if (OB_LEFT_ARROW == 0) {
			OB_LEFT_ARROW = 1;
			img.setAttribute("src",wgScriptPath+"/extensions/SMWHalo/skins/OntologyBrowser/images/bigarrow_left.gif");
		} else {
			OB_LEFT_ARROW = 0;
			img.setAttribute("src",wgScriptPath+"/extensions/SMWHalo/skins/OntologyBrowser/images/bigarrow.gif");
		}
	},
	
	/**
	 * Toggles right arrow
	 */
	toogleInstPropArrow: function(event) {
		var img = Event.element(event);
		smwhgLogger.log("", "OB","flipflow_right");
		if (OB_RIGHT_ARROW == 0) {
			OB_RIGHT_ARROW = 1;
			img.setAttribute("src",wgScriptPath+"/extensions/SMWHalo/skins/OntologyBrowser/images/bigarrow_left.gif");
		} else {
			OB_RIGHT_ARROW = 0;
			img.setAttribute("src",wgScriptPath+"/extensions/SMWHalo/skins/OntologyBrowser/images/bigarrow.gif");
		}
	}

}

	






// treeviewData.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
/*  Copyright 2007, ontoprise GmbH
*   Author: Kai Kühn
*   This file is part of the halo-Extension.
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

/*
 * Treeview Data
 */


// action listeners are global.
var categoryActionListener = null;
var instanceActionListener = null;
var globalActionListener = null;

// standard partition size
var OB_partitionSize = 40;

// Data for category trees

var OBDataAccess = Class.create();
OBDataAccess.prototype = {
	initialize: function() {
		
		// cached trees
		this.OB_cachedCategoryTree = null;
		this.OB_cachedPropertyTree = null;
	
		this.OB_cachedInstances = null;
		this.OB_cachedProperties = GeneralXMLTools.createDocumentFromString("<propertyList/>");
		
		// displayed tree
		this.OB_currentlyDisplayedTree = null;
		
		// initialize flags
		this.OB_categoriesInitialized = false;
		this.OB_attributesInitialized = false;
	
		
		 // initialize action listeners
		 // note: action listeners are global!
   		categoryActionListener = new OBCategoryTreeActionListener();
		instanceActionListener = new OBInstanceActionListener();
		propertyActionListener = new OBPropertyTreeActionListener();
		globalActionListener = new OBGlobalActionListener();
		annotationActionListener = new OBAnnotationActionListener();
		schemaActionPropertyListener = new OBSchemaPropertyActionListener();
		
		// One global instance of OBPendingIndicator for each container. 
		// The tree container has only one for the categoryTree (or any other tree)
		OB_tree_pendingIndicator = new OBPendingIndicator($("categoryTree"));
		OB_instance_pendingIndicator = new OBPendingIndicator($("instanceList"));
		OB_relatt_pendingIndicator = new OBPendingIndicator($("relattributes"));
	
	}, 
	
initializeTree: function (param) {
	// ----- initialize with appropriate data -------
	var title = GeneralBrowserTools.getURLParameter("entitytitle");
	var ns = GeneralBrowserTools.getURLParameter("ns");
	var searchTerm = GeneralBrowserTools.getURLParameter("searchTerm");
	
	// high priority: searchTerm in URL
	if (searchTerm != undefined) {
		var inputs = document.getElementsByTagName("input");
	 	inputs[0].value = searchTerm;
		globalActionListener.filterBrowsing(null, true);
		return;
	}
	
	// if no params: default initialization
	if (title == undefined && ns == undefined) {
  		// default: initialize with root categories
		this.initializeRootCategories();
		return;
   }
   
   // otherwise use namespace and title parameters
   if (ns == gLanguage.getMessage('CATEGORY_NS_WOC')) {
   	this.filterBrowseCategories(title);
   } else if (ns == undefined || ns == '') { // => NS_MAIN
   	this.filterBrowseInstances(title);
   } else if (ns == gLanguage.getMessage('PROPERTY_NS_WOC')) {
    this.filterBrowseProperties(title);
   } 
},

initializeRootCategoriesCallback: function (request) {
  OB_tree_pendingIndicator.hide();
  if ( request.status != 200 ) {
   alert("Error: " + request.status + " " + request.statusText + ": " + request.responseText);
	return;
  }
  
 	 this.OB_categoriesInitialized = true;
  
	var rootElement = $("categoryTree");
 
  // parse root category xml and transform it to HTML
   	this.OB_cachedCategoryTree = GeneralXMLTools.createDocumentFromString(request.responseText);
  	//transformer.transformXMLToHTML(this.OB_cachedCategoryTree, rootElement, true);
  	this.OB_currentlyDisplayedTree = GeneralXMLTools.createDocumentFromString(request.responseText);
  	selectionProvider.fireBeforeRefresh();
  	transformer.transformXMLToHTML(this.OB_currentlyDisplayedTree, rootElement, true);
 	selectionProvider.fireRefresh();
 	selectionProvider.fireSelectionChanged(null, null, SMW_CATEGORY_NS, null);
  	
},

initializeRootPropertyCallback: function (request) {
  OB_tree_pendingIndicator.hide();
  if ( request.status != 200 ) {
   alert("Error: " + request.status + " " + request.statusText + ": " + request.responseText);
	return;
  }
  
 
  	
  	this.OB_attributesInitialized = true;
  
	var rootElement = $("propertyTree");
 
  // parse root category xml and transform it to HTML
   	this.OB_cachedPropertyTree = GeneralXMLTools.createDocumentFromString(request.responseText);
  	//transformer.transformXMLToHTML(this.OB_cachedPropertyTree, rootElement, true);
  	this.OB_currentlyDisplayedTree = GeneralXMLTools.createDocumentFromString(request.responseText);
  	selectionProvider.fireBeforeRefresh();
  	transformer.transformXMLToHTML(this.OB_currentlyDisplayedTree, rootElement, true);
 	selectionProvider.fireRefresh();
  	selectionProvider.fireSelectionChanged(null, null, SMW_PROPERTY_NS, null);
},


updateTree: function(xmlText, rootElement) {
	var tree = GeneralXMLTools.createDocumentFromString(xmlText);
	selectionProvider.fireBeforeRefresh();
  	transformer.transformXMLToHTML(tree, rootElement, true);
  	selectionProvider.fireRefresh();
  	return tree;
},

initializeRootCategories: function(partition, force) {
	if (!this.OB_categoriesInitialized || force) {
		OB_tree_pendingIndicator.show(globalActionListener.activeTreeName);
		sajax_do_call('smwf_ob_OntologyBrowserAccess', ['getRootCategories',OB_partitionSize+","+partition], this.initializeRootCategoriesCallback.bind(this));
	} else {
  		// copy from cache
  		this.OB_currentlyDisplayedTree = GeneralXMLTools.createDocumentFromString("<result/>");
  		GeneralXMLTools.importSubtree(this.OB_currentlyDisplayedTree.firstChild, this.OB_cachedCategoryTree.firstChild, true);
  } 	
},

initializeRootProperties: function(partition, force) {
	 if (!this.OB_attributesInitialized || force) {
	 	OB_tree_pendingIndicator.show(globalActionListener.activeTreeName);
		sajax_do_call('smwf_ob_OntologyBrowserAccess', ['getRootProperties',OB_partitionSize+","+partition], this.initializeRootPropertyCallback.bind(this));
	 } else {
  		// copy from cache
  		this.OB_currentlyDisplayedTree = GeneralXMLTools.createDocumentFromString("<result/>");
  		GeneralXMLTools.importSubtree(this.OB_currentlyDisplayedTree.firstChild, this.OB_cachedPropertyTree.firstChild, true);
	}
},


/*
 * Category Subtree hook
 * param: title of parent node
 * callBack: callBack to be called from AJAX request
 */
getCategorySubTree: function (categoryID, categoryName, callBackOnAjax, callBackOnCache) {
	var nodeToExpand = GeneralXMLTools.getNodeById(this.OB_cachedCategoryTree, categoryID);
	if (nodeToExpand != null && nodeToExpand.getElementsByTagName('conceptTreeElement').length > 0) {
		// copy it from cache to displayed tree.
		var nodeInDisplayedTree = GeneralXMLTools.getNodeById(this.OB_currentlyDisplayedTree, categoryID);
		GeneralXMLTools.importSubtree(nodeInDisplayedTree, nodeToExpand);
		
		// create result dummy document and call 'callBackOnCache' to transform
		var subtree = GeneralXMLTools.createDocumentFromString("<result/>");
		GeneralXMLTools.importSubtree(subtree.firstChild, nodeToExpand);
		callBackOnCache(subtree);
		
		
	} else {
		// download it
		this.getCategoryPartition(false, 0, categoryName, callBackOnAjax);
	}
},

getPropertySubTree: function (attributeID, attributeName, callBackOnAjax, callBackOnCache) {
	var nodeToExpand = GeneralXMLTools.getNodeById(this.OB_cachedPropertyTree, attributeID);
	if (nodeToExpand != null && nodeToExpand.getElementsByTagName('propertyTreeElement').length > 0) {
		// copy it from cache to displayed tree.
		var nodeInDisplayedTree = GeneralXMLTools.getNodeById(this.OB_currentlyDisplayedTree, attributeID);
		GeneralXMLTools.importSubtree(nodeInDisplayedTree, nodeToExpand);
		
		// create result dummy document and call 'callBackOnCache' to transform
		var subtree = GeneralXMLTools.createDocumentFromString("<result/>");
		GeneralXMLTools.importSubtree(subtree.firstChild, nodeToExpand);
		callBackOnCache(subtree);
	} else {
		// download it
		sajax_do_call('smwf_ob_OntologyBrowserAccess', ['getSubProperties',attributeName+","+OB_partitionSize+",0"],  callBackOnAjax);
	}
},



getInstances: function(categoryName, partition, callback) {
	sajax_do_call('smwf_ob_OntologyBrowserAccess', ['getInstance',categoryName+","+OB_partitionSize+","+partition], callback);
},

getProperties: function(categoryName, onlyDirect, callback) {
	sajax_do_call('smwf_ob_OntologyBrowserAccess', ['getProperties',categoryName+","+onlyDirect], callback);
},

getAnnotations: function(instanceName, callback) {
	sajax_do_call('smwf_ob_OntologyBrowserAccess', ['getAnnotations',instanceName], callback);
},

getCategoryPartition: function(isRootLevel, partition, categoryName, selectPartitionCallback) {
	if (isRootLevel) {
		// root level
		sajax_do_call('smwf_ob_OntologyBrowserAccess', ['getRootCategories',OB_partitionSize+','+partition],  selectPartitionCallback);
	} else {
		// every other level
		sajax_do_call('smwf_ob_OntologyBrowserAccess', ['getSubCategory',categoryName+","+OB_partitionSize+","+partition],  selectPartitionCallback);
	}
},

getPropertyPartition: function(isRootLevel, partition, attributeName, selectPartitionCallback) {
	if (isRootLevel) {
		// root level
		sajax_do_call('smwf_ob_OntologyBrowserAccess', ['getRootProperties',OB_partitionSize+','+partition],  selectPartitionCallback);
	} else {
		// every other level
		sajax_do_call('smwf_ob_OntologyBrowserAccess', ['getSubProperties',attributeName+","+OB_partitionSize+","+partition],  selectPartitionCallback);
	}
},



getInstancesUsingProperty: function(propertyName, partition, callback) {
	sajax_do_call('smwf_ob_OntologyBrowserAccess', ['getInstancesUsingProperty',propertyName+","+OB_partitionSize+","+partition], callback);
},

filterBrowseCategories: function(title) {
	// initialize with given category
   	function filterBrowsingCategoryCallback(request) {
		OB_tree_pendingIndicator.hide();
	 	var categoryDIV = $("categoryTree");
	 	if (categoryDIV.firstChild) {
	 		GeneralBrowserTools.purge(categoryDIV.firstChild);
			categoryDIV.removeChild(categoryDIV.firstChild);
		}
	  	dataAccess.OB_cachedCategoryTree = GeneralXMLTools.createDocumentFromString(request.responseText);
  		dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(request.responseText, categoryDIV);
	 }
	OB_tree_pendingIndicator.show(); 
   	globalActionListener.switchTreeComponent(null, 'categoryTree', true);
	sajax_do_call('smwf_ob_OntologyBrowserAccess', ['filterBrowse',"category,"+title], filterBrowsingCategoryCallback);
   	
},

filterBrowseInstances: function(title) {
    // initialize with given instance
     function filterBrowsingInstanceCallback(request) {
        OB_instance_pendingIndicator.hide();
        var instanceDIV = $("instanceList");
        if (instanceDIV.firstChild) {
            GeneralBrowserTools.purge(instanceDIV.firstChild);
            instanceDIV.removeChild(instanceDIV.firstChild);
        }
        var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
        
        // if only one instance found -> fetch annotations too
        if (xmlFragmentInstanceList.firstChild.childNodes.length == 1) {
            var instance = xmlFragmentInstanceList.firstChild.firstChild;
            OB_relatt_pendingIndicator.show();
            sajax_do_call('smwf_ob_OntologyBrowserAccess', ['getAnnotations',instance.getAttribute("title")], getAnnotationsCallback);
        }
        dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
        selectionProvider.fireBeforeRefresh();
        transformer.transformResultToHTML(request,instanceDIV, true);
        selectionProvider.fireRefresh();
     }
     
     function getAnnotationsCallback(request) {
        OB_relatt_pendingIndicator.hide();
        var relattDIV = $("relattributes");
        if (relattDIV.firstChild) {
            GeneralBrowserTools.purge(relattDIV.firstChild);
            relattDIV.removeChild(relattDIV.firstChild);
        }
        var xmlFragmentPropertyList = GeneralXMLTools.createDocumentFromString(request.responseText);
        dataAccess.OB_cachedProperties = xmlFragmentPropertyList;
        selectionProvider.fireBeforeRefresh();
        transformer.transformResultToHTML(request,relattDIV);
        selectionProvider.fireRefresh();
        if (OB_bd.isGecko) {
            // FF needs repasting for chemical formulas and equations because FF's XSLT processor does not know 'disable-output-encoding' switch. IE does.
            // thus, repaste markup on all elements marked with a 'chemFoEq' attribute
            GeneralBrowserTools.repasteMarkup("chemFoEq");
        }
     }
	 
	 OB_instance_pendingIndicator.show();
	
   	 sajax_do_call('smwf_ob_OntologyBrowserAccess', ['filterBrowse',"instance,"+title], filterBrowsingInstanceCallback);	
   	
},

filterBrowseProperties: function(title) {
		// initialize with given attribute
   	 function filterBrowsingAttributeCallback(request) {
		OB_tree_pendingIndicator.hide();
	 	var attributeDIV = $("propertyTree");
	 	if (attributeDIV.firstChild) {
	 		GeneralBrowserTools.purge(attributeDIV.firstChild);
			attributeDIV.removeChild(attributeDIV.firstChild);
		}
	  	dataAccess.OB_cachedPropertyTree = GeneralXMLTools.createDocumentFromString(request.responseText);
  		dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(request.responseText, attributeDIV);
	 }
	 OB_tree_pendingIndicator.show(); 
	globalActionListener.switchTreeComponent(null, 'propertyTree', true);
   	sajax_do_call('smwf_ob_OntologyBrowserAccess', ['filterBrowse',"propertyTree,"+title], filterBrowsingAttributeCallback);
}


};

// SMW_tooltip.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH


addOnloadHook(smw_tooltipInit); 


//these two objects needed due to the "hack" in timeline-api.js
//see the comment there
BubbleTT = new Object();
BubbleTT.Platform= new Object();

var tt = null; //the tooltip
var all_tt = []; //record all active tooltips

var imagePath=wgScriptPath+"/extensions/SemanticMediaWiki/skins/images/";

//dimensions of persistent tooltips
var SMWTT_WIDTH_P=200;
var SMWTT_HEIGHT_P=80;

//dimensions of inline tooltips
var SMWTT_WIDTH_I=150;
var SMWTT_HEIGHT_I=50;

/*register events for the tooltips*/
function smw_tooltipInit() {
	var anchs = document.getElementsByTagName("span");
	for (var i=0; i<anchs.length; i++) {
		if(anchs[i].className=="smwttpersist")smw_makePersistentTooltip(anchs[i]);
		if(anchs[i].className=="smwttinline")smw_makeInlineTooltip(anchs[i]);
	}
}

function smw_makeInlineTooltip(a) {
	var spans = a.getElementsByTagName("span");
	a.className="smwttactiveinline";
	//make content invisible
	//done here and not in the css so that non-js clients can see it
	for (var i=0;i<spans.length;i++) {
		if(spans[i].className=="smwttcontent"){
			spans[i].style.display="none";
		}
	}
	a.onmouseover=smw_showTooltipInline;
	a.onmouseout=smw_hideTooltip;
}

function smw_makePersistentTooltip(a) {
	var spans = a.getElementsByTagName("span");
	a.className="smwttactivepersist";
	for (var i=0;i<spans.length;i++) {
		if(spans[i].className=="smwtticon"){
			img=document.createElement("img");
			img.setAttribute("src",imagePath+spans[i].innerHTML);
			img.className="smwttimg";
			a.replaceChild(img, a.firstChild);
		}
		//make content invisible
		//done here and not in the css so that non-js clients can see it
		if(spans[i].className=="smwttcontent"){
			spans[i].style.display="none";
		}
	}
	//register event with anchor
	if (BubbleTT.Platform.browser.isIE) {
		a.attachEvent("onclick", smw_showTooltipPersist);
	} else {
		a.addEventListener("click", smw_showTooltipPersist, false);
	}
}

/*display tooltip*/
function smw_showTooltipPersist(e) {
	var x; 
	var y; 
	if(BubbleTT.Platform.browser.isIE){
		c = BubbleTT.getElementCoordinates(window.event.srcElement);
		x = c.left;
		y = c.top;
	}else{
		x = e.pageX;
		y = e.pageY;
	}
	var origin = (BubbleTT.Platform.browser.isIE) ? window.event.srcElement : e.target;
	//If the anchor of the tooltip contains hmtl, the source of the event is not the anchor.
	//As we need a reference to it to get the tooltip content we need to go up the dom-tree.
	while(!(origin.className=="smwttactivepersist")){origin=origin.parentNode};

	tt = BubbleTT.createBubbleForPoint(true,origin,x,y,SMWTT_WIDTH_P,SMWTT_HEIGHT_P);
	all_tt.push(tt);
	BubbleTT.fillBubble(tt, origin);

	//unregister handler to open bubble 
	if (BubbleTT.Platform.browser.isIE) {
		origin.detachEvent("onclick", smw_showTooltipPersist);
	} else {
		origin.removeEventListener("click", smw_showTooltipPersist, false);
	}
}



function smw_showTooltipInline(e) {
	if (tt != null) { // show only one tooltip at a time
		return;
	}
	var x;
	var y;
	if(BubbleTT.Platform.browser.isIE){
		c = BubbleTT.getElementCoordinates(window.event.srcElement);
		x = c.left;
		y = c.top;
	}else{
		x = e.pageX;
		y = e.pageY;
	}
	var origin = (BubbleTT.Platform.browser.isIE) ? window.event.srcElement : e.target;
	//If the anchor of the tooltip contains hmtl, the source of the event is not the anchor.
	//As we need a reference to it to get the tooltip content we need to go up the dom-tree.
	while(!(origin.className=="smwttactiveinline"))origin=origin.parentNode;
	var doc = origin.ownerDocument;
	tt = BubbleTT.createBubbleForPoint(false,origin,x,y,SMWTT_WIDTH_I,SMWTT_HEIGHT_I);
	BubbleTT.fillBubble(tt, origin);
}

function smw_hideTooltip(){
	if (tt) {
		tt.close();
		tt = null;
	}
}

/**
 * Provided for the convenience of SMW extensions, used, e.g., by Halo
 */
function _smw_hideAllTooltips() {
	for(var i = 0; i < all_tt.length; i++) {
		all_tt[i].close();
	}
	all_tt = [];
}

/**
 * gets the coordinates of the element elmt
 * used to place tooltips in IE as mouse coordinates
 * behave strangely
 */
BubbleTT.getElementCoordinates = function(elmt) {
	var left = 0;
	var top = 0;

	if (elmt.nodeType != 1) {
		elmt = elmt.parentNode;
	}

	while (elmt != null) {
		left += elmt.offsetLeft;
		top += elmt.offsetTop - (elmt.scrollTop ? elmt.scrollTop : 0);
		elmt = elmt.offsetParent;
	}
	// consider document scroll position too
	top += document.documentElement.scrollTop;
	return { left: left, top: top };
};


/*==================================================================
 * code below from Simile-Timeline (util/graphics.js) modified 
 *==================================================================
 */


BubbleTT._bubbleMargins = {
	top:      33,
	bottom:   42,
	left:     33,
	right:    40
}

/*pixels from boundary of the whole bubble div to the tip of the arrow*/
BubbleTT._arrowOffsets = { 
	top:      0,
	bottom:   9,
	left:     1,
	right:    8
}

BubbleTT._bubblePadding = 15;
BubbleTT._bubblePointOffset = 15;
BubbleTT._halfArrowWidth = 18;



/*creates an empty bubble*/
BubbleTT.createBubbleForPoint = function(closingButton, origin, pageX, pageY, contentWidth, contentHeight) {
	var doc = origin.ownerDocument; 
	var bubble = {
		_closed:    false,
		_doc:       doc,
		close:      function() { 
			if (!this._closed) {
				this._doc.body.removeChild(this._div);
				this._doc = null;
				this._div = null;
				this._content = null;
				this._closed = true;
			if(closingButton){//for persistent bubble: re-attach handler to open bubble again
			if (BubbleTT.Platform.browser.isIE) {
					origin.attachEvent("onclick", smw_showTooltipPersist);
				} else {
					origin.addEventListener("click", smw_showTooltipPersist, false);
			}
		}
			}
		}
	};

	var docWidth = doc.body.offsetWidth;
	var docHeight = doc.body.offsetHeight;

	var margins = BubbleTT._bubbleMargins;
	var bubbleWidth = margins.left + contentWidth + margins.right;
	var bubbleHeight = margins.top + contentHeight + margins.bottom;

	var pngIsTranslucent =  (!BubbleTT.Platform.browser.isIE) || (BubbleTT.Platform.browser.majorVersion > 6);    

	var setImg = function(elmt, url, width, height) {
		elmt.style.position = "absolute";
		elmt.style.width = width + "px";
		elmt.style.height = height + "px";
		if (pngIsTranslucent) {
			elmt.style.background = "url(" + url + ")";
		} else {
			elmt.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + url +"', sizingMethod='crop')";
		}
	}
	var div = doc.createElement("div");
	div.style.width = bubbleWidth + "px";
	div.style.height = bubbleHeight + "px";
	div.style.position = "absolute";
	div.style.zIndex = 1000;
	bubble._div = div;

	var divInner = doc.createElement("div");
	divInner.style.width = "100%";
	divInner.style.height = "100%";
	divInner.style.position = "relative";
	div.appendChild(divInner);

	var createImg = function(url, left, top, width, height) {
		var divImg = doc.createElement("div");
		divImg.style.left = left + "px";
		divImg.style.top = top + "px";
		setImg(divImg, url, width, height);
		divInner.appendChild(divImg);
	}

	createImg(imagePath + "bubble-top-left.png", 0, 0, margins.left, margins.top);
	createImg(imagePath + "bubble-top.png", margins.left, 0, contentWidth, margins.top);
	createImg(imagePath + "bubble-top-right.png", margins.left + contentWidth, 0, margins.right, margins.top);

	createImg(imagePath + "bubble-left.png", 0, margins.top, margins.left, contentHeight);
	createImg(imagePath + "bubble-right.png", margins.left + contentWidth, margins.top, margins.right, contentHeight);

	createImg(imagePath + "bubble-bottom-left.png", 0, margins.top + contentHeight, margins.left, margins.bottom);
	createImg(imagePath + "bubble-bottom.png", margins.left, margins.top + contentHeight, contentWidth, margins.bottom);
	createImg(imagePath + "bubble-bottom-right.png", margins.left + contentWidth, margins.top + contentHeight, margins.right, margins.bottom);

	//closing button
	if(closingButton){
		var divClose = doc.createElement("div");
		divClose.style.left = (bubbleWidth - margins.right + BubbleTT._bubblePadding - 16 - 2) + "px";
		divClose.style.top = (margins.top - BubbleTT._bubblePadding + 1) + "px";
		divClose.style.cursor = "pointer";
		setImg(divClose, imagePath + "close-button.png", 16, 16);
		BubbleTT.DOM.registerEventWithObject(divClose, "click", bubble, bubble.close);
		divInner.appendChild(divClose);
	}

	var divContent = doc.createElement("div");
	divContent.style.position = "absolute";
	divContent.style.left = margins.left + "px";
	divContent.style.top = margins.top + "px";
	divContent.style.width = contentWidth + "px";
	divContent.style.height = contentHeight + "px";
	divContent.style.overflow = "auto";
	divContent.style.background = "white";
	divInner.appendChild(divContent);
	bubble.content = divContent;

	(function() {
		if (pageX - BubbleTT._halfArrowWidth - BubbleTT._bubblePadding > 0 &&
			pageX + BubbleTT._halfArrowWidth + BubbleTT._bubblePadding < docWidth) {
			
			var left = pageX - Math.round(contentWidth / 2) - margins.left;
			left = pageX < (docWidth / 2) ?
				Math.max(left, -(margins.left - BubbleTT._bubblePadding)) : 
				Math.min(left, docWidth + (margins.right - BubbleTT._bubblePadding) - bubbleWidth);
				
			if (pageY - BubbleTT._bubblePointOffset - bubbleHeight > 0) { // top
				var divImg = doc.createElement("div");
				
				divImg.style.left = (pageX - BubbleTT._halfArrowWidth - left) + "px";
				divImg.style.top = (margins.top + contentHeight) + "px";
				setImg(divImg, imagePath + "bubble-bottom-arrow.png", 37, margins.bottom);
				divInner.appendChild(divImg);
				
				div.style.left = left + "px";
				div.style.top = (pageY - BubbleTT._bubblePointOffset - bubbleHeight + 
					BubbleTT._arrowOffsets.bottom) + "px";
				
				return;
			} else if (pageY + BubbleTT._bubblePointOffset + bubbleHeight < docHeight) { // bottom
				var divImg = doc.createElement("div");
				
				divImg.style.left = (pageX - BubbleTT._halfArrowWidth - left) + "px";
				divImg.style.top = "0px";
				setImg(divImg, imagePath + "bubble-top-arrow.png", 37, margins.top);
				divInner.appendChild(divImg);
				
				div.style.left = left + "px";
				div.style.top = (pageY + BubbleTT._bubblePointOffset - 
					BubbleTT._arrowOffsets.top) + "px";
				
				return;
			}
		}

		var top = pageY - Math.round(contentHeight / 2) - margins.top;
		top = pageY < (docHeight / 2) ?
			Math.max(top, -(margins.top - BubbleTT._bubblePadding)) : 
			Math.min(top, docHeight + (margins.bottom - BubbleTT._bubblePadding) - bubbleHeight);
				
		if (pageX - BubbleTT._bubblePointOffset - bubbleWidth > 0) { // left
			var divImg = doc.createElement("div");
			
			divImg.style.left = (margins.left + contentWidth) + "px";
			divImg.style.top = (pageY - BubbleTT._halfArrowWidth - top) + "px";
			setImg(divImg, imagePath + "bubble-right-arrow.png", margins.right, 37);
			divInner.appendChild(divImg);
			
			div.style.left = (pageX - BubbleTT._bubblePointOffset - bubbleWidth +
				BubbleTT._arrowOffsets.right) + "px";
			div.style.top = top + "px";
		} else { // right
			var divImg = doc.createElement("div");
			
			divImg.style.left = "0px";
			divImg.style.top = (pageY - BubbleTT._halfArrowWidth - top) + "px";
			setImg(divImg, imagePath + "bubble-left-arrow.png", margins.left, 37);
			divInner.appendChild(divImg);
			
			div.style.left = (pageX + BubbleTT._bubblePointOffset - 
				BubbleTT._arrowOffsets.left) + "px";
			div.style.top = top + "px";
		}
	})();

	doc.body.appendChild(div);
	return bubble;
};



/*fill bubble with html content*/
BubbleTT.fillBubble = function(bubble,origin){
	doc=bubble._doc;
	div = doc.createElement("div");
	div.className = "smwtt";
	//get tooltip content 
	spans=origin.getElementsByTagName("span");
	for (i=0; i<spans.length; i++){
		/* "\n" and "<!--br-->" are replaced by "<br />" to support linebreaks 
		 * in tooltips without corrupting the page for non js-clients.
		 */
		if(spans[i].className=="smwttcontent") {
			div.innerHTML=spans[i].innerHTML.replace(/\n/g,"<br />");
			div.innerHTML=spans[i].innerHTML.replace(/<!--br-->/g,"<br />");
		}
	}
	bubble.content.appendChild(div);
}


/*==================================================================
 * all below from Simile-Timeline (util/platform.js) with classname
 * Timeline replaced by BubbleTT to avoid complications with both 
 * scripts running on the same page
 *==================================================================
 */


BubbleTT.Platform.os = {
	isMac:   false,
	isWin:   false,
	isWin32: false,
	isUnix:  false
};
BubbleTT.Platform.browser = {
	isIE:           false,
	isNetscape:     false,
	isMozilla:      false,
	isFirefox:      false,
	isOpera:        false,
	isSafari:       false,
	
	majorVersion:   0,
	minorVersion:   0
};

(function() {
	var an = navigator.appName.toLowerCase();
	var ua = navigator.userAgent.toLowerCase(); 

	/*
	 *  Operating system
	 */
	BubbleTT.Platform.os.isMac = (ua.indexOf('mac') != -1);
	BubbleTT.Platform.os.isWin = (ua.indexOf('win') != -1);
	BubbleTT.Platform.os.isWin32 = BubbleTT.Platform.isWin && (
		ua.indexOf('95') != -1 || 
		ua.indexOf('98') != -1 || 
		ua.indexOf('nt') != -1 || 
		ua.indexOf('win32') != -1 || 
		ua.indexOf('32bit') != -1
	);
	BubbleTT.Platform.os.isUnix = (ua.indexOf('x11') != -1);

	/*
	 *  Browser
	 */
	BubbleTT.Platform.browser.isIE = (an.indexOf("microsoft") != -1);
	BubbleTT.Platform.browser.isNetscape = (an.indexOf("netscape") != -1);
	BubbleTT.Platform.browser.isMozilla = (ua.indexOf("mozilla") != -1);
	BubbleTT.Platform.browser.isFirefox = (ua.indexOf("firefox") != -1);
	BubbleTT.Platform.browser.isOpera = (an.indexOf("opera") != -1);
	//BubbleTT.Platform.browser.isSafari = (an.indexOf("safari") != -1);

	var parseVersionString = function(s) {
		var a = s.split(".");
		BubbleTT.Platform.browser.majorVersion = parseInt(a[0]);
		BubbleTT.Platform.browser.minorVersion = parseInt(a[1]);
	};
	var indexOf = function(s, sub, start) {
		var i = s.indexOf(sub, start);
		return i >= 0 ? i : s.length;
	};

	if (BubbleTT.Platform.browser.isMozilla) {
		var offset = ua.indexOf("mozilla/");
		if (offset >= 0) {
			parseVersionString(ua.substring(offset + 8, indexOf(ua, " ", offset)));
		}
	}
	if (BubbleTT.Platform.browser.isIE) {
		var offset = ua.indexOf("msie ");
		if (offset >= 0) {
			parseVersionString(ua.substring(offset + 5, indexOf(ua, ";", offset)));
		}
	}
	if (BubbleTT.Platform.browser.isNetscape) {
		var offset = ua.indexOf("rv:");
		if (offset >= 0) {
			parseVersionString(ua.substring(offset + 3, indexOf(ua, ")", offset)));
		}
	}
	if (BubbleTT.Platform.browser.isFirefox) {
		var offset = ua.indexOf("firefox/");
		if (offset >= 0) {
			parseVersionString(ua.substring(offset + 8, indexOf(ua, " ", offset)));
		}
	}
})();

BubbleTT.Platform.getDefaultLocale = function() {
	return BubbleTT.Platform.clientLocale;
};

/*==================================================
 *  DOM Utility Functions
 * all below from Simile-Timeline (util/dom.js) with classname
 * Timeline replaced by BubbleTT to avoid complications with both 
 * scripts running on the same page
 *==================================================
 */

BubbleTT.DOM = new Object();

BubbleTT.DOM.registerEventWithObject = function(elmt, eventName, obj, handler) {
	BubbleTT.DOM.registerEvent(elmt, eventName, function(elmt2, evt, target) {
		return handler.call(obj, elmt2, evt, target);
	});
};

BubbleTT.DOM.registerEvent = function(elmt, eventName, handler) {
	var handler2 = function(evt) {
		evt = (evt) ? evt : ((event) ? event : null);
		if (evt) {
			var target = (evt.target) ? 
				evt.target : ((evt.srcElement) ? evt.srcElement : null);
			if (target) {
				target = (target.nodeType == 1 || target.nodeType == 9) ? 
					target : target.parentNode;
			}
			
			return handler(elmt, evt, target);
		}
		return true;
	}

	if (BubbleTT.Platform.browser.isIE) {
		elmt.attachEvent("on" + eventName, handler2);
	} else {
		elmt.addEventListener(eventName, handler2, false);
	}
};


