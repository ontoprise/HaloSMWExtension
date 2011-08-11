<?php
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
