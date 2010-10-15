<?php

/* Send mail to someone
 *
 * Usage:
 *
 *  php sendMail.php -t <recipient email address> [ -s <subject> ]
 *                   [ -m <mail text> | -f <file with mail text> ]
 *
 */

// get command line parameters
$args = $_SERVER['argv'];
while ($arg = array_shift($args)) {
    if ($arg == '-t')
        $to = array_shift($args) or die ("Error: missing value for -t\n");
    else if ($arg == '-s')
        $subject = array_shift($args) or die ("Error: missing value for -s\n");
    else if ($arg == '-m')
        $message = array_shift($args) or die ("Error: missing value for -m\n");
    else if ($arg == '-f') {
        $filename = array_shift($args) or die ("Error: missing value for -f\n");
        if (!file_exists($filename)) die ("Error: file doesn't exist\n");
        $message = file_get_contents($filename);
    }
}
if (!isset($to)) die ("Error: no recipient given whom to send mail to\n");
if (!isset($subject)) $subject = "";
if (!isset($message)) $message = "";
if (strlen($message) == 0 && strlen($subject) == 0)
    die ("Error: empty subject and mail body\n");

$headers = 'From: Hudson Buildserver <robotta@ontoprise.de>';

mail($to, $subject, $message, $headers);

