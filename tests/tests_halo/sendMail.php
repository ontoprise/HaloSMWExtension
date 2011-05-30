<?php

/* Send mail to someone. The only required parameter is -t to specify the
 * recipient.
 * The message will not be send if both body and subject are not set.
 * By default the mail command of PHP is used. This assumes that a local SMTP
 * server is running on the machine and that php is properly configured in PHP.
 * To configure php set the appropriate settings in the php.ini or by using the
 * following commands in your script:
 *   ini_set("SMTP","smtp.example.com" );
 *   ini_set('sendmail_from', 'user@example.com')
 * You may also use a different SMTP server. In this case set the options
 * -S -U and -P. Then the Pear Mail class is used in favor of the mail command.
 *
 * Usage:
 *
 *  php sendMail.php -t <recipient email address>
 *                   [ -s <subject> ]
 *                   [ -m <mail text> | -f <file with mail text> ]
 *                   [ -F <from adress> ]
 *                   [ -S <smtp server[:port]> (default port is 587)]
 *                   [ -U <smtp user> ]
 *                   [ -P <smtp password> ]
 *
 * Note: Windows user should not use single quotes to encapsulate strings
 * because these single quotes remain in the string. Solution: use double quotes.
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
    else if ($arg == '-F')
        $from = array_shift($args) or die ("Error: missing value for -F\n");
    else if ($arg == '-S') {
        $smtp= array_shift($args) or die ("Error: missing value for -S\n");
        if (preg_match('/:(\d+)$/', $smtp, $matches)) {
           $host = str_replace(':'.$matches[1], '', $smtp);
           $port = $matches[1];
        }
        else {
            $host = $smtp;
            $port = "587";
        }
    }
    else if ($arg == '-U') {
        $username= array_shift($args) or die ("Error: missing value for -U\n");
    }
    else if ($arg == '-P') {
        $password= array_shift($args) or die ("Error: missing value for -P\n");
    }
}
if (!isset($to)) die ("Error: no recipient given whom to send mail to\n");
if (!isset($subject)) $subject = "";
if (!isset($message)) $message = "";
if (strlen($message) == 0 && strlen($subject) == 0)
    die ("Error: empty subject and mail body\n");

// set from address to some default
if (! isset($from)) $from = "Hudson Buildserver <robotta@ontoprise.de>";

// send mail using the php mail command when we do not have smtp settings defined
if (! isset($smtp)) {
    $headers = 'From: '.trim($from). "\r\n";
    mail($to, $subject, $message, $headers);
    exit(0);
}

// smtp server config set, then use the Pear Mail package
require_once "Mail.php";

$headers = array ('From' => $from,
   'To' => $to,
   'Subject' => $subject);
$smtp = Mail::factory('smtp',
   array ('host' => $host,
     'port' => $port,
     'auth' => true,
     'username' => $username,
     'password' => $password));

$mail = $smtp->send($to, $headers, $message);

if (PEAR::isError($mail))
   echo("\n" . $mail->getMessage() );
else
   echo("\nMessage successfully sent!\n");
