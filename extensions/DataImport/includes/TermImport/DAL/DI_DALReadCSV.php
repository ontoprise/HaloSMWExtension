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
 * Implementation of the Data Access Layer (DAL) that is part of the term import feature.
 * 
 * @author Thomas Schweitzer 
 * @author Ingo Steinbauer
 */

class DALReadCSV implements IDAL {
	
	private $filename;
	private $csvContent;

	
	/**
	 * Returns a specification of the data source.
	 * See further details in SMW_IDAL.php
	 * 
	 * @return string:
	 * 		The returned XML structure specifies the data source i.e. a file. 
	 * 		The name of the file has to be specified by the user.
	 *		<?xml version="1.0"?>
	 *		<DataSource xmlns=http://www.ontoprise.de/smwplus#">
	 *	    	<filename display="Filename:" type="t"></filename>
	 *		</DataSource>
	 * 
	 */
	public function getSourceSpecification() {
		return 
			'<?xml version="1.0"?>'."\n".
			'<DataSource xmlns="http://www.ontoprise.de/smwplus#">'."\n".
			' 	<filename display="'.wfMsg('smw_ti_filename').'" type="text"></filename>'."\n".
			'</DataSource>'."\n";
	}
     
	/**
	 * Returns a list of import sets and their description.
	 * 
	 * @param string $dataSourceSpec: 
	 * 		The XML structure from getSourceSpecification(), filled with the data
	 * 		the user entered. 
	 * @return string:
     * 		Returns a list of import sets and their description (for the user) 
     * 		that the module can extract from the data source. An import set is 
     * 		just a name for a set of terms that module can extract e.g. different
     * 		domains of knowledge like Biological terms, Chemical terms etc. 
     * 		Each XML element <importSet> has the mandatory elements <name> and 
     * 		<desc>. Arbitrary, module dependent elements can be added. 
     * 		Example:
     * 		<?xml version="1.0"?>
	 *		<ImportSets xmlns="http://www.ontoprise.de/smwplus#">
	 *		    <importSet>
	 *		        <name>Biological terms</name>
	 *     			 <desc>Import all terms from the biology domain.</desc>
	 * 			</importSet>
	 *		    <importSet>
	 *		        <name>Biological terms</name>
	 *		        <desc>mport all terms from the chemistry domain.</desc>
	 *		    </importSet>
	 *		</ImportSets>
	 * 
	 * 		If the operation fails, an error message is returned.
	 */
	public function getImportSets($dataSourceSpec) {
		$filename = $this->getFilenameFromSpec($dataSourceSpec);
		$importSets = array();
		
		if (!$this->readContent($filename) 
		    || count($this->csvContent) == 0) {
		    return wfMsg('smw_ti_fileerror', $filename);
		}
		
		$firstLine = &$this->csvContent[0];
		$impSet = array();
		if (strtolower($firstLine[0]) == 'importset') {
			$len = count($this->csvContent);
			for ($i = 1; $i < $len; ++$i) {
				$importSets[$this->csvContent[$i][0]] = true;
			}
		}
		
		return array_keys($importSets);
	}
     
	/**
	 * Returns a list of properties and their description.
	 *          
	 * @param string $dataSourceSpec: 
	 * 		The XML structure from getSourceSpecification(), filled with the data
	 * 		the user entered.
     * @param string $importSet: 
     * 		One of the import sets that can be retrieved with getImportSet() or 
     * 		empty. The complete XML element <importSet> as specified above is 
     * 		passed as it may contain values besides <name> and <desc>.
     * @return string: 
     * 		Returns a list of properties and their description (for the user) 
     * 		that the module can extract from the data source for each term in the
     * 		specified import set.
     * 		Example:
     * 		<?xml version="1.0"?>
     *		<Properties xmlns="http://www.ontoprise.de/smwplus#">
     *		    <property>
     *		        <name>articleName</name>
     *		        <desc>An article with this name will be created for the term of the vocabulary.</desc>
     *		    </property>
     *		    <property>
     *		        <name>content</name>
     *		        <desc>The description of the term.</desc>
     *		    </property>
     *		    <property>
     *		        <name>author</name>
     *		        <desc>Name of the person who describe the term.</desc>
     *		    </property>
     *		</Properties>
	 * 
	 * 		If the operation fails, an error message is returned.
	 * 
	 */
	public function getProperties($dataSourceSpec, $importSet) {
		$filename = $this->getFilenameFromSpec($dataSourceSpec);
		$properties = array();
		
		if (!$this->readContent($filename) 
		    	|| count($this->csvContent) == 0) {
		    return wfMsg('smw_ti_fileerror', $filename);
		}
    	
		$firstLine = &$this->csvContent[0];
		foreach ($firstLine as $prop) {
			if (strtolower($prop) != 'importset') {
				$properties[trim($prop)] = true;
			}
		}
		
		return $properties;
	}
	
	/**
	 * Returns a list of the names of all terms that match the input policy. 
	 *
	 * @param string $dataSourceSpec
	 * 		The XML structure from getSourceSpecification(), filled with the data 
	 * 		the user entered.
	 * @param string $importSet
	 * 		One of the <importSet>-elements from the XML structure from 
	 * 		getImportSets() or empty.
	 * @param string $inputPolicy
	 * 		The XML structure of the input policy as defined in importTerms().
	 * 
	 * @return DITermCollection
	 * 
	  * 		If the operation fails, an error message is returned. 
	 * 
	 */
	public function getTermList($dataSourceSpec, $importSet, $inputPolicy) {
		return $this->createTerms($dataSourceSpec, $importSet, $inputPolicy, true);
	}
	
	/**
	 * Generates the XML description of all terms in the data source that match 
	 * the input policy.
	 * @param string $dataSourceSpec
	 * 		The XML structure from getSourceSpecification, filled with the data 
	 * 		the user entered.
     * @param string $importSet
     * 		One of the <importSet>-elements from the XML structure from 
     * 		getImportSets() or empty.
     * @param string $inputPolicy
     * 		The XML structure of the input policy. It contains the specification
     * 		of the terms to import and their properties.
     * @param string $conflictPolicy
     * 		The XML structure of the conflict policy. It defines if existing articles
     * 		are overwritten or not.
     *      * 
     * @return DITermCollection
     * 
      * If the operation fails, an error message is returned.
	 *
	 */
	public function getTerms($dataSourceSpec, $importSet, $inputPolicy, $conflictPolicy) {
		return $this->createTerms($dataSourceSpec, $importSet, $inputPolicy, false);
		
	}
	
	/**
	 * Extracts the filename from the data source specification.
	 *
	 * @param string $dataSourceSpec
	 * 		The XML structure that specifies the data source must contain the
	 * 		<filename> element with the name of a file.
	 * 
	 * @return string
	 * 		Name of a file or <null> if the value in not present. 
	 */
	private function getFilenameFromSpec($dataSourceSpec) {
		preg_match('/<filename.*?>(.*?)<\/filename>/i', $dataSourceSpec, $filename);
		
		return (count($filename) == 2) ? $filename[1] : null;
	}
	
	/**
	 * Reads the content of the csv file with the name $filename if it has not
	 * already been read. 
	 * The content is stored in $this->csvContent for further operations.
	 * Each line of the CSV file is an array of the separated values in the 
	 * array $this->csvContent. The first line/array contains the names of the
	 * properties of the terms.
	 *
	 * @param string $filename
	 * 		Name of the CSV file to read
	 * @return boolean
	 * 		<true>, if the file was read successfully (or is already cached)
	 * 		<false>, otherwise
	 */
	private function readContent($filename) {
		if(!is_null($this->csvContent)){
			return $this->csvContent;
		}
		
		if ($this->filename == $filename) {
			return true;
		}
		
		if (!$filename) {
			return false;
		}
				
		$file = @ fopen($filename, 'r');
		if (!$file) {
			return false;
		}
		
		$this->csvContent = array();
		while (!feof($file)) {
			$line = fgets($file);
			if ($line) {
				//escape special characters in an XML document:
				//$line = htmlspecialchars($line);
				@ $vals = &explode("\t", $line);
				$this->csvContent[] = $vals;
			}
		}
		fclose($file);
		
		$this->filename = $filename;
		
		return true;
	}
	
	/**
	 * Generates the XML description of all terms in the data source that match 
	 * the input policy.
	 * @param string $dataSourceSpec
	 * 		The XML structure from getSourceSpecification, filled with the data 
	 * 		the user entered.
     * @param string $importSet
     * 		One of the <importSet>-elements from the XML structure from 
     * 		getImportSets() or empty.
     * @param string $inputPolicy
     * 		The XML structure of the input policy. It contains the specification
     * 		of the terms to import and their properties.
     * @param boolean $createTermList
     * 		If <true>, the XML structure for <getTermList> is created otherwise
     * 		the one for <getTerms>
     * 
     * @return DITermCollection
	 *  
 	* If the operation fails, an error message is returned.
	 */
	private function createTerms($dataSourceSpec, $importSet, $inputPolicy, 
	                             $createTermList) {
	                            	
		$filename = $this->getFilenameFromSpec($dataSourceSpec);
		$policy = DIDALHelper::parseInputPolicy($inputPolicy);
		
		$terms = new DITermCollection();
		
		if (!$this->readContent($filename) 
		    	|| ($len = count($this->csvContent)) == 0) {
		    return wfMsg('smw_ti_fileerror', $filename);
		}

		$indexMap = array();
		foreach ($this->csvContent[0] as $idx => $prop) {
			$p = trim($prop);
			$p = preg_replace("/ +/", "__SPACE__", $p);
			if (strtolower($p) == 'articlename') {
				$p = 'articleName';
			} else if (strtolower($p) == 'importset') {
				$p = 'ImportSet';
			}
			$indexMap[$p] = $idx;
		}
		
		$articleIdx = $indexMap['articleName'];
		if ($articleIdx === null) {
		    return wfMsg('smw_ti_no_article_names', $filename);
		}
		
		$impSetIdx = array_key_exists('ImportSet', $indexMap)
						? $indexMap['ImportSet']
						: null;
		
		for ($i = 1; $i < $len; ++$i) {
			$impSet = ($impSetIdx === null) ? null
			                                : $this->csvContent[$i][$impSetIdx];
			if (!array_key_exists($i, $this->csvContent) 
					|| !array_key_exists($articleIdx, $this->csvContent[$i])) {                                
				continue;
			}
			
			$articleName = $this->csvContent[$i][$articleIdx];
			if (DIDALHelper::termMatchesRules($impSet, $articleName, $importSet, $policy)) {
			    
				$term = new DITerm();
				$term->setArticleName($articleName);
				
				if (!$createTermList) {
					
					// add all requested properties
					$props = &$policy['properties'];
					foreach ($props as $prop) {
						$prop = "".preg_replace("/ +/", "__SPACE__", $prop);
						$idx = $indexMap[$prop];
						if ($idx !== null) {
							$term->addProperty($prop, $this->csvContent[$i][$idx]);
						}
					}
				}
				$terms->addTerm($term);
			}
		}
		
		return $terms;
	}
	
	public function executeCallBack($signature, $mappingPolicy, $conflictPolicy, $termImportName){
		return true;
	}
}