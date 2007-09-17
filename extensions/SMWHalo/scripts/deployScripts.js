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

// smw_logger.js
/**
 *  Logger - logs msgs to the database 
 */

var Logger = Class.create();
Logger.prototype = {
	
	/**
	* default constructor
	* Constructor
	*
	*/
	initialize: function() {
	},
	
	/**
	 * Logs msgs through Ajax
	 * * @param 
	 * 
	 * Remote function in php is:
	 * smwLog($logmsg, $errortype = "" , $timestamp = "",$userid = "",$location="", $function="")
	 * 
	 */
	log: function(logmsg, type, func){
		
		//Default values
		var logmsg = (logmsg == null) ? "" : logmsg; 
		var type = (type == null) ? "" : type; 
			//Get Timestamp
			var time = new Date();
			var timestamp = time.toGMTString();
		var userid = (wgUserName == null) ? "" : wgUserName; 
		var location = (wgPageName == null) ? "" : wgPageName; 
		var func= (func == null) ? "" : func;
		
		sajax_do_call('smwLog', 
		              [logmsg,type,timestamp,userid,location,func], 
		              this.logcallback.bind(this));	
	},
	
	/**
	 * Shows alert if logging failed
	 * * @param ajax xml returnvalue
	 */
	logcallback: function(param) {
		if(param.status!=200){
			alert('logging failed: ' + param.statusText);
		}
	}
	
}

var logger = new Logger();

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
		this.readLanguageStrings();
		this.languageStrings = new Object();
	},


	/**
	 * @private
	 * 
	 * Reads all language dependent strings and their identifiers from 
	 * the server.
	 */
	readLanguageStrings : function() {
		function ajaxResponseReadLanguageStrings(request) {
			if (request.status != 200) {
				// call failed, do nothing.
				return;
			}
			
			var answer = request.responseText;
			var pairs = answer.split('<==');
			if (!pairs) {
				return;
			}
			for (var i = 0, len = pairs.length; i < len; ++i) {
				var idString = pairs[i].split("==>");
				if (idString) {
					this.languageStrings[idString[0]] = idString[1];
				}
			}
			
		};
		
		sajax_do_call('smwfGetLanguageStrings', 
		              [], 
		              ajaxResponseReadLanguageStrings.bind(this));
		              
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
		if (!this.languageStrings[id]) {
			return id;
		}
		var msg = this.languageStrings[id];
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

// STB_Framework.js
var ToolbarFramework = Class.create();

var HELPCONTAINER = 0; // contains help
var FACTCONTAINER = 1; // contains already annotated facts
var EDITCONTAINER = 2; // contains Linklist
var TYPECONTAINER = 3; // contains datatype selector on attribute pages
var CATEGORYCONTAINER = 4; // contains categories
var ATTRIBUTECONTAINER = 5; // contains attrributes
var RELATIONCONTAINER = 6; // contains relations
var PROPERTIESCONTAINER = 7; // contains the properties of attributes and relations
var CBSRCHCONTAINER = 8; // contains combined search functions
var COMBINEDSEARCHCONTAINER = 9;
var DBGCONTAINER = 10; // contains debug information

ToolbarFramework.prototype = {

	/**
	 * @public
	 *
	 * Constructor.
	 */

	stbconstructor : function() {
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


		this.var_onto = $("ontomenuanchor");
		this.var_onto.innerHTML += "<div id=\"tabcontainer\"></div>";
		this.var_onto.innerHTML += "<div id=\"activetabcontainer\"></div>";
		this.var_onto.innerHTML += "<div id=\"semtoolbar\"></div>";

		// create empty container (to preserve order of containers)

		this.var_stb = $("semtoolbar");
		if (this.var_stb) {
			for(var i=0;i<=10;i++) {
				this.var_stb.innerHTML += "<div id=\"stb_cont"+i+"-headline\"></div>";
				this.var_stb.innerHTML += "<div id=\"stb_cont"+i+"-content\"></div>";
				$("stb_cont"+i+"-headline").hide();
				$("stb_cont"+i+"-content").hide();
			}
		}
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

		if (contnum == HELPCONTAINER && this.cookieHelpTab != null) {
			this.contarray[contnum].setVisibility(this.cookieHelpTab);
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

// STB_Divcontainer.js
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

// wick.js
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

function autoCompletionsOptions(request) { autoCompleter.autoTriggering = request.responseText.indexOf('auto') != -1; document.cookie = "AC_mode="+request.responseText; }

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
            var selection_range = document.selection.createRange();

            var selection_rangeWhole = document.selection.createRange();
            selection_rangeWhole.moveToElementText(this.siw.inputBox);

            selection_range.setEndPoint("StartToStart", selection_rangeWhole);
            return selection_range.text;
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
        } else if (OB_bd.isGecko && this.siw.inputBox.tagName == 'TEXTAREA') {
            var userInput = this.getUserInputToMatch();

             // save scroll position
            var scrollTop = this.siw.inputBox.scrollTop;

             // get text before and after user input which is to be matched.
            var start = this.siw.inputBox.selectionStart;
            var pre = this.siw.inputBox.value.substring(0, start - userInput.length);
            var suf = this.siw.inputBox.value.substring(start);

             // insert text
            theString = pre + addedValue + suf;
            this.siw.inputBox.value = theString;

             // set the cursor behind the inserted text
            this.siw.inputBox.selectionStart = start + addedValue.length - userInput.length;
            this.siw.inputBox.selectionEnd = start + addedValue.length - userInput.length;

             // set old scroll position
            this.siw.inputBox.scrollTop = scrollTop;
        } else {
        	var pasteNS = this.currentInputBox != null ? this.currentInputBox.getAttribute("pasteNS") : null;
            theString = (baseValue ? baseValue : "") + addedValue;
        	if (pasteNS != null) {
        		switch(type) {
        			
        			case SMW_PROPERTY_NS: theString = "Property:"+theString; break;
        			case SMW_CATEGORY_NS: theString = "Category:"+theString; break;
        			case SMW_TEMPLATE_NS: theString = "Template:"+theString; break;
        			case SMW_TYPE_NS: theString = "Type:"+theString; break;
        		}
        	}
            this.siw.inputBox.value = theString;
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
             //textinHeader.setAttribute("src", wgServer + wgScriptPath + "/extensions/SemanticMediaWiki/skins/Autocompletion/clicktodrag.gif");
            textinHeader.setAttribute("style", "margin-left:5px;");
            textinHeader.innerHTML = "Auto-Completion - Click here to drag";

            var cross = document.createElement("img");
            Element.addClassName(cross, "closeFloater");
            cross.setAttribute("src",
                wgServer + wgScriptPath + "/extensions/SemanticMediaWiki/skins/Autocompletion/close.gif");
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
        acMessage.innerHTML = "Press Ctrl+Alt+Space to use auto-completion. (Ctrl+Space in IE) ";
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
            wgServer + wgScriptPath + "/extensions/SemanticMediaWiki/skins/Autocompletion/pending.gif");
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
                + "/extensions/SemanticMediaWiki/skins/Autocompletion/instance.gif\">";
        } else if (_type == SMW_CATEGORY_NS) {
            return "<img src=\"" + wgServer + wgScriptPath
                + "/extensions/SemanticMediaWiki/skins/Autocompletion/concept.gif\">";
        } else if (_type == SMW_PROPERTY_NS) {
            return "<img src=\"" + wgServer + wgScriptPath
                + "/extensions/SemanticMediaWiki/skins/Autocompletion/property.gif\">";
        } else if (_type == SMW_TEMPLATE_NS) {
            return "<img src=\"" + wgServer + wgScriptPath
                + "/extensions/SemanticMediaWiki/skins/Autocompletion/template.gif\">";
        } else if (_type == SMW_TYPE_NS) {
            return "<img src=\"" + wgServer + wgScriptPath
                + "/extensions/SemanticMediaWiki/skins/Autocompletion/template.gif\">"; // FIXME: separate icon for TYPE namespace
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

 // Get preference options
var AC_mode = GeneralBrowserTools.getCookie("AC_mode");
if (AC_mode == null) {
	sajax_do_call('smwfAutoCompletionOptions', [], autoCompletionsOptions);
} else {
	autoCompleter.autoTriggering = (AC_mode == 'auto');
}

// SMW_Help.js
Event.observe(window, 'load', callme);

var initHelp = function(){
	var ns = wgNamespaceNumber==0?"Main":wgCanonicalNamespace ;
	if (wgNamespaceNumber == -1 && wgCanonicalSpecialPageName == "Search"){
		ns = "Search";
	}
	sajax_do_call('smwfGetHelp', [ns , wgAction], displayHelp);
}

function callme(){
	if(wgAction == "edit" || wgCanonicalSpecialPageName == "Search"){
		helpcontainer = stb_control.createDivContainer(HELPCONTAINER, 0);
		helpcontainer.setHeadline("Help");
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

// SMW_Links.js
Event.observe(window, 'load', callme);

var createLinkList = function() {
	sajax_do_call('getLinks', [wgArticleId], addLinks);
}

function callme(){
	if(wgAction == "edit"){
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

// Annotation.js
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
		var newAnnotation = "[[" + this.prefix + "Category:" + newCategoryName;
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
		var newAnnotation = "[[" + this.prefix + "Category:" + this.name;
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
	 	var anno = "[[Category:" + name;
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
				alert("Error while querying existence of article " + pageName +".");
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
			schema += "\n[[SMW_SSP_HAS_DOMAIN_HINT::Category:"+domain+"]]";
		}
		if (type != null && type != "") {
			schema += "\n[[SMW_SP_HAS_TYPE::Type:"+type+"]]";
		}
		this.createArticle("Property:"+title, 
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
			schema += "\n[[SMW_SSP_HAS_DOMAIN_HINT::Category:"+domain+"]]";
		}
		if (ranges != null) {
			if (ranges.length == 1) { // normal binary relation
					if (ranges[0].indexOf("Type:") == 0) {
						schema += "\n[[SMW_SP_HAS_TYPE::"+ranges[0]+"]]";
					} else {
						schema += "\n[[SMW_SSP_HAS_RANGE_HINT::"+ranges[0]+"]]";
					}
			 	
			} else if (ranges.length > 1) { // n-ary relation
				var rangeStr = "\n[[SMW_SP_HAS_TYPE:="
				for(var i = 0, n = ranges.length; i < n; i++) {
					if (ranges[i].indexOf("Type:") == 0) {
						if (i < n-1) rangeStr += ranges[i]+";"; else rangeStr += ranges[i];
					} else {
						if (i < n-1) rangeStr += "Type:Page;"; else rangeStr += "Type:Page";
						schema += "\n[[SMW_SSP_HAS_RANGE_HINT::"+ranges[i]+"]]";
					}
			 	}
			 	schema += rangeStr+"]]";
			} 
		}
		
		this.createArticle("Property:"+title, 
						   initialContent, schema,
						   "Create a relation for category " + domain, true);
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
		this.createArticle("Category:"+title, 
						   initialContent, "",
						   "Create a category. ", true);
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
	 */
	createSubProperty : function(title, initialContent) {
		var schemaProp = this.getSchemaProperties();
		if (   wgNamespaceNumber == 102    // SMW_NS_PROPERTY
		    || wgNamespaceNumber == 100) { // SMW_NS_RELATION
			this.createArticle("Property:"+title, 
							 initialContent, 
							 schemaProp + 
							 "\n[[SMW_SP_SUBPROPERTY_OF::"+wgPageName+"]]",
							 "Create a sub-property", true);
			
		}/* else if (wgNamespaceNumber == SMW_NS_RELATION) {
			this.createArticle("Relation:"+title, 
							 initialContent, 
							 schemaProp + 
							 "\n[[SMW_SP_SUBPROPERTY_OF::"+wgPageName+"]]",
							 "Create a sub-relation", true);
		}*/ else {
			alert("The current article is neither relation nor attribute.")
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
	 */
	createSuperProperty : function(title, initialContent) {
		var schemaProp = this.getSchemaProperties();
		var wtp = new WikiTextParser();
		if (   wgNamespaceNumber == 102 // SMW_NS_PROPERTY
		    || wgNamespaceNumber == 100) {  // SMW_NS_RELATION
			this.createArticle("Property:"+title, 
							 initialContent, 
							 schemaProp,
							 "Create a super-property", true);
							 
			// append the sub-property annotation to the current article
			wtp.addRelation("subproperty of", "Property:"+title, "", true);
			
		}/* else if (wgNamespaceNumber == 100) {
			this.createArticle("Relation:"+title, 
							 initialContent, 
							 schemaProp,
							 "Create a super-relation", true);
			wtp.addRelation("subproperty of", "Relation:"+title, "", true);
		}*/ else {
			alert("The current article is neither relation nor attribute.")
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
	 */
	createSuperCategory : function(title, initialContent) {
		var wtp = new WikiTextParser();
		if (wgNamespaceNumber == 14) {
			this.createArticle("Category:"+title, initialContent, "",
							   "Create a super-category", true);
							 
			// append the sub-category annotation to the current article
			wtp.addCategory(title, "", true);
			
		} else {
			alert("The current article is not a category.")
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
		var wtp = new WikiTextParser();
		if (wgNamespaceNumber == 14) {
			this.createArticle("Category:"+title, initialContent, 
			                   "[[Category:"+wgTitle+"]]",
							   "Create a sub-category", true);			
		} else {
			alert("The current article is not a category.")
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
		props.push(wtp.getAttribute("Has max cardinality"));
		props.push(wtp.getAttribute("Has min cardinality"));
		
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
			alert("Error while creating article.");
			return;
		}
		
		var answer = request.responseText;
		var regex = /(true|false),(true|false),(.*)/;
		var parts = answer.match(regex);
		
		if (parts == null) {
			alert("Error while creating article.");
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
					"<a href=\"javascript:catToolBar.newItem()\" class=\"menulink\">Annotate</a>" +
					"<a href=\"javascript:catToolBar.newCategory()\" class=\"menulink\">Create</a>";
			if (wgNamespaceNumber == 14) {
				divlist += "<a href=\"javascript:catToolBar.CreateSubSup()\" class=\"menulink\">Sub/Super</a>";
			}
			divlist += "</div>";
	 		break;
		case "relation":
	  			divlist ="<div id=\"" + id +"-tools\">" +
	  					 "<a href=\"javascript:relToolBar.newItem()\" class=\"menulink\">Annotate</a>" +
					 "<a href=\"javascript:relToolBar.newRelation()\" class=\"menulink\">Create</a>";
				//regex for checking attribute namespace. 
				//since there's no special namespace number anymore since atr and rel are united 
				var attrregex =	new RegExp("Attribute:.*");
				if (wgNamespaceNumber == 100 || wgNamespaceNumber == 102  || attrregex.exec(wgPageName) != null) {
					divlist += "<a href=\"javascript:relToolBar.CreateSubSup()\" class=\"menulink\">Sub/Super</a>";
				}
	  			divlist += "<a href=\"javascript:relToolBar.newPart()\" class=\"menulink\">Has part</a>";
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
	  			firstValue = list[i].getValue ? this.cutdowntosize(list[i].getValue(),7,3) : "";
	  			prefix = "Category:";
	 			 break
			case "relation":
	  			fn = "relToolBar.getselectedItem(" + i + ")";
	  			prefix = "Property:";
	  		
	  			var rowSpan = 'rowspan="'+(list[i].getArity()-1)+'"';
	  			var values = list[i].getSplitValues();
	  			firstValue = this.cutdowntosize(values[0],7,3);
	  			var valueLink;
/* No links for values at the moment
				valueLink = '<a href="' + wgServer + path + values[0] +
				            '" target="blank">' + firstValue + '</a>';
				firstValue = valueLink;
*/	  			
	  			// HTML of parameter rows (except first)
	  			for (var j = 1, n = list[i].getArity()-1; j < n; j++) {
					valueLink = 
//No links for values at the moment						'<a href="' + wgServer + path + values[j] +
//					    '" target="blank">' + this.cutdowntosize(values[j],10,3) +
//					    '</a>';
						this.cutdowntosize(values[j],8,3);
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
		var shortName = (list[i].getValue 
					 ? this.cutdowntosize(list[i].getName(),7) 
					 : this.cutdowntosize(list[i].getName(),16));
		var elemName;
		//Construct the link
		elemName = '<a href="'+wgServer+path+prefix+list[i].getName();
		elemName += '" target="blank">' + shortName + '</a>';
		divlist += 	"<tr>" +
				"<td "+rowSpan+" class=\"" + id + "-col1\">" + 
					elemName + 
				" </td>" +
				"<td class=\"" + id + "-col2\">" + firstValue + " </td>" + // first value row
		           	"<td "+rowSpan+" class=\"" + id + "-col3\">" +
		           	'<a href=\"javascript:' + fn + '">' +
		           	'<img src="' + wgScriptPath  + '/extensions/SemanticMediaWiki/skins/edit.gif"/></a>' +
		           
		           	'</tr>' + multiValue; // all other value rows
  	}
  	divlist += "</table></div>";
  	return divlist;
},

cutdowntosize: function(word, size /*, Optional: maxrows */ ){
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
			value = target.options[target.selectedIndex].innerText;
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
				checkName = "Category:"+value;
				break;
			case 'property':
				checkName = "Property:"+value;
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
            'value="' + initialContent + '" '+
            'tabindex="'+ this.lastindex++ +'" />'+ 
			'<a href="javascript:' + deleteCallback + '">' +
			'<img src="' + 
			wgScriptPath + 
			'/extensions/SemanticMediaWiki/skins/redcross.gif"/>';				 	
	} else {
		containercontent += 
			'<input class="wickEnabled stb-input ' + this.id + '-input" ' +
			'id="'+ id + '" '+
			attributes + 
			' type="text" ' +
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
		containercontent += '<a href="javascript:' + deleteCallback + '"><img src="' + wgScriptPath  + '/extensions/SemanticMediaWiki/skins/redcross.gif"/>';				 	
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
					'<form class="stb-checkbox ' + this.id + '-checkbox" id="' + id +'"  ' + attributes + '">';
	
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
					imgtag = '<img src="' + wgScriptPath  + '/extensions/SemanticMediaWiki/skins/info.gif"/>';
		  			break
				case (image = '(w)'):
					//TODO: Error Icon should be replaced by a prober one
		  			imgtag = '<img src="' + wgScriptPath  + '/extensions/SemanticMediaWiki/skins/warning.png"/>';
		  			break
				case (image = '(e)'):
					//TODO: Error Icon should be replaced by a prober one
		  			imgtag = '<img src="' + wgScriptPath  + '/extensions/SemanticMediaWiki/skins/delete_icon.png"/>';
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
	this.append(this.createLink(705,[['logger.log(\'Testlog\',\'error\',\'log\');','Log']],'',true));
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

},

showToolbar: function(request){
	this.categorycontainer.setHeadline("Categories");
	this.wtp = new WikiTextParser();
	this.om = new OntologyModifier();
	this.fillList(true);
},

callme: function(event){
	if(wgAction == "edit"){
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
	this.toolbarContainer.hideSandglass();
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
   this.wtp.initialize();
   var name = $("cat-name").value;
   this.wtp.addCategory(name, true);
   this.fillList(true);
},

newItem: function() {
	var html;
	
	this.showList = false;
	
    this.wtp.initialize();
	var selection = this.wtp.getSelection();

	var tb = this.createToolbar(SMW_CAT_ALL_VALID);	
	tb.append(tb.createText('cat-help-msg', 'Annotate a category.', '' , true));
	tb.append(tb.createInput('cat-name', 'Name:', '', '',
	                         SMW_CAT_CHECK_CATEGORY +
	                         SMW_CAT_CHECK_EMPTY +
	                         SMW_CAT_HINT_CATEGORY,
	                         true));
	tb.append(tb.createText('cat-name-msg', 'Please enter a name.', '' , true));
	var links = [['catToolBar.addItem()','Add', 'cat-confirm', 'Invalid values.', 'cat-invalid'],
				 ['catToolBar.cancel()', 'Cancel']
				];
	tb.append(tb.createLink('cat-links', links, '', true));
				
	tb.finishCreation();
	gSTBEventActions.initialCheck($("category-content-box"));
	//Sets Focus on first Element
	setTimeout("$('cat-name').focus();",50);
},


CreateSubSup: function() {
    var html;

	this.showList = false;

	var tb = this.createToolbar(SMW_CAT_SUB_SUPER_ALL_VALID);	
	tb.append(tb.createText('cat-help-msg', 'Define a sub- or super-category.', '' , true));
	tb.append(tb.createInput('cat-subsuper', 'Name:', '', '',
	                         SMW_CAT_SUB_SUPER_CHECK_CATEGORY +
	                         SMW_CAT_CHECK_EMPTY +
	                         SMW_CAT_HINT_CATEGORY,
	                         true));
	tb.append(tb.createText('cat-subsuper-msg', 'Please enter a name.', '' , true));
	
	tb.append(tb.createLink('cat-make-sub-link', 
	                        [['catToolBar.createSubItem()', 'Create sub', 'cat-make-sub']], 
	                        '', false));
	tb.append(tb.createLink('cat-make-super-link', 
	                        [['catToolBar.createSuperItem()', 'Create super', 'cat-make-super']],
	                        '', false));
	
	var links = [['catToolBar.cancel()', 'Cancel']];
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
								[['catToolBar.createSuperItem()', sub, 'cat-make-sub']],
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
	//Check if Inputbox is empty
	if(name=="" || name == null ){
		alert("Error! Inputbox is empty.");
		return;
	}
 	this.om.createSubCategory(name, "");
 	this.fillList(true);
},

createSuperItem: function() {
	var name = $("cat-subsuper").value;
	//Check if Inputbox is empty
	if(name=="" || name == null ){
		alert("Error! Inputbox is empty.");
		return;
	}
 	this.om.createSuperCategory(name, "");
 	this.fillList(true);
},


changeItem: function(selindex) {
	this.wtp.initialize();
	//Get new values
	var name = $("cat-name").value;
	//Get category
	var annotatedElements = this.wtp.getCategories();
	//change relation
	if(   (selindex!=null) 
	   && ( selindex >=0) 
	   && (selindex <= annotatedElements.length)  ){
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
		anno.remove("");
	}
	//show list
	this.fillList(true);
},


newCategory: function() {

    var html;
    
	this.showList = false;
    
	var tb = this.createToolbar(SMW_CAT_ALL_VALID);	
	tb.append(tb.createText('cat-help-msg', 'Create a new category.', '' , true));
	tb.append(tb.createInput('cat-name', 'Name:', '', '',
	                         SMW_CAT_CHECK_CATEGORY_IIE+SMW_CAT_CHECK_EMPTY,
	                         true));
	tb.append(tb.createText('cat-name-msg', 'Please enter a name.', '' , true));
		
	var links = [['catToolBar.createNewCategory()','Create', 'cat-confirm', 'Invalid name.', 'cat-invalid'],
				 ['catToolBar.cancel()', 'Cancel']
				];
	tb.append(tb.createLink('cat-links', links, '', true));
				
	tb.finishCreation();
	gSTBEventActions.initialCheck($("category-content-box"));
	//Sets Focus on first Element
	setTimeout("$('cat-name').focus();",50);
},

createNewCategory: function() {
	var catName = $("cat-name").value;
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

	this.showList = false;
	
	var tb = this.createToolbar(SMW_CAT_ALL_VALID);	
	tb.append(tb.createText('cat-help-msg', 'Change the annotation of a category.', '' , true));
	
	tb.append(tb.createInput('cat-name', 'Name:', annotatedElements[selindex].getName(), '',
	                         SMW_CAT_CHECK_CATEGORY +
	                         SMW_CAT_CHECK_EMPTY +
	                         SMW_CAT_HINT_CATEGORY,
	                         true));
	tb.append(tb.createText('cat-name-msg', 'Please enter a name.', '' , true));
		
	var links = [['catToolBar.changeItem(' + selindex +')', 'Change', 'cat-confirm', 'Invalid name.', 'cat-invalid'],
				 ['catToolBar.deleteItem(' + selindex +')', 'Delete'],
				 ['catToolBar.cancel()', 'Cancel']
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
	'typeHint="100" ';
	

RelationToolBar.prototype = {

initialize: function() {
	this.genTB = new GenericToolBar();
	this.toolbarContainer = null;
	this.showList = true;
},

showToolbar: function(){
	this.relationcontainer.setHeadline("Properties");
	this.wtp = new WikiTextParser();
	this.om = new OntologyModifier();
	this.fillList(true);

},

callme: function(event){
	if(wgAction == "edit"){
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
	//Check if Inputbox is empty
	if (name=="" || name == null ){
		alert("Error! Inputbox is empty.");
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
	var selection = this.wtp.getSelection();
	
	var tb = this.createToolbar(SMW_REL_ALL_VALID);	
	tb.append(tb.createText('rel-help_msg', 'Annotate a property.', '' , true));
	tb.append(tb.createInput('rel-name', 'Name:', '', '',
	                         SMW_REL_CHECK_PROPERTY_UPDATE_SCHEMA +
	                         SMW_REL_CHECK_EMPTY +
	                         SMW_REL_HINT_PROPERTY,
	                         true));
	tb.append(tb.createText('rel-name-msg', 'Please enter a name.', '' , true));
	tb.append(tb.createInput('rel-value-0', 'Page:', selection, '', 
							 SMW_REL_CHECK_EMPTY_NEV,
	                         true));
	tb.append(tb.createText('rel-value-0-msg', 'Annotated page/value', '' , true));
	tb.append(tb.createInput('rel-show', 'Show:', '', '', '', true));
	var links = [['relToolBar.addItem()','Add', 'rel-confirm', 'Invalid values.', 'rel-invalid'],
				 ['relToolBar.cancel()', 'Cancel']
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
				  tb.createText('rel-value-'+i+'-msg', 'Annotated page/value', '' , true));
		selection = "";
	}
	
	tb.finishCreation();
	gSTBEventActions.initialCheck($("relation-content-box"));
},

CreateSubSup: function() {
    var html;

	this.showList = false;
	var tb = this.createToolbar(SMW_REL_SUB_SUPER_ALL_VALID);	
	tb.append(tb.createText('rel-help-msg', 'Define a sub- or super-property.', '' , true));
	tb.append(tb.createInput('rel-subsuper', 'Name:', '', '',
	                         SMW_REL_SUB_SUPER_CHECK_PROPERTY+SMW_REL_CHECK_EMPTY,
	                         true));
	tb.append(tb.createText('rel-subsuper-msg', 'Please enter a name.', '' , true));
	
	tb.append(tb.createLink('rel-make-sub-link', 
	                        [['relToolBar.createSubItem()', 'Create sub', 'rel-make-sub']], 
	                        '', false));
	tb.append(tb.createLink('rel-make-super-link', 
	                        [['relToolBar.createSuperItem()', 'Create super', 'rel-make-super']],
	                        '', false));
	
	var links = [['relToolBar.cancel()', 'Cancel']];
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
								[['relToolBar.createSubItem()', sub, 'rel-make-sub']],
								'', true);
		tb.replace('rel-make-sub-link', lnk);
		lnk = tb.createLink('rel-make-super-link', 
							[['relToolBar.createSuperItem()', superContent, 'rel-make-super']],
							'', true);
		tb.replace('rel-make-super-link', lnk);
	}
},
	
createSubItem: function() {
	var name = $("rel-subsuper").value;
	//Check if Inputbox is empty
	if(name=="" || name == null ){
		alert("Error! Inputbox is empty.");
		return;
	}
 	this.om.createSubProperty(name, "");
 	this.fillList(true);
},

createSuperItem: function() {
	var name = $("rel-subsuper").value;
	//Check if Inputbox is empty
	if(name=="" || name == null ){
		alert("Error! Inputbox is empty.");
		return;
	}

 	this.om.createSuperProperty(name, "");
 	this.fillList(true);
},

newRelation: function() {
    var html;
    gDataTypes.refresh();
    
	this.showList = false;
	var domain = (wgNamespaceNumber == 14)
					? wgTitle  // current page is a category
					: "";
	var tb = this.createToolbar(SMW_REL_ALL_VALID);	
	tb.append(tb.createText('rel-help-msg', 'Create a new property.', '' , true));
	tb.append(tb.createInput('rel-name', 'Name:', '', '',
	                         SMW_REL_CHECK_PROPERTY_IIE+SMW_REL_CHECK_EMPTY,
	                         true));
	tb.append(tb.createText('rel-name-msg', 'Please enter a name.', '' , true));
	
	tb.append(tb.createInput('rel-domain', 'Domain:', '', '', 
						     SMW_REL_CHECK_CATEGORY + SMW_REL_CHECK_EMPTY_WIE,
	                         true));
	tb.append(tb.createText('rel-domain-msg', 'Enter a domain.', '' , true));
	
	tb.append(tb.createInput('rel-range-0', 'Range:', '', 
							 "relToolBar.removeRangeOrType('rel-range-0')", 
						     SMW_REL_CHECK_CATEGORY + SMW_REL_CHECK_EMPTY_WIE,
	                         true));
	tb.append(tb.createText('rel-range-0-msg', 'Enter a range.', '' , true));
	
	var links = [['relToolBar.createNewRelation()','Create', 'rel-confirm', 'Invalid values.', 'rel-invalid'],
				 ['relToolBar.cancel()', 'Cancel']
				];
	tb.append(tb.createLink('rel-links', links, '', true));
	
	links = [['relToolBar.addRangeInput()','Add range'],
			 ['relToolBar.addTypeInput()', 'Add type']
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
			  tb.createInput('rel-range-'+i, 'Range:', '', 
                             "relToolBar.removeRangeOrType('rel-range-"+i+"')",
						     SMW_REL_CHECK_CATEGORY + SMW_REL_CHECK_EMPTY_WIE,
	                         true));
	tb.insert('rel-range-'+i,
	          tb.createText('rel-range-'+i+'-msg', 'Enter a range.', '' , true));
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
			  tb.createDropDown('rel-range-'+i, 'Type:', 
	                            this.getDatatypeOptions(), 
	                            "relToolBar.removeRangeOrType('rel-range-"+i+"')",
	                            0, 
	                            'isAttributeType="true" ' + 
	                            SMW_REL_NO_EMPTY_SELECTION, true));
	tb.insert('rel-range-'+i,
	          tb.createText('rel-range-'+i+'-msg', 'Enter a type.', '' , true));
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
		alert("Error! Inputbox is empty.");
		return;
    }
	// Create an ontology modifier instance
	var i = 0;

	// get all ranges and types
	var rangesAndTypes = new Array();
	while($('rel-range-'+i) != null) {
		if ($('rel-range-'+i).getAttribute("isAttributeType") == "true") {
			rangesAndTypes.push("Type:"+$('rel-range-'+i).value); // add as type
		} else {
			rangesAndTypes.push("Category:"+$('rel-range-'+i).value);	// add as category
		}
		i++;
	}

	this.om.createRelation(relName,
					   "This relation has been created for " + wgPageName + ".\n",
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
		alert("Error! Inputbox is empty.");
		return;
	}

   //Get relations
   var annotatedElements = this.wtp.getRelations();

	if ((selindex!=null) && ( selindex >=0) && (selindex <= annotatedElements.length)  ){
		var relation = annotatedElements[selindex];
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
		anno.remove(replText);
	}
	//show list
	this.fillList(true);
},

newPart: function() {
    var html;
    this.wtp.initialize();
    var selection = this.wtp.getSelection();

	this.showList = false;

	var path = wgArticlePath;
	var dollarPos = path.indexOf('$1');
	if (dollarPos > 0) {
		path = path.substring(0, dollarPos);
	}
	var poLink = "<a href='"+wgServer+path+"Property:has part' " +
			     "target='blank'> has part</a>";
	var bsuLink = "<a href='"+wgServer+path+"Property:has basic structural unit' " +
			      "target='blank'> has basic structural unit</a>";

	var tb = this.createToolbar(SMW_REL_ALL_VALID);	
	tb.append(tb.createText('rel-help-msg', 'Define a part-of relation.', '' , true));
	tb.append(tb.createText('rel-help-msg', wgTitle, '' , true));
	tb.append(tb.createRadio('rel-partof', '', [poLink, bsuLink], -1, 
							 SMW_REL_CHECK_PART_OF_RADIO, true));
	
	tb.append(tb.createInput('rel-name', 'Object:', selection, '',
	                         SMW_REL_CHECK_EMPTY_NEV,
	                         true));
	tb.append(tb.createText('rel-name-msg', '', '' , true));
	tb.append(tb.createInput('rel-show', 'Show:', '', '', '', true));
	var links = [['relToolBar.addPartOfRelation()','Add', 'rel-confirm', 'Invalid values.', 'rel-invalid'],
				 ['relToolBar.cancel()', 'Cancel']
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
		poType = "has part";
	} else if (element[1].checked == true) {
		poType = "has basic structural unit";
	}

	var obj = $("rel-name").value;
	if (obj == "") {
		alert("No object for part-of relation given.");
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
	
	var relation = annotatedElements[selindex];
	var tb = this.createToolbar(SMW_REL_ALL_VALID);

	var relations = this.wtp.getRelation(relation.getName());
	if (relations.length > 1) {
	    renameAll = tb.createCheckBox('rel-replace-all', '', ['Rename all in this article.'], [], '', true);
	}

	function getSchemaCallback(request) {
		tb.hideSandglass();
		if (request.status != 200) {
			// call for schema data failed, do nothing.
			alert("Schema Data call failed!");
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
		tb.append(tb.createInput('rel-name', 'Name:', relation.getName(), '', 
								 SMW_REL_CHECK_PROPERTY_UPDATE_SCHEMA +
		 						 SMW_REL_CHECK_EMPTY,
		 						 true));
		tb.append(tb.createText('rel-name-msg', '', '' , true));
		if (renameAll !='') {
			tb.append(renameAll);
		}
		tb.append(valueInputs);
		tb.append(tb.createInput('rel-show', 'Show:', relation.getRepresentation(), '', '', true));

		var links = [['relToolBar.changeItem('+selindex+')','Change', 'rel-confirm', 'Invalid values.', 'rel-invalid'],
					 ['relToolBar.deleteItem(' + selindex +')', 'Delete'],
					 ['relToolBar.cancel()', 'Cancel']
					];
		tb.append(tb.createLink('rel-links', links, '', true));
		
		tb.finishCreation();
		gSTBEventActions.initialCheck($("relation-content-box"));

		//Sets Focus on first Element
		setTimeout("$('rel-name').focus();",50);
	}
	tb.append(tb.createText('rel-help-msg', 'Change a property.', '' , true));
	if(relation.getName().strip()!=""){
		this.toolbarContainer.showSandglass('rel-help-msg');
		sajax_do_call('smwfRelationSchemaData', [relation.getName()], getSchemaCallback.bind(this));
	}
}

};// End of Class

var relToolBar = new RelationToolBar();
Event.observe(window, 'load', relToolBar.callme.bindAsEventListener(relToolBar));



// SMW_Properties.js
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
	'typeHint="100" ';
	
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

var PRP_NARY_CHANGE_LINKS = [['propToolBar.addType()','Add type', 'prp-add-type-lnk'],
				 			 ['propToolBar.addRange()', 'Add range', 'prp-add-range-lnk']];
		
var PRP_APPLY_LINK =
	[['propToolBar.apply()', 'Apply', 'prop-confirm', 'Invalid values.', 'prop-invalid']];

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
	this.propertiescontainer.setHeadline("Property Properties");
	this.wtp = new WikiTextParser();
	this.om = new OntologyModifier();
	
	this.createContent();
	
},

callme: function(event){
	
	if(wgAction == "edit" && (wgNamespaceNumber == 100 || wgNamespaceNumber == 102)){
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
		if (domain.indexOf("Category:") == 0) {
			// Strip the category-keyword
			domain = domain.substring(9);
		}
	}
	if (range == null) {
		range = "";
	} else {
		range = range[0].getValue();
		if (range.indexOf("Category:") == 0) {
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
		if (inverse.indexOf("Property:") == 0) {
			inverse = inverse.substring(9);
		}
	}
	transitive = (transitive != null) ? "checked" : "";
	symmetric = (symmetric != null) ? "checked" : "";

	var tb = this.toolbarContainer;
	tb.append(tb.createInput('prp-domain', 'Domain:', domain, '',
	                         SMW_PRP_CHECK_CATEGORY + 
	                         SMW_PRP_CHECK_EMPTY_WIE + 
	                         SMW_PRP_HINT_CATEGORY,
	                         true));
	tb.append(tb.createText('prp-domain-msg', '', '' , true));

	tb.append(tb.createInput('prp-range', 'Range:', range, '',
	                         SMW_PRP_CHECK_CATEGORY + 
	                         SMW_PRP_CHECK_EMPTY_WIE + 
	                         SMW_PRP_HINT_CATEGORY,
	                         true));
	tb.append(tb.createText('prp-range-msg', '', '' , true));

	tb.append(tb.createInput('prp-inverse-of', 'Inverse of:', inverse, '',
	                         SMW_PRP_CHECK_PROPERTY + 
	                         SMW_PRP_HINT_PROPERTY+
	                         SMW_PRP_CHECK_EMPTY_VIE,
	                         true));
	tb.append(tb.createText('prp-inverse-of-msg', '', '' , true));

	tb.append(this.createTypeSelector("prp-attr-type", "prpSelection", false, 
									  type, '', 
									  'smwChanged="(call:propToolBar.attrTypeChanged,call:propToolBar.enableWidgets)"' +
									  SMW_PRP_NO_EMPTY_SELECTION));
	tb.append(tb.createInput('prp-min-card', 'Min. card.:', minCard, '',
	                         SMW_PRP_CHECK_INTEGER + 
	                         SMW_PRP_CHECK_EMPTY_VIE,
	                         true));
	tb.append(tb.createText('prp-min-card-msg', '', '' , true));
	tb.append(tb.createInput('prp-max-card', 'Max. card.:', maxCard, '',
	                         SMW_PRP_CHECK_INTEGER +
	                         SMW_PRP_CHECK_EMPTY_VIE,
	                         true));
	tb.append(tb.createText('prp-max-card-msg', '', '' , true));
	tb.append(tb.createCheckBox('prp-transitive', '', ['Transitive'], [transitive == 'checked' ? 0 : -1], 'name="transitive"', true));
	tb.append(tb.createCheckBox('prp-symmetric', '', ['Symmetric'], [symmetric == 'checked' ? 0 : -1], 'name="symmetric"', true));

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
			if (types[i] == "Type:Page") {
				var r = "";
				if (ranges && rc < ranges.length) {
					r = ranges[rc++].getValue();
				}
				if (r.indexOf("Category:") == 0) {
					r = r.substring(9);
				}
				tb.append(tb.createInput('prp-nary-' + i, 'Range:', r, 
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
				if (t.indexOf("Type:") == 0) {
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
			  tb.createInput('prp-nary-' + this.prpNAry, 'Range:', "", 
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
											onlyTypes ? [] : ["page", "n-ary",""],
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
		if (type && type != "n-ary" && !typeFound) {
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
	var sel = [['Retrieving data types...'],0];
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
	
	var dropDown = this.toolbarContainer.createDropDown(id, 'Type:', sel[0], deleteAction, sel[1], attributes + ' name="' + name +'"', true);
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

	domain   = (domain   != null && domain   != "") ? "Category:"+domain : null;
	range    = (range    != null && range    != "") ? "Category:"+range : null;
	attrType = (attrType != null && attrType != "") ? "Type:"+attrType : null;
	inverse  = (inverse  != null && inverse  != "") ? "Property:"+inverse : null;
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
					typeString += "Type:" + obj.value + ";";
				} else {
					// Page found
					var r = "Category:"+obj.value;
					typeString += "Type:Page;";
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
},

refreshOtherTabs: function () {
	relToolBar.fillList();
	catToolBar.fillList();
}
};// End of Class

var propToolBar = new PropertiesToolBar();
Event.observe(window, 'load', propToolBar.callme.bindAsEventListener(propToolBar));


// SMW_Refresh.js
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
		if(wgAction == "edit"){
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
								 'Warning! The article contains syntax errors ("]]" missing)</div>');
				}
			}
		}
		
	}
}

var refreshSTB = new RefreshSemanticToolBar();
Event.observe(window, 'load', refreshSTB.register.bindAsEventListener(refreshSTB));

// SMW_FactboxType.js
function factboxTypeChanged(select, title){
		$('typeloader').show();
		var type = select.options[select.options.selectedIndex].value;
		sajax_do_call('smwgNewAttributeWithType', [title, type], refreshAfterTypeChange);
}

function refreshAfterTypeChange(request){
	window.location.href=location.href;
}

// obSemToolContribution.js
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
		this.comsrchontainer = stb_control.createDivContainer(CBSRCHCONTAINER, 0);
		this.comsrchontainer.setHeadline("OntologyBrowser");

		this.comsrchontainer.setContent('<button type="button" disabled="true" id="openEntityInOB" name="navigateToOB" onclick="obContributor.navigateToOB(event)">Mark a word...</button>');
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
		if (!GeneralBrowserTools.isTextSelected(this.textArea)) {
			// unselected
			Field.disable("openEntityInOB");
			$("openEntityInOB").innerHTML = "" + "Mark a word...";
			this.textArea.focus();
		} else {
			// selected
			Field.enable("openEntityInOB");
			$("openEntityInOB").innerHTML = "" + "Open in OntologyBrowser";
			this.textArea.focus();
		}
	},

	/**
	 * Navigates to the OntologyBrowser with ns and title
	 */
	navigateToOB: function(event) {
		var selectedText = GeneralBrowserTools.getSelectedText(this.textArea);
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
		var ontoBrowserSpecialPage = wgArticlePath.replace(/\$1/, 'Special:OntologyBrowser?'+queryString);
		window.open(wgServer + ontoBrowserSpecialPage, "");
	}


}

// create instance of contributor and register on load event so that the complete document is available
// when registerContributor is executed.
var obContributor = new OBSemanticToolbarContributor();
Event.observe(window, 'load', obContributor.registerContributor.bind(obContributor));




// CombinedSearch.js
var CombinedSearchContributor = Class.create();
CombinedSearchContributor.prototype = {
	initialize: function() {
		// create a query placeHolder for potential ask-queries
		this.queryPlaceholder = document.createElement("div");
		this.queryPlaceholder.setAttribute("id", "queryPlaceholder");
		this.queryPlaceholder.innerHTML = "Additional Combined Search results.";
		this.pendingElement = null;

	},

	/**
	 * Register the contribuor and puts a button in the semantic toolbar.
	 */
	registerContributor: function() {

		if (wgCanonicalSpecialPageName != 'Search' || wgCanonicalNamespace != 'Special') {
			// do only register on Special:Search
			return;
		}

		// register CS container
		this.comsrchontainer = stb_control.createDivContainer(COMBINEDSEARCHCONTAINER, 0);
		this.comsrchontainer.setHeadline("CombinedSearch");

		this.comsrchontainer.setContent('<div id="csFoundEntities"></div>');
		this.comsrchontainer.contentChanged();

		// register content function and notify about initial update

		var searchTerm = GeneralBrowserTools.getURLParameter("search");

		// do combined search and populate ST tab.
		if ($('stb_cont9-headline') == null) return;
		this.pendingElement = new OBPendingIndicator($('stb_cont9-headline'));
		if (searchTerm != undefined && searchTerm.strip() != '') {
			this.pendingElement.show();
			sajax_do_call('smwfCombinedSearchDispatcher', [searchTerm], this.smwfCombinedSearchCallback.bind(this, "csFoundEntities"));
		}

		// add query placeholder
		$("bodyContent").insertBefore(this.queryPlaceholder, $("bodyContent").firstChild);
	},

	smwfCombinedSearchCallback: function(containerID, request) {
		this.pendingElement.hide();
		$(containerID).innerHTML = request.responseText;
		this.comsrchontainer.contentChanged();
	},

	searchForAttributeValues: function(parts) {
		this.pendingElement.show($('cbsrch'));
		sajax_do_call('smwfCSAskForAttributeValues', [parts], this.smwfCombinedSearchCallback.bind(this, "queryPlaceholder"));
	}

	/*askCatAtt: function(cat, att) {
		sajax_do_call('smwfCSAskForCatAndAttr', [cat, att], this.queryPlaceholder);
	},

	askCatRel: function(cat, rel) {
		sajax_do_call('smwfCSAskForCatAndRel', [cat, rel], this.queryPlaceholder);
	},

	askInstProp: function(inst, atts, rels) {
		// convert JS arrays for attributes and relations in semicolon separated strings
		attStr = "";
		atts.each(function(t) { attStr += t +";"});
		attStr = attStr.substr(0, attStr.length-1);

		relStr = "";
		rels.each(function(t) { relStr += t +";"});
		relStr = relStr.substr(0, relStr.length-1);

		sajax_do_call('smwfCSAskForInstAndProp', [inst, attStr, relStr], this.queryPlaceholder);
	},

	askInstAllProp: function(inst) {
		sajax_do_call('smwfCSAskForInstAndAllProp', [inst], this.queryPlaceholder);
	}*/


}

// create instance of contributor and register on load event so that the complete document is available
// when registerContributor is executed.
var csContributor = new CombinedSearchContributor();
Event.observe(window, 'load', csContributor.registerContributor.bind(csContributor));



