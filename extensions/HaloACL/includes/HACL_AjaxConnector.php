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

$wgAjaxExportList[] = "ajaxTestFunction";
$wgAjaxExportList[] = "createACLPanels";
$wgAjaxExportList[] = "createAclContent";
$wgAjaxExportList[] = "createRightContent";
$wgAjaxExportList[] = "createModificationRightsContent";
$wgAjaxExportList[] = "createSaveContent";
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




//put your code here



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
    <div id="haloaclsubView" class="yui-navset"></div>
</div>
<script type="text/javascript">
  YAHOO.haloacl.buildSubTabView('haloaclsubView');
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

    $response = new AjaxResponse();
    $helpItem = new HACL_helpPopup("Right", "Select right types and groups/users who have access.");

    $html = <<<HTML
        <!-- section start -->
        <!-- rights section -->
        <div class="haloacl_tab_section_container">
            <div class="haloacl_tab_section_header">
                <div class="haloacl_tab_section_header_count">2.</div>
                <div class="haloacl_tab_section_header_title">
                    Rights
                </div>
HTML;

    $html .= $helpItem->getPanel();
    $html .= <<<HTML
        </div>
            <script type="javascript">

            


           /*     var myTooltip = new YAHOO.widget.Tooltip("myTooltip", {
                    context:"anchorHelpCreateRight",
                    text:"TOOLTOP TExt",
                    showDelay:500 }
                );
           */
            </script>

            <div id="haloacl_tab_createacl_rightsection" class="haloacl_tab_section_content">
                <div class="haloacl_tab_section_content_row">
                    <input type="button" value="Create right"
                        onclick="javascript:YAHOO.haloacl.createacl_addRightPanel('individual');"/>
                    &nbsp;
                    <input type="button" value="Create right template" />
                </div>
            </div>


        </div>
        <div id="step3" style="width:600px;">
            <input type="button" name="gotoStep3" value="Next Step"
            onclick="javascript:gotoStep3();" style="margin-left:30px;"/>
        </div>
        <!-- section end -->
        <script type="javascript">

            gotoStep3 = function() {

                //implement checks here
                //...

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

    $response = new AjaxResponse();

    $helpItem = new HACL_helpPopup("ModificationRight", "Select groups/users who can maintain this ACL descriptor.");

    $html = <<<HTML
        <!-- section start -->
        <!-- modificationrights section -->
        <div class="haloacl_tab_section_container">
            <div class="haloacl_tab_section_header">
                <div class="haloacl_tab_section_header_count">3.</div>
                <div class="haloacl_tab_section_header_title">
                    Modification Rights
                </div>
HTML;

    $html .= $helpItem->getPanel();
    $html .= <<<HTML

            </div>

            <div id="haloacl_tab_createacl_modificationrightsection" class="haloacl_tab_section_content">
                <div class="haloacl_tab_section_content_row">
                    Expand the box below, if you like to allow other users or groups to modify this access control list.
                </div>
                <div class="haloacl_tab_section_content_row">
                    <div id="create_acl_rights_row_modificationrights" class="haloacl_tab_section_content_row"></div>
                </div>
            </div>


        </div>
        <div id="step4" style="width:600px;">
            <input type="button" name="gotoStep4" value="Next Step"
            onclick="javascript:gotoStep4();" style="margin-left:30px;"/>
        </div>
        <!-- section end -->
        <script type="javascript">

            gotoStep4 = function() {

                //implement checks here
                //...

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

    $response = new AjaxResponse();

    $html = <<<HTML
        <!-- section start -->
        <div class="haloacl_tab_section_container">
            <div class="haloacl_tab_section_header">
                <div class="haloacl_tab_section_header_count">4.</div>
                <div class="haloacl_tab_section_header_title">
                    Save ACL
                </div>
            </div>

            <div class="haloacl_tab_section_content">
                <div class="haloacl_tab_section_content_row">
                    <div class="haloacl_tab_section_content_row_descr" style="width:300px">
                    Autogenerated ACL name:
                    </div>
                    <div class="haloacl_tab_section_content_row_content">
                        <div class="haloacl_tab_section_content_row_content_element">
                            <input type="text" disabled="true" id="create_acl_autogenerated_acl_name" value="" />
                        </div>
                    </div>
                </div>
                <div class="haloacl_tab_section_content_row">
                    <form>
                    <input type="button" name="safeACL" value="Save ACL"
                        onclick="javascript:YAHOO.haloacl.buildCreateAcl_SecDesc();"/>
                    </form>

                </div>


            </div>

        </div>
        <!-- section end -->
        <script type="javascript">

            var callback2 = function(result){
                    if(result.status == '200'){
                        $('create_acl_autogenerated_acl_name').value = result.responseText;
                    }else{
                        alert(result.responseText);
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
                xml+="<name>"+$('create_acl_general_name').value+"</name>";
                xml+="<definefor>"+""+"</definefor>";


                var callback = function(result){
                    if(result.status == '200'){
                        alert(result.responseText);
                    }else{
                        alert(result.responseText);
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


    $response->addText($html);
    return $response;
}



/**
 *
 * @return <html>
 *  returns content for createacl-tab
 */
function createAclContent() {

// clear temp-right-sessions
    clearTempSessionRights();

    $response = new AjaxResponse();

    $createStACLHeadline = wfMsg('hacl_create_acl_dut_headline');
    $createStACLInfo = wfMsg('hacl_create_acl_dut_info');
    $createStACLGeneralHeader = wfMsg('hacl_create_acl_dut_general');
    $createStACLGeneralDefine = wfMsg('hacl_create_acl_dut_general_definefor');
    $createStACLGeneralDefinePrivate = wfMsg('hacl_create_acl_dut_general_private_use');
    $createStACLGeneralDefineAll = wfMsg('hacl_create_acl_dut_general_all');
    $createStACLGeneralDefineSpecific = wfMsg('hacl_create_acl_dut_general_specific');
    $createStACLRightsHeader = wfMsg('hacl_create_acl_dut_rights');

    $createStACLRightsButtonCreate = wfMsg('hacl_create_acl_dut_button_create_right');
    $createStACLRightsButtonAddTemplate = wfMsg('hacl_create_acl_dut_button_add_template');
    $createStACLRightsLegend = wfMsg('hacl_create_acl_dut_new_right_legend');
    $createStACLRightsLegendSaved = wfMsg('hacl_create_acl_dut_new_right_legend_status_saved');
    //TODO: this needs to be variable!
    //$createStACLRightsLegendNotSaved = wfMsg('hacl_create_acl_dut_new_right_legend_status_notsaved');
    $createStACLRightsName = wfMsg('hacl_create_acl_dut_new_right_name');
    $createStACLRightsDefaultName = wfMsg('hacl_create_acl_dut_new_right_defaultname');
    $createStACLRights = wfMsg('hacl_create_acl_dut_new_right_rights');
    $createStACLRightsFullAccess = wfMsg('hacl_create_acl_dut_new_right_fullaccess');
    $createStACLRightsRead = wfMsg('hacl_create_acl_dut_new_right_read');
    $createStACLRightsEWF = wfMsg('hacl_create_acl_dut_new_right_ewf');
    $createStACLRightsEdit = wfMsg('hacl_create_acl_dut_new_right_edit');
    $createStACLRightsCreate = wfMsg('hacl_create_acl_dut_new_right_create');
    $createStACLRightsMove = wfMsg('hacl_create_acl_dut_new_right_move');
    $createStACLRightsDelete = wfMsg('hacl_create_acl_dut_new_right_delete');
    $createStACLRightsAnnotate = wfMsg('hacl_create_acl_dut_new_right_annotate');

    $helpText = '<strong>Protect:</strong><br />Choose the type you wish to protect (Page, Category, Property, Namespace)<br /><br /><strong>Protect:</strong><br />Enter the name or use the autocompletion feature to specifiy the item you wish to protect. Note: If you came from a page, the name of the page will be already filled in the text entrybox.<br /><br />';

    $helpItem = new HACL_helpPopup("General", $helpText);

    $html = <<<HTML
        <div class="haloacl_tab_content">
        <div class="haloacl_tab_content_description">
        To create a standard ACL you need to complete the following four steps.
        </div>

        <!-- section start -->
        <div class="haloacl_tab_section_container">
            <div class="haloacl_tab_section_header">
                <div class="haloacl_tab_section_header_count">1.</div>
                <div class="haloacl_tab_section_header_title">
                    General
                </div>
HTML;

    $html .= $helpItem->getPanel();
    $html .= <<<HTML
        </div>

            <div class="haloacl_tab_section_content">
                <div class="haloacl_tab_section_content_row">
                    <div class="haloacl_tab_section_content_row_descr">
                    Protect:
                    </div>
                    <div class="haloacl_tab_section_content_row_content">
                        <div class="haloacl_tab_section_content_row_content_element">
                            <input type="radio" class="create_acl_general_protect" name="create_acl_general_protect" value="page" />&nbsp;Page
                        </div>
                        <div class="haloacl_tab_section_content_row_content_element">
                            <input type="radio" class="create_acl_general_protect" name="create_acl_general_protect" value="property" />&nbsp;Property
                        </div>
                        <div class="haloacl_tab_section_content_row_content_element">
                            <input type="radio" class="create_acl_general_protect" name="create_acl_general_protect" value="namespace" />&nbsp;Namespace
                        </div>
                        <div class="haloacl_tab_section_content_row_content_element">
                            <input type="radio" class="create_acl_general_protect" name="create_acl_general_protect" value="category" />&nbsp;Category
                        </div>
                    </div>
                </div>

                <div class="haloacl_tab_section_content_row">
                    <div class="haloacl_tab_section_content_row_descr">
                    Name:
                    </div>
                    <div class="haloacl_tab_section_content_row_content">
                        <div class="haloacl_tab_section_content_row_content_element">
                            <form>
                                <input type="text" name="create_acl_general_name2" id="create_acl_general_name" value="" />
                            </form>
                        </div>
                    </div>
                </div>

                <div class="haloacl_tab_section_content_row">
                    <div class="haloacl_tab_section_content_row_descr">
                    Define for:
                    </div>
                    <div class="haloacl_tab_section_content_row_content">
                        <div class="haloacl_tab_section_content_row_content_element">
                            <input type="radio" class="create_acl_general_definefor" name="create_acl_general_definefor" value="privateuse" />&nbsp;Private use
                        </div>
                        <div class="haloacl_tab_section_content_row_content_element">
                            <input type="radio" class="create_acl_general_definefor" name="create_acl_general_definefor" value="allusers" />&nbsp;All users
                        </div>
                        <div style="width:400px!important" class="haloacl_tab_section_content_row_content_element">
                            <input type="radio" class="create_acl_general_definefor" name="create_acl_general_definefor" value="individual" />&nbsp;Individual user and/or groups of user
                        </div>

                    </div>
                </div>

            </div>

        </div>
        <!-- section end -->


        <div id="step2" style="width:600px">
            <input type="button" name="gotoStep2" value="Next Step"
            onclick="javascript:gotoStep2();" style="margin-left:30px;"/>
        </div>





        <script type="javascript">

            gotoStep2 = function() {



                //implement checks here
                //...


                //YAHOO.haloacl.loadContentToDiv('step2','createRightContent',{panelid:1});
                $$('.create_acl_general_definefor').each(function(item){
                    if(item.checked){
                        if (item.value == "individual") YAHOO.haloacl.loadContentToDiv('step2','createRightContent',{predefine:'individual'});
                        if (item.value == "privateuse") YAHOO.haloacl.loadContentToDiv('step2','createRightContent',{predefine:'private'});
                        if (item.value == "allusers") YAHOO.haloacl.loadContentToDiv('step2','createRightContent',{predefine:'all'});
                    }
                });


            }




        </script>
    </div>
HTML;


    $response->addText($html);
    return $response;
}

/**
 *
 * @param <String>  panelid of parents-div to have an unique identifier for each right-panel
 * @return <html>   right-panel html
 *
 */
function getRightsPanel($panelid, $predefine) {

    $myGenericPanel = new HACL_GenericPanel($panelid, "Right");


    $content = <<<HTML


		<div id="content_$panelid" class="panel haloacl_panel_content">
                    <div id="rightTypes_$panelid">
                    <div class="halocal_panel_content_row">
                        <div class="haloacl_panel_content_row_descr">
                            Name:
                        </div>
                        <div class="haloacl_panel_content_row_content">
                            <input type="text" id="right_name_$panelid" />
                        </div>
                    </div>

                    <div class="halocal_panel_content_row">
                        <div class="haloacl_panel_content_row_descr">
                            Rights:
                        </div>
                        <div class="haloacl_panel_rights">
                            <form name="formRights_$panelid">
                            <div class="right_fullaccess"><input id = "checkbox_right_fullaccess" actioncode="255" type="checkbox" class="right_rights_$panelid" name="fullaccess" onChange="updateRights$panelid(this)"/>&nbsp;Full access</div>
                            <div class="right_read"><input id = "checkbox_right_read" type="checkbox" actioncode="128" actioncode="128" class="right_rights_$panelid" name="read" onChange="updateRights$panelid(this)"/>&nbsp;Read</div>
                            <div class="right_edit"><input id = "checkbox_right_edit" type="checkbox" actioncode="8" class="right_rights_$panelid" name="edit" onChange="updateRights$panelid(this)"/>&nbsp;Edit</div>
                            <div class="right_editfromform"><input id = "checkbox_right_editfromform" actioncode="64" type="checkbox" class="right_rights_$panelid" name="editfromform" onChange="updateRights$panelid(this)"/>&nbsp;Edit from form</div>
                            <div class="right_wysiwyg"><input id = "checkbox_right_wysiwyg" type="checkbox" actioncode="32" class="right_rights_$panelid" name="wysiwyg" onChange="updateRights$panelid(this)"/>&nbsp;WYSIWYG</div>
                            <div class="right_create"><input id = "checkbox_right_create" type="checkbox" actioncode="4" class="right_rights_$panelid" name="create" onChange="updateRights$panelid(this)"/>&nbsp;Create</div>
                            <div class="right_move"><input id = "checkbox_right_move" type="checkbox" actioncode="2" class="right_rights_$panelid" name="move" onChange="updateRights$panelid(this)"/>&nbsp;Move</div>
                            <div class="right_delete"><input id = "checkbox_right_delete" type="checkbox" actioncode="1" class="right_rights_$panelid" name="delete" onChange="updateRights$panelid(this)"/>&nbsp;Delete</div>
                            <div class="right_annotate"><input id = "checkbox_right_annotate" type="checkbox" actioncode="16" class="right_rights_$panelid" name="annotate" onChange="updateRights$panelid(this)"/>&nbsp;Annotate</div>
                            </form>
                        </div>
                    </div>
                    <script type="javascript>
                        // tickbox handling
                        updateRights$panelid = function(element) {
                            var name = $(element).readAttribute("name");
                            element = $(element.id);
                            $$('.right_rights_$panelid').each(function(item){
                                if(name == "create" || name=="move" || name=="delete"){
                                    if(item.name != "create" && item.name != "move" && item.name != "delete"){
                                        item.checked = false;
                                    }

                                }else{
                                    if(item.name != name){
                                        item.checked = false;
                                    }
                                }
                            });

                            if(element.checked){
                                var includedrights = "";
                                if(name == "fullaccess"){
                                    includedrights = "create,read,write,edit,annotate,wysiwyg,editfromform,delete,move";
                                }else if(name == "read"){
                                    includedrights = "";

                                }else if(name == "editfromform"){
                                    includedrights = "read";

                                }else if(name == "annotate"){
                                    includedrights = "read";

                                }else if(name == "wysiwyg"){
                                    includedrights = "read";

                                }else if(name == "edit"){
                                    includedrights = "read,editfromform,annotate,wysiwyg";

                                }else if(name == "create"){
                                    includedrights = "read,editfromform,annotate,wysiwyg";

                                }else if(name == "move"){
                                    includedrights = "read,editfromform,annotate,wysiwyg";

                                }else if(name == "delete"){
                                    includedrights = "read,editfromform,annotate,wysiwyg";

                                }
                                $$('.right_rights_$panelid').each(function(item){
                                    if(includedrights.indexOf($(item).readAttribute("name")) >= 0){
                                        item.checked = true;
                                    }
                                });
                            }
                        }
                    </script>


                    <div class="haloacl_greyline">&nbsp;</div>
                    



                    <div class="halocal_panel_content_row">
                        <div class="haloacl_panel_content_row_descr" style="width:145px">
                            Right description:
                        </div>
                        <div class="haloacl_panel_content_row_content">
                            <input type="text" disabled="true" style="width:400px" id="right_description_$panelid" value="autogenerated"/>

                        </div>
                    </div>
                       <div class="halocal_panel_content_row">
                        <div class="haloacl_panel_content_row_descr" style="width:145px">
                            &nbsp;
                        </div>
                        <div class="haloacl_panel_content_row_content">
                            Autogenerate description text:
                            <input type="radio" value="on" name="right_descriptiontext_$panelid" onChange="YAHOO.haloacl.right_autodesc_$panelid(this)" class="right_descriptiontext_$panelid" checked/>&nbsp;on
                            <input type="radio" value="off" name="right_descriptiontext_$panelid"onChange="YAHOO.haloacl.right_autodesc_$panelid(this)"  class="right_descriptiontext_$panelid" />&nbsp;off
                        </div>
                    </div>
                    <script>
                        YAHOO.haloacl.right_autodesc_$panelid = function(element){
                            if(element.value == 'on'){
                               $('right_description_$panelid').disable();
                               $('right_description_$panelid').value = "autogenerated";
                            }else{
                               $('right_description_$panelid').enable();
                               $('right_description_$panelid').value = "";
                            }
                        }
                    </script>

                    <div class="haloacl_greyline">&nbsp;</div>
                    </div>

                    <div id="right_tabview_$panelid" class="yui-navset"></div>
                    <script type="text/javascript">
                      YAHOO.haloacl.buildRightPanelTabView('right_tabview_$panelid', '$predefine');
                    </script>

                    <div class="haloacl_greyline">&nbsp;</div>
                    <div>
                        <div style="width:33%;float:left;"><input type="button" value="Delete right" onclick="javascript:YAHOO.haloacl.removePanel('$panelid');" /></div>
                        <div style="width:33%;float:left;text-align:center"><input type="button" value="Reset right" onclick="javascript:YAHOO.haloacl.removePanel('$panelid');YAHOO.haloacl.createacl_addRightPanel();" /></div>
                        <div style="width:33%;float:left;text-align:right"><input type="button" name="safeRight" value="Save right" onclick="YAHOO.haloacl.buildRightPanelXML_$panelid();" /></div>
                    </div>
		</div>
HTML;








    $myGenericPanel->setContent($content);

    $footerextension = <<<HTML
    <script type="javascript>


            // rightpanel handling

            YAHOO.haloacl.buildRightPanelXML_$panelid = function(){
                var groups = YAHOO.haloacl.getCheckedNodesFromTree(YAHOO.haloacl.treeInstanceright_tabview_$panelid);
                var panelid = '$panelid';

                // building xml
                var xml = "<?xml version=\"1.0\"  encoding=\"UTF-8\"?>";
                xml+="<right>";
                xml+="<panelid>$panelid</panelid>";
                xml+="<name>"+$('right_name_$panelid').value+"</name>";
                xml+="<description>"+$('right_description_$panelid').value+"</description>";
                $$('.right_descriptiontext_$panelid').each(function(item){
                    if(item.checked){
                        xml+="<autoDescription>"+item.value+"</autoDescription>";
                    }
                });
    

                xml+="<rights>";
                $$('.right_rights_$panelid').each(function(item){
                    if(item.checked){
                        xml+="<right>"+item.name+"</right>";
                    }
                });
                xml+="</rights>";

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
                xml+="</right>";


                var callback = function(result){
                    if(result.status == '200'){
                        //parse result
                        //YAHOO.lang.JSON.parse(result.responseText);
                        genericPanelSetSaved_$panelid(true);
                        genericPanelSetName_$panelid("[ "+$('right_name_$panelid').value+" ] - ");
                        genericPanelSetDescr_$panelid(result.responseText);

                        YAHOO.haloacl.closePanel('$panelid');
                    }else{
                        alert(result.responseText);
                    }
                };
                YAHOO.haloacl.sendXmlToAction(xml,'saveTempRightToSession',callback);
            };

            YAHOO.util.Event.addListener("checkbox_right_fullaccess", "change", genericPanelSetSaved_$panelid, false);
            YAHOO.util.Event.addListener("checkbox_right_read", "change", genericPanelSetSaved_$panelid, false);
            YAHOO.util.Event.addListener("checkbox_right_edit", "change", genericPanelSetSaved_$panelid, false);
            YAHOO.util.Event.addListener("checkbox_right_editfromform", "change", genericPanelSetSaved_$panelid, false);
            YAHOO.util.Event.addListener("checkbox_right_wysiwyg", "change", genericPanelSetSaved_$panelid, false);
            YAHOO.util.Event.addListener("checkbox_right_create", "change", genericPanelSetSaved_$panelid, false);
            YAHOO.util.Event.addListener("checkbox_right_move", "change", genericPanelSetSaved_$panelid, false);
            YAHOO.util.Event.addListener("checkbox_right_delete", "change", genericPanelSetSaved_$panelid, false);
            YAHOO.util.Event.addListener("checkbox_right_annotate", "change", genericPanelSetSaved_$panelid, false);
            YAHOO.util.Event.addListener("right_name_$panelid", "change", genericPanelSetSaved_$panelid, false);
            YAHOO.util.Event.addListener("right_description_$panelid", "change", genericPanelSetSaved_$panelid, false);


            checkAll = function () {
                $('checkbox_right_read').checked = false;
                genericPanelSetSaved_$panelid(false);
            };


            resetPanel_$panelid = function(){
                $('filterSelectGroup_$panelid').value = "";
            };



        </script>
HTML;

    switch ($predefine) {

        case "modification":
            $footerextension .= <<<HTML
            <script type="javascript>
                $('rightTypes_$panelid').style.display = 'none';
                $('right_name_$panelid').value ="Modification Rights";
                $('right_description_$panelid').value ="modification rights";
                genericPanelSetName_$panelid('Modification Rights');
                YAHOO.haloacl.closePanel('$panelid');
            </script>
HTML;
            break;
        case "individual":break;
        case "all":break;
        case "private":
            $footerextension .= <<<HTML
            <script type="javascript>
                $('rightTypes_$panelid').style.display = 'none';
                genericPanelSetName_$panelid("Private right for user ".$wgUser->getName());
                //YAHOO.haloacl.buildRightPanelXML_$panelid();
            </script>
HTML;
            break;
    }


    $myGenericPanel->extendFooter($footerextension);




    /*

    $html = <<<HTML
	<!-- start of panel div-->
	<div id="$panelid" class="panel haloacl_panel">
		<!-- panel's top bar -->
		<div id="title_$panelid" class="panel haloacl_panel_title">
			<span class="haloacl_panel_expand-collapse">
				<a href="javascript:YAHOO.haloacl.togglePanel('$panelid');"><div id="exp-collapse-button_$panelid" class="haloacl_panel_button_collapse"></div></a>
			</span>
			<span id="haloacl_panel_name_$panelid" class="panel haloacl_panel_name">Right</span>
                        <span id="haloacl_panel_descr_$panelid" class="panel haloacl_panel_descr"></span>
			<span id="haloacl_panel_status_$panelid" class="haloacl_panel_status_notsaved">Not Saved</span>
			<span class="button haloacl_panel_close">
				<a href="javascript:YAHOO.haloacl.removePanel('$panelid');"><div id="close-button_$panelid" class="haloacl_panel_button_close"></div></a>
			</span>
		</div>
		<!-- end of panel's top bar -->

		<div id="content_$panelid" class="panel haloacl_panel_content">
                    <div class="halocal_panel_content_row">
                        <div class="haloacl_panel_content_row_descr">
                            Name:
                        </div>
                        <div class="haloacl_panel_content_row_content">
                            <input type="text" id="right_name_$panelid" />
                        </div>
                    </div>

                    <div class="halocal_panel_content_row">
                        <div class="haloacl_panel_content_row_descr">
                            Rights:
                        </div>
                        <div class="haloacl_panel_rights">
                            <form name="formRights_$panelid">
                            <div class="right_fullaccess"><input type="checkbox" class="right_rights_$panelid" name="fullaccess" onClick="checkAllRights$panelid()"/>&nbsp;Full access</div>
                            <div class="right_read"><input type="checkbox" class="right_rights_$panelid" name="read"/>&nbsp;Read</div>
                            <div class="right_edit"><input type="checkbox" class="right_rights_$panelid" name="edit"/>&nbsp;Edit</div>
                            <div class="right_editfromform"><input type="checkbox" class="right_rights_$panelid" name="editfromform"/>&nbsp;Edit from form</div>
                            <div class="right_wysiwyg"><input type="checkbox" class="right_rights_$panelid" name="wysiwyg"/>&nbsp;WYSIWYG</div>
                            <div class="right_create"><input type="checkbox" class="right_rights_$panelid" name="create"/>&nbsp;Create</div>
                            <div class="right_move"><input type="checkbox" class="right_rights_$panelid" name="move"/>&nbsp;Move</div>
                            <div class="right_delete"><input type="checkbox" class="right_rights_$panelid" name="delete"/>&nbsp;Delete</div>
                            <div class="right_annotate"><input type="checkbox" class="right_rights_$panelid" name="annotate"/>&nbsp;Annotate</div>
                            </form>
                        </div>
                    </div>
                    <script type="javascript>
                        // tickbox handling
                        checkAllRights$panelid = function() {
                            document.myform.formRights_$panelid.read.checked = true;
                        }
                    </script>



                    <div class="haloacl_greyline">&nbsp;</div>

                    <div class="halocal_panel_content_row">
                        <div class="haloacl_panel_content_row_descr" style="width:145px">
                            Right description:
                        </div>
                        <div class="haloacl_panel_content_row_content">
                            <input type="text" style="width:400px" id="right_description_$panelid" />

                        </div>
                    </div>
                       <div class="halocal_panel_content_row">
                        <div class="haloacl_panel_content_row_descr" style="width:145px">
                            &nbsp;
                        </div>
                        <div class="haloacl_panel_content_row_content">
                            Autogenerate description text:
                            <input type="radio" value="on" name="right_descriptiontext_$panelid" />&nbsp;on
                            <input type="radio" value="off" name="right_descriptiontext_$panelid" />&nbsp;off
                        </div>
                    </div>

                    <div class="haloacl_greyline">&nbsp;</div>

                    <div id="right_tabview_$panelid" class="yui-navset"></div>
                    <script type="text/javascript">
                      YAHOO.haloacl.buildRightPanelTabView('right_tabview_$panelid');
                    </script>

                    <div class="haloacl_greyline">&nbsp;</div>
                    <div cl
                    <input type="button" value="Delete right" />
                    <input type="button" value="Discard right" />
                    <input type="button" name="safeRight" value="Save right" onclick="YAHOO.haloacl.buildRightPanelXML_$panelid();"/>

		</div>
	</div> <!-- end of panel div -->
        <script type="javascript>
            // rightpanel handling



            YAHOO.haloacl.buildRightPanelXML_$panelid = function(){
                var groups = YAHOO.haloacl.getCheckedNodesFromTree(YAHOO.haloacl.treeInstanceright_tabview_$panelid);
                var panelid = '$panelid';

                // building xml
                var xml = "<?xml version=\"1.0\"  encoding=\"UTF-8\"?>";
                xml+="<right>";
                xml+="<panelid>$panelid</panelid>";
                xml+="<name>"+$('right_name_$panelid').value+"</name>";
                xml+="<description>"+$('right_description_$panelid').value+"</description>";

                xml+="<rights>";
                $$('.right_rights_$panelid').each(function(item){
                    if(item.checked){
                        xml+="<right>"+item.name+"</right>";
                    }
                });
                xml+="</rights>";

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
                xml+="</right>";


                var callback = function(result){
                    if(result.status == '200'){
                        //parse result
                        //YAHOO.lang.JSON.parse(result.responseText);
                        $('haloacl_panel_status_$panelid').textContent = 'Saved';
                        $('haloacl_panel_status_$panelid').setAttribute("class", "haloacl_panel_status_saved");
                        $('haloacl_panel_name_$panelid').textContent = "[ "+$('right_name_$panelid').value+" ] - ";
                        $('haloacl_panel_descr_$panelid').textContent = result.responseText;
                        YAHOO.haloacl.closePanel('$panelid');
                    }else{
                        alert(result.responseText);
                    }
                };
                YAHOO.haloacl.sendXmlToAction(xml,'saveTempRightToSession',callback);
            };


        </script>
HTML;*/
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
            YAHOO.haloacl.loadContentToDiv('modificationRights','getRightsPanel',{panelid:'$panelid', predefine:'modification'});
            
        </script>
HTML;
    /*
     *
    $html = <<<HTML
	<!-- start of panel div-->
	<div id="$panelid" class="panel haloacl_panel">
		<!-- panel's top bar -->
		<div id="title_$panelid" class="panel haloacl_panel_title">
			<span class="haloacl_panel_expand-collapse">
				<a href="javascript:YAHOO.haloacl.togglePanel('$panelid');"><div id="exp-collapse-button_$panelid" class="haloacl_panel_button_collapse"></div></a>
			</span>
			<span class="panel haloacl_panel_name">Modification Right</span>
			
		</div>
		<!-- end of panel's top bar -->
		<div id="content_$panelid" class="panel haloacl_panel_content">
                    <div id="right_tabview_$panelid" class="yui-navset"></div>
                    <script type="text/javascript">
                      YAHOO.haloacl.buildRightPanelTabView('right_tabview_$panelid');
                    </script>
                    <div class="haloacl_greyline">&nbsp;</div>
                    <div>
                        <div style="width:50%;float:left;"><input type="button" value="Reset right" onclick="javascript:resetPanel_$panelid();" /></div>
                        <div style="width:50%;float:left;text-align:right"><input type="button" name="safeRight" value="Save right" onclick="YAHOO.haloacl.buildRightPanelXML_$panelid();" /></div>
                    </div>
		</div>
	</div> <!-- end of panel div -->
HTML
    */


    return $html;
}

/**
 *
 * @param <string>  unique identifier
 * @return <html>   returns the user/group-select tabview; e.g. contained in right panel
 */
function rightPanelSelectDeselectTab($panelid, $predefine) {
    $html = <<<HTML
        <!-- leftpart -->
        <div class="haloacl_rightpanel_selecttab_container">
            <div class="haloacl_rightpanel_selecttab_leftpart">
                <div class="haloacl_rightpanel_selecttab_leftpart_filter">
                    <span class="haloacl_rightpanel_selecttab_leftpart_filter_title">
                        Groups and Users
                    </span>
                </div>
                <div class="haloacl_rightpanel_selecttab_leftpart_filter">
                    <span class="haloacl_rightpanel_selecttab_leftpart_filter_title">
                        Filter in groups:
                    </span>
                    <input id="filterSelectGroup_$panelid" type="text" />
                </div>
                <div id="treeDiv_$panelid" class="haloacl_rightpanel_selecttab_leftpart_treeview">&nbsp;</div>
                <div class="haloacl_rightpanel_selecttab_leftpart_treeview_userlink">
                    <a href="javascript:YAHOO.haloacl.datatableInstance$panelid.executeQuery('all');">Users</a>
                </div>
            </div>
            <!-- end of left part -->

            <!-- starting right part -->

            <div class="haloacl_rightpanel_selecttab_rightpart">
                <div class="haloacl_rightpanel_selecttab_rightpart_filter">
                    <span class="haloacl_rightpanel_selecttab_rightpart_filter_title">
                        Users <span id="datatablepaging_count_$panelid"></span>
                    </span>
                </div>
                <div class="haloacl_rightpanel_selecttab_rightpart_filter">
                    <span class="haloacl_rightpanel_selecttab_rightpart_filter_title">
                        Filter:
                    </span>
                    <input id="datatable_filter_$panelid" type="text" />
                </div>
                <div id="datatableDiv_$panelid" class="haloacl_rightpanel_selecttab_rightpart_datatable">&nbsp;</div>
                <div id="datatablepaging_datatableDiv_$panelid"></div>
                </div>
            </div>
            <!-- end of right part -->

        </div>
<script type="text/javascript">
 // user list on the right
    YAHOO.haloacl.datatableInstance$panelid = YAHOO.haloacl.userDataTable("datatableDiv_$panelid","$panelid");

    // treeview part - so the left part of the select/deselct-view
    //YAHOO.haloacl.treeInstance$panelid = new YAHOO.widget.TreeView("treeDiv_$panelid");

    YAHOO.haloacl.treeInstance$panelid = YAHOO.haloacl.getNewTreeview("treeDiv_$panelid",'$panelid');

    YAHOO.haloacl.treeInstance$panelid.labelClickAction = 'YAHOO.haloacl.datatableInstance$panelid.executeQuery';
    YAHOO.haloacl.buildTreeFirstLevelFromJson(YAHOO.haloacl.treeInstance$panelid);

    refilter = function() {
        YAHOO.haloacl.filterNodes (YAHOO.haloacl.treeInstance$panelid.getRoot(), document.getElementById("filterSelectGroup_$panelid").value);
    }
    //filter event
    YAHOO.util.Event.addListener("filterSelectGroup_$panelid", "keyup", refilter);
    YAHOO.util.Event.addListener("datatable_filter_$panelid", "keyup", function(){
        console.log("filterevent fired");
        YAHOO.haloacl.datatableInstance$panelid.executeQuery('');
    });
    console.log("datatable_filter_$panelid");
    

    handleDatatableClick = function(item){
       var panelid = '$panelid';
       YAHOO.haloacl.clickedArrayUsers[panelid] = new Array();
       YAHOO.haloacl.clickedArrayUsersGroups[panelid] = new Array();

       console.log("restet array for panelid:"+panelid);

       $$('.datatableDiv_'+panelid+'_users').each(function(item){
            if(item.checked){
               YAHOO.haloacl.clickedArrayUsers[panelid].push(item.name);
               console.log("adding "+item.name+" to list of checked users");
            }

            YAHOO.haloacl.clickedArrayUsersGroups[panelid][item.name] = $(item).readAttribute("groups");
       });

    };
    YAHOO.util.Event.addListener("datatableDiv_$panelid", "click", handleDatatableClick);


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
function rightPanelAssignedTab($panelid) {
    $html = <<<HTML
        <!-- leftpart -->
        <div class="haloacl_rightpanel_selecttab_container">
            <div class="haloacl_rightpanel_selecttab_leftpart">
                <div class="haloacl_rightpanel_selecttab_leftpart_filter">
                    <span class="haloacl_rightpanel_selecttab_leftpart_filter_title">
                        Groups and Users
                    </span>
                </div>
                <div class="haloacl_rightpanel_selecttab_leftpart_filter">
                    <span class="haloacl_rightpanel_selecttab_leftpart_filter_title">
                        Filter in groups:
                    </span>
                    <input id="filterAssignedGroup_$panelid" type="text" "/>
                </div>
                <div id="treeDivRO_$panelid" class="haloacl_rightpanel_selecttab_leftpart_treeview">&nbsp;</div>
                <div class="haloacl_rightpanel_selecttab_leftpart_treeview_userlink">
                    Users
                </div>
            </div>
            <!-- end of left part -->

            <!-- starting right part -->

            <div class="haloacl_rightpanel_selecttab_rightpart">
                <div class="haloacl_rightpanel_selecttab_rightpart_filter">
                    <span class="haloacl_rightpanel_selecttab_rightpart_filter_title">
                        Users
                    </span>
                </div>
                <div class="haloacl_rightpanel_selecttab_rightpart_filter">
                    <span class="haloacl_rightpanel_selecttab_rightpart_filter_title">
                        Filter:
                    </span>
                    <input type="text" />
                </div>
                <div id="ROdatatableDiv_$panelid" class="haloacl_rightpanel_selecttab_rightpart_datatable">&nbsp;</div>
                <div id="ROdatatablepaging_datatableDiv_$panelid"></div>
                </div>
            </div>
            <!-- end of right part -->

        </div>
<script type="text/javascript">

 // user list on the right
    YAHOO.haloacl.RODatatableInstace$panelid = YAHOO.haloacl.ROuserDataTableV2("ROdatatableDiv_$panelid","$panelid");
    //YAHOO.haloacl.ROdatatableInstance$panelid = YAHOO.haloacl.ROuserDataTable("ROdatatableDiv_$panelid","$panelid");

    // treeview part - so the left part of the select/deselct-view

    YAHOO.haloacl.ROtreeInstance$panelid = YAHOO.haloacl.getNewTreeview("treeDivRO_$panelid",'$panelid');

    //YAHOO.haloacl.ROtreeInstance$panelid.labelClickAction = 'YAHOO.haloacl.datatableInstance$panelid.executeQuery';
    YAHOO.haloacl.buildUserTreeRO(YAHOO.haloacl.treeInstance$panelid, YAHOO.haloacl.ROtreeInstance$panelid);

    refilter = function() {
        YAHOO.haloacl.filterNodes (YAHOO.haloacl.ROtreeInstance$panelid.getRoot(), document.getElementById("filterAssignedGroup_$panelid").value);
    }
    YAHOO.util.Event.addListener("filterAssignedGroup_$panelid", "keyup", refilter);

  




</script>

HTML;
    return $html;

}

/**
 * function called intern (not ajax) to clear all saved right
 */
function clearTempSessionRights() {
    unset($_SESSION['temprights']);
}

/**
 *
 * @param <string/xml>  right serialized as xml
 * @return <status>     200: ok / right saved to session
 *                      400: failure / rihght not saved to session (exception's message will be returned also)
 */
function saveTempRightToSession($rightxml) {
    try {
        $xml = new SimpleXMLElement($rightxml);
        $description = '';
        $actions = 0;
        $groups = '';
        $users = '';
        $description = $xml->description ? $xml->description : '';
        $autoDescription = $xml->autoDescription ? $xml->autoDescription : '';

        $autoGeneratedRightName = " for ";
        $autoGeneratedRightNameRights = "";


        foreach($xml->xpath('//group') as $group) {
            if($groups == '') {
                $groups = (string)$group;
            }else {
                $groups = $groups.",".(string)$group;
            }
            $autoGeneratedRightName .= "G:".(string)$group.",";
        }
        foreach($xml->xpath('//user') as $user) {
            if($users == '') {
                $users = (string)$user;
            }else {
                $users = $users.",".(string)$user;
            }
            $autoGeneratedRightName .= "U:".(string)$user.",";
        }

        $fa = false;

        foreach($xml->xpath('//right') as $right) {

            $actions = $actions + (int)HACLRight::getActionID($right);

            if ((int)HACLRight::getActionID($right) <> "fullaccess") $autoGeneratedRightNameRights .= $right.","; else $fa=true;
        }
        $autoGeneratedRightName = substr($autoGeneratedRightName,0,-1);
        $autoGeneratedRightNameRights = substr($autoGeneratedRightNameRights,0,-1);
        if ($fa) {
        //$autoGeneratedRightNameRights = "Full Access";
        }
        $actions = $actions > 255 ? 255 : $actions;

        $tempright = new HACLRight($actions,$groups,$users,$description, 0);

        $panelid = (string)$xml->panelid;
        #  $_SESSION['temprights'][$panelid] = $tempright;
        $_SESSION['temprights'][$panelid] = $rightxml;

        $autoGeneratedRightName = $autoGeneratedRightNameRights.$autoGeneratedRightName;
        if (strlen($autoGeneratedRightName) > 50) $autoGeneratedRightName = substr($autoGeneratedRightName,0,50)."...";


        if ($autoDescription == "on") $description = $autoGeneratedRightName;

        $ajaxResponse = new AjaxResponse();
        $ajaxResponse->setContentType("json");
        $ajaxResponse->setResponseCode(200);
        $ajaxResponse->addText($description);

    } catch (Exception  $e) {
        $ajaxResponse = new AjaxResponse();
        $ajaxResponse->setResponseCode(400);
        $ajaxResponse->addText($e->getMessage());
    }
    return $ajaxResponse;
//return (string)sizeof($_SESSION['temprights']);
}

/**
 *
 * @param <string/xml>  serialized form of a securitydescriptor
 */
function saveSecurityDescriptor($secDescXml) {


    try {
    // building rights
        foreach($_SESSION['temprights'] as $tempright) {
            $xml = new SimpleXMLElement($tempright);
            $actions = 0;
            $groups = '';
            $users = '';
            $description = $xml->description ? $xml->description : '';
            $autoDescription = $xml->autoDescription ? $xml->autoDescription : '';

            if ($description <> "modification rights") {

                $autoGeneratedRightName = " for ";
                $autoGeneratedRightNameRights = "";

                foreach($xml->xpath('//group') as $group) {
                    if($groups == '') {
                        $groups = (string)$group;
                    }else {
                        $groups = $groups.",".(string)$group;
                    }
                    $autoGeneratedRightName .= "G:".(string)$group.",";
                }
                foreach($xml->xpath('//user') as $user) {
                    if($users == '') {
                        $users = 'User:'.(string)$user;
                    }else {
                        $users = $users.",".'User:'.(string)$user;
                    }
                    $autoGeneratedRightName .= "U:".(string)$user.",";
                }
                foreach($xml->xpath('//right') as $right) {
                //$actions = $actions + (int)HACLRight::getActionID($right);

                    if($actions2 == '') {
                        $actions2 = (string)$right;
                    }else {
                        $actions2 = $actions2.",".(string)$right;
                    }
                }
                foreach($xml->xpath('//right') as $right) {
                    $actions = $actions + (int)HACLRight::getActionID($right);
                    if ($right <> 255) $autoGeneratedRightNameRights .= $right.",";
                }

                $autoGeneratedRightName = substr($autoGeneratedRightName,0,-1);
                $autoGeneratedRightNameRights = substr($autoGeneratedRightNameRights,0,-1);

                if ($actions >= 255) {
                    $autoGeneratedRightNameRights = "Fullaccess";
                }

                $autoGeneratedRightName = $autoGeneratedRightNameRights.$autoGeneratedRightName;
                if ($autoDescription == "on") $description = $autoGeneratedRightName;

                $inline .= '{{#access: assigned to='.$groups.','.$users.' |actions='.$actions2.' |description='.$description.'}}
';
            }
        //$title = new Title();
        //$title->newFromText("Category/Favorite books", "ACL");

        //$rightarticle = new Article($title);
        //$rightarticle->doEdit("text", "summary");
        //$rightarticle = $sdarticle->getID();

        //$tempright = new HACLRight($actions,$groups,$users,$description, $rightarticleid);
        }


        //get manage rights
        $secDescXml = new SimpleXMLElement($secDescXml);

        // securitydescriptor
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

        $SDName = $secDescXml->name;
        switch ($secDescXml->protect) {
            case "page":$peType = "Page";break;
            case "property":$peType = "Property";break;
            case "namespace":$peType = "Namespace";break;
            case "category":$peType = "Category";break;
        }

        // create article for security descriptor
        $sdarticle = new Article(Title::newFromText('ACL:'.$peType.'/'.$SDName));
        $inline .= '{{#manage rights:assigned to='.$sdgroups.','.$sdusers.'}}
        [[Category:ACL/ACL]]';

        $sdarticle->doEdit($inline, "");
        $SDID = $sdarticle->getID();

        /*
        $secDesc = new HACLSecurityDescriptor($SDID, "ACL:Page/Test", $SDName, $peType, $sdgroups, $sdusers);

        $secDesc->addInlineRights();
        $secDesc->save();
         *
         */

        $ajaxResponse = new AjaxResponse();
        $ajaxResponse->setContentType("json");
        $ajaxResponse->setResponseCode(200);
        $ajaxResponse->addText('ACL:'.$peType.'/'.$SDName );

    } catch (Exception  $e) {
        $ajaxResponse = new AjaxResponse();
        $ajaxResponse->setResponseCode(400);
        $ajaxResponse->addText($e->getMessage());
    }
    return $ajaxResponse;

}



function saveGroup($groupXml, $manageRightsXml) {

    try {
    //get group members
        $groupXml = new SimpleXMLElement($groupXml);
        // securitydescriptor-part
        foreach($groupXml->xpath('//group') as $group) {
            if($groups == '') {
                $groups = (string)$group;
            }else {
                $groups .= ",".(string)$group;
            }
        }
        foreach($groupXml->xpath('//user') as $user) {
            if($users == '') {
                $users = 'User:'.(string)$user;
            }else {
                $users .= ",".'User:'.(string)$user;
            }
        }

        //get manage rights
        $manageRightsXml = new SimpleXMLElement($manageRightsXml);
        // securitydescriptor-part
        foreach($manageRightsXml->xpath('//group') as $group) {
            if($mrgroups == '') {
                $mrgroups = (string)$group;
            }else {
                $mrgroups .= ",".(string)$group;
            }
        }
        foreach($manageRightsXml->xpath('//user') as $user) {
            if($mrusers == '') {
                $mrusers = 'User:'.(string)$user;
            }else {
                $mrusers .= ",".'User:'.(string)$user;
            }
        }

        $groupName = $groupXml->name;

        // create article for security descriptor
        $sdarticle = new Article(Title::newFromText('ACL:'.'Group'.'/'.$groupName));
        $inline = '{#member:members='.$users.','.$groups.'}';
        $inline .= '{{#manage rights:assigned to='.$mrgroups.','.$mrusers.'}}
        [[Category:ACL/Group]]';

        $sdarticle->doEdit($inline, "");
        $SDID = $sdarticle->getID();

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



function saveWhitelist($whitelistXml) {

    try {
    //get group members
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
        $ajaxResponse->addText("descriptor saved".$SDID );

    } catch (Exception  $e) {
        $ajaxResponse = new AjaxResponse();
        $ajaxResponse->setResponseCode(400);
        $ajaxResponse->addText($e->getMessage());
    }
    return $ajaxResponse;

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
    $a['recordsReturned'] = 5;
    #$a['totalrecords'] = 0;
    $a['startIndex'] = $startIndex;
    $a['sort'] = $sort;
    $a['dir'] = $dir;
    $a['pageSize'] = 5;

    $tmpstring = "";

    if ($selectedGroup == 'all' || $selectedGroup == '') {

        $db =& wfGetDB( DB_SLAVE );
        $gt = $db->tableName('user');
        $sql = "SELECT * FROM $gt ";

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
function getGroupsForRightPanel($query) {
    $array = array();

    // return first level
    if($query == 'all') {

    //get level 0 groups
        $groups = HACLStorage::getDatabase()->getGroups();
        foreach( $groups as $key => $value) {
            $tempgroup = array('name'=>$value->getGroupName(),'id'=>$value->getGroupId(),'checked'=>'false');
            $array[] = $tempgroup;
        }


    }else {

    //get group by name
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

//get types for
    $typeXML = new SimpleXMLElement($typeXML);
    foreach($typeXML->xpath('//type') as $type) {
        if($types == '') {
            $types = (string)$group;
        }else {
            $types .= ",".(string)$group;
        }
    }

    $array = array();

    $SDs = HACLStorage::getDatabase()->getSDs($types);
    foreach( $SDs as $key => $value) {

        $tempRights = array();

        //attach inline right texts
        foreach (getInlineRightsOfSDs(array($value->getSDID())) as $key2 => $rightId) {
            $tempright = HACLRight::newFromID($rightId);
            $tempRights[] = array('id'=>$rightId, 'description'=>$tempright->getDescription());
        }

        $tempSD = array('id'=>$value->getSDID(), 'name'=>$value->getSDName(), 'rights'=>$tempRights);
        $array[] = $tempSD;
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
function getWhitelistPages() {

//return (json_encode(HACLWhitelist::getPages()));

    $a = array();
    $a['recordsReturned'] = 5;
    $a['totalrecords'] = 10;
    $a['startIndex'] = 0;
    $a['sort'] = null;
    $a['dir'] = "asc";
    $a['pagesize'] = 5;

    $a['records'][] = array('name'=>"testpage1",'checked'=>'false');

    return(json_encode($a));



    /*
    while ($row = $db->fetchObject($res)) {
        $a['records'][] = array('name'=>$row->user_name,'id'=>$row->user_id,'checked'=>'false');
    }
     *
     */

}


/**
 *
 * @return <html>   returns tab-content for managel acls-tab
 */
function manageAclsContent() {
    $response = new AjaxResponse();
    $response->addText("manageAclsContent");
    return $response;
}

/**
 *
 * @return <html>   returns tab-content for manageUser-tab
 */
function manageUserContent() {
    $response = new AjaxResponse();
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
<div id=haclCreateDutContainer>
	<p class="haclHeadline">{$dutHeadline}</p>
	<p class="haclInfo">{$dutInfo}</p>
	<div id="haclCreateDutGeneralContainer">
		<div class="haclHeader">{$dutGeneralHeader}</div>
		<table>
			<tr>
				<td>{$dutGeneralDefine}</td>
				<td><input type="checkbox" checked="checked">{$dutGeneralDefinePrivate}</input></td>
				<td><input type="checkbox" checked="checked">{$dutGeneralDefineAll}</input></td>
				<td><input type="checkbox" checked="checked">{$dutGeneralDefineSpecific}</input></td>
			</tr>
		</table>
	</div><!-- End of haclCreateDutGeneralContainer -->
	<div id="haclCreateDutRightsContainer">
		<div class="haclHeader">{$dutRightsHeader}</div>
		<input class="haclCreateRightButton" type="button" value="{$dutRightsButtonCreate}" />
		<input class="haclCreateRightButton" type="button" value="{$dutRightsButtonAddTemplate}" />
		<div class="haclCreateDutRight">
			<fieldset>
				<legend class="haclCreateDutLegend"><span class="haclCreateDutRightLegend">{$dutRightsLegend}</span>
					<span class="haclCreateDutLegendControl">{$dutRightsLegendSaved}+ICON</span>
				</legend>
				<table>
				<tr>
					<td>{$dutRightsName}</td><td><input type="text" size="30" value="{$dutRightsDefaultName}"></input></td>
				</tr>
				<tr>
					<td>{$dutRights}</td>
					<td>
						<table>
							<tr>
								<td><input type="checkbox" name="full_access" value="full_access">{$dutRightsFullAccess}</input></td>
								<td><input type="checkbox" name="read" value="full_access">{$dutRightsRead}</input></td>
								<td><input type="checkbox" name="edit_with_forms" value="full_access">{$dutRightsEWF}</input></td>
								<td><input type="checkbox" name="edit" value="full_access">{$dutRightsEdit}</input></td
							</tr>
							<tr>
								<td><input type="checkbox" name="create" value="full_access">{$dutRightsCreate}</input></td>
								<td><input type="checkbox" name="move" value="full_access">{$dutRightsMove}</input></td>
								<td><input type="checkbox" name="delete" value="full_access">{$dutRightsDelete}</input></td>
								<td><input type="checkbox" name="annotate" value="full_access">{$dutRightsAnnotate}</input></td>
							</tr>
						</table>
					</td>
				</tr>
				</table>
				<div class="drawLine">&nbsp;</div>
				<div class="haclCreateDutUserContainer">
					<div class="haclCreateDutUserTabs">
						<ul>
							<li>1</li>
							<li>2</li>
						</ul>
					</div><!-- End Of haclCreateDutUserTabs -->
				</div><!-- End Of haclCreateDutUserContainer -->
			</fieldset>
		</div><!-- End Of haclDutRight -->
	</div><!-- End of haclCreateDutRightsContainer -->
	<div class="drawLine">&nbsp;</div>
</div><!-- End of haclCreateDutContainer -->
HTML;
    $response->addText($html);
    return $response;
}

/**
 *
 * @return <html>   returns content for whitelist-tab
 */
function whitelistsContent() {
    $response = new AjaxResponse();
    $wLHeadline = wfMsg('hacl_whitelist_headline');
    $wLInfo = wfMsg('hacl_whitelist_info');
    $wLFilter = wfMsg('hacl_whitelist_filter');
    $wLPageSetHeader = wfMsg('hacl_whitelist_pageset_header');
    $wLPageName = wfMsg('hacl_whitelist_pagename');
    $wlAddButton = wfMsg('hacl_whitelist_addbutton');
    $html = <<<HTML
<div class="haloacl_tab_content">
        <div class="haloacl_tab_content_description_header">
        {$wLHeadline}
        </div>
        <div class="haloacl_tab_content_description">
        {$wLInfo}
        </div>

	<div id="haclWhitelistFilter">
		<span class="marginToRight">{$wLFilter}</span><input type="text" length="20">
	</div>
	<div id="haclWhitelistPageSetContainer">
		<div id="haclWhitelistPageSetHeader">{$wLPageSetHeader}</div>
		<div id="haclWhitelistPageSet">
			<ul>
				<li><a href onclick="haloACLSpecialPage.toggleTab(1,4);return false;" href="#"><span>Mainpage</span></a></li>
			</ul>
		</div><!-- End of haclWhitelistPageSet -->
<div id="whitelistDatatableDiv" class="haloacl_rightpanel_selecttab_rightpart_datatable">&nbsp;</div>
                <div id="datatablepaging_whitelistDatatableDiv"></div>
                </div>
            </div>
                <form>
                    <input type="button" name="removePage" value="Remove selected page from whitelist"
                        onclick="javascript:YAHOO.haloacl.whitelist_removePage();"/>
                </form>
	</div><!-- End of haclWhitelistPageSetContainer -->
	<!-- <div class="drawLine">&nbsp;</div> -->
	<div id="haclWhitelistAddPageContainer">
		<span id="haclWhitelistPageName">{$wLPageName}</span>
		<input id="haclWhitelistPageInput" type="text" size="30"><input type="button" value="{$wlAddButton}">
	</div>
        <!-- section end -->

        <script type="javascript">

            YAHOO.haloacl.whitelistDatatableInstance = YAHOO.haloacl.pageDataTable("whitelistDatatableDiv");

            YAHOO.haloacl.whitelist_removePage = function(){

                  
                  YAHOO.haloacl.whitelistDatatableInstance.executeQuery;

                  var panelid = 'create_acl_right_'+ YAHOO.haloacl.panelcouner;

                  var divhtml = '<div id="create_acl_rights_row'+YAHOO.haloacl.panelcouner+'" class="haloacl_tab_section_content_row"></div>';

                  var containerWhereDivsAreInserted = $('haloacl_tab_createacl_rightsection');
                  $('haloacl_tab_createacl_rightsection').insert(divhtml,containerWhereDivsAreInserted);


                  YAHOO.haloacl.loadContentToDiv('create_acl_rights_row'+YAHOO.haloacl.panelcouner,'getRightsPanel',{panelid:panelid});
                  YAHOO.haloacl.panelcouner++;
            };




        </script>
</div><!-- End of haloacl_tab_content -->







HTML;
    $response->addText($html);
    return $response;
}

?>
