<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
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
	function SMWH_Skin( $skintemplate, $action ) {
		global $wgRequest, $wgUser;
		$this->skin = $skin = $skintemplate->data['skin'];
		$this->skintemplate = $skintemplate;
		$this->imagepath = "/ontoskin3/img";
		$this->action = $action;
	}

	/**
	 * Builds the menu and returns html snippet
	 *
	 * @return string
	 *
	 */
	public function buildMenuHtml() {
			$rawmenu = $this->getMenuItems();

		if ( count( $rawmenu ) <= 0 ) {
			return "<div style=\"margin-left: 30px; float:left;color: white\">no menu defined, see <a href=\"http://smwplus.com/index.php/Help:Configuring_the_menu_structure_%28Ontoskin3%29\">smwplus.com</a> for details</div>";
		}

		$index = 0;
		$menu = "<ul class=\"smwh_menulist\">";
		foreach ( $rawmenu as $menuName => $menuItems ) {
			$menu.= "<li class=\"smwh_menulistitem\">";

			//Check if submenu exists
			if ( count( $menuItems ) > 0 ) {
				//If it's a dropdown menu with items in it, add specific css class for traingle as visualization
				$menu.= "<div id=\"smwh_menuhead_$index\" class=\"smwh_menuhead smwh_menudropdown\">" . $this->parseWikiText( $menuName ) . "</div>";
				$menu.= "<div id=\"smwh_menubody_$index\" class=\"smwh_menubody autoW\">";
				$menu.= "<div class=\"smwh_menubody_visible\">";
				foreach ( $menuItems as $menuItem ) {
					$menu.= "<div class=\"smwh_menuitem\">" . $this->buildMenuItemHtml( $menuItem ) . "</div>";
				}
				$menu.= "</div></div>";
			} else {
				//Don't show "dropdowntraingle" when it's only a simple entry without dropdown and subitems
				$menu.= "<div id=\"smwh_menuhead_$index\" class=\"smwh_menuhead\">" . $this->parseWikiText( $menuName ) . "</div>";
			}
			$menu.= "</li>";
			$index++;
		}
//		$menu.= $this->buildMenuMediaWiki();
//		$menu.= $this->buildTools();
		$menu .= "<li style=\"display: none;\" class=\"smwh_menulistitem smwh_menuoverflow\">";
		$menu .= "<div id=\"smwh_menuhead_$index\" class=\"smwh_menuhead\">";
		$menu .= "<p>>></p></div>";
		$menu.= "<div id=\"smwh_menubody_$index\" class=\"smwh_menubody autoW\"><ul></ul></div>";
		$menu .= "</li>";
		$menu.= "</ul>";

		return $menu;
	}

	/**
	 * Gets the configured menu elements from MediaWiki:Halomenu
	 *
	 * @global $parserMemc
	 * @global $wgEnableSidebarCache
	 * @global $wgSidebarCacheExpiry
	 * @global $wgLang
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
		foreach ( $lines as $line ) {

			$line = str_replace( array( '[[SMW::on]]', '[[SMW::off]]' ), '', $line ); //for queries in menu
			//Lines starting with * but not **
			if ( strpos( $line, '*' ) === 0 && strpos( $line, '**' ) === false ) {
				$heading = trim( $line, '*' );
				$heading = trim( $heading );
				if ( !array_key_exists( $heading, $bar ) )
					$bar[$heading] = array();
				continue;
			}

			//Lines starting with **
			if ( strpos( $line, '**' ) === 0 ) {
				$link = trim( $line, '**' );
				$link = trim( $link );
				$title = Title::newFromText( $link );
				if ( $title )
					$bar[$heading][] = $title;
				continue;
			}
		}
		wfRunHooks( 'SkinBuildSidebar', array($this, &$bar) );
		if ( $wgEnableSidebarCache )
			$parserMemc->set( $key, $bar, $wgSidebarCacheExpiry );
		wfProfileOut( __METHOD__ );
		return $bar;
	}

	/**
	 *
	 * Generates the specific content shown under the menu entries
	 *
	 * @param  $menuItem
	 * @return string
	 */
	private function buildMenuItemHtml( $menuItem ) {

		//TODO: Check if this can be unified to work with PHP > 5.2 and PHP < 5.1
		if ( $menuItem instanceof Title ) {
			$titleId = $menuItem->getArticleID();
		} else {
			$titleId = Title::newFromText( trim( $menuItem ) )->getArticleId();
		}
		if ( !$titleId ) {
			return "";
		}
		$menuPage = Article::newFromId( $titleId );

		//This is an workaround to prevent mediawiki using OldId
		//when doing a diff in the pagehistory. OldId would cause a
		//false content for page rendering
		$menuPage->mOldId = 0;

		if ( !isset( $menuPage ) ) {
			return "";
		}
		return $this->parseWikiText( $menuPage->getContent() );
	}

	/**
	 * Generates the mediawiki menu similar to the menu boxes in monobook and returns the html snippet
	 *
	 * @global $wgStylePath
	 * @return string
	 */
	private function buildMenuMediaWiki() {
		global $wgStylePath;

		//Check if config of ontoskin3 is set to show the mediawiki menu
		//by default it's disabled
		$content = wfMsgForContent( 'halomenuconfig' );
		if ( strpos( $content, "showmediawikimenu=true" ) === false ) {
			$hidemediawikimenu = 'style="display:none;"';
		} else {
			$hidemediawikimenu = '';
		}

		$menu = "<!-- Standardmediawiki Menu -->";
		$menu.= "<li class=\"smwh_menulistitem\" " . $hidemediawikimenu . ">";
		$menu.= "<div id=\"smwh_menuhead_mediawiki\" class=\"smwh_menuhead smwh_menudropdown\"><p>MediaWiki";
		$menu.= "</p></div>";
		$menu.= "<div id=\"smwh_menubody_mediwiki\" class=\"smwh_menubody\">";
		$menu.= "<div class=\"smwh_menubody_visible\">";

		//catch echos from mediawik-skin into a variable
		ob_start();
		$sidebar = $this->data['sidebar'];
		if ( !isset( $sidebar['TOOLBOX'] ) )
			$sidebar['TOOLBOX'] = true;
		if ( !isset( $sidebar['LANGUAGES'] ) )
			$sidebar['LANGUAGES'] = true;
		foreach ( $sidebar as $boxName => $cont ) {
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
		$menu .= ob_get_contents();
		//stop catching echos
		ob_end_clean();

		$menu.= "</div></div>";
		$menu.= "</li>";
		return $menu;
	}

	/**
	 * Generates the administrator menu shown for WikiSysops and returns the html snippet
	 *
	 * @global $wgStylePath
	 * @global $wgUser
	 * @return string
	 */
	private function buildTools() {
		global $wgStylePath, $wgUser;

		//Get users groups and check for Sysop-Rights
		$groups = $wgUser->getEffectiveGroups();
		$isAllowed = false;
		if ( in_array( 'sysop', $wgUser->getEffectiveGroups() ) == 1 ) {
			$isAllowed = true;
		}
		if ( $isAllowed == false ) {
			return "";
		}

		$menu = "<!-- Tools Menu -->";
		$menu .= "<li class=\"smwh_menulistitem\">";
		$menu .= "<div id=\"smwh_menuhead_toolbar\" class=\"smwh_menuhead smwh_menudropdown\"><p>Administration</p></div>";

		//Get the content for the administration menu from MediaWiki:haloadministrator
		$content = wfMsgForContent( 'haloadministration' );

		if ( $content != null && $content != "&lt;haloadministration&gt;" ) {
			//parse wiki text and insert the returned html
			$menu .= "<div id=\"smwh_menubody_toolbar\" class=\"smwh_menubody\">";
			$menu .= "<div class=\"smwh_menubody_visible\">";
			$menu .= $this->parseWikiText( $content );
			$menu .= "</div></div>";
		} else {
			//if the administration menu is not defined, return a link to the help section in smwforum describing how to configure it
			$menu .= "<div id=\"smwh_menubody_toolbar\" class=\"smwh_menubody\">";
			$menu .= "<div class=\"smwh_menubody_visible\">";
			$menu .= "<p>no administration menu defined, see <a href=\"http://smwplus.com/index.php/Help:Configuring_the_menu_structure_%28Ontoskin3%29\">smwforum.ontoprise.com</a> for details</p>";
			$menu .= "</div></div>";
		}
		$menu .= "</li>";
		return $menu;
	}

	/**
	 *  Generates the page tabs and returns the html snippet
	 *
	 * @global $IP
	 * @global $wgTitle
	 * @global $wgScriptPath
	 * @global $wgStylePath
	 * @return string
	 */
	public function buildTabs() {
		global $IP, $wgTitle, $wgScriptPath, $wgStylePath, $wgLang;
		$tabs = "<!-- Tabs -->";
		$tabsstart = "<div id=\"tabsleft\">";
		$firstTabs = "";
		//right tab elements
		$functionsaggregated = "";
		$functionsright = $this->buildHelpTab();

		// page incl. name / edit / discussion
		// rest in "more"
		$pageName = htmlspecialchars( $this->skintemplate->data['title'] );

		foreach ( $this->skintemplate->data['content_actions'] as $key => $tab ) {

			if ( substr( $key, 0, 6 ) == "nstab-" ) {
				$tabs = $this->buildContentIcons( $pageName );
				$tabs .= "<div id=\"" . Sanitizer::escapeId( "ca-$key" ) . "\"";
				$tabs .= " class=\"pagetab";
				if ( $tab['class'] ) {
					$tabs .= " " . htmlspecialchars( $tab['class'] );
				}
				$tabs .= "\">";
				$tabs .= $pageName . "</div>";
				if( $wgTitle->getNamespace() == NS_TALK ) {
					//provide back to page link
					$tabs .= '<a class="tab" href="';
					$tabs .= htmlspecialchars( $tab['href'] ) . '">';
					$tabs .= wfMsg( 'smw_back_to_article') . '</a>';
				}
				$firstTabs = $tabs . $firstTabs;
				continue;
			} elseif ( $key == "edit" ) {
				if( in_array( $this->action, array('edit') ) ) {
					// no edit button in edit mode
					continue;
				}
				# build the edit link
				$link = '';
				# if the SF forms are in use, make the edit with semantic forms
				if ( defined( 'SF_VERSION' ) && class_exists( SFFormLinker ) && # SF are included
						isset( $wgTitle ) && # title obj exists and we are not on a special page
						$wgTitle->getNamespace() != NS_SPECIAL ) {
					# check if there are forms available for the current article
					global $asfAutomaticFormExists;
					if ( count( SFFormLinker::getDefaultFormsForPage( $this->skintemplate->skin->getTitle() ) ) > 0
							|| isset( $asfAutomaticFormExists ) )
					{
						$link = htmlspecialchars(
							str_replace( 'action=edit', 'action=formedit', $tab['href'] )
						);
					}
				}
				# none of the conditions above came into action, then use the normal
				# wiki editor for editing pages.
				if ( !$link )
					$link = htmlspecialchars( $tab['href'] );
				# add the href $link now to the tabs
				$tabs = '<a href="' . $link . '"';

				# We don't want to give the watch tab an accesskey if the
				# page is being edited, because that conflicts with the
				# accesskey on the watch checkbox.  We also don't want to
				# give the edit tab an accesskey, because that's fairly su-
				# perfluous and conflicts with an accesskey (Ctrl-E) often
				# used for editing in Safari.
				if ( in_array( $this->action, array('edit', 'submit') )
						&& in_array( $key, array('edit', 'watch', 'unwatch') ) ) {
					$tabs .= $this->skintemplate->skin->tooltip( "ca-$key" );
				} else {
					$tabs .= $this->skintemplate->skin->tooltipAndAccesskey( "ca-$key" );
				}
				$tabs .= ">";
				$tabs .= "<div id=\"" . Sanitizer::escapeId( "ca-$key" ) . "\"";
				$tabs .= " class=\"tab";
				if ( $tab['class'] ) {
					$tabs .= " " . htmlspecialchars( $tab['class'] );
				}
				$tabs .= "\">";

				$tabs.= "<img id=\"editimage\" src=\"" .
					$wgStylePath . $this->imagepath . "/button_edit.gif\" alt=\"edit\"/>";
				$tabs .= htmlspecialchars( $tab['text'] ) . "</div></a>";
				$firstTabs .= $tabs;
			} elseif ( $key == "talk" ) {
				if ( strstr( $tab['class'], 'selected' ) ) {
					// no discussion link when on discussion page
					continue;
				} elseif ( strstr( $tab['class'], 'new' ) ) {
					$tabs = '<a href="' . htmlspecialchars( $tab['href'] ) . '" class="tablink" >';
					$tabs .= "<div id=\"" . Sanitizer::escapeId( "ca-$key" ) . "\"";
					$tabs .= " class=\"aggregatedtabelements";
					if ( $tab['class'] ) {
						$tabs .= " " . htmlspecialchars( $tab['class'] );
					}
					$tabs .= "\">";
					$tabs .= htmlspecialchars( wfMsg( "smw_start_discussion",
						$tab['text'] ) ) . "</div></a>";
					$functionsaggregated .= $tabs;
				} else {
					// discussion bubble only if page exists and we're not already on discussion page
					$tabs = '<a href="' . htmlspecialchars( $tab['href'] ) . '" class="tablink" >';
					$tabs .= "<div id=\"" . Sanitizer::escapeId( "ca-$key" ) . "\"";
					$tabs .= " class=\"tab";
					if ( $tab['class'] ) {
						$tabs .= " " . htmlspecialchars( $tab['class'] );
					}
					$tabs .= "\">";
					$tabs .= htmlspecialchars( $tab['text'] ) . "</div></a>";
					$tabsleft .= $tabs;
				}
			} else {
				$tabs = "<div id=\"" . Sanitizer::escapeId( "ca-$key" ) . "\"";
				$tabs .= " class=\"aggregatedtabelements";
				if ( $tab['class'] ) {
					$tabs .= " " . htmlspecialchars( $tab['class'] );
				}
				$tabs .= "\">";
				$tabs .= '<a href="' . htmlspecialchars( $tab['href'] ) . '"';
				# We don't want to give the watch tab an accesskey if the
				# page is being edited, because that conflicts with the
				# accesskey on the watch checkbox.  We also don't want to
				# give the edit tab an accesskey, because that's fairly su-
				# perfluous and conflicts with an accesskey (Ctrl-E) often
				# used for editing in Safari.
				if ( in_array( $this->action, array('edit', 'submit') )
						&& in_array( $key, array('edit', 'watch', 'unwatch') ) ) {
					$tabs.= $this->skintemplate->skin->tooltip( "ca-$key" );
				} else {
					$tabs.= $this->skintemplate->skin->tooltipAndAccesskey( "ca-$key" );
				}
				$tabs.= ">" . htmlspecialchars( ucfirst( $tab['text'] ) ) . "</a></div>";
				$functionsaggregated .= $tabs;
			}
		}
		$functionsaggregated .= $this->buildPageOptions();

		//Check if there were functions added to the more-tab
		//and don't add the more tab if empty
		if ( $functionsaggregated != "" ) {
			//all functions which are aggregated in the right of the right tab

			$tabmore = "<ul id=\"more\" class=\"tab smwh_menulist\"><li class=\"smwh_menulistitem\">";
			$tabmore .= "<div id=\"smwh_menuhead_more\" class=\"smwh_menuhead\">" . wfMsg( "more_functions" ) . "</div>";
			$tabmore .= "<div id=\"smwh_menubody_more\" class=\"smwh_menubody\">";
			$tabmore .= "<div class=\"smwh_menubody_visible\">";
			$tabmore .= $functionsaggregated . "</div></div></li></ul>";
		} else {
			$tabmore = "";
		}

		//Check if there were functions added to the right-tab
		//and don't add the right tab if it's completly empty
		if ( $functionsright != "" || $functionsaggregated != "" ) {
			//right tab holding all functions other than page/talk
			$tabright .= $tabmore . $functionsright;
		} else {
			$tabright = "";
		}

		//return html for tabs
		return $tabsstart . $firstTabs . $tabsleft . $tabright . "</div>";
	}

	/**
	 * Generates the created by <user> at <date>, on <time> string
	 *
	 * @return string
	 */
	public function buildCreatedBy() {
		global $wgTitle, $wgLang;

		$html = '';
		$rev = $wgTitle->getFirstRevision();
		if ( $rev ) {
			$ts = $rev->getTimestamp();
			$ed = $rev->getUserText();
			if ( $ts && $ed ) {
				$d = $wgLang->date( $ts, true );
				$t = $wgLang->time( $ts, true );
				$html .= "<div class=\"page_createdby\">" .
					wfMsg( 'smw_pagecreation', $ed, $d, $t ) .
					"</div>";
			}
		}
		return $html;
	}

	/**
	 * Generates the Help Icon in the tab bar for the context sensitive help
	 *
	 * @return string
	 */
	private function buildHelpTab() {
		global $wgExtensionFunctions;
		if ( !in_array( 'setupSMWUserManual', $wgExtensionFunctions ) )
			return;
		global $wgStylePath;
		$tab = '<div id="helptab" class="tab">';
		$tab.= '<div id="smw_csh"><img id="helpimage" src="' . $wgStylePath .
			$this->imagepath . '/help_icon.png" alt="help" title="' .
			wfMsg( 'smw_csh_icon_tooltip' ) . '"/></div>';
		$tab.= "</div>";
		return $tab;
	}

	/**
	 * Generates related category icons for the article
	 * @param string $pageName
	 *  Name of the current page
	 *
	 * @return string
	 */
	private function buildContentIcons( $pageName = '' ) {

		if( empty( $pageName ) ) {
			return '';
		}
		$title = Title::newFromText( $pageName );
		if( $title == null || !( $title instanceof Title )  ){
			return '';
		}
		if( !function_exists( "smwfGetSemanticStore") ) {
			return '';
		}
		$store = smwfGetSemanticStore();
		// determine which categories the page is assigned to
		$pageCats = $store->getCategoriesForInstance( $title );
		// special case: category pages
		if( $title->getNamespace() === NS_CATEGORY ) {
			$pageCats[] = Title::newFromText(
				MWNamespace::getCanonicalName(NS_CATEGORY) . ':Category'
			);
		}

		$iconHTML = '<div id="cat_icons">';
		foreach ( $pageCats as $pageCat ) {
			// ----------
			SMWQueryProcessor::processFunctionParams(
				array(
					'[[:' . $pageCat->getFullText() . ']]',
					'[[Category has icon::+]]',
					'?Category has icon='
				),
				$querystring, $params, $printouts
			);

			$params = SMWQueryProcessor::getProcessedParams(
				$params,
				$printouts
			);
			$query = SMWQueryProcessor::createQuery(
				$querystring,
				$params,
				SMWQueryProcessor::INLINE_QUERY,
				"",
				$printouts
			);
			$queryResult = SMWQueryProcessor::getResultFromQuery(
				$query,
				$params,
				$printouts,
				$outputmode,
				SMWQueryProcessor::INLINE_QUERY,
				""
			);
			// "Category has icon" is of type page so the html returned here
			// can contain links etc - so get only the img tag.
			preg_match( '/<img[^>]+>/i', $queryResult, $img );
			$iconHTML .= $img[0] ? $img[0] : '';
		}
		$iconHTML .= '</div>';

		return $iconHTML;
	}

	/**
	 * Generates the quicklinks/footer add the page bottom
	 *
	 * @return string
	 */
	public function buildQuickLinks() {
		global $wgStylePath;

		$quicklinks = "<!-- HaloQuickLinks -->";
		$quicklinks .= "<div id='smwh_quicklinks'>";
		$quicklinks .= "<div class='smwh_quicklinks_static'>";
		$quicklinks .= "<img src='" . $wgStylePath . $this->imagepath . "/logo_smw+_small_trans.png' title='Powered by SMW+' alt='Powered by SMW+'/>";
		$quicklinks .= $this->parseWikiText( "[[Imprint|Imprint]]" );
		$quicklinks .= "<a href='http://smwplus.com/index.php/About_us' title='About ontoprise (link opens in a new window)' target='_blank'>About ontoprise</a>";
		$quicklinks .= $this->parseWikiText( "[[Contact|Contact]]" );
		//$quicklinks .= $this->parseWikiText( "[[Privacy policy|Privacy policy]]" );
		$quicklinks .= $this->parseWikiText( "[[Terms and conditions|Terms & Conditions]]" );
		$quicklinks .= "<a href='http://smwplus.com/index.php/FAQ' title='FAQ (link opens in a new window)' target='_blank'>Frequently asked questions</a>";
		$quicklinks .= "</div>";

		//Get the content for the page options from MediaWiki:halopageoptions
		$content = wfMsgForContent( 'haloquicklinks' );

		//TODO: Make if clause consistent with buildPageOptions
		if ( $content != null && $content != "&lt;haloquicklinks&gt;" ) {
			//parse wiki text and insert the returned html
			$quicklinks .= $this->parseWikiText( $content );
		} else {
			//if the footer is not defined, return a link to the help section in smwforum describing how to configure it
			$quicklinks.= "<p style=\"margin-left: 30px;\">no quicklinks defined, see <a href=\"http://smwplus.com/index.php/Help:Configuring_the_menu_structure_%28Ontoskin3%29\">smwforum.ontoprise.com</a> for details<p>";
		}
		$quicklinks .="</div>";
		//return the html snippet
		return $quicklinks;
	}

	/**
	 * Build the personal quick links
	 *
	 * @return string
	 */
	public function buildPersonalQuickLinks() {
		global $wgUser;

		$wikiText = '<div id="quicklinks">[[Special:SpecialPages|Special pages]]' .
			'| [[Special:DataExplorer|Data Explorer]] | [[Special:QueryInterface|Query Interface]]';
		if( $wgUser->isLoggedIn() ) {
			$wikiText .= '| [[Special:Preferences|Preferences]]';
			$groups = $wgUser->getEffectiveGroups();
			if ( in_array( 'sysop', $wgUser->getEffectiveGroups() ) == 1 ) {
				$wikiText .= '| [[Mediawiki:Haloadministration|Administration]]';
			}
		}
		$html = $this->parseWikiText( $wikiText . '</div>' );

		return '' . $html . '';
	}

	/**
	 * Generates the page options shown in the 'more'-tab below the aggregated mediawiki tabs
	 *
	 * @return string
	 */
	private function buildPageOptions() {
		$pageoptions = "<!-- HaloPageOptions -->";
		$pageoptions .= "<div id=\"smwh_halopageoptions\">";

		//Get the content for the page options from MediaWiki:halopageoptions
		$content = wfMsgForContent( 'halopageoptions' );

		if ( strpos( $content, "halopageoptions" ) == false ) {
			//parse wiki text and insert the returned html
			$pageoptions .= $this->parseWikiText( $content );
		} else {
			//return nothing if the page options are not defined
			return "";
		}
		$pageoptions .="</div>";
		//return the html snippet
		return $pageoptions;
	}

	/**
	 * Parses Wikitext and returns html
	 *
	 * @global object $wgParser
	 * @param  string $text
	 * @return string
	 */
	private function parseWikiText( $text ) {
		global $wgParser, $wgTitle;
		$output = $wgParser->parse( $text, $wgTitle, new ParserOptions() );
		return $output->getText();
	}

	/**
	 * Gets the treeview and returns the html code of it.
	 *
	 * @global $wgStylePath
	 * @return string
	 */
	public function treeview() {
		global $wgStylePath;
		//catch echo of the tree view extension, which consists of the tree
		ob_start();
		//Run the hook, where treeview extension is registered
		wfRunHooks( 'OntoSkinInsertTreeNavigation', array(&$treeview) );
		//add output to menu
		$tree.= ob_get_contents();
		//stop catching echos
		ob_end_clean();

		//Generate the necessary surrounding html if the return of the treeview
		//extension is not empty otherwise return an empty string
		if ( $tree != null && $tree != "" ) {

			//Add the right treeview button
//			$treeview .= '<div id="smwh_treeviewtoggleright" style="display:none" title="' . wfMsg( 'smw_treeviewright' ) . '">';
//			$treeview .= '</div>';

			//Add the treeview itself
			$treeview .= '<div id="smwh_treeview">';
			$treeview .= '<div id="smwh_treeview_head">Tree view <a class="smwh_treeview_close" href="#"></a></div>';
			$treeview .= '<div id="smwh_treeview_content">';
			$treeview .= $tree;
			$treeview .= "</div>";
			$treeview .= "</div>";
			return $treeview;
		} else {
			//return empty string, so nothing tree related is added to the skin html
			return "";
		}
	}

	/**
	 * Generate the information about last modification time, views and watching users
	 *
	 * @return string
	 */
	public function showPageStats() {
		//Select footer links
		$footerlinks = array(
			'lastmod', 'viewcount', 'numberofwatchingusers'
		);

		$pstats = "";

		//Get footer links html
		foreach ( $footerlinks as $aLink ) {
			if ( isset( $this->skintemplate->data[$aLink] ) && $this->skintemplate->data[$aLink] ) {
				$pstats .= $this->skintemplate->html( $aLink );
			}
		}
		//Return html
		return $pstats;
	}

}
