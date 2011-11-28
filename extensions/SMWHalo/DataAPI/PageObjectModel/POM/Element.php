<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */


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
	static public $elementCounter = 0;

	/**
	 * Convert the element to string.
	 *
	 * @return string
	 */
	public  function toString(){
		return $this->nodeText;
	}


}

