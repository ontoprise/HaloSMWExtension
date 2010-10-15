<?php
/**
 * Transforms a PHPUnit XML test result to into a Hudson compatible version.
 *
 * Usage:
 *
 *  php Transform.php -i <input file> -o|-O <output file> [ -t xsl stylesheet ]
 *                    [ -D <key=value> ]
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
    // -t => xslt stylesheet
	if ($arg == '-t') {
		$stylesheet = next($args);
		continue;
	}
    // -D param=value
	if ($arg == '-D') {
		$keyVal = next($args);
        if (is_null($keyVal) || 
            strpos($keyVal, "=") === false ||
            strpos($keyVal, "=") == 0) continue;
        $key = substr($keyVal, 0, strpos($keyVal, "="));
        $val = substr($keyVal, strlen($key)+1);
        $params[$key] = $val;
	}

}

$xp = new XsltProcessor;
foreach (array_keys($params) as $key) {
    $xp->setParameter("", $key, $params[$key]);
}

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