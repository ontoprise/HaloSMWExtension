<?php

/*  Copyright 2009, ontoprise GmbH
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
 * Restore tool. Creates and restores wiki installations (aka 'restore points).
 * Can handle several restore points.
 *
 * @author: Kai K�hn / ontoprise / 2009
 *
 */
class Rollback {

	// installation directory of Mediawiki
	var $rootDir;

	// temporary directory where rollback data is stored.
	var $tmpDir;


	static $instance;

	public static function getInstance($rootDir) {
		if (is_null(self::$instance)) {
			self::$instance = new Rollback($rootDir);

		}
		return self::$instance;
	}

	private function __construct($rootDir) {

		$this->rootDir = $rootDir;
		$homeDir = Tools::getHomeDir();
		$this->tmpDir = "$homeDir/rollback_smwadmin";

	}

	/**
	 * Returns the absolute paths of all restore points.
	 *
	 * @return array of string
	 */
	public function getAllRestorePoints() {
		if (!file_exists($this->tmpDir."/")) return array();
		$dirs = Tools::get_all_dirs($this->tmpDir."/");
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

		// make sure to save only once
		static $savedInstallation = false;
		if ($savedInstallation) return true;

		if (is_null($name)) {
			if (!$this->acquireNewRestorePoint($name)) return true;
		} 

		$logger = Logger::getInstance();
		$logger->info("Save installation to ".$this->tmpDir."/$name");
		print "\n[Save installation...";
		$success = Tools::mkpath($this->tmpDir."/$name");
		$success = $success && Tools::copy_dir($this->rootDir, $this->tmpDir."/$name", array($this->rootDir."/deployment"));
		print "done.]";
		$savedInstallation = true;
		if (!$success) {
			$logger->error("Could not copy the MW installation.");
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

		global $mwrootDir;
		if (file_exists("$mwrootDir/AdminSettings.php")) {
			require_once "$mwrootDir/AdminSettings.php";
		} else {
			// possible since MW 1.16
			$wgDBadminuser = $this->getVariableValue("LocalSettings.php", "wgDBadminuser");
			$wgDBadminpassword = $this->getVariableValue("LocalSettings.php", "wgDBadminpassword");
		}

		if (is_null($name)) {
			if (!$this->acquireNewRestorePoint($name)) return true;
		} 
		// make sure to save only once
		static $savedDataBase = false;
		if ($savedDataBase) return true;

		$logger = Logger::getInstance();
		$logger->info("Save database to ".$this->tmpDir."/$name/dump.sql");

		$wgDBname = $this->getVariableValue("LocalSettings.php", "wgDBname");
		print "\n[Saving database...";
		//print "\nmysqldump -u $wgDBadminuser --password=$wgDBadminpassword $wgDBname > ".$this->tmpDir."/$name/dump.sql";
		exec("mysqldump -u $wgDBadminuser --password=$wgDBadminpassword $wgDBname > \"".$this->tmpDir."/$name/dump.sql\"");
		print "done.]";
		$savedDataBase = true;

		if ($ret != 0) {
			$logger->error("Could not save the database.");
		}
		return $ret == 0;
	}

	/**
	 * Rolls back from the latest rollback point.
	 *
	 * @param string Name of restore point.
	 * @return bool true on success.
	 */
	public function restore($name) {
		if (!file_exists($this->tmpDir."/$name")) return false;
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
		static $calledOnce = false;
		static $answer;
		static $namedStored;
		$name = $namedStored;

		if ($calledOnce) return $answer;
		$calledOnce = true;

		print "\nCreate new restore point (y/n)? ";
		$line = trim(fgets(STDIN));
		if (strtolower($line) == 'n') {
			print "\n\nDo not create a restore point.\n\n";
			$answer = false;
			return $answer;
		}

		$namedStored = $this->getRestorePointName();
		$name = $namedStored;

		// clear if it already exists
		if (file_exists($this->tmpDir."/".$name)) {
			Tools::remove_dir($this->tmpDir."/".$name);
		}
		Tools::mkpath($this->tmpDir."/".$name);
		$answer = true;
		return $answer;

	}

	/**
	 * Asks for the name of a restore point.
	 * If it exists it asks for permission to overwrite.
	 *
	 * Note: REQUIRES user interaction
	 *
	 * @return string Name of restore point directory.
	 */
	protected function getRestorePointName() {
		$done = false;
		do {
			print "\nPlease enter a name for the restore point: ";
			$name = trim(fgets(STDIN));
			$name = str_replace(" ","_", $name);

			if (preg_match('/\w+/', $name, $matches) === false) continue;
			if ($name !== $matches[0]) {
				print "\nForbidden characters. Please use only alphanumeric chars and spaces";
				continue;
			}

			// clear if it already exists
			if (file_exists($this->tmpDir.$name)) {
				print "\nA restore point with this name already exists. Overwrite? (y/n) ";
				$line = trim(fgets(STDIN));
				if (strtolower($line) == 'n') {
					continue;
				}
			}
			$done = true;
		} while(!$done);
		return $name;
	}

	/**
	 * Asks for confirmation.
	 *
	 * Note: REQUIRES user interaction
	 *
	 * @param $msg
	 */
	protected function askForConfirmation($msg) {
		return Tools::consoleConfirm($msg);
	}

	/**
	 * Restores complete code base of installation including LocalSettings.php
	 *
	 * @param string Name of restore point.
	 */
	private function restoreInstallation($name) {
		$logger = Logger::getInstance();

		$logger->info("Remove current installation");
		print "\n[Remove current installation...";
		Tools::remove_dir($this->rootDir, array(Tools::normalizePath($this->rootDir."/deployment")));
		print "done.]";

		$logger->info("Restore old installation");
		print "\n[Restore old installation...";
		$success = Tools::copy_dir($this->tmpDir."/$name", $this->rootDir);
		if (!$success) {
			$logger->error("Restore old installation faild. Could not copy from ".$this->tmpDir."/$name");
		}
		print "done.]";
	}

	/**
	 * Restore the database dump from the rollback directory.
	 *
	 * @param string Name of restore point.
	 * @return boolean
	 */
	private function restoreDatabase($name) {
		global $mwrootDir;
		if (file_exists("$mwrootDir/AdminSettings.php")) {
			require_once "$mwrootDir/AdminSettings.php";
		} else {
			// possible since MW 1.16
			$wgDBadminuser = $this->getVariableValue("LocalSettings.php", "wgDBadminuser");
			$wgDBadminpassword = $this->getVariableValue("LocalSettings.php", "wgDBadminpassword");
		}
		$wgDBname = $this->getVariableValue("LocalSettings.php", "wgDBname");
		if (!file_exists($this->tmpDir."/$name/dump.sql")) return false; // nothing to restore
		if (!$this->askForConfirmation("Restore database? (y/n) ")) return false;
		print "\n[Restore database...";
		$logger = Logger::getInstance();
		$logger->info("Restore database");
		exec("mysql -u $wgDBadminuser --password=$wgDBadminpassword --database=$wgDBname < \"".$this->tmpDir."/$name/dump.sql\"", $out, $ret);
		if ($ret != 0){
			$logger->error("Could not restore database.");
			print "\nError: Could not restore database.";
		}  else print "done.]";
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
		return $matches[1];
	}




}
