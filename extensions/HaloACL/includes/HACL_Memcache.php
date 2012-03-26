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
 * @ingroup HaloACL
 * 
 * This file contains the class HACLMemcache
 * 
 * @author Thomas Schweitzer
 * Date: 24.02.2012
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the IAI module. It is not a valid entry point.\n" );
}


 //--- Includes ---

/**
 * The HACLMemcache stores evaluation results of HaloACl and access rights for
 * articles in memcache if it is enabled.
 * 
 * This class is a singleton.
 * 
 * @author Thomas Schweitzer
 * 
 */
class HACLMemcache {
	
	//--- Constants ---
	const HALOACLKEYS_KEY = 'HaloACL';
	const HALOACL_GROUP_PERMISSION_KEY = "GroupPermissionHash";
		
	//--- Private fields ---
	
	// @staticvar HACLMemcache The only instance of this class
	private static $mInstance = null;
	
	// @var	boolean true if the HaloACL memcache is enabled. However this does
	//		not control the general memcache of MediaWiki. If this is disabled
	//		the HaloACL memcache won't work either.
	private $mMemcacheEnabled = true;
	
	// @var boolean The memcache has to be disabled if HaloACL uses its dynamic
	//		features (e.g. dynamic group membership). In a web request the methods
	//		of the memcache will be called several times but the check for 
	//		dynamic features should only performed once. This field is true, if
	//		the condition were already checked in the current web request.
	private $mMemcacheConditionsCheckedForRequest = false;
	
	// @var boolean MediaWiki's $wgGroupPermissions influence the access rights
	//		and thus the validity of the corresponding memcache entries. A hash
	//		value for the content of the group permissions is stored in memcache.
	//		If it does no longer match the current permissions, the cache must
	//		be purged. However the check for this should only happen once for a
	//		web request. This field is true, if	the hash code was already checked 
	//		in the current web request.
	private $mGroupPermissionsChecked = false;
	
	/**
	 * Constructor for HACLMemcache
	 *
	 */		
	function __construct() {
	}
	

	//--- getter/setter ---
	
	
	//--- Public methods ---
	
	/**
	 * Returns the singleton instance of this class
	 * @return HACLMemcache
	 * 		The instance of this class
	 */
	public static function getInstance() {
		if (is_null(self::$mInstance)) {
			self::$mInstance = new self;
		}
		return self::$mInstance;
	}

	/**
	 * Enables or disables the HaloACL memcache. The previous state is returned.
	 * 
	 * @access public
	 * @param boolean $enable
	 * 		true  => enable the memcache
	 * 		false => disable the memcache
	 * @return boolean
	 * 		The previous state of the memcache. 
	 */
	public function enableMemcache($enable) {
		// We have to check the conditions for using memcache again 
		$this->mMemcacheConditionsCheckedForRequest = false;
		// as well as the group permissions
		$this->mGroupPermissionsChecked = false;
		 
		$oldState = $this->mMemcacheEnabled;
		$this->mMemcacheEnabled = $enable;
		return $oldState;	
	}
	
	/**
	 * 
	 * Tries to store a permission for a $user on a $title with the given
	 * $action in memcache. The $title may also be a namespace index.
	 * 
	 * @param User $user
	 * 		A user object
	 * @param Title/int $title
	 * 		The title for which the permission is stored. If an integer is
	 * 		passed, it is the index of a namespace.
	 * @param String $action
	 * 		The action that shall be performed on the title
	 * @param boolean $permitted
	 * 		If the action is permitted (true) or not (false)
	 * @return boolean
	 * 		true, if the permission was successfully stored
	 * 		false otherwise (possibly because memcache is disabled)
	 * @access public
	 */
	public function storePermission($user, $title, $action, $permitted) {
		if (!$this->isMemcacheEnabled()) {
			return false;
		}
		$this->checkGroupPermissionsChanged();
				
		$key = $this->makeKey($user, $title, $action);
//echo "Stored permission in memcache: $key => $permitted<br />\n";
		$this->addHaloACLKey($key);
		global $wgMemc;
		return $wgMemc->set($key, $permitted ? '1' : '0');
	}
	
	/**
	 * 
	 * Tries to retrieve a permission for a $user on a $title with the given
	 * $action from the memcache. The $title may also be a namespace index.
	 * 
	 * @param User $user
	 * 		A user object
	 * @param Title/int $title
	 * 		The title for which the permission is stored. If an integer is
	 * 		passed, it is the index of a namespace.
	 * @param String $action
	 * 		The action that shall be performed on the title
	 * @param boolean $permitted
	 * 		If the action is permitted (true) or not (false)
	 * @return mixed boolean/integer
	 * 		true, if the action is permitted
	 * 		false, if the action is denied
	 * 		-1, if the memcache contains no information about the permission or
	 * 			if memcache is disabled
	 * @access public
	 */
	public function retrievePermission($user, $title, $action) {
		// Check if the memcache has to be disabled because of dynamic group
		// members or dynamic assignees 
		// or if the cache has to be purged because of changed $wgGroupPermissions
		if ($this->isMemcacheEnabled() 
		    && !$this->mMemcacheConditionsCheckedForRequest) {
			
			$this->checkGroupPermissionsChanged();
			$this->checkDynamicFeaturesUsed();
			$this->mMemcacheConditionsCheckedForRequest = true;
		}
		
		if (!$this->isMemcacheEnabled()) {
			return -1;
		}
		
		$key = $this->makeKey($user, $title, $action);
		
		global $wgMemc;
		$permission = $wgMemc->get($key);
//echo "Retrieved permission from memcache: $key => $permission<br />\n";		
		return $permission === '1' ? true
								 : ($permission === '0' ? false : -1);
	}
	
	
	/**
	 * This class stores all memcache keys it has created so far in memcache.
	 * This method returns all keys in an array.
	 * 
	 * @access public
	 * @return array of String or boolean
	 * 		array: all keys used by the HaloACL memcache
	 * 		false: if accessing memcache failed.
	 * 
	 */
	public function getHaloACLKeys() {
		if (!$this->isMemcacheEnabled()) {
			return false;
		}
		
		global $wgMemc;
		$keys = $wgMemc->get(wfMemcKey(self::HALOACLKEYS_KEY));
		if ($keys) {
			$keys = explode("\r\n", $keys);
		} else {
			$keys = array();
		}
		return $keys;
	}
	
	/**
	 * Registers all hooks that are needed to track changes in the wiki content 
	 * and HaloACL rights that affect the cache.
	 */
	public function setupHooks() {
		global $wgHooks;
		$wgHooks['ArticleSaveComplete'][] = 'HACLMemcache::onArticleSaveComplete';
		$wgHooks['ArticleDelete'][] = 'HACLMemcache::onArticleDelete';
		$wgHooks['HaloACLAddSecurityDescriptor'][] = 'HACLMemcache::onAddSecurityDescriptor';
		$wgHooks['HaloACLModifySecurityDescriptor'][] = 'HACLMemcache::onModifySecurityDescriptor';
		$wgHooks['HaloACLDeleteSecurityDescriptor'][] = 'HACLMemcache::onDeleteSecurityDescriptor';
		$wgHooks['HaloACLAddGroup'][] = 'HACLMemcache::onAddGroup';
		$wgHooks['HaloACLModifyGroup'][] = 'HACLMemcache::onModifyGroup';
		$wgHooks['HaloACLDeleteGroup'][] = 'HACLMemcache::onDeleteGroup';
		$wgHooks['HaloACLModifyWhitelist'][] = 'HACLMemcache::onModifyWhitelist';
		$wgHooks['HaloACLDeleteWhitelist'][] = 'HACLMemcache::onDeleteWhitelist';
		$wgHooks['TitleMoveComplete'][] = 'HACLMemcache::onMoveArticle';
		$wgHooks['AfterImportPage'][]   = 'HACLMemcache::onAfterImportPage';
	}
	
	/**
	 * Deletes all cache entries that were made by HaloACL.
	 */
	public function purgeCache() {
		if (!$this->isMemcacheEnabled()) {
			return;
		}
		
		$keys = $this->getHaloACLKeys();
		if ($keys) {
			global $wgMemc;
			foreach ($keys as $key) {
				$wgMemc->delete($key);
			}
			$this->purgeHaloACLKeys();
		}
	}
	
	/**
	 * Returns the names of all cache keys for the given $title.
	 * 
	 * @param Title $title
	 * 		A title object.
	 * 
	 * @return array(string)
	 * 		All keys that contain the title
	 */
	public function getCacheKeysForArticle(Title $title) {
		$id = preg_quote($title->getDBkey(), '/');
		$ns = $title->getNamespace();
		$allKeys = $this->getHaloACLKeys();
		if ($allKeys === false) {
			return array();
		}
		$regex = "/.*?\:.*?$ns\:$id\:.*?/";
		$keys = preg_grep($regex, $allKeys);
		return $keys;
	}
	
	/**
	 * Deletes all cache entries for the article with the given title.
	 * 
	 * @param Title $title
	 * 
	 */
	public function deleteEntriesForArticle(Title $title) {
		$keys = $this->getCacheKeysForArticle($title);
		if (count($keys) === 0) {
			// no keys => nothing to do
			return;
		}
		$this->deleteEntries($keys);
	}
	
	/**
	 * Deletes the cache entries with the given keys.
	 * 
	 * @param array(string) $keys
	 * 		Keys of the entries that should be deleted.
	 */
	public function deleteEntries(array $keys) {
		if (!$this->isMemcacheEnabled()) {
			return;
		}
		
		if ($keys) {
			// remove the given keys from the list of all keys
			$this->removeHaloACLKeys($keys);
		
			// Now remove the entries for each key.
			global $wgMemc;
			foreach ($keys as $key) {
				$wgMemc->delete($key);
			}
		}
		
	}

	
	/**
	 * This hook function is called after an article was saved.
	 * If it belongs to the namespace Category, the cache is purged. Otherwise 
	 * only the memcache entries for that article are removed.
	 * 
	 * @return boolean
	 * 		Returns always true.
	 */
	public static function onArticleSaveComplete(&$article) {
		$t = $article->getTitle();
		$ns = $t->getNamespace() == NS_CATEGORY;
		if ($ns) {
			// When a category is changed the whole hierarchy of categories might
			// be changed
			// => purge the whole cache
			self::getInstance()->purgeCache();
		} else {
			// For any other article just delete the entries that belong to it
			self::getInstance()->deleteEntriesForArticle($t);
		}
		return true;
	}
	
	/**
	 * This hook function is called when an article is deleted.
	 * If it belongs to the namespace Category, the cache is purged. Otherwise 
	 * only the memcache entries for that article are removed.
	 * 
	 * @return boolean
	 * 		Returns always true.
	 */
	public function onArticleDelete(&$article) {
		$t = $article->getTitle();
		$ns = $t->getNamespace() == NS_CATEGORY;
		if ($ns) {
			// When a category is changed the whole hierarchy of categories might
			// be changed
			// => purge the whole cache
			self::getInstance()->purgeCache();
		} else {
			// For any other article just delete the entries that belong to it
			self::getInstance()->deleteEntriesForArticle($t);
		}
		return true;
	}
	
	/**
	 * This hook function is called when an article is moved.
	 * If it belongs to the namespace Category, the cache is purged. Otherwise 
	 * only the memcache entries for that article are removed.
	 * 
	 * @return boolean
	 * 		Returns always true.
	 */
	public function onMoveArticle(Title &$oldTitle, Title &$newTitle) {
		$ns = $newTitle->getNamespace() == NS_CATEGORY;
		if ($ns) {
			// When a category is changed the whole hierarchy of categories might
			// be changed
			// => purge the whole cache
			self::getInstance()->purgeCache();
		} else {
			// For any other article just delete the entries that belong to it
			self::getInstance()->deleteEntriesForArticle($oldTitle);
		}
		return true;
	}
	
	/**
	 * This hook function is called when a new security descriptor is added.
	 * 
	 * @param Title $sdTitle
	 * 		Title object of the security descriptor
	 * @param string $nameOfPE
	 * 		Name of the object that is protected by the SD
	 * @param int $peType
	 * 		Type of the protected element
	 * 
	 * @return boolean
	 * 		Returns always true.
	 */
	public function onAddSecurityDescriptor($sdTitle, $nameOfPE, $peType) {
		self::getInstance()->sdChanged($sdTitle, $nameOfPE, $peType);
		return true;
	}
	
	/**
	 * This hook function is called when a new security descriptor is modified.
	 * 
	 * @param Title $sdTitle
	 * 		Title object of the security descriptor
	 * @param string $nameOfPE
	 * 		Name of the object that is protected by the SD
	 * @param int $peType
	 * 		Type of the protected element
	 * 
	 * @return boolean
	 * 		Returns always true.
	 */
	public function onModifySecurityDescriptor($sdTitle, $nameOfPE, $peType) {
		self::getInstance()->sdChanged($sdTitle, $nameOfPE, $peType);
		return true;
	}
	
	/**
	 * This hook function is called when a security descriptor is deleted.
	 * 
	 * @param Title $sdTitle
	 * 		Title object of the security descriptor
	 * @param string $nameOfPE
	 * 		Name of the object that is protected by the SD
	 * @param int $peType
	 * 		Type of the protected element
	 * 
	 * @return boolean
	 * 		Returns always true.
	 */
	public function onDeleteSecurityDescriptor($sdTitle, $nameOfPE, $peType) {
		self::getInstance()->sdChanged($sdTitle, $nameOfPE, $peType);
		return true;
	}
	
	/**
	 * This hook function is called when a new group is added
	 * 
	 * @param Title $groupTitle
	 * 		Title of the article that describes the new group
	 * 
	 * @return boolean
	 * 		Returns always true.
	 */
	public function onAddGroup(Title $groupTitle) {
		self::getInstance()->groupChanged($groupTitle);
		return true;
	}
	
	/**
	 * This hook function is called when a group was modified.
	 * 
	 * @return boolean
	 * 		Returns always true.
	 */
	public function onModifyGroup(Title $groupTitle) {
		self::getInstance()->groupChanged($groupTitle);
		return true;
	}
	
	/**
	 * This hook function is called when a group was deleted.
	 * 
	 * @return boolean
	 * 		Returns always true.
	 */
	public function onDeleteGroup(Title $groupTitle) {
		self::getInstance()->groupChanged($groupTitle);
		return true;
	}
	
	/**
	 * This hook function is called when the whitelist was modified.
	 * 
	 * @return boolean
	 * 		Returns always true.
	 */
	public function onModifyWhitelist() {
		self::getInstance()->purgeCache();
		return true;
	}
	
	/**
	 * This hook function is called when the whitelist was deleted.
	 * 
	 * @return boolean
	 * 		Returns always true.
	 */
	public function onDeleteWhitelist() {
		self::getInstance()->purgeCache();
		return true;
	}
	
	/**
	 * This hook function is called after pages were imported. The cache will be
	 * purged.
	 * 
	 * @return boolean
	 * 		Returns always true.
	 */
	public function onAfterImportPage() {
		self::getInstance()->purgeCache();
		return true;
	}
	
	
	//--- Private methods ---
	
	/**
	 * Generates a key for the permission of the given $user to perform the
	 * $action on the $title.
	 * 
	 * @param User $user
	 * 		A user object
	 * @param Title/int $title
	 * 		The title that for which the permission is stored. If an integer is
	 * 		passed, it is the index of a namespace.
	 * @param String $action
	 * 		The action that shall be performed on the title
	 * @return String
	 * 		A key for the given parameters.
	 * @access private
	 */
	private function makeKey($user, $title, $action) {
		if (is_int($title)) {
			$key = wfMemcKey($user->getName(), $title, $action);
		} else {
			$key = wfMemcKey($user->getName(), $title->getNamespace(), $title->getDBkey(), $action);
		}
		return $key;
	}
	
	/**
	 * We maintain a list of all keys that we added for HaloACL in memcache.
	 * This function adds the $key to this list if it not already exists. 
	 * @param string $key
	 * 		The key to add
	 */
	private function addHaloACLKey($key) {
		global $wgMemc;
		$usedKeysKey = wfMemcKey(self::HALOACLKEYS_KEY);
		
		// Check if we find data for the given key
		if (!$wgMemc->get($key)) {
				// No data available for key yet.
			if (!$wgMemc->_set('append', $usedKeysKey, $key."\r\n", 0)) {
				$success = $wgMemc->set($usedKeysKey, $key."\r\n", 0);
			}
		}
	}
	
	/**
	 * Removes the given set of keys from the list of all keys. The entries for
	 * these keys are not deleted.
	 * 
	 * @param array(string) $keys
	 * 		Keys to be removed.
	 * @return boolean
	 * 		true if successfull
	 * 		false otherwise, e.g. if memcaching is disabled.
	 */
	private function removeHaloACLKeys(array $keys) {
		if (!$this->isMemcacheEnabled()) {
			return false;
		}
		
		global $wgMemc;
		$usedKeysKey = wfMemcKey(self::HALOACLKEYS_KEY);
				
		// remove the given keys from the list of all keys
		$allKeys = $this->getHaloACLKeys();
		$allKeys = array_diff($allKeys, $keys);
		if (count($allKeys) === 0) {
			// no key remains => remove all keys
			return $this->purgeHaloACLKeys();
		}
		$allKeys = implode("\r\n", $allKeys);
		$success = $wgMemc->set($usedKeysKey, $allKeys."\r\n", 0);
		return $success;
	}
	
	/**
	 * Clears the list of all keys that are used by HaloACL in memcache.
	 */
	private function purgeHaloACLKeys() {
		global $wgMemc;
		return $wgMemc->delete(wfMemcKey(self::HALOACLKEYS_KEY));
	}
	
	
	/**
	 * Checks if memcache is enabled.
	 * @access private
	 * @return boolean
	 * 		true, if memcache is enabled
	 * 		false otherwise
	 */
	private function isMemcacheEnabled() {
		global $wgMemc;
		return $this->mMemcacheEnabled && ($wgMemc instanceof MWMemcached);
	}
	
	/**
	 * This function is called when a security descriptor was added, modified or
	 * deleted.
	 * The entries in the memcache that are affected by this change are deleted. 
	 * 
	 * @param Title $sdTitle
	 * 		Title object of the security descriptor
	 * @param string $nameOfPE
	 * 		Name of the object that is protected by the SD
	 * @param int $peType
	 * 		Type of the protected element
	 */
	private function sdChanged($sdTitle, $nameOfPE, $peType) {
		switch ($peType) {
			case HACLSecurityDescriptor::PET_PAGE:
				// Protection of a page changed
				$tpe = Title::newFromText($nameOfPE);
				// Does the page have subpages?
				if ($tpe->hasSubpages()) {
					// purge the cache
					$this->purgeCache();
				} else {
					// just remove the cache entries for the protected article
					$this->deleteEntriesForArticle($tpe);
				}
				break;
			case HACLSecurityDescriptor::PET_CATEGORY:
			case HACLSecurityDescriptor::PET_NAMESPACE:
			case HACLSecurityDescriptor::PET_RIGHT:
			case HACLSecurityDescriptor::PET_PROPERTY:
				// An ACL for a category, namespace, right or property changed
				$this->purgeCache();
				break;
				
		}
	}
	
	/**
	 * This function is called when a group was added, modified or deleted.
	 * All entries in the memcache are deleted. 
	 * 
	 * @param Title $groupTitle
	 * 		Title object of the group
	 */
	private function groupChanged($groupTitle) {
		$this->purgeCache();
	}
	
	/**
	 * Checks if HaloACL currently uses dynamic features like dynamic assignees
	 * or dynamic group members. If this is the case, the memcache is purged and
	 * disabled.
	 */
	private function checkDynamicFeaturesUsed() {
	
		$store = HACLStorage::getDatabase();
		if ($store->dynamicAssigneesUsed()
			|| $store->dynamicGroupMembersUsed()) {
			// Dynamic members are used
			// => clear the cache and disable it
			$this->purgeCache();
			$this->enableMemcache(false);
		}
	}
	
	/**
	 * Checks if the hash code for the content of $wgGroupPermissions has changed.
	 * If it did, the new hash code is stored and the cache is purged.
	 */
	private function checkGroupPermissionsChanged() {
		if ($this->mGroupPermissionsChecked) {
			// Check already done for this web request
			return;
		}
		$this->mGroupPermissionsChecked = true;
		
		// Create the hash value for the current $wgGroupPermissions
		global $wgGroupPermissions;
		$gp = print_r($wgGroupPermissions, true);
		$gp = md5($gp);
		
		// Compare the hash value with the value stored in memcache
		global $wgMemc;
		$gpkey = wfMemcKey(self::HALOACL_GROUP_PERMISSION_KEY);
		$hash = $wgMemc->get($gpkey);
		if (!$hash) {
			// No data available for key yet => store the current hash
			$wgMemc->set($gpkey, $gp, 0);
		} else {
			// Compare the hash values
			if ($hash !== $gp) {
				// Hash changed => purge the cache and store the new hash
				$this->purgeCache();
				$wgMemc->set($gpkey, $gp, 0);
			}
		}
		
	}
	
}
