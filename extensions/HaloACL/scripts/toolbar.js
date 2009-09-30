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

YAHOO.haloacl.toolbar_handleSaveClick = function(element){

    //var textbox = $('wpTextbox1');
    var tps = $('haloacl_toolbar_pagestate');
    var state  = tps[tps.selectedIndex].text;

    if (state == "protected"){
        var tpw = $('haloacl_template_protectedwith');
        var tmpvalue  = tpw[tpw.selectedIndex].text;
        //textbox.value = textbox.value + "{{#protectwith:"+$('haloacl_template_protectedwith').value+"}}";
        YAHOO.haloacl.toolbar.callAction('setToolbarChoose',{tpl:tmpvalue});

    }else{
        //textbox.value = textbox.value + "{{#protectwith:unprotected}}";
        YAHOO.haloacl.toolbar.callAction('setToolbarChoose',{tpl:'unprotected'},function(result){
           
        });
    }


};

YAHOO.haloacl.toolbar_initToolbar = function(){
	var value = $('wpSave').readAttribute('value');
	var title = $('wpSave').readAttribute('title');
	var accesskey = $('wpSave').readAttribute('accesskey');
	var tabindex = $('wpSave').readAttribute('tabindex');
	var name = $('wpSave').readAttribute('name');
	$('wpSave').hide();
	new Insertion.After('wpSave', '<input id="wpSaveReplacement" type="button" value="'+value+'" title="'+title+'" accesskey="'+accesskey+'" tabindex="'+tabindex+'" name="'+name+'" />');
//        $('wpSave').writeAttribute("type","button");
    $('wpSaveReplacement').writeAttribute("onClick","YAHOO.haloacl.toolbar_handleSaveClick(this);return false;");
    YAHOO.haloacl.toolbar_updateToolbar();

}

YAHOO.haloacl.toolbar_updateToolbar = function(){
	var selection = $('haloacl_toolbar_pagestate');
	var state = selection[selection.selectedIndex].text;
    if(state == "protected"){
        try{
     	   $('haloacl_template_protectedwith').show();
        }catch(e){}
        try{
     	   $('haloacl_template_protectedwith_desc').show();
        }catch(e){}
        try{
      	  $('haloacl_toolbar_popuplink').show();
        }catch(e){}
    }else{
        $('haloacl_template_protectedwith').hide();
        $('haloacl_template_protectedwith_desc').hide();
        $('haloacl_toolbar_popuplink').hide();
    }
};


YAHOO.haloacl.callbackSDpopupByName = function(result){
	var tpw = $('haloacl_template_protectedwith');
	var protectedWith = tpw[tpw.selectedIndex].text;
    if(result.status == '200'){
        YAHOO.haloaclrights.popup(result.responseText, protectedWith, 'toolbar');
    }else{
        alert(result.responseText);
    }
};

YAHOO.haloacl.sDpopupByName = function(sdName){
    YAHOO.haloacl.callAction('sDpopupByName', {
        sdName:sdName
    }, YAHOO.haloacl.callbackSDpopupByName);

};



