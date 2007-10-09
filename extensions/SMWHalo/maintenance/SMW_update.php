<?php
/*  Copyright 2007, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
 * Created on 21.09.2007
 *
 * Update database for HALO extension.
 * 
 * 
 * Author: kai
 */
 

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";

$dbr =& wfGetDB( DB_MASTER );

print "\nUpdate the database now...";

$dbr->query('UPDATE smw_attributes SET value_datatype = '.$dbr->addQuotes('_chf').' WHERE value_datatype = '.$dbr->addQuotes('Chemical_formula'));
$dbr->query('UPDATE smw_attributes SET value_datatype = '.$dbr->addQuotes('_che').' WHERE value_datatype = '.$dbr->addQuotes('Chemical_equation'));

print "done!\n";
 
?>
