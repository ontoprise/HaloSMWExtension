<?php
/*
 * Created on 18.02.2009
 *
 * Author: ingo
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

