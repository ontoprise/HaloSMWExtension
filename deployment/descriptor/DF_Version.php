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
 * @defgroup DFDeployDescriptor Deploy Descriptor
 * @ingroup DeployFramework
 *
 * This class represents a version number
 *
 * @author: Kai Kühn
 *
 */
class DFVersion {

	public static $MINVERSION;
    public static $MAXVERSION; 
    
	private $major;
	private $minor;
	private $subminor;


	public function __construct($version_string) {

		$this->parseVersions($version_string);

	}

	public function getMajor() {
		return $this->major;
	}

	public function getMinor() {
		return $this->minor;
	}

	public function getSubMinor() {
		return $this->subminor;
	}

	/**
	 * Parses a version string.
	 *
	 * Format: major.minor.subminor
	 *
	 * Examples:
	 *     * 1.40
	 *     * 1.10.4
	 *     * 4.3.10
	 *     * 1.4  -> is extended to 1.4.0
	 *
	 * Note: Backwards compatible to old version number format with 3 or 4 digits:
	 *
	 *  130 -> 1.3.0
	 *  1164 -> 1.16.4
	 *
	 * @param string $version_string
	 * @throws Exception
	 */
	private function parseVersions($version_string) {
		$parts = explode(".", $version_string);
		if (count($parts) < 2 || count($parts) > 3) {
			if (strlen($version_string) == 3 && is_numeric($version_string)) {
				// interprete it as old version number (before DF 1.6, for downwards compatibility)
				$this->major = intval($version_string[0]);
				$this->minor = intval($version_string[1]);
				$this->subminor = intval($version_string[2]);
				return;
			} else if (strlen($version_string) == 4 && is_numeric($version_string)) {
				// interprete it as old version number (before DF 1.6, for downwards compatibility)
				$this->major = intval($version_string[0]);
				$this->minor = intval($version_string[1].$version_string[2]);
				$this->subminor = intval($version_string[3]);
				return;
			} else if (strlen($version_string) == 2 && is_numeric($version_string)) {
                // e.g. 16 -> 1.6.0
                $this->major = intval($version_string[0]);
                $this->minor = intval($version_string[1]);
                $this->subminor = 0;
                return;
            }  else {
				// invalid format
				throw new Exception("Invalid DF version format: $version_string");
			}
		}
		$major = reset($parts);
		$minor = next($parts);
		$subminor = next($parts);

		if ($subminor === false) $subminor = 0;

		$this->major = intval($major);
		$this->minor = intval($minor);
		$this->subminor = intval($subminor);
	}

	public function toVersionString() {
		return "$this->major.$this->minor.$this->subminor";
	}

	public function isEqual(DFVersion $v) {
		return $this->major === $v->getMajor()
		&& $this->minor === $v->getMinor()
		&& $this->subminor === $v->getSubMinor();
	}

	public function isLower(DFVersion $v) {
		if ($this->major === $v->getMajor()) {
			if ($this->minor === $v->getMinor()) {
				return (is_numeric($this->subminor) && is_numeric($v->getSubminor())
				&& $this->subminor < $v->getSubMinor());
			} else {
				return $this->minor < $v->getMinor();
			}
		} else {
			return $this->major < $v->getMajor();
		}
	}

	public function isLowerOrEqual(DFVersion $v) {
		return $this->isEqual($v) || $this->isLower($v);
	}

	public function isHigher(DFVersion $v) {
		return !$this->isEqual($v) && !$this->isLower($v);
	}

	/**
	 * Sorts and compacts versions. That means it filters out all doubles.
	 *
	 * @param array of tuples(DFVersion, patchlevel, ...) $versions
	 */
	public static function sortVersions(& $versions) {

		// sort
		for($i = 0; $i < count($versions); $i++) {
			for($j = 0; $j < count($versions)-1; $j++) {

				if (is_array($versions[$j])) {
					// DFVersion with patchlevel
					list($ver1, $pl1) = $versions[$j];
					list($ver2, $pl2) = $versions[$j+1];
					if ($ver1->isEqual($ver2)) {
						if ($pl1 < $pl2) {
							$help = $versions[$j];
							$versions[$j] = $versions[$j+1];
							$versions[$j+1] = $help;
						}
					}
					if ($ver1->isLower($ver2)) {
						$help = $versions[$j];
						$versions[$j] = $versions[$j+1];
						$versions[$j+1] = $help;
					}
				} else {
					// DFVersion only
					$ver1 = $versions[$j];
					$ver2 = $versions[$j+1];
					
					if ($ver1->isLower($ver2)) {
						$help = $versions[$j];
						$versions[$j] = $versions[$j+1];
						$versions[$j+1] = $help;
					}
				}
			}
		}

		// remove doubles
		$result = array();
		$last = NULL;
		for($i = 0; $i < count($versions); $i++) {
			if (is_null($last)) {
				$last = $versions[$i];
				continue;
			}
			if (is_array($versions[$j])) {
				// DFVersion with patchlevel
				list($ver1, $pl1) = $last;
				list($ver2, $pl2) = $versions[$i];
				if($ver1->isEqual($ver2) && $pl1 === $pl2) {
					$versions[$i] = NULL;
				} else {
					$last = $versions[$i];
				}
			} else {
				// DFVersion
				$ver1 = $last;
				$ver2 = $versions[$i];
				if($ver1->isEqual($ver2)) {
					$versions[$i] = NULL;
				} else {
					$last = $versions[$i];
				}
			}

		}

		// remove NULLs
		$vresult = array();
		foreach($versions as $v) {
			if (!is_null($v)) $vresult[] = $v;
		}

		// set result array
		$versions = array();
		foreach($vresult as $v) $versions[] = $v;

	}

	/**
	 * Returns the maximum version.
	 *
	 * @param array of tuples(DFVersion, patchlevel, ...) $versions
	 *
	 * @return tuples(DFVersion, patchlevel, ...)
	 */
	public static function getMaxVersion(& $versions) {
		$maxTuple = NULL;
		$maxVersion = new DFVersion("0.0.0");
		foreach($versions as $tuple) {
			list($v,$p) = $tuple;
			if ($v->isHigher($maxVersion)) {
				$maxVersion = $v;
				$maxPatchlevel = $p;
				$maxTuple = $tuple;
			} else if ($v->isEqual($maxVersion)) {
				if ($p > $maxPatchlevel) {
					$maxPatchlevel = $p;
					$maxTuple = $tuple;
				}
			}
		}
		return $maxTuple;
	}

	public static function removePatchlevel($versionString) {
		if (strpos($versionString, "_") === false) return $versionString;
		return substr($versionString, 0, strpos($versionString, "_"));
	}
}

DFVersion::$MINVERSION = new DFVersion("00.00.00");
DFVersion::$MAXVERSION = new DFVersion("99.99.99");
