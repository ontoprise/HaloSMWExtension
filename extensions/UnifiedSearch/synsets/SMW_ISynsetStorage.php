<?php
/*
 * Created on 18.02.2009
 *
 * Author: ingo
 */

/*
 * This interface is responsible for database access
 */
interface ISynsetStorage{

	/**
	 * Setups database for Synsets
	 *
	 * @param boolean $verbose
	 */
	public function setup($verbose);

	/**
	 * Adds a new term together with its corresponding synset id.
	 *
	 * @param string $term
	 * @param int $synsetId
	 */
	public function addTerm($term, $synsetId) ;

	/**
	 * Get all synonyms of a term. A term can in theory belong to
	 * several synsets. This method returns therefore an array of 
	 * arrays, which each contain the synonyms belonging to one
	 * of the synsets.
	 *
	 * @param string $term
	 * 
	 * @return Array<Array<String>>
	 */
	public function getSynsets($term);
	
	/**
	 * Fills the database with all synonyms contained in a sql dump
	 * 
	 * @param $file: a file handler	
	 */
	public function importSQLDump($file);

}

