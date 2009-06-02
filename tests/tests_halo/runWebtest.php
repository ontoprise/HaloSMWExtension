<?php
/**
 * 
 * Run webtests and reading the current wiki configuration.
 * The following porperties will be passed to the build file:
 * wgServer, wgScriptPath, wgSitename (with appropriate Wiki values)
 * and webtest.home with path to webtest.
 * This makes it flexible to run the tests on any server setup.
 * 
 * To run this script you must specify the test directory, for which
 * you want to start the webtests. Within this directory, a makeWebtests.xml
 * is expected which is the build file. Optional you can tell the script
 * where you installed the Canoo webtest.
 * 
 * Usage: runWebtests.php -t <dir with makeWebtests.xml> [-x <dir of webtest installation>]
 * 
 */

$USAGE= "Usage: runWebtests.php -t <dir with makeWebtests.xml> [-x <dir of webtest installation>]";

$mw_dir = dirname(__FILE__) . '/../../';

require_once('testFunctionsCmd.php');

# Process command line arguments
# Parse arguments

echo "Parse arguments...";

list($testDir, $webtestDir) = parseCmdParams('-t', '-x');

if ($testDir == null) {
    echo "\nTestdir missing. Use -t to set the extension's test directory.\n$USAGE\n";
    die();
}
$buildTarget = isWindows() ? $testDir.'\makeWebtests.xml' : $testDir.'/makeWebtests.xml';
if (!file_exists($buildTarget)) {
    echo "\nmakeWebtests.xml in Testdir not found. Make sure that there is a build file for webtests existing.\n$USAGE\n";
    die();
}

if ($webtestDir == null) {
    $webtestDir = getWebtestdir();
    if ($webtestDir == "") {
        echo "\nInstall location of Canoo Webtest could not be determinded.\n".
             "Please set complete path to webtest installation directory.\n$USAGE\n";
        die();
    }
}


if (preg_match('/sh|bat$/i', $webtestDir)) {
  $tp = strrpos($webtestDir, isWindows() ? '\\' : '/');
  $webtestDir = substr($webtestDir, 0, $tp);
}
if (preg_match('@bin(\\|/)?$@i', $webtestDir))
  $webtestBaseDir = substr($webtestDir, 0, -4);
else $webtestBaseDir = $webtestDir;

if (isWindows()) {
    $webtestExec = $webtestBaseDir.'\bin\webtest.bat';
    $webtestXML = $webtestBaseDir.'\webtest.xml';
}
else {
    $webtestExec = $webtestBaseDir.'/bin/webtest.sh';
    $webtestXML = $webtestBaseDir."/webtest.xml";
}
if (!file_exists($webtestExec) || !file_exists($webtestXML)) {
    echo "The provided location of the Canoo Webtest at $webtestBaseDir seems to be invalid\n$USAGE\n";
    die();
}

require_once( dirname(__FILE__) . '/../../maintenance/commandLine.inc' );

$cmd = "$webtestExec -f $buildTarget -Dwebtest.home=\"$webtestBaseDir\" -DwgSitename=\"$wgSitename\" -DwgServer=\"$wgServer\" -DwgScriptPath=\"$wgScriptPath\"";
echo "\nexecute Webtests:\n$cmd\n";
runProcess($cmd);

function getWebtestdir() {
    if (isWindows()) {
        $out = runProcess("echo %PATH%");
        $out = trim($out);
        $paths = explode(';', $out);
        foreach ($paths as $path) {
            if (preg_match('/webtest/i', $path))
                return $path;
        }
    }
    else
        return exec('which webtest');
}


?>