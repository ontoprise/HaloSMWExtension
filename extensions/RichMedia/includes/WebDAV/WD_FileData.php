<?php
class FileData {

	private $folderName = "";
	private $fileName = "";
	private $isEmpty = false;
	private $folderNamePrefix = "";

	function __construct($pathComponents){
		if(empty($pathComponents)){
			//todo: this is an error
			$this->$isEmpty = true;
			return $this;
		}

		$pathComponent = array_shift($pathComponents);
		if($pathComponent != 'WD_WebDAV.php'){
			//todo: this is an error
			$this->isEmpty = true;
			return $this;
		}

		if(!empty($pathComponents)) {
			$this->folderName = array_shift($pathComponents);
		}
		
		while(!empty($pathComponents)){
			$pathComponent = array_shift($pathComponents);
			//todo: implement a is special folder method
			if($this->isFileNamespaceFolder($pathComponent)
					|| $this->isAllFilesNamespaceFolder($pathComponent)){
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
			return substr($this->fileName, 0, strlen($this->fileName)-6);
		} else {
			return $this->fileName;
		}
	}

	public function isWikiArticle(){
		$ext = explode(".", $this->fileName);
		if($ext[count($ext)-1] == "mwiki"){
			return true;
		}
		return false;
	}

	public function isValidFile(){
		return $this->folderName != "" && $this->fileName != "" && !$this->isEmpty;
	}

	public function isValidFolder(){
		return !$this->isEmpty;
	}

	public function isFileNamespaceFolder($folderName = null){
		if(is_null($folderName)){
			$folderName = $this->folderName;
		}

		global $smwgEnableRichMedia;
		if($smwgEnableRichMedia){
			$isFileNS = false;
			RMNamespace::isImage($this->getNamespaceId($folderName), $isFileNS);
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

	public function getNamespaceId($folderName = null){
		if(is_null($folderName)){
			$folderName = $this->folderName;
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
		} else {
			$nsId = $folderName;
		}
		
		return $nsId;
	}
}