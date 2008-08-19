<?php
global $wgHooks;
$wgHooks['LanguageGetMagic'][] = 'wfTreeGeneratorLanguageGetMagic';

// name of tree generator parser function
define ('GENERATE_TREE_PF', 'generateTree');

class TreeGenerator {

	/**
	 * Register parser function for tree generation
	 *
	 */
	public function __construct() {
		global $wgTreeView5Magic, $wgParser;
		$wgParser->setFunctionHook( GENERATE_TREE_PF, array($this,'generateTree'));

	}

	/**
	 * Entry point for parser function
	 *
	 * @param unknown_type $parser
	 * @return String Wiki-Tree
	 */
	public function generateTree(&$parser){
		global $wgUser;
		$params = func_get_args();
		array_shift( $params ); // we already know the $parser ...
		$genTreeParameters = array();
		foreach($params as $p) {
			$keyValue = explode("=", $p);
			if (count($keyValue) != 2) continue;
			$genTreeParameters[$keyValue[0]] = $keyValue[1];
		}
		if (!array_key_exists('property', $genTreeParameters)) return "";
		$relationName = Title::newFromText($genTreeParameters['property'], SMW_NS_PROPERTY);
		if (array_key_exists('category', $genTreeParameters)) {
			$genTreeParameters['category'] = str_replace("{{{USER-NAME}}}", $wgUser != NULL ? $wgUser->getName() : "", $genTreeParameters['category']);
			$categoryName = Title::newFromText($genTreeParameters['category'], NS_CATEGORY);
		} else {
			$categoryName = NULL;
		}
		$start = array_key_exists('start', $genTreeParameters) ? Title::newFromText($genTreeParameters['start']) : NULL;
		$result = "";
		$tv_store = TreeviewStorage::getTreeviewStorage();
		$tree = $tv_store->getHierarchyByRelation($relationName, $categoryName, $start);
		$maxDepth = array_key_exists('maxDepth', $genTreeParameters) ? $genTreeParameters['maxDepth'] : NULL;
		if ($maxDepth > 0) $redirectPage = Title::newFromText($genTreeParameters['redirectPage']);
		$displayProperty = array_key_exists('display', $genTreeParameters) ? Title::newFromText($genTreeParameters['display'], SMW_NS_PROPERTY) : NULL;
		$startLevel = array_key_exists('level', $genTreeParameters) ? $genTreeParameters['level'] : 1;
		$hchar = "";
		for($i = 0; $i < $startLevel; $i++) $hchar .= '*';
		$this->dumpTree($tree, $result, $maxDepth, $redirectPage, $displayProperty, $hchar);
		return $result;
	}

	/**
	 * Recursive tree generator function.
	 *
	 * @param TreeNode $tree
	 * @param String $result
	 * @param int $maxDepth
	 * @param Title $redirectPage
	 * @param Title $displayProperty
	 * @param String $hchar
	 */
	private function dumpTree($tree, &$result, $maxDepth, $redirectPage, $displayProperty, $hchar='*') {
		if ($maxDepth === NULL || $maxDepth >= 0) {
			foreach($tree->children as $n) {
				if ($displayProperty == NULL) {
					$result .= $hchar."[[".$n->title->getPrefixedText()."]]\n";
				} else {
					$smwValues = smwfGetStore()->getPropertyValues($n->title, $displayProperty);
					if (count($smwValues) > 0) {
						$result .= $hchar."[[".$n->title->getPrefixedText()."|".$smwValues[0]->getXSDValue()."]]\n";
					} else {
						$result .= $hchar."[[".$n->title->getPrefixedText()."]]\n";
					}
				}
				if ($maxDepth !== NULL) $maxDepth--;
				$this->dumpTree($n, $result, $maxDepth, $redirectPage, $displayProperty, $hchar.'*');
			}
		} else if ($maxDepth < 0 && $redirectPage !== NULL) {
			$result .= $hchar."[[".$redirectPage->getPrefixedText()."|...]]\n";
		}
	}


}

abstract class TreeviewStorage {
    
	private static $store;
	/**
	 * Returns hierrachy of Titles connected by given relation.
	 *
	 * @param Title $relation Connector relation
	 * @param Title $category Category constraint (optional)
	 * @param Title $start Article to start (optional)
	 * @return Tree of TreeNode objects
	 */
	public abstract function getHierarchyByRelation(Title $relation, $category = NULL, $start = NULL);
	
    public static function getTreeviewStorage() {
        global $smwgHaloIP;
        if (self::$store == NULL) {
            global $smwgDefaultStore;
            switch ($smwgDefaultStore) {
                case (SMW_STORE_TESTING):
                    self::$store = null; // not implemented yet
                    trigger_error('Testing store not implemented for HALO extension.');
                    break;
                case ('SMWHaloStore2'):
                    self::$store = new TreeviewStorageSQL2();
                    break;
                case ('SMWHaloStore'): default:
                    self::$store = new TreeviewStorageSQL();
                    break;
            }
        }
        return self::$store;
    }
}

class TreeviewStorageSQL extends TreeviewStorage {

	public function getHierarchyByRelation(Title $relation, $category = NULL, $start = NULL) {
		$db =& wfGetDB( DB_MASTER );
		$smw_relations = $db->tableName('smw_relations');
		$page = $db->tableName('page');
		$categorylinks = $db->tableName('categorylinks');
		 
		$tree = new TreeNode();
		$treelevel = &$tree->children;

		$categoryConstraint = "";
		$categoryConstraintWhere = "";
		if ($category != NULL) {
			$categoryConstraint = " JOIN $page ON r1.object_namespace = page_namespace AND r1.object_title = page_title JOIN $categorylinks ON page_id = cl_from";
			$categoryConstraintWhere = " AND cl_to =".$db->addQuotes($category->getDBkey());
		}
		if ($start == NULL) {
			// query for root pages
			$res = $db->query('SELECT r1.object_namespace AS ns, r1.object_title AS title FROM '.$smw_relations.' r1 '.$categoryConstraint.' WHERE r1.relation_title='.$db->addQuotes($relation->getDBkey()).$categoryConstraintWhere.
                       ' AND NOT EXISTS (SELECT r2.subject_id FROM '.$smw_relations.' r2 WHERE r1.object_title=r2.subject_title AND r1.object_namespace = r2.object_namespace AND r2.relation_title = '.$db->addQuotes($relation->getDBkey()).') GROUP BY ns,title');
			if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
					$treelevel[] = new TreeNode(Title::newFromText($row->title, $row->ns));
				}
			}
			$db->freeResult($res);
		} else {
			$treelevel[] = new TreeNode($start);
		}
		$visitedNodes = array();
		foreach($treelevel as $n) {
			$this->_getHierarchyByRelation($relation, $category, $n, $visitedNodes);
		}
		return $tree;
	}

	/**
	 * Returns hierrachy of Titles connected by given relation.
	 *
	 * @param Title $relation
	 * @param Title $category
	 * @param TreeNode $node Current node
	 * @param Array of String $visitedNodes
	 */
	private function _getHierarchyByRelation(Title $relation, $category, & $node, & $visitedNodes) {
		$db =& wfGetDB( DB_MASTER );
		$smw_relations = $db->tableName('smw_relations');
		$categorylinks = $db->tableName('categorylinks');
		if (in_array($node->title->getDBkey().$node->title->getNamespace(), $visitedNodes)) {
			return;
		}
		$categoryConstraint = "";
		$categoryConstraintWhere = "";
		if ($category != NULL) {
			$categoryConstraint = " JOIN $categorylinks ON subject_id = cl_from";
			$categoryConstraintWhere = " AND cl_to =".$db->addQuotes($category->getDBkey());
		}
		$visitedNodes[] = $node->title->getDBkey().$node->title->getNamespace();
		$treelevel = &$node->children;
		// query for root pages
		$res = $db->query('SELECT r1.subject_namespace AS ns, r1.subject_title AS title FROM '.$smw_relations.' r1 '.$categoryConstraint.' WHERE r1.relation_title='.$db->addQuotes($relation->getDBkey()).
                   ' AND r1.object_title='.$db->addQuotes($node->title->getDBkey()).' AND r1.object_namespace = '.$node->title->getNamespace().$categoryConstraintWhere);
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$treelevel[] = new TreeNode(Title::newFromText($row->title, $row->ns));
			}
		}
		$db->freeResult($res);
		foreach($treelevel as $n) {
			$this->_getHierarchyByRelation($relation, $category, $n, $visitedNodes);
		}
		array_pop($visitedNodes);
	}
}

class TreeviewStorageSQL2 extends TreeviewStorageSQL {
	
	public function getHierarchyByRelation(Title $relation, $category = NULL, $start = NULL) {
		$db =& wfGetDB( DB_MASTER );
		$smw_rels2 = $db->tableName('smw_rels2');
        $smw_ids = $db->tableName('smw_ids');
        $page = $db->tableName('page');
        $categorylinks = $db->tableName('categorylinks');
		 
		$tree = new TreeNode();
		$treelevel = &$tree->children;

		$categoryConstraint = "";
		$categoryConstraintWhere = "";
		if ($category != NULL) {
			$categoryConstraint = " JOIN $page ON i3.smw_title = page_title AND i3.smw_namespace = page_namespace JOIN $categorylinks ON page_id = cl_from ";
			$categoryConstraintWhere = " AND cl_to =".$db->addQuotes($category->getDBkey());
		}
		if ($start == NULL) {
			// query for root pages
			$res = $db->query('SELECT i.smw_namespace AS ns, i.smw_title AS title '.
			                 'FROM '.$smw_rels2.' r '.
			                 'JOIN '.$smw_ids.' i ON r.o_id = i.smw_id '.
			                 'JOIN '.$smw_ids.' i2 ON r.p_id = i2.smw_id '.
			                 'JOIN '.$smw_ids.' i3 ON r.s_id = i3.smw_id '.$categoryConstraint.
			                 'WHERE i2.smw_title='.$db->addQuotes($relation->getDBkey()).$categoryConstraintWhere.
                             ' AND NOT EXISTS (SELECT s_id FROM '.$smw_rels2.' r2 JOIN '.$smw_ids.' i4 ON r2.p_id = i4.smw_id WHERE r.o_id=r2.s_id AND i4.smw_title = '.$db->addQuotes($relation->getDBkey()).') GROUP BY ns,title');
			if($db->numRows( $res ) > 0) {
				while($row = $db->fetchObject($res)) {
					$treelevel[] = new TreeNode(Title::newFromText($row->title, $row->ns));
				}
			}
			$db->freeResult($res);
		} else {
			$treelevel[] = new TreeNode($start);
		}
		$visitedNodes = array();
		foreach($treelevel as $n) {
			$this->_getHierarchyByRelation($relation, $category, $n, $visitedNodes);
		}
		return $tree;
	}

	/**
	 * Returns hierrachy of Titles connected by given relation.
	 *
	 * @param Title $relation
	 * @param Title $category
	 * @param TreeNode $node Current node
	 * @param Array of String $visitedNodes
	 */
	private function _getHierarchyByRelation(Title $relation, $category, & $node, & $visitedNodes) {
		$db =& wfGetDB( DB_MASTER );
		$smw_rels2 = $db->tableName('smw_rels2');
		$smw_ids = $db->tableName('smw_ids');
		$page = $db->tableName('page');
		$categorylinks = $db->tableName('categorylinks');
		if (in_array($node->title->getDBkey().$node->title->getNamespace(), $visitedNodes)) {
			return;
		}
		$categoryConstraint = "";
		$categoryConstraintWhere = "";
		if ($category != NULL) {
			$categoryConstraint = " JOIN $page ON i3.smw_title = page_title AND i3.smw_namespace = page_namespace JOIN $categorylinks ON page_id = cl_from";
			$categoryConstraintWhere = " AND cl_to =".$db->addQuotes($category->getDBkey());
		}
		$visitedNodes[] = $node->title->getDBkey().$node->title->getNamespace();
		$treelevel = &$node->children;
		// query for root pages
		$query = 'SELECT i3.smw_namespace AS ns, i3.smw_title AS title '.
                             'FROM '.$smw_rels2.' r '.
                             'JOIN '.$smw_ids.' i ON r.o_id = i.smw_id '.
                             'JOIN '.$smw_ids.' i2 ON r.p_id = i2.smw_id '.
                             'JOIN '.$smw_ids.' i3 ON r.s_id = i3.smw_id '.$categoryConstraint.
                             ' WHERE i2.smw_title='.$db->addQuotes($relation->getDBkey()).' AND i.smw_title = '.$db->addQuotes($node->title->getDBkey()).' AND i.smw_namespace = '.$node->title->getNamespace().$categoryConstraintWhere;
		$res = $db->query($query);
		
		if($db->numRows( $res ) > 0) {
			while($row = $db->fetchObject($res)) {
				$treelevel[] = new TreeNode(Title::newFromText($row->title, $row->ns));
			}
		}
		$db->freeResult($res);
		foreach($treelevel as $n) {
			$this->_getHierarchyByRelation($relation, $category, $n, $visitedNodes);
		}
		array_pop($visitedNodes);
	}
}
/**
 * Helper class for representing a Tree of Titles.
 *
 */
class TreeNode {

	public function __construct($title = NULL) {
		$this->title = $title;
		$this->children = array();
	}
	public $title;
	public $children;
}

function wfTreeGeneratorLanguageGetMagic(&$magicWords,$langCode = 0) {
	$magicWords[GENERATE_TREE_PF] = array( 0, GENERATE_TREE_PF );
	return true;
}
?>