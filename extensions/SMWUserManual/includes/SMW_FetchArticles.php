<?php
/* 
 * This file is part of the SMW User Manual Extension
 */


class UME_FetchArticles {

    // int error code which is 0 when no error occured
    static $error= 0;

    const ERR_NO_CONNECT = -2;
    const ERR_NO_HTTPS = -3;

    const PROPERTY_DISCOURSE_STATE_TITLE = 'UME discourse state';
    const PROPERTY_DISCOURSE_STATE_TEXT = '[[Has domain and range::Page| ]][[Has type::Type:String| ]]';
    const PROPERTY_LINK_TITLE = 'UME link';
    const PROPERTY_LINK_TEXT = '[[Has domain and range::Page| ]][[Has type::Type:URL| ]]';

    public function installPages() {
        self::$error = 0;
        $pages = self::getPageList();
        //$pages = array('Help:How can I annotate a property', 'Help:What external tools can be used with SMW+?');
        $data = self::getPages($pages);
        //var_dump($data);
        while ($o = array_shift($data)) {
            $newTitle = substr($o->getTitle(), 5);
            $text = $o->getContent().
                "\n[[UME discourse state::".$o->getDiscourseState()."| ]]".
                "\n[[UME link::".$o->getLink()."| ]]";
            self::createPage(SMW_NS_USER_MANUAL, $newTitle, $text);
        }
    }

    public function installProperties() {
        self::$error = 0;
        self::createPage(SMW_NS_PROPERTY,
            self::PROPERTY_DISCOURSE_STATE_TITLE,
            self::PROPERTY_DISCOURSE_STATE_TEXT
        );
        self::createPage(SMW_NS_PROPERTY,
            self::PROPERTY_LINK_TITLE,
            self::PROPERTY_LINK_TEXT
        );
    }

    private function createPage($ns, $title, $text) {
        global $wgLang;
        echo sprintf(wfMsg('smw_ume_create_page'),
                     $wgLang->getNsText($ns).':'.$title).'  ';
        $t = Title::makeTitle($ns, $title);
        if ($t->exists()) {
            echo wfMsg('smw_ume_warning_page')."\n";
            return;
        }
        $a = new Article($t);
        $a->doEdit($text,"", EDIT_NEW);
        echo wfMsg('smw_ume_done')."\n";
    }

    private function getPageList() {
        $params = 'action=ajax&'.
            'rs=smwf_qi_QIAccess&'.
            'rsargs[]=getQueryResult&'.
            'rsargs[]='.urlencode(SMW_FORUM_QUERY_CSH.',list,none,,,250,,ascending,,show');
        $pageList = self::callWiki(SMW_FORUM_URL, $params);
        if (self::$error != 0) {
            echo wfMsg('smw_ume_no_article_list')."\n";
            if (self::$error > 0) echo "HTTP";
            echo wfMsg('smw_ume_error_code').' '.self::$error."\n";
            exit(self::$error);
        }
        $pageList = strip_tags($pageList);
        $pages = explode(',', $pageList);
        $is = count($pages);
        // remove the "' and' Help:..." from the last element
        $pages[$is - 1] = substr($pages[$is - 1], 4);
        // trim whitspaces from page names
        for ($i = 0; $i < $is; $i++) {
            $pages[$i] = trim($pages[$i]);
            if (substr($pages[$i], 0, 5) != 'Help:')
                unset($pages[$i]);
        }
        return $pages;
    }

    private function getPages($pageList) {
        $params = array(
            'action' => 'query',
            'titles' => implode('|', $pageList),
            'prop'   => 'revisions',
            'rvprop' => 'content',
            'format' => 'php'
        );
        $pageData = self::callWiki(SMW_FORUM_API, $params);
        $pageList = unserialize(trim($pageData));
        $myList = array();

        if (isset($pageList['query']['pages']))
            $pages = &$pageList['query']['pages'];
        if (is_array($pages)) {
            while ($page = array_shift($pages)) {
                $po = new UME_CshArticle();
                $po->initByArray($page);
                $myList[] = $po;
            }
        }
        return $myList;
    }

    private function callWiki($server, $params) {
        // remove the "http://" protocol from host name
        $host = substr($server, strpos($server, ':') + 3);
        // split server and path at the first / after the "http://"
        $p = strpos($host, '/');
        $path = substr($host, $p);
        $host = substr($host, 0, $p);
        // if the server has a port, a : is in the string
        $p = strpos($host, ':');
        if ( $p !== false) {
            $port = substr($host, $p);
            $host = substr($host, 0, $p);
        }
        // standard http(s) ports
        else if (strtolower(substr($server, 0, 5)) == 'https')
            $port = 443;
        else
            $port = 80;
        // open socket now
        $api = fsockopen($host, $port, $errno, $errstr);
        if (!$api) {
            self::$error = self::ERR_NO_CONNECT;
            return;
        }
        // convert params array to string, if it's an array
        if (is_array($params)) {
            $getparams = "";
            foreach ($params as $key => $val) {
                $getparams .= $key.'='.urlencode($val).'&';
            }
            $params = rtrim($getparams, '&');
        }
        // formulate a POST request and send it to the server
        $com = "POST $path HTTP/1.1\r\n".
           "Accept: */*\r\n".
           "Content-Type: application/x-www-form-urlencoded\r\n".
           "Content-Length: ".strlen($params)."\r\n".
           "User-Agent: UserManualExtension ".SMW_USER_MANUAL_VERSION.", php ".phpversion()." on ".php_uname('s')." ".php_uname('r')."\r\n".
           "Host: $host:$port\r\n".
           "\r\n".
           "$params\r\n";
        fputs($api, $com);
        $cont = '';
        while (!feof($api)) {
            $cont .= fgets($api, 4096);
        }
        fclose($api);
        $httpHeaders= explode("\r\n", substr($cont, 0, strpos($cont, "\r\n\r\n")));
        list($protocol, $httpErr, $message) = explode(' ', $httpHeaders[0]);
        $offset = 0;
        $cont = substr($cont, strpos($cont, "\r\n\r\n") + $offset );
        if ($httpErr == '200') self::$error = 0;
        else self::$error = intval($httpErr);
        return $cont;
    }
}
?>
