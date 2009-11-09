<?php
/* Copyright 2009, ontoprise GmbH
 * This file is part of the HaloACL-Extension.
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
 * Maintenance script for setting up the database tables for Halo ACL
 *
 * @author Thomas Schweitzer
 * Date: 21.04.2009
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

$delete = array_key_exists('delete', $options);

if ($delete) {
	echo wfMsg("smw_ume_del_csh_pages");
	//HACLStorage::getDatabase()->dropDatabaseTables();
	echo wfMsg("smw_ume_done")."\n";
} else {
    echo wfMsg("smw_ume_create_props")."\n";
    UME_FetchArticles::installProperties();
    echo wfMsg("smw_ume_get_csh_pages")."\n";
    UME_FetchArticles::installPages();
    echo wfMsg("smw_ume_install_done")."\n";

}
?>
