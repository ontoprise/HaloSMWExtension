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
