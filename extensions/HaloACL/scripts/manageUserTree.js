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

//var clickedTreeNodes = [];


// defining customnode
YAHOO.widget.ManageUserNode = function(oData, oParent, expanded, checked) {
    YAHOO.widget.ManageUserNode.superclass.constructor.call(this,oData,oParent,expanded);
    this.setUpCheck(checked || oData.checked);

};

// impl of customnode; extending textnode
YAHOO.extend(YAHOO.widget.ManageUserNode, YAHOO.widget.TextNode, {

    /**
     * True if checkstate is 1 (some children checked) or 2 (all children checked),
     * false if 0.
     * @type boolean
     */
    checked: false,

    /**
     * checkState
     * 0=unchecked, 1=some children checked, 2=all children checked
     * @type int
     */
    checkState: 0,

    /**
     * id of contained acl group
     * @type int
     */
    groupId: 0,


    /**
     * tree type
     * rw=read/write, r=read
     * @type string
     */
    treeType: "rw",

    /**
     * The node type
     * @property _type
     * @private
     * @type string
     * @default "TextNode"
     */
    _type: "CustomNode",

    customNodeParentChange: function() {
    //this.updateParent();
    },

    // function called from constructor
    //  -> creates/registers events
    setUpCheck: function(checked) {
        // if this node is checked by default, run the check code to update
        // the parent's display state
        if (checked && checked === true) {
            this.check();
        // otherwise the parent needs to be updated only if its checkstate
        // needs to change from fully selected to partially selected
        } else if (this.parent && 2 === this.parent.checkState) {
            this.updateParent();
        }

        // set up the custom event on the tree for checkClick

        if (this.tree && !this.tree.hasEvent("checkClick")) {
            this.tree.createEvent("checkClick", this.tree);
        }
        this.tree.subscribe('clickEvent',this.checkClick);
				
        this.subscribe("parentChange", this.customNodeParentChange);
       
    },


    /**
     * set group id
     * @newGroupId int
     */
    setGroupId: function(newGroupId) {
        this.groupId = newGroupId;
    },

    /**
     * get group id
     */
    getGroupId: function() {
        return this.groupId;
    },

    /**
     * The id of the check element
     * @for YAHOO.widget.CustomNode
     * @type string
     */
    getCheckElId: function() { 
        return "ygtvcheck" + this.index; 
    },

    /**
     * Returns the check box element
     * @return the check html element (img)
     */
    getCheckEl: function() { 
        return document.getElementById(this.getCheckElId()); 
    },

    /**
     * The style of the check element, derived from its current state
     * @return {string} the css style for the current check state
     */
    getCheckStyle: function() { 
        return "ygtvcheck" + this.checkState;
    },


    /**
     * Invoked when the user clicks the check box
     */
    checkClick: function(oArgs) {
        var node = oArgs.node;
        var target = YAHOO.util.Event.getTarget(oArgs.event);
        if (YAHOO.util.Dom.hasClass(target,'ygtvspacer')) {
            if (node.checkState === 0) {
                node.check();
            } else {
                node.uncheck();
            }

            node.onCheckClick(node);
            this.fireEvent("checkClick", node);
            return false;
        }

    },

    


    /**
     * Override to get the check click event
     */
    onCheckClick: function() { 
    },

    /**
     * Refresh the state of this node's parent, and cascade up.
     */
    updateParent: function() { 
        var p = this.parent;

        if (!p || !p.updateParent) {
            return;
        }

        var somethingChecked = false;
        var somethingNotChecked = false;

        for (var i=0, l=p.children.length;i<l;i=i+1) {

            var n = p.children[i];

            if ("checked" in n) {
                if (n.checked) {
                    somethingChecked = true;
                    // checkState will be 1 if the child node has unchecked children
                    if (n.checkState === 1) {
                        somethingNotChecked = true;
                    }
                } else {
                    somethingNotChecked = true;
                }
            }
        }

        if (somethingChecked) {
            p.setCheckState( (somethingNotChecked) ? 1 : 2 );
        } else {
            p.setCheckState(0);
        }

        p.updateCheckHtml();
        p.updateParent();
    },

    /**
     * If the node has been rendered, update the html to reflect the current
     * state of the node.
     */
    updateCheckHtml: function() { 
        if (this.parent && this.parent.childrenRendered) {
            this.getCheckEl().className = this.getCheckStyle();
        }
    },

    /**
     * Updates the state.  The checked property is true if the state is 1 or 2
     * 
     * @param the new check state
     */
    setCheckState: function(state) { 
        this.checkState = state;
        this.checked = (state > 0);
             
    },

    /**
     * Updates the state.  The checked property is true if the state is 1 or 2
     *
     * @param the new check state
     */
    getLabelElId: function() {
        return this.labelElId;
    },

    /**
     * Check this node
     */
    check: function() {
        this.setCheckState(2);
        for (var i=0, l=this.children.length; i<l; i=i+1) {
            var c = this.children[i];
            if (c.check) {
                c.check();
            }
        }
        this.updateCheckHtml();
        this.updateParent();
    },

    /**
     * Uncheck this node
     */
    uncheck: function() { 
        this.setCheckState(0);
        for (var i=0, l=this.children.length; i<l; i=i+1) {
            var c = this.children[i];
            if (c.uncheck) {
                c.uncheck();
            }
        }
        this.updateCheckHtml();
        this.updateParent();
    },
    
    setTreeType: function(newTreeType) { 
        this.treeType = newTreeType
    },


    // Overrides YAHOO.widget.TextNode
    getContentHtml: function() {                                                                                                                                           
        var sb = [];


        sb[sb.length] = '<td><span';
        sb[sb.length] = ' id="manageUserRow_' + this.label + '"';
        if (this.title) {
            sb[sb.length] = ' title="' + this.title + '"';
        }
        sb[sb.length] = ' class="haloacl_manageuser_list_title ' + this.labelStyle  + '"';
        sb[sb.length] = ' >';
        sb[sb.length] = "<a href='javascript:"+this.tree.labelClickAction+"(\""+this.label+"\");'>"+this.label+"</a>";

        sb[sb.length] = '</span></td>';
        sb[sb.length] = '<td><span class="haloacl_manageuser_list_information">Information</span></td>';
        sb[sb.length] = '<td><span class=""><a class="haloacl_manageuser_list_edit" href="javascript:YAHOO.haloacl.manageUsers_handleEdit(\''+this.label+'\');">&nbsp;</a></span></td>';
        // sb[sb.length] = '<td><span class="haloacl_manageuser_list_delete">delete</span></td>';
        sb[sb.length] = '<td';
        sb[sb.length] = ' id="' + this.getCheckElId() + '"';
        sb[sb.length] = ' class="' + this.getCheckStyle() + '"';
        sb[sb.length] = '>';
        sb[sb.length] = '<div class="ygtvspacer"></div></td>';



        
        return sb.join("");                                                                                                                                                
    }  
});



/*
 * treeview-dataconnect
 * @param mediawiki / rs-action
 * @param list (object) of parameters to be added
 * @param callback for asyncRequest
 */
YAHOO.haloacl.manageUser.treeviewDataConnect = function(action,parameterlist,callback){
    var url= "?action=ajax";
    var appendedParams = '';
    appendedParams = '&rs='+action;
    var temparray = new Array();
    for(param in parameterlist){
        temparray.push(parameterlist[param]);
    }
    appendedParams = appendedParams + "&rsargs="+ temparray;
    YAHOO.util.Connect.asyncRequest('POST', url, callback,appendedParams);
};

/*
 * function for dynamic node-loading
 * @param node
 * @parm callback on complete
 */
YAHOO.haloacl.manageUser.loadNodeData = function(node, fnLoadComplete)  {

    var nodeLabel = encodeURI(node.label);


    //prepare our callback object
    var callback = {
        panelid:"",

        //if our XHR call is successful, we want to make use
        //of the returned data and create child nodes.
        success: function(oResponse) {
            YAHOO.haloacl.manageUser.buildNodesFromData(node,YAHOO.lang.JSON.parse(oResponse.responseText,panelid));
            oResponse.argument.fnLoadComplete();
        },

        failure: function(oResponse) {
            oResponse.argument.fnLoadComplete();
        },
        argument: {
            "node": node,
            "fnLoadComplete": fnLoadComplete
        },
        timeout: 7000
    };
    YAHOO.haloacl.manageUser.treeviewDataConnect('getGroupsForManageUser',{
        query:nodeLabel
    },callback);

};





/*
 * function to build nodes from data
 * @param parent node / root
 * @param data
 */
YAHOO.haloacl.manageUser.buildNodesFromData = function(parentNode,data,panelid){

    for(var i= 0, len = data.length; i<len; ++i){
        var element = data[i];
        var tmpNode = new YAHOO.widget.ManageUserNode(element.name, parentNode,false);
        tmpNode.setGroupId(element.name);

        
    };

};


/*
 * filter tree
 * @param parent node / root
 * @param filter String
 */
YAHOO.haloacl.manageUser.filterNodes = function(parentNode,filter){

    filter = filter.toLowerCase();
    
    var nodes;
    nodes = parentNode.children;

    for(var i=0, l=nodes.length; i<l; i=i+1) {
        var n = nodes[i];
        var temp = n.label;
        temp = temp.toLowerCase();
        if (temp.indexOf(filter) < 0) {
            document.getElementById(n.getLabelElId()).parentNode.parentNode.style.display = "none";
        } else {
            document.getElementById(n.getLabelElId()).parentNode.parentNode.style.display = "inline";
        }
        
    /*
        if (n.checkState > 0) {
            var tmpNode = new YAHOO.widget.CustomNode(n.label, rwTree.getRoot(),false);
            tmpNode.setCheckState(n.checkState);
            tmpNode.setTreeType("r");
        }
        */

    }

};

/*
 * function to build user tree and add labelClickAction
 * @param tree
 * @param data
 * @param labelClickAction (name)
 */
YAHOO.haloacl.manageUser.buildUserTree = function(tree,data) {

    YAHOO.haloacl.manageUser.buildNodesFromData(tree.getRoot(),data,tree.panelid);

    //using custom loadNodeDataHandler
    var loadNodeData = function(node, fnLoadComplete)  {
        var nodeLabel = encodeURI(node.label);
        //prepare our callback object
        var callback = {
            panelid:"",
            success: function(oResponse) {
                YAHOO.haloacl.manageUser.buildNodesFromData(node,YAHOO.lang.JSON.parse(oResponse.responseText,tree.panelid));
                oResponse.argument.fnLoadComplete();
            },
            failure: function(oResponse) {
                oResponse.argument.fnLoadComplete();
            },
            argument: {
                "node": node,
                "fnLoadComplete": fnLoadComplete
            },
            timeout: 7000
        };
        YAHOO.haloacl.manageUser.treeviewDataConnect('getGroupsForManageUser',{
            query:nodeLabel
        },callback);

    };



    tree.setDynamicLoad(loadNodeData);
    tree.draw();

};




/*
 * function to be called from outside to init a tree
 * @param tree-instance
 */
YAHOO.haloacl.manageUser.buildTreeFirstLevelFromJson = function(tree){
    var callback = {
        success: function(oResponse) {
            var data = YAHOO.lang.JSON.parse(oResponse.responseText);
            YAHOO.haloacl.manageUser.buildUserTree(tree,data);
        },
        failure: function(oResponse) {
        }
    };
    YAHOO.haloacl.manageUser.treeviewDataConnect('getGroupsForManageUser',{
        query:'all'
    },callback);
};



/**
 * returns a new treeinstance
 */
YAHOO.haloacl.getNewManageUserTree = function(divname,panelid){
    console.log("first");
    var instance = new YAHOO.widget.TreeView(divname);
    instance.panelid = panelid;
    console.log("here");
   
    return instance;
};

YAHOO.haloacl.manageUser.findGroup = function(parentNode,query){
    var nodes;
    nodes = parentNode.children;
    for(var i=0, l=nodes.length; i<l; i=i+1) {
        var n = nodes[i];
        var temp = n.label;
        if (temp.indexOf(query) >= 0) {
            YAHOO.haloacl.manageUser_parentGroup = parentNode.label;
            return parentNode;
        }
        if(n.hasChildren(false) == true){
            var recfound = YAHOO.haloacl.manageUser.findGroupAndReturnParent(n,query);
            if(recfound != null){
                YAHOO.haloacl.manageUser_parentGroup = n.label;
                return n;
            }
        }

    }
}

YAHOO.haloacl.manageUser.addNewSubgroupOnSameLevel = function(tree,groupname){
    var nodeToAttachTo = YAHOO.haloacl.manageUser.findGroup(tree,groupname);
    console.log(nodeToAttachTo);
    var tmpNode = new YAHOO.widget.ManageUserNode("new subgroup", nodeToAttachTo,false);
    nodeToAttachTo.refresh();

};
YAHOO.haloacl.manageUser.findGroupAndReturnParent = function(parentNode,query){
    var nodes;
    nodes = parentNode.children;
    for(var i=0, l=nodes.length; i<l; i=i+1) {
        var n = nodes[i];
        var temp = n.label;
        if (temp.indexOf(query) >= 0) {
            YAHOO.haloacl.manageUser_parentGroup = n.label;
            return n;
        }
        if(n.hasChildren(false) == true){
            var recfound = YAHOO.haloacl.manageUser.findGroupAndReturnParent(n,query);
            if(recfound != null){
                YAHOO.haloacl.manageUser_parentGroup = recfound.label;
                return recfound;
            }
        }

    }
}

YAHOO.haloacl.manageUser.addNewSubgroup = function(tree,groupname){
    var nodeToAttachTo = YAHOO.haloacl.manageUser.findGroupAndReturnParent(tree,groupname);
    console.log(nodeToAttachTo);
    var tmpNode = new YAHOO.widget.ManageUserNode("new subgroup", nodeToAttachTo,false);
    // turn of dynamic load on that node
    tmpNode.setDynamicLoad();
    nodeToAttachTo.expand();
    nodeToAttachTo.refresh();


};