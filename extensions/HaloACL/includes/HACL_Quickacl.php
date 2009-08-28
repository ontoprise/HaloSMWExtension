<?php
/* 
 * B2browse Group
 * patrick.hilsbos@b2browse.com
 */

/**
 * Description of HACL_Quickacl
 *
 * @author hipath
 */
if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file is part of the HaloACL extension. It is not a valid entry point.\n" );
}

//--- Includes ---
global $haclgIP;
class HACLQuickacl {

    private $userid = 0;
    private $sd_ids = array();


    public function getUserid() {
        return $this->userid;
    }

    public function setUserid($userid) {
        $this->userid = $userid;
    }


    function __construct($userid,$sd_ids) {
        $this->userid = $userid;
        $this->sd_ids = $sd_ids;
    }


    public function getSD_IDs() {
        return $this->sd_ids;
    }
    
    public static function newForUserId($user_id){
        return HACLStorage::getDatabase()->getQuickacl($user_id);

    }

    public function save(){
        return HACLStorage::getDatabase()->saveQuickacl($this->userid, $this->sd_ids);
    }
}
?>
