<?php
/*  Copyright 2008, ontoprise GmbH
 *  This file is part of the Data Import-Extension.
 *
 *   The Data Import-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Data Import-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup DIWebServices
 * 
 * @author Ingo Steinbauer
 * This file provides the WebServiceCache class which
 * provides some utillity methods for cache access
 * @author Ingo Steinbauer
 *
 */

require_once("SMW_WSStorage.php");
require_once("SMW_WebService.php");

/**
 * this class provides some static methods for
 * managing the cache
 *
 */
class WebServiceCache {

	/**
	 * this function checks if the given ws-parameterset-pair is
	 * no longer referenced and deletes the related cache entry
	 * in this case
	 *
	 * @param string $webServiceId
	 * @param string $parameterSetId
	 */
	public static function removeWSParameterPair($webServiceId, $parameterSetId){
		if(sizeof(WSStorage::getDatabase()->getUsedWSParameterSetPairs($webServiceId, $parameterSetId)) == 0){
			$wsResult = WSStorage::getDatabase()->removeWSEntryFromCache($webServiceId, $parameterSetId);
		}
	}

	/**
	 * this function deletes all cache entries related
	 * to a wwsd that longer exists..
	 *
	 * @param string $webServiceId
	 */
	public static function removeWS($webServiceId){
		WSStorage::getDatabase()->removeWSFromCache($webServiceId);
	}
}




