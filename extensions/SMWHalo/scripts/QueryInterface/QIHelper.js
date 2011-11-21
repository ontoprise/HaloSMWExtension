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


window.qihelper = null;
var QIHelperSavedQuery;
var qiPreviewDialog;

var QIHelper = Class.create();
QIHelper.prototype = {

  /**
	 * Initialize the QIHelper object and all variables
	 */
  initialize : function() {
    this.imgpath = wgScriptPath + '/extensions/SMWHalo/skins/QueryInterface/images/';
    this.divQiDefTabHeight = 300;
    this.divPreviewcontentHeight = 160;
    if (! this.numTypes ) { // get them only once.
      this.numTypes = new Array();
      this.getNumericDatatypes();
    }
    this.queries = Array();
    this.activeQuery = null;
    this.activeQueryId = null;
    this.nextQueryId = 0;
    this.activeInputs = 0;
    this.activeDialogue = null;
    this.propname = null;
    this.proparity = null;
    this.propIsEnum = false;
    this.propRange = new Array();
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
    this.sortColumn = null;
    this.DS_SELECTED = 0;
    this.TPEE_SELECTED = 1;
    this.propertyAddClicked = false;
    this.colNameEntered = false;

    var qiStatus = $$('#askQI #qistatus')[0];
    if(qiStatus)
      qiStatus.innerHTML = gLanguage.getMessage('QI_START_CREATING_QUERY');
    if (! this.noTabSwitch)
      this.switchTab(1, true);
    this.sourceChanged = 0;
    // if triplestore is enabled in wiki, the <input id="usetriplestore"> exists
    var useTripleStore = $$('#askQI #usetriplestore')[0];
    if (useTripleStore)
      Event.observe(useTripleStore,'click', this.resetTscOptions.bind(this));
    if (! this.queryList)
      this.queryList = new QIList();
    
    this.enableResetQueryButton();
    jQuery('#qiDialogButtonAdd').click(function(){
      qihelper.enableResetQueryButton();
    });
    jQuery('#qidelete').click(function(){
      qihelper.enableResetQueryButton();
    });
    jQuery('#qiLoadFromSourceButton').click(function(){
      qihelper.enableResetQueryButton();
    });
    jQuery('#qiDiscardChangesButton').click(function(){
      qihelper.enableResetQueryButton();
    });


  },

  enableResetQueryButton: function(forceDisable){
    if(forceDisable){
      jQuery('#askQI #qiResetQueryButton').attr('disabled', 'disabled');
    }
    else{
      //if query source or query tree is not empty enable "reset query" button
      if(jQuery('#fullAskText').val().length || jQuery('#treeanchor').children().length){
        jQuery('#askQI #qiResetQueryButton').removeAttr('disabled');
      }
      else{
        jQuery('#askQI #qiResetQueryButton').attr('disabled', 'disabled');
      }
    }
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
    var layoutContent = $$("#askQI #layoutcontent")[0];
    if(layoutContent){
      if (layoutContent.style.display == "none") {
        layoutContent.style.display = "";
        $$("#askQI #queryprinteroptions")[0].style.display = "";
        $$("#askQI #layouttitle-link")[0].removeClassName("plusminus");
        $$("#askQI #layouttitle-link")[0].addClassName("minusplus");
        this.getSpecialQPParameters($$('#askQI #layout_format')[0].value);
      } else {
        $$("#askQI #layoutcontent")[0].style.display = "none";
        $$("#askQI #queryprinteroptions")[0].style.display = "none";
        $$("#askQI #layouttitle-link")[0].removeClassName("minusplus");
        $$("#askQI #layouttitle-link")[0].addClassName("plusminus");
      }
    }
  },

  switchDefinition : function() {
    var qiQueryDefinition = $$('#askQI #qiquerydefinition')[0];
    if(qiQueryDefinition){
      if (qiQueryDefinition.style.display == "none") {
        qiQueryDefinition.style.display = "";
        $$('#askQI #definitiontitle-link')[0].removeClassName("plusminus");
        $$('#askQI #definitiontitle-link')[0].addClassName("minusplus");
        $$('#askQI #previewcontent')[0].style.height = this.divPreviewcontentHeight + 'px';
      } else {
        qiQueryDefinition.style.display = "none";
        $$('#askQI #definitiontitle-link')[0].removeClassName("minusplus");
        $$('#askQI #definitiontitle-link')[0].addClassName("plusminus");
        $$('#askQI #previewcontent')[0].style.height = (this.divQiDefTabHeight + this.divPreviewcontentHeight) + 'px';
      }
    }
  },

  switchResult : function() {
    if ($$('#askQI #qiresultcontent')[0].style.display == "none") {
      $$('#askQI #qiresultcontent')[0].style.display = "";
      $$('#askQI #qiresulttitle-link')[0].removeClassName("plusminus");
      $$('#askQI #qiresulttitle-link')[0].addClassName("minusplus");
      this.updatePreview();
    }else {
      $$('#askQI #qiresultcontent')[0].style.display = "none";
      $$('#askQI #qiresulttitle-link')[0].removeClassName("minusplus");
      $$('#askQI #qiresulttitle-link')[0].addClassName("plusminus");
    }
  },

  switchOption : function() {
    if ($$('#askQI #qioptioncontent')[0].style.display == "none") {
      $$('#askQI #qioptioncontent')[0].style.display = "";
      $$('#askQI #qioptiontitle-link')[0].removeClassName("plusminus");
      $$('#askQI #qioptiontitle-link')[0].addClassName("minusplus");
      this.updatePreview();
    }else {
      $$('#askQI #qioptioncontent')[0].style.display = "none";
      $$('#askQI #qioptiontitle-link')[0].removeClassName("minusplus");
      $$('#askQI #qioptiontitle-link')[0].addClassName("plusminus");
    }
  },

  resetSelection : function(selector, defaultVal) {
    if (defaultVal == null) defaultVal = -1;
    if (selector) {
      for (var i=0; i < selector.options.length; i++) {
        if (i == defaultVal) selector.options[i].selected='selected'
        else selector.options[i].selected=null;
      }
    }
  },

  resetTscOptions : function() {
    // check for linked data options and reset these
    this.resetSelection( $$('#askQI #qidatasourceselector')[0], 0);
    if ( $$('#askQI #qio_showrating')[0] != null )
      $$('#askQI #qio_showrating')[0].checked = null;
    if ( $$('#askQI #qio_showmetadata')[0] != null )
      $$('#askQI #qio_showmetadata')[0].checked = null;
    if ( $$('#askQI #qio_showdatasource_div')[0] )
      $$('#askQI #qio_showdatasource_div')[0].style.display = "none";
    //this.resetSelection( $('qitpeeselector'), 0);
    this.updateSrcAndPreview();
    // check if the TSC has been disabled or enabled and hide DS and TPEE box
    if ($$('#askQI #usetriplestore')[0].checked)
      this.selectDsTpee(this.DS_SELECTED);
    else
      this.selectDsTpee(-1);
  },

  clickUseTsc : function () {
    if ($$('#askQI #usetriplestore')[0]) {
      $$('#askQI #usetriplestore')[0].checked="checked";
      this.updateSrcAndPreview();
    }
  },
  clickMetadata : function () {
    if ( $$('#askQI #qio_showmetadata')[0] && $$('#askQI #qio_showmetadata')[0].checked )
      $$('#askQI #qio_showdatasource_div')[0].style.display = "block";
    else
      $$('#askQI #qio_showdatasource_div')[0].style.display = "none";
    this.clickUseTsc();
  },
  clickTpee : function () {
    for ( var i = 0; i < $$('#askQI #qitpeeselector')[0].options.length; i++ ) {
      var div = 'qitpeeparams_' + $$('#askQI #qitpeeselector')[0].options[i].value;
      $(div).style.display = 'none';
      if ($$('#askQI #qitpeeselector')[0].options[i].selected)
        $(div).style.display = '';
    }
    this.clickUseTsc();
  },
  selectDsTpee : function (val, noupdate) {
    // no datasources or trust policy in use
    if (! ( $$('#askQI #qiTpeeSelected')[0] && $$('#askQI #qiDsSelected')[0] ) ) return;
    if (! $$('#askQI #qiTpeeSelected')[0] ) { // only datasources are in use
      $$('#askQI #qiDsSelected')[0].style.display = ( val == -1 ) ? 'none' : '';
      return;
    }
    var radio = $$('#askQI #qioptioncontent')[0].getElementsBySelector('[name="qiDsTpeeSelector"]');
    if (val == this.TPEE_SELECTED) {
      $$('#askQI #qiDsSelected')[0].style.display = 'none';
      $$('#askQI #qiTpeeSelected')[0].style.display = '';
      radio[1].checked = 'checked';
      if (! noupdate)
        this.clickUseTsc();
    }
    else if (val == this.DS_SELECTED) {
      $$('#askQI #qiDsSelected')[0].style.display = '';
      $$('#askQI #qiTpeeSelected')[0].style.display = 'none';
      radio[0].checked = 'checked';
      if (! noupdate)
        this.clickUseTsc();
    }
    else {
      $$('#askQI #qiDsSelected')[0].style.display = 'none';
      $$('#askQI #qiTpeeSelected')[0].style.display = 'none';
      radio[0].checked = null;
      radio[1].checked = null;
      $$('#askQI #usetriplestore')[0].checked=null;
      if (! noupdate )
        this.updateSrcAndPreview();
    }
  },
  tpeeOrder : function (move) {
    var policy_id = '';
    for (var i = 0; i < $$('#askQI #qitpeeselector')[0].options.length; i++) {
      if ($$('#askQI #qitpeeselector')[0].options[i].selected) {
        policy_id = $$('#askQI #qitpeeselector')[0].options[i].value;
        break;
      }
    }
    if (policy_id.length == 0) return;
    var table = $('qitpeeparamval_' + policy_id + '_PAR_ORDER');
    if (! table) return;
    for (var i = 0; i < table.rows.length; i++) {
      if (table.rows[i].cells[0].className == 'qiTpeeSelected') {
        var newRow = -1;
        if (move == 'up' && i > 0)
          newRow = i - 1;
        if (move == 'down' && i < table.rows.length -1)
          newRow = i + 1;
        if (newRow == -1) break;
        var html = table.rows[i].cells[0].innerHTML;
        var sourceId = table.rows[i].cells[0].getAttribute('_sourceid');
        table.deleteRow(i);
        var row = table.insertRow(newRow);
        var cell = row.insertCell(0);
        cell.className = 'qiTpeeSelected';
        cell.setAttribute('_sourceid', sourceId);
        cell.innerHTML = html;
        break;
      }
    }
    this.clickUseTsc(); // updates source and preview
  },
  tpeeOrderSelect : function (el) {
    var table = el.parentNode;
    while (table.nodeName.toUpperCase() != 'TABLE')
      table = table.parentNode;
    for (var i = 0; i < table.rows.length; i++) {
      table.rows[i].cells[0].className = null;
    }
    el.className="qiTpeeSelected";
  },
    
  /**
	 * Called whenever preview result printer needs to be updated.
   * This is only done, if the results are visible.
	 */
  updatePreview : function() {
    // update result preview
    if ($$("#askQI #previewcontent")[0].style.display == "" &&
      $$("#askQI #qiresultcontent")[0].style.display == "") {
      this.previewResultPrinter();
    }
  },

  updateQuerySource : function() {
    // if query source tab is active
    if ($$('#askQI #qiDefTab3')[0].className.indexOf('qiDefTabActive') > -1)
      this.showFullAsk('parser', false);
  },
    
  updateSrcAndPreview : function() {
    this.updateQuerySource();
    this.updatePreview();
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
        if(e.name !== 'format' && e.name !== 'default'){
          var visibleName = gLanguage.getMessage('QI_QP_PARAM_'+e.name);
          if (visibleName == 'QI_QP_PARAM_'+ e.name) // no text for argument
            visibleName = e.name;
          if (i % columns == 0)
            html += '<tr>'
          html += '<td title="' + e.description + '">' + visibleName + '</td>';
          if (e.values instanceof Array) {
            html += '<td>' + createSelectionBox(e.name, e.values)
            + '</td>';
          } else if (e.type == 'string' || e.type == 'integer') {
            html += '<td>' + createInputBox(e.name, e.values, e.constraints)
            + '</td>';
          } else if (e.type == 'boolean') {
            html += '<td>' + createCheckBox(e.name, e.defaultValue)
            + '</td>';
          }

          if (i % columns == 2)
            html += '</tr>'
          i++;
        }
      });
      html += '</table>';
      autoCompleter.deregisterAllInputs();
      $$('#askQI #queryprinteroptions')[0].innerHTML = html;
      autoCompleter.registerAllInputs();
      this.specialQPParameters = qpParameters;
      if (callWhenFinished) callWhenFinished();
    }
    var createSelectionBox = function(id, values) {
      var html = '<select id="' + 'qp_param_' + id + '" onchange="qihelper.updateSrcAndPreview()">';
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
      var html = '<input id="' + 'qp_param_' + id + '" type="text" '+aclAttributes+' onchange="qihelper.updateSrcAndPreview()"/>';
      return html;
    }
    var createCheckBox = function(id, defaultValue) {
      var defaultValueAtt = defaultValue ? 'checked="checked"' : '';
      var html = '<input id="' + 'qp_param_' + id + '" type="checkbox" '
      + defaultValueAtt + ' onchange="qihelper.updateSrcAndPreview()"/>';
      return html;
    }
    if (this.parameterPendingElement)
      this.parameterPendingElement.remove();
    this.parameterPendingElement = new OBPendingIndicator($$('#askQI #querylayout')[0]);
    this.parameterPendingElement.show();
        
    sajax_do_call('smwf_qi_QIAccess', [ 'getSupportedParameters', qp ],
      callback.bind(this));

  },

  serializeSpecialQPParameters : function(sep) {
    var paramStr = "";
    var first = true;
    this.specialQPParameters.each(function(p) {
      var element = $('qp_param_' + p.name);
      if(element){
        if (p.type == 'boolean' && element.checked) {
          paramStr += first ? p.name : sep + " " + p.name;
        } else {
          if (element.value != "" && element.value != p.defaultValue) {
            paramStr += first ? p.name + "=" + element.value.replace(/,/g,"%2C") : sep + " " + p.name + "=" + element.value;
          }
        }
        first = false;
      }
    });
    return paramStr;
  },

  /**
	 * Called whenever preview result printer is minimized or maximized
	 */
  switchpreview : function() {
    if ($$("#askQI #previewcontent")[0].style.display == "none") {
      $$("#askQI #previewcontent")[0].style.display = "";
      $$("#askQI #previewtitle-link")[0].removeClassName("plusminus");
      $$("#askQI #previewtitle-link")[0].addClassName("minusplus");
      // update preview
      this.previewResultPrinter();
    } else {
      $$("#askQI #previewcontent")[0].style.display = "none";
      $$("#askQI #previewtitle-link")[0].removeClassName("minusplus");
      $$("#askQI #previewtitle-link")[0].addClassName("plusminus");
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
  },

  /**
	 * Shows a confirmation dialogue
	 */
  resetQuery : function() {
    $$('#askQI #shade')[0].style.display = "inline";
    $$('#askQI #resetdialogue')[0].style.display = "inline";
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
    $$('#askQI #shade')[0].style.display = "none";
    $$('#askQI #resetdialogue')[0].style.display = "none";
    this.updatePreview();

    jQuery('#askQI #fullAskText').val('');
    this.enableResetQueryButton(true);
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
    var treeAnchor = $$('#askQI #treeanchor')[0];
    if(treeAnchor)
      treeAnchor.innerHTML = treeXML;
  },

  getPreviewDialog: function(){
  //locate priview dialog

  //if not found then create a new div at #askQI
  },

  getFullPreviewElement: function(){
    //    var element = jQuery('#askQI #fullpreview');
    //    if(!jQuery(element).length){
    jQuery('#askQI').append('<div id="fullpreview"/>');
    element = jQuery('#askQI').children('#fullpreview');
    //    }

    return element;
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

    try {
      this.pendingElement.remove();
    } catch(e) {};
    var qiFullPreviewElement = this.getFullPreviewElement();
    qiFullPreviewElement.html('<img src="' + wgServer + wgScriptPath + '/extensions/SMWHalo/skins/OntologyBrowser/images/ajax-loader.gif" />');
    qiPreviewDialog = qiFullPreviewElement.dialog(this.getDialogConfig());
    
    if (!this.queries[0].isEmpty()) { // only do this if the query is not
      // empty
      var ask = this.recurseQuery(0, "parser"); // Get full ask syntax
      this.queries[0].getDisplayStatements().each(function(s) {
        ask += "|?" + s
      });
			
      var params = ask.replace(/,/g, '%2C') + ",";
      var reasonerAndDs = this.getReasonerAndParams();
      if (reasonerAndDs.length > 0)
        params += reasonerAndDs.replace(/,/g, '%2C') + '|';
      params += $$('#askQI #layout_sort')[0].value == gLanguage.getMessage('QI_ARTICLE_TITLE')? "" : 'sort=' + $$('#askQI #layout_sort')[0].value + '|';
      params += 'format=' + $$('#askQI #layout_format')[0].value + '|';
      params += this.serializeSpecialQPParameters("|");
      params += '|merge=false';
      var currentPage = null;
      if (window.parent.wgPageName) {
        currentPage = window.parent.wgPageName.wgCanonicalNamespace || '';
        currentPage += ':' + window.parent.wgPageName;
      }
      sajax_do_call('smwf_qi_QIAccess', [ "getQueryResult", params, currentPage ],
        this.openPreview.bind(this));
    } else { // query is empty
      var request = Array();
      request.responseText = gLanguage.getMessage('QI_EMPTY_QUERY');
      this.openPreview(request);
    }

    
  },

  getDialogConfig: function(){
    var config = {
      title: 'Query result',
      height: 300,
      width: 500,
      closeOnEscape: true,
      modal: true,
      buttons: {
        "Close": function() {
          jQuery(this).remove();
        }
      }
    };

    //if ie8 and jquery version < 1.4.3
    //then make it full screen, non-resizable, non-draggable,
    var jqueryVersionStr = jQuery().jquery;
    var jqueryVersionNum = parseInt(jqueryVersionStr.replace(/[^\d]/g, ''));

    var browserVersionStr = navigator.userAgent;
    var msieRegex = /msie\s(\d+)\.\d;/i;
    var isIE = browserVersionStr.match(msieRegex);
    var ieVersionNum = isIE ? parseInt(browserVersionStr.match(msieRegex)[1]) : 0;

    if(jqueryVersionNum < 143 && isIE && ieVersionNum < 9){
      config.resizable = false;
      config.draggable = false;
      config.height = jQuery(window).height() - 50;
      config.width = jQuery(window).width() - 50;
    }

    return config;
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
    try {
      this.pendingElement.remove();
    } catch(e) {};
    this.pendingElement = new OBPendingIndicator($$('#askQI #previewcontent')[0]);
    this.pendingElement.show();

    var ask = this.getQueryFromTree();

    if (ask.length > 0) {
      var currentPage = null;
      if (window.parent.wgPageName) {
        currentPage = window.parent.wgPageName.wgCanonicalNamespace || '';
        currentPage += ':' + window.parent.wgPageName;
      }
      sajax_do_call('smwf_qi_QIAccess', [ "getQueryResult", ask, currentPage],
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
      ask = ask.replace(/,/g, '%2C');

      this.queries[0].getDisplayStatements().each(function(s) {
        ask += "|?" + s
      });
      var params = ask + ",";
      var reasonerAndDs = this.getReasonerAndParams();
      if (reasonerAndDs.length > 0)
        params += reasonerAndDs.replace(/,/g, '%2C') + '|';
      params += "format="+$$('#askQI #layout_format')[0].value + '|';
      if ($$('#askQI #layout_sort')[0].value != gLanguage.getMessage('QI_ARTICLE_TITLE')) params += "sort="+$$('#askQI #layout_sort')[0].value + '|';
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
    switch ($$('#askQI #layout_format')[0].value) {
            
      // for certain query printer it is
      // necessary to clear content of preview
      case 'ofc-pie':
      case 'ofc-bar':
      case 'ofc-bar_3d':
      case 'ofc-line':
      case 'ofc-scatterline':
        $$('#askQI #previewcontent')[0].innerHTML = '';
        break;
    }
    this.pastePreview(request);
  },

  /**
	 * Displays the preview created by the server
	 * 
	 * @param request
	 *            Request of AJAX call
	 */
  openResultPreview : function(request) {
    this.pastePreview(request, $$('#askQI #previewcontent')[0]);
  },
	
  pastePreview: function(request, preview) {
    if(this.pendingElement)
      this.pendingElement.hide();
        
    // pre-processing
    var resultHTML;
    var resultCode;
    switch ($$('#askQI #layout_format')[0].value) {
            
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
    if(!preview){
      if(qiPreviewDialog && qiPreviewDialog.dialog('isOpen')){
        qiPreviewDialog.html(resultHTML);
      }
    }
    else{
      preview.innerHTML = resultHTML;
    }

      
    // post processing of javascript for resultprinters:
    switch ($$('#askQI #layout_format')[0].value) {
      case "timeline":
      case "eventline":
        this.parseWikilinks2Html();
        //        smw_timeline_init();

        break;
      case "exhibit":
        if (typeof createExhibit == 'function') createExhibit();
        break;
      case 'ofc-pie':
      case 'ofc-bar':
      case 'ofc-bar_3d':
      case 'ofc-line':
      case 'ofc-scatterline':
        ofc_data_objs = {
          data:[]
        };
        if (resultCode != null) eval(resultCode);
        document.ofc.js.resetOfc();
        break;
      case 'tabularform':
        window.tf.loadForms();
        break;
    }
  },
  // ofc stuff can be once at a page only. If the full preview is closed,
  // load the small preview box again
  reloadOfcPreview : function() {
    if ($$('#askQI #layout_format')[0].value.indexOf('ofc-') == 0)
      this.updatePreview();
  },
  /**
	 * Creates valid links for Wiki Links in Preview div for elements like in
	 * timeline div with id="previewcontent" innerHtml is changed directly
	 */
  parseWikilinks2Html : function() {
		
    if ($$('#askQI #layout_link')[0] != null && $$('#askQI #layout_link')[0].value == "none")
      return;
    var text = $$('#askQI #previewcontent')[0].innerHTML;
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
    $$('#askQI #previewcontent')[0].innerHTML = newt;
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
    var breadcrumpDIV = $$('#askQI #treeviewbreadcrumbs')[0];
    if (breadcrumpDIV) breadcrumpDIV.innerHTML = html;
  },

  updateHeightBoxcontent : function() {
    var off = 0;
    var dim = $$('#askQI #treeviewbreadcrumbs')[0].getDimensions();
    off += dim.height + 3;
    dim = $$('#askQI #qistatus')[0].getDimensions();
    off += dim.height + 3;
    dim = $$('#askQI #dialoguebuttons')[0].getDimensions();
    off += dim.height + 3;
    $$('#askQI #boxcontent')[0].style.height = (300 - off) + 'px';
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
    var layoutSort = $$('#askQI #layout_sort')[0];
    if(layoutSort){
      layoutSort.innerHTML = "";
      for ( var i = 0; i < columns.length; i++) {
        layoutSort.options[layoutSort.length] = new Option(
          columns[i], columns[i]); // add options to optionbox
        if (this.sortColumn == columns[i])
          layoutSort.options[layoutSort.length -1].selected="selected";
      }
    }
  },

  getFullParserAsk : function() {
    var asktext = this.recurseQuery(0, "parser");
    var displays = this.queries[0].getDisplayStatements();
    var fullQuery = "{{#ask: " + asktext;
    for ( var i = 0; i < displays.length; i++) {
      fullQuery += "| ?" + displays[i];
    }
    fullQuery += ' | format=' + $$('#askQI #layout_format')[0].value;
    fullQuery += $$('#askQI #layout_sort')[0].value == gLanguage.getMessage('QI_ARTICLE_TITLE')
    ? ""
    : (' | sort=' + $$('#askQI #layout_sort')[0].value);
    var qParams = this.serializeSpecialQPParameters("|");
    if (qParams.length > 0) {
      if (! qParams.match(/^\s*\|/))
        fullQuery += '| ';
      fullQuery += qParams;
    }
    qParams = this.getReasonerAndParams();
    if (qParams.length > 0) fullQuery += "| "+ qParams;
    if ($$('#askQI #qiQueryName')[0].value)
      fullQuery += '| queryname=' + $$('#askQI #qiQueryName')[0].value;
    fullQuery += "| merge=false|}}";

    return fullQuery;
  },

  getAskQueryFromGui : function() {
    // which tab is active? query source or any other
    if ($$('#askQI #qiDefTab3')[0].className.indexOf('qiDefTabActive') != -1)
      return $$('#askQI #fullAskText')[0].value;
    else
      return this.getFullParserAsk();
  },

  insertAsNotification : function() {
    var query = this.getFullParserAsk();
    document.cookie = "NOTIFICATION_QUERY=<snq>" + query + "</snq>";
    if (query != "") {
      var snPage = $$('#askQI #qi-insert-notification-btn')[0].readAttribute(
        'specialpage');
      snPage = unescape(snPage);
      location.href = snPage;
    // window.open(snPage), "_blank");
    }

  },

  getReasonerAndParams : function() {
    var args = [];
    if ( $$('#askQI #usetriplestore')[0] != null && $$('#askQI #usetriplestore')[0].checked )
      args.push('source=tsc');
    else
      args.push('source=wiki');
    var selectedDataSources = [];
    var selectorDsTpee = ($$('#askQI #qioptioncontent')[0]) ? $$('#askQI #qioptioncontent')[0].getElementsBySelector('[name="qiDsTpeeSelector"]') : [];
    var dataSources = $$('#askQI #qidatasourceselector')[0];
    if (selectorDsTpee.length > 0 && selectorDsTpee[0].checked || selectorDsTpee.length == 0 && dataSources) {
      for (var i=0; i < dataSources.options.length; i++) {
        if (dataSources.options[i].selected) {
          selectedDataSources.push(dataSources.options[i].value);
        }
      }
      if (!(selectedDataSources.length == 1 &&
        selectedDataSources[0] == '-Wiki-'))
        args.push('dataspace=' + selectedDataSources.join(','));
    }
    if ( $$('#askQI #qio_showrating')[0] != null && $$('#askQI #qio_showrating')[0].checked )
      args.push('enableRating=true');
    if ( $$('#askQI #qio_showmetadata')[0] != null && $$('#askQI #qio_showmetadata')[0].checked ) {
      if ($$('#askQI #qio_showdatasource')[0].checked)
        args.push('metadata=(DATASOURCE_LABEL_FROM)');
      else if ($$('#askQI #qio_showmetadata')[0].value)
        args.push('metadata=' + $$('#askQI #qio_showmetadata')[0].value);
      else
        args.push('metadata=*');
    }
    if ( selectorDsTpee.length > 0 && selectorDsTpee[1].checked ) {
      var tpee = '';
      for (var i=0; i < $$('#askQI #qitpeeselector')[0].options.length; i++) {
        if ($$('#askQI #qitpeeselector')[0].options[i].selected) {
          tpee = $$('#askQI #qitpeeselector')[0].options[i].value;
          args.push('policyid=' + tpee);
          break;
        }
      }
      if ( $('qitpeeparams_' + tpee )) {
        var span = $$('#qitpeeparams_' + tpee + ' span' );
        var jsonParams = [];
        for (var i = 0; i < span.length; i++) {
          var pname = (span[i].name) ? span[i].name.replace('qitpeeparams_ ' + tpee + '_', '') : span[i].innerHTML;
          var val = $('qitpeeparamval_' + tpee + '_' + pname) &&
          ( $('qitpeeparamval_' + tpee + '_' + pname).value || $('qitpeeparamval_' + tpee + '_' + pname).innerHTML);
          if (pname && val) {
            if (pname == 'PAR_USER') {
              val = $$('#askQI #qi_tsc_wikigraph')[0].innerHTML + '/' + $$('#askQI #qi_tsc_userns')[0].innerHTML + '/' + val;
            }
            if (pname == 'PAR_ORDER') {
              var vals = [];
              var table = $('qitpeeparamval_' + tpee + '_' + pname);
              for (var r = 0; r < table.rows.length; r++) {
                // vars need to be in double quotes
                vals.push('\\"' + table.rows[r].cells[0].getAttribute('_sourceid') + '\\"');
              }
              if (vals.length > 0) {
                val = '[' + vals.join(',') + ']';
              }
            }
            jsonParams.push( '"' + pname + '":"' + val + '"' );
          }
        }
        if (jsonParams.length > 0) {
          args.push('policyparams={' + jsonParams.join(',') +'}');
        }
        // if we had the order param we must add the policy var with the printout statement
        // it must be one only so take the first one
        if ($('qitpeeparamval_' + tpee + '_PAR_ORDER') ){
          var printouts = this.queries[0].getDisplayStatements();
          args.push('policyvar='+ ((printouts[0]) ? printouts[0] : ''));
        }
        // trust based queries use their specified graphs, hence send empty
        // graph statement so that it's not overwritten with the wiki graph in the sparql
        args.push('graph=');
      }
    }
    // return now all parameters
    if (args.length > 0)
      return args.join("| ");
    return "";
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
    $$('#askQI #qidelete')[0].style.display = "none";   // New dialogue, no delete button
    $$('#askQI #qistatus')[0].innerHTML= '';            // empty status message
    autoCompleter.deregisterAllInputs();
    if (reset)
      this.loadedFromId = null;
    for ( var i = 0, n = $$('#askQI #dialoguecontent')[0].rows.length; i < n; i++)
      // empty dialogue table
      $$('#askQI #dialoguecontent')[0].deleteRow(0);
    // the property dialogue has several tables
    while (1) {
      var n = $$('#askQI #dialoguecontent')[0].parentNode.nextSibling;
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

    var newrow = $$('#askQI #dialoguecontent')[0].insertRow(-1); // create the
    // dialogue
    var cell = newrow.insertCell(0);
    cell.innerHTML = gLanguage.getMessage('CATEGORY');
    cell = newrow.insertCell(1);
    // input field with autocompletion enabled
    cell.innerHTML = '<input type="text" id="input0" class="wickEnabled general-forms" constraints="namespace: 14" autocomplete="OFF" '+
    'title="' +  gLanguage.getMessage('AUTOCOMPLETION_HINT') + '"/>';
    cell = newrow.insertCell(2);
    // link to add another input for or-ed values
    newrow = $$('#askQI #dialoguecontent')[0].insertRow(-1);
    cell = newrow.insertCell(0);
    cell.style.textAlign="left";
    cell.setAttribute('colspan', '3');
    cell.innerHTML = '<a href="javascript:void(0)" onclick="qihelper.addDialogueInput()">'
    + gLanguage.getMessage('QI_BC_ADD_OTHER_CATEGORY') + '</a>';
    $$('#askQI #dialoguebuttons')[0].style.display = "inline";
    var btn = $$('#askQI #dialoguebuttons')[0].getElementsByTagName('button').item(0);
    btn.innerHTML =
    gLanguage.getMessage((reset) ? 'QI_BUTTON_ADD' : 'QI_BUTTON_UPDATE');
    autoCompleter.registerAllInputs();
    if (reset)
      $$('#askQI #input0')[0].focus();
    this.updateHeightBoxcontent();
    this.enableButton(this.getInputs());
    this.setListeners(this, this.getInputs());
    initToolTips();
  },
        
  enableButton: function(inputsArray){
    if(inputsArray){
      var btn = $$('#askQI #dialoguebuttons')[0].getElementsByTagName('button').item(0);
      btn.disabled = false;
      $(inputsArray).each(function(inputElement){
        if(!inputElement.getValue()){
          btn.disabled = true;
        }
      });
    }
  },
    
        
  setListeners: function(thisObj, inputsArray){
    if(inputsArray){
      $(inputsArray).each(function(inputElement){
        inputElement.observe('keyup', function(event){
          thisObj.enableButton(inputsArray);
        });
        inputElement.observe('change', function(event){
          thisObj.enableButton(inputsArray);
        });      
        inputElement.observe('blur', function(event){
          thisObj.enableButton(inputsArray);
        });
      });
    }
  },
        
  //  getPropertyDialogInputs: function(){
  //    var inputs = this.getInputs();
  //    if($$('#askQI #dialoguecontent_pvalues')[0].visible()){
  //      inputs = inputs.concat($$('#dialoguecontent_pvalues input[type="text"]'));
  //    }
  //    return inputs;
  //  },
        
  getInputs: function(){
    var visibleTables = $$('#boxcontent table').findAll(function(element) {
      return element.visible();
    });
    var inputs = [];
    visibleTables.each(function(element){
      inputs = inputs.concat(element.select('input[type="text"]'));
    });
    return inputs;
  },

  observeRadioBtnClick: function(thisObj){
    var radioBtns = $$('#dialoguecontent_pradio input[type="radio"][name="input_r0"]');
    radioBtns.each(function(radioBtnElement){
      radioBtnElement.observe('change', function(event){        
        thisObj.setListeners(thisObj, thisObj.getInputs());
        thisObj.observeSelectBoxChange(thisObj);
        thisObj.enableButton(thisObj.getInputs());
      });
    });
  },


  observeSelectBoxChange: function(thisObj){
    var selectBoxes = $$('#dialoguecontent_pvalues select');
    selectBoxes.each(function(selectBoxElement){
      selectBoxElement.observe('change', function(event){
        thisObj.enableButton(thisObj.getInputs());
      });
    });
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
    var catConstraint = "";
    if (this.activeQuery.categories.length > 0) {
      catConstraint = "ask:";
      var categories = this.activeQuery.categories;
      categories.each(function(c) {
        catConstraint += '[[' + gLanguage.getMessage('CATEGORY_NS',
          'cont') + c + ']]';
      });
      catConstraint += '|';
    }
    var newrow = $$('#askQI #dialoguecontent')[0].insertRow(-1);
    var cell = newrow.insertCell(0);
    cell.innerHTML = gLanguage.getMessage('QI_INSTANCE');
    cell = newrow.insertCell(1);
    cell.innerHTML = '<input type="text" id="input0" class="wickEnabled general-forms" constraints="' + catConstraint + 'namespace: 0" autocomplete="OFF" '+
    'title="' +  gLanguage.getMessage('AUTOCOMPLETION_HINT') + '"/>';
    cell = newrow.insertCell(2);
    // link to add another input for or-ed values
    newrow = $$('#askQI #dialoguecontent')[0].insertRow(-1);
    cell = newrow.insertCell(0);
    cell.style.textAlign="left";
    cell.setAttribute('colspan', '3');
    cell.innerHTML = '<a href="javascript:void(0)" onclick="qihelper.addDialogueInput()">'
    + gLanguage.getMessage('QI_BC_ADD_OTHER_INSTANCE') + '</a>';
    $$('#askQI #dialoguebuttons')[0].style.display = "inline";
    var btn = $$('#askQI #dialoguebuttons')[0].getElementsByTagName('button').item(0);
        
    btn.innerHTML =
    gLanguage.getMessage((reset) ? 'QI_BUTTON_ADD' : 'QI_BUTTON_UPDATE');
    autoCompleter.registerAllInputs();
    if (reset)
      $$('#askQI #input0')[0].focus();
    this.updateHeightBoxcontent();
    this.enableButton(this.getInputs());
    this.setListeners(this, this.getInputs());
    initToolTips();
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
       
    $$('#askQI #dialoguebuttons')[0].style.display = "inline";
    var btn = $$('#askQI #dialoguebuttons')[0].getElementsByTagName('button').item(0);
    btn.innerHTML =
    gLanguage.getMessage((reset) ? 'QI_BUTTON_ADD' : 'QI_BUTTON_UPDATE');
    this.proparity = 2;
    autoCompleter.registerAllInputs();
    if (reset)
      $$('#askQI #input_p0')[0].focus();
    this.updateHeightBoxcontent();   
        
    var propLabelInput = $$('#askQI #input_c3')[0];
    var propNameInput = $$('#askQI #input_p0')[0];
        
    propLabelInput.observe('keyup', function(event){
      if(Event.element(event).getValue()){
        qihelper.colNameEntered = true;
      }
      else{
        qihelper.colNameEntered = false;
      }
    });

    propNameInput.observe('keyup', function(event){
      if(!qihelper.colNameEntered){
        propLabelInput.setValue(propNameInput.getValue());
      }
    });
    propNameInput.observe('change', function(event){
      if(!qihelper.colNameEntered){
        propLabelInput.setValue(propNameInput.getValue());
      }
    });
    propNameInput.observe('blur', function(event){
      if(!qihelper.colNameEntered){
        propLabelInput.setValue(propNameInput.getValue());
      }
    });
            
    this.observeRadioBtnClick(this);
    this.observeSelectBoxChange(this);
    this.enableButton(this.getInputs());
    this.setListeners(this, this.getInputs());
    initToolTips();
  },

  getCategoryConstraints : function() {
    // fetch category constraints:
    var cats = this.activeQuery.categories; // get the category group
    var constraintsCategories = "";
    for ( var i = 0, n = cats.length; i < n; i++) {
      var catconstraint = cats[i].join(',');
      if (i < n -1)
        catconstraint += ',';
    }
    constraintsCategories += gLanguage.getMessage('CATEGORY_NS',
      'cont')
    + catconstraint;
    return constraintsCategories;
  },

  addPropertyChainInput : function(propName) {
    autoCompleter.deregisterAllInputs();
    // fetch category constraints:
    var constraintsCategories = "";
    // calculate index of current field
    var idx = $$('#askQI #dialoguecontent')[0].rows.length;
    if (idx > 0) idx = (idx - 1) / 2;
        
    // check if this is an element of a property chain
    if  (idx > 0) {
      var pName = $('input_p'+(idx-1)).value;
      if (this.propRange[pName.toLowerCase()]) {
        constraintsCategories = this.propRange[pName.toLowerCase()];
      }
    }
    if (constraintsCategories.length == 0) {
      constraintsCategories = this.getCategoryConstraints();
    }
    var constraintstring = "schema-property-domain: "+constraintsCategories+ "|annotation-property: "+constraintsCategories + "|namespace: 102";
    var newrow = $$('#askQI #dialoguecontent')[0].insertRow(idx*2);
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
    + 'onkeyup="qihelper.clearPropertyType('+idx+'), qihelper.getPropertyInformation()" '
    + ((propName) ? 'value="'+propName+'" ' : '')
    + 'title="' +  gLanguage.getMessage('AUTOCOMPLETION_HINT') + '"'
    + '/>';
    if (idx > 0)
      tmpHTML += ' <img src="'	+ this.imgpath + 'delete.png" alt="deleteInput" onclick="qihelper.removePropertyChainInput()"/>';
    cell.innerHTML = tmpHTML;
    // row with property type
    newrow = $$('#askQI #dialoguecontent')[0].insertRow(idx*2+1);
    newrow.style.lineHeight="1";
    newrow.insertCell(0);
    cell = newrow.insertCell(1);
    cell.style.textAlign="left";
    cell.style.fontSize="60%";
    cell.style.color="#AAAAAA";
    cell.innerHTML = gLanguage.getMessage('QI_PROPERTY_TYPE') + ':';
    // link to add property chain
    if (idx == 0) {
      newrow = $$('#askQI #dialoguecontent')[0].insertRow(-1);
      cell = newrow.insertCell(0);
      cell = newrow.insertCell(1);
      cell.style.textAlign="left";
      cell.setAttribute('colspan', 2);
      cell.innerHTML = '<div id="addchain"></div>';
    }
    else {
      // if there is a remove icon in the previous line, remove it.
      try {
        var img = $$('#askQI #dialoguecontent')[0].rows[(idx-1)*2].getElementsByTagName('td')[1].getElementsByTagName('img');
        if (img.length > 0)
          img[0].parentNode.removeChild(img[0]);
      } catch (e) {};
      // if the previous input field has bold style, remove that
      try {
        var input = $$('#askQI #dialoguecontent')[0].rows[(idx-1)*2].getElementsByTagName('td')[1].getElementsByTagName('input');
        input[0].style.fontWeight = 'normal';
      }catch (e) {};
    }
    autoCompleter.registerAllInputs();
    if (!propName) $('input_p' + idx).focus(); // focus created input
    this.toggleAddchain(false);
    this.enableButton(this.getInputs());
    this.setListeners(this.getInputs());
  },

  setPropertyRestriction : function () {
    if (this.oldPropertyRestriction == null) this.oldPropertyRestriction = -1;
    var table = $$('#askQI #dialoguecontent_pradio')[0]
    if (!table) return;
    var radio = table.getElementsByTagName('input');
    $$('#askQI #usesub_text')[0].style.display= (radio[2].checked) ? "block" : "none";
    if (radio[1].checked) {
      $$('#askQI #dialoguecontent_pvalues')[0].style.display="inline";
      if ($$('#askQI #dialoguecontent_pvalues')[0].rows.length == 0)
        this.addRestrictionInput();
    }
    else {
      $$('#askQI #dialoguecontent_pvalues')[0].style.display="none";
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
    if ($$('#askQI #dialoguecontent_pvalues')[0].rows.length ==  0 || arity > 2)
      var newrow = $$('#askQI #dialoguecontent_pvalues')[0].insertRow(-1);
    else
      var newrow = $$('#askQI #dialoguecontent_pvalues')[0].insertRow($$('#askQI #dialoguecontent_pvalues')[0].rows.length -1);
    try {
      var newRowIndex = $$('#askQI #dialoguecontent_pvalues')[0].rows[newrow.rowIndex - 1].id;
      newRowIndex = parseInt(newRowIndex.substr(5))+1;
    } catch (e) {
      newRowIndex = 1;
    }
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
    var param = (this.propTypename) ? this.propTypename : gLanguage.getMessage('QI_PAGE');
    var ptype =  (this.propTypetype) ? this.propTypetype : '_wpg';
    var numericType = this.numTypes[param.toLowerCase()] ? 1 : 0;
    var propNameAC = gLanguage.getMessage('PROPERTY_NS')+this.propname.replace(/\s/g, '_');
    var ac_constraint = 'annotation-value: '+propNameAC;
    if (ptype == '_wpg') { // property type = page
      if ( this.propname.toLowerCase() == gLanguage.getMessage('HAS_TYPE') ) // property: has type
        ac_constraint = 'namespace: 104';
      else if (this.propRange[this.propname.toLowerCase()])
        ac_constraint = 'instance-property-range: '+propNameAC;
      else
        ac_constraint += '|namespace: 0';
    } else if (ptype == '_dat') { // property type = date
      ac_constraint = 'fixvalues: {{NOW}},{{TODAY}}|annotation-value: '+propNameAC;
      numericType= 1;
    } else if (ptype == '_str') {
      numericType= 2;
    } // else property, no page type and no date type, use defaults as set above.
    cell = newrow.insertCell(1);
    cell.innerHTML = this.createRestrictionSelector("=", false, numericType);
    cell = newrow.insertCell(2);
    if (this.propIsEnum) { // if enumeration, a select box is used
      // instead of a text input field
      var oSelect = document.createElement("SELECT");
      oSelect.id = "input_r" + newRowIndex;
      var optOff = 0;
      if (arity == 2 && newRowIndex == 1) {
        oSelect.options[0]= new Option(gLanguage.getMessage('QI_ALL_VALUES'), '*');
        optOff = 1;
      }
      for ( var i = 0; i < this.enumValues.length; i++) {
        oSelect.options[i+optOff]=
        new Option(this.enumValues[i], this.enumValues[i]);
        oSelect.options[i+optOff].style.width="100%";
      }
      cell.appendChild(oSelect);
      this.observeSelectBoxChange(this);
    } else { // no enumeration, no page type, simple input field
      var oInput = document.createElement("INPUT");
      oInput.type = "text";
      oInput.id = "input_r" + newRowIndex;
      oInput.className = "wickEnabled general-forms";
      oInput.setAttribute('autocomplete', 'OFF');
      oInput.setAttribute('constraints', ac_constraint);
      cell.appendChild(oInput);
      try {
        var uIdx = (arity == 2) ? 0 : newRowIndex - 1;
        if (this.propUnits.length > 0 && this.propUnits[uIdx].length > 0) {
          var oSelect = document.createElement("SELECT");
          oSelect.id = "input_ru" + newRowIndex;
          for (var i = 0, m = this.propUnits[uIdx].length; i < m; i++) {
            oSelect.options[i] =
            new Option(this.propUnits[uIdx][i], this.propUnits[uIdx][i]);
          }
          cell.appendChild(oSelect);
        }
      } catch (e) {};
    }
    if (arity == 2) {
      if ($$('#askQI #dialoguecontent_pvalues')[0].rows.length > 1) {
        cell = newrow.insertCell(-1);
        cell.innerHTML = '<img src="'
        + this.imgpath
        + 'delete.png" alt="deleteInput" onclick="qihelper.removeRestrictionInput(this)"/>';
      }
      else {
        newrow = $$('#askQI #dialoguecontent_pvalues')[0].insertRow(-1);
        cell = newrow.insertCell(-1);
        cell.setAttribute('colspan', '4');
        cell.innerHTML = '<a href="javascript:void(0);" onclick="qihelper.addRestrictionInput()">'
        + gLanguage.getMessage('QI_DC_ADD_OTHER_RESTRICT') + '</a>';
      }
    }
    if ($$('#askQI #dialoguecontent_pvalues')[0].style.display != 'none')
      $('input_r' + newRowIndex).focus(); // focus created input
    autoCompleter.registerAllInputs();
                
    this.enableButton(this.getInputs());
    this.setListeners(this, this.getInputs());
    initToolTips();
  },

  removeRestrictionInput : function(element) {
    var tr = element.parentNode.parentNode;
    tr.parentNode.removeChild(tr);
        
    this.enableButton(this.getInputs());
    this.setListeners(this, this.getInputs());
  },

  removePropertyChainInput : function() {
    var idx = ($$('#askQI #dialoguecontent')[0].rows.length -1) / 2 -1;
    if (idx == 0) return;
    $$('#askQI #dialoguecontent')[0].deleteRow(idx*2+1);
    $$('#askQI #dialoguecontent')[0].deleteRow(idx*2);
    if (idx > 1) {
      var img = document.createElement('img');
      img.src=this.imgpath + "delete.png";
      img.alt="deleteInput";
      img.setAttribute('onclick', "qihelper.removePropertyChainInput()");
      $$('#askQI #dialoguecontent')[0].rows[idx *2 - 2].getElementsByTagName('td')[1].appendChild(img);
      $$('#askQI #dialoguecontent')[0].rows[idx *2 - 2].getElementsByTagName('td')[1]
      .getElementsByTagName('input').item(0).style.fontWeight = "bold";
    }
    this.toggleAddchain(true);
    this.enableButton(this.getInputs());
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
    this.propTypetype = null;
    this.propUnits = null;
    var dialogContent = $$('#askQI #dialoguecontent')[0];
    if(dialogContent){
      for ( var i = 0, n = dialogContent.rows.length; i < n; i++)
        dialogContent.deleteRow(0);
      while (n = dialogContent.parentNode.nextSibling ) {
        n.parentNode.removeChild(n);
      }
      $$('#askQI #dialoguebuttons')[0].style.display = "none";
      $$('#askQI #qistatus')[0].innerHTML = "";
      $$('#askQI #qidelete')[0].style.display = "none";
      this.activeInputs = 0;
    }
    this.updateBreadcrumbs(this.activeQueryId);
  },
    
  /**
	 * Add another input to the current dialogue
	 */
  addDialogueInput : function() {
    autoCompleter.deregisterAllInputs();
    // id for input field, increased by one from the last field
    var inputs = $$('#askQI #dialoguecontent')[0].getElementsByTagName('input');
    var id = inputs[inputs.length-1].id;
    id = parseInt(id.substring(5))+1;
    var newRowId = $$('#askQI #dialoguecontent')[0].rows.length - 1;
    var newrow = $$('#askQI #dialoguecontent')[0].insertRow(newRowId);
    var cell = newrow.insertCell(0);
    cell.style.fontWeight = "bold";
    cell.innerHTML = gLanguage.getMessage('QI_OR').toUpperCase();
    cell = newrow.insertCell(1);

    var ns;
    if (this.activeDialogue == "category") // add input fields according to dialogue
      ns = '14';
    else if (this.activeDialogue == "instance")
      ns = '0';

    cell.innerHTML = '<input class="wickEnabled general-forms" constraints="namespace: '+ns+'" autocomplete="OFF" '+
    'type="text" id="input' + id + '" title="' +  gLanguage.getMessage('AUTOCOMPLETION_HINT') + '"/>';
    cell = newrow.insertCell(-1);
    cell.innerHTML = '<img src="'
    + this.imgpath
    + 'delete.png" alt="deleteInput" onclick="qihelper.removeInput(this);"/>';
    $('input' + id).focus(); // focus created input
    autoCompleter.registerAllInputs();
    this.setListeners(this, this.getInputs());
    this.enableButton(this.getInputs());
    initToolTips();
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
    this.setListeners(this, this.getInputs());
    this.enableButton(this.getInputs());
  },

  /**
   * When the inputfield with the property name is changed, then clear the
   * property type so that it is retrieved again. This must be done so that the
   * user cannot just hit "update" still with the old property information
   * @param idx
   *            Integer with the field number that has been changed
   */
  clearPropertyType : function( idx ){
    $$('#askQI #dialoguecontent')[0].rows[idx * 2 +1].cells[1].innerHTML=
    gLanguage.getMessage('QI_PROPERTY_TYPE') + ':';
  },
  /**
	 * Is called everytime a user entered a property name and leaves the input
	 * field. Executes an ajax call which will get information about the
	 * property (if available)
	 */
  getPropertyInformation : function() {
    var idx = ($$('#askQI #dialoguecontent')[0].rows.length -1) / 2 - 1;
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
      if ($$('#askQI #dialoguecontent_pvalues')[0]) {
        while ($$('#askQI #dialoguecontent_pradio')[0].rows.length > 1)
          $$('#askQI #dialoguecontent_pradio')[0].deleteRow(1);
      }
      // clean hidden table with old data and add pending indicator.
      /*
            $('displaycontent_pvalues_hidden').innerHTML = '';
            var row = $('displaycontent_pvalues_hidden').insertRow(-1);
            var cell = row.insertCell(-1);
            this.pendingElement = new OBPendingIndicator(cell);
            this.pendingElement.show();
       */
      if(window.timeoutId){
        window.clearTimeout(timeoutId);
      }
      window.timeoutId = window.setTimeout(function(){
        sajax_do_call('smwf_qi_QIAccess', [ 'getPropertyInformation', escapeQueryHTML(propname) ], qihelper.adaptDialogueToProperty.bind(qihelper))
      }, 500);
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
      try {
        var oldsubid = $$('#askQI #dialoguecontent_pradio')[0].getElementsByTagName('input')[2].value;
      } catch (e) {
        var oldsubid = this.nextQueryId;
      }
      if ($$('#askQI #dialoguecontent_pvalues')[0]) {
        while ($$('#askQI #dialoguecontent_pvalues')[0].rows.length > 0)
          $$('#askQI #dialoguecontent_pvalues')[0].deleteRow(0);
      }
      if ($$('#askQI #dialoguecontent_pradio')[0])
        $$('#askQI #dialoguecontent_pradio')[0].insertRow(-1);
      var tmpHTML = "";
      // create standard values in case request fails
      this.proparity = 2;
      var parameterNames = [ gLanguage.getMessage('QI_PAGE') ];
      var parameterTypes = [];
      var possibleValues = new Array();
      var possibleUnits = new Array();
      var propertyName= $$('#askQI #dialoguecontent')[0].rows[$$('#askQI #dialoguecontent')[0].rows.length -3]
      .getElementsByTagName('input')[0].value;

      if (request.status == 200) {
        var schemaData = GeneralXMLTools
        .createDocumentFromString(request.responseText);

        // read arity
        this.proparity = parseInt(schemaData.documentElement
          .getAttribute("arity"));
        // check that the property which we are receiving information for
        // is that that the property we've asked for
        // (important for AC - then this function is called twice
        // 1st with substr of user input and 2nd with completed prop name from AC)
        if (schemaData.documentElement.getAttribute("name") != propertyName) return;
        propertyName = schemaData.documentElement.getAttribute("name");
        parameterNames = [];
        parameterRanges = [];
        // parse all parameter names
        for ( var i = 0, n = schemaData.documentElement.childNodes.length; i < n; i++) {
          parameterNames.push(schemaData.documentElement.childNodes[i].getAttribute("name"));
          parameterTypes.push(schemaData.documentElement.childNodes[i].getAttribute("type"));
          var range = schemaData.documentElement.childNodes[i].getAttribute("range");
          if (range) parameterRanges.push(range);
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
        // save the range information if there were any
        if (parameterRanges.length > 0)
          this.propRange[ propertyName.toLowerCase() ] = parameterRanges.join(',');
      }
      // remove additional rows, if these had been added before
      // we got the information that this property is not of the type page
      var rowCount= origRowCount = $$('#askQI #dialoguecontent')[0].rows.length;
      while (rowCount > 3 && propertyName.length > 0 &&
        $$('#askQI #dialoguecontent')[0].rows[rowCount -3].cells[1].firstChild.value != propertyName) {
        $$('#askQI #dialoguecontent')[0].deleteRow(rowCount-2);
        $$('#askQI #dialoguecontent')[0].deleteRow(rowCount-3);
        rowCount= $$('#askQI #dialoguecontent')[0].rows.length;
      }
      if (rowCount < origRowCount) {
        var img = document.createElement('img');
        img.src = this.imgpath + 'delete.png';
        img.alt = "deleteInput"
        img.setAttribute('onclick',"qihelper.removePropertyChainInput()");
        $$('#askQI #dialoguecontent')[0].rows[rowCount - 3].cells[1].appendChild(img);
      }
      // property name with _ for auto completion
      var propNameAC = gLanguage.getMessage('PROPERTY_NS')+propertyName.replace(/\s/g, '_');
      if (this.proparity == 2) {
        // Special treatment: binary properties support conjunction,
        // therefore we need an "add" button
        var ac_constraint = "";
        if (parameterTypes[0] == '_wpg') {
          ac_constraint = 'constraints="'+
          (this.propRange[propertyName.toLowerCase()]) ? 'instance-property-range: ' : 'annotation-value: '
          +propNameAC+'|namespace: 0"';
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
        this.propTypetype = parameterTypes[0];
        $$('#askQI #dialoguecontent')[0].rows[$$('#askQI #dialoguecontent')[0].rows.length -2].cells[1].innerHTML=
        gLanguage.getMessage('QI_PROPERTY_TYPE') + ': ' + parameterNames[0];

        // add units to selection to show property checkbox if there are any
        if (possibleUnits.length > 0 && possibleUnits[0].length > 0) {
          for (var i = 0; i < possibleUnits[0].length; i++ ) {
            $$('#askQI #input_c4')[0].options[i] = new Option(possibleUnits[0][i], possibleUnits[0][i]);
          }
          // runtime issue, check if we display hide values at once
          $$('#askQI #input_c4d')[0].style.display = $$('#askQI #input_c1')[0].checked ? null : 'none';
        }
        else {
          $$('#askQI #input_c4')[0].outerHTML = '<select id="input_c4"></select>';
          $$('#askQI #input_c4d')[0].style.display = 'none';
        }
				
        // special input field for enums
        if (possibleValues.length > 0) { // enumeration
          this.propIsEnum = true;
          this.enumValues = new Array();
					
          for ( var i = 0; i < possibleValues.length; i++) {
            this.enumValues.push(possibleValues[i]); // save
          // enumeration
          // values
          // for later
          // use
          }
        }
                
        // if binary property, make an 'insert subquery' checkbox
        if (parameterTypes[0] == '_wpg') {
          $$('#askQI #dialoguecontent_pradio')[0].getElementsByTagName('input')[2].value = oldsubid;
          $$('#askQI #dialoguecontent_pradio')[0].getElementsByTagName('input')[2].disabled = '';
        } else { // no checkbox for other types
          $$('#askQI #dialoguecontent_pradio')[0].getElementsByTagName('input')[2].disabled = 'true';
        }
      }else {
        // properties with arity > 2: attributes or n-ary. no conjunction, no subqueries
        $$('#askQI #dialoguecontent_pradio')[0].getElementsByTagName('input')[2].disabled = 'true';
        // set property type
        $$('#askQI #dialoguecontent')[0].rows[$$('#askQI #dialoguecontent')[0].rows.length -2].cells[1].innerHTML=
        gLanguage.getMessage('QI_PROPERTY_TYPE') + ': ' + gLanguage.getMessage('TYPE_RECORD');
        this.propTypename = gLanguage.getMessage('TYPE_RECORD');
        this.propTypetype = '_rec';
        this.toggleSubqueryAddchain(false);

        var row = $$('#askQI #dialoguecontent_pvalues')[0].insertRow(-1);
        var cell = row.insertCell(-1);
        cell.innerHTML = gLanguage.getMessage('QI_PROPERTYVALUE');
        for ( var i = 0; i < parameterNames.length; i++) {
          // Label of cell is parameter name (ex.: Integer, Date,...)
          row = $$('#askQI #dialoguecontent_pvalues')[0].insertRow(-1);
          cell = row.insertCell(-1);
          cell.style.textAlign="right";
          cell.innerHTML = parameterNames[i];
          cell = row.insertCell(-1);
          cell.style.textAlign="right";
          if (this.numTypes[parameterNames[i].toLowerCase()])
            cell.innerHTML = this.createRestrictionSelector("=", false, 1);
          else if ( parameterNames[i] == gLanguage.getMessage('TYPE_STRING') )
            cell.innerHTML = this.createRestrictionSelector("=", false, 2);
          else
            cell.innerHTML = this.createRestrictionSelector("=", false, 0);
          cell = row.insertCell(-1);
          if (parameterTypes[i] == '_wpg') {
            cell.innerHTML = '<input class="wickEnabled general-forms" constraints="'
            + (this.propRange[parameterNames[i].toLowerCase()]) ? 'instance-property-range: ' : 'annotation-value: '
            + propNameAC+'|namespace: 0" type="text" id="input_r' + (i + 1) + '"/>';
          }else if (parameterTypes[i] == '_dat') {
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
      // set the column name if there is nothing typed in yet
      if ( !qihelper.colNameEntered ) {
        $$('#askQI #input_c3')[0].value = this.propname;
      }
      // runtime issue: if the user selected radio for specific value
      // and the property information is loaded after that, make the new
      // created restriction table visible
      if ($$('#askQI #dialoguecontent_pradio')[0].getElementsByTagName('input')[1].checked)
        this.setPropertyRestriction();
    }
    autoCompleter.registerAllInputs();
    //this.pendingElement.hide();
    if (this.propertyAddClicked) this.addPropertyGroup(1);

    this.observeRadioBtnClick(this);
    this.observeSelectBoxChange(this);
    this.enableButton(this.getInputs());
    this.setListeners(this, this.getInputs());
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
    if (this.activeDialogue == "property" && $$('#askQI #input_c1')[0]) return;
    // hr line
    var node = document.createElement('hr');
    $$('#askQI #dialoguecontent')[0].parentNode.parentNode.appendChild(node);
    // second table with checkbox for display option and value must be set
    node = document.createElement('table');
    var row = node.insertRow(-1);
    var cell = row.insertCell(0);
    cell.style.verticalAlign="top";
    cell.innerHTML = gLanguage.getMessage('QI_PROPERTYVALUE');
    cell = row.insertCell(1);
    var tmpHTML='<table><tr><td '
    + 'title="' + gLanguage.getMessage('QI_TT_SHOW_IN_RES') + '">'
    + '<input type="checkbox" id="input_c1"'
    + (this.activeQueryId == 0 ? ' checked="checked"':'')+' />'
    + ' </td><td> '
    + '<span title="' + gLanguage.getMessage('QI_TT_SHOW_IN_RES') + '">'
    + gLanguage.getMessage('QI_SHOW_PROPERTY')
    + '</span></td><td> </td></tr>'
    + '<tr id="input_c3d"'+(this.activeQueryId > 0 ? ' style="display:none"':'')+'><td> </td>'
    + '<td>' + gLanguage.getMessage('QI_COLUMN_LABEL') + ':</td>'
    + '<td><input type="text" id="input_c3"/></td></tr>'
    + '<tr id="input_c4d" style="display:none"><td> </td>'
    + '<td>' + gLanguage.getMessage('QI_SHOWUNIT') + ':</td>'
    + '<td><select id="input_c4"></select></td></tr>'
    + '<tr><td title="' + gLanguage.getMessage('QI_TT_MUST_BE_SET') + '">'
    + '<input type="checkbox" id="input_c2"/></td>'
    + '<td><span title="' + gLanguage.getMessage('QI_TT_MUST_BE_SET') + '">'
    + gLanguage.getMessage('QI_PROPERTY_MUST_BE_SET')
    + '</span></td><td> </td></tr></table>';
    cell.innerHTML = tmpHTML;
    $$('#askQI #dialoguecontent')[0].parentNode.parentNode.appendChild(node);
    // add event handler when clicking the checkbox "show in result"
    if (this.activeQueryId == 0)
      $$('#askQI #input_c1')[0].onclick = function() {
        qihelper.toggleShowProperty();
      }
    else
      $$('#askQI #input_c1')[0].disabled = "disabled";
            
    // hr line
    node = document.createElement('hr');
    $$('#askQI #dialoguecontent')[0].parentNode.parentNode.appendChild(node);

    // property restriction table
    node = document.createElement('table');
    node.className = "propertyvalues";
    node.id = "dialoguecontent_pradio";
    row = node.insertRow(-1);
    cell = row.insertCell(-1);
    cell.setAttribute('style', 'border-botton: 1px solid #AAAAAA;');
    cell.innerHTML = gLanguage.getMessage('QI_PROP_VALUES_RESTRICT') + ': '
    + '<span title="' + gLanguage.getMessage('QI_TT_NO_RESTRICTION') + '">'
    + '<input type="radio" name="input_r0" value="-1" checked="checked" />' + gLanguage.getMessage('QI_NONE')
    + '</span>&nbsp;<span title="' + gLanguage.getMessage('QI_TT_VALUE_RESTRICTION') + '">'
    + '<input type="radio" name="input_r0" value="-2" />' + gLanguage.getMessage('QI_SPECIFIC_VALUE')
    + '</span>&nbsp;<span title="' + gLanguage.getMessage('QI_TT_SUBQUERY') + '">'
    + '<input type="radio" name="input_r0" value="'+this.nextQueryId+'" />'
    + '<span id="usesub">' + gLanguage.getMessage('QI_SUBQUERY') + '</span></span>&nbsp'
    + '<div id="usesub_text" style="display:none">' + gLanguage.getMessage('QI_SUBQUERY_TEXT') + '</div>';
    $$('#askQI #dialoguecontent')[0].parentNode.parentNode.appendChild(node);
    node = document.createElement('table');
    node.style.display="none";
    node.id = "dialoguecontent_pvalues";
    $$('#askQI #dialoguecontent')[0].parentNode.parentNode.appendChild(node);
    // add onclick handler for changing the value (IE won't accept onchange)
    var radiobuttons = $$('#askQI #dialoguecontent_pradio')[0].getElementsByTagName('input');
    for (var i = 0; i < radiobuttons.length; i++)
      radiobuttons[i].onclick = function() {
        qihelper.setPropertyRestriction();
      }
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
        $$('#askQI #usesub')[0].className = "";
        document.getElementsByName('input_r0')[2].disabled = "";
      } catch (e) {};
    }
    else {
      try {
        $$('#askQI #usesub')[0].className = "qiDisabled";
        document.getElementsByName('input_r0')[2].checked = false;
        document.getElementsByName('input_r0')[2].disabled = true;
      } catch (e) {};
    }
  },

  /**
   * toggles the add chain link
   */
  toggleAddchain : function(op) {
    if (!$$('#askQI #addchain')[0]) return;
    if (op) {
      var msg = $$('#askQI #dialoguecontent')[0].getElementsByTagName('input').length > 1
      ? gLanguage.getMessage('QI_ADD_PROPERTY_CHAIN')
      : gLanguage.getMessage('QI_CREATE_PROPERTY_CHAIN');
      $$('#askQI #addchain')[0].innerHTML =
      '<a href="javascript:void(0)" '
      + 'title="' + gLanguage.getMessage('QI_TT_ADD_CHAIN') + '" '
      + 'onclick="qihelper.addPropertyChainInput()">'
      + msg + '</a>';
    }
    else {
      $$('#askQI #addchain')[0].innerHTML = '';
    }
  },

  toggleShowProperty : function() {
    if ($$('#askQI #input_c1')[0].checked) {
      // default value for column name if not yet set at all
      if ( !('input_c3').value ) {
        $$('#askQI #input_c3')[0].value = this.propname;
      }
      $$('#askQI #input_c3d')[0].style.display = (Prototype.Browser.IE) ? 'inline' : null;
      if ($$('#askQI #input_c4')[0].getElementsByTagName('option').length > 0)
        $$('#askQI #input_c4d')[0].style.display = (Prototype.Browser.IE) ? 'inline' : null;
    } else {
      $$('#askQI #input_c3d')[0].style.display = 'none';
      $$('#askQI #input_c4d')[0].style.display = 'none'
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
    $$('#askQI #input0')[0].value = unescapeQueryHTML(cats[0]);
    for ( var i = 1; i < cats.length; i++) {
      this.addDialogueInput();
      $('input' + i).value = unescapeQueryHTML(cats[i]);
    }
    if (focus) $('input' + focus).focus();
    $$('#askQI #qidelete')[0].style.display = "inline"; // show delete button
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
    $$('#askQI #input0')[0].value = unescapeQueryHTML(ins[0]);
    for ( var i = 1; i < ins.length; i++) {
      this.addDialogueInput();
      $('input' + i).value = unescapeQueryHTML(ins[i]);
    }
    if (focus) $('input' + focus).focus();
    $$('#askQI #qidelete')[0].style.display = "inline";
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
    $$('#askQI #input_p0')[0].value=propChain[0];
    for (var i = 1, n = propChain.length; i < n; i++) {
      $$('#askQI #dialoguecontent')[0].rows[i * 2 - 1].cells[1].innerHTML =
      gLanguage.getMessage('QI_PROPERTY_TYPE') + ': ' +
      gLanguage.getMessage('QI_PAGE');
      this.addPropertyChainInput(propChain[i]);

    }
    this.propname = propChain[propChain.length - 1];
    this.completePropertyDialogue();
    // check box value must be set
    $$('#askQI #input_c2')[0].checked = prop.mustBeSet();

    // set correct property type under last property input
    var typeRow = $$('#askQI #dialoguecontent')[0].rows.length-2;
    if (this.proparity > 2) {
      $$('#askQI #dialoguecontent')[0].rows[typeRow].cells[1].innerHTML =
      gLanguage.getMessage('QI_PROPERTY_TYPE') + ': ' +
      gLanguage.getMessage('TYPE_RECORD') ;
      this.toggleSubquery(false);
    } else {
      // get type of property, if it's a subquery then type is page
      this.propTypename = (selector >= 0) ? gLanguage.getMessage('QI_PAGE') : vals[0][0];
      $$('#askQI #dialoguecontent')[0].rows[typeRow].cells[1].innerHTML =
      gLanguage.getMessage('QI_PROPERTY_TYPE') + ': ' + this.propTypename;
      if (this.propTypename != gLanguage.getMessage('QI_PAGE'))
        this.toggleSubquery(false);
      else
        this.toggleAddchain(true);
    }
    // set property is shown and colum name (these are empty for properties in subqueries)
    $$('#askQI #input_c1')[0].checked = prop.isShown(); // check box if appropriate
    $$('#askQI #input_c3')[0].value = prop.getColName();
    $$('#askQI #input_c3d')[0].style.display= prop.isShown()
    ? (Prototype.Browser.IE) ? 'inline' : null : 'none';

    // if we have a subquery set the selector correct and we are done
    if (selector >= 0) {
      document.getElementsByName('input_c1').disabled = "disabled";
      document.getElementsByName('input_r0')[2].checked = true;
      document.getElementsByName('input_r0')[2].value = selector;
      $$('#askQI #usesub_text')[0].style.display="block";
      this.toggleAddchain(false);
    }
    else {
      if (this.activeQueryId == 0) {
        if (prop.supportsUnits() && this.proparity == 2) {
          $$('#askQI #input_c4')[0].value = prop.getShowUnit();
          for (var i = 0; i < this.propUnits[0].length; i++) {
            $$('#askQI #input_c4')[0].options[i]=
            new Option(this.propUnits[0][i],this.propUnits[0][i]);
            if (prop.getShowUnit() == this.propUnits[0][i])
              $$('#askQI #input_c4')[0].options[i].selected = "selected";
          }
          $$('#askQI #input_c4d')[0].style.display= prop.isShown()
          ? Prototype.Browser.IE ? 'inline' : null : 'none';
        }
      } else {
        $$('#askQI #input_c1')[0].disabled = "disabled";
      }
      // if the selector is set to "restict value" then make the restictions visible
      if (selector == -2) {
        document.getElementsByName('input_r0')[1].checked = true;
        $$('#askQI #dialoguecontent_pvalues')[0].style.display = "inline";
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
        var newrow = $$('#askQI #dialoguecontent_pvalues')[0].insertRow(-1);
        var cell = newrow.insertCell(-1);
        cell.innerHTML = gLanguage.getMessage('QI_PROPERTYVALUE');
        rowOffset++;
      }
      for (var i = 0, n = vals.length; i < n; i++) {
        var numType = 0;
        var currRow = i + rowOffset;
        if (this.numTypes[vals[0][0].toLowerCase()]) { // is it a numeric type?
          numType = 1;
          this.propTypetype = '_num';
        }
        else if (vals[0][0] == gLanguage.getMessage('TYPE_STRING')) {
          numType = 2;
          this.propTypetype = '_str';
        }
        if (vals[0][0] == gLanguage.getMessage('TYPE_DATE')) {
          this.propTypetype = '_dat';
        }
        this.addRestrictionInput();
        $$('#askQI #dialoguecontent_pvalues')[0].rows[currRow].cells[1].innerHTML =
        this.createRestrictionSelector(vals[i][1], false, numType);
        this.observeSelectBoxChange(this);
        // deactivate autocompletion
        if (!acChange)
          autoCompleter.deregisterAllInputs();
        acChange = true;
                
        // add unit selection, do this for all properties, even in subqueries
        try {
          var propUnits = prop.getUnits();
          var uIdx = (this.proparity == 2) ? 0 : i;
          var oSelect = $$('#askQI #dialoguecontent_pvalues')[0].rows[currRow].cells[2]
          .firstChild.nextSibling;
          for (var k = 0, m = propUnits[uIdx].length; k < m; k++) {
            oSelect.options[k] = new Option(propUnits[uIdx][k], propUnits[uIdx][k]);
            if (propUnits[uIdx][k] == vals[i][3])
              oSelect.options[k].selected="selected";
          }
        } catch(e) {};
        if (this.proparity > 2) {
          $$('#askQI #dialoguecontent_pvalues')[0].rows[currRow].cells[0].innerHTML= vals[i][0];
          $$('#askQI #dialoguecontent_pvalues')[0].rows[currRow].cells[0].style.fontWeight="normal";
        }
        if (vals[i][2] != '*') // if a real value is set and not the placeholder for no value.
          $('input_r'+(i+1)).value = vals[i][2].unescapeHTML();
      }
      if (acChange) autoCompleter.registerAllInputs();
    }
    $$('#askQI #qidelete')[0].style.display = "inline";
		
    if (!prop.isEnumeration()) this.restoreAutocompletionConstraints();
  },
	
  restoreAutocompletionConstraints : function() {
    var idx = ($$('#askQI #dialoguecontent')[0].rows.length -1) / 2 - 1;
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
      var ranges = [];
      // parse all parameter names
      for ( var i = 0, n = schemaData.documentElement.childNodes.length; i < n; i++) {
        parameterNames
        .push(schemaData.documentElement.childNodes[i]
          .getAttribute("name"));
        parameterTypes
        .push(schemaData.documentElement.childNodes[i]
          .getAttribute("type"));
        var range = schemaData.documentElement.childNodes[i].getAttribute("range");
        if (range) ranges.push(range);
      }
                
      // Special treatment: binary properties support conjunction,
      // therefore we need an "add" button
      var idx = ($$('#askQI #dialoguecontent')[0].rows.length -1) / 2 - 1;
      var propertyName = $('input_p'+idx).value;
      propertyName = gLanguage.getMessage('PROPERTY_NS')+propertyName.replace(/\s/g, '_');
      this.propRange[propertyName.toLowerCase()]= ranges.join(',');
      var ac_constraint = "";
      if (parameterTypes[0] == '_wpg') {
        ac_constraint = (this.propRange[propertyName.toLowerCase()])
        ? 'annotation-value: ' : 'instance-property-range: '
        +propertyName+'|namespace: 0';
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
    var treeAnchor = $$('#askQI #treeanchor')[0];
    if(treeAnchor){
      var cells = treeAnchor.getElementsByTagName('td');
      for (i = 0; i < cells.length; i++) {
        if (cells[i].style.backgroundColor) {
          cells[i].style.backgroundColor = (Prototype.Browser.IE) ? '' : null;
          for (j = 0; j < cells[i].childNodes.length; j++) {
            if (cells[i].childNodes[j].style){
              cells[i].childNodes[j].style.color= (Prototype.Browser.IE ) ? '': null;
              cells[i].childNodes[j].style.fontWeight = 'normal';
            }
          }
        }
      }
      // now mark the clicked cell as selected
      //      el.parentNode.style.backgroundColor='#1122FF';
      el.parentNode.style.backgroundColor='#53bff5';
      for (i = 0; i < el.parentNode.childNodes.length; i++) {
        if (el.parentNode.childNodes[i].style){
          el.parentNode.childNodes[i].style.color='#FFFFFF';
          el.parentNode.childNodes[i].style.fontWeight = 'bold';
        }
      }
      var vals = label.split('-');
      this.setActiveQuery(vals[1]);
      if (vals[0] == 'category')
        this.loadCategoryDialogue(vals[2], vals[3]);
      else if (vals[0] == 'instance')
        this.loadInstanceDialogue(vals[2], vals[3]);
      else
        this.loadPropertyDialogue(vals[2]);
    }
  },

  /**
	 * Creates an HTML option with the different possible restrictions
	 * 
	 * @param disabled
	 *            enabled only for numeric datatypes
	 * @param type
   *            0 = other
   *            1 = numeric
   *            2 = string
	 */
  createRestrictionSelector : function(option, disabled, type) {
    var html = disabled ? '<select disabled="disabled">' : '<select>';
    var optionsFunc = function(op) {
			
      var escapeXMLEntities =  function(xml) {
        var result = xml.replace(/</g, '&lt;');
        result = result.replace(/>/g, '&gt;');
        return result;
      }
      if (! op) return;
      var selected = (op[0] == option) ? 'selected="selected"' : '';
      html += '<option value="'+escapeXMLEntities(op[0])+'" '+selected+'>'+op[2] + ' ('+escapeXMLEntities(op[1])+')</option>';
    }
    if (type == 1) {
      [   // internal operator, shown op to user, textual message
      ["=", "=", gLanguage.getMessage('QI_EQUAL') ],
      [">", ">=", gLanguage.getMessage('QI_GT') ],
      [">>", ">", gLanguage.getMessage('QI_GT') ],
      ["<", "<=", gLanguage.getMessage('QI_LT') ],
      ["<<", "<", gLanguage.getMessage('QI_LT') ],
      ["!", "!=", gLanguage.getMessage('QI_NOT') ]
      ].each(optionsFunc);
    } else if (type == 2) {
      [
      ["=", "=", gLanguage.getMessage('QI_EQUAL') ],
      ["!", "!=", gLanguage.getMessage('QI_NOT') ],
      ["~", "~", gLanguage.getMessage('QI_LIKE') ]
      ].each(optionsFunc);
    } else {
      [
      ["=", "=", gLanguage.getMessage('QI_EQUAL') ],
      ["!", "!=", gLanguage.getMessage('QI_NOT') ]
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
    this.updateSrcAndPreview()
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
    var inputs = $$('#askQI #dialoguecontent')[0].getElementsByTagName('input');
    for ( var i = 0; i < inputs.length; i++) {
      if (inputs[i].id && inputs[i].id.match(/^input\d+$/))
        tmp.push(escapeQueryHTML(inputs[i].value));
      if (inputs[i].value == "")
        allinputs = false;
    }
    if (!allinputs) { // show error
      $$('#askQI #qistatus')[0].innerHTML = (this.activeDialogue == "category")
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
        $$('#askQI #qistatus')[0].innerHTML = gLanguage.getMessage('QI_CAT_ADDED_SUCCESSFUL');
      }
      else {
        this.activeQuery.addInstanceGroup(tmp, this.loadedFromId);
        this.emptyDialogue();
        $$('#askQI #qistatus')[0].innerHTML = gLanguage.getMessage('QI_INST_ADDED_SUCCESSFUL');
      }
                        			
    }
  },

  /**
	 * Reads the input fields of a property dialogue and adds them to the query
	 */
  addPropertyGroup : function(updateGui) {
    // check if user clicked on add, while prop information is not yet loaded.
    this.propertyAddClicked = true;
    var typeRow = ($$('#askQI #dialoguecontent')[0].rows.length -2);
    if ($$('#askQI #dialoguecontent')[0].rows[typeRow].cells[1].innerHTML == gLanguage.getMessage('QI_PROPERTY_TYPE') + ':') return
    this.propertyAddClicked = false;

    var pname='';
    var propInputFields = $$('#askQI #dialoguecontent')[0].getElementsByTagName('input');
    for (var i = 0, n = propInputFields.length; i < n; i++) {
      pname += propInputFields[i].value + '.';
    }
    pname = pname.replace(/\.$/,'');
    var subqueryIds = Array();
    if (pname == "") { // no name entered?
      $$('#askQI #qistatus')[0].innerHTML = gLanguage
      .getMessage('QI_ENTER_PROPERTY_NAME');
      this.updateHeightBoxcontent();
    } else {
      var pshow = $$('#askQI #input_c1')[0].checked; // show in results?
      // when show in results is checked, add label and unit if they exist
      var colName = (pshow) ? $$('#askQI #input_c3')[0].value : null;
      var showUnit = (pshow) ? $$('#askQI #input_c4')[0].value : null;
      var pmust = $$('#askQI #input_c2')[0].checked; // value must be set?
      var arity = this.proparity;
      var selector = this.getPropertyValueSelector();
      // create propertyGroup
      var pgroup = new PropertyGroup(escapeQueryHTML(pname), arity,
        pshow, pmust, this.propIsEnum, this.enumValues, selector, showUnit, colName);
      pgroup.setUnits(this.propUnits);
      var allValueRows = $$('#askQI #dialoguecontent_pvalues')[0].rows.length;
      // there is no value restriction
      if (selector != -2) {
        var paramname = $$('#askQI #dialoguecontent')[0].rows[$$('#askQI #dialoguecontent')[0].rows.length -2].cells[1].innerHTML;
        paramname = paramname.replace(gLanguage.getMessage('QI_PROPERTY_TYPE') + ': ', '');
        // no subquery, so add a dumy value
        if (selector == -1) {
          if (arity == 2)
            pgroup.addValue(paramname, '=', '*');
          else {
            for (s = 1; s < arity; s++) {
              pgroup.addValue($$('#askQI #dialoguecontent_pvalues')[0].rows[s].cells[0].innerHTML, '=', '*');
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
            var paramvalue = $$('#askQI #dialoguecontent_pvalues')[0].rows[i].cells[2].firstChild.value;
          } catch (e) {
            continue;
          }
          // no value is replaced by "*" which means all values
          paramvalue = paramvalue == "" ? "*" : paramvalue;
          var paramname;
          if (arity == 2) {
            paramname = $$('#askQI #dialoguecontent')[0].rows[$$('#askQI #dialoguecontent')[0].rows.length -2].cells[1].innerHTML;
            paramname = paramname.replace(gLanguage.getMessage('QI_PROPERTY_TYPE') + ': ', '');
          }
          else {
            paramname = $$('#askQI #dialoguecontent_pvalues')[0].rows[i].cells[0].innerHTML;
          }
          var restriction = $$('#askQI #dialoguecontent_pvalues')[0].rows[i].cells[1].firstChild.value;
          var unit = null;
          try {
            unit = $$('#askQI #dialoguecontent_pvalues')[0].rows[i].cells[2].firstChild.nextSibling.value;
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
      $$('#askQI #qistatus')[0].innerHTML = gLanguage.getMessage('QI_PROP_ADDED_SUCCESSFUL')
      // if the property contains a subquery, set the active query now to this subquery
      if (selector > 0) this.setActiveQuery(selector);
    }

    // update tree and query preview if this has not yet happened
    if ( updateGui ) {
      this.updateTree();
      this.updateSrcAndPreview()
      this.updateBreadcrumbs(this.activeQueryId);
      this.loadedFromID = null;
    }
  },

  switchTab : function(id, flush) {
    var divcontainer = ['treeview', '', 'qisource'];
    if (!flush) {
      // user selected the source tab, convert query to source code
      if (id == 3) {
        this.showFullAsk('parser', false);
        $$('#askQI #query4DiscardChanges')[0].innerHTML = escapeQueryHTML($$('#askQI #fullAskText')[0].value);
      }
      // user selected the tree tab, load the query from source
      else {
        this.loadFromSource();
        $$('#askQI #query4DiscardChanges')[0].innerHTML = "";
      }
    }
    for (var i = 0; i < divcontainer.length; i++) {
      if (divcontainer[i].length == 0)
        continue;
      var qiDefTab = $$('#askQI #qiDefTab' + (i+1))[0];
      if(qiDefTab){
        if (id == i+1){
          qiDefTab.className='qiDefTabActive';
          $$('#askQI #' + divcontainer[i])[0].style.display='inline';
        }
        else {
          qiDefTab.className='qiDefTabInactive';
          $$('#askQI #' +divcontainer[i])[0].style.display='none';
        }
      }
    }

    this.enableResetQueryButton();
  },

  switchMainTab : function(noreset) {
    // change the tabs and visibility and copy the query tree at the correct position
    if ($$('#askQI #qiMainTab1')[0].className == 'qiDefTabActive') {
      $$('#askQI #qiMainTab1')[0].className = 'qiDefTabInactive';
      $$('#askQI #qiMainTab2')[0].className = 'qiDefTabActive';
      $$('#askQI #qiMaintabQueryCont')[0].style.display = 'none';
      $$('#askQI #qiMaintabLoadCont')[0].style.display = '';
      var treeContent = $$('#askQI #qiDefTab')[0].innerHTML;
      $$('#askQI #qiDefTab')[0].innerHTML = '';
      $$('#askQI #qiDefTabInLoad')[0].innerHTML = treeContent;
      $$('#askQI #qisourceButtons')[0].style.display = 'none';
      // save original query
      if (!this.queries[0].isEmpty())
        QIHelperSavedQuery = this.getFullParserAsk();
      // if there was a previous search and results, reset the selected
      // query and tree but keep the results
      if (this.queryList)
        this.queryList.selectRow();
      $$('#askQI #qiLoadConditionTerm')[0].focus();
    }
    else {
      $$('#askQI #qiMainTab2')[0].className = 'qiDefTabInactive';
      $$('#askQI #qiMainTab1')[0].className = 'qiDefTabActive';
      $$('#askQI #qiMaintabQueryCont')[0].style.display = '';
      $$('#askQI #qiMaintabLoadCont')[0].style.display = 'none';
      var treeContent = $$('#askQI #qiDefTabInLoad')[0].innerHTML;
      $$('#askQI #qiDefTabInLoad')[0].innerHTML = '';
      $$('#askQI #qiDefTab')[0].innerHTML = treeContent;
      $$('#askQI #qisourceButtons')[0].style.display = '';
      if (QIHelperSavedQuery)
        this.initFromQueryString(QIHelperSavedQuery);
      else if (! noreset)
        this.doReset();
    }
  },

  searchQueries : function() {
    this.queryList = new QIList();
    this.queryList.search();
  },

  resetSearch : function() {
    this.queryList = new QIList();
    this.queryList.reset();
    $$('#askQI #qiLoadConditionTerm')[0].focus();
  },

  loadSelectedQuery : function() {
    QIHelperSavedQuery = null; // purge saved query to use the current loaded one
    this.switchMainTab(true);  // do not trigger a reset
    this.updateTree();         // but update tree to get the node links
  },

  updateSearchAc : function() {
    var constraint,
    oldConstr = $$('#askQI #qiLoadConditionTerm')[0].getAttribute("constraints");
    switch ($$('#askQI #qiLoadCondition')[0].value) {
      case 'p':
      case 's':
        constraint = 'namespace: 102';
        break;
      case 'c':
        constraint = 'namespace: 14';
        break;
      case 'i':
        constraint = 'namespace: 0';
        break;
      case '*':
        constraint = 'namespace: 14,102,0';
        break;
    }
    if (constraint != oldConstr) {
      autoCompleter.deregisterAllInputs();
      if (constraint) {
        $$('#askQI #qiLoadConditionTerm')[0].setAttribute("constraints", constraint);
        $$('#askQI #qiLoadConditionTerm')[0].addClassName('wickEnabled');
      }
      else {
        $$('#askQI #qiLoadConditionTerm')[0].removeAttribute("constraints");
        $$('#askQI #qiLoadConditionTerm')[0].removeClassName('wickEnabled');
      }
      autoCompleter.registerAllInputs();
    }
  },

  discardChangesOfSource : function() {
    $$('#askQI #fullAskText')[0].value =   $$('#askQI #query4DiscardChanges')[0].innerHTML.unescapeHTML();
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
    } else if (($$('#askQI #layout_format')[0].value == "template")
      && ($$('#askQI #template_name')[0].value == "")) {
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

  /**
	 * run when user switches to "Query source"
	 */
  showFullAsk : function(type, toggle) {
    if (toggle) {
      $$('#askQI #shade')[0].toggle();
      $$('#askQI #showAsk')[0].toggle();
    }
    if (this.queries[0].isEmpty()) {
      //if (!this.isExcelBridge)
      $$('#askQI #fullAskText')[0].value = '';
      return;
    } else if (($$('#askQI #layout_format')[0].value == "template")
      && ($$('#askQI #template_name')[0].value == "")) {
      $$('#askQI #fullAskText')[0].value = '';
      alert(gLanguage.getMessage('QI_EMPTY_TEMPLATE'));
      return;
    }
    var ask = this.getFullParserAsk();
    ask = ask.replace(/\]\]\[\[/g, "]]\n[[");
    ask = ask.replace(/>\[\[/g, ">\n[[");
    ask = ask.replace(/\]\]</g, "]]\n<");
    if (type == "parser")
      ask = ask.replace(/([^\|]{1})\|{1}(?!\|)/g, "$1\n|");
    $$('#askQI #fullAskText')[0].value = ask;
    this.queryFormated = true;
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
    $$('#askQI #shade')[0].toggle();
    $$('#askQI #savedialogue')[0].toggle();
  },

  doSave : function() {
    if (!this.queries[0].isEmpty()) {
      if (this.pendingElement)
        this.pendingElement.remove();
      this.pendingElement = new OBPendingIndicator($$('#askQI #savedialogue')[0]);
      this.pendingElement.show();
      var params = $$('#askQI #saveName')[0].value + ",";
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
      $$('#askQI #shade')[0].toggle();
      $$('#askQI #savedialogue')[0].toggle();
      $$('#askQI #saveName')[0].value = "";
    } else if (request.responseText == "exists") {
      alert(gLanguage.getMessage('QI_QUERY_EXISTS'));
      $$('#askQI #saveName')[0].value = "";
    } else if (request.responseText == "true") {
      alert(gLanguage.getMessage('QI_QUERY_SAVED'));
      $$('#askQI #shade')[0].toggle();
      $$('#askQI #savedialogue')[0].toggle();
      $$('#askQI #saveName')[0].value = "";
    } else { // Unknown error
      alert(gLanguage.getMessage('QI_SAVE_ERROR'));
      $$('#askQI #shade')[0].toggle();
      $$('#askQI #savedialogue')[0].toggle();
    }
  },

  exportToXLS : function() {
    if (!this.queries[0].isEmpty()) {
      var ask = this.recurseQuery(0);
      var params = ask + ",";
      params += $$('#askQI #layout_format')[0].value + ',';
      params += $$('#askQI #layout_sort')[0].value == "" ? ","
      : $$('#askQI #layout_sort')[0].value + ',';
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
    this.getSpecialQPParameters($$('#askQI #layout_format')[0].value);
    this.updateSrcAndPreview();
  },
    
  /**
   * called when the Query Tree tab is clicked and the Query source tab is still active
   */
  loadFromSource : function(noTabSwitch) {
    this.noTabSwitch = noTabSwitch;
    if ($$('#askQI #qiDefTab3')[0].className.indexOf('qiDefTabActive') > -1 &&
      $$('#askQI #fullAskText')[0].value.length > 0 &&
      this.sourceChanged)
      this.initFromQueryString($$('#askQI #fullAskText')[0].value);
    if ($$('#askQI #fullAskText')[0].value.length == 0)
      $$('#askQI #previewcontent')[0].innerHTML = "";
  },

  initFromQueryString : function(ask) {
    this.doReset();
    jQuery('#askQI #fullAskText').val(ask);
    // does ask contain any data?
    if (ask.replace(/^\s+/, '').replace(/\s+$/, '').length == 0)
      return;
        
    // check triplestore switch if it comes from sparql parser function
    if (ask.indexOf('#sparql:') != -1) {
      // check if this is really a sparq query and add send a warning to the user
      if (ask.match(/\sselect\s/i)) {
        alert (gLanguage.getMessage('QI_SPARQL_NOT_SUPPORTED'));
        return;
      }
      var triplestoreSwitch = $$('#askQI #usetriplestore')[0];
      if (triplestoreSwitch && ask.match(/source\s*=\s*tsc/im)){
        triplestoreSwitch.checked = true;
      }
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
        pchain = pchain.firstUpperCase();
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
        pchain = pchain.firstUpperCase();
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
      this.savePropertyInformation(request.responseText);
    }
    this.parseQueryString();
  },

  trimArray: function(array){
    if(!array){
      return array;
    }
    for(var i = 0; i < array.length; i++){
      if(array[i] === ''){
        array.splice(i, 1);
        i--;
      }
    }
    return array;
  },

  parseQueryString : function() {
    var sub = this.queryPartsFromInitByAsk;

    // properties that must be shown in the result
    var pMustShow = this.applyOptionParams(sub[0]);

    // run over all query strings and start parsing
    for (var f = 0; f < sub.length; f++) {
      // set current query to active, do this manually (treeview is not
      // updated)
      this.activeQuery = this.queries[f];
      this.activeQueryId = f;

      // merge something like this [[Category:X]] | [[PropX::+]]
      // but also escape double || like in [[Category:X||Y]]
      var tmp = sub[f].replace(/\|\|/g, '%%!!%%').split('|');
      tmp = this.trimArray(tmp);
      tmp2 = [ '' ];

      for (var t = 0; t < tmp.length; t++) {
        if (tmp[t].match(/^\s*\[\[/) && tmp[t].match(/\]\]\s*$/))
          tmp2[0] += tmp[t];
        else
          tmp2.push(tmp[t]);
      }
      // join elements again and revert escaping of ||
      sub[f] = tmp2.join('|').replace(/%%!!%%/g, '||');

      // extact the arguments, i.e. all between [[...]]
      var args = [];
      var regexPattern = new RegExp('\\[\\[([^\\]]+)\\]\\]', 'gm');
      var match;
      while(match = regexPattern.exec(sub[f])){
        args.push(match[1]);
      }

      this.handleQueryString(args, f, pMustShow);
    }
    this.setActiveQuery(0); // set main query to active
    this.updateTree();      // show new tree
    this.updateColumnPreview(); // update sort selection
    this.updatePreview(); // update result preview
  },
    
  savePropertyInformation : function(xml) {
    var xmlDoc = GeneralXMLTools
    .createDocumentFromString(xml);
    var prop = xmlDoc.getElementsByTagName('relationSchema');
    var returnedPropNames = [];
    for ( var i = 0; i < prop.length; i++) {
      var pname = prop.item(i).getAttribute('name');
      returnedPropNames.push(pname);
      var arity = parseInt(prop.item(i).getAttribute('arity'));
      var ptype = prop.item(i).getElementsByTagName('param')[0]
      .getAttribute('name');
      var noval = new Array();
      var enumValues = [];
      var unitVals = new Array();
      var prange = prop.item(i).getElementsByTagName('param')[0]
      .getAttribute('range');
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
          if (!this.numTypes[ptype.toLowerCase()])
            this.numTypes[ptype.toLowerCase()]= true;
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
      this.propertyTypesList.add(pname, pgroup, [], ptype, prange);
    }
    return returnedPropNames;
  },
  handleQueryString : function(args, queryId, pMustShow) {

    // list of properties (each property has an own pgoup)
    var propList = new PropertyList();
    var operators= ['<', '<<', '>', '>>', '~', '!', '!~', '≤', '≥'];

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
        var pchain = pname.split('.');
        pchain = pchain.firstUpperCase();
        pname = pchain[pchain.length - 1];
        args[i] = pchain.join('.') + '::' + pval;

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
            if (pMustShow[j][0] == pchain.join('.')) {
              pshow = true;
              unit = pMustShow[j][1];
              column = pMustShow[j][2];
              break;
            }
          }
        }
        // must be set?
        var pmust = args.inArray(pchain.join('.') + '::+');
        var arity = propdef ? propdef.getArity() : 2;
        var isEnum = propdef ? propdef.isEnumeration() : false;
        var enumValues = propdef ? propdef.getEnumValues() : [];
        // create propertyGroup
        var pgroup = new PropertyGroup(escapeQueryHTML(pchain.join('.')), arity,
          pshow, pmust, isEnum, enumValues, null, unit, column);
        if (arity > 2) {
          var naryVals = propdef.getValues();
          for (e = 0; e < naryVals.length; e++)
            pgroup.addValue(naryVals[e][0], naryVals[e][1], naryVals[e][2]);
        }
        pgroup.setUnits(propdef.getUnits());

        var subqueryIds = propList.getSubqueryIds(propList.getIndex(pchain.join('.')));
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
          this.insertQuery(paramvalue, queryId, pchain.join('.'));
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
              if (args[k].indexOf(pchain.join('.')) != -1 && args[k] != pchain.join('.') + '::+')
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
              var op = vals[j].match(/^([\!|<|>|=|~]{0,2})(.*)/);
              if (op[1].length > 0) {
                if (operators.inArray(op[1])) {
                  switch (op[1]) {
                    case '≤':
                      restriction= '<';
                      break
                    case '≥':
                      restriction= '>';
                      break
                    default :
                      restriction= op[1];
                  }
                }
                else
                  restriction= ''; // default value for any invalid operator
                paramvalue = op[2];
              }
              else {
                paramvalue = vals[j];
                restriction = '=';
              }
              // check for a unit
              var paramunit = "";
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
              var op = vals[j].match(/^([\!|<|>|=|~]{0,2})(.*)/);
              if (op[1].length > 0) {
                if (operators.inArray(op[1])) {
                  switch (op[1]) {
                    case '≤':
                      restriction= '<';
                      break
                    case '≥':
                      restriction= '>';
                      break
                    default :
                      restriction= op[1];
                  }
                }
                else
                  restriction= ''; // default value for any invalid operator
                paramvalue = op[2];
              }
              else
                paramvalue = vals[j];
              // no value or '+' (must set) is replaced, by "*" which means all values
              if (paramvalue == "" || paramvalue == '+') paramvalue = "*";
              // if j > 0 conjunction: page/type = val1 'or' valX
              if (j > 0) paramname = gLanguage.getMessage('QI_OR');
              // check for a unit
              var paramunit = "";
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
        propList.addNew(pchain.join('.'), pgroup, subqueryIds); // add current property to property list
      }
    }

    // if a property must be shown in results only, it may not appear in the
    // [[...]] part but only as |?myprop in the printout
    // therefore check now that in the main query we also have all "must show"
    // properties included
    var propMerged = new Array();
    if (queryId == 0) { // do this only for the main query, subqueries have no printouts
      for ( var i = 0; i < pMustShow.length; i++) { // loop over all properties to show
        var pgroup;
        // get information about the property itself
        // split of propname
        var pchain = pMustShow[i][0].split('.');
        pchain = pchain.firstUpperCase();
        var plocname = pchain[pchain.length - 1];
        var defPgroup = this.propertyTypesList.getPgroup(plocname);
        var ptype = gLanguage.getMessage('QI_PAGE');
        // use the definition, like enum values and arity from the ajax
        // call when querying the property types
        if (defPgroup) {
          ptype = this.propertyTypesList.getType(plocname);
          pgroup = new PropertyGroup(escapeQueryHTML(pMustShow[i][0]),
            defPgroup.getArity(), true, false,
            defPgroup.isEnumeration(), defPgroup.getEnumValues(), -1,
            pMustShow[i][1], pMustShow[i][2]);
          pgroup.setUnits(defPgroup.getUnits());
          pgroup.addValue(ptype, '=', '*'); // add default values
        }
        else // create property group with default values
          pgroup = new PropertyGroup(escapeQueryHTML(pMustShow[i][0]),
            2, true, false, null, null, -1,
            pMustShow[i][1], pMustShow[i][2]);
            
        // we have this property for the first time and it exists also in the query condition
        // then merge the conditions and must show settings into one property group
        var oldPgroup = propList.getPgroup(pMustShow[i][0]); // get information about property
        var subqueryIds = [];
        if (oldPgroup != null && ! propMerged[ pMustShow[i][0] ]) {
          pgroup.setValues(oldPgroup.getValues());
          pgroup.setSelector(oldPgroup.getSelector());
          pgroup.setMustBeSet(oldPgroup.mustBeSet());
          subqueryIds = oldPgroup.getSubqueryIds();
        }
        // add current property to property list
        if (propMerged[ pMustShow[i][0] ] == null ) // property does not exist yet
          propList.add(pMustShow[i][0], pgroup, subqueryIds, ptype);
        else
          propList.addNew(pMustShow[i][0], pgroup, subqueryIds, ptype);
        // and remember now that this porperty appeared in the printouts
        propMerged[ pMustShow[i][0] ] = true;
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
    $$('#askQI #qiQueryName')[0].value = ''; // reset query name
    var format = "table"; // default format
    var tpeeParamsObj = new Object(); // empty policy params
    var tpeePolicyId = ''; // empty name of TPEE
    var useTscSwitch = false;
    // selector of TPEE or DS
    var selectorDsTpee = ( $$('#askQI #qioptioncontent')[0] )
    ? $$('#askQI #qioptioncontent')[0].getElementsBySelector('[name="qiDsTpeeSelector"]') : [];
    for ( var i = 1; i < options.length; i++) {
      // check for additionl printouts like |?myProp
      var m = options[i].match(/^\s*\?/)
      if (m) {
        m = options[i].replace(/\r?\n/g,'').match(/^(.*?)(#.*?)?(=.*?)?$/);
        var pname = m[1].replace(/^\s*\?\s*/, '').replace(/\s*$/,'');
        pname = pname.substr(0, 1).toUpperCase() + pname.substr(1); // fist upper case
        var punit = (m[2]) ? m[2].replace(/#/,'').replace(/\s*$/,'') : null;
        var col = (m[3]) ? m[3].replace(/=\s*/,'').replace(/\s*$/,'') : null;
        if (col == null) { // set default column value if not set.
          var pchain = pname.split('.');
          col = pchain[pchain.length -1];
          col = col.substr(0, 1).toUpperCase() + col.substr(1); // fist upper case
        }
        mustShow.push([pname, punit, col]);
        continue;
      }
      // check for key value pairs like format=table
      var kv = options[i].replace(/^\s*(.*?)\s*$/, '$1').split(/=/);
        if (kv.length == 1)
          continue;
            
        var key = kv[0].replace(/^\s*(.*?)\s*$/, '$1');
        var val = kv[1].replace(/^\s*(.*?)\s*$/, '$1');
        if (key=="format")
          format = val;
        else if (key=="sort")
          this.sortColumn = val;
        else if (key=='queryname')
          $$('#askQI #qiQueryName')[0].value = val;
        else if ( key == "enableRating" && $$('#askQI #qio_showrating')[0] )
          $$('#askQI #qio_showrating')[0].checked = "checked";
        else if ( key == "metadata" && $$('#askQI #qio_showmetadata')[0] ) {
          $$('#askQI #qio_showmetadata')[0].checked = "checked";
          $$('#askQI #qio_showmetadata')[0].value = val;
        }
        else if ( key == 'source' ) {
          useTscSwitch = ( val == 'tsc' ) ? true : false;
        }
        else if (key == "dataspace" && $$('#askQI #qidatasourceselector')[0] ) {
          useTscSwitch = true;
          var dsVals = val.split(',');
          for (var s= 0; s < $$('#askQI #qidatasourceselector')[0].length; s++ ) {
            $$('#askQI #qidatasourceselector')[0].options[s].selected = null;
            for (var t= 0; t < dsVals.length; t++) {
              if (dsVals[t].replace(/^\s*(.*?)\s*$/, '$1') == $$('#askQI #qidatasourceselector')[0].options[s].value )
                $$('#askQI #qidatasourceselector')[0].options[s].selected = "selected";
            }
          }
        }
        // trust policy settings
        else if (key == "policyid" && selectorDsTpee.length > 0 ) {
          useTscSwitch = true;
          tpeePolicyId = val;
          this.selectDsTpee(this.TPEE_SELECTED, true);
          for (var s= 0; s < $$('#askQI #qitpeeselector')[0].options.length; s++ ) {
            if ( val == $$('#askQI #qitpeeselector')[0].options[s].value ) {
              $$('#askQI #qitpeeselector')[0].options[s].selected = "selected";
              $('qitpeeparams_'+val).style.display = 'inline';
            }
            else {
              var divId = 'qitpeeparams_'+$$('#askQI #qitpeeselector')[0].options[s].value;
              $$('#askQI #qitpeeselector')[0].options[s].selected = null;
              $(divId).style.display = 'none';
            }
          }
          $('qitpeeparams_'+tpeePolicyId).style.display = 'inline';
        }
        else if (key == "policyparams" ) {
          val = val.replace(/^\s*/,'').replace(/\s*$/, '');
          tpeeParamsObj = JSON.parse(val);
        //once jQuery 1.4.1 is available use this line instead
        //tpeeParamsObj = jQuery.parseJSON(val);
        }

      }
    if ( tpeeParamsObj ) {
      for ( var parname in tpeeParamsObj ) {
        // PAR_USER -> remove the graph URI from the user name
        if ( parname == 'PAR_USER' ) {
          var wikigraph = $$('#askQI #qi_tsc_wikigraph')[0].innerHTML || '';
          var userns = $$('#askQI #qi_tsc_userns')[0].innerHTML || '';
          tpeeParamsObj[parname] = tpeeParamsObj[parname].replace(wikigraph + '/' + userns + '/', '');
        }
        else if ( parname == 'PAR_ORDER') {
          var table = $('qitpeeparamval_' + tpeePolicyId + '_PAR_ORDER');
          if (table) {
            var orderValues = [];
            var parOrderArr = JSON.parse(tpeeParamsObj.PAR_ORDER);
            for (var t = 0; t < table.rows.length; t++) {
              orderValues.push(table.rows[t].cells[0].getAttribute('_sourceid'));
              table.deleteRow(t);
              t--;
            }
            for (var t = 0; t < parOrderArr.length; t++ ) {
              if (orderValues.inArray(parOrderArr[t])) {
                var row = table.insertRow(-1);
                var cell = row.insertCell(-1);
                cell.setAttribute('onclick', "qihelper.tpeeOrderSelect(this)");
                cell.setAttribute('_sourceid', parOrderArr[t]);
                cell.innerHTML = parOrderArr[t];
              }
            }
            for (var t = 0; t < orderValues.length; t++) {
              if (! parOrderArr.inArray(orderValues[t])) {
                var row = table.insertRow(-1);
                var cell = row.insertCell(-1);
                cell.setAttribute('onclick', "qihelper.tpeeOrderSelect(this)");
                cell.setAttribute('_sourceid', orderValues[t]);
                cell.innerHTML = orderValues[t];
              }
            }
          }
        }
        if ( $('qitpeeparamval_' + tpeePolicyId + '_' + parname) )
          $('qitpeeparamval_' + tpeePolicyId + '_' + parname).value = tpeeParamsObj[parname];
      }
    }
    if ( $$('#askQI #usetriplestore')[0] )
      $$('#askQI #usetriplestore')[0].checked= (useTscSwitch) ? "checked" : null;

    // The following callback is called after the query printer parameters were displayed.
    var callback = function() {
      var qpChanged = false;
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
            var layout_format = $$('#askQI #layout_format')[0];
            layout_format.value = val;
            qpChanged = true;
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
      if (qpChanged) this.updatePreview();
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
      this.values = Array(); // paramName, restriction, paramValue, unitOfvalue
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

    setMustBeSet : function(val) {
      this.must = val;
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
    },
    getSubqueryIds : function() {
      var ids = [];
      for (var i = 0; i < this.values.length; i++ ) {
        if (this.values[i][0] == 'subquery')
          ids.push(this.values[i][2]);
      }
      return ids;
    }
  }

  var PropertyList = Class.create();
  PropertyList.prototype = {

    initialize : function() {
      this.name = Array();
      this.pgroup = Array();
      this.subqueries = Array();
      this.type = Array();
      this.range = Array();
      this.length = 0;
    },

    add : function(name, pgroup, subqueries, type, range) {
      for ( var i = 0; i < this.name.length; i++) {
        if (this.name[i] == name) {
          this.pgroup[i] = pgroup;
          this.subqueries[i] = (subqueries) ? subqueries : [];
          this.type[i] = type;
          this.range[i] = (range) ? range : "";
          return;
        }
      }
      this.addNew(name, pgroup, subqueries, type, range);
    },
    addNew : function(name, pgroup, subqueries, type, range) {
      this.name.push(name);
      this.pgroup.push(pgroup);
      this.subqueries.push((subqueries) ? subqueries : []);
      this.type.push(type);
      this.range.push(range);
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
	
    getRange : function(name) {
      for ( var i = 0; i < this.name.length; i++) {
        if (this.name[i] == name)
          return this.range[i];
      }
    },

    supportsUnits : function(name) {
      for ( var i = 0; i < this.name.length; i++) {
        if (this.name[i] == name)
          return this.pgroup[i].supportsUnits();
      }
    }
  }

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
  if (!Array.prototype.firstUpperCase) {
    Array.prototype.firstUpperCase = function() {
      var data = [];
      for ( var i = 0; i < this.length; i++ ) {
        data.push( this[i].substr(0, 1).toUpperCase() +
          this[i].substr(1) );
      }
      return data;
    }
  }

