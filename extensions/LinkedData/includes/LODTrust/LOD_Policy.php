<?php

/**
 * @file
 * @ingroup LinkedDataAdministration
 */
/*  Copyright 2011, MediaEvent Services GmbH & Co. KG
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
//require_once("$lodgIP/...");

/**
 * This class describes a Linked Data Trust Policy.
 * 
 * @author Magnus Niemann
 * 
 */
class LODPolicy extends LODResource {

    //--- Constants ---
//	const XY= 0;		// the result has been added since the last time


    /*
     * 		a smw-lde:TrustPolicy ;
      smw-lde:ID "P2"^^xsd:string ;
      smw-lde:description "Just an example policy for testing purposes" ;
      smw-lde:parameter smwTrustPolicies:ParUsername ;
      smw-lde:heuristic smwTrustPolicies:PreferCurrentInformation ;
      smw-lde:pattern """{
      GRAPH smwGraphs:ProvenanceGraph {
      ?GRAPH swp:assertedBy ?warrant .
      ?warrant swp:authority ?dataSource .
      ?GRAPH smw-lde:created ?retrievalDate .
      }

      GRAPH smwGraphs:UserGraph {
      ?PAR_USER smw-lde:trusts ?trustStatement .
      ?trustStatement smw-lde:authority ?dataSource .
      }
      }""" ;
      .

     */

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