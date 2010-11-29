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



	/**
	 * Copy complete code base of installation including LocalSettings.php
	 * (but excluding deployment folder)
	 *
	 */
	public function saveInstallation() {
		if (!$this->acquireNewRollback()) return;
		print "\n[Save installation...";
		Tools::mkpath($this->tmpDir."/rollback_data/");
		Tools::copy_dir($this->rootDir, $this->tmpDir."/rollback_data", array($this->rootDir."/deployment"));
		print "done.]";
	}

	/**
	 * Save the database to the rollback directory
	 *
	 * @return error code of mysqldump process
	 */
	public function saveDatabase() {
		global $mwrootDir;
		require_once "$mwrootDir/AdminSettings.php";
		if (!$this->acquireNewRollback()) return;
		// make sure to save only once
		static $savedDataBase = false;
		if ($savedDataBase) return true;

		$savedDataBase = true;
		$wgDBname = $this->getDatabasename();
		print "\n[Saving database...";
		//print "\nmysqldump -u $wgDBadminuser --password=$wgDBadminpassword $wgDBname > ".$this->tmpDir."/rollback_data/dump.sql";
		exec("mysqldump -u $wgDBadminuser --password=$wgDBadminpassword $wgDBname > ".$this->tmpDir."/dump.sql", $out, $ret);
		if ($ret != 0) print "\nWarning: Could not save database for rollback"; else print "done.]";
		return $ret == 0;
	}

	/**
	 * Rolls back from the latest rollback point.
	 *
	 */
	public function rollback() {

		$this->restoreInstallation();
		$this->restoreDatabase();
	}

	/**
	 * Restores complete code base of installation including LocalSettings.php
	 *
	 */
	private function restoreInstallation() {
		print "\n\t[Remove current installation...";
		Tools::remove_dir($this->rootDir, array(Tools::normalizePath($this->rootDir."/deployment")));
		print "done.]";
		print "\n[Restore old installation...";
		Tools::copy_dir($this->tmpDir."/rollback_data", $this->rootDir);
		print "\ndone.]";
	}

	/**
	 * Acquires a new rollback operation. The user has to confirm to
	 * overwrite exisiting rollback data.
	 * 
	 * @return boolean True if a rollback should be done.
	 */
	private function acquireNewRollback() {
		static $newRollback = true;
		static $createRollbackPoint = NULL;
		if (!is_null($createRollbackPoint)) return $createRollbackPoint;
		if ($newRollback) { // initialize new rollback
			$newRollback = false;
			if (file_exists($this->tmpDir)) {
				print "\nCreate new rollback point (y/n) ?";
				$line = trim(fgets(STDIN));
				if (strtolower($line) == 'n') {
					print "\n\nDo not create a rollback point.\n\n";
					$createRollbackPoint = false;
					return false;
				}
				Tools::remove_dir($this->tmpDir);
			}
			Tools::mkpath($this->tmpDir);
			$createRollbackPoint=true;
			return true;
		}
	}

	/**
	 * Restore the database dump from the rollback directory.
	 *
	 * @return boolean
	 */
	private function restoreDatabase() {
		global $mwrootDir;
		require_once "$mwrootDir/AdminSettings.php";
		$wgDBname = $this->getDatabasename();
		if (!file_exists($this->tmpDir."/dump.sql")) return false; // nothing to restore
		print "\n[Restore database...";

		exec("mysql -u $wgDBadminuser --password=$wgDBadminpassword --database=$wgDBname < ".$this->tmpDir."/dump.sql", $out, $ret);
		if ($ret != 0) print "\nWarning: Could not restore database."; else print "done.]";
		return ($ret == 0);
	}

	/**
	 *
	 * Reads databasename from LocalSettings.php
	 */
	private function getDatabasename() {
		global $mwrootDir;
		$ls_content = file_get_contents("$mwrootDir/LocalSettings.php");
		preg_match('/\$wgDBname\s*=\s*["\']([^"\']+)["\']/', $ls_content, $matches);
		return $matches[1];
	}




}
