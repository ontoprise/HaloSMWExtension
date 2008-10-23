/**
 * Breadcrump is a tool which displays the last 5 (default) visited pages as a queue.
 * 
 * It uses a DIV element with id 'breadcrump' to add its list content. If this is not
 * available it displays nothing.
 */
var Breadcrump = Class.create();
Breadcrump.prototype = {
    initialize: function(lengthOfBreadcrump) {
        this.lengthOfBreadcrump = lengthOfBreadcrump;
    },
    
    update: function() {
        var breadcrump = GeneralBrowserTools.getCookie("breadcrump");
        var breadcrumpArray;
        if (breadcrump == null) {
            breadcrump = wgTitle;
            breadcrumpArray = [breadcrump];
        } else {
            // parse breadcrump and add new title
            breadcrumpArray = breadcrump.split(",");
            // do not add doubles
            if (breadcrumpArray[breadcrumpArray.length-1] != wgPageName) {
                breadcrumpArray.push(wgPageName);
                if (breadcrumpArray.length > this.lengthOfBreadcrump) {
                    breadcrumpArray.shift();
                } 
            }
            //serialize breadcrump
            breadcrump = "";
            for (var i = 0; i < breadcrumpArray.length-1; i++) {
                breadcrump += breadcrumpArray[i]+",";
            }
            breadcrump += breadcrumpArray[breadcrumpArray.length-1];
                
        }
        // (re-)set cookie
        document.cookie = "breadcrump="+breadcrump+"; path="+wgScript;
        this.pasteInHTML(breadcrumpArray);
    },
    
    pasteInHTML: function(breadcrumpArray) {
        var html = "";
        breadcrumpArray.each(function(b) {
            
            // remove namespace and replace underscore by whitespace
            var title = b.split(":");
            var show = title.length == 2 ? title[1] : title[0];
            show = show.replace("_", " ");
            
            // add item 
            var encURI = encodeURIComponent(b);
            encURI = encURI.replace(/%2F/g, "/"); // do not encode slash
            html += '<a href="'+wgServer+wgScript+'/'+encURI+'">'+show+' &gt; </a>'; 
        });
        var bc_div = $('breadcrump');
        if (bc_div != null) bc_div.innerHTML = html;
    }
}
var smwhg_breadcrump = new Breadcrump(5);
Event.observe(window, 'load', smwhg_breadcrump.update.bind(smwhg_breadcrump));