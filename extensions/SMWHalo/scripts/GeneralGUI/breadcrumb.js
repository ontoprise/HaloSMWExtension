/**
 * 
 * @file
 * @ingroup SMWHaloMiscellaneous
 * 
 * @defgroup SMWHaloMiscellaneous SMWHalo miscellaneous components
 * @ingroup SMWHalo
 * 
 * @author Kai K�hn, Robert Ulrich
 * 
 * Breadcrumb is a tool which displays the last 5 (default) visited pages as a queue.
 * 
 * It uses a DIV element with id 'breadcrumb' to add its list content. If this is not
 * available it displays nothing.
 */
var Breadcrumb = Class.create();
Breadcrumb.prototype = {
    initialize: function(lengthOfBreadcrumb) {
        //set the maximum count of elements in the breadcumbs
        this.lengthOfBreadcrumb = lengthOfBreadcrumb;
    },
    
    update: function() {
        //Read breadcrumbs from cookie
        var breadcrumb = GeneralBrowserTools.getCookie("breadcrumb");
        var breadcrumbArray;
        //get the querystring without the title=$foo, since pagename is handled different
        var currenturlquerystring = this.removeTitleFromQuery(document.location.search);
        
        try{
            breadcrumbArray = breadcrumb.evalJSON(true);
        } catch(err) {
            breadcrumbArray = null;
        }
        

        if (breadcrumbArray == null) {
            //Initialize Array with first breadcrumb entry using json
            breadcrumbArray = [
                                {pageName: wgPageName,
                                queryString: currenturlquerystring
                                }
            ];
        } else {
            //get breadcrumbs from cookie string and add new title
            //Add new entry, if pagename is different
            if (breadcrumbArray[breadcrumbArray.length-1].pageName != wgPageName) {
                breadcrumbArray.push(
                                     {pageName: wgPageName,
                                      queryString: currenturlquerystring
                                     }
                );
            //Overwrite last entry, if pagename is the same but querystring is different
            //this prevents the breadcrumb from showing only one page with different querystrings
            //trade off is that only the last action on the specific page is shown
            } else if(breadcrumbArray[breadcrumbArray.length-1].pageName == wgPageName
                && breadcrumbArray[breadcrumbArray.length-1].queryString != currenturlquerystring ) {
                breadcrumbArray[breadcrumbArray.length-1]={pageName: wgPageName,
                                      queryString: currenturlquerystring
                                     };
                
            }

            //cut down breadcrumbs to maximum length
            if (breadcrumbArray.length > this.lengthOfBreadcrumb) {
                    breadcrumbArray.shift();
            }   
        }
        // serialize breadcrumb to JSON and (re-)set cookie
        document.cookie = "breadcrumb="+breadcrumbArray.toJSON()+"; path="+wgScript;
        this.pasteInHTML(breadcrumbArray);
    },
    
    pasteInHTML: function(breadcrumbArray) {
        var html = "";

        for (var index = 0, len = breadcrumbArray.length; index < len; ++index) {
            var breadcrumb = breadcrumbArray[index];
            // remove namespace and replace underscore by whitespace
            var title = breadcrumb.pageName.split(":");
            var show = title.length == 2 ? title[1] : title[0];
            show = show.replace(/_/g, " ");
            
            // add item
            var encURI = encodeURIComponent(breadcrumb.pageName);
            if (wgArticlePath.indexOf('?title=') != -1) {
            	encURI = encURI.replace(/%3A/g, ":"); // do not encode colon
            	var articlePath = wgArticlePath.replace("$1", encURI) + breadcrumb.queryString;
            } else {
           	    encURI = encURI.replace(/%2F/g, "/"); // do not encode slash
           	    encURI = encURI.replace(/%3A/g, ":"); // do not encode colon
            	var articlePath = wgArticlePath.replace("$1", encURI) + breadcrumb.queryString;
            }
            //add all previous visited pages as link
            if (index < len -1){
                html += '<a href="'+wgServer+articlePath+'">'+show+' &gt; </a>';
            }
            //add current page as normal text
            else {
               html += '<span id="smwh_breadcrumb_currentpage">'+show+'</span>';
            }
            
        };
        //add the last item (current page) not as link

        //Check if breadcrumb-div exists
        var bc_div = $('breadcrumb');

        //Check if there's no breadcrumb div
        if ( bc_div == null){
            //if so, check if there's a firstHeading
            var firstHeading = $('firstHeading');
            if( firstHeading != null){
                //Add breadcrumb div before Heading
                firstHeading.insert({
                    before: "<div id='breadcrumb'/>"
                });
                bc_div = $('breadcrumb');
            }
        }
        //verify that the div exists
        if (bc_div != null) bc_div.innerHTML = html;
    },

    //return the querystring without the title=$foo
    removeTitleFromQuery: function(querystring){
      if(querystring != null && querystring !=undefined){
        //remove title=$foobar& from querystring
        querystring = querystring.replace(/title=(.*?)&/i,"");
        //if title= is the only query and so the regex above doesn't match, remove it completely
        querystring = querystring.replace(/\?title=(.*?)$/i,"");
      } else {
        querystring = "";
      }
      return querystring.replace('/title=(.*?)&/i',"");
    }

}
var smwhg_breadcrumb = new Breadcrumb(5);
Event.observe(window, 'load', smwhg_breadcrumb.update.bind(smwhg_breadcrumb));