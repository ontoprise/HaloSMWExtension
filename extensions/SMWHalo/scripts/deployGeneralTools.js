
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
// generalTools.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
function BrowserDetectLite(){var ua=navigator.userAgent.toLowerCase();this.isGecko=(ua.indexOf('gecko')!= -1);this.isMozilla=(this.isGecko&&ua.indexOf("gecko/")+14==ua.length);this.isNS=((this.isGecko)?(ua.indexOf('netscape')!= -1):((ua.indexOf('mozilla')!= -1)&&(ua.indexOf('spoofer')== -1)&&(ua.indexOf('compatible')== -1)&&(ua.indexOf('opera')== -1)&&(ua.indexOf('webtv')== -1)&&(ua.indexOf('hotjava')== -1)));this.isIE=((ua.indexOf("msie")!= -1)&&(ua.indexOf("opera")== -1)&&(ua.indexOf("webtv")== -1));this.isOpera=(ua.indexOf("opera")!= -1);this.isSafari=(ua.indexOf("safari")!= -1);this.isKonqueror=(ua.indexOf("konqueror")!= -1);this.isIcab=(ua.indexOf("icab")!= -1);this.isAol=(ua.indexOf("aol")!= -1);this.isWebtv=(ua.indexOf("webtv")!= -1);this.isGeckoOrOpera=this.isGecko||this.isOpera;};var OB_bd=new BrowserDetectLite();GeneralBrowserTools=new Object();GeneralBrowserTools.getCookie=function(name){var value=null;if(document.cookie!=""){var kk=document.cookie.indexOf(name+"=");if(kk>=0){kk=kk+name.length+1;var ll=document.cookie.indexOf(";",kk);if(ll<0)ll=document.cookie.length;value=document.cookie.substring(kk,ll);value=unescape(value);}}return value;};GeneralBrowserTools.setCookieObject=function(key,object){var json=Object.toJSON(object);document.cookie=key+"="+json;};GeneralBrowserTools.getCookieObject=function(key){var json=GeneralBrowserTools.getCookie(key);return json!=null?json.evalJSON(false):null;};GeneralBrowserTools.selectAllCheckBoxes=function(formid){var form=$(formid);var checkboxes=form.getInputs('checkbox');checkboxes.each(function(cb){cb.checked= !cb.checked});};GeneralBrowserTools.getSelectedText=function(textArea){if(OB_bd.isGecko){var selStart=textArea.selectionStart;var selEnd=textArea.selectionEnd;var text=textArea.value.substring(selStart,selEnd);}else if(OB_bd.isIE){var text=document.selection.createRange().text;}return text;};GeneralBrowserTools.isTextSelected=function(inputBox){if(OB_bd.isGecko){if(inputBox.selectionStart!=inputBox.selectionEnd){return true;}}else if(OB_bd.isIE){if(document.selection.createRange().text.length>0){return true;}}return false;};GeneralBrowserTools.purge=function(d){var a=d.attributes,i,l,n;if(a){l=a.length;for(i=0;i<l;i+=1){n=a[i].name;if(typeof d[n]==='function'){d[n]=null;}}}a=d.childNodes;if(a){l=a.length;for(i=0;i<l;i+=1){GeneralBrowserTools.purge(d.childNodes[i]);}}};GeneralBrowserTools.getURLParameter=function(paramName){var queryParams=location.href.toQueryParams();return queryParams[paramName];};GeneralBrowserTools.navigateToPage=function(ns,name,editmode){var articlePath=wgArticlePath.replace(/\$1/,ns!=null?ns+":"+name:name);window.open(wgServer+articlePath+(editmode?"?action=edit":""),"");};GeneralBrowserTools.toggleHighlighting=function(oldNode,newNode){if(oldNode){Element.removeClassName(oldNode,"selectedItem");}Element.addClassName(newNode,"selectedItem");return newNode;};GeneralBrowserTools.repasteMarkup=function(attribute){if(Prototype.BrowserFeatures.XPath){var nodesWithID=document.evaluate("//*[@"+attribute+"=\"true\"]",document,null,XPathResult.ANY_TYPE,null);var node=nodesWithID.iterateNext();var nodes=new Array();var i=0;while(node!=null){nodes[i]=node;node=nodesWithID.iterateNext();i++;}nodes.each(function(n){var textContent=n.textContent;n.innerHTML=textContent;});}};GeneralBrowserTools.nextDIV=function(node){var nextDIV=node.nextSibling;while(nextDIV&&nextDIV.nodeName!="DIV"){nextDIV=nextDIV.nextSibling;}return nextDIV};GeneralXMLTools=new Object();GeneralXMLTools.createTreeViewDocument=function(){if(OB_bd.isGeckoOrOpera){var parser=new DOMParser();var xmlDoc=parser.parseFromString("<result/>","text/xml");}else if(OB_bd.isIE){var xmlDoc=new ActiveXObject("Microsoft.XMLDOM");xmlDoc.async="false";xmlDoc.loadXML("<result/>");}return xmlDoc;};GeneralXMLTools.createDocumentFromString=function(xmlText){if(OB_bd.isGeckoOrOpera){var parser=new DOMParser();var xmlDoc=parser.parseFromString(xmlText,"text/xml");}else if(OB_bd.isIE){var xmlDoc=new ActiveXObject("Microsoft.XMLDOM");xmlDoc.async="false";xmlDoc.loadXML(xmlText);}return xmlDoc;};GeneralXMLTools.addBranch=function(xmlDoc,branch){var currentNode=xmlDoc;for(var i=branch.length-3;i>=0;i--){currentNode=GeneralXMLTools.addNodeIfNecessary(branch[i],currentNode);}if(!currentNode.hasChildNodes()){currentNode.removeAttribute("expanded");}};GeneralXMLTools.addNodeIfNecessary=function(nodeToAdd,parentNode){var a1=nodeToAdd.getAttribute("title");for(var i=0;i<parentNode.childNodes.length;i++){if(parentNode.childNodes[i].getAttribute("title")==a1){return parentNode.childNodes[i];}}var appendedChild=GeneralXMLTools.importNode(parentNode,nodeToAdd,false);if(nodeToAdd.firstChild!=null&&nodeToAdd.firstChild.tagName=='gissues'){GeneralXMLTools.importNode(appendedChild,nodeToAdd.firstChild,true);}return appendedChild;};GeneralXMLTools.importNode=function(parentNode,child,deep){var appendedChild;if(OB_bd.isGeckoOrOpera){appendedChild=parentNode.appendChild(document.importNode(child,deep));}else if(OB_bd.isIE){appendedChild=parentNode.appendChild(child.cloneNode(deep));}return appendedChild;};GeneralXMLTools.getNodeById=function(node,id){if(Prototype.BrowserFeatures.XPath){var nodeWithID=document.evaluate("//*[@id=\""+id+"\"]",node,null,XPathResult.ANY_TYPE,null);return nodeWithID.iterateNext();}else if(OB_bd.isIE){return node.selectSingleNode("//*[@id=\""+id+"\"]");}else{var children=node.childNodes;var result;if(children.length==0){return null;}for(var i=0,n=children.length;i<n;i++){if(children[i].getAttribute("id")){if(children[i].getAttribute("id")==id){return children[i];}}result=GeneralXMLTools.getNodeById(children[i],id);if(result!=null){return result;}}return result;}};GeneralXMLTools.getNodeByText=function(node,text){if(Prototype.BrowserFeatures.XPath){var results=new Array();var nodesWithID=document.evaluate("/descendant::text()[contains(string(self::node()), '"+text+"')]",node,null,XPathResult.ANY_TYPE,null);var nextnode=nodesWithID.iterateNext();while(nextnode!=null){results.push(nextnode);nextnode=nodesWithID.iterateNext();}return results;}else if(OB_bd.isIE){var nodeList=node.selectNodes("/descendant::text()[contains(string(self::node()), '"+text+"')]");nodeList.moveNext();nextnode=nodeList.current();while(nextnode!=null){results.push(nodeList.current());nodeList.moveNext();}return result;}};GeneralXMLTools.importSubtree=function(nodeToImport,subTree){for(var i=0;i<subTree.childNodes.length;i++){GeneralXMLTools.importNode(nodeToImport,subTree.childNodes[i],true);}};GeneralXMLTools.removeAllChildNodes=function(node){if(node.firstChild){child=node.firstChild;do{nextSibling=child.nextSibling;GeneralBrowserTools.purge(child);node.removeChild(child);child=nextSibling;}while(child!=null);}};GeneralXMLTools.getAllParents=function(node){var parentNodes=new Array();var count=0;do{parentNodes[count]=node;node=node.parentNode;count++;}while(node!=null);return parentNodes;};GeneralTools=new Object();GeneralTools.getEvent=function(event){return event?event:window.event;};GeneralTools.getImgDirectory=function(source){return source.substring(0,source.lastIndexOf('/')+1);};GeneralTools.splitSearchTerm=function(searchTerm){var filterParts=searchTerm.split(" ");return filterParts.without('');};GeneralTools.matchArrayOfRegExp=function(term,regexArray){var doesMatch=true;for(var j=0,m=regexArray.length;j<m;j++){if(regexArray[j].exec(term)==null){doesMatch=false;break;}}return doesMatch;};var OBPendingIndicator=Class.create();OBPendingIndicator.prototype={initialize:function(container){this.container=container;this.pendingIndicator=document.createElement("img");Element.addClassName(this.pendingIndicator,"obpendingElement");this.pendingIndicator.setAttribute("src",wgServer+wgScriptPath+"/extensions/SMWHalo/skins/OntologyBrowser/images/ajax-loader.gif");this.contentElement=null;},show:function(container,alignment){if($("content")==null){return;}var alignOffset=0;if(alignment!=undefined){switch(alignment){case "right":{if(!container){alignOffset=$(this.container).offsetWidth-16;}else{alignOffset=$(container).offsetWidth-16;}break;}case "left":break;}}if(this.contentElement==null){this.contentElement=$("content");this.contentElement.appendChild(this.pendingIndicator);}if(!container){this.pendingIndicator.style.left=(alignOffset+Position.cumulativeOffset(this.container)[0]-Position.realOffset(this.container)[0])+"px";this.pendingIndicator.style.top=(Position.cumulativeOffset(this.container)[1]-Position.realOffset(this.container)[1]+this.container.scrollTop)+"px";}else{this.pendingIndicator.style.left=(alignOffset+Position.cumulativeOffset($(container))[0]-Position.realOffset($(container))[0])+"px";this.pendingIndicator.style.top=(Position.cumulativeOffset($(container))[1]-Position.realOffset($(container))[1]+$(container).scrollTop)+"px";}this.pendingIndicator.style.display="block";this.pendingIndicator.style.visibility="visible";},hide:function(){Element.hide(this.pendingIndicator);}}

// smw_logger.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
var smwghLoggerEnabled=false;var SmwhgLogger=Class.create();SmwhgLogger.prototype={initialize:function(){},log:function(logmsg,type,func){if(!smwghLoggerEnabled){return;}var logmsg=(logmsg==null)?"":logmsg;var type=(type==null)?"":type;var time=new Date();var timestamp=time.toGMTString();var userid=(wgUserName==null)?"":wgUserName;var locationURL=(wgPageName==null)?"":wgPageName;var func=(func==null)?"":func;sajax_do_call('smwLog',[logmsg,type,func,locationURL,timestamp],this.logcallback.bind(this));},logcallback:function(param){if(param.status!=200){alert('logging failed: '+param.statusText);}}};var smwhgLogger=new SmwhgLogger();

// SMW_Language.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
var Language=Class.create();Language.prototype={initialize:function(){},getMessage:function(id){var msg=wgLanguageStrings[id];if(!msg){msg=id;}msg=msg.replace(/\$n/g,wgCanonicalNamespace);msg=msg.replace(/\$p/g,wgPageName);msg=msg.replace(/\$t/g,wgTitle);msg=msg.replace(/\$u/g,wgUserName);msg=msg.replace(/\$s/g,wgServer);return msg;}};var gLanguage=new Language();

