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
global $dfgTestfunctions;
$dfgTestfunctions[] = 'di_checkInstallation';

function di_checkInstallation() {
	global $dfgRequiredExtensions, $dfgRequiredPHPVersions, $dfgRequiredFunctions;

	$dfgRequiredPHPVersions[] = '5.3.2';
	$dfgRequiredExtensions['imap'][] = "Please install PHP extension 'php_imap'. It is required from DataImport";

	$version = phpversion();
	if (strpos($version, "-") !== false) {
		list($version, $rest) = explode("-", $version);
	}
	$currentPHPVersion = new DFVersion($version);

	if ($currentPHPVersion->isLower(new DFVersion("5.3.3"))) {
		$dfgRequiredExtensions['mime_magic'][] = "Please install PHP extension 'php_mime_magic'. It is required from DataImport.";
	}

}