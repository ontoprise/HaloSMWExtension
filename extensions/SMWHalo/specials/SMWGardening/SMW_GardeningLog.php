<?php
/*
 * Created on 16.03.2007
 *
 * Abstract SMWGardeningLog interface.
 * 
 * Author: kai
 */
 abstract class SMWGardeningLog {
 	
 	static $g_interface;
 	/**
 	 * Setups GardeningLog table
 	 */
 	public abstract function setup($verbose);
 	/**
 	 * Returns the complete gardening log as a 2-dimensional array.
 	 */
 	public abstract function getGardeningLogAsTable();
 	
 	/**
 	 * Adds a gardening task. One must specify the $botID.
 	 * Returns a task id which identifies the task.
 	 * 
 	 * @param $botID botID
 	 * @return taskID
 	 */
 	public abstract function addGardeningTask($botID);
	
	/**
	 * Removes a gardening task
	 * 
	 * @param $id taskID
	 */
	public abstract function removeGardeningTask($taskID);
	
	/**
	 * Marks a Gardening task as finished.
	 * 
	 * @param $taskID taskID
	 * @param $logContent content of log as wiki markup
	 */
	public abstract function markGardeningTaskAsFinished($taskID, $logContent);
	
	/**
	 * Update progress information. Allows database updates every 15s at max. 
	 * 
	 * @param $taskID task to update
	 * @param $value incremental update
	 */
	public abstract function updateProgress($taskID, $value);
	
	/**
	 * Returns last finished Gardening task of the given type
	 * 
	 * @param botID type of Gardening task
	 */
	public abstract function getLastFinishedGardeningTask($botID = NULL);
	
	/**
	 * Cleanup Gardening log.
	 */
	public abstract function cleanupGardeningLog();
	
    public static function getGardeningLogAccess() {
        global $smwgHaloIP;
        if (SMWGardeningLog::$g_interface == NULL) {
            global $smwgDefaultStore;
            switch ($smwgDefaultStore) {
                case (SMW_STORE_TESTING):
                    SMWGardeningLog::$g_interface = null; // not implemented yet
                    trigger_error('Testing store not implemented for HALO extension.');
                break;
                case (SMW_STORE_MWDB): default:
                    require_once($smwgHaloIP . '/specials/SMWGardening/storage/SMW_GardeningLogSQL.php');
                    SMWGardeningLog::$g_interface = new SMWGardeningLogSQL();
                break;
            }
        }
        return SMWGardeningLog::$g_interface;
    }
 }
?>
