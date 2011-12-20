<?
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

/* Check for *.rej files that are created when patches fail
 *
 * Usage: checkForFailedPatches.php [ -d <directory> ]
 *
 * -d may contain the path where the search is started.
 */

$dir= ".";
$args = $_SERVER['argv'];
while ($arg = array_shift($args)) {
    if ($arg == '-d')
        $dir = array_shift($args) or die ("Error: missing value for -d\n");
}

if (substr($dir, -1) == '/' || substr($dir, -1) == '\\')
   $dir = substr($dir, 0, -1);

$dirList= array($dir);
    
while ($dir= array_shift($dirList)) {
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
            if ($file == "." || $file == "..")
                continue;
            else if (is_dir($dir.'/'.$file)) {
                $dirList[]= $dir.'/'.$file;
                continue;
            }
            else if (preg_match('/.rej$/', $file))
                echo $dir.'/'."$file\r\n";
        }
        closedir($handle);
    }
}