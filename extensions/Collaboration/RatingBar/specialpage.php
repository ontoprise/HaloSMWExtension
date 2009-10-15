<?php

/****************************************************************************
**
** This file is part of the Rating Bar extension for MediaWiki
** Copyright (C)2009
**                - Franck Dernoncourt <www.francky.me>
**                - PatheticCockroach <www.patheticcockroach.com>
**
** Home Page : http://www.wiki4games.com
**
** This program is free software; you can redistribute it and/or
** modify it under the terms of the GNU General Public License
** as published by the Free Software Foundation; either
** version 3 of the License, or (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
** <http://www.gnu.org/licenses/>
*********************************************************************/

	
class Ratings extends SpecialPage {
	function __construct() {
		parent::__construct( 'Ratings' );
		wfLoadExtensionMessages('Ratings');
	}
 
	function execute( $par ) {
		global $wgRequest, $wgOut;
 
		$this->setHeaders();
		
		// Get some variables
		require ('config.php');


		$wikitext = '<w4g_ratinglist numberofitems ="10" numberofdays="7"/>';
		$wikitext .= '<w4g_ratinglist numberofitems ="10" numberofdays="50"/>';
		$wikitext .= '<w4g_ratinglist numberofitems ="10" />';
		$wikitext .= '<w4g_ratinglist topvoters="20"/>';
		$wikitext .= '<w4g_ratinglist latestvotes="20"/>';
		
		// Output the result
		//$wgOut->addHTML( $output );
		
		// Output 
		$wgOut->addWikiText($wikitext);
	}
}