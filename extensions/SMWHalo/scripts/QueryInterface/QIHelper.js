/*
 *  Query Interface for Semantic MediaWiki
 *  Developed by Markus Nitsche <fitsch@gmail.com>
 *
 *  QIHelper.js
 *  Manages major functionalities and GUI of the Query Interface
 *  @author Markus Nitsche [fitsch@gmail.com]
 *  @author Joerg Heizmann
 */

var qihelper = null;

var QIHelper = Class.create();
QIHelper.prototype = {

	/**
	 * Initialize the QIHelper object and all variables
	 */
	initialize : function() {
		this.imgpath = wgScriptPath + '/extensions/SMWHalo/skins/QueryInterface/images/';
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
		this.queryPartsFromInitByAsk = Array();
		this.propertyTypesList = new PropertyList();

		this.specialQPParameters = new Array();

                // if triplestore is enabled in wiki, the <input id="usetriplestore"> exists
                if ($('usetriplestore'))
                    Event.observe($('usetriplestore'),'click', this.updatePreview.bind(this));
	},

	/**
	 * define here that the caller is the excel bridge
	 */
	setExcelBridge : function() {
		this.isExcelBridge = 1;
	},

	/**
	 * Called whenever table column preview is minimized or maximized
	 */
	switchtcp : function() {
		if ($("tcp_boxcontent").style.display == "none") {
			$("tcp_boxcontent").style.display = "";
			$("tcptitle-link").removeClassName("plusminus");
			$("tcptitle-link").addClassName("minusplus");
		} else {
			$("tcp_boxcontent").style.display = "none";
			$("tcptitle-link").removeClassName("minusplus");
			$("tcptitle-link").addClassName("plusminus");
		}
	},

	/**
	 * Called whenever query layout manager is minimized or maximized
	 */
	switchlayout : function() {
		if ($("layoutcontent").style.display == "none") {
			$("layoutcontent").style.display = "";
			$("queryprinteroptions").style.display = "";
			$("layouttitle-link").removeClassName("plusminus");
			$("layouttitle-link").addClassName("minusplus");
			this.getSpecialQPParameters($('layout_format').value);
		} else {
			$("layoutcontent").style.display = "none";
			$("queryprinteroptions").style.display = "none";
			$("layouttitle-link").removeClassName("minusplus");
			$("layouttitle-link").addClassName("plusminus");
		}
	},

	/**
	 * Called whenever preview result printer needs to be updated
	 */
	updatePreview : function() {
		// update result preview
		if ($("previewcontent").style.display == "") {
			this.previewResultPrinter();
		}
	},

	getSpecialQPParameters : function(qp, callWhenFinished) {
		var callback = function(request) {
			this.parameterPendingElement.hide();
			var columns = 3;
			var html = gLanguage.getMessage('QI_SPECIAL_QP_PARAMS') + " <i>"
					+ qp + '</i>:<table style="width: 100%;">';
			var qpParameters = request.responseText.evalJSON();
			var i = 0;
			qpParameters.each(function(e) {
				if (i % columns == 0)
					html += "<tr>"
				html += '<td onmouseover="Tip(\'' + e.mParamDescription
						+ '\');">' + e.mParamName + "</td>";
				if (e.mValues instanceof Array) {
					html += '<td>' + createSelectionBox(e.mParam, e.mValues)
							+ "</td>";
				} else if (e.mValues == '<string>' || e.mValues == '<number>') {
					html += '<td>' + createInputBox(e.mParam, e.mValues, e.mConstraints)
							+ "</td>";
				} else if (e.mValues == '<boolean>') {
					html += '<td>' + createCheckBox(e.mParam, e.mDefault)
							+ "</td>";
				}

				if (i % columns == 2)
					html += "</tr>"
				i++;
			});
			html += '</table>';
			autoCompleter.deregisterAllInputs();
			$('queryprinteroptions').innerHTML = html;
			autoCompleter.registerAllInputs();
			this.specialQPParameters = qpParameters;
			if (callWhenFinished) callWhenFinished();
		}
		var createSelectionBox = function(id, values) {
			var html = '<select id="' + 'qp_param_' + id + '" onchange="qihelper.updatePreview()">';
			values.each(function(v) {
				html += '<option value="' + v + '">' + v + '</option>';
			})
			html += '</select>';
			return html;
		}
		var createInputBox = function(id, values, constraints) {
			var aclAttributes = "";
			if (constraints != null) {
				aclAttributes = 'class="wickEnabled" constraints="'+constraints+'"';
			}
			var html = '<input id="' + 'qp_param_' + id + '" type="text" '+aclAttributes+' onchange="qihelper.updatePreview()"/>';
			return html;
		}
		var createCheckBox = function(id, defaultValue) {
			var defaultValueAtt = defaultValue ? 'checked="checked"' : '';
			var html = '<input id="' + 'qp_param_' + id + '" type="checkbox" '
					+ defaultValueAtt + ' onchange="qihelper.updatePreview()"/>';
			return html;
		}
		if (this.parameterPendingElement)
            this.parameterPendingElement.hide();
        this.parameterPendingElement = new OBPendingIndicator($('querylayout'));
        this.parameterPendingElement.show();
        
		sajax_do_call('smwf_qi_QIAccess', [ 'getSupportedParameters', qp ],
				callback.bind(this));

	},

	serializeSpecialQPParameters : function(sep) {
		var paramStr = "";
		var first = true;
		this.specialQPParameters.each(function(p) {
			var element = $('qp_param_' + p.mParam);
			if (p.mValues == '<boolean>' && element.checked) {
				paramStr += first ? p.mParam : sep + " " + p.mParam;
			} else {
				if (element.value != "" && element.value != p.mDefault) {
					paramStr += first ? p.mParam + "=" + element.value : sep + " " + p.mParam + "=" + element.value;
				}
			}
			first = false;
		});
		return paramStr;
	},

	/**
	 * Called whenever preview result printer is minimized or maximized
	 */
	switchpreview : function() {
		if ($("previewcontent").style.display == "none") {
			$("previewcontent").style.display = "";
			$("previewtitle-link").removeClassName("plusminus");
			$("previewtitle-link").addClassName("minusplus");
			// update preview
			this.previewResultPrinter();
		} else {
			$("previewcontent").style.display = "none";
			$("previewtitle-link").removeClassName("minusplus");
			$("previewtitle-link").addClassName("plusminus");
		}
	},

	/**
	 * Performs ajax call on startup to get a list of all numeric datatypes.
	 * Needed to find out if users can use operators (< and >)
	 */
	getNumericDatatypes : function() {
		sajax_do_call('smwf_qi_QIAccess', [ "getNumericTypes", "dummy" ],
				this.setNumericDatatypes.bind(this));
	},

	/**
	 * Save all numeric datatypes into an associative array
	 * 
	 * @param request
	 *            Request of AJAX call
	 */
	setNumericDatatypes : function(request) {
		var types = request.responseText.split(",");
		for ( var i = 0; i < types.length; i++) {
			// remove leading and trailing whitespaces
			var tmp = types[i].replace(/^\s+|\s+$/g, '');
			this.numTypes[tmp] = true;
		}
	},

	/**
	 * Add a new query. This happens everytime a user adds a property with a
	 * subquery
	 * 
	 * @param parent
	 *            ID of parent query
	 * @param name
	 *            name of the property which is referencing this query
	 */
	addQuery : function(parent, name) {
		this.queries.push(new Query(this.nextQueryId, parent, name));
		this.nextQueryId++;
	},

	/**
	 * Insert a query, works similar to add query but a given index is replaced
	 * with the new query data. This is needed when parsing the ask query string
	 * and creating the query objects in the QI.
	 * 
	 * @param parent
	 *            ID of parent query
	 * @param name
	 *            name of the property which is referencing this query
	 */
	insertQuery : function(id, parent, name) {
		if (this.queries[id]) {
			this.queries[id] = new Query(id, parent, name);
			return;
		}
		while (this.nextQueryId <= id)
			this.addQuery(parent, name);
	},

	/**
	 * Set a certain query as active query.
	 * 
	 * @param id
	 *            IS of the query to switch to
	 */
	setActiveQuery : function(id) {
		this.activeQuery = this.queries[id];
		this.activeQuery.updateTreeXML(); // update treeview
		this.activeQueryId = id;
		this.emptyDialogue(); // empty open dialogue
		this.updateBreadcrumbs(id); // update breadcrumb navigation of treeview
		// update everything
	},

	/**
	 * Shows a confirmation dialogue
	 */
	resetQuery : function() {
		$('shade').style.display = "";
		$('resetdialogue').style.display = "";
	},

	/**
	 * Executes a reset. Initializes Query Interface so everything is in its
	 * initial state
	 */
	doReset : function() {
		/* STARTLOG */
		if (window.smwhgLogger) {
			smwhgLogger.log("Reset Query", "QI", "query_reset");
		}
		/* ENDLOG */
		this.emptyDialogue();
		this.initialize();
		$('shade').style.display = "none";
		$('resetdialogue').style.display = "none";
		this.updatePreview();
	},

	/**
	 * Gets all display parameters and the full ask syntax to perform an ajax
	 * call which will create the preview
	 */
	previewQuery : function() {

		/* STARTLOG */
		if (window.smwhgLogger) {
			smwhgLogger.log("Preview Query", "QI", "query_preview");
		}
		/* ENDLOG */
		$('shade').toggle();
		if (this.pendingElement)
			this.pendingElement.hide();
		this.pendingElement = new OBPendingIndicator($('shade'));
		this.pendingElement.show();
                $('fullpreviewbox').toggle();
                $('fullpreview').innerHTML = '<img src="' + wgServer + wgScriptPath + '/extensions/SMWHalo/skins/OntologyBrowser/images/ajax-loader.gif" />';
		if (!this.queries[0].isEmpty()) { // only do this if the query is not
											// empty
			var ask = this.recurseQuery(0, "parser"); // Get full ask syntax
			this.queries[0].getDisplayStatements().each(function(s) {
				ask += "|?" + s
			});
			
			var reasoner = $('usetriplestore') ? $('usetriplestore').checked ? "sparql" : "ask" : "ask";
			var params = ask.replace(',', '%2C') + ",";
			params +='reasoner='+reasoner+'|';
			params += $('layout_sort').value == gLanguage.getMessage('QI_ARTICLE_TITLE')? "" : 'sort=' + $('layout_sort').value + '|';
			params += 'format=' + $('layout_format').value + '|';
			params += this.serializeSpecialQPParameters("|");
			params += '|merge=false';
			sajax_do_call('smwf_qi_QIAccess', [ "getQueryResult", params ],
					this.openPreview.bind(this));
		} else { // query is empty
			var request = Array();
			request.responseText = gLanguage.getMessage('QI_EMPTY_QUERY');
			this.openPreview(request);
		}
	},

	/**
	 * Gets all display parameters and the full ask syntax to perform an ajax
	 * call which will create the result preview
	 */
	previewResultPrinter : function() {

		/* STARTLOG */
		if (window.smwhgLogger) {
			smwhgLogger.log("Preview Result Printer", "QI",
					"query_result_preview");
		}
		/* ENDLOG */

		if (this.pendingElement)
			this.pendingElement.hide();
		this.pendingElement = new OBPendingIndicator($('previewcontent'));
		this.pendingElement.show();

		if (!this.queries[0].isEmpty()) { // only do this if the query is not
											// empty
			var ask = this.recurseQuery(0, "parser"); // Get full ask syntax
			
			// replace variables
			var xsdDatetime = /(\d{4,4}-\d{1,2}-\d{1,2})T(\d{2,2}:\d{2,2}:\d{2,2})Z/;
			var currentDate = new Date().toJSON();
			var matches = xsdDatetime.exec(currentDate);
			var nowDateTime = matches[1] + "T" + matches[2];
			var todayDateTime = matches[1] + "T00:00:00";
			ask = ask.replace(/\{\{NOW\}\}/gi, nowDateTime);
			ask = ask.replace(/\{\{TODAY\}\}/gi, todayDateTime);
			
			// replace comma in ask query
			ask = ask.replace(',', '%2C');
			
			this.queries[0].getDisplayStatements().each(function(s) {
				ask += "|?" + s
			});
			var triplestoreSwitch = $('usetriplestore');
			var reasoner = triplestoreSwitch != null && triplestoreSwitch.checked ? "sparql" : "ask";
			var params = ask + ",";  
			params +='reasoner='+reasoner+'|';
			params += "format="+$('layout_format').value + '|';
			if ($('layout_sort').value != gLanguage.getMessage('QI_ARTICLE_TITLE')) params += "sort="+$('layout_sort').value + '|';
			params += this.serializeSpecialQPParameters("|");
			params += '|merge=false';
			sajax_do_call('smwf_qi_QIAccess', [ "getQueryResult", params ],
					this.openResultPreview.bind(this));
		} else { // query is empty
			var request = Array();
			request.responseText = gLanguage.getMessage('QI_EMPTY_QUERY');
			this.openResultPreview(request);
		}
	},

	/**
	 * Displays the preview created by the server
	 * 
	 * @param request
	 *            Request of AJAX call
	 */
	openPreview : function(request) {
		this.pendingElement.hide();
		$('fullpreview').innerHTML = request.responseText;
		smw_tooltipInit();
	},

	/**
	 * Displays the preview created by the server
	 * 
	 * @param request
	 *            Request of AJAX call
	 */
	openResultPreview : function(request) {
		this.pendingElement.hide();
		$('previewcontent').innerHTML = request.responseText;
		smw_tooltipInit();

		// post processing of javascript for resultprinters:
		switch ($('layout_format').value) {
		case "timeline":
		case "eventline":
			this.parseWikilinks2Html();
			smw_timeline_init();
			break;
		case "exhibit":
			if (typeof createExhibit == 'function') createExhibit();
			break;
		}
	},
	/**
	 * Creates valid links for Wiki Links in Preview div for elements like in
	 * timeline div with id="previewcontent" innerHtml is changed directly
	 */
	parseWikilinks2Html : function() {
		
		if ($('layout_link') != null && $('layout_link').value == "none")
			return;
		var text = $('previewcontent').innerHTML;
		var newt = '';
		var seek = '[[';
		var p = text.indexOf(seek);
		while (p != -1) {
			if (seek == "[[") {
				newt += text.substring(0, p);
				text = text.substring(p + 2);
				seek = "]]";
			} else {
				var wikilink = text.substr(0, p);
				text = text.substr(p + 2);
				if (wikilink.indexOf(':') == 0)
					wikilink = wikilink.substring(1);
				var link = wikilink.split('|');
				var url = link[0];
				var title = link[0];
				if (link.length == 2)
					title = link[1];
				newt += '<a href="' + wgServer + wgScript + '/'
						+ GeneralTools.URLEncode(link[0].replace(' ', '_'))
						+ '">' + title + '</a>';
				seek = "[[";
			}
			p = text.indexOf(seek);
		}
		newt += text;
		$('previewcontent').innerHTML = newt;
	},

	/**
	 * Update breadcrumb navigation on top of the query tree. The BN will show
	 * the active query and all its parents as a mean to navigate
	 * 
	 * @param id
	 *            ID of the active query
	 */
	updateBreadcrumbs : function(id) {
		var nav = Array();
		while (this.queries[id].getParent() != null) { // null = root query
			nav.unshift(id);
			id = this.queries[id].getParent();
		}
		nav.unshift(id);
		var html = "";
		for ( var i = 0; i < nav.length; i++) { // create html for BN
			if (i > 0)
				html += "&gt;";
			html += '<span class="qibutton" onclick="qihelper.setActiveQuery(' + nav[i] + ')">';
			html += this.queries[nav[i]].getName() + '</span>';
		}
		html += "<hr/>";
		var breadcrumpDIV = $('treeviewbreadcrumbs');
 		if (breadcrumpDIV) breadcrumpDIV.innerHTML = html;
	},

	/**
	 * Updates the table column preview as well as the option box "Sort by".
	 * Both contain ONLY the properties of the root query that are shown in the
	 * result table
	 */
	updateColumnPreview : function() {
		var columns = new Array();
		columns.push(gLanguage.getMessage('QI_ARTICLE_TITLE')); // First column
																// has no name
																// in SMW,
																// therefore we
																// introduce our
																// own one
		var tmparr = this.queries[0].getAllProperties(); // only root query,
															// subquery results
															// can not be shown
															// in results
		for ( var i = 0; i < tmparr.length; i++) {
			if (tmparr[i].isShown()) { // show
				columns.push(tmparr[i].getName());
			}
		}
		var tcp_html = '<table id="tcp" summary="Preview of table columns"><tr>'; // html
																					// for
																					// table
																					// column
																					// preview
		$('layout_sort').innerHTML = "";
		for ( var i = 0; i < columns.length; i++) {
			tcp_html += "<td>" + columns[i] + "</td>";
			$('layout_sort').options[$('layout_sort').length] = new Option(
					columns[i], columns[i]); // add options to optionbox
		}
		tcp_html += "</tr></table>";
		$('tcpcontent').innerHTML = tcp_html;
	},

	getFullParserAsk : function() {
		var asktext = this.recurseQuery(0, "parser");
		var displays = this.queries[0].getDisplayStatements();
		var triplestoreSwitch = $('usetriplestore');
		var reasoner = triplestoreSwitch != null && triplestoreSwitch.checked ? "sparql" : "ask";
		var fullQuery = "{{#"+reasoner+": " + asktext;
		for ( var i = 0; i < displays.length; i++) {
			fullQuery += "| ?" + displays[i];
		}
		fullQuery += ' | format=' + $('layout_format').value;
		fullQuery += $('layout_sort').value == gLanguage
		.getMessage('QI_ARTICLE_TITLE') ? ""
				: (' | sort=' + $('layout_sort').value);
		
		fullQuery += "| " + this.serializeSpecialQPParameters("|");
		

		fullQuery += "|merge=false|}}";

		return fullQuery;
	},

	insertAsNotification : function() {
		var query = this.getFullParserAsk();
		document.cookie = "NOTIFICATION_QUERY=<snq>" + query + "</snq>";
		if (query != "") {
			var snPage = $('qi-insert-notification-btn').readAttribute(
					'specialpage');
			snPage = unescape(snPage);
			location.href = snPage;
			// window.open(snPage), "_blank");
		}

	},

	/**
	 * Recursive function that creates the ask syntax for the query with the ID
	 * provided and all its subqueries
	 * 
	 * @param id
	 *            ID of query to start
	 */
	recurseQuery : function(id, type) {
		if (!this.queries[id])
			return '';
		var sq = this.queries[id].getSubqueryIds();
		if (sq.length == 0) {
			if (type == "parser")
				return this.queries[id].getParserAsk();
			else
				return this.queries[id].getAskText(); // no subqueries, get
														// the asktext
		} else {
			if (type == "parser") {
				var tmptext = this.queries[id].getParserAsk();
				for ( var i = 0; i < sq.length; i++) {
					var regex = null;
					eval('regex = /Subquery:' + sq[i] + ':/g'); // search for
																// all Subquery
																// tags and
																// extract the
																// ID
					tmptext = tmptext.replace(regex, '<q>' + this.recurseQuery(
							sq[i], "ask") + '</q>'); // recursion
				}
				return tmptext;
			} else {
				var tmptext = this.queries[id].getAskText();
				for ( var i = 0; i < sq.length; i++) {
					var regex = null;
					eval('regex = /Subquery:' + sq[i] + ':/g'); // search for
																// all Subquery
																// tags and
																// extract the
																// ID
					tmptext = tmptext.replace(regex, '<q>' + this.recurseQuery(
							sq[i], "parser") + '</q>'); // recursion
				}
				return tmptext;
			}
		}
	},

	/**
	 * Creates a new dialogue for adding categories to the query
	 * 
	 * @param reset
	 *            indicates if this is a new dialogue or if it is loaded from
	 *            the tree
	 */
	newCategoryDialogue : function(reset) {
		$('qidelete').style.display = "none"; // New dialogue, no delete
												// button
		autoCompleter.deregisterAllInputs();
		if (reset)
			this.loadedFromId = null;
		this.activeDialogue = "category";
		for ( var i = 0, n = $('dialoguecontent').rows.length; i < n; i++)
			// empty dialogue table
			$('dialoguecontent').deleteRow(0);
		var newrow = $('dialoguecontent').insertRow(-1); // create the
															// dialogue
		var cell = newrow.insertCell(0);
		cell.innerHTML = gLanguage.getMessage('CATEGORY');
		cell = newrow.insertCell(1);
		cell.innerHTML = '<input type="text" id="input0" class="wickEnabled general-forms" constraints="namespace: 14" autocomplete="OFF"/>'; // input
																																// field
																																// with
																																// autocompletion
																																// enabled
		cell = newrow.insertCell(2);
		cell.innerHTML = '<img src="' + this.imgpath + 'add.png" alt="addCategoryInput" onclick="qihelper.addDialogueInput()"/>'; // button
																																	// to
																																	// add
																																	// another
																																	// input
																																	// for
																																	// or-ed
																																	// values
		this.activeInputs = 1;
		$('dialoguebuttons').style.display = "";
		autoCompleter.registerAllInputs();
		if (reset)
			$('input0').focus();
	},

	/**
	 * Creates a new dialogue for adding instances to the query
	 * 
	 * @param reset
	 *            indicates if this is a new dialogue or if it is loaded from
	 *            the tree
	 */
	newInstanceDialogue : function(reset) {
		$('qidelete').style.display = "none";
		autoCompleter.deregisterAllInputs();
		if (reset)
			this.loadedFromId = null;
		this.activeDialogue = "instance";
		for ( var i = 0, n = $('dialoguecontent').rows.length; i < n; i++)
			$('dialoguecontent').deleteRow(0);
		var newrow = $('dialoguecontent').insertRow(-1);
		var cell = newrow.insertCell(0);
		cell.innerHTML = gLanguage.getMessage('QI_INSTANCE');
		cell = newrow.insertCell(1);
		cell.innerHTML = '<input type="text" id="input0" class="wickEnabled general-forms" constraints="namespace: 0" autocomplete="OFF"/>';
		cell = newrow.insertCell(2);
		cell.innerHTML = '<img src="' + this.imgpath + 'add.png" alt="addInstanceInput" onclick="qihelper.addDialogueInput()"/>';
		this.activeInputs = 1;
		$('dialoguebuttons').style.display = "";
		autoCompleter.registerAllInputs();
		if (reset)
			$('input0').focus();
	},

	/**
	 * Creates a new dialogue for adding properties to the query
	 * 
	 * @param reset
	 *            indicates if this is a new dialogue or if it is loaded from
	 *            the tree
	 */
	newPropertyDialogue : function(reset) {
		$('qidelete').style.display = "none";
		autoCompleter.deregisterAllInputs();
		if (reset)
			this.loadedFromId = null;
		this.activeDialogue = "property";
		this.propname = "";
		for ( var i = 0, n = $('dialoguecontent').rows.length; i < n; i++)
			$('dialoguecontent').deleteRow(0);

		var constraintstring = "schema-property-domain: ";
		// fetch category constraints:
		var cats = this.activeQuery.categories; // get the category group
        var constraintsCategories = "";
		if (cats != null) {
			for ( var i = 0, n = cats.length; i < n; i++) {
				catconstraint = cats[i];
				if (i > 0) {
					constraintstring += ",";
				}
				for ( var j = 0, m = catconstraint.length; j < m; j++) {
					orconstraint = catconstraint[j];
					if (j > 0) {
						constraintstring += ",";
					}
					constraintsCategories += gLanguage.getMessage('CATEGORY_NS',
							'cont')
							+ orconstraint;
				}
			}
		}
        constraintstring = "schema-property-domain: "+constraintsCategories+ "|annotation-property: "+constraintsCategories + "|namespace: 102";
		var newrow = $('dialoguecontent').insertRow(-1); // First row: input
															// for property name
		var cell = newrow.insertCell(0);
		cell.innerHTML = gLanguage.getMessage('QI_PROPERTYNAME');
		cell = newrow.insertCell(1);
		cell.style.textAlign = "left";
		cell.setAttribute("colSpan", 2);
		cell.innerHTML = '<input type="text" id="input0" class="wickEnabled general-forms" constraints="' + constraintstring + '" autocomplete="OFF" onblur="qihelper.getPropertyInformation()"/>';
              
		newrow = $('dialoguecontent').insertRow(-1); // second row: checkbox
														// for display option
		cell = newrow.insertCell(0);
		cell.innerHTML = gLanguage.getMessage('QI_SHOW_PROPERTY');
		cell = newrow.insertCell(1);
		cell.style.textAlign = "left";
		cell.setAttribute("colSpan", 2);
		if (this.activeQueryId == 0)
			cell.innerHTML = '<input type="checkbox" id="input1">';
		else
			cell.innerHTML = '<input type="checkbox" disabled="disabled" id="input1">';

		newrow = $('dialoguecontent').insertRow(-1); // second row: checkbox
														// for display option
		cell = newrow.insertCell(0);
		cell.innerHTML = gLanguage.getMessage('QI_PROPERTY_MUST_BE_SET');
		cell = newrow.insertCell(1);
		cell.style.textAlign = "left";
		cell.setAttribute("colSpan", 2);
		cell.innerHTML = '<input type="checkbox" id="input2">';

		newrow = $('dialoguecontent').insertRow(-1); // third row: input for
														// property value and
														// subquery
		cell = newrow.insertCell(0);
		cell.id = "mainlabel";
		cell.innerHTML = gLanguage.getMessage('QI_PAGE'); // we assume Page as
															// type since this
															// is standard
		cell = newrow.insertCell(1);
		cell.id = "restricionSelector";
		cell.innerHTML = this.createRestrictionSelector("=", false, false);
		cell = newrow.insertCell(2);
		cell.innerHTML = '<input class="wickEnabled general-forms" constraints="namespace: 0" autocomplete="OFF" type="text" id="input3"/>';
		cell = newrow.insertCell(3);
		cell.innerHTML = '<img src="' + this.imgpath + 'add.png" alt="addPropertyInput" onclick="qihelper.addDialogueInput()"/>';
		cell = newrow.insertCell(4);
		cell.className = "subquerycell";
		cell.innerHTML = '&nbsp;' + gLanguage.getMessage('QI_USE_SUBQUERY') + '<input type="checkbox" id="usesub" onclick="qihelper.useSub(this.checked)"/>';
		this.activeInputs = 4;
		$('dialoguebuttons').style.display = "";
		this.proparity = 2;
		autoCompleter.registerAllInputs();
		if (reset)
			$('input0').focus();
	},

	/**
	 * Empties the current dialogue and resets all relevant variables. Called on
	 * "cancel" button
	 */
	emptyDialogue : function() {
		this.activeDialogue = null;
		this.loadedFromId = null;
		this.propIsEnum = false;
		this.enumValues = null;
		this.propname = null;
		this.proparity = null;
		for ( var i = 0, n = $('dialoguecontent').rows.length; i < n; i++)
			$('dialoguecontent').deleteRow(0);
		$('dialoguebuttons').style.display = "none";
		$('qistatus').innerHTML = "";
		$('qidelete').style.display = "none";
		this.activeInputs = 0;
	},

	/**
	 * Add another input to the current dialogue
	 */
	addDialogueInput : function() {
		autoCompleter.deregisterAllInputs();
		var delimg = wgScriptPath + '/extensions/SemanticMediaWiki/skins/QueryInterface/images/delete.png';
		var newrow = $('dialoguecontent').insertRow(-1);
		newrow.id = "row" + newrow.rowIndex; // id needed for delete button
												// later on
		var cell = newrow.insertCell(0);
		cell.innerHTML = gLanguage.getMessage('QI_OR');
		cell = newrow.insertCell(1);
		var param = $('mainlabel') ? $('mainlabel').innerHTML : "";

		if (this.activeDialogue == "category") // add input fields according to
												// dialogue
			cell.innerHTML = '<input class="wickEnabled general-forms" constraints="namespace: 14" autocomplete="OFF" type="text" id="input' + this.activeInputs + '"/>';
		else if (this.activeDialogue == "instance")
			cell.innerHTML = '<input class="wickEnabled general-forms" constraints="namespace: 0" autocomplete="OFF" type="text" id="input' + this.activeInputs + '"/>';
		else if (param == gLanguage.getMessage('QI_PAGE')) { // property
																// dialogue &
																// type = page
			cell.innerHTML = this.createRestrictionSelector("=", true, false);
			cell = newrow.insertCell(2);
			cell.innerHTML = '<input class="wickEnabled general-forms" constraints="namespace: 0" autocomplete="OFF" type="text" id="input' + this.activeInputs + '"/>';
		} else { // property, no page type
			if (this.numTypes[param.toLowerCase()]) // numeric type? operators
													// possible
				cell.innerHTML = this.createRestrictionSelector("=", false, true);
			else
				cell.innerHTML = this.createRestrictionSelector("=", false, false);

			cell = newrow.insertCell(2);
			if (this.propIsEnum) { // if enumeration, a select box is used
									// instead of a text input field
				var tmphtml = '<select id="input' + this.activeInputs + '">';
				for ( var i = 0; i < this.enumValues.length; i++) {
					tmphtml += '<option value="' + this.enumValues[i]
							+ '" style="width:100%">' + this.enumValues[i]
							+ '</option>';
				}
				tmphtml += '</select>';
				cell.innerHTML = tmphtml;
			} else { // no enumeration, no page type, simple input field
				cell.innerHTML = '<input type="text" id="input' + this.activeInputs + '"/>';
			}
		}
		cell = newrow.insertCell(-1);
		cell.innerHTML = '<img src="'
				+ this.imgpath
				+ 'delete.png" alt="deleteInput" onclick="qihelper.removeInput('
				+ newrow.rowIndex + ')"/>';
		$('input' + this.activeInputs).focus(); // focus created input
		this.activeInputs++;
		autoCompleter.registerAllInputs();
	},

	/**
	 * Removes an input if the remove icon is clicked
	 * 
	 * @param index
	 *            index of the table row to delete
	 */
	removeInput : function(index) {
		$('dialoguecontent').removeChild($('row' + index));
		this.activeInputs--;
	},

	/**
	 * Is called everytime a user entered a property name and leaves the input
	 * field. Executes an ajax call which will get information about the
	 * property (if available)
	 */
	getPropertyInformation : function() {
		var propname = $('input0').value;
		if (propname != "" && propname != this.propname) { // only if not empty
															// and name changed
			this.propname = propname;
			if (this.pendingElement)
				this.pendingElement.hide();
			this.pendingElement = new OBPendingIndicator($('input3'));
			this.pendingElement.show();
			sajax_do_call('smwf_qi_QIAccess', [ "getPropertyInformation",
					escapeQueryHTML(propname) ], this.adaptDialogueToProperty
					.bind(this));
		}
	},

	/**
	 * Receives an XML string containing schema information of a property.
	 * Depending on this information, the dialogue has to be adapted. You need
	 * to consider: arity, enumeration and type of property.
	 * 
	 * @param request
	 *            Request of the ajax call
	 */
	adaptDialogueToProperty : function(request) {
		this.propIsEnum = false;
		autoCompleter.deregisterAllInputs();
		if (this.activeDialogue != null) { // check if user cancelled the
											// dialogue whilst ajax call
			var oldval = $('input3').value;
			var oldcheck = $('usesub') ? $('usesub').checked : false;
			var oldsubid = $('usesub') ? $('usesub').value : this.nextQueryId;
			for ( var i = 4, n = $('dialoguecontent').rows.length; i < n; i++) {
				$('dialoguecontent').deleteRow(4); // delete all rows for value
													// inputs
			}
			// create standard values in case request fails
			var arity = 2;
			this.proparity = 2;
			var parameterNames = [ gLanguage.getMessage('QI_PAGE') ];
			var parameterTypes = [];
			var possibleValues = new Array();

			if (request.status == 200) {
				var schemaData = GeneralXMLTools
						.createDocumentFromString(request.responseText);

				// read arity
				arity = parseInt(schemaData.documentElement
						.getAttribute("arity"));
				this.proparity = arity;
				parameterNames = [];
				// parse all parameter names
				for ( var i = 0, n = schemaData.documentElement.childNodes.length; i < n; i++) {
					parameterNames
							.push(schemaData.documentElement.childNodes[i]
									.getAttribute("name"));
					parameterTypes
                            .push(schemaData.documentElement.childNodes[i]
                                    .getAttribute("type"));				
					for ( var j = 0, m = schemaData.documentElement.childNodes[i].childNodes.length; j < m; j++) {
						possibleValues
								.push(schemaData.documentElement.childNodes[i].childNodes[j]
										.getAttribute("value")); // contains
																	// allowed
																	// values
																	// for
																	// enumerations
																	// if
																	// applicable
					}
				}
			}
			if (arity == 2) {
				// Speical treatment: binary properties support conjunction,
				// therefore we need an "add" button
				$('mainlabel').innerHTML = parameterNames[0];
				var propertyName = gLanguage.getMessage('PROPERTY_NS')+$('input0').value.replace(/\s/g, '_');
				var ac_constraint = "";
				if (parameterTypes[0] == '_wpg') {
					ac_constraint = 'constraints="annotation-value: '+propertyName+'|namespace: 0"';
				} else if (parameterTypes[0] == '_dat') {
					ac_constraint = 'constraints="fixvalues: {{NOW}},{{TODAY}}|annotation-value: '+propertyName+'"';
    			} else {
					ac_constraint = 'constraints="annotation-value: '+propertyName+'"';
				}
				$('dialoguecontent').rows[3].cells[2].innerHTML = '<input class="wickEnabled general-forms" '+ac_constraint+' autocomplete="OFF" type="text" id="input3"/>';
				
				// set restriction selector
				if (this.numTypes[parameterNames[0].toLowerCase()]) {
					$('restricionSelector').innerHTML = this
							.createRestrictionSelector("=", false, true);
				} else
					$('restricionSelector').innerHTML = this
							.createRestrictionSelector("=", false, false);
								
				// add property input button 
				$('dialoguecontent').rows[3].cells[3].innerHTML = '<img src="' + this.imgpath + 'add.png" alt="addPropertyInput" onclick="qihelper.addDialogueInput()"/>';
                
                // if binary property, make an 'insert subquery' checkbox
				if (parameterTypes[0] == '_wpg') { 
					$('dialoguecontent').rows[3].cells[4].innerHTML = '&nbsp;'
							+ gLanguage.getMessage('QI_USE_SUBQUERY')
							+ '<input type="checkbox" id="usesub" value="'
							+ oldsubid
							+ '" onclick="qihelper.useSub(this.checked)"/>';
					$('dialoguecontent').rows[3].cells[4].className = "subquerycell";
					$('usesub').checked = oldcheck;
					this.activeInputs = 4;
				} else { // no checkbox for other types
					$('dialoguecontent').rows[3].cells[4].innerHTML = ""
					$('dialoguecontent').rows[3].cells[4].className = "";
					this.activeInputs = 4;
				}
				
				// special input field for enums
				if (possibleValues.length > 0) { // enumeration
					this.propIsEnum = true;
					this.enumValues = new Array();
					
					var option = '<select id="input3">'; // create html for
															// option box
					option += '<option value="" style="width:100%">*</option>';
					for ( var i = 0; i < possibleValues.length; i++) {
						this.enumValues.push(possibleValues[i]); // save
																	// enumeration
																	// values
																	// for later
																	// use
						option += '<option value="' + possibleValues[i]
								+ '" style="width:100%">' + possibleValues[i]
								+ '</option>';
					}
					option += "</select>";
					$('dialoguecontent').rows[3].cells[2].innerHTML = option;
					
				}
			} else {
				// properties with arity > 2: attributes or n-ary. no conjunction, no subqueries
				this.activeInputs = 4;
				$('dialoguecontent').rows[3].cells[3].innerHTML = "";
				$('dialoguecontent').rows[3].cells[4].innerHTML = "";
				$('dialoguecontent').rows[3].cells[4].className = "";
				$('mainlabel').innerHTML = parameterNames[0];
				if (this.numTypes[parameterNames[0].toLowerCase()]) {
					$('restricionSelector').innerHTML = this
							.createRestrictionSelector("=", false, true);
					
				} else
					$('restricionSelector').innerHTML = this
							.createRestrictionSelector("=", false, false);

				for ( var i = 1; i < parameterNames.length; i++) {
					var newrow = $('dialoguecontent').insertRow(-1);
					var cell = newrow.insertCell(0);
					cell.innerHTML = parameterNames[i]; // Label of cell is
														// parameter name (ex.:
														// Integer, Date,...)
					cell = newrow.insertCell(1);
					if (this.numTypes[parameterNames[i].toLowerCase()])
						cell.innerHTML = this.createRestrictionSelector("=",
								false, true);
					else
						cell.innerHTML = this.createRestrictionSelector("=",
								false, false);

					cell = newrow.insertCell(2);
					var propertyName = gLanguage.getMessage('PROPERTY_NS')+$('input0').value.replace(/\s/g, '_');
					if (parameterTypes[i] == '_wpg') {
                    	cell.innerHTML = '<input class="wickEnabled general-forms" constraints="annotation-value: '+propertyName+'|namespace: 0" autocomplete="OFF" type="text" id="input' + this.activeInputs + '"/>';
					} else if (parameterTypes[i] == '_dat') {
						cell.innerHTML = '<input type="text" id="input' + this.activeInputs + '" constraints="fixvalues: {{NOW}},{{TODAY}}|annotation-value: '+propertyName+'"/>';
					} else {
						cell.innerHTML = '<input type="text" id="input' + this.activeInputs + '" constraints="annotation-value: '+propertyName+'"/>';
					}
					this.activeInputs++;
				}
			}
		}
		autoCompleter.registerAllInputs();
		this.pendingElement.hide();
	},

	/**
	 * Loads values of an existing category group. This happens if a users
	 * clicks on a category folder in the query tree.
	 * 
	 * @param id
	 *            id of the category group (saved with the query tree)
	 */
	loadCategoryDialogue : function(id) {
		this.newCategoryDialogue(false);
		this.loadedFromId = id;
		var cats = this.activeQuery.getCategoryGroup(id); // get the category
															// group
		$('input0').value = unescapeQueryHTML(cats[0]);
		for ( var i = 1; i < cats.length; i++) {
			this.addDialogueInput();
			$('input' + i).value = unescapeQueryHTML(cats[i]);
		}
		$('qidelete').style.display = ""; // show delete button
	},

	/**
	 * Loads values of an existing instance group. This happens if a users
	 * clicks on an instance folder in the query tree.
	 * 
	 * @param id
	 *            id of the instace group (saved with the query tree)
	 */
	loadInstanceDialogue : function(id) {
		this.newInstanceDialogue(false);
		this.loadedFromId = id;
		var ins = this.activeQuery.getInstanceGroup(id);
		$('input0').value = unescapeQueryHTML(ins[0]);
		for ( var i = 1; i < ins.length; i++) {
			this.addDialogueInput();
			$('input' + i).value = unescapeQueryHTML(ins[i]);
		}
		$('qidelete').style.display = "";
	},

	/**
	 * Loads values of an existing property group. This happens if a users
	 * clicks on a property folder in the query tree. WARNING: This is a MESS!
	 * Don't change anything unless you really know what you are doing.
	 * 
	 * @param id
	 *            id of the property group (saved with the query tree)
	 * @todo find a better way to do this
	 */
	loadPropertyDialogue : function(id) {
		this.newPropertyDialogue(false);
		this.loadedFromId = id;
		var prop = this.activeQuery.getPropertyGroup(id);
		var vals = prop.getValues();
		this.proparity = prop.getArity();

		$('input0').value = unescapeQueryHTML(prop.getName()); // fill input
																// filed with
																// name
		$('input1').checked = prop.isShown(); // check box if appropriate
		$('input2').checked = prop.mustBeSet();
		$('mainlabel').innerHTML = (vals[0][0] == "subquery" ? gLanguage
				.getMessage('QI_PAGE') : vals[0][0]); // subquery means type
														// is page

		if ($('mainlabel').innerHTML != gLanguage.getMessage('QI_PAGE')) { // remove
																			// subquery
																			// box
			$('dialoguecontent').rows[3].cells[4].innerHTML = ""; // remove
																	// subquery
																	// checkbox
																	// since no
																	// subqueries
																	// are
																	// possible
			$('dialoguecontent').rows[3].cells[4].className = ""; // remove
																	// the
																	// seperator
		}

		var disabled = true;
		if (this.numTypes[vals[0][0].toLowerCase()]) { // is it a numeric type?
			disabled = false;

			$('dialoguecontent').rows[3].cells[1].innerHTML = this
					.createRestrictionSelector(vals[0][1], disabled, true);
			autoCompleter.deregisterAllInputs();
			$('dialoguecontent').rows[3].cells[2].firstChild.className = ""; // deactivate
																				// autocompletion
			autoCompleter.registerAllInputs();
		}
		if (vals[0][0] == "subquery") { // grey out input field and check
										// checkbox
			this.useSub(true);
			$('usesub').checked = true;
			$('usesub').value = vals[0][2];
		} else {
			if (!prop.isEnumeration())
				$('input3').value = unescapeQueryHTML(vals[0][2]); // enter the
																	// value
																	// into the
																	// input box
			else { // create option box for enumeration
				this.propIsEnum = true;
				var tmphtml = '<select id="input3">';
				this.enumValues = prop.getEnumValues();
				// create the options; check which one was selected and add the
				// 'selected' param then
				tmphtml += '<option style="width:100%" value="">*</option>'; // empty
																				// value
																				// first
				for ( var i = 0; i < this.enumValues.length; i++) {
					tmphtml += '<option style="width:100%" value="'
							+ unescapeQueryHTML(this.enumValues[i])
							+ '" '
							+ (this.enumValues[i] == vals[0][2] ? 'selected="selected"'
									: '') + '>' + this.enumValues[i]
							+ '</option>';
				}
				tmphtml += '</select>';
				$('dialoguecontent').rows[3].cells[2].innerHTML = tmphtml;
			}
		}
		if (prop.getArity() == 2) { // simply add further inputs if there are
									// any
			if (!prop.isEnumeration()) {
				for ( var i = 1; i < vals.length; i++) {
					this.addDialogueInput();
					$('input' + (i + 3)).value = unescapeQueryHTML(vals[i][2]);
					$('dialoguecontent').rows[i + 3].cells[1].innerHTML = this
							.createRestrictionSelector(vals[i][1], disabled, true);
				}
			} else { // enumeration
				this.propIsEnum = true;
				this.enumValues = prop.getEnumValues();
				for ( var i = 1; i < vals.length; i++) {
					this.addDialogueInput();
					var tmphtml = '<select id="input' + (i + 3) + '">';
					// create the options; check which one was selected and add
					// the 'selected' param then
					for ( var j = 0; j < this.enumValues.length; j++) {
						tmphtml += '<option style="width:100%" value="'
								+ unescapeQueryHTML(this.enumValues[j])
								+ '" '
								+ (this.enumValues[j] == vals[i][2] ? 'selected="selected"'
										: '') + '>'
								+ unescapeQueryHTML(this.enumValues[j])
								+ '</option>';
					}
					tmphtml += '</select>';
					$('dialoguecontent').rows[i + 3].cells[2].innerHTML = tmphtml;
					$('dialoguecontent').rows[i + 3].cells[1].innerHTML = this
							.createRestrictionSelector(vals[i][1], disabled, false);
				}
			}
		} else { // property with arity > 2
			autoCompleter.deregisterAllInputs();
			$('dialoguecontent').rows[3].cells[3].innerHTML = ""; // remove
																	// plus icon
																	// since no
																	// conjunction
																	// is
																	// possible
			$('dialoguecontent').rows[3].cells[4].innerHTML = ""; // remove
																	// subquery
																	// checkbox
																	// since no
																	// subqueries
																	// are
																	// possible
			$('dialoguecontent').rows[3].cells[4].className = ""; // remove
																	// the
																	// seperator
			for ( var i = 1; i < vals.length; i++) {
				var row = $('dialoguecontent').insertRow(-1);
				var cell = row.insertCell(0);
				cell.innerHTML = vals[i][0]; // parameter name

				cell = row.insertCell(1); // restriction selector
				if (this.numTypes[vals[i][0].toLowerCase()])
					cell.innerHTML = this.createRestrictionSelector(vals[i][1],
							false, true);
				else
					cell.innerHTML = this.createRestrictionSelector(vals[i][1],
							false, false);

				cell = row.insertCell(2); // input field
				if (vals[i][0] == gLanguage.getMessage('QI_PAGE')) // autocompletion
																	// needed?
					cell.innerHTML = '<input type="text" class="wickEnabled general-forms" constraints="namespace: 0" autocomplete="OFF" id="input'
							+ (i + 2)
							+ '" value="'
							+ unescapeQueryHTML(vals[i][2]) + '"/>';
				else
					cell.innerHTML = '<input type="text" id="input' + (i + 2)
							+ '" value="' + unescapeQueryHTML(vals[i][2])
							+ '"/>';
			}
			autoCompleter.registerAllInputs();
		}
		$('qidelete').style.display = "";
		
		if (!prop.isEnumeration()) this.restoreAutocompletionConstraints();
	},
	
	 restoreAutocompletionConstraints : function() {
        var propname = $('input0').value;
        if (propname != "" && propname != this.propname) { // only if not empty
                                                            // and name changed
            this.propname = propname;
            if (this.pendingElement)
                this.pendingElement.hide();
            this.pendingElement = new OBPendingIndicator($('input3'));
            this.pendingElement.show();
            sajax_do_call('smwf_qi_QIAccess', [ "getPropertyInformation",
                    escapeQueryHTML(propname) ], this.restoreAutocompletionConstraintsCallback
                    .bind(this));
        }
    },
    
    restoreAutocompletionConstraintsCallback: function(request) {
    	autoCompleter.deregisterAllInputs();
    	   if (request.status == 200) {
                var schemaData = GeneralXMLTools
                        .createDocumentFromString(request.responseText);

                // read arity
                var arity = parseInt(schemaData.documentElement
                        .getAttribute("arity"));
                this.proparity = arity;
                var parameterNames = [];
                var parameterTypes = [];
                // parse all parameter names
                for ( var i = 0, n = schemaData.documentElement.childNodes.length; i < n; i++) {
                    parameterNames
                            .push(schemaData.documentElement.childNodes[i]
                                    .getAttribute("name"));
                    parameterTypes
                            .push(schemaData.documentElement.childNodes[i]
                                    .getAttribute("type"));             
                    for ( var j = 0, m = schemaData.documentElement.childNodes[i].childNodes.length; j < m; j++) {
                        possibleValues
                                .push(schemaData.documentElement.childNodes[i].childNodes[j]
                                        .getAttribute("value")); 
                    }
                }
                
	                // Speical treatment: binary properties support conjunction,
	                // therefore we need an "add" button
	                
	                var propertyName = gLanguage.getMessage('PROPERTY_NS')+$('input0').value.replace(/\s/g, '_');
	                var ac_constraint = "";
	                if (parameterTypes[0] == '_wpg') {
	                    ac_constraint = 'annotation-value: '+propertyName+'|namespace: 0';
	                } else if (parameterTypes[0] == '_dat') {
	                    ac_constraint = 'fixvalues: {{NOW}},{{TODAY}}|annotation-value: '+propertyName;
	                } else {
	                    ac_constraint = 'annotation-value: '+propertyName;
	                }
	                var i = 3;
	                while($('input'+i) != null) {
	                	$('input'+i).addClassName("wickEnabled");
	                	$('input'+i).setAttribute("constraints", ac_constraint);
	                	i++;
	                }
                
            }
    
    	autoCompleter.registerAllInputs();
    	this.pendingElement.hide();
    },

	/**
	 * Deletes the currently shown dialogue from the query
	 */
	deleteActivePart : function() {
		switch (this.activeDialogue) {
		case "category":
			/* STARTLOG */
			if (window.smwhgLogger) {
				var logstr = "Remove category "
						+ this.activeQuery.getCategoryGroup(this.loadedFromId)
								.join(",") + " from query";
				smwhgLogger.log(logstr, "QI", "query_category_removed");
			}
			/* ENDLOG */
			this.activeQuery.removeCategoryGroup(this.loadedFromId);
			break;
		case "instance":
			/* STARTLOG */
			if (window.smwhgLogger) {
				var logstr = "Remove instance "
						+ this.activeQuery.getInstanceGroup(this.loadedFromId)
								.join(",") + " from query";
				smwhgLogger.log(logstr, "QI", "query_instance_removed");
			}
			/* ENDLOG */
			this.activeQuery.removeInstanceGroup(this.loadedFromId);
			break;
		case "property":
			var pgroup = this.activeQuery.getPropertyGroup(this.loadedFromId);
			/* STARTLOG */
			if (window.smwhgLogger) {
				var logstr = "Remove property " + pgroup.getName()
						+ " from query";
				smwhgLogger.log(logstr, "QI", "query_property_removed");
			}
			/* ENDLOG */
			if (pgroup.getValues()[0][0] == "subquery") {
				/* STARTLOG */
				if (window.smwhgLogger) {
					var logstr = "Remove subquery (property: "
							+ pgroup.getName() + ") from query";
					smwhgLogger.log(logstr, "QI", "query_subquery_removed");
				}
				/* ENDLOG */
				// recursively delete all subqueries of this one. It's id is
				// values[0][2]
				this.deleteSubqueries(pgroup.getValues()[0][2]);
			}
			this.activeQuery.removePropertyGroup(this.loadedFromId);
			break;
		}
		this.emptyDialogue();
		this.activeQuery.updateTreeXML();
		this.updateColumnPreview();

		// update result preview
		this.updatePreview();
	},

	/**
	 * Recursively deletes all subqueries of a given query
	 * 
	 * @param id
	 *            ID of the query to start with
	 */
	deleteSubqueries : function(id) {
		if (!this.queries[id])
			return;
		if (this.queries[id].hasSubqueries()) {
			for ( var i = 0; i < this.queries[id].getSubqueryIds().length; i++) {
				this.deleteSubqueries(this.queries[id].getSubqueryIds()[i]);
			}
		}
		this.queries[id] = null;

		// update result preview
		this.updatePreview();
	},

	/**
	 * Creates an HTML option with the different possible restrictions
	 * 
	 * @param disabled
	 *            enabled only for numeric datatypes
	 */
	createRestrictionSelector : function(option, disabled, numericType) {
		var html = disabled ? '<select disabled="disabled">' : '<select>';
		var optionsFunc = function(op) {
			
			var escapeXMLEntities =  function(xml) {
		        var result = xml.replace(/</g, '&lt;');
		        result = result.replace(/>/g, '&gt;');
		        return result;
		    }
			var selected = (op == option) ? 'selected="selected"' : ''; 
            var esc_op = escapeXMLEntities(op);
            html += '<option value="'+esc_op+'" '+selected+'>'+esc_op+'</option>';
		}
		if (numericType) {
			["=", ">=", "<=", "!", "~"].each(optionsFunc);
		} else {
			["=", "!", "~"].each(optionsFunc);
		}
		
		return html + "</select>";
	},
	
	

	/**
	 * Activate or deactivate input if subquery checkbox is checked
	 * 
	 * @param checked
	 *            did user check or uncheck?
	 */
	useSub : function(checked) {
		if (checked) {
			$('input3').value = "";
			$('input3').disabled = true;
			$('input3').style.background = "#DDDDDD";
		} else {
			$('input3').disabled = false;
			$('input3').style.background = "#FFFFFF";
		}
	},

	/**
	 * Adds a new Category/Instance/Property Group to the query
	 */
	add : function() {
		if (this.activeDialogue == "category") {
			this.addCategoryGroup();
		} else if (this.activeDialogue == "instance") {
			this.addInstanceGroup();
		} else {
			this.addPropertyGroup();
		}
		this.activeQuery.updateTreeXML();
		this.updatePreview();
		this.loadedFromID = null;
	},

	/**
	 * Reads the input fields of a category dialogue and adds them to the query
	 */
	addCategoryGroup : function() {
		var tmpcat = Array();
		var allinputs = true; // checks if all inputs are set for error
								// message
		for ( var i = 0; i < this.activeInputs; i++) {
			var tmpid = "input" + i;
			tmpcat.push(escapeQueryHTML($(tmpid).value));
			if ($(tmpid).value == "")
				allinputs = false;
		}
		if (!allinputs)
			$('qistatus').innerHTML = gLanguage.getMessage('QI_ENTER_CATEGORY'); // show
																					// error
		else {
			/* STARTLOG */
			if (window.smwhgLogger) {
				var logstr = "Add category " + tmpcat.join(",") + " to query";
				smwhgLogger.log(logstr, "QI", "query_category_added");
			}
			/* ENDLOG */
			this.activeQuery.addCategoryGroup(tmpcat, this.loadedFromId); // add
																			// to
																			// query
			this.emptyDialogue();
		}
	},

	/**
	 * Reads the input fields of an instance dialogue and adds them to the query
	 */
	addInstanceGroup : function() {
		var tmpins = Array();
		var allinputs = true;
		for ( var i = 0; i < this.activeInputs; i++) {
			var tmpid = "input" + i;
			tmpins.push(escapeQueryHTML($(tmpid).value));
			if ($(tmpid).value == "")
				allinputs = false;
		}
		if (!allinputs)
			$('qistatus').innerHTML = gLanguage.getMessage('QI_ENTER_INSTANCE');
		else {
			/* STARTLOG */
			if (window.smwhgLogger) {
				var logstr = "Add instance " + tmpins.join(",") + " to query";
				smwhgLogger.log(logstr, "QI", "query_instance_added");
			}
			/* ENDLOG */
			this.activeQuery.addInstanceGroup(tmpins, this.loadedFromId);
			this.emptyDialogue();
		}
	},

	/**
	 * Reads the input fields of a property dialogue and adds them to the query
	 */
	addPropertyGroup : function() {
		var pname = $('input0').value;
		var subqueryIds = Array();
		if (pname == "") { // no name entered?
			$('qistatus').innerHTML = gLanguage
					.getMessage('QI_ENTER_PROPERTY_NAME');
		} else {
			var pshow = $('input1').checked; // show in results?
			var pmust = $('input2').checked;
			var arity = this.proparity;
			var pgroup = new PropertyGroup(escapeQueryHTML(pname), arity,
					pshow, pmust, this.propIsEnum, this.enumValues); // create
																		// propertyGroup
			var allValueRows = $('dialoguecontent').rows.length;
			var cHtmlRow = 3;
			for ( var i = 3; i < allValueRows; i++) {
				// for a property several values were selected but if in the
				// list a value
				// in the middle has been deleted, the inputX doesn't exist
				// anymore, so skip this one and
				// continue with the next one. For example if the 3rd value was
				// deleted (input5) then:
				// variable i contains the logical value i.e. input3 = 3, input4
				// = 4, input6 = 6
				// variable cHtmlRow is the current html row, i.e. input3 = 3,
				// input4 = 4, input6 = 5
				try {
					var paramvalue = $('input' + i).value;
				} catch (e) {
					allValueRows++;
					continue;
				}
				paramvalue = paramvalue == "" ? "*" : paramvalue; // no value
																	// is
																	// replaced
																	// by "*"
																	// which
																	// means all
																	// values
				var paramname = $('dialoguecontent').rows[cHtmlRow].cells[0].innerHTML;
				if (paramname == gLanguage.getMessage('QI_PAGE') && arity == 2
						&& $('usesub').checked) { // Subquery?
					paramname = "subquery";
					var cSubQueryId = parseInt($('usesub').value);
					if (cSubQueryId < this.nextQueryId) // Subquery does exists
														// already
						paramvalue = cSubQueryId;
					else { // Sub Query does not yet exist
						paramvalue = this.nextQueryId;
						subqueryIds.push(this.nextQueryId);
						this.addQuery(this.activeQueryId, pname);
					}
					/* STARTLOG */
					if (window.smwhgLogger) {
						var logstr = "Add subquery to query, property '"
								+ pname + "'";
						smwhgLogger.log(logstr, "QI", "query_subquery_added");
					}
					/* ENDLOG */
				}

				var restriction = $('dialoguecontent').rows[cHtmlRow].cells[1].firstChild.value;
				pgroup.addValue(paramname, restriction,
						escapeQueryHTML(paramvalue)); // add a value group to
														// the property group
				cHtmlRow++;
			}
			/* STARTLOG */
			if (window.smwhgLogger) {
				var logstr = "Add property " + pname + " to query";
				smwhgLogger.log(logstr, "QI", "query_property_added");
			}
			/* ENDLOG */
			this.activeQuery.addPropertyGroup(pgroup, subqueryIds,
					this.loadedFromId); // add the property group to the query
			this.emptyDialogue();
			this.updateColumnPreview();
		}
	},

	/**
	 * copies the full query text to the clients clipboard. Works on IE and FF
	 * depending on the users security settings.
	 */
	copyToClipboard : function() {

		if (this.queries[0].isEmpty()) {
			alert(gLanguage.getMessage('QI_EMPTY_QUERY'));
		} else if (($('layout_format').value == "template")
				&& ($('template_name').value == "")) {
			alert(gLanguage.getMessage('QI_EMPTY_TEMPLATE'));
		} else {
			/* STARTLOG */
			if (window.smwhgLogger) {
				smwhgLogger
						.log("Copy query to clipboard", "QI", "query_copied");
			}
			/* ENDLOG */
			var text = this.getFullParserAsk();
			if (window.clipboardData) { // IE
				window.clipboardData.setData("Text", text);
				if (!this.isExcelBridge)
					alert(gLanguage.getMessage('QI_CLIPBOARD_SUCCESS'));
			} else if (window.netscape) {
				try {
					netscape.security.PrivilegeManager
							.enablePrivilege('UniversalXPConnect');
					var clip = Components.classes['@mozilla.org/widget/clipboard;1']
							.createInstance(Components.interfaces.nsIClipboard);
					if (!clip) {
						alert(gLanguage.getMessage('QI_CLIPBOARD_FAIL'));
						return;
					}
					var trans = Components.classes['@mozilla.org/widget/transferable;1']
							.createInstance(Components.interfaces.nsITransferable);
					if (!trans) {
						alert(gLanguage.getMessage('QI_CLIPBOARD_FAIL'));
						return;
					}
					trans.addDataFlavor('text/unicode');
					var str = new Object();
					var len = new Object();
					var str = Components.classes["@mozilla.org/supports-string;1"]
							.createInstance(Components.interfaces.nsISupportsString);
					str.data = text;
					trans.setTransferData("text/unicode", str, text.length * 2);
					var clipid = Components.interfaces.nsIClipboard;
					if (!clip) {
						alert(gLanguage.getMessage('QI_CLIPBOARD_FAIL'));
						return;
					}
					clip.setData(trans, null, clipid.kGlobalClipboard);
					if (!this.isExcelBridge)
						alert(gLanguage.getMessage('QI_CLIPBOARD_SUCCESS'));
				} catch (e) {
					alert(gLanguage.getMessage('QI_CLIPBOARD_FAIL'));
				}
			} else {
				alert(gLanguage.getMessage('QI_CLIPBOARD_FAIL'));
			}
		}
	},

	showFullAsk : function(type, toggle) {
		if (toggle) {
			$('shade').toggle();
			$('showAsk').toggle();
		}
		if (this.queries[0].isEmpty()) {
			//if (!this.isExcelBridge)
				$('fullAskText').value = gLanguage.getMessage('QI_EMPTY_QUERY');
			return;
		} else if (($('layout_format').value == "template")
				&& ($('template_name').value == "")) {
			$('fullAskText').value = gLanguage.getMessage('QI_EMPTY_TEMPLATE');
			return;
		}
		var ask = this.getFullParserAsk();
		ask = ask.replace(/\]\]\[\[/g, "]]\n[[");
		ask = ask.replace(/>\[\[/g, ">\n[[");
		ask = ask.replace(/\]\]</g, "]]\n<");
		if (type == "parser")
			ask = ask.replace(/([^\|]{1})\|{1}(?!\|)/g, "$1\n|");
		$('fullAskText').value = ask;
	},

	showLoadDialogue : function() {
		// List of saved queries with filter
		// load
		sajax_do_call('smwf_qi_QIAccess', [ "loadQuery", "Query:SaveTestQ" ],
				this.loadQuery.bind(this));

	},

	loadQuery : function(request) {
		/*
		 * if(request.responseText == "false"){ //error handling } else { var
		 * query =
		 * request.responseText.substring(request.responseText.indexOf(">"),
		 * request.responseText.indexOf("</ask>")); var elements =
		 * query.split("[["); }
		 */
		alert(request.responseText);
	},

	showSaveDialogue : function() {
		$('shade').toggle();
		$('savedialogue').toggle();
	},

	doSave : function() {
		if (!this.queries[0].isEmpty()) {
			if (this.pendingElement)
				this.pendingElement.hide();
			this.pendingElement = new OBPendingIndicator($('savedialogue'));
			this.pendingElement.show();
			var params = $('saveName').value + ",";
			params += this.getFullParserAsk();
			sajax_do_call('smwf_qi_QIAccess', [ "saveQuery", params ],
					this.saveDone.bind(this));
		} else {
			var request = Array();
			request.responseText = "empty";
			this.saveDone(request);
		}
	},

	saveDone : function(request) {
		this.pendingElement.hide();
		if (request.responseText == "empty") {
			alert(gLanguage.getMessage('QI_EMPTY_QUERY'));
			$('shade').toggle();
			$('savedialogue').toggle();
			$('saveName').value = "";
		} else if (request.responseText == "exists") {
			alert(gLanguage.getMessage('QI_QUERY_EXISTS'));
			$('saveName').value = "";
		} else if (request.responseText == "true") {
			alert(gLanguage.getMessage('QI_QUERY_SAVED'));
			$('shade').toggle();
			$('savedialogue').toggle();
			$('saveName').value = "";
		} else { // Unknown error
			alert(gLanguage.getMessage('QI_SAVE_ERROR'));
			$('shade').toggle();
			$('savedialogue').toggle();
		}
	},

	exportToXLS : function() {
		if (!this.queries[0].isEmpty()) {
			var ask = this.recurseQuery(0);
			var params = ask + ",";
			params += $('layout_format').value + ',';
			params += $('layout_sort').value == "" ? ","
					: $('layout_sort').value + ',';
			params += this.serializeSpecialQPParameters(",");
			sajax_do_call('smwf_qi_QIAccess', [ "getQueryResultForDownload",
					params ], this.initializeDownload.bind(this));
		} else {
			var request = Array();
			request.responseText = gLanguage.getMessage('QI_EMPTY_QUERY');
			this.openPreview(request);
		}
	},

	initializeDownload : function(request) {
		encodedHtml = escape(request.responseText);
		encodedHtml = encodedHtml.replace(/\//g, "%2F");
		encodedHtml = encodedHtml.replace(/\?/g, "%3F");
		encodedHtml = encodedHtml.replace(/=/g, "%3D");
		encodedHtml = encodedHtml.replace(/&/g, "%26");
		encodedHtml = encodedHtml.replace(/@/g, "%40");
		var url = wgServer
				+ wgScriptPath
				+ "/extensions/SMWHalo/specials/SMWQueryInterface/SMW_QIExport.php?q="
				+ encodedHtml;
		window.open(url, "Download", 'height=1,width=1');
	},

	checkFormat : function() {
		
		// update result preview
		this.getSpecialQPParameters($('layout_format').value);
		this.updatePreview();

	},

	initFromQueryString : function(ask) {
		this.doReset();
   
		// does ask contain any data?
		if (ask.replace(/^\s+/, '').replace(/\s+$/, '').length == 0)
			return;
        
        // check triplestore switch if it comes from sparql parser function			
		if (ask.indexOf('#sparql:') != -1) {
			var triplestoreSwitch = $('usetriplestore');
			if (triplestoreSwitch) triplestoreSwitch.checked = true;
		}	

		// split of query parts to handle subqueries seperately
		var sub = this.splitQueryParts(ask);
		// main query must exist, otherwise quit right away
		if (sub.length == 0)
			return;

		// save current query parts in an object variabke to have access on
		// these later
		this.queryPartsFromInitByAsk = sub;

		// run over all query strings and fetch property names
		var propertiesInQuery = new Array();
		for ( var i = 0; i < sub.length; i++) {
			var props = sub[i].match(/\[\[([\w\d _]*)::.*?\]\]/g);
			if (!props)
				props = [];
			for ( var j = 0; j < props.length; j++) {
				var pname = escapeQueryHTML(props[j].substring(2, props[j]
						.indexOf('::')));
				if (!propertiesInQuery.inArray(pname))
					propertiesInQuery.push(pname);
			}
		}
		// check all properties that exist in parameter "must show" only (like |
		// ?myproperty)
		var props = sub[0].split('|');
		for ( var i = 1; i < props.length; i++) {
			if (props[i].match(/^\s*\?/)) {
				var pname = props[i].substring(props[i].indexOf('?') + 1,
						props[i].length);
				pname = escapeQueryHTML(pname.replace(/\s*$/));
				if (!propertiesInQuery.inArray(pname))
					propertiesInQuery.push(pname);
			}
		}
		if (propertiesInQuery.length > 0) {
			// add function to fetch property information
			propertiesInQuery.unshift('getPropertyTypes');
			sajax_do_call('smwf_qi_QIAccess', propertiesInQuery,
					this.parsePropertyTypes.bind(this));
		} else
			// no properties in query
			this.parseQueryString();
	},

	parsePropertyTypes : function(request) {
		if (request.status == 200) {
			var xmlDoc = GeneralXMLTools
					.createDocumentFromString(request.responseText);
			var prop = xmlDoc.getElementsByTagName('relationSchema');
			for ( var i = 0; i < prop.length; i++) {
				var pname = prop.item(i).getAttribute('name');
				var arity = parseInt(prop.item(i).getAttribute('arity'));
				var ptype = prop.item(i).getElementsByTagName('param')[0]
						.getAttribute('name');
				var noval = prop.item(i).getElementsByTagName('allowedValue');
				var isEnum = noval.length > 0 ? true : false;
				var enumValues = [];
				for ( var j = 0; j < noval.length; j++) {
					enumValues.push(noval.item(j).getAttribute('value'));
				}
				var pgroup = new PropertyGroup(pname, arity, false, false,
						isEnum, enumValues);
				this.propertyTypesList.add(pname, pgroup, [], ptype);
			}
		}
		this.parseQueryString();
	},

	parseQueryString : function() {
		var sub = this.queryPartsFromInitByAsk;

		// properties that must be shown in the result
	var pMustShow = this.applyOptionParams(sub[0]);

	// run over all query strings and start parsing
	for (i = 0; i < sub.length; i++) {
		// set current query to active, do this manually (treeview is not
		// updated)
		this.activeQuery = this.queries[i];
		this.activeQueryId = i;
		// extact the arguments, i.e. all between [[...]]
		var args = sub[i].split(/\]\]\s*\[\[/);
		// remove the ]] from the last element
		args[args.length - 1] = args[args.length - 1].substring(0,
				args[args.length - 1].indexOf(']]'));
		// and [[ from the first element
		args[0] = args[0].replace(/^\s*\[\[/, '');
		this.handleQueryString(args, i, pMustShow);
	}
	this.setActiveQuery(0); // set main query to active
	this.updatePreview(); // update result preview
},

handleQueryString : function(args, queryId, pMustShow) {

	// list of properties (each property has an own pgoup)
	var propList = new PropertyList();

	for ( var i = 0; i < args.length; i++) {
		// Category
		if (args[i].indexOf('Category:') == 0) {
			var vals = args[i].substring(9).split(/\s*\|\|\s*/);
			this.activeQuery.addCategoryGroup(vals); // add to query
		}
		// Instance
		else if (args[i].indexOf('::') == -1) {
			var vals = args[i].split(/\s*\|\|\s*/);
			this.activeQuery.addInstanceGroup(vals); // add to query
		}
		// Property
		else {
			var pname = args[i].substring(0, args[i].indexOf('::'));
			var pval = args[i].substring(args[i].indexOf('::') + 2,
					args[i].length);

			// if the property was already once in the arguments, we already
			// have details about the property
			var pgroup = propList.getPgroup(pname);
			if (!pgroup) {
				// get property data from definitions
				var propdef = this.propertyTypesList.getPgroup(pname);
				// show in results? if queryId == 0 then this is the main query
				// and we check the params
				var pshow = (queryId == 0) ? pMustShow.inArray(pname) : false;
				// must be set?
				var pmust = args.inArray('[[' + pname + '::+]]');
				var arity = propdef ? propdef.getArity() : 2;
				var isEnum = propdef ? propdef.isEnumeration() : false;
				var enumValues = propdef ? propdef.getEnumValues() : [];
				pgroup = new PropertyGroup(escapeQueryHTML(pname), arity,
						pshow, pmust, isEnum, enumValues); // create
															// propertyGroup
			}
			var subqueryIds = propList.getSubqueryIds(pname);
			var paramname = this.propertyTypesList
					&& this.propertyTypesList.getType(pname) ? this.propertyTypesList
					.getType(pname)
					: gLanguage.getMessage('QI_PAGE');
			var paramvalue = pval == "" ? "*" : pval; // no value is replaced
														// by "*" which means
														// all values
			var restriction = '=';

			// Subquery
			if (pval.match(/___q\d+q___/)) {
				paramname = "subquery";
				paramvalue = parseInt(pval.replace(/___q(\d+)q___/, '$1'));
				this.insertQuery(paramvalue, queryId, pname);
				subqueryIds.push(paramvalue);
				pgroup.addValue(paramname, restriction, paramvalue); // add a
																		// value
																		// group
																		// to
																		// the
																		// property
																		// group
			} else { // check for restricion (makes sence for numeric
						// properties)
				var vals = pval.split(/\s*\|\|\s*/);
				for ( var j = 0; j < vals.length; j++) {
					var op = vals[j].match(/^([\!|<|>]?=?)(.*)/);
					if (op[1].length > 0) {
						restriction = op[1].indexOf('=') == -1 ? op[1] + '='
								: op[1];
						paramvalue = op[2];
					}
					pgroup.addValue(paramname, restriction,
							escapeQueryHTML(paramvalue)); // add a value group
															// to the property
															// group
				}
			}
			propList.add(pname, pgroup, subqueryIds); // add current property
														// to property list
			// sajax_do_call('smwf_qi_QIAccess', ["getPropertyInformation",
			// escapeQueryHTML(propname)],
			// this.adaptDialogueToProperty.bind(this));
		}
	}

	// if a property must be shown in results only, it may not appear in the
	// [[...]] part
	// therfore check now that in the main query we also have all "must show"
	// properties included
	if (queryId == 0) { // do this only for the main query
		for ( var i = 0; i < pMustShow.length; i++) { // loop over all
														// properties to show
			if (propList.getPgroup(pMustShow[i]) == null) { // property does not
															// exist yet
				var pgroup = new PropertyGroup(escapeQueryHTML(pMustShow[i]),
						2, true, false); // create propertyGroup
				pgroup.addValue('Page', '=', '*'); // add default values
				propList.add(pMustShow[i], pgroup, []); // add current property
														// to property list
			}
		}
	}

	// we are done with all agruments, now add the collected property
	// information to the active query
	propList.reset();
	var cProp = propList.next();
	while (cProp != null) {
		var pgroup = propList.getPgroup(cProp);
		var subqueryIds = propList.getSubqueryIds(cProp);
		this.activeQuery.addPropertyGroup(pgroup, subqueryIds);
		cProp = propList.next();
	}

},

applyOptionParams : function(query) {
	var options = query.split('|');
	// parameters to show
    var mustShow = [];
	// get printout format of query
	var format = "table"; // default format
	for ( var i = 1; i < options.length; i++) {
            var m = options[i].match(/^\s*\?(.*?)\s*$/);
            if (m) {
                mustShow.push(m[1]);
                continue;
            }
            var kv = options[i].replace(/^\s*(.*?)\s*$/, '$1').split(/=/);
            if (kv.length == 1)
                continue;
            
            var key = kv[0].replace(/^\s*(.*?)\s*$/, '$1');
            var val = kv[1].replace(/^\s*(.*?)\s*$/, '$1');
            if (key=="format") {
            	format = val;
            	
            }
    }
	
    
    // The following callback is called after the query printer parameters were displayed.
	var callback = function() {
		// start by 1, first element is the query itself
		for ( var i = 1; i < options.length; i++) {
			
			var kv = options[i].replace(/^\s*(.*?)\s*$/, '$1').split(/=/);
			if (kv.length == 1)
				continue;
			// check if layout_kv[0] exists, then a correct parameter was defined
			// and we set the form element with its value
			var key = kv[0].replace(/^\s*(.*?)\s*$/, '$1');
			var val = kv[1].replace(/^\s*(.*?)\s*$/, '$1');
			if (key == 'format') {
				// special handling for format
				var layout_format = $('layout_format');
				layout_format.value = val;
				this.updatePreview();
			} else {
			    var optionParameter = $('qp_param_' + key);
			    if (optionParameter == null) continue; // ignore unknown options
			    if ('checked' in optionParameter) {
			    	optionParameter.checked = (val == "on" || val == "true");
			    } else {
			    	optionParameter.value = val;
			    }
			}
		}
	}
	
	// and request according format printer parameters
    this.getSpecialQPParameters(format, callback.bind(this));  
	
	// return the properties, that must be shown in the query
	return mustShow;
},

splitQueryParts : function(ask) {
	// ltrim and rtrim
	ask = ask.replace(/^\s*\{\{#(ask|sparql):\s*/, '');
	ask = ask.replace(/\s*\}\}\s*$/, '');

	// store here all queries (sub[0] is the main query
	var sub = [];
	sub.push(ask);
	var todo;
	while (1) {
		todo = null;
		if (sub.length > 0) {
			for ( var i = 0; i < sub.length; i++) {
				if (sub[i].indexOf('<q>') != -1) {
					todo = true;
					var pa = sub[i].indexOf('<q>'); // first occurence of <q>
					var pe = -4; // position of </q>
					var po = pa; // start position where to look for the next
									// <q> after the first one
					// look now for the closing part
					var op = 0; // number of opened brakets of sub queries
					do {
						// look for the first closing </q>
						pe = sub[i].indexOf('</q>', pe + 4);
						// and for another opening <q>
						var po = sub[i].indexOf('<q>', po + 3);
						// if a <q> is found and it's before the </q> then
						// the already know </q> belongs to a inner subquery
						// the </q> for the outer query part is more on the
						// right
						if (po > -1 && po < pe)
							op++;
						else
							op--;
					} while (op > 0); // keep on going if we still have open
										// brakets
					// add the new found sub query to the list of queries
					sub.push(sub[i].substring(sub[i].indexOf('<q>') + 3, pe));
					// replace the sub query with a placeholder in the orignal
					// query
					sub[i] = sub[i].substring(0, sub[i].indexOf('<q>'))
							+ '___q' + (sub.length - 1) + 'q___'
							+ sub[i].substring(pe + 4);
				}
			}
		}
		if (!todo)
			break;
	}
	return sub;
}

}
// end class qiHelper

var PropertyGroup = Class.create();
PropertyGroup.prototype = {

	initialize : function(name, arity, show, must, isEnum, enumValues) {
		this.name = name;
		this.arity = arity;
		this.show = show;
		this.must = must;
		this.isEnum = isEnum;
		this.enumValues = enumValues;
		this.values = Array(); // paramName, retriction, paramValue
},

addValue : function(name, restriction, value) {
	this.values[this.values.length] = new Array(name, restriction, value);
},

getName : function() {
	return this.name;
},

getArity : function() {
	return this.arity;
},

isShown : function() {
	return this.show;
},

mustBeSet : function() {
	return this.must;
},

getValues : function() {
	return this.values;
},

isEnumeration : function() {
	return this.isEnum;
},

getEnumValues : function() {
	return this.enumValues;
}
}

var PropertyList = Class.create();
PropertyList.prototype = {

	initialize : function() {
		this.name = Array();
		this.pgroup = Array();
		this.subqueries = Array();
		this.type = Array();
		this.pointer = -1;
	},

	add : function(name, pgroup, subqueries, type) {
		for ( var i = 0; i < this.name.length; i++) {
			if (this.name[i] == name) {
				this.pgroup[i] = pgroup;
				this.subqueries[i] = subqueries;
				this.type[i] = type;
				return;
			}
		}
		this.name.push(name);
		this.pgroup.push(pgroup);
		this.subqueries.push(subqueries);
		this.type.push(type);
	},

	getPgroup : function(name) {
		for ( var i = 0; i < this.name.length; i++) {
			if (this.name[i] == name)
				return this.pgroup[i];
		}
		return;
	},

	getSubqueryIds : function(name) {
		for ( var i = 0; i < this.name.length; i++) {
			if (this.name[i] == name)
				return this.subqueries[i];
		}
		return new Array();
	},

	getType : function(name) {
		for ( var i = 0; i < this.name.length; i++) {
			if (this.name[i] == name)
				return this.type[i];
		}
	},

	reset : function() {
		this.pointer = -1;
	},

	next : function() {
		this.pointer++;
		if (this.name[this.pointer])
			return this.name[this.pointer];
	}
}

Event.observe(window, 'load', initialize_qi);

function initialize_qi() {
	if (!qihelper)
		qihelper = new QIHelper();
		
}

function initialize_qi_from_querystring(ask) {
	if (!qihelper)
		qihelper = new QIHelper();
	qihelper.initFromQueryString(ask);
}

function initialize_qi_from_excelbridge() {
	if (!qihelper)
		qihelper = new QIHelper();
	qihelper.setExcelBridge();
}

function escapeQueryHTML(string) {
	string = ("" + string).escapeHTML();
	string = string.replace(/\"/g, "&quot;");
	return string;
}

function unescapeQueryHTML(string) {
	string = ("" + string).unescapeHTML();
	string = string.replace(/&quot;/g, "\"");
	return string;
}

// add function iArray (like PHP in_array() ) to Array Object
if (!Array.prototype.inArray) {
	Array.prototype.inArray = function in_array(val) {
		for ( var i = 0; i < this.length; i++) {
			if (val == this[i])
				return true;
		}
		return false;
	}
};

