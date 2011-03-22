<?php

class SMWQMQueryMetadata {
	
	public $queryString;
	
	public $limit;
	public $offset;

	public $propertyPrintRequests;
	public $hasCategoryPrintRequest;
	
	public $isSparqlQuery;

	public $categoryConditions;
	public $propertyConditions;

	public $usedInArticle;
	
	public $queryPrinter;
	
	public $queryName;
	
	public $isDisjunctive;
	
	public $usesASKSyntax;
	
	public function __construct($isDisjunctive = false, $propertyPrintRequests = null, $isSparqlQuery = null, $categoryConditions = null, 
			$propertyConditions = null, $usedInArticle = null, $queryPrinter = null, $queryName = null, $usesASKSyntax = null){
		
		$this->isDisjunctive = $isDisjunctive;
		$this->propertyPrintRequests = $propertyPrintRequests;
		$this->isSparqlQuery = $isSparqlQuery;
		$this->categoryConditions = $categoryConditions;
		$this->propertyConditions = $propertyConditions;
		$this->usedInArticle = $usedInArticle;
		$this->queryPrinter = $queryPrinter;
		$this->queryName = $queryName;
		$this->usesASKSyntax = $usesASKSyntax;
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
	
	public function fillFromPropertyValues($pVs){
		foreach($pVs as $pV){
			if($pV[0] == QRC_HQS_LABEL) $this->queryString = $pV[1][0];
			if($pV[0] == QRC_HQL_LABEL) $this->limit = $pV[1][0];
			if($pV[0] == QRC_HQO_LABEL) $this->offset = $pV[1][0];
			if($pV[0] == QRC_HEPP_LABEL) $this->propertyPrintRequests[$pV[1][0]] = true;
			if($pV[0] == QRC_HECP_LABEL) $this->hasCategoryPrintRequest = $pV[1][0];
			if($pV[0] == QRC_ISQ_LABEL) $this->isSparqlQuery = $pV[1][0];
			if($pV[0] == QRC_UAS_LABEL) $this->usesASKSyntax = $pV[1][0];
			if($pV[0] == QRC_DOP_LABEL) $this->propertyConditions[$pV[1][0]] = true;
			if($pV[0] == QRC_DOC_LABEL) $this->categoryConditions[$pV[1][0]] = true;
			if($pV[0] == QM_UIA_LABEL) $this->usedInArticle = $pV[1][0];
			if($pV[0] == QM_UQP_LABEL) $this->queryPrinter = $pV[1][0];
			if($pV[0] == QM_HQN_LABEL) $this->queryName = $pV[1][0];
		}
	}
	
}