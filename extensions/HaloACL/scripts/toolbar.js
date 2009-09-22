/*  Copyright 2009, ontoprise GmbH
*  This file is part of the HaloACL-Extension.
*
*   The HaloACL-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The HaloACL-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file contains the class HACLGroup.
 *
 * @author B2browse/Patrick Hilsbos, Steffen Schachtler
 * Date: 03.04.2009
 *
 */

/**
 * Description of HACL_AjaxConnector
 *
 * @author hipath
 */

// general ajax stuff
YAHOO.namespace("haloacl");
YAHOO.namespace("haloacl.toolbar");


YAHOO.haloacl.toolbar.loadContentToDiv = function(targetdiv, action, parameterlist){
    /*   var queryparameterlist = {
        rs:action
    };
     */


    //    console.log($(targetdiv));
    
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
            //            console.log(o);
            $(targetdiv).insert({
                top:o.responseText
            })
        },
        onFailure: function(o) {
        }
    });
};

YAHOO.haloacl.toolbar.callAction = function(action, parameterlist, callback){
    if(callback == null){
        callback = function(result){
        //            console.log("stdcallback:"+result);
        }
    }

    var querystring = "rs="+action;

    if(parameterlist != null){
        for(param in parameterlist){
            querystring = querystring + "&rsargs[]="+parameterlist[param];
        }
    }
    new Ajax.Request("?action=ajax",{
        method:'post',
        onSuccess:function(result){
            try{
                $('wpSave').writeAttribute("type","submit");
                $('wpSave').writeAttribute("onClick","");

            }catch(e){}
            $('wpSave').click();
        },
        onFailure:function(result){
            try{
                $('wpSave').writeAttribute("type","submit");
                $('wpSave').writeAttribute("onClick","");
            }catch(e){}
            $('wpSave').click();
        },
        parameters:querystring
    });
};
