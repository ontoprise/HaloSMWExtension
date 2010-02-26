/**
 * @file
 * @ingroup SMWHaloSpecials
 * @ingroup SMWHaloQueryInterface
 * 
 * @author Markus Nitsche
 */
var xslStylesheet;
var myDOM;
var xmldoc;

function updateQueryTree(xmltext){

	var xmldoc = GeneralXMLTools.createDocumentFromString(xmltext);
	if (OB_bd.isGeckoOrOpera) {
		var QI_xsltProcessor_gecko = new XSLTProcessor();

	  	var myXMLHTTPRequest = new XMLHttpRequest();
	  	myXMLHTTPRequest.open("GET", wgServer + wgScriptPath + "/extensions/SMWHalo/skins/QueryInterface/treeview.xslt", false);
	 	myXMLHTTPRequest.send(null);
	  	var xslRef = GeneralXMLTools.createDocumentFromString(myXMLHTTPRequest.responseText);

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