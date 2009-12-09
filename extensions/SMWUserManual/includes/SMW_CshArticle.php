<?php
/*
 * This file is part of the SMW User Manual Extension
 */

class UME_CshArticle {

    private $title;
    private $content;
    private $discourseState;
    private $link;


    public function __construct() {
        $this->title = '';
        $this->content = '';
        $this->discourseState = '';
        $this->link = '';
    }

    public function getTitle() {
        return $this->title;
    }

    public function getContent() {
        return $this->content;
    }

    public function getDiscourseState() {
        return $this->discourseState;
    }

    public function getLink() {
        return $this->link;
    }

    public function initByArray($arr) {
        $this->title = $arr['title'];
        $this->content = $arr['revisions'][0]['*'];
        $this->processRawSmwforumContent();
    }

    private function processRawSmwforumContent() {
        $template = $this->extractTemplate('Context sensitive help article');
        if (isset($template['discourseState']))
            $this->discourseState = $template['discourseState'];
        if (isset($template['assigned to manual']))
            $this->link = $template['assigned to manual'];
        $template = $this->extractTemplate('Cite web in help', false);
        if ($this->link == "" || $this->link == "undefined")
            $this->link = $template['url'];
    }

    private function extractTemplate($tname, $modifyContent = true) {
        // the easy way, template name is prefixed by {{
        $p = strpos($this->content, '{{'.$tname);
        if ($p === false) {
            // it's also a valid temlate call when there are whitespaces
            // between the {{ and the template name, check this now.
            $trans = array('?' => '\?', '*' => '\*', '+' => '\+', '-' => '\-');
            $pattern = '/\{\{\s+'.strtr($tname, $trans).'/';
            if (preg_match($pattern, $this->content, $match))
                $p = strpos($this->content, $match[0]);
        }
        if ($p === false) return;
        $s = -1;
        $q = 0;
        $o = $p;
        // find the corresponding closing brakets for the template call
        while ($s && $s < $q) {
            $q = strpos($this->content, '}}', $o);
            $s = strpos($this->content, '{{', $o + 2);
            $o = $q;
        }
        // extract the complete template string
        $traw = substr($this->content, $p, $q - $p );
        // if we have to modify the content, the template string is removed
        if ($modifyContent) {
            $before = substr($this->content, 0, $p);
            $after = substr($this->content, $q + 2);
            $this->content = $before . $after;
        }
        // split the template call into it's parameters
        $tarr = explode('|', $traw);
        $template = array();
        while ($val = array_shift($tarr)) {
            $val = trim($val);
            $f = strpos($val, '=');
            if ($f !== false)
                $template[trim(substr($val, 0, $f))] = trim(substr($val, $f + 1));
            else
                $template[$val] = '';
        }
        return $template;
    }

}
