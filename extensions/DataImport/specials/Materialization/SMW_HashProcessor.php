<?php

/**
 * @file
  * @ingroup DIWSMaterialization
  * 
  * @author Ingo Steinbauer
 */

class SMWHashProcessor {
	
	public static function generateHashValue($message){
		return hash("md5", $message);
	}
	
	public static function isHashValueEqual($hashOne, $hashTwo){
		if($hashOne != $hashTwo){
			return false;
		}
		return true;
	}
}