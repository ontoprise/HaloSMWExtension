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
 * Restore tool. Creates and restores wiki installations (aka 'restore points).
 * Can handle several restore points.
 *
 * @author: Kai K�hn
 *
 */
class Rollback {

	// installation directory of Mediawiki
	var $rootDir;

	// restoreDir directory where rollback data is stored.
	var $restoreDir;


	static $instance;

	public static function getInstance($rootDir) {
		if (is_null(self::$instance)) {
			self::$instance = new Rollback($rootDir);

		}
		return self::$instance;
	}

	private function __construct($rootDir) {

		$this->rootDir = $rootDir;
		if (array_key_exists('df_homedir', DF_Config::$settings)) {
			$homeDir = DF_Config::$settings['df_homedir'];
		} else {
			$homeDir = Tools::getHomeDir();
			if (is_null($homeDir)) throw new DF_SettingError(DEPLOY_FRAMEWORK_NO_HOME_DIR, "No homedir found. Please configure one in settings.php");
		}
		if (!is_writable($homeDir)) {
			throw new DF_SettingError(DF_HOME_DIR_NOT_WRITEABLE, "Homedir not writeable.");
		}
		$wikiname = DF_Config::$df_wikiName;
		$this->restoreDir = "$homeDir/$wikiname/df_restore";

	}

	/**
	 * Returns the absolute paths of all restore points.
	 *
	 * @return array of string
	 */
	public function getAllRestorePoints() {
		if (!file_exists($this->restoreDir."/")) return array();
		$dirs = Tools::get_all_dirs($this->restoreDir."/");
		return $dirs;
	}

	/**
	 * Copy complete code base of installation including LocalSettings.php
	 * (but excluding deployment folder)
	 *
	 * @param boolean $name Do not ask the user for confirmation, use this name
	 *
	 * @boolean True if no error occured
	 */
	public function saveInstallation($name = NULL) {
		global $dfgOut;
		// make sure to save only once
		static $savedInstallation = false;
		if ($savedInstallation) return true;

		if (is_null($name)) {
			if (!$this->acquireNewRestorePoint($name)) return true;
		}

		$logger = Logger::getInstance();
		$logger->info("Save installation to ".$this->restoreDir."/$name");
		$dfgOut->outputln("[Save installation...");
		$success = Tools::mkpath($this->restoreDir."/$name");
		//$success = $success && Tools::copy_dir($this->rootDir, $this->restoreDir."/$name", array($this->rootDir."/deployment"));
		$success = $success && Tools::makeZip($this->rootDir, $this->restoreDir."/$name/software.zip", $this->rootDir);
		$dfgOut->output("done.]");
		$savedInstallation = true;
		if (!$success) {
			$logger->error("Could not copy the MW installation.");
		} else {
			$logger->info("Software backup file: ".$this->restoreDir."/$name/software.zip");
			$dfgOut->outputln("Software backup file: ".$this->restoreDir."/$name/software.zip");
		}
		return $success;
	}

	/**
	 * Save the database to the rollback directory
	 *
	 * @param boolean $name Do not ask the user for confirmation, use this name
	 *
	 * @return boolean True if no error occured on creating a database dump
	 */
	public function saveDatabase($name = NULL) {

		global $mwrootDir, $dfgOut;
		if (file_exists("$mwrootDir/AdminSettings.php")) {
			require_once "$mwrootDir/AdminSettings.php";
		} else {
			// possible since MW 1.16
			$wgDBadminuser = $this->getVariableValue("LocalSettings.php", "wgDBadminuser");
			$wgDBadminpassword = $this->getVariableValue("LocalSettings.php", "wgDBadminpassword");
			$wgDBserver = $this->getVariableValue("LocalSettings.php", "wgDBserver");

			if (empty($wgDBadminuser) || empty($wgDBadminpassword)) {
				$dfgOut->outputln('$wgDBadminuser and $wgDBadminpassword is empty! Please set.', DF_PRINTSTREAM_TYPE_WARN);
			}
		}

		if (is_null($name)) {
			if (!$this->acquireNewRestorePoint($name)) return true;
		}
		// make sure to save only once
		static $savedDataBase = false;
		if ($savedDataBase) return true;

		$logger = Logger::getInstance();
		$logger->info("Save database to ".$this->restoreDir."/$name/dump.sql");

		$wgDBname = $this->getVariableValue("LocalSettings.php", "wgDBname");
		$dfgOut->outputln("[Saving database...");

		$mysqlDump = "mysqldump";
		if (array_key_exists('df_mysql_dir', DF_Config::$settings) && !empty(DF_Config::$settings['df_mysql_dir'])) {
			$mysqlDump = DF_Config::$settings['df_mysql_dir']."/bin/mysqldump";
		}

		$logger->info("\n\"$mysqlDump\" -u $wgDBadminuser --password=$wgDBadminpassword --host=$wgDBserver $wgDBname > ".$this->restoreDir."/$name/dump.sql");
		exec("\"$mysqlDump\" -u $wgDBadminuser --password=$wgDBadminpassword --host=$wgDBserver $wgDBname > \"".$this->restoreDir."/$name/dump.sql\"", $out, $ret);
		$dfgOut->output("done.]");
		$savedDataBase = true;

		if ($ret != 0) {
			$dfgOut->outputln("Could not run mysqldump. Skip that. Please set 'df_mysql_dir'. See log for details.");
			$logger->error("Could not save the database.");
		} else {
			$logger->info("Database dump file: ".$this->restoreDir."/$name/dump.sql");
			$dfgOut->outputln("Database dump file: ".$this->restoreDir."/$name/dump.sql");
		}
		return $ret == 0;
	}

	/**
	 * Removes the restore point with the given name.
	 *
	 * @param string $name
	 * @throws InstallationError
	 */
	public function removeRestorePoint($name) {
		global $dfgOut;
		$logger = Logger::getInstance();

		// make sure $name points to a subdirectory below df_restore
		// and is not something like this: ../../xyz
		$pathNormalized = realpath($this->restoreDir."/$name");
		$pathNormalized = Tools::makeUnixPath($pathNormalized);
		$pathRestoreDir =  Tools::makeUnixPath(realpath($this->restoreDir));
		if (strpos($pathNormalized, $pathRestoreDir) !== 0) {
			throw new InstallationError(DEPLOY_FRAMEWORK_INVALID_RESTOREPOINT, "Invalid restore point: $name",$name);
		}

		// remove restore point
		$dfgOut->outputln("[Remove restore point...");
		$success = Tools::remove_dir_native($this->restoreDir."/$name");
		if (!$success) {
			$logger->error("Could not remove restore point: '$name'.");
		} else {
			$logger->info("Restore point removed: $name");
		}
		$dfgOut->output("done.]");

		return $success;
	}

	/**
	 * Rolls back from the latest rollback point.
	 *
	 * @param string Name of restore point.
	 * @return bool true on success.
	 */
	public function restore($name) {
		if (!file_exists($this->restoreDir."/$name")) return false;
		$this->restoreInstallation($name);
		$this->restoreDatabase($name);
		return true;
	}



	/**
	 * Acquires a new restore point. The user has to enter a name and to confirm to
	 * overwrite an exisiting restore point. Can be called several
	 * times but will always return the result of the first call (holds
	 * also for all out parameters. All subsequent calls will have no
	 * further effects.
	 *
	 * Note: REQUIRES user interaction
	 *
	 * @param string (out) name of the restore point
	 * @return boolean True if a restore point should be created/overwritten.
	 */
	protected function acquireNewRestorePoint(& $name) {

		global $dfgGlobalOptionsValues;
		if (array_key_exists('df_watsettings_create_restorepoints', $dfgGlobalOptionsValues)) {
			if (!$dfgGlobalOptionsValues['df_watsettings_create_restorepoints']) {
				return false;
			}
			$name = "auto_generated_".uniqid();
		} else {
			$create = DFUserInput::getInstance()->askForRestorePoint($name, $this->restoreDir);
			if (!$create) return false;
		}

		// clear if it already exists
		if (file_exists($this->restoreDir."/".$name)) {
			Tools::remove_dir($this->restoreDir."/".$name);
		}
		Tools::mkpath($this->restoreDir."/".$name);
		$answer = true;
		return $answer;

	}




	/**
	 * Restores complete code base of installation including LocalSettings.php
	 *
	 * @param string Name of restore point.
	 */
	private function restoreInstallation($name) {
		global $dfgOut;
		$logger = Logger::getInstance();

		$logger->info("Remove current installation");
		$dfgOut->outputln("[Remove current installation...");

		Tools::remove_dir($this->rootDir, "unzip.exe");
		$dfgOut->output("done.]");

		$logger->info("Restore old installation");
		$dfgOut->outputln("[Restore old installation...");

		$success = Tools::unpackZip($this->restoreDir."/$name/software.zip", $this->rootDir, $this->rootDir);
		if (!$success) {
			$logger->error("Restore old installation faild. Could not copy from ".$this->restoreDir."/$name");
		}
		$dfgOut->output("done.]");
	}

	/**
	 * Restore the database dump from the rollback directory.
	 *
	 * @param string Name of restore point.
	 * @return boolean
	 */
	private function restoreDatabase($name) {
		global $mwrootDir, $dfgOut;
		if (file_exists("$mwrootDir/AdminSettings.php")) {
			require_once "$mwrootDir/AdminSettings.php";
		} else {
			// possible since MW 1.16
			$wgDBadminuser = $this->getVariableValue("LocalSettings.php", "wgDBadminuser");
			$wgDBadminpassword = $this->getVariableValue("LocalSettings.php", "wgDBadminpassword");
			$wgDBserver = $this->getVariableValue("LocalSettings.php", "wgDBserver");
		}
		$wgDBname = $this->getVariableValue("LocalSettings.php", "wgDBname");
		if (!file_exists($this->restoreDir."/$name/dump.sql")) return false; // nothing to restore

		global $dfgNoAsk;
		if (isset($dfgNoAsk) && $dfgNoAsk == true) {
			// default answer is yes, restore.
		} else {
			if (!DFUserInput::consoleConfirm("Restore database? (y/n) ")) return false;
		}
		$dfgOut->outputln("[Restore database...");
		$logger = Logger::getInstance();
		$logger->info("Restore database");
		$mysqlExec = "mysql";
		if (array_key_exists('df_mysql_dir', DF_Config::$settings) && !empty(DF_Config::$settings['df_mysql_dir'])) {
			$mysqlExec = DF_Config::$settings['df_mysql_dir']."/bin/mysql";
		}
		$logger->info("\"$mysqlExec\" -u $wgDBadminuser --password=$wgDBadminpassword --host=$wgDBserver --database=$wgDBname < \"".$this->restoreDir."/$name/dump.sql\"");
		exec("\"$mysqlExec\" -u $wgDBadminuser --password=$wgDBadminpassword --host=$wgDBserver --database=$wgDBname < \"".$this->restoreDir."/$name/dump.sql\"", $out, $ret);
		if ($ret != 0){
			$logger->error("Could not restore database.");
			$dfgOut->outputln("Could not restore database. See log for details.", DF_PRINTSTREAM_TYPE_ERROR);
		}  else $dfgOut->output("done.]");
		return ($ret == 0);
	}

	/**
	 *
	 * Reads variables value.
	 *
	 * @param $file File path (relative to MW directory)
	 * @param $varname Variable name
	 */
	private function getVariableValue($file,$varname) {
		global $mwrootDir;
		$ls_content = file_get_contents("$mwrootDir/$file");
		preg_match('/\$'.$varname.'\s*=\s*["\']([^"\']+)["\']/', $ls_content, $matches);
		return array_key_exists(1, $matches) ? $matches[1] : '';
	}




}
