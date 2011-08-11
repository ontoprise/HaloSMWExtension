<?php

/**
 * @file
  * @ingroup DAPOM
  * 
  * @author Dian
 */

/**
 * Represents a built-in parser function in the POM of a page.
 *
 */
class POMBuiltInParserFunction extends POMParserFunction
{
	/**
	 * Constructs a new built-in parser function.
	 *
	 * @param string $text The original text of the element.
	 * @return POMBuiltInParserFunction
	 */
	public function POMBuiltInParserFunction($text)
	{
		$this->children = null; // forcefully ignore children		
		$this->nodeText = $text;
	}

	/**
	 * Convert the object to a string.
	 *
	 * @return string
	 */
	public function toString()
	{	
		return $this->nodeText;
	}
}
