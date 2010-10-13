<?php
/**
 * File containing SCRefreshJob. @file
 * @ingroup SMW
 */

if ( !defined( 'MEDIAWIKI' ) ) {
  die( "This file is part of the Semantic NotifyMe Extension. It is not a valid entry point.\n" );
}
global $IP;
require_once( "$IP/includes/JobQueue.php" );

class SMWConnectorRefreshJob extends Job {

	function __construct(Title $title) {
		parent::__construct( 'SMWConnectorRefreshJob', $title);
	}

	/**
	 * Run job
	 * @return boolean success
	 */
	function run() {
		wfProfileIn('SMWConnectorRefreshJob::run (SMW)');
		// refresh schema mapping properties, TBD!!!
		
		wfProfileOut('SMWConnectorRefreshJob::run (SMW)');
		return true;
	}

	/**
	 * This actually files the job. This is prevented if the configuration of SMW
	 * disables jobs.
	 * NOTE: Any method that inserts jobs with Job::batchInsert or otherwise must
	 * implement this check individually. The below is not called in these cases.
	 */
	function insert() {
		global $smwgEnableUpdateJobs;
		if ($smwgEnableUpdateJobs) {
			parent::insert();
		}
	}
}
