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
 	 * @param $ignoreRedirects False, if no redirects should be returned.
 	 */
 	public abstract function getPages($namespaces = NULL, $requestoptions = NULL, $ignoreRedirects = false);
	
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
 	
 	/* 
 	 * Note: 
 	 * 		
 	 *   All following methods require a reference to a complete inheritance graph in memory. 
	 *	 They are intended to be used thousands of times in a row, since it is a complex
	 *	 task to load and prepare a complete inheritance graph for pathfinding at maximum speed. 
	 *	 So if you just need for instance a domain of _one_ super property, do this manually. 
	 * 
	 */
	 	
 	
 	/**
 	 * Returns the domain and ranges of the first super property which has defined some.
 	 * 
 	 * @param & $inheritance graph Reference to array of GraphEdge objects.
 	 * @param $a Property
 	 */ 	
 	public abstract function getDomainsAndRangesOfSuperProperty(& $inheritanceGraph, $p);
 	
 	/**
 	 * Determines minimum cardinality of an attribute,
 	 * which may be inherited.
 	 * 
 	 * @param & $inheritance graph Reference to array of GraphEdge objects.
 	 * @param $a Property
 	 */
 	public abstract function getMinCardinalityOfSuperProperty(& $inheritanceGraph, $a);
 	
 	/**
 	 * Determines minimum cardinality of an attribute,
 	 * which may be inherited.
 	 * 
 	 * @param & $inheritance graph Reference to array of GraphEdge objects.
 	 * @param $a Property
 	 */
 	public abstract function getMaxCardinalityOfSuperProperty(& $inheritanceGraph, $a);
 	
 	/**
 	 * Returns type of superproperty
 	 * 
 	 * @param & $inheritance graph Reference to array of GraphEdge objects.
 	 * @param $a Property
 	 */
 	public abstract function getTypeOfSuperProperty(& $inheritanceGraph, $a);
 	
 	/**
 	 * Returns categories of super property
 	 * 
 	 * @param & $inheritance graph Reference to array of GraphEdge objects.
 	 * @param $a Property
 	 */
 	public abstract function getCategoriesOfSuperProperty(& $inheritanceGraph, $a);
 	
 	/**
 	 * Returns a sorted array of (category,supercategory) page_id tuples
 	 * representing an category inheritance graph. 
 	 * 
 	 * @return array of GraphEdge objects;
 	 */
 	public abstract function getCategoryInheritanceGraph();
 	
 	/**
 	 * Returns a sorted array of (attribute,superattribute) page_id tuples
 	 * representing an attribute inheritance graph. 
 	 * 
 	 *  @return array of GraphEdge objects;
 	 */
 	public abstract function getPropertyInheritanceGraph();
 	
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
