
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2004 Jean-Michel Garnier (garnierjm@yahoo.fr)
*  All rights reserved
*
*  This script is part of the phpXplorer project. The phpXplorer project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt distributed with these scripts.
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


  	var xslRef = GeneralXMLTools.createDocumentFromString(myXMLHTTPRequest.responseText);
 
  	// Finally import the .xsl
  	this.OB_xsltProcessor_gecko.importStylesheet(xslRef);
  	this.OB_xsltProcessor_gecko.setParameter(null, "param-img-directory", wgServer + wgScriptPath + "/extensions/SMWHalo/skins/OntologyBrowser/images/");
	this.OB_xsltProcessor_gecko.setParameter(null, "param-wiki-path", wgServer + wgScriptPath);
	this.OB_xsltProcessor_gecko.setParameter(null, "param-ns-concept", gLanguage.getMessage('CATEGORY_NS_WOC'));
	this.OB_xsltProcessor_gecko.setParameter(null, "param-ns-property", gLanguage.getMessage('PROPERTY_NS_WOC'));
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
		this.OB_xsltProcessor_ie.addParameter("param-wiki-path", wgServer + wgScriptPath);
		this.OB_xsltProcessor_ie.addParameter("param-ns-concept", gLanguage.getMessage('CATEGORY_NS_WOC'));
		this.OB_xsltProcessor_ie.addParameter("param-ns-property", gLanguage.getMessage('PROPERTY_NS_WOC'));
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
  	 	
  	 	// translate XSLT output
  	 	var languageNodes = GeneralXMLTools.getNodeByText(document, '{{');
   		var regex = new RegExp("\{\{(\\w+)\}\}");
		languageNodes.each(function(n) { 
			var vars;
			var text = n.textContent;
			while (vars = regex.exec(text)) { 
				text = text.replace(new RegExp('\{\{'+vars[1]+'\}\}', "g"), gLanguage.getMessage(vars[1]));
			}
			n.textContent = text;
   		});
  		
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
      
      // translate XSLT output
      var translatedOutput = this.OB_xsltProcessor_ie.output;
      var regex = new RegExp("\{\{(\\w+)\}\}");
  	  var vars;
	  while (vars = regex.exec(translatedOutput)) { 
			translatedOutput = translatedOutput.replace(new RegExp('\{\{'+vars[1]+'\}\}', "g"), gLanguage.getMessage(vars[1]));
	  }
  	  // insert HTML
      node.innerHTML = translatedOutput;
    
  }
}
};

 
// one global tree transformer
var transformer = new TreeTransformer();



// ---------------------------------------------------------------------------









