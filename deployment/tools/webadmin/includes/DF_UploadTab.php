<?php
/*  Copyright 2011, ontoprise GmbH
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
 * @ingroup WebAdmin
 *
 * Upload tab
 *
 * @author: Kai Kühn / ontoprise / 2011
 *
 */
if (!defined("DF_WEBADMIN_TOOL")) {
    die();
}

class DFUploadTab {
	/**
     * Status tab
     *
     */
    public function __construct() {

    }
    
    public function getTabName() {
        global $dfgLang;
        return $dfgLang->getLanguageString('df_webadmin_uploadtab');
    }

    public function getHTML() {
    	 global $dfgLang;
	    $uploadButtonText = $dfgLang->getLanguageString('df_webadmin_upload');
    	$html = <<<ENDS
<form action="upload.php" method="post" enctype="multipart/form-data">
<input type="file" name="datei"><br>
<input type="submit" value="$uploadButtonText">
</form>
ENDS
;
return $html;
    }
}