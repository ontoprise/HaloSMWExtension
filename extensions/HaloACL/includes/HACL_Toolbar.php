<?php
/*
 * B2browse Group
 * patrick.hilsbos@b2browse.com
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
    catch(Exception $e)  {    }


    // retrieving quickacl
    #$array = array();
    $quickacls = HACLQuickacl::newForUserId($wgUser->getId());
    $tpllist = array();
    $protectedWith = "";

    // is it a new article?
    if(!$newArticle) {
    // trying to get assigned right
        try {
            $SD = HACLSecurityDescriptor::newFromName("$ns:Page/".$articleTitle);
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
    }


    // does a default template exist?
    try {

        $defaultSD = HACLSecurityDescriptor::newFromName("$ns:Template/".$wgUser->getName());
        $defaultSDExists = true;
        // if no other right is assigned to that article the default will be used
        if(!$isPageProtected) {
            $protectedWith = "Template/".$wgUser->getName();
            $isPageProtected = true;
        }
    }
    catch(Exception $e) {
        $defaultSDExists = false;
    }

    global $haclgIP;
    $html = <<<HTML
    	<script type="text/javascript" src="$haclgIP/scripts/toolbar.js"></script>
    	<script type="text/javascript">
			YAHOO.haloacl.toolbar_initToolbar();     
	    </script>


        <div id="hacl_toolbarcontainer" class="yui-skin-sam">

        <div id="hacl_toolbarcontainer_section1">
            Page state:&nbsp;
HTML;

    if($toolbarEnabled) {
        $html .=       '<select id="haloacl_toolbar_pagestate" onChange="YAHOO.haloacl.toolbar_updateToolbar();">';
    }else {
        $html .=       '<select disabled id="haloacl_toolbar_pagestate" onChange="YAHOO.haloacl.toolbar_updateToolbar();">';
    }
    // bulding protected state indicator
    if($isPageProtected) {
        $html .= "   <option>unprotected</option>
                     <option selected='selected'>protected</option>
                     </select>
";
        $html .="</select>";
    }else {
        $html .= "   <option selected='selected'>unprotected</option>
                     <option>protected</option>
                     </select>";
    }

    // building quickacl / protected with-indicator

    if($protectedWith != "" && !in_array($protectedWith, $tpllist)) {
        $tpllist[] = $protectedWith;
    }

//    if(sizeof($tpllist) > 0) {
        $html .= "<span id='haloacl_template_protectedwith_desc'>&nbsp;with:&nbsp;</span>";
        if($toolbarEnabled) {
            $html .= "<select id='haloacl_template_protectedwith'>";
        }else {
            $html .= "<select disabled id='haloacl_template_protectedwith'>";
        }
        foreach($tpllist as $tpl) {
            if($tpl == $protectedWith) {
                $html .= "<option selected='selected'>$tpl</option>";
            }else {
                $html .= "<option>$tpl</option>";
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
        $html .= <<<HTML
             
        </div>

        <div id="hacl_toolbarcontainer_section3">
            <a id="haloacl_toolbar_advrightlink" target="_blank" href="index.php?title=Special:HaloACL&articletitle={$articleTitle}">&raquo; Advanced access rights definition</a>
        </div>
    </div>

    <script>
YAHOO.haloacl.toolbar_updateToolbar();
    </script>

HTML;
    }
    return $html;

}