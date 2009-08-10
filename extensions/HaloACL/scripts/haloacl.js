// Globals
YAHOO.namespace("haloacl");
YAHOO.namespace ("haloacl.constants");
YAHOO.namespace ("haloacl.settings");

YAHOO.haloacl.panelcouner = 0;
YAHOO.haloacl.clickedArray = new Array();

// Tabview related stuff

// building the main tabview
YAHOO.haloacl.buildMainTabView = function(containerName){
    YAHOO.haloacl.haloaclTabs = new YAHOO.widget.TabView(containerName);

    var tab1 = new YAHOO.widget.Tab({
        label: 'Create ACL',
        dataSrc:'createACLPanels',
        cacheData:false,
        active:true
    });
    tab1._dataConnect = YAHOO.haloacl.tabDataConnect;
    YAHOO.haloacl.haloaclTabs.addTab(tab1);
    tab1.addListener('click', function(e){});
    $(tab1.get('contentEl')).setAttribute('id','creataclTab');


    // ------

    var tab2 = new YAHOO.widget.Tab({
        label: 'Manage ACLs',
        dataSrc:'manageAclsContent',
        cacheData:false,
        active:false
    });
    tab2._dataConnect = YAHOO.haloacl.tabDataConnect;
    YAHOO.haloacl.haloaclTabs.addTab(tab2);
    tab2.addListener('click', function(e){});
    $(tab2.get('contentEl')).setAttribute('id','manageaclmainTab');

    // ------

    var tab3 = new YAHOO.widget.Tab({
        label: 'Manage User',
        dataSrc:'manageUserContent',
        cacheData:false,
        active:false
    });
    tab3._dataConnect = YAHOO.haloacl.tabDataConnect;
    YAHOO.haloacl.haloaclTabs.addTab(tab3);
    tab3.addListener('click', function(e){});
    $(tab1.get('contentEl')).setAttribute('id','manageuserTab');

    // ------

    var tab4 = new YAHOO.widget.Tab({
        label: 'Whitelists',
        dataSrc:'whitelistsContent',
        cacheData:false,
        active:false
    });
    tab4._dataConnect = YAHOO.haloacl.tabDataConnect;
    YAHOO.haloacl.haloaclTabs.addTab(tab4);
    tab4.addListener('click', function(e){});
    $(tab1.get('contentEl')).setAttribute('id','whitelistsTab');

// ------

};


// building the main tabview
YAHOO.haloacl.buildSubTabView = function(containerName){
    YAHOO.haloacl.haloaclTabs = new YAHOO.widget.TabView(containerName);

    var tab1 = new YAHOO.widget.Tab({
        label: 'Create standard ACL',
        dataSrc:'createAclContent',
        cacheData:false,
        active:true,
        id:"createStdAclTab"
    });
    tab1._dataConnect = YAHOO.haloacl.tabDataConnect;
    YAHOO.haloacl.haloaclTabs.addTab(tab1);
    tab1.addListener('click', function(e){});


    // ------

    var tab2 = new YAHOO.widget.Tab({
        label: 'Create ACL template',
        dataSrc:'manageAclsContent',
        cacheData:false,
        active:false,
        id:"createTmpAclTab"
    });
    tab2._dataConnect = YAHOO.haloacl.tabDataConnect;
    YAHOO.haloacl.haloaclTabs.addTab(tab2);
    tab2.addListener('click', function(e){});

    // ------

    var tab3 = new YAHOO.widget.Tab({
        label: 'Create ACL default user template',
        dataSrc:'manageUserContent',
        cacheData:false,
        active:false,
        id:"createUserAclTab"
    });
    tab3._dataConnect = YAHOO.haloacl.tabDataConnect;
    YAHOO.haloacl.haloaclTabs.addTab(tab3);
    tab3.addListener('click', function(e){});


// ------

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
        },
        onFailure: function(o) {
        }
    });
};


YAHOO.haloacl.sendXmlToAction = function(xml, action,callback){
    if(callback == null){
        callback = function(result){
            alert("stdcallback:"+result);
        }
    }
    var querystring = "rs="+action+"&rsargs[]="+escape(xml);

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

YAHOO.haloacl.removePanel = function(panelid){
    var element = $(panelid);
    element.remove();
};
YAHOO.haloacl.closePanel = function(panelid){
    var element = $('content_'+panelid);
    var button = $('exp-collapse-button_'+panelid);
    button.removeClassName('haloacl_panel_button_collapse');
    button.addClassName('haloacl_panel_button_expand');
    element.hide();
};

/* RIGHT PANEL STUFF */

YAHOO.haloacl.buildRightPanelTabView = function(containerName){
    YAHOO.haloacl.haloaclRightPanelTabs = new YAHOO.widget.TabView(containerName);
    var parameterlist = {
        panelid:containerName
    };
    
    var tab1 = new YAHOO.widget.Tab({
        label: 'Select / Deselect',
        dataSrc:'rightPanelSelectDeselectTab',
        cacheData:false,
        active:true,
        postData:parameterlist
    });
    tab1._dataConnect = YAHOO.haloacl.tabDataConnect;
    YAHOO.haloacl.haloaclRightPanelTabs.addTab(tab1);
    tab1.addListener('click', function(e){});
    $(tab1.get('contentEl')).setAttribute('id','rightPanelSelectDeselectTab'+containerName);


    // ------

    var tab2 = new YAHOO.widget.Tab({
        label: 'Assigned',
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

