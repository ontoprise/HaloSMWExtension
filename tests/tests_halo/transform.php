<?php
/**
 * Transforms a PHPUnit XML test result to into a Hudson compatible version.
 *
 * Usage:
 *
 *  php Transform.php -i <input file> -o|-O <output file> [ -p <package name> ]
 *                    [ -t xsl stylesheet ]
 *
 *
 * @author Kai K�hn
 *
 */
$stylesheet = dirname(__FILE__)."/transform.xslt";
$appendOutput = false;
$params = array();

// get command line parameters
$args = $_SERVER['argv'];
array_shift($args); // remove script name
for( $arg = reset( $args ); $arg !== false; $arg = next( $args ) ) {
	//-i => input file
	if ($arg == '-i') {
		$inputFile = next($args);
		continue;
	}
	// -o => output file
	if ($arg == '-o') {
		$outputFile = next($args);
		continue;
	}
    // -O => append to output file
	if ($arg == '-O') {
		$outputFile = next($args);
        $appendOutput = true;
		continue;
	}
	// -p => package name
	if ($arg == '-p') {
		$package = next($args);
		continue;
	}
    // -t => xslt stylesheet
	if ($arg == '-t') {
		$stylesheet = next($args);
		continue;
	}
	$params[] = $arg;
}


$xp = new XsltProcessor;
$xp->setParameter("", "package", isset($package) ? $package : "GeneralTests");
$xsl = new DOMDocument;
$xsl->load($stylesheet);
$xp->importStylesheet($xsl);

$xml = new DOMDocument;
echo "\nReading testcases from $inputFile ...";
$xml->load($inputFile);

echo "\nTransforming...";
$output = $xp->transformToXML($xml)
or die('Transformation error!');

$mode = ($appendOutput) ? "ab" : "wb";
$handle = fopen($outputFile, $mode);
echo "\nWriting in output file: ".$outputFile;
fwrite($handle, $output);
fclose($handle);
echo "\ndone.\n";

?>