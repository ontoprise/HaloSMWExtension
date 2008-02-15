<?php
/*
 * Created on 12.03.2007
 *
 * Author: kai
 */

 
 // include them for synchronous run
 require_once("ConsistencyBot/SMW_ConsistencyBot.php");
 require_once("Bots/SMW_SimilarityBot.php");
 require_once("Bots/SMW_TemplateMaterializerBot.php");
 require_once("Bots/SMW_UndefinedEntitiesBot.php");
 require_once("Bots/SMW_MissingAnnotationsBot.php");
 require_once("Bots/SMW_AnomaliesBot.php");
 require_once("Bots/SMW_ImportOntologyBot.php");
 require_once("Bots/SMW_ExportOntologyBot.php");
 
 require_once("SMW_GardeningLog.php");
 
 // user groups  
 define('SMW_GARD_ALL_USERS', 'darkmatter');
 define('SMW_GARD_GARDENERS', 'gardener');
 define('SMW_GARD_SYSOPS' , 'sysop');
 
 // defines a port range (100 ports) beginning with 5000 by default
 // can be configured in LocalSettings.php by setting $smwgAbortBotPortRange
 define('ABORT_BOT_PORT_RANGE', 5000);
 
 abstract class GardeningBot {
 	
 	/**
 	 * Bot ID
 	 */
 	protected $id;
 	
 	/**
 	 * Parameters
 	 */
 	protected $parameters;
 	
 	// progress
 	protected $totalWork = 0;
 	protected $subtaskWork = 0;
 	protected $currentTask = 0;
 	protected $currentWork = 0;
 	private $taskId = -1;
 	private $lastUpdate = 0;
 	
 	// socket for termination signal
 	private $socket;
 	private $isAborted = false;
 	
 	protected function GardeningBot($id) {
 		$this->id = $id;
 		
 		// registering bot
 		global $registeredBots;
 		$registeredBots[$id] = $this;
 		$this->parameters = $this->createParameters();
  	}
 	
 	final public function getBotID() {
 		return $this->id;
 	}
 	
 	final public function getParameters() {
 		return $this->parameters;
 	}
 	
 	final public function getTermSignalSocket() {
 		return $this->socket;
 	}
 	
 	
 	/**
 	 * Returns an array mapping parameter IDs to parameter objects
 	 */
 	protected abstract function createParameters();
 	
 	/**
 	 * Returns a short help text.
 	 */
 	public abstract function getHelpText();
 	
 	/**
 	 * Returns the bot name for a user
 	 */
 	public abstract function getLabel();
 	
 	/**
 	 * Returns array of user group names which may
 	 * use this bot.
 	 * 
 	 * see user groups constants above
 	 */
 	public abstract function allowedForUserGroups();
 	
 	/**
 	 * Method which starts the actual gardening operations.
 	 * Should return a log as wiki markup.
 	 * 
 	 * @param $paramArray hash array containing the given value for each parameter ID as key. 
 	 * @param $isAsync indicates if the bots runs asynchronously.
 	 * @param $delay indicates if the gardening process should take a periodic delay. (may be ignored, but should not) 
 	 */
 	public abstract function run($paramArray, $isAsync, $delay);
 	
 		/**
 	 * Total work (= total number of subtasks)
 	 */
 	public function setNumberOfTasks($totalwork) {
 		$this->totalWork = $totalwork;
 	}
 	
 	/**
 	 * Announces work done in current subtask.
 	 * 
 	 * @param $work incremental 
 	 */
 	public function worked($work) {
 		$this->currentWork += $work;
 		$currentTime = time();
 		if ($currentTime-$this->lastUpdate > 15) { // allow updates only after 15 seconds
 			$this->lastUpdate = $currentTime;
 			if ($this->taskId != -1) SMWGardening::getGardeningLogAccess()->updateProgress($this->taskId, $this->getWorkDone());
 		}
 	}
 	
 	/**
 	 * Adds next subtask
 	 * 
 	 * @param $work total work of subtask
 	 */
 	public function addSubTask($work) {
 		$this->subtaskWork = $work;
 		$this->currentTask++;
 		$this->currentWork = 0;
 	}
 	
 	/**
 	 * Determines if bot was aborted.
 	 * Must be regularly called to be effective!
 	 * If it once returned TRUE, it will always return TRUE.
 	 * 
 	 * @return TRUE, if bot received the termination signal. Otherwise FALSE.
 	 */
 	public function isAborted() {
 		if ($this->isAborted) return true;
 		$accept_sock = @socket_accept($this->socket);	
		if ($accept_sock !== false && $accept_sock !== NULL) {
			$name = "";
			socket_getpeername($accept_sock, $name);
			if ($name == '127.0.0.1') { //TODO: save? spoofing?
				socket_close($accept_sock);
				$this->isAborted = true;
				return true;	
			}
		}
		return false;
 	}
 	
 	/**
 	 * Returns work done at current subtask.
 	 * 
 	 * @return Integer cummulative work done. 
 	 */
 	public function getCurrentWorkDone() {
 		return $this->currentWork;
 	}
 	
 	/**
 	 * DO NOT CALL EVER
 	 */
 	public function initializeTermSignal($taskid) {
 		global $smwgAbortBotPortRange;
 		if (!isset($smwgAbortBotPortRange)) $smwgAbortBotPortRange = ABORT_BOT_PORT_RANGE;
 		// create a socket for termination signal
 		// port is freely chosen $smwgAbortBotPortRange <= port <= $smwgAbortBotPortRange + 100
 		$this->socket = socket_create_listen(($taskid % 100) + $smwgAbortBotPortRange); 
 		if ($this->socket !== false) {
 			socket_set_nonblock($this->socket);
 		}
 	}
 	
 	/**
 	 * DO NOT CALL EVER
 	 */
 	public function setTaskID($taskId) {
 		$this->taskId = $taskId;
 	}
 	
  	/**
 	 * Returns total work done.
 	 * 
 	 * Mutiply with 100 to get percentage value.
 	 * 
 	 * @return 0 <= $value <= 1
 	 */
 	private function getWorkDone() {
 		return (($this->currentTask-1)/$this->totalWork) + ($this->currentWork / $this->subtaskWork / $this->totalWork); 
 	}
 	
 	
 	
 	/**
 	 * Checks if user is member of at least one of the given groups.
 	 */
 	public static function isUserAllowed($allowedGroupsForBot) {
 		global $wgUser;
 		$allowed = false;
 		$groupsOfUser = $wgUser->getGroups();
 		foreach($groupsOfUser as $g) {
 			if (in_array($g, $allowedGroupsForBot)) {
 				$allowed = true;
 			}
 		} 		
 		return $allowed;
 	}
 	
 	/**
 	 * Aborts a bot.
 	 * 
 	 * Abortion should be preferred from killing, but
 	 * it requires a regularly calling of GardeningBot::isAborted()
 	 * 
 	 * @param $taskid
 	 * @return TRUE if abortion was auccessful, otherwise FALSE.
 	 */
 	public static function abortBot($taskid) {
 		global $smwgAbortBotPortRange;
 		if (!isset($smwgAbortBotPortRange)) $smwgAbortBotPortRange = ABORT_BOT_PORT_RANGE;
 		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
 		// port is freely chosen $smwgAbortBotPortRange <= port <= $smwgAbortBotPortRange + 100
 		$success = @socket_connect($socket, "127.0.0.1", ($taskid % 100) + $smwgAbortBotPortRange); 
		socket_close($socket);
		return $success;
 	}
 	
 	/**
 	 * Kills a bot.
 	 * 
 	 * Terminates the process of the bot.
 	 * CAUTION: May cause database inconsistencies in rare cases.
 	 * 
 	 * @param taskid
 	 */
 	public static function killBot($taskid) {
 		$processID = GardeningBot::getProcessID($taskid);
 		if ($processID == NULL) return;
 		if (GardeningBot::isWindows()) {
 			exec("taskkill /PID $processID"); // should work on Windows XP Home too
 		} else {
 			exec("kill $processID");
 		}
 	}
 	 	
 	/**
    * Runs a bot.
    * 
    * @param $botID:
    * @param $params: parameter string. Blank is separator, 
    * 		   because it must be passed to an external script.
    * @param $runAsnyc: 
    * @param $keepConsoleAfterTermination: 
    */	
 	 public static function runBot($botID, $params = "", $runAsync = true) {
 	 	global $keepGardeningConsole;
 	 	$keepConsoleAfterTermination = isset($keepGardeningConsole) ? $keepGardeningConsole : false;
 	 	
 	 	// check if bot is registered
 	 	if (!GardeningBot::isBotKnown($botID)) {
 	 		return "ERROR:gardening-tooldetails:".wfMsg('smw_gard_unknown_bot');  
 	 	}
 	 	global $phpInterpreter, $wgUser, $registeredBots, $wgGardeningBotDelay;
 		$userId = $wgUser->getId();
 		$bot = $registeredBots[$botID];
 		
 		// check if user is allowed to start the bot
 		if (!GardeningBot::isUserAllowed($bot->allowedForUserGroups())) {
 			return "ERROR:gardening-tooldetails:".wfMsg('smw_gard_no_permission'); 
 		}
 		
 		
 		// validate parameters
 	 	$isValid = GardeningBot::checkParameters($botID, GardeningBot::convertParamStringToArray($params));
 	 	if (gettype($isValid) == 'string') {
 	 		return "ERROR:$isValid";
 	 	}
 	 	
 	 	// ok everything is fine, so add a gardening task
 	 	$taskid = SMWGardening::getGardeningLogAccess()->addGardeningTask($botID);
 		$IP = realpath( dirname( __FILE__ ) . '/..' );
 		
 		
 		if (!$phpInterpreter) {
 			// if $phpInterpreter is not set, assume it is in search path
 			// if not, starting of bot will FAIL!
 			$phpInterpreter = "php";
 		}
		
				
		// and start it...
		$runCommand = "$phpInterpreter -q $IP/SMWGardening/SMW_AsyncBotStarter.php"; 
		global $wgServer;	
		$serverNameParam = escapeshellarg($wgServer);	 		
 		if(GardeningBot::isWindows()==false) { //*nix (aka NOT windows)
 			
 			
 			if ($runAsync) { 
 				//TODO: test async code for linux. 
 				//low prio 
 				$runCommand .= " -b ".escapeshellarg($botID)." -t $taskid -u $userId -s $serverNameParam ".escapeshellarg(str_replace("%", '{{percentage}}', $params));
  	 			$nullResult = `$runCommand > /dev/null &`;
  	 			
  	 		
 			} else { // run sync
 				
  
 				$paramArray = explode(" ", urldecode($params));
 				if ($bot != null) { 
 					$log = $bot->run($paramArray, $runAsync, isset($wgGardeningBotDelay) ? $wgGardeningBotDelay : 0);
 					$log .= "\n[[category:GardeningLog]]";
 					SMWGardening::getGardeningLogAccess()->markGardeningTaskAsFinished($taskid, $log);
 				}
 				socket_close($this->socket);
 			}
  		}
  		else //windowze
  		{
  	 		$wshShell = new COM("WScript.Shell");
  	 		$clOption = $keepConsoleAfterTermination ? "/K" : "/C"; 
			$runCommand = "cmd $clOption ".$runCommand;
  			
  			if ($runAsync) { // run async
 			
 				// botID is first parameter
 				// taskID is second
 				// user defined parameters follow
 				// special escaping for % --> {{percentage}} because escapeshellarg(...) replaces % by blanks
 				$runCommand .= " -b ".escapeshellarg($botID)." -t $taskid -u $userId -s $serverNameParam ".escapeshellarg(str_replace("%", '{{percentage}}', $params));
 			
 				$oExec = $wshShell->Run($runCommand, 7, false);
 				
 			} else { // run synchron
 		
				  
 				$paramArray = explode(" ", urldecode($params));
 				if ($bot != null) { 
 					$log = $bot->run($paramArray, $runAsync, isset($wgGardeningBotDelay) ? $wgGardeningBotDelay : 0);
 					$log .= "\n[[category:GardeningLog]]";
 					SMWGardening::getGardeningLogAccess()->markGardeningTaskAsFinished($taskid, $log);
 				}
 				socket_close($this->socket);
 			}
  		}
   		return $taskid;
 	}
 	
 	/**
 	* Checks if the ServerOS is Windows 
 	* returns true/false 
 	*/
 	private static function isWindows() {
 		ob_start();
        phpinfo();
        $info = ob_get_contents();
        ob_end_clean();
		//Get Systemstring
        preg_match('!\nSystem(.*?)\n!is',strip_tags($info),$ma);
		//Check if it consists 'windows' as string
        preg_match('/[Ww]indows/',$ma[1],$os);
        if($os[0]=='' && $os[0]==null ) {
                return false;
        } else {
                return true;
        }
 	}
 	
 	/**
 	 * Returns true if bot is registered.
 	 */
 	private static function isBotKnown($botID) {
 		global $registeredBots; 
  		$bot = $registeredBots[$botID];
  		return $bot != null;
 	}
 	
 	
 	
 	/**
 	 * Validates parameters against a given bot
 	 */
 	private static function checkParameters($botID, $params) {
 		global $registeredBots; 
  		$bot = $registeredBots[$botID];
  		return GardeningBot::validatesParameters($bot, $params); 
 	}
 	
 	/**
 	 * Validates the Parameters. 
 	 * $paramArray: array of params sent by user
 	 * Returns true if everything is ok or a string
 	 *  explaining the problem occured.
 	 */
 	private static function validatesParameters($bot, $paramValues) {
 		$result = true;
 		 
 		foreach ($bot->getParameters() as $paramObject) {
 			$ok = $paramObject->validate($paramValues[$paramObject->getID()]);
 			if (gettype($ok) == 'string') { // error
 				$lastFailure = $paramObject->getID().":".$ok;
 			} 
 			$result = $result && ($ok === true);
 			
 		}
 		return $result ? true : $lastFailure;
 	}
 	
 	public static function convertParamStringToArray($param) {
 		$result = array();
 		$paramArray = explode(",", $param);
 		foreach($paramArray as $p) {
 			$keyValue = explode("=", $p);
 			if (count($keyValue) == 2) $result[$keyValue[0]] = $keyValue[1];
 		}
 		
 		return $result;
 	}
 	
 	/**
 	 * Returns the process ID for a given task ID. (OS-dependant)
 	 */
 	private static function getProcessID($taskID) {
 		if (GardeningBot::isWindows()) {
 			$processes = array();
 			exec('tasklist /V /FO CSV /NH', $processes);
 			
 			foreach($processes as $p) {
 				$data = explode(",", $p);
 				if (strpos($data[8], "-t $taskID") !== false 
 					&& strpos($data[8], "SMW_AsyncBotStarter.php") !== false) {
 					return str_replace("\"", "", $data[1]) + 0; // return processID as number
 				}
 			}
 			
 		} else { // *nix
 			$processes = array();
 			exec('ps -eo pid,args', $processes);
 			foreach($processes as $p) {
 				$matches = array();
 				preg_match('/(\s*\d+)(.*)/', $p, $matches);
 				if (strpos($matches[2], "-t $taskID") !== false 
 					&& strpos($matches[2], "SMW_AsyncBotStarter.php") !== false) {
 						return $matches[1] + 0; // return processID as number
 				}
 			}
		}
 		return NULL;
 	}
 	
 	
 	
 	/**
 	 * Prints a textual progress indication.
 	 * 
 	 * @param 0 <= $percentage <= 1
 	 */
 	public static function printProgress($percentage) {
 		$pro_str = number_format($percentage*100, 0);
 		if ($percentage == 0) { 
 			print $pro_str."%";
 			return;
 		} 
 		switch(strlen($pro_str)) {
 			case 4: print "\x08\x08\x08\x08\x08"; break;
 			case 3: print "\x08\x08\x08\x08"; break;
 			case 2: print "\x08\x08\x08"; break;
 			case 1: print "\x08\x08"; break;
 			case 0: print "\x08";
 		}
 		print $pro_str."%";
 	}
 }
 
   
   
 	
 	
?>
