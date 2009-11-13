/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


if (!window.SimplePopup) {
SimplePopup = {

    width: '90%',
    height: '95%',

    init: function(){
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
    },

    loadUrl: function(headline, url) {
        SimplePopup.init()
        SimplePopup.open(headline)
        SimplePopup.showPendingIndicator()
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
    },

    open: function(headline){
        var div=document.getElementById('SimplePopup')
        if (!div){
            div=document.createElement('div')
            div.id = 'SimplePopup'
            var body=document.getElementsByTagName('body')[0]
            body.appendChild(div)
        }
        div.style.display='block'
        div.style.width=SimplePopup.width
        div.style.height=SimplePopup.height
        div.style.position='fixed'
        div.style.top=parseInt((window.innerHeight-(window.innerHeight / 100 * parseInt(SimplePopup.height)))/2) + 'px'
        div.style.left=parseInt((window.innerWidth-(window.innerWidth / 100 * parseInt(SimplePopup.width)))/2) + 'px'
        div.style.backgroundColor='#FFFFFF'
        div.style.zIndex=100
        div.innerHTML = '<span style="font-weight: bold">'+headline+'</span>'
            +'<img src="'+us_Path+'/../scripts/GreyBox/smw_plus_closewindow_icon_16x16.png" '
            +'alt="Close" align="right" style="cursor:pointer; cursor:hand;" '
            +'onclick="SimplePopup.close();" />'
            +'<hr style="border: solid thin;"/>'
            +'<div id="SimplePopup_content" '
            +'style="width: 100%; text-align: center; vertical-align: middle;">'
            +'</div>'
    },

    showPendingIndicator: function(){
        var div=document.getElementById('SimplePopup_content')
        div.innerHTML='<img src="'+us_Path+'/../scripts/GreyBox/indicator.gif" align="center" alt="loading..."/>'
    }

}

}