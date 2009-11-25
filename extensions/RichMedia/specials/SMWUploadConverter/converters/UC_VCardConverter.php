<?php

require_once('SMW_VCardParser.php');

class UCVCardConverter {
	
	private $text = "";
	
	public function __construct($text){
		$this->text = $text;
		return $this;
	}
	
	public function getConvertedText(){
		$vCardParser = new VCard();
		$vCardParser->parse(explode("\n", $this->text));

		global $wgUploadConverterTemplateMapping;
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
		return $result;
	}
}