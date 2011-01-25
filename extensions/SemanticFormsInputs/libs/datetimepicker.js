/**
 * Javascript code to be used with input type datepicker.
 *
 * @author Stephan Gambke
 * @version 0.4
 *
 */


function SFI_DTP_init ( input_id, params ) {

	var dp = jQuery( "#" + input_id + "_dp_show"); // datepicker element
	var tp = jQuery( "#" + input_id + "_tp_show"); // timepicker element

	jQuery( "#" + input_id + "_dp_show, #" + input_id + "_tp_show" ).change (function(){
		jQuery( "#" + input_id ).attr("value",
			jQuery.datepicker.formatDate( dp.datepicker("option", "altFormat"), dp.datepicker("getDate"), null ) +
			" " + tp.attr( "value" )
		);
	})

	if ( params.resetButtonImage  ) {

		if ( params.disabled ) {
			// append inert reset button if image is set
			tp.parent().append('<button type="button" class="ui-datetimepicker-trigger' + params.userClasses + '" disabled><img src="' + params.resetButtonImage + '" alt="..." title="..."></button>');
		} else {
			var resetbutton = jQuery('<button type="button" class="ui-datetimepicker-trigger ' + params.userClasses + '" ><img src="' + params.resetButtonImage + '" alt="..." title="..."></button>');
			tp.parent().append(resetbutton);
			resetbutton.click(function(){
				dp.datepicker( "setDate", null);
				tp.attr( "value", "" );
				jQuery( "#" + input_id ).attr( "value", "" );
			})
		}
	}
}