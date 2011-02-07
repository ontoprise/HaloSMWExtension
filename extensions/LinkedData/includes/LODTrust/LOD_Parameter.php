<?php

/**
 * @file
 * @ingroup LinkedDataAdministration
 */
/*  Copyright 2011, MES GmbH
 *  This file is part of the LinkedData-Extension.
 *
 *   The LinkedData-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The LinkedData-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * This file contains the class LODParameter.
 * 
 * @author Magnus Niemann
 * Date: 06.01.2011
 * 
 */
if (!defined('MEDIAWIKI')) {
    die("This file is part of the LinkedData extension. It is not a valid entry point.\n");
}

//--- Includes ---
global $lodgIP;
//require_once("$lodgIP/...");

/**
 * This class describes a parameter.
 * 
 * @author Magnus Niemann
 * 
 */
class LODParameter extends LODResource {

    //--- Constants ---
    //--- Private fields ---
    // string:
    // A short name  for the parameter.
    private $mName;
    // string:
    // 	A label of the parameter.
    private $mLabel;
    // string:
    // 	A optional textual description of the parameter.
    private $mDescription;

    /**
     * Constructor for LODParameter.
     *
     * @param string $URI
     * 		URI of the parameter.
     */
    function __construct($uri) {
        $this->setURI($uri);
    }

    //--- getter/setter ---
    public function getName() {
        return $this->mName;
    }

    public function getDescription() {
        return $this->mDescription;
    }

    public function getLabel() {
        return $this->mLabel;
    }

    public function setName($val) {
        $this->mName = $val;
    }

    public function setDescription($val) {
        $this->mDescription = $val;
    }

    public function setLabel($val) {
        $this->mLabel = $val;
    }

    //--- Public methods ---
    //--- Private methods ---
}