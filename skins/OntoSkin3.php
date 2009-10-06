<?php
/**    This skin is based on Monobook from Mediawiki 1.15
 *     changes making this compatible to Mediawiki 1.13 have been marked
 */

/**
 * OntoSkin3 nouveau
 *
 * Translated from gwicke's previous TAL template version to remove
 * dependency on PHPTAL.
 *
 * @todo document
 * @file
 * @ingroup Skins
 */

if( !defined( 'MEDIAWIKI' ) )
    die( -1 );

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 * @todo document
 * @ingroup Skins
 */
class SkinOntoSkin3 extends SkinTemplate {


    /**
     *   Using OntoSkin3.
     **/
     
    /** mw 1.13 */
    function initPage( &$out ) {
        SkinTemplate::initPage( $out );
        $this->skinname  = 'ontoskin3';
        $this->stylename = 'ontoskin3';
        $this->template  = 'OntoSkin3Template';
    }

    /**
     *   Using OntoSkin3.
     **/

    /** mw 1.15
    function initPage( OutputPage $out ) {
    
            parent::initPage( $out );
            $this->skinname  = 'ontoskin3';
            $this->stylename = 'ontoskin3';
            $this->template  = 'OntoSkin3Template';

    } */

    function getSkinName() {
	return 'ontoskin3';
    }

    function isSemantic() {
        return true;
    }

    function setupSkinUserCss( OutputPage $out ) {
        global $wgHandheldStyle;

        parent::setupSkinUserCss( $out );

        // Append to the default screen common & print styles...
        $out->addStyle( 'ontoskin3/main.css', 'screen' );
        if( $wgHandheldStyle ) {
        // Currently in testing... try 'chick/main.css'
            $out->addStyle( $wgHandheldStyle, 'handheld' );
        }

        $out->addStyle( 'ontoskin3/IE50Fixes.css', 'screen', 'lt IE 5.5000' );
        $out->addStyle( 'ontoskin3/IE55Fixes.css', 'screen', 'IE 5.5000' );
        $out->addStyle( 'ontoskin3/IE60Fixes.css', 'screen', 'IE 6' );
        $out->addStyle( 'ontoskin3/IE70Fixes.css', 'screen', 'IE 7' );

        $out->addStyle( 'ontoskin3/rtl.css', 'screen', '', 'rtl' );
    }
}

/**
 * @todo document
 * @ingroup Skins
 */
class OntoSkin3Template extends QuickTemplate {
    var $skin;
    /**
     * Template filter callback for OntoSkin3 skin.
     * Takes an associative array of data set from a SkinTemplate-based
     * class, and a wrapper for MediaWiki's localization database, and
     * outputs a formatted page.
     *
     * @access private
     */
    function execute() {
        global $wgRequest;
        $this->skin = $skin = $this->data['skin'];
        $action = $wgRequest->getText( 'action' );

        // Suppress warnings to prevent notices about missing indexes in $this->data
        wfSuppressWarnings();

        ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="<?php $this->text('xhtmldefaultnamespace') ?>" <?php
        foreach($this->data['xhtmlnamespaces'] as $tag => $ns) {
                  ?>xmlns:<?php echo "{$tag}=\"{$ns}\" ";
              } ?>xml:lang="<?php $this->text('lang') ?>" lang="<?php $this->text('lang') ?>" dir="<?php $this->text('dir') ?>">
    <head>
                <?php /** BEGIN HEAD MW 1.15
                 <meta http-equiv="Content-Type" content="<?php $this->text('mimetype') ?>; charset=<?php $this->text('charset') ?>" />
                 <?php $this->html('headlinks') ?>
                 <title><?php $this->text('pagetitle') ?></title>
                 <?php $this->html('csslinks') ?>

                 <!--[if lt IE 7]><script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath') ?>/common/IEFixes.js?<?php echo $GLOBALS['wgStyleVersion'] ?>"></script>
                 <meta http-equiv="imagetoolbar" content="no" /><![endif]-->

                 <?php print Skin::makeGlobalVariablesScript( $this->data ); ?>

                 <script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath' ) ?>/common/wikibits.js?<?php echo $GLOBALS['wgStyleVersion'] ?>"><!-- wikibits js --></script>
                 <!-- Head Scripts -->
                 <?php $this->html('headscripts') ?>
                 <?php	if($this->data['jsvarurl']) { ?>
                 <script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('jsvarurl') ?>"><!-- site js --></script>
                 <?php	} ?>
                 <?php	if($this->data['pagecss']) { ?>
                 <style type="text/css"><?php $this->html('pagecss') ?></style>
                 <?php	}
                 if($this->data['usercss']) { ?>
                 <style type="text/css"><?php $this->html('usercss') ?></style>
                 <?php	}
                 if($this->data['userjs']) { ?>
                 <script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('userjs' ) ?>"></script>
                 <?php	}
                 if($this->data['userjsprev']) { ?>
                 <script type="<?php $this->text('jsmimetype') ?>"><?php $this->htmgl('userjsprev') ?></script>
                 <?php	}
                 if($this->data['trackbackhtml']) print $this->data['trackbackhtml']; ?>
                 END HEAD MW 1.15 */?>

        <!-- BEGIN HEAD MW 1.13-->
        <!-- This header has to be removed when switching to mw 1.15 -->
        <meta http-equiv="Content-Type" content="<?php $this->text('mimetype') ?>; charset=<?php $this->text('charset') ?>" />
        <style type="text/css" media="screen,projection">
            @import "<?php $this->text('stylepath') ?>/common/shared.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";
            @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/css/skin-colorfont.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";
            @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/css/skin-main.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";
            @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/css/skin-pagecontent.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";
        </style>

        <!-- Default monobook css disabled
        <style type="text/css" media="screen,projection">
                @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/main.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";
			@import "<?php $this->text('stylepath') ?>/common/shared.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";
		</style>
        -->
        <style type="text/css" media="screen,projection"> @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/niftyCorners.css"; </style>
                <?php $this->html('headlinks') ?>
        <title><?php $this->text('pagetitle') ?></title>
        <link rel="stylesheet" type="text/css" <?php if(empty($this->data['printable']) ) { ?>media="print"<?php } ?> href="<?php $this->text('stylepath') ?>/ontoskin3/commonPrint.css?<?php echo $GLOBALS['wgStyleVersion'] ?>" />
        <link rel="stylesheet" type="text/css" media="handheld" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/handheld.css?<?php echo $GLOBALS['wgStyleVersion'] ?>" />
        <!--[if lt IE 5.5000]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE50Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
        <!--[if IE 5.5000]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE55Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
        <!--[if IE 6]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE60Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
        <!--[if IE 7]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE70Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
        <!--[if IE 8]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE70Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
        <!--[if lt IE 7]><script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath') ?>/common/IEFixes.js?<?php echo $GLOBALS['wgStyleVersion'] ?>"></script>
		<meta http-equiv="imagetoolbar" content="no" /><![endif]-->
                <?php print Skin::makeGlobalVariablesScript( $this->data ); ?>
        <script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath' ) ?>/common/wikibits.js?<?php echo $GLOBALS['wgStyleVersion'] ?>"><!-- wikibits js --></script>
                <?php	if($this->data['jsvarurl'  ]) { ?>
        <script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('jsvarurl'  ) ?>"><!-- site js --></script>
                <?php	} ?>

                <?php 	global $wgRequest;
                global $wgTitle;
                ?>
                <?php	if($this->data['pagecss'   ]) { ?>
        <style type="text/css"><?php $this->html('pagecss'   ) ?></style>
                <?php	}
                if($this->data['usercss'   ]) { ?>
        <style type="text/css"><?php $this->html('usercss'   ) ?></style>
                <?php	}
                if($this->data['userjs'    ]) { ?>
        <script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('userjs' ) ?>"></script>
                <?php	}
                if($this->data['userjsprev']) { ?>
        <script type="<?php $this->text('jsmimetype') ?>"><?php $this->html('userjsprev') ?></script>
                <?php	}
                if($this->data['trackbackhtml']) print $this->data['trackbackhtml']; ?>
        <!-- Head Scripts -->
                <?php $this->html('headscripts') ?>

        <script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath' ) ?>/<?php $this->text('stylename') ?>/javascript/jquery.js"><!-- jquery.js --></script>
        <script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath' ) ?>/<?php $this->text('stylename') ?>/javascript/skin.js"><!-- skin.js --></script>
        <!-- END HEAD MW 1.13-->


    </head>
    <body<?php if($this->data['body_ondblclick']) { ?> ondblclick="<?php $this->text('body_ondblclick') ?>"<?php } ?>
                                                               <?php if($this->data['body_onload']) { ?> onload="<?php $this->text('body_onload') ?>"<?php } ?>
                                                       class="mediawiki <?php $this->text('dir') ?> <?php $this->text('pageclass') ?> <?php $this->text('skinnameclass') ?>">
        <div id="globalWrapper">
            <?php if ($wgRequest->getText('page') != "plain") : ?>
            <table id="shadows" border="0" cellspacing="0" cellpadding="1" align="center">
                <colgroup>
                    <col width="10">
                    <col width="*">
                    <col width="10">
                </colgroup>
                <tbody>
                    <tr>
                        <td id="shadow_left" width="10">
                        </td>
                        <td id="shadow_center" width="*">
            <!-- Header -->
            <div id="smwh_head">
                <!--  Logo -->
                <div id="smwh_logo">
                    <a href="<?php echo htmlspecialchars($this->data['nav_urls']['mainpage']['href'])?>"<?php
                               echo $skin->tooltipAndAccesskey('p-logo') ?>><img src="<?php $this->text('logopath') ?>"/></a>
                </div>

                <!-- Personalbar -->
                <div id="smwh_personal">
                    <a id="personal_expand" href="javascript:smwh_Skin.expandPage()"> <img src="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/img/button_expandview.gif"/>Expand view</a>
                            <?php foreach($this->data['personal_urls'] as $key => $item) {
                                //echo $key;
                                if(!($key=="login" || $key=="anonlogin" || $key=="logout" || $key=="userpage") ) continue;
                                ?>

                    <a id="personal_<?php echo $key ?>"
                        href="<?php
                                   echo htmlspecialchars($item['href']) ?>"<?php echo $skin->tooltipAndAccesskey('pt-'.$key) ?>
                       class="<?php
                                   if ($item['active']) { ?>active<?php }
                                   if(!empty($item['class'])) { echo htmlspecialchars($item['class']);}?>
                       ">
                                       <?php echo htmlspecialchars($item['text']) ?>
                    </a>
                            <?php } ?>

                </div>
                <!-- Search -->
                        <?php $this->searchBox(); ?>
            </div>

            <div id="smwh_menu">
                        <!-- Halo Menu -->
                        <div id="home">
                            <a href="<?php echo htmlspecialchars($this->data['nav_urls']['mainpage']['href'])?>"<?php
                               echo $skin->tooltipAndAccesskey('p-logo'); ?>>
                                <img src="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/img/menue_mainpageicon.gif" alt="mainpage"/>
                            </a>
                        </div>
                        <?php echo $this->buildMenuHtml(); ?>


            </div>

            <div id="smwh_breadcrumbs">
                <div id="thispage">
                    <img src="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/img/breadcrumb_pfeil.gif"/>
                    <?php $this->data['displaytitle']!=""?$this->html('title'):$this->text('title'); ?>
                </div>
                <div id="breadcrump">
		</div>
            </div>
            <div id="mainpage">
            <table id="mainpagetable" width="100%">
            <colgroup>

                <!-- insert treeview if present -->
                <?php $tree=$this->treeviewBox();
                if($tree!=false){?>
                    <col width="25%">
                <?php } ?>
                    
                <col width="*">
            </colgroup>
            <tr>

                <!-- insert treeview if present -->
                <?php //$tree=$this->treeviewBox();
                if($tree!=false){?>
                    <td valign="top" width="25%">
                        <?php echo $tree; ?>
                    </td>
                <?php } ?>
            
                <!-- normal page content and tabs -->
                <td valign="top" width="*">

            <div id="smwh_tabs">
                <?php echo $this->buildTabs(); ?>
            </div>
                <?php endif; // action != 'plainpage' ?>
                <div id="column-content">
                    <div id="content">
                        <!-- div from mw 1.13 removed 1.15 -->
                        <div id="bodyContent">
                            <h3 id="siteSub"><?php $this->msg('tagline') ?></h3>
                            <div id="contentSub"><?php $this->html('subtitle') ?></div>
                                    <?php if($this->data['undelete']) { ?><div id="contentSub2"><?php     $this->html('undelete') ?></div><?php } ?>
                                    <?php if($this->data['newtalk'] ) { ?><div class="usermessage"><?php $this->html('newtalk')  ?></div><?php } ?>
                                    <?php if($this->data['showjumplinks']) { ?><div id="jump-to-nav"><?php $this->msg('jumpto') ?> <a href="#column-one"><?php $this->msg('jumptonavigation') ?></a>, <a href="#searchInput"><?php $this->msg('jumptosearch') ?></a></div><?php } ?>
                            <!-- start content -->
                                    <?php $this->html('bodytext') ?>
                                    <?php if($this->data['catlinks']) { ?><div id="catlinks"><?php       $this->html('catlinks') ?></div><?php } ?>
                            <!-- end content -->
                            <div class="visualClear"></div>
                        </div>
                    </div>
                </div>
                <?php if ($wgRequest->getText('page') != "plain") : ?>
            </td>
            </tr>
            </table>
            </div>
            <div class="visualClear"></div>
            <div id="footer">
                <?php echo $this->buildQuickLinks(); ?>
            </div>
            <?php endif; // page != 'plain' ?>
            <div id="ontomenuanchor"></div>
                
                <?php $this->html('bottomscripts'); /* JS call to runBodyOnloadHook */ ?>
                <?php $this->html('reporttime') ?>
                <?php if ( $this->data['debug'] ): ?>
        <!-- Debug output:
                    <?php $this->text( 'debug' ); ?>

        -->
                <?php endif; ?>
            <?php if ($wgRequest->getText('page') != "plain") : ?>
            </td>
            <td id="shadow_right" width="10">
            </td>
            </tr>
            </tbody>
            </table>
            </div>
            <?php endif; // page != 'plain' ?>
        </div>
    </body></html>
        <?php
        wfRestoreWarnings();
    } // end of execute() method

	/*************************************************************************************************/
    function searchBox() {
        global $wgUseTwoButtonsSearchForm;
        ?>
<div id="smwh_search" class="portlet">
    <div id="searchBody" class="pBody">
        <form action="<?php $this->text('wgScript') ?>" id="searchform">
                <input type='hidden' name="title" value="<?php $this->text('searchtitle') ?>"/>
                <input id="searchInput" pasteNS="true" class="wickEnabled" name="search" constraints="all" onfocus="this.value='';" type="text"<?php echo $this->skin->tooltipAndAccesskey('search'); ?>
                     value="<?php $this->msg('smw_search_this_wiki'); ?>"/>

                <input type='image' value='Submit' src='<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/img/button_go.gif' name="go" class="searchButton" id="searchGoButton"	value="<?php $this->msg('searcharticle') ?>"<?php echo $this->skin->tooltipAndAccesskey( 'search-go' ); ?> />
                <input type='image' value='Submit' src='<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/img/button_search.gif' name="fulltext" class="searchButton" id="mw-searchButton" value="<?php $this->msg('searchbutton') ?>"<?php echo $this->skin->tooltipAndAccesskey( 'search-fulltext' ); ?> />


        </form>
    </div>
</div>
    <?php
    }

	/*************************************************************************************************/
    function toolbox() {
        ?>
<div class="portlet" id="p-tb">
    <h5><?php $this->msg('toolbox') ?></h5>
    <div class="pBody">
        <ul>
                    <?php
                    if($this->data['notspecialpage']) { ?>
            <li id="t-whatlinkshere"><a href="<?php
                            echo htmlspecialchars($this->data['nav_urls']['whatlinkshere']['href'])
                                                    ?>"<?php echo $this->skin->tooltipAndAccesskey('t-whatlinkshere') ?>><?php $this->msg('whatlinkshere') ?></a></li>
                            <?php
                            if( $this->data['nav_urls']['recentchangeslinked'] ) { ?>
            <li id="t-recentchangeslinked"><a href="<?php
                                echo htmlspecialchars($this->data['nav_urls']['recentchangeslinked']['href'])
                                                              ?>"<?php echo $this->skin->tooltipAndAccesskey('t-recentchangeslinked') ?>><?php $this->msg('recentchangeslinked') ?></a></li>
                            <?php 		}
                        }
                        if(isset($this->data['nav_urls']['trackbacklink'])) { ?>
            <li id="t-trackbacklink"><a href="<?php
                            echo htmlspecialchars($this->data['nav_urls']['trackbacklink']['href'])
                                                    ?>"<?php echo $this->skin->tooltipAndAccesskey('t-trackbacklink') ?>><?php $this->msg('trackbacklink') ?></a></li>
                        <?php 	}
                        if($this->data['feeds']) { ?>
            <li id="feedlinks"><?php foreach($this->data['feeds'] as $key => $feed) {
                                ?><a id="<?php echo Sanitizer::escapeId( "feed-$key" ) ?>" href="<?php
                                   echo htmlspecialchars($feed['href']) ?>" rel="alternate" type="application/<?php echo $key ?>+xml" class="feedlink"<?php echo $this->skin->tooltipAndAccesskey('feed-'.$key) ?>><?php echo htmlspecialchars($feed['text'])?></a>&nbsp;
                        <?php } ?></li><?php
                    }

                    foreach( array('contributions', 'log', 'blockip', 'emailuser', 'upload', 'specialpages') as $special ) {

                        if($this->data['nav_urls'][$special]) {
                            ?><li id="t-<?php echo $special ?>"><a href="<?php echo htmlspecialchars($this->data['nav_urls'][$special]['href'])
                                                                 ?>"<?php echo $this->skin->tooltipAndAccesskey('t-'.$special) ?>><?php $this->msg($special) ?></a></li>
                            <?php		}
                        }

                        if(!empty($this->data['nav_urls']['print']['href'])) { ?>
            <li id="t-print"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['print']['href'])
                                            ?>" rel="alternate"<?php echo $this->skin->tooltipAndAccesskey('t-print') ?>><?php $this->msg('printableversion') ?></a></li><?php
                        }

                        if(!empty($this->data['nav_urls']['permalink']['href'])) { ?>
            <li id="t-permalink"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['permalink']['href'])
                                                ?>"<?php echo $this->skin->tooltipAndAccesskey('t-permalink') ?>><?php $this->msg('permalink') ?></a></li><?php
                        } elseif ($this->data['nav_urls']['permalink']['href'] === '') { ?>
            <li id="t-ispermalink"<?php echo $this->skin->tooltip('t-ispermalink') ?>><?php $this->msg('permalink') ?></li><?php
                    }

                    wfRunHooks( 'MonoBookTemplateToolboxEnd', array( &$this ) );
                    wfRunHooks( 'SkinTemplateToolboxEnd', array( &$this ) );
                    ?>
        </ul>
    </div>
</div>
    <?php
    }

	/*************************************************************************************************/
    function languageBox() {
        if( $this->data['language_urls'] ) {
            ?>
<div id="p-lang" class="portlet">
    <h5><?php $this->msg('otherlanguages') ?></h5>
    <div class="pBody">
        <ul>
                        <?php		foreach($this->data['language_urls'] as $langlink) { ?>
            <li class="<?php echo htmlspecialchars($langlink['class'])?>"><?php
                                ?><a href="<?php echo htmlspecialchars($langlink['href']) ?>"><?php echo $langlink['text'] ?></a></li>
                        <?php		} ?>
        </ul>
    </div>
</div>
        <?php
        }
    }

	/*************************************************************************************************/
    function customBox( $bar, $cont ) {
        ?>
<div class='generated-sidebar portlet' id='<?php echo Sanitizer::escapeId( "p-$bar" ) ?>'<?php echo $this->skin->tooltip('p-'.$bar) ?>>
    <h5><?php $out = wfMsg( $bar ); if (wfEmptyMsg($bar, $out)) echo $bar; else echo $out; ?></h5>
    <div class='pBody'>
                <?php   if ( is_array( $cont ) ) { ?>
        <ul>
                        <?php 			foreach($cont as $key => $val) { ?>
            <li id="<?php echo Sanitizer::escapeId($val['id']) ?>"<?php
                                if ( $val['active'] ) { ?> class="active" <?php }
                                ?>><a href="<?php echo htmlspecialchars($val['href']) ?>"<?php echo $this->skin->tooltipAndAccesskey($val['id']) ?>><?php echo htmlspecialchars($val['text']) ?></a></li>
                            <?php			} ?>
        </ul>
                <?php   } else {
                # allow raw HTML block to be defined by extensions
                    print $cont;
                }
                ?>
    </div>
</div>
    <?php
    }


   function treeviewBox() {
        //catch echo
        ob_start();
            wfRunHooks( 'OntoSkinInsertTreeNavigation', array( &$treeview ) );
        //add output to menu
        $tree.= ob_get_contents();
        //stop catching echos
        ob_end_clean();

        if($tree!=null && $tree!=""){
            $treeview = '<div id="smwh_browser"><div id="smwh_browserview">';
            $treeview .= $tree;
            $treeview .= "</div></div>";
            return $treeview;
        } else {
           return false;
        }
        
 
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
    function getMenuItems() {

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
     * Builds the menu and returns html snippet
     *
     * @return string
     *
     */
    function buildMenuHtml() {
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
     *
     * @param <type> $menuItem
     * @return <type>
     */
    function buildMenuItemHtml( $menuItem ){
        /** mw 1.15 only
        $title = Title::newFromText(trim($menuItem))->getArticleId();
        if(!$title) return;
        echo $title;
        $menuPage = Article::newFromId($title);
	if(!isset($menuPage)) return;
	return $this->parseWikiText($menuPage->getRawText());
        */
        
        /** mw 1.13 could work with 1.15 */
        
        $title = Title::newFromText(trim($menuItem));
        if(!$title) return;
        //echo $title->getFullURL();
        $menuPage = new Article($title, 0);
        //echo $menuPage->getContent();
        if ($menuPage->exists()) {
            return $this->parseWikiText($menuPage->getContent());
        }

    }

    function buildMenuMediaWiki() {
        global $wgStylePath;

        $content = wfMsgForContent( 'halomenuconfig' );
        
        if(strpos($content,"showmediawikimenu=true")===false){
            return "";
        }

        $menu = "<!-- Standardmediawiki Menu -->";
        $menu.= "<li class=\"smwh_menulistitem\">";
        $menu.= "<div id=\"smwh_menuhead_mediawiki\" class=\"smwh_menuhead\"><p>MediaWiki";
        //$menu.= "<img id=\"toolsimage\" src=\"".$wgStylePath."/ontoskin3/img/button_mediawiki.gif\" alt=\"tools\"/>";
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
                $this->toolbox();
            } elseif ( $boxName == 'LANGUAGES' ) {
                $this->languageBox();
            } else {
                $this->customBox( $boxName, $cont );
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

    function buildTools() {

        global $wgStylePath, $wgUser;
        
        //Get users groups and check for Sysop-Rights
        $groups = $wgUser->getEffectiveGroups();
        $isAllowed = false;
        if (in_array( 'sysop', $wgUser->getEffectiveGroups() ) == 1) $isAllowed = true;       
        if($isAllowed == false) return "";
        
        $menu = "<!-- Tools Menu -->";
        $menu.= "<li class=\"smwh_menulistitem\">";
        $menu.= "<div id=\"smwh_menuhead_toolbar\" class=\"smwh_menuhead\"><p>Administration";
        $menu.= "<img id=\"toolsimage\" src=\"".$wgStylePath."/ontoskin3/img/button_tools.gif\" alt=\"tools\"/>";
        $menu.= "</p></div>";
        $content = wfMsgForContent( 'Administration' );
        if($content!=null){
            $menu.= "<div id=\"smwh_menubody_toolbar\" class=\"smwh_menubody\">";
            $menu.= "<div class=\"smwh_menubody_visible\">";
            $menu.=  $this->parseWikiText($content);
            $menu.= "</div></div>";
        }
        $menu.= "</li>";
        return $menu;
    }

    function buildTabs() {
        global $IP, $wgTitle, $wgScriptPath, $wgStylePath;
        $tabs  = "<!-- Tabs -->";
        $tabsleft = "<div id=\"tabsleft\">";
        //right tab elements
        $functionsright = "";
        $functionsaggregated ="";

        foreach($this->data['content_actions'] as $key => $tab) {


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
                                            if( in_array( $action, array( 'edit', 'submit' ) )
                                                && in_array( $key, array( 'edit', 'watch', 'unwatch' ))) {
                                                $tabs.= $this->skin->tooltip( "ca-$key" );
                                            } else {
                                                $tabs.= $this->skin->tooltipAndAccesskey( "ca-$key" );
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
                        			if (count(SFLinkUtils::getFormsForArticle($this->skin)) > 0)
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
                                            if( in_array( $action, array( 'edit', 'submit' ) )
                                                && in_array( $key, array( 'edit', 'watch', 'unwatch' ))) {
                                                $tabs.= $this->skin->tooltip( "ca-$key" );
                                            } else {
                                                $tabs.= $this->skin->tooltipAndAccesskey( "ca-$key" );
                                            }
                                            $tabs.= ">";
                                            if($key == "edit") {
                                                $tabs.= "<img id=\"editimage\" src=\"".$wgStylePath."/ontoskin3/img/button_edit.gif\" alt=\"edit\"/>";
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
                                            if( in_array( $action, array( 'edit', 'submit' ) )
                                                && in_array( $key, array( 'edit', 'watch', 'unwatch' ))) {
                                                $tabs.= $this->skin->tooltip( "ca-$key" );
                                            } else {
                                                $tabs.= $this->skin->tooltipAndAccesskey( "ca-$key" );
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
             $tabmore .= "<div id=\"aggregated\" class=\"righttabelements\"><ul class=\"smwh_menulist\"><li class=\"smwh_menulistitem\">";
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

    function buildQuickLinks(){
        $ql = "<!-- HaloQuickLinks -->";
        $ql.= "<div id=\"smwh_quicklinks\">";
        $content = wfMsgForContent( 'haloquicklinks' );
        if($content!=null){
            
            $ql.=  $this->parseWikiText($content);
            
        }
        $ql.="</div>";
        return $ql;
    }

    function buildPageOptions(){
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
    function parseWikiText($text){
        global $wgParser;
        $output = $wgParser->parse($text,$this->skin->mTitle, new ParserOptions());
        return $output->getText();

    }

} // end of class

