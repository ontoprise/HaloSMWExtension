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
* Skin class - Javascript functionality of Ontoskin3
*
* @author Robert Ulrich
*/
function Smwh_Skin() {

	//Variables
	this.expanded = false; //stores if skin is expanded or not
	this.treeviewhidden = true; //stores if treeview is hidden or not

	/**
	 * @brief function showMenu
	 *        This functions sets the hovering class so the menu is shown.
	 *        It's bound to hover events
	 *
	 */
	this.showMenu = function() {
		$jq( this ).addClass( "hovering" );
	};

	/**
	 * @brief function hideMenu
	 *        This functions removes the hovering class so the menu is hidden.
	 *        It's bound to mouseout events
	 *
	 */
	this.hideMenu = function() {
		$jq( this ).removeClass( "hovering" );
	};

	/**
	 * @brief function resizePage
	 *        This function resizes the skin between a fixed width and full width.
	 *
	 */
	this.resizePage = function () {
		if( this.expanded === false ) {
			//show layout, which uses full browser window size
			$jq( ".shadows, #smwh_menu > div:first-child" ).removeClass( "smwh_center" );
			$jq( "#personal_expand" )
				.removeClass( "limited" )
				.addClass( "expanded" );
			$jq( "#smwh_treeviewtoggleleft, #smwh_treeviewtoggleright, #smwh_treeview")
				.addClass( "expanded" );
			this.expanded = true;

			//Hide treeview (necessary if shown on the left side)
			this.hideTree();
		} else {
			//show layout, which is optimized for 1024x768
			$jq( ".shadows, #smwh_menu > div:first-child" ).addClass( "smwh_center" );
			$jq( "#personal_expand" )
				.removeClass( "expanded" )
				.addClass( "limited" );
			$jq( "#smwh_treeviewtoggleleft, #smwh_treeviewtoggleright, " + 
				"#smwh_treeviewtogglecenter, #smwh_treeview" ).removeClass( "expanded" );
			this.expanded = false;
		}
		//store state in a cookie
		this.setCookieObj( "smwSkinExpanded", this.expanded );

		//Call resize controll, so button for left treeview is shown or hidden
		this.resizeControl();
		
		// fire resize event
		this.fireResizeListener();
	};
	
	/**
	 * Add a resize event listener. The listener must 
	 * be a function with no parametes.
	 */
	this.addResizeListener = function(listener) {
		this.resizelisteners.push(listener);
	}
	
	/**
	 * Fires resize event
	 */
	this.fireResizeListener = function(listener) {
		$jq.each(this.resizelisteners, function(i, onresizeListener) { 
			onresizeListener();
		});
		
	}

	/**
	 * @brief function hideTree
	 *        This functions hides the treeview if its open and shown.
	 *
	 */
	this.hideTree = function(){
		this.treeviewhidden = true;
		//remove classes with the style for treeviews shown either right or left
		$jq( "#smwh_treeview" ).removeClass( "smwh_treeviewright smwh_treeviewleft" );
		//change state of the treeview icons
		$jq( "#smwh_treeviewtoggleleft" ).removeClass( "active" );
		$jq( "#smwh_treeviewtoggleright" ).removeClass( "active" );
		//remove styles like width and right set directly in the elements style
		$jq( "#smwh_treeview" ).removeAttr( "style" );
		//store state in a cookie
		this.setCookieObj( "smwSkinTree", "none" );
	};

	/**
	 * @brief function showTreeViewLeftSide
	 *        This functions opens the treeview and shows it on the left side of the treeview icons.
	 *
	 */
	this.showTreeViewLeftSide = function() {
		if( !this.treeviewhidden ) {
			//If the treeview is shown just hide it
			this.hideTree();
		} else {
			//Hide tree, this resets the tree styles and classes
			this.hideTree();
			$jq( "#smwh_treeview" ).addClass( "smwh_treeviewleft" );
			$jq( "#smwh_treeviewtoggleleft" ).addClass( "active" );
			//calculate and set distance to the right
			this.setRightDistance();
			//Set tree as shown
			this.treeviewhidden = false;

			//store state in a cookie
			this.setCookieObj( "smwSkinTree", "left" );
		}
	};

	/**
	 * @brief function showTreeViewRightSide
	 *        This functions opens the treeview and shows it on the right side of the treeview icons.
	 *
	 */
	this.showTreeViewRightSide = function() {
		if( this.treeviewhidden === false ) {
			//If the treeview is shown just hide it
			this.hideTree();
		} else {
			//Hide tree, this resets the tree styles and classes
			this.hideTree()
			//Show tree
			//if page uses full screen width don't show tree on the right
			if( this.expanded === true ) {
				return;
			}
			//if the calculated width is too small don't show tree
			if( this.getRightWidth() < 200 ) {
				return;
			}
			//add Class with styles for right side view
			$jq( "#smwh_treeview" ).addClass( "smwh_treeviewright" );
			//Set right icon to active
			$jq( "#smwh_treeviewtoggleright" ).addClass( "active" );

			//Set witdh of the tree view
			$jq( ".smwh_treeviewright" ).css( "width", this.getRightWidth() + "px" );
			//Set tree as shown
			this.treeviewhidden = false;

			//store state in a cookie
			this.setCookieObj( "smwSkinTree", "right" );
		}
	};

	/**
	 * @brief function setRightDistance
	 *        Calculate distance to the right browser border and apply to treeview if shown on the leftside
	 *
	 */
	this.setRightDistance = function() {
		//Get x-coordinates from the treeview icons
		var toggleoffset = $jq( ".shadows" ).offset().left;
		//Get window width
		var windowwidth  = $jq( window ).width();
		//Subtract both to calculate the space on the right side
		var rightspace = windowwidth - toggleoffset;

		if( this.expanded ) {
			//remove space to the right
			$jq( '.smwh_treeviewleft' ).css( 'right', null );
		} else {
			//set space to the right
			$jq( '.smwh_treeviewleft' ).css( 'right', rightspace + 'px' );
		}
	}

	/**
	 * @brief function setRightWidth
	 *        Calculate gap between page and right browser border and apply to treeview if shown on the rightside
	 *
	 */

	//TODO: Split up in two functions to make code more readable
	this.setRightWidth = function( contentoffset ) {
	//Set width for treeview if shown right
	}

	/**
	 * @brief function getRightWidth
	 *        Calculate gap between page and right browser border and apply to treeview if shown on the rightside
	 *
	 */
	this.getRightWidth = function() {
		//Get left offset (same as right) and subtract the space needed for treeview icons
		var contentoffset = $jq( ".shadows" ).offset().left - 40;
		return contentoffset;
	}

	/**
	 * @brief function resizeControl
	 *        Checks and set values if screen is resized and on startup
	 *
	 */
	this.resizeControl = function() {
		//set minimum height, so page always reachs to the bottom of the browser screen
		var windowheight = $jq( window ).height();
		$jq( "#smwh_HeightShell" ).css( "min-height", windowheight + "px" );

		//Adjust css for left and right viewed treeview
		this.setRightDistance();
		//hide tree if shown on the right side and not enough space is given.
		if( this.getRightWidth() < 200 && $jq( ".smwh_treeviewright" ).length > 0 ) {
			this.hideTree();
		}
        
		//Check if there is enough space on the right side to show the treeview otherwise remove button
		if( this.expanded == true || this.getRightWidth() < 200 ) {
			$jq( "#smwh_treeviewtoggleright" ).css( "display", "none" );
		} else {
			$jq( "#smwh_treeviewtoggleright" ).css( "display", "block" );
		}
	}

	/**
	 * @brief function constructor
	 *       initializes the skin object
	 *
	 */
	this.constructor = function() {
		
		// register here if you want to get informed about "Change view" events
		this.resizelisteners = [];
		
		//Check if BrowserToolsObject is available provided by halo
		if( typeof GeneralBrowserTools != undefined ) {
			//get from cookie stored value if the skin was expanded or not last time
			var state = GeneralBrowserTools.getCookieObject( "smwSkinExpanded" );
			if( state == true && this.expanded == false ) {
				this.resizePage();
			}
			//get from cookie stored value if the tree was shown or not last time
			state = GeneralBrowserTools.getCookieObject( "smwSkinTree" );
			if( state == "left" && this.treeviewhidden == true ) {
				this.showTreeViewLeftSide();
			} else if ( state == "right" && this.treeviewhidden == true ) {
				this.showTreeViewRightSide();
			}
		}

		//register Eventhandler for the menubar itself
		$jq( "#smwh_menu" ).find( ".smwh_menulistitem" ).hover( this.showMenu, this.hideMenu );
		//register Eventhandler for the more tab
		$jq( "#more" ).hover( this.showMenu, this.hideMenu );
		//register Eventhandler for the tree view icons
		$jq( "#smwh_treeviewtoggleright" ).click( this.showTreeViewRightSide.bind( this ) );
		$jq( "#smwh_treeviewtoggleleft" ).click( this.showTreeViewLeftSide.bind( this ) );
		//register resize control, so everything gets update if size of the browser window changes
		//e.g. the treeview gots hidden if shown on the right and width to small after resize
		$jq( window ).resize( this.resizeControl.bind( this ) );
		//Call it on startup so everything is set right
		this.resizeControl();

		/**
		 * Move the [edit] link from the opposite edge to the side of the heading title itself
		 *
		 * @source: http://www.mediawiki.org/wiki/Snippets/Editsection_inline
		 * @rev: 4 (modified)
		 */
		if ( $jq.inArray( mw.config.get( 'editsection-inline' ), [ 'no', false ] ) !== -1 ) {
			return;
		}
		$jq( '#content' ).find( '.editsection' ).each( function() {
			var editsec = $jq( this ),
			$what = editsec.parent().children();
			$what.first().before( $what.last() );
			editsec.html( editsec.children() );
		});
		
		if ( !( 'placeholder' in document.createElement( 'input' ) ) ) {
			$jq('#searchInput').placeholder();
		}
	},

	/**
	 * @brief function setCookieObj
	 *       sets the cookie obj
	 *
	 */
	this.setCookieObj = function( key, val ) {
		if( GeneralBrowserTools !== undefined 
			&& key !== undefined && val !== undefined )
		{
			GeneralBrowserTools.setCookieObject( key, val );
		}
	}

	//Execute constructor on object creation
	this.constructor();    
}

//Set global variable for accessing skin functions
var smwh_Skin;

//Initialize Skin functions if page is loaded
$jq = jQuery;
$jq( document ).ready(
	function() {
		smwh_Skin = new Smwh_Skin();
	}
);