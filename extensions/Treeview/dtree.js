/*--------------------------------------------------|
| dTree 2.05 | www.destroydrop.com/javascript/tree/ |
|---------------------------------------------------|
| Copyright (c) 2002-2003 Geir Landrö               |
|                                                   |
| This script can be used freely as long as all     |
| copyright messages are intact.                    |
|                                                   |
| Updated: 17.04.2003                               |
| Additonal changes on Feb. 2009 by Ontoprise.      |
| This version will work with the Semantic Treeview |
| extension only - which is part of SMW+ for        |
| Mediawiki.                                        |
|--------------------------------------------------*/

var httpRequest;
var cachedData = [];

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
};

Node.prototype.serialize = function() {
	var str;
	str =
		((this.id) ? this.id : "") + "." + 
		((this.pid) ? this.pid : "") + ".";
		
	if (this.name) {
		var link = this.name.replace(/.*href=(.*?\/)*(.*?)" .*/, "$2");
		var content= this.name.replace(/.*>(.*?)<.*/,"$1");
		link = link.replace(/\./, "%2E");
		content = content.replace(/\./, "%2E");
		content = content.replace(/ /, "_");
		str += (link == content) ? link + "." : link + "." + content;
		str += ".";
	} else  str += "..";
	
	str +=  
		((this._hc) ? "1" : "0") + "." +
		((this._complete) ? "1" : "0");
	return str;
}

Node.prototype.unserialize = function(str) {
	var url = wgServer + wgScript + '/index.php/';
	
	var nVar = str.split('.');
	if (nVar.length != 6) return;
    this.id = nVar[0];
    this.pid = nVar[1];
    link = nVar[2].replace(/%2E/i, ".");
    content = (nVar[3]) ? nVar[3].replace(/%2E/i, ".") : link;
    content = content.replace(/_/, " ");
    this.name = '<a href=\"' + url + link + '\">' + content + '</a>';
	this._hc = (nVar[4] == 1) ? true : false;
	this._complete = (nVar[5] == 1) ? true : false;
	return true;
}

// SMW Data object (for relation and display)
function SmwData(id, relation, display) {
	this.id = id;
	this.relation = relation;
	this.display = display;
}

SmwData.prototype.getUrlParams = function() {
	var str = 'p%3D' + escape(this.relation);
	if (this.display) str += '%26d%3D' + escape(this.display); 
	str += '%26';
	return str;
}

// Tree object
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
		inOrder				: false
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

// Add a smw setup for a specific node
dTree.prototype.addSmwData = function(id, relation, display) {
	this.aSmw[this.aSmw.length] = new SmwData(id, relation, display);
};

// Get Smw url params for a specific node
dTree.prototype.getSmwData = function(id) {
	if (this.aSmw.length == 0) return "";
	while(id >= 0) {
		var cn = this.aNodes[id];
		for (var i = 0; i < this.aSmw.length; i++) {
			if (cn.id == this.aSmw[i].id) {
				return this.aSmw[i].getUrlParams();
			}
		}
		if (id == this.root.id) return "";
		id = cn.pid;
	}
	return "";
}

dTree.prototype.isDynamicNode = function(id) {
	var str = this.getSmwData(id); 
	return (str.length == 0) ? false : true;
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
	var str;
    if (this.className == 'dtreestatic') {
       str = '<h5><label>'+gLanguage.getMessage('smw_stv_browse')+'</label></h5><div class="'+this.className+'">\n';
    } else {
       str = '<div class="'+this.className+'">\n';
    }
	if (document.getElementById) {
		if (this.config.useCookies) {
			this.selectedNode = this.getSelected();
			if (this.smwAjaxUrl) this.readAjaxTreeCache();
		}
		str += this.addNode(this.root);
	} else str += 'Browser not supported.';
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
	var str = '<div class="dTreeNode" style="white-space:nowrap;">' + this.indent(node, nodeId);
	if (this.config.useIcons) {
		if (!node.icon) node.icon = (this.root.id == node.pid) ? this.icon.root : ((node._hc) ? this.icon.folder : this.icon.node);
		node.iconOpen = (node._hc) ? this.icon.folderOpen : this.icon.node;
		if (this.root.id == node.pid) {
			node.icon = this.icon.root;
			node.iconOpen = this.icon.root;
		} else if (this.isDynamicNode(nodeId) && !node._complete) {
			node.icon = this.icon.folder;
			node.iconOpen = this.icon.folderOpen;
		}
		str += '<img id="i' + this.obj + nodeId + '" src="' + ((node._io) ? node.iconOpen : node.icon) + '" alt="" />';
	}
	if (node.url) {
		str += '<a id="s' + this.obj + nodeId + '" class="' + ((this.config.useSelection) ? ((node._is ? 'nodeSel' : 'node')) : 'node') + '" href="' + node.url + '"';
		if (node.title) str += ' title="' + node.title + '"';
		if (node.target) str += ' target="' + node.target + '"';
		if (this.config.useStatusText) str += ' onmouseover="window.status=\'' + node.name + '\';return true;" onmouseout="window.status=\'\';return true;" ';
		if (this.config.useSelection && ((node._hc && this.config.folderLinks) || !node._hc))
			str += ' onclick="javascript: ' + this.obj + '.s(' + nodeId + ');"';
		str += '>';
	}
	//else if ((!this.config.folderLinks || !node.url) && node._hc && node.pid != this.root.id)
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
	if (!cn._complete && this.isDynamicNode(id) && cn._io) this.loadNextLevel(id);
	if (this.config.closeSameLevel) this.closeLevel(cn);
	if (this.config.useCookies) this.updateCookie();
};

// fetch children for a node
dTree.prototype.loadNextLevel = function(id) {
	if (this.aNodes[id]._hc) {
		this.aNodes[id]._complete = true;
		return;
	}
	
	var params = this.getSmwData(id);
	var token = this.obj + "_" + id;
	cachedData[cachedData.length] = new Array(token, this);
	params += 's%3D' + this.aNodes[id].name.replace(/.*href=(.*?\/)*(.*?)" .*/, "$2");
	params += '%26t%3D' + token; 
    this.getHttpRequest(params);
};

// start http request for Ajax call
dTree.prototype.getHttpRequest = function(params) {
    httpRequest = null;     
    // Mozilla, Safari and other browsers
    if (window.XMLHttpRequest) { 
        httpRequest = new XMLHttpRequest(); 
    } 
    // IE 
    else if (window.ActiveXObject) {
    	try {
            httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {}
        }
    }
    
    if (!httpRequest) return;

    httpRequest.onreadystatechange = parseHttpResponse
    httpRequest.open("GET", this.smwAjaxUrl + params, true); 
	httpRequest.send(null);
};

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
	if (document.cookie.length + new_cookie.length < 7000 &&
	    new_cookie.length < 4096)
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
			if (oNodes[i].substr(0, 10) == nNodes[j].substr(0, 10)) {
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

// parse reponse of Ajax call
parseHttpResponse = function() {
	var result;
	var resObj;
	var dTree;

	if (httpRequest.readyState == 4 && httpRequest.status == 200) 
    	result = httpRequest.responseText;
    else
    	return;
    	
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
    
    if (noc > 0) dTree.aNodes[parentId]._hc = true;
    dTree.aNodes[parentId]._complete = true;
    
    var newSerialData = '{' + dTree.aNodes[parentId].serialize() + '}';
    for (var i = 0; i < noc; i++) {
    	var str = '<a href=\"' + url + resObj.treelist[i].link +'\" title=\"';
    	str += resObj.treelist[i].name + '\">' + resObj.treelist[i].name + '</a>';
    	dTree.add(dTree.aNodes.length, dTree.aNodes[parentId].id, str);
    	newSerialData += '{' + dTree.aNodes[dTree.aNodes.length - 1].serialize() + '}';
    }
    var toggleCookies = dTree.config.useCookies;
    if (dTree.config.useCookies) {
    	dTree.updateCookie();
    	dTree.updateAjaxTreeCache(newSerialData);
    	dTree.config.useCookies = false;
    }
    document.getElementById(dTree.obj.substr(2)).innerHTML = dTree.toString();
    if (toggleCookies) dTree.config.useCookies = true;
};
