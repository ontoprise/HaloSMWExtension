<?php
/*  Copyright 2011, ontoprise GmbH
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
 * @ingroup DFDeployDescriptor
 *
 * @defgroup DFDeployDescriptor Deploy Descriptor
 * @ingroup DeployFramework
 *
 * This class represents a version number
 *
 * @author: Kai Kühn / ontoprise / 2011
 *
 */
class DFVersion {


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

	private function parseVersions($version_string) {
		$parts = explode(".", $version_string);
		if (count($parts) < 2 || count($parts) > 3) {
			if (strlen($version_string) == 3 && is_numeric($version_string)) {
				// interprete it as old version number (before DF 1.6, for downwards compatibility)
				$this->major = $version_string[0];
				$this->minor = $version_string[1];
				$this->subminor = $version_string[2];
				return;
			} else if (strlen($version_string) == 4 && is_numeric($version_string)) {
                // interprete it as old version number (before DF 1.6, for downwards compatibility)
                $this->major = $version_string[0];
                $this->minor = $version_string[1].$version_string[2];
                $this->subminor = $version_string[3];
                return;
            } else {
				// invalid format
				throw new Exception("Invalid DF version format: $version_string");
			}
		}
		$major = reset($parts);
		$minor = next($parts);
		$subminor = next($parts);
        
		if ($subminor === false) $subminor = 0;

		$this->major = $major;
		$this->minor = $minor;
		$this->subminor = $subminor;
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
}
