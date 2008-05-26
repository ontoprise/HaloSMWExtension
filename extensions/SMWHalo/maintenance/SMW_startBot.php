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

*   Created on 17.01.2008
*
*   Starts a gardening bot from the commandline.
* 
*       Usage: php SMW_startBot.php -b <name of bot> -p <parameters>
* 
*   where <parameters> are quoted key/value pairs separated by comma.
* 
*   Example:
*   
*       php SMW_startBot.php -b smw_exportontologybot -p "GARD_EO_NAMESPACE=http://sementicwiki.org, GARD_EO_ONLYSCHEMA=1"
* 
*   Note:
*      Apostroph char '"' is escaped by {{apos}}
* 
*   Author: kai
*/

if ($_SERVER['SERVER_NAME'] != NULL) {
    echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
    return;
}

# Process command line arguments
# Parse arguments

echo "Parse arguments...";

$params = array();
for( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) {
    //-b => BotID
    if ($arg == '-b') {
        $botID = next($argv);
        continue;
    } // -p => Parameters
    if ($arg == '-p') {
        $userId = next($argv);
        continue;
    }
    
    $params[] = $arg;
}

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";
 
// include bots
require_once( $mediaWikiLocation . '/extensions/SMWHalo/specials/SMWGardening/SMW_GardeningBot.php');
require_once( $mediaWikiLocation . "/extensions/SMWHalo/specials/SMWGardening/ConsistencyBot/SMW_ConsistencyBot.php");
require_once( $mediaWikiLocation . "/extensions/SMWHalo/specials/SMWGardening/Bots/SMW_SimilarityBot.php");
require_once( $mediaWikiLocation . "/extensions/SMWHalo/specials/SMWGardening/Bots/SMW_TemplateMaterializerBot.php");
require_once( $mediaWikiLocation . "/extensions/SMWHalo/specials/SMWGardening/Bots/SMW_UndefinedEntitiesBot.php");
require_once( $mediaWikiLocation . "/extensions/SMWHalo/specials/SMWGardening/Bots/SMW_MissingAnnotationsBot.php");
require_once( $mediaWikiLocation . "/extensions/SMWHalo/specials/SMWGardening/Bots/SMW_AnomaliesBot.php");
require_once( $mediaWikiLocation . "/extensions/SMWHalo/specials/SMWGardening/Bots/SMW_ImportOntologyBot.php");
require_once( $mediaWikiLocation . "/extensions/SMWHalo/specials/SMWGardening/Bots/SMW_ExportOntologyBot.php");
require_once( $mediaWikiLocation . "/extensions/SMWHalo/specials/SMWTermImport/SMW_TermImportBot.php");
require_once( $mediaWikiLocation . "/extensions/SMWHalo/specials/SMWGardening/SMW_GardeningLog.php");


// run bot

  array_shift($params); // get rid of path
  
  // get bot object
  global $registeredBots, $wgUser; 
  $bot = $registeredBots[$botID];
    
 if ($bot != null) { 
    echo ("Starting bot: $botID\n");
    
    global $smwgGardeningBotDelay, $wgContLang;
    $gl = SMWGardeningLog::getGardeningLogAccess();
    try {
    	$taskid = $gl->addGardeningTask($botID); 
        $bot->setTaskID($taskid);
        // initialize term signal socket (for abortion)
        $bot->initializeTermSignal($taskid);
        
        SMWGardeningIssuesAccess::getGardeningIssuesAccess()->clearGardeningIssues($botID);
        
        // Transformation of parameters:
        //  1. Concat to a string
        //  2. Replace {{percantage}} by %
        //  3. decode URL
        //  4. convert string of the form (key=value,)* to a hash array
        $paramString = urldecode(str_replace("{{apos}}", "\"", implode($params,"")));
        $parameters = GardeningBot::convertParamStringToArray($paramString); 
        $log = $bot->run($parameters, true, isset($smwgGardeningBotDelay) ? $smwgGardeningBotDelay : 0);
        
        @socket_close($bot->getTermSignalSocket());
        
        if ($bot->isAborted()) {
            print "\n - Bot was aborted by user! - \n";
            die();
        }
        echo $log;
        if ($log != NULL && $log != '') {
            $glp = Title::newFromText(wfMsg('gardeninglog'), NS_SPECIAL);
            $log .= "\n[[".$wgContLang->getNsText(NS_CATEGORY).":".wfMsg('smw_gardening_log_cat')."]]";
        }
        
        // mark as finished
        $title = SMWGardeningLog::getGardeningLogAccess()->markGardeningTaskAsFinished($taskid, $log);
        if ($title != NULL) echo "Log saved at: ".$title->getLocalURL()."\n";
        
    } catch(Exception $e) {
        
        $log = 'Something bad happened during execution of "'.$botID.'": '.$e->getMessage();
        $log .= "\n[[".$wgContLang->getNsText(NS_CATEGORY).":".wfMsg('smw_gardening_log_cat')."]]";
        echo $log;
        
        $title = $gl->markGardeningTaskAsFinished($taskid, $log);
        if ($title != NULL) {
            echo "\nLog saved at: ".$title->getLocalURL();
        }
    } 
 }
 
 
?>