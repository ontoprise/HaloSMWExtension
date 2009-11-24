<?php

/*
 * This file is based on the MediaWiki WebDAV extension,
 * which is available at http://www.mediawiki.org/wiki/Extension:WebDAV
 */

/*
 * This modified version was implemented by Ingo Steinbauer @ontoprise GmbH
 */

/*
 * This modified version was implemented by Ingo Steinbauer @ontoprise GmbH
 */

/*
 *   This file is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This file is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

define( 'MW_SEARCH_LIMIT', 50 );

require_once('WD_FileData.php');
require_once( 'WD_Server.php' );

class WebDAVServer extends HTTP_WebDAV_Server {

	private $time;

	function init() {
		parent::init();

		# Prepend script path component to path components
		array_unshift( $this->pathComponents, array_pop( $this->baseUrlComponents['pathComponents'] ) );
	}

	function getAllowedMethods() {
		//todo: add copy and move method
		// i removed the REPORT method
		return array( 'OPTIONS', 'PROPFIND', 'GET', 'HEAD', 'DELETE', 'PUT', 'COPY', 'MOVE', 'MKCOL');
	}

	function options( &$serverOptions ) {
		parent::options( &$serverOptions );

		if ( $serverOptions['xpath']->evaluate( 'boolean(/D:options/D:activity-collection-set)' ) ) {
			$this->setResponseHeader( 'Content-Type: text/xml; charset="utf-8"' );

			echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
			echo "<D:options-response xmlns:D=\"DAV:\">\n";
			echo '  <D:activity-collection-set><D:href>' . $this->getUrl( array( 'path' => 'deltav.php/act' ) ) . "</D:href></D:activity-collection-set>\n";
			echo "</D:options-response>\n";
		}

		return true;
	}

	private function createFolderResponse($path, $displayName){
		return $this->createFileResponse($path, $displayName, 0, 'httpd/unix-directory',
			"collection", time());
	}

	private function createFileResponse($path, $displayName, $contentLength,
	$contentType, $resourceType, $lastEdit){
		$response = array();
		$response['path'] = 'WD_WebDAV.php/'.$path;
		$response['props'][] = WebDavServer::mkprop( 'displayname', $displayName );
		$response['props'][] = WebDavServer::mkprop( 'getcontentlength', $contentLength );
		$response['props'][] = WebDavServer::mkprop( 'getcontenttype', $contentType);
		$response['props'][] = WebDavServer::mkprop( 'resourcetype', $resourceType );
		$response['props'][] = WebDavServer::mkprop( 'getlastmodified', wfTimestamp( TS_UNIX, $lastEdit) );
		return $response;
	}

	function propfind( &$serverOptions ) {
		//todo: deal with propfind on files; think also about the isValidPropfindPath method
		$temp = implode("/", $this->pathComponents);
		$this->writeLog("propfind ".$temp);
		
		//$serverOptions['namespaces']['http://subversion.tigris.org/xmlns/dav/'] = 'V';
		$status = array();

		//todo: login stuff

		$fileData = new FileData($this->pathComponents);
		if(!$fileData->isValidPropfindPath()){
			return "400 Bad request";
		}

		# TODO: Use $wgMemc
		# todo: deal with table prefixes
		# deal with that in the complete class
		$dbr =& wfGetDB( DB_SLAVE );

		if($fileData->isDirectory()){
			if ($fileData->isRootDirectory()) {
				global $wgSitename;
				$status[] = $this->createFolderResponse("", $wgSitename);

				# Don't descend if depth is zero
				if ( empty( $serverOptions['depth'] ) ) {
					//todo remove comment
					//return $status;
				}

				$status = array_merge($status, $this->getNamespaceFolders());

				return $status;
			} else if($fileData->isRootFileNamespaceFolder()){
				$status[] = $this->createFolderResponse(
				$fileData->getPrefixedFolderName().'/', $fileData->getFolderName());
					
				if ( empty( $serverOptions['depth'] ) ) {
					return $status;
				}

				$status = array_merge($status, $this->getFileNamespaceFolders());

				$status[] = $this->createFolderResponse(
				$fileData->getPrefixedFolderName().'/'."File", "File");

				$status[] = $this->createFolderResponse(
				$fileData->getPrefixedFolderName().'/'."All", "All");

				return $status;
			} else if ($fileData->isAllFilesNamespaceFolder()){
				$status[] = $this->createFolderResponse(
				$fileData->getPrefixedFolderName().'/', $fileData->getFolderName());

				if ( empty( $serverOptions['depth'] ) ) {
					return $status;
				}

				//$status = array_merge($status, $this->getTempFiles($fileData));
					
				global $wgRichMediaNamespaceAliases, $wgWebDAVDisplayTalkNamespaces;
				if(isset($wgRichMediaNamespaceAliases)){
					$namespaces = $wgRichMediaNamespaceAliases;
					$namespaces["File"] = null;
					foreach($namespaces as $ns => $dontCare){
						if(!strpos($ns, "_talk") || $wgWebDAVDisplayTalkNamespaces){
							$nsId = $fileData->getNamespaceId(true, $ns);

							$results = $dbr->query(
							'SELECT page_title, page_latest, page_len, page_touched
							FROM page WHERE page_namespace = '.$nsId.'');

							$titles = array();
							while ( ( $result = $dbr->fetchRow( $results ) ) !== false ) {
								# TODO: Should maybe not be using page_title as URL component, but it's currently what we do elsewhere
								$title = Title::newFromUrl( $result[0] );
								$titles[] = $title;

								$title = $fileData->encodeFileName($title->getText());
								$status[] = $this->createFileResponse(
								$fileData->getPrefixedFolderName().'/'.$title.".mwiki",
								$title.".mwiki", $result[2], "text/x-wiki", null, $result[3]);
							}

							$files = RepoGroup::singleton()->findFiles($titles);
							foreach($files as $name => $file){
								$status[] = $this->createFileResponse(
								$fileData->getPrefixedFolderName().'/'.$name, $name,
								$file->size, $file->mime, null, $file->timestamp);
							}
						}
					}
				}

				return $status;
			} else if ($fileData->isNamespaceFolder()){
				$status[] = $this->createFolderResponse(
				$fileData->getPrefixedFolderName().'/', $fileData->getFolderName());
					
				if ( empty( $serverOptions['depth'] ) ) {
					return $status;
				}

				$nsId = $fileData->getNamespaceId();

				$results = $dbr->query(
				'SELECT page_title, page_latest, page_len, page_touched
				FROM page WHERE page_namespace = '.$nsId.'');

				$titles = array();
				while ( ( $result = $dbr->fetchRow( $results ) ) !== false ) {
					# TODO: Should maybe not be using page_title as URL component, but it's currently what we do elsewhere
					$title = Title::newFromUrl( $result[0] );
					$titles[] = $title;

					$title = $fileData->encodeFileName($result[0]);
					$status[] = $this->createFolderResponse(
						$fileData->getPrefixedFolderName().'/'.$title, $title);
				}

				if($fileData->isFileNamespaceFolder()){
					$files = RepoGroup::singleton()->findFiles($titles);

					foreach($files as $name => $file){
						$status[] = $this->createFileResponse(
						$fileData->getPrefixedFolderName().'/'.$name, $name,
						$file->size, $file->mime, null, $file->timestamp);
					}
				}

				return $status;
			} else if ($fileData->isFileNamespaceFolder()){
				$status[] = $this->createFolderResponse(
					$fileData->getPrefixedFolderName().'/', $fileData->getFolderName());

				if ( empty( $serverOptions['depth'] ) ) {
					return $status;
				}

				//$status = array_merge($status, $this->getTempFiles($fileData));
					
				$nsId = $fileData->getNamespaceId();
				
				$results = $dbr->query(
					'SELECT page_title, page_latest, page_len, page_touched
					FROM page WHERE page_namespace = '.$nsId.'');

					$titles = array();
					while ( ( $result = $dbr->fetchRow( $results ) ) !== false ) {
						# TODO: Should maybe not be using page_title as URL component, but it's currently what we do elsewhere
						$title = Title::newFromUrl( $result[0] );
						$titles[] = $title;

						$title = $fileData->encodeFileName($title->getText());
						$status[] = $this->createFileResponse(
							$fileData->getPrefixedFolderName().'/'.$title.".mwiki",
							$title.".mwiki", $result[2], "text/x-wiki", null, $result[3]);
					}

					$files = RepoGroup::singleton()->findFiles($titles);
					foreach($files as $name => $file){
						$status[] = $this->createFileResponse(
							$fileData->getPrefixedFolderName().'/'.$name, $name,
							$file->size, $file->mime, null, $file->timestamp);
					}
				return $status;
			} else {
				$status[] = $this->createFolderResponse(
				$fileData->getPrefixedFolderName().'/', $fileData->getFolderName());
					
				//if ( empty( $serverOptions['depth'] ) ) {
				//	return $status;
				//}
				
				//the namespace is given by the upper folder name
				$folderName = explode("/", $fileData->getPrefixedFolderName());
				$folderName = $folderName[count($folderName)-2];
				$nsId = $fileData->getNamespaceId(true, $folderName);
				
				$results = $dbr->query(
					'SELECT page_title, page_latest, page_len, page_touched
					FROM page WHERE page_namespace = '.$nsId.'
					AND page_title = "'.$fileData->getFolderName().'"');

				$titles = array();
				while ( ( $result = $dbr->fetchRow( $results ) ) !== false ) {
					# TODO: Should maybe not be using page_title as URL component, but it's currently what we do elsewhere
					$title = Title::newFromUrl( $result[0] );
					$titles[] = $title;

					$title = $fileData->encodeFileName($result[0]);
					$status[] = $this->createFileResponse(
						$fileData->getPrefixedFolderName().'/'.$title.".mwiki",
						$title.".mwiki",
						$result[2], "text/x-wiki", null, $result[3]);
				}
				
				$status = array_merge($status, $this->getAttachmentFiles($fileData));

				return $status;
			}
		} else { //end of if is directory
			if($fileData->isTempFile()){
				if(file_exists("./extensions/RichMedia/includes/WebDAV/tmp/"
				.$fileData->getFileName())){
					$fileSize = filesize("./extensions/RichMedia/includes/WebDAV/tmp/"
					.$fileData->getFileName());
					$file = urlencode($fileData->getFileName());
					$status[] = $this->createFileResponse(
					$fileData->getPrefixedFolderName()."/".$file, $file,
					$fileSize,
						"unknown/unknown", null, time());
				} else {
					return;
				}
			} else if(!$fileData->isWikiArticle()){
				$file = RepoGroup::singleton()
				->findFile($fileData->getFileName());
				if($file){
					$status[] = $this->createFileResponse(
					$fileData->getPrefixedFolderName().'/'.$fileData->getFileName(),
					$fileData->getFileName(),
					$file->size, $file->mime, null, $file->timestamp);
				} else {
					return;
				}
			} else {
				return;
			}
			$temp = print_r($status, true);
			$this->writeLog("\r\n##\r\n ".$temp."\r\n##\r\n");
			return $status;
		}
	}
	
	private function getAttachmentFiles($fileData){
		$status = array();
		
		$ns = explode("/",$fileData->getPrefixedFolderName());
		$ns = $ns[count($ns)-2];
		$ns = ($ns == "Main") ? "" : $ns.":";
		
		//todo: make this query configurable in the settings
		SMWQueryProcessor::processFunctionParams(array("[[Category:Media]] [[HasRelatedArticle::"
			.$ns.$fileData->getFolderName()."]]")
			,$querystring,$params,$printouts);
		$queryResult = explode("|",
			SMWQueryProcessor::getResultFromQueryString($querystring,$params,
			$printouts, SMW_OUTPUT_WIKI));
		
		unset($queryResult[0]);
		
		foreach($queryResult as $fileName){
			$fileName = substr($fileName, 0, strpos($fileName, "]]"));
			$file = RepoGroup::singleton()->findFile($fileName);
			if($file){
				$status[] = $this->createFileResponse(
					$fileData->getPrefixedFolderName().'/'.$fileName, $fileName,
						$file->size, $file->mime, null, $file->timestamp);
			}
		}
		return $status;
	}

	private function getFileNamespaceFolders(){
		$status = array();
		global $wgRichMediaNamespaceAliases, $wgWebDAVDisplayTalkNamespaces;
		if(isset($wgRichMediaNamespaceAliases)){
			foreach($wgRichMediaNamespaceAliases as $folderName => $dontCare){
				if(!strpos($folderName, "_talk")  || $wgWebDAVDisplayTalkNamespaces){
					$status[] = $this->createFolderResponse("Files/".$folderName."/", $folderName);
				}
			}
		}
		return $status;
	}

	private function getNamespaceFolders(){
		//todo: add namespace blacklist
		global $wgCanonicalNamespaceNames, $wgExtraNamespaces, $wgWebDAVNamespaceBlackList,
		$wgWebDAVDisplayTalkNamespaces;
		$status = array();
		//$flag = "all";
		$namespaces = $wgCanonicalNamespaceNames + $wgExtraNamespaces + array("Main", "Main_talk");
		$namespaces[] = "Files";

		$fileData = new FileData(array("WD_WebDAV.php"));
		foreach($namespaces as $title){
			//todo: provide a flag for this one
			//provide also a black list
			if(!strpos($title, "_talk") || $wgWebDAVDisplayTalkNamespaces){
				if(!$fileData->isFileNamespaceFolder($title)){
					if(!array_key_exists($title, $wgWebDAVNamespaceBlackList)){
						$status[] = $this->createFolderResponse($title.'/', $title);
					}
				}
			}
		}
		return $status;
	}


	function getRawPage() {
		global $wgScript;

		$_SERVER['SCRIPT_URL'] = $wgScript;

		$fileData = new FileData($this->pathComponents);

		//if (!$fileData->isValidGetPath()){
		//	return;
		//}

		$nsId = $fileData->getNamespaceId(false);

		$title = Title::newFromText( $fileData->getFileName(), $nsId);
		if (!isset( $title )) {
			//todo: handle this
			return;
		}

		$mediaWiki = new MediaWiki();
		$article = $mediaWiki->articleFromTitle( $title );

		$rawPage = new RawPage( $article );

		return $rawPage;
	}

	function doGet($pathComponents, $echo = true){
		$temp = implode("/",$pathComponents);
		$this->writeLog("get ".$temp);

		$text = "";
		$fileData = new FileData($pathComponents);
		if($fileData->isWikiArticle()){
			$rawPage = $this->getRawPage();
			if ( !isset( $rawPage ) ) {
				$this->setResponseStatus("404 Not found", false );
				return false;
			}

			if($echo){
				//$rawPage->view();
				echo($text = $rawPage->getRawText());
			} else {
				$text = $rawPage->getRawText();
			}
		} else if($fileData->isTempFile()) {
			if(file_exists("./extensions/RichMedia/includes/WebDAV/tmp/"
					.$fileData->getFileName())){
				$text = file_get_contents("./extensions/RichMedia/includes/WebDAV/tmp/"
					.$fileData->getFileName());
			} else {
				$this->setResponseStatus("404 Not Found");
				return false;
			}

			if($echo){
				echo($text);
			}
		} else {
			$file = RepoGroup::singleton()
				->findFile($fileData->getFileName());
			if($file){
				$fileURL = $file->getFullURL();
				if($echo){
					echo(file_get_contents($fileURL));
				} else {
					$text = file_get_contents($fileURL);
				}
			} else {
				$this->setResponseStatus("404 Not Found");
				return false;
			}
		}
		
		if(!$echo){
			return $text;
		} else {
			return true;
		}
	}

	# RawPage::view handles Content-Type, Cache-Control, etc. and we don't want get_response_helper to overwrite, but MediaWiki doesn't let us get response headers.  It could work if we kept setResponseHeader updated with headers_list on PHP 5.
	function get_wrapper() {
		$result = $this->doGet($this->pathComponents, true);
		if($result === true){
			$this->setResponseStatus("200 OK", false);
		}
	}

	
	
	function head_wrapper() {
		//todo: implement that method
		$temp = implode("/",$this->pathComponents);
		$this->writeLog("head ".$temp);
		
		if ( !$this->userIsAllowed()){
			$this->setResponseStatus("401 Unauthorized", false );
			return;
		}
		
		$getResult = $this->doGet($this->pathComponents, false);
		if($getResult === false){
			return false;
		} else {
			$this->setResponseStatus("200 OK", false);
			return true;
		}
	}

	function delete($serverOptions){
		$result =  $this->doDelete($serverOptions, $this->pathComponents);
		return $result;
	}

	function doDelete($serverOptions, $pathComponents) {
		$temp = implode("/",$pathComponents);
		$this->writeLog("delete ".$temp);

		if ( !$this->userIsAllowed()){
			return "401 Unauthorized";
		}

		$fileData = new FileData($pathComponents);
		if (!$fileData->isValidDeletePath()) {
			return "400 Bad Request";
		}

		$isArticleFolder = $fileData->isArticleFolder();
		if(!$fileData->isDirectory() && 
				($fileData->isFileNamespaceFolder()
				|| $isArticleFolder)){
			if($fileData->isTempFile()){
				if(file_exists("./extensions/RichMedia/includes/WebDAV/tmp/"
						.$fileData->getFileName())){
					unlink("./extensions/RichMedia/includes/WebDAV/tmp/"
						.$fileData->getFileName());
				}
				return true;
			} else {
				$title = Title::newFromText( $fileData->getFileName());
				$file = RepoGroup::singleton()->findFile($title);
				if($file){
					if(!$fileData->isFileNamespaceFolder()){
						$ext = explode(".", $fileData->getFileName());
						$ext = $ext[count($ext)-1];
						global $wgNamespaceByExtension;
						if(array_key_exists($ext, $wgNamespaceByExtension)){
							$nsId = $wgNamespaceByExtension[$ext];
						} else {
							$nsId = NS_IMAGE;
						}
						$title = Title::newFromText($fileData->getFileName(), $nsId);
						
						$this->setRichMediaMapping($fileData->getFileName());
						
						$ns = explode("/",$fileData->getPrefixedFolderName());
						$ns = $ns[count($ns)-2];
						$ns = ($ns == "Main") ? "" : $ns.":";
						$wdTP = new WDTemplateParser($title, "delete", 
							$ns.$fileData->getFolderName());
						
						$article = new Article($title);
						$article->doEdit($wdTP->getNewArticleTextForDelete(),
							"Edited by the WebDAV extension");
					
						return true;
					} else {
						$file->delete("Deleted via the WebDAV extension");
					}
				}
			}
		}

		$nsId = $fileData->getNamespaceId(false);
		$title = "";
		if($fileData->isDirectory()){
			$title = $fileData->getFolderName();
		} else {
			$title = $fileData->getFileName();
		}
		$title = Title::newFromText($title, $nsId);
		

		if (!isset( $title )) {
			//does not matter that the article does not exist
			return true;
		}
		
		if (!$title->exists()) {
			//does not matter that the article does not exist
			return true;
		}

		$mediaWiki = new MediaWiki();
		$article = $mediaWiki->articleFromTitle( $title );

		$articleId = $title->getArticleID( GAID_FOR_UPDATE );
		$article->doDeleteArticle("Deleted via WebDAV", true, $articleId);

		return true;
	}

	//todo: implement this method
	private function userIsAllowed(){
		//todo: implement a login mechanism and remove this code
		global $wgUser;
		
		if ( !$wgUser->isAllowed( 'edit' ) ) {
			$this->setResponseStatus( '401 Unauthorized' );
			return false;
		}

		if ( !$wgUser->isAllowed( 'delete' ) ) {
			$this->setResponseStatus( '401 Unauthorized' );
			return false;
		}

		if ( wfReadOnly() ) {
			$this->setResponseStatus( "401 Unauthorized" );
			return false;
		}
		return true;
	}

	private function getPostedFileContent(){
		if ( ( $handle = $this->openRequestBody() ) === false ) {
			return null;
		}

		$text = "";
		while($buffer = fread($handle, 1024)){
			$text .= $buffer;
		}
		return $text;
	}

	private function uploadTempFile($title, $text){
		//create temporary file
		file_put_contents("./extensions/RichMedia/includes/WebDAV/tmp/".$title, $text);
		return true;
	}
	
	private function setRichMediaMapping($title){
		$ext = explode(".", $title);
		$ext = $ext[count($ext)-1];
		global $wgNamespaceByExtension;
		if(array_key_exists($ext, $wgNamespaceByExtension)){
			$nsId = $wgNamespaceByExtension[$ext];
		} else {
			$nsId = NS_IMAGE;
		}
		global $wgWebDAVRichMediaMapping;
		$wgWebDAVRichMediaMapping = $wgWebDAVRichMediaMapping[$nsId];
	}

	private function uploadFile($fileData, $text, $relatedArticleTitle){
		$title = $fileData->getFileName();
		file_put_contents("./extensions/RichMedia/includes/WebDAV/tmp/tempfile", $text);
		$local = wfLocalFile($title);
		
		$this->setRichMediaMapping($title);
		
		//get articles that are also related to that file
		$relatedArticleTitles = "";
		if($local->title->exists()){
			$wdTP = new WDTemplateParser($local->title, "put");
			$relatedArticleTitles = $wdTP->getRelatedArticles();
		}
		
		//upload file
		$status = $local->upload(
			"./extensions/RichMedia/includes/WebDAV/tmp/tempfile"
			, "Created via the WebDAV extension", "",
			File::DELETE_SOURCE);
		
		$local->load();
		
		global $smwgEnableUploadConverter, $smwgRMIP;
		$text = "";
		if($smwgEnableUploadConverter){
			$text = UploadConverter::getFileContent($local);
		}
		
		//concat relatedArticlesString
		if(strlen($relatedArticleTitles) > 0){
			global $wgWebDAVRichMediaMapping;
			$delimiter = $wgWebDAVRichMediaMapping["Delimiter"];
			$found = false;
			
			$relatedArticleTitles = explode($delimiter, $relatedArticleTitles);
			foreach($relatedArticleTitles as $article){
				if(trim($article) == $relatedArticleTitle){
					$found = true;
				}
			}
			if(!$found){
				$relatedArticleTitles[] = $relatedArticleTitle;
			}
			$relatedArticleTitles = implode($delimiter, $relatedArticleTitles);
		} else {
			$relatedArticleTitles = $relatedArticleTitle;
		}
		
		$text = $this->createRichMediaTemplate(
			$local->title->getText()
			, $relatedArticleTitles, $local->getUser(), 
			wfTimestamp(TS_MW, $local->getTimestamp()))
			."\n".$text;

		if(strlen(trim($text)) > 0){
			$article = new Article($local->title);
			$article->doEdit(
				$text, "Uploaded via WebDAV extension.");
		}


		// todo:error handling
		return true;
	}
	
	private function createRichMediaTemplate($fileName, $relatedArticleTitles,
			$uploader, $uploadDate){
		$delimiter = $wgWebDAVRichMediaMapping["Delimiter"];
		global $wgWebDAVRichMediaMapping;
		$result = "{{".$wgWebDAVRichMediaMapping["TemplateName"]."\n";
		$result .= "|".$wgWebDAVRichMediaMapping["Filename"]."=".$fileName."\n";
		$result .= "|".$wgWebDAVRichMediaMapping["RelatedArticles"]."="
			.$relatedArticleTitles."\n";
		$result .= "|".$wgWebDAVRichMediaMapping["Uploader"]."="
			."User:".$uploader."\n";
		$result .= "|".$wgWebDAVRichMediaMapping["UploadDate"]."="
			.$uploadDate."\n";
		$result .= "}}\n";
		return $result;
	}

	function move($serverOptions){
		$temp = implode("/",$this->pathComponents);
		$this->writeLog("move ".$temp);

		$status = $this->doCopy($serverOptions);
		$status = $this->doDelete($serverOptions, $this->pathComponents);

		$this->writeLog("move finished");
		return $status;
	}

	function copy($serverOptions) {
		return $this->doCopy($serverOptions);
	}

	function doCopy($serverOptions) {
		//todo deal with copy response i.e. no timestamp and no content length
		//todo; check source AND destination
		if(!$this->userIsAllowed())
		return;

		$text = $this->doGet($this->pathComponents, false);

		$destination = $serverOptions["destinationUrlComponents"]["pathComponents"];
		array_shift($destination);
		array_shift($destination);
		array_shift($destination);
		array_shift($destination);
		array_shift($destination);

		return $this->doPut($serverOptions, $destination, $text);
	}

	function put( $serverOptions){
		$result = $this->doPut($serverOptions, $this->pathComponents);
		return $result;
	}

	function doPut($serverOptions, $pathComponents, $text = null) {
		$temp = implode("/",$pathComponents);
		$this->writeLog("put ".$temp);

		if(!$this->userIsAllowed())
			return "401 Unauthorized";

		$fileData = new FileData($pathComponents);
		if (!$fileData->isValidPutPath()) {
			return "400 Bad Request";
		}

		$nsId = $fileData->getNamespaceId(false);
		
		if(is_null($text)){
			if(!$fileData->isDirectory()){
				$text = $this->getPostedFileContent();
				if(!$text){
					return "400 Bad Request";
				}
			} else {
				$text = "";
			}
		}

		$isArticleFolder = $fileData->isArticleFolder();
		if(!$fileData->isDirectory() && 
			($fileData->isFileNamespaceFolder()
				|| $isArticleFolder)){
			if(!$fileData->isWikiArticle()){
				if($fileData->isTempFile()){
					$this->uploadTempFile($fileData->getFileName(), $text);
				} else {
					$relatedArticleTitle = "";
					if($isArticleFolder){
						$ns = explode("/",$fileData->getPrefixedFolderName());
						$ns = $ns[count($ns)-2];
						$ns = ($ns == "Main") ? "" : $ns.":";
						$relatedArticleTitle = $ns.$fileData->getFolderName();
					}
					 
					$this->uploadFile($fileData, $text, $relatedArticleTitle);
				}
				return true;
			}
		}
		
		$title = "";
		if($fileData->isDirectory()){
			$title = $fileData->getFolderName();
		} else {
			$title = $fileData->getFileName();
		}
		
		$title = Title::newFromText($title, $nsId);
		if (!isset( $title )) {
			return "400 Bad Request";
		}
		
		if ( !$title->exists() && !$title->userCan( 'create' ) ) {
			$this->setResponseStatus( '401 Unauthorized' );
			return;
		}

		$mediaWiki = new MediaWiki();
		$article = $mediaWiki->articleFromTitle( $title );

		$article->doEdit( $text, "Uploaded via WebDAV" );
		return true;
	}

	function search( &$serverOptions ) {
		$serverOptions['namespaces']['http://subversion.tigris.org/xmlns/dav/'] = 'V';

		$status = array();

		$search = SearchEngine::create();

		# TODO: Use (int)$wgUser->getOption( 'searchlimit' );
		$search->setLimitOffset( MW_SEARCH_LIMIT );

		$results = $search->searchText( $serverOptions['xpath']->evaluate( 'string(/D:searchrequest/D:basicsearch/D:where/D:contains)' ) );

		while ( ( $result = $results->next() ) !== false ) {
			$title = $result->getTitle();
			$revision = Revision::newFromTitle( $title );

			$response = array();
			$response['path'] = 'WD_WebDAV.php/' . $title->getPrefixedUrl();
			$response['props'][] = WebDavServer::mkprop( 'checked-in', $this->getUrl( array( 'path' => 'deltav.php/ver/' . $revision->getId() ) ) );
			$response['props'][] = WebDavServer::mkprop( 'displayname', $title->getText() );
			$response['props'][] = WebDavServer::mkprop( 'getcontentlength', $revision->getSize() );
			$response['props'][] = WebDavServer::mkprop( 'getcontenttype', 'text/x-wiki' );
			$response['props'][] = WebDavServer::mkprop( 'getlastmodified', wfTimestamp( TS_UNIX, $revision->mTimestamp ) );
			$response['props'][] = WebDavServer::mkprop( 'resourcetype', null );
			$response['props'][] = WebDavServer::mkprop( 'version-controlled-configuration', $this->getUrl( array( 'path' => 'deltav.php/vcc/default' ) ) );

			$response['props'][] = WebDavServer::mkprop( 'http://subversion.tigris.org/xmlns/dav/', 'baseline-relative-path', $title->getFullUrl() );
			$response['score'] = $result->getScore();

			$status[] = $response;
		}

		# TODO: Check if we exceed our limit
		#$response = array();
		#$response['status'] = '507 Insufficient Storage';

		#$status[] = $response;

		return $status;
	}

	private function getTempFiles($fileData){
		$files = scandir("./extensions/RichMedia/includes/WebDAV/tmp/");
		//
		$status = array();
		foreach($files as $file){
			if($file != "." && $file != ".."){
				$fileSize = filesize("./extensions/RichMedia/includes/WebDAV/tmp/".$file);
				$file = urlencode($file);
				$status[] = $this->createFileResponse(
				$fileData->getPrefixedFolderName()."/".$file, $file,
				$fileSize,
						"unknown/unknown", null, time());
			}
		}
		return $status;
	}

	public function writeLog($text){
		if(false){
			if(is_null($this->time)){
				$this->time = time();
			}

			$temp = @file_get_contents("d:\\webdav-log.txt");
			file_put_contents("d:\\webdav-log.txt", $temp."\r\n"
			.$this->time."  ".$text);
		}
	}
	
	public function mkcol($options){
		return $this->put($options);
	}
	
	public function checkAuth($authType, $user, $password){
		if(strlen($user) == 0){
			return false;
		}
		
		$tmpUser = User::newFromName($user);
		if(!is_null($tmpUser)){
			if($tmpUser->getId() != 0){
				if($tmpUser->checkPassword($password)){
					global $wgUser;
					$wgUser = $tmpUser;
					return true;
				}
			}
		}
		global $wdGrantAnonymousAccess;
		if(strtolower($user) == "anonymous" && $wdGrantAnonymousAccess){
			return true;
		}
		return false;
	}
}