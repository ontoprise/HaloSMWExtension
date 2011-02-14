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
		return array();
	}

}


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
	
	public function __construct($queryResult, $queryParams, $linker, $hasFurtherResults){
		$this->queryResult = $queryResult;
		$this->outputMode = SMW_OUTPUT_HTML;
		$this->queryParams = $queryParams;
		$this->linker = $linker;
		$this->hasFurtherResults = $hasFurtherResults;
		
		$this->initializeAnnotationPrintRequests();
		
		$link = $this->queryResult->getQueryLink();
		
		//Lecho('<pre>'.print_r($link, true).'</pre>');
		
		$this->initializeTemplateParameterPrintRequests();
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
		//todo:ICON pending
		global $smwgHaloScriptPath;
		$html .= '<div class="tabf_loader">';
		$html .= 'Tabular form is loading'.' ';
		$html .= '<img title="Loading Tabular Form" src="'.$smwgHaloScriptPath.'/skins/TabularForms/Pending.gif"></img>';
		$html .= '</div>';
		
		//display serialized query
		$html .= '<span class="tabf_query_serialization" style="display: none">';
		
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
		
		$html .= $this->getTabularFormHTML(1);
		
		$html .= '</div>';
		
		$html .= '</div>';
		
		return $html;
	}
	
	/*
	 * Returns the HTML for this tabular form
	 */
	public function getTabularFormHTML($tabularFormId){
		
		// process each query result row
		while ( $row = $this->queryResult->getNext() ) {
			$this->initializeFormRowData($row, $this->getSubjectFromFirstPrintRequest);
		}
		$this->mergeTemplateParametersRowData();
		
		
		$html = '';
		
		$html .= '<table class="smwtable" width="100%">';
		
		$html .= $this->addTableHeaderHTML();
		
		foreach($this->formRowsData as $rowData){
			$html .= $this->addRowHTML($rowData);
		}
		
		$html .= $this->addTabularFormFooterHTML($tabularFormId);
		
		$html .= '</table>';
		
		return $html;
	}
	
	
	/*
	 * Get column headings html
	 */
	private function addTableHeaderHTML(){
		$html ='<tr>';
		
		//add subject column
		$html .= '<th>'.$this->subjectColumnLabel.'</th>';
		
		//add annotation columns
		foreach($this->annotationPrintRequests as $annotation){
			$html .= '<th class="tabf_column_header" field-address="'.$annotation['title'].'" is-template="false">'
				.$annotation['label'].'</th>';
		}
		
		//add template parameter columns
		foreach($this->templateParameterPrintRequests as $template => $params){
			foreach($params as $param => $dC){
				$html .= '<th class="tabf_column_header" field-address="'.$template.'#'.$param.'" is-template="true">';
				if(array_key_exists($template, $this->templateParameterPrintRequestLabels) 
						&& array_key_exists($param, $this->templateParameterPrintRequestLabels[$template])){
					$html .= $this->templateParameterPrintRequestLabels[$template][$param];	
				} else {
					$html .= $template.'#'.$param;
				}
				$html .= '</th>';
			}
		}
		
		//add status column
		$html .= '<th></th>';
		
		$html.= '</tr>';
		return $html;
	}
	
	/*
	 * Get HTML for tabular form rows
	 */
	private function addRowHTML($rowData){
		$html = '';
		
		$html .= '<tr class="tabf_table_row">';
		
		$html .= $rowData->getHTML($this->annotationPrintRequests, $this->templateParameterPrintRequests);
		
		$html .= '</tr>';
		
		return $html;
	}
	
	
	/*
	 * get the footer HTML for the tabular form
	 */
	private function addTabularFormFooterHTML($tabularFormId){
		$html = '';
		
		$html .= '<tr class="smwfooter">';
		
		//todo: deal with further results
		
		$colSpan = 2;
		$colSpan += count($this->annotationPrintRequests);
		foreach($this->templateParameterPrintRequests as $template => $parameters){
			$colSpan += count($parameters);
		}
		
		//todo:language file
		$html .= '<td colspan="'.$colSpan.'">';
		
		if ( $this->hasFurtherResults){
			$link = $this->queryResult->getQueryLink();
			
			$link->setParameter('tabularform', 'format');
			$link->setParameter('1', 'limit');
			
			//add template parameter printrequests
			foreach($this->queryParams as $param => $label){
				if($param[0] == '#'){
					$link->setParameter($label, $param);
				}
			}
			
			$html .= '<span class="tabf_further_results">'.$link->getText( $this->outputMode, $this->linker).'</span>';
		}
		
		
		$html .= '<input type="button" value="Refresh" onclick="tf.refreshForm('."'".$tabularFormId."'".')"/>';
		$html .= '<input type="button" value="Save" onclick="tf.saveFormData(event,'."'".$tabularFormId."'".')"/>';
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
					
				$formRowData->addAnnotation(
					$this->annotationPrintRequests[$key]['title'], $rawFieldValue, $renderedFieldValue);
					
				$noResults = false;
			}
				
			//add annotation also if no results have been found	
			if($noResults){
				$formRowData->addAnnotation($this->annotationPrintRequests[$key]['title'], '', '');
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
		//todo: deal with category print requests
		
		if(array_key_exists('mainlabel', $this->queryParams) && $this->queryParams['mainlabel'] != '-'){
			$this->subjectColumnLabel = $this->queryParams['mainlabel'];
		}
		
		$count = -1;
		foreach($this->queryResult->getPrintRequests() as $printRequest){
			
			$count += 1;
			if($count == 0){
				if(is_null($printRequest->getData()) && $printRequest->getHash() != '0:Category::'){	
					$this->getSubjectFromFirstPrintRequest = true; 
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
			
			
			//todo: deal with labels for print requests
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
		//echo('<pre>START'.print_r($this->templateParameterPrintRequests, true).'</pre>');
		
		foreach($this->formRowsData as $row){
			$this->templateParameterPrintRequests = 
				$row->getMissingTemplateParameters($this->templateParameterPrintRequests);
		}
		
		//echo('<pre>MERGED'.print_r($this->templateParameterPrintRequests, true).'</pre>');
		foreach($this->templateParameterPrintRequests as $template => $params){
			$tmpParams = array();
			foreach($params as $param => $subParams){
				if($param == ''){
					ksort($subParams);
					foreach($subParams as $subParam => $dontCare){
						$tmpParams[$subParam] = true;
					}
				} else {
					$tmpParams[$param] = true;
				}
			}
			$this->templateParameterPrintRequests[$template] = $tmpParams;
		}
		
		//echo('<pre>FINAL'.print_r($this->templateParameterPrintRequests, true).'</pre>');
		
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
	public function addAnnotation($name, $value, $renderedValue){
		$this->annotations[] = new TFAnnotationData($name, $value, $renderedValue);
	}
	
	/*
	 * Detect which of the annotations, that are shown in this row,
	 * are writable and which are read-only.
	 */
	public function detectWritableAnnotations(){
		//todo: class not available if query does not contain annotations
		
		$collection = new TFAnnotationDataCollection();
		$collection->addAnnotations($this->annotations);
		$this->annotations = $this->dataAPIAccess->getWritableAnnotations($collection);
		
		//echo('<pre>'.print_r($this->annotations, true).'</pre>');
		
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
		
		//echo('<pre>'.print_r($this->templateParameters, true).'</pre>');
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
					//todo: create empty value instead of no value?
					$this->templateParameters[$template][$name] = new TFTemplateParameter($template.'.'.$name); 
				}
			}
		}
	}
	
	/*
	 * Returns tabular form HTML for this row
	 */
	public function getHTML($annotationPrintRequests, $parameterPrintRequests){
		
		$html = '';
		
		//Add subject
		$linker = new Linker();
		$html .= '<td class="tabf_table_cell" revision-id="'.$this->revisionId.'" article-name="'.$this->title->getFullText().'" >'.$linker->makeLinkObj($this->title).'</td>';

		//Add cells for annotations
		foreach($annotationPrintRequests as $annotation){
			$html .= '<td class="tabf_table_cell">';
			
			$annotations = $this->annotations[$annotation['title']];
			foreach($annotations as $annotation){
				if($annotation->isWritable){
					$html .= "<textarea rows='1'>".$annotation->currentValue."</textarea>";
				} else {
				$html .= '<div style="height: 3em; width: 100%">'.$annotation->renderedValue.'</div>';
				}
				$first = false;
			}
			
			$html .= '</td>';
		}
		
		//Add template cells
		foreach($parameterPrintRequests as $template => $params){
			foreach($params as $param => $dC){
				$html .= '<td class="tabf_table_cell">';
				
				if(count($this->templateParameters[$template][$param]->currentValues) == 0){
					$html .= "<textarea rows='1' ></textarea>";
				} else {
					foreach($this->templateParameters[$template][$param]->currentValues as $templateId => $currentValue){
						$html .= "<textarea rows='1' template-id=".'"'.$templateId.'"'."'>".$currentValue."</textarea>";
					}
				}
			
				$html .= '</td>';
			}
		}
		
		//add status column
		//Todo:Language
		global $smwgHaloScriptPath;
		$html .= '<td>';
		$html .= '<img class="tabf_ok_status" title="Not yet modified" src="'.$smwgHaloScriptPath.'/skins/TabularForms/Unmodified.png"></img>';
		$html .= '<img class="tabf_modified_status" title="Modified" style="display: none" src="'.$smwgHaloScriptPath.'/skins/TabularForms/Modified.png"></img>';
		$html .= '<img class="tabf_saved_status" title="Saved" style="display: none" src="'.$smwgHaloScriptPath.'/skins/TabularForms/Saved.png"></img>';
		$html .= '<img class="tabf_error_status" title="An error occured" style="display: none" src="'.$smwgHaloScriptPath.'/skins/TabularForms/Error.png"></img>';
		$html .= '<img class="tabf_pending_status" title="Updating" style="display: none" src="'.$smwgHaloScriptPath.'/skins/TabularForms/Pending.gif"></img>';
		$html .= '</td>';
		
		return $html;
	}
	
	
}




