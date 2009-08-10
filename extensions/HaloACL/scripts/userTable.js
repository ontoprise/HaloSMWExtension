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
            elLiner.innerHTML = "<input groups=\""+oRecord._oData.groups+"\" type='checkbox' checked='' class='"+divid+"_users' name='"+oRecord._oData.name+"' />";
        }else{
            elLiner.innerHTML = "<input groups=\""+oRecord._oData.groups+"\" type='checkbox' class='"+divid+"_users' name='"+oRecord._oData.name+"' />";
        }
            
    };
    this.myGroupFormatter = function(elLiner, oRecord, oColumn, oData) {
        elLiner.innerHTML = "<span style=\"font-size:8px\">"+oRecord._oData.groups+"</span>";

    };

    this.myNameFormatter = function(elLiner, oRecord, oColumn, oData) {
        elLiner.innerHTML = "<span class='"+divid+"_usersnames' groups=\""+oRecord._oData.groups+"\">"+oRecord._oData.name+"</span>";

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
        sortable:false,
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

    // userdatatable configuration
    var myConfigs = {
        initialRequest: "rs=getUsersForUserTable&rsargs[]=test&rsargs[]=name&rsargs[]=asc&rsargs[]=0&rsargs[]=25", // Initial request for first page of data
        dynamicData: true, // Enables dynamic server-driven data
        sortedBy : {
            key:"id",
            dir:YAHOO.widget.DataTable.CLASS_ASC
        }, // Sets UI initial sort arrow
        paginator: new YAHOO.widget.Paginator({ 
            rowsPerPage:25,
            containers:'datatablepaging_'+divid
        }),
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

    return myDataTable;

};

YAHOO.haloacl.checkAlreadySelectedUsersInDatatable = function(panelid){
    //console.log("autoselectevent fired for panel:"+panelid);
    //console.log("searching for users in following class:"+'.datatableDiv_'+panelid+'_users');
    //console.log("listing known selections for panel:");


    $$('.datatableDiv_'+panelid+'_usersnames').each(function(item){
        $(item).removeClassName("groupselected");
    });

    $$('.datatableDiv_'+panelid+'_usersnames').each(function(item){
        var groupstring = ""+$(item).readAttribute("groups");
        //console.log("looking for groups:"+groupstring);
        var grouparray = groupstring.split(",");

        for(i=0;i<grouparray.length;i++){
            var grouparraytemp = grouparray[i];
            if(grouparraytemp != ""){
                //console.log("temp:"+grouparraytemp);
                if(YAHOO.haloacl.clickedArray[panelid][grouparraytemp]){
                    $(item).addClassName("groupselected");
                }
            }
        }

    });

//console.log("elements in checked array for panel");
//for(i=0;i<YAHOO.haloacl.clickedArray[panelid].length;i++){
//    console.log(YAHOO.haloacl.clickedArray[panelid][i]);
//}
};

