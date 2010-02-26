<?php
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
 * @author: Kai Kühn / ontoprise / 2009
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
	 * @param int $old_article_id
	 * @param int $new_article_id
	 */
	public function updateRules($old_article_id, $new_article_id) {
		// no impl
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
