<?php
/**
 * See skin.txt
 *
 * @todo document
 * @addtogroup Skins
 */

if( !defined( 'MEDIAWIKI' ) )
	die( -1 );

/**
 * @todo document
 * @addtogroup Skins
 */
class SkinOntoStandard extends Skin {
	
	/**
	 * SMW+
	 * Sets skin name
	 */
	
	function getSkinName() {
		return "OntoStandard";
	}


	/**
	 * SMW+
	 * Sets skin name
	 */
	function isSemantic(){
		return true;	
	}

	/**
	 *
	 */
	function getHeadScripts( $allowUserJs ) {
		global $wgStylePath, $wgJsMimeType, $wgStyleVersion;

		$s = parent::getHeadScripts( $allowUserJs );
		if ( 3 == $this->qbSetting() ) { # Floating left
			$s .= "<script language='javascript' type='$wgJsMimeType' " .
			  "src='{$wgStylePath}/common/sticky.js?$wgStyleVersion'>" .
			  "</script>\n";
		}

		return $s;
	}

	/**
	 *
	 */
	function getUserStyles() {
		global $wgStylePath, $wgStyleVersion;
		$s = '';
		if ( 3 == $this->qbSetting() ) { # Floating left
			$s .= "<style type='text/css'>\n" .
			  "@import '{$wgStylePath}/common/quickbar.css?$wgStyleVersion';\n</style>\n";
		} else if ( 4 == $this->qbSetting() ) { # Floating right
			$s .= "<style type='text/css'>\n" .
			  "@import '{$wgStylePath}/common/quickbar-right.css?$wgStyleVersion';\n</style>\n";
		}
		$s .= parent::getUserStyles();
		return $s;
	}

	/**
	 *
	 */
	function doGetUserStyles() {
		global $wgStylePath;

		$s = parent::doGetUserStyles();
		$qb = $this->qbSetting();
		$s .= "#breadcrump{ list-style: none;}";
		if ( 2 == $qb ) { # Right
			$s .= "#quickbar { position: absolute; top: 4px; right: 4px; " .
			  "border-left: 2px solid #000000; }\n" .
			  "#article { margin-left: 4px; margin-right: 152px; }\n";
		} else if ( 1 == $qb || 3 == $qb ) {
			$s .= "#quickbar { position: absolute; top: 4px; left: 4px; " .
			  "border-right: 1px solid gray; }\n" .
			  "#article { margin-left: 152px; margin-right: 4px; }\n";
		} else if ( 4 == $qb) {
			$s .= "#quickbar { border-right: 1px solid gray; }\n" .
			  "#article { margin-right: 152px; margin-left: 4px; }\n";
		}
		return $s;
	}

	/**
	 *
	 */
	function getBodyOptions() {
		$a = parent::getBodyOptions();

		if ( 3 == $this->qbSetting() ) { # Floating left
			$qb = "setup(\"quickbar\")";
			if($a["onload"]) {
				$a["onload"] .= ";$qb";
			} else {
				$a["onload"] = $qb;
			}
		}
		return $a;
	}
	
	/**
	 * SMW+
	 * Copied from skin class for adding div bodyContent
	 */
	
	function doBeforeContent() {
		global $wgContLang;
		$fname = 'Skin::doBeforeContent';
		wfProfileIn( $fname );

		$s = '';
		$qb = $this->qbSetting();

		if( $langlinks = $this->otherLanguages() ) {
			$rows = 2;
			$borderhack = '';
		} else {
			$rows = 1;
			$langlinks = false;
			$borderhack = 'class="top"';
		}

		$s .= "\n<div id='content'>\n<div id='topbar'>\n" .
		  "<table border='0' cellspacing='0' width='98%'>\n<tr>\n";

		$shove = ($qb != 0);
		$left = ($qb == 1 || $qb == 3);
		if($wgContLang->isRTL()) $left = !$left;

		if ( !$shove ) {
			$s .= "<td class='top' align='left' valign='top' rowspan='{$rows}'>\n" .
			  $this->logoText() . '</td>';
		} elseif( $left ) {
			$s .= $this->getQuickbarCompensator( $rows );
		}
		$l = $wgContLang->isRTL() ? 'right' : 'left';
		$s .= "<td {$borderhack} align='$l' valign='top'>\n";

		$s .= $this->topLinks() ;
		$s .= "<p class='subtitle'>" . $this->pageTitleLinks() . "</p>\n";

		$r = $wgContLang->isRTL() ? "left" : "right";
		$s .= "</td>\n<td {$borderhack} valign='top' align='$r' nowrap='nowrap'>";
		$s .= $this->nameAndLogin();
		$s .= "\n<br />" . $this->searchForm() . "</td>";

		if ( $langlinks ) {
			$s .= "</tr>\n<tr>\n<td class='top' colspan=\"2\">$langlinks</td>\n";
		}

		if ( $shove && !$left ) { # Right
			$s .= $this->getQuickbarCompensator( $rows );
		}
		$s .= "</tr>\n</table>\n</div>\n";
		$s .= "\n<div id='article'>\n";
		$s .= "\n<div id='bodyContent'>\n";
		$notice = wfGetSiteNotice();
		if( $notice ) {
			$s .= "\n<div id='siteNotice'>$notice</div>\n";
		}
		$s .= $this->pageTitle();
		$s .= $this->pageSubtitle() ;
		$s .= $this->getCategories();
		wfProfileOut( $fname );
		return $s;
	}
	
	function doAfterContent() {
		global $wgContLang, $wgTitle;
		$fname =  'SkinOntoStandard::doAfterContent';
		wfProfileIn( $fname );
		wfProfileIn( $fname.'-1' );

		$s = "\n</div><div id='ontomenuanchor'></div>\n<br style=\"clear:both\" />\n";
		$s .= "\n<div id='footer'>";
		$s .= '<table border="0" cellspacing="0"><tr>';

		wfProfileOut( $fname.'-1' );
		wfProfileIn( $fname.'-2' );

		$qb = $this->qbSetting();
		$shove = ($qb != 0);
		$left = ($qb == 1 || $qb == 3);
		if($wgContLang->isRTL()) $left = !$left;

		if ( $shove && $left ) { # Left
				$s .= $this->getQuickbarCompensator();
		}
		wfProfileOut( $fname.'-2' );
		wfProfileIn( $fname.'-3' );
		$l = $wgContLang->isRTL() ? 'right' : 'left';
		$s .= "<td class='bottom' align='$l' valign='top'>";

		$s .= $this->bottomLinks();
		$s .= "\n<br />" . $this->mainPageLink()
		  . ' | ' . $this->aboutLink()
		  . ' | ' . $this->specialLink( 'recentchanges' )
		  . ' | ' . $this->searchForm()
		  . '<br /><span id="pagestats">' . $this->pageStats() . '</span>';

		$s .= "</td>";
		if ( $shove && !$left ) { # Right
			$s .= $this->getQuickbarCompensator();
		}
		$s .= "</tr></table>\n</div>\n</div>\n</div>\n";

		wfProfileOut( $fname.'-3' );
		wfProfileIn( $fname.'-4' );
		if ( 0 != $qb ) { $s .= $this->quickBar(); }
		wfProfileOut( $fname.'-4' );
		wfProfileOut( $fname );
		return $s;

	}
	
	/**
	* SMW+
	* Copied from skin.php to add  call of BeforePageDisplay
	*/
	
	function outputPage( &$out ) {
		global $wgDebugComments;

		wfProfileIn( __METHOD__ );
		$this->initPage( $out );
		
		// Hook that allows last minute changes to the output page, e.g.
		// adding of CSS or Javascript by extensions.
		wfRunHooks( 'BeforePageDisplay', array( &$out ) );

		$out->out( $out->headElement() );

		$out->out( "\n<body" );
		
		$ops = $this->getBodyOptions();
		foreach ( $ops as $name => $val ) {
			$out->out( " $name='$val'" );
		}
		$out->out( ">\n" );
		$out->out( "<div id='globalWrapper'>\n" );
		if ( $wgDebugComments ) {
			$out->out( "<!-- Wiki debugging output:\n" .
			  $out->mDebugtext . "-->\n" );
		}

		$out->out( $this->beforeContent() );

		$out->out( $out->mBodytext . "\n" );

		$out->out( $this->afterContent() );

		$out->out( $this->bottomScripts() );

		$out->out( $out->reportTime() );
		
		$out->out( "</div>\n" );
		$out->out( "\n</body></html>" );
		wfProfileOut( __METHOD__ );
	}

	function quickBar() {
		global $wgOut, $wgTitle, $wgUser, $wgRequest, $wgContLang;
		global $wgEnableUploads, $wgRemoteUploads;

		$fname =  'Skin::quickBar';
		wfProfileIn( $fname );

		$action = $wgRequest->getText( 'action' );
		$wpPreview = $wgRequest->getBool( 'wpPreview' );
		$tns=$wgTitle->getNamespace();

		$s = "\n<div id='quickbar'>";
		$s .= "\n" . $this->logoText() . "\n<hr class='sep' />";

		$sep = "\n<br />";

		# Use the first heading from the Monobook sidebar as the "browse" section
		$bar = $this->buildSidebar();
		$browseLinks = reset( $bar );

		foreach ( $browseLinks as $link ) {
			if ( $link['text'] != '-' ) {
				$s .= "<a href=\"{$link['href']}\">" .
					htmlspecialchars( $link['text'] ) . '</a>' . $sep;
			}
		}

		if( $wgUser->isLoggedIn() ) {
			$s.= $this->specialLink( 'watchlist' ) ;
			$s .= $sep . $this->makeKnownLink( $wgContLang->specialPage( 'Contributions' ),
				wfMsg( 'mycontris' ), 'target=' . wfUrlencode($wgUser->getName() ) );
		}
		// only show watchlist link if logged in
		$s .= "\n<hr class='sep' />";
		$articleExists = $wgTitle->getArticleId();
		if ( $wgOut->isArticle() || $action =='edit' || $action =='history' || $wpPreview) {
			if($wgOut->isArticle()) {
				$s .= '<strong>' . $this->editThisPage() . '</strong>';
				$s .= $sep . $this->makeKnownLinkObj( $wgTitle, wfMsg( 'annotatethispage' ), "action=annotate" );
			} else { # backlink to the article in edit or history mode
				if($articleExists){ # no backlink if no article
					switch($tns) {
						case NS_TALK:
						case NS_USER_TALK:
						case NS_PROJECT_TALK:
						case NS_IMAGE_TALK:
						case NS_MEDIAWIKI_TALK:
						case NS_TEMPLATE_TALK:
						case NS_HELP_TALK:
						case NS_CATEGORY_TALK:
							$text = wfMsg('viewtalkpage');
							break;
						case NS_MAIN:
							$text = wfMsg( 'articlepage' );
							break;
						case NS_USER:
							$text = wfMsg( 'userpage' );
							break;
						case NS_PROJECT:
							$text = wfMsg( 'projectpage' );
							break;
						case NS_IMAGE:
							$text = wfMsg( 'imagepage' );
							break;
						case NS_MEDIAWIKI:
							$text = wfMsg( 'mediawikipage' );
							break;
						case NS_TEMPLATE:
							$text = wfMsg( 'templatepage' );
							break;
						case NS_HELP:
							$text = wfMsg( 'viewhelppage' );
							break;
						case NS_CATEGORY:
							$text = wfMsg( 'categorypage' );
							break;
						default:
							$text= wfMsg( 'articlepage' );
					}

					$link = $wgTitle->getText();
					if ($nstext = $wgContLang->getNsText($tns) ) { # add namespace if necessary
						$link = $nstext . ':' . $link ;
					}

					$s .= $this->makeLink( $link, $text );
				} elseif( $wgTitle->getNamespace() != NS_SPECIAL ) {
					# we just throw in a "New page" text to tell the user that he's in edit mode,
					# and to avoid messing with the separator that is prepended to the next item
					$s .= '<strong>' . wfMsg('newpage') . '</strong>';
				}

			}

			# "Post a comment" link
			if( ( $wgTitle->isTalkPage() || $wgOut->showNewSectionLink() ) && $action != 'edit' && !$wpPreview )
				$s .= '<br />' . $this->makeKnownLinkObj( $wgTitle, wfMsg( 'postcomment' ), 'action=edit&section=new' );
			
			#if( $tns%2 && $action!='edit' && !$wpPreview) {
				#$s.= '<br />'.$this->makeKnownLink($wgTitle->getPrefixedText(),wfMsg('postcomment'),'action=edit&section=new');
			#}

			/*
			watching could cause problems in edit mode:
			if user edits article, then loads "watch this article" in background and then saves
			article with "Watch this article" checkbox disabled, the article is transparently
			unwatched. Therefore we do not show the "Watch this page" link in edit mode
			*/
			if ( $wgUser->isLoggedIn() && $articleExists) {
				if($action!='edit' && $action != 'submit' )
				{
					$s .= $sep . $this->watchThisPage();
				}
				if ( $wgTitle->userCan( 'edit' ) )
					$s .= $sep . $this->moveThisPage();
			}
			if ( $wgUser->isAllowed('delete') and $articleExists ) {
				$s .= $sep . $this->deleteThisPage() .
				$sep . $this->protectThisPage();
			}
			$s .= $sep . $this->talkLink();
			if ($articleExists && $action !='history') {
				$s .= $sep . $this->historyLink();
			}
			$s.=$sep . $this->whatLinksHere();

			if($wgOut->isArticleRelated()) {
				$s .= $sep . $this->watchPageLinksLink();
			}

			if ( NS_USER == $wgTitle->getNamespace()
				|| $wgTitle->getNamespace() == NS_USER_TALK ) {

				$id=User::idFromName($wgTitle->getText());
				$ip=User::isIP($wgTitle->getText());

				if($id||$ip) {
					$s .= $sep . $this->userContribsLink();
				}
				if( $this->showEmailUser( $id ) ) {
					$s .= $sep . $this->emailUserLink();
				}
			}
			$s .= "\n<br /><hr class='sep' />";
		}

		if ( $wgUser->isLoggedIn() && ( $wgEnableUploads || $wgRemoteUploads ) ) {
			$s .= $this->specialLink( 'upload' ) . $sep;
		}
		$s .= $this->specialLink( 'specialpages' )
		  . $sep . $this->bugReportsLink();
 
		global $wgSiteSupportPage;
		if( $wgSiteSupportPage ) {
			$s .= "\n<br /><a href=\"" . htmlspecialchars( $wgSiteSupportPage ) .
			  '" class="internal">' . wfMsg( 'sitesupport' ) . '</a>';
		}
		
		//Adds breadcrumps
		$s .= "\n<br /><hr class='sep' />";
		$s .= '<div id="breadcrump"></div>';
		//Adds Treenavigation
		$s .= "\n<br /><hr class='sep' />";
		$s .= "<div id='treenavigation'>"; 
		wfRunHooks( 'OntoSkinInsertTreeNavigation', array( &$treeview ) );
		$s .= $treeview;
		$s .= "</div>";
		$s .= "\n<br /></div>\n";
		wfProfileOut( $fname );
		return $s;
	}
	
	/**
	 * SMW+
	 * Adds annotate tab to toplinks
	 */
	 
	function topLinks() {
		global $wgOut, $wgTitle;
		$sep = " |\n";

		$s = $this->mainPageLink() . $sep
		  . $this->specialLink( 'recentchanges' );

		if ( $wgOut->isArticleRelated() ) {
			$s .=  $sep . $this->editThisPage()
			  . $sep . $this->makeKnownLinkObj( $wgTitle, wfMsg( 'annotatethispage' ), "action=annotate" )
			  . $sep . $this->historyLink();
		}
		# Many people don't like this dropdown box
		#$s .= $sep . $this->specialPagesList();
		
		wfRunHooks( 'OntoSkinTemplateNavigationEnd', array( &$navEnd ) );
		if($navEnd != ""){
				$s .= $sep . $navEnd;
		}
		
		$s .= $this->variantLinks();
		
		$s .= $this->extensionTabLinks();

		return $s;
	}

		/**
	 * SMW+
	 * Adds autocompletion to search field
	 */

	function searchForm() {
		global $wgRequest;
		$search = $wgRequest->getText( 'search' );

		$s = '<form name="search" class="inline" method="post" action="'
		  . $this->escapeSearchLink() . "\">\n"
		  . '<input type="text" name="search" pasteNS="true" class="wickEnabled" size="19" value="'
		  . htmlspecialchars(substr($search,0,256)) . "\" />\n"
		  . '<input type="submit" name="go" value="' . wfMsg ('searcharticle') . '" />&nbsp;'
		  . '<input type="submit" name="fulltext" value="' . wfMsg ('searchbutton') . "\" />\n</form>";

		return $s;
	}

	/**
	 * SMW+
	 * Adds annotate tab to footer
	 */

	function bottomLinks() {
		global $wgOut, $wgUser, $wgTitle, $wgUseTrackbacks;
		$sep = " |\n";

		$s = '';
		if ( $wgOut->isArticleRelated() ) {
			$s .= '<strong>' . $this->editThisPage() . '</strong>';
			//Add annotate 'tab'
			$s .= $sep . $this->makeKnownLinkObj( $wgTitle, wfMsg( 'annotatethispage' ), "action=annotate" );
			if ( $wgUser->isLoggedIn() ) {
				$s .= $sep . $this->watchThisPage();
			}
			$s .= $sep . $this->talkLink()
			  . $sep . $this->historyLink()
			  . $sep . $this->whatLinksHere()
			  . $sep . $this->watchPageLinksLink();

			if ($wgUseTrackbacks)
				$s .= $sep . $this->trackbackLink();

			if ( $wgTitle->getNamespace() == NS_USER
			    || $wgTitle->getNamespace() == NS_USER_TALK )

			{
				$id=User::idFromName($wgTitle->getText());
				$ip=User::isIP($wgTitle->getText());

				if($id || $ip) { # both anons and non-anons have contri list
					$s .= $sep . $this->userContribsLink();
				}
				if( $this->showEmailUser( $id ) ) {
					$s .= $sep . $this->emailUserLink();
				}
			}
			if ( $wgTitle->getArticleId() ) {
				$s .= "\n<br />";
				if($wgUser->isAllowed('delete')) { $s .= $this->deleteThisPage(); }
				if($wgUser->isAllowed('protect')) { $s .= $sep . $this->protectThisPage(); }
				if($wgUser->isAllowed('move')) { $s .= $sep . $this->moveThisPage(); }
			}
			$s .= "<br />\n" . $this->otherLanguages();
		}
		return $s;
	}
	
	/**
	 * SMW+
	 * Fixes php error with $wgArticle is non-object 
	 */
	function pageStats() {
		global $wgOut, $wgLang, $wgArticle, $wgRequest, $wgUser;
		global $wgDisableCounters, $wgMaxCredits, $wgShowCreditsIfMax, $wgTitle, $wgPageShowWatchingUsers;

		$oldid = $wgRequest->getVal( 'oldid' );
		$diff = $wgRequest->getVal( 'diff' );
		if ( ! $wgOut->isArticle() ) { return ''; }
		if ( isset( $oldid ) || isset( $diff ) ) { return ''; }
		if ( !is_object($wgArticle) ) { return ''; }
		if ( 0 == $wgArticle->getID() ) { return ''; }

		$s = '';
		if ( !$wgDisableCounters ) {
			$count = $wgLang->formatNum( $wgArticle->getCount() );
			if ( $count ) {
				$s = wfMsgExt( 'viewcount', array( 'parseinline' ), $count );
			}
		}

	        if (isset($wgMaxCredits) && $wgMaxCredits != 0) {
		    require_once('Credits.php');
		    $s .= ' ' . getCredits($wgArticle, $wgMaxCredits, $wgShowCreditsIfMax);
		} else {
		    $s .= $this->lastModified();
		}

		if ($wgPageShowWatchingUsers && $wgUser->getOption( 'shownumberswatching' )) {
			$dbr = wfGetDB( DB_SLAVE );
			$watchlist = $dbr->tableName( 'watchlist' );
			$sql = "SELECT COUNT(*) AS n FROM $watchlist
				WHERE wl_title='" . $dbr->strencode($wgTitle->getDBkey()) .
				"' AND  wl_namespace=" . $wgTitle->getNamespace() ;
			$res = $dbr->query( $sql, 'Skin::pageStats');
			$x = $dbr->fetchObject( $res );

			$s .= ' ' . wfMsgExt( 'number_of_watching_users_pageview',
				array( 'parseinline' ), $wgLang->formatNum($x->n)
			);
		}

		return $s . ' ' .  $this->getCopyright();
	}

}


