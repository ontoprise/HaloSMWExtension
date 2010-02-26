<?php

/**
 * @file
  * @ingroup DAPCP
  *
  * @author Dian
 */

/**
 * Adds and handles the 'wspcp' action to the MediaWiki API.
 *
 * @author dian
 */

/**
 * Protect against register_globals vulnerabilities.
 */
if (!defined('MEDIAWIKI')) die();

global $wgAPIModules;
$wgAPIModules['wspcp'] = 'PCPServerWS_API';

/**
 * @addtogroup API
 */
class PCPServerWS_API extends ApiBase {

	public function __construct($query, $moduleName) {
		parent :: __construct($query, $moduleName);
	}

	public function execute() {
		global $wgContLang;

		$params = $this->extractRequestParams();
		$__method = $params['method'];

		$__title = $params['title'];
		if (array_key_exists('un', $params)){
			$__username = $params['un'];
		}else{
			$__username = NULL;
		}
		if (array_key_exists('pwd', $params)){
			$__pwd = $params['pwd'];
		}else{
			$__pwd = NULL;
		}
		if (array_key_exists('uid', $params)){
			$__uid = $params['uid'];
		}else{
			$__uid = NULL;
		}
		if (array_key_exists('lt', $params)){
			$__loginToken = $params['lt'];
		}else{
			$__loginToken = NULL;
		}
		if (array_key_exists('text', $params)){
			$__text = $params['text'];
		}else{
			$__text = NULL;
		}
		if (array_key_exists('rid', $params)){
			$__revisionID = $params['rid'];
		}else{
			$__revisionID = NULL;
		}
		if (array_key_exists('summary', $params)){
			$__summary = $params['summary'];
		}else{
			$__summary = NULL;
		}
		if (array_key_exists('bt', $params)){
			$__basetimestamp = $params['bt'];
		}else{
			$__basetimestamp = NULL;
		}
		if (array_key_exists('md5_hash', $params)){
			$__md5_hash = $params['md5_hash'];
		}else{
			$__md5_hash = NULL;
		}
		if (array_key_exists('toTitle', $params)){
			$__toTitle = $params['toTitle'];
		}else{
			$__toTitle = NULL;
		}
		if (array_key_exists('moveTalk', $params)){
			$__moveTalk = $params['moveTalk'];
		}else{
			$__moveTalk = NULL;
		}
		if (array_key_exists('noredirect', $params)){
			$__noredirect = $params['noredirect'];
		}else{
			$__noredirect = NULL;
		}

		$limit = $params['limit'];

		if (strlen($__method) == 0)
		{
			$this->dieUsage("The method must be specified", 'param_method');
		}elseif ($__title == '' && $__method != "login" ) {
			$this->dieUsage("The title must be specified", 'param_title');
		}elseif ($__method == "login") {
			$data = $this->login($__username, $__pwd);
		}elseif ($__method == "createPage") {
			$data = $this->createPage($__username, $__pwd, $__uid, $__loginToken, $__title, $__text, $__summary);
		}elseif ($__method == "readPage") {
			$data = $this->readPage($__username, $__pwd, $__uid, $__loginToken, $__title, $__revisionID);
		}elseif ($__method == "updatePage") {
			$data = $this->updatePage($__username, $__pwd, $__uid, $__loginToken, $__title, $__text, $__summary, $__basetimestamp, $__md5_hash);
		}elseif ($__method == "deletePage") {
			$data = $this->deletePage($__username, $__pwd, $__uid, $__loginToken, $__title);
		}elseif ($__method == "movePage") {
			$data = $this->movePage($__username, $__pwd, $__uid, $__loginToken, $__title, $__toTitle, $__moveTalk, $__noredirect);
		}else {
			$date = array();
		}
		if (count($data)<=0) {
			return;
		}

		// Set top-level elements
		$result = $this->getResult();
		$result->setIndexedTagName($data, 'p');
		$result->addValue(null, $this->getModuleName(), $data);
	}

	protected function getAllowedParams() {
		return array (
			'method' => null,
			'title' => null,
			'un' => null,
			'pwd' => null,
			'uid' => null,
			'lt' => null,
			'text' => null,
			'rid' => null,
			'summary' => null,
			'bt' => null,
			'md5_hash' => null,
			//'fromTitle' => null,
			'toTitle' => null,
			'moveTalk' => null,
			'noredirect' => null,
			'summary' => null,
			'limit' => array (
		ApiBase :: PARAM_TYPE => 'limit',
		ApiBase :: PARAM_DFLT => 10,
		ApiBase :: PARAM_MIN => 1,
		ApiBase :: PARAM_MAX => ApiBase :: LIMIT_BIG1,
		ApiBase :: PARAM_MAX2 => ApiBase :: LIMIT_BIG2
		),
		);
	}

	protected function getParamDescription() {
		return array (
			'title' => 'The page title',			
			'method' => 'The method to be called. Supported methods: login|createPage(s)|readPage(s)|updatePage(s)|deletePage(s)|movePage(s)',
			'un' => 'The username',
			'pwd' => 'The password',
			'uid' => 'The user ID',
			'lt' => 'The login token',
			'text' => 'The text of the page if updating or creating',
			'rid' => 'The revision ID',
			'summary' => 'The summary',
			'bt' => 'The base timestamp',
			'md5_hash' => 'The MD5 hash',
			//'fromTitle' => 'The "from" title when moving a page',
			'toTitle' => 'The "to" title when moving a page',
			'moveTalk' => 'The movetalk when moving a page',
			'noredirect' => 'The redirect when moving a page',
			'summary' => 'Summary text edit actions.',		

		);
	}

	protected function getDescription() {
		return 'The Page CRUD Plus REST API.';
	}

	protected function getExamples() {
		return array (
			'api.php?action=wspcp&method=readPage&title=Main_Page',
			'api.php?action=wspcp&method=updatePage&title=Testpage&text=Changed',			
		);
	}

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}
	
	/**
	 * Login to the system with the given user credentials.
	 *
	 * @param string $username
	 * @param string $password
	 * @return If user authorized - the login token and user ID.
	 */
	protected function login($username = NULL, $password = NULL){
		$__pcpServer = new PCPServer();
		$__userCredentials = new PCPUserCredentials($username, $password);	
		$__resultingUserData = $__pcpServer->login($__userCredentials);
		$__result['login'] = array(); 
		if (isset($__resultingUserData->un)){
			$__result['login'] = $__resultingUserData->toHashmap();
			return $__result;
		}else{
			return $__resultingUserData;
		}
	}
	
	/**
	 * Create a new page.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $id
	 * @param string $loginToken
	 * @param string $title
	 * @param string $text
	 * @param string $summary
	 * @return status
	 */
	protected function createPage($username=NULL, $password=NULL, $id=NULL, $loginToken = NULL, $title=NULL, $text=NULL, $summary=NULL){
		$__pcpServer = new PCPServer();
		$__userCredentials = new PCPUserCredentials($username, $password, $id, $loginToken);
		$__result = array();
		$__result['createPage'] = $__pcpServer->createPage($__userCredentials,$title, $text, $summary); 
		return $__result;
	}	
	
	/**
	 * Create new pages.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $id
	 * @param string $loginToken
	 * @param string $title The titles must be separated by "|".
	 * @param string $text The texts are read from an associative array with titles as keys.
	 * @param string $summary The summaries are read from an associative array with titles as keys.
	 * @return hashmap Status per title.
	 */
	protected function createPages($username=NULL, $password=NULL, $id=NULL, $loginToken = NULL, $title=NULL, $text=NULL, $summary=NULL){
		$__pcpServer = new PCPServer();
		$__userCredentials = new PCPUserCredentials($username, $password, $id, $loginToken);		
		$__titles = split("|", $title);
		$__resultSet = array();
		$__result = array();
		
		foreach ($__titles as $__title){
			$__resultSet[str_replace(" ", "_",$__title)] =$__pcpServer->createPage($__userCredentials,$__title, $text[$__title], $summary[$__title]); 
		}
		
		$__result['createPage'] = $__resultSet; 
		return $__result;
	}

	/**
	 * Read a page.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $id
	 * @param string $loginToken	 
	 * @param string $title
	 * @param string $revisionID
	 * @return PCPPage The page.
	 */
	protected function readPage($username=NULL, $password=NULL, $id=NULL, $loginToken = NULL, $title= NULL, $revisionID = NULL){
		$__pcpServer = new PCPServer();
		$__userCredentials = new PCPUserCredentials($username, $password, $id, $loginToken);
		$__result = array();
		$__result['readPage'] = $__pcpServer->readPage($__userCredentials,$title, $revisionID)->toHashmap();
		return $__result;
	}
	
	/* Read pages.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $id
	 * @param string $loginToken
	 * @param string $title The titles must be separated by "|".
	 * @param string $revisionID The revision IDs are read from an associative array with titles as keys.
	 * @return hashmap The pages.
	 */
	protected function readPages($username=NULL, $password=NULL, $id=NULL, $loginToken = NULL, $title= NULL, $revisionID = NULL){
		$__pcpServer = new PCPServer();
		$__userCredentials = new PCPUserCredentials($username, $password, $id, $loginToken);		
		$__titles = split("|", $title);
		$__resultSet = array();
		$__result = array();
		
		foreach ($__titles as $__title){
			$__resultSet[str_replace(" ", "_",$__title)] =$__pcpServer->readPage($__userCredentials,$__title, $revisionID[$__title])->toHashmap(); 
		}
		$__result['readPage'] = $__resultSet;
		return $__result;
	}

	/**
	 * Update a page.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $id
	 * @param string $loginToken
	 * @param string $title
	 * @param string $text
	 * @param string $summary
	 * @param string $basetimestamp
	 * @param string $md5_hash
	 * @return status
	 */
	protected function updatePage($username=NULL, $password=NULL, $id=NULL, $loginToken = NULL, $title= NULL, $text = NULL, $summary=NULL, $basetimestamp = NULL, $md5_hash = NULL){
		$__pcpServer = new PCPServer();
		$__userCredentials = new PCPUserCredentials($username, $password, $id, $loginToken);
		$__result = array();
		$__result['updatePage'] = $__pcpServer->updatePage($__userCredentials,$title,$text, $summary, $basetimestamp, $md5_hash); 
		return $__result; 
	}

	/**
	 * Delete a page.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $id
	 * @param string $loginToken
	 * @param string $title
	 * @return status
	 */
	protected function deletePage($username=NULL, $password=NULL, $id=NULL, $loginToken = NULL, $title=NULL){
		$__pcpServer = new PCPServer();
		$__userCredentials = new PCPUserCredentials($username, $password, $id, $loginToken);
		$__result = array();
		$__result['deletePage'] = $__pcpServer->deletePage($__userCredentials,$title); 
		return $__result;
	}

	/**
	 * Move a page.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $id
	 * @param string $loginToken
	 * @param string $fromTitle
	 * @param string $toTitle
	 * @param string $movetalk
	 * @param string $noredirect
	 * @return status
	 */
	protected function movePage($username=NULL, $password=NULL, $id=NULL, $loginToken = NULL,
	$fromTitle=NULL, $toTitle=NULL, $movetalk=false, $noredirect=false){
		$__pcpServer = new PCPServer();
		$__userCredentials = new PCPUserCredentials($username, $password, $id, $loginToken);
		return $__pcpServer->movePage($__userCredentials,$fromTitle, $toTitle, $movetalk, $noredirect);
	}

	/**
	 * Returns the edit token.
	 *
	 * @see PCPServer::getEditToken
	 *
	 * @param string $username The username
	 * @param string_type $password
	 * @return PCPUserCredentials The user credentials object, including the edit token.
	 * @deprecated Edit tokens will be used only for internal purposes.
	 */
	protected function getEditToken($username=NULL, $password=NULL){
		$__pcpServer = new PCPServer();
		$__userCredentials = new PCPUserCredentials($username, $password);
		$__pcpServer->login($__userCredentials);
		$__et['editToken'] = $__pcpServer->getEditToken();

		return $__et;
	}	
}

