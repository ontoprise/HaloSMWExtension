<?php
/*  Copyright 2010, ontoprise GmbH
 *
 *   The deployment tool is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The deployment tool is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file
 * @ingroup DeployFramework
 *
 * Checks if the installation is valid.
 *
 * @author: Kai Kuehn / ontoprise / 2010
 *
 *
 */

global $rootDir;
$rootDir = dirname(__FILE__);
$rootDir = str_replace("\\", "/", $rootDir);
$rootDir = realpath($rootDir."/../../");

require_once('DF_Tools.php');
require_once('DF_PackageRepository.php');
require_once('DF_ConsistencyChecker.php');

$mwrootDir = dirname(__FILE__);
$mwrootDir = str_replace("\\", "/", $mwrootDir);
$mwrootDir = realpath($mwrootDir."/../../../");
$cchecker = ConsistencyChecker::getInstance($mwrootDir);
$cchecker->checkInstallation();