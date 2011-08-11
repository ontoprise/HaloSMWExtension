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

* 	Created on 17.01.2008
*
* 	Starts a PHP process in background which runs jobs periodically.
* 	Set $wgJobRunRate = 0 in LocalSettings.php when using this script.
* 
* 	Usage:
* 
* 		php SMW_runJobsInBackground.php [--r=<rate>]
* 
* 	Default rate is 0.5 jobs/second
* 
* 	Author: kai
*/
/**
 *  @file
 *  @ingroup SMWHaloMaintenance
 *  
 *  @author Kai Kühn
 */
if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}

 $mediaWikiLocation = dirname(__FILE__) . '/../../..';
 require_once "$mediaWikiLocation/maintenance/commandLine.inc";
 
 $rate = array_key_exists('r', $options) ? $options['r'] : NULL;

 if (!is_numeric($rate) || $rate > 1) {
 	$rate = 0.5; // 0.5 jobs/second is default rate
 }
 
 $dbw = wfGetDB( DB_SLAVE );
 
 
 // define socket which listens for a break signal
 $socket = socket_create_listen("9876"); // port is freely chosen
 socket_set_nonblock($socket);
 
 // max number of threads to be considered to calculate sleeping time
 define('MAX_THREADS_CONSIDERED', 10);
 
 global $wgLoadBalancer;
 print "-------------------------------------------------\n";
 print " Running jobs... ($rate jobs/second)    		 \n";
 print "-------------------------------------------------\n";
	for (;;) {
								
		// determine the most lagged slave
		// if $lag == -1, there's no slave.
		list( $host, $lag ) = LBFactory::singleton()->getMainLB()->getMaxLag();
		if ($lag == -1) {
			// make sleeping time adaptive to database load.
			$runningThreads = smwfGetNumOfRunningThreads($dbw);
			$runningThreads = $runningThreads <= MAX_THREADS_CONSIDERED ? 
								$runningThreads : MAX_THREADS_CONSIDERED;
								
			// wait depending on user-defined $rate and server load
			sleep(1/$rate + $runningThreads);
		} else {
			// wait for most lagged slave to be *below* 1/$rate + 3 seconds lag time.
			wfWaitForSlaves(1/$rate + 3);
		}
		
		// get next job
		$job = Job::pop();
		
		// is there a break signal?
		$accept_sock = @socket_accept($socket);	
		if ($accept_sock !== false) {
			socket_getpeername($accept_sock, $name);
			if ($name == '127.0.0.1') { 
				// only stop, if request comes from localhost. 
				// TODO: is this SAVE? spoofing?
				print "\n\nStopped.";
				socket_close($accept_sock);
				break;	
			}
		}
		
		
		if ($job == false || $job == NULL) {
			continue;
		}
		print $job->id . "  " . $job->toString() . "\n";
		
		// run job
		if ( !$job->run() ) {
			print "Error: {$job->error}\n";
		}
		
	}
	
	socket_close($socket);
/**
 * Returns number of running threads in mysqld
 */
function smwfGetNumOfRunningThreads(& $db) {
	
	$res = $db->query( 'SHOW STATUS LIKE \'Threads_%\'' );
		# Find slave SQL thread
		while ( $row = $db->fetchObject( $res ) ) {
			/* This should work for most situations - when default db 
			 * for thread is not specified, it had no events executed, 
			 * and therefore it doesn't know yet how lagged it is.
			 *
			 * Relay log I/O thread does not select databases.
			 */
			if ($row->Variable_name == 'Threads_running') {
				$threads_running = $row->Value;
			}
			
			
		}
		$db->freeResult($res);
		return $threads_running;
}

