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
 * @ingroup SMWHaloJobs
 *  
 * @author Kai K�hn
 */
/* The DummyJob Class
 * - SemanticMediaWiki Extension -
 *
 * 1. Rename the class. Fill the run() method with the tasks the job should do for you.
 * 2. Insert the job into the queue using its insert() method
 *    or if you generate a bunch of jobs, put them in an array $jobs and use Job::batchInsert($jobs)
 *
 * @author Daniel M. Herzig
 *
 */

global $IP;
require_once ("$IP/includes/job/JobQueue.php");

class SMW_DummyJob extends Job {

	//Constructor
	function __construct($title, $params = '', $id = 0) {
		wfDebug(__METHOD__);
		parent::__construct( get_class($this), $title, $params, $id );

	}

	/**
	 * Run method
	 * @return boolean success
	 */
	function run() {

		//What ever the Job has to do...

		return true;
	}
}

