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

/**
 * jQuery plugin for Ontoskin3
 *
 * @author Robert Ulrich and Benjamin Langguth
 * @since Version 1.6.1
 * 
 */

;(function( $, win, doc, mw ) {
	$.Ontoskin = function( el ) {
		var	base = this,
			expanded = false,
			externalOptions;

		base.$el = $( el );
		base.el  = el;
		base.$el.data( 'Ontoskin', base );
		base.resizelisteners = [];

		// Default settings, is extended with the hash passed when initializing.
		base.settings = {
			menu : {
				id : '#smwh_menu',
				useMega : false
			},
			elems : {
				center : '.shadows, #smwh_menu > div:first-child, .smwh_megamenu',
				personalBar : '#personal_expand',
				menuHead : '.smwh_menuhead',
				menuBody : '.smwh_menubody',
				more : '#more'
			},
			classes : {
				center : 'smwh_center'
			},
			treeView : {
				elems : {
					toggle : '#smwh_treeviewtoggle',
					general : '#smwh_treeview',
					head : '#smwh_treeview_head',
					close : '.smwh_treeview_close'
				}
			}
		};

		externalOptions = mw.config.get( 'wgOntoSkin' );
		if( externalOptions && typeof externalOptions === 'object' ) {
			// the default options are extended with the options defined in php
			$.extend( true, base.settings, externalOptions );
		}

		// cache elements
		base.$menuList = base.$el.find('.smwh_menulist' );
		base.$more = $( base.settings.elems.more );
		base.$tv = $( base.settings.treeView.elems.general );
		base.$tvHead = $( base.settings.treeView.elems.head );
		base.$home = base.$el.find( '#home' );
		base.$subMenuItem = base.$el.find( '.smwh_menuoverflow' );
		base.$searchBox = base.$el.find( '#smwh_search' );

		// Function declarations from here on.
		/**
		 * @brief function init
		 *		initialize function called once on setup
		 */
		base.init = function() {
			var footerPositionTop = $( '#footer' ).position().top,
				footerMarginBottom = parseInt( $( '#footer' )
					.css( 'marginTop' ), 10),
				mainPositionTop = $( '#main' ).position().top,
				mainHeight = $( '#main' ).outerHeight(),
				diff = (footerPositionTop + footerMarginBottom - 10) -
					( mainPositionTop + mainHeight ),
				state = $.cookie( 'smwSkinExpanded' );

			base.$menuDump = base.$menuList.clone();
			if( diff > 0 ) {
				$( '#main' ).css( 'min-height', mainHeight + diff );
			}
			base.modifyEditLinks();

			if( state === true && expanded === false ) {
				base.resizePage();
			}

			if( !( 'placeholder' in doc.createElement( 'input' ) ) ) {
				$( '#searchInput' ).placeholder();
			}

			if( base.settings.menu.useMega ) {
				base.makeMegaMenu();
			}

			setTimeout( base.hideTree, 1500 );
			base.initTree();
			base.registerEventHandler();

			base.$menuList.css( 'width' , 'auto' );
			base.calculateMenuSize();
			if( base.menuListSize > base.maxMenuWidth ) {
				base.createSubMenus();
			} else {
				base.$menuList.show();
			}
			base.$menuList.css( 'overflow', 'visible' );
		}

		/**
		 * @brief function registerEventHandler
		 *		Takes care of all event bindings
		 */
		base.registerEventHandler = function() {
			// the menubar
			// edge case: useMega === true
			if( base.settings.menu.useMega ) {
				base.$el.delegate( '.smwh_menulistitem:not(.smwh_menuoverflow)',
					'click',
					base.toggleMenu
				);
			} else {
				base.$el.delegate( '.smwh_menulistitem:not(.smwh_menuoverflow)',
					'mouseenter',
					base.toggleMenu
				);
				base.$el.delegate( '.smwh_menulistitem:not(.smwh_menuoverflow)',
					'mouseleave',
					base.toggleMenu
				);
				base.$el.delegate( '.smwh_menulistitem:not(.smwh_menuoverflow)',
					'click',
					base.showMenu
				);
			}

			base.$el.delegate( '.smwh_menulistitem',
				'mouseenter',
				function() {$( this ).addClass( 'hovering' );}
			);
			base.$el.delegate( '.smwh_menulistitem',
				'mouseleave',
				function() {$( this ).removeClass( 'hovering' );}
			);

			base.$el.delegate( '.smwh_menuoverflow > .smwh_menuhead',
				'click',
				base.toggleSubmenu,
				true
			);
			base.$el.delegate( '.smwh_menuoverflow > .smwh_menuhead',
				'mouseenter',
				base.toggleSubmenu
			);
			base.$el.delegate( '.smwh_menuoverflow',
				'mouseleave',
				base.hideSubmenu
			);
			// the more tab
			base.$more.hover( base.showMenu, base.hideMenu );

			// TreeView
			base.$el.delegate( base.settings.treeView.elems.toggle,
				'click',
				base.showTree
			);
			base.$tvHead.delegate( base.settings.treeView.elems.close,
				'click',
				function( ev ) {
					ev.preventDefault();
					base.hideTree();
				}
			);

			// register resize control, so everything gets update if
			// size of the browser window changes
			$( win ).resize( base.resizePage.bind( base ) );
			// "Change view"
			$( base.settings.elems.personalBar ).click( base.resizePage );
		}

		/**
		 * @brief function modifyEditLinks
		 *		Move the [edit] link from the opposite edge to the side of the heading title itself
		 *
		 * @source: http://www.mediawiki.org/wiki/Snippets/Editsection_inline
		 * @rev: 4 (modified)
		 */
		base.modifyEditLinks = function() {
			if( $.inArray( mw.config.get( 'editsection-inline' ),
				[ 'no', false ] ) !== -1 )
			{
				return;
			}
			$( '#content' ).find( '.editsection' ).each( function() {
				var	editsec = $( this ),
					$what = editsec.parent().children();
					$what.first().before( $what.last() );
				editsec.html( editsec.children() );
			});
		}
		
		/**
		 *@brief function calculateMenuSize
		 *		This function calculates the available space for the menu
		 */
		base.calculateMenuSize = function() {
			base.menuListSize = base.$menuList && base.$menuList.outerWidth();
			base.subMenuItemWidth = base.$el.find( '.smwh_menuoverflow' ).outerWidth();
			base.homeRightPosition = base.$home && base.$home.position().left +
				base.$home.outerWidth();
			base.searchBoxLeftPosition = base.$searchBox &&
				base.$searchBox.position().left - 20; // style for "new page" missing
			base.maxMenuWidth = base.searchBoxLeftPosition - base.homeRightPosition -
				base.subMenuItemWidth;
		}

		/**
		 * @brief function makeMegaMenu
		 *		Special handling for the mega menu
		 */
		base.makeMegaMenu = function(){
			var $bodies = base.$el.find( base.settings.elems.menuBody ),
				closeIconPath = mw.config.get( 'stylepath' ) + '/' +
					mw.config.get( 'skin' ) + '/img/button_close.png',
				$closeIcon = $( '<div class="menu-close"' +
					'style="background:url(' + closeIconPath + ') no-repeat;' +
					'position: absolute; top: 0; right: 0; height: 16px; width: 16px;' +
					'cursor: pointer" title="close menu"></div>' );

			$bodies.find( '.smwh_menuitem' ).css( 'position', 'relative' )
				.find( '> :first-child' ).append( $closeIcon );
			$bodies.each( function( i, el ) {
				var li = $( el ).closest( 'li' );
				$( el ).find( '.menu-close' ).bind( 'click', function() {
					$.proxy( base.toggleMenu, li )()
				});
			})
		}

		/**
		 *@brief function createSubMenus
		 *		Creates sub menu items
		 */
		base.createSubMenus = function() {
			var currentWidth = 0,
				needSubMenu = false,
				missingItems = [],
				i,
				len,
				$subMenuBody;

			base.$menuList.css( 'width',
				base.maxMenuWidth + base.subMenuItemWidth
			);
			currentWidth = base.subMenuItemWidth;
			base.$el.find( '.smwh_menulistitem:not(.smwh_menuoverflow)' )
				.each( function(i, item) {
				var $item = $( item ),
					itemWidth = $item.outerWidth();

				if( !needSubMenu && currentWidth + itemWidth < base.maxMenuWidth ) {
					currentWidth += itemWidth;
				} else {
					needSubMenu = true;
					$item.hide();
					missingItems.push( item );
				}
			});
			if( needSubMenu ) {
				// create >> item and align missing menu items
				$subMenuBody = base.$el.find( '.smwh_menuoverflow .smwh_menubody > ul' );
				for( i = 0, len = missingItems.length; i < len; i++ ) {
					$subMenuBody.append( missingItems[i] );
				}

				base.$el.find( '.smwh_menuoverflow .smwh_menuhead p' ).css( 'white-space', 'nowrap' );
				base.$el.find( '.smwh_menuoverflow > .smwh_menubody' )
					.show();
				base.$el.find( '.smwh_menuoverflow .smwh_menulistitem' )
					.css({'width' : '100%'});
				base.$el.find( '.smwh_menuoverflow .smwh_menulistitem > .smwh_menuhead' )
					.css({'width': '100%', 'margin' : 0})
					.filter( '.smwh_menudropdown' )
					.removeClass( 'smwh_menudropdown' )
					.addClass( 'smwh_menudropleft' );

				base.$el.find( '.smwh_menuoverflow' ).css( 'display', 'inline-block' );
			}
		}

		/**
		 * @brief function toggleMenu
		 *		Handles menu toggles.
		 */
		base.toggleMenu = function() {
			var $this = $( this ),
				self = this,
				timeout = base.settings.menu.useMega ? 0 : 500;

			setTimeout( function(){
				if( $this.hasClass( 'smwh_active' ) &&
					!$this.hasClass( 'hovering' ) )
				{
					$.proxy( base.hideMenu, self )();
				} else {
					if( $this.hasClass( 'hovering' ) ) {
						$.proxy( base.showMenu, self )();
					}
				}
			}, timeout );
		}

		/**
		 * @brief function showMenu
		 *		Shows a menu item with handling for mega menus
		 */
		base.showMenu = function() {
			var $this = $( this ),
				id = this.id,
				$menuBody = $this.find( base.settings.elems.menuBody ),
				// another menu element open?
				openElements = base.$el.find( '.smwh_active, .open' );

			if( openElements.length > 0 ) {
				$.proxy( base.hideMenu, openElements )();
			}
			if( !$this.parent().closest( '.smwh_menulistitem' )
				.hasClass( 'smwh_menuoverflow' ) || 
				base.settings.menu.useMega )
			{
				base.hideSubmenu();
			}

			if( id !== 'more' && base.settings.menu.useMega ) {
				$menuBody.insertAfter( base.$el ).removeClass(
					'autoW' )
					.addClass( 'smwh_megamenu' );
				if( !expanded ) {
					$menuBody.addClass( 'smwh_center' );
				}
			}
			$this.addClass( 'open smwh_active' );
			$menuBody.addClass( 'open' );
		}

		/**
		 * @brief function hideMenu
		 *		This function hides a menu item
		 */
		base.hideMenu = function() {
			var $this = $( this ),
				id = this.id,
				$head = $this.find( base.settings.elems.menuHead ),
				$body = base.settings.menu.useMega ?
					base.$el.siblings( base.settings.elems.menuBody ) :
					$this.find( base.settings.elems.menuBody )

			$this.removeClass( 'smwh_active open' );
			$body.removeClass( 'open' );
			if( id !== 'more' && base.settings.menu.useMega ) {
				$body.appendTo( $head ).removeClass( 'smwh_center smwh_megamenu' );
			}
		}

		base.toggleSubmenu = function( click ) {
			var $this = $( this ),
				$parent = $this.parent(),
				timeout = click === true? 0 : 500;

			setTimeout( function() {
				if( $parent.find( '> .smwh_menubody:hidden' ).length ) {
					$parent.find( '> .smwh_menubody' ).show( 1 , function() {
						var width = $parent.find( '> .smwh_menubody' )
							.outerWidth() || 0;

						$parent.find( '.smwh_menulistitem .smwh_menubody' )
							.css({'right' : width, 'top' : 0});
					});
					$parent.find( '.smwh_menulistitem' ).show();
					$this.addClass( 'overflow_active' );
				} else {
					if( !$this.hasClass('overflow_active') ) {
						base.hideSubmenu();
						$this.removeClass( 'overflow_active' );
					}
				}
			}, timeout );
		}

		/**
		 * @brief function hideSubmenu
		 *		This function hide the submenu items
		 */
		base.hideSubmenu = function() {
			base.$el.find( '.smwh_menuoverflow > .smwh_menubody' ).hide();
			base.$el.find( '.smwh_menuoverflow > .smwh_menulistitem' ).hide();
		}

		/**
		 * @brief function resizePage
		 *		This function resizes the skin between a fixed width and full width.
		 */
		base.resizePage = function( ev ) {
			ev.preventDefault();
			if( expanded === false ) {
				//show layout, which uses full browser window size
				$( base.settings.elems.center ).removeClass( 'smwh_center' );
				$( base.settings.elems.personalBar )
					.removeClass( 'limited' )
					.addClass( 'expanded' );
				expanded = true;
			} else {
				//show layout, which is optimized for 1024x768
				$( base.settings.elems.center ).addClass( 'smwh_center' );
				$( base.settings.elems.personalBar )
					.removeClass( 'expanded' )
					.addClass( 'limited' );
				expanded = false;
			}
			//store state in a cookie
			$.cookie( 'smwSkinExpanded', expanded, {
				path: '/'
			});

			base.$menuList.html( base.$menuDump.html() ).css( 'width', 'auto' );
			base.calculateMenuSize();
			if( base.menuListSize > base.maxMenuWidth ) {
				base.createSubMenus();
			} else {
				base.$menuList.show();
			}

			if( base.settings.menu.useMega ) {
				$( doc ).find( '.smwh_megamenu' ).remove();
				base.makeMegaMenu();
			}
			
			// fire resize event
			base.fireResizeListener();
		}
		
		/**
		 * Add a resize event listener. The listener must 
		 * be a function with no parametes.
		 */
		base.addResizeListener = function(listener) {
			base.resizelisteners.push(listener);
		}
		
		/**
		 * Fires resize event
		 */
		base.fireResizeListener = function(listener) {
			$.each(this.resizelisteners, function(i, onresizeListener) { 
				onresizeListener();
			});
			
		}

		
		/**
		 * @brief function initTree
		 *		Initialize the tree
		 */
		base.initTree = function() {
			var mainOffset = $( '#smwh_menu' ).offset().top;

			base.$tv.css( 'top', mainOffset );
		}
		/**
		 * @brief function showTree
		 *		Displays the Tree at the page
		 */
		base.showTree = function() {
			base.$tv.show();
			base.$tv.animate({
				left: 0
			},{
				queue: false
			}, 250 );
		}

		/**
		 * @brief function hideTree
		 *		Hides the Tree
		 */
		base.hideTree = function() {
			var tvW = base.$tv.outerWidth( true );

			base.$tv.animate({
				left: -tvW -17 //box-shadow + close-icon
			},{
				queue: false
			}, 250 );
		}
	};

	$.fn.ontoskin = function( method ) {
		var args = Array.prototype.slice.call( arguments, 1 );

		return this.each( function() {
			var obj = $( this ).data( 'Ontoskin' );

			if( !obj && typeof( method ) === 'string' ) {
				// Method called without init, need to init.
				obj = ( new $.Ontoskin( this ) );
				obj.init();
			}

			if( obj && obj[method] && method !== 'init' ) {
				// Standard method call.
				obj[method].apply( this, args );
			} else if( typeof( method ) === 'object' || !method ) {
				// Method isn't present or is an hash for settings (init calling style), init only if needed.
				if( !obj ) {
					obj = ( new $.Ontoskin( this, method ) );
					obj.init();
				}
			} else {
				$.error( 'Ontoskin method not found: ' + method );
			}
		});
	};
	
	$.fn.getOntoskin = function( ) {
		return $( this ).data( 'Ontoskin' );
	};

	$( '#smwh_menu' ).ontoskin();

})( jQuery, window, window.document, mediaWiki );