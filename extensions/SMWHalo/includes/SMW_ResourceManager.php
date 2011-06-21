<?php
/**
 * @file
 * @ingroup SMWHaloMiscellaneous
 * 
 * Created on 09.07.2007
 *
 * @author: Kai K�hn
 */
 
 /**
  * SMWResourceManager allows registering of scripts under certain conditions.
  * For example: Load script xy only if user is in edit mode on attribute pages.
  */
 class SMWResourceManager {
 	private static $singleton;
 	private $scripts; // registered scripts
 	private $css; // registered css
 	
 	// state
 	private $action; // current action: view, edit, preview, submit, ....
 	private $namespace; // namespace of page
 	private $page; // page title (as string)
 	
 	public static function SINGLETON() {
 		if (SMWResourceManager::$singleton == NULL) {
 			SMWResourceManager::$singleton = new SMWResourceManager();
 		}
 		return SMWResourceManager::$singleton;
 	} 
 	
 	private function SMWResourceManager() {
 		global $wgRequest, $wgTitle;
 		$this->scripts = array();
 		$this->css = array();
 		
 		// read state
 		if ($wgRequest != NULL && $wgTitle != NULL) { 
 			$action = $wgRequest->getVal("action");
 			// $action of NULL or '' means view mode
 			$this->action = $action == NULL || $action == '' ? "view" : $action;
 			$this->namespace = $wgTitle->getNamespace();
 			$this->page = $wgTitle->getNamespace().":".$wgTitle->getText();
 			
 		} else { // if no state could be read, set default -> load all!
 			$this->action = "all";
 			$this->namespace = -1;
 			$this->page = array();
 		}
 	}
 	
 	/**
 	 * Adds a script under certain conditions.
 	 * 
 	 * @param $path Path of script
 	 * @param $action "all", "edit", "view", etc..
 	 * @param $namespace NS_CATEGORY, SMW_NS_ATTRIBUTE, ... or -1 for all namespaces
 	 * @param $pages A single page name or an array of page names (strings).
 	 */
 	public function addScriptIf($path, $action = "all", $namespace = -1, $pages = array()) {
 		if ($path == NULL) return;
 		
 		$res = new ResourceEntry();
 		$res->path = $path;
 		$res->action = $action;
 		$res->namespace = $namespace;
 		$res->pages = $pages;
 		$res->id = NULL;
 		$this->scripts[] = $res;
 	}
 	
 	/**
 	 * Adds CSS under certain conditions.
 	 * 
 	 * @param $path Path of script
 	 * @param $action "all", "edit", "view", etc..
 	 * @param $namespace NS_CATEGORY, SMW_NS_ATTRIBUTE, ... or -1 for all namespaces
 	 * @param $pages A single page name or an array of page names (strings).
 	 */
 	public function addCSSIf($path, $action = "all", $namespace = -1, $pages = array()) {
 		if ($path == NULL) return;
 		
 		$res = new ResourceEntry();
 		$res->path = $path;
 		$res->action = $action;
 		$res->namespace = $namespace;
 		$res->pages = $pages;
 		$res->id = NULL;
 		$this->css[] = $res;
 	}
 	
 	/**
 	 * Serialize script tags into $out OutputPage. Makes sure that
 	 * no script is serialized more than once.
 	 * 
 	 * @param $out OutputPage
 	 */
 	public function serializeScripts(& $out) {
        global $smwgHaloStyleVersion;
 		$alreadyRegistered = array();
 		foreach($this->scripts as $script) {
 			if ($this->matchesAction($script) 
 					&& $this->matchesNamespace($script) 
 					&& $this->matchesPage($script) 
 					&& !array_key_exists($script->path, $alreadyRegistered)) {
 				$id = $script->id != NULL ? "id=\"".$script->id."\"" : "";
 				$out->addScript('<script type="text/javascript" '.$id.' src="'.$script->path.$smwgHaloStyleVersion.'"></script>');
 				$alreadyRegistered[$script->path] = 1;
 			}
 		}
 	}
 	
 	/**
 	 * Serialize CSS into $out OutputPage. Makes sure that
 	 * no CSS is serialized more than once.
 	 * 
 	 * @param $out OutputPage
 	 */
 	public function serializeCSS(& $out) {
        global $smwgHaloStyleVersion;
 		$alreadyRegistered = array();
 		foreach($this->css as $css) {
 			if ($this->matchesAction($css) && $this->matchesNamespace($css) && $this->matchesPage($css) && !array_key_exists($css->path, $alreadyRegistered)) {
 				$out->addStyle($css->path.$smwgHaloStyleVersion, 'screen, projection');
 				$alreadyRegistered[$css->path] = 1;
 			}
 		}
 	}
 	
 	/**
 	 * Sets the ID of the given script.
 	 */
 	public function setScriptID($path, $id) {
 		foreach($this->scripts as $script) {
 			if ($script->path == $path) { 
 				$script->id = $id;
 			}
 		}
 	}
 	
 	private function matchesAction($res) {
 		return $res->action == $this->action || $res->action == "all";
 	}
 	
 	private function matchesNamespace($res) {
 		return $res->namespace == $this->namespace || $res->namespace == -1;
 	}
 	
 	private function matchesPage($res) {
 		if (is_array($res->pages)) {
 			return in_array($this->page, $res->pages) || empty($res->pages);
 		}
 		return $this->page == $res->pages;
 	}
 }
 
 class ResourceEntry {
 	public $path;
 	public $action;
 	public $namespace;
 	public $pages;
 	public $id;
 }

