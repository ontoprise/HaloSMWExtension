<?php
/*
 * Copyright (C) ontoprise GmbH
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
 * @ingroup ARCLibrary
 */
/* 
 * This file is the main entry point for the ARCLibrary extension.
 * It adds the ARC library, which was authored by Benjamin Nowack,
 * to §wgAuto, so that other extensions can make use of the rich feature
 * set of the ARC library. This file has to be included in LocalSettings.php
 * in order to enable the ARC2Library extension.
 * 
 * @author Ingo Steinbauer
 */


if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the ARCLibrary extension. It is not a valid entry point.\n" );
}

# Define the version constant, which enables other extensions to check whther ARCLib is installed.
define('ARCLIB_ARCLIBRARY_VERSION', '{{$VERSION}} [B{{$BUILDNUMBER}}]');


# This is the path to the ARCLibrary extension
$arclibgIP = $IP . '/extensions/ARCLibrary';


# load global functions
require_once($arclibgIP.'/includes/ARCLIB_GlobalFunctions.php');
enableARCLibrary();
