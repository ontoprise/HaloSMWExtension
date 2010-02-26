<?php

/**
 * @file
  * @ingroup DAPOM
  * 
  * @author Dian
 */

/**
 * Represents an ask function in the POM of a page.
 *
 */
class POMAskFunction extends POMExtensionParserFunction
{
	/**
	 * Constructs a new ask function.
	 *
	 * @param string $text The original text of the element.
	 * @return POMAskFunction
	 */
	public function POMAskFunction($text)
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
