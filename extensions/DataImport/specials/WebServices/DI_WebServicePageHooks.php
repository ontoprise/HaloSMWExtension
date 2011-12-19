<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @ingroup DIWebServices
 *
 * This is the main entry file for the web service component.
 *
 * @author Thomas Schweitzer, Ingo Steinbauer
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) die;

/**
 * This class implements some parser hooks for
 * the special web service article page.
 */
class DIWebServicePageHooks {

	public static $mNewWebService = null;
	public static $mOldWebservice = null; // the webserve in its state before the wwsd is changed
	public static $mOldWebserviceRemembered = false;

	/**
	 * Register the WebServicePage for articles in the namespace 'WebService'.
	 *
	 * @return boolean
	 * 		Returns always <true>.
	 */
	static function showWebServicePage(&$title, &$article) {
		if ($title->getNamespace() == SMW_NS_WEB_SERVICE) {
			$article = new DIWebServicePage($title);
		}
		return true;
	}

	/**
	 * Stores the previously parsed WWSD in the database. This function is a hook for
	 * 'ArticleSaveComplete.'
	 *
	 * This function also deletes wwsd and cache entries from the db
	 * that are no longer needed.
	 *
	 * @param Article $article
	 * @param User $user
	 * @param string $text
	 * @return boolean true
	 */
	public static function articleSavedHook(&$article, &$user, $text) {
		if($article->getTitle()->getNamespace() != SMW_NS_WEB_SERVICE) {
			return true;
		}
		
		//deal with case where user replaces a WWSD with a completely empty article
		if(!self::$mOldWebserviceRemembered){
			$wwsd = DIWebService::newFromID($article->getID());;
			self::rememberWWSD($wwsd);
		}

		global $smwgDIIP;
		
		// check if an wwsd was change and delete the old wwsd and the
		// related cache entries from the db
		if(self::detectModifiedWWSD(self::$mNewWebService)){
			DIWebServiceCache::removeWS(self::$mOldWebservice->getArticleID());
			self::$mOldWebservice->removeFromDB();
				
			//deal with triplification
			if(self::$mOldWebservice){
				$articles = difGetWSStore()->getWSArticles(self::$mOldWebservice->getArticleID(), new SMWRequestOptions());
				DIWSTriplifier::getInstance()->removeWS(self::$mOldWebservice->getArticleID(), $articles);
			}
		}

		//handle triplification processing
		if (self::$mNewWebService) {
			self::$mNewWebService->store();
			DIWSTriplifier::getInstance()->addWSAsDataSource($article->getID());
			
			global $smwgDIIP;
			require_once($smwgDIIP . '/specials/WebServices/DI_WebServiceRepositoryAjaxAccess.php');
			smwf_ws_confirmWWSD(self::$mNewWebService->getArticleID());
		}
		
		
		self::$mNewWebService = null;
		self::$mOldWebservice = null;

		return true;
	}

	/*
	 * Removes a deleted wwsd and its related cache entries from the database.
	 * This function is a hook for 'ArticleDelete'
	 */
	public static function articleDeleteHook(&$article, &$user, $reason){
		if ($article->getTitle()->getNamespace() != SMW_NS_WEB_SERVICE) {
			return true;
		}
		$ws = DIWebService::newFromID($article->getID());
		if ($ws) {
			//triplification processing
			global $smwgDIIP;
			//deal with triplification
			$articles = difGetWSStore()->getWSArticles($article->getID(), new SMWRequestOptions());
			DIWSTriplifier::getInstance()->removeWS($article->getID(), $articles);
				
			DIWebServiceCache::removeWS($ws->getArticleID());
				
			$options = new SMWRequestOptions();
			$pageIds = difGetWSStore()->getWSArticles($ws->getArticleID(), $options);
			foreach($pageIds as $articleId){
				$usedWSs = difGetWSStore()->getWSsUsedInArticle($articleId);
				foreach($usedWSs as $usedWS){
					if($usedWS[0] == $ws->getArticleID()){
						difGetWSStore()->removeWSArticle(
						$ws->getArticleID(), $usedWS[1], $articleId);
						$parameterSetIds = difGetWSStore()->getUsedParameterSetIds($usedWS[1]);
						if(sizeof($parameterSetIds) == 0){
							difGetWSStore()->removeParameterSet($usedWS[1]);
						}
					}
				}
			}

			$ws->removeFromDB();
		}
		self::$mNewWebService = null;
		self::$mOldWebservice = null;
		return true;
	}

	/**
	 * this method checks if a wwsd was deleted or modified
	 * and therefore has to be removed from the database
	 *
	 * @param WebService $mNewWebService
	 * @return boolean
	 */
	public static function detectModifiedWWSD($mNewWebService){
		if(self::$mOldWebservice){
			if(!$mNewWebService){
				return true;
			}
				
			$remove = false;
			if(self::$mOldWebservice->getArticleID() != $mNewWebService->getArticleID()){
				$remove = true;
			} else if(self::$mOldWebservice->getMethod() != $mNewWebService->getMethod()){
				$remove = true;
			} else if(self::$mOldWebservice->getParameters() != $mNewWebService->getParameters()){
				$remove = true;
			} else if(self::$mOldWebservice->getProtocol() != $mNewWebService->getProtocol()){
				$remove = true;
			} else if(self::$mOldWebservice->getResult() != $mNewWebService->getResult()){
				$remove = true;
			} else if(self::$mOldWebservice->getURI() != $mNewWebService->getURI()){
				$remove = true;
			}
			return $remove;
		}
		return false;
	}

	/*
	 * remembers the WWSD in a static variable
	 */
	public static function rememberWWSD(&$ws){
		self::$mOldWebservice = $ws;
		self::$mOldWebserviceRemembered = true;
	}

	/**
	 * This function is called, when a <WebService>-tag for a WWSD has been
	 * found in an article. If the content of the definition is correct, and
	 * if the namespace of the article is the WebService namespace, the WWSD
	 * will be stored in the database.
	 *
	 * @param string $input
	 * 		The content of the tag
	 * @param array $args
	 * 		Array of attributes in the tag
	 * @param Parser $parser
	 * 		The wiki text parser
	 * @return string
	 * 		The text to be rendered
	 */
	public static function wwsdParserHook($input, $args, $parser) {
		global $smwgDIIP;
		
		$wwsd = DIWebService::newFromID($parser->getTitle()->getArticleID());
		self::rememberWWSD($wwsd);
	
		$attr = "";
		foreach ($args as $k => $v) {
			$attr .= " ". $k . '="' . $v . '"';
		}
		$completeWWSD = "<WebService$attr>".$input."</WebService>\n";
	
		$notice = '';
		$name = $parser->mTitle->getText();
		$id = $parser->mTitle->getArticleID();
		
		$ws = DIWebService::newFromWWSD($name, $completeWWSD);
		
		$errors = null;
		$warnings = null;
		if (is_array($ws)) {
			$errors = $ws;
		} else {
			// A web service object was returned. Validate the definition
			// with respect to the WSDL.
			
			$res = $ws->validateWWSD();
			
			if (is_array($res)) {
				// Warnings were returned
				$warnings = $res;
			} else if (is_string($res)){
				$errors = array($res);
			}
		}
		$msg = "";
		if ($errors != null || $warnings != null) {
			// Errors within the WWSD => show them as a bullet list
			$ew = $errors ? $errors : $warnings;
			$msg = '<h4><span class="mw-headline">'.wfMsg('smw_wws_wwsd_errors').'</h4><ul>';
			foreach ($ew as $err) {
				$msg .= '<li>'.$err.'</li>';
			}
			$msg .= '</ul>';
			if ($errors) {
				return '<h4><span class="mw-headline">Web Service Definition</span></h4>'.
						"<pre>\n".htmlspecialchars($completeWWSD)."\n</pre><br />". $msg;
			}
		}
		if ($parser->mTitle->getNamespace() == SMW_NS_WEB_SERVICE) {
			// store the WWSD in the database in the hook function <articleSavedHook>.
			self::$mNewWebService = $ws;
		} else {
			// add message: namespace webService needed.
			$notice = "<b>".wfMsg('smw_wws_wwsd_needs_namespace')."</b>";
		}
	
		return  '<h4><span class="mw-headline">Web Service Definition</span></h4>'
			."<pre>\n".htmlspecialchars($completeWWSD)."\n</pre>".$notice.$msg; 
	}
	
}
