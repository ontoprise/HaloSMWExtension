<?php

/**
 * @file
  * @ingroup DAPCP
  *
  * @author Dian
 */

/**
 * PCP utilities.
 *
 * @author Dian
 * @version 0.1
 */
class PCPUtil{
	/**
	 * Replaces placeholders with the given values.
	 *
	 * @param string[] $placeholders The placeholders have the form ':n' with 'n' a digit.
	 * @param string[] $values The values.
	 * @return strung[] The replacements.
	 */
	public static function replaceInHash($placeholders=NULL, $values=NULL){
		$__idx=0;
		$__phKeys = array_keys($placeholders);
		foreach ($values as $__value){			
			foreach ($__phKeys as $__phKey){				
				if ($placeholders[$__phKey] == ":".$__idx){
					$placeholders[$__phKey]= $__value;					
				}
			}
			$__idx++;
		}		
		return $placeholders;
	}

	/**
	 * Public. Convert a string from URL-format to UTF8 proper.
	 *
	 *
	 * @param string $string the name of the article, urlencoded
	 * @return string the UTF8 name of the same article
	 */
	public static function fromURL($string)
	{
		return str_replace("_"," ",urldecode($string));
	}

	/**
	 * Public. Convert a string from UTF8 to URL format
	 *
	 *
	 * @param string $string the UTF8 name of the article
	 * @return string the name of the same article, urlencoded
	 */
	public static function toURL($string)
	{
		return
		str_replace("%2F","/",
		str_replace("%3A",":",
		urlencode(
		str_replace(" ","_",$string)
		)
		)
		);
	}

	/**
	 * If the title sting contains more than one title, return only the first one.
	 * The titles are supposed to be separated by a '|'.
	 *
	 * @param string $title
	 * @return string The single title.
	 */
	public static function trimFirstTitle($title = NULL){
		if (strrpos($title , '|')){
			return substr($title , strrpos($title , '|') +1);
		}else{
			return $title;
		}
	}

	/**
	 * Create a response for the REST server. Passed results have two types of elements:<br/>
	 * *page-Element or
	 * *userCredentials -Element.<br/>
	 *
	 * Example:<i>
	 * <p>
	 * &lt;response&gt;
	 * 	&lt;smw&gt;
	 * 		&lt;page&gt;
	 * 			&lt;!--here comes the page data--&gt;
	 * 		&lt;/page&gt;
	 * 		&lt;userCredetials un="Tester" pw="test" .../&gt;
	 * 	&lt;/smw&gt;
	 * &lt;/response&gt;
	 * </p>
	 * </i>
	 * @see PCPUserCredentials
	 * @see PCPPage
	 *
	 * @param string $result The result of a function call - an XML element.
	 * @param string $status The status of the operation: success or failed.
	 * @return string The XML string.
	 */
	public static function createXMLResponse($result, $status = "success"){
		$__xmlString = '';
		$__xmlString ='<?xml version="1.0" encoding="ISO-8859-1"?>';
		$__xmlString.="\n<response>\n<smw>\n";
		$__xmlString.= $result;
		$__xmlString.= "\n<status>$status</status>";
		$__xmlString.="</smw>\n</response>";
		return $__xmlString;

	}
	/**
	 * Converts an array into an obect (from a nonspecific type).
	 *
	 * @param array $array
	 * @return obejct The converted array.
	 */
	public static function array2Object($array) {
		$__object = new stdClass();
		if (is_array($array) && count($array) > 0) {
			foreach ($array as $__key=>$__value) {
				$__key = strtolower(trim($__key));
				if (!empty($__key)) {
					$__object->$__key = $__value;
				}
			}
		}
		return $__object;
	}

	/**
	 * Converts an object into a hashmap (array).
	 *
	 * @param object $object
	 * @return array The converted object.
	 */
	public static function object2Array($object) {
		$__array = array();
		if (is_object($object)) {
			$__array = get_object_vars($object);
		}
		return $__array;
	}
}
