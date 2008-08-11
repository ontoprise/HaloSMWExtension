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
 * This class handles the conversion of PDF and MS Word documents to text.
 * When these files are uploaded as files in the wiki (similar to images), their
 * content is converted to text and inserted in the article that hosts the file.
 * Thus the content is made available for search operations.
 * 
 * @author Thomas Schweitzer
 * 
 */
class UploadConverter {

	/**
	 * Converts the content of the uploaded file to text, if its mime type is
	 * 'application/pdf' or 'application/doc'. 
	 * The text is added to the article that hosts the file.
	 * This function is called by the hook 'UploadComplete'.
	 *
	 * @param UploadForm $uploadedFile
	 * 		This object contains the description of the uploaded file.
	 * @return bool
	 * 		true
	 */
	public static function convertUpload(&$uploadedFile) {
		global $smwgHaloIP;
		require_once("$smwgHaloIP/specials/SMWUploadConverter/SMW_UploadConverterSettings.php");
		
		$file = $uploadedFile->mLocalFile; // can't avoid to access private field :(  
		$mimeType = $file->getMimeType();
		$converterApp = $smwgUploadConverter[$mimeType];
		if (!$converterApp) {
			// no converter specified for the mime type
			return true;
		}
		wfLoadExtensionMessages('UploadConverter');
		
		$path = $file->getFullPath();
		$ext  = $file->getExtension();
		$textFile = substr($path,0,strlen($path)-strlen($ext)).'txt';
		$converterApp = str_replace('{infile}', $path, $converterApp);
		$converterApp = str_replace('{outfile}', $textFile, $converterApp);
		$ret = exec($converterApp, $output, $retVar);

		$text = "";
		if (file_exists($textFile)) {
			// a temporary file has been written 
			// => add its content into the article 
			$text = '<pre>'.file_get_contents($textFile).'</pre>';
			// delete temp. file
			unlink($textFile);
		} else {
			$text = wfMsg('uc_not_converted', $mimeType, $converterApp);
		}			
		$title = $file->getTitle();
		$article = new Article($title);

		if ($article->exists()) {
			// Set the article's content
			$success = $article->doEdit($text, wfMsg('uc_edit_comment'));
		}

		return true;
	}
	
}

