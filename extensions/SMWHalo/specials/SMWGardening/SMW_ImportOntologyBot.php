<?php
/*
 * Created on 03.07.2007
 *
 * Author: kai
 */
 
 
 
 class ImportOntologyBot extends GardeningBot {
 	
 	// global log which contains wiki-markup
 	private $globalLog;
 	
 	// RDF model
 	private $model; 
 	
 	// set of wiki statements (semantic markup)
 	private $wikiStatements = array(); 
 	
 	
 	function ImportOntologyBot() {
 		parent::GardeningBot("smw_importontologybot");
 		$this->globalLog = "== The following wiki pages were created during import: ==\n\n";
 	}
 	
 	public function getHelpText() {
 		return wfMsg('smw_gard_import_docu');
 	}
 	
 	public function getLabel() {
 		return wfMsg($this->id);
 	}
 	
 	public function allowedForUserGroups() {
 		return array(SMW_GARD_GARDENERS, SMW_GARD_SYSOPS);
 		//return array();
 	}
 	
 	/**
 	 * Returns an array mapping parameter IDs to parameter objects
 	 */
 	public function createParameters() {
 		$param1 = new GardeningParamFile('GARD_IO_FILENAME', wfMsg('smw_gard_import_locationowl'), SMW_GARD_PARAM_REQUIRED);
 		return array($param1);
 	}
 	
 	/**
 	 * Import ontology
 	 * Do not use echo when it is not running asynchronously.
 	 */
 	public function run($paramArray, $isAsync, $delay) {
 		$this->globalLog = "";
 		// do not allow to start synchronously.
 		if (!$isAsync) {
 			return 'Import ontology bot should not be started synchronously!';
 		}
 		
 		$fileLocation = urldecode($paramArray['GARD_IO_FILENAME']);
 		
 		// initialize RAP
 		echo "\nTry to include RAP RDF-API...";
 		$smwgRAPPath = dirname(__FILE__) . "/../../../SemanticMediaWiki/libs/rdfapi-php";
		$Rdfapi_includes= $smwgRAPPath . '/api/';
		define("RDFAPI_INCLUDE_DIR", $Rdfapi_includes); // not sure if the constant is needed within RAP
		include(RDFAPI_INCLUDE_DIR . "RdfAPI.php");
		include( RDFAPI_INCLUDE_DIR . 'vocabulary/RDF_C.php');
		include( RDFAPI_INCLUDE_DIR . 'vocabulary/OWL_C.php');
		include( RDFAPI_INCLUDE_DIR . 'vocabulary/RDFS_C.php');
		echo "done!.\n";
		
		// Load model
		echo "Load model: ".$fileLocation."...";
		$this->model = ModelFactory::getDefaultModel();
		$this->model->load($fileLocation);
		echo "done!.\n";
		
		echo "Number of triples: ".$this->model->size()."\n";
		
		// Translate RDF model to wiki markup
		echo "\nTranslate model...\n";
		$this->translateModel();
		echo "\nModel translated!\n";
		
		// Import model (and merge markup if necessary)
		echo "\nImport model...";
		$this->importModel();
		//$this->printModel();
		//$this->printAllStatements();
		echo "done!\n";
		
		// this is just for debugging
		#$this->model->writeAsHtmlTable();
		
		$this->globalLog .= "\nModel was successfully translated and imported!\n";
		print $this->globalLog;
		return $this->globalLog;
 	}
 	
 	/**
 	 * Print generated Wiki text (for debugging reasons)
 	 */
 	private function printModel() {
 		echo "\n----------------------\nPrint wikiStatements\n\n";
 		    $this->mergeStatementArray($this->wikiStatements);
 			foreach($this->wikiStatements as $s) { 
 				if ($s == NULL) continue;
 				echo "Pagename: ".$s['PAGENAME']." Namespace: ".$s['NS']."\n";
 			
 				foreach($s['WIKI'] as $stat) {
 					echo $stat."\n";
 					
 				}

 				echo "------------------------------------------------------\n";
 			}
 			
 	}
 	
 	/**
 	 * Prints all statements of the model. For debugging reasons.
 	 */
 	private function printAllStatements() {
 		$it  = $this->model->findAsIterator(NULL, NULL, NULL);
		while ($it->hasNext()) {
			$statement = $it->next();
			print_r($statement);
		}
 	}
 	
 	/**
 	 * Translates the whole OWL model and populate the wiki statement arrays for 
 	 * the different namespaces ($ns_MAIN, $ns_ATTRIBTE, $ns_RELATION, $ns_CATEGORY) 
 	 */
 	private function translateModel() {
 		$triplesNum = $this->model->size();
 		$oneCent = $triplesNum / 100;
 		$this->setNumberOfTasks(5);
 		
 		// translate categories
 		$this->addSubTask(1);
 		print "\nTranslate categories...";
 		$it  = $this->model->findAsIterator(NULL, RDF::TYPE(), OWL::OWL_CLASS());
		while ($it->hasNext()) {
			if ($triplesNum % $oneCent == 0) echo ".";
			
			$statement = $it->next();
				
			$subject = $statement->getSubject();
			$s = $this->createCategoryStatements($subject);
			$this->wikiStatements = array_merge($this->wikiStatements, $s);
			$triplesNum--;
		}
		$this->worked(1);
		print "done!\n";
		
		// translate relations
		$this->addSubTask(1);
		print "\nTranslate properties...";
		$it  = $this->model->findAsIterator(NULL, RDF::TYPE(), OWL::OBJECT_PROPERTY());
		while ($it->hasNext()) {
			if ($triplesNum % $oneCent == 0) echo ".";
			
			$statement = $it->next();
			
			$subject = $statement->getSubject();
			$s = $this->createRelationStatements($subject);
			$this->wikiStatements = array_merge($this->wikiStatements, $s);
			$triplesNum--;
		}
		$this->worked(1);
				
		// translate attributes	
		$this->addSubTask(1);
		$it  = $this->model->findAsIterator(NULL, RDF::TYPE(), OWL::DATATYPE_PROPERTY());
		while ($it->hasNext()) {
			if ($triplesNum % $oneCent == 0) echo ".";
			
			$statement = $it->next();
			$subject = $statement->getSubject();
			$s = $this->createAttributeStatements($subject);
			$this->wikiStatements = array_merge($this->wikiStatements, $s);
			$triplesNum--;
		}
		$this->worked(1);
		print "done!\n";
		
		print "\nTranslate instances...";
		// translate instances, instantiated properties 
		$this->addSubTask(1);
		$s = array();
		$it  = $this->model->findAsIterator(NULL, RDF::TYPE(), NULL);
		while ($it->hasNext()) {
			if ($triplesNum % $oneCent == 0) echo ".";
 			
			$statement = $it->next();
			
			$subject = $statement->getSubject();
			$object = $statement->getObject();
			$objOWLClass = ($this->model->find($object, RDF::TYPE(), OWL::OWL_CLASS()));
			if (!$objOWLClass->isEmpty()) { 
				$s = $this->createArticleStatements($subject);
			}
			$this->wikiStatements = array_merge($this->wikiStatements, $s);
		 	$triplesNum--;
		 	
		}
		$this->worked(1);
		print "done!\n";
	
 	}
 	
 	/**
 	 * Creates Wiki pages from the transformed model.
 	 */
 	private function importModel() {
 		// merge statements
 		ImportOntologyBot::mergeStatementArray($this->wikiStatements);
 		$this->addSubTask(count($this->wikiStatements));
 		// import them
 		foreach($this->wikiStatements as $s) { 
 			$this->worked(1);
 			if ($s == NULL) continue;
 			print "\nImporting: ".$s['PAGENAME'];
 			$this->globalLog .= "\n*Importing: ".ImportOntologyBot::getNamespaceText($s['NS']).":".$s['PAGENAME'];
 			$title = Title::makeTitle( $s['NS'] , $s['PAGENAME'] );
 			$wikiMarkup = $s['WIKI'];
			$this->insertOrUpdateArticle($title, $wikiMarkup);
 		}
 	}
 	
 	
 	
 	/**
 	 * Inserts an article or updates it if it already exists
 	 * 
 	 * @param $title Title of article
 	 * @param $text Text to add
 	 */
 	private function insertOrUpdateArticle($title, $wikiMarkup) {
 		if (NULL == $title) continue;
		if ($title->exists()) {
			print " (merging)";
			$this->globalLog .= " (merging)";
			$article = new Article($title);
			$oldtext = $article->getContent();
			// article exists, so paste only diff of semantic markup
			$text = implode("\n", $this->diffSemanticMarkup($oldtext, $wikiMarkup));
			$article->updateArticle( $oldtext . "\n" . "\n" . $text, wfMsg( 'smw_oi_importedfromontology' ), FALSE, FALSE );
		} else {
			// articles does not exist, so simply paste all semantic markup
			$text = implode("\n", $wikiMarkup);
			$newArticle = new Article($title);
			$newArticle->insertNewArticle( $text, wfMsg( 'smw_oi_importedfromontology' ), FALSE, FALSE, FALSE, FALSE );
			SMWFactbox::storeData($newArticle->getTitle(), smwfIsSemanticsProcessed($newArticle->getTitle()->getNamespace()));
		}
 	}
 	
 	/**
 	 * Calculates diff of semantic markup and an existing wiki text. 
 	 */
 	private function diffSemanticMarkup($oldWikiText, $newSemanticMarkup) {
 		$annotations = array();
 		// Parse links to extract relations
 		$semanticLinkPattern = '(\[\[(([^:][^]]*)::)+([^\|\]]*)(\|([^]]*))?\]\])';
		$num = preg_match_all($semanticLinkPattern, $oldWikiText, $relMatches);
		$oldSemanticMarkup = array();
		if ($num > 0) {
			for($i = 0, $n = count($relMatches[0]); $i < $n; $i++) {
				
				$oldSemanticMarkup[] = $relMatches[0][$i];
			}
		}
		// Parse links to extract attributes
		$semanticLinkPattern = '(\[\[(([^:][^]]*):=)+((?:[^|\[\]]|\[\[[^]]*\]\])*)(\|([^]]*))?\]\])';
		$text = preg_match_all($semanticLinkPattern, $oldWikiText, $attMatches);
		if ($num > 0) {
			for($i = 0, $n = count($attMatches[0]); $i < $n; $i++) {
				
				$oldSemanticMarkup[] = $attMatches[0][$i];
			}
		}
		//TODO: categories
		return array_diff($newSemanticMarkup, $oldSemanticMarkup);
 	}
 	
 	/**
	 * Turns the triples of an individual into wiki source
	 */
	private function createArticleStatements($entity) {
		$statements = array();

		$sLabel = $this->getLabelForEntity($entity);
		$st = Title::newFromText( $sLabel , NS_MAIN );
		if ($st == NULL) continue; // Could not create a title, next please
		
		// instantiated relations and attributes
		$it  = $this->model->findAsIterator($entity, NULL, NULL);
		while ($it->hasNext()) {
			$statement = $it->next();
			$property = $statement->getPredicate();
			$object = $statement->getObject();
			$propertyIsRelation = ($this->model->find($property, RDF::TYPE(), OWL::OBJECT_PROPERTY()));
			$propertyIsAttribute = ($this->model->find($property, RDF::TYPE(), OWL::DATATYPE_PROPERTY()));
			if (!$propertyIsRelation->isEmpty()) {
				$this->createRelationAnnotation($st, $property, $object, $statements);
			}
			if (!$propertyIsAttribute->isEmpty()) {
				$this->createAttributeAnnotation($st, $property, $object, $statements);
			}
			
		}

		// categories links from instances
		$it  = $this->model->findAsIterator($entity, RDF::TYPE(), NULL);
		while ($it->hasNext()) {
			$statement = $it->next();
			
			$concept = $statement->getObject();
			$label = $this->getLabelForEntity($concept);
			$t = Title::newFromText( $label , NS_CATEGORY );
			if ($this->isInCategory($st, $t)) continue;
			if ($t == NULL) continue; // Could not create a title, next please
			
			$s = array();
			$s['NS'] = NS_MAIN;
			$s['PAGENAME'] = $st->getDBkey();
			$s['WIKI'] = array();
			$s['WIKI'][] = "[[" . $t->getPrefixedText() . "]]" . "\n";
			$statements[] = $s;
		}

		return $statements;
	}
	
	/**
	 * Turns an OBJECT_PROPERTY statement into wiki source
	 * 
	 * @param $st subject title
	 * @param $property statement
	 * @param $object statement
	 * @param $statement reference to set of statements for this subject. 
	 */
	private function createRelationAnnotation($st, $property, $object, & $statements) {
		
		$pLabel = $this->getLabelForEntity($property);
		$pt = Title::newFromText( $pLabel , SMW_NS_PROPERTY );
		if ($pt == NULL) continue; // Could not create a title, next please
			
		$oLabel = $this->getLabelForEntity($object);
		$ot = Title::newFromText( $oLabel , NS_MAIN );
		if ($ot == NULL) return; // Could not create a title, next please
				
		$s = array();
		$s['NS'] = NS_MAIN;
		$s['WIKI'] = array();
		$s['WIKI'][] = "[[" . $pt->getText() . ":=" . $ot->getPrefixedText() . "]]";
		$s['PAGENAME'] = $st->getDBkey();
		$statements[] = $s;
			
	}
	
	/**
	 * Turns an DATATYPE_PROPERTY statement into wiki source
	 * 
	 * @param $st subject title
	 * @param $property statement
	 * @param $object statement
	 * @param $statement reference to set of statements for this subject. 
	 */
	private function createAttributeAnnotation($st, $property, $object, & $statements) {
		$pLabel = $this->getLabelForEntity($property);
			$pt = Title::newFromText( $pLabel , SMW_NS_PROPERTY );
			if ($pt == NULL) continue; // Could not create a title, next please
			
			$oLabel = $object->getLabel();
			// TODO check if already within wiki
			// TODO use datatype handler
			$s = array();
			$s['NS'] = NS_MAIN;
			$s['WIKI'] = array();
			$s['WIKI'][] = "[[" . $pt->getText() . ":=" . $oLabel . "]]";
			$s['PAGENAME'] = $st->getDBkey();
			$statements[] = $s;
	}
	
	/**
	 * Turns the triples of a class into wiki source.
	 */
	private function createCategoryStatements($entity) {
		$statements = array();
		 		
		$it  = $this->model->findAsIterator($entity, RDFS::SUB_CLASS_OF(), NULL);

		$slabel = $this->getLabelForEntity($entity, $this->model);
		if ($entity instanceof BlankNode) return $statements;
		$st = Title::newFromText( $slabel , NS_CATEGORY );
		if ($st == NULL) return $statements; // Could not create a title, next please
		$s1 = array();
		$s1['NS'] = NS_CATEGORY;
		$s1['WIKI'] = array();
		$s1['PAGENAME'] = $st->getDBkey();
		
		while ($it->hasNext()) {
			$statement = $it->next();
		
			$superClass = $statement->getObject();
			
			$superClassLabel = $this->getLabelForEntity($superClass, $this->model);
			$superClassTitle = Title::newFromText( $superClassLabel , NS_CATEGORY );
			if ($superClassTitle == NULL) continue; // Could not create a title, next please
			if ($this->isInCategory($st, $superClassTitle)) continue;
			
			$s1['WIKI'][] = !($superClass instanceof BlankNode) ? "[[" . $superClassTitle->getPrefixedText() . "]]"  : "";
			
			// create relations or attribute which has $entity as domain (with range, cardinality)		
			if ($superClass instanceof BlankNode) {
				$this->createPropertiesFromCategory($st, $superClass, $statements);
			}
		}
		$statements[] = $s1;
		
		return ImportOntologyBot::mergeStatementsForNS($statements);
		
	}
	
	private function createPropertiesFromCategory($st, $superClass, & $statements) {
		global $smwgContLang, $smwgHaloContLang, $wgContLang;
 		$ssp = $smwgHaloContLang->getSpecialSchemaPropertyArray();
 		$sp = $smwgContLang->getSpecialPropertiesArray();
 		
		$it2 = $this->model->findAsIterator($superClass, OWL::ON_PROPERTY(), NULL);
		if ($it2->hasNext()) {
			$property = $it2->next()->getObject();
			$propertyName = $this->getLabelForEntity($property, $this->model);
			$propertyTitle = Title::newFromText( $propertyName);
		}
		
		if (!isset($property)) return; // can not do anything, if property name can not be read.
		
		$s2 = array();
		$s2['PAGENAME'] = $propertyTitle->getDBkey();
		$s2['WIKI'] = array();
		
		$it3 = $this->model->findAsIterator($superClass, OWL::ALL_VALUES_FROM(), NULL);
		if ($it3->hasNext()) {
			$range = $it3->next()->getObject();
		}
		
		if (!isset($range)) {
			$it6 = $this->model->findAsIterator($superClass, RDFS::RANGE(), NULL);
			if ($it6->hasNext()) {
				$range = $it6->next()->getObject();
			}
		}
		
		if (isset($range)) {
			$namespace =  SMW_NS_PROPERTY;
			$s2['NS'] = $namespace;
		
			
			$rangeCategoryName = $this->getLabelForEntity($range, $this->model);
			$rangeCategoryTitle = Title::newFromText( $rangeCategoryName , (ImportOntologyBot::isXMLSchemaType($range->getURI())) ? SMW_NS_TYPE : NS_CATEGORY );
			
		
			if ((ImportOntologyBot::isXMLSchemaType($range->getURI()))) { 
				 $s2['WIKI'][] = "[[".$sp[SMW_SP_HAS_TYPE].":=".$rangeCategoryTitle->getPrefixedText()."]]\n";
				 $s2['WIKI'][] = "[[".$ssp[SMW_SSP_HAS_DOMAIN_HINT].":=".$st->getPrefixedText()."]]\n";
			} else {
				$s2['WIKI'][] = "[[".$sp[SMW_SP_HAS_TYPE].":=Type:Page]]\n";
				$s2['WIKI'][] = "[[".$ssp[SMW_SSP_HAS_RANGE_HINT].":=".$rangeCategoryTitle->getPrefixedText()."]]\n";
				$s2['WIKI'][] = "[[".$ssp[SMW_SSP_HAS_DOMAIN_HINT].":=".$st->getPrefixedText()."]]\n";
			}
		}
		
		$it4 = $this->model->findAsIterator($superClass, OWL::MIN_CARDINALITY(), NULL);
		if ($it4->hasNext()) {
			$minCardinality = $it4->next()->getObject()->getLabel();
		}
		
		$it5 = $this->model->findAsIterator($superClass, OWL::MAX_CARDINALITY(), NULL);
		if ($it5->hasNext()) {
			$maxCardinality = $it5->next()->getObject()->getLabel();
		}
		
		if (isset($minCardinality)) {
			$s2['WIKI'][] = "[[".$ssp[SMW_SSP_HAS_MIN_CARD].":=$minCardinality]]\n";
		}
		if (isset($maxCardinality)) {
			$s2['WIKI'][] = "[[".$ssp[SMW_SSP_HAS_MAX_CARD].":=$maxCardinality]]\n";
		}
		$statements[] = $s2;
	}
	
	/**
	 * Turns an OBJECT_PROPERTY statements into relation pages
	 * Reads also schema data which is exported by wiki. (may be obsolete) 
	 */
	private function createRelationStatements($entity) {
		$statements = array();
 		global $smwgContLang, $smwgHaloContLang, $wgContLang;
 		$ssp = $smwgHaloContLang->getSpecialSchemaPropertyArray();
 		$sc = $smwgHaloContLang->getSpecialCategoryArray();
		$smwNSArray = $smwgContLang->getNamespaceArray();
 		
		$slabel = $this->getLabelForEntity($entity, $this->model);
		$st = Title::newFromText( $slabel , SMW_NS_PROPERTY );
		if ($st == NULL) continue; // Could not create a title, next please

		$s = array();
		$s['NS'] = SMW_NS_PROPERTY;
		$s['PAGENAME'] = $st->getDBkey();
		$s['WIKI'] = array(); 
		
		
		// read domain (if available)
		$it  = $this->model->findAsIterator($entity, RDFS::DOMAIN(), NULL);
		while ($it->hasNext()) {
			$statement = $it->next();
			$object = $statement->getObject();
			$label = $this->getLabelForEntity($object);
			$s['WIKI'][] = "[[".$ssp[SMW_SSP_HAS_DOMAIN_HINT].":=" . $wgContLang->getNsText(NS_CATEGORY) . ":" . $label . "]]" . "\n";
		}

		// read range (if available)
		$it  = $this->model->findAsIterator($entity, RDFS::RANGE(), NULL);
		while ($it->hasNext()) {
			$statement = $it->next();
			$object = $statement->getObject();
			$label = $this->getLabelForEntity($object);
			$s['WIKI'][] = "[[".$ssp[SMW_SSP_HAS_RANGE_HINT].":=" . $wgContLang->getNsText(NS_CATEGORY) . ":" . $label . "]]" . "\n";
		}
		
		// read inverse statement (if available) 
		$it  = $this->model->findAsIterator($entity, OWL::INVERSE_OF(), NULL);
		while ($it->hasNext()) {
			$statement = $it->next();
			$object = $statement->getObject();
			$label = $this->getLabelForEntity($object);
			$s['WIKI'][] = "[[".$ssp[SMW_SSP_IS_INVERSE_OF].":=" . $smwNSArray[SMW_NS_PROPERTY] . ":" . $label . "]]" . "\n";
		}
		
		// read symetry (if available) 
		$symProp  = $this->model->findFirstMatchingStatement($entity, RDF::TYPE(), OWL::SYMMETRIC_PROPERTY());
		if ($symProp != null) {
			$s['WIKI'][] = "[[".$wgContLang->getNsText(NS_CATEGORY).":".$sc[SMW_SC_SYMMETRICAL_RELATIONS]."]]" . "\n";
		}
		
		// read transitivity (if available) 
		$transProp  = $this->model->findFirstMatchingStatement($entity, RDF::TYPE(), OWL::TRANSITIVE_PROPERTY());
		if ($transProp != null) {
			$s['WIKI'][] = "[[".$wgContLang->getNsText(NS_CATEGORY).":".$sc[SMW_SC_TRANSITIVE_RELATIONS]."]]" . "\n";
		}
		
		$statements[] = $s;
		return $statements;
	}

	
	/**
	 *  Turns an DATATYPE_PROPERTY statements into attribute pages
	 */
	private function createAttributeStatements($entity) {
		$statements = array();
		global $smwgContLang, $smwgHaloContLang, $wgContLang;
 		$ssp = $smwgHaloContLang->getSpecialSchemaPropertyArray();
 		$sp = $smwgContLang->getSpecialPropertiesArray();
 		
		$slabel = $this->getLabelForEntity($entity);
		$st = Title::newFromText( $slabel , SMW_NS_PROPERTY );
		if ($st == NULL) continue; // Could not create a title, next please

		$s = array();
		$s['NS'] = SMW_NS_PROPERTY;
		$s['PAGENAME'] = $st->getDBkey();
		$s['WIKI'] = array();
		
		
		// read domain (if available)
		$it  = $this->model->findAsIterator($entity, RDFS::DOMAIN(), NULL);
		while ($it->hasNext()) {
			$statement = $it->next();
			$object = $statement->getObject();
			$label = $this->getLabelForEntity($object);
			$s['WIKI'][] = "[[".$ssp[SMW_SSP_HAS_DOMAIN_HINT].":=" . $wgContLang->getNsText(NS_CATEGORY) . ":" . $label . "]]" . "\n";
			
		}
		
		// read range (if available)
		$it  = $this->model->findAsIterator($entity, RDFS::RANGE(), NULL);
		while ($it->hasNext()) {
			$statement = $it->next();
			$object = $statement->getObject();
			$label = $this->getLabelForEntity($object);
			$label = ImportOntologyBot::mapXSDTypesToWikiTypes($label);
			$s['WIKI'][] = "[[".$sp[SMW_SP_HAS_TYPE].":=" . $wgContLang->getNsText(SMW_NS_TYPE) . ":" . $label . "]]" . "\n";
		}
		
		$statements[] = $s;
		return $statements;
	}
	
	
	
 	private function getLabelForEntity($entity) {
		// TODO look for language hints in the labels
		$labelstatement = $this->model->findFirstMatchingStatement($entity, RDFS::LABEL(), NULL, 0);
		if ($labelstatement != NULL) {
			$label = $labelstatement->getLabelObject();
		} else {
			$label = $entity->getLocalName();
		}

		return $label;
	}
	
	/**
 	* Checks if an article is in a category. Returns true if yes, and false else.
 	* Works also to check if a category is a subcategory of the second.
 	*/
	private function isInCategory($article, $category) {
		if (!$article->exists()) return FALSE; // this was the easy part :)

		$categories = $article->getParentCategories();
		if ('' == $categories) return FALSE;
	
		$catkeys = array_keys($categories);
	
		foreach($catkeys as $cat) {
			if ($category->getPrefixedDBKey() == $cat) {
				return TRUE;
			}
		}

		return FALSE;
	}
	
	private static function isXMLSchemaType($type) {
		return $type == XML_SCHEMA.'integer' || $type == XML_SCHEMA.'string' 
				|| $type == XML_SCHEMA.'boolean' || $type == XML_SCHEMA.'number'
				|| $type == XML_SCHEMA.'date' || $type == XML_SCHEMA.'time' || $type == XML_SCHEMA.'datetime';
	}
	
	/**
	 * Merges an array of wiki statement objects (= hash arrays of PAGENAME, NS, WIKI). Makes sure that
	 * all entries have namespaces afterwards.
	 * $statements array should be small. 
	 * 
	 * @param & $statements Array of hash arrays containing the following keys: PAGENAME, NS, WIKI
	 * 
	 * @return statement array with namespaces for all entries.
	 */
	private static function mergeStatementsForNS( & $statements) {
		$result = array(); 
		
		foreach($statements as $s) {
			if (!array_key_exists($s['PAGENAME'], $result)) {
				$entry = array();
				if (array_key_exists('NS', $s)) {
					$entry['NS'] = $s['NS'];
				}
				$entry['PAGENAME'] = $s['PAGENAME'];
				$entry['WIKI'] = array();
				$entry['WIKI'] = array_merge($entry['WIKI'], $s['WIKI']);
				$result[$s['PAGENAME']] = $entry;
			} else {
				$entry = $result[$s['PAGENAME']];
				if (array_key_exists('NS', $s)) {
					$entry['NS'] = $s['NS'];
				}
				$entry['PAGENAME'] = $s['PAGENAME'];
				$entry['WIKI'] = array_merge($entry['WIKI'], $s['WIKI']);
				$result[$s['PAGENAME']] = $entry;
			}
		}
		
		return array_values($result);
	}
	
	/**
	 * Merges statements concerning the same wiki page.
	 * 
	 * @param & $statements Array of hash arrays containing the following keys: PAGENAME, NS, WIKI
	 */
	private static function mergeStatementArray( & $statements) {
		
		if (count($statements) == 0) return;
		
		// sort array for PAGENAME
		for($i = 0, $n = count($statements); $i < $n; $i++) {
			for($j = 0; $j < $n-1; $j++) {
				if (strcmp($statements[$j]['PAGENAME'], $statements[$j+1]['PAGENAME']) > 0) {
					$help = $statements[$j];
					$statements[$j] = $statements[$j+1];
					$statements[$j+1] = $help;
				}
			}
		}
		
		// sort for NS
		for($i = 0, $n = count($statements); $i < $n; $i++) {
			for($j = 0; $j < $n-1; $j++) {
				if ($statements[$j]['NS'] > $statements[$j+1]['NS']) {
					$help = $statements[$j];
					$statements[$j] = $statements[$j+1];
					$statements[$j+1] = $help;
				}
			}
		}
		
		// merge wiki statements and set all merged entries to NULL
		$current = & $statements[0];
		for($i = 1, $n = count($statements); $i < $n; $i++) {
			if (($statements[$i]['PAGENAME'] == $current['PAGENAME']) && ($statements[$i]['NS'] == $current['NS'])) {
				
				$diff = array_diff($statements[$i]['WIKI'], $current['WIKI']);
				
				$current['WIKI'] = array_merge($current['WIKI'], $diff);
				$statements[$i] = NULL;
			} else {
				$current = & $statements[$i];
			}
			
		}
	}
	
	private static function mapXSDTypesToWikiTypes($xsdType) {
		switch($xsdType) {
			case 'string': return 'String';
			case 'int': return 'Integer';
			case 'float': return 'Float';
			default: return 'String';
		}
	}
	
	private static function getNamespaceText($ns) {
 		global $smwgContLang, $wgLang;
 		$nsArray = $smwgContLang->getNamespaceArray();
 		if ($ns == NS_TEMPLATE || $ns == NS_CATEGORY) {
 			$ns = $wgLang->getNsText($ns);
 				} else { 
 			$ns = $ns != NS_MAIN ? $nsArray[$ns] : "";
 		}
 		return $ns;
 	}
 }
 
 
 
 new ImportOntologyBot();
?>
