<?php
/* Copyright 2009, ontoprise GmbH
 * This file is part of the User Manual Extension.
 *
 * The SMW UserManual-Extension is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or (at your
 * option) any later version.
 *
 * The SMW UserManual-Extension is distributed in the hope that it will be
 * useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Maintenance script for setting up wikipages with Context sensitive help
 * articles in the local wiki.
 * See README file on how to use this script.
 *
 * @author Stephan Robotta
 * Date: 10.12.2009
 *
 */
if (array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] != NULL) {
    echo "Invalid access! A maintenance script MUST NOT accessed from remote.";
    return;
}

$mediaWikiLocation = dirname(__FILE__) . '/../../..';
require_once "$mediaWikiLocation/maintenance/commandLine.inc";
$dir = dirname(__FILE__);
$umeIP = "$dir/..";

require_once("$umeIP/includes/SMW_CshArticle.php");
require_once("$umeIP/includes/SMW_FetchArticles.php");

$delete = array_key_exists('d', $options);
$overwrite = array_key_exists('o', $options);
$export = array_key_exists('e', $options);
$import = array_key_exists('i', $options);
$file = (isset($options['file']))?$options['file']:'';

if ($delete) {
	echo wfMsg("smw_ume_del_csh_pages")."\n";
	UME_FetchArticles::deletePages();
	echo wfMsg("smw_ume_done")."\n";
} else if ($export) {
    if (!$file) {
        echo wfMsg("smw_ume_missing_fparam")."\n";
        echo wfMsg("smw_ume_setup_usage")."\n";
        return;
    }
    echo wfMsg("smw_ume_export_csh_pages")."\n";
    UME_FetchArticles::exportPages($file, $overwrite);
    echo wfMsg("smw_ume_done")."\n";
} else if ($import) {
    if (!$file) {
        echo wfMsg("smw_ume_missing_fparam")."\n";
        echo wfMsg("smw_ume_setup_usage")."\n";
        return;
    }
    echo wfMsg("smw_ume_import_csh_pages")."\n";
    UME_FetchArticles::importPages($file, $overwrite);
    echo wfMsg("smw_ume_done")."\n";
} else {
    echo wfMsg("smw_ume_get_csh_pages")."\n";
    UME_FetchArticles::installPages($overwrite);
    echo wfMsg("smw_ume_install_done")."\n";
}
?>
