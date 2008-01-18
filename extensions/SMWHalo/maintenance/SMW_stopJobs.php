<?php
/*
 * Created on 17.01.2008
 * 
 * Stops the PHP process which runs jobs in background.
 * 
 * Usage:
 * 
 * php SMW_stopJobs.php
 * 
 * Author: kai
 */
 
 if ($_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}

print "\nStopping jobs...";
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_connect($socket, "127.0.0.1", "9876"); // port is freely chosen
print "done!";
?>
