<?php
/**
 * Applies a patch file. Files are denoted by relative paths, a directory
 * which make this paths absolute is provideded as parameter. As well as the
 * patch file itself.
 *
 *  Usage: php patch.php -d <wiki path> -p <patch file>
 *
 * Both are absolute paths
 *
 * @author: Kai Kühn / ontoprise / 2009
 */

for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {
	//-d => absolute path to extend relative
	if ($arg == '-d') {
		$absPath = next($argv);
		continue;
	}
	//-p => patch file
	if ($arg == '-p') {
		$patchFile = next($argv);
		continue;
	}

}

if (!isset($absPath) || !isset($patchFile)) {
	echo "\nUsage: php patch.php -d <wiki path> -p <patch file>\n";
	die();
}

// make platform independant paths
$absPath = trim(str_replace("\\","/", $absPath));
$patchFile = trim(str_replace("\\","/", $patchFile));
if (substr($absPath, -1) != '/') $absPath .= "/";

echo "\nRead patch file:\n $patchFile";
$patchFileContent = file_get_contents($patchFile);

// split patch file in array of single patches for each file.
$patches = $patchFileContent = preg_split('/Index:\s+(.+)[\n|\r\n]+=+[\n|\r\n]+/', $patchFileContent);

foreach($patches as $p) {
	if ($p == '') continue;

	// get (relative path of) file to patch
	preg_match('/\+\+\+\s+([^\s]+)/', $p, $matches);
	$path = dirname($matches[1]);
	 
	echo "\nApplying patch to:\n $matches[1]";
	if (isWindows()) {
		// make sure patch file is windows style
		$p = str_replace("\r\n","\n",$p);
		$p = str_replace("\n","\r\n",$p);
	} else {
		// make sure patch file is unix style
		$p = str_replace("\r\n","\n",$p);
	}

	echo "\nWrite patch file:\n $absPath$path/__patch__.txt";
	// write patch file
	$handle = fopen($absPath.$path.'/__patch__.txt', 'w');
	fwrite($handle, $p);
	fclose($handle);

	echo "\nExecute patch:\n ".'patch -u -l -s --no-backup-if-mismatch -i __patch__.txt -d "'.$absPath.$path.'"';
	// run patch
	exec('patch -u -l -s --no-backup-if-mismatch -i __patch__.txt -d "'.$absPath.$path.'"');

	echo "\nDelete patch file:\n ".$absPath.$path.'/__patch__.txt';
	// delete patch file
	unlink($absPath.$path.'/__patch__.txt');
	
	echo "\n\n------------";
}

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