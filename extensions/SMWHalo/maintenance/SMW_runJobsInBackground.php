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
 * Default rate is 0.5 jobs/second
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

 if (!is_numeric($rate) || $rate > 1) {
 	$rate = 0.5; // 0.5 jobs/second is default rate
 }
 
 $dbw = wfGetDB( DB_MASTER );

 $socket = socket_create_listen("9876"); // port is freely chosen
 socket_set_nonblock($socket);
 
 define('MAX_THREADS_CONSIDERED', 10);
 
 print "-------------------------------------------------\n";
 print " Running jobs... ($rate jobs/second)    		 \n";
 print "-------------------------------------------------\n";
	for (;;) {
		
		// make sleeping time adaptive to database load.
		$currentThreadNum = smwfGetDBUserThreadNum($dbw);
		$currentThreadNum = $currentThreadNum <= MAX_THREADS_CONSIDERED ? 
							$currentThreadNum : MAX_THREADS_CONSIDERED;
		
		sleep(1/$rate + $currentThreadNum);
		
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

/**
 * Returns number of threads created by $wgDBuser
 */
function smwfGetDBUserThreadNum(& $db) {
	global $wgDBuser;
	$count = 0;
	$res = $db->query( 'SHOW PROCESSLIST' );
		# Find slave SQL thread
		while ( $row = $db->fetchObject( $res ) ) {
			/* This should work for most situations - when default db 
			 * for thread is not specified, it had no events executed, 
			 * and therefore it doesn't know yet how lagged it is.
			 *
			 * Relay log I/O thread does not select databases.
			 */
			if ( $row->User == $wgDBuser ) {
				$count++;
			}
		}
		return $count;
}
?>
