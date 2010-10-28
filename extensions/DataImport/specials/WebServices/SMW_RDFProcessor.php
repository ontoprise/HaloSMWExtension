<?php
/**
 * @file
 * @ingroup DIWebServices
 *
 * @author Ingo Steinbauer
 */

//Outcommented because this is now done by ARCLibrary extension
//if(!class_exists('ARC2')){
//	//this is necessary, because other extensions also are shipped with that library
//	global $smwgDIIP;
//	require_once("$smwgDIIP/libs/arc/ARC2.php");
//}

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
	private $allPropertysRequested = false; //user has used the DI_ALL_PROPERTIES property
	private $allObjectsRequested = false; //user has used the DI_ALL_OBJECTS property
	private $language;



	/**
	 * Parse the WS result with the ARC2 library
	 *
	 * @param unknown_type $uri
	 * @param unknown_type $subject
	 * @param unknown_type $content
	 * @return unknown_type
	 */
	public function parse($uri, $subject, $content = null, $format, $language){
		$parserName = !$format ? "RDF" : $format;
		$parserName = "get".$parserName."Parser";
		$parser = ARC2::$parserName();
		$parser->parse($uri, $content);
		$this->index = $parser->getSimpleIndex(false);

		$this->subject = $subject;
		$this->language = $language;

		$this->processedIndex = array();
		$this->allPropertysRequested = false;
		$this->allObjectsRequested = false;
	}

	/**
	 * Add namespace prefixes which will be used when
	 * processing propertys
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

	private function getRequestedLanguage($property){
		$lang = $this->language;
		if(strpos($property, "@") > 0){
			$lang = substr($property, strpos($property, "@")+1);
			$property = substr($property, 0, strpos($property, "@"));
		}
		return array($lang, $property);
	}

	/**
	 * This method is called by SMWWebService
	 * when processing result parts. It updates the internal
	 * processed index, which can be accessed by
	 * SMWWebService after all propertys have
	 * been preprocessed.
	 *
	 * @param unknown_type $property
	 * @return unknown_type
	 */
	public function preprocessProperty($property){
		list($lang, $property) = $this->getRequestedLanguage($property);
		$property = $this->resolveNamespacePrefix($property);

		if(strlen($this->subject) > 0){ //user has chosen to retrieve triples for a certain subject
			$subject = $this->resolveNamespacePrefix($this->subject);
				
			if(array_key_exists($subject, $this->index)){
				if($property == DI_ALL_SUBJECTS){
					if(array_key_exists($subject, $this->index)){
						$result = array($this->subject);
						if(!array_key_exists($subject, $this->processedIndex)){
							$this->processedIndex[$subject] = array();
						}
					}
				} else if($property == DI_ALL_PROPERTIES){
					$this->allPropertysRequested = true;;
					if(!array_key_exists($subject, $this->processedIndex)){
						$this->processedIndex[$subject] = array();
					}
					foreach(array_keys($this->index[$subject]) as $property){
						if(!array_key_exists($property, $this->processedIndex[$subject])){
							$this->processedIndex[$subject][$property] = array();
						}
					}
				} else if($property == DI_ALL_OBJECTS){
					$this->allObjectsRequested = true;
					foreach($this->index[$subject] as $propertyId => $property){
						$processedObjects = array();
						foreach($property as $object){
							if(array_key_exists('lang', $object)){
								if($object['lang'] == $lang || strlen($lang) == 0){
									$processedObjects[] = $object['value'];
								}
							} else {
								$processedObjects[] = $object['value'];
							}
						}
						$this->processedIndex[$subject][$propertyId] = $processedObjects;
					}
				} else if(array_key_exists($property, $this->index[$subject])){
					$processedObjects = array();
						
					foreach($this->index[$subject][$property] as $object){
						if(array_key_exists('lang', $object)){
							if($object['lang'] == $lang || strlen($lang) == 0){
								$processedObjects[] = $object['value'];
							}
						} else {
							$processedObjects[] = $object['value'];
						}
					}
					$this->processedIndex[$subject][$property] = $processedObjects;
				}
			}
		} else { //user is not interested in a certain subject
			if($property == DI_ALL_SUBJECTS){
				foreach(array_keys($this->index) as $subjectId){ //add all subject ids to the processed index
					if(!array_key_exists($subjectId, $this->processedIndex)){
						$this->processedIndex[$subjectId] = array();
					}
				}
			} else { // property != DI_ALL_SUBJECTS
				foreach($this->index as $subjectId => $subject){
					if($property == DI_ALL_PROPERTIES){
						$this->allPropertysRequested = true;
						foreach(array_keys($subject) as $propertyId){
							if(!array_key_exists($propertyId, $this->processedIndex[$subjectId])){
								$this->processedIndex[$subjectId][$propertyId] = array();
							}
						}
					} else if($property == DI_ALL_OBJECTS){
						$this->allObjectsRequested = true;
						foreach($subject as $propertyId => $objects){
							$processedObjects = array();
							foreach($objects as $object){
								if(array_key_exists('lang', $object)){
									if($object['lang'] == $lang || strlen($lang) == 0){
										$processedObjects[] = $object['value'];
									}
								} else {
									$processedObjects[] = $object['value'];
								}
							}
								
							$this->processedIndex[$subjectId][$propertyId] = $processedObjects;
						}
					} else if(array_key_exists($property, $subject)){

						$processedObjects = array();
						foreach($subject[$property] as $object){
							if(array_key_exists('lang', $object)){
								if($object['lang'] == $lang || strlen($lang) == 0){
									$processedObjects[] = $object['value'];
								}
							} else {
								$processedObjects[] = $object['value'];
							}
						}
						$this->processedIndex[$subjectId][$property] = $processedObjects;
					}
				}
			}
		}

		return DI_RDF_POSTPROCESS_REQUIRED;
	}

	/**
	 * This method is called by SMWWebService
	 * after all propertys have been been Preprocessed
	 * in order to get the final result part values.
	 *
	 * @param unknown_type $property
	 * @return unknown_type
	 */
	public function getFinalResult($property){
		list($lang, $property) = $this->getRequestedLanguage($property);
		$requestProperty = $this->resolveNamespacePrefix($property);

		$result = array();
		foreach($this->processedIndex as $subjectId => $subject){
			if(count($subject) == 0){ //a subject with no associated triples
				if($requestProperty == DI_ALL_SUBJECTS){
					$result[] = $subjectId;
				} else if($requestProperty == DI_ALL_PROPERTIES){
					$result[] = ""; //this should never happen
				} else {
					$result [] = "";
				}
			} else { //this subject has associated triples
				if(!$this->allPropertysRequested && !$this->allObjectsRequested){ //all propertys was not requested => do not add spacers for them
					//add spacers if some propertys have several values
					$maxObjects = 1;
					$objects = array();
					foreach($subject as $propertyId => $property){
						$maxObjects = max($maxObjects, count($property));
						if($propertyId == $requestProperty){
							$objects = $property;
						}
					}
						
					if($requestProperty == DI_ALL_SUBJECTS){
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
				} else { //all propertys were requested
					foreach($subject as $propertyId => $property){
						if(count($property) == 0){ //no values for that property exist
							if($requestProperty == DI_ALL_SUBJECTS){
								$result[] = $subjectId;
							} else if($requestProperty == DI_ALL_PROPERTIES){
								$result[] = $propertyId;
							} else {
								$result [] = "";
							}
						} else { //values for the current property are available
							foreach($property as $object){
								if($requestProperty == DI_ALL_SUBJECTS){
									$result[] = $subjectId;
								} else if($requestProperty == DI_ALL_PROPERTIES){
									$result[] = $propertyId;
								} else if($requestProperty == DI_ALL_OBJECTS){
									$result[] = $object;
								} else {
									if($propertyId == $requestProperty){
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