<?php
/**
 * @file
 * @ingroup DIWebServices
 * 
 * @author Ingo Steinbauer
 */

global $smwgDIIP;
require_once("$smwgDIIP/libs/arc/ARC2.php");

//define some special predicates, which can be used in result part definitions
define("ALL_SUBJECTS", "__triple_subjects");
define("ALL_PREDICATES", "__triple_predicates");
define("ALL_OBJECTS", "__triple_objects");
define("RDF_POSTPROCESS_REQUIRED", "postprocess required");


/**
 * This class provides a wrapper for the ARC2 library
 * which is used by SMWWebService to extract result parts
 * from serialized RDF
 * 
 * @author Ingo Steinbauer
 *
 */
class SMWRDFProcessor {
	
	private static $instance;
	
	/*
	 * singleton
	 */
		public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance; 
	}
	
	private $index;
	private $processedIndex = array();
	private $subject;
	private $namespacePrefixes = array();
	private $allPredicatesRequested = false; //user has used the ALL_PREDICATES predicate
	private $allObjectsRequested = false; //user has used the ALL_OBJECTS predicate
	
	
	
	/**
	 * Parse the WS result with the ARC2 library
	 * 
	 * @param unknown_type $uri
	 * @param unknown_type $subject
	 * @param unknown_type $content
	 * @return unknown_type
	 */
	public function parse($uri, $subject, $content = null){
		$parser = ARC2::getRDFParser();
		$parser->parse($uri, $content);
		$this->index = $parser->getSimpleIndex();
		$this->subject = $subject;
	}
	
	/**
	 * Add namespace prefixes which will be used when
	 * processing predicates
	 * 
	 * @param $nsp
	 * @return unknown_type
	 */
	public function setNamespacePrefixes($nsp){
		$this->namespacePrefixes = $nsp;
	}
	
	/**
	 * Replace namespace prefixes with URIs
	 * 
	 * @param $uri
	 * @return unknown_type
	 */ 
	private function resolveNamespacePrefix($uri){
		foreach($this->namespacePrefixes as $nsp){
			if(strpos($uri, "".$nsp['prefix'].":") === 0){
				return str_replace("".$nsp['prefix'].":",$nsp['uri'], $uri);
			}
		}
		return $uri;
	}
		
	/**
	 * This method is called by SMWWebService
	 * when processing result parts. It updates the internal
	 * processed index, which can be accessed by
	 * SMWWebService after all predicates have 
	 * been preprocessed.
	 * 
	 * @param unknown_type $predicate
	 * @return unknown_type
	 */
	public function preprocessPredicate($predicate){
		$predicate = $this->resolveNamespacePrefix($predicate);
		
		if(strlen($this->subject) > 0){ //user has chosen to retrieve triples for a certain subject
			$subject = $this->resolveNamespacePrefix($this->subject);
			
			if($predicate == ALL_SUBJECTS){
				if(array_key_exists($subject, $this->index)){
					$result = array($this->subject);
					if(!array_key_exists($subject, $this->processedIndex)){
						$this->processedIndex[$subject] = array();
					}
				}
			} else if($predicate == ALL_PREDICATES){
				$this->allPredicatesRequested = true;;
				if(!array_key_exists($subject, $this->processedIndex)){
					$this->processedIndex[$subject] = array();
				}
				foreach(array_keys($this->index[$subject]) as $predicate){
					if(!array_key_exists($predicate, $this->processedIndex[$subject])){
						$this->processedIndex[$subject][$predicate] = array();
					}	
				}
			} else if($predicate == ALL_OBJECTS){
				$this->allObjectsRequested = true;
				foreach($this->index[$subject] as $predicateId => $predicate){
					$this->processedIndex[$subject][$predicateId] = $predicate;
				}
			} else {		
				$result = $this->index[$subject][$predicate];
				$this->processedIndex[$subject][$predicate] = $this->index[$subject][$predicate];
			}
		} else { //user is not interested in a certain subject
			if($predicate == ALL_SUBJECTS){
				foreach(array_keys($this->index) as $subjectId){ //add all subject ids to the processed index
					if(!array_key_exists($subjectId, $this->processedIndex)){
						$this->processedIndex[$subjectId] = array();
					}
				}		
			} else { // predicate != ALL_SUBJECTS
				foreach($this->index as $subjectId => $subject){
					if($predicate == ALL_PREDICATES){
						$this->allPredicatesRequested = true;
						foreach(array_keys($subject) as $predicateId){
							if(!array_key_exists($predicateId, $this->processedIndex[$subjectId])){
								$this->processedIndex[$subjectId][$predicateId] = array();
							}
						}
					} else if($predicate == ALL_OBJECTS){
						$this->allObjectsRequested = true;
						foreach($subject as $predicateId => $objects){
							$this->processedIndex[$subjectId][$predicateId] = $objects;
						}
					} else if(array_key_exists($predicate, $subject)){
						$result = array_merge($result, $subject[$predicate]);
						$this->processedIndex[$subjectId][$predicate] = $subject[$predicate]; 
					} 
				}
			}
		}
		
		return RDF_POSTPROCESS_REQUIRED;
	}
	
	/**
	 * This method is called by SMWWebService
	 * after all predicates have been been Preprocessed
	 * in order to get the final result part values.
	 * 
	 * @param unknown_type $predicate
	 * @return unknown_type
	 */
	public function getFinalResult($predicate){
		$requestPredicate = $this->resolveNamespacePrefix($predicate);
		
		$result = array();
		foreach($this->processedIndex as $subjectId => $subject){
			if(count($subject) == 0){ //a subject with no associated triples
				if($requestPredicate == ALL_SUBJECTS){
					$result[] = $subjectId;
				} else if($requestPredicate == ALL_PREDICATES){
					$result[] = ""; //this should never happen
				} else {
					$result [] = "";
				}
			} else { //this subject has associated triples
				if(!$this->allPredficatesRequested && !$this->allObjectsRequested){ //all predicates was not requested => do not add spacers for them
					//add spacers if some predicates have several values
					$maxObjects = 1;
					$objects = array();							
					foreach($subject as $predicateId => $predicate){
						$maxObjects = max($maxObjects, count($predicate));
						if($predicateId == $requestPredicate){
							$objects = $predicate;
						}
					}
					
					if($requestPredicate == ALL_SUBJECTS){
						for($i=0; $i < $maxObjects; $i++){
							$result[] = $subjectId;
						}
					}	else {
						for($i=0; $i < $maxObjects; $i++){
							if(array_key_exists($i, $objects)){
								$result[] = $objects[$i];
							} else {
								$result[] = "";
							}
						}
					}
				} else { //all predicates were requested	
					foreach($subject as $predicateId => $predicate){
						if(count($predicate) == 0){ //no values for that predicate exist
							if($requestPredicate == ALL_SUBJECTS){
								$result[] = $subjectId;
							} else if($requestPredicate == ALL_PREDICATES){
								$result[] = $predicateId;
							} else {
								$result [] = "";
							}
						} else { //values for the current predicate are available
							foreach($predicate as $object){
								if($requestPredicate == ALL_SUBJECTS){
									$result[] = $subjectId;
								} else if($requestPredicate == ALL_PREDICATES){
									$result[] = $predicateId;
								} else if($requestPredicate == ALL_OBJECTS){
									$result[] = $object;
								} else {
									if($predicateId == $requestPredicate){
										$result [] = $object;
									} else {
										$result[] = "";
									}
								}
							}
						}
					}
				}
			}
		}
		
		return $result;
	}
	
}