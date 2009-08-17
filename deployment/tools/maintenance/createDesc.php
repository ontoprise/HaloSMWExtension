<?php
/**
 * Creates deploy descriptor skeletton.
 *
 * @author: Kai Kühn / ontoprise / 2009
 *
 */
if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
	echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
	return;
}


// get parameters
for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {

	//-d => directory
	if ($arg == '-d') {
		$dir = next($argv);
		$dir = str_replace("\\","/", $dir);
		continue;
	}
	$params[] = $arg;
}

if (!isset($dir)) {
	print "\nUsage: php createDesc.php -d <directory of package>\n";
	die();
}



print "\nID: ";
$id = trim(fgets(STDIN));

print "Version: ";
$version = trim(fgets(STDIN));

print "Installation directory: ";
$instdir = trim(fgets(STDIN));

print "Vendor: ";
$vendor = trim(fgets(STDIN));

// calculate code hashes
print "\nCalculating hashes...";
if (strpos(trim($dir), -1) != '/') $dir = trim($dir)."/";
$filesAndHashes = array();
$hashes="";
iterate_dir($dir, $hashes);

// global header
$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n";
$xml .= '<depoydescriptor>'."\n";
$xml .= getDDGlobals($version, $id, $instdir, $vendor);

$xml .= "\t".'<codefiles hash="'.md5($hashes).'">'."\n";

$files = get_files($dir);

foreach($files as $file) {
	$file = substr($file, strlen($dir)+1);
	$xml .= "\t\t".'<file loc="'.$file.'"/>'."\n";
}
$xml .= "\t".'</codefiles>'."\n";
$xml .= "\t".'<wikidumps>'."\n";
$xml .= "\t\t".'<file loc="..."/>'."\n";
$xml .= "\t".'</wikidumps>'."\n";
$xml .= "\t".'<resources>'."\n";
$xml .= "\t\t".'<file loc="..."/>'."\n";
$xml .= "\t".'</resources>'."\n";
$xml .= "\t".'<configs>'."\n";
$xml .= "\t\t".'<new>'."\n";
$xml .= "\t\t".'</new>'."\n";
$xml .= "\t\t".'<update from="...">'."\n";
$xml .= "\t\t".'</update>'."\n";
$xml .= "\t\t".'<uninstall>'."\n";
$xml .= "\t\t".'</uninstall>'."\n";
$xml .= "\t".'</configs>'."\n";
$xml .= '</depoydescriptor>'."\n";

// write descriptor
$handle = fopen("deploy.xml", "w");
fwrite($handle, $xml);
fclose($handle);

print "\n\nCreation successful!\n";

function iterate_dir($current_dir, & $hashes) {
	if (strpos(trim($current_dir), -1) != '/') $current_dir = trim($current_dir)."/";
	if($dir = @opendir($current_dir)) {
		while (($f = readdir($dir)) !== false) {
			if($f > '0' and filetype($current_dir.$f) == "file") {
				if (strpos($f, ".svn") !== false) continue;
				$content = file_get_contents($current_dir.$f);
				$hashes .= md5($content);

			} elseif($f > '0' and filetype($current_dir.$f) == "dir") {
				iterate_dir($current_dir.$f);
			}
		}
		closedir($dir);

	}
}

function get_files($current_dir) {
	$files = array();
	if (strpos(trim($current_dir), -1) != '/') $current_dir = trim($current_dir)."/";

	if($dir = @opendir($current_dir)) {
		while (($f = readdir($dir)) !== false) {
			if($f > '0' and filetype($current_dir.$f) == "file") {
				$files[] = $current_dir.$f;

			} elseif($f > '0' and filetype($current_dir.$f) == "dir") {
				$files[] = $current_dir.$f;
			}
		}
	}
	return $files;
}

function getDDGlobals($version, $id, $instdir, $vendor) {
	$xml = '<depoydescriptor>'."\n";
	$xml .= "\t".'<global>'."\n";
	$xml .= "\t\t".'<version>'.$version.'</version>'."\n";
	$xml .= "\t\t".'<id>'.$id.'</id>'."\n";
	$xml .= "\t\t".'<instdir>'.$instdir.'</instdir>'."\n";
	$xml .= "\t\t".'<vendor>'.$vendor.'</vendor>'."\n";
	$xml .= "\t\t".'<description>...</description>'."\n";
	$xml .= "\t\t".'<dependencies>'."\n";
	$xml .= "\t\t\t".'<dependency from="xxx" to="xxx">...</dependency>'."\n";
	$xml .= "\t".'</dependencies>'."\n";
	$xml .= "\t".'</global>'."\n";
    return $xml;
}
?>