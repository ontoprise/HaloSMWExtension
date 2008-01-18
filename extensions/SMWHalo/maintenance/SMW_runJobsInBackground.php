<?php
/*
 * Created on 17.01.2008
 *
 * Starts a PHP process in background which runs jobs periodically.
 * Set $wgJobRunRate = 0 in LocalSettings.php when using this script.
 * 
 * Usage:
 * 
 * php SMW_runJobsInBackground.php [--r=<rate>]
 * 
 * Default rate is 2 jobs/second
 * 
 * Author: kai
 */
 if ($_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}

 $mediaWikiLocation = dirname(__FILE__) . '/../../..';
 require_once "$mediaWikiLocation/maintenance/commandLine.inc";
 
 $rate = $options['r'];

 if (!is_numeric($rate) || $rate < 1) {
 	$rate = 2; // 2 jobs/second is default rate
 }
 
 $dbw = wfGetDB( DB_MASTER );

 $socket = socket_create_listen("9876"); // port is freely chosen
 socket_set_nonblock($socket);
 
 print "\nRunning jobs... (rate is: $rate jobs/second)\n";
	for (;;) {
		sleep($rate);
		
		$job = Job::pop();
		
		$accept_sock = @socket_accept($socket);	
		if ($accept_sock !== false) {
			socket_getpeername($accept_sock, $name);
			if ($name == '127.0.0.1') { // if it comes from localhost
				print "\n\nStopped.";
				break;	
			}
		}
		
		
		if ($job == false || $job == NULL) {
			continue;
		}
		print $job->id . "  " . $job->toString() . "\n";
		
		if ( !$job->run() ) {
			print "Error: {$job->error}\n";
		}
		
	}

?>
