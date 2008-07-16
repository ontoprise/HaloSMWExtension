/* Resizing Content window slider using scriptacolus slider */
var ContentSlider = Class.create();
ContentSlider.prototype = {

    initialize: function() {
        this.sliderObj = null;
        this.oldHeight = 0;
        this.oldWidth  = 0;
        this.sliderWidth = OB_bd.isIE ? 13 : 12;
       
    },
    //if()
    activateResizing: function() {
    //Check if semtoolbar is available and action is not annotate
   
    if(!$('contentslider')) return;
    
    //Load image to the slider div
    $('contentslider').innerHTML = '<img id="contentSliderHandle" src="' +
            wgScriptPath +
            '/extensions/SMWHalo/skins/slider.gif"/>';
        var windowWidth = OB_bd.isIE ? document.body.offsetWidth : window.innerWidth
        // 25px for the silder
        var iv = ($("p-logo").clientWidth -  this.sliderWidth) / windowWidth;
        var saved_iv = GeneralBrowserTools.getCookie("cp-slider");    
        var initialvalue = saved_iv != null ? saved_iv : iv;
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
          maximum:1,
          //range: $R(0.5,0.75),
          onSlide: this.slide.bind(this),
          onChange: this.slide.bind(this)
       });
      
    },

    //Checks for min max and sets the content and the semtoolbar to the correct width
    slide: function(v)
          {
            var windowWidth = OB_bd.isIE ? document.body.offsetWidth : window.innerWidth
            var iv = ($("p-logo").clientWidth - this.sliderWidth) / windowWidth;    
            var currMarginDiv = windowWidth*(v-iv)+$("p-logo").clientWidth;
            
            var leftmax = iv; // range 0 - 1
            var rightmax = 1; // range 0 - 1

             if( v < leftmax){
                this.sliderObj.setValue(leftmax);
                return;
             }

             if( v > rightmax){
                this.sliderObj.setValue(rightmax);
                return;
             }
            var sliderSmooth = OB_bd.isIE ? v*25 : v*38;
            // move toolbar and content pane
            $('p-cactions').style.marginLeft = (windowWidth*(v-iv)) - sliderSmooth +"px";
            $('content').style.marginLeft = currMarginDiv - sliderSmooth + "px";
           
           // change width of Treeviews of class 'dtreestatic'
           var sliderWidth = this.sliderWidth;
           $$('div.dtreestatic').each(function(s) { 
                s.style.width = windowWidth*v+sliderWidth-7- sliderSmooth +"px";
           });
           $$('div.Treeview5').each(function(s) { 
                s.style.width = windowWidth*v+sliderWidth-5- sliderSmooth +"px";
           });
           
           // change sidebars
           $('p-navigation').style.width = windowWidth*v+sliderWidth-5- sliderSmooth +"px";
           $('p-search').style.width = windowWidth*v+sliderWidth-5- sliderSmooth +"px";
           $('p-tb').style.width = windowWidth*v+sliderWidth-5- sliderSmooth +"px";
           
           document.cookie = "cp-slider="+v+"; path="+wgScript;
    },
     /**
      * Resizes the slide if window size is changed
      * since IE fires the resize event in much more cases than the desired
      * we have to do some additional checks
      */
     resizeTextbox: function(){
        if( OB_bd.isIE == true){
            if( typeof document.documentElement != 'undefined' && document.documentElement.clientHeight != this.oldHeight && document.documentElement.clientHeight != this.oldWidth ){
                this.activateResizing();
                this.oldHeight = document.documentElement.clientHeight;
                this.oldWidth  = document.documentElement.clientWidth;
            } else{
                if( typeof window.innerHeight != 'undefined' && window.innerHeight != this.oldHeight && window.innerWidth != this.oldWidth){
                    alert('resize');
                    this.activateResizing();
                    this.oldHeight = window.innerHeight;
                    this.oldWidth  = window.innerWidth;
                }
            }
       }else {
            this.activateResizing();
        }
     }
}

var smwhg_contentslider = new ContentSlider();
Event.observe(window, 'load', smwhg_contentslider.activateResizing.bind(smwhg_contentslider));
//Resizes the slider if window size is changed
//Event.observe(window, 'resize', smwhg_contentslider.resizeTextbox.bind(smwhg_contentslider));
