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
 	public $domainRangeHintRelation;
 	
 	
	
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
	
	public $inverseOf;
	/**
	 * Must be called from derived class to initialize the member variables.
	 */
	protected function SMWSemanticStore(Title $domainRangeHintRelation,
									 Title $minCard, Title $maxCard, 
									 Title $transitiveCat, Title $symetricalCat, Title $inverseOf) {
		$this->domainRangeHintRelation = $domainRangeHintRelation;
	
		$this->maxCard = $maxCard;
		$this->minCard = $minCard;
		$this->transitiveCat = $transitiveCat;
		$this->symetricalCat = $symetricalCat;
		$this->inverseOf = $inverseOf;
	}
 	
 	/**
 	 * Initializes all tables and predefined pages.
 	 */
 	public abstract function setup($verbose);
 	
 	/**
 	 * Returns pages of the given namespace.
 	 * 
 	 * @param $namespaces Array of ns constants.
 	 * @param $requestoptions SMWRequestOptions object.
 	 * @param $addRedirectTargets If false, redirect are completely ignored. Otherwise their targets are added. 
 	 * 
 	 * @return array of Title
 	 */
 	public abstract function getPages($namespaces = NULL, $requestoptions = NULL, $addRedirectTargets = false);
	
	/**
	 * Returns root categories (categories which have no super-category).
	 * 
	 * @return array of Title
	 */
	public abstract function getRootCategories($requestoptions = NULL);
	
	
	/**
	 *  Returns root properties (properties which have no super-property).
	 * 
	 * @return array of Title
	 */
	public abstract function getRootProperties($requestoptions = NULL);
	
	/**
	 * Returns direct subcategories of $categoryTitle.
	 * 
	 * @return array of Title
	 */
	public abstract function getDirectSubCategories(Title $categoryTitle, $requestoptions = NULL);
	
	/**
	 * Returns all subcategories of $category
	 * 
	 * @param $category
	 * @return array of Title
	 */
	public abstract function getSubCategories(Title $category);
	
	/**
	 * Returns direct supercategories of $categoryTitle.
	 * 
	 * @return array of Title
	 */
	public abstract function getDirectSuperCategories(Title $categoryTitle, $requestoptions = NULL);
	
	/**
	 * Returns all categories the given instance is member of.
	 * 
	 * @return array of Title
	 */
	public abstract function getCategoriesForInstance(Title $instanceTitle, $requestoptions = NULL);
	
	/**
	 * Returns all instances of $categoryTitle including instances of all subcategories of $categoryTitle.
	 * 
	 * In the case of a cycle in the category inheritance graph, this method has a treshhold
	 * to stop execution before a stack overflow occurs.
	 * 
	 * @return array of tuples (instance, category)
	 */
	public abstract function getInstances(Title $categoryTitle, $requestoptions = NULL); 
	
	/**
	 * Returns all direct instances of $categoryTitle
	 * 
	 * @return array of Title
	 */
	public abstract function getDirectInstances(Title $categoryTitle, $requestoptions = NULL);
	
		
	/**
	 * Returns all properties with schema of $categoryTitle (including inherited).
	 * 
	 * @return array of tuples: (title, minCard, maxCard, type, isSym, isTrans, range)
	 */
	public abstract function getPropertiesWithSchemaByCategory(Title $categoryTitle, $requestoptions = NULL); 
	
	/**
	 * Returns all properties of matching $requestoptions
	 * 
	 * array of tuples: (title, minCard, maxCard, type, isSym, isTrans, range)
	 */
	public abstract function getPropertiesWithSchemaByName($requestoptions); 

	/**
 	* Returns direct properties of $categoryTitle (but no schema-data!)
 	* 
 	* @return array of Title
 	*/	
	public abstract function getDirectPropertiesByCategory(Title $categoryTitle, $requestoptions = NULL);
	
	/**
 	  * Returns all domain categories for a given property.
 	  */
	public abstract function getDomainCategories($propertyTitle, $reqfilter);
	/**
	 * Returns all direct subproperties of $property.
	 * 
	 * @return array of Title
	 */
	public abstract function getDirectSubProperties(Title $property, $requestoptions = NULL);
	
	/**
	 * Returns all direct superproperties of $property.
	 * 
	 * @return array of Title
	 */
	public abstract function getDirectSuperProperties(Title $property, $requestoptions = NULL); 
	
	/**
	 * Returns all pages which are redirects to the given page.
	 * 
	 * @param $title Target of redirects
	 * 
	 * @return array of Title objects
	 */
	public abstract function getRedirectPages(Title $title);
	
	/**
	 * Returns the redirect target, if $title is a redirect.
	 * Otherwise $title itsself is returned.
	 * 
	 * @param $title possible redirect page
	 * 
	 * @return Target of redirect or $title.
	 */
	public abstract function getRedirectTarget(Title $title);
	
	
	
	/**
	 * Returns total number of usages of $property on arbitrary wiki pages.
	 */
	public abstract function getNumberOfUsage(Title $property);
 	
 	/**
 	 * Returns number of (direct and indirect) instances and number of subcategories.
 	 * 
 	 * @param $category Title
 	 * @return array($numOfInstance, $numOfCategories);
 	 */
 	public abstract function getNumberOfInstancesAndSubcategories(Title $category);
 	
 	/**
 	 * Returns number of properties for a $category.
 	 * 
 	 * @param $category
 	 */
 	public abstract function getNumberOfProperties(Title $category);
 	
 	/**
 	 * Returns number of annotation which have $target as their target.
 	 * 
 	 * @param $target Title
 	 */
 	public abstract function getNumberOfPropertiesForTarget(Title $target);
 	
 	/**
 	 * Returns all different units of annotations of a given type.
 	 * 
 	 * @param Title $type
 	 * 
 	 * @return array of strings
 	 */
 	public abstract function getDistinctUnits(Title $type);
 	
 	/**
 	 * Returns all annotations of the given user-defined type with the given unit.
 	 * 
 	 * @param Title $type
 	 * @param unit string
 	 * 
 	 * @return array of (Title subject, Title property)
 	 */
 	public abstract function getAnnotationsWithUnit(Title $type, $unit);
 	
 	/**
 	 * Returns all annotations tuples ($property, $value, $rating) of $subject.
 	 * 
 	 * @param $subject (DBkey) 
 	 */
 	public abstract function getRatedAnnotations($subject);
 	
 	/**
 	 * Returns an array of $limit annotations (non-nary) which are not yet rated.
 	 * 
 	 * @param $limit integer
 	 * @return array of titles (subject, predicate, objectOrValue)
 	 */
 	public abstract function getAnnotationsForRating($limit);
 	// Methods which modifies the database
 	
 	/**
 	 * Rate annotation specified by $subject, $predicate, $object
 	 * 
 	 * @param $subject (DBkey)
 	 * @param $predicate (DBkey)
 	 * @param $object (DBkey)
 	 * @param $rating Integer (positive or negative)
 	 * 
 	 */
 	public abstract function rateAnnotation($subject, $predicate, $object, $rating);
 	
 	
 	/**
 	 * Replaces redirect annotations, i.e. pages with annotations made with redirect 
 	 * property pages. Does also replace such annotations on template pages with the usual 
 	 * constraints. Modifies database!
 	 * 
 	 * @param $verbose If true, method prints some output.
 	 */
 	public abstract function replaceRedirectAnnotations($verbose = false);
 }
?>
