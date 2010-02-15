<?
// script that is installed on the SMW Forum to receive all requests and
// forward these as mails, enter in Bugzilla or create a new Comment wikipage
// The script doesn't return any useful data yet. The caller doesn't check
// whether the submit was successful or not. However the result output of
// this script (can be seen in any debug tool) gives information wether the
// request was handled successful or not.
// Logging is not yet done but would be useful to do so.

// SMW forum API
define('SMW_FORUM_API', 'http://smwforum.ontoprise.com/smwforum/api.php');
// Recipient email address where to send general requests for a component or own question  
define('SMW_EMAIL_ADDRESS', 'smw_support@ontoprise.de');
// cookies dir where to store temporary cookies that are needed by the login process
// into the SMW forum or bugzilla and also where the log file is located
define('COOKIES_DIR', 'commentdata');
// Bugzilla URL
define('BUGZILLA_URL', 'http://smwforum.ontoprise.com/smwbugs');
// log file -> in same diretory as cookies
define('LOG_FILE', 'comment.log');

// check if there are any POST parameter, if not print 1 and quit
if (!isset($_POST) || count($_POST) == 0) { echo '1'; exit(); }

// check the for required parameters, that must exist in every call
$user=(in_array('user', array_keys($_POST))) ? $_POST['user'] : "";
$pass=(in_array('pass', array_keys($_POST))) ? $_POST['pass'] : "";
$action=(in_array('action', array_keys($_POST))) ? $_POST['action'] : "";
$text=(in_array('text', array_keys($_POST))) ? $_POST['text'] : "";
if (!$user || !$pass || !$action || !$text) { echo '1'; exit(); }

// depending on the action parameter do the following.
if ($action == "c" || $action == "q") {
    sendMailToSmw($action, $text);
}
else if ($action == "b") {
    sendBugReport($user, $pass, $text);
}
else if ($action == "r") {
    sendRating($user, $pass, $text);
}
else {
    echo '1';
}


function sendMailToSmw($action, $text) {
    if ($action == "c") 
        $subject = "Feedback for a component";
    else
        $subject = "User has a question";
    $text = substr($text, strpos($text, '|')+1);
    $text = substr($text, 0, -2);
    $comment = substr($text, strpos($text, 'CommentContent=') +15);
    $comment = substr($comment, 0, strpos($comment, '|'));  
    $comment = urldecode($comment);
    $text = str_replace('|', "\n", $text);
    $text.="\n\n\n".$comment;
    $res= mail(SMW_EMAIL_ADDRESS, $subject, $text);
    $logtext = "Mail send: ".(($res) ? 'yes' : 'no');
    $logres = (($res) ? '0' : '1');
    logLine($subject, $logtext, $logres);
    echo $logres;
    exit();
}

function sendBugReport($user, $pass, $text) {
    $args = explode('&', $text);
    if (count($args) == 0) { echo '1'; die; }
    $text = '';
    for ($i=0; $i< count($args); $i++) {
        $p = strpos($args[$i], '=');
        if ($p === false) {
            $text.= $args[$i]."&";
            continue;
        }
        $text.= substr($args[$i], 0, $p)."=".urlencode(urldecode(substr($args[$i], $p+1)))."&";
    }
    $cookiefile = COOKIES_DIR.'/cookies.txt.'.date('YmdHis', time());
    $cc = new cURL(true, $cookiefile);

    $cc->post(BUGZILLA_URL.'/index.cgi', 'Bugzilla_login='.urlencode($user).'&Bugzilla_password='.urlencode($pass).'&Bugzilla_restrictlogin=1&GoAheadAndLogIn=Login');

    $res= $cc->post(BUGZILLA_URL.'/post_bug.cgi', $text);
    if (preg_match('/<title>(.*?)<\/title>/', $res, $matches)) {
        $logres = "0";
        $logtext= $matches[1];
    }
    else {
        $logtext= "Error while submitting bug";
        $logres= "1";
    }
    logLine('send bug report', $logtext, $logres);
    echo $logres;
    @unlink($cookiefile);

}
function sendRating($user, $pass, $text) {
    $newpage='Comment%3A'.date('YmdHis', time());
    $cookiefile = COOKIES_DIR.'/cookies.txt.'.date('YmdHis', time());
    $logtext="New page: $newpage  - ";
    $logres="0";
    $cc = new cURL(true, $cookiefile);
    $cc->post(SMW_FORUM_API, "action=login&lgname=".urlencode($user)."&lgpassword=".urlencode($pass)."&lgdomain=smwforum&format=xml");
    $editToken = $cc->post(SMW_FORUM_API, "action=query&prop=info|revisions&intoken=edit&titles=".$newpage."&format=xml");
    $editToken = substr($editToken, strpos($editToken, "<?xml"));

    $domDocument = new DOMDocument();

    $cookies = array();
    $success = $domDocument->loadXML($editToken);
    $domXPath = new DOMXPath($domDocument);

    $nodes = $domXPath->query('//page/@edittoken');
    $et = "";
    foreach ($nodes AS $node) {
        $et = $node->nodeValue;
    }
    $et = urlencode($et);
    if (strlen($et) > 0 ) {
        $logtext.="Edit token: ".$et;
        $res = $cc->post(SMW_FORUM_API, "action=edit&title=".$newpage."&createonly=1&text=".urlencode($text)."&token=".$et."&format=xml");
        $domDocument->loadXML($res);
        $domXPath = new DOMXPath($domDocument);
        $nodes = $domXPath->query('//error/@code');
        $ec = "";
        foreach ($nodes AS $node) {
            $ec = $node->nodeValue;
        }
        if (strlen($ec) > 0) {
            $logtext.= " - Error $ec";
            $logres = '1';
        }
    }
    else {
        $logtext.= "Error: no edit token";
        $logres= '1';
    }
    logLine('send rating', $logtext, $logres);
    echo $logres;

    @unlink($cookiefile);
}

function logLine($action, $text, $result) {
    $text = str_replace("\n", "<br> ", $text);
    $line = date("Y-m-d H:i:s", time()).'|'.$_SERVER['REMOTE_ADDR']."|$result|$action|$text|\n";
    $cnt = 3;
    while ($cnt > 0) {
        $lock = fopen(COOKIES_DIR.'/'.LOG_FILE.'.lck', "x");
        if ($lock) {
            $fp = fopen(COOKIES_DIR.'/'.LOG_FILE, "a+");
            if ($fp) {
                fputs($fp, $line, strlen($line));
                fclose($fp);
            }
            fclose($lock);
            @unlink(COOKIES_DIR.'/'.LOG_FILE.'.lck');
            return;
        }
        usleep(5000); // half second
        $cnt--;
    }
}

// class with CURL that makes also handling with cookies easy.
class cURL {
    var $headers;
    var $user_agent;
    var $compression;
    var $cookie_file;
    var $proxy;
    function cURL($cookies=TRUE,$cookie='cookies.txt',$compression='gzip',$proxy='') {
        $this->headers[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg';
        $this->headers[] = 'Connection: Keep-Alive';
        $this->headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
        $this->user_agent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)';
        $this->compression=$compression;
        $this->proxy=$proxy;
        $this->cookies=$cookies;
        if ($this->cookies == TRUE) $this->cookie($cookie);
    }
    function cookie($cookie_file) {
        if (file_exists($cookie_file)) {
            $this->cookie_file=$cookie_file;
        } else {
            $fp= fopen($cookie_file,'w') or $this->error('The cookie file could not be opened. Make sure this directory has the correct permissions');
            $this->cookie_file=$cookie_file;
            fclose($fp);
        }
    }
    function get($url) {
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
        if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
        if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($process,CURLOPT_ENCODING , $this->compression);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        if ($this->proxy) curl_setopt($cUrl, CURLOPT_PROXY, 'proxy_ip:proxy_port');
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
        $return = curl_exec($process);
        curl_close($process);
        return $return;
    }
    function post($url,$data) {
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($process, CURLOPT_HEADER, 1);
        curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
        if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
        if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($process, CURLOPT_ENCODING , $this->compression);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        if ($this->proxy) curl_setopt($process, CURLOPT_PROXY, $this->proxy);
        curl_setopt($process, CURLOPT_POSTFIELDS, $data);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($process, CURLOPT_POST, 1);
        $return = curl_exec($process);
        curl_close($process);
        return $return;
    }
    function error($error) {
        echo "1 cURL Error $error";
        die;
    }
}
