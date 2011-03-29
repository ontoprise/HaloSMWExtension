/**
 * Javascript code to be used with input type menuselect.
 *
 * @author Stephan Gambke
 *
 */

/**
 * Initializes a menuselect input
 *
 * @param inputID ( String ) the id of the input to initialize
 */
function SFI_MS_init( inputID, params ) {

	jQuery('#' + inputID + "_show").one('focus', function(){
		var treeid = "#" + inputID.replace(/input/,"span") + "_tree"

		jQuery(treeid).css("visibility","hidden");

		// wrap content in span to separate content from sub-menus,
		// wrap content in div to support animating the list item width later
		jQuery( treeid + " li" ).each(
			function() {

				jQuery( this ).contents().not( "ul" )
				.wrapAll( '<span />' )
				.wrapAll( '<div class="cont"/>' );

				jQuery( this ).contents().not( "ul" )
				.find("div.cont")
				.css({
					position:'fixed',
					top: '0px',
					left: '0px'
				});

				// insert the arrows indicating submenus
				if ( jQuery( this ).children( "ul" ).length > 0 ) {
					jQuery( this ).children( "span" ).children( "div" )
					.before( '<div class="arrow" ><img src="' + sfigScriptPath + '/images/MenuSelectArrow.gif" /></div>' )
				}

		} );

		// ensure labels of list item have constant width regardless of width of list item
		// prevents layout changes when list item width is changed
		// set position static ( was set to fixed to calculate text width )
		jQuery( treeid + " li>span>div.cont" ).each( function() {
			jQuery( this ).width( jQuery( this ).outerWidth(true) +  jQuery( this ).siblings("div.arrow").outerWidth(true) + 5);
			jQuery( this ).css( "position", "static" );
		} );

		// add class for default state and fix dimensions
		jQuery( treeid + " li" )
		.addClass( "ui-state-default" )
		.each(
			function() {
				jQuery(this).height(jQuery(this).height());
				jQuery(this).width(jQuery(this).width());

				// to be used for restoring width after mouse leves this item
				jQuery(this).data("width", jQuery(this).width());
			}
		);

		// initially hide everything
		jQuery( treeid + " ul" )
		.css({"z-index":1})
		.hide()
		.fadeTo(0, 0 );

		// some crap "browsers" need special treatment
		if ( jQuery.browser.msie ) {
			jQuery( treeid + " ul" ).css({ "position":"relative" });
		}

		// sanitize links
		jQuery( treeid ).find( "a" )
		.each(
			function() {

				// find title of target page
				if ( jQuery( this ).hasClass( 'new' ) ) { // for red links get it from the href

					regexp = /.*title=([^&]*).*/;
					res = regexp.exec( jQuery( this ).attr( 'href' ) );

					title = unescape( res[1] );

					jQuery( this ).data( 'title', title ); // save title in data

				} else { // for normal links title is in the links title attribute
					jQuery( this )
					.data( 'title', jQuery( this ).attr( 'title' ) ); // save title in data
				}

				jQuery( this )
				.removeAttr( 'title' )  // remove title to prevent tooltips on links
				.bind( "click", function( event ) {
					event.preventDefault();
				} ) // prevent following links

			}
		);

		// attach event handlers

		// mouse entered list item
		jQuery( treeid + " li" )
		.mouseenter( function( evt ) {

			// switch classes to change display style
			jQuery( evt.currentTarget )
			.removeClass( "ui-state-default" )
			.addClass( "ui-state-hover" );

			// if we reentered (i.e. moved mouse from item to sub-item)
			if (jQuery( evt.currentTarget ).data( "timeout" ) != null) {

				// clear any timeout that may still run on the list item
				// (i.e. do not fade out submenu)
				clearTimeout( jQuery( evt.currentTarget ).data( "timeout" ) );
				jQuery( evt.currentTarget ).data( "timeout", null );

				// abort further actions (just leave the submenu open)
				return;
			}


			// if list item has sub-items...
			if ( jQuery( evt.currentTarget ).children( "ul" ).length > 0 ) {

				// set timeout to show sub-items
				jQuery( evt.currentTarget )
				.data( "timeout", setTimeout(
					function() {

						// clear timeout data
						jQuery( evt.currentTarget ).data( "timeout", null );

						// some crap "browsers" need special treatment
						if ( jQuery.browser.msie ) {
							jQuery( evt.currentTarget ).children( "ul" )
							.css( {
								"top": -jQuery( evt.currentTarget ).outerHeight(),
								"left": jQuery( evt.currentTarget ).outerWidth() + 10
							} );
						}

						// fade in sub-menu
						// can not use fadeIn, it sets display:block
						jQuery( evt.currentTarget ).children( "ul" )
						.css( {
							"display":"inline",
							"z-index":100
						} )
						.fadeTo( 400, 1 );

						w = jQuery( evt.currentTarget ).width();

						// animate list item width
						jQuery( evt.currentTarget )
						.animate( { "width": w + 10 }, 100 );

					}, 400 )
				);
			}

		} );

		// mouse left list item
		jQuery( treeid + " li" )
		.mouseleave( function( evt ) {

			// switch classes to change display style
			jQuery( evt.currentTarget )
			.removeClass( "ui-state-hover" )
			.addClass( "ui-state-default" )

			// if we just moved in and out of the item (without really hovering)
			if (jQuery( evt.currentTarget ).data( "timeout" ) != null) {

				// clear any timeout that may still run on the list item
				// (i.e. do not fade in submenu)
				clearTimeout( jQuery( evt.currentTarget ).data( "timeout" ) );
				jQuery( evt.currentTarget ).data( "timeout", null );

				// abort further actions (no need to close)
				return;
			}

			// if list item has sub-items...
			if ( jQuery( evt.currentTarget ).children( "ul" ).length > 0 ) {

				// hide sub-items after a short pause
				jQuery( evt.currentTarget ).data( "timeout", setTimeout(
					function() {

						// clear timeout data
						jQuery( evt.currentTarget ).data( "timeout", null );

						// fade out sub-menu
						// when finished set display:none and put list item back in
						// line ( i.e. animate to original width )
						jQuery( evt.currentTarget ).children( "ul" )
						.css( "z-index", 1 )
						.fadeTo( 400, 0,
							function() {

								jQuery( this ).css( "display", "none" );

								// animate list item width
								jQuery( this ).parent()
								.animate( { "width": jQuery( this ).parent().data( "width" ) }, 100 );
							}
						);

					}, 400 )
				);
			}

		} );

		// clicked list item
		jQuery( treeid + " li" )
		.mousedown( function() {

			// set visible value and leave input
			jQuery( "#" + inputID + "_show" ).attr( "value", jQuery( this )
			.children( "span" ).find( "div.cont" ).text() ).blur();

			// set hidden value that gets sent back to the server
			link = jQuery( this ).children( "span" ).find( "div.cont>a" );

			// if content is link
			if ( link.length == 1 ) {

				// use title set by MW
				jQuery( "#" + inputID ).attr( "value", link.data( "title" ) );

			} else {

				// just use text of list item
				jQuery( "#" + inputID ).attr( "value", jQuery( this ).children( "span" ).find( "div.cont" ).text() );

			}
			return false;

		} );

		// show top menu when input gets focus
		jQuery( "#" + inputID + "_show" )
		.focus( function() {
			jQuery( treeid + ">ul" ).css( "display", "inline" ).fadeTo( 400, 1 );
		} );

		// hide all menus when input loses focus
		jQuery( "#" + inputID + "_show" )
		.blur( function() {

			jQuery( treeid + " ul" ).fadeTo( 400, 0,
				function() {
					jQuery( this ).css( "display", "none" );
				} );
		} );

		jQuery( treeid ).css("visibility","visible");
		jQuery( treeid + ">ul" ).css( "display", "inline" ).fadeTo( 400, 1 );

	});
}