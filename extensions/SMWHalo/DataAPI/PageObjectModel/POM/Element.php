<?php

/**
 * @file
  * @ingroup DAPOM
  * 
  * @author Dian
 */

/**
 * This class is used as an abstract representation for elements of a page.
 * All element types should extend this class.
 *
 * @author Sergey Chernyshev
 * @author Dian As of version 0.4.
 */
abstract class POMElement {
	/**
	 * The original text.
	 *
	 * @var string
	 */
	public $nodeText = '';
	
	/**
	 * The ID of the element. 
	 * Is a unique value only for a specific revision ID of the page.
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * The children nodes of the element.
	 *
	 * @var DLList
	 */
	protected $children;
	
	/**
	 * Used to count element on a page and to create an ID for each element.
	 * 
	 * @var int
	 */
	static protected $elementCounter = 0;

	/**
	 * Convert the element to string.
	 *
	 * @return string
	 */
	public  function toString(){
		return $this->nodeText;
	}


}

