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
 * Rollback an installation.
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
		$this->tmpDir = Tools::isWindows() ? 'c:/temp/rollback_smwadmin' : '/tmp/rollback_smwadmin';

	}


	public function getAllRestorePoints() {
		if (!file_exists($this->tmpDir."/rollback_data/")) return array();
		$dirs = Tools::get_all_dirs($this->tmpDir."/rollback_data/");
		return $dirs;
	}

	/**
	 * Copy complete code base of installation including LocalSettings.php
	 * (but excluding deployment folder)
	 *
	 */
	public function saveInstallation() {

		// make sure to save only once
		static $savedInstallation = false;
		if ($savedInstallation) return true;

		if (!$this->acquireNewRestorePoint($name)) return;
		print "\n[Save installation...";
		Tools::mkpath($this->tmpDir."/rollback_data/$name");
		Tools::copy_dir($this->rootDir, $this->tmpDir."/rollback_data/$name", array($this->rootDir."/deployment"));
		print "done.]";
		$savedInstallation = true;
	}

	/**
	 * Save the database to the rollback directory
	 *
	 * @return error code of mysqldump process
	 */
	public function saveDatabase() {

		global $mwrootDir;
		if (file_exists("$mwrootDir/AdminSettings.php")) {
			require_once "$mwrootDir/AdminSettings.php";
		} else {
			// possible since MW 1.16
			$wgDBadminuser = $this->getVariableValue("LocalSettings.php", "wgDBadminuser");
			$wgDBadminpassword = $this->getVariableValue("LocalSettings.php", "wgDBadminpassword");
		}


		if (!$this->acquireNewRestorePoint($name)) return;
		// make sure to save only once
		static $savedDataBase = false;
		if ($savedDataBase) return true;

		$wgDBname = $this->getVariableValue("LocalSettings.php", "wgDBname");
		print "\n[Saving database...";
		print "\nmysqldump -u $wgDBadminuser --password=$wgDBadminpassword $wgDBname > ".$this->tmpDir."/rollback_data/$name/dump.sql";
		exec("mysqldump -u $wgDBadminuser --password=$wgDBadminpassword $wgDBname > ".$this->tmpDir."/$name/dump.sql", $out, $ret);
		if ($ret != 0) print "\nWarning: Could not save database for rollback"; else print "done.]";
		$savedDataBase = true;

		return $ret == 0;
	}

	/**
	 * Rolls back from the latest rollback point.
	 *
	 * @param string Name of restore point.
	 * @return bool true on success.
	 */
	public function rollback($name) {
		if (!file_exists($this->tmpDir."/rollback_data/$name")) return false;
		$this->restoreInstallation($name);
		$this->restoreDatabase($name);
		return true;
	}

	/**
	 * Restores complete code base of installation including LocalSettings.php
	 *
	 * @param string Name of restore point.
	 */
	private function restoreInstallation($name) {
		print "\n[Remove current installation...";
		Tools::remove_dir($this->rootDir, array(Tools::normalizePath($this->rootDir."/deployment")));
		print "done.]";
		print "\n[Restore old installation...";
		Tools::copy_dir($this->tmpDir."/rollback_data/$name", $this->rootDir);
		print "done.]";
	}

	/**
	 * Acquires a new restore point. The user has to confirm to
	 * overwrite an exisiting restore point. Can be called several
	 * times but will always return the result of the first call. All
	 * subsequent calls will have to further effects.
	 *
	 *@return boolean True if a restore point should be created/overwritten.
	 */
	private function acquireNewRestorePoint(& $name) {
		static $calledOnce = false;
		static $answer;

		if ($calledOnce) return $answer;
		$calledOnce = true;

		print "\nCreate new restore point (y/n)? ";
		$line = trim(fgets(STDIN));
		if (strtolower($line) == 'n') {
			print "\n\nDo not create a restore point.\n\n";
			$answer = false;
			return $answer;
		}

		$name = $this->getRestorePointName();

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
	 * @return string Name of restore point directory.
	 */
	private function getRestorePointName() {
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
		print "\n[Restore database...";

		exec("mysql -u $wgDBadminuser --password=$wgDBadminpassword --database=$wgDBname < ".$this->tmpDir."/$name/dump.sql", $out, $ret);
		if ($ret != 0) print "\nWarning: Could not restore database."; else print "done.]";
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
