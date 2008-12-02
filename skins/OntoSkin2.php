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
/**
 * OntoSkin nouveau
 *
 * Translated from gwicke's previous TAL template version to remove
 * dependency on PHPTAL.
 *
 * @todo document
 * @addtogroup Skins
 */
		
		if( !defined( 'MEDIAWIKI' ) )
			die( -1 );
		
		/** */
		require_once('includes/SkinTemplate.php');
		
		/**
		 * Inherit main code from SkinTemplate, set the CSS and template filter.
		 * @todo document
		 * @addtogroup Skins
		 */
		class SkinOntoSkin2 extends SkinTemplate {
			
			/** Using OntoSkin2. */
			function initPage( &$out ) {
				SkinTemplate::initPage( $out );
				$this->skinname  = 'ontoskin2';
				$this->stylename = 'ontoskin2';
				$this->template  = 'OntoSkin2Template';
			}
			
			function getSkinName() {
				return 'ontoskin2';
			}
			
			function isSemantic() {
				return true;	
			}
			
		}
		
		/**
		 * @todo document
		 * @addtogroup Skins
		 */
		class OntoSkin2Template extends QuickTemplate {
			var $skin;
			/**
			 * Template filter callback for iswc skin.
			 * Takes an associative array of data set from a SkinTemplate-based
			 * class, and a wrapper for MediaWiki's localization database, and
			 * outputs a formatted page.
			 *
			 * @access private
			 */
			function execute() {
				global $wgUser;
				$this->skin = $skin = $this->data['skin'];		
				// Suppress warnings to prevent notices about missing indexes in $this->data
				wfSuppressWarnings();
				
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="<?php $this->text('xhtmldefaultnamespace') ?>" <?php
	foreach($this->data['xhtmlnamespaces'] as $tag => $ns) {
		?>xmlns:<?php echo "{$tag}=\"{$ns}\" ";
	} ?>xml:lang="<?php $this->text('lang') ?>" lang="<?php $this->text('lang') ?>" dir="<?php $this->text('dir') ?>">
	<head>
		<meta http-equiv="Content-Type" content="<?php $this->text('mimetype') ?>; charset=<?php $this->text('charset') ?>" />
		<style type="text/css" media="screen,projection"> @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/main.css?<?php echo $GLOBALS['wgStyleVersion'] ?>"; </style>
		<style type="text/css" media="screen,projection"> @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/niftyCorners.css?>"; </style>
		<?php $this->html('headlinks') ?>
		<title><?php $this->text('pagetitle') ?></title>
		<link rel="stylesheet" type="text/css" <?php if(empty($this->data['printable']) ) { ?>media="print"<?php } ?> href="<?php $this->text('stylepath') ?>/common/commonPrint.css?<?php echo $GLOBALS['wgStyleVersion'] ?>" />
		<link rel="stylesheet" type="text/css" media="handheld" href="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/handheld.css?<?php echo $GLOBALS['wgStyleVersion'] ?>" />
		<!--[if lt IE 5.5000]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE50Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
		<!--[if IE 5.5000]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE55Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
		<!--[if IE 6]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE60Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
		<!--[if IE 7]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE70Fixes.css?<?php echo $GLOBALS['wgStyleVersion'] ?>";</style><![endif]-->
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
	</head>
	
	
<body <?php if($this->data['body_ondblclick']) { ?>ondblclick="<?php $this->text('body_ondblclick') ?>"<?php } ?>
<?php if($this->data['body_onload'    ]) { ?>onload="<?php     $this->text('body_onload')     ?>"<?php } ?>
 class="mediawiki <?php $this->text('nsclass') ?> <?php $this->text('dir') ?> <?php $this->text('pageclass') ?>">
 	
<!-- Page content -->
 	<div id="globalWrapper">
 		<!-- Header -->
 		<div id="smwf_head">	
 				<div id="smwf_logo">
 					<a <?php
					?>href="<?php echo htmlspecialchars($this->data['nav_urls']['mainpage']['href'])?>"<?php
					echo $skin->tooltipAndAccesskey('n-mainpage') ?>><img src="<?php $this->text('logopath') ?>"/></a>
 				</div>
 				<!-- Personalbar -->
 				<div id="p-personal">
							<?php foreach($this->data['personal_urls'] as $key => $item) { ?>
							<a href="<?php
							echo htmlspecialchars($item['href']) ?>"<?php echo $skin->tooltipAndAccesskey('pt-'.$key) ?><?php
							if(!empty($item['class'])) { ?> class="<?php
							echo htmlspecialchars($item['class']) ?>"<?php } ?>><?php
							echo htmlspecialchars($item['text']) ?></a>
							<?php			} ?>
				</div>
				<!-- Search -->
				<div id="search">
					<form action="<?php $this->text('searchaction') ?>" id="searchform">
						<input id="searchInput" pasteNS="true" class="wickEnabled" name="search" type="text"<?php echo $skin->tooltipAndAccesskey('search');
							if( isset( $this->data['search'] ) ) {
							?> value="<?php $this->text('search') ?>"<?php } ?> />
						<input type='submit' name="go" class="searchButton" id="searchGoButton"	value="<?php $this->msg('searcharticle') ?>" />
						<input type='submit' name="fulltext" class="searchButton" id="mw-searchButton" value="<?php $this->msg('searchbutton') ?>" />
			    	</form>
			    </div>
					
 		</div>
 		<!-- Top category link bar -->
 		<div id="smwf_catlinkblock">
		</div>
		
		<div id="smwf_breadcrump">
			<table id="smwf_breadcrump_table"><tr><td>
				<div id="breadcrump">
				</div>
			</td></tr></table>
		</div>
		<!-- page tab block -->
		<div id="smwf_tabblock">
			<div id="smwf_tabcontainer">
					<table><tr>
					<?php			foreach($this->data['content_actions'] as $key => $tab) { ?>
				 	<th>
				 		<div id="ca-<?php echo Sanitizer::escapeId($key) ?>" class="smwf_tabs <?php
					 	if($tab['class']) { echo htmlspecialchars($tab['class']);}?>">
					 		<a href="<?php echo htmlspecialchars($tab['href']) ?>"<?php echo $skin->tooltipAndAccesskey('ca-'.$key) ?>><?php
					 	echo htmlspecialchars($tab['text']) ?></a>
					 	</div>
					 </th>
					<?php			 } ?>
					</tr></table>
			</div>
		</div>
		
		
		<!-- Main block with menu on the left side and page on the right side -->
		<div id="smwf_mainblock">
			
			<!--  left side menu  -->
			<div id="smwf_naviblock">
				<?php 
					$sidebar = $this->data['sidebar'];		
					if ( !isset( $sidebar['SEARCH'] ) ) $sidebar['SEARCH'] = true;
					if ( !isset( $sidebar['TOOLBOX'] ) ) $sidebar['TOOLBOX'] = true;
					if ( !isset( $sidebar['LANGUAGES'] ) ) $sidebar['LANGUAGES'] = true;
					if ( !isset( $sidebar['TREEVIEW'] ) ) $sidebar['TREEVIEW'] = true;
					foreach ($sidebar as $boxName => $cont) {
						if ( $boxName == 'SEARCH' ) {
							//$this->searchBox();
						} elseif ( $boxName == 'TOOLBOX' ) {
							$this->toolbox();
						} elseif ( $boxName == 'LANGUAGES' ) {
							$this->languageBox();
						} elseif ( $boxName == 'TREEVIEW' ) {
							$this->treeviewBox();
						} else {
							$this->customBox( $boxName, $cont );
						}
					}
				?>
				
			</div>
			
			<!-- right side	page -->
			<div id="smwf_pageblock" class="smwf_pageblock">
				<div id="content">
					<?php 	global $wgRequest,$wgTitle;
				
							if ($wgRequest->getText('action') == "edit" || $wgRequest->getText('action') == "annotate" || ($wgTitle->getPrefixedText() == $wgTitle->getNsText().":".wfMsg('search')))
							{ ?>
							<div id="slider">	
							</div>
							<!--This contenttabposdiv div is just a workaround for a position bug in ie and has no further function-->
							<div id="contenttabposdiv">
							<table id="contenttab">
							  <tr>
								<td id="contentcol1">
				
								<!--<div id="clearfloat">-->
							    <div id="innercontent">
									<a name="top" id="top"></a>
									<?php if($this->data['sitenotice']) { ?><div id="siteNotice"><?php $this->html('sitenotice') ?></div><?php } ?>
									<h1 class="firstHeading"><?php $this->data['displaytitle']!=""?$this->html('title'):$this->text('title') ?></h1>
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
								</td>
								<td id="contentcol2">
								<?php if ($_REQUEST['mode'] !== 'wysiwyg') echo '<div id="ontomenuanchor"></div>' ?>
									<!-- This is the location, where the ontoskin.js will insert the toolbar. -->
					            
					            <!--</div>-->
					            </td>
					            </tr>
					        </table>
							</div>	
							<?php
							}
							else
							{ ?>
				                       
									<div id="innercontent">
									<a name="top" id="top"></a>
									<?php if($this->data['sitenotice']) { ?><div id="siteNotice"><?php $this->html('sitenotice') ?></div><?php } ?>
									<h1 class="firstHeading"><?php $this->data['displaytitle']!=""?$this->html('title'):$this->text('title') ?></h1>
									<div id="smwf_socialbookmarks">																								
									</div>
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
				
							<?php
							} ?>
					  </div>
				</div>
			</div> 
		
		
			<div id="smwf_footerblock">
				<div id="footer">
							<?php
							if($this->data['poweredbyico']) { ?>
							<div id="f-poweredbyico"><?php $this->html('poweredbyico') ?></div>
							<?php 	}
							if($this->data['copyrightico']) { ?>
							<div id="f-copyrightico"><?php $this->html('copyrightico') ?></div>
							<?php	} ?>						
							<?php // Generate additional footer links
								$footerlinks = array(
								'lastmod', 'viewcount', 'numberofwatchingusers', 'credits', 'copyright',
								'privacy', 'about', 'disclaimer', 'tagline',
								);
								foreach( $footerlinks as $aLink ) {
								if( isset( $this->data[$aLink] ) && $this->data[$aLink] ) {
								?><div class="footerlinks" id="<?php echo$aLink?>"><?php $this->html($aLink) ?></div>
								<?php 		}
								}?>							
				</div>
			</div>
	</div>


	
<?php $this->html('bottomscripts'); /* JS call to runBodyOnloadHook */ ?>

<!-- Nifty cube for round corners -->

	<script type="text/javascript" src="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/niftycube.js"></script>
	<script type="text/javascript">
		var smwgoldStartup = window.onload; 
		window.onload=function(){
			if (typeof smwgoldStartup == 'function'){
		    	smwgoldStartup();
		    }
		    //Setting of all elemtents with round corners 
			Nifty("div.selected","top transparent");
			Nifty("div.smwf_navihead","normal");
			//Nifty("div.smwf_naviitem","normal");
			Nifty("div.blueround_head","top");
			Nifty("div.round","big");
		}
		
		function switchVisibility(container) {
			var visible = $(container).visible();
			if ( visible ) {	
				$(container).hide();
			} else {
				$(container).show();
		}
	}
	</script>

<?php $this->html('reporttime') ?>
<?php if ( $this->data['debug'] ): ?>
<!-- Debug output:
<?php $this->text( 'debug' ); ?>
-->
<?php endif; ?>
</body></html>
<?php
	wfRestoreWarnings();
	} // end of execute() method
/*************************************************************************************************/
	function searchBox() {
	?>
				<div id="search">
					<form action="<?php $this->text('searchaction') ?>" id="searchform">
						<input id="searchInput" pasteNS="true" class="wickEnabled" name="search" type="text"<?php echo $this->skin->tooltipAndAccesskey('search');
							if( isset( $this->data['search'] ) ) {
							?> value="<?php $this->text('search') ?>"<?php } ?> />
						<input type='submit' name="go" class="searchButton" id="searchGoButton"	value="<?php $this->msg('searcharticle') ?>" />
						<input type='submit' name="fulltext" class="searchButton" id="mw-searchButton" value="<?php $this->msg('searchbutton') ?>" />
			    	</form>
			    </div>
	<?php
	}

	/*************************************************************************************************/
	function toolbox() {
		?>
		<div id="smwf_toolbox">
					<div class="smwf_navihead" onclick="smwhg_generalGUI.switchVisibilityWithState('toolboxlist')"><?php $this->msg('toolbox') ?>
					<img class="icon_navi" onmouseout="(src='<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/expandable.gif')" onmouseover="(src='<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/expandable-act.gif')" src="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/expandable.gif"/>
					</div>
					<div id="toolboxlist" class="smwf_navilist">
							<table class="naviitemtable">
							
							<?php if($this->data['notspecialpage']) { ?>
							<tr><td>
								<div class="smwf_naviitem" id="t-whatlinkshere"><a href="<?php
								echo htmlspecialchars($this->data['nav_urls']['whatlinkshere']['href'])
								?>"<?php echo $this->skin->tooltipAndAccesskey('t-whatlinkshere') ?>><?php $this->msg('whatlinkshere') ?>
								</a></div>
							</td></tr>
							
							<?php	if( $this->data['nav_urls']['recentchangeslinked'] ) { ?>
							<tr><td>
								<div class="smwf_naviitem" id="t-recentchangeslinked"><a href="<?php
								echo htmlspecialchars($this->data['nav_urls']['recentchangeslinked']['href'])
								?>"<?php echo $this->skin->tooltipAndAccesskey('t-recentchangeslinked') ?>><?php $this->msg('recentchangeslinked') ?>
						2		</a></div>
							</td></tr>	
							<?php 		}
							}
							if(isset($this->data['nav_urls']['trackbacklink'])) { ?>
							<tr><td>
								<div class="smwf_naviitem" id="t-trackbacklink"><a href="<?php
								echo htmlspecialchars($this->data['nav_urls']['trackbacklink']['href'])
								?>"<?php echo $this->skin->tooltipAndAccesskey('t-trackbacklink') ?>><?php $this->msg('trackbacklink') ?>
								</a></div>
							</td></tr>
							<?php 	}
							if($this->data['feeds']) { ?>
							<tr><td>
								<div class="smwf_naviitem" id="feedlinks"><?php foreach($this->data['feeds'] as $key => $feed) {
								?><span id="feed-<?php echo Sanitizer::escapeId($key) ?>"><a href="<?php
								echo htmlspecialchars($feed['href']) ?>"<?php echo $this->skin->tooltipAndAccesskey('feed-'.$key) ?>><?php echo htmlspecialchars($feed['text'])?></a>&nbsp;</span>
								<?php } ?>
								</div>
							</td></tr>
							<?php }
							global $wgTitle;
		
							foreach( array('contributions', 'blockip', 'emailuser', 'upload', 'specialpages', 'ontologybrowser', 'smw_viewinOB', 'smw_editwysiwyg', 'gardening', 'gardeninglog', 'findwork', 'queryinterface', 'smw_ti_termimport') as $special ) {						
								if($this->data['nav_urls'][$special]) {?>
									<tr><td>
										<div class="smwf_naviitem" id="t-<?php echo $special ?>"><a href="<?php echo htmlspecialchars($this->data['nav_urls'][$special]['href'])
										?>"<?php echo $this->skin->tooltipAndAccesskey('t-'.$special) ?>><?php $this->msg($special) ?>
										</a></div>
									</td></tr>
							<?php		}
							}
							if(!empty($this->data['nav_urls']['print']['href'])) { ?>
									<tr><td>
										<div class="smwf_naviitem" id="t-print"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['print']['href'])
										?>"<?php echo $this->skin->tooltipAndAccesskey('t-print') ?>><?php $this->msg('printableversion') ?>
										</a></div>
									</td></tr>
							<?php }
							if(!empty($this->data['nav_urls']['permalink']['href'])) { ?>
								<tr><td>
									<div class="smwf_naviitem" id="t-permalink"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['permalink']['href'])
									?>"<?php echo $this->skin->tooltipAndAccesskey('t-permalink') ?>><?php $this->msg('permalink') ?>
									</a></div>
								</td></tr>
							<?php
							} elseif ($this->data['nav_urls']['permalink']['href'] === '') { ?>
								<tr><td>
									<div class="smwf_naviitem" id="t-ispermalink"<?php echo $this->skin->tooltip('t-ispermalink') ?>><?php $this->msg('permalink') ?></div>
								</td></tr>	
							<?php } ?>
							<tr><td>
								<ul> <?php
							 			wfRunHooks( 'MonoBookTemplateToolboxEnd', array( &$this ) );
										wfRunHooks( 'SkinTemplateToolboxEnd', array( &$this ) );
								?> </ul>
							</td></tr><?php
							$result=array();
							wfRunHooks( 'OntoSkinTemplateToolboxEnd', array(&$this, &$result));
							foreach($result as $toolboxlink){
								echo '<tr><td>'.$toolboxlink.'</td></tr>';
							} ?>							
							</table>
					</div>
				</div>
	<?php	
	}

	/*************************************************************************************************/
	function languageBox() { 
		if( $this->data['language_urls'] ) {?>
		<div id='smwf_language'>
			<div class="smwf_navihead" onclick="smwhg_generalGUI.switchVisibilityWithState('smwf_languageview')"><?php $this->msg('otherlanguages')?>
					<img class="icon_navi" onmouseout="(src='<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/expandable.gif')" onmouseover="(src='<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/expandable-act.gif')" src="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/expandable.gif"/>
			</div>
			<div id="smwf_languageview">						
				<table class="naviitemtable">
					<?php	foreach($this->data['language_urls'] as $langlink) { ?>
					<tr><td><?php
					?><a href="<?php echo htmlspecialchars($langlink['href']) ?>"><?php echo $langlink['text'] ?></a>
					</td></tr>
					<?php		} ?>
				</table>			
						
			</div>
		</div>
		
		<?php
			}
	}
	/*************************************************************************************************/
	function treeviewBox() { ?>
		<div id='smwf_browser'>
					<div class="smwf_navihead" onclick="smwhg_generalGUI.switchVisibilityWithState('smwf_browserview')">Browser
					<img class="icon_navi" onmouseout="(src='<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/expandable.gif')" onmouseover="(src='<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/expandable-act.gif')" src="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/expandable.gif"/>
					</div>
					<div id="smwf_browserview">						
						<?php wfRunHooks( 'OntoSkinInsertTreeNavigation', array( &$treeview ) );
						$webcode .= $treeview; ?>
					</div>
		</div>
		  
	<?php }

	/*************************************************************************************************/
	function customBox( $bar, $cont ) {
		?>
					<div class="smwf_navihead" onclick="smwhg_generalGUI.switchVisibilityWithState('navigationlist')"><?php $out = wfMsg( $bar ); if (wfEmptyMsg($bar, $out)) echo $bar; else echo $out; ?>
					<img class="icon_navi" onmouseout="(src='<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/expandable.gif')" onmouseover="(src='<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/expandable-act.gif')" src="<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/expandable.gif"/>
					</div>
					<div id="navigationlist" class="smwf_navilist">
							<table class="naviitemtable">
							<?php foreach($cont as $key => $val) { ?>
							<tr><td>
							<div class="smwf_naviitem" id="<?php echo Sanitizer::escapeId($val['id'])?>"<?php if ( $val['active'] ) { ?> class="active" <?php }?>>						
							<a href="<?php echo htmlspecialchars($val['href']) ?>"<?php echo $this->skin->tooltipAndAccesskey($val['id']) ?>><?php echo htmlspecialchars($val['text']) ?>
							</a></div>
							</td></tr>
							<?php }						
							$result=array();
							wfRunHooks( 'OntoSkinTemplateNavigationEnd', array(&$this, &$result));
							foreach($result as $templatelink){
								echo '<tr><td>'.$templatelink.'</tr></td>';
							} 								
							?>		
								
													
							</table>
					</div>
				
		<?php 
	}
	
} // end of class
?>