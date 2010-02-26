<?php

/**
 * @file
  * @ingroup DAPOM
  * 
  * @author Dian
 */

/**
 * The abstract class representing an annotation.
 *
 */
abstract class POMAnnotation extends POMElement {

	/**
	 * The name of the annotation.
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * The value of the annotation.
	 *
	 * @var string
	 */
	public $value = '';

	/**
	 * The representation of the value, i.e. the string after the '|'.
	 *
	 * @var string
	 */
	public $representation = '';

}

/**
 * The category class used.
 *
 */
class POMCategory extends POMAnnotation {
	/**
	 * Class constructor. Parses all category attributes.
	 *
	 * @param string $text The original text.
	 * @return POMAnnotation
	 */
	public function POMCategory($text)
	{
//		$this->nodeText = $text;
		$this->name = 'Category';
		$this->value = $this->parseValue($text);
		$this->representation = '';

		$this->children = null; // forcefully ignore children

		$this->id = "category".POMElement::$elementCounter;
		POMElement::$elementCounter++;
	}

	/**
	 * Class constructor. Creates the annotation based on the given parameters.
	 *
	 * @param string $value The name of the category.
	 * @return POMCategory
	 */
	public static function createCategory($value)
	{
		$__nodeText = '[['.'Category'.':'.$value.']]';
		return new POMCategory($__nodeText);
	}

	private function parseValue ($text){
		if(strpos($text, '|')){
			$__start = strpos($text, ':')+1;
			$__end = strpos($text, '|');
			return substr($text, $__start , $__end - $__start);
		}else{
			$__start = strpos($text, ':')+1;
			$__end = strpos($text, ']]');
			return substr($text, $__start , $__end - $__start);
		}
	}

	/**
	 * Copies the current attribute values into a string representing a category annotation.
	 *
	 * @return string The markup for the category.
	 */
	public function toString(){
		$__stringValue = '';
		$__stringValue = '[['.$this->name.':'.$this->value.']]';

		return $__stringValue;
	}

	/**
	 * Get the value of the category.
	 *
	 * @return string The value of the category.
	 */
	public function getValue(){
		return $this->value;
	}

	/**
	 * Set the value of the category.
	 *
	 * @param string $value The value.
	 */
	public function setValue($value){
		$this->value = $value;
	}
}

/**
 * The property class.<br/>
 * PLEASE NOTE: n-ary properties are generally not supported since the value
 * in a name::value pair is represented as a string and DOESN'T underly further processing, e.g.
 * splitting by ';' or checking the property type on the property definition page.
 *
 */
class POMProperty extends POMAnnotation {
	/**
	 * Class constructor. Parses all annotation attributes.
	 *
	 * @param string $text The original text.
	 * @return POMAnnotation
	 */
	public function POMProperty($text)
	{
//		$this->nodeText = $text;
		$this->name = $this->parseName($text);
		$this->value = $this->parseValue($text);
		$this->representation = $this->parseRepresentation($text);

		$this->children = null; // forcefully ignore children

		$this->id = "property".POMElement::$elementCounter;
		POMElement::$elementCounter++;
	}

	/**
	 * Class constructor. Creates the annotation based on the given parameters.
	 *
	 * @param string $name
	 * @param string $value
	 * @param string $representation
	 * @return POMProperty
	 */
	public static function createProperty($name, $value, $representation = NULL)
	{
		if($representation !== NULL){
			$__nodeText = '[['.$name.'::'.$value.'|'.$representation.']]';
		}else{
			$__nodeText = '[['.$name.'::'.$value.']]';
		}

		return new POMProperty($__nodeText);
	}

	/**
	 * Copies the current attribute values into a string representing a property annotation.
	 *
	 * @return string The markup for the property.
	 */
	public function toString()
	{	
		$__stringValue = '';
		if ( strcmp($this->representation, '') === 0){
			$__stringValue = '[['.$this->name.'::'.$this->value.']]';
		}else{
			$__stringValue = '[['.$this->name.'::'.$this->value.'|'.$this->representation.']]';
		}
		return $__stringValue;
	}

	private function parseName ($text){		
		while(preg_match('/^\[\[/',$text) !== 0){
			$__start = strpos($text, '[[')+2;
			$text = substr($text, $__start);			
		}
		return substr($text, 0, strpos($text, '::'));
	}

	private function parseValue ($text){
		if(strpos($text, '|')){
			$__start = strpos($text, '::')+2;
			$__end = strpos($text, '|');
			return substr($text, $__start , $__end - $__start);
		}else{
			$__start = strpos($text, '::')+2;
			$__end = strpos($text, ']]');
			return substr($text, $__start , $__end - $__start);
		}
	}

	private function parseRepresentation ($text){
		if(strpos($text, '|')){
			$__start = strpos($text, '|')+1;
			$__end = strpos($text, ']]');
			return substr($text, $__start , $__end - $__start);
		}else{
			return '';
		}
	}

	/**
	 * Get the name of the property.
	 *
	 * @return string The name of the property.
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * Set the name of the property.
	 *
	 * @param string $name The name.
	 */
	public function setName($name){
		$this->name = $name;
	}

	/**
	 * Get the value of the property.
	 *
	 * @return string The value of the property.
	 */
	public function getValue(){
		return $this->value;
	}

	/**
	 * Set the value of the property.
	 *
	 * @param string $value The value.
	 */
	public function setValue($value){
		$this->value = $value;
	}

	/**
	 * Get the representation of the property.
	 *
	 * @return string The representation of the property.
	 */
	public function getRepresentation(){
		return $this->representation;
	}

	/**
	 * Set the representation of the property.
	 *
	 * @param string $representation The representation.
	 */
	public function setRepresentation($representation){
		$this->representation = $representation;
	}

}
