/**
 * @file
 * @ingroup SMWHaloSemanticToolbar
 * @author Thomas Schweitzer
 */

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
		this.markerindex = 0;
		//Stores the information for marking elements after traversing the DOM-Tree
		//Elements are array [0] ID of Marker [1] html of Marker [2] Item to Mark [3] Position Top [4] Position Left [5] Height [6] Width   
		this.transparencymarkerlist = new Array();
		//Stores the information for marking elements after traversing the DOM-Tree
		//Elements are array [0] ID of Marker [1] html of Marker [2] Item to Mark [3] Position Top [4] Position Left
		this.iconmarkerlist = new Array();		
	},
	
	/**
 	* @public marks an element with a transparent layer  
 	* 
 	* @param divtomark object
 	* 			element to mark
 	*/
 	insertMarkers: function(){
 		//return if AAM-Div does not exist
                if( !$(this.rootnode) ) return;
 		$(this.rootnode).hide();
 		// transparencyMarkers
 		for(var index=0; index < this.transparencymarkerlist.length; index++){
			if($(this.iconmarkerlist[index][2])){
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
 	
 	/**
 	* @public marks an element with an overlay
 	* 
 	* @param divtomark object
 	* 			element to mark
 	*/
	transparencyMarker: function(divtomark) {
		if(divtomark == null) return;
		//Create and insert markerdiv
		var marker = '<div id="' + this.markerindex + '-marker" class="div-marker"></div>';
		//Set width&height for the marker so it fits the original element which should be marked
		var width = divtomark.offsetWidth;
		var height = divtomark.offsetHeight;
		//Set position of the marker
		var top = divtomark.offsetTop;
		var left = divtomark.offsetLeft;
		//increase marker index
		this.transparencymarkerlist.push( new Array(this.markerindex+"-marker", marker, $(divtomark).identify(), top, left, height, width ))
		this.markerindex++;	
	},
	
	/**
 	* @public marks an element with an image laying above the upper left corner
 	* 
 	* @param divtomark object
 	* 			element to mark
 	* 		 links
 	* 			links to the templates
 	*/
	iconMarker: function(divtomark,links) {
		if(divtomark == null) return;
		//Create and insert markerdiv
		var marker = Array();
		marker.push('<div id="' + this.markerindex + '-marker" class="icon-marker">');
			//Check if multiple links has been passed and generate one clickable picture for each
			if( links  instanceof Array){					
				for(var index=0; index < links.length; index++){ 
					marker.push('<a href="'+ wgServer + wgScript+ "/" +links[index] +'"><img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/templatemarker.png"/></a>');
				};
			// Check if only one link has been passe	
			} else if ( links  instanceof String || typeof(links) == "string"){
				marker.push('<a href="'+ wgServer + wgScript+ "/" + links +'"><img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/templatemarker.png"/></a>'); 
			//If nothing has been passed, only mark it with a non clickable picture
				} else {
					marker.push('<img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/templatemarker.png"/>');
				}
		marker.push( '</div>');
		//Set position of the marker		
		var top = divtomark.offsetTop;
		var left = divtomark.offsetLeft;
		this.iconmarkerlist.push( new Array(this.markerindex+"-marker", marker.join(''), $(divtomark).identify(), top, left))
		//increase marker index				
		this.markerindex++;
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
			return 0;
		}
	}, 
	
	
	/**
 	* @public Gets all descendants and removes markers 
 	* 
 	* @param rootnode object 
 	* 				Element which descendants will be checked for removing
 	*/
	removeMarkers: function(){
                        //return if AAM-Div does not exist
                        if( !$(this.rootnode) ) return;
			$(this.rootnode).hide();
			var markers = $$('.icon-marker');
			for(var index=0; index < markers.length; index++){
				markers[index].remove();
			}
			var markers = $$('.div-marker');
			for(var index=0; index < markers.length; index++){
				markers[index].remove();
			}
			var markers = $$('.text-marker');
			for(var index=0; index < markers.length; index++){
				markers[index].remove();
			}
			this.transparencymarkerlist = new Array();
			this.iconmarkerlist = new Array();
			$(this.rootnode).show();
	},
	
	/**
 	* @public Marks all templates
 	* 
 	*/	
	markNodes: function(){
		this.removeMarkers();		
		//var time = new Date();
		//var timestamp1 = time;		
		this.mark($(this.rootnode), true);
		this.insertMarkers();
		//time = new Date();
		//var timestamp2 = time;
		//alert(timestamp2 -timestamp1 );
	},
	
	/**
 	* @public Checks all child nodes for templates and marks the proper Elements
 	* 
 	* @param 
 	*/	
	mark: function(rootnode, mark){
                //return if AAM-Div does not exist
                if( !$(this.rootnode) ) return;
		//Stores template links found by checking the subtree of childelements, so elements can be marked with later
		//return:  -1 the currently opened template was close in the subtree
		// 			0 nothing has been found in the subtree
		// 		 else template found returned 
		var templates = Array();		
		templates.push(0);		
		//Stores the templatename and the id of the current open but not closed template
		var currentTmpl = null;
		//Get Childelements
		var childelements = rootnode.childNodes;
		//Walk over every next sibling
		//this uses plain javascript functions, since prototype doesn't support textnodes
		for(var index=0; index < childelements.length; index++){
			//Get current node 
			var node = childelements[index];			
			//If nodetyp is textnode and template tag is open but not closed
			if( node.nodeType == 3 && currentTmpl != null ){
				//mark text
				if( mark == true) this.textMarker(node,wgServer + wgScript+ "/" +currentTmpl);
			//If nodetype is elementnode
			} else if(node.nodeType == 1 ){
				//Treating different types of elements
				var tag = node.tagName.toLowerCase()	
					//Treat template anchors
					if(tag == 'a'){
						//Find opening and closing tags
							//Check if this is an opening anchor, indicating that a template starts 
							var attrtype = $(node).readAttribute('type');
							if( attrtype =='template'){
			  					currentTmpl = $(node).readAttribute('tmplname');
			  					templates.push(currentTmpl); 			
			  					continue;
			  				}
			  				//Check if this is an closing anchor, indicating that a template ends
			  				if( attrtype =='templateend'){
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
						result.shift();
						templates = templates.concat( result );
					};
				

			}
		}
		//return current opened Template
		if(currentTmpl != null ){
			templates[0] = currentTmpl; 
		}
		return templates;	
	}

}


//var smwhg_marker = new Marker('innercontent');
var smwhg_marker = new Marker('smwh_AAM');
Event.observe(window, 'load', smwhg_marker.markNodes.bind(smwhg_marker));
Event.observe(window, 'resize', smwhg_marker.markNodes.bind(smwhg_marker));