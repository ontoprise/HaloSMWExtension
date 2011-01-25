/**
 * Javascript code to be used with input type timepicker.
 *
 * @author Stephan Gambke
 * @version 0.4
 *
 */

/**
 * Initializes a timepicker input
 *
 * @param inputID (String) the id of the input to initialize
 * @param params (Object) the parameter object for the timepicker, contains
 *		minTime: (String) the minimum time to be shown (format hh:mm)
 *		maxTime: (String) the maximum time to be shown (format hh:mm)
 *		interval: (String) the interval between selectable times in minutes
 *		format: (String) a format string (unused) (do we even need it?)
 *
 */
function SFI_TP_init( inputIDshow, params ) { // minTime, maxTime, interval, format

	var inputID = inputIDshow.replace( "_tp_show", "" );

	// sanitize inputs
	var re = /^\d+:\d\d$/;
	var minh = 0;
	var minm = 0;

	var maxh = 23;
	var maxm = 59;

	if ( re.test( params.minTime ) ) {

		var min = params.minTime.split( ':', 2 );
		minh = Number( min[0] );
		minm = Number( min[1] );

		if ( minm > 59 ) minm = 59;
	}

	if ( re.test( params.maxTime ) ) {

		var max = params.maxTime.split( ':', 2 );
		maxh = Number( max[0] );
		maxm = Number( max[1] );

		if ( maxm > 59 ) maxm = 59;
	}

	var interv = Number( params.interval );

	if ( interv < 1 ) interv = 1;
	else if ( interv > 60 ) interv = 60;

	// build html structure
	var sp = jQuery( "<span class='SFI_timepicker' id='" + inputID + "_tree' ></span>" ).insertBefore( "#" + inputIDshow );

	var ulh = jQuery( "<ul>" ).appendTo( sp );


	for ( var h = minh; h <= maxh; ++h ) {

		var lih = jQuery( "<li class='ui-state-default'>" + ( ( h < 10 ) ? "0" : "" ) + h + "</li>" ).appendTo( ulh );

		//TODO: Replace value for "show" by formatted string
		lih
		.data( "value", ( ( h < 10 ) ? "0" : "" ) + h + ":00" )
		.data( "show", ( ( h < 10 ) ?"0" : "" ) + h + ":00" );

		var ulm = jQuery( "<ul>" ).appendTo( lih );

		for ( var m = ( (h == minh) ? minm : 0 ) ; m <= ( (h == maxh) ? maxm : 59 ); m += interv ) {

			var lim = jQuery( "<li class='ui-state-default'>" + ( ( m < 10 ) ? "0" : "" ) + m  + "</li>" ).appendTo( ulm );

			//TODO: Replace value for "show" by formatted string
			lim
			.data( "value", ( ( h < 10 ) ? "0" : "" ) + h + ":" + ( ( m < 10 ) ? "0" : "") + m )
			.data( "show", ( ( h < 10 ) ? "0" : "") + h + ":" + ( ( m < 10 ) ? "0" : "" ) + m );

		}

	}

	// initially hide everything
	jQuery("#" + inputID + "_tree ul")
	.hide();

	// attach event handlers
	jQuery("#" + inputID + "_tree li") // hours
	.mouseover(function(evt){

		// clear any timeout that may still run on the last list item
		clearTimeout(jQuery(evt.currentTarget).data("timeout"));

		jQuery(evt.currentTarget)

		// switch classes to change display style
		.removeClass("ui-state-default")
		.addClass("ui-state-hover")

		// set timeout to show minutes for selected hour
		.data("timeout", setTimeout(
			function(){
				//console.log("mouseover timer " + jQuery(evt.currentTarget).text());
				jQuery(evt.currentTarget).children().fadeIn();
			},400));

	});

	jQuery("#" + inputID + "_tree li") // hours
	.mouseout(function(evt){

		// clear any timeout that may still run on this jQuery list item
		clearTimeout(jQuery(evt.currentTarget).data("timeout"));

		jQuery(evt.currentTarget)

		// switch classes to change display style
		.removeClass("ui-state-hover")
		.addClass("ui-state-default")

		// hide minutes after a short pause
		.data("timeout", setTimeout(
			function(){
				//console.log("mouseout timer " + jQuery(evt.currentTarget).text());
				jQuery(evt.currentTarget).children().fadeOut();
			},400));

	});

	jQuery("#" + inputID + "_tree li") // hours, minutes
	.mousedown(function(evt){
		// set values and leave input
		jQuery("#" + inputIDshow ).attr("value", jQuery(this).data("show")).blur().change();
		//jQuery("#" + inputID ).attr("value", jQuery(this).data("value"));

		// clear any timeout that may still run on this jQuery list item
		clearTimeout(jQuery(evt.currentTarget).data("timeout"));

		jQuery(evt.currentTarget)

		// switch classes to change display style
		.removeClass("ui-state-hover")
		.addClass("ui-state-default")

		// avoid propagation to parent list item (e.g. hours),
		// they would overwrite the input value
		return false;
	});

	// show timepicker when input gets focus
	jQuery("#" + inputIDshow ).focus(function() {
		jQuery("#" + inputID + "_tree>ul").fadeIn();
	});

	// hide timepicker when input loses focus
	jQuery("#" + inputIDshow ).blur(function() {
		jQuery("#" + inputID + "_tree ul").fadeOut("normal", function() {jQuery(this).hide()});
	});

	if ( ! params.partOfDTP ) {
		jQuery("#" + inputIDshow).change(function() {
			jQuery("#" + inputID ).attr("value", jQuery(this).attr("value"));
		});
	}

	jQuery("#" + inputID + '_tp_show ~ button[name="button"]' )
	.attr("id", inputID + "_button")
	.click(function() {
		jQuery("#" + inputIDshow ).focus();
	});

	jQuery("#" + inputID + '_tp_show ~ button[name="resetbutton"]' )
	.attr("id", inputID + "_resetbutton")
	.click(function() {
		jQuery("#" + inputIDshow ).attr("value", "");
	});
}