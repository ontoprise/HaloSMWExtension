<?php

global $wgAjaxExportList;
$wgAjaxExportList[] = 'asff_getNewForm';

function asff_getNewForm($categories, $existingAnnotations){

	//init params
	$categories = explode('<span>,</span> ', $categories);
	
	$existingAnnotations = explode('<<<', $existingAnnotations);
	unset($existingAnnotations[0]);
	
	echo('<pre>'.print_r($existingAnnotations, true).'</pre>');
	
	$annotationsToKeep = array();
	foreach($existingAnnotations as $anno){
		$anno = explode('[', $anno);
		$anno[1] = substr($anno[1], 0, strlen($anno[1]) -1);
		if(count($anno) == 2){
			$annotationsToKeep[$anno[1]] = true;
		} else {
			//date input field or so
			if(!array_key_exists($anno[1], $annotationsToKeep)){
				$annotationsToKeep[$anno[1]] = array();
			}
			$anno[2] = substr($anno[2], 0, strlen($anno[2]) -1);
			$annotationsToKeep[$anno[1]][$anno[2]] = true;
		}
	}
	foreach($annotationsToKeep as $anno => $fields){
		if(is_array($fields)){
			if(array_key_exists('year', $fields) && !array_key_exists('day', $fields)){
				//this is a form input field of date which has not yet been filled with values
				unset($annotationsToKeep[$anno]);
			} else if(array_key_exists('is_checkbox', $fields) && count($fields) == 1){
				//this is a checkbox, that has not been selected
				unset($annotationsToKeep[$anno]);
			} else {
				//keep this
				$annotationsToKeep[$anno] = true;
			}
		}
	}
	
	echo('<pre>'.print_r($annotationsToKeep, true).'</pre>');
	
	//create the form definition
	$result = ASFFormGenerator::getInstance()->generateFormForCategories($categories);
	
	//No form can be created
	if(!$result){
		//todo: deal with this case
	}
	
	ASFFormGenerator::getInstance()->getFormDefinition()
		->addUnresolvedAnnotationsSection($annotationsToKeep);
	
	//Get the form HTML
	global $asfDummyFormName;
	$errors = ASFFormGeneratorUtils::createFormDummyIfNecessary();
	$title = Title::newFromText('Form:'.$asfDummyFormName);
	$article = new Article($title);
	
	global $wgTitle;
	$wgTitle = $title;
			
	$formPrinter = new ASFFormPrinter(); 
	$html = $formPrinter->formHTML(
		$article->getRawText(), false, false, $article->getID(), '', $asfDummyFormName, null, true);
	$html = $html[0];
	
	//Do post processing
	$startMarker = '<div id="asf_formfield_container';
	$html = substr($html, strpos($html, $startMarker) + strlen($startMarker));
	
	$endmarker = '</div><div id="asf_formfield_container2';
	$html = substr($html, 0, strpos($html, $endmarker));
	
	//return result
	$result = array('html' => $html);
	$result = json_encode($result);
	return '--##starttf##--' . $result . '--##endtf##--';
	
	
	
	
}

class ASFFormUpdater {
	
	/*
	 * Get the post processed property value
	 *
	 * Code has been copied without any changes from
	 * SFFormPrinter->formHTML()
	 */
	private function getPropertyValue($cur_value){
		if ( is_array( $cur_value ) ) {
			// first, check if it's a list
			if ( array_key_exists( 'is_list', $cur_value ) &&
					$cur_value['is_list'] == true ) {
				$cur_value_in_template = "";
				if ( array_key_exists( 'delimiter', $field_args ) ) {
					$delimiter = $field_args['delimiter'];
				} else {
					$delimiter = ",";
				}
				foreach ( $cur_value as $key => $val ) {
					if ( $key !== "is_list" ) {
						if ( $cur_value_in_template != "" ) {
							$cur_value_in_template .= $delimiter . " ";
						}
						$cur_value_in_template .= $val;
					}
				}
			} else {
				// otherwise:
				// if it has 1 or 2 elements, assume it's a checkbox; if it has
				// 3 elements, assume it's a date
				// - this handling will have to get more complex if other
				// possibilities get added
				if ( count( $cur_value ) == 1 ) {
					$cur_value_in_template = SFUtils::getWordForYesOrNo( false );
				} elseif ( count( $cur_value ) == 2 ) {
					$cur_value_in_template = SFUtils::getWordForYesOrNo( true );
					// if it's 3 or greater, assume it's a date or datetime
				} elseif ( count( $cur_value ) >= 3 ) {
					$month = $cur_value['month'];
					$day = $cur_value['day'];
					if ( $day !== '' ) {
						global $wgAmericanDates;
						if ( $wgAmericanDates == false ) {
							// pad out day to always be two digits
							$day = str_pad( $day, 2, "0", STR_PAD_LEFT );
						}
					}
					$year = $cur_value['year'];
					$hour = $minute = $second = $ampm24h = $timezone = null;
					if ( isset( $cur_value['hour'] ) ) $hour = $cur_value['hour'];
					if ( isset( $cur_value['minute'] ) ) $minute = $cur_value['minute'];
					if ( isset( $cur_value['second'] ) ) $second = $cur_value['second'];
					if ( isset( $cur_value['ampm24h'] ) ) $ampm24h = $cur_value['ampm24h'];
					if ( isset( $cur_value['timezone'] ) ) $timezone = $cur_value['timezone'];
					if ( $month !== '' && $day !== '' && $year !== '' ) {
						// special handling for American dates - otherwise, just
						// the standard year/month/day (where month is a number)
						global $wgAmericanDates;
						if ( $wgAmericanDates == true ) {
							$cur_value_in_template = "$month $day, $year";
						} else {
							$cur_value_in_template = "$year/$month/$day";
						}
						// include whatever time information we have
						if ( ! is_null( $hour ) )
							$cur_value_in_template .= " " . str_pad( intval( substr( $hour, 0, 2 ) ), 2, '0', STR_PAD_LEFT ) . ":" . str_pad( intval( substr( $minute, 0, 2 ) ), 2, '0', STR_PAD_LEFT );
						if ( ! is_null( $second ) )
							$cur_value_in_template .= ":" . str_pad( intval( substr( $second, 0, 2 ) ), 2, '0', STR_PAD_LEFT );
						if ( ! is_null( $ampm24h ) )
							$cur_value_in_template .= " $ampm24h";
						if ( ! is_null( $timezone ) )
							$cur_value_in_template .= " $timezone";
					} else {
						$cur_value_in_template = "";
					}
				}
			}
		} else { // value is not an array
			$cur_value_in_template = $cur_value;
		}
		
		return $cur_value_in_template;
	}
	
}