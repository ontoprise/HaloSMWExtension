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
 * @ingroup WebAdmin
 *
 * Command interface
 *
 * @author: Kai Kühn / ontoprise / 2011
 *
 */
if (!defined("DF_WEBADMIN_TOOL")) {
	die();
}

require_once ( $mwrootDir.'/deployment/descriptor/DF_DeployDescriptorProcessor.php' );
require_once ( $mwrootDir.'/deployment/tools/smwadmin/DF_PackageRepository.php' );
require_once($mwrootDir.'/deployment/tools/smwadmin/DF_Tools.php');
require_once($mwrootDir.'/deployment/tools/smwadmin/DF_Installer.php');
require_once($mwrootDir.'/deployment/io/DF_Log.php');
require_once($mwrootDir.'/deployment/tools/smwadmin/DF_UserInput.php');

class DFCommandInterface {

	// path to PHP executable
	var $phpExe;

	// switch which keeps the cmd window or closes it after the process terminates.
	var $keepCMDWindow;
	/**
	 *
	 *
	 */
	public function __construct() {
		$this->phpExe = 'php';
		if (array_key_exists('df_php_executable', DF_Config::$settings)
		&& !empty(DF_Config::$settings['df_php_executable'])) {
			$this->phpExe = DF_Config::$settings['df_php_executable'];
		}
		$this->keepCMDWindow = array_key_exists('df_keep_cmd_window', DF_Config::$settings)
		&& DF_Config::$settings['df_keep_cmd_window'] === true ? "/K" : "/C";
	}

	public function dispatch($command, $args) {
		try {
			return call_user_func_array(array($this, $command), $args);
		} catch(Exception $e) {
			header( "Status: " . $e->getCode(), true, (int)$e->getCode() );
			print $e->getMessage();
		}
	}

	public function readLog($filename) {
		global $mwrootDir, $dfgOut;
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();

		$absoluteFilePath = "$logdir/$filename";
		Tools::mkpath(dirname($absoluteFilePath));
		if (!file_exists($absoluteFilePath)) {
			return '$$NOTEXISTS$$';
		}
		$log = file_get_contents($absoluteFilePath);
		return $log;
	}

	public function getLocalSettingFragment($extid) {
		global $mwrootDir;
		$localPackages = PackageRepository::getLocalPackages($mwrootDir);
		if (!array_key_exists($extid, $localPackages)) {
			throw new Exception("Extension not found: $extid", 400);
		}
		$ddproc = new DeployDescriptionProcessor($mwrootDir."/LocalSettings.php",$localPackages[$extid]);
		$fragment = $ddproc->getConfigFragment($extid);
		if (is_null($fragment)) {
			throw new Exception("Fragment not found: $extid", 400);
		}
		return $fragment;
	}

	public function saveLocalSettingFragment($extid, $fragment) {
		global $mwrootDir;
		$localPackages = PackageRepository::getLocalPackages($mwrootDir);
		$ddproc = new DeployDescriptionProcessor($mwrootDir."/LocalSettings.php",$localPackages[$extid]);
		$content = $ddproc->replaceConfigFragment($extid, $fragment);
		if ($content === false) {
			throw new Exception("Replacing fragment for $extid failed!", 500);
		}
		$ddproc->writeLocalSettingsFile();
		return true;
	}

	public function getLocalDeployDescriptor($extid) {
		global $mwrootDir, $dfgOut;

		$localPackages = PackageRepository::getLocalPackages($mwrootDir);
		if (!array_key_exists($extid, $localPackages)) {
			return NULL;
		}
		$dd = $localPackages[$extid];
		$result=array();
		$result['id'] = $dd->getID();
		$result['version'] = $dd->getVersion()->toVersionString();
		$result['patchlevel'] = $dd->getPatchlevel();
		foreach($dd->getDependencies() as $d) {
			$result['dependencies'][] = array(implode(",",$d->getIDs()),$d->getMinVersion()->toVersionString());
		}
		$result['maintainer'] = $dd->getMaintainer();
		$result['vendor'] = $dd->getVendor();
		$result['license'] = $dd->getLicense();
		$result['helpurl'] = $dd->getHelpURL();

		$result['resources'] = $dd->getResources();
		$result['onlycopyresources'] = $dd->getOnlyCopyResources();

		$php = $this->phpExe;
		$runCommand = "\"$php\" \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --listpages $extid --outputformat json --nocheck --noask";
		exec($runCommand, $out, $ret);

		$outText = implode("",$out);
		if (strpos($outText, '$$ERROR$$') !== false) {
			$result['error'] = $outText;
		} else {
			$wikidumps = json_decode(trim($outText));
			$result['wikidumps'] = $wikidumps->wikidumps;
			$result['ontologies'] = $wikidumps->ontologies;
		}
		return json_encode($result);
	}

	public function getDeployDescriptor($extid, $version = '') {
		global $mwrootDir, $dfgOut;

		if (empty($version)) {
			$dd = PackageRepository::getLatestDeployDescriptor($extid);
		} else{
			$dd = PackageRepository::getDeployDescriptor($extid, $version);
		}
		if (is_null($dd)) {
			return NULL;
		}

		$result=array();
		$result['id'] = $dd->getID();
		$result['version'] = $dd->getVersion()->toVersionString();
		$result['patchlevel'] = $dd->getPatchlevel();
		foreach($dd->getDependencies() as $d) {
			$result['dependencies'][] = array(implode(",",$d->getIDs()),$d->getMinVersion()->toVersionString());
		}
		$result['maintainer'] = $dd->getMaintainer();
		$result['vendor'] = $dd->getVendor();
		$result['license'] = $dd->getLicense();
		$result['helpurl'] = $dd->getHelpURL();

		$result['resources'] = $dd->getResources();
		$result['onlycopyresources'] = $dd->getOnlyCopyResources();

		$result['wikidumps'] = array();
		foreach($dd->getWikidumps() as $loc) {
			$result['wikidumps'][$loc] = array();
		}

		$result['ontologies'] = array();
		foreach($dd->getOntologies() as $loc) {
			$result['ontologies'][$loc] = array();
		}
		return json_encode($result);
	}

	public function getDependencies($extid, $version) {
		global $mwrootDir, $dfgOut;

		try {
			$dfgOut->setVerbose(false);
			$installer = Installer::getInstance($mwrootDir);
			$dependencies = $installer->getExtensionsToInstall($extid, new DFVersion($version));

			$dfgOut->setVerbose(true);
			return json_encode($dependencies);
		} catch(InstallationError $e) {
			$error = array();
			$error['exception'] = array($e->getMsg(), $e->getErrorCode(), $e->getArg1(), $e->getArg2());
			$dfgOut->setVerbose(true);
			return json_encode($error);
		} catch(RepositoryError $e) {
			$error = array();
			$error['exception'] = array($e->getMsg(), $e->getErrorCode(), $e->getArg1(), $e->getArg2());
			$dfgOut->setVerbose(true);
			return json_encode($error);
		}
	}

	public function install($extid) {
		global $mwrootDir, $dfgOut;

		$filename = uniqid().".log";
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();
		touch("$logdir/$filename");

		chdir($mwrootDir.'/deployment/tools');
		$php = $this->phpExe;
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd $this->keepCMDWindow START ".$this->quotePathForWindowsCMD($php)." \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --noask -i \"$extid\"";
			$oExec = $wshShell->Run("$runCommand", 7, false);

		} else {
			$runCommand = "\"$php\" \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --noask -i \"$extid\"";
			$nullResult = `$runCommand &`;
		}
		return $filename;
	}

	public function deinstall($extid) {
		global $mwrootDir, $dfgOut;

		$filename = uniqid().".log";
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();
		touch("$logdir/$filename");

		chdir($mwrootDir.'/deployment/tools');
		$php = $this->phpExe;
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd $this->keepCMDWindow START ".$this->quotePathForWindowsCMD($php)." \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --noask -d \"$extid\"";
			$oExec = $wshShell->Run("$runCommand", 7, false);

		} else {
			$runCommand = "\"$php\" \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --noask -d \"$extid\"";
			$nullResult = `$runCommand &`;
		}
		return $filename;
	}

	public function update($extid) {
		global $mwrootDir, $dfgOut;

		$filename = uniqid().".log";
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();
		touch("$logdir/$filename");

		chdir($mwrootDir.'/deployment/tools');
		$php = $this->phpExe;
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd $this->keepCMDWindow START ".$this->quotePathForWindowsCMD($php)." \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --noask -u \"$extid\"";
			$oExec = $wshShell->Run("$runCommand", 7, false);
		} else {
			$runCommand = "\"$php\" \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --noask -u \"$extid\"";
			$nullResult = `$runCommand &`;
		}
		return $filename;
	}

	public function finalize($extid) {
		global $mwrootDir, $dfgOut;

		$filename = uniqid().".log";
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();
		touch("$logdir/$filename");

		chdir($mwrootDir.'/deployment/tools');
		$php = $this->phpExe;
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd $this->keepCMDWindow START ".$this->quotePathForWindowsCMD($php)." \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --noask --finalize";
			$oExec = $wshShell->Run("$runCommand", 7, false);

		} else {
			$runCommand = "\"$php\" \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --noask --finalize";
			$nullResult = `$runCommand &`;
		}
		return $filename;
	}

	public function checkforGlobalUpdate() {
		global $mwrootDir, $dfgOut;

		$dfgOut->setVerbose(false);
		try {
			$installer = Installer::getInstance($mwrootDir);
			$dependencies = $installer->checkforGlobalUpdate();
			$dfgOut->setVerbose(true);
			return json_encode($dependencies);
		} catch(InstallationError $e) {
			$error = array();
			$error['exception'] = array($e->getMsg(), $e->getErrorCode(), $e->getArg1(), $e->getArg2());
			return json_encode($error);
		}  catch(RepositoryError $e) {
			$error = array();
			$error['exception'] = array($e->getMsg(), $e->getErrorCode(), $e->getArg1(), $e->getArg2());
			$dfgOut->setVerbose(true);
			return json_encode($error);
		}
	}

	public function doGlobalUpdate() {
		global $mwrootDir, $dfgOut;

		$filename = uniqid().".log";
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();
		touch("$logdir/$filename");

		chdir($mwrootDir.'/deployment/tools');
		$php = $this->phpExe;
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd $this->keepCMDWindow START ".$this->quotePathForWindowsCMD($php)." \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --noask -u";
			$oExec = $wshShell->Run("$runCommand", 7, false);

		} else {
			$runCommand = "\"$php\" \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --noask -u";
			$nullResult = `$runCommand &`;
		}
		return $filename;
	}

	public function restore($restorepoint) {
		global $mwrootDir, $dfgOut;

		$filename = uniqid().".log";
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();
		touch("$logdir/$filename");

		chdir($mwrootDir.'/deployment/tools');
		$php = $this->phpExe;
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd $this->keepCMDWindow START ".$this->quotePathForWindowsCMD($php)." \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --noask -r $restorepoint";
			$oExec = $wshShell->Run("$runCommand", 7, false);

		} else {
			$runCommand = "\"$php\" \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --noask -r $restorepoint";
			$nullResult = `$runCommand &`;
		}
		return $filename;
	}

	public function removeRestorePoint($restorepoint) {
		global $mwrootDir, $dfgOut;

		$filename = uniqid().".log";
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();
		touch("$logdir/$filename");

		chdir($mwrootDir.'/deployment/tools');
		$php = $this->phpExe;
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd $this->keepCMDWindow START ".$this->quotePathForWindowsCMD($php)." \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --noask --rremove $restorepoint";
			$oExec = $wshShell->Run("$runCommand", 7, false);

		} else {
			$runCommand = "\"$php\" \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --noask --rremove $restorepoint";
			$nullResult = `$runCommand &`;
		}
		return $filename;
	}

	public function createRestorePoint($restorepoint) {
		global $mwrootDir, $dfgOut;

		$filename = uniqid().".log";
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();
		touch("$logdir/$filename");

		chdir($mwrootDir.'/deployment/tools');
		$php = $this->phpExe;
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd $this->keepCMDWindow START ".$this->quotePathForWindowsCMD($php)." \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --noask --rcreate $restorepoint";
			$oExec = $wshShell->Run("$runCommand", 7, false);

		} else {
			$runCommand = "\"$php\" \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --noask --rcreate $restorepoint";
			$nullResult = `$runCommand &`;
		}
		return $filename;
	}

	public function search($searchValue) {
		global $mwrootDir, $dfgOut, $dfgLang, $dfgSearchTab;
		$results = array();
		$findall = $dfgLang->getLanguageString('df_webadmin_findall');
		if ($searchValue == $findall) $searchValue = '';
		$packages = PackageRepository::searchAllPackages($searchValue);
		$localPackages = PackageRepository::getLocalPackages($mwrootDir);
		$dfgOut->outputln($dfgSearchTab->searializeSearchResults($packages, $localPackages, $searchValue));
		return true;
	}

	public function removeFile($filepath) {
		global $mwrootDir, $dfgOut;
		unlink($filepath);
	}

	public function removeFromRepository($url) {
		global $rootDir;
		if (!file_exists("$rootDir/tools/repositories")) {
			throw new Exception("Could not find repositories file", 500);
		}
		if (!is_writable("$rootDir/tools/repositories")) {
			throw new Exception("$rootDir/tools/repositories is not writeable!", 500);
		}
		$contents = file_get_contents("$rootDir/tools/repositories");

		//FIXME: consider credentials
		$contents = str_replace($url, "", $contents);
		$handle = fopen("$rootDir/tools/repositories", "w");
		fwrite($handle, $contents);
		fclose($handle);
		return;


	}

	public function addToRepository($url) {
		global $rootDir;
		if (!is_writable("$rootDir/tools/repositories")) {
			throw new Exception("$rootDir/tools/repositories is not writeable!", 500);
		}
		$contents = file_get_contents("$rootDir/tools/repositories");
		$contents .= "\n$url";
		$handle = fopen("$rootDir/tools/repositories", "w");
		fwrite($handle, $contents);
		fclose($handle);
		return;

	}

	public function isProcessRunning($processName) {
		return Tools::isProcessRunning($processName) ? "true" : "false";
	}

	public function areProcessesRunning($processNames) {
		$doesRun = Tools::areProcessesRunning(explode(",",$processNames));
		return implode(",", $doesRun);
	}

	public function startProcess($path, $async) {
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "$path";
			$oExec = $wshShell->Run("$runCommand", 7, false);
			return "true";
		} else {
			@chdir(dirname($path));
			@exec($path, $out, $ret);
			return $ret == 0 ? implode("\n", $out) : "false";
		}
	}

	public function loadServerSettings() {
		global $mwrootDir;
		$server_settings = @file_get_contents($mwrootDir."/deployment/tools/webadmin/server_settings");
		return $server_settings !== false ? $server_settings : "false";
	}

	public function storeServerSettings($jsondata) {
		global $mwrootDir;
		$server_settings = fopen($mwrootDir."/deployment/tools/webadmin/server_settings","w");
		if ($server_settings === false) return "false";
		fwrite($server_settings, $jsondata);
		fclose($server_settings);
		return "true";
	}

	/**
	 * Special quoting for cmd /c START ....
	 * Quotes only if necessary and then like this:  d:\"my folder"
	 *
	 * @param string $path
	 */
	private function quotePathForWindowsCMD($path) {
		if (strpos($path, " ") === false) return $path;
		return substr($path, 0, 3).'"'.substr($path, 3).'"';
	}
}