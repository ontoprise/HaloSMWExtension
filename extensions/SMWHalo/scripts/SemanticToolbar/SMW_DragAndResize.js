//Class which holds functionality to make the toolbar draggable and resizeable
var DragResizeHandler = Class.create();
DragResizeHandler.prototype = {

/**
 * @public constructor to initialize class 
 * 
 * @param 
 **/
initialize: function() {
	//Object to store scriptacolous' draggable object
	this.draggable = null;
	//Object to store the modified scriptacolous' object
	this.resizeable = null;
	this.posX = null;
	this.posY = null;
},

/**
 * @public makes toolbar drag and resizable   
 * 
 * @param 
 * 
 */
callme: function(){
	// Makes the toolbar draggable in all modes resizable only in annotation mode.
	if (wgAction == "annotate")
            this.resizeable = new Resizeable('ontomenuanchor',{top: 10, left:10, bottom: 10, right: 10});
        this.enableDragging();
},
/**
 * @public disables dragging of toolbar  
 * 
 * @param 
 */
disableDragging: function(){
	if(this.draggable != null ){
		this.draggable.destroy()
		this.draggable = null;
	}
},
/**
 * @public enables dragging of toolbar  
 * 
 * @param
 */
enableDragging: function(){
	if(this.draggable == null) {
		this.draggable = new Draggable('ontomenuanchor', {
			//TODO: replace handle with proper tab if present	
			handle: 'tabcontainer', 
			starteffect: function( ) {
				stb_control.setDragging(true);
				smwhg_dragresizetoolbar.storePosition();
				smwhg_dragresizetoolbar.restorePosition();
				}, 
			endeffect: function(){setTimeout(stb_control.setDragging.bind(stb_control,false),200);}});
		
		//Adds an Observer which stores the position of the stb after each drag
		//this is temporary and probably will be removed if lightweight framework is implemented
		var DragObserver = Class.create();
		DragObserver.prototype = {
			  initialize: function() {
    			this.element = null;
    	
 		 },
			onEnd: function(){
				smwhg_dragresizetoolbar.storePosition();
			}
		};
		
		var dragObserver = new DragObserver();
		Draggables.addObserver(dragObserver);
	}
},
/**
 * @public adjust size of the ontomenuanchor to the semtoolbar laying above   
 * 
 * @param
 */
fixAnchorSize: function(){
	if($('semtoolbar')){
		var height = $('semtoolbar').scrollHeight + $('tabcontainer').scrollHeight + $('activetabcontainer').scrollHeight
		height = height+'px';
		var obj = new Object();
		obj.height = height;
		$('ontomenuanchor').setStyle(obj); 	 	
	}
},

/**
 * @public buffers the current position so it can later be restored
 * 
 */
storePosition: function(){
	var pos = this.getPosition();
	this.posX = pos[0];
	this.posY = pos[1];
},

/**
 * @public buffers the current position so it can later be restored
 *
 * @return array[0] xposition
 * 		   array[1]	yposition
 * 
 */
restorePosition: function(){
	if(!isNaN(this.posX) && !isNaN(this.posY)){
		this.fixAnchorSize();
		this.setPosition(this.posX, this.posY);
	}	
},

/**
 * @public  returns the act. position of the toolbar
 *
 * @return array[0] xposition
 * 		   array[1]	yposition
 * 
 */
getPosition: function(){
	return new Array($('ontomenuanchor').offsetLeft,$('ontomenuanchor').offsetTop);	
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
	//X-Coordinates
	var toolbarWidth = $('ontomenuanchor').scrollWidth;
	//Check if it fits right to the coordinates
	if( window.innerWidth - posX < toolbarWidth) {
		//Check if it fits left to the coordinates
		if( posX < toolbarWidth){
			// if not place it on the left side of the window
			$('ontomenuanchor').setStyle({right: '' });
			$('ontomenuanchor').setStyle({left: '10px'});
			
		} else {
			//if it fits position it left to the coordinates
			var pos = window.innerWidth - posX;
			$('ontomenuanchor').setStyle({right: pos + 'px' });
			$('ontomenuanchor').setStyle({left: ''});
		}
	} else {
		//if it fits position it right to the coordinates
		var pos = posX;
		$('ontomenuanchor').setStyle({right: ''});
		$('ontomenuanchor').setStyle({left: pos  + 'px'});
	}
	//Y-Coordinates
	var toolbarHeight = $('ontomenuanchor').scrollHeight;
	//Check if it fits bottom to the coordinates
	if( window.innerHeight - posY < toolbarHeight) {
		//Check if it fits top to the coordinates
		if(posY < toolbarHeight){
			// if not place it on the top side of the window	
			$('ontomenuanchor').setStyle({bottom: '' });
			$('ontomenuanchor').setStyle({top: '10px'});
			
		} else {
		var pos = window.innerHeight - posY;
			//if it fits position it top to the coordinates
			$('ontomenuanchor').setStyle({bottom: pos + 'px' });
			$('ontomenuanchor').setStyle({top: ''});
		}
	}else {
		//if it fits position it bottom to the coordinates
		var pos = posY;
		$('ontomenuanchor').setStyle({bottom: ''});
		$('ontomenuanchor').setStyle({top: pos  + 'px'});
	}
} 

}


// TODO: Check License for Resizeable-Code http://blog.craz8.com/articles/2005/12/01/make-your-divs-resizeable
var Resizeable = Class.create();
Resizeable.prototype = {
  initialize: function(element) {
    var options = Object.extend({
      top: 6,
      bottom: 6,
      left: 6,
      right: 6,
      minHeight: 0,
      minWidth: 0,
      zindex: 1000,
      resize: null
    }, arguments[1] || {});

    this.element      = $(element);
    this.handle 	  = this.element;

	if (this.element) {
    	Element.makePositioned(this.element); // fix IE
    }    

    this.options      = options;

    this.active       = false;
    this.resizing     = false;   
    this.currentDirection = '';

    this.eventMouseDown = this.startResize.bindAsEventListener(this);
    this.eventMouseUp   = this.endResize.bindAsEventListener(this);
    this.eventMouseMove = this.update.bindAsEventListener(this);
    this.eventCursorCheck = this.cursor.bindAsEventListener(this);
    this.eventKeypress  = this.keyPress.bindAsEventListener(this);
    
    this.registerEvents();
  },
  destroy: function() {
    Event.stopObserving(this.handle, "mousedown", this.eventMouseDown);
    this.unregisterEvents();
  },
  registerEvents: function() {
    Event.observe(document, "mouseup", this.eventMouseUp);
    Event.observe(document, "mousemove", this.eventMouseMove);
    Event.observe(document, "keypress", this.eventKeypress);
    Event.observe(this.handle, "mousedown", this.eventMouseDown);
    Event.observe(this.element, "mousemove", this.eventCursorCheck);
  },
  unregisterEvents: function() {
    //if(!this.active) return;
    //Event.stopObserving(document, "mouseup", this.eventMouseUp);
    //Event.stopObserving(document, "mousemove", this.eventMouseMove);
    //Event.stopObserving(document, "mousemove", this.eventCursorCheck);
    //Event.stopObserving(document, "keypress", this.eventKeypress);
  },
  startResize: function(event) {
    if(Event.isLeftClick(event)) {
      
      // abort on form elements, fixes a Firefox issue
      var src = Event.element(event);
      if(src.tagName && (
        src.tagName=='INPUT' ||
        src.tagName=='SELECT' ||
        src.tagName=='BUTTON' ||
        src.tagName=='TEXTAREA')) return;

	  var dir = this.directions(event);
	  if (dir.length > 0) {      
	      this.active = true;
    	  var offsets = Position.cumulativeOffset(this.element);
	      this.startTop = offsets[1];
	      this.startLeft = offsets[0];
	      this.startWidth = parseInt(Element.getStyle(this.element, 'width'));
	      this.startHeight = parseInt(Element.getStyle(this.element, 'height'));
	      this.startX = event.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
	      this.startY = event.clientY + document.body.scrollTop + document.documentElement.scrollTop;
	      
	      this.currentDirection = dir;
	      Event.stop(event);
	      //This is to fix resizing bug with only style:right on the beginning
	      //if not set, the left side moves if the right is touched   
	      $('ontomenuanchor').setStyle({left: $('ontomenuanchor').offsetLeft + 'px'});
	      smwhg_dragresizetoolbar.disableDragging();
	  }
    }
  },
  finishResize: function(event, success) {
    // this.unregisterEvents();

    this.active = false;
    this.resizing = false;

    if(this.options.zindex)
      this.element.style.zIndex = this.originalZ;
      
    if (this.options.resize) {
    	this.options.resize(this.element);
    }
  },
  keyPress: function(event) {
    if(this.active) {
      if(event.keyCode==Event.KEY_ESC) {
        this.finishResize(event, false);
        Event.stop(event);
      }
    }
  },
  endResize: function(event) {
    if(this.active && this.resizing) {
      this.finishResize(event, true);
      Event.stop(event);
    }
    this.active = false;
    this.resizing = false;
    smwhg_dragresizetoolbar.enableDragging();
    smwhg_dragresizetoolbar.fixAnchorSize();
  },
  draw: function(event) {
    var pointer = [Event.pointerX(event), Event.pointerY(event)];
    var style = this.element.style;
    if (this.currentDirection.indexOf('n') != -1) {
    	var pointerMoved = this.startY - pointer[1];
    	var margin = Element.getStyle(this.element, 'margin-top') || "0";
    	var newHeight = this.startHeight + pointerMoved;
    	if (newHeight > this.options.minHeight) {
    		style.height = newHeight + "px";
    		style.top = (this.startTop - pointerMoved - parseInt(margin)) + "px";
    	}
    }
    if (this.currentDirection.indexOf('w') != -1) {
    	var pointerMoved = this.startX - pointer[0];
    	var margin = Element.getStyle(this.element, 'margin-left') || "0";
    	var newWidth = this.startWidth + pointerMoved;
    	if (newWidth > this.options.minWidth) {
    		style.left = (this.startLeft - pointerMoved - parseInt(margin))  + "px";
    		style.width = newWidth + "px";
    	}
    }
    if (this.currentDirection.indexOf('s') != -1) {
    	var newHeight = this.startHeight + pointer[1] - this.startY;
    	if (newHeight > this.options.minHeight) {
    		style.height = newHeight + "px";
    	}
    }
    if (this.currentDirection.indexOf('e') != -1) {
    	var newWidth = this.startWidth + pointer[0] - this.startX;
    	if (newWidth > this.options.minWidth) {
    		style.width = newWidth + "px";
    	}
    }
    if(style.visibility=="hidden") style.visibility = ""; // fix gecko rendering
  },
  between: function(val, low, high) {
  	return (val >= low && val < high);
  },
  directions: function(event) {
    var pointer = [Event.pointerX(event), Event.pointerY(event)];
    var offsets = Position.cumulativeOffset(this.element);
    
	var cursor = '';
	if (this.between(pointer[1] - offsets[1], 0, this.options.top)) cursor += 'n';
	if (this.between((offsets[1] + this.element.offsetHeight) - pointer[1], 0, this.options.bottom)) cursor += 's';
	if (this.between(pointer[0] - offsets[0], 0, this.options.left)) cursor += 'w';
	if (this.between((offsets[0] + this.element.offsetWidth) - pointer[0], 0, this.options.right)) cursor += 'e';

	return cursor;
  },
  cursor: function(event) {
  	var cursor = this.directions(event);
	if (cursor.length > 0) {
		cursor += '-resize';
	} else {
		cursor = '';
	}
	this.element.style.cursor = cursor;		
  },
  update: function(event) {
   if(this.active) {
      if(!this.resizing) {
        var style = this.element.style;
        this.resizing = true;
        
        if(Element.getStyle(this.element,'position')=='') 
          style.position = "relative";
        
        if(this.options.zindex) {
          this.originalZ = parseInt(Element.getStyle(this.element,'z-index') || 0);
          style.zIndex = this.options.zindex;
        }
      }
      this.draw(event);

      // fix AppleWebKit rendering
      if(navigator.appVersion.indexOf('AppleWebKit')>0) window.scrollBy(0,0); 
      Event.stop(event);
      return false;
   }
  }
}

//Initialize dragging and resizing functions of stb
smwhg_dragresizetoolbar = new DragResizeHandler();
Event.observe(window, 'load', smwhg_dragresizetoolbar.callme.bind(smwhg_dragresizetoolbar));

/*
setTimeout(function() { 
	setTimeout( function(){
		smwhg_dragresizetoolbar.storePosition();
		smwhg_dragresizetoolbar.setPosition(100,100);
		smwhg_dragresizetoolbar.restorePosition();
		//var ret = smwhg_dragresizetoolbar.getPosition();
		//alert("PosX: "+ret[0]+" PosY: "+ret[0]);
		},1000);
},3000);
*/