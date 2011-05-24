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
require_once ( $mwrootDir.'/deployment/tools/smwadmin/DF_PackageRepository.php' );
require_once($mwrootDir.'/deployment/tools/smwadmin/DF_Tools.php');
require_once($mwrootDir.'/deployment/tools/smwadmin/DF_Installer.php');
require_once($mwrootDir.'/deployment/tools/smwadmin/DF_UserInput.php');

class DFCommandInterface {
	/**
	 *
	 *
	 */
	public function __construct() {

	}

	public function dispatch($command, $args) {
		switch($command) {
			case "readlog":
				return $this->readLog($args);
			case "getdependencies":
				return $this->getDependencies($args);
			case "search":
				return $this->search($args);
			case "finalize":
				return $this->finalize($args);
			case "install":
				return $this->install($args);
			case "deinstall":
				return $this->deinstall($args);
			case "checkforGlobalUpdate":
				return $this->checkforGlobalUpdate();
			case "doGlobalUpdate":
				return $this->doGlobalUpdate();
			default: return "unsupported command";
		}
	}

	public function readLog($args) {
		global $mwrootDir, $dfgOut;
		$filename = reset($args);
		$absoluteFilePath = Tools::getTempDir()."/$filename";
		if (!file_exists($absoluteFilePath)) {
			return '$$NOTEXISTS$$';
		}
		$log = file_get_contents($absoluteFilePath);
		return $log;
	}

	public function getDependencies($args) {
		global $mwrootDir, $dfgOut;
		$extid = reset($args);
		$dfgOut->setVerbose(false);
		try {
			$installer = Installer::getInstance($mwrootDir);
			$dependencies = $installer->getExtensionsToInstall($extid);
			$dfgOut->setVerbose(true);
			return json_encode($dependencies);
		} catch(InstallationError $e) {
			$error = array();
			$error['exception'] = array($e->getMsg(), $e->getErrorCode(), $e->getArg1(), $e->getArg2());
			return json_encode($error);
		}
	}

	public function install($args) {
		global $mwrootDir, $dfgOut;
		$extid = reset($args);
		$filename = uniqid().".log";
		chdir($mwrootDir.'/deployment/tools');
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd /K START php $mwrootDir/deployment/tools/smwadmin/smwadmin.php --logtofile $filename --outputformat html --nocheck --noask -i $extid";
			$oExec = $wshShell->Run("$runCommand", 7, false);

		} else {
			//TODO: impl.linux command
		}
		return $filename;
	}

	public function deinstall($args) {
		global $mwrootDir, $dfgOut;
		$extid = reset($args);
		$filename = uniqid().".log";
		chdir($mwrootDir.'/deployment/tools');
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd /K START php $mwrootDir/deployment/tools/smwadmin/smwadmin.php --logtofile $filename --outputformat html --nocheck --noask -d $extid";
			$oExec = $wshShell->Run("$runCommand", 7, false);

		} else {
			//TODO: impl.linux command
		}
		return $filename;
	}

	public function finalize($args) {
		global $mwrootDir, $dfgOut;
		$extid = reset($args);
		$filename = uniqid().".log";
		chdir($mwrootDir.'/deployment/tools');
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd /K START php $mwrootDir/deployment/tools/smwadmin/smwadmin.php --logtofile $filename --outputformat html --nocheck --noask --finalize";
			$oExec = $wshShell->Run("$runCommand", 7, false);

		} else {
			//TODO: impl.linux command
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
		}
	}
	
	public function doGlobalUpdate() {
		global $mwrootDir, $dfgOut;
        $extid = reset($args);
        $filename = uniqid().".log";
        chdir($mwrootDir.'/deployment/tools');
        if (Tools::isWindows()) {
            $wshShell = new COM("WScript.Shell");
            $runCommand = "cmd /K START php $mwrootDir/deployment/tools/smwadmin/smwadmin.php --logtofile $filename --outputformat html --nocheck --noask -u";
            $oExec = $wshShell->Run("$runCommand", 7, false);

        } else {
            //TODO: impl.linux command
        }
        return $filename;
	}

	public function search($args) {
		global $mwrootDir, $dfgOut, $dfgSearchTab;
		$searchValue = reset($args);

		$results = array();
		$packages = PackageRepository::searchAllPackages($searchValue);
		$localPackages = PackageRepository::getLocalPackages($mwrootDir);
		$dfgOut->outputln($dfgSearchTab->searializeSearchResults($packages, $localPackages));
		return true;
	}


}