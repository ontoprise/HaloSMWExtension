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

/**
 * @file
 * @ingroup DFMaintenance
 *
 * Utility class which contains various methods for maintenance operations.
 *
 * @author: Kai Kuehn / ontoprise / 2010
 */
class MaintenanceTools {

	/**
	 * Checks if there are unresolved dependencies. Returns false if there
	 * are unresolved dependencies and true if not. (Textual) details are returned
	 * in $out.
	 *
	 * @param array of DeployDescriptor $localPackages
	 * @param array of string & $out
	 * @return boolean
	 */
	public static function checkDependencies($localPackages, & $out) {
		$errorFound = false;
		$out = array();
		foreach($localPackages as $p) {
			$errorInExt = false;
			$out[$p->getID()]= array();
			$dependencies = $p->getDependencies();
			foreach($dependencies as $dep) {
				list($id, $min, $max) = $dep;

				// check if dependant package exists
				if (!array_key_exists($id, $localPackages)) {
					$out[$p->getID()][]= "Extension missing: ".$id;
					$errorInExt = true;
					$errorFound |= $errorInExt;
					continue;
				}

				// check if dependant package has correct version
				$ext = $localPackages[$id];
				if ($ext->getVersion() < $min || $ext->getVersion() > $max) {
					$out[$p->getID()][]=  "Dep. to '".$id."' has wrong version: ".$ext->getVersion().". \n\t\tExpected range $min-$max";
					$errorInExt = true;
					$errorFound |= $errorInExt;
					continue;
				}
			}
			if (!$errorInExt) $out[$p->getID()][]= NULL;
		}
		return $errorFound;
	}
}

/**
 * * Checks an installation for common consistency problems.
 *
 *  1. Unresolved dependencies
 *  2. Inconsistent LocalSettings.php entries
 *  	- existing extensions which are not registered
 *  	- registered extensions which do not exist
 *  	- double registered extension
 *  	- double require/include statements
 *  3. Special config checks
 *  	- If the rule extension is installed, the triplestore must be installed as well.
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

		print "\n\n -- CHECK INSTALLATION --\n";
		$errorFound = false;
		$errorFound |= $this->checkDependencies($repair);
		$errorFound |= $this->checkLocalSettings($repair);
		$errorFound |= $this->checkSpecialConfigs($repair);
		print "\n\n";
		return $errorFound;
	}


	/**
	 * Checks for missing dependencies of deploy descriptors.
	 *
	 * @param unknown_type $repair
	 */
	public  function checkDependencies($repair) {

		if (count($this->localPackages) == 0) {
			print "\nNO extensions found!\n";
		}

		print "\nchecking consistency of dependencies in installed packages...";
		$errorFound = MaintenanceTools::checkDependencies($this->localPackages, $out);

		if ($errorFound) {
			foreach($out as $ext => $line) {
				//if (!is_null(reset($line))) print "\n\n$ext: ";
				foreach($line as $l) {
					if (is_null($l)) break; else print "\n\t[FAILED] $ext:".$l;
				}
			}
			print "\n";
		} else {
			print "\n\tNone.";
		}
		return $errorFound;
	}

	/**
	 * Checks LocalSettings.php for common problems.
	 *
	 *
	 * @param boolean $repair Try to repair problems
	 */
	private function checkLocalSettings($repair) {

		print "\ncheck LocalSettings.php...";
		if (!file_exists($this->rootDir."/LocalSettings.php")) {
			print "\n\t[FAILED] LocalSettings.php does not exist.\n";
			if ($repair) print "\n\tCannot repair. Please create LocalSettings.php manually.";
			return true;
		}
		print "done.";

		print "\ncheck AdminSettings.php...";
		if (!file_exists($this->rootDir."/AdminSettings.php")) {
			print "\n\t[FAILED] AdminSettings.php does not exist.\n";
			if ($repair) print "\n\tCannot repair. Please create AdminSettings.php from AdminSettingsTemplate.php";
			return true;
		}
		print "done.";

		$ls = file_get_contents($this->rootDir."/LocalSettings.php");
		if (trim($ls) == '') {
			print "\n\t[FAILED]LocalSettings.php exists but is empty.\n";
			if ($repair) print "\n\tCannot repair. Please create LocalSettings.php manually.";
			return true;
		}


		print "\ncheck if existing extensions are registered in LocalSettings.php...\n";
		$errorFound1 = false;
		foreach($this->localPackages as $p) {
			if ($p->getID() == 'mw') continue;
				
			$start = strpos($ls, "/*start-".$p->getID()."*/");
			$end = strpos($ls, "/*end-".$p->getID()."*/");

			if ($start === false && $end === false) {
				print "\n\t[FAILED] ".$p->getID()." is not configured.";
				$errorFound1 = true;

				if ($repair) {
					// install extension
					print "\n\tRepair...";
					$dd = $this->localPackages[$p->getID()];
					$dp = new DeployDescriptionProcessor($this->rootDir.'/LocalSettings.php', $dd);

					$dp->applyLocalSettingsChanges($this, $dd->getUserRequirements(), false);
					$dp->applyPatches($this);
					$dp->applySetups();
					$this->lastErrors = $dp->getErrorMessages();
					print "done.";
					return;
				}
			} else {
				if ($start === false) {
					print "\n\t[FAILED] Start tag missing: ".$p->getID();
					$errorFound1 = true;
				}
				if ($end === false) {
					print "\n\t[FAILED] End tag missing: ".$p->getID();
					$errorFound1 = true;
				}
			}
		}

		if (!$errorFound1) {
			print "\n\tNone.";
		}

		print "\n\ncheck if there are registerings for non-existings extensions...";
		preg_match_all('/\/\*start-(\w+)\*\//', $ls, $matches);
		$errorFound2 = false;
		foreach ($matches[1] as $m) {
			if (!array_key_exists($m, $this->localPackages)) {
				$errorFound2 = true;
				print "\n\t[FAILED] configuration for non-existing extension detected: $m";

				if ($repair) {
					// remove config from LocalSettings.php
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
		if (!$errorFound2) {
			print "\n\tNone.";
		}


		print "\n\ncheck if there are double registered extensions...";
		$errorFound3 = false;
		preg_match_all('/\/\*start-(\w+)\*\//', $ls, $matches);
		$ext_counts = array_count_values($matches[1]);
		foreach ($ext_counts as $ext => $freq) {
			if ($freq > 1) {
				$errorFound3 = true;
				print "\n\t[FAILED] double registered extension detected: $ext";
				if ($repair) print "\n\tCannot repair automatically. Please remove '$ext' manually";
			}
		}
		if (!$errorFound3) {
			print "\n\tNone.";
		}

		print "\n\ncheck if there are double require/include statements...";
		$errorFound4 = false;
		preg_match_all('/(require|include)(_once)?\s*\(\s*["\']([^"\']*)["\']\s*\)/', $ls, $matches);
		for($i = 0; $i < count($matches[3]); $i++) $matches[3][$i] = trim($matches[3][$i]);
		$ext_counts = array_count_values($matches[3]);
		foreach ($ext_counts as $ext => $freq) {
			if ($freq > 1) {
				$errorFound4 = true;
				print "\n\t[FAILED] double registered extension detected: $ext";
				if ($repair) print "\n\tCannot repair automatically. Please remove '$ext' manually";
			}
		}
		if (!$errorFound4) {
			print "\n\tNone.";
		}

		return $errorFound1 || $errorFound2 || $errorFound3 || $errorFound4;
	}

	/**
	 * Contains several specific tests on problems already occured. Can be extended in future.
	 *
	 */
	private function checkSpecialConfigs($repair) {
		$ls = file_get_contents($this->rootDir."/LocalSettings.php");
		preg_match_all('/(require|include)(_once)?\s*\(\s*["\']([^"\']*)["\']\s*\)/', $ls, $matches);
		for($i = 0; $i < count($matches[3]); $i++) $matches[3][$i] = trim($matches[3][$i]);

		// check if semantic rule extension AND triplestore is installed.
		if (in_array("extensions/SemanticRules/includes/SR_Initialize.php", $matches[3])) {
			if (strpos($ls, "SMWTripleStore") === false) {
				$errorFound=true;
				print "\n\t[FAILED] Rule extension installed but no triplestore is active.";
				if ($repair) print "\n\tCannot activate triplestore automatically. Please take a look in the manual.";
			}
		}
	}

	/**
	 * Callback method. Requests user configs.
	 * @param has map  $userParams name=>($type, $desc)
	 * @param hash map $mapping name=>value
	 */
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

	/**
	 * Callback method. Requests a confirmation by the user.
	 *
	 *
	 * @param string $message
	 * @param out boolean $result
	 * @return unknown
	 */
	public function getUserConfirmation($message, & $result) {

		print "\n\n$message [ (y)es/(n)o ]";
		$line = trim(fgets(STDIN));
		$result = strtolower($line);
	}
}