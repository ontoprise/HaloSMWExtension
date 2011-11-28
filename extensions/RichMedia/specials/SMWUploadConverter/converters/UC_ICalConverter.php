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


/**
 * @file
  * @ingroup RMUploadConverter
  * 
  * @author Ingo Steinbauer
 */

require_once('UC_ICalParser.php');

class UCICalConverter {
	
	private $text = "";
	
	public function __construct($text){
		$this->text = $text;
		return $this;
	}
	
	public function getConvertedText(){
		wfProfileIn( __METHOD__ . ' [Rich Media]' );
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

		wfProfileOut( __METHOD__ . ' [Rich Media]' );
		return $completeResult;
	}
}
