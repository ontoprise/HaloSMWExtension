<?php


define('TF_NEW_TEMPLATE_CALL', '000');

include_once('extensions/SMWHalo/DataAPI/PageCRUD_Plus/PCP.php');
include_once('extensions/SMWHalo/DataAPI/PageObjectModel/POM.php');


/*
 * Provides methods for modifying and reading instance data
 * via the Data API
 */
class TFDataAPIACCESS {

		private static $instance = null;
	
		/*
		 * Singleton
		 * 
		 * @param: Title $title : Title object of article to read or write
		 */
		public static function getInstance($title){
			
			if(self::$instance == null){
				self::$instance = new self();
			}
			
			if(self::$instance->title == null || self::$instance->title->getFullText() != $title->getFullText()){
				self::$instance->title = $title;
				 self::$instance->initialize();
			}
			
			return self::$instance;
		}

		private $title = null;
		private $pomPage = '';
		private $article = null;
		
		public $isReadProtected;
		public $isWriteProtected;
		
		/*
		 * Parse article and initialize the POMPage object
		 */
		private function initialize(){
			
			$this->article = null;
			
			//update access rights information
			if(!is_null($this->title)){
				$this->isReadProtected = !$this->title->userCan('read');
				$this->isWriteProtected = !$this->title->userCan('edit');
			}
			
			if(!is_null($this->title) && $this->title->exists()){
				
				$this->article = new Article($this->title);
				
				$text = $this->article->getContent();
			
				POMElement::$elementCounter = 0;
			
				$this->pomPage = new POMPage($this->title->getFullText(), $text);
			} else {
				$this->articleDoesNotExist = true;
				
				$this->pomPage = null;
			}
		}
		
		/*
		 * Returns the Revision Id fo this article
		 */
		public function getRevisionId(){
			if(!is_null($this->article)){
				return $this->article->getRevIdFetched();
			} else {
				return null;
			}
		}

		
		/*
		 * Check for the given annotations, if they can be edited
		 * via the Data API
		 * 
		 * Only annotations, that can be found directly in the article and
		 * not encapsulated in parser functions or templates can be edited.
		 */
		public function getWritableAnnotations($annotations){
			
			if(is_null($this->title) || !$this->title->exists()){
				return $annotations->getAnnotations(); 
			}
			
			if($this->isReadProtected){
				return $annotations->getAnnotations();
			}
			
			if($this->isWriteProtected){
				return $annotations->getAnnotations();
			}
			
			$elements = $this->pomPage->getElements()->listIterator();

			while($elements->hasNext()){
				$element = $elements->getNext()->getNodeValue();
				
				if($element instanceof POMProperty){
					$annotations->setWritable($element->name, $element->value);
				} else if($element instanceof POMCategory){
					$annotations->setWritable(TF_CATEGORY_KEYWORD, $element->value);
				} else if ($element instanceof POMExtensionParserFunction){
					if(strpos($element->nodeText, '{{#set:') === 0){
						$sets = trim(substr($element->nodeText, strlen('{{#set:')));
						$sets = substr($sets, 0, strlen($sets)-2);
						$sets = explode('|', $sets);
						foreach($sets as $set){
							$set = explode('=', $set, 2);
							if(count($set) == 2){
								$annotations->setWritable(trim($set[0]), trim($set[1]));
							}
						}
					} 
				} else if ($element instanceof POMBuiltInParserFunction){
					if(strpos($element->nodeText, '{{CreateSilentAnnotations:') === 0){
						$silents = trim(substr($element->nodeText, strlen('{{CreateSilentAnnotations:')));
						$silents = substr($silents, 0, strlen($silents)-2);
						$silents = explode('|', $silents);
						foreach($silents as $silent){
							if(strlen($silent) == 0) continue;
							$silent = explode('=', $silent, 2);
							if(count($silent) == 2){
								
								//check if value must be split with a delimiter
								if($delimiter = $this->getSilentAnnotationsDelimiter($silent[0])){
									$values = explode($delimiter, $silent[1]);
								} else {
									$values = array($silent[1]);
								}
								
								foreach($values as $val){
									$annotations->setWritable(trim($silent[0]), trim($val));
								}
							}
						}
					} 
				}
			}
			
			return $annotations->getAnnotations();
		}
		
		
		
		/*
		 * This method fills the parameter print requests with values.
		 * 
		 * only parameters of templates, which are not encapsulated in parser
		 * functions or other templates are supported.
		 */
		public function readTemplateParameters($parameters){
			
			if(is_null($this->title) || !$this->title->exists()){
				return $parameters->getParameters();
			}
			
			if($this->isReadProtected){
				return $parameters->getParameters();
			}
			
			$elements = $this->pomPage->getElements()->listIterator(); 
			
			while($elements->hasNext()){
				$element = $elements->getNext()->getNodeValue();
				
				if($element instanceof POMTemplate){
					
					$parameters->addTemplate($element->getTitle(), $element->id);
					
					$number = 1;
					foreach($element->parameters as $key => $parameter){
						if($parameter instanceof POMTemplateNamedParameter) {
							$name = $parameter->getName();
						} else {
							$name = $number;
							$number++;
						}
						$parameters->setTemplateParameterValue(
							$element->getTitle(), $name, $parameter->getValue()->text, $element->id);
					}
				}
			}
			
			$parameters = $parameters->getParameters();
			
			return $parameters;
		}
		
		
		
		/*
		 * Updates the annotations and template parameters of an instance
		 * and adds new ones
		 */
		public function updateValues($annotations, $parameters, $revisionId, $useSAT){
		
			if(is_null($this->title) || !$this->title->exists()){
				return wfMsg('tabf_response_deleted'); 
			}
			
			if($this->getRevisionId() != $revisionId){
				return wfMsg('tabf_response_modified');
			}
			
			if($this->isReadProtected){
				return wfMsg('tabf_response_readprotected');
			}
			
			if($this->isWriteProtected){
				return wfMsg('tabf_response_writeprotected');
			}
			
			$elements = $this->pomPage->getElements()->listIterator();

			$createSilentAnnotationsTemplateFound = false;
			
			while($elements->hasNext()){
				$element = $elements->getNextNodeValueByReference();
				
				if($element instanceof POMProperty){
					if(!is_null($newValue = $annotations->getNewValue($element->name, $element->value))){
						if(strlen($newValue) == 0){
							$this->pomPage->delete($element);
						} else {
							$element->value = $newValue;
							
							//replace label if a visible lable was originally defined
							if(is_string($element->representation) && strlen($element->representation) > 0 
								&& $element->representation != ' '){
								$element->representation = $newValue;
							}
						}
					}
				} else if($element instanceof POMCategory){
					if(!is_null($newValue = $annotations->getNewValue(TF_CATEGORY_KEYWORD, $element->value))){
						if(strlen($newValue) == 0){
							$this->pomPage->delete($element);
						} else {
							$element->value = $newValue;
						}
					}
				} else if ($element instanceof POMExtensionParserFunction){
					if(strpos($element->nodeText, '{{#set:') === 0){
						$sets = trim(substr($element->nodeText, strlen('{{#set:')));
						$sets = substr($sets, 0, strlen($sets)-2);
						$sets = explode('|', $sets);
						$modified = false;
						foreach($sets as $key => $set){
							$set = explode('=', $set, 2);
							if(count($set) == 2){
								if(!is_null($newValue = $annotations->getNewValue(trim($set[0]), trim($set[1])))){
									$modified = true;
									if(strlen($newValue) == 0){
										unset($sets[$key]);	
									} else {
										$sets[$key] = $set[0].'='.$newValue;
									}
								}
							}
						}
						
						if($modified){
							$element->nodeText = '{{#set:'.implode('|', $sets).'}}';
						}
					} 
				} else if ($element instanceof POMBuiltInParserFunction){
					if(strpos($element->nodeText, '{{CreateSilentAnnotations:') === 0){
						$createSilentAnnotationsTemplateFound = true;
						$silents = trim(substr($element->nodeText, strlen('{{CreateSilentAnnotations:')));
						$silents = substr($silents, 0, strlen($silents)-2);
						$silents = explode('|', $silents);
						$modified = false;
						foreach($silents as $key => $silent){
							if($key == 0) continue;
							$silent = explode('=', $silent, 2);
							if(count($silent == 2)){
								
								//check if value must be split with a delimiter
								if($delimiter = $this->getSilentAnnotationsDelimiter($silent[0])){
									$values = explode($delimiter, $silent[1]);
								} else {
									$values = array($silent[1]);
									$delimiter = "";
								}
								
								$silent[1] = '';
								$first = true;
								$valueModified = false;
								foreach($values as $value){
									if(!is_null($newValue = $annotations->getNewValue(trim($silent[0]), trim($value)))){
										$valueModified = true;
										if(strlen($newValue) > 0){
											if($first){
												$first = false;
											} else {
												$silent[1] .= $delimiter;
											}
											$silent[1] .= $newValue;
										}
									} else {
										if($first){
											$first = false;
										} else {
											$silent[1] .= $delimiter;
										}
										$silent[1] .= $value; 
									}
								}
								
								if($valueModified){
									$modified = true;
									if(strlen($silent[1]) == 0){
										unset($silents[$key]);
									} else {
										$silents[$key] = $silent[0].'='.$silent[1];
									}
								}
										
							}
						}
						
						$newAnnotations = $annotations->getNewAnnotations();
						if(count($newAnnotations) > 0){
							$modified = true;
							foreach($newAnnotations as $newAnnotation){
								$silents[] = $newAnnotation['name'].'='.$newAnnotation['value'];
							}
						}
						
						if($modified){
							$element->nodeText = '{{CreateSilentAnnotations:'.implode('|', $silents).'}}';
						}
					} 
				} else if($element instanceof POMTemplate){
					$newParameters = $parameters->getNewTemplateParameters($element->getTitle(), $element->id);
					
					foreach($newParameters as $name => $newValue){
						$element->setParameter($name, $newValue);
					}
					
					$number = 1;
					foreach($element->parameters as $key => $parameter){
						if($parameter instanceof POMTemplateNamedParameter) {
							$name = $parameter->getName();
						} else {
							$name = $number;
							$number++;
						}
						
						$newValue = $parameters->getNewTemplateParameterValue(
							$element->getTitle(), $name, $parameter->getValue()->text, $element->id);
						
						if(!is_null($newValue)){
							if(strlen($newValue) == 0){
								unset($element->parameters[$key]);
							} else {
								$element->setParameter($name, $newValue);
							}
						}
					}
				}
			}
			
			$this->pomPage->sync();
			
			$text = $this->pomPage->text;
			
			//Reininitialization required after edit
			$this->title = null;
			
			$newTemplateCalls = $parameters->getNewTemplateCalls();
			foreach($newTemplateCalls as $template => $parameters){
				if(count($parameters) > 0){
					$text .= "{{".$template;
					foreach($parameters as $parameter => $value){
						$text .= "\n|".$parameter."=".$value;
					}
					$text .= "\n}}";
				}
			}
			
			$newAnnotations = $annotations->getNewAnnotations();
			if($useSAT != 'true'){
				foreach($newAnnotations as $newAnnotation){
					if($newAnnotation['name'] == TF_CATEGORY_KEYWORD){
						$text .= '[[Category:'.$newAnnotation['value'].'| ]]';
					} else {
						$text .= '[['.$newAnnotation['name'].'::'.$newAnnotation['value'].'| ]]';
					}
				}
			} else {
				$silentAnnotations = '{{CreateSilentAnnozazions:';
				foreach($newAnnotations as $newAnnotation){
					if($newAnnotation['name'] == TF_CATEGORY_KEYWORD){
						$text .= '[[Category:'.$newAnnotation['value'].'| ]]';
					} else {
						$silentAnnotations .= '| '.$newAnnotation['name'].'='.$newAnnotation['value'];
					}
				}
				if(!$createSilentAnnotationsTemplateFound){
					$text .= $silentAnnotations.'}}';
				}
			}
			
			$this->article->doEdit($text, 'tabular forms');
			smwfGetStore()->refreshData($this->article->getID(), 1, false, false);
			
			return true;
		}
		
	/*
	 * This method creates a new instance 
	 */
	public function createInstance($annotations, $parameters, $useSAT){
		
		//todo: Language
		
		if(is_null($this->title)){
			return wfMsg('tabf_response_invalidname'); 
		}
		
		if($this->title->exists()){
			return wfMsg('tabf_response_created');
		}
		
		if(!$this->title->userCan('createpage')){
			return wfMsg('tabf_response_nocreatepermission');
		}
		
		$text = '';
		
		file_put_contents('d://annotations.rtf', print_r($annotations, trie));
		
		$annotations = $annotations->getNewAnnotations();
		if($useSAT != 'true'){
			foreach($annotations as $annotation){
				if($annotation['name'] == TF_CATEGORY_KEYWORD){
					$text .= '[[Category:'.$annotation['value'].'| ]]';
				} else if (strlen($annotation['name']) >0){
					$text .= '[['.$annotation['name'].'::'.$annotation['value'].'| ]]';
				}
			}
		} else {
			$silentAnnotations = "{{CreateSilentAnnotations:";
			foreach($annotations as $annotation){
				if($annotation['name'] == TF_CATEGORY_KEYWORD){
					$text .= '[[Category:'.$annotation['value'].'| ]]';
				} else if (strlen($annotation['name']) >0){
					$silentAnnotations .= '| '.$annotation['name'].'='.$annotation['value'];
				}
			}
			
			$text = $silentAnnotations.'}}'.$text;
		}
			
		$newTemplateCalls = $parameters->getNewTemplateCalls();
		foreach($newTemplateCalls as $template => $parameters){
			if(count($parameters) > 0){
				$text .= "{{".$template;
				foreach($parameters as $parameter => $value){
					$text .= "\n|".$parameter."=".$value;
				}
				$text .= "\n}}";
			}
		}
		
		$this->article = new Article($this->title);
		$this->article->doEdit($text, 'tabular forms');
					smwfGetStore()->refreshData($this->article->getID(), 1, false, false);
			
		return true;
	}
	
	/*
	 * Deletes an instance
	 */
	public function deleteInstance($revisionId){
		
		if(is_null($this->title) || !$this->title->exists()){
			return true; 
		}
		
		if($this->getRevisionId() != $revisionId){
			return wfMsg('tabf_response_modified');
		}

		if(!$this->title->userCan('delete')){
			return wfMsg('tabf_response_nodeletepermission');
		}
		
		$this->article->doDelete('tabular forms');
		
		return true;
	}
	
	/*
	 * Get delimiter for CreateSilentAnnotations parser function annotation
	 */
	private function getSilentAnnotationsDelimiter($propertyName){
		$title = Title::newFromText($propertyName, SMW_NS_PROPERTY);
		
		if(!$title->exists()){
			return false;
		}
		
		$store = smwfNewBaseStore();
		$semanticData = $store->getSemanticData($title);
		$properties = $semanticData->getProperties();
		
		$maxCardinality = false;
		if(array_key_exists('Has_max_cardinality', $properties)){
			$pVals = $semanticData->getPropertyValues($properties['Has_max_cardinality']);
			$idx = array_keys($pVals);
			$maxCardinality = $pVals[$idx[0]]->getShortWikiText();
		}
		
		$delimiter = false;
		if(array_key_exists('Delimiter', $properties)){
			$pVals = $semanticData->getPropertyValues($properties['Delimiter']);
			$idx = array_keys($pVals);
			$delimiter = $pVals[$idx[0]]->getShortWikiText();
		}
			
		if($maxCardinality != 1 || $delimiter){
				if(!$delimiter) 
					return ',';
				else
					return $delimiter;
		}
		
	}
		
}

class TFAnnotationDataCollection {
	
	private $annotations = array();
	
	public function addAnnotations($annotations){
		foreach($annotations as $annotation){
			$add = true;
			if(array_key_exists($annotation->name, $this->annotations)){
				foreach($this->annotations[$annotation->name] as $cAnnotation){
					if($annotation->currentValue != ''){
						if($annotation->currentValue == $cAnnotation->currentValue){
							$add = false;
						}
					}
				}
			}
			
			if($add){
				$this->annotations[$annotation->name][] = $annotation;
			}
		}
	}
	
	public function addAnnotation($annotation){
		$this->addAnnotations(array($annotation));
	}
	
	public function setWritable($name, $value){
		if(!is_null($annotation = $this->getAnnotationByReference(ucfirst($name), ucfirst($value)))){
			$annotation->isWritable = true;
		}
	}
	
	public function getNewValue($name, $value){
		if(!is_null($annotation = $this->getAnnotationByReference(ucfirst($name), ucfirst($value)))){
			return $annotation->newValue;
		}
		return null;
	}
	
	private function &getAnnotationByReference($name, $value){
		if(array_key_exists($name, $this->annotations)){
			foreach($this->annotations[$name] as $key => $dc){
				if($this->annotations[$name][$key]->equals($value)){
					return $this->annotations[$name][$key];
				}
			}
		}
		
		$nullResult = null;
		return $nullResult;
	}
	
	public function getNewAnnotations(){
		$newAnnotations = array();
		
		foreach($this->annotations as $annotations){
			foreach($annotations as $annotation){
				if($annotation->currentValue == null || $annotation->currentValue == ''){
					$newAnnotations[] = array('name' => $annotation->name, 'value' => $annotation->newValue);
				}
			}
		}
		
		return $newAnnotations;
	}
	
	public function getAnnotations(){
		return $this->annotations;
	}
	
	
}

class TFAnnotationData {
	
	public $name;
	public $currentValue = null;
	public $renderedValue;
	public $newValue = null;
	public $isWritable = false;
	public $hash = null;
	public $dataValue = null;
	public $typeId = null;
	
	
	public function __construct($name, $currentValue = null, $renderedValue = null, 
			$hash = null, $typeId=null, $newValue = null){
				
		$this->name = $name;
		$this->currentValue = ucfirst($currentValue);
		$this->renderedValue = $renderedValue;
		$this->newValue = $newValue;
		$this->hash = $hash;
		$this->typeId = $typeId;
		
		if(!is_null($this->typeId)){
			$this->dataValue = SMWDataValueFactory::newTypeIDValue($typeId);
		}
		
		if(is_null($this->currentValue) || $this->currentValue == ''){
			$this->isWritable = true;
		}
		
		//remove category prefix from category annotations
		if($this->name == TF_CATEGORY_KEYWORD){
			global $wgLang;
			if(strpos($this->newValue, $wgLang->getNSText(NS_CATEGORY).":") === 0){
				$this->newValue = substr($this->newValue, strpos($this->newValue, ":") +1);
			}
		}
		
		
	}
	
	public function equals($value){
		if(is_null($this->dataValue)){
			return false;
		}
		
		$this->dataValue->setUserValue($value);
		
		if(ucfirst($this->hash) == ucfirst($this->dataValue->getHash())){
			return true;
		} else {
			return false;
		}
	}
}

class TFTemplateParameterCollection {

	public $templateParameters = array();
	private $allTemplateParameters = array();
	private $pomTemplateIds = array();
	
	public function addTemplateParameter($parameter){
		if(!is_null($parameter->template)){
			if(!array_key_exists($parameter->template, $this->templateParameters)){
				$this->templateParameters[$parameter->template] = array();
			}
			
			if(!is_null($parameter->name)){
				if(!array_key_exists($parameter->name, $this->templateParameters[$parameter->template])){
					$this->templateParameters[$parameter->template][$parameter->name] = $parameter;
				} else {
					foreach($parameter->currentValues as $key => $value){
						$this->templateParameters[$parameter->template][$parameter->name]->currentValues[$key] = $value;
					}
					
					foreach($parameter->newValues as $key => $value){
						$this->templateParameters[$parameter->template][$parameter->name]->newValues[$key] = $value;
					}
				}
			} else {
				$this->allTemplateParameters[$parameter->template] = true;				
			}
		}
	}
	
	public function addTemplateParameters($parameters){
		foreach($parameters as $parameter){
			$this->addTemplateParameter($parameter);
		}
	}
	
	public function setTemplateParameterValue($template, $name, $value, $pomTemplateId){
		if(strlen($value) > 0){
			if(array_key_exists($template, $this->templateParameters)){
				if(array_key_exists($template, $this->allTemplateParameters)
						&& !array_key_exists($name, $this->templateParameters[$template])){
					$this->templateParameters[$template][$name] = new TFTemplateParameter($template.'#'.$name);
				}
				
				if(array_key_exists($name, $this->templateParameters[$template])){
					$this->templateParameters[$template][$name]->currentValues[$pomTemplateId] = $value;
				} 
			}
		}
	}
	
	public function getNewTemplateParameterValue($template, $name, $value, $pomTemplateId){
		if(array_key_exists($template, $this->templateParameters) ){
			if(array_key_exists($name, $this->templateParameters[$template])){
				if(array_key_exists($pomTemplateId, $this->templateParameters[$template][$name]->currentValues)){
					if($value == $this->templateParameters[$template][$name]->currentValues[$pomTemplateId]){
						if(array_key_exists($pomTemplateId, $this->templateParameters[$template][$name]->newValues)){
							return $this->templateParameters[$template][$name]->newValues[$pomTemplateId];
						}
					}
				}
			}
		}
		return null;
	}
	
	public function getNewTemplateParameters($template, $pomTemplateId){
		$newParameters = array();
		
		if(array_key_exists($template, $this->templateParameters) ){
			foreach($this->templateParameters[$template] as $parameter){
				if(array_key_exists($pomTemplateId, $parameter->currentValues)){
					if(is_null($parameter->currentValues[$pomTemplateId])){
						if(array_key_exists($pomTemplateId, $parameter->newValues)){
							$newParameters[$parameter->name] = $parameter->newValues[$pomTemplateId];
						}
						
					}
				}
			}
		}
		
		return $newParameters;
	}
	
	public function getNewTemplateCalls(){
		$newTemplateCalls = array();
		
		foreach($this->templateParameters as $template => $parameters){
			foreach($parameters as $parameter){
				if(array_key_exists(TF_NEW_TEMPLATE_CALL, $parameter->newValues)){
					if(!array_key_exists($template, $newTemplateCalls)){
						$newTemplateCalls[$template] = array();
					}
					$newTemplateCalls[$template][$parameter->name] = $parameter->newValues[TF_NEW_TEMPLATE_CALL];
				}
			}
		}
		
		return $newTemplateCalls;
	}
	
	public function getParameters(){
		foreach($this->templateParameters as $template => $parameters){
			if(array_key_exists($template, $this->pomTemplateIds)){
				foreach($parameters as $parameter){
					foreach($this->pomTemplateIds[$template] as $pomTemplateId => $dontCare){
						if(!array_key_exists($pomTemplateId, $parameter->currentValues)){
							$this->templateParameters[$template][$parameter->name]->currentValues[$pomTemplateId] = '';
						}
					}
				}
			}
		}
		return $this->templateParameters;
	}
	
	public function addTemplate($template, $pomTemplateId){
		if(!array_key_exists($template, $this->pomTemplateIds)){
			$this->pomTemplateIds[$template] = array();;
		}
		$this->pomTemplateIds[$template][$pomTemplateId] = true;
	}
}

class TFTemplateParameter {
	
	public $name;
	public $template;
	public $currentValues = array();
	public $newValues = array();
	public $isWritable = true;
	
	public function __construct($address, $currentValues = array(), $newValues = array()){
		$address = explode('#', $address, 2);
		if(count($address) >= 1){
			$this->template = $address[0];
		}
		
		if(count($address) == 2){
			$this->name = $address[1];
		}
		
		$this->currentValues = $currentValues;
		$this->newValues = $newValues;
	}
}





//error();