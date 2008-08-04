/** *****************************************************************************
*  Query Interface for Semantic MediaWiki
*  Developed by Markus Nitsche <fitsch@gmail.com>
*
*  QIHelper.js
*  Manages major functionalities and GUI of the Query Interface
*  @author Markus Nitsche [fitsch@gmail.com]
*/

var qihelper = null;

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
* Called whenever table column preview is minimized or maximized
*/
switchtcp:function(){
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
},

/**
* Called whenever query layout manager is minimized or maximized
*/
switchlayout:function(){
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
},

/**
* Performs ajax call on startup to get a list of all numeric datatypes.
* Needed to find out if users can use operators (< and >)
*/
getNumericDatatypes:function(){
	sajax_do_call('smwf_qi_QIAccess', ["getNumericTypes", "dummy"], this.setNumericDatatypes.bind(this));
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
	    smwhgLogger.log("Reset Query","QI","query_reset");
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
	    smwhgLogger.log("Preview Query","QI","query_preview");
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
		sajax_do_call('smwf_qi_QIAccess', ["getQueryResult", params], this.openPreview.bind(this));
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
	smw_tooltipInit();
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
	var asktext = this.recurseQuery(0, "ask");
	//get Layout parameters
	var starttag = "<ask "; //create ask tags and display params
	starttag += 'format="' + $('layout_format').value + '" ';
	starttag += $('layout_link').value == "subject" ? "" : ('link="' + $('layout_link').value + '" ');
	starttag += $('layout_intro').value == "" ? "" : ('intro="' + $('layout_intro').value + '" ');
	starttag += $('layout_sort').value == gLanguage.getMessage('QI_ARTICLE_TITLE') ? "" : ('sort="' + $('layout_sort').value + '" ');
	starttag += $('layout_limit').value == "" ? '' : ('limit="' + $('layout_limit').value + '" ');
	starttag += $('layout_label').value == "" ? "" : ('mainlabel="' + $('layout_label').value + '" ');
	starttag += $('layout_order').value == "ascending" ? '' : 'order="descending" ';
	starttag += $('layout_headers').checked ? '' : 'headers="hide" ';
	starttag += $('layout_default').value == "" ? '' : 'default="' + $('layout_default').value +'" ';
	if ($('layout_format').value == "template"){
		starttag += 'template="' + $('template_name').value + '" ';
	} else if ($('layout_format').value == "rss"){
		starttag += $('rsstitle').value == "" ? '' : 'rsstitle="' + $('rsstitle').value + '" ';
		starttag += $('rssdescription').value == "" ? '' : 'rssdescription="' + $('rssdescription').value + '" ';
	}
	starttag += ">";
	return starttag + asktext + "</ask>";
},

getFullParserAsk:function(){
	var asktext = this.recurseQuery(0, "parser");
	var displays = this.queries[0].getDisplayStatements();
	var fullQuery = "{{#ask: " + asktext;
	for(var i=0; i<displays.length; i++){
		fullQuery += "| ?" + displays[i];
	}
	fullQuery += ' | format=' + $('layout_format').value;
	fullQuery += $('layout_link').value == "subject" ? "" : (' | link=' + $('layout_link').value);
	fullQuery += $('layout_intro').value == "" ? "" : (' | intro=' + $('layout_intro').value);
	fullQuery += $('layout_sort').value == gLanguage.getMessage('QI_ARTICLE_TITLE') ? "" : (' | sort=' + $('layout_sort').value);
	fullQuery += $('layout_limit').value == "" ? '' : (' | limit=' + $('layout_limit').value);
	fullQuery += $('layout_label').value == "" ? "" : (' | mainlabel=' + $('layout_label').value);
	fullQuery += $('layout_order').value == "ascending" ? '' : ' | order=descending ';
	fullQuery += $('layout_headers').checked ? '' : ' | headers=hide ';
	fullQuery += $('layout_default').value == "" ? '' : ' | default=' + $('layout_default').value;
	if ($('layout_format').value == "template"){
		fullQuery += ' | template=' + $('template_name').value;
	} else if ($('layout_format').value == "rss"){
		fullQuery += $('rsstitle').value == "" ? '' : ' | rsstitle=' + $('rsstitle').value;
		fullQuery += $('rssdescription').value == "" ? '' : ' | rssdescription=' + $('rssdescription').value;
	}
	fullQuery += "|}}";
	
	return fullQuery;
},

insertAsNotification: function() {
	var query = this.recurseQuery(0);
	document.cookie = "NOTIFICATION_QUERY=<snq>"+query+"</snq>";
	if (query != "") {
		var snPage = $('qi-insert-notification-btn').readAttribute('specialpage');
		snPage = unescape(snPage);
		location.href = snPage;
		//window.open(snPage), "_blank");
	}
	
},

/**
* Recursive function that creates the ask syntax for the query with the ID provided
* and all its subqueries
* @param id ID of query to start
*/
recurseQuery:function(id, type){
	var sq = this.queries[id].getSubqueryIds();
	if(sq.length == 0){
		if(type == "parser")
			return this.queries[id].getParserAsk();
		else
			return this.queries[id].getAskText(); // no subqueries, get the asktext
	}
	else {
		if(type == "parser"){
			var tmptext = this.queries[id].getAskText();
			for(var i=0; i<sq.length; i++){
				var regex = null;
				eval('regex = /Subquery:' + sq[i] + ':/g'); //search for all Subquery tags and extract the ID
				tmptext = tmptext.replace(regex, '<q>' + this.recurseQuery(sq[i], "ask") + '</q>'); //recursion
			}
			return tmptext;
		} else {
			var tmptext = this.queries[id].getParserAsk();
			for(var i=0; i<sq.length; i++){
				var regex = null;
				eval('regex = /Subquery:' + sq[i] + ':/g'); //search for all Subquery tags and extract the ID
				tmptext = tmptext.replace(regex, '<q>' + this.recurseQuery(sq[i], "parser") + '</q>'); //recursion
			}
			return tmptext;
		}
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
	cell.style.textAlign = "left";
	cell.setAttribute("colSpan",2);
	cell.innerHTML = '<input type="text" id="input0" class="wickEnabled general-forms" typehint="102" autocomplete="OFF" onblur="qihelper.getPropertyInformation()"/>';

	newrow = $('dialoguecontent').insertRow(-1); // second row: checkbox for display option
	cell = newrow.insertCell(0);
	cell.innerHTML = gLanguage.getMessage('QI_SHOW_PROPERTY');
	cell = newrow.insertCell(1);
	cell.style.textAlign = "left";
	cell.setAttribute("colSpan",2);
	if(this.activeQueryId == 0)
		cell.innerHTML = '<input type="checkbox" id="input1">';
	else
		cell.innerHTML = '<input type="checkbox" disabled="disabled" id="input1">';

	newrow = $('dialoguecontent').insertRow(-1); // second row: checkbox for display option
	cell = newrow.insertCell(0);
	cell.innerHTML = gLanguage.getMessage('QI_PROPERTY_MUST_BE_SET');
	cell = newrow.insertCell(1);
	cell.style.textAlign = "left";
	cell.setAttribute("colSpan",2);
	cell.innerHTML = '<input type="checkbox" id="input2">';
	
	newrow = $('dialoguecontent').insertRow(-1); // third row: input for property value and subquery
	cell = newrow.insertCell(0);
	cell.id = "mainlabel";
	cell.innerHTML = gLanguage.getMessage('QI_PAGE'); // we assume Page as type since this is standard
	cell = newrow.insertCell(1);
	cell.id = "restricionSelector";
	cell.innerHTML = this.createRestrictionSelector("=", true);
	cell = newrow.insertCell(2);
	cell.innerHTML = '<input class="wickEnabled general-forms" typehint="0" autocomplete="OFF" type="text" id="input3"/>';
	cell = newrow.insertCell(3);
	cell.innerHTML = '<img src="' + this.imgpath + 'add.png" alt="addPropertyInput" onclick="qihelper.addDialogueInput()"/>';
	cell = newrow.insertCell(4);
	cell.className = "subquerycell";
	cell.innerHTML = '&nbsp;' + gLanguage.getMessage('QI_USE_SUBQUERY') + '<input type="checkbox" id="usesub" onclick="qihelper.useSub(this.checked)"/>';
	this.activeInputs = 4;
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
		this.pendingElement = new OBPendingIndicator($('input3'));
		this.pendingElement.show();
		sajax_do_call('smwf_qi_QIAccess', ["getPropertyInformation", escapeQueryHTML(propname)], this.adaptDialogueToProperty.bind(this));
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
		var oldval = $('input3').value;
		var oldcheck = $('usesub')?$('usesub').checked:false;
		for(var i=4, n = $('dialoguecontent').rows.length; i<n; i++){
			$('dialoguecontent').deleteRow(4); //delete all rows for value inputs
		}
		//create standard values in case request fails
		var arity = 2;
		this.proparity = 2;
		var parameterNames = [gLanguage.getMessage('QI_PAGE')];
		var possibleValues = new Array();

		if (request.status == 200) {
			var schemaData = GeneralXMLTools.createDocumentFromString(request.responseText);

			// read arity
			arity = parseInt(schemaData.documentElement.getAttribute("arity"));
			this.proparity = arity;
			parameterNames = [];
			//parse all parameter names
			for (var i = 0, n = schemaData.documentElement.childNodes.length; i < n; i++) {
				parameterNames.push(schemaData.documentElement.childNodes[i].getAttribute("name"));
				for (var j = 0, m = schemaData.documentElement.childNodes[i].childNodes.length; j<m; j++){
					possibleValues.push(schemaData.documentElement.childNodes[i].childNodes[j].getAttribute("value")); //contains allowed values for enumerations if applicable
				}
			}
		}
		if (arity == 2){
		// Speical treatment: binary properties support conjunction, therefore we need an "add" button
			$('mainlabel').innerHTML = parameterNames[0];
			$('dialoguecontent').rows[3].cells[2].innerHTML = '<input class="wickEnabled general-forms" typehint="0" autocomplete="OFF" type="text" id="input3"/>';
			if(this.numTypes[parameterNames[0].toLowerCase()]){
				$('restricionSelector').innerHTML = this.createRestrictionSelector("=", false);
				autoCompleter.deregisterAllInputs();
				$('dialoguecontent').rows[3].cells[2].firstChild.className = "";
				autoCompleter.registerAllInputs();
			}
			else
				$('restricionSelector').innerHTML = this.createRestrictionSelector("=", true);
			if (parameterNames[0] == gLanguage.getMessage('QI_PAGE')){
				autoCompleter.deregisterAllInputs();
				$('dialoguecontent').rows[3].cells[2].firstChild.className = "wickEnabled";
				autoCompleter.registerAllInputs();
			}
			$('dialoguecontent').rows[3].cells[3].innerHTML = '<img src="' + this.imgpath + 'add.png" alt="addPropertyInput" onclick="qihelper.addDialogueInput()"/>';

			if(parameterNames[0] == gLanguage.getMessage('QI_PAGE')){ //if type is page, we need a subquery checkbox
				$('dialoguecontent').rows[3].cells[4].innerHTML = '&nbsp;' + gLanguage.getMessage('QI_USE_SUBQUERY') + '<input type="checkbox" id="usesub" onclick="qihelper.useSub(this.checked)"/>';
				$('dialoguecontent').rows[3].cells[4].className = "subquerycell";
				$('usesub').checked = oldcheck;
				this.activeInputs = 4;
			}
			else { //no checkbox for other types
				$('dialoguecontent').rows[3].cells[4].innerHTML = ""
				$('dialoguecontent').rows[3].cells[4].className = "";
				this.activeInputs = 4;
			}
			if(possibleValues.length > 0){ //enumeration
				this.propIsEnum = true;
				this.enumValues = new Array();
				autoCompleter.deregisterAllInputs();
				var option = '<select id="input3" style="width:100%">'; //create html for option box
				for(var i = 0; i < possibleValues.length; i++){
					this.enumValues.push(possibleValues[i]); //save enumeration values for later use
					option += '<option value="' + possibleValues[i] + '">' + possibleValues[i] + '</option>';
				}
				option += "</select>";
				$('dialoguecontent').rows[3].cells[2].innerHTML = option;
				autoCompleter.registerAllInputs();
			}
		}
		else {
		// properties with arity >2: no conjunction, no subqueries
			this.activeInputs = 4;
			$('dialoguecontent').rows[3].cells[3].innerHTML = "";
			$('dialoguecontent').rows[3].cells[4].innerHTML = "";
			$('dialoguecontent').rows[3].cells[4].className = "";
			$('mainlabel').innerHTML = parameterNames[0];
			if(this.numTypes[parameterNames[0].toLowerCase()]){
				$('restricionSelector').innerHTML = this.createRestrictionSelector("=", false);
				autoCompleter.deregisterAllInputs();
				$('dialoguecontent').rows[3].cells[2].firstChild.className = "";
				autoCompleter.registerAllInputs();
			}
			else
				$('restricionSelector').innerHTML = this.createRestrictionSelector("=", true);

			for (var i=1; i<parameterNames.length; i++){
				var newrow = $('dialoguecontent').insertRow(-1);
				var cell = newrow.insertCell(0);
				cell.innerHTML = parameterNames[i]; // Label of cell is parameter name (ex.: Integer, Date,...)
				cell = newrow.insertCell(1);
				if(this.numTypes[parameterNames[i].toLowerCase()])
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
	$('input0').value = unescapeQueryHTML(cats[0]);
	for (var i=1; i<cats.length; i++){
		this.addDialogueInput();
		$('input' + i).value = unescapeQueryHTML(cats[i]);
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
	$('input0').value = unescapeQueryHTML(ins[0]);
	for (var i=1; i<ins.length; i++){
		this.addDialogueInput();
		$('input' + i).value = unescapeQueryHTML(ins[i]);
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

	$('input0').value = unescapeQueryHTML(prop.getName()); //fill input filed with name
	$('input1').checked = prop.isShown(); //check box if appropriate
	$('input2').checked = prop.mustBeSet();
	$('mainlabel').innerHTML = (vals[0][0] == "subquery"?gLanguage.getMessage('QI_PAGE'):vals[0][0]); //subquery means type is page

	if($('mainlabel').innerHTML != gLanguage.getMessage('QI_PAGE')){ //remove subquery box
		$('dialoguecontent').rows[3].cells[4].innerHTML = ""; //remove subquery checkbox since no subqueries are possible
		$('dialoguecontent').rows[3].cells[4].className = ""; //remove the seperator
	}

	var disabled = true;
	if(this.numTypes[vals[0][0].toLowerCase()]){ //is it a numeric type?
		disabled = false;

		$('dialoguecontent').rows[3].cells[1].innerHTML = this.createRestrictionSelector(vals[0][1], disabled);
		autoCompleter.deregisterAllInputs();
		$('dialoguecontent').rows[3].cells[2].firstChild.className = ""; //deactivate autocompletion
		autoCompleter.registerAllInputs();
	}
	if(vals[0][0] == "subquery"){ //grey out input field and check checkbox
		this.useSub(true);
		$('usesub').checked = true;
	} else {
		if(!prop.isEnumeration())
			$('input3').value = unescapeQueryHTML(vals[0][2]); //enter the value into the input box
		else { //create option box for enumeration
			var tmphtml = '<select id="input3" style="width:100%">';
			this.enumValues = prop.getEnumValues();
			for(var i = 0; i < this.enumValues.length; i++){
				tmphtml += '<option value="' + unescapeQueryHTML(this.enumValues[i]) + '" ' + (this.enumValues[i]==vals[0][2]?'selected="selected"':'') + '>' + this.enumValues[i] + '</option>';
			}
			tmphtml += '</select>';
			$('dialoguecontent').rows[3].cells[2].innerHTML = tmphtml;
		}
	}
	if(prop.getArity() == 2){ // simply add further inputs if there are any
		if(!prop.isEnumeration()){
			for(var i=1; i<vals.length; i++){
				this.addDialogueInput();
				$('input' + (i+2)).value = unescapeQueryHTML(vals[i][2]);
				$('dialoguecontent').rows[i+3].cells[1].innerHTML = this.createRestrictionSelector(vals[i][1], disabled);
			}
		} else { //enumeration
			this.enumValues = prop.getEnumValues();
			for(var i=1; i<vals.length; i++){
				this.addDialogueInput();
				var tmphtml = '<select id="input' + (i+2) + '" style="width:100%">';
				//create the options; check which one was selected and add the 'selected' param then
				for(var j = 0; j < this.enumValues.length; j++){
					tmphtml += '<option value="' + unescapeQueryHTML(this.enumValues[j]) + '" ' + (this.enumValues[j]==vals[i][2]?'selected="selected"':'') + '>' + unescapeQueryHTML(this.enumValues[j]) + '</option>';
				}
				tmphtml += '</select>';
				$('dialoguecontent').rows[i+3].cells[2].innerHTML = tmphtml;
				$('dialoguecontent').rows[i+3].cells[1].innerHTML = this.createRestrictionSelector(vals[i][1], disabled);
			}
		}
	} else { // property with arity > 2
		autoCompleter.deregisterAllInputs();
		$('dialoguecontent').rows[3].cells[3].innerHTML = ""; //remove plus icon since no conjunction is possible
		$('dialoguecontent').rows[3].cells[4].innerHTML = ""; //remove subquery checkbox since no subqueries are possible
		$('dialoguecontent').rows[3].cells[4].className = ""; //remove the seperator
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
				cell.innerHTML = '<input type="text" class="wickEnabled general-forms" typehint="0" autocomplete="OFF" id="input' + (i+2) + '" value="' + unescapeQueryHTML(vals[i][2]) + '"/>';
			else
				cell.innerHTML = '<input type="text" id="input' + (i+2) + '" value="' + unescapeQueryHTML(vals[i][2]) + '"/>';
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
			    smwhgLogger.log(logstr,"QI","query_category_removed");
			}
			/*ENDLOG*/
			this.activeQuery.removeCategoryGroup(this.loadedFromId);
			break;
		case "instance":
			/*STARTLOG*/
			if(window.smwhgLogger){
				var logstr = "Remove instance " + this.activeQuery.getInstanceGroup(this.loadedFromId).join(",") + " from query";
			    smwhgLogger.log(logstr,"QI","query_instance_removed");
			}
			/*ENDLOG*/
			this.activeQuery.removeInstanceGroup(this.loadedFromId);
			break;
		case "property":
			var pgroup = this.activeQuery.getPropertyGroup(this.loadedFromId);
			/*STARTLOG*/
			if(window.smwhgLogger){
				var logstr = "Remove property " + pgroup.getName() + " from query";
			    smwhgLogger.log(logstr,"QI","query_property_removed");
			}
			/*ENDLOG*/
			if(pgroup.getValues()[0][0] == "subquery"){
				/*STARTLOG*/
				if(window.smwhgLogger){
					var logstr = "Remove subquery (property: " + pgroup.getName() + ") from query";
				    smwhgLogger.log(logstr,"QI","query_subquery_removed");
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
		$('input3').value="";
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
		tmpcat.push(escapeQueryHTML($(tmpid).value));
		if($(tmpid).value == "")
			allinputs = false;
	}
	if(!allinputs)
		$('qistatus').innerHTML = gLanguage.getMessage('QI_ENTER_CATEGORY'); //show error
	else {
		/*STARTLOG*/
		if(window.smwhgLogger){
			var logstr = "Add category " + tmpcat.join(",") + " to query";
		    smwhgLogger.log(logstr,"QI","query_category_added");
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
		tmpins.push(escapeQueryHTML($(tmpid).value));
		if($(tmpid).value == "")
			allinputs = false;
	}
	if(!allinputs)
		$('qistatus').innerHTML = gLanguage.getMessage('QI_ENTER_INSTANCE');
	else {
		/*STARTLOG*/
		if(window.smwhgLogger){
			var logstr = "Add instance " + tmpins.join(",") + " to query";
		    smwhgLogger.log(logstr,"QI","query_instance_added");
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
		var pmust = $('input2').checked;
		var arity = this.proparity;
		var pgroup = new PropertyGroup(escapeQueryHTML(pname), arity, pshow, pmust, this.propIsEnum, this.enumValues); //create propertyGroup
		for(var i = 3; i<$('dialoguecontent').rows.length; i++){
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
				    smwhgLogger.log(logstr,"QI","query_subquery_added");
				}
				/*ENDLOG*/
			}

			var restriction = $('dialoguecontent').rows[i].cells[1].firstChild.value;
			pgroup.addValue(paramname, restriction, escapeQueryHTML(paramvalue)); // add a value group to the property group
		}
		/*STARTLOG*/
		if(window.smwhgLogger){
			var logstr = "Add property " + pname + " to query";
		    smwhgLogger.log(logstr,"QI","query_property_added");
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
	} else if (($('layout_format').value == "template") && ($('template_name').value == "")){
		alert (gLanguage.getMessage('QI_EMPTY_TEMPLATE'));
	} else {
		/*STARTLOG*/
		if(window.smwhgLogger){
		    smwhgLogger.log("Copy query to clipboard","QI","query_copied");
		}
		/*ENDLOG*/
		var text = this.getFullAsk();
	 	if (window.clipboardData){ //IE
			window.clipboardData.setData("Text", text);
			alert(gLanguage.getMessage('QI_CLIPBOARD_SUCCESS'));
		}
	  	else if (window.netscape) {
			try {
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
			catch (e) {
				alert(gLanguage.getMessage('QI_CLIPBOARD_FAIL'));
			}
		}
		else{
			alert(gLanguage.getMessage('QI_CLIPBOARD_FAIL'));
		}
	}
},

showFullAsk:function(type, toggle){
	if(toggle){
		$('shade').toggle();
		$('showAsk').toggle();
	}
	if (this.queries[0].isEmpty()){
		$('fullAskText').value = gLanguage.getMessage('QI_EMPTY_QUERY');
		return;
	} else if (($('layout_format').value == "template") && ($('template_name').value == "")){
		$('fullAskText').value = gLanguage.getMessage('QI_EMPTY_TEMPLATE');
		return;
	}
	var ask = null;
	if(type == "ask"){
		ask = this.getFullAsk();
		$("showAskButton").setStyle({fontWeight: 'bold', textDecoration: 'none', cursor: 'default'});
		$("showParserAskButton").setStyle({fontWeight: 'normal', textDecoration: 'underline', cursor: 'pointer'});
	}
	else{
		ask = this.getFullParserAsk();
		$("showAskButton").setStyle({fontWeight: 'normal', textDecoration: 'underline', cursor: 'pointer'});
		$("showParserAskButton").setStyle({fontWeight: 'bold', textDecoration: 'none', cursor: 'default'});
	}
	ask = ask.replace(/\]\]\[\[/g, "]]\n[[");
	ask = ask.replace(/>\[\[/g, ">\n[[");
	ask = ask.replace(/\]\]</g, "]]\n<");
	if(type == "parser")
		ask = ask.replace(/\|/g, "\n|");
	$('fullAskText').value = ask;
},


showLoadDialogue:function(){
	//List of saved queries with filter
	//load
	sajax_do_call('smwf_qi_QIAccess', ["loadQuery", "Query:SaveTestQ"], this.loadQuery.bind(this));

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
		sajax_do_call('smwf_qi_QIAccess', ["saveQuery", params], this.saveDone.bind(this));
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
		params += $('layout_sort').value== ""?",":$('layout_sort').value + ',';
		params += $('layout_limit').value==""?"50,":$('layout_limit').value + ',';
		params += $('layout_label').value==""?",":$('layout_label').value + ',';
		params += $('layout_order').value=="ascending"?'ascending,':'descending,';
		params += $('layout_default').value==""?',':$('layout_default').value;
		params += $('layout_headers').checked?'show':'hide';
		sajax_do_call('smwf_qi_QIAccess', ["getQueryResultForDownload", params], this.initializeDownload.bind(this));
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
},

checkFormat:function(){
	if($('layout_format').value == "template"){
		$('templatenamefield').style.display = "";
		$('rssfield').style.display = "none";
	} else if ($('layout_format').value == "rss"){
		$('rssfield').style.display = "";
		$('templatenamefield').style.display = "none";
	} else {
		$('templatenamefield').style.display = "none";
		$('rssfield').style.display = "none";
	}
}

} //end class qiHelper

var PropertyGroup = Class.create();
PropertyGroup.prototype = {

	initialize:function(name, arity, show, must, isEnum, enumValues){
		this.name = name;
		this.arity = arity;
		this.show = show;
		this.must = must;
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
	
	mustBeSet:function(){
		return this.must;
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

Event.observe(window, 'load', initialize_qi);

function initialize_qi(){
	qihelper = new QIHelper();
}

function escapeQueryHTML(string){
	string = string.escapeHTML();
	string = string.replace(/\"/g, "&quot;");
	return string;
}


function unescapeQueryHTML(string){
	string = string.unescapeHTML();
	string = string.replace(/&quot;/g, "\"");
	return string;
}



