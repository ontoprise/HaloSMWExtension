/**
 * @file
 * @ingroup SMWHaloSpecials
 * @ingroup SMWHaloQueryInterface
 * 
 *  Query Interface for Semantic MediaWiki
 *  Developed by Markus Nitsche <fitsch@gmail.com>
 *
 *  QIHelper.js
 *  Manages major functionalities and GUI of the Query Interface
 *  @author Markus Nitsche [fitsch@gmail.com]
 *  @author Joerg Heizmann
 *  @author Stephan Robotta
 */


var qihelper = null;

var QIHelper = Class.create();
QIHelper.prototype = {

	/**
	 * Initialize the QIHelper object and all variables
	 */
	initialize : function() {
		this.imgpath = wgScriptPath + '/extensions/SMWHalo/skins/QueryInterface/images/';
        this.divQiDefTabHeight = 300;
        this.divPreviewcontentHeight = 160;
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
        this.updateTree();
		this.setActiveQuery(0);
		this.updateColumnPreview();
		this.pendingElement = null;
		this.queryPartsFromInitByAsk = Array();
		this.propertyTypesList = new PropertyList();
		this.specialQPParameters = new Array();
        
        $('qistatus').innerHTML = gLanguage.getMessage('QI_START_CREATING_QUERY');
        if (! this.noTabSwitch) this.switchTab(1, true);
        this.sourceChanged = 0;
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

    switchDefinition : function() {
        if ($('qiquerydefinition').style.display == "none") {
            $('qiquerydefinition').style.display = "";
            $('definitiontitle-link').removeClassName("plusminus");
            $('definitiontitle-link').addClassName("minusplus");
            $('previewcontent').style.height = this.divPreviewcontentHeight + 'px';
        } else {
            $('qiquerydefinition').style.display = "none";
            $('definitiontitle-link').removeClassName("minusplus");
            $('definitiontitle-link').addClassName("plusminus");
            $('previewcontent').style.height = (this.divQiDefTabHeight + this.divPreviewcontentHeight) + 'px';
        }
    },

    switchResult : function() {
        if ($('qiresultcontent').style.display == "none") {
            $('qiresultcontent').style.display = "";
            $('qiresulttitle-link').removeClassName("plusminus");
            $('qiresulttitle-link').addClassName("minusplus");
            this.updatePreview();
        }else {
            $('qiresultcontent').style.display = "none";
            $('qiresulttitle-link').removeClassName("minusplus");
            $('qiresulttitle-link').addClassName("plusminus");
        }
    },


	/**
	 * Called whenever preview result printer needs to be updated.
     * This is only done, if the results are visible.
	 */
	updatePreview : function() {
		// update result preview
		if ($("previewcontent").style.display == "" &&
            $("qiresultcontent").style.display == "") {
			this.previewResultPrinter();
		}
	},

    updateQuerySource : function() {
        // if query source tab is active
        if ($('qiDefTab3').className.indexOf('qiDefTabActive') > -1)
            this.showFullAsk('parser', false);
    },

	getSpecialQPParameters : function(qp, callWhenFinished) {
		var callback = function(request) {
			this.parameterPendingElement.hide();
			var columns = 3;
			var html = '<b>' + gLanguage.getMessage('QI_SPECIAL_QP_PARAMS') + "</b> <i>"
					+ qp + '</i>:<table style="width: 100%;">';
			var qpParameters = request.responseText.evalJSON();
			var i = 0;
			qpParameters.each(function(e) {
				if (i % columns == 0)
					html += "<tr>"
				html += '<td onmouseover="Tip(\'' + e.description
						+ '\');">' + e.name + "</td>";
				if (e.values instanceof Array) {
					html += '<td>' + createSelectionBox(e.name, e.values)
							+ "</td>";
				} else if (e.type == 'string' || e.type == 'int') {
					html += '<td>' + createInputBox(e.name, e.values, e.constraints)
							+ "</td>";
				} else if (e.type == 'boolean') {
					html += '<td>' + createCheckBox(e.name, e.defaultValue)
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
			var html = '<select id="' + 'qp_param_' + id + '" onchange="qihelper.updateQuerySource(); qihelper.updatePreview()">';
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
			var html = '<input id="' + 'qp_param_' + id + '" type="text" '+aclAttributes+' onchange="qihelper.updateQuerySource(); qihelper.updatePreview()"/>';
			return html;
		}
		var createCheckBox = function(id, defaultValue) {
			var defaultValueAtt = defaultValue ? 'checked="checked"' : '';
			var html = '<input id="' + 'qp_param_' + id + '" type="checkbox" '
					+ defaultValueAtt + ' onchange="qihelper.updateQuerySource(); qihelper.updatePreview()"/>';
			return html;
		}
		if (this.parameterPendingElement)
                    this.parameterPendingElement.remove();
                this.parameterPendingElement = new OBPendingIndicator($('querylayout'));
                this.parameterPendingElement.show();
        
		sajax_do_call('smwf_qi_QIAccess', [ 'getSupportedParameters', qp ],
				callback.bind(this));

	},

	serializeSpecialQPParameters : function(sep) {
		var paramStr = "";
		var first = true;
		this.specialQPParameters.each(function(p) {
			var element = $('qp_param_' + p.name);
			if (p.type == 'boolean' && element.checked) {
				paramStr += first ? p.name : sep + " " + p.name;
			} else {
				if (element.value != "" && element.value != p.defaultValue) {
					paramStr += first ? p.name + "=" + element.value.replace(/,/g,"%2C") : sep + " " + p.name + "=" + element.value;
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
		this.activeQueryId = id;
		this.emptyDialogue(); // empty open dialogue
		this.updateBreadcrumbs(id); // update breadcrumb navigation of treeview
		// update everything
	},

	/**
	 * Shows a confirmation dialogue
	 */
	resetQuery : function() {
		$('shade').style.display = "inline";
		$('resetdialogue').style.display = "inline";
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

    updateTree : function() {
        var treeXML = "";
        var queryIds = new Array();
        queryIds.push(0);
        for (var i = 0; i < queryIds.length; i++) {
            var activeQuery = this.queries[queryIds[i]];
            if (! activeQuery) continue; // deleted subqueries are removed but position is empty
            var xml = activeQuery.updateTree(); // update treeview
            if (i == 0) treeXML = xml;
            else
                treeXML = treeXML.replace('___SUBQUERY_'+i+'___', xml);
            for (var j = 0; j < activeQuery.subqueryIds.length; j++)
                queryIds.push(activeQuery.subqueryIds[j]);
        }
        $('treeanchor').innerHTML = treeXML;
        //updateQueryTree(treeXML);
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
        try {this.pendingElement.remove();} catch(e) {};
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
        try {this.pendingElement.remove();} catch(e) {};
		this.pendingElement = new OBPendingIndicator($('previewcontent'));
		this.pendingElement.show();

        var ask = this.getQueryFromTree();

        if (ask.length > 0) {
			sajax_do_call('smwf_qi_QIAccess', [ "getQueryResult", ask ],
					this.openResultPreview.bind(this));
		} else { // query is empty
			var request = Array();
			request.responseText = gLanguage.getMessage('QI_EMPTY_QUERY');
			this.openResultPreview(request);
		}
	},

    getQueryFromTree : function() {
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
            return params;
        }
        return "";
    },

	/**
	 * Displays the preview created by the server
	 * 
	 * @param request
	 *            Request of AJAX call
	 */
	openPreview : function(request) {
		 switch ($('layout_format').value) {
            
            // for certain query printer it is
            // necessary to clear content of preview
            case 'ofc-pie':
            case 'ofc-bar':
            case 'ofc-bar_3d':
            case 'ofc-line':
            case 'ofc-scatterline':
		       $('previewcontent').innerHTML = '';
		       break;
		 }
		this.pastePreview(request, $('fullpreview'));
	},

	/**
	 * Displays the preview created by the server
	 * 
	 * @param request
	 *            Request of AJAX call
	 */
	openResultPreview : function(request) {
		this.pastePreview(request, $('previewcontent'));
	},
	
	pastePreview: function(request, preview) {
		this.pendingElement.hide();
        
        // pre-processing
        var resultHTML;
        var resultCode;
        switch ($('layout_format').value) {
            
            case 'ofc-pie':
            case 'ofc-bar':
            case 'ofc-bar_3d':
            case 'ofc-line':
            case 'ofc-scatterline':
            var tuple =  request.responseText.split("|||");
            resultHTML = tuple[0];
            resultCode = tuple[1];
            
            break;
        default:
            resultHTML = request.responseText;
            resultCode = null;
        }
        
        preview.innerHTML = resultHTML;
        $('fullpreviewbox').width = ''; // clear fixed width if we had a timeline
        
        smw_tooltipInit();

        // post processing of javascript for resultprinters:
        switch ($('layout_format').value) {
        case "timeline":
        case "eventline":
            this.parseWikilinks2Html();
            smw_timeline_init();
            $('fullpreviewbox').width = '500px';
            break;
        case "exhibit":
            if (typeof createExhibit == 'function') createExhibit();
            break;
        case 'ofc-pie':
        case 'ofc-bar':
        case 'ofc-bar_3d':
        case 'ofc-line':
        case 'ofc-scatterline':
            ofc_data_objs = [];
            if (resultCode != null) eval(resultCode);
            resetOfc();
            break;
        }
	},
    // ofc stuff can be once at a page only. If the full preview is closed,
    // load the small preview box again
    reloadOfcPreview : function() {
        if ($('layout_format').value.indexOf('ofc-') == 0)
            this.updatePreview();
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
	updateBreadcrumbs : function(id, action) {
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
			html += '<span class="qibuttonEmp" onclick="qihelper.setActiveQuery(' + nav[i] + ')">';
			html += this.queries[nav[i]].getName() + '</span>';
		}
        if (action) html += ': <b>' + action + '</b>';
		var breadcrumpDIV = $('treeviewbreadcrumbs');
 		if (breadcrumpDIV) breadcrumpDIV.innerHTML = html;
	},

    updateHeightBoxcontent : function() {
        var off = 0;
        var dim = $('treeviewbreadcrumbs').getDimensions();
        off += dim.height + 3;
        dim = $('qistatus').getDimensions();
        off += dim.height + 3;
        dim = $('dialoguebuttons').getDimensions();
        off += dim.height + 3;
        $('boxcontent').style.height = (300 - off) + 'px';
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
		$('layout_sort').innerHTML = "";
		for ( var i = 0; i < columns.length; i++) {
			$('layout_sort').options[$('layout_sort').length] = new Option(
					columns[i], columns[i]); // add options to optionbox
		}
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
		var qParams = this.serializeSpecialQPParameters("|");
		if (qParams.length > 0) {
            if (! qParams.match(/^\s*\|/))
                fullQuery += '| ';
            fullQuery += qParams;
        }
		fullQuery += "| merge=false|}}";

		return fullQuery;
	},

    getAskQueryFromGui : function() {
        // which tab is active? query source or any other
        if ($('qiDefTab3').className.indexOf('qiDefTabActive') != -1)
            return $('fullAskText').value;
        else
            return this.getFullParserAsk();
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

    resetDialogueContent : function(reset) {
        $('qidelete').style.display = "none";   // New dialogue, no delete button
        $('qistatus').innerHTML= '';            // empty status message
		autoCompleter.deregisterAllInputs();
		if (reset)
			this.loadedFromId = null;
		for ( var i = 0, n = $('dialoguecontent').rows.length; i < n; i++)
            // empty dialogue table
			$('dialoguecontent').deleteRow(0);
        // the property dialogue has several tables
        while (1) {
            var n = $('dialoguecontent').parentNode.nextSibling;
            if (!n) break;
            n.parentNode.removeChild(n);
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
        this.emptyDialogue();
        // remove any selection in the tree, in case there is one
        if (reset) this.updateTree();
        // add current action to breadcrumbs path
        this.updateBreadcrumbs(this.activeQueryId, gLanguage.getMessage((reset) ? 'QI_BC_ADD_CATEGORY' : 'QI_BC_EDIT_CATEGORY') );
		this.activeDialogue = "category";
        this.resetDialogueContent(reset);

        var newrow = $('dialoguecontent').insertRow(-1); // create the
															// dialogue
		var cell = newrow.insertCell(0);
		cell.innerHTML = gLanguage.getMessage('CATEGORY');
		cell = newrow.insertCell(1);
        // input field with autocompletion enabled
		cell.innerHTML = '<input type="text" id="input0" class="wickEnabled general-forms" constraints="namespace: 14" autocomplete="OFF"/>';
        cell = newrow.insertCell(2);
        // link to add another input for or-ed values
        newrow = $('dialoguecontent').insertRow(-1);
        cell = newrow.insertCell(0);
        cell.style.textAlign="left";
        cell.setAttribute('colspan', '3');
		cell.innerHTML = '<a href="javascript:void(0)" onclick="qihelper.addDialogueInput()">'
            + gLanguage.getMessage('QI_BC_ADD_OTHER_CATEGORY') + '</a>';
		$('dialoguebuttons').style.display = "inline";
        $('dialoguebuttons').getElementsByTagName('button').item(0).innerHTML = 
            gLanguage.getMessage((reset) ? 'QI_BUTTON_ADD' : 'QI_BUTTON_UPDATE');
		autoCompleter.registerAllInputs();
		if (reset)
			$('input0').focus();
        this.updateHeightBoxcontent();
	},

	/**
	 * Creates a new dialogue for adding instances to the query
	 * 
	 * @param reset
	 *            indicates if this is a new dialogue or if it is loaded from
	 *            the tree
	 */
	newInstanceDialogue : function(reset) {
        this.emptyDialogue();
        if (reset) this.updateTree();
        this.updateBreadcrumbs(this.activeQueryId, gLanguage.getMessage((reset) ? 'QI_BC_ADD_INSTANCE' : 'QI_BC_EDIT_INSTANCE') );
		this.activeDialogue = "instance";
        this.resetDialogueContent(reset);
		var newrow = $('dialoguecontent').insertRow(-1);
		var cell = newrow.insertCell(0);
		cell.innerHTML = gLanguage.getMessage('QI_INSTANCE');
		cell = newrow.insertCell(1);
		cell.innerHTML = '<input type="text" id="input0" class="wickEnabled general-forms" constraints="namespace: 0" autocomplete="OFF"/>';
        cell = newrow.insertCell(2);
        // link to add another input for or-ed values
        newrow = $('dialoguecontent').insertRow(-1);
        cell = newrow.insertCell(0);
        cell.style.textAlign="left";
        cell.setAttribute('colspan', '3');
		cell.innerHTML = '<a href="javascript:void(0)" onclick="qihelper.addDialogueInput()">'
            + gLanguage.getMessage('QI_BC_ADD_OTHER_INSTANCE') + '</a>';
		$('dialoguebuttons').style.display = "inline";
        $('dialoguebuttons').getElementsByTagName('button').item(0).innerHTML =
            gLanguage.getMessage((reset) ? 'QI_BUTTON_ADD' : 'QI_BUTTON_UPDATE');
		autoCompleter.registerAllInputs();
		if (reset)
			$('input0').focus();
        this.updateHeightBoxcontent();
	},

	/**
	 * Creates a new dialogue for adding properties to the query
	 * 
	 * @param reset
	 *            indicates if this is a new dialogue or if it is loaded from
	 *            the tree
	 */
	newPropertyDialogue : function(reset) {
        this.emptyDialogue();
        if (reset) this.updateTree();
        this.updateBreadcrumbs(this.activeQueryId, gLanguage.getMessage((reset) ? 'QI_BC_ADD_PROPERTY' : 'QI_BC_EDIT_PROPERTY') );
		this.activeDialogue = "property";
		this.propname = "";
        this.resetDialogueContent();
        
        // first table, with at least one input field for property name
        this.addPropertyChainInput();

        this.completePropertyDialogue();
       
		$('dialoguebuttons').style.display = "inline";
        $('dialoguebuttons').getElementsByTagName('button').item(0).innerHTML =
            gLanguage.getMessage((reset) ? 'QI_BUTTON_ADD' : 'QI_BUTTON_UPDATE');
		this.proparity = 2;
		autoCompleter.registerAllInputs();
		if (reset)
			$('input_p0').focus();
        this.updateHeightBoxcontent();
	},

    addPropertyChainInput : function(propName) {
        autoCompleter.deregisterAllInputs();
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
        var constraintstring = "schema-property-domain: "+constraintsCategories+ "|annotation-property: "+constraintsCategories + "|namespace: 102";
        var idx = $('dialoguecontent').rows.length;
        if (idx > 0) idx = (idx - 1) / 2;
		var newrow = $('dialoguecontent').insertRow(idx*2);
        // row with input field and remove icon
        var cell = newrow.insertCell(0);
        if (idx == 0) cell.innerHTML = gLanguage.getMessage('QI_PROPERTYNAME');
        else cell.innerHTML = ' <img src="' + this.imgpath + 'chain.png" alt="chain"/>';
		cell = newrow.insertCell(1);
        cell.style.textAlign="left";
        cell.style.verticalAlign="middle";
        var tmpHTML = '<input type="text" id="input_p'+ idx +'" '
            + 'class="wickEnabled general-forms" constraints="' + constraintstring + '" '
            + ((idx > 0) ? 'style="font-weight:bold;" ' : '')
            + 'autocomplete="OFF" onblur="qihelper.getPropertyInformation()"'
            + ((propName) ? ' value="'+propName+'"' : '')
            + '/>';
        if (idx > 0)
            tmpHTML += ' <img src="'	+ this.imgpath + 'delete.png" alt="deleteInput" onclick="qihelper.removePropertyChainInput()"/>';
        cell.innerHTML = tmpHTML;
        // row with property type
        newrow = $('dialoguecontent').insertRow(idx*2+1);
        newrow.style.lineHeight="1";
        newrow.insertCell(0);
        cell = newrow.insertCell(1);
        cell.style.textAlign="left";
        cell.style.fontSize="60%";
        cell.style.color="#AAAAAA";
        cell.innerHTML = gLanguage.getMessage('QI_PROPERTY_TYPE') + ':';
        // link to add property chain
        if (idx == 0) {
            newrow = $('dialoguecontent').insertRow(-1);
            cell = newrow.insertCell(0);
            cell.style.textAlign="left";
            cell.setAttribute('colspan', 3);
            cell.innerHTML = '<div id="addchain"></div>';
        }
        else {
            // if there is a remove icon in the previous line, remove it.
            try {
                var img = $('dialoguecontent').rows[(idx-1)*2].getElementsByTagName('td')[1].getElementsByTagName('img');
                if (img.length > 0)
                    img[0].parentNode.removeChild(img[0]);
            } catch (e) {};
            // if the previous input field has bold style, remove that
            try {
                var input = $('dialoguecontent').rows[(idx-1)*2].getElementsByTagName('td')[1].getElementsByTagName('input');
                input[0].style.fontWeight = null;
            } catch (e) {};
        }
        autoCompleter.registerAllInputs();
		if (!propName) $('input_p' + idx).focus(); // focus created input
        this.toggleAddchain(false);
    },

    setPropertyRestriction : function () {
        if (this.oldPropertyRestriction == null) this.oldPropertyRestriction = -1;
        var table = $('dialoguecontent_pradio')
        if (!table) return;
        var radio = table.getElementsByTagName('input');
        if (radio[1].checked) {
            $('dialoguecontent_pvalues').style.display="inline";
            if ($('dialoguecontent_pvalues').rows.length == 0)
                this.addRestrictionInput();
        }
        else {
            $('dialoguecontent_pvalues').style.display="none";
        }
        for (var i = 0, n = radio.length; i < n; i++) {
            if (radio[i].checked) {
                this.oldPropertyRestriction = radio[i].value;
                break;
            }
        }
    },

    addRestrictionInput : function () {
        autoCompleter.deregisterAllInputs();
        var arity= (this.proparity) ? this.proparity : 2;
        if ($('dialoguecontent_pvalues').rows.length ==  0 || arity > 2)
            var newrow = $('dialoguecontent_pvalues').insertRow(-1);
        else
            var newrow = $('dialoguecontent_pvalues').insertRow($('dialoguecontent_pvalues').rows.length -1);
        try {
            var newRowIndex = $('dialoguecontent_pvalues').rows[newrow.rowIndex - 1].id;
            newRowIndex = parseInt(newRowIndex.substr(5))+1;
        } catch (e) {newRowIndex = 1;}
        if (!newRowIndex) newRowIndex = 1;
        newrow.id = "row_r" + newRowIndex;
        var cell = newrow.insertCell(0);
        if (newrow.rowIndex == 0)
            cell.innerHTML = gLanguage.getMessage('QI_PROPERTYVALUE');
        else {
            cell.innerHTML = gLanguage.getMessage('QI_OR').toUpperCase();
            cell.style.fontWeight="bold";
            cell.style.textAlign="right";
        }
        cell = newrow.insertCell(1);
        var param = (this.propTypename) ? this.propTypename : gLanguage.getMessage('QI_PAGE');
		if (param == gLanguage.getMessage('QI_PAGE')) { // property dialogue & type = page
			cell.innerHTML = this.createRestrictionSelector("=", false, false);
			cell = newrow.insertCell(2);
			cell.innerHTML = '<input class="wickEnabled general-forms" constraints="namespace: 0" autocomplete="OFF" type="text" id="input_r' + newRowIndex + '"/>';
		} else { // property, no page type
			if (this.numTypes[param.toLowerCase()]) // numeric type? operators
													// possible
				cell.innerHTML = this.createRestrictionSelector("=", false, true);
			else
				cell.innerHTML = this.createRestrictionSelector("=", false, false);
			cell = newrow.insertCell(2);
			if (this.propIsEnum) { // if enumeration, a select box is used
									// instead of a text input field
				var tmpHTML = '<select id="input_r' + newRowIndex + '">';
				for ( var i = 0; i < this.enumValues.length; i++) {
					tmpHTML += '<option value="' + this.enumValues[i]
							+ '" style="width:100%">' + this.enumValues[i]
							+ '</option>';
				}
				tmpHTML += '</select>';
				cell.innerHTML = tmpHTML;
			} else { // no enumeration, no page type, simple input field
				var tmpHTML = '<input type="text" id="input_r' + newRowIndex + '"/>';
                try {
                    var uIdx = (arity == 2) ? 0 : newRowIndex - 1;
                    if (this.propUnits.length > 0 && this.propUnits[uIdx].length > 0) {
                        tmpHTML += '<select id="input_ru' + newRowIndex + '">';
                        for (var i = 0, m = this.propUnits[uIdx].length; i < m; i++)
                            tmpHTML += '<option>'+ this.propUnits[uIdx][i] + '</option>';
                        tmpHTML += '</select>';
                    }
                } catch (e) {};
                cell.innerHTML = tmpHTML;
			}
		}
        if (arity == 2) {
            if ($('dialoguecontent_pvalues').rows.length > 1) {
                cell = newrow.insertCell(-1);
                cell.innerHTML = '<img src="'
                	+ this.imgpath
                    + 'delete.png" alt="deleteInput" onclick="qihelper.removeRestrictionInput(this)"/>';
            }
            else {
                newrow = $('dialoguecontent_pvalues').insertRow(-1);
                cell = newrow.insertCell(-1);
                cell.setAttribute('colspan', '4');
                cell.innerHTML = '<a href="javascript:void(0);" onclick="qihelper.addRestrictionInput()">'
                    + gLanguage.getMessage('QI_DC_ADD_OTHER_RESTRICT') + '</a>';
            }
        }
        if ($('dialoguecontent_pvalues').style.display != 'none')
            $('input_r' + newRowIndex).focus(); // focus created input
		autoCompleter.registerAllInputs();
    },

    removeRestrictionInput : function(element) {
        var tr = element.parentNode.parentNode;
        tr.parentNode.removeChild(tr);
    },

    removePropertyChainInput : function() {
        var idx = ($('dialoguecontent').rows.length -1) / 2 -1;
        if (idx == 0) return;
        $('dialoguecontent').deleteRow(idx*2+1);
        $('dialoguecontent').deleteRow(idx*2);
        if (idx > 1) {
            var img = document.createElement('img');
            img.src=this.imgpath + "delete.png";
            img.alt="deleteInput";
            img.setAttribute('onclick', "qihelper.removePropertyChainInput()");
            $('dialoguecontent').rows[idx *2 - 2].getElementsByTagName('td')[1].appendChild(img);
            $('dialoguecontent').rows[idx *2 - 2].getElementsByTagName('td')[1]
                .getElementsByTagName('input').item(0).style.fontWeight = "bold";
        }
        this.toggleAddchain(true);
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
        this.propTypename = null;
        this.propUnits = null;
		for ( var i = 0, n = $('dialoguecontent').rows.length; i < n; i++)
			$('dialoguecontent').deleteRow(0);
        while (n = $('dialoguecontent').parentNode.nextSibling ) {
            n.parentNode.removeChild(n);
        }
		$('dialoguebuttons').style.display = "none";
		$('qistatus').innerHTML = "";
		$('qidelete').style.display = "none";
		this.activeInputs = 0;
        this.updateBreadcrumbs(this.activeQueryId);
	},

	/**
	 * Add another input to the current dialogue
	 */
	addDialogueInput : function() {
		autoCompleter.deregisterAllInputs();
        // id for input field, increased by one from the last field
        var inputs = $('dialoguecontent').getElementsByTagName('input');
        var id = inputs[inputs.length-1].id;
        id = parseInt(id.substring(5))+1;
        var newRowId = $('dialoguecontent').rows.length - 1;
		var newrow = $('dialoguecontent').insertRow(newRowId);
		var cell = newrow.insertCell(0);
        cell.style.fontWeight = "bold";
		cell.innerHTML = gLanguage.getMessage('QI_OR').toUpperCase();
		cell = newrow.insertCell(1);

		if (this.activeDialogue == "category") // add input fields according to
												// dialogue
			cell.innerHTML = '<input class="wickEnabled general-forms" constraints="namespace: 14" autocomplete="OFF" type="text" id="input' + id + '"/>';
		else if (this.activeDialogue == "instance")
			cell.innerHTML = '<input class="wickEnabled general-forms" constraints="namespace: 0" autocomplete="OFF" type="text" id="input' + id + '"/>';
		cell = newrow.insertCell(-1);
		cell.innerHTML = '<img src="'
				+ this.imgpath
				+ 'delete.png" alt="deleteInput" onclick="qihelper.removeInput(this);"/>';
		$('input' + id).focus(); // focus created input
		autoCompleter.registerAllInputs();
	},

	/**
	 * Removes an input if the remove icon is clicked
	 * 
	 * @param el
	 *           DOMnode of the image element, which is in a table row
     *           that will be deleted
	 */
	removeInput : function(el) {
        var tr = el.parentNode.parentNode;
        tr.parentNode.removeChild(tr);
	},

	/**
	 * Is called everytime a user entered a property name and leaves the input
	 * field. Executes an ajax call which will get information about the
	 * property (if available)
	 */
	getPropertyInformation : function() {
        var idx = ($('dialoguecontent').rows.length -1) / 2 - 1;
 		var propname = $('input_p'+idx).value;
		if (propname != "" && propname != this.propname) { // only if not empty
															// and name changed
			this.propname = propname;
			if (this.pendingElement) {
                try {
                    this.pendingElement.remove();
                } catch (e) {}
            }
            // try to remove blank row that indicates that a property information is loaded
            if ($('dialoguecontent_pvalues')) {
                while ($('dialoguecontent_pradio').rows.length > 1)
                    $('dialoguecontent_pradio').deleteRow(1);
            }
            // clean hidden table with old data and add pending indicator.
            /*
            $('displaycontent_pvalues_hidden').innerHTML = '';
            var row = $('displaycontent_pvalues_hidden').insertRow(-1);
            var cell = row.insertCell(-1);
            this.pendingElement = new OBPendingIndicator(cell);
            this.pendingElement.show();
            */
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
			var oldsubid = $('input_r0') ? $('input_r0').value : this.nextQueryId;
            if ($('dialoguecontent_pvalues')) {
                while ($('dialoguecontent_pvalues').rows.length > 0)
                    $('dialoguecontent_pvalues').deleteRow(0);
            }
            if ($('dialoguecontent_pradio'))
                $('dialoguecontent_pradio').insertRow(-1);
            var tmpHTML = "";
			// create standard values in case request fails
			this.proparity = 2;
			var parameterNames = [ gLanguage.getMessage('QI_PAGE') ];
			var parameterTypes = [];
			var possibleValues = new Array();
            var possibleUnits = new Array();
            var propertyName= $('dialoguecontent').rows[$('dialoguecontent').rows.length -3]
                                    .getElementsByTagName('input')[0].value;

			if (request.status == 200) {
				var schemaData = GeneralXMLTools
						.createDocumentFromString(request.responseText);

				// read arity
				this.proparity = parseInt(schemaData.documentElement
						.getAttribute("arity"));
                propertyName = schemaData.documentElement.getAttribute("name");
				parameterNames = [];
				// parse all parameter names
				for ( var i = 0, n = schemaData.documentElement.childNodes.length; i < n; i++) {
					parameterNames.push(schemaData.documentElement.childNodes[i].getAttribute("name"));
					parameterTypes.push(schemaData.documentElement.childNodes[i].getAttribute("type"));
                    var allowedValues = schemaData.documentElement.childNodes[i].getElementsByTagName('allowedValue');
					for ( var j = 0, m = allowedValues.length; j < m; j++) {
                        // contains allowed values for enumerations if applicable
						possibleValues.push(allowedValues[j].getAttribute("value"));
					}
                    var units = schemaData.documentElement.childNodes[i].getElementsByTagName('unit');
                    possibleUnits.push(new Array());
  					for ( var j = 0, m = units.length; j < m; j++) {
                        // contains unit label
     					possibleUnits[i].push(units[j].getAttribute("label"));
                    }
				}
                this.propUnits = possibleUnits;
                // if this property has units, it's a nummeric type
                if (possibleUnits.length > 0 &&
                    possibleUnits[0].length > 0 &&
                    !this.numTypes[parameterNames[0].toLowerCase()])
                    this.numTypes[parameterNames[0].toLowerCase()] = true;
			}
            // remove additional rows, if these had been added before
            // we got the information that this property is not of the type page
            var rowCount= origRowCount = $('dialoguecontent').rows.length;
            while (rowCount > 3 && propertyName.length > 0 &&
                   $('dialoguecontent').rows[rowCount -3].cells[1].firstChild.value != propertyName) {
                $('dialoguecontent').deleteRow(rowCount-2);
                $('dialoguecontent').deleteRow(rowCount-3);
                rowCount= $('dialoguecontent').rows.length;
            }
            if (rowCount < origRowCount) {
                var img = document.createElement('img');
                img.src = this.imgpath + 'delete.png';
                img.alt = "deleteInput"
                img.setAttribute('onclick',"qihelper.removePropertyChainInput()");
                $('dialoguecontent').rows[rowCount - 3].cells[1].appendChild(img);
            }
            // property name with _ for auto completion
            var propNameAC = gLanguage.getMessage('PROPERTY_NS')+propertyName.replace(/\s/g, '_');
			if (this.proparity == 2) {
				// Special treatment: binary properties support conjunction,
				// therefore we need an "add" button
				var ac_constraint = "";
				if (parameterTypes[0] == '_wpg') {
					ac_constraint = 'constraints="annotation-value: '+propNameAC+'|namespace: 0"';
                    // enable subquery and add chain link
                    this.toggleSubqueryAddchain(true);
				} else if (parameterTypes[0] == '_dat') {
					ac_constraint = 'constraints="fixvalues: {{NOW}},{{TODAY}}|annotation-value: '+propNameAC+'"';
                    this.toggleSubqueryAddchain(false);
    			} else {
					ac_constraint = 'constraints="annotation-value: '+propNameAC+'"';
                    this.toggleSubqueryAddchain(false);
				}
                // set property type
                this.propTypename = parameterNames[0];
                $('dialoguecontent').rows[$('dialoguecontent').rows.length -2].cells[1].innerHTML=
                    gLanguage.getMessage('QI_PROPERTY_TYPE') + ': ' + parameterNames[0];
                // start to build HTML for restriction values
				tmpHTML += '<tr><td>' + gLanguage.getMessage('QI_PROPERTYVALUE') + '</td><td>';

                // add units to selection to show property chekbox if there are any
                if (possibleUnits.length > 0 && possibleUnits[0].length > 0) {
                    var uopts= '';
                    for (var i = 0; i < possibleUnits[0].length; i++ ) {
                        uopts += '<option>'+ possibleUnits[0][i] + '</option>';
                    }
                    $('input_c4').innerHTML = uopts;
                    // runtime issue, check if we display hide values at once
                    $('input_c4d').style.display = $('input_c1').checked ? null : 'none';
                }
                else {
                    $('input_c4').innerHTML = "";
                    $('input_c4d').style.display = 'none';
                }
				
				// set restriction selector
				if (this.numTypes[parameterNames[0].toLowerCase()]) {
					tmpHTML += this.createRestrictionSelector("=", false, true);
				} else
					tmpHTML += this.createRestrictionSelector("=", false, false);
                // input field
				tmpHTML +='</td><td>';
                
   				// special input field for enums
				if (possibleValues.length > 0) { // enumeration
					this.propIsEnum = true;
					this.enumValues = new Array();
					
					tmpHTML += '<select id="input_r1">' // create html for option box
                        + '<option value="" style="width:100%">*</option>';
					for ( var i = 0; i < possibleValues.length; i++) {
						this.enumValues.push(possibleValues[i]); // save
																	// enumeration
																	// values
																	// for later
																	// use
						tmpHTML += '<option value="' + possibleValues[i]
								+ '" style="width:100%">' + possibleValues[i]
								+ '</option>';
					}
					tmpHTML += "</select>";
				}
                else { // normal input field
                    tmpHTML += '<input class="wickEnabled general-forms" '+ac_constraint+' autocomplete="OFF" type="text" id="input_r1"/>';
                    if (possibleUnits.length > 0 && possibleUnits[0].length > 0) {
                        tmpHTML += '<select id="input_ru1">';
                        for (var i = 0, m = possibleUnits[0].length; i < m; i++)
                            tmpHTML += '<option>' + possibleUnits[0][i] + '</option>';
                        tmpHTML += '</select>';
                    }
                }

				// add property input button 
				tmpHTML += '</td><td><img src="' + this.imgpath + 'add.png" alt="addPropertyInput" onclick="qihelper.addRestrictionInput()"/></td></tr>';
                
                // if binary property, make an 'insert subquery' checkbox
				if (parameterTypes[0] == '_wpg') {
					$('dialoguecontent_pradio').getElementsByTagName('input')[2].value = oldsubid;
                    $('dialoguecontent_pradio').getElementsByTagName('input')[2].disabled = '';
				} else { // no checkbox for other types
   					$('dialoguecontent_pradio').getElementsByTagName('input')[2].disabled = 'true';
				}
			} else {
				// properties with arity > 2: attributes or n-ary. no conjunction, no subqueries
                $('dialoguecontent_pradio').getElementsByTagName('input')[2].disabled = 'true';
                // set property type
                $('dialoguecontent').rows[$('dialoguecontent').rows.length -2].cells[1].innerHTML=
                    gLanguage.getMessage('QI_PROPERTY_TYPE') + ': ' + gLanguage.getMessage('QI_RECORD');
                this.propTypename = gLanguage.getMessage('QI_RECORD');
                this.toggleSubqueryAddchain(false);

                var row = $('dialoguecontent_pvalues').insertRow(-1);
                var cell = row.insertCell(-1);
                cell.innerHTML = gLanguage.getMessage('QI_PROPERTYVALUE');
                tmpHTML += '<tr><td>' +  + '</td></tr>';
				for ( var i = 0; i < parameterNames.length; i++) {
                    // Label of cell is parameter name (ex.: Integer, Date,...)
                    row = $('dialoguecontent_pvalues').insertRow(-1);
                    cell = row.insertCell(-1);
                    cell.style.textAlign="right";
                    cell.innerHTML = parameterNames[i];
                    cell = row.insertCell(-1);
                    cell.style.textAlign="right";
					if (this.numTypes[parameterNames[i].toLowerCase()])
						cell.innerHTML = this.createRestrictionSelector("=", false, true);
					else
						cell.innerHTML = this.createRestrictionSelector("=", false, false);
                    cell = row.insertCell(-1);
					if (parameterTypes[i] == '_wpg') {
                    	cell.innerHTML = '<input class="wickEnabled general-forms" constraints="annotation-value: '+propNameAC+'|namespace: 0" autocomplete="OFF" type="text" id="input_r' + (i + 1) + '"/>';
					} else if (parameterTypes[i] == '_dat') {
						cell.innerHTML = '<input type="text" id="input_r' + (i + 1) + '" constraints="fixvalues: {{NOW}},{{TODAY}}|annotation-value: '+propNameAC+'"/>';
					} else {
						var tmpHTML = '<input type="text" id="input_r' + (i + 1) + '" constraints="annotation-value: '+propNameAC+'"/>';
                        if (possibleUnits.length > 0 && possibleUnits[i].length > 0) {
                            tmpHTML += '<select id="input_ru' + (i + 1) +'">';
                            for (var j = 0, m = possibleUnits[i].length; j < m; j++)
                                tmpHTML += '<option>' + possibleUnits[i][j] + '</option>';
                            tmpHTML += '</select>';
                        }
                        cell.innerHTML = tmpHTML;
					}
				}
			}
            // runtime issue: if the user selected radio for specific value
            // and the property information is loaded after that, make the new
            // created restriction table visible
            if ($('dialoguecontent_pradio').getElementsByTagName('input')[1].checked)
                this.setPropertyRestriction();
		}
		autoCompleter.registerAllInputs();
		//this.pendingElement.hide();
	},

    /**
     * After the property name has been entered into the input field, the
     * type is retrieved and the property dialogue is extended with selector
     * for restrition values and printout options.
     * Without automatic AC the dialogue must be completed before the property
     * name has been entered.
     */
    completePropertyDialogue: function() {
        // check if the dialogue is already complete
        if (this.activeDialogue == "property" && $('input_c1')) return;
        // hr line
        node = document.createElement('hr');
        $('dialoguecontent').parentNode.parentNode.appendChild(node);
        // second table with checkbox for display option and value must be set
        node = document.createElement('table');
        var row = node.insertRow(-1);
        var cell = row.insertCell(0);
        cell.style.verticalAlign="top";
        cell.innerHTML = gLanguage.getMessage('QI_PROPERTYVALUE');
        cell = row.insertCell(1);
        var tmpHTML='<table><tr><td '
            + 'onmouseover="Tip(\'' + gLanguage.getMessage('QI_TT_SHOW_IN_RES') + '\')">'
            + '<input type="checkbox" id="input_c1" />'
            + ' </td><td> '
            + '<span onmouseover="Tip(\'' + gLanguage.getMessage('QI_TT_SHOW_IN_RES') + '\')">'
            + gLanguage.getMessage('QI_SHOW_PROPERTY')
            + '</span></td><td> </td></tr>'
            + '<tr id="input_c3d" style="display:none"><td> </td>'
            + '<td>' + gLanguage.getMessage('QI_COLUMN_LABEL') + ':</td>'
            + '<td><input type="text" id="input_c3"/></td></tr>'
            + '<tr id="input_c4d" style="display:none"><td> </td>'
            + '<td>' + gLanguage.getMessage('QI_SHOWUNIT') + ':</td>'
            + '<td><select id="input_c4"></select></td></tr>'
            + '<tr><td onmouseover="Tip(\'' + gLanguage.getMessage('QI_TT_MUST_BE_SET') + '\')">'
            + '<input type="checkbox" id="input_c2"/></td>'
            + '<td><span onmouseover="Tip(\'' + gLanguage.getMessage('QI_TT_MUST_BE_SET') + '\')">'
            + gLanguage.getMessage('QI_PROPERTY_MUST_BE_SET')
            + '</span></td><td> </td></tr></table>';
        cell.innerHTML = tmpHTML;
        $('dialoguecontent').parentNode.parentNode.appendChild(node);
        // add event handler when clicking the checkbox "show in result"
        if (this.activeQueryId == 0)
            $('input_c1').onclick = function() { qihelper.toggleShowProperty(); }
        else
            $('input_c1').disabled = "disabled";
            
        // hr line
        node = document.createElement('hr');
        $('dialoguecontent').parentNode.parentNode.appendChild(node);

        // property restriction table
        node = document.createElement('table');
        node.className = "propertyvalues";
        node.id = "dialoguecontent_pradio";
        row = node.insertRow(-1);
        cell = row.insertCell(-1);
        cell.setAttribute('style', 'border-botton: 1px solid #AAAAAA;');
        cell.innerHTML = gLanguage.getMessage('QI_PROP_VALUES_RESTRICT') + ': '
            + '<span onmouseover="Tip(\'' + gLanguage.getMessage('QI_TT_NO_RESTRICTION') + '\')">'
            + '<input type="radio" name="input_r0" value="-1" checked="checked" />' + gLanguage.getMessage('QI_NONE')
            + '</span>&nbsp;<span onmouseover="Tip(\'' + gLanguage.getMessage('QI_TT_VALUE_RESTRICTION') + '\')">'
            + '<input type="radio" name="input_r0" value="-2" />' + gLanguage.getMessage('QI_SPECIFIC_VALUE')
            + '</span>&nbsp;<span onmouseover="Tip(\'' + gLanguage.getMessage('QI_TT_SUBQUERY') + '\')">'
            + '<input type="radio" name="input_r0" value="'+this.nextQueryId+'" />'
            + '<span id="usesub">' + gLanguage.getMessage('QI_SUBQUERY') + '</span></span>&nbsp;';
        $('dialoguecontent').parentNode.parentNode.appendChild(node);
        node = document.createElement('table');
        node.style.display="none";
        node.id = "dialoguecontent_pvalues";
        $('dialoguecontent').parentNode.parentNode.appendChild(node);
        // add onclick handler for changing the value (IE won't accept onchange)
        var radiobuttons = $('dialoguecontent_pradio').getElementsByTagName('input');
        for (var i = 0; i < radiobuttons.length; i++)
            radiobuttons[i].onclick = function() { qihelper.setPropertyRestriction(); } 
    },

    /**
     * depending on the property type, another property can be added to a chain and
     * a subquery can be used for that property. This is only possible if the
     * current property is of the type page. Hence we must toggle:
     * - the link to add another property to a chain
     * - select the option subquery in the radio button
     */
    toggleSubqueryAddchain : function(op) {
        this.toggleSubquery(op);
        this.toggleAddchain(op);
    },

    /**
     * toggles the subquery radio button
     */
    toggleSubquery : function (op) {
        if (op) {
            try {
                $('usesub').className = "";
                document.getElementsByName('input_r0')[2].disabled = "";
            } catch (e) {};
        }
        else {
            try {
                $('usesub').className = "qiDisabled";
                document.getElementsByName('input_r0')[2].checked = false;
                document.getElementsByName('input_r0')[2].disabled = true;
            } catch (e) {};
        }
    },

    /**
     * toggles the add chain link
     */
    toggleAddchain : function(op) {
        if (!$('addchain')) return;
        if (op) {
            var msg = $('dialoguecontent').getElementsByTagName('input').length > 1
                ? gLanguage.getMessage('QI_ADD_PROPERTY_CHAIN')
                : gLanguage.getMessage('QI_CREATE_PROPERTY_CHAIN');
            $('addchain').innerHTML =
                '<a href="javascript:void(0)" '
                + 'onmouseover="Tip(\'' + gLanguage.getMessage('QI_TT_ADD_CHAIN') + '\')" '
                + 'onclick="tt_Hide(); qihelper.addPropertyChainInput()">'
                + msg + '</a>';
        }
        else {
            $('addchain').innerHTML = '';
        }
    },

    toggleShowProperty : function() {
        if ($('input_c1').checked) {
            $('input_c3d').style.display = (Prototype.Browser.IE) ? 'inline' : null;
            if ($('input_c4').getElementsByTagName('option').length > 0)
                $('input_c4d').style.display = (Prototype.Browser.IE) ? 'inline' : null;
        } else {
            $('input_c3d').style.display = 'none';
            $('input_c4d').style.display = 'none'
        }
    },

	/**
	 * Loads values of an existing category group. This happens if a users
	 * clicks on a category folder in the query tree.
	 * 
	 * @param id
	 *            id of the category group (saved with the query tree)
     * @param focus
     *            number of input field to set the focus
	 */
	loadCategoryDialogue : function(id, focus) {
		this.newCategoryDialogue(false);
		this.loadedFromId = id;
		var cats = this.activeQuery.getCategoryGroup(id); // get the category
															// group
		$('input0').value = unescapeQueryHTML(cats[0]);
		for ( var i = 1; i < cats.length; i++) {
			this.addDialogueInput();
			$('input' + i).value = unescapeQueryHTML(cats[i]);
		}
        if (focus) $('input' + focus).focus();
		$('qidelete').style.display = "inline"; // show delete button
	},

	/**
	 * Loads values of an existing instance group. This happens if a users
	 * clicks on an instance folder in the query tree.
	 * 
	 * @param id
	 *            id of the instace group (saved with the query tree)
     * @param focus
     *            number of input field to set the focus
	 */
	loadInstanceDialogue : function(id, focus) {
		this.newInstanceDialogue(false);
		this.loadedFromId = id;
		var ins = this.activeQuery.getInstanceGroup(id);
		$('input0').value = unescapeQueryHTML(ins[0]);
		for ( var i = 1; i < ins.length; i++) {
			this.addDialogueInput();
			$('input' + i).value = unescapeQueryHTML(ins[i]);
		}
        if (focus) $('input' + focus).focus();
		$('qidelete').style.display = "inline";
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
        var selector = prop.getSelector();
        this.propUnits = prop.getUnits();

		var propChain = unescapeQueryHTML(prop.getName()).split('.'); // fill input
                                                                    // filed with
                                                                    // name
        $('input_p0').value=propChain[0];
        for (var i = 1, n = propChain.length; i < n; i++) {
            $('dialoguecontent').rows[i * 2 - 1].cells[1].innerHTML =
                gLanguage.getMessage('QI_PROPERTY_TYPE') + ': ' +
                gLanguage.getMessage('QI_PAGE');
            this.addPropertyChainInput(propChain[i]);

        }
        this.propname = propChain[propChain.length - 1];
        this.completePropertyDialogue();
        // check box value must be set
        $('input_c2').checked = prop.mustBeSet();

        // set correct property type under last property input
        var typeRow = $('dialoguecontent').rows.length-2;
        if (this.proparity > 2) {
            $('dialoguecontent').rows[typeRow].cells[1].innerHTML =
                gLanguage.getMessage('QI_PROPERTY_TYPE') + ': ' +
                gLanguage.getMessage('QI_RECORD') ;
            this.toggleSubquery(false);
        } else {
            // get type of property, if it's a subquery then type is page
            this.propTypename = (selector >= 0) ? gLanguage.getMessage('QI_PAGE') : vals[0][0];
            $('dialoguecontent').rows[typeRow].cells[1].innerHTML =
                    gLanguage.getMessage('QI_PROPERTY_TYPE') + ': ' + this.propTypename;
            if (this.propTypename != gLanguage.getMessage('QI_PAGE'))
                this.toggleSubquery(false);
            else
                this.toggleAddchain(true);
        }

        // if we have a subquery set the selector correct and we are done
        if (selector >= 0) {
            document.getElementsByName('input_c1').disabled = "disabled";
            document.getElementsByName('input_r0')[2].checked = true;
            document.getElementsByName('input_r0')[2].value = selector;
            this.toggleAddchain(false);
        }
        else {
            if (this.activeQueryId == 0) {
                $('input_c1').checked = prop.isShown(); // check box if appropriate
                $('input_c3').value = prop.getColName();
                $('input_c3d').style.display= prop.isShown()
                    ? (Prototype.Browser.IE) ? 'inline' : null : 'none';
                if (prop.supportsUnits() && this.proparity == 2) {
                    $('input_c4').value = prop.getShowUnit();
                    var options = "";
                    for (var i = 0; i < this.propUnits[0].length; i++) {
                        options += '<option';
                        if (prop.getShowUnit() == this.propUnits[0][i])
                            options += ' selected="selected"';
                        options += '>' + this.propUnits[0][i] + '</option>';
                    }
                    $('input_c4').innerHTML=options;
                    $('input_c4d').style.display= prop.isShown()
                        ? Prototype.Browser.IE ? 'inline' : null : 'none';
                }
            } else {
                $('input_c1').disabled = "disabled";
            }
            // if the selector is set to "restict value" then make the restictions visible
            if (selector == -2) {
                document.getElementsByName('input_r0')[1].checked = true;
                $('dialoguecontent_pvalues').style.display = "inline";
            }
            // load enumeration values
            if (prop.isEnumeration()) {
                this.propIsEnum = true;
                this.enumValues = prop.getEnumValues();
            }
            var acChange=false;
            var rowOffset = 0;
            // if arity > 2 then add the first row under the radio buttons without input field
            if (this.proparity > 2) {
                var newrow = $('dialoguecontent_pvalues').insertRow(-1);
                var cell = newrow.insertCell(-1);
                cell.innerHTML = gLanguage.getMessage('QI_PROPERTYVALUE');
                rowOffset++;
            }
            for (var i = 0, n = vals.length; i < n; i++) {
                this.addRestrictionInput();
                var numType = false;
                var currRow = i + rowOffset;
                if (this.numTypes[vals[0][0].toLowerCase()]) // is it a numeric type?
                    numType = true;
                $('dialoguecontent_pvalues').rows[currRow].cells[1].innerHTML =
                    this.createRestrictionSelector(vals[i][1], false, numType);
                // deactivate autocompletion
                if (!acChange)
                    autoCompleter.deregisterAllInputs();
                acChange = true;
                $('dialoguecontent_pvalues').rows[currRow].cells[2].firstChild.className = "";
                
                // add unit selection, do this for all properties, even in subqueries
                try {
                    var propUnits = prop.getUnits();
                    var uIdx = (this.proparity == 2) ? 0 : i;
                    var tmpHTML = '';
                    for (var k = 0, m = propUnits[uIdx].length; k < m; k++) {
                        tmpHTML += '<option';
                        if (propUnits[uIdx][k] == vals[i][3])
                            tmpHTML += ' selected="selected"';
                        tmpHTML += '>'+ propUnits[uIdx][k] + '</option>';
                    }
                    $('dialoguecontent_pvalues').rows[currRow].cells[2]
                        .firstChild.nextSibling.innerHTML = tmpHTML;
                } catch(e) {};
                if (this.proparity > 2) {
                    $('dialoguecontent_pvalues').rows[currRow].cells[0].innerHTML= vals[i][0];
                    $('dialoguecontent_pvalues').rows[currRow].cells[0].style.fontWeight="normal";
                }
                $('input_r'+(i+1)).value = vals[i][2];
            }
            if (acChange) autoCompleter.registerAllInputs();
        }
		$('qidelete').style.display = "inline";
		
		if (!prop.isEnumeration()) this.restoreAutocompletionConstraints();
	},
	
	 restoreAutocompletionConstraints : function() {
         var idx = ($('dialoguecontent').rows.length -1) / 2 - 1;
         var propname = $('input_p'+idx).value;
         if (propname != "" && propname != this.propname) { // only if not empty
                                                            // and name changed
            this.propname = propname;
            if (this.pendingElement)
                this.pendingElement.remove();
            //this.pendingElement = new OBPendingIndicator($('input_r0'));
            //this.pendingElement.show();
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
                }
                
	                // Special treatment: binary properties support conjunction,
	                // therefore we need an "add" button
	                var idx = ($('dialoguecontent').rows.length -1) / 2 - 1;
                    var propertyName = $('input_p'+idx).value;
	                propertyName = gLanguage.getMessage('PROPERTY_NS')+propertyName.replace(/\s/g, '_');
	                var ac_constraint = "";
	                if (parameterTypes[0] == '_wpg') {
	                    ac_constraint = 'annotation-value: '+propertyName+'|namespace: 0';
	                }else if (parameterTypes[0] == '_dat') {
	                    ac_constraint = 'fixvalues: {{NOW}},{{TODAY}}|annotation-value: '+propertyName;
	                } else {
	                    ac_constraint = 'annotation-value: '+propertyName;
	                }
	                var i = 1;
	                while($('input_r'+i) != null) {
	                	$('input_r'+i).addClassName("wickEnabled");
	                	$('input_r'+i).setAttribute("constraints", ac_constraint);
	                	i++;
	                }
                
            }
    
    	autoCompleter.registerAllInputs();
    	//this.pendingElement.hide();
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
		this.updateTree();
		this.updateColumnPreview();
        this.updateBreadcrumbs(this.activeQueryId);
        this.updateQuerySource();

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

    selectNode : function(el, label) {
        // remove any other highlighed nodes
        var cells = $('treeanchor').getElementsByTagName('td');
        for (i = 0; i < cells.length; i++) {
            if (cells[i].style.backgroundColor) {
                cells[i].style.backgroundColor = null;
                for (j = 0; j < cells[i].childNodes.length; j++) {
                    if (cells[i].childNodes[j].style)
                        cells[i].childNodes[j].style.color= null;
                }
            }
        }
        // now mark the clicked cell as selected
        el.parentNode.style.backgroundColor='#1122FF';
        for (i = 0; i < el.parentNode.childNodes.length; i++) {
            if (el.parentNode.childNodes[i].style)
                el.parentNode.childNodes[i].style.color='#FFFFFF';
        }
        var vals = label.split('-');
        this.setActiveQuery(vals[1]);
        if (vals[0] == 'category')
            this.loadCategoryDialogue(vals[2], vals[3]);
        else if (vals[0] == 'instance')
            this.loadInstanceDialogue(vals[2], vals[3]);
        else
            this.loadPropertyDialogue(vals[2]);
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
		    if (! op) return;
			var selected = (op[0] == option) ? 'selected="selected"' : '';
            var esc_op = escapeXMLEntities(op[0]);
            html += '<option value="'+esc_op+'" '+selected+'>'+op[1] + ' ('+esc_op+')</option>';
		}
		if (numericType) {
			[ 
                ["=", gLanguage.getMessage('QI_EQUAL') ],
                [">=", gLanguage.getMessage('QI_GT') ],
                ["<=", gLanguage.getMessage('QI_LT') ],
                ["!", gLanguage.getMessage('QI_NOT') ],
                ["~", gLanguage.getMessage('QI_LIKE') ],
            ].each(optionsFunc);
		} else {
			[
                ["=", gLanguage.getMessage('QI_EQUAL') ],
                ["!", gLanguage.getMessage('QI_NOT') ],
                ["~", gLanguage.getMessage('QI_LIKE') ],
            ].each(optionsFunc);
		}
		
		return html + "</select>";
	},

    /**
     * get the value of the selector whether to define a property value
     * or add a subquery to the property. If the subproperty option
     * was disabled but checked, then return -1 (no restriction set)
     */
    getPropertyValueSelector : function() {
        var radio = document.getElementsByName('input_r0');
        var val;
        if (radio.length == 0) return;
        for (var i = 0; i < radio.length; i++) {
            if (radio[i].checked) {
                val = parseInt(radio[i].value);
                break;
            }
        }
        if (val == null || val > -1 && radio[2].disabled)
            return -1;
        return val;
    },
	
	/**
	 * Adds a new Category/Instance/Property Group to the query
	 */
	add : function() {
		if (this.activeDialogue == "property")
            this.addPropertyGroup();
        else
			this.addCatInstGroup();
		this.updateTree();
		this.updatePreview();
        this.updateQuerySource();
        this.updateBreadcrumbs(this.activeQueryId);
		this.loadedFromID = null;
	},

	/**
	 * Reads the input fields of a category or instance dialogue and adds them
     * to the query.
	 */
	addCatInstGroup : function() {
		var tmp = Array();
		var allinputs = true; // checks if all inputs are set for error
								// message
        var inputs = $('dialoguecontent').getElementsByTagName('input');
		for ( var i = 0; i < inputs.length; i++) {
			if (inputs[i].id && inputs[i].id.match(/^input\d+$/))
                tmp.push(escapeQueryHTML(inputs[i].value));
			if (inputs[i].value == "")
				allinputs = false;
		}
		if (!allinputs) { // show error
			$('qistatus').innerHTML = (this.activeDialogue == "category")
                ? gLanguage.getMessage('QI_ENTER_CATEGORY')
                : gLanguage.getMessage('QI_ENTER_INSTANCE');
            this.updateHeightBoxcontent();
        }
		else {
			/* STARTLOG */
			if (window.smwhgLogger) {
				var logstr = "Add " + this.activeDialogue + " " + tmp.join(",") + " to query";
				smwhgLogger.log(logstr, "QI",
                    (this.activeDialogue == "category")
                        ? "query_category_added" : "query_instance_added");
			}
			/* ENDLOG */
            // add to query
            if (this.activeDialogue == "category") {
                this.activeQuery.addCategoryGroup(tmp, this.loadedFromId);
                this.emptyDialogue();
                $('qistatus').innerHTML = gLanguage.getMessage('QI_CAT_ADDED_SUCCESSFUL');
            }
            else {
                this.activeQuery.addInstanceGroup(tmp, this.loadedFromId);
                this.emptyDialogue();
                $('qistatus').innerHTML = gLanguage.getMessage('QI_INST_ADDED_SUCCESSFUL');
            }
                        			
		}
	},

	/**
	 * Reads the input fields of a property dialogue and adds them to the query
	 */
	addPropertyGroup : function() {
        // check if user clicked on add, while prop information is not yet loaded.
        if (!$('input_c1')) return;
		var pname='';
        var propInputFields = $('dialoguecontent').getElementsByTagName('input');
        for (var i = 0, n = propInputFields.length; i < n; i++) {
            pname += propInputFields[i].value + '.';
        }
        pname = pname.replace(/\.$/,'');
		var subqueryIds = Array();
		if (pname == "") { // no name entered?
			$('qistatus').innerHTML = gLanguage
					.getMessage('QI_ENTER_PROPERTY_NAME');
		} else {
			var pshow = $('input_c1').checked; // show in results?
            // when show in results is checked, add label and unit if they exist
            var colName = (pshow) ? $('input_c3').value : null;
            var showUnit = (pshow) ? $('input_c4').value : null;
			var pmust = $('input_c2').checked; // value must be set?
			var arity = this.proparity;
            var selector = this.getPropertyValueSelector();
            // create propertyGroup
			var pgroup = new PropertyGroup(escapeQueryHTML(pname), arity,
					pshow, pmust, this.propIsEnum, this.enumValues, selector, showUnit, colName);
            pgroup.setUnits(this.propUnits);
			var allValueRows = $('dialoguecontent_pvalues').rows.length;
            // there is no value restriction
            if (selector != -2) {
                var paramname = $('dialoguecontent').rows[$('dialoguecontent').rows.length -2].cells[1].innerHTML;
                paramname = paramname.replace(gLanguage.getMessage('QI_PROPERTY_TYPE') + ': ', '');
                // no subquery, so add a dumy value
                if (selector == -1) {
                    if (arity == 2)
                        pgroup.addValue(paramname, '=', '*');
                    else {
                        for (s = 1; s < arity; s++) {
                            pgroup.addValue($('dialoguecontent_pvalues').rows[s].cells[0].innerHTML, '=', '*');
                        }
                    }
                }
                else {
					if (selector < this.nextQueryId) // Subquery does exists
														// already
						paramvalue = selector;
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
                    pgroup.addValue('subquery', '=', paramvalue);
                }
            } else {
			for ( var i = 0; i < allValueRows; i++) {
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
                    // works on normal input fiels as well as on selection lists
					var paramvalue = $('dialoguecontent_pvalues').rows[i].cells[2].firstChild.value;
                } catch (e) {continue;}
                // no value is replaced by "*" which means all values
				paramvalue = paramvalue == "" ? "*" : paramvalue; 
				var paramname;
                if (arity == 2) {
                    paramname = $('dialoguecontent').rows[$('dialoguecontent').rows.length -2].cells[1].innerHTML;
                    paramname = paramname.replace(gLanguage.getMessage('QI_PROPERTY_TYPE') + ': ', '');
                }
                else {
                    paramname = $('dialoguecontent_pvalues').rows[i].cells[0].innerHTML;
                }
				var restriction = $('dialoguecontent_pvalues').rows[i].cells[1].firstChild.value;
                var unit = null;
                try {
                    unit = $('dialoguecontent_pvalues').rows[i].cells[2].firstChild.nextSibling.value;
                } catch (e) {};
                // add a value group to the property group
				pgroup.addValue(paramname, restriction, escapeQueryHTML(paramvalue), unit);
			}
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
            $('qistatus').innerHTML = gLanguage.getMessage('QI_PROP_ADDED_SUCCESSFUL')
		}
	},

    switchTab : function(id, flush) {
        var divcontainer = ['treeview', '', 'qisource'];
        if (!flush) {
            // user selected the source tab, convert query to source code
            if (id == 3) {
                this.showFullAsk('parser', false);
                $('query4DiscardChanges').innerHTML = escapeQueryHTML($('fullAskText').value);
            }
            // user selected the tree tab, load the query from source
            else {
                this.loadFromSource();
                $('query4DiscardChanges').innerHTML = "";
            }
        }
        for (var i = 0; i < divcontainer.length; i++) {
            if (divcontainer[i].length == 0) continue;
            if (id == i+1) {
                $('qiDefTab' + (i+1)).className='qiDefTabActive';
                $(divcontainer[i]).style.display='inline';
            } else {
                $('qiDefTab' + (i+1)).className='qiDefTabInactive';
                $(divcontainer[i]).style.display='none';
            }
        }
    },

    discardChangesOfSource : function() {
        $('fullAskText').value =   $('query4DiscardChanges').innerHTML.unescapeHTML();
        this.sourceChanged=1;
        this.loadFromSource(true);
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
				$('fullAskText').value = '';
			return;
		} else if (($('layout_format').value == "template")
				&& ($('template_name').value == "")) {
			$('fullAskText').value = '';
            alert(gLanguage.getMessage('QI_EMPTY_TEMPLATE'));
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
				this.pendingElement.remove();
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
        this.updateQuerySource();
		this.updatePreview();

	},

    /**
     * called when the Query Tree tab is clicked and the Query source tab is still active
     */
    loadFromSource : function(noTabSwitch) {
        this.noTabSwitch = noTabSwitch;
        if ($('qiDefTab3').className.indexOf('qiDefTabActive') > -1 &&
            $('fullAskText').value.length > 0 &&
            this.sourceChanged)
                this.initFromQueryString($('fullAskText').value);
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
			var props = sub[i].match(/\[\[([\w\d _\.]*)::.*?\]\]/g);
			if (!props)
				props = [];
			for ( var j = 0; j < props.length; j++) {
				var pname = escapeQueryHTML(props[j].substring(2, props[j]
						.indexOf('::')));
                var pchain = pname.split('.');
                for (var c = 0; c < pchain.length; c++) {
                    if (!propertiesInQuery.inArray(pchain[c]))
                        propertiesInQuery.push(pchain[c]);
                }
			}
		}
		// check all properties that exist in parameter "must show" only (like |
		// ?myproperty)
		var props = sub[0].split('|');
		for ( var i = 1; i < props.length; i++) {
			if (props[i].match(/^\s*\?/)) {
				var pname = props[i].substring(props[i].indexOf('?') + 1,
						props[i].length);
                pname = pname.replace(/^([^#|=]*).*/, "$1");
				pname = escapeQueryHTML(pname.replace(/\s*$/,''));
                var pchain = pname.split('.');
                for (var c = 0; c < pchain.length; c++) {
    				if (!propertiesInQuery.inArray(pchain[c]))
        				propertiesInQuery.push(pchain[c]);
                }
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
                var noval = new Array();
                var enumValues = [];
                var unitVals = new Array();
                if (arity == 2) {
    				noval = prop.item(i).getElementsByTagName('allowedValue');
                	for ( var j = 0; j < noval.length; j++) {
                    	enumValues.push(noval.item(j).getAttribute('value'));
                    }
                    // fill the units array
                    var units = prop.item(i).getElementsByTagName('unit');
                    var uvals = new Array();
                    for ( var j = 0; j < units.length; j++) {
                        uvals.push(units.item(j).getAttribute('label'));
                    }
                    if (uvals.length > 0) {
                        unitVals.push(uvals);
                        if (!this.numTypes[pname.toLowerCase()])
                            this.numTypes[pname.toLowerCase()]= true;
                    }
                }
                var isEnum = noval.length > 0 ? true : false;
				var pgroup = new PropertyGroup(pname, arity, false, false,
						isEnum, enumValues);
                // Nary property
                if (arity > 2) {
                    var naryParams = prop.item(i).getElementsByTagName('param');
                    for (k = 0; k < naryParams.length; k++) {
                        // enumerations
                        var enumValNodes = naryParams.item(k).getElementsByTagName('allowedValue');
                        var enumVals = [];
                        for (m = 0; m < enumValNodes.length; m++ )
                            enumVals[m] = enumValNodes.item(m).getAttribute('value');
                        pgroup.addValue(naryParams.item(k).getAttribute('name'), null, enumVals);
                        // units
                        var units = naryParams.item(k).getElementsByTagName('unit');
                        var uvals = new Array();
                        for ( var m = 0; m < units.length; m++)
                            uvals.push(units.item(m).getAttribute('label'));
                        unitVals.push(uvals);
                    }
                }
                pgroup.setUnits(unitVals);
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
	for (f = 0; f < sub.length; f++) {
		// set current query to active, do this manually (treeview is not
		// updated)
		this.activeQuery = this.queries[f];
		this.activeQueryId = f;
		// extact the arguments, i.e. all between [[...]]
		var args = sub[f].split(/\]\]\s*\[\[/);
		// remove the ]] from the last element
		args[args.length - 1] = args[args.length - 1].substring(0,
				args[args.length - 1].indexOf(']]'));
		// and [[ from the first element
		args[0] = args[0].replace(/^\s*\[\[/, '');
		this.handleQueryString(args, f, pMustShow);
	}
	this.setActiveQuery(0); // set main query to active
    this.updateTree();      // show new tree
	this.updatePreview(); // update result preview
},

handleQueryString : function(args, queryId, pMustShow) {

	// list of properties (each property has an own pgoup)
	var propList = new PropertyList();

	for ( var i = 0; i < args.length; i++) {
		// Category
		if ( args[i].indexOf( gLanguage.getMessage('CATEGORY')) == 0 ||
                     args[i].indexOf( 'Category:') == 0 ) {
			var vals = args[i].substring(args[i].indexOf(':') + 1).split(/\s*\|\|\s*/);
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
            var pchains = pname.split('.');
            pname = pchains[pchains.length - 1];

			// if the property was already once in the arguments, we already
			// have details about the property
        	// get property data from definitions
			var propdef = this.propertyTypesList.getPgroup(pname);
			// show in results? if queryId == 0 then this is the main query
			// and we check the params
			var pshow = false;
            var unit = null;
            var column = null;
            // in main query, check if property is on the list of props to show
            if (queryId == 0) {
                for (var j = 0; j < pMustShow.length; j++) {
                    if (pMustShow[j][0] == pname) {
                        pshow = true;
                        unit = pMustShow[j][1];
                        column = pMustShow[j][2];
                        break;
                    }
                }
            }
			// must be set?
			var pmust = args.inArray(pname + '::+');
			var arity = propdef ? propdef.getArity() : 2;
			var isEnum = propdef ? propdef.isEnumeration() : false;
			var enumValues = propdef ? propdef.getEnumValues() : [];
            // create propertyGroup
			var pgroup = new PropertyGroup(escapeQueryHTML(pname), arity,
                	pshow, pmust, isEnum, enumValues, null, unit, column);
            if (arity > 2) {
                var naryVals = propdef.getValues();
                for (e = 0; e < naryVals.length; e++)
                    pgroup.addValue(naryVals[e][0], naryVals[e][1], naryVals[e][2]);
            }
            pgroup.setUnits(propdef.getUnits());

			var subqueryIds = propList.getSubqueryIds(propList.getIndex(pname));
            if (!subqueryIds) subqueryIds = new Array();
			var paramname = this.propertyTypesList && this.propertyTypesList.getType(pname)
                            ? this.propertyTypesList.getType(pname)
                            : gLanguage.getMessage('QI_PAGE');
			var paramvalue; // initialize param value, the actual value is assigned below
			var restriction = '=';

			// Check if the value contains a Subquery
			if (pval.match(/___q\d+q___/)) {
				paramname = "subquery";
				paramvalue = parseInt(pval.replace(/___q(\d+)q___/, '$1'));
				this.insertQuery(paramvalue, queryId, pname);
				subqueryIds.push(paramvalue);
                // add a value group to the property group
				pgroup.addValue(paramname, restriction, paramvalue);
                // set the selector to subquery id
                pgroup.setSelector(paramvalue);
			} else { // no subquery contained, proceed normaly

                // if the value is + then the property is a "must have a value"
                // if this is the only "value" for this property, then set it with
                // page/type = *. Otherwise the value is explicitly set in another
                // run of the loop, so skip this + value
                var skipMustShow = false;
                if (pval == "+") {
                    for (var k = 0; k < args.length; k++) {
                        // check if poperty exists again in query but with other value
                        if (args[k].indexOf(pname) != -1 && args[k] != pname + '::+')
                            skipMustShow = true;
                    }
                }
                if (skipMustShow) continue;
                // set selector to special value choosen for property
                pgroup.setSelector(-2);
                // special handling for nary properties
                if (pgroup.getArity() > 2) {
                    var vals = pval.split(/\s*;\s*/);
                    var valsDef = pgroup.getValues();
                    pgroup.setValues();
                    // empty values for all record fields
                    if (pval == "+") {
                        vals[0] = "";
                        pgroup.setSelector(-1);
                    }
                    // uncomplete values for record fields
                    if (vals.length < valsDef.length) {
                        for (j = vals.length; j < valsDef.length; j++)
                            vals.push('');
                    }
                    for (j = 0; j < vals.length; j++) {
                        // check for restricion (makes sence for numeric properties)
        				var op = vals[j].match(/^([\!|<|>|~]?=?)(.*)/);
            			if (op[1].length > 0) {
                			restriction = op[1].indexOf('=') == -1
                                ? op[1] + '='
                        		: op[1];
                            paramvalue = op[2];
                        }
                        else {
                            paramvalue = vals[j];
                            restriction = '=';
                        }
                        // check for a unit
                        var paramunit;
                        if (this.propertyTypesList.supportsUnits(pname)) {
                            op = paramvalue.match(/^\s*(\d+(\.\d+)?)(.*?)$/);
                            if (op) {
                                paramvalue = op[1];
                                paramunit = op[3].replace(/^\s+|\s+$/g, '');
                            }
                        }
                        // add a value group to the property group
                        pgroup.addValue(
                            valsDef.length > j ? valsDef[j][0] : gLanguage.getMessage('QI_PAGE'),
                            restriction,
                            escapeQueryHTML(paramvalue), escapeQueryHTML(paramunit)
                        );
                    }
                }
                else {
                    // normal property
                    // split values by || "or" conjunction
                    if (pval == '+') pgroup.setSelector(-1);
        			var vals = pval.split(/\s*\|\|\s*/);
            		for ( var j = 0; j < vals.length; j++) {
                        // check for restricion (makes sence for numeric properties)
                    	var op = vals[j].match(/^([\!|<|>]?=?)(.*)/);
                        if (op[1].length > 0) {
                            restriction = op[1].indexOf('=') == -1
                                ? op[1] + '='
                                : op[1];
                            paramvalue = op[2];
                        }
                        else
                            paramvalue = vals[j];
                        // no value or '+' (must set) is replaced, by "*" which means all values
                        if (paramvalue == "" || paramvalue == '+') paramvalue = "*";
                        // if j > 0 conjunction: page/type = val1 'or' valX
                        if (j > 0) paramname = gLanguage.getMessage('QI_OR');
                        // check for a unit
                        var paramunit;
                        if (this.propertyTypesList.supportsUnits(pname)) {
                            op = paramvalue.match(/^\s*(\d+(\.\d+)?)(.*?)$/);
                            if (op) {
                                paramvalue = op[1];
                                paramunit = op[3].replace(/^\s+|\s+$/g, '');
                            }
                        }
                        // add a value group to the property group
                        pgroup.addValue(paramname, restriction,
                            	escapeQueryHTML(paramvalue), escapeQueryHTML(paramunit));
                    }
                }
			}
			propList.addNew(pname, pgroup, subqueryIds); // add current property to property list
		}
	}

	// if a property must be shown in results only, it may not appear in the
	// [[...]] part but only as |?myprop in the printout
	// therefore check now that in the main query we also have all "must show"
	// properties included
	if (queryId == 0) { // do this only for the main query, subqueries have no printouts
		for ( var i = 0; i < pMustShow.length; i++) { // loop over all properties to show
			if (propList.getPgroup(pMustShow[i][0]) == null) { // property does not exist yet
                // create property grou with default values
                var pgroup = new PropertyGroup(escapeQueryHTML(pMustShow[i][0]),
						2, true, false, null, null, -1,
                        pMustShow[i][1], pMustShow[i][2]);
                var defPgroup = this.propertyTypesList.getPgroup(pMustShow[i][0]);
                var ptype = gLanguage.getMessage('QI_PAGE');
                // use the definition, like enum values and arity from the ajax
                // call when querying the property types
                if (defPgroup) {
                    ptype = this.propertyTypesList.getType(pMustShow[i][0]);
                    pgroup = new PropertyGroup(escapeQueryHTML(pMustShow[i][0]),
                        defPgroup.getArity(), true, false,
                        defPgroup.isEnumeration(), defPgroup.getEnumValues(), -1,
                        pMustShow[i][1], pMustShow[i][2]);
                    pgroup.setUnits(defPgroup.getUnits());
                }
				pgroup.addValue(ptype, '=', '*'); // add default values
                // add current property to property list
				propList.add(pMustShow[i][0], pgroup, [], ptype);
			}
		}
	}

	// we are done with all agruments, now add the collected property
	// information to the active query
	for (i = 0; i < propList.length; i++) {
		var pgroup = propList.getPgroupById(i);
		var subqueryIds = propList.getSubqueryIds(i);
		this.activeQuery.addPropertyGroup(pgroup, subqueryIds);
	}

},

/**
 * Check the main query and all options for properties that must be shown in
 * the result. The returned array contains triples of properties with
 * array(propname, unit, column text)
 */
applyOptionParams : function(query) {
	var options = query.split('|');
	// parameters to show
    var mustShow = [];
	// get printout format of query
	var format = "table"; // default format
	for ( var i = 1; i < options.length; i++) {
            // check for additionl printouts like |?myProp
            var m = options[i].match(/^\s*\?/)
            if (m) {
                m = options[i].replace(/\n/g,'').match(/^([^#|=]*)(#[^=]*)?(=.*?)?$/);
                var pname = m[1].replace(/^\s*\?/, '').replace(/\s*$/,'');
                var punit = (m[2]) ? m[2].replace(/#/,'').replace(/\s*$/,'') : null;
                var col = (m[3]) ? m[3].replace(/=\s*/,'').replace(/\s*$/,'') : null;
                mustShow.push([pname, punit, col]);
                continue;
            }
            // check for key value pairs like format=table
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
			    if (typeof optionParameter.type != 'undefined' &&
                    optionParameter.type.toLowerCase() == 'checkbox') {
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

	// store here all queries (sub[0] is the main query)
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

	initialize : function(name, arity, show, must, isEnum, enumValues, selector, showUnit, colName) {
		this.name = name;
		this.arity = arity;
		this.show = show;
		this.must = must;
		this.isEnum = isEnum;
		this.enumValues = enumValues;
        this.selector = selector;
        this.showUnit = showUnit;
        this.colName = colName;
		this.values = Array(); // paramName, retriction, paramValue, unitOfvalue
        this.units = Array();
},

addValue : function(name, restriction, value, unit) {
	this.values[this.values.length] = new Array(name, restriction, value, unit);
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

setValues : function(vals) {
    this.values = (!vals) ? new Array() : vals;
},

setUnits : function(vals) {
    this.units = (!vals) ? new Array() : vals;
},
setSelector : function(val) {
    this.selector = val;
},

supportsUnits : function() {
    return (this.units.length > 0 && this.units[0].length > 0) ? true : false;
},
getUnits : function () {
    return this.units;
},

isEnumeration : function() {
	return this.isEnum;
},

getEnumValues : function() {
	return this.enumValues;
},
getSelector : function() {
    return this.selector;
},
getShowUnit : function() {
    return this.showUnit;
},
getColName : function() {
    return (this.colName) ? this.colName : "";
}
}

var PropertyList = Class.create();
PropertyList.prototype = {

	initialize : function() {
		this.name = Array();
		this.pgroup = Array();
		this.subqueries = Array();
		this.type = Array();
		this.length = 0;
	},

	add : function(name, pgroup, subqueries, type) {
		for ( var i = 0; i < this.name.length; i++) {
			if (this.name[i] == name) {
				this.pgroup[i] = pgroup;
				this.subqueries[i] = (subqueries) ? subqueries : [];
				this.type[i] = type;
				return;
			}
		}
		this.addNew(name, pgroup, subqueries, type);
	},
    addNew : function(name, pgroup, subqueries, type) {
		this.name.push(name);
		this.pgroup.push(pgroup);
		this.subqueries.push((subqueries) ? subqueries : []);
		this.type.push(type);
        this.length++;
    },

	getPgroup : function(name) {
		for ( var i = 0; i < this.name.length; i++) {
			if (this.name[i] == name)
				return this.pgroup[i];
		}
		return;
	},
    
    getPgroupById : function(i) {
        if (this.length > i)
    		return this.pgroup[i];
		return;
	},

	getSubqueryIds : function(i) {
        if (this.length > i)
            return this.subqueries[i];
        return new Array();
	},

	getIndex : function(name) {
		for ( var i = 0; i < this.name.length; i++) {
			if (this.name[i] == name)
				return i;
		}
		return -1;
	},

	getType : function(name) {
		for ( var i = 0; i < this.name.length; i++) {
			if (this.name[i] == name)
				return this.type[i];
		}
	},

    supportsUnits : function(name) {
		for ( var i = 0; i < this.name.length; i++) {
			if (this.name[i] == name)
				return this.pgroup[i].supportsUnits();
		}
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
    if (! string) return "";
	string = ("" + string).escapeHTML();
	string = string.replace(/\"/g, "&quot;");
	return string;
}

function unescapeQueryHTML(string) {
    if (! string) return "";
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

