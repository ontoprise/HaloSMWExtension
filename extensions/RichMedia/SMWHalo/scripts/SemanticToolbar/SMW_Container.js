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

/**
 * 
 * @file
 * @ingroup SMWHaloSemanticToolbar
 * @author: Thomas Schweitzer
 *  framework for menu container handling of STB++
 */

var ContainerToolBar = Class.create();
ContainerToolBar.prototype = {

/**
 * Constructor
 * 
 *  * @param string id
 * 		name of the menucontainerframework (e.g. 'rel' for Relation)
 *  * @param integer tabindex
 * 		first tabindex to start with
 *  * @param object frameworkcontainer
 * 		frameworkcontainer which the items will be added to 
 */
initialize: function(id,tabindex, frameworkcontainer) {
	//tabindex to start with when creating elements
	this.id = id;
	this.startindex = tabindex;
	this.lastindex = tabindex;  
	//header / containerlist / footer
	this.cointainerlist = new Array();
	//container in the framework, where to add the menu
	this.frameworkcontainer = frameworkcontainer;
	//TODO: FIX: new throws error if nothing is present  
	this.sandglass = new OBPendingIndicator();
	this.eventManager = new EventManager();
	//Register this object add the ctbHandler
	if(smw_ctbHandler) {
		smw_ctbHandler.addContainer(this.id,this);
	}
	
},


//Sand glass eventuell in die generelle bla auslagern
/**
 * @public
 * 
 * Shows the sand glass at the given element
 * 
 * @param object element
 * 		the element the sand glass will be shown at
 */
showSandglass: function(element){
	this.sandglass.hide();
	this.sandglass.show(element);
},

/**
 * @public
 * 
 * Hide the sand glass 
 * 
 */
hideSandglass: function(){
		this.sandglass.hide();
},

/**
 * @public
 * 
 * Creates the header and footer for this framework and adds it to the framework container
 * 
 * @param string attributes
 * 		Attributes for the new <div>-element
 * 		  containertype enum
 */
createContainerBody: function(attributes,containertype,headline){
	//defines header and footer
	var header = '<div id="' + this.id + '-box" '+attributes+'>';
	var footer = '</div>';
	//Adds the body to the stbframework
	this.frameworkcontainer.setContent(header + footer,containertype,headline);
	this.frameworkcontainer.contentChanged();
	//for testing:
	//setTimeout(this.foo.bind(this),3000);
},



/**
 * @public
 * 
 * Creates an Input Element
 * 
 * @param string id
 * 		id of the element
 * @param string description
 * 		name of the element which will be shown 
 * @para string initialContent
 * 		The initial content of the input field.
 * @param string deleteCallback
 * 		A function. If not empty, a delete button will be added and the function
 * 		will be called if the button is pressed.
 * @param string attributes
 *		attributes which will be passed to the specific element
 * @param boolean visibility
 * 		if false the element will be collapsed initially
 * @param boolean autoCompletion
 * 		if <false>, the input field provides no auto completion. If <true> or
 * 		undefined, auto completion is supported.
 */
 
createInput: function(id, description, initialContent, deleteCallback, attributes ,visibility, autoCompletion){
	
	var ac = "wickEnabled";
	if (typeof autoCompletion == "boolean") {
		if (autoCompletion == false) {
			ac = "";
		} 
	}
	var containercontent = '<table class="stb-table stb-input-table ' + this.id + '-table ' + this.id + '-input-table"' +
			(visibility ? '' : 'style="display:none;"')  + 'id="' + this.id + '-table-' + id +'">' +
 			'<tr>' +
 				//Name of Inputfield
 				'<td class="stb-input-col1 ' + this.id + '-input-col1">' + description + '</td>' +
 				//Inputfield 
 				'<td class="stb-input-col2 ' + this.id + '-input-col2">';

	if (initialContent) {
		initialContent = initialContent.escapeHTML();
		initialContent = initialContent.replace(/"/g,"&quot;");
	}
	
 	if (deleteCallback){
		//if deletable change classes and add button			
		containercontent += 
			'<input class="' + ac + ' stb-delinput ' + this.id + '-delinput" ' +
            'id="'+ id + '" ' +
            attributes + 
            ' type="text" ' +
            ' alignfloater="right" ' +
            'value="' + initialContent + '" '+
            'tabindex="'+ this.lastindex++ +'" />'+
            '</td>'+ 
            '<td class="stb-input-col3 ' + this.id + '-input-col3">' +
			'<a href="javascript:' + deleteCallback + '">' +
			'<img src="' + 
			wgScriptPath + 
			'/extensions/SMWHalo/skins/redcross.gif"/>';				 	
	} else {
		containercontent += 
			'<input class="' + ac + ' stb-input ' + this.id + '-input" ' +
			'id="'+ id + '" '+
			attributes + 
			' type="text" ' +
            ' alignfloater="right" ' +
            'value="' + initialContent + '" '+
			'tabindex="'+ this.lastindex++ +'" />';
	}
	containercontent += '</td>' +
 			'</tr>' +
 			'</table>';
			
	return containercontent;
},


/**
 * @public
 * 
 * Creates an dropdown element
 * 
 * @param string id
 * 		id of the element
 * @param string description
 * 		name of the element which will be shown
 * @param array<string> options
 * 		array of strings representing the options of the dropdown box
 * @param string deleteCallback
 * 		A function. If not empty, a delete button will be added and the function
 * 		will be called if the button is pressed.
 * @param integer selecteditem
 * 		gives the number if the item in the options array which will be preselected 
 * @param string attributes
 *		attributes which will be passed to the specific element
 * @param boolean visibility
 * 		if false the element will be collapsed initially
 */
createDropDown: function(id, description, options, deleteCallback, selecteditem, attributes, visibility){
	
	//Select header
	var containercontent = '<table class="stb-table stb-select-table ' + this.id + '-table ' + this.id + '-select-table"' +
			(visibility ? '' : 'style="display:none;"')  + 'id="' + this.id + '-table-' + id +'">' +
 			'<tr>' +
 				//Name of Selectbox
 				'<td class="stb-select-col1 ' + this.id + '-select-col1">' +
				description +
				'</td>' +
 				//Selectbox
 				'<td class="stb-select-col2 ' + this.id + '-select-col2">';
	//if deletable change classes
	if(deleteCallback){
		containercontent += '<select class="stb-delselect ' + this.id + '-delselect" id="' + id + '"  ' + attributes + ' tabindex="'+ this.lastindex++ +'">';
		 	
	} else {
 		containercontent += '<select class="stb-select ' + this.id + '-select" id="' + id + '"  ' + attributes + ' tabindex="'+ this.lastindex++ +'">';
 	}
	//Generate Options from the aray				
	for( var i = 0; i < options.length; i++ ){
		if(i!=selecteditem){
			//Normal option
			containercontent += '<option>' + options[i] + '</option>'
		} else {
			//Preselected Option
			containercontent += '<option selected="selected">' + options[i] + '</option>'
		}
	}
	containercontent += '</select>';
	//if deletable add button
	if(deleteCallback){
		containercontent += '</td>';
		containercontent += '<td class="stb-select-col3 ' + this.id + '-select-col3">';;
		containercontent += '<a href="javascript:' + deleteCallback + '"><img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/redcross.gif"/>';				 	
	}
	//Select footer
	containercontent += '</td>' +
 			'</tr>' +
 			'</table>';
 			
 	return containercontent;

},

/**
 * @public
 * 
 * Creates an radiobutton element
 * 
 * @param string id
 * 		id of the element
 * @param string description
 * 		name of the element which will be shown
 * @param array[] options
 * 		array of strings representing the options of the radiobuttons
 * @param integer selecteditem
 * 		gives the number if the item in the options array which will be preselected 
 * @param string attributes
 *		attributes which will be passed to the specific element
 * @param boolean visibility
 * 		if false the element will be collapsed initially
 */
createRadio: function(id, description, options, selecteditem, attributes, visibility){
	
	//Radio header
	var containercontent = '<table class="stb-table stb-radio-table ' + this.id + '-table ' + this.id + '-radio-table"' +
			(visibility ? '' : 'style="display:none;"')  + 'id="' + this.id + '-table-' + id +'">' +
 			'<tr>' +
 				//Name of Radiobuttons
 				'<td class="stb-radio-col1 ' + this.id + '-input-radio1">' +
				description +
				'</td>' +
			'</tr><tr>'+
 				//Radiobuttons
 				'<td class="stb-radio-col2 ' + this.id + '-radio-col2">' +
					'<form class="stb-radio ' + this.id + '-radio" id="'+ id +'"  ' + attributes + ' tabindex="'+ this.lastindex++ +'">';
	
	//Generate Options from the aray				
	for( var i = 0; i < options.length; i++ ){
		if(i!=selecteditem){
			//Normal option
			containercontent += '<input type="radio" name="' + id +'" value="' + options[i] + '">' + options[i] + '<br>'
		} else {
			//Preselected Option
			containercontent += '<input type="radio" name="' + id + '" value="' + options[i] + '" checked="checked">' + options[i] + '</br>'
		}
	}
	
	//Radio footer
	containercontent += '</form>' +
 				'</td>' +
 			'</tr>' +
 			'</table>';
 			
 	return containercontent;

},


/**
 * @public
 * 
 * Creates an checkbox element
 * 
 * @param string id
 * 		id of the element
 * @param string description
 * 		name of the element which will be shown
 * @param array[] options
 * 		array of strings representing the options of the checkboxes
 * @param array selecteditems
 * 		array of integers representing the preselected items of the checkboxes
 * @param string attributes
 *		attributes which will be passed to the specific element
 * @param boolean visibility
 * 		if false the element will be collapsed initially
 */
createCheckBox: function(id, description, options, selecteditems, attributes, visibility){
	
	//checkbox header
	var containercontent = '<table class="stb-table stb-checkbox-table ' + this.id + '-table ' + this.id + '-checkbox-table"' +
			(visibility ? '' : 'style="display:none;"')  + 'id="' + this.id + '-table-' + id +'">' +
 			'<tr>' +
 				//Name of checkboxes
 				'<td class="stb-checkbox-col1 ' + this.id + '-checkbox-col1">' +
				description +
				'</td>' +
			'</tr><tr>'+
 				//checkboxes
 				'<td class="stb-checkbox-col2 ' + this.id + '-checkbox-col2">' +
					'<form class="stb-checkbox ' + this.id + '-checkbox" id="' + id +'"  ' + attributes + '>';
	
	//Generate Options from the aray				
	for( var i = 0; i < options.length; i++ ){
		if(!this.isInArray(i,selecteditems)){
			//Normal option
			containercontent += '<input type="checkbox" ' +
					                    'name="' + id + '"' + 
					                    ' tabindex="' + this.lastindex++ + '"' +
					                    ' value="' + options[i] + '">' + options[i] + '<br>'
		} else {
			//Preselected Option
			containercontent += '<input type="checkbox" ' +
					                   'name="' + id + '"' +
					                   ' tabindex="' + this.lastindex++ + '" ' +
					                   ' value="' + options[i] + '" checked="checked">' + options[i] + '<br>'
		}
	}
	
	//Radio footer
	containercontent += '</form>' +
 				'</td>' +
 			'</tr>' +
 			'</table>';
 			
 	return containercontent;

},

/**
 * @private 
 * 
 * Checks if the given item is in the given array
 * 
 * @param item
 * 		the item which will be searched in the array
 * @param array
 * 		array the item will be searched in
 */
isInArray: function(item ,array){
	for(var j = 0; j< array.length; j++ ){
		if(item==array[j]) return true;
	}
	return false;
},

/**
 * @public
 * 
 * Creates an text element
 * 
 * @param string id
 * 		id of the element
 * @param string description
 * 		name of the element which will be shown
 * @param string attributes
 *		attributes which will be passed to the specific element
 * @param boolean visibility
 * 		if false the element will be collapsed initially
 */
createText: function(id, description, attributes ,visibility){
		
		var imgtag = '';
		//will look for the proper imagetag within the description
		//i: info w: warning e: error
		var imgregex = /(\([iwe]\))(.*)/;
		var regexresult;
		if(regexresult = imgregex.exec(description)) {
			//select the icon which will be shown in front of the text
			switch (regexresult[1])
			{
				case (image = '(i)'):
		  			//Info Icon
					imgtag = '<img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/info.gif"/>';
		  			break
				case (image = '(w)'):
					//TODO: Error Icon should be replaced by a prober one
		  			imgtag = '<img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/warning.png"/>';
		  			break
				case (image = '(e)'):
					//TODO: Error Icon should be replaced by a prober one
		  			imgtag = '<img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/delete_icon.png"/>';
		  			break
				default:
					imgtag = '';
			}
			description = regexresult[2];
		}
		
		var containercontent = '<table class="stb-table stb-text-table ' + this.id + '-table ' + this.id + '-text-table"' +
			(visibility ? '' : 'style="display:none;"')  + 'id="' + this.id + '-table-' + id +'">' +
 			'<tr>' +
 				//Text
 				'<td class="stb-text-col1 ' + this.id + '-text-col1">' + imgtag +
				'&#32<span class="stb-text ' + this.id + '-radio" id="'+ id +'" id="'+ id + '" '+ attributes +'>' + description + '</span>' + 
				'</td>' +
 			'</tr>' +
 			'</table>';
			
	return containercontent;

},

/**
 * @public
 * 
 * Creates a button
 * 
 * @param string id
 * 		id of the element
 * @param string btLabel
 * 		The label of the button
 * @param string callback
 * 		The function that will be called when the button is clicked
 * @param string attributes
 *		attributes which will be passed to the specific element
 * @param boolean visibility
 * 		if false the element will be collapsed initially
 */
createButton: function(id, btLabel, callback, attributes ,visibility){
	
	var containercontent = 
		'<table class="stb-table stb-button-table ' + this.id + '-table ' + this.id + '-button-table"' +
			(visibility ? '' : 'style="display:none;"')  + 'id="' + this.id + '-table-' + id +'">' +
		'<tr>' +
			//Text
			'<td class="stb-button-col ' + this.id + '-button-col">' +
			 '<input type="button" id="' + id + 
			 	'" name="' + id + 
			 	'" value="' + btLabel + 
			 	'" '+ attributes +
			 	'onclick="' + callback + 
			 	'">' +
			'</td>' +
		'</tr>' +
		'</table>';
			
	return containercontent;

},


/**
 * @public
 * 
 * Creates an link element
 * 
 * @param string id
 * 		id of the element
 * @param array[] functions
 *  	array of arrays describing the functions
 *  	elements are two dimensional arrays 
 * 		array[0] function which will be called
 * 		array[1] string representing the function in the htmlpage
 * 		array[2] string representing the id of the function in the htmlpage (optional)
 * 		array[3] string representing the alternativ text in the htmlpage if function is disabled (optional)
 * 		array[4] string representing the id of the alternative text in the htmlpage (optional)
 * 		Example: [['alert(\'f1\')','function1'],['alert(\'f2\')','function2'],['alert(\'f3\')','function3']
 * @param string attributes
 * 		not used yet
 *		attributes which will be passed to the specific element
 * @param boolean visibility
 * 		if false the element will be collapsed initially
 */
createLink: function(id, functions, attributes ,visibility){
	
	//function list header
	var containercontent = '<table class="stb-table stb-link-table ' + this.id + '-table ' + this.id + '-link-table"' +
			(visibility ? '' : 'style="display:none;"')  + 'id="' + this.id + '-table-' + id +'">';
			
	//select layout or more precisely how much columns
	switch(functions.length){
		case 1: var tablelayout = 1; break;
		case 2: var tablelayout = 2; break;
		//looks better with 2+2 than 3+1
		case 4: var tablelayout = 2; break;
		//all other cases choose 3 columns
		default: var tablelayout = 3;
	}
	
	var i = 0;
	//Generate columns
	//Schema for generating class names
	//1. class ln-dt-$numberofcolumns
	//2. class $containername-ln-dt-$numberofcolumns
	//3. class $elementid-ln-dt-$numberofcolumns
	for(var row = 0; row*tablelayout < functions.length; row++ ){
		containercontent += '<tr class=" ln-tr-' + tablelayout + ' '+ this.id +'-ln-tr-'+ tablelayout +' '+ id +'-ln-tr-'+ tablelayout +'">';
		//Generate rows
		//Schema for generating class names
		//1. class ln-dt-$numberofcolumns-$speficcolumnoftd
		//2. class $containername-ln-dt-$numberofcolumns-$speficcolumnoftd
		//3. class $elementid-ln-dt-$numberofcolumns-$speficcolumnoftd  
		for(var column=0; column<tablelayout; column++){
			containercontent += '<td class=" ln-td-' + tablelayout + '-' + column +' '+ this.id +'-ln-td-'+ tablelayout + '-' + column +' '+ id +'-ln-td-'+ tablelayout + '-' + column +'">';
			//GenerateLink
			if(i<functions.length){
				switch (functions[i].length) {
					case 2 :
						//Function with displayed text
		  				containercontent += '<a tabindex="'+ this.lastindex+++'" + href="javascript:' + functions[i][0] + '">'+ functions[i][1]+'&#32</a>';
		  				break;
					case 3 :
						//Function with id and displayed text
					  	containercontent += '<a tabindex="'+ this.lastindex+++'" + id="' + functions[i][2] + '" href="javascript:' + functions[i][0] + '">'+ functions[i][1]+'&#32</a>';
		  				break;
					case 5 :
						//Function with id and displayed text
					  	containercontent += '<a tabindex="'+ this.lastindex+++'" + id="' + functions[i][2] + '" href="javascript:' + functions[i][0] + '">'+ functions[i][1]+'&#32</a>';
						//altnative text with id, which will be shown if functions is disabled 
						containercontent += '<span id="' + functions[i][4] + '" style="display: none;">' + functions[i][3] + '</span>'
		  				break;		
					default:
		  				//do nothing
					}
				//select next function by increasing index 
				i++;				
			}
			containercontent += '</td>';
		}
		containercontent += '</tr>';
	}
	
	//deprecated functions
	//for(var i = 0; i< functions.length; i++ ){	
	//}
				
 	//function list footer
 	containercontent += '</table>';
			
	return containercontent;
	
},

/**
 * Changes the ID of the DOM-element <obj> to <newID>. The IDs of <obj>'s parents
 * are updated accordingly to maintain consistent names.
 * 
 * @param Object obj
 * 		The object that gets a new ID
 * @param string newID
 * 		The new ID of the object
 */
changeID: function(obj, newID) {
	
	var oldID = obj.id;
	var table = $(this.id + '-table-' + oldID);
	if (table) {
		table.id = this.id + '-table-' + newID;
	}
	obj.id = newID;
},

/**
 * @public
 * 		removes an element from the dom-tree
 * @param object element
 * 		element to remove
 */
remove: function(element){
	if(element instanceof Array){
		//Add elements
		for(var i = 0; i< element.length; i++ ){
			$(this.id+'-table-'+element[i]).remove();
			this.eventManager.deregisterEventsFromItem(element[i]);
		}
	} else {
		$(this.id+'-table-'+element).remove();
		this.eventManager.deregisterEventsFromItem(element);
	}	
	this.rebuildTabindex($(this.id + '-box'));
	autoCompleter.deregisterAllInputs();		
	autoCompleter.registerAllInputs();	
},

/**
 * @public Checks all child nodes for the attribute tabindex and rebuilt the correct order
 * 
 * @param object rootnode 
 * 		element which child elements will be updated
 */
rebuildTabindex: function(rootnode){
		//Check
		if(rootnode == null) return;
		//reset last index
		this.lastindex = this.startindex;
		//Get childs
		var elements = rootnode.descendants();
		//update each child
		elements.each(this.updateTabindex.bind(this));
		
},


/**
 * @private Checks element for the attribute tabindex and sets it to a correct value
 * 
 * @param object element
 * 		the element which will be updated 
 */
updateTabindex: function(element){
	//Check if tabindex is set, if yes update it
	if(element.readAttribute('tabindex')!= null && element.readAttribute('tabindex')!= 0){
		element.writeAttribute('tabindex',this.lastindex++);
	}	
},



/**
 * @public appends container to the menu
 * 
 * @param string content
 * 		array of strings
 * 		the html code of the element(s) which will be added
 */
append: function(content){
	if(content instanceof Array){
		//Add elements
		for(var i = 0; i< content.length; i++ ){
			new Insertion.Bottom($(this.id + '-box'), content[i]);
		}
	} else {
		new Insertion.Bottom($(this.id + '-box'), content);
	}
},

/**
 * @public insert new element to the menu after the given element
 * 
 * @param string id
 * 		the name of the element, after which the new one will be added
 * @param string content
 * 		the html code which will be added
 */
insert: function(id, content){
	if(content instanceof Array){
		//Add elements
		for(var i = 0; i< content.length; i++ ){
			new Insertion.After($(this.id + '-table-' + id), content[i]);
		}
	} else {
		new Insertion.After($(this.id + '-table-' + id), content);
	}
	
},
/**
 * @public replace an element with a new one
 * 
 * @param string id
 * 		the name of the element which will be replaced
 * @param string content
 * 		the html code which will be added afterwards
 */
replace: function(id, content){
    $(this.id + '-table-' + id).replace(content);
},
/**
 * @public shows or hide an element
 * 
 * @param string id
 * 		the name of the element, which will be shown or hidden
 * @param boolean visibility
 * 		true for show, false for hide 
 */
show: function(id, visibility){
	var obj = $(this.id + '-table-' + id);
	if (!obj) {
		obj = $(id);
	}
	if (obj) {
		if (visibility) {
			obj.show();
		} else {
			obj.hide();
		}
	}
	 
},

/**
 * @public
 * 
 * Checks, if the element with the given id is visible.
 * @param string id
 * 		The id of the element whose visibility is checked.
 * 
 * @return bool
 * 		true, if the element is visible and
 * 		false otherwise
 */
 isVisible: function(id) {
	var obj = $(this.id + '-table-' + id);
	if (!obj) {
		obj = $(id);
	}
	return (obj) ? obj.visible() : false;
 	
 },
 
/**
 * This function must be called after the last element has been added or after
 * the container has been modified.
 */
finishCreation: function() {
	this.eventManager.deregisterAllEvents();
	var desc = $(this.id+'-box').descendants();
	for (var i = 0, len = desc.length; i < len; i++) {
		var elem = desc[i];
		if (elem.type == 'text') {
			this.eventManager.registerEvent(elem,'blur',gSTBEventActions.onBlur.bindAsEventListener(gSTBEventActions));
			this.eventManager.registerEvent(elem,'keyup',gSTBEventActions.onKeyUp.bindAsEventListener(gSTBEventActions));
		} else if (elem.type == 'radio') {
			this.eventManager.registerEvent(elem,'click',gSTBEventActions.onClick.bindAsEventListener(gSTBEventActions));
			this.eventManager.registerEvent(elem,'keyup',gSTBEventActions.onKeyUp.bindAsEventListener(gSTBEventActions));
		} else if (elem.type == 'select-one'){
			this.eventManager.registerEvent(elem,'change',gSTBEventActions.onChange.bindAsEventListener(gSTBEventActions));
			this.eventManager.registerEvent(elem,'keyup',gSTBEventActions.onKeyUp.bindAsEventListener(gSTBEventActions));
		} else if (elem.type == 'checkbox'){
			this.eventManager.registerEvent(elem,'keyup',gSTBEventActions.onKeyUp.bindAsEventListener(gSTBEventActions));
		}
	}
	// install the standard event handlers on all input fields
	autoCompleter.deregisterAllInputs();		
	autoCompleter.registerAllInputs();
	this.frameworkcontainer.contentChanged();		
	this.rebuildTabindex($(this.id + '-box'));
},

/**
 * Deregisters all events and the autocompleter from all input fields.
 */
release: function() {
	this.eventManager.deregisterAllEvents();
	autoCompleter.deregisterAllInputs();		
	autoCompleter.registerAllInputs();		
},

/**
 * Sets value of input field and corrects size in ie
 * This is a workaround for IE, which sets the size 
 * of input fields too large in the case the value 
 * attribute is set with a long string bt javascript
 */
setInputValue: function(id,presetvalue){
	if (navigator.appVersion.match(/MSIE 7.0/)) {
		var parentwidth = $(id).getWidth();
		$(id).value = presetvalue;
		$(id).setStyle({width: parentwidth + "px"});
	} else {
		$(id).value = presetvalue;
	}
},

/**
 * @public this is just a test function adding some sample boxes
 * 
 * @param none
 */
foo: function(){
	this.createContainerBody('',RELATIONCONTAINER,"Ueberschrift");
	this.showSandglass($(this.id + '-box'));
	this.append(this.createInput(700,'Test','', 'alert(\'loeschmich\')','',true));
	this.append(this.createText(701,'Test','',true));
	this.append(this.createDropDown(702,'Test',['Opt1','Opt2','Opt3'],'alert(\'loeschmich\')',2,'',true));
	this.insert('702',this.createRadio(703,'Test',['val1','val2','val3'],2,'',true));
	this.append(this.createCheckBox(704,'Test',['val1','val2','val3','val4'],[1,3],'',true));
	this.append(this.createLink(705,[['smwhgLogger.log(\'Testlog\',\'error\',\'log\');','Log']],'',true));
	this.append(this.createLink(706,[['alert(\'f1\')','function1'],['alert(\'f2\')','function2','fid2']],'',true));
	this.append(this.createLink(707,[['alert(\'f1\')','function1'],['alert(\'f2\')','function2','fid2'],['alert(\'f3\')','function3','fid3','alt-f3','faltid3']],'',true));
	this.append(this.createLink(708,[['alert(\'f1\')','function1'],['alert(\'f2\')','function2','fid2'],['alert(\'f3\')','function3','fid3','alt-f3','faltid3'],['alert(\'f4\')','function4']],'',true));
	this.append(this.createLink(709,[['alert(\'f1\')','function1'],['alert(\'f2\')','function2','fid2'],['alert(\'f3\')','function3','fid3','alt-f3','faltid3'],['alert(\'f4\')','function5'],['alert(\'f5\')','function5']],'',true));
	this.rebuildTabindex($(this.id + '-box'));
	//this.remove(['703','704']);
	this.hideSandglass();
	//$('faltid3').show();
	//$('faltid3').hide();
	this.showSandglass($(this.id + '-box'));
	ctbHandler = new CTBHandler();
	ctbHandler.addContainer('category',this);
	var obj = smw_ctbHandler.findContainer('703');
	obj.replace('701',obj.createText(701,'(e) Testreplace','',true))
	this.hideSandglass();

}


};// End of Class ContainerToolBar

/**
 *  Handler which allows to register Containerobjects and regain them by an elementid
 */
var CTBHandler = Class.create();
CTBHandler.prototype = {
	
/**
 * Constructor
 */
initialize: function() {
	this.containerlist = new Array();
},

/**
 * @public registers an given containerobj with the containerid as index 
 * 
 * @param String containerid
 * 	ContainerId
 * @param String containerobj
 * 	ContainerObject
 */
addContainer: function(containerid,containerobj){
	var pos = this.posInArray(containerid);
	
	if(pos<0){
		this.containerlist.push([containerid,containerobj])
	} else {
		this.containerlist[pos] = [containerid,containerobj];	
	}
},

/**
 * @private 
 * 
 * Checks if the given containerid is in the given array
 * 
 * @param containerid
 * 		the containerid which will be searched in the array
 */
posInArray: function(containerid){
	for(var j = 0; j< this.containerlist.length; j++ ){
		if(containerid==this.containerlist[j][0]) return j;
	}
	return -1;
},

/**
 * @public 
 * 
 * @param 
 * 	
 * 	
 */
findContainer: function(elementid){
	//Get list with all ancestors
	var elem = $(elementid);
	if (!elem) {
		return false;
	}
	var ancestorlist = elem.ancestors();
	//Look for an ancestor with a if that probably represents the containerbody-div
	for(var j = 0; j< ancestorlist.length; j++ ){
		//Read id
		var elementid = ancestorlist[j].readAttribute('id');
		//RegEx to isolate the containerid
		var regexsearch = /(.*)-box/g
		var regexresult;
		if(regexresult = regexsearch.exec(elementid)) {
			//Look if the containerid is registered and return the object  
		 	var pos = this.posInArray(regexresult[1]);
			if(pos>=0) return this.containerlist[pos][1];
		}
	}
	//Nothing found :(
	return false;
}

} //End of Class CTBHandler

//Global ctbHandler where all container framework objects register themself
var smw_ctbHandler = new CTBHandler();



//Test in CatContainer

/*
setTimeout(function() { 
	//categorycontainer = new divContainer(CATEGORYCONTAINER);
	var conToolbar = new ContainerToolBar('category',900,catToolBar.categorycontainer);
	Event.observe(window, 'load', conToolbar.createContainerBody.bindAsEventListener(conToolbar));
	setTimeout(conToolbar.foo.bind(conToolbar),1000);
},3000);
*/