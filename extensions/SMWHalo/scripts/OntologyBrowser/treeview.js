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

/**
 * @file
 * @ingroup SMWHaloSpecials
 * @ingroup SMWHaloOntologyBrowser
 * 
 * Heaviliy modified by Ontoprise 2007
 * @author Kai K�hn
 */

// ---------------------------------------------------------------------------
// --- Name: Easy DHTML Treeview --
// --- Original idea by : D.D. de Kerf --
// --- Updated by Jean-Michel Garnier, garnierjm@yahoo.fr --
// ---------------------------------------------------------------------------
window.TreeTransformer = Class.create();
TreeTransformer.prototype = {

	initialize : function(styleSheetPath) {
		this.styleSheetPath = styleSheetPath;
		// initialize root nodes after document has been loaded
		Event.observe(window, 'load', this.initializeTree
				.bindAsEventListener(this));
		this.languageProvider = new Array();
	},
	initializeTree : function() {

		// deactivate not supported browsers
		if (OB_bd.isKonqueror) {
			alert(gLanguage.getMessage('KS_NOT_SUPPORTED'));
			return;
		}

		if (OB_bd.isGeckoOrOpera) {
			this.OB_xsltProcessor_gecko = new XSLTProcessor();

			// Load the xsl file using synchronous (third param is set to false)
			// XMLHttpRequest
			var myXMLHTTPRequest = new XMLHttpRequest();
			myXMLHTTPRequest.open("GET", wgServer + wgScriptPath
					+ this.styleSheetPath, false);
			myXMLHTTPRequest.send(null);

			var xslRef = GeneralXMLTools
					.createDocumentFromString(myXMLHTTPRequest.responseText);

			// Finally import the .xsl
			this.OB_xsltProcessor_gecko.importStylesheet(xslRef);
			this.OB_xsltProcessor_gecko.setParameter(null,
					"param-img-directory", wgServer + wgScriptPath);
			this.OB_xsltProcessor_gecko.setParameter(null, "param-wiki-path",
					wgServer + wgArticlePath);
			this.OB_xsltProcessor_gecko.setParameter(null, "param-ns-concept",
					gLanguage.getMessage('CATEGORY_NS_WOC', 'cont'));
			this.OB_xsltProcessor_gecko.setParameter(null, "param-ns-property",
					gLanguage.getMessage('PROPERTY_NS_WOC', 'cont'));
		} else if (OB_bd.isIE) {

			// create MSIE DOM object
			var xsl = new ActiveXObject("MSXML2.FreeThreadedDOMDocument");
			xsl.async = false;

			// load stylesheet
			xsl.load(wgServer + wgScriptPath + this.styleSheetPath);

			// create XSLT Processor
			var template = new ActiveXObject("MSXML2.XSLTemplate");
			template.stylesheet = xsl;
			this.OB_xsltProcessor_ie = template.createProcessor();
			this.OB_xsltProcessor_ie.addParameter("param-img-directory",
					wgServer + wgScriptPath);
			this.OB_xsltProcessor_ie.addParameter("param-wiki-path", wgServer
					+ wgArticlePath);
			this.OB_xsltProcessor_ie.addParameter("param-ns-concept", gLanguage
					.getMessage('CATEGORY_NS_WOC', 'cont'));
			this.OB_xsltProcessor_ie.addParameter("param-ns-property",
					gLanguage.getMessage('PROPERTY_NS_WOC', 'cont'));
		}

	},

	/**
	 * Adds a language providers. It must provide a function with parameter
	 * 'id'.
	 * 
	 */
	addLanguageProvider : function(provider) {
		if (typeof (provider) == 'function') {
			this.languageProvider.push(provider);
		}
	},

	/*
	 * Transforms a AJAX request xml output using the predefined stylesheet
	 * (treeview.xslt) and adds under the given node.
	 */
	transformResultToHTML : function(request, node, level) {
		if (request.status != 200) {
			if (request.status == 0) {
				alert("Error: Could not connect to wiki. Webserver running?");
			} else {
				alert("Error: " + request.status + " " + request.statusText
						+ ": " + request.responseText);
			}
			return;
		}

		if (request.responseText == '') {
			// result is empty;
			return;
		}
		// parse xml and transform it to HTML
		if (OB_bd.isGecko) {
			var parser = new DOMParser();
			var xmlDoc = parser.parseFromString(request.responseText,
					"text/xml");
			this.transformXMLToHTML(xmlDoc, node, level ? level : false);
			return xmlDoc;
		} else if (OB_bd.isIE) {
			var myDocument = new ActiveXObject("Microsoft.XMLDOM")
			myDocument.async = "false";
			myDocument.loadXML(request.responseText);
			this.transformXMLToHTML(myDocument, node, level ? level : false);
			return myDocument;
		}
	},

	/*
	 * xmlDoc: document to transform node: HTML node to add transformed document
	 * to level: true = root level, otherwise false (only relevant for tree
	 * transformations)
	 */
	transformXMLToHTML : function(xmlDoc, node, level) {
		if (OB_bd.isGecko) {
			// set startDepth parameter. start on root level or below?
			this.OB_xsltProcessor_gecko.setParameter(null, "startDepth",
					level ? 1 : 2);

			// transform, remove all existing and add new generated nodes
			var fragment = this.OB_xsltProcessor_gecko.transformToFragment(
					xmlDoc, document);
			GeneralXMLTools.removeAllChildNodes(node);
			node.appendChild(fragment);

			// translate XSLT output

			// replace language constant in text nodes
			var languageNodes = GeneralXMLTools.getNodeByText(document, '{{');
			var regex = new RegExp("\{\{(\\w+)\}\}");
			var lp = this.languageProvider;
			languageNodes.each(function(n) {
				var vars;
				var text = n.textContent;
				while (vars = regex.exec(text)) {
					var reg_exp = new RegExp('\{\{' + vars[1] + '\}\}', "g");

					// use local language data
					var msg = gLanguage.getMessage(vars[1])
					if (msg != vars[1]) {
						text = text.replace(reg_exp, msg);
					}

					// use other language providers
					lp.each(function(provider) {
						var msg = provider(vars[1]);
						if (msg != vars[1])
							text = text.replace(reg_exp, msg);

					});
				}
				n.textContent = text;

			});

			// replace language constants in HTML attribute nodes
			var languageAtts = GeneralXMLTools.getAttributeNodeByText(document,
					'{{');
			var regex = new RegExp("\{\{(\\w+)\}\}");
			var lp = this.languageProvider;
			languageAtts.each(function(n) {
				var vars;
				var text = n.textContent;
				while (vars = regex.exec(text)) {
					var reg_exp = new RegExp('\{\{' + vars[1] + '\}\}', "g");

					// use local language data
					var msg = gLanguage.getMessage(vars[1])
					if (msg != vars[1]) {
						text = text.replace(reg_exp, msg);
					} else {
						// probably missing language constant
						text = text.replace(reg_exp, "!!" + msg + "!!");
					}

					// use other language providers
					lp.each(function(provider) {
						var msg = provider(vars[1]);
						if (msg != vars[1])
							text = text.replace(reg_exp, msg);
						else {
							// probably missing language constant
							text = text.replace(reg_exp, "!!" + msg + "!!");
						}
					});
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
			for ( var i = 0, n = node.childNodes.length; i < n; i++) {
				GeneralBrowserTools.purge(node.childNodes[i]);
			}

			// translate XSLT output
			var translatedOutput = this.OB_xsltProcessor_ie.output;
			var regex = new RegExp("\{\{(\\w+)\}\}");
			var vars;
			while (vars = regex.exec(translatedOutput)) {
				var msg = gLanguage.getMessage(vars[1]);
				if (msg != vars[1]) {
					translatedOutput = translatedOutput.replace(new RegExp(
							'\{\{' + vars[1] + '\}\}', "g"), msg);
				}

				var lp = this.languageProvider;
				for ( var i = 0; i < lp.length; i++) {
					var msg = lp[i](vars[1]);
					if (msg != vars[1]) {
						translatedOutput = translatedOutput.replace(new RegExp(
								'\{\{' + vars[1] + '\}\}', "g"), msg);
					}
				}
			}

			// insert HTML
			node.innerHTML = translatedOutput;

		}
	}
};

// one global tree transformer
var transformer = new TreeTransformer(
		"/extensions/SMWHalo/skins/OntologyBrowser/treeview.xslt");

function resetOntologyBrowser() {
	
	// set content of category and property view invalid
	dataAccess.OB_categoriesInitialized = false;
	dataAccess.OB_attributesInitialized = false;
	
	// refresh the currently visible tree
	if (globalActionListener.activeTreeName == 'categoryTree') {
		dataAccess.initializeRootCategories(0, true);
	} else if (globalActionListener.activeTreeName == 'propertyTree'){
		dataAccess.initializeRootProperties(0, true);
	}
	
	if ($('instanceList') != null && $('instanceList').down() != null) {
		$('instanceList').down().remove();
	}
	if ($('relattributes') != null && $('relattributes').down() != null) {
		$('relattributes').down().remove();
	}
}

Event.observe(window, 'load', function() { // call initialize hook
	dataAccess = new OBDataAccess();
	dataAccess.initializeTree(null);

	// initialize event listener for FilterBrowser
	var filterBrowserInput = $("FilterBrowserInput");
	Event.observe(filterBrowserInput, "keyup",
			globalActionListener.filterBrowsing.bindAsEventListener(
					globalActionListener, false));

	// initialize handlers for property switches
	var showInheritedPropertySwitch = $("directPropertySwitch");
	Event.observe(showInheritedPropertySwitch, "change",
			schemaActionPropertyListener.reloadProperties.bindAsEventListener(
					schemaActionPropertyListener, false));

	var showRangesForPropertySwitch = $("showForRange");
	Event.observe(showRangesForPropertySwitch, "change",
			schemaActionPropertyListener.reloadProperties.bindAsEventListener(
					schemaActionPropertyListener, false));
});

// ---------------------------------------------------------------------------

