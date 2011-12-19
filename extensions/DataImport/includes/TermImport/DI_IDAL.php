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
 * @ingroup DITIDataAccessLayer
 * 
 * @author Thomas Schweitzer, Ingo Steinbauer
 */

/**
 * This group contains all parts of the Term Import component that deal with the Data Access Layer (DAL)
 * @defgroup DITIDataAccessLayer
 * @ingroup DITermImport
 */

/**
 * Interface of the Data Access Layer (DAL) that is part of the term import feature.
 * The DAL access a data source and creates terms, which then can be imported
 * by the Term Import Bot 
 * 
 * @author Thomas Schweitzer
 */
interface IDAL {
	
	/**
	 * Returns a specification of the data source.
	 * 
	 * @return string: 
	 *  	Returns an XML structure with an element named <DataSource>.
	 * 		This element contains a list of elements that specify the data source.
	 * 		The names of the elements will be displayed in the user interface 
	 * 		followed by an input field if the tag has the attribute "display".
	 *      An additional attribute can specify the type of the source. For
	 * 		instance, the GUI offers a "Browse" button if the type is "file".
	 *      The element can already contain data. In this case it is used as 
	 * 		default value for the input field. The user can/has to specify the 
	 *      values. These are inserted into the XML structure which is needed 
	 *      for subsequent actions.
	 * 		Examples:
	 *	    The data source is a file. The user has to enter its name.
	 *		<?xml version="1.0"?>
	 *		<DataSource xmlns=http://www.ontoprise.de/smwplus#">
	 *	    	<filename display="Filename:" type="file"></filename>
	 *		</DataSource>
	 * 
	 */
	public function getSourceSpecification();
     
	/**
	 * Returns a list of import sets and their description.
	 * 
	 * @param string $dataSourceSpec: 
	 * 		The XML structure from getSourceSpecification(), filled with the data
	 * 		the user entered. 
	 * @return mixed
	 * 		array of Import Set Names
	 * 		error MSG otherwise
     */
	public function getImportSets($dataSourceSpec);
     
	/**
	 * Returns a list of properties and their description.
	 *          
	 * @param string $dataSourceSpec: 
	 * 		The XML structure from getSourceSpecification(), filled with the data
	 * 		the user entered.
     * @param string $importSet: 
     * 		One of the import sets that can be retrieved with getImportSet() or 
     * 		empty. 
     * @return mixed 
     * 		array of the names of the available attributes
     * 		error MSG otherwise
	 */
	public function getProperties($dataSourceSpec, $importSet);
	
	
	/**
	 * Executes a callback function. Callback functions are executed by the term import bot
	 * when it finds a term with the attribute "callback". This feature allows a DAL to 
	 * actively participate in the term import process.
	 * 
	 * This method returns an XML structure that describes the result of
	 * the method call. It contains a boolean value the describes the 
	 * callback success together with zero or more log messages:
	 * 
	 * 	<CallBackResult xmlns="http://www.ontoprise.de/smwplus#">
	 *		<success> true/false </success>
	 *		<logMessage>log messages</logMessage>
	 *	</CallBackResult>
	 * 
	 *  @parameter string the method signature i.e. "createFile('data.csv')"
	 * @param string mappingPolicy
	 * @parameter boolean overwriteExistingArticles
	 * @return string
	 *
	 */
	public function executeCallBack($callback, $templateName, $extraCategories, $delimiter, $overwriteExistingArticles, $termImportName);
	
	/**
	 * Returns a list of the names of all terms that match the input policy. 
	 *
	 * @param string $dataSourceSpec
	 * 		The XML structure from getSourceSpecification(), filled with the data 
	 * 		the user entered.
	 * @param string $importSet
	 * 		One of the <importSet>-elements from  
	 * 		getImportSets() or empty.
	 * @param string $inputPolicy
	 * 		The XML structure of the input policy as defined in importTerms().
	 * 
	 * @return mixed
	 * 		DITermCollection instance on success
	 * 		error message otherwise
	 */
	public function getTermList($dataSourceSpec, $importSet, $inputPolicy);
	
	/**
	 * Returns a collection of all terms that match the input policy
	 * 
	 * @param string $dataSourceSpec
	 * 		The XML structure from getSourceSpecification, filled with the data 
	 * 		the user entered.
     * @param string $importSet
     * 		One of the <importSet>-elements from  
	 * 		getImportSets() or empty.
     * @param string $inputPolicy
     * 		The XML structure of the input policy. It contains the specification
     * 		of the terms to import and their properties.
     * @param boolean $conflictPolicy
     * 		Overwrite or ignore existing articles.
     *
     * @return mixed
	 * 		DITermCollection instance on success
	 * 		error message otherwise
	 */
	public function getTerms($dataSourceSpec, $importSet, $inputPolicy, $conflictPolicy);
	
}