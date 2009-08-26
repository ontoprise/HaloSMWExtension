// general ajax stuff
YAHOO.namespace("haloacl");
YAHOO.namespace("haloacl.toolbar");




YAHOO.haloacl.toolbar.loadContentToDiv = function(targetdiv, action, parameterlist){
    /*   var queryparameterlist = {
        rs:action
    };
     */


    console.log($(targetdiv));
    
    var querystring = "rs="+action;

    if(parameterlist != null){
        for(param in parameterlist){
            // temparray.push(parameterlist[param]);
            querystring = querystring + "&rsargs[]="+parameterlist[param];
        }
    }

    new Ajax.Request("?action=ajax", {
        //method:tab.get('loadMethod'),
        method:'post',
        // parameters: queryparameterlist,
        parameters: querystring,
        asynchronous:true,
        evalScripts:true,
      //  insertion:before,
        onSuccess: function(o) {
            console.log(o);
            $(targetdiv).insert({top:o.responseText})
        },
        onFailure: function(o) {
        }
    });
};

