

var QIHelper = Class.create();
QIHelper.prototype = {

initialize:function(){
	this.imgpath = wgScriptPath  + '/extensions/SemanticMediaWiki/skins/QueryInterface/images/';
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
},

getNumericDatatypes:function(){
	sajax_do_call('smwfQIAccess', ["getNumericTypes", "dummy"], this.setNumericDatatypes.bind(this));
},

setNumericDatatypes:function(request){
	var types = request.responseText.split(",");
	for(var i=0; i<types.length; i++){
		//remove leading and trailing whitespaces
		var tmp = types[i].replace(/^\s+|\s+$/g, '');
		this.numTypes[tmp] = true;
	}
},

addQuery:function(parent, name){
	this.queries.push(new Query(this.nextQueryId, parent, name));
	this.nextQueryId++;
},

setActiveQuery:function(id){
	this.activeQuery = this.queries[id];
	this.activeQuery.updateTreeXML();
	this.activeQueryId = id;
	this.emptyDialogue();
	this.updateBreadcrumbs(id);
	//update everything
},

resetQuery:function(){
	$('shade').style.display="";
	$('resetdialogue').style.display="";
},

doReset:function(){
	this.emptyDialogue();
	this.initialize();
	$('shade').style.display="none";
	$('resetdialogue').style.display="none";
},

previewQuery:function(){
	$('shade').toggle();
	var ask = this.recurseQuery(0);
	if (ask != ""){
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
	else {
		var request = Array();
		request.responseText = gLanguage.getMessage('QI_EMPTY_QUERY');
		this.openPreview(request);
	}
},

openPreview:function(request){
	$('fullpreviewbox').toggle();
	$('fullpreview').innerHTML = request.responseText;
},

updateBreadcrumbs:function(id){
	var nav = Array();
	while(this.queries[id].getParent() != null){
		nav.unshift(id);
		id = this.queries[id].getParent();
	}
	nav.unshift(id);
	var html = "";
	for(var i=0; i<nav.length; i++){
		if (i>0)
			html += "&gt;";
		html += '<span class="qibutton" onclick="qihelper.setActiveQuery(' + nav[i] + ')">';
		html += this.queries[nav[i]].getName() + '</span>';
	}
	html += "<hr/>";
	$('treeviewbreadcrumbs').innerHTML = html;
},

updateColumnPreview:function(){
	var columns = new Array();
	columns.push(gLanguage.getMessage('QI_ARTICLE_TITLE'));
	var tmparr = this.queries[0].getAllProperties();
	for(var i=0; i<tmparr.length; i++){
		if(tmparr[i].isShown()){ //show
			columns.push(tmparr[i].getName());
		}
	}
	var html = '<table id="tcp" summary="Preview of table columns"><tr>';
	$('layout_sort').innerHTML = "";
	for(var i=0; i<columns.length; i++){
		html += "<td>" + columns[i] + "</td>";
		$('layout_sort').innerHTML += "<option>" + columns[i] + "</option>";
	}
	html += "</tr></table>";
	$('tcpcontent').innerHTML = html;
},

getFullAsk:function(){
	var asktext = this.recurseQuery(0);
	//get Layout parameters
	var starttag = "<ask ";
	starttag += 'format="' + $('layout_format').value + '" ';
	starttag += $('layout_link').value=="subject"?"":('link="' + $('layout_link').value + '" ');
	starttag += $('layout_intro').value==""?"":('intro="' + $('layout_intro').value + '" ');
	starttag += $('layout_sort').value==gLanguage.getMessage('QI_ARTICLE_TITLE')?"":('sort="' + $('layout_sort').value + '" ');
	starttag += $('layout_limit').value==""?'limit="20"':('limit="' + $('layout_limit').value + '" ');
	starttag += $('layout_label').value==""?"":('label="' + $('layout_label').value + '" ');
	starttag += $('layout_order').value=="ascending"?'order="ascending" ':'order="descending" ';
	starttag += $('layout_headers').checked?'':'headers="hide" ';
	starttag += $('layout_default').value==""?'':'default="' + $('layout_default').value +'" ';
	starttag += ">";
	return starttag + asktext + "</ask>";
},

recurseQuery:function(id){
	var sq = this.queries[id].getSubqueryIds();
	if(sq.length == 0)
		return this.queries[id].getAskText();
	else {
		var tmptext = this.queries[id].getAskText();
		for(var i=0; i<sq.length; i++){
			var regex = null;
			eval('regex = /Subquery:' + sq[i] + ':/g');
			tmptext = tmptext.replace(regex, '<q>' + this.recurseQuery(sq[i]) + '</q>');
		}
		return tmptext;
	}
},

newCategoryDialogue:function(reset){
	$('qidelete').style.display = "none";
	autoCompleter.deregisterAllInputs();
	if(reset)
		this.loadedFromId = null;
	this.activeDialogue = "category";
	for (var i=0, n=$('dialoguecontent').rows.length; i<n; i++)
		$('dialoguecontent').deleteRow(0);
	var newrow = $('dialoguecontent').insertRow(-1);
	var cell = newrow.insertCell(0);
	cell.innerHTML = gLanguage.getMessage('CATEGORY');
	cell = newrow.insertCell(1);
	cell.innerHTML = '<input type="text" id="input0" class="wickEnabled general-forms" typehint="14" autocomplete="OFF"/>';
	cell = newrow.insertCell(2);
	cell.innerHTML = '<img src="' + this.imgpath + 'add.png" alt="addCategoryInput" onclick="qihelper.addDialogueInput()"/>';
	this.activeInputs = 1;
	$('dialoguebuttons').style.display="";
	autoCompleter.registerAllInputs();
},

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
},

newPropertyDialogue:function(reset){
	$('qidelete').style.display = "none";
	autoCompleter.deregisterAllInputs();
	if(reset)
		this.loadedFromId = null;
	this.activeDialogue = "property";
	this.propname = "";
	for (var i=0, n=$('dialoguecontent').rows.length; i<n; i++)
		$('dialoguecontent').deleteRow(0);
	var newrow = $('dialoguecontent').insertRow(-1);
	var cell = newrow.insertCell(0);
	cell.innerHTML = gLanguage.getMessage('QI_PROPERTYNAME');
	cell = newrow.insertCell(1);
	cell = newrow.insertCell(2);
	cell.innerHTML = '<input type="text" id="input0" class="wickEnabled general-forms" typehint="102" autocomplete="OFF" onblur="qihelper.getPropertyInformation()"/>';

	newrow = $('dialoguecontent').insertRow(-1);
	cell = newrow.insertCell(0);
	cell.innerHTML = gLanguage.getMessage('QI_SHOW_PROPERTY');
	cell = newrow.insertCell(1);
	cell = newrow.insertCell(2);
	if(this.activeQueryId == 0)
		cell.innerHTML = '<input type="checkbox" id="input1">';
	else
		cell.innerHTML = '<input type="checkbox" disabled="disabled" id="input1">';

	newrow = $('dialoguecontent').insertRow(-1);
	cell = newrow.insertCell(0);
	cell.id = "mainlabel";
	cell.innerHTML = gLanguage.getMessage('QI_PAGE');
	cell = newrow.insertCell(1);
	cell.id = "restricionSelector";
	cell.innerHTML = this.createRestrictionSelector("=", true);
	cell = newrow.insertCell(2);
	cell.innerHTML = '<input class="wickEnabled general-forms" typehint="0" autocomplete="OFF" type="text" id="input2"/>';
	cell = newrow.insertCell(3);
	cell.innerHTML = '<img src="' + this.imgpath + 'add.png" alt="addPropertyInput" onclick="qihelper.addDialogueInput()"/>';
	cell = newrow.insertCell(4);
	cell.className = "subquerycell";
	cell.id = "subquerycell";
	cell.innerHTML = '&nbsp;' + gLanguage.getMessage('QI_USE_SUBQUERY') + '<input type="checkbox" id="usesub" onclick="qihelper.useSub(this.checked)"/>';
	this.activeInputs = 3;
	$('dialoguebuttons').style.display="";
	this.proparity = 2;
	autoCompleter.registerAllInputs();
},

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

addDialogueInput:function(){
	autoCompleter.deregisterAllInputs();
	var delimg = wgScriptPath  + '/extensions/SemanticMediaWiki/skins/QueryInterface/images/delete.png';
	var newrow = $('dialoguecontent').insertRow(-1);
	newrow.id = "row" + newrow.rowIndex;
	var cell = newrow.insertCell(0);
	cell.innerHTML = gLanguage.getMessage('QI_OR');
	cell = newrow.insertCell(1);
	var param = $('mainlabel')?$('mainlabel').innerHTML:"";

	if(this.activeDialogue == "category")
		cell.innerHTML = '<input class="wickEnabled general-forms" typehint="14" autocomplete="OFF" type="text" id="input' + this.activeInputs + '"/>';
	else if(this.activeDialogue == "instance")
		cell.innerHTML = '<input class="wickEnabled general-forms" typehint="0" autocomplete="OFF" type="text" id="input' + this.activeInputs + '"/>';
	else if(param == gLanguage.getMessage('QI_PAGE')){
		cell.innerHTML = this.createRestrictionSelector("=", true);
		cell = newrow.insertCell(2);
		cell.innerHTML = '<input class="wickEnabled general-forms" typehint="0" autocomplete="OFF" type="text" id="input' + this.activeInputs + '"/>';
	}
	else{
		if(this.numTypes[param.toLowerCase()])
			cell.innerHTML = this.createRestrictionSelector("=", false);
		else
			cell.innerHTML = this.createRestrictionSelector("=", true);
		cell = newrow.insertCell(2);
		if(this.propIsEnum){
			var tmphtml = '<select id="input' + this.activeInputs + '" style="width:100%">';
			for(var i = 0; i < this.enumValues.length; i++){
				tmphtml += '<option value="' + this.enumValues[i] + '">' + this.enumValues[i] + '</option>';
			}
			tmphtml += '</select>';
			cell.innerHTML = tmphtml;
		} else {
			cell.innerHTML = '<input type="text" id="input' + this.activeInputs + '"/>';
		}
	}
	cell = newrow.insertCell(-1);
	cell.innerHTML = '<img src="' + this.imgpath + 'delete.png" alt="deleteInput" onclick="qihelper.removeInput(' + newrow.rowIndex + ')"/>';
	this.activeInputs++;
	autoCompleter.registerAllInputs();
},

removeInput:function(index){
	$('dialoguecontent').removeChild($('row'+index));
	this.activeInputs--;
},

getPropertyInformation:function(){
	var propname = $('input0').value;
	if (propname != "" && propname != this.propname){
		this.propname = propname;
		sajax_do_call('smwfQIAccess', ["getPropertyInformation", propname], this.adaptDialogueToProperty.bind(this));
	}
},

adaptDialogueToProperty:function(request){
this.propIsEnum = false;
if (this.activeDialogue != null){
	var oldval = $('input2').value;
	var oldcheck = $('usesub')?$('usesub').checked:false;
	for(var i=3, n = $('dialoguecontent').rows.length; i<n; i++){
		$('dialoguecontent').deleteRow(3);
	}

	var arity = 2;
	this.proparity = 2;
	var parameterNames = [gLanguage.getMessage('QI_PAGE')];
	var parameterIsNumeric = [false];
	var possibleValues = new Array();

	if (request.status == 200) {
		var schemaData = GeneralXMLTools.createDocumentFromString(request.responseText);

		// read arity and parameter names
		arity = parseInt(schemaData.documentElement.getAttribute("arity"));
		this.proparity = arity;
		parameterNames = [];
		parameterIsNumeric = [];
		for (var i = 0, n = schemaData.documentElement.childNodes.length; i < n; i++) {
			parameterNames.push(schemaData.documentElement.childNodes[i].getAttribute("name"));
			parameterIsNumeric.push(schemaData.documentElement.childNodes[i].getAttribute("isNumeric")=="true"?true:false);
			for (var j = 0, m = schemaData.documentElement.childNodes[i].childNodes.length; j<m; j++){
				possibleValues.push(schemaData.documentElement.childNodes[i].childNodes[j].getAttribute("value"));
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

		if(parameterNames[0] == gLanguage.getMessage('QI_OR')){ //if type is page, we need a subquery checkbox
			$('dialoguecontent').rows[2].cells[4].innerHTML = '&nbsp;' + gLanguage.getMessage('QI_OR') + '<input type="checkbox" id="usesub" onclick="qihelper.useSub(this.checked)"/>';
			$('dialoguecontent').rows[2].cells[4].className = "subquerycell";
			$('usesub').checked = oldcheck;
			this.activeInputs = 3;
		}
		else { //no checkbox for other types
			$('dialoguecontent').rows[2].cells[4].innerHTML = ""
			$('dialoguecontent').rows[2].cells[4].className = "";
			this.activeInputs = 3;
		}
		if(possibleValues.length > 0){
			this.propIsEnum = true;
			this.enumValues = new Array();
			autoCompleter.deregisterAllInputs();
			var option = '<select id="input2" style="width:100%">';
			for(var i = 0; i < possibleValues.length; i++){
				this.enumValues.push(possibleValues[i]);
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
			cell.innerHTML = parameterNames[i];
			cell = newrow.insertCell(1);
			if (parameterIsNumeric[i])
				cell.innerHTML = this.createRestrictionSelector("=", false);
			else
				cell.innerHTML = this.createRestrictionSelector("=", true);

			cell = newrow.insertCell(2);
			if(parameterNames[i] == gLanguage.getMessage('QI_PAGE'))
				cell.innerHTML = '<input class="wickEnabled general-forms" typehint="0" autocomplete="OFF" type="text" id="input' + this.activeInputs + '"/>';
			else
				cell.innerHTML = '<input type="text" id="input' + this.activeInputs + '"/>';
			this.activeInputs++;
		}
	}
}
},

loadCategoryDialogue:function(id){
	this.newCategoryDialogue(false);
	this.loadedFromId = id;
	var cats = this.activeQuery.getCategoryGroup(id);
	$('input0').value = cats[0];
	for (var i=1; i<cats.length; i++){
		this.addDialogueInput();
		$('input' + i).value = cats[i];
	}
	$('qidelete').style.display = "";
},

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

loadPropertyDialogue:function(id){
	this.newPropertyDialogue(false);
	this.loadedFromId = id;
	var prop = this.activeQuery.getPropertyGroup(id);
	var vals = prop.getValues();
	this.proparity = prop.getArity();
	$('input0').value = prop.getName();

	$('input1').checked = prop.isShown();
	$('mainlabel').innerHTML = (vals[0][0] == "subquery"?gLanguage.getMessage('PAGE'):vals[0][0]);
	var disabled = true;
	if(this.numTypes[vals[0][0].toLowerCase()]){
		disabled = false;

		$('dialoguecontent').rows[2].cells[1].innerHTML = this.createRestrictionSelector(vals[0][1], disabled);
		autoCompleter.deregisterAllInputs();
		$('dialoguecontent').rows[2].cells[2].firstChild.className = "";
		autoCompleter.registerAllInputs();
	}
	if(vals[0][0] == "subquery"){
		this.useSub(true);
		$('usesub').checked = true;
	} else {
		if(!prop.isEnumeration())
			$('input2').value = vals[0][2];
		else {
			var tmphtml = '<select id="input2" style="width:100%">';
			var tempvals = prop.getEnumValues();
			for(var i = 0; i < tempvals.length; i++){
				tmphtml += '<option value="' + tempvals[i] + '" ' + (tempvals[i]==vals[0][2]?'selected="selected"':'') + '>' + tempvals[i] + '</option>';
			}
			tmphtml += '</select>';
			$('dialoguecontent').rows[2].cells[2].innerHTML = tmphtml;
		}
	}
	if(prop.getArity() == 2){
		if(!prop.isEnumeration()){
			for(var i=1; i<vals.length; i++){
				this.addDialogueInput();
				$('input' + (i+2)).value = vals[i][2];
				$('dialoguecontent').rows[i+2].cells[1].innerHTML = this.createRestrictionSelector(vals[i][1], disabled);
			}
		} else {
			var tempvals = prop.getEnumValues();
			for(var i=1; i<vals.length; i++){
				this.addDialogueInput();
				var tmphtml = '<select id="input' + (i+2) + '" style="width:100%">';
				for(var j = 0; j < tempvals.length; j++){
					tmphtml += '<option value="' + tempvals[j] + '" ' + (tempvals[j]==vals[i][2]?'selected="selected"':'') + '>' + tempvals[j] + '</option>';
				}
				tmphtml += '</select>';
				$('dialoguecontent').rows[i+2].cells[2].innerHTML = tmphtml;
				$('dialoguecontent').rows[i+2].cells[1].innerHTML = this.createRestrictionSelector(vals[i][1], disabled);
			}
		}
	} else {
		autoCompleter.deregisterAllInputs();
		$('dialoguecontent').rows[2].cells[3].innerHTML = "";
		$('dialoguecontent').rows[2].cells[4].innerHTML = "";
		$('dialoguecontent').rows[2].cells[4].className = "";
		for(var i=1; i<vals.length; i++){
			var row = $('dialoguecontent').insertRow(-1);
			var cell = row.insertCell(0);
			cell.innerHTML = vals[i][0];
			cell = row.insertCell(1);
			if(this.numTypes[vals[i][0].toLowerCase()])
				cell.innerHTML = this.createRestrictionSelector(vals[i][1], false);
			else
				cell.innerHTML = this.createRestrictionSelector(vals[i][1], true);
			cell = row.insertCell(2);
			if(vals[i][0] == gLanguage.getMessage('QI_PAGE'))
				cell.innerHTML = '<input type="text" class="wickEnabled general-forms" typehint="0" autocomplete="OFF" id="input' + (i+2) + '" value="' + vals[i][2] + '"/>';
			else
				cell.innerHTML = '<input type="text" id="input' + (i+2) + '" value="' + vals[i][2] + '"/>';
		}
		autoCompleter.registerAllInputs();
	}
	$('qidelete').style.display = "";
},

deleteActivePart:function(){
	switch(this.activeDialogue){
		case "category":
			this.activeQuery.removeCategoryGroup(this.loadedFromId);
			break;
		case "instance":
			this.activeQuery.removeInstanceGroup(this.loadedFromId);
			break;
		case "property":
			this.activeQuery.removePropertyGroup(this.loadedFromId);
			break;
	}
	this.activeQuery.updateTreeXML();
	this.emptyDialogue();
},

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

addCategoryGroup:function(){
	var tmpcat = Array();
	var allinputs = true;
	for(var i=0; i<this.activeInputs; i++){
		var tmpid = "input" + i;
		tmpcat.push($(tmpid).value);
		if($(tmpid).value == "")
			allinputs = false;
	}
	if(!allinputs)
		$('qistatus').innerHTML = gLanguage.getMessage('QI_ENTER_CATEGORY');
	else {
		this.activeQuery.addCategoryGroup(tmpcat, this.loadedFromId);
		this.emptyDialogue();
	}
},

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
		this.activeQuery.addInstanceGroup(tmpins, this.loadedFromId);
		this.emptyDialogue();
	}
},

addPropertyGroup:function(){
	var pname = $('input0').value;
	var subqueryIds = Array();
	if (pname == ""){
		$('qistatus').innerHTML = gLanguage.getMessage('QI_ENTER_PROPERTY_NAME');
	} else {
		var pshow = $('input1').checked;
		var arity = this.proparity;
		var pgroup = new PropertyGroup(pname, arity, pshow, this.propIsEnum, this.enumValues);
		for(var i = 2; i<$('dialoguecontent').rows.length; i++){
			var paramvalue = $('input' + i).value;
			paramvalue = paramvalue==""?"*":paramvalue;
			var paramname = $('dialoguecontent').rows[i].cells[0].innerHTML;
			if(paramname == gLanguage.getMessage('PAGE') && arity == 2 && $('usesub').checked){
				paramname = "subquery";
				paramvalue = this.nextQueryId;
				subqueryIds.push(this.nextQueryId);
				this.addQuery(this.activeQueryId, pname);
			}
			var restriction = $('dialoguecontent').rows[i].cells[1].firstChild.value;
			pgroup.addValue(paramname, restriction, paramvalue);
		}
		this.activeQuery.addPropertyGroup(pgroup, subqueryIds, this.loadedFromId);
		this.emptyDialogue();
		this.updateColumnPreview();
	}
},

copyToClipboard:function(){
	var text = this.getFullAsk();
 	if (window.clipboardData){
		window.clipboardData.setData("Text", text);
		alert(gLanguage.getMessage('QI_CLIPBOARD_FAIL'));
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



