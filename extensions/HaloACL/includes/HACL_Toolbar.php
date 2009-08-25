
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
    <div id="hacl_toolbarcontainer">

    </div>
        <script>
            YAHOO.haloacl.toolbar.actualTitle = '{$content_actions->mTitle}';
            //YAHOO.haloacl.toolbar.loadContentToDiv('hacl_toolbarcontainer','getHACLToolbar',{title:'{$content_actions->mTitle}'});
            YAHOO.haloacl.toolbar.loadContentToDiv('bodyContent','getHACLToolbar',{title:'{$content_actions->mTitle}'});
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

        $tempRights = array();

        //attach inline right texts
        foreach (getInlineRightsOfSDs(array($SD->getSDID())) as $key2 => $rightId) {
            $tempright = HACLRight::newFromID($rightId);
            $tempRights[] = array('id'=>$rightId, 'description'=>$tempright->getDescription());
        }

        $tempSD = array('id'=>$SD->getSDID(), 'name'=>$SD->getSDName(), 'rights'=>$tempRights);
        $isPageProtected = true;
        print_r($tempSD);
    }
    catch(Exception $e ){
    
    }

    $html = <<<HTML
        <div id="hacl_toolbarcontainer_section1">
            Page state:&nbsp;
            <select id="haloacl_toolbar_pagestate">
                <option>unprotected</option>
                <option>protected</option>
            </select>
        </div>
        <div id="hacl_toolbarcontainer_section2">
            show all....
        </div>
        <div id="hacl_toolbarcontainer_section3">
            <a href="YAHOO.haloacl.toolbar.jumpToAdvancedRights()">&raquo; Advanced access rights definition</a>
        </div>

HTML;
    return $html;

}