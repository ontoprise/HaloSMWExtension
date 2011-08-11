/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


if (!window.SimplePopup) {
SimplePopup = {

    headline: '',
    width: '90%',
    height: '95%',
    items: [],
    zIndex: 100,

    init: function(headline, width, height, items){
        if (headline) SimplePopup.headline=headline
        if (width) SimplePopup.width=width
        if (height) SimplePopup.height=height
        if (items) SimplePopup.items=items
        
        // create the HTTP request object
        // Mozilla, Safari and other browsers
        if (window.XMLHttpRequest)
            SimplePopup.httpRequest = new XMLHttpRequest()
        // IE
        else if (window.ActiveXObject) {
        	try {
                SimplePopup.httpRequest = new ActiveXObject("Msxml2.XMLHTTP")
            }
            catch (e) {
                try {
                    SimplePopup.httpRequest = new ActiveXObject("Microsoft.XMLHTTP")
                }
                catch (e) {}
            }
        }
        // open the popup and show pending indicator.
        SimplePopup.open()
        SimplePopup.showPendingIndicator()
    },

    loadUrl: function(headline, url, width, height, items) {
        SimplePopup.init(headline, width, height, items)
        SimplePopup.httpRequest.onreadystatechange = SimplePopup.handleResponse
        SimplePopup.httpRequest.open("GET", url)
        SimplePopup.httpRequest.send(null)

    },

    handleResponse: function() {
        var result
      	if (SimplePopup.httpRequest.readyState == 4 &&
            SimplePopup.httpRequest.status == 200) {
            result = SimplePopup.httpRequest.responseText;
            SimplePopup.httpRequest = null;
        }
        else return
        var div = document.getElementById('SimplePopup_content')
        div.innerHTML=result
    },
    
    close: function(){
        var div=document.getElementById('SimplePopup')
        if (div) div.parentNode.removeChild(div)
        div=document.getElementById('SimplePopup_overlay')
        if (div) div.parentNode.removeChild(div)
    },

    open: function(){
        var body=document.getElementsByTagName('body')[0]
        // insert overlay that fades background
        var div=document.createElement('div')
        div.id="SimplePopup_overlay"
        div.style.zIndex=SimplePopup.zIndex-1
        div.style.height=SimplePopup.getDocumentHeight()+'px'
        // get the first element node, so that the div is inserted there
        var firstNode=body.firstChild
        while(firstNode && firstNode.nodeType!=1)
            firstNode=firstNode.nextSibling
        if (firstNode) body.insertBefore(div, firstNode)
        else body.appendChild(div)
        // get the div for the popup itself, or create it if it's not there
        div=document.getElementById('SimplePopup')
        if (!div){
            div=document.createElement('div')
            div.id = 'SimplePopup'
            body.appendChild(div)
        }
        div.style.width=SimplePopup.width
        div.style.height=SimplePopup.height
        div.style.top=parseInt((window.innerHeight-(window.innerHeight / 100 * parseInt(SimplePopup.height)))/2) + 'px'
        div.style.left=parseInt((window.innerWidth-(window.innerWidth / 100 * parseInt(SimplePopup.width)))/2) + 'px'
        div.style.backgroundColor=SimplePopup.backgroundColor
        div.style.zIndex=SimplePopup.zIndex
        div.innerHTML = '<span style="font-weight: bold">'+SimplePopup.headline+'</span>'
            +'<img src="'+SIMPLE_POPUP_DIR+'smw_plus_closewindow_icon_16x16.png" '
            +'alt="Close" align="right" style="cursor:pointer; cursor:hand;" '
            +'onclick="SimplePopup.close();" />'
            +'<hr style="border: solid thin;"/>'
            +'<div id="SimplePopup_content"></div>'
    },

    showPendingIndicator: function(){
        var div=document.getElementById('SimplePopup_content')
        div.innerHTML='<img src="'+SIMPLE_POPUP_DIR+'indicator.gif" align="center" alt="loading..."/>'
    },

    getDocumentHeight: function() {
        var D = document;
        return Math.max(
            Math.max(D.body.scrollHeight, D.documentElement.scrollHeight),
            Math.max(D.body.offsetHeight, D.documentElement.offsetHeight),
            Math.max(D.body.clientHeight, D.documentElement.clientHeight)
        )
    }

}

}