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
 * Parser is an abstract class for various parsers that will post-process a page.
 * All parsers must subclass this class.
 *
 * @author Sergey Chernyshev  
 */
abstract class POMParser
{
	/**
		This is main method for parsers
		It takes a page as argument and processes it adding elements
	*/
	public abstract function Parse(POMPage &$page);
}

