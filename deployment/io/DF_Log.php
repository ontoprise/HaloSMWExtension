<?php
/*  Copyright 2011, ontoprise GmbH
 *
 *   The deployment tool is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The deployment tool is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup DFInstaller
 *
 * Singleton
 *
 * Logger for writing an installation log. Creates a logging
 * directory in the systems temp dir. For each instantation
 * (= application runs) a new log file is created.
 *
 * @author: Kai Kühn / ontoprise / 2011
 *
 */

define("DF_MAX_LOG_SIZE", 1024*1024);

class Logger {

	var $logDir;
	var $logFileHandle;
	static $instance;

	/**
	 * Acquires the logger. 
	 * 
	 * */
	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new Logger();

		}
		return self::$instance;
	}

	private function __construct() {
	
		$homeDir = Tools::getHomeDir();
		$this->logDir = "$homeDir/df_log";
		Tools::mkpath($this->logDir);
	    $i = 1;
		if (filesize($this->logDir."/df_$i.log") > DF_MAX_LOG_SIZE) {
			while(file_exists($this->logDir."/df_$i.log")) {
				$i++;
			} 
		}
		$this->logFileHandle = fopen($this->logDir."/df_$i.log", "a");
	}

	/**
	 * Logs on INFO level
	 *
	 * @param string $msg
	 */
	public function info($msg) {
		$currentDate = date(DATE_RSS);
		fwrite($this->logFileHandle, "\n[INFO] $currentDate: $msg");
		fflush($this->logFileHandle);
	}

	/**
	 * Logs on WARN level
	 *
	 * @param string $msg
	 */
	public function warn($msg) {
		$currentDate = date(DATE_RSS);
		fwrite($this->logFileHandle, "\n[WARN] $currentDate: $msg");
		fflush($this->logFileHandle);
	}

	/**
	 * Logs on ERROR level
	 *
	 * @param string $msg
	 */
	public function error($msg) {
		$currentDate = date(DATE_RSS);
		fwrite($this->logFileHandle, "\n[ERROR] $currentDate: $msg");
		fflush($this->logFileHandle);
	}

	/**
	 * Logs on FATAL level
	 *
	 * FATAL logs should cause a termination of the program afterwards.
	 *
	 * @param string $msg
	 */
	public function fatal($msg) {
		$currentDate = date(DATE_RSS);
		fwrite($this->logFileHandle, "\n[FATAL] $currentDate: $msg");
		fflush($this->logFileHandle);
	}

	public function closeLogFile() {
		fclose($this->logFileHandle);
	}

	/*private function createDateForFileName() {
		$currentDate = date(DATE_RSS);
		$currentDate = str_replace(",","_",$currentDate);
		$currentDate = str_replace(" ","_",$currentDate);
		$currentDate = str_replace(":","_",$currentDate);
		$currentDate = str_replace("+","_",$currentDate);
		return $currentDate;
	}*/
}