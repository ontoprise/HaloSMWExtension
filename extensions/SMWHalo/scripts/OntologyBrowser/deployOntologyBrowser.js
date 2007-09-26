
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
 
// effects.js
// under MIT-License
// script.aculo.us effects.js v1.7.0, Fri Jan 19 19:16:36 CET 2007

// Copyright (c) 2005, 2006 Thomas Fuchs (http://script.aculo.us, http://mir.aculo.us)
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
  if(this.slice(0,4) == 'rgb(') {  
    var cols = this.slice(4,this.length-1).split(',');  
    var i=0; do { color += parseInt(cols[i]).toColorPart() } while (++i<3);  
  } else {  
    if(this.slice(0,1) == '#') {  
      if(this.length==4) for(var i=1;i<4;i++) color += (this.charAt(i) + this.charAt(i)).toLowerCase();  
      if(this.length==7) color = this.toLowerCase();  
    }  
  }  
  return(color.length==7 ? color : (arguments[0] || this));  
}

/*--------------------------------------------------------------------------*/

Element.collectTextNodes = function(element) {  
  return $A($(element).childNodes).collect( function(node) {
    return (node.nodeType==3 ? node.nodeValue : 
      (node.hasChildNodes() ? Element.collectTextNodes(node) : ''));
  }).flatten().join('');
}

Element.collectTextNodesIgnoreClass = function(element, className) {  
  return $A($(element).childNodes).collect( function(node) {
    return (node.nodeType==3 ? node.nodeValue : 
      ((node.hasChildNodes() && !Element.hasClassName(node,className)) ? 
        Element.collectTextNodesIgnoreClass(node, className) : ''));
  }).flatten().join('');
}

Element.setContentZoom = function(element, percent) {
  element = $(element);  
  element.setStyle({fontSize: (percent/100) + 'em'});   
  if(navigator.appVersion.indexOf('AppleWebKit')>0) window.scrollBy(0,0);
  return element;
}

Element.getOpacity = function(element){
  return $(element).getStyle('opacity');
}

Element.setOpacity = function(element, value){
  return $(element).setStyle({opacity:value});
}

Element.getInlineOpacity = function(element){
  return $(element).style.opacity || '';
}

Element.forceRerendering = function(element) {
  try {
    element = $(element);
    var n = document.createTextNode(' ');
    element.appendChild(n);
    element.removeChild(n);
  } catch(e) { }
};

/*--------------------------------------------------------------------------*/

Array.prototype.call = function() {
  var args = arguments;
  this.each(function(f){ f.apply(this, args) });
}

/*--------------------------------------------------------------------------*/

var Effect = {
  _elementDoesNotExistError: {
    name: 'ElementDoesNotExistError',
    message: 'The specified DOM element does not exist, but is required for this effect to operate'
  },
  tagifyText: function(element) {
    if(typeof Builder == 'undefined')
      throw("Effect.tagifyText requires including script.aculo.us' builder.js library");
      
    var tagifyStyle = 'position:relative';
    if(/MSIE/.test(navigator.userAgent) && !window.opera) tagifyStyle += ';zoom:1';
    
    element = $(element);
    $A(element.childNodes).each( function(child) {
      if(child.nodeType==3) {
        child.nodeValue.toArray().each( function(character) {
          element.insertBefore(
            Builder.node('span',{style: tagifyStyle},
              character == ' ' ? String.fromCharCode(160) : character), 
              child);
        });
        Element.remove(child);
      }
    });
  },
  multiple: function(element, effect) {
    var elements;
    if(((typeof element == 'object') || 
        (typeof element == 'function')) && 
       (element.length))
      elements = element;
    else
      elements = $(element).childNodes;
      
    var options = Object.extend({
      speed: 0.1,
      delay: 0.0
    }, arguments[2] || {});
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
    }, arguments[2] || {});
    Effect[element.visible() ? 
      Effect.PAIRS[effect][1] : Effect.PAIRS[effect][0]](element, options);
  }
};

var Effect2 = Effect; // deprecated

/* ------------- transitions ------------- */

Effect.Transitions = {
  linear: Prototype.K,
  sinoidal: function(pos) {
    return (-Math.cos(pos*Math.PI)/2) + 0.5;
  },
  reverse: function(pos) {
    return 1-pos;
  },
  flicker: function(pos) {
    return ((-Math.cos(pos*Math.PI)/4) + 0.75) + Math.random()/4;
  },
  wobble: function(pos) {
    return (-Math.cos(pos*Math.PI*(9*pos))/2) + 0.5;
  },
  pulse: function(pos, pulses) { 
    pulses = pulses || 5; 
    return (
      Math.round((pos % (1/pulses)) * pulses) == 0 ? 
            ((pos * pulses * 2) - Math.floor(pos * pulses * 2)) : 
        1 - ((pos * pulses * 2) - Math.floor(pos * pulses * 2))
      );
  },
  none: function(pos) {
    return 0;
  },
  full: function(pos) {
    return 1;
  }
};

/* ------------- core effects ------------- */

Effect.ScopedQueue = Class.create();
Object.extend(Object.extend(Effect.ScopedQueue.prototype, Enumerable), {
  initialize: function() {
    this.effects  = [];
    this.interval = null;
  },
  _each: function(iterator) {
    this.effects._each(iterator);
  },
  add: function(effect) {
    var timestamp = new Date().getTime();
    
    var position = (typeof effect.options.queue == 'string') ? 
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

    if(!effect.options.queue.limit || (this.effects.length < effect.options.queue.limit))
      this.effects.push(effect);
    
    if(!this.interval) 
      this.interval = setInterval(this.loop.bind(this), 15);
  },
  remove: function(effect) {
    this.effects = this.effects.reject(function(e) { return e==effect });
    if(this.effects.length == 0) {
      clearInterval(this.interval);
      this.interval = null;
    }
  },
  loop: function() {
    var timePos = new Date().getTime();
    for(var i=0, len=this.effects.length;i<len;i++) 
      if(this.effects[i]) this.effects[i].loop(timePos);
  }
});

Effect.Queues = {
  instances: $H(),
  get: function(queueName) {
    if(typeof queueName != 'string') return queueName;
    
    if(!this.instances[queueName])
      this.instances[queueName] = new Effect.ScopedQueue();
      
    return this.instances[queueName];
  }
}
Effect.Queue = Effect.Queues.get('global');

Effect.DefaultOptions = {
  transition: Effect.Transitions.sinoidal,
  duration:   1.0,   // seconds
  fps:        60.0,  // max. 60fps due to Effect.Queue implementation
  sync:       false, // true for combining
  from:       0.0,
  to:         1.0,
  delay:      0.0,
  queue:      'parallel'
}

Effect.Base = function() {};
Effect.Base.prototype = {
  position: null,
  start: function(options) {
    this.options      = Object.extend(Object.extend({},Effect.DefaultOptions), options || {});
    this.currentFrame = 0;
    this.state        = 'idle';
    this.startOn      = this.options.delay*1000;
    this.finishOn     = this.startOn + (this.options.duration*1000);
    this.event('beforeStart');
    if(!this.options.sync)
      Effect.Queues.get(typeof this.options.queue == 'string' ? 
        'global' : this.options.queue.scope).add(this);
  },
  loop: function(timePos) {
    if(timePos >= this.startOn) {
      if(timePos >= this.finishOn) {
        this.render(1.0);
        this.cancel();
        this.event('beforeFinish');
        if(this.finish) this.finish(); 
        this.event('afterFinish');
        return;  
      }
      var pos   = (timePos - this.startOn) / (this.finishOn - this.startOn);
      var frame = Math.round(pos * this.options.fps * this.options.duration);
      if(frame > this.currentFrame) {
        this.render(pos);
        this.currentFrame = frame;
      }
    }
  },
  render: function(pos) {
    if(this.state == 'idle') {
      this.state = 'running';
      this.event('beforeSetup');
      if(this.setup) this.setup();
      this.event('afterSetup');
    }
    if(this.state == 'running') {
      if(this.options.transition) pos = this.options.transition(pos);
      pos *= (this.options.to-this.options.from);
      pos += this.options.from;
      this.position = pos;
      this.event('beforeUpdate');
      if(this.update) this.update(pos);
      this.event('afterUpdate');
    }
  },
  cancel: function() {
    if(!this.options.sync)
      Effect.Queues.get(typeof this.options.queue == 'string' ? 
        'global' : this.options.queue.scope).remove(this);
    this.state = 'finished';
  },
  event: function(eventName) {
    if(this.options[eventName + 'Internal']) this.options[eventName + 'Internal'](this);
    if(this.options[eventName]) this.options[eventName](this);
  },
  inspect: function() {
    var data = $H();
    for(property in this)
      if(typeof this[property] != 'function') data[property] = this[property];
    return '#<Effect:' + data.inspect() + ',options:' + $H(this.options).inspect() + '>';
  }
}

Effect.Parallel = Class.create();
Object.extend(Object.extend(Effect.Parallel.prototype, Effect.Base.prototype), {
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
      if(effect.finish) effect.finish(position);
      effect.event('afterFinish');
    });
  }
});

Effect.Event = Class.create();
Object.extend(Object.extend(Effect.Event.prototype, Effect.Base.prototype), {
  initialize: function() {
    var options = Object.extend({
      duration: 0
    }, arguments[0] || {});
    this.start(options);
  },
  update: Prototype.emptyFunction
});

Effect.Opacity = Class.create();
Object.extend(Object.extend(Effect.Opacity.prototype, Effect.Base.prototype), {
  initialize: function(element) {
    this.element = $(element);
    if(!this.element) throw(Effect._elementDoesNotExistError);
    // make this work on IE on elements without 'layout'
    if(/MSIE/.test(navigator.userAgent) && !window.opera && (!this.element.currentStyle.hasLayout))
      this.element.setStyle({zoom: 1});
    var options = Object.extend({
      from: this.element.getOpacity() || 0.0,
      to:   1.0
    }, arguments[1] || {});
    this.start(options);
  },
  update: function(position) {
    this.element.setOpacity(position);
  }
});

Effect.Move = Class.create();
Object.extend(Object.extend(Effect.Move.prototype, Effect.Base.prototype), {
  initialize: function(element) {
    this.element = $(element);
    if(!this.element) throw(Effect._elementDoesNotExistError);
    var options = Object.extend({
      x:    0,
      y:    0,
      mode: 'relative'
    }, arguments[1] || {});
    this.start(options);
  },
  setup: function() {
    // Bug in Opera: Opera returns the "real" position of a static element or
    // relative element that does not have top/left explicitly set.
    // ==> Always set top and left for position relative elements in your stylesheets 
    // (to 0 if you do not need them) 
    this.element.makePositioned();
    this.originalLeft = parseFloat(this.element.getStyle('left') || '0');
    this.originalTop  = parseFloat(this.element.getStyle('top')  || '0');
    if(this.options.mode == 'absolute') {
      // absolute movement, so we need to calc deltaX and deltaY
      this.options.x = this.options.x - this.originalLeft;
      this.options.y = this.options.y - this.originalTop;
    }
  },
  update: function(position) {
    this.element.setStyle({
      left: Math.round(this.options.x  * position + this.originalLeft) + 'px',
      top:  Math.round(this.options.y  * position + this.originalTop)  + 'px'
    });
  }
});

// for backwards compatibility
Effect.MoveBy = function(element, toTop, toLeft) {
  return new Effect.Move(element, 
    Object.extend({ x: toLeft, y: toTop }, arguments[3] || {}));
};

Effect.Scale = Class.create();
Object.extend(Object.extend(Effect.Scale.prototype, Effect.Base.prototype), {
  initialize: function(element, percent) {
    this.element = $(element);
    if(!this.element) throw(Effect._elementDoesNotExistError);
    var options = Object.extend({
      scaleX: true,
      scaleY: true,
      scaleContent: true,
      scaleFromCenter: false,
      scaleMode: 'box',        // 'box' or 'contents' or {} with provided values
      scaleFrom: 100.0,
      scaleTo:   percent
    }, arguments[2] || {});
    this.start(options);
  },
  setup: function() {
    this.restoreAfterFinish = this.options.restoreAfterFinish || false;
    this.elementPositioning = this.element.getStyle('position');
    
    this.originalStyle = {};
    ['top','left','width','height','fontSize'].each( function(k) {
      this.originalStyle[k] = this.element.style[k];
    }.bind(this));
      
    this.originalTop  = this.element.offsetTop;
    this.originalLeft = this.element.offsetLeft;
    
    var fontSize = this.element.getStyle('font-size') || '100%';
    ['em','px','%','pt'].each( function(fontSizeType) {
      if(fontSize.indexOf(fontSizeType)>0) {
        this.fontSize     = parseFloat(fontSize);
        this.fontSizeType = fontSizeType;
      }
    }.bind(this));
    
    this.factor = (this.options.scaleTo - this.options.scaleFrom)/100;
    
    this.dims = null;
    if(this.options.scaleMode=='box')
      this.dims = [this.element.offsetHeight, this.element.offsetWidth];
    if(/^content/.test(this.options.scaleMode))
      this.dims = [this.element.scrollHeight, this.element.scrollWidth];
    if(!this.dims)
      this.dims = [this.options.scaleMode.originalHeight,
                   this.options.scaleMode.originalWidth];
  },
  update: function(position) {
    var currentScale = (this.options.scaleFrom/100.0) + (this.factor * position);
    if(this.options.scaleContent && this.fontSize)
      this.element.setStyle({fontSize: this.fontSize * currentScale + this.fontSizeType });
    this.setDimensions(this.dims[0] * currentScale, this.dims[1] * currentScale);
  },
  finish: function(position) {
    if(this.restoreAfterFinish) this.element.setStyle(this.originalStyle);
  },
  setDimensions: function(height, width) {
    var d = {};
    if(this.options.scaleX) d.width = Math.round(width) + 'px';
    if(this.options.scaleY) d.height = Math.round(height) + 'px';
    if(this.options.scaleFromCenter) {
      var topd  = (height - this.dims[0])/2;
      var leftd = (width  - this.dims[1])/2;
      if(this.elementPositioning == 'absolute') {
        if(this.options.scaleY) d.top = this.originalTop-topd + 'px';
        if(this.options.scaleX) d.left = this.originalLeft-leftd + 'px';
      } else {
        if(this.options.scaleY) d.top = -topd + 'px';
        if(this.options.scaleX) d.left = -leftd + 'px';
      }
    }
    this.element.setStyle(d);
  }
});

Effect.Highlight = Class.create();
Object.extend(Object.extend(Effect.Highlight.prototype, Effect.Base.prototype), {
  initialize: function(element) {
    this.element = $(element);
    if(!this.element) throw(Effect._elementDoesNotExistError);
    var options = Object.extend({ startcolor: '#ffff99' }, arguments[1] || {});
    this.start(options);
  },
  setup: function() {
    // Prevent executing on elements not in the layout flow
    if(this.element.getStyle('display')=='none') { this.cancel(); return; }
    // Disable background image during the effect
    this.oldStyle = {};
    if (!this.options.keepBackgroundImage) {
      this.oldStyle.backgroundImage = this.element.getStyle('background-image');
      this.element.setStyle({backgroundImage: 'none'});
    }
    if(!this.options.endcolor)
      this.options.endcolor = this.element.getStyle('background-color').parseColor('#ffffff');
    if(!this.options.restorecolor)
      this.options.restorecolor = this.element.getStyle('background-color');
    // init color calculations
    this._base  = $R(0,2).map(function(i){ return parseInt(this.options.startcolor.slice(i*2+1,i*2+3),16) }.bind(this));
    this._delta = $R(0,2).map(function(i){ return parseInt(this.options.endcolor.slice(i*2+1,i*2+3),16)-this._base[i] }.bind(this));
  },
  update: function(position) {
    this.element.setStyle({backgroundColor: $R(0,2).inject('#',function(m,v,i){
      return m+(Math.round(this._base[i]+(this._delta[i]*position)).toColorPart()); }.bind(this)) });
  },
  finish: function() {
    this.element.setStyle(Object.extend(this.oldStyle, {
      backgroundColor: this.options.restorecolor
    }));
  }
});

Effect.ScrollTo = Class.create();
Object.extend(Object.extend(Effect.ScrollTo.prototype, Effect.Base.prototype), {
  initialize: function(element) {
    this.element = $(element);
    this.start(arguments[1] || {});
  },
  setup: function() {
    Position.prepare();
    var offsets = Position.cumulativeOffset(this.element);
    if(this.options.offset) offsets[1] += this.options.offset;
    var max = window.innerHeight ? 
      window.height - window.innerHeight :
      document.body.scrollHeight - 
        (document.documentElement.clientHeight ? 
          document.documentElement.clientHeight : document.body.clientHeight);
    this.scrollStart = Position.deltaY;
    this.delta = (offsets[1] > max ? max : offsets[1]) - this.scrollStart;
  },
  update: function(position) {
    Position.prepare();
    window.scrollTo(Position.deltaX, 
      this.scrollStart + (position*this.delta));
  }
});

/* ------------- combination effects ------------- */

Effect.Fade = function(element) {
  element = $(element);
  var oldOpacity = element.getInlineOpacity();
  var options = Object.extend({
  from: element.getOpacity() || 1.0,
  to:   0.0,
  afterFinishInternal: function(effect) { 
    if(effect.options.to!=0) return;
    effect.element.hide().setStyle({opacity: oldOpacity}); 
  }}, arguments[1] || {});
  return new Effect.Opacity(element,options);
}

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
  }}, arguments[1] || {});
  return new Effect.Opacity(element,options);
}

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
     }, arguments[1] || {})
   );
}

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
    }, arguments[1] || {})
  );
}

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
  }, arguments[1] || {}));
}

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
  }, arguments[1] || {}));
}

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
      }, arguments[1] || {}));
}

Effect.Shake = function(element) {
  element = $(element);
  var oldStyle = {
    top: element.getStyle('top'),
    left: element.getStyle('left') };
    return new Effect.Move(element, 
      { x:  20, y: 0, duration: 0.05, afterFinishInternal: function(effect) {
    new Effect.Move(effect.element,
      { x: -40, y: 0, duration: 0.1,  afterFinishInternal: function(effect) {
    new Effect.Move(effect.element,
      { x:  40, y: 0, duration: 0.1,  afterFinishInternal: function(effect) {
    new Effect.Move(effect.element,
      { x: -40, y: 0, duration: 0.1,  afterFinishInternal: function(effect) {
    new Effect.Move(effect.element,
      { x:  40, y: 0, duration: 0.1,  afterFinishInternal: function(effect) {
    new Effect.Move(effect.element,
      { x: -20, y: 0, duration: 0.05, afterFinishInternal: function(effect) {
        effect.element.undoPositioned().setStyle(oldStyle);
  }}) }}) }}) }}) }}) }});
}

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
      if(window.opera) effect.element.setStyle({top: ''});
      effect.element.makeClipping().setStyle({height: '0px'}).show(); 
    },
    afterUpdateInternal: function(effect) {
      effect.element.down().setStyle({bottom:
        (effect.dims[0] - effect.element.clientHeight) + 'px' }); 
    },
    afterFinishInternal: function(effect) {
      effect.element.undoClipping().undoPositioned();
      effect.element.down().undoPositioned().setStyle({bottom: oldInnerBottom}); }
    }, arguments[1] || {})
  );
}

Effect.SlideUp = function(element) {
  element = $(element).cleanWhitespace();
  var oldInnerBottom = element.down().getStyle('bottom');
  return new Effect.Scale(element, window.opera ? 0 : 1,
   Object.extend({ scaleContent: false, 
    scaleX: false, 
    scaleMode: 'box',
    scaleFrom: 100,
    restoreAfterFinish: true,
    beforeStartInternal: function(effect) {
      effect.element.makePositioned();
      effect.element.down().makePositioned();
      if(window.opera) effect.element.setStyle({top: ''});
      effect.element.makeClipping().show();
    },  
    afterUpdateInternal: function(effect) {
      effect.element.down().setStyle({bottom:
        (effect.dims[0] - effect.element.clientHeight) + 'px' });
    },
    afterFinishInternal: function(effect) {
      effect.element.hide().undoClipping().undoPositioned().setStyle({bottom: oldInnerBottom});
      effect.element.down().undoPositioned();
    }
   }, arguments[1] || {})
  );
}

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
}

Effect.Grow = function(element) {
  element = $(element);
  var options = Object.extend({
    direction: 'center',
    moveTransition: Effect.Transitions.sinoidal,
    scaleTransition: Effect.Transitions.sinoidal,
    opacityTransition: Effect.Transitions.full
  }, arguments[1] || {});
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
}

Effect.Shrink = function(element) {
  element = $(element);
  var options = Object.extend({
    direction: 'center',
    moveTransition: Effect.Transitions.sinoidal,
    scaleTransition: Effect.Transitions.sinoidal,
    opacityTransition: Effect.Transitions.none
  }, arguments[1] || {});
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
}

Effect.Pulsate = function(element) {
  element = $(element);
  var options    = arguments[1] || {};
  var oldOpacity = element.getInlineOpacity();
  var transition = options.transition || Effect.Transitions.sinoidal;
  var reverser   = function(pos){ return transition(1-Effect.Transitions.pulse(pos, options.pulses)) };
  reverser.bind(transition);
  return new Effect.Opacity(element, 
    Object.extend(Object.extend({  duration: 2.0, from: 0,
      afterFinishInternal: function(effect) { effect.element.setStyle({opacity: oldOpacity}); }
    }, options), {transition: reverser}));
}

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
  }}, arguments[1] || {}));
};

Effect.Unfold = function(element) {
  element = $(element);
  var elementDimensions = element.getDimensions();
  var oldStyle = {
    top: element.style.top,
    left: element.style.left,
    width: element.style.width,
    height: element.style.height };
  element.makeClipping();
  return new Effect.Scale(element, 100, Object.extend({   
    scaleContent: false,
    scaleY: false,
    scaleFrom: 0,
    scaleMode: {originalHeight: elementDimensions.height, originalWidth: elementDimensions.width},
    afterSetup: function(effect) {
      effect.element.makeClipping().setStyle({width: '1px', height: '1px'}).show();
    },
    afterFinishInternal: function(effect) {
    new Effect.Scale(element, 100, {
      scaleContent: false, 
      scaleX: false,
      scaleFrom: 5,
      scaleMode: {originalHeight: elementDimensions.height, originalWidth: elementDimensions.width},
          restoreAfterFinish: true,
        afterSetup: function(effect) {
            effect.element.makeClipping().setStyle({height: '1px'}).show(); 
          },  
          afterFinishInternal: function(effect) {
            effect.element.undoClipping().setStyle(oldStyle);
          }
    });
  }}, arguments[1] || {}));
};

Effect.Morph = Class.create();
Object.extend(Object.extend(Effect.Morph.prototype, Effect.Base.prototype), {
  initialize: function(element) {
    this.element = $(element);
    if(!this.element) throw(Effect._elementDoesNotExistError);
    var options = Object.extend({
      style: {}
    }, arguments[1] || {});
    if (typeof options.style == 'string') {
      if(options.style.indexOf(':') == -1) {
        var cssText = '', selector = '.' + options.style;
        $A(document.styleSheets).reverse().each(function(styleSheet) {
          if (styleSheet.cssRules) cssRules = styleSheet.cssRules;
          else if (styleSheet.rules) cssRules = styleSheet.rules;
          $A(cssRules).reverse().each(function(rule) {
            if (selector == rule.selectorText) {
              cssText = rule.style.cssText;
              throw $break;
            }
          });
          if (cssText) throw $break;
        });
        this.style = cssText.parseStyle();
        options.afterFinishInternal = function(effect){
          effect.element.addClassName(effect.options.style);
          effect.transforms.each(function(transform) {
            if(transform.style != 'opacity')
              effect.element.style[transform.style.camelize()] = '';
          });
        }
      } else this.style = options.style.parseStyle();
    } else this.style = $H(options.style)
    this.start(options);
  },
  setup: function(){
    function parseColor(color){
      if(!color || ['rgba(0, 0, 0, 0)','transparent'].include(color)) color = '#ffffff';
      color = color.parseColor();
      return $R(0,2).map(function(i){
        return parseInt( color.slice(i*2+1,i*2+3), 16 ) 
      });
    }
    this.transforms = this.style.map(function(pair){
      var property = pair[0].underscore().dasherize(), value = pair[1], unit = null;

      if(value.parseColor('#zzzzzz') != '#zzzzzz') {
        value = value.parseColor();
        unit  = 'color';
      } else if(property == 'opacity') {
        value = parseFloat(value);
        if(/MSIE/.test(navigator.userAgent) && !window.opera && (!this.element.currentStyle.hasLayout))
          this.element.setStyle({zoom: 1});
      } else if(Element.CSS_LENGTH.test(value)) 
        var components = value.match(/^([\+\-]?[0-9\.]+)(.*)$/),
          value = parseFloat(components[1]), unit = (components.length == 3) ? components[2] : null;

      var originalValue = this.element.getStyle(property);
      return $H({ 
        style: property, 
        originalValue: unit=='color' ? parseColor(originalValue) : parseFloat(originalValue || 0), 
        targetValue: unit=='color' ? parseColor(value) : value,
        unit: unit
      });
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
    var style = $H(), value = null;
    this.transforms.each(function(transform){
      value = transform.unit=='color' ?
        $R(0,2).inject('#',function(m,v,i){
          return m+(Math.round(transform.originalValue[i]+
            (transform.targetValue[i] - transform.originalValue[i])*position)).toColorPart() }) : 
        transform.originalValue + Math.round(
          ((transform.targetValue - transform.originalValue) * position) * 1000)/1000 + transform.unit;
      style[transform.style] = value;
    });
    this.element.setStyle(style);
  }
});

Effect.Transform = Class.create();
Object.extend(Effect.Transform.prototype, {
  initialize: function(tracks){
    this.tracks  = [];
    this.options = arguments[1] || {};
    this.addTracks(tracks);
  },
  addTracks: function(tracks){
    tracks.each(function(track){
      var data = $H(track).values().first();
      this.tracks.push($H({
        ids:     $H(track).keys().first(),
        effect:  Effect.Morph,
        options: { style: data }
      }));
    }.bind(this));
    return this;
  },
  play: function(){
    return new Effect.Parallel(
      this.tracks.map(function(track){
        var elements = [$(track.ids) || $$(track.ids)].flatten();
        return elements.map(function(e){ return new track.effect(e, Object.extend({ sync:true }, track.options)) });
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

String.prototype.parseStyle = function(){
  var element = Element.extend(document.createElement('div'));
  element.innerHTML = '<div style="' + this + '"></div>';
  var style = element.down().style, styleRules = $H();
  
  Element.CSS_PROPERTIES.each(function(property){
    if(style[property]) styleRules[property] = style[property]; 
  });
  if(/MSIE/.test(navigator.userAgent) && !window.opera && this.indexOf('opacity') > -1) {
    styleRules.opacity = this.match(/opacity:\s*((?:0|1)?(?:\.\d*)?)/)[1];
  }
  return styleRules;
};

Element.morph = function(element, style) {
  new Effect.Morph(element, Object.extend({ style: style }, arguments[2] || {}));
  return element;
};

['setOpacity','getOpacity','getInlineOpacity','forceRerendering','setContentZoom',
 'collectTextNodes','collectTextNodesIgnoreClass','morph'].each( 
  function(f) { Element.Methods[f] = Element[f]; }
);

Element.Methods.visualEffect = function(element, effect, options) {
  s = effect.gsub(/_/, '-').camelize();
  effect_class = s.charAt(0).toUpperCase() + s.substring(1);
  new Effect[effect_class](element, options);
  return $(element);
};

Element.addMethods();

// treeview.js
// under GPL-License

/***************************************************************
*  Copyright notice
*
*  (c) 2003-2004 Jean-Michel Garnier (garnierjm@yahoo.fr)
*  All rights reserved
*
*  This script is part of the phpXplorer project. The phpXplorer 
project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt distributed with these 
scripts.
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
	if (OB_bd.isKonqueror || OB_bd.isSafari) {
		alert(gLanguage.getMessage('KS_NOT_SUPPORTED'));
		return;
	}
	
	if (OB_bd.isGeckoOrOpera) {
		this.OB_xsltProcessor_gecko = new XSLTProcessor();

  	// Load the xsl file using synchronous (third param is set to false) XMLHttpRequest
  	var myXMLHTTPRequest = new XMLHttpRequest();
  	myXMLHTTPRequest.open("GET", wgServer + wgScriptPath + "/extensions/SMWHalo/skins/OntologyBrowser/treeview.xslt", false);
 		myXMLHTTPRequest.send(null);


  	var xslRef = myXMLHTTPRequest.responseXML;
 
  	// Finally import the .xsl
  	this.OB_xsltProcessor_gecko.importStylesheet(xslRef);
  	this.OB_xsltProcessor_gecko.setParameter(null, "param-img-directory", wgServer + wgScriptPath + "/extensions/SMWHalo/skins/OntologyBrowser/images/");
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
  		if (OB_bd.isGeckoOrOpera) {
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
	if (OB_bd.isGeckoOrOpera) {
		// set startDepth parameter. start on root level or below?
 		this.OB_xsltProcessor_gecko.setParameter(null, "startDepth", level ? 1 : 2);
 		
 		// transform, remove all existing and add new generated nodes
  	 	var fragment = this.OB_xsltProcessor_gecko.transformToFragment(xmlDoc, document);
  	 	GeneralXMLTools.removeAllChildNodes(node); 
  	 	node.appendChild(fragment);
  	
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
      node.innerHTML = this.OB_xsltProcessor_ie.output;
  }
}
};

 
// one global tree transformer
var transformer = new TreeTransformer();



// ---------------------------------------------------------------------------











// treeviewActions.js
// under GPL-License
/*
 * TreeView actions 
 * Author: KK
 * Ontoprise 2007
 * 
 * One listener object for each type entity in each container.
 */

/**
 * Global selection (node)
 */
var OB_oldSelectedCategoryNode = null;
var OB_oldSelectedInstanceNode = null;
var OB_oldSelectedAttributeNode = null;
var OB_oldSelectedRelationNode = null;

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
	 * Togges a tree node expansion.
	 * event:
	 * node:
	 * folderCode:
	 * tree: cached tree to update
	 * accessFunc: Function which returns children needed for expansion.
	 */
	_toggleExpand: function (event, node, folderCode, tree, accessFunc) {
	
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
	  		var subTree = transformer.transformResultToHTML(request,nextDIV);
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
 	
  _selectNextPartition: function (e, htmlNode, tree, accessFunc, treeName, calledOnFinish) {
	
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
			// nextSibling is DIV element
			htmlNodeToReplace = document.getElementById(idOfChildrenToReplace).nextSibling
			
			// adjust XML structure
			GeneralXMLTools.removeAllChildNodes(parentOfChildrenToReplaceInCache);
			GeneralXMLTools.importSubtree(parentOfChildrenToReplaceInCache, xmlFragmentForCache.firstChild);
			
			GeneralXMLTools.removeAllChildNodes(parentOfChildrenToReplace);
			GeneralXMLTools.importSubtree(parentOfChildrenToReplace, xmlFragmentForDisplayTree.firstChild);
		}
		// transform structure to HTML
		transformer.transformXMLToHTML(xmlFragmentForDisplayTree, htmlNodeToReplace, isRootLevel);
		calledOnFinish(tree);
	}		
	// Identify partition node in XML
	var id = htmlNode.getAttribute("id");
	var partition = htmlNode.getAttribute("partitionnum");
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
	
	_selectPreviousPartition: function (e, htmlNode, tree, accessFunc, treeName, calledOnFinish) {
	
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
			htmlNodeToReplace = document.getElementById(idOfChildrenToReplace).nextSibling
			
			// adjust XML structure
			GeneralXMLTools.removeAllChildNodes(parentOfChildrenToReplaceInCache);
			GeneralXMLTools.importSubtree(parentOfChildrenToReplaceInCache, xmlFragmentForCache.firstChild);
			
			GeneralXMLTools.removeAllChildNodes(parentOfChildrenToReplace);
			GeneralXMLTools.importSubtree(parentOfChildrenToReplace, xmlFragmentForDisplayTree.firstChild);
		}
		// transform structure to HTML
		transformer.transformXMLToHTML(xmlFragmentForDisplayTree, htmlNodeToReplace, isRootLevel);
		calledOnFinish(tree);
	}	
	// Identify partition node in XML
	var id = htmlNode.getAttribute("id");
	var partition = htmlNode.getAttribute("partitionnum");
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
  
  /*
 	* Filter categories which matches a given term
 	*/
  _filterTree: function (e, tree, treeName, catFilter) {
    var xmlDoc = GeneralXMLTools.createTreeViewDocument();
   
   	var nodesFound = new Array();
   	
   	// generate filters
   	var regex = new Array();
    var filterTerms = GeneralTools.splitSearchTerm(catFilter);
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
   	transformer.transformXMLToHTML(xmlDoc, rootElement, true);
   	dataAccess.OB_currentlyDisplayedTree = xmlDoc;
},

  
  _filterTree_: function (nodesFound, node, count, regex) {

	var children = node.childNodes;
	
	if (children) {
   	  for (var i = 0; i < children.length; i++) {
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
		
		this.OB_currentlySelectedCategory = null;
	},
	
	toggleExpand: function (event, node, folderCode) {
		this._toggleExpand(event, node, folderCode, dataAccess.OB_cachedCategoryTree, dataAccess.getCategorySubTree.bind(dataAccess));
	},


	navigateToEntity: function(event, node, categoryName) {
		smwhgLogger.log(categoryName, "OB","inspect_entity");
		GeneralBrowserTools.navigateToPage(gLanguage.getMessage('CATEGORY_NS_WOC'), categoryName);
	},
// ---- Selection methods. Called when the entity is selected ---------------------

select: function (event, node, categoryID, categoryName) {
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
	// check if node is already expanded and expand it if not
	if (!nextDIV.hasChildNodes() || nextDIV.style.display == 'none') {
		this.toggleExpand(event, node, categoryID);
	}
		
	var instanceDIV = document.getElementById("instanceList");
	var relattDIV = document.getElementById("relattributes");
	OB_oldSelectedCategoryNode = GeneralBrowserTools.toggleHighlighting(OB_oldSelectedCategoryNode, node);
	
	// adjust relatt table headings
	if (!$("relattRangeType").visible()) {
		$("relattRangeType").show();
		$("relattValues").hide();
	}
	
	smwhgLogger.log(categoryName, "OB","clicked");
	
	function callbackOnCategorySelect(request) {
		OB_instance_pendingIndicator.hide();
	  	if (instanceDIV.firstChild) {
	  			GeneralBrowserTools.purge(instanceDIV.firstChild);
				instanceDIV.removeChild(instanceDIV.firstChild);
		}
		
		var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
		dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
	  	transformer.transformResultToHTML(request,instanceDIV, true);
	 }
	 function callbackOnCategorySelect2(request) {
	 	OB_relatt_pendingIndicator.hide();
	  	if (relattDIV.firstChild) {
	  			GeneralBrowserTools.purge(relattDIV.firstChild);
				relattDIV.removeChild(relattDIV.firstChild);
		}
		var xmlFragmentPropertyList = GeneralXMLTools.createDocumentFromString(request.responseText);
		dataAccess.OB_cachedProperties = xmlFragmentPropertyList;
	  	transformer.transformResultToHTML(request,relattDIV);
	 }
	 this.OB_currentlySelectedCategory = categoryName;
	 
	 if (OB_LEFT_ARROW == 0) {
	 	OB_instance_pendingIndicator.show();
	 	dataAccess.getInstances(categoryName, 0, callbackOnCategorySelect);
	 }
	 if (OB_RIGHT_ARROW == 0) {
	 	OB_relatt_pendingIndicator.show();
	 	dataAccess.getProperties(categoryName, callbackOnCategorySelect2);
	 }
	
	}
},


selectNextPartition: function (e, htmlNode) {
	
	function calledOnFinish(tree) {
		dataAccess.OB_cachedCategoryTree = tree;
	}
	this._selectNextPartition(e, htmlNode, dataAccess.OB_cachedCategoryTree, dataAccess.getCategoryPartition.bind(dataAccess), "categoryTree", calledOnFinish);
	
},

selectPreviousPartition: function (e, htmlNode) {
	
	function calledOnFinish(tree) {
		dataAccess.OB_cachedCategoryTree = tree;
	}
	this._selectPreviousPartition(e, htmlNode, dataAccess.OB_cachedCategoryTree, dataAccess.getCategoryPartition.bind(dataAccess), "categoryTree", calledOnFinish);

}



});


var OBInstanceActionListener = Class.create();
OBInstanceActionListener.prototype = {
	initialize: function() {
		//empty
		
	},
	
	navigateToInstance: function(event, node, instanceName) {
		smwhgLogger.log(instanceName, "OB","inspect_entity");
		GeneralBrowserTools.navigateToPage(null, instanceName);
		
	},
	
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
	 sajax_do_call('smwfOntologyBrowserAccess', ['filterBrowse',"category,"+categoryName], filterBrowsingCategoryCallback);
   	
	},
	
	selectInstance: function (event, node, instanceName) {
	
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
		OB_oldSelectedInstanceNode = GeneralBrowserTools.toggleHighlighting(OB_oldSelectedInstanceNode, node);
		
		smwhgLogger.log(instanceName, "OB","clicked");
		
		function callbackOnInstanceSelectToRight(request) {
		OB_relatt_pendingIndicator.hide();
	  	if (relattDIV.firstChild) {
	  		  	GeneralBrowserTools.purge(relattDIV.firstChild);
				relattDIV.removeChild(relattDIV.firstChild);
			}
			var xmlFragmentPropertyList = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_cachedProperties = xmlFragmentPropertyList;
	  		transformer.transformResultToHTML(request,relattDIV);
	  		if (OB_bd.isGecko) {
	  			// FF needs repasting for chemical formulas and equations because FF's XSLT processor does not know 'disable-output-encoding' switch. IE does.
	  			// thus, repaste markup on all elements marked with a 'chemFoEq' attribute
	  			GeneralBrowserTools.repasteMarkup("chemFoEq");
	  		}
	  	}
	  	
	  	function callbackOnInstanceSelectToLeft (request) {
	  		OB_tree_pendingIndicator.hide();
	  		if (categoryDIV.firstChild) {
	  		  	GeneralBrowserTools.purge(categoryDIV.firstChild);
				categoryDIV.removeChild(categoryDIV.firstChild);
			}
			dataAccess.OB_cachedCategoryTree = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(request.responseText, categoryDIV);
	  	}
	  	
	  	
	  	
	  	if (OB_RIGHT_ARROW == 0) {
	  		OB_relatt_pendingIndicator.show();
		 	sajax_do_call('smwfOntologyBrowserAccess', ['getAnnotations',instanceName], callbackOnInstanceSelectToRight);
	  	} 
	  	if (OB_LEFT_ARROW == 1) {
	  		OB_tree_pendingIndicator.show();
	  		sajax_do_call('smwfOntologyBrowserAccess', ['getCategoryForInstance',instanceName], callbackOnInstanceSelectToLeft);
	  	}
	
		}
	},
	
	selectNextPartition: function (e, htmlNode) {
			
		var partition = htmlNode.getAttribute("partitionnum");
		partition++;
		OB_instance_pendingIndicator.show();
		if (globalActionListener.activeTreeName == 'categoryTree') {
			dataAccess.getInstances(categoryActionListener.OB_currentlySelectedCategory, partition, this.selectPartitionCallback.bind(this));
		} else if (globalActionListener.activeTreeName == 'propertyTree') {
			dataAccess.getInstancesUsingProperty(propertyActionListener.OB_currentlySelectedAttribute, partition, this.selectPartitionCallback.bind(this));
		} else { // relation tree
			dataAccess.getInstancesUsingProperty(relationActionListener.OB_currentlySelectedRelation, partition, this.selectPartitionCallback.bind(this));
		}
	},
	
	selectPreviousPartition: function (e, htmlNode) {
		
		var partition = htmlNode.getAttribute("partitionnum");
		partition--;
		OB_instance_pendingIndicator.show();
		if (globalActionListener.activeTreeName == 'categoryTree') {
			dataAccess.getInstances(categoryActionListener.OB_currentlySelectedCategory, partition, this.selectPartitionCallback.bind(this));
		} else if (globalActionListener.activeTreeName == 'propertyTree') {
			dataAccess.getInstancesUsingProperty(propertyActionListener.OB_currentlySelectedAttribute, partition, this.selectPartitionCallback.bind(this));
		} else { // relation tree
			dataAccess.getInstancesUsingProperty(relationActionListener.OB_currentlySelectedRelation, partition, this.selectPartitionCallback.bind(this));
		}
	},
	
	selectPartitionCallback: function (request) {
			OB_instance_pendingIndicator.hide();
			var instanceListNode = $("instanceList");			
			GeneralXMLTools.removeAllChildNodes(instanceListNode);
			var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
			transformer.transformXMLToHTML(xmlFragmentInstanceList, instanceListNode, true);
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
			new Effect.Unfold('instanceContainer');
			$("hideInstancesButton").innerHTML = gLanguage.getMessage('HIDE_INSTANCES');
			new Effect.Unfold($("leftArrow"));
		}
	}
	
}

/**
 * Action Listener for attributes in the attribute tree
 */
var OBPropertyTreeActionListener = Class.create();
OBPropertyTreeActionListener.prototype = Object.extend(new OBTreeActionListener() , {
  initialize: function() {
		
		this.OB_currentlySelectedAttribute = null;
	},
	
	navigateToEntity: function(event, node, attributeName) {
		smwhgLogger.log(attributeName, "OB","inspect_entity");
		GeneralBrowserTools.navigateToPage(gLanguage.getMessage('PROPERTY_NS_WOC'), attributeName);
	},
	
  select: function (event, node, attributeID, attributeName) {
  			var e = GeneralTools.getEvent(event);
	
		// if Ctrl is pressed: navigation mode
		if (e["ctrlKey"]) {
			GeneralBrowserTools.navigateToPage(gLanguage.getMessage('PROPERTY_NS_WOC'), attributeName);
		} else {
		
		var nextDIV = node.nextSibling;
	
 		// find the next DIV
		while(nextDIV.nodeName != "DIV") {
		nextDIV = nextDIV.nextSibling;
		}
		// check if node is already expanded and expand it if not
		if (!nextDIV.hasChildNodes()  || nextDIV.style.display == 'none') {
			this.toggleExpand(event, node, attributeID);
		}
		
		var instanceDIV = document.getElementById("instanceList");
		
		OB_oldSelectedAttributeNode = GeneralBrowserTools.toggleHighlighting(OB_oldSelectedAttributeNode, node);
	
		smwhgLogger.log(attributeName, "OB","clicked");	
	
		function callbackOnPropertySelect(request) {
			OB_instance_pendingIndicator.hide();
	  		if (instanceDIV.firstChild) {
	  			 	GeneralBrowserTools.purge(instanceDIV.firstChild);
					instanceDIV.removeChild(instanceDIV.firstChild);
			}
			var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
	 	 	transformer.transformResultToHTML(request,instanceDIV, true);
		}
	     OB_instance_pendingIndicator.show();
	 	 this.OB_currentlySelectedAttribute = attributeName;
	 	 dataAccess.getInstancesUsingProperty(attributeName, 0, callbackOnPropertySelect);
		}
	},
	
  toggleExpand: function (event, node, folderCode) {
  	this._toggleExpand(event, node, folderCode, dataAccess.OB_cachedPropertyTree, dataAccess.getPropertySubTree.bind(dataAccess));
  },
  selectNextPartition: function (e, htmlNode) {
	function calledOnFinish(tree) {
			dataAccess.OB_cachedPropertyTree = tree;
		}
		this._selectNextPartition(e, htmlNode, dataAccess.OB_cachedPropertyTree, dataAccess.getPropertyPartition.bind(dataAccess), "propertyTree", calledOnFinish);

	},

	selectPreviousPartition: function (e, htmlNode) {
	 function calledOnFinish(tree) {
			dataAccess.OB_cachedPropertyTree = tree;
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
	
	selectAttribute: function(event, node, attributeName) {
		// delegate to schemaPropertyListener
		schemaActionPropertyListener.selectAttribute(event, node, attributeName);
	},
	selectRelation: function(event, node, relationName) {
		// delegate to schemaPropertyListener
		schemaActionPropertyListener.selectRelation(event, node, relationName);
	}
}

/**
 * Action Listener for schema properties, i.e. attributes and relations
 * on schema level
 */
var OBSchemaPropertyActionListener = Class.create();
OBSchemaPropertyActionListener.prototype = {
	initialize: function() {
		//empty
		
	},
	
	navigateToAttribute: function(event, node, attributeName) {
		smwhgLogger.log(attributeName, "OB","inspect_entity");
		GeneralBrowserTools.navigateToPage(gLanguage.getMessage('PROPERTY_NS_WOC'), attributeName);
	},
	
	navigateToRelation: function(event, node, relationName) {
		smwhgLogger.log(relationName, "OB","inspect_entity");
		GeneralBrowserTools.navigateToPage(gLanguage.getMessage('PROPERTY_NS_WOC'), relationName);
	},
	
	selectAttribute: function(event, node, attributeName) {
		var categoryDIV = $("categoryTree");
		var instanceDIV = $("instanceList");
		
		OB_oldSelectedAttributeNode = GeneralBrowserTools.toggleHighlighting(OB_oldSelectedAttributeNode, node);
		
		smwhgLogger.log(attributeName, "OB","clicked");	
		
		function callbackOnPropertySelectForCategory (request) {
			OB_tree_pendingIndicator.hide();
	  		if (categoryDIV.firstChild) {
	  		  	GeneralBrowserTools.purge(categoryDIV.firstChild);
				categoryDIV.removeChild(categoryDIV.firstChild);
			}
			dataAccess.OB_cachedCategoryTree = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(request.responseText, categoryDIV);
	  	}
	  	
	  	function callbackOnPropertySelectForInstance (request) {
	  		OB_instance_pendingIndicator.hide();
	  		if (instanceDIV.firstChild) {
	  			GeneralBrowserTools.purge(instanceDIV.firstChild);
				instanceDIV.removeChild(instanceDIV.firstChild);
			}
		
			var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
	  		transformer.transformResultToHTML(request,instanceDIV, true);
	  	}
		// if Ctrl is pressed: navigation mode
		if (event["ctrlKey"]) {
			GeneralBrowserTools.navigateToPage(gLanguage.getMessage('PROPERTY_NS_WOC'), attributeName);
		} else {
			if (OB_LEFT_ARROW == 1) {
				OB_tree_pendingIndicator.show();
				sajax_do_call('smwfOntologyBrowserAccess', ['getCategoryForProperty',attributeName], callbackOnPropertySelectForCategory);
			}
			if (OB_RIGHT_ARROW == 1) {
				 OB_instance_pendingIndicator.show();
				 this.OB_currentlySelectedAttribute = attributeName;
				 dataAccess.getInstancesUsingProperty(attributeName, 0, callbackOnPropertySelectForInstance);
			}
		}
	},
	selectRelation: function(event, node, relationName) {
	
		var categoryDIV = $("categoryTree");
		var instanceDIV = $("instanceList");
		
		OB_oldSelectedRelationNode = GeneralBrowserTools.toggleHighlighting(OB_oldSelectedRelationNode, node);
		
		smwhgLogger.log(relationName, "OB","clicked");	
		
		function callbackOnPropertySelectForCategory (request) {
			OB_tree_pendingIndicator.hide();
	  		if (categoryDIV.firstChild) {
	  		  	GeneralBrowserTools.purge(categoryDIV.firstChild);
				categoryDIV.removeChild(categoryDIV.firstChild);
			}
			dataAccess.OB_cachedCategoryTree = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(request.responseText, categoryDIV);
	  	}
	  	
	  	function callbackOnPropertySelectForInstance (request) {
	  		OB_instance_pendingIndicator.hide();
	  		if (instanceDIV.firstChild) {
	  			GeneralBrowserTools.purge(instanceDIV.firstChild);
				instanceDIV.removeChild(instanceDIV.firstChild);
			}
		
			var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
	  		transformer.transformResultToHTML(request,instanceDIV, true);
	  	}
		// if Ctrl is pressed: navigation mode
		if (event["ctrlKey"]) {
			GeneralBrowserTools.navigateToPage(gLanguage.getMessage('RELATION_NS_WOC'), relationName);
		} else {
			if (OB_LEFT_ARROW == 1) {
				OB_tree_pendingIndicator.show();
				sajax_do_call('smwfOntologyBrowserAccess', ['getCategoryForProperty',relationName, gLanguage.getMessage('RELATION_NS_WOC')], callbackOnPropertySelectForCategory);
			}
			if (OB_RIGHT_ARROW == 1) {
				OB_instance_pendingIndicator.show();
				this.OB_currentlySelectedRelation = relationName;
			 	dataAccess.getInstancesUsingProperty(relationName, 0, callbackOnPropertySelectForInstance);
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
		var inputs = document.getElementsByTagName("input");
		new Form.Element.Observer(inputs[1], 0.5, this.filterTree.bindAsEventListener(this));
		new Form.Element.Observer(inputs[2], 0.5, this.filterInstances.bindAsEventListener(this));
		new Form.Element.Observer(inputs[3], 0.5, this.filterProperties.bindAsEventListener(this));
		
		// make sure that OntologyBrowser Filter search gets focus if a key is pressed
		Event.observe(document, 'keydown', function(event) { 
			if (event.target.id == 'searchInput') return;
			if (event.target.parentNode != document && $(event.target.parentNode).hasClassName('OB-filters')) return;
			$('FilterBrowserInput').focus() 
		});
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
			
		} else if ($("propertyTree").visible() && showWhichTree != 'propertyTree') {
			$("propertyTree").hide();
			$(showWhichTree).show();
			$(showWhichTree+"Switch").addClassName("selectedSwitch");
			$("propertyTreeSwitch").removeClassName("selectedSwitch");
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
	 * Global filter event listener
	 */
	filterTree: function(event) {
		
		var inputs = document.getElementsByTagName("input");
		var filter = inputs[1].value;
		var tree;
		var actionListener;
		if (this.activeTreeName == 'categoryTree') {
			actionListener = categoryActionListener;
			tree = dataAccess.OB_cachedCategoryTree;
			if (filter == "") { //special case empty filter, just copy
				dataAccess.initializeRootCategories(0);
				transformer.transformXMLToHTML(dataAccess.OB_currentlyDisplayedTree, $(this.activeTreeName), true);
				return;
			}	
		} else if (this.activeTreeName == 'propertyTree') {
			actionListener = propertyActionListener;	
			tree = dataAccess.OB_cachedPropertyTree;
			if (filter == "") {
				dataAccess.initializeRootProperties(0);
				transformer.transformXMLToHTML(dataAccess.OB_currentlyDisplayedTree, $(this.activeTreeName), true);
				return;
			}
		}  
		
		actionListener._filterTree(event, tree, this.activeTreeName, filter);
		
		
	},
	
	filterInstances: function(event) {
		if (dataAccess.OB_cachedInstances == null) {
			return;
		}
		var inputs = document.getElementsByTagName("input");
		var filter = inputs[2].value;
		
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
		transformer.transformXMLToHTML(nodesFound, $("instanceList"), true); 
	},
	
	filterProperties: function(event) {
		if (dataAccess.OB_cachedProperties == null) {
			return;
		}
		var inputs = document.getElementsByTagName("input");
		var filter = inputs[3].value;
		
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
		transformer.transformXMLToHTML(nodesFound, $("relattributes"), true); 
		GeneralBrowserTools.repasteMarkup("chemFoEq");
	},
	
	/**
	 * Global filter event listener
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
	  	transformer.transformResultToHTML(request,instanceDIV, true);
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
	  	transformer.transformResultToHTML(request,propertyDIV, true);
	 }
	 
	 if (!force && event["keyCode"] != 13 ) {
	 	return;
	 }
	 var inputs = document.getElementsByTagName("input");
	 var hint = inputs[0].value;
	 
	 if (hint.length <= 1) {
	 	alert(gLanguage.getMessage('ENTER_MORE_LETTERS'));
	 	return;
	 }
	 if (this.activeTreeName == 'categoryTree') {
	 	 OB_tree_pendingIndicator.show(this.activeTreeName);
		 sajax_do_call('smwfOntologyBrowserAccess', ['filterBrowse',"category,"+hint], filterBrowsingCategoryCallback);
	 }  else if (this.activeTreeName == 'propertyTree') {
	 	 OB_tree_pendingIndicator.show(this.activeTreeName);
         sajax_do_call('smwfOntologyBrowserAccess', ['filterBrowse',"propertyTree,"+hint], filterBrowsingAttributeCallback);
	 } 
	  OB_instance_pendingIndicator.show();
	  OB_relatt_pendingIndicator.show();
	  sajax_do_call('smwfOntologyBrowserAccess', ['filterBrowse',"instance,"+hint], filterBrowsingInstanceCallback);	
	  sajax_do_call('smwfOntologyBrowserAccess', ['filterBrowse',"property,"+hint], filterBrowsingPropertyCallback);
	 
	},
	
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
// under GPL-License
/*
 * Treeview Data
 * Author: KK
 * Ontoprise 2007
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
		this.OB_cachedProperties = null;
		
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
  	transformer.transformXMLToHTML(this.OB_currentlyDisplayedTree, rootElement, true);
 
  	
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
  	transformer.transformXMLToHTML(this.OB_currentlyDisplayedTree, rootElement, true);
 
  	
},


updateTree: function(xmlText, rootElement) {
	var tree = GeneralXMLTools.createDocumentFromString(xmlText);
  	transformer.transformXMLToHTML(tree, rootElement, true);
  	return tree;
},

initializeRootCategories: function(partition, force) {
	if (!this.OB_categoriesInitialized || force) {
		OB_tree_pendingIndicator.show(globalActionListener.activeTreeName);
		sajax_do_call('smwfOntologyBrowserAccess', ['getRootCategories',OB_partitionSize+","+partition], this.initializeRootCategoriesCallback.bind(this));
	} else {
  		// copy from cache
  		this.OB_currentlyDisplayedTree = GeneralXMLTools.createDocumentFromString("<result/>");
  		GeneralXMLTools.importSubtree(this.OB_currentlyDisplayedTree.firstChild, this.OB_cachedCategoryTree.firstChild, true);
  } 	
},

initializeRootProperties: function(partition, force) {
	 if (!this.OB_attributesInitialized || force) {
	 	OB_tree_pendingIndicator.show(globalActionListener.activeTreeName);
		sajax_do_call('smwfOntologyBrowserAccess', ['getRootProperties',OB_partitionSize+","+partition], this.initializeRootPropertyCallback.bind(this));
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
	if (nodeToExpand != null && nodeToExpand.hasChildNodes()) {
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
	var nodeToExpand = GeneralXMLTools.getNodeById(this.OB_cachedCategoryTree, attributeID);
	if (nodeToExpand != null && nodeToExpand.hasChildNodes()) {
		// copy it from cache to displayed tree.
		var nodeInDisplayedTree = GeneralXMLTools.getNodeById(this.OB_currentlyDisplayedTree, attributeID);
		GeneralXMLTools.importSubtree(nodeInDisplayedTree, nodeToExpand);
		
		// create result dummy document and call 'callBackOnCache' to transform
		var subtree = GeneralXMLTools.createDocumentFromString("<result/>");
		GeneralXMLTools.importSubtree(subtree.firstChild, nodeToExpand);
		callBackOnCache(subtree);
	} else {
		// download it
		sajax_do_call('smwfOntologyBrowserAccess', ['getSubProperties',attributeName+","+OB_partitionSize+",0"],  callBackOnAjax);
	}
},



getInstances: function(categoryName, partition, callback) {
	sajax_do_call('smwfOntologyBrowserAccess', ['getInstance',categoryName+","+OB_partitionSize+","+partition], callback);
},

getProperties: function(categoryName, callback) {
	sajax_do_call('smwfOntologyBrowserAccess', ['getProperties',categoryName], callback);
},

getAnnotations: function(instanceName, callback) {
	sajax_do_call('smwfOntologyBrowserAccess', ['getAnnotations',instanceName], callback);
},

getCategoryPartition: function(isRootLevel, partition, categoryName, selectPartitionCallback) {
	if (isRootLevel) {
		// root level
		sajax_do_call('smwfOntologyBrowserAccess', ['getRootCategories',OB_partitionSize+','+partition],  selectPartitionCallback);
	} else {
		// every other level
		sajax_do_call('smwfOntologyBrowserAccess', ['getSubCategory',categoryName+","+OB_partitionSize+","+partition],  selectPartitionCallback);
	}
},

getPropertyPartition: function(isRootLevel, partition, attributeName, selectPartitionCallback) {
	if (isRootLevel) {
		// root level
		sajax_do_call('smwfOntologyBrowserAccess', ['getRootProperties',OB_partitionSize+','+partition],  selectPartitionCallback);
	} else {
		// every other level
		sajax_do_call('smwfOntologyBrowserAccess', ['getSubProperties',attributeName+","+OB_partitionSize+","+partition],  selectPartitionCallback);
	}
},



getInstancesUsingProperty: function(propertyName, partition, callback) {
	sajax_do_call('smwfOntologyBrowserAccess', ['getInstancesUsingProperty',propertyName+","+OB_partitionSize+","+partition], callback);
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
	sajax_do_call('smwfOntologyBrowserAccess', ['filterBrowse',"category,"+title], filterBrowsingCategoryCallback);
   	
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
			sajax_do_call('smwfOntologyBrowserAccess', ['getAnnotations',instance.getAttribute("title")], getAnnotationsCallback);
		}
		dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
	  	transformer.transformResultToHTML(request,instanceDIV, true);
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
	  	transformer.transformResultToHTML(request,relattDIV);
	  	if (OB_bd.isGecko) {
	  		// FF needs repasting for chemical formulas and equations because FF's XSLT processor does not know 'disable-output-encoding' switch. IE does.
	  		// thus, repaste markup on all elements marked with a 'chemFoEq' attribute
	  		GeneralBrowserTools.repasteMarkup("chemFoEq");
	  	}
	 }
	 
	 OB_instance_pendingIndicator.show();
	
   	 sajax_do_call('smwfOntologyBrowserAccess', ['filterBrowse',"instance,"+title], filterBrowsingInstanceCallback);	
   	
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
   	sajax_do_call('smwfOntologyBrowserAccess', ['filterBrowse',"propertyTree,"+title], filterBrowsingAttributeCallback);
}


};

