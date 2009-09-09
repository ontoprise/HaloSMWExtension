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

// Globals
YAHOO.namespace("haloacl");
YAHOO.namespace("haloaclrights");
YAHOO.namespace ("haloacl.constants");
YAHOO.namespace ("haloacl.settings");
YAHOO.namespace ("haloacl.manageUser");

// log debug information to js-console
YAHOO.haloacl.debug = false;

if(YAHOO.haloacl.debug){
    console.log("======== DEBUG MODE ENABLED =========");
    console.log("-- visit haloacl.js to switch mode --");
    console.log("=====================================");
}


// set standard define-type for createacl panel
// must be one of: individual, privateuse, allusers, allusersregistered, allusersanonymous
YAHOO.haloacl.createAclStdDefine = "individual";


YAHOO.haloacl.panelcouner = 0;
// has all checked users from grouptree
YAHOO.haloacl.clickedArrayGroups = new Array();
// has all checked users form datatable
YAHOO.haloacl.clickedArrayUsers = new Array();
// has all selected ACL templates from template tree
YAHOO.haloacl.selectedTemplates = new Array();
// has groups for the checked users [panelid][username] = groupsstring
YAHOO.haloacl.clickedArrayUsersGroups = new Array();

// has all checked users from righttree
YAHOO.haloaclrights.clickedArrayGroups = new Array();

// knows, if the actual modificationrights have been saved
YAHOO.haloacl.modrightssaved = false;




// Tabview related stuff

// building the main tabview
YAHOO.haloacl.buildMainTabView = function(containerName,requestedTitle,showWhitelistTab,activeTab){
    if(YAHOO.haloacl.debug) console.log("got requestedtitle:"+requestedTitle);
    if(requestedTitle != null){
        YAHOO.haloacl.requestedTitle = requestedTitle;
    }else{
        YAHOO.haloacl.requestedTitle = "";
    }
    
    var createACLActive = false;
    var manageUserActive = false;
    var manageACLActive = false;
    var whitelistActive = false;
    if(activeTab == "createACL"){
        createACLActive = true;
    }else if(activeTab == "manageACLs"){
        manageACLActive = true;
    }else if (activeTab == "manageUsers"){
        manageUserActive = true;
    }else if(activeTab == "whitelists"){
        whitelistActive = true;
    }



    YAHOO.haloacl.haloaclTabs = new YAHOO.widget.TabView(containerName);

    var tab1 = new YAHOO.widget.Tab({
        label: gLanguage.getMessage('createACL'),
        dataSrc:'createACLPanels',
        cacheData:false,
        active:createACLActive,
        id:'createACLPanel_button'
    });
    tab1._dataConnect = YAHOO.haloacl.tabDataConnect;
    YAHOO.haloacl.haloaclTabs.addTab(tab1);
    tab1.addListener('click', function(e){});
    $(tab1.get('contentEl')).setAttribute('id','creataclTab');
    tab1.addListener('click', function(e){
        $('manageaclmainTab').innerHTML = "";
        $('manageuserTab').innerHTML = "";
    });

    new YAHOO.widget.Tooltip("createACLPanel_tooltip", {
        context:"createACLPanel_button",
        text:"Create standard ACL, Create ACL template and Create ACL default user template",
        zIndex :10
    });


    // ------

    var tab2 = new YAHOO.widget.Tab({
        label: gLanguage.getMessage('manageACLs'),
        dataSrc:'createManageACLPanels',
        cacheData:false,
        active:manageACLActive,
        id:"manageACLPanel_button"
    });
    tab2._dataConnect = YAHOO.haloacl.tabDataConnect;
    YAHOO.haloacl.haloaclTabs.addTab(tab2);
    tab2.addListener('click', function(e){});
    $(tab2.get('contentEl')).setAttribute('id','manageaclmainTab');
    tab1.addListener('click', function(e){
        $('creataclTab').innerHTML = "";
        $('manageuserTab').innerHTML = "";
    });

    new YAHOO.widget.Tooltip("manageACLPanel_tooltip", {
        context:"manageACLPanel_button",
        text:gLanguage.getMessage('manageACLTooltip'),
        zIndex :10
    });
    // ------

    var tab3 = new YAHOO.widget.Tab({
        label: gLanguage.getMessage('manageUser'),
        dataSrc:'manageUserContent',
        cacheData:false,
        active:manageUserActive,
        id:"manageUserContent_button"
    });
    tab3._dataConnect = YAHOO.haloacl.tabDataConnect;
    YAHOO.haloacl.haloaclTabs.addTab(tab3);
    tab3.addListener('click', function(e){});
    $(tab3.get('contentEl')).setAttribute('id','manageuserTab');
    tab1.addListener('click', function(e){
        $('creataclTab').innerHTML = "";
        $('manageaclmainTab').innerHTML = "";
    });

    new YAHOO.widget.Tooltip("manageUserContent_tooltip", {
        context:"manageUserContent_button",
        text:gLanguage.getMessage('manageUserTooltip'),
        zIndex :10
    });
    // ------

    if(showWhitelistTab == "true"){
        var tab4 = new YAHOO.widget.Tab({
            label: gLanguage.getMessage('whitelists'),
            dataSrc:'whitelistsContent',
            cacheData:false,
            active:whitelistActive,
            id:"whitelist_button"
        });
        tab4._dataConnect = YAHOO.haloacl.tabDataConnect;
        YAHOO.haloacl.haloaclTabs.addTab(tab4);
        tab4.addListener('click', function(e){});
        $(tab4.get('contentEl')).setAttribute('id','whitelistsTab');

        new YAHOO.widget.Tooltip("whitelist_tooltip", {
            context:"whitelist_button",
            text:gLanguage.getMessage('manageWhitelistTooltip'),
            zIndex :10
        });
    }
// ------

};


// building the  sub tabview
YAHOO.haloacl.buildSubTabView = function(containerName){
    YAHOO.haloacl.haloaclTabs = new YAHOO.widget.TabView(containerName);

    if (containerName == "haloaclsubViewManageACL") {
        var tab1 = new YAHOO.widget.Tab({
            label: gLanguage.getMessage('manageExistingACLs'),
            dataSrc:'createManageExistingACLContent',
            cacheData:false,
            active:true,
            id:"createStdAclTab"
        });
        tab1._dataConnect = YAHOO.haloacl.tabDataConnect;
        YAHOO.haloacl.haloaclTabs.addTab(tab1);
        tab1.addListener('click', function(e){});


        // ------

        var tab2 = new YAHOO.widget.Tab({
            label: gLanguage.getMessage('manageDefaultUserTemplate'),
            dataSrc:'createManageUserTemplateContent',
            cacheData:false,
            active:false,
            id:"createTmpAclTab"
        });
        tab2._dataConnect = YAHOO.haloacl.tabDataConnect;
        YAHOO.haloacl.haloaclTabs.addTab(tab2);
        tab2.addListener('click', function(e){});


        // ------

        var tab3 = new YAHOO.widget.Tab({
            label: gLanguage.getMessage('manageQuickAccess'),
            dataSrc:'createQuickAclTab',
            cacheData:false,
            active:false,
            id:"createQuickAclTab"
        });
        tab3._dataConnect = YAHOO.haloacl.tabDataConnect;
        YAHOO.haloacl.haloaclTabs.addTab(tab3);
        tab3.addListener('click', function(e){});


    } else if (containerName == "haloaclsubView") {

        var tab1 = new YAHOO.widget.Tab({
            label: gLanguage.getMessage('createStandardACL'),
            dataSrc:'createAclContent',
            cacheData:false,
            active:true,
            id:"createStdAclTab"
        });
        tab1._dataConnect = YAHOO.haloacl.tabDataConnect;
        YAHOO.haloacl.haloaclTabs.addTab(tab1);
        tab1.addListener('click', function(e){
            $('createTmpAclTab_content').innerHTML = "";
            $('createUserAclTab_content').innerHTML = "";
        });
        $(tab1.get('contentEl')).setAttribute('id','createStdAclTab_content');


        // ------

        var tab2 = new YAHOO.widget.Tab({
            label: gLanguage.getMessage('createACLTemplate'),
            dataSrc:'createAclTemplateContent',
            cacheData:false,
            active:false,
            id:"createTmpAclTab"
        });
        tab2._dataConnect = YAHOO.haloacl.tabDataConnect;
        YAHOO.haloacl.haloaclTabs.addTab(tab2);
        tab2.addListener('click', function(e){
            $('createStdAclTab_content').innerHTML = "";
            $('createUserAclTab_content').innerHTML = "";
        });
        $(tab2.get('contentEl')).setAttribute('id','createTmpAclTab_content');



        // ------

        var tab3 = new YAHOO.widget.Tab({
            label: gLanguage.getMessage('createDefaultUserTemplate'),
            dataSrc:'createAclUserTemplateContent',
            cacheData:false,
            active:false,
            id:"createUserAclTab"
        });
        tab3._dataConnect = YAHOO.haloacl.tabDataConnect;
        YAHOO.haloacl.haloaclTabs.addTab(tab3);
        tab3.addListener('click', function(e){
            $('createStdAclTab_content').innerHTML = "";
            $('createTmpAclTab_content').innerHTML = "";
        });
        $(tab3.get('contentEl')).setAttribute('id','createUserAclTab_content');

    }

};


YAHOO.haloacl.tabDataConnect = function() {
    var tab = this;
    /*
    var queryparameterlist = {
        rs:tab.get('dataSrc')
    };
     */
    var querystring = "rs="+tab.get('dataSrc');
    var postData = tab.get('postData');
    
    if(postData != null){
        for(param in postData){
            //queryparameterlist.rsargs = postData[param];
            querystring = querystring + "&rsargs[]="+postData[param];
        }

    }
    YAHOO.util.Dom.addClass(tab.get('contentEl').parentNode, tab.LOADING_CLASSNAME);
    tab._loading = true;
    new Ajax.Updater(tab.get('contentEl'), "?action=ajax", {
        //method:tab.get('loadMethod'),
        method:'post',
        parameters:querystring,
        // parameters:queryparameterlist,
        asynchronous:true,
        evalScripts:true,
        onSuccess: function(o) {
            YAHOO.util.Dom.removeClass(tab.get('contentEl').parentNode, tab.LOADING_CLASSNAME);
            tab.set('dataLoaded', true);
            tab._loading = false;
        },
        onFailure: function(o) {
            YAHOO.util.Dom.removeClass(tab.get('contentEl').parentNode, tab.LOADING_CLASSNAME);
            tab._loading = false;
        }
    });
};

// general ajax stuff
YAHOO.haloacl.loadContentToDiv = function(targetdiv, action, parameterlist){
    /*   var queryparameterlist = {
        rs:action
    };
     */
    var querystring = "rs="+action;

    if(parameterlist != null){
        for(param in parameterlist){
            // temparray.push(parameterlist[param]);
            querystring = querystring + "&rsargs[]="+parameterlist[param];
        }
    }

    new Ajax.Updater(targetdiv, "?action=ajax", {
        //method:tab.get('loadMethod'),
        method:'post',
        // parameters: queryparameterlist,
        parameters: querystring,
        asynchronous:true,
        evalScripts:true,
        onSuccess: function(o) {
            tab._loading = false;
            $(targetdiv).scrollTo();
            $(targetdiv).visible();
        },
        onFailure: function(o) {
        }
    });
};


YAHOO.haloacl.sendXmlToAction = function(xml, action,callback,parentNode){
    if(callback == null){
        callback = function(result){
            alert("stdcallback:"+result);
        }
    }
    var querystring = "rs="+action+"&rsargs[]="+escape(xml);
    if(parentNode != ""){
        querystring += "&rsargs[]="+escape(parentNode);
    }

    new Ajax.Request("?action=ajax",{
        method:'post',
        onSuccess:callback,
        onFailure:callback,
        parameters:querystring
    });


};

YAHOO.haloacl.callAction = function(action, parameterlist, callback){
    if(callback == null){
        callback = function(result){
            alert("stdcallback:"+result);
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
        onSuccess:callback,
        onFailure:callback,
        parameters:querystring
    });
};

YAHOO.haloacl.togglePanel = function(panelid){
    var element = $('content_'+panelid);
    var button = $('exp-collapse-button_'+panelid);
    if(element.visible()){
        button.removeClassName('haloacl_panel_button_collapse');
        button.addClassName('haloacl_panel_button_expand');
        element.hide();
    }else{
        button.addClassName('haloacl_panel_button_collapse');
        button.removeClassName('haloacl_panel_button_expand');
        element.show();
    }
};

YAHOO.haloacl.removePanel = function(panelid,callback){
    YAHOO.haloacl.notification.createDialogYesNo("content","Confirm delete/reset","All entered data in this right will get lost",{
        yes:function(){
            var element = $(panelid);
            element.remove();
            if(callback != null){
                callback();
            }
        },
        no:function(){}
    },"Ok","Cancel");
};
YAHOO.haloacl.closePanel = function(panelid){
    var element = $('content_'+panelid);
    var button = $('exp-collapse-button_'+panelid);
    button.removeClassName('haloacl_panel_button_collapse');
    button.addClassName('haloacl_panel_button_expand');
    element.hide();
};

/* RIGHT PANEL STUFF */

YAHOO.haloacl.buildRightPanelTabView = function(containerName, predefine, readOnly, preload, preloadRightId){

    
    YAHOO.haloacl.haloaclRightPanelTabs = new YAHOO.widget.TabView(containerName);
    var parameterlist = {
        panelid:containerName,
        predefine:predefine,
        readOnly:readOnly,
        preload:preload,
        preloadRightId:preloadRightId
    };



    myLabel = gLanguage.getMessage('selectDeselect');
    if (!readOnly) {
        selectActive = true;
        selectDisabled = false;
        assActive = false;
    } else {
        selectActive = false;
        selectDisabled = false;
        assActive = true;
    }

    var tab1 = new YAHOO.widget.Tab({
        label: myLabel,
        dataSrc:'rightPanelSelectDeselectTab',
        cacheData:false,
        active:selectActive,
        disabled:selectDisabled,
        postData:parameterlist
    });
    tab1._dataConnect = YAHOO.haloacl.tabDataConnect;
    YAHOO.haloacl.haloaclRightPanelTabs.addTab(tab1);
    tab1.addListener('click', function(e){});
    //$(tab1.get('contentEl')).style.display = 'none';
    $(tab1.get('contentEl')).setAttribute('id','rightPanelSelectDeselectTab'+containerName);
 
    var tab2 = new YAHOO.widget.Tab({
        label: gLanguage.getMessage('assigned'),
        dataSrc:'rightPanelAssignedTab',
        cacheData:false,
        active:assActive,
        postData:parameterlist
    });

    tab2._dataConnect = YAHOO.haloacl.tabDataConnect;
    YAHOO.haloacl.haloaclRightPanelTabs.addTab(tab2);
    tab2.addListener('click', function(e){});
    $(tab2.get('contentEl')).setAttribute('id','rightPanelAssignedTab'+containerName);

    

// ------

};

// --- handling global arrays for selection of users and groups

YAHOO.haloacl.removeUserFromUserArray = function(panelid,name,deletable){
    if(YAHOO.haloacl.debug) console.log("deletable-type:"+deletable);
    if(YAHOO.haloacl.debug) console.log("array before deletion");
    if(YAHOO.haloacl.debug) console.log(YAHOO.haloacl.clickedArrayUsers[panelid]);
    var elementToRemove = 0;
    for(i=0;i<YAHOO.haloacl.clickedArrayUsers[panelid].length;i++){
        if(YAHOO.haloacl.clickedArrayUsers[panelid][i] == name){
            elementToRemove = i;
        }
    }
    YAHOO.haloacl.clickedArrayUsers[panelid].splice(elementToRemove,1);

    var element = $(panelid+"assigned"+name);
    if(deletable == "user"){
        try{
            element.parentNode.parentNode.parentNode.hide();
        }
        catch(e){
            if(YAHOO.haloacl.debug) console.log("hiding element failed");
            if(YAHOO.haloacl.debug) console.log(e);
        }
    }else{
        deletable == "groupuser"
    }{
        try{
            element.hide();
        //element.parentNode.parentNode.parentNode.hide();
        }
        catch(e){
            if(YAHOO.haloacl.debug) console.log(e);
        }
    }
    if(YAHOO.haloacl.debug) console.log("array after deletion");
    if(YAHOO.haloacl.debug) console.log(YAHOO.haloacl.clickedArrayUsers[panelid]);


    var fncname = "YAHOO.haloacl.refreshPanel_"+panelid.substr(14)+"();";
    eval(fncname);


};

YAHOO.haloacl.addUserToUserArray = function(panelid, name){
    if(name.length > 4){

        if (!YAHOO.haloacl.clickedArrayUsers[panelid]){
            YAHOO.haloacl.clickedArrayUsers[panelid] = new Array();
        }
        if(YAHOO.haloacl.debug) console.log("adding user "+name+" to "+panelid+"-array");
        var alreadyContained = false;
        for(i=0;i<YAHOO.haloacl.clickedArrayUsers[panelid].length;i++){
            if(YAHOO.haloacl.clickedArrayUsers[panelid][i] == name){
                alreadyContained = true;
                if(YAHOO.haloacl.debug) console.log("found element - not creating new entry");
            }
        }
        if(!alreadyContained){
            YAHOO.haloacl.clickedArrayUsers[panelid].push(name);
        }
    }else{
        if(YAHOO.haloacl.debug) console.log("to short username added - skipping");
    }

    if(YAHOO.haloacl.debug) console.log(":::"+YAHOO.haloacl.clickedArrayUsers[panelid]);


};

YAHOO.haloacl.addGroupToGroupArray = function(panelid, name){
    if(name.length > 4){
        if(YAHOO.haloacl.debug) console.log("adding group "+name+" to "+panelid+"-array");
        var alreadyContained = false;
        for(i=0;i<YAHOO.haloacl.clickedArrayGroups[panelid].length;i++){
            if(YAHOO.haloacl.clickedArrayGroups[panelid][i] == name){
                alreadyContained = true;
                if(YAHOO.haloacl.debug) console.log("found element - not creating new entry");
            }
        }
        if(!alreadyContained){
            YAHOO.haloacl.clickedArrayGroups[panelid].push(name);
        }
    }else{
        if(YAHOO.haloacl.debug) console.log("to short groupname added - skipping");
    }
};

YAHOO.haloacl.getGroupsArray = function (panelid){
    return YAHOO.haloacl.clickedArrayGroups[panelid];
};

YAHOO.haloacl.removeGroupFromGroupArray = function(panelid,name){
    if(YAHOO.haloacl.debug) console.log("trying to remove "+name+" from "+panelid+"-array");
    var elementToRemove = 0;
    for(i=0;i<YAHOO.haloacl.clickedArrayGroups[panelid].length;i++){
        if(YAHOO.haloacl.clickedArrayGroups[panelid][i] == name){
            elementToRemove = i;
            if(YAHOO.haloacl.debug) console.log("found element");
        }
    }
    YAHOO.haloacl.clickedArrayGroups[panelid].splice(elementToRemove,1);
};

YAHOO.haloacl.isNameInGroupArray = function(panelid, name){
    /*
    for(i=0;i<YAHOO.haloacl.clickedArrayGroups[panelid].length;i++){
        if(YAHOO.haloacl.clickedArrayGroups[panelid][i] == name){
            return true;
        }
    }
    return false;
    */
    if(YAHOO.haloacl.clickedArrayGroups[panelid].indexOf(name) == -1){
        return false;
    }
    return true;
};

YAHOO.haloacl.isNameInUsersGroupsArray = function(panelid, name){
    for(i=0;i<YAHOO.haloacl.clickedArrayGroups[panelid].length;i++){
        if(YAHOO.haloacl.clickedArrayGroups[panelid][i] == name){
            return true;
        }
    }
    return false;

};






YAHOO.haloacl.isNameInUserArray = function(panelid, name){
    for(i=0;i<YAHOO.haloacl.clickedArrayUsers[panelid].length;i++){
        if(YAHOO.haloacl.clickedArrayUsers[panelid][i] == name){
            return true;
        }
    }
    return false;

};

YAHOO.haloacl.hasGroupsOrUsers = function(panelid){
    if(YAHOO.haloacl.debug) console.log("testing "+panelid);
    if (((YAHOO.haloacl.clickedArrayGroups[panelid]) && (YAHOO.haloacl.clickedArrayGroups[panelid].length > 0)) || (YAHOO.haloacl.clickedArrayUsers[panelid] && (YAHOO.haloacl.clickedArrayUsers[panelid].length > 0))) {
        if(YAHOO.haloacl.debug) console.log("available");
        return true;
    } else {
        if(YAHOO.haloacl.debug) console.log("not available");
        return false;
    }

};

YAHOO.haloacl.buildGroupPanelTabView = function(containerName, predefine, readOnly, preload, preloadRightId){
    YAHOO.haloacl.haloaclRightPanelTabs = new YAHOO.widget.TabView(containerName);
    var parameterlist = {
        panelid:containerName,
        predefine:predefine,
        readOnly:readOnly,
        preload:preload,
        preloadRightId:preloadRightId
    };

    //if (!readOnly) {

    var tab1 = new YAHOO.widget.Tab({
        label: gLanguage.getMessage('selectDeselect'),
        dataSrc:'rightPanelSelectDeselectTab',
        cacheData:false,
        active:true,
        postData:parameterlist
    });
    tab1._dataConnect = YAHOO.haloacl.tabDataConnect;
    YAHOO.haloacl.haloaclRightPanelTabs.addTab(tab1);
    tab1.addListener('click', function(e){});
    $(tab1.get('contentEl')).setAttribute('id','rightPanelSelectDeselectTab'+containerName);
    //}


    // ------

    var tab2 = new YAHOO.widget.Tab({
        label: gLanguage.getMessage('assigned'),
        dataSrc:'rightPanelAssignedTab',
        cacheData:false,
        active:false,
        postData:parameterlist
    });

    tab2._dataConnect = YAHOO.haloacl.tabDataConnect;
    YAHOO.haloacl.haloaclRightPanelTabs.addTab(tab2);
    tab2.addListener('click', function(e){});
    $(tab2.get('contentEl')).setAttribute('id','rightPanelAssignedTab'+containerName);



// ------


};



YAHOO.haloacl.callbackDeleteSD = function(result){
    if(result.status == '200'){
        alert(result.responseText);
    }else{
        alert(result.responseText);
    }
};

YAHOO.haloacl.deleteSD = function(sdId){
    YAHOO.haloacl.callAction('deleteSecurityDescriptor', {
        sdId:sdId
    }, YAHOO.haloacl.callbackDeleteSD);

};

YAHOO.haloacl.removeHighlighting = function(){
    $$('.highlighted').each(function(item){
        $(item).removeClassName("highlighted");
    });
};


YAHOO.haloaclrights.popup = function(id, label, anchorId){

    /*
    if(YAHOO.haloaclrights.popupPanel == null){

        YAHOO.haloaclrights.popupPanel = new YAHOO.widget.Panel('popup_'+id,{
            close:true,
            visible:true,
            draggable:true,
            resizable:true,
            context:  ["anchorPopup_"+id,"tl","bl", ["beforeShow"]]
        });
        popupClose = function(type, args) {
            //YAHOO.haloaclrights.popupPanel.destroy();
        }
        YAHOO.haloaclrights.popupPanel.subscribe("hide", popupClose);
    }

    YAHOO.haloaclrights.popupPanel.setHeader(label);
    YAHOO.haloaclrights.popupPanel.setBody('<div id="popup_content_'+id+'">');
    YAHOO.haloaclrights.popupPanel.render();
    YAHOO.haloaclrights.popupPanel.show();
*/
    if (!anchorId) {
        anchorId = id;
    }

    var myPopup = new YAHOO.widget.Panel('popup_'+anchorId,{
        close:true,
        visible:true,
        draggable:true,
        resizable:true,
        zIndex :10,
        context:  ["anchorPopup_"+anchorId,"tl","bl", ["beforeShow"]]
    });
    popupClose = function(type, args) {
    //YAHOO.haloaclrights.popupPanel.destroy();
    }
    myPopup.subscribe("hide", popupClose);

    myPopup.setHeader('<div class="haloacl_infobutton"></div>'+'ACL:'+label);
    myPopup.setBody('<div id="popup_content_'+id+'">');
    myPopup.render();
    myPopup.show();
    YAHOO.haloacl.loadContentToDiv('popup_content_'+id,'getSDRightsPanel',{
        sdId:id,
        readOnly:'true'
    });

};


YAHOO.haloacl.addTooltip = function(name, context, text){
    new YAHOO.widget.Tooltip(name, {
        context:context,
        text:text,
        zIndex :10
    });
}

