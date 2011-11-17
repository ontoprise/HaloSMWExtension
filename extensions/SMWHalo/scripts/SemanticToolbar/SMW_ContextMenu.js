/**
 * @file
 * @ingroup SMWHaloSemanticToolbar
 * @author: Thomas Schweitzer
 */
//Lightweight Framework for displaying context menu in aam
window.ContextMenuFramework = Class.create();
ContextMenuFramework.prototype = {
	// Stores the last position of the context menu. If it was dragged, it should
	// be opened at the same position the next time is is opened.
	mLastPosition : null,
	mWasDragged   : false,
	
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
//      var menu = '<div id="contextmenu" style="z-index: '+ zindex +'"><div id="topToolbar"><div>' + gLanguage.getMessage('ADD_ANNOTATION') + '<img src="'
//      + mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/expanded-close.gif"/><div></div></div>';

      var menu = '<div id="contextmenu" style="z-index: '+ zindex +'"><div id="topToolbar">'
        + gLanguage.getMessage('ADD_ANNOTATION')
        + '<img src="'
        + mw.config.get('wgServer')
        + mw.config.get('wgScriptPath')
        + '/extensions/SMWHalo/skins/expanded-close.gif"/></div>'
        + '<div id="contextmenuContent"></div></div>';



      var self = this;
      jQuery('#topToolbar img').live('click', function(){
        self.remove();
      });
	  jQuery(document).keypress(function(event){
	  	if (event.keyCode === Event.KEY_ESC) {
	        self.remove();
		}
      });
      if ($('smwh_AAM'))
        //			    new Insertion.After($('smwh_AAM'), menu );
        new Insertion.After($('ontomenuanchor'), menu );
      else // in edit mode smwh_AAM doesn't exist.
        new Insertion.After($('ontomenuanchor'), menu );
    }
	
	if (ContextMenuFramework.prototype.mWasDragged) {
		// Restore the position of the menu
		this.setPosition(ContextMenuFramework.prototype.mLastPosition.left, 
		                 ContextMenuFramework.prototype.mLastPosition.top);
	}
  },
  
  wasDragged: function() {
  	return ContextMenuFramework.prototype.mWasDragged;
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
	var minusImg = wgScriptPath + '/extensions/SMWHalo/skins/Annotation/images/minus.gif';
	var plusImg  = wgScriptPath + '/extensions/SMWHalo/skins/Annotation/images/plus.gif';
	var imgStyle = 'style="padding-right:5px; display:none" border="0"';
    switch(containertype){
      case CATEGORYCONTAINER:
        if($('cmCategoryHeader')) {
          $('cmCategoryHeader').remove();
        }
        if($('cmCategoryContent')) {
          $('cmCategoryContent').remove();
        }
        header = '<div id="cmCategoryHeader">' +
					'<img src="' + minusImg + '" id="cmCategoryHeaderClose" ' + imgStyle + '>' +
					'<img src="' + plusImg + '" id="cmCategoryHeaderOpen" ' + imgStyle + '>' +
					headline +
				 '</div>';
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
        header = '<div id="cmPropertyHeader">' +
					'<img src="' + minusImg + '" id="cmPropertyHeaderClose" ' + imgStyle + '>' +
					'<img src="' + plusImg + '" id="cmPropertyHeaderOpen" ' + imgStyle + '>' +
					headline +
				 '</div>';
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
    new Insertion.Bottom('contextmenuContent', header );
    new Insertion.Bottom('contextmenuContent', content );
    new Insertion.Bottom(contentdiv, htmlcontent );
	if ($('cmCategoryHeader') && $('cmPropertyHeader')) {
		// Both property and category toolbox are present
		// => add buttons for toggling them
		this.tooglePropertyAndCategoryToolboxes();
		this.updateOpenCloseButtons(true);
		Event.observe('cmCategoryHeader', 'click', 
					  this.tooglePropertyAndCategoryToolboxes.bindAsEventListener(this));
		Event.observe('cmPropertyHeader', 'click',
					  this.tooglePropertyAndCategoryToolboxes.bindAsEventListener(this));
	}

	
  },
  
  /**
   * Updates the visibility of the plus/minus buttons in the category and 
   * property toolbox headers.
   * 
   * @param {bool} propertiesAreVisible
   * 		true if the property toolbox is currently visible.
   */
  updateOpenCloseButtons: function (propertiesAreVisible) {
  	if (propertiesAreVisible) {
		$('cmCategoryHeaderOpen').show();
		$('cmCategoryHeaderClose').hide();
		$('cmPropertyHeaderOpen').hide();
		$('cmPropertyHeaderClose').show();
	} else {
		// Categories are visible
		$('cmCategoryHeaderOpen').hide();
		$('cmCategoryHeaderClose').show();
		$('cmPropertyHeaderOpen').show();
		$('cmPropertyHeaderClose').hide();
	}
  	
  },
  
  tooglePropertyAndCategoryToolboxes: function () {
  	if ($('cmCategoryContent').visible()) {
		// The category toolbox is currently visible 
		// => switch to the property toolbox
		$('cmCategoryContent').hide();
		$('cmPropertyContent').show();
		this.updateOpenCloseButtons(true);
	} else {
		$('cmCategoryContent').show();
		$('cmPropertyContent').hide();
		this.updateOpenCloseButtons(false);
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
	element.setStyle({
		left: posX + 'px',
		top:  posY + 'px',
	});
  },
  /**
 * @public  shows menu
 * 
 */
  showMenu: function(){
    $('contextmenu').show();
	var inp = $('contextmenu').select('input');
	if (inp.length > 0) {
		setTimeout(function () {inp[0].focus();}, 500);
	}
	
    var numberOfSubContainers = $('contextmenuContent').immediateDescendants().length;
    if ($('cmCategoryContent') && numberOfSubContainers > 3) {
      // The category section is initially folded in
      $('cmCategoryContent').hide();
    }

    mw.loader.using('jquery.ui.draggable', function(){
      jQuery('#contextmenu').draggable({
	  	stop: function(event,ui) {
			// Store the last position for the next time
			ContextMenuFramework.prototype.mLastPosition = ui.position;
			ContextMenuFramework.prototype.mWasDragged = true; 
		}
	  });
    });
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