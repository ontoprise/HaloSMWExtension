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


YAHOO.haloacl.userDataTable = function(divid,panelid) {

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
    this.myGroupFormatter = function(elLiner, oRecord, oColumn, oData) {
        var groupsstring = ""+oRecord._oData.groups;
        var groupsarray = groupsstring.split(",");
        var resultstring = "<div class='yui-dt-liner datatable-groups-col-div'>";
        
        for (i=0;i<groupsarray.length;i++){
            var element = ""+groupsarray[i];
            try{
                element = element.trim();
            }
            catch(e){
                console.log(e);
            }
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
        sortable:false
    },
    {
        key:"name",
        label:"Name",
        sortable:false,
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
            for(i=0;i<YAHOO.haloacl.clickedArrayUsers[panelid].length;i++){
                if(YAHOO.haloacl.clickedArrayUsers[panelid][i] == item.name){
                    item.checked = true;
                }
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
        YAHOO.haloacl.highlightAlreadySelectedUsersInDatatable(panelid);
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

// --------------------
// --------------------
// --------------------

// ASSIGNED USERTABLE FROM JSARRAY
YAHOO.haloacl.ROuserDataTableV2 = function(divid,panelid){
    console.log("ROuserDataTableV2 called");
    var groupstring = "";
    var grouparray = YAHOO.haloacl.getGroupsArray(panelid);
    grouparray.each(function(item){
        if(groupstring == ""){
            groupstring = item;
        }else{
            groupstring += ","+item;
        }
    });
    console.log("retrieving user for following groups");
    console.log(groupstring);
    console.log("---");

    var callback = function(data){
        var result = new Array();
        if(data != null){
            var usersFromGroupsArray = YAHOO.lang.JSON.parse(data.responseText);

            // also adding users from group-selection - so all members of a selected group will also be shown
            usersFromGroupsArray.each(function(item){
                var temp = new Array();
                temp['name'] = item.name;
                temp["groups"] = item.groups;
                temp["deleteable"] = false;
                result.push(temp);
            });
        }

        // handling users form user-datatable on select and deselct tab
        console.log("panelid"+panelid);
        if(YAHOO.haloacl.clickedArrayUsers[panelid]){
            YAHOO.haloacl.clickedArrayUsers[panelid].each(function(item){
                // lets see if this users already exists in the datatabel
                var reallyAddUser = true;
                result.each(function(el){
                    if(el.name == item){
                        reallyAddUser = false;
                    }
                });

                if(reallyAddUser){
                    var temp = new Array();
                    temp['name'] = item;
                    temp['groups'] = YAHOO.haloacl.clickedArrayUsersGroups[panelid][item];
                    temp['deleteable'] = true;
                    result.push(temp);
                }
                
            });
        };

        return YAHOO.haloacl.ROuserDataTable(divid,panelid,result);
    };


    var action = "getUsersForGroups";
    var querystring = "rs="+action+"&rsargs[]="+groupstring;

    new Ajax.Request("?action=ajax",{
        method:'post',
        onSuccess:callback,
        onFailure:callback,
        parameters:querystring
    });

   
};
  
YAHOO.haloacl.ROuserDataTable = function(divid,panelid,dataarray) {

    // custom defined formatter
    this.mySelectFormatter = function(elLiner, oRecord, oColumn, oData) {
        if(oRecord._oData.deleteable == true){
            elLiner.innerHTML = "<a id='"+panelid+"assigned"+oRecord._oData.name+"' class='removebutton' href=\"javascript:YAHOO.haloacl.removeUserFromUserArray('"+panelid+"','"+oRecord._oData.name+"');\">&nbsp;</a>";
        }else{
            elLiner.innerHTML = "&nbsp;";
        }

    };

    this.myGroupFormatter = function(elLiner, oRecord, oColumn, oData) {
        var groupsstring = ""+oRecord._oData.groups;
        var groupsarray = groupsstring.split(",");
        var resultstring = "<div class='yui-dt-liner datatable-groups-col-div'>";

        for (i=0;i<groupsarray.length;i++){
            var element = ""+groupsarray[i];
            try{
                element = element.trim();
            }
            catch(e){
                console.log(e);
            }
            if(element != ""){
                resultstring = resultstring+"<span class='"+divid+"_usersgroups datatable_usergroups' name=\""+groupsarray[i]+"\">"+groupsarray[i]+"</span>, &nbsp; ";
            }
        }

        resultstring = resultstring +"</div>";
        elLiner.innerHTML = resultstring;

    };


    // building shortcut for custom formatter
    YAHOO.widget.DataTable.Formatter.myGroup = this.myGroupFormatter;
    YAHOO.widget.DataTable.Formatter.myName = this.myNameFormatter;
    YAHOO.widget.DataTable.Formatter.mySelect = this.mySelectFormatter;

    var myColumnDefs = [ // sortable:true enables sorting
   
    {
        key:"name",
        label:"Name",
        sortable:false
    },
    {
        key:"groups",
        label:"Groups",
        sortable:false,
        formatter:"myGroup"
    },
    
    {
        key:"deleteable",
        label:"Remove",
        formatter:"mySelect"
    },

    ];

    // datasource for this userdatatable
    var myDataSource = new YAHOO.util.DataSource(dataarray
        );
    myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
    // userdatatable configuration
    var myConfigs = {
        sortedBy : {
            key:"name",
            dir:YAHOO.widget.DataTable.CLASS_ASC
        }
    };

    // instanciating datatable
    var myDataTable = new YAHOO.widget.DataTable(divid, myColumnDefs, myDataSource, myConfigs);

    myDataTable.panelid = panelid;
    myDataTable.subscribe("postRenderEvent",function(){
        YAHOO.haloacl.highlightAlreadySelectedUsersInRODatatable(panelid);
    });


    // setting up clickevent-handling
    return myDataTable;

};



// handles
// standard part (select deselect tab)
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
        if(YAHOO.haloacl.isNameInGroupArray(panelid,name)){
            $(item).addClassName("groupselected");
        }
    });
};


// readnonly-part (assigned tab)
YAHOO.haloacl.highlightAlreadySelectedUsersInRODatatable = function(panelid){
    //console.log("autoselectevent fired for panel:"+panelid);
    //console.log("searching for users in following class:"+'.datatableDiv_'+panelid+'_users');
    //console.log("listing known selections for panel:");


    $$('.ROdatatableDiv_'+panelid+'_usersgroups').each(function(item){
        $(item).removeClassName("groupselected");
    });

    
    $$('.ROdatatableDiv_'+panelid+'_usersgroups').each(function(item){
        var name = $(item).readAttribute("name");
        //console.log("checking for name:"+name);
        if(YAHOO.haloacl.isNameInGroupArray(panelid,name)){
            $(item).addClassName("groupselected");
        }
    });
 
};



