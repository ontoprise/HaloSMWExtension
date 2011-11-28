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
 * This file provides an abstract class which represents a printer
 * for webservice usage results
 *
 * @author Ingo Steinbauer
 */

/**
 * @file
 * @ingroup DIWSResultPrinters
 * 
 * @author Ingo Steinbauer
 */

/**
 * This group contains all result printers for web service results.
 * @defgroup DIWSResultPrinters
 * @ingroup DIWebServices
 */

/**
 * an abstract class which represents a printer for web service usage results
 *
 */
abstract class WebServiceResultPrinter{

	/**
	 * get an instance of this class
	 *
	 * @return WebServiceListResultPribter
	 */
	public static function getInstance(){
		
	}

	protected function __construct(){}
	protected function __clone(){}

	/**
	 * get web service usage result as wikitext
	 *
	 * @param unknown_type $wsResult
	 * @return unknown
	 */
	abstract public function getWikiText($wsTemplate, $wsResult);

}
