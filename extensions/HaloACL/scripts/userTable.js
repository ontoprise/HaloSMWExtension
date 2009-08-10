/**
 *  @param  target-div-id
 *
 */


YAHOO.haloacl.userDataTable = function(divid,panelid) {

    // custom defined formatter
    this.mySelectFormatter = function(elLiner, oRecord, oColumn, oData) {
        var checkedFromTree = false;
        var groupsstring = ""+oRecord._oData.groups;
        var groupsarray = groupsstring.split(",");

        if(oData == true || checkedFromTree == true){
            elLiner.innerHTML = "<input type='checkbox' checked='' class='"+divid+"_users' name='"+oRecord._oData.name+"' />";
        }else{
            elLiner.innerHTML = "<input type='checkbox' class='"+divid+"_users' name='"+oRecord._oData.name+"' />";
        }
            
    };
    this.myGroupFormatter = function(elLiner, oRecord, oColumn, oData) {
        var groupsstring = ""+oRecord._oData.groups;
        var groupsarray = groupsstring.split(",");
        var resultstring = "<div class='yui-dt-liner datatable-groups-col-div'>";
        
        for (i=0;i<groupsarray.length;i++){
            var element = groupsarray[i].trim();
            if(element != ""){
                resultstring = resultstring+"<span class='"+divid+"_usersgroups datatable_usergroups' name=\""+groupsarray[i]+"\">"+groupsarray[i]+"</span>, &nbsp; ";
            }
        }

        resultstring = resultstring +"</div>";
        elLiner.innerHTML = resultstring;

    };

    this.myNameFormatter = function(elLiner, oRecord, oColumn, oData) {
        elLiner.innerHTML = "<span class='"+divid+"_usersgroups' groups=\""+oRecord._oData.groups+"\">"+oRecord._oData.name+"</span>";

    };

    // building shortcut for custom formatter
    YAHOO.widget.DataTable.Formatter.mySelect = this.mySelectFormatter;
    YAHOO.widget.DataTable.Formatter.myGroup = this.myGroupFormatter;
    YAHOO.widget.DataTable.Formatter.myName = this.myNameFormatter;

    var myColumnDefs = [ // sortable:true enables sorting
    {
        key:"id",
        label:"ID",
        sortable:true
    },
    {
        key:"name",
        label:"Name",
        sortable:true,
        formatter:"myName"
    },
    {
        key:"groups",
        label:"Groups",
        sortable:false
        ,
        formatter:"myGroup"
    },
    {
        key:"checked",
        label:"Selected",
        formatter:"mySelect"
    },

    ];

    // datasource for this userdatatable
    var myDataSource = new YAHOO.util.DataSource("?action=ajax");
    myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
    myDataSource.connMethodPost = true;
    myDataSource.responseSchema = {
        resultsList: "records",
        fields: [
        {
            key:"id",
            parser:"number"
        },
        {
            key:"name"
        },
        {
            key:"groups"
        },
        {
            key:"checked"
        },
        ],
        metaFields: {
            totalRecords: "totalRecords" // Access to value in the server response
        }
    };

    // our customrequestbuilder (attached to the datasource)
    // this requestbuilder, builds a valid mediawiki-ajax-request
    var customRequestBuilder = function(oState, oSelf) {
        // Get states or use defaults
        oState = oState;
        var totalRecords = oState.pagination.totalRecords;
        var sort = (oState.sortedBy) ? oState.sortedBy.key : null;
        var dir = (oState.sortedBy && oState.sortedBy.dir == YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";
        var startIndex = oState.pagination.recordOffset;
        var results = oState.pagination.rowsPerPage;
        /* make the initial cache of the form data */

        if(myDataTable.query == null){
            myDataTable.query = '';
        }

        return "rs=getUsersForUserTable&rsargs[]="
        +myDataTable.query+"&rsargs[]="+sort
        +"&rsargs[]="+dir
        +"&rsargs[]="+startIndex
        +"&rsargs[]="+results;


    };



    var setupCheckboxHandling = function(){
        //console.log("checkAllSelectesUsers fired");
        $$('.datatableDiv_'+panelid+'_users').each(function(item){
            //console.log("found element");
            //console.log(item.name);
            if(YAHOO.haloacl.clickedArrayUsers[panelid][item.name]){
                item.checked = true;
            }
        });

    };

    var getPaginator = function(){
        var temp = new YAHOO.widget.Paginator({
            rowsPerPage:25,
            containers:'datatablepaging_'+divid
        }); 
        return temp;
    }

    // userdatatable configuration
    var myConfigs = {
        initialRequest: "rs=getUsersForUserTable&rsargs[]=test&rsargs[]=name&rsargs[]=asc&rsargs[]=0&rsargs[]=25", // Initial request for first page of data
        dynamicData: true, // Enables dynamic server-driven data
        sortedBy : {
            key:"id",
            dir:YAHOO.widget.DataTable.CLASS_ASC
        }, // Sets UI initial sort arrow
        paginator: getPaginator(),
        generateRequest:customRequestBuilder
    };

    // instanciating datatable
    var myDataTable = new YAHOO.widget.DataTable(divid, myColumnDefs, myDataSource, myConfigs);

    // Update totalRecords on the fly with value from server
    myDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
        oPayload.totalRecords = oResponse.meta.totalRecords;
        return oPayload;
    }
    myDataTable.query = "";

    myDataTable.panelid = panelid;

    myDataTable.subscribe("postRenderEvent",function(){
        setupCheckboxHandling();
    });




    //YAHOO.util.Event.addListener(myDataTable,"initEvent",myDataTable.checkAllSelectedUsers());

    // function called from grouptree to update userdatatable on GroupTreeClick
    myDataTable.executeQuery = function(query){
        myDataTable.query = query;
        var oCallback = {
            success : myDataTable.onDataReturnInitializeTable,
            failure : myDataTable.onDataReturnInitializeTable,
            scope : myDataTable,
            argument : myDataTable.getState()
        };
        myDataSource.sendRequest(customRequestBuilder(myDataTable.getState(),null), oCallback);
    }

    // setting up clickevent-handling
    return myDataTable;

};



YAHOO.haloacl.ROuserDataTable = function(divid,panelid) {

    // custom defined formatter
    this.mySelectFormatter = function(elLiner, oRecord, oColumn, oData) {
        var checkedFromTree = false;
        var groupsstring = ""+oRecord._oData.groups;
        var groupsarray = groupsstring.split(",");

        if(oData == true || checkedFromTree == true){
            elLiner.innerHTML = "<input type='checkbox' checked='' class='"+divid+"_users' name='"+oRecord._oData.name+"' />";
        }else{
            elLiner.innerHTML = "<input type='checkbox' class='"+divid+"_users' name='"+oRecord._oData.name+"' />";
        }

    };
    this.myGroupFormatter = function(elLiner, oRecord, oColumn, oData) {
        var groupsstring = ""+oRecord._oData.groups;
        var groupsarray = groupsstring.split(",");
        var resultstring = "<div class='yui-dt-liner datatable-groups-col-div'>";

        for (i=0;i<groupsarray.length;i++){
            var element = groupsarray[i].trim();
            if(element != ""){
                resultstring = resultstring+"<span class='"+divid+"_usersgroups datatable_usergroups' name=\""+groupsarray[i]+"\">"+groupsarray[i]+"</span>, &nbsp; ";
            }
        }

        resultstring = resultstring +"</div>";
        elLiner.innerHTML = resultstring;

    };

    this.myNameFormatter = function(elLiner, oRecord, oColumn, oData) {
        elLiner.innerHTML = "<span class='"+divid+"_usersgroups' groups=\""+oRecord._oData.groups+"\">"+oRecord._oData.name+"</span>";

    };

    // building shortcut for custom formatter
    YAHOO.widget.DataTable.Formatter.mySelect = this.mySelectFormatter;
    YAHOO.widget.DataTable.Formatter.myGroup = this.myGroupFormatter;
    YAHOO.widget.DataTable.Formatter.myName = this.myNameFormatter;

    var myColumnDefs = [ // sortable:true enables sorting
    {
        key:"id",
        label:"ID",
        sortable:true
    },
    {
        key:"name",
        label:"Name",
        sortable:true,
        formatter:"myName"
    },
    {
        key:"groups",
        label:"Groups",
        sortable:false
        ,
        formatter:"myGroup"
    },
    {
        key:"checked",
        label:"Selected",
        formatter:"mySelect"
    },

    ];

    // datasource for this userdatatable
    var myDataSource = new YAHOO.util.DataSource("?action=ajax");
    myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
    myDataSource.connMethodPost = true;
    myDataSource.responseSchema = {
        resultsList: "records",
        fields: [
        {
            key:"id",
            parser:"number"
        },
        {
            key:"name"
        },
        {
            key:"groups"
        },
        {
            key:"checked"
        },
        ],
        metaFields: {
            totalRecords: "totalRecords" // Access to value in the server response
        }
    };

    // our customrequestbuilder (attached to the datasource)
    // this requestbuilder, builds a valid mediawiki-ajax-request
    var customRequestBuilder = function(oState, oSelf) {
        // Get states or use defaults
        oState = oState;
        var totalRecords = oState.pagination.totalRecords;
        var sort = (oState.sortedBy) ? oState.sortedBy.key : null;
        var dir = (oState.sortedBy && oState.sortedBy.dir == YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";
        var startIndex = oState.pagination.recordOffset;
        var results = oState.pagination.rowsPerPage;
        /* make the initial cache of the form data */

        if(myDataTable.query == null){
            myDataTable.query = '';
        }

        return "rs=getUsersForUserTable&rsargs[]="
        +myDataTable.query+"&rsargs[]="+sort
        +"&rsargs[]="+dir
        +"&rsargs[]="+startIndex
        +"&rsargs[]="+results;


    };



    var setupCheckboxHandling = function(){
        //console.log("checkAllSelectesUsers fired
        console.log("RO datatable using panelid:"+panelid);
        $$('.ROdatatableDiv_'+panelid+'_users').each(function(item){
            //console.log("found element");
            //console.log(item.name);
            if(YAHOO.haloacl.clickedArrayUsers[panelid][item.name]){
                item.checked = true;
            }
        });

    };

    var getPaginator = function(){
        var temp = new YAHOO.widget.Paginator({
            rowsPerPage:25,
            containers:'ROdatatablepaging_'+divid
        });
        return temp;
    }

    // userdatatable configuration
    var myConfigs = {
        initialRequest: "rs=getUsersForUserTable&rsargs[]=test&rsargs[]=name&rsargs[]=asc&rsargs[]=0&rsargs[]=25", // Initial request for first page of data
        dynamicData: true, // Enables dynamic server-driven data
        sortedBy : {
            key:"id",
            dir:YAHOO.widget.DataTable.CLASS_ASC
        }, // Sets UI initial sort arrow
        paginator: getPaginator(),
        generateRequest:customRequestBuilder
    };

    // instanciating datatable
    var myDataTable = new YAHOO.widget.DataTable(divid, myColumnDefs, myDataSource, myConfigs);

    // Update totalRecords on the fly with value from server
    myDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
        oPayload.totalRecords = oResponse.meta.totalRecords;
        return oPayload;
    }
    myDataTable.query = "";

    myDataTable.panelid = panelid;

    myDataTable.subscribe("postRenderEvent",function(){
        setupCheckboxHandling();
    });


    //YAHOO.util.Event.addListener(myDataTable,"initEvent",myDataTable.checkAllSelectedUsers());

    // function called from grouptree to update userdatatable on GroupTreeClick
    myDataTable.executeQuery = function(query){
        myDataTable.query = query;
        var oCallback = {
            success : myDataTable.onDataReturnInitializeTable,
            failure : myDataTable.onDataReturnInitializeTable,
            scope : myDataTable,
            argument : myDataTable.getState()
        };
        myDataSource.sendRequest(customRequestBuilder(myDataTable.getState(),null), oCallback);
    }

    // setting up clickevent-handling
    return myDataTable;

};



// handles
YAHOO.haloacl.highlightAlreadySelectedUsersInDatatable = function(panelid){
    //console.log("autoselectevent fired for panel:"+panelid);
    //console.log("searching for users in following class:"+'.datatableDiv_'+panelid+'_users');
    //console.log("listing known selections for panel:");


    $$('.datatableDiv_'+panelid+'_usersgroups').each(function(item){
        $(item).removeClassName("groupselected");
    });

    $$('.datatableDiv_'+panelid+'_usersgroups').each(function(item){
        var name = $(item).readAttribute("name");
        //console.log("checking for name:"+name);
        if(YAHOO.haloacl.clickedArrayGroups[panelid][name]){
            $(item).addClassName("groupselected");
        }
    });
};

