<?php
/*  Copyright 2011, ontoprise GmbH
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
 * This file contains common classes for all test cases.
 * 
 * @author Thomas Schweitzer
 * Date: 02.02.2011
 * 
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the HaloACL extension. It is not a valid entry point.\n" );
}

 //--- Includes ---

/**
 * 
 * This class creates and deletes articles that are used in test case.
 * 
 * @author Thomas Schweitzer
 * 
 */
class ArticleManager {
	
	//--- Constants ---
	//--- Private fields ---
	// List of articles that were added during a test.
	private $mAddedArticles = array();

	private $mBaseArticles = array(
//------------------------------------------------------------------------------		
			'Category:ACL/Group' =>
<<<ACL
This is the category for groups.
ACL
,
//------------------------------------------------------------------------------		
			'Category:ACL/Right' =>
<<<ACL
This is the category for rights.
ACL
,
//------------------------------------------------------------------------------		
			'Category:ACL/ACL' =>
<<<ACL
This is the category for security descriptors.
ACL
,
);
	
		
	/**
	 * Constructor for  ArticleManager
	 *
	 */		
	function __construct() {
	}
	

	//--- getter/setter ---
	public function getAddedArticles()	{ return $this->mAddedArticles; }
	
	//--- Public methods ---
	
	/**
	 * Adds the article with the name $articleName for later deletion.
	 * 
	 * @param string $articleName
	 * 	Full name of the article
	 */
	public function addArticle($articleName) {
		$this->mAddedArticles[] = $articleName;
	}
	
	/**
	 * Creates articles as a given user. The names of all created articles are 
	 * stored so that they can be deleted later.
	 * 
	 * @param array(string => string) $articles
	 * 		A map from article names to their content.
	 * @param string $user
	 * 		Name of the user who will create the articles
	 * @param array(string) $orderOfArticles
	 * 		If specified, the articles are created in the order given by the 
	 * 		article names in this array.
	 */
	public function createArticles($articles, $user, $orderOfArticles = null) {
    	global $wgUser;
    	$wgUser = User::newFromName($user);
    	
    	if (!is_null($orderOfArticles)) {
	    	foreach ($orderOfArticles as $name) {
	    		$this->createArticle($name, $articles[$name]);
	    		$this->mAddedArticles[] = $name;
	    	}
    	} else {
	    	foreach ($articles as $name => $content) {
	    		//	    		$pf = HACLParserFunctions::getInstance();
	    		//	    		$pf->reset();
	    		$this->createArticle($name, $content);
	    		$this->mAddedArticles[] = $name;
	    	}
    	}
    }
    
    /**
     * Creates the base articles that are needed for HaloACL.
     * 
     * @param string $user
     * 		The name of the user who creates this article. 
     */
    public function createACLBaseArticles($user) {
    	$this->createArticles($this->mBaseArticles, $user);
    }
	
    /**
     * Delete all articles that were created during a test.
     */
    function deleteArticles($user) {
   		global $wgUser, $wgOut;
    	$wgUser = User::newFromName($user);
    	
		foreach ($this->mAddedArticles as $a) {
		    $t = Title::newFromText($a);
		    $wgOut->setTitle($t); // otherwise doDelete() will throw an exception
	    	$article = new Article($t);
			$article->doDelete("Testing");
		}
		$this->mAddedArticles[] = array();
    }
	
	/**
	 * Checks if the article with the given name $title exists.
	 * 
	 * @param string $title
	 * 		Name of the article.
	 * @return
	 * 		<true>, if the article exists
	 * 		<false> otherwise
	 */
	public static function articleExists($title) {
		$title = Title::newFromText($title);
		return $title->exists();
	}
    

	//--- Private methods ---
    /**
     * Creates the article with the name $title and the given $content.
     * @param string $title
     * 		Name of the article
     * @param string $content
     * 		Content of the article
     */
    private function createArticle($title, $content) {
	
    	global $wgTitle;
		$wgTitle = Title::newFromText($title);
		$article = new Article($wgTitle);
		HACLParserFunctions::getInstance()->reset();
		// Set the article's content
		$status = $article->doEdit($content, 'Created for test case', 
		                            $article->exists() ? EDIT_UPDATE : EDIT_NEW);
		if (!$status->isOK()) {
			echo "Creating article ".$wgTitle->getFullText()." failed\n";
		}
	}
    
}

/**
 * This class contains common functions for testing HaloACL.
 * @author thsc
 *
 */
class HaloACLCommon {

	/**
	 * Evaluates rights with the userCan hook.
	 * 
	 * @param PHPUnit_Framework_TestCase $testInstance
	 * 		The instance of the test case.
	 * @param string $testcase
	 * 		Name of the test case.
	 * @param array<array<string, string, string, bool>> $expectedResults
	 * 		An array of settings to test and the expected result. Each array
	 * 		consists of:
	 * 		0 - article name
	 * 		1 - user name
	 * 		2 - action
	 * 		3 - expected result
	 */
	public static function checkRights($testInstance, $testcase, $expectedResults) {
		foreach ($expectedResults as $er) {
			$articleName = $er[0];
			$user = $username = $er[1];
			$action = $er[2];
			$res = $er[3];
			
			$etc = haclfDisableTitlePatch();			
			$article = Title::newFromText($articleName);
			haclfRestoreTitlePatch($etc);			
			
			$user = $user == '*' ? new User() : User::newFromName($user);
			unset($result);
			HACLEvaluator::userCan($article, $user, $action, $result);
			if (is_null($result)) {
				$result = true;
			}
			
			$testInstance->assertEquals($res, $result, "Test of rights failed for: $article, $username, $action (Testcase: $testcase)\n");
			
		}
	}
	
	/**
	 * Creates new users with the given names and the password 'test'.
	 * @param array<string> $userNames
	 * 		Names of users to create
	 */
	public static function createUsers(array $userNames) {
		foreach ($userNames as $u) {
    		User::createNew($u, array('password' => User::crypt('test')));
		}
	}
	
}