//First implementation of marking Templates in Mediawiki
var Marker = Class.create();
Marker.prototype = {
	
	initialize: function() {		
	},
	markTemplate: function(divtomark) {
		if(divtomark == null || divtomark == "") return;
		//$(divtomark)
		var marker = '<div id="' + divtomark + '-marker" class="div-marker"></div>';
		new Insertion.After($(divtomark), marker );
		
		var borderwidthx = Number(this.getBorderWidth(divtomark+"-marker","left")) + Number(this.getBorderWidth(divtomark+"-marker","right"));
		if(isNaN(Number(borderwidthx))) return;
		var borderwidthy = Number(this.getBorderWidth(divtomark+"-marker","top")) + Number(this.getBorderWidth(divtomark+"-marker","bottom"));
		if(isNaN(Number(borderwidthy))) return;
		var width = $(divtomark).offsetWidth - borderwidthx;
		$(divtomark+"-marker").style.width =  + width + "px";
		var height = $(divtomark).offsetHeight - borderwidthy;
		$(divtomark+"-marker").style.height = height + "px";
		$(divtomark+"-marker").style.top = $(divtomark).offsetTop + "px";
		$(divtomark+"-marker").style.left = $(divtomark).offsetLeft + "px";				
	},
	
	markTextTemplate: function(divtomark) {
		if(divtomark == null || divtomark == "") return;
		//$(divtomark)
		var marker = '<div id="' + divtomark + '-marker" class="span-marker"><img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/templatemarker.png"/></div>';
		new Insertion.After($(divtomark), marker );		
		$(divtomark+"-marker").style.top = $(divtomark).offsetTop + "px";
		$(divtomark+"-marker").style.left = $(divtomark).offsetLeft + "px";				
	},
	
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
 	* @public 
 	* 
 	* @param
 	*/
	checkForTemplates: function(rootnode){
			//Check
			if(rootnode == null) return;
			//Get childs
			var elements = rootnode.descendants();
			//update each child
			elements.each(this.checkElement.bind(this));
			
	},
	
	/**
	 * @private 
	 * 
	 * @param  
	 */
	checkElement: function(element){
		//Check if tabindex is set, if yes update it
		if(element.readAttribute('type')!= null && element.readAttribute('type')== "template"){
			firstchild = element.firstDescendant();
			if(firstchild != null && firstchild.tagName.toLowerCase() == 'div'){
				this.markTemplate(firstchild.readAttribute('id'));
				this.markTextTemplate(firstchild.readAttribute('id'));	
			} else {
				this.markTextTemplate(element.readAttribute('id'));
			}
		}	
	},
	
	samplePage: function(){
		$("innercontent").replace('<div id="innercontent">' +
				'normal text<br>' +
				'normal text<br>' +
				'<span id="sp1" type="template"><div id="testdiv1">yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy byyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyydfgdg</div></span>' +
				'<span id="sp2" style="background-color: #00AA00;" type="template">yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy byyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyydfgdg</span>' +
				'normal text<br>' +
				'normal text<br>' +
				'</div>');
		this.checkForTemplates($("innercontent"));
	}
}

/*
setTimeout(function() { 
	//categorycontainer = new divContainer(CATEGORYCONTAINER);
	var marker = new Marker();
	//Event.observe(window, 'load', marker.createContainerBody.bindAsEventListener(conToolbar));
	setTimeout(marker.samplePage.bind(marker),1000);
},3000);
*/
