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
 * @ingroup EnhancedRetrievalSynsets
 * 
 * @author Ingo Steinbauer
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

