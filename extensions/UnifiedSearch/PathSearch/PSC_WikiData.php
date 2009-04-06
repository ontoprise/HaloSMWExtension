<?php

 /*
  * This object reads the appropriate tables of a wiki to find all existing categories and
  * properties. Also a structure is created that can tell whether a page of a certain category 
  * might have a certain property. Also the property types (domain, range) are considered when
  * trying to examine a relation.
  * All the ids (even not mentioned explicit in some comments) are smw_ids from the table smw_ids
  * column smw_id. The type of an id can be told by it's namespace, which is used here when defining
  * a type. Everything that has no namespace of NS_CATEGORY or SMW_NS_PROPERY must be a page,
  * including all the differen namespaces that a page can have. 
  */

 define('PSC_PROPERTY_NAME',   0);
 define('PSC_DOMAIN',          1);
 define('PSC_RANGE',           2);
 define('PSC_XSDTYPE',         3);
 define('PSC_VALUE_STRING',    4);
 define('PSC_CATEGORY_NAME',   0);
 define('PSC_SUBCATEGORY',     1);
 define('PSC_PARENT_CATEGORY', 2);

 class PSC_WikiData {
	private static $property;
	private static $category;
	
	private static $db;
	
	/**
	 * if called static, the constructor is not called but if someone
	 * used this object not static, the data is initialized at this moment.
	 */
	public function __construct() {
		self::initData();
	}
	
	/**
	 * initializing the data. 
	 * 
	 * @access public
	 */
	public static function initData() {

		if (self::$category == NULL) {
			self::$category == array();
			self::fetchCategories();
		}

		if (self::$property == NULL) {
			// just for faster access when building the properties, flip category names and ids
 			$cats = array();
 			foreach (array_keys(self::$category) as $id)
 				$cats[self::$category[$id][PSC_CATEGORY_NAME]] = $id;
			
			self::$property = array();
			self::fetchPropertiesWithDomainAndRange($cats);
			self::fetchDatatypeProperties($cats);
			unset($cats);
		}

	}
 
 	/**
 	 * check if a certain smw_id is a property
 	 * 
 	 * @access public
 	 * @param  int smw_id
 	 * @return bool true if it is a property or false if it is not
 	 */
 	public static function isProperty($id) {
 		self::initData();
 		return isset(self::$property[$id]);
 	}

 	/**
 	 * check if a certain smw_id is a category
 	 * 
 	 * @access public
 	 * @param  int smw_id
 	 * @return bool true if it is a category or false if it is not
 	 */
 	public static function isCategory($id) {
 		self::initData();
 		return isset(self::$category[$id]);
 	}

 	/**
 	 * check if a certain smw_id is a domain of a property, id must be a category
 	 * 
 	 * @access public
 	 * @param  int smw_id
 	 * @return bool true if it is a domain type of a property or false if it is not
 	 */
 	public static function isPropertyDomain($id) {
 		self::initData();
 		return isset(self::$property[$id][PSC_DOMAIN]);
 	}

 	/**
 	 * check if a certain smw_id is a range of a property, id must be a category
 	 * 
 	 * @access public
 	 * @param  int smw_id
 	 * @return bool true if it is a range type of a property or false if it is not
 	 */
 	public static function isPropertyRange($id) {
 		self::initData();
 		return isset(self::$property[$id][PSC_RANGE]);
 	}

 	/**
 	 * check if a certain smw_id is a property that has an XSD type for a range
 	 * 
 	 * @access public
 	 * @param  int smw_id
 	 * @return bool true if property hase an XSD type as a range or false if it has not
 	 */
 	public static function isPropertyXsdType($id) {
 		self::initData();
 		return isset(self::$property[$id][PSC_VALUE_STRING]);
 	}
 	
 	/**
 	 * get all domains for a certain property. Id must be a property, returned are
 	 * ids of categories that may have pages that are a domain for this property.
 	 * 
 	 * @access public
 	 * @param  int smw_id of a property
 	 * @return array (int) of categories
 	 */
 	public static function getPropertyDomain($id) {
 		self::initData();
 		return isset(self::$property[$id][PSC_DOMAIN]) ? self::$property[$id][PSC_DOMAIN] : array();
 	}

 	/**
 	 * get all ranges for a certain property. Id must be a property, returned are
 	 * ids of categories that may have pages that are a range for this property.
 	 * 
 	 * @access public
 	 * @param  int smw_id of a property
 	 * @return array (int) of categories
 	 */ 	
 	public static function getPropertyRange($id) {
 		self::initData();
 		return isset(self::$property[$id][PSC_RANGE]) ? self::$property[$id][PSC_RANGE] : array();
 	}

 	/**
 	 * get the string of the type that the range values of this property have. This can be e.g.
 	 * Date for some Date values. The property must be set up to have a certain value. the value type
 	 * is taken from the column value_string of the table smw_specs2.
 	 * 
 	 * @access public
 	 * @param  int smw_id of a property
 	 * @return string value type
 	 */
  	public static function getPropertyXsdType($id) {
  		self::initData();
 		return isset(self::$property[$id][PSC_VALUE_STRING]) ? self::$property[$id][PSC_VALUE_STRING] : "";
 	}
 	
 	/**
 	 * get all domains for a certain property where the range is an xsd value. Id must be a
 	 * property that has an xsd type as range value. Returned are ids of categories that may
 	 * have pages that are a domain for this property.
 	 * 
 	 * @access public
 	 * @param  int smw_id of a property
 	 * @return array (int) of categories
 	 */
  	public static function getPropertyDomainByXsdType($id) {
  		self::initData();
 		return isset(self::$property[$id][PSC_XSDTYPE]) ? self::$property[$id][PSC_XSDTYPE] : array();
 	}

	/**
	 * return the name of a property/category
	 * 
	 * @access public
	 * @param  int id
	 * @return string
	 */
	public static function getNameById($id) {
		self::initData();
		if (isset(self::$property[$id])) return self::$property[$id][PSC_PROPERTY_NAME];
		if (isset(self::$category[$id])) return self::$category[$id][PSC_CATEGORY_NAME];
		return "";
	}
	
	/** 
	 * return the type of an id (property or category), the namsespace id is returned.
	 * If the current id is no property or category, then the main namespace id is returned.  
	 * 
	 * @access public
	 * @param  int id
	 * @return int namespace id
	 */
	public static function getTypeById($id) {
		self::initData();
		if (isset(self::$property[$id])) return SMW_NS_PROPERTY;
		if (isset(self::$category[$id])) return NS_CATEGORY;
		return NS_MAIN;		
	}
 	
 	/**
 	 * return all existing category ids
 	 * 
 	 * @access public
 	 * @return array(int) of category ids
 	 */
 	public static function getCategories() {
 		self::initData();
 		return array_keys(self::$category);
 	}

 	/**
 	 * return all existing property ids
 	 * 
 	 * @access public
 	 * @return array(int) of property ids
 	 */
 	public static function getProperties() {
 		self::initData();
 		return array_keys(self::$property);
 	}
 	
 	/**
 	 * get the top category (this may not the parent category if this category itself
 	 * has another parent) of a certain category. If this category has no parents
 	 * it's own id is returned
 	 * 
 	 * @access public
 	 * @param  int category id
 	 * @return int of top category 
 	 */
 	public function getTopCategory($id) {
 		self::initData();
 		while (isset(self::$category[$id][PSC_PARENT_CATEGORY])) {
 			$id = self::$category[$id][PSC_PARENT_CATEGORY];
 		}
 		return $id;
 	}

	/**
	 * for a category check all sub categories and further donw until the category doesn't
	 * have any children. All these categories of the last level (those that don't have any
	 * children, are returned)
	 * 
	 * @access public
	 * @param  int id of category
	 * @return array (int) of categories
	 */
 	public function getLowestCategories($id) {
 		self::initData();
 		$cats = array();
 		$c2check = array($id);
 		while ($id = end($c2check)) {
 			$sub = self::getSubCategories($id);
 			if (count($sub) > 0) {
 				foreach ($sub as $c) array_unshift($c2check, $c);
 			}
 			else 
 				$cats[] = $id;
 			array_pop($c2check);
 		}
 		return $cats;
 	}

	/**
	 * for a category check all sub categories and further donw until the category doesn't
	 * have any children. All categories below this top category are returned.
	 * 
	 * @access public
	 * @param  int id of category
	 * @return array (int) of categories
	 */
 	public function getAllSubCategories($id) {
 		self::initData();
 		$cats = array($id);
 		for ($i = 0; $i < count($cats); $i++) {
 			$sub = self::getSubCategories($id);
 			if (count($sub) > 0) {
 				foreach ($sub as $c) $cats[] = $c; 
 			}
 		}
 		// remove subject
 		array_shift($cats);
 		return $cats;
 	}

	/**
	 * get parent category of a certain category. If the category doesn't have a parent
	 * its own id is returned.
	 * 
	 * @access public
	 * @param  int id of category
	 * @return int id of patent category
	 */
 	public function getParentCategory($id) {
 		self::initData();
 		if (isset(self::$category[$id][PSC_PARENT_CATEGORY]))
 			return self::$category[$id][PSC_PARENT_CATEGORY];
 		return $id;
 	}

	/**
	 * for a category get all sub categories (direct children). If the category
	 * doesn't have any children, an empty array is returned.
	 * 
	 * @access public
	 * @param  int id of category
	 * @return array (int) of categories
	 */
 	public function getSubCategories($id) {
 		self::initData();
 		return isset(self::$category[$id][PSC_SUBCATEGORY])
 		       ? self::$category[$id][PSC_SUBCATEGORY]
 		       : array();
 	}
 	
 	/**
 	 * Get all pages that are a range for a certain property. If the property
 	 * has an XSD Value, all values are returned for that relation.
 	 * 
 	 * @access public
 	 * @param  int smw_id of property
 	 * @return array(mixed) of xsd values or array (int) of smw_ids of pages that are ranges
 	 */
	public function searchNextRange($id) {
		self::initData();
		if (isset(self::$property[$id][PSC_VALUE_STRING])) // property has value type
			return $this->searchNext('smw_atts2', 's_id' , $id);	
		// proerty has Domain and Range
		return self::searchNext('smw_rels2', 'o_id', $id);
	} 

	/**
	 * get all pages that are a domain for a certain property
	 * 
	 * @access public
	 * @param  int smw_id of property
	 * @return array(int) smw_ids of pages
	 */
	public function searchNextDomain($id) {
		return self::searchNext('smw_rels2', 's_id', $id);
	} 

	/**
	 * get all properties annotated at a certain page
	 * 
	 * @access public
	 * @param  int smw_id of page
	 * @return array (int) smw_ids of properties
	 */
	public function searchProperty4Page($id) {
		return self::searchNext('smw_rels2', 's_id', $id);
	}

	/**
	 * get a category for a certain page
	 * 
	 * @access public
	 * @param  int smw_id of page
	 * @return array(int) of categories
	 */
	public function searchCategory4Page($id) {
		$result = array();
		$db =& wfGetDB(DB_SLAVE);

		$smw_ids = $db->tableName('smw_ids');
		$categorylinks = $db->tableName('categorylinks');
		
		$query = "SELECT smw_id AS id FROM $smw_ids WHERE smw_title IN 
				     (SELECT c.cl_to FROM $smw_ids s, $categorylinks c WHERE s.smw_id = $id AND c.cl_sortkey = s.smw_sortkey)";
		$res = $db->query($query);
		if ($res) {
			while ($row = $db->fetchObject($res)) $result[] = $row->id;
		}	
		return $result;
	} 	

	/**
	 * get all pages that are in a certain category
	 * 
	 * @access public
	 * @param  int smw_id of category
	 * @return array(int) smw_ids of pages
	 */
	public function searchPage4Category($id) {
		$result = array();
		$db =& wfGetDB(DB_SLAVE);

		$smw_ids = $db->tableName('smw_ids');
		$categorylinks = $db->tableName('categorylinks');
		$query = "SELECT smw_id AS id FROM $smw_ids WHERE smw_sortkey IN
				     (SELECT c.cl_sortkey FROM $smw_ids s, $categorylinks c WHERE s.smw_id = $id AND c.cl_to = s.smw_sortkey)";
		$res = $db->query($query);
		if ($res) {
			while ($row = $db->fetchObject($res)) $result[] = $row->id;
		}	
		return $result;
	} 	

 	
 	// private functions to retrieve data from the database

	/**
	 * fetch data from the database, based on a property and a subject or object
	 * from smw_rels2 or smw_atts2.
	 * This function is used by searchNextRange() searchNextProperty() and searchProperty4Page()
	 * 
	 * @access private
	 * @param  string table name (smw_rels2 or smw_atts2)
	 * @param  string column name (s_id or _o_id for the value that we have in the id)
	 * @param  int    id smw_id of the subject or object
	 * @return array (int) smw_ids.
	 */
	private function searchNext($table, $col, $id) {
		$result = array();
		$db =& wfGetDB(DB_SLAVE);
		
		$smw_ids = $db->tableName('smw_ids');
		$t2 = $db->tableName($table);
		$query = "SELECT s.smw_id AS id FROM $smw_ids s, $table t WHERE t.p_id = $id AND t.$col = s.smw_id";
		$res = $db->query($query);
		if ($res && $db->numRows($res) > 0) {
			while ($row = $db->fetchObject($res)) $result[] = $row->id;	
		}
		return $result;
	} 

	/**
	 * fetch all category data for initializing the static variable $category
	 * 
	 * @access private
	 */
 	private function fetchCategories() {
 		if (! self::$db) self::$db =& wfGetDB(DB_SLAVE);
 		self::$category = array();
 		$smw_ids = self::$db->tableName('smw_ids');
 		$category = self::$db->tableName('category');

 		$query = "SELECT s.smw_id AS id, s.smw_sortkey AS name FROM $smw_ids s, $category c WHERE c.cat_title = s.smw_title AND c.cat_pages > 0";
 		$res = self::$db->query($query);
 		if ($res) {
 			while ($row = self::$db->fetchObject($res)) {
 				self::$category[$row->id][PSC_CATEGORY_NAME] = $row->name;
 			}
 		}
 		self::$db->freeResult($res);
 	
 	    // sub categories	
 		$categorylinks = self::$db->tableName('categorylinks');
        $query = "SELECT s.smw_id AS id, s.smw_sortkey AS subcat, REPLACE(cl.cl_to, '_', ' ') AS cat " .
        		 "FROM $smw_ids s, $category c, $categorylinks cl " .
        		 "WHERE c.cat_title = s.smw_title AND c.cat_title  = REPLACE(cl.cl_sortkey, ' ', '_')";
 		
 		$res = self::$db->query($query);
 		
 		$parents= array(); // this is for faster lookup only
 		if ($res) {
 			while ($row = self::$db->fetchObject($res)) {
 				if (! isset($parents[$row->cat])) {
 					foreach (array_keys(self::$category) as $id) {
 						if (self::$category[$id][PSC_CATEGORY_NAME] == $row->cat) {
 							$parents[$row->cat] = $id;
 							break;
 						}
 					}
 				}
 				if (isset(self::$category[$parents[$row->cat]][PSC_SUBCATEGORY]))
 					self::$category[$parents[$row->cat]][PSC_SUBCATEGORY][]= intval($row->id);
 				else
 					self::$category[$parents[$row->cat]][PSC_SUBCATEGORY] = array(intval($row->id));
 				self::$category[$row->id][PSC_PARENT_CATEGORY] = $parents[$row->cat];
 			}
 		}
 		self::$db->freeResult($res);
 	}
 	
 	/**
 	 * fetch all properties and fill the static variable $property with all properties
 	 * that have a domain and range with a page.
 	 * 
 	 * @access private 
 	 */
 	private function fetchPropertiesWithDomainAndRange($cats) {
 		if (! self::$db) self::$db =& wfGetDB(DB_SLAVE);
 		
 		// Property names
 		$smw_ids = self::$db->tableName('smw_ids');
 		$smw_rels2 = self::$db->tableName('smw_rels2');
 		$query = 
 			"SELECT s.smw_id AS id, s.smw_sortkey AS name FROM $smw_ids s, $smw_rels2 r ".
 			"WHERE s.smw_id = r.p_id and s.smw_iw != ':smw' GROUP BY s.smw_id";

 		$res = self::$db->query($query);
 		if ($res) {
 			while ($row = self::$db->fetchObject($res)) {
 				self::$property[$row->id][PSC_PROPERTY_NAME] = $row->name;
 			}
 		}
 		self::$db->freeResult($res);
 		
 		// Domain
 		$categorylinks = self::$db->tableName('categorylinks');
 		$query = 
			"SELECT r.p_id AS p_id, REPLACE(c.cl_to, '_', ' ') AS cl_to FROM $smw_rels2 r, $smw_ids s, $categorylinks c WHERE r.p_id in ".
 				 " (SELECT s.smw_id FROM $smw_ids s, $smw_rels2 r WHERE r.p_id = s.smw_id AND s.smw_iw != ':smw' ) ".
 			"AND s.smw_id = r.s_id and c.cl_sortkey = s.smw_sortkey group by r.p_id, c.cl_to";
 		$res = self::$db->query($query);
 		if ($res) {
 			while ($row = self::$db->fetchObject($res)) {
 				if (isset(self::$property[$row->p_id][PSC_DOMAIN]))
 					self::$property[$row->p_id][PSC_DOMAIN][] = $cats[$row->cl_to];
 				else
 					self::$property[$row->p_id][PSC_DOMAIN] = array($cats[$row->cl_to]);
 			}
 		}
 		self::$db->freeResult($res);

        // Range -> Category
 		$query = 
			"SELECT r.p_id AS p_id, REPLACE(c.cl_to, '_', ' ') AS cl_to FROM $smw_rels2 r, $smw_ids s, $categorylinks c WHERE r.p_id in ".
 				 " (SELECT s.smw_id FROM $smw_ids s, $smw_rels2 r WHERE r.p_id = s.smw_id AND s.smw_iw != ':smw' ) ".
 			"AND s.smw_id = r.o_id and c.cl_sortkey = s.smw_sortkey group by r.p_id, c.cl_to";
 		$res = self::$db->query($query);
 		if ($res) {
 			while ($row = self::$db->fetchObject($res)) {
 				if (isset(self::$property[$row->p_id][PSC_RANGE]))
 					self::$property[$row->p_id][PSC_RANGE][] = $cats[$row->cl_to];
 				else
 					self::$property[$row->p_id][PSC_RANGE] = array($cats[$row->cl_to]);
 			}
 		}
 		self::$db->freeResult($res);
 		
 	}
 
 	/**
 	 * fetch all property data  that have an XSD Valuetype for it's ranges, fill the
 	 * static variable $property
 	 * 
 	 * @access private
 	 */
	private function fetchDatatypeProperties($cats) {
        if (! self::$db) self::$db =& wfGetDB(DB_SLAVE);
                
        $smw_ids = self::$db->tableName('smw_ids');
        $smw_spec2 = self::$db->tableName('smw_spec2');
        $smw_atts2 = self::$db->tableName('smw_atts2');     
        $page = self::$db->tableName('page');
        $hasTypePropertyID = smwfGetStore()->getSMWPropertyID(SMWPropertyValue::makeProperty("_TYPE"));
        
        // properties that have a value type
        $query = "SELECT i.smw_id as p_id, s.value_string as value, i.smw_sortkey as name FROM $smw_atts2 a 
                        JOIN $smw_spec2 s ON s.s_id = a.p_id AND s.p_id=".$hasTypePropertyID." 
                        JOIN $smw_ids i ON i.smw_id = a.p_id
                        JOIN $page p ON page_title = i.smw_title AND page_namespace = i.smw_namespace  
                GROUP BY i.smw_title, s.value_string";

 		$res = self::$db->query($query);
 		if ($res && self::$db->numRows($res) > 0) {
 			while ($row = self::$db->fetchObject($res)) {
 				self::$property[$row->p_id][PSC_PROPERTY_NAME] = $row->name;
				self::$property[$row->p_id][PSC_VALUE_STRING] = $row->value;
 			}
 		}
 		else return;

        $categorylinks = self::$db->tableName('categorylinks');

 		// Categories where these value properties are used on
 		$query = "SELECT a.p_id AS p_id, REPLACE(c.cl_to, '_', ' ') AS cl_to FROM $categorylinks c, $smw_ids i, $smw_atts2 a, $smw_spec2 s 
 		          WHERE s.s_id = a.p_id AND s.p_id = ".$hasTypePropertyID." AND a.s_id = i.smw_id AND c.cl_sortkey = i.smw_sortkey
 		          GROUP BY a.p_id, c.cl_to";
 		$res = self::$db->query($query);

 		if ($res) {
 			while ($row = self::$db->fetchObject($res)) {
 				if (isset(self::$property[$row->p_id][PSC_XSDTYPE]))
 					self::$property[$row->p_id][PSC_XSDTYPE][] = $cats[$row->cl_to];
 				else
					self::$property[$row->p_id][PSC_XSDTYPE] = array($cats[$row->cl_to]);
 			}
 		}
 		        
    }
 	
 }
 
?>
