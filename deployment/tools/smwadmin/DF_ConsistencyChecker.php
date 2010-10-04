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
require_once($rootDir."/tools/smwadmin/DF_PackageRepository.php");
require_once($rootDir."/tools/smwadmin/DF_Tools.php");
require_once($rootDir."/tools/maintenance/maintenanceTools.inc");

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

	public function checkInstallation($repair) {

		print "\n";
		$errorFound = false;
		$errorFound |= $this->checkDependencies($repair);
		$errorFound |= $this->checkLocalSettings($repair);
		$errorFound |= $this->checkSpecialConfigs($repair);
		print "\n\n";
		return $errorFound;
	}

	private  function checkDependencies($repair) {

		if (count($this->localPackages) == 0) {
			print "\nNO extensions found!\n";
		}

		print "\nchecking consistency of dependencies in installed packages...";
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
		return $errorFound;
	}

	private function checkLocalSettings($repair) {

		print "\ncheck LocalSettings.php...";
		if (!file_exists($this->rootDir."/LocalSettings.php")) {
			print "\n\t[FAILED] LocalSettings.php does not exist.\n";
			return true;
		}
		print "\ncheck AdminSettings.php...";
		if (!file_exists($this->rootDir."/AdminSettings.php")) {
			print "\n\t[FAILED] AdminSettings.php does not exist.\n";
			return true;
		}

		$ls = file_get_contents($this->rootDir."/LocalSettings.php");
		if (trim($ls) == '') {
			print "\n\t[FAILED]LocalSettings.php exists but is empty.\n";
			return true;
		}


		print "\ncheck if existing extensions are registered in LocalSettings.php...\n";
		$errorFound = false;
		foreach($this->localPackages as $p) {
			$start = strpos($ls, "/*start-".$p->getID()."*/");
			$end = strpos($ls, "/*end-".$p->getID()."*/");
				
			if ($start === false && $end === false) {
				print "\n\t[FAILED] ".$p->getID()." is not configured.";
				$errorFound = true;
				
				if ($repair) {
					print "\n\tRepair...";
					$dd = $this->localPackages[$p->getID()];
					$dp = new DeployDescriptionProcessor($this->rootDir.'/LocalSettings.php', $dd);

					$dp->applyLocalSettingsChanges($this, $dd->getUserRequirements(), false);
					return;
					//$dp->applyPatches($userCallback);
					//$dp->applySetups();
					$this->lastErrors = $dp->getErrorMessages();
					print "done.";
				}
			} else {
				if ($start === false) {
					print "\n\t[FAILED] Start tag missing: ".$p->getID();
					$errorFound = true;
				}
				if ($end === false) {
					print "\n\t[FAILED] End tag missing: ".$p->getID();
					$errorFound = true;
				}
			}
		}

		print "\n\ncheck if there are registerings for non-existings extensions";
		preg_match_all('/\/\*start-(\w+)\*\//', $ls, $matches);
		foreach ($matches[1] as $m) {
			if (!array_key_exists($m, $this->localPackages)) {
				$errorFound = true;
				print "\n\t[FAILED] configuration for non-existing extension detected: $m";
				
				if ($repair) {
					print "\n\tRepair...";
					$start = strpos($ls, '/*start-'.$m.'*/');
					$end = strpos($ls, '/*end-'.$m.'*/') + strlen('/*end-'.$m.'*/');
					$ls = substr_replace($ls, "", $start, $end-$start);
					print "done.";
				}
			}
		}
		if ($repair) {
			$handle = fopen($this->rootDir."/LocalSettings.php", "wb");
			fwrite($handle, $ls);
			fclose($handle);
		}
		
		
		print "\n\ncheck if there are double registered extensions";
		preg_match_all('/\/\*start-(\w+)\*\//', $ls, $matches);
		$ext_counts = array_count_values($matches[1]);
		foreach ($ext_counts as $ext => $freq) {
			if ($freq > 1) {
				$errorFound = true;
				print "\n\t[FAILED] double registered extension detected: $ext";
			}
		}
		
		
		print "\n\ncheck if there are double require/include statements";
		preg_match_all('/(require|include)(_once)?\s*\(\s*["\']([^"\']*)["\']\s*\)/', $ls, $matches);
		for($i = 0; $i < count($matches[3]); $i++) $matches[3][$i] = trim($matches[3][$i]);
		$ext_counts = array_count_values($matches[3]);
		foreach ($ext_counts as $ext => $freq) {
			if ($freq > 1) {
				$errorFound = true;
				print "\n\t[FAILED] double registered extension detected: $ext";
			}
		}
		
		
		return $errorFound;
	}
	
	/**
	 * Contains several specific tests on problems already occured. Can be extended in future.
	 * 
	 */
	private function checkSpecialConfigs($repair) {
		$ls = file_get_contents($this->rootDir."/LocalSettings.php");
		preg_match_all('/(require|include)(_once)?\s*\(\s*["\']([^"\']*)["\']\s*\)/', $ls, $matches);
		for($i = 0; $i < count($matches[3]); $i++) $matches[3][$i] = trim($matches[3][$i]);
		if (in_array("extensions/SemanticRules/includes/SR_Initialize.php", $matches[3])) {
			if (strpos($ls, "SMWTripleStore") === false) {
				$errorFound=true;
				print "\n\t[FAILED] Rule extension installed but no triplestore is active.";
			}
		}
	}

public function getUserReqParams($userParams, & $mapping) {
		if (count($userParams) == 0) return;
		print "\n\nRequired parameters:";
		foreach($userParams as $name => $up) {
			list($type, $desc) = $up;
			print "\n$desc\n";
			print "$name ($type): ";
			$line = trim(fgets(STDIN));
			$line = str_replace("\\", "/", $line); // do not allow backslashes
			$mapping[$name] = $line;
		}

	}
}