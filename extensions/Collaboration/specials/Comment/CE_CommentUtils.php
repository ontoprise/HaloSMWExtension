<?php
/*  Copyright 2009, ontoprise GmbH
 *  This file is part of the Collaboration-Extension.
 *
 *   The Collaboration-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Collaboration-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup CEComment
 * 
 * Utils for comment component of Collaboration extension.
 *
 * @author Benjamin Langguth
 * Date: 16.11.2009
 *
 */


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