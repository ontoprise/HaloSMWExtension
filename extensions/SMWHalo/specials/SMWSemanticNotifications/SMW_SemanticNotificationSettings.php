<?php
/*  Copyright 2008, ontoprise GmbH
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

/**
 * This file contains the settings that configure the semantic notification extension.
 * 
 * @author Thomas Schweitzer
 */

$smwgSemanticNotificationLimits = array(
    "allUsers" => array(
        "notifications" => 2, 
        "size" => 10000, 
        "min interval" => 7),
    "group darkmatter" => array(
        "notifications" => 10, 
        "size" => 100000, 
        "min interval" => 3),
    "group gardener" => array(
        "notifications" => 20, 
        "size" => 1000000,
        "min interval" => 1),
    "group sysop" => array(
        "notifications" => 100, 
        "size" => 1000000,
        "min interval" => 1)
);
?>