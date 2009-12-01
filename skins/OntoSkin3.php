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
     *
     *    Using OntoSkin3.
     *
     **/

    function initPage( OutputPage $out ) {
    
            parent::initPage( $out );
            $this->skinname  = 'ontoskin3';
            $this->stylename = 'ontoskin3';
            $this->template  = 'OntoSkin3Template';
          
    }

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
        // $out->addStyle( 'ontoskin3/main.css', 'screen' );
        
        // Append Ontoskin3 css
        $out->addStyle( 'ontoskin3/css/skin-colorfont.css','screen');
        $out->addStyle( 'ontoskin3/css/skin-main.css','screen');

        $out->addStyle( 'ontoskin3/css/skin-pagecontent.css');


        if( $wgHandheldStyle ) {
        // Currently in testing... try 'chick/main.css'
        // $out->addStyle( $wgHandheldStyle, 'handheld' );
        }

        $out->addStyle( 'ontoskin3/IE50Fixes.css', 'screen', 'lt IE 5.5000' );
        $out->addStyle( 'ontoskin3/IE55Fixes.css', 'screen', 'IE 5.5000' );
        $out->addStyle( 'ontoskin3/IE60Fixes.css', 'screen', 'IE 6' );
        $out->addStyle( 'ontoskin3/IE70Fixes.css', 'screen', 'IE 7' );

        $out->addStyle( 'ontoskin3/rtl.css', 'screen', '', 'rtl' );

        // Append to the print styles...
        $out->addStyle( 'ontoskin3/css/skin-printable.css', 'print' );
        
        
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
    function execute(){
        global $wgRequest, $wgUser;
        $this->skin = $skin = $this->data['skin'];
        $action = $wgRequest->getText( 'action' );

        //Load skinlib providing additional feature like halomenu quicklinks etc.
        require_once("OntoSkin3Lib.php");
        //create smwh_Skin Object, which provides functions for menu, quicklings, tabs
        $this->smwh_Skin = new SMWH_Skin($this,$action);
        
        // Suppress warnings to prevent notices about missing indexes in $this->data
        wfSuppressWarnings();

        ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="<?php $this->text('xhtmldefaultnamespace') ?>" <?php
        foreach($this->data['xhtmlnamespaces'] as $tag => $ns) {
                  ?>xmlns:<?php echo "{$tag}=\"{$ns}\" ";
              } ?>xml:lang="<?php $this->text('lang') ?>" lang="<?php $this->text('lang') ?>" dir="<?php $this->text('dir') ?>">
    <head>
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

                 <!-- Ontoskin3 javascripts -->
                 <script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath' ) ?>/<?php $this->text('stylename') ?>/javascript/jquery.js"><!-- jquery.js --></script>
                 <script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath' ) ?>/<?php $this->text('stylename') ?>/javascript/skin.js"><!-- skin.js --></script>
    </head>
    <body<?php if($this->data['body_ondblclick']) { ?> ondblclick="<?php $this->text('body_ondblclick') ?>"<?php } ?>
                                                               <?php if($this->data['body_onload']) { ?> onload="<?php $this->text('body_onload') ?>"<?php } ?>
                                                       class="mediawiki <?php $this->text('dir') ?> <?php $this->text('pageclass') ?> <?php $this->text('skinnameclass') ?>">
        <div id="globalWrapper">
            <?php if ($wgRequest->getText('page') != "plain") : ?>
            <table id="shadows" border="0" cellspacing="0" cellpadding="0" align="center">
                <colgroup>
                    <col width="7"/>
                    <col width="*"  valign="top"/>
                    <col width="7"/>
                </colgroup>
                <tbody>
                    <tr>
                        <td rowspan="2" id="shadow_left" valign="top" width="7">
                            <div id="smwh_HeightShell"/>
                        </td>
                        <td id="shadow_center" width="*" valign="top">
            <!-- Header -->
            <div id="smwh_head">
                <!--  Logo -->
                <div id="smwh_logo">
                    <a href="<?php echo htmlspecialchars($this->data['nav_urls']['mainpage']['href'])?>"<?php
                               echo $skin->tooltipAndAccesskey('p-logo') ?>><img src="<?php $this->text('logopath') ?>"/></a>
                </div>

                <!-- Personalbar -->
                <div id="smwh_personal">
                    <a id="personal_expand" class="limited" href="javascript:smwh_Skin.expandPage()">Change view</a>
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
                        <?php echo $this->smwh_Skin->buildMenuHtml(); ?>


            </div>

            <div id="smwh_breadcrumbs">
                <div id="thispage">
                    <img src="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/img/breadcrumb_pfeil.gif"/>
                </div>
                <div id="breadcrumb">
		</div>
            </div>
            <div id="mainpage">
            <div id="smwh_tabs">
                <?php echo $this->smwh_Skin->buildTabs(); ?>
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
                                    <div class="visualClear"></div>
                                    <?php if($this->data['catlinks']) { ?><div id="catlinks"><?php       $this->html('catlinks') ?></div><?php } ?>
                            <!-- end content -->
                            <?php if($this->data['dataAfterContent']) { $this->html ('dataAfterContent'); } ?>
                            <div class="visualClear"></div>
                        </div>
                    </div>
                </div>
                <?php if ($wgRequest->getText('page') != "plain") : ?>
            </div>
            <div class="visualClear"></div>
            <div id="smwh_pstats"> <?php echo $this->smwh_Skin->showPageStats(); ?> </div>
            <?php endif; // page != 'plain' ?>
            
                
                <?php $this->html('bottomscripts'); /* JS call to runBodyOnloadHook */ ?>
                <?php $this->html('reporttime') ?>
                <?php if ( $this->data['debug'] ): ?>
        <!-- Debug output:
                    <?php $this->text( 'debug' ); ?>

        -->
                <?php endif; ?>
            <?php if ($wgRequest->getText('page') != "plain") : ?>
            </td>
            <td rowspan="2" id="shadow_right" width="7">
                <?php echo $this->smwh_Skin->treeview(); ?>
            </td>
            </tr>
                <tr id="smwh_tr_footer">
                    <td id="smwh_td_footer" valign="bottom">
                        <div id="footer">
                                        <?php echo $this->smwh_Skin->buildQuickLinks(); ?>
                        </div>
                    </td>
                </tr>
            </tbody>
            </table>
            </div>
            <?php endif; // page != 'plain' ?>
        </div>
        <div id="ontomenuanchor"></div>
    </body></html>
        <?php
        wfRestoreWarnings();
    } // end of execute() method

	/*************************************************************************************************/
    function searchBox() {
        global $wgUseTwoButtonsSearchForm;
        ?>
 <div id="smwh_search" class="portlet">
    <div id="searchBody" class="pBody" >
        <form action="<?php $this->text('wgScript') ?>" id="searchform">
                <input type='hidden' name="title" value="<?php $this->text('searchtitle') ?>"/>
                <input id="searchInput" pasteNS="true" class="wickEnabled" name="search" constraints="all" onfocus="this.value='';" type="text"<?php echo $this->skin->tooltipAndAccesskey('search'); ?>
                     value="<?php $this->msg('smw_search_this_wiki'); ?>"/>

                <input type='submit'  src='<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/img/button_go.png' name="go" class="searchButton" id="searchGoButton"	value="<?php $this->msg('searcharticle') ?>"<?php echo $this->skin->tooltipAndAccesskey( 'search-go' ); ?> />
                <input type='submit'  src='<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/img/button_search.png' name="fulltext" class="searchButton" id="mw-searchButton" value="<?php $this->msg('searchbutton') ?>"<?php echo $this->skin->tooltipAndAccesskey( 'search-fulltext' ); ?> />
                

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





} // end of class

