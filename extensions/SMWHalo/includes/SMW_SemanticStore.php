<?php
/*
 * Created on 19.09.2007
 *
 * Author: kai
 */
 
 abstract class SMWSemanticStore {
 	
 	/**
 	 * Domain hint relation. 
 	 * Determines the domain of an attribute or relation. 
 	 */
 	public $domainHintRelation;
 	
 	/**
 	 * Range hint relation. 
 	 * Determines the range of a relation. 
 	 */
	public $rangeHintRelation;
	
	/**
	 * Minimum cardinality. 
	 * Determines how often an attribute or relations must be instantiated per instance at least.
	 * Allowed values: 0..n, default is 0.
	 */
	public $minCard;
	
	/**
	 * Maximum cardinality. 
	 * Determines how often an attribute or relations may instantiated per instance at most.
	 * Allowed values: 1..*, default is *, which means unlimited.
	 */
	public $maxCard;
	
	/**
	 * Transitive category
	 * All relations of this category are transitive.
	 */
	public $transitiveCat;
	
	/**
	 * All relations of this category are symetrical.
	 */
	public $symetricalCat;
	
	/**
	 * Must be called from derived class to initialize the member variables.
	 */
	protected function SMWSemanticStore(Title $domainHintRelation, Title $rangeHintRelation, 
									 Title $minCard, Title $maxCard, 
									 Title $transitiveCat, Title $symetricalCat) {
		$this->domainHintRelation = $domainHintRelation;
		$this->rangeHintRelation = $rangeHintRelation;
		$this->maxCard = $maxCard;
		$this->minCard = $minCard;
		$this->transitiveCat = $transitiveCat;
		$this->symetricalCat = $symetricalCat;
	}
 	
 	/**
 	 * Returns pages of the given namespace
 	 */
 	public abstract function getPages($namespaces = NULL, $requestoptions = NULL);
	
	/**
	 * Returns root categories (categories which have no super-category).
	 */
	public abstract function getRootCategories($requestoptions = NULL);
	
	/**
	 *  Returns root properties (properties which have no super-property).
	 */
	public abstract function getRootProperties($requestoptions = NULL);
	
	/**
	 * Returns direct subcategories of $categoryTitle.
	 */
	public abstract function getDirectSubCategories(Title $categoryTitle, $requestoptions = NULL);
	
	/**
	 * Returns direct supercategories of $categoryTitle.
	 */
	public abstract function getDirectSuperCategories(Title $categoryTitle, $requestoptions = NULL);
	
	/**
	 * Returns all categories the given instance is member of.
	 */
	public abstract function getCategoriesForInstance(Title $instanceTitle, $requestoptions = NULL);
	
	/**
	 * Returns all instances of $categoryTitle including instances of all subcategories of $categoryTitle.
	 * 
	 * In the case of a cycle in the category inheritance graph, this method should have at least a treshhold
	 * to stop execution before a stack overflow occurs.
	 */
	public abstract function getInstances(Title $categoryTitle, $requestoptions = NULL); 
	
	/**
	 * Returns all direct instances of $categoryTitle
	 */
	public abstract function getDirectInstances(Title $categoryTitle, $requestoptions = NULL);
	
	/**
	 * Returns all properties of $categoryTitle including properties of all subcategories of $categoryTitle.
	 * 
	 * In the case of a cycle in the category inheritance graph, this method should have at least a treshhold
	 * to stop execution before a stack overflow occurs.
	 */	
	public abstract function getPropertiesOfCategory(Title $categoryTitle, $requestoptions = NULL);
	
	/**
	 * Returns all direct properties of $categoryTitle.
	 */
	public abstract function getDirectPropertiesOfCategory(Title $categoryTitle, $requestoptions = NULL); 
	
	/**
	 * Returns all direct subproperties of $property.
	 */
	public abstract function getDirectSubProperties(Title $property, $requestoptions = NULL);
	
	/**
	 * Returns all direct superproperties of $property.
	 */
	public abstract function getDirectSuperProperties(Title $property, $requestoptions = NULL); 
	
	/**
	 * Returns total number of usages of $property on arbitrary wiki pages.
	 */
	public abstract function getNumberOfUsage(Title $property);
 	
 }
?>
