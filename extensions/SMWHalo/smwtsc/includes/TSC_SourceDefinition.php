<?php
/**
 * @file
 * @ingroup LinkedDataAdministration
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
 * This file contains the class TSCSourceDefinition.
 * 
 * @author Thomas Schweitzer
 * Date: 13.04.2010
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the LinkedData extension. It is not a valid entry point.\n" );
}

 //--- Includes ---
 global $lodgIP;
//require_once("$lodgIP/...");

/**
 * This class describes the configuration of a Linked Data Source.
 * 
 * @author Thomas Schweitzer
 * 
 */
class TSCSourceDefinition  {
	
	//--- Constants ---
//	const XY= 0;		// the result has been added since the last time
		
	//--- Private fields ---
	
	// string: 	
	// A short name like "dbpedia" for the source (to be precise: the dataset of
	// a source). The ID will later be referenced in other places e.g. queries and
	// mappings. 
	private $mID;
	
	// string:
	// 	A optional textual description of the dataset. 
	private $mDescription;
	
	// string:
	// An optional label that provides the name of the dataset. 
	private $mLabel;
	
	// string (URI):
	// The homepage of the dataset. Note, this must be different from the 
	// homepage of the creator or publisher to avoid incorrect 'smushing'. If the
	// dataset has no homepage dedicated to it, then foaf:page  can be used instead. 
	private $mHomepage;
	
	// array<string> (URI):
	// This tag can be used to point to a URI within the dataset which can be 
	// considered a representative "sample". This is useful for Semantic Web 
	// clients to provide starting points for human exploration of the dataset. 
	// There can be any number of sample URIs. 
	private $mSampleURIs;
	
	// string (URI):
	// The location of a SPARQL protocol endpoint for the dataset. There can be 
	// zero or one for a dataset.
	private $mSparqlEndpointLocation;
	
	// string (URI):
	// If this optional parameter is present, then it specifies the URI of a 
	// named graph within the SPARQL endpoint. This named graph is assumed to 
	// contain the data of this dataset. This tag must be used only if 
	// sparqlEndpointLocation is also present, and there must be at most one 
	// sparqlGraphName per dataset.
	// If the data is distributed over multiple named graphs in the endpoint, 
	// then the publisher should either use a value of â€œ*â€� for this tag, or 
	// create separate datasets for each named graph.
	// If the tag is omitted, the dataset is assumed to be available through the 
	// endpoint's default graph. 
	private $mSparqlGraphName;
	
	// array<string> (URI):
	// If this optional parameter is present, then it specifies a list of graph
	// pattern restrictions that refer to the variables ?s, ?p, ?o and are applied
	// in conjunction.
	// An exemplary graph pattern restriction is FILTER (?p = <http://www.w3.org/1999/02/22-rdf-syntax-ns#type>).
	private $mSparqlGraphPatterns;

	// array<string>  (URI):
	// Indicates the location of an RDF data dump file. There can be any numbers
	// of data dump location values. The dataset is said to contain the RDF merge
	// of all the dumps. 
	private $mDataDumpLocations;
	
	// string (date):
	// This optional parameter, defined by the Sitemap protocol, gives the date 
	// of last modification of the dataset. This date should be in W3C Datetime
	// format. Example values are 2007-11-21 and 2007-11-21T14:41:09+00:00. 
	private $mLastMod;
	
	// string:
	// This optional tag, defined by the Sitemap protocol, describes how often 
	// the dataset is expected to be updated. Possible values are: always, hourly,
	// daily, weekly, monthly, yearly, never. 
	private $mChangeFreq;
	
	// array<string>  (URI):
	// Every RDF dataset uses one or more RDFS vocabularies or OWL ontologies. 
	// The vocabulary provides the terms (classes and properties) for expressing 
	// the data. The vocabulary property can be used to list vocabularies used in 
	// a dataset. 
	private $mVocabularies;
	
	// boolean
	// Is true if the datasource was imported at least once.
	private $mIsImported = false;
		
	// string
	// error message from last import operation
	private $mErrorMessagesLastImport = "";
	
	// date
	// last import date
	private $mLastImportDate = "";
	
	// array<string> (URI):
	// When importing from URIs, this property allows to specify a predicate
	// that should be followed by the crawler.
	private $mPredicatesToCrawl;
        
        // integer
	// Indicates the maximum number of levels to be crawled.
	private $mLevelsToCrawl;
	
	/**
	 * Constructor for TSCSourceDefinition.
	 *
	 * @param string $ID
	 * 		ID of the datasource.
	 */		
	function __construct($ID) {
		$this->mID = $ID;
	}
	

	//--- getter/setter ---
	public function getID()						{ return $this->mID; }
	public function getDescription()			{ return $this->mDescription; }
	public function getLabel()					{ return $this->mLabel; }
	public function getHomepage()				{ return $this->mHomepage; }
	public function getSampleURIs()				{ return $this->mSampleURIs; }
	public function getSparqlEndpointLocation()	{ return $this->mSparqlEndpointLocation; }
	public function getSparqlGraphName()		{ return $this->mSparqlGraphName; }
	public function getSparqlGraphPatterns()	{ return $this->mSparqlGraphPatterns; }
	public function getDataDumpLocations()		{ return $this->mDataDumpLocations; }
	public function getLastMod()				{ return $this->mLastMod; }
	public function getChangeFreq()				{ return $this->mChangeFreq; }
	public function getVocabularies()			{ return $this->mVocabularies; }
    public function isImported()               { return $this->mIsImported; }
    public function getErrorMessagesFromLastImport(){ return $this->mErrorMessagesLastImport; }
    public function getLastImportDate(){ return $this->mLastImportDate; }
	public function getPredicatesToCrawl()		{ return $this->mPredicatesToCrawl; }
        public function getLevelsToCrawl()		{ return $this->mLevelsToCrawl; }

	public function setID($val)						{ $this->mID = $val; }
	public function setDescription($val)			{ $this->mDescription = $val; }
	public function setLabel($val)					{ $this->mLabel = $val; }
	public function setHomepage($val)				{ $this->mHomepage = $val; }
	public function setSampleURIs(array $val)		{ $this->mSampleURIs = $val; }
	public function setSparqlEndpointLocation($val)	{ $this->mSparqlEndpointLocation = $val; }
	public function setSparqlGraphName($val)		{ $this->mSparqlGraphName = $val; }
	public function setSparqlGraphPatterns(array $val) { $this->mSparqlGraphPatterns = $val; }
	public function setDataDumpLocations(array $val) { $this->mDataDumpLocations = $val; }
	public function setLastMod($val)				{ $this->mLastMod = $val; }
	public function setChangeFreq($val)				{ $this->mChangeFreq = $val; }
	public function setVocabularies(array $val)		{ $this->mVocabularies = $val; }
    public function setImported($val)               { $this->mIsImported = $val; }
	public function setErrorMessagesFromLastImport($val){ $this->mErrorMessagesLastImport = $val; }
    public function setLastImportDate($val){ $this->mLastImportDate = $val; }
	public function setPredicatesToCrawl(array $val){ $this->mPredicatesToCrawl = $val; }
        public function setLevelsToCrawl($val){ $this->mLevelsToCrawl = $val; }
	//--- Public methods ---
	
	

	//--- Private methods ---
}