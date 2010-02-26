<?php

/**
 * @file
  * @ingroup DAPOM
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
$wgAPIModules['wspom'] = 'POMPageWS_API';

/**
 * @addtogroup API
 */
class POMPageWS_API extends ApiBase {

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
		if (array_key_exists('et', $params)){
			$__editToken = $params['et'];
		}else{
			$__editToken = NULL;
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
		// template title
		if (array_key_exists('ttitle', $params)){
			$__ttitle = $params['ttitle'];
		}else{
			$__ttitle = NULL;
		}
		// property name
		if (array_key_exists('pname', $params)){
			$__pname = $params['pname'];
		}else{
			$__pname = NULL;
		}
		// category name
		if (array_key_exists('cname', $params)){
			$__cname = $params['cname'];
		}else{
			$__cname = NULL;
		}

		$limit = $params['limit'];

		if (strlen($__method) == 0)
		{
			$this->dieUsage("The method must be specified", 'param_method');
		}elseif ($__title == '') {
			$this->dieUsage("The title must be specified", 'param_title');
		}elseif ($__method == "getElements") {
			$data = $this->getElements($__username, $__pwd, $__uid, $__loginToken, $__editToken, $__title, $__revisionID);
		}elseif ($__method == "getTemplates") {
			$data = $this->getTemplates($__username, $__pwd, $__uid, $__loginToken, $__editToken, $__title, $__revisionID);
		}
		elseif ($__method == "getTemplateByTitle") {
			$data = $this->getTemplateByTitle($__username, $__pwd, $__uid, $__loginToken, $__editToken, $__title, $__revisionID, $__ttitle);
		}
		elseif ($__method == "getProperties") {
			$data = $this->getProperties($__username, $__pwd, $__uid, $__loginToken, $__editToken, $__title, $__revisionID);
		}
		elseif ($__method == "getPropertyByName") {
			$data = $this->getPropertyByName($__username, $__pwd, $__uid, $__loginToken, $__editToken, $__title, $__revisionID, $__pname);
		}
		elseif ($__method == "getCategories") {
			$data = $this->getCategories($__username, $__pwd, $__uid, $__loginToken, $__editToken, $__title, $__revisionID);
		}
		elseif ($__method == "getCategoryByName") {
			$data = $this->getCategoryByName($__username, $__pwd, $__uid, $__loginToken, $__editToken, $__title, $__revisionID, $__cname);
		}
		elseif ($__method == "getSimpleTexts") {
			$data = $this->getSimpleTexts($__username, $__pwd, $__uid, $__loginToken, $__editToken, $__title, $__revisionID);
		}
		else {
			$data = array();
		}
		if (count($data)<=0) {
			return;
		}
		
		$data = $this->postProcessData($data);

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
			//'et' => null,
			//'text' => null,
			'rid' => null,
			'ttitle' => null,
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
			'method' => 'The method to be called. Supported methods: getElements|getTemplates|getTemplateByTitle|getSimpleTexts',
			//'method' => 'The method to be called. Supported methods: getElements|getTemplates|getTemplateByTitle|getProperties|getPropertyByName|getCategories|getCategoryByName|getSimpleTexts',
			'un' => 'The username',
			'pwd' => 'The password',
			'uid' => 'The user ID',
			'lt' => 'The login token',
			//'et' => 'The edit token',
			//'text' => 'The text of the page if updating or creating',
			'rid' => 'The page revision ID',
			'ttitle' => 'The template title',
			//'pname' => 'The property name',
			//'cname' => 'The category name',

		);
	}

	protected function getDescription() {
		return 'The Page Object Model REST API.';
	}

	protected function getExamples() {
		return array (
			'api.php?action=wspom&method=getElements&title=Main_Page',
			'api.php?action=wspom&method=getTemplatesByTitle&title=Main_Page&ttitle=Templatename',			
		);
	}

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}

	protected function getElements($username=NULL, $password=NULL, $id=NULL, $loginToken = NULL, $editToken = NULL, $title= NULL, $revisionID = NULL){
		$__pcpServer = new PCPServer();
		$__userCredentials = new PCPUserCredentials($username, $password, $id, $loginToken, $editToken);
		$__pcpServer->login($__userCredentials);
		$__pcpPage = $__pcpServer->readPage($__userCredentials,$title, $revisionID);
		$__pom = new POMPage($__pcpPage->titel, $__pcpPage->text);
		$__result = array();
		$__elementsIterator = $__pom->getElements()->listIterator();
		$__result["page"] = $this->toArray ($__pcpPage);
		$__cnt = 0;
		while($__elementsIterator->hasNext()){
			$__element = &$__elementsIterator->getNextNodeValueByReference();
			$__array = $this->toArray($__element);
			$__result["page"][get_class($__element)][$__cnt] = $__array;
			$__cnt++;
		}
		$result = $this->getResult();
		$result->setIndexedTagName_recursive($__result, 'el');
		$result->addValue(null, $this->getModuleName(), $__result);
	}
	protected function getTemplates($username=NULL, $password=NULL, $id=NULL, $loginToken = NULL, $editToken = NULL, $title= NULL, $revisionID = NULL){
		$__pcpServer = new PCPServer();
		$__userCredentials = new PCPUserCredentials($username, $password, $id, $loginToken, $editToken);
		$__pcpServer->login($__userCredentials);
		$__pom = new POMPage($title, $__pcpServer->readPage($__userCredentials,$title, $revisionID)->text);
		$__result = array();
		$__elementsIterator = $__pom->getTemplates()->listIterator();
		while($__elementsIterator->hasNext()){
			$__element = &$__elementsIterator->getNextNodeValueByReference();
			$__array = $this->toArray($__element);
			$__result[get_class($__element)][$__element->id] = $__array;
		}
		return $__result;
	}
	protected function getTemplateByTitle($username=NULL, $password=NULL, $id=NULL, $loginToken = NULL, $editToken = NULL, $title= NULL, $revisionID = NULL, $templateTitle = NULL){
		$__pcpServer = new PCPServer();
		$__userCredentials = new PCPUserCredentials($username, $password, $id, $loginToken, $editToken);
		$__pcpServer->login($__userCredentials);
		$__pom = new POMPage($title, $__pcpServer->readPage($__userCredentials,$title, $revisionID)->text);
		$__result = array();
		$__elementsIterator = $__pom->getTemplateByTitle($templateTitle)->listIterator();
		while($__elementsIterator->hasNext()){
			$__element = &$__elementsIterator->getNextNodeValueByReference();
			$__array = $this->toArray($__element);
			$__result[get_class($__element)][$__element->id] = $__array;
		}
		return $__result;
	}
	protected function getProperties($username=NULL, $password=NULL, $id=NULL, $loginToken = NULL, $editToken = NULL, $title= NULL, $revisionID = NULL){
		$__pcpServer = new PCPServer();
		$__userCredentials = new PCPUserCredentials($username, $password, $id, $loginToken, $editToken);
		$__pcpServer->login($__userCredentials);
		$__pom = new POMPage($title, $__pcpServer->readPage($__userCredentials,$title, $revisionID)->text);
		$__result = array();
		$__elementsIterator = $__pom->getProperties()->listIterator();
		while($__elementsIterator->hasNext()){
			$__element = &$__elementsIterator->getNextNodeValueByReference();
			$__array = $this->toArray($__element);
			$__result[get_class($__element)][$__element->id] = $__array;
		}
		return $__result;
	}
	protected function getPropertyByName($username=NULL, $password=NULL, $id=NULL, $loginToken = NULL, $editToken = NULL, $title= NULL, $revisionID = NULL, $propertyName = NULL){
		$__pcpServer = new PCPServer();
		$__userCredentials = new PCPUserCredentials($username, $password, $id, $loginToken, $editToken);
		$__pcpServer->login($__userCredentials);
		$__pom = new POMPage($title, $__pcpServer->readPage($__userCredentials,$title, $revisionID)->text);
		$__result = array();
		$__elementsIterator = $__pom->getPropertyByName($propertyName)->listIterator();

		while($__elementsIterator->hasNext()){
			$__element = &$__elementsIterator->getNextNodeValueByReference();
			$__array = $this->toArray($__element);
			var_dump($__elementsIterator);
			$__result[get_class($__element)][$__element->id] = $__array;
		}

		return $__result;
	}
	protected function getCategories($username=NULL, $password=NULL, $id=NULL, $loginToken = NULL, $editToken = NULL, $title= NULL, $revisionID = NULL){
		$__pcpServer = new PCPServer();
		$__userCredentials = new PCPUserCredentials($username, $password, $id, $loginToken, $editToken);
		$__pcpServer->login($__userCredentials);
		$__pom = new POMPage($title, $__pcpServer->readPage($__userCredentials,$title, $revisionID)->text);
		$__result = array();
		$__elementsIterator = $__pom->getCategories()->listIterator();

		while($__elementsIterator->hasNext()){
			$__element = &$__elementsIterator->getNextNodeValueByReference();
			$__array = $this->toArray($__element);
			$__result[get_class($__element)][$__element->id] = $__array;
		}
		return $__result;
	}
	protected function getCategoryByName($username=NULL, $password=NULL, $id=NULL, $loginToken = NULL, $editToken = NULL, $title= NULL, $revisionID = NULL, $categoryName = NULL){
		$__pcpServer = new PCPServer();
		$__userCredentials = new PCPUserCredentials($username, $password, $id, $loginToken, $editToken);
		$__pcpServer->login($__userCredentials);
		$__pom = new POMPage($title, $__pcpServer->readPage($__userCredentials,$title, $revisionID)->text);
		$__result = array();
		$__elementsIterator = $__pom->getCategoryByName($categoryName)->listIterator();
		while($__elementsIterator->hasNext()){
			$__element = &$__elementsIterator->getNextNodeValueByReference();
			$__array = $this->toArray($__element);
			$__result[get_class($__element)][$__element->id] = $__array;
		}
		return $__result;
	}
	protected function getSimpleTexts($username=NULL, $password=NULL, $id=NULL, $loginToken = NULL, $editToken = NULL, $title= NULL, $revisionID = NULL){
		$__pcpServer = new PCPServer();
		$__userCredentials = new PCPUserCredentials($username, $password, $id, $loginToken, $editToken);
		$__pcpServer->login($__userCredentials);
		$__pom = new POMPage($title, $__pcpServer->readPage($__userCredentials,$title, $revisionID)->text);
		$__result = array();
		$__elementsIterator = $__pom->getSimpleTexts()->listIterator();

		while($__elementsIterator->hasNext()){
			$__element = &$__elementsIterator->getNextNodeValueByReference();
			$__array = $this->toArray($__element);
			$__result[get_class($__element)][$__element->id] = $__array;
		}
		return $__result;
	}

	private function toArray($data)
	{
		if(is_array($data) || is_object($data))
		{
			if(get_class($data) =='POMPage'){
				$__pom = new POMPage($data->titel, $data->text);
				$__result = array();
				$__elementsIterator = $__pom->getElements()->listIterator();
				$__result["page"] = $this->toArray ($__pcpPage);
				$__cnt = 0;
				while($__elementsIterator->hasNext()){
					$__element = &$__elementsIterator->getNextNodeValueByReference();
					$__array = $this->toArray($__element);
					$__result["page"][get_class($__element)][get_class($__element).$__cnt] = $__array;
					$__cnt++;
				}
				return $__result;
			}
			$__result = array();

			foreach ($data as $__key => $__value)
			{
				$__result[$__key] = $this->toArray($__value);
			}
			return $__result;
		}
		return $data;
	}
	
	private function postProcessData($data, $fatherKey = ""){
		if(is_array($data)){
			foreach($data as $key => $value){
				if(is_int($key)){
					unset($data[$key]);
					$key = $fatherKey.$key;
				}
				$data[$key] = $this->postProcessData($value, $key);
			}
		}
		return $data;
	}
}

