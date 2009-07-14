/* Resizing Content window slider using scriptacolus slider */
var MainSlider = Class.create();
MainSlider.prototype = {

    initialize: function() {
        this.sliderObj = null;
        this.oldHeight = 0;
        this.oldWidth  = 0;
        this.savedPos = -1;
    },
    //if()
    activateResizing: function() {

        //Check if semtoolbar is available and action is not annotate
        if(!$('mainslider') || wgAction == 'annotate') return;

        //Load image to the slider div
        $('mainslider').innerHTML = '<img id="mainsliderHandle" src="' +
        wgScriptPath +
        '/skins/ontoskin2/slider.gif"/>';

        var saved_iv = GeneralBrowserTools.getCookie("cp-slider");
        if( saved_iv != null && !isNaN(saved_iv)){
            var initialvalue = saved_iv;
        } else {
            var initialvalue = 190 / $('mainslider').getWidth();
        }

        //create slider after old one is removed
        if(this.sliderObj != null){
            this.sliderObj.setDisabled();
            this.sliderObj= null;
        }


        var min = 160 / $('mainslider').getWidth();
        var max = 1- (400 / $('mainslider').getWidth());

        //alert(min);
        //alert(max);

        this.sliderObj = new Control.Slider('mainsliderHandle','mainslider',{
            //axis:'vertical',
            sliderValue:initialvalue,
            //minimum:min,
            //maximum:max,
            //range: $R(min,max),
            onSlide: this.slide.bind(this),
            onChange: this.slide.bind(this)
        });
        this.slide(initialvalue);
    },

    //Check for min max and sets the content and the semtoolbar to the correct width
    slide: function(v)
    {

        // change width of divs of class 'dtreestatic' below main_navtree
        // and of main_navtree itself.
        var menuwidth = $('smwf_naviblock').getWidth();
        $$('#smwf_browserview div.dtreestatic').each(function(statictree) {
            statictree.style.width = menuwidth - 30 +"px";
        });

        var totalwidth = $('mainslider').getWidth();

        //If totalwidth is below overall minimum size of menu + page
        //then cancel resize and do nothing
        if(totalwidth <= 460) return;

        var min = 160 / totalwidth;
        var max = 1- (400 / totalwidth);

        //initial position
        if (isNaN(v) ) {
            v = 190 / $('mainslider').getWidth();
        }

        if( v < min){
            if (this.sliderObj != null) this.sliderObj.setValue(min);
            $('mainsliderHandle').style.left = 160;
            return;
        } else if( v > max){
            if (this.sliderObj != null) this.sliderObj.setValue(max);
            $('mainsliderHandle').style.left = totalwidth - 400;
            return;
        }

        //calculate left div size and rightdiv size
        var currLeftDiv = 100*v; //width of left menu
        var currRightDiv = currLeftDiv + 1; //margi-left of mainpage


        if(currLeftDiv != Infinity && currRightDiv != Infinity && !isNaN(currRightDiv) && !isNaN(currLeftDiv) ){
            $('smwf_naviblock').style.width = currLeftDiv + "%";
            $('smwf_pageblock').style.marginLeft = currRightDiv + "%";
        }

        document.cookie = "cp-slider="+v+"; path="+wgScript;
        this.savedPos = v;
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

var smwhg_mainslider = new MainSlider();
Event.observe(window, 'load', smwhg_mainslider.activateResizing.bind(smwhg_mainslider));
//Resizes the slider if window size is changed
Event.observe(window, 'resize', smwhg_mainslider.resizeTextbox.bind(smwhg_mainslider));