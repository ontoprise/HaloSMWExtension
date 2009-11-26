<?php

require_once('UC_ICalParser.php');

class UCICalConverter {
	
	private $text = "";
	
	public function __construct($text){
		$this->text = $text;
		return $this;
	}
	
	public function getConvertedText(){
		global $wgUploadConverterTemplateMapping;
		$completeResult = "";
		$iCalParser = new ICalParser();
		$iCals = $iCalParser->parse($this->text);

		foreach($iCals as $iCalArray){
			$result = "{{".$wgUploadConverterTemplateMapping['TemplateName'];
			foreach ($iCalArray as $attribute => $value) {
				if(array_key_exists($attribute, $wgUploadConverterTemplateMapping)){
					if(!mb_check_encoding($value, "UTF-8")){
						$value = utf8_encode($value);
					}
					$result .= "\n|".$wgUploadConverterTemplateMapping[$attribute]
						." = ".$value;
				}
			}
			$result .= "}}";
			$completeResult .= $result."\n";	
		}
		return $completeResult;
	}
}