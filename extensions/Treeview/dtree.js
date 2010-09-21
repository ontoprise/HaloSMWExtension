/*--------------------------------------------------|
| dTree 2.05 | www.destroydrop.com/javascripts/tree/|
|---------------------------------------------------|
| Copyright (c) 2002-2003 Geir Landr�               |
|                                                   |
| This script can be used freely as long as all     |
| copyright messages are intact.                    |
|                                                   |
| Updated: 17.04.2003                               |
|                                                   |
| Additonal changes on Feb. 2009 by Ontoprise.      |
| This version will work with the Semantic Treeview |
| extension only - which is part of SMW+ for        |
| Mediawiki.                                        |
|--------------------------------------------------*/
// $Id$


var dtreeHttp = dtreeCreateRequestObject();
var dtreeInCall = false;
var dtreeCallToArray = new Array();
var dtreeReturnToArray = new Array();

var cachedData = [];
var refreshOpenNodes = [];
var refreshRootNodes = [];
var refreshDtree;

/**
 * Loads the TreeView by Ajax
 * 
 */
function smwhf_loadNavTree(){
    sajax_do_call('smwfGetNavTree',
		              [],
		              smwhf_insertNavTree);
}

/**
 *  Inserts NavTree into the html document
 *  @param ajax xml returnvalue
 *  
 */
function smwhf_insertNavTree(param){
    if(param.status!=200){
        //Something went wrong with the ajax call
        alert('NavTree insertion  failed. \n'
            + 'param.status: ' + param.status + '\n'
            + 'param.statusText: ' + param.statusText +'\n'
            + 'param.responseText: ' + param.responseText);
    } else {
       //get return value
       var tree = param.responseText;
       if(tree=="") return;
       //create a new div, which we wanna insert in the document
       var treeDiv = document.createElement("div");
       treeDiv.setAttribute("id", "smwh_tree");
       treeDiv.setAttribute("class", "treeoverlay");
       treeDiv.setAttribute("style", "display: none;");
       //add the tree loading code into the div
       treeDiv.innerHTML = "<a id=\"smwh_closetree\" href=\"javascript:smwhf_hideTree()\"><img src=\""+wgScriptPath+"/extensions/Treeview/img/button_close.png\"/></a> "+ tree;
       //add the div containing the tree to the body
       window.document.body.appendChild(treeDiv);
       //window.document.body.
       //call the javascript in the added html, so tree gets populated with content by ajax
       eval(document.getElementById('navtreejs').innerHTML);
    }
}

function smwhf_hideTree(){
    var tree = document.getElementById('smwh_tree');
    $(tree).hide();
    //tree.setAttribute("css", "display: none;");
}

function smwhf_showTree(){
    var tree = document.getElementById('smwh_tree');
    $(tree).show();
}


//load the tree dynamically after the page is loaded in Skins different then Ontoskin
addOnloadHook(smwhf_loadNavTree);

// Node object
function Node(id, pid, name, url, title, target, icon, iconOpen, open) {
	this.id = id;
	this.pid = pid;
	this.name = name;
	this.url = url;
	this.title = title;
	this.target = target;
	this.icon = icon;
	this.iconOpen = iconOpen;
	this._io = open || false;
	this._is = false;
	this._ls = false;
	this._hc = false;
	this._ai = 0;
	this._p;
	this._complete = false;
	this._refresh = -1;
	this._smwIdx = null;
};

Node.prototype.serialize = function() {
	var str;
	str =
		((this.id) ? this.id : "") + "." + 
		((this.pid) ? this.pid : "") + ".";
		
	if (this.name) {
		// get pagename from href attribute or selflink content
		var link = this.getPageName();
		// remove any url params after the page name, in case there are any
		if (link.indexOf('?') != -1)
			link = link.substring(0, link.indexOf('?'));
		// get the content from the displayed content of the anchor element a
		var content= this.name.replace(/.*>(.*?)<.*/,"$1");
		// encode the . as they are delimiter in the cookie value
		link = link.replace(/\./g, "%2E");
		// modify the content in the same way as the link:
		// change spaces to underscores
		content = content.replace(/ /g, "_");
		// do an url encode on content
		content = URLEncode(content);
		// escape the . because used as delimiter
		content = content.replace(/\./g, "%2E");
		
		// if page name and link name are the same (which is the case usually)
		// add one element to the cookie as the other can be reconstructed again
		// this safes storage in the cookie (length problem)
		str += (link == content) ? link + "." : link + "." + content;
		str += ".";
	} else  str += "..";
	
	str +=  
		((this._hc) ? "1" : "0") + "." +
		((this._complete) ? "1" : "0");
	return str;
}

Node.prototype.unserialize = function(str) {
	var url = wgServer + wgScript;

	// if page is purged, the path might contain the index.php already
	if (! url.match(/index.php(\/?)$/i))
		url += '/index.php/';
	else if (url.substr(-1) != "/")
		url += '/';
		
	var nVar = str.split('.');
	if (nVar.length != 6) return;
    this.id = nVar[0];
    this.pid = nVar[1];
    var link = nVar[2].replace(/%2E/i, ".");
    var content = (nVar[3].length > 0) ? nVar[3] : link;
    content = URLDecode(content);
    content = content.replace(/_/g, " ");
    if (URLDecode(link) == wgPageName)
    	this.name = '<strong class=\"selflink\">' + content + '</strong>';
    else
    	this.name = '<a href=\"' + url + link + '\" title=\"' + content + '\">' + content + '</a>';
	this._hc = (nVar[4] == 1) ? true : false;
	this._complete = (nVar[5] == 1) ? true : false;
	return true;
}

// retrieve the page name from an node link (here stored in the node name)
Node.prototype.getPageName = function() {
	// check if variable is set
	if (!this.name) return '';

	// are we on the current page? i.e.  <strong class=\"selflink\">This page</strong>
	if (this.name.indexOf('<strong class=') != -1) {
		var name = URLEncode(wgPageName)
		return name.replace("%3A", ":");
	}
	// fetch name from link i.e. href attribute in a tag
	var name = this.name.replace(/.*href=(.*?\/)*(.*?)"( |>).*/, "$2");
	// check if the relation exists -> so the node, but if the page itself doesn't exist
	// then there is a link that the user can create this page. Extract the pagename here
	if (name.indexOf('&amp;action=edit&amp;redlink=1') != -1) // page doesn't exist
		name = name.replace(/index.php\?title=(.*?)&amp;action=edit&amp;redlink=1/, "$1");
	// remove any parameters, that might be trailed at the page name
	if (name.indexOf('?') != -1)
		name = name.substring(0, name.indexOf('?'));
	return name;
}

// SMW Data object (for all setup related to smw, when doing Ajax calls)
function SmwData(id, relation, category, display, linkto, start, maxDepth, condition, urlparams, orderbyProperty, checkNode, useTsc) {
	this.id = id;
	this.relation = relation;
	this.category = category;
	this.display = display;
    this.linkto = linkto;
	this.start = start;
	this.maxDepth = maxDepth;
	this.condition = condition;
    this.urlparams = urlparams;
	this.orderbyProperty = orderbyProperty;
	this.checkNode = checkNode;
    this.useTsc = useTsc;
}

SmwData.prototype.getParamsForAjaxRequest = function() {
	var str = 'p%3D' + URLEncode(this.relation);
	if (this.category) str += '%26c%3D' + URLEncode(this.category);
	if (this.display) str += '%26d%3D' + URLEncode(this.display);
    if (this.linkto) str += '%26l%3D' + URLEncode(this.linkto);
	if (this.condition) str += '%26q%3D' + this.condition; 
	if (this.urlparams) str += '%26u%3D' + this.urlparams;
	if (this.orderbyProperty) str += '%26b%3D' + URLEncode(this.orderbyProperty);
	if (this.checkNode) str += '%26n%3D1';
    if (this.useTsc) str += '%26j%3D1';
	str += '%26';
	return str;
}

SmwData.prototype.getUrlParams = function() {
	return this.urlparams;
}

// Object for managing parents for different node levels
function Parents() {
	// 2 dim array [x,y] where x contains elements and 
	// y = 1 => depth, y = 2 => node id
	this.data = [];
}

// set a parent for a certain depth
Parents.prototype.set = function(depth, node) {
	var size = this.data.length;
	var set;
	for (var i = 0; i < size; i++) {
		if (this.data[i][0] == depth) {
			this.data[i][1] = node;
			set = true;
		}
	}
	if (!set) this.data[size] = [depth, node];
}

// get a parent for a certain depth
Parents.prototype.get = function(depth) {
	var i = 0;
	var size = this.data.length;
	for (var i = 0; i < this.data.length; i++) {
		if (this.data[i][0] == depth) {
			return this.data[i][1];
		}
	}
	return; 
}

// Tree object, based on dtree (see copyrights at the begining of this file)
function dTree(objName, className) {
	this.config = {
		target				: null,
		folderLinks			: true,
		useSelection		: true,
		useCookies			: true,
		useLines			: true,
		useIcons			: true,
		useStatusText		: false,
		closeSameLevel		: false,
		inOrder				: false,
		refresh             : false
	}
	this.icon = {
		root				: 'img/base.gif',
		folder				: 'img/folder.gif',
		folderOpen			: 'img/folderopen.gif',
		node				: 'img/page.gif',
		empty				: 'img/empty.gif',
		line				: 'img/line.gif',
		join				: 'img/join.gif',
		joinBottom			: 'img/joinbottom.gif',
		plus				: 'img/plus.gif',
		plusBottom			: 'img/plusbottom.gif',
		minus				: 'img/minus.gif',
		minusBottom			: 'img/minusbottom.gif',
		nlPlus				: 'img/nolines_plus.gif',
		nlMinus				: 'img/nolines_minus.gif'
	};
	this.obj = objName;
	this.aNodes = [];
	this.aIndent = [];
	this.aSmw = [];
	this.root = new Node(-1);
	this.selectedNode = null;
	this.selectedFound = false;
	this.completed = false;
	this.className = className;
	this.smwAjaxUrl = null;
	this.callInitOnload = null;
};

// setup for smw+
dTree.prototype.setupSmwUrl = function(url) {
	this.smwAjaxUrl = url;
	if (url.substr(-1) != "/") this.smwAjaxUrl += "/";
	this.smwAjaxUrl += 'index.php?action=ajax&rs=smw_treeview_getTree&rsargs[]=';
};

// Adds a new node to the node array
dTree.prototype.add = function(id, pid, name, url, title, target, icon, iconOpen, open) {
	this.aNodes[this.aNodes.length] = new Node(id, pid, name, url, title, target, icon, iconOpen, open);
};

// Set node as leaf that cannot be expanded anymore
dTree.prototype.setLeaf = function(id) {
	if (this.aNodes[id]) {
		this.aNodes[id]._hc = false;
		this.aNodes[id]._complete = true;
	}
}

// Add a smw setup for a specific node
dTree.prototype.addSmwData = function(id, relation, category, display, linkto, start, maxDepth, condition, urlparams, orderbyProperty, checkNode, useTsc) {
	this.aSmw[this.aSmw.length] = new SmwData(id, relation, category, display, linkto, start, maxDepth, condition, urlparams, orderbyProperty, checkNode, useTsc);
};

// Get Smw url params to make an Ajax request for a specific node 
dTree.prototype.getSmwData = function(id) {
	var index = this.getSmwDataIndex(id); 
	return (index >= 0) ? this.aSmw[index].getParamsForAjaxRequest() : "";
}

// Get possible url params to add at link
dTree.prototype.getSmwDataUrlparams = function(id) {
	var index = this.getSmwDataIndex(id); 
	return (index >= 0) ? this.aSmw[index].getUrlParams() : "";
}

// return index of aSmw for a dynamic node smw settings
dTree.prototype.getSmwDataIndex = function(id) {
	if (this.aSmw.length == 0) return;
	while(id > this.root.id) {
		var cn = this.aNodes[id];
		if (cn._smwIdx != null) return cn._smwIdx;
		for (var i = 0; i < this.aSmw.length; i++) {
			if (cn.id == this.aSmw[i].id) {
				this.aNodes[id]._smwIdx = i;
				return i;
			}
		}
		id = cn.pid;
	}
	return;
}

// check if a node has children (regardless of _hc if the depth is 1,
// dynamic expansion is set and the tree is drawn the first time, we
// should draw leafes imediately as we already no that a dynamic
// expansion will not make sence for leafes there.
dTree.prototype.checkForChildren = function(id) {
	for (var i = id + 1; i < this.aNodes.length; i++ ) {
		if (this.aNodes[i].pid == id) return true;
	}
	this.aNodes[id]._hc = false;
	this.aNodes[id]._complete = true;
	return false;
} 

// check if a node is created dynamically, important for mixed trees
dTree.prototype.isDynamicNode = function(id) {
	return (this.getSmwDataIndex(id) >= 0) ? true : false;
}

// check, if a node has the maximum depth level (set in parameter maxDepth)
dTree.prototype.isMaxDepth = function(id) {
	var smwIdx = this.getSmwDataIndex(id);
	if (smwIdx == null) return false;
	var maxDepth = this.aSmw[smwIdx].maxDepth;
	if (! maxDepth) return false;
	else  maxDepth = maxDepth + 2; // correct number to be compatible with rest
	var depth = 1;
	var cParent = this.aNodes[id].pid;
	while (cParent != -1) {
		depth++;
		cParent = this.aNodes[cParent].pid;
		if (depth == maxDepth) return true;
	}
	if (depth == maxDepth) return true;
	return false;
}

// refresh the trees dynamic nodes
dTree.prototype.refresh = function() {

	// flush cache vars although this shouldn't be neccessary
	refreshOpenNodes = new Array();
    refreshRootNodes = new Array();

	// get all dynamic root nodes
	for (var i = 0; i < this.aSmw.length; i++)
		refreshRootNodes.push(this.aSmw[i].id);

	// these nodes will be fetched automatically and must
	// marked as to be refreshed
	var rFstCh = new Array();	// nodes one level below root
		
	// traverse through nodes and check, if the nodem must be
	// refreshed (_refresh = 1) or will just remain as it is (_refresh = 0)
	for (var i = 0; i < this.aNodes.length; i++) {
		var add = false;
		// dynamic root node
		if (refreshRootNodes.indexOf(i) > -1)
			add = true;
		// first child of a dynamic root node - will be fetched
		// automatically by this.loadFirstLevel(rootNodeId)
		else if (refreshRootNodes.indexOf(this.aNodes[i].pid) > -1) {
			rFstCh.push(i);			
			add = true;
		}
		// second child of a dynamic root node - will be fetched
		// automatically by this.loadFirstLevel(rootNodeId)
		else if (rFstCh.indexOf(this.aNodes[i].pid) > -1)
			add = true;
			
		// check dynamic nodes that are at least two levels below
		// the dynamic root nodes
		if (this.isDynamicNode(i) &&
			(refreshRootNodes.indexOf(i) == -1) &&
			(rFstCh.indexOf(i) == -1)) {
			// if the node is open, check for children
			// if node is completed, it might be a node without children, this has been checked
			if (this.aNodes[i]._io || this.aNodes[i]._complete) {
				refreshOpenNodes.push(i);
				add = true;
			}
			// if the node is a child from an open node,
			// it will be refreshed automatically, mark it for refresh
			else if (this.aNodes[this.aNodes[i].pid]._io) {
				add = true;
			}
		}
		// if the node is marked as being refreshed, reset some variables
		// to the initial values and set _refresh to 1
		if (add) {
			this.aNodes[i]._hc = false;
			this.aNodes[i]._complete = false;
			this.aNodes[i]._refresh = 1;
		}
		// static nodes must be distinguished from new nodes therefore
		// set _refresh from -1 to 0.
		else this.aNodes[i]._refresh = 0;
	}
	
	if (refreshRootNodes.length == 0 && refreshOpenNodes.length == 0)
		return;

	document.getElementById(this.obj.substr(2)).innerHTML = 
		'<img src="' + wgServer + wgScriptPath + '/extensions/Treeview/img/ajax-loader.gif" alt="Updating tree, please wait"/>';
	
	// now start with the first dynamic root node, the rest follows
	// in dtreeHandleResponseRefresh() triggered by the http requests.
	// set refreshDtree to true, later the dtree object is stored there
	// this variable is used for locking.
	drn = refreshRootNodes.shift();
	if (drn != null) {
		refreshDtree = true;
		this.loadFirstLevel(drn, 'r');
	}
}

// removes a node by eleminating the element of the array and shifting
// all other elements so that array index equals node id
// if the node has children, these are removed as well
// also if the array refreshRootNodes and refreshOpenNodes is set, the ids
// to remove must be eliminated from these arrays as well
dTree.prototype.removeNode = function(id) {
	// does the node have any children?
	if (this.aNodes[id]._hc == true) {
		for (i = id + 1; i < this.aNodes.length; i++) {
			if (this.aNodes[i].pid == id) {
				this.removeNode(i);
			}	
		}
	}
	// remember parent of node to remove
	var cParent = this.aNodes[id].pid;
	// move all references one position to the left
	for (var i = id + 1; i < this.aNodes.length; i++) {
		if (this.aNodes[i].id > id) this.aNodes[i].id--;
		if (this.aNodes[i].pid > id) this.aNodes[i].pid--;
	} 
	// and remove node now from array
	this.aNodes.splice(id, 1);
	// update references to nodes in smw data
	for (var i = 0; i < this.aSmw.length; i++) {
		if (this.aSmw[i].id > id) this.aSmw[i].id--;
	}
	// check removed node had siblings, if not
	// ajust _hc of parent
	this.aNodes[cParent]._hc = false;
	for (var i = 0; i < this.aNodes.length; i++) {
		if (this.aNodes[i].pid == cParent) {
			this.aNodes[cParent]._hc = true;
			break;
		}
	}

	// update cookie information
	this.updateCookie(); // open nodes
	// selected node
	if (this.selectedNode != null && this.selectedNode > id)
		this.s(this.selectedNode - 1);
	// ajax tree cache
   	var ostr = this.getCookie('ca' + this.obj);
	if (ostr != '') {
		var oNodes = this.extractSdataFromCookie(ostr);
	   	var newSdata = '';
		for (var i = 0; i < oNodes.length; i++) {
			cn = new Node();
			cn.unserialize(oNodes[i]);
			if (cn.id == id)
				continue;
			if (cn.id > id) cn.id = cn.id - 1;
			if (cn.pid > id) cn.pid = cn.pid - 1;
			newSdata += '{' + cn.serialize() + '}';
		}
	   	this.setCookie('ca' + this.obj, newSdata);
	}
}

dTree.prototype.updateRefreshNodeArray = function(id) {
	// remove node from array refreshRootNodes and refreshOpenNodes
	for (var i = 0; i < refreshRootNodes.length; i++) {
		if (refreshRootNodes[i] == id) {
			refreshRootNodes.splice(i, 1);				
		}
	}
	for (var i = 0; i < refreshOpenNodes.length; i++) {
		if (refreshOpenNodes[i] == id) {
			refreshOpenNodes.splice(i, 1);				
		}
	}
}

// Open/close all nodes
dTree.prototype.openAll = function() {
	this.oAll(true);
};
dTree.prototype.closeAll = function() {
	this.oAll(false);
};

// Outputs the tree to the page
dTree.prototype.toString = function() {
	var str = '<div class="'+this.className+'">\n';
	if (document.getElementById) {
		if (this.config.useCookies) {
			this.selectedNode = this.getSelected();
			if (this.smwAjaxUrl) this.readAjaxTreeCache();
		}
		str += this.addNode(this.root);
	} else str += 'Browser not supported.';
	if (this.callInitOnload)
		str += '<img src="' + wgServer + wgScriptPath + '/extensions/Treeview/img/ajax-loader.gif" alt="Loading tree, please wait"/>';
	str += '</div>';
	if (!this.selectedFound) this.selectedNode = null;
	this.completed = true;
	return str;
};

// Creates the tree structure
dTree.prototype.addNode = function(pNode) {
	var str = '';
	var n=0;
	if (this.config.inOrder) n = pNode._ai;
	for (n; n<this.aNodes.length; n++) {
		if (this.aNodes[n].pid == pNode.id) {
			var cn = this.aNodes[n];
			cn._p = pNode;
			cn._ai = n;
			this.setCS(cn);
			if (!cn.target && this.config.target) cn.target = this.config.target;
			if (cn._hc && !cn._io && this.config.useCookies) cn._io = this.isOpen(cn.id);
			if (!this.config.folderLinks && cn._hc) cn.url = null;
			if (this.config.useSelection && cn.id == this.selectedNode && !this.selectedFound) {
					cn._is = true;
					this.selectedNode = n;
					this.selectedFound = true;
			}
			str += this.node(cn, n);
			if (cn._ls) break;
		}
	}
	return str;
};

// Creates the node icon, url and text
dTree.prototype.node = function(node, nodeId) {
	var str = '<div class="dTreeNode" style="white-space:nowrap;">';
	if (this.root.id == node.pid && this.config.refresh) str += '<a href="javascript: ' + this.obj + '.refresh();" title="refresh">';
	if (this.aIndent.length == 0 && this.root.id != node.pid && this.isDynamicNode(nodeId))
		this.checkForChildren(node.id);
	str += this.indent(node, nodeId);
	if (this.config.useIcons) {
		node.icon = (this.root.id == node.pid) ? this.icon.root : (node._hc) ? this.icon.folder : this.icon.node;
		node.iconOpen = (node._hc) ? this.icon.folderOpen : this.icon.node;
		if (this.root.id == node.pid) {
			node.icon = this.icon.root;
			node.iconOpen = this.icon.root;
		} else if (this.isDynamicNode(nodeId) && !node._complete) {
			node.icon = this.icon.folder;
			node.iconOpen = this.icon.folderOpen;
		}
		str += '<img id="i' + this.obj + nodeId + '" src="' + ((node._io) ? node.iconOpen : node.icon) + '" alt="" />';
	} else {
		str += 'refresh';
	}
	if (this.root.id == node.pid && this.config.refresh) str += '</a>';
	if (node.url) {
		str += '<a id="s' + this.obj + nodeId + '" class="' + ((this.config.useSelection) ? ((node._is ? 'nodeSel' : 'node')) : 'node') + '" href="' + node.url + '"';
		if (node.title) str += ' title="' + node.title + '"';
		if (node.target) str += ' target="' + node.target + '"';
		if (this.config.useStatusText) str += ' onmouseover="window.status=\'' + node.name + '\';return true;" onmouseout="window.status=\'\';return true;" ';
		if (this.config.useSelection && ((node._hc && this.config.folderLinks) || !node._hc))
			str += ' onclick="javascript: ' + this.obj + '.s(' + nodeId + ');"';
		str += '>';
	}
	else if ((!this.config.folderLinks || !node.url) && 
	         (node._hc || (this.isDynamicNode(nodeId) && !node._complete)) && 
	         (node.pid != this.root.id))
		str += '<a href="javascript: ' + this.obj + '.o(' + nodeId + ');" class="node">';
	str += node.name;
	if (node.url || ((!this.config.folderLinks || !node.url) && 
	                (node._hc || (this.isDynamicNode(nodeId) && !node._complete)))) str += '</a>';
	str += '</div>';
	if (node._hc) {
		str += '<div id="d' + this.obj + nodeId + '" class="clip" style="display:' + ((this.root.id == node.pid || node._io) ? 'block' : 'none') + ';">';
		str += this.addNode(node);
		str += '</div>';
	}
	this.aIndent.pop();
	return str;
};

// Adds the empty and line icons
dTree.prototype.indent = function(node, nodeId) {
	var str = '';
	if (this.root.id != node.pid) {
		for (var n=0; n<this.aIndent.length; n++)
			str += '<img src="' + ( (this.aIndent[n] == 1 && this.config.useLines) ? this.icon.line : this.icon.empty ) + '" alt="" />';
		(node._ls) ? this.aIndent.push(0) : this.aIndent.push(1);
		if (node._hc || (this.isDynamicNode(nodeId) && !node._complete)) {
			str += '<a href="javascript: ' + this.obj + '.o(' + nodeId + ');"><img id="j' + this.obj + nodeId + '" src="';
			if (!this.config.useLines) str += (node._io) ? this.icon.nlMinus : this.icon.nlPlus;
			else str += ( (node._io) ? ((node._ls && this.config.useLines) ? this.icon.minusBottom : this.icon.minus) : ((node._ls && this.config.useLines) ? this.icon.plusBottom : this.icon.plus ) );
			str += '" alt="" /></a>';
		} else str += '<img src="' + ( (this.config.useLines) ? ((node._ls) ? this.icon.joinBottom : this.icon.join ) : this.icon.empty) + '" alt="" />';
	}
	return str;
};

// Checks if a node has any children and if it is the last sibling
dTree.prototype.setCS = function(node) {
	var lastId;
	for (var n=0; n<this.aNodes.length; n++) {
		if (this.aNodes[n].pid == node.id) node._hc = true;
		if (this.aNodes[n].pid == node.pid) lastId = this.aNodes[n].id;
	}
	if (lastId==node.id) node._ls = true;
};

// Returns the selected node
dTree.prototype.getSelected = function() {
	var sn = this.getCookie('cs' + this.obj);
	return (sn) ? sn : null;
};

// Highlights the selected node
dTree.prototype.s = function(id) {
	if (!this.config.useSelection) return;
	var cn = this.aNodes[id];
	if (cn._hc && !this.config.folderLinks) return;
	if (this.selectedNode != id) {
		if (this.selectedNode || this.selectedNode==0) {
			eOld = document.getElementById("s" + this.obj + this.selectedNode);
			eOld.className = "node";
		}
		eNew = document.getElementById("s" + this.obj + id);
		eNew.className = "nodeSel";
		this.selectedNode = id;
		if (this.config.useCookies) this.setCookie('cs' + this.obj, cn.id);
	}
};

// Toggle Open or close
dTree.prototype.o = function(id) {
	var cn = this.aNodes[id];
	this.nodeStatus(!cn._io, id, cn._ls);
	cn._io = !cn._io;
	if (!cn._complete && this.isDynamicNode(id) && cn._io) this.loadNextLevel(id, 'o');
	if (this.config.closeSameLevel) this.closeLevel(cn);
	if (this.config.useCookies) this.updateCookie();
};

// fetch children for a node
dTree.prototype.loadNextLevel = function(id, callBackMethod) {
	if (this.aNodes[id]._hc) {
		this.aNodes[id]._complete = true;
		return;
	}
	var params = this.getSmwData(id);
	params += 's%3D' + this.aNodes[id].getPageName().replace(/%2B/g, '%252B');
	params += this.getTokenAndWriteCache(id);
	dtreeSendCall(this.smwAjaxUrl + params, callBackMethod);
};

// load first level (needed for refresh)
dTree.prototype.loadFirstLevel = function(id) {
	var params = this.getSmwData(id);
	params += 'r%3D1%26';
	params += this.getTokenAndWriteCache(id);
	dtreeSendCall(this.smwAjaxUrl + params, 'r');
}

// load the tree via Ajax when the Wiki page is loaded and parsed already
dTree.prototype.initOnload = function(id, arg) {
	this.callInitOnload = true;
	var params = '';
	var args = arg.split('&amp;');
	var dynamic;
	var opento;
    var kv = [];
	// walk through the key value pairs and map each key
	// to the corresponding key in the ajax call
	// this is one letter only, to save space in the URI length
	// most of the keys have a mapping to their first letter (see getTree.php
	// for details), exceptions are handled in the if clauses
	for (var i = 0; i < args.length; i++) {
        if (args[i].indexOf('=') == -1) continue;
		var key = args[i].substring(0, args[i].indexOf('='));
		var value = args[i].substring(key.length + 1);
        // encode the encoded + once more to avoid spaces when the browser decodes it
        value = value.replace(/%2B/g, '%252B');
        var p="";
		if (key == 'condition')
			p = 'q';
		else if (key == 'refresh')
			p = 'f';
		else if (key == 'orderbyProperty')
			p = 'b';
		else if (key == 'checkNode')
			p = 'n';
        else if (key == 'useTsc')
            p = 'j';
        else if (key == 'level')
            p = 'v';
		else if (key == 'dynamic') {
			dynamic = 1;
			continue;
		}
		else
			p = key.substring(0, 1);
        kv[p] = value;
		params += p + '%3D' + value + '%26';
		if (key == 'opento') {
			opento = value.replace(/\+/g, '_');
			opento = opento.replace('%3A', ':');
		}
	}
    // set configuration
    this.aSmw[this.aSmw.length] = new SmwData(
        id,
        (kv.p ? kv.p : null), // relation
        (kv.c ? kv.c : null), // category
        (kv.d ? kv.d : null), // display
        (kv.l ? kv.l : null), // linkto
        (kv.s ? kv.s : null), // start
        (kv.m ? kv.m : null), // maxDepth
        (kv.q ? kv.q : null), // condition
        (kv.u ? kv.u : null), // urlparams
        (kv.b ? kv.b : null), // orderbyProperty
        (kv.n ? kv.n : null), // checkNode
        (kv.j ? kv.j : null), // useTsc
        (kv.v ? kv.v : null)  // level
    );
	if (! dynamic) params += '%26z%3D1';  // set iolStatic
	params += this.getTokenAndWriteCache(id);

	// clear cookie with saved nodes that where loaded as this causes trouble otherwise
	this.setCookie('ca'+this.obj, '');

	dtreeSendCall(this.smwAjaxUrl + params, 'r');
	if (opento)
	    dtreeSendCall(this.obj + '.openToName(\'' + opento +'\');', '0');
};

// create token and add current dTree to cache
dTree.prototype.getTokenAndWriteCache = function(id) {
	var token = this.obj + "_" + id;
	if (refreshDtree && refreshDtree.obj == this.obj) 
		cachedData[cachedData.length] = new Array(token, refreshDtree);
	else 
		cachedData[cachedData.length] = new Array(token, this);
	return '%26t%3D' + token;
}

// Open or close all nodes
dTree.prototype.oAll = function(status) {
	for (var n=0; n<this.aNodes.length; n++) {
		if (this.aNodes[n]._hc && this.aNodes[n].pid != this.root.id) {
			this.nodeStatus(status, n, this.aNodes[n]._ls)
			this.aNodes[n]._io = status;
		}
	}
	if (this.config.useCookies) this.updateCookie();
};

// Opens the tree to a specific node
dTree.prototype.openTo = function(nId, bSelect, bFirst) {
	if (!bFirst) {
		for (var n=0; n<this.aNodes.length; n++) {
			if (this.aNodes[n].id == nId) {
				nId=n;
				break;
			}
		}
	}
	var cn=this.aNodes[nId];
	if (cn.pid==this.root.id || !cn._p) return;
	cn._io = true;
	cn._is = bSelect;
	if (this.completed && cn._hc) this.nodeStatus(true, cn._ai, cn._ls);
	if (this.completed && bSelect) this.s(cn._ai);
	else if (bSelect) this._sn=cn._ai;
	this.openTo(cn._p._ai, false, true);
};

// Opens the tree to a specified node referenced by name
// acctually the parent of this node must be opened, because
// this opens the level with the node itself
dTree.prototype.openToName = function(name) {
	name = name.replace('%3A', ':');
	name = name.replace(/\+/g, '_');
	for (var i = 1; i < this.aNodes.length; i++) {
		if (this.aNodes[i].getPageName() == name) {
			this.openTo(this.aNodes[i].pid, false);
			return;
		}
	}
}

// Closes all nodes on the same level as certain node
dTree.prototype.closeLevel = function(node) {
	for (var n=0; n<this.aNodes.length; n++) {
		if (this.aNodes[n].pid == node.pid && this.aNodes[n].id != node.id && this.aNodes[n]._hc) {
			this.nodeStatus(false, n, this.aNodes[n]._ls);
			this.aNodes[n]._io = false;
			this.closeAllChildren(this.aNodes[n]);
		}
	}
}

// Closes all children of a node
dTree.prototype.closeAllChildren = function(node) {
	for (var n=0; n<this.aNodes.length; n++) {
		if (this.aNodes[n].pid == node.id && this.aNodes[n]._hc) {
			if (this.aNodes[n]._io) this.nodeStatus(false, n, this.aNodes[n]._ls);
			this.aNodes[n]._io = false;
			this.closeAllChildren(this.aNodes[n]);		
		}
	}
}

// Change the status of a node(open or closed)
dTree.prototype.nodeStatus = function(status, id, bottom) {
	eDiv	= document.getElementById('d' + this.obj + id);
	eJoin	= document.getElementById('j' + this.obj + id);
	if (this.config.useIcons) {
		eIcon	= document.getElementById('i' + this.obj + id);
		eIcon.src = (status) ? this.aNodes[id].iconOpen : this.aNodes[id].icon;
	}
	eJoin.src = (this.config.useLines)?
	((status)?((bottom)?this.icon.minusBottom:this.icon.minus):((bottom)?this.icon.plusBottom:this.icon.plus)):
	((status)?this.icon.nlMinus:this.icon.nlPlus);
	if (eDiv) eDiv.style.display = (status) ? 'block': 'none';
};


// [Cookie] Clears a cookie
dTree.prototype.clearCookie = function() {
	var now = new Date();
	var yesterday = new Date(now.getTime() - 1000 * 60 * 60 * 24);
	this.setCookie('co'+this.obj, 'cookieValue', yesterday);
	this.setCookie('cs'+this.obj, 'cookieValue', yesterday);
	this.setCookie('ca'+this.obj, 'cookieValue', yesterday);
};

// [Cookie] Sets value in a cookie
dTree.prototype.setCookie = function(cookieName, cookieValue, expires, path, domain, secure) {
	var new_cookie =
		escape(cookieName) + '=' + escape(cookieValue)
		+ (expires ? '; expires=' + expires.toGMTString() : '')
		+ (path ? '; path=' + path : '')
		+ (domain ? '; domain=' + domain : '')
		+ (secure ? '; secure' : '');
	if ( document.cookie.length + new_cookie.length < 3800) // limit of cookie length is 4096
		document.cookie = new_cookie;
};

// [Cookie] Gets a value from a cookie
dTree.prototype.getCookie = function(cookieName) {
	var cookieValue = '';
	var posName = document.cookie.indexOf(escape(cookieName) + '=');
	if (posName != -1) {
		var posValue = posName + (escape(cookieName) + '=').length;
		var endPos = document.cookie.indexOf(';', posValue);
		if (endPos != -1) cookieValue = unescape(document.cookie.substring(posValue, endPos));
		else cookieValue = unescape(document.cookie.substring(posValue));
	}
	return (cookieValue);
};

// [Cookie] Returns ids of open nodes as a string
dTree.prototype.updateCookie = function() {
	var str = '';
	for (var n=0; n<this.aNodes.length; n++) {
		if (this.aNodes[n]._io && this.aNodes[n].pid != this.root.id) {
			if (str) str += '.';
			str += this.aNodes[n].id;
		}
	}
	this.setCookie('co' + this.obj, str);
};

// [Cookie] Checks if a node id is in a cookie
dTree.prototype.isOpen = function(id) {
	var aOpen = this.getCookie('co' + this.obj).split('.');
	for (var n=0; n<aOpen.length; n++)
		if (aOpen[n] == id) return true;
	return false;
};

// Read nodes from cookie that were fetched by an ajax call before
dTree.prototype.readAjaxTreeCache = function() {
	var data = this.getCookie('ca' + this.obj);
	if (data == '') return;
	var snodes = this.extractSdataFromCookie(data); 
	for (var n = 0; n < snodes.length; n++) {
		var cn = new Node();
		if (cn.unserialize(snodes[n])) {
			var modified = false;
			for (var i = 0; i < this.aNodes.length; i++) {
				if (this.aNodes[i].id == cn.id) {
					this.aNodes[i]._hc = cn._hc;
					this.aNodes[i]._complete = cn._complete;
					modified = true;
					break;
				}
			}
			if (!modified) this.aNodes[this.aNodes.length] = cn;
		}
	}
}

// Update cookie data with information from recently expanded nodes
dTree.prototype.updateAjaxTreeCache = function(nstr) {
   	var ostr = this.getCookie('ca' + this.obj);   	
	if (ostr == '') {
		this.setCookie('ca' + this.obj, nstr);
		return;
	}
   	var oNodes = this.extractSdataFromCookie(ostr);
   	var nNodes = this.extractSdataFromCookie(nstr);
   	var newSdata = '';
	for (var i = 0; i < oNodes.length; i++) {
		var found = false;
		for (var j = 0; j < nNodes.length; j++) {
			var o = oNodes[i].split('.');
			var n = nNodes[j].split('.');
			if (o[0] == n[0] && o[1] == n[1]) {
				newSdata += '{' + nNodes[j] + '}';
				nNodes.splice(j, 1);
				found = true;
				break;
			}
		}
		if (!found) newSdata += '{' + oNodes[i] + '}';
	}
	for (var j = 0; j < nNodes.length; j++) {
		newSdata += '{' + nNodes[j] + '}';
	}
   	this.setCookie('ca' + this.obj, newSdata);
}

dTree.prototype.extractSdataFromCookie = function(str) {
	var arr = str.split('}{');
	arr[0] = arr[0].substr(1);
	arr[arr.length - 1] = arr[arr.length - 1]
	arr[arr.length - 1] = arr[arr.length - 1].substr(0, arr[arr.length - 1].length - 1); 
	return arr;
}

// if nodes have been expanded or loaded, save new gained information in cookies
// and write all nodes to page
dTree.prototype.saveCookiesAndDisplay = function(newSerialData) {
    var toggleCookies = this.config.useCookies;
    if (! this.callInitOnload && this.config.useCookies) {
    	this.updateCookie();
    	this.updateAjaxTreeCache(newSerialData);
   		this.config.useCookies = false;
    }
    if (this.callInitOnload) this.callInitOnload = null;
    document.getElementById(this.obj.substr(2)).innerHTML = this.toString();
    if (toggleCookies) this.config.useCookies = true;
};

// creates the HTML string for nodes received by an Ajax call
dTree.prototype.getHtml4Node = function(cn, url, params) {
	var str;
	var link = cn.link.replace('%3A', ':');
    if (params) {
        params = URLDecode(params);
        if (params.match(/^\?[\d\w_-]+%3D/))
            params = URLDecode(params); // fix double encoding
    }
    if (URLDecode(cn.link) == wgPageName)
    	str = '<strong class="selflink">' + cn.name + '</strong>';
    else {
        // if there is a / in the pagename, add name as URL param and not as path
        if (link.indexOf('%2F') != -1) {
            url = url.substr(0, url.length-1);
            if (params) params += '&title='+link;
            else params = '?title='+link;
            link = ''
        }
		str = '<a href=\"' + url + link + params +'\" title=\"'
    		+ cn.name + '\">' + cn.name + '</a>';
    }
	return str;
}

// If Push and pop is not implemented by the browser
if (!Array.prototype.push) {
	Array.prototype.push = function array_push() {
		for(var i=0;i<arguments.length;i++)
			this[this.length]=arguments[i];
		return this.length;
	}
};
if (!Array.prototype.pop) {
	Array.prototype.pop = function array_pop() {
		lastElement = this[this.length-1];
		this.length = Math.max(this.length-1,0);
		return lastElement;
	}
};

// parse reponse of Ajax call when fetching
// children of a certain node
dtreeParseHttpResponse = function() {
	var result;
	var resObj;
	var dTree;

	if (dtreeHttp.readyState == 4 && dtreeHttp.status == 200) {
    	result = dtreeHttp.responseText;
    	dtreeInCall = false;
    }
    else return;

    resObj = !(/[^,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]/.test(result.replace(/"(\\.|[^"\\])*"/g, ''))) 
             && eval('(' + result + ')');
    if (resObj.result != 'success' || !resObj.token) return;
	
	var parentId = resObj.token.substr(resObj.token.lastIndexOf("_")+1);

	if (!parentId || !parentId.match(/^\d+$/))
		return;

	for (var i = 0; i < cachedData.length; i++) {
		if (cachedData[i][0] == resObj.token) {
			dTree = cachedData[i][1];
			cachedData.splice(i, 1);
			break;
		}
	} 
	if (!dTree) return;

    var noc = (resObj.treelist) ? resObj.treelist.length : 0;
    var url = dTree.smwAjaxUrl.substr(0, dTree.smwAjaxUrl.lastIndexOf("/")) + '/index.php/';
	var params= dTree.getSmwDataUrlparams(parentId);
	if (params) params = '?' + params;
	else params = '';
    
    return new Array (parentId, dTree, resObj.treelist, noc, url, params);    
}

dtreeHandleResponseOpen = function() {
	var responseArr = dtreeParseHttpResponse();
	if (!responseArr) return;

	var parentId = responseArr[0];		
	var dTree    = responseArr[1];
	var treelist = responseArr[2];
	var noc      = responseArr[3];
	var url      = responseArr[4];
	var urlParams= responseArr[5];

    if (noc > 0) dTree.aNodes[parentId]._hc = true;
    dTree.aNodes[parentId]._complete = true;
    
    var newSerialData = '{' + dTree.aNodes[parentId].serialize() + '}';
    for (var i = 0; i < noc; i++) {
    	var str = dTree.getHtml4Node(treelist[i], url, urlParams);
    	var newId = dTree.aNodes.length; 
    	dTree.add(newId, parentId, str);
    	if (treelist[i].leaf || dTree.isMaxDepth(newId))
    		dTree.setLeaf(newId);
    	newSerialData += '{' + dTree.aNodes[newId].serialize() + '}';
    }
    dTree.saveCookiesAndDisplay(newSerialData);
}

dtreeHandleResponseRefresh = function() {

	var responseArr = dtreeParseHttpResponse();
	if (!responseArr) return;
	
	var parentId = responseArr[0];		
	var dTree    = responseArr[1];
	var treelist = responseArr[2];
	var noc      = responseArr[3];
	var url      = responseArr[4];
	var urlParams= responseArr[5];
	
    var parents = new Parents();
    var lastDepth;

    if (noc > 0) {
    	dTree.aNodes[parentId]._hc = true;
    	parents.set(treelist[0].depth, parentId);
    	lastDepth = treelist[0].depth;
    }
    else treelist = [];
    dTree.aNodes[parentId]._complete = true;
    
	var found;
	var foundParents = new Array();
	var cn;
	while (cn = treelist.shift()) {
    	// build comlete name (i.e. link to item)
    	var str = dTree.getHtml4Node(cn, url, urlParams);

    	// evaluate current parent
    	if (cn.depth > lastDepth)
    		parents.set(cn.depth, found);
   		var cParent = parents.get(cn.depth);
   		if (foundParents.indexOf(cParent) == -1) foundParents.push(cParent);
   		lastDepth = cn.depth;
   		
   		// search if this node already exists, only needed on refresh
   		found = null;
        if (!dTree.initOnload) {
            for (var k = 0; k < dTree.aNodes.length; k++) {
                var cName = dTree.aNodes[k].getPageName();
                if (dTree.aNodes[k].pid == cParent && cName == cn.link.replace('%3A', ':') &&
                    (dTree.aNodes[k]._refresh == 1 || dTree.aNodes[k]._refresh == -1)) {
                    found = k;
                    dTree.aNodes[k]._refresh = 0;
                }
            }
   		}
   		if (found == null) {
   			found = dTree.aNodes.length;
    		dTree.add(found, cParent, str);
    		dTree.aNodes[found]._refresh = 2;
    		for (var i = 0; i < found; i++) {
    			if (dTree.aNodes[i].pid == cParent) {
    				dTree.aNodes[i]._ls = false;
    			}
    		}
    	}
    	if (cn.leaf || dTree.isMaxDepth(found))
    		dTree.setLeaf(found);
    	
    }
	
	// search for old nodes that are not there anymore, only needed on refresh
    if (!dTree.initOnload) {
        for (var j = 0; j < foundParents.length; j++) {
            for (var k = 0; k < dTree.aNodes.length; k++) {
    			if (dTree.aNodes[k].pid == foundParents[j] && dTree.aNodes[k]._refresh == 1) {
        			dTree.removeNode(k);
            		k--;
                }
            }
		}
	}

	// safe current changes in dTree to current instance of object
	refreshDtree = dTree;
	 
	// check if there are other dynamic root nodes to fetch
	var drn = refreshRootNodes.shift();
	if (drn != null) {
		dTree.loadFirstLevel(drn);
		return;
	}
	
	// fetch child nodes
	var cn = refreshOpenNodes.shift();
	if (cn != null) {
		dTree.loadNextLevel(cn, 'r');
		return;
	}
	
	// all dynamic nodes are retrieved, write cookie of new gained nodes
	var newSerialData = '';
	for (var i = 0; i < dTree.aNodes.length; i++) {
		if (dTree.aNodes[i]._refresh == 2) {
			newSerialData += '{' + dTree.aNodes[i].serialize() + '}';
			dTree.aNodes[i]._refresh = -1;
		}
	}

	// update cookies with new information
	dTree.saveCookiesAndDisplay(newSerialData);
	
	// unlock
	refreshDtree = null;
}

// http request Object
function dtreeCreateRequestObject() {
	var reqObj;
	// Mozilla, Safari and other browsers
	if (window.XMLHttpRequest)
		reqObj = new XMLHttpRequest(); 
    // IE 
	else if (window.ActiveXObject) {
		try {
			reqObj = ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				reqObj = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {
			    alert('Could not create connection object, Ajax calls are not possible.');
			}
		}
	}
	return reqObj;
}

function dtreeSendCall(whereTo, returnTo){
	// add the next call to the queue 
	dtreeCallToArray.push(whereTo);
	dtreeReturnToArray.push(returnTo);
}

function dtreeCallQueue(){
	// check the queue and send next call in line
	if(!dtreeInCall && dtreeCallToArray.length > 0) {
		// do we have anything in the queue?
		if(dtreeCallToArray.length > 0) {
			//yes, so take the first item from the whereTo and returnTo array
			whereTo = dtreeCallToArray.shift();
			returnTo = dtreeReturnToArray.shift();
			// check, if the whereTo starts with http(s)://
			if (whereTo.match(/^https?:\/\//i))
				// and send that call
				dtreeDoCall(whereTo, returnTo);
			else
				// if whereTo starts not with http then this is a normal function call
				eval(whereTo);
		}
	}
}

function dtreeDoCall(whereTo, returnTo){
	dtreeInCall = true;
	dtreeHttp.open('get', whereTo);
	if(returnTo == "r")
		dtreeHttp.onreadystatechange = dtreeHandleResponseRefresh;
	else
		dtreeHttp.onreadystatechange = dtreeHandleResponseOpen;
	dtreeHttp.send(null);
}

var dtreeQueueWatcher = setInterval(dtreeCallQueue, 100);

function URLEncode( str ) {
    // version: 911.718
    // discuss at: http://phpjs.org/functions/urlencode
    str = (str+'').toString();
    return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');

}

function URLDecode( str ) {
    // version: 1001.2911
    // discuss at: http://phpjs.org/functions/urldecode
    return decodeURIComponent(str.replace(/\+/g, '%20'));
}
