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
