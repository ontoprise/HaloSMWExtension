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
			$out[]= "\nChecking ".$p->getID()."...";
			$dependencies = $p->getDependencies();
			foreach($dependencies as $dep) {
				list($id, $min, $max) = $dep;

				// check if dependant package exists
				if (!array_key_exists($id, $localPackages)) {
					$out[]= "\n\tExtension missing: ".$id;
					$out[]= " [FAILED]";
					$errorInExt = true;
					$errorFound |= $errorInExt;
					continue;
				}

				// check if dependant package has correct version
				$ext = $localPackages[$id];
				if ($ext->getVersion() < $min || $ext->getVersion() > $max) {
					$out[]= "\n\tExtension ".$id." has wrong version: ".$ext->getVersion();
					$out[]= " [FAILED]";
					$out[]= "\n\tExpected range $min-$max";
					$errorInExt = true;
					$errorFound |= $errorInExt;
					continue;
				}
			}
			if (!$errorInExt) $out[]= " [OK] ";
		}
		return $errorFound;
	}
}