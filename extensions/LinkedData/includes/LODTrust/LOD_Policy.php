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
 * This file contains the class LODPolicy.
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
 * This class describes a Linked Data Trust Policy.
 * 
 * @author Magnus Niemann
 * 
 */
class LODPolicy extends LODResource {

    //--- Constants ---

    //--- Private fields ---
    // string:
    // A short name like "dbpedia" for the policy.
    private $mID;
    // string:
    // 	A optional textual description of the policy.
    private $mDescription;
    // array<LODParameter>:
    // A list of parameters.
    private $mParameters;
    // LODHeuristic:
    // A conflict resolution heuristic.
    private $mHeuristic;
    // string:
    // A pattern describing the trust policy.
    private $mPattern;

    /**
     * Constructor for LODPolicy.
     *
     * @param string $ID
     * 		ID of the datasource.
     */
    function __construct($ID, $uri) {
        $this->mID = $ID;
        $this->setURI($uri);
    }

    //--- getter/setter ---
    public function getID() {
        return $this->mID;
    }

    public function getDescription() {
        return $this->mDescription;
    }

    public function getParameters() {
        return $this->mParameters;
    }

    public function getHeuristic() {
        return $this->mHeuristic;
    }

    public function getPattern() {
        return $this->mPattern;
    }

    public function setID($val) {
        $this->mID = $val;
    }

    public function setDescription($val) {
        $this->mDescription = $val;
    }

    public function setParameters($val) {
        $this->mParameters = $val;
    }

    public function setHeuristic($val) {
        $this->mHeuristic = $val;
    }

    public function setPattern($val) {
        $this->mPattern = $val;
    }

    //--- Public methods ---
    //--- Private methods ---
}
