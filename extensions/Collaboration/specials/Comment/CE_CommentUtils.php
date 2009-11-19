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
	
}