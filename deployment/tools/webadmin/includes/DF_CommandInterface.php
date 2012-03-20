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
 * @ingroup WebAdmin
 *
 * Command interface
 *
 * @author: Kai Kühn
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

	public function readLog($filename, $format = 'html') {
		global $mwrootDir, $dfgOut;
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();

		$absoluteFilePath = "$logdir/$filename";
		Tools::mkpath(dirname($absoluteFilePath));
		if (!file_exists($absoluteFilePath)) {
			return '$$NOTEXISTS$$';
		}
		if ($format == 'html') {
			header( "Content-type: text/html;" );
		} else if ($format == 'text') {
			header( "Content-type: text/plain;" );
		}
		$log = file_get_contents($absoluteFilePath);
		return $log;
	}

	public function getLocalSettingFragment($extid) {
		global $mwrootDir;
		$localPackages = PackageRepository::getLocalPackages($mwrootDir);
		if ($extid != 'all') {
			if (!array_key_exists($extid, $localPackages)) {
				throw new Exception("Extension not found: $extid", 400);
			}
			$ddproc = new DeployDescriptionProcessor($mwrootDir."/LocalSettings.php",$localPackages[$extid]);
			$fragment = $ddproc->getConfigFragment($extid);
		} else {
			$ddproc = new DeployDescriptionProcessor($mwrootDir."/LocalSettings.php", NULL);
			$fragment = $ddproc->getConfigFragment(NULL);
		}
		if (is_null($fragment)) {
			throw new Exception("Fragment not found: $extid", 400);
		}
		return $fragment;
	}

	public function saveLocalSettingFragment($extid, $fragment) {
		global $mwrootDir;

		//FIXME: this is necessary for Linux because it escapes quotes in $fragment. why?
		if (!Tools::isWindows()) {
			$fragment = str_replace(array('\"', "\\'"), array('"', "'"), $fragment);
		}
		if ($extid != 'all') {
			$localPackages = PackageRepository::getLocalPackages($mwrootDir);
			$ddproc = new DeployDescriptionProcessor($mwrootDir."/LocalSettings.php",$localPackages[$extid]);
			$content = $ddproc->replaceConfigFragment($extid, $fragment);
		} else {
			$ddproc = new DeployDescriptionProcessor($mwrootDir."/LocalSettings.php", NULL);
			$content = $ddproc->replaceConfigFragment(NULL, $fragment);
		}
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
		$result['dependencies'] = array();
		foreach($dd->getDependencies() as $d) {
			$result['dependencies'][] = array(implode(",",$d->getIDs()),$d->getMinVersion()->toVersionString());
		}
		$result['maintainer'] = $dd->getMaintainer();
		$result['vendor'] = $dd->getVendor();
		$result['license'] = $dd->getLicense();
		$result['helpurl'] = $dd->getHelpURL();
		$result['notice'] = $dd->getNotice();

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
			$result['wikidumps'] = isset($wikidumps->wikidumps) ? $wikidumps->wikidumps : array();
			$result['ontologies'] = isset($wikidumps->ontologies) ? $wikidumps->ontologies : array();
		}
		return json_encode($result);
	}

	public function getDeployDescriptor($extid, $version = '') {
		global $mwrootDir, $dfgOut;
		$dfgOut->setVerbose(false);
		if (empty($version)) {
			$dd = PackageRepository::getLatestDeployDescriptor($extid);
		} else{
			$dd = PackageRepository::getDeployDescriptor($extid, $version);
		}
		if (is_null($dd)) {
			return NULL;
		}
		$dfgOut->setVerbose(true);
		$result=array();
		$result['id'] = $dd->getID();
		$result['version'] = $dd->getVersion()->toVersionString();
		$result['patchlevel'] = $dd->getPatchlevel();
		$result['dependencies'] = array();
		foreach($dd->getDependencies() as $d) {
			$result['dependencies'][] = array(implode(",",$d->getIDs()),$d->getMinVersion()->toVersionString());
		}
		$result['maintainer'] = $dd->getMaintainer();
		$result['vendor'] = $dd->getVendor();
		$result['license'] = $dd->getLicense();
		$result['helpurl'] = $dd->getHelpURL();
		$result['notice'] = $dd->getNotice();

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

	public function install($extid, $settings) {
		global $mwrootDir, $dfgOut;

		$unique_id = uniqid();
		$filename = $unique_id.".log";
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();
		touch("$logdir/$filename");

		$console_out = "$logdir/$filename.console_out";
		
		$settings = json_decode($settings);
		$optionString = self::getOptions($settings);
		
		chdir($mwrootDir.'/deployment/tools');
		$php = $this->phpExe;
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd $this->keepCMDWindow  ".$this->quotePathForWindowsCMD($php)." \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --options $optionString --noask --outputformat html --nocheck --showOKHint -i \"$extid\" > \"$console_out\" 2>&1";
			$oExec = $wshShell->Run("$runCommand", 7, false);

		} else {
			$runCommand = "\"$php\" \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --options $optionString --noask --nocheck --showOKHint -i \"$extid\" > \"$console_out\" 2>&1";
			$nullResult = `$runCommand &`;
		}
		return $filename;
	}
	
    public function installAll($extids, $settings) {
        global $mwrootDir, $dfgOut;

        $unique_id = uniqid();
        $filename = $unique_id.".log";
        $logger = Logger::getInstance();
        $logdir = $logger->getLogDir();
        touch("$logdir/$filename");

        $console_out = "$logdir/$filename.console_out";
        
        $settings = json_decode($settings);
        $optionString = self::getOptions($settings);
        
        $extidsString = implode(" -i ", array_map(function($e) {
           return "\"$e\"";  
        }, explode(",",$extids)));
        
        chdir($mwrootDir.'/deployment/tools');
        $php = $this->phpExe;
        if (Tools::isWindows()) {
            $wshShell = new COM("WScript.Shell");
            $runCommand = "cmd $this->keepCMDWindow  ".$this->quotePathForWindowsCMD($php)." \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --options $optionString --noask --outputformat html --nocheck --showOKHint -i $extidsString > \"$console_out\" 2>&1";
            $oExec = $wshShell->Run("$runCommand", 7, false);

        } else {
            $runCommand = "\"$php\" \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --options $optionString --noask --nocheck --showOKHint -i \"$extid\" > \"$console_out\" 2>&1";
            $nullResult = `$runCommand &`;
        }
        return $filename;
    }

	public function deinstall($extid, $settings) {
		global $mwrootDir, $dfgOut;

		$unique_id = uniqid();
		$filename = $unique_id.".log";
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();
		touch("$logdir/$filename");

		$console_out = "$logdir/$filename.console_out";

		$settings = json_decode($settings);
		$optionString = self::getOptions($settings);
		
		chdir($mwrootDir.'/deployment/tools');
		$php = $this->phpExe;
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd $this->keepCMDWindow  ".$this->quotePathForWindowsCMD($php)." \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --options $optionString --noask --outputformat html --nocheck --showOKHint -d \"$extid\" > \"$console_out\" 2>&1";
			$oExec = $wshShell->Run("$runCommand", 7, false);

		} else {
			$runCommand = "\"$php\" \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --options $optionString --noask --nocheck --showOKHint -d \"$extid\" > \"$console_out\" 2>&1";
			$nullResult = `$runCommand &`;
		}
		return $filename;
	}

	public function update($extid, $settings) {
		global $mwrootDir, $dfgOut;

		$unique_id = uniqid();
		$filename = $unique_id.".log";
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();
		touch("$logdir/$filename");

		$console_out = "$logdir/$filename.console_out";

		$settings = json_decode($settings);
		$optionString = self::getOptions($settings);

		chdir($mwrootDir.'/deployment/tools');
		$php = $this->phpExe;
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd $this->keepCMDWindow  ".$this->quotePathForWindowsCMD($php)." \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --options $optionString --noask --outputformat html --nocheck --showOKHint -u \"$extid\" > \"$console_out\" 2>&1";
			$oExec = $wshShell->Run("$runCommand", 7, false);
		} else {
			$runCommand = "\"$php\" \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --options $optionString --noask --nocheck --showOKHint -u \"$extid\" > \"$console_out\" 2>&1";
			$nullResult = `$runCommand &`;
		}
		return $filename;
	}

	public function finalize($extid, $settings) {
		global $mwrootDir, $dfgOut;

		$unique_id = uniqid();
		$filename = $unique_id.".log";
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();
		touch("$logdir/$filename");

		$console_out = "$logdir/$filename.console_out";
		
		$settings = json_decode($settings);
		$optionString = self::getOptions($settings);
		
		chdir($mwrootDir.'/deployment/tools');
		$php = $this->phpExe;
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd $this->keepCMDWindow  ".$this->quotePathForWindowsCMD($php)." \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --options $optionString --noask --outputformat html --nocheck --showOKHint --finalize > \"$console_out\" 2>&1";
			$oExec = $wshShell->Run("$runCommand", 7, false);

		} else {
			$runCommand = "\"$php\" \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --options $optionString --noask --nocheck --showOKHint --finalize > \"$console_out\" 2>&1";
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

	public function doGlobalUpdate($settings) {
		global $mwrootDir, $dfgOut;

		$unique_id = uniqid();
		$filename = $unique_id.".log";
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();
		touch("$logdir/$filename");

		$console_out = "$logdir/$filename.console_out";
		
		$settings = json_decode($settings);
		$optionString = self::getOptions($settings);
		
		chdir($mwrootDir.'/deployment/tools');
		$php = $this->phpExe;
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd $this->keepCMDWindow  ".$this->quotePathForWindowsCMD($php)." \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --options $optionString --noask --outputformat html --nocheck --showOKHint -u > \"$console_out\" 2>&1";
			$oExec = $wshShell->Run("$runCommand", 7, false);

		} else {
			$runCommand = "\"$php\" \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --options $optionString --noask --nocheck --showOKHint -u > \"$console_out\" 2>&1";
			$nullResult = `$runCommand &`;
		}
		return $filename;
	}

	public function restore($restorepoint) {
		global $mwrootDir, $dfgOut;

		$unique_id = uniqid();
		$filename = $unique_id.".log";
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();
		touch("$logdir/$filename");

		$console_out = "$logdir/$filename.console_out";

		chdir($mwrootDir.'/deployment/tools');
		$php = $this->phpExe;
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd $this->keepCMDWindow  ".$this->quotePathForWindowsCMD($php)." \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --showOKHint --noask -r $restorepoint > \"$console_out\" 2>&1";
			$oExec = $wshShell->Run("$runCommand", 7, false);

		} else {
			$runCommand = "\"$php\" \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --showOKHint --noask -r $restorepoint > \"$console_out\" 2>&1";
			$nullResult = `$runCommand &`;
		}
		return $filename;
	}

	public function removeRestorePoint($restorepoint) {
		global $mwrootDir, $dfgOut;

		$unique_id = uniqid();
		$filename = $unique_id.".log";
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();
		touch("$logdir/$filename");

		$console_out = "$logdir/$filename.console_out";

		chdir($mwrootDir.'/deployment/tools');
		$php = $this->phpExe;
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd $this->keepCMDWindow  ".$this->quotePathForWindowsCMD($php)." \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --showOKHint --noask --rremove $restorepoint > \"$console_out\" 2>&1";
			$oExec = $wshShell->Run("$runCommand", 7, false);

		} else {
			$runCommand = "\"$php\" \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --showOKHint --noask --rremove $restorepoint > \"$console_out\" 2>&1";
			$nullResult = `$runCommand &`;
		}
		return $filename;
	}

	public function createRestorePoint($restorepoint) {
		global $mwrootDir, $dfgOut;

		$unique_id = uniqid();
		$filename = $unique_id.".log";
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();
		touch("$logdir/$filename");

		$console_out = "$logdir/$filename.console_out";

		chdir($mwrootDir.'/deployment/tools');
		$php = $this->phpExe;
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd $this->keepCMDWindow  ".$this->quotePathForWindowsCMD($php)." \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --showOKHint --noask --rcreate $restorepoint > \"$console_out\" 2>&1";
			$oExec = $wshShell->Run("$runCommand", 7, false);

		} else {
			$runCommand = "\"$php\" \"$mwrootDir/deployment/tools/smwadmin/smwadmin.php\" --logtofile $filename --outputformat html --nocheck --showOKHint --noask --rcreate $restorepoint > \"$console_out\" 2>&1";
			$nullResult = `$runCommand &`;
		}
		return $filename;
	}

	public function search($searchValue) {
		global $mwrootDir, $dfgOut, $dfgLang, $dfgSearchTab;
		$results = array();
		$findall = $dfgLang->getLanguageString('df_webadmin_findall');
		if ($searchValue == $findall) $searchValue = '';
		$dfgOut->setVerbose(false);
		$packages = PackageRepository::searchAllPackages($searchValue);
		$localPackages = PackageRepository::getLocalPackages($mwrootDir);
		$dfgOut->setVerbose(true);
		$dfgOut->outputln($dfgSearchTab->searializeSearchResults($packages, $localPackages, $searchValue));
		return true;
	}

	public function removeFile($filepath) {
		global $mwrootDir, $dfgOut;
		unlink($filepath);
	}

	public function removeFromRepository($url) {
		global $rootDir;
		if (!file_exists("$rootDir/config/repositories")) {
			throw new Exception("Could not find repositories file", 500);
		}
		if (!is_writable("$rootDir/config/repositories")) {
			throw new Exception("$rootDir/config/repositories is not writeable!", 500);
		}
		$contents = file_get_contents("$rootDir/config/repositories");

		$url = trim($url);
		$url_trimmed = rtrim($url, '/');

		$lines = explode("\n", $contents);

		$lines = array_filter($lines, function ($item) use ($url, $url_trimmed) {
			$l = trim($item);
			if ($l == $url || $l == $url_trimmed) {
				return false;
			}
			return true;
		});

		$contents = implode("\n", $lines);

		//FIXME: consider credentials

		$handle = fopen("$rootDir/config/repositories", "w");
		fwrite($handle, $contents);
		fclose($handle);
		return;


	}

	public function addToRepository($url) {
		global $rootDir;
		if (!is_writable("$rootDir/config/repositories")) {
			throw new Exception("$rootDir/config/repositories is not writeable!", 500);
		}
		$contents = file_get_contents("$rootDir/config/repositories");
		$contents .= "\n$url";
		$handle = fopen("$rootDir/config/repositories", "w");
		fwrite($handle, $contents);
		fclose($handle);
		return;

	}

	/**
	 * Checks if the given process is running.
	 *
	 * @param string $processName
	 * @return string true/false
	 */
	public function isProcessRunning($processName) {
		return Tools::isProcessRunning($processName) ? "true" : "false";
	}

	/**
	 * Checks if certain process/services are running.
	 *
	 * On Windows a list of process names is expected.
	 * On Linux a list of service scripts is expected.
	 *
	 * @param string $processNames Comma separated
	 * @param service scripts $servicescripts Comma separated
	 * @return string comma separated list.
	 */
	public function areServicesRunning($processNames, $servicescripts) {
		$doesRun = Tools::areServicesRunning($processNames, $servicescripts);
		return implode(",", $doesRun);
	}



	/**
	 * Starts a process. Optionally it can be run under a certain account,
	 * if this is configured in settings.php
	 *
	 * If you run on a particular account:
	 *
	 * Windows:
	 *
	 *    You have to diable the UAC, otherwise a dialog pops up on the server
	 *
	 * Linux:
	 *
	 * You have to disable the password check for the account
	 * in the sudoers file with this entry (assuming your account is 'wiki'):
	 *
	 * wiki    ALL = NOPASSWD: ALL
	 *
	 * @param string $commandLineToStart Command to start (with parameters)
	 */
	public function startProcess($commandLineToStart, $operation) {
		$runAsUser = isset(DF_Config::$df_runas_user) ? DF_Config::$df_runas_user : NULL;
		$password = isset(DF_Config::$df_runas_password) ? DF_Config::$df_runas_password : NULL;

		if (Tools::isWindows()) {
			if (!is_null($runAsUser)) {
				global $mwrootDir;

				$command = $mwrootDir."/deployment/tools/internal/pcwrunas/pcwRunAs4.exe ";
				$command .= "/u $runAsUser /p $password /app cmd /arg \"/c $commandLineToStart\"";

				@exec($command, $out, $ret);
				return $ret == 0 ? implode("\n", $out) : "false";
			} else {
				$wshShell = new COM("WScript.Shell");

				@chdir(dirname($commandLineToStart));
				@exec($commandLineToStart, $out, $ret);
				return "true";
			}
		} else {

			if (strpos($commandLineToStart, "/etc/init.d") !== false) {
				$command = "sudo $commandLineToStart $operation";
			} else {
				$command = "sudo /sbin/$operation $commandLineToStart";
			}

			@exec($command, $out, $ret);

			return $ret == 0 ? implode("\n", $out) : "false";
		}
	}



	public function loadServerSettings() {
		global $mwrootDir;
		$server_settings = @file_get_contents($mwrootDir."/deployment/config/serversettings");
		return $server_settings !== false ? $server_settings : "false";
	}

	public function storeServerSettings($jsondata) {
		global $mwrootDir;
		//FIXME: this is necessary for Linux because it escapes quotes in $fragment. why?
		if (!Tools::isWindows()) {
			$jsondata = str_replace(array('\"', "\\'"), array('"', "'"), $jsondata);
		}
		$server_settings = fopen($mwrootDir."/deployment/config/serversettings","w");
		if ($server_settings === false) return "false";
		fwrite($server_settings, $jsondata);
		fclose($server_settings);
		return "true";
	}

	public function clearLog() {
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();

		$handle = @opendir($logdir);
		if (!$handle) {

			return;
		}

		while ($entry = readdir($handle) ){
			if ($entry[0] == '.'){
				continue;
			}

			$file = "$logdir/$entry";
			if (strpos($entry, "console_out") === false) {
				continue;
			}
			unlink($file);
		}
		@closedir($handle);
		return "true";
	}

	public function downloadBundleFile($bundleFile) {

		global $dfgContentBundlesTab;
		$bundleExportDir = $dfgContentBundlesTab->getBundleExportDirectory();

		header( 'Content-Encoding: identity' );
		header( "Content-type: application/zip;" );
		header( "Content-disposition: attachment;filename={$bundleFile}" );

		$filePath = $bundleExportDir."/$bundleFile";
		// write bundle
		$handle = fopen($filePath, "rb");
		while (!feof($handle)) {
			print fread($handle, 1024*100);
		}
		fclose($handle);
	}



	public function createBundle($bundleID) {
		global $dfgContentBundlesTab;
		$bundleExportDir = $dfgContentBundlesTab->getBundleExportDirectory();
		$bundleFileName = Tools::makeFileName($bundleID);
		return json_encode($this->exportBundle($bundleID, $bundleExportDir."/$bundleFileName.zip"));
	}

	private function exportBundle($bundleID, $outputFile = "") {
		global $mwrootDir;

		$unique_id = uniqid();
		$filename = $unique_id.".log";
		$logger = Logger::getInstance();
		$logdir = $logger->getLogDir();
		$console_out = "$logdir/$filename.console_out.txt";

		// check if file already exists and append a number if so
		if (file_exists($outputFile)) {
			$outputDir = dirname($outputFile);
			$i = 1;
			$file_wo_ending = Tools::removeFileEnding($outputFile);
			$file_ext = Tools::getFileExtension($outputFile);
			while(file_exists("$outputDir/$file_wo_ending($i).$file_ext")) $i++;
			$filename = "$file_wo_ending($i).$file_ext";
			$outputFile = $filename;
		}

		chdir($mwrootDir."/deployment/tools/maintenance/export");
		$outputFile_esc = $outputFile != "" ? '"'.$outputFile.'"' : "";
		if (Tools::isWindows()) {
			$outputFile_esc = str_replace("/", "\\", $outputFile_esc);
			exec("exportBundle.bat $bundleID $outputFile_esc > \"$console_out\" 2>&1", $out, $ret );
			$filePath = $outputFile == "" ? "c:\\temp\\$bundleID\\$bundleID.zip" : $outputFile;
		} else {
			exec("exportBundle.sh $bundleID $outputFile_esc > \"$console_out\" 2>&1", $out, $ret );
			$filePath = $outputFile == "" ? "/tmp/$bundleID/$bundleID.zip" : $outputFile;
		}
		$o = new stdClass();
		$o->returnCode = $ret;
		$o->outputFile = "$filename.console_out.txt";
		$o->bundleFile = $filePath;
		return $o;
	}

	private static function getOptions($settings) {
		$result = array();
		$result[] = self::getOption($settings, 'df_watsettings_overwrite_always');
		$result[] = self::getOption($settings, 'df_watsettings_merge_with_other_bundle');
		$result[] = self::getOption($settings, 'df_watsettings_apply_patches');
		$result[] = self::getOption($settings, 'df_watsettings_create_restorepoints');
		$result[] = self::getOption($settings, 'df_watsettings_hidden_annotations');
		$result[] = self::getOption($settings, 'df_watsettings_use_namespaces');
		return implode(",", $result);
	}
	
	/**
	 * Returns state of option as a string.
	 * 
	 * @param object $settings Settings object (de-serialized from JSON)
	 * @param string $option
	 */
	private static function getOption($settings, $option) {
		return (property_exists($settings, $option) && $settings->$option == "true") ? "$option=true" : "$option=false";
	}

	/**
	 * Special quoting for cmd /c  ....
	 * Quotes only if necessary and then like this:  d:\"my folder"
	 *
	 * @param string $path
	 */
	private function quotePathForWindowsCMD($path) {
		if (strpos($path, " ") === false) return $path;
		return substr($path, 0, 3).'"'.substr($path, 3).'"';
	}
}
