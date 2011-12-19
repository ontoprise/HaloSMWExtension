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


global $wgAjaxExportList;
$wgAjaxExportList[] = 'dapi_refreshData';

global $wgHooks;
$wgHooks['ParserFirstCallInit'][] = 'ASFDataPickerParserFunction::registerFunctions';
$wgHooks['LanguageGetMagic'][] = 'ASFDataPickerParserFunction::languageGetMagic';

function dapi_refreshData($wsParam, $dapiId, $selectedIds, $containerId){
	
	global $dapi_instantiations;
	
	$wsCallParameters = array();
	$wsCallParameters[] = "dummy";
	$wsCallParameters[] = $dapi_instantiations[$dapiId]['ws-name'];
	$wsCallParameters[] = $dapi_instantiations[$dapiId]['param'].'='.$wsParam;
	$wsCallParameters[] = "?result.".$dapi_instantiations[$dapiId]['id'];
	$wsCallParameters[] = "?result.".$dapi_instantiations[$dapiId]['label'];
	
	$parser = null;
	$wsresult = DIWebServiceUsage::processCall($parser, $wsCallParameters, true, false, true);
	
	$resultItems = array();
	if(is_array($wsresult)){
		
		$selectedIds = json_decode($selectedIds, true);
		if(!is_array($selectedIds)){
			$selectedIds = json_decode($selectedIds, true);
		}
		
		for($i = 0; $i < count($wsresult[$dapi_instantiations[$dapiId]['id']]); $i++){
			
			if(in_array($wsresult[$dapi_instantiations[$dapiId]['id']][$i], $selectedIds)){
				$selected = 'true';
			} else {
				$selected = 'false';
			}
			
			$resultItems[] = array('id' => $wsresult[$dapi_instantiations[$dapiId]['id']][$i], 
				'label' 	=> $wsresult[$dapi_instantiations[$dapiId]['label']][$i], 'selected' => $selected); 		
		}
	} else {
		//todo: error processing (optional(
	}
	
	$result = array('results' => $resultItems, 'containerId' => $containerId);
	$result = json_encode($result);

	return '--##starttf##--' . $result . '--##endtf##--';
}

class ASFDataPickerInputType {
	
	public static function getHTML($currentValue, $inputName, $isMandatory, $isDisabled, $otherArgs){
		
		$dataPickerId = '';
		if(array_key_exists('input type', $otherArgs)){
			$dataPickerId = $otherArgs['input type'];
		}
		
		global $dapi_instantiations;
		if(!array_key_exists($dataPickerId, $dapi_instantiations)){
			return self::getErrorMessageHTML($dataPickerId);
		}
		
		$className = $isMandatory ? "mandatoryField" : "createboxInput";
		if(array_key_exists('class', $otherArgs)){
			$className .= " " . $otherArgs['class'];
		}
		$className .= " dapi_values_multiselect ";
		
		global $sfgFieldNum;
		$inputFieldId = "input_$sfgFieldNum";
		
		if ( array_key_exists( 'delimiter', $otherArgs)){
			$delimiter = $otherArgs['delimiter'];
		} else {
			//default is comma
			$delimiter = ",";
		}
		
		if(is_array($currentValue) && array_key_exists(0, $currentValue)){
			$currentValue = $currentValue[0];
		}
		
		$json = substr($currentValue, 
			strpos($currentValue, '{{#DataPickerValues:') + strlen('{{#DataPickerValues:'));
		$json = substr($json, 0,  
			strrpos($json, '}}'));
		$json = str_replace(
			array('##dlcb##', '##drcb##', '##pipe##', '##dlsb##', '##drsb##'), array("{{", "}}", "|", "[[", "]]"), $json);
		$json = json_decode($json, true);
		if(!is_array($json)){
			$json = json_decode($json, true);
		}
		
		$values = array();
		if(is_array($json)){
			$values = $json[0];
			
			if(!$dapi_instantiations[$dataPickerId]['remember options']){
				foreach($values as $key => $value){
					if($value['selected'] != 'true'){
						unset($values[$key]);
					}
				}
			}
		}
		
		$wsParam = '';
		if(is_array($json)){
			$wsParam = $json[2];
		}
		
		$size = null;
		if(array_key_exists('size', $otherArgs ) ) {
			$size = $otherArgs['size'];
		}
		
		global $wgOut;
		$wgOut->addModules( 'ext.automaticsemanticforms.datapicker' );
				
		$html = '<span class="dapi-form-field-container" id="dapi-container-'.$sfgFieldNum.'" 
			onmouseover="dapi_showRefreshdControls(event)" onmouseout="dapi_hideRefreshdControls(event)" 
			dapi_container="true">';
		
		$html .= self::getRefreshControlsHTML($wsParam);
		
		$html .= self::getChooseValueControlsHTML(
			$values, $inputFieldId, $inputName, $className, $size, $isDisabled, $isMandatory);
		
		$html .= self::getHiddenDataContainerHTML($delimiter, $dataPickerId);
		
		$html .= '</span>';
		
		return $html;
	}
	
	private static function getRefreshControlsHTML($wsParam){
		$html = '<span class="dapi-refresh-controls" style="display: none">';
		
		$html .= '<input value="'.$wsParam.'"/>';
		
		//todo: use lanfuage file
		$attributes = array('type' => 'button'
			,'onclick' => 'dapi_doRefresh(event)'
			,'value' => 'Refresh');
		$html .= Xml::tags('input', $attributes, '');
		
		$html .= '<br/>';
		
		$html .= '</span>'; 
		
		return $html;
	}
	
	
	private static function getChooseValueControlsHTML(
			$currentValues, $inputFieldId, $inputName, $className, $size, $isDisabled, $isMandatory){
		
		$html = '<span class="dapi-choose-value-controls">';
		
		$optionsText = "";
		foreach($currentValues as $value){
			$attributes = array('value' => $value['value']);
			if($value['selected'] == 'true'){
				$attributes['selected'] = 'selected';
			}
			$optionsText .= Xml::element('option', $attributes, $value['label']);
		}
		
		global $sfgTabIndex;
		$attributes = array(
			'id' => $inputFieldId,
			'tabindex' => $sfgTabIndex,
			'name' => $inputName . '[]',
			'class' => $className,
			'multiple' => 'multiple',
			'width' => '50%',
			'size' => '6',
			'style' => "width: 90%",
			'onmouseover' => "dapi_showRefreshdControls(event)"
		);
		if(!is_null($size)){
			//$attributes['size'] = $size;
		}
		if ($isDisabled ) {
			$attributes['disabled'] = 'disabled';
		}
		$html .= Xml::tags( 'select', $attributes, $optionsText );
		$html .= "\t" . Xml::hidden( $inputName . '[is_list]', 1 ) . "\n";
		
		//necessary for sf's mandatory field handling
		$extraSpanclass = ''; 
		if ( $isMandatory ) {
			$extraSpanclass = 'inputSpan mandatoryFieldSpan';
		}
		$html = Xml::tags( 'span', array( 'class' => $extraSpanclass ), $html );
		
		$html .= '</span>';
		
		return $html;
	}
	
	
	
	private static function getHiddenDataContainerHTML($delimiter, $dataPickerId){
		$html = '<span class="dapi-hidden-data" style="display: none">';	

		$html .= '<span class="dapi-delimiter">'.$delimiter.'</span>';
		
		$html .= '<span class="dapi-dpid">'.$dataPickerId.'</span>';
		
		$html .= '</span>';
		
		return $html;		
	}
	
	
	
	private static function getErrorMessageHTML($dataPickerId){
		$html = '<span class="dapi-error">';	

		//todo; use language file
		$html .= 'A datapicker with the id '.$dataPickerId.' has not been defined.';
		
		$html .= '</span>';
		
		return $html;		
	}

}


class ASFDataPickerParserFunction {
	
	static function registerFunctions( &$parser ) {
		$parser->setFunctionHook( 'DataPickerValues', 
			array( 'ASFDataPickerParserFunction', 'renderDataPickerValues' ));
			
		return true;
	}
	
	
	static function languageGetMagic( &$magicWords, $langCode = "en" ) {
		$magicWords['DataPickerValues']	= array ( 0, 'DataPickerValues' );
		
		return true;
	}
	
	
	static function renderDataPickerValues( &$parser) {
		$json = func_get_args();
		$json = trim($json[1]);
		$json = str_replace(
			array('##dlcb##', '##drcb##', '##pipe##', '##dlsb##', '##drsb##'), array("{{", "}}", "|", "[[", "]]"), $json);
		$json = json_decode($json, true);
		$json = json_decode($json, true);
		
		$values = $json[0];
		$delimiter = $json[1];
		
		$output = array();
		foreach($values as $value){
			if($value['selected'] == 'true'){
				$output[] = $value['value'];
			}
		}
		
		$output = implode($delimiter, $output);
		
		return $output;
	}
	
	
}













