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





// defining ACLNode
YAHOO.widget.ACLNode = function(oData, oParent, expanded, checked) {
    YAHOO.widget.ACLNode.superclass.constructor.call(this,oData,oParent,expanded);
    this.setUpCheck(checked || oData.checked);

};

// impl of customnode; extending textnode
YAHOO.extend(YAHOO.widget.ACLNode, YAHOO.widget.TextNode, {

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
        if(YAHOO.haloacl.debug) console.log(oArgs);
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
        //this.tree.clickedTreeNodes[this.groupId] = this.checked;
        // this.tree.clickedHandler.add(this.groupId);
        //YAHOO.haloacl.clickedArrayGroups[this.tree.panelid][this.groupId] = this.checked;
        if(this.checked){
            YAHOO.haloacl.addGroupToGroupArray(this.tree.panelid, this.groupId);
        }else{
            YAHOO.haloacl.removeGroupFromGroupArray(this.tree.panelid, this.groupId);
        }
    // update usertable
    // YAHOO.haloacl.highlightAlreadySelectedUsersInDatatable(this.tree.panelid);

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
        YAHOO.haloacl.selectedTemplates[this.tree.panelid] = this.label;
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

        if (this.treeType=="readOnly") {
        
            sb[sb.length] = '<td><span';
            sb[sb.length] = ' id="' + this.labelElId + '"';
            sb[sb.length] = ' class="haloacl_manageuser_list_title ' + this.labelStyle  + '"';

            if (this.title) {
                sb[sb.length] = ' title="' + this.title + '"';
            }
            sb[sb.length] = ' class="' + this.labelStyle  + '"';
            sb[sb.length] = ' >';
            sb[sb.length] = "<a href='javascript:"+this.tree.labelClickAction+"(\""+this.label+"\");'>"+this.label+"</a>";
            

            sb[sb.length] = '</span></td>';

            sb[sb.length] = '<td><span class="haloacl_manageuser_list_information">';
            sb[sb.length] = '</span></td>';


            sb[sb.length] = '<td><div id="anchorPopup_'+this.groupId+'" class="haloacl_infobutton" onclick="javascript:YAHOO.haloaclrights.popup(\''+this.groupId+'\',\''+this.label+'\');return false;"></div>';
            sb[sb.length] = '<div id="popup_'+this.groupId+'"></div>';

            sb[sb.length] = '</td>';

            sb[sb.length] = "<a href='javascript:"+this.tree.labelClickAction+"(\""+this.label+"\");'></a>";

      

            sb[sb.length] = '<td';
            sb[sb.length] = ' id="' + this.getCheckElId() + '"';
            sb[sb.length] = ' class="' + this.getCheckStyle() + '"';
            sb[sb.length] = '>';
            sb[sb.length] = '<div class="ygtvspacer"></div></td>';

        } else {
            //  sb[sb.length] = '<td>';
            //  sb[sb.length] = '<div class="ygtvspacer"></div></td>';



            sb[sb.length] = '<td><span';
            sb[sb.length] = ' id="manageUserRow_' + this.label + '"';
            if (this.title) {
                sb[sb.length] = ' title="' + this.title + '"';
            }
            sb[sb.length] = ' class="haloacl_manageuser_list_title ' + this.labelStyle  + '"';
            sb[sb.length] = ' >';
            sb[sb.length] = "<a href='javascript:"+this.tree.labelClickAction+"(\""+this.label+"\");'>"+this.label+"</a>";

            sb[sb.length] = '</span></td>';
            sb[sb.length] = '<td><span class="haloacl_manageuser_list_information"><div id="anchorPopup_'+this.groupId+'" class="haloacl_manageright_list_edit" onclick="javascript:YAHOO.haloaclrights.popup(\''+this.groupId+'\',\''+this.label+'\');return false;"></div><div id="popup_'+this.groupId+'"></div></span></td>';
            sb[sb.length] = '<td><span class=""><a class="haloacl_manageuser_list_edit" href="javascript:YAHOO.haloacl.loadContentToDiv(\'ManageACLDetail\',\'getSDRightsPanelContainer\',{sdId:\''+this.groupId+'\',sdName:\''+this.label+'\',readOnly:\'false\'});">&nbsp;</a></span></td>';
            // sb[sb.length] = '<td><span class="haloacl_manageuser_list_delete">delete</span></td>';
            sb[sb.length] = '<td';
            sb[sb.length] = ' id="' + this.getCheckElId() + '"';
            sb[sb.length] = ' class="' + this.getCheckStyle() + '"';
            sb[sb.length] = '>';
            sb[sb.length] = '<div class="ygtvspacer"></div></td>';



        /*

            sb[sb.length] = '<td><span';
            sb[sb.length] = ' id="' + this.labelElId + '"';
            sb[sb.length] = ' class="haloacl_manageuser_list_title ' + this.labelStyle  + '"';

            if (this.title) {
                sb[sb.length] = ' title="' + this.title + '"';
            }
            sb[sb.length] = ' class="' + this.labelStyle  + '"';
            sb[sb.length] = ' >'+this.label;
            // sb[sb.length] = '<a href="javascript:YAHOO.haloacl.loadContentToDiv(\'ManageACLDetail\',\'getSDRightsPanelContainer\',{sdId:\''+this.groupId+'\',sdName:\''+this.label+'\',readOnly:\'false\'})">'+this.label+"</a>";

            sb[sb.length] = '</span></td>';

            sb[sb.length] = '<td><span class="haloacl_manageuser_list_information">&nbsp;</span></td>';
            
            sb[sb.length] = '<td><span class="">';
            //<a class="haloacl_manageuser_list_edit" href="javascript:YAHOO.haloacl.manageUsers_handleEdit(\''+this.label+'\');">&nbsp;</a></span></td>';
            sb[sb.length] = '<a class="haloacl_manageuser_list_edit" href="javascript:YAHOO.haloacl.loadContentToDiv(\'ManageACLDetail\',\'getSDRightsPanelContainer\',{sdId:\''+this.groupId+'\',sdName:\''+this.label+'\',readOnly:\'false\'})">&nbsp;'+"</a></span>";
            sb[sb.length] = "</td>";

            sb[sb.length] = '<td';
            sb[sb.length] = ' id="' + this.getCheckElId() + '"';
            //            sb[sb.length] = ' class="ygtvcheck3"';
            sb[sb.length] = ' class="' + this.getCheckStyle() + '"';

            sb[sb.length] = '>';
            sb[sb.length] = '<div class="ygtvspacer"></div></td>';
*/
        }


        return sb.join("");
    }
});




// defining RightNode
YAHOO.widget.RightNode = function(oData, oParent, expanded, checked) {
    YAHOO.widget.RightNode.superclass.constructor.call(this,oData,oParent,expanded);
    this.setUpCheck(checked || oData.checked);

};

// impl of customnode; extending textnode
YAHOO.extend(YAHOO.widget.RightNode, YAHOO.widget.TextNode, {

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
    title:'',


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
    tt1:YAHOO.widget.Tooltip,

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
        //this.tree.clickedTreeNodes[this.groupId] = this.checked;
        // this.tree.clickedHandler.add(this.groupId);
        //YAHOO.haloacl.clickedArrayGroups[this.tree.panelid][this.groupId] = this.checked;
        if(this.checked){
            YAHOO.haloacl.addGroupToGroupArray(this.tree.panelid, this.groupId);
        }else{
            YAHOO.haloacl.removeGroupFromGroupArray(this.tree.panelid, this.groupId);
        }
        // update usertable
        YAHOO.haloacl.highlightAlreadySelectedUsersInDatatable(this.tree.panelid);

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

        var shortLabel = "";
        if (this.label.length > 25) {
            shortLabel = "..."+this.label.substring(this.label.length-25,this.label.length);
        } else {
            shortLabel = this.label;
        }

        if (this.treeType=="rw") {

            sb[sb.length] = '<td><div class="ygtvspacer"></div></td>';

            sb[sb.length] = '<td><span class="haloacl_manageACL_right_title">'+this.title+'</span></td>';

            sb[sb.length] = '<td><span';
            sb[sb.length] = ' id="' + this.labelElId + '"';
            if (this.title) {
                sb[sb.length] = ' title="' + this.title + '"';
            }
            sb[sb.length] = ' class="haloacl_manageACL_right_description"';
            sb[sb.length] = ' >';
            //sb[sb.length] = shortLabel;
            sb[sb.length] = '<div id="tt1'+this.labelElId+'" class="haloacl_infobutton" ></div>';
            this.tt1 = new YAHOO.widget.Tooltip("tt1"+this.labelElId, { 
                context:this.labelElId,
                text:this.label,
                zIndex :10
            });

            
            sb[sb.length] = '</span></td>';


        } else {
            sb[sb.length] = '<td>';
            sb[sb.length] = '<div class="ygtvspacer"></div></td>';

            sb[sb.length] = '<td><span';
            sb[sb.length] = ' id="' + this.labelElId + '"';
            if (this.title) {
                sb[sb.length] = ' title="' + this.title + '"';
            }
            sb[sb.length] = ' class="' + this.labelStyle  + '"';
            sb[sb.length] = ' >';
            sb[sb.length] = "<a href='javascript:"+this.tree.labelClickAction+"(\""+this.label+"\");'>"+this.label+"</a>";

            sb[sb.length] = '</span></td>';

            sb[sb.length] = '<td';
            sb[sb.length] = ' id="' + this.getCheckElId() + '"';
            sb[sb.length] = ' class="ygtvcheck3"';
            sb[sb.length] = '>';
            sb[sb.length] = '<div class="ygtvspacer"></div></td>';


        }


        return sb.join("");
    }
});






/*
 * treeview-dataconnect
 * @param mediawiki / rs-action
 * @param list (object) of parameters to be added
 * @param callback for asyncRequest
 */
YAHOO.haloaclrights.treeviewDataConnect = function(action,parameterlist,callback){
    var url= "?action=ajax";
    var appendedParams = '';
    appendedParams = '&rs='+action;
    var temparray = new Array();
    for(param in parameterlist){
        temparray.push(parameterlist[param]);
    }
    appendedParams = appendedParams + "&rsargs[]="+ temparray;


    var filterControl = $('haloacl_manageuser_contentmenu');

    if(filterControl != null){
        var xml = "<?xml version=\"1.0\"  encoding=\"UTF-8\"?>";
        xml+="<types>";
        $$('.haloacl_manageacl_filter').each(function(item){
            if(item.checked){
                xml += "<type>"+item.name+"</type>";
            }
        });
        xml+="</types>";
        
        appendedParams = '&rs='+action+"&rsargs[]="+escape(xml);
    }
    YAHOO.util.Connect.asyncRequest('POST', url, callback,appendedParams);
};

/*
 * function for dynamic node-loading
 * @param node
 * @parm callback on complete
 */
YAHOO.haloaclrights.loadNodeData = function(node, fnLoadComplete)  {

    var nodeLabel = encodeURI(node.label);


    //prepare our callback object
    var callback = {
        panelid:"",

        //if our XHR call is successful, we want to make use
        //of the returned data and create child nodes.
        success: function(oResponse) {
            YAHOO.haloaclrights.buildNodesFromData(node,YAHOO.lang.JSON.parse(oResponse.responseText,panelid));
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
    YAHOO.haloaclrights.treeviewDataConnect('getGroupsForRightPanel',{
        query:nodeLabel
    },callback);

};





/*
 * function to build nodes from data
 * @param parent node / root
 * @param data
 */
YAHOO.haloaclrights.buildNodesFromData = function(parentNode,data,panelid){

    //build ACL nodes
    for(var i= 0, len = data.length; i<len; ++i){
        
        var element = data[i];

        if (!element.name) element.name = gLanguage.getMessage('NoName');
        var tmpNode = new YAHOO.widget.ACLNode(element.name, parentNode, false);

        tmpNode.setGroupId(element.id);
        tmpNode.setTreeType(tmpNode.tree.type);

        //build right subnodes
        if(YAHOO.haloacl.debug) console.log("rights array:"+element.rights.length+element.rights);
        if (element.rights.length > 0) {
            for(var i2= 0, len2 = element.rights.length; i2<len2; ++i2){

                var element2 = element.rights[i2];

                if (!element2.description) element2.description = gLanguage.getMessage('NoName');
                var tmpNode2 = new YAHOO.widget.RightNode(element2.description, tmpNode, false);
                tmpNode2.title = element2.name;
                tmpNode2.setGroupId(element2.id);
            };
        }

    };
};


/*
 * filter tree
 * @param parent node / root
 * @param filter String
 */
YAHOO.haloaclrights.filterNodes = function(parentNode,filter){

    var nodes;
    nodes = parentNode.children;

    for(var i=0, l=nodes.length; i<l; i=i+1) {
        var n = nodes[i];

        if (n.label.indexOf(filter) < 0) {
            document.getElementById(n.getLabelElId()).parentNode.parentNode.style.display = "none";
        } else {
            document.getElementById(n.getLabelElId()).parentNode.parentNode.style.display = "inline";
        }

    /*
        if (n.checkState > 0) {
            var tmpNode = new YAHOO.widget.ACLNode(n.label, rwTree.getRoot(),false);
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
YAHOO.haloaclrights.buildUserTree = function(tree,data) {

    YAHOO.haloaclrights.buildNodesFromData(tree.getRoot(),data,tree.panelid);

    //using custom loadNodeDataHandler
    var loadNodeData = function(node, fnLoadComplete)  {
        var nodeLabel = encodeURI(node.label);
        //prepare our callback object
        var callback = {
            panelid:"",
            success: function(oResponse) {
                YAHOO.haloaclrights.buildNodesFromData(node,YAHOO.lang.JSON.parse(oResponse.responseText,tree.panelid));
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
        YAHOO.haloaclrights.treeviewDataConnect('getGroupsForRightPanel',{
            query:nodeLabel
        },callback);

    };



    //tree.setDynamicLoad(loadNodeData);
    tree.draw();

};


/*
 * builds mirrored, read only user tree for "assigned" panel from existing r/w user tree in "select" panel
 * @param tree
 * @param rwTree
 */
YAHOO.haloaclrights.buildUserTreeRO = function(tree,rwTree) {

    var nodes;
    nodes = tree.getRoot().children;

    for(var i=0, l=nodes.length; i<l; i=i+1) {
        var n = nodes[i];

        if (n.checkState > 0) {
            var tmpNode = new YAHOO.widget.ACLNode(n.label, rwTree.getRoot(),false);
            tmpNode.setCheckState(n.checkState);
            tmpNode.setTreeType("rw");
        }

    }

    rwTree.draw();

};


/*
 * function to be called from outside to init a tree
 * @param tree-instance
 */
YAHOO.haloaclrights.buildTreeFirstLevelFromJson = function(tree){
    var callback = {
        success: function(oResponse) {
            var data = YAHOO.lang.JSON.parse(oResponse.responseText);
            YAHOO.haloaclrights.buildUserTree(tree,data);
        },
        failure: function(oResponse) {
        }
    };
    if(tree.treeType != null && tree.treeType != "readonly"){
        YAHOO.haloaclrights.treeviewDataConnect('getACLs',{
            query:'all'
        },callback);
    }else{
        var temp = escape('<?xml version="1.0" encoding="UTF-8"?><types><type>acltemplate</type></types>');
        YAHOO.haloaclrights.treeviewDataConnect('getACLs',{
            query:temp
        },callback);
    }
};

/*
 * returns checked nodes
 * USE ONE OF BOTH PARAMS, so ONE HAS TO BE NULL
 *
 * @param tree
 * @param nodes
 */

/*
YAHOO.haloaclrights.getCheckedNodesFromTree = function(tree, nodes){
    if(nodes == null){
        nodes = tree.getRoot().children;
    }
    checkedNodes = [];
    for(var i=0, l=nodes.length; i<l; i=i+1) {
        var n = nodes[i];
        //if (n.checkState > 0) { // if we were interested in the nodes that have some but not all children checked
        if (n.checkState === 2) {
            checkedNodes.push(n.label); // just using label for simplicity
        }

        if (n.hasChildren()) {
            checkedNodes = checkedNodes.concat(YAHOO.haloaclrights.getCheckedNodesFromTree(null, n.children));
        }
    }

    return checkedNodes;
};
*/



/**
 * returns a new treeinstance
 */
YAHOO.haloaclrights.getNewRightsTreeview = function(divname, panelid, type){


    var instance = new YAHOO.widget.TreeView(divname);
    instance.panelid = panelid;
    instance.type = type;
    if(!YAHOO.haloaclrights.clickedArrayGroups[panelid]){
        YAHOO.haloaclrights.clickedArrayGroups[panelid] = new Array();
    }
    return instance;
};
