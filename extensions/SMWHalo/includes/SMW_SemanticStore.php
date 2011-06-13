<?php
/**
 * @file
 * @ingroup SMWHaloSemanticStorage
 * 
 * Created on 19.09.2007
 *
 * Author: kai
 * 
 * @defgroup SMWHaloSemanticStorage SMWHalo Semantic storage layer
 * @ingroup SMWHalo
 */
 
 abstract class SMWSemanticStore {
 	
 	/**
 	 * SMWHalo defines some properties with special semantics 
 	 */
 	
 	
 	/**
 	 * Domain hint property. 
 	 * Determines the domain and range of a property.
 	 * 
 	 * It is a n-ary properties with take the domain as first parameter
 	 * and the range as second. The range is optional.
 	 * 
 	 * It is defined as: 
 	 *    [[has type::Type:Record]] 
 	 *    [[has fields::Type:Page; Type:Page]]
 	 * 
 	 * @var Title 
 	 */
 	public $domainRangeHintRelation;
 		
	/**
	 * Minimum cardinality. 
	 * Determines how often an attribute or relations must be instantiated per instance at least.
	 * Allowed values: 0..n, default is 0.
	 * 
	 * @var Title
	 */
	public $minCard;
	
	/**
	 * Maximum cardinality. 
	 * Determines how often an attribute or relations may instantiated per instance at most.
	 * Allowed values: 1..*, default is *, which means unlimited.
	 * 
	 * @var Title
	 */
	public $maxCard;
	
	/**
	 * Transitive category.
	 * All relations of this category are transitive.
	 * 
	 * @var Title
	 */
	public $transitiveCat;
	
	/**
	 * Symmtric category.
	 * All relations of this category are symetrical.
	 * 
	 * @var Title
	 */
	public $symetricalCat;
	
	/**
	 * Inverse property. Binary property which defines the inverse property.
	 * 
	 * [[has type::Type:Page]]
	 * 
	 * @var Title
	 */
	public $inverseOf;
	
	/**
	 * Ontology representation in OntoStudio.
	 *  
	 * [[has type::Type:URL]]
	 * 
	 * @var Title
	 */
	public $ontologyURI;
	
	/**
	 * SMWPropertyValue version of properties above 
	 */
	public $domainRangeHintProp;
	public $minCardProp;
	public $maxCardProp;
	public $inverseOfProp;
	public $ontologyURIProp;
	
	/**
	 * Must be called from derived class to initialize the member variables.
	 */
	protected function SMWSemanticStore(Title $domainRangeHintRelation,
									 Title $minCard, Title $maxCard, 
									 Title $transitiveCat, Title $symetricalCat, Title $inverseOf, Title $ontologyURI) {
		$this->domainRangeHintRelation = $domainRangeHintRelation;
	
		$this->maxCard = $maxCard;
		$this->minCard = $minCard;
		$this->transitiveCat = $transitiveCat;
		$this->symetricalCat = $symetricalCat;
		$this->inverseOf = $inverseOf;
		$this->ontologyURI = $ontologyURI;
		
		$this->domainRangeHintProp = SMWPropertyValue::makeUserProperty($this->domainRangeHintRelation->getDBkey());
        $this->minCardProp = SMWPropertyValue::makeUserProperty($this->minCard->getDBkey());
        $this->maxCardProp = SMWPropertyValue::makeUserProperty($this->maxCard->getDBkey());
        $this->inverseOfProp = SMWPropertyValue::makeUserProperty($this->inverseOf->getDBkey());
		$this->ontologyURIProp = SMWPropertyValue::makeUserProperty($this->ontologyURI->getDBkey());;
        
	}
 	
 	/**
 	 * Initializes all tables and predefined pages.
 	 */
 	public abstract function setup($verbose);
 	
 	/**
 	 * Returns pages of the given namespace.
 	 * 
 	 * @param $namespaces Array of ns constants, positive ns constants get or'ed, negative get and'ed and excluded.
 	 * @param $requestoptions SMWRequestOptions object.
 	 * @param $addRedirectTargets If false, redirect are completely ignored. Otherwise their targets are added. 
 	 * 
 	 * @return array of Title
 	 */
 	public abstract function getPages($namespaces = NULL, $requestoptions = NULL, $addRedirectTargets = false);
	
	/**
	 * Returns root categories (categories which have no super-category).
	 * Also returns non-existing root categories, ie. root categories which
	 * do only exist implicitly.
	 * 
	 * @return array of (Title t, boolean isLeaf)
	 */
	public abstract function getRootCategories($requestoptions = NULL);
	
	
	/**
	 *  Returns root properties (properties which have no super-property).
	 * 
	 * @return array of (Title t, boolean isLeaf)
	 */
	public abstract function getRootProperties($requestoptions = NULL);
	
	/**
	 * Returns direct subcategories of $categoryTitle.
	 * 
	 * @returnarray of (Title t, boolean isLeaf)
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
	 * Returns all articles of $categoryTitle lying in NS_MAIN including articles of all subcategories of $categoryTitle.
	 * 
	 * In the case of a cycle in the category inheritance graph, this method has a treshhold
	 * to stop execution before a stack overflow occurs.
	 * 
	 * @return if $withCategories == true array of tuples (Title instance, Title category), otherwise array of Title
	 */
	public abstract function getInstances(Title $categoryTitle, $requestoptions = NULL, $withCategories = true); 
	
	/**
     * Returns all instances of $categoryTitle including instances of all subcategories of $categoryTitle.
     * Articles of any namespace except the category namespace can be returned.
     * 
     * In the case of a cycle in the category inheritance graph, this method has a treshhold
     * to stop execution before a stack overflow occurs.
     * 
     * @return if $withCategories == true array of tuples (Title instance, Title category), otherwise array of Title
     */
    public abstract function getAllInstances(Title $categoryTitle, $requestoptions = NULL, $withCategories = true); 
	
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
	//public abstract function getDirectPropertiesByCategory(Title $categoryTitle, $requestoptions = NULL);
	
	/**
	 * Returns all properties with the given domain category.
	 * 
	 * @param category Title
	 * @return array of Title
	 */
	public abstract function getPropertiesWithDomain(Title $category);
	
	/**
	 * Return all properties with the given range category
	 * 
	 * @param category Title
	 * @return array of Title
	 */
	public abstract function getPropertiesWithRange(Title $category);
	
	/**
 	  * Returns all domain categories for a given property.
 	  */
	public abstract function getDomainCategories($propertyTitle, $reqfilter = NULL);
	/**
	 * Returns all direct subproperties of $property.
	 * 
	 * @return array of (Title t, boolean isLeaf)
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
 	 * Returns number of pages of the given namespace.
 	 *
 	 * @param int $namespace
 	 */
 	public abstract function getNumber($namespace);
 	
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
 	 * Replaces redirect annotations, i.e. pages with annotations made with redirect 
 	 * property pages. Does also replace such annotations on template pages with the usual 
 	 * constraints. Modifies database!
 	 * 
 	 * @param $verbose If true, method prints some output.
 	 */
 	public abstract function replaceRedirectAnnotations($verbose = false);
 }

