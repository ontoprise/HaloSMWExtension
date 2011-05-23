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
			case "getdependencies":
				return $this->getDependencies($args);
			case "search":
				return $this->search($args);
			case "finalize":
				return $this->finalize($args);
			case "install":
				return $this->install($args);
			default: return "unsupported command";
		}
	}

	public function getDependencies($args) {
		global $mwrootDir, $dfgOut;
		$extid = reset($args);
		$dfgOut->setVerbose(false);
		$installer = Installer::getInstance($mwrootDir);
		$dependencies = $installer->getExtensionsToInstall($extid);
		$dfgOut->setVerbose(true);
		return json_encode($dependencies);
	}

	public function install($args) {
		global $mwrootDir, $dfgOut;
		$extid = reset($args);
		$filename = $dfgOut->start(DF_OUTPUT_TARGET_FILE);
		chdir($mwrootDir.'/deployment/tools');
		if (Tools::isWindows()) {
			$wshShell = new COM("WScript.Shell");
			$runCommand = "cmd /K START php $mwrootDir/deployment/tools/smwadmin/smwadmin.php --nocheck --noask -i $extid";
			$oExec = $wshShell->Run("$runCommand", 7, false);
		
		} else {
			//TODO: impl.linux command
		}
		return $filename;
	}

	public function deinstall($args) {
		global $mwrootDir;
		$extid = reset($args);
		$installer = Installer::getInstance($mwrootDir);
		$installer->deInstall($extid);
		return true;
	}

	public function finalize($args) {
		global $mwrootDir;
		chdir($mwrootDir.'/deployment/tools');
		if (Tools::isWindows()) {
			passthru($mwrootDir.'/deployment/tools/smwadmin.bat --finalize --nocheck');
		} else {
			passthru('sh '.$mwrootDir.'/deployment/tools/smwadmin.sh --finalize --nocheck');
		}
		return true;
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