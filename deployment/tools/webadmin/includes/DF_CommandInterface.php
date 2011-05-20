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
			case "search":
				return $this->search($args);
			case "install":
				return $this->install($args);
			default: return "unsupported command";
		}
	}

	public function install($args) {
		global $mwrootDir;
		$extid = reset($args);
		$installer = Installer::getInstance($mwrootDir);
		$installer->installOrUpdate($extid);
		
	}

	public function search($args) {
		global $mwrootDir, $dfgOut, $dfgSearchTab;
		$searchValue = reset($args);

		$results = array();
		$packages = PackageRepository::searchAllPackages($searchValue);
		$localPackages = PackageRepository::getLocalPackages($mwrootDir);
		$dfgOut->outputln($dfgSearchTab->searializeSearchResults($packages, $localPackages));
	}

	
}