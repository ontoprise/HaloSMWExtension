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
 * This class represents a dependency
 *
 * @author: Kai Kühn
 *
 */

class DFDependency {


	private $from; /* DFVersion minimum required version */
	private $to;   /* DFVersion maximum required version */
	private $ids;  /* Bundle IDs string[] 
					   multiple IDs means alternatives, NOT that 
	                   all extensions are required altogether */
	private $optional; /* boolean */
	private $message; /* string */
	
    /**
     * Constructor
     *  
     * @param string $idsString pipe separated list of alternatives.
     * @param DFVersion $from
     * @param DFVersion $to
     * @param boolean $optional default is false
     * @param string $message default is empty string
     */
	public function __construct($idsString, DFVersion $from, DFVersion $to, $optional = false, $message = '') {
		$this->ids = explode("|",$idsString);
		$this->from = $from;
		$this->to = $to;
		$this->optional = $optional;
		$this->message = $message;
	}

	public function getIDs() {
		return $this->ids;
	}

	public function getMinVersion() {
		return $this->from;
	}

	public function getMaxVersion() {
		return $this->to;
	}

	public function isOptional() {
		return $this->optional;
	}

	public function getMessage() {
		return $this->message;
	}
	
	public function toString() {
		$optionalText = $this->optional ? "(optional)" : "";
		return "one of (".implode(",",$this->ids).") in range ".$this->from->toVersionString()." - ".$this->to->toVersionString()."; $optionalText";
	}
    
	/**
	 * Returns true if the given $id matches one of the alternatives of this dependency.
	 * 
	 * @param string $id
	 * @param DFVersion $version (optional)
	 * 
	 * @return boolean
	 */
	public function matchBundle($id, $version = NULL) {
		if (!is_null($version)) {
			return in_array($id, $this->ids) && $this->from->isLowerOrEqual($version)
			&& $version->isLowerOrEqual($this->to);
		} else {
			return in_array($id, $this->ids);
		}
	}

	/**
	 * Check if at least one of the dependency alternatives matches the set
	 * of extension IDs or DeployDescriptors.
	 *
	 * @param mixed string[] / DeployDescriptor[] $set
	 *
	 * @return mixed boolean/string False if not found, otherwise the ID
	 */
	public function isContained(array $set) {
		foreach($set as $s) {
			if (is_string($s)) {
				foreach($this->ids as $id) {
					if ($s == $id) return $id;
				}
			} else {
				foreach($this->ids as $id) {
					if ($s->getID() == $id) return $id;
				}
			}
		}
		return false;
	}

}
