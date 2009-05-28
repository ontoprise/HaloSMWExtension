<?php
function isWindows() {
    static $thisBoxRunsWindows;
    
    if (! is_null($thisBoxRunsWindows)) return $thisBoxRunsWindows;
    
    ob_start();
    phpinfo();
    $info = ob_get_contents();
    ob_end_clean();
    //Get Systemstring
    preg_match('!\nSystem(.*?)\n!is',strip_tags($info),$ma);
    //Check if it consists 'windows' as string
    preg_match('/[Ww]indows/',$ma[1],$os);
    if($os[0]=='' && $os[0]==null ) {
        $thisBoxRunsWindows= false;
    } else {
        $thisBoxRunsWindows = true;
    }
    return $thisBoxRunsWindows;
}
?>