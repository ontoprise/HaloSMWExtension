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
var GenericToolBar = Class.create();

GenericToolBar.prototype = {

initialize: function() {

},

createList: function(list,id) {
	var len = list == null ? 0 : list.length;
	var divlist = "";
	switch (id) {
		case "category":
			divlist ='<div id="' + id +'-tools">';
			divlist += '<a id="cat-menu-annotate" href="javascript:catToolBar.newItem()" class="menulink">'+gLanguage.getMessage('ANNOTATE')+'</a>';
			if (wgAction != 'annotate') {
				divlist += '<a href="javascript:catToolBar.newCategory()" class="menulink">'+gLanguage.getMessage('CREATE')+'</a>';
			}
			if (wgNamespaceNumber == 14) {
				divlist += '<a href="javascript:catToolBar.CreateSubSup()" class="menulink">'+gLanguage.getMessage('SUB_SUPER')+'</a>';
			}
			divlist += '</div>';
	 		break;
		case "relation":
	  		divlist ='<div id="' + id +'-tools">';
			if (wgAction != 'annotate') {
				divlist += '<a id="rel-menu-annotate" href="javascript:relToolBar.newItem()" class="menulink">'+gLanguage.getMessage('ANNOTATE')+'</a>';
			}
			divlist += '<a href="javascript:relToolBar.newRelation()" class="menulink">'+gLanguage.getMessage('CREATE')+'</a>';
			//regex for checking attribute namespace. 
			//since there's no special namespace number anymore since atr and rel are united 
			var attrregex =	new RegExp("Attribute:.*");
			if (wgNamespaceNumber == 100 || wgNamespaceNumber == 102  || attrregex.exec(wgPageName) != null) {
				divlist += "<a href=\"javascript:relToolBar.CreateSubSup()\" class=\"menulink\">"+gLanguage.getMessage('SUB_SUPER')+"</a>";
			}
  			divlist += '<a id="rel-menu-has-part" href="javascript:relToolBar.newPart()" class="menulink">'+gLanguage.getMessage('MHAS_PART')+'</a>';
  			divlist += '</div>';
	  		break;
	}
  	divlist += "<div id=\"" + id +"-itemlist\"><table id=\"" + id +"-table\">";

	var path = wgArticlePath;
	var dollarPos = path.indexOf('$1');
	if (dollarPos > 0) {
		path = path.substring(0, dollarPos);
	}
	
	//Calculate the size of the property columns depending on the length of the content
	var maxlen1 = 0;
	var maxlen2 = 0;
	if(id=="relation"){
		for (var i = 0; i < len; i++) {
				list[i].getName().length > maxlen2 ? maxlen2 = list[i].getName().length : "";
				// HTML of parameter rows (except first)
				var propertyvalues = list[i].getSplitValues();
	  			for (var j = 0, n = list[i].getArity()-1; j < n; j++) {
	  				propertyvalues[j].length > maxlen1 ? maxlen1 = propertyvalues[j].length : "";
	  			}
		}	
	}
	
	var len1="";
	var len2="";
	if( id == "relation" && maxlen2 != 0){
  		len2 = 20 + 100*(0.55*(maxlen1/(maxlen2+maxlen1)));
  		len2 = 'style="width:'+ len2 + '%;"';
  		len1 = 20 + 100*(0.55 - 0.55*(maxlen1/(maxlen2+maxlen1)));
  		len1 = 'style="width:'+ len1 + '%;"';
	}
	//End calculating size
	
	
  	for (var i = 0; i < len; i++) {
  		var rowSpan = "";
  		var firstValue = "";
  		var multiValue = ""; // for n-ary relations
  		var value = "";
  		var prefix = "";
  		
		switch (id)	{
			case "category":
	  			fn = "catToolBar.getselectedItem(" + i + ")";
	  			firstValue = list[i].getValue ? list[i].getValue().escapeHTML(): "";
	  			prefix = gLanguage.getMessage('CATEGORY');
	 			 break
			case "relation":
	  			fn = "relToolBar.getselectedItem(" + i + ")";
	  			prefix = gLanguage.getMessage('PROPERTY');
	  		
	  			var rowSpan = 'rowspan="'+(list[i].getArity()-1)+'"';
	  			var values = list[i].getSplitValues();
	  			firstValue = values[0].escapeHTML();
	  			var valueLink;

				//firstValue.length > maxlen1 ? maxlen1 = firstValue.length : "";

				valueLink = '<span title="' + firstValue + '">' + firstValue + '<span>';
				firstValue = valueLink;
				
	  			// HTML of parameter rows (except first)
	  			for (var j = 1, n = list[i].getArity()-1; j < n; j++) {
	  				//values[j].length > maxlen1 ? maxlen1 = values[j].length : "";
	  				var v = values[j].escapeHTML();
					valueLink = 
						'<span title="' + v + '">' + v +
					    '</span>';
	//						values[j];
					multiValue += 
						"<tr>" +
							"<td class=\"" + id + "-col2\">" + 
							valueLink + 
							" </td>" +
						"</tr>";
	  			}
  			break
		}
		
		//Checks if getValue exists if no it's an Category what allows longer text
		var shortName = list[i].getName().escapeHTML();
		var elemName;
		//shortName.length > maxlen2 ? maxlen2 = shortName.length : "";
		//Construct the link
		elemName = '<a href="'+wgServer+path+prefix+list[i].getName().escapeHTML();
		elemName += '" target="blank" title="' + shortName +'">' + shortName + '</a>';
		divlist += 	"<tr>" +
				"<td "+rowSpan+" class=\"" + id + "-col1\" " + len1 + ">" + 
					elemName + 
				" </td>" +
				"<td class=\"" + id + "-col2\"  " + len2 + ">" + firstValue + " </td>" + // first value row
		           	"<td "+rowSpan+" class=\"" + id + "-col3\">" +
		           	'<a href=\"javascript:' + fn + '">' +
		           	'<img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/edit.gif"/></a>' +
		           
		           	'</tr>' + multiValue; // all other value rows
  	}
  	divlist += "</table></div>";
  	return divlist;
},


/*deprecated*/
cutdowntosize: function(word, size /*, Optional: maxrows */ ){
	return word;
	var result;
	var subparts= new Array();
	var from;
	var to;
	
	arguments.length == 3 ? maxrows = arguments[2] : maxrows = 0;
	
	//Check in how many parts with full size will the string be divided
	var partscount = parseInt(word.length / size);
	//if theres a rest
	if((word.length % size) != 0){
	 partscount++;
	}
	for(var part=0; part < partscount; part++){
		//Calculate boundaries of the substring to get
	   	from = ((part)*size);
	   	to = (((part)*size)+(size));
	   	//Check if stringlength is exceeded
	   	if(to>word.length){
	   		to=word.length;
	   	};
	   	//Check if maximum rows are exceeded 
	   	if(maxrows!=0 && maxrows == part +1 ){
	   		//Add '...' to the last substring, which will be shown, without exceeding sizelength
	   		if((to-from)<size-3){ 
	   			subparts[part] = word.substring(from,to) + "...";
	   		} else {
	   			subparts[part] = word.substring(from, from+size-3) + "...";
	   		}
	   		break;
	   	} else {
	   	    subparts[part] = word.substring(from,to);	
	   	}
	}
	//Build result with linebreakingcharactes
	result = subparts[0].replace(/\s/g, '&nbsp;');;
	for(var part=1; part < subparts.length; part++){
		result += "<br>" + subparts[part].replace(/\s/g, '&nbsp;');
	}
	return (result ? result : "");
},


/**
 * This function triggers an blur event for the given element
 * so checks will run if element was automaticall filled with marked text
 * This is a _quick_ and _dirty_ implementation, which should be replaced 
 * with redesign 
 */
triggerEvent: function(element){
	if(element){
		element.focus();
		element.blur();
		element.focus();
	}
	
}

};//CLASS END



/*
 * The EventManager allows to observe events using prototype 
 * and stopobserving all previously registered events, with one call.   
 * Usage:
 *  
 *  Register:
 * 	this.eventManager = new EventManager();
 * 	this.eventManager.registerEvent('wpTextbox1','keyup',this.showAlert.bind(this));
 * 
 * 	Deregister:
 * 	this.eventManager.deregisterAllEvents();
 * 
 */
var EventManager = Class.create();

EventManager.prototype = {
	
	initialize: function() {
		this.eventlist = new Array();
	},
	
	registerEvent: function(element, eventName, handler) {
		var event = new Array(element,eventName,handler);
		this.eventlist.push(event);
		Event.observe(element,eventName,handler);
	},
	
	deregisterAllEvents: function() {
		this.eventlist.each( 
			this.stopEvent
		);
		this.eventlist = new Array();
		
	},
	
	stopEvent: function(item) {
		if (item == null) {
			return;
		}
		var obj = $(item[0]);
		if (!obj) {
			return;
		}
		
		Event.stopObserving(item[0],item[1],item[2])
	},
	
	deregisterEventsFromItem: function(itemID) {
		for(var i = 0; i < this.eventlist.length; i++) { 
				if (this.eventlist[i] != null && this.eventlist[i][0] == itemID) {
					this.stopEvent(this.eventlist[i]);
					this.eventlist[i] = null;
				}		
		} 
	
	}
	
};
	
var EventActions = Class.create();
EventActions.prototype = {
	
	initialize: function() {		
	},
	
	eventActions: function() {
		this.istyping = false;
		this.registered = false
	},
	
	setIsTyping: function(bool) {
		this.istyping = bool;
	},
	
	getIsTyping: function() {
		return this.istyping;
	},
	
	isEmpty: function(element){
		if(element.getValue().strip()!='' && element.getValue()!= null ){
			return false;
		} else {
			return true;
		}
	},
	
	targetelement: function(event) {
		return (event.srcElement ? event.srcElement : (event.target ? event.target : event.currentTarget));
	},
	
	timedcallback: function(fnc) {
		if(!this.registered){
			this.registered = true;
			var cb = this.callback.bind(this,fnc);
			setTimeout(cb,500);
		} 
	},
	
	callback: function(fnc){
		if(this.istyping){
			this.istyping = false;
			var cb = this.callback.bind(this,fnc);
			setTimeout(cb,500);
		} else {	
			fnc();
			this.registered = false;
			this.istyping = false;
		}
	} 
	

}

/**
 * The class STBEventActions contains the essential event callbacks for input
 * fields in the semantic toolbar. Their behaviour can be controlled by special
 * attributes. Thus checks for emptyness and correct syntax can be defined. The 
 * checks are performed during key-up and blur events.
 * 
 * There is a singleton for this class: gSTBEventActions. It should be used for 
 * registering the event callbacks.
 */
var STBEventActions = Class.create();
STBEventActions.prototype = Object.extend(new EventActions(),{

	/*
	 * Initializes this object.
	 */
	initialize: function() {
		this.om = new OntologyModifier();
		// As actions for key-up events are delayed, the last event is stored.
		this.keyUpEvent = null;
		this.pendingIndicator = null;
	},

	/*
	 * Callback for key-up events. It starts a timer that calls <delayedKeyUp>
	 * when the user finishes typing.
	 * 
	 * @param event 
	 * 			The key-up event.
	 */
	onKeyUp: function(event){
		
		this.setIsTyping(true);
		var key = event.which || event.keyCode;
		if (key == Event.KEY_RETURN) {
			// set focus on next element in tab order
			var elem = $(event.target);
			if (elem.type == 'a') {
				// found a link
				return true;
			}
			// find the next element in the tab order
			var tabIndex = elem.getAttribute("tabIndex");
			if (!tabIndex) {
				return false;
			}
			tabIndex = tabIndex*1 + 1;
			var div = elem.up('div');
			var children = div.descendants();
			for (var i = 0; i < children.length; ++i) {
				var child = children[i];
				var ti = child.getAttribute("tabIndex");
				if (ti && ti*1 == tabIndex) {
					if (child.disabled == true 
					    || !child.visible()) {
						tabIndex++;
					} else {
						child.focus();
						break;
					}
				}
			}
			return false;
		}		
		
		this.keyUpEvent = event;
		this.timedcallback(this.delayedKeyUp.bind(this));
		
	},
	
	/*
	 * Callback for blur events. 
	 * Checks if input fields are empty and performs the specified syntax checks
	 * if not.
	 * 
	 * @param event 
	 * 			The blur event.
	 */
	onBlur: function(event) {
		var target = $(event.target);

		var oldValue = target.getAttribute("smwOldValue");
		if (oldValue && oldValue == target.value) {
			// content if input field did not change => return
			return;
		}
		target.setAttribute("smwOldValue", target.value);
		
		if (this.checkIfEmpty(target) == false
			&& this.handleValidValue(target)) {
			this.handleCheck(target);
		}
		this.doFinalCheck(target);
		
		
	},
	
	/*
	 * Callback for click events. 
	 * 
	 * 
	 * @param event 
	 * 			The click event.
	 */
	onClick: function(event) {
		var target = $(event.target);
		if (target.type == 'radio') {
			// a radio button has been clicked.
			this.doFinalCheck(target);
		}
	},

	/*
	 * Callback for change events. 
	 * 
	 * 
	 * @param event 
	 * 			The change event.
	 */
	onChange: function(event) {
		var target = $(event.target);
		if (this.checkIfEmpty(target) == false
			&& this.handleValidValue(target)) {
			this.handleCheck(target);
		}
		this.handleChange(target);
		this.doFinalCheck(target);
	},
	
	/*
	 * @public
	 * After a container has been created and filled with values, an initial
	 * check on all input elements can be started.
	 * 
	 * @param Object target
	 * 			DIV-container that contains the input elements to check.
	 */
	initialCheck: function(target) {

		var children = target.descendants();
		
		var elem;
		for (var i = 0, len = children.length; i < len; ++i) {
			elem = children[i];
			var oldValue = elem.getAttribute("smwOldValue");
			if (!oldValue || oldValue != elem.value) {
				// content if input field did change => perform check

				if (this.checkIfEmpty(elem) == false
					&& this.handleValidValue(elem)) {
					this.handleCheck(elem);
				}
				elem.setAttribute("smwOldValue", elem.value);
				
			}
		}
		this.doFinalCheck(elem);
		
	},
	
	/*
	 * This callback for key-up events is called after the user has finished 
	 * typing. The last key-up event is stored in <this.keyUpEvent>.
	 * This method checks if the input field is empty and performs the specified
	 * syntax checks if not.
	 */
	delayedKeyUp: function() {
		var target = $(this.keyUpEvent.target);
		var oldValue = target.getAttribute("smwOldValue");
		if (oldValue && oldValue == target.value) {
			// content if input field did not change => return
			return;
		}
		target.setAttribute("smwOldValue", target.value);
		if (this.checkIfEmpty(target) == false
			&& this.handleValidValue(target)) {
			this.handleCheck(target);
			this.handleChange(target);
		}
		this.doFinalCheck(target);
	},
	
	/*
	 * Checks if the target input field is empty. If actions are tied to this 
	 * check, they are performed.
	 * Example for the HTML-attribute:
	 * smwCheckEmpty="empty ? (color:white) : (color:red)"
	 * 
	 * @param Object target
	 * 			The input element that is checked for emptyness
	 * @return
	 * 			<true> if the input field is empty,
	 * 			<false> otherwise
	 */
	checkIfEmpty: function(target) {
		var value = target.value;
		if (target.type == 'select-one') {
			value = target.options[target.selectedIndex].text;
		}
		var empty = value == "";
		var cie = target.getAttribute("smwCheckEmpty");
		if (!cie) {
			return empty;
		}
		var actions = this.parseConditional("empty", cie);
		if (actions) {
			this.performActions(empty ? actions[0] : actions[1], 
			                    target);
		}
		return empty;
	},
	
	/*
	 * Checks if the value in the input field <target> is valid. A regular
	 * expression decides if this is the case.
	 * 
	 * Example:
	 *    smwValidValue="^.{1,255}$: valid ? (color:white) : (color:red)"
	 * 
	 * @param Object target
	 * 			The target element (an input field)
	 * @return boolean
	 * 		true, if the content of the target is matched by the reg. expr.
	 * 		false, if not
	 */
	handleValidValue: function(target) {
		var check = target.getAttribute("smwValidValue");
		if (!check)	{
			// no constraint defined => value is valid
			return true;
		}
		var regexStr = check.match(/(.*?):\s*(valid\s*\?.*)/);
		if (regexStr) {
			var regex = new RegExp(regexStr[1]);
			var actions = regexStr[2];
			return this.checkWithRegEx(target.value, regex, actions, target);
		}
		return true;
	},
	
	/*
	 * This method handles type checks. Valid type identifiers are:
	 * - regex (valid)
	 * - integer (valid)
	 * - float (valid)
	 * - category (exists, annotated)
	 * - property (exists)
	 * (The names in brackets are the identifiers of the conditional that follows
	 * the type.)
	 * Examples:
	 *   smwCheckType="regex=^\\d+$: valid ? (color:white) : (color:red)"
	 *   smwCheckType="integer: valid ? (color:white) : (color:red)"
	 *   smwCheckType="category: exists ? (color:white) : (color:red)"
	 * 
	 * @param Object target
	 * 			The target element (an input field)
	 */
	handleCheck: function(target) {
		var check = target.getAttribute("smwCheckType");
		if (!check)	{
			return;
		}
		var type = check;
		var actions = "";
		var pos = check.indexOf(":");
		if (pos != -1) {
			type = check.substring(0, pos);
			actions = check.substring(pos+1);
		}
		type = type.toLowerCase();
		if (type.indexOf("regex") == 0) {
			// Handle type checks with regular expressions
			var regexStr = check.match(/regex\s*=\s*(.*?):\s*valid\s*\?/);
			if (regexStr) {
				var regex = new RegExp(regexStr[1]);
				pos = check.search(/:\s*valid\s*\?/);
				actions = check.substring(pos+1);
				this.checkWithRegEx(target.value, regex, actions, target);
			}
			
		} else {
			switch (type) {
				case 'integer':
					this.checkWithRegEx(target.value, /^\d+$/, actions, target);
					break;
				case 'float':
					this.checkWithRegEx(target.value, 
					                    /^[+-]?\d+(\.\d+)?([Ee][+-]?\d+)?$/,
					                    actions, target);
					break;
				case 'category':
				case 'property':
					this.handleSchemaCheck(type, check, target);
					break;
			}
		}
	},

	/**
	 * Handles a change on the DOM-element <target>.
	 * 
	 * @param Object target
	 * 			The target of the change event
	 * 
	 */
	handleChange: function(target) {
		var changeActions = target.getAttribute("smwChanged");
		if (!changeActions)	{
			return;
		}
		changeActions = changeActions.match(/\s*\((.*?)\)\s*$/);
		if (changeActions) {
			this.performActions(changeActions[1], target);
		}
		
	},
	
	/*
	 * Performs a final check on all input fields of the <div> that is the
	 * parent of the DOM-element <target>. 
	 * 
	 * @param Object target
	 * 			The HTML-element that was a target of an event e.g.
	 * 			an input field. The final check actions of its DIV-parent are 
	 * 			processed.
	 */
	doFinalCheck: function(target) {
		var parentDiv = target.up('div');
		if (!parentDiv) {
			return;
		}
		
		var allValidCndtl = parentDiv.getAttribute("smwAllValid");
		if (allValidCndtl) {
			var children = parentDiv.descendants();
			
			var allValid = true;
			for (var i = 0, len = children.length; i < len; ++i) {
				var elem = children[i];
				var valid = elem.getAttribute("smwValid");
				if (valid) {
					if (valid == "false") {
						allValid = false;
//						break;
					} else if (valid != "true") {
						// is the term a conditional?
						var qPos = valid.indexOf('?');
						var func = valid;
						var cond = null;
						if (qPos > -1) {
							func = valid.substring(0, qPos);
							cond = this.parseConditional(func, valid);
						}
						// call a function
						valid = eval(func+'("'+elem.id+'")');
						if (cond) {
							this.performActions(valid ? cond[0] : cond[1], elem);
						}
						if (!valid) {
							allValid = false;
//							break;
						}
					}
				}
			}
			
			var c = this.parseConditional("allValid", allValidCndtl);
			this.performActions(allValid ? c[0] : c[1], parentDiv);
		}
	},

	/*
	 * Checks if <value> matches the regular expression <regex>. Corresponding
	 * actions which are specified in the <conditional> are performed on the 
	 * <target>.
	 * 
	 * @param string value
	 * 			This value is parsed with the regular expression
	 * @param RegExp regex
	 * 			The regular expression that is applied to the value. 
	 * @param string conditional
	 * 			This conditional contains lists of actions for the cases that
	 * 			the reg. exp. matches or not. The name of the conditional must
	 * 			be "valid". (e.g. valid ? (...) : (...) )
	 * @param Object target
	 * 			The target (an input field) for which the actions
	 * 			are performed.
	 * @return boolean
	 * 		true, if the value was matched by the regular expression
	 * 		false, otherwise
	 */
	checkWithRegEx: function(value, regex, conditional, target) {
		var valid = value.match(regex);
		var c = this.parseConditional("valid", conditional);
		this.performActions(valid ? c[0] : c[1], target);
		return valid;
	},
	
	/*
	 * This method checks if an article for a category or a property exists.
	 * It shows the pending indicator and starts an ajax call that calls back in 
	 * function <ajaxCbSchemaCheck>.
	 * 
	 * @param string type
	 * 			Must be one of "category" or "property"
	 * @param string check
	 * 			The complete specification of the check that is performed i.e.
	 * 			the content of the attribute "smwCheckType".
	 * @param Object target
	 * 			The target i.e. an input field
	 */
	handleSchemaCheck: function(type, check, target) {
		var value = target.value;
		var checkName;
		switch (type) {
			case 'category':
				checkName = gLanguage.getMessage('CATEGORY')+value;
				break;
			case 'property':
				checkName = gLanguage.getMessage('PROPERTY')+value;
				break;
		}
		this.showPendingIndicator(target);
		if (!this.om.existsArticle(checkName, 
		                      this.ajaxCbSchemaCheck.bind(this), 
		                      value, [type, check], target.id)) {
			// there is something wrong with the page name
			this.ajaxCbSchemaCheck(checkName, false, value, [type, check], target);
		}							
	},
	
	/*
	 * This method is a callback of the ajax-call that checks if an article exists.
	 * Depending on the existence of the article, actions specified in a conditional
	 * are performed.
	 * 
	 * @param string pageName
	 * 			Complete name of the page whose existence is checked.
	 * @param boolean exists
	 * 			<true> if the article exists
	 * 			<false> otherwise
	 * @param string title
	 * 			Content of the input field that was checked.
	 * @param array<string> param
	 * 			[0]: Type ("category" or "property")
	 * 			[1]: The complete specification of the check that was performed 
	 * 			     i.e. the content of the attribute "smwCheckType".
	 * @param string elementID
	 * 			DOM-ID of the input element for which the check was performed
	 */
	ajaxCbSchemaCheck: function(pageName, exists, title, param, elementID) {
		
		this.hidePendingIndicator();
		var check = param[1];
		var pos = check.indexOf(":");
		if (pos != -1) {
			var conditional = check.substring(pos+1);		
			var actions = this.parseConditional("exists", conditional);
			if (actions) {
				this.performActions(exists ? actions[0] : actions[1], $(elementID))
			}
		}
		this.doFinalCheck($(elementID));
	},
	
	/*
	 * Parses a conditional and returns the actions for the positive and negative 
	 * cases.
	 * A conditions has a name, followed by "?", a list of actions if the condition
	 * holds, a colon and a list of actions if the condition fails.
	 * Example:
	 *   exists ? (color:orange, show:linkID) : (color:white, hide:linkID)
	 * 
	 * @param string name
	 * 			Name of the conditions e.g. "exists" or "valid"
	 * @param string conditional
	 * 			This conditional is parsed and split in its positive and negative
	 * 			part.
	 * @return array<string>
	 * 			[0]: The positive part of the conditional
	 * 			[1]: The negative part of the conditional
	 * 			<null> is returned if the conditional has syntax errors
	 * 
	 */
	parseConditional: function(name, conditional) {
		var regex = new RegExp("\\s*"+name+"\\s*\\?\\s*\\(([^)]*)\\)\\s*:\\s*\\(([^)]*)\\)");
		var parts = conditional.match(regex);
		if (parts) {
			return [parts[1], parts[2]];
		}
		return null;
	},
	
	/*
	 * Performs all actions that are given in a comma separated list.
	 * 
	 * @param string actions
	 * 			The comma separated list of actions
	 * @param Onject element
	 * 			The input field for which the actions are performed
	 */
	performActions: function(actions, element) {
		
		// Actions are comma separated
		var allActions = actions.split(",");
		
		for (var i = 0, len = allActions.length; i < len; i++) {
			// actions and their parameters are separated by colons
			var actionAndParam = allActions[i].split(":");
			var act = "";
			var param = "";
			if (actionAndParam.length > 0) {
				act = actionAndParam[0].match(/^\s*(.*?)\s*$/);
				if (act) {
					act = act[1];
				}
			}			
			if (actionAndParam.length > 1) {
				param = actionAndParam[1].match(/^\s*(.*?)\s*$/);
				if (param) {
					param = param[1];
				}
			}			
			this.performSingleAction(act.toLowerCase(), param, element);
			
		}
		
	},
	
	/*
	 * Performs a single action.
	 * 
	 * @param string action
	 * 			Name of the action e.g. color, show, hide, call, showmessage
	 * @param string parameter
	 * 			Parameter for the action
	 * @param Object element
	 * 			The input field for which the action is performed
	 * 
	 */
	performSingleAction: function(action, parameter, element) {
		switch (action) {
			case 'color':
				if (element) {
					element.setStyle({ background:parameter});
				}
				break;
			case 'show':
				var tbc = smw_ctbHandler.findContainer(parameter);
				if (tbc) {
					tbc.show(parameter, true);
				}
				break;
			case 'hide':
				var tbc = smw_ctbHandler.findContainer(parameter);
				if (tbc) {
					tbc.show(parameter, false);
				}
				break;
			case 'call':
				eval(parameter+'("'+element.id+'")');
				break;
			case 'showmessage':
				var msgElem = $(element.id+'-msg');
				if (msgElem) {
					var msg = gLanguage.getMessage(parameter);
					var value = element.value;
					msg = msg.replace(/\$c/g,value);
					var tbc = smw_ctbHandler.findContainer(msgElem);
					tbc.replace(msgElem.id,
					            tbc.createText(msgElem.id, msg, '' , true));
					tbc.show(msgElem.id, true);
				}
				break;
			case 'hidemessage':
				var msgElem = $(element.id+'-msg');
				if (msgElem) {
					var tbc = smw_ctbHandler.findContainer(msgElem.id);
					tbc.show(msgElem.id, false);
				}
				break;
			case 'valid':
				element.setAttribute("smwValid", parameter);
				break;
			case 'attribute':
				var attrValue = parameter.split("=");
				if (attrValue && attrValue.length == 2) {
					element.setAttribute(attrValue[0], attrValue[1]);
				}
				break;
		}
		
	},
	
	/*
	 * Shows the pending indicator on the element with the DOM-ID <onElement>
	 * 
	 * @param string onElement
	 * 			DOM-ID if the element over which the indicator appears
	 */
	showPendingIndicator: function(onElement) {
		this.hidePendingIndicator();
		this.pendingIndicator = new OBPendingIndicator($(onElement));
		this.pendingIndicator.show();
	},

	/*
	 * Hides the pending indicator.
	 */
	hidePendingIndicator: function() {
		if (this.pendingIndicator != null) {
			this.pendingIndicator.hide();
			this.pendingIndicator = null;
		}
}
	
});

/*
 * The singleton instance of the semantic toolbar event action handler.
 */
var gSTBEventActions = new STBEventActions();

