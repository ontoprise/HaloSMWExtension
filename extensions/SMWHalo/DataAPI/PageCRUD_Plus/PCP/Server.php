<?php

/**
 * @file
  * @ingroup DAPCP
  *
  * @author Dian
 */

//require_once('C:/xampp/htdocs/mw/includes/StubObject.php');

/**
 * The class provides the API functions on the server side.
 *
 * @author  Dian
 * @version 0.1
 */
class PCPServer extends PCPAny{

	/**
	 * A flag used to set cookies only once per function execution queue.
	 *
	 * @var boolean
	 */
	protected $cookiesSet = false;

	/**
	 * The class constructor.
	 *
	 */
	public function PCPServer(){
		$this->usedUC = new PCPUserCredentials();
	}

	protected function setCookies(PCPUserCredentials $userCredentials = NULL){
		global $wgUser;
		if ( !isset($wgUser->mId) || !$wgUser->mId){
			if ($this->usedUC->lgToken == NULL){
				$this->usedUC = $userCredentials;
			}
			
			if(!$this->cookiesSet){
				// workaround: setting cookies internally
				global $wgCookiePrefix;
				$_COOKIE["{$wgCookiePrefix}UserID"] = $this->usedUC->id; 
				
				$_SESSION['wsUserID'] = $this->usedUC->id;
				$_SESSION['wsUserName'] = $this->usedUC->un;
				$_SESSION['wsToken'] = $this->usedUC->lgToken;
					
				$wgUser = User::newFromSession();
				$wgUser->load();
				$this->usedUC->editToken = $wgUser->editToken();

				$this->cookiesSet = true;
			}
		}else{
			$this->usedUC->id = $wgUser->mId;
			$this->usedUC->un = $wgUser->mName;
			$this->usedUC->lgToken = $wgUser->mToken;
			$this->usedUC->editToken = $wgUser->editToken();
			
			$this->cookiesSet = true;
		}		
	}

	public function login(PCPUserCredentials $userCredentials=NULL){

		$__request = new FauxRequest(
		PCPUtil::replaceInHash($this->lgQuery,
		array($userCredentials->un,
		$userCredentials->pwd)));

		$__api = new ApiMain($__request);
		$__api->execute();
		$__result =& $__api->GetResultData();
		
		$__request = new FauxRequest( array(
			"action" => "login",
			"lgname"=> $userCredentials->un,
			"lgpassword"=> $userCredentials->pwd,
			"lgtoken"=>	 $__result['login']['token']));
		
		$__api = new ApiMain($__request);
		$__api->execute();
		$__result =& $__api->GetResultData();
		
		if ($__result['login']['result']!=="Success"){
			return $__result;
		}else{
			$userCredentials->id = $__result['login']['lguserid'];
			$userCredentials->pwd = ''; // remove the password
			$userCredentials->lgToken = $__result['login']['lgtoken'];
			
			$this->setCookies($userCredentials);
		}
		$this->usedUC = $userCredentials;
		return $this->usedUC;
	}

	public function logout(PCPUserCredentials $userCredentials=NULL){
		$this->setCookies($userCredentials);

		$__request = new FauxRequest($this->lgQueryLogout);
		$__api = new ApiMain($__request);
		$__api->execute();
		$this->usedUC = NULL;

	}

	public function getEditToken(PCPUserCredentials $userCredentials=NULL, $title=NULL){
		$this->setCookies($userCredentials);
		
		if($this->usedUC->editToken !== NULL && 
				$this->usedUC->editToken != "+\\"){
			return $this->usedUC->editToken;
		}else{
			if(!isset($title)){
				// try with Main Page
				$title="Main Page";
			}
			// extract only the first title
			$title = PCPUtil::trimFirstTitle($title);

			$__request = new FauxRequest(
			PCPUtil::replaceInHash($this->queryEditToken,
			array($title))
			);

			$__api = new ApiMain($__request);
			$__api->execute();
			$__result =& $__api->GetResultData();
			
			if (!isset($__result['query']['pages'])){
				// error handling
				return NULL;
			}else{
				// there could be more than one page in the result
				// each entry in the hashmap has the page ID as key
				foreach ($__result['query']['pages'] as $__pageid => $__pageObject){
					// get the page that has the matching title
					if ($__result['query']['pages'][$__pageid]['title'] == $title){
						$this->usedUC->editToken = $__result['query']['pages'][$__pageid]['edittoken'];
						$this->page->pageid=$__pageid;
						$this->page->title=$__result['query']['pages'][$__pageid]['title'];
						$this->page->lastrevid=$__result['query']['pages'][$__pageid]['lastrevid'];
						$this->page->usedrevid=$__result['query']['pages'][$__pageid]['lastrevid'];
						$this->page->basetimestamp=$__result['query']['pages'][$__pageid]['touched'];
						$this->page->namespace=$__result['query']['pages'][$__pageid]['ns'];
					}
				}

			}
		}
		return $this->usedUC->editToken;
	}

	public function createPage(PCPUserCredentials $userCredentials=NULL, $title=NULL, $text=NULL, $summary=NULL){
		/*
		 * NOTE: MW (tested >= 1.12.x) has a problem using the MW API
		 * when the extension=php_domxml.dll in php.ini is enabled.
		 * more information: http://blog.airness.de/2008/08/28/mediawiki-problem-with-xampp-167/
		 * DATE: 2008-10-09
		 * AUTHOR: Dian
		 */
		if($title == NULL){
			// trigger an error?
			return false;
		}
		$this->getEditToken($userCredentials, $title);

		$__request = new FauxRequest(
		PCPUtil::replaceInHash($this->queryCreatePage,
		array($title,$this->usedUC->editToken, $text, $summary))
		);

		$__api = new ApiMain($__request, true);
		$__api->execute();
		
		return $__api->GetResultData();
	}

	public function createPages(PCPUserCredentials $userCredentials=NULL, $titles = NULL, $texts = NULL, $summaries = NULL){
		$__result = array();
		foreach ($titles as $__title){
			$__result[] = $this->createPage($userCredentials, $__title);
		}
		return $__result;
	}

	public function &readPage(PCPUserCredentials $userCredentials=NULL, $title= NULL, $revisionID = NULL){

		$this->page = new PCPPage($title);
		$this->setCookies($userCredentials);

		// extract only the first title
		$title = PCPUtil::trimFirstTitle($title);

		$__request = new FauxRequest(
		PCPUtil::replaceInHash($this->queryReadPage,
				array($title)));

		$__api = new ApiMain($__request, true);
		$__api->execute();

		$__result =& $__api->GetResultData();
		
		if (!isset($__result['query']['pages'])){
			// error handling
			print ("ERROR: Reading a single page failed".__FILE__);
		}else{
			// there could be more than one page in the result
			// each entry in the hashmap has the page ID as key
			foreach ($__result['query']['pages'] as $__pageid => $__pageObject){
				if ($__result['query']['pages'][$__pageid]['title'] == str_replace('_', ' ', $title)){
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
								if($__revision['revid'] == $revisionID){
									$this->page->usedrevid = $revisionID;
									$this->page->text = $__revision['*'];
								}
							}
						}else{							
							$this->page->usedrevid = $__result['query']['pages'][$__pageid]['lastrevid'];
							foreach ($__result['query']['pages'][$__pageid]['revisions'] as $__idx => $__revision){
								if($__revision['revid'] == $this->page->lastrevid){
									$this->page->text = $__result['query']['pages'][$__pageid]['revisions'][$__idx]['*'];
								}
							}
						}
					}
				}
			}			
			return $this->page;
		}

	}

	public function readPages(PCPUserCredentials $userCredentials=NULL, $titles = NULL, $revisionIDs = NULL){
		$__pages = array();
		foreach ($titles as $__title){
			$__pages[$__title] = $this->readPage($userCredentials, $__title, $revisionIDs[$__title]);
		}
		return $__pages;
	}

	public function updatePage(PCPUserCredentials $userCredentials=NULL, $title=NULL, $text=NULL, $summary=NULL, $basetimestamp = NULL, $md5_hash = NULL){
		/*
		 * NOTE: MW (tested >= 1.12.x) has a problem using the MW API
		 * when the extension=php_domxml.dll in php.ini is enabled.
		 * more information: http://blog.airness.de/2008/08/28/mediawiki-problem-with-xampp-167/
		 * DATE: 2008-10-09
		 * AUTHOR: Dian
		 */
		if($title == NULL){
			// trigger an error?
			return false;
		}
		$this->getEditToken($userCredentials, $title);
				
		// if md5 is not set, do not use it
		if( $md5_hash !== NULL){
			if ($basetimestamp == NULL)
			{
				// $basetimestamp = $this->page->basetimestamp; // use the last revision
				unset($this->queryUpdatePage['basetimestamp']);
				$__request = new FauxRequest(
				PCPUtil::replaceInHash($this->queryUpdatePage,
				array($title,$this->usedUC->editToken, $text, $summary, $md5_hash ))
				);
			}else{
				$__request = new FauxRequest(
				PCPUtil::replaceInHash($this->queryUpdatePage,
				array($title,$this->usedUC->editToken, $text, $summary, $basetimestamp,$md5_hash ))
				);
			}
		}else{
			unset($this->queryUpdatePage['md5']);
			if ($basetimestamp == NULL)
			{				
				// $basetimestamp = $this->page->basetimestamp;
				unset($this->queryUpdatePage['basetimestamp']);
				$__request = new FauxRequest(
				PCPUtil::replaceInHash($this->queryUpdatePage,
				array($title,$this->usedUC->editToken, $text, $summary))
				);								
			}else{
				$__request = new FauxRequest(
				PCPUtil::replaceInHash($this->queryUpdatePage,
				array($title,$this->usedUC->editToken, $text, $summary, $basetimestamp))
				);
			}

		}	
			
		$__api = new ApiMain($__request, true);
		$__api->execute();
		return $__api->GetResultData();
	}

	public function updatePages(PCPUserCredentials $userCredentials=NULL, $titles = NULL, $texts = NULL, $summaries = NULL, $basetimestamps = NULL, $md5_hashes = NULL){
		$__pages = array();
		foreach ($titles as $__title){
			$__pages[$__title] = $this->updatePage($userCredentials, $__title, $texts[$__title], $summaries[$__title], $basetimestamps[$__title], $md5_hashes[$__title]);
		}
		return $__pages;
	}

	public function deletePage(PCPUserCredentials $userCredentials=NULL, $title=NULL, $reason){
		if($title == NULL){
			// trigger an error?
			return false;
		}

		$this->getEditToken($userCredentials, $title);

		$__request = new FauxRequest(
		PCPUtil::replaceInHash($this->queryDeletePage,
		array($title,$this->usedUC->editToken, $reason))
		);

		$__api = new ApiMain($__request, true);
		$__api->execute();
		//		$__result =& $__api->GetResultData();

		//		return true;
		return $__api->GetResultData();
	}

	public function deletePages(PCPUserCredentials $userCredentials=NULL, $titles = NULL, $reasons=NULL){
		$__pages = array();
		foreach ($titles as $__title){
			$__pages[$__title] = $this->deletePage($userCredentials, $__title, $reasons[$__title]);
		}
		return $__pages;
	}

	public function movePage(PCPUserCredentials $userCredentials=NULL, $fromTitle=NULL, $toTitle=NULL, $movetalk=false, $noredirect=false){
		if($fromTitle == NULL || $toTitle == NULL){
			// trigger an error?
			return false;
		}
		$this->getEditToken($userCredentials, $fromTitle);

		// if movetalk is not set, do not use it
		if($movetalk){
			$this->queryMovePage = array_merge($this->queryMovePage, array('movetalk'));
		}
		if($noredirect){
			$this->queryMovePage = array_merge($this->queryMovePage, array('noredirect'));
		}

		$__request = new FauxRequest(
		PCPUtil::replaceInHash($this->queryMovePage,
		array($fromTitle, $toTitle, $this->usedUC->editToken))
		);

		$__api = new ApiMain($__request, true);
		$__api->execute();
		//		$__result =& $__api->GetResultData();

		//		return true;
		return $__api->GetResultData();
	}

	public function movePages(PCPUserCredentials $userCredentials=NULL, $fromTitles=NULL, $toTitles=NULL, $movetalks=false, $noredirects=false){
		$__pages = array();
		foreach ($fromTitles as $__title){
			$__pages[$__title] = $this->movePage($userCredentials, $__title, $toTitles[$__title], $movetalks[$__title], $noredirects[$__title]);
		}
		return $__pages;
	}
}
