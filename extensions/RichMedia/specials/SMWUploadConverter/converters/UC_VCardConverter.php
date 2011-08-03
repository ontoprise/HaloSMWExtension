<?php

/**
 * @file
  * @ingroup RMUploadConverter
  * 
  */

require_once('UC_VCardParser.php');

class UCVCardConverter {
	
	private $text = "";
	
	public function __construct($text){
		$this->text = $text;
		return $this;
	}
	
	public function getConvertedText(){
		wfProfileIn( __METHOD__ . ' [Rich Media]' );
		global $wgUploadConverterTemplateMapping;

		$vCardParser = new VCard();
		$explodedText = explode("\n", $this->text);
		$vCardParser->parse($explodedText);

		$result = "{{".$wgUploadConverterTemplateMapping['TemplateName'];
		unset($wgUploadConverterTemplateMapping['TemplateName']);
		foreach ($wgUploadConverterTemplateMapping as $attribute => $param){
			
			$values = $vCardParser->getProperties($attribute);
			if ($values) {
				$result .= "\n| ".$param." = ";
				$first = true;
				foreach ($values as $value) {
					if(!$first){
						$result .= "; ";
					}
					$first = false;
					$components = $value->getComponents();
					$lines = array();
					foreach ($components as $component) {
						if ($component) {
							if(!mb_check_encoding($component, "UTF-8")){
								$component = utf8_encode($component);
							}
							$lines[] = $component;
						}
					}
					$result .= join(",", $lines);
					if(array_key_exists('TYPE', $value->params)){
						$types = $value->params['TYPE'];
						if ($types) {
							$type = join(", ", $types);
							$result .= " (" . ucwords(strtolower($type)) . ")";
						}
					}
				}
			}
		}
		$result .= "}}";

		wfProfileOut( __METHOD__ . ' [Rich Media]' );
		return $result;
	}
}