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

	function init() {
		parent::init();

		# Prepend script path component to path components
		array_unshift( $this->pathComponents, array_pop( $this->baseUrlComponents['pathComponents'] ) );
	}

	function getAllowedMethods() {
		//todo: add copy and move method
		// i removed the REPORT method
		return array( 'OPTIONS', 'PROPFIND', 'GET', 'HEAD', 'DELETE', 'PUT', 'SEARCH', 'COPY', 'MOVE');
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
		$serverOptions['namespaces']['http://subversion.tigris.org/xmlns/dav/'] = 'V';
		$status = array();
		
		//todo: login stuff
		
		$fileData = new FileData($this->pathComponents);
		
		if(!$fileData->isValidFolder())
			return;
		
		# TODO: Use $wgMemc
		# todo: deal with table prefixes
		# deal with that in the complete class
		$dbr =& wfGetDB( DB_SLAVE );
		
		if ($fileData->isRootDirectory()) {
			global $wgSitename;
			$status[] = $this->createFolderResponse("", $wgSitename);
			
			# Don't descend if depth is zero
			//todo: what does this one do?
			if ( empty( $serverOptions['depth'] ) ) {
				return $status;
			}
			
			$status = array_merge($status, $this->getNamespaceFolders());
			
			return $status;
		} else if($fileData->isRootFileNamespaceFolder()){
			$status[] = $this->createFolderResponse(
				$fileData->getPrefixedFolderName().'/', $fileData->getFolderName());
			
			$status = array_merge($status, $this->getFileNamespaceFolders());
			
			$status[] = $this->createFolderResponse(
				$fileData->getPrefixedFolderName().'/'."File", "File");
			
			$status[] = $this->createFolderResponse(
				$fileData->getPrefixedFolderName().'/'."All", "All");	
			
			return $status;
		} else if ($fileData->isAllFilesNamespaceFolder()){
			$status[] = $this->createFolderResponse(
				$fileData->getPrefixedFolderName().'/', $fileData->getFolderName());
			
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
		} else {
			$status[] = $this->createFolderResponse(
				$fileData->getPrefixedFolderName().'/', $fileData->getFolderName());
			
			//todo: auch hier diese depth abfrage einbauen?
			
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
				$status[] = $this->createFileResponse(
					$fileData->getPrefixedFolderName().'/'.$title.".mwiki", 
					$title.".mwiki",
					$result[2], "text/x-wiki", null, $result[3]);
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
		}
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
		
		if (!$fileData->isValidFile()){
			return;
		}
		
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
		$text = "";
		$fileData = new FileData($pathComponents);
		if($fileData->isWikiArticle()){
			$rawPage = $this->getRawPage();
			if ( !isset( $rawPage ) ) {
				$this->setResponseStatus( false, false );
				return;
			}
			
			if($echo){
				//$rawPage->view();
				$rawPage->view();
			} else {
				$text = $rawPage->getRawText();
			}
		} else {
			$fileURL = RepoGroup::singleton()
				->findFile($fileData->getFileName())->getFullURL();
			if($echo){
				echo(file_get_contents($fileURL));
			} else {
				$text = file_get_contents($fileURL);
			}
		}
		return $text;
	}

	# RawPage::view handles Content-Type, Cache-Control, etc. and we don't want get_response_helper to overwrite, but MediaWiki doesn't let us get response headers.  It could work if we kept setResponseHeader updated with headers_list on PHP 5.
	function get_wrapper() {
		$this->doGet($this->pathComponents, true);
	}

	function head_wrapper() {
		$rawPage = $this->getRawPage();
		if ( !isset( $rawPage ) ) {
			$this->setResponseStatus( false, false );
			return;
		}
		
		if ( !isset( $rawText ) ) {
			$this->setResponseStatus( false, false );
			return;
		}

		# TODO: Does MediaWiki handle HEAD requests specially?
		ob_start();
		$rawPage->view();
		ob_end_clean();
	}

	function delete($serverOptions){
		return $this->doDelete($serverOptions, $this->pathComponents);
	}
	
	function doDelete($serverOptions, $pathComponents) {
		if ( !$this->userIsAllowed())
			return;
		
		$fileData = new FileData($pathComponents);
		if (!$fileData->isValidFile()) {
			return;
		}
		
		if($fileData->isFileNamespaceFolder()){
			if(!$fileData->isWikiArticle()){
				$title = Title::newFromText( $fileData->getFileName());
				$file = RepoGroup::singleton()->findFile($title);
				$file->delete("via webdav");
				return true;
			}
		}

		$nsId = $fileData->getNamespaceId(false);
			
		$title = Title::newFromText( $fileData->getFileName(), $nsId);
		
		if (!isset( $title )) {
			//todo:error handling
			return;
		}
		
		$mediaWiki = new MediaWiki();
		$article = $mediaWiki->articleFromTitle( $title );

		# Must check if article exists to avoid 500 Internal Server Error

		$articleId = $title->getArticleID( GAID_FOR_UPDATE );
		$article->doDeleteArticle("Deleted via WebDAV", true, $articleId);
		
		return true;
	}
	
	//todo: implement this method
	private function userIsAllowed(){
		//todo: implement a login mechanism and remove this code
		global $wgUser;
		$wgUser = User::newFromName("WikiSysop");
		
		if ( !$wgUser->isAllowed( 'edit' ) ) {
			$this->setResponseStatus( '401 Unauthorized' );
			return false;
		}
		
	if ( !$wgUser->isAllowed( 'delete' ) ) {
			$this->setResponseStatus( '401 Unauthorized' );
			return false;
		}
		
		if ( wfReadOnly() ) {
			$this->setResponseStatus( '403 Forbidden' );
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
	
	private function uploadFile($title, $text){
		//create temporary file
		file_put_contents("tempfile", $text);
		
		//upload file
		$local = wfLocalFile($title);
		
		$status = $local->upload(
			"tempfile", "created via webdav", "",
			File::DELETE_SOURCE);
		
		global $smwgEnableUploadConverter, $smwgRMIP;
		if($smwgEnableUploadConverter){
			$local->load();
			$text = UploadConverter::getFileContent($local);
			
			$article = new Article($local->title);
			$article->doEdit(
				$text, "uploaded via webdav");
		}
		
		
		// todo metadata
		// todo:error handling
		return true;
	}
	
	function move($serverOptions){
		$status = $this->doCopy($serverOptions);
		$status = $this->doDelete($serverOptions, $this->pathComponents);
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
		
		return $this->doPut($serverOptions, $destination, $text);
	}
	
	function put( $serverOptions){
		return $this->doPut($serverOptions, $this->pathComponents);
	}
	
	function doPut($serverOptions, $pathComponents, $text = null) {
		if(!$this->userIsAllowed()) 
			return; 
		
		
		$fileData = new FileData($pathComponents);
		if (!$fileData->isValidFile()) {
			return;
		}
		
		$nsId = $fileData->getNamespaceId(false);
		
		if(is_null($text)){
			$text = $this->getPostedFileContent();
			if(!$text){
				//todo: failure
				return;
			}
		}
		
		if($fileData->isFileNamespaceFolder()){
			if(!$fileData->isWikiArticle()){
				$this->uploadFile($fileData->getFileName(), $text);
				return true;
			}
		}
		
		$title = Title::newFromText(
				$fileData->getFileName(), $nsId);
		if (!isset( $title )) {
			// todo: what does this do I added the return statement instead
			return;
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
}
