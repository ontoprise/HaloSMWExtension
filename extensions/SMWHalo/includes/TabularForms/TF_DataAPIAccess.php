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
			
			//todo:think about when and where to initialize POM. LocalSettings is not good.
			
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
					$annotations->setWritable('__Category__', $element->value);
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
								$annotations->setWritable(trim($silent[0]), trim($silent[1]));
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
		public function updateValues($annotations, $parameters, $revisionId){
		
			//todo: annotation labels
			
			//todo: LANGUAGE
			if(is_null($this->title) || !$this->title->exists()){
				return 'This instance has been deleted in the meantime.'; 
			}
			
			if($this->getRevisionId() != $revisionId){
				return 'This instance has been deleted in the meantime.';
			}
			
			if($this->isReadProtected){
				return 'This instance has been made read-protected in the meantime.';
			}
			
			if($this->isWriteProtected){
				return 'This instance has been made write-protected in the meantime.';
			}
			
			$elements = $this->pomPage->getElements()->listIterator(); 
			
			while($elements->hasNext()){
				$element = $elements->getNextNodeValueByReference();
				
				if($element instanceof POMProperty){
					if(!is_null($newValue = $annotations->getNewValue($element->name, $element->value))){
						if(strlen($newValue) == 0){
							$this->pomPage->delete($element);
						} else {
							$element->value = $newValue;
						}
					}
				} else if($element instanceof POMCategory){
					if(!is_null($newValue = $annotations->getNewValue('__Category__', $element->value))){
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
						$silents = trim(substr($element->nodeText, strlen('{{CreateSilentAnnotations:')));
						$silents = substr($silents, 0, strlen($silents)-2);
						$silents = explode('|', $silents);
						$modified = false;
						foreach($silents as $key => $silent){
							if($key == 0) continue;
							$silent = explode('=', $silent, 2);
							if(count($silent == 2)){
								if(!is_null($newValue = $annotations->getNewValue(trim($silent[0]), trim($silent[1])))){
									$modified = true;
									if(strlen($newValue) == 0){
										unset($silents[$key]);
									} else {
										$silents[$key] = $silent[0].'='.$newValue;
									}
								}
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
			foreach($newAnnotations as $newAnnotation){
				if($newAnnotation['name'] == '__Category__'){
					$text .= '[[Category:'.$newAnnotation['value'].'| ]]';
				} else {
					$text .= '[['.$newAnnotation['name'].'::'.$newAnnotation['value'].'| ]]';
				}
			}
			
			$this->article->doEdit($text, 'tabular forms');
			
			return true;
		}
		
	/*
	 * This method creates a new instance 
	 */
	public function createInstance($annotations, $parameters){
		
		//todo: deal with acls
		
		if(is_null($this->title) || $this->title->exists()){
			return false; 
		}
		
		$text = '';
		
		$annotations = $annotations->getNewAnnotations();
		foreach($annotations as $annotation){
			if($annotation['name'] == '__Category__'){
				$text .= '[[Category:'.$annotation['value'].'| ]]';
			} else if (strlen($annotation['name']) >0){
				$text .= '[['.$annotation['name'].'::'.$annotation['value'].'| ]]';
			}
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
			
		return true;
	}
	
	/*
	 * Deletes an instance
	 */
	public function deleteInstance($revisionId){
		
		//todo: Deal with ACLS
		
		if(is_null($this->title) || !$this->title->exists()){
			return true; 
		}
		
		if($this->getRevisionId() != $revisionId){
			return false;
		}	
		
		$this->article->doDelete('tabular forms');
		
		return true;
	}
		
}

class TFAnnotationDataCollection {
	
	private $annotations = array();
	
	public function addAnnotations($annotations){
		foreach($annotations as $annotation){
			$add = true;
			if(array_key_exists($annotation->name, $this->annotations)){
				foreach($this->annotations[$annotation->name] as $cAnnotation){
					if($annotation->currentValue == $cAnnotation->currentValue){
						$add = false;
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
	
	
	public function __construct($name, $currentValue = null, $renderedValue = null, $hash = null, $typeId=null, $newValue = null){
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
		
	}
	
	public function equals($value){
		if(is_null($this->dataValue)){
			return false;
		}
		
		$this->dataValue->setUserValue($value);
		
		if($this->hash == $this->dataValue->getHash()){
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