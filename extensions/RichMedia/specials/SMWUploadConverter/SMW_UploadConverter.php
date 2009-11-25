<?php
/*  Copyright 2008, ontoprise GmbH
*  This file is part of the RichMedia-Extension.
*
*   The RichMedia-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The RichMedia-Extension is distributed in the hope that it will be useful,
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
		global $smwgRMIP, $smwgUploadConverterExternal;
		require_once("$smwgRMIP/specials/SMWUploadConverter/SMW_UploadConverterSettings.php");
		
		$file = $uploadedFile->mLocalFile; // can't avoid to access private field :(  
		$mimeType = $file->getMimeType();
		$fileNameArray = split("\.", $file->getFullPath());
		$ext = $fileNameArray[count($fileNameArray)-1];	
		if($ext == "vcf"){
			$mimeType = "application/vcard";
		} else if ($ext == "ics"){
			$mimeType = "application/icalendar";
		}
		
		if (isset($smwgUploadConverterExternal[$mimeType])) {
			$converterApp = $smwgUploadConverterExternal[$mimeType];
			
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
	//			$text = '<pre>'.file_get_contents($textFile, FILE_USE_INCLUDE_PATH).'</pre>';
				$text = file_get_contents($textFile, FILE_USE_INCLUDE_PATH);
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
		} else if(array_key_exists($mimeType, $smwgUploadConverterInternal)){
			global $wgUploadConverterTemplateMapping;
			$wgUploadConverterTemplateMapping = $wgUploadConverterTemplateMapping[$mimeType];
			foreach($wgUploadConverterTemplateMapping as $key => $value){
				if(is_null($value)){
					unset($wgUploadConverterTemplateMapping[$key]);
				}
			}
			$path = $file->getFullPath();
			$text = file_get_contents($path);
			$class = $smwgUploadConverterInternal[$mimeType];
			$converter = new $class($text);
			$text = $converter->getConvertedText();
			  	
			$title = $file->getTitle();
			$article = new Article($title);
	
			if ($article->exists()) {
				// Set the article's content
				$success = $article->doEdit($text, wfMsg('uc_edit_comment'));
			}
			
			return true;
		} else {
			// no converter specified for the mime type
			return true;
		}
		
	}
	
	public static function getFileContent(&$file) {
		global $smwgRMIP;
		require_once("$smwgRMIP/specials/SMWUploadConverter/SMW_UploadConverterSettings.php");
		global $smwgUploadConverterExternal, $smwgUploadConverterInternal;
		
		$mimeType = $file->getMimeType();
		
		$fileNameArray = split("\.", $file->getFullPath());
		$ext = $fileNameArray[count($fileNameArray)-1];
		if($mimeType == "text/plain" && $ext == "doc"){
			$mimeType = "application/msword";
		} else if($ext == "vcf"){
			//todo:deal with mime types
			$mimeType = "application/vcard";
		} else if ($ext == "ics"){
			$mimeType = "application/icalendar";
		}
		
		if (isset($smwgUploadConverterExternal[$mimeType])){
			$converterApp = $smwgUploadConverterExternal[$mimeType];
			
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
				// => return its content 
				$text = '<pre>'.file_get_contents($textFile, FILE_USE_INCLUDE_PATH).'</pre>';
				// delete temp. file
				unlink($textFile);
			} else {
				$text = wfMsg('uc_not_converted', $mimeType, $converterApp);
			}			
			return $text;
		} else if(array_key_exists($mimeType, $smwgUploadConverterInternal)){
			global $wgUploadConverterTemplateMapping;
			$wgUploadConverterTemplateMapping = $wgUploadConverterTemplateMapping[$mimeType];
			foreach($wgUploadConverterTemplateMapping as $key => $value){
				if(is_null($value)){
					unset($wgUploadConverterTemplateMapping[$key]);
				}
			}
			$path = $file->getFullPath();
			$text = file_get_contents($path);
			$class = $smwgUploadConverterInternal[$mimeType];
			$converter = new $class($text);
			return $converter->getConvertedText();
			  
		} else {
			return $mimeType;
			// no converter specified for the mime type
			return "";
		}
	}
}

