//Second implementation of marking Templates in Mediawiki
var Marker = Class.create();
Marker.prototype = {
	
	/**
 	* @public constructor 
 	* 
 	* @param rootnode string
 	* 			root element where all elements which have to be marked are child of
 	*/
	initialize: function(rootnode) {
		//root node from which all descendants will be checked for marking 
		//storing the object directly would cause errors, since in most cases the object 
		//is still not present when the constructor is called
		this.rootnode = rootnode;
		//
		this.markerindex = 0;		
	},
	
	/**
 	* @public marks an element with a transparent layer  
 	* 
 	* @param divtomark object
 	* 			element to mark
 	*/
	transparencyMarker: function(divtomark) {
		if(divtomark == null) return;
		//Create and insert markerdiv
		var marker = '<div id="' + this.markerindex + '-marker" class="div-marker"></div>';
		new Insertion.After(divtomark, marker );		
		//Get borderwidth defined in css for the div-marker
		var borderwidthx = Number(this.getBorderWidth(this.markerindex+"-marker","left")) + Number(this.getBorderWidth(this.markerindex+"-marker","right"));
		if(isNaN(Number(borderwidthx))) return;
		var borderwidthy = Number(this.getBorderWidth(this.markerindex+"-marker","top")) + Number(this.getBorderWidth(this.markerindex+"-marker","bottom"));
		if(isNaN(Number(borderwidthy))) return;
		//Set width for the marker minus borderwidth so it fits the original element which should be marked
		var width = divtomark.offsetWidth - borderwidthx;
		$(this.markerindex+"-marker").style.width =  + width + "px";
		var height = divtomark.offsetHeight - borderwidthy;
		$(this.markerindex+"-marker").style.height = height + "px";
		//Set position of the marker
		$(this.markerindex+"-marker").style.top = divtomark.offsetTop + "px";
		$(this.markerindex+"-marker").style.left = divtomark.offsetLeft + "px";
		//increase marker index
		this.markerindex++;				
	},
	
	/**
 	* @public marks an element with an image laying above the upper left corner
 	* 
 	* @param divtomark object
 	* 			element to mark
 	*/
	iconMarker: function(divtomark,links) {
		if(divtomark == null) return;
		//Create and insert markerdiv
		var marker = '<div id="' + this.markerindex + '-marker" class="span-marker">';
			//Check if multiple links has been passed and generate one clickable picture for each
			if( links  instanceof Array){ 
				links.each(function(link){
					marker += '<a href="'+ link +'"><img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/templatemarker.png"/></a>';
				});
			// Check if only one link has been passe	
			} else if ( links  instanceof String || typeof(links) == "string"){
				marker += '<a href="'+ links +'"><img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/templatemarker.png"/></a>'; 
			//If nothing has been paased, only mark it with a non clickable picture
				} else {
					marker += '<img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/templatemarker.png"/>';
				}
		marker += '</div>';
		new Insertion.After(divtomark, marker );
		//Set position of the marker		
		$(this.markerindex+"-marker").style.top = divtomark.offsetTop + "px";
		$(this.markerindex+"-marker").style.left = divtomark.offsetLeft + "px";
		//increase marker index				
		this.markerindex++;			
	},

	/**
 	* @public marks an text node with an proper span and image laying above the upper left corner
 	* 
 	* @param node object
 	* 			text node to mark
 	*/
	textMarker: function(node,links){
				//Create span element
				var span = document.createElement('span');
				//Create attributes for span element
				var idattr = document.createAttribute("id");
					idattr.nodeValue = this.markerindex+"-textmarker";
					span.setAttributeNode(idattr);
				//create Classes for marking and colorizing the span so it can be removed later
				var classattr = document.createAttribute("class");
					classattr.nodeValue = "aam_template_highlight text-marker";
					span.setAttributeNode(classattr);
				//Create textcontent for span attribute
				var textdata = document.createTextNode(node.nodeValue);
					span.appendChild(textdata);
				//var replacement 
				node.parentNode.replaceChild(span, node);
				this.iconMarker($(this.markerindex+"-textmarker"),links);
	},
	
	/**
 	* @public Gets the border with of an element as number, if it's defined in pixel in the css
 	* 
 	* @param 	el object
 	* 				element with border
 	* 			borderposition string
 	* 				location of the border, possible values: "left", "right", "bottom", "top"
 	*/
	getBorderWidth: function(el, borderposition)
	{
		//retrieve css value
		var borderwidth = $(el).getStyle("border-"+borderposition+"-width");
		//parse for px unit
		var borderregex = /(\d*)(px)/;
		var regexresult;
		if(regexresult = borderregex.exec(borderwidth)) {
			return regexresult[1];
		} else {
			return "";
		}

	}, 
	
	/**
	* DEPRECATED! Should not be working anymore with the new tags 
 	* @public Gets all descendants (from this.rootnode) and checks if there are elements to mark  
 	* 
 	* @param
 	*/
	checkForTemplates: function(){
			rootnode = $(this.rootnode);
			this.removeMarkers(rootnode);
			//Check
			if(rootnode == null) return;
			
			//Get childs
			var elements = rootnode.descendants();
			
			//check each child
			elements.each(this.checkElement.bind(this));
			
	},
	
	/**
	 * DEPRECATED! Should not be working anymore with the new tags
	 * @private Checks if the element belongs to the template indicators and if yes marks it  
	 * 
	 * @param  element object 
	 * 				element to check
	 */
	checkElement: function(element){
		//Check if element has a type="template" attribute
		if(element.readAttribute('type')!= null && element.readAttribute('type')== "template"){
			//get first child
			firstchild = element.firstDescendant();
			//Mark Div or table with image and overlay
			if(firstchild != null && (firstchild.tagName.toLowerCase() == 'div' || firstchild.tagName.toLowerCase() == 'table')){
				this.transparencyMarker(firstchild);
				this.iconMarker(firstchild);		
			} 
			//Mark text with image
			else {
				this.iconMarker(element);
			}
		}	
	},
	
	/**
 	* @public Gets all descendants and removes markers 
 	* 
 	* @param rootnode object 
 	* 				Element which descendants will be checked for removing
 	*/
	removeMarkers: function(){
			var rootnode = $(this.rootnode);
			//Check rootnode
			if(rootnode == null) return;
			//Get childs
			var elements = rootnode.descendants();
			//remove marker
			elements.each(this.removeMarker.bind(this));
			//reset marker index
			this.markerindex = 0; 
	},
	
	/**
 	* @private Check if the element is an marker and if yes removes it
 	* 
 	* @param element object 
 	* 				element to check
 	*/	
	removeMarker: function(element){
		//firstchild.tagName.toLowerCase() == 'span'
		//debugger;
		if(element == null) return;
		//Check if tabindex is set, if yes update it
		if(element.readAttribute('class')!= null && (element.readAttribute('class')== "span-marker" || element.readAttribute('class')== "div-marker")){
			element.remove();
		}
		//Check for textmarkers and remove only the span not the text 
		if(element.readAttribute('class')!= null && element.readAttribute('class')== "text-marker"){
			element.replace(element.innerHTML);
		}	
	},
	
	/**
 	* @public Checks all child nodes for templates and marks the proper Elements
 	* 
 	* @param 
 	*/	
	markNodes: function(){
		//get rootnode 
		var rootnode = $(this.rootnode);
		//Stores the templatename and the id of the current open but not closed template
		var currentTmpl = null;
		var currentTmplid = null;		
		//Get first Child
		var element = rootnode.firstChild;
		//Walk over every next sibling
		//this uses plain javascript functions, since prototype doesn't support textnodes
		while( element != null){
			//Get current node and set element to the next sibling so it won't be effected by changes of the current node
			var node = element;
			element = element.nextSibling;
			//If nodetyp is textnode and template tag is open but not closed
			if(node.nodeType == 3 && currentTmpl != null ){
				//mark text
				this.textMarker(node,wgServer + wgScript+ "/" +currentTmpl);
			//If nodetype is elementnode
			} else if(node.nodeType == 1 ){
				//right now only tables and anchors are treated.
				//but should be easy to extend it to other elements representing boxes
				switch(node.tagName.toLowerCase()){	
				case 'table':
					//Get template tags from the sub node
					var anchors = this.getTemplateAnchors(node);
					//Array to store the templatenames in
					var links = new Array();
					//Add templatename of the current open template
					if (currentTmpl != null ) links.push(wgServer + wgScript+ "/" +currentTmpl);
					//Add all templatenames of all opening anchors which can be found in the table's descendants
					//Templatenames are stored in the third field of the returned array
					for (var index = 0, len = anchors[2].length; index < len; ++index) {
  						var subTmpl = anchors[2][index];
  						links.push(wgServer + wgScript+ "/" + subTmpl);
					}
					//Check if the opened anchor is closed within the table
					//and if yes remove it from the buffer
					if(anchors[1].indexOf(currentTmplid)!=-1){
						currentTmpl = null;
  						currentTmplid = null;
					} 
					//Remove all anchors from the opening list, which are closed within the table
					//if the list empty afterwards, then all anchors were close
					//otherwise there is an anchor which will be close further on in the dom tree and there
					//needs to be buffered for marking elements following after the table
					var openanchor = anchors[0].without(anchors[1])[0];
					if(openanchor!=null){
						currentTmplid = openanchor;
						currentTmpl = anchors[2][anchors[0].indexOf(openanchor)]
					}
					//Mark table with an tranparent overlay and icons 
					this.transparencyMarker(node);
					this.iconMarker(node,links);
					this.markerindex++;
					break;
				case 'a':
					//Check if this is an opening anchor, indicating that a template starts 
					if($(node).readAttribute('type')=='template'){
  						currentTmplid = node.readAttribute('id');
  						currentTmpl = node.readAttribute('tmplname');			
  						break;
  					}
  					//Check if this is an closing anchor, indicating that a template ends
  					if($(node).readAttribute('type')=='templateend'){
  						currentTmpl = null;
  						currentTmplid = null;
  						break;
  					}
				default:
					//TODO: Treat other elements like paragraph etc.
				}	
			}
		}	
	},
	
	/**
 	* @public looks for opening/closing anchors and returns an 2-dimensonal array
 	* 	array[0]: array id's of the openening anchors
 	* 	array[1]: array element id's of the closing anchors without '_end' for further matching of both lists 
 	*   array[2]: array templatenames of the openening anchors
 	* 
 	* @param element object
 	* 			element which should be checked for matching anchors in its descendants
 	*/
	getTemplateAnchors: function(node){
		//Get all descendants of the node
		var elements = $(node).descendants();
		//Arrays storing the anchors and being returned 
		var starttags = new Array();
		var endtags = new Array();
		var templates = new Array();
		//return empty lists if no descendants are found
		if(elements == null) return [starttags,endtags,templates];
		//Check all descendants
		for (var index = 0, len = elements.length; index < len; ++index) {
  			//Get current element
  			var element = elements[index];
  			//Check if it is an opening anchor 
  			if(element.readAttribute('type')=='template'){
  				//Push into result into return list
  				//id
  				starttags.push(element.readAttribute('id'));
  				//templatename (should include namespace)
  				templates.push(element.readAttribute('tmplname'));
  				continue;
  			}
  			//Check if it is an opening anchor 
  			if(element.readAttribute('type')=='templateend'){
  				//Push into result into return list
  				//id
  				endtags.push(element.readAttribute('id').sub('_end',''));
  				continue;
  			}
  			
		}
		return [starttags,endtags,templates];
	},
	
	
	
	//Samplepage for develepoment
	//TODO: Remove if not needed anymore
	samplePage: function(){
		$("innercontent").replace('<div id="innercontent">' +
				'<a id="1" type="template" tmplname="Template:1"></a>normal text<br><a id="1_end" type="templateend"></a>' +
				'normal text<br>' +
				'<a id="2" type="template" tmplname="Template:2"></a>Text vor der Tabelle' +
				'<table><tr><td>Eins</td><td>Zwei</td></tr><a id="2_end" type="templateend"/></a>' +
				'<a id="3" type="template" tmplname="Template:3"></a><tr><td>Drei</td><td>Vier</td></tr></table>'+
				'<br>Hier kommt noch eine schoene Tabellen unterschrift<a id="3_end" type="templateend"/></a>' +
				'<br>normal text<br>' +
				'normal text<br>' +
				'normal text<br>' +
				'<a id="4" type="template" tmplname="Template:4"></a>normal text<br><a id="4_end" type="templateend"></a>' +
				'</div>');
		//this.checkForTemplates();
		this.markNodes();
		//this.removeMarkers();
	}

}


var smwhg_marker = new Marker('innercontent');
Event.observe(window, 'resize', smwhg_marker.markNodes.bind(smwhg_marker));
Event.observe(window, 'load', smwhg_marker.markNodes.bind(smwhg_marker));

/*
setTimeout(function() { 
	setTimeout(smwhg_marker.samplePage.bind(smwhg_marker),1000);
},3000);
//*/