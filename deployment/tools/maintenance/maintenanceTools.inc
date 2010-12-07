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

define ("DF_OUTPUT_FORMAT_TEXT", 0);
define ("DF_OUTPUT_FORMAT_HTML", 1);


define ("DF_SMWFORUM_LINK_MISSING_DEPENDENCY", "http://smwforum.ontoprise.de");
define ("DF_SMWFORUM_LINK_MISSING_LOCALSETTINGS", "http://smwforum.ontoprise.de");
define ("DF_SMWFORUM_LINK_MISSING_ADMINSETTINGS", "http://smwforum.ontoprise.de");
define ("DF_SMWFORUM_LINK_MISSING_NON_REGISTERED", "http://smwforum.ontoprise.de");
define ("DF_SMWFORUM_LINK_MISSING_NON_EXISTING", "http://smwforum.ontoprise.de");
define ("DF_SMWFORUM_LINK_MISSING_DOUBLE_REGISTERED", "http://smwforum.ontoprise.de");


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
				list($id, $min, $max, $optional, $message) = $dep;

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
					$out[$p->getID()][]=  "Dependency to '".$id."' has wrong version: ".$ext->getVersion().". \n\t\tExpected range $min-$max";
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
	var $statusLog;

	public function __construct($rootDir) {
		$this->rootDir = $rootDir;
		$this->localPackages = PackageRepository::getLocalPackages($this->rootDir."/extensions");
		$this->statusLog = array();
	}

	static $instance;

	public static function getInstance($rootDir) {
		if (is_null(self::$instance)) {
			self::$instance = new ConsistencyChecker($rootDir);
		}
		return self::$instance;
	}

	public function getStatusLog() {
		return $this->statusLog;
	}

	public function checkInstallation($repair, $format) {


		$errorFound = false;
		$errorFound |= $this->checkDependencies($repair, $format);
		$errorFound |= $this->checkLocalSettings($repair, $format);
		$errorFound |= $this->checkSpecialConfigs($repair);
		$this->statusLog[] = $this->out("\n\n", $format);
		return $errorFound;
	}


	/**
	 * Checks for missing dependencies of deploy descriptors.
	 *
	 * @param unknown_type $repair
	 */
	public  function checkDependencies($repair, $format) {

		if (count($this->localPackages) == 0) {
			$this->statusLog[] = $this->out(array("\n","NO extensions found!", "\n"), $format);
		}

		$this->statusLog[] = $this->out(array("\n","checking consistency of dependencies in installed packages..."), $format);
		$errorFound = MaintenanceTools::checkDependencies($this->localPackages, $out);


		if ($errorFound) {
			foreach($out as $ext => $line) {
				//if (!is_null(reset($line))) $this->statusLog[] = "\n\n$ext: ";
				foreach($line as $l) {
					if (is_null($l)) break; else $this->statusLog[] = $this->out(array("\n","\t","[FAILED]", $ext.":".$l), $format);
				}
			}
			$this->statusLog[] = $this->out(array("\n"), $format);
		} else {
			$this->statusLog[] = $this->out(array("\n", "\t", "none."), $format);
		}
		if (!$errorFound) {
			$this->statusLog[] = $this->out(array("\n", "\t[OK]"), $format);

		} else {
			if ($format == DF_OUTPUT_FORMAT_HTML)    {
				// in case of HTML write a link to the SMW-Forum
				$this->statusLog[] = $this->linkout(array(DF_SMWFORUM_LINK_MISSING_DEPENDENCY, "Get help: Dependency issue"));
			}
		}
		return $errorFound;
	}

	/**
	 * Checks LocalSettings.php for common problems.
	 *
	 *
	 * @param boolean $repair Try to repair problems
	 */
	private function checkLocalSettings($repair, $format) {

		//
		// check if LocalSettings.php exists
		//
		$this->statusLog[] = $this->out(array("\n","check if LocalSettings.php exists...", "\n"), $format);
		$errorFound = !file_exists($this->rootDir."/LocalSettings.php");
		if ($errorFound) {
			$this->statusLog[] = $this->out(array("\n","\t","[FAILED]", "LocalSettings.php does not exist", "\n"), $format);

			if ($repair) $this->out(array("\n","\t","Cannot repair. Please create LocalSettings.php manually"), $format);
			return true;
		}
		$this->statusLog[] = $this->out(array("\t", "done."), $format);
		if (!$errorFound) {
			$this->statusLog[] = $this->out(array("\n", "\t[OK]"), $format);
		} else {
			if ($format == DF_OUTPUT_FORMAT_HTML)    {
				// in case of HTML write a link to the SMW-Forum
				$this->statusLog[] = $this->linkout(array(DF_SMWFORUM_LINK_MISSING_LOCALSETTINGS, "Get help: Missing LocalSettings.php"));
			}
		}

		//
		// check if AdminSettings.php exists
		//
		$this->statusLog[] = $this->out(array("\n","check if AdminSettings.php exists...", "\n"), $format);
		$errorFound =!file_exists($this->rootDir."/AdminSettings.php");
		if ($errorFound) {
			$this->statusLog[] = $this->out(array("\n","\t","[FAILED]", "AdminSettings.php does not exist", "\n"), $format);
			if ($repair) $this->out(array("\n","\t","Cannot repair. Please create AdminSettings.php from AdminSettingsTemplate.php"), $format);
			return true;
		}
		$this->statusLog[] = $this->out(array("\t", "done."), $format);
		if (!$errorFound) {
			$this->statusLog[] = $this->out(array("\n", "\t[OK]"), $format);
		}else {
			if ($format == DF_OUTPUT_FORMAT_HTML)    {
				// in case of HTML write a link to the SMW-Forum
				$this->statusLog[] = $this->linkout(array(DF_SMWFORUM_LINK_MISSING_ADMINSETTINGS, "Get help: Missing AdminSettings.php"));
			}
		}

		//
		// check if LocalSettings.php is not empty
		//
		$this->statusLog[] = $this->out(array("\n","check if LocalSettings.php is non-empty...", "\n"), $format);
		$ls = file_get_contents($this->rootDir."/LocalSettings.php");
		$errorFound = trim($ls) == '';
		if (trim($ls) == '') {
			$this->statusLog[] = $this->out(array("\n","\t","[FAILED]", "LocalSettings.php exists but is empty", "\n"), $format);
			if ($repair) $this->out(array("\n","\t","Cannot repair. Please create LocalSettings.php manually"), $format);
			return true;
		}
		$this->statusLog[] = $this->out(array("\t", "done."), $format);
		if (!$errorFound) $this->statusLog[] = $this->out(array("\n", "\t[OK]"), $format);

		//
		// check if existing extensions are registered in LocalSettings.php
		//
		$this->statusLog[] = $this->out(array("\n","check if existing extensions are registered in LocalSettings.php...","\n"), $format);
		$errorFound1 = false;
		foreach($this->localPackages as $p) {
			if ($p->getID() == 'mw') continue;

			$start = strpos($ls, "/*start-".$p->getID()."*/");
			$end = strpos($ls, "/*end-".$p->getID()."*/");

			if ($start === false && $end === false) {
				$this->statusLog[] = $this->out(array("\n","\t","[FAILED] ", $p->getID()." is not configured."), $format);
				$errorFound1 = true;


			} else {
				if ($start === false) {
					$this->statusLog[] =  $this->out(array("\n","\t","[FAILED]", "Start tag missing: ".$p->getID()), $format);
					$errorFound1 = true;
				}
				if ($end === false) {
					$this->statusLog[] =  $this->out(array("\n","\t","[FAILED]", "End tag missing: ".$p->getID()), $format);
					$errorFound1 = true;
				}
			}
		}

		if (!$errorFound1) {
			$this->statusLog[] = "\n\tnone.";
			$this->statusLog[] = $this->out(array("\n", "\t[OK]"), $format);
		} else {
			if ($format == DF_OUTPUT_FORMAT_HTML)    {
				// in case of HTML write a link to the SMW-Forum
				$this->statusLog[] = $this->linkout(array(DF_SMWFORUM_LINK_MISSING_NON_REGISTERED, "Get help: Non-registered extensions"));
			}
		}

		//
		// check if there are registerings for non-existings extensions
		//
		$this->statusLog[] = $this->out(array("\n","\n","check if there are registerings for non-existings extensions..."), $format);
		preg_match_all('/\/\*start-(\w+)\*\//', $ls, $matches);
		$errorFound2 = false;
		foreach ($matches[1] as $m) {
			if (!array_key_exists($m, $this->localPackages)) {
				$errorFound2 = true;
				$this->statusLog[] = $this->out(array("\n","\t","[FAILED]","configuration for non-existing extension detected: $m"), $format);

				if ($repair) {
					// remove config from LocalSettings.php
					$this->statusLog[] = "\n\tRepair...";
					$start = strpos($ls, '/*start-'.$m.'*/');
					$end = strpos($ls, '/*end-'.$m.'*/') + strlen('/*end-'.$m.'*/');
					$ls = substr_replace($ls, "", $start, $end-$start);
					$this->statusLog[] = $this->out(array("\n","done."), $format);
				}
			}
		}
		if ($repair) {
			$handle = fopen($this->rootDir."/LocalSettings.php", "wb");
			fwrite($handle, $ls);
			fclose($handle);
		}
		if (!$errorFound2) {
			$this->statusLog[] = $this->out(array("\n","\t","none."), $format);
			$this->statusLog[] = $this->out(array("\n", "\t[OK]"), $format);
		} else {
		 if ($format == DF_OUTPUT_FORMAT_HTML)    {
		 	// in case of HTML write a link to the SMW-Forum
		 	$this->statusLog[] = $this->linkout(array(DF_SMWFORUM_LINK_MISSING_NON_EXISTING, "Get help: Non-existing extensions"));
		 }
		}

		//
		// check if there are double registered extensions
		//
		$this->statusLog[] = $this->out(array("\n","\n","check if there are double registered extensions..."), $format);
		$errorFound3 = false;
		preg_match_all('/\/\*start-(\w+)\*\//', $ls, $matches);
		$ext_counts = array_count_values($matches[1]);
		foreach ($ext_counts as $ext => $freq) {
			if ($freq > 1) {
				$errorFound3 = true;
				$this->statusLog[] = $this->out(array("\n","\t","[FAILED]","double registered extension detected: $ext"), $format);
				if ($repair) $this->statusLog[] = $this->out(array("\n","\t","Cannot repair automatically. Please remove '$ext' manually"), $format);
			}
		}
		if (!$errorFound3) {
			$this->statusLog[] = $this->out(array("\n","\t","none."), $format);
			$this->statusLog[] = $this->out(array("\n", "\t[OK]"), $format);
		} else {
			if ($format == DF_OUTPUT_FORMAT_HTML) {
				// in case of HTML write a link to the SMW-Forum
				$this->statusLog[] = $this->linkout(array(DF_SMWFORUM_LINK_MISSING_DOUBLE_REGISTERED, "Get help: Double registered extensions"));
			}
		}

		//
		// check if there are double require/include statements
		//
		$this->statusLog[] = $this->out(array("\n","\n","check if there are double require/include statements..."), $format);
		$errorFound4 = false;
		preg_match_all('/(require|include)(_once)?\s*\(\s*["\']([^"\']*)["\']\s*\)/', $ls, $matches);
		for($i = 0; $i < count($matches[3]); $i++) $matches[3][$i] = trim($matches[3][$i]);
		$ext_counts = array_count_values($matches[3]);
		foreach ($ext_counts as $ext => $freq) {
			if ($freq > 1) {
				$errorFound4 = true;
				$this->statusLog[] = $this->out(array("\n","\t","[FAILED]"," double registered extension detected: $ext"), $format);
				if ($repair) $this->statusLog[] = $this->out(array("\n","\t","Cannot repair automatically. Please remove '$ext' manually"), $format);
			}
		}
		if (!$errorFound4) {
			$this->statusLog[] = $this->out(array("\n","\t","none."), $format);
			$this->statusLog[] = $this->out(array("\n", "\t[OK]"), $format);
		} else {
			if ($format == DF_OUTPUT_FORMAT_HTML) {
				// in case of HTML write a link to the SMW-Forum
				$this->statusLog[] = $this->linkout(array(DF_SMWFORUM_LINK_MISSING_DOUBLE_REGISTERED, "Get help: Double registered extensions"));
			}
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
				$this->statusLog[] = $this->out(array("\n","\t","[FAILED]","Rule extension installed but no triplestore is active."), $format);
				if ($repair) $this->statusLog[] = $this->out(array("\n","\t","Cannot activate triplestore automatically. Please take a look in the manual."), $format);
			}
		}
	}


	/**
	 * Prints out a status log message.
	 *
	 * @param array of string $text
	 * @param string $format DF_OUTPUT_FORMAT_TEXT or DF_OUTPUT_FORMAT_HTML
	 */
	private function out($text, $format) {
		switch($format) {
			case DF_OUTPUT_FORMAT_TEXT:
				return is_array($text) ? implode("", $text) : $text;
				break;
			case DF_OUTPUT_FORMAT_HTML:
				if (is_array($text)) {
					for($i = 0; $i < count($text); $i++){
						$text[$i] = str_replace("\n", "<br>", $text[$i]);
						$text[$i] = str_replace("\t", '<div style="display: inline; margin-left: 10px;"></div>', $text[$i]);
						$text[$i] = str_replace("[FAILED]", '<span class="df_checkinst_error">[FAILED]</span>', $text[$i]);
						$text[$i] = str_replace("[OK]", '<span class="df_checkinst_ok">[OK]</span>', $text[$i]);
					}
					return implode("", $text);
				} else return $text;
				break;
		}
	}

	/**
	 * Creates an HTML link.
	 * 
	 * @param array $link
	 */
	private function linkout($link) {
		list($href, $label) = $link;
		return "<div class=\"df_checkinst_links\"><a href=\"".htmlspecialchars($href)."\">$label</a></div>";
	}
}