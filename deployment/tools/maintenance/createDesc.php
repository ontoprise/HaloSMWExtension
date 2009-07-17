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
iterate_dir($dir, $filesAndHashes);

// global header
$xml = '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n";
$xml .= '<depoydescriptor>'."\n";
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

$xml .= "\t".'<codefiles>'."\n";
$xml .= "\t\t".'<patch file="..."/>'."\n";
foreach($filesAndHashes as $f) {
    list($file, $hash) = $f;
    $file = substr($file, strlen($dir)+1);
    $xml .= "\t\t".'<file loc="'.$file.'" hash="'.$hash.'"/>'."\n";
}
$xml .= "\t".'</codefiles>'."\n";
$xml .= "\t".'<wikidumps>'."\n";
$xml .= "\t\t".'<file loc="..."/>'."\n";
$xml .= "\t".'</wikidumps>'."\n";
$xml .= "\t".'<resources>'."\n";
$xml .= "\t\t".'<file loc="..."/>'."\n";
$xml .= "\t\t".'<dir loc="..."/>'."\n";
$xml .= "\t".'</resources>'."\n";
$xml .= "\t".'<configs>'."\n";
$xml .= "\t\t".'<new>'."\n";
$xml .= "\t\t".'</new>'."\n";
$xml .= "\t\t".'<update from="...">'."\n";
$xml .= "\t\t".'</update>'."\n";
$xml .= "\t".'</configs>'."\n";
$xml .= '</depoydescriptor>'."\n";

// write descriptor
$handle = fopen("deploy.xml", "w");
fwrite($handle, $xml);
fclose($handle);

print "\n\nCreation successful!\n";

function iterate_dir($current_dir, & $filesAndHashes) {
	if (strpos(trim($current_dir), -1) != '/') $current_dir = trim($current_dir)."/";
	if($dir = @opendir($current_dir)) {
		while (($f = readdir($dir)) !== false) {
			if($f > '0' and filetype($current_dir.$f) == "file") {
				if (strpos($f, ".svn") !== false) continue;
				$content = file_get_contents($current_dir.$f);
				$hash = md5($content);
                $filesAndHashes[] = array($current_dir.$f, $hash);
			} elseif($f > '0' and filetype($current_dir.$f) == "dir") {
				iterate_dir($current_dir.$f);
			}
		}
		closedir($dir);
		
	}
}
?>