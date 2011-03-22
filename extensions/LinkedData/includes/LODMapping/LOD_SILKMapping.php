<?php
/**
 * @file
 * @ingroup LinkedData
 */

/*  Copyright 2010, ontoprise GmbH
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
 * This file contains the class LODSILKMapping.
 * 
 * @author Ingo Steinbauer
 * Date: 28.02.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

/**
 * This class manages SILK mappings.
 * 
 * @author Ingo Steinbauer
 * 
 */
class LODSILKMapping  extends LODMapping{
	
	//--- Private fields ---
	public static $mappingType = 'smw-lde:SilkMatchingDescription';
	
	// array
	//  This is the uriMintNamespace
	private $mMintNamespace;
	
	// array
	//  This is an array of uriMintLabelPredicates
	private $mMintLabelPredicates;
	
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
	 * @param string $mintNamespace
	 *		The uriMintNamespace,
	 * @param array $mintLabelPredicates
	 * 		The uriMintLabelPredicates
	 */		
	function __construct($uri, $mappingText, $source, $target = null, $mintNamespace = null,
		$mintLabelPredicates = array(), $additionalProps = null) {
		parent::__construct($uri, $mappingText, $source, $target);
		
		$this->mMintNamespace = $mintNamespace;
		$this->mMintLabelPredicates = $mintLabelPredicates;
		
		if(is_array($additionalProps)){
			foreach($additionalProps as $prop => $values){
				if($prop == $this->getMintNamespaceProp(false)){
					$this->mMintNamespace = '<'.$values[0].'>';
				} else if($prop == $this->getMintLabelPredicateProp(false)){
					foreach($values as $val){
						$this->mMintLabelPredicates[] = '<'.$val.'>';
					}
				}
			}
		}
	}
	

	//--- getter/setter ---
	public function getMintNamespace()			{return $this->mMintNamespace;}
	public function getMintLabelPredicates()			{return $this->mMintLabelPredicates;}
	
	//methods
	
	/*
	 * get the triples, which represent this mapping 
	 */
	public function getTriples(){
		$triples = parent::getTriples();
		
		$pm = LODPrefixManager::getInstance();
		
		$subject = $this->getSubject();

		//set uriMintNamespace
		$property = $this->getMintNamespaceProp();
		$triples[] = new LODTriple($subject, $property, $this->mMintNamespace, '__objectURI');
		
		//Set uriMintLabelPredicates
		$property = $this->getMintLabelPredicateProp();
		foreach($this->mMintLabelPredicates as $mintLabelPredicate){
			$triples[] = new LODTriple($subject, $property, $mintLabelPredicate, '__objectURI');
		}
		
		return $triples;
	}
	
	private function getMintNamespaceProp($braced = true){
		$pm = LODPrefixManager::getInstance();
		
		$prop = 'smw-lde:uriMintNamespace';
		$prop = $pm->makeAbsoluteURI($prop, $braced);
		
		return $prop;
	}
	
	private function getMintLabelPredicateProp($braced = true){
		$pm = LODPrefixManager::getInstance();
		
		$prop = 'smw-lde:uriMintLabelPredicate';
		$prop = $pm->makeAbsoluteURI($prop, $braced);
		
		return $prop;
	}
	
	/*
	 * Returns true if this mapping equals the given one.
	 */
	public function equals($mapping){
		if(!parent::equals($mapping)) return false;

		if(!($mapping instanceof LODSILKMapping)) return false;
		
		if($this->getMintNamespace() != $mapping->getMintNamespace()) return false;
		
		if(count($this->getMintLabelPredicates()) 
			!= count($mapping->getMintLabelPredicates())) return false;
		
		foreach($this->getMintLabelPredicates() as $pred){
			if(!in_array($pred, $mapping->getMintLabelPredicates())) return false;
		}	
			
		return true;
	}

	/*
	 * Get the type URI of this mapping
	 */
	public function getMappingType(){
		return self::$mappingType;
	}
	
}






