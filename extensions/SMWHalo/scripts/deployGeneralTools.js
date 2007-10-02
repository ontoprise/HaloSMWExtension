
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
 
// generalTools.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
function BrowserDetectLite(){var ua=navigator.userAgent.toLowerCase();this.isGecko=(ua.indexOf('gecko')!= -1);this.isMozilla=(this.isGecko&&ua.indexOf("gecko/")+14==ua.length);this.isNS=((this.isGecko)?(ua.indexOf('netscape')!= -1):((ua.indexOf('mozilla')!= -1)&&(ua.indexOf('spoofer')== -1)&&(ua.indexOf('compatible')== -1)&&(ua.indexOf('opera')== -1)&&(ua.indexOf('webtv')== -1)&&(ua.indexOf('hotjava')== -1)));this.isIE=((ua.indexOf("msie")!= -1)&&(ua.indexOf("opera")== -1)&&(ua.indexOf("webtv")== -1));this.isOpera=(ua.indexOf("opera")!= -1);this.isSafari=(ua.indexOf("safari")!= -1);this.isKonqueror=(ua.indexOf("konqueror")!= -1);this.isIcab=(ua.indexOf("icab")!= -1);this.isAol=(ua.indexOf("aol")!= -1);this.isWebtv=(ua.indexOf("webtv")!= -1);this.isGeckoOrOpera=this.isGecko||this.isOpera;};var OB_bd=new BrowserDetectLite();GeneralBrowserTools=new Object();GeneralBrowserTools.getCookie=function(name){var value=null;if(document.cookie!=""){var kk=document.cookie.indexOf(name+"=");if(kk>=0){kk=kk+name.length+1;var ll=document.cookie.indexOf(";",kk);if(ll<0)ll=document.cookie.length;value=document.cookie.substring(kk,ll);value=unescape(value);}}return value;};GeneralBrowserTools.setCookieObject=function(key,object){var json=Object.toJSON(object);document.cookie=key+"="+json;};GeneralBrowserTools.getCookieObject=function(key){var json=GeneralBrowserTools.getCookie(key);return json!=null?json.evalJSON(false):null;};GeneralBrowserTools.selectAllCheckBoxes=function(formid){var form=$(formid);var checkboxes=form.getInputs('checkbox');checkboxes.each(function(cb){cb.checked= !cb.checked});};GeneralBrowserTools.getSelectedText=function(textArea){if(OB_bd.isGecko){var selStart=textArea.selectionStart;var selEnd=textArea.selectionEnd;var text=textArea.value.substring(selStart,selEnd);}else if(OB_bd.isIE){var text=document.selection.createRange().text;}return text;};GeneralBrowserTools.isTextSelected=function(inputBox){if(OB_bd.isGecko){if(inputBox.selectionStart!=inputBox.selectionEnd){return true;}}else if(OB_bd.isIE){if(document.selection.createRange().text.length>0){return true;}}return false;};GeneralBrowserTools.purge=function(d){var a=d.attributes,i,l,n;if(a){l=a.length;for(i=0;i<l;i+=1){n=a[i].name;if(typeof d[n]==='function'){d[n]=null;}}}a=d.childNodes;if(a){l=a.length;for(i=0;i<l;i+=1){GeneralBrowserTools.purge(d.childNodes[i]);}}};GeneralBrowserTools.getURLParameter=function(paramName){var queryParams=location.href.toQueryParams();return queryParams[paramName];};GeneralBrowserTools.navigateToPage=function(ns,name){var articlePath=wgArticlePath.replace(/\$1/,ns!=null?ns+":"+name:name);window.open(wgServer+articlePath,"");};GeneralBrowserTools.toggleHighlighting=function(oldNode,newNode){if(oldNode){Element.removeClassName(oldNode,"selectedItem");}Element.addClassName(newNode,"selectedItem");return newNode;};GeneralBrowserTools.repasteMarkup=function(attribute){if(Prototype.BrowserFeatures.XPath){var nodesWithID=document.evaluate("//*[@"+attribute+"=\"true\"]",document,null,XPathResult.ANY_TYPE,null);var node=nodesWithID.iterateNext();var nodes=new Array();var i=0;while(node!=null){nodes[i]=node;node=nodesWithID.iterateNext();i++;}nodes.each(function(n){var textContent=n.textContent;n.innerHTML=textContent;});}};GeneralXMLTools=new Object();GeneralXMLTools.createTreeViewDocument=function(){if(OB_bd.isGeckoOrOpera){var parser=new DOMParser();var xmlDoc=parser.parseFromString("<result/>","text/xml");}else if(OB_bd.isIE){var xmlDoc=new ActiveXObject("Microsoft.XMLDOM");xmlDoc.async="false";xmlDoc.loadXML("<result/>");}return xmlDoc;};GeneralXMLTools.createDocumentFromString=function(xmlText){if(OB_bd.isGeckoOrOpera){var parser=new DOMParser();var xmlDoc=parser.parseFromString(xmlText,"text/xml");}else if(OB_bd.isIE){var xmlDoc=new ActiveXObject("Microsoft.XMLDOM");xmlDoc.async="false";xmlDoc.loadXML(xmlText);}return xmlDoc;};GeneralXMLTools.addBranch=function(xmlDoc,branch){var currentNode=xmlDoc;for(var i=branch.length-3;i>=0;i--){currentNode=GeneralXMLTools.addNodeIfNecessary(branch[i],currentNode);}if(!currentNode.hasChildNodes()){currentNode.removeAttribute("expanded");}};GeneralXMLTools.addNodeIfNecessary=function(nodeToAdd,parentNode){var a1=nodeToAdd.getAttribute("title");for(var i=0;i<parentNode.childNodes.length;i++){if(parentNode.childNodes[i].getAttribute("title")==a1){return parentNode.childNodes[i];}}var appendedChild=GeneralXMLTools.importNode(parentNode,nodeToAdd,false);return appendedChild;};GeneralXMLTools.importNode=function(parentNode,child,deep){var appendedChild;if(OB_bd.isGeckoOrOpera){appendedChild=parentNode.appendChild(document.importNode(child,deep));}else if(OB_bd.isIE){appendedChild=parentNode.appendChild(child.cloneNode(deep));}return appendedChild;};GeneralXMLTools.getNodeById=function(node,id){if(Prototype.BrowserFeatures.XPath){var nodeWithID=document.evaluate("//*[@id=\""+id+"\"]",node,null,XPathResult.ANY_TYPE,null);return nodeWithID.iterateNext();}else if(OB_bd.isIE){return node.selectSingleNode("//*[@id=\""+id+"\"]");}else{var children=node.childNodes;var result;if(children.length==0){return null;}for(var i=0,n=children.length;i<n;i++){if(children[i].getAttribute("id")){if(children[i].getAttribute("id")==id){return children[i];}}result=GeneralXMLTools.getNodeById(children[i],id);if(result!=null){return result;}}return result;}};GeneralXMLTools.importSubtree=function(nodeToImport,subTree){for(var i=0;i<subTree.childNodes.length;i++){GeneralXMLTools.importNode(nodeToImport,subTree.childNodes[i],true);}};GeneralXMLTools.removeAllChildNodes=function(node){if(node.firstChild){child=node.firstChild;do{nextSibling=child.nextSibling;GeneralBrowserTools.purge(child);node.removeChild(child);child=nextSibling;}while(child!=null);}};GeneralXMLTools.getAllParents=function(node){var parentNodes=new Array();var count=0;do{parentNodes[count]=node;node=node.parentNode;count++;}while(node!=null);return parentNodes;};GeneralTools=new Object();GeneralTools.getEvent=function(event){return event?event:window.event;};GeneralTools.getImgDirectory=function(source){return source.substring(0,source.lastIndexOf('/')+1);};GeneralTools.splitSearchTerm=function(searchTerm){var filterParts=searchTerm.split(" ");return filterParts.without('');};GeneralTools.matchArrayOfRegExp=function(term,regexArray){var doesMatch=true;for(var j=0,m=regexArray.length;j<m;j++){if(regexArray[j].exec(term)==null){doesMatch=false;break;}}return doesMatch;};var OBPendingIndicator=Class.create();OBPendingIndicator.prototype={initialize:function(container){this.container=container;this.pendingIndicator=document.createElement("img");Element.addClassName(this.pendingIndicator,"obpendingElement");this.pendingIndicator.setAttribute("src",wgServer+wgScriptPath+"/extensions/SMWHalo/skins/OntologyBrowser/images/ajax-loader.gif");this.contentElement=null;},show:function(container,alignment){if($("content")==null){return;}var alignOffset=0;if(alignment!=undefined){switch(alignment){case "right":{if(!container){alignOffset=$(this.container).offsetWidth-16;}else{alignOffset=$(container).offsetWidth-16;}break;}case "left":break;}}if(this.contentElement==null){this.contentElement=$("content");this.contentElement.appendChild(this.pendingIndicator);}if(!container){this.pendingIndicator.style.left=(alignOffset+Position.cumulativeOffset(this.container)[0]-Position.realOffset(this.container)[0])+"px";this.pendingIndicator.style.top=(Position.cumulativeOffset(this.container)[1]-Position.realOffset(this.container)[1]+this.container.scrollTop)+"px";}else{this.pendingIndicator.style.left=(alignOffset+Position.cumulativeOffset($(container))[0]-Position.realOffset($(container))[0])+"px";this.pendingIndicator.style.top=(Position.cumulativeOffset($(container))[1]-Position.realOffset($(container))[1]+$(container).scrollTop)+"px";}this.pendingIndicator.style.display="block";this.pendingIndicator.style.visibility="visible";},hide:function(){Element.hide(this.pendingIndicator);}}

// smw_logger.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
var smwghLoggerEnabled=true;var SmwhgLogger=Class.create();SmwhgLogger.prototype={initialize:function(){},log:function(logmsg,type,func){if(!smwghLoggerEnabled){return;}var logmsg=(logmsg==null)?"":logmsg;var type=(type==null)?"":type;var time=new Date();var timestamp=time.toGMTString();var userid=(wgUserName==null)?"":wgUserName;var locationURL=(wgPageName==null)?"":wgPageName;var func=(func==null)?"":func;sajax_do_call('smwLog',[logmsg,type,func,locationURL,timestamp],this.logcallback.bind(this));},logcallback:function(param){if(param.status!=200){alert('logging failed: '+param.statusText);}}};var smwhgLogger=new SmwhgLogger();

// SMW_Language.js
// under GPL-License; Copyright (c) 2007 Ontoprise GmbH
var Language=Class.create();Language.prototype={initialize:function(){},getMessage:function(id){var msg=wgLanguageStrings[id];if(!msg){msg=id;}msg=msg.replace(/\$n/g,wgCanonicalNamespace);msg=msg.replace(/\$p/g,wgPageName);msg=msg.replace(/\$t/g,wgTitle);msg=msg.replace(/\$u/g,wgUserName);msg=msg.replace(/\$s/g,wgServer);return msg;}};var gLanguage=new Language();

