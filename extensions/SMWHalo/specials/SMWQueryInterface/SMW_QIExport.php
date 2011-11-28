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
 * @ingroup SMWHaloSpecials
 * @ingroup SMWHaloQueryInterface
 * 
 * @author Markus Nitsche
 */
global $request_query;

if(isset($request_query) && $request_query == true){
	unset($request_query);
	header('Pragma: no-cache');
	header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header('Expires: 0');
	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
	header("Content-type: application/xls");
	header("Content-Disposition: inline; filename=result.xls");
	header("Content-Transfer-Encoding: binary");
	header("Content-Description: Downloaded File");
	
	
	$query = $_GET['q'];
	$query = htmlspecialchars_decode($query);
	$query = str_replace("\\", "", $query);
	echo $query;
} else {
	unset($request_query);
	echo "Sorry, an error occured during export!";
}
