<?php
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
 * This file contains functions for client/server communication with Ajax.
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

$wgAjaxExportList[] = "ajaxTestFunction";
$wgAjaxExportList[] = "createACLPanels";
$wgAjaxExportList[] = "createManageACLPanels";
$wgAjaxExportList[] = "createAclContent";
$wgAjaxExportList[] = "createAclTemplateContent";
$wgAjaxExportList[] = "createRightContent";
$wgAjaxExportList[] = "createModificationRightsContent";
$wgAjaxExportList[] = "createSaveContent";
$wgAjaxExportList[] = "createManageExistingACLContent";
$wgAjaxExportList[] = "rightList";
$wgAjaxExportList[] = "manageAclsContent";
$wgAjaxExportList[] = "manageUserContent";
$wgAjaxExportList[] = "whitelistsContent";
$wgAjaxExportList[] = "getRightsPanel";
$wgAjaxExportList[] = "rightPanelSelectDeselectTab";
$wgAjaxExportList[] = "rightPanelAssignedTab";
$wgAjaxExportList[] = "getGroupsForRightPanel";
$wgAjaxExportList[] = "getUsersForUserTable";
$wgAjaxExportList[] = "saveTempRightToSession";
$wgAjaxExportList[] = "getModificationRightsPanel";
$wgAjaxExportList[] = "saveSecurityDescriptor";
$wgAjaxExportList[] = "getWhitelistPages";
$wgAjaxExportList[] = "getUsersWithGroups";
$wgAjaxExportList[] = "getUsersForGroups";
$wgAjaxExportList[] = "getGroupsForManageUser";
$wgAjaxExportList[] = "getACLs";
$wgAjaxExportList[] = "getSDRightsPanel";
$wgAjaxExportList[] = "getManageUserGroupPanel";
$wgAjaxExportList[] = "saveTempGroupToSession";
$wgAjaxExportList[] = "saveGroup";
$wgAjaxExportList[] = "getSDRightsPanelContainer";
$wgAjaxExportList[] = "deleteSecurityDescriptor";
$wgAjaxExportList[] = "createAclUserTemplateContent";
$wgAjaxExportList[] = "getRightsContainer";
$wgAjaxExportList[] = "saveWhitelist";
$wgAjaxExportList[] = "getAutocompleteDocuments";
$wgAjaxExportList[] = "createManageUserTemplateContent";
$wgAjaxExportList[] = "deleteGroups";
$wgAjaxExportList[] = "deleteWhitelist";
$wgAjaxExportList[] = "getGroupDetails";
$wgAjaxExportList[] = "createQuickAclTab";
$wgAjaxExportList[] = "getQuickACLData";
$wgAjaxExportList[] = "saveQuickacl";
$wgAjaxExportList[] = "doesArticleExists";
$wgAjaxExportList[] = "sDpopupByName";



function ajaxTestFunction() {
    $temp = new AjaxResponse();
    $temp->addText("testtext");

    return $temp;
}


function createACLPanels() {

// clear temp-right-sessions
    clearTempSessionRights();

    $response = new AjaxResponse();

    $html = <<<HTML
        <div class="yui-skin-sam">
            <input id="processType" type="hidden" value="init" />
            <div id="haloaclsubView" class="yui-navset"></div>
        </div>
        <script type="text/javascript">
            YAHOO.haloacl.buildSubTabView('haloaclsubView');
        </script>
HTML;

    $response->addText($html);
    return $response;

}


function createManageACLPanels() {

// clear temp-right-sessions
    clearTempSessionRights();

    $response = new AjaxResponse();

    $html = <<<HTML
        <div class="yui-skin-sam">
            <div id="haloaclsubViewManageACL" class="yui-navset"></div>
        </div>
        <script type="text/javascript">
            YAHOO.haloacl.buildSubTabView('haloaclsubViewManageACL');
        </script>
HTML;

    $response->addText($html);
    return $response;

}


/**
 *
 * @return <html>
 *  returns rights section content for createacl-tab
 */
function createRightContent($predefine) {

    $hacl_createRightContent_help = wfMsg('hacl_createRightContent_help');
    $hacl_haloacl_tab_section_header_title = wfMsg('hacl_haloacl_tab_section_header_title');

    $response = new AjaxResponse();
    $helpItem = new HACL_helpPopup("Right", $hacl_createRightContent_help);

    $html = <<<HTML
        <!-- section start -->
        <!-- rights section -->
        <div class="haloacl_tab_section_container">
            <div class="haloacl_tab_section_header">
                <div class="haloacl_tab_section_header_count">2.</div>
                <div class="haloacl_tab_section_header_title">
        $hacl_haloacl_tab_section_header_title
                </div>
HTML;

    $html .= $helpItem->getPanel();
    $html .= <<<HTML
        </div>
            <div id="haloacl_tab_createacl_rightsection" class="haloacl_tab_section_content">
                <div class="haloacl_tab_section_content_row">
                    <input id="haloacl_create_right_$predefine" type="button" value="Create right"
                        onclick="javascript:YAHOO.haloacl.createacl_addRightPanel('$predefine');"/>
                    &nbsp;
                    <input id="haloacl_add_right_$predefine" type="button" value="Add right template"
                        onclick="javascript:YAHOO.haloacl.createacl_addRightTemplate('$predefine');"/>
                </div>
            </div>
        </div>

        <div id="step3" style="display:none;width:600px;">
            <input id="haloacl_createacl_nextstep_$predefine" type="button" name="gotoStep3" value="Next Step"
            onclick="javascript:YAHOO.haloacl.createacl_gotoStep3();" style="margin-left:30px;"/>
        </div>

        <!-- section end -->

        <script type="javascript">

            YAHOO.haloacl.createacl_gotoStep3 = function() {
                YAHOO.haloacl.loadContentToDiv('step3','createModificationRightsContent',{panelid:1});
            }

            YAHOO.haloacl.createacl_addRightPanel = function(predefine){
                  var panelid = 'create_acl_right_'+ YAHOO.haloacl.panelcouner;
                  var divhtml = '<div id="create_acl_rights_row'+YAHOO.haloacl.panelcouner+'" class="haloacl_tab_section_content_row"></div>';
                  var containerWhereDivsAreInserted = $('haloacl_tab_createacl_rightsection');
                  $('haloacl_tab_createacl_rightsection').insert(divhtml,containerWhereDivsAreInserted);

                  YAHOO.haloacl.loadContentToDiv('create_acl_rights_row'+YAHOO.haloacl.panelcouner,'getRightsPanel',{panelid:panelid, predefine:predefine});
                  YAHOO.haloacl.panelcouner++;
            };

            YAHOO.haloacl.createacl_addRightTemplate = function(predefine){
                  var panelid = 'create_acl_right_'+ YAHOO.haloacl.panelcouner;
                  var divhtml = '<div id="create_acl_rights_row'+YAHOO.haloacl.panelcouner+'" class="haloacl_tab_section_content_row"></div>';
                  var containerWhereDivsAreInserted = $('haloacl_tab_createacl_rightsection');
                  $('haloacl_tab_createacl_rightsection').insert(divhtml,containerWhereDivsAreInserted);

                  YAHOO.haloacl.loadContentToDiv('create_acl_rights_row'+YAHOO.haloacl.panelcouner,'getRightsContainer',{panelid:panelid});
                  YAHOO.haloacl.panelcouner++;
            };

        </script>

HTML;

    //create default panels
    switch ($predefine) {
        case "private":
            $html .= <<<HTML
                <script type="javascript">
                    YAHOO.haloacl.createacl_addRightPanel("private");
                </script>
HTML;
            break;
        case "all":
            $html .= <<<HTML
                <script type="javascript">
                    YAHOO.haloacl.createacl_addRightPanel("all");
                </script>
HTML;
            break;
    }


    $response->addText($html);
    return $response;
}


function createModificationRightsContent() {


    $hacl_createModificationRightContent_help = wfMsg('hacl_createModificationRightContent_help');
    $hacl_haloacl_tab_section_header_mod_title = wfMsg('hacl_haloacl_tab_section_header_mod_title');
    $hacl_haloacl_mod_1 = wfMsg('hacl_haloacl_mod_1');
    $hacl_haloacl_mod_2 = wfMsg('hacl_haloacl_mod_2');
    $hacl_haloacl_mod_3 = wfMsg('hacl_haloacl_mod_3');

    global $wgUser;
    $currentUser = $wgUser->getName();

    $response = new AjaxResponse();

    $helpItem = new HACL_helpPopup("ModificationRight", $hacl_createModificationRightContent_help);

    $html = <<<HTML
        <!-- section start -->
        <!-- modificationrights section -->
        <div class="haloacl_tab_section_container">
            <div class="haloacl_tab_section_header">
                <div class="haloacl_tab_section_header_count">3.</div>
                <div class="haloacl_tab_section_header_title">
        $hacl_haloacl_tab_section_header_mod_title
                </div>
HTML;

    $html .= $helpItem->getPanel();
    $html .= <<<HTML

            </div>

            <div id="haloacl_tab_createacl_modificationrightsection" class="haloacl_tab_section_content">
                <div class="haloacl_tab_section_content_row">
        $hacl_haloacl_mod_1
                </div>
                <div class="haloacl_tab_section_content_row">
                    <div id="create_acl_rights_row_modificationrights" class="haloacl_tab_section_content_row"></div>
                </div>
            </div>


        </div>
        <div id="step4" style="width:600px;">
            <input id="haloacl_save_modificationrights" type="button" name="gotoStep4" value="Next Step"
            onclick="javascript:YAHOO.haloacl.create_acl_gotoStep4();" style="margin-left:30px;"/>
        </div>
        <!-- section end -->
        <script type="javascript">

            YAHOO.haloacl.create_acl_gotoStep4 = function() {
                /*
                //checks and warnings
                var goAhead = false;
                if (YAHOO.haloacl.hasGroupsOrUsers('right_tabview_create_acl_modificationrights')) {
                    goAhead=true; 
                    if (!YAHOO.haloacl.isNameInUserArray('right_tabview_create_acl_modificationrights', '$currentUser') && !YAHOO.haloacl.isNameInUsersGroupsArray('right_tabview_create_acl_modificationrights', '$currentUser')) {alert('$hacl_haloacl_mod_2'); }
                } else {
                    alert("$hacl_haloacl_mod_3");
                }
                if (goAhead)
                */


                YAHOO.haloacl.loadContentToDiv('step4','createSaveContent',{panelid:1});
            }
        </script>
        <!-- section end -->
        <script type="javascript">
            // retrieving modification rights panel
            var panelid = 'create_acl_modificationrights';
            YAHOO.haloacl.loadContentToDiv('create_acl_rights_row_modificationrights','getModificationRightsPanel',{panelid:panelid});
        </script>

HTML;


    $response->addText($html);
    return $response;
}


/**
 *
 * @return <html>
 *  returns rights section content for createacl-tab
 */
function createSaveContent() {

    $hacl_createSaveContent_1 = wfMsg('hacl_createSaveContent_1');
    $hacl_createSaveContent_2 = wfMsg('hacl_createSaveContent_2');
    $hacl_createSaveContent_3 = wfMsg('hacl_createSaveContent_3');
    $hacl_createSaveContent_4 = wfMsg('hacl_createSaveContent_4');

    global $wgUser;
    $userName = $wgUser->getName();

    $response = new AjaxResponse();


    $html = <<<HTML
        <!-- section start -->
        <div class="haloacl_tab_section_container">
            <div class="haloacl_tab_section_header">
                <div class="haloacl_tab_section_header_count">4.</div>
                <div class="haloacl_tab_section_header_title">
        $hacl_createSaveContent_1
                </div>
            </div>

            <div class="haloacl_tab_section_content">
                <div class="haloacl_tab_section_content_row">
                    <div class="haloacl_tab_section_content_row_descr" style="width:300px">
        $hacl_createSaveContent_2
                    </div>
                    <div class="haloacl_tab_section_content_row_content">
                        <div class="haloacl_tab_section_content_row_content_element">
                            <input type="text" disabled="true" id="create_acl_autogenerated_acl_name" value="" />
                        </div>
                    </div>
                </div>
                <div class="haloacl_tab_section_content_row">
                   <div style="width:800px;text-align:center">
                    <form>
                     <input type="button" id="haloacl_discardacl_button" value="Discard changes"
                        style="color:red" onclick="javascript:YAHOO.haloacl.discardChanges();"/>
                    &nbsp;<input type="button" id="haloacl_saveacl_button" name="safeACL" value="Save ACL"
                        onclick="javascript:YAHOO.haloacl.buildCreateAcl_SecDesc();"/>
                    </form>
                    </div>
                </div>

            </div>
        </div>
        <!-- section end -->
        <script type="javascript">
            YAHOO.haloacl.discardChanges = function(){
                //YAHOO.haloacl.notification.createDialogYesNo = function (renderedTo,title,content,callback,yestext,notext){
                YAHOO.haloacl.notification.createDialogYesNo("content","Discard changes","All changes will get lost!",{
                    yes:function(){window.location.href='?activetab=createACL';},
                    no:function(){}
                   },"Ok","Cancel");
          }

            ////////ACL Name
            var ACLName = "";
            var protectedItem = "";
            $$('.create_acl_general_protect').each(function(item){
                if(item.checked){
                    protectedItem = item.value;
                }
            });
            switch (protectedItem) {
                case "page":ACLName = "Page";break;
                case "property":ACLName = "Property";break;
                case "namespace":ACLName = "Namespace";break;
                case "category":ACLName = "Category";break;
            }

            switch ($('processType').value) {
                case "createACL":ACLName = "ACL:"+ACLName+'/'+$('create_acl_general_name').value;break;
                case "createAclTemplate":ACLName = 'ACL:Template/'+$('create_acl_general_name').value;break;
                case "createAclUserTemplate":ACLName = 'ACL:Template/$userName'; break;
                case "all_edited":ACLName = $('create_acl_general_name').value; break; //Name already existing - reuse
            }

            $('create_acl_autogenerated_acl_name').value = ACLName;


            var callback2 = function(result){
                if(result.status == '200'){
                    YAHOO.haloacl.notification.createDialogYesNo("content","$hacl_createSaveContent_3",result.responseText,{
                        yes:function(){window.location.href='?activetab=createACL';},
                        no:function(){window.location.href='../index.php?title='+result.responseText;},
                    },"Ok","Jump To Article");
                }else{
                    YAHOO.haloacl.notification.createDialogOk("content","$hacl_createSaveContent_4",result.responseText,{
                    yes:function(){}
                    });
                }
            };

            YAHOO.haloacl.buildCreateAcl_SecDesc = function(){
                var groups = YAHOO.haloacl.getCheckedNodesFromTree(YAHOO.haloacl.treeInstanceright_tabview_create_acl_modificationrights);

                // building xml
                var xml = "<?xml version=\"1.0\"  encoding=\"UTF-8\"?>";
                xml+="<secdesc>";
                xml+="<panelid>create_acl</panelid>";

                xml+="<users>";
                $$('.datatableDiv_right_tabview_create_acl_modificationrights_users').each(function(item){
                    if(item.checked){
                        xml+="<user>"+item.name+"</user>";
                    }
                });
                xml+="</users>";

                xml+="<groups>";
                groups.each(function(group){
                    xml+="<group>"+group+"</group>";
                });
                xml+="</groups>";
                xml+="<name>"+$('create_acl_autogenerated_acl_name').value+"</name>";
                xml+="<definefor>"+""+"</definefor>";
                xml+="<ACLType>"+$('processType').value+"</ACLType>";


                $$('.create_acl_general_protect').each(function(item){
                    if(item.checked){
                        xml+="<protect>"+item.value+"</protect>";
                    }
                });

                xml+="</secdesc>";

                YAHOO.haloacl.sendXmlToAction(xml,'saveSecurityDescriptor',callback2);
            };
        </script>

HTML;


    $response->addText($html);
    return $response;
}


/**
 *
 * @return <html>
 *  returns content for createacl-tab
 */
function createManageExistingACLContent() {

    $hacl_createManageACLContent_1 = wfMsg('hacl_createManageACLContent_1');
    $hacl_createManageACLContent_2 = wfMsg('hacl_createManageACLContent_2');

    $myGenericPanel = new HACL_GenericPanel("ManageExistingACLPanel", "[ ACL Explorer ]", "[ ACL Explorer ]", false, false,false);

    $tempContent = <<<HTML
        <div id="content_ManageExistingACLPanel">
            <div id="manageExistingACLRightList"></div>
        </div>
        <script>
            YAHOO.haloacl.loadContentToDiv('manageExistingACLRightList','rightList',{panelid:'manageExistingACLRightList', type:'edit'});
        </script>
HTML;

    $myGenericPanel->setContent($tempContent);

    $html = <<<HTML
        <div class="haloacl_tab_content">
            <div class="haloacl_tab_content_description">
                <div class="haloacl_manageusers_title">$hacl_createManageACLContent_1</div>
        $hacl_createManageACLContent_2
                </div>

HTML;
    $html.= $myGenericPanel->getPanel();
    $html.= <<<HTML

            <div class="haloacl_greyline">&nbsp;</div>
            <div id="ManageACLDetail"></div>
        </div>
HTML;

    return $html;
}


/**
 *
 * @return <html>
 *  returns content for createacl-tab
 */
function createManageUserTemplateContent() {

    $hacl_createManageUserTemplateContent_1 = wfMsg('hacl_createManageUserTemplateContent_1');

    global $wgUser;
    $myGenericPanel = new HACL_GenericPanel("ManageExistingACLPanel", "[ $hacl_createManageUserTemplateContent_1 ]", "[ $hacl_createManageUserTemplateContent_1 ]", false, false,false);
    try {
        $SDName = "Template/".$wgUser->getName();
        $SD = HACLSecurityDescriptor::newFromName($SDName);
        $SDId = $SD->getSDID();

        $tempContent = <<<HTML
        <div id="ManageACLDetail2"></div>
        <script>
            YAHOO.haloacl.loadContentToDiv('ManageACLDetail2','getSDRightsPanelContainer',{sdId:'$SDId',sdName:'$SDName',readOnly:'false'});
        </script>
HTML;
    }
    catch(Exception $e ) {
        $tempContent = "<p>no default template for user";
    }

    $myGenericPanel->setContent($tempContent);

    return $myGenericPanel->getPanel();
}


/**
 *
 * @return <html>
 *  returns content for createacl-tab
 */
function createGeneralContent($panelId, $nameBlock, $helpItem, $processType) {

    $hacl_createGeneralContent_1 = wfMsg('hacl_createGeneralContent_1');
    $hacl_createGeneralContent_2 = wfMsg('hacl_createGeneralContent_2');
    $hacl_createGeneralContent_3 = wfMsg('hacl_createGeneralContent_3');
    $hacl_createGeneralContent_4 = wfMsg('hacl_createGeneralContent_4');
    $hacl_createGeneralContent_5 = wfMsg('hacl_createGeneralContent_5');
    $hacl_createGeneralContent_6 = wfMsg('hacl_createGeneralContent_6');
    $hacl_createGeneralContent_7 = wfMsg('hacl_createGeneralContent_7');
    $hacl_createGeneralContent_8 = wfMsg('hacl_createGeneralContent_8');

    $hacl_general_nextStep = wfMsg('hacl_general_nextStep');

    $hacl_createGeneralContent_message1 = wfMsg('hacl_createGeneralContent_message1');
    $hacl_createGeneralContent_message2 = wfMsg('hacl_createGeneralContent_message2');
    $hacl_createGeneralContent_message3 = wfMsg('hacl_createGeneralContent_message3');

    $html = <<<HTML
        <div class="haloacl_tab_content">

        <div class="haloacl_tab_content_description">
        $hacl_createGeneralContent_1
        </div>

        <!-- section start -->
        <div class="haloacl_tab_section_container">
            <div class="haloacl_tab_section_header">
                <div class="haloacl_tab_section_header_count">1.</div>
                <div class="haloacl_tab_section_header_title">
        $hacl_createGeneralContent_2
                </div>
HTML;

    $html .= $helpItem->getPanel();
    $html .= <<<HTML
        </div>
            <div class="haloacl_tab_section_content">
HTML;
    if ($processType <> "createAclTemplate") {
        $html .= <<<HTML
            <div class="haloacl_tab_section_content_row">
                    <div class="haloacl_tab_section_content_row_descr">
            $hacl_createGeneralContent_3
                    </div>
                    <div class="haloacl_tab_section_content_row_content">
                        <div class="haloacl_tab_section_content_row_content_element">
                            <input type="radio" checked class="create_acl_general_protect" name="create_acl_general_protect" value="page" />&nbsp;$hacl_createGeneralContent_4
                        </div>
HTML;
        if($processType <> "createAclUserTemplate")
            $html .= <<<HTML
                        <div class="haloacl_tab_section_content_row_content_element">
                            <input type="radio" class="create_acl_general_protect" name="create_acl_general_protect" value="property" />&nbsp;$hacl_createGeneralContent_5
                        </div>
                        <div class="haloacl_tab_section_content_row_content_element">
                            <input type="radio" class="create_acl_general_protect" name="create_acl_general_protect" value="namespace" />&nbsp;$hacl_createGeneralContent_6
                        </div>
                        <div class="haloacl_tab_section_content_row_content_element">
                            <input type="radio" class="create_acl_general_protect" name="create_acl_general_protect" value="category" />&nbsp;$hacl_createGeneralContent_7
                        </div>
                  
HTML;
        $html .= <<<HTML
            </div>
                </div>
HTML;
    }

    if ($nameBlock) {
        $html .= <<<HTML

                <div class="haloacl_tab_section_content_row">
                    <div class="haloacl_tab_section_content_row_descr">
            $hacl_createGeneralContent_8
                    </div>
                    <div class="haloacl_tab_section_content_row_content">
                        <div class="haloacl_tab_section_content_row_content_element">
 
                            <input type="text" name="create_acl_general_name2" id="create_acl_general_name" value="" />
                            <div id="create_acl_general_name_container"></div>

                            <script type="javascript">
                                YAHOO.haloacl.AutoCompleter('create_acl_general_name', 'create_acl_general_name_container');
                            </script>
                        </div>
                    </div>
                </div>
                <script>
HTML;
        if ($processType == "createACL") {
            $html .= 'YAHOO.haloacl.addTooltip("tooltip_create_acl_general_name","create_acl_general_name","Enter a name of an existing page");';
        }else if($processType == "createAclTemplate") {
                $html .= 'YAHOO.haloacl.addTooltip("tooltip_create_acl_general_name","create_acl_general_name","Enter a name for the template");';
            }
        $html .= <<<HTML
            // set title when comming form request to specialpage
                    if(YAHOO.haloacl.requestedTitle != ""){
                        try{
                            $("create_acl_general_name").value = YAHOO.haloacl.requestedTitle;
                        }
                        catch(e){};
                    }
                </script>
HTML;
    }
    $html .= <<<HTML
        </div>

        <!-- section end -->

        <div id="step2_$panelId" style="width:600px">
            <input id="step2_button_$panelId" type="button" name="gotoStep2" value="$hacl_general_nextStep"
            style="margin-left:30px;"/>
        </div>

        <script type="javascript">

            // load autocompleter
            //YAHOO.haloacl.AutoCompleter();
            // processtype-values: createACL, createAclTemplate, createAclUserTemplate
            $('processType').value = '$processType';
            var processType = '$processType';

            // create listener for next-button
            var step2callbackSecondCheck = function(){
                var message = "";
                // check for protected
                if(processType != "createAclTemplate"){
                    var nextOk = false;
                    $$('.create_acl_general_protect').each(function(item){
                        if(item.checked) nextOk = true;
                    });
                    if(!nextOk)message+="$hacl_createGeneralContent_message1";
                }
                // check for name
                if(processType != "createAclUserTemplate"){
                    if($('create_acl_general_name').value=="")message+="$hacl_createGeneralContent_message2";
                }

                if (message == "") {
 
                    // loading individual user panel
                    YAHOO.haloacl.loadContentToDiv('step2_$panelId','createRightContent',{predefine:YAHOO.haloacl.createAclStdDefine});

                    // disabeling controlls
                    $$('.create_acl_general_protect').each(function(item){
                        item.disabled = true;
                    });
                    if($('create_acl_general_name') != null){
                        $('create_acl_general_name').disabled = true;
                    }
                    // ------------
                }else{
                    YAHOO.haloacl.notification.createDialogOk("content","",message,{
                        yes:function(){}
                    });
                }
            };

           var step2callback = function(){
                if(processType == "createACL"){
                    // getting protect
                    var protect = "";
                    $$('.create_acl_general_protect').each(function(item){
                        if(item.checked){
                            protect = item.value;
                        }
                    });

                    YAHOO.haloacl.callAction("doesArticleExists", {articletitle:$('create_acl_general_name').value,type:protect}, function(result){
                        if(result.responseText == "true"){
                            step2callbackSecondCheck();
                        }else if(result.responseText == "sdexisting"){
                            YAHOO.haloacl.notification.createDialogOk("content","","This "+protect+" is already protected. Please go to ManageACLs to change the acl.",{
                                yes:function(){}
                            });

                        }else{
                            YAHOO.haloacl.notification.createDialogOk("content","","Please enter a name for an existing "+protect+ ".",{
                                yes:function(){}
                            });
                        }
                    });
                }else{
                    step2callbackSecondCheck();
                }

            };

            YAHOO.haloacl.notification.subscribeToElement('step2_button_$panelId','click',step2callback);
 
        </script>
    </div>
HTML;

    return $html;

}


/**
 *
 * returns content of "create ACL" panel
 *
 * @return <html>
 *  returns content for createacl-tab
 */
function createAclContent() {

    $hacl_createACLContent_1 = wfMsg('hacl_createACLContent_1');
    $hacl_createACLContent_2 = wfMsg('hacl_createACLContent_2');

    // clear rights saved in session
    clearTempSessionRights();

    $response = new AjaxResponse();

    $helpText = $hacl_createACLContent_1;
    $helpItem = new HACL_helpPopup($hacl_createACLContent_2, $helpText);

    $html = createGeneralContent("createAcl", true, $helpItem, "createACL");

    $response->addText($html);
    return $response;
}


/**
 *
 * returns content of "manage ACL" panel
 *
 * @return <html>   returns tab-content for managel acls-tab
 */
function manageAclsContent() {

// clear rights saved in session
    clearTempSessionRights();

    $response = new AjaxResponse();

    $html = createGeneralContent("createAcl", true, $helpItem, "ManageACLTemplate");

    $response->addText($html);
    return $response;
}


/**
 *
 * returns content of "create ACL Template" panel
 *
 * @return <html>
 *  returns content for createacl-tab
 */
function createAclTemplateContent() {

    $hacl_createACLTemplateContent_1 = wfMsg('hacl_createACLTemplateContent_1');
    $hacl_createACLTemplateContent_2 = wfMsg('hacl_createACLTemplateContent_2');

    // clear temp-right-sessions
    clearTempSessionRights();

    $response = new AjaxResponse();

    $helpText = $hacl_createACLTemplateContent_1;
    $helpItem = new HACL_helpPopup($hacl_createACLTemplateContent_2, $helpText);

    $html = createGeneralContent("createAclTemplate", true, $helpItem, "createAclTemplate");

    $response->addText($html);
    return $response;
}


/**
 *
 * returns content of "create ACL Template" panel
 *
 * @return <html>
 *  returns content for createacl-tab
 */
function createAclUserTemplateContent() {
    global $wgUser;
    $hacl_createUserTemplateContent_1 = wfMsg('hacl_createUserTemplateContent_1');
    $hacl_createUserTemplateContent_2 = wfMsg('hacl_createUserTemplateContent_2');


    // clear temp-right-sessions
    clearTempSessionRights();

    $response = new AjaxResponse();

    $alreadyExisting = false;
    try {
        $SDName = "Template/".$wgUser->getName();
        $SD = HACLSecurityDescriptor::newFromName($SDName);
        $alreadyExisting = true;
    }
    catch(Exception $e ) {

    }

    if($alreadyExisting) {
        $response->addText("You already created a default user template.");

        return $response;
    }



    $helpText = $hacl_createUserTemplateContent_1;
    $helpItem = new HACL_helpPopup($hacl_createUserTemplateContent_2, $helpText);

    $html = createGeneralContent("createAclTemplate", false, $helpItem, "createAclUserTemplate");

    $response->addText($html);
    return $response;
}



/**
 *
 * @param <String>  panelid of parents-div to have an unique identifier for each right-panel
 * @return <html>   right-panel html
 *
 */
function getManageUserGroupPanel($panelid, $name="", $description="", $users=null, $groups=null, $manageUsers=null, $manageGroups=null) {

    $hacl_manageUserGroupPanel_1 = wfMsg('hacl_manageUserGroupPanel_1');

    $myGenericPanel = new HACL_GenericPanel($panelid, "Group","Group settings","",true,false);


    $content = <<<HTML

		<div id="content_$panelid" class="panel haloacl_panel_content">
                    <div id="rightTypes_$panelid">
                    <div class="halocal_panel_content_row">
                        <div class="haloacl_panel_content_row_descr">
        $hacl_manageUserGroupPanel_1
                        </div>
                        <div class="haloacl_panel_content_row_content">
HTML;
    // when there is already a name set, we set it and disable changing of the name
    if($name == "") {
        $content .='<input type="text"  id="right_name_'.$panelid.'" value=""/>';
    }else {
        $name = preg_replace("/Group\//is", "", $name);
        $content .='<input type="text"  id="right_name_'.$panelid.'" value="'.$name.'" disabled="true"/>';
    }
    $content .= <<<HTML
    </div>
                    </div>

                    <div class="haloacl_greyline">&nbsp;</div>


                    </div>

                    <div id="right_tabview_$panelid" class="yui-navset"></div>
                    <script type="text/javascript">
                       // resetting previously selected items
                      YAHOO.haloacl.clickedArrayGroups['right_tabview_$panelid'] = new Array();
                      YAHOO.haloacl.clickedArrayUsers['right_tabview_$panelid'] = new Array();
                      YAHOO.haloacl.clickedArrayUsersGroups['right_tabview_$panelid'] = new Array();

                      YAHOO.haloaclrights.clickedArrayGroups['right_tabview_$panelid'] = new Array();

                      YAHOO.haloacl.buildGroupPanelTabView('right_tabview_$panelid', '','' , '', '');
                    </script>

                    <div class="haloacl_greyline">&nbsp;</div>
                    <div>
                        <div style="width:100%;float:left;text-align:center"><input id="haloacl_save_$panelid" type="button" name="safeRight" value="Save groupsetting" onclick="YAHOO.haloacl.buildGroupPanelXML_$panelid();" /></div>
                    </div>
		</div>
HTML;

    $myGenericPanel->setContent($content);

    $footerextension = <<<HTML
    <script type="javascript>

            YAHOO.haloacl.panelcouner++;

            // rightpanel handling

            YAHOO.haloacl.buildGroupPanelXML_$panelid = function(){

                var groups = YAHOO.haloacl.getCheckedNodesFromTree(YAHOO.haloacl.treeInstanceright_tabview_$panelid);
                var panelid = '$panelid';
                if(YAHOO.haloacl.debug) console.log("panelid of grouppanel:"+panelid)

                // building xml
                var xml = "<?xml version=\"1.0\"  encoding=\"UTF-8\"?>";
                xml+="<inlineright>";
                xml+="<panelid>$panelid</panelid>";
                if($('right_name_$panelid') != null){
                    xml+="<name>"+$('right_name_$panelid').value+"</name>";
                }

                xml+="<users>";
                $$('.datatableDiv_right_tabview_'+panelid+'_users').each(function(item){
                    if(item.checked){
                        xml+="<user>"+item.name+"</user>";
                    }
                });
                xml+="</users>";

                xml+="<groups>";
                groups.each(function(group){
                    xml+="<group>"+group+"</group>";
                });
                xml+="</groups>";
                xml+="</inlineright>";


                var callback = function(result){
                    if(result.status == '200'){

                        //parse result
                        //YAHOO.lang.JSON.parse(result.responseText);
                        //genericPanelSetSaved_$panelid(true);
                        //genericPanelSetName_$panelid("[ "+$('right_name_$panelid').value+" ] - ");
                        //genericPanelSetDescr_$panelid(result.responseText);

                        YAHOO.haloacl.closePanel('$panelid');
                        $('manageUserGroupSettingsModificationRight').show();
                        $('manageUserGroupFinishButtons').show();
                        YAHOO.haloacl.loadContentToDiv('manageUserGroupSettingsModificationRight','getRightsPanel',{panelid:'manageUserGroupSettingsModificationRight',predefine:'modification'});

                    }else{
                   YAHOO.haloacl.notification.createDialogOk("content","Something went wrong",result.responseText,{
                        yes:function(){
                            }
                    });
                    }
                };
                if(YAHOO.haloacl.debug) console.log(xml);
                if(YAHOO.haloacl.debug) console.log("sending xml to saveTempGroupToSession");
                YAHOO.haloacl.sendXmlToAction(xml,'saveTempGroupToSession',callback);

            };



            resetPanel_$panelid = function(){
                $('filterSelectGroup_$panelid').value = "";
            };

        </script>
HTML;

    // if we have got users or groups, we set them to the specific array
    $footerextension .= "<script>";
    foreach(explode(",",$users) as $item) {
        $footerextension .= "YAHOO.haloacl.clickedArrayUsers['right_tabview_$panelid'].push('$item');";
    }
    foreach(explode(",",$groups) as $item) {
        $footerextension .= "YAHOO.haloacl.clickedArrayGroups['right_tabview_$panelid'].push('$item');";
    }

    $footerextension .= "YAHOO.haloacl.clickedArrayUsers['right_tabview_manageUserGroupSettingsModificationRight'] = new Array();";
    $footerextension .= "YAHOO.haloacl.clickedArrayGroups['right_tabview_manageUserGroupSettingsModificationRight'] = new Array();";

    foreach(explode(",",$manageUsers) as $item) {
        $footerextension .= "YAHOO.haloacl.clickedArrayUsers['right_tabview_manageUserGroupSettingsModificationRight'].push('$item');";
    }
    foreach(explode(",",$manageGroups) as $item) {
        $footerextension .= "YAHOO.haloacl.clickedArrayGroups['right_tabview_manageUserGroupSettingsModificationRight'].push('$item');";
    }

    foreach(array_intersect(explode(",",$users), explode(",",$groups)) as $item) {
    //   $footerextension .= "YAHOO.haloacl.clickedArrayUsersGroups['right_tabview_$panelid'].push('$item');";
    }
    $footerextension .= "</script>";

    // end of processing of old data
    $myGenericPanel->extendFooter($footerextension);


    return $myGenericPanel->getPanel();
}


/**
 *
 * flexibly used rights panel for creating new / editing existing / viewing existing inline rights
 *
 * @param <String>  panelid of parents-div to have an unique identifier for each right-panel
 * @return <html>   right-panel html
 *
 */
function getRightsPanel($panelid, $predefine, $readOnly = false, $preload = false, $preloadRightId = 0, $panelName = "Right", $rightDescription = "") {
    /* define for part */
    $hacl_createGeneralContent_9 = wfMsg('hacl_createGeneralContent_9');
    $hacl_createGeneralContent_10 = wfMsg('hacl_createGeneralContent_10');
    $hacl_createGeneralContent_11 = wfMsg('hacl_createGeneralContent_11');
    $hacl_createGeneralContent_12 = wfMsg('hacl_createGeneralContent_12');
    $hacl_createGeneralContent_13 = wfMsg('hacl_createGeneralContent_13');
    $hacl_createGeneralContent_14 = wfMsg('hacl_createGeneralContent_14');
    /* ---- */

    $hacl_rightsPanel_1 = wfMsg('hacl_rightsPanel_1');
    $hacl_rightsPanel_2 = wfMsg('hacl_rightsPanel_2');
    $hacl_rightsPanel_3 = wfMsg('hacl_rightsPanel_3');
    $hacl_rightsPanel_4 = wfMsg('hacl_rightsPanel_4');
    $hacl_rightsPanel_5 = wfMsg('hacl_rightsPanel_5');
    $hacl_rightsPanel_6 = wfMsg('hacl_rightsPanel_6');
    $hacl_rightsPanel_7 = wfMsg('hacl_rightsPanel_7');
    $hacl_rightsPanel_8 = wfMsg('hacl_rightsPanel_8');
    $hacl_rightsPanel_9 = wfMsg('hacl_rightsPanel_9');
    $hacl_rightsPanel_10 = wfMsg('hacl_rightsPanel_10');
    $hacl_rightsPanel_11 = wfMsg('hacl_rightsPanel_11');
    $hacl_rightsPanel_12 = wfMsg('hacl_rightsPanel_12');
    $hacl_rightsPanel_13 = wfMsg('hacl_rightsPanel_13');


    $hacl_rightsPanel_allUsersRegistered = wfMsg('hacl_rightsPanel_allUsersRegistered');
    $hacl_rightsPanel_allAnonymousUsers = wfMsg('hacl_rightsPanel_allAnonymousUsers');
    $hacl_rightsPanel_allUsers = wfMsg('hacl_rightsPanel_allUsers');

    $hacl_rightsPanel_right_fullaccess = wfMsg('hacl_rightsPanel_right_fullaccess');
    $hacl_rightsPanel_right_read = wfMsg('hacl_rightsPanel_right_read');
    $hacl_rightsPanel_right_edit = wfMsg('hacl_rightsPanel_right_edit');
    $hacl_rightsPanel_right_editfromform = wfMsg('hacl_rightsPanel_right_editfromform');
    $hacl_rightsPanel_right_WYSIWYG = wfMsg('hacl_rightsPanel_right_WYSIWYG');
    $hacl_rightsPanel_right_create = wfMsg('hacl_rightsPanel_right_create');
    $hacl_rightsPanel_right_move = wfMsg('hacl_rightsPanel_right_move');
    $hacl_rightsPanel_right_delete = wfMsg('hacl_rightsPanel_right_delete');
    $hacl_rightsPanel_right_annotate = wfMsg('hacl_rightsPanel_right_annotate');

    global $wgUser;
    $currentUser = $wgUser->getName();

    if ($readOnly === "true") $readOnly = true;
    if ($readOnly === "false") $readOnly = false;

    if ($readOnly) {
        $expandMode = "expand";
    } else {
        $expandMode = "expand";
    }

    if($predefine == "modification") {
        $myGenericPanel = new HACL_GenericPanel($panelid, "Right", $panelName, $rightDescription, !$readOnly, !$readOnly, "Default", $expandMode);
    }else {
        $myGenericPanel = new HACL_GenericPanel($panelid, "Right", $panelName, $rightDescription, !$readOnly, !$readOnly, null, $expandMode);
    }
    if (($readOnly === true) or ($readOnly == "true")) $disabled = "disabled"; else $disabled = "";

    if ($readOnly) $ro = "ja"; else $ro = "nein";

    $content = <<<HTML

		<div id="content_$panelid" class="panel haloacl_panel_content">
                    <div id="rightTypes_$panelid">
HTML;

    if (!$readOnly) {
        $content .= <<<HTML
                        <div class="halocal_panel_content_row">
                            <div class="haloacl_panel_content_row_descr">
            $hacl_rightsPanel_1
                            </div>
                            <div class="haloacl_panel_content_row_content">
                                <input type="text" id="right_name_$panelid" class="haloacl_right_name" value="$panelName" $disabled />
                            </div>
                        </div>
HTML;
    }
    $content .= <<<HTML
                        <div class="halocal_panel_content_row">
                            <div class="haloacl_panel_content_row_descr">
        $hacl_rightsPanel_2
                            </div>
                            <div class="haloacl_panel_rights">

                                <div class="right_fullaccess"><input id = "checkbox_right_fullaccess" actioncode="255" type="checkbox" class="right_rights_$panelid" name="fullaccess" onClick="updateRights$panelid(this)" $disabled/>&nbsp;$hacl_rightsPanel_right_fullaccess</div>
                                <div class="right_read"><input id = "checkbox_right_read" type="checkbox" actioncode="128" actioncode="128" class="right_rights_$panelid" name="read" onClick="updateRights$panelid(this)" $disabled/>&nbsp;$hacl_rightsPanel_right_read</div>
                                <div class="right_edit"><input id = "checkbox_right_edit" type="checkbox" actioncode="8" class="right_rights_$panelid" name="edit" onClick="updateRights$panelid(this)" $disabled/>&nbsp;$hacl_rightsPanel_right_edit</div>
                                <div class="right_formedit"><input id = "checkbox_right_formedit" actioncode="64" type="checkbox" class="right_rights_$panelid" name="formedit" onClick="updateRights$panelid(this)" $disabled/>&nbsp;$hacl_rightsPanel_right_editfromform</div>
                                <div class="right_wysiwyg"><input id = "checkbox_right_wysiwyg" type="checkbox" actioncode="32" class="right_rights_$panelid" name="wysiwyg" onClick="updateRights$panelid(this)" $disabled/>&nbsp;$hacl_rightsPanel_right_WYSIWYG</div>
                                <div class="right_create"><input id = "checkbox_right_create" type="checkbox" actioncode="4" class="right_rights_$panelid" name="create" onClick="updateRights$panelid(this)" $disabled/>&nbsp;$hacl_rightsPanel_right_create</div>
                                <div class="right_move"><input id = "checkbox_right_move" type="checkbox" actioncode="2" class="right_rights_$panelid" name="move" onClick="updateRights$panelid(this)" $disabled/>&nbsp;$hacl_rightsPanel_right_move</div>
                                <div class="right_delete"><input id = "checkbox_right_delete" type="checkbox" actioncode="1" class="right_rights_$panelid" name="delete" onClick="updateRights$panelid(this)" $disabled/>&nbsp;$hacl_rightsPanel_right_delete</div>
                                <div class="right_annotate"><input id = "checkbox_right_annotate" type="checkbox" actioncode="16" class="right_rights_$panelid" name="annotate" onClick="updateRights$panelid(this)" $disabled/>&nbsp;$hacl_rightsPanel_right_annotate</div>

                            </div>
                        </div>
                        <script type="javascript>
                            // tickbox handling
                            updateRights$panelid = function(element) {
                                var name = $(element).readAttribute("name");
                                element = $(element.id);

                                    var includedrights = "";
                                    if(name == "fullaccess"){
                                        includedrights = "create,read,write,edit,annotate,wysiwyg,formedit,delete,move";
                                    }else if(name == "read"){
                                        includedrights = "";
                                    }else if(name == "formedit"){
                                        includedrights = "read";
                                    }else if(name == "annotate"){
                                        includedrights = "read";
                                    }else if(name == "wysiwyg"){
                                        includedrights = "read";
                                    }else if(name == "edit"){
                                        includedrights = "read,formedit,annotate,wysiwyg";
                                    }else if(name == "create"){
                                        includedrights = "read,formedit,annotate,wysiwyg";
                                    }else if(name == "move"){
                                        includedrights = "read,formedit,annotate,wysiwyg";
                                    }else if(name == "delete"){
                                        includedrights = "read,formedit,annotate,wysiwyg";
                                    }



                                    var excluderight = "";
                                    if(name == "fullaccess"){
                                        excluderight = "fullaccess,create,read,write,edit,annotate,wysiwyg,formedit,delete,move";
                                    }else if(name == "read"){
                                        excluderight = "fullaccess,create,read,write,edit,annotate,wysiwyg,formedit,delete,move";
                                    }else if(name == "formedit"){
                                        excluderight = "fullaccess,create,write,formedit,delete,move";
                                    }else if(name == "annotate"){
                                        excluderight = "fullaccess,create,write,annotate,delete,move";
                                    }else if(name == "wysiwyg"){
                                        excluderight = "fullaccess,create,write,wysiwyg,delete,move";
                                    }else if(name == "edit"){
                                        excluderight = "fullaccess,create,delete,move";
                                    }else if(name == "create"){
                                        excluderight = "fullaccess,create";
                                    }else if(name == "move"){
                                        excluderight = "fullaccess,move";
                                    }else if(name == "delete"){
                                        excluderight = "fullaccess,delete";
                                    }
                                if(element.checked){

                                    $$('.right_rights_$panelid').each(function(item){
                                        if(includedrights.indexOf($(item).readAttribute("name")) >= 0){
                                            item.checked = true;
                                        }
                                    });
                                    
                                }else{
                                    // remove all lower rights when this right is removed
                                    $$('.right_rights_$panelid').each(function(item){
                                        if(excluderight.indexOf($(item).readAttribute("name")) >= 0){
                                            item.checked = false;
                                        }
                                    });
                                }
                            }
                        </script>

                        

                    </div>
HTML;

    /*
     * starting NOT readonly part of rightpanel,
     * so this part will be only displayed if this panel
     * is not for information-only purpose
     */
    if (!$readOnly) {
        $content .= <<<HTML
                    <div class="haloacl_greyline">&nbsp;</div>
                    <div class="halocal_panel_content_row">
                        <div class="haloacl_panel_content_row_descr" style="width:145px">
            $hacl_rightsPanel_3
                        </div>
                        <div class="haloacl_panel_content_row_content">
                            <input type="text" disabled="true" style="width:700px" id="right_description_$panelid" value="$rightDescription" />
                        </div>
                    </div>
                    <div class="halocal_panel_content_row">
                        <div class="haloacl_panel_content_row_descr" style="width:145px">
                            &nbsp;
                        </div>
                        <div class="haloacl_panel_content_row_content">
            $hacl_rightsPanel_4
                            <input id="right_autodescron_$panelid" type="radio" value="on" name="right_descriptiontext_$panelid" class="right_descriptiontext_$panelid" checked $disabled />&nbsp;$hacl_rightsPanel_5
                            <input id="right_autodescroff_$panelid" type="radio" value="off" name="right_descriptiontext_$panelid" class="right_descriptiontext_$panelid" $disabled />&nbsp;$hacl_rightsPanel_6
                        </div>
                    </div>
                    <script>
                        YAHOO.haloacl.right_descriptiontext_$panelid = function(element){

                            var autoGenerate;
                            $$('.right_descriptiontext_$panelid').each(function(item){
                                if(item.checked){
                                    autoGenerate = item.value;
                                }
                            });

                            if (autoGenerate == "on") {
                               $('right_description_$panelid').disabled = true;
                               YAHOO.haloacl.refreshPanel_$panelid();
                            }else{
                               $('right_description_$panelid').disabled = false;
                            }
                        }

                        YAHOO.util.Event.addListener("right_autodescron_$panelid", "click", YAHOO.haloacl.right_descriptiontext_$panelid);
                        YAHOO.util.Event.addListener("right_autodescroff_$panelid", "click", YAHOO.haloacl.right_descriptiontext_$panelid);

                    </script>
HTML;
    }

    $content .= <<<HTML
        <div class="haloacl_greyline">&nbsp;</div>
HTML;
    if($predefine != "modification") {
        $content .= <<<HTML
        <!-- define for start -->
           <div style="width:800px!important" class="halocal_panel_content_row">
                <div class="haloacl_panel_content_row_descr">
            $hacl_createGeneralContent_9
                </div>
                <form>
                <div class="haloacl_panel_content_row_content">
                    <div class="haloacl_panel_define_element">
                        <input type="radio" class="create_acl_general_definefor create_acl_general_definefor_$panelid" name="create_acl_general_definefor" value="privateuse" />&nbsp;$hacl_createGeneralContent_10
                    </div>
                    <div style="width:400px!important" class="haloacl_panel_define_element">
                        <input type="radio" class="create_acl_general_definefor create_acl_general_definefor_$panelid" name="create_acl_general_definefor" value="individual" />&nbsp;$hacl_createGeneralContent_11
                    </div>
                </div>
           <br/>
           <div class="haloacl_panel_content_row_descr">&nbsp;</div>
            <div class="haloacl_panel_content_row_content">
                <div class="haloacl_panel_define_element">
                    <input type="radio" class="create_acl_general_definefor create_acl_general_definefor_$panelid" name="create_acl_general_definefor" value="allusers" />&nbsp;$hacl_createGeneralContent_12
                </div>
                <div style="width:170px" class="haloacl_panel_define_element">
                    <input type="radio" class="create_acl_general_definefor create_acl_general_definefor_$panelid" name="create_acl_general_definefor" value="allusersregistered" />&nbsp;$hacl_createGeneralContent_13
                </div>
                <div style="width:170px" class="haloacl_panel_define_element">
                    <input type="radio" class="create_acl_general_definefor create_acl_general_definefor_$panelid" name="create_acl_general_definefor" value="allusersanonymous" />&nbsp;$hacl_createGeneralContent_14
                </div>
            </div>
          </div>
          </form>
        <!-- define for end -->
HTML;
    }

    $content .= <<<HTML

            <div class="haloacl_greyline">&nbsp;</div>

        <!-- tabview container -->
            <div id="haloacl_inline_notification_$panelid" class="haloacl_inline_notification">&nbsp;</div>
            <div id="right_tabview_$panelid" class="yui-navset"></div>
        <!-- tabview container end -->



        <script>
            /* define-for-javascript-handling */
            YAHOO.haloacl.panelDefinePanel_$panelid = "";
   

            function loadUserTabIfNeeded(){
                if(YAHOO.haloacl.panelDefinePanel_$panelid == 'privateuse' || YAHOO.haloacl.panelDefinePanel_$panelid == 'modification' ||YAHOO.haloacl.panelDefinePanel_$panelid == 'individual'){
                    $('right_tabview_$panelid').innerHTML = "";
                    YAHOO.haloacl.buildRightPanelTabView('right_tabview_$panelid',YAHOO.haloacl.panelDefinePanel_$panelid , '$readOnly', '$preload', '$preloadRightId');
                }else{
                    $('right_tabview_$panelid').innerHTML = "";
                }
            }

            function init(){
                //console.log("running rightspanel init method for predfined: $predefine");
                YAHOO.haloacl.panelDefinePanel_$panelid = '$predefine';
                $$('.create_acl_general_definefor_$panelid').each(function(item){
                    if(YAHOO.haloacl.panelDefinePanel_$panelid == item.value){
                        item.checked = true;
                    }
                });
                loadUserTabIfNeeded();
            }

            init();

            YAHOO.haloacl.defineForChange_$panelid = function(){
                var element = null;
                $$('.create_acl_general_definefor_$panelid').each(function(item){
                    if(item.checked){
                        element = item;
                    }
                });
                if(element == null){alert("failure");}
                YAHOO.haloacl.panelDefinePanel_$panelid = element.value;
                YAHOO.haloacl.refreshPanel_$panelid();
                loadUserTabIfNeeded();
            };

            YAHOO.util.Event.addListener($$('.create_acl_general_definefor_$panelid'), "click", function(item){YAHOO.haloacl.defineForChange_$panelid();});
        </script>

HTML;
    //    }



    if (!$readOnly) {
        $content .= <<<HTML
            <br/>
                    <div class="haloacl_greyline">&nbsp;</div>
                    <div>
HTML;
        if($predefine != "modification") {
            $content .= <<<HTML

                        <div style="width:33%;float:left;"><input type="button" value="$hacl_rightsPanel_8" onclick="javascript:YAHOO.haloacl.removePanel('$panelid');" /></div>
                        <div style="width:33%;float:left;text-align:center"><input type="button" value="$hacl_rightsPanel_9" onclick="javascript:YAHOO.haloacl.removePanel('$panelid',function(){YAHOO.haloacl.createacl_addRightPanel('$predefine');});" /></div>
                        <div style="width:33%;float:left;text-align:right"><input id="haloacl_save_$panelid" type="button" name="safeRight" value="$hacl_rightsPanel_10" onclick="YAHOO.haloacl.buildRightPanelXML_$panelid();" /></div>
HTML;
        }else {
            $content .= <<<HTML
                        <div style="width:50%;float:left;text-align:right"><input id="haloacl_save_$panelid" type="button" name="safeRight" value="$hacl_rightsPanel_10" onclick="YAHOO.haloacl.buildRightPanelXML_$panelid();" /></div>
HTML;
        }
        $content .= <<<HTML
            </div>
HTML;
    }
    $content .= <<<HTML
        </div>
HTML;

    $myGenericPanel->setContent($content);

    $footerextension = <<<HTML
    <script type="javascript>

            // recreates save status and autogenerated fields
            // to be called whenever an element in right panel changes
            YAHOO.haloacl.refreshPanel_$panelid = function(){

                ////////saved state
                genericPanelSetSaved_$panelid(false);

                ////////autogenerated description
                var autoGenerate;
                $$('.right_descriptiontext_$panelid').each(function(item){
                    if(item.checked){
                        autoGenerate = item.value;
                    }
                });

                if (autoGenerate == "on") {

                    var description = "";
                    $$('.right_rights_$panelid').each(function(item){
                        if(item.checked && item.name != "fullaccess"){
                            if ((description) != "") description = description+", ";
                            description = description+item.name;
                        }
                    });

                    var users = "";
                    var groups = "";

                    switch (YAHOO.haloacl.panelDefinePanel_$panelid) {
                        case "modification":
                            description = "Modification rights";
                        case "individual":
                        case "privateuse":
                        case "private":

                            for(i=0;i<YAHOO.haloacl.clickedArrayUsers['right_tabview_$panelid'].length;i++){
                                users = users+", U:"+YAHOO.haloacl.clickedArrayUsers['right_tabview_$panelid'][i];
                            }

                            /*
                            $$('.datatableDiv_right_tabview_$panelid'+'_users').each(function(item){
                                if(item.checked){
                                    users = users+", U:"+item.name;
                                }
                            });
                            */
                            
                            var groupsarray = YAHOO.haloacl.getCheckedNodesFromTree(YAHOO.haloacl.treeInstanceright_tabview_$panelid);
                            for(i=0;i<YAHOO.haloacl.clickedArrayGroups['right_tabview_$panelid'].length;i++){
                                groups = groups+", G:"+YAHOO.haloacl.clickedArrayGroups['right_tabview_$panelid'][i];
                            }
                            /*
                            groupsarray.each(function(group){
                                groups = groups+", G:"+group;
                            });
                            */
                            break;
                        case "allusersregistered":
                            users = "  $hacl_rightsPanel_allUsersRegistered";
                            break;
                        case "allusersanonymous":
                            users = "  $hacl_rightsPanel_allAnonymousUsers";
                            break;
                        case "allusers":
                            users = "  $hacl_rightsPanel_allUsers";
                            break;
                    }


                    if ((users != "") || (groups != "")) {
                        description = description+" for ";
                        if ((users != "")) description = description+users.substr(2);
                        if ((users != "") && (groups != "")) description = description+", ";
                        if ((groups != "")) description = description+groups.substr(2);
                    }

                    $('right_description_$panelid').value = description;
                    if($('right_name_$panelid') != null){
                        genericPanelSetName_$panelid("[ "+$('right_name_$panelid').value+" ] - ");
                    }

                    var descrLong = description;
                    if (description.length > 80) description = description.substr(0,80)+"...";
                    genericPanelSetDescr_$panelid(description,descrLong);

                }

            };

            // rightpanel handling
            YAHOO.haloacl.buildRightPanelXML_$panelid = function(onlyReturnXML){
                var panelid = '$panelid';

                if(YAHOO.haloacl.panelDefinePanel_$panelid == "individual" || YAHOO.haloacl.panelDefinePanel_$panelid == "privateuse" || YAHOO.haloacl.panelDefinePanel_$panelid == "modification"){
                    var somedatawasentered = false;
                }else{
                    var somedatawasentered = true;
                }

                if(YAHOO.haloacl.panelDefinePanel_$panelid == "modification"){
                    var rightswereset = true;
                }else{
                    var rightswereset = false;
                }



                var currentUserIncludedInRight = false;

                // building xml
                var xml = "<?xml version=\"1.0\"  encoding=\"UTF-8\"?>";
                xml+="<inlineright>";
                xml+="<panelid>$panelid</panelid>";
                xml+="<type>"+YAHOO.haloacl.panelDefinePanel_$panelid+"</type>";
                if($('right_name_$panelid') != null){
                    xml+="<name>"+escape($('right_name_$panelid').value)+"</name>";
                }
                if($('right_description_$panelid') != null){
                    xml+="<description>"+$('right_description_$panelid').value+"</description>";
                }

                xml+="<rights>";
                $$('.right_rights_$panelid').each(function(item){
                    if(item.checked){
                        rightswereset = true;
                        xml+="<right>"+item.name+"</right>";
                    }
                });
                xml+="</rights>";


                 if(YAHOO.haloacl.panelDefinePanel_$panelid == "individual" || YAHOO.haloacl.panelDefinePanel_$panelid == "privateuse" || YAHOO.haloacl.panelDefinePanel_$panelid == "modification"){

                    var groups = YAHOO.haloacl.getCheckedNodesFromTree(YAHOO.haloacl.treeInstanceright_tabview_$panelid);

                    xml+="<users>";
                    $$('.datatableDiv_right_tabview_'+panelid+'_users').each(function(item){
                        if(item.checked){
                            somedatawasentered= true;
                            xml+="<user>"+item.name+"</user>";

                            if(item.name == '$currentUser'){
                                currentUserIncludedInRight = true;
                            }
                        }
                    });
                    xml+="</users>";

                    xml+="<groups>";
                    groups.each(function(group){
                        somedatawasentered= true;
                        xml+="<group>"+group+"</group>";
                    });
                    xml+="</groups>";
                }

        
                xml+="</inlineright>";

                if(onlyReturnXML == true){
                    return xml;
                }else{
                    if(somedatawasentered == false || rightswereset == false){
                        YAHOO.haloacl.notification.createDialogOk("content","Something went wrong","You can't create an empty right. Please select an user or a group and actions.",{
                            yes:function(){
                                }
                        });
                    }else{
                        if('$predefine' == 'modification' && currentUserIncludedInRight == false){
                            YAHOO.haloacl.notification.createDialogYesNo("content","Warning","You are not included in that right.",{
                                yes:function(){

                                    var callback = function(result){
                                        if(result.status == '200'){
                                            //parse result
                                            //YAHOO.lang.JSON.parse(result.responseText);
                                            genericPanelSetSaved_$panelid(true);
                                            YAHOO.haloacl.closePanel('$panelid');
                                            if($('step3') != null){
                                                $('step3').show();
                                            }
                                        }else{
                                            alert(result.responseText);
                                        }
                                    };
                                    YAHOO.haloacl.sendXmlToAction(xml,'saveTempRightToSession',callback);
                                },
                                no:function(){}
                            },"Ok","Cancel");
                        }else{

                                    var callback = function(result){
                                        if(result.status == '200'){
                                            //parse result
                                            //YAHOO.lang.JSON.parse(result.responseText);
                                            genericPanelSetSaved_$panelid(true);
                                            YAHOO.haloacl.closePanel('$panelid');
                                            if($('step3') != null){
                                                $('step3').show();
                                            }
                                        }else{
                                            alert(result.responseText);
                                        }
                                    };
                                    YAHOO.haloacl.sendXmlToAction(xml,'saveTempRightToSession',callback);
                        }
                   }
                }
            };


            YAHOO.util.Event.addListener("checkbox_right_fullaccess", "click", YAHOO.haloacl.refreshPanel_$panelid);
            YAHOO.util.Event.addListener("checkbox_right_read", "click", YAHOO.haloacl.refreshPanel_$panelid);
            YAHOO.util.Event.addListener("checkbox_right_edit", "click", YAHOO.haloacl.refreshPanel_$panelid);
            YAHOO.util.Event.addListener("checkbox_right_formedit", "click", YAHOO.haloacl.refreshPanel_$panelid);
            YAHOO.util.Event.addListener("checkbox_right_wysiwyg", "click", YAHOO.haloacl.refreshPanel_$panelid);
            YAHOO.util.Event.addListener("checkbox_right_create", "click", YAHOO.haloacl.refreshPanel_$panelid);
            YAHOO.util.Event.addListener("checkbox_right_move", "click", YAHOO.haloacl.refreshPanel_$panelid);
            YAHOO.util.Event.addListener("checkbox_right_delete", "click", YAHOO.haloacl.refreshPanel_$panelid);
            YAHOO.util.Event.addListener("checkbox_right_annotate", "click", YAHOO.haloacl.refreshPanel_$panelid);
            YAHOO.util.Event.addListener("right_name_$panelid", "click", YAHOO.haloacl.refreshPanel_$panelid);
            YAHOO.util.Event.addListener("right_description_$panelid", "click", YAHOO.haloacl.refreshPanel_$panelid);



            checkAll_$panelid = function () {
                $('checkbox_right_read').checked = false;
                genericPanelSetSaved_$panelid(false);
            };

            resetPanel_$panelid = function(){
                $('filterSelectGroup_$panelid').value = "";
            };

        </script>
HTML;

    if ($preload == true) {

    // preload rights
        if ($predefine <> "modification") {
            $right = HACLRight::newFromID($preloadRightId);
            $actions = $right->getActions();

            $footerextension .= <<<HTML
                <script type="javascript>
HTML;
            if ($actions & HACLRight::EDIT)  $footerextension .= "$('checkbox_right_edit').checked = true;";
            if ($actions & HACLRight::CREATE)  $footerextension .= "$('checkbox_right_create').checked = true;";
            if ($actions & HACLRight::MOVE)  $footerextension .= "$('checkbox_right_move').checked = true;";
            if ($actions & HACLRight::DELETE)  $footerextension .= "$('checkbox_right_delete').checked = true;";
            if ($actions & HACLRight::READ)  $footerextension .= "$('checkbox_right_read').checked = true;";
            if ($actions & HACLRight::FORMEDIT)  $footerextension .= "$('checkbox_right_formedit').checked = true;";
            if ($actions & HACLRight::ANNOTATE)  $footerextension .= "$('checkbox_right_annotate').checked = true;";
            if ($actions & HACLRight::WYSIWYG)  $footerextension .= "$('checkbox_right_wysiwyg').checked = true;";

            $footerextension .= "</script>";

        } else {

        }



        $footerextension .= <<<HTML
        <script type="javascript>
            YAHOO.haloacl.closePanel('$panelid');

            // preload = true ==> already save this right in session.
            //YAHOO.haloacl.buildRightPanelXML_$panelid();

        </script>
HTML;

    }

    switch ($predefine) {

        case "modification":
            $footerextension .= <<<HTML
            <script type="javascript>
                $('rightTypes_$panelid').style.display = 'none';
                if($('rigth_name_$panelid') != null){
                    $('right_name_$panelid').value ="$hacl_rightsPanel_11";
                }
                if($('right_description_$panelid') != null){
                    $('right_description_$panelid').value ="$hacl_rightsPanel_12";
                }
                //$('close-button_$panelid').style.display = 'none';
                genericPanelSetName_$panelid('$hacl_rightsPanel_11');
               // YAHOO.haloacl.closePanel('$panelid');
            </script>
HTML;
            break;
        case "individual":break;
        case "all":break;
        case "private":
            $footerextension .= <<<HTML
            <script type="javascript>
                //$('rightTypes_$panelid').style.display = 'none';
                genericPanelSetName_$panelid("$hacl_rightsPanel_13 $currentUser");
                //YAHOO.haloacl.buildRightPanelXML_$panelid();
            </script>
HTML;
            break;
    }

    $myGenericPanel->extendFooter($footerextension);

    return $myGenericPanel->getPanel();
}


/**
 *
 * @param <String>  panelid of parents-div to have an unique identifier for each modification-right-panel
 * @return <html>   modification-right-panel html
 */
function getModificationRightsPanel($panelid) {

    $html = <<<HTML
	<!-- start of panel div-->
	<div id="modificationRights" >
		
	</div> <!-- end of panel div -->
        <script type="javascript>
            YAHOO.haloacl.loadContentToDiv('modificationRights','getRightsPanel',{panelid:'$panelid', predefine:'modification', readOnly:'false', preload:false, preloadRightId:0});
        </script>
HTML;

    return $html;
}

/**
 *
 * @param <string>  unique identifier
 * @return <html>   returns the user/group-select tabview; e.g. contained in right panel
 */
function rightPanelSelectDeselectTab($panelid, $predefine, $readOnly, $preload, $preloadRightId) {
    if($preload == "false") {
        $preload=false;
    };

    $hacl_rightPanelSelectDeselectTab_1 = wfMsg('hacl_rightPanelSelectDeselectTab_1');
    $hacl_rightPanelSelectDeselectTab_2 = wfMsg('hacl_rightPanelSelectDeselectTab_2');
    $hacl_rightPanelSelectDeselectTab_3 = wfMsg('hacl_rightPanelSelectDeselectTab_3');
    $hacl_rightPanelSelectDeselectTab_4 = wfMsg('hacl_rightPanelSelectDeselectTab_4');
    $hacl_rightPanelSelectDeselectTab_5 = wfMsg('hacl_rightPanelSelectDeselectTab_5');

    global $wgUser;
    $currentUser = $wgUser->getName();

    $parentsPanelid = substr($panelid, 14);

    $html = <<<HTML
        <!-- leftpart -->
        <div class="haloacl_rightpanel_selecttab_container">
            <div class="haloacl_rightpanel_selecttab_leftpart">
                <div class="haloacl_rightpanel_selecttab_leftpart_filter">
                    <span class="haloacl_rightpanel_selecttab_leftpart_filter_title">
        $hacl_rightPanelSelectDeselectTab_1
                    </span>
                    <span style="margin-right:29px;float:right;font-weight:bold">select</span>
                </div>
                <div class="haloacl_rightpanel_selecttab_leftpart_filter">
                    <span class="haloacl_rightpanel_selecttab_leftpart_filter_title">
        $hacl_rightPanelSelectDeselectTab_2
                    </span>
                    <input id="filterSelectGroup_$panelid" class="haloacl_filter_input" type="text" />
                </div>
                <div id="treeDiv_$panelid" class="haloacl_rightpanel_selecttab_leftpart_treeview">&nbsp;</div>
                <div class="haloacl_rightpanel_selecttab_leftpart_treeview_userlink">
                    <a class="highlighted datatable_user_link" onClick="YAHOO.haloacl.removeHighlighting();$(this).addClassName('highlighted');$('datatablepaging_groupinfo_$panelid').innerHTML='';" href="javascript:YAHOO.haloacl.datatableInstance$panelid.executeQuery('all');">$hacl_rightPanelSelectDeselectTab_3</a>
                </div>
            </div>

            <!-- end of left part -->


            <!-- starting right part -->

            <div class="haloacl_rightpanel_selecttab_rightpart">
                <div class="haloacl_rightpanel_selecttab_rightpart_filter">

                    <span class="haloacl_rightpanel_selecttab_rightpart_filter_title">
        $hacl_rightPanelSelectDeselectTab_4:&nbsp;<span id="datatablepaging_count_$panelid"></span> <span id="datatablepaging_groupinfo_$panelid"></span>
                    </span>
                    <span style="margin-right:5px;float:right;font-weight:bold">select</span>

                </div>
                <div class="haloacl_rightpanel_selecttab_rightpart_filter">
                    <span class="haloacl_rightpanel_selecttab_rightpart_filter_title">
        $hacl_rightPanelSelectDeselectTab_5
                    </span>
                    <input id="datatable_filter_$panelid" class="haloacl_filter_input" type="text" />

                </div>
                <div id="datatableDiv_$panelid" class="haloacl_rightpanel_selecttab_rightpart_datatable">&nbsp;</div>
                <div id="datatablepaging_datatableDiv_$panelid"></div>
                </div>
            </div>

            <!-- end of right part -->

        </div>
    <script type="text/javascript">
        YAHOO.haloacl.notification.clearAllNotification();
        // user list on the right

        YAHOO.haloacl.datatableInstance$panelid = YAHOO.haloacl.userDataTable("datatableDiv_$panelid","$panelid");


        YAHOO.haloacl.treeInstance$panelid = YAHOO.haloacl.getNewTreeview("treeDiv_$panelid",'$panelid');

        YAHOO.haloacl.labelClickAction_$panelid = function(query,element){
            if(YAHOO.haloacl.debug) console.log("element"+element);
            if(YAHOO.haloacl.debug) console.log("query"+query);
            var element = $(element);

            YAHOO.haloacl.removeHighlighting();

            $(element.parentNode.parentNode).addClassName("highlighted");
            if(YAHOO.haloacl.debug) console.log(element);
            $('datatablepaging_groupinfo_$panelid').innerHTML = "in "+query;

            YAHOO.haloacl.datatableInstance$panelid.executeQuery(query);

        };

        YAHOO.haloacl.treeInstance$panelid.labelClickAction = 'YAHOO.haloacl.labelClickAction_$panelid';
        YAHOO.haloacl.buildTreeFirstLevelFromJson(YAHOO.haloacl.treeInstance$panelid);

       
        //filter event
        YAHOO.util.Event.addListener("filterSelectGroup_$panelid", "keyup", function(e){
            var filtervalue = document.getElementById("filterSelectGroup_$panelid").value;
            YAHOO.haloacl.applyFilterOnTree (YAHOO.haloacl.treeInstance$panelid.getRoot(), filtervalue);

            if($('filterSelectGroup_$panelid').value != "" || $('datatable_filter_$panelid').value != ""){
                YAHOO.haloacl.notification.showInlineNotification('filter active','haloacl_inline_notification_$parentsPanelid');
            }else{
                YAHOO.haloacl.notification.hideInlineNotification('haloacl_inline_notification_$parentsPanelid');
            }
        });


        YAHOO.util.Event.addListener("datatable_filter_$panelid", "keyup", function(){
            if(YAHOO.haloacl.debug) console.log("filterevent fired");
            YAHOO.haloacl.datatableInstance$panelid.executeQuery('');


            if($('filterSelectGroup_$panelid').value != "" || $('datatable_filter_$panelid').value != ""){
                YAHOO.haloacl.notification.showInlineNotification('filter active','haloacl_inline_notification_$parentsPanelid');
            }else{
                YAHOO.haloacl.notification.hideInlineNotification('haloacl_inline_notification_$parentsPanelid');
            }

        });
        if(YAHOO.haloacl.debug) console.log("datatable_filter_$panelid");


        /* this function is called from onClick-attribute in userTable.js
         * select formater
         */
        YAHOO.haloacl.handleDatatableClick_$panelid = function(item){
           var panelid = '$panelid';

           // recreate desc
            var fncname = "YAHOO.haloacl.refreshPanel_"+panelid.substr(14)+"();";
            eval(fncname);

           if(YAHOO.haloacl.clickedArrayUsers[panelid] == null){
                YAHOO.haloacl.clickedArrayUsers[panelid] = new Array();
           }
           if(YAHOO.haloacl.clickedArrayUsersGroups[panelid] == null){
                YAHOO.haloacl.clickedArrayUsersGroups[panelid] = new Array();
            }
            var element = item;
            if(element.checked){
                if(YAHOO.haloacl.debug) console.log("adding "+item.name+" to list of checked users - panelid:$panelid");
                YAHOO.haloacl.addUserToUserArray('$panelid',element.name);
            }else{
                if(YAHOO.haloacl.debug) console.log("removing "+item.name+" from list of checked users - panelid:$panelid");
                YAHOO.haloacl.removeUserFromUserArray('$panelid',element.name);
            }
            YAHOO.haloacl.clickedArrayUsersGroups['$panelid'][element.name] = $(element).readAttribute("groups");

            /*
           if(YAHOO.haloacl.debug) console.log("restet array for panelid:"+panelid);
           $$('.datatableDiv_'+panelid+'_users').each(function(item){
                if(item.checked){
                   YAHOO.haloacl.clickedArrayUsers[panelid].push(item.name);

                }
                YAHOO.haloacl.clickedArrayUsersGroups[panelid][item.name] = $(item).readAttribute("groups");
           });
           */

        };
        
        //YAHOO.util.Event.addListener("datatableDiv_$panelid", "click", handleDatatableClick);

        

HTML;

    if ($preload) {

        if ($predefine <> "modification") {

            $right = HACLRight::newFromID($preloadRightId);
            $users = $right->getUsers();
            $groups = $right->getGroups();

        } else {

            $SD = HACLSecurityDescriptor::newFromID($preloadRightId);
            $users = $SD->getManageUsers();
            $groups = $SD->getManageGroups();
        }

        // preload users
        foreach ($users as $user) {
            $hUser = User::newFromId($user)->getName();
            $html .= <<<HTML
                    YAHOO.haloacl.addUserToUserArray('$panelid', '$hUser');
HTML;
        }

        // preload usgroupsers
        foreach ($groups as $group) {
            $hGroup = HACLGroup::newFromId($group)->getGroupName();
            $html .= <<<HTML
                    YAHOO.haloacl.addGroupToGroupArray('$panelid', '$hGroup');
HTML;
        }
    }else {
        $html .="YAHOO.haloacl.addUserToUserArray('$panelid', '$currentUser')";
    }
    $html .= <<<HTML
        </script>
HTML;

/*
switch ($predefine) {
    case "all":
        $html .= <<<HTML
        <script type="text/javascript">
            YAHOO.haloacl.selectAllNodes(YAHOO.haloacl.treeInstance$panelid.getRoot());
        </script>
HTML;
        break;
    case "private":
        $html .= <<<HTML
        <script type="text/javascript">
            YAHOO.haloacl.selectPrivateNode(YAHOO.haloacl.treeInstance$panelid.getRoot());
        </script>
HTML;
        break;

}*/
    return $html;

}


/**
 *
 * @param <string>  unique identifier
 * @return <html>   returns the assigned tabview; e.g. contained in right panel
 */
function rightPanelAssignedTab($panelid, $predefine, $readOnly, $preload=false, $preloadRightId=8) {

    $hacl_rightPanelSelectDeselectTab_1 = wfMsg('hacl_rightPanelSelectDeselectTab_1');
    $hacl_rightPanelSelectDeselectTab_2 = wfMsg('hacl_rightPanelSelectDeselectTab_2');
    $hacl_rightPanelSelectDeselectTab_3 = wfMsg('hacl_rightPanelSelectDeselectTab_3');
    $hacl_rightPanelSelectDeselectTab_4 = wfMsg('hacl_rightPanelSelectDeselectTab_4');
    $hacl_rightPanelSelectDeselectTab_5 = wfMsg('hacl_rightPanelSelectDeselectTab_5');


    $html = <<<HTML
        <!-- leftpart -->
        <div id="haloacl_rightpanel_selectab_info_$panelid" style="width:875px;height:320px;display:none" class="haloacl_rightpanel_selecttab_container">
        </div>
        <div id="haloacl_rightpanel_selectab_$panelid" class="haloacl_rightpanel_selecttab_container">
            <div class="haloacl_rightpanel_selecttab_leftpart">
                <div class="haloacl_rightpanel_selecttab_leftpart_filter">
                    <span class="haloacl_rightpanel_selecttab_leftpart_filter_title">
        $hacl_rightPanelSelectDeselectTab_1
                    </span>
                </div>
                <div class="haloacl_rightpanel_selecttab_leftpart_filter">
                    <span class="haloacl_rightpanel_selecttab_leftpart_filter_title">
        $hacl_rightPanelSelectDeselectTab_2
                    </span>
                    <input class="haloacl_filter_input" id="filterAssignedGroup_$panelid" type="text" "/>
                </div>
                <div id="treeDivRO_$panelid" class="haloacl_rightpanel_selecttab_leftpart_treeview">&nbsp;</div>
                <!--
                <div class="haloacl_rightpanel_selecttab_leftpart_treeview_userlink">
        $hacl_rightPanelSelectDeselectTab_3
                </div>
                -->
            </div>
            <!-- end of left part -->

            <!-- starting right part -->

            <div class="haloacl_rightpanel_selecttab_rightpart">
                <div class="haloacl_rightpanel_selecttab_rightpart_filter">
                    <span class="haloacl_rightpanel_selecttab_rightpart_filter_title">
        $hacl_rightPanelSelectDeselectTab_4
                    </span>
                </div>
                <div class="haloacl_rightpanel_selecttab_rightpart_filter">
                    <span class="haloacl_rightpanel_selecttab_rightpart_filter_title">
        $hacl_rightPanelSelectDeselectTab_5
                    </span>
                    <input class="haloacl_filter_input type="text" onKeyup="YAHOO.haloacl.refilterUsersAssigned_$panelid(this);"/>
                </div>
                <div id="ROdatatableDiv_$panelid" class="haloacl_rightpanel_selecttab_rightpart_datatable">&nbsp;</div>
                <div id="ROdatatablepaging_datatableDiv_$panelid"></div>
                </div>
            </div>
            <!-- end of right part -->

        </div>
    <script type="text/javascript">
        YAHOO.haloacl.notification.clearAllNotification();

        if(YAHOO.haloacl.hasGroupsOrUsers('$panelid') == true){
            $('haloacl_rightpanel_selectab_info_$panelid').hide();
            $('haloacl_rightpanel_selectab_$panelid').show();

            // user list on the right
            YAHOO.haloacl.RODatatableInstace$panelid = YAHOO.haloacl.ROuserDataTableV2("ROdatatableDiv_$panelid","$panelid");

            // treeview part - so the left part of the select/deselct-view

            YAHOO.haloacl.ROtreeInstance$panelid = YAHOO.haloacl.getNewTreeview("treeDivRO_$panelid",'$panelid');

            YAHOO.haloacl.labelClickASSIGNED$panelid = function(name,element){
                element.parentNode.parentNode.remove();
                YAHOO.haloacl.removeGroupFromGroupArray('$panelid', name);
                $('ROdatatableDiv_$panelid').innerHTML = "";
                YAHOO.haloacl.RODatatableInstace$panelid = YAHOO.haloacl.ROuserDataTableV2("ROdatatableDiv_$panelid","$panelid");
            };
            YAHOO.haloacl.ROtreeInstance$panelid.labelClickAction = "YAHOO.haloacl.labelClickASSIGNED$panelid";

        }else{
            $('haloacl_rightpanel_selectab_$panelid').hide();
            $('haloacl_rightpanel_selectab_info_$panelid').innerHTML = "<h4>&nbsp;&nbsp;No groups or users have been selected.</h4><h4>&nbsp;&nbsp;Please select a group or an user.</h4>";
            $('haloacl_rightpanel_selectab_info_$panelid').show();


        }
       
HTML;


    //load groups (from right groups list, or sd modification groups)
    if ($preload) {
        $tempGroups2 = array();

        if ($predefine == "individual") {
            $tempRight = HACLRight::newFromID($preloadRightId);
            $tempGroups = $tempRight->getGroups();

            foreach ($tempGroups as $key => $value) {
                $tempGroup = HACLGroup::newFromID($value);
                $tempGroups2[] = $tempGroup->getGroupName();
            }

        } elseif ($predefine == "modification") {
            $tempRight = HACLSecurityDescriptor::newFromID($preloadRightId);
            $tempGroups = $tempRight->getManageGroups();

            foreach ($tempGroups as $key => $value) {
                $tempGroup = HACLGroup::newFromID($value);
                $tempGroups2[] = $tempGroup->getGroupName();
            }
        }

        $tempGroups2 = json_encode($tempGroups2);

        $html .= <<<HTML
        YAHOO.haloacl.preloadCheckedGroups('$tempGroups2', YAHOO.haloacl.ROtreeInstance$panelid);
HTML;
    }
    $html .= <<<HTML
    

        //YAHOO.haloacl.ROtreeInstance$panelid.labelClickAction = 'YAHOO.haloacl.datatableInstance$panelid.executeQuery';
        YAHOO.haloacl.buildUserTreeRO(YAHOO.haloacl.treeInstance$panelid, YAHOO.haloacl.ROtreeInstance$panelid);

        refilterGroup = function() {
            YAHOO.haloacl.filterNodesGroupUser (YAHOO.haloacl.ROtreeInstance$panelid.getRoot(), document.getElementById("filterAssignedGroup_$panelid").value);
        }
        YAHOO.util.Event.addListener("filterAssignedGroup_$panelid", "keyup", refilterGroup);

        YAHOO.haloacl.refilterUsersAssigned_$panelid = function(element){
            YAHOO.haloacl.filterUserDatatableJS("userdatatable_name_$panelid",element.value);
        }


    </script>

HTML;
    return $html;

}


/**
 *
 * returns panel listing all existing rights
 *
 * @param <string>  unique identifier
 * @return <html>   returns the user/group-select tabview; e.g. contained in right panel
 */
function rightList($panelid, $type = "readOnly") {

    $hacl_rightList_All = wfMsg('hacl_rightList_All');
    $hacl_rightList_StandardACLs = wfMsg('hacl_rightList_StandardACLs');
    $hacl_rightList_Page = wfMsg('hacl_rightList_Page');
    $hacl_rightList_Category = wfMsg('hacl_rightList_Category');
    $hacl_rightList_Property = wfMsg('hacl_rightList_Property');
    $hacl_rightList_Namespace = wfMsg('hacl_rightList_Namespace');
    $hacl_rightList_ACLtemplates = wfMsg('hacl_rightList_ACLtemplates');
    $hacl_rightList_Defaultusertemplates = wfMsg('hacl_rightList_Defaultusertemplates');

    $hacl_rightList_1 = wfMsg('hacl_rightList_1');
    $hacl_manageUser_7 = wfMsg('hacl_manageUser_7');


    $response = new AjaxResponse();

    $html = <<<HTML

    <div class="haloacl_manageacl_selector_content">
HTML;
    if($type != "readOnly") {
        $html .= <<<HTML
            <script>
            YAHOO.haloacl.filter_handleFilterChangeEvent = function(e){
                if(e.name == "all"){
                    if(e.checked){
                        $$('.haloacl_manageacl_filter').each(function(i){i.checked=true;});
                    }else{
                        $$('.haloacl_manageacl_filter').each(function(i){i.checked=false;});
                    }
                }else if(e.name == "standardacl"){
                    if(e.checked){
                        $$('.haloacl_manageacl_filter').each(function(i){
                            if(i.name == "page" || i.name == "category" || i.name == "property" || i.name == "namespace"){
                                i.checked=true;
                            }
                        });
                    }else{
                        $$('.haloacl_manageacl_filter').each(function(i){
                            if(i.name == "all" ||i.name == "page" || i.name == "category" || i.name == "property" || i.name == "namespace"){
                                i.checked=false;
                            }
                        });
                    }
                }else if(e.name == "page"){
                    if(e.checked){
                    }else{
                       $$('.haloacl_manageacl_filter').each(function(i){
                            if(i.name == "all" || i.name == "standardacl"){
                                i.checked=false;
                            }
                        });
                    }
                }else if(e.name == "category"){
                    if(e.checked){
                    }else{
                       $$('.haloacl_manageacl_filter').each(function(i){
                            if(i.name == "all"|| i.name == "standardacl"){
                                i.checked=false;
                            }
                        });
                    }
                }else if(e.name == "property"){
                    if(e.checked){
                    }else{
                       $$('.haloacl_manageacl_filter').each(function(i){
                            if(i.name == "all"|| i.name == "standardacl"){
                                i.checked=false;
                            }
                        });
                    }
                }else if(e.name == "namespace"){
                    if(e.checked){
                    }else{
                       $$('.haloacl_manageacl_filter').each(function(i){
                            if(i.name == "all"|| i.name == "standardacl"){
                                i.checked=false;
                            }
                        });
                    }
                }else if(e.name == "acltemplate"){
                    if(e.checked){
                    }else{
                       $$('.haloacl_manageacl_filter').each(function(i){
                            if(i.name == "all"){
                                i.checked=false;
                            }
                        });
                    }
                }else if(e.name == "defusertemplate"){
                    if(e.checked){
                    }else{
                       $$('.haloacl_manageacl_filter').each(function(i){
                            if(i.name == "all"){
                                i.checked=false;
                            }
                        });
                    }
                }
            };
            </script>


            <div id="haloacl_manageuser_contentmenu">
            <div id="haloacl_manageacl_contentmenu_title">
                Show ACLs
            </div>
            <div class="haloacl_manageacl_contentmenu_element">
                <input class="haloacl_manageacl_filter" onClick="YAHOO.haloacl.filter_handleFilterChangeEvent(this);" type="checkbox" checked="" name="all"/>&nbsp;$hacl_rightList_All
            </div>
            <div class="haloacl_manageacl_contentmenu_element">
                <input class="haloacl_manageacl_filter" onClick="YAHOO.haloacl.filter_handleFilterChangeEvent(this);" type="checkbox" checked="" name="standardacl"/>&nbsp;$hacl_rightList_StandardACLs
            </div>
            <div class="haloacl_manageacl_contentmenu_element">
                <input class="haloacl_manageacl_filter sub" onClick="YAHOO.haloacl.filter_handleFilterChangeEvent(this);"  type="checkbox" checked="" name="page"/>&nbsp;$hacl_rightList_Page
            </div>
            <div class="haloacl_manageacl_contentmenu_element">
                <input class="haloacl_manageacl_filter sub" onClick="YAHOO.haloacl.filter_handleFilterChangeEvent(this);" type="checkbox" checked="" name="category"/>&nbsp;$hacl_rightList_Category
            </div>
            <div class="haloacl_manageacl_contentmenu_element">
                <input class="haloacl_manageacl_filter sub" onClick="YAHOO.haloacl.filter_handleFilterChangeEvent(this);" type="checkbox" checked="" name="property"/>&nbsp;$hacl_rightList_Property
            </div>
            <div class="haloacl_manageacl_contentmenu_element">
                <input class="haloacl_manageacl_filter  sub" onClick="YAHOO.haloacl.filter_handleFilterChangeEvent(this);" type="checkbox" checked="" name="namespace"/>&nbsp;$hacl_rightList_Namespace
            </div>
            <div class="haloacl_manageacl_contentmenu_element">
                <input class="haloacl_manageacl_filter" onClick="YAHOO.haloacl.filter_handleFilterChangeEvent(this);" type="checkbox" checked="" name="acltemplate"/>&nbsp;$hacl_rightList_ACLtemplates
            </div>
            <div class="haloacl_manageacl_contentmenu_element">
                <input class="haloacl_manageacl_filter" onClick="YAHOO.haloacl.filter_handleFilterChangeEvent(this);" type="checkbox" checked="" name="defusertemplate"/>&nbsp;$hacl_rightList_Defaultusertemplates
            </div>


        </div>
HTML;
    }

    $html .= <<<HTML

        <div id="haloacl_manageuser_contentlist">

        <div id="manageuser_grouplisting">
                <div class="haloacl_manageacl_contenttitle">
        $hacl_rightList_1
HTML;

    if($type != "readOnly") {
        $html .= <<<HTML
            <span style="margin-left:280px">edit</span><span style="margin-left:65px">delete</span>
HTML;
    }else {
        $html .= <<<HTML
            <span style="margin-left:280px">Information</span><span style="margin-left:65px">Select</span>
HTML;

    }

    $html .= <<<HTML
        </div>
HTML;

    if($type != "readOnly") {
        $html .= <<<HTML
            <div class="haloacl_manageacl_contenttitle">
            Filter:&nbsp;<input class="haloacl_filter_input"/>
        </div>
HTML;
    }
    $html .= <<<HTML

            <div id="haloacl_manageacl_acltree">
                <div id="treeDiv_$panelid" class="haloacl_rightpanel_selecttab_leftpart_treeview">&nbsp;</div>
            </div>
        </div>
HTML;

    if($type != "readOnly") {
        $html .= <<<HTML
        <div id="haloacl_manageuser_contentlist_footer">
            <input type="button" onClick="YAHOO.haloacl.manageACLdeleteCheckedGroups();" value="$hacl_manageUser_7" />
        </div>
HTML;

    }

    $html .= <<<HTML
    </div>

    

<script type="text/javascript">

    YAHOO.haloacl.manageACLdeleteCheckedGroups = function(){
        var checkedgroups = YAHOO.haloacl.getCheckedNodesFromTree(YAHOO.haloaclrights.treeInstance$panelid, null);

        var xml = "<?xml version=\"1.0\"  encoding=\"UTF-8\"?>";
        xml += "<groupstodelete>";
        for(i=0;i<checkedgroups.length;i++){
            xml += "<group>"+checkedgroups[i]+"</group>";
        }
        xml += "</groupstodelete>";
        if(YAHOO.haloacl.debug) console.log(xml);

        var callback5 = function(result){
            YAHOO.haloacl.notification.createDialogOk("content","Manage ACLs",result.responseText,{
                yes:function(){
                    window.location.href='?activetab=manageACLs';
                    }
            });
        };

        YAHOO.haloacl.sendXmlToAction(xml,'deleteGroups',callback5);

    };


    // treeview part - so the left part of the select/deselct-view

    YAHOO.haloaclrights.treeInstance$panelid = YAHOO.haloaclrights.getNewRightsTreeview("treeDiv_$panelid",'$panelid', '$type');
    //YAHOO.haloaclrights.treeInstance$panelid.labelClickAction = 'YAHOO.haloaclrights.datatableInstance$panelid.executeQuery';
    YAHOO.haloaclrights.treeInstance$panelid.labelClickAction = 'null';

    YAHOO.haloaclrights.buildTreeFirstLevelFromJson(YAHOO.haloaclrights.treeInstance$panelid,"template");


HTML;
    if($type != "readOnly") {
        $html .=<<<HTML

        var reloadaction = function(){
            YAHOO.haloaclrights.treeInstance$panelid = YAHOO.haloaclrights.getNewRightsTreeview("treeDiv_$panelid",'$panelid', '$type');
            YAHOO.haloaclrights.buildTreeFirstLevelFromJson(YAHOO.haloaclrights.treeInstance$panelid,"template");
        }
        $$('.haloacl_manageacl_filter').each(function(item){
            YAHOO.util.Event.addListener(item, "click", reloadaction);
        });
        
HTML;
    }

    $html .= <<<HTML


/*
    refilter = function() {
        YAHOO.haloaclrights.filterNodes (YAHOO.haloaclrights.treeInstance$panelid.getRoot(), document.getElementById("filterSelectGroup_$panelid").value);
    }
    //filter event
    YAHOO.util.Event.addListener("filterSelectGroup_$panelid", "keyup", refilter);
    YAHOO.util.Event.addListener("datatable_filter_$panelid", "keyup", function(){
        if(YAHOO.haloacl.debug) console.log("filterevent fired");
        YAHOO.haloaclrights.datatableInstance$panelid.executeQuery('');
    });
*/

</script>

HTML;

    $html = "<div id=\"content_".$panelid."\">".$html."</div>";

    $response->addText($html);
    return $response;

}


/**
 *
 * returns panel listing all existing rights, embedded in container for editing (Manage ACL Panel)
 *
 * @param <string>  unique identifier
 * @return <html>   returns the user/group-select tabview; e.g. contained in right panel
 */
function getSDRightsPanelContainer($sdId, $sdName, $readOnly=false) {

    $hacl_SDRightsPanelContainer_1 = wfMsg('hacl_SDRightsPanelContainer_1');
    $hacl_SDRightsPanelContainer_2 = wfMsg('hacl_SDRightsPanelContainer_2');
    $hacl_SDRightsPanelContainer_3 = wfMsg('hacl_SDRightsPanelContainer_3');
    $hacl_SDRightsPanelContainer_4 = wfMsg('hacl_SDRightsPanelContainer_4');

    if ($readOnly === "true") $readOnly = true;
    if ($readOnly === "false") $readOnly = false;

    $sdName = "ACL:".$sdName;
    $panelid = "SDRightsPanel_$sdId";
    $response = new AjaxResponse();

    $myGenericPanel = new HACL_GenericPanel($panelid, "[ $hacl_SDRightsPanelContainer_1 $sdName ]", "[ $hacl_SDRightsPanelContainer_1 $sdName ]");

    $predefine = "individual";
    $html = "";
    if(!$readOnly) {
        $html = <<<HTML
        <!-- add right part -->
        <p/>
                <div class="haloacl_existing_right_add_buttons">
                    <input id="haloacl_create_right_$predefine" type="button" value="Create right"
                        onclick="javascript:YAHOO.haloacl.createacl_addRightPanel('$predefine');"/>
                    &nbsp;
                    <input id="haloacl_add_right_$predefine" type="button" value="Add right template"
                        onclick="javascript:YAHOO.haloacl.createacl_addRightTemplate('$predefine');"/>
                </div>
            <script>
            YAHOO.haloacl.createacl_addRightPanel = function(predefine){
                  var panelid = 'create_acl_right_'+ YAHOO.haloacl.panelcouner;
                  var divhtml = '<div id="create_acl_rights_row'+YAHOO.haloacl.panelcouner+'" class="haloacl_tab_section_content_row"></div>';
                  var containerWhereDivsAreInserted = $('haloacl_tab_createacl_rightsection');
                  $('haloacl_tab_createacl_rightsection').insert(divhtml,containerWhereDivsAreInserted);

                  YAHOO.haloacl.loadContentToDiv('create_acl_rights_row'+YAHOO.haloacl.panelcouner,'getRightsPanel',{panelid:panelid, predefine:predefine});
                  YAHOO.haloacl.panelcouner++;
            };

            YAHOO.haloacl.createacl_addRightTemplate = function(predefine){
                  var panelid = 'create_acl_right_'+ YAHOO.haloacl.panelcouner;
                  var divhtml = '<div id="create_acl_rights_row'+YAHOO.haloacl.panelcouner+'" class="haloacl_tab_section_content_row"></div>';
                  var containerWhereDivsAreInserted = $('haloacl_tab_createacl_rightsection');
                  $('haloacl_tab_createacl_rightsection').insert(divhtml,containerWhereDivsAreInserted);

                  YAHOO.haloacl.loadContentToDiv('create_acl_rights_row'+YAHOO.haloacl.panelcouner,'getRightsContainer',{panelid:panelid});
                  YAHOO.haloacl.panelcouner++;
            };
            </script>

        <div id="haloacl_tab_createacl_rightsection">&nbsp;</div>
        <p/>

        <!-- end of add right part -->
HTML;
    }
    $html .= <<<HTML
        <div class="haloacl_sd_container_$readOnly" id="SDRightsPanelContainer_$sdId">
        </div>
        <script>
            YAHOO.haloacl.loadContentToDiv('SDRightsPanelContainer_$sdId','getSDRightsPanel',{sdId:'$sdId', readOnly:'$readOnly'});
        </script>
        <div class="haloacl_greyline">&nbsp;</div>
        <div>
            <div style="width:33%;float:left;"><input type="button" value="$hacl_SDRightsPanelContainer_2" onclick="javascript:YAHOO.haloacl.deleteSD('$sdId');" /></div>
            <div style="width:33%;float:left;text-align:center"><input type="button" value="$hacl_SDRightsPanelContainer_3" onclick="javascript:YAHOO.haloacl.removePanel('$panelid');YAHOO.haloacl.createacl_addRightPanel();" /></div>
            <div style="width:33%;float:left;text-align:right"><input id="haloacl_save_right" type="button" name="safeRight" value="$hacl_SDRightsPanelContainer_4" onclick="YAHOO.haloacl.buildCreateAcl_SecDesc();" /></div>
        </div>

        <script type="javascript">

            var callback2 = function(result){
                    if(result.status == '200'){
                        try{
                        $('create_acl_autogenerated_acl_name').value = result.responseText;
                        }catch(e){};
                        genericPanelSetSaved_$panelid(true);

                        YAHOO.haloacl.notification.createDialogOk("content","ACL","Right has been saved",{
                            yes:function(){
                                window.location.href='?activetab=manageACLs';
                                }
                        });
                    }else{
                        YAHOO.haloacl.notification.createDialogOk("content","Something went wrong",result.responseText,{
                            yes:function(){
                                }
                        });
                    }
                };

            YAHOO.haloacl.buildCreateAcl_SecDesc = function(){
                
                // building xml
                var xml = "<?xml version=\"1.0\"  encoding=\"UTF-8\"?>";
                xml+="<secdesc>";
                xml+="<panelid>create_acl</panelid>";
                xml+="<name>$sdName</name>";
                xml+="<ACLType>all_edited</ACLType>";

                var callback = function(result){
                    if(result.status == '200'){

                        YAHOO.haloacl.notification.createDialogOk("content","Right",result.responseText,{
                            yes:function(){
                                }
                        });
                    }else{
                        YAHOO.haloacl.notification.createDialogOk("content","Something went wrong",result.responseText,{
                            yes:function(){
                                }
                        });
                    }
                };

                $$('.create_acl_general_protect').each(function(item){
                    if(item.checked){
                        xml+="<protect>"+item.value+"</protect>";
                    }
                });

                xml+="</secdesc>";

                YAHOO.haloacl.sendXmlToAction(xml,'saveSecurityDescriptor',callback2);
            };
        </script>
HTML;

    $html = "<div id=\"content_".$panelid."\">".$html."</div>";

    $myGenericPanel->setContent($html);

    $response->addText($myGenericPanel->getPanel());
    return $response;

}


/**
 *
 * returns panel listing all existing rights, embedded in container for selection (Create ACL Panel)
 *
 * @param <string>  unique identifier
 * @return <html>   returns the user/group-select tabview; e.g. contained in right panel
 */
function getRightsContainer($panelid, $type = "readOnly") {

    $hacl_RightsContainer_1 = wfMsg('hacl_RightsContainer_1');
    $hacl_RightsContainer_2 = wfMsg('hacl_RightsContainer_2');

    $response = new AjaxResponse();

    $myGenericPanel = new HACL_GenericPanel($panelid, "$hacl_RightsContainer_1", "$hacl_RightsContainer_1");

    $html = <<<HTML
        <div id="SDRightsPanelContainer_$panelid"></div>
        <script>
            YAHOO.haloacl.loadContentToDiv('SDRightsPanelContainer_$panelid','rightList',{panelid:'$panelid', type:'$type'});
        </script>
        <div class="haloacl_greyline">&nbsp;</div>
        <div>
            <div style="width:33%;float:left;"></div>
            <div style="width:33%;float:left;text-align:center"></div>
            <div style="width:33%;float:left;text-align:right"><input type="button" name="useTemplate" value="$hacl_RightsContainer_2" onclick="YAHOO.haloacl.buildTemplatePanelXML_$panelid();" /></div>
        </div>

        <script type="javascript">

            YAHOO.haloacl.buildTemplatePanelXML_$panelid = function(onlyReturnXML){

                var panelid = '$panelid';
                var checkedgroups = YAHOO.haloaclrights.getCheckedNodesFromRightsTree(YAHOO.haloaclrights.treeInstance$panelid, null);
                console.log(checkedgroups);

                var counter = 0;
                checkedgroups.each(function(actualtemplate){

                    // building xml
                    var xml = "<?xml version=\"1.0\"  encoding=\"UTF-8\"?>";
                    xml+="<inlineright>";
                    xml+="<panelid>$panelid"+"_templatecount_"+counter+"</panelid>";
                    xml+="<type>template</type>";

                    xml+="<name>"+actualtemplate+"</name>";

                    xml+="</inlineright>";

                    var callback3 = function(result){
                        console.log(result);
                    };
                    YAHOO.haloacl.sendXmlToAction(xml,'saveTempRightToSession',callback3);
                    counter++;
                });
                genericPanelSetSaved_$panelid(true);
                YAHOO.haloacl.closePanel('$panelid');
            };

        </script>
HTML;

    $html = "<div id=\"content_".$panelid."\">".$html."</div>";

    $myGenericPanel->setContent($html);

    $response->addText($myGenericPanel->getPanel());
    return $response;

}


/**
 *
 * returns panel listing all existing rights
 *
 * @param <string>  unique identifier
 * @return <html>   returns the user/group-select tabview; e.g. contained in right panel
 */
function getSDRightsPanel($sdId, $readOnly = false) {


    if ($readOnly === "true") $readOnly = true;
    if ($readOnly === "false") $readOnly = false;

    $html = "";
    $response = new AjaxResponse();

    $SD = HACLSecurityDescriptor::newFromId($sdId);

    $tempRights = array();

    if ($readOnly) {
        $expandMode = "replace";
    } else {
        $expandMode = "expand";
    }

    //attach inline right texts
    foreach ($SD->getInlineRights(false) as $rightId) {

        $tempRight = HACLRight::newFromId($rightId);
        $html .= getRightsPanel("SDDetails_".$sdId."_".$rightId, 'individual', $readOnly, true, $rightId, $tempRight->getName(), HACLRight::newFromID($rightId)->getDescription());
    }

    //attach predefined right texts
    foreach ($SD->getPredefinedRights(false) as $subSdId) {
        $sdName = HACLSecurityDescriptor::nameForID($subSdId);

        $myGenericPanel = new HACL_GenericPanel("subRight_$subSdId", "[ Template: $sdName ]", "[ Template: $sdName ]", "", false, false, null, $expandMode);

        $temphtml = <<<HTML
        <div id="content_subRight_$subSdId">
        <div id="subPredefinedRight_$subSdId"></div>
        <script>
            YAHOO.haloacl.loadContentToDiv('subPredefinedRight_$subSdId','getSDRightsPanel',{sdId:'$subSdId', readOnly:'true'});
            //show closed at first
            YAHOO.haloacl.togglePanel('subRight_$subSdId');
        </script>
        </div>
HTML;
        $myGenericPanel->setContent($temphtml);
        $html .= $myGenericPanel->getPanel();

    }

    $html .= '<div class="haloacl_greyline">&nbsp;</div>';
    #    $html .= getRightsPanel("SDDetails_".$sdId."_modification", 'modification', $readOnly, true, $sdId, "Modification Right");


    $response->addText($html);
    return $response;

}


/**
 * function called intern (not ajax) to clear all saved right
 */
function clearTempSessionRights() {
    unset($_SESSION['temprights']);
}


function clearTempSessionGroup() {
    unset($_SESSION['tempgroups']);
}


function saveTempGroupToSession($groupxml) {
    $_SESSION['tempgroups'] = $groupxml;
    return wfMsg('hacl_saveTempGroup_1');
}


/**
 *
 * @param <string/xml>  right serialized as xml
 * @return <status>     200: ok / right saved to session
 *                      400: failure / rihght not saved to session (exception's message will be returned also)
 */
function saveTempRightToSession($rightxml) {
    $ajaxResponse = new AjaxResponse();
    try {

        $xml = new SimpleXMLElement($rightxml);

        $panelid = (string)$xml->panelid;
        #  $_SESSION['temprights'][$panelid] = $tempright;

        $_SESSION['temprights'][$panelid] = $rightxml;

/*
 *      actually done on clientside / javascript
 *      $autoGeneratedRightName = $autoGeneratedRightNameRights.$autoGeneratedRightName;
        if (strlen($autoGeneratedRightName) > 50) $autoGeneratedRightName = substr($autoGeneratedRightName,0,50)."...";

        if ($autoDescription == "on") $description = $autoGeneratedRightName;
*/
        $ajaxResponse->setContentType("json");
        $ajaxResponse->setResponseCode(200);
    //$ajaxResponse->addText("");

    } catch (Exception  $e) {

        $ajaxResponse->setResponseCode(400);
        $ajaxResponse->addText($e->getMessage());
    }
    return $ajaxResponse;

}


/**
 *
 * @param <string/xml>  SD name
 * @return <status>     200: ok / SD id
 *                      400: failure
 */
function sDpopupByName($sdName) {
    $ajaxResponse = new AjaxResponse();
    try {


        $tempSD = HACLSecurityDescriptor::newFromName($sdName);
        $ajaxResponse->addText($tempSD->getSDID());

        $ajaxResponse->setResponseCode(200);
    //$ajaxResponse->addText("");

    } catch (Exception  $e) {

        $ajaxResponse->setResponseCode(400);
        $ajaxResponse->addText($e->getMessage());
    }
    return $ajaxResponse;

}



/**
 *
 * @param <string/xml>  serialized form of a securitydescriptor
 */
function deleteSecurityDescriptor($sdId) {

    $ajaxResponse = new AjaxResponse();
    try {

        HACLSecurityDescriptor::newFromID($sdId)->delete();

        $ajaxResponse->setContentType("json");
        $ajaxResponse->setResponseCode(200);
        $ajaxResponse->addText(wfMsg('hacl_deleteSecurityDescriptor_1'));

    } catch (Exception  $e) {
        $ajaxResponse = new AjaxResponse();
        $ajaxResponse->setResponseCode(400);
        $ajaxResponse->addText($e->getMessage());
    }
    return $ajaxResponse;

}


/**
 *
 * @param <string/xml>  serialized form of a securitydescriptor
 */
function saveSecurityDescriptor($secDescXml) {
    global $wgUser;

    try {
        $inline = "";

        $modificationSaved = false;
        // building rights
        foreach($_SESSION['temprights'] as $tempright) {

            $xml = new SimpleXMLElement($tempright);
            $actions = 0;
            $groups = '';
            $users = '';
            $type = $xml->type;

            if ($type == "template") {
                $inline .= '
{{#predefined right:rights='.$xml->name.'}}';
            } else {
                $description = $xml->description ? $xml->description : '';
                $autoDescription = $xml->autoDescription ? $xml->autoDescription : '';
                $rightName = $xml->name ? $xml->name : '';

                switch ($type) {
                    case "privateuse":
                    case "individual":
                    case "private":
                        foreach($xml->xpath('//group') as $group) {
                            if($groups == '') {
                                $groups = (string)$group;
                            }else {
                                $groups = $groups.",".(string)$group;
                            }
                        }
                        foreach($xml->xpath('//user') as $user) {
                            if($users == '') {
                                $users = 'User:'.(string)$user;
                            }else {
                                $users = $users.",".'User:'.(string)$user;
                            }
                        }
                        break;
                    case "modification":
                        $foundModrights = false;
                        foreach($xml->xpath('//group') as $group) {
                            $foundModrights = true;
                            if($groups == '') {
                                $groups = (string)$group;
                            }else {
                                $groups = $groups.",".(string)$group;
                            }
                        }
                        foreach($xml->xpath('//user') as $user) {
                            $foundModrights = true;
                            if($users == '') {
                                $users = 'User:'.(string)$user;
                            }else {
                                $users = $users.",".'User:'.(string)$user;
                            }

                        }
                        if(!$foundModrights) {
                            $users = "Users:".$wgUser->getName();
                        }
                        break;
                    case "allusersregistered":
                        $users = "#";
                        break;
                    case "allusersanonymous":
                        $users = "*";
                        break;
                    case "allusers":
                        $users = "*,#";
                        break;
                }

                $actions2 = "";
                foreach($xml->xpath('//right') as $right) {
                //$actions = $actions + (int)HACLRight::getActionID($right);
                    if((string)$right != "fullaccess") {
                        if($actions2 == '') {
                            $actions2 = (string)$right;
                        }else {
                            $actions2 .= ",".(string)$right;
                        }
                    }
                }
                foreach($xml->xpath('//right') as $right) {
                    if ($right <> '') {
                        $actions = $actions + (int)HACLRight::getActionID($right);
                    }
                }

                if ($type <> "modification") {
                //normal rights
                    $inline .= '
{{#access: assigned to=';
                    if ($groups <> '') $inline .= $groups;
                    if (($users <> '') && ($groups <> '')) $inline .= ','.$users;
                    if (($users <> '') && ($groups == '')) $inline .= $users;
                    $inline .= ' |actions='.$actions2.' |description='.$description.' |name='.$rightName.'}}';

                } else {
                //modification rights
                    $inline .= '
{{#manage rights:assigned to=';
                    if ($groups <> '') $inline .= $groups;
                    if (($users <> '') && ($groups <> '')) $inline .= ','.$users;
                    if (($users <> '') && ($groups == '')) $inline .= $users;

                    $inline .='}}';
                    $modificationSaved = true;
                }

                //line break
                $inline .= <<<HTML

HTML;

            }

        } // end of foreach
        if($modificationSaved == false) {
            $inline .= '
{{#manage rights:assigned to=User:'.$wgUser->getName()."}}";
        }

        //get manage rights
        $secDescXml = new SimpleXMLElement($secDescXml);

        /*
        foreach($secDescXml->xpath('//group') as $group) {
            if($sdgroups == '') {
                $sdgroups = (string)$group;
            }else {
                $sdgroups .= ",".(string)$group;
            }
        }
        foreach($secDescXml->xpath('//user') as $user) {
            if($sdusers == '') {
                $sdusers = 'User:'.(string)$user;
            }else {
                $sdusers .= ",".'User:'.(string)$user;
            }
        }
         *
         */

        //complete inline code
        #        echo "======$type======";

        $aclType = (String)$secDescXml->ACLType;

        if ($aclType == "createACL") {
            $inline .= '
[[Category:ACL/ACL]]';
        }else if($aclType == "createAclUserTemplate") {
                $inline .= '
[[Category:ACL/Template]]';
            }else {
                $inline .= '
[[Category:ACL/Right]]';
            }


        $SDName = $secDescXml->name;
        /*
        switch ($secDescXml->protect) {
            case "page":$peType = "Page";break;
            case "property":$peType = "Property";break;
            case "namespace":$peType = "Namespace";break;
            case "category":$peType = "Category";break;
        }

        global $wgUser;

        switch ($secDescXml->ACLType) {
            case "createACL":$aclName = 'ACL:'.$peType.'/'.$SDName;break;
            case "createAclTemplate":$aclName = 'ACL:Template/'.$SDName;break;
            case "createAclUserTemplate":$aclName = 'ACL:Template/'.$wgUser->getName(); break;
            case "all_edited":$aclName = $SDName; break; //Name already existing - reuse
        }

         */
        $aclName = (string)$SDName;

        // create article for security descriptor

        $sdarticle = new Article(Title::newFromText($aclName));


        $sdarticle->doEdit($inline, "");
        $SDID = $sdarticle->getID();

        $articlename = preg_replace("/ACL:Page\//is", "", $aclName);

        $ajaxResponse = new AjaxResponse();
        $ajaxResponse->setContentType("json");
        $ajaxResponse->setResponseCode(200);
        $ajaxResponse->addText($articlename);

    } catch (Exception  $e) {
        $ajaxResponse = new AjaxResponse();
        $ajaxResponse->setResponseCode(400);
        $ajaxResponse->addText($e->getMessage());
    }
    return $ajaxResponse;

}


function saveGroup($manageRightsXml,$parentgroup = null) {
    $groupXml = $_SESSION['tempgroups'];
    $groups = "";
    $users = "";
    $mrgroups = "";
    $mrusers = "";

    try {
        /* create group inline from 2 xmls */
    //get group members
        $groupXml = new SimpleXMLElement($groupXml);
        // securitydescriptor-part
        foreach($groupXml->xpath('//group') as $group) {
            if(trim($group) != "") {
                if($groups == '') {
                    $groups = (string)$group;
                }else {
                    $groups .= ",".(string)$group;
                }
            }
        }
        foreach($groupXml->xpath('//user') as $user) {
            if(trim($user)!="") {
                if($users == '') {
                    $users = 'User:'.(string)$user;
                }else {
                    $users .= ",".'User:'.(string)$user;
                }
            }
        }

        //get manage rights
        $manageRightsXml = new SimpleXMLElement($manageRightsXml);
        // securitydescriptor-part
        foreach($manageRightsXml->xpath('//group') as $group) {
            if(trim($group)) {
                if($mrgroups == '') {
                    $mrgroups = (string)$group;
                }else {
                    $mrgroups .= ",".(string)$group;
                }
            }
        }
        foreach($manageRightsXml->xpath('//user') as $user) {
            if(trim($user) !="") {
                if($mrusers == '') {
                    $mrusers = 'User:'.(string)$user;
                }else {
                    $mrusers .= ",".'User:'.(string)$user;
                }
            }
        }

        $groupName = $groupXml->name;

        // create article for security descriptor
        $sdarticle = new Article(Title::newFromText('ACL:'.'Group'.'/'.$groupName));
        if($users == "") {
            $inline = '
{{#member:members='.$groups.'}}';
        }elseif($groups == "") {
            $inline = '
{{#member:members='.$users.'}}';
        }else {
            $inline = '
{{#member:members='.$users.','.$groups.'}}';
        }

        if($mrgroups == "") {
            $inline .= '
{{#manage group:assigned to='.$mrusers.'}}
        [[Category:ACL/Group]]';
        }elseif($mrusers == "") {
            $inline .= '
{{#manage group:assigned to='.$mrgroups.'}}
        [[Category:ACL/Group]]';
        }else {
            $inline .= '
{{#manage group:assigned to='.$mrgroups.','.$mrusers.'}}
        [[Category:ACL/Group]]';

        }


        $sdarticle->doEdit($inline, "");
        $SDID = $sdarticle->getID();

        // as a new article starts we have to reset the parser
        HACLParserFunctions::getInstance()->reset();

        // new group saved
        // if new group is a subgroup we have to attach it to that
        if($parentgroup) {
        // now we have to edit the parentgroup's definition

            $parentGroupArray = readGroupDefinition($parentgroup);
            $parentgrouparticle = new Article(Title::newFromText("ACL:".$parentgroup));
            #echo ("opening article with title:ACL:".$parentgroup);

            // building new arent inline
            $parent_memuser = "";
            // setting the new group as first member
            $parent_memgroup = 'Group'.'/'.$groupName;
            $parent_user = "";
            $parent_group = "";
            if(isset($parentGroupArray['members']['group'])) {
                foreach($parentGroupArray['members']['group'] as $group) {
                    if(trim($group)) {
                        if($parent_memgroup == '') {
                            $parent_memgroup = (string)$group;
                        }else {
                            $parent_memgroup .= ",".(string)$group;
                        }
                    }
                }
            }
            if(isset($parentGroupArray['members']['user'])) {
                foreach($parentGroupArray['members']['user'] as $user) {
                    if(trim($user) !="") {
                        if($parent_memuser == '') {
                            $parent_memuser = 'User:'.(string)$user;
                        }else {
                            $parent_memuser .= ",".'User:'.(string)$user;
                        }
                    }
                }
            }
            if(isset($parentGroupArray['members']['user'])) {

                foreach($parentGroupArray['members']['user'] as $user) {
                    if(trim($user)) {
                        if($parent_user == '') {
                            $parent_user = 'User:'.(string)$user;
                        }else {
                            $parent_user .= ",".'User:'.(string)$user;
                        }
                    }
                }
            }
            if(isset($parentGroupArray['manage']['group'])) {
                foreach($parentGroupArray['manage']['group'] as $group) {
                    if(trim($group) !="") {
                        if($parent_group == '') {
                            $parent_group = (string)$group;
                        }else {
                            $parent_group .= (string)$group;
                        }
                    }
                }
            }
            if($parent_memuser == "") {
                $newparentinline = '
{{#member:members='.$parent_memgroup.'}}';
            }elseif($parent_memgroup == "") {
                $newparentinline = '
{{#member:members='.$parent_memuser.'}}';
            }else {
                $newparentinline = '
{{#member:members='.$parent_memuser.','.$parent_memgroup.'}}';
            }

            if($parent_group == "") {
                $newparentinline .= '
{{#manage group:assigned to='.$parent_user.'}}
        [[Category:ACL/Group]]';
            }elseif($parent_user == "") {
                $newparentinline .= '
{{#manage group:assigned to='.$parent_group.'}}
        [[Category:ACL/Group]]';
            }else {
                $newparentinline .= '
{{#manage group:assigned to='.$parent_group.','.$parent_user.'}}
        [[Category:ACL/Group]]';

            }

            //echo ("trying to insert following inline:".$newparentinline);
            $parentgrouparticle->doEdit($newparentinline,"");


        }
        $ajaxResponse = new AjaxResponse();
        $ajaxResponse->setContentType("json");
        $ajaxResponse->setResponseCode(200);
        $ajaxResponse->addText("descriptor saved".$SDID );

    } catch (Exception  $e) {
        $ajaxResponse = new AjaxResponse();
        $ajaxResponse->setResponseCode(400);
        $ajaxResponse->addText($e->getMessage());
    }
    return $ajaxResponse;

}

function readGroupDefinition($groupName) {
    $result = array();
    $group = HACLGroup::newFromName($groupName);
    $temp = $group->getGroups(HACLGroup::OBJECT);
    foreach($temp as $item) {
        $result['members']['group'][] = $item->getGroupName();
    }
    $temp = null;
    $temp = $group->getUsers(HACLGroup::OBJECT);
    foreach($temp as $item) {
        $result['members']['user'][] = $item->getName();
    }
    $temp = null;
    $temp = $group->getManageGroups(HACLGroup::OBJECT);

    foreach($temp as $item) {
        $result['manage']['group'][] = $item->getGroupName();
    }
    $temp = null;
    $temp = $group->getManageUsers(HACLGroup::OBJECT);
    foreach($temp as $item) {
        $db =& wfGetDB( DB_SLAVE );
        $gt = $db->tableName('user');
        $sql = "SELECT * FROM user where user_id = ".$item;
        $res = $db->query($sql);
        $row = $db->fetchObject($res);
        $result['manage']['user'][] = $row->user_name;
    }

    return $result;
}


function saveWhitelist($whitelistXml) {

    try {
    //get group members
        $oldWhitelists = HACLWhitelist::newFromDB();
        $pages = "";

        foreach($oldWhitelists->getPages() as $item) {
            if($pages == '') {
                $pages = (string)$item;
            }else {
                $pages .= ",".(string)$item;
            }
        }

        $whitelistXml = new SimpleXMLElement($whitelistXml);
        foreach($whitelistXml->xpath('//page') as $page) {
            if($pages == '') {
                $pages = (string)$page;
            }else {
                $pages .= ",".(string)$page;
            }
        }


        // create article
        $sdarticle = new Article(Title::newFromText('ACL:'.'Whitelist'));
        $inline = '{{#whitelist:pages='.$pages.'}}';

        $sdarticle->doEdit($inline, "");
        $SDID = $sdarticle->getID();

        $ajaxResponse = new AjaxResponse();
        $ajaxResponse->setContentType("json");
        $ajaxResponse->setResponseCode(200);
        $ajaxResponse->addText($inline );

    } catch (Exception   $e) {
        $ajaxResponse = new AjaxResponse();
        $ajaxResponse->setResponseCode(400);
        $ajaxResponse->addText($e->getMessage());
    }
    return $ajaxResponse;

}

/**
 *
 * @param <String>  Document Name Substring
 * @return <JSON>   return array of documents
 */
function getAutocompleteDocuments($subName) {

    $a = array();
    $a['records'] =  HACLStorage::getDatabase()->getArticles($subName,true);
    return(json_encode($a));

}


/**
 *
 * @param <String>  selected group in tree
 * @param <String>  column to sort by
 * @param <String>  sort-direction
 * @param <Int>     first index of resultlist (paging)
 * @param <Int>     total results (paging)
 * @return <JSON>   return array of users
 */
function getUsersForUserTable($selectedGroup,$sort,$dir,$startIndex,$results,$filter) {

    global $wgUser;
    global $wgTitle;
    $a = array();
    $a['recordsReturned'] = 10;
    #$a['totalrecords'] = 0;
    $a['startIndex'] = $startIndex;
    $a['sort'] = $sort;
    $a['dir'] = $dir;
    $a['pageSize'] = 10;

    $tmpstring = "";

    if ($selectedGroup == 'all' || $selectedGroup == '') {

        $db =& wfGetDB( DB_SLAVE );
        $gt = $db->tableName('user');
        $sql = "SELECT * FROM $gt order by user_name";

        $res = $db->query($sql);
        while ($row = $db->fetchObject($res)) {
            $tmpstring = "";
            $tmlGroups = HACLGroup::getGroupsOfMember($row->user_id);
            foreach ($tmlGroups as $key => $val) {
                if(!strpos($tmpstring, $val["name"])) {
                    $tmpstring .= $val["name"].",";
                }
            }
            //$tmpstring = '<br /><span style="font-size:8px;">'.$tmpstring."</span>";

            $a['records'][] = array('name'=>$row->user_name,'groups'=>$tmpstring,'id'=>$row->user_id,'checked'=>'false');
        }

        $db->freeResult($res);

    } else {
        $group = HACLGroup::newFromName($selectedGroup);
        $groupUsers = $group->getUsers(HACLGroup::OBJECT);
        foreach ($groupUsers as $key => $val) {
            $tempgroup = array();
            foreach(HACLGroup::getGroupsOfMember($val->getId()) as $blubb) {
                $tempgroup[] = $blubb['name'];
            }
            $a['records'][] = array('name'=>$val->getName(),'id'=>$val->getId(),'checked'=>'false', 'groups'=>$tempgroup);

        }
    }

    // doing filtering php-based
    if($filter !="" && $filter != null) {
        $filteredResults = array();
        $pattern = "/".$filter."/is";
        foreach($a['records'] as $record) {
            if(preg_match($pattern, $record["name"])) {
                $filteredResults[] = $record;
            }
        }
        $a['records'] = $filteredResults;
    }
    #print_r($a['records']);

    // generating paging-stuff
    $a['totalRecords'] = sizeof($a['records']);
    $a['records'] = array_slice($a['records'],$startIndex,$a['pageSize']);
    $a['recordsReturned'] = sizeof($a['records']);

    return(json_encode($a));

}


/**
 *
 * @param <String>  groups  (list -> csv!!!)
 *
 * @return <JSON>   return array of users
 */
function getUsersForGroups($groupsstring) {
    if($groupsstring == "") {
        return json_encode(array());
    }
    global $wgUser;
    global $wgTitle;
    $a = array();

    $groupsarray = explode(",",$groupsstring);
    $result = array();

    foreach($groupsarray as $group) {
        $group = HACLGroup::newFromName($group);
        $groupUsers = $group->getUsers(HACLGroup::OBJECT);
        $finalGroupusers = array();
        foreach($groupUsers as $user) {
            $temp["id"] = $user->getId();
            $temp["name"] = $user->getName();
            $finalGroupuser[] = $temp;
        }

    }
    foreach($finalGroupuser as $user) {
        $reallyAddToArray = true;
        foreach($result as $test) {
            if($test["name"] == $user["name"]) {
                $reallyAddToArray = false;
            }
        }
        if($reallyAddToArray) {
            $tmpstring = "";
            $tmlGroups = HACLGroup::getGroupsOfMember($user["id"]);
            foreach ($tmlGroups as $key => $val) {
                $tmpstring .= $val["name"].",";
            }
            $temp = array('name'=>$user['name'],'groups'=>$tmpstring);
            $result[] = $temp;
        }
    }

    return(json_encode($result));

}


/**
 *
 * @param <String>  selected group
 * @return <JSON>   json from first-level-childs of the query-group; not all childs!
 */
function getGroupsForRightPanel($clickedGroup, $search=null, $recursive=false, $level=0) {
    $array = array();


    if($search == null || $search == "") {
    // return first level
        if($clickedGroup == 'all' || $clickedGroup == "Groups") {
        //get level 0 groups
            $groups = HACLStorage::getDatabase()->getGroups();
            foreach( $groups as $key => $value) {

                if ($recursive) {
                    $subgroups = getGroupsForRightPanel($value->getGroupName(), $search, true, $level+1);
                    $tempgroup = array('name'=>$value->getGroupName(),'id'=>$value->getGroupId(),'checked'=>'false', 'children'=>$subgroups);
                } else {
                    $tempgroup = array('name'=>$value->getGroupName(),'id'=>$value->getGroupId(),'checked'=>'false');
                }
                $array[] = $tempgroup;
            }

        }else {
        //get group by name
            $parent = HACLGroup::newFromName($clickedGroup);
            //groups
            $groups = $parent->getGroups(HACLGroup::OBJECT);
            foreach( $groups as $key => $value ) {
                $tempgroup = array('name'=>$value->getGroupName(),'id'=>$value->getGroupId(),'checked'=>'false');
                $array[] = $tempgroup;
            }
        }

    }else {
    // performing search
        $groups = HACLStorage::getDatabase()->getGroups();
        foreach( $groups as $key => $value) {
        //print_r($value);
            $subgroups = array();
            //$subgroups = getGroupsForRightPanel($value->getGroupName(), $search, true, $level+1);
            if(strpos($value->getGroupName(),$search) || sizeof($subgroups)> 0) {
                $tempgroup = array('name'=>$value->getGroupName(),'id'=>$value->getGroupId(),'checked'=>'false', 'children'=>$subgroups);
                $array[] = $tempgroup;
            }
        }
    }

    //only json encode final result
    if ($level == 0) {
        $array = (json_encode($array));
    }

    return $array;

}

function getGroupsForManageUser($query) {
    ini_set("display_errors",0);

    $array = array();

    // return first level
    if($query == 'all') {

    //get level 0 groups
        $groups = HACLStorage::getDatabase()->getGroups();
        foreach( $groups as $key => $value) {
            $group = HACLGroup::newFromID($value->getGroupId());

            $tempgroup = array('name'=>$value->getGroupName(),'id'=>$value->getGroupId(),'checked'=>'false');
            $array[] = $tempgroup;

        }

    }else {

    //get group by name
        try {
            $parent = HACLGroup::newFromName($query);

            //groups
            $groups = $parent->getGroups(HACLGroup::OBJECT);
            foreach( $groups as $key => $value ) {
                $tempgroup = array('name'=>$value->getGroupName(),'id'=>$value->getGroupId(),'checked'=>'false');
                $array[] = $tempgroup;
            }

        /*

        //users
        $users = $parent->getUsers(HACLGroup::OBJECT);
        foreach( $users as $key => $value ) {
            $tempgroup = array('name'=>$value->getName(),'id'=>$value->getId(),'checked'=>'false');
            $array[] = $tempgroup;
        }
         *
         */
        }
        catch(Exception $e ) {
            $array = array();
        }
    }

    return (json_encode($array));
}


/**
 *
 * @param <String>  selected group
 * @return <JSON>   json from first-level-childs of the query-group; not all childs!
 */
function getUsersWithGroups() {
    return (json_encode(HACLStorage::getDatabase()->getUsersWithGroups()));
}



/**
 *
 * Delivers per-type-filtered ACLs for the Manage ACLs view
 * for ACL management, user template views
 *
 * @param <XML>     selected types
 * @return <JSON>   json from first-level-childs of the query-group; not all childs!
 */
function getACLs($typeXML) {
    global $wgUser;
    global $haclCrossTemplateAccess;
    $username = $wgUser->getName();

    $types = array();

    if($typeXML == "all") {
        $types[] = "all";
    }else {
        $typeXML = new SimpleXMLElement($typeXML);
        foreach($typeXML->xpath('//type') as $type) {
            $types[] = $type;
        }
    }

    $array = array();

    $SDs = HACLStorage::getDatabase()->getSDs($types);
    foreach( $SDs as $key => $SD) {
        if(preg_match("/Template/is",$SD->getSDName())) {
            if(($SD->getSDName() == "Template/$username") || (array_intersect($wgUser->getGroups(), $haclCrossTemplateAccess) != null)) {
                $tempRights = array();
                foreach ($SD->getInlineRights(false) as $rightId) {
                    try {
                        $tempright = HACLRight::newFromID($rightId);
                        $tempRights[] = array('id'=>$rightId, 'name'=>$tempright->getName(),'description'=>$tempright->getDescription());
                    }catch(Exception $e ) {}
                }
                foreach ($SD->getPredefinedRights(false) as $subSdId) {
                    try {
                        $tempright = HACLSecurityDescriptor::newFromID($subSdId);
                        $tempRights[] = array('id'=>$subSdId, 'description'=>"Template - ".$tempright->getSDName());
                    }catch(Exception $e ) {}
                }
                $tempSD = array('id'=>$SD->getSDID(), 'name'=>$SD->getSDName(), 'rights'=>$tempRights);
                $array[] = $tempSD;
            }

        }else {
            $tempRights = array();
            foreach ($SD->getInlineRights(false) as $rightId) {
                try {
                    $tempright = HACLRight::newFromID($rightId);
                    $tempRights[] = array('id'=>$rightId, 'name'=>$tempright->getName(),'description'=>$tempright->getDescription());
                }catch(Exception $e ) {}
            }
            foreach ($SD->getPredefinedRights(false) as $subSdId) {
                try {
                    $tempright = HACLSecurityDescriptor::newFromID($subSdId);
                    $tempRights[] = array('id'=>$subSdId, 'description'=>"Template - ".$tempright->getSDName());
                }catch(Exception $e ) {}
            }
            $tempSD = array('id'=>$SD->getSDID(), 'name'=>$SD->getSDName(), 'rights'=>$tempRights);
            $array[] = $tempSD;
        }
    }

    return (json_encode($array));

}


/**
 *
 * Delivers an ACL incl. rights selected by name of descriptor article
 * for ACL management, user template views
 *
 * @param <String>  name of ACL Descriptor article
 * @return <JSON>   json from first-level-childs of the query-group; not all childs!
 */
function getACLByName($name) {

    $array = array();

    $SD = HACLSecurityDescriptor::newFromName($name);

    $tempRights = array();

    //attach inline right texts
    foreach (getInlineRightsOfSDs(array($SD->getSDID())) as $key2 => $rightId) {
        $tempright = HACLRight::newFromID($rightId);
        $tempRights[] = array('id'=>$rightId, 'description'=>$tempright->getDescription());
    }

    $tempSD = array('id'=>$SD->getSDID(), 'name'=>$SD->getSDName(), 'rights'=>$tempRights);
    $array[] = $tempSD;


    return (json_encode($array));
}


/**
 *
 * Delivers an ACL incl. rights selected by id of descriptor
 *
 * @param <int>  id of ACL Descriptor
 * @return <JSON>   json from first-level-childs of the query-group; not all childs!
 */
function getACLById($id) {

    $array = array();

    $SD = HACLSecurityDescriptor::newFromId($id);

    $tempRights = array();

    //attach inline right texts
    foreach (getInlineRightsOfSDs(array($SD->getSDID())) as $key2 => $rightId) {
        $tempright = HACLRight::newFromID($rightId);
        $tempRights[] = array('id'=>$rightId, 'description'=>$tempright->getDescription());
    }

    $tempSD = array('id'=>$SD->getSDID(), 'name'=>$SD->getSDName(), 'rights'=>$tempRights);
    $array[] = $tempSD;


    return (json_encode($array));
}



/**
 *
 * Delivers whitelist pages for Manage Whitelist view
 *
 * @return <JSON>   json from first-level-childs of the query-group; not all childs!
 */
function getWhitelistPages($query,$sort,$dir,$startIndex,$results,$filter) {

    $a = array();
    $a['recordsReturned'] = 2;
    $a['totalRecords'] = 10;
    $a['startIndex'] = 0;
    $a['sort'] = null;
    $a['dir'] = "asc";
    $a['pageSize'] = 5;

    $test = HACLWhitelist::newFromDB();

    $a['records'] = array();
    foreach($test->getPages() as $item) {
        if($query == "all" || preg_match('/'.$query.'/is',$item)) {
            $a['records'][] = array('name'=>$item,'checked'=>'false');
        }
    }
    // generating paging-stuff
    $a['totalRecords'] = sizeof($a['records']);
    $a['records'] = array_slice($a['records'],$startIndex,$a['pageSize']);
    $a['recordsReturned'] = sizeof($a['records']);

    return(json_encode($a));

}




/**
 *
 * @return <html>   returns tab-content for manageUser-tab
 */
function manageUserContent() {
    clearTempSessionGroup();

    $response = new AjaxResponse();

    $hacl_manageUser_1 = wfMsg('hacl_manageUser_1');
    $hacl_manageUser_2 = wfMsg('hacl_manageUser_2');
    $hacl_manageUser_3 = wfMsg('hacl_manageUser_3');
    $hacl_manageUser_4 = wfMsg('hacl_manageUser_4');
    $hacl_manageUser_5 = wfMsg('hacl_manageUser_5');
    $hacl_manageUser_6 = wfMsg('hacl_manageUser_6');
    $hacl_manageUser_7 = wfMsg('hacl_manageUser_7');
    $hacl_manageUser_8 = wfMsg('hacl_manageUser_8');
    $hacl_manageUser_9 = wfMsg('hacl_manageUser_9');
    $hacl_manageUser_10 = wfMsg('hacl_manageUser_10');


    $dutHeadline = wfMsg('hacl_create_acl_dut_headline');
    $dutInfo = wfMsg('hacl_create_acl_dut_info');
    $dutGeneralHeader = wfMsg('hacl_create_acl_dut_general');
    $dutGeneralDefine = wfMsg('hacl_create_acl_dut_general_definefor');
    $dutGeneralDefinePrivate = wfMsg('hacl_create_acl_dut_general_private_use');
    $dutGeneralDefineAll = wfMsg('hacl_create_acl_dut_general_all');
    $dutGeneralDefineSpecific = wfMsg('hacl_create_acl_dut_general_specific');
    $dutRightsHeader = wfMsg('hacl_create_acl_dut_rights');

    $dutRightsButtonCreate = wfMsg('hacl_create_acl_dut_button_create_right');
    $dutRightsButtonAddTemplate = wfMsg('hacl_create_acl_dut_button_add_template');
    $dutRightsLegend = wfMsg('hacl_create_acl_dut_new_right_legend');
    $dutRightsLegendSaved = wfMsg('hacl_create_acl_dut_new_right_legend_status_saved');
    //$dutRightsLegendNotSaved = wfMsg('hacl_create_acl_dut_new_right_legend_status_notsaved');
    $dutRightsName = wfMsg('hacl_create_acl_dut_new_right_name');
    $dutRightsDefaultName = wfMsg('hacl_create_acl_dut_new_right_defaultname');
    $dutRights = wfMsg('hacl_create_acl_dut_new_right_rights');
    $dutRightsFullAccess = wfMsg('hacl_create_acl_dut_new_right_fullaccess');
    $dutRightsRead = wfMsg('hacl_create_acl_dut_new_right_read');
    $dutRightsEWF = wfMsg('hacl_create_acl_dut_new_right_ewf');
    $dutRightsEdit = wfMsg('hacl_create_acl_dut_new_right_edit');
    $dutRightsCreate = wfMsg('hacl_create_acl_dut_new_right_create');
    $dutRightsMove = wfMsg('hacl_create_acl_dut_new_right_move');
    $dutRightsDelete = wfMsg('hacl_create_acl_dut_new_right_delete');
    $dutRightsAnnotate = wfMsg('hacl_create_acl_dut_new_right_annotate');

    $html = <<<HTML
        <div class="haloacl_manageusers_container">
            <div class="haloacl_manageusers_title">
        $hacl_manageUser_1
            </div>
            <div class="haloacl_manageusers_subtitle">
        $hacl_manageUser_2
            </div>
HTML;

    $panelContent = <<<HTML
        <div id="content_manageUsersPanel">
        <div id="haloacl_manageuser_contentmenu">
            <div id="haloacl_manageuser_contentmenu_title">
        $hacl_manageUser_3
            </div>
            <div id="haloacl_manageuser_contentmenu_element">
                <a disabled="" id="haloacl_managegroups_newsubgroup" href="javascript:YAHOO.haloacl.manageUser.addNewSubgroup(YAHOO.haloacl.treeInstancemanageuser_grouplisting.getRoot(),YAHOO.haloacl.manageUser_selectedGroup);">$hacl_manageUser_4</a>
            </div>
            <div id="haloacl_manageuser_contentmenu_element">
                <a disabled="" href="javascript:YAHOO.haloacl.manageUser.addNewSubgroupOnSameLevel(YAHOO.haloacl.treeInstancemanageuser_grouplisting.getRoot(),YAHOO.haloacl.manageUser_selectedGroup);">$hacl_manageUser_5</a>
            </div>
        </div>
        <div id="haloacl_manageuser_contentlist">
            <div id="manageuser_grouplisting">
                <div id="haloacl_manageuser_contentlist_title">
        $hacl_manageUser_6<span style="margin-left:282px">edit</span><span style="margin-left:65px">delete</span>
                </div>
                <div id="haloacl_manageuser_contentlist_title">
                    Filter:&nbsp;<input class="haloacl_filter_input" onKeyup="YAHOO.haloacl.manageUserRefilter(this);"/>
                </div>
                <div id="treeDiv_manageuser_grouplisting">
                </div>
                <div id="haloacl_manageuser_contentlist_footer">
                    <input type="button" onClick="YAHOO.haloacl.manageACLdeleteCheckedGroups();" value="$hacl_manageUser_7" />
                </div>
            </div>
        </div>
    </div>
        <script>
            // treeview part - so the left part of the select/deselct-view
            YAHOO.haloacl.manageUser_selectedGroup = "";

            YAHOO.haloacl.treeInstancemanageuser_grouplisting = new YAHOO.widget.TreeView("treeDiv_manageuser_grouplisting");
            YAHOO.haloacl.treeInstancemanageuser_grouplisting.labelClickAction = 'YAHOO.haloacl.manageUser_handleGroupSelect';

            YAHOO.haloacl.manageUser_handleGroupSelect = function(groupname){
                if(YAHOO.haloacl.debug) console.log(groupname);
                $$('.manageUser_highlighted').each(function(item){
                    item.removeClassName("manageUser_highlighted");
                });
                try{
                var element = $('manageUserRow_'+groupname);
                if(YAHOO.haloacl.debug) console.log(element);
                element.parentNode.parentNode.parentNode.parentNode.addClassName("manageUser_highlighted");
                }catch(e){}
                YAHOO.haloacl.manageUser_selectedGroup = groupname;
              
            };

            YAHOO.haloacl.manageUser.buildTreeFirstLevelFromJson(YAHOO.haloacl.treeInstancemanageuser_grouplisting);

            YAHOO.haloacl.manageACLdeleteCheckedGroups = function(){
                var checkedgroups = YAHOO.haloacl.getCheckedNodesFromTree(YAHOO.haloacl.treeInstancemanageuser_grouplisting, null);

                var xml = "<?xml version=\"1.0\"  encoding=\"UTF-8\"?>";
                xml += "<groupstodelete>";
                for(i=0;i<checkedgroups.length;i++){
                    xml += "<group>"+checkedgroups[i]+"</group>";
                }
                xml += "</groupstodelete>";
                if(YAHOO.haloacl.debug) console.log(xml);

                var callback5 = function(result){
                    YAHOO.haloacl.notification.createDialogOk("content","Manage Groups",result.responseText,{
                        yes:function(){
                            window.location.href='?activetab=manageUsers';
                            }
                    });
                };

                YAHOO.haloacl.sendXmlToAction(xml,'deleteGroups',callback5);

            };

            YAHOO.haloacl.manageUserRefilter = function(element) {
                YAHOO.haloacl.filterNodes (YAHOO.haloacl.treeInstancemanageuser_grouplisting.getRoot(), element.value);
            }


        </script>
HTML;
    //$panelid, $name="", $title, $description = "", $showStatus = true,$showClose = true
    $myGenericPanel = new HACL_GenericPanel("manageUsersPanel","manageUsersPanel", "[ $hacl_manageUser_8 ]","",false,false);
    $myGenericPanel->setSaved(true);
    $myGenericPanel->setContent($panelContent);

    $html .= $myGenericPanel->getPanel();

    $html .= <<<HTML
        </div>
HTML;

    // ------ NOW STARTS THE EDITING PART ------
    $html .= <<<HTML
        <div id="haloacl_manageUser_editing_container" style="display:none">
            <div id="haloacl_manageUser_editing_title">
        $hacl_manageUser_9
            </div>
HTML;

    $groupPanelContent = <<<HTML
        <div id="content_manageUserGroupsettings">
            <div id="manageUserGroupSettingsRight">
            </div>
            <div id="manageUserGroupSettingsModificationRight" style="display:none">
                <script>
                    YAHOO.haloacl.loadContentToDiv('manageUserGroupSettingsRight','getRightsPanel',{panelid:'manageUserGroupSettingsRight',predefine:'individual'});
                </script>

                <div id="manageUserGroupSettingsModificationRight">
                </div>
                <script>
                    YAHOO.haloacl.loadContentToDiv('manageUserGroupSettingsModificationRight','getRightsPanel',{panelid:'manageUserGroupSettingsModificationRight',predefine:'modification'});
                </script>
            </div>
            <div id="manageUserGroupFinishButtons">
                <input id="haloacl_managegroups_save" type="button" value="$hacl_manageUser_10" onClick="YAHOO.haloacl.manageUsers_saveGroup();" />
            </div>
        </div>

        <script>
            YAHOO.haloacl.manageUsers_handleEdit = function(groupname){
                if(YAHOO.haloacl.debug) console.log("handle edit called for groupname:"+groupname);
                 new Ajax.Request('index.php?action=ajax&rs=getGroupDetails&rsargs[]='+groupname,
                        {
                        method:'post',
                        onSuccess:function(o){
                            var magic = YAHOO.lang.JSON.parse(o.responseText);
                            if(YAHOO.haloacl.debug) console.log(magic);
                            YAHOO.haloacl.loadContentToDiv('manageUserGroupSettingsRight','getManageUserGroupPanel',
                            {panelid:'manageUserGroupSettingsRight',name:magic['name'],description:'',users:magic['memberUsers'],groups:magic['memberGroups'],manageUsers:magic['manageUsers'],manageGroups:magic['manageGroups']});
                            $('haloacl_manageUser_editing_container').show();

                        }
                 });


                if(groupname.indexOf("new subgroup")> 0){
                    null;
                }else{
                    YAHOO.haloacl.loadContentToDiv('manageUserGroupSettingsRight','getManageUserGroupPanel',{panelid:'manageUserGroupSettingsRight'});
                    $('haloacl_manageUser_editing_container').show();
                }
            }

            YAHOO.haloacl.manageUsers_saveGroup = function(){
                if(YAHOO.haloacl.debug) console.log("modificationxml:");
                var modxml = YAHOO.haloacl.buildRightPanelXML_manageUserGroupSettingsModificationRight(true);
                if(YAHOO.haloacl.debug) console.log(modxml);
                var callback = function(result){
                            if(result.status == '200'){
                                //parse result
                                //YAHOO.lang.JSON.parse(result.responseText);
                                try{
                                    genericPanelSetSaved_manageUsersPanel(true);
                                    genericPanelSetName_manageUsersPanel("saved");
                                    genericPanelSetDescr_manageUsersPanel(result.responseText);
                                }catch(e){}
                                YAHOO.haloacl.notification.createDialogOk("content","Groups","Group has been saved",{
                                    yes:function(){
                                        window.location.href='?activetab=manageUsers';
                                    //YAHOO.haloacl.loadContentToDiv('manageUserGroupSettingsModificationRight','getRightsPanel',{panelid:'manageUserGroupSettingsModificationRight',predefine:'modification',readOnly:'true'});
                                    }
                                });


                    }else{
                        alert(result.responseText);
                    }
                };
                var parentgroup = YAHOO.haloacl.manageUser_parentGroup;

                        YAHOO.haloacl.sendXmlToAction(modxml,'saveGroup',callback, parentgroup);

        }
        </script>


HTML;

    $groupPanel = new HACL_GenericPanel("manageUserGroupsettings","manageUserGroupsettings", "Group","",true,false);
    $groupPanel->setSaved(false);
    $groupPanel->setContent($groupPanelContent);
    $html .= $groupPanel->getPanel();


    $html .= <<<HTML
        </div>
HTML;

    $response->addText($html);
    return $response;
}


/**
 *
 * @return <html>   returns content for whitelist-tab
 */
function whitelistsContent() {

    $hacl_whitelist_1 = wfMsg('hacl_whitelist_1');
    $hacl_whitelist_2 = wfMsg('hacl_whitelist_2');
    $hacl_whitelist_3 = wfMsg('hacl_whitelist_3');
    $hacl_whitelist_4 = wfMsg('hacl_whitelist_4');

    $response = new AjaxResponse();
    $html = <<<HTML
        <div class="haloacl_manageusers_container">
            <div class="haloacl_manageusers_title">
        $hacl_whitelist_1
            </div>
            <div class="haloacl_manageusers_subtitle">
        $hacl_whitelist_2
            </div>
HTML;
    //$myGenPanel = new HACL_GenericPanel("haloacl_whitelist_panel", "$hacl_whitelist_1", "$hacl_whitelist_1", "", false, false);
    $myGenPanelContent = <<<HTML
   <div id="content_manageUsersPanel">
        <div id="haloacl_whitelist_contentlist">

            <div id="manageuser_grouplisting">
            <div id="haloacl_manageuser_contentlist_title">
        $hacl_whitelist_3<span style="margin-right:40px;float:right">delete</span>
            </div>
                <div id="haloacl_manageuser_contentlist_title">
                    Filter:&nbsp;<input id="haloacl_whitelist_filterinput" class="haloacl_filter_input" onKeyup="YAHOO.haloacl.whitelistDatatableInstance.executeQuery(this.value);"/>
                </div>
            <div id="haloacl_whitelist_datatablecontainer">
                <div id="haloacl_whitelist_datatable" class="haloacl_whitelist_datatable yui-content">
                </div>
            </div>
            <div style="clear:both;border:1px solid;border-style:solid none none none;text-align:right;padding:3px 0">
                <input type="button" value="delete selected" onClick="YAHOO.haloacl.deleteWhitelist()"; />
            </div>
             
        </div>
   
    </div>
    <div style="clear:both">&nbsp;</div>

        $hacl_whitelist_4 &nbsp;
    <div style="clear:both">
        <input type="text" id="haloacl_whitelist_pagename" />
        <div id="whitelist_name_container"></div>
        <input style="margin-left:211px" type="button" value="add Page" onClick="YAHOO.haloacl.saveWhitelist();" />
    </div>
        
    <script type="javascript">
        YAHOO.haloacl.AutoCompleter('haloacl_whitelist_pagename', 'whitelist_name_container');
    </script>
 </div>
HTML;
    // $myGenPanel->setContent($myGenPanelContent);

    // $html .= $myGenPanel->getPanel();
    $html .= $myGenPanelContent;
    $html .= <<<HTML

    <script>
        YAHOO.haloacl.whitelistDatatableInstance = YAHOO.haloacl.whitelistTable('haloacl_whitelist_datatable','haloacl_whitelist_datatable');

        YAHOO.haloacl.saveWhitelist = function(){

            if(YAHOO.haloacl.debug) console.log("saveWhitelist called");
            var xml = "<?xml version=\"1.0\"  encoding=\"UTF-8\"?>";
            xml += "<whitelistContent>";
            xml += "<page>"+$('haloacl_whitelist_pagename').value+"</page>";
            xml += "</whitelistContent>";

            var callback4 = function(result){
                if(YAHOO.haloacl.debug) console.log("callback4 called");
                if(YAHOO.haloacl.debug) console.log(YAHOO.haloacl.whitelistDatatableInstance);
                YAHOO.haloacl.whitelistDatatableInstance.executeQuery("");

                if(YAHOO.haloacl.debug) console.log(result);
                $('haloacl_whitelist_pagename').value = "";
                if(YAHOO.haloacl.debug) console.log("callback4 end");
            };
            
            YAHOO.haloacl.sendXmlToAction(xml,'saveWhitelist',callback4);

        };

        YAHOO.haloacl.deleteWhitelist = function(){

            if(YAHOO.haloacl.debug) console.log("deleteWhitelist called");
            var xml = "<?xml version=\"1.0\"  encoding=\"UTF-8\"?>";
            xml += "<whitelistContent>";
            $$('.haloacl_whitelist_datatable_users').each(function(item){
                if(item.checked){
                    xml += "<page>"+item.name+"</page>";
                }
            });
            xml += "</whitelistContent>";

            var callback6 = function(result){
                YAHOO.haloacl.notification.createDialogOk("content","Whitelist","Page removed from whitelist",{
                    yes:function(){}
                });
                YAHOO.haloacl.whitelistDatatableInstance.executeQuery("");

            };
            
            YAHOO.haloacl.sendXmlToAction(xml,'deleteWhitelist',callback6);

        };


    </script>
</div>
HTML;

    $response->addText($html);
    return $response;
}


/**
 *
 * deletes items (groups or acls)
 *
 */
function deleteGroups($grouspXML, $type) {
    $result = "";
    $whitelistXml = new SimpleXMLElement($grouspXML);
    $result = wfMsg("hacl_nothing_deleted");
    foreach($whitelistXml->xpath('//group') as $group) {
        if($group != null) {
            try {
                $sdarticle = new Article(Title::newFromText('ACL:'.$group));
                $sdarticle->doDelete("gui-deletion");
                $result = wfMsg('hacl_deleteGroup_1');

            }catch(Exception $e ) {
                $result .= "Error while deleting ACL:".$group.". ";
            }
        }

    }


    // create article
    return $result;

}

function deleteWhitelist($whitelistXml) {

    try {

        $whitelists = array();
        $whitelistXml = new SimpleXMLElement($whitelistXml);
        foreach($whitelistXml->xpath('//page') as $page) {
            $whitelists[] = (string)$page;
        }

        print_r($whitelists);

        //get group members
        $oldWhitelists = HACLWhitelist::newFromDB();
        $pages = "";

        echo "oldwhite";
        print_r($oldWhitelists->getPages());

        foreach($oldWhitelists->getPages() as $item) {
            if(!in_array($item,$whitelists)) {
                if($pages == '') {
                    $pages = (string)$item;
                }else {
                    $pages .= ",".(string)$item;
                }
            }
        }

        // create article
        $sdarticle = new Article(Title::newFromText('ACL:'.'Whitelist'));
        if($pages == "") {
            $inline = '';
        }else {
            $inline = '{{#whitelist:pages='.$pages.'}}';
        }
        $sdarticle->doEdit($inline, "");
        $SDID = $sdarticle->getID();

        $ajaxResponse = new AjaxResponse();
        $ajaxResponse->setContentType("json");
        $ajaxResponse->setResponseCode(200);
        $ajaxResponse->addText($inline );

    } catch (Exception   $e ) {
        $ajaxResponse = new AjaxResponse();
        $ajaxResponse->setResponseCode(400);
        $ajaxResponse->addText($e->getMessage());
    }
    return $ajaxResponse;

}


function getGroupDetails($groupname) {
    $g = HACLGroup::newFromName($groupname);
    $result = array(
        'name'=>$g->getGroupName(),
        'memberUsers'=>$g->getUsers(HACLGroup::NAME),
        'memberGroups'=>$g->getGroups(HACLGroup::NAME),
        'manageUsers'=>array(),
        'manageGroups'=>array()
    );
    foreach($g->getManageUsers() as $id) {
        $db =& wfGetDB( DB_SLAVE );
        $gt = $db->tableName('user');
        $sql = "SELECT * FROM $gt ";
        $sql .= "WHERE user_id = ".$id;
        $res = $db->query($sql);
        while ($row = $db->fetchObject($res)) {
            $result['manageUsers'][] = $row->user_name;

        }
    }
    foreach($g->getManageGroups() as $id) {
        $result['manageGroups'][] = HACLGroup::newFromID($id)->getGroupName();
    }
    return json_encode($result);
}

function createQuickAclTab() {

    $hacl_quickACL_1 = wfMsg('hacl_quickACL_1');
    $hacl_quickACL_2 = wfMsg('hacl_quickACL_2');
    $hacl_quickACL_3 = wfMsg('hacl_quickACL_3');

    $response = new AjaxResponse();
    $html = <<<HTML
        <div class="haloacl_manageusers_container">
            <div class="haloacl_manageusers_title">
        $hacl_quickACL_1
            </div>
            <div class="haloacl_manageusers_subtitle">
        $hacl_quickACL_2
            </div>
HTML;
    //$myGenPanel = new HACL_GenericPanel("haloacl_quicklist_panel", $hacl_quickACL_1, $hacl_quickACL_1, "", false, false);
    $myGenPanelContent = <<<HTML

   <div id="content_haloacl_quicklist_panel">
        <div id="haloacl_whitelist_contentlist">
            <div id="manageuser_grouplisting">
            <div id="haloacl_manageuser_contentlist_title">
        $hacl_quickACL_3<span style="margin-left:415px">active</span>
            </div>
                <div id="haloacl_manageuser_contentlist_title">
                    Filter:&nbsp;<input class="haloacl_filter_input" onKeyup="YAHOO.haloacl.quickaclTableInstance.executeQuery(this.value);"/>
                </div>
            <div id="haloacl_whitelist_datatablecontainer">
                <div id="haloacl_quickacl_datatable" class="haloacl_whitelist_datatable yui-content">
                </div>
            </div>
            <div style="clear:both;border:1px solid;border-style:solid none none none;text-align:right;padding:3px 0">
                <input id="haloacl_save_quickacl_button" type="button" value="save Quickacl" onClick="YAHOO.haloacl.saveQuickacl()"; />
            </div>
           </div>
       </div>
       <div style="clear:both">&nbsp;</div>
  </div>

HTML;
    //$myGenPanel->setContent($myGenPanelContent);

    //$html .= $myGenPanel->getPanel();
    $html .= $myGenPanelContent;
    $html .= <<<HTML

  <script>
        YAHOO.haloacl.quickaclTableInstance = YAHOO.haloacl.quickaclTable('haloacl_quickacl_datatable','haloacl_quickacl_datatable');

        YAHOO.haloacl.saveQuickacl = function(){

            if(YAHOO.haloacl.debug) console.log("saveQuickacl called");
            var xml = "<?xml version=\"1.0\"  encoding=\"UTF-8\"?>";
            xml += "<quickaclContent>";
            $$('.haloacl_quickacl_datatable_template').each(function(item){
                if(item.checked){
                    xml += "<template>"+item.name+"</template>";
                }
            });
            xml += "</quickaclContent>";

            var callback4 = function(result){
                YAHOO.haloacl.quickaclTableInstance.executeQuery("");
                YAHOO.haloacl.notification.createDialogOk("content","QuickACL",result.responseText,{
                    yes:function(){}
                    });

            };

            YAHOO.haloacl.sendXmlToAction(xml,'saveQuickacl',callback4);

        };

    </script>
</div>
HTML;

    $response->addText($html);
    return $response;

    return $html;
}


/**
 *
 * Delivers whitelist pages for Manage Whitelist view
 *
 * @return <JSON>   json from first-level-childs of the query-group; not all childs!
 */
function getQuickACLData($query,$sort,$dir,$startIndex,$results,$filter) {
    global $wgUser;

    $a = array();
    $a['recordsReturned'] = 2;
    $a['totalRecords'] = 10;
    $a['startIndex'] = 0;
    $a['sort'] = null;
    $a['dir'] = "asc";
    $a['pageSize'] = 5;

    $types = array("acltemplate");
    $templates = HACLStorage::getDatabase()->getSDs($types);

    $quickacl = HACLQuickacl::newForUserId($wgUser->getId());

    $a['records'] = array();
    foreach($templates as $sd) {
        if($query == "all" || preg_match('/'.$query.'/is',$sd->getSDName())) {
            $checked = false;
            if(in_array($sd->getSDId(), $quickacl->getSD_IDs())) {
                $checked = true;
            }
            $a['records'][] = array('id'=>$sd->getSDId(), 'name'=>$sd->getSDName(),'checked'=>$checked);
        }
    }

    // generating paging-stuff
    $a['totalRecords'] = sizeof($a['records']);
    $a['records'] = array_slice($a['records'],$startIndex,$a['pageSize']);
    $a['recordsReturned'] = sizeof($a['records']);

    return(json_encode($a));

}


function saveQuickacl($xml) {
    global $wgUser;
    $templates = array();
    $xmlInstance = new SimpleXMLElement($xml);

    foreach($xmlInstance->xpath('//template') as $template) {
        $templates[] = (string)$template;
    }
    if(sizeof($templates) < 15) {
        $quickacl = new HACLQuickacl($wgUser->getId(), $templates);
        $quickacl->save();
        return wfMsg('hacl_quickACL_4');
    }else {
        return wfMsg('hacl_quickacl_limit');
    }
}

function doesArticleExists($articlename,$protect) {
    $response = new AjaxResponse();
    $article = new Article(Title::newFromText($articlename));
    if($article->exists()) {
        $sd = new Article(Title::newFromText("ACL:$protect/$articlename"));
        if($sd->exists()) {
            $response->addText("sdexisting");

        }else {
            $response->addText("true");
        }
    }else {
        $response->addTexta("false");
    }
    return $response;
}
