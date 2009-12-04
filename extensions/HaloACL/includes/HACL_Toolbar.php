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
 * Description of HACL_Toolbar
 *
 * @author hipath
 */



global $wgAjaxExportList;

$wgAjaxExportList[] = "getHACLToolbar";
$wgAjaxExportList[] = "setToolbarChoose";


global $wgHooks;
$wgHooks['EditPageBeforeEditButtons'][] = 'AddHaclToolbarForEditPage';
$wgHooks['sfEditPageBeforeForm'][] = 'AddHaclToolbarForSemanticForms';



function setToolbarChoose($templateToUse) {
    global $wgUser;

    $_SESSION['haloacl_toolbar'][$wgUser->getName()] = $templateToUse;
    //return "for user ". $wgUser->getName(). " will that template be used: "+$_SESSION['haloacl_toolbar'][$wgUser->getName()];
    return "saved";

}

/**
 Adds
 TODO: document
 */
function AddHaclToolbarForEditPage ($content_actions) {

    if ($content_actions->mArticle->mTitle->mNamespace == HACL_NS_ACL) {
        return $content_actions;
    }
    global $haclgIP;
    $html = <<<HTML
    	<script type="text/javascript" src="$haclgIP/scripts/toolbar.js"></script>
        <script>
            YAHOO.haloacl.toolbar.actualTitle = '{$content_actions->mTitle}';
            YAHOO.haloacl.toolbar.loadContentToDiv('content','getHACLToolbar',{title:'{$content_actions->mTitle}'});
        </script>
HTML;
    #$content_actions->editFormPageTop = 'editFormPageTop';
    #$content_actions->editFormTextTop = 'editFormTextTop';
    $content_actions->editFormTextBeforeContent = $html;
    #$content_actions->editFormTextAfterWarn = 'editFormTextAfterWarn';
    #$content_actions->editFormTextAfterTools = 'editFormTextAfterTools';
    #$content_actions->editFormTextBottom = "editFormTextBottom";
    #$content_actions = array_merge($content_actions, $main_action);   //add a new action

    return true;
}

/**
 Adds
 TODO: document
 */
function AddHaclToolbarForSemanticForms($pageTitle, $html) {
    global $haclgIP;
    $html = <<<HTML
    		<script type="text/javascript" src="$haclgIP/scripts/toolbar.js"></script>
    		<script>
	            YAHOO.haloacl.toolbar.actualTitle = '$pageTitle';
	            YAHOO.haloacl.toolbar.loadContentToDiv('content','getHACLToolbar',{title:'$pageTitle'});
	        </script>
HTML;

    return true;
}



function getHACLToolbar($articleTitle) {
    global $wgUser;
    global $haclgContLang;
    $ns = $haclgContLang->getNamespaces();
    $ns = $ns[HACL_NS_ACL];
    $pagePrefix = $haclgContLang->getPetPrefix(HACLSecurityDescriptor::PET_PAGE);
    $templatePrefix = $haclgContLang->getSDTemplateName();
    
    $isPageProtected = false;
    $toolbarEnabled = true;
    $newArticle = true;


    // does that aritcle exist or is it a new article
    try {
        if (!empty($articleTitle)) {
            $t = Title::newFromText($articleTitle);
            if($t->exists()) {
                $newArticle = false;
            }
        }
    }
    catch(Exception $e) {    }


    // retrieving quickacl
    #$array = array();
    $quickacls = HACLQuickacl::newForUserId($wgUser->getId());
    $tpllist = array();
    $validTmpl = array();
    $protectedWith = "";

    // is it a new article?
    if(!$newArticle) {
    // trying to get assigned right
        try {
            $SD = HACLSecurityDescriptor::newFromName("$ns:$pagePrefix/".$articleTitle);
            $protectedWith = $SD->getSDName();
            $isPageProtected = true;
            if(!$SD->userCanModify($wgUser->getName())) {
                $toolbarEnabled = false;
            }
        }
        catch(Exception $e) {

        }
    }

    // adding quickaclpages to selectbox
    foreach($quickacls->getSD_IDs() as $sdid) {
        $sd = HACLSecurityDescriptor::nameForID($sdid);
        $tpllist[] = $sd;
        
        // Check if the template is valid or corrupted by missing groups, user,...
        try {
        	$sd = HACLSecurityDescriptor::newFromID($sdid);
	        $validTmpl[] = $sd->checkIntegrity() === true ? 'true' : 'false';
        } catch (HACLException $e) {
	        $validTmpl[] = false;
        }
    }


    // does a default template exist?
    $defaultSD = null;
    try {

        $defaultSD = HACLSecurityDescriptor::newFromName("$ns:$templatePrefix/".$wgUser->getName());
        $defaultSDExists = true;
        // if no other right is assigned to that article the default will be used
        if(!$isPageProtected) {
            $protectedWith = "$templatePrefix/".$wgUser->getName();
            $isPageProtected = true;
        }
    } catch(Exception $e) {
        $defaultSDExists = false;
    }



    // building quickacl / protected with-indicator

    if($protectedWith != "" && !in_array($protectedWith, $tpllist)) {
        $tpllist[] = $protectedWith;
        // Check if the template is valid or corrupted by missing groups, user,...
        $validTmpl[] = $defaultSD->checkIntegrity() === true ? 'true' : 'false';
    }

    global $haclgIP;
    $html = <<<HTML
    	<script type="text/javascript" src="$haclgIP/scripts/toolbar.js"></script>
    	<script type="text/javascript">
			YAHOO.haloacl.toolbar_initToolbar();     
	    </script>


        <div id="hacl_toolbarcontainer" class="yui-skin-sam hacl_toolbar_validAcl">

        <div id="hacl_toolbarcontainer_section1">
            Page state:&nbsp;
HTML;

    if($toolbarEnabled) {
        $html .=       '<select id="haloacl_toolbar_pagestate" onChange="YAHOO.haloacl.toolbar_updateToolbar();">';
    }else {
        $html .=       '<select disabled id="haloacl_toolbar_pagestate" onChange="YAHOO.haloacl.toolbar_updateToolbar();">';
    }
    // bulding protected state indicator

    $hacl_protected_label = wfMsg('hacl_protected_label');
    $hacl_unprotected_label = wfMsg('hacl_unprotected_label');

    if($isPageProtected) {
        $html .= "   <option value='unprotected'>$hacl_unprotected_label</option>
                     <option selected='selected' value='protected'>$hacl_protected_label</option>
                     </select>";
        $html .="</select>";
    } elseif (sizeof($tpllist) > 0) {
        $html .= "   <option selected='selected' value='unprotected'>$hacl_unprotected_label</option>
                     <option value='protected'>$hacl_protected_label</option>
                     </select>";
    }else {
        $html .= "   <option value='unprotected'>$hacl_unprotected_label</option>
                     </select>";
    }


    //    if(sizeof($tpllist) > 0) {
    $html .= "<span id='haloacl_template_protectedwith_desc'>&nbsp;with:&nbsp;</span>";
    if($toolbarEnabled) {
        $html .= "<select id='haloacl_template_protectedwith' onChange='YAHOO.haloacl.toolbar_templateChanged();'>";
    }else {
        $html .= "<select disabled id='haloacl_template_protectedwith'>";
    }
    foreach($tpllist as $idx => $tpl) {
    	$validAttr = ' valid="'.$validTmpl[$idx].'" ';
        if($tpl == $protectedWith) {
            $html .= "<option selected='selected' $validAttr>$tpl</option>";
        }else {
            $html .= "<option $validAttr>$tpl</option>";
        }
    }
    $html .= "</select>";
    $html .= <<<HTML
<div id="haloacl_toolbar_popuplink" style="display:inline;float:right">
	<div id="anchorPopup_toolbar" 
	     class="haloacl_infobutton" 
	     onclick="javascript:
	     	var tpw = $('haloacl_template_protectedwith');
	     	var protectedWith = tpw[tpw.selectedIndex].text; 
	     	YAHOO.haloacl.sDpopupByName(protectedWith)">&nbsp;
	</div>
</div>
HTML;
    $html .= '<div id="popup_toolbar"></div>';
    //    }


    if(!$newArticle) {
        $tooltiptext = wfMsg('hacl_advancedToolbarTooltip');

        $html .= <<<HTML
             
        </div>

        <div id="hacl_toolbarcontainer_section3">
            <a id="haloacl_toolbar_advrightlink" target="_blank" href="index.php?title=Special:HaloACL&articletitle={$articleTitle}">&raquo; Advanced access rights definition</a>
        </div>
    </div>

    <script>
YAHOO.haloacl.toolbar_updateToolbar();
    var a = new YAHOO.widget.Tooltip("hacl_toolbarcontainer_section3_tooltip", {
        context:"haloacl_toolbar_advrightlink",
        text:"$tooltiptext",
        zIndex :10
    });
    </script>

HTML;
    }
    return $html;

}