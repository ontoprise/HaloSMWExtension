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
		if (count($parts) > 3) {
			// invalid format
			throw new Exception();
		}
		$major = reset($parts);
		$minor = next($parts);
		$subminor = next($parts);


		$this->major = $major;
		$this->minor = $minor;
		$this->subminor = $subminor;
	}

	public function toVersionString() {
		return $this->subminor === false ? "$this->major.$this->minor" : "$this->major.$this->minor.$this->subminor";
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
		return !$this->Equal($v) && !$this->isLower($v);
	}
}
