<?php
/*  Copyright 2009, ontoprise GmbH
*  This file is part of the HaloACL-Extension.
*
*   The HaloACL-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The HaloACL-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This file contains the class HACLDefaultSD.
 * 
 * @author Thomas Schweitzer
 * Date: 22.05.2009
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the HaloACL extension. It is not a valid entry point.\n" );
}


 //--- Includes ---
 global $haclgIP;
//require_once("$haclgIP/...");

/**
 * This class manages the default security descriptor for users.
 * 
 * What happens when a user creates a new article? Does the user have to create 
 * the corresponding security descriptor or is it created automatically? 
 * And if so, what is its initial content?
 * 
 * "Default security descriptors" satisfy three scenarios:
 *    1. The wiki is by default an open wiki i.e. all new articles are accessible 
 *       by all users. Only if a page should be protected explicitly a security
 *       descriptor must be provided.
 *    2. New articles are automatically protected and belong to the author until
 *       he releases it. In this case a security descriptor must be created 
 *       automatically with an ACL that permits only access for the author.
 *    3. New articles are automatically protected and belong to users and groups
 *       that can be freely defined. In this case a security descriptor must be 
 *       created automatically with an ACL that can be configured. 
 * 
 * The solution for this is simple. Every user can define a template 
 * (not a MediaWiki template) for his default ACL. There is a special article 
 * with the naming scheme ACL:Template/<username> e.g. ACL:Template/Peter. This 
 * template article can contain any kind of valid ACL as described above. It can
 * define rights for the author alone or arbitrary combinations of users and 
 * groups.
 * 
 * If the user creates a new article, the system checks, if he has defined an
 * ACL template. If not, no security descriptor is created. This solves the 
 * problem of the first scenario, the open wiki. Otherwise, if the template 
 * exists, a security descriptor is created and filled with the content of the 
 * template. This serves the latter two scenarios.  
 * 
 * This class registers the hook "ArticleSaveComplete", which checks for each 
 * saved article, if a default SD has to be created.
 * 
 * @author Thomas Schweitzer
 * 
 */
class  HACLDefaultSD  {
	
	//--- Constants ---
//	const XY= 0;		// the result has been added since the last time
		
	//--- Private fields ---
	private $mXY;    		//string: comment
	
	/**
	 * Constructor for  HACLDefaultSD
	 *
	 * @param type $param
	 * 		Name of the notification
	 */		
	function __construct() {
//		$this->mXY = $xy;
	}
	

	//--- getter/setter ---
//	public function getXY()           {return $this->mXY;}

//	public function setXY($xy)               {$this->mXY = $xy;}
	
	//--- Public methods ---
	
	
	/**
	 * This method is called, after an article has been saved. If the article
	 * belongs to the namespace ACL (i.e. a right, SD, group or whitelist)
	 * it is ignored. Otherwise the following happens:
	 * - Check the namespace of the article (must not be ACL)
	 * - Check if $user is a registered user
	 * - Check if the article already has an SD
	 * - Check if the user has defined a default SD
	 * - Create the default SD for the article.
	 *
	 * @param Article $article
	 * 		The article which was saved
	 * @param User $user
	 * 		The user who saved the article
	 * @param string $text
	 * 		The content of the article
	 * 
	 * @return true
	 */
	public static function articleSaveComplete(&$article, &$user, $text) {
		
		if ($article->getTitle()->getNamespace() == HACL_NS_ACL) {
			// No default SD for articles in the namespace ACL
			return true;
		}
		
		if ($user->isAnon()) {
			// Don't create default SDs for anonymous users
			return true;
		}
		
		$articleID = $article->getTitle()->getArticleID();
		if (HACLSecurityDescriptor::getSDForPE($articleID, HACLSecurityDescriptor::PET_PAGE) !== false) {
			// There is already an SD for the article
			return true;
		}
		
		// Did the user define a default SD
		
		global $haclgContLang;
		
		$ns = $haclgContLang->getNamespaces();
		$ns = $ns[HACL_NS_ACL];
		$template = $haclgContLang->getSDTemplateName();
		$defaultSDName = "$ns:$template/{$user->getName()}";
		$etc = haclfDisableTitlePatch();
		$defaultSD = Title::newFromText($defaultSDName);
		haclfRestoreTitlePatch($etc);
		if (!$defaultSD->exists()) {
			// No default SD defined
			return true;
		}
		
		// Create the default SD for the saved article
		// Get the content of the default SD
		$defaultSDArticle = new Article($defaultSD);
		$content = $defaultSDArticle->getContent();
		
		// Create the new SD
		$newSDName = HACLSecurityDescriptor::nameOfSD($article->getTitle()->getFullText(),
													  HACLSecurityDescriptor::PET_PAGE);
		
		$etc = haclfDisableTitlePatch();
		$newSD = Title::newFromText($newSDName);
		haclfRestoreTitlePatch($etc);
		
		$newSDArticle = new Article($newSD);
		$newSDArticle->doEdit($content, "Default security descriptor.", EDIT_NEW);
		
		return true;
	}
	
	
	/**
	 * Checks if the given user can modify the given title, if it is a 
	 * default security descriptor.
	 *
	 * @param Title $title
	 * 		The title that is checked.
	 * @param User $user
	 * 		The user who wants to access the article.
	 * 
	 * @return array(bool rightGranted, bool hasSD)
	 * 		rightGranted:
	 * 			<true>, if title is the name for a default SD and the user is 
	 * 					allowed to create it or if it no default SD
	 * 			<false>, if title is the name for a default SD and the user is 
	 * 					 not allowed.
	 * 		hasSD:
	 * 			<true>, if the article is a default SD
	 * 			<false>, if not
	 */
	public static function userCanModify($title, $user) {
		// Check for default rights template
		if ($title->getNamespace() !== HACL_NS_ACL) {
			// wrong namespace
			return array(true, false);
		}
		global $haclgContLang;
		$prefix = $haclgContLang->getSDTemplateName();
		if (strpos($title->getText(), "$prefix/") !== 0) {
			// wrong prefix
			return array(true, false);
		}
		// article is a default rights template
		$userName = substr($title->getText(), strlen($prefix)+1);
		// Is this the template of another user?
		if ($user->getName() != $userName) {
			// no rights for other users but sysops and bureaucrats
			$groups = $user->getGroups();
			$r = (in_array('sysop', $groups) || in_array('bureaucrat', $groups));
			return array($r, true);
		}
		// user has all rights on the template
		return array(true, true);
				
	}
	
	//--- Private methods ---
}
