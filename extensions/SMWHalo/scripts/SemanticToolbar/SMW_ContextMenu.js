/**
 * @file
 * @ingroup SMWHaloSemanticToolbar
 * @author: Thomas Schweitzer
 */
//Lightweight Framework for displaying context menu in aam
var ContextMenuFramework = Class.create();
ContextMenuFramework.prototype = {
/**
 * Constructor
 */
initialize: function() {
		if(!$("contextmenu")){
                        // Context menu is supposed to overlap Semantic toolbar
                        var zindex = ($('ontomenuanchor').getStyle('z-index'))
                                    ? parseInt($('ontomenuanchor').getStyle('z-index')) + 1
                                    : 30;
                        // because of Ontoskin3 set zIndex at least to 30
                        if (zindex < 30) zindex = 30;
			var menu = '<div id="contextmenu" style="z-index: '+ zindex +'"></div>';
//			new Insertion.Top($('innercontent'), menu );
			new Insertion.After($('content'), menu );
		}
		
},

/**
 * Removes the context menu from the DOM tree.
 */
remove: function() {
	if ($("contextmenu")) {
		$("contextmenu").remove();
	}
},

/**
 * @public positions the STB at the given coordinates considering how it fits best     
 * 
 * @param 	String htmlcontent
 * 				htmlcontent which will be set
 * 			Integer containertype 
 * 				containertype (uses enum defined in STB_Framwork.js
 * 			String headline 
 * 				text of the shown headline
 */
setContent: function(htmlcontent,containertype, headline){
	var header;
	var content;
	var contentdiv;
	switch(containertype){
		case CATEGORYCONTAINER:
			if($('cmCategoryHeader')) {
				$('cmCategoryHeader').remove();
			}
			if($('cmCategoryContent')) {
				$('cmCategoryContent').remove();
			}
			header =  '<div id="cmCategoryHeader">'+headline+'</div>';
			content = '<div id="cmCategoryContent"></div>';
			contentdiv = 'cmCategoryContent';
			break;
		case RELATIONCONTAINER:
			if($('cmPropertyHeader')) {
				$('cmPropertyHeader').remove();
			}
			if($('cmPropertyContent')) {
				$('cmPropertyContent').remove();
			}
			header =  '<div id="cmPropertyHeader">'+headline+'</div>';
			content = '<div id="cmPropertyContent"></div>';
			contentdiv = 'cmPropertyContent'
			break;
		case 'ANNOTATIONHINT':
			if($('cmAnnotationHintHeader')) {
				$('cmAnnotationHintHeader').remove();
			}
			if($('cmAnnotationHintContent')) {
				$('cmAnnotationHintContent').remove();
			}
			header =  '<div id="cmAnnotationHintHeader">'+headline+'</div>';
			content = '<div id="cmAnnotationHintContent"></div>';
			contentdiv = 'cmAnnotationHintContent'
			break;
		default:
			if($('cmDefaultHeader')) {
				$('cmDefaultHeader').remove();
			}
			if($('cmDefaultContent')) {
				$('cmDefaultContent').remove();
			}
			header =  '<div id="cmDefaultHeader">'+headline+'</div>';
			content = '<div id="cmDefaultContent"></div>';
			contentdiv = 'cmDefaultContent'
	}
	new Insertion.Bottom('contextmenu', header );
	new Insertion.Bottom('contextmenu', content );
	new Insertion.Bottom(contentdiv, htmlcontent );
	if ($('cmCategoryHeader') && $('cmPropertyContent')) {
		Event.observe('cmCategoryHeader', 'click',
					  function(event) {
					  	$('cmCategoryContent').show();
					  	$('cmPropertyContent').hide();
					  });
	}
	if ($('cmPropertyHeader') && $('cmCategoryContent')) {
		Event.observe('cmPropertyHeader', 'click',
					  function(event) {
					  	$('cmCategoryContent').hide();
					  	$('cmPropertyContent').show();
					  });
	}

},

/**
 * @public  dummy since changes will be visible on the fly with setContent
 *			this is for compatiblity with the stb_framework
 */
contentChanged: function(){

},

/**
 * @public positions the STB at the given coordinates considering how it fits best     
 * 
 * @param 	posX
 * 				desired X position
 * 			posY 
 * 				desired Y position
 */
setPosition: function(posX,posY){
	element = $('contextmenu');
	//X-Coordinates
	var toolbarWidth = element.scrollWidth;
	//Check if it fits right to the coordinates
	var width = (window.innerWidth) ? window.innerWidth : document.body.clientWidth;
	if( width - posX < toolbarWidth) {
		//Check if it fits left to the coordinates
		if( posX < toolbarWidth){
			// if not place it on the left side of the window
			element.setStyle({right: '' });
			element.setStyle({left: '10px'});
			
		} else {
			//if it fits position it left to the coordinates
			var pos = width - posX;
			element.setStyle({right: pos + 'px' });
			element.setStyle({left: ''});
		}
	} else {
		//if it fits position it right to the coordinates
		var pos = posX;
		element.setStyle({right: ''});
		element.setStyle({left: pos  + 'px'});
	}
	//Y-Coordinates
	var toolbarHeight = element.scrollHeight;
	//Check if it fits bottom to the coordinates
	if( window.innerHeight - posY < toolbarHeight) {
		//Check if it fits top to the coordinates
		if(posY < toolbarHeight){
			// if not place it on the top side of the window	
			element.setStyle({bottom: '' });
			element.setStyle({top: '10px'});
			
		} else {
		var pos = window.innerHeight - posY;
			//if it fits position it top to the coordinates
			element.setStyle({bottom: pos + 'px' });
			element.setStyle({top: ''});
		}
	}else {
		//if it fits position it bottom to the coordinates
		var pos = posY;
		element.setStyle({bottom: ''});
		element.setStyle({top: pos  + 'px'});
	}
},
/**
 * @public  shows menu
 * 
 */
showMenu: function(){
	$('contextmenu').show();
        var numberOfSubContainers = $('contextmenu').immediateDescendants().length;
	if ($('cmCategoryContent') && numberOfSubContainers > 2) {
		// The category section is initially folded in
		$('cmCategoryContent').hide();
	}
},
/**
 * @public  hides menu
 */
hideMenu: function(){
	$('contextmenu').hide();
} 

};

/*
setTimeout(function() { 
	//categorycontainer = new divContainer(CATEGORYCONTAINER);
	var contextMenu = new ContextMenuFramework();
	var conToolbar = new ContainerToolBar('menu',500,contextMenu);
	//Event.observe(window, 'load', conToolbar.createContainerBody.bindAsEventListener(conToolbar));
	conToolbar.foo();
	//contextMenu.setPosition(100,100);
},3000);
//*/