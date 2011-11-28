<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
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
 * @author: Kai Kühn
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
	
	public function getLogDir() {
		return $this->logDir;
	} 

	private function __construct() {
		if (array_key_exists('df_homedir', DF_Config::$settings)) {
			$homeDir = DF_Config::$settings['df_homedir'];
		} else {
			$homeDir = Tools::getHomeDir();
			if (is_null($homeDir)) throw new DF_SettingError(DEPLOY_FRAMEWORK_NO_HOME_DIR, "No homedir found. Please configure one in settings.php");
		}
		$wikiname = DF_Config::$df_wikiName;
		$this->logDir = "$homeDir/$wikiname/df_log";
		Tools::mkpath($this->logDir);
		if (is_writable($this->logDir)) {
			$i = 1;
			if (@filesize($this->logDir."/df_$i.log") > DF_MAX_LOG_SIZE) {
				while(file_exists($this->logDir."/df_$i.log")) {
					$i++;
				}
			}
			$this->logFileHandle = fopen($this->logDir."/df_$i.log", "a");
		} else {
			throw new DF_SettingError(DF_HOME_DIR_NOT_WRITEABLE, "Homedir not writeable.");
		}
	}

	/**
	 * Logs on INFO level
	 *
	 * @param string $msg
	 */
	public function info($msg) {
		if (is_null($this->logFileHandle)) return;
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
		if (is_null($this->logFileHandle)) return;
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
		if (is_null($this->logFileHandle)) return;
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
		if (is_null($this->logFileHandle)) return;
		$currentDate = date(DATE_RSS);
		fwrite($this->logFileHandle, "\n[FATAL] $currentDate: $msg");
		fflush($this->logFileHandle);
	}

	public function closeLogFile() {
		if (is_null($this->logFileHandle)) return;
		fclose($this->logFileHandle);
	}


}
