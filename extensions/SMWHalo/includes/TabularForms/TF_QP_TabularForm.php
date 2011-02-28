<?php

/*
 * Query printer which displays a tabular form
 */
class TFTabularFormQueryPrinter extends SMWResultPrinter {

	/*
	 * Returns printer name
	 */
	public function getName() {
		return 'Tabular Form';
	}

	/*
	 * Returns the HTML output of this query printer
	 */
	protected function getResultText( $queryResult, $outputMode ) {
		$this->isHTML = true;
		
		$tabularFormData = new TFTabularFormData($queryResult, $this->m_params, $this->mLinker,
			$this->linkFurtherResults( $queryResult));
		
		if(array_key_exists(TF_SHOW_AJAX_LOADER_HTML_PARAM, $this->m_params) 
				&& $this->m_params[TF_SHOW_AJAX_LOADER_HTML_PARAM] == 'false'){
					
			//the tabular form HTML must be displayed		
			$html = $tabularFormData->getTabularFormHTML($this->m_params[TF_TABULAR_FORM_ID_PARAM]);
		} else {
			//the Ajax loader HTML must be displayed
			$html = $tabularFormData->getAjaxLoaderHTML(); 
		}
		
		return $html;
	}

	public function getParameters() {
		//todo: deal with parameters
		//todo: add no edit and no delete parameter
		return array();
	}

}


/*
 * Represents a tabular form and prints its
 * HTML representations
 */
class TFTabularFormData {
	
	private $formRowsData = array();
	private $annotationPrintRequests = array();
	private $templateParameterPrintRequests = array();
	private $templateParameterPrintRequestLabels = array();
	private $queryResult;
	private $outputMode;
	private $queryParams;
	private $linker;
	private $subjectColumnLabel;
	private $getSubjectFromFirstPrintRequest = false;
	private $hasFurtherResults;
	private $isSPARQLQuery = false;
	private $enableInstanceDelete = false;
	private $enableInstanceAdd = false;
		
	public function __construct($queryResult, $queryParams, $linker, $hasFurtherResults){
		$this->queryResult = $queryResult;
		$this->outputMode = SMW_OUTPUT_HTML;
		$this->queryParams = $queryParams;
		$this->linker = $linker;
		$this->hasFurtherResults = $hasFurtherResults;
		
		if($this->queryResult instanceof SMWHaloQueryResult && 
				$this->queryResult->getQuery() instanceof SMWSPARQLQuery && $this->queryResult->getQuery()->fromASK){
			$this->isSPARQLQuery = true;			
		}
		
		$this->initializeAnnotationPrintRequests();
		
		$link = $this->queryResult->getQueryLink();
		
		$this->initializeTemplateParameterPrintRequests();
		
		if(array_key_exists('enable add', $this->queryParams)
				&& $this->queryParams['enable add'] == true){
			$this->enableInstanceAdd = true;
		}
		
		if(array_key_exists('enable delete', $this->queryParams)
				&& $this->queryParams['enable delete'] == true){
			$this->enableInstanceDelete = true;
		}
	}
	
	/*
	 * Returns the HTML of the Ajax loader
	 */
	public function getAjaxLoaderHTML(){
		global $tfgTabularFormCount;
		$tfgTabularFormCount += 1;
		
		$html = '';
		
		$html .= '<div id="tabf_container_'.$tfgTabularFormCount.'" class="tabf_container">';
		
		//todo:LANGUAGE
		global $smwgHaloScriptPath;
		$html .= '<div class="tabf_loader">';
		$html .= 'Tabular form is loading'.' ';
		$html .= '<img title="Loading Tabular Form" src="'.$smwgHaloScriptPath.'/skins/TabularForms/Pending.gif"></img>';
		$html .= '</div>';
		
		//display serialized query
		$html .= '<span class="tabf_query_serialization" style="display: none" isSPARQL="'.$this->isSPARQLQuery.'">';
		
		$query = array();
		foreach($this->queryParams as $param => $value){
			if(!($param == 'mainlabel' && $value == '-')){
				if(strlen($value) > 0){
					$param .= '='.$value;
				}
				$query[] = $param;
			}
		}
		
		$query[] = $this->queryResult->getQueryString();
		
		foreach($this->annotationPrintRequests as $annotation){
			if($annotation['title'] == '__Category__'){
				//replace internal category id
				$annotation['title'] = 'Category';	
			} 
			
			if(strlen($annotation['rawlabel']) > 0){
				$query[] = '?'.$annotation['title'].'='.$annotation['rawlabel'];
			} else {
				$query[] = '?'.$annotation['title'];
			}
		}
		
		$html .= json_encode($query);
		$html .= '</span>';
		
		$html .= '<div class="tabf_table_container" width="100%" style="display: none">';
		
		$html .= '</div>';
		
		$html .= '</div>';
		
		return $html;
	}
	
	/*
	 * Returns the HTML for this tabular form
	 */
	public function getTabularFormHTML($tabularFormId){
		
		//this must be done since we use the first print request as subject column
		//in the SPARQL case
		if($this->isSPARQLQuery){
			unset($this->annotationPrintRequests[0]);
		}
		
		$this->initializeAnnotationAutocompletion();
		
		// process each query result row
		while ( $row = $this->queryResult->getNext() ) {
			$this->initializeFormRowData($row, $this->getSubjectFromFirstPrintRequest);
		}
		$this->mergeTemplateParametersRowData();
		
		
		$html = '';
		
		$html .= '<table border="0" cellspacing="0" cellpadding="0" width="100%">';
		
		$html .= $this->addTableHeaderHTML();
		
		foreach($this->formRowsData as $rowData){
			$html .= $this->addRowHTML($rowData);
		}
		
		$html .= $this->addTabularFormFooterHTML($tabularFormId);
		
		$html .= $this->addTabularFormAddRowTemplateHTML($tabularFormId);
		
		$html .= '</table>';
		
		$html .= '<textarea class="tabf_rowindex_comparator" style="visibility: hidden; height: 1em"></textarea>';
		
		return $html;
	}
	
	
	/*
	 * Get range property for annotation print requests
	 */
	private function initializeAnnotationAutocompletion(){
		
		foreach($this->annotationPrintRequests as $key => $annotation){
			if($annotation['title'] == '__Category__'){
				$this->annotationPrintRequests[$key]['autocomplete'] = 'ask: [[:Category:+]]';
			} else {
				//default autocompletion for properties is all
				$this->annotationPrintRequests[$key]['autocomplete'] = 'all';
				
				$prop = Title::newFromText($this->annotationPrintRequests[$key]['title'], SMW_NS_PROPERTY);
				if($prop->exists()){
					$store = smwfNewBaseStore();
					$semanticData = $store->getSemanticData($prop);
					$annotations = $semanticData->getProperties();
					
					//get type
					$type = null;
					if(array_key_exists('Has_type', $annotations)){
						$type = $semanticData->getPropertyValues($annotations['Has_type']);
						$idx = array_keys($type);
						$idx = $idx[0];
						$type = $type[$idx]->getShortWikiText();
					}
					
					
					if($type == null || strtolower($type) == 'page'){
						//check if there is a range defined
						if(array_key_exists('Has_domain_and_range', $annotations)){
							$range = $semanticData->getPropertyValues($annotations['Has_domain_and_range']);
							if(is_array($range) && array_key_exists(0, $range)){
								if($range[0] instanceof SMWRecordValue){
									$range = $range[0]->getDVs();
									if(array_key_exists(1, $range)){
										$range = $range[1]->getShortWikiText();
										
										$this->annotationPrintRequests[$key]['autocomplete'] = 'ask: [['.$range.']]';
									}
								}
							}
						}		
					} else {
						$this->annotationPrintRequests[$key]['autocomplete'] =  
							'annotation-value: Property:'.$this->annotationPrintRequests[$key]['title'];
					}
				}
			}
		}
	}
	
	
	/*
	 * Get column headings html
	 */
	private function addTableHeaderHTML(){
		global $smwgScriptPath;
		
		$html ='<tr>';
		
		//add subject column
		$html .= '<th class="tabf_column_header">';
		$html .= '<a href="#" class="sortheader" onclick="tf.startRowSort(event);return false;">';
		$html .= '<span class="sortarrow"><img alt="[&lt;&gt;]" src="'.$smwgScriptPath.'/skins/images/sort_none.gif"/>';
		$html .= '</span></a>&nbsp;';
		$html .= '<span>';
		$html .= $this->subjectColumnLabel;
		$html .= '</span>';
		$html .= '</th>';
		
		//add annotation columns
		foreach($this->annotationPrintRequests as $annotation){
			$html .= '<th class="tabf_column_header" field-address="'.$annotation['title'].'" is-template="false">';
			$html .= '<a href="#" class="sortheader" onclick="tf.startRowSort(event);return false;">';
			$html .= '<span class="sortarrow"><img alt="[&lt;&gt;]" src="'.$smwgScriptPath.'/skins/images/sort_none.gif"/>';
			$html .= '</span></a>&nbsp;';
			$html .= '<span>';
			$html .= $annotation['label'];
			$html .= '</span>';
			$html .= '</th>';
		}
		
		//add template parameter columns
		foreach($this->templateParameterPrintRequests as $template => $params){
			foreach($params as $param => $dC){
				$html .= '<th class="tabf_column_header" field-address="'.$template.'#'.$param.'" is-template="true">';
				$html .= '<a href="#" class="sortheader" onclick="tf.startRowSort(event);return false;">';
				$html .= '<span class="sortarrow"><img alt="[&lt;&gt;]" src="'.$smwgScriptPath.'/skins/images/sort_none.gif"/>';
				$html .= '</span></a>&nbsp;';
				$html .= '<span>';
				if(array_key_exists($template, $this->templateParameterPrintRequestLabels) 
						&& array_key_exists($param, $this->templateParameterPrintRequestLabels[$template])){
					$html .= $this->templateParameterPrintRequestLabels[$template][$param];	
				} else {
					$html .= $template.'#'.$param;
				}
				$html .= '</span>';
				$html .= '</th>';
			}
		}
		
		//add status column
		$html .= '<th>';
		$html .= '<a href="#" class="sortheader" onclick="tf.startRowSort(event);return false;">';
		$html .= '<span class="sortarrow"><img alt="[&lt;&gt;]" src="'.$smwgScriptPath.'/skins/images/sort_none.gif"/>';
		$html .= '</span></a>';
		$html .= '</th>';
		
		$html.= '</tr>';
		return $html;
	}
	
	/*
	 * Adds invisible row add the end of table. This row is used as
	 * template for adding new instances.
	 */
	private function addTabularFormAddRowTemplateHTML(){
		$html = '<tr style="display: none" class="tabf_table_row">';
		
		$html .= '<td revision-id="-1" ><textarea rows="1"></textarea>';
		$html .= '<input class="tabf-delete-button" type="button" value="Delete" style="display: none" onclick="tf.deleteInstance(event)"/>';
		$html .= '</td>';

		//Add cells for annotations
		foreach($this->annotationPrintRequests as $annotation){
			$html .= '<td>';
			
			$autocomplete = '';
			if(!is_null($annotation['autocomplete'])){
				$autocompletion = 'class="wickEnabled" constraints="';
				$autocompletion .= $annotation['autocomplete'];
				$autocompletion .= '"';
			}
			
			$html .= "<textarea ".$autocompletion." rows='1' originalValue='' ></textarea>";
			
			$html .= '</td>';
		}
		
		//Add template cells
		foreach($this->templateParameterPrintRequests as $template => $params){
			foreach($params as $param){
				$html .= '<td>';
			
				$html .= "<textarea rows='1' template-id=".'"'.TF_NEW_TEMPLATE_CALL.'"'." originalValue=''></textarea>";
			
				$html .= '</td>';
			}
		}
		
		//add status column
		$html .= '<td>';
		
		$okDisplay = $readProtectedDisplay = $writeProtectedDisplay = 
			$addedDisplay = $notExistsDisplay = 'none';
		
		$addedDisplay = '';
		
		$html .= TFTabularFormRowData::getStatusColumnHTML($okDisplay, $readProtectedDisplay, $writeProtectedDisplay, 
			$addedDisplay, $notExistsDisplay);
			
		$html .= '</td>';
		
		return $html;
	}
	
	/*
	 * Get HTML for tabular form rows
	 */
	private function addRowHTML($rowData){
		$html = '';
		
		$html .= $rowData->getHTML($this->annotationPrintRequests, $this->templateParameterPrintRequests
			, $this->enableInstanceDelete, $this->enableInstanceAdd);
		
		return $html;
	}
	
	
	/*
	 * get the footer HTML for the tabular form
	 */
	private function addTabularFormFooterHTML($tabularFormId){
		$html = '';
		
		$html .= '<tr class="tabf_table_footer">';
		
		$colSpan = 2;
		$colSpan += count($this->annotationPrintRequests);
		foreach($this->templateParameterPrintRequests as $template => $parameters){
			$colSpan += count($parameters);
		}
		
		//todo:LANGUAGE
		$html .= '<td colspan="'.$colSpan.'">';
		
		if ( $this->hasFurtherResults){
			$link = $this->queryResult->getQueryLink();
			
			$link->setParameter('tabularform', 'format');
			
			//add template parameter printrequests
			foreach($this->queryParams as $param => $label){
				if($param[0] == '#'){
					$link->setParameter($label, $param);
				}
			}
			
			$html .= '<span class="tabf_further_results" width="100%">'.$link->getText( $this->outputMode, $this->linker).'</span>';
		}
		
		//todo:LANGUAGE
		
		if($this->enableInstanceAdd){
			$html .= '<input class="tabf_add_button" type="button" value="Add instance" onclick="tf.addInstance('."'".$tabularFormId."'".')"/>';
		}
		
		$html .= '<input type="button" value="Refresh" onclick="tf.refreshForm('."'".$tabularFormId."'".')"/>';
		$html .= '<input class="tabf_save_button" style="display:none" type="button" value="Save" onclick="tf.saveFormData(event,'."'".$tabularFormId."'".')"/>';
		$html .= '</td>';
		
		$html .= '</tr>';
		
		return $html;
	}
	
		/*
	 * Initializes the form row data for a given query result row
	 */
	private function initializeFormRowData($row, $subjectFromFirstColumn){
		//get instance behind query result row
		if($subjectFromFirstColumn){
			$title = $row[0]->getNextObject()->getLongText($this->outputMode, null);
			unset( $row[0]);
		} else {
			$title = $row[0]->getResultSubject()->getLongText();
		}
		
		$formRowData = new TFTabularFormRowData($title);
		
		//process row fields
		foreach ( $row as $key => $field ) {
			
			$noResults = true;
			
			while ( ( $fieldValue = $field->getNextObject() ) !== false ) {
				if(( $fieldValue->getTypeID() == '_wpg') || ($fieldValue->getTypeID() == '__sin' )){
					$rawFieldValue = $fieldValue->getLongText( $this->outputMode, null);
					$renderedFieldValue = $fieldValue->getLongText( $this->outputMode, $this->linker);	
				} else {
					$rawFieldValue = $fieldValue->getShortText( $this->outputMode, null);
					$renderedFieldValue = $fieldValue->getShortText( $this->outputMode, $this->linker);
				}
				
				$hash = $fieldValue->getHash();
				$typeId = $fieldValue->getTypeID();
				
				$formRowData->addAnnotation(
					$this->annotationPrintRequests[$key]['title'], $rawFieldValue, $renderedFieldValue,$hash, $typeId );
					
				$noResults = false;
			}
				
			//add annotation also if no results have been found	
			if($noResults){
				$formRowData->addAnnotation($this->annotationPrintRequests[$key]['title'], null, null, null, null);
			}
		}
			
		$formRowData->detectWritableAnnotations();
			
		$formRowData->readTemplateParameters($this->templateParameterPrintRequests);

		$this->formRowsData[] = $formRowData;	
	}
	
	
	/*
	 * Extract the names of the requested annotations
	 */
	private function initializeAnnotationPrintRequests(){
		
		if(array_key_exists('mainlabel', $this->queryParams) && $this->queryParams['mainlabel'] != '-'){
			$this->subjectColumnLabel = $this->queryParams['mainlabel'];
		}
		
		$count = -1;
		foreach($this->queryResult->getPrintRequests() as $printRequest){
			
			$count += 1;
			if($count == 0){
				if((is_null($printRequest->getData()) && $printRequest->getHash() != '0:Category::')
						|| $this->isSPARQLQuery){	
					
					$this->getSubjectFromFirstPrintRequest = true;
					
					if($this->isSPARQLQuery){
						$this->subjectColumnLabel = $printRequest->getText($this->outputMode, $this->linker);
					}
				}	
			}
			
			if(is_null($printRequest->getData())){
				
				//deal with category print requests
				if($printRequest->getHash() == '0:Category::'){
					$this->annotationPrintRequests[$count] = 
						array('title' => '__Category__', 
						'label' => $printRequest->getText($this->outputMode, $this->linker),
						'rawlabel' => $printRequest->getText($this->outputMode, null));
				} 
				
				continue;
			}
			
			$this->annotationPrintRequests[$count] = 
				array('title' => $printRequest->getData()->getText(), 
				'label' => $printRequest->getText($this->outputMode, $this->linker),
				'rawlabel' => $printRequest->getText($this->outputMode, null));
		}
	}
	
	
	/*
	 * Initialize template parameter print requests from query parameters
	 * 
	 * Make sure, that the order of the parameters does not get lost
	 */
	private function initializeTemplateParameterPrintRequests(){
		foreach($this->queryParams as $param => $label){
			if($param[0] == '#'){
				$param = substr($param, 1);
				$templateParameter = $param;
				
				$param = explode('#', $param, 2);
				if(count($param) == 1){
					$this->templateParameterPrintRequests[$param[0]][''] = $templateParameter;
				} else {
					if(!array_key_exists($param[0], $this->templateParameterPrintRequests)){
						$this->templateParameterPrintRequests[$param[0]] = array();
					}
					
					$this->templateParameterPrintRequests[$param[0]][$param[1]] = $templateParameter;
					
					//deal with labels
					if(strlen($label) > 0){
						if(!array_key_exists($param[0], $this->templateParameterPrintRequestLabels)){
							$this->templateParameterPrintRequestLabels[$param[0]] = array();
						}
						
						$this->templateParameterPrintRequestLabels[$param[0]][$param[1]] = $label;
					}
				}
			}
		}
	}
	
	
	/*
	 * Make sure that all rows have columns for all template parameters
	 * that have been found in any of the instances
	 */
	private function mergeTemplateParametersRowData(){
		foreach($this->formRowsData as $row){
			$this->templateParameterPrintRequests = 
				$row->getMissingTemplateParameters($this->templateParameterPrintRequests);
		}
		
		foreach($this->templateParameterPrintRequests as $template => $params){
			$tmpParams = array();
			foreach($params as $param => $subParams){
				if($param == ''){
					if(is_array($subParams)){ //otherwise no params for this template have been found
						ksort($subParams);
						foreach($subParams as $subParam => $dontCare){
							$tmpParams[$subParam] = true;
						}
					}
				} else {
					$tmpParams[$param] = true;
				}
			}
			$this->templateParameterPrintRequests[$template] = $tmpParams;
		}
		
		foreach($this->formRowsData as $row){
			$row->addMissingTemplateParameters($this->templateParameterPrintRequests);
		}
	}

}


/*
 * This class represents a row in the tabular form
 */
class TFTabularFormRowData {
	
	private $title;
	private $dataAPIAccess = null;
	private $revisionId = 0;
	private $annotations = array();
	public $templateParameters = array();
	
	
	/*
	 * Constructor, that sets the title of the instance, which is
	 * represented by this row
	 */
	public function __construct($title){
		$this->title = Title::newFromText($title);
		$this->dataAPIAccess = TFDataAPIAccess::getInstance($this->title);
		$this->revisionId = $this->dataAPIAccess->getRevisionId();
	}
	
	/*
	 * Add am annotation, which is shown in this row
	 */
	public function addAnnotation($name, $value, $renderedValue, $hash, $typeId){
		$this->annotations[] = new TFAnnotationData($name, $value, $renderedValue, $hash, $typeId);
	}
	
	/*
	 * Detect which of the annotations, that are shown in this row,
	 * are writable and which are read-only.
	 */
	public function detectWritableAnnotations(){
		$collection = new TFAnnotationDataCollection();
		$collection->addAnnotations($this->annotations);
		$this->annotations = $this->dataAPIAccess->getWritableAnnotations($collection);
		
		//All values are read-only if article does not exist and add is not enabled
		if((!($this->title instanceof Title && $this->title->exists()) && !$enableInstanceAdd)
				|| $this->dataAPIAccess->isReadProtected || $this->dataAPIAccess->isWriteProtected){
			foreach($this->annotations as $key => $annotations){
				foreach($annotations as $k => $annotation){
					$this->annotations[$key][$k]->isWritable = false;
				}
			}
		}
	}
	
	/*
	 * Populate the template param print requests with values
	 */
	public function readTemplateParameters($templateParams){
		$collection = new TFTemplateParameterCollection();
		foreach($templateParams as $template => $params){
			foreach($params as $param){
				$collection->addTemplateParameter(new TFTemplateParameter($param));		
			}
		}
		
		$this->templateParameters = $this->dataAPIAccess->readTemplateParameters($collection);
	}
	
	
	/*
	 * Called for each row in order to collect all template parameters
	 * that have been found in all instances
	 */
	public function getMissingTemplateParameters($parameters){
		foreach($this->templateParameters as $template => $paramNames){
			if(!array_key_exists($template, $parameters)){
				$parameters[$template] = array();
			}
			
			foreach($paramNames as $name => $dC){
				if(array_key_exists('', $parameters[$template]) && !array_key_exists($name, $parameters[$template])){
					if(!is_array($parameters[$template][''])){
						$parameters[$template][''] = array();
					}
					$parameters[$template][''][$name] = true;
				} else {
					$parameters[$template][$name] = true;
				} 
			}
		}
		
		return $parameters;
	}
	
	
	/*
	 * Template parameters that have been introduced by other rows
	 */
	public function addMissingTemplateParameters($parameters){
		foreach($parameters as $template => $parameterNames){
			if(!array_key_exists($template, $this->templateParameters)){
				$this->templateParameters[$template] = array();
			}
			
			foreach($parameterNames As $name => $dC){
				if(!array_key_exists($name, $this->templateParameters[$template])){
					
					//check if this template already is used in the article
					//and use one of the other parameters current values
					//in order to get template Ids
					if(count($this->templateParameters[$template]) > 0){
						$idx = array_keys($this->templateParameters[$template]);
						$currentValues = $this->templateParameters[$template][$idx[0]]->currentValues;
						foreach($currentValues as $templateId => $value){
							$currentValues[$templateId] = ''; 
						}
					} else {
						$currentValues = array();
					}
					
					$this->templateParameters[$template][$name] = new TFTemplateParameter($template.'.'.$name, $currentValues); 
				}
			}
		}
		
		//Template parameters are read-only if article does not exist and add is not enabled
		if((!($this->title instanceof Title && $this->title->exists()) && !$enableInstanceAdd)
				|| $this->dataAPIAccess->isReadProtected || $this->dataAPIAccess->isWriteProtected){
			foreach($this->templateParameters as $key => $parameters){
				foreach($parameters as $k => $parameter){
					$this->templateParameters[$key][$k]->isWritable = false;
				}
			}
		}
	}
	
	/*
	 * Returns tabular form HTML for this row
	 */
	public function getHTML($annotationPrintRequests, $parameterPrintRequests, 
			$enableInstanceDelete, $enableInstanceAdd){
		
		$html = '';
		
		//Add table row tag
		$class = 'tabf_table_row';
		$additionalAttributes = "";
		if($this->title instanceof Title && $this->title->exists()){
			if($this->dataAPIAccess->isReadProtected){
				$class .= ' tabf_read_protected_row';
			} else if($this->dataAPIAccess->isWriteProtected){
				$class .= ' tabf_write_protected_row';
			}
		} else {
			if($enableInstanceAdd){
				$class .= ' tabf_new_row';
				$additionalAttributes .= ' isNew="true" ';
			} else {
				$class = ' tabf_non_existing_row';
			}
		}
		
		$html .= '<tr class="'.$class.'" '.$additionalAttributes.' >';
		
		//Add subject
		if(($this->title instanceof Title && $this->title->exists()) || !$enableInstanceAdd){
			$linker = new Linker();
			$html .= '<td class="tabf_table_cell" revision-id="'.$this->revisionId.
				'" article-name="'.$this->title->getFullText().'">';
			if($this->title instanceof Title){
				$html .= $linker->makeLinkObj($this->title);
			}
			if($enableInstanceDelete && ($this->title instanceof Title && $this->title->exists())){
				$html .= '<input class="tabf-delete-button" type="button" value="Delete" style=" display: none" onclick="tf.deleteInstance(event)"/>';
			}
		} else {
			$html .= '<td class="tabf_table_cell" revision-id="-1">';
			$html .= '<textarea class="tabf_valid_instance_name" rows="1">';
			if($this->title instanceof Title){ 
				$html .= $this->title->getFullText();
			}
			$html .= '</textarea>';
			$html .= '<input class="tabf-delete-button" type="button" value="Delete" style="display: none" onclick="tf.deleteInstance(event)"/>';
		}
		$html .= '</td>';

		//Add cells for annotations
		foreach($annotationPrintRequests as $annotation){
			$html .= '<td class="tabf_table_cell">';
			
			$autocomplete = '';
			if(!is_null($annotation['autocomplete'])){
				$autocompletion = 'class="wickEnabled" constraints="';
				$autocompletion .= $annotation['autocomplete'];
				$autocompletion .= '"';
			}
			
			$annotations = $this->annotations[$annotation['title']];
			$moreThanOne = false;
			foreach($annotations as $annotation){
				if($annotation->isWritable){
					$moreThanOne == true ? $style = 'style="border-top: 1px inset grey;"' : $style ='';
					$html .= "<textarea ".$autocompletion." ". $style ." rows='2' annotation-hash='".$annotation->hash
						."' annotation-type-id='".$annotation->typeId."'>".$annotation->currentValue."</textarea>";
				} else {
					$html .= '<div style="height: 3em; width: 100%">'.$annotation->renderedValue.'</div>';
				}
				$moreThanOne = true;
			}
			
			$html .= '</td>';
		}
		
		//Add template cells
		foreach($parameterPrintRequests as $template => $params){
			foreach($params as $param => $dC){
				$html .= '<td class="tabf_table_cell">';
				
				ksort($this->templateParameters[$template][$param]->currentValues);
				if(count($this->templateParameters[$template][$param]->currentValues) == 0){
					if($this->templateParameters[$template][$param]->isWritable){
						$html .= "<textarea rows='1' template-id=".'"'.TF_NEW_TEMPLATE_CALL.'"'."></textarea>";
					} else {
						$html .= '<div style="height: 3em; width: 100%"></div>';
					}
				} else {
					foreach($this->templateParameters[$template][$param]->currentValues as $templateId => $currentValue){
						if($this->templateParameters[$template][$param]->isWritable){
							$html .= "<textarea rows='1' template-id=".'"'.$templateId.'"'."'>".$currentValue."</textarea>";
						} else {
							$html .= '<div style="height: 3em; width: 100%">'.$currentValue.'</div>';
						}
					}
				}
			
				$html .= '</td>';
			}
		}
		
		//add status column
		$html .= '<td class="tabf_status_cell">';
		
		$okDisplay = $readProtectedDisplay = $writeProtectedDisplay = 
			$addedDisplay = $notExistsDisplay = 'none';
		
		if($this->title instanceof Title && $this->title->exists()){
			if($this->dataAPIAccess->isReadProtected == true){
				$readProtectedDisplay = '';
			} else if($this->dataAPIAccess->isWriteProtected == true){
				$writeProtectedDisplay = '';
			} else {
				// I think it is not necessary to have an ICON for this staus
				//$okDisplay = '';
			}
		} else {
			if($enableInstanceAdd){
				$addedDisplay = '';
			} else {
				$notExistsDisplay = '';
			}
		}
		
		$html .= self::getStatusColumnHTML($okDisplay, $readProtectedDisplay, $writeProtectedDisplay, 
			$addedDisplay, $notExistsDisplay);
		
		$html .= '</td>';
		
		$html .= '</tr>';
		
		return $html;
	}
	
	/* 
	 * Returns the status icons for the last column
	 */static 
	public function getStatusColumnHTML($okDisplay, $readProtectedDisplay, $writeProtectedDisplay, 
			$addedDisplay, $notExistsDisplay){

		global $smwgHaloScriptPath;
				
		$html = '';
		
		//todo: LANGUAGE
		$title = 'Not yet modified.';
		$html .= '<img class="tabf_ok_status" title="'.$title.'" style="display: '.$okDisplay.'" src="'.
			$smwgHaloScriptPath.'/skins/TabularForms/Unmodified.png"></img>';
		
		$title = 'Instance does not exist and will be created.';
		$html .= '<img class="tabf_added_status" title="'.$title.'" style="display: '.$addedDisplay.'" src="'
			.$smwgHaloScriptPath.'/skins/TabularForms/Added.png"></img>';
		
		$title = 'Instance does not exist.';
		$html .= '<img class="tabf_exists_not_status" title="'.$title.'" style="display: '.$notExistsDisplay.'" src="'
			.$smwgHaloScriptPath.'/skins/TabularForms/Warning.png"></img>';
		
		$title = 'Instance is read protected.';
		$html .= '<img class="tabf_exists_not_status" title="'.$title.'" style="display: '.$readProtectedDisplay.'" src="'
			.$smwgHaloScriptPath.'/skins/TabularForms/Warning.png"></img>';

		$title = 'Instance is write protected.';
		$html .= '<img class="tabf_exists_not_status" title="'.$title.'" style="display: '.$writeProtectedDisplay.'" src="'
			.$smwgHaloScriptPath.'/skins/TabularForms/Warning.png"></img>';
			
		$title = "Instance will be deleted.";	
		$html .= '<img class="tabf_deleted_status" title="'.$title.'" style="display: none" src="'
			.$smwgHaloScriptPath.'/skins/TabularForms/Deleted.png"></img>';
		
		$title = 'Instance has been modified.';
		$html .= '<img class="tabf_modified_status" title="'.$title.'" style="display: none" src="'
			.$smwgHaloScriptPath.'/skins/TabularForms/Modified.png"></img>';
		
		$title = 'Instance has been saved successfully.';
		$html .= '<img class="tabf_saved_status" title="'.$title.'" style="display: none" src="'
			.$smwgHaloScriptPath.'/skins/TabularForms/Saved.png"></img>';
		
		$title = '';
		$html .= '<img class="tabf_error_status" title="'.$title.'" style="display: none" src="'
			.$smwgHaloScriptPath.'/skins/TabularForms/Error.png"></img>';
		
		$title = 'Updating instance.';
		$html .= '<img class="tabf_pending_status" title="'.$title.'" style="display: none" src="'
			.$smwgHaloScriptPath.'/skins/TabularForms/Pending.gif"></img>';
	
		return $html;	
	}
	
	
}




