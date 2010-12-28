<?php
/**
 * @author Ning Hu
 */

if (!defined('MEDIAWIKI')) die();

class SCRESTful extends SpecialPage {
	public function __construct() {
		parent::__construct('RESTful');
	}

	function execute($query) {
		global $wgRequest;
		$rest = $wgRequest->getVal('rest');

		if (! $rest) {
			$rest = $query;
		}

		global $wgOut, $wgUser;
		if($rest == '') {
			$this->setHeaders();
			$text = '<p class="error">' . wfMsg('sc_restful_badurl') . "</p>\n";

			$wgOut->addHTML($text);
		} elseif ( !in_array('edit', $wgUser->getRights() ) ) {		
			$this->setHeaders();
			$text = '<p class="error">' . wfMsg('sc_restful_forbidden') . "</p>\n";

			$wgOut->addHTML($text);
		} else {
			$wgOut->disable();
			SCRest::registerRestApis();
			ob_start();
//			header( "Content-type: application/json; charset=UTF-8" );
			$result = SCRest::callRestApi($query);
			print $result;

			ob_flush();
			flush();
		}
	}
}

global $scgRestApiPatterns;
$scgRestApiPatterns['/lookup/form/{form_name}'] = 'restLookupForm';
$scgRestApiPatterns['/lookup/template/{template_name}'] = 'restLookupTemplate';
$scgRestApiPatterns['/lookup/field/{template_name}/{field_name}'] = 'restLookupField';
$scgRestApiPatterns['/mapping/{map_type}/{form_name}/{template_name}/{field_name}/{mapped_form_name}/{mapped_template_name}/{mapped_field_name}'] = 'addSchemaMapping';

function restFillLookupForm( $res ) {
	$first = true;
	$result = '[';
	foreach($res as $r) {
		if(!$first) {
			$result .= ',';
		}
		$result .= '
		{
			type : "same as", 
			src : "' . str_replace('"', '\"', $r['src']) . '",
			map : "' . str_replace('"', '\"', $r['map']) . '"
		}';
		$first = false;
	}
	$result .= "\n]";
	return $result;
}
function restLookupForm($param) {
	$fname = 'SCRESTful::restLookupForm (SMW)';
	wfProfileIn($fname);

	$sStore = SCStorage::getDatabase();
	$form_name = Title::newFromText($param['form_name'])->getText();
	$res = $sStore->lookupFormMapping($form_name);
	$result = restFillLookupForm($res);

	wfProfileOut($fname);
	return $result;
}
function restLookupTemplate($param) {
	$fname = 'SCRESTful::restLookupTemplate (SMW)';
	wfProfileIn($fname);

	$sStore = SCStorage::getDatabase();
	$template_name = Title::newFromText($param['template_name'])->getText();
	$res = $sStore->lookupTemplateMapping($template_name);
	$result = restFillLookupForm($res);

	wfProfileOut($fname);
	return $result;
}
function restLookupField($param) {
	$fname = 'SCRESTful::restLookupField (SMW)';
	wfProfileIn($fname);

	$sStore = SCStorage::getDatabase();
	$template_name = Title::newFromText($param['template_name'])->getText();
	$res = $sStore->lookupFieldMapping($template_name, $param['field_name']);
	$result = restFillLookupForm($res);

	wfProfileOut($fname);
	return $result;
}
function addSchemaMapping($param) {
	$fname = 'SCRESTful::addSchemaMapping (SMW)';
	wfProfileIn($fname);
	if(Title::newFromText($param['map_type'])->getText() != "Same as"){
		wfProfileOut($fname);
		return "Map type is not supported yet.";
	}
	$form_name = Title::newFromText($param['form_name'])->getText();
	$template_name = Title::newFromText($param['template_name'])->getText();
	$field_name = $param['field_name'];
	$mapped_form_name = Title::newFromText($param['mapped_form_name'])->getText();
	$mapped_template_name = Title::newFromText($param['mapped_template_name'])->getText();
	$mapped_field_name = $param['mapped_field_name'];

	$sStore = SCStorage::getDatabase();
	$sStore->saveMappingDataSameAs($form_name . '.' . $template_name . '.' . $field_name, 
		$mapped_form_name . '.' . $mapped_template_name . '.' . $mapped_field_name);
	
	$param = array('template_name'=>$template_name, 'field_name'=>$field_name);
	
	wfProfileOut($fname);
	return restLookupField($param);
}
