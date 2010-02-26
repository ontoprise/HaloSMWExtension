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
  * @ingroup DIWSMaterialization
  * 
  * @author Ingo Steinbauer
 */

/**
 * interface for the materialization storage layer implementations
 *
 */
interface IMaterializationStorage{

	/**
	 * Setups database for Materialization
	 *
	 * @param boolean $verbose
	 */
	public function setup($verbose);

	/**
	 * Add the data of a new materialization
	 *
	 * @param string $pageId : id of the page where the materialization takes place
	 * @param string $callHash : hash value of the call of which the result gets materialized
	 * @param string $materializationHash : hash value of the materialized call result
	 */
	public function addMaterializationHash($pageId, $callHash, $materializationHash) ;

	/**
	 * get the data of a new materialization
	 *
	 * @param string $pageId : id of the page where the materialization takes place
	 * @param string $callHash : hash value of the call which gets materialized
	 * 
	 * @return string : hash value of the materialized call result or null
	 */
	public function getMaterializationHash($pageId, $callHash);
	
	/**
	 * delete the data of a materialization
	 *
	 * @param string $pageId : id of the page where the materialization takes place
	 * @param string $callHash : hash value of the call which gets materialized
	 */
	public function deleteMaterializationHash($pageId, $callHash);

	
	/**
	 * get all call hashes for a given pageId
	 *
	 * @param string $pageId : id of the page where the materialization takes place
	 *
	 * @return array<string> : hash values of the calls which gets materialized
	 */
	public function getCallHashes($pageId);
}