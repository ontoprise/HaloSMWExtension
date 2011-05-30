<?

/* Check for *.rej files that are created when patches fail
 *
 * Usage: checkForFailedPatches.php [ -d <directory> ]
 *
 * -d may contain the path where the search is started.
 */

$dir= ".";
$args = $_SERVER['argv'];
while ($arg = array_shift($args)) {
    if ($arg == '-d')
        $dir = array_shift($args) or die ("Error: missing value for -d\n");
}

if (substr($dir, -1) == '/' || substr($dir, -1) == '\\')
   $dir = substr($dir, 0, -1);

$dirList= array($dir);
    
while ($dir= array_shift($dirList)) {
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
            if ($file == "." || $file == "..")
                continue;
            else if (is_dir($dir.'/'.$file)) {
                $dirList[]= $dir.'/'.$file;
                continue;
            }
            else if (preg_match('/.rej$/', $file))
                echo $dir.'/'."$file\r\n";
        }
        closedir($handle);
    }
}