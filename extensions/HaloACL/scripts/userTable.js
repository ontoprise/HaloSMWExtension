var usersGroups = [];

/**
 *  @param  target-div-id
 *
 */
YAHOO.haloacl.userDataTable = function(divid) {

    // custom defined formatter
    this.myCustomFormatter = function(elLiner, oRecord, oColumn, oData) {
        if(oData == true){
            elLiner.innerHTML = "<input type='checkbox' checked='' class='"+divid+"_users' name='"+oRecord._oData.name+"' />";
        }else{
            elLiner.innerHTML = "<input type='checkbox' class='"+divid+"_users' name='"+oRecord._oData.name+"' />";
        }
            
    };

    // building shortcut for custom formatter
    YAHOO.widget.DataTable.Formatter.myCustom = this.myCustomFormatter;

    var myColumnDefs = [ // sortable:true enables sorting
    {
        key:"id",
        label:"ID",
        sortable:true
    },
    {
        key:"name",
        label:"Name",
        sortable:true
    },
    {
        key:"checked",
        label:"Selected",
        formatter:"myCustom"
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


/*
 * get users with groups they're attached to
 * this array is used for displaying each users groups in the user table
 * @param node
 * @parm callback on complete
 */
YAHOO.haloacl.getUsersWithGroups = function()  {

    //prepare our callback object
    var callback = {

        success: function(oResponse) {
            usersGroups = YAHOO.lang.JSON.parse(oResponse.responseText);
            alert(usersGroups);
        },

        failure: function(oResponse) {
        },
        argument: {

        },
        timeout: 7000
    };

    YAHOO.haloacl.treeviewDataConnect('getUsersWithGroups',{},callback);

};

YAHOO.haloacl.getUsersWithGroups();