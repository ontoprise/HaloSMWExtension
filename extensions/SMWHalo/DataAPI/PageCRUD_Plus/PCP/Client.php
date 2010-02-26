<?php

/**
 * @file
  * @ingroup DAPCP
  *
  * @author Dian
 */

/**
 * This is an abstract class used for creating API functions for the client side.
 * <b>NOT USED IN THE CURRENT VERSION.</b>
 * @author  Dian
 * @version 0.1
 *
 */
class PCPClient extends PCPAny{

	/**
	 * The target wiki system.
	 *
	 * @var PCPWikiSystem
	 */
	protected $targetWiki;

	/**
	 * Sets the return format of the target API. By default: <i>&format=php</i>
	 *
	 *
	 * @var string
	 */
	private $_returnFormat;

	/**
	 * The filepath + filename used to save cookies. By default <i>./cookiejar.txt</i>
	 *
	 * @var string
	 */
	public $cookieFile = '';

	/**
	 * The client constructor.
	 *
	 * @param PCPUserCredentials $useCredentials The user credentials to be used.
	 * @param PCPWikiSystem $wikiSystem The target wiki system.
	 * @return PCPClient
	 */
	public function PCPClient(PCPUserCredentials $useCredentials, PCPWikiSystem $wikiSystem){
		$this->usedUC = $useCredentials;
		$this->targetWiki = $wikiSystem;
		$this->_returnFormat = '&format=php';
		$this->cookieFile = './cookiejar.txt';
	}

	public function login(PCPUserCredentials $userCredentials=NULL){
		// check if new user credentials are set
		if($userCredentials!= NULL && $this->usedUC->un!=$userCredentials->un){
			$this->usedUC = $userCredentials;
		}
		if($userCredentials==NULL){
			$userCredentials=$this->usedUC;
		}

		foreach (PCPUtil::replaceInHash($this->lgQuery,
		array(urlencode($userCredentials->un),urlencode($userCredentials->pwd))) as $__parameter=>$__parameterValue){
			$__parameters.=$__parameter.'='.$__parameterValue.'&';
		}

		$__request = $this->targetWiki->url.'/'.$this->targetWiki->api.'?'. // adds the main part
		$__parameters. // adds the parameters
		$this->_returnFormat; // sets the return format

		$__curlHandler = curl_init();
		curl_setopt($__curlHandler, CURLOPT_URL, $__request);
		if ($this->targetWiki->proxyAddr != "") {
			curl_setopt($__curlHandler, CURLOPT_PROXY, $this->targetWiki->proxyAddr);
		}
		curl_setopt($__curlHandler, CURLOPT_HEADER, false);// don't return the header in teh result
		curl_setopt($__curlHandler, CURLOPT_COOKIEJAR, $this->cookieFile);
		curl_setopt($__curlHandler, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($__curlHandler, CURLOPT_POST, 1); // use the POST method
		curl_setopt($__curlHandler, CURLOPT_POSTFIELDS, "wpName=".$this->usedUC->un."&wpPassword=".$this->usedUC->pwd."&wpLoginattempt=true");
		$__serializedResult = curl_exec($__curlHandler);
		curl_close($__curlHandler);

		$__result = unserialize($__serializedResult);
		if ($__result['login']['result']!=="Success"){
			return NULL;
		}else{
			$this->usedUC->id = $__result['login']['lguserid'];
			$this->usedUC->pwd = ''; // remove the password
			$this->usedUC->lgToken = $__result['login']['lgtoken'];
		}
		return $this->usedUC;
	}

	public function logout(PCPUserCredentials $userCredentials=NULL){

	}

	public function getEditToken(PCPUserCredentials $userCredentials=NULL, $title=NULL){
		// check if new user credentials are set
		if($userCredentials!= NULL && $this->usedUC->un!=$userCredentials->un){
			$this->usedUC = $userCredentials;
			$this->login();
		}

		if($this->usedUC->editToken !== NULL){
			return $this->usedUC->editToken;
		}else{
			if(!isset($title)){
				// try with Main Page
				$title="Main Page";
			}
			// extract only the first title
			$title = PCPUtil::trimFirstTitle($title);

			foreach (PCPUtil::replaceInHash($this->queryEditToken, array(urlencode($title))) as $__parameter=>$__parameterValue){
				$__parameters.=$__parameter.'='.$__parameterValue.'&';
			}

			$__request = $this->targetWiki->url.'/'.$this->targetWiki->api.'?'. // adds the main part
			$__parameters. // adds the parameters
			$this->_returnFormat; // sets the return format

			$__curlHandler = curl_init();
			curl_setopt($__curlHandler, CURLOPT_URL, $__request);
			if ($this->targetWiki->proxyAddr != "") {
				curl_setopt($__curlHandler, CURLOPT_PROXY, $this->targetWiki->proxyAddr);
			}
			curl_setopt($__curlHandler, CURLOPT_HEADER, false); // don't return the header in teh result
			curl_setopt($__curlHandler, CURLOPT_COOKIEJAR, $this->cookieFile);
			curl_setopt($__curlHandler, CURLOPT_COOKIEFILE, $this->cookieFile); // set the file where cookies from which cookies are being read
			curl_setopt($__curlHandler, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($__curlHandler, CURLOPT_POST, 1);// use the POST method
			#curl_setopt($__curlHandler, CURLOPT_POSTFIELDS, "wpName=".$this->usedUC->un."&wpLoginToken=".$this->usedUC->lgToken);
			$__serializedResult = curl_exec($__curlHandler);
			curl_close($__curlHandler);

			$__result = unserialize($__serializedResult);
			if (!isset($__result['query']['pages'])){
				// error handling
				return NULL;
			}else{
				// there could be more than one page in the result
				// each entry in the hashmap has the page ID as key
				foreach ($__result['query']['pages'] as $__pageid => $__pageObject){
					// get the page that has the matching title
					if ($__result['query']['pages'][$__pageid]['title'] === $title){
						$this->usedUC->editToken = $__result['query']['pages'][$__pageid]['edittoken'];
					}
				}

			}
		}
		return $this->usedUC->editToken;
	}

	public function createPage(PCPUserCredentials $uc=NULL, $title=NULL, $text=NULL, $summary=NULL){
		// check if new user credentials are set
		if($userCredentials!= NULL && $this->usedUC->un!=$userCredentials->un){
			$this->usedUC = $userCredentials;
			$this->login();
		}

		if(!isset($title)){
			return false;
		}

		// get the edit token for the page
		$this->getEditToken(NULL, urldecode($title));

		// extract only the first title
		$title = PCPUtil::trimFirstTitle($title);
			
		foreach (PCPUtil::replaceInHash($this->queryCreatePage, array(urlencode( $title),urlencode($this->usedUC->editToken), urlencode($text), urlencode($summary)))
		as $__parameter=>$__parameterValue){
			$__parameters.=$__parameter.'='.$__parameterValue.'&';
		}

		$__request = $this->targetWiki->url.'/'.$this->targetWiki->api.'?'. // adds the main part
		$__parameters. // adds the parameters
		$this->_returnFormat; // sets the return format

		$__curlHandler = curl_init();
		curl_setopt($__curlHandler, CURLOPT_URL, $__request);
		if ($this->targetWiki->proxyAddr != "") {
			curl_setopt($__curlHandler, CURLOPT_PROXY, $this->targetWiki->proxyAddr);
		}
		curl_setopt($__curlHandler, CURLOPT_HEADER, false);// don't return the header in teh result
		curl_setopt($__curlHandler, CURLOPT_COOKIEJAR, $this->cookieFile);
		curl_setopt($__curlHandler, CURLOPT_COOKIEFILE, $this->cookieFile);// set the file where cookies from which cookies are being read
		curl_setopt($__curlHandler, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($__curlHandler, CURLOPT_POST, 1);// use the POST method
		#curl_setopt($__curlHandler, CURLOPT_POSTFIELDS, "wpName=".$this->usedUC->un."&wpLoginToken=".$this->usedUC->lgToken);
		$__serializedResult = curl_exec($__curlHandler);
		curl_close($__curlHandler);

		$__result = unserialize($__serializedResult);
		return $__result;
	}

	public function readPage(PCPUserCredentials $uc=NULL, $title= NULL, $revisionID = NULL){
		if($userCredentials!= NULL && $this->usedUC->un!=$userCredentials->un){
			$this->usedUC = $userCredentials;
			$this->login();
		}

		$this->page = new PCPPage($title);

		// extract only the first title
		$title = PCPUtil::trimFirstTitle($title);
			
		foreach (PCPUtil::replaceInHash($this->queryReadPage,array(urlencode($title)))
		as $__parameter=>$__parameterValue){
			$__parameters.=$__parameter.'='.$__parameterValue.'&';
		}

		$__request = $this->targetWiki->url.'/'.$this->targetWiki->api.'?'. // adds the main part
		$__parameters. // adds the parameters
		$this->_returnFormat; // sets the return format

		$__curlHandler = curl_init();
		curl_setopt($__curlHandler, CURLOPT_URL, $__request);
		if ($this->targetWiki->proxyAddr != "") {
			curl_setopt($__curlHandler, CURLOPT_PROXY, $this->targetWiki->proxyAddr);
		}
		curl_setopt($__curlHandler, CURLOPT_HEADER, false);// don't return the header in teh result
		curl_setopt($__curlHandler, CURLOPT_COOKIEJAR, $this->cookieFile);
		curl_setopt($__curlHandler, CURLOPT_COOKIEFILE, $this->cookieFile);// set the file where cookies from which cookies are being read
		curl_setopt($__curlHandler, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($__curlHandler, CURLOPT_POST, 1);// use the POST method
		#curl_setopt($__curlHandler, CURLOPT_POSTFIELDS, "wpName=".$this->usedUC->un."&wpLoginToken=".$this->usedUC->lgToken);
		$__serializedResult = curl_exec($__curlHandler);
		curl_close($__curlHandler);

		$__result = unserialize($__serializedResult);
		if (!isset($__result['query']['pages'])){
			// error handling
			// print ("ERROR: Reading a single page failed".__FILE__);
			return NULL;
		}else{
			// there could be more than one page in the result
			// each entry in the hashmap has the page ID as key
			foreach ($__result['query']['pages'] as $__pageid => $__pageObject){
				if ($__result['query']['pages'][$__pageid]['title'] === $title){
					// get the page that has the matching title
					if ($__pageid !== -1){
						// the page exists
						$this->page->pageid=$__pageid;
						$this->page->title=$__result['query']['pages'][$__pageid]['title'];
						$this->page->lastrevid=$__result['query']['pages'][$__pageid]['lastrevid'];
						$this->page->basetimestamp=$__result['query']['pages'][$__pageid]['touched'];
						$this->page->namespace=$__result['query']['pages'][$__pageid]['ns'];

						if($revisionID !==NULL){
							foreach ($__result['query']['pages'][$__pageid]['revisions'] as $__idx => $__revision){
								if($__revision['revid'] === $revisionID){
									$this->page->usedrevid = $revisionID;
									$this->page->text = $__revision['*'];
								}
							}
						}else{
							$this->page->usedrevid = $__result['query']['pages'][$__pageid]['lastrevid'];
							foreach ($__result['query']['pages'][$__pageid]['revisions'] as $__idx => $__revision){
								if($__revision['revid'] === $this->page->lastrevid){
									$this->page->text = $__result['query']['pages'][$__pageid]['revisions'][$__idx]['*'];
								}
							}
						}
					}else{
						// print ("WARNING: The page $title does not exist.");
						return NULL;
					}

				}
			}
			return $this->page;
		}
	}

	public function updatePage(PCPUserCredentials $uc=NULL, $title= NULL, $text = NULL, $summary=NULL, $basetimestamp = NULL, $md5_hash = NULL){
		// check if new user credentials are set
		if($userCredentials!= NULL && $this->usedUC->un!=$userCredentials->un){
			$this->usedUC = $userCredentials;
			$this->login();
		}

		if(!isset($title)){
			return false;
		}

		// get the edit token for the page
		$this->getEditToken(NULL, urldecode($title));

		// extract only the first title
		$title = PCPUtil::trimFirstTitle($title);
			

		if( $md5_hash !== NULL){
			if ($basetimestamp === NULL)
			{
				// $basetimestamp = $this->page->basetimestamp; // use the last revision
				unset($this->queryUpdatePage['basetimestamp']);
				foreach (PCPUtil::replaceInHash($this->queryUpdatePage, array(urlencode( $title),urlencode($this->usedUC->editToken), urlencode($text), urlencode($summary), urlencode($md5_hash)))
				as $__parameter=>$__parameterValue){
					$__parameters.=$__parameter.'='.$__parameterValue.'&';
				}
			}else{
				foreach (PCPUtil::replaceInHash($this->queryUpdatePage, array(urlencode( $title),urlencode($this->usedUC->editToken), urlencode($text), urlencode($summary), urlencode($basetimestamp), urlencode($md5_hash)))
				as $__parameter=>$__parameterValue){
					$__parameters.=$__parameter.'='.$__parameterValue.'&';
				}
			}
		}else{
			unset($this->queryUpdatePage['md5']);
			if ($basetimestamp === NULL)
			{
				// $basetimestamp = $this->page->basetimestamp;
				unset($this->queryUpdatePage['basetimestamp']);
				foreach (PCPUtil::replaceInHash($this->queryUpdatePage, array(urlencode( $title),urlencode($this->usedUC->editToken), urlencode($text), urlencode($summary)))
				as $__parameter=>$__parameterValue){
					$__parameters.=$__parameter.'='.$__parameterValue.'&';
				}
			}else{
				foreach (PCPUtil::replaceInHash($this->queryUpdatePage, array(urlencode( $title),urlencode($this->usedUC->editToken), urlencode($text), urlencode($summary), urlencode($basetimestamp)))
				as $__parameter=>$__parameterValue){
					$__parameters.=$__parameter.'='.$__parameterValue.'&';
				}
			}

		}

		$__request = $this->targetWiki->url.'/'.$this->targetWiki->api.'?'. // adds the main part
		$__parameters. // adds the parameters
		$this->_returnFormat; // sets the return format

		$__curlHandler = curl_init();
		curl_setopt($__curlHandler, CURLOPT_URL, $__request);
		if ($this->targetWiki->proxyAddr != "") {
			curl_setopt($__curlHandler, CURLOPT_PROXY, $this->targetWiki->proxyAddr);
		}
		curl_setopt($__curlHandler, CURLOPT_HEADER, false);// don't return the header in teh result
		curl_setopt($__curlHandler, CURLOPT_COOKIEJAR, $this->cookieFile);
		curl_setopt($__curlHandler, CURLOPT_COOKIEFILE, $this->cookieFile);// set the file where cookies from which cookies are being read
		curl_setopt($__curlHandler, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($__curlHandler, CURLOPT_POST, 1);// use the POST method
		#curl_setopt($__curlHandler, CURLOPT_POSTFIELDS, "wpName=".$this->usedUC->un."&wpLoginToken=".$this->usedUC->lgToken);
		$__serializedResult = curl_exec($__curlHandler);
		curl_close($__curlHandler);

		$__result = unserialize($__serializedResult);
		return $__result;
	}


	public function deletePage(PCPUserCredentials $uc=NULL, $title=NULL, $reason=NULL){
		// check if new user credentials are set
		if($userCredentials!= NULL && $this->usedUC->un!=$userCredentials->un){
			$this->usedUC = $userCredentials;
			$this->login();
		}

		if(!isset($title)){
			return false;
		}

		// get the edit token for the page
		$this->getEditToken(NULL, urldecode($title));

		// extract only the first title
		$title = PCPUtil::trimFirstTitle($title);
			
		foreach (PCPUtil::replaceInHash($this->queryDeletePage, array(urlencode( $title),urlencode($this->usedUC->editToken), urlencode($reason)))
		as $__parameter=>$__parameterValue){
			$__parameters.=$__parameter.'='.$__parameterValue.'&';
		}

		$__request = $this->targetWiki->url.'/'.$this->targetWiki->api.'?'. // adds the main part
		$__parameters. // adds the parameters
		$this->_returnFormat; // sets the return format

		$__curlHandler = curl_init();
		curl_setopt($__curlHandler, CURLOPT_URL, $__request);
		if ($this->targetWiki->proxyAddr != "") {
			curl_setopt($__curlHandler, CURLOPT_PROXY, $this->targetWiki->proxyAddr);
		}
		curl_setopt($__curlHandler, CURLOPT_HEADER, false);// don't return the header in teh result
		curl_setopt($__curlHandler, CURLOPT_COOKIEJAR, $this->cookieFile);
		curl_setopt($__curlHandler, CURLOPT_COOKIEFILE, $this->cookieFile);// set the file where cookies from which cookies are being read
		curl_setopt($__curlHandler, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($__curlHandler, CURLOPT_POST, 1);// use the POST method
		#curl_setopt($__curlHandler, CURLOPT_POSTFIELDS, "wpName=".$this->usedUC->un."&wpLoginToken=".$this->usedUC->lgToken);
		$__serializedResult = curl_exec($__curlHandler);
		curl_close($__curlHandler);

		$__result = unserialize($__serializedResult);
		return $__result;
	}
	public function movePage(PCPUserCredentials $uc=NULL,$fromTitle=NULL, $toTitle=NULL, $movetalk=false, $noredirect=false){
		// check if new user credentials are set
		if($userCredentials!= NULL && $this->usedUC->un!=$userCredentials->un){
			$this->usedUC = $userCredentials;
			$this->login();
		}

		if(!isset($fromTitle)||!isset($toTitle)){
			return false;
		}

		// get the edit token for the page
		$this->getEditToken(NULL, urldecode($fromTitle));
			
		// if movetalk is not set, do not use it
		if($movetalk){
			$this->queryMovePage = array_merge($this->queryMovePage, array('movetalk'));
		}
		if($noredirect){
			$this->queryMovePage = array_merge($this->queryMovePage, array('noredirect'));
		}

		foreach (PCPUtil::replaceInHash($this->queryMovePage, array(urlencode($fromTitle),urlencode($toTitle), urlencode($this->usedUC->editToken)))
		as $__parameter=>$__parameterValue){
			$__parameters.=$__parameter.'='.$__parameterValue.'&';
		}

		$__request = $this->targetWiki->url.'/'.$this->targetWiki->api.'?'. // adds the main part
		$__parameters. // adds the parameters
		$this->_returnFormat; // sets the return format

		$__curlHandler = curl_init();
		curl_setopt($__curlHandler, CURLOPT_URL, $__request);
		if ($this->targetWiki->proxyAddr != "") {
			curl_setopt($__curlHandler, CURLOPT_PROXY, $this->targetWiki->proxyAddr);
		}
		curl_setopt($__curlHandler, CURLOPT_HEADER, false);// don't return the header in teh result
		curl_setopt($__curlHandler, CURLOPT_COOKIEJAR, $this->cookieFile);
		curl_setopt($__curlHandler, CURLOPT_COOKIEFILE, $this->cookieFile);// set the file where cookies from which cookies are being read
		curl_setopt($__curlHandler, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($__curlHandler, CURLOPT_POST, 1);// use the POST method
		#curl_setopt($__curlHandler, CURLOPT_POSTFIELDS, "wpName=".$this->usedUC->un."&wpLoginToken=".$this->usedUC->lgToken);
		$__serializedResult = curl_exec($__curlHandler);
		curl_close($__curlHandler);

		$__result = unserialize($__serializedResult);
		return $__result;
	}

	public function deleteCookies(){
		return unlink($this->cookieFile);
	}

	public function __destruct(){
		if(file_exists($this->cookieFile)){
			$this->deleteCookies();
		}
	}
}
