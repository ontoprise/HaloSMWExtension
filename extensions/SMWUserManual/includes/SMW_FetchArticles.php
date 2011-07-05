<?php
/**
 * @file
 * @ingroup SMWUserManual
 */

/**
 * Class to access the SMW forum to fetch all current CSH articles so that these
 * can be installed in the local wiki
 *
 * @ingroup SMWUserManual
 */
class UME_FetchArticles {

    // int error code which is 0 when no error occured
    static $error= 0;
    static $overwrite=false;
    static $export=false;
    static $import=false;
    static $fp=null;

    const ERR_NO_CONNECT = -2;
    const ERR_NO_HTTPS = -3;
    
    const LIMIT_TITLES4API = 20; // how many titles to fetch in one call from the api

    const PROPERTY_DISCOURSE_STATE_TITLE = SMW_UME_PROPERTY_DISCOURSE_STATE;
    const PROPERTY_LINK_TITLE = SMW_UME_PROPERTY_LINK;
    const SMW_TEMPLATE_PREFIX = SMW_TEMPLATE_PREFIX;
    
    public static function installPages($overwrite) {
        self::$error = 0;
        self::$overwrite = $overwrite;
        $pages = self::getPageList();
        while (count($pages) > 0) {
            $chunk = array();
            while (count($pages) > 0 && count($chunk) < self::LIMIT_TITLES4API) {
                $chunk[]= array_shift($pages);
            }
            $data = self::getPages($chunk);
            while ($o = array_shift($data)) {
                $newTitle = preg_replace('/^Help:/', '', $o->getTitle());
                $text = $o->getContent().
                    self::makeDiscourseStatesProperty($o->getDiscourseState()).
                    "\n[[UME link::".$o->getLink()."| ]]\n".
                    "[[Rationale::This is an help article.| ]]\n".
                    "{{Content hash|value=}}\n".
                    "{{Part of bundle|value=Smwusermanual}}\n";
                self::createPage(SMW_NS_USER_MANUAL, $newTitle, $text);
            }
        }
    }
    
    public static function exportPages($file, $overwrite) {
        if (!$overwrite && file_exists($file)) {
            echo wfMsg('smw_ume_export_fexists')."\n";
            return;
        }
        self::$fp = fopen ($file, "w");
        if (!self::$fp) {
             echo wfMsg('smw_ume_nofopen')."\n";
             return;
        }
        self::$export = true;
        fputs(self::$fp, '<?xml version="1.0" encoding="UTF-8"?>'."\n<!-- SMW+ UserManual help pages exported on: ".date('Y-m-d H:i:s')."-->\n<pages>\n");
        self::installPages(true);
        fputs(self::$fp, "</pages>");
        fclose(self::$fp);
    }
    
    public static function importPages($file, $overwrite) {
        if (!file_exists($file)) {
            echo wfMsg('smw_ume_import_fmissing')."\n";
            return;
        }
        self::$overwrite = $overwrite;
        $doc = new DOMDocument();
        $doc->load($file);
        $pages = $doc->getElementsByTagName('page');
        foreach ($pages as $page) {
            $title = $page->getAttribute('title');
            $ns = $page->getAttribute('ns');
            $text = $page->nodeValue;
            // namespace main means User Manual namespace, others
            // like SMW Property or Template are the same in each wiki
            if ($ns == 0) $ns = SMW_NS_USER_MANUAL;
            $text= htmlspecialchars_decode($text);
            $title= htmlspecialchars_decode($title);
            self::createPage($ns, $title, $text);
        }
    }

    public static function deletePages() {
        self::$error = 0;
        $db =& wfGetDB(DB_SLAVE);
        $page = $db->tableName('page');
        $query= "select page_title from $page where page_namespace = ".SMW_NS_USER_MANUAL;
        $res = $db->query($query);
        if ($res && $db->numRows($res) > 0) {
            while ($row = $db->fetchObject($res)) {
                // echo 'delete page: '.$row->page_title."\n";
                self::deletePage(SMW_NS_USER_MANUAL, $row->page_title);
            }
        }
    }
    
    private static function createPage($ns, $title, $text) {
        global $wgLang;
        if (in_array($title, explode(',', SMW_SKIP_CSH_ARTICLE))) return;
        echo sprintf(wfMsg('smw_ume_create_page'),
                     $wgLang->getNsText($ns).':'.$title).'  ';
        // not writing to local wiki, but into a file
        if (self::$export) {
            // Namespace for csh articles will be exported as 0 and
            // redefined on import in local wiki.
            if ($ns == SMW_NS_USER_MANUAL) $ns = 0;
            $text= htmlspecialchars($text);
            $title=htmlspecialchars($title);
            $page= '<page ns="'.$ns.'" title="'.$title.'">'.$text."</page>\n";
            fputs(self::$fp, $page, strlen($page));
            echo wfMsg('smw_ume_done')."\n";
            return;
        }
        $t = Title::makeTitle($ns, $title);
        global $wgTitle;
        $wgTitle= $t;
        if ($t->exists()){
            if (!self::$overwrite) {
                echo wfMsg('smw_ume_warning_page')."\n";
                return;
            }
            echo wfMsg('smw_ume_overwrite_page').' ';
        }
        $a = new Article($t);
        $a->doEdit($text,"", EDIT_NEW);
        echo wfMsg('smw_ume_done')."\n";
    }
    
    private static function deletePage($ns, $title) {
        global $wgLang;
        $t = Title::makeTitle($ns, $title);
        if ($t->exists()){
            echo sprintf(wfMsg('smw_ume_delete_page'),
                $wgLang->getNsText($ns).':'.$title).'  ';
            $a = new Article($t);
            if ($a->doDeleteArticle(wfMsg('smw_ume_deinstall')))
                echo wfMsg('smw_ume_done')."\n";
            else
                echo wfMsg('smw_ume_failed')."\n";
        }
    }
    
    private static function getPageList() {
        $params = 'action=ajax&'.
            'rs=smwf_qi_QIAccess&'.
            'rsargs[]=getQueryResult&'.
            'rsargs[]='.urlencode(SMW_FORUM_QUERY_CSH).
            '%2Creasoner%3Dask%7Cformat%3Dlist%7Clink%3Dnone%7Climit%3D500';
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
        }
        return $pages;
    }

    private static function getPages($pageList) {
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

    private static function callWiki($server, $params) {
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
    
    private static function makeDiscourseStatesProperty($dsStr) {
        $txt = "";
        $ds = explode(',', $dsStr);
        for ($i = 0; $i < count($ds); $i++)
           $txt .= "\n[[UME discourse state::".trim($ds[$i])."| ]]";
        return $txt;
    }
}
