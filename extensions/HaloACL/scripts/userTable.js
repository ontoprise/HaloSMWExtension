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
        var resultstring = "<div name='"+oRecord._oData.name+"' groups='"+groupsstring+"' class='haloacl_datatable_groupscol  datatable_usergroups haloacl_datatable_groupdiv"+this.panelid+"'></div>";
        elLiner.innerHTML = resultstring;
    };

    this.myNameFormatter = function(elLiner, oRecord, oColumn, oData) {
        //elLiner.innerHTML = "<span class='"+divid+"_usersgroups' groups=\""+oRecord._oData.groups+"\">"+oRecord._oData.name+"</span>";
        elLiner.innerHTML = "<span  class='userdatatable_name' groups=\""+oRecord._oData.groups+"\">"+oRecord._oData.name+"</span>";

    };

    // building shortcut for custom formatter
    YAHOO.widget.DataTable.Formatter.mySelect = this.mySelectFormatter;
    YAHOO.widget.DataTable.Formatter.myGroup = this.myGroupFormatter;
    YAHOO.widget.DataTable.Formatter.myName = this.myNameFormatter;

    var myColumnDefs = [ // sortable:true enables sorting
 
    {
        key:"name",
        label:gLanguage.getMessage('name'),
        sortable:false,
        formatter:"myName"
    },
    {
        key:"groups",
        label:gLanguage.getMessage('groups'),
        sortable:false
        ,
        formatter:"myGroup"
    },
    {
        key:"checked",
        label:gLanguage.getMessage('selected'),
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

        var filter = $('datatable_filter_'+myDataTable.panelid).value;
        
        return "rs=getUsersForUserTable&rsargs[]="
        +myDataTable.query+"&rsargs[]="+sort
        +"&rsargs[]="+dir
        +"&rsargs[]="+startIndex
        +"&rsargs[]="+results
        +"&rsargs[]="+filter;


    };



    var setupCheckboxHandling = function(){
        //if(YAHOO.haloacl.debug) console.log("checkAllSelectesUsers fired");
        if(YAHOO.haloacl.debug) console.log(YAHOO.haloacl.clickedArrayUsers);
        $$('.datatableDiv_'+panelid+'_users').each(function(item){
            //if(YAHOO.haloacl.debug) console.log("found element");
            //if(YAHOO.haloacl.debug) console.log(item.name);
            for(i=0;i<YAHOO.haloacl.clickedArrayUsers[panelid].length;i++){
                if(YAHOO.haloacl.clickedArrayUsers[panelid][i] == item.name){
                    item.checked = true;
                }
            }

        });

    };


    var handlePagination = function(state){

        //var divid = myPaginator._containers.parentNode.id;
        if(YAHOO.haloacl.debug) console.log("should be:"+"right_tabview_create_acl_right_0");
        //if(YAHOO.haloacl.debug) console.log("is:"+divid);
        
        var divid = myPaginator._containers[0].parentNode.children[0].children[0].children[0].id;

        if(YAHOO.haloacl.debug) console.log("changeRequest fired");
        var displaying = state.totalRecords - state.recordOffset;
        if(displaying > state.rowsPerPage){
            displaying = state.rowsPerPage
        };
        var to = displaying*1 + state.recordOffset*1;
        var from = state.totalRecords > 0 ? (state.recordOffset*1+1) : 0;
        var html = from + "<span style='font-weight:normal'>"+" - "+ "</span> "+ to+ "<span style='font-weight:normal'> "    +gLanguage.getMessage('from') + "&nbsp;</span>" +state.totalRecords+" <span style='font-weight:normal'>in</span> Users";

//        var html = from + " " +gLanguage.getMessage('from')+ " " + to   + " " +gLanguage.getMessage('to')+ " " +state.totalRecords;
        $(divid).innerHTML = html;
        if(YAHOO.haloacl.debug) console.log($('datatablepaging_count_'+divid));
    };


    var myPaginator = new YAHOO.widget.Paginator({
        rowsPerPage:10,
        containers:'datatablepaging_'+divid
    });

    myPaginator.subscribe("changeRequest",handlePagination);

  

    // userdatatable configuration
    var myConfigs = {
        initialRequest: "rs=getUsersForUserTable&rsargs[]=all&rsargs[]=name&rsargs[]=asc&rsargs[]=0&rsargs[]=5&rsargs[]=", // Initial request for first page of data
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

// ASSIGNED USERTABLE FROM JSARRAY
YAHOO.haloacl.ROuserDataTableV2 = function(divid,panelid){
    if(YAHOO.haloacl.debug) console.log("ROuserDataTableV2 called");
    var groupstring = "";
    var grouparray = YAHOO.haloacl.getGroupsArray(panelid);
    grouparray.each(function(item){
        if(groupstring == ""){
            groupstring = item;
        }else{
            groupstring += ","+item;
        }
    });
    if(YAHOO.haloacl.debug) console.log("retrieving user for following groups");
    if(YAHOO.haloacl.debug) console.log(groupstring);
    if(YAHOO.haloacl.debug) console.log("---");

    var callback = function(data){
        var result = new Array();
        if(data != null){
            var usersFromGroupsArray = YAHOO.lang.JSON.parse(data.responseText);

            // also adding users from group-selection - so all members of a selected group will also be shown
            usersFromGroupsArray.each(function(item){
                var temp = new Array();
                temp['name'] = item.name;
                temp["groups"] = item.groups;
                temp["deletable"] = "group";
                result.push(temp);
            });
        }

        // handling users form user-datatable on select and deselct tab
        if(YAHOO.haloacl.debug) console.log("panelid"+panelid);
        if(YAHOO.haloacl.clickedArrayUsers[panelid]){
            YAHOO.haloacl.clickedArrayUsers[panelid].each(function(item){
                // lets see if this users already exists in the datatabel
                var reallyAddUser = "user";
              
                result.each(function(el){
                    if(el.name == item){
                        if(el.deletable == "group"){
                            reallyAddUser = "groupuser";
                        }else if(el.deletable == "groupuser"){
                            reallyAddUser = "no";
                        }else if(el.deletable == "user"){
                            reallyAddUser = "groupuser";
                        }
                        
                        // remove it from array, as its added later again with other deletable tag
                        var elementToRemove = null;
                        for(i=0;i<result.length;i++){
                            if(result[i] == el.name){
                                elementToRemove = i;
                            }
                        }
                        result.splice(elementToRemove,1);
                       
                    }
                });

                if(reallyAddUser == "user"){
                    var temp = new Array();
                    temp['name'] = item;
                    temp['groups'] = YAHOO.haloacl.clickedArrayUsersGroups[panelid][item];
                    temp['deletable'] = "user";
                    result.push(temp);
                }else if(reallyAddUser == "groupuser"){
                    var temp = new Array();
                    temp['name'] = item;
                    temp['groups'] = YAHOO.haloacl.clickedArrayUsersGroups[panelid][item];
                    temp['deletable'] = "groupuser";
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


// this userdatatable is called from V2 !!!
YAHOO.haloacl.ROuserDataTable = function(divid,panelid,dataarray) {

    // custom defined formatter
    this.mySelectFormatter = function(elLiner, oRecord, oColumn, oData) {
        if(oRecord._oData.deletable !="group"){
            elLiner.innerHTML = "<a id='"+panelid+"assigned"+oRecord._oData.name+"' class='removebutton' href=\"javascript:YAHOO.haloacl.removeUserFromUserArray('"+panelid+"','"+oRecord._oData.name+"','"+oRecord._oData.deletable+"');\">&nbsp;</a>";
        }else{
            elLiner.innerHTML = "&nbsp;";
        }

    };

    this.myGroupFormatter = function(elLiner, oRecord, oColumn, oData) {
        var groupsstring = ""+oRecord._oData.groups;
        var resultstring = "<div name='"+oRecord._oData.name+"' groups='"+groupsstring+"' class='haloacl_datatable_groupscol  datatable_usergroups haloacl_datatable_groupdiv"+panelid+"'></div>";
        elLiner.innerHTML = resultstring;
    };
    this.myNameFormatter = function(elLiner, oRecord, oColumn, oData) {
        //elLiner.innerHTML = "<span class='"+divid+"_usersgroups' groups=\""+oRecord._oData.groups+"\">"+oRecord._oData.name+"</span>";
        elLiner.innerHTML = "<span  class='userdatatable_name' groups=\""+oRecord._oData.groups+"\">"+oRecord._oData.name+"</span>";

    };

    // building shortcut for custom formatter
    YAHOO.widget.DataTable.Formatter.myGroup = this.myGroupFormatter;
    YAHOO.widget.DataTable.Formatter.myName = this.myNameFormatter;
    YAHOO.widget.DataTable.Formatter.mySelect = this.mySelectFormatter;

    var myColumnDefs = [ // sortable:true enables sorting
   
    {
        key:"name",
        label:gLanguage.getMessage('name'),
        sortable:false,
        formatter:"myName"
    },
    {
        key:"groups",
        label:gLanguage.getMessage('groups'),
        sortable:false,
        formatter:"myGroup"
    },
    
    {
        key:"deletable",
        label:gLanguage.getMessage('remove'),
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
    //if(YAHOO.haloacl.debug) console.log("autoselectevent fired for panel:"+panelid);
    //if(YAHOO.haloacl.debug) console.log("searching for users in following class:"+'.datatableDiv_'+panelid+'_users');
    //if(YAHOO.haloacl.debug) console.log("listing known selections for panel:");
    
    /* non sorted part */
    /*
    
    $$('.datatableDiv_'+panelid+'_usersgroups').each(function(item){
        $(item).removeClassName("groupselected");
    });

    $$('.datatableDiv_'+panelid+'_usersgroups').each(function(item){
        var name = $(item).readAttribute("name");
        //if(YAHOO.haloacl.debug) console.log("checking for name:"+name);
        if(YAHOO.haloacl.isNameInGroupArray(panelid,name)){
            $(item).addClassName("groupselected");
        }
    });
     */

    /* non sorted end */
    $$('.haloacl_datatable_groupdiv'+panelid).each(function(divitem){
        if(YAHOO.haloacl.debug) console.log("processing divitem:");
        if(YAHOO.haloacl.debug) console.log(divitem);
        
        var highlighted = new Array();
        var nonHighlighted = new Array();
        var groupsarray = $(divitem).readAttribute("groups").split(",");
        if(YAHOO.haloacl.debug) console.log("got groupsarray:");
        if(YAHOO.haloacl.debug) console.log(groupsarray);

        for(i=0;i<groupsarray.length;i++){
            var item = groupsarray[i];
            if(item != ""){
                if(YAHOO.haloacl.isNameInGroupArray(panelid,item)){
                    highlighted.push(item);
                }else{
                    nonHighlighted.push(item);
                }
            }
        }

        var result = "<div class='haloacl_usertable_groupsrow_before_tooltip' style='float:left'>";
        for(i=0;i<highlighted.length;i++){
            result += "<span class='groupselected'>";
            result+= ""+highlighted[i];
            result+="</span>&nbsp;";
        }
        for(i=0;i<nonHighlighted.length;i++){
            result +="<span class='groupunselected'>";
            result+= ""+nonHighlighted[i];
            result+="</span>&nbsp;";
        }
        result +="</div>";

        var divname = $(divitem).readAttribute("name");
        
        //var innerhtml =result+ '<div class="haloacl_infobutton" style="float:left;display:inline"></div><div id="tt1'+panelid+divname+'"></div>';
        var innerhtml =result+ '<div id="tt1'+panelid+divname+'"></div>';


        divitem.innerHTML = innerhtml;
        
        var test = new YAHOO.widget.Tooltip('tt1'+panelid+divname, {
            context:divitem,
            text:result,
            zIndex :10,
            constraintoviewport:false
        });
        if(YAHOO.haloacl.debug) console.log(test);




    });


};


// readnonly-part (assigned tab)
YAHOO.haloacl.highlightAlreadySelectedUsersInRODatatable = function(panelid){
    //if(YAHOO.haloacl.debug) console.log("autoselectevent fired for panel:"+panelid);
    //if(YAHOO.haloacl.debug) console.log("searching for users in following class:"+'.datatableDiv_'+panelid+'_users');
    //if(YAHOO.haloacl.debug) console.log("listing known selections for panel:");

    /*
    $$('.ROdatatableDiv_'+panelid+'_usersgroups').each(function(item){
        $(item).removeClassName("groupselected");
    });

    
    $$('.ROdatatableDiv_'+panelid+'_usersgroups').each(function(item){
        var name = $(item).readAttribute("name");
        //if(YAHOO.haloacl.debug) console.log("checking for name:"+name);
        if(YAHOO.haloacl.isNameInGroupArray(panelid,name)){
            $(item).addClassName("groupselected");
        }
    });
  */

    $$('.haloacl_datatable_groupdiv'+panelid).each(function(divitem){
        if(YAHOO.haloacl.debug) console.log("processing divitem:");
        if(YAHOO.haloacl.debug) console.log(divitem);

        var highlighted = new Array();
        var nonHighlighted = new Array();
        var groupsarray = $(divitem).readAttribute("groups").split(",");
        if(YAHOO.haloacl.debug) console.log("got groupsarray:");
        if(YAHOO.haloacl.debug) console.log(groupsarray);

        for(i=0;i<groupsarray.length;i++){
            var item = groupsarray[i];
            if(item != ""){
                if(YAHOO.haloacl.isNameInGroupArray(panelid,item)){
                    highlighted.push(item);
                }else{
                    nonHighlighted.push(item);
                }
            }
        }

        var result = "<div class='haloacl_usertable_groupsrow_before_tooltip' style='float:left'>";
        for(i=0;i<highlighted.length;i++){
            result += "<span class='groupselected'>";
            result+= ""+highlighted[i];
            result+="</span>&nbsp;";
        }
        for(i=0;i<nonHighlighted.length;i++){
            result +="<span class='groupunselected'>";
            result+= ""+nonHighlighted[i];
            result+="</span>&nbsp;";
        }
        result +="</div>";

        var divname = $(divitem).readAttribute("name");

        //var innerhtml =result+ '<div class="haloacl_infobutton" style="float:left;display:inline"></div><div id="tt1'+panelid+divname+'"></div>';
        var innerhtml =result+ '<div id="tt1'+panelid+divname+'"></div>';


        divitem.innerHTML = innerhtml;

        var test = new YAHOO.widget.Tooltip('tt1'+panelid+divname, {
            context:divitem,
            text:result,
            zIndex :10,
            constraintoviewport:false
        });
        if(YAHOO.haloacl.debug) console.log(test);




    });
 
};



