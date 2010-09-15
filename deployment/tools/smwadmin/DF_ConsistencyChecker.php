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
 * 
 * @author: Kai Kuehn / ontoprise / 2010
 */
class ConsistencyChecker {

	var $rootDir;
    var $errorLog;
    
	public function __construct($rootDir) {
		$this->rootDir = $rootDir;
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
    }
    
	private  function checkDependencies() {
		
		$localPackages = PackageRepository::getLocalPackages($this->rootDir."/extensions");

		if (count($localPackages) == 0) {
			print "\nNO extensions found!\n";
			die(1);
		}
        
		print "\nChecking consistency of dependencies in installed packages...";
		$errorFound = MaintenanceTools::checkDependencies($localPackages, $out);
		foreach($out as $line) print $line;
		
		if ($errorFound) {
		
		}
		
	}


}