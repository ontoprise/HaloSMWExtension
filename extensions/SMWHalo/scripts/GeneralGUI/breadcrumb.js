/**
 * Breadcrumb is a tool which displays the last 5 (default) visited pages as a queue.
 * 
 * It uses a DIV element with id 'breadcrumb' to add its list content. If this is not
 * available it displays nothing.
 */
var Breadcrumb = Class.create();
Breadcrumb.prototype = {
    initialize: function(lengthOfBreadcrumb) {
        this.lengthOfBreadcrumb = lengthOfBreadcrumb;
    },
    
    update: function() {
        var breadcrumb = GeneralBrowserTools.getCookie("breadcrumb");
        var breadcrumbArray;
        if (breadcrumb == null) {
            breadcrumb = wgPageName;
            breadcrumbArray = [breadcrumb];
        } else {
            // parse breadcrumb and add new title
            breadcrumbArray = breadcrumb.split(" ");
            // do not add doubles
            if (breadcrumbArray[breadcrumbArray.length-1] != wgPageName) {
                breadcrumbArray.push(wgPageName);
                if (breadcrumbArray.length > this.lengthOfBreadcrumb) {
                    breadcrumbArray.shift();
                } 
            }
            //serialize breadcrumb
            breadcrumb = "";
            for (var i = 0; i < breadcrumbArray.length-1; i++) {
                breadcrumb += breadcrumbArray[i]+" ";
            }
            breadcrumb += breadcrumbArray[breadcrumbArray.length-1];
                
        }
        // (re-)set cookie
        document.cookie = "breadcrumb="+breadcrumb+"; path="+wgScript;
        this.pasteInHTML(breadcrumbArray);
    },
    
    pasteInHTML: function(breadcrumbArray) {
        var html = "";
        breadcrumbArray.each(function(b) {
            
            // remove namespace and replace underscore by whitespace
            var title = b.split(":");
            var show = title.length == 2 ? title[1] : title[0];
            show = show.replace(/_/g, " ");
            
            // add item
             var encURI = encodeURIComponent(b);
            if (wgArticlePath.indexOf('?title=') != -1) {
            	encURI = encURI.replace(/%3A/g, ":"); // do not encode colon
            	var articlePath = wgArticlePath.replace("$1", encURI);
            }  else {
           	    encURI = encURI.replace(/%2F/g, "/"); // do not encode slash
           	    encURI = encURI.replace(/%3A/g, ":"); // do not encode colon
            	var articlePath = wgArticlePath.replace("$1", encURI);
            }
            html += '<a href="'+wgServer+articlePath+'">'+show+' &gt; </a>'; 
        });
        var bc_div = $('breadcrumb');
        if (bc_div != null) bc_div.innerHTML = html;
    }
}
var smwhg_breadcrumb = new Breadcrumb(5);
Event.observe(window, 'load', smwhg_breadcrumb.update.bind(smwhg_breadcrumb));