/**
 *  @file
 *  @ingroup SMWHaloMiscellaneous
 *  @author Kai Kühn
 *  
 *  Resizing Content window slider using scriptacolus slider 
 *	
 *  
 */
var ContentSlider = Class.create();
ContentSlider.prototype = {

    initialize: function() {
        this.sliderObj = null;
        this.savedPos = -1; // save position within a page. hack for IE
        this.sliderWidth = OB_bd.isIE ? 13 : 12;
        this.timer = null;
    },
    //if()
    activateResizing: function() {
    //Check if semtoolbar is available and action is not annotate
   
    if(!$('contentslider') || wgAction == "annotate") return;
    
    //Load image to the slider div
    $('contentslider').innerHTML = '<img id="contentSliderHandle" src="' +
            wgScriptPath +
            '/extensions/SMWHalo/skins/slider.gif"/>';
        var windowWidth = OB_bd.isIE ? document.body.offsetWidth : window.innerWidth
        // 25px for the silder
        var iv = ($("p-logo").getWidth() -  this.sliderWidth) / windowWidth;
        var saved_iv = GeneralBrowserTools.getCookie("cp-slider");    
        var initialvalue = saved_iv != null ? saved_iv : this.savedPos != -1 ? this.savedPos : iv;
        
        this.slide(initialvalue);
       //create slider after old one is removed
       if(this.sliderObj != null){
            this.sliderObj.setDisabled();
            this.sliderObj= null;
       }
       this.sliderObj = new Control.Slider('contentSliderHandle','contentslider',{
          //axis:'vertical',
          sliderValue:initialvalue,
          minimum:iv,
          maximum:0.5,
          //range: $R(0.5,0.75),
          onSlide: this.slide.bind(this),
          onChange: this.slide.bind(this)
       });
      
    },

    //Checks for min max and sets the content and the semtoolbar to the correct width
    slide: function(v)
          {
          	
            var windowWidth = OB_bd.isIE ? document.body.offsetWidth : window.innerWidth
            var iv = ($("p-logo").getWidth() - this.sliderWidth) / windowWidth;    
            var currMarginDiv = windowWidth*(v-iv)+$("p-logo").getWidth();
            
            var leftmax = iv; // range 0 - 1
            var rightmax = 0.5; // range 0 - 1

             if( v < leftmax){
                if (this.sliderObj != null) this.sliderObj.setValue(leftmax);
                return;
             }

             if( v > rightmax){
                if (this.sliderObj != null) this.sliderObj.setValue(rightmax);
                return;
             }
            var sliderSmooth = OB_bd.isIE ? v*25 : v*38;
            // move toolbar and content pane
            $('p-cactions').style.marginLeft = (windowWidth*(v-iv)) - sliderSmooth +"px";
            $('content').style.marginLeft = currMarginDiv - sliderSmooth + "px";
           
           // change width of divs of class 'dtreestatic' below main_navtree
           // and of main_navtree itself.
           var sliderWidth = this.sliderWidth;
           $$('#main_navtree div.dtreestatic').each(function(s) { 
                s.style.width = windowWidth*v+sliderWidth-7- sliderSmooth +"px";
           });
           var main_navTree = $('main_navtree');
           if (main_navTree != null) main_navTree.style.width = windowWidth*v+sliderWidth-5- sliderSmooth +"px";
           
           
           // change sidebars
           $('p-navigation').style.width = windowWidth*v+sliderWidth-5- sliderSmooth +"px";
           $('p-search').style.width = windowWidth*v+sliderWidth-5- sliderSmooth +"px";
           $('p-tb').style.width = windowWidth*v+sliderWidth-5- sliderSmooth +"px";
           if ($('p-treeview') != null) $('p-treeview').style.width = windowWidth*v+sliderWidth-5- sliderSmooth +"px";
           
           document.cookie = "cp-slider="+v+"; path="+wgScript;
           this.savedPos = v;
    },
     /**
      * Resizes the slide if window size is changed
      * since IE fires the resize event in much more cases than the desired
      * we have to do some additional checks
      */
     resizeTextbox: function(){
        if( !OB_bd.isIE ){
            this.activateResizing();
        } else {
        	
        	if (this.timer != null) window.clearTimeout(this.timer);
        	var slider = this; // copy reference to make it readable in closure
        	this.timer = window.setTimeout(function() {
        		 slider.activateResizing();
        	},1000);
        	 
        }        
     }
}

var smwhg_contentslider = new ContentSlider();
Event.observe(window, 'load', smwhg_contentslider.activateResizing.bind(smwhg_contentslider));
//Resizes the slider if window size is changed
Event.observe(window, 'resize', smwhg_contentslider.resizeTextbox.bind(smwhg_contentslider));
