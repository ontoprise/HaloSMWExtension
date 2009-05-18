<?php

if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
    print "This script must be run from the command line\n$USAGE\m";
    exit();
}


if( version_compare( PHP_VERSION, '5.0.0' ) < 0 ) {
    print "Sorry! This version of MediaWiki requires PHP 5; you are running " .
    PHP_VERSION . ".\n\n" .
        "If you are sure you already have PHP 5 installed, it may be " .
        "installed\n" .
        "in a different path from PHP 4. Check with your system administrator.\n";
    die( -1 );
}

/**
 * Return param content for command line switches
 * i.e. myscript.php -p1 value1 -p2 value2 would use:
 * list($arg1, $arg2) = parseCmdParams('-p1', '-p2');
 * and $arg1 == "value1", $arg2 == "value2"
 *
 * @param string [string... ]
 * @return array
 */
function parseCmdParams() {
    global $argv;
    $params = null;
    $retVars =null;
    foreach (func_get_args() as $arg) {
       $params[]= $arg;
       $retVars[] = null;
    } 
    $paramPos = array_flip($params);
    for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {
        if (isset($paramPos[$arg])) {
            $retVars[$paramPos[$arg]] = next($argv);
        }
    }
    return $retVars;
}


/**
 * Runs an external process synchronous. 
 *
 * @param string $runCommand
 */
function runProcess($runCommand) {
    if (isWindows()) {
        $outputFile= ".output00.out";
        $keepConsoleAfterTermination = false;
        $runCommand = "\"$runCommand\"";
        $wshShell = new COM("WScript.Shell");
        $clOption = $keepConsoleAfterTermination ? "/K" : "/C";
        $runCommand = "cmd $clOption ".$runCommand." > ".$outputFile;
        $oExec = $wshShell->Run($runCommand, 7, true);
        $output = file_get_contents($outputFile);
        unlink($outputFile);
        return $output;
    } else {
        return exec($runCommand);
    }
}

/**
 * Checks if the OS is Windows
 * returns true/false
 */
function isWindows() {
    static $thisBoxRunsWindows;
    
    if (! is_null($thisBoxRunsWindows)) return $thisBoxRunsWindows;
    
    ob_start();
    phpinfo();
    $info = ob_get_contents();
    ob_end_clean();
    //Get Systemstring
    preg_match('!\nSystem(.*?)\n!is',strip_tags($info),$ma);
    //Check if it consists 'windows' as string
    preg_match('/[Ww]indows/',$ma[1],$os);
    if($os[0]=='' && $os[0]==null ) {
        $thisBoxRunsWindows= false;
    } else {
        $thisBoxRunsWindows = true;
    }
    return $thisBoxRunsWindows;
}

?>