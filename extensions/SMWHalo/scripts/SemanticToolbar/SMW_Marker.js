//First implementation of marking Templates in Mediawiki
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
	markTemplate: function(divtomark) {
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
	markTextTemplate: function(divtomark) {
		if(divtomark == null) return;
		//Create and insert markerdiv
		var marker = '<div id="' + this.markerindex + '-marker" class="span-marker"><img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/templatemarker.png"/></div>';
		new Insertion.After(divtomark, marker );
		//Set position of the marker		
		$(this.markerindex+"-marker").style.top = divtomark.offsetTop + "px";
		$(this.markerindex+"-marker").style.left = divtomark.offsetLeft + "px";
		//increase marker index				
		this.markerindex++;			
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
		var borderwidth = $(el).getStyle("border-"+borderposition+"-width");
		var borderregex = /(\d*)(px)/;
		var regexresult;
		if(regexresult = borderregex.exec(borderwidth)) {
			return regexresult[1];
		} else {
			return "";
		}

	}, 
	
	/**
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
				this.markTemplate(firstchild);
				this.markTextTemplate(firstchild);		
			} 
			//Mark text with image
			else {
				this.markTextTemplate(element);
			}
		}	
	},
	
	/**
 	* @public Gets all descendants and removes markers 
 	* 
 	* @param rootnode object 
 	* 				Element which descendants will be checked for removing
 	*/
	removeMarkers: function(rootnode){
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
 	* @public Check if the element is an marker and if yes removes it
 	* 
 	* @param element object 
 	* 				element to check
 	*/	
	removeMarker: function(element){
		//Check if tabindex is set, if yes update it
		if(element.readAttribute('class')!= null && (element.readAttribute('class')== "span-marker" || element.readAttribute('class')== "div-marker")){
			element.remove();
		}	
	},
	
	
	//Samplepage for develepoment
	//TODO: Remove if not needed anymore
	samplePage: function(){
		$("innercontent").replace('<div id="innercontent">' +
				'normal text<br>' +
				'normal text<br>' +
				'<span id="sp1" type="template"><table id="foo"><tr><td>yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy byyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyydfgdg</td></tr></table></span>' +
				'<span id="sp1" type="template"><div id="testdiv1">yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy byyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyydfgdg</div></span>' +
				'<span id="sp2" style="background-color: #00AA00;" type="template">yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy byyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyydfgdg</span>' +
				'normal text<br>' +
				'normal text<br>' +
				'</div>');
		this.checkForTemplates();
	}
}

var smwhg_marker = new Marker('innercontent');
Event.observe(window, 'resize', smwhg_marker.checkForTemplates.bind(smwhg_marker));
Event.observe(window, 'load', smwhg_marker.checkForTemplates.bind(smwhg_marker));

/*
setTimeout(function() { 
	setTimeout(smwhg_marker.samplePage.bind(smwhg_marker),1000);
},3000);
*/