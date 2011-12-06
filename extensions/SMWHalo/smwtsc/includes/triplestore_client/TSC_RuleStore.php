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
 * @ingroup SMWHaloTriplestore
 * 
 * SMWRuleStore is an abstraction for a rule store. This dummy implementation
 * does nothing. It has to be implemented by other extensions.
 *
 * $smwgDefaultRuleStore must be set to the implementator class name
 * This class should be loaded by autoload mechanism.
 *
 * @author: Kai K�hn
 *
 */
class SMWRuleStore {
	private static $INSTANCE = NULL;

	public static function getInstance() {
		if (self::$INSTANCE == NULL) {
			global $smwgDefaultRuleStore;
			self::$INSTANCE = !isset($smwgDefaultRuleStore) ? new SMWRuleStore() : new $smwgDefaultRuleStore();
		}
		return self::$INSTANCE;
	}

	/**
	 * Returns rule from local rule store for a given page id.
	 *
	 * @param int $page_id
	 * @return array of rule_id
	 */
	public function getRules($page_id) {
		$results = array(); //dummy impl
		return $results;
	}
	
	/**
	 * Returns all rules 
	 * 
	 * @return int[] $pageID
	 */
	public function getAllRulePages() {
		$results = array(); //dummy impl
        return $results;
	}

	/**
	 * Adds new rules to the local rule store.
	 *
	 * @param int $article_id
	 * @param array $new_rules (ruleID => ruleText)
	 */
	public function addRules($article_id, $new_rules) {
		// no impl
	}

	/**
	 * Removes rule from given article
	 *
	 * @param int $article_id
	 */
	public function clearRules($article_id) {
		// no impl
	}

	/**
     * Updates article IDs. In case of a renaming operation.
     *
     * @param int $new_article_id
     * @param int $old_article_id
     * @param Title $newTitle
     * 
     * @return tuple(old rule URI, new rule URI)
     */
    public function updateRules($new_article_id, $old_article_id, $newTitle) {
        // no impl
        return array();
    }

	/**
	 * Setups database tables for semantic rules extension.
	 *
	 * @param boolean $verbose
	 */
	public function setup($verbose) {
		// noimpl
	}

	/**
	 * Drops database tables for semantic rules extension.
	 *
	 * @param boolean $verbose
	 */
	public function drop($verbose) {
		// noimpl
	}
}
