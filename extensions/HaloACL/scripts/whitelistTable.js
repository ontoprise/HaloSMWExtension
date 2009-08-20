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

/**
 *  @param  target-div-id
 *
 */


YAHOO.haloacl.whitelistTable = function(divid,panelid) {

    // custom defined formatter
    this.mySelectFormatter = function(elLiner, oRecord, oColumn, oData) {
        var checkedFromTree = false;
        var groupsstring = ""+oRecord._oData.groups;

        if(oData == true || checkedFromTree == true){
            elLiner.innerHTML = "<input type='checkbox' groups='"+groupsstring+"' checked='' class='"+divid+"_users' name='"+oRecord._oData.name+"' />";
        }else{
            elLiner.innerHTML = "<input type='checkbox' groups='"+groupsstring+"' class='"+divid+"_users' name='"+oRecord._oData.name+"' />";
        }
            
    };
    

    this.myNameFormatter = function(elLiner, oRecord, oColumn, oData) {
        elLiner.innerHTML = "<span class='"+divid+"_usersgroups' groups=\""+oRecord._oData.groups+"\">"+oRecord._oData.name+"</span>";

    };

    // building shortcut for custom formatter
    YAHOO.widget.DataTable.Formatter.mySelect = this.mySelectFormatter;
    YAHOO.widget.DataTable.Formatter.myName = this.myNameFormatter;

    var myColumnDefs = [ // sortable:true enables sorting

    {
        key:"name",
        label:"Name",
        sortable:false,
        formatter:"myName"
    },
   
    {
        key:"checked",
        label:"delete",
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

        var filter = $('datatable_filter_'+myDataTable.panelid).value;
        
        return "rs=getUsersForUserTable&rsargs[]="
        +myDataTable.query+"&rsargs[]="+sort
        +"&rsargs[]="+dir
        +"&rsargs[]="+startIndex
        +"&rsargs[]="+results
        +"&rsargs[]="+filter;


    };



    var setupCheckboxHandling = function(){
        //console.log("checkAllSelectesUsers fired");
        $$('.datatableDiv_'+panelid+'_users').each(function(item){
            //console.log("found element");
            //console.log(item.name);
            for(i=0;i<YAHOO.haloacl.clickedArrayUsers[panelid].length;i++){
                if(YAHOO.haloacl.clickedArrayUsers[panelid][i] == item.name){
                    item.checked = true;
                }
            }

        });

    };


    var handlePagination = function(state){
        console.log(state);
        // TODO!!!
        var divid = "right_tabview_create_acl_right_0";

        console.log("changeRequest fired");
        var displaying = state.totalRecords - state.recordOffset;
        if(displaying > state.rowsPerPage){
            displaying = state.rowsPerPage
        };
        var to = displaying*1 + state.recordOffset*1;
        var from = state.totalRecords > 0 ? (state.recordOffset*1+1) : 0;
        
        var html = from + " to " + to   + " from " +state.totalRecords;
        $('datatablepaging_count_'+divid).innerHTML = html;
        console.log($('datatablepaging_count_'+divid));
    };


    var myPaginator = new YAHOO.widget.Paginator({
        rowsPerPage:5,
        containers:'datatablepaging_'+divid
    });

    myPaginator.subscribe("changeRequest",handlePagination);

  

    // userdatatable configuration
    var myConfigs = {
        initialRequest: "rs=getWhitelistPages&rsargs[]=all&rsargs[]=name&rsargs[]=asc&rsargs[]=0&rsargs[]=5&rsargs[]=", // Initial request for first page of data
        dynamicData: true, // Enables dynamic server-driven data
        sortedBy : {
            key:"name",
            dir:YAHOO.widget.DataTable.CLASS_ASC
        }, // Sets UI initial sort arrow
        paginator: myPaginator,
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
        handlePagination(myPaginator.getState());
    });
    
    myDataTable.subscribe("postRenderEvent",function(){
        setupCheckboxHandling();
    });

    myDataTable.subscribe("postRenderEvent",function(){
        YAHOO.haloacl.highlightAlreadySelectedUsersInDatatable(panelid);
    });





    //YAHOO.util.Event.addListener(myDataTable,"initEvent",myDataTable.checkAllSelectedUsers());

    // function called from grouptree to update userdatatable on GroupTreeClick
    myDataTable.executeQuery = function(query){
        if(!query == ""){
            myDataTable.query = query;
        }
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

// --------------------
// --------------------
// --------------------



