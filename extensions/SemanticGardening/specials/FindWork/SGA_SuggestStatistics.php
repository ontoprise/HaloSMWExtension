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
 * @ingroup SemanticGardeningStorage
 *
 * Created on 26.11.2007
 *
 * Author: kai
 */

abstract class SMWSuggestStatistics {

	private static $store;

	/**
	 * Returns pages edited by $username, which have gardening issues of the given type.
	 *
	 * @param $bot_id
	 * @param $gi_class
	 * @param $gi_type
	 * @param $username
	 * @param $requestoptions
	 */
	public abstract function getLastEditedPages($botID, $gi_class, $gi_type, $username, $requestoptions);

	/**
	 * Returns pages which are member of the same category as articles edited by $username
	 * and which have gardening issues of the given type.
	 *
	 * @param $bot_id
	 * @param $gi_class
	 * @param $gi_type
	 * @param $username
	 * @param $requestoptions
	 */
	public abstract function getLastEditedPagesOfSameCategory($botID, $gi_class, $gi_type, $username, $requestoptions) ;

	/**
	 * Returns undefined categories which are used on articles edited by $username
	 *
	 * @param $username
	 * @param $requestoptions
	 */
	public abstract function getLastEditPagesOfUndefinedCategories($username, $requestoptions) ;

	/**
	 * Returns undefined properties which are used on articles edited by $username
	 *
	 * @param $username
	 * @param $requestoptions
	 */
	public abstract function getLastEditPagesOfUndefinedProperties($username, $requestoptions) ;



	/**
	 * Setups tables
	 *
	 */
	public abstract function setup($verbose = true);

	public static function getStore() {
		global $sgagIP, $wgUser;
		if (self::$store == NULL) {

			require_once($sgagIP . '/specials/FindWork/SGA_SuggestStatisticsSQL2.php');
			self::$store = new SMWSuggestStatisticsSQL2();

		}
		return self::$store;
	}
}

