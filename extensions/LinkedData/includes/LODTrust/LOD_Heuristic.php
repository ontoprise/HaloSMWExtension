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
 * @ingroup LinkedDataAdministration
 */
/**
 * This file contains the class LODHeuristic.
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

/**
 * This class describes a conflict resolution heuristic.
 * 
 * @author Magnus Niemann
 * 
 */
class LODHeuristic extends LODResource {

    //--- Constants ---
    //--- Private fields ---
    // string:
    // 	A label of the heuristic.
    private $mLabel;
    
    // array<LODParameter>:
    // A list of parameters.
    private $mParameters;

    /**
     * Constructor for LODHeuristic.
     *
     * @param string $URI
     * 		URI of the heuristic.
     */
    function __construct($URI) {
        $this->setURI($URI);
    }

    //--- getter/setter ---
    public function getLabel() {
        return $this->mLabel;
    }

    public function setLabel($val) {
        $this->mLabel = $val;
    }

    public function getParameters() {
        return $this->mParameters;
    }

    public function setParameters($pars) {
        $this->mParameters = $pars;
    }

    //--- Public methods ---
    //--- Private methods ---
}
