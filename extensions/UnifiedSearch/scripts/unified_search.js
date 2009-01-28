// deactivate combined search if necessary
var csLoadObserver;
if (csLoadObserver != null) Event.stopObserving(window, 'load', csLoadObserver);

var ToleranceSlider = Class.create();
ToleranceSlider.prototype = {
	
	 initialize: function() {
        this.sliderObj = null;
        this.savedPos = -1; // save position within a page. hack for IE
        this.sliderWidth = OB_bd.isIE ? 13 : 12;
        this.timer = null;
        this.hiddenElement = document.createElement("input");
        this.hiddenElement.setAttribute("name", "tolerance");
        this.hiddenElement.setAttribute("type", "hidden");
        this.hiddenElement.setAttribute("value", "0");
    },
    
    activate: function() {
    	$('searchform').appendChild(this.hiddenElement);
    	$('toleranceslider').innerHTML = '<img id="tolerancesliderHandle" src="' +
            wgScriptPath +
            '/extensions/UnifiedSearch/skin/images/slider.gif"/>';
    	 this.sliderObj = new Control.Slider('tolerancesliderHandle','toleranceslider',{
          //axis:'vertical',
          sliderValue:0,
          minimum:0,
          maximum:2,
          step:1,
          increment:1,
          range: $R(0.0,2),
          onSlide: this.slide.bind(this),
          onChange: this.slide.bind(this)
       });
    },
    
    slide: function(v) {
        
      if (v > 0 && v < 1) {
       	this.sliderObj.setValue(0);
      	this.hiddenElement.setAttribute("value", "0");
      }
      if (v > 1 && v < 2) { 
       	this.sliderObj.setValue(1);
      	this.hiddenElement.setAttribute("value", "1");
      }
      if (v == 2) {
        this.sliderObj.setValue(2);
        this.hiddenElement.setAttribute("value", "2");
      } 
       
    }
    
}

var smwhg_toleranceslider = new ToleranceSlider();
Event.observe(window, 'load', smwhg_toleranceslider.activate.bind(smwhg_toleranceslider));

