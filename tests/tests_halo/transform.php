<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Transforms a PHPUnit XML test result to into a Hudson compatible version.
 *
 * Usage:
 *
 *  php Transform.php -i <input file> -o|-O <output file> [ -t xsl stylesheet ]
 *                    [ -D <key=value> ] [ -e <error_message> ]
 *
 * -i: xml file that is supposed to be transformed
 * -o: output file where the transformation is written to
 * -O: same as -o but if the file exists, the output if appended
 * -t: file with the xslt stylesheet, if none is given, transform.xslt in
 *     the current directory is used.
 * -D: key=value parameters that will be passed to the xslt stylesheet and
 *     that can be used in the transformation process.
 * -e: if the transformation fails, the output would be empty, no output
 *     file would be written. If an error message is provided, it will be
 *     written into the output file. 
 * @author Kai K�hn
 *
 */
$stylesheet = dirname(__FILE__)."/transform.xslt";
$appendOutput = false;
$params = array();
$errorMessage= "";

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
    // -e => error message
    if ($arg == '-e') {
        $errorMessage = next($args);
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

$output = $xp->transformToXML($xml);
if ($output == NULL) {
  if ( strlen($errorMessage) > 0 ) {
     $output = $errorMessage;
     echo "Transformation error!";
  }
  else die('Transformation error!');
}

$mode = ($appendOutput) ? "ab" : "wb";
$handle = fopen($outputFile, $mode);
echo "\nWriting in output file: ".$outputFile;
fwrite($handle, $output);
fclose($handle);
echo "\ndone.\n";

?>