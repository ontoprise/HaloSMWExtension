
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
// ajaxhalo.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
// remote scripting library
// (c) copyright 2005 modernmethod, inc

var SMW_AJAX_GENERAL = 0;

var AjaxRequestManager = function() {};
AjaxRequestManager.prototype = { 
    initialize: function() {
        this.calls = new Array();
        
    },
    
    /**
     * Adds a call to the manager. Called by sajax framework.
     * DO NOT CALL MANUALLY
     */
    addCall: function(xmlHttp, type) {
        var i = type == undefined ? 0 : type;
        if (this.calls[i] == undefined) {
            this.calls[i] = new Array();
        }
        this.calls[i].push(xmlHttp);
    },
    
    /**
     * Removes a call from the manager. Called by sajax framework.
     * DO NOT CALL MANUALLY
     */
    removeCall: function(xmlHttp, type) {
        var i = type == undefined ? 0 : type;
        if (this.calls[i] == undefined) return;
        for(var j = 0, n=this.calls[i].length; j < n; j++) {
            var index = this.calls[i].indexOf(xmlHttp);
            if (index != -1) this.calls[i].splice(index, 1);
        }
    },
    
    /**
     * Stops all calls of the given type and 
     * calls the callback function afterwards.
     * 
     * @param type
     * @param callback function (optional)
     */
    stopCalls: function(type, callback) {
        var i = type == undefined ? 0 : type;
        if (this.calls[i] == undefined) return;
        for(var j = 0, n=this.calls[i].length; j < n; j++) {
                if (this.calls[i][j]) { 
                    this.calls[i][j].abort();
                    delete this.calls[i][j];
                    this.calls[i][j] = null;
                }
        }
        this.calls.splice(i,1);
        if (callback) callback();
    }
};

var ajaxRequestManager = new AjaxRequestManager();
ajaxRequestManager.initialize();

var sajax_debug_mode = false;
var sajax_request_type = "POST";
var NULL = function() {} // empty dummy function 
/**
* if sajax_debug_mode is true, this function outputs given the message into 
* the element with id = sajax_debug; if no such element exists in the document, 
* it is injected.
*/
function sajax_debug(text) {
    if (!sajax_debug_mode) return false;

    var e= document.getElementById('sajax_debug');

    if (!e) {
        e= document.createElement("p");
        e.className= 'sajax_debug';
        e.id= 'sajax_debug';

        var b= document.getElementsByTagName("body")[0];

        if (b.firstChild) b.insertBefore(e, b.firstChild);
        else b.appendChild(e);
    }

    var m= document.createElement("div");
    m.appendChild( document.createTextNode( text ) );

    e.appendChild( m );

    return true;
}

/**
* compatibility wrapper for creating a new XMLHttpRequest object.
*/
function sajax_init_object() {
    sajax_debug("sajax_init_object() called..")
    var A;
    try {
        A=new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
        try {
            A=new ActiveXObject("Microsoft.XMLHTTP");
        } catch (oc) {
            A=null;
        }
    }
    if(!A && typeof XMLHttpRequest != "undefined")
        A = new XMLHttpRequest();
    if (!A)
        sajax_debug("Could not create connection object.");

    return A;
}

/**
* Perform an ajax call to mediawiki. Calls are handeled by AjaxDispatcher.php
*   func_name - the name of the function to call. Must be registered in $wgAjaxExportList
*   args - an array of arguments to that function
*   target - the target that will handle the result of the call. If this is a function,
*            if will be called with the XMLHttpRequest as a parameter; if it's an input
*            element, its value will be set to the resultText; if it's another type of
*            element, its innerHTML will be set to the resultText.
*
* Example:
*    sajax_do_call('doFoo', [1, 2, 3], document.getElementById("showFoo"));
*
* This will call the doFoo function via MediaWiki's AjaxDispatcher, with
* (1, 2, 3) as the parameter list, and will show the result in the element
* with id = showFoo
*/
function sajax_do_call(func_name, args, target, type) {
    var i, x, n;
    var uri;
    var post_data;
    type = type ? type : 0; // undefined is GENERAL call
    uri = wgServer + wgScriptPath + "/index.php?action=ajax";
    if (sajax_request_type == "GET") {
        if (uri.indexOf("?") == -1)
            uri = uri + "?rs=" + encodeURIComponent(func_name);
        else
            uri = uri + "&rs=" + encodeURIComponent(func_name);
        for (i = 0; i < args.length; i++)
            uri = uri + "&rsargs[]=" + encodeURIComponent(args[i]);
        //uri = uri + "&rsrnd=" + new Date().getTime();
        post_data = null;
    } else {
        post_data = "rs=" + encodeURIComponent(func_name);
        for (i = 0; i < args.length; i++)
            post_data = post_data + "&rsargs[]=" + encodeURIComponent(args[i]);
    }
    x = sajax_init_object();
    if (!x) {
        alert("AJAX not supported");
        return false;
    }

    try {
        x.open(sajax_request_type, uri, true);
    } catch (e) {
        if (window.location.hostname == "localhost") {
            alert("Your browser blocks XMLHttpRequest to 'localhost', try using a real hostname for development/testing.");
        }
        throw e;
    }
    if (sajax_request_type == "POST") {
        x.setRequestHeader("Method", "POST " + uri + " HTTP/1.1");
        x.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    }
    x.setRequestHeader("Pragma", "cache=yes");
    x.setRequestHeader("Cache-Control", "no-transform");
    x.onreadystatechange = function() {
        
        //KK: remove call from manager. do not remove if GENERAL call.
        if (type != 0) ajaxRequestManager.removeCall(x, type); 
        if (x.readyState != 4)
            return;
        
        //KK: fix to prevent exception when reading status property during an aborted call.     
        try {
            var state = x.status;
        } catch(e) {
            return; // probably an aborted call
        }
        sajax_debug("received (" + x.status + " " + x.statusText + ") " + x.responseText);
        

        //if (x.status != 200)
        //  alert("Error: " + x.status + " " + x.statusText + ": " + x.responseText);
        //else

        if ( typeof( target ) == 'function' ) {
            target( x );
        }
        else if ( typeof( target ) == 'object' ) {
            if ( target.tagName == 'INPUT' ) {
                if (x.status == 200) target.value= x.responseText;
                //else alert("Error: " + x.status + " " + x.statusText + " (" + x.responseText + ")");
            }
            else {
                if (x.status == 200) target.innerHTML = x.responseText;
                else target.innerHTML= "<div class='error'>Error: " + x.status + " " + x.statusText + " (" + x.responseText + ")</div>";
            }
        }
        else {
            alert("bad target for sajax_do_call: not a function or object: " + target);
        }
        // KK: IE fix. Make sure that reference to callback closure is removed.
        x.onreadystatechange = NULL;
        delete x;
        return;
    }

    sajax_debug(func_name + " uri = " + uri + " / post = " + post_data);
    x.send(post_data);
    //KK: add call from manager. do not add if GENERAL call.
    if (type != 0) ajaxRequestManager.addCall(x, type); 
    sajax_debug(func_name + " waiting..");
    delete x; // KK: why? x can not be removed here, isn't it?

    return true;
}



// generalTools.js
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

/*
 * General JS tools
 */

function BrowserDetectLite() {

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
GeneralBrowserTools.navigateToPage = function (ns, name, editmode) {
	var articlePath = wgArticlePath.replace(/\$1/, ns != null ? ns+":"+name : name);
	window.open(wgServer + articlePath + (editmode ? "?action=edit" : ""), "");
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

GeneralBrowserTools.nextDIV = function(node) {
	var nextDIV = node.nextSibling;
		
 	// find the next DIV
	while(nextDIV && nextDIV.nodeName != "DIV") {
		nextDIV = nextDIV.nextSibling;
	}
	return nextDIV
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

/**
 * Returns true if node has child nodes with tagname
 */
GeneralXMLTools.hasChildNodesWithTag = function(node, tagname) {
	if (node == null) return false;
	return node.getElementsByTagName(tagname).length > 0;
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
 * nodeToAdd: node to add
 * parentNode: node to add it to
 */
GeneralXMLTools.addNodeIfNecessary = function (nodeToAdd, parentNode) {
	var a1 = nodeToAdd.getAttribute("title");
	for (var i = 0; i < parentNode.childNodes.length; i++) {
		if (parentNode.childNodes[i].getAttribute("title") == a1) {
			return parentNode.childNodes[i];
		}
	}
	
	var appendedChild = GeneralXMLTools.importNode(parentNode, nodeToAdd, false);
	
	/// XXX: hack to include gardening issues. They must be firstchild of treeelement
	if (nodeToAdd.firstChild != null && nodeToAdd.firstChild.tagName == 'gissues') {
		GeneralXMLTools.importNode(appendedChild, nodeToAdd.firstChild, true);
		
	}
	
	return appendedChild;
}

/*
 * Import a node
 */
GeneralXMLTools.importNode = function(parentNode, child, deep) {
	var appendedChild;
	if (OB_bd.isIE || OB_bd.isSafari) {
        appendedChild = parentNode.appendChild(child.cloneNode(deep));

    } else if (OB_bd.isGecko) {
		appendedChild = parentNode.appendChild(document.importNode(child, deep));
		
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
		var nodeWithID;
		// distinguish between XML and HTML content (necessary in FF3)
		if ((node.contentType == "text/xml") || (node.ownerDocument != null && node.ownerDocument.contentType == "text/xml")) {
		  var xmlDOM = node.documentElement != null ? node.documentElement.ownerDocument : node.ownerDocument;
		  nodeWithID = xmlDOM.evaluate("//*[@id=\""+id+"\"]", node, null, XPathResult.ANY_TYPE,null);
		} else {
	      nodeWithID = document.evaluate("//*[@id=\""+id+"\"]", document.documentElement, null, XPathResult.ANY_TYPE,null);
		}
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
		
		if (children[i].nodeType == 4) continue; // ignore CDATA sections
					
			if (children[i].getAttribute("id") == id) {
				return children[i];
			}
		
    	result = GeneralXMLTools.getNodeById(children[i], id);
    	if (result != null) {
    	
    		return result;
    	}
	}
	
	return null;
	}
}


/**
 * Returns textnodes below node which contains the given text.
 * Does not work with IE at the moment!
 * 
 * @param node
 * @param text
 * 
 * @return array of textnodes
 */
GeneralXMLTools.getNodeByText = function(node, text) {
	if (Prototype.BrowserFeatures.XPath) {
		var results = new Array();
		// FF supports DOM 3 XPath. That makes things easy and blazing fast...
		var nodesWithID;
		// distinguish between XML and HTML content (necessary in FF3)
		if ((node.contentType == "text/xml") || (node.ownerDocument != null && node.ownerDocument.contentType == "text/xml")) {
			var xmlDOM = node.documentElement != null ? node.documentElement.ownerDocument : node.ownerDocument;
            nodesWithID = xmlDOM.evaluate("/descendant::text()[contains(string(self::node()), '"+text+"')]", node, null, XPathResult.ANY_TYPE,null);
		} else {
            nodesWithID = document.evaluate("/descendant::text()[contains(string(self::node()), '"+text+"')]", document.documentElement, null, XPathResult.ANY_TYPE,null);
		}
		var nextnode = nodesWithID.iterateNext();
		while (nextnode != null) {
			results.push(nextnode);
			nextnode = nodesWithID.iterateNext();
		} 
		return results; 
	} else if (OB_bd.isIE) {
		// this should work, but does not for some reason (IE does not support selectNodes although it should)
		var nodeList = node.selectNodes("/descendant::text()[contains(string(self::node()), '"+text+"')]");
		nodeList.moveNext();
		nextnode = nodeList.current();
		while (nextnode != null) {
			results.push(nodeList.current());
			nodeList.moveNext();
		} 
		return result;
	} else if (OB_bd.isSafari) {
		// should be relative slow. Safari does not support XPath
		var nodes = new Array();
    
	    function iterate(_node) {
	        // do a depth first search
	        var children = _node.childNodes;
	        var result;
	        if (children.length == 0) { return; }
	        for (var i=0, n = children.length; i < n;i++) {
	            if (children[i].nodeType == 3) { // textnode
	                
	                if (children[i].nodeValue.indexOf(text) != -1) {
	                    nodes.push(children[i]);
	                }
	            } else {
	                iterate(children[i]);
	            }
	           
	        }
	        
	    }
	    
	    iterate(node, text);
	    return nodes;
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
		this.pendingIndicator.setAttribute("src", wgServer + wgScriptPath + "/extensions/SMWHalo/skins/OntologyBrowser/images/ajax-loader.gif");
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
	show: function(container, alignment) {
		//check if the content element is there
		if($("content") == null){
			return;
		}
		
		var alignOffset = 0;
		if (alignment != undefined) {
			switch(alignment) {
				case "right": { 
					if (!container) { 
						alignOffset = $(this.container).offsetWidth - 16;
					} else {
						alignOffset = $(container).offsetWidth - 16;
					}
					
					break;
				}
				case "left": break;
			}
		}
			
		//if not already done, append the indicator to the content element so it can become visible
		if(this.contentElement == null) {
				this.contentElement = $("content");
				this.contentElement.appendChild(this.pendingIndicator);
		}
		if (!container) {
			this.pendingIndicator.style.left = (alignOffset + Position.cumulativeOffset(this.container)[0]-Position.realOffset(this.container)[0])+"px";
			this.pendingIndicator.style.top = (Position.cumulativeOffset(this.container)[1]-Position.realOffset(this.container)[1]+this.container.scrollTop)+"px";
		} else {
			this.pendingIndicator.style.left = (alignOffset + Position.cumulativeOffset($(container))[0]-Position.realOffset($(container))[0])+"px";
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

// breadcrump.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
/**
 * Breadcrump is a tool which displays the last 5 (default) visited pages as a queue.
 * 
 * It uses a DIV element with id 'breadcrump' to add its list content. If this is not
 * available it displays nothing.
 */
var Breadcrump = Class.create();
Breadcrump.prototype = {
    initialize: function(lengthOfBreadcrump) {
        this.lengthOfBreadcrump = lengthOfBreadcrump;
    },
    
    update: function() {
        var breadcrump = GeneralBrowserTools.getCookie("breadcrump");
        var breadcrumpArray;
        if (breadcrump == null) {
            breadcrump = wgPageName;
            breadcrumpArray = [breadcrump];
        } else {
            // parse breadcrump and add new title
            breadcrumpArray = breadcrump.split(" ");
            // do not add doubles
            if (breadcrumpArray[breadcrumpArray.length-1] != wgPageName) {
                breadcrumpArray.push(wgPageName);
                if (breadcrumpArray.length > this.lengthOfBreadcrump) {
                    breadcrumpArray.shift();
                } 
            }
            //serialize breadcrump
            breadcrump = "";
            for (var i = 0; i < breadcrumpArray.length-1; i++) {
                breadcrump += breadcrumpArray[i]+" ";
            }
            breadcrump += breadcrumpArray[breadcrumpArray.length-1];
                
        }
        // (re-)set cookie
        document.cookie = "breadcrump="+breadcrump+"; path="+wgScript;
        this.pasteInHTML(breadcrumpArray);
    },
    
    pasteInHTML: function(breadcrumpArray) {
        var html = "";
        breadcrumpArray.each(function(b) {
            
            // remove namespace and replace underscore by whitespace
            var title = b.split(":");
            var show = title.length == 2 ? title[1] : title[0];
            show = show.replace(/_/g, " ");
            
            // add item
             var encURI = encodeURIComponent(b);
            if (wgArticlePath.indexOf('?title=') != -1) {
            	encURI = encURI.replace(/%3A/g, ":"); // do not encode colon
            	var articlePath = wgArticlePath.replace("$1", encURI);
            }  else {
           	    encURI = encURI.replace(/%2F/g, "/"); // do not encode slash
           	    encURI = encURI.replace(/%3A/g, ":"); // do not encode colon
            	var articlePath = wgArticlePath.replace("$1", encURI);
            }
            html += '<a href="'+wgServer+articlePath+'">'+show+' &gt; </a>'; 
        });
        var bc_div = $('breadcrump');
        if (bc_div != null) bc_div.innerHTML = html;
    }
}
var smwhg_breadcrump = new Breadcrump(5);
Event.observe(window, 'load', smwhg_breadcrump.update.bind(smwhg_breadcrump));

// generalGUI.js
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
*   Contains general GUI functions
*/
var GeneralGUI = Class.create();
GeneralGUI.prototype = {
    initialize: function() {
        this.closedContainers = GeneralBrowserTools.getCookieObject("smwNavigationContainers");
        if (this.closedContainers == null) this.closedContainers = new Object();
    },
    
    switchVisibilityWithState: function(id) {
    	if ($(id).visible()) {
    		this.closedContainers[id] = true;
    	} else {
    		this.closedContainers[id] = false;
    	}
    	GeneralBrowserTools.setCookieObject("smwNavigationContainers", this.closedContainers);
    	this.switchVisibility(id);
    },
    
    update: function() {
    	for (var id in this.closedContainers) {
    		if (this.closedContainers[id] == true) {
    			this.switchVisibility(id);
    		}
    	}
    },
    
    switchVisibility: function(container) {
        var visible = $(container).visible();
        if ( visible ) {    
            $(container).hide();
        } else {
            $(container).show();
        }
    }
   
}
var smwhg_generalGUI = new GeneralGUI();
Event.observe(window, 'load', smwhg_generalGUI.update.bind(smwhg_generalGUI));

// contentSlider.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
/* Resizing Content window slider using scriptacolus slider */
var ContentSlider = Class.create();
ContentSlider.prototype = {

    initialize: function() {
        this.sliderObj = null;
        this.savedPos = -1; // save position within a page. hack for IE
        this.sliderWidth = OB_bd.isIE ? 13 : 12;
        this.timer = null;
    },
    //if()
    activateResizing: function() {
    //Check if semtoolbar is available and action is not annotate
   
    if(!$('contentslider') || wgAction == "annotate") return;
    
    //Load image to the slider div
    $('contentslider').innerHTML = '<img id="contentSliderHandle" src="' +
            wgScriptPath +
            '/extensions/SMWHalo/skins/slider.gif"/>';
        var windowWidth = OB_bd.isIE ? document.body.offsetWidth : window.innerWidth
        // 25px for the silder
        var iv = ($("p-logo").clientWidth -  this.sliderWidth) / windowWidth;
        var saved_iv = GeneralBrowserTools.getCookie("cp-slider");    
        var initialvalue = saved_iv != null ? saved_iv : this.savedPos != -1 ? this.savedPos : iv;
        
        this.slide(initialvalue);
       //create slider after old one is removed
       if(this.sliderObj != null){
            this.sliderObj.setDisabled();
            this.sliderObj= null;
       }
       this.sliderObj = new Control.Slider('contentSliderHandle','contentslider',{
          //axis:'vertical',
          sliderValue:initialvalue,
          minimum:iv,
          maximum:0.5,
          //range: $R(0.5,0.75),
          onSlide: this.slide.bind(this),
          onChange: this.slide.bind(this)
       });
      
    },

    //Checks for min max and sets the content and the semtoolbar to the correct width
    slide: function(v)
          {
          	
            var windowWidth = OB_bd.isIE ? document.body.offsetWidth : window.innerWidth
            var iv = ($("p-logo").clientWidth - this.sliderWidth) / windowWidth;    
            var currMarginDiv = windowWidth*(v-iv)+$("p-logo").clientWidth;
            
            var leftmax = iv; // range 0 - 1
            var rightmax = 0.5; // range 0 - 1

             if( v < leftmax){
                if (this.sliderObj != null) this.sliderObj.setValue(leftmax);
                return;
             }

             if( v > rightmax){
                if (this.sliderObj != null) this.sliderObj.setValue(rightmax);
                return;
             }
            var sliderSmooth = OB_bd.isIE ? v*25 : v*38;
            // move toolbar and content pane
            $('p-cactions').style.marginLeft = (windowWidth*(v-iv)) - sliderSmooth +"px";
            $('content').style.marginLeft = currMarginDiv - sliderSmooth + "px";
           
           // change width of divs of class 'dtreestatic' below main_navtree
           // and of main_navtree itself.
           var sliderWidth = this.sliderWidth;
           $$('#main_navtree div.dtreestatic').each(function(s) { 
                s.style.width = windowWidth*v+sliderWidth-7- sliderSmooth +"px";
           });
           var main_navTree = $('main_navtree');
           if (main_navTree != null) main_navTree.style.width = windowWidth*v+sliderWidth-5- sliderSmooth +"px";
           
           
           // change sidebars
           $('p-navigation').style.width = windowWidth*v+sliderWidth-5- sliderSmooth +"px";
           $('p-search').style.width = windowWidth*v+sliderWidth-5- sliderSmooth +"px";
           $('p-tb').style.width = windowWidth*v+sliderWidth-5- sliderSmooth +"px";
           if ($('p-treeview')) $('p-treeview').style.width = windowWidth*v+sliderWidth-5- sliderSmooth +"px";
           
           document.cookie = "cp-slider="+v+"; path="+wgScript;
           this.savedPos = v;
    },
     /**
      * Resizes the slide if window size is changed
      * since IE fires the resize event in much more cases than the desired
      * we have to do some additional checks
      */
     resizeTextbox: function(){
        if( !OB_bd.isIE ){
            this.activateResizing();
        } else {
        	
        	if (this.timer != null) window.clearTimeout(this.timer);
        	var slider = this; // copy reference to make it readable in closure
        	this.timer = window.setTimeout(function() {
        		 slider.activateResizing();
        	},1000);
        	 
        }        
     }
}

var smwhg_contentslider = new ContentSlider();
Event.observe(window, 'load', smwhg_contentslider.activateResizing.bind(smwhg_contentslider));
//Resizes the slider if window size is changed
Event.observe(window, 'resize', smwhg_contentslider.resizeTextbox.bind(smwhg_contentslider));


// smw_logger.js
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
 *  Logger - logs msgs to the database 
 */
var smwghLoggerEnabled = false;

var SmwhgLogger = Class.create();
SmwhgLogger.prototype = {
	
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
		if (!smwghLoggerEnabled) {
			return;
		}
		//Default values
		var logmsg = (logmsg == null) ? "" : logmsg; 
		var type = (type == null) ? "" : type; 
			//Get Timestamp
			var time = new Date();
			var timestamp = time.toGMTString();
		var userid = (wgUserName == null) ? "" : wgUserName; 
		var locationURL = (wgPageName == null) ? "" : wgPageName; 
		var func= (func == null) ? "" : func;
		
		sajax_do_call('smwLog', 
		              [logmsg,type,func,locationURL,timestamp], 
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

var smwhgLogger = new SmwhgLogger();

// SMW_Language.js
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
	getMessage: function(id, type) {
		switch (type) {
			case "user":
				var msg = wgUserLanguageStrings[id];
				if (!msg) {
					msg = id;
				} 
				break;
				
			case "cont":
				var msg = wgContLanguageStrings[id];
				if (!msg) {
					msg = id;
				} 
				break;
			default: 
				var msg = wgUserLanguageStrings[id];
				if (!msg) {
					var msg = wgContLanguageStrings[id];
					if (!msg) {
						msg = id;
					}
				}
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

