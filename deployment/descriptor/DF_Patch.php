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
 * This class represents a patch
 *
 * @author: Kai Kühn
 *
 */
class DFPatch {

	private $id;           // string (extension ID)
	private $minversion;   // DFVersion (minimal version patch can be applied)
	private $maxversion;   // DFVersion (maximal version patch can be applied)
	private $patchfile;    // string  (path of patchfile, relative to bundle)
	private $mayfail;      // boolean (inidicates if a patch may fail)

	public function __construct($id, $minversion, $maxversion, $patchfile, $mayfail) {
		$this->id = $id;
		$this->minversion = $minversion;
		$this->maxversion = $maxversion;
		$this->patchfile = $patchfile;
		$this->mayfail = $mayfail;
	}

	public function getID() {
		return $this->id;
	}

	public function getMinversion() {
		return $this->minversion;
	}

	public function getMaxversion() {
		return $this->maxversion;
	}

	public function getPatchfile() {
		return $this->patchfile;
	}

	public function mayFail() {
		return $this->mayfail;
	}
	
	/**
	 * Checks if the $patch is contained in $patches 
	 * 
	 * @param DFPatch[] $patches
	 * @param DFPatch $patch
	 * 
	 * @return boolean
	 */
	public static function containsPatchfile($patches, $patch) {
		foreach($patches as $p) {
			if ($p->getPatchfile() == $patch->getPatchfile()) return true;
		}
		return false;
	}
}
