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
		this.transparencymarkerlist = new Array();
		this.iconmarkerlist = new Array();		
	},
	
	/**
 	* @public marks an element with a transparent layer  
 	* 
 	* @param divtomark object
 	* 			element to mark
 	*/
 	
 	insertMarkers: function(){
 		$(this.rootnode).hide();
 		// transparencyMarkers
 		for(var index=0; index < this.transparencymarkerlist.length; index++){
			if($(this.transparencymarkerlist[index][2])){
	 			if($(this.iconmarkerlist[index][2]).tagName.toLowerCase() == 'div'){
	 				if( $(this.iconmarkerlist[index][2]).style.position == ""){
	 					$(this.iconmarkerlist[index][2]).style.position = "relative";
	 				}
	 				 new Insertion.Bottom($(this.transparencymarkerlist[index][2]), this.transparencymarkerlist[index][1]);
					//Set position of the marker		
					$(this.transparencymarkerlist[index][0]).setStyle( {top:  "0px"});
					$(this.transparencymarkerlist[index][0]).setStyle( {left: "0px"});
	 			} else { 
	 				new Insertion.After(this.transparencymarkerlist[index][2], this.transparencymarkerlist[index][1]);
					//Set position of the marker		
					$(this.transparencymarkerlist[index][0]).setStyle( {top: this.transparencymarkerlist[index][3] + "px"});
					$(this.transparencymarkerlist[index][0]).setStyle( {left: this.transparencymarkerlist[index][4] + "px"});
	 			}
				//calculate and set width and height
				var borderwidth = Number(this.getBorderWidth($(this.transparencymarkerlist[index][0]),"left")) + Number(this.getBorderWidth($(this.transparencymarkerlist[index][0]),"right"));
				if(isNaN(Number(borderwidth))) return;
				var borderheight = Number(this.getBorderWidth($(this.transparencymarkerlist[index][0]),"top")) + Number(this.getBorderWidth($(this.transparencymarkerlist[index][0]),"bottom"));
				if(isNaN(Number(borderheight))) return;
				var mheight = this.transparencymarkerlist[index][5] - borderheight;
				var mwidth = this.transparencymarkerlist[index][6] - borderwidth;
				if(mheight > 0 && mwidth > 0 ){
					$(this.transparencymarkerlist[index][0]).setStyle({height: mheight + "px"});
					$(this.transparencymarkerlist[index][0]).setStyle({width: mwidth + "px"});
				}
			}
 		}
 		///*
 		//iconMarkers
 		for(var index=0; index < this.iconmarkerlist.length; index++){
 			if($(this.iconmarkerlist[index][2]).tagName.toLowerCase() == 'div'){
 				if( $(this.iconmarkerlist[index][2]).style.position == ""){
 					$(this.iconmarkerlist[index][2]).style.position = "relative";
 				}
 				new Insertion.Bottom(this.iconmarkerlist[index][2], this.iconmarkerlist[index][1]);
 				//Set position of the marker		
				$(this.iconmarkerlist[index][0]).setStyle( {top:  "0px"});
				$(this.iconmarkerlist[index][0]).setStyle( {left: "0px"});
 			} else {
 				new Insertion.After($(this.iconmarkerlist[index][2]), this.iconmarkerlist[index][1]);
				//Set position of the marker		
				$(this.iconmarkerlist[index][0]).setStyle( {top: this.iconmarkerlist[index][3] + "px"});
				$(this.iconmarkerlist[index][0]).setStyle( {left: this.iconmarkerlist[index][4] + "px"});
 			}
 		} //*/
 		$(this.rootnode).show();
 	},
 	
 	
	transparencyMarker: function(divtomark) {
		if(divtomark == null) return;
		//Create and insert markerdiv
		var marker = '<div id="' + this.markerindex + '-marker" class="div-marker"></div>';		
		//Get borderwidth defined in css for the div-marker
		//var borderwidthx = Number(this.getBorderWidth(this.markerindex+"-marker","left")) + Number(this.getBorderWidth(this.markerindex+"-marker","right"));
		//if(isNaN(Number(borderwidthx))) return;
		//var borderwidthy = Number(this.getBorderWidth(this.markerindex+"-marker","top")) + Number(this.getBorderWidth(this.markerindex+"-marker","bottom"));
		//if(isNaN(Number(borderwidthy))) return;
		//Set width for the marker minus borderwidth so it fits the original element which should be marked
		var width = divtomark.offsetWidth; //- borderwidthx;
		var height = divtomark.offsetHeight; //- borderwidthy;
		//Set position of the marker
		var top = divtomark.offsetTop;
		var left = divtomark.offsetLeft;
		//increase marker index
		this.transparencymarkerlist.push( new Array(this.markerindex+"-marker", marker, $(divtomark).identify(), top, left, height, width ))
		this.markerindex++;	
		/* on the fly code
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
		$(this.markerindex+"-marker").style.width = width + "px";
		var height = divtomark.offsetHeight - borderwidthy;
		$(this.markerindex+"-marker").style.height = height + "px";
		//Set position of the marker
		$(this.markerindex+"-marker").style.top = divtomark.offsetTop + "px";
		$(this.markerindex+"-marker").style.left = divtomark.offsetLeft + "px";
		//increase marker index
		this.markerindex++;//*/	
	},
	
	/**
 	* @public marks an element with an image laying above the upper left corner
 	* 
 	* @param divtomark object
 	* 			element to mark
 	*/
	iconMarker: function(divtomark,links) {
		///*
		if(divtomark == null) return;
		//Create and insert markerdiv
		var marker = '<div id="' + this.markerindex + '-marker" class="icon-marker">';
			//Check if multiple links has been passed and generate one clickable picture for each
			if( links  instanceof Array){ 
				links.each(function(link){
					marker += '<a href="'+ wgServer + wgScript+ "/" +link +'"><img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/templatemarker.png"/></a>';
				});
			// Check if only one link has been passe	
			} else if ( links  instanceof String || typeof(links) == "string"){
				marker += '<a href="'+ wgServer + wgScript+ "/" + links +'"><img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/templatemarker.png"/></a>'; 
			//If nothing has been paased, only mark it with a non clickable picture
				} else {
					marker += '<img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/templatemarker.png"/>';
				}
		marker += '</div>';
		//Set position of the marker		
		var top = divtomark.offsetTop;
		var left = divtomark.offsetLeft;
		this.iconmarkerlist.push( new Array(this.markerindex+"-marker", marker, $(divtomark).identify(), top, left))
		//increase marker index				
		this.markerindex++;//*/
				
		/*
		if(divtomark == null) return;
		//Create and insert markerdiv
		var marker = '<div id="' + this.markerindex + '-marker" class="icon-marker">';
			//Check if multiple links has been passed and generate one clickable picture for each
			if( links  instanceof Array){ 
				links.each(function(link){
					marker += '<a href="'+ wgServer + wgScript+ "/" +link +'"><img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/templatemarker.png"/></a>';
				});
			// Check if only one link has been passe	
			} else if ( links  instanceof String || typeof(links) == "string"){
				marker += '<a href="'+ wgServer + wgScript+ "/" + links +'"><img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/templatemarker.png"/></a>'; 
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
		this.markerindex++;//*/			
	},

	/**
 	* @public marks an text node and spans with an proper span and image laying above the upper left corner
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
				//if(node.nodeValue != null){
					//Don't mark blank strings (e.g. "\n")
					if(node.nodeValue.blank()==true) return;
					//If node is an normal text node use the nodeValue
					var textdata = document.createTextNode(node.nodeValue);
						span.appendChild(textdata);
				//} else if(node.innerHTML !=null) {
					//If node is not an normal text node (e.g. span) use innerhtml
					//var textdata = document.createTextNode(node.innerHTML);
				//		span.innerHTML = node.innerHTML;
				//}
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
 	* @public Gets all descendants and removes markers 
 	* 
 	* @param rootnode object 
 	* 				Element which descendants will be checked for removing
 	*/
	removeMarkers: function(){
			$$('.icon-marker').each(function(node) {
  				node.remove();
			});
			$$('.div-marker').each(function(node) {
  				node.remove();
			});
			$$('.text-marker').each(function(node) {
  				node.replace(node.innerHTML);
			});
			this.transparencymarkerlist = new Array();
			this.iconmarkerlist = new Array();
			return;
			/*var rootnode = $(this.rootnode);
			//Check rootnode
			if(rootnode == null) return;
			//Get childs
			var elements = rootnode.descendants();
			//remove marker
			elements.each(this.removeMarker.bind(this));
			//reset marker index
			this.markerindex = 0;*/
	},
	
	/**
 	* @private Check if the element is an marker and if yes removes it
 	* 
 	* @param element object 
 	* 				element to check
 	*/	
	/*removeMarker: function(element){
		//firstchild.tagName.toLowerCase() == 'span'
		//debugger;
		if(element == null) return;
		//Check if tabindex is set, if yes update it
		if(element.readAttribute('class')!= null && (element.readAttribute('class')== "icon-marker" || element.readAttribute('class')== "div-marker")){
			element.remove();
		}
		//Check for textmarkers and remove only the span not the text 
		if(element.readAttribute('class')!= null && element.readAttribute('class')== "text-marker"){
			element.replace(element.innerHTML);
		}	
	},*/
	
	/**
 	* @public Marks all templates
 	* 
 	*/	
	markNodes: function(){
		this.removeMarkers();
		//$(this.rootnode).hide();
		var time = new Date();
		var timestamp1 = time;//.toGMTString();		
		this.mark($(this.rootnode), true);
		this.insertMarkers();
		time = new Date();
		var timestamp2 = time;//.toGMTString();
		//alert(timestamp2 -timestamp1 );
		//$(this.rootnode).show();
	},
	
	/**
 	* @public Checks all child nodes for templates and marks the proper Elements
 	* 
 	* @param 
 	*/	
	mark: function(rootnode, mark){

		
		var templates = Array();
		templates.push(0);
		
		//Stores the templatename and the id of the current open but not closed template
		var currentTmpl = null;
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
				if(mark == true) this.textMarker(node,wgServer + wgScript+ "/" +currentTmpl);
			//If nodetype is elementnode
			} else if(node.nodeType == 1 ){
				//Treating different types of elements
				var tag = node.tagName.toLowerCase()	
					//Treat template anchors
					if(tag == 'a'){
						//Find opening and closing tags
							//Check if this is an opening anchor, indicating that a template starts 
							if($(node).readAttribute('type')=='template'){
			  					currentTmpl = node.readAttribute('tmplname');
			  					templates.push(currentTmpl); 			
			  					continue;
			  				}
			  				//Check if this is an closing anchor, indicating that a template ends
			  				if($(node).readAttribute('type')=='templateend'){
			  					currentTmpl = null;
			  					templates[0] = -1;
			  				 	continue;
			  				}
		  			}
					var result;
					if(currentTmpl != null ){
  						result = this.mark(node,false);
  						var links = currentTmpl;
  						if(result[0] != 0 && result[0] != -1 ) links = Array(currentTmpl).push(result[0]);
  						if(result.length > 1){
  							result.shift()
  							links = Array(links).concat(result);
  						}						
  						if(mark == true && $(node).visible()){
  							this.transparencyMarker(node);
  							this.iconMarker(node,links);
  						}
  					} else {
  						(mark == true) ? result = this.mark(node,true) : result = this.mark(node,false);
  					}
  					
  					switch(result[0]){
  						//template close
  						case -1: 
  							currentTmpl = null;
  							break
  						//nothing found
  						case 0:
  							break
  						//Opened template	
  						default:
  							templates.push( result[0] );
  							currentTmpl = result[0];
  						}
					if(result.length > 1){
						result.shift()
						templates = templates.concat( result );
					};
				

			}
		}
		//return current opened Template
		if(currentTmpl != null ){
			templates[0] = currentTmpl; 
		}
		return templates;	
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


//var smwhg_marker = new Marker('innercontent');
var smwhg_marker = new Marker('bodyContent');
Event.observe(window, 'resize', smwhg_marker.markNodes.bind(smwhg_marker));
Event.observe(window, 'load', smwhg_marker.markNodes.bind(smwhg_marker));

/*
setTimeout(function() { 
	setTimeout(smwhg_marker.samplePage.bind(smwhg_marker),1000);
},3000);
//*/