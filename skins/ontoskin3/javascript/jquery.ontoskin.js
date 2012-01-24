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
 * @todo: create submodules for menu & tree?!?
 * @todo: #more handling is defect
 * @todo: effects (.animate() with deffereds)
 * @todo: "v" in mega menu items
 */

;(function( $, win, doc, mw ) {
	$.Ontoskin = function( el ) {
		var	base = this,
			expanded = false,
			treeViewHidden = true,
			externalOptions,
			supportedEvents = ['mouseenter', 'mouseleave', 'click'];

		base.$el = $( el );
		base.el  = el;
		base.$el.data( 'Ontoskin', base );

		// Default settings, is extended with the hash passed when initializing.
		base.settings = {
			menu : {
				id : '#smwh_menu',
				showEvent : 'mouseenter',
				showEffect : '',
				hideEvent : 'mouseleave',
				hideEffect : '',
				useMega : false // true -> makes showEvent and hideEvent obsolet
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
					left : '#smwh_treeviewtoggleleft',
					center : '#smwh_treeviewtogglecenter',
					right : '#smwh_treeviewtoggleright',
					general : '#smwh_treeview'
				}
			}
		};

		externalOptions = mw.config.get( 'wgOntoSkin' );
		if( externalOptions && typeof externalOptions === 'object' ) {
			// the default options are extended with the options defined in php
			$.extend( true, base.settings, externalOptions );
		}

		// cache elements
		base.$menuItems = base.$el.find( '.smwh_menulistitem' );
		base.$more = $( base.settings.elems.more );
		base.$tv = $( base.settings.treeView.elems.general );
		base.$tvtLeft = $( base.settings.treeView.elems.left );
		base.$tvtRight = $( base.settings.treeView.elems.right );
		base.$tvtCenter = $( base.settings.treeView.elems.center );
		base.tvSize = base.$tv.outerWidth( true );

		base.$pb = $( base.settings.elems.personalBar );

		// Function declarations from here on.
		/**
		 * @brief function init
		 *		initialize function called once on setup
		 */
		base.init = function() {
			var state = $.cookie( 'smwSkinExpanded' );
			base.registerEventHandler();

			if( state === true && expanded === false ) {
				base.resizePage();
			}

			state = $.cookie( 'smwSkinTree' );
			if( treeViewHidden === true ) {
				if( state === 'left' ) {
					base.showTree( 'left' );
				} else if ( state === 'right' ) {
					base.showTree( 'right' );
				}
			}

			if( !( 'placeholder' in doc.createElement( 'input' ) ) ) {
				$( '#searchInput' ).placeholder();
			}

			base.resizeControl();
			base.modifyEditLinks();
			if( base.settings.menu.useMega ) {
				base.makeMegaMenu();
			}
		}

		/**
		 * @brief function registerEventHandler
		 *		Takes care of all event bindings
		 */
		base.registerEventHandler = function() {
			// the menubar
			// edge case: click as showEvent *and* hideEvent or useMega === true
			if( base.settings.menu.useMega
				|| ( base.settings.menu.showEvent === 'click'
				&& base.settings.menu.showEvent === base.settings.menu.showEvent ) )
			{
				base.$menuItems.toggle( base.toggleMenu, base.toggleMenu );
			} else {
				base.$menuItems.bind( base.settings.menu.showEvent, base.toggleMenu );
				base.$menuItems.bind( base.settings.menu.hideEvent, base.toggleMenu );
			}

			base.$menuItems.bind( 'mouseenter', function() {
				$( this ).addClass( 'hovering' );
			});
			base.$menuItems.bind( 'mouseleave', function() {
				$( this ).removeClass( 'hovering' );
			})
			// the more tab
			base.$more.hover( base.showMenu, base.hideMenu );

			// TreeView
			base.$tv.find( '.smwh_treeview_close' ).click( function( ev ) {
				ev.preventDefault();
				base.hideTree();
			});
			base.$tvtLeft.click( base.showTree.bind( base, 'left' ) );
			base.$tvtRight.click( base.showTree.bind( base, 'right' ) );
			// register resize control, so everything gets update if
			// size of the browser window changes e.g. the TreeView gots hidden
			// if shown on the right and width to small after resize
			$( win ).resize( base.resizeControl.bind( base ) );
			// "Change view
			base.$pb.click( base.resizePage );
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
		 * @brief function makeMegaMenu
		 *		Special handling for the mega menu
		 */
		base.makeMegaMenu = function(){
			var $bodies = base.$el.find( base.settings.elems.menuBody ),
				closeIconPath = mw.config.get( 'stylepath' ) + '/' + mw.config.get( 'skin' ) +
					'/img/button_close.png',
				$closeIcon = $( '<div class="menu-close"' +
					'style="background:url(' + closeIconPath + ') no-repeat;' +
					'position: absolute; top: 0; right: 0; height: 16px; width: 16px;' +
					'cursor: pointer" title="close menu"></div>' );

			$bodies.find( '.smwh_menuitem' ).css( 'position', 'relative' ).
				find( '> :first-child' ).append( $closeIcon );
			$bodies.each( function( i, el ) {
				var li = $( el ).closest( 'li' );
				$( el ).find( '.menu-close' ).bind( 'click', function() {
					$.proxy( base.toggleMenu, li )()
				});
			})
		}

		/**
		 * @brief function toggleMenu
		 *		Handles menu toggles.
		 */
		base.toggleMenu = function() {
			var $this = $( this );

			if( $this.hasClass( 'smwh_active' ) ) {
				$.proxy( base.hideMenu, this )();
			} else {
				// another menu element open?
				var openElements = $this.parent().find( '.smwh_active' );
				if( openElements.length > 0 ) {
					$.proxy( base.hideMenu, openElements )();
				}
				$.proxy( base.showMenu, this )();
			}
		}

		/**
		 * @brief function showMenu
		 *		Shows a menu item with handling for mega menus
		 */
		base.showMenu = function() {
			var $this = $( this ),
				id = this.id,
				$body = $this.find( base.settings.elems.menuBody );

			if( id !== 'more' && base.settings.menu.useMega ) {
				$body.insertAfter( base.$el ).removeClass(
					'autoW' )
					.addClass( 'smwh_megamenu' );
				if( !expanded ) {
					$body.addClass( 'smwh_center' );
				}
			}
			$this.addClass( 'open smwh_active' );
			$body.addClass( 'open' );
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

				//Hide TreeView (necessary if shown on the left side)
				base.hideTree();
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
			} );

			//Call resize control, so button for left TreeView is shown or hidden
			base.resizeControl();
		}

		/**
		 * @brief function showTree
		 *		Displays the Tree at the page dependending on the position ('left' or 'right')
		 */
		base.showTree = function( position ) {
			if( !treeViewHidden ) {
				base.hideTree();
			} else {
				//Hide tree, this resets the tree styles and classes
				base.hideTree();
				if( position === 'left' ) {
					base.$tv.addClass( 'smwh_treeviewleft' );
					base.$tvtLeft.addClass( 'active' );
					$.cookie( 'smwSkinTree', 'left', {
						path: '/'
					});
				} else { //right side
					//if page uses full screen width don't show tree on the right
					if( expanded ) {
						return;
					}
					//if the calculated width is too small don't show tree
					if( base.getRightWidth() < base.tvSize ) {
						return;
					}
					base.$tv.addClass( 'smwh_treeviewright' );
					base.$tvtRight.addClass( 'active' );
					$.cookie( 'smwSkinTree', 'right', {
						path: '/'
					} );
				}

				//calculate and set distance to the right
				base.setRightDistance();
				treeViewHidden = false;
			}
		}

		/**
		 * @brief function hideTree
		 *		Hides the Tree
		 */
		base.hideTree = function() {
			//remove classes with the style for TreeViews shown either right or left
			//and remove styles like width and right set directly in the elements style
			base.$tv.removeClass( 'smwh_treeviewright smwh_treeviewleft' ).removeAttr( 'style' );
			//change state of the TreeView icons
			base.$tvtLeft.removeClass( 'active' );
			base.$tvtRight.removeClass( 'active' );
			$.cookie( 'smwSkinTree', 'none', {
				path: '/'
			} );
			treeViewHidden = true;
		}

		/**
		 * @brief function setRightDistance
		 *		Calculate distance to the right browser border and apply
		 *		to TreeView if shown on the leftside
		 */
		base.setRightDistance = function() {
			//Get x-coordinates from the TreeView icons
			var	toggleOffset = expanded ? 0 : $( '.shadows' ).offset().left,
				toggleWidth = base.$tvtLeft.outerWidth();

			base.$tvtLeft.css( 'right', toggleOffset - toggleWidth );
			base.$tvtRight.css( 'right', toggleOffset - toggleWidth );
			$( '.smwh_treeviewleft' ).css( 'right', toggleOffset + 'px' );
		}

		/**
		 * @brief function getRightWidth
		 *		Calculate gap between page and right browser border and apply 
		 *		to TreeView if shown on the rightside
		 */
		base.getRightWidth = function() {
			//Get left offset (same as right) and subtract the space needed for TreeView icons
			return $( '.shadows' ).offset().left - 40;
		}

		/**
		 * @brief function resizeControl
		 *		Checks and set values if screen is resized and on startup
		 */
		base.resizeControl = function() {
			var rightWidth = base.getRightWidth();

			//Adjust css for left and right viewed TreeView
			base.setRightDistance();
			//hide tree if shown on the right side and not enough space is given.
			if( rightWidth < base.tvSize
				&& $( '.smwh_treeviewright' ).length > 0 )
			{
				base.hideTree();
			}

			base.$tvtLeft.css( 'display', 'block' )
			//Check if there is enough space on the right side
			//to show the TreeView otherwise remove button
			if( expanded === true || rightWidth < base.tvSize ) {
				base.$tvtRight.css( 'display', 'none' );
			} else {
				base.$tvtRight.css( 'display', 'block' );
			}
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

	$( '#smwh_menu' ).ontoskin();

})( jQuery, window, window.document, mediaWiki );