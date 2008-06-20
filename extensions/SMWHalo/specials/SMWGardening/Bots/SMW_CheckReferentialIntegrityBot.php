<?php
/*  Copyright 2008, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
 * Created on 21.05.2008
 *
 * Author: kai
 */

 global $smwgHaloIP;
 require_once("$smwgHaloIP/specials/SMWGardening/SMW_GardeningBot.php");
 require_once("$smwgHaloIP/specials/SMWGardening/SMW_ParameterObjects.php");

 
 class CheckReferentialIntegrityBot extends GardeningBot {
    
    function __construct() {
        parent::GardeningBot("smw_checkrefintegritybot");
        
    }
    
    public function getHelpText() {
        return wfMsg('smw_gard_checkrefint_docu');
    }
    
    public function getLabel() {
        return wfMsg($this->id);
    }
    
    public function allowedForUserGroups() {
        return array(SMW_GARD_GARDENERS, SMW_GARD_SYSOPS);
    }
    
    /**
     * Returns an array of GardeningParamObjects
     */
    public function createParameters() {
        return array();
    }
    
    /**
     * Check referential integrity
     * 
     * DO NOT use echo when it is not running asynchronously.
     */
    public function run($paramArray, $isAsync, $delay) {
        echo "Check referential integrity";
        $gi = SMWGardeningIssuesAccess::getGardeningIssuesAccess();
        
        // request $limit links successivly
        $limit = 200;
        $offset = 0;
        $this->setNumberOfTasks(1);
        $total_links_num = $this->getNumberOfExternalLinks();
        $this->addSubTask($total_links_num);
        print "\n";
        GardeningBot::printProgress(0);
        do {
            $results = $this->getExternalLinks($limit, $offset);
            foreach($results as $r) {
                list($pageID, $url) = $r;
                $title = Title::newFromID($pageID);
                $categories = smwfGetSemanticStore()->getCategoriesForInstance($title);
                // ignore GardeningLog pages
                if (count($categories) > 0 && $categories[0]->getText() == wfMsg('gardeninglog')) continue;
                
                // request header and add issue if necessary.
                $http_status = $this->getHTTPStatus($url);
                if ($http_status != "200") { // 200 == OK
                    switch($http_status) {
                        case "404" : $gi->addGardeningIssueAboutValue($this->id, SMW_GARDISSUE_RESOURCE_NOT_EXISTS, $title, $url); break;
                        case "301" : $gi->addGardeningIssueAboutValue($this->id, SMW_GARDISSUE_RESOURCE_MOVED_PERMANANTLY, $title, $url); break;
                        case "302" : $gi->addGardeningIssueAboutValue($this->id, SMW_GARDISSUE_RESOURCE_MOVED_PERMANANTLY, $title, $url); break;
                        //TODO: add other HTTP error codes
                        
                        // unknown HTTP error
                        default: $gi->addGardeningIssueAboutValue($this->id, SMW_GARDISSUE_RESOURCE_NOT_ACCESSIBLE, $title, $url); break;
                    }
                    
                }
            }
            GardeningBot::printProgress($offset/$total_links_num);
            $this->worked($limit);
            $offset += $limit;
            if ($this->isAborted()) break;
        } while (count($results) == $limit);
        GardeningBot::printProgress(1);
    }
    
    /**
     * Returns all all pages with their external links.
     *
     * @param int $limit
     * @param int $offset
     * @return array of (id, link)
     */
    private function getExternalLinks($limit, $offset) {
        $db = wfGetDB(DB_SLAVE);
        $results = array();
        
        $res = $db->select(array($db->tableName('externallinks')), array('el_from', 'el_to'), array(),"CheckReferentialIntegrityBot::getExternalLinks", array('LIMIT'=>$limit, 'OFFSET' => $offset));
        if($db->numRows( $res ) > 0) {
             while($row = $db->fetchObject($res)) {
                 $results[] = array($row->el_from, $row->el_to);
             }
        }
        $db->freeResult($res); 
        return $results;
    }
    
    /**
     * Returns total number of external links.
     *
     * @return int
     */
    private function getNumberOfExternalLinks() {
        $db = wfGetDB(DB_SLAVE);
        $results = array();
        $res = $db->query('SELECT COUNT(el_from) AS num FROM '.$db->tableName('externallinks'));
        if($db->numRows( $res ) > 0) {
            $row = $db->fetchObject($res);
            $db->freeResult($res); 
            return $row->num;
        }
    }
    
    /**
     * Requestst HTTP header of a resource and 
     * returns HTTP status code.
     *
     * @param string $url
     * @return int
     */
    private function getHTTPStatus($url) {
        //echo "Loading header: ".$url."\n";
        $head = @get_headers($url);
        
        if ($head == NULL) return -1;
        
        $matches = array();
        preg_match('/HTTP\/1\.[01]\s*(\d+)/', $head[0], $matches);
        
        return isset($matches[1]) ? $matches[1] : "-1"; 
    }
 }
 
  new CheckReferentialIntegrityBot();
  
define('SMW_REFERENTIALINTEGRITYBOT_BASE', 1200);
define('SMW_GARDISSUE_RESOURCE_NOT_EXISTS', SMW_REFERENTIALINTEGRITYBOT_BASE * 100 + 1);
define('SMW_GARDISSUE_RESOURCE_MOVED_PERMANANTLY', SMW_REFERENTIALINTEGRITYBOT_BASE * 100 + 2);
define('SMW_GARDISSUE_RESOURCE_NOT_ACCESSIBLE', SMW_REFERENTIALINTEGRITYBOT_BASE * 100 + 3);
 
 class CheckReferentialIntegrityBotFilter extends GardeningIssueFilter {
        
    public function __construct() {
        parent::__construct(SMW_REFERENTIALINTEGRITYBOT_BASE);
        $this->gi_issue_classes = array(wfMsg('smw_gardissue_class_all'));
    }
    
    public function getUserFilterControls($specialAttPage, $request) {
        return '';
    }
    
    public function linkUserParameters(& $wgRequest) {
        return array();
    }

    public function getData($options, $request) {
       return parent::getData($options, $request);
    }

    
 }
 
 
 class CheckReferentialIntegrityBotIssue extends GardeningIssue {
    
    public function __construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified) {
        parent::__construct($bot_id, $gi_type, $t1_ns, $t1, $t2_ns, $t2, $value, $isModified);
    }
    
    protected function getTextualRepresenation(& $skin,  $text1, $text2, $local = false) {
        $text1 = $local ? wfMsg('smw_gard_issue_local') : $text1;
             
        switch($this->gi_type) {
            case SMW_GARDISSUE_RESOURCE_NOT_EXISTS:
                return wfMsg('smw_gardissue_resource_not_exists', $this->value, $this->getLocalName($this->value));
            case SMW_GARDISSUE_RESOURCE_MOVED_PERMANANTLY:
                return wfMsg('smw_gardissue_resource_moved_permanantly', $this->value, $this->getLocalName($this->value));  
             case SMW_GARDISSUE_RESOURCE_NOT_ACCESSIBLE:
                return wfMsg('smw_gardissue_resource_not_accessible', $this->value, $this->getLocalName($this->value));     
            default: return NULL;
        }
        
    }
    
    /**
     * Returns localname from a URL. If localname is
     * empty it returns 'smw_gardissue_thisresource' message
     *
     * @param string $url
     * @return string
     */
    private function getLocalName($url) {
        $localname = strrpos($url, "/") !== false ? substr($url, strrpos($url, "/")+1) : $url;
        return $localname == '' ? wfMsg('smw_gardissue_thisresource') : $localname;
    }
 }
 

?>