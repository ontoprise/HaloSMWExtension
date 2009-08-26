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
    $isPageProtected = false;

    $array = array();
    try {
        $SD = HACLSecurityDescriptor::newFromName("ACL:Page/".$articleTitle);


        $assignedRights = array();

        //attach inline right texts
        foreach($SD->getInlineRights() as $rightId) {
        //    foreach ($SD->getInlineRightsOfSDs(array($SD->getSDID())) as $key2 => $rightId) {
            $assignedRights[] = HACLRight::newFromID($rightId)->getName();
        }

        $isPageProtected = true;
    }
    catch(Exception $e) {

    }

    $html = <<<HTML
        <div id="hacl_toolbarcontainer">

        <div id="hacl_toolbarcontainer_section1">
            Page state:&nbsp;
            <select id="haloacl_toolbar_pagestate">
HTML;
    if($isPageProtected) {
        $html .= "   <option>unprotected</option>
                     <option selected='true'>protected</option>
                     </select>
                     &nbsp;with:&nbsp;
                    <select>
";
        foreach($assignedRights as $assignedRight) {
            $html .= "<option>".$assignedRight."</option>";
        }
        $html .="</select>";


    }else {
        $html .= "   <option selected='true'>unprotected</option>
                     <option>protected</option>
                     </select>";

    }
    $html .= <<<HTML
             
        </div>
        <div id="hacl_toolbarcontainer_section2">
            &nbsp;
        </div>
        <div id="hacl_toolbarcontainer_section3">
            <a target="_blank" href="index.php?title=Special:HaloACL&articletitle={$articleTitle}">&raquo; Advanced access rights definition</a>
        </div>
    </div>
    <div style="clear:both;height:1px;font-size:1px">&nbsp;</div>

HTML;
    return $html;

}