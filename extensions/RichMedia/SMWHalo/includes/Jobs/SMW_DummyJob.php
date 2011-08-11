<?php

/**
 * @file
 * @ingroup SMWHaloJobs
 *  
 * @author Kai Kühn
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
require_once ("$IP/includes/JobQueue.php");

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

