// prototype.js
/*  Prototype JavaScript framework, version 1.6.0_pre1
 *  (c) 2005-2007 Sam Stephenson
 *
 *  Prototype is freely distributable under the terms of an MIT-style license.
 *  For details, see the Prototype web site: http://www.prototypejs.org/
 *
 *--------------------------------------------------------------------------*/

var Prototype = {
  Version: '1.6.0_pre1',

  Browser: {
    IE:     !!(window.attachEvent && !window.opera),
    Opera:  !!window.opera,
    WebKit: navigator.userAgent.indexOf('AppleWebKit/') > -1,
    Gecko:  navigator.userAgent.indexOf('Gecko') > -1 && navigator.userAgent.indexOf('KHTML') == -1,
    MobileSafari: !!navigator.userAgent.match(/iPhone.*Mobile.*Safari/)
  },

  BrowserFeatures: {
    XPath: !!document.evaluate,
    ElementExtensions: !!window.HTMLElement,
    SpecificElementExtensions:
      document.createElement('div').__proto__ !==
       document.createElement('form').__proto__
  },

  ScriptFragment: '<script[^>]*>([\\S\\s]*?)<\/script>',
  JSONFilter: /^\/\*-secure-([\s\S]*)\*\/\s*$/,

  emptyFunction: function() { },
  K: function(x) { return x }
};

if (Prototype.Browser.MobileSafari)
  Prototype.BrowserFeatures.SpecificElementExtensions = false;

/* Based on Alex Arnell's inheritance implementation. */
var Class = {
  create: function(parent, methods) {
    if (arguments.length == 1 && !Object.isFunction(parent))
      methods = parent, parent = null;

    var method = function() {
      if (!Class.extending) this.initialize.apply(this, arguments);
    };

    method.superclass = parent;
    method.subclasses = [];

    if (Object.isFunction(parent)) {
      Class.extending = true;
      method.prototype = new parent();
      method.prototype.constructor = method;

      parent.subclasses.push(method);

      delete Class.extending;
    }

    if (methods) Class.extend(method, methods);

    return method;
  },

  extend: function(destination, source) {
    for (var name in source) Class.inherit(destination, source, name);
    return destination;
  },

  inherit: function(destination, source, name) {
    var prototype = destination.prototype, ancestor = prototype[name],
     descendant = source[name];
    if (ancestor && Object.isFunction(descendant) &&
        descendant.argumentNames().first() == "$super") {
      var method = descendant, descendant = ancestor.wrap(method);
      Object.extend(descendant, {
        valueOf:  function() { return method },
        toString: function() { return method.toString() }
      });
    }

    prototype[name] = descendant;

    if (destination.subclasses && destination.subclasses.length > 0) {
      for (var i = 0, subclass; subclass = destination.subclasses[i]; i++) {
        Class.extending = true;
        Object.extend(subclass.prototype, new destination());
        subclass.prototype.constructor = subclass;
        delete Class.extending;
        Class.inherit(subclass, destination.prototype, name);
      }
    }
  },

  mixin: function(destination, source) {
    return Object.extend(destination, source);
  }
};

var Abstract = { };

Object.extend = function(destination, source) {
  for (var property in source) {
    destination[property] = source[property];
  }
  return destination;
};

Object.extend(Object, {
  inspect: function(object) {
    try {
      if (object === undefined) return 'undefined';
      if (object === null) return 'null';
      return object.inspect ? object.inspect() : object.toString();
    } catch (e) {
      if (e instanceof RangeError) return '...';
      throw e;
    }
  },

  toJSON: function(object) {
    var type = typeof object;
    switch (type) {
      case 'undefined':
      case 'function':
      case 'unknown': return;
      case 'boolean': return object.toString();
    }

    if (object === null) return 'null';
    if (object.toJSON) return object.toJSON();
    if (Object.isElement(object)) return;

    var results = [];
    for (var property in object) {
      var value = Object.toJSON(object[property]);
      if (value !== undefined)
        results.push(property.toJSON() + ': ' + value);
    }

    return '{' + results.join(', ') + '}';
  },

  toHTML: function(object) {
    return object && object.toHTML ? object.toHTML() : String.interpret(object);
  },

  keys: function(object) {
    var keys = [];
    for (var property in object)
      keys.push(property);
    return keys;
  },

  values: function(object) {
    var values = [];
    for (var property in object)
      values.push(object[property]);
    return values;
  },

  clone: function(object) {
    return Object.extend({ }, object);
  },

  isElement: function(object) {
    return object && object.nodeType == 1;
  },

  isArray: function(object) {
    return object && object.constructor === Array;
  },

  isFunction: function(object) {
    return typeof object == "function";
  },

  isString: function(object) {
    return typeof object == "string";
  },

  isNumber: function(object) {
    return typeof object == "number";
  },

  isUndefined: function(object) {
    return typeof object == "undefined";
  }
});

Object.extend(Function.prototype, {
  argumentNames: function() {
    var names = this.toString().match(/^[\s\(]*function\s*\((.*?)\)/)[1].split(",").invoke("strip");
    return names.length == 1 && !names[0] ? [] : names;
  },

  bind: function() {
    if (arguments.length < 2 && arguments[0] === undefined) return this;
    var __method = this, args = $A(arguments), object = args.shift();
    return function() {
      return __method.apply(object, args.concat($A(arguments)));
    }
  },

  bindAsEventListener: function() {
    var __method = this, args = $A(arguments), object = args.shift();
    return function(event) {
      return __method.apply(object, [event || window.event].concat(args));
    }
  },

  curry: function() {
    if (!arguments.length) return this;
    var __method = this, args = $A(arguments);
    return function() {
      return __method.apply(this, args.concat($A(arguments)));
    }
  },

  delay: function() {
    var __method = this, args = $A(arguments), timeout = args.shift() * 1000;
    return window.setTimeout(function() {
      return __method.apply(__method, args);
    }, timeout);
  },

  wrap: function(wrapper) {
    var __method = this;
    return function() {
      return wrapper.apply(this, [__method.bind(this)].concat($A(arguments)));
    }
  },

  methodize: function() {
    if (this._methodized) return this._methodized;
    var __method = this;
    return this._methodized = function() {
      return __method.apply(null, [this].concat($A(arguments)));
    };
  }
});

Function.prototype.defer = Function.prototype.delay.curry(0.01);

Date.prototype.toJSON = function() {
  return '"' + this.getFullYear() + '-' +
    (this.getMonth() + 1).toPaddedString(2) + '-' +
    this.getDate().toPaddedString(2) + 'T' +
    this.getHours().toPaddedString(2) + ':' +
    this.getMinutes().toPaddedString(2) + ':' +
    this.getSeconds().toPaddedString(2) + '"';
};

var Try = {
  these: function() {
    var returnValue;

    for (var i = 0, length = arguments.length; i < length; i++) {
      var lambda = arguments[i];
      try {
        returnValue = lambda();
        break;
      } catch (e) { }
    }

    return returnValue;
  }
};

RegExp.prototype.match = RegExp.prototype.test;

RegExp.escape = function(str) {
  return String(str).replace(/([.*+?^=!:${}()|[\]\/\\])/g, '\\$1');
};

/*--------------------------------------------------------------------------*/

var PeriodicalExecuter = Class.create({
  initialize: function(callback, frequency) {
    this.callback = callback;
    this.frequency = frequency;
    this.currentlyExecuting = false;

    this.registerCallback();
  },

  registerCallback: function() {
    this.timer = setInterval(this.onTimerEvent.bind(this), this.frequency * 1000);
  },

  stop: function() {
    if (!this.timer) return;
    clearInterval(this.timer);
    this.timer = null;
  },

  onTimerEvent: function() {
    if (!this.currentlyExecuting) {
      try {
        this.currentlyExecuting = true;
        this.callback(this);
      } finally {
        this.currentlyExecuting = false;
      }
    }
  }
});
Object.extend(String, {
  interpret: function(value) {
    return value == null ? '' : String(value);
  },
  specialChar: {
    '\b': '\\b',
    '\t': '\\t',
    '\n': '\\n',
    '\f': '\\f',
    '\r': '\\r',
    '\\': '\\\\'
  }
});

Object.extend(String.prototype, {
  gsub: function(pattern, replacement) {
    var result = '', source = this, match;
    replacement = arguments.callee.prepareReplacement(replacement);

    while (source.length > 0) {
      if (match = source.match(pattern)) {
        result += source.slice(0, match.index);
        result += String.interpret(replacement(match));
        source  = source.slice(match.index + match[0].length);
      } else {
        result += source, source = '';
      }
    }
    return result;
  },

  sub: function(pattern, replacement, count) {
    replacement = this.gsub.prepareReplacement(replacement);
    count = count === undefined ? 1 : count;

    return this.gsub(pattern, function(match) {
      if (--count < 0) return match[0];
      return replacement(match);
    });
  },

  scan: function(pattern, iterator) {
    this.gsub(pattern, iterator);
    return String(this);
  },

  truncate: function(length, truncation) {
    length = length || 30;
    truncation = truncation === undefined ? '...' : truncation;
    return this.length > length ?
      this.slice(0, length - truncation.length) + truncation : String(this);
  },

  strip: function() {
    return this.replace(/^\s+/, '').replace(/\s+$/, '');
  },

  stripTags: function() {
    return this.replace(/<\/?[^>]+>/gi, '');
  },

  stripScripts: function() {
    return this.replace(new RegExp(Prototype.ScriptFragment, 'img'), '');
  },

  extractScripts: function() {
    var matchAll = new RegExp(Prototype.ScriptFragment, 'img');
    var matchOne = new RegExp(Prototype.ScriptFragment, 'im');
    return (this.match(matchAll) || []).map(function(scriptTag) {
      return (scriptTag.match(matchOne) || ['', ''])[1];
    });
  },

  evalScripts: function() {
    return this.extractScripts().map(function(script) { return eval(script) });
  },

  escapeHTML: function() {
    var self = arguments.callee;
    self.text.data = this;
    return self.div.innerHTML;
  },

  unescapeHTML: function() {
    var div = new Element('div');
    div.innerHTML = this.stripTags();
    return div.childNodes[0] ? (div.childNodes.length > 1 ?
      $A(div.childNodes).inject('', function(memo, node) { return memo+node.nodeValue }) :
      div.childNodes[0].nodeValue) : '';
  },

  toQueryParams: function(separator) {
    var match = this.strip().match(/([^?#]*)(#.*)?$/);
    if (!match) return { };

    return match[1].split(separator || '&').inject({ }, function(hash, pair) {
      if ((pair = pair.split('='))[0]) {
        var key = decodeURIComponent(pair.shift());
        var value = pair.length > 1 ? pair.join('=') : pair[0];
        if (value != undefined) value = decodeURIComponent(value);

        if (key in hash) {
          if (!Object.isArray(hash[key])) hash[key] = [hash[key]];
          hash[key].push(value);
        }
        else hash[key] = value;
      }
      return hash;
    });
  },

  toArray: function() {
    return this.split('');
  },

  succ: function() {
    return this.slice(0, this.length - 1) +
      String.fromCharCode(this.charCodeAt(this.length - 1) + 1);
  },

  times: function(count) {
    var result = '';
    for (var i = 0; i < count; i++) result += this;
    return result;
  },

  camelize: function() {
    var parts = this.split('-'), len = parts.length;
    if (len == 1) return parts[0];

    var camelized = this.charAt(0) == '-'
      ? parts[0].charAt(0).toUpperCase() + parts[0].substring(1)
      : parts[0];

    for (var i = 1; i < len; i++)
      camelized += parts[i].charAt(0).toUpperCase() + parts[i].substring(1);

    return camelized;
  },

  capitalize: function() {
    return this.charAt(0).toUpperCase() + this.substring(1).toLowerCase();
  },

  underscore: function() {
    return this.gsub(/::/, '/').gsub(/([A-Z]+)([A-Z][a-z])/,'#{1}_#{2}').gsub(/([a-z\d])([A-Z])/,'#{1}_#{2}').gsub(/-/,'_').toLowerCase();
  },

  dasherize: function() {
    return this.gsub(/_/,'-');
  },

  inspect: function(useDoubleQuotes) {
    var escapedString = this.gsub(/[\x00-\x1f\\]/, function(match) {
      var character = String.specialChar[match[0]];
      return character ? character : '\\u00' + match[0].charCodeAt().toPaddedString(2, 16);
    });
    if (useDoubleQuotes) return '"' + escapedString.replace(/"/g, '\\"') + '"';
    return "'" + escapedString.replace(/'/g, '\\\'') + "'";
  },

  toJSON: function() {
    return this.inspect(true);
  },

  unfilterJSON: function(filter) {
    return this.sub(filter || Prototype.JSONFilter, '#{1}');
  },

  isJSON: function() {
    var str = this.replace(/\\./g, '@').replace(/"[^"\\\n\r]*"/g, '');
    return (/^[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]*$/).test(str);
  },

  evalJSON: function(sanitize) {
    var json = this.unfilterJSON();
    try {
      if (!sanitize || json.isJSON()) return eval('(' + json + ')');
    } catch (e) { }
    throw new SyntaxError('Badly formed JSON string: ' + this.inspect());
  },

  include: function(pattern) {
    return this.indexOf(pattern) > -1;
  },

  startsWith: function(pattern) {
    return this.indexOf(pattern) === 0;
  },

  endsWith: function(pattern) {
    var d = this.length - pattern.length;
    return d >= 0 && this.lastIndexOf(pattern) === d;
  },

  empty: function() {
    return this == '';
  },

  blank: function() {
    return /^\s*$/.test(this);
  },

  interpolate: function(object, pattern) {
    return new Template(this, pattern).evaluate(object);
  }
});

if (Prototype.Browser.WebKit || Prototype.Browser.IE) Object.extend(String.prototype, {
  escapeHTML: function() {
    return this.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  },
  unescapeHTML: function() {
    return this.replace(/&amp;/g,'&').replace(/&lt;/g,'<').replace(/&gt;/g,'>');
  }
});

String.prototype.gsub.prepareReplacement = function(replacement) {
  if (Object.isFunction(replacement)) return replacement;
  var template = new Template(replacement);
  return function(match) { return template.evaluate(match) };
};

String.prototype.parseQuery = String.prototype.toQueryParams;

Object.extend(String.prototype.escapeHTML, {
  div:  document.createElement('div'),
  text: document.createTextNode('')
});

with (String.prototype.escapeHTML) div.appendChild(text);

var Template = Class.create();
Template.Pattern = /(^|.|\r|\n)(#\{(.*?)\})/;
Template.prototype = {
  initialize: function(template, pattern) {
    this.template = template.toString();
    this.pattern = pattern || Template.Pattern;
  },

  evaluate: function(object) {
    if (Object.isFunction(object.toTemplateReplacements))
      object = object.toTemplateReplacements();

    return this.template.gsub(this.pattern, function(match) {
      if (object == null) return '';

      var before = match[1] || '';
      if (before == '\\') return match[2];

      var ctx = object, expr = match[3];
      var pattern = /^([^.[]+|\[((?:.*?[^\\])?)\])(\.|\[|$)/, match = pattern.exec(expr);
      if (match == null) return '';

      while (match != null) {
        var comp = match[1].startsWith('[') ? match[2].gsub('\\\\]', ']') : match[1];
        ctx = ctx[comp];
        if (null == ctx || '' == match[3]) break;
        expr = expr.substring('[' == match[3] ? match[1].length : match[0].length);
        match = pattern.exec(expr);
      }

      return before + String.interpret(ctx);
    }.bind(this));
  }
};

var $break = { };

var Enumerable = {
  each: function(iterator, context) {
    var index = 0;
    iterator = iterator.bind(context);
    try {
      this._each(function(value) {
        iterator(value, index++);
      });
    } catch (e) {
      if (e != $break) throw e;
    }
    return this;
  },

  eachSlice: function(number, iterator, context) {
    iterator = iterator ? iterator.bind(context) : Prototype.K;
    var index = -number, slices = [], array = this.toArray();
    while ((index += number) < array.length)
      slices.push(array.slice(index, index+number));
    return slices.collect(iterator, context);
  },

  all: function(iterator, context) {
    iterator = iterator ? iterator.bind(context) : Prototype.K;
    var result = true;
    this.each(function(value, index) {
      result = result && !!iterator(value, index);
      if (!result) throw $break;
    });
    return result;
  },

  any: function(iterator, context) {
    iterator = iterator ? iterator.bind(context) : Prototype.K;
    var result = false;
    this.each(function(value, index) {
      if (result = !!iterator(value, index))
        throw $break;
    });
    return result;
  },

  collect: function(iterator, context) {
    iterator = iterator ? iterator.bind(context) : Prototype.K;
    var results = [];
    this.each(function(value, index) {
      results.push(iterator(value, index));
    });
    return results;
  },

  detect: function(iterator, context) {
    iterator = iterator.bind(context);
    var result;
    this.each(function(value, index) {
      if (iterator(value, index)) {
        result = value;
        throw $break;
      }
    });
    return result;
  },

  findAll: function(iterator, context) {
    iterator = iterator.bind(context);
    var results = [];
    this.each(function(value, index) {
      if (iterator(value, index))
        results.push(value);
    });
    return results;
  },

  grep: function(filter, iterator, context) {
    iterator = iterator ? iterator.bind(context) : Prototype.K;
    var results = [];

    if (Object.isString(filter))
      filter = new RegExp(filter);

    this.each(function(value, index) {
      if (filter.match(value))
        results.push(iterator(value, index));
    });
    return results;
  },

  include: function(object) {
    if (Object.isFunction(this.indexOf))
      return this.indexOf(object) != -1;

    var found = false;
    this.each(function(value) {
      if (value === object) {
        found = true;
        throw $break;
      }
    });
    return found;
  },

  inGroupsOf: function(number, fillWith) {
    fillWith = fillWith === undefined ? null : fillWith;
    return this.eachSlice(number, function(slice) {
      while(slice.length < number) slice.push(fillWith);
      return slice;
    });
  },

  inject: function(memo, iterator, context) {
    iterator = iterator.bind(context);
    this.each(function(value, index) {
      memo = iterator(memo, value, index);
    });
    return memo;
  },

  invoke: function(method) {
    var args = $A(arguments).slice(1);
    return this.map(function(value) {
      return value[method].apply(value, args);
    });
  },

  max: function(iterator, context) {
    iterator = iterator ? iterator.bind(context) : Prototype.K;
    var result;
    this.each(function(value, index) {
      value = iterator(value, index);
      if (result == undefined || value >= result)
        result = value;
    });
    return result;
  },

  min: function(iterator, context) {
    iterator = iterator ? iterator.bind(context) : Prototype.K;
    var result;
    this.each(function(value, index) {
      value = iterator(value, index);
      if (result == undefined || value < result)
        result = value;
    });
    return result;
  },

  partition: function(iterator, context) {
    iterator = iterator ? iterator.bind(context) : Prototype.K;
    var trues = [], falses = [];
    this.each(function(value, index) {
      (iterator(value, index) ?
        trues : falses).push(value);
    });
    return [trues, falses];
  },

  pluck: function(property) {
    var results = [];
    this.each(function(value) {
      results.push(value[property]);
    });
    return results;
  },

  reject: function(iterator, context) {
    iterator = iterator.bind(context);
    var results = [];
    this.each(function(value, index) {
      if (!iterator(value, index))
        results.push(value);
    });
    return results;
  },

  sortBy: function(iterator, context) {
    iterator = iterator.bind(context);
    return this.map(function(value, index) {
      return {value: value, criteria: iterator(value, index)};
    }).sort(function(left, right) {
      var a = left.criteria, b = right.criteria;
      return a < b ? -1 : a > b ? 1 : 0;
    }).pluck('value');
  },

  toArray: function() {
    return this.map();
  },

  zip: function() {
    var iterator = Prototype.K, args = $A(arguments);
    if (Object.isFunction(args.last()))
      iterator = args.pop();

    var collections = [this].concat(args).map($A);
    return this.map(function(value, index) {
      return iterator(collections.pluck(index));
    });
  },

  size: function() {
    return this.toArray().length;
  },

  inspect: function() {
    return '#<Enumerable:' + this.toArray().inspect() + '>';
  }
};

Object.extend(Enumerable, {
  map:     Enumerable.collect,
  find:    Enumerable.detect,
  select:  Enumerable.findAll,
  filter:  Enumerable.findAll,
  member:  Enumerable.include,
  entries: Enumerable.toArray,
  every:   Enumerable.all,
  some:    Enumerable.any
});
function $A(iterable) {
  if (!iterable) return [];
  if (iterable.toArray) return iterable.toArray();
  else {
    var results = [];
    for (var i = 0, length = iterable.length; i < length; i++)
      results.push(iterable[i]);
    return results;
  }
}

if (Prototype.Browser.WebKit) {
  function $A(iterable) {
    if (!iterable) return [];
    if (!(Object.isFunction(iterable) && iterable == '[object NodeList]') &&
        iterable.toArray) {
      return iterable.toArray();
    } else {
      var results = [];
      for (var i = 0, length = iterable.length; i < length; i++)
        results.push(iterable[i]);
      return results;
    }
  }
}

Array.from = $A;

Object.extend(Array.prototype, Enumerable);

if (!Array.prototype._reverse) Array.prototype._reverse = Array.prototype.reverse;

Object.extend(Array.prototype, {
  _each: function(iterator) {
    for (var i = 0, length = this.length; i < length; i++)
      iterator(this[i]);
  },

  clear: function() {
    this.length = 0;
    return this;
  },

  first: function() {
    return this[0];
  },

  last: function() {
    return this[this.length - 1];
  },

  compact: function() {
    return this.select(function(value) {
      return value != null;
    });
  },

  flatten: function() {
    return this.inject([], function(array, value) {
      return array.concat(Object.isArray(value) ?
        value.flatten() : [value]);
    });
  },

  without: function() {
    var values = $A(arguments);
    return this.select(function(value) {
      return !values.include(value);
    });
  },

  reverse: function(inline) {
    return (inline !== false ? this : this.toArray())._reverse();
  },

  reduce: function() {
    return this.length > 1 ? this : this[0];
  },

  uniq: function(sorted) {
    return this.inject([], function(array, value, index) {
      if (0 == index || (sorted ? array.last() != value : !array.include(value)))
        array.push(value);
      return array;
    });
  },

  intersect: function(array) {
    return this.uniq().findAll(function(item) {
      return array.include(item);
    });
  },

  clone: function() {
    return [].concat(this);
  },

  size: function() {
    return this.length;
  },

  inspect: function() {
    return '[' + this.map(Object.inspect).join(', ') + ']';
  },

  toJSON: function() {
    var results = [];
    this.each(function(object) {
      var value = Object.toJSON(object);
      if (value !== undefined) results.push(value);
    });
    return '[' + results.join(', ') + ']';
  }
});

// use native browser JS 1.6 implementation if available
if (Object.isFunction(Array.prototype.forEach))
  Array.prototype._each = Array.prototype.forEach;

if (!Array.prototype.indexOf) Array.prototype.indexOf = function(item, i) {
  i || (i = 0);
  var length = this.length;
  if (i < 0) i = length + i;
  for (; i < length; i++)
    if (this[i] === item) return i;
  return -1;
};

if (!Array.prototype.lastIndexOf) Array.prototype.lastIndexOf = function(item, i) {
  i = isNaN(i) ? this.length : (i < 0 ? this.length + i : i) + 1;
  var n = this.slice(0, i).reverse().indexOf(item);
  return (n < 0) ? n : i - n - 1;
};

Array.prototype.toArray = Array.prototype.clone;

function $w(string) {
  string = string.strip();
  return string ? string.split(/\s+/) : [];
}

if (Prototype.Browser.Opera){
  Array.prototype.concat = function() {
    var array = [];
    for (var i = 0, length = this.length; i < length; i++) array.push(this[i]);
    for (var i = 0, length = arguments.length; i < length; i++) {
      if (Object.isArray(arguments[i])) {
        for (var j = 0, arrayLength = arguments[i].length; j < arrayLength; j++)
          array.push(arguments[i][j]);
      } else {
        array.push(arguments[i]);
      }
    }
    return array;
  };
}
Object.extend(Number.prototype, {
  toColorPart: function() {
    return this.toPaddedString(2, 16);
  },

  succ: function() {
    return this + 1;
  },

  times: function(iterator) {
    $R(0, this, true).each(iterator);
    return this;
  },

  toPaddedString: function(length, radix) {
    var string = this.toString(radix || 10);
    return '0'.times(length - string.length) + string;
  },

  toJSON: function() {
    return isFinite(this) ? this.toString() : 'null';
  }
});

$w('abs round ceil floor').each(function(method){
  Number.prototype[method] = Math[method].methodize();
});
var Hash = function(object) {
  if (object instanceof Hash) this.merge(object);
  else Object.extend(this, object || { });
};

Object.extend(Hash, {
  toQueryString: function(obj) {
    var parts = [];
    parts.add = arguments.callee.addPair;

    this.prototype._each.call(obj, function(pair) {
      if (!pair.key) return;
      var value = pair.value;

      if (value && typeof value == 'object') {
        if (Object.isArray(value)) value.each(function(value) {
          parts.add(pair.key, value);
        });
        return;
      }
      parts.add(pair.key, value);
    });

    return parts.join('&');
  },

  toJSON: function(object) {
    var results = [];
    this.prototype._each.call(object, function(pair) {
      var value = Object.toJSON(pair.value);
      if (value !== undefined) results.push(pair.key.toJSON() + ': ' + value);
    });
    return '{' + results.join(', ') + '}';
  }
});

Hash.toQueryString.addPair = function(key, value, prefix) {
  key = encodeURIComponent(key);
  if (value === undefined) this.push(key);
  else this.push(key + '=' + (value == null ? '' : encodeURIComponent(value)));
};

Object.extend(Hash.prototype, Enumerable);
Object.extend(Hash.prototype, {
  _each: function(iterator) {
    for (var key in this) {
      var value = this[key];
      if (value && value == Hash.prototype[key]) continue;

      var pair = [key, value];
      pair.key = key;
      pair.value = value;
      iterator(pair);
    }
  },

  keys: function() {
    return this.pluck('key');
  },

  values: function() {
    return this.pluck('value');
  },

  index: function(value) {
    var match = this.detect(function(pair) {
      return pair.value === value;
    });
    return match && match.key;
  },

  merge: function(hash) {
    return $H(hash).inject(this, function(mergedHash, pair) {
      mergedHash[pair.key] = pair.value;
      return mergedHash;
    });
  },

  remove: function() {
    var result;
    for(var i = 0, length = arguments.length; i < length; i++) {
      var value = this[arguments[i]];
      if (value !== undefined){
        if (result === undefined) result = value;
        else {
          if (!Object.isArray(result)) result = [result];
          result.push(value);
        }
      }
      delete this[arguments[i]];
    }
    return result;
  },

  toQueryString: function() {
    return Hash.toQueryString(this);
  },

  inspect: function() {
    return '#<Hash:{' + this.map(function(pair) {
      return pair.map(Object.inspect).join(': ');
    }).join(', ') + '}>';
  },

  toJSON: function() {
    return Hash.toJSON(this);
  }
});

function $H(object) {
  if (object instanceof Hash) return object;
  return new Hash(object);
};

// Safari iterates over shadowed properties
if (function() {
  var i = 0, Test = function(value) { this.key = value };
  Test.prototype.key = 'foo';
  for (var property in new Test('bar')) i++;
  return i > 1;
}()) Hash.prototype._each = function(iterator) {
  var cache = [];
  for (var key in this) {
    var value = this[key];
    if ((value && value == Hash.prototype[key]) || cache.include(key)) continue;
    cache.push(key);
    var pair = [key, value];
    pair.key = key;
    pair.value = value;
    iterator(pair);
  }
};
ObjectRange = Class.create();
Object.extend(ObjectRange.prototype, Enumerable);
Object.extend(ObjectRange.prototype, {
  initialize: function(start, end, exclusive) {
    this.start = start;
    this.end = end;
    this.exclusive = exclusive;
  },

  _each: function(iterator) {
    var value = this.start;
    while (this.include(value)) {
      iterator(value);
      value = value.succ();
    }
  },

  include: function(value) {
    if (value < this.start)
      return false;
    if (this.exclusive)
      return value < this.end;
    return value <= this.end;
  }
});

var $R = function(start, end, exclusive) {
  return new ObjectRange(start, end, exclusive);
};

var Ajax = {
  getTransport: function() {
    return Try.these(
      function() {return new XMLHttpRequest()},
      function() {return new ActiveXObject('Msxml2.XMLHTTP')},
      function() {return new ActiveXObject('Microsoft.XMLHTTP')}
    ) || false;
  },

  activeRequestCount: 0
};

Ajax.Responders = {
  responders: [],

  _each: function(iterator) {
    this.responders._each(iterator);
  },

  register: function(responder) {
    if (!this.include(responder))
      this.responders.push(responder);
  },

  unregister: function(responder) {
    this.responders = this.responders.without(responder);
  },

  dispatch: function(callback, request, transport, json) {
    this.each(function(responder) {
      if (Object.isFunction(responder[callback])) {
        try {
          responder[callback].apply(responder, [request, transport, json]);
        } catch (e) { }
      }
    });
  }
};

Object.extend(Ajax.Responders, Enumerable);

Ajax.Responders.register({
  onCreate: function() {
    Ajax.activeRequestCount++;
  },
  onComplete: function() {
    Ajax.activeRequestCount--;
  }
});

Ajax.Base = function() { };
Ajax.Base.prototype = {
  setOptions: function(options) {
    this.options = {
      method:       'post',
      asynchronous: true,
      contentType:  'application/x-www-form-urlencoded',
      encoding:     'UTF-8',
      parameters:   '',
      evalJSON:     true,
      evalJS:       true
    };
    Object.extend(this.options, options || { });

    this.options.method = this.options.method.toLowerCase();
    if (Object.isString(this.options.parameters))
      this.options.parameters = this.options.parameters.toQueryParams();
  }
};

Ajax.Request = Class.create();
Ajax.Request.Events =
  ['Uninitialized', 'Loading', 'Loaded', 'Interactive', 'Complete'];

Ajax.Request.prototype = Object.extend(new Ajax.Base(), {
  _complete: false,

  initialize: function(url, options) {
    this.transport = Ajax.getTransport();
    this.setOptions(options);
    this.request(url);
  },

  request: function(url) {
    this.url = url;
    this.method = this.options.method;
    var params = Object.clone(this.options.parameters);

    if (!['get', 'post'].include(this.method)) {
      // simulate other verbs over post
      params['_method'] = this.method;
      this.method = 'post';
    }

    this.parameters = params;

    if (params = Hash.toQueryString(params)) {
      // when GET, append parameters to URL
      if (this.method == 'get')
        this.url += (this.url.include('?') ? '&' : '?') + params;
      else if (/Konqueror|Safari|KHTML/.test(navigator.userAgent))
        params += '&_=';
    }

    try {
      var response = new Ajax.Response(this);
      if (this.options.onCreate) this.options.onCreate(response);
      Ajax.Responders.dispatch('onCreate', this, response);

      this.transport.open(this.method.toUpperCase(), this.url,
        this.options.asynchronous);

      if (this.options.asynchronous) this.respondToReadyState.bind(this).defer(1);

      this.transport.onreadystatechange = this.onStateChange.bind(this);
      this.setRequestHeaders();

      this.body = this.method == 'post' ? (this.options.postBody || params) : null;
      this.transport.send(this.body);

      /* Force Firefox to handle ready state 4 for synchronous requests */
      if (!this.options.asynchronous && this.transport.overrideMimeType)
        this.onStateChange();

    }
    catch (e) {
      this.dispatchException(e);
    }
  },

  onStateChange: function() {
    var readyState = this.transport.readyState;
    if (readyState > 1 && !((readyState == 4) && this._complete))
      this.respondToReadyState(this.transport.readyState);
  },

  setRequestHeaders: function() {
    var headers = {
      'X-Requested-With': 'XMLHttpRequest',
      'X-Prototype-Version': Prototype.Version,
      'Accept': 'text/javascript, text/html, application/xml, text/xml, */*'
    };

    if (this.method == 'post') {
      headers['Content-type'] = this.options.contentType +
        (this.options.encoding ? '; charset=' + this.options.encoding : '');

      /* Force "Connection: close" for older Mozilla browsers to work
       * around a bug where XMLHttpRequest sends an incorrect
       * Content-length header. See Mozilla Bugzilla #246651.
       */
      if (this.transport.overrideMimeType &&
          (navigator.userAgent.match(/Gecko\/(\d{4})/) || [0,2005])[1] < 2005)
            headers['Connection'] = 'close';
    }

    // user-defined headers
    if (typeof this.options.requestHeaders == 'object') {
      var extras = this.options.requestHeaders;

      if (Object.isFunction(extras.push))
        for (var i = 0, length = extras.length; i < length; i += 2)
          headers[extras[i]] = extras[i+1];
      else
        $H(extras).each(function(pair) { headers[pair.key] = pair.value });
    }

    for (var name in headers)
      this.transport.setRequestHeader(name, headers[name]);
  },

  success: function() {
    var status = this.getStatus();
    return !status || (status >= 200 && status < 300);
  },

  getStatus: function() {
    try {
      return this.transport.status || 0;
    } catch (e) { return 0 }
  },

  respondToReadyState: function(readyState) {
    var state = Ajax.Request.Events[readyState], response = new Ajax.Response(this);

    if (state == 'Complete') {
      try {
        this._complete = true;
        (this.options['on' + response.status]
         || this.options['on' + (this.success() ? 'Success' : 'Failure')]
         || Prototype.emptyFunction)(response, response.headerJSON);
      } catch (e) {
        this.dispatchException(e);
      }

      var contentType = response.getHeader('Content-type');
      if (this.options.evalJS == 'force'
          || (this.options.evalJS && contentType
          && contentType.match(/^\s*(text|application)\/(x-)?(java|ecma)script(;.*)?\s*$/i)))
        this.evalResponse();
    }

    try {
      (this.options['on' + state] || Prototype.emptyFunction)(response, response.headerJSON);
      Ajax.Responders.dispatch('on' + state, this, response, response.headerJSON);
    } catch (e) {
      this.dispatchException(e);
    }

    if (state == 'Complete') {
      // avoid memory leak in MSIE: clean up
      this.transport.onreadystatechange = Prototype.emptyFunction;
    }
  },

  getHeader: function(name) {
    try {
      return this.transport.getResponseHeader(name);
    } catch (e) { return null }
  },

  evalResponse: function() {
    try {
      return eval((this.transport.responseText || '').unfilterJSON());
    } catch (e) {
      this.dispatchException(e);
    }
  },

  dispatchException: function(exception) {
    (this.options.onException || Prototype.emptyFunction)(this, exception);
    Ajax.Responders.dispatch('onException', this, exception);
  }
});

Ajax.Response = Class.create();
Ajax.Response.prototype = {
  initialize: function(request){
    this.request = request;
    var transport  = this.transport  = request.transport,
        readyState = this.readyState = transport.readyState;

    if((readyState > 2 && !Prototype.Browser.IE) || readyState == 4) {
      this.status       = this.getStatus();
      this.statusText   = this.getStatusText();
      this.responseText = String.interpret(transport.responseText);
      this.headerJSON   = this.getHeaderJSON();
    }

    if(readyState == 4) {
      var xml = transport.responseXML;
      this.responseXML  = xml === undefined ? null : xml;
      this.responseJSON = this.getResponseJSON();
    }
  },

  status:      0,
  statusText: '',

  getStatus: Ajax.Request.prototype.getStatus,

  getStatusText: function() {
    try {
      return this.transport.statusText || '';
    } catch (e) { return '' }
  },

  getHeader: Ajax.Request.prototype.getHeader,

  getAllHeaders: function() {
    try {
      return this.getAllResponseHeaders();
    } catch (e) { return null }
  },

  getResponseHeader: function(name) {
    return this.transport.getResponseHeader(name);
  },

  getAllResponseHeaders: function() {
    return this.transport.getAllResponseHeaders();
  },

  getHeaderJSON: function() {
    var json = this.getHeader('X-JSON');
    try {
      return json ? json.evalJSON(this.request.options.sanitizeJSON) : null;
    } catch (e) {
      this.request.dispatchException(e);
    }
  },

  getResponseJSON: function() {
    var options = this.request.options;
    try {
      if (options.evalJSON == 'force' || (options.evalJSON &&
          (this.getHeader('Content-type') || '').include('application/json')))
        return this.transport.responseText.evalJSON(options.sanitizeJSON);
      return null;
    } catch (e) {
      this.request.dispatchException(e);
    }
  }
};

Ajax.Updater = Class.create();

Object.extend(Object.extend(Ajax.Updater.prototype, Ajax.Request.prototype), {
  initialize: function(container, url, options) {
    this.container = {
      success: (container.success || container),
      failure: (container.failure || (container.success ? null : container))
    };

    this.transport = Ajax.getTransport();
    this.setOptions(options);

    var onComplete = this.options.onComplete || Prototype.emptyFunction;
    this.options.onComplete = (function(response, param) {
      this.updateContent(response.responseText);
      onComplete(response, param);
    }).bind(this);

    this.request(url);
  },

  updateContent: function(responseText) {
    var receiver = this.container[this.success() ? 'success' : 'failure'],
        options = this.options;

    if (!options.evalScripts) responseText = responseText.stripScripts();

    if (receiver = $(receiver)) {
      if (options.insertion) {
        if (Object.isString(options.insertion)) {
          var insertion = { }; insertion[options.insertion] = responseText;
          receiver.insert(insertion);
        }
        else options.insertion(receiver, responseText);
      }
      else receiver.update(responseText);
    }

    if (this.success()) {
      if (this.onComplete) this.onComplete.bind(this).defer();
    }
  }
});

Ajax.PeriodicalUpdater = Class.create();
Ajax.PeriodicalUpdater.prototype = Object.extend(new Ajax.Base(), {
  initialize: function(container, url, options) {
    this.setOptions(options);
    this.onComplete = this.options.onComplete;

    this.frequency = (this.options.frequency || 2);
    this.decay = (this.options.decay || 1);

    this.updater = { };
    this.container = container;
    this.url = url;

    this.start();
  },

  start: function() {
    this.options.onComplete = this.updateComplete.bind(this);
    this.onTimerEvent();
  },

  stop: function() {
    this.updater.options.onComplete = undefined;
    clearTimeout(this.timer);
    (this.onComplete || Prototype.emptyFunction).apply(this, arguments);
  },

  updateComplete: function(responseText) {
    if (this.options.decay) {
      this.decay = (responseText == this.lastText ?
        this.decay * this.options.decay : 1);

      this.lastText = responseText;
    }
    this.timer = this.onTimerEvent.bind(this).delay(this.decay * this.frequency);
  },

  onTimerEvent: function() {
    this.updater = new Ajax.Updater(this.container, this.url, this.options);
  }
});
function $(element) {
  if (arguments.length > 1) {
    for (var i = 0, elements = [], length = arguments.length; i < length; i++)
      elements.push($(arguments[i]));
    return elements;
  }
  if (Object.isString(element))
    element = document.getElementById(element);
  return Element.extend(element);
}

if (Prototype.BrowserFeatures.XPath) {
  document._getElementsByXPath = function(expression, parentElement) {
    var results = [];
    var query = document.evaluate(expression, $(parentElement) || document,
      null, XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null);
    for (var i = 0, length = query.snapshotLength; i < length; i++)
      results.push(query.snapshotItem(i));
    return results;
  };
}

/*--------------------------------------------------------------------------*/

if (!window.Node)
  var Node = { };

Object.extend(Node, {
  ELEMENT_NODE: 1,
  ATTRIBUTE_NODE: 2,
  TEXT_NODE: 3,
  CDATA_SECTION_NODE: 4,
  ENTITY_REFERENCE_NODE: 5,
  ENTITY_NODE: 6,
  PROCESSING_INSTRUCTION_NODE: 7,
  COMMENT_NODE: 8,
  DOCUMENT_NODE: 9,
  DOCUMENT_TYPE_NODE: 10,
  DOCUMENT_FRAGMENT_NODE: 11,
  NOTATION_NODE: 12
});

(function() {
  var element = this.Element;
  this.Element = function(tagName, attributes) {
    attributes = attributes || { };
    tagName = tagName.toLowerCase();
    var cache = Element.cache;
    if (Prototype.Browser.IE && attributes.name) {
      tagName = '<' + tagName + ' name="' + attributes.name + '">';
      delete attributes.name;
      return Element.writeAttribute(document.createElement(tagName), attributes);
    }
    if (!cache[tagName]) cache[tagName] = Element.extend(document.createElement(tagName));
    return Element.writeAttribute(cache[tagName].cloneNode(false), attributes);
  };
  Object.extend(this.Element, element || { });
}).call(window);

Element.cache = { };

Element.Methods = {
  visible: function(element) {
    return $(element).style.display != 'none';
  },

  toggle: function(element) {
    element = $(element);
    Element[Element.visible(element) ? 'hide' : 'show'](element);
    return element;
  },

  hide: function(element) {
    $(element).style.display = 'none';
    return element;
  },

  show: function(element) {
    $(element).style.display = '';
    return element;
  },

  remove: function(element) {
    element = $(element);
    element.parentNode.removeChild(element);
    return element;
  },

  update: function(element, content) {
    element = $(element);
    if (content && content.toElement) content = content.toElement();
    if (Object.isElement(content)) return element.update().insert(content);
    content = Object.toHTML(content);
    element.innerHTML = content.stripScripts();
    content.evalScripts.bind(content).defer();
    return element;
  },

  replace: function(element, content) {
    element = $(element);
    if (content && content.toElement) content = content.toElement();
    else if (!Object.isElement(content)) {
      content = Object.toHTML(content);
      var range = element.ownerDocument.createRange();
      range.selectNode(element);
      content.evalScripts.bind(content).defer();
      content = range.createContextualFragment(content.stripScripts());
    }
    element.parentNode.replaceChild(content, element);
    return element;
  },

  insert: function(element, insertions) {
    element = $(element);

    if (Object.isString(insertions) || Object.isNumber(insertions) ||
        Object.isElement(insertions) || (insertions && (insertions.toElement || insertions.toHTML)))
          insertions = {bottom:insertions};

    var content, t, range;

    for (position in insertions) {
      content  = insertions[position];
      position = position.toLowerCase();
      t = Element._insertionTranslations[position];

      if (content && content.toElement) content = content.toElement();
      if (Object.isElement(content)) {
        t.insert(element, content);
        continue;
      }

      content = Object.toHTML(content);

      range = element.ownerDocument.createRange();
      t.initializeRange(element, range);
      t.insert(element, range.createContextualFragment(content.stripScripts()));

      content.evalScripts.bind(content).defer();
    }

    return element;
  },

  wrap: function(element, wrapper, attributes) {
    element = $(element);
    if (Object.isElement(wrapper))
      $(wrapper).writeAttribute(attributes || { });
    else if (Object.isString(wrapper)) wrapper = new Element(wrapper, attributes);
    else wrapper = new Element('div', wrapper);
    if (element.parentNode)
      element.parentNode.replaceChild(wrapper, element);
    wrapper.appendChild(element);
    return element;
  },

  inspect: function(element) {
    element = $(element);
    var result = '<' + element.tagName.toLowerCase();
    $H({'id': 'id', 'className': 'class'}).each(function(pair) {
      var property = pair.first(), attribute = pair.last();
      var value = (element[property] || '').toString();
      if (value) result += ' ' + attribute + '=' + value.inspect(true);
    });
    return result + '>';
  },

  recursivelyCollect: function(element, property) {
    element = $(element);
    var elements = [];
    while (element = element[property])
      if (element.nodeType == 1)
        elements.push(Element.extend(element));
    return elements;
  },

  ancestors: function(element) {
    return $(element).recursivelyCollect('parentNode');
  },

  descendants: function(element) {
    return $A($(element).getElementsByTagName('*')).each(Element.extend);
  },

  firstDescendant: function(element) {
    element = $(element).firstChild;
    while (element && element.nodeType != 1) element = element.nextSibling;
    return $(element);
  },

  immediateDescendants: function(element) {
    if (!(element = $(element).firstChild)) return [];
    while (element && element.nodeType != 1) element = element.nextSibling;
    if (element) return [element].concat($(element).nextSiblings());
    return [];
  },

  previousSiblings: function(element) {
    return $(element).recursivelyCollect('previousSibling');
  },

  nextSiblings: function(element) {
    return $(element).recursivelyCollect('nextSibling');
  },

  siblings: function(element) {
    element = $(element);
    return element.previousSiblings().reverse().concat(element.nextSiblings());
  },

  match: function(element, selector) {
    if (Object.isString(selector))
      selector = new Selector(selector);
    return selector.match($(element));
  },

  up: function(element, expression, index) {
    element = $(element);
    if (arguments.length == 1) return $(element.parentNode);
    var ancestors = element.ancestors();
    return expression ? Selector.findElement(ancestors, expression, index) :
      ancestors[index || 0];
  },

  down: function(element, expression, index) {
    element = $(element);
    if (arguments.length == 1) return element.firstDescendant();
    var descendants = element.descendants();
    return expression ? Selector.findElement(descendants, expression, index) :
      descendants[index || 0];
  },

  previous: function(element, expression, index) {
    element = $(element);
    if (arguments.length == 1) return $(Selector.handlers.previousElementSibling(element));
    var previousSiblings = element.previousSiblings();
    return expression ? Selector.findElement(previousSiblings, expression, index) :
      previousSiblings[index || 0];
  },

  next: function(element, expression, index) {
    element = $(element);
    if (arguments.length == 1) return $(Selector.handlers.nextElementSibling(element));
    var nextSiblings = element.nextSiblings();
    return expression ? Selector.findElement(nextSiblings, expression, index) :
      nextSiblings[index || 0];
  },

  select: function() {
    var args = $A(arguments), element = $(args.shift());
    return Selector.findChildElements(element, args);
  },

  adjacent: function() {
    var args = $A(arguments), element = $(args.shift());
    return Selector.findChildElements(element.parentNode, args).without(element);
  },

  identify: function(element) {
    element = $(element);
    var id = element.readAttribute('id'), self = arguments.callee;
    if (id) return id;
    do { id = 'anonymous_element_' + self.counter++ } while ($(id));
    element.writeAttribute('id', id);
    return id;
  },

  readAttribute: function(element, name) {
    element = $(element);
    if (Prototype.Browser.IE) {
      var t = Element._attributeTranslations.read;
      if (t.values[name]) return t.values[name](element, name);
      if (t.names[name]) name = t.names[name];
    }
    return element.getAttribute(name);
  },

  writeAttribute: function(element, name, value) {
    element = $(element);
    var attributes = { }, t = Element._attributeTranslations.write;

    if (typeof name == 'object') attributes = name;
    else attributes[name] = value === undefined ? true : value;

    for (var attr in attributes) {
      var name = t.names[attr] || attr, value = attributes[attr];
      if (t.values[attr]) name = t.values[attr](element, value);
      if (value === false || value === null)
        element.removeAttribute(name);
      else if (value === true)
        element.setAttribute(name, name);
      else element.setAttribute(name, value);
    }
    return element;
  },

  getHeight: function(element) {
    return $(element).getDimensions().height;
  },

  getWidth: function(element) {
    return $(element).getDimensions().width;
  },

  classNames: function(element) {
    return new Element.ClassNames(element);
  },

  hasClassName: function(element, className) {
    if (!(element = $(element))) return;
    var elementClassName = element.className;
    return (elementClassName.length > 0 && (elementClassName == className ||
      elementClassName.match(new RegExp("(^|\\s)" + className + "(\\s|$)"))));
  },

  addClassName: function(element, className) {
    if (!(element = $(element))) return;
    if (!element.hasClassName(className))
      element.className += (element.className ? ' ' : '') + className;
    return element;
  },

  removeClassName: function(element, className) {
    if (!(element = $(element))) return;
    element.className = element.className.replace(
      new RegExp("(^|\\s+)" + className + "(\\s+|$)"), ' ').strip();
    return element;
  },

  toggleClassName: function(element, className) {
    if (!(element = $(element))) return;
    return element[element.hasClassName(className) ?
      'removeClassName' : 'addClassName'](className);
  },

  // removes whitespace-only text node children
  cleanWhitespace: function(element) {
    element = $(element);
    var node = element.firstChild;
    while (node) {
      var nextNode = node.nextSibling;
      if (node.nodeType == 3 && !/\S/.test(node.nodeValue))
        element.removeChild(node);
      node = nextNode;
    }
    return element;
  },

  empty: function(element) {
    return $(element).innerHTML.blank();
  },

  descendantOf: function(element, ancestor) {
    element = $(element), ancestor = $(ancestor);
    while (element = element.parentNode)
      if (element == ancestor) return true;
    return false;
  },

  scrollTo: function(element) {
    element = $(element);
    var pos = element.cumulativeOffset();
    window.scrollTo(pos[0], pos[1]);
    return element;
  },

  getStyle: function(element, style) {
    element = $(element);
    style = style == 'float' ? 'cssFloat' : style.camelize();
    var value = element.style[style];
    if (!value) {
      var css = document.defaultView.getComputedStyle(element, null);
      value = css ? css[style] : null;
    }
    if (style == 'opacity') return value ? parseFloat(value) : 1.0;
    return value == 'auto' ? null : value;
  },

  getOpacity: function(element) {
    return $(element).getStyle('opacity');
  },

  setStyle: function(element, styles) {
    element = $(element);
    var elementStyle = element.style, match;
    if (Object.isString(styles)) {
      element.style.cssText += ';' + styles;
      return styles.include('opacity') ?
        element.setOpacity(styles.match(/opacity:\s*(\d?\.?\d*)/)[1]) : element;
    }
    for (var property in styles)
      if (property == 'opacity') element.setOpacity(styles[property]);
      else
        elementStyle[(property == 'float' || property == 'cssFloat') ?
          (elementStyle.styleFloat === undefined ? 'cssFloat' : 'styleFloat') :
            property] = styles[property];

    return element;
  },

  setOpacity: function(element, value) {
    element = $(element);
    element.style.opacity = (value == 1 || value === '') ? '' :
      (value < 0.00001) ? 0 : value;
    return element;
  },

  getDimensions: function(element) {
    element = $(element);
    var display = $(element).getStyle('display');
    if (display != 'none' && display != null) // Safari bug
      return {width: element.offsetWidth, height: element.offsetHeight};

    // All *Width and *Height properties give 0 on elements with display none,
    // so enable the element temporarily
    var els = element.style;
    var originalVisibility = els.visibility;
    var originalPosition = els.position;
    var originalDisplay = els.display;
    els.visibility = 'hidden';
    els.position = 'absolute';
    els.display = 'block';
    var originalWidth = element.clientWidth;
    var originalHeight = element.clientHeight;
    els.display = originalDisplay;
    els.position = originalPosition;
    els.visibility = originalVisibility;
    return {width: originalWidth, height: originalHeight};
  },

  makePositioned: function(element) {
    element = $(element);
    var pos = Element.getStyle(element, 'position');
    if (pos == 'static' || !pos) {
      element._madePositioned = true;
      element.style.position = 'relative';
      // Opera returns the offset relative to the positioning context, when an
      // element is position relative but top and left have not been defined
      if (window.opera) {
        element.style.top = 0;
        element.style.left = 0;
      }
    }
    return element;
  },

  undoPositioned: function(element) {
    element = $(element);
    if (element._madePositioned) {
      element._madePositioned = undefined;
      element.style.position =
        element.style.top =
        element.style.left =
        element.style.bottom =
        element.style.right = '';
    }
    return element;
  },

  makeClipping: function(element) {
    element = $(element);
    if (element._overflow) return element;
    element._overflow = element.style.overflow || 'auto';
    if ((Element.getStyle(element, 'overflow') || 'visible') != 'hidden')
      element.style.overflow = 'hidden';
    return element;
  },

  undoClipping: function(element) {
    element = $(element);
    if (!element._overflow) return element;
    element.style.overflow = element._overflow == 'auto' ? '' : element._overflow;
    element._overflow = null;
    return element;
  },

  cumulativeOffset: function(element) {
    var valueT = 0, valueL = 0;
    do {
      valueT += element.offsetTop  || 0;
      valueL += element.offsetLeft || 0;
      element = element.offsetParent;
    } while (element);
    return Element._returnOffset(valueL, valueT);
  },

  positionedOffset: function(element) {
    var valueT = 0, valueL = 0;
    do {
      valueT += element.offsetTop  || 0;
      valueL += element.offsetLeft || 0;
      element = element.offsetParent;
      if (element) {
        if (element.tagName == 'BODY') break;
        var p = Element.getStyle(element, 'position');
        if (p == 'relative' || p == 'absolute') break;
      }
    } while (element);
    return Element._returnOffset(valueL, valueT);
  },

  absolutize: function(element) {
    element = $(element);
    if (element.getStyle('position') == 'absolute') return;
    // Position.prepare(); // To be done manually by Scripty when it needs it.

    var offsets = element.positionedOffset();
    var top     = offsets[1];
    var left    = offsets[0];
    var width   = element.clientWidth;
    var height  = element.clientHeight;

    element._originalLeft   = left - parseFloat(element.style.left  || 0);
    element._originalTop    = top  - parseFloat(element.style.top || 0);
    element._originalWidth  = element.style.width;
    element._originalHeight = element.style.height;

    element.style.position = 'absolute';
    element.style.top    = top + 'px';
    element.style.left   = left + 'px';
    element.style.width  = width + 'px';
    element.style.height = height + 'px';
    return element;
  },

  relativize: function(element) {
    element = $(element);
    if (element.getStyle('position') == 'relative') return;
    // Position.prepare(); // To be done manually by Scripty when it needs it.

    element.style.position = 'relative';
    var top  = parseFloat(element.style.top  || 0) - (element._originalTop || 0);
    var left = parseFloat(element.style.left || 0) - (element._originalLeft || 0);

    element.style.top    = top + 'px';
    element.style.left   = left + 'px';
    element.style.height = element._originalHeight;
    element.style.width  = element._originalWidth;
    return element;
  },

  cumulativeScrollOffset: function(element) {
    var valueT = 0, valueL = 0;
    do {
      valueT += element.scrollTop  || 0;
      valueL += element.scrollLeft || 0;
      element = element.parentNode;
    } while (element);
    return Element._returnOffset(valueL, valueT);
  },

  getOffsetParent: function(element) {
    if (element.offsetParent) return $(element.offsetParent);
    if (element == document.body) return $(element);

    while ((element = element.parentNode) && element != document.body)
      if (Element.getStyle(element, 'position') != 'static')
        return $(element);

    return $(document.body);
  },

  viewportOffset: function(forElement) {
    var valueT = 0, valueL = 0;

    var element = forElement;
    do {
      valueT += element.offsetTop  || 0;
      valueL += element.offsetLeft || 0;

      // Safari fix
      if (element.offsetParent == document.body &&
        Element.getStyle(element, 'position') == 'absolute') break;

    } while (element = element.offsetParent);

    element = forElement;
    do {
      if (!Prototype.Browser.Opera || element.tagName == 'BODY') {
        valueT -= element.scrollTop  || 0;
        valueL -= element.scrollLeft || 0;
      }
    } while (element = element.parentNode);

    return Element._returnOffset(valueL, valueT);
  },

  clonePosition: function(element, source) {
    var options = Object.extend({
      setLeft:    true,
      setTop:     true,
      setWidth:   true,
      setHeight:  true,
      offsetTop:  0,
      offsetLeft: 0
    }, arguments[2] || { });

    // find page position of source
    source = $(source);
    var p = source.viewportOffset();

    // find coordinate system to use
    element = $(element);
    var delta = [0, 0];
    var parent = null;
    // delta [0,0] will do fine with position: fixed elements,
    // position:absolute needs offsetParent deltas
    if (Element.getStyle(element, 'position') == 'absolute') {
      parent = element.getOffsetParent();
      delta = parent.viewportOffset();
    }

    // correct by body offsets (fixes Safari)
    if (parent == document.body) {
      delta[0] -= document.body.offsetLeft;
      delta[1] -= document.body.offsetTop;
    }

    // set position
    if (options.setLeft)   element.style.left  = (p[0] - delta[0] + options.offsetLeft) + 'px';
    if (options.setTop)    element.style.top   = (p[1] - delta[1] + options.offsetTop) + 'px';
    if (options.setWidth)  element.style.width = source.offsetWidth + 'px';
    if (options.setHeight) element.style.height = source.offsetHeight + 'px';
    return element;
  }
};

Element.Methods.identify.counter = 1;

if (!document.getElementsByClassName) document.getElementsByClassName = function(instanceMethods){
  function iter(name) {
    return name.blank() ? null : "[contains(concat(' ', @class, ' '), ' " + name + " ')]";
  }

  instanceMethods.getElementsByClassName = Prototype.BrowserFeatures.XPath ?
  function(element, className) {
    className = className.toString().strip();
    var cond = /\s/.test(className) ? $w(className).map(iter).join('') : iter(className);
    return cond ? document._getElementsByXPath('.//*' + cond, element) : [];
  } : function(element, className) {
    className = className.toString().strip();
    var elements = [], classNames = (/\s/.test(className) ? $w(className) : null);
    if (!classNames && !className) return elements;

    var nodes = $(element).getElementsByTagName('*');
    className = ' ' + className + ' ';

    for (var i = 0, child, cn; child = nodes[i]; i++) {
      if (child.className && (cn = ' ' + child.className + ' ') && (cn.include(className) ||
          (classNames && classNames.all(function(name) {
            return !name.toString().blank() && cn.include(' ' + name + ' ');
          }))))
        elements.push(Element.extend(child));
    }
    return elements;
  };

  return function(className, parentElement) {
    return $(parentElement || document.body).getElementsByClassName(className);
  };
}(Element.Methods);

Object.extend(Element.Methods, {
  getElementsBySelector: Element.Methods.select,
  childElements: Element.Methods.immediateDescendants
});

Element._attributeTranslations = {
  write: {
    names: {
      className: 'class',
      htmlFor:   'for'
    },
    values: { }
  }
};


if (!document.createRange || Prototype.Browser.Opera) {
  Element.Methods.insert = function(element, insertions) {
    element = $(element);

    if (Object.isString(insertions) || Object.isNumber(insertions) ||
        Object.isElement(insertions) || (insertions && (insertions.toElement || insertions.toHTML)))
          insertions = { bottom: insertions };

    var t = Element._insertionTranslations, content, position, pos, tagName;

    for (position in insertions) {
      content  = insertions[position];
      position = position.toLowerCase();
      pos      = t[position];

      if (content && content.toElement) content = content.toElement();
      if (Object.isElement(content)) {
        pos.insert(element, content);
        continue;
      }

      content = Object.toHTML(content);
      tagName = ((position == 'before' || position == 'after')
        ? element.parentNode : element).tagName.toUpperCase();

      if (t.tags[tagName]) {
        var fragments = Element._getContentFromAnonymousElement(tagName, content.stripScripts());
        if (position == 'top' || position == 'after') fragments.reverse();
        fragments.each(pos.insert.curry(element));
      }
      else element.insertAdjacentHTML(pos.adjacency, content.stripScripts());

      content.evalScripts.bind(content).defer();
    }

    return element;
  };
}

if (Prototype.Browser.Opera) {
  Element.Methods._getStyle = Element.Methods.getStyle;
  Element.Methods.getStyle = function(element, style) {
    switch(style) {
      case 'left':
      case 'top':
      case 'right':
      case 'bottom':
        if (Element._getStyle(element, 'position') == 'static') return null;
      default: return Element._getStyle(element, style);
    }
  };
  Element.Methods._readAttribute = Element.Methods.readAttribute;
  Element.Methods.readAttribute = function(element, attribute) {
    if (attribute == 'title') return element.title;
    return Element._readAttribute(element, attribute);
  };
}

else if (Prototype.Browser.IE) {
  Element.Methods.getStyle = function(element, style) {
    element = $(element);
    style = (style == 'float' || style == 'cssFloat') ? 'styleFloat' : style.camelize();
    var value = element.style[style];
    if (!value && element.currentStyle) value = element.currentStyle[style];

    if (style == 'opacity') {
      if (value = (element.getStyle('filter') || '').match(/alpha\(opacity=(.*)\)/))
        if (value[1]) return parseFloat(value[1]) / 100;
      return 1.0;
    }

    if (value == 'auto') {
      if ((style == 'width' || style == 'height') && (element.getStyle('display') != 'none'))
        return element['offset' + style.capitalize()] + 'px';
      return null;
    }
    return value;
  };

  Element.Methods.setOpacity = function(element, value) {
    function stripAlpha(filter){
      return filter.replace(/alpha\([^\)]*\)/gi,'');
    }
    element = $(element);
    var filter = element.getStyle('filter'), style = element.style;
    if (value == 1 || value === '') {
      (filter = stripAlpha(filter)) ?
        style.filter = filter : style.removeAttribute('filter');
      return element;
    } else if (value < 0.00001) value = 0;
    style.filter = stripAlpha(filter) +
      'alpha(opacity=' + (value * 100) + ')';
    return element;
  };

  Element._attributeTranslations = {
    read: {
      names: {
        'class': 'className',
        'for':   'htmlFor'
      },
      values: {
        _getAttr: function(element, attribute) {
          return element.getAttribute(attribute, 2);
        },
        _getEv: function(element, attribute) {
          var attribute = element.getAttribute(attribute);
          return attribute ? attribute.toString().slice(23, -2) : null;
        },
        _flag: function(element, attribute) {
          return $(element).hasAttribute(attribute) ? attribute : null;
        },
        style: function(element) {
          return element.style.cssText.toLowerCase();
        },
        title: function(element) {
          return element.title;
        }
      }
    }
  };

  Element._attributeTranslations.write = {
    names: Object.extend({
      colspan:   'colSpan',
      rowspan:   'rowSpan',
      valign:    'vAlign',
      datetime:  'dateTime',
      accesskey: 'accessKey',
      tabindex:  'tabIndex',
      enctype:   'encType',
      maxlength: 'maxLength',
      readonly:  'readOnly',
      longdesc:  'longDesc'
    }, Element._attributeTranslations.read.names),

    values: {
      checked: function(element, value) {
        element.checked = !!value;
      },

      style: function(element, value) {
        element.style.cssText = value ? value : '';
      }
    }
  };

  (function(v) {
    Object.extend(v, {
      href: v._getAttr,
      src:  v._getAttr,
      type: v._getAttr,
      disabled: v._flag,
      checked:  v._flag,
      readonly: v._flag,
      multiple: v._flag,
      onload:      v._getEv,
      onunload:    v._getEv,
      onclick:     v._getEv,
      ondblclick:  v._getEv,
      onmousedown: v._getEv,
      onmouseup:   v._getEv,
      onmouseover: v._getEv,
      onmousemove: v._getEv,
      onmouseout:  v._getEv,
      onfocus:     v._getEv,
      onblur:      v._getEv,
      onkeypress:  v._getEv,
      onkeydown:   v._getEv,
      onkeyup:     v._getEv,
      onsubmit:    v._getEv,
      onreset:     v._getEv,
      onselect:    v._getEv,
      onchange:    v._getEv
    });
  })(Element._attributeTranslations.read.values);
}

else if (Prototype.Browser.Gecko) {
  Element.Methods.setOpacity = function(element, value) {
    element = $(element);
    element.style.opacity = (value == 1) ? 0.999999 :
      (value === '') ? '' : (value < 0.00001) ? 0 : value;
    return element;
  };
}

else if (Prototype.Browser.WebKit) {
  Element.Methods.setOpacity = function(element, value) {
    element = $(element);
    element.style.opacity = (value == 1 || value === '') ? '' :
      (value < 0.00001) ? 0 : value;

    if (value == 1)
      if(element.tagName == 'IMG' && element.width) {
        element.width++; element.width--;
      } else try {
        var n = document.createTextNode(' ');
        element.appendChild(n);
        element.removeChild(n);
      } catch (e) { }

    return element;
  };

  // Safari returns margins on body which is incorrect if the child is absolutely
  // positioned.  For performance reasons, redefine Position.cumulativeOffset for
  // KHTML/WebKit only.
  Element.Methods.cumulativeOffset = function(element) {
    var valueT = 0, valueL = 0;
    do {
      valueT += element.offsetTop  || 0;
      valueL += element.offsetLeft || 0;
      if (element.offsetParent == document.body)
        if (Element.getStyle(element, 'position') == 'absolute') break;

      element = element.offsetParent;
    } while (element);

    return [valueL, valueT];
  };
}

if (Prototype.Browser.IE || Prototype.Browser.Opera) {
  // IE and Opera are missing .innerHTML support for TABLE-related and SELECT elements
  Element.Methods.update = function(element, content) {
    element = $(element);

    if (content && content.toElement) content = content.toElement();
    if (Object.isElement(content)) return element.update().insert(content);

    content = Object.toHTML(content);
    var tagName = element.tagName.toUpperCase();

    if (tagName in Element._insertionTranslations.tags) {
      $A(element.childNodes).each(function(node) { element.removeChild(node) });
      Element._getContentFromAnonymousElement(tagName, content.stripScripts())
        .each(function(node) { element.appendChild(node) });
    }
    else element.innerHTML = content.stripScripts();

    content.evalScripts.bind(content).defer();
    return element;
  };
}

if (document.createElement('div').outerHTML) {
  Element.Methods.replace = function(element, content) {
    element = $(element);

    if (content && content.toElement) content = content.toElement();
    if (Object.isElement(content)) {
      element.parentNode.replaceChild(content, element);
      return element;
    }

    content = Object.toHTML(content);
    var parent = element.parentNode, tagName = parent.tagName.toUpperCase();

    if (Element._insertionTranslations.tags[tagName]) {
      var nextSibling = element.next();
      var fragments = Element._getContentFromAnonymousElement(tagName, content.stripScripts());
      parent.removeChild(element);
      if (nextSibling)
        fragments.each(function(node) { parent.insertBefore(node, nextSibling) });
      else
        fragments.each(function(node) { parent.appendChild(node) });
    }
    else element.outerHTML = content.stripScripts();

    content.evalScripts.bind(content).defer();
    return element;
  };
}

Element._returnOffset = function(l, t) {
  var result = [l, t];
  result.left = l;
  result.top = t;
  return result;
};

Element._getContentFromAnonymousElement = function(tagName, html) {
  var div = new Element('div'), t = Element._insertionTranslations.tags[tagName];
  div.innerHTML = t[0] + html + t[1];
  t[2].times(function() { div = div.firstChild });
  return $A(div.childNodes);
};

Element._insertionTranslations = {
  before: {
    adjacency: 'beforeBegin',
    insert: function(element, node) {
      element.parentNode.insertBefore(node, element);
    },
    initializeRange: function(element, range) {
      range.setStartBefore(element);
    }
  },
  top: {
    adjacency: 'afterBegin',
    insert: function(element, node) {
      element.insertBefore(node, element.firstChild);
    },
    initializeRange: function(element, range) {
      range.selectNodeContents(element);
      range.collapse(true);
    }
  },
  bottom: {
    adjacency: 'beforeEnd',
    insert: function(element, node) {
      element.appendChild(node);
    }
  },
  after: {
    adjacency: 'afterEnd',
    insert: function(element, node) {
      element.parentNode.insertBefore(node, element.nextSibling);
    },
    initializeRange: function(element, range) {
      range.setStartAfter(element);
    }
  },
  tags: {
    TABLE:  ['<table>',                '</table>',                   1],
    TBODY:  ['<table><tbody>',         '</tbody></table>',           2],
    TR:     ['<table><tbody><tr>',     '</tr></tbody></table>',      3],
    TD:     ['<table><tbody><tr><td>', '</td></tr></tbody></table>', 4],
    SELECT: ['<select>',               '</select>',                  1]
  }
};

(function() {
  this.bottom.initializeRange = this.top.initializeRange;
  Object.extend(this.tags, {
    THEAD: this.tags.TBODY,
    TFOOT: this.tags.TBODY,
    TH:    this.tags.TD
  });
}).call(Element._insertionTranslations);

Element.Methods.Simulated = {
  hasAttribute: function(element, attribute) {
    var t = Element._attributeTranslations.read, node;
    attribute = t.names[attribute] || attribute;
    node = $(element).getAttributeNode(attribute);
    return node && node.specified;
  }
};

Element.Methods.ByTag = { };

Object.extend(Element, Element.Methods);

if (!Prototype.BrowserFeatures.ElementExtensions &&
    document.createElement('div').__proto__) {
  window.HTMLElement = { };
  window.HTMLElement.prototype = document.createElement('div').__proto__;
  Prototype.BrowserFeatures.ElementExtensions = true;
}

Element.extend = (function() {
  if (Prototype.BrowserFeatures.SpecificElementExtensions)
    return Prototype.K;

  var Methods = { }, ByTag = Element.Methods.ByTag;

  var extend = Object.extend(function(element) {
    if (!element || element._extendedByPrototype ||
        element.nodeType != 1 || element == window) return element;

    var methods = Object.clone(Methods),
      tagName = element.tagName, property, value;

    // extend methods for specific tags
    if (ByTag[tagName]) Object.extend(methods, ByTag[tagName]);

    for (property in methods) {
      value = methods[property];
      if (Object.isFunction(value) && !(property in element))
        element[property] = value.methodize();
    }

    element._extendedByPrototype = Prototype.emptyFunction;
    return element;

  }, {
    refresh: function() {
      // extend methods for all tags (Safari doesn't need this)
      if (!Prototype.BrowserFeatures.ElementExtensions) {
        Object.extend(Methods, Element.Methods);
        Object.extend(Methods, Element.Methods.Simulated);
      }
    }
  });

  extend.refresh();
  return extend;
})();

Element.hasAttribute = function(element, attribute) {
  if (element.hasAttribute) return element.hasAttribute(attribute);
  return Element.Methods.Simulated.hasAttribute(element, attribute);
};

Element.addMethods = function(methods) {
  var F = Prototype.BrowserFeatures, T = Element.Methods.ByTag;

  if (!methods) {
    Object.extend(Form, Form.Methods);
    Object.extend(Form.Element, Form.Element.Methods);
    Object.extend(Element.Methods.ByTag, {
      "FORM":     Object.clone(Form.Methods),
      "INPUT":    Object.clone(Form.Element.Methods),
      "SELECT":   Object.clone(Form.Element.Methods),
      "TEXTAREA": Object.clone(Form.Element.Methods)
    });
  }

  if (arguments.length == 2) {
    var tagName = methods;
    methods = arguments[1];
  }

  if (!tagName) Object.extend(Element.Methods, methods || { });
  else {
    if (Object.isArray(tagName)) tagName.each(extend);
    else extend(tagName);
  }

  function extend(tagName) {
    tagName = tagName.toUpperCase();
    if (!Element.Methods.ByTag[tagName])
      Element.Methods.ByTag[tagName] = { };
    Object.extend(Element.Methods.ByTag[tagName], methods);
  }

  function copy(methods, destination, onlyIfAbsent) {
    onlyIfAbsent = onlyIfAbsent || false;
    for (var property in methods) {
      var value = methods[property];
      if (!Object.isFunction(value)) continue;
      if (!onlyIfAbsent || !(property in destination))
        destination[property] = value.methodize();
    }
  }

  function findDOMClass(tagName) {
    var klass;
    var trans = {
      "OPTGROUP": "OptGroup", "TEXTAREA": "TextArea", "P": "Paragraph",
      "FIELDSET": "FieldSet", "UL": "UList", "OL": "OList", "DL": "DList",
      "DIR": "Directory", "H1": "Heading", "H2": "Heading", "H3": "Heading",
      "H4": "Heading", "H5": "Heading", "H6": "Heading", "Q": "Quote",
      "INS": "Mod", "DEL": "Mod", "A": "Anchor", "IMG": "Image", "CAPTION":
      "TableCaption", "COL": "TableCol", "COLGROUP": "TableCol", "THEAD":
      "TableSection", "TFOOT": "TableSection", "TBODY": "TableSection", "TR":
      "TableRow", "TH": "TableCell", "TD": "TableCell", "FRAMESET":
      "FrameSet", "IFRAME": "IFrame"
    };
    if (trans[tagName]) klass = 'HTML' + trans[tagName] + 'Element';
    if (window[klass]) return window[klass];
    klass = 'HTML' + tagName + 'Element';
    if (window[klass]) return window[klass];
    klass = 'HTML' + tagName.capitalize() + 'Element';
    if (window[klass]) return window[klass];

    window[klass] = { };
    window[klass].prototype = document.createElement(tagName).__proto__;
    return window[klass];
  }

  if (F.ElementExtensions) {
    copy(Element.Methods, HTMLElement.prototype);
    copy(Element.Methods.Simulated, HTMLElement.prototype, true);
  }

  if (F.SpecificElementExtensions) {
    for (var tag in Element.Methods.ByTag) {
      var klass = findDOMClass(tag);
      if (Object.isUndefined(klass)) continue;
      copy(T[tag], klass.prototype);
    }
  }

  Object.extend(Element, Element.Methods);
  delete Element.ByTag;

  if (Element.extend.refresh) Element.extend.refresh();
  Element.cache = { };
};

document.viewport = {
  getDimensions: function() {
    var dimensions = { };
    $w('width height').each(function(d) {
      var D = d.capitalize();
      dimensions[d] = self['inner' + D] ||
       (document.documentElement['client' + D] || document.body['client' + D]);
    });
    return dimensions;
  },

  getWidth: function() {
    return this.getDimensions().width;
  },

  getHeight: function() {
    return this.getDimensions().height;
  },

  getScrollOffsets: function() {
    return Element._returnOffset(
      window.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft,
      window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop);
  }
};
/* Portions of the Selector class are derived from Jack Slocum’s DomQuery,
 * part of YUI-Ext version 0.40, distributed under the terms of an MIT-style
 * license.  Please see http://www.yui-ext.com/ for more information. */

var Selector = Class.create();

Selector.prototype = {
  initialize: function(expression) {
    this.expression = expression.strip();
    this.compileMatcher();
  },

  compileMatcher: function() {
    // Selectors with namespaced attributes can't use the XPath version
    if (Prototype.BrowserFeatures.XPath && !(/\[[\w-]*?:/).test(this.expression))
      return this.compileXPathMatcher();

    var e = this.expression, ps = Selector.patterns, h = Selector.handlers,
        c = Selector.criteria, le, p, m;

    if (Selector._cache[e]) {
      this.matcher = Selector._cache[e];
      return;
    }

    this.matcher = ["this.matcher = function(root) {",
                    "var r = root, h = Selector.handlers, c = false, n;"];

    while (e && le != e && (/\S/).test(e)) {
      le = e;
      for (var i in ps) {
        p = ps[i];
        if (m = e.match(p)) {
          this.matcher.push(Object.isFunction(c[i]) ? c[i](m) :
    	      new Template(c[i]).evaluate(m));
          e = e.replace(m[0], '');
          break;
        }
      }
    }

    this.matcher.push("return h.unique(n);\n}");
    eval(this.matcher.join('\n'));
    Selector._cache[this.expression] = this.matcher;
  },

  compileXPathMatcher: function() {
    var e = this.expression, ps = Selector.patterns,
        x = Selector.xpath, le, m;

    if (Selector._cache[e]) {
      this.xpath = Selector._cache[e]; return;
    }

    this.matcher = ['.//*'];
    while (e && le != e && (/\S/).test(e)) {
      le = e;
      for (var i in ps) {
        if (m = e.match(ps[i])) {
          this.matcher.push(Object.isFunction(x[i]) ? x[i](m) :
            new Template(x[i]).evaluate(m));
          e = e.replace(m[0], '');
          break;
        }
      }
    }

    this.xpath = this.matcher.join('');
    Selector._cache[this.expression] = this.xpath;
  },

  findElements: function(root) {
    root = root || document;
    if (this.xpath) return document._getElementsByXPath(this.xpath, root);
    return this.matcher(root);
  },

  match: function(element) {
    this.tokens = [];

    var e = this.expression, ps = Selector.patterns, as = Selector.assertions;
    var le, p, m;

    while (e && le !== e && (/\S/).test(e)) {
      le = e;
      for (var i in ps) {
        p = ps[i];
        if (m = e.match(p)) {
          // use the Selector.assertions methods unless the selector
          // is too complex.
          if (as[i]) {
            this.tokens.push([i, Object.clone(m)]);
            e = e.replace(m[0], '');
          } else {
            // reluctantly do a document-wide search
            // and look for a match in the array
            return this.findElements(document).include(element);
          }
        }
      }
    }

    var match = true, name, matches;
    for (var i = 0, token; token = this.tokens[i]; i++) {
      name = token[0], matches = token[1];
      if (!Selector.assertions[name](element, matches)) {
        match = false; break;
      }
    }

    return match;
  },

  toString: function() {
    return this.expression;
  },

  inspect: function() {
    return "#<Selector:" + this.expression.inspect() + ">";
  }
};

Object.extend(Selector, {
  _cache: { },

  xpath: {
    descendant:   "//*",
    child:        "/*",
    adjacent:     "/following-sibling::*[1]",
    laterSibling: '/following-sibling::*',
    tagName:      function(m) {
      if (m[1] == '*') return '';
      return "[local-name()='" + m[1].toLowerCase() +
             "' or local-name()='" + m[1].toUpperCase() + "']";
    },
    className:    "[contains(concat(' ', @class, ' '), ' #{1} ')]",
    id:           "[@id='#{1}']",
    attrPresence: "[@#{1}]",
    attr: function(m) {
      m[3] = m[5] || m[6];
      return new Template(Selector.xpath.operators[m[2]]).evaluate(m);
    },
    pseudo: function(m) {
      var h = Selector.xpath.pseudos[m[1]];
      if (!h) return '';
      if (Object.isFunction(h)) return h(m);
      return new Template(Selector.xpath.pseudos[m[1]]).evaluate(m);
    },
    operators: {
      '=':  "[@#{1}='#{3}']",
      '!=': "[@#{1}!='#{3}']",
      '^=': "[starts-with(@#{1}, '#{3}')]",
      '$=': "[substring(@#{1}, (string-length(@#{1}) - string-length('#{3}') + 1))='#{3}']",
      '*=': "[contains(@#{1}, '#{3}')]",
      '~=': "[contains(concat(' ', @#{1}, ' '), ' #{3} ')]",
      '|=': "[contains(concat('-', @#{1}, '-'), '-#{3}-')]"
    },
    pseudos: {
      'first-child': '[not(preceding-sibling::*)]',
      'last-child':  '[not(following-sibling::*)]',
      'only-child':  '[not(preceding-sibling::* or following-sibling::*)]',
      'empty':       "[count(*) = 0 and (count(text()) = 0 or translate(text(), ' \t\r\n', '') = '')]",
      'checked':     "[@checked]",
      'disabled':    "[@disabled]",
      'enabled':     "[not(@disabled)]",
      'not': function(m) {
        var e = m[6], p = Selector.patterns,
            x = Selector.xpath, le, m, v;

        var exclusion = [];
        while (e && le != e && (/\S/).test(e)) {
          le = e;
          for (var i in p) {
            if (m = e.match(p[i])) {
              v = Object.isFunction(x[i]) ? x[i](m) : new Template(x[i]).evaluate(m);
              exclusion.push("(" + v.substring(1, v.length - 1) + ")");
              e = e.replace(m[0], '');
              break;
            }
          }
        }
        return "[not(" + exclusion.join(" and ") + ")]";
      },
      'nth-child':      function(m) {
        return Selector.xpath.pseudos.nth("(count(./preceding-sibling::*) + 1) ", m);
      },
      'nth-last-child': function(m) {
        return Selector.xpath.pseudos.nth("(count(./following-sibling::*) + 1) ", m);
      },
      'nth-of-type':    function(m) {
        return Selector.xpath.pseudos.nth("position() ", m);
      },
      'nth-last-of-type': function(m) {
        return Selector.xpath.pseudos.nth("(last() + 1 - position()) ", m);
      },
      'first-of-type':  function(m) {
        m[6] = "1"; return Selector.xpath.pseudos['nth-of-type'](m);
      },
      'last-of-type':   function(m) {
        m[6] = "1"; return Selector.xpath.pseudos['nth-last-of-type'](m);
      },
      'only-of-type':   function(m) {
        var p = Selector.xpath.pseudos; return p['first-of-type'](m) + p['last-of-type'](m);
      },
      nth: function(fragment, m) {
        var mm, formula = m[6], predicate;
        if (formula == 'even') formula = '2n+0';
        if (formula == 'odd')  formula = '2n+1';
        if (mm = formula.match(/^(\d+)$/)) // digit only
          return '[' + fragment + "= " + mm[1] + ']';
        if (mm = formula.match(/^(-?\d*)?n(([+-])(\d+))?/)) { // an+b
          if (mm[1] == "-") mm[1] = -1;
          var a = mm[1] ? Number(mm[1]) : 1;
          var b = mm[2] ? Number(mm[2]) : 0;
          predicate = "[((#{fragment} - #{b}) mod #{a} = 0) and " +
          "((#{fragment} - #{b}) div #{a} >= 0)]";
          return new Template(predicate).evaluate({
            fragment: fragment, a: a, b: b });
        }
      }
    }
  },

  criteria: {
    tagName:      'n = h.tagName(n, r, "#{1}", c);   c = false;',
    className:    'n = h.className(n, r, "#{1}", c); c = false;',
    id:           'n = h.id(n, r, "#{1}", c);        c = false;',
    attrPresence: 'n = h.attrPresence(n, r, "#{1}"); c = false;',
    attr: function(m) {
      m[3] = (m[5] || m[6]);
      return new Template('n = h.attr(n, r, "#{1}", "#{3}", "#{2}"); c = false;').evaluate(m);
    },
    pseudo: function(m) {
      if (m[6]) m[6] = m[6].replace(/"/g, '\\"');
      return new Template('n = h.pseudo(n, "#{1}", "#{6}", r, c); c = false;').evaluate(m);
    },
    descendant:   'c = "descendant";',
    child:        'c = "child";',
    adjacent:     'c = "adjacent";',
    laterSibling: 'c = "laterSibling";'
  },

  patterns: {
    // combinators must be listed first
    // (and descendant needs to be last combinator)
    laterSibling: /^\s*~\s*/,
    child:        /^\s*>\s*/,
    adjacent:     /^\s*\+\s*/,
    descendant:   /^\s/,

    // selectors follow
    tagName:      /^\s*(\*|[\w\-]+)(\b|$)?/,
    id:           /^#([\w\-\*]+)(\b|$)/,
    className:    /^\.([\w\-\*]+)(\b|$)/,
    pseudo:       /^:((first|last|nth|nth-last|only)(-child|-of-type)|empty|checked|(en|dis)abled|not)(\((.*?)\))?(\b|$|\s|(?=:))/,
    attrPresence: /^\[([\w]+)\]/,
    attr:         /\[((?:[\w-]*:)?[\w-]+)\s*(?:([!^$*~|]?=)\s*((['"])([^\4]*?)\4|([^'"][^\]]*?)))?\]/
  },

  // for Selector.match and Element#match
  assertions: {
    tagName: function(element, matches) {
      return matches[1].toUpperCase() == element.tagName.toUpperCase();
    },

    className: function(element, matches) {
      return Element.hasClassName(element, matches[1]);
    },

    id: function(element, matches) {
      return element.id === matches[1];
    },

    attrPresence: function(element, matches) {
      return Element.hasAttribute(element, matches[1]);
    },

    attr: function(element, matches) {
      var nodeValue = Element.readAttribute(element, matches[1]);
      return Selector.operators[matches[2]](nodeValue, matches[3]);
    }
  },

  handlers: {
    // UTILITY FUNCTIONS
    // joins two collections
    concat: function(a, b) {
      for (var i = 0, node; node = b[i]; i++)
        a.push(node);
      return a;
    },

    // marks an array of nodes for counting
    mark: function(nodes) {
      for (var i = 0, node; node = nodes[i]; i++)
        node._counted = true;
      return nodes;
    },

    unmark: function(nodes) {
      for (var i = 0, node; node = nodes[i]; i++)
        node._counted = undefined;
      return nodes;
    },

    // mark each child node with its position (for nth calls)
    // "ofType" flag indicates whether we're indexing for nth-of-type
    // rather than nth-child
    index: function(parentNode, reverse, ofType) {
      parentNode._counted = true;
      if (reverse) {
        for (var nodes = parentNode.childNodes, i = nodes.length - 1, j = 1; i >= 0; i--) {
          node = nodes[i];
          if (node.nodeType == 1 && (!ofType || node._counted)) node.nodeIndex = j++;
        }
      } else {
        for (var i = 0, j = 1, nodes = parentNode.childNodes; node = nodes[i]; i++)
          if (node.nodeType == 1 && (!ofType || node._counted)) node.nodeIndex = j++;
      }
    },

    // filters out duplicates and extends all nodes
    unique: function(nodes) {
      if (nodes.length == 0) return nodes;
      var results = [], n;
      for (var i = 0, l = nodes.length; i < l; i++)
        if (!(n = nodes[i])._counted) {
          n._counted = true;
          results.push(Element.extend(n));
        }
      return Selector.handlers.unmark(results);
    },

    // COMBINATOR FUNCTIONS
    descendant: function(nodes) {
      var h = Selector.handlers;
      for (var i = 0, results = [], node; node = nodes[i]; i++)
        h.concat(results, node.getElementsByTagName('*'));
      return results;
    },

    child: function(nodes) {
      var h = Selector.handlers;
      for (var i = 0, results = [], node; node = nodes[i]; i++) {
        for (var j = 0, children = [], child; child = node.childNodes[j]; j++)
          if (child.nodeType == 1 && child.tagName != '!') results.push(child);
      }
      return results;
    },

    adjacent: function(nodes) {
      for (var i = 0, results = [], node; node = nodes[i]; i++) {
        var next = this.nextElementSibling(node);
        if (next) results.push(next);
      }
      return results;
    },

    laterSibling: function(nodes) {
      var h = Selector.handlers;
      for (var i = 0, results = [], node; node = nodes[i]; i++)
        h.concat(results, Element.nextSiblings(node));
      return results;
    },

    nextElementSibling: function(node) {
      while (node = node.nextSibling)
	      if (node.nodeType == 1) return node;
      return null;
    },

    previousElementSibling: function(node) {
      while (node = node.previousSibling)
        if (node.nodeType == 1) return node;
      return null;
    },

    // TOKEN FUNCTIONS
    tagName: function(nodes, root, tagName, combinator) {
      tagName = tagName.toUpperCase();
      var results = [], h = Selector.handlers;
      if (nodes) {
        if (combinator) {
          // fastlane for ordinary descendant combinators
          if (combinator == "descendant") {
            for (var i = 0, node; node = nodes[i]; i++)
              h.concat(results, node.getElementsByTagName(tagName));
            return results;
          } else nodes = this[combinator](nodes);
          if (tagName == "*") return nodes;
        }
        for (var i = 0, node; node = nodes[i]; i++)
          if (node.tagName.toUpperCase() == tagName) results.push(node);
        return results;
      } else return root.getElementsByTagName(tagName);
    },

    id: function(nodes, root, id, combinator) {
      var targetNode = $(id), h = Selector.handlers;
      if (!targetNode) return [];
      if (!nodes && root == document) return [targetNode];
      if (nodes) {
        if (combinator) {
          if (combinator == 'child') {
            for (var i = 0, node; node = nodes[i]; i++)
              if (targetNode.parentNode == node) return [targetNode];
          } else if (combinator == 'descendant') {
            for (var i = 0, node; node = nodes[i]; i++)
              if (Element.descendantOf(targetNode, node)) return [targetNode];
          } else if (combinator == 'adjacent') {
            for (var i = 0, node; node = nodes[i]; i++)
              if (Selector.handlers.previousElementSibling(targetNode) == node)
                return [targetNode];
          } else nodes = h[combinator](nodes);
        }
        for (var i = 0, node; node = nodes[i]; i++)
          if (node == targetNode) return [targetNode];
        return [];
      }
      return (targetNode && Element.descendantOf(targetNode, root)) ? [targetNode] : [];
    },

    className: function(nodes, root, className, combinator) {
      if (nodes && combinator) nodes = this[combinator](nodes);
      return Selector.handlers.byClassName(nodes, root, className);
    },

    byClassName: function(nodes, root, className) {
      if (!nodes) nodes = Selector.handlers.descendant([root]);
      var needle = ' ' + className + ' ';
      for (var i = 0, results = [], node, nodeClassName; node = nodes[i]; i++) {
        nodeClassName = node.className;
        if (nodeClassName.length == 0) continue;
        if (nodeClassName == className || (' ' + nodeClassName + ' ').include(needle))
          results.push(node);
      }
      return results;
    },

    attrPresence: function(nodes, root, attr) {
      var results = [];
      for (var i = 0, node; node = nodes[i]; i++)
        if (Element.hasAttribute(node, attr)) results.push(node);
      return results;
    },

    attr: function(nodes, root, attr, value, operator) {
      if (!nodes) nodes = root.getElementsByTagName("*");
      var handler = Selector.operators[operator], results = [];
      for (var i = 0, node; node = nodes[i]; i++) {
        var nodeValue = Element.readAttribute(node, attr);
        if (nodeValue === null) continue;
        if (handler(nodeValue, value)) results.push(node);
      }
      return results;
    },

    pseudo: function(nodes, name, value, root, combinator) {
      if (nodes && combinator) nodes = this[combinator](nodes);
      if (!nodes) nodes = root.getElementsByTagName("*");
      return Selector.pseudos[name](nodes, value, root);
    }
  },

  pseudos: {
    'first-child': function(nodes, value, root) {
      for (var i = 0, results = [], node; node = nodes[i]; i++) {
        if (Selector.handlers.previousElementSibling(node)) continue;
          results.push(node);
      }
      return results;
    },
    'last-child': function(nodes, value, root) {
      for (var i = 0, results = [], node; node = nodes[i]; i++) {
        if (Selector.handlers.nextElementSibling(node)) continue;
          results.push(node);
      }
      return results;
    },
    'only-child': function(nodes, value, root) {
      var h = Selector.handlers;
      for (var i = 0, results = [], node; node = nodes[i]; i++)
        if (!h.previousElementSibling(node) && !h.nextElementSibling(node))
          results.push(node);
      return results;
    },
    'nth-child':        function(nodes, formula, root) {
      return Selector.pseudos.nth(nodes, formula, root);
    },
    'nth-last-child':   function(nodes, formula, root) {
      return Selector.pseudos.nth(nodes, formula, root, true);
    },
    'nth-of-type':      function(nodes, formula, root) {
      return Selector.pseudos.nth(nodes, formula, root, false, true);
    },
    'nth-last-of-type': function(nodes, formula, root) {
      return Selector.pseudos.nth(nodes, formula, root, true, true);
    },
    'first-of-type':    function(nodes, formula, root) {
      return Selector.pseudos.nth(nodes, "1", root, false, true);
    },
    'last-of-type':     function(nodes, formula, root) {
      return Selector.pseudos.nth(nodes, "1", root, true, true);
    },
    'only-of-type':     function(nodes, formula, root) {
      var p = Selector.pseudos;
      return p['last-of-type'](p['first-of-type'](nodes, formula, root), formula, root);
    },

    // handles the an+b logic
    getIndices: function(a, b, total) {
      if (a == 0) return b > 0 ? [b] : [];
      return $R(1, total).inject([], function(memo, i) {
        if (0 == (i - b) % a && (i - b) / a >= 0) memo.push(i);
        return memo;
      });
    },

    // handles nth(-last)-child, nth(-last)-of-type, and (first|last)-of-type
    nth: function(nodes, formula, root, reverse, ofType) {
      if (nodes.length == 0) return [];
      if (formula == 'even') formula = '2n+0';
      if (formula == 'odd')  formula = '2n+1';
      var h = Selector.handlers, results = [], indexed = [], m;
      h.mark(nodes);
      for (var i = 0, node; node = nodes[i]; i++) {
        if (!node.parentNode._counted) {
          h.index(node.parentNode, reverse, ofType);
          indexed.push(node.parentNode);
        }
      }
      if (formula.match(/^\d+$/)) { // just a number
        formula = Number(formula);
        for (var i = 0, node; node = nodes[i]; i++)
          if (node.nodeIndex == formula) results.push(node);
      } else if (m = formula.match(/^(-?\d*)?n(([+-])(\d+))?/)) { // an+b
        if (m[1] == "-") m[1] = -1;
        var a = m[1] ? Number(m[1]) : 1;
        var b = m[2] ? Number(m[2]) : 0;
        var indices = Selector.pseudos.getIndices(a, b, nodes.length);
        for (var i = 0, node, l = indices.length; node = nodes[i]; i++) {
          for (var j = 0; j < l; j++)
            if (node.nodeIndex == indices[j]) results.push(node);
        }
      }
      h.unmark(nodes);
      h.unmark(indexed);
      return results;
    },

    'empty': function(nodes, value, root) {
      for (var i = 0, results = [], node; node = nodes[i]; i++) {
        // IE treats comments as element nodes
        if (node.tagName == '!' || (node.firstChild && !node.innerHTML.match(/^\s*$/))) continue;
        results.push(node);
      }
      return results;
    },

    'not': function(nodes, selector, root) {
      var h = Selector.handlers, selectorType, m;
      var exclusions = new Selector(selector).findElements(root);
      h.mark(exclusions);
      for (var i = 0, results = [], node; node = nodes[i]; i++)
        if (!node._counted) results.push(node);
      h.unmark(exclusions);
      return results;
    },

    'enabled': function(nodes, value, root) {
      for (var i = 0, results = [], node; node = nodes[i]; i++)
        if (!node.disabled) results.push(node);
      return results;
    },

    'disabled': function(nodes, value, root) {
      for (var i = 0, results = [], node; node = nodes[i]; i++)
        if (node.disabled) results.push(node);
      return results;
    },

    'checked': function(nodes, value, root) {
      for (var i = 0, results = [], node; node = nodes[i]; i++)
        if (node.checked) results.push(node);
      return results;
    }
  },

  operators: {
    '=':  function(nv, v) { return nv == v; },
    '!=': function(nv, v) { return nv != v; },
    '^=': function(nv, v) { return nv.startsWith(v); },
    '$=': function(nv, v) { return nv.endsWith(v); },
    '*=': function(nv, v) { return nv.include(v); },
    '~=': function(nv, v) { return (' ' + nv + ' ').include(' ' + v + ' '); },
    '|=': function(nv, v) { return ('-' + nv.toUpperCase() + '-').include('-' + v.toUpperCase() + '-'); }
  },

  matchElements: function(elements, expression) {
    var matches = new Selector(expression).findElements(), h = Selector.handlers;
    h.mark(matches);
    for (var i = 0, results = [], element; element = elements[i]; i++)
      if (element._counted) results.push(element);
    h.unmark(matches);
    return results;
  },

  findElement: function(elements, expression, index) {
    if (Object.isNumber(expression)) {
      index = expression; expression = false;
    }
    return Selector.matchElements(elements, expression || '*')[index || 0];
  },

  findChildElements: function(element, expressions) {
    var exprs = expressions.join(','), expressions = [];
    exprs.scan(/(([\w#:.~>+()\s-]+|\*|\[.*?\])+)\s*(,|$)/, function(m) {
      expressions.push(m[1].strip());
    });
    var results = [], h = Selector.handlers;
    for (var i = 0, l = expressions.length, selector; i < l; i++) {
      selector = new Selector(expressions[i].strip());
      h.concat(results, selector.findElements(element));
    }
    return (l > 1) ? h.unique(results) : results;
  }
});

function $$() {
  return Selector.findChildElements(document, $A(arguments));
}
var Form = {
  reset: function(form) {
    $(form).reset();
    return form;
  },

  serializeElements: function(elements, options) {
    if (typeof options != 'object') options = { hash: !!options };
    else if (options.hash === undefined) options.hash = true;
    var key, value, submitted = false, submit = options.submit;

    var data = elements.inject({ }, function(result, element) {
      if (!element.disabled && element.name) {
        key = element.name; value = $(element).getValue();
        if (value != null && (element.type != 'submit' || (!submitted &&
            submit !== false && (!submit || key == submit) && (submitted = true)))) {
          if (key in result) {
            // a key is already present; construct an array of values
            if (!Object.isArray(result[key])) result[key] = [result[key]];
            result[key].push(value);
          }
          else result[key] = value;
        }
      }
      return result;
    });

    return options.hash ? data : Hash.toQueryString(data);
  }
};

Form.Methods = {
  serialize: function(form, options) {
    return Form.serializeElements(Form.getElements(form), options);
  },

  getElements: function(form) {
    return $A($(form).getElementsByTagName('*')).inject([],
      function(elements, child) {
        if (Form.Element.Serializers[child.tagName.toLowerCase()])
          elements.push(Element.extend(child));
        return elements;
      }
    );
  },

  getInputs: function(form, typeName, name) {
    form = $(form);
    var inputs = form.getElementsByTagName('input');

    if (!typeName && !name) return $A(inputs).map(Element.extend);

    for (var i = 0, matchingInputs = [], length = inputs.length; i < length; i++) {
      var input = inputs[i];
      if ((typeName && input.type != typeName) || (name && input.name != name))
        continue;
      matchingInputs.push(Element.extend(input));
    }

    return matchingInputs;
  },

  disable: function(form) {
    form = $(form);
    Form.getElements(form).invoke('disable');
    return form;
  },

  enable: function(form) {
    form = $(form);
    Form.getElements(form).invoke('enable');
    return form;
  },

  findFirstElement: function(form) {
    var elements = $(form).getElements().findAll(function(element) {
      return 'hidden' != element.type && !element.disabled;
    });
    var firstByIndex = elements.findAll(function(element) {
      return element.hasAttribute('tabIndex') && element.tabIndex >= 0;
    }).sortBy(function(element) { return element.tabIndex }).first();

    return firstByIndex ? firstByIndex : elements.find(function(element) {
      return ['input', 'select', 'textarea'].include(element.tagName.toLowerCase());
    });
  },

  focusFirstElement: function(form) {
    form = $(form);
    form.findFirstElement().activate();
    return form;
  },

  request: function(form, options) {
    form = $(form), options = Object.clone(options || { });

    var params = options.parameters, action = form.readAttribute('action') || '';
    if (action.blank()) action = window.location.href;
    options.parameters = form.serialize(true);

    if (params) {
      if (Object.isString(params)) params = params.toQueryParams();
      Object.extend(options.parameters, params);
    }

    if (form.hasAttribute('method') && !options.method)
      options.method = form.method;

    return new Ajax.Request(action, options);
  }
};

/*--------------------------------------------------------------------------*/

Form.Element = {
  focus: function(element) {
    $(element).focus();
    return element;
  },

  select: function(element) {
    $(element).select();
    return element;
  }
};

Form.Element.Methods = {
  serialize: function(element) {
    element = $(element);
    if (!element.disabled && element.name) {
      var value = element.getValue();
      if (value != undefined) {
        var pair = { };
        pair[element.name] = value;
        return Hash.toQueryString(pair);
      }
    }
    return '';
  },

  getValue: function(element) {
    element = $(element);
    var method = element.tagName.toLowerCase();
    return Form.Element.Serializers[method](element);
  },

  setValue: function(element, value) {
    element = $(element);
    var method = element.tagName.toLowerCase();
    Form.Element.Serializers[method](element, value);
    return element;
  },

  clear: function(element) {
    $(element).value = '';
    return element;
  },

  present: function(element) {
    return $(element).value != '';
  },

  activate: function(element) {
    element = $(element);
    try {
      element.focus();
      if (element.select && (element.tagName.toLowerCase() != 'input' ||
          !['button', 'reset', 'submit'].include(element.type)))
        element.select();
    } catch (e) { }
    return element;
  },

  disable: function(element) {
    element = $(element);
    element.blur();
    element.disabled = true;
    return element;
  },

  enable: function(element) {
    element = $(element);
    element.disabled = false;
    return element;
  }
};

/*--------------------------------------------------------------------------*/

var Field = Form.Element;
var $F = Form.Element.Methods.getValue;

/*--------------------------------------------------------------------------*/

Form.Element.Serializers = {
  input: function(element, value) {
    switch (element.type.toLowerCase()) {
      case 'checkbox':
      case 'radio':
        return Form.Element.Serializers.inputSelector(element, value);
      default:
        return Form.Element.Serializers.textarea(element, value);
    }
  },

  inputSelector: function(element, value) {
    if (value === undefined) return element.checked ? element.value : null;
    else element.checked = !!value;
  },

  textarea: function(element, value) {
    if (value === undefined) return element.value;
    else element.value = value;
  },

  select: function(element, index) {
    if (index === undefined)
      return this[element.type == 'select-one' ?
        'selectOne' : 'selectMany'](element);
    else {
      var opt, value, single = !Object.isArray(index);
      for (var i = 0, length = element.length; i < length; i++) {
        opt = element.options[i];
        value = this.optionValue(opt);
        if (single) {
          if (value == index) {
            opt.selected = true;
            return;
          }
        }
        else opt.selected = index.include(value);
      }
    }
  },

  selectOne: function(element) {
    var index = element.selectedIndex;
    return index >= 0 ? this.optionValue(element.options[index]) : null;
  },

  selectMany: function(element) {
    var values, length = element.length;
    if (!length) return null;

    for (var i = 0, values = []; i < length; i++) {
      var opt = element.options[i];
      if (opt.selected) values.push(this.optionValue(opt));
    }
    return values;
  },

  optionValue: function(opt) {
    // extend element because hasAttribute may not be native
    return Element.extend(opt).hasAttribute('value') ? opt.value : opt.text;
  }
};

/*--------------------------------------------------------------------------*/

Abstract.TimedObserver = function() { };
Abstract.TimedObserver.prototype = {
  initialize: function(element, frequency, callback) {
    this.frequency = frequency;
    this.element   = $(element);
    this.callback  = callback;

    this.lastValue = this.getValue();
    this.registerCallback();
  },

  registerCallback: function() {
    setInterval(this.onTimerEvent.bind(this), this.frequency * 1000);
  },

  onTimerEvent: function() {
    var value = this.getValue();
    var changed = (Object.isString(this.lastValue) && Object.isString(value)
      ? this.lastValue != value : String(this.lastValue) != String(value));
    if (changed) {
      this.callback(this.element, value);
      this.lastValue = value;
    }
  }
};

Form.Element.Observer = Class.create();
Form.Element.Observer.prototype = Object.extend(new Abstract.TimedObserver(), {
  getValue: function() {
    return Form.Element.getValue(this.element);
  }
});

Form.Observer = Class.create();
Form.Observer.prototype = Object.extend(new Abstract.TimedObserver(), {
  getValue: function() {
    return Form.serialize(this.element);
  }
});

/*--------------------------------------------------------------------------*/

Abstract.EventObserver = function() { };
Abstract.EventObserver.prototype = {
  initialize: function(element, callback) {
    this.element  = $(element);
    this.callback = callback;

    this.lastValue = this.getValue();
    if (this.element.tagName.toLowerCase() == 'form')
      this.registerFormCallbacks();
    else
      this.registerCallback(this.element);
  },

  onElementEvent: function() {
    var value = this.getValue();
    if (this.lastValue != value) {
      this.callback(this.element, value);
      this.lastValue = value;
    }
  },

  registerFormCallbacks: function() {
    Form.getElements(this.element).each(this.registerCallback.bind(this));
  },

  registerCallback: function(element) {
    if (element.type) {
      switch (element.type.toLowerCase()) {
        case 'checkbox':
        case 'radio':
          Event.observe(element, 'click', this.onElementEvent.bind(this));
          break;
        default:
          Event.observe(element, 'change', this.onElementEvent.bind(this));
          break;
      }
    }
  }
};

Form.Element.EventObserver = Class.create();
Form.Element.EventObserver.prototype = Object.extend(new Abstract.EventObserver(), {
  getValue: function() {
    return Form.Element.getValue(this.element);
  }
});

Form.EventObserver = Class.create();
Form.EventObserver.prototype = Object.extend(new Abstract.EventObserver(), {
  getValue: function() {
    return Form.serialize(this.element);
  }
});
if (!window.Event) var Event = { };

Object.extend(Event, {
  KEY_BACKSPACE: 8,
  KEY_TAB:       9,
  KEY_RETURN:   13,
  KEY_ESC:      27,
  KEY_LEFT:     37,
  KEY_UP:       38,
  KEY_RIGHT:    39,
  KEY_DOWN:     40,
  KEY_DELETE:   46,
  KEY_HOME:     36,
  KEY_END:      35,
  KEY_PAGEUP:   33,
  KEY_PAGEDOWN: 34,
  KEY_INSERT:   45,

  DOMEvents: ['click', 'dblclick', 'mousedown', 'mouseup', 'mouseover',
              'mousemove', 'mouseout', 'keypress', 'keydown', 'keyup',
              'load', 'unload', 'abort', 'error', 'resize', 'scroll',
              'select', 'change', 'submit', 'reset', 'focus', 'blur',
              'DOMFocusIn', 'DOMFocusOut', 'DOMActivate',
              'DOMSubtreeModified', 'DOMNodeInserted',
              'NodeInsertedIntoDocument', 'DOMAttrModified',
              'DOMCharacterDataModified'],

  cache: { },

  relatedTarget: function(event) {
    var element;
    switch(event.type) {
      case 'mouseover': element = event.fromElement; break;
      case 'mouseout':  element = event.toElement;   break;
      default: return null;
    }
    return Element.extend(element);
  }
});

Event.Methods = {
  element: function(event) {
    var node = event.target;
    if (!node) node = event.srcElement;
    return Element.extend(node.nodeType == Node.TEXT_NODE ? node.parentNode : node);
  },

  findElement: function(event, expression) {
    var element = Event.element(event);
    return element.match(expression) ? element : element.up(expression);
  },

  isLeftClick: function(event) {
    return (((event.which) && (event.which == 1)) ||
            ((event.button) && (event.button == 1)));
  },

  pointer: function(event) {
    return {
      x: event.pageX || (event.clientX +
        (document.documentElement.scrollLeft || document.body.scrollLeft)),
      y: event.pageY || (event.clientY +
        (document.documentElement.scrollTop || document.body.scrollTop))
    };
  },

  pointerX: function(event) { return Event.pointer(event).x },
  pointerY: function(event) { return Event.pointer(event).y },

  stop: function(event) {
    if (event.preventDefault) {
      event.preventDefault();
      event.stopPropagation();
    } else {
      event.returnValue = false;
      event.cancelBubble = true;
    }
  }
};

Event.extend = (function() {
  var methods = Object.keys(Event.Methods).inject({ }, function(m, name) {
    m[name] = Event.Methods[name].methodize();
    return m;
  });

  if (Prototype.Browser.IE) {
    Object.extend(methods, {
      stopPropagation: function() { this.cancelBubble = true },
      preventDefault:  function() { this.returnValue = false },
      inspect: function() { return "[object Event]" }
    });

    return function(event) {
      if (!event) return false;
      if (event._extendedByPrototype) return event;

      event._extendedByPrototype = Prototype.emptyFunction;
      var pointer = Event.pointer(event);
      Object.extend(event, {
        target: event.srcElement,
        relatedTarget: Event.relatedTarget(event),
        pageX:  pointer.x,
        pageY:  pointer.y
      });
      return Object.extend(event, methods);
    };

  } else {
    Event.prototype = Event.prototype || document.createEvent("HTMLEvents").__proto__;
    Object.extend(Event.prototype, methods);
    return Prototype.K;
  }
})();

Object.extend(Event, (function() {
  var cache = Event.cache;

  function getEventID(element) {
    if (element._eventID) return element._eventID;
    arguments.callee.id = arguments.callee.id || 1;
    return element._eventID = ++arguments.callee.id;
  }

  function getDOMEventName(eventName) {
    if (!Event.DOMEvents.include(eventName)) return "dataavailable";
    return { keypress: "keydown" }[eventName] || eventName;
  }

  function getCacheForID(id) {
    return cache[id] = cache[id] || { };
  }

  function getWrappersForEventName(id, eventName) {
    var c = getCacheForID(id);
    return c[eventName] = c[eventName] || [];
  }

  function createWrapper(id, eventName, handler) {
    var c = getWrappersForEventName(id, eventName);
    if (c.pluck("handler").include(handler)) return false;

    var wrapper = function(event) {
      if (event.eventName && event.eventName != eventName)
        return false;

      Event.extend(event);
      handler.call(event.target, event);
    };

    wrapper.handler = handler;
    c.push(wrapper);
    return wrapper;
  }

  function findWrapper(id, eventName, handler) {
    var c = getWrappersForEventName(id, eventName);
    return c.find(function(wrapper) { return wrapper.handler == handler });
  }

  function destroyWrapper(id, eventName, handler) {
    var c = getCacheForID(id);
    if (!c[eventName]) return false;
    c[eventName] = c[eventName].without(findWrapper(id, eventName, handler));
  }

  function destroyCache() {
    for (var id in cache)
      for (var eventName in cache[id])
        cache[id][eventName] = null;
  }

  if (window.attachEvent) {
    window.attachEvent("onunload", destroyCache);
  }

  return {
    observe: function(element, eventName, handler) {
      element = $(element);
      var id = getEventID(element), name = getDOMEventName(eventName);

      var wrapper = createWrapper(id, eventName, handler);
      if (!wrapper) return false;

      if (element.addEventListener) {
        element.addEventListener(name, wrapper, false);
      } else {
        element.attachEvent("on" + name, wrapper);
      }
    },

    stopObserving: function(element, eventName, handler) {
      element = $(element);
      var id = getEventID(element), name = getDOMEventName(eventName);

      if (!handler && eventName) {
        return getWrappersForEventName(id, eventName).each(function(wrapper) {
          element.stopObserving(eventName, wrapper.handler);
        }) && false;

      } else if (!eventName) {
        return Object.keys(getCacheForID(id)).each(function(eventName) {
          element.stopObserving(eventName);
        }) && false;
      }

      var wrapper = findWrapper(id, eventName, handler);
      if (!wrapper) return false;

      if (element.removeEventListener) {
        element.removeEventListener(name, wrapper, false);
      } else {
        element.detachEvent("on" + name, wrapper);
      }

      destroyWrapper(id, eventName, handler);
    },

    fire: function(element, eventName, memo) {
      element = $(element);
      if (element == document && document.createEvent && !element.dispatchEvent)
        element = document.documentElement;

      if (document.createEvent) {
        var event = document.createEvent("HTMLEvents");
        event.initEvent("dataavailable", true, true);
      } else {
        var event = document.createEventObject();
        event.eventType = "ondataavailable";
      }

      event.eventName = eventName;
      event.memo = memo || { };

      if (document.createEvent) {
        element.dispatchEvent(event);
      } else {
        element.fireEvent(event.eventType, event);
      }

      return element;
    }
  };
})());

Object.extend(Event, Event.Methods);

Element.addMethods({
  fire: function() {
    Event.fire.apply(Event, arguments);
    return $A(arguments).first();
  },

  observe: function() {
    Event.observe.apply(Event, arguments);
    return $A(arguments).first();
  },

  stopObserving: function() {
    Event.stopObserving.apply(Event, arguments);
    return $A(arguments).first();
  }
});

Object.extend(document, {
  fire:          Element.Methods.fire.methodize(),
  observe:       Element.Methods.observe.methodize(),
  stopObserving: Element.Methods.stopObserving.methodize()
});

(function() {
  /* Support for the DOMContentLoaded event is based on work by Dan Webb,
     Matthias Miller, Dean Edwards and John Resig. */

  var timer, fired = false;

  function fireContentLoadedEvent() {
    if (fired) return;
    if (timer) window.clearInterval(timer);
    document.fire("contentloaded");
    fired = true;
  }

  if (document.addEventListener) {
    if (Prototype.Browser.WebKit) {
      timer = window.setInterval(function() {
        if (/loaded|complete/.test(document.readyState))
          fireContentLoadedEvent();
      }, 0);

      Event.observe(window, "load", fireContentLoadedEvent);

    } else {
      document.addEventListener("DOMContentLoaded", fireContentLoadedEvent, false);
    }

  } else {
    var dummy = location.protocol == "https:" ? "https://javascript:void(0)" : "javascript:void(0)";
    document.write("<script id=__onDOMContentLoaded defer src='" + dummy + "'><\/script>");
    $("__onDOMContentLoaded").onreadystatechange = function() {
      if (this.readyState == "complete") {
        this.onreadystatechange = null;
        fireContentLoadedEvent();
      }
    };
  }
})();
/*------------------------------- DEPRECATED -------------------------------*/

var Toggle = { display: Element.toggle };

Element.Methods.childOf = Element.Methods.descendantOf;

var Insertion = {
  Before: function(element, content) {
    return Element.insert(element, {before:content});
  },

  Top: function(element, content) {
    return Element.insert(element, {top:content});
  },

  Bottom: function(element, content) {
    return Element.insert(element, {bottom:content});
  },

  After: function(element, content) {
    return Element.insert(element, {after:content});
  }
};

var $continue = new Error('"throw $continue" is deprecated, use "return" instead');

// This should be moved to script.aculo.us; notice the deprecated methods
// further below, that map to the newer Element methods.
var Position = {
  // set to true if needed, warning: firefox performance problems
  // NOT neeeded for page scrolling, only if draggable contained in
  // scrollable elements
  includeScrollOffsets: false,

  // must be called before calling withinIncludingScrolloffset, every time the
  // page is scrolled
  prepare: function() {
    this.deltaX =  window.pageXOffset
                || document.documentElement.scrollLeft
                || document.body.scrollLeft
                || 0;
    this.deltaY =  window.pageYOffset
                || document.documentElement.scrollTop
                || document.body.scrollTop
                || 0;
  },

  // caches x/y coordinate pair to use with overlap
  within: function(element, x, y) {
    if (this.includeScrollOffsets)
      return this.withinIncludingScrolloffsets(element, x, y);
    this.xcomp = x;
    this.ycomp = y;
    this.offset = Element.cumulativeOffset(element);

    return (y >= this.offset[1] &&
            y <  this.offset[1] + element.offsetHeight &&
            x >= this.offset[0] &&
            x <  this.offset[0] + element.offsetWidth);
  },

  withinIncludingScrolloffsets: function(element, x, y) {
    var offsetcache = Element.cumulativeScrollOffset(element);

    this.xcomp = x + offsetcache[0] - this.deltaX;
    this.ycomp = y + offsetcache[1] - this.deltaY;
    this.offset = Element.cumulativeOffset(element);

    return (this.ycomp >= this.offset[1] &&
            this.ycomp <  this.offset[1] + element.offsetHeight &&
            this.xcomp >= this.offset[0] &&
            this.xcomp <  this.offset[0] + element.offsetWidth);
  },

  // within must be called directly before
  overlap: function(mode, element) {
    if (!mode) return 0;
    if (mode == 'vertical')
      return ((this.offset[1] + element.offsetHeight) - this.ycomp) /
        element.offsetHeight;
    if (mode == 'horizontal')
      return ((this.offset[0] + element.offsetWidth) - this.xcomp) /
        element.offsetWidth;
  },

  // Deprecation layer -- use newer Element methods now (1.5.2).

  cumulativeOffset: Element.Methods.cumulativeOffset,

  positionedOffset: Element.Methods.positionedOffset,

  absolutize: function(element) {
    Position.prepare();
    return Element.absolutize(element);
  },

  relativize: function(element) {
    Position.prepare();
    return Element.relativize(element);
  },

  realOffset: Element.Methods.cumulativeScrollOffset,

  offsetParent: Element.Methods.getOffsetParent,

  page: Element.Methods.viewportOffset,

  clone: function(source, target, options) {
    options = options || { };
    return Element.clonePosition(target, source, options);
  }
};

/*--------------------------------------------------------------------------*/

Element.ClassNames = Class.create();
Element.ClassNames.prototype = {
  initialize: function(element) {
    this.element = $(element);
  },

  _each: function(iterator) {
    this.element.className.split(/\s+/).select(function(name) {
      return name.length > 0;
    })._each(iterator);
  },

  set: function(className) {
    this.element.className = className;
  },

  add: function(classNameToAdd) {
    if (this.include(classNameToAdd)) return;
    this.set($A(this).concat(classNameToAdd).join(' '));
  },

  remove: function(classNameToRemove) {
    if (!this.include(classNameToRemove)) return;
    this.set($A(this).without(classNameToRemove).join(' '));
  },

  toString: function() {
    return $A(this).join(' ');
  }
};

Object.extend(Element.ClassNames.prototype, Enumerable);

/*--------------------------------------------------------------------------*/

Element.addMethods();

// effects.js
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

// generalTools.js
/*
 * General JS tools
 * Author: KK
 */

function BrowserDetectLite() {

	var ua = navigator.userAgent.toLowerCase();

	// browser name
	this.isGecko     = (ua.indexOf('gecko') != -1);
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
}

// one global instance of Browser detector 
var OB_bd = new BrowserDetectLite();

GeneralBrowserTools = new Object();

/**
 * Returns the cookie value for the given key
 */
GeneralBrowserTools.getCookie = function (name) {
    var value=null;
    if(document.cookie != "") {
      var kk=document.cookie.indexOf(name+"=");
      if(kk >= 0) {
        kk=kk+name.length+1;
        var ll=document.cookie.indexOf(";", kk);
        if(ll < 0)ll=document.cookie.length;
        value=document.cookie.substring(kk, ll);
        value=unescape(value); 
      }
    }
    return value;
  }
  
 GeneralBrowserTools.setCookieObject = function(key, object) {
 	var json = Object.toJSON(object);
 	document.cookie = key+"="+json; 
 }
 
 GeneralBrowserTools.getCookieObject = function(key) {
 	var json = GeneralBrowserTools.getCookie(key);
 	return json != null ? json.evalJSON(false) : null;
 }
  
GeneralBrowserTools.selectAllCheckBoxes = function(formid) {
	var form = $(formid)
	var checkboxes = form.getInputs('checkbox');
	checkboxes.each(function(cb) { cb.checked = !cb.checked});
}

GeneralBrowserTools.getSelectedText = function (textArea) {
 if (OB_bd.isGecko) {
	var selStart = textArea.selectionStart;
	var selEnd = textArea.selectionEnd;
    var text = textArea.value.substring(selStart, selEnd);
 } else if (OB_bd.isIE) {
	var text = document.selection.createRange().text;
 }
 return text;
}

/*
 * checks if some text is selected.
 */
GeneralBrowserTools.isTextSelected = function (inputBox) {
	if (OB_bd.isGecko) {
		if (inputBox.selectionStart != inputBox.selectionEnd) {
			return true;
		}
	} else if (OB_bd.isIE) {
		if (document.selection.createRange().text.length > 0) {
			return true;
		}
	}
	return false;
}
/**
 * Purge method for removing DOM elements in IE properly 
 * and *without* memory leak. Harmless to Mozilla/FF/Opera
 */
GeneralBrowserTools.purge = function (d) {
    var a = d.attributes, i, l, n;
    if (a) {
        l = a.length;
        for (i = 0; i < l; i += 1) {
            n = a[i].name;
            if (typeof d[n] === 'function') {
                d[n] = null;
            }
        }
    }
    a = d.childNodes;
    if (a) {
        l = a.length;
        for (i = 0; i < l; i += 1) {
            GeneralBrowserTools.purge(d.childNodes[i]);
        }
    }
}

GeneralBrowserTools.getURLParameter = function (paramName) {
  var queryParams = location.href.toQueryParams();
  return queryParams[paramName];
}

/*
 * ns: namespace, e.g. Category. May be null.
 * name: name of article
 */
GeneralBrowserTools.navigateToPage = function (ns, name) {
	var articlePath = wgArticlePath.replace(/\$1/, ns != null ? ns+":"+name : name);
	window.open(wgServer + articlePath, "");
}

GeneralBrowserTools.toggleHighlighting = function  (oldNode, newNode) {
	if (oldNode) {
		Element.removeClassName(oldNode,"selectedItem");
	}
	Element.addClassName(newNode,"selectedItem");
	return newNode;
	
}

GeneralBrowserTools.repasteMarkup = function(attribute) {
	if (Prototype.BrowserFeatures.XPath) {
		// FF supports DOM 3 XPath. That makes things easy and blazing fast...
		// Browser which don't support XPath do nothing here
		var nodesWithID = document.evaluate("//*[@"+attribute+"=\"true\"]", document, null, XPathResult.ANY_TYPE,null); 
		var node = nodesWithID.iterateNext(); 
		var nodes = new Array();
		var i = 0;
		while (node != null) {
			nodes[i] = node;
			node = nodesWithID.iterateNext(); 
			i++;
		}
		nodes.each(function(n) {
			var textContent = n.textContent;
			n.innerHTML = textContent; 
		});
	}
}

// ------------------------------------------------------
// General Tools is a Utility class.
GeneralXMLTools = new Object();


/**
 * Creates an XML document with a treeview node as root node.
 */
GeneralXMLTools.createTreeViewDocument = function() {
	 // create empty treeview
   if (OB_bd.isGeckoOrOpera) {
   	 var parser=new DOMParser();
     var xmlDoc=parser.parseFromString("<result/>","text/xml");
   } else if (OB_bd.isIE) {
   	 var xmlDoc = new ActiveXObject("Microsoft.XMLDOM") 
     xmlDoc.async="false"; 
     xmlDoc.loadXML("<result/>");   
   }
   return xmlDoc;
}

/**
 * Creates an XML document from string
 */
GeneralXMLTools.createDocumentFromString = function (xmlText) {
	 // create empty treeview
   if (OB_bd.isGeckoOrOpera) {
   	 var parser=new DOMParser();
     var xmlDoc=parser.parseFromString(xmlText,"text/xml");
   } else if (OB_bd.isIE) {
   	 var xmlDoc = new ActiveXObject("Microsoft.XMLDOM") 
     xmlDoc.async="false"; 
     xmlDoc.loadXML(xmlText);   
   }
   return xmlDoc;
}

/*
 * Adds a branch to the current document. Ignoring document node and root node.
 * Removes the expanded attribute for leaf nodes.
 * branch: array of nodes
 * xmlDoc: document to add branch to
 */
GeneralXMLTools.addBranch = function (xmlDoc, branch) {
	var currentNode = xmlDoc;
	// ignore document and root node
	for (var i = branch.length-3; i >= 0; i-- ) {
		currentNode = GeneralXMLTools.addNodeIfNecessary(branch[i], currentNode);
	}
	if (!currentNode.hasChildNodes()) {
		currentNode.removeAttribute("expanded");
	}
}

/*
 * Add the node if a child with same title does not exist.
 * nodeToAdd:
 * parentNode:
 */
GeneralXMLTools.addNodeIfNecessary = function (nodeToAdd, parentNode) {
	var a1 = nodeToAdd.getAttribute("title");
	for (var i = 0; i < parentNode.childNodes.length; i++) {
		if (parentNode.childNodes[i].getAttribute("title") == a1) {
			return parentNode.childNodes[i];
		}
	}
	
	var appendedChild = GeneralXMLTools.importNode(parentNode, nodeToAdd, false);
	return appendedChild;
}

/*
 * Import a node
 */
GeneralXMLTools.importNode = function(parentNode, child, deep) {
	var appendedChild;
	if (OB_bd.isGeckoOrOpera) {
		appendedChild = parentNode.appendChild(document.importNode(child, deep));
		
	} else if (OB_bd.isIE) {
		appendedChild = parentNode.appendChild(child.cloneNode(deep));

	}
	return appendedChild;
}

/* 
 * Search a node in the xml caching
 * node: root where search begins
 * id: id
 */
GeneralXMLTools.getNodeById = function (node, id) {
	if (Prototype.BrowserFeatures.XPath) {
		// FF supports DOM 3 XPath. That makes things easy and blazing fast...
		var nodeWithID = document.evaluate("//*[@id=\""+id+"\"]", node, null, XPathResult.ANY_TYPE,null); 
		return nodeWithID.iterateNext(); // there *must* be only one
	} else if (OB_bd.isIE) {
		// IE supports XPath in a proprietary way
		return node.selectSingleNode("//*[@id=\""+id+"\"]");
	} else {
	// otherwise do a depth first search:
	var children = node.childNodes;
	var result;
	if (children.length == 0) { return null; }
	for (var i=0, n = children.length; i < n;i++) {
		if (children[i].getAttribute("id")) {
			
			if (children[i].getAttribute("id") == id) {
				return children[i];
			}
		}
    	result = GeneralXMLTools.getNodeById(children[i], id);
    	if (result != null) {
    		return result;
    	}
	}
	return result;
	}
}



/*
 * Import a subtree
 * nodeToImport: node to which the subtree is appended.
 * subTree: node which children are imported.
 */ 
GeneralXMLTools.importSubtree = function (nodeToImport, subTree) {
	for (var i = 0; i < subTree.childNodes.length; i++) {
			GeneralXMLTools.importNode(nodeToImport, subTree.childNodes[i], true);
	}
}

/*
 * Remove all children of a node.
 */
GeneralXMLTools.removeAllChildNodes = function (node) {
	if (node.firstChild) {
		child = node.firstChild;
		do {
			nextSibling = child.nextSibling;
			GeneralBrowserTools.purge(child); // important for IE. Prevents memory leaks.
			node.removeChild(child);
			child = nextSibling;
		} while (child!=null);
	}
}


/*
 * Get all parents of a node
 */
GeneralXMLTools.getAllParents = function (node) {
	var parentNodes = new Array();
	var count = 0;
	do {
		parentNodes[count] = node;
		node = node.parentNode;
		count++;
	} while (node != null);
	return parentNodes;
}


// ------ misc tools --------------------------------------------

GeneralTools = new Object();

GeneralTools.getEvent = function (event) {
	return event ? event : window.event;
}

GeneralTools.getImgDirectory = function (source) {
    return source.substring(0, source.lastIndexOf('/') + 1);
}


GeneralTools.splitSearchTerm = function (searchTerm) {
   	var filterParts = searchTerm.split(" ");
   	return filterParts.without('');
}

GeneralTools.matchArrayOfRegExp = function (term, regexArray) {
	var doesMatch = true;
    for(var j = 0, m = regexArray.length; j < m; j++) {
    	if (regexArray[j].exec(term) == null) {
    		doesMatch = false;
    		break;
    	}
    }
    return doesMatch;
}
  




var OBPendingIndicator = Class.create();
OBPendingIndicator.prototype = {
	initialize: function(container) {
		this.container = container;
		this.pendingIndicator = document.createElement("img");
		Element.addClassName(this.pendingIndicator, "obpendingElement");
		this.pendingIndicator.setAttribute("src", wgServer + wgScriptPath + "/extensions/SemanticMediaWiki/skins/OntologyBrowser/images/ajax-loader.gif");
		//this.pendingIndicator.setAttribute("id", "pendingAjaxIndicator_OB");
		//this.pendingIndicator.style.left = (Position.cumulativeOffset(this.container)[0]-Position.realOffset(this.container)[0])+"px";
		//this.pendingIndicator.style.top = (Position.cumulativeOffset(this.container)[1]-Position.realOffset(this.container)[1])+"px";
		//this.hide();
		//Indicator will not be added to the page on creation anymore but on fist time calling show
		//this is preventing errors during add if contentelement is not yet available  
		this.contentElement = null;
	},
	
	/**
	 * Shows pending indicator relative to given container or relative to initial container
	 * if container is not specified.
	 */
	show: function(container) {
		//check if the content element is there
		if($("content") == null){
			return;
		}
		//if not already done, append the indicator to the content element so it can become visible
		if(this.contentElement == null) {
				this.contentElement = $("content");
				this.contentElement.appendChild(this.pendingIndicator);
		}
		if (!container) {
			this.pendingIndicator.style.left = (Position.cumulativeOffset(this.container)[0]-Position.realOffset(this.container)[0])+"px";
			this.pendingIndicator.style.top = (Position.cumulativeOffset(this.container)[1]-Position.realOffset(this.container)[1]+this.container.scrollTop)+"px";
		} else {
			this.pendingIndicator.style.left = (Position.cumulativeOffset($(container))[0]-Position.realOffset($(container))[0])+"px";
			this.pendingIndicator.style.top = (Position.cumulativeOffset($(container))[1]-Position.realOffset($(container))[1]+$(container).scrollTop)+"px";
		}
		// hmm, why does Element.show(...) not work here?
		this.pendingIndicator.style.display="block";
		this.pendingIndicator.style.visibility="visible";

	},
	
	hide: function() {
		Element.hide(this.pendingIndicator);
	}
}

// SMW_Language.js
/**
* SMW_Language.js
* 
* A class that reads language strings from the server by an ajax call.
* 
* @author Thomas Schweitzer
*
*/

var Language = Class.create();

/**
 * This class provides language dependent strings for an identifier.
 * 
 */
Language.prototype = {

	/**
	 * @public
	 * 
	 * Constructor.
	 */
	initialize: function() {
	},

	/*
	 * @public
	 * 
	 * Returns a language dependent message for an ID, or the ID, if there is 
	 * no message for it.
	 * 
	 * @param string id
	 * 			ID of the message to be retrieved.
	 * @return string
	 * 			The language dependent message for the given ID.
	 */
	getMessage: function(id) {
		var msg = wgLanguageStrings[id];
		if (!msg) {
			msg = id;
		}
		// Replace variables
		msg = msg.replace(/\$n/g,wgCanonicalNamespace); 
		msg = msg.replace(/\$p/g,wgPageName);
		msg = msg.replace(/\$t/g,wgTitle);
		msg = msg.replace(/\$u/g,wgUserName);
		msg = msg.replace(/\$s/g,wgServer);
		return msg;
	}
	
}

// Singleton of this class

var gLanguage = new Language();

// treeview.js

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
  	myXMLHTTPRequest.open("GET", wgServer + wgScriptPath + "/extensions/SemanticMediaWiki/skins/OntologyBrowser/treeview.xslt", false);
 		myXMLHTTPRequest.send(null);


  	var xslRef = myXMLHTTPRequest.responseXML;
 
  	// Finally import the .xsl
  	this.OB_xsltProcessor_gecko.importStylesheet(xslRef);
  	this.OB_xsltProcessor_gecko.setParameter(null, "param-img-directory", wgServer + wgScriptPath + "/extensions/SemanticMediaWiki/skins/OntologyBrowser/images/");
  } else if (OB_bd.isIE) {
  
    // create MSIE DOM object
  	var xsl = new ActiveXObject("MSXML2.FreeThreadedDOMDocument");
		xsl.async = false;
		
		// load stylesheet
		xsl.load(wgServer + wgScriptPath + "/extensions/SemanticMediaWiki/skins/OntologyBrowser/treeview.xslt");
		
		// create XSLT Processor
		var template = new ActiveXObject("MSXML2.XSLTemplate");
		template.stylesheet = xsl;
		this.OB_xsltProcessor_ie = template.createProcessor();
		this.OB_xsltProcessor_ie.addParameter("param-img-directory", wgServer + wgScriptPath + "/extensions/SemanticMediaWiki/skins/OntologyBrowser/images/");
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
		GeneralBrowserTools.navigateToPage(gLanguage.getMessage('PROPERTY_NS_WOC'), attributeName);
	},
	
	navigateToRelation: function(event, node, relationName) {
		GeneralBrowserTools.navigateToPage(gLanguage.getMessage('PROPERTY_NS_WOC'), relationName);
	},
	
	selectAttribute: function(event, node, attributeName) {
		var categoryDIV = $("categoryTree");
		var instanceDIV = $("instanceList");
		
		OB_oldSelectedAttributeNode = GeneralBrowserTools.toggleHighlighting(OB_oldSelectedAttributeNode, node);
		
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
		if (OB_LEFT_ARROW == 0) {
			OB_LEFT_ARROW = 1;
			img.setAttribute("src",wgScriptPath+"/extensions/SemanticMediaWiki/skins/OntologyBrowser/images/bigarrow_left.gif");
		} else {
			OB_LEFT_ARROW = 0;
			img.setAttribute("src",wgScriptPath+"/extensions/SemanticMediaWiki/skins/OntologyBrowser/images/bigarrow.gif");
		}
	},
	
	toogleInstPropArrow: function(event) {
		var img = Event.element(event);
		if (OB_RIGHT_ARROW == 0) {
			OB_RIGHT_ARROW = 1;
			img.setAttribute("src",wgScriptPath+"/extensions/SemanticMediaWiki/skins/OntologyBrowser/images/bigarrow_left.gif");
		} else {
			OB_RIGHT_ARROW = 0;
			img.setAttribute("src",wgScriptPath+"/extensions/SemanticMediaWiki/skins/OntologyBrowser/images/bigarrow.gif");
		}
	}
}


	





// treeviewData.js
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
	var title = GeneralBrowserTools.getURLParameter("title");
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

filterBrowserProperties: function(title) {
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

