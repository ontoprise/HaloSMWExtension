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
 * This interface is responsible for filling the data base
 * with synonyms from a file
 */
interface ISynsetInitialiser {
	
	/**
	 * Reads the synsets from an sql dump and stores them in the database
	 * 
	 */
	public function storeSynsets();
	
	/**
	 * Reads the synsets from the original source and stores them in the database
	 * 
	 */
	public function storeSynsetsFromSource();
	
}

