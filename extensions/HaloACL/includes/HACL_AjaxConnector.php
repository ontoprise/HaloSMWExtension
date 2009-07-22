<?php
/* 
 * B2browse Group
 * patrick.hilsbos@b2browse.com
 */

/**
 * Description of HACL_AjaxConnector
 *
 * @author hipath
 */
$wgAjaxExportList[] = "ajaxTestFunction";
$wgAjaxExportList[] = "createAclContent";
$wgAjaxExportList[] = "manageAclsContent";
$wgAjaxExportList[] = "manageUserContent";
$wgAjaxExportList[] = "whitelistsContent";
$wgAjaxExportList[] = "getRightsPanel";
$wgAjaxExportList[] = "rightPanelSelectDeselectTab";
$wgAjaxExportList[] = "getGroupsForRightPanel";
$wgAjaxExportList[] = "getUsersForUserTable";

//put your code here



function ajaxTestFunction() {
    $temp = new AjaxResponse();
    $temp->addText("testtext");

    return $temp;
}

function createAclContent() {
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
                    Gerneral
                </div>
            </div>

            <div class="haloacl_tab_section_content">
                <div class="haloacl_tab_section_content_row">
                    <div class="haloacl_tab_section_content_row_descr">
                    Protect:
                    </div>
                    <div class="haloacl_tab_section_content_row_content">
                        <div class="haloacl_tab_section_content_row_content_element">
                            <input type="radio" name="general_protect" value="page" />&nbsp;Page
                        </div>
                        <div class="haloacl_tab_section_content_row_content_element">
                            <input type="radio" name="general_protect" value="property" />&nbsp;Property
                        </div>
                        <div class="haloacl_tab_section_content_row_content_element">
                            <input type="radio" name="general_protect" value="namespace" />&nbsp;Namespace
                        </div>
                        <div class="haloacl_tab_section_content_row_content_element">
                            <input type="radio" name="general_protect" value="category" />&nbsp;Category
                        </div>
                    </div>
                </div>

                <div class="haloacl_tab_section_content_row">
                    <div class="haloacl_tab_section_content_row_descr">
                    Name:
                    </div>
                    <div class="haloacl_tab_section_content_row_content">
                        <div class="haloacl_tab_section_content_row_content_element">
                            <input type="text" name="general_name" value="" />
                        </div>
                    </div>
                </div>

                <div class="haloacl_tab_section_content_row">
                    <div class="haloacl_tab_section_content_row_descr">
                    Define for:
                    </div>
                    <div class="haloacl_tab_section_content_row_content">
                        <div class="haloacl_tab_section_content_row_content_element">
                            <input type="radio" name="general_definefor" value="privateuse" />&nbsp;Private use
                        </div>
                        <div class="haloacl_tab_section_content_row_content_element">
                            <input type="radio" name="general_definefor" value="allusers" />&nbsp;All users
                        </div>
                        <div style="width:400px!important" class="haloacl_tab_section_content_row_content_element">
                            <input type="radio" name="general_definefor" value="individual" />&nbsp;Individual user and/or groups of user
                        </div>

                    </div>
                </div>

            </div>

        </div>
        <!-- section end -->


        <!-- section start -->
        <div class="haloacl_tab_section_container">
            <div class="haloacl_tab_section_header">
                <div class="haloacl_tab_section_header_count">2.</div>
                <div class="haloacl_tab_section_header_title">
                    Rights
                </div>
            </div>

            <div id="haloacl_tab_createacl_rightsection" class="haloacl_tab_section_content">
                <div class="haloacl_tab_section_content_row">
                    <input type="button" value="Create right" 
                        onclick="javascript:YAHOO.haloacl.createacl_addRightPanel();"/>
                    &nbsp;
                    <input type="button" value="Create right template" />
                </div>
            </div>


        </div>

        <script type="javascript">
            YAHOO.haloacl.createacl_addRightPanel = function(){
                  var panelid = 'create_acl_right_'+ YAHOO.haloacl.panelcouner;

                  var divhtml = '<div id="create_acl_rights_row'+YAHOO.haloacl.panelcouner+'" class="haloacl_tab_section_content_row"></div>';

                  var containerWhereDivsAreInserted = $('haloacl_tab_createacl_rightsection');
                  $('haloacl_tab_createacl_rightsection').insert(divhtml,containerWhereDivsAreInserted);


                  YAHOO.haloacl.loadContentToDiv('create_acl_rights_row'+YAHOO.haloacl.panelcouner,'getRightsPanel',{panelid:panelid});
                  YAHOO.haloacl.panelcouner++;
            };


        </script>

        <!-- section end -->

    </div>


HTML;


    $response->addText($html);
    return $response;
}


function getRightsPanel($panelid) {
    $html = <<<HTML
	<!-- start of panel div-->
	<div id="$panelid" class="panel haloacl_panel">
		<!-- panel's top bar -->
		<div id="title_$panelid" class="panel haloacl_panel_title">
			<span class="haloacl_panel_expand-collapse">
				<a href="javascript:YAHOO.haloacl.togglePanel('$panelid');"><div id="exp-collapse-button_$panelid" class="haloacl_panel_button_collapse"></div></a>
			</span>
			<span class="panel haloacl_panel_name">Right</span>
			<span class="haloacl_panel_status">Not Saved ---- x</span>
			<span class="button haloacl_panel_close">
				<a href="javascript:YAHOO.haloacl.closePanel('$panelid');"><div id="close-button_$panelid" class="haloacl_panel_button_close"></div></a>
			</span>
		</div>
		<!-- end of panel's top bar -->

		<div id="content_$panelid" class="panel haloacl_panel_content">
                    <div class="halocal_panel_content_row">
                        <div class="haloacl_panel_content_row_descr">
                            Name:
                        </div>
                        <div class="haloacl_panel_content_row_content">
                            <input type="text" name="right_name_$panelid" />
                        </div>
                    </div>

                    <div class="halocal_panel_content_row">
                        <div class="haloacl_panel_content_row_descr">
                            Rights:
                        </div>
                        <div class="haloacl_panel_content_row_content">
                            <input type="checkbox" name="right_rights_fullaccess_$panelid" />&nbsp;Full access
                            <input type="checkbox" name="right_rights_read_$panelid" />&nbsp;Read
                            <input type="checkbox" name="right_rights_edit_$panelid" />&nbsp;Edit
                            <input type="checkbox" name="right_rights_editfromform_$panelid" />&nbsp;Edit from form
                            <input type="checkbox" name="right_rights_wysiwyg_$panelid" />&nbsp;WYSIWYG
                            <input type="checkbox" name="right_rights_create_$panelid" />&nbsp;Create
                            <input type="checkbox" name="right_rights_move_$panelid" />&nbsp;Move
                            <input type="checkbox" name="right_rights_delete_$panelid" />&nbsp;Delete
                            <input type="checkbox" name="right_rights_annotate_$panelid" />&nbsp;Annotate
                        </div>
                    </div>

                    <div class="haloacl_greyline">&nbsp;</div>

                    <div class="halocal_panel_content_row">
                        <div class="haloacl_panel_content_row_descr" style="width:145px">
                            Right description:
                        </div>
                        <div class="haloacl_panel_content_row_content">
                            <input type="text" style="width:400px" name="right_description_$panelid" />

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
                    <input type="button" value="Save right" />

		</div>
	</div> <!-- end of panel div -->
HTML;
    return $html;
}

function rightPanelSelectDeselectTab($panelid) {
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
                    <input type="text" />
                </div>
                <div id="treeDiv_$panelid" class="haloacl_rightpanel_selecttab_leftpart_treeview">&nbsp;</div>
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
                <div id="datatableDiv_$panelid" class="haloacl_rightpanel_selecttab_rightpart_datatable">&nbsp;</div>
                <div id="datatablepaging_datatableDiv_$panelid"></div>
                </div>
            </div>
            <!-- end of right part -->

        </div>
<script type="text/javascript">
 // user list on the right
    YAHOO.haloacl.datatableInstance$panelid = YAHOO.haloacl.userDataTable("datatableDiv_$panelid");

    // treeview part - so the left part of the select/deselct-view
    YAHOO.haloacl.treeInstance$panelid = new YAHOO.widget.TreeView("treeDiv_$panelid");
    YAHOO.haloacl.treeInstance$panelid.labelClickAction = 'YAHOO.haloacl.datatableInstance$panelid.executeQuery';
    YAHOO.haloacl.buildTreeFirstLevelFromJson(YAHOO.haloacl.treeInstance$panelid);

   


</script>

HTML;
    return $html;

}


function getUsersForUserTable($query,$sort,$dir,$startIndex,$results) {
    $a = array();
    $a['recordsReturned'] = 5;
    $a['totalrecords'] = 10;
    $a['startIndex'] = 0;
    $a['sort'] = null;
    $a['dir'] = "asc";
    $a['pagesize'] = 5;

    $u1 = array('id'=>1,'name'=>'Torben');
    $u2 = array('id'=>2,'name'=>'Ricky');
    $u3 = array('id'=>3,'name'=>'Anna');
    $u4 = array('id'=>4,'name'=>'Detlef');
    $u5 = array('id'=>5,'name'=>'queryCheck:'.$query);


    $a['records'] = array($u1,$u2,$u3,$u4,$u5);

    return(json_encode($a));

}

/* FAKE FUNKTION */
function getGroupsForRightPanel($query) {
    $array = array();

    $tempgroup = array('name'=>"Admins",'id'=>'10');
    $array[] = $tempgroup;

    $tempgroup = array('name'=>"Deppen",'id'=>'11');
    $array[] = $tempgroup;

    $tempgroup = array('name'=>"Dudes",'id'=>'12');
    $array[] = $tempgroup;

    $tempgroup2 = array('name'=>"Sub1",'id'=>'55');
    $tempgroup3 = array('name'=>"Sub2",'id'=>'66');
    $tempgroup4 = array('name'=>"Sub3",'id'=>'77');

    $tempsubgroup = array($tempgroup2,$tempgroup3,$tempgroup4);

    $tempgroup = array('name'=>"Schnuffs",'id'=>'13','childs'=>$tempsubgroup);
    $array[] = $tempgroup;

    // return first level
    if($query == 'all') {

        return(json_encode($array));

    // return childs of Schnuffs
    }elseif($query == 'Schnuffs') {
        return(json_encode($tempsubgroup));

    }else {
        return(json_encode(array()));
    }
}



function manageAclsContent() {
    $response = new AjaxResponse();
    $response->addText("manageAclsContent");
    return $response;
}
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
function whitelistsContent() {
    $response = new AjaxResponse();
    $wLHeadline = wfMsg('hacl_whitelist_headline');
    $wLInfo = wfMsg('hacl_whitelist_info');
    $wLFilter = wfMsg('hacl_whitelist_filter');
    $wLPageSetHeader = wfMsg('hacl_whitelist_pageset_header');
    $wLPageName = wfMsg('hacl_whitelist_pagename');
    $wlAddButton = wfMsg('hacl_whitelist_addbutton');
    $html = <<<HTML
<div id="haclWhitelistContainer">
	<p id="haclWhitelistHeadline">{$wLHeadline}</p>
	<p id="haclWhitelistInfo">{$wLInfo}</p>
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
	</div><!-- End of haclWhitelistPageSetContainer -->
	<!-- <div class="drawLine">&nbsp;</div> -->
	<div id="haclWhitelistAddPageContainer">
		<span id="haclWhitelistPageName">{$wLPageName}</span>
		<input id="haclWhitelistPageInput" type="text" size="30"><input type="button" value="{$wlAddButton}">
	</div>
</div><!-- End of haclWhitelistContainer -->
HTML;
    $response->addText($html);
    return $response;
}

?>
