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
if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	die( "This script must be run from the command line\n" );
}
/**
 * This file contains common classes for all test cases.
 *
 * @author Thomas Schweitzer
 * Date: 02.02.2011
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is part of the SMWHalo extension. It is not a valid entry point.\n" );
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
				$this->createArticle($name, $content);
				$this->mAddedArticles[] = $name;
			}
		}
	}

	/**
	 * Moves articles as a given user. The names of all moved articles are
	 * stored so that they can be deleted later.
	 *
	 * @param array(string => string) $articles
	 * 		A map from article names from source to target name.
	 */
	public function moveArticles($articles) {
		$linkCache = LinkCache::singleton();
		foreach ($articles as $source => $target) {
			// Clear the link cache as it returns wrong title IDs otherwise.
			$linkCache->clear();

			// We must use newFromURL as newFromText uses an internal cache
			// that contains old IDs
			$st = Title::newFromURL($source);
			$st->moveNoAuth(Title::newFromURL($target));
			$this->mAddedArticles[] = $target;
		}
	}


	/**
	 * Deletes the articles given in $articles as user $user. If $articles is null,
	 * all articles that were created during a test are deleted.
	 *
	 * @param string $user
	 * 		User who deletes the articles
	 * @param array(string) $articles
	 * 		Names of the articles that will be deleted.
	 */
	function deleteArticles($user, $articles = null) {
		global $wgUser, $wgOut;
		$wgUser = User::newFromName($user);
		 
		if (is_null($articles)) {
			$articles = $this->mAddedArticles;
		}
		foreach ($articles as $a) {
			$t = Title::newFromText($a);
			$wgOut->setTitle($t); // otherwise doDelete() will throw an exception
			$article = new Article($t);
			$article->doDelete("Testing");
			$key = array_search($a, $this->mAddedArticles);
			if ($key !== false) {
				unset($this->mAddedArticles[$key]);
			}
		}
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

	/**
	 * Imports the articles that are stored in the wiki dump with the given
	 * $filename.
	 * @param string $filename
	 * 		Name of the file that contains the wiki dump.
	 */
	public function importArticles($filename) {
		$source = ImportStreamSource::newFromFile($filename);
		if ($source->isOK()) {
			$source = $source->value;
		}
		$importer = new WikiImporter($source);
		$result = $importer->doImport();

		if ($result) {
			// Import was successful
			// => get the names of imported articles
			$doc = new DOMDocument();
			$doc->load($filename);
			$entries = $doc->getElementsByTagName('title');
			foreach ($entries as $entry) {
				$article = $entry->nodeValue;
				$this->mAddedArticles[] = $article;
			}
		}
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
		// Set the article's content
		$status = $article->doEdit($content, 'Created for test case',
		$article->exists() ? EDIT_UPDATE : EDIT_NEW);
		if (!$status->isOK()) {
			echo "Creating article ".$wgTitle->getFullText()." failed\n";
		}
	}

}

/**
 * This class contains common functions for testing SMWHalo.
 * @author thsc
 *
 */
class SMWHaloCommon {

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
