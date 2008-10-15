
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


// slider.js
// under MIT-License; Copyright (c) 2005, 2006 Thomas Fuchs
// script.aculo.us slider.js v1.8.0, Tue Nov 06 15:01:40 +0300 2007

// Copyright (c) 2005-2007 Marty Haught, Thomas Fuchs 
//
// script.aculo.us is freely distributable under the terms of an MIT-style license.
// For details, see the script.aculo.us web site: http://script.aculo.us/

if (!Control) var Control = { };

// options:
//  axis: 'vertical', or 'horizontal' (default)
//
// callbacks:
//  onChange(value)
//  onSlide(value)
Control.Slider = Class.create({
  initialize: function(handle, track, options) {
    var slider = this;
    
    if (Object.isArray(handle)) {
      this.handles = handle.collect( function(e) { return $(e) });
    } else {
      this.handles = [$(handle)];
    }
    
    this.track   = $(track);
    this.options = options || { };

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

    if (this.options.disabled) this.setDisabled();

    // Allowed values array
    this.allowedValues = this.options.values ? this.options.values.sortBy(Prototype.K) : false;
    if (this.allowedValues) {
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
        (Object.isArray(slider.options.sliderValue) ? 
          slider.options.sliderValue[i] : slider.options.sliderValue) || 
         slider.range.start), i);
      h.makePositioned().observe("mousedown", slider.eventMouseDown);
    });
    
    this.track.observe("mousedown", this.eventMouseDown);
    document.observe("mouseup", this.eventMouseUp);
    document.observe("mousemove", this.eventMouseMove);
    
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
    if (this.allowedValues){
      if (value >= this.allowedValues.max()) return(this.allowedValues.max());
      if (value <= this.allowedValues.min()) return(this.allowedValues.min());
      
      var offset = Math.abs(this.allowedValues[0] - value);
      var newValue = this.allowedValues[0];
      this.allowedValues.each( function(v) {
        var currentOffset = Math.abs(v - value);
        if (currentOffset <= offset){
          newValue = v;
          offset = currentOffset;
        } 
      });
      return newValue;
    }
    if (value > this.range.end) return this.range.end;
    if (value < this.range.start) return this.range.start;
    return value;
  },
  setValue: function(sliderValue, handleIdx){
    if (!this.active) {
      this.activeHandleIdx = handleIdx || 0;
      this.activeHandle    = this.handles[this.activeHandleIdx];
      this.updateStyles();
    }
    handleIdx = handleIdx || this.activeHandleIdx || 0;
    if (this.initialized && this.restricted) {
      if ((handleIdx>0) && (sliderValue<this.values[handleIdx-1]))
        sliderValue = this.values[handleIdx-1];
      if ((handleIdx < (this.handles.length-1)) && (sliderValue>this.values[handleIdx+1]))
        sliderValue = this.values[handleIdx+1];
    }
    sliderValue = this.getNearestValue(sliderValue);
    this.values[handleIdx] = sliderValue;
    this.value = this.values[0]; // assure backwards compat
    
    this.handles[handleIdx].style[this.isVertical() ? 'top' : 'left'] = 
      this.translateToPx(sliderValue);
    
    this.drawSpans();
    if (!this.dragging || !this.event) this.updateFinished();
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
        this.track.style.width.replace(/px$/,"")) - this.alignX);
  },  
  isVertical:  function(){
    return (this.axis == 'vertical');
  },
  drawSpans: function() {
    var slider = this;
    if (this.spans)
      $R(0, this.spans.length-1).each(function(r) { slider.setSpan(slider.spans[r], slider.getRange(r)) });
    if (this.options.startSpan)
      this.setSpan(this.options.startSpan,
        $R(0, this.values.length>1 ? this.getRange(0).min() : this.value ));
    if (this.options.endSpan)
      this.setSpan(this.options.endSpan, 
        $R(this.values.length>1 ? this.getRange(this.spans.length-1).max() : this.value, this.maximum));
  },
  setSpan: function(span, range) {
    if (this.isVertical()) {
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
    if (Event.isLeftClick(event)) {
      if (!this.disabled){
        this.active = true;
        
        var handle = Event.element(event);
        var pointer  = [Event.pointerX(event), Event.pointerY(event)];
        var track = handle;
        if (track==this.track) {
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
            
          if (this.handles.indexOf(handle)!=-1) {
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
   if (this.active) {
      if (!this.dragging) this.dragging = true;
      this.draw(event);
      if (Prototype.Browser.WebKit) window.scrollBy(0,0);
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
    if (this.initialized && this.options.onSlide)
      this.options.onSlide(this.values.length>1 ? this.values : this.value, this);
  },
  endDrag: function(event) {
    if (this.active && this.dragging) {
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
    if (this.initialized && this.options.onChange) 
      this.options.onChange(this.values.length>1 ? this.values : this.value, this);
    this.event = null;
  }
});


// dragdrop.js
// under MIT-License; Copyright (c) 2005, 2006 Thomas Fuchs
// script.aculo.us dragdrop.js v1.8.0, Tue Nov 06 15:01:40 +0300 2007

// Copyright (c) 2005-2007 Thomas Fuchs (http://script.aculo.us, http://mir.aculo.us)
//           (c) 2005-2007 Sammi Williams (http://www.oriontransfer.co.nz, sammi@oriontransfer.co.nz)
// 
// script.aculo.us is freely distributable under the terms of an MIT-style license.
// For details, see the script.aculo.us web site: http://script.aculo.us/

if(Object.isUndefined(Effect))
  throw("dragdrop.js requires including script.aculo.us' effects.js library");

var Droppables = {
  drops: [],

  remove: function(element) {
    this.drops = this.drops.reject(function(d) { return d.element==$(element) });
  },

  add: function(element) {
    element = $(element);
    var options = Object.extend({
      greedy:     true,
      hoverclass: null,
      tree:       false
    }, arguments[1] || { });

    // cache containers
    if(options.containment) {
      options._containers = [];
      var containment = options.containment;
      if(Object.isArray(containment)) {
        containment.each( function(c) { options._containers.push($(c)) });
      } else {
        options._containers.push($(containment));
      }
    }
    
    if(options.accept) options.accept = [options.accept].flatten();

    Element.makePositioned(element); // fix IE
    options.element = element;

    this.drops.push(options);
  },
  
  findDeepestChild: function(drops) {
    deepest = drops[0];
      
    for (i = 1; i < drops.length; ++i)
      if (Element.isParent(drops[i].element, deepest.element))
        deepest = drops[i];
    
    return deepest;
  },

  isContained: function(element, drop) {
    var containmentNode;
    if(drop.tree) {
      containmentNode = element.treeNode; 
    } else {
      containmentNode = element.parentNode;
    }
    return drop._containers.detect(function(c) { return containmentNode == c });
  },
  
  isAffected: function(point, element, drop) {
    return (
      (drop.element!=element) &&
      ((!drop._containers) ||
        this.isContained(element, drop)) &&
      ((!drop.accept) ||
        (Element.classNames(element).detect( 
          function(v) { return drop.accept.include(v) } ) )) &&
      Position.withinIncludingScrolloffsets(drop.element, point[0], point[1]) );
  },

  deactivate: function(drop) {
    if(drop.hoverclass)
      Element.removeClassName(drop.element, drop.hoverclass);
    this.last_active = null;
  },

  activate: function(drop) {
    if(drop.hoverclass)
      Element.addClassName(drop.element, drop.hoverclass);
    this.last_active = drop;
  },

  show: function(point, element) {
    if(!this.drops.length) return;
    var drop, affected = [];
    
    this.drops.each( function(drop) {
      if(Droppables.isAffected(point, element, drop))
        affected.push(drop);
    });
        
    if(affected.length>0)
      drop = Droppables.findDeepestChild(affected);

    if(this.last_active && this.last_active != drop) this.deactivate(this.last_active);
    if (drop) {
      Position.withinIncludingScrolloffsets(drop.element, point[0], point[1]);
      if(drop.onHover)
        drop.onHover(element, drop.element, Position.overlap(drop.overlap, drop.element));
      
      if (drop != this.last_active) Droppables.activate(drop);
    }
  },

  fire: function(event, element) {
    if(!this.last_active) return;
    Position.prepare();

    if (this.isAffected([Event.pointerX(event), Event.pointerY(event)], element, this.last_active))
      if (this.last_active.onDrop) {
        this.last_active.onDrop(element, this.last_active.element, event); 
        return true; 
      }
  },

  reset: function() {
    if(this.last_active)
      this.deactivate(this.last_active);
  }
}

var Draggables = {
  drags: [],
  observers: [],
  
  register: function(draggable) {
    if(this.drags.length == 0) {
      this.eventMouseUp   = this.endDrag.bindAsEventListener(this);
      this.eventMouseMove = this.updateDrag.bindAsEventListener(this);
      this.eventKeypress  = this.keyPress.bindAsEventListener(this);
      
      Event.observe(document, "mouseup", this.eventMouseUp);
      Event.observe(document, "mousemove", this.eventMouseMove);
      Event.observe(document, "keypress", this.eventKeypress);
    }
    this.drags.push(draggable);
  },
  
  unregister: function(draggable) {
    this.drags = this.drags.reject(function(d) { return d==draggable });
    if(this.drags.length == 0) {
      Event.stopObserving(document, "mouseup", this.eventMouseUp);
      Event.stopObserving(document, "mousemove", this.eventMouseMove);
      Event.stopObserving(document, "keypress", this.eventKeypress);
    }
  },
  
  activate: function(draggable) {
    if(draggable.options.delay) { 
      this._timeout = setTimeout(function() { 
        Draggables._timeout = null; 
        window.focus(); 
        Draggables.activeDraggable = draggable; 
      }.bind(this), draggable.options.delay); 
    } else {
      window.focus(); // allows keypress events if window isn't currently focused, fails for Safari
      this.activeDraggable = draggable;
    }
  },
  
  deactivate: function() {
    this.activeDraggable = null;
  },
  
  updateDrag: function(event) {
    if(!this.activeDraggable) return;
    var pointer = [Event.pointerX(event), Event.pointerY(event)];
    // Mozilla-based browsers fire successive mousemove events with
    // the same coordinates, prevent needless redrawing (moz bug?)
    if(this._lastPointer && (this._lastPointer.inspect() == pointer.inspect())) return;
    this._lastPointer = pointer;
    
    this.activeDraggable.updateDrag(event, pointer);
  },
  
  endDrag: function(event) {
    if(this._timeout) { 
      clearTimeout(this._timeout); 
      this._timeout = null; 
    }
    if(!this.activeDraggable) return;
    this._lastPointer = null;
    this.activeDraggable.endDrag(event);
    this.activeDraggable = null;
  },
  
  keyPress: function(event) {
    if(this.activeDraggable)
      this.activeDraggable.keyPress(event);
  },
  
  addObserver: function(observer) {
    this.observers.push(observer);
    this._cacheObserverCallbacks();
  },
  
  removeObserver: function(element) {  // element instead of observer fixes mem leaks
    this.observers = this.observers.reject( function(o) { return o.element==element });
    this._cacheObserverCallbacks();
  },
  
  notify: function(eventName, draggable, event) {  // 'onStart', 'onEnd', 'onDrag'
    if(this[eventName+'Count'] > 0)
      this.observers.each( function(o) {
        if(o[eventName]) o[eventName](eventName, draggable, event);
      });
    if(draggable.options[eventName]) draggable.options[eventName](draggable, event);
  },
  
  _cacheObserverCallbacks: function() {
    ['onStart','onEnd','onDrag'].each( function(eventName) {
      Draggables[eventName+'Count'] = Draggables.observers.select(
        function(o) { return o[eventName]; }
      ).length;
    });
  }
}

/*--------------------------------------------------------------------------*/

var Draggable = Class.create({
  initialize: function(element) {
    var defaults = {
      handle: false,
      reverteffect: function(element, top_offset, left_offset) {
        var dur = Math.sqrt(Math.abs(top_offset^2)+Math.abs(left_offset^2))*0.02;
        new Effect.Move(element, { x: -left_offset, y: -top_offset, duration: dur,
          queue: {scope:'_draggable', position:'end'}
        });
      },
      endeffect: function(element) {
        var toOpacity = Object.isNumber(element._opacity) ? element._opacity : 1.0;
        new Effect.Opacity(element, {duration:0.2, from:0.7, to:toOpacity, 
          queue: {scope:'_draggable', position:'end'},
          afterFinish: function(){ 
            Draggable._dragging[element] = false 
          }
        }); 
      },
      zindex: 1000,
      revert: false,
      quiet: false,
      scroll: false,
      scrollSensitivity: 20,
      scrollSpeed: 15,
      snap: false,  // false, or xy or [x,y] or function(x,y){ return [x,y] }
      delay: 0
    };
    
    if(!arguments[1] || Object.isUndefined(arguments[1].endeffect))
      Object.extend(defaults, {
        starteffect: function(element) {
          element._opacity = Element.getOpacity(element);
          Draggable._dragging[element] = true;
          new Effect.Opacity(element, {duration:0.2, from:element._opacity, to:0.7}); 
        }
      });
    
    var options = Object.extend(defaults, arguments[1] || { });

    this.element = $(element);
    
    if(options.handle && Object.isString(options.handle))
      this.handle = this.element.down('.'+options.handle, 0);
    
    if(!this.handle) this.handle = $(options.handle);
    if(!this.handle) this.handle = this.element;
    
    if(options.scroll && !options.scroll.scrollTo && !options.scroll.outerHTML) {
      options.scroll = $(options.scroll);
      this._isScrollChild = Element.childOf(this.element, options.scroll);
    }

    Element.makePositioned(this.element); // fix IE    

    this.options  = options;
    this.dragging = false;   

    this.eventMouseDown = this.initDrag.bindAsEventListener(this);
    Event.observe(this.handle, "mousedown", this.eventMouseDown);
    
    Draggables.register(this);
  },
  
  destroy: function() {
    Event.stopObserving(this.handle, "mousedown", this.eventMouseDown);
    Draggables.unregister(this);
  },
  
  currentDelta: function() {
    return([
      parseInt(Element.getStyle(this.element,'left') || '0'),
      parseInt(Element.getStyle(this.element,'top') || '0')]);
  },
  
  initDrag: function(event) {
    if(!Object.isUndefined(Draggable._dragging[this.element]) &&
      Draggable._dragging[this.element]) return;
    if(Event.isLeftClick(event)) {    
      // abort on form elements, fixes a Firefox issue
      var src = Event.element(event);
      if((tag_name = src.tagName.toUpperCase()) && (
        tag_name=='INPUT' ||
        tag_name=='SELECT' ||
        tag_name=='OPTION' ||
        tag_name=='BUTTON' ||
        tag_name=='TEXTAREA')) return;
        
      var pointer = [Event.pointerX(event), Event.pointerY(event)];
      var pos     = Position.cumulativeOffset(this.element);
      this.offset = [0,1].map( function(i) { return (pointer[i] - pos[i]) });
      
      Draggables.activate(this);
      Event.stop(event);
    }
  },
  
  startDrag: function(event) {
    this.dragging = true;
    if(!this.delta)
      this.delta = this.currentDelta();
    
    if(this.options.zindex) {
      this.originalZ = parseInt(Element.getStyle(this.element,'z-index') || 0);
      this.element.style.zIndex = this.options.zindex;
    }
    
    if(this.options.ghosting) {
      this._clone = this.element.cloneNode(true);
      this.element._originallyAbsolute = (this.element.getStyle('position') == 'absolute');
      if (!this.element._originallyAbsolute)
        Position.absolutize(this.element);
      this.element.parentNode.insertBefore(this._clone, this.element);
    }
    
    if(this.options.scroll) {
      if (this.options.scroll == window) {
        var where = this._getWindowScroll(this.options.scroll);
        this.originalScrollLeft = where.left;
        this.originalScrollTop = where.top;
      } else {
        this.originalScrollLeft = this.options.scroll.scrollLeft;
        this.originalScrollTop = this.options.scroll.scrollTop;
      }
    }
    
    Draggables.notify('onStart', this, event);
        
    if(this.options.starteffect) this.options.starteffect(this.element);
  },
  
  updateDrag: function(event, pointer) {
    if(!this.dragging) this.startDrag(event);
    
    if(!this.options.quiet){
      Position.prepare();
      Droppables.show(pointer, this.element);
    }
    
    Draggables.notify('onDrag', this, event);
    
    this.draw(pointer);
    if(this.options.change) this.options.change(this);
    
    if(this.options.scroll) {
      this.stopScrolling();
      
      var p;
      if (this.options.scroll == window) {
        with(this._getWindowScroll(this.options.scroll)) { p = [ left, top, left+width, top+height ]; }
      } else {
        p = Position.page(this.options.scroll);
        p[0] += this.options.scroll.scrollLeft + Position.deltaX;
        p[1] += this.options.scroll.scrollTop + Position.deltaY;
        p.push(p[0]+this.options.scroll.offsetWidth);
        p.push(p[1]+this.options.scroll.offsetHeight);
      }
      var speed = [0,0];
      if(pointer[0] < (p[0]+this.options.scrollSensitivity)) speed[0] = pointer[0]-(p[0]+this.options.scrollSensitivity);
      if(pointer[1] < (p[1]+this.options.scrollSensitivity)) speed[1] = pointer[1]-(p[1]+this.options.scrollSensitivity);
      if(pointer[0] > (p[2]-this.options.scrollSensitivity)) speed[0] = pointer[0]-(p[2]-this.options.scrollSensitivity);
      if(pointer[1] > (p[3]-this.options.scrollSensitivity)) speed[1] = pointer[1]-(p[3]-this.options.scrollSensitivity);
      this.startScrolling(speed);
    }
    
    // fix AppleWebKit rendering
    if(Prototype.Browser.WebKit) window.scrollBy(0,0);
    
    Event.stop(event);
  },
  
  finishDrag: function(event, success) {
    this.dragging = false;
    
    if(this.options.quiet){
      Position.prepare();
      var pointer = [Event.pointerX(event), Event.pointerY(event)];
      Droppables.show(pointer, this.element);
    }

    if(this.options.ghosting) {
      if (!this.element._originallyAbsolute)
        Position.relativize(this.element);
      delete this.element._originallyAbsolute;
      Element.remove(this._clone);
      this._clone = null;
    }

    var dropped = false; 
    if(success) { 
      dropped = Droppables.fire(event, this.element); 
      if (!dropped) dropped = false; 
    }
    if(dropped && this.options.onDropped) this.options.onDropped(this.element);
    Draggables.notify('onEnd', this, event);

    var revert = this.options.revert;
    if(revert && Object.isFunction(revert)) revert = revert(this.element);
    
    var d = this.currentDelta();
    if(revert && this.options.reverteffect) {
      if (dropped == 0 || revert != 'failure')
        this.options.reverteffect(this.element,
          d[1]-this.delta[1], d[0]-this.delta[0]);
    } else {
      this.delta = d;
    }

    if(this.options.zindex)
      this.element.style.zIndex = this.originalZ;

    if(this.options.endeffect) 
      this.options.endeffect(this.element);
      
    Draggables.deactivate(this);
    Droppables.reset();
  },
  
  keyPress: function(event) {
    if(event.keyCode!=Event.KEY_ESC) return;
    this.finishDrag(event, false);
    Event.stop(event);
  },
  
  endDrag: function(event) {
    if(!this.dragging) return;
    this.stopScrolling();
    this.finishDrag(event, true);
    Event.stop(event);
  },
  
  draw: function(point) {
    var pos = Position.cumulativeOffset(this.element);
    if(this.options.ghosting) {
      var r   = Position.realOffset(this.element);
      pos[0] += r[0] - Position.deltaX; pos[1] += r[1] - Position.deltaY;
    }
    
    var d = this.currentDelta();
    pos[0] -= d[0]; pos[1] -= d[1];
    
    if(this.options.scroll && (this.options.scroll != window && this._isScrollChild)) {
      pos[0] -= this.options.scroll.scrollLeft-this.originalScrollLeft;
      pos[1] -= this.options.scroll.scrollTop-this.originalScrollTop;
    }
    
    var p = [0,1].map(function(i){ 
      return (point[i]-pos[i]-this.offset[i]) 
    }.bind(this));
    
    if(this.options.snap) {
      if(Object.isFunction(this.options.snap)) {
        p = this.options.snap(p[0],p[1],this);
      } else {
      if(Object.isArray(this.options.snap)) {
        p = p.map( function(v, i) {
          return (v/this.options.snap[i]).round()*this.options.snap[i] }.bind(this))
      } else {
        p = p.map( function(v) {
          return (v/this.options.snap).round()*this.options.snap }.bind(this))
      }
    }}
    
    var style = this.element.style;
    if((!this.options.constraint) || (this.options.constraint=='horizontal'))
      style.left = p[0] + "px";
    if((!this.options.constraint) || (this.options.constraint=='vertical'))
      style.top  = p[1] + "px";
    
    if(style.visibility=="hidden") style.visibility = ""; // fix gecko rendering
  },
  
  stopScrolling: function() {
    if(this.scrollInterval) {
      clearInterval(this.scrollInterval);
      this.scrollInterval = null;
      Draggables._lastScrollPointer = null;
    }
  },
  
  startScrolling: function(speed) {
    if(!(speed[0] || speed[1])) return;
    this.scrollSpeed = [speed[0]*this.options.scrollSpeed,speed[1]*this.options.scrollSpeed];
    this.lastScrolled = new Date();
    this.scrollInterval = setInterval(this.scroll.bind(this), 10);
  },
  
  scroll: function() {
    var current = new Date();
    var delta = current - this.lastScrolled;
    this.lastScrolled = current;
    if(this.options.scroll == window) {
      with (this._getWindowScroll(this.options.scroll)) {
        if (this.scrollSpeed[0] || this.scrollSpeed[1]) {
          var d = delta / 1000;
          this.options.scroll.scrollTo( left + d*this.scrollSpeed[0], top + d*this.scrollSpeed[1] );
        }
      }
    } else {
      this.options.scroll.scrollLeft += this.scrollSpeed[0] * delta / 1000;
      this.options.scroll.scrollTop  += this.scrollSpeed[1] * delta / 1000;
    }
    
    Position.prepare();
    Droppables.show(Draggables._lastPointer, this.element);
    Draggables.notify('onDrag', this);
    if (this._isScrollChild) {
      Draggables._lastScrollPointer = Draggables._lastScrollPointer || $A(Draggables._lastPointer);
      Draggables._lastScrollPointer[0] += this.scrollSpeed[0] * delta / 1000;
      Draggables._lastScrollPointer[1] += this.scrollSpeed[1] * delta / 1000;
      if (Draggables._lastScrollPointer[0] < 0)
        Draggables._lastScrollPointer[0] = 0;
      if (Draggables._lastScrollPointer[1] < 0)
        Draggables._lastScrollPointer[1] = 0;
      this.draw(Draggables._lastScrollPointer);
    }
    
    if(this.options.change) this.options.change(this);
  },
  
  _getWindowScroll: function(w) {
    var T, L, W, H;
    with (w.document) {
      if (w.document.documentElement && documentElement.scrollTop) {
        T = documentElement.scrollTop;
        L = documentElement.scrollLeft;
      } else if (w.document.body) {
        T = body.scrollTop;
        L = body.scrollLeft;
      }
      if (w.innerWidth) {
        W = w.innerWidth;
        H = w.innerHeight;
      } else if (w.document.documentElement && documentElement.clientWidth) {
        W = documentElement.clientWidth;
        H = documentElement.clientHeight;
      } else {
        W = body.offsetWidth;
        H = body.offsetHeight
      }
    }
    return { top: T, left: L, width: W, height: H };
  }
});

Draggable._dragging = { };

/*--------------------------------------------------------------------------*/

var SortableObserver = Class.create({
  initialize: function(element, observer) {
    this.element   = $(element);
    this.observer  = observer;
    this.lastValue = Sortable.serialize(this.element);
  },
  
  onStart: function() {
    this.lastValue = Sortable.serialize(this.element);
  },
  
  onEnd: function() {
    Sortable.unmark();
    if(this.lastValue != Sortable.serialize(this.element))
      this.observer(this.element)
  }
});

var Sortable = {
  SERIALIZE_RULE: /^[^_\-](?:[A-Za-z0-9\-\_]*)[_](.*)$/,
  
  sortables: { },
  
  _findRootElement: function(element) {
    while (element.tagName.toUpperCase() != "BODY") {  
      if(element.id && Sortable.sortables[element.id]) return element;
      element = element.parentNode;
    }
  },

  options: function(element) {
    element = Sortable._findRootElement($(element));
    if(!element) return;
    return Sortable.sortables[element.id];
  },
  
  destroy: function(element){
    var s = Sortable.options(element);
    
    if(s) {
      Draggables.removeObserver(s.element);
      s.droppables.each(function(d){ Droppables.remove(d) });
      s.draggables.invoke('destroy');
      
      delete Sortable.sortables[s.element.id];
    }
  },

  create: function(element) {
    element = $(element);
    var options = Object.extend({ 
      element:     element,
      tag:         'li',       // assumes li children, override with tag: 'tagname'
      dropOnEmpty: false,
      tree:        false,
      treeTag:     'ul',
      overlap:     'vertical', // one of 'vertical', 'horizontal'
      constraint:  'vertical', // one of 'vertical', 'horizontal', false
      containment: element,    // also takes array of elements (or id's); or false
      handle:      false,      // or a CSS class
      only:        false,
      delay:       0,
      hoverclass:  null,
      ghosting:    false,
      quiet:       false, 
      scroll:      false,
      scrollSensitivity: 20,
      scrollSpeed: 15,
      format:      this.SERIALIZE_RULE,
      
      // these take arrays of elements or ids and can be 
      // used for better initialization performance
      elements:    false,
      handles:     false,
      
      onChange:    Prototype.emptyFunction,
      onUpdate:    Prototype.emptyFunction
    }, arguments[1] || { });

    // clear any old sortable with same element
    this.destroy(element);

    // build options for the draggables
    var options_for_draggable = {
      revert:      true,
      quiet:       options.quiet,
      scroll:      options.scroll,
      scrollSpeed: options.scrollSpeed,
      scrollSensitivity: options.scrollSensitivity,
      delay:       options.delay,
      ghosting:    options.ghosting,
      constraint:  options.constraint,
      handle:      options.handle };

    if(options.starteffect)
      options_for_draggable.starteffect = options.starteffect;

    if(options.reverteffect)
      options_for_draggable.reverteffect = options.reverteffect;
    else
      if(options.ghosting) options_for_draggable.reverteffect = function(element) {
        element.style.top  = 0;
        element.style.left = 0;
      };

    if(options.endeffect)
      options_for_draggable.endeffect = options.endeffect;

    if(options.zindex)
      options_for_draggable.zindex = options.zindex;

    // build options for the droppables  
    var options_for_droppable = {
      overlap:     options.overlap,
      containment: options.containment,
      tree:        options.tree,
      hoverclass:  options.hoverclass,
      onHover:     Sortable.onHover
    }
    
    var options_for_tree = {
      onHover:      Sortable.onEmptyHover,
      overlap:      options.overlap,
      containment:  options.containment,
      hoverclass:   options.hoverclass
    }

    // fix for gecko engine
    Element.cleanWhitespace(element); 

    options.draggables = [];
    options.droppables = [];

    // drop on empty handling
    if(options.dropOnEmpty || options.tree) {
      Droppables.add(element, options_for_tree);
      options.droppables.push(element);
    }

    (options.elements || this.findElements(element, options) || []).each( function(e,i) {
      var handle = options.handles ? $(options.handles[i]) :
        (options.handle ? $(e).select('.' + options.handle)[0] : e); 
      options.draggables.push(
        new Draggable(e, Object.extend(options_for_draggable, { handle: handle })));
      Droppables.add(e, options_for_droppable);
      if(options.tree) e.treeNode = element;
      options.droppables.push(e);      
    });
    
    if(options.tree) {
      (Sortable.findTreeElements(element, options) || []).each( function(e) {
        Droppables.add(e, options_for_tree);
        e.treeNode = element;
        options.droppables.push(e);
      });
    }

    // keep reference
    this.sortables[element.id] = options;

    // for onupdate
    Draggables.addObserver(new SortableObserver(element, options.onUpdate));

  },

  // return all suitable-for-sortable elements in a guaranteed order
  findElements: function(element, options) {
    return Element.findChildren(
      element, options.only, options.tree ? true : false, options.tag);
  },
  
  findTreeElements: function(element, options) {
    return Element.findChildren(
      element, options.only, options.tree ? true : false, options.treeTag);
  },

  onHover: function(element, dropon, overlap) {
    if(Element.isParent(dropon, element)) return;

    if(overlap > .33 && overlap < .66 && Sortable.options(dropon).tree) {
      return;
    } else if(overlap>0.5) {
      Sortable.mark(dropon, 'before');
      if(dropon.previousSibling != element) {
        var oldParentNode = element.parentNode;
        element.style.visibility = "hidden"; // fix gecko rendering
        dropon.parentNode.insertBefore(element, dropon);
        if(dropon.parentNode!=oldParentNode) 
          Sortable.options(oldParentNode).onChange(element);
        Sortable.options(dropon.parentNode).onChange(element);
      }
    } else {
      Sortable.mark(dropon, 'after');
      var nextElement = dropon.nextSibling || null;
      if(nextElement != element) {
        var oldParentNode = element.parentNode;
        element.style.visibility = "hidden"; // fix gecko rendering
        dropon.parentNode.insertBefore(element, nextElement);
        if(dropon.parentNode!=oldParentNode) 
          Sortable.options(oldParentNode).onChange(element);
        Sortable.options(dropon.parentNode).onChange(element);
      }
    }
  },
  
  onEmptyHover: function(element, dropon, overlap) {
    var oldParentNode = element.parentNode;
    var droponOptions = Sortable.options(dropon);
        
    if(!Element.isParent(dropon, element)) {
      var index;
      
      var children = Sortable.findElements(dropon, {tag: droponOptions.tag, only: droponOptions.only});
      var child = null;
            
      if(children) {
        var offset = Element.offsetSize(dropon, droponOptions.overlap) * (1.0 - overlap);
        
        for (index = 0; index < children.length; index += 1) {
          if (offset - Element.offsetSize (children[index], droponOptions.overlap) >= 0) {
            offset -= Element.offsetSize (children[index], droponOptions.overlap);
          } else if (offset - (Element.offsetSize (children[index], droponOptions.overlap) / 2) >= 0) {
            child = index + 1 < children.length ? children[index + 1] : null;
            break;
          } else {
            child = children[index];
            break;
          }
        }
      }
      
      dropon.insertBefore(element, child);
      
      Sortable.options(oldParentNode).onChange(element);
      droponOptions.onChange(element);
    }
  },

  unmark: function() {
    if(Sortable._marker) Sortable._marker.hide();
  },

  mark: function(dropon, position) {
    // mark on ghosting only
    var sortable = Sortable.options(dropon.parentNode);
    if(sortable && !sortable.ghosting) return; 

    if(!Sortable._marker) {
      Sortable._marker = 
        ($('dropmarker') || Element.extend(document.createElement('DIV'))).
          hide().addClassName('dropmarker').setStyle({position:'absolute'});
      document.getElementsByTagName("body").item(0).appendChild(Sortable._marker);
    }    
    var offsets = Position.cumulativeOffset(dropon);
    Sortable._marker.setStyle({left: offsets[0]+'px', top: offsets[1] + 'px'});
    
    if(position=='after')
      if(sortable.overlap == 'horizontal') 
        Sortable._marker.setStyle({left: (offsets[0]+dropon.clientWidth) + 'px'});
      else
        Sortable._marker.setStyle({top: (offsets[1]+dropon.clientHeight) + 'px'});
    
    Sortable._marker.show();
  },
  
  _tree: function(element, options, parent) {
    var children = Sortable.findElements(element, options) || [];
  
    for (var i = 0; i < children.length; ++i) {
      var match = children[i].id.match(options.format);

      if (!match) continue;
      
      var child = {
        id: encodeURIComponent(match ? match[1] : null),
        element: element,
        parent: parent,
        children: [],
        position: parent.children.length,
        container: $(children[i]).down(options.treeTag)
      }
      
      /* Get the element containing the children and recurse over it */
      if (child.container)
        this._tree(child.container, options, child)
      
      parent.children.push (child);
    }

    return parent; 
  },

  tree: function(element) {
    element = $(element);
    var sortableOptions = this.options(element);
    var options = Object.extend({
      tag: sortableOptions.tag,
      treeTag: sortableOptions.treeTag,
      only: sortableOptions.only,
      name: element.id,
      format: sortableOptions.format
    }, arguments[1] || { });
    
    var root = {
      id: null,
      parent: null,
      children: [],
      container: element,
      position: 0
    }
    
    return Sortable._tree(element, options, root);
  },

  /* Construct a [i] index for a particular node */
  _constructIndex: function(node) {
    var index = '';
    do {
      if (node.id) index = '[' + node.position + ']' + index;
    } while ((node = node.parent) != null);
    return index;
  },

  sequence: function(element) {
    element = $(element);
    var options = Object.extend(this.options(element), arguments[1] || { });
    
    return $(this.findElements(element, options) || []).map( function(item) {
      return item.id.match(options.format) ? item.id.match(options.format)[1] : '';
    });
  },

  setSequence: function(element, new_sequence) {
    element = $(element);
    var options = Object.extend(this.options(element), arguments[2] || { });
    
    var nodeMap = { };
    this.findElements(element, options).each( function(n) {
        if (n.id.match(options.format))
            nodeMap[n.id.match(options.format)[1]] = [n, n.parentNode];
        n.parentNode.removeChild(n);
    });
   
    new_sequence.each(function(ident) {
      var n = nodeMap[ident];
      if (n) {
        n[1].appendChild(n[0]);
        delete nodeMap[ident];
      }
    });
  },
  
  serialize: function(element) {
    element = $(element);
    var options = Object.extend(Sortable.options(element), arguments[1] || { });
    var name = encodeURIComponent(
      (arguments[1] && arguments[1].name) ? arguments[1].name : element.id);
    
    if (options.tree) {
      return Sortable.tree(element, arguments[1]).children.map( function (item) {
        return [name + Sortable._constructIndex(item) + "[id]=" + 
                encodeURIComponent(item.id)].concat(item.children.map(arguments.callee));
      }).flatten().join('&');
    } else {
      return Sortable.sequence(element, arguments[1]).map( function(item) {
        return name + "[]=" + encodeURIComponent(item);
      }).join('&');
    }
  }
}

// Returns true if child is contained within element
Element.isParent = function(child, element) {
  if (!child.parentNode || child == element) return false;
  if (child.parentNode == element) return true;
  return Element.isParent(child.parentNode, element);
}

Element.findChildren = function(element, only, recursive, tagName) {   
  if(!element.hasChildNodes()) return null;
  tagName = tagName.toUpperCase();
  if(only) only = [only].flatten();
  var elements = [];
  $A(element.childNodes).each( function(e) {
    if(e.tagName && e.tagName.toUpperCase()==tagName &&
      (!only || (Element.classNames(e).detect(function(v) { return only.include(v) }))))
        elements.push(e);
    if(recursive) {
      var grandchildren = Element.findChildren(e, only, recursive, tagName);
      if(grandchildren) elements.push(grandchildren);
    }
  });

  return (elements.length>0 ? elements.flatten() : []);
}

Element.offsetSize = function (element, type) {
  return element['offset' + ((type=='vertical' || type=='height') ? 'Height' : 'Width')];
}


// wick.js
// under WICK-License; Copyright (c) 2004, Christopher T. Holland
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

// Halo defined namespaces constants
var SMW_WEBSERVICE_NS = 200;

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
              this.resetCursorinIE();
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
            this.resetCursorinIE();
            sajax_do_call('smwf_ac_AutoCompletionDispatcher', [
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
                   
					var textarea = advancedEditor ? $('frame_wpTextbox1') : this.siw.inputBox;
                    var posY = this.findElementPosY(textarea);
                    var posX = this.findElementPosX(textarea);
					
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
                    }
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
		var reMeasure = new RegExp("(([+-]?\d*(\.\d+([eE][+-]?\d*)?)?)\s+)?(.*)", "gi");
		
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
                              mEntry.replace(/\>/gi, '}').replace(/\< ?/gi, '{').replace(re, "<b>$1</b>").replace(/_/g, ' '),
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
            } else if (mEntry.match(reMeasure)) {
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
			
            var selection_range = document.selection.createRange();
            selection_range.moveStart("character", -userInput.length);
            selection_range.text = addedValue;
            selection_range.collapse(false);
            this.resetCursorinIE();
            
            if (refreshSTB) refreshSTB.changed();
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
            var theString = pre + addedValue + suf;
            this.siw.inputBox.value = theString;

             // set the cursor behind the inserted text
            this.siw.inputBox.selectionStart = start + addedValue.length - userInput.length;
            this.siw.inputBox.selectionEnd = start + addedValue.length - userInput.length;

             // set old scroll position
            this.siw.inputBox.scrollTop = scrollTop;
            
            if (refreshSTB) refreshSTB.changed();
            // log
            smwhgLogger.log(userInput+addedValue, "AC", "close_with_selection");
        } else {
        	var pasteNS = this.currentInputBox != null ? this.currentInputBox.getAttribute("pasteNS") : null;
            var theString = (baseValue ? baseValue : "") + addedValue;
        	if (pasteNS != null) {
        		switch(type) {
        			
        			case SMW_PROPERTY_NS: theString = gLanguage.getMessage('PROPERTY_NS','cont')+theString; break;
        			case SMW_CATEGORY_NS: theString = gLanguage.getMessage('CATEGORY_NS','cont')+theString; break;
        			case SMW_TEMPLATE_NS: theString = gLanguage.getMessage('TEMPLATE_NS','cont')+theString; break;
        			case SMW_TYPE_NS: theString = gLanguage.getMessage('TYPE_NS','cont')+theString; break;
        			case SMW_WEBSERVICE_NS: theString = gLanguage.getMessage('WEBSERVICE_NS','cont')+theString; break;
        		}
        	}
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
        } else if (_type == SMW_ENUM_POSSIBLE_VALUE_OR_UNIT) {
        	return "<img src=\"" + wgServer + wgScriptPath
                + "/extensions/SMWHalo/skins/enum.gif\">";
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
        	if (generalCache[matchText] && typeof(generalCache[matchText]) == 'object') {
           	 	return generalCache[matchText];
        	}
    	} else {
    		// use typeFiltered cache
    		var cache = typeFilteredCache[parseInt(typeHint)];
			if (!cache) return null;
			return typeof(cache[matchText]) == 'object' ? cache[matchText] : null;
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
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
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
Event.observe(window, 'load', smw_help_callme);

var smw_help_getNamespace = function() {
	var ns = wgNamespaceNumber==0?"Main":wgCanonicalNamespace ;
    if (wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "Search"){
        ns = "Search";
    }
    else if (wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "QueryInterface"){
        ns = "QueryInterface";
    }
    else if (wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "Gardening"){
        ns = "Gardening";
    }
    else if (wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "GardeningLog"){
        ns = "Gardening";
    }
    else if (wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "OntologyBrowser"){
        ns = "OntologyBrowser";
    }
    return ns;
}
var initHelp = function(){
	var ns = smw_help_getNamespace();
	sajax_do_call('smwf_tb_GetHelp', [ns , wgAction], displayHelp.bind(this));
	
}

function smw_help_callme(){
	var ns = smw_help_getNamespace();
	if((wgAction == "edit" || wgAction == "annotate"
	    || wgCanonicalSpecialPageName == "Search")
	   && stb_control.isToolbarAvailable()){
		helpcontainer = stb_control.createDivContainer(HELPCONTAINER, 0);
		helpcontainer.setHeadline('<img src="'+wgScriptPath+'/extensions/SMWHalo/skins/help.gif"/> Help');
		
		// KK: initalize help only when Help container is open.
		var helpLoaded = false;
		helpcontainer.showContainerEvent = function() {
			if (!helpcontainer.isVisible()) return;
			if (helpLoaded) return;
					   
		    sajax_do_call('smwf_tb_GetHelp', [ns , wgAction], displayHelp.bind(this));
		    helpLoaded = true;
		}
		
		displayHelp();	
			
		
	}
	else if (wgCanonicalSpecialPageName == "QueryInterface"){
		
		 sajax_do_call('smwf_tb_GetHelp', [ns , wgAction], displayHelp.bind(this));
	}
}

function displayHelp(request){
	
	if (!request) {
		helpcontainer.setHeadline = ' ';
		helpcontainer.contentChanged();
		return;
	}
	//No SemTB in QI, therefore special treatment
	if(wgCanonicalSpecialPageName == "QueryInterface"){
		if ( request.responseText != '' ){
			$('qi-help-content').innerHTML = request.responseText;
		}
	}
	else { //SemTB available
		if (request.responseText!=''){
			helpcontainer.setContent(request.responseText);
		}
		else {
			helpcontainer.setHeadline = ' ';
		}
		helpcontainer.contentChanged();
	}
}

function askQuestion(){
	$('questionLoaderIcon').show();
	var ns = smw_help_getNamespace();
	sajax_do_call('smwf_tb_AskQuestion', [ns , wgAction, $('question').value], hideQuestionForm.bind(this));
}

function hideQuestionForm(request){
	initHelp();
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
	    smwhgLogger.log(logmsg,"CSH","help_clickedtopic");
	}
	/*ENDLOG*/
	return true;
}

// SMW_Links.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
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
Event.observe(window, 'load', smw_links_callme);


var createLinkList = function() {
	sajax_do_call('smwf_tb_getLinks', [wgArticleId], addLinks);
}


    
    
function smw_links_callme(){
	if(wgAction == "edit"
	   && stb_control.isToolbarAvailable()){
		var _linksHaveBeenAdded = false;
		editcontainer = stb_control.createDivContainer(EDITCONTAINER, 1);
		
		// KK: checks if link tab is open and ask for links if necessary
		var stbpreftab = GeneralBrowserTools.getCookie("stbpreftab")
		if (stbpreftab) {
			if (stbpreftab.split(",")[0] == '0') {
				createLinkList();
                _linksHaveBeenAdded = true;
			}
		}
		
		// KK: called when the user switches the tab.
		editcontainer.showTabEvent = function(tabnum) {
			if (tabnum == 1 && !_linksHaveBeenAdded) {
				createLinkList();
				_linksHaveBeenAdded = true;
			}
		}
		
	}
}

function addLinks(request){
	if (request.responseText!=''){
		editcontainer.setContent(request.responseText);
		editcontainer.contentChanged();
	} else {
		editcontainer.setContent("<p>There are no links on this page.</p>");
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
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
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
		this.splitValues = this.splitValues(this.value);
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
		var newAnnotation = "[[" + this.prefix + newRelationName + "::" + this.value;
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
		var newAnnotation = "[[" + this.prefix + this.name + "::" + newValue;
		if (this.representation && newValue != this.representation) {
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
		var newAnnotation = "[[" + this.prefix + this.name + "::" + this.value;
		if (newRepresentation && newRepresentation != "" 
		    && newRepresentation != this.value) {
			newAnnotation += "|" + newRepresentation;
		}
		newAnnotation += "]]";
		this.representation = newRepresentation;
		this.replaceAnnotation(newAnnotation);
	},
	
	/**
	 * @private
	 * 
	 * Splits the (n-ary) values of a relation at semicolons and takes care of
	 * HTML-entities like &auml;
	 * 
	 * @param string value
	 * 		The value(s) of the relation
	 * 
	 */
	splitValues: function(value) {
		var values = [];
		var start = 0;
		var htmlEntity = '';
		for (var i = 0, n = value.length; i < n; ++i) {
			var ch = value.charAt(i);
			
			if (ch == '&') {
				// maybe a html entity starts
				htmlEntity = '&';
			} else if (ch == ';') {
				var split = false;
				if (htmlEntity != '') {
					// maybe a html entity ends
					htmlEntity += ';';
					var ch = htmlEntity.unescapeHTML();
					if (ch == htmlEntity) {
						// no html entity found
						// => values must be split
						split = true;;
					}
					htmlEntity = '';
				} else {
					// no html entity => values must be split
					split = true;;
				}
				if (split) {				
					values.push(value.substring(start, i));
					start = i + 1;
				}
			} else if (htmlEntity != '') {
				htmlEntity += ch;
			}
		}
		values.push(value.substring(start, i));
		return values;
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
		var newAnnotation = "[[" + this.prefix + gLanguage.getMessage('CATEGORY_NS') + newCategoryName;
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
		var newAnnotation = "[[" + this.prefix + gLanguage.getMessage('CATEGORY_NS') + this.name;
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

/**
 * Class for simples rules - derived from WtpAnnotation
 * 
 * Stores
 * - the name of the rule
 * - the host language
 * - the type of the rule
 * - the text of the rule
 * 
 */
var WtpRule = Class.create();
WtpRule.prototype = Object.extend(new WtpAnnotation(), {
	
	/**
	 * @public
	 * @see constructor of WtpRule
	 */
	initialize: function(annotation, start, end, wtp, 
	                     name, hostlanguage, type, ruleText) {
		this.WtpAnnotation(annotation, start, end, wtp, "");
		this.WtpRule(name, hostlanguage, type, ruleText);
	},

	/**
	 * @private - called by <initialize>
	 * 
	 * Constructor.
	 * 
	 * @param string name
	 * 		Name of the rule
	 * @param string hostlanguage
	 * 		Host language e.g. FLogic
	 * @param string type
	 * 		Type of the rule e.g. Definition, Calculation
	 * @param string ruleText
	 * 		Text of the rule
	 * 
	 */
	WtpRule: function(name, hostlanguage, type, ruleText) {
		this.name = name;
		this.hostlanguage = hostlanguage;
		this.type = type;
		this.ruleText = ruleText;
	},

	/**
	 * @public
	 * 
	 * Replaces a rule in the wiki text.
	 * 
	 * @param string newRule The complete definition of the new rule
	 */
	changeRule: function(newRule) {
		this.replaceAnnotation(newRule);
	},
	
	/**
	 * @public
	 * 
	 * @return string
	 * 		Returns the text of the rule e.g. the FLogic.
	 */
	getRuleText: function() {
		return this.ruleText;
	}
	
	
});



// WikiTextParser.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
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

/*
	Cross-Browser Split 0.2.1
	By Steven Levithan <http://stevenlevithan.com>
	MIT license
*/

var nativeSplit = nativeSplit || String.prototype.split;

String.prototype.split = function (s /* separator */, limit) {
	// If separator is not a regex, use the native split method
	if (!(s instanceof RegExp))
		return nativeSplit.apply(this, arguments);

	/* Behavior for limit: If it's...
	 - Undefined: No limit
	 - NaN or zero: Return an empty array
	 - A positive number: Use limit after dropping any decimal
	 - A negative number: No limit
	 - Other: Type-convert, then use the above rules */
	if (limit === undefined || +limit < 0) {
		limit = false;
	} else {
		limit = Math.floor(+limit);
		if (!limit)
			return [];
	}

	var	flags = (s.global ? "g" : "") + (s.ignoreCase ? "i" : "") + (s.multiline ? "m" : ""),
		s2 = new RegExp("^" + s.source + "$", flags),
		output = [],
		lastLastIndex = 0,
		i = 0,
		match;

	if (!s.global)
		s = new RegExp(s.source, "g" + flags);

	while ((!limit || i++ <= limit) && (match = s.exec(this))) {
		var zeroLengthMatch = !match[0].length;

		// Fix IE's infinite-loop-resistant but incorrect lastIndex
		if (zeroLengthMatch && s.lastIndex > match.index)
			s.lastIndex = match.index; // The same as s.lastIndex--

		if (s.lastIndex > lastLastIndex) {
			// Fix browsers whose exec methods don't consistently return undefined for non-participating capturing groups
			if (match.length > 1) {
				match[0].replace(s2, function () {
					for (var j = 1; j < arguments.length - 2; j++) {
						if (arguments[j] === undefined)
							match[j] = undefined;
					}
				});
			}

			output = output.concat(this.slice(lastLastIndex, match.index), (match.index === this.length ? [] : match.slice(1)));
			lastLastIndex = s.lastIndex;
		}

		if (zeroLengthMatch)
			s.lastIndex++;
	}

	return (lastLastIndex === this.length) ?
		(s.test("") ? output : output.concat("")) :
		(limit      ? output : output.concat(this.slice(lastLastIndex)));
};

/**
 * WikiTextParser.js
 *
 * Class for parsing annotations in wiki text.
 *
 * @author Thomas Schweitzer
 */

var WTP_NO_ERROR = 0;
var WTP_UNMATCHED_BRACKETS = 1;

var WTP_WIKITEXT_MODE = 1;
var WTP_EDITAREA_MODE = 2;

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
	 * Constructor. If no wiki text is given, the text from the textarea of the 
	 * edit page is stored. The text is parsed and the list of of annotations is 
	 * initialized.
	 * This function may be called several times. The first invocation defines
	 * the source on which the parser operates (the parser's mode): given wiki #
	 * text or content of the edit area. The mode does not change, even if this
	 * function is called without wikiText parameter. 
	 * 
	 * @param string wikiText 
	 *		If not <null>, this wiki text is parsed and used for further 
	 * 		operations. Otherwise the text from the edit area is retrieved.
	 *               
	 */
	initialize: function(wikiText) {
		if (wikiText == "") {
			// Empty strings are treated as false => make a non-emty string
			wikiText = " ";
		}
		if (this.parserMode == WTP_WIKITEXT_MODE) {
			// Parser mode is 'wiki text' => do not release the current text
			if (!wikiText) {
				wikiText = this.text;
			}
		}
		if (!wikiText || this.parserMode == WTP_EDITAREA_MODE) {
			// no wiki text => retrieve from text area.
			var txtarea;
			if (document.editform) {
				txtarea = document.editform.wpTextbox1;
			} else {
				// some alternate form? take the first one we can find
				var areas = document.getElementsByTagName('textarea');
				txtarea = areas[0];
			}
	
			if (gEditInterface == null) {
				gEditInterface = new SMWEditInterface();
			}
			this.editInterface = gEditInterface;
			this.text = this.editInterface.getValue();
			this.parserMode = WTP_EDITAREA_MODE;
		} else if (!this.parserMode) {
			this.editInterface = null;
			this.text = wikiText;
			this.parserMode = WTP_WIKITEXT_MODE;
			this.wtsStart = -1; // start of internal wiki text selection
			this.wtsEnd   = -1  // end of internal wiki text selection
			
		}
		if (!this.textChangedHooks) {
			// Array of hooks that are called when the wiki text has been changed
			this.textChangedHooks = new Array(); 
			// Array of hooks that are called when a category has been added
			this.categoryAddedHooks = new Array();
			// Array of hooks that are called when a relation has been added
			this.relationAddedHooks = new Array();
			// Array of hooks that are called when an annotation has been removed
			this.annotationRemovedHooks = new Array();
		}
		
		this.relations  = null;
		this.categories  = null;
		this.links  = null;
		this.rules  = null;
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
	 * @public
	 *
	 * Returns the wiki text from the edit box of the edit page.
	 *
	 * @return string Text from the edit box.
	 */
	getWikiText: function() {
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
	 * Returns the rule with the given name or null if it is not present.
	 *
	 * @return WtpRule The rule's definitions.
	 */
	getRule: function(name) {
		if (this.rules == null) {
			this.parseAnnotations();
		}
		var matching = new Array();

		for (var i = 0, num = this.rules.length; i < num; ++i) {
			var rule = this.rules[i];
			if (this.equalWikiName(rule.getName(), name)) {
				return rule;
			}
		}
		return null;
	},


	/**
	 * @public
	 *
	 * Returns an array that contains the rules, that are annotated in
	 * the current wiki text. Rules within templates are not considered.
	 *
	 * @return array(WtpRule) An array of rule definitions.
	 */
	getRules: function() {
		if (this.rules == null) {
			this.parseAnnotations();
		}

		return this.rules;
	},


	addTextChangedHook: function(hookFnc) {
		this.textChangedHooks.push(hookFnc);
	},
	
	addCategoryAddedHook: function(hookFnc) {
		this.categoryAddedHooks.push(hookFnc);
	},
	
	addRelationAddedHook: function(hookFnc) {
		this.relationAddedHooks.push(hookFnc);
	},
	
	addAnnotationRemovedHook: function(hookFnc) {
		this.annotationRemovedHooks.push(hookFnc);
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
	 	var anno = "[[" + name + "::" + value;
	 	if (representation && value != representation) {
	 		anno += "|" + representation;
	 	}
	 	anno += "]]";
	 	var posInfo = this.addAnnotation(anno, append);
	 	for (var i = 0; i < this.relationAddedHooks.size(); ++i) {
	 		this.relationAddedHooks[i](posInfo[0], posInfo[0] + posInfo[2], name);
	 	}
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
	 	var anno = "[["+gLanguage.getMessage('CATEGORY_NS') + name;
	 	anno += "]]";
	 	var posInfo = this.addAnnotation(anno, append);
	 	for (var i = 0; i < this.categoryAddedHooks.size(); ++i) {
	 		this.categoryAddedHooks[i](posInfo[0], posInfo[0] + posInfo[2], name);
	 	}
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
		var startText = this.text.substring(0,annoObj.getStart());
		var endText = this.text.substr(annoObj.getEnd());
		var diffLen = newAnnotation.length - annoObj.getAnnotation().length;

		// construct the new wiki text
		this.text = startText + newAnnotation + endText;
		if (this.editInterface) {
			this.editInterface.setValue(this.text);
		}

		var result = [annoObj.getStart(), annoObj.getEnd(), newAnnotation.length];
		for (var i = 0; i < this.textChangedHooks.size(); ++i) {
			this.textChangedHooks[i](result);
		}
		
		// all following annotations have moved => update their location
		this.updateAnnotationPositions(annoObj.getStart(), diffLen);
		
		
	},

	/**
	 * @public
	 * 
	 * Returns the text that is currently selected in the wiki text editor.
	 *
	 * @param boolean trim
	 * 			If <true>, spaces that surround the selection are skipped and
	 * 			the complete annotation including brackets is selected.
	 * @return string Currently selected text.
	 */
	getSelection: function(trim) {
		var text = "";
		if (this.editInterface) {
			trim = true;
			var text = this.editInterface.getSelectedText();
			if (trim == true && text && text.length > 0) {
				var regex = /^(\s*(\[\[)?)\s*(.*?)\s*((\]\])?\s*)$/;
				var parts = text.match(regex);
				if (parts) {
					var rng = this.editInterface.selectCompleteAnnotation();
					return parts[3];
				}
			}
		} else {
			// wiki text mode
			if (this.wtsStart >= 0 && this.wtsEnd >= 0) {
				text = this.text.substring(this.wtsStart, this.wtsEnd);
			}
		}
		return text;
	},

	/**
	 * @public
	 * 
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
		if (this.editInterface) {
			this.editInterface.setSelectionRange(start, end);
		} else {
			this.wtsStart = start;
			this.wtsEnd = end;
		}
	},

	/**
	 * @public
	 * 
	 * Searches in the given range of the wiki text for the given text. If it is
	 * found, the corresponding section is internally marked as selected and <true>
	 * is returned. Otherwise a reason for the failed search is returned.
	 * 
	 * This function is applied to the wiki text only i.e. it does not use the 
	 * edit area and its content in the edit mode.
	 * 
	 * @param string text
	 * 		This text will be searched in the wiki text
	 * @param int start
	 * 		0-based start index of the range where the search happens
	 * @param int end
	 * 		0-based end index of the range where the search happens. If end==-1,
	 * 		the search runs till the end of the text
	 * @param array<string> context
	 * 		The context of the text i.e. some words before and after
	 * @return
	 * 		boolean <true>, if the text was found
	 * 		string reason why the search failed otherwise
	 */
	findText: function(text, start, end, context) {

		this.wtsStart = -1;
		this.wtsEnd   = -1;
		
		if (end == -1) {
			end = this.text.length;
		}
		
		var withContext = text;
		var preContext = "";
		if (typeof(context) == "object") {
			preContext = context[0] + context[1];
			withContext = preContext + text + context[2] + context[3]; 
		}
		// try a simple search
//		var pos = this.text.indexOf(text, start);
		var pos = this.text.indexOf(withContext, start);
		
		if (pos >= 0 && pos < end) {
			this.wtsStart = pos + preContext.length;
			this.wtsEnd = this.wtsStart + text.length;
			return true;
		}
		
		// consider bold ''' and italic '' formatting instructions
		// Mapping from pure text to wiki text - Example:
		// this is '''bold''' text:&nbsp;space
		// this is bold text: space
		// 012345678911111111112222
		//           01234567890123
		// 012345671111112222233333
		//         1234890123401234
		// 0=>0, 8=>11, 12=>18, 19=>30
		
		var wikitext = this.text.substring(start,end);
		var pureText = '';
		var pti = 0; // Index in pure text
		var wti = 0; // Index in wiki text
		var map = new Array(); // Map from pure text indices to wiki text indices
		var parts = wikitext.split(/('{2,})|(&nbsp;)|(\[\[.*?\]\])|(\[http.*?\])|(\s+)/);
		parts = parts.compact();
		var openApos = 0; // number of opening apostrophes (max 5)
		
		// Rules for finding bold and italic formatting instructions
		var rules = [
			[0,'a',5,3,2],
			[2,'a',3],
			[3,'c',3],
			[3,'a',2],
			[5,'c',5,3,2],
			[3,'c',3,2],
			[2,'c',2]
		];
		var closingRulesStart = 4;
		
		// Count all available apostrophes
		var numApos = 0;
		for (var i = 0; i < parts.length; ++i) {
			if (parts[i].charAt(0) == "'") {
				numApos += parts[i].length;
			}
		}
		
		var lastWasSpace = false;
		for (var i = 0; i < parts.length; ++i) {
			var part = parts[i];
			if (part.length == 0) {
				continue;
			}
			
			if (part.charAt(0) == "'") {
				// a sequence of at least 2 apostrophes
				var num = part.length;
				var rulesStart = 0;
				if (openApos+num > numApos) {
					rulesStart = closingRulesStart;
				}
				numApos -= num;
				var ruleApplied = false;
				for (var r = rulesStart; r < rules.length && !ruleApplied; ++r) {
					var rule = rules[r];
					var writeApos = 0;
					if (openApos == rule[0]) {
						// number of open apostrophes matches the rule
						for (var j = 2; j < rule.length; ++j) {
							if (num >= rule[j]) {
								ruleApplied = true;
								if (rule[1] == 'a') {
									//add opening apostrophes
									openApos += rule[j];
								} else if (rule[1] == 'c') {
									//closing apostrophes
									openApos -= rule[j];
								}
								writeApos = num-rule[j];
								if (writeApos != 0) {
									// write remaining apostrophes to pure text
									map.push([pti,wti+writeApos,openApos]);
									pti += writeApos;
									while (writeApos-- > 0) {
										pureText += "'";
									}
									lastWasSpace = false;
								}
								break;
							}
						}
					} 
				}
			} else if (link = part.match(/\[\[(.*?)(\|.*?)?\]\]/)) {
				var pt = link[2]; // Representation
				if (!pt) {
					pt = link[1]; // link
				}
				pureText += pt;
				map.push([pti,wti,openApos]);
				pti += pt.length;
				lastWasSpace = false;
			} else if (part.match(/\s+/) || part == '&nbsp;') {
				if (!lastWasSpace) {
					pureText += ' ';
					map.push([pti,wti+part.length-1,openApos]);
					pti++;
				}
				lastWasSpace = true;
			} else if (part.charAt(0) == '[') {
				
			} else {
				// normal text
				pureText += part;
				map.push([pti,wti,openApos]);
				pti += part.length;
				lastWasSpace = false;
			}
			wti += part.length;
			
		}
		
		// find the selection in the pure text
		pos = pureText.indexOf(withContext);
		if (pos == -1) {
			pos = pureText.indexOf(text);
		} else {
			pos += preContext.length;
		}
		if (pos == -1) {
			// text not found
			var msg = gLanguage.getMessage('WTP_TEXT_NOT_FOUND');
			msg = msg.replace(/\$1/g, '<b>'+text+'</b>');
			return msg;
		}
		
		// find the start and end indices in the wiki text with the map from
		// pure text indices to wiki text indices.
		var wtStart = -1;
		var wtEnd = -1;
		var startLevel = 0;
		var endLevel = 0;
		var endMapIdx = -1;
		pos += text.length;
		for (var i = map.length-1; i >= 0; --i) {
			if (pos >= map[i][0]) {
				if (wtEnd == -1) {
					wtEnd = map[i][1] + (pos - map[i][0]);
					endLevel = map[i][2];
					endMapIdx = i;
					pos -= text.length;
					++i;
				} else {
					wtStart = map[i][1] + (pos - map[i][0]);
					startLevel = map[i][2];
					if (startLevel != endLevel) {
						// text across different formats
						if (pos == map[i][0]) {
							// maybe we are at the first character of a 
							// bold/italic section
							if (i-1 >= 0 
							    && map[i-1][2] == endLevel 
							    && wikitext.charAt(map[i][1]-1) == "'") {
								wtStart = map[i-1][1] + (pos - map[i-1][0]);
								startLevel = map[i-1][2];
							} else if (i == 0 && endLevel == 0) {
								// selection starts at the very beginning which
								// is formated bold/italic and ends in normal text
								wtStart = 0;
								startLevel = endLevel;
							}
						}
						if (startLevel != endLevel) {
							if (pos+text.length == map[endMapIdx][0]) {
								// maybe we are at the last character of a 
								// bold/italic section
								if (endMapIdx > 0 && map[endMapIdx-1][2] == startLevel) {
									wtEnd -= startLevel;
									endLevel = startLevel;
								}
							} else if (pos+text.length == pureText.length 
									   && startLevel == 0) {
								// Selection ends at the very end
								wtEnd = wikitext.length;
								endLevel = startLevel;
							}
						}
						
					}
					break;
				}
			}
		}
		if (startLevel != endLevel) {
			var msg = gLanguage.getMessage('WTP_SELECTION_OVER_FORMATS');
			msg = msg.replace(/\$1/g, '<b>'+text+'</b>');
			return msg;
		}
		this.wtsStart = wtStart + start;
		this.wtsEnd = wtEnd + start;
//		var wikiText = this.text.substring(this.wtsStart, this.wtsEnd);
//		return "Matching text:<br><b>"+wikitext+"</b><br><b>"+pureText+"</b><br><b>"+wikiText+"</b>";
		return true;
		
	},	 

	/**
	 * @private
	 *
	 * Parses the content of the edit box and retrieves relations, 
	 * categories and links. These are stored in internal arrays.
	 *
	 * <nowiki>, <pre> and <ask>-sections are ignored.
	 */
	parseAnnotations: function() {

		this.relations  = new Array();
		this.categories = new Array();
		this.links      = new Array();
		this.rules      = new Array();
		this.error = WTP_NO_ERROR;

		// Parsing-States
		// 0 - find [[, <nowiki>, <pre> or <ask>
		// 1 - find [[ or ]]
		// 2 - find <nowiki> or </nowiki>
		// 3 - find <ask> or </ask>
		// 4 - find {{#ask:
		// 5 - find <pre> or </pre>
		// 6 - find <rule or </rule>
		var state = 0;
		var bracketCount = 0; // Number of open brackets "[["
		var askCount = 0;  	  // Number of open <ask>-statements
		var currentPos = 0;   // Starting index for next search
		var bracketStart = -1;
		var parsing = true;
		while (parsing) {
			switch (state) {
				case 0:
					// Search for "[[", "<nowiki>", <pre>, <rule or <ask
					var findings = this.findFirstOf(currentPos, ["[[", "<nowiki>", "<pre>", "<ask", "<rule", "{{#ask:"]);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+1;
					bracketStart = -1;
					if (findings[1] == "[[") {
						// opening bracket found
						bracketStart = findings[0];
						bracketCount++;
						state = 1;
					} else if (findings[1] == "<nowiki>") {
						state = 2;
					} else if (findings[1] == "<pre>") {
						state = 5;
					} else if (findings[1] == "<rule") {
						state = 6;
					} else if (findings[1] == "<ask") {
						askCount++;
						state = 3;
					} else if (findings[1] == "{{#ask:") {
						state = 4;
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
					// => search for </nowiki>
					var findings = this.findFirstOf(currentPos, ["</nowiki>"]);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+7;
					// opening <nowiki> is closed
					state = 0;
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
				case 4:
					// we are within an {{#ask:-template
					var pos = this.parseAskTemplate(currentPos);
					currentPos = (pos == -1) ? currentPos+7 : pos;
					state = 0;
					break;
				case 5:
					// we are within a <pre>-block
					// => search for </pre>
					var findings = this.findFirstOf(currentPos, ["</pre>"]);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					currentPos = findings[0]+4;
					// opening <pre> is closed
					state = 0;
					break;
				case 6:
					// we are within a <rule>-block
					// => search for </rule>
					var findings = this.findFirstOf(currentPos, ["</rule>"]);
					if (findings[1] == null) {
						// nothing found
						parsing = false;
						break;
					}
					var start = currentPos-1;
					var end = findings[0]+7;
					var rule = this.parseRule(this.text.substring(start, end), start, end);
					if (rule != null) {
						this.rules.push(rule);
					}
					currentPos = end;
					// opening <rule> is closed
					state = 0;
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
	 * Parses an ask-template until its end starting at position <currentPos>
	 * in the wikitext. The position after the template is returned.
	 * 
	 * @param int currentPos
	 * 		Start position in the wikitext (right after the opening '{{#ask:'
	 * 
	 * @return int 
	 * 		The position after the closing '}}' or -1, if parsing fails due to
	 * 		syntax error.
	 */
	parseAskTemplate : function(currentPos) {
		var parserTable = new Object();
		parserTable['ask'] = ["{{#ask:", "{{{", "{{", "}}"];
		parserTable['tparam'] = ["}}}"];
		parserTable['tmplt'] = ["{{#ask:", "{{{", "}}"];
		
		var actionTable = new Object();
		actionTable['ask'] = new Object();
		actionTable['ask']["{{#ask:"] = ["push", "ask"];
		actionTable['ask']["{{"]      = ["push", "tmplt"];
		actionTable['ask']["{{{"]     = ["push", "tparam"];
		actionTable['ask']["}}"]      = ["pop"];
		
		actionTable['tparam'] = new Object();
		actionTable['tparam']["}}}"] = ["pop"];
		
		actionTable['tmplt'] = new Object();
		actionTable['tmplt']["{{#ask:"] = ["push", "ask"];
		actionTable['tmplt']["{{{"]     = ["push", "tparam"];
		actionTable['tmplt']["}}"]      = ["pop"];
		
		var stack = new Array();
		stack.push('ask'); // the first opening ask is already parsed
		while (stack.size() > 0) {
			var ct = stack[stack.size()-1];
			var findings = this.findFirstOf(currentPos, parserTable[ct]);
			if (findings[1] == null) {
				// nothing found
				return -1;
			}
			
			var action = actionTable[ct];
			if (!action) {
				return -1;
			}
			action = action[findings[1]];
			if (!action) {
				return -1;
			}
			if (action[0] === 'push') {
				stack.push(action[1]);
			} else if (action[0] === 'pop') {
				stack.pop();
			}
			currentPos = findings[0]+ findings[1].length;
		}
		return currentPos;
	},
	
	/**
	 * @private
	 * 
	 * Parses the rule that is given in <ruleTxt>.
	 * 
	 * @param string ruleTxt
	 * 		Definition of the rule
	 * @param int start
	 * 		Start index of the rule in the wiki text
	 * @param int ent
	 * 		End index of the rule in the wiki text
	 * 
	 * @return WtpRule
	 * 		A rule object or <null> if parsing failed.
	 * 
	 */
	 parseRule: function(ruleTxt, start, end) {
		var hl = ruleTxt.match(/.*hostlanguage\s*=\s*"(.*?)"/);
		var rulename = ruleTxt.match(/.*name\s*=\s*"(.*?)"/);
		var type = ruleTxt.match(/.*type\s*=\s*"(.*?)"/);
		var rule = ruleTxt.match(/<rule(?:.|\s)*?>((.|\s)*?)<\/rule>/m);
		
		if (hl && rulename && type && rule) {
			hl = hl[1];
			rulename = rulename[1];
			type = type[1];
			rule = rule[1];
			return new WtpRule(ruleTxt, start, end, this, rulename, hl, type, rule);
		} else {
			return null;
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
	 * 
	 * @return 
	 * 		boolean false, if the text has been replaced in the edit area
	 * 		array<int>[3], if text was replaced in the wiki text
	 * 			[0]: start index of replacement in original text
	 * 			[1]: end index of replacement in original text
	 * 			[2]: length of inserted text 
	 * 
	 */
	addAnnotation : function(annotation, append) {
		var result = false;
		if (append) {
			if (this.editInterface) {
				this.editInterface.setValue(this.editInterface.getValue() + annotation);
			} else {
				result = [this.text.length, this.text.length, annotation.length];
				this.text += annotation
			}
		} else {
			result = this.replaceText(annotation);
		}
		// invalidate all parsed data
		this.initialize(this.text);
		return result;
	},

	/**
	 *
	 * @private
	 *
	 * Removes the annotation from the internal arrays. The hooks for removed
	 * annotations are called.
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
		for (var i = 0; i < this.annotationRemovedHooks.size(); ++i) {
	 		this.annotationRemovedHooks[i](annotation);
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
	 * Inserts a text at the cursor or replaces the current selection. This applies
	 * also, if only the wiki text if given without an edit area.
	 *
	 * @param string text The text that is inserted.
	 * @return 
	 * 		boolean false, if the text has been replaced in the edit area
	 * 		array<int>[3], if text was replaced in the wiki text
	 * 			[0]: start index of replacement in original text
	 * 			[1]: end index of replacement in original text
	 * 			[2]: length of inserted text 
	 *
	 */
	replaceText : function(text)  {
		if (this.editInterface) {
			this.editInterface.setSelectedText(text);
		} else if (this.wtsStart >= 0) {
			this.text = this.text.substring(0, this.wtsStart)
			            + text
			            + this.text.substring(this.wtsEnd);
			var result = [this.wtsStart, this.wtsEnd, text.length];
			for (var i = 0; i < this.textChangedHooks.size(); ++i) {
				this.textChangedHooks[i](result);
			}
			this.wtsStart = -1;			 
			this.wtsEnd   = -1;
			return result;
		}
		return false;
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
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
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
		
		// Array of hooks that are called, when the ajax call in <editArticle>
		// returns
		this.editArticleHooks = new Array();
	},

	/**
	 * @public
	 * 
	 * Adds a hook function that is called, when the ajax call in <editArticle>
	 * returns.
	 * 
	 * @param function hook
	 * 		The hook function. It must have this signature:
	 * 		hook(boolean success, boolean created, string title)
	 * 			success: <true> if the article was successfully edited
	 * 			created: <true> if the article has been created
	 * 			title: Title of the article		
	 */
	addEditArticleHook: function(hook) {
		this.editArticleHooks.push(hook);
	},

	/**
	 * @public
	 * 
	 * Checks if an article exists in the wiki. This is an asynchronous ajax call.
	 * When the result is returned, the function <callback> will be called.
	 * The existence will not be checked, if the page name is too long.
	 * 
	 * @param string pageName 
	 * 			Full page name of the article.
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
	 * @return boolean
	 * 		true, if the existence of the article will be checked
	 * 		false, if the <pageName> is longer than 254 characters.
	 */
	existsArticle : function(pageName, callback, title, optparam, domElementID) {
		function ajaxResponseExistsArticle(request) {
			var answer = request.responseText;
			var regex = /(true|false)/;
			var parts = answer.match(regex);
			
			if (parts == null) {
				// Error while querying existence of article, probably due to 
				// invalid article name => article does not exist
				callback(pageName, false, title, optparam, domElementID);
/*				var errMsg = gLanguage.getMessage('ERR_QUERY_EXISTS_ARTICLE');
				errMsg = errMsg.replace(/\$-page/g, pageName);
				alert(errMsg);
*/ 
				return;
			}
			callback(pageName, parts[1] == 'true' ? true : false, title, optparam, domElementID);
			
		};
		
		if (pageName.length < 255) {
			sajax_do_call('smwf_om_ExistsArticle', 
			              [pageName], 
			              ajaxResponseExistsArticle.bind(this));
			return true;
		} else {
			return false;
		}
		              
		              
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
		sajax_do_call('smwf_om_CreateArticle', 
		              [title, wgUserName , content, optionalText, creationComment], 
		              this.ajaxResponseCreateArticle.bind(this));
		              
	},
	
	/**
	 * @public
	 * 
	 * Replaces the complete content of an article in the wiki. If the article
	 * does not exist, it will be created.
	 * 
	 * @param string title 
	 * 			Title of the article.
	 * @param string content 
	 * 			New content of the article.
	 * @param string editComment
	 * 			This text describes why the article has been edited. 
	 * @param bool redirect If <true>, the system asks the user, if he he wants 
	 * 			to be redirected to the new article after its creation.
	 */
	editArticle : function(title, content, editComment, redirect) {
		this.redirect = redirect;
		sajax_do_call('smwf_om_EditArticle', 
		              [title, wgUserName, content, editComment], 
		              this.ajaxResponseEditArticle.bind(this));
	},

	/**
	 * @public
	 * 
	 * Touches the article with the given title, i.e. the article's HTML-cache is
 	 * invalidated.
	 * 
	 * @param string title 
	 * 			Title of the article.
	 */
	touchArticle : function(title) {
		function touchArticleCallback(request) {
			
		};
		
		sajax_do_call('smwf_om_TouchArticle', [title], touchArticleCallback.bind(this));
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
			schema += "\n[[SMW_SSP_HAS_DOMAIN_HINT::"+gLanguage.getMessage('CATEGORY_NS')+domain+"]]";
		}
		if (type != null && type != "") {
			schema += "\n[[SMW_SP_HAS_TYPE::"+gLanguage.getMessage('TYPE_NS')+type+"]]";
		}
		this.createArticle(gLanguage.getMessage('PROPERTY_NS')+title, 
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
			domain = gLanguage.getMessage('CATEGORY_NS')+domain;
		} else {
			domain = '';
		}
		var domainHintWritten = false;
		if (ranges != null) {
			if (ranges.length >= 1) {
				var rangeStr = "\n[[SMW_SP_HAS_TYPE:="
				for(var i = 0, n = ranges.length; i < n; i++) {
					if (ranges[i].indexOf(gLanguage.getMessage('TYPE_NS')) == 0) {
						rangeStr += ranges[i];
					} else {
						rangeStr += gLanguage.getMessage('TYPE_PAGE');
						domainHintWritten = true;
						if (ranges[i]) {
							// Range hint is not empty
							schema += "\n[[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT::"
							          + domain + ";" + ranges[i]+"]]";
						} else {
							// no range hint. Anyway a hint must be given.
							schema += "\n[[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT::"
							          + domain + ";]]";
						}

					}
					if (i < n-1) {
						rangeStr += ';';
					}
			 	}
			 	schema += rangeStr+"]]";
			} 
		}
		
		if (!domainHintWritten && domain != '') {
			schema += "\n[[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT::"
			          + domain + ";]]";
		}
		this.createArticle(gLanguage.getMessage('PROPERTY_NS')+title, 
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
		this.createArticle(gLanguage.getMessage('CATEGORY_NS')+title, 
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
			this.createArticle(gLanguage.getMessage('PROPERTY_NS')+title, 
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
	 * @param WikiTextParser wtp
	 * 			If given, this parser is used to annotate the current article.
	 * 			Otherwise a new one is created.
	 */
	createSuperProperty : function(title, initialContent, openNewArticle, wtp) {
		if (openNewArticle == undefined) {
			openNewArticle = true;
		}
		var schemaProp = this.getSchemaProperties();
		if (!wtp) {
			wtp = new WikiTextParser();
		}
		if (   wgNamespaceNumber == 102 // SMW_NS_PROPERTY
		    || wgNamespaceNumber == 100) {  // SMW_NS_RELATION
			this.createArticle(gLanguage.getMessage('PROPERTY_NS')+title, 
							 initialContent, 
							 schemaProp,
							 gLanguage.getMessage('CREATE_SUPER_PROPERTY'), 
							 openNewArticle);
							 
			// append the sub-property annotation to the current article
			wtp.addRelation("subproperty of", gLanguage.getMessage('PROPERTY_NS')+title, "", true);
			
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
	 * @param WikiTextParser wtp
	 * 			If given, this parser is used to annotate the current article.
	 * 			Otherwise a new one is created.
	 */
	createSuperCategory : function(title, initialContent, openNewArticle, wtp) {
		if (openNewArticle == undefined) {
			openNewArticle = true;
		}
		if (!wtp) {
			wtp = new WikiTextParser();
		}
		if (wgNamespaceNumber == 14) {
			this.createArticle(gLanguage.getMessage('CATEGORY_NS')+title, initialContent, "",
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
			this.createArticle(gLanguage.getMessage('CATEGORY_NS')+title, initialContent, 
			                   "[["+gLanguage.getMessage('CATEGORY_NS')+wgTitle+"]]",
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
		props.push(wtp.getRelation(HAS_TYPE));
		props.push(wtp.getRelation(DOMAIN_HINT));
		props.push(wtp.getRelation(MAX_CARDINALITY));
		props.push(wtp.getRelation(MIN_CARDINALITY));
		
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
		var transitive = wtp.getCategory(TRANSITIVE_RELATION);
		var symmetric = wtp.getCategory(SYMMETRICAL_RELATION);
		
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
	 * @param request Created by the framework. Contains the ajax request and
	 *                its result.
	 * 
	 */
	ajaxResponseCreateArticle: function(request) {
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
			if (this.redirect) {
				// open the new article in another tab.
				var indexStr = wgScript.substring(wgScript.lastIndexOf("/")+1);
				window.open(indexStr+"?title="+title,"_blank");
			}
		} else if (created == 'denied') {
			var msg = gLanguage.getMessage('smw_acl_create_denied').replace(/\$1/g, title);
			alert(msg);
		}
	},
	
	/**
	 * This function is called when the ajax request for changing an
	 * article returns. The answer has the following format:
	 * bool, bool, string
	 * - The first boolean signals success (true) of the operation.
	 * - The second boolean signals that a new article has been created (true), or
	 *   that it already existed(false).
	 * - The string contains the name of the (new) article.
	 * 
	 * @param request Created by the framework. Contains the ajax request and
	 *                its result.
	 * 
	 */
	ajaxResponseEditArticle: function(request) {
		if (request.status != 200) {
			alert(gLanguage.getMessage('ERROR_EDITING_ARTICLE'));
			return;
		}
		
		var answer = request.responseText;
		var regex = /(true|false),(true|denied|false),(.*)/;
		var parts = answer.match(regex);
		
		if (parts == null) {
			alert(gLanguage.getMessage('ERROR_EDITING_ARTICLE'));
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
		} else if (created == 'denied') {
			var msg = gLanguage.getMessage('smw_acl_edit_denied').replace(/\$1/g, title);
			alert(msg);
		}
		
		success = (success == 'true');
		created = (created == 'true');
		for (var i = 0; i < this.editArticleHooks.length; ++i) {
			this.editArticleHooks[i](success, created, title);
		}
	}
	

}


// SMW_DataTypes.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
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
		if (this.builtinTypes && this.userTypes) {
// Change request (Bug 7077): Do not update the user types every time they are
// needed.
			for (var i = 0; i < this.callback.length; ++i) {
				this.callback[i]();
			}
			this.callback.clear();
			
			return;
		}
		if (!this.refreshPending) {
			this.refreshPending = true;
			sajax_do_call('smwf_tb_GetUserDatatypes', 
			              [], 
			              this.ajaxResponseGetDatatypes.bind(this));
			if (!this.builtinTypes) {
				this.builtinTypes = GeneralBrowserTools.getCookieObject("smwh_builtinTypes");
				if (this.builtinTypes == null) {
					sajax_do_call('smwf_tb_GetBuiltinDatatypes', 
					              [], 
					              this.ajaxResponseGetDatatypes.bind(this));
				}
			}
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
			// received user defined types
			this.userTypes = new Array(types.length-1);
			for (var i = 1, len = types.length; i < len; ++i) {
				this.userTypes[i-1] = types[i];
			}
		} else {
			// received builtin types
			this.builtinTypes = new Array(types.length-1);
			for (var i = 1, len = types.length; i < len; ++i) {
				this.builtinTypes[i-1] = types[i];
			}
			GeneralBrowserTools.setCookieObject("smwh_builtinTypes", this.builtinTypes);
		}
		if (this.userTypes && this.builtinTypes) {
			
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
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
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
var GenericToolBar = Class.create();

GenericToolBar.prototype = {

initialize: function() {

},

createList: function(list,id) {
	var len = list == null ? 0 : list.length;
	var divlist = "";
	switch (id) {
		case "category":
			divlist ='<div id="' + id +'-tools">';
			divlist += '<a id="cat-menu-annotate" enabled="true" href="javascript:catToolBar.newItem()" class="menulink">'+gLanguage.getMessage('ANNOTATE')+'</a>';
			if (wgAction != 'annotate') {
				divlist += '<a href="javascript:catToolBar.newCategory()" class="menulink">'+gLanguage.getMessage('CREATE')+'</a>';
			}
			if (wgNamespaceNumber == 14) {
				divlist += '<a href="javascript:catToolBar.CreateSubSup()" class="menulink">'+gLanguage.getMessage('SUB_SUPER')+'</a>';
			}
			divlist += '</div>';
	 		break;
		case "relation":
	  		divlist ='<div id="' + id +'-tools">';
			if (wgAction != 'annotate') {
				divlist += '<a id="rel-menu-annotate" href="javascript:relToolBar.newItem()" class="menulink">'+gLanguage.getMessage('ANNOTATE')+'</a>';
			}
			divlist += '<a href="javascript:relToolBar.newRelation()" class="menulink">'+gLanguage.getMessage('CREATE')+'</a>';
			//regex for checking attribute namespace. 
			//since there's no special namespace number anymore since atr and rel are united 
			var attrregex =	new RegExp("Attribute:.*");
			if (wgNamespaceNumber == 100 || wgNamespaceNumber == 102  || attrregex.exec(wgPageName) != null) {
				divlist += "<a href=\"javascript:relToolBar.CreateSubSup()\" class=\"menulink\">"+gLanguage.getMessage('SUB_SUPER')+"</a>";
			}
  			divlist += '<a id="rel-menu-has-part" href="javascript:relToolBar.newPart()" class="menulink">'+gLanguage.getMessage('MHAS_PART')+'</a>';
  			divlist += '</div>';
	  		break;
		case "rules":
			divlist ='<div id="' + id +'-tools">';
			divlist += '<a id="rules-menu-annotate" href="javascript:ruleToolBar.createRule()" class="menulink">'+gLanguage.getMessage('CREATE')+'</a>';
			divlist += '</div>';
	 		break;
	}
  	divlist += "<div id=\"" + id +"-itemlist\"><table id=\"" + id +"-table\">";

	var path = wgArticlePath;
	var dollarPos = path.indexOf('$1');
	if (dollarPos > 0) {
		path = path.substring(0, dollarPos);
	}
	
	//Calculate the size of the property columns depending on the length of the content
	var maxlen1 = 0;
	var maxlen2 = 0;
	if(id=="relation"){
		for (var i = 0; i < len; i++) {
				list[i].getName().length > maxlen2 ? maxlen2 = list[i].getName().length : "";
				// HTML of parameter rows (except first)
				var propertyvalues = list[i].getSplitValues();
	  			for (var j = 0, n = list[i].getArity()-1; j < n; j++) {
	  				propertyvalues[j].length > maxlen1 ? maxlen1 = propertyvalues[j].length : "";
	  			}
		}	
	}
	
	var len1="";
	var len2="";
	if( id == "relation" && maxlen2 != 0){
  		len2 = 20 + 100*(0.55*(maxlen1/(maxlen2+maxlen1)));
  		len2 = 'style="width:'+ len2 + '%;"';
  		len1 = 20 + 100*(0.55 - 0.55*(maxlen1/(maxlen2+maxlen1)));
  		len1 = 'style="width:'+ len1 + '%;"';
	}
	//End calculating size
	
	
  	for (var i = 0; i < len; i++) {
  		var rowSpan = "";
  		var firstValue = "";
  		var multiValue = ""; // for n-ary relations
  		var value = "";
  		var prefix = "";
  		
		switch (id)	{
			case "category":
	  			fn = "catToolBar.getselectedItem(" + i + ")";
	  			firstValue = list[i].getValue ? list[i].getValue().escapeHTML(): "";
	  			prefix = gLanguage.getMessage('CATEGORY_NS');
	 			break
			case "rules":
	  			fn = "ruleToolBar.editRule(" + i + ")";
	  			firstValue = "";
	  			prefix = '';
	 			break
			case "relation":
	  			fn = "relToolBar.getselectedItem(" + i + ")";
	  			prefix = gLanguage.getMessage('PROPERTY_NS');
	  		
	  			var rowSpan = 'rowspan="'+(list[i].getArity()-1)+'"';
	  			var values = list[i].getSplitValues();
	  			firstValue = values[0].escapeHTML();
	  			var valueLink;

				//firstValue.length > maxlen1 ? maxlen1 = firstValue.length : "";

				valueLink = '<span title="' + firstValue + '">' + firstValue + '<span>';
				firstValue = valueLink;
				
	  			// HTML of parameter rows (except first)
	  			for (var j = 1, n = list[i].getArity()-1; j < n; j++) {
	  				//values[j].length > maxlen1 ? maxlen1 = values[j].length : "";
	  				var v = values[j].escapeHTML();
					valueLink = 
						'<span title="' + v + '">' + v +
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
		var shortName = list[i].getName().escapeHTML();
		var elemName;
		//shortName.length > maxlen2 ? maxlen2 = shortName.length : "";
		//Construct the link
		if (id == 'rules') {
			elemName = list[i].getName().escapeHTML();
		} else {
			elemName = '<a href="'+wgServer+path+prefix+list[i].getName().escapeHTML();
			elemName += '" target="blank" title="' + shortName +'">' + shortName + '</a>';
		}
		divlist += 	"<tr>" +
				"<td "+rowSpan+" class=\"" + id + "-col1\" " + len1 + ">" + 
					elemName + 
				" </td>" +
				"<td class=\"" + id + "-col2\"  " + len2 + ">" + firstValue + " </td>" + // first value row
		           	"<td "+rowSpan+" class=\"" + id + "-col3\">" +
		           	'<a href=\"javascript:' + fn + '">' +
		           	'<img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/edit.gif"/></a>' +
		           
		           	'</tr>' + multiValue; // all other value rows
  	}
  	divlist += "</table></div>";
  	return divlist;
},


/*deprecated*/
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
		
		if (this.checkIfEmpty(target) == false
			&& this.handleValidValue(target)) {
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
		if (this.checkIfEmpty(target) == false
			&& this.handleValidValue(target)) {
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
			if (!elem.visible()) {
				continue;
			}
			var oldValue = elem.getAttribute("smwOldValue");
			if (!oldValue || oldValue != elem.value) {
				// content if input field did change => perform check
				if (this.checkIfEmpty(elem) == false
					&& this.handleValidValue(elem)) {
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
		if (this.checkIfEmpty(target) == false
			&& this.handleValidValue(target)) {
			this.handleCheck(target);
			this.handleChange(target);
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
	 * Checks if the value in the input field <target> is valid. A regular
	 * expression decides if this is the case.
	 * 
	 * Example:
	 *    smwValidValue="^.{1,255}$: valid ? (color:white) : (color:red)"
	 * 
	 * @param Object target
	 * 			The target element (an input field)
	 * @return boolean
	 * 		true, if the content of the target is matched by the reg. expr.
	 * 		false, if not
	 */
	handleValidValue: function(target) {
		var check = target.getAttribute("smwValidValue");
		if (!check)	{
			// no constraint defined => value is valid
			return true;
		}
		var regexStr = check.match(/(.*?):\s*(valid\s*\?.*)/);
		if (regexStr) {
			var regex = new RegExp(regexStr[1]);
			var actions = regexStr[2];
			return this.checkWithRegEx(target.value, regex, actions, target);
		}
		return true;
	},
	
	/*
	 * This method handles type checks. Valid type identifiers are:
	 * - regex (valid)
	 * - integer (valid)
	 * - float (valid)
	 * - category (exists, annotated)
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
		if (!target) {
			return;
		}
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
				var e = elem;
				var visible = true;
				while (e != parentDiv) {
					if (!e.visible()) {
						visible = false;
						break;
					}
					e = e.up();
				}
				if (visible == false) {
					continue;
				}
				var valid = elem.getAttribute("smwValid");
				if (valid) {
					if (valid == "false") {
						allValid = false;
//						break;
					} else if (valid != "true") {
						// is the term a conditional?
						var qPos = valid.indexOf('?');
						var func = valid;
						var cond = null;
						if (qPos > -1) {
							func = valid.substring(0, qPos);
							cond = this.parseConditional(func, valid);
						}
						// call a function
						valid = eval(func+'("'+elem.id+'")');
						if (cond) {
							this.performActions(valid ? cond[0] : cond[1], elem);
						}
						if (!valid) {
							allValid = false;
//							break;
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
	 * @return boolean
	 * 		true, if the value was matched by the regular expression
	 * 		false, otherwise
	 */
	checkWithRegEx: function(value, regex, conditional, target) {
		var valid = value.match(regex);
		var c = this.parseConditional("valid", conditional);
		this.performActions(valid ? c[0] : c[1], target);
		return valid;
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
				checkName = gLanguage.getMessage('CATEGORY_NS')+value;
				break;
			case 'property':
				checkName = gLanguage.getMessage('PROPERTY_NS')+value;
				break;
		}
		this.showPendingIndicator(target);
		if (!this.om.existsArticle(checkName, 
		                      this.ajaxCbSchemaCheck.bind(this), 
		                      value, [type, check], target.id)) {
			// there is something wrong with the page name
			this.ajaxCbSchemaCheck(checkName, false, value, [type, check], target);
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
				if (element) {
					element.setStyle({ background:parameter});
				}
				break;
			case 'show':
				var tbc = smw_ctbHandler.findContainer(parameter);
				if (tbc) {
					tbc.show(parameter, true);
				}
				break;
			case 'hide':
				var tbc = smw_ctbHandler.findContainer(parameter);
				if (tbc) {
					tbc.show(parameter, false);
				}
				break;
			case 'call':
				eval(parameter+'("'+element.id+'")');
				break;
			case 'showmessage':
				if (element) {
					var msgElem = $(element.id+'-msg');
					if (msgElem) {
						var msg = gLanguage.getMessage(parameter);
						var value = element.value;
						msg = msg.replace(/\$c/g,value);
						var tbc = smw_ctbHandler.findContainer(msgElem);
						var visible = tbc.isVisible(element.id);
						tbc.replace(msgElem.id,
						            tbc.createText(msgElem.id, msg, '' , true));
					 	// Show the message, if the corresponding element is
					 	// visible.
					 	tbc.show(msgElem.id, visible);
					}
				}
				break;
			case 'hidemessage':
				if (element) {
					var msgElem = $(element.id+'-msg');
					if (msgElem) {
						var tbc = smw_ctbHandler.findContainer(msgElem.id);
						tbc.show(msgElem.id, false);
					}
				}
				break;
			case 'valid':
				if (element) {
					element.setAttribute("smwValid", parameter);
				}
				break;
			case 'attribute':
				var attrValue = parameter.split("=");
				if (attrValue && attrValue.length == 2 && element) {
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
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
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
 * 		  containertype enum
 */
createContainerBody: function(attributes,containertype,headline){
	//defines header and footer
	var header = '<div id="' + this.id + '-box" '+attributes+'>';
	var footer = '</div>';
	//Adds the body to the stbframework
	this.frameworkcontainer.setContent(header + footer,containertype,headline);
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
 * @param boolean autoCompletion
 * 		if <false>, the input field provides no auto completion. If <true> or
 * 		undefined, auto completion is supported.
 */
 
createInput: function(id, description, initialContent, deleteCallback, attributes ,visibility, autoCompletion){
	
	var ac = "wickEnabled";
	if (typeof autoCompletion == "boolean") {
		if (autoCompletion == false) {
			ac = "";
		} 
	}
	var containercontent = '<table class="stb-table stb-input-table ' + this.id + '-table ' + this.id + '-input-table"' +
			(visibility ? '' : 'style="display:none;"')  + 'id="' + this.id + '-table-' + id +'">' +
 			'<tr>' +
 				//Name of Inputfield
 				'<td class="stb-input-col1 ' + this.id + '-input-col1">' + description + '</td>' +
 				//Inputfield 
 				'<td class="stb-input-col2 ' + this.id + '-input-col2">';

	if (initialContent) {
		initialContent = initialContent.escapeHTML();
		initialContent = initialContent.replace(/"/g,"&quot;");
	}
	
 	if (deleteCallback){
		//if deletable change classes and add button			
		containercontent += 
			'<input class="' + ac + ' stb-delinput ' + this.id + '-delinput" ' +
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
			'<input class="' + ac + ' stb-input ' + this.id + '-input" ' +
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
 * Creates a button
 * 
 * @param string id
 * 		id of the element
 * @param string btLabel
 * 		The label of the button
 * @param string callback
 * 		The function that will be called when the button is clicked
 * @param string attributes
 *		attributes which will be passed to the specific element
 * @param boolean visibility
 * 		if false the element will be collapsed initially
 */
createButton: function(id, btLabel, callback, attributes ,visibility){
	
	var containercontent = 
		'<table class="stb-table stb-button-table ' + this.id + '-table ' + this.id + '-button-table"' +
			(visibility ? '' : 'style="display:none;"')  + 'id="' + this.id + '-table-' + id +'">' +
		'<tr>' +
			//Text
			'<td class="stb-button-col ' + this.id + '-button-col">' +
			 '<input type="button" id="' + id + 
			 	'" name="' + id + 
			 	'" value="' + btLabel + 
			 	'" '+ attributes +
			 	'onclick="' + callback + 
			 	'">' +
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
 * @public
 * 
 * Checks, if the element with the given id is visible.
 * @param string id
 * 		The id of the element whose visibility is checked.
 * 
 * @return bool
 * 		true, if the element is visible and
 * 		false otherwise
 */
 isVisible: function(id) {
	var obj = $(this.id + '-table-' + id);
	if (!obj) {
		obj = $(id);
	}
	return (obj) ? obj.visible() : false;
 	
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
 * Sets value of input field and corrects size in ie
 * This is a workaround for IE, which sets the size 
 * of input fields too large in the case the value 
 * attribute is set with a long string bt javascript
 */
setInputValue: function(id,presetvalue){
	if (OB_bd.isIE) {
		var parentwidth = $(id).getWidth();
		$(id).value = presetvalue;
		$(id).setStyle({width: parentwidth + "px"});
	} else {
		$(id).value = presetvalue;
	}
},

/**
 * @public this is just a test function adding some sample boxes
 * 
 * @param none
 */
foo: function(){
	this.createContainerBody('',RELATIONCONTAINER,"Ueberschrift");
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
	var elem = $(elementid);
	if (!elem) {
		return false;
	}
	var ancestorlist = elem.ancestors();
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

// SMW_Marker.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
//Second implementation of marking Templates in Mediawiki
var Marker = Class.create();
Marker.prototype = {
	
	/**
 	* @public constructor 
 	* 
 	* @param rootnode string
 	* 			root element where all elements which have to be marked are child of
 	*/
	initialize: function(rootnode) {
		//root node from which all descendants will be checked for marking 
		//storing the object directly would cause errors, since in most cases the object 
		//is still not present when the constructor is called
		this.rootnode = rootnode;	
		this.markerindex = 0;
		//Stores the information for marking elements after traversing the DOM-Tree
		//Elements are array [0] ID of Marker [1] html of Marker [2] Item to Mark [3] Position Top [4] Position Left [5] Height [6] Width   
		this.transparencymarkerlist = new Array();
		//Stores the information for marking elements after traversing the DOM-Tree
		//Elements are array [0] ID of Marker [1] html of Marker [2] Item to Mark [3] Position Top [4] Position Left
		this.iconmarkerlist = new Array();		
	},
	
	/**
 	* @public marks an element with a transparent layer  
 	* 
 	* @param divtomark object
 	* 			element to mark
 	*/
 	insertMarkers: function(){
 		
 		$(this.rootnode).hide();
 		// transparencyMarkers
 		for(var index=0; index < this.transparencymarkerlist.length; index++){
			if($(this.iconmarkerlist[index][2])){
	 			if($(this.iconmarkerlist[index][2]).tagName.toLowerCase() == 'div'){
	 				if( $(this.iconmarkerlist[index][2]).style.position == ""){
	 					$(this.iconmarkerlist[index][2]).style.position = "relative";
	 				}
	 				 new Insertion.Bottom($(this.transparencymarkerlist[index][2]), this.transparencymarkerlist[index][1]);
					//Set position of the marker		
					$(this.transparencymarkerlist[index][0]).setStyle( {top:  "0px"});
					$(this.transparencymarkerlist[index][0]).setStyle( {left: "0px"});
	 			} else { 
	 				new Insertion.After(this.transparencymarkerlist[index][2], this.transparencymarkerlist[index][1]);
					//Set position of the marker		
					$(this.transparencymarkerlist[index][0]).setStyle( {top: this.transparencymarkerlist[index][3] + "px"});
					$(this.transparencymarkerlist[index][0]).setStyle( {left: this.transparencymarkerlist[index][4] + "px"});
	 			}
				//calculate and set width and height
				var borderwidth = Number(this.getBorderWidth($(this.transparencymarkerlist[index][0]),"left")) + Number(this.getBorderWidth($(this.transparencymarkerlist[index][0]),"right"));
				if(isNaN(Number(borderwidth))) return;
				var borderheight = Number(this.getBorderWidth($(this.transparencymarkerlist[index][0]),"top")) + Number(this.getBorderWidth($(this.transparencymarkerlist[index][0]),"bottom"));
				if(isNaN(Number(borderheight))) return;
				var mheight = this.transparencymarkerlist[index][5] - borderheight;
				var mwidth = this.transparencymarkerlist[index][6] - borderwidth;
				if(mheight > 0 && mwidth > 0 ){
					$(this.transparencymarkerlist[index][0]).setStyle({height: mheight + "px"});
					$(this.transparencymarkerlist[index][0]).setStyle({width: mwidth + "px"});
				}
			}
 		}
 		///*
 		//iconMarkers
 		for(var index=0; index < this.iconmarkerlist.length; index++){
 			if($(this.iconmarkerlist[index][2]).tagName.toLowerCase() == 'div'){
 				if( $(this.iconmarkerlist[index][2]).style.position == ""){
 					$(this.iconmarkerlist[index][2]).style.position = "relative";
 				}
 				new Insertion.Bottom(this.iconmarkerlist[index][2], this.iconmarkerlist[index][1]);
 				//Set position of the marker		
				$(this.iconmarkerlist[index][0]).setStyle( {top:  "0px"});
				$(this.iconmarkerlist[index][0]).setStyle( {left: "0px"});
 			} else {
 				new Insertion.After($(this.iconmarkerlist[index][2]), this.iconmarkerlist[index][1]);
				//Set position of the marker		
				$(this.iconmarkerlist[index][0]).setStyle( {top: this.iconmarkerlist[index][3] + "px"});
				$(this.iconmarkerlist[index][0]).setStyle( {left: this.iconmarkerlist[index][4] + "px"});
 			}
 		} //*/
 		$(this.rootnode).show();
 	},
 	
 	/**
 	* @public marks an element with an overlay
 	* 
 	* @param divtomark object
 	* 			element to mark
 	*/
	transparencyMarker: function(divtomark) {
		if(divtomark == null) return;
		//Create and insert markerdiv
		var marker = '<div id="' + this.markerindex + '-marker" class="div-marker"></div>';
		//Set width&height for the marker so it fits the original element which should be marked
		var width = divtomark.offsetWidth;
		var height = divtomark.offsetHeight;
		//Set position of the marker
		var top = divtomark.offsetTop;
		var left = divtomark.offsetLeft;
		//increase marker index
		this.transparencymarkerlist.push( new Array(this.markerindex+"-marker", marker, $(divtomark).identify(), top, left, height, width ))
		this.markerindex++;	
	},
	
	/**
 	* @public marks an element with an image laying above the upper left corner
 	* 
 	* @param divtomark object
 	* 			element to mark
 	* 		 links
 	* 			links to the templates
 	*/
	iconMarker: function(divtomark,links) {
		if(divtomark == null) return;
		//Create and insert markerdiv
		var marker = Array();
		marker.push('<div id="' + this.markerindex + '-marker" class="icon-marker">');
			//Check if multiple links has been passed and generate one clickable picture for each
			if( links  instanceof Array){					
				for(var index=0; index < links.length; index++){ 
					marker.push('<a href="'+ wgServer + wgScript+ "/" +links[index] +'"><img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/templatemarker.png"/></a>');
				};
			// Check if only one link has been passe	
			} else if ( links  instanceof String || typeof(links) == "string"){
				marker.push('<a href="'+ wgServer + wgScript+ "/" + links +'"><img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/templatemarker.png"/></a>'); 
			//If nothing has been passed, only mark it with a non clickable picture
				} else {
					marker.push('<img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/templatemarker.png"/>');
				}
		marker.push( '</div>');
		//Set position of the marker		
		var top = divtomark.offsetTop;
		var left = divtomark.offsetLeft;
		this.iconmarkerlist.push( new Array(this.markerindex+"-marker", marker.join(''), $(divtomark).identify(), top, left))
		//increase marker index				
		this.markerindex++;
	},

	/**
 	* @public marks an text node and spans with an proper span and image laying above the upper left corner
 	* 
 	* @param node object
 	* 			text node to mark
 	*/
	textMarker: function(node,links){
				//Create span element
				var span = document.createElement('span');
				//Create attributes for span element
				var idattr = document.createAttribute("id");
					idattr.nodeValue = this.markerindex+"-textmarker";
					span.setAttributeNode(idattr);
				//create Classes for marking and colorizing the span so it can be removed later
				var classattr = document.createAttribute("class");
					classattr.nodeValue = "aam_template_highlight text-marker";
					span.setAttributeNode(classattr);
				//Create textcontent for span attribute
				//if(node.nodeValue != null){
					//Don't mark blank strings (e.g. "\n")
					if(node.nodeValue.blank()==true) return;
					//If node is an normal text node use the nodeValue
					var textdata = document.createTextNode(node.nodeValue);
						span.appendChild(textdata);
				//} else if(node.innerHTML !=null) {
					//If node is not an normal text node (e.g. span) use innerhtml
					//var textdata = document.createTextNode(node.innerHTML);
				//		span.innerHTML = node.innerHTML;
				//}
				//var replacement 
				node.parentNode.replaceChild(span, node);
				this.iconMarker($(this.markerindex+"-textmarker"),links);
	},
	
	/**
 	* @public Gets the border with of an element as number, if it's defined in pixel in the css
 	* 
 	* @param 	el object
 	* 				element with border
 	* 			borderposition string
 	* 				location of the border, possible values: "left", "right", "bottom", "top"
 	*/
	getBorderWidth: function(el, borderposition)
	{
		//retrieve css value
		var borderwidth = $(el).getStyle("border-"+borderposition+"-width");
		//parse for px unit
		var borderregex = /(\d*)(px)/;
		var regexresult;
		if(regexresult = borderregex.exec(borderwidth)) {
			return regexresult[1];
		} else {
			return 0;
		}
	}, 
	
	
	/**
 	* @public Gets all descendants and removes markers 
 	* 
 	* @param rootnode object 
 	* 				Element which descendants will be checked for removing
 	*/
	removeMarkers: function(){
			$(this.rootnode).hide();
			var markers = $$('.icon-marker');
			for(var index=0; index < markers.length; index++){
				markers[index].remove();
			}
			var markers = $$('.div-marker');
			for(var index=0; index < markers.length; index++){
				markers[index].remove();
			}
			var markers = $$('.text-marker');
			for(var index=0; index < markers.length; index++){
				markers[index].remove();
			}
			this.transparencymarkerlist = new Array();
			this.iconmarkerlist = new Array();
			$(this.rootnode).show();
	},
	
	/**
 	* @public Marks all templates
 	* 
 	*/	
	markNodes: function(){
		this.removeMarkers();		
		//var time = new Date();
		//var timestamp1 = time;		
		this.mark($(this.rootnode), true);
		this.insertMarkers();
		//time = new Date();
		//var timestamp2 = time;
		//alert(timestamp2 -timestamp1 );
	},
	
	/**
 	* @public Checks all child nodes for templates and marks the proper Elements
 	* 
 	* @param 
 	*/	
	mark: function(rootnode, mark){
		//Stores template links found by checking the subtree of childelements, so elements can be marked with later
		//return:  -1 the currently opened template was close in the subtree
		// 			0 nothing has been found in the subtree
		// 		 else template found returned 
		var templates = Array();		
		templates.push(0);		
		//Stores the templatename and the id of the current open but not closed template
		var currentTmpl = null;
		//Get Childelements
		var childelements = rootnode.childNodes;
		//Walk over every next sibling
		//this uses plain javascript functions, since prototype doesn't support textnodes
		for(var index=0; index < childelements.length; index++){
			//Get current node 
			var node = childelements[index];			
			//If nodetyp is textnode and template tag is open but not closed
			if( node.nodeType == 3 && currentTmpl != null ){
				//mark text
				if( mark == true) this.textMarker(node,wgServer + wgScript+ "/" +currentTmpl);
			//If nodetype is elementnode
			} else if(node.nodeType == 1 ){
				//Treating different types of elements
				var tag = node.tagName.toLowerCase()	
					//Treat template anchors
					if(tag == 'a'){
						//Find opening and closing tags
							//Check if this is an opening anchor, indicating that a template starts 
							var attrtype = $(node).readAttribute('type');
							if( attrtype =='template'){
			  					currentTmpl = $(node).readAttribute('tmplname');
			  					templates.push(currentTmpl); 			
			  					continue;
			  				}
			  				//Check if this is an closing anchor, indicating that a template ends
			  				if( attrtype =='templateend'){
			  					currentTmpl = null;
			  					templates[0] = -1;
			  				 	continue;
			  				}
		  			}
					var result;
					if(currentTmpl != null ){
  						result = this.mark(node,false);
  						var links = currentTmpl;
  						if(result[0] != 0 && result[0] != -1 ) links = Array(currentTmpl).push(result[0]);
  						if(result.length > 1){
  							result.shift()
  							links = Array(links).concat(result);
  						}						
  						if(mark == true && $(node).visible()){
  							this.transparencyMarker(node);
  							this.iconMarker(node,links);
  						}
  					} else {
  						
  						(mark == true) ? result = this.mark(node,true) : result = this.mark(node,false);
  					}
 					  						
  					switch(result[0]){
  						//template close
  						case -1: 
  							currentTmpl = null;
  							break
  						//nothing found
  						case 0:
  							break
  						//Opened template	
  						default:
  							templates.push( result[0] );
  							currentTmpl = result[0];
  					}
  						
					if(result.length > 1){
						result.shift();
						templates = templates.concat( result );
					};
				

			}
		}
		//return current opened Template
		if(currentTmpl != null ){
			templates[0] = currentTmpl; 
		}
		return templates;	
	}
}


//var smwhg_marker = new Marker('innercontent');
var smwhg_marker = new Marker('bodyContent');
Event.observe(window, 'resize', smwhg_marker.markNodes.bind(smwhg_marker));
Event.observe(window, 'load', smwhg_marker.markNodes.bind(smwhg_marker));

// SMW_Category.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
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

var SMW_CAT_VALID_CATEGORY_NAME =
	'smwValidValue="^[^<>\|!&$%&\/=\?]{1,255}$: valid ' +
		'? (color: white, hideMessage, valid:true) ' +
	 	': (color: red, showMessage:CATEGORY_NAME_TOO_LONG, valid:false)" ';

var SMW_CAT_CHECK_CATEGORY = 
	'smwCheckType="category: exists ' +
		'? (color: lightgreen, hideMessage, valid:true, attribute:catExists=true) ' +
	 	': (color: orange, showMessage:CATEGORY_DOES_NOT_EXIST, valid:true, attribute:catExists=false)" ';

var SMW_CAT_CHECK_CATEGORY_CREATE = 
	'smwCheckType="category: exists ' +
		'? (color: lightgreen, hideMessage, valid:true, attribute:catExists=true, hide:cat-addandcreate, show:cat-confirm) ' +
	 	': (color: orange, showMessage:CATEGORY_DOES_NOT_EXIST, valid:true, attribute:catExists=false, show:cat-confirm, show:cat-addandcreate)" ';

var SMW_CAT_CHECK_CATEGORY_IIE = // Invalid if exists
	'smwCheckType="category:exists ' +
		'? (color: red, showMessage:CATEGORY_ALREADY_EXISTS, valid:false) ' +
	 	': (color: lightgreen, hideMessage, valid:true)" ';

var SMW_CAT_CHECK_EMPTY = 
	'smwCheckEmpty="empty' +
		'? (color:red, showMessage:MUST_NOT_BE_EMPTY, valid:false) ' +
		': (color:white, hideMessage)"';

var SMW_CAT_CHECK_EMPTY_CM = 
	'smwCheckEmpty="empty' +
		'? (color:red, showMessage:MUST_NOT_BE_EMPTY, valid:false, hide:cat-confirm, hide:cat-addandcreate) ' +
		': (color:white, hideMessage)"';

var SMW_CAT_ALL_VALID =	
	'smwAllValid="allValid ' +
 		'? (show:cat-confirm, hide:cat-invalid) ' +
 		': (show:cat-invalid, hide:cat-confirm, hide:cat-addandcreate)"';
 		
var SMW_CAT_ALL_VALID_ANNOTATED =	
	'smwAllValid="allValid ' +
 		'? (show:cat-confirm, call:catToolBar.finalCategoryCheck) ' +
 		': (hide:cat-confirm, hide:cat-addandcreate, call:catToolBar.finalCategoryCheck)"';

var SMW_CAT_HINT_CATEGORY =
	'typeHint = "' + SMW_CATEGORY_NS + '" position="fixed"';

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

showToolbar: function(){
	this.categorycontainer.setHeadline(gLanguage.getMessage('CATEGORIES'));
	if (wgAction == 'edit') {
		// Create a wiki text parser for the edit mode. In annotation mode,
		// the mode's own parser is used.
		this.wtp = new WikiTextParser();
	}
	this.om = new OntologyModifier();
	this.fillList(true);
},

callme: function(event){
	if ((wgAction == "edit" || wgAction == "annotate")
	    && stb_control.isToolbarAvailable()){
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
	if (this.wtp) {
		this.wtp.initialize();
		this.categorycontainer.setContent(this.genTB.createList(this.wtp.getCategories(),"category"));
		this.categorycontainer.contentChanged();
	}
},

/**
 * @public 
 * 
 * Sets the wiki text parser <wtp>.
 * @param WikiTextParser wtp 
 * 		The parser that is used for this toolbar container.	
 * 
 */
setWikiTextParser: function(wtp) {
	this.wtp = wtp;
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

enableAnnotation: function(enable) {
	if ($('cat-menu-annotate')) {
		var enabled = $('cat-menu-annotate').readAttribute('enabled') == 'true';
		if (enable == enabled) {
			return;
		}
		if (enable) {
//		$('cat-menu-annotate').show();
			$('cat-menu-annotate').replace(
				'<a id="cat-menu-annotate" enabled="true" href="javascript:catToolBar.newItem()" class="menulink">'
				+ gLanguage.getMessage('ANNOTATE')
				+ '</a>');
		} else {
//			$('cat-menu-annotate').hide();
			$('cat-menu-annotate').replace(
				'<span id="cat-menu-annotate" enabled="false" class="menulink" style="color:grey">&nbsp;'
				+ gLanguage.getMessage('ANNOTATE')
				+ '</span>');
		}
	}
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

/**
 * Creates the content of a <contextMenuContainer> for annotating a category.
 * 
 * @param ContextMenuFramework contextMenuContainer
 * 		The container of the context menu.
 */
createContextMenu: function(contextMenuContainer) {
	if (this.toolbarContainer) {
		this.toolbarContainer.release();
	}
	this.toolbarContainer = new ContainerToolBar('category-content',600,contextMenuContainer);
	var tb = this.toolbarContainer;
	tb.createContainerBody(SMW_CAT_ALL_VALID_ANNOTATED, CATEGORYCONTAINER, gLanguage.getMessage('ANNOTATE_CATEGORY'));

	this.currentAction = "annotate";
	
    this.wtp.initialize();
	var selection = this.wtp.getSelection(true);
	
	/*STARTLOG*/
    smwhgLogger.log(selection,"STB-Categories","annotate_clicked");
	/*ENDLOG*/

	tb.append(tb.createInput('cat-name', 
							 gLanguage.getMessage('CATEGORY'), '', '',
	                         SMW_CAT_CHECK_CATEGORY_CREATE +
	                         SMW_CAT_CHECK_EMPTY_CM +
	                         SMW_CAT_VALID_CATEGORY_NAME +
	                         SMW_CAT_HINT_CATEGORY,
	                         true));
	tb.setInputValue('cat-name',selection);	                         
	tb.append(tb.createText('cat-name-msg', 
							gLanguage.getMessage('ENTER_NAME'), '' , true));
	var links = [['catToolBar.addItem(false)',gLanguage.getMessage('ADD'), 'cat-confirm',
	                                     gLanguage.getMessage('INVALID_VALUES'), 'cat-invalid'],
				 ['catToolBar.addItem(true)',gLanguage.getMessage('ADD_AND_CREATE_CAT'), 'cat-addandcreate']
	                                     
				];
	tb.append(tb.createLink('cat-links', links, '', true));
				
	tb.finishCreation();
	$('cat-addandcreate').hide();
	gSTBEventActions.initialCheck($("category-content-box"));
},

/**
 * This method is called, when the name of a category has been changed in the
 * input field of the context menu. If the category is already annotated in the
 * wiki text, the links for adding the category are hidden.
 * 
 * @param string target
 * 		ID of the element, on which the change event occurred.
 */
finalCategoryCheck: function(target) {
	var catName = $('cat-name').value;
	var cat = this.wtp.getCategory(catName);
	if (cat) {
		gSTBEventActions.performSingleAction('showmessage', 
											 'CATEGORY_ALREADY_ANNOTATED', 
											 $('cat-name'));
		gSTBEventActions.performSingleAction('hide', 'cat-confirm');			
		gSTBEventActions.performSingleAction('hide', 'cat-addandcreate');			
	}
},

/**
 * Annotate a category in the article as specified in the input field with id 
 * 'cat-name'.
 * 
 * @param boolean create
 * 		If <true>, the category is created, if it does not already exist.
 */
addItem: function(create) {
	var catName = $("cat-name");
	/*STARTLOG*/
    smwhgLogger.log(catName.value,"STB-Categories","annotate_added");
	/*ENDLOG*/
	this.wtp.initialize();
	var name = catName.value;
	this.wtp.addCategory(name, true);
	this.fillList(true);
	
	// Create the category, if it does not exist.
	if (create && catName.getAttribute("catexists") == "false") {
		this.om.createCategory(name, "");
		/*STARTLOG*/
	    smwhgLogger.log(name,"STB-Categories","create_added");
		/*ENDLOG*/
	}
},

newItem: function() {

	this.showList = false;
	this.currentAction = "annotate";
	
    this.wtp.initialize();
	var selection = this.wtp.getSelection(true);
	
	/*STARTLOG*/
    smwhgLogger.log(selection,"STB-Categories","annotate_clicked");
	/*ENDLOG*/

	var tb = this.createToolbar(SMW_CAT_ALL_VALID_ANNOTATED);	
	if (wgAction == 'edit') {
		tb.append(tb.createText('cat-help-msg', 
		                        gLanguage.getMessage('ANNOTATE_CATEGORY'),
		                        '' , true));
	}
	tb.append(tb.createInput('cat-name', 
							 gLanguage.getMessage('CATEGORY'), '', '',
	                         SMW_CAT_CHECK_CATEGORY_CREATE +
	                         SMW_CAT_CHECK_EMPTY_CM +
	                         SMW_CAT_VALID_CATEGORY_NAME +
	                         SMW_CAT_HINT_CATEGORY,
	                         true));
	tb.setInputValue('cat-name',selection);	                         
	tb.append(tb.createText('cat-name-msg', 
							gLanguage.getMessage('ENTER_NAME'), '' , true));
	var links = [['catToolBar.addItem(false)',gLanguage.getMessage('ADD'), 'cat-confirm',
	                                     gLanguage.getMessage('INVALID_VALUES'), 'cat-invalid'],
				 ['catToolBar.addItem(true)',gLanguage.getMessage('ADD_AND_CREATE_CAT'), 'cat-addandcreate'],
				 ['catToolBar.cancel()', gLanguage.getMessage('CANCEL')]
				];
	tb.append(tb.createLink('cat-links', links, '', true));
				
	tb.finishCreation();
	$('cat-addandcreate').hide();
	gSTBEventActions.initialCheck($("category-content-box"));
	//Sets Focus on first Element
	setTimeout("$('cat-name').focus();",50);
},


CreateSubSup: function() {
	this.currentAction = "sub/super-category";
	this.showList = false;
	
    this.wtp.initialize();
	var selection = this.wtp.getSelection(true);
	
	/*STARTLOG*/
    smwhgLogger.log(selection,"STB-Categories","sub/super-category_clicked");
	/*ENDLOG*/

	var tb = this.createToolbar(SMW_CAT_SUB_SUPER_ALL_VALID);	
	tb.append(tb.createText('cat-help-msg', gLanguage.getMessage('DEFINE_SUB_SUPER_CAT'), '' , true));
	tb.append(tb.createInput('cat-subsuper', gLanguage.getMessage('CATEGORY'),
	                         '', '',
	                         SMW_CAT_SUB_SUPER_CHECK_CATEGORY +
	                         SMW_CAT_CHECK_EMPTY +
	                         SMW_CAT_HINT_CATEGORY,
	                         true));
	tb.setInputValue('cat-subsuper',selection);	                         
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
 	this.om.createSuperCategory(name, "", openTargetArticle, this.wtp);
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

	this.currentAction = "create";
	this.showList = false;
 
    this.wtp.initialize();
	var selection = this.wtp.getSelection(true);
   
	/*STARTLOG*/
    smwhgLogger.log(selection,"STB-Categories","create_clicked");
	/*ENDLOG*/
    
	var tb = this.createToolbar(SMW_CAT_ALL_VALID);	
	tb.append(tb.createText('cat-help-msg', gLanguage.getMessage('CREATE_NEW_CATEGORY'), '' , true));
	tb.append(tb.createInput('cat-name', gLanguage.getMessage('CATEGORY'), 
							 '', '',
	                         SMW_CAT_CHECK_CATEGORY_IIE +
	                         SMW_CAT_CHECK_EMPTY +
	                         SMW_CAT_VALID_CATEGORY_NAME +
	                         SMW_CAT_HINT_CATEGORY,
	                         true));
	tb.setInputValue('cat-name',selection);	                         
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
	// Create the new category
	this.om.createCategory(catName, "");

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
	
	tb.append(tb.createInput('cat-name', gLanguage.getMessage('CATEGORY'), '', '',
	                         SMW_CAT_CHECK_CATEGORY +
	                         SMW_CAT_CHECK_EMPTY +
	                         SMW_CAT_VALID_CATEGORY_NAME +
	                         SMW_CAT_HINT_CATEGORY,
	                         true));
	tb.setInputValue('cat-name',annotatedElements[selindex].getName());	                         
	                         
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
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
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
var RelationToolBar = Class.create();

var SMW_REL_VALID_PROPERTY_NAME =
	'smwValidValue="^[^<>\\|&$\\/=\\?\\{\\}\\[\\]]{1,255}$: valid ' +
		'? (color: white, hideMessage, valid:true) ' +
	 	': (color: red, showMessage:PROPERTY_NAME_TOO_LONG, valid:false)" ';

var SMW_REL_VALID_PROPERTY_VALUE =
	'smwValidValue="^.{1,255}$: valid ' +
		'? (color: white, hideMessage, valid:true) ' +
	 	': (color: orange, showMessage:PROPERTY_VALUE_TOO_LONG, valid:true)" ';

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

var SMW_REL_VALID_CATEGORY_NAME =
	'smwValidValue="^[^<>\\|!&$%&\\/=\\?]{1,255}$: valid ' +
		'? (color: white, hideMessage, valid:true) ' +
	 	': (color: red, showMessage:CATEGORY_NAME_TOO_LONG, valid:false)" ';

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
		'? (color:red, showMessage:MUST_NOT_BE_EMPTY, valid:false, call:relToolBar.updateTypeHint) ' +
		': (color:white, hideMessage, valid:true, call:relToolBar.updateTypeHint)"';

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
	'typeHint = "' + SMW_CATEGORY_NS + '" position="fixed"';

var SMW_REL_HINT_PROPERTY =
	'typeHint="'+ SMW_PROPERTY_NS + '" position="fixed"';

var SMW_REL_HINT_INSTANCE =
	'typeHint="'+ SMW_INSTANCE_NS + '" position="fixed"';

var SMW_REL_TYPE_CHANGED =
	'smwChanged="(call:relToolBar.relTypeChanged)"';

RelationToolBar.prototype = {

initialize: function() {
	this.genTB = new GenericToolBar();
	this.toolbarContainer = null;
	this.showList = true;
	this.currentAction = "";
},

showToolbar: function(){
	this.relationcontainer.setHeadline(gLanguage.getMessage('PROPERTIES'));
	if (wgAction == 'edit') {
		// Create a wiki text parser for the edit mode. In annotation mode,
		// the mode's own parser is used.
		this.wtp = new WikiTextParser();
	}
	this.om = new OntologyModifier();
	this.fillList(true);

},

callme: function(event){
	if((wgAction == "edit" || wgAction == "annotate")
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
	if (this.wtp) {
		this.wtp.initialize();
		this.relationcontainer.setContent(this.genTB.createList(this.wtp.getRelations(),"relation"));
		this.relationcontainer.contentChanged();
	}
},

/**
 * @public 
 * 
 * Sets the wiki text parser <wtp>.
 * @param WikiTextParser wtp 
 * 		The parser that is used for this toolbar container.	
 * 
 */
setWikiTextParser: function(wtp) {
	this.wtp = wtp;
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

/**
 * Creates the content of a <contextMenuContainer> for annotating a property.
 * 
 * @param ContextMenuFramework contextMenuContainer
 * 		The container of the context menu.
 * @param string value (optional)
 * 		The default value for the property. If it is not given, the current 
 * 		selection of the wiki text parser is used.
 * @param string repr (optional)
 * 		The default representation for the property. If it is not given, the current 
 * 		selection of the wiki text parser is used.
 */
createContextMenu: function(contextMenuContainer, value, repr) {
	if (this.toolbarContainer) {
		this.toolbarContainer.release();
	}
	this.toolbarContainer = new ContainerToolBar('relation-content',500,contextMenuContainer);
	var tb = this.toolbarContainer;
	tb.createContainerBody(SMW_REL_ALL_VALID, RELATIONCONTAINER, gLanguage.getMessage('SPECIFY_PROPERTY'));

    this.wtp.initialize();
	this.currentAction = "annotate";

	var valueEditable = false;
	if (!value) {
		value = this.wtp.getSelection(true);
		//replace newlines by spaces
		value = value.replace(/\n/,' ');
		repr = value;
		valueEditable = true;
	}
	
	/*STARTLOG*/
    smwhgLogger.log(value,"AAM-Properties","annotate_clicked");
	/*ENDLOG*/
	
	tb.append(tb.createInput('rel-name', gLanguage.getMessage('PROPERTY'), '', '',
	                         SMW_REL_CHECK_PROPERTY_UPDATE_SCHEMA +
	                         SMW_REL_CHECK_EMPTY +
	                         SMW_REL_VALID_PROPERTY_NAME +
	                         SMW_REL_HINT_PROPERTY,
	                         true));
	tb.setInputValue('rel-name','');
	tb.append(tb.createText('rel-name-msg', gLanguage.getMessage('ENTER_NAME'), '' , true));
	
	tb.append(tb.createInput('rel-value-0', gLanguage.getMessage('PAGE'), '', '', '',
							 SMW_REL_CHECK_EMPTY_NEV + 
							 SMW_REL_HINT_INSTANCE +
							 SMW_REL_VALID_PROPERTY_VALUE,
	                         true));
	tb.setInputValue('rel-value-0', value);
		                         
	tb.append(tb.createText('rel-value-0-msg', gLanguage.getMessage('ANNO_PAGE_VALUE'), '' , true));
	
	tb.append(tb.createInput('rel-show', gLanguage.getMessage('SHOW'), '', '', '', true));
	tb.setInputValue('rel-show', repr);	                         
	
	var links = [['relToolBar.addItem()',
	              gLanguage.getMessage('ADD'), 'rel-confirm', 
	              gLanguage.getMessage('INVALID_VALUES'), 'rel-invalid']];
	
	tb.append(tb.createLink('rel-links', links, '', true));
				
	tb.finishCreation();
	
	if (wgAction == 'annotate') {
		$('rel-show').disable();
		if (!valueEditable) {
			$('rel-value-0').disable();
		}
	}
	
//	$('relation-content-table-rel-show').hide();
	gSTBEventActions.initialCheck($("relation-content-box"));
	
	//Sets Focus on first Element
	setTimeout("if ($('rel-name')) $('rel-name').focus();",250);
	
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
    this.wtp.initialize();
	this.showList = false;
	this.currentAction = "annotate";

	var selection = this.wtp.getSelection(true);
	/*STARTLOG*/
    smwhgLogger.log(selection,"STB-Properties","annotate_clicked");
	/*ENDLOG*/
	
	var tb = this.createToolbar(SMW_REL_ALL_VALID);	
	tb.append(tb.createText('rel-help_msg', gLanguage.getMessage('ANNOTATE_PROPERTY'), '' , true));
	tb.append(tb.createInput('rel-name', gLanguage.getMessage('PROPERTY'), '', '',
	                         SMW_REL_CHECK_PROPERTY_UPDATE_SCHEMA +
	                         SMW_REL_CHECK_EMPTY +
	                         SMW_REL_VALID_PROPERTY_NAME +
	                         SMW_REL_HINT_PROPERTY,
	                         true));
	tb.setInputValue('rel-name','');
	tb.append(tb.createText('rel-name-msg', gLanguage.getMessage('ENTER_NAME'), '' , true));
	tb.append(tb.createInput('rel-value-0', gLanguage.getMessage('PAGE'), '', '', 
							 SMW_REL_CHECK_EMPTY_NEV +
							 SMW_REL_HINT_INSTANCE +
							 SMW_REL_VALID_PROPERTY_VALUE,
	                         true));
	tb.setInputValue('rel-value-0', selection);	                         
	                         
	tb.append(tb.createText('rel-value-0-msg', gLanguage.getMessage('ANNO_PAGE_VALUE'), '' , true));
	
	tb.append(tb.createInput('rel-show', gLanguage.getMessage('SHOW'), '', '', '', true));
	tb.setInputValue('rel-show','');
	
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
	sajax_do_call('smwf_om_RelationSchemaData',
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
	var selection = this.wtp.getSelection(true);
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
				  tb.createInput('rel-value-'+ i, parameterNames[i], '', '', 
								 SMW_REL_CHECK_EMPTY_NEV +
							     SMW_REL_VALID_PROPERTY_VALUE + 
								 (parameterNames[i] == "Page" ? SMW_REL_HINT_INSTANCE : ""),
		                         true));
		tb.setInputValue('rel-value-'+ i, value);    
		                         
		tb.insert('rel-value-'+ i,
				  tb.createText('rel-value-'+i+'-msg', gLanguage.getMessage('ANNO_PAGE_VALUE'), '' , true));
		selection = "";
	}
	
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));
},

CreateSubSup: function() {

	this.showList = false;
	this.currentAction = "sub/super-category";

	this.wtp.initialize();
	var selection = this.wtp.getSelection(true);
	/*STARTLOG*/
    smwhgLogger.log(selection,"STB-Properties","sub/super-property_clicked");
	/*ENDLOG*/

	var tb = this.createToolbar(SMW_REL_SUB_SUPER_ALL_VALID);	
	tb.append(tb.createText('rel-help-msg', gLanguage.getMessage('DEFINE_SUB_SUPER_PROPERTY'), '' , true));
	tb.append(tb.createInput('rel-subsuper', 
							 gLanguage.getMessage('PROPERTY'), '', '',
	                         SMW_REL_SUB_SUPER_CHECK_PROPERTY +
	                         SMW_REL_CHECK_EMPTY +
	                         SMW_REL_VALID_PROPERTY_NAME +
	                         SMW_REL_HINT_PROPERTY,
	                         true));
	tb.setInputValue('rel-subsuper', selection);	                         
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

 	this.om.createSuperProperty(name, "", openTargetArticle, this.wtp);
 	this.fillList(true);
},

/**
 * Sets the auto completion type hint of the relation name field depending on the
 * value of the element with ID <elementID>.
 * The following formats are supported:
 * - Dates (yyyy-mm-dd and dd-mm-yyyy, separator can be "-" , "/" and ".")
 * - Email addresses
 * - Numerical values with units of measurement
 * - Floats, integers
 * - Instances that belong to a category.
 * If no properties with these restrictions are found, all properties that match
 * a part of the entered property name are listed.  
 */
updateTypeHint: function(elementID) {
	var elem = $(elementID);
	var value = elem.value;
	var relation = $('rel-name');
	
	var hint = SMW_PROPERTY_NS;
	
	// Date: yyyy-mm-dd
	var date = value.match(/\d{1,5}[- \/.](0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])/);
	if (!date) {
		// Date: dd-mm-yyyy
		date = value.match(/(0[1-9]|[12][0-9]|3[01])[- \/.](0[1-9]|1[012])[- \/.]\d{1,5}/);
	} 
	var email = value.match(/^([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})$/i);
	var numeric = value.match(/([+-]?\d*(\.\d+([eE][+-]?\d*)?)?)\s*(.*)/);
	
	if (date) {
		hint = '_dat;'+SMW_PROPERTY_NS;
	} else if (email) {
		hint = '_ema;'+SMW_PROPERTY_NS;
	} else if (numeric) {
		var number = numeric[1];
		var unit = numeric[4];
		var mantissa = numeric[2];
		if (number && unit) {
			var c = unit.charCodeAt(0);
			if (unit === "K" || unit === '°C' || unit === '°F' ||
				(c == 176 && unit.length == 2 && 
				 (unit.charAt(1) == 'C' || unit.charAt(1) == 'F'))) {
				hint = "_tem;"+SMW_PROPERTY_NS;
			} else {
				hint = unit+';'+SMW_PROPERTY_NS;
			}
		} else if (number && mantissa) {
			hint = '_flt;'+SMW_PROPERTY_NS;
		} else if (number) {
			hint = '_num;_int;_flt;'+SMW_PROPERTY_NS;
		} else if (unit) {
			hint = ':'+unit+';'+SMW_PROPERTY_NS;
		}
	}
	relation.setAttribute('typeHint', hint);
	
},

newRelation: function() {
    gDataTypes.refresh();
    
	this.showList = false;
	this.currentAction = "create";
	
    this.wtp.initialize();
	var selection = this.wtp.getSelection(true);
   
	/*STARTLOG*/
    smwhgLogger.log(selection,"STB-Properties","create_clicked");
	/*ENDLOG*/

	var domain = (wgNamespaceNumber == 14)
					? wgTitle  // current page is a category
					: "";
	var tb = this.createToolbar(SMW_REL_ALL_VALID);	
	tb.append(tb.createText('rel-help-msg', gLanguage.getMessage('CREATE_NEW_PROPERTY'), '' , true));
	tb.append(tb.createInput('rel-name', 
							 gLanguage.getMessage('PROPERTY'), '', '',
	                         SMW_REL_CHECK_PROPERTY_IIE +
	                         SMW_REL_CHECK_EMPTY+
	                         SMW_REL_VALID_PROPERTY_NAME +
	                         SMW_REL_HINT_PROPERTY,
	                         true));
	tb.setInputValue('rel-name', selection);	                         
	tb.append(tb.createText('rel-name-msg', gLanguage.getMessage('ENTER_NAME'), '' , true));
	
	tb.append(tb.createInput('rel-domain', gLanguage.getMessage('DOMAIN'), '', '', 
						     SMW_REL_CHECK_CATEGORY +
						     SMW_REL_VALID_CATEGORY_NAME + 
						     SMW_REL_CHECK_EMPTY_WIE +
						     SMW_REL_HINT_CATEGORY,
	                         true));
	tb.setInputValue('rel-domain', domain);	                         
	tb.append(tb.createText('rel-domain-msg', gLanguage.getMessage('ENTER_DOMAIN'), '' , true));

	this.addTypeInput();
		
	var links = [['relToolBar.addTypeInput()', gLanguage.getMessage('ADD_TYPE')]];
	tb.append(tb.createLink('rel-add-links', links, '', true));		
			
	links = [['relToolBar.createNewRelation()',
			  gLanguage.getMessage('CREATE'), 'rel-confirm', 
			  gLanguage.getMessage('INVALID_VALUES'), 'rel-invalid'],
			 ['relToolBar.cancel()', gLanguage.getMessage('CANCEL')]
			];
	tb.append(tb.createLink('rel-links', links, '', true));
	
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));
	

	//Sets Focus on first Element
	setTimeout("$('rel-name').focus();",50);

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
	
	var datatypes = this.getDatatypeOptions();
	var page = gLanguage.getMessage('TYPE_PAGE_WONS');
	var pIdx = datatypes.indexOf(page);
	tb.insert(insertAfter,
			  tb.createDropDown('rel-type-'+i, gLanguage.getMessage('TYPE'), 
	                            this.getDatatypeOptions(), 
	                            "relToolBar.removeType('rel-type-"+i+"')",
	                            pIdx, 
	                            SMW_REL_NO_EMPTY_SELECTION +
	                            SMW_REL_TYPE_CHANGED, true));
	var msgID = 'rel-type-'+i+'-msg';                           
	tb.insert('rel-type-'+i,
	          tb.createText(msgID, gLanguage.getMessage('ENTER_TYPE'), '' , true));

	tb.insert(msgID,
			  tb.createInput('rel-range-'+i, gLanguage.getMessage('RANGE'), '', '',
						     SMW_REL_CHECK_CATEGORY + SMW_REL_CHECK_EMPTY_WIE +
						     SMW_REL_VALID_CATEGORY_NAME + SMW_REL_HINT_CATEGORY,
	                         true));
	tb.setInputValue('rel-range-'+i, '');
	tb.insert('rel-range-'+i,
	          tb.createText('rel-range-'+i+'-msg', gLanguage.getMessage('ENTER_RANGE'), '' , true));
	          
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

removeType: function(id) {
	var typeInput = $(id);
	if (typeInput != null) {
		var tb = this.toolbarContainer;
		var rowsAfterRemoved = typeInput.parentNode.parentNode.nextSibling;

		// get ID of range input to be removed.
		var idOfValueInput = typeInput.getAttribute('id');
		var i = parseInt(idOfValueInput.substr(idOfValueInput.length-1, idOfValueInput.length));

		// remove it
		tb.remove(id);
		if ($(id+'-msg')) {
			tb.remove(id+'-msg');
		}
		var rid = id.replace(/type/, 'range');
		tb.remove(rid);
		if ($(rid+'-msg')) {
			tb.remove(rid+'-msg');
		}
		
		// remove gap from IDs
		id = idOfValueInput.substr(0, idOfValueInput.length-1);
		var obj;
		while ((obj = $(id + ++i))) {
			// is there a delete-button
			var delBtn = obj.up().up().down('a');
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
			var rid = id.replace(/type/, 'range');
			obj = $(rid + i);
			tb.changeID(obj, rid + (i-1));
			if ((obj = $(rid + i + '-msg'))) {
				tb.changeID(obj, rid + (i-1) + '-msg');
			}
			
		}
		tb.finishCreation();
		gSTBEventActions.initialCheck($("relation-content-box"));

	}

},

relTypeChanged: function(target) {
	var target = $(target);
	
	var typeIdx = target.id.substring(9);
	var rangeId = "rel-range-"+typeIdx;
	
	var attrType = target[target.selectedIndex].text;
	
	var isPage = attrType == gLanguage.getMessage('TYPE_PAGE_WONS');
	var tb = relToolBar.toolbarContainer;
	tb.show(rangeId, isPage);
	if (!isPage) {
		tb.show(rangeId+'-msg', false);
	}
	gSTBEventActions.initialCheck($("relation-content-box"));
	
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
	while($('rel-type-'+i) != null) {
		var obj = $('rel-type-'+i);
		var value = obj.options[obj.selectedIndex].text;
		if (value != gLanguage.getMessage('TYPE_PAGE_WONS')) {
			rangesAndTypes.push(gLanguage.getMessage('TYPE_NS')+value); // add as type
		} else {
			var range = $('rel-range-'+i).value;
			rangesAndTypes.push((range && range != '')
									? gLanguage.getMessage('CATEGORY_NS')+range 	// add as category
			                        : "");
		}
		i++;
	}
	/*STARTLOG*/
	var signature = "";
	for (i = 0; i < rangesAndTypes.length; i++) {
		signature += (rangesAndTypes[i] != '') ? rangesAndTypes[i] : gLanguage.getMessage('TYPE_PAGE');
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
    this.wtp.initialize();
    var selection = this.wtp.getSelection(true);

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
	
	tb.append(tb.createInput('rel-name', 
							 gLanguage.getMessage('OBJECT'), '', '',
	                         SMW_REL_CHECK_EMPTY_NEV +
	                         SMW_REL_VALID_PROPERTY_NAME +
	                         SMW_REL_HINT_INSTANCE,
	                         true));
	tb.setInputValue('rel-name', selection);	                         
	                         
	tb.append(tb.createText('rel-name-msg', '', '' , true));
	
	tb.append(tb.createInput('rel-show', gLanguage.getMessage('SHOW'), 
	                         '', '', '', true));
	tb.setInputValue('rel-show', (wgAction == 'annotate') ? selection : '');	                         
	                         
	var links = [['relToolBar.addPartOfRelation()',gLanguage.getMessage('ADD'), 'rel-confirm', 
	                                               gLanguage.getMessage('INVALID_VALUES'), 'rel-invalid'],
				 ['relToolBar.cancel()', gLanguage.getMessage('CANCEL')]
				];
	tb.append(tb.createLink('rel-links', links, '', true));
				
	tb.finishCreation();
	if (wgAction == 'annotate') {
		$('rel-show').disable();
		$('rel-value-0').disable();
	}
	
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
		var schemaValid = true;

		if (request.responseText != 'noSchemaData') {

			var schemaData = GeneralXMLTools.createDocumentFromString(request.responseText);
			if (schemaData.documentElement.tagName == 'parsererror') {
				schemaValid = false;
			} else {
				// read parameter names
				parameterNames = [];
				for (var i = 0, n = schemaData.documentElement.childNodes.length; i < n; i++) {
					parameterNames.push(schemaData.documentElement.childNodes[i].getAttribute("name"));
				}
			}
		} else {
			schemaValid = false; 
		}
		if (!schemaValid) {
			// schema data could not be retrieved for some reason (property may 
			// not yet exist). Show "Value" as default.
			for (var i = 0; i < relation.getArity()-1; i++) {
		 		parameterNames.push("Value");
			}
		}

		var valueInputs = new Array();
		var inputNames = new Array();
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
									 typeCheck +
							 		 SMW_REL_VALID_PROPERTY_VALUE +
									 (parName == "Page" ? SMW_REL_HINT_INSTANCE : "") ,true);

			valueInputs.push(obj);
			obj = tb.createText('rel-value-'+i+'-msg', '', '', true);
			valueInputs.push(obj);
			inputNames.push(['rel-value-'+i,relation.getSplitValues()[i]]);
		}
		tb.append(tb.createInput('rel-name', 
								 gLanguage.getMessage('PROPERTY'), '', '', 
								 SMW_REL_CHECK_PROPERTY_UPDATE_SCHEMA +
		 						 SMW_REL_CHECK_EMPTY +
		                         SMW_REL_VALID_PROPERTY_NAME +
		 						 SMW_REL_HINT_PROPERTY,
		 						 true));
		tb.setInputValue('rel-name', relation.getName());	                         
		 						 
		tb.append(tb.createText('rel-name-msg', '', '' , true));
		if (renameAll !='') {
			tb.append(renameAll);
		}
		tb.append(valueInputs);
		for (var i = 0; i < inputNames.length; i++) {
			tb.setInputValue(inputNames[i][0],inputNames[i][1]);
		}
		
		// In the Advanced Annotation Mode the representation can not be changed
		var repr = relation.getRepresentation(); 
		if (wgAction == 'annotate') {
			if (repr == '') {
				// embrace further values
				var values = relation.getSplitValues();
				repr = values[0];
				if (values.size() > 1) {
					repr += ' (';
					for (var i = 1; i < values.size(); ++i) {
						repr += values[i];
						if (i < values.size()-1) {
							repr += ","
						}
					}
					repr += ')';
				}
			}
		}
		tb.append(tb.createInput('rel-show', gLanguage.getMessage('SHOW'), repr, '', '', true));
		tb.setInputValue('rel-show', repr);	                         

		var links = [['relToolBar.changeItem('+selindex+')',gLanguage.getMessage('CHANGE'), 'rel-confirm', 
		                                                    gLanguage.getMessage('INVALID_VALUES'), 'rel-invalid'],
					 ['relToolBar.deleteItem(' + selindex +')', gLanguage.getMessage('DELETE')],
					 ['relToolBar.cancel()', gLanguage.getMessage('CANCEL')]
					];
		tb.append(tb.createLink('rel-links', links, '', true));
		
		tb.finishCreation();
		if (wgAction == 'annotate') {
			$('rel-show').disable();
			$('rel-value-0').disable();
		}
		gSTBEventActions.initialCheck($("relation-content-box"));

		//Sets Focus on first Element
		setTimeout("$('rel-name').focus();",50);
	}
	tb.append(tb.createText('rel-help-msg', gLanguage.getMessage('CHANGE_PROPERTY'), '' , true));
	if(relation.getName().strip()!=""){
		this.toolbarContainer.showSandglass('rel-help-msg');
		sajax_do_call('smwf_om_RelationSchemaData', [relation.getName()], getSchemaCallback.bind(this));
	}
}

};// End of Class

var relToolBar = new RelationToolBar();
Event.observe(window, 'load', relToolBar.callme.bindAsEventListener(relToolBar));



// SMW_Rule.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
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
var RuleToolBar = Class.create();

var SMW_RULE_VALID_RULE_NAME =
	'smwValidValue="^[^<>\|!&$%&\/=\?]{1,255}$: valid ' +
		'? (color: white, hideMessage, valid:true) ' +
	 	': (color: red, showMessage:RULE_NAME_TOO_LONG, valid:false)" ';

var SMW_RULE_CHECK_EMPTY = 
	'smwCheckEmpty="empty' +
		'? (color:red, showMessage:MUST_NOT_BE_EMPTY, valid:false) ' +
		': (color:white, hideMessage)"';

var SMW_RULE_NO_EMPTY_SELECTION =
	'smwCheckEmpty="empty' +
	'? (color:red, showMessage:SELECTION_MUST_NOT_BE_EMPTY, valid:false) ' +
	': (color:white, hideMessage, valid:true)"';		

var SMW_RULE_ALL_VALID =	
	'smwAllValid="allValid ' +
 		'? (show:rule-confirm, hide:rule-invalid) ' +
 		': (show:rule-invalid, hide:rule-confirm)"';
 		

RuleToolBar.prototype = {

initialize: function() {
	this.genTB = new GenericToolBar();
	this.toolbarContainer = null;
	this.showList = true;
	this.currentAction = "";
	this.typeMap = []; // Maps from the language dependent name of a rule type
					   // to the internal name
	this.currentEditObj = null;
},

showToolbar: function(){
	this.rulescontainer.setHeadline(gLanguage.getMessage('RULE_RULES'));
	if (wgAction == 'edit') {
		// Create a wiki text parser for the edit mode. In annotation mode,
		// the mode's own parser is used.
		this.wtp = new WikiTextParser();
	}
	
	this.fillList(true);

},

callme: function(event){
	if((wgAction == "edit" || wgAction == "annotate")
	    && stb_control.isToolbarAvailable() 
	    && (wgNamespaceNumber == 14 || wgNamespaceNumber == 102)){
		this.rulescontainer = stb_control.createDivContainer(RULESCONTAINER, 0);
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
	if (this.wtp) {
		this.wtp.initialize();
		this.rulescontainer.setContent(this.genTB.createList(this.wtp.getRules(),"rules"));
		this.rulescontainer.contentChanged();
	}
},


cancel: function(){
	
	if (this.currentEditObj != null) {
		this.currentEditObj.cancel();
	}
	
	/*STARTLOG*/
    smwhgLogger.log("","STB-Rules",this.currentAction+"_canceled");
	/*ENDLOG*/
	this.currentAction = "";
	
	this.toolbarContainer.hideSandglass();
	this.toolbarContainer.release();
	this.toolbarContainer = null;
	this.fillList(true);
},

/**
 * Creates a new toolbar for the rules container with the standard menu.
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
	
	this.toolbarContainer = new ContainerToolBar('rules-content',1200,this.rulescontainer);
	var tb = this.toolbarContainer;
	tb.createContainerBody(attributes);
	return tb;
},

createRule: function() {

	this.currentAction = "create rule";

	/*STARTLOG*/
    smwhgLogger.log('',"STB-Rules","create_clicked");
	/*ENDLOG*/
	
	var tb = this.createToolbar(SMW_RULE_ALL_VALID);	
	tb.append(tb.createText('rule-help_msg', gLanguage.getMessage('RULE_CREATE'), '' , true));
	tb.append(tb.createInput('rule-name', gLanguage.getMessage('NAME'), '', '',
	                         SMW_RULE_CHECK_EMPTY +
	                         SMW_RULE_VALID_RULE_NAME,
	                         true));
	tb.setInputValue('rule-name','');
	tb.append(tb.createText('rule-name-msg', gLanguage.getMessage('ENTER_NAME'), '' , true));
	
	tb.append(tb.createDropDown('rule-type', gLanguage.getMessage('RULE_TYPE'), 
	                            this.getRuleTypes(), 
	                            0,0, 
	                            SMW_RULE_NO_EMPTY_SELECTION, true));
		
	var links = [['ruleToolBar.doCreateRule()',gLanguage.getMessage('CREATE'), 'rule-confirm', gLanguage.getMessage('INVALID_VALUES'), 'rule-invalid'],
				 ['ruleToolBar.cancel()', gLanguage.getMessage('CANCEL')]
				];
	
	tb.append(tb.createLink('rule-links', links, '', true));
				
	tb.finishCreation();
	gSTBEventActions.initialCheck($("rules-content-box"));
	
	//Sets Focus on first Element
	setTimeout("$('rule-name').focus();",50);
},

/**
 * Called from the UI if a new rule should be created or an existing rule should
 * be edited.
 * 
 * @param WtpRule rule
 * 		If this parameter is defined, the given rule will be edited. Otherwise
 * 		a new rule will be created.
 */
doCreateRule: function(rule) {
	var rt = $('rule-type');
	rt = rt.options[rt.selectedIndex].text;

	for (var i = 0; i < this.typeMap.length; i += 2) {
		if (this.typeMap[i] == rt) {
			rt = this.typeMap[i+1];
			break;
		}
	}
	
	$('rule-confirm').hide();
	$('rule-type').disable();
	
	if (rt == gLanguage.getMessage('RULE_TYPE_DEFINITION')) {
		// Create/edit a definition rule for categories or properties
		var cr = new CategoryRule($('rule-name').value, rt);
		this.currentEditObj = cr;
		cr.createRule();
	} else if (rt == gLanguage.getMessage('RULE_TYPE_CALCULATION')) {
		// Create/edit a calculation rule for properties
		var cr = new CalculationRule($('rule-name').value, rt);
		this.currentEditObj = cr;
		cr.editRule();
	} else if (rt == gLanguage.getMessage('RULE_TYPE_PROP_CHAINING')) {
		// Create/edit a definition rule for properties
		var pcr = new PropertyChain($('rule-name').value, rt);
		this.currentEditObj = pcr;
		pcr.createChain();
	}
},

editRule: function(selindex) {

	this.showList = false;
	this.currentAction = "edit rule";
	this.wtp.initialize();

	var rules = this.wtp.getRules();
	if (   selindex == null
	    || selindex < 0
	    || selindex >= rules.length) {
		// Invalid index
		return;
	}

	/*STARTLOG*/
    smwhgLogger.log('',"STB-Rules","edit_clicked");
	/*ENDLOG*/

	var rule = rules[selindex];
	var ruleName = rule.name;
	var pos = ruleName.lastIndexOf('#');
	if (pos != -1) {
		ruleName = ruleName.substr(pos+1);
	}
	
	var tb = this.createToolbar(SMW_RULE_ALL_VALID);	
	tb.append(tb.createText('rule-help_msg', gLanguage.getMessage('RULE_EDIT'), '' , true));
	tb.append(tb.createInput('rule-name', gLanguage.getMessage('NAME'), '', '',
	                         SMW_RULE_CHECK_EMPTY +
	                         SMW_RULE_VALID_RULE_NAME,
	                         true));
	tb.setInputValue('rule-name', ruleName);
	tb.append(tb.createText('rule-name-msg', gLanguage.getMessage('ENTER_NAME'), '' , true));
			
	var links = [['ruleToolBar.cancel()', gLanguage.getMessage('CANCEL')]
				];
	
	tb.append(tb.createLink('rule-links', links, '', true));
				
	tb.finishCreation();
	gSTBEventActions.initialCheck($("rules-content-box"));
	
	//Sets Focus on first Element
	setTimeout("$('rule-name').focus();",50);

	for (var i = 0; i < this.typeMap.length; i += 2) {
		if (this.typeMap[i] == rule.type) {
			rule.type = this.typeMap[i+1];
			break;
		}
	}
	if (rule.type == gLanguage.getMessage('RULE_TYPE_DEFINITION')) {
		// Edit a definition rule for categories of properties
		var cr = new CategoryRule(ruleName, rule.type);
		this.currentEditObj = cr;
		cr.editRule(rule);
	} else if (rule.type == gLanguage.getMessage('RULE_TYPE_CALCULATION')) {
		// Edit a calculation rule for properties
		var cr = new CalculationRule(ruleName, rule.type);
		this.currentEditObj = cr;
		cr.editRule(rule);
	} else if (rule.type == gLanguage.getMessage('RULE_TYPE_PROP_CHAINING')) {
		// Edit a property chaining rule
		var pcr = new PropertyChain(ruleName, rule.type);
		this.currentEditObj = pcr;
		pcr.editChain(rule);
	} 	

},

deleteRule: function() {

	this.showList = false;
	this.currentAction = "delete rule";

	/*STARTLOG*/
    smwhgLogger.log('',"STB-Rules","delete_clicked");
	/*ENDLOG*/
	
},

getRuleTypes: function() {
	switch (wgNamespaceNumber) {
		case 14: // Category
			this.typeMap = [gLanguage.getMessage('RULE_TYPE_DEFINITION'), "Definition"];
			return [gLanguage.getMessage('RULE_TYPE_DEFINITION')];
		case 102: //properties
			var hasType = gLanguage.getMessage('PC_HAS_TYPE');
			var page = gLanguage.getMessage('TYPE_PAGE').toLowerCase();
			var type = this.wtp.getRelation(hasType);
			if (type) {
				type = type[0].getValue().toLowerCase();
			}
			if (type == null || type == page) {
				// object property
				this.typeMap = [gLanguage.getMessage('RULE_TYPE_DEFINITION'), "Definition",
				                gLanguage.getMessage('RULE_TYPE_PROP_CHAINING'), 'Property chaining'];
				return [gLanguage.getMessage('RULE_TYPE_DEFINITION'),
				        gLanguage.getMessage('RULE_TYPE_PROP_CHAINING')];
			} else {
				// data type property
				this.typeMap = [gLanguage.getMessage('RULE_TYPE_DEFINITION'), "Definition",
				                gLanguage.getMessage('RULE_TYPE_CALCULATION'), 'Calculation'];
				return [gLanguage.getMessage('RULE_TYPE_DEFINITION'),
				        gLanguage.getMessage('RULE_TYPE_CALCULATION')];
			}
	}
	return [];
}


};// End of Class

var ruleToolBar = new RuleToolBar();
Event.observe(window, 'load', ruleToolBar.callme.bindAsEventListener(ruleToolBar));



// SMW_CategoryRule.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
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
var CategoryRule = Class.create();

 		

CategoryRule.prototype = {


/**
 * Constructor
 * @param string ruleName
 * 		Name of the rule
 * @param string ruleType
 * 		Type of the rule e.g. Calculation, Property Chaining, Deduction, Mapping
 */
initialize: function(ruleName, ruleType) {
	this.ruleName = ruleName;
	this.ruleType = ruleType;
	smwhgCreateDefinitionRule = this;
	this.numParts  = 0; // The number of parts the  rule consists of.
	this.variables = 1; // number of variables
	this.pendingIndicator = null;
	this.annotation = null;
	
},

/**
 * Creates the initial user interface of the simple rules editor.
 */
createRule: function() {
	// hide the wiki text editor
	var bodyContent = $('bodyContent');
	bodyContent.hide();
	var html;
	
	var headText = this.createHeadHTML(1, wgCanonicalNamespace, wgTitle);
	
	var catOrProp = gLanguage.getMessage('SR_MCATPROP');
	catOrProp = catOrProp.replace(/\$1/g, '<a href="javascript:smwhgCreateDefinitionRule.memberOfCategory()">');
	catOrProp = catOrProp.replace(/\$2/g, '</a>');
	catOrProp = catOrProp.replace(/\$3/g, '<a href="javascript:smwhgCreateDefinitionRule.memberOfProperty()">');
	catOrProp = catOrProp.replace(/\$4/g, '</a>');
	catOrProp = 
'				<div id="bodyPart0">' +
					catOrProp +
'				</div>';
				
	html = this.getHTMLRuleFramework(headText, catOrProp);

	new Insertion.After(bodyContent, html);
	
	Event.observe('sr-save-rule-btn', 'click', 
			      smwhgCreateDefinitionRule.saveRule.bindAsEventListener(smwhgCreateDefinitionRule));
	if ($('sr-head-value-selector')) {			      
		Event.observe('sr-head-value-selector', 'change', 
				      smwhgCreateDefinitionRule.propValueChanged.bindAsEventListener());
	}
	$('sr-save-rule-btn').disable();
	
},

/**
 * Creates the HTML for the head of a rule for categories or properties. The 
 * type of the head (category or property) depends on the namespace of the
 * current page.
 * 
 * @param string varIdx
 * 		Index of the variable in the head
 * @param string catOrProp
 * 		The language dependent name for categories or properties
 * @param string title
 * 		Name of the category or property
 */
createHeadHTML: function(varIdx, catOrProp, title, propValue, propIsVariable) {
	if (wgNamespaceNumber == 14) {
		// Head for categories
		var headText = gLanguage.getMessage('SR_CAT_HEAD_TEXT');
		headText = headText.replace(/\$1/g, '<span class="rules-variable">X<sub>'+varIdx+'</sub> </span>');
		headText = headText.replace(/\$2/g, catOrProp);
		headText = headText.replace(/\$3/g, '<span class="rules-category">' + title + '</span>');
		return headText;
	} else if (wgNamespaceNumber == 102) {
		// Head for properties
		var headText = gLanguage.getMessage('SR_PROP_HEAD_TEXT');
		headText = headText.replace(/\$1/g, '<span class="rules-variable">X<sub>'+varIdx+'</sub> </span>');
		headText = headText.replace(/\$2/g, '<span class="rules-category">' + title + '</span>');
		var propHTML =
			'&nbsp;' +
			this.createVariableSelector("sr-head-value-selector", gLanguage.getMessage('SR_SIMPLE_VALUE'),"X2") +
			'&nbsp;' +
			'<input type="text" value="" id="sr-prop-head-value" style="display:none" />' +
			'&nbsp;';		
		headText = headText.replace(/\$3/g, propHTML);
		this.variables = 2;
		return headText;
		
	}	
},

/**
 * Returns the HTML structure of the rule interface consisting of a head, body
 * and preview part.
 * 
 * @param string headText
 * 		HTML-content of the head part
 * @param string bodyText
 * 		HTML-content of the body part
 */
getHTMLRuleFramework: function(headText, bodyText) {	
	var derive = gLanguage.getMessage('SR_DERIVE_BY');
	derive = derive.replace(/\$1/g, wgCanonicalNamespace);
	derive = derive.replace(/\$2/g, '<span class="rules-category">'+wgTitle+'</span>');
	html = 
'<div id="createRuleContent" class="rules-complete-content">' +
	derive +
'	<div id="headBodyDiv" style="padding-top:5px">' +
'		<div id="headDiv" class="rules-frame" style="border-bottom:0px">' +
'			<div id="headTitle" class="rules-title">' +
				gLanguage.getMessage('SR_HEAD') +
'			</div>' +
'			<div id="headContent" class="rules-content">' +
				headText +
'			</div>' +
'		</div>' +
'		<div id="bodyDiv" class="rules-frame">' +
'			<div id="bodyTitle" class="rules-title">' +
				gLanguage.getMessage('SR_BODY') +
'			</div>' +
'			<div id="ruleBodyContent" class="rules-content">' +
				bodyText +
'			</div>' +
'		</div>' +
'		<div style="height:20px"></div>' +
'		<div id="implicationsDiv" class="rules-frame">' +
'			<div id="implicationsTitle" class="rules-title" style="width:auto;">' +
				gLanguage.getMessage('SR_RULE_IMPLIES') +
'			</div>' +
'			<div id="implicationsContent" class="rules-content">' +
'			</div>' +
'		</div>' +
'	</div>' +
'	<div style="height:20px"></div>' +
'   <input type="submit" accesskey="s" value="' +
		gLanguage.getMessage('SR_SAVE_RULE') +
		'" name="sr-save-rule-btn" id="sr-save-rule-btn"/>' +
'</div>';

	return html;
},

/**
 * Edits the rule with the given rule text.
 * 
 * @param WtpRule rule
 * 		The annotation object of the rule
 */
editRule: function(ruleAnnotation) {
	
	function ajaxResponseParseRule(request) {
		this.hidePendingIndicator();			
		if (request.status == 200) {
			// success
			var xml = request.responseText;
			if (xml == 'false') {
				//TODO
				return;
			}
			// create the user interface
			this.createUIForRule(xml);
		} else {
		}
	};

	this.showPendingIndicator($('rule-name'));
	this.annotation = ruleAnnotation;
	var ruleText = ruleAnnotation.getRuleText();
	sajax_do_call('smwf_sr_ParseRule', 
	          [this.ruleName, ruleText], 
	          ajaxResponseParseRule.bind(this));
	
},


/**
 * Cancels editing or creating the rule. Closes the rule edit part of the UI and
 * reopens the wiki text edit part.
 *  
 */
cancel: function() {
	
	$('bodyContent').show();
	if ($('createRuleContent')) {
		$('createRuleContent').remove();
	}
		
},

/**
 * Creates the user interface for the rule that is given in the XML format.
 * 
 * @param string ruleXML
 * 		Description of the rule in XML
 */
createUIForRule: function(ruleXML) {
	// hide the wiki text editor
	var bodyContent = $('bodyContent');
	bodyContent.hide();
	
	var rule = GeneralXMLTools.createDocumentFromString(ruleXML);
	
	var head = rule.getElementsByTagName("head")[0].childNodes;
	var body = rule.getElementsByTagName("body")[0].childNodes;
	
	this.variables = 1;
	
	var headHTML = '';
	for (var i = 0, n = head.length; i < n; i++) {
		var headLit = head[i]; 
		if (headLit.nodeType == 1) {
			// skip text nodes
			headHTML += this.getHTMLForLiteral(headLit, true, 888888);
		}
	}
	
	var bodyHTML = '';
	this.numParts = 0;
	for (var i = 0, n = body.length; i < n; i++) {
		var bodyLit = body[i]; 
		if (bodyLit.nodeType == 1) {
			// skip text nodes
			bodyHTML += this.getHTMLForLiteral(bodyLit, false, this.numParts);
	    	bodyHTML +=
				'<div id="AND' + this.numParts + '">' +
					'<b>' +
					gLanguage.getMessage('SR_AND') +
					'</b>' +
				'</div>';
		    this.numParts++;
		}
	}
	var catOrProp = gLanguage.getMessage('SR_MCATPROP');
	catOrProp = catOrProp.replace(/\$1/g, '<a href="javascript:smwhgCreateDefinitionRule.memberOfCategory()">');
	catOrProp = catOrProp.replace(/\$2/g, '</a>');
	catOrProp = catOrProp.replace(/\$3/g, '<a href="javascript:smwhgCreateDefinitionRule.memberOfProperty()">');
	catOrProp = catOrProp.replace(/\$4/g, '</a>');
	bodyHTML += 
		'<div id="bodyPart'+this.numParts+'">' +
			catOrProp +
		'</div>';
	
	html = this.getHTMLRuleFramework(headHTML, bodyHTML);

	new Insertion.After(bodyContent, html);
	
	Event.observe('sr-save-rule-btn', 'click', 
			      smwhgCreateDefinitionRule.saveRule.bindAsEventListener(smwhgCreateDefinitionRule));
	if ($('sr-head-value-selector')) {			      
		Event.observe('sr-head-value-selector', 'change', 
				      smwhgCreateDefinitionRule.propValueChanged.bindAsEventListener());
	}
		
},

/**
 * Assembles the HTML for a literal of a rule. The literal is passed as a DOM
 * node.
 * 
 * @param DOMnode literal
 * 		Literal of a rule (a category or a property)
 * @param bool isHead
 * 		If <true>, HTML code the head of the rule generated.
 *
 * @return string
 * 		HTML code that represents the literal
 * 
 */
getHTMLForLiteral: function(literal, isHead, partID) {
	var html = '';
	switch (literal.tagName) {
		case 'category':
			var catName = literal.getElementsByTagName('name')[0].firstChild.nodeValue;
			var subject = literal.getElementsByTagName('subject')[0].firstChild.nodeValue;
			var varIdx = subject.match(/X(\d+)/);
			if (varIdx[1]*1 > this.variables) {
				this.variables = varIdx[1]*1;
			}

			if (isHead) {
				html = this.createHeadHTML(varIdx[1], wgCanonicalNamespace, catName);
			} else {
				html =	
	'<div id="bodyPart' + partID + '">' +
	gLanguage.getMessage('SR_ALL_ARTICLES') +
		'&nbsp;' +
		'<span id="var_' + partID + '" ' +
			'varname="' + subject + '" ' +
			'class="rules-variable">' +
		'X<sub>' + varIdx[1] + '</sub>' + 
		'</span>&nbsp;' +
		gLanguage.getMessage('SR_BELONG_TO_CAT') +
		'&nbsp;' +
		'<span id="cat_' + partID + '" ' +
			'catname="' + escape(catName) + '" ' +
			'class="rules-category">' +
		catName +
		'</span>&nbsp;' +
		'<span id="buttons_' + partID + '">' +
			'<a href="javascript:smwhgCreateDefinitionRule.editCategoryCondition(' + partID + ')">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/edit.gif"/>' +
			'</a>' +
			'<a href="javascript:smwhgCreateDefinitionRule.removeCondition(' + partID + ')">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/delete.png"/>' +
			'</a>' +
		'</span>' + 		
	'</div>';
			}
			break;
		case 'property':
			var propName = literal.getElementsByTagName('name')[0].firstChild.nodeValue;
			var subject = literal.getElementsByTagName('subject')[0].firstChild.nodeValue;
			var subjIdx = subject.match(/X(\d+)/);
			if (subjIdx[1]*1 > this.variables) {
				this.variables = subjIdx[1]*1;
			}
			
			var valueHTML;
			var variable = literal.getElementsByTagName('variable');
			if (variable.length) {
				variable = variable[0].firstChild.nodeValue;
				var varIdx = variable.match(/X(\d+)/);
				if (varIdx[1]*1 > this.variables) {
					this.variables = varIdx[1]*1;
				}
				valueHTML = '<span class="rules-variable" ' + 
								'id="value_' + partID + '" ' +
								'propvalue="' + escape(variable) + '"' +
								'proptype="variable"' +
								'>' +
								'X<sub>' + varIdx[1] + '</sub>' + 
							'</span>';
			}
			var value = literal.getElementsByTagName('value');
			if (value.length) {
				value = value[0].firstChild.nodeValue;
				var valueInfo = 
				valueHTML = '<span class="rules-category"' + 
								'id="value_' + partID + '" ' +
								'propvalue="' + escape(value) + '"' +
								'proptype="value"' +							
								'>' +
								value + 
							'</span>';
			}
			
			var html =	
'<div id="bodyPart' + partID + '">' +
	gLanguage.getMessage('SR_ALL_ARTICLES') +
		'&nbsp;' +
		'<span id="var_' + partID + '" ' +
			'varname="' + subject + '" ' +
			'class="rules-variable">' +
			'X<sub>' + subjIdx[1] + '</sub>' + 
		'</span>&nbsp;' +
		gLanguage.getMessage('SR_HAVE_PROP') +
		'&nbsp;' +
		'<span id="prop_' + partID + '" ' +
			'propname="' + propName + '" ' +
			'class="rules-category">' +
		propName +
		'</span>&nbsp;' +
		gLanguage.getMessage('SR_WITH_VALUE') +
		'&nbsp;' +
		valueHTML + 
		'&nbsp;' +
		'<span id="buttons_' + partID + '">' +
			'<a href="javascript:smwhgCreateDefinitionRule.editPropertyCondition(' + partID + ')">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/edit.gif"/>' +
			'</a>' +
			'<a href="javascript:smwhgCreateDefinitionRule.removeCondition(' + partID + ')">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/delete.png"/>' +
			'</a>' +
		'</span>' +
'</div>';
			
			break;
	}
	return html;
},

/**
 * Creates the section of the user interface where a category condition can be
 * defined.
 */
memberOfCategory: function() {
	
	$('sr-save-rule-btn').disable();
	
	var id = 'bodyPart'+this.numParts;
	var currPart = $('bodyPart'+this.numParts);
	
	var html;
	html = 
'<div id="bodyPart' + this.numParts + '">' +
	gLanguage.getMessage('SR_ALL_ARTICLES') +
		'&nbsp;' +
		this.createVariableSelector('sr-variable-selector',null,"X1") +
		'&nbsp;' +
		gLanguage.getMessage('SR_BELONG_TO_CAT') +
		'&nbsp;' +
		'<input type="text" value="" id="sr-cat-name" class="wickEnabled" />' +
		'&nbsp;' +
		'<a href="javascript:smwhgCreateDefinitionRule.showCategoryCondition(' + this.numParts + ',false)">' +
			'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/add.png"/>' +
		'</a>' +
'</div>';

	currPart.replace(html);
},

/**
 * Replaces the display of a category condition by the editable user interface.
 * 
 * @param int partID
 * 		Index of the part of the rule.
 */
editCategoryCondition: function(partID) {
	
	$('ruleBodyContent').hide();
	$('sr-save-rule-btn').disable();
	
	this.showButtons(false);
	
	var elem = $('var_'+partID);
	if (elem) {
		var val = elem.readAttribute('varname');
		elem.replace(this.createVariableSelector('sr-variable-selector', null, val));
	}
	elem = $('cat_'+partID);
	if (elem) {
		var val = unescape(elem.readAttribute('catname'));
		elem.replace('<input type="text" value="" id="sr-cat-name" class="wickEnabled" />');
		$("sr-cat-name").value = val;
	}
	elem = $('buttons_'+partID);
	if (elem) {
		new Insertion.After(elem,
			'<a href="javascript:smwhgCreateDefinitionRule.showCategoryCondition('+ partID +',true)">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/add.png"/>' +
			'</a>');
		elem.remove();
	}

	$('ruleBodyContent').show();
},

/**
 * After a category condition has been defined, it is displayed in a simplified
 * format without input fields etc. The section for defining the next condition
 * is added if <update> is 'false'. 
 * 
 * @param int partID 
 * 		ID of the part where the category condition is added
 * @param bool update
 * 		If <true>, the current part is updated. The next condition will not be 
 * 		appended.
 */
showCategoryCondition: function(partID, update) {
	var category = $('sr-cat-name').value;
	if (category.length == 0) {
		return;
	}
	
	$('ruleBodyContent').hide();
	$('sr-save-rule-btn').enable();
	
	var variable = $('sr-variable-selector');
	variable = variable.options[variable.selectedIndex].text;
	
	var varIdx = variable.substr(1) * 1;
	
	if (varIdx > this.variables) {
		this.variables = varIdx;
	}
	
	var id = 'bodyPart'+partID;
	var currPart = $('bodyPart'+partID);

	var catOrProp = gLanguage.getMessage('SR_MCATPROP');
	catOrProp = catOrProp.replace(/\$1/g, '<a href="javascript:smwhgCreateDefinitionRule.memberOfCategory()">');
	catOrProp = catOrProp.replace(/\$2/g, '</a>');
	catOrProp = catOrProp.replace(/\$3/g, '<a href="javascript:smwhgCreateDefinitionRule.memberOfProperty()">');
	catOrProp = catOrProp.replace(/\$4/g, '</a>');

	var html;

	html =	
'<div id="bodyPart' + partID + '">' +
	gLanguage.getMessage('SR_ALL_ARTICLES') +
		'&nbsp;' +
		'<span id="var_' + partID + '" ' +
			'varname="' + variable + '" ' +
			'class="rules-variable">' +
		'X<sub>' + varIdx + '</sub>' + 
		'</span>&nbsp;' +
		gLanguage.getMessage('SR_BELONG_TO_CAT') +
		'&nbsp;' +
		'<span id="cat_' + partID + '" ' +
			'catname="' + escape(category) + '" ' +
			'class="rules-category">' +
		category +
		'</span>&nbsp;' +
		'<span id="buttons_' + partID + '">' +
			'<a href="javascript:smwhgCreateDefinitionRule.editCategoryCondition(' + partID + ')">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/edit.gif"/>' +
			'</a>' +
			'<a href="javascript:smwhgCreateDefinitionRule.removeCondition(' + partID + ')">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/delete.png"/>' +
			'</a>' +
		'</span>' + 		
'</div>';

	if (!update) {
		html +=
'<div id="AND' + partID + '">' +
	'<b>' +
	gLanguage.getMessage('SR_AND') +
	'</b>' +
'</div>';

		++this.numParts;
		html +=
'<div id="bodyPart' + this.numParts + '">' +
	catOrProp +
'</div>';
	}
	
	currPart.replace(html);
	
	this.showButtons(true);
	$('ruleBodyContent').show();
	
},

/**
 * Creates the section of the user interface where a property condition can be
 * defined.
 */
memberOfProperty: function() {
	
	$('sr-save-rule-btn').disable();
	
	var id = 'bodyPart'+this.numParts;
	var currPart = $('bodyPart'+this.numParts);
	
	var html;
	html = 
'<div id="bodyPart' + this.numParts + '">' +
	gLanguage.getMessage('SR_ALL_ARTICLES') +
		'&nbsp;' +
		this.createVariableSelector("sr-variable-selector", null, "X1") +
		'&nbsp;' +
		gLanguage.getMessage('SR_HAVE_PROP') +
		'&nbsp;' +
		'<input type="text" value="" id="sr-prop-name" class="wickEnabled" />' +
		'&nbsp;' +
		gLanguage.getMessage('SR_WITH_VALUE') +
		'&nbsp;' +
		this.createVariableSelector("sr-value-selector", gLanguage.getMessage('SR_SIMPLE_VALUE'),"X1") +
		'&nbsp;' +
		'<input type="text" value="" id="sr-prop-value" style="display:none" />' +
		'&nbsp;' +
		
		'<a href="javascript:smwhgCreateDefinitionRule.showPropertyCondition('+this.numParts+',false)">' +
			'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/add.png"/>' +
		'</a>' +
'</div>';

	currPart.replace(html);
	
	Event.observe('sr-value-selector', 'change', 
			      smwhgCreateDefinitionRule.propValueChanged.bindAsEventListener());
	
},

/**
 * Replaces the display of a property condition by the editable user interface.
 * 
 * @param int partID
 * 		Index of the part of the rule.
 */
editPropertyCondition: function(partID) {
	
	$('ruleBodyContent').hide();
	$('sr-save-rule-btn').disable();
	this.showButtons(false);
	
	var elem = $('var_'+partID);
	if (elem) {
		var val = elem.readAttribute('varname');
		elem.replace(this.createVariableSelector('sr-variable-selector', null, val));
	}
	elem = $('prop_'+partID);
	if (elem) {
		var val = unescape(elem.readAttribute('propname'));
		elem.replace('<input type="text" value="" id="sr-prop-name" class="wickEnabled" />');
		$("sr-prop-name").value = val;
	}
	elem = $('value_'+partID);
	if (elem) {
		var val = unescape(elem.readAttribute('propvalue'));
		var type = unescape(elem.readAttribute('proptype'));
		var select = (type == 'variable') ? val : gLanguage.getMessage('SR_SIMPLE_VALUE');
		var html = this.createVariableSelector("sr-value-selector", 
		                                       gLanguage.getMessage('SR_SIMPLE_VALUE'),select);
		html += '<input type="text" value="" id="sr-prop-value" style="display:none"/>';
		elem.replace(html);
		if (type == 'value') {
			$("sr-prop-value").value = val;
			$("sr-prop-value").show();
		}
		Event.observe('sr-value-selector', 'change', 
				      smwhgCreateDefinitionRule.propValueChanged.bindAsEventListener());
		
	}
	
	elem = $('buttons_'+partID);
	if (elem) {
		new Insertion.After(elem,
			'<a href="javascript:smwhgCreateDefinitionRule.showPropertyCondition('+ partID +',true)">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/add.png"/>' +
			'</a>');
		elem.remove();
	}
	$('ruleBodyContent').show();
	
},

/**
 * This event function is called, when the value of the value selector for
 * property values has been changed.
 * If the value of a property is a variable, the input field for simple values
 * is hidden, otherwise it is shown. 
 * 
 * @param s selectorID
 * 		DOM-ID of the selector
 */
 propValueChanged: function(event) {
 	var val = event.target.options[event.target.selectedIndex].text;

 	var input = (event.target.id == 'sr-value-selector') 
	 				? $('sr-prop-value')
	 				: $('sr-prop-head-value');
 	if (val == gLanguage.getMessage('SR_SIMPLE_VALUE')) {
 		input.show();
 	} else {
 		input.hide();
 	}
 },

/**
 * After a property condition has been defined, it is displayed in a simplified
 * format without input fields etc. The section for defining the next condition
 * is added if <update> is 'false'. 
 * 
 * @param int partID 
 * 		ID of the part where the category condition is added
 * @param bool update
 * 		If <true>, the current part is updated. The next condition will not be 
 * 		appended.
 */
 showPropertyCondition: function(partID, update) {
	var variable = $('sr-variable-selector')
	variable = variable.options[variable.selectedIndex].text;
	
	var property = $('sr-prop-name').value;
	var vsv = $('sr-value-selector');
	vsv = vsv.options[vsv.selectedIndex].text;
	
	var valueIsVariable = vsv != gLanguage.getMessage('SR_SIMPLE_VALUE');
	var value = (valueIsVariable) ? vsv 
	                              : $('sr-prop-value').value;

	if (property.length == 0) {
		// The name of the property must not be empty
		return;
	}
	if (!valueIsVariable && value.length == 0) {
		// The value of the property must not be empty if it is not a variable
		return;
	}
	$('ruleBodyContent').hide();
	$('sr-save-rule-btn').enable();

	var varIdx = variable.substr(1) * 1;
	
	if (varIdx > this.variables) {
		this.variables = varIdx;
	}
	
	var id = 'bodyPart'+partID;
	var currPart = $('bodyPart'+partID);

	var catOrProp = gLanguage.getMessage('SR_MCATPROP');
	catOrProp = catOrProp.replace(/\$1/g, '<a href="javascript:smwhgCreateDefinitionRule.memberOfCategory()">');
	catOrProp = catOrProp.replace(/\$2/g, '</a>');
	catOrProp = catOrProp.replace(/\$3/g, '<a href="javascript:smwhgCreateDefinitionRule.memberOfProperty()">');
	catOrProp = catOrProp.replace(/\$4/g, '</a>');

	var valueHTML;
	var escapedValue = escape(value);
	
	var valueInfo = 'id="value_' + partID + '" ' +
			'propvalue="' + escapedValue + '"' +
			'proptype="' + (valueIsVariable ? 'variable"' : 'value"');
	
	if (valueIsVariable) {
		var vi = value.substr(1) * 1;
		valueHTML = '<span class="rules-variable" ' + valueInfo + '>' +
						'X<sub>' + vi + '</sub>' + 
					'</span>';
		if (vi > this.variables) {
			this.variables = vi;
		}
					
	} else {
		valueHTML = '<span class="rules-category"' + valueInfo + '>' +
						value + 
					'</span>';
	}
					
	var html;

	html =	
'<div id="bodyPart' + partID + '">' +
	gLanguage.getMessage('SR_ALL_ARTICLES') +
		'&nbsp;' +
		'<span id="var_' + partID + '" ' +
			'varname="' + variable + '" ' +
			'class="rules-variable">' +
			'X<sub>' + varIdx + '</sub>' + 
		'</span>&nbsp;' +
		gLanguage.getMessage('SR_HAVE_PROP') +
		'&nbsp;' +
		'<span id="prop_' + partID + '" ' +
			'propname="' + property + '" ' +
			'class="rules-category">' +
		property +
		'</span>&nbsp;' +
		gLanguage.getMessage('SR_WITH_VALUE') +
		'&nbsp;' +
		valueHTML + 
		'&nbsp;' +
		'<span id="buttons_' + partID + '">' +
			'<a href="javascript:smwhgCreateDefinitionRule.editPropertyCondition(' + partID + ')">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/edit.gif"/>' +
			'</a>' +
			'<a href="javascript:smwhgCreateDefinitionRule.removeCondition(' + partID + ')">' +
				'<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/delete.png"/>' +
			'</a>' +
		'</span>' +
'</div>';

	if (!update) {
		html +=
'<div id="AND' + partID + '">' +
	'<b>' +
	gLanguage.getMessage('SR_AND') +
	'</b>' +
'</div>';

		++this.numParts;
	html +=
'<div id="bodyPart' + this.numParts + '">' +
	catOrProp +
'</div>';
	}			
	
	currPart.replace(html);
	
	this.showButtons(true);
	$('ruleBodyContent').show();
	
},

/**
 * Retrieves the conditions from the user interface, creates a rule and saves it.
 */
saveRule: function(event) {

	function ajaxResponseAddRule(request) {
		this.hidePendingIndicator();			
		if (request.status == 200) {
			// success

			if ($('rule-name')) {
				this.ruleName = $('rule-name').value;
			}			
			
			var ruleText = 
				"\n\n" +
				'<rule hostlanguage="f-logic" ' +
				      'name="' + this.ruleName + '" ' +
				      'type="' + this.ruleType + '">' + "\n" +
				request.responseText +
				"\n</rule>\n";
			 	
			// hide the rule editor GUI
			$('createRuleContent').remove();
			
			// show normal wiki text editor GUI
			$('bodyContent').show();
			
			if (this.annotation) {
				// update an existing annotation
				this.annotation.replaceAnnotation(ruleText); 
			} else {
				// append the text to the edit field
				var ei = new SMWEditInterface();
				ei.setValue(ei.getValue() + ruleText);
			}
			ruleToolBar.fillList(true);
						 	
		} else {
		}
	};

	var xml = this.serializeRule();

	this.showPendingIndicator($('sr-save-rule-btn'));
	
	sajax_do_call('smwf_sr_AddRule', 
	          [this.ruleName, xml], 
	          ajaxResponseAddRule.bind(this));
	
},

/**
 * Removes the condition with the given ID.
 * 
 * @param int partID
 * 		Index of the condition to be removed
 */
 removeCondition: function(partID) {
 	if ($('bodyPart'+partID)) {
 		$('bodyPart'+partID).remove();
 	}
 	if ($('AND'+partID)) {
 		$('AND'+partID).remove();
 	}
 },

/**
 * Creates the HTML-code for the variable selector.
 * 
 * @param string id
 * 		ID of the selector
 * @param string option
 * 		One additional option that is appended
 * @param string select
 * 		If this item occurrs in the list of options, it will be selected.
 */
createVariableSelector: function(id, option, select) {
	var html =
		'<select id ="' + id + '">';
	
	for (var i = 1; i <= this.variables + 1; ++i) {
		var variable = 'X'+i;
		if (select == variable) {
			variable = 'selected="selected">' + variable;
		} else {
			variable = '>' + variable;
		}
		html += '<option ' + variable + '</option>';
	}
	if (option != undefined && option != null) {
		html += (select == option) 
					? '<option selected="selected">' + option + '</option>'
					: '<option>' + option + '</option>';
	}	
	html +=		
		'</select>';
		
	return html;
	
},

/**
 * Shows or hides the edit and delete buttons to the right of a condition.
 * 
 * @param bool show
 * 		If <true>, buttons are shown, otherwise they are hidden.
 */
showButtons: function(show) {
	for (var i = 0; i < this.numParts; ++i) {
		var b = $('buttons_'+i);
		if (b) {
			if (show) {
				b.show();
			} else {
				b.hide();
			}
		}
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
},

/**
 * Creates an XML representation of the current rule.
 */
serializeRule: function() {
	
	var xml;
	
	var title = wgTitle.replace(/ /g,'_');
	
	xml = '<?xml version="1.0" encoding="UTF-8"?>' +
		  '<SimpleRule>';
	
	// serialize head
	xml += '<head>';
	if (wgNamespaceNumber == 14) {
		// create a category rule
		xml += '<category>' +
				'<name>' +
					title +
				'</name>' +
				'<subject>X1</subject>' +
				'</category>';
	} else if (wgNamespaceNumber == 102) {
		// create a property rule
		xml += '<property>' +
				'<subject>X1</subject>' +
				'<name>' +
					title +
				'</name>';
		
		var isVariable = false;
		var value = '';
		var val = $('value_888888');
		if (val) {
			isVariable = (val.readAttribute('proptype') == 'variable');
			value = val.readAttribute('propvalue');
		} else {
			var val = $('sr-head-value-selector').value;
			isVariable = (val != gLanguage.getMessage('SR_SIMPLE_VALUE'));
			value = isVariable 
						? val
						: $('sr-prop-head-value').value;
		}
		
		if (isVariable) {
			xml += '<variable>'+value+'</variable>';
	 	} else {
		 	xml += '<value>'+value+'</value>';
	 	}
		xml += '</property>';
		
	}
	
	xml += '</head>';
		  
	// serialize body
	xml += '<body>';
	for (var i = 0; i < this.numParts; ++i) {
		var subject = $('var_'+i);
		if (!subject) {
			// a gap in the rule parts
			continue;
		}
		subject = '<subject>'+subject.readAttribute('varname')+'</subject>';
		var cat = $('cat_'+i);
		if (cat) {
			var catName = unescape(cat.readAttribute('catname'));
			catName = catName.replace(/ /g, '_');
			// variable belongs to a category
			xml += '<category>' +
					'<name>' +
						catName +
					'</name>' +
					subject +
					'</category>';
		}
		var prop = $('prop_'+i);
		if (prop) {
			// variable belongs to a property
			xml += '<property>' +
					subject +
					'<name>' +
						unescape(prop.readAttribute('propname')).replace(/ /g, '_') +
					'</name>';
			var value = $('value_'+i);
			if (value.readAttribute('proptype') == 'variable') {
				xml += '<variable>'+value.readAttribute('propvalue')+'</variable>';
			} else {
				xml += '<value>'+value.readAttribute('propvalue')+'</value>';
			}
			xml += '</property>';
		}
	}
		  
	xml += '</body></SimpleRule>';
	
	return xml;
	
},

/**
 * Checks if the rule that is currently presented in the UI is valid i.e. if
 * all variables are transitively connected to X1 via a property.
 * 
 */
checkRule: function() {
	
	var variables = []; // Array of all variables in the rule
	var connections = []; // Array of all connected variables e.g. [[X1, X2], [X2, X3]]
	for (var i = 0; i < this.numParts; ++i) {
		var subject = $('var_'+i);
		if (!subject) {
			// a gap in the rule parts
			continue;
		}
		subject = subject.readAttribute('varname');
		if (variables.indexOf(subject) == -1) {
			variables.push(subject);
		}
		var prop = $('prop_'+i);
		if (prop) {
			// variable belongs to a property
			var value = $('value_'+i);
			if (value.readAttribute('proptype') == 'variable') {
				var object = value.readAttribute('propvalue');
				connections.push([subject, object]);
			}
		}
	}
	
	// build transitive connections
	while (true) {
		var connAdded = false;
		for (var i = 0, n = connections.length; i < n; ++i) {
			var currSubj = connections[i][0];
			
			//TODOs
		}
	}
}



};// End of Class

var smwhgCreateDefinitionRule = null;


// SMW_CalculationRule.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
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
var CalculationRule = Class.create();

 		

CalculationRule.prototype = {


/**
 * Constructor
 * @param string ruleName
 * 		Name of the rule
 * @param string ruleType
 * 		Type of the rule e.g. Calculation, Property Chaining, Deduction, Mapping
 */
initialize: function(ruleName, ruleType) {
	this.ruleName = ruleName;
	this.ruleType = ruleType;
	smwhgCreateCalculationRule = this;
	this.pendingIndicator = null;
	this.variableSpec = '';
	this.annotation = null;
	this.formulaValid = false;
	this.checkRuleTimer = null;	
},

/**
 * @public
 * 
 * Creates the initial user interface of the calculation rules editor.
 * 
 * @param string ruleText
 * 		If this parameter is defined, an existing rule will be edited otherwise
 * 		a new rule will be created.
 */
editRule: function(ruleAnnotation) {
	
	if (ruleAnnotation == undefined) {
		this.createUI();
	} else {
		// parse the rule text
		this.annotation = ruleAnnotation;
		var ruleText = ruleAnnotation.getAnnotation();
		var rule = FormulaRuleParser.parseRule(ruleText);
		if (rule == false) {
			// The rule is erroneous => create a UI without predefined rule
			rule = undefined;
		}
		this.createUI(rule);
		$('variablesDiv').show();
		
		function ajaxResponseCheckFormula(request) {
			if (request.status == 200) {
				// success
				var result = request.responseText;
				var variables = result.split(',');
				if (variables[0] != 'error' 
				    && !(variables.size() == 2 && variables[1] == '')) {
					// the formula is valid
					this.formulaValid = true;
				} else {
					this.editFormula();
				}
			}
		}
		sajax_do_call('smwf_sr_ParseFormula', 
		          [$('sr-formula').value], 
		          ajaxResponseCheckFormula.bind(this));
		          
		
	}
	
},

/**
 * Cancels editing or creating the rule. Closes the rule edit part of the UI and
 * reopens the wiki text edit part.
 *  
 */
cancel: function() {
	
	$('bodyContent').show();
	if ($('createRuleContent')) {
		$('createRuleContent').remove();
	}
	if (this.checkRuleTimer) {
		this.checkRuleTimer.stop();
	} 
		
},

/**
 * @private
 * 
 * Creates the initial user interface of the calculation rules editor.
 * 
 * @param FormulaRule parsedRule
 * 		If this parameter is defined, it contains a representation of the parsed
 * 		rule that defines the content of the GUI.
 */
createUI: function(parsedRule) {
	// hide the wiki text editor
	var bodyContent = $('bodyContent');
	bodyContent.hide();
	var html;
					
	html = this.getHTMLRuleFramework(parsedRule);

	new Insertion.After(bodyContent, html);
	
	var opHelp = this.operatorHelpHTML();
	if (!$('sr-calc-op-help')) {
		new Insertion.After('contenttabposdiv', opHelp);
	}	
	
	if (parsedRule == undefined) {
		$('sr-save-rule-btn').disable();
	}
	Event.observe('sr-save-rule-btn', 'click', 
			      smwhgCreateCalculationRule.saveRule.bindAsEventListener(smwhgCreateCalculationRule));
			      
	this.checkRuleTimer = new PeriodicalExecuter(function(pe) {
			var saveRuleBtn = $('sr-save-rule-btn');
			if (saveRuleBtn) {
				var disabled = saveRuleBtn.disabled;
				if (smwhgCreateCalculationRule.checkRule()) {
					if (disabled) {
						saveRuleBtn.disabled = false;
					}
				} else {
					if (!disabled) {
						saveRuleBtn.disabled = true;
					}
				}
			}
		}, 0.5);			      
},


/**
 * Returns the HTML structure of the rule interface consisting of the formula 
 * part, the variable definition area and the preview area.
 * 
 * @param FormulaRule parsedRule
 * 		If defined, it contains the parsed representation of the rule for the
 * 		initial GUI.
 */
getHTMLRuleFramework: function(parsedRule) {	
	var derive = gLanguage.getMessage('SR_DERIVE_BY');
	derive = derive.replace(/\$1/g, wgCanonicalNamespace);
	derive = derive.replace(/\$2/g, '<span class="rules-category">'+wgTitle+'</span>');
	
	var defFormulaHTML   = (parsedRule == undefined)
								? this.defineFormulaHTML(parsedRule)
								: this.confirmedFormulaHTML(parsedRule);
	var defVariablesHTML = this.defineVariablesHTML(parsedRule);
	var previewHTML      = this.previewHTML();
	
	html = 
'<div id="createRuleContent" class="rules-complete-content">' +
'	<div id="headBodyDiv" style="padding-top:5px">' +
		defFormulaHTML +
		defVariablesHTML +
		previewHTML +
'	   <input type="submit" accesskey="s" value="' +
			gLanguage.getMessage('SR_SAVE_RULE') +
'			" name="sr-save-rule-btn" id="sr-save-rule-btn"/>' +
'	</div>' +
'</div>';
	return html;
},

/**
 * @private
 * 
 * This function returns the HTML of the upper part of the GUI where the formula
 * is entered. This part allows editing the formula. 
 * 
 * @param FormulaRule parsedRule
 * 		If defined, it contains the parsed representation of the rule for the
 * 		initial GUI.
 */
defineFormulaHTML: function(parsedRule) {
	
	var formulaResult = wgTitle;
	var initialFormula = (parsedRule == undefined) ? '' : parsedRule.getFormula();
	var enterFormula = gLanguage.getMessage('SR_ENTER_FORMULA');
	enterFormula = enterFormula.replace(/\$1/g, formulaResult);
	 
	var html = 
'<div id="formulaDiv" class="rules-frame">' +
'	<div id="formulaIntro" class="rules-content">' +
		enterFormula +
'	</div>' +
'	<div id="formulaInput" class="rules-content">' +
		formulaResult + '&nbsp;=&nbsp;' + 
'		<input type="text" style="width:60%" ' +
'				value="'+initialFormula+'" id="sr-formula" />' +
'		&nbsp;' +
'		<img id="sr-op-help-img"' +
'			 src="' + wgScriptPath + '/extensions/SMWHalo/skins/help.gif"' +
'			 onmouseover="smwhgCreateCalculationRule.showOpHelp(true)"' +
'			 onmouseout="smwhgCreateCalculationRule.showOpHelp(false)"' +
'		/>' +
'	</div>' +
'	<div id="formulaErrorMsgDiv" class="rules-content rules-err-msg" style="display:none">' +
'		<span id="formulaErrorMsg" />' +
'	</div>' +
'	<div id="formulaSubmit" class="rules-content">' +
'		<a href="javascript:smwhgCreateCalculationRule.submitFormula()">' +
			gLanguage.getMessage('SR_SUBMIT') +
'		</a>' +
'	</div>' +
'</div>';

	return html;
},

/**
 * @private
 * 
 * This function returns the HTML of the upper part of the GUI where the formula
 * has already been confirmed. This part no longer allows editing the formula. 
 * 
 * @param FormulaRule parsedRule
 * 		Contains the parsed representation of the rule with the definition of
 * 		the formula.
 */
confirmedFormulaHTML: function(parsedRule) {
	
	var formulaResult = wgTitle;
	var initialFormula = parsedRule.getFormula();
	var syntaxChecked = gLanguage.getMessage('SR_SYNTAX_CHECKED');
	var edit = gLanguage.getMessage('SR_EDIT_FORMULA');
	 
	var html = 
'<div id="confirmedFormulaDiv" class="rules-frame" style="border-bottom:0px">' +
'	<div id="confFormulaInput" class="rules-content">' +
		formulaResult + '&nbsp;=&nbsp;' + 
'		<input type="text" style="width:50%"' +
'			value="'+initialFormula+'" id="sr-formula" readonly="readonly" />' +
'		&nbsp;' +
'		<img src="' + wgScriptPath + '/extensions/SMWHalo/skins/checkmark.png"/>' +
		syntaxChecked +
'		&nbsp;' +
'		<span style="position:absolute; right:1em">' +		
'			<a href="javascript:smwhgCreateCalculationRule.editFormula()">' +
				edit +
'			</a>' +
'		</span>' +
'	</div>' +
'</div>';

	return html;
},

/**
 * @private
 * 
 * This function returns the HTML of the middle part of the GUI where the variables
 * are specified. This part is initially invisible.
 * 
 * @param FormulaRule parsedRule
 * 		If defined, it contains the parsed representation of the rule for the
 * 		initial GUI.
 */
defineVariablesHTML: function(parsedRule) {
	var specifyVariables = gLanguage.getMessage('SR_SPECIFY_VARIABLES');

	var html =
'<div id="variablesDiv" class="rules-frame" style="display:none">' +
'	<div id="variableIntro" class="rules-content">' +
		specifyVariables +
'	</div>' +
'	<div id="variableInput">' +
	this.allVariableSpecificationsHTML(parsedRule) +
'	</div>' +
'</div>';
	
	return html;
},

/**
 * @private
 * 
 * This function returns the HTML for the specification of all variables in
 * the given rule.
 * 
 * @param FormulaRule parsedRule
 * 		Contains the parsed representation of the rule for the
 * 		initial GUI.
 * 
 * @return string
 * 		HTML for all variable specifications
 */
allVariableSpecificationsHTML: function(parsedRule) {
	
	if (parsedRule == undefined) {
		return "";
	}
	var variables = parsedRule.getVariables();
	
	var html =
		'<div class="rules-content">' +
		'	<table style="overflow:hidden; border-color:#aaaaaa" rules="groups" cellpadding="10">';
			
	for (var i = 0, n = variables.length; i < n; ++i) {
		v = variables[i];
		html += this.variableSpecificationHTML(v.name, v.type, v.value);
	}
	
	html += '</table></div>';
	
	return html;
}, 

/**
 * @private
 * 
 * This function returns the HTML for the specification of one variable.
 * 
 * @param string variable
 * 		The name of the variable
 * @param string type
 * 		The type of the variable (i.e. 'prop' or 'const')
 * @param string value
 * 		Depending in the type this is the name of the property or the value
 * 		of the constant.
 * 
 * @return string
 * 		The HTML that allows editing the variable's specification.
 * 
 */
 variableSpecificationHTML: function(variable, type, value) {
 	var varDef = '<span class="calc-rule-variable">'+variable+ '</span>' +
 	             " " + gLanguage.getMessage('SR_IS_A');
 	var propValue = gLanguage.getMessage('SR_PROPERTY_VALUE');
 	var absTerm   = gLanguage.getMessage('SR_ABSOLUTE_TERM');
 	var radioName = 'sr-radio-'+variable;
 	
 	// Initialize variables for type 'prop'
 	var propChecked = 'checked="checked"';
 	var termChecked = '';
 	var propInputVisible = '';
 	var termInputVisible = 'style="display:none"';
 	var property = value;
 	var term = gLanguage.getMessage('SR_ENTER_VALUE');
 	
 	if (type == 'const') {
 		// Change variables for type 'const'
	 	propChecked = '';
	 	termChecked = 'checked="checked"';
	 	propInputVisible = 'style="display:none"';
	 	termInputVisible = '';
	 	property = gLanguage.getMessage('SR_ENTER_PROPERTY');
	 	term = value;
 	}
 	
 	if (value == undefined || value == '') {
 		property = gLanguage.getMessage('SR_ENTER_PROPERTY');
 		term = gLanguage.getMessage('SR_ENTER_VALUE');
 	}
 	
 	var html =
'	<tbody>' +
'		<tr>' +
'			<td>' + varDef + '</td>' +
'			<td>' +
'				<input type="radio" ' +
'                      id="sr-radio-prop-'+variable+'"' +
'                      name="'+radioName+'"'+
'                      onchange="smwhgCreateCalculationRule.radioChanged(this.id)"' +
'					   varname="'+variable+'"'+
'					   value="property" '+propChecked+'>' + 
				propValue + 
           '</td>' +
'			<td><input type="text" value="'+property+'"'+
'					   id="sr-input-prop-'+variable+'"' +
					   propInputVisible +
'					   class="wickEnabled" ' +
'					   onfocus="smwhgCreateCalculationRule.inputFocus(this)"' +
'					   typeHint="'+SMW_PROPERTY_NS+'">' +
           '</td>' +
'		</tr>' +
'		<tr>' +
'			<td></td>' +
'			<td>' +
'				<input type="radio" ' +
'                      id="sr-radio-term-'+variable+'"' +
'                      name="'+radioName+'"'+
'                      onchange="smwhgCreateCalculationRule.radioChanged(this.id)"'+
'					   varname="'+variable+'"'+
'                      value="term" '+termChecked+'>' + 
				absTerm + 
			'</td>' +
'			<td><input type="text" value="'+term+'"'+
					   termInputVisible +
'					   onfocus="smwhgCreateCalculationRule.inputFocus(this)"' +
'					   id="sr-input-term-'+variable+'"' +
'				>' +
           '</td>' +
'		</tr>' +
'	</tbody>';

	return html;
},

/**
 * Returns the HTML of the help box that shows all available operators.
 */
operatorHelpHTML: function() {
	
	var html =
'<div id="sr-calc-op-help" style="position:absolute; top:100px; left:100px; z-index:2; display:none; ">' +
'<table id="sr-calc-op-help-table" border="1" rules="groups">' +
'  <thead class="sr-calc-op-help-table-head">' +
'    <tr>' +
'      <th colspan="4">' + gLanguage.getMessage("SR_OP_HELP_ENTER") + '</th>' +
'    </tr>' +
'  </thead>' +
'  <tbody>' +
'    <tr>' +
'      <td>+</td><td>' + gLanguage.getMessage("SR_OP_ADDITION") + '</td>' +
'      <td>sqrt()</td><td>' + gLanguage.getMessage("SR_OP_SQUARE_ROOT") + '</td>' +
'    </tr>' +
'    <tr>' +
'      <td>-</td><td>' + gLanguage.getMessage("SR_OP_SUBTRACTION") + '</td>' +
'      <td>^</td><td>' + gLanguage.getMessage("SR_OP_EXPONENTIATE") + '</td>' +
'    </tr>' + 
'    <tr>' +
'      <td>*</td><td>' + gLanguage.getMessage("SR_OP_MULTIPLY") + '</td>' +
'      <td>sin()</td><td>' + gLanguage.getMessage("SR_OP_SINE") + '</td>' +
'    </tr>' +
'    <tr>' +
'      <td>/</td><td>' + gLanguage.getMessage("SR_OP_DIVIDE") + '</td>' +
'      <td>cos()</td><td>' + gLanguage.getMessage("SR_OP_COSINE") + '</td>' +
'    </tr>' +
'    <tr>' + 
'      <td>%</td><td>' + gLanguage.getMessage("SR_OP_MODULO") + '</td>' +
'      <td>tan()</td><td>' + gLanguage.getMessage("SR_OP_TANGENT") + '</td>' +
'    </tr>' +
'  </tbody>' +
'</table>' +
'</div>';	 
	return html;
},


/**
 * Callback function for the focus event of the input fields of the variable 
 * specification. If the field contains the initial text like "Enter a property...",
 * the input field is cleared.
 *   
 */
inputFocus: function(object) {
	if (object.value == gLanguage.getMessage('SR_ENTER_VALUE')
	    || object.value == gLanguage.getMessage('SR_ENTER_PROPERTY')) {
		object.value = '';
	}
},

/**
 * @private
 * 
 * This function returns the HTML of the lower part of the GUI where the 
 * preview for the rule is rendered.
 * 
 */
previewHTML: function() {
	
	var html =
'<div id="implicationsDiv" class="rules-frame" style="display:none">' +
'	<div id="implicationsTitle" class="rules-title" style="width:auto;">' +
		gLanguage.getMessage('SR_DERIVED_FACTS') +
'	</div>' +
'	<div id="implicationsContent" class="rules-content">' +
'	</div>' +
'</div>';

	return html;
	
},

/**
 * @public
 * 
 * Callback function for the "Submit..." link. The input area of the formula
 * is replaced with the confirmed formula if the formula is syntactically 
 * correct. The definition area for variables is opened.
 */
submitFormula: function() {
	if ($('formulaDiv')) {
		var formula = $('sr-formula').value;
		this.checkFormula(formula);
	}	
},

/**
 * Closes the variables section and reopens the formula editor.
 */
editFormula: function() {
	$('variablesDiv').hide();
	$('variableInput').innerHTML = "";
	var html = this.defineFormulaHTML(new FormulaRule($('sr-formula').value));
	$('confirmedFormulaDiv').replace(html);
	this.formulaValid = false;
},

/**
 * @public
 * 
 * Shows or hides the operator help box.
 * 
 * @param bool doShow
 * 		
 */
showOpHelp: function(doShow) {
	var help = $('sr-calc-op-help');
	if ($('sr-calc-op-help')) {
		var helpImg = $('sr-op-help-img');
		var l = ""+(helpImg.x-help.getWidth()+20)+"px";
		var t = ""+(helpImg.y + 45)+"px";
		help.setStyle({left:l, top:t});
		if (doShow) {
			$('sr-calc-op-help').show();
		} else {
			$('sr-calc-op-help').hide();
		}
	}
},

/**
 * The radio button in a variable specification has been changed. The corresponding
 * input field is shown and the other is hidden. 
 */
radioChanged: function(radioID) {
	var inputID = radioID.replace(/-radio-/,'-input-');
	var radio = $(radioID);
	$(inputID).show();
	$(inputID).focus();
	
	if ($(inputID).value == '') {
		// The input field is empty => show the request to enter something
		$(inputID).value = (inputID.indexOf('-input-prop-') > 0)
				? gLanguage.getMessage('SR_ENTER_PROPERTY')
				: gLanguage.getMessage('SR_ENTER_VALUE');
		$(inputID).select();
	}
		
	if (inputID.indexOf('-input-prop-') > 0) {
		inputID = inputID.replace(/-input-prop-/,'-input-term-');
	} else {
		inputID = inputID.replace(/-input-term-/,'-input-prop-');
	}
	$(inputID).hide();
	
},

/**
 * @private
 * 
 * Checks if the formula is syntactically correct. If it is, the part for specifying
 * variables is opened, otherwise an error message is shown. 
 * 
 * @param string formula
 * 		The formula to be checked
 */
checkFormula: function(formula) {
	
	function ajaxResponseParseFormula(request) {
		this.hidePendingIndicator();			
		if (request.status == 200) {
			// success
			var result = request.responseText;
			var variables = result.split(',');
			if (variables[0] == 'error') {
				// The formula is erroneous
				$('formulaErrorMsg').innerHTML = result.substr(6);
				$('formulaErrorMsgDiv').show();
			} else if (variables.size() == 2 && variables[1] == '') {
				// There is no variable in the formula
				$('formulaErrorMsg').innerHTML = gLanguage.getMessage('SR_NO_VARIABLE');
				$('formulaErrorMsgDiv').show();
			} else {
				// the formula is valid
				this.formulaValid = true;
				var rule = new FormulaRule(formula);
				var varArray = new Array();
				for (var i = 1; i < variables.size(); ++i) {
					varArray.push({name: variables[i]});
				}
				rule.setVariables(varArray);
				confFormula = this.confirmedFormulaHTML(rule);
				$('formulaDiv').replace(confFormula);
				
				var varSpec = this.allVariableSpecificationsHTML(rule);
				$('variableInput').innerHTML = varSpec;
				$('variablesDiv').show();
			}
		} else {
		}
	};

	this.showPendingIndicator($('sr-formula'));
	
	sajax_do_call('smwf_sr_ParseFormula', 
	          [formula], 
	          ajaxResponseParseFormula.bind(this));

	return false;
},

/**
 * @private
 * 
 * Retrieves the conditions from the user interface, creates a rule and saves it.
 */
saveRule: function(event) {

	function ajaxResponseAddRule(request) {
		this.hidePendingIndicator();			
		if (request.status == 200) {
			// success

			if ($('rule-name')) {
				this.ruleName = $('rule-name').value;
			}			
			var ruleText = 
				"\n\n" +
				'<rule hostlanguage="f-logic" ' +
				      'name="'+this.ruleName+'" ' +
				      'type="' + this.ruleType + '" ' +
				      'formula="'+$('sr-formula').value+'" ' +
				      'variableSpec="'+this.variableSpec+'">' + "\n" +
				request.responseText +
				"\n</rule>\n";
			 	
			// hide the rule editor GUI
			$('createRuleContent').remove();
			
			// show normal wiki text editor GUI
			$('bodyContent').show();
			
			if (this.annotation) {
				// update an existing annotation
				this.annotation.replaceAnnotation(ruleText); 
			} else {
				// append the text to the edit field
				var ei = new SMWEditInterface();
				ei.setValue(ei.getValue() + ruleText);
			}
			ruleToolBar.fillList(true);
						 	
		} else {
		}
	};

	var xml = this.serializeRule();

	if (this.checkRuleTimer) {
		this.checkRuleTimer.stop();
	} 

	this.showPendingIndicator($('sr-save-rule-btn'));
	
	sajax_do_call('smwf_sr_AddRule', 
	          [this.ruleName, xml], 
	          ajaxResponseAddRule.bind(this));
	
},



/**
 * @private
 * 
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

/**
 * @private
 * 
 * Hides the pending indicator.
 */
hidePendingIndicator: function() {
	if (this.pendingIndicator != null) {
		this.pendingIndicator.hide();
		this.pendingIndicator = null;
	}
},

/**
 * @private 
 * 
 * Creates an XML representation of the current rule.
 */
serializeRule: function() {
	
	var xml;
	
	this.variableSpec = '';
	xml = '<?xml version="1.0" encoding="UTF-8"?>' +
		  '<SimpleRule>';
		  
	// serialize property and formula
	xml += '<formula>' +
				'<property>'+wgTitle.replace(/ /g,'_')+'</property>' +
				'<expr>' +
					$('sr-formula').value +
				'</expr>';
			  
	// serialize variables
	var vi = $('variableInput');
	var radios = vi.getElementsBySelector('[type="radio"]');
	for (var i = 0, n = radios.size(); i < n; ++i) {
		var r = radios[i];
		if (r.checked) {
			xml += '<variable>';
			var varname = r.readAttribute('varname');
			xml += '<name>'+varname+'</name>';
			this.variableSpec += varname + '#';
			var inputId = r.id.replace(/-radio-/, '-input-');
			var value = $(inputId).value;
			if (r.id.indexOf('-radio-prop-') > 0) {
				xml += '<property>' + value + '</property>';
				this.variableSpec += 'prop#' + value + ';';
			} else {
				xml += '<constant>' + value + '</constant>';
				this.variableSpec += 'const#' + value + ';';
			}
			xml += '</variable>';
		}
	}
	xml += '</formula></SimpleRule>';
	
	return xml;
	
},

/**
 * @private 
 * 
 * Checks, if the rule in its current state in the UI is valid, i.e. if a 
 * formula is given and if the variables or constants are properly defined.
 * 
 * @return boolean
 * 	true, if the rule is valid and
 * 	false otherwise
 */
checkRule: function() {
	
	// check formula
	if (!this.formulaValid) {
		return false;
	}
	
	// check variables
	var vi = $('variableInput');
	var radios = vi.getElementsBySelector('[type="radio"]');
	for (var i = 0, n = radios.size(); i < n; ++i) {
		var r = radios[i];
		if (r.checked) {
			var inputId = r.id.replace(/-radio-/, '-input-');
			var value = $(inputId).value;
			if (value == '' 
			    || value == gLanguage.getMessage('SR_ENTER_VALUE')
			    || value == gLanguage.getMessage('SR_ENTER_PROPERTY')) {
				// invalid 
				return false;
			}
		}
	}
	
	return true;
	
}

};// End of Class

var FormulaRule = Class.create();

FormulaRule.prototype = {
	
/**
 * Constructor
 * @param string formula
 * 		The formula
 */
initialize: function(formula) {
	this.formula = formula;
	this.variables = null;
},

getFormula: function() {
	return this.formula;
},

/**
 * Sets the variables of the rule. 
 * 
 * @param array<{name,type,value}> variables
 * 		An array of variable definitions. A definition is an object with the
 * 		fields name, type and value.
 */
setVariables: function(variables) {
	this.variables = variables;
},

getVariables: function() {
	return this.variables;
}

}; // End of class FormulaRule

var FormulaRuleParser = {
	
/**
 * A calculation rule in the wiki text starts with the rule element (<rule ...>).
 * This element has a formula- and a variableSpec-attribute. These attributes
 * are used to create a formula rule object of type (FormulaRule).
 * 
 * @param string ruleText
 * 		The text of the rule beginning with the <rule> element
 * 
 * @return bool/FormulaRule
 * 		false, if the rule element does not contain a valid formula of variables or a
 * 		FormulaRule, if the specification is correct.
 */	
parseRule: function(ruleText) {
	var ruleSpec = ruleText.match(/(<rule.*?>)/)
	if (ruleSpec.size() != 2) {
		return false;
	}
	ruleSpec = ruleSpec[1];
	
	var formula = ruleSpec.match(/formula\s*=\s*\"(.*?)\"/);
	if (formula.size() != 2) {
		return false;
	}
	formula = formula[1];
	
	var rule = new FormulaRule(formula);
	
	var variables = ruleSpec.match(/variableSpec\s*=\s*\"(.*?)\"/);
	if (variables.size() != 2) {
		return false;
	}
	variables = variables[1];
	varArray = new Array();

	var vars = variables.split(/;/);
	
	for (var i = 0, n = vars.size(); i < n; ++i) {
		var varspec = vars[i];
		var parts = varspec.split(/#/);
		if (parts.size() == 3) {
			var v = {
				name: parts[0],
				type: parts[1],
				value: parts[2]
			}
			varArray.push(v);
		} else if (varspec != ''){
			return false;
		}
	}
	rule.setVariables(varArray);
	
	return rule;
}
	
};


//  
var smwhgCreateCalculationRule = null;


// SMW_Properties.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
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
var DOMAIN_HINT = "has domain and range";
var RANGE_HINT  = "has domain and range";
var HAS_TYPE = "has type";
var MAX_CARDINALITY = "Has max cardinality";
var MIN_CARDINALITY = "Has min cardinality";
var INVERSE_OF = "Is inverse of";
var TRANSITIVE_RELATION = "Transitive properties";
var SYMMETRICAL_RELATION = "Symmetrical properties";

var SMW_PRP_ALL_VALID =	
	'smwAllValid="allValid ' +
 		'? (show:prop-confirm, hide:prop-invalid) ' +
 		': (show:prop-invalid, hide:prop-confirm)"';
 		
 		
var SMW_PRP_CHECK_MAX_CARD =
	'smwValid="propToolBar.checkMaxCard"';

var SMW_PRP_VALID_CATEGORY_NAME =
	'smwValidValue="^[^<>\|!&$%&\/=\?]{1,255}$: valid ' +
		'? (color: white, hideMessage, valid:true) ' +
	 	': (color: red, showMessage:CATEGORY_NAME_TOO_LONG, valid:false)" ';

var SMW_PRP_CHECK_CATEGORY = 
	'smwCheckType="category: exists ' +
		'? (color: lightgreen, hideMessage, valid:true) ' +
	 	': (color: orange, showMessage:CATEGORY_DOES_NOT_EXIST, valid:true)" ';

var SMW_PRP_CHECK_PROPERTY = 
	'smwCheckType="property: exists ' +
		'? (color: lightgreen, hideMessage, valid:true) ' +
	 	': (color: orange, showMessage:PROPERTY_DOES_NOT_EXIST, valid:true)" ';

var SMW_PRP_VALID_PROPERTY_NAME =
	'smwValidValue="^[^<>\|!&$%&\/=\?]{1,255}$: valid ' +
		'? (color: white, hideMessage, valid:true) ' +
	 	': (color: red, showMessage:PROPERTY_NAME_TOO_LONG, valid:false)" ';


var SMW_PRP_HINT_CATEGORY =
	'typeHint = "' + SMW_CATEGORY_NS + '" ';

var SMW_PRP_HINT_PROPERTY =
	'typeHint="'+ SMW_PROPERTY_NS + '" ';
	
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

var SMW_PRP_TYPE_CHANGED =
	'smwChanged="(call:propToolBar.propTypeChanged)"';
	

var PRP_NARY_CHANGE_LINKS = [['propToolBar.addType()',gLanguage.getMessage('ADD_TYPE'), 'prp-add-type-lnk']];
		
var PRP_APPLY_LINK =
	[['propToolBar.apply()', 'Apply', 'prop-confirm', gLanguage.getMessage('INVALID_VALUES'), 'prop-invalid'],
	 ['propToolBar.cancel()', gLanguage.getMessage('CANCEL')]
	];

var PropertiesToolBar = Class.create();

PropertiesToolBar.prototype = {

initialize: function() {
	//Reference
	this.genTB = new GenericToolBar();
	this.toolbarContainer = null;
	this.pendingIndicator = null;
	this.isRelation = true;
	this.numOfParams = 0;	// number of relation parameters (for n-aries) 
	this.prpNAry = 0;		// DOM-ID-Index for relation parameters
	this.hasDuplicates = false;
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

	// Check if some property characteristic are given several times
	var duplicatesFound = false;
	var doubleDefinition = gLanguage.getMessage('PC_DUPLICATE') + "<ul>";
	
	if (type && type.size() > 1) {
		doubleDefinition += "<li><tt>"+gLanguage.getMessage('PC_HAS_TYPE')+"<tt></li>";
		duplicatesFound = true;
	}
	if (maxCard && maxCard.size() > 1) {
		doubleDefinition += "<li><tt>"+gLanguage.getMessage('PC_MAX_CARD')+"<tt></li>";
		duplicatesFound = true;
	}
	if (minCard && minCard.size() > 1) {
		doubleDefinition += "<li><tt>"+gLanguage.getMessage('PC_MIN_CARD')+"<tt></li>";
		duplicatesFound = true;
	}
	if (inverse && inverse.size() > 1) {
		doubleDefinition += "<li><tt>"+gLanguage.getMessage('PC_INVERSE_OF')+"<tt></li>";
		duplicatesFound = true;
	}
	doubleDefinition += "</ul>";
	
	if (duplicatesFound) {
		if (this.toolbarContainer) {
			this.toolbarContainer.release();
		}
		this.toolbarContainer = new ContainerToolBar('properties-content',800,this.propertiescontainer);
		this.toolbarContainer.createContainerBody(SMW_PRP_ALL_VALID);
		this.toolbarContainer.append(doubleDefinition);
		this.toolbarContainer.finishCreation();
		this.hasDuplicates = true;
		return;
	}
	
	var changed = this.hasAnnotationChanged(
						[type, domain, range, maxCard, minCard, inverse], 
	                    [transitive, symmetric]);
	                    
	changed |= this.hasDuplicates; // Duplicates have been removed
	this.hasDuplicates = false;
	
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
		var typeNs = gLanguage.getMessage('TYPE_NS');
		var l = typeNs.length;
		type = type.charAt(l).toLowerCase() + type.substring(l+1);
	} else {
		type = gLanguage.getMessage('TYPE_PAGE_WONS');
		type = type.charAt(0).toLowerCase() + type.substring(1);
	}
	this.isRelation = (type.toLowerCase() == gLanguage.getMessage('TYPE_PAGE_WONS').toLowerCase());
	
	if (domain == null) {
		domain = "";
	} else {
		domain = domain[0].getSplitValues()[0];
		// trim
		domain = domain.replace(/^\s*(.*?)\s*$/,"$1");
		if (domain.indexOf(gLanguage.getMessage('CATEGORY_NS')) == 0) {
			// Strip the category-keyword
			domain = domain.substring(9);
		}
	}
	if (range == null) {
		range = "";
	} else {
		if (range[0].getSplitValues()[1]) {
			range = range[0].getSplitValues()[1];
			// trim
			range = range.replace(/^\s*(.*?)\s*$/,"$1");
			if (range.indexOf(gLanguage.getMessage('CATEGORY_NS')) == 0) {
				range = range.substring(9);
			}
		} else {
			//range = range[0].getValue();
			range = "";
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
		if (inverse.indexOf(gLanguage.getMessage('PROPERTY_NS')) == 0) {
			inverse = inverse.substring(9);
		}
	}
	transitive = (transitive != null) ? "checked" : "";
	symmetric = (symmetric != null) ? "checked" : "";

	var tb = this.toolbarContainer;
	tb.append(tb.createInput('prp-domain', gLanguage.getMessage('DOMAIN'), '', '',
	                         SMW_PRP_CHECK_CATEGORY + 
	                         SMW_PRP_VALID_CATEGORY_NAME +
	                         SMW_PRP_CHECK_EMPTY_WIE + 
	                         SMW_PRP_HINT_CATEGORY,
	                         true));
	tb.setInputValue('prp-domain',domain);	                         
	                         
	tb.append(tb.createText('prp-domain-msg', '', '' , true));
	
	this.prpNAry = 0;
	this.numOfParams = 0;
	var types = this.wtp.getRelation(HAS_TYPE);
	if (types) {
		types = types[0];
		types = types.getSplitValues();
	} else {
		// no type definition given => default is Type:Page
		types = [gLanguage.getMessage("TYPE_PAGE")];
	}

	var ranges = this.wtp.getRelation(RANGE_HINT);
	
	var rc = 0;
	for (var i = 0, num = types.length; i < num; ++i) {
		
		var t = types[i];
		if (t.indexOf(gLanguage.getMessage('TYPE_NS')) == 0) {
			t = t.substring(gLanguage.getMessage('TYPE_NS').length);
		}	
		tb.append(this.createTypeSelector("prp-type-" + i, 
		                                  "prpNaryType"+i, t,
		                                  "propToolBar.removeType('prp-type-" + i + "')",
		                                  SMW_PRP_NO_EMPTY_SELECTION+
		                                  SMW_PRP_TYPE_CHANGED));
		var r = "";
		var isPage = false;
		if (types[i] == gLanguage.getMessage('TYPE_PAGE')) {
			if (ranges && rc < ranges.length) {
				r = ranges[rc++].getSplitValues()[1];
			}
			// trim
			r = r.replace(/^\s*(.*?)\s*$/,"$1");
			
			if (r.indexOf(gLanguage.getMessage('CATEGORY_NS')) == 0) {
				r = r.substring(gLanguage.getMessage('CATEGORY_NS').length);
			}
			isPage = true;
		}
		tb.append(tb.createInput('prp-range-' + i, gLanguage.getMessage('RANGE'), 
								 '', '',
                     			 SMW_PRP_CHECK_CATEGORY + 
                     			 SMW_PRP_VALID_CATEGORY_NAME +
                     			 SMW_PRP_CHECK_EMPTY_WIE +
	                 			 SMW_PRP_HINT_CATEGORY,
                     			 isPage));
		tb.setInputValue('prp-range-' + i, r);	                         
		tb.append(tb.createText('prp-range-' + i + '-msg', '', '' , isPage));
                    			 
		this.prpNAry++;
		this.numOfParams++;
	}

	tb.append(tb.createInput('prp-inverse-of', gLanguage.getMessage('INVERSE_OF'), '', '',
	                         SMW_PRP_CHECK_PROPERTY +
	                         SMW_PRP_VALID_PROPERTY_NAME +
	                         SMW_PRP_HINT_PROPERTY+
	                         SMW_PRP_CHECK_EMPTY_VIE,
	                         true));
	tb.setInputValue('prp-inverse-of',inverse);	                         
	                         
	tb.append(tb.createText('prp-inverse-of-msg', '', '' , true));

	tb.append(tb.createInput('prp-min-card', gLanguage.getMessage('MIN_CARD'), '', '', 
	                         SMW_PRP_CHECK_MAX_CARD, true, false));
	tb.setInputValue('prp-min-card',minCard);	                         
	                         
	tb.append(tb.createText('prp-min-card-msg', '', '' , true));
	tb.append(tb.createInput('prp-max-card', gLanguage.getMessage('MAX_CARD'), '', '', 
	                         SMW_PRP_CHECK_MAX_CARD, true, false));
	tb.setInputValue('prp-max-card',maxCard);	                         
	tb.append(tb.createText('prp-max-card-msg', '', '' , true));
	tb.append(tb.createCheckBox('prp-transitive', '', [gLanguage.getMessage('TRANSITIVE')], [transitive == 'checked' ? 0 : -1], 'name="transitive"', true));
	tb.append(tb.createCheckBox('prp-symmetric', '', [gLanguage.getMessage('SYMMETRIC')], [symmetric == 'checked' ? 0 : -1], 'name="symmetric"', true));

	
	tb.append(tb.createLink('prp-change-links', PRP_NARY_CHANGE_LINKS, '', true));
	tb.append(tb.createLink('prp-links', PRP_APPLY_LINK, '', true));
				
	tb.finishCreation();
	this.enableWidgets();
	gSTBEventActions.initialCheck($("properties-content-box"));
	//Sets Focus on first Element
//	setTimeout("$('prp-domain').focus();",50);
    
},

checkMaxCard: function(domID) {
	var maco = $('prp-max-card');
	var maxCard = maco.value;
	var mico =  $('prp-min-card');
	var minCard = mico.value;
		
	gSTBEventActions.performSingleAction('color', 'white', mico);
	gSTBEventActions.performSingleAction('hidemessage', null, mico);
	gSTBEventActions.performSingleAction('color', 'white', maco);
	gSTBEventActions.performSingleAction('hidemessage', null, maco);

	if (!maxCard && ! minCard) {
		// neither max. nor min. card. are given
		return true;
	}
	var result = true;
	if (minCard != '') {
		minCard = minCard.match(/^\d+$/);
		if (!minCard) {
			gSTBEventActions.performSingleAction('color', 'red', mico);
			gSTBEventActions.performSingleAction('showmessage', 'INVALID_FORMAT_OF_VALUE', mico);
			result = false;
		} else {
			minCard = minCard * 1;
			gSTBEventActions.performSingleAction('color', 'lightgreen', mico);
			gSTBEventActions.performSingleAction('hidemessage', '', mico);
		}
	}
	if (maxCard != '') {
		maxCard = maxCard.match(/^\d+$/);
		if (!maxCard) {
			gSTBEventActions.performSingleAction('color', 'red', maco);
			gSTBEventActions.performSingleAction('showmessage', 'INVALID_FORMAT_OF_VALUE', maco);
			result = false;
		} else {
			maxCard = maxCard * 1;
			// maxCard must not be 0
			if (maxCard == 0) {
				gSTBEventActions.performSingleAction('color', 'red', maco);
				gSTBEventActions.performSingleAction('showmessage', 'MAX_CARD_MUST_NOT_BE_0', maco);
				result = false;
			} else {
				gSTBEventActions.performSingleAction('color', 'lightgreen', maco);
				gSTBEventActions.performSingleAction('hidemessage', '', maco);
			}
		}
	}
	if (!result) {
		return false;
	}
	
	if (typeof(maxCard) == 'number' && typeof(minCard) == 'string') {
		//maxCard given, minCard not
		gSTBEventActions.performSingleAction('color', 'white', mico);
		gSTBEventActions.performSingleAction('showmessage', 'ASSUME_CARDINALITY_0', mico);
		return true;
	}
	if (typeof(maxCard) == 'string' && typeof(minCard) == 'number') {
		//minCard given, maxCard not
		gSTBEventActions.performSingleAction('color', 'white', maco);
		gSTBEventActions.performSingleAction('showmessage', 'ASSUME_CARDINALITY_INF', maco);
		return true;
	}

	if (!result) {
		return false;
	}	
	
	// maxCard and minCard given => min must be smaller than max
	if (minCard > maxCard) {
		gSTBEventActions.performSingleAction('color', 'red', mico);
		gSTBEventActions.performSingleAction('showmessage', 'MIN_CARD_INVALID', mico);
		return false;
	}
		
	return true;
	
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

propTypeChanged: function(target) {
	var target = $(target);
	
	var typeIdx = target.id.substring(9);
	var rangeId = "prp-range-"+typeIdx;
	
	var attrType = target[target.selectedIndex].text;
	
	var isPage = attrType == gLanguage.getMessage('TYPE_PAGE_WONS');
	var tb = propToolBar.toolbarContainer;
	tb.show(rangeId, isPage);
	if (!isPage) {
		tb.show(rangeId+'-msg', false);
	}
	
	this.isRelation = (this.numOfParams == 1) ? isPage : false;  
	gSTBEventActions.initialCheck($("properties-content-box"));
	this.enableWidgets();

},


addType: function() {
	var tb = this.toolbarContainer;
	var insertAfter = (this.prpNAry==0) ? 'prp-domain-msg' 
							 : $('prp-range-'+(this.prpNAry-1)+'-msg') 
							 	? 'prp-range-'+(this.prpNAry-1)+'-msg'
							 	: 'prp-range-'+(this.prpNAry-1);
	
	
	this.toolbarContainer.insert(insertAfter,
			  this.createTypeSelector("prp-type-" + this.prpNAry, 
	                                  "prpNaryType"+this.prpNAry, 
	                                  gLanguage.getMessage('TYPE_PAGE_WONS'),
	                                  "propToolBar.removeType('prp-type-" + this.prpNAry + "')",
	                                  SMW_PRP_NO_EMPTY_SELECTION+
	                                  SMW_PRP_TYPE_CHANGED));

	tb.insert("prp-type-" + this.prpNAry,
			  tb.createInput('prp-range-' + this.prpNAry, gLanguage.getMessage('RANGE'), 
			  				 "", '',
                 			 SMW_PRP_CHECK_CATEGORY +
                 			 SMW_PRP_VALID_CATEGORY_NAME + 
                 			 SMW_PRP_CHECK_EMPTY_WIE +
                 			 SMW_PRP_HINT_CATEGORY,
                 			 true));
    tb.setInputValue('prp-range-' + this.prpNAry,'');
	tb.insert('prp-range-' + this.prpNAry,
	          tb.createText('prp-range-' + this.prpNAry + '-msg', '', '' , true));

	this.prpNAry++;
	this.numOfParams++;
	this.toolbarContainer.finishCreation();
	this.enableWidgets();
	gSTBEventActions.initialCheck($("properties-content-box"));
		
},

removeType: function(domID) {
	
	this.toolbarContainer.remove(domID)
	this.toolbarContainer.remove(domID+'-msg');
	domID = domID.replace(/type/, 'range');
	this.toolbarContainer.remove(domID)
	this.toolbarContainer.remove(domID+'-msg');
	
	this.numOfParams--;
	if (domID == 'prp-range-'+(this.prpNAry-1)) {
		while (this.prpNAry > 0) {
			--this.prpNAry;
			if ($('prp-type-'+ this.prpNAry)) {
				this.prpNAry++;
				break;
			}
		}
	}
	if (this.numOfParams == 1) {
		var selector = $('prp-type-'+(this.prpNAry*1-1));
		var type = selector[selector.selectedIndex].text;
		this.isRelation = type == gLanguage.getMessage('TYPE_PAGE_WONS');
	}
	this.toolbarContainer.finishCreation();
	this.enableWidgets();
	gSTBEventActions.initialCheck($("properties-content-box"));
},

createTypeSelector: function(id, name, type, deleteAction, attributes) {
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
		var allTypes = builtinTypes.concat([""], userTypes);
		
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
		if (type && !typeFound) {
			if (selection) {
				selection.options[i] = new Option(origTypeString, origTypeString, true, true);
			}
			selIdx = allTypes.length;
			allTypes[allTypes.length] = origTypeString;
//			sel += '<option selected="">' + origTypeString + '</option>';
		}
		
		if ($(id)) {
			gSTBEventActions.initialCheck($(id).up());
		}
		propToolBar.toolbarContainer.finishCreation();
		return [allTypes, selIdx];
	};
	
	var sel = [[gLanguage.getMessage('RETRIEVING_DATATYPES')],0];
	if (gDataTypes.getUserDefinedTypes() == null 
	    || gDataTypes.getBuiltinTypes() == null) {
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

	var isnary = propToolBar.numOfParams > 1;
	
	tb.show("prp-inverse-of", propToolBar.isRelation && !isnary);
	tb.show("prp-transitive", propToolBar.isRelation && !isnary);
	tb.show("prp-symmetric", propToolBar.isRelation && !isnary);
	
	tb.show('prp-min-card', !isnary);
	tb.show('prp-max-card', !isnary);
},

cancel: function(){
	this.toolbarContainer.hideSandglass();
	this.relValues = null;
	this.catValues = null;
	this.createContent();
},

apply: function() {
	this.wtp.initialize();
	var domain   = $("prp-domain").value;
	var inverse  = this.isRelation ? $("prp-inverse-of").value : null;
	var minCard  = this.isNAry ? null : $("prp-min-card").value;
	var maxCard  = this.isNAry ? null : $("prp-max-card").value;
	var transitive = this.isRelation ? $("prp-transitive") : null;
	var symmetric  = this.isRelation ? $("prp-symmetric") : null;

	domain   = (domain   != null && domain   != "") ? gLanguage.getMessage('CATEGORY_NS')+domain : null;
	inverse  = (inverse  != null && inverse  != "") ? gLanguage.getMessage('PROPERTY_NS')+inverse : null;
	minCard  = (minCard  != null && minCard  != "") ? minCard : null;
	maxCard  = (maxCard  != null && maxCard  != "") ? maxCard : null;

	var domainRangeAnno = this.wtp.getRelation(DOMAIN_HINT);
	var maxCardAnno = this.wtp.getRelation(MAX_CARDINALITY);
	var minCardAnno = this.wtp.getRelation(MIN_CARDINALITY);
	var inverseAnno = this.wtp.getRelation(INVERSE_OF);
	  
	var transitiveAnno = this.wtp.getCategory(TRANSITIVE_RELATION);
	var symmetricAnno = this.wtp.getCategory(SYMMETRICAL_RELATION);
	
	
	// change existing annotations
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
	
	// Handle the definition of (n-ary) relations
	// First, remove all domain/range hints
	rangeAnno = this.wtp.getRelation(RANGE_HINT);
	if (rangeAnno) {
		for (var i = 0, num = rangeAnno.length; i < num; i++) {
			rangeAnno[i].remove("");
		}
	}
	
	// Create new domain/range hints.
	var typeString = "";
	for (var i = 0; i < this.prpNAry; i++) {
		var obj = $('prp-type-'+i);
		if (obj) {
			var type = obj[obj.selectedIndex].text;
			if (type.toLowerCase() == gLanguage.getMessage('TYPE_PAGE_WONS').toLowerCase()) {
				// Page found
				var range = $('prp-range-'+i).value;
				var r = (range == '') ? '' : gLanguage.getMessage('CATEGORY_NS')+range;
				r = ((domain == null) ? "" : domain) + "; " + r;
				typeString += gLanguage.getMessage('TYPE_PAGE')+';';
				this.wtp.addRelation(RANGE_HINT, r, null, true);
			} else {
				// type is not Page
				typeString += gLanguage.getMessage('TYPE_NS') + type + ";";
			}
		}
	}
	
	// add the (n-ary) type definition
	attrTypeAnno = this.wtp.getRelation(HAS_TYPE);
	if (typeString != "") {
		// remove final semi-colon
		typeString = typeString.substring(0, typeString.length-1);
		if (attrTypeAnno != null) {
			attrTypeAnno[0].changeValue(typeString);
		} else {			
			this.wtp.addRelation(HAS_TYPE, typeString, null, true);
		}
	} else {
		attrTypeAnno[0].remove("");
	}
	
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
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
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

var REFRESH_DELAY = 0.5; // Refresh delay is 500 ms
var RefreshSemanticToolBar = Class.create();

RefreshSemanticToolBar.prototype = {

	//Constructor
	initialize: function() {
		this.userIsTyping = false;
		this.lastKeypress = 0;	// Timestamp of last keypress event
		this.timeOffset = 0;
		this.contentChanged = false;
		this.wtp = null;

	},

	//Registers event
	register: function(event){
		if(wgAction == "edit"
		   && stb_control.isToolbarAvailable()){
			Event.observe('wpTextbox1', 'change' ,this.changed.bind(this));
			Event.observe('wpTextbox1', 'keyup' ,this.setUserIsTyping.bind(this));
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
			var t = new Date().getTime() - this.timeOffset;
			var dt = (this.lastKeypress != 0)
						? t - this.lastKeypress
						: 0;
			if (dt > REFRESH_DELAY*1000) {
				this.contentChanged = false;
				this.refreshToolBar();
			}
		}
	},

	//registers automatic refresh
	registerTimer: function(){
		this.periodicalTimer = new PeriodicalExecuter(this.refresh.bind(this), REFRESH_DELAY);
	},

	setUserIsTyping: function(event){
		if (typeof(event) == "undefined"  || !event.timeStamp) {
			this.lastKeypress = new Date().getTime();
		} else {
			this.lastKeypress = event.timeStamp;
		}
		if (this.timeOffset == 0) {
			this.timeOffset = new Date().getTime() - this.lastKeypress;
		}
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
		if(window.ruleToolBar){
			ruleToolBar.fillList()
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
					gEditInterface.focus();
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
					gEditInterface.focus();
				}
			}
			if (gEditInterface == null) {
				gEditInterface = new SMWEditInterface();
			}
//			gEditInterface.focus();
		}

	}
}

var refreshSTB = new RefreshSemanticToolBar();
Event.observe(window, 'load', refreshSTB.register.bindAsEventListener(refreshSTB));

// SMW_DragAndResize.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
//Class which holds functionality to make the toolbar draggable and resizeable
var DragResizeHandler = Class.create();
DragResizeHandler.prototype = {

/**
 * @public constructor to initialize class 
 * 
 * @param 
 **/
initialize: function() {
	//Object to store scriptacolous' draggable object
	this.draggable = null;
	//Object to store the modified scriptacolous' object
	this.resizeable = null;
	this.posX = null;
	this.posY = null;
},

/**
 * @public makes toolbar drag and resizable   
 * 
 * @param 
 * 
 */
callme: function(){
	//Makes the toolbar draggable and resizable 
	if(wgAction == "annotate"){
		this.resizeable = new Resizeable('ontomenuanchor',{top: 10, left:10, bottom: 10, right: 10});
		this.enableDragging();
	}
},
/**
 * @public disables dragging of toolbar  
 * 
 * @param 
 */
disableDragging: function(){
	if(this.draggable != null ){
		this.draggable.destroy()
		this.draggable = null;
	}
},
/**
 * @public enables dragging of toolbar  
 * 
 * @param
 */
enableDragging: function(){
	if(this.draggable == null) {
		this.draggable = new Draggable('ontomenuanchor', {
			//TODO: replace handle with proper tab if present	
			handle: 'tab_0', 
			starteffect: function( ){stb_control.setDragging(true);}, 
			endeffect: function(){setTimeout(stb_control.setDragging.bind(stb_control,false),200);}});
		
		//Adds an Observer which stores the position of the stb after each drag
		//this is temporary and probably will be removed if lightweight framework is implemented
		var DragObserver = Class.create();
		DragObserver.prototype = {
			  initialize: function() {
    			this.element = null;
    	
 		 },
			onEnd: function(){
				smwhg_dragresizetoolbar.storePosition();
			}
		};
		
		var dragObserver = new DragObserver();
		Draggables.addObserver(dragObserver);
	}
},
/**
 * @public adjust size of the ontomenuanchor to the semtoolbar laying above   
 * 
 * @param
 */
fixAnchorSize: function(){
	if($('semtoolbar')){
		var height = $('semtoolbar').scrollHeight + $('tabcontainer').scrollHeight + $('activetabcontainer').scrollHeight
		height = height+'px';
		var obj = new Object();
		obj.height = height;
		$('ontomenuanchor').setStyle(obj); 	 	
	}
},

/**
 * @public buffers the current position so it can later be restored
 * 
 */
storePosition: function(){
	var pos = this.getPosition();
	this.posX = pos[0];
	this.posY = pos[1];
},

/**
 * @public buffers the current position so it can later be restored
 *
 * @return array[0] xposition
 * 		   array[1]	yposition
 * 
 */
restorePosition: function(){
	if(!isNaN(this.posX) && !isNaN(this.posY)){
		this.fixAnchorSize();
		this.setPosition(this.posX, this.posY);
	}	
},

/**
 * @public  returns the act. position of the toolbar
 *
 * @return array[0] xposition
 * 		   array[1]	yposition
 * 
 */
getPosition: function(){
	return new Array($('ontomenuanchor').offsetLeft,$('ontomenuanchor').offsetTop);	
},

/**
 * @public positions the STB at the given coordinates considering how it fits best     
 * 
 * @param 	posX
 * 				desired X position
 * 			posY 
 * 				desired Y position
 */
setPosition: function(posX,posY){
	//X-Coordinates
	var toolbarWidth = $('ontomenuanchor').scrollWidth;
	//Check if it fits right to the coordinates
	if( window.innerWidth - posX < toolbarWidth) {
		//Check if it fits left to the coordinates
		if( posX < toolbarWidth){
			// if not place it on the left side of the window
			$('ontomenuanchor').setStyle({right: '' });
			$('ontomenuanchor').setStyle({left: '10px'});
			
		} else {
			//if it fits position it left to the coordinates
			var pos = window.innerWidth - posX;
			$('ontomenuanchor').setStyle({right: pos + 'px' });
			$('ontomenuanchor').setStyle({left: ''});
		}
	} else {
		//if it fits position it right to the coordinates
		var pos = posX;
		$('ontomenuanchor').setStyle({right: ''});
		$('ontomenuanchor').setStyle({left: pos  + 'px'});
	}
	//Y-Coordinates
	var toolbarHeight = $('ontomenuanchor').scrollHeight;
	//Check if it fits bottom to the coordinates
	if( window.innerHeight - posY < toolbarHeight) {
		//Check if it fits top to the coordinates
		if(posY < toolbarHeight){
			// if not place it on the top side of the window	
			$('ontomenuanchor').setStyle({bottom: '' });
			$('ontomenuanchor').setStyle({top: '10px'});
			
		} else {
		var pos = window.innerHeight - posY;
			//if it fits position it top to the coordinates
			$('ontomenuanchor').setStyle({bottom: pos + 'px' });
			$('ontomenuanchor').setStyle({top: ''});
		}
	}else {
		//if it fits position it bottom to the coordinates
		var pos = posY;
		$('ontomenuanchor').setStyle({bottom: ''});
		$('ontomenuanchor').setStyle({top: pos  + 'px'});
	}
} 

}


// TODO: Check License for Resizeable-Code http://blog.craz8.com/articles/2005/12/01/make-your-divs-resizeable
var Resizeable = Class.create();
Resizeable.prototype = {
  initialize: function(element) {
    var options = Object.extend({
      top: 6,
      bottom: 6,
      left: 6,
      right: 6,
      minHeight: 0,
      minWidth: 0,
      zindex: 1000,
      resize: null
    }, arguments[1] || {});

    this.element      = $(element);
    this.handle 	  = this.element;

	if (this.element) {
    	Element.makePositioned(this.element); // fix IE
    }    

    this.options      = options;

    this.active       = false;
    this.resizing     = false;   
    this.currentDirection = '';

    this.eventMouseDown = this.startResize.bindAsEventListener(this);
    this.eventMouseUp   = this.endResize.bindAsEventListener(this);
    this.eventMouseMove = this.update.bindAsEventListener(this);
    this.eventCursorCheck = this.cursor.bindAsEventListener(this);
    this.eventKeypress  = this.keyPress.bindAsEventListener(this);
    
    this.registerEvents();
  },
  destroy: function() {
    Event.stopObserving(this.handle, "mousedown", this.eventMouseDown);
    this.unregisterEvents();
  },
  registerEvents: function() {
    Event.observe(document, "mouseup", this.eventMouseUp);
    Event.observe(document, "mousemove", this.eventMouseMove);
    Event.observe(document, "keypress", this.eventKeypress);
    Event.observe(this.handle, "mousedown", this.eventMouseDown);
    Event.observe(this.element, "mousemove", this.eventCursorCheck);
  },
  unregisterEvents: function() {
    //if(!this.active) return;
    //Event.stopObserving(document, "mouseup", this.eventMouseUp);
    //Event.stopObserving(document, "mousemove", this.eventMouseMove);
    //Event.stopObserving(document, "mousemove", this.eventCursorCheck);
    //Event.stopObserving(document, "keypress", this.eventKeypress);
  },
  startResize: function(event) {
    if(Event.isLeftClick(event)) {
      
      // abort on form elements, fixes a Firefox issue
      var src = Event.element(event);
      if(src.tagName && (
        src.tagName=='INPUT' ||
        src.tagName=='SELECT' ||
        src.tagName=='BUTTON' ||
        src.tagName=='TEXTAREA')) return;

	  var dir = this.directions(event);
	  if (dir.length > 0) {      
	      this.active = true;
    	  var offsets = Position.cumulativeOffset(this.element);
	      this.startTop = offsets[1];
	      this.startLeft = offsets[0];
	      this.startWidth = parseInt(Element.getStyle(this.element, 'width'));
	      this.startHeight = parseInt(Element.getStyle(this.element, 'height'));
	      this.startX = event.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
	      this.startY = event.clientY + document.body.scrollTop + document.documentElement.scrollTop;
	      
	      this.currentDirection = dir;
	      Event.stop(event);
	      //This is to fix resizing bug with only style:right on the beginning
	      //if not set, the left side moves if the right is touched   
	      $('ontomenuanchor').setStyle({left: $('ontomenuanchor').offsetLeft + 'px'});
	      smwhg_dragresizetoolbar.disableDragging();
	  }
    }
  },
  finishResize: function(event, success) {
    // this.unregisterEvents();

    this.active = false;
    this.resizing = false;

    if(this.options.zindex)
      this.element.style.zIndex = this.originalZ;
      
    if (this.options.resize) {
    	this.options.resize(this.element);
    }
  },
  keyPress: function(event) {
    if(this.active) {
      if(event.keyCode==Event.KEY_ESC) {
        this.finishResize(event, false);
        Event.stop(event);
      }
    }
  },
  endResize: function(event) {
    if(this.active && this.resizing) {
      this.finishResize(event, true);
      Event.stop(event);
    }
    this.active = false;
    this.resizing = false;
    smwhg_dragresizetoolbar.enableDragging();
    smwhg_dragresizetoolbar.fixAnchorSize();
  },
  draw: function(event) {
    var pointer = [Event.pointerX(event), Event.pointerY(event)];
    var style = this.element.style;
    if (this.currentDirection.indexOf('n') != -1) {
    	var pointerMoved = this.startY - pointer[1];
    	var margin = Element.getStyle(this.element, 'margin-top') || "0";
    	var newHeight = this.startHeight + pointerMoved;
    	if (newHeight > this.options.minHeight) {
    		style.height = newHeight + "px";
    		style.top = (this.startTop - pointerMoved - parseInt(margin)) + "px";
    	}
    }
    if (this.currentDirection.indexOf('w') != -1) {
    	var pointerMoved = this.startX - pointer[0];
    	var margin = Element.getStyle(this.element, 'margin-left') || "0";
    	var newWidth = this.startWidth + pointerMoved;
    	if (newWidth > this.options.minWidth) {
    		style.left = (this.startLeft - pointerMoved - parseInt(margin))  + "px";
    		style.width = newWidth + "px";
    	}
    }
    if (this.currentDirection.indexOf('s') != -1) {
    	var newHeight = this.startHeight + pointer[1] - this.startY;
    	if (newHeight > this.options.minHeight) {
    		style.height = newHeight + "px";
    	}
    }
    if (this.currentDirection.indexOf('e') != -1) {
    	var newWidth = this.startWidth + pointer[0] - this.startX;
    	if (newWidth > this.options.minWidth) {
    		style.width = newWidth + "px";
    	}
    }
    if(style.visibility=="hidden") style.visibility = ""; // fix gecko rendering
  },
  between: function(val, low, high) {
  	return (val >= low && val < high);
  },
  directions: function(event) {
    var pointer = [Event.pointerX(event), Event.pointerY(event)];
    var offsets = Position.cumulativeOffset(this.element);
    
	var cursor = '';
	if (this.between(pointer[1] - offsets[1], 0, this.options.top)) cursor += 'n';
	if (this.between((offsets[1] + this.element.offsetHeight) - pointer[1], 0, this.options.bottom)) cursor += 's';
	if (this.between(pointer[0] - offsets[0], 0, this.options.left)) cursor += 'w';
	if (this.between((offsets[0] + this.element.offsetWidth) - pointer[0], 0, this.options.right)) cursor += 'e';

	return cursor;
  },
  cursor: function(event) {
  	var cursor = this.directions(event);
	if (cursor.length > 0) {
		cursor += '-resize';
	} else {
		cursor = '';
	}
	this.element.style.cursor = cursor;		
  },
  update: function(event) {
   if(this.active) {
      if(!this.resizing) {
        var style = this.element.style;
        this.resizing = true;
        
        if(Element.getStyle(this.element,'position')=='') 
          style.position = "relative";
        
        if(this.options.zindex) {
          this.originalZ = parseInt(Element.getStyle(this.element,'z-index') || 0);
          style.zIndex = this.options.zindex;
        }
      }
      this.draw(event);

      // fix AppleWebKit rendering
      if(navigator.appVersion.indexOf('AppleWebKit')>0) window.scrollBy(0,0); 
      Event.stop(event);
      return false;
   }
  }
}

//Initialize dragging and resizing functions of stb
smwhg_dragresizetoolbar = new DragResizeHandler();
Event.observe(window, 'load', smwhg_dragresizetoolbar.callme.bind(smwhg_dragresizetoolbar));

/*
setTimeout(function() { 
	setTimeout( function(){
		smwhg_dragresizetoolbar.storePosition();
		smwhg_dragresizetoolbar.setPosition(100,100);
		smwhg_dragresizetoolbar.restorePosition();
		//var ret = smwhg_dragresizetoolbar.getPosition();
		//alert("PosX: "+ret[0]+" PosY: "+ret[0]);
		},1000);
},3000);
*/

// SMW_ContextMenu.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
//Lightweight Framework for displaying context menu in aam
var ContextMenuFramework = Class.create();
ContextMenuFramework.prototype = {
/**
 * Constructor
 */
initialize: function() {
		if(!$("contextmenu")){
			var menu = '<div id="contextmenu"></div>';
//			new Insertion.Top($('innercontent'), menu );
			new Insertion.After($('content'), menu );
		}
		
},

/**
 * Removes the context menu from the DOM tree.
 */
remove: function() {
	if ($("contextmenu")) {
		$("contextmenu").remove();
	}
},

/**
 * @public positions the STB at the given coordinates considering how it fits best     
 * 
 * @param 	String htmlcontent
 * 				htmlcontent which will be set
 * 			Integer containertype 
 * 				containertype (uses enum defined in STB_Framwork.js
 * 			String headline 
 * 				text of the shown headline
 */
setContent: function(htmlcontent,containertype, headline){
	var header;
	var content;
	var contentdiv;
	switch(containertype){
		case CATEGORYCONTAINER:
			if($('cmCategoryHeader')) {
				$('cmCategoryHeader').remove();
			}
			if($('cmCategoryContent')) {
				$('cmCategoryContent').remove();
			}
			header =  '<div id="cmCategoryHeader">'+headline+'</div>';
			content = '<div id="cmCategoryContent"></div>';
			contentdiv = 'cmCategoryContent';
			break;
		case RELATIONCONTAINER:
			if($('cmPropertyHeader')) {
				$('cmPropertyHeader').remove();
			}
			if($('cmPropertyContent')) {
				$('cmPropertyContent').remove();
			}
			header =  '<div id="cmPropertyHeader">'+headline+'</div>';
			content = '<div id="cmPropertyContent"></div>';
			contentdiv = 'cmPropertyContent'
			break;
		case 'ANNOTATIONHINT':
			if($('cmAnnotationHintHeader')) {
				$('cmAnnotationHintHeader').remove();
			}
			if($('cmAnnotationHintContent')) {
				$('cmAnnotationHintContent').remove();
			}
			header =  '<div id="cmAnnotationHintHeader">'+headline+'</div>';
			content = '<div id="cmAnnotationHintContent"></div>';
			contentdiv = 'cmAnnotationHintContent'
			break;
		default:
			if($('cmDefaultHeader')) {
				$('cmDefaultHeader').remove();
			}
			if($('cmDefaultContent')) {
				$('cmDefaultContent').remove();
			}
			header =  '<div id="cmDefaultHeader">'+headline+'</div>';
			content = '<div id="cmDefaultContent"></div>';
			contentdiv = 'cmDefaultContent'
	}
	new Insertion.Bottom('contextmenu', header );
	new Insertion.Bottom('contextmenu', content );
	new Insertion.Bottom(contentdiv, htmlcontent );
	if ($('cmCategoryHeader')) {
		Event.observe('cmCategoryHeader', 'click',
					  function(event) {
					  	$('cmCategoryContent').show();
					  	$('cmPropertyContent').hide();
					  });
	}
	if ($('cmPropertyHeader')) {
		Event.observe('cmPropertyHeader', 'click',
					  function(event) {
					  	$('cmCategoryContent').hide();
					  	$('cmPropertyContent').show();
					  });
	}

},

/**
 * @public  dummy since changes will be visible on the fly with setContent
 *			this is for compatiblity with the stb_framework
 */
contentChanged: function(){

},

/**
 * @public positions the STB at the given coordinates considering how it fits best     
 * 
 * @param 	posX
 * 				desired X position
 * 			posY 
 * 				desired Y position
 */
setPosition: function(posX,posY){
	element = $('contextmenu');
	//X-Coordinates
	var toolbarWidth = element.scrollWidth;
	//Check if it fits right to the coordinates
	if( window.innerWidth - posX < toolbarWidth) {
		//Check if it fits left to the coordinates
		if( posX < toolbarWidth){
			// if not place it on the left side of the window
			element.setStyle({right: '' });
			element.setStyle({left: '10px'});
			
		} else {
			//if it fits position it left to the coordinates
			var pos = window.innerWidth - posX;
			element.setStyle({right: pos + 'px' });
			element.setStyle({left: ''});
		}
	} else {
		//if it fits position it right to the coordinates
		var pos = posX;
		element.setStyle({right: ''});
		element.setStyle({left: pos  + 'px'});
	}
	//Y-Coordinates
	var toolbarHeight = element.scrollHeight;
	//Check if it fits bottom to the coordinates
	if( window.innerHeight - posY < toolbarHeight) {
		//Check if it fits top to the coordinates
		if(posY < toolbarHeight){
			// if not place it on the top side of the window	
			element.setStyle({bottom: '' });
			element.setStyle({top: '10px'});
			
		} else {
		var pos = window.innerHeight - posY;
			//if it fits position it top to the coordinates
			element.setStyle({bottom: pos + 'px' });
			element.setStyle({top: ''});
		}
	}else {
		//if it fits position it bottom to the coordinates
		var pos = posY;
		element.setStyle({bottom: ''});
		element.setStyle({top: pos  + 'px'});
	}
},
/**
 * @public  shows menu
 * 
 */
showMenu: function(){
	$('contextmenu').show();
	if ($('cmCategoryContent')) {
		// The category section is initially folded in
		$('cmCategoryContent').hide();
	}
},
/**
 * @public  hides menu
 */
hideMenu: function(){
	$('contextmenu').hide();
} 

};

/*
setTimeout(function() { 
	//categorycontainer = new divContainer(CATEGORYCONTAINER);
	var contextMenu = new ContextMenuFramework();
	var conToolbar = new ContainerToolBar('menu',500,contextMenu);
	//Event.observe(window, 'load', conToolbar.createContainerBody.bindAsEventListener(conToolbar));
	conToolbar.foo();
	//contextMenu.setPosition(100,100);
},3000);
//*/

// CombinedSearch.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
/*  Copyright 2007, ontoprise GmbH
*   Author: Kai K�hn
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
			sajax_do_call('smwf_cs_Dispatcher', [searchTerm], this.smwfCombinedSearchCallback.bind(this, "csFoundEntities"));
			this.tripleSearchPendingElement.show();
			sajax_do_call('smwf_cs_SearchForTriples', [searchTerm], this.smwfTripleSearchCallback.bind(this, "queryPlaceholder"));
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
		sajax_do_call('smwf_cs_AskForAttributeValues', [parts], this.smwfCombinedSearchCallback.bind(this, "queryPlaceholder"));
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




// SMW_AdvancedAnnotation.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
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
* 
* @author Thomas Schweitzer
*/
//Constants
var AA_RELATION = 0;
var AA_CATEGORY = 1;
	
var AdvancedAnnotation = Class.create();

/**
 * This class handles selections in the rendered wiki page. It loads the 
 * corresponding wiki text from the server and tries to match HTML and wiki text.
 * Annotations can be added to the wiki text and are highlighted in the rendered
 * page.
 */
AdvancedAnnotation.prototype = {
	
	
	/**
	 * Initializes an instance of this class.
	 */
	initialize: function() {
		this.resetSelection();
		
		// The wiki text parser manages the wiki text and adds annotations 
		this.wikiTextParser = null;
		
		this.om = new OntologyModifier();
		this.om.addEditArticleHook(this.annotationsSaved.bind(this));
		
		// Load the wiki text for the current page and store it in the parser.
		this.loadWikiText();
		this.annoCount = 10000;
		this.annotationsChanged = false;
		
		this.contextMenu = null;
		
		// Invalidate the HTML-cache for this article
//		this.om.touchArticle(wgPageName);
		
	},
	
	/**
	 * This method is called, when the mouse button is released. The current
	 * selection is retrieved and used as annotation. Only events in div#bodyContent 
	 * are processed
	 */
	onMouseUp: function(event) {
		smwhgAnnotationHints.hideHints();
		this.hideToolbar();
		
		// Check if the event occurred in div#bodyContent
		var target = event.target;
		while (target) {
			if (target.id && target.id == 'bodyContent') {
				break;
			}
			target = $(target).up('div');
		}
		if (!target) {
			// event was outside of div#bodyContent
			return;
		}
		var annoSelection = this.getSel();
		var sel = annoSelection.toString();
//		window.console.log('Current selection:>'+sel+"<\n");
//		if (this.selection)
//			window.console.log('Prev. selection:>'+this.selectionText+"<\n");
		if (annoSelection.anchorNode == null || sel == '') {
			// nothing selected
			annoSelection = null;
//			window.console.log("Selection empty\n");
		}
		
		var sameSelection = (this.selection != null
		                     && sel == this.selectionText);
//		window.console.log("Same selection:"+sameSelection+"\n");
		
		this.selection = annoSelection;
		this.selectionText = annoSelection ? annoSelection.toString() : null;
		
		var cba = this.canBeAnnotated(annoSelection);
			
		if (annoSelection && !sameSelection && sel != '' && !cba) {
			// a non empty selection can not be annotated
			smwhgAnnotationHints.showMessageAndWikiText(
				gLanguage.getMessage('CAN_NOT_ANNOTATE_SELECTION'), "", 
				event.clientX, event.clientY);
		}
				
		if (cba && annoSelection != '' && !sameSelection) {
			// was it a selection from right to left?
			var leftToRight = true;
			if (annoSelection.anchorNode != annoSelection.focusNode) {
				var node = this.searchBackwards(annoSelection.anchorNode,
												 function(node, param) {
												 	if (node == param) {
												 		return node;
												 	}
												 },
												 annoSelection.focusNode);
				if (node != null) {
					// right to left selection over different nodes
					leftToRight = false;
				}
			} else if (annoSelection.anchorOffset > annoSelection.focusOffset) {
				// right to left selection in a single nodes
				leftToRight = false;
			}
			
			// store details of the selection
			this.selectedText = sel;
			//trim selection
			var trimmed = this.selectedText.replace(/^\s*(.*?)\s*$/,'$1');
			var off1 = this.selectedText.indexOf(trimmed);
			var off2 = this.selectedText.length-trimmed.length-off1;
			this.selectedText = trimmed;
			this.annotatedNode = (leftToRight) ? annoSelection.anchorNode
											   : annoSelection.focusNode;
			this.annoOffset    = (leftToRight) ? annoSelection.anchorOffset
											   : annoSelection.focusOffset;
			this.focusNode   = (leftToRight) ? annoSelection.focusNode
											 : annoSelection.anchorNode;
			this.focusOffset = (leftToRight) ? annoSelection.focusOffset
											 : annoSelection.anchorOffset;
			this.annoOffset += off1;
			this.focusOffset -= off2;
			this.selectionContext = this.getSelectionContext();
			this.performAnnotation(event);
		}
	},
	
	/*
	 * Callback for key-up events. 
	 * When the ESC-key is released, the context menu is hidden.
	 * 
	 * @param event 
	 * 			The key-up event.
	 */
	onKeyUp: function(event){
		
		var key = event.which || event.keyCode;
		if (key == Event.KEY_ESC) {
			this.hideToolbar();
		}
	},
	
	/**
	 * Checks if the <selection> can be annotated, as far as this can be decided
	 * on the HTML level. This is the case, if it does not contain an annotation
	 * or a paragraph.
	 * 
	 * @param selection
	 * 			The selection may contain several nodes, starting at the
	 * 			anchorNode and ending at the focusNode. All nodes between these
	 * 			are analysed.
	 * 
	 * @return boolean
	 * 		<false>, if a span with type 'annotationHighlight' or a paragraph 
	 * 		         is among the selected nodes.
	 * 		<true>, otherwise
	 */
	canBeAnnotated: function(selection) {
		
		if (!selection) {
			return false;
		}
		var anchorNode = selection.anchorNode;
		var focusNode = selection.focusNode;
		
		var an = anchorNode;
		if (!an) {
			return false;
		}
		if (!$(an).up) {
			// <an> is a text node => get its parent
			an = an.parentNode;
		}
		if ($(an).getAttribute('type') === "annotationHighlight" 
		    || $(an).getAttribute('class') === "aam_page_link_highlight") {
			// selection starts in an annotation highlight
			return false;
		} else {
			var annoHighlight = $(an).up('span[type="annotationHighlight"]');
			if (annoHighlight) {
				// selection starts in an annotation highlight
				return false;
			}
			
			var pageLinkHighlight = $(an).up('span[class="aam_page_link_highlight"]');
			if (pageLinkHighlight) {
				// selection starts in an annotation page link highlight
				return false;
			}
			
		}
	
		var fn = focusNode;
		if (!$(fn).up) {
			// <fn> is a text node => get its parent
			fn = fn.parentNode;
		}
		if ($(fn).getAttribute('type') === "annotationHighlight" 
		    || $(fn).getAttribute('class') === "aam_page_link_highlight") {
			// selection ends in an annotation highlight
			return false;
		} else {
			var annoHighlight = $(fn).up('span[type="annotationHighlight"]');
			if (annoHighlight) {
				// selection starts in an annotation highlight
				return false;
			}
			var pageLinkHighlight = $(fn).up('span[class="aam_page_link_highlight"]');
			if (pageLinkHighlight) {
				// selection ends in an annotation page link highlight
				return false;
			}
		}
	
		if (anchorNode !== focusNode) {
			// Check if there is an annotation highlight in the selection.
			var next = this.searchForward(anchorNode, this.searchSelectionEnd.bind(this));
			var prev = this.searchBackwards(anchorNode, this.searchSelectionEnd.bind(this));
			if (next !== focusNode && prev !== focusNode) {
				return false;
			}
			// check if the selection spans different paragraphs
			if ($(an).nodeName !== 'P') {
				an = an.up('p');
			} 
			if ($(fn).nodeName !== 'P') {
				fn = fn.up('p');
			} 
			if (an !== fn) {
				// different paragraphs
				return false;
			}
		}
		
		
		return true;
	},
	
	/**
	 * Tries to find the current selection in the wiki text. If successful, the
	 * corresponding wiki text is augmented with an annotation.
	 * 
	 * @param event
	 * 		The mouse up event
	 */
	performAnnotation: function(event) {
		var anchor = null;
		var firstAnchor = null;
		var secondAnchor = null;
				
		firstAnchor = this.searchBackwards(this.annotatedNode, 
										   this.searchWtoAnchorWoCat.bind(this));
		secondAnchor = this.searchForward(this.focusNode, 
										  this.searchWtoAnchorWoCat.bind(this));

		if (firstAnchor) {
			var start = firstAnchor.getAttribute('name')*1;
			var end = (secondAnchor != null)
						? secondAnchor.getAttribute('name')*1
						: -1;
			// The selection must not contain invalid nodes like pre, nowiki etc.
			var invalid = this.searchInvalidNode(firstAnchor);
			if (!invalid && this.annotatedNode != this.focusNode) {
				// the selection spans several nodes
				invalid = this.searchForward(firstAnchor, 
										     this.searchInvalidNode.bind(this),
										     secondAnchor);
			}
			if (invalid && invalid !== true) {
				// an invalid node has been found.
				var obj = invalid.getAttribute('obj');
				var msgId = "This selection can not be annotated.";
				switch (obj) {
					case 'nowiki': msgId = 'WTP_NOT_IN_NOWIKI'; break;
					case 'template': msgId = 'WTP_NOT_IN_TEMPLATE'; break;
					case 'annotation': msgId = 'WTP_NOT_IN_ANNOTATION'; break;
					case 'ask': msgId = 'WTP_NOT_IN_QUERY'; break;
					case 'pre': msgId = 'WTP_NOT_IN_PREFORMATTED'; break;
				}
				msg = gLanguage.getMessage(msgId);
				msg = msg.replace(/\$1/g, this.selectedText);
				smwhgAnnotationHints.showMessageAndWikiText("(e)"+msg,
															this.wikiTextParser.text.substring(start,end),
															event.clientX, event.clientY);

				this.toolbarEnableAnnotation(false);
				return;
			}										     
			
			var res = this.wikiTextParser.findText(this.selectedText, start, end, this.selectionContext);
			if (res != true) {
				this.toolbarEnableAnnotation(true);
				smwhgAnnotationHints.showMessageAndWikiText("(e)"+res,
															this.wikiTextParser.text.substring(start,end),
															event.clientX, event.clientY);
			} else {
				this.toolbarEnableAnnotation(false);
/*
				smwhgAnnotationHints.showMessageAndWikiText(
					"(i)Wikitext found for selection:<br><b>"+this.selectedText+"</b>",
					this.wikiTextParser.text.substring(start,end),
					event.clientX, event.clientY);
*/					
				// Show toolbar at the cursor position
				this.annotateWithToolbar(event);

			}
		} else {
			this.toolbarEnableAnnotation(false);
			smwhgAnnotationHints.showMessageAndWikiText("(e)No wiki text found for selection:",
			                                            "<b>"+this.selectedText+"</b>",
														event.clientX, event.clientY);
		}
	
	},
	
	/**
	 * Enables or disables the annotation actions in the semantic toolbar.
	 * 
	 * @param boolean enable
	 * 		true  => enable actions
	 * 		false => disable actions
	 */
	toolbarEnableAnnotation: function(enable) {
		catToolBar.enableAnnotation(enable);
	},
	
	/**
	 * Displays the semantic toolbar at the cursor position and shows the
	 * dialogs for annotating categories or properties.
	 * 
	 * @param event
	 * 		The event contains the coordinates for the position of the toolbar.
	 * 
	 */
	annotateWithToolbar: function(event) {
		if (!this.contextMenu) {
			this.contextMenu = new ContextMenuFramework();
		}
		relToolBar.createContextMenu(this.contextMenu);
		catToolBar.createContextMenu(this.contextMenu);
		this.contextMenu.setPosition(event.clientX, event.clientY);
		this.contextMenu.showMenu();
	},
	
	/**
	 * Hides the toolbar if annotation has been cancelled.
	 */
	hideToolbar: function() {
		if (this.contextMenu) {
			this.contextMenu.remove();
			this.contextMenu = null;
		}
		this.toolbarEnableAnnotation(true);
		this.annotatedNode = null;
		this.annotationProposal = null;
		this.wikiTextParser.setSelection(-1, -1);
	},
	
	searchWtoAnchorWoCat: function(node, parameters) {
		if (node.tagName == 'A' 
		    && node.type == "wikiTextOffset"
		    && node.getAttribute('annoType') != 'category') {
			return node;
		} 
	},

	searchWtoAnchor: function(node, parameters) {
		if (node.tagName == 'A' 
		    && node.type == "wikiTextOffset") {
			return node;
		} 
	},
		
	searchSelectionEnd: function(node, parameters) {
		if (node.tagName == 'P') {
			// end search at paragraphs
			return true;
		}
		if (node === this.selection.focusNode) {
			return node;
		} else if (node.getAttribute && 
				   node.getAttribute('type') === 'annotationHighlight') {
			return node;
		}
	},
	
	searchTextNode: function(node, parameters) {
		if (node.nodeName == '#text') {
			// found a text node
			if (parameters) {
				var content = getTextContent(node);
				if (content.indexOf(parameters) >= 0) {
					return node;
				} else {
					return;
				}
			}
			return node;
		}
		
	},
		
	/**
	 * Visits all nodes betweenthe first and the second anchor of the selection.
	 * The selection must not span invalid nodes i.e. nowiki, pre, ask, template, 
	 * annotations. If such a node is found, it is returned. Otherwise the search
	 * is terminated with the result <true>.
	 * 
	 * @param DomNode node
	 * 		The node that is currently visited
	 * @param DomNode secondAnchor
	 * 		The search end, if this node is reached.
	 * @return DomNode or boolean
	 * 		The invalid DOM-node or <true>, if the secondAnchor has been reached.
	 */
	searchInvalidNode: function(node, secondAnchor) {
		if (node === secondAnchor) {
			return true;
		}
		if (node.tagName == 'A' 
		    && node.type == "wikiTextOffset") {
			var obj = node.getAttribute('obj');
			if (obj === 'pre'
//				|| obj === 'annotation'
			    || obj === 'ask'
			    || obj === 'nowiki'
//			    || obj === 'newline'
			    || obj === 'template') {
				return node;
			}
		}
		
	},
	
	/**
	 * Searches recursively backwards from the given node <startNode> to the top
	 * of the document. The document order is traversed in reverse order, visiting
	 * all nodes.
	 * 
	 * @param DomNode startNode
	 * 		Traversal starts at this node. The callback is not called for it.
	 * @param function cbFnc
	 * 		This callback function is called at each node. Traversal stops,
	 * 		if it returns a value. Signature:
	 * 		returnValue function(DomNode node, Object parameters)
	 * @param object parameters
	 * 		This can be any object. It is passed as second parameter to the 
	 * 		callback function <cbFnc>
	 * @param boolean diveDeeper
	 * 		Only uses internally. Don't specify this value.
	 */
	searchBackwards: function(startNode, cbFnc, parameters, diveDeeper) {
		var node = startNode;
		if (!diveDeeper) {
			// go to the previous sibling or the sibling of a parent node
			while (node) {
				if (node.previousSibling) {
					node = node.previousSibling;
					break;
				}
				node = node.parentNode;
			}
		}	
		while (node) {
			// process all siblings and their children
			if (node.lastChild) {
				var result = this.searchBackwards(node.lastChild, cbFnc, parameters, true);
				if (result) {
					return result;
				}
			}
			var result = cbFnc(node, parameters);
			if (result) {
				return result;
			} 
			if (node.previousSibling) {
				node = node.previousSibling;
			} else {
				break;
			}
		}
		if (!diveDeeper && node) {
			node = node.parentNode;
			if (node) {
				var result = this.searchBackwards(node, cbFnc, parameters);
				if (result) {
					return result;
				}
			}
		}
		return null;
		
	},
	

	/**
	 * Searches recursively forward from the given node <startNode> to the end
	 * of the document. The document order is traversed in normal order, visiting
	 * all nodes.
	 * 
	 * @param DomNode startNode
	 * 		Traversal starts at this node. The callback is not called for it.
	 * @param function cbFnc
	 * 		This callback function is called at each node. Traversal stops,
	 * 		if it returns a value. Signature:
	 * 		returnValue function(DomNode node, Object parameters)
	 * @param object parameters
	 * 		This can be any object. It is passed as second parameter to the 
	 * 		callback function <cbFnc>
	 * @param boolean diveDeeper
	 * 		Only uses internally. Don't specify this value.
	 */
	searchForward: function(startNode, cbFnc, parameters, diveDeeper) {
		var node = startNode;
		if (!diveDeeper) {
			// go to the next sibling or the sibling of a parent node
			while (node) {
				if (node.nextSibling) {
					node = node.nextSibling;
					break;
				}
				node = node.parentNode;
			}
		}	
		while (node) {
			// process all siblings and their children
			if (node.firstChild) {
				var result = this.searchForward(node.firstChild, cbFnc, parameters, true);
				if (result) {
					return result;
				}
			}
			var result = cbFnc(node, parameters);
			if (result) {
				return result;
			} 
			if (node.nextSibling) {
				node = node.nextSibling;
			} else {
				break;
			}
		}
		if (!diveDeeper && node) {
			node = node.parentNode;
			if (node) {
				var result = this.searchForward(node, cbFnc, parameters);
				if (result) {
					return result;
				}
			}
		}
		return null;
		
	},
	
	/**
	 * Gets the current selection from the browser.
	 */
	getSel: function() {
		var txt = '';
		if (window.getSelection) {
			txt = window.getSelection();
		} else if (document.getSelection) {
			txt = document.getSelection();
		} else if (document.selection) {
			//IE 
			var selection = document.selection.createRange();
			if (selection.text == '') {
				return {anchorNode: null, 
			       focusNode: null, 
			       anchorOffset: 0,
			       focusOffset: 0,
			       text:"",
			       toString:function() {
			       	return this.text;
			       }
			      };
			}
			var selectedText = selection.text;
			var start = selection.duplicate();
			var end = selection.duplicate();
			start.collapse(true);
			end.collapse(false);
			start.pasteHTML('<a name="ieselectionstart" />');
			end.pasteHTML('<a name="ieselectionend" />');
			var startNode = start.parentElement();
			var tmpNode = startNode.down('a[name=ieselectionstart]');
//			var tmpNode = $$('a[name=ieselectionstart]')[0];
			startNode = tmpNode.nextSibling;
			var anchorOffset = 0;
			$(tmpNode).remove();
			var prev = startNode.previousSibling ? startNode.previousSibling : null;
			if (prev) {
				if (prev.nodeName == '#text') {
					var t = getTextContent(prev);
					anchorOffset = t.length;
					setTextContent(startNode, t+getTextContent(startNode));
					prev.parentNode.removeChild(prev);
				}
			}
			var endNode = end.parentElement();
			tmpNode = endNode.down('a[name=ieselectionend]');
//			tmpNode = $$('a[name=ieselectionend]')[0];
			endNode = tmpNode.previousSibling;
			var focusOffset = getTextContent(endNode).length;
			$(tmpNode).remove();
			var next = endNode.nextSibling ? endNode.nextSibling : null;
			if (next) {
				if (next.nodeName == '#text') {
					var t = getTextContent(next);
					setTextContent(endNode, getTextContent(endNode)+t);
					next.parentNode.removeChild(next);
				}
			}
			
						
			txt = {anchorNode: startNode, 
			       focusNode: endNode, 
			       anchorOffset: anchorOffset,
			       focusOffset: focusOffset,
			       text:selectedText,
			       toString:function() {
			       	return this.text;
			       }
			      };
		}
		return txt;
	},
	
	/**
	 * Gets the context of the current selection i.e. some words before and 
	 * after the current selection.
	 * 
	 * @return array<string>
	 * 		[0]: some words in the text node before the selection; can be empty
	 * 		[1]: some words in the selected text node before the selection; can be empty
	 * 		[2]: some words in the selected text node after the selection; can be empty
	 * 		[3]: some words in the text node after the selection; can be empty
	 *  
	 */
	getSelectionContext: function() {
		var result = new Array("","","","");
		var numWords = 2;
		var preContext = getTextContent(this.annotatedNode);
		preContext = preContext.substring(0, this.annoOffset);
//		window.console.log('preContext:'+preContext+'\n');
		preContext = this.getWords(preContext, numWords, false);
//		window.console.log('preContext:'+preContext+'\n');
		result[1] = preContext;
		
		var postContext = getTextContent(this.focusNode);
		postContext = postContext.substring(this.focusOffset);
//		window.console.log('postContext:'+postContext+'\n');
		postContext = this.getWords(postContext, numWords, true);
//		window.console.log('postContext:'+postContext+'\n');
		result[2] = postContext;
		
		if (preContext == '') {
			var prevNode = this.searchBackwards(this.annotatedNode, 
										        this.searchTextNode.bind(this));
			if (prevNode) {
				preContext = getTextContent(prevNode);
				preContext = this.getWords(preContext, numWords, false);
//				window.console.log('preContext:'+preContext+'\n');
				result[0] = preContext;
			}										        
		}
		if (postContext == '') {
			var postNode = this.searchForward(this.annotatedNode, 
										        this.searchTextNode.bind(this));
			if (postNode) {
				postContext = getTextContent(postNode);
				postContext = this.getWords(postContext, numWords, true);
//				window.console.log('postContext:'+postContext+'\n');
				result[3] = postContext;
			}										        
		}
		
		return result;
	},
	
	/**
	 * Returns the first/last <numWords> words of the string <str>. Words can
	 * be separated by spaces or tabs.
	 * 
	 * @param string str
	 * 		The words are extracted from this string
	 * @param int numWords
	 * 		Number of words to return. 
	 * @param boolean atBeginning
	 * 		true : words are extracted at the beginning of the string
	 * 		false: words are extracted at the end of the string
	 */
	getWords: function(str, numWords, atBeginning) {
		
		if (numWords <= 0 || str == '') {
			return "";
		}
		
		var words = 0;
		var len = str.length-1;
		var start = (atBeginning) ? 0 : len;
		var end = (atBeginning) ? len : 0;
		var inc = (atBeginning) ? 1 : -1;

		for (var i = start; i != end; i += inc) {
			var c = str.charAt(i);
			if (c == ' ' || c == "\t") {
				words++;
				if (words == numWords) {
					break;
				}
			}
		}
		
		return (atBeginning) ? str.substring(0,i) : str.substr(i);
	},
		
	/**
	 * @public
	 * 
	 * Loads the current wiki text via an ajax call. The wiki text is stored in
	 * the wiki text parser <this.wikiTextParser>.
	 * 
	 */
	loadWikiText : function() {
		function ajaxResponseLoadWikiText(request) {
			if (request.status == 200) {
				// success => store wikitext
				this.wikiTextParser = new WikiTextParser(request.responseText);
				this.wikiTextParser.addTextChangedHook(this.updateAnchors.bind(this));
				this.wikiTextParser.addCategoryAddedHook(this.categoryAdded.bind(this));
				this.wikiTextParser.addRelationAddedHook(this.relationAdded.bind(this));
				this.wikiTextParser.addAnnotationRemovedHook(this.annotationRemoved.bind(this));
				catToolBar.setWikiTextParser(this.wikiTextParser);
				relToolBar.setWikiTextParser(this.wikiTextParser);
				catToolBar.fillList(true);
				relToolBar.fillList(true);
			} else {
				this.wikiTextParser = null;
			}
		};
		
		sajax_do_call('smwf_om_GetWikiText', 
		              [wgPageName], 
		              ajaxResponseLoadWikiText.bind(this));
		              
		              
	},
	
	/**
	 * This function is a hook for the wiki text parser. It is called after a
	 * category has been added to the wiki text.
	 * The currently selected text is highlighted with a background specific for
	 * categories. The selection is reset.
	 * 
	 * @param int startPos
	 * 		Start position of the new annotation
	 * @param int endPos
	 * 		End position of the new annotation
	 * @param string name
	 * 		Name of the new category.
	 */
	categoryAdded: function(startPos, endPos, name) {
		this.highlightSelection(AA_CATEGORY, 'aam_new_category_highlight', startPos, endPos);
		catToolBar.fillList();
		smwhgSaveAnnotations.markDirty();
		this.annotationsChanged = true;
		if (this.contextMenu) {
			this.hideToolbar();
		}
	},
	
	/**
	 * This function is a hook for the wiki text parser. It is called after a
	 * relation has been added to the wiki text.
	 * The currently selected text is highlighted with a background specific for
	 * relations. The selection is reset.
	 * 
	 * @param int startPos
	 * 		Start position of the new annotation
	 * @param int endPos
	 * 		End position of the new annotation
	 * @param string name
	 * 		Name of the new relation.
	 */
	relationAdded: function(startPos, endPos, name) {
		if (this.annotationProposal) {
			this.markProposal(AA_RELATION, 'aam_new_anno_prop_highlight');
			this.annotationProposal = null;
		} else {
//			this.markSelection(AA_RELATION, 'aam_new_anno_prop_highlight', startPos, endPos);
			this.highlightSelection(AA_RELATION, 'aam_new_anno_prop_highlight', startPos, endPos);
		}
		relToolBar.fillList();
		smwhgSaveAnnotations.markDirty();
		this.annotationsChanged = true;
		this.hideToolbar();
	},
	
	
	/**
	 * This function is a hook for the wiki text parser. It is called after an
	 * annotation has been removed from the wiki text.
	 * The highlight for the annotation in the rendered article is removed.
	 * 
	 * @param WtpAnnotation annotation
	 * 		The annotation that is removed.
	 */
	annotationRemoved: function(annotation) {
		this.removeAnnotationHighlight(annotation);
		smwhgSaveAnnotations.markDirty();
		this.annotationsChanged = true;
	},


	/**
	 * Resets the stored selection information.
	 */
	resetSelection: function() {
		this.selection = null;
		this.selectionText = null;
		this.annotatedNode = null;
		this.focusNode = null;
		this.annoOffset = 0;
		this.focusOffset = 0;
	},
	
	/**
	 * Embraces the currently selected text with a <span> tag with the css style
	 * <cssClass>.
	 * @param int type
	 * 		The selection is either AA_RELATION or AA_CATEGORY
	 * @param string cssClass
	 * 		Name of the css style that is added as class to the <span> tag.
	 * @param int startPos
	 * 		Wikitextoffset of the new annotation's start that has been created 
	 * 		for the	selection.
	 * @param int endPos
	 * 		Wikitextoffset of the new annotation's end.
	 */
	highlightSelection: function(type, cssClass, startPos, endPos) {

		if (!this.annotatedNode || this.selectedText === "") {
			return;
		}
		
		var imgPath = wgScriptPath + "/extensions/SMWHalo/skins/Annotation/images/"
		var annoDecoStart =
			'<a href="javascript:AdvancedAnnotation.smwhfEditAnno('+this.annoCount+')">'+
			((type == AA_RELATION) 
				? '<img src="' + imgPath + 'edit.gif"/>'
				: "" ) +
			'</a>' +
			'<span id="anno' + this.annoCount +
				'" class="' +cssClass +
				'" type="annotationHighlight">';
		var annoDecoEnd =
			'</span>'+
			'<a href="javascript:AdvancedAnnotation.smwhfDeleteAnno('+this.annoCount+')">'+
   			'<img src="' + imgPath + 'delete.png"/></a>';
   		
   		// add a wrapper span
   		if (this.selectedText.length <= 20) {
			annoDecoStart = '<span id="anno'+this.annoCount+'w" style="white-space:nowrap">'+
						annoDecoStart;
			annoDecoEnd += '</span>';
   		} else {
			annoDecoStart = '<span id="anno'+this.annoCount+'w">'+
						annoDecoStart;
			annoDecoEnd += '</span>';
   		}
   		
   		var annoType = (type == AA_RELATION) 
   						? 'annoType="relation"'
   						: 'annoType="category"';

		// add wiki text offset anchors around the highlight   						
   		annoDecoStart = '<a type="wikiTextOffset" name="'+startPos+'" '+annoType+'></a>' 
   		                + annoDecoStart;
		annoDecoEnd += '<a type="wikiTextOffset" name="'+endPos+'" '+annoType+'></a>';

		var first = this.annotatedNode;
		var second = this.focusNode;
		var foff = this.annoOffset;
		var soff = this.focusOffset;
		
		var t = getTextContent(second);
		t = t.substring(0, soff) + '###end###' + t.substring(soff);
		setTextContent(second, t);
		
		t = getTextContent(first);
		t = t.substring(0, foff) + '###start###' + t.substring(foff);
		setTextContent(first, t);

		var p1 = first.parentNode;
		var p2 = second.parentNode;
		var html1 = p1.innerHTML;
		html1 = html1.replace(/###start###/, annoDecoStart);
		html1 = html1.replace(/###end###/, annoDecoEnd);
		if (p1 === p2) {
			p1.innerHTML = html1;
		} else {
			// The first and the last node of the selection are different
			var html2 = p2.innerHTML;
			// a selection might start within a bold or italic node and end
			// somewhere else => create the span outside the formatted node.
			html2 = html2.replace(/(<b><i>|<i><b>|<i>|<b>)###start###/, '###start###$1');
			html2 = html2.replace(/###start###/, annoDecoStart);
			html2 = html2.replace(/###end###/, annoDecoEnd);
			p1.innerHTML = html1;
			p2.innerHTML = html2;
		}
		
		// reset the current selection
		this.resetSelection();
		
		// The highlighted section may contain annotation proposal => hide them
		var wrapperSpan = $("anno"+this.annoCount+"w");
		
		var proposals = wrapperSpan.descendants();
		for (var i = 0; i < proposals.length; ++i) {
			var p = proposals[i];
			if (p.id.match(/anno\d*w/)) {
				this.hideProposal(p);
			}
		} 
		this.annoCount++;
	},

	
	/**
	 * An annotation proposal is highlighted with a green border and a "+"-icon.
	 * This highlight is replaced by the normal highlight of annotations.
	 * 
	 * @param int type
	 * 		The selection is either AA_RELATION or AA_CATEGORY
	 * @param string cssClass
	 * 		Name of the css style that is added as class to the <span> tag.
	 */
	markProposal: function(type, cssClass) {
		if (!this.annotationProposal) {
			return;
		}
		var text = getTextContent(this.annotationProposal);
		
		var wrapper = this.annotationProposal;
		wrapper.id = 'anno'+this.annoCount+'w';
		if (text.length < 20) {
			wrapper.setStyle("white-space:nowrap");
		}
		if (type == AA_RELATION) {
			var imgPath = wgScriptPath + "/extensions/SMWHalo/skins/Annotation/images/"
			$(wrapper.down('a'))
				.replace('<a href="javascript:AdvancedAnnotation.smwhfEditAnno('+this.annoCount+')">'+
						 '<img src="' + imgPath + 'edit.gif"/>' +
						 '</a>')
		} else {
			$(wrapper.down('a')).remove();
		}
		
		var innerSpan = $(wrapper.down('span'));
		innerSpan.className = cssClass;
		innerSpan.id = 'anno' + this.annoCount;
		
		Insertion.Bottom(wrapper, 
			'<a href="javascript:AdvancedAnnotation.smwhfDeleteAnno('+this.annoCount+')">'+
   			'<img src="' + imgPath + 'delete.png"/></a>'
		);
		this.annoCount++;
		
	},
	
	/**
	 * Hides a proposal.
	 * Proposals are highlighted with a green border and a (+)-button. All this
	 * is contained in a <span> that surrounds the actual text. This method hides 
	 * the proposal visually, without deleting the <span> etc. Thus it can be
	 * restored later.
	 * 
	 * @param DomNode wrapperSpan
	 * 		This DOM node is the wrapper <span> around the proprosed text. 
	 */
	hideProposal: function(wrapperSpan) {
		var img = wrapperSpan.down('img');
		if (img) {
			img.hide();
		}
		var span = wrapperSpan.down('span');
		if (span) {
			span.className = '';
		}
	},
		
	/**
	 * Restores a proposal.
	 * Proposals are highlighted with a green border and a (+)-button. All this
	 * is contained in a <span> that surrounds the actual text. This method restores 
	 * the proposal visually, without creating the <span> etc. See <hideProposal>.
	 * 
	 * @param DomNode wrapperSpan
	 * 		This DOM node is the wrapper <span> around the proprosed text. 
	 */
	restoreProposal: function(wrapperSpan) {
		var img = wrapperSpan.down('img');
		if (img) {
			img.show();
		}
		var span = wrapperSpan.down('span');
		if (span) {
			span.className = 'aam_page_link_highlight';
		}
	},
	/**
	 * This function is a hook for changed text in the wiki text parser. 
	 * It updates the anchors with the wiki text offsets in the DOM after text
	 * has been added or removed.
	 * If a property-annotation has been changed, it gets the highlight style
	 * of a new annotation. 
	 * 
	 * @param array<int>[3] textModifications
	 * 			[0]: start index of replacement in original text
	 * 			[1]: end index of replacement in original text
	 * 			[2]: length of inserted text 
	 */
	updateAnchors: function(textModifications) {
								
//		alert("Added at: "+textModifications.toString());
		if (textModifications) {
			// update anchors
			var start = textModifications[0];
			var end = textModifications[1];
			var len = textModifications[2];
			
			var offset = len - (end-start);
			// get all anchors of type "wikiTextOffset"			
			var anchors = $('bodyContent').getElementsBySelector('a[type="wikiTextOffset"]')
			for (var i = 0; i < anchors.size(); ++i) {
				var val = anchors[i].getAttribute('name')*1;
				if (val > start) {
					anchors[i].setAttribute('name', val+offset);
				}
			}
			
			// If an annotation has been modified, its highlighting should reflect 
			// the change i.e. the class of the surrounding span has to be changed.
			var anchor = $('bodyContent').getElementsBySelector('a[name="'+start+'"]');
			if (anchor.size() == 1) {
				// anchor with wiki text offset found. A span follows the anchor
				var wrapperSpan = anchor[0].next('span');
				if (wrapperSpan) {
					// The wrapper contains a span with the actual highlight
					var span = wrapperSpan.down('span');
					if (span) {
						var highlightClass = span.getAttribute('class');
						if (highlightClass == 'aam_prop_highlight') {
							span.setAttribute('class', 'aam_new_anno_prop_highlight');
						}
					}
				}
			}
			
			smwhgSaveAnnotations.markDirty();
			this.annotationsChanged = true;
		}
	},
	
	/**
	 * @private
	 * 
	 * Deletes an annotation. The <span> that highlights the text and annotation in 
	 * the wiki text are removed.
	 * 
	 * @param int id
	 * 		Each <span> has a unique id that is composed of "anno" and this counter.
	 * 
	 */
	deleteAnnotation: function(id) {
		var annoDescr = this.findAnnotationWithId(id);
		if (!annoDescr) {
			return;
		}
		var anno = annoDescr[0];
		var type = annoDescr[2];
		
		// Remove the annotation from the wiki text
		// => the highlight will be removed in the hook function 
		//    <removeAnnotationHighlight>
		var value = "";
		
		if (anno.getRepresentation().length != 0) {
			value = anno.getRepresentation();
		} else if (anno.getValue) {
			value = anno.getValue();
		}
		anno.remove(value);
		
		if (type && type == 'category') {
			catToolBar.fillList();
		} else {
			relToolBar.fillList();
		}
	},
	
	/**
	 * @private
	 * 
	 * Edits an annotation. The <span> that highlights the text has an <id> that
	 * is used to find the corresponding annotation in the wiki text.
	 * 
	 * @param int id
	 * 		Each <span> has a unique id that is composed of "anno" and this counter.
	 * 
	 */
	editAnnotation: function(id) {
		var annoDescr = this.findAnnotationWithId(id);
		if (!annoDescr) {
			return;
		}
		var anno = annoDescr[0];
		var index = annoDescr[1];
		var type = annoDescr[2];
		
		relToolBar.getselectedItem(index);
	},
	
	/**
	 * The system highlight annotation proposals with a green border. This 
	 * function is called to annotate the proposal with the id <id>.
	 * 
	 * 
	 */
	annotateProposal: function(id) {
		smwhgAnnotationHints.hideHints();
		
		var annoDescr = this.findAnnotationWithId(id);
		if (!annoDescr) {
			return;
		}

		var wrapper = $('anno'+id+'w');
		this.annotationProposal = wrapper;
		
		var anno = annoDescr[0];
		// The selection of the wiki text parser will be replaced by the annotation
		this.wikiTextParser.setSelection(anno.getStart(), anno.getEnd());
		// open property context menu
		if (this.contextMenu) {
			this.contextMenu.remove;
		}
		this.contextMenu = new ContextMenuFramework();
		var annoRepr = anno.getRepresentation();
		var annoName = anno.getName();
		relToolBar.createContextMenu(this.contextMenu, annoName, annoRepr);

 		var vo = wrapper.viewportOffset();
		this.contextMenu.setPosition(vo[0], vo[1]+20);
		this.contextMenu.showMenu();

	},
	
	/**
	 * @private
	 * 
	 * Tries to find an annotation by an id. The <span> that highlights the text
	 * in the article has an <id> that is used to find the corresponding 
	 * annotation in the wiki text.
	 * 
	 * @param int id
	 * 		Each <span> has a unique id that is composed of "anno" and this counter.
	 * 
	 * @return Array<WtpAnnotation, int, String>[annotation, index, type]
	 * 			annotation: The annotation which is managed by the WikiTextParser
	 * 			index: Index of the annotation in the array of annotations in the
	 * 				   WikiTextParser
	 * 			type: Type of the annotation i.e. 'category' or 'relation'
	 * 		  or <null>, if the annotation could not be found
	 * 
	 */
	findAnnotationWithId: function(id) {
		// The highlighted text is embedded in a span with the given id
		var wrapper = $('anno'+id+'w');
		if (!wrapper) {
			alert("Corresponding annotation not found.");
			return null;
		}
		// There is a wiki text offset anchor before the wrapper span.
//		var wtoAnchor = wrapper.previous('a[type="wikiTextOffset"]');
		var wtoAnchor = this.searchBackwards(wrapper, 
										     this.searchWtoAnchor.bind(this));
		
		var annotationStart = wtoAnchor.getAttribute("name")*1;
		var type = wtoAnchor.getAttribute("annoType");
		// Remove the annotation from the wiki text
		var annotations = (type && type == 'category')
							? this.wikiTextParser.getCategories()
							: this.wikiTextParser.getRelations();
		for (var i = 0; i < annotations.length; ++i) {
			var anno = annotations[i];
			if (anno.getStart() == annotationStart) {
				return [anno, i, type];
			}
		}
		// Nothing found among categories or relations
		// => search among links
		var annotations = this.wikiTextParser.getLinks();
		for (var i = 0; i < annotations.length; ++i) {
			var anno = annotations[i];
			if (anno.getStart() == annotationStart) {
				return [anno, i, 'link'];
			}
		}
		return null;		
	},
	
	/**
	 * @private
	 * 
	 * Removes the highlight of an annotation in the rendered article that 
	 * corresponds to the <annotation> of the wiki text parser.
	 * 
	 * @param WtpAnnotation annotation
	 * 		The highlight for this annotation is removed.
	 */
	removeAnnotationHighlight: function(annotation) {
		var start = annotation.getStart();
		
		// find the anchor that marks the start of the annotation
		var wtoAnchor = $('bodyContent').down('a[name="'+start+'"]');
		if (!wtoAnchor) {
			alert("Anchor for annotation not found.")
			return;
		}
		// there must be a wrappper span for the annotation's highlight after the anchor
		var wrapper = wtoAnchor.next("span");
		if (!wrapper) {
			// no wrapper found => wiki text led to empty HTML. This can happen 
			// for category annotations
//			return alert("Corresponding annotation not found.");
			return;
		}
		// There is always the highlighting span within the wrapper span.
		var span = $(wrapper).down('span');
		
		// The highlighted section may contain hidden annotation proposal 
		// => restore them
		// Normal links are replaced by their text content
		var proposals = $(span).descendants();
		for (var i = 0; i < proposals.length; ++i) {
			var p = proposals[i];
			if (p.id.match(/anno\d*w/)) {
				this.restoreProposal(p);
			} else if (p.tagName == 'A') {
				// found a link
				var href = p.getAttribute("href");
				if (href && href.startsWith(wgScriptPath)) {
					// found an internal link.
					if (p.parentNode.className != "aam_page_link_highlight") {
						// its parent is not an annotation proposal
						//  => replace it by the text content
						p.replace(getTextContent(p));
					}
				}
			}
		} 
		
		
		var htmlContent =  span.innerHTML;
		
		// There is a wiki text offset anchor after the wrapper span.
		var nextWtoAnchor = wtoAnchor.next('a[type="wikiTextOffset"]');
		
		// replace the wrapper by the content i.e. create normal text
		wrapper.replace(htmlContent);
		
		// remove the wiki text offset anchor around the annotation
		if (wtoAnchor.getAttribute("name") != "0") {
			// do not remove the very first anchor
			wtoAnchor.remove();
		}
		if (nextWtoAnchor) {
			nextWtoAnchor.remove();
		}
		
	},
	
	/**
	 * Saves the annotations of the current session.
	 * @param boolean exit
	 * 		If <true>, AAM is exited and view mode is entered
	 * 
	 */
	saveAnnotations: function(exit) {
		this.om.editArticle(wgPageName, this.wikiTextParser.getWikiText(),
							gLanguage.getMessage('AH_SAVE_COMMENT'), false);
		smwhgSaveAnnotations.savingAnnotations(exit);
	},
	
	/**
	 * @private
	 * 
	 * This hook function is called when the ajax call for saving the annotations
	 * returns (see <saveAnnotations>).
	 * 
	 * @param boolean success
	 * 		 <true> if the article was successfully edited
	 * @param boolean created
	 * 		<true> if the article has been created
	 * @param string title
	 * 		Title of the article		
	 */
	annotationsSaved: function(success, created, title) {
					
		smwhgSaveAnnotations.annotationsSaved(success);
		
		if (success === true) {
			this.annotationsChanged = false;
		} else {
			smwhgSaveAnnotations.markDirty();
		}
		
	}
	
};// End of Class

AdvancedAnnotation.create = function() {
	if (wgAction == "annotate") {
		smwhgAdvancedAnnotation = new AdvancedAnnotation();
		new PeriodicalExecuter(function(pe) {
			var content = $('content');
			Event.observe(content, 'mouseup', 
			              smwhgAdvancedAnnotation.onMouseUp.bindAsEventListener(smwhgAdvancedAnnotation));
			Event.observe('globalWrapper', 'keyup', 
			              smwhgAdvancedAnnotation.onKeyUp.bindAsEventListener(smwhgAdvancedAnnotation));
						              
			pe.stop();
		}, 2);
	}
	
};

/**
 * This function is called when the page is closed. If the annotations have been
 * changed, the user is asked, if he wants to save the changes.
 */
AdvancedAnnotation.unload = function() {
	if (wgAction == "annotate" && smwhgAdvancedAnnotation.annotationsChanged === true) {
		var save = confirm(gLanguage.getMessage('AAM_SAVE_ANNOTATIONS'));
		if (save === true) {
			smwhgAdvancedAnnotation.saveAnnotations();
		}
	}
	
};

/**
 * Edits an annotation. The <span> that highlights the text has an <id> that
 * is used to find the corresponding annotation in the wiki text.
 * 
 * @param int id
 * 		Each <span> has a unique id that is composed of "anno" and this counter.
 * 
 */
AdvancedAnnotation.smwhfEditAnno = function(id) {
	smwhgAdvancedAnnotation.editAnnotation(id);
};

/**
 * Deletes an annotation. The <span> that highlights the text and annotation in 
 * the wiki text are removed.
 * 
 * @param int id
 * 		Each <span> has a unique id that is composed of "anno" and this counter.
 * 
 */
AdvancedAnnotation.smwhfDeleteAnno = function(id) {
	var del = confirm(gLanguage.getMessage('AAM_DELETE_ANNOTATIONS'));
	if (del === true) {
		smwhgAdvancedAnnotation.deleteAnnotation(id);
	}
	
};

AdvancedAnnotation.smwhfEditLink = function(id) {
	smwhgAdvancedAnnotation.annotateProposal(id);
};

function getTextContent(elem) {
	if (!elem) {
		return null;
	}
	if (elem.textContent) {
		return elem.textContent;
	} else if (elem.innerText) {
		return elem.innerText;
	} else if (elem.nodeValue) {
		return elem.nodeValue;
	}
	return null;
}

function setTextContent(elem, text) {
	if (!elem) {
		return null;
	}
	if (elem.textContent) {
		elem.textContent = text;
	} else if (elem.innerText) {
		elem.innerText = text;
	} else if (elem.nodeValue) {
		elem.nodeValue = text;
	}
}

var smwhgAdvancedAnnotation = null;
Event.observe(window, 'load', AdvancedAnnotation.create);
Event.observe(window, 'unload', AdvancedAnnotation.unload);



// SMW_AnnotationHints.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
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
* 
* @author Thomas Schweitzer
*/

/**
 * @class AnnotationHints
 * This class provides a container for hints and error messages in the semantic
 * toolbar (in the Advanced Annotation Mode).
 * 
 */
var AnnotationHints = Class.create();

AnnotationHints.prototype = {

initialize: function() {

},

showMessageAndWikiText: function(message, wikiText, x, y) {
	this.contextMenu = new ContextMenuFramework();
	
	var tb = new ContainerToolBar('annotationhints-content', 1000, 
	                              this.contextMenu);
	tb.createContainerBody('', 'ANNOTATIONHINT', 
	                       gLanguage.getMessage('ANNOTATION_ERRORS'));

	var m = message.stripScripts();
	if (m != message) {
		m = message.replace(/<\/?b>/g,'');
		m = m.escapeHTML();
	} 
	var w = wikiText.stripScripts();
	if (w != wikiText) {
		w = wikiText.replace(/<\/?b>/g,'');
		w = w.escapeHTML();
	} 
	tb.append(tb.createText('ah-error-msg', m, '', true));
	tb.append(tb.createText('ah-wikitext-msg', w, '' , true));

	tb.finishCreation();
	
	this.contextMenu.setPosition(x,y);
	this.contextMenu.showMenu();
	
	document.onkeyup = function(e) {
		if (!e) {
			return;
		}
		var key = e.which || e.keyCode;
		if (key == Event.KEY_ESC) {
			smwhgAnnotationHints.hideHints();
		}
	}
	
},

hideHints: function() {
	if (this.contextMenu) {
		this.contextMenu.remove();
	}
}

};// End of Class

var smwhgAnnotationHints = new AnnotationHints();




// SMW_GardeningHints.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
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
* 
* @author Thomas Schweitzer
*/

/**
 * @class GardeningHints
 * This class provides a container for gardening hints in semantic toolbar 
 * (in the Advanced Annotation and Edit Mode).
 * 
 */
var GardeningHints = Class.create();

GardeningHints.prototype = {

initialize: function() {
	this.toolbarContainer = null;
},

showToolbar: function() {
	this.gardeningHintContainer.setHeadline(gLanguage.getMessage('ANNOTATION_HINTS'));
	var container = this;
	var hintsLoaded = false;
	container.gardeningHintContainer.showContainerEvent = function() {
		if (hintsLoaded) return;
		if (!container.gardeningHintContainer.isVisible()) return;
		sajax_do_call('smwf_ga_GetGardeningIssues', 
                  [['smw_consistencybot', 'smw_undefinedentitiesbot', 'smw_missingannotationsbot'], '', '', wgPageName, ''], 
                  container.createContent.bind(container));
        hintsLoaded = true;
	}
	this.gardeningHintContainer.setVisibility(false);
	this.gardeningHintContainer.contentChanged();
},

createContainer: function(event){
	if ((wgAction == "edit" || wgAction == "annotate") 
	     && stb_control.isToolbarAvailable()){
		this.gardeningHintContainer = stb_control.createDivContainer(ANNOTATIONHINTCONTAINER,0);
		this.showToolbar();
	}
},

createContent: function(request) {
	
	var tb = this.createToolbar("");
	var html = '';
	if (request.status == 200 
	   && request.responseText != "smwf_ga_GetGardeningIssues: invalid title specified.") {
		var hints = GeneralXMLTools.createDocumentFromString(request.responseText);
		if (hints.documentElement) {
			for (var b = 0, bn = hints.documentElement.childNodes.length; b < bn; b++) {
				// iterate over bots
				var bot = hints.documentElement.childNodes[b];
							
				var n = bot.childNodes.length;
				if (n > 0) {						
	//				html += '<b>' + bot.getAttribute("title") + '</b>';
					html += '<ul>';
					for (var i = 0; i < n; i++) {
						// iterate over the bot's issues
						var issue = bot.childNodes[i];
						html += '<li>' + (issue.textContent?issue.textContent:issue.text) + '</li>';
					}
					html += '</ul>';
				}
			}
		}
	}	
	if (!html) {
		// no hints found
		html = tb.createText('ah-status-msg', gLanguage.getMessage('AH_NO_HINTS'), '', true); 
	}
	tb.append(html);

	tb.finishCreation();
	
	this.gardeningHintContainer.contentChanged();
},

/**
 * Creates a new toolbar for the gardening hints container.
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
	
	this.toolbarContainer = new ContainerToolBar('annotationhint-content',1000,this.gardeningHintContainer);
	var tb = this.toolbarContainer;
	tb.createContainerBody(attributes);
	return tb;
}

};// End of Class

var smwhgGardeningHints = new GardeningHints();
Event.observe(window, 'load', smwhgGardeningHints.createContainer.bindAsEventListener(smwhgGardeningHints));


// SMW_SaveAnnotations.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
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
* 
* @author Thomas Schweitzer
*/

/**
 * @class SaveAnnotations
 * This class provides a container for the save hint ("Don't forget to save your
 * work") in semantic toolbar (in the Advanced Annotation Mode).
 * 
 */
var SaveAnnotations = Class.create();

SaveAnnotations.prototype = {

initialize: function() {
	this.toolbarContainer = null;
	this.exitPage = false;
},

showToolbar: function(request){
	this.savehintcontainer.setHeadline(gLanguage.getMessage('SA_SAVE_ANNOTATION_HINTS'));
	this.createContent();
},

createContainer: function(event){
	if (wgAction == "annotate"
	    && stb_control.isToolbarAvailable()){
		this.savehintcontainer = stb_control.createDivContainer(SAVEANNOTATIONSCONTAINER,0);
		this.showToolbar();
	}
},

createContent: function() {
	
	var tb = this.createToolbar("");
	tb.append(tb.createText('sa-save-msg', '', '', true));
	var html = '<table border="0" class= "saveannotations-innertable"><tr><td>';
	html += tb.createButton('ah-savewikitext-btn',
							  gLanguage.getMessage('SA_SAVE_ANNOTATIONS'), 
							  'smwhgAdvancedAnnotation.saveAnnotations(false)', 
							  '' , true);
	html += "</td><td>";							  
	html += tb.createButton('ah-savewikitext-and-exit-btn',
							  gLanguage.getMessage('SA_SAVE_ANNOTATIONS_AND_EXIT'), 
							  'smwhgAdvancedAnnotation.saveAnnotations(true)', 
							  '' , true);						   
	html += "</td></tr></table>";							  

	tb.append(html);
	
	tb.finishCreation();
	
	this.savehintcontainer.contentChanged();
	$('ah-savewikitext-btn').disable();
	$('ah-savewikitext-and-exit-btn').disable();
},

savingAnnotations: function(exit) {
	
	var msg = gLanguage.getMessage('SA_SAVING_ANNOTATIONS');
	
	var tb = this.toolbarContainer;
	
	var sm = tb.createText('sa-save-msg', msg, '', true);
	tb.replace('sa-save-msg', sm);
	$('saveannotations-content-table-sa-save-msg').show();
	$('ah-savewikitext-btn').disable();
	$('ah-savewikitext-and-exit-btn').disable();
	this.exitPage = exit;
	
},

annotationsSaved: function(success) {
	
	var msg = (success) 
				? gLanguage.getMessage('SA_ANNOTATIONS_SAVED')
				: gLanguage.getMessage('SA_SAVING_ANNOTATIONS_FAILED');
	
	var tb = this.toolbarContainer;
	
	var sm = tb.createText('sa-save-msg', msg, '', true);
	tb.replace('sa-save-msg', sm);
	$('saveannotations-content-table-sa-save-msg').show();
	if (success) {
		$('ah-savewikitext-btn').disable();
		$('ah-savewikitext-and-exit-btn').disable();
		if (this.exitPage) {
			location.href=wgServer+wgScript+"/"+wgPageName;
		}
	}
},

markDirty: function() {
	var tb = this.toolbarContainer;
	
	$('saveannotations-content-table-sa-save-msg').hide();
	$('ah-savewikitext-btn').enable();
	$('ah-savewikitext-and-exit-btn').enable();
	
},

/**
 * Creates a new toolbar for the save annotations container.
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
	
	this.toolbarContainer = new ContainerToolBar('saveannotations-content',900,this.savehintcontainer);
	var tb = this.toolbarContainer;
	tb.createContainerBody(attributes);
	return tb;
}

};// End of Class

var smwhgSaveAnnotations = new SaveAnnotations();
Event.observe(window, 'load', smwhgSaveAnnotations.createContainer.bindAsEventListener(smwhgSaveAnnotations));




// SMW_SemanticNotifications.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
/*  Copyright 2008, ontoprise GmbH
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
* 
* @author Thomas Schweitzer
*/

/**
 * @class SemanticNotifications
 * This class handles the events on Special:SemanticNotifications 
 * 
 */
var SemanticNotifications = Class.create();

SemanticNotifications.prototype = {

	initialize: function() {
		this.pendingIndicator = null;
		$('sn-notification-name').disable();
		this.enable('sn-add-notification', false);
		this.notifications = [];	// The names of all existing notifications
		this.queryLen = 0;
		this.queryEdited = false;
		this.minInterval = 1000;
		this.initialName = $('sn-notification-name').value;
		this.previewOK = false;
	},

	/**
	 * Key-up and blur callback for the query text area. If the query text has 
	 * been changed the input field for the name of the notification and the 'Add' 
	 * button are disabled.
	 */	
	queryChanged: function(event) {
		var key = event.which || event.keyCode;
		
		var len = $('sn-querytext').value.length;
		if (len != this.queryLen) {
			$('sn-notification-name').disable();
			this.enable('sn-add-notification', false);
			$('sn-querytext').focus();
			this.queryEdited = true;
			this.queryLen = len;
		}
		
	},
	
	/**
	 * Key-up and blur callback for the query text area. If the query text has 
	 * been changed the input field for the name of the notification and the 'Add' 
	 * button are disabled.
	 */	
	nameChanged: function(event) {
		var key = event.which || event.keyCode;
		var name = $('sn-notification-name').value.replace(/^\s*(.*?)\s*$/,"$1");
		
		if (name.length == 0) {
			// no name given
			this.enable('sn-add-notification', false);
			$('sn-notification-name').focus();
		} else {
			this.enable('sn-add-notification', this.previewOK);
		}
		
	},
	
	
	/**
	 * The user has changed the update interval. Check if the value is valid.
	 */
	updateIntervalChanged: function(event) {
		var val = $('sn-update-interval').value;
		val = parseInt(val); 
		if (isNaN(val) || val < this.minInterval) {
			var msg = gLanguage.getMessage('SMW_SN_INVALID_UPDATE_INTERVAL');
			msg = msg.replace(/\$1/g, this.minInterval);
			alert(msg);
			val = this.minInterval;
		}
		$('sn-update-interval').value = val;
	},
	
	/**
	 * Adds the query to the semantic notifications of the current user.
	 */
	addNotification: function() {
		
		function ajaxResponseAddNotification(request) {
			this.hidePendingIndicator();			
			if (request.status == 200) {
				// success
				if (request.responseText.indexOf("true") >= 0) {
					this.getAllNotifications();
					// disable button and name input
					$('sn-notification-name').disable();
					this.enable('sn-add-notification', false);

					// Clear input box for query text
					$('sn-querytext').value = '';
					$('sn-notification-name').value = '';
					$('sn-previewbox').innerHTML = '';
					
					this.queryEdited = false;
				} else {
					alert(request.responseText);
				}
			} else {
			}
		};
		
	 	var e = $('sn-add-notification');
	 	var cls = e.className;
	 	if (cls.indexOf('btndisabled') >= 0) {
	 		// Button is disabled
	 		return;
	 	}

		var name =  $('sn-notification-name').value.replace(/^\s*(.*?)\s*$/,"$1");
		
		// does the name already exist?
		if (this.notifications.indexOf(name) >= 0) {
			var msg = gLanguage.getMessage('SN_OVERWRITE_EXISTING');
			msg = msg.replace(/\$1/g, name);
			
			if (!confirm(msg)) {
				return;
			}
		}
		var query = $('sn-querytext').value;
		this.showPendingIndicator(e);
		var ui = $('sn-update-interval').value;
		sajax_do_call('smwf_sn_AddNotification', 
                      [name, wgUserName, query, ui], 
                      ajaxResponseAddNotification.bind(this));
		
	},

	/**
	 * Shows a preview of the result set for the current query.
	 */
	showPreview: function() {

		function ajaxResponseShowPreview(request) {
			this.hidePendingIndicator();			
			if (request.status == 200) {
				var pos = request.responseText.indexOf(',');
				success = request.responseText.substring(0, pos);
				var res = request.responseText.substr(pos+1);
				$('sn-previewbox').innerHTML = res;
				if (success.indexOf('true')>= 0) {
					this.previewOK = true;
					$('sn-notification-name').enable();
					$('sn-notification-name').focus();
					if ($('sn-notification-name').value == this.initialName) {
						$('sn-notification-name').value = '';
						this.enable('sn-add-notification', false);
					} else {
						this.enable('sn-add-notification', true);
					}
				}
			} else {
				$('sn-notification-name').disable();
				this.enable('sn-add-notification', false);
				this.previewOK = false;
			}
		};

	 	var e = $('sn-show-preview-btn');
	 	var cls = e.className;
	 	if (cls.indexOf('btndisabled') >= 0) {
	 		// Button is disabled
	 		return;
	 	}

		this.showPendingIndicator(e);
		var query = $('sn-querytext').value;
		query = this.stripQuery(query);
		sajax_do_call('smwf_sn_ShowPreview', 
                      [query], 
                      ajaxResponseShowPreview.bind(this));
		
	},
	
	/**
	 * Opens the query interface in another tab
	 */
	openQueryInterface: function(element) {
		var qiPage = element.target.readAttribute('specialpage');
		qiPage = unescape(qiPage);
		location.href = qiPage;
//		window.open(qiPage, '_blank');
	},
	
	/**
	 * Retrieves a list of all notifications of the current users and displays
	 * them in the box 'My Notifications'. 
	 */
	getAllNotifications: function() {
		function ajaxResponseGetAllNotifications(request) {
			this.notifications.clear();
			if (request.status == 200) {
				var notifications = request.responseText.split(",");
				var html = '<table class="sn-my-notifications-table">';
				html += '<colgroup>'
						+ '<col width="80%" span="1">'
						+ '<col width="10%" span="2">'
						+ '</colgroup>';
				for (var i = 0; i < notifications.length; ++i) {
					// trim
  					n = notifications[i].replace(/^\s*(.*?)\s*$/,"$1");
  					if (n == '') { 
  						continue;
  					}
  					this.notifications.push(n);
  					html += "<tr><td>"+n+"</td>";
  					html += '<td><a href="javascript:smwhgSemanticNotifications.editNotification(\''+n+'\')">';
  					html += '<img src="'+wgScriptPath+'/extensions/SMWHalo/skins/edit.gif" /></a></td>'; 
  					html += '<td><a href="javascript:smwhgSemanticNotifications.deleteNotification(\''+n+'\')">';
  					html += '<img src="'+wgScriptPath+'/extensions/SMWHalo/skins/delete.png" /></a></td>'; 
					html += "</tr>";
				}
				html += "</table>";
				
				$('sn-notifications-list').innerHTML = html;
			} else {
			}
		};
		sajax_do_call('smwf_sn_GetAllNotifications', 
                      [wgUserName], 
                      ajaxResponseGetAllNotifications.bind(this));
		
	},
	
	/**
	 * Retrieves the definition of the given notification and displays the 
	 * values for editing.
	 * 
	 * @param string notification
	 * 		Name of the notification
	 */
	editNotification: function(notification) {

		function ajaxResponseEditNotification(request) {
			this.hidePendingIndicator();			
			if (request.status == 200) {
				var notification = GeneralXMLTools.createDocumentFromString(request.responseText);
		
				var name  = notification.documentElement.getElementsByTagName("name")[0].firstChild.data;
				var query = notification.documentElement.getElementsByTagName("query")[0].firstChild.data;
				var ui    = notification.documentElement.getElementsByTagName("updateInterval")[0].firstChild.data;
				$('sn-notification-name').value = name;
				$('sn-querytext').value = query;
				$('sn-update-interval').value = ui;
				$('sn-previewbox').innerHTML = '';
				$('sn-querytext').focus();
				this.queryLen = query.length;
				this.queryChanged = false;
			} else {
			}
		};

		if (this.queryEdited) {
			if (!confirm('The current query has been edited but not saved. Do you really want to edit another notification?')) {
				return;
			}
		}

		this.showPendingIndicator($('sn-notifications-list'));
		sajax_do_call('smwf_sn_GetNotification', 
                      [notification, wgUserName], 
                      ajaxResponseEditNotification.bind(this));
		
	},

	/**
	 * Deletes the given notification in the wiki's database.
	 * 
	 * @param string notification
	 * 		Name of the notification
	 */
	deleteNotification: function(notification) {
		function ajaxResponseDeleteNotification(request) {
			this.hidePendingIndicator();			
			if (request.status == 200) {
				if (request.responseText.indexOf("true") >= 0) {
					this.getAllNotifications();
				}
			} else {
			}
		};
		
		var msg = gLanguage.getMessage('SN_DELETE');
		msg = msg.replace(/\$1/g, notification);
		
		if (!confirm(msg)) {
			return;
		}
		

		this.showPendingIndicator($('sn-notifications-list'));
		sajax_do_call('smwf_sn_DeleteNotification', 
                      [notification, wgUserName], 
                      ajaxResponseDeleteNotification.bind(this));
		
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
	},

	/**
	 * 
	 */
	 enable: function(element, enable) {
	 	var e = $(element);
	 	var cls = e.className;
	 	var start = cls.indexOf('btndisabled');
	 	if (enable) {
	 		if (start >= 0) {
	 			e.className = cls.substring(0, start) + cls.substring(start+11);
	 		}
	 	} else {
	 		if (start == -1) {
	 			e.className = cls + " btndisabled";
	 		}
	 	} 
	 },
	 
	 /**
	  * Sets the css-class 'btnhov' for the button under the mouse cursor.
	  */
	 btnMouseOver: function(element) {
	 	var e = element.target;
	 	var cls = e.className;
	 	var start = cls.indexOf('btnhov');
 		if (start == -1) {
 			e.className = cls + " btnhov";
 		}
	 },

	 /**
	  * Removes the css-class 'btnhov' from the button that the mouse cursor
	  * just left.
	  */
	 btnMouseOut: function(element) {
	 	var e = element.target;
	 	var cls = e.className;
	 	var start = cls.indexOf('btnhov');
 		if (start >= 0) {
 			e.className = cls.substring(0, start) + cls.substring(start+6);
 		}
	 },
	
	/**
	 * Removes the ask tags from a query
	 */
	stripQuery: function(query) {
		query = query.replace(/^\s*<ask.*?>\s*(.*?)\s*<\/ask>\s*$/m,"$1");
		
		// strip {{ask#
		var p = query.indexOf('{{#ask:');
		if (p >= 0) {
			query = query.substr(p+7);
			p = query.indexOf('|');
			if (p >= 0) {
				query = query.substring(0, p);
			} else {
				p = query.lastIndexOf('}}');
				if (p >= 0) {
					query = query.substring(0, p);
				}
			}
		}
		
		return query;
		
	}

}

SemanticNotifications.create = function() {
	// Check, if semantic notifications are enabled (user logged in with valid 
	// email address). If not, the complete UI is disabled.
	var qt = $('sn-querytext');
	var enabled = (qt != null) && qt.readAttribute('snenabled');
	if (enabled == 'true') {
		// enable the user interface
		smwhgSemanticNotifications = new SemanticNotifications();
		smwhgSemanticNotifications.minInterval = $('sn-update-interval').value;
		
		var addNotification = $('sn-add-notification');
		Event.observe(addNotification, 'click', 
				      smwhgSemanticNotifications.addNotification.bindAsEventListener(smwhgSemanticNotifications));
		Event.observe(addNotification, 'mouseover', 
				      smwhgSemanticNotifications.btnMouseOver.bindAsEventListener(smwhgSemanticNotifications));
		Event.observe(addNotification, 'mouseout', 
				      smwhgSemanticNotifications.btnMouseOut.bindAsEventListener(smwhgSemanticNotifications));

		var showPreview = $('sn-show-preview-btn');
		Event.observe(showPreview, 'click', 
				      smwhgSemanticNotifications.showPreview.bindAsEventListener(smwhgSemanticNotifications));
		Event.observe(showPreview, 'mouseover', 
				      smwhgSemanticNotifications.btnMouseOver.bindAsEventListener(smwhgSemanticNotifications));
		Event.observe(showPreview, 'mouseout', 
				      smwhgSemanticNotifications.btnMouseOut.bindAsEventListener(smwhgSemanticNotifications));

		var queryInterface = $('sn-query-interface-btn');
		Event.observe(queryInterface, 'click', 
				      smwhgSemanticNotifications.openQueryInterface.bindAsEventListener(smwhgSemanticNotifications));
		Event.observe(queryInterface, 'mouseover', 
				      smwhgSemanticNotifications.btnMouseOver.bindAsEventListener(smwhgSemanticNotifications));
		Event.observe(queryInterface, 'mouseout', 
				      smwhgSemanticNotifications.btnMouseOut.bindAsEventListener(smwhgSemanticNotifications));

		Event.observe('sn-querytext', 'keyup', 
		              smwhgSemanticNotifications.queryChanged.bindAsEventListener(smwhgSemanticNotifications));
		Event.observe('sn-querytext', 'blur', 
		              smwhgSemanticNotifications.queryChanged.bindAsEventListener(smwhgSemanticNotifications));

		Event.observe('sn-notification-name', 'keyup', 
		              smwhgSemanticNotifications.nameChanged.bindAsEventListener(smwhgSemanticNotifications));
		Event.observe('sn-notification-name', 'blur', 
		              smwhgSemanticNotifications.nameChanged.bindAsEventListener(smwhgSemanticNotifications));

		Event.observe('sn-update-interval', 'blur', 
		              smwhgSemanticNotifications.updateIntervalChanged.bindAsEventListener(smwhgSemanticNotifications));
	
		// read a query of the query interface from the cookie
		var query = document.cookie;
		var start = query.indexOf('NOTIFICATION_QUERY=<snq>');
		var end = query.indexOf('</snq>');
		if (start >= 0 && end >= 0) {
			// Query found
			// remove the query from the cookie
			document.cookie = 'NOTIFICATION_QUERY=<snq></snq>;';
			query = query.substring(start+24, end);
			qt.value = query;
			
			this.queryEdited = true;
			this.queryLen = query.length;
		}
		
		smwhgSemanticNotifications.getAllNotifications();
		$('sn-querytext').focus();
	}	
}

var smwhgSemanticNotifications = null;
Event.observe(window, 'load', SemanticNotifications.create);


