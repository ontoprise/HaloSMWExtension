<?php

/**
 * @file
  * @ingroup DASemanticForms
  * 
  * @author Dian
 */

/**
 * Adds and handles the 'sfdata' action to the MediaWiki API.
 *
 * @author Dian
 * @todo This is a patched version for SF 1.6. There might be some issues with "new" methods returning array instead of a single value, e.g. getDefaultForms.
 */

/**
 * Protect against register_globals vulnerabilities.
 * This line must be present before any global variable is referenced.
 */
if (!defined('MEDIAWIKI')) die();

/**
 * @addtogroup API
 */
class SFDataAPI extends ApiBase {

	private $mPageTitle = null;
	
	public function __construct($query, $moduleName) {
		parent :: __construct($query, $moduleName);
	}

	public function execute() {
		global $wgRequest;
		$__params = $this->extractRequestParams();
		$__data = array();

		$__username = $__params['un'];
		$__userId = $__params['uid'];
		$__loginToken = $__params['lt'];

		// only with GET
		if($wgRequest->wasPosted() === false){
			$__title = str_replace(' ', '_', $__params['title']);
			$sfName = str_replace(' ', '_', $__params['sfname']);
			$__revisionId = $__params['rid'];
			$__catTree = str_replace(' ', '_', $__params['cattree']);
			$__catLevel = $__params['catlevel'];
			$__sfList = str_replace(' ', '_', $__params['sflist']);
			$__pageList = str_replace(' ', '_', $__params['pagelist']);
			$__substr = $__params['substr'];			

			if ($__title != ''){
				$__data = $this->getSerializedForm($__title, $__revisionId, $__username, $__userId, $__loginToken);
			} else if($sfName != ""){
				$__data = $this->getSerializedForm(
					$sfName, $__revisionId, $__username, $__userId, $__loginToken, true);
			} elseif ($__catTree != '') {
				if ($__catTree == "root") $__catTree = "";
				$__data = $this->getCategoryTree($__catTree, $__catLevel, $__substr);
			}elseif ($__sfList != '') {
				if ($__sfList == "root") $__sfList = "";
				$__data = $this->getSFList($__sfList,$__substr);
			}elseif ($__pageList != '') {
				if ($__pageList == "root") $__pageList = "";
				$__data = $this->getPageList($__pageList,$__substr);
			}
		}else{
			// only with POST
			$__serializedData = $__params['xmldata'];
			$__methodType = $__params['mt'];
			$__serializedJsonData = $__params['jsondata'];

			if ($__serializedData != NULL){
				$__data = $this->setSerializedForm($__serializedData, $__methodType, $__username, $__userId, $__loginToken);
			}else if ($__serializedJsonData != NULL){
				$__data = $this->setSerializedJsonForm($__serializedJsonData, $__methodType, $__username, $__userId, $__loginToken);
			}
		}

		if (count($__data)<=0 || !$__data) {
			return;
		}

		// Set top-level elements
		$result = $this->getResult();
		$result->setIndexedTagName($__data, 'p');
		$result->addValue(null, $this->getModuleName(), $__data);
	}	

	protected function getAllowedParams() {
		return array (

			'title' => null,
			'rid' => null,
			'xmldata' => null,
			'jsondata' => null,
			'mt' => null,			
			'un' => null,
			'uid' => null,
			'lt' => null,
			'cattree' => null,
			'catlevel' => null,
			'sflist' => null,
			'pagelist' => null,
			'substr' => null,
			'sfname' => null
		);
	}

	protected function getParamDescription() {
		return array (
			'title' => 'The title(s) of the page(s). Allowed only with a GET operation',
			'rid' => 'The revision ID(s) of the page(s). Allowed only with a GET operation',
			'xmldata' => 'The serialized XML formatted data to be inserted in the wiki. Allowed only with a POST operation',
			'jsondata' => 'The serialized JSON formatted data to be inserted in the wiki. Allowed only with a POST operation',						
			'mt'	=> 'The method to be performed on the serialized data: c(reate)|u(pdate)|d(elete). Allowed only with a POST operation',
			'un' => 'The username used',
			'uid' => 'The user ID used',
			'lt' => 'The login token used',
			'cattree' => 'Category name used for the category tree. If "root" given starts with all toplevel categories.Allowed only with a GET operation',
			'catlevel' => 'The level set when retrieving pages or the tree of categories for a category. Allowed only with a GET operation',
			'sflist' => 'List of available SFs for a page. If "root" given returns all available SFs in the wiki. Allowed only with a GET operation',
			'pagelist' => 'List of pages for a SF. If "root" given returns all available SFs in the wiki and teh pages using them. Allowed only with a GET operation',
			'substr' => 'Search substring',
			'sfname' => 'Get serialization of the form with the given name. Use a GET request.'
		);
	}

	protected function getDescription() {
		return 'Perform GET / POST operations on SFs - retrieving SF lists and serialized data. Used by the Semantic Forms extension (http://www.mediawiki.org/Extension:Semantic_Forms)';
	}

	protected function getExamples() {
		return array (
			'api.php?action=sfdata&title=John_Doe',	
			'api.php?action=sfdata&substr=P&cattree=root&catlevel=3',
			'api.php?action=sfdata&sflist=root',
			'api.php?action=sfdata&pagelist=root',
		);
	}

	/**
	 * Reads the SF from the requested page and returns the form as an array ready for serialization. Works for both SF definitions and "normal" pages.
	 *
	 * @todo Currently reads only one SF instance per page. Add support for mutiple forms. For multiple instances add the <mfi name="formname"> element.
	 * @param string $title The title(s) of the page(s) (namespace included).
	 * @param string $revisionId The revision IDs of the page(s).
	 * @param string $username The username provided.
	 * @param string $userId The user ID provided.
	 * @param string $loginToken The login token provided.
	 * @return array An associative array of the resulting SF(s).
	 */
	public function getSerializedForm(
			$title, $revisionId, $username = NULL, $userId = NULL, $loginToken = NULL, $direct = false){
		
		if($direct){
			//a serialized form is rerquzested directly
			if (strpos($title,':') == false){
				//todo: use language file
				$title = "Form:".$title;
			}
		}
		$__pageReader = new PCPServer();
		$__page = $__pageReader->readPage(new PCPUserCredentials($username, NULL, $userId, $loginToken),$title, $revisionId);
		
		if (strpos($title,':') !=false){
			list($__ns, $__title) = split(':', $__page->title);
		}else{
			$__title = $title;
		}

		$__result['page']['title'] = $__title;
		$__result['page']['ns'] = $__page->namespace;
		$__result['page']['rid'] = $__page->usedrevid;
		if(!$direct){
			$__formNames = SFLinkUtils::getFormsForArticle(new Article(Title::newFromText($__title, $__page->namespace)));
		} else {
			$__formNames = array($title);
		}
		
		if( count($__formNames) > 0 && !$direct){
			$__formName = $__formNames[0];
			global $wgContLang;
			if ($__formName !== NULL && $__formName != ""){
				$__result['page'][str_ireplace(' ','_',$__formName)] = $this->formSerialize(
					$__pageReader->readPage(NULL,$wgContLang->getNsText(SF_NS_FORM) . ':' . $__formName)->text,
					true,
					$__page->text, $title);
			}
		}elseif($__page->namespace === SF_NS_FORM){
			global $wgContLang;
			$__result['page'][str_ireplace(' ','_',$__title)] = $this->formSerialize(
			$__page->text,
			false,
			$__page->text, $title);
		}
		return($__result);
	}

	/**
	 * Reads serialized data in XML format and saves the requested chages.
	 *
	 * @param XMLchunk $sfdata The posted data including page and SF attributes.
	 * @param string $methodType The action to be performed: c(create) or u(update) or d(delete).
	 * @param string $username The username provided.
	 * @param string $userId The user ID provided.
	 * @param string $loginToken The login token provided.
	 */
	public function setSerializedForm($sfdata, $methodType, $username = NULL, $userId = NULL, $loginToken = NULL){
		$__xmlData = $this->XML2Array($sfdata, false);
		$__serverUtility = new PCPServer();
		$__userCredentials = new PCPUserCredentials(
			$username,
			NULL,
			$userId,
			$loginToken);

		$__pageNs = utf8_decode($__xmlData['sfdata']['page']['@attributes']['ns']);
		$__pageTitle = $__xmlData['sfdata']['page']['@attributes']['title'];
		$__pageRId = @ $__xmlData['sfdata']['page']['@attributes']['rid'];
		$__page = $__serverUtility->readPage(
			$__userCredentials,
			$__pageTitle,
			$__pageRId);
		
		if(stristr($methodType,"c")){ // the requested method is create
			if($__page->pageid == NULL || $__page->pageid == ""){
				// the page doesn't exist so we have to create it
				// the XML data is read and a template is created for each SF

				// since @array will be read together with the SF elements unset it
				unset($__xmlData['sfdata']['page']['@attributes']);
				$__sfNames = array_keys($__xmlData['sfdata']['page']);
				$__tmplString = ""; // used for the creation of all template strings
				$__tmplNames = ""; // used for adding addtional info to the summary

				foreach ($__sfNames  as $__sfName){
					$__sf = $__xmlData['sfdata']['page'][$__sfName];
					if (isset($__sf['@attributes']) && isset($__sf['@attributes']['mfi'])){
						// multiple SF instancies detected - multiple SFs to be saved in one request
						// TODO: implement

					}else{
						unset($__sf['@attributes']);
						
						// build the template string for each SF element
						foreach($__sf as $template){
							$__tmplString .= "\n{{".
								utf8_decode($template['@attributes']['tmpl_name'])."\n"; // the tempalte name
							$__tmplNames .= utf8_decode($template['@attributes']['tmpl_name'])."\t";
							// since @array will be read together with the SF field elements unset it
							unset($template['@attributes']);

							foreach ($template as $__field){ // in the SF element are listed all SF fields
								// add the field name and value to the template
								$__tmplString .= "|"
									.utf8_decode($__field['template_field']['@attributes']['field_name'])
									."=".utf8_decode($__field['@attributes']['cur_value'])."\n";
							}
							$__tmplString .= "}}\n"; // add closing brackets to the template
						}
					}
				}
				
				return $__serverUtility->createPage(
					$__userCredentials,
					$__pageTitle,
					$__tmplString,
					"SF data added to the page via the SF API. Templates:\t".$__tmplNames
				);

			}else{
				// create the POM object for the requested page
				$__pom = new POMPage(
					$__pageTitle,
					$__page->text,
					array('POMExtendedParser'));

				// since @array will be read together with the SF elements unset it
				unset($__xmlData['sfdata']['page']['@attributes']);
				$__sfNames = array_keys($__xmlData['sfdata']['page']);
				$__tmplString = $__pom->text; // used for the creation of all template strings - preserve the existing page text if any
				$__tmplNames = ""; // used for adding addtional info to the summary

				foreach ($__sfNames  as $__sfName){
					$__sf = $__xmlData['sfdata']['page'][$__sfName];
					
					if (isset($__sf['@attributes']) && isset($__sf['@attributes']['mfi'])){
						// multiple SF instances detected
						// TODO: implement

					}else{
						unset($__sf['@attributes']);
						foreach($__sf as $template){
							$__iterator = $__pom->getTemplateByTitle(
								$template['@attributes']['tmpl_name'])->listIterator();

							if($__iterator->hasNext()){
								// there is already a template defined and no multiple templates per page are supported
								$this->dieUsage("Request cancelled. Page $__pageTitle already uses the semantic form $__sfName.", 'param_xmldata');
							}else{
								// build the template string for each SF element
								$__tmplString .= "\n{{".utf8_decode(
									$template['@attributes']['tmpl_name'])
									."\n"; // the tempalte name
									$__tmplNames .= utf8_decode($template['@attributes']['tmpl_name'])."\t";
							// since @array will be read together with the SF field elements unset it
							unset($template['@attributes']);

							foreach ($template as $__field){ // in the SF element are listed all SF fields
								// add the field name and value to the template
								$__tmplString .= "|".utf8_decode(
									$template['template_field']['@attributes']['field_name'])
									."=".utf8_decode($template['@attributes']['cur_value'])."\n";
								}
								$__tmplString .= "}}\n"; // add closing brackets to the template
							}
						}
					}
				}

				return $__serverUtility->updatePage(
				$__userCredentials,
				$__pageTitle,
				$__tmplString,
				"SF data added to the page via the SF API. Templates:\t".$__tmplNames
				);
			}

		}
		
		if(stristr($methodType,"u")){ // the requested method is update
			if($__page->pageid != NULL && $__page->pageid != ""){ // the page exists
				// create the POM object for the requested page
				
				$__pom = new POMPage(
					$__pageTitle,
					$__page->text,
					array('POMExtendedParser'));
				
				// since @array will be read together with the SF elements unset it
				unset($__xmlData['sfdata']['page']['@attributes']);
				$__sfNames = array_keys($__xmlData['sfdata']['page']);
				
				foreach ($__sfNames  as $__sfName){
					$__sf = $__xmlData['sfdata']['page'][$__sfName];
					unset($__sf['@attributes']);
					foreach($__sf as $template){
						$t = $template['@attributes']['tmpl_name'];
						$__iterator = $__pom->getTemplateByTitle(
							utf8_decode($template['@attributes']['tmpl_name']))->listIterator();
					
						if (isset($__sf['@attributes']) && isset($__sf['@attributes']['mfi'])){
							// todo: does this work any longer?
							// 	multiple SF instances detected
							// TODO: implement
						}else{
							$__template = &$__iterator->getNextNodeValueByReference(); # get reference for direct changes
							if(!$__template){
								continue;
							}
						
							// since @array will be read together with the SF field elements unset it
							//unset($__sf['@attributes']);
							unset($template['@attributes']);
	
							foreach ($template as $__field){ // in the SF element are listed all SF fields
								if($__template->getParameter(utf8_decode($__field['template_field']['@attributes']['field_name']))!== NULL){
									$__paramValue = &$__template->getParameterValue(utf8_decode($__field["template_field"]['@attributes']['field_name']));
									$__paramValue = new POMSimpleText(utf8_decode($__field['@attributes']['cur_value']));
								}
							}
						}
					}
				}
				$__pom->sync();

				return $__serverUtility->updatePage(
				$__userCredentials,
				$__pageTitle,
				$__pom->text
				);

			}else{ // the page does not exist
				$this->dieUsage("Request cancelled. Page ".$__pageTitle." does not exist.", 'param_xmldata');
			}
		}
		if(stristr($methodType,"d")){ // the requested method is delete
			if($__page->pageid != NULL && $__page->pageid != ""){ // the page exists
				// create the POM object for the requested page
				$__pom = new POMPage(
				$__pageTitle,
				$__page->text,
				array('POMExtendedParser'));

				// since @array will be read together with the SF elements unset it
				unset($__xmlData['sfdata']['page']['@attributes']);
				$__sfNames = array_keys($__xmlData['sfdata']['page']);

				foreach ($__sfNames  as $__sfName){
					$__sf = $__xmlData['sfdata']['page'][$__sfName];
					$__iterator = $__pom->getTemplateByTitle(utf8_decode($__sf['@attributes']['tmpl_name']))->listIterator();

					if (isset($__sf['@attributes']) && isset($__sf['@attributes']['mfi'])){
						// multiple SF instances detected
						// TODO: implement

					}else{
						$__template = &$__iterator->getNextNodeValueByReference(); # get reference for direct changes
						$__template = new POMSimpleText("");
					}
				}
				$__pom->sync();

				return $__serverUtility->updatePage(
				$__userCredentials,
				$__pageTitle,
				$__pom->text
				);

			}else{ // the page does not exist
				$this->dieUsage("Request cancelled. Page ".$__pageTitle." does not exist.", 'param_xmldata');
			}
		}
		return "";
	}

	/**
	 * Reads serialized data in JSON format and saves the requested chages.
	 *
	 * @param JSON $sfdata The posted data including page and SF attributes.
	 * @param string $methodType The action to be performed: c(create) or u(update) or d(delete).
	 * @param string $username The username provided.
	 * @param string $userId The user ID provided.
	 * @param string $loginToken The login token provided.
	 */
	public function setSerializedJsonForm($sfdata, $methodType, $username = NULL, $userId = NULL, $loginToken = NULL){
		$__jsonData = json_decode(str_replace('#U002B#', '+', $sfdata), true);
		
		$__serverUtility = new PCPServer();
		$__userCredentials = new PCPUserCredentials(
			$username,
			NULL,
			$userId,
			$loginToken);

		//print_r($__jsonData);die;
		$__pageNs = utf8_decode($__jsonData['sfdata']['page']['ns']);
		$__pageTitle = $__jsonData['sfdata']['page']['title'];
		$__pageRId = $__jsonData['sfdata']['page']['rid'];
		
		$__page = $__serverUtility->readPage(
			$__userCredentials,
			$__pageTitle,
			$__pageRId);
		
		if(stristr($methodType,"c")){ // the requested method is create
			if($__page->pageid == NULL || $__page->pageid == ""){
				// the page doesn't exist so we have to create it
				// the XML data is read and a template is created for each SF

				// since the attributes will be read together with the SF elements unset them
				unset($__jsonData['sfdata']['page']['title']);
				unset($__jsonData['sfdata']['page']['ns']);
				unset($__jsonData['sfdata']['page']['rid']);
				$__sfNames = array_keys($__jsonData['sfdata']['page']);
				$__tmplString = ""; // used for the creation of all template strings
				$__tmplNames = ""; // used for adding addtional info to the summary
				
				foreach ($__sfNames  as $__sfName){
					$__sf = $__jsonData['sfdata']['page'][$__sfName];

					if (isset($__sf['mfi'])){
						// multiple SF instancies detected - multiple SFs to be saved in one request
						// TODO: implement

					}else{
						// build the template string for each SF element
						foreach($__sf as $template){
							$__tmplString .= "\n{{".
								utf8_decode($template['tmpl_name'])."\n"; // the tempalte name
							$__tmplNames .= utf8_decode($template['tmpl_name'])."\t";
							// since @array will be read together with the SF field elements unset it
							foreach ($template as $__field){ // in the SF element are listed all SF fields
								// add the field name and value to the template
								if(array_key_exists('template_field', $__field)){
									$__tmplString .= "|".utf8_decode($__field['template_field']['field_name'])."=".utf8_decode($__field['cur_value'])."\n";
								}
							}
							$__tmplString .= "}}\n"; // add closing brackets to the template
						}
					}
				}

				return $__serverUtility->createPage(
				$__userCredentials,
				$__pageTitle,
				$__tmplString,
				"SF data added to the page via the SF API. Templates:\t".$__tmplNames
				);

			}else{
				// create the POM object for the requested page
				$__pom = new POMPage(
					$__pageTitle,
					$__page->text,
					array('POMExtendedParser'));

				// since the attributes will be read together with the SF elements unset them
				unset($__jsonData['sfdata']['page']['title']);
				unset($__jsonData['sfdata']['page']['ns']);
				unset($__jsonData['sfdata']['page']['rid']);
				$__sfNames = array_keys($__jsonData['sfdata']['page']);
				$__tmplString = $__pom->text; // used for the creation of all template strings - preserve the existing page text if any
				$__tmplNames = ""; // used for adding addtional info to the summary

				foreach ($__sfNames  as $__sfName){
					$__sf = $__jsonData['sfdata']['page'][$__sfName];
					foreach($__sf as $template){
						$__iterator = $__pom->getTemplateByTitle($template['tmpl_name'])->listIterator();

						if (isset($__sf['mfi'])){
							// multiple SF instances detected
							// TODO: implement
						}else{
							// build the template string for each SF element
							$__tmplString .= "\n{{".utf8_decode($template['tmpl_name'])."\n"; // the tempalte name
							$__tmplNames .= utf8_decode($__sf['tmpl_name'])."\t";
							// since @array will be read together with the SF field elements unset it
							unset($__sf['tmpl_name']);

							foreach ($template as $__field){ // in the SF element are listed all SF fields
								// add the field name and value to the template
								$__tmplString .= "|".utf8_decode($__field['template_field']['field_name'])
									."=".utf8_decode($__field['cur_value'])."\n";
							}
							$__tmplString .= "}}\n"; // add closing brackets to the template
						}
					}
				}

				return $__serverUtility->updatePage(
					$__userCredentials,
					$__pageTitle,
					$__tmplString,
					"SF data added to the page via the SF API. Templates:\t".$__tmplNames
					);
			}

		}
		if(stristr($methodType,"u")){ // the requested method is update
			if($__page->pageid != NULL && $__page->pageid != ""){ // the page exists
				// create the POM object for the requested page
				$__pom = new POMPage(
				$__pageTitle,
				$__page->text,
				array('POMExtendedParser'));

				// since the attributes will be read together with the SF elements unset them
				unset($__jsonData['sfdata']['page']['title']);
				unset($__jsonData['sfdata']['page']['ns']);
				unset($__jsonData['sfdata']['page']['rid']);
				$__sfNames = array_keys($__jsonData['sfdata']['page']);
				$__tmplNames = ""; // used for adding addtional info to the summary
				
				foreach ($__sfNames  as $__sfName){
					$__sf = $__jsonData['sfdata']['page'][$__sfName];
					foreach($__sf as $template){
						$__iterator = $__pom->getTemplateByTitle(utf8_decode($template['tmpl_name']))->listIterator();
						$__tmplNames .= $template." ";
					
						if (isset($__sf['mfi'])){
							// multiple SF instances detected
							// TODO: implement
						}else{
							$__template = &$__iterator->getNextNodeValueByReference(); # get reference for direct changes
							// since the attribute will be read together with the SF field elements unset it
							unset($template['tmpl_name']);
							//print_r($__sf); die;						
							foreach ($template as $__field){ // in the SF element are listed all SF fields
								if($__template->getParameter($__field['template_field']['field_name'])!== NULL){
									$__paramValue = &$__template->getParameterValue(utf8_decode($__field['template_field']['field_name']));
									$__paramValue = new POMSimpleText(utf8_decode($__field['cur_value']));								
								}
							}
						}
					}
				}

				$__pom->sync();
				//print($__pom->text);die;
				return $__serverUtility->updatePage(
				$__userCredentials,
				$__pageTitle,
				$__pom->text, 
				"SF data updated on the page via the SF API. Templates:\t".$__tmplNames
				);

			}else{ // the page does not exist
				$this->dieUsage("Request cancelled. Page ".$__pageTitle." does not exist.", 'param_xmldata');
			}
		}
		if(stristr($methodType,"d")){ // the requested method is delete
			if($__page->pageid != NULL && $__page->pageid != ""){ // the page exists
				// create the POM object for the requested page
				$__pom = new POMPage(
				$__pageTitle,
				$__page->text,
				array('POMExtendedParser'));

				// since the attributes will be read together with the SF elements unset them
				unset($__jsonData['sfdata']['page']['title']);
				unset($__jsonData['sfdata']['page']['ns']);
				unset($__jsonData['sfdata']['page']['rid']);
				$__sfNames = array_keys($__jsonData['sfdata']['page']);
				$__tmplNames = ""; // used for adding addtional info to the summary

				foreach ($__sfNames  as $__sfName){
					$__sf = $__jsonData['sfdata']['page'][$__sfName];
					$__iterator = $__pom->getTemplateByTitle(utf8_decode($__sf['tmpl_name']))->listIterator();
					$__tmplNames .= $__sf." ";

					if (isset($__sf['@attributes']['mfi'])){
						// multiple SF instances detected
						// TODO: implement

					}else{
						$__template = &$__iterator->getNextNodeValueByReference(); # get reference for direct changes
						$__template = new POMSimpleText("");
					}
				}
				$__pom->sync();

				return $__serverUtility->updatePage(
				$__userCredentials,
				$__pageTitle,
				$__pom->text,
				"SF data deleted on the page via the SF API. Templates:\t".$__tmplNames
				);

			}else{ // the page does not exist
				$this->dieUsage("Request cancelled. Page ".$__pageTitle." does not exist.", 'param_xmldata');
			}
		}
		return "";
	}

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}

	/**
	 * Converts an XML string to an associative array.
	 *
	 * @param string $xml
	 * @param boolean $recursive
	 * @return array The resulting array.
	 */
	private function XML2Array ( $xml , $recursive = false )
	{
		if ( $recursive )
		{
			$__array = $xml;

		}
		else
		{
			$__array = simplexml_load_string ( $xml );
		}

		$__newArray = array ();
		$__array = ( array ) $__array;
		foreach ( $__array as $__key => $__value )
		{
			$__value = ( array ) $__value ;
			if ( isset ( $__value [ 0 ] ) )
			{
				$__newArray [ $__key ] = trim ( $__value [ 0 ] ) ;
			}
			else
			{
				$__newArray [ $__key ] = $this->XML2Array ( $__value , true ) ;
			}
		}
		return $__newArray;
	}

	/**
	 * Gets the category tree based on a category name and sublevel number.
	 * The resulting array consists of:
	 * * the category name
	 * * catOptions child element having: SF=<sfname>|ROOT(the root point for categories - not a category itself)
	 * * children: the subcategories (based on the number sublevels)
	 *
	 * @param string $catName The starting category name.
	 * @param integer $sublevels The sublevels to eb checked. If the sublevel is equal 0, all sublevels are returned.
	 * @param string $substring Substring check upon category names.
	 * @return associativeArray The tree of categories.
	 */
	public function getCategoryTree($catName, $sublevels = 1, $substring){
		global $smwgDefaultStore;
		$__store = smwfGetSemanticStore();

		$__catHashmap = array(); // the resulting associative array
		$__tmpCats = array(); // temporary category name list

		if ($sublevels <0) {// safety check
			$__catHashmap[str_ireplace(' ','_',$catName)] = array();
			return $__catHashmap;
		}

		// get the direct subcategores for the requested category
		// if the category name is empty get the root categories
		if ($catName != '' && $catName != NULL){
			$__tmpCats = $__store->getDirectSubCategories(Title::newFromText($catName, NS_CATEGORY));
			if ($substring != ''){ // add only category names matching the substring
				foreach ($__tmpCats as $__tmpCat){
					if (stristr($__tmpCat[0]->getText(), $substring) !== false){
						$__catHashmap[str_ireplace(' ','_',$catName)][str_ireplace(' ','_',$__tmpCat[0]->getText())] = array();
					}
				}
			}else{
				foreach ($__tmpCats as $__tmpCat){
					$__catHashmap[str_ireplace(' ','_',$catName)][str_ireplace(' ','_',$__tmpCat[0]->getText())] = array();
				}
			}
			// check if there is a SF defined for the category
			$__sfName = SFLinkUtils::getFormsThatPagePointsTo($catName, NS_CATEGORY, '_SF_DF', '_SF_DF_BACKUP', SF_SP_HAS_DEFAULT_FORM);
			if ( $__sfName != NULL){
				$__catHashmap[str_ireplace(' ','_',$catName)]['catOptions'] = "Form:$__sfName[0]";
			}
		}else{
			$catName = 'root';

			// check if the substring is defined
			$__tmpCats = $__store->getRootCategories();
			if ($substring != ''){ // add only category names matching the substring
				foreach ($__tmpCats as $__tmpCat){
					if (stristr($__tmpCat[0]->getText(), $substring) !== false){
						$__catHashmap[$catName][str_ireplace(' ','_',$__tmpCat[0]->getText())] = array();
					}
				}
			}else{
				foreach ($__tmpCats as $__tmpCat){
					$__catHashmap[$catName][str_ireplace(' ','_',$__tmpCat[0]->getText())] = array();
				}
			}
			$__catHashmap[$catName]['catOptions'] = "ROOT";
		}

		// now check for the requested sublevels
		$__subcatCnt = count($__catHashmap[str_ireplace(' ','_',$catName)]);
		if ($__subcatCnt>0 && array_key_exists('catOptions', $__catHashmap[str_ireplace(' ','_',$catName)])){
			$__subcatCnt--; // decrease the counter if catOptions is set
		}
		if( $__subcatCnt ){
			if ($sublevels >1 ){
				foreach ($__catHashmap[str_ireplace(' ','_',$catName)] as $__subCategory=>$__emptySpace){
					if($__subCategory != 'catOptions'){
						$__catHashmap[str_ireplace(' ','_',$catName)] = array_merge( $__catHashmap[str_ireplace(' ','_',$catName)], $this->toArray($this->getCategoryTree($__subCategory, $sublevels-1, $substring)));
					}

				}
			}elseif($sublevels == 1){
				foreach ($__catHashmap[str_ireplace(' ','_',$catName)] as $__subCategory=>$__emptySpace){
					if($__subCategory != 'catOptions'){
						// check if there is a SF defined for the subcategories only if no further levels requested
						$__sfName = SFLinkUtils::getFormsThatPagePointsTo(
							$__subCategory, NS_CATEGORY, '_SF_DF', '_SF_DF_BACKUP', SF_SP_HAS_DEFAULT_FORM);
						if(count($__sfName) > 0){
							$__sfName = $__sfName[0];
						} else {
							$__sfName = null;
						}
						
						if ( $__sfName != NULL){
							$__catHashmap[str_ireplace(' ','_',$catName)][str_ireplace(' ','_',$__subCategory)]['catOptions'] = "Form:$__sfName";
						} else {
							$__catHashmap[str_ireplace(' ','_',$catName)][str_ireplace(' ','_',$__subCategory)] = array();
						}
					}
				}
			}elseif($sublevels == 0){
				foreach ($__catHashmap[str_ireplace(' ','_',$catName)] as $__subCategory=>$__emptySpace){
					if($__subCategory != 'catOptions'){
						$__catHashmap[str_ireplace(' ','_',$catName)] = array_merge( $__catHashmap[str_ireplace(' ','_',$catName)], $this->toArray($this->getCategoryTree($__subCategory, $sublevels, $substring)));
					}

				}
			}
		}
		return $__catHashmap;
	}

	/**
	 * Gets a list of all available SFs for a page or for the whole wiki.
	 *
	 * @param string $page The namespace:title of the page or ''(blank) for all SFs in the wiki
	 * @param string $substring Subtsting to search upon in the names of the SFs.
	 * @return array A list of SF names.
	 */
	public function getSFList($page, $substring){
		$__sfList = array();
		$__tmpListAlternateSF = array();
		$__tmpDefaultSF = NULL;

		if(strstr($page, ':') !== false){
			list($__pageNamespace,$__pageTitle) = split(':', $page);
		}else{
			$__pageTitle = $page;
			$__pageNamespace = 0;
		}

		if($__pageTitle == ''){
			// return all SFs, top element is 'root'
			$__tmpList = SFUtils::getAllForms();

			if($substring != '' ) {
				// check if the substring matches
				foreach ($__tmpList as $__tmpListEntry){
					if (stristr($__tmpListEntry, $substring) !== false){
						$__sfList['root'][str_ireplace(' ','_',$__tmpListEntry)] = array();
					}
				}
			}else{
				foreach ($__tmpList as $__tmpListEntry){
					$__sfList['root'][str_ireplace(' ','_',$__tmpListEntry)] = array();
				}
			}
		}else{
			$result = array();
			if($__pageNamespace !== 0){
				global $wgContLang;
				$__pageNamespace =  
					$wgContLang->getLocalNsIndex($__pageNamespace);
			}
			
			$sfList = SFLinkUtils::getFormsForArticle(
				new Article(Title::newFromText($__pageTitle, $__pageNamespace)));
			
		
			foreach($sfList as $key => $sf){
				if($substring != '' ) {
					if (stristr($sf, $substring) !== false){
						$result[str_ireplace(' ','_',$sf)] = array();
					}
				} else {
					$result[str_ireplace(' ','_',$sf)] = array();
				}
			}
			return array($__pageTitle => array('def' => $result));
			// get all SFs for the particular page
			// TODO: does alternate include the default form?
			global $wgContLang;
			// retrieve the store first
			$__store = smwfGetSemanticStore();

			// first determine which categories the page is assigned to
			$__pageCats = $__store->getCategoriesForInstance(
				Title::newFromText($__pageTitle, $wgContLang->getLocalNsIndex($__pageNamespace)));

			// for each category only a default form should exist
			foreach ($__pageCats as $__pageCat){
				$__tmpDefaultSFs = SFLinkUtils::
					getFormsThatPagePointsTo(
					$__pageCat->getText(), NS_CATEGORY, '_SF_DF', '_SF_DF_BACKUP', SF_SP_HAS_DEFAULT_FORM);
				if(count($__tmpDefaultSFs) > 0){
					$__tmpDefaultSF = $__tmpDefaultSFs[0];
					if($substring != '' ) {
						// check if the substring matches
						if (stristr($__tmpDefaultSF, $substring) !== false){
							$__sfList[$__pageTitle]['def'][str_ireplace(' ','_',$__tmpDefaultSF)] = array();
						}
					}else{
						$__sfList[$__pageTitle]['def'][str_ireplace(' ','_',$__tmpDefaultSF)] = array();
					}
				}
			}
			$__tmpDefaultSF = NULL;

			// change the store - getProperties is supported by the SMW store only ...
			$__store = smwfGetStore();


			// second determine which properties are defined on the page
			// default forms are only set via categories
			$__semData  = $__store->getSemanticData(Title::newFromText($__pageTitle, $wgContLang->getLocalNsIndex($__pageNamespace)));
			$__pageProps = $__semData->getProperties();

			// for each property a default and alternate forms may exist
			foreach ($__pageProps as $__pageProp){
				if($__pageProp->isVisible()){ // exclude internal set properties
					$__tmpListAlternateSF = SFLinkUtils::getAlternateForms(
					$__pageProp->getWikiPageValue()->getTitle()->getText(),
					$__pageProp->getWikiPageValue()->getNamespace());
					$__tmpDefaultSFs = SFLinkUtils::getDefaultForms(
					$__pageProp->getWikiPageValue()->getTitle()->getText(),
					$__pageProp->getWikiPageValue()->getNamespace());

					if(count($__tmpDefaultSFs) > 0){
							
						$__tmpDefaultSF = $__tmpDefaultSFs[0];

						if($substring != '' ) {
							// check if the substring matches
							foreach ($__tmpListAlternateSF as $__tmpListEntry){
								if (stristr($__tmpListEntry, $substring) !== false){
									$__sfList[$__pageTitle]['alt'][str_ireplace(' ','_',$__tmpListEntry)] = array();
								}
							}
							// ... and for the default form
							if (stristr($__tmpDefaultSF, $substring) !== false){
								$__sfList[$__pageTitle]['alt'][str_ireplace(' ','_',$__tmpDefaultSF)] = array();
							}
						}else{
							foreach ($__tmpListAlternateSF as $__tmpListEntry){
								$__sfList[$__pageTitle]['alt'][str_ireplace(' ','_',$__tmpListEntry)] = array();
							}
							if($__tmpDefaultSF != NULL){
								$__sfList[$__pageTitle]['alt'][str_ireplace(' ','_',$__tmpDefaultSF)] = array();
							}
						}
					}
				}
			}
		}
		return $__sfList;
	}

	/**
	 * Gets all pages for a specific SF. The pages returned have an instance of the requested SF defined.
	 *
	 * @param string $sfName The name of the SF.
	 * @param string $substring Subtsting to search upon in the names of the SFs.
	 * @return array A list of page names.
	 */
	public function getPageList($sfName, $substring){
		$__pageList = array();
		$__serverUtility = new PCPServer();
		$__store = smwfGetStore();

		if(strstr($sfName, ':') !== false){
			list($__sfNamespace,$__sfTitle) = split(':', $sfName);
		}else{
			$__sfTitle = $sfName;
		}

		if($__sfTitle == ''){
			// search for all SFs
			$__tmpList = SFUtils::getAllForms();

			// categories or properties which use a spcific SF
			// the structure is $__referencingAnnotations['root']['FORMNAME'][NS-NUMBER]['PAGETITLE']
			// 				    $__referencingAnnotations['root']['FORMNAME']['sfobj']
			$__referencingAnnotations = array();

			// first: get all categories / properties that have the SF as default form
			foreach ($__tmpList as $__tmpSF){
				// workaround: trigger an ASK query
				$__queryobj = 
					SMWQueryProcessor::createQuery("[[Has default form::$__tmpSF]]", array());
				$__queryobj->querymode = SMWQuery::MODE_INSTANCES;
				$__res = smwfGetStore()->getQueryResult($__queryobj);
				$__resCount = $__res->getCount();

				for($__i=0; $__i<$__resCount;$__i++){
					$__resArray = $__res->getNext();// SMWResultArray[]

					foreach($__resArray as $__resElement){ // object from class SMWResultArray
						$__tmpArr = $__resElement->getContent(); // SMWWikiPageValue[]
						$__resPage = $__tmpArr[0]; // object from class SMWWikiPageValue - only 1 element is expected
						$__referencingAnnotations[$__tmpSF][$__resPage->getNamespace()][$__resPage->getText()] = $__resPage->getTitle();
					}

				}
			}
			
			
			// second: get all categories / properties that have the SF as an alternate form
			$__queryobj = array();
			$__res = array();
			foreach ($__tmpList as $__tmpSF){
				// workaround: trigger an ASK query
				$__queryobj = SMWQueryProcessor::createQuery("[[Has alternate form::$__tmpSF]]", array());
				$__queryobj->querymode = SMWQuery::MODE_INSTANCES;
				$__res = smwfGetStore()->getQueryResult($__queryobj);
				$__resCount = $__res->getCount();

				for($__i=0; $__i<$__resCount;$__i++){
					$__resArray = $__res->getNext();// SMWResultArray[]

					foreach($__resArray as $__resElement){ // object from class SMWResultArray
						$__tmpArr = $__resElement->getContent(); // SMWWikiPageValue[]
						$__resPage = $__tmpArr[0]; // object from class SMWWikiPageValue - only 1 element is expected
						$__referencingAnnotations[$__tmpSF][$__resPage->getNamespace()][$__resPage->getText()] = $__resPage->getTitle();
					}

				}
				// now add the SF structure
				// we need at first the template title, but in future even comparision based on fields is possible
				if(isset($__referencingAnnotations[$__tmpSF][$__resPage->getNamespace()])){
					$__referencingAnnotations[$__tmpSF]['sfobj'] = $this->serializedForm($__tmpSF);
				}
			}

			// now determine the pages using the found categories / properties

			
			foreach(array_keys($__referencingAnnotations) as $__sformName){
				$__sfCategories = $__referencingAnnotations[$__sformName][NS_CATEGORY];
				$__sfProperties = $__referencingAnnotations[$__sformName][SMW_NS_PROPERTY];
				
				// build a complex ASK query for all categories and properties
				$__complexQuery = '';
				if(isset($__sfCategories))
				if ($__sfCategories !==NULL){
					foreach (array_keys($__sfCategories) as $__sfCategory){
						if($__complexQuery !== ''){
							$__complexQuery.= " OR [[Category:$__sfCategory]]";
						}else{
							$__complexQuery.= "[[Category:$__sfCategory]]";
						}
					}
				}
				
				if(isset($__sfProperties))
				if($__sfProperties !== NULL){
					foreach (array_keys($__sfProperties) as $__sfProperty){
						if($__complexQuery !== ''){
							$__complexQuery.= " OR [[$__sfProperty::+]]";
						}else{
							$__complexQuery.= "[[$__sfProperty::+]]";
						}
					}
				}
				
				$__queryobj = SMWQueryProcessor::createQuery($__complexQuery, array());
				$__queryobj->querymode = SMWQuery::MODE_INSTANCES;
				$__res = smwfGetStore()->getQueryResult($__queryobj);
				$__resCount = $__res->getCount();

				for($__i=0; $__i<$__resCount;$__i++){
					$__resArray = $__res->getNext();// SMWResultArray[]

					foreach($__resArray as $__resElement){ // object from class SMWResultArray
						$__tmpArr = $__resElement->getContent(); // SMWWikiPageValue[]
						$__resPage = $__tmpArr[0]; // object from class SMWWikiPageValue - only 1 element is expected

						// check if the substring matches
						if($substring != ''){
							if(stristr($__resPage->getText(), $substring)){
								// now read the POM of each page and search for the template used by the SF
								$__pcpPage= $__serverUtility->readPage(
									NULL,
									$__resPage->getText());
								$__pom = new POMPage(
									$__resPage->getText(),
									$__pcpPage->text,
									array('POMExtendedParser'));

								// search for the template
								foreach($__referencingAnnotations[$__sformName]['sfobj'] as $template){
									$__iterator = $__pom->getTemplateByTitle($template['tmpl_name'])->listIterator();
									if($__iterator->hasNext())
									{
										$__pageList['root'][str_replace(" ", "_",$__sformName)][str_replace(" ", "_", $__resPage->getText())] = array();
										$__pageList['root'][str_replace(" ", "_",$__sformName)][str_replace(" ", "_", $__resPage->getText())]['ns'] = $__pcpPage->ns;
										$__pageList['root'][str_replace(" ", "_",$__sformName)][str_replace(" ", "_", $__resPage->getText())]['rid'] = $__pcpPage->lastrevid;
									}
								}
							}
						}else{
							// now read the POM of each page and search for the template used by the SF
							$__pcpPage= $__serverUtility->readPage(
								NULL,
								$__resPage->getText());
							$__pom = new POMPage(
								$__resPage->getText(),
								$__pcpPage->text,
								array('POMExtendedParser'));
							// search for the template
							foreach($__referencingAnnotations[$__sformName]['sfobj'] as $template){
								$__iterator = $__pom->getTemplateByTitle($template['tmpl_name'])->listIterator();
								
								
								if($__iterator->hasNext()){
									$__pageList['root'][str_replace(" ", "_",$__sformName)][str_replace(" ", "_", $__resPage->getText())] = array();
									$__pageList['root'][str_replace(" ", "_",$__sformName)][str_replace(" ", "_", $__resPage->getText())]['ns'] = $__pcpPage->ns;
									$__pageList['root'][str_replace(" ", "_",$__sformName)][str_replace(" ", "_", $__resPage->getText())]['rid'] = $__pcpPage->lastrevid;
								}
							}
						}
					}
				}
			}
		}else{
			// search only for a single SF

			// categories or properties which use a spcific SF
			// the structure is $__referencingAnnotations['FORMNAME'][NS-NUMBER]['PAGETITLE']
			// 				    $__referencingAnnotations['FORMNAME']['sfobj']
			$__referencingAnnotations = array();


			// first: get all categories / properties that have the SF as default form
			// workaround: trigger an ASK query
			$__queryobj = SMWQueryProcessor::createQuery("[[Has default form::$__sfTitle]]", array());
			$__queryobj->querymode = SMWQuery::MODE_INSTANCES;
			$__res = smwfGetStore()->getQueryResult($__queryobj);
			$__resCount = $__res->getCount();

			for($__i=0; $__i<$__resCount;$__i++){
				$__resArray = $__res->getNext();// SMWResultArray[]

				foreach($__resArray as $__resElement){ // object from class SMWResultArray
					$__tmpArr = $__resElement->getContent(); // SMWWikiPageValue[]
					$__resPage = $__tmpArr[0]; // object from class SMWWikiPageValue - only 1 element is expected
					$__referencingAnnotations[$__sfTitle][$__resPage->getNamespace()][$__resPage->getText()] = $__resPage->getTitle();
				}

			}


			// second: get all categories / properties that have the SF as an alternate form
			$__queryobj = array();
			$__res = array();
			// workaround: trigger an ASK query
			$__queryobj = SMWQueryProcessor::createQuery("[[Has alternate form::$__sfTitle]]", array());
			$__queryobj->querymode = SMWQuery::MODE_INSTANCES;
			$__res = smwfGetStore()->getQueryResult($__queryobj);
			$__resCount = $__res->getCount();

			for($__i=0; $__i<$__resCount;$__i++){
				$__resArray = $__res->getNext();// SMWResultArray[]

				foreach($__resArray as $__resElement){ // object from class SMWResultArray
					$__tmpArr = $__resElement->getContent(); // SMWWikiPageValue[]
					$__resPage = $__tmpArr[0]; // object from class SMWWikiPageValue - only 1 element is expected
					$__referencingAnnotations[$__sfTitle][$__resPage->getNamespace()][$__resPage->getText()] = $__resPage->getTitle();
				}

			}
			// now add the SF structure
			// we need at first the template title, but in future even comparision based on fields is possible
			if(isset($__referencingAnnotations[$__sfTitle][$__resPage->getNamespace()])){
				$__referencingAnnotations[$__sfTitle]['sfobj'] = $this->serializedForm($__sfTitle);
			}


			// now determine the pages using the found categories / properties

			$__sfCategories = $__referencingAnnotations[$__sfTitle][NS_CATEGORY];
			$__sfProperties = $__referencingAnnotations[$__sfTitle][SMW_NS_PROPERTY];

			// build a complex ASK query for all categories and properties
			$__complexQuery = '';
			if(isset($__sfCategories))
			foreach (array_keys($__sfCategories) as $__sfCategory){
				if($__complexQuery !== ''){
					$__complexQuery.= " OR [[Category:$__sfCategory]]";
				}else{
					$__complexQuery.= "[[Category:$__sfCategory]]";
				}
			}

			if(isset($__sfProperties))
			foreach (array_keys($__sfProperties) as $__sfProperty){
				if($__complexQuery !== ''){
					$__complexQuery.= " OR [[$__sfProperty::+]]";
				}else{
					$__complexQuery.= "[[$__sfProperty::+]]";
				}
			}

			$__queryobj = SMWQueryProcessor::createQuery($__complexQuery, array());
			$__queryobj->querymode = SMWQuery::MODE_INSTANCES;
			$__res = smwfGetStore()->getQueryResult($__queryobj);
			$__resCount = $__res->getCount();

			for($__i=0; $__i<$__resCount;$__i++){
				$__resArray = $__res->getNext();// SMWResultArray[]

				foreach($__resArray as $__resElement){ // object from class SMWResultArray
					$__tmpArr = $__resElement->getContent(); // SMWWikiPageValue[]
					$__resPage = $__tmpArr[0]; // object from class SMWWikiPageValue - only 1 element is expected

					// check if the substring matches
					if($substring != ''){
						if(stristr($__resPage->getText(), $substring)){
							// now read the POM of each page and search for the template used by the SF
							$__pcpPage= $__serverUtility->readPage(
								NULL,
								$__resPage->getText());
							$__pom = new POMPage(
								$__resPage->getText(),
								$__pcpPage->text,
								array('POMExtendedParser'));

							// search for the template
							foreach($__referencingAnnotations[$__sfTitle]['sfobj'] as $template){
								$__iterator = $__pom->getTemplateByTitle($template['tmpl_name'])->listIterator();
								if($__iterator->hasNext())
								{
									$__pageList[str_replace(" ", "_", $__sfTitle)][str_replace(" ", "_", $__resPage->getText())] = array();
									$__pageList[str_replace(" ", "_", $__sfTitle)][str_replace(" ", "_", $__resPage->getText())]['ns'] = $__pcpPage->ns;
									$__pageList[str_replace(" ", "_", $__sfTitle)][str_replace(" ", "_", $__resPage->getText())]['rid'] = $__pcpPage->lastrevid;
								}
							}
						}
					}else{
						// now read the POM of each page and search for the template used by the SF
						$__pcpPage= $__serverUtility->readPage(
						NULL,
						$__resPage->getText());
						$__pom = new POMPage(
						$__resPage->getText(),
						$__pcpPage->text,
						array('POMExtendedParser'));

						// search for the template
						foreach($__referencingAnnotations[$__sfTitle]['sfobj'] as $template){
								$__iterator = $__pom->getTemplateByTitle($template['tmpl_name'])->listIterator();
							if($__iterator->hasNext())
							{
								$__pageList[str_replace(" ", "_", $__sfTitle)][str_replace(" ", "_", $__resPage->getText())] = array();
								$__pageList[str_replace(" ", "_", $__sfTitle)][str_replace(" ", "_", $__resPage->getText())]['ns'] = $__pcpPage->ns;
								$__pageList[str_replace(" ", "_", $__sfTitle)][str_replace(" ", "_", $__resPage->getText())]['rid'] = $__pcpPage->lastrevid;
							}
						}
					}
				}
			}
		}
		return $__pageList;
	}

	private function serializedForm($title){
		$__pageReader = new PCPServer();
		$__page = $__pageReader->readPage(NULL,"Form:".$title);

		return $this->formSerialize(
		$__page->text,
		false,
		$__page->text, $title);
	}

	/**
	 * Converts simple data (e.g. string) into arrays
	 *
	 * @param somedata $data
	 * @return array
	 */
	private function toArray($data)
	{
		if(is_array($data) || is_object($data))
		{

		}else{
			$__result[$data] = '';
			return $__result;
		}
		return $data;
	}
	
	/**
	 * Prepares the SF data for searialization, including the SF definition and the template data (if any).
	 *
	 * @param string $form_def The definition of the SF.
	 * @param boolean $source_is_page Set the flag if the source is a page.
	 * @param string $existing_page_content The wiki markup of the page using the SF (with template data).
	 * @param string $page_title The title of the page.
	 * @return hashmap All fields of the SF with attributes and template data.
	 */
	/*
	 * This method was taken from the patched SF_FormPrinter.inc
	 */
	public function formSerialize($form_def = '', $source_is_page = false, $existing_page_content = null, $page_title = null) {
		//	public function formSerialize($form_def, $source_is_page, $existing_page_content = null, $page_title = null, $page_name_formula = null) {
		global $wgRequest, $wgUser, $wgParser;
		global $sfgTabIndex; // used to represent the current tab index in the form
		global $sfgFieldNum; // used for setting various HTML IDs
		global $sfgJSValidationCalls; // array of Javascript calls to determine if page can be saved
	
		# define a var for all fields
		$__fields = array();
	
		// initialize some variables
		$sfgTabIndex = 1;
		$sfgFieldNum = 1;
		$source_page_matches_this_form = false;
		$form_page_title = NULL;		
		// $form_is_partial is true if:
		// (a) 'partial' == 1 in the arguments
		// (b) 'partial form' is found in the form definition
		// in the latter case, it may remain false until close to the end of
		// the parsing, so we have to assume that it will become a possibility
		$form_is_partial = false;
		$new_text = "";
	
		// if we have existing content and we're not in an active replacement
		// situation, preserve the original content. We do this because we want
		// to pass the original content on IF this is a partial form
		// TODO: A better approach here would be to pass the revision id of the
		// existing page content through the replace value, which would
		// minimize the html traffic and would allow us to do a concurrent
		// update check.  For now, we pass it through the hidden text field...
	
		if (! $wgRequest->getCheck('partial')) {
			$original_page_content = $existing_page_content;
		} else {
			$original_page_content = null;
			if($wgRequest->getCheck('free_text')) {
				$existing_page_content= $wgRequest->getVal('free_text');
				$form_is_partial = true;
			}
		}
	
		// disable all form elements if user doesn't have edit permission -
		// two different checks are needed, because editing permissions can be
		// set in different ways
		// HACK - sometimes we don't know the page name in advance, but we still
		// need to set a title here for testing permissions
		if ($page_title == '')
		$this->mPageTitle = Title::newFromText("Semantic Forms permissions test");
		else
		$this->mPageTitle = Title::newFromText($page_title);
		if ($wgUser->isAllowed('edit') && $this->mPageTitle->userCanEdit()) {
			$form_is_disabled = false;
			$form_text = "";
			// show "Your IP address will be recorded" warning if user is
			// anonymous - wikitext for bolding has to be replaced with HTML
			if ($wgUser->isAnon()) {
				$anon_edit_warning = preg_replace("/'''(.*)'''/", "<strong>$1</strong>", wfMsg('anoneditwarning'));
				$form_text .= "<p>$anon_edit_warning</p>\n";
			}
		} else {
			$form_is_disabled = true;
			// display a message to the user explaining why they can't edit the
			// page - borrowed heavily from EditPage.php
			if ( $wgUser->isAnon() ) {
				$skin = $wgUser->getSkin();
				$loginTitle = SpecialPage::getTitleFor( 'Userlogin' );
				$loginLink = $skin->makeKnownLinkObj( $loginTitle, wfMsgHtml( 'loginreqlink' ) );
				$form_text = wfMsgWikiHtml( 'whitelistedittext', $loginLink );
			} else {
				$form_text = wfMsg( 'protectedpagetext' );
			}
		}
		$javascript_text = "";
		$sfgJSValidationCalls = array();
		$fields_javascript_text = "";
	
		// Remove <noinclude> sections and <includeonly> tags from form definition
		$form_def = StringUtils::delimiterReplace('<noinclude>', '</noinclude>', '', $form_def);
		$form_def = strtr($form_def, array('<includeonly>' => '', '</includeonly>' => ''));
	
		// parse wiki-text
		// add '<nowiki>' tags around every triple-bracketed form definition
		// element, so that the wiki parser won't touch it - the parser will
		// remove the '<nowiki>' tags, leaving us with what we need
		global $sfgDisableWikiTextParsing;
		if (! $sfgDisableWikiTextParsing) {
			$form_def = "__NOEDITSECTION__" . strtr($form_def, array('{{{' => '<nowiki>{{{', '}}}' => '}}}</nowiki>'));
			$wgParser->mOptions = new ParserOptions();
			$wgParser->mOptions->initialiseFromUser($wgUser);
			$form_def = $wgParser->parse($form_def, $this->mPageTitle, $wgParser->mOptions)->getText();
		}
	
		// turn form definition file into an array of sections, one for each
		// template definition (plus the first section)
		$form_def_sections = array();
		$start_position = 0;
		$section_start = 0;
		$free_text_was_included = false;
		$free_text_preload_page = null;
		$all_values_for_template = array();
		// unencode and HTML-encoded representations of curly brackets and
		// pipes - this is a hack to allow for forms to include templates
		// that themselves contain form elements - the escaping is needed
		// to make sure that those elements don't get parsed too early
		$form_def = str_replace(array('&#123;', '&#124;', '&#125;'), array('{', '|', '}'), $form_def);
		// and another hack - replace the 'free text' standard input with
		// a field declaration to get it to be handled as a field
		$form_def = str_replace('standard input|free text', 'field|<freetext>', $form_def);
		while ($brackets_loc = strpos($form_def, "{{{", $start_position)) {
			$brackets_end_loc = strpos($form_def, "}}}", $brackets_loc);
			$bracketed_string = substr($form_def, $brackets_loc + 3, $brackets_end_loc - ($brackets_loc + 3));
			$tag_components = explode('|', $bracketed_string);
			$tag_title = trim($tag_components[0]);
			if ($tag_title == 'for template' || $tag_title == 'end template') {
				// create a section for everything up to here
				$section = substr($form_def, $section_start, $brackets_loc - $section_start);
				$form_def_sections[] = $section;
				$section_start = $brackets_loc;
			}
			$start_position = $brackets_loc + 1;
		} // end while
		$form_def_sections[] = trim(substr($form_def, $section_start));
		
		// cycle through form definition file (and possibly an existing article
		// as well), finding template and field declarations and replacing them
		// with form elements, either blank or pre-populated, as appropriate
		$all_fields = array();
		$data_text = "";
		$template_name = "";
		$allow_multiple = false;
		$instance_num = 0;
		$all_instances_printed = false;
		$strict_parsing = false;
		// initialize list of choosers (dropdowns with available templates)
		$choosers = array();
		for ($section_num = 0; $section_num < count($form_def_sections); $section_num++) {
			$tif = new SFTemplateInForm();
			$start_position = 0;
			$template_text = "";
			// the append is there to ensure that the original array doesn't get
			// modified; is it necessary?
			$section = " " . $form_def_sections[$section_num];
	
			while ($brackets_loc = strpos($section, '{{{', $start_position)) {
				$brackets_end_loc = strpos($section, "}}}", $brackets_loc);
				$bracketed_string = substr($section, $brackets_loc + 3, $brackets_end_loc - ($brackets_loc + 3));
				$tag_components = explode('|', $bracketed_string);
				$tag_title = trim($tag_components[0]);
				// =====================================================
				// for template processing
				// =====================================================
				if ($tag_title == 'for template') {
					$old_template_name = $template_name;
					$template_name = trim($tag_components[1]);
					$tif->template_name = $template_name;
					$query_template_name = str_replace(' ', '_', $template_name);
					// also replace periods with underlines, since that's what
					// POST does to strings anyway
					$query_template_name = str_replace('.', '_', $query_template_name);
					$chooser_name = false;
					$chooser_caption = false;
					// cycle through the other components
					for ($i = 2; $i < count($tag_components); $i++) {
						$component = $tag_components[$i];
						if ($component == 'multiple') $allow_multiple = true;
						if ($component == 'strict') $strict_parsing = true;
						$sub_components = explode('=', $component);
						if (count($sub_components) == 2) {
							if ($sub_components[0] == 'label') {
								$template_label = $sub_components[1];
							} elseif ($sub_components[0] == 'chooser') {
								$allow_multiple = true;
								$chooser_name = $sub_components[1];
							} elseif ($sub_components[0] == 'chooser caption') {
								$chooser_caption = $sub_components[1];
							}
						}
					}
					// if this is the first instance, add the label in the form
					if (($old_template_name != $template_name) && isset($template_label)) {
						// add a placeholder to the form text so the fieldset can be
						// hidden if chooser support demands it
						if ($chooser_name !== false)
						$form_text .= "<fieldset [[placeholder]] haschooser=true>\n";
						else
						$form_text .= "<fieldset>\n";
						$form_text .= "<legend>$template_label</legend>\n";
					}
					$template_text .= "{{" . $tif->template_name;
					# reads all the fields of the template definition page
					$all_fields = $tif->getAllFields();
					// remove template tag
					$section = substr_replace($section, '', $brackets_loc, $brackets_end_loc + 3 - $brackets_loc);
					$template_instance_query_values = $wgRequest->getArray($query_template_name);
					// if we are editing a page, and this template can be found more than
					// once in that page, and multiple values are allowed, repeat this
					// section
					$existing_template_text = null;
					// replace underlines with spaces in template name, to allow for
					// searching on either
					$search_template_str = str_replace('_', ' ', $tif->template_name);
					if ($source_is_page || $form_is_partial) {
						if ($allow_multiple) {
							// find instances of this template in the page -
							// if there's at least one, re-parse this section of the
							// definition form for the subsequent template instances in
							// this page; if there's none, don't include fields at all.
							// there has to be a more efficient way to handle multiple
							// instances of templates, one that doesn't involve re-parsing
							// the same tags, but I don't know what it is.
							if (stripos(str_replace('_', ' ', $existing_page_content), '{{' . $search_template_str) !== false) {
								$instance_num++;
							} else {
								$all_instances_printed = true;
							}
						}
						// get the first instance of this template on the page being edited,
						// even if there are more
						if (($start_char = stripos(str_replace('_', ' ', $existing_page_content), '{{' . $search_template_str)) !== false) {
							$fields_start_char = $start_char + 2 + strlen($search_template_str);
							// skip ahead to the first real character
							while (in_array($existing_page_content[$fields_start_char], array(' ', '\n', '|'))) {
								$fields_start_char++;
							}
							$template_contents = array('0' => '');
							// cycle through template call, splitting it up by pipes ('|'),
							// except when that pipe is part of a piped link
							$field = "";
							$uncompleted_square_brackets = 0;
							$uncompleted_curly_brackets = 2;
							$template_ended = false;
							for ($i = $fields_start_char; ! $template_ended && ($i < strlen($existing_page_content)); $i++) {
								$c = $existing_page_content[$i];
								if ($c == '[') {
									$uncompleted_square_brackets++;
								} elseif ($c == ']' && $uncompleted_square_brackets > 0) {
									$uncompleted_square_brackets--;
								} elseif ($c == '{') {
									$uncompleted_curly_brackets++;
								} elseif ($c == '}' && $uncompleted_curly_brackets > 0) {
									$uncompleted_curly_brackets--;
								}
								// handle an end to a field and/or template declaration
								$template_ended = ($uncompleted_curly_brackets == 0 && $uncompleted_square_brackets == 0);
								$field_ended = ($c == '|' && $uncompleted_square_brackets == 0);
								if ($template_ended || $field_ended) {
									// if this was the last character in the template, remove
									// the closing curly brackets
									if ($template_ended) {
										$field = substr($field, 0, -1);
									}
									// either there's an equals sign near the beginning or not -
									// handling is similar in either way; if there's no equals
									// sign, the index of this field becomes the key
									$sub_fields = explode('=', $field, 2);
									if (count($sub_fields) > 1) {
										$template_contents[trim($sub_fields[0])] = trim($sub_fields[1]);
									} else {
										$template_contents[] = trim($sub_fields[0]);
									}
									$field = '';
								} else {
									$field .= $c;
								}
							}
							$existing_template_text = substr($existing_page_content, $start_char, $i - $start_char);
							// now remove this template from the text being edited
							// if this is a partial form, establish a new insertion point
							if ($existing_page_content && $form_is_partial && $wgRequest->getCheck('partial')) {
								// if something already exists, set the new insertion point
								// to its position; otherwise just let it lie
								if (strpos($existing_page_content, $existing_template_text) !== false) {
									$existing_page_content = str_replace('{{{insertionpoint}}}', '', $existing_page_content);
									$existing_page_content = str_replace($existing_template_text, '{{{insertionpoint}}}', $existing_page_content);
								}
							} else {
								$existing_page_content = str_replace($existing_template_text, '', $existing_page_content);
							}
							// if this is not a multiple-instance template, and we've found
							// a match in the source page, there's a good chance that this
							// page was created with this form - note that, so we don't
							// send the user a warning
							// (multiple-instance templates have a greater chance of
							// getting repeated from one form to the next)
							if (! $allow_multiple) {
								$source_page_matches_this_form = true;
							}
						}
					}
					// if the input is from the form (meaning the user has hit one
					// of the bottom row of buttons), and we're dealing with a
					// multiple template, get the values for this instance of this
					// template, then delete them from the array, so we can get the
					// next group next time - the next() command for arrays doesn't
					// seem to work here
					if ((! $source_is_page) && $allow_multiple && $wgRequest) {
						$all_instances_printed = true;
						if ($old_template_name != $template_name) {
							$all_values_for_template = $wgRequest->getArray($query_template_name);
						}
						if ($all_values_for_template) {
							$cur_key = key($all_values_for_template);
							// skip the input coming in from the "starter" div
							if ($cur_key == 'num') {
								unset($all_values_for_template[$cur_key]);
								$cur_key = key($all_values_for_template);
							}
							if ($template_instance_query_values = current($all_values_for_template)) {
								$all_instances_printed = false;
								unset($all_values_for_template[$cur_key]);
							}
						}
					}
					//  save the template name
					$field = array();
					$field['tmpl_name'] = $tif->template_name;
					// =====================================================
					// end template processing
					// =====================================================
				} elseif ($tag_title == 'end template') {
					// remove this tag, reset some variables, and close off form HTML tag
					$section = substr_replace($section, '', $brackets_loc, $brackets_end_loc + 3 - $brackets_loc);
					$template_name = null;
					if (isset($template_label)) {
						$form_text .= "</fieldset>\n";
						unset ($template_label);
					}
					$allow_multiple = false;
					$all_instances_printed = false;
					$instance_num = 0;
					// if the hiding placeholder is still around, this fieldset should
					// be hidden because it is empty and choosers are being used. So,
					// hide it.
					$form_text = str_replace("[[placeholder]]", "style='display:none'", $form_text);
					
					$__fields["template".count($__fields)] = $field;
					
					// =====================================================
					// field processing
					// =====================================================
				} elseif ($tag_title == 'field') {
					$field_name = trim($tag_components[1]);
					// cycle through the other components
					$is_mandatory = false;
					$is_hidden = false;
					$is_restricted = false;
					$is_uploadable = false;
					$is_list = false;
					$input_type = null;
					$field_args = array();
					$default_value = "";
					$possible_values = null;
					$preload_page = null;
					for ($i = 2; $i < count($tag_components); $i++) {
						$component = trim($tag_components[$i]);
						if ($component == 'mandatory') {
							$is_mandatory = true;
						} elseif ($component == 'hidden') {
							$is_hidden = true;
						} elseif ($component == 'restricted') {
							$is_restricted = true;
						} elseif ($component == 'uploadable') {
							$field_args['is_uploadable'] = true;
						} elseif ($component == 'list') {
							$is_list = true;
						} elseif ($component == 'autocomplete') {
							$field_args['autocomplete'] = true;
						} elseif ($component == 'no autocomplete') {
							$field_args['no autocomplete'] = true;
						} elseif ($component == 'remote autocompletion') {
							$field_args['remote autocompletion'] = true;
						} else {
							$sub_components = explode('=', $component);
							if (count($sub_components) == 2) {
								if ($sub_components[0] == 'input type') {
									$input_type = $sub_components[1];
								} elseif ($sub_components[0] == 'default') {
									$default_value = $sub_components[1];
								} elseif ($sub_components[0] == 'preload') {
									// free text field has special handling
									if ($field_name == 'free text' || $field_name = '<freetext>') {
										$free_text_preload_page = $sub_components[1];
									} else {
										// this variable is not used
										$preload_page = $sub_components[1];
									}
								} elseif ($sub_components[0] == 'values') {
									$possible_values = explode(',', $sub_components[1]);
								} elseif ($sub_components[0] == 'values from category') {
									$possible_values = SFUtils::getAllPagesForCategory($sub_components[1], 10);
								} elseif ($sub_components[0] == 'values from concept') {
									$possible_values = SFUtils::getAllPagesForConcept($sub_components[1]);
								} else {
									$field_args[$sub_components[0]] = $sub_components[1];
								}
								// for backwards compatibility
								if ($sub_components[0] == 'autocomplete on' && $sub_components[1] == null) {
									$field_args['no autocomplete'] = true;
								}
							}
						}
					}
					$field_args['part_of_multiple'] = $allow_multiple;
					// get the value from the request, if it's there, and if it's not
					// an array
					$escaped_field_name = str_replace("'", "\'", $field_name);
					if (isset($template_instance_query_values) &&
					$template_instance_query_values != null &&
					array_key_exists($escaped_field_name, $template_instance_query_values)) {
						$field_query_val = $template_instance_query_values[$escaped_field_name];
						if ($field_query_val && ! is_array($field_query_val)) {
							$cur_value = $field_query_val;
						}
					} else
					$cur_value = '';
					if ($cur_value && ! is_array($cur_value)) {
						# no escaping needed
						// $cur_value = str_replace('"', '&quot;', $cur_value);
						
					}
	
					if ($cur_value == null) {
						// set to default value specified in the form, if it's there
						$cur_value = $default_value;
					}
	
					// if the user is starting to edit a page, and that page contains
					// the current template being processed, get the current template
					// field's value in the existing page
					if ($source_is_page && (! empty($existing_template_text))) {
						if (isset($template_contents[$field_name])) {
							$cur_value = $template_contents[$field_name];
						} else
						$cur_value = '';
						if ($cur_value) {
							# no escaping needed
							// $cur_value = str_replace('"', '&quot;', $cur_value);
						}
					}
	
					// handle the free text field - if it was declared as
					// "field|free text" (a deprecated usage), it has to be outside
					// of a template
					if (($template_name == '' && $field_name == 'free text') ||
					$field_name == '<freetext>') {
						// add placeholders for the free text in both the form and
						// the page, using <free_text> tags - once all the free text
						// is known (at the end), it will get substituted in
						if ($is_hidden) {
							$new_text = SFFormUtils::hiddenFieldHTML('free_text', '<free_text>');
						} else {
							if (! array_key_exists('rows', $field_args))
							$field_args['rows'] = 5;
							if (! array_key_exists('cols', $field_args))
							$field_args['cols'] = 80;
							$sfgTabIndex++;
							$sfgFieldNum++;
							list($new_text, $new_javascript_text) = SFFormInputs::textAreaHTML('<free_text>', 'free_text', false, ($form_is_disabled || $is_restricted), $field_args);
							$fields_javascript_text .= $new_javascript_text;
						}
						$free_text_was_included = true;
						// add a similar placeholder to the data text
						$data_text .= "<free_text>\n";
					}
	
					if ($template_name == '') {
						$section = substr_replace($section, $new_text, $brackets_loc, $brackets_end_loc + 3 - $brackets_loc);
					} else {
						if (is_array($cur_value)) {
							// first, check if it's a list
							if (array_key_exists('is_list', $cur_value) &&
							$cur_value['is_list'] == true) {
								$cur_value_in_template = "";
								if (array_key_exists('delimiter', $field_args)) {
									$delimiter = $field_args['delimiter'];
								} else {
									$delimiter = ",";
								}
								foreach ($cur_value as $key => $val) {
									if ($key !== "is_list") {
										if ($cur_value_in_template != "") {
											$cur_value_in_template .= $delimiter . " ";
										}
										$cur_value_in_template .= $val;
									}
								}
							} else {
								// otherwise:
								// if it has 1 or 2 elements, assume it's a checkbox; if it has
								// 3 elements, assume it's a date
								// - this handling will have to get more complex if other
								// possibilities get added
								if (count($cur_value) == 1) {
									// manually load SMW's message values here, in case they
									// didn't get loaded before
									wfLoadExtensionMessages('SemanticMediaWiki');
									$words_for_false = explode(',', wfMsgForContent('smw_false_words'));
									// for each language, there's a series of words that are
									// equal to false - get the word in the series that matches
									// "no"; generally, that's the third word
									$index_of_no = 2;
									if (count($words_for_false) > $index_of_no) {
										$no = ucwords($words_for_false[$index_of_no]);
									} elseif (count($words_for_false) == 0) {
										$no = "0"; // some safe value if no words are found
									} else {
										$no = ucwords($words_for_false[0]);
									}
									$cur_value_in_template = $no;
								} elseif (count($cur_value) == 2) {
									wfLoadExtensionMessages('SemanticMediaWiki');
									$words_for_true = explode(',', wfMsgForContent('smw_true_words'));
									// get the value in the 'true' series that tends to be "yes",
									// and go with that one - generally, that's the third word
									$index_of_yes = 2;
									if (count($words_for_true) > $index_of_yes) {
										$yes = ucwords($words_for_true[$index_of_yes]);
									} elseif (count($words_for_true) == 0) {
										$yes = "1"; // some safe value if no words are found
									} else {
										$yes = ucwords($words_for_true[0]);
									}
									$cur_value_in_template = $yes;
									// if it's 3 or greater, assume it's a date or datetime
								} elseif (count($cur_value) >= 3) {
									$month = $cur_value['month'];
									$day = $cur_value['day'];
									if ($day != '') {
										global $wgAmericanDates;
										if ($wgAmericanDates == false) {
											// pad out day to always be two digits
											$day = str_pad($day, 2, "0", STR_PAD_LEFT);
										}
									}
									$year = $cur_value['year'];
									if (isset($cur_value['hour'])) $hour = $cur_value['hour'];
									if (isset($cur_value['minute'])) $minute = $cur_value['minute'];
									if (isset($cur_value['second'])) $second = $cur_value['second'];
									if (isset($cur_value['ampm24h'])) $ampm24h = $cur_value['ampm24h'];
									if (isset($cur_value['timezone'])) $timezone = $cur_value['timezone'];
									if ($month != '' && $day != '' && $year != '') {
										// special handling for American dates - otherwise, just
										// the standard year/month/day (where month is a number)
										global $wgAmericanDates;
										if ($wgAmericanDates == true) {
											$cur_value_in_template = "$month $day, $year";
										} else {
											$cur_value_in_template = "$year/$month/$day";
										}
										// include whatever time information we have
										if(isset($hour)) $cur_value_in_template .= " " . str_pad(intval(substr($hour,0,2)),2,'0',STR_PAD_LEFT) . ":" . str_pad(intval(substr($minute,0,2)),2,'0',STR_PAD_LEFT);
										if(isset($second)) $cur_value_in_template .= ":" . str_pad(intval(substr($second,0,2)),2,'0',STR_PAD_LEFT);
										if(isset($ampm24h)) $cur_value_in_template .= " $ampm24h";
										if(isset($timezone)) $cur_value_in_template .= " $timezone";
									} else {
										$cur_value_in_template = "";
									}
								}
							}
						} else { // value is not an array
							$cur_value_in_template = $cur_value;
						}
						if ($query_template_name == null || $query_template_name == '')
						$input_name = $field_name;
						elseif ($allow_multiple)
						// 'num' will get replaced by an actual index, either in PHP
						// or in Javascript, later on
						$input_name = $query_template_name . '[num][' . $field_name . ']';
						else
						$input_name = $query_template_name . '[' . $field_name . ']';
					
						// disable this field if either the whole form is disabled, or
						// it's a restricted field and user doesn't have sysop privileges
						$is_disabled = ($form_is_disabled ||
						($is_restricted && (! $wgUser || ! $wgUser->isAllowed('editrestrictedfields'))));
						// create an SFFormTemplateField instance based on all the
						// parameters in the form definition, and any information from
						// the template definition (contained in the $all_fields parameter)
	
						# creation of a form field from the definition page
						$possible_values['_element'] = "value";
						$form_field = $this->createFromDefinitionForSerialization($field_name, $input_name,
						$is_mandatory, $is_hidden, $is_uploadable, $possible_values, $is_disabled,
						$is_list, $input_type, $field_args, $all_fields, $strict_parsing);
						// if this is not part of a 'multiple' template, incrememt the
						// global tab index (used for correct tabbing)
						if (! $field_args['part_of_multiple'])
						$sfgTabIndex++;
						// increment the global field number regardless
						$sfgFieldNum++;
						// if the field is a date field, and its default value was set
						// to 'now', and it has no current value, set $cur_value to be
						// the current date
						if ($default_value == 'now' &&
						// if the date is hidden, cur_value will already be set
						// to the default value
						($cur_value == '' || $cur_value == 'now')) {
							if ($input_type == 'date' || $input_type == 'datetime' ||
							$input_type == 'datetime with timezone' ||
							($input_type == '' && $form_field->template_field->field_type == 'Date')) {
								$cur_time = time();
								$year = date("Y", $cur_time);
								$month = date("n", $cur_time);
								$day = date("j", $cur_time);
								global $wgAmericanDates, $sfg24HourTime;
								if ($wgAmericanDates == true) {
									$month_names = SFFormUtils::getMonthNames();
									$month_name = $month_names[$month - 1];
									$cur_value_in_template = "$month_name $day, $year";
								} else {
									$cur_value_in_template = "$year/$month/$day";
								}
								if ($input_type ==  'datetime' || $input_type == 'datetime with timezone') {
									if ($sfg24HourTime) {
										$hour = str_pad(intval(substr(date("G", $cur_time),0,2)),2,'0',STR_PAD_LEFT);
									} else {
										$hour = str_pad(intval(substr(date("g", $cur_time),0,2)),2,'0',STR_PAD_LEFT);
									}
									$minute = str_pad(intval(substr(date("i", $cur_time),0,2)),2,'0',STR_PAD_LEFT);
									$second = str_pad(intval(substr(date("s", $cur_time),0,2)),2,'0',STR_PAD_LEFT);
									if ($sfg24HourTime) {
										$cur_value_in_template .= " $hour:$minute:$second";
									} else {
										$ampm = date("A", $cur_time);
										$cur_value_in_template .= " $hour:$minute:$second $ampm";
									}
								}
								if ($input_type == 'datetime with timezone') {
									$timezone = date("T", $cur_time);
									$cur_value_in_template .= " $timezone";
								}
							}
						}
						// if the field is a text field, and its default value was set
						// to 'current user', and it has no current value, set $cur_value
						// to be the current user
						if ($default_value == 'current user' &&
						// if the date is hidden, cur_value will already be set
						// to the default value
						($cur_value == '' || $cur_value == 'current user')) {
							if ($input_type == 'text' || $input_type == '') {
								$cur_value_in_template = $wgUser->getName();
								$cur_value = $cur_value_in_template;
							}
						}
						
						# field + field value
						$form_field->cur_value = $cur_value;						
						# possible_values hack
						$__tmpValues = $form_field->template_field->possible_values;
						$form_field->template_field->possible_values = array();
						if($__tmpValues != NULL)
						{
							foreach ($__tmpValues as $key=>$value){
							$form_field->template_field->possible_values["value".$key] = $value;
							}
						}						
	
						$field["field".count($field)] = $this->toArrayForSerialize($form_field);
						
						$new_text = "dummy"; // set only in order to break						
	
						if ($new_text) {
							$section = substr_replace($section, $new_text, $brackets_loc, $brackets_end_loc + 3 - $brackets_loc);
						} else {
							$start_position = $brackets_end_loc;
						}
					}					
				} else { // tag is not one of the three allowed values
					// ignore tag
					$start_position = $brackets_end_loc;
				} // end if
			} // end while
		} // end for
		
		// get free text, and add to page data, as well as retroactively
		// inserting it into the form
	
		// If $form_is_partial is true then either:
		// (a) we're processing a replacement (param 'partial' == 1)
		// (b) we're sending out something to be replaced (param 'partial' is missing)
		if ($form_is_partial) {
			if(!$wgRequest->getCheck('partial')) {
				$free_text = $original_page_content;
				$form_text .= SFFormUtils::hiddenFieldHTML('partial', 1);
			} else {
				$free_text = null;
				$existing_page_content = preg_replace('/²\{(.*?)\}²/s', '{{\1}}', $existing_page_content);
				$existing_page_content = preg_replace('/\{\{\{insertionpoint\}\}\}/', '', $existing_page_content);
				$existing_page_content = Sanitizer::safeEncodeAttribute($existing_page_content);
			}
		} elseif ($source_is_page) {
			// if the page is the source, free_text will just be whatever in the
			// page hasn't already been inserted into the form
			$free_text = trim($existing_page_content);
			// or get it from a form submission
		} elseif ($wgRequest->getCheck('free_text')) {
			$free_text = $wgRequest->getVal('free_text');
			if (! $free_text_was_included) {
				$data_text .= "<free_text>";
			}
			// or get it from the form definition
		} elseif ($free_text_preload_page != null) {
			$free_text = SFFormUtils::getPreloadedText($free_text_preload_page);
		} else {
			$free_text = null;
		}
		# the free text is set here
		// if the FCKeditor extension is installed, use that for the free text input
		global $wgFCKEditorDir;
		if ($wgFCKEditorDir) {
			$showFCKEditor = SFFormUtils::getShowFCKEditor();
			$free_text = htmlspecialchars( $free_text );
			if($showFCKEditor & RTE_VISIBLE) {
				$free_text = SFFormUtils::prepareTextForFCK($free_text);
			}
		} else {
			$showFCKEditor = 0;
			$free_text = Sanitizer::safeEncodeAttribute($free_text);
		}
		// now that we have it, substitute free text into the form and page
		$form_text = str_replace('<free_text>', $free_text, $form_text);
		$data_text = str_replace('<free_text>', $free_text, $data_text);
	
		# return the fields		
		return $__fields;
	}
	
	/*
	* This method was taken from the patched SF_FormPrinter.inc
	*/
	function toArrayForSerialize($data){
		if(is_array($data) || is_object($data))
		{
			if(get_class($data) =='POMPage'){
				$__pom = new POMPage($data->titel, $data->text);
				$__result = array();
				$__elementsIterator = $__pom->getElements()->listIterator();
				$__result["page"] = $this->toArrayForSerialize ($__pcpPage);
				$__cnt = 0;
				while($__elementsIterator->hasNext()){
					$__element = &$__elementsIterator->getNextNodeValueByReference();
					$__array = $this->toArrayForSerialize($__element);
					$__result["page"][get_class($__element)][$__cnt] = $__array;
					$__cnt++;
				}
				return $__result;
			}
			$__result = array();
	
			foreach ($data as $__key => $__value)
			{
				$__result[$__key] = $this->toArrayForSerialize($__value);
			}
			return $__result;
		}
		return $data;
	}
	
	/*
	 * taken from the patched SF_FormField.inc
	 */
	function createFromDefinitionForSerialization($field_name, $input_name, $is_mandatory, $is_hidden, $is_uploadable, $possible_values, $is_disabled, $is_list, $input_type, $field_args, $all_fields, $strict_parsing) {
		// see if this field matches one of the fields defined for this template -
		// if it is, use all available information about that field; if it's not,
		// either include it in the form or not, depending on whether the
		// template has a 'strict' setting in the form definition
		$the_field = null;
		foreach ($all_fields as $cur_field) {
			if ($field_name == $cur_field->field_name) {
				$the_field = $cur_field;
				break;
			}
		}
		if ($the_field == null) {
			if ($strict_parsing) {
				$dummy_ftf = new SFFormField();
				$dummy_ftf->template_field = new SFTemplateField();
				$dummy_ftf->is_list = false;
				$dummy_ftf->field_args = array();
				return $dummy_ftf;
			}
			$the_field = new SFTemplateField();
		}
	
		// create an SFFormTemplateField object, containing this field as well
		// as settings from the form definition file
		$f = new SFFormField();
		$f->template_field = $the_field;
		$f->is_mandatory = $is_mandatory;
		$f->is_hidden = $is_hidden;
		$f->is_uploadable = $is_uploadable;
		$f->possible_values = $possible_values;
		$f->input_type = $input_type;		
		//$f->input_name = $input_name; // not needed
		$f->is_disabled = $is_disabled;
		$f->is_list = $is_list;
		// add field args directly
		if(array_key_exists('autocomplete on category', $field_args)){
			$f->autocomplete_category = $field_args['autocomplete on category'];
		}
		$f->part_of_multiple = $field_args['part_of_multiple'];
		
		return $f;
	}
}


