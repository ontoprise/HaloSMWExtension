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



global $wgHooks;
$wgHooks['EditPageBeforeEditButtons'][] = 'AddHaclToolbar';

/**
 Adds
 TODO: document
 */
function AddHaclToolbar ($content_actions) {

    $html = <<<HTML
        <script>
            YAHOO.haloacl.toolbar.actualTitle = '{$content_actions->mTitle}';
            //YAHOO.haloacl.toolbar.loadContentToDiv('hacl_toolbarcontainer','getHACLToolbar',{title:'{$content_actions->mTitle}'});
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
    return $content_actions;
}



function getHACLToolbar($articleTitle) {
    global $wgUser;
    $isPageProtected = false;
    $newArticle = true;


    // does that aritcle exist or is it a new article
    try {
        $article = new Article(Title::newFromText($articleTitle));
        if($article->exists()) {
            $newArticle = false;
        }
    }
    catch(Exception $e) {    }
    
    $array = array();
    $quickacls = HACLQuickacl::newForUserId($wgUser->getId());
    $tpllist = array();
    $protectedWith = "";

    if(!$newArticle) {
    // trying to get assigned right
        try {
            $SD = HACLSecurityDescriptor::newFromName("ACL:Page/".$articleTitle);
            $protectedWith = $SD->getSDName();
            $isPageProtected = true;
        }
        catch(Exception $e) {

        }
    }else{

    }


    foreach($quickacls->getSD_IDs() as $sdid) {
        $sd = HACLSecurityDescriptor::nameForID($sdid);
        $tpllist[] = $sd;
    }

    $html = <<<HTML
        <div id="hacl_toolbarcontainer">

        <div id="hacl_toolbarcontainer_section1">
            Page state:&nbsp;
            <select id="haloacl_toolbar_pagestate">
HTML;
    // bulding protected state indicator
    if($isPageProtected) {
        $html .= "   <option>unprotected</option>
                     <option selected='true'>protected</option>
                     </select>
";
        foreach($tpllist as $tpl) {
            $html .= "<option>".$tpl."</option>";
        }
        $html .="</select>";
    }else {
        $html .= "   <option selected='true'>unprotected</option>
                     <option>protected</option>
                     </select>";
    }

    // building quickacl / protected with-indicator
    if($protectedWith != "" && !in_array($protectedWith, $tpllist)) {
        $tpllist[] = $protectedWith;
    }

    $html .= "&nbsp;with:&nbsp;<select>";
    foreach($tpllist as $tpl) {
        if($tpl == $protectedWith) {
            $html .= "<option selected='true'>$tpl</option>";
        }else {
            $html .= "<option>$tpl</option>";
        }
    }
    $html .= "</select>";



    if(!$newArticle) {
        $html .= <<<HTML
             
        </div>

        <div id="hacl_toolbarcontainer_section3">
            <a target="_blank" href="index.php?title=Special:HaloACL&articletitle={$articleTitle}">&raquo; Advanced access rights definition</a>
        </div>
    </div>
    <div style="clear:both;height:1px;font-size:1px">&nbsp;</div>

HTML;
    }
    return $html;

}