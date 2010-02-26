<?php

/**
 * @file
  * @ingroup DAPOM
  * 
  * @author Dian
 */

/**
 * Represents an extension parser function in the POM of a page.
 *
 */
class POMExtensionParserFunction extends POMParserFunction
{
	/**
	 * Constructs an extension parser function.
	 *
	 * @param string $text The original text of the element.
	 * @return POMExtensionParserFunction
	 */
	public function POMExtensionParserFunction($text)
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
