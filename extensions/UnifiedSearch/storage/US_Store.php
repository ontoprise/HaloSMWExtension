<?php
abstract class USStore {
	
	/**
	 * Lookup page titles in $namespaces
	 *
	 */
	public abstract function lookUpTitlesByText($termString, array $namespaces, $disjunctive = false, $limit=10, $offset=0); 
}
?>