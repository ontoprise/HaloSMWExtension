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
 * @ingroup DFDeployDescriptor
 *
 * Applies changes to the the LocalSettings.php.
 * Modifications are specified in the deploy descriptor.
 *
 *  @author: Kai K�hn
 *
 */

class DeployDescriptionProcessor {

	// Location of LocalSettings file
	private $ls_loc;

	// content of LocalSettings file
	private $localSettingsContent;

	// Deploy descriptor used
	private $dd_parser;

	// error messages which occur during processing
	private $errorMessages;

	private $logger;

	// PHP executable
	private $phpExe;

	/**
	 * Creates new DeployDescriptorProcessor.
	 *
	 * @param string $ls_loc Location of LocalSettings
	 * @param DeployDescriptor $dd_parser
	 *
	 */
	function __construct($ls_loc, $dd_parser) {
		$this->dd_parser = $dd_parser;
		$this->errorMessages = array();
		$this->ls_loc = $ls_loc;
		$this->logger = Logger::getInstance();
		$this->phpExe = 'php';
		if (array_key_exists('df_php_executable', DF_Config::$settings) && !empty(DF_Config::$settings['df_php_executable'])) {
			$this->phpExe = DF_Config::$settings['df_php_executable'];
		}
		if (file_exists($ls_loc)) {
			$this->localSettingsContent = file_get_contents($ls_loc);
			// strip php endtag (if existing)
			$phpEndTag = strpos($this->localSettingsContent, "?>");
			if ($phpEndTag !== false) {
				$this->localSettingsContent = substr($this->localSettingsContent, 0, $phpEndTag);
			}
			return;
		}
		throw new IllegalArgument("$ls_loc does not exist!");
	}

	/**
	 * Reads the LocalSettings.php file, applies changes and return it as string.
	 *
	 * @param callback $userCallback Callback function which asks for user requirements. Returns hash array ($nameConfigElement => $value)
	 * @param hash array ($nameConfigElement => array($type, $description)) User requirements
	 * @param boolean $dryRun Dry run or actual change.
	 * @return string changed LocalSettings.php file
	 */
	function applyLocalSettingsChanges($userCallback, $userRequirements, $dryRun) {
		$userValues = array();

		if (!is_null($userCallback)) {
			$userCallback->getUserReqParams($userRequirements, $userValues);
		}
		// calculate changes
		$insertions = ""; // reset
			
		foreach($this->dd_parser->getConfigs() as $ce) {
			$insertions .= $ce->apply($this->localSettingsContent, $this->dd_parser->getID(), $userValues);
		}
		list($insertpos, $ext_found) = $this->getInsertPosition($this->dd_parser->getID());

		if ($ext_found && $this->dd_parser->doRemoveAllConfigs()) {
			// extension exists but all configs should be replaced
			$prefix = substr($this->localSettingsContent, 0 , strpos($this->localSettingsContent, "/*start-".$this->dd_parser->getID()."*/") + strlen("/*start-".$this->dd_parser->getID()."*/"));
			$suffix = substr($this->localSettingsContent, strpos($this->localSettingsContent, "/*end-".$this->dd_parser->getID()."*/") );
			$startTag = "";
			$endTag = "";
		} else {
			// add new configblock or append to existing
			$prefix = substr($this->localSettingsContent, 0 , $insertpos);
			$suffix = substr($this->localSettingsContent, $insertpos);
			$startTag = $ext_found ? "" : "\n/*start-".$this->dd_parser->getID()."*/";
			$endTag = $ext_found ? "" : "\n/*end-".$this->dd_parser->getID()."*/\n";
		}
		$this->localSettingsContent = $prefix . $startTag . $insertions . $endTag . $suffix;

		$this->logger->info("Added to LocalSettings.php: $startTag . $insertions . $endTag");
		if (!$dryRun) $this->writeLocalSettingsFile();
		return $this->localSettingsContent;
	}

	/**
	 * Unapplies changes to LocalSettings.php by reversing the operations.
	 *
	 * @return string changed LocalSettings.php file
	 */
	function unapplyLocalSettingsChanges() {
		//TODO: external variables get not re-set. hard to fix.
		$fragment = ConfigElement::getExtensionFragment($this->dd_parser->getID(), $this->localSettingsContent);

		if (is_null($fragment)) {
			//FIXME: introduce exceptions here
			$this->logger->error("Could not find configuration for ".$this->dd_parser->getID());
			$this->errorMessages[] = "Could not find configuration for ".$this->dd_parser->getID();
			echo "\n\tCould not find configuration for ".$this->dd_parser->getID();
			echo "\n\tAbort changing LocalSettings.php...";
			return $this->localSettingsContent;
		}
		$this->logger->info("Remove from LocalSettings.php: $fragment");
		$this->localSettingsContent = str_replace($fragment, "", $this->localSettingsContent);
		$this->localSettingsContent = Tools::removeTrailingWhitespaces($this->localSettingsContent);
		$this->writeLocalSettingsFile();
		return $this->localSettingsContent;
	}

	function getConfigFragment($extid) {

		if (is_null($extid)) {
			return $this->localSettingsContent;
		}

		$start = strpos($this->localSettingsContent, "/*start-$extid*/");
		$end = strpos($this->localSettingsContent, "/*end-$extid*/");

		if ($start === false || $end === false) return NULL;

		$start += strlen("/*start-$extid*/") + 1;

		return substr($this->localSettingsContent, $start, $end - $start);
	}

	function replaceConfigFragment($extid, $replacement) {
		if (is_null($extid)) {
			$this->localSettingsContent =$replacement;
		} else {
			$fragment = $this->getConfigFragment($extid);
			if (is_null($fragment)) return false;
			$this->localSettingsContent = str_replace($fragment, $replacement, $this->localSettingsContent);
		}
		return $this->localSettingsContent;
	}

	/**
	 * Runs the given setup scripts.
	 *
	 * Note: Needs php interpreter in PATH. Must be checked beforehand.
	 *
	 */
	function applySetups() {
		global $dfgOut;
		$rootDir = self::makeUnixPath(dirname($this->ls_loc));
		foreach($this->dd_parser->getInstallScripts() as $setup) {
			$instDir = trim(self::makeUnixPath($this->dd_parser->getInstallationDirectory()));
			if (substr($instDir, -1) != '/') $instDir .= "/";
			$script = $instDir.self::makeUnixPath($setup['script']);
			if (!file_exists($rootDir."/".$script)) {
				$this->errorMessages[] = "WARNING: setup script at '$rootDir/$script' does not exist";
				$dfgOut->outputln("\tsetup script at '$rootDir/$script' does not exist", DF_PRINTSTREAM_TYPE_WARN);
				continue;
			}
			$this->logger->info("Run script: $script");
			$dfgOut->outputln("[Run script: $script");
			exec("\"$this->phpExe\" \"".$rootDir."/".$script."\" ".$setup['params'], $out, $ret);
			$dfgOut->output( "done.]");
			foreach($out as $line) $dfgOut->outputln($line);
			if ($ret != 0) {
				$this->logger->error("Script: '$script' failed.");
				$this->errorMessages[] = "Script ".$rootDir."/".$script." failed!";
				$dfgOut->outputln("\tScript ".$rootDir."/".$script." failed!");
				throw new RollbackInstallation();
			}
			$out = array(); // delete output
		}
	}

	/**
	 * Runs the given setup scripts in de-install mode.
	 *
	 * Note: Needs php interpreter in PATH. Must be checked beforehand.
	 *
	 */
	function unapplySetups() {
		global $dfgOut;
		$rootDir = self::makeUnixPath(dirname($this->ls_loc));
		foreach($this->dd_parser->getUninstallScripts() as $setup) {
			$instDir = trim(self::makeUnixPath($this->dd_parser->getInstallationDirectory()));
			if (substr($instDir, -1) != '/') $instDir .= "/";
			$script = $instDir.self::makeUnixPath($setup['script']);
			if (!file_exists($rootDir."/".$script)) {
				$this->errorMessages[] = "Warning: setup script at '$rootDir/$script' does not exist";
				$dfgOut->outputln( "setup script at '$rootDir/$script' does not exist", DF_PRINTSTREAM_TYPE_WARN);
				continue;
			}
			$dfgOut->outputln("[Run script: $script");
			exec("\"$this->phpExe\" \"".$rootDir."/".$script."\" ".$setup['params'], $out, $ret);
			$dfgOut->output( "done.]");
			foreach($out as $line) $dfgOut->outputln($line);
			if ($ret != 0) {
				$this->errorMessages[] = "Script ".$rootDir."/".$script." failed!";
				$dfgOut->outputln( "\tScript ".$rootDir."/".$script." failed!",DF_PRINTSTREAM_TYPE_ERROR );
				throw new RollbackInstallation();
			}

		}
	}

	/**
	 * Applies patches
	 *
	 * Note: Needs php Interpreter and GNU-patch in PATH. Must be checked beforehand.
	 *
	 * @param callback $userCallback Callback function for user confirmation. Returns 'y' or 'n'.
	 */
	function applyPatches($userCallback, $patchesToSkip = array()) {
		$rootDir = self::makeUnixPath(dirname($this->ls_loc));
		$localPackages = PackageRepository::getLocalPackages($rootDir, true);

		foreach($this->dd_parser->getPatches($localPackages) as $patchObject) {
			$this->applyPatch($patchObject, $userCallback, $patchesToSkip);
		}
	}

	function applyPatch($patchObject, $userCallback, $patchesToSkip = array()) {
		global $dfgOut;
		$rootDir = self::makeUnixPath(dirname($this->ls_loc));
		$patch = $patchObject->getPatchfile();
		$mayfail = $patchObject->mayFail();
		$instDir = trim(self::makeUnixPath($this->dd_parser->getInstallationDirectory()));
		if (substr($instDir, -1) != '/') $instDir .= "/";
		$patch = $instDir.self::makeUnixPath($patch);

		if (in_array($patchObject->getPatchfile(), $patchesToSkip)) return;
		$patchFailed = false;
		if (!file_exists($rootDir."/".$patch)) {
			$this->errorMessages[] = "WARNING: patch at '$rootDir/$patch' does not exist";
			$dfgOut->outputln( "patch at '$rootDir/$patch' does not exist", DF_PRINTSTREAM_TYPE_WARN);
			return;
		}
		// do dry-run at first to check for rejected patches
		$dfgOut->outputln("[Test patch ".$patch."...");
		// give exact path to patch.exe in Windows, don't do so on linux
		$patchtool = Tools::isWindows() ? "--patchtool \"".$rootDir."/deployment/tools/patch.exe\"" : "";
		exec("\"$this->phpExe\" \"".$rootDir."/deployment/tools/patch.php\" -p \"".$rootDir."/".$patch."\" -d \"".$rootDir."\" --dry-run --onlypatch $patchtool", $out, $ret);
		$dfgOut->output( "done.]");
		$patchFailed = false;

		$filesOfNotFoundPatches = $this->checkPatchesByHeuristic($rootDir."/".$patch);
		$filteredOut = array();
		foreach($out as $line) {
			if (!Tools::inStringArray($filesOfNotFoundPatches, $line)) {
				// if a FAILED patch is not found in $filesOfNotFoundPatches
				// it is assumed that is was correctly applied. Then there is
				// no need to show the error to the user.
				continue;
			}
			$filteredOut[] = $line;
			if (strpos($line, "FAILED") !== false) {
				$patchFailed = true;
			}
		}

		// ask user to continue/rollback in case of failed patches
		$result = 'y';
		if (!is_null($userCallback) && $patchFailed) {

			if (count($filteredOut) > 0) {
				foreach($filteredOut as $line) $dfgOut->outputln($line); // show failures
				$dfgOut->outputln();
				global $dfgGlobalOptionsValues;
				if (array_key_exists('df_watsettings_apply_patches', $dfgGlobalOptionsValues)) {
					$result = $dfgGlobalOptionsValues['df_watsettings_apply_patches'] ? 'y' : 'n';
				} else {
					$userCallback->getUserConfirmation("Some patches failed. Apply anyway?", $result);
				}
			}
		}

		switch($result) {

			case 'y': // apply the patches
				$dfgOut->outputln("[Apply patch...");
				$this->logger->info("Apply patch: $patch");
				exec("\"$this->phpExe\" \"".$rootDir."/deployment/tools/patch.php\" -p \"".$rootDir."/".$patch."\" -d \"".$rootDir."\" --onlypatch $patchtool", $out, $ret);
				if ($ret !== 0) {
					$this->logger->warn("Patch failed: '$patch'. Output: ".Tools::arraytostring($out));
				}
				$dfgOut->output( "done.]");
				break;
			case 'r': throw new RollbackInstallation();
			case 'n': break; // just ignore the patches completely
		}

		// clear patch.php output
		$out = array();


	}



	/**
	 * Checks the patches in the given patch file by a heuristic.
	 * Halo patches have markers. If a marker is found in the patched
	 * file, it is assumed that the patch was successfully applied.
	 *
	 * If no marker is found, the patch is ignored.
	 *
	 * @param string $patchFile Absolute path
	 * @return string[] All files which are *not* patched but should according to $patchFile
	 */
	private function checkPatchesByHeuristic($patchFile) {
		$rootDir = self::makeUnixPath(dirname($this->ls_loc));
		$patchFileContent = file_get_contents($patchFile);
		$patches = preg_split('/Index:\s+(.+)[\n|\r\n]+=+[\n|\r\n]+/', $patchFileContent);
		$allPatchesFound = true;
		$filesOfNotFoundPatches = array();
		foreach($patches as $p) {
			if ($p == '') continue;
			preg_match('/\+\+\+\s+([^\s]+)/', $p, $patchesFiles);
			$relPath=$patchesFiles[1];
			preg_match_all('/\/\*op-patch\|([^*]+)\*\//', $p, $patchHints);
			$absPath = "$rootDir/$relPath";
			$codeFileContent = file_get_contents($absPath);
			foreach($patchHints[1] as $hint) {
				$patchFound = (strpos($codeFileContent, $hint) !== false);
				if (!$patchFound) $filesOfNotFoundPatches[] = basename($relPath);
				$allPatchesFound = $allPatchesFound & $patchFound;
			}
		}
		return array_unique($filesOfNotFoundPatches);
	}




	/**
	 * Removes patches
	 *
	 * Note: Needs php Interpreter and GNU-patch in PATH. Must be checked beforehand.
	 *
	 *
	 */
	function unapplyPatches() {
		global $dfgOut;
		$rootDir = self::makeUnixPath(dirname($this->ls_loc));
		$localPackages = PackageRepository::getLocalPackages($rootDir);
		foreach($this->dd_parser->getUninstallPatches($localPackages) as $patchObject) {
			$patch = $patchObject->getPatchfile();
			$instDir = trim(self::makeUnixPath($this->dd_parser->getInstallationDirectory()));
			if (substr($instDir, -1) != '/') $instDir .= "/";
			$patch = $instDir.self::makeUnixPath($patch);
			if (!file_exists($rootDir."/".$patch)) {
				$this->errorMessages[] = "WARNING: patch at '$rootDir/$patch' does not exist";
				$dfgOut->outputln("\tpatch at '$rootDir/$patch' does not exist", DF_PRINTSTREAM_TYPE_WARN);
				continue;
			}
			// do dry-run at first to check for rejected patches
			// give exact path to patch.exe in Windows, don't do so on linux
			$patchtool = Tools::isWindows() ? "--patchtool \"".$rootDir."/deployment/tools/patch.exe\"" : "";
			exec("\"$this->phpExe\" \"".$rootDir."/deployment/tools/patch.php\" -r -p \"".$rootDir."/".$patch."\" -d \"".$rootDir."\" --dry-run --onlypatch $patchtool", $out, $ret);
			$patchFailed = false;
			foreach($out as $line) {
				if (strpos($line, "FAILED") !== false) {
					$patchFailed = true;
				}
			}
			if ($patchFailed) $dfgOut->outputln("\tSome patches can not be removed! Reject files are created.", DF_PRINTSTREAM_TYPE_WARN) ;
			$this->logger->info("Unapply patch: $patch");
			$dfgOut->outputln("\t[Remove patch $patch...");
			exec("\"$this->phpExe\" \"".$rootDir."/deployment/tools/patch.php\" -r -p \"".$rootDir."/".$patch."\" -d \"".$rootDir."\" $patchtool", $out, $ret);
			if ($ret !== 0) {
				$this->logger->warn("Patch failed: '$patch'. Output: ".Tools::arraytostring($out));
			}
			$dfgOut->output( "done.]");

			// clear patch.php output
			$out = array();
		}
	}

	/**
	 * Checks if patches are already applied. Returns a list of patches of this deployable
	 * already applied.
	 *
	 * @param (out) array & $alreadyApplied List of patch files (paths relative to MW folder).
	 */
	function checkIfPatchesAlreadyApplied(& $alreadyApplied) {
		$rootDir = self::makeUnixPath(dirname($this->ls_loc));
		$localPackages = PackageRepository::getLocalPackages($rootDir, true);

		foreach($this->dd_parser->getPatches($localPackages) as $patchObject) {
			$applied = array();
			$this->checkIfPatchAlreadyApplied($patchObject, $applied);
			$alreadyApplied = array_merge($alreadyApplied, $applied);
		}
	}

	function checkIfPatchAlreadyApplied($patchObject, & $alreadyApplied) {
		global $dfgOut;
		$rootDir = self::makeUnixPath(dirname($this->ls_loc));
		$patch = $patchObject->getPatchfile();
		$mayfail = $patchObject->mayFail();
		$instDir = trim(self::makeUnixPath($this->dd_parser->getInstallationDirectory()));
		if (substr($instDir, -1) != '/') $instDir .= "/";
		$patch = $instDir.self::makeUnixPath($patch);
		$patchFailed = false;
		$out = array();
		if (!file_exists($rootDir."/".$patch)) {
			$this->errorMessages[] = "WARNING: patch at '$rootDir/$patch' does not exist";
			$dfgOut->outputln( "patch at '$rootDir/$patch' does not exist", DF_PRINTSTREAM_TYPE_WARN);
			return;
		}
		// do dry-run at first to check for rejected patches
		// give exact path to patch.exe in Windows, don't do so on linux
		$patchtool = Tools::isWindows() ? "--patchtool \"".$rootDir."/deployment/tools/patch.exe\"" : "";
		$dfgOut->outputln("[Check if patch is already applied ".$patch."...");
		exec("\"$this->phpExe\" \"".$rootDir."/deployment/tools/patch.php\" -r -p \"".$rootDir."/".$patch."\" -d \"".$rootDir."\" --dry-run --onlypatch $patchtool", $out, $ret);
		$dfgOut->output( "done.]");
		$patchFailed = false;

		foreach($out as $line) {
			if (strpos($line, "FAILED") !== false) {
				$patchFailed = true;
			}
		}

		if (!$patchFailed) {
			$dfgOut->outputln("[$patch seems to be applied already. Skipped]");
			$alreadyApplied[] = $patchObject->getPatchfile();
		}
	}

	/**
	 * Writes LocalSettings.php
	 *
	 */
	function writeLocalSettingsFile() {
		if (empty($this->localSettingsContent)) {
			$this->errorMessages[] = "WARNING: LocalSettings.php is empty. Nothing done here.";
			// do never write an empty localsettings file.
			return;
		}
		$handle = fopen($this->ls_loc, "wb");
		fwrite($handle, $this->localSettingsContent);
		fclose($handle);
	}

	/**
	 * Returns errormessages which occured during processing.
	 *
	 * @return array of string
	 */
	function getErrorMessages() {
		return $this->errorMessages;
	}

	/*
	 * Calculates the insert position. Normallay at the end but not if successors must be considered.
	 *
	 * If there is already an extension with the $ext_id, return (insertPosition, true) otherwise (insertPosition, false)
	 */
	private function getInsertPosition($ext_id) {
		$MAXINT = pow(2,32);
		$maximumInsert = $MAXINT;

		$pos = strpos($this->localSettingsContent, "/*end-$ext_id*/");

		if ($pos === false) {
			foreach($this->dd_parser->getSuccessors() as $extensionID) {
				$pos = strpos($this->localSettingsContent, "/*start-$extensionID*/");
				if ($pos === false) continue;
				$maximumInsert = $pos < $maximumInsert ? $pos : $maximumInsert;
			}
			$ext_found = false;
			$pos = $maximumInsert == $MAXINT ? strlen($this->localSettingsContent) : $maximumInsert;
			return array($pos, $ext_found);
		}
		$ext_found = true;
		return array($pos-1, $ext_found);
	}

	private static function makeUnixPath($path) {
		return str_replace("\\", "/", $path);
	}
}



/**
 * Represents a configuration change in the localsettings file.
 *
 * @author: Kai K�hn
 *
 */
abstract class ConfigElement {
	var $type;
	public function __construct($type) { $this->type = $type; }
	public function getType() { return $this->type; }

	/**
	 * Applies the changes described by the config element to the local settings.
	 *
	 * @param string $ls LocalSettings text
	 * @param string $ext_id Extension_ID
	 * @param array $userValues Hash arrays with user values for required variables.
	 */
	public abstract function apply(& $ls, $ext_id, $userValues = array());


	/**
	 * Returns the configuration fragement of the extension. The text between
	 *
	 *     start-$ext_id ... end-$ext_id
	 *
	 * @param string $ext_id extension ID
	 * @param string $ls localsettings text
	 * @return string Fragment or NULL if it does not exist.
	 */
	public static function getExtensionFragment($ext_id, & $ls) {
		$start = strpos($ls, "/*start-$ext_id*/");
		$end = strpos($ls, "/*end-$ext_id*/");
		if ($start === false || $end === false) {
			return NULL; // fragment does not exist
		}
		return substr($ls, $start, $end-$start+strlen("/*end-$ext_id*/"));
	}

	/**
	 *  Replaces the configuration fragement of the extension by the given.
	 *
	 * @param striing $ext_id
	 * @param string $fragment new fragment
	 * @param string $ls localsettings text
	 */
	protected function replaceExtensionFragment($ext_id, $fragment, & $ls) {
		$start = strpos($ls, "/*start-$ext_id*/");
		$end = strpos($ls, "/*end-$ext_id*/");
		if ($start === false || $end === false) {
			throw new IllegalArgument("$ext_id is not installed.");
		}
		$ls = substr($ls, 0, $start). $fragment . substr($ls, $end + strlen("/*end-$ext_id*/"));
	}



	/**
	 * Serializes arguments of a PHP function.
	 *
	 * @param SimpleXMLElement $child Config element as XML object
	 * @param array $mappings Values to be set (name=>value)
	 */
	protected function serializeParameters($child, $mappings = array()) {
		$resultsStr="";
		$children = $child->children();

		foreach($children as $ch) {
			switch($ch->getName()) {
				case "string": {
					$name = (string) $ch->attributes()->name;
					$key = (string) $ch->attributes()->key;
					$null = (string) $ch->attributes()->null;
					$p = array_key_exists($name, $mappings) ? $mappings[$name] : (string) $ch[0];
					$p = (strtolower($null) === "true") ? "NULL" : "'".$p."'";

					$key = $key != NULL ? "'$key'=>" : "";
					$resultsStr .= ($resultsStr == "" ? "$key$p" : ", $key$p");break;
				}
				case "number": {
					$name = (string) $ch->attributes()->name;
					$key = (string) $ch->attributes()->key;
					$null = (string) $ch->attributes()->null;
					$p = array_key_exists($name, $mappings) ? $mappings[$name] : (string) $ch[0];
					$p = (strtolower($null) === "true") ? "NULL" : $p;
					$key = $key != NULL ? "'$key'=>" : "";
					$resultsStr .= ($resultsStr == "" ? "$key $p" : ", $key $p");break;
				}
				case "boolean": {
					$name = (string) $ch->attributes()->name;
					$key = (string) $ch->attributes()->key;
					$null = (string) $ch->attributes()->null;
					$p = array_key_exists($name, $mappings) ? $mappings[$name] : (string) $ch[0];
					$p = (strtolower($null) === "true") ? "NULL" : $p;
					$key = $key != NULL ? "'$key'=>" : "";
					$resultsStr .= ($resultsStr == "" ? "$key $p" : ", $key $p");break;
				}
				case "array": {
					$p = $this->serializeParameters($ch, $mappings);
					$resultsStr .=  $resultsStr == "" ? "array($p)" : ", array($p)";
				}
			}
		}
		return $resultsStr;
	}

	/**
	 * Parses a PHP array content and interprete it using the XML representation.
	 *
	 * @param SimpleXMLElement $child XML represenation of a function argument list or a variable value
	 * @param string $phpArgString php argument string
	 * @return array $mappings Mappings name=>value
	 */
	protected function deserialize($child, $phpArgString) {
		$phpArg = eval('return array('.$phpArgString.');');

		$mappings = array();
		$this->_deserialize($child, $phpArg, $mappings);
		return $mappings;
	}

	private function _deserialize($child, $phpArg, & $mappings) {
		$children = $child->children();
		$i = 0;
		foreach($children as $ch) {
			switch($ch->getName()) {
				case "string": {
					$name = (string) $ch->attributes()->name;
					$key = (string) $ch->attributes()->key;
					$p = $phpArg[($key != NULL ? $key : $i)];
					$mappings[$name] = $p;break;
				}
				case "number": {
					$name = (string) $ch->attributes()->name;
					$key = (string) $ch->attributes()->key;
					$p = $phpArg[($key != NULL ? $key : $i)];
					$mappings[$name] = $p;break;
				}
				case "boolean": {
					$name = (string) $ch->attributes()->name;
					$key = (string) $ch->attributes()->key;
					$p = $phpArg[($key != NULL ? $key : $i)];
					$mappings[$name] = $p;break;
				}
				case "array": {
					$key = (string) $ch->attributes()->key;
					$p = $phpArg[($key != NULL ? $key : $i)];
					$this->_deserialize($ch, $p, $mappings);
				}
			}
			$i++;
		}
	}




}

/**
 * Represents a variable setting.
 *
 * e.g. $smwgMySetting = true;
 *
 */
class VariableConfigElement extends ConfigElement {
	var $name;
	var $value;
	var $remove;
	var $external; // indicates that variable is defined elsewhere and not in the extensions's section
	var $requireUserValue;

	public function __construct($child) {
		parent::__construct("var");
		$this->argumentsAsXML = $child;
		$this->name = $child->attributes()->name;
		$this->remove = $child->attributes()->remove;
		$this->external = $child->attributes()->external;

	}

	public function apply(& $ls, $ext_id, $userValues = array()) {
		$this->value = $this->serializeParameters($this->argumentsAsXML, $userValues);
		$this->value = count($this->argumentsAsXML->children()) > 1 ? 'array('.$this->value.')' : $this->value;
		$remove = ($this->remove == "true");

		if ($this->external) {
			if ($remove) {
				$this->removeVariable($ls, $this->name);
			} else {
				$this->changeVariable($ls, $this->name, $this->remove ? NULL : $this->value);
			}
			return;
		} else {
			$fragment = self::getExtensionFragment($ext_id, $ls);
			$res = $this->changeVariable($fragment, $this->name, $this->remove ? NULL : $this->value);

			if ($res === true) {
				$this->replaceExtensionFragment($ext_id, $fragment, $ls);

				return;
			}
		}

		return "\n$$this->name = /*start-variable-$this->name*/ $this->value;/*end-variable-$this->name*/";

	}

	private function removeVariable(& $content, $name) {
		// remove it
		$content = preg_replace('/\$'.preg_quote($name).'\s*=.*/', "", $content);
	}

	/**
	 * Changes a variable value in a text.
	 *
	 * @param $content
	 * @param $name
	 * @param $value
	 * @param $notquote
	 *
	 */
	private function changeVariable(& $content, $name, $value, $notquote=false) {

		if (preg_match('/\$'.preg_quote($name).'\s*=.*/', $content) > 0) {
			$content = preg_replace('/\$'.preg_quote($name).'\s*=.*/', "\$$name=$value;", $content);
			return true;
		}
		return false;
	}
}

/**
 * Represents a require/include statement in the settings.
 *
 * @author: Kai K�hn
 *
 */
class RequireConfigElement extends ConfigElement {
	var $file;
	var $remove;
	public function __construct($child) {
		parent::__construct("require");
		$this->file = $child->attributes()->file;
		$this->remove = $child->attributes()->remove;
		$this->remove = ($this->remove == "true");
	}

	public function apply(& $ls, $ext_id, $userValues = array()) {
		if ($this->remove) {
			$this->removeRequireonce($ls, $this->file);
		} else {
			return "\nrequire_once('".$this->file."');";
		}
	}

	private function removeRequireonce(& $content, $file) {
		$file = preg_quote($file);
		$file = str_replace("/", '\/', $file);
		$toRemove = '/(require|include)(_once)?\s*\(\s*\'\s*'.$file.'\s*\'\s*\)\s*;/';
		$content = preg_replace($toRemove, "", $content);
		return true;

	}
}

/**
 * A generic replacement command. Useful to remove bugs.
 *
 * @author: Kai Kuehn
 *
 */
class ReplaceConfigElement extends ConfigElement {

	// text to search for
	var $search;

	// text to replace with
	var $replacement;
	// optional attribute parameter for replacement
	var $proposal;

	// optional file in which text is replaced
	// if missing the replacement is done in LocalSettings.php
	var $file;

	// DeployDescriptor
	var $dd;

	// location of PHP interpreter
	var $phpExe;

	public function __construct($child, $dd) {
		parent::__construct("replace");
		$this->dd = $dd;
		$this->search = (string) $child[0]->search[0];
		$this->replacement = (string) $child[0]->replacement[0];
		$this->proposal = (string) $child[0]->replacement[0]->attributes()->proposal;
		$this->file = isset($child[0]->file) ? (string) $child[0]->file[0] : '';

		$this->phpExe = 'php';
		if (class_exists('DF_Config') && array_key_exists('df_php_executable', DF_Config::$settings) && !empty(DF_Config::$settings['df_php_executable'])) {
			$this->phpExe = DF_Config::$settings['df_php_executable'];
		}
	}

	public function apply(& $ls, $ext_id, $userValues = array()) {
		if ($this->file == '') {
			// replace in LocalSettings.php
			$ls = str_replace($this->search, $this->replacement, $ls);
			return ""; // do not return anything, just change
		} else {
			// <file> set so replace in a file
			global $mwrootDir;
			if ($this->proposal != '') {
				// if a proposal is given use it
				// for now only "variable: <name>" is possible
				$parts = explode(":", $this->proposal);
				exec("\"$this->phpExe\" \"$mwrootDir/deployment/tools/maintenance/getSettings.php\" -v ".trim($parts[1]), $out, $ret);
				$this->replacement = $ret == 0 ? trim(reset($out)) : '';

			}

			// get file location
			if ($this->dd->isNonPublic()) {
				$appPaths = Tools::getNonPublicAppPath($mwrootDir);
				$filePath = $appPaths[$this->dd->getID()]."/$this->file";
			} else {
				$filePath = $mwrootDir."/".$this->dd->getInstallationDirectory()."/$this->file";
			}
				
			// change file
			$content = file_get_contents($filePath);
			$content = str_replace($this->search, $this->replacement, $content);
			$handle = fopen($filePath, "w");
			fwrite($handle, $content);
			fclose($handle);
			return "";
		}
	}

}

/**
 * Represents a arbitrary PHP statement in the settings.
 *
 * @author: Kai K�hn
 *
 */
class PHPConfigElement extends ConfigElement {
	public function __construct($child) {
		parent::__construct("php");
		$this->name = (string) $child->attributes()->name;
		$this->remove = $child->attributes()->remove;
		$this->remove = ($this->remove == "true");
		$this->content = (string) $child[0];
	}

	public function apply(& $ls, $ext_id, $userValues = array()) {
		if ($this->remove) {
			$fragment = self::getExtensionFragment($ext_id, $ls);
			$start = strpos($fragment, "/*php-start-$this->name*/");
			$end = strpos($fragment, "/*php-end-$this->name*/");
			if ($start !== false && $end !== false) {
				$part1 = substr($fragment, 0, $start);
				$part2 = substr($fragment, $end + strlen("/*php-end-$this->name*/"));
				$fragment =  $part1.$part2;
				$this->replaceExtensionFragment($ext_id, $fragment, $ls);
			}

		} else {
			return "\n/*php-start-$this->name*/\n".trim($this->content)."\n/*php-end-$this->name*/";
		}
	}
}

/**
 * Represents a function call in the settings.
 *
 * e.g. enableSemantics('http://localhost:8080', true);
 *
 * @author: Kai K�hn
 *
 */
class FunctionCallConfigElement extends ConfigElement {
	public function __construct($child) {
		parent::__construct("function");
		$this->argumentsAsXML = $child;
		$this->functionname = $child->attributes()->name;
		$this->remove = $child->attributes()->remove;
		$this->remove = ($this->remove === "true");
		$this->ext = $child->attributes()->ext;

	}

	public function apply(& $ls, $ext_id, $userValues = array()) {

		$arguments = $this->serializeParameters($this->argumentsAsXML, $userValues);
		$parameters = "/*param-start-".$this->functionname."*/".$arguments."/*param-end-".$this->functionname."*/";
		$appliedCommand = "\n".$this->functionname."(".$parameters.");";

		if ($this->remove) {

			$fragment = self::getExtensionFragment($ext_id, $ls);
			$fragment = $this->replaceFunction($fragment, "");
			$this->replaceExtensionFragment($ext_id, $fragment, $ls);

		}  else {

			$fragment = self::getExtensionFragment($ext_id, $ls);

			if (is_null($fragment)) return $appliedCommand;
			$start = strpos($fragment, '/*param-start-'.$this->functionname."*/");
			$end = strpos($fragment, '/*param-end-'.$this->functionname."*/");
			if ($start === false || $end === false) {
				return $appliedCommand;
			}
			$mappings = $this->deserialize($this->argumentsAsXML, substr($fragment, $start, $end-$start));
			$arguments = $this->serializeParameters($this->argumentsAsXML, $mappings);
			$fragment = $this->replaceFunction($fragment, $arguments);
			$this->replaceExtensionFragment($ext_id, $fragment, $ls);
		}
	}

	/**
	 * Replace the function call in fragment with the given function call.
	 *
	 * @param string $fragment
	 * @param string $arguments serialized arguments
	 */
	protected function replaceFunction($fragment, $arguments = "") {
		$start = strpos($fragment, '/*param-start-'.$this->functionname."*/");
		$end = strpos($fragment, '/*param-end-'.$this->functionname."*/");
		return preg_replace("/".$this->functionname."\\s*\\(\\s*\\)\\s*;/", $this->functionname."(/*param-start-".$this->functionname."*/".$arguments."/*param-end-".$this->functionname."*/);", $fragment);
	}


}

class IllegalArgument extends Exception {

}

class RollbackInstallation extends Exception {

}
