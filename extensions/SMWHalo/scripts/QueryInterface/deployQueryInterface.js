
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
 
// treeviewQI.js
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

/*****************************************************************************

Name : toggle

Parameters :  node , DOM element (<a> tag)

Description :     Description, collapse or unfold a branch

Author : Jean-Michel Garnier /  D.D. de Kerf

*****************************************************************************/



function toggle(node) {

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

				node.childNodes.item(0).src = getImgDirectory(node.childNodes.item(0).src) + "minus.gif";

			}

		}



		nextDIV.style.display = 'block';

	}

	// Collapse the branch if it IS visible

	else {



		// Change the image (if there is an image)

		if (node.childNodes.length > 0) {

			if (node.childNodes.item(0).nodeName == "IMG") {

  				node.childNodes.item(0).src = getImgDirectory(node.childNodes.item(0).src) + "plus.gif";

			}

		}

		nextDIV.style.display = 'none';

	}

}



/*****************************************************************************

Name : toggle2

Parameters :  node DOM element (<a> tag), folderCode String

Description :    if you use the "code" attribute in a folder element, toggle2 is called

instead of toggle. The consequence is that you MUST implement a selectFolder function in your page.

Author : Jean-Michel Garnier

*****************************************************************************/

function toggle2(node, folderCode) {

    //toggle(node);

    selectFolder(folderCode);

}



/*****************************************************************************

Name : getImgDirectory

Parameters : Image source path

Return : Image source Directory

Author : Jean-Michel Garnier

*****************************************************************************/



function getImgDirectory(source) {

    return source.substring(0, source.lastIndexOf('/') + 1);

}



/************************************

************* IMPORTANT *************

*************************************



The functions above are NOT used by the DHTML treeview. Netherless, have a look bc some be useful if you

need to make XSLT on the client (since IE 5.5 and soon Mozilla !)



*/





/*****************************************************************************

Name : stringExtract

Parameters :

- st String input string, contains n separators

- position int, from 0 to n, position of the token wanted

- separator char, separator between token



Return : the token at the position wanted if it exists



Description : Equivalent to class java.util.StringTokenizer

Example -> stringExtract("A; B; C", 0, ";") = "A"



Author : Jean-Michel Garnier

*****************************************************************************/



function stringExtract( st, position, separator ) {

	var array;

	var result = new String('');

	var s = new String(st);

	if (s != '' ) {

		array = s.split( separator);

		// @TODO, add a control on position value ...

		result = array[position];

	}

	return result;

}



/*****************************************************************************

Name : jsTrim

Parameters : value, String

Return : the same String, with space characters removed

Description : equivalent to trim function

Author : Jean-Michel Garnier

*****************************************************************************/



function jsTrim(value) {

    var result = "";

    for (i=0; i < value.length; i++) {

        if (value.charAt(i) != ' ') {

            result += value.charAt(i);

        }

    }

    return result;

}



/*****************************************************************************

Name : findObj

Parameters :

- n String object's name

- d Document document

Return : a reference on the object if it exists

Description : Search an object in a document from its name.

Author : Macromedia

*****************************************************************************/



function findObj(n, d) {

  var p, i, x;

  if (!d)

    d = document;

  if ( (p=n.indexOf("?") )>0 && parent.frames.length ) {

		d = parent.frames[n.substring(p+1)].document;

		n = n.substring(0,p);

  }

  if (!(x=d[n])&& d.all )

	x = d.all[n];

  for (i=0; !x && i < d.forms.length; i++)

	x = d.forms[i][n];

  for (i=0; !x && d.layers && i<d.layers.length; i++)

	x = findObj(n, d.layers[i].document);



  return x;

}



/*****************************************************************************

Name : isInSelectInput

Parameters :

- v String Option value

- select_input input SELECT

Return : true if the SELECT already value

Author : Jean-Michel Garnier

*****************************************************************************/



function isInSelectInput(v, select_input) {

	for(var i=0; i<select_input.options.length; i++) {

		if (select_input.options[i].value == v) {

			return true;

		}

	}

	return false;

}



/*****************************************************************************

Name : selectOption

Parameters :

- v_value String Option value

- select_input SELECT

Description : Select all options whose value

Author : Jean-Michel Garnier

*****************************************************************************/



function selectOption(v_value, select_input) {

	var i, nb_item;

	nb_item = select_input.options.length;

	for (i = 0; i < nb_item ; i++) {

		if ( select_input.options[i].value == v_value )

			select_input.options[i].selected = true;

	}

}



/*****************************************************************************

Name : selectRemoveSelectedOption

Parameters : select_input SELECT

Description : removes all the selected options

Author : Jean-Michel Garnier

*****************************************************************************/



function selectRemoveSelectedOption(select_input) {

    for(var i=0; i<select_input.options.length; i++) {

        if ( select_input.options[i].selected ) {

  			select_input.options[i] = null;

   		}

	}

	select_input.selectedIndex = -1;

}



/*****************************************************************************

Name : selectRemoveAll

Parameters : select_input

Description : This Function removes all options

Author : Jean-Michel Garnier

*****************************************************************************/



function selectRemoveAll(select_input) {



    var linesNumber = select_input.options.length;

    for(i=0; i < linesNumber; i++) {

		select_input.options[0] = null;

    }

	select_input.selectedIndex = -1;

}



/*****************************************************************************

Name : buildXMLSource

Parameters : xmlSource_name, String, can be a file name (.xml or .xslt) or

a String containing the xml

!!!BE SURE xml and xlt are lowercase

Return : a reference on a ActiveX Msxml2.FreeThreadedDOMDocument with the xml loaded

Author : Jean-Michel Garnier

*****************************************************************************/



function buildXMLSource(xmlSource_name) {



    var obj, file_extension;

    obj = new ActiveXObject("Msxml2.FreeThreadedDOMDocument");

    obj.async = false;

    obj.resolveExternals = false;



    file_extension = stringExtract(xmlSource_name, 1, ".");

    // if there is a file extension, then load the file

    if (file_extension == "xml" || file_extension == "xslt" ) {

        obj.load(xmlSource_name);

    }

    else {

        // else load the XML String

        obj.loadXML(xmlSource_name);

    }



    return obj;

}



/*****************************************************************************

Name : transform

Parameters :

- xmlSource Msxml2.FreeThreadedDOMDocument ActiveX XML

- xsltSource Msxml2.FreeThreadedDOMDocument ActiveX XSLT

Return : String with the result of the transformation (not an ActiveX object !)

Description :



Author : Jean-Michel Garnier

*****************************************************************************/



function transform(xmlSource, xsltSource) {



    var xslt;

    var xslProc, paramName, paramValue;



    // Create XLST

    xslt = new ActiveXObject("Msxml2.XSLTemplate");

    xslt.stylesheet = xsltSource;



    // Add parameters

    xslProc = xslt.createProcessor();



    xslProc.input = xmlSource;



    // add parameters if present

    if (arguments.length >2 && arguments.length % 2 == 0){

        for (var i=0; i < Math.floor((arguments.length)/2)-1; i++){

            paramName = arguments[2*i+2];

            paramValue = arguments[2*i+3];

            xslProc.addParameter(paramName, paramValue);

        }

    }



    xslProc.transform();

    return xslProc.output;

}



function BrowserDetectLite() {

	var ua = navigator.userAgent.toLowerCase();



	// browser name

	this.isGecko     = (ua.indexOf('gecko') != -1);

	this.isMozilla   = (this.isGecko && ua.indexOf("gecko/") + 14 == ua.length);

	this.isNS        = ( (this.isGecko) ? (ua.indexOf('netscape') != -1) : ( (ua.indexOf('mozilla') != -1) && (ua.indexOf('spoofer') == -1) && (ua.indexOf('compatible') == -1) && (ua.indexOf('opera') == -1) && (ua.indexOf('webtv') == -1) && (ua.indexOf('hotjava') == -1) ) );

	this.isIE        = ( (ua.indexOf("msie") != -1) && (ua.indexOf("opera") == -1) && (ua.indexOf("webtv") == -1) );

	this.isOpera     = (ua.indexOf("opera") != -1);

	this.isKonqueror = (ua.indexOf("konqueror") != -1);

	this.isIcab      = (ua.indexOf("icab") != -1);

	this.isAol       = (ua.indexOf("aol") != -1);

	this.isWebtv     = (ua.indexOf("webtv") != -1);



	// spoofing and compatible browsers

	this.isIECompatible = ( (ua.indexOf("msie") != -1) && !this.isIE);

	this.isNSCompatible = ( (ua.indexOf("mozilla") != -1) && !this.isNS && !this.isMozilla);



	// browser version

	this.versionMinor = parseFloat(navigator.appVersion);



	// correct version number

	if (this.isNS && this.isGecko) {

		this.versionMinor = parseFloat( ua.substring( ua.lastIndexOf('/') + 1 ) );

	}

	else if (this.isIE && this.versionMinor >= 4) {

		this.versionMinor = parseFloat( ua.substring( ua.indexOf('msie ') + 5 ) );

	}

	else if (this.isMozilla) {

      this.versionMinor = parseFloat( ua.substring( ua.indexOf('rv:') + 3 ) );

   }

   else if (this.isOpera) {

		if (ua.indexOf('opera/') != -1) {

			this.versionMinor = parseFloat( ua.substring( ua.indexOf('opera/') + 6 ) );

		}

		else {

			this.versionMinor = parseFloat( ua.substring( ua.indexOf('opera ') + 6 ) );

		}

	}

	else if (this.isKonqueror) {

		this.versionMinor = parseFloat( ua.substring( ua.indexOf('konqueror/') + 10 ) );

	}

	else if (this.isIcab) {

		if (ua.indexOf('icab/') != -1) {

			this.versionMinor = parseFloat( ua.substring( ua.indexOf('icab/') + 6 ) );

		}

		else {

			this.versionMinor = parseFloat( ua.substring( ua.indexOf('icab ') + 6 ) );

		}

	}

	else if (this.isWebtv) {

		this.versionMinor = parseFloat( ua.substring( ua.indexOf('webtv/') + 6 ) );

	}



	this.versionMajor = parseInt(this.versionMinor);

	this.geckoVersion = ( (this.isGecko) ? ua.substring( (ua.lastIndexOf('gecko/') + 6), (ua.lastIndexOf('gecko/') + 14) ) : -1 );



	// dom support

   this.isDOM1 = (document.getElementById);

	this.isDOM2Event = (document.addEventListener && document.removeEventListener);



   // css compatibility mode

   this.mode = document.compatMode ? document.compatMode : 'BackCompat';



	// platform

	this.isWin   = (ua.indexOf('win') != -1);

	this.isWin32 = (this.isWin && ( ua.indexOf('95') != -1 || ua.indexOf('98') != -1 || ua.indexOf('nt') != -1 || ua.indexOf('win32') != -1 || ua.indexOf('32bit') != -1 || ua.indexOf('xp') != -1) );

	this.isMac   = (ua.indexOf('mac') != -1);

	this.isUnix  = (ua.indexOf('unix') != -1 || ua.indexOf('linux') != -1 || ua.indexOf('sunos') != -1 || ua.indexOf('bsd') != -1 || ua.indexOf('x11') != -1)



	// specific browser shortcuts

	this.isNS4x = (this.isNS && this.versionMajor == 4);

	this.isNS40x = (this.isNS4x && this.versionMinor < 4.5);

	this.isNS47x = (this.isNS4x && this.versionMinor >= 4.7);

	this.isNS4up = (this.isNS && this.versionMinor >= 4);

	this.isNS6x = (this.isNS && this.versionMajor == 6);

	this.isNS6up = (this.isNS && this.versionMajor >= 6);

	this.isNS7x = (this.isNS && this.versionMajor == 7);

	this.isNS7up = (this.isNS && this.versionMajor >= 7);



	this.isIE4x = (this.isIE && this.versionMajor == 4);

	this.isIE4up = (this.isIE && this.versionMajor >= 4);

	this.isIE5x = (this.isIE && this.versionMajor == 5);

	this.isIE55 = (this.isIE && this.versionMinor == 5.5);

	this.isIE5up = (this.isIE && this.versionMajor >= 5);

	this.isIE6x = (this.isIE && this.versionMajor == 6);

	this.isIE6up = (this.isIE && this.versionMajor >= 6);



	this.isIE4xMac = (this.isIE4x && this.isMac);

}



// queryTree.js
// under GPL-License
var xslStylesheet;
var myDOM;
var xmldoc;

function updateQueryTree(xmltext){
	if (OB_bd.isKonqueror || OB_bd.isSafari) {
		alert(gLanguage.getMessage('KS_NOT_SUPPORTED'));
		return;
	}

	var xmldoc = GeneralXMLTools.createDocumentFromString(xmltext);
	if (OB_bd.isGeckoOrOpera) {
		var QI_xsltProcessor_gecko = new XSLTProcessor();

	  	var myXMLHTTPRequest = new XMLHttpRequest();
	  	myXMLHTTPRequest.open("GET", wgServer + wgScriptPath + "/extensions/SMWHalo/skins/QueryInterface/treeview.xslt", false);
	 	myXMLHTTPRequest.send(null);
	  	var xslRef = myXMLHTTPRequest.responseXML;

	  	// Finally import the .xsl
	  	QI_xsltProcessor_gecko.importStylesheet(xslRef);
	  	QI_xsltProcessor_gecko.setParameter(null, "param-img-directory", wgServer + wgScriptPath + "/extensions/SMWHalo/skins/QueryInterface/images/");

		var fragment = QI_xsltProcessor_gecko.transformToFragment(xmldoc, document);

		var oldtree = document.getElementById("treeanchor").firstChild;
		if(oldtree){
			document.getElementById("treeanchor").removeChild(oldtree);
		}
		document.getElementById("treeanchor").appendChild(fragment);

	} else if (OB_bd.isIE) {
		var xsl = new ActiveXObject("MSXML2.FreeThreadedDOMDocument");
		xsl.async = false;

		// load stylesheet
		xsl.load(wgServer + wgScriptPath + "/extensions/SMWHalo/skins/QueryInterface/treeview.xslt");
		// create XSLT Processor
		var template = new ActiveXObject("Msxml2.XSLTemplate");
		template.stylesheet = xsl;
		var QI_xsltProcessor_ie = template.createProcessor();
		QI_xsltProcessor_ie.addParameter("param-img-directory", wgServer + wgScriptPath + "/extensions/SMWHalo/skins/QueryInterface/images/");

		//QI_xsltProcessor_ie.addParameter("startDepth", 0);
		QI_xsltProcessor_ie.input = xmldoc;
		QI_xsltProcessor_ie.transform();
		var oldtree = document.getElementById("treeanchor").firstChild;
		if(oldtree){
			$("treeanchor").removeChild(oldtree);
		}
		$("treeanchor").innerHTML = QI_xsltProcessor_ie.output;
		//blub;
	}

}

function selectLeaf(title, code) {
	var id = code.substring(8, code.indexOf('-'));
	if (code.indexOf("category") == 0){
		selectFolder("categories" + id);
	}
	else if (code.indexOf("instance") == 0){
		selectFolder("instances" + id);
	}
	else if (code.indexOf("property") == 0){
		selectFolder("properties" + id);
	}
	else if (code.indexOf("subquery") == 0){
		id = code.substring(8, code.length);
		qihelper.setActiveQuery(id);
	}
}

function selectFolder(folderCode) {
	var id = null;
	if(folderCode.indexOf("categories")==0){
		id = folderCode.substring(10, folderCode.length);
		qihelper.loadCategoryDialogue(id);
		//create dialogue
	} else if(folderCode.indexOf("instances")==0){
		id = folderCode.substring(9, folderCode.length);
		qihelper.loadInstanceDialogue(id);
		//create dialogue
	} else if(folderCode.indexOf("properties")==0){
		id = folderCode.substring(10, folderCode.length);
		qihelper.loadPropertyDialogue(id);
		//create dialogue
	}
}

// Query.js
// under GPL-License
/*
* Query.js
* Query object representing a single query. Subqueries are
* seperate objects which are referenced by an ID.
* @author Markus Nitsche [fitsch@gmail.com]
*/

var Query = Class.create();
Query.prototype = {

/**
* Initialize a new query
* @param id ID
* @param parent parentID
* @param name QueryName
*/
	initialize:function(id, parent, name){
		this.id = id; //id of this query
		this.parent = parent; //parent of this query, null if root
		this.name = name; //name of the property referencing on this query
		this.hasSubquery = false; //has it subqueries?
		this.categories = Array(); //All categories
		this.instances = Array(); //All Instances
		this.properties = Array(); //All properties
		this.subqueryIds = Array(); //IDs of subqueries
	},

/**
* Add a category or a gourp of or-ed
* categories to the query
* @param cat CategoryGroup
* @param oldid null if new, otherwise ID of an existing
* category group which will be overwritten
*/
	addCategoryGroup:function(cat, oldid){
		if(oldid==null)
			this.categories.push(cat);
		else
			this.categories[oldid] = cat;
	},

/**
* Add a instance or a gourp of or-ed
* instances to the query
* @param ins InstanceGroup
* @param oldid null if new, otherwise ID of an existing
* instance group which will be overwritten
*/
	addInstanceGroup:function(ins, oldid){
		if(oldid==null)
			this.instances.push(ins);
		else
			this.instances[oldid] = ins;
	},

/**
* Add a property or a gourp of or-ed
* properties to the query
* @param pgroup PropertyGroup
* @param subIds IDs of subqueries that are referenced within
* this property group
* @param oldid null if new, otherwise ID of an existing
* property group which will be overwritten
*/
	addPropertyGroup:function(pgroup, subIds, oldid){
		if(oldid == null)
			this.properties.push(pgroup);
		else
			this.properties[oldid] = pgroup;
		if (subIds.length > 0){
			this.hasSubquery = true;
			for(var i=0; i<subIds.length; i++){
				this.subqueryIds.push(subIds[i]);
			}
		}
	},

	hasSubqueries:function(){
		return this.hasSubquery;
	},
/**
* Creates XML string for the query tree representation. The tree representation is
* laid out like a file browser with folders and leafs.
*/
	updateTreeXML:function(){
		var treexml = '<?xml version="1.0" encoding="UTF-8"?>';
		treexml += '<treeview title=" Query"><folder title=" ' + this.name + '" code="root" expanded="true" img="question.gif">';
		for(var i=0; i<this.categories.length; i++){
			treexml += '<folder title="' + gLanguage.getMessage('QI_CATEGORIES') + '" code="categories' + i +'" expanded="true" img="category.gif">';
			for(var j=0; j<this.categories[i].length; j++){
					treexml += '<leaf title=" ' + this.categories[i][j] + '" code="category' + i + '-' + j + '" img="blue_ball.gif"/>';
			}
			treexml += '</folder>';
		}
		for(var i=0; i<this.instances.length; i++){
			treexml += '<folder title="' + gLanguage.getMessage('QI_INSTANCES') + '" code="instances' + i +'" expanded="true" img="instance.gif">';
			for(var j=0; j<this.instances[i].length; j++){
				treexml += '<leaf title=" ' + this.instances[i][j] + '" code="instance' + i + '-' + j + '" img="red_ball.gif"/>';
			}
			treexml += '</folder>';
		}
		for(var i=0; i<this.properties.length; i++){
			treexml += '<folder title=" ' + this.properties[i].getName() + '" code="properties' + i +'" expanded="true" img="property.gif">';
			propvalues = this.properties[i].getValues();
			for(var j=0; j<propvalues.length; j++){
				if(propvalues[j][0] == "subquery")
					treexml += '<leaf title=" ' + gLanguage.getMessage('QI_SUBQUERY') + ' ' + propvalues[j][2] + '" code="subquery' + propvalues[j][2] + '" img="subquery.png" class="treesub"/>';
				else {
					var res = ""; //restriction for numeric values. Encode for HTML display
					switch(propvalues[j][1]){
						case "<=":
							res = "&lt;=";
							break;
						case ">=":
							res = "&gt;=";
							break;
						default:
							res = propvalues[j][1];
							break;
					}
					treexml += '<leaf title=" ' + propvalues[j][0] + " " + res + " " + propvalues[j][2] + '" code="property' + i + '-' + j + '" img="yellow_ball.gif"/>';
				}
			}
			treexml += '</folder>';
		}
		treexml += '</folder></treeview>';
		updateQueryTree(treexml);
	},

/**
* Create the syntax for the ask query of this object. Subqueries are not resolved
* but marked with "Subquery:[ID]:". Recursive resolving of all subqueries is done
* within QIHelper.js
* @return asktext string containing the ask syntax
*/
	getAskText:function(){
		var asktext = "";
		for(var i=0; i<this.categories.length; i++){
			asktext += "[[Category:";
			for(var j=0; j<this.categories[i].length; j++){
				asktext += this.categories[i][j];
				if(j<this.categories[i].length-1){ //add disjunction operator
					asktext += "||";
				}
			}
			asktext += "]]";
		}
		for(var i=0; i<this.instances.length; i++){
			asktext += "[[";
			for(var j=0; j<this.instances[i].length; j++){
				asktext += this.instances[i][j];
				if(j<this.instances[i].length-1){ //add disjunction operator
					asktext += "||";
				}
			}
			asktext += "]]";
		}
		for(var i=0; i<this.properties.length; i++){
			if(this.properties[i].isShown()){ // "Show in results" checked?
				asktext += "[[" + this.properties[i].getName() + ":=*]]"; // Display statement
				asktext += "[[" + this.properties[i].getName() + ":=+]]"; //Currently there is no option for this in the QI, but it makes sense
			}
			asktext += "[[" + this.properties[i].getName() + ":=";
			if(this.properties[i].getArity() > 2){ // always special treatment for arity > 2
				var vals = this.properties[i].getValues();
				for(var j=0; j<vals.length; j++){
					if(j!=0)
						asktext += ";"; // connect values with semicolon
					if(vals[j][1]!="=")
						asktext += vals[j][1].substring(0,1); //add operator <, >, ! if existing
					asktext += vals[j][2];
				}
			} else { //binary property
				var vals = this.properties[i].getValues();
				for(var j=0; j<vals.length; j++){
					if(j!=0) //add disjunction operator
						asktext += "||";
					if(vals[j][1]!= "=")
						asktext += vals[j][1].substring(0,1);
					if(vals[j][0] == "subquery") // Mark ID of subqueries so they can easily be parsed
						asktext += "Subquery:" + vals[j][2] + ":";
					else
						asktext += vals[j][2];
				}
			}
			asktext += "]]";
		}
		return asktext;
	},

	isEmpty:function(){
		if(this.categories.length == 0 && this.instances.length == 0 && this.properties.length == 0){
			return true;
		} else {
			return false;
		}
	},

	getName:function(){
		return this.name;
	},

	getSubqueryIds:function(){
		return this.subqueryIds;
	},

	getParent:function(){
		return this.parent;
	},

	getCategoryGroup:function(id){
		return this.categories[id];
	},

	getInstanceGroup:function(id){
		return this.instances[id];
	},

	getPropertyGroup:function(id){
		return this.properties[id];
	},

	getAllProperties:function(){
		return this.properties;
	},

	removeCategoryGroup:function(id){
		if(id < this.categories.length-1)
			this.categories[id]= this.categories.pop();
		else
			this.categories.pop();
	},

	removeInstanceGroup:function(id){
		if(id < this.instances.length-1)
			this.instances[id]= this.instances.pop();
		else
			this.instances.pop();
	},

	removePropertyGroup:function(id){
		if(id < this.properties.length-1){
			this.properties[id]= this.properties.pop();
			}
		else{
			this.properties.pop();
		}
	}


};

// QIHelper.js
// under GPL-License
/**
* QIHelper.js
* Manages major functionalities and GUI of the Query Interface
* @author Markus Nitsche [fitsch@gmail.com]
*/

var QIHelper = Class.create();
QIHelper.prototype = {

/**
* Initialize the QIHelper object and all variables
*/
initialize:function(){
	this.imgpath = wgScriptPath  + '/extensions/SMWHalo/skins/QueryInterface/images/';
	this.numTypes = new Array();
	this.getNumericDatatypes();
	this.queries = Array();
	this.activeQuery = null;
	this.activeQueryId = null;
	this.nextQueryId = 0;
	this.activeInputs = 0;
	this.activeDialogue = null;
	this.propname = null;
	this.proparity = null;
	this.propIsEnum = false;
	this.enumValues = null;
	this.loadedFromId = null;
	this.addQuery(null, gLanguage.getMessage('QI_MAIN_QUERY_NAME'));
	this.setActiveQuery(0);
	this.updateColumnPreview();
	this.pendingElement = null;
},

/**
* Performs ajax call on startup to get a list of all numeric datatypes.
* Needed to find out if users can use operators (< and >)
*/
getNumericDatatypes:function(){
	sajax_do_call('smwfQIAccess', ["getNumericTypes", "dummy"], this.setNumericDatatypes.bind(this));
},

/**
* Save all numeric datatypes into an associative array
* @param request Request of AJAX call
*/
setNumericDatatypes:function(request){
	var types = request.responseText.split(",");
	for(var i=0; i<types.length; i++){
		//remove leading and trailing whitespaces
		var tmp = types[i].replace(/^\s+|\s+$/g, '');
		this.numTypes[tmp] = true;
	}
},

/**
* Add a new query. This happens everytime a user adds a property with a subquery
* @param parent ID of parent query
* @param name name of the property which is referencing this query
*/
addQuery:function(parent, name){
	this.queries.push(new Query(this.nextQueryId, parent, name));
	this.nextQueryId++;
},

/**
* Set a certain query as active query.
* @param id IS of the query to switch to
*/
setActiveQuery:function(id){
	this.activeQuery = this.queries[id];
	this.activeQuery.updateTreeXML(); //update treeview
	this.activeQueryId = id;
	this.emptyDialogue(); //empty open dialogue
	this.updateBreadcrumbs(id); // update breadcrumb navigation of treeview
	//update everything
},

/**
* Shows a confirmation dialogue
*/
resetQuery:function(){
	$('shade').style.display="";
	$('resetdialogue').style.display="";
},

/**
* Executes a reset. Initializes Query Interface so everything is in its initial state
*/
doReset:function(){
	/*STARTLOG*/
	if(window.smwhgLogger){
	    smwhgLogger.log("Reset Query","info","query_reset");
	}
	/*ENDLOG*/
	this.emptyDialogue();
	this.initialize();
	$('shade').style.display="none";
	$('resetdialogue').style.display="none";
},

/**
* Gets all display parameters and the full ask syntax to perform an ajax call
* which will create the preview
*/
previewQuery:function(){
	/*STARTLOG*/
	if(window.smwhgLogger){
	    smwhgLogger.log("Preview Query","info","query_preview");
	}
	/*ENDLOG*/
	$('shade').toggle();
	if(this.pendingElement)
		this.pendingElement.hide();
	this.pendingElement = new OBPendingIndicator($('shade'));
	this.pendingElement.show();

	if (!this.queries[0].isEmpty()){ //only do this if the query is not empty
		var ask = this.recurseQuery(0); // Get full ask syntax
		var params = ask + ",";
		params += $('layout_format').value + ',';
		params += $('layout_link').value + ',';
		params += $('layout_intro').value==""?",":$('layout_intro').value + ',';
		params += $('layout_sort').value== gLanguage.getMessage('QI_ARTICLE_TITLE')?",":$('layout_sort').value + ',';
		params += $('layout_limit').value==""?"50,":$('layout_limit').value + ',';
		params += $('layout_label').value==""?",":$('layout_label').value + ',';
		params += $('layout_order').value=="ascending"?'ascending,':'descending,';
		params += $('layout_default').value==""?',':$('layout_default').value;
		params += $('layout_headers').checked?'show':'hide';
		sajax_do_call('smwfQIAccess', ["getQueryResult", params], this.openPreview.bind(this));
	}
	else { // query is empty
		var request = Array();
		request.responseText = gLanguage.getMessage('QI_EMPTY_QUERY');
		this.openPreview(request);
	}
},

/**
* Displays the preview created by the server
* @param request Request of AJAX call
*/
openPreview:function(request){
	this.pendingElement.hide();
	$('fullpreviewbox').toggle();
	$('fullpreview').innerHTML = request.responseText;
},

/**
* Update breadcrumb navigation on top of the query tree. The BN
* will show the active query and all its parents as a mean to
* navigate
* @param id ID of the active query
*/
updateBreadcrumbs:function(id){
	var nav = Array();
	while(this.queries[id].getParent() != null){ //null = root query
		nav.unshift(id);
		id = this.queries[id].getParent();
	}
	nav.unshift(id);
	var html = "";
	for(var i=0; i<nav.length; i++){ //create html for BN
		if (i>0)
			html += "&gt;";
		html += '<span class="qibutton" onclick="qihelper.setActiveQuery(' + nav[i] + ')">';
		html += this.queries[nav[i]].getName() + '</span>';
	}
	html += "<hr/>";
	$('treeviewbreadcrumbs').innerHTML = html;
},

/**
* Updates the table column preview as well as the option box "Sort by".
* Both contain ONLY the properties of the root query that are shown in
* the result table
*/
updateColumnPreview:function(){
	var columns = new Array();
	columns.push(gLanguage.getMessage('QI_ARTICLE_TITLE')); // First column has no name in SMW, therefore we introduce our own one
	var tmparr = this.queries[0].getAllProperties(); //only root query, subquery results can not be shown in results
	for(var i=0; i<tmparr.length; i++){
		if(tmparr[i].isShown()){ //show
			columns.push(tmparr[i].getName());
		}
	}
	var tcp_html = '<table id="tcp" summary="Preview of table columns"><tr>'; //html for table column preview
	$('layout_sort').innerHTML = "";
	for(var i=0; i<columns.length; i++){
		tcp_html += "<td>" + columns[i] + "</td>";
		$('layout_sort').options[$('layout_sort').length] = new Option(columns[i], columns[i]); // add options to optionbox
	}
	tcp_html += "</tr></table>";
	$('tcpcontent').innerHTML = tcp_html;
},

/**
* Get the full ask syntax and the layout parameters of the whole query
* @return string containing full ask
*/
getFullAsk:function(){
	var asktext = this.recurseQuery(0);
	//get Layout parameters
	var starttag = "<ask "; //create ask tags and display params
	starttag += 'format="' + $('layout_format').value + '" ';
	starttag += $('layout_link').value == "subject" ? "" : ('link="' + $('layout_link').value + '" ');
	starttag += $('layout_intro').value == "" ? "" : ('intro="' + $('layout_intro').value + '" ');
	starttag += $('layout_sort').value == gLanguage.getMessage('QI_ARTICLE_TITLE') ? "" : ('sort="' + $('layout_sort').value + '" ');
	starttag += $('layout_limit').value == "" ? 'limit="20"' : ('limit="' + $('layout_limit').value + '" ');
	starttag += $('layout_label').value == "" ? "" : ('mainlabel="' + $('layout_label').value + '" ');
	starttag += $('layout_order').value == "ascending" ? 'order="ascending" ' : 'order="descending" ';
	starttag += $('layout_headers').checked ? '' : 'headers="hide" ';
	starttag += $('layout_default').value == "" ? '' : 'default="' + $('layout_default').value +'" ';
	starttag += ">";
	return starttag + asktext + "</ask>";
},

/**
* Recursive function that creates the ask syntax for the query with the ID provided
* and all its subqueries
* @param id ID of query to start
*/
recurseQuery:function(id){
	var sq = this.queries[id].getSubqueryIds();
	if(sq.length == 0)
		return this.queries[id].getAskText(); // no subqueries, get the asktext
	else {
		var tmptext = this.queries[id].getAskText();
		for(var i=0; i<sq.length; i++){
			var regex = null;
			eval('regex = /Subquery:' + sq[i] + ':/g'); //search for all Subquery tags and extract the ID
			tmptext = tmptext.replace(regex, '<q>' + this.recurseQuery(sq[i]) + '</q>'); //recursion
		}
		return tmptext;
	}
},

/**
* Creates a new dialogue for adding categories to the query
* @param reset indicates if this is a new dialogue or if it is loaded from the tree
*/
newCategoryDialogue:function(reset){
	$('qidelete').style.display = "none"; // New dialogue, no delete button
	autoCompleter.deregisterAllInputs();
	if(reset)
		this.loadedFromId = null;
	this.activeDialogue = "category";
	for (var i=0, n=$('dialoguecontent').rows.length; i<n; i++) //empty dialogue table
		$('dialoguecontent').deleteRow(0);
	var newrow = $('dialoguecontent').insertRow(-1); //create the dialogue
	var cell = newrow.insertCell(0);
	cell.innerHTML = gLanguage.getMessage('CATEGORY');
	cell = newrow.insertCell(1);
	cell.innerHTML = '<input type="text" id="input0" class="wickEnabled general-forms" typehint="14" autocomplete="OFF"/>'; // input field with autocompletion enabled
	cell = newrow.insertCell(2);
	cell.innerHTML = '<img src="' + this.imgpath + 'add.png" alt="addCategoryInput" onclick="qihelper.addDialogueInput()"/>'; // button to add another input for or-ed values
	this.activeInputs = 1;
	$('dialoguebuttons').style.display="";
	autoCompleter.registerAllInputs();
	if(reset)
		$('input0').focus();
},

/**
* Creates a new dialogue for adding instances to the query
* @param reset indicates if this is a new dialogue or if it is loaded from the tree
*/
newInstanceDialogue:function(reset){
	$('qidelete').style.display = "none";
	autoCompleter.deregisterAllInputs();
	if(reset)
		this.loadedFromId = null;
	this.activeDialogue = "instance";
	for (var i=0, n=$('dialoguecontent').rows.length; i<n; i++)
		$('dialoguecontent').deleteRow(0);
	var newrow = $('dialoguecontent').insertRow(-1);
	var cell = newrow.insertCell(0);
	cell.innerHTML = gLanguage.getMessage('QI_INSTANCE');
	cell = newrow.insertCell(1);
	cell.innerHTML = '<input type="text" id="input0" class="wickEnabled general-forms" typehint="0" autocomplete="OFF"/>';
	cell = newrow.insertCell(2);
	cell.innerHTML = '<img src="' + this.imgpath + 'add.png" alt="addInstanceInput" onclick="qihelper.addDialogueInput()"/>';
	this.activeInputs = 1;
	$('dialoguebuttons').style.display="";
	autoCompleter.registerAllInputs();
	if(reset)
		$('input0').focus();
},

/**
* Creates a new dialogue for adding properties to the query
* @param reset indicates if this is a new dialogue or if it is loaded from the tree
*/
newPropertyDialogue:function(reset){
	$('qidelete').style.display = "none";
	autoCompleter.deregisterAllInputs();
	if(reset)
		this.loadedFromId = null;
	this.activeDialogue = "property";
	this.propname = "";
	for (var i=0, n=$('dialoguecontent').rows.length; i<n; i++)
		$('dialoguecontent').deleteRow(0);
	var newrow = $('dialoguecontent').insertRow(-1); // First row: input for property name
	var cell = newrow.insertCell(0);
	cell.innerHTML = gLanguage.getMessage('QI_PROPERTYNAME');
	cell = newrow.insertCell(1);
	cell = newrow.insertCell(2);
	cell.innerHTML = '<input type="text" id="input0" class="wickEnabled general-forms" typehint="102" autocomplete="OFF" onblur="qihelper.getPropertyInformation()"/>';

	newrow = $('dialoguecontent').insertRow(-1); // second row: checkbox for display option
	cell = newrow.insertCell(0);
	cell.innerHTML = gLanguage.getMessage('QI_SHOW_PROPERTY');
	cell = newrow.insertCell(1);
	cell = newrow.insertCell(2);
	if(this.activeQueryId == 0)
		cell.innerHTML = '<input type="checkbox" id="input1">';
	else
		cell.innerHTML = '<input type="checkbox" disabled="disabled" id="input1">';

	newrow = $('dialoguecontent').insertRow(-1); // third row: input for property value and subquery
	cell = newrow.insertCell(0);
	cell.id = "mainlabel";
	cell.innerHTML = gLanguage.getMessage('QI_PAGE'); // we assume Page as type since this is standard
	cell = newrow.insertCell(1);
	cell.id = "restricionSelector";
	cell.innerHTML = this.createRestrictionSelector("=", true);
	cell = newrow.insertCell(2);
	cell.innerHTML = '<input class="wickEnabled general-forms" typehint="0" autocomplete="OFF" type="text" id="input2"/>';
	cell = newrow.insertCell(3);
	cell.innerHTML = '<img src="' + this.imgpath + 'add.png" alt="addPropertyInput" onclick="qihelper.addDialogueInput()"/>';
	cell = newrow.insertCell(4);
	cell.className = "subquerycell";
	cell.innerHTML = '&nbsp;' + gLanguage.getMessage('QI_USE_SUBQUERY') + '<input type="checkbox" id="usesub" onclick="qihelper.useSub(this.checked)"/>';
	this.activeInputs = 3;
	$('dialoguebuttons').style.display="";
	this.proparity = 2;
	autoCompleter.registerAllInputs();
	if(reset)
		$('input0').focus();
},

/**
* Empties the current dialogue and resets all relevant variables. Called on "cancel" button
*/
emptyDialogue:function(){
	this.activeDialogue = null;
	this.loadedFromId = null;
	this.propIsEnum = false;
	this.enumValues = null;
	this.propname = null;
	this.proparity = null;
	for (var i=0, n=$('dialoguecontent').rows.length; i<n; i++)
		$('dialoguecontent').deleteRow(0);
	$('dialoguebuttons').style.display="none";
	$('qistatus').innerHTML = "";
	$('qidelete').style.display = "none";
	this.activeInputs = 0;
},

/**
* Add another input to the current dialogue
*/
addDialogueInput:function(){
	autoCompleter.deregisterAllInputs();
	var delimg = wgScriptPath  + '/extensions/SemanticMediaWiki/skins/QueryInterface/images/delete.png';
	var newrow = $('dialoguecontent').insertRow(-1);
	newrow.id = "row" + newrow.rowIndex; //id needed for delete button later on
	var cell = newrow.insertCell(0);
	cell.innerHTML = gLanguage.getMessage('QI_OR');
	cell = newrow.insertCell(1);
	var param = $('mainlabel')?$('mainlabel').innerHTML:"";

	if(this.activeDialogue == "category") //add input fields according to dialogue
		cell.innerHTML = '<input class="wickEnabled general-forms" typehint="14" autocomplete="OFF" type="text" id="input' + this.activeInputs + '"/>';
	else if(this.activeDialogue == "instance")
		cell.innerHTML = '<input class="wickEnabled general-forms" typehint="0" autocomplete="OFF" type="text" id="input' + this.activeInputs + '"/>';
	else if(param == gLanguage.getMessage('QI_PAGE')){ //property dialogue & type = page
		cell.innerHTML = this.createRestrictionSelector("=", true);
		cell = newrow.insertCell(2);
		cell.innerHTML = '<input class="wickEnabled general-forms" typehint="0" autocomplete="OFF" type="text" id="input' + this.activeInputs + '"/>';
	}
	else{ // property, no page type
		if(this.numTypes[param.toLowerCase()]) // numeric type? operators possible
			cell.innerHTML = this.createRestrictionSelector("=", false);
		else
			cell.innerHTML = this.createRestrictionSelector("=", true);

		cell = newrow.insertCell(2);
		if(this.propIsEnum){ // if enumeration, a select box is used instead of a text input field
			var tmphtml = '<select id="input' + this.activeInputs + '" style="width:100%">';
			for(var i = 0; i < this.enumValues.length; i++){
				tmphtml += '<option value="' + this.enumValues[i] + '">' + this.enumValues[i] + '</option>';
			}
			tmphtml += '</select>';
			cell.innerHTML = tmphtml;
		} else { // no enumeration, no page type, simple input field
			cell.innerHTML = '<input type="text" id="input' + this.activeInputs + '"/>';
		}
	}
	cell = newrow.insertCell(-1);
	cell.innerHTML = '<img src="' + this.imgpath + 'delete.png" alt="deleteInput" onclick="qihelper.removeInput(' + newrow.rowIndex + ')"/>';
	$('input' + this.activeInputs).focus(); // focus created input
	this.activeInputs++;
	autoCompleter.registerAllInputs();
},

/**
* Removes an input if the remove icon is clicked
* @param index index of the table row to delete
*/
removeInput:function(index){
	$('dialoguecontent').removeChild($('row'+index));
	this.activeInputs--;
},

/**
* Is called everytime a user entered a property name and leaves the input field.
* Executes an ajax call which will get information about the property (if available)
*/
getPropertyInformation:function(){
	var propname = $('input0').value;
	if (propname != "" && propname != this.propname){ //only if not empty and name changed
		this.propname = propname;
		if(this.pendingElement)
			this.pendingElement.hide();
		this.pendingElement = new OBPendingIndicator($('input2'));
		this.pendingElement.show();
		sajax_do_call('smwfQIAccess', ["getPropertyInformation", propname], this.adaptDialogueToProperty.bind(this));
	}
},

/**
* Receives an XML string containing schema information of a property. Depending on this
* information, the dialogue has to be adapted. You need to consider: arity, enumeration
* and type of property.
* @param request Request of the ajax call
*/
adaptDialogueToProperty:function(request){
	this.propIsEnum = false;
	if (this.activeDialogue != null){ //check if user cancelled the dialogue whilst ajax call
		var oldval = $('input2').value;
		var oldcheck = $('usesub')?$('usesub').checked:false;
		for(var i=3, n = $('dialoguecontent').rows.length; i<n; i++){
			$('dialoguecontent').deleteRow(3); //delete all rows for value inputs
		}
		//create standard values in case request fails
		var arity = 2;
		this.proparity = 2;
		var parameterNames = [gLanguage.getMessage('QI_PAGE')];
		var parameterIsNumeric = [false];
		var possibleValues = new Array();

		if (request.status == 200) {
			var schemaData = GeneralXMLTools.createDocumentFromString(request.responseText);

			// read arity
			arity = parseInt(schemaData.documentElement.getAttribute("arity"));
			this.proparity = arity;
			parameterNames = [];
			parameterIsNumeric = [];
			//parse all parameter names
			for (var i = 0, n = schemaData.documentElement.childNodes.length; i < n; i++) {
				parameterNames.push(schemaData.documentElement.childNodes[i].getAttribute("name"));
				parameterIsNumeric.push(schemaData.documentElement.childNodes[i].getAttribute("isNumeric")=="true"?true:false);
				for (var j = 0, m = schemaData.documentElement.childNodes[i].childNodes.length; j<m; j++){
					possibleValues.push(schemaData.documentElement.childNodes[i].childNodes[j].getAttribute("value")); //contains allowed values for enumerations if applicable
				}
			}
		}
		if (arity == 2){
		// Speical treatment: binary properties support conjunction, therefore we need an "add" button
			$('mainlabel').innerHTML = parameterNames[0];
			if (parameterIsNumeric[0]){
				$('restricionSelector').innerHTML = this.createRestrictionSelector("=", false);
				autoCompleter.deregisterAllInputs();
				$('dialoguecontent').rows[2].cells[2].firstChild.className = "";
				autoCompleter.registerAllInputs();
			}
			else
				$('restricionSelector').innerHTML = this.createRestrictionSelector("=", true);
			if (parameterNames[0] == gLanguage.getMessage('QI_PAGE')){
				autoCompleter.deregisterAllInputs();
				$('dialoguecontent').rows[2].cells[2].firstChild.className = "wickEnabled";
				autoCompleter.registerAllInputs();
			}
			$('dialoguecontent').rows[2].cells[3].innerHTML = '<img src="' + this.imgpath + 'add.png" alt="addPropertyInput" onclick="qihelper.addDialogueInput()"/>';

			if(parameterNames[0] == gLanguage.getMessage('QI_PAGE')){ //if type is page, we need a subquery checkbox
				$('dialoguecontent').rows[2].cells[4].innerHTML = '&nbsp;' + gLanguage.getMessage('QI_USE_SUBQUERY') + '<input type="checkbox" id="usesub" onclick="qihelper.useSub(this.checked)"/>';
				$('dialoguecontent').rows[2].cells[4].className = "subquerycell";
				$('usesub').checked = oldcheck;
				this.activeInputs = 3;
			}
			else { //no checkbox for other types
				$('dialoguecontent').rows[2].cells[4].innerHTML = ""
				$('dialoguecontent').rows[2].cells[4].className = "";
				this.activeInputs = 3;
			}
			if(possibleValues.length > 0){ //enumeration
				this.propIsEnum = true;
				this.enumValues = new Array();
				autoCompleter.deregisterAllInputs();
				var option = '<select id="input2" style="width:100%">'; //create html for option box
				for(var i = 0; i < possibleValues.length; i++){
					this.enumValues.push(possibleValues[i]); //save enumeration values for later use
					option += '<option value="' + possibleValues[i] + '">' + possibleValues[i] + '</option>';
				}
				option += "</select>";
				$('dialoguecontent').rows[2].cells[2].innerHTML = option;
				autoCompleter.registerAllInputs();
			}
		}
		else {
		// properties with arity >2: no conjunction, no subqueries
			this.activeInputs = 3;
			$('dialoguecontent').rows[2].cells[3].innerHTML = "";
			$('dialoguecontent').rows[2].cells[4].innerHTML = "";
			$('dialoguecontent').rows[2].cells[4].className = "";
			$('mainlabel').innerHTML = parameterNames[0];
			if (parameterIsNumeric[0]){
				$('restricionSelector').innerHTML = this.createRestrictionSelector("=", false);
				autoCompleter.deregisterAllInputs();
				$('dialoguecontent').rows[2].cells[2].firstChild.className = "";
				autoCompleter.registerAllInputs();
			}
			else
				$('restricionSelector').innerHTML = this.createRestrictionSelector("=", true);

			for (var i=1; i<parameterNames.length; i++){
				var newrow = $('dialoguecontent').insertRow(-1);
				var cell = newrow.insertCell(0);
				cell.innerHTML = parameterNames[i]; // Label of cell is parameter name (ex.: Integer, Date,...)
				cell = newrow.insertCell(1);
				if (parameterIsNumeric[i])
					cell.innerHTML = this.createRestrictionSelector("=", false);
				else
					cell.innerHTML = this.createRestrictionSelector("=", true);

				cell = newrow.insertCell(2);
				if(parameterNames[i] == gLanguage.getMessage('QI_PAGE')) //Page means autocompletion enabled
					cell.innerHTML = '<input class="wickEnabled general-forms" typehint="0" autocomplete="OFF" type="text" id="input' + this.activeInputs + '"/>';
				else
					cell.innerHTML = '<input type="text" id="input' + this.activeInputs + '"/>';
				this.activeInputs++;
			}
		}
	}
	this.pendingElement.hide();
},

/**
* Loads values of an existing category group. This happens if a users clicks on a category
* folder in the query tree.
* @param id id of the category group (saved with the query tree)
*/
loadCategoryDialogue:function(id){
	this.newCategoryDialogue(false);
	this.loadedFromId = id;
	var cats = this.activeQuery.getCategoryGroup(id); //get the category group
	$('input0').value = cats[0];
	for (var i=1; i<cats.length; i++){
		this.addDialogueInput();
		$('input' + i).value = cats[i];
	}
	$('qidelete').style.display = ""; // show delete button
},

/**
* Loads values of an existing instance group. This happens if a users clicks on an instance
* folder in the query tree.
* @param id id of the instace group (saved with the query tree)
*/
loadInstanceDialogue:function(id){
	this.newInstanceDialogue(false);
	this.loadedFromId = id;
	var ins = this.activeQuery.getInstanceGroup(id);
	$('input0').value = ins[0];
	for (var i=1; i<ins.length; i++){
		this.addDialogueInput();
		$('input' + i).value = ins[i];
	}
	$('qidelete').style.display = "";
},

/**
* Loads values of an existing property group. This happens if a users clicks on a property
* folder in the query tree.
* WARNING: This is a MESS! Don't change anything unless you really know what you are doing.
* @param id id of the property group (saved with the query tree)
* @todo find a better way to do this
*/
loadPropertyDialogue:function(id){
	this.newPropertyDialogue(false);
	this.loadedFromId = id;
	var prop = this.activeQuery.getPropertyGroup(id);
	var vals = prop.getValues();
	this.proparity = prop.getArity();

	$('input0').value = prop.getName(); //fill input filed with name
	$('input1').checked = prop.isShown(); //check box if appropriate

	$('mainlabel').innerHTML = (vals[0][0] == "subquery"?gLanguage.getMessage('QI_PAGE'):vals[0][0]); //subquery means type is page

	if($('mainlabel').innerHTML != gLanguage.getMessage('QI_PAGE')){ //remove subquery box
		$('dialoguecontent').rows[2].cells[4].innerHTML = ""; //remove subquery checkbox since no subqueries are possible
		$('dialoguecontent').rows[2].cells[4].className = ""; //remove the seperator
	}

	var disabled = true;
	if(this.numTypes[vals[0][0].toLowerCase()]){ //is it a numeric type?
		disabled = false;

		$('dialoguecontent').rows[2].cells[1].innerHTML = this.createRestrictionSelector(vals[0][1], disabled);
		autoCompleter.deregisterAllInputs();
		$('dialoguecontent').rows[2].cells[2].firstChild.className = ""; //deactivate autocompletion
		autoCompleter.registerAllInputs();
	}
	if(vals[0][0] == "subquery"){ //grey out input field and check checkbox
		this.useSub(true);
		$('usesub').checked = true;
	} else {
		if(!prop.isEnumeration())
			$('input2').value = vals[0][2]; //enter the value into the input box
		else { //create option box for enumeration
			var tmphtml = '<select id="input2" style="width:100%">';
			this.enumValues = prop.getEnumValues();
			for(var i = 0; i < this.enumValues.length; i++){
				tmphtml += '<option value="' + this.enumValues[i] + '" ' + (this.enumValues[i]==vals[0][2]?'selected="selected"':'') + '>' + this.enumValues[i] + '</option>';
			}
			tmphtml += '</select>';
			$('dialoguecontent').rows[2].cells[2].innerHTML = tmphtml;
		}
	}
	if(prop.getArity() == 2){ // simply add further inputs if there are any
		if(!prop.isEnumeration()){
			for(var i=1; i<vals.length; i++){
				this.addDialogueInput();
				$('input' + (i+2)).value = vals[i][2];
				$('dialoguecontent').rows[i+2].cells[1].innerHTML = this.createRestrictionSelector(vals[i][1], disabled);
			}
		} else { //enumeration
			this.enumValues = prop.getEnumValues();
			for(var i=1; i<vals.length; i++){
				this.addDialogueInput();
				var tmphtml = '<select id="input' + (i+2) + '" style="width:100%">';
				//create the options; check which one was selected and add the 'selected' param then
				for(var j = 0; j < this.enumValues.length; j++){
					tmphtml += '<option value="' + this.enumValues[j] + '" ' + (this.enumValues[j]==vals[i][2]?'selected="selected"':'') + '>' + this.enumValues[j] + '</option>';
				}
				tmphtml += '</select>';
				$('dialoguecontent').rows[i+2].cells[2].innerHTML = tmphtml;
				$('dialoguecontent').rows[i+2].cells[1].innerHTML = this.createRestrictionSelector(vals[i][1], disabled);
			}
		}
	} else { // property with arity > 2
		autoCompleter.deregisterAllInputs();
		$('dialoguecontent').rows[2].cells[3].innerHTML = ""; //remove plus icon since no conjunction is possible
		$('dialoguecontent').rows[2].cells[4].innerHTML = ""; //remove subquery checkbox since no subqueries are possible
		$('dialoguecontent').rows[2].cells[4].className = ""; //remove the seperator
		for(var i=1; i<vals.length; i++){
			var row = $('dialoguecontent').insertRow(-1);
			var cell = row.insertCell(0);
			cell.innerHTML = vals[i][0]; // parameter name

			cell = row.insertCell(1); // restriction selector
			if(this.numTypes[vals[i][0].toLowerCase()])
				cell.innerHTML = this.createRestrictionSelector(vals[i][1], false);
			else
				cell.innerHTML = this.createRestrictionSelector(vals[i][1], true);

			cell = row.insertCell(2); // input field
			if(vals[i][0] == gLanguage.getMessage('QI_PAGE')) // autocompletion needed?
				cell.innerHTML = '<input type="text" class="wickEnabled general-forms" typehint="0" autocomplete="OFF" id="input' + (i+2) + '" value="' + vals[i][2] + '"/>';
			else
				cell.innerHTML = '<input type="text" id="input' + (i+2) + '" value="' + vals[i][2] + '"/>';
		}
		autoCompleter.registerAllInputs();
	}
	$('qidelete').style.display = "";
},

/**
* Deletes the currently shown dialogue from the query
*/
deleteActivePart:function(){
	switch(this.activeDialogue){
		case "category":
			/*STARTLOG*/
			if(window.smwhgLogger){
				var logstr = "Remove category " + this.activeQuery.getCategoryGroup(this.loadedFromId).join(",") + " from query";
			    smwhgLogger.log(logstr,"info","query_category_removed");
			}
			/*ENDLOG*/
			this.activeQuery.removeCategoryGroup(this.loadedFromId);
			break;
		case "instance":
			/*STARTLOG*/
			if(window.smwhgLogger){
				var logstr = "Remove instance " + this.activeQuery.getInstanceGroup(this.loadedFromId).join(",") + " from query";
			    smwhgLogger.log(logstr,"info","query_instance_removed");
			}
			/*ENDLOG*/
			this.activeQuery.removeInstanceGroup(this.loadedFromId);
			break;
		case "property":
			var pgroup = this.activeQuery.getPropertyGroup(this.loadedFromId);
			/*STARTLOG*/
			if(window.smwhgLogger){
				var logstr = "Remove property " + pgroup.getName() + " from query";
			    smwhgLogger.log(logstr,"info","query_property_removed");
			}
			/*ENDLOG*/
			if(pgroup.getValues()[0][0] == "subquery"){
				/*STARTLOG*/
				if(window.smwhgLogger){
					var logstr = "Remove subquery (property: " + pgroup.getName() + ") from query";
				    smwhgLogger.log(logstr,"info","query_subquery_removed");
				}
				/*ENDLOG*/
				//recursively delete all subqueries of this one. It's id is values[0][2]
				this.deleteSubqueries(pgroup.getValues()[0][2])
			}
			this.activeQuery.removePropertyGroup(this.loadedFromId);
			break;
	}
	this.emptyDialogue();
	this.activeQuery.updateTreeXML();
	this.updateColumnPreview();
},

/**
* Recursively deletes all subqueries of a given query
* @param id ID of the query to start with
*/
deleteSubqueries:function(id){
	if(this.queries[id].hasSubqueries()){
		for(var i = 0; i < this.queries[id].getSubqueryIds().length; i++){
			this.deleteSubqueries(this.queries[id].getSubqueryIds()[i]);
		}
	}
	this.queries[id] = null;
},

/**
* Creates an HTML option with the different possible restrictions
* @param disabled enabled only for numeric datatypes
*/
createRestrictionSelector:function(option, disabled){
	var html = disabled?'<select disabled="disabled">':'<select>';
	switch (option){
		case "=":
			html += '<option value="=" selected="selected">=</option><option value="&lt;=">&lt;=</option><option value="&gt;=">&gt;=</option><option value="!=">!=</option></select>';
			break;
		case "<=":
			html += '<option value="=">=</option><option value="&lt;=" selected="selected">&lt;=</option><option value="&gt;=">&gt;=</option><option value="!=">!=</option></select>';
			break;
		case ">=":
			html += '<option value="=">=</option><option value="&lt;=">&lt;=</option><option value="&gt;=" selected="selected">&gt;=</option><option value="!=">!=</option></select>';
			break;
		case "!=":
			html += '<option value="=">=</option><option value="&lt;=">&lt;=</option><option value="&gt;=">&gt;=</option><option value="!=" selected="selected">!=</option></select>';
			break;
	}
	return html;
},

/**
* Activate or deactivate input if subquery checkbox is checked
* @param checked did user check or uncheck?
*/
useSub:function(checked){
	if(checked){
		$('input2').value="";
		$('input2').disabled = true;
		$('input2').style.background = "#DDDDDD";
	} else {
		$('input2').disabled = false;
		$('input2').style.background = "#FFFFFF";
	}
},

/**
* Adds a new Category/Instance/Property Group to the query
*/
add:function(){
	if(this.activeDialogue == "category"){
		this.addCategoryGroup();
	} else if(this.activeDialogue == "instance"){
		this.addInstanceGroup();
	} else {
		this.addPropertyGroup();
	}
	this.activeQuery.updateTreeXML();
	this.loadedFromID = null;
},

/**
* Reads the input fields of a category dialogue and adds them to the query
*/
addCategoryGroup:function(){
	var tmpcat = Array();
	var allinputs = true; // checks if all inputs are set for error message
	for(var i=0; i<this.activeInputs; i++){
		var tmpid = "input" + i;
		tmpcat.push($(tmpid).value);
		if($(tmpid).value == "")
			allinputs = false;
	}
	if(!allinputs)
		$('qistatus').innerHTML = gLanguage.getMessage('QI_ENTER_CATEGORY'); //show error
	else {
		/*STARTLOG*/
		if(window.smwhgLogger){
			var logstr = "Add category " + tmpcat.join(",") + " to query";
		    smwhgLogger.log(logstr,"info","query_category_added");
		}
		/*ENDLOG*/
		this.activeQuery.addCategoryGroup(tmpcat, this.loadedFromId); //add to query
		this.emptyDialogue();
	}
},

/**
* Reads the input fields of an instance dialogue and adds them to the query
*/
addInstanceGroup:function(){
	var tmpins = Array();
	var allinputs = true;
	for(var i=0; i<this.activeInputs; i++){
		var tmpid = "input" + i;
		tmpins.push($(tmpid).value);
		if($(tmpid).value == "")
			allinputs = false;
	}
	if(!allinputs)
		$('qistatus').innerHTML = gLanguage.getMessage('QI_ENTER_INSTANCE');
	else {
		/*STARTLOG*/
		if(window.smwhgLogger){
			var logstr = "Add instance " + tmpins.join(",") + " to query";
		    smwhgLogger.log(logstr,"info","query_instance_added");
		}
		/*ENDLOG*/
		this.activeQuery.addInstanceGroup(tmpins, this.loadedFromId);
		this.emptyDialogue();
	}
},

/**
* Reads the input fields of a property dialogue and adds them to the query
*/
addPropertyGroup:function(){
	var pname = $('input0').value;
	var subqueryIds = Array();
	if (pname == ""){ //no name entered?
		$('qistatus').innerHTML = gLanguage.getMessage('QI_ENTER_PROPERTY_NAME');
	} else {
		var pshow = $('input1').checked; // show in results?
		var arity = this.proparity;
		var pgroup = new PropertyGroup(pname, arity, pshow, this.propIsEnum, this.enumValues); //create propertyGroup
		for(var i = 2; i<$('dialoguecontent').rows.length; i++){
			var paramvalue = $('input' + i).value;
			paramvalue = paramvalue==""?"*":paramvalue; //no value is replaced by "*" which means all values
			var paramname = $('dialoguecontent').rows[i].cells[0].innerHTML;
			if(paramname == gLanguage.getMessage('QI_PAGE') && arity == 2 && $('usesub').checked){ //Subquery?
				paramname = "subquery";
				paramvalue = this.nextQueryId;
				subqueryIds.push(this.nextQueryId);
				this.addQuery(this.activeQueryId, pname);
				/*STARTLOG*/
				if(window.smwhgLogger){
					var logstr = "Add subquery to query, property '" + pname + "'";
				    smwhgLogger.log(logstr,"info","query_subquery_added");
				}
				/*ENDLOG*/
			}
			var restriction = $('dialoguecontent').rows[i].cells[1].firstChild.value;
			pgroup.addValue(paramname, restriction, paramvalue); // add a value group to the property group
		}
		/*STARTLOG*/
		if(window.smwhgLogger){
			var logstr = "Add property " + pname + " to query";
		    smwhgLogger.log(logstr,"info","query_property_added");
		}
		/*ENDLOG*/
		this.activeQuery.addPropertyGroup(pgroup, subqueryIds, this.loadedFromId); //add the property group to the query
		this.emptyDialogue();
		this.updateColumnPreview();
	}
},

/**
* copies the full query text to the clients clipboard. Works on IE and FF depending on the users
* security settings.
*/
copyToClipboard:function(){

	if(this.queries[0].isEmpty() ){
		alert(gLanguage.getMessage('QI_EMPTY_QUERY'));
	} else {
		/*STARTLOG*/
		if(window.smwhgLogger){
		    smwhgLogger.log("Copy query to clipboard","info","query_copied");
		}
		/*ENDLOG*/
		var text = this.getFullAsk();
	 	if (window.clipboardData){ //IE
			window.clipboardData.setData("Text", text);
			alert(gLanguage.getMessage('QI_CLIPBOARD_SUCCESS'));
		}
	  	else if (window.netscape) {
			netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');
			var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
			if (!clip){
				alert(gLanguage.getMessage('QI_CLIPBOARD_FAIL'));
				return;
			}
			var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
			if (!trans){
				alert(gLanguage.getMessage('QI_CLIPBOARD_FAIL'));
				return;
			}
			trans.addDataFlavor('text/unicode');
			var str = new Object();
			var len = new Object();
			var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
			str.data=text;
			trans.setTransferData("text/unicode",str,text.length*2);
			var clipid=Components.interfaces.nsIClipboard;
			if (!clip){
				alert(gLanguage.getMessage('QI_CLIPBOARD_FAIL'));
				return;
			}
			clip.setData(trans,null,clipid.kGlobalClipboard);
			alert(gLanguage.getMessage('QI_CLIPBOARD_SUCCESS'));
		}
		else{
			alert(gLanguage.getMessage('QI_CLIPBOARD_FAIL'));
		}
	}
},

showLoadDialogue:function(){
	//List of saved queries with filter
	//load
	sajax_do_call('smwfQIAccess', ["loadQuery", "Query:SaveTestQ"], this.loadQuery.bind(this));

},

loadQuery:function(request){
	/* if(request.responseText == "false"){
		//error handling
	} else {
		var query = request.responseText.substring(request.responseText.indexOf(">"), request.responseText.indexOf("</ask>"));
		var elements = query.split("[[");
	} */
	alert(request.responseText);
},

showSaveDialogue:function(){
	$('shade').toggle();
	$('savedialogue').toggle();
},

doSave:function(){
	if (!this.queries[0].isEmpty()){
		if(this.pendingElement)
			this.pendingElement.hide();
		this.pendingElement = new OBPendingIndicator($('savedialogue'));
		this.pendingElement.show();
		var params = $('saveName').value + ",";
		params += this.getFullAsk();
		sajax_do_call('smwfQIAccess', ["saveQuery", params], this.saveDone.bind(this));
	}
	else {
		var request = Array();
		request.responseText = "empty";
		this.saveDone(request);
	}
},

saveDone:function(request){
	this.pendingElement.hide();
	if(request.responseText == "empty"){
		alert(gLanguage.getMessage('QI_EMPTY_QUERY'));
		$('shade').toggle();
		$('savedialogue').toggle();
		$('saveName').value = "";
	}
	else if(request.responseText == "exists"){
		alert(gLanguage.getMessage('QI_QUERY_EXISTS'));
		$('saveName').value = "";
	}
	else if(request.responseText == "true"){
		alert(gLanguage.getMessage('QI_QUERY_SAVED'));
		$('shade').toggle();
		$('savedialogue').toggle();
		$('saveName').value = "";
	}
	else { // Unknown error
		alert(gLanguage.getMessage('QI_SAVE_ERROR'));
		$('shade').toggle();
		$('savedialogue').toggle();
	}
},

exportToXLS:function(){
	if (!this.queries[0].isEmpty()){
		var ask = this.recurseQuery(0);
		var params = ask + ",";
		params += $('layout_format').value + ',';
		params += $('layout_link').value + ',';
		params += $('layout_intro').value==""?",":$('layout_intro').value + ',';
		params += $('layout_sort').value== gLanguage.getMessage('QI_ARTICLE_TITLE')?",":$('layout_sort').value + ',';
		params += $('layout_limit').value==""?"50,":$('layout_limit').value + ',';
		params += $('layout_label').value==""?",":$('layout_label').value + ',';
		params += $('layout_order').value=="ascending"?'ascending,':'descending,';
		params += $('layout_default').value==""?',':$('layout_default').value;
		params += $('layout_headers').checked?'show':'hide';
		sajax_do_call('smwfQIAccess', ["getQueryResult", params], this.initializeDownload.bind(this));
	}
	else {
		var request = Array();
		request.responseText = gLanguage.getMessage('QI_EMPTY_QUERY');
		this.openPreview(request);
	}
},

initializeDownload:function(request){
	encodedHtml = escape(request.responseText);
	encodedHtml = encodedHtml.replace(/\//g,"%2F");
	encodedHtml = encodedHtml.replace(/\?/g,"%3F");
	encodedHtml = encodedHtml.replace(/=/g,"%3D");
	encodedHtml = encodedHtml.replace(/&/g,"%26");
	encodedHtml = encodedHtml.replace(/@/g,"%40");
	var url = wgServer + wgScriptPath + "/extensions/SMWHalo/specials/SMWQueryInterface/SMW_QIExport.php?q=" + encodedHtml;
	window.open(url, "Download", 'height=1,width=1');
}

} //end class qiHelper

var PropertyGroup = Class.create();
PropertyGroup.prototype = {

	initialize:function(name, arity, show, isEnum, enumValues){
		this.name = name;
		this.arity = arity;
		this.show = show;
		this.isEnum = isEnum;
		this.enumValues = enumValues;
		this.values = Array(); // paramName, retriction, paramValue
	},

	addValue:function(name, restriction, value){
		this.values[this.values.length] = new Array(name, restriction, value);
	},

	getName:function(){
		return this.name;
	},

	getArity:function(){
		return this.arity;
	},

	isShown:function(){
		return this.show;
	},

	getValues:function(){
		return this.values;
	},

	isEnumeration:function(){
		return this.isEnum;
	},

	getEnumValues:function(){
		return this.enumValues;
	}
}





// qi.js
// under GPL-License
//var qihelper = new QIHelper();
var qihelper = null;
Event.observe(window, 'load', initialize);

function initialize(){
	qihelper = new QIHelper();
}

function plusminus(){
	if($('tcpplusminus').className == "plus"){
		$('tcpplusminus').removeClassName("plus");
		$('tcpplusminus').addClassName("minus");
	} else {
		$('tcpplusminus').removeClassName("minus");
		$('tcpplusminus').addClassName("plus");
	}
}

function switchtcp(){
	if($("tcp_boxcontent").style.display == "none"){
		$("tcp_boxcontent").style.display = "";
		$("tcptitle-link").removeClassName("plusminus");
		$("tcptitle-link").addClassName("minusplus");
	}
	else {
		$("tcp_boxcontent").style.display = "none";
		$("tcptitle-link").removeClassName("minusplus");
		$("tcptitle-link").addClassName("plusminus");
	}
}

function switchlayout(){
	if($("layoutcontent").style.display == "none"){
		$("layoutcontent").style.display = "";
		$("layouttitle-link").removeClassName("plusminus");
		$("layouttitle-link").addClassName("minusplus");
	}
	else {
		$("layoutcontent").style.display = "none";
		$("layouttitle-link").removeClassName("minusplus");
		$("layouttitle-link").addClassName("plusminus");
	}
}

