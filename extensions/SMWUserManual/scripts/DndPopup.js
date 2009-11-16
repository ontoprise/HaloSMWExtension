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
    // element where the popup is embedded in, if not set, it's attached to the <body>
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

    isVisible: function() {
        var obj=document.getElementById(this.id)
        if (!obj) return false
        if (obj.style.visibility == 'visible' ||
            obj.style.display == 'block' ||
            obj.style.display == 'inline')
            return true
        return false
    },

    createDivBox: function() {
        var div=document.createElement('div')
        div.id=this.id
        div.style.position='fixed'
        div.style.visibility='hidden'
        div.style.height=this.height
        div.style.width=this.width
        div.style.left=this.left
        div.style.top=this.top
        div.innerHTML='<table border="0" style="width:100%; height:100%" bgcolor="'+this.headerBgColor+'" cellspacing="0" cellpadding="2">'
            +'<tr><td width="100%">'
            +'<table style="border:0px; width:100%; height:100%;" cellspacing="0" cellpadding="0">'
            +'<tr>'
            +'<td id="'+this.id+'_dragbar"'+(this.dnd?' style="cursor:move"':'')+' width="100%">'
            +'<ilayer width="100%" onSelectStart="return false">'
            +'<layer width="100%">'
            +'<font color="'+this.headerColor+'">'+this.headline+'</font>'
            +'</layer></ilayer></td>'
            +'<td style="cursor:hand; cursor:pointer; vertical-align:middle">'
            +'<a onclick="'+this.actionOnClose+'return false" href="#"><img src="'+DND_POPUP_DIR+'skins/close.gif" border="0"></a></td>'
            +'</tr>'
            +'<tr style="width:100%; height:100%;">'
            +'<td bgcolor="'+this.boxBgColor+'" style="width:100%; height:100%; padding:4px; vertical-align:top; color:'+this.boxColor+';" colspan="2">'
            +'<div id="'+this.id+'_content"></div>'
            +'</td></tr></table>'
        if (this.attachTo)
            this.attachTo.appendChild(div)
        else {
            var body=document.getElementsByTagName('body')[0]
            body.appendChild(div)
        }
    },

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