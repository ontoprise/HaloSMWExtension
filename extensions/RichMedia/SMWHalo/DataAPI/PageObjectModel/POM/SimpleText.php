<?php

/**
 * @file
  * @ingroup DAPOM
  * 
  * @author Dian
 */

/**
 * Represents a text node that is neither a template nor an annotation.
 * 
 * @version 0.1
 * @author Dian
 *
 */
class POMSimpleText extends POMElement {

	/**
	 * The class constructor.
	 *
	 * @param string $text	 
	 * @return POMSimpleTextNode
	 */
	public function POMSimpleText($text)
	{
		$this->nodeText = $text;		
		
		$this->children = null; // forcefully ignore children
		
		$this->id = "simpletext".POMElement::$elementCounter;
		POMElement::$elementCounter++;
	}

	public function toString()
	{
		return $this->nodeText;
	}
}
