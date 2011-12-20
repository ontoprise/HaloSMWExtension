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
	 * Must be called from derived class to initialize the member variables.
	 */
	protected function SMWSemanticStore() {
	       // nothing to do

	}

	/**
	 * Initializes all tables and predefined pages.
	 */
	public abstract function setup($verbose);

	/**
	 * Returns pages of the given namespace.
	 *
	 * @param int[] $namespaces Array of ns constants, positive ns constants get or'ed, negative get and'ed and excluded.
	 * @param SMWRequestOptions $requestoptions  object.
	 * @param boolean $addRedirectTargets If false, redirect are completely ignored. Otherwise their targets are added.
	 * @param string $bundleID Returns only pages which are part of the given bundle.
	 *
	 * @return array of Title
	 */
	public abstract function getPages($namespaces = NULL, $requestoptions = NULL, $addRedirectTargets = false, $bundleID = '');

	/**
	 * Returns root categories (categories which have no super-category).
	 * Also returns non-existing root categories, ie. root categories which
	 * do only exist implicitly.
	 *
	 * @param SMWRequestOptions $requestoptions  object.
	 * @param string $bundleID Returns only pages which are part of the given bundle.
	 *
	 * @return array of (Title t, boolean isLeaf)
	 */
	public abstract function getRootCategories($requestoptions = NULL, $bundleID = '');


	/**
	 * Returns root properties (properties which have no super-property).
	 *
	 * @param SMWRequestOptions $requestoptions  object.
	 * @param string $bundleID Returns only pages which are part of the given bundle.
	 *
	 * @return array of (Title t, boolean isLeaf)
	 */
	public abstract function getRootProperties($requestoptions = NULL, $bundleID = '');

	/**
	 * Returns direct subcategories of $categoryTitle.
	 *
	 * @param Title $categoryTitle
	 * @param SMWRequestOptions $requestoptions  object.
	 * @param string $bundleID Returns only pages which are part of the given bundle.
	 *
	 * @return array of (Title t, boolean isLeaf)
	 */
	public abstract function getDirectSubCategories(Title $categoryTitle, $requestoptions = NULL, $bundleID = '');

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
	 * @param Title $instanceTitle
	 * @param SMWRequestOptions $requestoptions  object.
	 * @param string $bundleID Returns only pages which are part of the given bundle.
	 *
	 * @return array of Title
	 */
	public abstract function getCategoriesForInstance(Title $instanceTitle, $requestoptions = NULL, $bundleID = '');

	/**
	 * Checks if $article is an article which is of category $category
	 * 
	 * @param Title $article
	 * @param Title $category
	 * 
	 * @return boolean
	 */
	public abstract function isInCategory(Title $article, Title $category);
	
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
	 * @param SMWRequestOption $requestOptions
	 * @return array of Title
	 */
	public abstract function getDirectInstances(Title $categoryTitle, $requestoptions = NULL);


	/**
	 * Returns all properties with schema of $categoryTitle (including inherited).
	 * 
	 * @param Title $categoryTitle Category whose properties should be returned. 
	 * @param boolean $onlyDirect Show only direct properties (no inherited from super categories)
	 * @param int $dIndex 0 = get properties with the given category as domain
	 *                    1 = get properties with the given category as range
	 * @param SMWRequestOption $requestOptions
	 * @param string $bundleID Retrieve only properties of the given bundle.
	 * @return tuples (title, minCard, maxCard, type, isSym, isTrans, range)
	 */
	public abstract function getPropertiesWithSchemaByCategory(Title $categoryTitle, $onlyDirect = false, $dIndex = 0, $requestoptions = NULL,$bundleID= '');

	/**
	 * Returns all properties of matching $requestoptions
	 * 
	 * @param SMWRequestOption $requestOptions
	 * @return tuples: (title, minCard, maxCard, type, isSym, isTrans, range)
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
	 * 
	 * @return array of Title
	 */
	public abstract function getPropertiesWithRange(Title $category);

	/**
	 * Returns all domain categories for a given property.
	 *
	 * @param Title $propertyTitle
	 * @param SMWRequestOptions $reqfilter.
	 * @param string $bundleID Returns only pages which are part of the given bundle.
	 *
	 * @return array of Title
	 * 
	 */
	public abstract function getDomainCategories($propertyTitle, $reqfilter = NULL, $bundleID = '');
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

