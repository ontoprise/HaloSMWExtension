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
 * @ingroup LinkedData
 */
/**
 * This file contains the class LODMapping.
 * 
 * @author Thomas Schweitzer, Ingo Steinbauer
 * Date: 12.05.2010
 * 
 */
if (!defined('MEDIAWIKI')) {
    die("This file is part of the LinkedData extension. It is not a valid entry point.\n");
}

/**
 * This class manages mappings among different LOD sources.
 * This is the super class for R2R and SILK mappings
 * 
 * @author Thomas Schweitzer, Ingo Steinbauer
 * 
 */
abstract class LODMapping {


    /*
     * This counter is used to generate mapping URIs
     */

    public static $mappingCounter = 0;
    //--- Constants ---
    //--- Private fields ---
    // string
    // This is the ID of the source of the mapping (see the ID of class
    // TSCSourceDefinition). By convention, the name of the article that defines
    // the mapping is also the source.
    private $mSource;
    // string
    // This is the ID of the target of the mapping, which is typically the wiki.
    // The default value can be configured with the global variable
    // $lodgDefaultMappingTarget.
    private $mTarget;
    // string
    // The "source code" of the mapping.
    private $mMappingText;
    // string
    // The URI
    private $mUri;

    /**
     * Constructor for  LODMapping
     *
     * @param string $mappingText
     * 		The text of the mapping 
     * @param string $source
     * 		The ID of the mapping's source
     * @param string $target
     * 		The ID of the mapping's target. If not set, the default mapping 
     * 		target that is defined in the global variable $lodgDefaultMappingTarget
     * 		is used.
     * 
     */
    function __construct($uri, $mappingText, $source, $target = null) {
        global $lodgDefaultMappingTarget;
        $this->mSource = $source;
        $this->mTarget = isset($target) ? $target : $lodgDefaultMappingTarget;
        $this->mMappingText = $mappingText;
        $this->mUri = $uri;
    }

    //--- getter/setter ---
    public function getSource() {
        return $this->mSource;
    }

    public function getTarget() {
        return $this->mTarget;
    }

    public function getMappingText() {
        return $this->mMappingText;
    }

    public function getURI() {
        return $this->mUri;
    }

    public function getID() {
        return preg_replace("/.*\//", "", $this->mUri);
    }

    public function setSource($s) {
        $this->mSource = $s;
    }

    public function setTarget($t) {
        $this->mTarget = $t;
    }

    public function setMappingText($mappingText) {
        $this->mMappingText = $mappingText;
    }

    public function setURI($u) {
        $this->mUri = $u;
    }

    //--- Public methods ---

    /*
     * Get the triples, which represent this mapping
     */
    public function getTriples() {
        $triples = array();

        $pm = TSCPrefixManager::getInstance();

        self::$mappingCounter += 1;
        $subject = $this->getSubject();

        ////Set mapping type
        $property = self::getTypeProp();
        $object = $this->getMappingType();
        $object = $pm->makeAbsoluteURI($object);
        $triples[] = new TSCTriple($subject, $property, $object, '__objectURI');

        //set mapping source
        $property = self::getSourceProp();
        $object = 'smwDatasources:' . $this->getSource();
        $object = $pm->makeAbsoluteURI($object);
        $triples[] = new TSCTriple($subject, $property, $object, '__objectURI');

        //set target
        $property = self::getTargetProp();
        $object = 'smwDatasources:' . $this->getTarget();
        $object = $pm->makeAbsoluteURI($object);
        $triples[] = new TSCTriple($subject, $property, $object, '__objectURI');

        //Set mapping description
        $property = self::getCodeProp();
        $triples[] = new TSCTriple($subject, $property, $this->getMappingText(), 'xsd:string');

        return $triples;
    }

    /*
     * get SPARQL query for searching this mapping in the TSC
     */

    public static function getQueryString($source = null, $target = null, $mappingType = null) {
        $queryString = "SELECT ?mapping ?p ?o ";
        $queryString .= " WHERE { ?mapping ?p ?o. ";

        $pm = TSCPrefixManager::getInstance();

        $where = '';
        if (!is_null($mappingType)) {
            $mappingType = 'LOD' . strtoupper($mappingType) . 'Mapping';
            $mappingType = get_class_vars($mappingType);
            $mappingType = $mappingType['mappingType'];
            $property = self::getTypeProp();
            $object = $mappingType;
            $object = $pm->makeAbsoluteURI($object);
            $queryString .= ' ?mapping ' . $property . ' ' . $object . '. ';
        }
        if (!is_null($source)) {
            $property = self::getSourceProp();
            $object = 'smwDatasources:' . $source;
            $object = $pm->makeAbsoluteURI($object);
            $queryString .= ' ?mapping ' . $property . ' ' . $object . '. ';
        }
        if (!is_null($target)) {
            $property = self::getTargetProp();
            $object = 'smwDatasources:' . $target;
            $object = $pm->makeAbsoluteURI($object);
            $queryString .= ' ?mapping ' . $property . ' ' . $object . '. ';
        }

        $queryString .= ' } ';

        return $queryString;
    }

    /*
     * Create a LODMapping object from a SPARQL query result
     */

    public static function createMappingFromSPARQLResult($mappingData, $uri) {
        $pm = TSCPrefixManager::getInstance();

        $type = null;
        $source = null;
        $target = null;
        $code = null;
        foreach ($mappingData as $prop => $values) {
            if ($prop == self::getTypeProp(false)) {
                if ($values[0] == $pm->makeAbsoluteURI(LODSILKMapping::$mappingType, false)) {
                    $type = 'SILK';
                } else if ($values[0] == $pm->makeAbsoluteURI(LODR2RMapping::$mappingType, false)) {
                    $type = 'R2R';
                }
                unset($mappingData[$prop]);
            } else if ($prop == self::getSourceProp(false)) {
                $source = $values[0];
                $source = str_replace($pm->getNamespaceURI('smwDatasources'), '', $source);
                unset($mappingData[$prop]);
            } else if ($prop == self::getTargetProp(false)) {
                $target = $values[0];
                $target = str_replace($pm->getNamespaceURI('smwDatasources'), '', $target);
                unset($mappingData[$prop]);
            } else if ($prop == self::getcodeProp(false)) {
                $code = $values[0];
                unset($mappingData[$prop]);
            }
        }

        if (!is_null($type) && !is_null($source) && !is_null($target) && !is_null($code)) {
            $class = 'LOD' . $type . 'Mapping';
            return new $class($uri, $code, $source, $target, null, null, $mappingData);
        } else {
            return null;
        }
    }

    /*
     * Returns true if this mapping equals the given one.
     */

    public function equals($mapping) {
        if (!($mapping instanceof LODMapping))
            return false;

        if ($this->getSource() != $mapping->getSource())
            return false;

        if ($this->getTarget() != $mapping->getTarget())
            return false;

        if ($this->getMappingText() != $mapping->getMappingText())
            return false;

        return true;
    }

    //--- Private methods ---

    /*
     * get URI of this mapping
     */
    protected function getSubject() {
        $pm = TSCPrefixManager::getInstance();
        $subject = 'smwDatasourceLinks:' . $this->getSource() . '_to_' . $this->getTarget() . '_Mapping_' . self::$mappingCounter;
        $subject = $pm->makeAbsoluteURI($subject);
        return $subject;
    }

    private static function getTypeProp($braced = true) {
        $pm = TSCPrefixManager::getInstance();

        $prop = 'rdf:type';
        $prop = $pm->makeAbsoluteURI($prop, $braced);

        return $prop;
    }

    private static function getSourceProp($braced = true) {
        $pm = TSCPrefixManager::getInstance();

        $prop = 'smw-lde:linksFrom';
        $prop = $pm->makeAbsoluteURI($prop, $braced);

        return $prop;
    }

    private static function getTargetProp($braced = true) {
        $pm = TSCPrefixManager::getInstance();

        $prop = 'smw-lde:linksTo';
        $prop = $pm->makeAbsoluteURI($prop, $braced);

        return $prop;
    }

    private static function getCodeProp($braced = true) {
        $pm = TSCPrefixManager::getInstance();

        $prop = 'smw-lde:sourceCode';
        $prop = $pm->makeAbsoluteURI($prop, $braced);

        return $prop;
    }

    /*
     * Get type ORO pf this mapping
     */

    abstract public function getMappingType();

    public static function id2uri($id) {
        $pm = TSCPrefixManager::getInstance();
        $ns = $pm->getNamespaceURI("smwDatasourceLinks");
        return $ns.$id;
    }
}

