<?php
/*  Copyright 2008, ontoprise GmbH
 *  This file is part of the halo-Extension.
 *
 *   The halo-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The halo-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * todo: describe this
 *
 * @author Ingo Steinbauer
 *
 */

require_once("SMW_WSStorage.php");
require_once("SMW_WebService.php");

/*
 * todo: describe this
 */
class WebServiceCache {

	/**
	 *
	 */
	public static function removeWSParameterPair($webServiceId, $parameterSetId){
		if(sizeof(WSStorage::getDatabase()->getArticlesUsingWSParameterSetPair($wsPageId, $parameterSetId)) == 0){
			$wsResult = WSStorage::getDatabase()->removeWSEntryFromCache($webServiceId, $parameterSetId);
		}
	}

	/**
	 *
	 */
	public static function removeWS($webServiceId){
		WSStorage::getDatabase()->removeWSFromCache($webServiceId);
	}
}





?>