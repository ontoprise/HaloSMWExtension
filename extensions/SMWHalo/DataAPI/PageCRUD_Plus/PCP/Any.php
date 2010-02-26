<?php

/**
 * @file
  * @ingroup DAPCP
  *
  * @author Dian
 */

/**
 * Describes the functions which the SMW Edit API should support.
 *
 * @author  Dian
 * @version 0.1
 *
 */
abstract class PCPAny{
	/**
	 * The current user credentials.
	 *
	 * @var PCPUserCredentials
	 */
	protected $usedUC = NULL;

	/**
	 * The current page object.
	 *
	 * @var PCPPage
	 */
	protected $page = NULL;

	/**
	 * The login action with placeholders for username and password.<br/>0 - login name <br/>1 - password
	 *
	 * @var hashmap
	 */
	public $lgQuery = array(
	"action" => "login",
	"lgname"=> ":0",
	"lgpassword"=> ":1"		
	);

	/**
	 * The logout action.
	 *
	 * @var hashmap
	 */
	public $lgQueryLogout = array(
	"action" => "logout"
	);

	/**
	 * The query for requesting an edit token with a placeholder for the page title.<br/>0 - page title
	 *
	 * @var hashmap
	 */
	public $queryEditToken = array(
	"action" => "query",
	"prop" => "info|revisions",
	"intoken" => "edit",
	"titles" => ":0"
	);

	/**
	 * The query for creating a page with placeholders.<br/>0 - page title <br/>1 - edit token <br/>2 - text <br/>3 - summary (the edit comment)
	 * @var hashmap
	 */
	public $queryCreatePage = array(
	"action" => "edit",	
	"title" => ":0",
	"token" => ":1",
	"text" => ":2",
	"summary" => ":3",
	"createonly" => "true"
	);

	/**
	 * The query for reading a page with placeholders.<br/>0 - page title
	 * Only the last 10 revisions will be loaded.
	 * @var hashmap
	 */
	public $queryReadPage = array(
	"action" => "query",	
	"prop" => "info|revisions",
	"titles" => ":0",
	"rvprop" => "timestamp|content|ids",
	"rvlimit" => "max"
	);

	/**
	 * The query for updating a page with placeholders.<br/>0 - page title <br/>1 - edit token <br/>2 - text <br/>3 - summary (the edit comment) <br/>4 - basetimestamp of the last rev <br/>5 - md5 hash of the text
	 * @var hashmap
	 */
	public $queryUpdatePage = array(
	"action" => "edit",	
	"title" => ":0",
	"token" => ":1",
	"text" => ":2",
	"summary" => ":3",
	"basetimestamp" => ":4",
	"md5" => ":5",
	"nocreate" => "true"
	);

	/**
	 * The query for requesting an edit token with a placeholder for the page title.<br/>0 - page title
	 *
	 * @var hashmap
	 */
	public $queryDeleteToken = array(
	"action" => "query",
	"prop" => "info",
	"intoken" => "delete",
	"titles" => ":0"
	);

	/**
	 * The query for deleteing a page with placeholders.<br/>0 - page title <br/>1 - delete token
	 * @var hashmap
	 */
	public $queryDeletePage = array(
	"action" => "delete",	
	"title" => ":0",
	"token" => ":1",
	"reason" => ":2",
	);

	/**
	 * The query for requesting an edit token with a placeholder for the page title.<br/>0 - page title
	 *
	 * @var hashmap
	 */
	public $queryMoveToken = array(
	"action" => "query",
	"prop" => "info",
	"intoken" => "move",
	"titles" => ":0"
	);

	/**
	 * The query for deleteing a page with placeholders.<br/>0 - page title from <br/>1 - page title to <br/>1 - move token
	 * @var hashmap
	 */
	public $queryMovePage = array(
	"action" => "move",	
	"from" => ":0",
	"to" => ":1",
	"token" => ":2",
	);

	/**
	 * The class constructor. Since the class is not to be initiated directly,
	 * the constructor is protected.
	 *
	 * @category SMW Edit API
	 *
	 * @return PCPAny
	 */
	protected function PCPAny(){
		$this->page = new PCPPage();
	}

	/**
	 * Login to a wiki system and save the connection data (cookies).
	 *
	 * @param PCPUserCredentials $userCredentials
	 * @return PCPUserCredentials The uc after the login.
	 */
	public function login(PCPUserCredentials $userCredentials=NULL){
		return NULL;
	}

	/**
	 * Logout from a wiki system and delete the connection data (cookies).
	 *
	 * @param PCPUserCredentials $userCredentials
	 */
	public function logout(PCPUserCredentials $userCredentials=NULL){
	}

	//	/**
	//	 * Get the cookies in a forms as described <a href="http://www.mediawiki.org/wiki/API:Login">
	//	 * on the MediaWiki API pages</a>.
	//	 *
	//	 * @return An array with cookies.
	//	 */
	//	public function getCookies(){
	//		$__cookies = array(
	//		#$this->_cookieprefix."UserName" => $this->_usedUC->un,
	//		#$this->_cookieprefix."UserID" => $this->_usedUC->id,
	//		#$this->_cookieprefix."Token" => $this->_lgtoken,
	//		#$this->_cookieprefix."_session" => $this->_sessionid
	//		"UserName" => $this->_usedUC->un,
	//		"UserID" => $this->_usedUC->id,
	//		"Token" => $this->_lgtoken,
	//		"_session" => $this->_sessionid
	//		);
	//		return $__cookies;
	//	}

	//	/**
	//	 * Set the cookies after a successful login.
	//	 *
	//	 * @param PCPUserCredentials $userCredentials
	//	 */
	//	public function setCookies(PCPUserCredentials $userCredentials = NULL){
	//		// workaround: setting cookies internally
	//		$_SESSION['wsUserID'] = $userCredentials->id;
	//		$_SESSION['wsUserName'] = $userCredentials->un;
	//		$_SESSION['wsToken'] = $userCredentials->lgToken;
	//
	//		global $wgUser;
	//		$wgUser = User::newFromSession();
	//
	//	}

	//	/**
	//	 * Adds the cookies to the PCP object after a login.
	//	 *
	//	 * @param PCPUserCredentials $userCredentials
	//	 */
	//	protected function addCookies(PCPUserCredentials  $userCredentials = NULL){
	//		$this->_lgtoken = $lgToken;
	//		$this->_sessionid = $sessionID;
	//		$this->_usedUC = $uc;
	//	}

	/**
	 * Get the edit token for a page. If no page name is give, presumes that "Main Page" exists. 
	 *
	 * @param PCPUserCredentials $userCredentials
	 * @param string $title
	 * @return PCPUserCredentials The uc including the edit token.
	 */
	protected function getEditToken(PCPUserCredentials $userCredentials=NULL, $title=NULL){
		return NULL;
	}

	/**
	 * Create a page based on a title and return "true".
	 * If the page exists, "false" is returned.
	 *
	 * @param PCPUserCredentials $userCredentials
	 * @param String $title
	 * @param String $text
	 * @param String $summary
	 * @return boolean
	 */
	public function createPage(PCPUserCredentials $userCredentials=NULL, $title=NULL, $text=NULL, $summary=NULL){
		return false;
	}

	/**
	 * Create pages based on a title and return "true".
	 * If a page exists, "false" is returned.
	 *
	 * @param PCPUserCredentials $userCredentials
	 * @param string[] $titles
	 * @param string[] $texts
	 * @param string[] $summaries
	 * @return boolean[]
	 */
	public function createPages(PCPUserCredentials $userCredentials=NULL, $titles = NULL, $texts = NULL, $summaries = NULL){
		return false;
	}

	/**
	 * Read a page from the wiki. If no revisionID given, read the latest revision.
	 * Redirects are NOT supported.
	 *
	 * @param PCPUserCredentials $userCredentials
	 * @param string $title
	 * @param int $revisionID
	 * @return PCPPage
	 */
	public function readPage(PCPUserCredentials $userCredentials=NULL, $title=NULL, $revisionID=NULL){
		return NULL;
	}

	/**
	 * Read pages from the wiki. If no revisionID given, read the latest revision.
	 * Redirects are NOT supported.
	 *
	 * @param PCPUserCredentials $userCredentials
	 * @param string[] $titles
	 * @param HashMap(string,int) $revisionIDs A hashmap with titles as keys and IDs as values.
	 * @return PCPPage array
	 */
	public function readPages(PCPUserCredentials $userCredentials=NULL, $titles=NULL, $revisionIDs=NULL){
		return NULL;
	}

	/**
	 * Update a page in the wiki. If the page doesn't exist, an error occurs.
	 * If the basetimestamp is not set, revision conflicts will be ignored.
	 * The md5 hash can be set to check if the text is corrupted.
	 *
	 * @param PCPUserCredentials $userCredentials
	 * @param string $title
	 * @param string $text
	 * @param string $summary
	 * @param string $basetimestamp
	 * @param string $md5_hash
	 * @return boolean
	 */
	public function updatePage(PCPUserCredentials $userCredentials=NULL, $title=NULL, $text=NULL, $summary=NULL, $basetimestamp = NULL, $md5_hash = NULL){
		return false;
	}

	/**
	 * Update pages in the wiki. If a page doesn't exist, an error occurs.
	 * If the basetimestamp is not set, revision conflicts will be ignored.
	 * The md5 hash can be set to check if the text is corrupted.
	 *
	 * @param PCPUserCredentials $userCredentials
	 * @param string[] $titles
	 * @param HashMap(string,string) $texts
	 * @param HashMap(string,string) $summaries A hashmap with titles as keys and summaries as values.
	 * @param HashMap(string,string) $basetimestamps A hashmap with titles as keys and base timestamps as values.
	 * @param HashMap(string,string) $md5_hashes A hashmap with titles as keys and MD5 hashes as values.
	 * @return boolean
	 */
	public function updatePages(PCPUserCredentials $userCredentials=NULL, $titles=NULL, $texts=NULL, $summaries=NULL, $basetimestamps = NULL, $md5_hashes = NULL){
		return false;
	}


	//	/**
	//	 * Get the delete token for a page.
	//	 *
	//	 * @param string $title
	//	 * @return PCPUserCredentials The uc including the delete token.
	//	 */
	//	protected function getDeleteToken(PCPUserCredentials $userCredentials=NULL, $title=NULL){
	//		return NULL;
	//	}

	/**
	 * Delete a given page.
	 *
	 * @param PCPUserCredentials $userCredentials
	 * @param string $title
	 * @param string reason
	 * @return boolean
	 */
	public function deletePage(PCPUserCredentials $userCredentials=NULL, $title=NULL, string $reason=NULL){
		return false;
	}

	/**
	 * Delete given pages in the wiki.
	 *
	 * @param PCPUserCredentials $userCredentials
	 * @param string[] $titles
	 * @param string[] $reasons
	 * @return boolean
	 */
	public function deletePages(PCPUserCredentials $userCredentials=NULL, $titles = NULL, $reasons=NULL){
		return false;
	}
	//	/**
	//	 * Get the move token for a page.
	//	 *
	//	 * @param string $title
	//	 * @return PCPUserCredentials The uc including the move token.
	//	 */
	//	protected function getMoveToken(PCPUserCredentials $userCredentials=NULL, $title = NULL){
	//		return '';
	//	}

	/**
	 * Move a page in the wiki.
	 *
	 * @param PCPUserCredentials $userCredentials
	 * @param string $fromTitle
	 * @param string $toTitle
	 * @param boolean $movetalk
	 * @param boolean $noredirect
	 * @return boolean
	 */
	public function movePage(PCPUserCredentials $userCredentials=NULL, $fromTitle=NULL, $toTitle=NULL, $movetalk=false, $noredirect=false){
		return false;
	}

	/**
	 * Move pages in the wiki.
	 *
	 * @param PCPUserCredentials $userCredentials
	 * @param string[] $fromTitles
	 * @param HashMap(string,string) $toTitles A hashmap with fromTitles as keys and toTitles as values.
	 * @param HashMap(string,boolean) $movetalks A hashmap with fromTitles as keys and movetalks as values.
	 * @param HashMap(string,boolean) $noredirects A hashmap with fromTitles as keys and noredirects as values.
	 * @return boolean
	 */
	public function movePages(PCPUserCredentials $userCredentials=NULL, $fromTitles=NULL, $toTitles=NULL, $movetalks=false, $noredirects=false){
		return false;
	}
}
