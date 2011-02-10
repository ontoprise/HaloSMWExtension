<?php


define('TF_NEW_TEMPLATE_CALL', '000');

include_once('extensions/SMWHalo/DataAPI/PageCRUD_Plus/PCP.php');
include_once('extensions/SMWHalo/DataAPI/PageObjectModel/POM.php');


class TFTest {

	public static function test(){
		//test is writable
		$annotations = new TFAnnotationDataCollection();
		
		$annotations = array();
		$annotations[] = new TFAnnotationData('PropA', '500');
		$annotations[] = new TFAnnotationData('PropA', '501');
		$annotations[] = new TFAnnotationData('PropA', '503');
		$annotations[] = new TFAnnotationData('PropA', '504');
		$annotations[] = new TFAnnotationData('PropB', '505');
		$annotations[] = new TFAnnotationData('PropA', '506');
		$annotations[] = new TFAnnotationData('PropA', '507');
		$annotations[] = new TFAnnotationData('PropB', '508');
		$annotations[] = new TFAnnotationData('PropA', '509');
		$annotations[] = new TFAnnotationData('PropC', '510');
		$annotations[] = new TFAnnotationData('PropD', '');
		
		
		$annotations->addAnnotations($annotations);
		
		$title = Title::newFromText('TestTF');
		
		TFDataAPIAccess::getInstance($title)->getWritableAnnotations($annotations);
		
		
		//read template parameters
		
		$parameters = new TFTemplateParameterCollection();
		$parameters->addTemplateParameter(new TFTemplateParameter('TFTemplateA'));
		$parameters->addTemplateParameter(new TFTemplateParameter('TFTemplateB.1'));
		$parameters->addTemplateParameter(new TFTemplateParameter('TFTemplateB.2'));
		$parameters->addTemplateParameter(new TFTemplateParameter('TFTemplateC.A'));
		$parameters->addTemplateParameter(new TFTemplateParameter('TFTemplateD.A'));
		$parameters->addTemplateParameter(new TFTemplateParameter('TFTemplateE'));
		$parameters->addTemplateParameter(new TFTemplateParameter('TFTemplateF'));
		
		$title = Title::newFromText('TestTF');
		
		TFDataAPIAccess::getInstance($title)->readTemplateParameters($parameters);
		
		
		
		//test write
		$annotations = new TFAnnotationDataCollection();
		
		$annotations = array();
		$annotations[] = new TFAnnotationData('PropA', '500', null, 'MOD500');
		$annotations[] = new TFAnnotationData('PropA', '501', null, 'MOD501');
		$annotations[] = new TFAnnotationData('PropA', '503', null, 'MOD503');
		$annotations[] = new TFAnnotationData('PropA', '504', null, 'MOD504');
		$annotations[] = new TFAnnotationData('PropB', '505', null, 'MOD505');
		$annotations[] = new TFAnnotationData('PropA', '506', null, 'MOD506');
		$annotations[] = new TFAnnotationData('PropA', '507', null, 'MOD507');
		$annotations[] = new TFAnnotationData('PropB', '508', null, 'MOD508');
		$annotations[] = new TFAnnotationData('PropD', '', null, 'MODNEW');
		
		$annotations->addAnnotations($annotations);
		
		$parameters = new TFTemplateParameterCollection();
		
		$parameters->addTemplateParameter(new TFTemplateParameter('CA.1', array('template4' => 'A45'), array('template4' => 'MOD')));
		$parameters->addTemplateParameter(new TFTemplateParameter('TFTemplateA.Param1',	
			array('template10' => '600', 'template16' => '602', 'template20' => null), 
			array('template10' => 'MOD600', 'template16' => 'MOD602', 'template20' => 'NEWMOD1')));
		$parameters->addTemplateParameter(new TFTemplateParameter('TFTemplateA.Param2',	
			array('template10' => '601', 'template16' => null, 'template20' => null), 
			array('template10' => 'MOD601', 'template16' => 'NEWMOD2', 'template20' => 'NEWMOD3')));		
		$parameters->addTemplateParameter(new TFTemplateParameter('TFTemplateB.1', array('template22' => '603'), array('template22' => 'MOD603')));
		$parameters->addTemplateParameter(new TFTemplateParameter('TFTemplateB.2', array('template22' => '604'), array('template22' => 'MOD604')));
		$parameters->addTemplateParameter(new TFTemplateParameter('TFTemplateC.A', array('template28' => '605'), array('template28' => 'MOD605')));
		$parameters->addTemplateParameter(new TFTemplateParameter('TFTemplateD.A', array('template34' => null), array('template34' => 'NEWMOD4')));
		
		
		$title = Title::newFromText('TestTF');

		TFDataAPIAccess::getInstance($title)->updateValues($annotations, $parameters);
	}
}




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
		
		/*
		 * Parse article and initialize the POMPage object
		 */
		private function initialize(){
			
			//think about when and where to initialize POM. LocalSettings is not good.
			
			$article = Article::newFromID($this->title->getArticleID());
			$text = $article->getContent();
			$this->pomPage = new POMPage($this->title->getFullText(), $text);
			
		}

		
		public function getWritableAnnotations($annotations){
			
			//todo: deal with asf and delimiters
			
			$elements = $this->pomPage->getElements()->listIterator(); 
			
			while($elements->hasNext()){
				$element = $elements->getNext()->getNodeValue();
				
				//echo('<pre>'.print_r($element, true).'</pre>');
				
				if($element instanceof POMProperty){
					$annotations->setWritable($element->name, $element->value);
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
					if(strpos($element->nodeText, '{{SilentAnnotations:') === 0){
						$silents = trim(substr($element->nodeText, strlen('{{SilentAnnotations:')));
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
			
			//todo:implement a getAnnotationsMethod
			
			//echo('<pre>'.print_r($annotations, true).'</pre>');
			
			return $annotations->getAnnotations();
		}
		
		
		
		public function readTemplateParameters($parameters){
			
			//todo:markAsRead-only depending on acls
			
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
			
			//echo('<pre>'.print_r($parameters, true).'</pre>');
			
			return $parameters;
		}
		
		
		
		
		public function updateValues($annotations, $parameters){
		
			//todo: revision check
			
			//todo can edit check
			
			//todo: annotation labels
			
			//todo category annotations
			
			$elements = $this->pomPage->getElements()->listIterator(); 
			
			while($elements->hasNext()){
				$element = $elements->getNextNodeValueByReference();
				
				if($element instanceof POMProperty){
					if(!is_null($newValue = $annotations->getNewValue($element->name, $element->value))){
						$element->value = $newValue;
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
									$sets[$key] = $set[0].'='.$newValue;
								}
							}
						}
						
						if($modified){
							$element->nodeText = '{{#set:'.implode('|', $sets).'}}';
						}
					} 
				} else if ($element instanceof POMBuiltInParserFunction){
					if(strpos($element->nodeText, '{{SilentAnnotations:') === 0){
						$silents = trim(substr($element->nodeText, strlen('{{SilentAnnotations:')));
						$silents = substr($silents, 0, strlen($silents)-2);
						$silents = explode('|', $silents);
						$modified = false;
						foreach($silents as $key => $silent){
							if($key == 0) continue;
							$silent = explode('=', $silent, 2);
							if(count($silent == 2)){
								if(!is_null($newValue = $annotations->getNewValue(trim($silent[0]), trim($silent[1])))){
									$modified = true;
									$silents[$key] = $silent[0].'='.$newValue;
								}
							}
						}
						
						if($modified){
							$element->nodeText = '{{SilentAnnotations:'.implode('|', $silents).'}}';
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
							$element->setParameter($name, $newValue);
						}
					}
				}
			}
			
			$this->pomPage->sync();
			
			$text = $this->pomPage->text;
			
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
				$text .= '[['.$newAnnotation['name'].'::'.$newAnnotation['value'].'| ]]';
			}
			
			//echo('<pre>'.$text.'</pre>');
	
			//$article->doEdit($pomPage->text, 'tabular forms');
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
		if(!is_null($annotation = $this->getAnnotationByReference($name, $value))){
			$annotation->isWritable = true;
		}
	}
	
	public function getNewValue($name, $value){
		if(!is_null($annotation = $this->getAnnotationByReference($name, $value))){
			return $annotation->newValue;
		}
		return null;
	}
	
	private function &getAnnotationByReference($name, $value){
		if(array_key_exists($name, $this->annotations)){
			foreach($this->annotations[$name] as $key => $dc){
				if($this->annotations[$name][$key]->currentValue == $value){
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
	
	
	public function __construct($name, $currentValue = null, $renderedValue = null, $newValue = null){
		$this->name = $name;
		$this->currentValue = $currentValue;
		$this->renderedValue = $renderedValue;
		$this->newValue = $newValue;	
		
		if(is_null($this->currentValue) || $this->currentValue == ''){
			$this->isWritable = true;
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
		if(array_key_exists($template, $this->templateParameters) ){
			
			if(array_key_exists($template, $this->allTemplateParameters)
					&& !array_key_exists($name, $this->templateParameters[$template])){
				$this->templateParameters[$template][$name] = new TFTemplateParameter($template.'.'.$name);
			}
			
			if(array_key_exists($name, $this->templateParameters[$template])){
				$this->templateParameters[$template][$name]->currentValues[$pomTemplateId] = $value;
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
	
	public function __construct($address, $currentValues = array(), $newValues = array()){
		$address = explode('#', $address, 2);
		if(count($address) >= 1){
			$this->template = $address[0];
		}
		
		if(count($address) == 2){
			$this->name = $address[1];
			
			$this->currentValues = $currentValues;
			$this->newValues = $newValues;
		}
	}
}





//error();