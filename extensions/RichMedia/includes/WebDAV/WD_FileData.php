<?php

/*
 * @author Ingo Steinbauer
 */

class FileData {

	private $folderName = "";
	private $fileName = "";
	private $isEmpty = false;
	private $folderNamePrefix = "";
	private $namespaceName = "";

	function __construct($pathComponents){
		if(empty($pathComponents)){
			//todo: this is an error
			$this->$isEmpty = true;
			return $this;
		}

		$pathComponent = array_shift($pathComponents);
		if(strtolower($pathComponent) != 'wd_webdav.php'){
			//todo: this is an error
			$this->isEmpty = true;
			return $this;
		}

		if(!empty($pathComponents)) {
			$this->folderName = array_shift($pathComponents);
		}
		
		while(!empty($pathComponents)){
			$pathComponent = array_shift($pathComponents);
			
			
			if($this->isFileNamespaceFolder($pathComponent)
					|| $this->isAllFilesNamespaceFolder($pathComponent)
					|| $this->isNamespaceFolder($pathComponent)
					|| $this->folderName == "Files"){
				$this->folderNamePrefix .= $this->folderName."/";
				$this->folderName = $pathComponent;
			} else if ($this->isNamespaceFolder($this->folderName) 
					&& !$this->isWikiArticle($pathComponent)){
				$this->namespaceName = $this->folderName;
				$this->folderNamePrefix .= $this->folderName."/";
				$this->folderName = $pathComponent;
			} else {
				$this->fileName = $pathComponent;
			}
		}

		return $this;
	}

	public function isRootDirectory(){
		if($this->folderName == ""){
			return true;
		} else {
			return false;
		}
	}

	public function getFolderName(){
		return $this->folderName;
	}
	
	public function getPrefixedFolderName(){
		return $this->folderNamePrefix.$this->folderName;
	}

	public function getFileName(){
		if($this->isWikiArticle()){
			return urldecode($this->decodeFileName(substr($this->fileName, 0, strlen($this->fileName)-6)));
		} else {
			return urldecode($this->decodeFileName($this->fileName));
		}
	}

	public function isWikiArticle($fileName = null){
		if(is_null($fileName)){
			$fileName = $this->fileName;
		}
		
		$ext = explode(".", $fileName);
		if($ext[count($ext)-1] == "mwiki"){
			return true;
		}
		return false;
	}

	public function isFileNamespaceFolder($folderName = null){
		if(is_null($folderName)){
			$folderName = $this->folderName;
		}

		global $smwgEnableRichMedia;
		if($smwgEnableRichMedia){
			if($folderName == "All"){
				return true;
			}
			
			$isFileNS = false;
			RMNamespace::isImage($this->getNamespaceId(true, $folderName), $isFileNS);
			return $isFileNS;
		} else {
			//todo: implement this method
			if($folderName == "File"){
				return true;
			} else if ($folderName == 'All'){
				return true;
			}
			return false;
		}
	}
	
	public function isAllFilesNamespaceFolder($folderName = null){
		if(is_null($folderName)){
			$folderName = $this->folderName;
		}

		//todo: deal with image and media namespace
		if($folderName == "All"){
			return true;
		}
		return false;
	}

	public function isRootFileNamespaceFolder($folderName = null){
		if(is_null($folderName)){
			$folderName = $this->folderName;
		}

		//todo: deal with image and media namespace
		if($folderName == "Files"){
			return true;
		}
		return false;
	}

	public function getNamespaceId($isFolder = true, $folderName = null, $fileName = null){
		if(is_null($folderName)){
			if(strlen($this->namespaceName) > 0){
				$folderName = $this->namespaceName;
			} else {
				$folderName = $this->folderName;
			}
		}
		
		global $wgNamespaceAliases, $wgContLang;
			
		if(array_key_exists($folderName, $wgNamespaceAliases)){
			$nsId = $wgNamespaceAliases[$folderName];
		} else if($wgContLang->getNsIndex($folderName)){
			$nsId = $wgContLang->getNsIndex($folderName);
		} else if ($folderName == "Main"){
			$nsId = 0;
		} else if ($folderName == "Main_talk"){
			$nsId = 1;
		} else if($folderName == "All" && !$isFolder){
			if(is_null($fileName)){
				$fileName = $this->getFileName();
			}
			
			$nsId = $this->getNamespaceIdBasedOnFileExtension($fileName);
		} else {
			$nsId = $folderName;
		}
		return $nsId;
	}
	
	public function getNamespaceIdBasedOnFileExtension($fileName){
		$nsId = NS_FILE;
		$ext = explode(".", $fileName);
		$ext = $ext[count($ext) -1 ];
		
		global $wgNamespaceByExtension;
		if(array_key_exists(strtolower($ext), $wgNamespaceByExtension)){
			$nsId = $wgNamespaceByExtension[$ext];
		}
		
		return $nsId;
	}
	
	public function decodeFileName($fileName){
		$fileName = str_replace("-colon-",':', $fileName);
		$fileName = str_replace("-asterisk-",'*', $fileName);
		$fileName = str_replace("-slash-", '/', $fileName);
		$fileName = str_replace("-quote-", '"', $fileName);
		$fileName = str_replace("-ampersize-", "&", $fileName);
		$fileName = str_replace("-apostroph-", "'", $fileName);
		$fileName = str_replace("-percent-", "%", $fileName);
		return $fileName;
	}
	
	public function encodeFileName($fileName){
		$fileName = str_replace(':', "-colon-", $fileName);
		$fileName = str_replace('*', "-asterisk-", $fileName);
		$fileName = str_replace('/', "-slash-", $fileName);
		$fileName = str_replace('"', "-quote-", $fileName);
		$fileName = str_replace('&', "-ampersize-", $fileName);
		$fileName = str_replace('%', "-percent-", $fileName);
		$fileName = str_replace("'", "-apostroph-", $fileName);
		return urlencode($fileName);
	}
	
	public function isTempFile($fileName = null){
		if(is_null($fileName))
			$fileName = $this->getFileName();
		
		
		if(strpos($fileName, "~") == 0 && strpos($fileName, "~") !== false){
			return true;
		}
		
		return false;
	}
	
	public function isDirectory(){
		if(strlen($this->getFileName()) == 0){
			return true;
		}
		return false;
	}
	
	public function isNamespaceFolder($folderName = null, $ignoreFolderNamePrefix = false){
		if(is_null($folderName)){
			$folderName = $this->folderName;
		}
		if($folderName != "Files"){
			if(!$this->isFileNamespaceFolder($folderName)){
				if(strlen($this->folderNamePrefix) == 0
						|| $ignoreFolderNamePrefix){
					global $wgWebDAVDisplayTalkNamespaces;
					if(!strpos($folderName, "_talk") 
							|| $wgWebDAVDisplayTalkNamespaces){
						global $wgWebDAVNamespaceBlackList;
						if(!array_key_exists($folderName, 
								$wgWebDAVNamespaceBlackList)){
							global $wgCanonicalNamespaceNames, $wgExtraNamespaces, 
								$wgWebDAVNamespaceBlackList;
							$namespaces = 
								$wgCanonicalNamespaceNames + $wgExtraNamespaces 
								+ array("Main", "Main_talk");
							$namespaces = array_flip($namespaces);
							if(array_key_exists($folderName, $namespaces)){
								return true;
							}
						}
					}
				}
			}
		}
		return false;
	}
	
	public function isArticleFolder(){
		$folderName = explode("/", $this->folderNamePrefix);
		if(array_key_exists(count($folderName)-2, $folderName)){
			return $this->isNamespaceFolder($folderName[count($folderName)-2], true);
		}
		return false;	
	}
	
	private function getFolderPathType(){
		//todo: define constants
		if(!$this->isEmpty){
			if($this->isRootDirectory()){
				return "root";
			} else {
				$path = explode("/",$this->folderNamePrefix.$this->folderName);
				if($this->isNamespaceFolder($path[0], true)){
					if(count($path) == 1){
						return "namespace";
					} else if(count($path) == 2 && !$this->isDirectory()){
						$nsId = $this->getNamespaceId(true, $path[0]);
						$title = Title::newFromText($path[1], $nsId);
						if($title->exists()){
							return "article";
						}
					} else if(count($path) == 2 && $this->isDirectory()){
						return "article";
					}
				} else if ($path[0] == "Files"){
					if(count($path) == 1){
						return "files";
					} else if(count($path) == 2){
						if($this->isFileNamespaceFolder($path[1])){
							return "filenamespace";
						}
					}
				}
			}
		}
		return "invalid";
	}
	
	public function isValidPropfindPath(){
		$folderPathType = $this->getFolderPathType();
		if($folderPathType === "invalid"){
			return false;
		}
		return true;
	}
	
	public function isValidPutPath(){
		$folderPathType = $this->getFolderPathType();
		if($folderPathType == "invalid"){
			return false;
		} else if($folderPathType == "root"
				|| $folderPathType == "files"){
					return false;
		} else if($folderPathType == "article" && $this->isWikiArticle()){
			return $this->folderName.".mwiki" == $this->fileName;
		} else if($folderPathType == "article" && $this->isDirectory()){
			return true;
		}
		return true;
	}
	
	public function isValidDeletePath(){
		$folderPathType = $this->getFolderPathType();
		if($folderPathType == "invalid"){
			return false;
		} else if($folderPathType == "root"
				|| $folderPathType == "files"){
					return false;
		} 
		return true;
	}
}