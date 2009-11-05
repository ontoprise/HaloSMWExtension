<?php
/*  Copyright 2007, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//@todo add specific javascript here instead of skin
//@todo add specific css here instead of the skin
//@todo see if constructor can get it's stuff itself, instead of passed

/**
 * This Class is responsible for the menu in OntoSkin
 * and parses the proper Pages to generate it.
 *
 * @author Robert Ulrich
 */
class SMWH_Skin {


    /**
     * Constructor which calls
     *
     * @param <type> $skintemplate
     * @param <type> $action
     */
    function SMWH_Skin($skintemplate,$action) {
       global $wgRequest, $wgUser;
       $this->skin = $skin = $skintemplate->data['skin'];
       $this->skintemplate = $skintemplate;
       $this->imagepath = "/ontoskin3/img";
       $this->action =$action;
    }




    /**
     * Builds the menu and returns html snippet
     *
     * @return string
     *
     */
    public function buildMenuHtml() {
        $rawmenu = $this->getMenuItems();
        $index = 0;
        $menu = "<ul id=\"menuleft\" class=\"smwh_menulist\">";
        foreach($rawmenu as $menuName => $menuItems) {
            $menu.= "<li class=\"smwh_menulistitem\">";
            $menu.= "<div id=\"smwh_menuhead_$index\" class=\"smwh_menuhead\">".$this->parseWikiText($menuName)."</div>";

            //Check if submenu exists
            if ( count($menuItems) > 0)
            {
                $menu.= "<div id=\"smwh_menubody_$index\" class=\"smwh_menubody\">";
                $menu.= "<div class=\"smwh_menubody_visible\">";
                foreach($menuItems as $menuItem) {
                    $menu.= "<div class=\"smwh_menuitem\">".$this->buildMenuItemHtml($menuItem)."</div>";
                }
                $menu.= "</div></div>";
            }
            $menu.= "</li>";
            $index++;
        }


        $menu.= "</ul>";

        $menu.= "<ul id=\"menuright\" class=\"smwh_menulist\">";
        $menu.= $this->buildMenuMediaWiki();
        $menu.= $this->buildTools();
        $menu.= "</ul>";

        return $menu;
    }
    
    
    /**
     * Gets the menu items from MediaWiki:Halomenu
     *
     * @global <type> $parserMemc
     * @global <type> $wgEnableSidebarCache
     * @global <type> $wgSidebarCacheExpiry
     * @global <type> $wgLang
     * @return array
     */

     private function getMenuItems() {

        global $parserMemc, $wgEnableSidebarCache, $wgSidebarCacheExpiry;
        global $wgLang;
        wfProfileIn( __METHOD__ );

        $key = wfMemcKey( 'halomenu', $wgLang->getCode() );

        if ( $wgEnableSidebarCache ) {
            $cachedsidebar = $parserMemc->get( $key );
            if ( $cachedsidebar ) {
                wfProfileOut( __METHOD__ );
                return $cachedsidebar;
            }
        }

        $bar = array();
        $lines = explode( "\n", wfMsgForContent( 'halomenu' ) );
        $heading = '';
        foreach ($lines as $line) {
            if (strpos($line, '* ') === 0) {
                $heading = trim($line, '* ');
                if( !array_key_exists($heading, $bar) ) $bar[$heading] = array();
                continue;
            }
            if (strpos($line, '** ') === 0) {
                $link = trim($line, '** ');
                $title = Title::newFromText( $link );
                if ( $title ) $bar[$heading][] = $title;
                continue;
            }
        }
        wfRunHooks('SkinBuildSidebar', array($this, &$bar));
        if ( $wgEnableSidebarCache ) $parserMemc->set( $key, $bar, $wgSidebarCacheExpiry );
        wfProfileOut( __METHOD__ );
        return $bar;
    }

    /**
     *
     * @param <type> $menuItem
     * @return <type>
     */
    private function buildMenuItemHtml( $menuItem ){
        /** mw 1.15 only */
        $title = Title::newFromText(trim($menuItem))->getArticleId();
        if(!$title) return;
        //echo $title;
        $menuPage = Article::newFromId($title);
	if(!isset($menuPage)) return;
	return $this->parseWikiText($menuPage->getContent());
        /**/

        /** mw 1.13 could work with 1.15 */
        /*
        $title = Title::newFromText(trim($menuItem));
        if(!$title) return;
        //echo $title->getFullURL();
        $menuPage = new Article($title, 0);
        //echo $menuPage->getContent();
        if ($menuPage->exists()) {
            return $this->parseWikiText($menuPage->getContent());
        }/**/

    }

    private function buildMenuMediaWiki() {
        global $wgStylePath;

        $content = wfMsgForContent( 'halomenuconfig' );

        if(strpos($content,"showmediawikimenu=true")===false){
            return "";
        }

        $menu = "<!-- Standardmediawiki Menu -->";
        $menu.= "<li class=\"smwh_menulistitem\">";
        $menu.= "<div id=\"smwh_menuhead_mediawiki\" class=\"smwh_menuhead\"><p>MediaWiki";
        //$menu.= "<img id=\"toolsimage\" src=\"".$wgStylePath.$imagepath."/button_mediawiki.gif\" alt=\"tools\"/>";
        $menu.= "</p></div>";
        $menu.= "<div id=\"smwh_menubody_mediwiki\" class=\"smwh_menubody\">";
        $menu.= "<div class=\"smwh_menubody_visible\">";
        //catch echos from mediawik -skin
        ob_start();

        $sidebar = $this->data['sidebar'];
        if ( !isset( $sidebar['TOOLBOX'] ) ) $sidebar['TOOLBOX'] = true;
        if ( !isset( $sidebar['LANGUAGES'] ) ) $sidebar['LANGUAGES'] = true;
        foreach ($sidebar as $boxName => $cont) {
            if ( $boxName == 'SEARCH' ) {
            //$this->searchBox();
            } elseif ( $boxName == 'TOOLBOX' ) {
                $this->skintemplate->toolbox();
            } elseif ( $boxName == 'LANGUAGES' ) {
                $this->skintemplate->languageBox();
            } else {
                $this->skintemplate->customBox( $boxName, $cont );
            }
        }

        //add output to menu
        $menu.= ob_get_contents();
        //stop catching echos
        ob_end_clean();

        $menu.= "</div></div>";
        $menu.= "</li>";
        return $menu;
    }

    private function buildTools() {

        global $wgStylePath, $wgUser;

        //Get users groups and check for Sysop-Rights
        $groups = $wgUser->getEffectiveGroups();
        $isAllowed = false;
        if (in_array( 'sysop', $wgUser->getEffectiveGroups() ) == 1) $isAllowed = true;
        if($isAllowed == false) return "";

        $menu = "<!-- Tools Menu -->";
        $menu.= "<li class=\"smwh_menulistitem\">";
        $menu.= "<div id=\"smwh_menuhead_toolbar\" class=\"smwh_menuhead\"><p>Administration";
        $menu.= "<img id=\"toolsimage\" src=\"".$wgStylePath.$this->imagepath."/img/button_tools.gif\" alt=\"tools\"/>";
        $menu.= "</p></div>";
        $content = wfMsgForContent( 'haloadministration' );
        if($content!=null){
            $menu.= "<div id=\"smwh_menubody_toolbar\" class=\"smwh_menubody\">";
            $menu.= "<div class=\"smwh_menubody_visible\">";
            $menu.=  $this->parseWikiText($content);
            $menu.= "</div></div>";
        }
        $menu.= "</li>";
        return $menu;
    }

    public function buildTabs() {
        global $IP, $wgTitle, $wgScriptPath, $wgStylePath;
        $tabs  = "<!-- Tabs -->";
        $tabsleft = "<div id=\"tabsleft\">";
        //right tab elements
        $functionsright = "";
        $functionsaggregated ="";

        foreach($this->skintemplate->data['content_actions'] as $key => $tab) {


                                    if( substr($key,0,6) == "nstab-" || $key == "talk" ){
                                            $tabs ="<div id=\"" . Sanitizer::escapeId( "ca-$key" ) . "\"";
                                            $tabs .= " class=\"tab";
                                            if( $tab['class'] ) {
                                                $tabs .= " ".htmlspecialchars($tab['class']);
                                            }
                                            $tabs .= "\">";
                                            $tabs .= '<a href="'.htmlspecialchars($tab['href']).'"';
                                            # We don't want to give the watch tab an accesskey if the
                                            # page is being edited, because that conflicts with the
                                            # accesskey on the watch checkbox.  We also don't want to
                                            # give the edit tab an accesskey, because that's fairly su-
                                            # perfluous and conflicts with an accesskey (Ctrl-E) often
                                            # used for editing in Safari.
                                            if( in_array( $this->action, array( 'edit', 'submit' ) )
                                                && in_array( $key, array( 'edit', 'watch', 'unwatch' ))) {
                                                $tabs.= $this->skintemplate->skin->tooltip( "ca-$key" );
                                            } else {
                                                $tabs.= $this->skintemplate->skin->tooltipAndAccesskey( "ca-$key" );
                                            }
                                            $tabs.= ">".htmlspecialchars($tab['text'])."</a></div>";

                                        $tabsleft .= $tabs;
                                    } else if ($key == "purge" || $key == "history" || $key == "edit") {
                                            $tabs ="<div id=\"" . Sanitizer::escapeId( "ca-$key" ) . "\"";
                                            $tabs .= " class=\"righttabelements";
                                            if( $tab['class'] ) {
                                                $tabs .= " ".htmlspecialchars($tab['class']);
                                            }
                                            $tabs .= "\">";

                                            # build the edit link
                                            $link = '';
                                            # if the SF forms are in use, make the edit with semantic forms
                                            if (defined('SF_VERSION') &&  # SF are included
                                                isset($wgTitle) && # title obj exists and we are not on a special page
                                                $wgTitle->getNamespace() != NS_SPECIAL
                                               ) {
                                                # check if there are forms available for the current article
                        			if (count(SFLinkUtils::getFormsForArticle($this->skintemplate->skin)) > 0)
                                                    $link = htmlspecialchars(
                                                                str_replace('action=edit',
                                                                    'action=formedit',
                                                                    $tab['href']
                                                                )
                                                            );
                                            }
                                            # if the FCKeditor is available use it. Check this with file_exists,
                                            # because there are installations where the include is done only if
                                            # action == edit and mode == wysiwyg. Therefore on page view the FCK
                                            # might not be included at this moment.
                                            if (!$link && $key == "edit" && file_exists($IP.'/extensions/FCKeditor/FCKeditor.php'))
                                                $link = htmlspecialchars($tab['href']).'&mode=wysiwyg';
                                            # none of the conditions above came into action, then use the normal
                                            # wiki editor for editing pages.
                                            if (!$link) $link = htmlspecialchars($tab['href']);
                                            # add the href $link now to the tabs
                                            $tabs .= '<a href="'.$link.'"';

                                            # We don't want to give the watch tab an accesskey if the
                                            # page is being edited, because that conflicts with the
                                            # accesskey on the watch checkbox.  We also don't want to
                                            # give the edit tab an accesskey, because that's fairly su-
                                            # perfluous and conflicts with an accesskey (Ctrl-E) often
                                            # used for editing in Safari.
                                            if( in_array( $this->action, array( 'edit', 'submit' ) )
                                                && in_array( $key, array( 'edit', 'watch', 'unwatch' ))) {
                                                $tabs.= $this->skintemplate->skin->tooltip( "ca-$key" );
                                            } else {
                                                $tabs.= $this->skintemplate->skin->tooltipAndAccesskey( "ca-$key" );
                                            }
                                            $tabs.= ">";
                                            if($key == "edit") {
                                                $tabs.= "<img id=\"editimage\" src=\"".$wgStylePath.$this->imagepath."/button_edit.gif\" alt=\"edit\"/>";
                                            }
                                            $tabs.= htmlspecialchars($tab['text'])."</a></div>";

                                        $functionsright .= $tabs;
                                    } else {
                                            $tabs ="<div id=\"" . Sanitizer::escapeId( "ca-$key" ) . "\"";
                                            $tabs .= " class=\"aggregatedtabelements";
                                            if( $tab['class'] ) {
                                                $tabs .= " ".htmlspecialchars($tab['class']);
                                            }
                                            $tabs .= "\">";
                                            $tabs .= '<a href="'.htmlspecialchars($tab['href']).'"';
                                            # We don't want to give the watch tab an accesskey if the
                                            # page is being edited, because that conflicts with the
                                            # accesskey on the watch checkbox.  We also don't want to
                                            # give the edit tab an accesskey, because that's fairly su-
                                            # perfluous and conflicts with an accesskey (Ctrl-E) often
                                            # used for editing in Safari.
                                            if( in_array( $this->action, array( 'edit', 'submit' ) )
                                                && in_array( $key, array( 'edit', 'watch', 'unwatch' ))) {
                                                $tabs.= $this->skintemplate->skin->tooltip( "ca-$key" );
                                            } else {
                                                $tabs.= $this->skintemplate->skin->tooltipAndAccesskey( "ca-$key" );
                                            }
                                            $tabs.= ">".htmlspecialchars($tab['text'])."</a></div>";
                                       $functionsaggregated .= $tabs;
                                    }

         }

         $tabsleft .=  "</div>";

         $functionsaggregated .= $this->buildPageOptions();

         //Check if there were functions added to the more-tab
         //and don't add the more tab if empty
         if($functionsaggregated != "") {
         //all functions which are aggregated in the right of the right tab
             $tabmore = "<div id=\"aggregated\" class=\"righttabelements\"><ul class=\"smwh_menulist\"><li class=\"smwh_menulistitem\">";
             $tabmore .= "<div id=\"smwh_menuhead_mediawiki\" class=\"smwh_menuhead\">".wfMsg("more_functions")."</div>";
             $tabmore .= "<div id=\"smwh_menubody_mediwiki\" class=\"smwh_menubody\">";
             $tabmore .= $functionsaggregated."</div></li></ul></div>";
         } else {
             $tabmore = "";
         }

         //Check if there were functions added to the right-tab
         //and don't add the right tab if it's completly empty
         if($functionsright != "" || $functionsaggregated != "" ) {
         //right tab holding all functions other than page/talk
             $tabright = "<div id=\"tabsright\"><div class=\"tab\">";
             $tabright .=  $tabmore.$functionsright."</div></div>";
         } else {
             $tabright = "";
         }

         //return html for tabs
         return $tabsleft.$tabright;
    }

    public function buildQuickLinks(){
        $ql = "<!-- HaloQuickLinks -->";
        $ql.= "<div id=\"smwh_quicklinks\">";
        $content = wfMsgForContent( 'haloquicklinks' );
        if($content!=null){

            $ql.=  $this->parseWikiText($content);

        }
        $ql.="</div>";
        return $ql;
    }

    private function buildPageOptions(){
        $ql = "<!-- HaloPageOptions -->";
        $ql.= "<div id=\"smwh_halopageoptions\">";
        $content = wfMsgForContent( 'halopageoptions' );

        if(strpos($content,"halopageoptions")==false){
            $ql.=  $this->parseWikiText($content);
        } else {
            return "";
        }
        $ql.="</div>";
        return $ql;
    }
    /**
     * Parses Wikitext and returns html
     *
     * @global <type> $wgParser
     * @param <type> $text
     * @return <type>
     */
    private function parseWikiText($text){
        //mw1.15
        global $wgParser, $wgTitle;
        $output = $wgParser->parse($text,$wgTitle, new ParserOptions());
        //mw1.13
        //global $wgParser;
        //$output = $wgParser->parse($text,$this->skin->mTitle, new ParserOptions());
        return $output->getText();

    }


    public function treeview() {
        global $wgStylePath;
        //catch echo
        ob_start();
            wfRunHooks( 'OntoSkinInsertTreeNavigation', array( &$treeview ) );
        //add output to menu
        $tree.= ob_get_contents();
        //stop catching echos
        ob_end_clean();

        if($tree!=null && $tree!=""){
            $treeview =  '<div id="smwh_treeviewtoggleright">';
            $treeview .= '<img id="smwh_treeviewtogglerightimg" src="'.$wgStylePath.$this->imagepath.'/arrow_right.gif" alt="tools"/>';
            $treeview .= '</div>';
            $treeview .=  '<div id="smwh_treeviewtoggleleft">';
            $treeview .= '<img id="smwh_treeviewtoggleleftimg" src="'.$wgStylePath.$this->imagepath.'/arrow_left.gif" alt="tools"/>';
            $treeview .= '</div>';
            $treeview .= '<div id="smwh_treeview">';
            $treeview .= $tree;
            $treeview .= "</div>";
            return $treeview;
        } else {
           return "";
        }


    }

}
