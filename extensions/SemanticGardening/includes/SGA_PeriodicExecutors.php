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
require_once($sgagIP . '/includes/storage/SGA_PeriodicExecutorsSQL.php');

abstract class SGAPeriodicExecutors {
	
	private static $instance;
	
	public abstract function getAllRegisteredBots();
	
	public abstract function addBot($id, $params, $duration, $lastRun);
	
	public abstract function removeBot($listid);
	
	public function getBotsToRun() {
	   	$allregsiteredBots = $this->getAllRegisteredBots();
	   	$result = array();
	   	foreach($allregsiteredBots as $bot) {
	   		list($id, $botid, $params, $lastrun, $interval) = $bot;
	   		// check if lastrun + interval < now
	   		$lastrunUnix = strtotime($lastrun);
	   		$now = time();
	   		if ($lastrunUnix + $interval < $now) {
	   			$result[] = $bot;
	   		}
	   	}
	   	return $result;
	}
	
    public static function getPeriodicExecutors() {
        if (is_null(self::$instance)) {
            self::$instance = new SGAPeriodicExecutorsSQL();
        }
        return self::$instance;
    }
}