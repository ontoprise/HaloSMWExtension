<?php
/**
 * Input definitions for the Semantic Forms Inputs extension.
 *
 * @author Stephan Gambke
 * @author Sanyam Goyal
 * @author Yaron Koren
 * @version 0.4
 *
 */

if ( !defined( 'SFI_VERSION' ) ) {
	die( 'This file is part of a MediaWiki extension, it is not a valid entry point.' );
}

class SFIInputs {

	/**
	 * Creates the html text for an input.
	 *
	 * Common attributes for input types are set according to the parameters.
	 * The parameters are the standard parameters set by Semantic Forms'
	 * InputTypeHook plus some optional.
	 *
	 * @param string $cur_value
	 * @param string $input_name
	 * @param boolean $is_mandatory
	 * @param boolean $is_disabled
	 * @param array $other_args
	 * @param string $input_id (optional)
	 * @param int $tabindex (optional)
	 * @param string $class
	 * @return string the html text of an input element
	 */
	static private function textHTML ( $cur_value, $input_name, $is_mandatory, $is_disabled, $other_args, $input_id = null, $tabindex = null, $class = "" ) {

		global $sfgFieldNum, $sfgTabIndex;

		// array of attributes to pass to the input field
		$attribs = array(
				"name" => $input_name,
				"class" => $class,
				"value" => $cur_value,
				"type" => "text"
		);

		// set size attrib
		if ( array_key_exists( 'size', $other_args ) ) {
			$attribs['size'] = $other_args['size'];
		}

		// set maxlength attrib
		if ( array_key_exists( 'maxlength', $other_args ) ) {
			$attribs['maxlength'] = $other_args['maxlength'];
		}

		// modify class attribute for mandatory form fields
		if ( $is_mandatory ) {
			$attribs["class"] .= ' mandatoryField';
		}

		// add user class(es) to class attribute of input field
		if ( array_key_exists( 'class', $other_args ) ) {
			$attribs["class"] .= ' ' . $other_args['class'];
		}

		// set readonly attrib
		if ( $is_disabled ) {
			$attribs["readonly"] = "1";
		}

		// if no special input id is specified set the Semantic Forms standard
		if ( $input_id == null ) {
			$attribs[ 'id' ] = "input_" . $sfgFieldNum;
		} else {
			$attribs[ 'id' ] = $input_id;
		}


		if ( $tabindex == null ) $attribs[ 'tabindex' ] = $sfgTabIndex;
		else $attribs[ 'tabindex' ] = $tabindex;

		// $html = Html::element( "input", $attribs );
		$html = Xml::element( "input", $attribs );

		return $html;

	}

	/**
	 * Setup function for input type regexp.
	 *
	 * Loads the Javascript code used by all regexp filters.
	*/
	static private function regexpSetup() {

		global $wgOut;
		global $sfigSettings;

		static $hasRun = false;

		if ( !$hasRun ) {
			$hasRun = true;

			$wgOut->addScript( '<script type="text/javascript" src="' . $sfigSettings->scriptPath . '/libs/regexp.js"></script> ' );

		}
	}


	/**
	 * Definition of input type "regexp"
	 *
	 * Returns the html code to be included in the page and registers the
	 * input's JS initialisation method
	 *
	 * @param string $cur_value current value of this field (which is sometimes null)
	 * @param string $input_name HTML name that this input should have
	 * @param boolean $is_mandatory indicates whether this field is mandatory for the user
	 * @param boolean $is_disabled indicates whether this field is disabled (meaning, the user can't edit)
	 * @param array $other_args hash representing all the other properties defined for this input in the form definition
	 * @return string html code of input
	*/
	static function regexpHTML ( $cur_value, $input_name, $is_mandatory, $is_disabled, $other_args ) {

		global $wgOut;
		global $sfgFieldNum; // used for setting various HTML IDs
		global $sfgJSValidationCalls; // array of Javascript calls to determine if page can be saved
		global $sfgFormPrinter;

		self::regexpSetup();

		// set base input type
		if ( array_key_exists( 'base type', $other_args ) ) {

			$baseType = trim( $other_args['base type'] );
			unset( $other_args['base type'] );

			// if unknown set default base input type
			if ( ! array_key_exists( $baseType, $sfgFormPrinter->mInputTypeHooks ) )
			$baseType = 'text';
		}
		else $baseType = 'text';

		// set base prefix string
		if ( array_key_exists( 'base prefix', $other_args ) ) {
			$basePrefix = trim( $other_args['base prefix'] ) . ".";
			unset( $other_args['base prefix'] );
		}
		else $basePrefix = '';

		// set OR character
		if ( array_key_exists( 'or char', $other_args ) ) {
			$orChar = trim( $other_args['or char'] );
			unset( $other_args['or char'] );
		}
		else $orChar = '!';

		// set inverse string
		if ( array_key_exists( 'inverse', $other_args ) ) {
			$inverseString = 'true';
			unset( $other_args['inverse'] );
		}
		else $inverseString = 'false';

		// set regexp string
		if ( array_key_exists( 'regexp', $other_args ) ) {

			$regexp = str_replace( $orChar, '|', trim( $other_args['regexp'] ) );
			unset( $other_args['regexp'] );

			// check for leading/trailing delimiter and remove it (else reset regexp)
			if ( preg_match  ( "/^\/.*\/\$/", $regexp ) ) {

				$regexp = substr( $regexp, 1, strlen( $regexp ) - 2 );

			}
			else $regexp = '.*';

		}
		else $regexp = '.*';

		// set failure message string
		if ( array_key_exists( 'message', $other_args ) ) {
			$message = trim( $other_args['message'] );
			unset( $other_args['message'] );
		}
		else $message = wfMsg( 'semanticformsinputs-wrongformat' );

		// sanitize error message and regexp for JS
		$message = Xml::encodeJsVar( $message );
		$regexp = Xml::encodeJsVar( $regexp );

		// register event to validate regexp on submit/preview
		$jstext = <<<JAVASCRIPT
jQuery(function(){
	jQuery('#input_$sfgFieldNum').SemanticForms_registerInputValidation( SFI_RE_validate, {retext: {$regexp}, inverse: {$inverseString}, message: {$message} });
});
JAVASCRIPT;

		$wgOut->addInlineScript( $jstext );

		// create other_args for base input type
		$new_other_args = array();

		foreach ( $other_args as $key => $value )
			if ( $basePrefix && strpos( $key, $basePrefix ) === 0 ) {
				$new_other_args[substr( $key, strlen( $basePrefix ) )] = $value;
			} else
				$new_other_args[$key] = $value;

		// setup parameters for base input type
		$funcArgs = array();
		$funcArgs[] = $cur_value;
		$funcArgs[] = $input_name;
		$funcArgs[] = $is_mandatory;
		$funcArgs[] = $is_disabled;
		$funcArgs[] = $new_other_args;

		// get the input type hooks for the base input type
		$hook_values = $sfgFormPrinter->mInputTypeHooks[$baseType];

		// generate html and JS code for base input type and return it
		return call_user_func_array( $hook_values[0], $funcArgs );

}


	/**
	 * Setup for input type jqdatepicker.
	 *
	 * Adds the Javascript code used by all datepickers.
	*/
	static private function jqDatePickerSetup () {
		global $wgOut, $wgLang, $sfgScriptPath;
		global $sfigSettings;

		static $hasRun = false;

		if ( !$hasRun ) {
			$hasRun = true;

			$wgOut->addScript( '<script type="text/javascript" src="' . $sfgScriptPath . '/libs/jquery-ui/jquery.ui.datepicker.min.js"></script> ' );
			$wgOut->addExtensionStyle( $sfgScriptPath . '/skins/jquery-ui/base/jquery.ui.datepicker.css' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $sfigSettings->scriptPath . '/libs/datepicker.js"></script> ' );

			// set localized messages (use MW i18n, not jQuery i18n)
			$jstext =
					"jQuery(function(){\n"
					. "	jQuery.datepicker.regional['wiki'] = {\n"
					. "		closeText: '" . wfMsg( 'semanticformsinputs-close' ) . "',\n"
					. "		prevText: '" . wfMsg( 'semanticformsinputs-prev' ) . "',\n"
					. "		nextText: '" . wfMsg( 'semanticformsinputs-next' ) . "',\n"
					. "		currentText: '" . wfMsg( 'semanticformsinputs-today' ) . "',\n"
					. "		monthNames: ['"
						. wfMsg( 'january' ) . "','"
						. wfMsg( 'february' ) . "','"
						. wfMsg( 'march' ) . "','"
						. wfMsg( 'april' ) . "','"
						. wfMsg( 'may_long' ) . "','"
						. wfMsg( 'june' ) . "','"
						. wfMsg( 'july' ) . "','"
						. wfMsg( 'august' ) . "','"
						. wfMsg( 'september' ) . "','"
						. wfMsg( 'october' ) . "','"
						. wfMsg( 'november' ) . "','"
						. wfMsg( 'december' ) . "'],\n"
					. "		monthNamesShort: ['"
						. wfMsg( 'jan' ) . "','"
						. wfMsg( 'feb' ) . "','"
						. wfMsg( 'mar' ) . "','"
						. wfMsg( 'apr' ) . "','"
						. wfMsg( 'may' ) . "','"
						. wfMsg( 'jun' ) . "','"
						. wfMsg( 'jul' ) . "','"
						. wfMsg( 'aug' ) . "','"
						. wfMsg( 'sep' ) . "','"
						. wfMsg( 'oct' ) . "','"
						. wfMsg( 'nov' ) . "','"
						. wfMsg( 'dec' ) . "'],\n"
					. "		dayNames: ['"
						. wfMsg( 'sunday' ) . "','"
						. wfMsg( 'monday' ) . "','"
						. wfMsg( 'tuesday' ) . "','"
						. wfMsg( 'wednesday' ) . "','"
						. wfMsg( 'thursday' ) . "','"
						. wfMsg( 'friday' ) . "','"
						. wfMsg( 'saturday' ) . "'],\n"
					. "		dayNamesShort: ['"
						. wfMsg( 'sun' ) . "','"
						. wfMsg( 'mon' ) . "','"
						. wfMsg( 'tue' ) . "','"
						. wfMsg( 'wed' ) . "','"
						. wfMsg( 'thu' ) . "','"
						. wfMsg( 'fri' ) . "','"
						. wfMsg( 'sat' ) . "'],\n"
					. "		dayNamesMin: ['"
						. $wgLang->firstChar( wfMsg( 'sun' ) ) . "','"
						. $wgLang->firstChar( wfMsg( 'mon' ) ) . "','"
						. $wgLang->firstChar( wfMsg( 'tue' ) ) . "','"
						. $wgLang->firstChar( wfMsg( 'wed' ) ) . "','"
						. $wgLang->firstChar( wfMsg( 'thu' ) ) . "','"
						. $wgLang->firstChar( wfMsg( 'fri' ) ) . "','"
						. $wgLang->firstChar( wfMsg( 'sat' ) ) . "'],\n"
					. "		weekHeader: '',\n"
					. "		dateFormat: '" . wfMsg( 'semanticformsinputs-dateformatshort' ) . "',\n"
					. "		firstDay: '" . wfMsg( 'semanticformsinputs-firstdayofweek' ) . "',\n"
					. "		isRTL: " . ( $wgLang->isRTL() ? "true":"false" ) . ",\n"
					. "		showMonthAfterYear: false,\n"
					. "		yearSuffix: ''};\n"
					. "	jQuery.datepicker.setDefaults(jQuery.datepicker.regional['wiki']);\n"
					. "});\n";


			$wgOut->addInlineScript( $jstext );

		}
	}

	/**
	 * Sort and merge time ranges in an array
	 *
	 * expects an array of arrays
	 * the inner arrays must contain two dates representing the start and end
	 * date of a time range
	 *
	 * returns an array of arrays with the date ranges sorted and overlapping
	 * ranges merged
	 *
	 * @param array $ranges array of arrays of DateTimes
	 * @return array of arrays of DateTimes
	*/
	static private function sortAndMergeRanges ( $ranges ) {

		// sort ranges, earliest date first
		sort( $ranges );

		// stores the start of the current date range
		$currmin = FALSE;

		// stores the date the next ranges start date has to top to not overlap
		$nextmin = FALSE;

		// result array
		$mergedRanges = array();

		foreach ( $ranges as $range ) {

			// ignore empty date ranges
			if ( !$range ) continue;

			if ( !$currmin ) { // found first valid range

				$currmin = $range[0];
				$nextmin = $range[1];
				$nextmin->modify( '+1 day' );

			} elseif ( $range[0] <=  $nextmin ) { // overlap detected

				$currmin = min( $currmin, $range[0] );

				$range[1]->modify( '+1 day' );
				$nextmin = max( $nextmin, $range[1] );

			} else { // no overlap, store current range and continue with next

				$nextmin->modify( '-1 day' );
				$mergedRanges[] = array( $currmin, $nextmin );

				$currmin = $range[0];
				$nextmin = $range[1];
				$nextmin->modify( '+1 day' );

			}

		}

		// store last range
		if ( $currmin ) {
			$nextmin->modify( '-1 day' );
			$mergedRanges[] = array( $currmin, $nextmin );
		}

		return $mergedRanges;

	}

	/**
	 * Creates an array of arrays of dates from an array of strings
	 *
	 * expects an array of strings containing dates or date ranges in the format
	 * "yyyy/mm/dd" or "yyyy/mm/dd-yyyy/mm/dd"
	 *
	 * returns an array of arrays, each of the latter consisting of two dates
	 * representing the start and end date of the range
	 *
	 * The result array will contain null values for unparseable date strings
	 *
	 * @param array $rangesAsStrings array of strings with dates and date ranges
	 * @return array of arrays of DateTimes
	*/
   static private function createRangesArray ( $rangesAsStrings ) {

	   // transform array of strings into array of array of dates
	   // have to use create_function to be PHP pre5.3 compatible
	   return array_map( create_function( '$range', '

					if ( strpos ( $range, "-" ) === FALSE ) { // single date
						$date = date_create( $range );
						return ( $date ) ? array( $date, clone $date ):null;
					} else { // date range
						$dates = array_map( "date_create", explode( "-", $range ) );
						return  ( $dates[0] && $dates[1] ) ? $dates:null;
					}

					' ), $rangesAsStrings );

   }

	/**
	 * Takes an array of date ranges and returns an array containing the gaps
	 *
	 * The very first and the very last date of the original string are lost in
	 * the process, of course, as they do not delimit a gap. This means, after
	 * repeated inversion the result would eventually be empty.
	 *
	 * @param array $ranges of arrays of DateTimes
	 * @return array of arrays of DateTimes
	*/
	static private function invertRangesArray( $ranges ) {

		// the result (initially empty)
		$invRanges = null;

		// the minimum of the current gap (initially none)
		$min = null;

		foreach ( $ranges as $range ) {

			if ( $min ) { // if min date of current gap is known store gap
				$min->modify( "+1day " );
				$range[0]->modify( "-1day " );
				$invRanges[] = array( $min, $range[0] );
			}

			$min = $range[1];  // store min date of next gap

		}

		return $invRanges;
	}


	/**
	 * Definition of input type "datepicker".
	 *
	 * Returns the html code to be included in the page and registers the
	 * input's JS initialisation method
	 *
	 * @param string $cur_value current value of this field (which is sometimes null)
	 * @param string $input_name HTML name that this input should have
	 * @param boolean $is_mandatory indicates whether this field is mandatory for the user
	 * @param boolean $is_disabled indicates whether this field is disabled (meaning, the user can't edit)
	 * @param array $other_args hash representing all the other properties defined for this input in the form definition
	 * @return string html code of input
	*/
	static function jqDatePickerHTML( $cur_value, $input_name, $is_mandatory, $is_disabled, $other_args ) {

		global $wgOut, $wgLang, $wgAmericanDates;  // MW variables
		global $sfgFieldNum, $sfgScriptPath, $sfgTabIndex;  // SF variables
		global $sfigSettings; // SFI variables

		// call common setup for all jqdatepickers
		self::jqDatePickerSetup();

		// The datepicker is created in three steps:
		// first: set up HTML attributes
		// second: set up JS attributes
		// third: assemble HTML and JS code


		// first: set up HTML attributes
		// nothing much to do here, self::textHTML will take care od the standard stuff

		// store user class(es) for use with buttons
		if ( array_key_exists( 'class', $other_args ) ) {
			$userClasses = $other_args['class'];
		} else $userClasses = "";

		// should the input field be disabled?
		$inputFieldDisabled =
			array_key_exists( 'disable input field', $other_args )
			|| ( !array_key_exists( 'enable input field', $other_args ) && $sfigSettings->datePickerDisableInputField )
			|| $is_disabled	;

		// second: set up JS attributes

		// set up attributes required for both enabled and disabled datepickers
		$jsattribs = array(
				'currValue' => $cur_value,
				'disabled' => $is_disabled,
				'userClasses' => $userClasses
		);

		if ( array_key_exists( 'part of dtp', $other_args ) ) {
			$jsattribs['partOfDTP'] = $other_args['part of dtp'];
		}

		// set date format
		// SHORT and LONG are SFI specific acronyms and are translated here
		// into format strings, anything else is passed to the jQuery date picker
		// Americans need special treatment
		if ( $wgAmericanDates && $wgLang->getCode() == "en" ) {

			if ( array_key_exists( 'date format', $other_args ) ) {

				if ( $other_args['date format'] == 'SHORT' ) {
					$jsattribs['dateFormat'] = 'mm/dd/yy';
				} elseif ( $other_args['date format'] == 'LONG' ) {
					$jsattribs['dateFormat'] = 'MM d, yy';
				} else {
					$jsattribs['dateFormat'] = $other_args['date format'];
				}

			} elseif ( $sfigSettings->datePickerDateFormat ) {

				if ( $sfigSettings->datePickerDateFormat == 'SHORT' ) {
					$jsattribs['dateFormat'] = 'mm/dd/yy';
				} elseif ( $sfigSettings->datePickerDateFormat == 'LONG' ) {
					$jsattribs['dateFormat'] = 'MM d, yy';
				} else {
					$jsattribs['dateFormat'] = $sfigSettings->datePickerDateFormat;
				}

			} else $jsattribs['dateFormat'] = 'yy/mm/dd';

		} else {

			if ( array_key_exists( 'date format', $other_args ) ) {

				if ( $other_args['date format'] == 'SHORT' ) {
					$jsattribs['dateFormat'] = wfMsg( 'semanticformsinputs-dateformatshort' );
				} elseif ( $other_args['date format'] == 'LONG' ) {
					$jsattribs['dateFormat'] = wfMsg( 'semanticformsinputs-dateformatlong' );
				} else {
					$jsattribs['dateFormat'] = $other_args['date format'];
				}

			} elseif ( $sfigSettings->datePickerDateFormat ) {

				if ( $sfigSettings->datePickerDateFormat == 'SHORT' ) {
					$jsattribs['dateFormat'] = wfMsg( 'semanticformsinputs-dateformatshort' );
				} elseif ( $sfigSettings->datePickerDateFormat == 'LONG' ) {
					$jsattribs['dateFormat'] = wfMsg( 'semanticformsinputs-dateformatlong' );
				} else {
					$jsattribs['dateFormat'] = $sfigSettings->datePickerDateFormat;
				}

			} else $jsattribs['dateFormat'] = 'yy/mm/dd';

		}

		// setup attributes required only for either disabled or enabled datepickers
		if ( $is_disabled ) {

			$jsattribs['buttonImage'] = $sfigSettings->scriptPath . '/images/DatePickerButtonDisabled.gif';

			if ( array_key_exists( 'show reset button', $other_args ) ||
					( !array_key_exists( 'hide reset button', $other_args ) && $sfigSettings->datePickerShowResetButton ) ) {

				$jsattribs['resetButtonImage'] = $sfigSettings->scriptPath . '/images/DatePickerResetButtonDisabled.gif';

			}

		} else {

			$jsattribs['buttonImage'] = $sfigSettings->scriptPath . '/images/DatePickerButton.gif';

			if ( array_key_exists( 'show reset button', $other_args ) ||
					( !array_key_exists( 'hide reset button', $other_args ) && $sfigSettings->datePickerShowResetButton ) ) {

				$jsattribs['resetButtonImage'] = $sfigSettings->scriptPath . '/images/DatePickerResetButton.gif';

			}

			// find min date, max date and disabled dates

			// set first date
			if ( array_key_exists( 'first date', $other_args ) ) {
				$minDate = date_create( $other_args['first date'] );
			} elseif ( $sfigSettings->datePickerFirstDate ) {
				$minDate = date_create( $sfigSettings->datePickerFirstDate );
			} else {
				$minDate = null;
			}

			// set last date
			if ( array_key_exists( 'last date', $other_args ) ) {
				$maxDate = date_create( $other_args['last date'] );
			} elseif ( $sfigSettings->datePickerLastDate ) {
				$maxDate = date_create( $sfigSettings->datePickerLastDate );
			} else {
				$maxDate = null;
			}

			// find allowed values and invert them to get disabled values
			if ( array_key_exists( 'possible_values', $other_args ) && count( $other_args['possible_values'] ) ) {

				$enabledDates = self::sortAndMergeRanges( self::createRangesArray( $other_args['possible_values'] ) );

				// correct min/max date to the first/last allowed value
				if ( !$minDate || $minDate < $enabledDates[0][0] ) {
					$minDate = $enabledDates[0][0];
				}

				if ( !$maxDate || $maxDate > $enabledDates[count( $enabledDates ) - 1][1] ) {
					$maxDate = $enabledDates[count( $enabledDates ) - 1][1];
				}

				$disabledDates = self::invertRangesArray( $enabledDates );

			} else $disabledDates = array();

			// add user-defined or default disabled values
			if ( array_key_exists( 'disable dates', $other_args ) ) {

				$disabledDates =
						self::sortAndMergeRanges(
						array_merge( $disabledDates, self::createRangesArray( explode( ',' , $other_args['disable dates'] ) ) ) );

			} elseif ( $sfigSettings->datePickerDisabledDates ) {

				$disabledDates =
						self::sortAndMergeRanges(
						array_merge( $disabledDates, self::createRangesArray( explode( ',' , $sfigSettings->datePickerDisabledDates ) ) ) );

			}

			// if a minDate is set, discard all disabled dates below the min date
			if ( $minDate ) {

				// discard all ranges of disabled dates that are entirely below the min date
				while ( $minDate && count( $disabledDates ) && $disabledDates[0][1] < $minDate ) array_shift( $disabledDates );

				// if min date is in first disabled date range, discard that range and adjust min date
				if ( count( $disabledDates ) && $disabledDates[0][0] <= $minDate && $disabledDates[0][1] >= $minDate ) {
					$minDate = $disabledDates[0][1];
					array_shift( $disabledDates );
					$minDate->modify( "+1 day" );
				}
			}

			// if a maxDate is set, discard all disabled dates above the max date
			if ( $maxDate ) {

				// discard all ranges of disabled dates that are entirely above the max date
				while ( count( $disabledDates ) && $disabledDates[count( $disabledDates ) - 1][0] > $maxDate ) array_pop( $disabledDates );

				// if max date is in last disabled date range, discard that range and adjust max date
				if ( count( $disabledDates ) && $disabledDates[count( $disabledDates ) - 1][0] <= $maxDate && $disabledDates[count( $disabledDates ) - 1][1] >= $maxDate ) {
					$maxDate = $disabledDates[count( $disabledDates ) - 1][0];
					array_pop( $disabledDates );
					$maxDate->modify( "-1 day" );
				}
			}
			// finished with disabled dates

			// find highlighted dates
			if ( array_key_exists( "highlight dates", $other_args ) ) {
				$highlightedDates = self::sortAndMergeRanges ( self::createRangesArray( explode( ',' , $other_args["highlight dates"] ) ) ) ;
			} else if ( $sfigSettings->datePickerHighlightedDates ) {
				$highlightedDates = self::sortAndMergeRanges ( self::createRangesArray( explode( ',' , $sfigSettings->datePickerHighlightedDates  ) ) ) ;
			} else {
				$highlightedDates = null;
			}


			// find disabled week days and mark them in an array
			if ( array_key_exists( "disable days of week", $other_args ) ) {
				$disabledDaysString = $other_args['disable days of week'];
			} else {
				$disabledDaysString = $sfigSettings->datePickerDisabledDaysOfWeek;
			}

			if ( $disabledDaysString != null ) {

				$disabledDays = array( false, false, false, false, false, false, false );

				foreach ( explode( ',', $disabledDaysString ) as $day ) {

					if ( is_numeric( $day ) && $day >= 0 && $day <= 6 ) {
						$disabledDays[$day] = true;
					}

				}

			} else {
				$disabledDays = null;
			}

			// find highlighted week days and mark them in an array
			if ( array_key_exists( "highlight days of week", $other_args ) ) {
				$highlightedDaysString = $other_args['highlight days of week'];
			} else {
				$highlightedDaysString = $sfigSettings->datePickerHighlightedDaysOfWeek;
			}

			if ( $highlightedDaysString != null ) {

				$highlightedDays = array( false, false, false, false, false, false, false );

				foreach ( explode( ',', $highlightedDaysString ) as $day ) {

					if ( is_numeric( $day ) && $day >= 0 && $day <= 6 ) {
						$highlightedDays[$day] = true;
					}

				}

			} else {
				$highlightedDays = null;
			}

			// set first day of the week
			if ( array_key_exists( 'week start', $other_args ) ) {
				$jsattribs['firstDay'] = $other_args['week start'];
			} elseif ( $sfigSettings->datePickerWeekStart != null ) {
				$jsattribs['firstDay'] = $sfigSettings->datePickerWeekStart;
			} else {
				$jsattribs['firstDay'] = wfMsg( 'semanticformsinputs-firstdayofweek' );
			}

			// set show week number
			if ( array_key_exists( 'show week numbers', $other_args )
					|| ( !array_key_exists( 'hide week numbers', $other_args ) && $sfigSettings->datePickerShowWeekNumbers ) ) {

				$jsattribs['showWeek'] = true;
			} else {
				$jsattribs['showWeek'] = false;
			}

			// store min date as JS attrib
			if ( $minDate ) {
				$jsattribs['minDate'] = $minDate->format( 'Y/m/d' );
			}

			// store max date as JS attrib
			if ( $maxDate ) {
				$jsattribs['maxDate'] = $maxDate->format( 'Y/m/d' );
			}

			// register disabled dates with datepicker
			if ( count( $disabledDates ) > 0 ) {

				// convert the PHP array of date ranges into an array of numbers
				$jsattribs["disabledDates"] = array_map( create_function ( '$range', '

							$y0 = $range[0]->format( "Y" );
							$m0 = $range[0]->format( "m" ) - 1;
							$d0 = $range[0]->format( "d" );

							$y1 = $range[1]->format( "Y" );
							$m1 = $range[1]->format( "m" ) - 1;
							$d1 = $range[1]->format( "d" );

							return array($y0, $m0, $d0, $y1, $m1, $d1);
						' ) , $disabledDates );
			}

			// register highlighted dates with datepicker
			if ( count( $highlightedDates ) > 0 ) {

				// convert the PHP array of date ranges into an array of numbers
				$jsattribs["highlightedDates"] = array_map( create_function ( '$range', '

							$y0 = $range[0]->format( "Y" );
							$m0 = $range[0]->format( "m" ) - 1;
							$d0 = $range[0]->format( "d" );

							$y1 = $range[1]->format( "Y" );
							$m1 = $range[1]->format( "m" ) - 1;
							$d1 = $range[1]->format( "d" );

							return array($y0, $m0, $d0, $y1, $m1, $d1);
						' ) , $highlightedDates );
			}

			// register disabled days of week with datepicker
			if ( count( $disabledDays ) > 0 ) {
				$jsattribs["disabledDays"] = $disabledDays;
			}

			// register highlighted days of week with datepicker
			if ( count( $highlightedDays ) > 0 ) {
				$jsattribs["highlightedDays"] = $highlightedDays;
			}
		}


		// third: assemble HTML and JS code

		// start with the displayed input and append the real, but hidden
		// input that gets sent to SF; it will be filled by the datepicker
		$html = self::textHTML( $cur_value, "", $is_mandatory, $inputFieldDisabled,
					$other_args, "input_{$sfgFieldNum}_dp_show", null, "createboxInput" );

		if ( ! array_key_exists( 'part of dtp', $other_args ) ) {

			$html .= Xml::element( "input",
					array(
						"id" => "input_{$sfgFieldNum}",
						"name" => $input_name,
						"type" => "hidden",
						"value" => $cur_value
					) );

			// wrap in span (e.g. used for mandatory inputs)
			$html = '<span class="inputSpan' . ($is_mandatory ? ' mandatoryFieldSpan' : '') . '">' .$html . '</span>';
		}

		// build JS code from attributes array
		$jsattribsString = Xml::encodeJsVar( $jsattribs );

		// wrap the JS code fragment in a function for deferred init
		$jstext = <<<JAVASCRIPT
jQuery(function(){ jQuery('#input_{$sfgFieldNum}_dp_show').SemanticForms_registerInputInit(SFI_DP_init, $jsattribsString ); });
JAVASCRIPT;

		// insert the code of the JS init function into the pages code
		$wgOut->addScript( '<script type="text/javascript">' . $jstext . '</script>' );

		//return array( $html, "", "initInput$sfgFieldNum" );
		return $html;

	}

	/**
	 * Setup for input type jqdatepicker.
	 *
	 * Adds the Javascript code used by all datetimepickers.
	*/
	static private function datetimePickerSetup () {

		global $wgOut, $sfigSettings;

		static $hasRun = false;

		if ( !$hasRun ) {
			$hasRun = true;

			$wgOut->addScript( '<script type="text/javascript" src="' . $sfigSettings->scriptPath . '/libs/datetimepicker.js"></script> ' );

		}
	}

	/**
	 * Definition of input type "datetimepicker".
	 *
	 * Returns the html code to be included in the page and registers the
	 * input's JS initialisation method
	 *
	 * @param string $cur_value current value of this field (which is sometimes null)
	 * @param string $input_name HTML name that this input should have
	 * @param boolean $is_mandatory indicates whether this field is mandatory for the user
	 * @param boolean $is_disabled indicates whether this field is disabled (meaning, the user can't edit)
	 * @param array $other_args hash representing all the other properties defined for this input in the form definition
	 * @return string html code of input
	*/
	static function datetimepickerHTML($cur_value, $input_name, $is_mandatory, $is_disabled, $other_args) {

		global $wgOut, $sfgFieldNum, $sfigSettings;

		self::datetimePickerSetup();

		$other_args["part of dtp"] = true;

		$jsattribs = array();

		// if we have to show a reset button
		if ( array_key_exists( 'show reset button', $other_args ) ||
				( !array_key_exists( 'hide reset button', $other_args ) && $sfigSettings->datetimePickerShowResetButton ) ) {

			// some values must be available to the init function

			// is the button disabled?
			$jsattribs['disabled'] = $is_disabled;

			// set the button image
			if ( $is_disabled ) {
				$jsattribs['resetButtonImage'] = $sfigSettings->scriptPath . '/images/DateTimePickerResetButtonDisabled.gif';
			} else {
				$jsattribs['resetButtonImage'] = $sfigSettings->scriptPath . '/images/DateTimePickerResetButton.gif';
			}

			// set user classes
			if ( array_key_exists( 'class', $other_args ) ) $jsattribs[ "userClasses" ] = $other_args['class'];
			else $jsattribs[ "userClasses" ] = "";

		}

		// build JS code from attributes array
		$jsattribsString = Xml::encodeJsVar( $jsattribs );

		$jstext = <<<JAVASCRIPT
jQuery(function(){ jQuery('#input_$sfgFieldNum').SemanticForms_registerInputInit(SFI_DTP_init, $jsattribsString ); });
JAVASCRIPT;

		// insert the code of the JS init function into the pages code
		$wgOut->addScript('<script type="text/javascript">' . $jstext . '</script>');

		// find allowed values and keep only the date portion
		if ( array_key_exists( 'possible_values', $other_args ) &&
				count( $other_args[ 'possible_values' ] ) ) {

			$other_args[ 'possible_values' ] = preg_replace(
							'/^\s*(\d{4}\/\d{2}\/\d{2}).*/',
							'$1',
							$other_args[ 'possible_values' ]
			);
		}

		$separator = strpos($cur_value, " ");

		$html = '<span class="inputSpan' . ($is_mandatory ? ' mandatoryFieldSpan' : '') . '">' .
				self::jqDatePickerHTML(substr($cur_value + " ", 0, $separator), $input_name, $is_mandatory, $is_disabled, $other_args) . " " .
				self::timepickerHTML(substr($cur_value + " ", $separator + 1), $input_name, $is_mandatory, $is_disabled, $other_args) .
				Xml::element("input",
						array(
							"id" => "input_{$sfgFieldNum}",
							"name" => $input_name,
							"type" => "hidden",
							"value" => $cur_value
						))
				. '</span>';

		return $html;
	}

//
//	static function wysiwygSetup() {
//
//	}
//
//
//	static function wysiwygHTML ( $cur_value, $input_name, $is_mandatory, $is_disabled, $other_args ) {
//
//		global $wgOut, $wgLang, $wgAmericanDates, $wgScriptPath, $wgFCKEditorDir;
//		global $sfgFieldNum, $sfgScriptPath, $sfigSettings;
//		global $sfgTabIndex, $sfgJSValidationCalls; // used to represent the current tab index in the form
//
//		$htmltext = Html::element('textarea', array(
//			'id' => "input_$sfgFieldNum",
//			'class' => 'createboxInput',
//			'name' => $input_name,
//			'tabindex' => $sfgTabIndex,
//			'rows' => 25,
//			'cols' => 80
//			));
//
//		$jstext = <<<JAVASCRIPT
// addOnloadHook(function()
// {
// var oFCKeditor = new FCKeditor( 'input_$sfgFieldNum', '100%', '100%') ;
// oFCKeditor.BasePath = "$wgScriptPath/$wgFCKEditorDir/" ;
// oFCKeditor.ReplaceTextarea() ;
// });
//
// JAVASCRIPT;
//
//		$sfgJSValidationCalls[] = "function() {document.getElementById('input_$sfgFieldNum').value = FCKeditorAPI.GetInstance('input_$sfgFieldNum').GetHtml();return true;}";
//
//		$wgOut->addScript( '<script type="text/javascript">' . $jstext . '</script>' );
//		return array( $htmltext, "" );
//	}
//


	/**
	 * Setup for input type "timepicker".
	 *
	 * Adds the Javascript code and css used by all timepickers.
	*/
	static private function timepickerSetup() {

		global $sfigSettings, $wgOut;

		static $hasRun = false;

		if ( !$hasRun ) {

			$wgOut->addScript( '<script type="text/javascript" src="' . $sfigSettings->scriptPath . '/libs/timepicker.js"></script> ' );
			$wgOut->addExtensionStyle( $sfigSettings->scriptPath . '/skins/SFI_Timepicker.css' );

		}

	}

	/**
	 * Definition of input type "timepicker"
	 *
	 * Returns the html code to be included in the page and registers the
	 * input's JS initialisation method
	 *
	 * @param string $cur_value current value of this field (which is sometimes null)
	 * @param string $input_name HTML name that this input should have
	 * @param boolean $is_mandatory indicates whether this field is mandatory for the user
	 * @param boolean $is_disabled indicates whether this field is disabled (meaning, the user can't edit)
	 * @param array $other_args hash representing all the other properties defined for this input in the form definition
	 * @return string html code of input
	*/
	static function timepickerHTML( $cur_value, $input_name, $is_mandatory, $is_disabled, $other_args ) {

		global $wgOut;
		global $sfgFieldNum;
		global $sfigSettings;

		// The timepicker is created in four steps:
		// first: set up HTML attributes
		// second: assemble HTML
		// third: set up JS attributes
		// fourth: assemble JS call


		// first: set up HTML attributes
		$inputFieldDisabled =
			array_key_exists( 'disable input field', $other_args )
			|| ( !array_key_exists( 'enable input field', $other_args ) && $sfigSettings->timePickerDisableInputField )
			|| $is_disabled	;

		if ( array_key_exists( 'class', $other_args ) ) $userClasses = $other_args['class'];
		else $userClasses = "";

		// second: assemble HTML
		// create visible input field (for display) and invisible field (for data)
		$html = self::textHTML( $cur_value, '', $is_mandatory, $inputFieldDisabled, $other_args, "input_{$sfgFieldNum}_tp_show", null, "createboxInput" );

		if ( ! array_key_exists( 'part of dtp', $other_args ) ) {
			$html .= Xml::element( "input", array(
					'id' => "input_{$sfgFieldNum}",
					'type' => 'hidden',
					'name' => $input_name,
					'value' => $cur_value
				) );
		}

		// append time picker button
		if ( $is_disabled ) {

			$html .= Xml::openElement(
					"button",
					array(
					'type' => 'button',
					'class' => 'createboxInput ' . $userClasses,
					'disabled' => '1',
					'id' => "input_{$sfgFieldNum}_button"
					) )

					. Xml::element(
					"image",
					array( 'src' => $sfigSettings->scriptPath . '/images/TimePickerButtonDisabled.gif'	)
					)

					. Xml::closeElement( "button" );

		} else {

			$html .= "<button "
					. Xml::expandAttributes ( array(
					'type' => 'button',
					'class' => 'createboxInput ' . $userClasses,
					'name' => "button",
					) )
					. " onclick=\"document.getElementById(this.id.replace('_button','_tp_show')).focus();\""
					. ">"

					. Xml::element(
					"image",
					array( 'src' => $sfigSettings->scriptPath . '/images/TimePickerButton.gif'	)
					)

					. Xml::closeElement( "button" );

		}

		// append reset button (if selected)
		if ( ! array_key_exists( 'part of dtp', $other_args ) &&
				( array_key_exists( 'show reset button', $other_args ) ||
					$sfigSettings->timePickerShowResetButton && !array_key_exists( 'hide reset button', $other_args )
				)
			)  {

			if ( $is_disabled ) {

				$html .= Xml::openElement(
						"button",
						array(
						'type' => 'button',
						'class' => 'createboxInput ' . $userClasses,
						'disabled' => '1',
						'id' => "input_{$sfgFieldNum}_resetbutton"
						) )

						. Xml::element(
						"image",
						array( 'src' => $sfigSettings->scriptPath . '/images/TimePickerResetButtonDisabled.gif'	)

						)
						. Xml::closeElement( "button" );

			} else {

				$html .= "<button "
						. Xml::expandAttributes ( array(
						'type' => 'button',
						'class' => 'createboxInput ' . $userClasses,
						'name' => "resetbutton",
						) )
						. " onclick=\"document.getElementById(this.id.replace('_resetbutton','')).value='';"
						. "document.getElementById(this.id.replace('_resetbutton','_tp_show')).value='';\""
						. ">"

						. Xml::element(
						"image",
						array( 'src' => $sfigSettings->scriptPath . '/images/TimePickerResetButton.gif'	)

						)
						. Xml::closeElement( "button" );

			}
		}

		// wrap in span (e.g. used for mandatory inputs)
		if ( ! array_key_exists( 'part of dtp', $other_args ) ) {
			$html = '<span class="inputSpan' . ($is_mandatory ? ' mandatoryFieldSpan' : '') . '">' .$html . '</span>';
		}

		// third: if the timepicker is not disabled set up JS attributes ans assemble JS call
		if ( !$is_disabled ) {

			self::timepickerSetup();

			// set min time if valid, else use default
			if ( array_key_exists( 'mintime', $other_args )
					&& ( preg_match( '/^\d+:\d\d$/', trim( $other_args['mintime'] ) ) == 1 ) ) {
					$minTime = trim( $other_args[ 'mintime' ] );
			} else {
				$minTime = '00:00';
			}

			// set max time if valid, else use default
			if ( array_key_exists( 'maxtime', $other_args )
					&& ( preg_match( '/^\d+:\d\d$/', trim( $other_args['maxtime'] ) ) == 1 ) ) {
					$maxTime = trim( $other_args[ 'maxtime' ] );
			} else {
				$maxTime = '23:59';
			}

			// set interval if valid, else use default
			if ( array_key_exists( 'interval', $other_args )
					&& preg_match( '/^\d+$/', trim( $other_args['interval'] ) ) == 1 ) {
					$interval = trim( $other_args[ 'interval' ] );
			} else {
				$interval = '15';
			}

			// build JS code from attributes array
			$jsattribs = array(
				"minTime" => $minTime,
				"maxTime" => $maxTime,
				"interval" => $interval,
				"format" => "hh:mm"
			);

			if ( array_key_exists( 'part of dtp', $other_args ) ) {
				$jsattribs['partOfDTP'] = $other_args['part of dtp'];
			}

			$jstext = Xml::encodeJsVar( $jsattribs );

			$jstext = <<<JAVASCRIPT
jQuery(function(){ jQuery('#input_{$sfgFieldNum}_tp_show').SemanticForms_registerInputInit(SFI_TP_init, $jstext ); });
JAVASCRIPT;

			// write JS code directly to the page's code
			$wgOut->addScript( '<script type="text/javascript">' . $jstext . '</script>' );

			// return HTML and name of JS init function

		}

		return $html;

	}

	/**
	 * Setup for input type "menuselect".
	 * Adds the Javascript code and css used by all menuselects.
	*/
	static private function menuselectSetup() {

		global $wgOut;
		global $sfigSettings;

		static $hasRun = false;

		if ( !$hasRun ) {

			$wgOut->addScript( '<script type="text/javascript">sfigScriptPath="' . $sfigSettings->scriptPath . '";</script> ' );
			$wgOut->addScript( '<script type="text/javascript" src="' . $sfigSettings->scriptPath . '/libs/menuselect.js"></script> ' );
			$wgOut->addExtensionStyle( $sfigSettings->scriptPath . '/skins/SFI_Menuselect.css' );

		}

	}

	/**
	 * Definition of input type "menuselect"
	 *
	 * Returns the html code to be included in the page and registers the
	 * input's JS initialisation method
	 *
	 * @param string $cur_value current value of this field (which is sometimes null)
	 * @param string $input_name HTML name that this input should have
	 * @param boolean $is_mandatory indicates whether this field is mandatory for the user
	 * @param boolean $is_disabled indicates whether this field is disabled (meaning, the user can't edit)
	 * @param array $other_args hash representing all the other properties defined for this input in the form definition
	 * @return string html code of input
	*/
	static function menuselectHTML( $cur_value, $input_name, $is_mandatory, $is_disabled, $other_args ) {
		global $wgParser, $wgUser, $wgTitle, $wgOut;
		global $sfgFieldNum;
		global $sfigSettings;

		self::menuselectSetup();

		// first: set up HTML attributes
		$inputFieldDisabled =
			array_key_exists( 'disable input field', $other_args )
			|| ( !array_key_exists( 'enable input field', $other_args ) && $sfigSettings->timePickerDisableInputField )
			|| $is_disabled	;

		// second: assemble HTML
		// create visible input field (for display) and invisible field (for data)
		$html = self::textHTML( $cur_value, '', $is_mandatory, $inputFieldDisabled, $other_args, "input_{$sfgFieldNum}_show", null, "createboxInput" )
				. Xml::element( "input", array(
					'id' => "input_{$sfgFieldNum}",
					'type' => 'hidden',
					'name' => $input_name,
					'value' => $cur_value
				) );


		$html .= "<span class='SFI_menuselect' id='span_{$sfgFieldNum}_tree'>";


		// if ( array_key_exists( 'delimiter', $other_args ) ) $delimiter = $other_args[ 'delimiter' ];
		// else $delimiter = ' ';

		// parse menu structure

		$options = ParserOptions::newFromUser( $wgUser );

		$oldStripState = $wgParser->mStripState;
		$wgParser->mStripState = new StripState();

		// FIXME: SF does not parse options correctly. Users have to replace | by {{!}}
		$structure = str_replace( '{{!}}', '|', $other_args["structure"] );

		$structure = $wgParser->parse( $structure, $wgTitle, $options )->getText();

		$wgParser->mStripState = $oldStripState;


		$html .= str_replace( '<li', '<li class=\'ui-state-default\'', $structure );

		$html .= "</span>";

		// wrap in span (e.g. used for mandatory inputs)
		$html = '<span class="inputSpan' . ($is_mandatory ? ' mandatoryFieldSpan' : '') . '">' .$html . '</span>';

		$jstext = <<<JAVASCRIPT
jQuery(function(){ jQuery('#input_$sfgFieldNum').SemanticForms_registerInputInit(SFI_MS_init, null ); });
JAVASCRIPT;

			// write JS code directly to the page's code
		$wgOut->addScript( '<script type="text/javascript">' . $jstext . '</script>' );

		return array( $html, "", "SFI_MS_init" );

	}
}
