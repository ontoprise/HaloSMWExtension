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
 * General JS tools
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