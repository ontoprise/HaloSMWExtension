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
 * @ingroup FacetedSearch
 *
 * This file contains the class FSMessages.
 * 
 * @author Thomas Schweitzer
 * Date: 23.03.2012
 * 
 */
if ( !defined( 'SOLRPROXY' ) ) {
	die( "This file is part of the FacetedSearch extension. It is not a valid entry point.\n" );
}

//--- Includes ---

/**
 * This class contains messages in all supported languages.
 * 
 * @author Thomas Schweitzer
 * 
 */
class FSMessages {
	
	//--- Public methods ---
	
	public static function msg($msgID) {
		global $spgHaloACLConfig;
		$lang = $spgHaloACLConfig['contentlanguage'];
		if (!array_key_exists($lang, self::$mMessages)) {
			$lang = 'en';
		}
		return self::$mMessages[$lang][$msgID];
	}
	
	private static $mMessages = array(
		"en" => array(
			'snippet_removed' => 'The preview was replaced because it may contain protected content.'
			),
		"de" => array(
			'snippet_removed' => 'Die Vorschau wurde ersetzt weil sie geschützte Inhalte zeigen könnte.'
			),
	);
}