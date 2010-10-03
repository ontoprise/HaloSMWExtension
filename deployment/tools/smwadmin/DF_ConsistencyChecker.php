<?php
/*  Copyright 2010, ontoprise GmbH
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

global $rootDir;
require_once $rootDir.'/tools/maintenance/maintenanceTools.inc';


/**
 * @file
 * @ingroup DFInstaller
 *
 * Checks an installation for common consistency problems.
 *
 *  1. Unresolved dependencies
 *  2. Inconsistent LocalSettings.php entries
 *
 * @author: Kai Kuehn / ontoprise / 2010
 */
class ConsistencyChecker {

	var $rootDir;
	var $localPackages;
	var $errorLog;

	public function __construct($rootDir) {
		$this->rootDir = $rootDir;
		$this->localPackages = PackageRepository::getLocalPackages($this->rootDir."/extensions");
	}

	static $instance;

	public static function getInstance($rootDir) {
		if (is_null(self::$instance)) {
			self::$instance = new ConsistencyChecker($rootDir);
		}
		return self::$instance;
	}

	public function checkInstallation() {
		$this->checkDependencies();
		$this->checkLocalSettings();
	}

	private  function checkDependencies() {

		if (count($this->localPackages) == 0) {
			print "\nNO extensions found!\n";
		}

		print "\nChecking consistency of dependencies in installed packages...";
		$errorFound = MaintenanceTools::checkDependencies($this->localPackages, $out);

		if ($errorFound) {
			foreach($out as $ext => $line) {
				if (!is_null(reset($line))) print "\n\n$ext: ";
				foreach($line as $l) {
					if (is_null($l)) break; else print "\n\t[FAILED] ".$l;
				}
			}
			print "\n";
		}

	}

	private function checkLocalSettings() {
		
		if (!file_exists($this->rootDir."/LocalSettings.php")) {
			print "\n\tLocalSettings.php does not exist.\n";
			return;
		}
		
		$ls = file_get_contents($this->rootDir."/LocalSettings.php");
		if (trim($ls) == '') {
			print "\n\tLocalSettings.php exists but is empty.\n";
			return;
		}
		
		// check if existing extensions are registered in LocalSettings.php
		foreach($localPackages as $p) {
			$start = strpos($ls, "/*start-".$p->getID()."*/");
			$end = strpos($ls, "/*end-".$p->getID()."*/");
			
			if ($start === false && $end === false) {
				print $p->getID()." is not configured.";
			} else {
				if ($start === false) {
					print "\nStart tag missing: ".$p->getID();
				}
				if ($end === false) {
					print "\nEnd tag missing: ".$p->getID();
				}
			}
		}
		
		// check if there are registerings for non-existings extensions
		preg_match_all('/\/\*start-(\w+)\*\//', $ls, $matches);
		foreach ($matches[1] as $m) {
			if (!array_key_exists($m, $localPackages)) {
				print "\nconfiguration for non-existing extension detected: $m";
			}
		}
	}


}