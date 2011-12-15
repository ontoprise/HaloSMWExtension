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


class SMWQMQueryMetadata {
	
	public $queryString;
	
	public $limit;
	public $offset;

	public $propertyPrintRequests;
	public $hasCategoryPrintRequest;
	
	public $isSparqlQuery;

	public $categoryConditions;
	public $propertyConditions;
	public $instanceOccurences;

	public $usedInArticle;
	
	public $queryPrinter;
	
	public $queryName;
	
	public $isDisjunctive;
	
	public $usesASKSyntax;
	
	public function __construct($isDisjunctive = false, $propertyPrintRequests = null, $isSparqlQuery = null, $categoryConditions = null, 
			$propertyConditions = null, $usedInArticle = null, $queryPrinter = null, $queryName = null, $usesASKSyntax = null, $instanceOccurences = null){
		
		$this->isDisjunctive = $isDisjunctive;
		$this->propertyPrintRequests = $propertyPrintRequests;
		$this->isSparqlQuery = $isSparqlQuery;
		$this->categoryConditions = $categoryConditions;
		$this->propertyConditions = $propertyConditions;
		$this->usedInArticle = $usedInArticle;
		$this->queryPrinter = $queryPrinter;
		$this->queryName = $queryName;
		$this->usesASKSyntax = $usesASKSyntax;
		$this->instanceOccurences = $instanceOccurences;;
	}
	
	public function getMetadaSearchQueryString(){
		$queryString = array();

		if(!is_null($this->propertyPrintRequests)){
			foreach($this->propertyPrintRequests as $prop => $dontCare){
				$queryString[] = ' [['.QRC_UQC_LABEL.'.'.QRC_HEPP_LABEL.'::'.$prop.']]';
			}
		}
		
		if(!is_null($this->categoryConditions)){
			foreach($this->categoryConditions as $cc => $dontCare){
				$queryString[] = ' [['.QRC_UQC_LABEL.'.'.QRC_DOC_LABEL.'::'.$cc.']]';
			}
		}
		
		if(!is_null($this->instanceOccurences)){
			foreach($this->instanceOccurences as $io => $dontCare){
				$queryString[] = ' [['.QRC_UQC_LABEL.'.'.QRC_DOI_LABEL.'::'.$io.']]';
			}
		}
		
		if(!is_null($this->propertyConditions)){
			foreach($this->propertyConditions as $pc => $dontCare){
				$queryString[] = ' [['.QRC_UQC_LABEL.'.'.QRC_DOP_LABEL.'::'.$pc.']]';
			}
		}
		
		if(!is_null($this->usedInArticle)){
			$queryString[] = ' [['.QRC_UQC_LABEL.'.'.QM_UIA_LABEL.'::'.$this->usedInArticle.']]';
		}
		
		if(!is_null($this->queryPrinter)){
			$queryString[] = ' [['.QRC_UQC_LABEL.'.'.QM_UQP_LABEL.'::'.$this->queryPrinter.']]';
		}
		
		if(!is_null($this->queryName)){
			$queryString[] = ' [['.QRC_UQC_LABEL.'.'.QM_HQN_LABEL.'::'.$this->queryName.']]';	
		}
		
		if($this->isDisjunctive){
			$queryString = implode(' || ', $queryString);
		} else {
			$queryString = implode(' ', $queryString);
		}
		
		if(!is_null($this->isSparqlQuery)){
			$queryString = '('.$queryString.') [['.QRC_UQC_LABEL.'.'.QRC_ISQ_LABEL.'::'.$this->isSparqlQuery.']]';
		}
		
		if(!is_null($this->usesASKSyntax)){
			$queryString = '('.$queryString.') [['.QRC_UQC_LABEL.'.'.QRC_UAS_LABEL.'::'.$this->usesASKSyntax.']]';
		}
		
		if(strlen($queryString) == 0){
			$queryString = '[['.QRC_UQC_LABEL.'::+]]';
		}
		
		return $queryString;
	}
	
	public function matchesQueryMetadataPattern($queryMetadataPattern){
		
		$this->isDisjunctive = $queryMetadataPattern->isDisjunctive; 
		
		if(!is_null($this->isSparqlQuery) && !is_null($queryMetadataPattern->isSparqlQuery)){
			if($this->isSparqlQuery != $queryMetadataPattern->isSparqlQuery){
				return false;
			}
		}

		if(!is_null($this->usesASKSyntax) && !is_null($queryMetadataPattern->usesASKSyntax)){
			if($this->usesASKSyntax != $queryMetadataPattern->usesASKSyntax){
				return false;
			}
		}
		
		if(!is_null($this->propertyPrintRequests) && !is_null($queryMetadataPattern->propertyPrintRequests)){
			foreach($queryMetadataPattern->propertyPrintRequests as $prop => $dontCare){
				if(!array_key_exists($prop, $this->propertyPrintRequests) && !$this->isDisjunctive){
					return false;
				} else if(array_key_exists($prop, $this->propertyPrintRequests) && $this->isDisjunctive){
					return true;
				}
			}
		}
		
		if(!is_null($this->categoryConditions) && !is_null($queryMetadataPattern->categoryConditions)){
			foreach($queryMetadataPattern->categoryConditions as $cc => $dontCare){
				if(!array_key_exists($cc, $this->categoryConditions) && !$this->isDisjunctive){
					return false;
				} else if(array_key_exists($cc, $this->categoryConditions) && $this->isDisjunctive){
					return true;
				}
			}
		}
		
		if(!is_null($this->instanceOccurences) && !is_null($queryMetadataPattern->instanceOccurences)){
			foreach($queryMetadataPattern->instanceOccurences as $io => $dontCare){
				if(!array_key_exists($io, $this->instanceOccurences) && !$this->isDisjunctive){
					return false;
				} else if(array_key_exists($io, $this->instanceOccurences) && $this->isDisjunctive){
					return true;
				}
			}
		}
		
		if(!is_null($this->propertyConditions) && !is_null($queryMetadataPattern->propertyConditions)){
			foreach($queryMetadataPattern->propertyConditions as $pc => $dontCare){
				if(!array_key_exists($pc, $this->propertyConditions)  && !$this->isDisjunctive){
					return false;
				} else if(array_key_exists($pc, $this->propertyConditions)  && $this->isDisjunctive){
					return true;
				}
			}
		}
		
		if(!is_null($this->usedInArticle) && !is_null($queryMetadataPattern->usedInArticle)){
			if($this->usedInArticle != $queryMetadataPattern->usedInArticle && !$this->isDisjunctive){
				return false;
			} else if($this->usedInArticle == $queryMetadataPattern->usedInArticle && $this->isDisjunctive){
				return true;
			}
		}
		
		if(!is_null($this->queryPrinter) && !is_null($queryMetadataPattern->queryPrinter)){
			if($this->queryPrinter != $queryMetadataPattern->queryPrinter && !$this->isDisjunctive){
				return false;
			} else if($this->queryPrinter == $queryMetadataPattern->queryPrinter && $this->isDisjunctive){
				return true;
			}
		}
		
		if(!is_null($this->queryName) && !is_null($queryMetadataPattern->queryName)){
			if($this->queryName != $queryMetadataPattern->queryName && !$this->isDisjunctive){ 
				return false;
			} else if($this->queryName == $queryMetadataPattern->queryName && $this->isDisjunctive){ 
				return true;
			}
		}
		
		if($this->isDisjunctive){
			return false;
		} else {
			return true;
		}
	}
	
	public function fillFromPropertyValues($container){
		
		$properties = array(
			'queryString' => QRC_HQS_LABEL,
			'limit' => QRC_HQL_LABEL,
			'offset' => QRC_HQO_LABEL,
			'hasCategoryPrintRequest' => QRC_HECP_LABEL,
			'isSparqlQuery' => QRC_ISQ_LABEL,
			'usesASKSyntax' => QRC_UAS_LABEL,
			'usedInArticle' => QM_UIA_LABEL,
			'queryPrinter' => QM_UQP_LABEL,
			'queryName' => QM_HQN_LABEL
		);

		foreach($properties as $attr => $propLabel){
			$property = SMWDIProperty::newFromUserLabel($propLabel);
			$vals = $container->getSemanticData()->getPropertyValues($property);
			if(count($vals) > 0){
				$this->$attr = $vals[0]->getSortKey();
			}
		}
		
		$properties = array(
			'propertyPrintRequests' => QRC_HEPP_LABEL,
			'propertyConditions' => QRC_DOP_LABEL,
			'categoryConditions' => QRC_DOC_LABEL,
			'instanceOccurences' =>QRC_DOI_LABEL 
		);
		
		foreach($properties as $attr => $propLabel){
			$property = SMWDIProperty::newFromUserLabel($propLabel);
			$vals = $container->getSemanticData()->getPropertyValues($property);
			if(count($vals) > 0){
				$this->$attr = array();
				foreach($vals as $v){
					array_push($this->$attr, $v->getSortKey());
				}
				$this->$attr = array_flip($this->$attr);
			}
		}
	}
}
