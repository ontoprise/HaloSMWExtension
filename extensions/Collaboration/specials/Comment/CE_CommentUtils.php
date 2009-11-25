<?php

define('CE_COM_RESP_START',
			'<?xml version="1.0"?>'."\n".
			'<ReturnValue xmlns="http://www.ontoprise.de/collaboration#">'."\n".
    		'<value>');

define('CE_COM_RESP_MIDDLE',
			'</value>'. "\n" .
			'<message>');

define('CE_COM_RESP_END',
			'</message>'."\n".
    		'</ReturnValue>'."\n");


class CECommentUtils {

	/**
	 * Create a response for the AJAX functions of Collaboration Comment.
	 *
	 * Example:
	 *
	 * @param string $status The status of the operation: success or failed.
	 * @param string $message The user message of the function status.
	 *
	 * @return string The XML string.
	 */
	public static function createXMLResponse($message, $statusCode = 0){
		
		$xmlString = CE_COM_RESP_START;
		$xmlString .= $statusCode;
		$xmlString .= CE_COM_RESP_MIDDLE;
		$xmlString .= $message;
		$xmlString .= CE_COM_RESP_END;
		return $xmlString;

	}

	/**
	 *
	 * @param <string> javascript-escaped string
	 * @return <string> unescaped string
	 */
	public function unescape($source) {
		$decodedStr = '';
		$pos = 0;
		$len = strlen ($source);

		while ($pos < $len) {
			$charAt = substr ($source, $pos, 1);
			if ($charAt == '%') {
				$pos++;
				$charAt = substr ($source, $pos, 1);
				if ($charAt == 'u') {
					// we got a unicode character
					$pos++;
					$unicodeHexVal = substr ($source, $pos, 4);
					$unicode = hexdec ($unicodeHexVal);
					$decodedStr .= code2utf($unicode);
					$pos += 4;
				} else {
					// we have an escaped ascii character
					$hexVal = substr ($source, $pos, 2);
					$decodedStr .= code2utf (hexdec ($hexVal));
					$pos += 2;
				}
			} else {
				$decodedStr .= $charAt;
				$pos++;
			}
		}
		return $decodedStr;
	}
	
}