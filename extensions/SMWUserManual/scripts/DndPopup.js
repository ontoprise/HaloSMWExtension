/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var DndPopup = Class.create();
DndPopup.prototype = {

    id:             'DndPopup',     // name of id attribute of div element
    dnd:            1,              // drag and drop enabled (0 = disable)
    headline:       '',             // headline which is displayed
    preserveContent:0,              // keep content in page when popup is closed
    height:         '300px',        // height of popup
    width:          '400px',        // width of popup
    left:           '250px',        // left offset where popup starts
    top:            '150px',        // top offset where popup starts
    headerColor:    '#FFFFFF',      // font color for headline
    headerBgColor:  '#000080',      // background color for headline
    boxColor:       '#000000',      // font color for content
    boxBgColor:     '#CBCBCB',      // bckground color for content
    zIndex:         100,            // zIndex for popup being in foreground
    // DOM node where the popup is embedded in, if not set, it's embedded in <body>
    attachTo:       null,
    // function to call when hitting the close button
    actionOnClose:  'DndPopup.close();',
    // image of the close button which is displayed in the upper right corner
    closeImage:     DND_POPUP_DIR+'/close.gif',

    initialize: function(divid, headline, width, height){
        if (divid) this.id=divid
        if (headline) this.headline=headline
        if (width) this.width=width
        if (height) this.height=height
    },

    /**
     * calls the popup, makes it visible to the user. Also two event handlers
     * mousedown and mouseup are registered, which toggle the dragging (i.e. after
     * a mousedown event and before receiving the mouseup event the mouse key is
     * pressed and the window can be dragged.
     */
    open: function() {
        var obj=document.getElementById(this.id)
        if (!obj) this.createDivBox()
        obj=document.getElementById(this.id)
        obj.style.visibility = 'visible'
        obj.style.zIndex = this.zIndex
        if (this.dnd) {
            Event.observe(obj, "mousedown", this.initializeDrag.bindAsEventListener(this), false)
            Event.observe(obj, "mouseup", this.finishDragging.bindAsEventListener(this), false)
        }
    },

    /**
     * check if the popup is visible and return true if this is the case
     * or false if the popup is not vivible to the user
     */
    isVisible: function() {
        var obj=document.getElementById(this.id)
        if (!obj) return false
        if (obj.style.visibility == 'visible' ||
            obj.style.display == 'block' ||
            obj.style.display == 'inline')
            return true
        return false
    },

    /**
     * create the div box with the popup nd it's elements
     * later the content of the popup can be defined by calling the method
     * setHtmlContent() whih inserts html inside a div container.
     * The created html is added to the document below the <body> tag or another
     * specified DOM node (in the member variable attachTo)
     */
    createDivBox: function() {
        var div=document.createElement('div')
        div.id=this.id
        div.style.position='fixed'
        div.style.visibility='hidden'
        div.style.height=this.height
        div.style.width=this.width
        div.style.left=this.left
        div.style.top=this.top
        div.innerHTML=''
            +'<table style="border: solid 2px '+this.headerBgColor+';" width="100%" height="100%" cellspacing="0" cellpadding="0">'
            +'<tr>'
            +'<td id="'+this.id+'_dragbar" bgcolor="'+this.headerBgColor+'"'
            +(this.dnd?' style="cursor:move"':'')+' color="'+this.headerColor+'" width="'+(parseInt(this.width)-15)+'px">'
            +(this.headline?this.headline:'&nbsp;')+'</td>'
            +'<td style="cursor:hand; cursor:pointer; vertical-align:middle" bgcolor="'+this.headerBgColor+'">'
            +'<a onclick="'+this.actionOnClose+'return false" href="#"><img src="'+this.closeImage+'" border="0"></a></td>'
            +'</tr>'
            +'<tr width="100%" height="100%">'
            +'<td bgcolor="'+this.boxBgColor+'" style="width:100%; height:100%; padding:4px; vertical-align:top; color:'+this.boxColor+';" colspan="2">'
            +'<div id="'+this.id+'_content"></div></td></tr></table>'
        if (this.attachTo)
            this.attachTo.appendChild(div)
        else {
            var body=document.getElementsByTagName('body')[0]
            body.appendChild(div)
        }
    },

    /**
     * add html content to the popup. Needs to be called after createDivBox()
     */
    setHtmlContent: function(html) {
        document.getElementById(this.id+'_content').innerHTML=html
    },

    /* drag and drop functions start here */

    /**
     * sets dragging to false. This happens when the mouse is released
     * (mouseup event is called)
     */
    finishDragging: function() {
        this.dragging=false
    },

    /**
     * new position for help popup, called on mouse move. The window is positioned
     * only if the mouse is pressed (mousedown) but has not yet been released
     * (mouseup)
     *
     * @param Event e
     */
    dragDrop: function(e){
        if (!e) e=window.event
        if (this.dragging) {
            var obj = document.getElementById(this.id)
            obj.style.left=this.tempx+e.clientX-this.offsetx + 'px'
            obj.style.top=this.tempy+e.clientY-this.offsety + 'px'
        }
    },

    /**
     * start with drag and drop, called on a mousedown event. It's checked if the
     * event happend inside the table column id=smw_csh_dragbar. If this is the
     * case, a mousemove event is registered and the dagging is set to true.
     *
     * @param Event e
     */
    initializeDrag: function(e) {
        if (!e) e=window.event
        var eL = e.srcElement ? e.srcElement : e.target ? e.target : e.currentTarget
        var obj= document.getElementById(this.id);
        while (eL.tagName != 'HTML' && eL.tagName != 'BODY' && eL.id!=this.id+"_dragbar"){
            eL = eL.parentNode
        }
        if (eL.id==this.id+"_dragbar"){
            this.offsetx=e.clientX
            this.offsety=e.clientY
            this.tempx=parseInt(obj.style.left)
            this.tempy=parseInt(obj.style.top)
            this.dragging=true
            Event.observe(obj, 'mousemove', this.dragDrop.bindAsEventListener(this), false)
        }
    },
    /* drag and drop functions end here */

    /**
     * close the popup. This is done by removing all related html from the
     * document. If the member variable preserveContent is set, then the html
     * is not removed from the document but made invisible to the user. Calling
     * the same popup again would then reveal the content in it's state when the
     * popup close() function was called.
     */
    close: function() {
        var obj = document.getElementById(this.id)
        if (this.preserveContent) {
            obj.style.visibility="hidden"
            obj.style.zIndex = null
            Event.stopObserving(obj, 'mousedown', this.initializeDrag.bindAsEventListener(this) )
            Event.stopObserving(obj, 'mousemove', this.dragDrop.bindAsEventListener(this) )
            Event.stopObserving(obj, "mouseup", this.finishDragging.bindAsEventListener(this) )
        } else
            obj.parentNode.removeChild(obj)
        obj=document.getElementById(this.id+'_overlay')
        if (obj) obj.parentNode.removeChild(obj)
    }
}