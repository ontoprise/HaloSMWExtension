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

	public function getMimeType( $res ) {
		//This is a trick to force SMW to also show TF if no results exist

		if($res->getCount() == 0 ){
			return true;
		} else {
			return false;
		}
	}


	/*
	 * Also called by Halo Initialize
	 */
	public function getScripts() {
		global $smwgHaloScriptPath;
		$scripts=array();
		global $smwgHaloStyleVersion;
		$scripts[] = '<script type="text/javascript" src="' . $smwgHaloScriptPath .
				'/scripts/TabularForms/tabularforms.js'.$smwgHaloStyleVersion.'"></script>' . "\n";
		return $scripts;
	}

	/*
	 * Also called by Halo Initialize
	 */
	function getStylesheets() {
		global $smwgHaloScriptPath;
		global $smwgHaloStyleVersion;
		$css = array();
		$css[] = array(
            'rel' => 'stylesheet',
            'type' => 'text/css',
            'media' => "screen, projection",
            'href' => $smwgHaloScriptPath . '/skins/TabularForms/tabularforms.css'.$smwgHaloStyleVersion
            );
            return $css;
	}
	
	/**
	 * Reads the parameters and gets the query printers output.
	 * 
	 * @param SMWQueryResult $results
	 * @param array $params
	 * @param $outputmode
	 * 
	 * @return array
	 */
	public final function getResult( SMWQueryResult $results, array $params, $outputmode ) {
		$this->handleParameters( $params, $outputmode );
		$result = $this->getResultText( $results, SMW_OUTPUT_HTML );
		return $result;
	}


	/*
	 * Returns the HTML output of this query printer
	 */
	protected function getResultText(SMWQueryResult $queryResult, $outputMode ) {
		$this->isHTML = true;
		
		//echo('<pre>'.print_r($queryResult, true).'</pre>');

		$tabularFormData = new TFTabularFormData($queryResult, $this->m_params, $this->mLinker,
			$this->linkFurtherResults( $queryResult));
		$this->mShowErrors = false;

		if(array_key_exists(TF_SHOW_AJAX_LOADER_HTML_PARAM, $this->m_params)
			&& $this->m_params[TF_SHOW_AJAX_LOADER_HTML_PARAM] == 'false'){

			//the tabular form HTML must be displayed
			$html = $tabularFormData->getTabularFormHTML($this->m_params[TF_TABULAR_FORM_ID_PARAM]);
			
			return $html;
		} else {
			//the Ajax loader HTML must be displayed
			$html = $tabularFormData->getAjaxLoaderHTML();

			//Add script
			SMWOutputs::requireResource('ext.tabularforms.main');
			
			return array(
					$html,
					'noparse' => true, 
					'isHTML' => true
				);
		}
	}

	public function getParameters() {
		$params = parent::getParameters();
		
		$params['enable filtering'] = new Parameter('enable filtering'); 
		$params['enable filtering']->setMessage(wfMsg( 'smw_tf_paramdesc_filtering' ));
		$params['enable filtering']->addCriteria( new CriterionInArray( 'false', 'true' ) );
		$params['enable filtering']->setDefault('false');
		
		$params['expert mode'] = new Parameter('expert mode'); 
		$params['expert mode']->setMessage(wfMsg( 'smw_tf_paramdesc_expertmode' ));
		$params['expert mode']->addCriteria( new CriterionInArray( 'false', 'true' ) );
		$params['expert mode']->setDefault('false');
		
		$params['enable add'] = new Parameter('enable add'); 
		$params['enable add']->setMessage(wfMsg( 'smw_tf_paramdesc_add' ));
		$params['enable add']->addCriteria( new CriterionInArray( 'false', 'true' ) );
		$params['enable add']->setDefault('false');
		
		$params['enable delete'] = new Parameter('enable delete'); 
		$params['enable delete']->setMessage(wfMsg( 'smw_tf_paramdesc_delete' ));
		$params['enable delete']->addCriteria( new CriterionInArray( 'false', 'true' ) );
		$params['enable delete']->setDefault('false');
		
		$params['write protected annotations'] = new Parameter('write protected annotations'); 
		$params['write protected annotations']->setMessage(wfMsg('tabf_parameter_write_protected_desc'));
		$params['write protected annotations']->setDefault('');
		
		$params['instance name preload value'] = new Parameter('instance name preload value'); 
		$params['instance name preload value']->setMessage(wfMsg('tabf_parameter_instance_preload_desc'));
		$params['instance name preload value']->setDefault('');
		
		return $params;
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
	private $templateParameterPrintRequestPreload = array();
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
	private $annotationPreloadValues = array();
	private $instanceNamePreloadValue = '';
	private $writeProtectedAnnotations = array();
	private $annotationQueryConditions = array();
	private $addInstanceBlockers = array();
	private $tabularFormId;

	public function __construct($queryResult, $queryParams, $linker, $hasFurtherResults){
		$this->queryResult = $queryResult;
		$this->outputMode = SMW_OUTPUT_HTML;
		$this->queryParams = $queryParams;
		$this->linker = $linker;
		$this->hasFurtherResults = $hasFurtherResults;
		
		if($this->queryResult instanceof SMWHaloQueryResult &&
		$this->queryResult->getQuery() instanceof SMWSPARQLQuery && !$this->queryResult->getQuery()->fromASK){
			$this->isSPARQLQuery = true;
		}

		$this->initializeAnnotationPrintRequests();

		$link = $this->queryResult->getQueryLink();

		$this->initializeTemplateParameterPrintRequests();

		if(array_key_exists('enable add', $this->queryParams)
				&& $this->queryParams['enable add'] == 'true'){
			$this->enableInstanceAdd = true;
		}

		if(array_key_exists('enable delete', $this->queryParams)
				&& $this->queryParams['enable delete'] == 'true'){
			$this->enableInstanceDelete = true;
		}

		$this->initializeWriteProtectedAnnotations();
	}


	/*
	 * Returns the HTML of the Ajax loader
	 */
	public function getAjaxLoaderHTML(){
		global $tfgTabularFormCount;
		$tfgTabularFormCount += 1;

		$html = '';

		$html .= '<div id="tabf_container_'.$tfgTabularFormCount.'" class="tabf_container" ';
		if(array_key_exists('expert mode', $this->queryParams) && $this->queryParams['expert mode'] == 'true'){
			$html .= ' expertMode="true" ';
		}
		$html .= '>';

		global $smwgHaloScriptPath;
		$html .= '<div class="tabf_loader">';
		$html .= wfMsg('tabf_load_msg');
		$html .= '<img title="Loading Tabular Form" src="'.$smwgHaloScriptPath.'/skins/TabularForms/Pending.gif"></img>';
		$html .= '</div>';

		//display serialized query
		$html .= '<span class="tabf_query_serialization" style="display: none" isSPARQL="'.$this->isSPARQLQuery.'">';

		$query = $this->getQuerySerialization();

		$html .= json_encode($query);
		$html .= '</span>';

		$html .= '<div class="tabf_table_container" width="100%" style="display: none"></div>';
		
		$html .= '<div class="tabf_table_container_cache" style="display: none">fo</div>';

		$html .= '</div>';

		return $html;
	}


	/*
	 * Returns the query as an array
	 */
	private function getQuerySerialization(){
		
		foreach($this->queryParams as $param => $value){
			if(is_array($value)){
				$value = implode(',', $value);
			}
			if(!($param == 'mainlabel' && $value == '-')){
				if(strlen($value) > 0){
					$param .= '='.$value;
					$query[] = $param;
				} else if($param[0] == '#'){
					$query[] = $param;
				}
			}
		}

		$query[] = $this->queryResult->getQueryString();

		foreach($this->annotationPrintRequests as $annotation){
			if($annotation['title'] == TF_CATEGORY_KEYWORD){
				//replace internal category id
				$annotation['title'] = 'Category';
			}

			if(strlen($annotation['rawlabel']) > 0){
				$query[] = '?'.$annotation['title'].'='.$annotation['rawlabel'];
			} else {
				$query[] = '?'.$annotation['title'];
			}
		}

		return $query;
	}


	/*
	 * Returns the HTML for this tabular form
	 */
	public function getTabularFormHTML($tabularFormId){

		$this->tabularFormId = $tabularFormId;
		
		//this must be done since we use the first print request as subject column
		//in the SPARQL case
		if($this->isSPARQLQuery){
			unset($this->annotationPrintRequests[0]);
		}

		$this->initializeAnnotationCharacteristics();
		
		list($this->annotationPreloadValues, $this->instanceNamePreloadValue) =
			TFQueryAnalyser::getPreloadValues($this->getQuerySerialization(), $this->isSPARQLQuery);

		$this->annotationQueryConditions =
			TFQueryAnalyser::getQueryConditions($this->getQuerySerialization(), $this->isSPARQLQuery);
			
		$this->checkEnableAddInstance();

		// process each query result row
		while ( $row = $this->queryResult->getNext() ) {
			$this->initializeFormRowData($row, $this->getSubjectFromFirstPrintRequest);
		}
		$this->mergeTemplateParametersRowData();


		$html = '';

		$html .= '<table class="tabf_in_view_mode" border="0" cellspacing="0" cellpadding="0" width="100% "';
		$offset = 0;
		if(array_key_exists('offset', $this->queryParams)){
			$offset = $this->queryParams['offset'];
		}
		$html .= ' offset="'.$offset.'" ';
		
		global $smwgQDefaultLimit;
		$limit = $smwgQDefaultLimit;
		if(array_key_exists('limit', $this->queryParams)){
			$limit = $this->queryParams['limit'];
		}
		$html .= ' limit="'.$limit.'" ';
		$html .= '>';

		$html .= $this->addTableHeaderHTML();

		foreach($this->formRowsData as $rowData){
			$html .= $this->addRowHTML($rowData);
		}

		$html .= $this->addTabularFormFooterHTML($tabularFormId);

		$html .= $this->addTabularFormAddRowTemplateHTML($tabularFormId);

		$html .= '</table>';

		$html .= '<span class="tabf_update_warning" style="display: none">';
		$html .= wfMsg( 'tabf_update_warning' );
		$html .= '</span>';

		$html .= $this->getNotificationSystemHTML();

		$html .= '<textarea class="tabf_rowindex_comparator" style="visibility: hidden; height: 1em"></textarea><br/>';

		return $html;
	}
	
	
	/*
	 * Detect which annotation print requests are write protected and which are not
	 */
	private function initializeWriteProtectedAnnotations(){

		//first deal with the ones set by the query designer
		if(array_key_exists('write protected annotations', $this->queryParams)){
			$wPAs = str_replace('\;', '##,-,##', $this->queryParams['write protected annotations']);
			$wPAs = explode(';', $wPAs);
			foreach($wPAs as $key => $wPA){
				global $wgLang;
				if(trim($wPA) == $wgLang->getNSText(NS_CATEGORY)) $wPA = TF_CATEGORY_KEYWORD;
					$wPAs[$key] = trim($wPA);
			}
			$this->writeProtectedAnnotations = array_flip($wPAs);
		}
		
		//now deal with the ones set via HaloACL
		if(defined('HACL_HALOACL_VERSION')){
			
			global $wgUser, $wgLang;
			foreach($this->annotationPrintRequests as $annotation){
				$isWriteProtected = false;
				if($annotation['title'] != TF_CATEGORY_KEYWORD){
				 	$annotationId = Title::newFromText($wgLang->getNSText(SMW_NS_PROPERTY)
				 		.':'.$annotation['title'])->getArticleID();
				 	
				 	$isWriteProtected = !HACLEvaluator::hasPropertyRight($annotationId, 
						$wgUser->getId(), HACLRight::CREATE);
						
					$isEditProtected = !HACLEvaluator::hasPropertyRight($annotationId, 
						$wgUser->getId(), HACLRight::EDIT);

					if($isWriteProtected || $isEditProtected){
						$this->writeProtectedAnnotations[$annotation['title']] = true;
					}	
				}
			}
		}
	}


	private function getNotificationSystemHTML(){
		global $smwgHaloScriptPath;

		$displayNotificationSystem = '';
		if(count($this->queryResult->getErrors()) == 0){
			$displayNotificationSystem = ' style="display: none" ';
		}
		
		$html = '<div class="tabf_notification_system" '.$displayNotificationSystem.'>';

		$numberOfWarnings = count($this->addInstanceBlockers) + count($this->queryResult->getErrors());

		$html .= '<div class="tabf_notifications_heading">';
		$html .= '<div onclick="tf.expandNotificationSystem(event)" style="cursor: pointer">';
		$html .= '<img title="Expand" src="'.$smwgHaloScriptPath.'/skins/Annotation/images/plus.gif"></img>';
		$html .= '<span>&nbsp;'.wfMsg('tabf_ns_header_show').'</span><span class="tabf-warnings-number">'.$numberOfWarnings.'</span>';
		$html .= '</div>';
		$html .= '<div onclick="tf.collapseNotificationSystem(event)" style="cursor: pointer; display: none">';
		$html .= '<img title="Hide" src="'.$smwgHaloScriptPath.'/skins/Annotation/images/minus.gif"></img>';
		$html .= '<span>&nbsp;'.wfMsg('tabf_ns_header_hide').'</span><span class="tabf-warnings-number">'.$numberOfWarnings.'</span>';
		$html .= '</div>';
		$html .= '</div>';

		$html .= '<div class="tabf_notifications" style="display: none">';
		$html .= '<ol>';

		$html .= '<li class="tabf_add_instance_error" style="display: none">';
		$html .= wfMsg('tabf_ns_warning_invalid_instance_name');
		$html .= '<ul></ul>';
		$html .= '</li>';

		$html .= '<li class="tabf_invalid_value_warning" style="display: none">';
		$html .= wfMsg('tabf_ns_warning_invalid_value');
		$html .= '<ul></ul>';
		$html .= '</li>';

		$html .= '<li class="tabf_probably_lost_instance" style="display: none">';
		$html .= wfMsg('tabf_ns_warning_lost_instance_otf'); 
		$html .= '<ul></ul>';
		$html .= '</li>';

		$html .= '<li class="tabf_lost_instance_warning" style="display: none">';
		$html .= wfMsg('tabf_ns_warning_lost_instance'); 
		$html .= '<ul></ul>';
		$html .= '</li>';
		
		$html .= '<li class="tabf_save_error_warning" style="display: none">';
		$html .= wfMsg('tabf_ns_warning_save_error');
		$html .= '<ul></ul>';
		$html .= '</li>';

		if(count($this->addInstanceBlockers) > 0){
			$html .= '<li class="tabf_add_instance_warnings">';
			$html .= wfMsg('tabf_ns_warning_add_disabled');
			$html .= '<ul><li>';
			$html .= implode('</li><li>', $this->addInstanceBlockers);
			$html .= '</li></ul>';
			$html .= '</li>';
		}

		if(count($this->queryResult->getErrors()) > 0){
			$html .= '<li class="tabf_generic_query_warnings">';
			$html .= wfMsg('tabf_ns_warning_by_system');
			$html .= '<ul><li>';
			$html .= implode('</li><li>', $this->queryResult->getErrors());
			$html .= '</li></ul>';
			$html .= '</li>';
		}

		$html .= '</ol>';
		$html .= '</div>';

		$html .= '</div>';

		return $html;
	}


	/*
	 * Get range, type and allows values property for annotation print requests
	 */
	private function initializeAnnotationCharacteristics(){

		foreach($this->annotationPrintRequests as $key => $annotation){
			
			$this->annotationPrintRequests[$key]['allows value'] = array();
			
			if($annotation['title'] == TF_CATEGORY_KEYWORD){
				$this->annotationPrintRequests[$key]['autocomplete'] = 'ask: [[:Category:+]]';
				$this->annotationPrintRequests[$key]['type'] = 'category';
			} else {
				//default autocompletion for properties is all
				$this->annotationPrintRequests[$key]['autocomplete'] = 'all';
				$this->annotationPrintRequests[$key]['type'] = 'page';

				$prop = Title::newFromText($this->annotationPrintRequests[$key]['title'], SMW_NS_PROPERTY);
				if($prop->exists()){
					$prop = SMWWikiPageValue::makePageFromTitle($prop)->getDataItem();
					$store = smwfGetStore();
					$semanticData = $store->getSemanticData($prop);
					$annotations = $semanticData->getProperties();

					//get allowed values
					if(array_key_exists('_PVAL', $annotations)){
						
						foreach($semanticData->getPropertyValues($annotations['_PVAL']) as $val){
							$this->annotationPrintRequests[$key]['allows value'][] = 
								SMWDataValueFactory::newDataItemValue($val, null)->getShortWikiText();
						}
					}
					
					
					//get type
					$type = null;
					if(array_key_exists('_TYPE', $annotations)){
						$type = $semanticData->getPropertyValues($annotations['_TYPE']);
						$idx = array_keys($type);
						$idx = $idx[0];
						$type = SMWDataValueFactory::newDataItemValue($type[$idx], null)
								->getShortWikiText();
								
						global $smwgContLang;
						$datatypeLabels = $smwgContLang->getDatatypeLabels();
						$type = $datatypeLabels[substr($type, strpos($type, '#') + 1)];
						
						$this->annotationPrintRequests[$key]['type'] = strtolower($type);
					}

					if($type == null || strtolower($type) == 'page'){
						//check if there is a range defined
						if(array_key_exists('Has_domain_and_range', $annotations)){
							$range = $semanticData->getPropertyValues($annotations['Has_domain_and_range']);
							if(is_array($range)){
								$idx = array_keys($range);
								$idx = $idx[0];
								if($range[$idx] instanceof SMWDIContainer){
									$rProperties = $range[$idx]->getSemanticData()->getProperties();
									
									if(array_key_exists('Has_range', $rProperties)){
										$rVals = $range[$idx]->getSemanticData()->getPropertyValues($rProperties['Has_range']);
										$idx = array_keys($rVals);
										$idx = $idx[0];
										$range = SMWDataValueFactory::newDataItemValue($rVals[$idx], null)
											->getShortWikiText();
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
	 * Checks if adding instances is allowed or if it must be disabled
	 * because of insufficient preload values
	 */
	private function checkEnableAddInstance(){

		//check must not be done if enable add is deactivated anyway
		if(!$this->enableInstanceAdd){
			return;
		}

		foreach(TFQueryAnalyser::getQueryConditions($this->getQuerySerialization(), $this->isSPARQLQuery) as $name => $conditions){

			if($name == TF_INSTANCENAME_KEYWORD){
				continue;
			}

			global $wgLang;
			if($name == $wgLang->getNSText(NS_CATEGORY)){
				$name = TF_CATEGORY_KEYWORD;
			}

			//user must not enter a value for that annotation, e.g. because its value will be inferenced
			if(array_key_exists($name, $this->writeProtectedAnnotations)){
				continue;
			}

			// This works because: If there is only one condition and if we hace a preload value,
			// then:
			// a manual value was passed by the user
			// pr a preload value value for the only condition could be computed and there is not other
			// one for which no preload value could be computed
			if(count($conditions) == 1){
				if(array_key_exists($name, $this->annotationPreloadValues)){
					continue;
				}
			}

			//if the annotation is shown in the TF ten w.th. is ok, because the user can enter the appropriate values
			$found = false;
			foreach($this->annotationPrintRequests as $annotation){
				if($name == $annotation['title']){
					$found = true;
				}
			}
			if($found){
				continue;
			}

			$this->addInstanceBlockers[] = $name;
		}

		if(count($this->addInstanceBlockers) > 0){
			$this->enableInstanceAdd = false;
		}
	}


	/*
	 * Get column headings html
	 */
	private function addTableHeaderHTML(){
		global $smwgScriptPath, $smwgHaloScriptPath;

		$html ='<tr class="tf-table-header">';

		//add subject column
		$html .= '<th class="tabf_column_header">';
		$html .= '<a href="#" class="sortheader" onclick="tf.startRowSort(event);return false;">';
		$html .= '<span class="sortarrow"><img alt="[&lt;&gt;]" src="'.$smwgScriptPath.'/skins/images/sort_none.gif"/>';
		$html .= '</span></a>&nbsp;';
		$html .= '<span>';
		$html .= $this->subjectColumnLabel;
		$html .= '</span>';
		$html .= '<br/>';
		if(array_key_exists('enable filtering', $this->queryParams) && $this->queryParams['enable filtering'] == 'true' && !$this->isSPARQLQuery){ 
			$html .= '<input class="tf_filter_input wickEnabled" cmp-type="instance" style="width: 99%" constraints="ask: '.$this->queryResult->getQueryString().'" placeholder="'.wfMsg('tabf_filter_placeholder').'"></input>';
		}
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
			
			//add filter input
			if(array_key_exists('enable filtering', $this->queryParams) && $this->queryParams['enable filtering'] == 'true'  && !$this->isSPARQLQuery){
				$html .= '<div style="min-width: 100%; max-width: 100%;vertical-align: bottom">';
				
				if(count($annotation['allows value']) > 0){
					$html .= '<select class="tf_filter_input" cmp-type="=" style="width: 99%; display: inline">';
					$html .= '<option value="">'.wfMsg('tabf_filter_placeholder').'</option>';
					foreach($annotation['allows value'] as $val){
						$html .= '<option>'.$val.'</option>';
					}
					$html .= '</select>';
				} else {
					$type = $annotation['type'];
					
					$autocompletion = 'class="wickEnabled" constraints="annotation-value:'.$annotation['title'].'" ';
					
					switch($type){
						case 'number' :
						case 'date' :
							$html .= '<nobr style="width: 100%">';
							$html .= '<select class="tf_filter_input_helper" style="width: 15%; min-width: 35px"><option>=</option><option>&lt;</option><option>&gt;</option></select>';
							$html .= '<input class="tf_filter_input wickEnabled" cmp-type="choose" type="text" style="width: 84%" '.$autocompletion.' placeholder="'.wfMsg('tabf_filter_placeholder').'"/>';
							$html .= '</nobr>';
							break;
						case 'boolean' :
							$html .= '<select class="tf_filter_input" cmp-type="=" style="width: 99%">';
							$html .= '<option value="">'.wfMsg('tabf_filter_placeholder').'</option>';
							$html .= '<option>yes</option><option>no</option>';
							break;
						case 'page';
						case 'string';
						case 'url';
							$html .= '<input class="tf_filter_input wickEnabled" cmp-type="~" style="width: 99%" '.$autocompletion.' placeholder="'.wfMsg('tabf_filter_placeholder').'"/>';
							break;
						case 'category';
							$html .= '<input class="tf_filter_input wickEnabled" cmp-type="category" style="display: inline; width: 99%" constraints="namespace : category" placeholder="'.wfMsg('tabf_filter_placeholder').'"/>';
							break;
						default:
							$html .= '<input class="tf_filter_input wickEnabled" cmp-type="=" style="width: 100%" '.$autocompletion.' placeholder="'.wfMsg('tabf_filter_placeholder').'"/>';
							break;
					}
					
				}
				$html .= '</div>';
			}
			
			//add query condition data
			if(array_key_exists($annotation['title'], $this->annotationQueryConditions)){
				$html .= '<span class="tabf-query-conditions" style="display: none">';
				$html .= json_encode($this->annotationQueryConditions[$annotation['title']]);
				$html .= '</span>';
			}

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

				$html .= '<nobr>';
				if(array_key_exists($template, $this->templateParameterPrintRequestLabels)
						&& array_key_exists($param, $this->templateParameterPrintRequestLabels[$template])){
					$linkLabel = $this->templateParameterPrintRequestLabels[$template][$param];
				} else {
					$linkLabel = $template.'#'.$param;
				}
				$html .= $this->linker->makeLink(Title::newFromText($template, NS_TEMPLATE)->getFullText(), $linkLabel .' ');
				$html .= '<img src="'.$smwgHaloScriptPath.'/skins/template.gif" alt="[T]"></img>';
				$html .= '</nobr>';

				$html .= '</span>';
				if(array_key_exists('enable filtering', $this->queryParams) && $this->queryParams['enable filtering'] == 'true'  && !$this->isSPARQLQuery){
					$html .= '<br/><br/>';
				}
				$html .= '</th>';
			}
		}

		//add status column
		$html .= '<th style="display: none" class="tabf_status_column_header">';
		$html .= '<a href="#" class="sortheader" onclick="tf.startRowSort(event);return false;">';
		$html .= '<span class="sortarrow"><img alt="[&lt;&gt;]" src="'.$smwgScriptPath.'/skins/images/sort_none.gif"/>';
		$html .= '</span></a>';
		$html .= '<br/><br/>';
		$html .= '</th>';

		$html.= '</tr>';
		return $html;
	}

	/*
	 * Adds invisible row add the end of table. This row is used as
	 * template for adding new instances.
	 */
	private function addTabularFormAddRowTemplateHTML(){

		$html = '<tr style="display: none" class="tabf_table_row tabf_add_instance_template">';

		$html .= '<td revision-id="-1" ><textarea rows="1" >'.$this->parsePreloadValue($this->instanceNamePreloadValue).'</textarea>';
		$html .= '<input class="tabf-delete-button" type="button" value="Delete" style="display: none" onclick="tf.deleteInstance(event)"/>';
		$html .= '</td>';

		//Add cells for annotations
		foreach($this->annotationPrintRequests as $annotation){
			$html .= '<td>';

			if(array_key_exists($annotation['title'], $this->writeProtectedAnnotations)){
				$html .= '<div></div>';
			} else {
				$autocompletion = '';
				if(!is_null($annotation['autocomplete'])){
					$autocompletion = 'class="wickEnabled" constraints="';
					$autocompletion .= $annotation['autocomplete'];
					$autocompletion .= '"';

					//do not display ns for category column when autocompleting
					if($annotation['title'] == TF_CATEGORY_KEYWORD){
						$autocompletion .= ' pasteNS="" ';
					} else {
						$autocompletion .= ' pasteNS="true" ';
					}
				}

				$preloadValues = array('');
				if(array_key_exists($annotation['title'], $this->annotationPreloadValues)){
					$preloadValues = $this->annotationPreloadValues[$annotation['title']];
					unset($this->annotationPreloadValues[$annotation['title']]);
				}

				foreach($preloadValues as $preloadValue){
					$html .= "<textarea ".$autocompletion." rows='1' originalValue='' pasteNS='true'>"
					.$this->parsePreloadValue($preloadValue)."</textarea>";
				}
			}
			$html .= '</td>';
		}

		//Add template cells
		foreach($this->templateParameterPrintRequests as $template => $params){
			foreach($params as $param => $dC){
				$html .= '<td>';

				$value = '';
				if(array_key_exists($template, $this->templateParameterPrintRequestPreload)){
					if(array_key_exists($param, $this->templateParameterPrintRequestPreload[$template])){
						$value = $this->parsePreloadValue($this->templateParameterPrintRequestPreload[$template][$param]);
					}
				}
					
				$html .= "<textarea rows='1' template-id=".'"'.TF_NEW_TEMPLATE_CALL.'"'." originalValue='' >".$value."</textarea>";
					
				$html .= '</td>';
			}
		}

		//add status column
		$html .= '<td class="tabf_status_cell">';

		$okDisplay = $readProtectedDisplay = $writeProtectedDisplay =
		$addedDisplay = $notExistsDisplay = 'none';

		$addedDisplay = '';

		$html .= TFTabularFormRowData::getStatusColumnHTML($okDisplay, $readProtectedDisplay, $writeProtectedDisplay,
		$addedDisplay, $notExistsDisplay);
			
		$html .= '</td>';

		$html .= '<div class="tf-hidden-preload-values" style="display: none">';
		foreach($this->annotationPreloadValues as $name => $values){
			if(!array_key_exists($name, $this->writeProtectedAnnotations)){
				foreach($values as $value){
					$html .= '<div annotationName="'.$name.'">'.$value.'</div>';
				}
			}
		}
		$html .= '</div>';

		return $html;
	}

	/*
	 *	Parse preloading values in case they contain Wiki text
	 */
	private function parsePreloadValue($preload){
		if(strlen(trim($preload)) > 0){
			global $wgParser;
			$popts = new ParserOptions();
			$wgParser->startExternalParse(Title::newFromText('TabularFormsDummy'), $popts, Parser::OT_HTML);

			$preload = $wgParser->internalParse($preload);
		}

		return $preload;
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

		$html .= '<td colspan="'.$colSpan.'" style="vertical-align: top">';

		//show further results widget
		$html .= '<span class="tabf_further_results">';
		
		$offset = 0;
		if(array_key_exists('offset', $this->queryParams)){
			$offset = $this->queryParams['offset']*1;
		}
		if($offset > 0){
			$html .= '<a href="javascript:tf.showPrevious(\''.$this->tabularFormId.'\');">'.wfMsg( 'tabf_paging_previous').'</a>';
		} else {
			$html .= '<b>'.wfMsg( 'tabf_paging_previous').'</b>';
		}
		
		$html .= '&nbsp;&nbsp;&nbsp;&nbsp;<b>'.wfMsg( 'tabf_paging_results').' '.$offset.' - '.(count($this->formRowsData)+$offset).'</b>&nbsp;&nbsp;&nbsp;&nbsp;';
			
		if($this->hasFurtherResults){
			$html .= '<a href="javascript:tf.showNext(\''.$this->tabularFormId.'\');">'.wfMsg( 'tabf_paging_next').'</a>';
		} else {
			$html .= '<b>'.wfMsg( 'tabf_paging_next').'</b>';	
		}
			
		global $smwgQDefaultLimit;
		$limit = $smwgQDefaultLimit;
		if(array_key_exists('limit', $this->queryParams)){
			$limit = $this->queryParams['limit']*1;
		}
				
		$preConfiguredLimits = array(20, 50, 100, 250, 500);
		$html .= '<span>&nbsp;&nbsp;&nbsp;&nbsp;(&nbsp;';
		foreach($preConfiguredLimits as $key => $pcLimit){
			if($limit == $pcLimit){
				$preConfiguredLimits[$key] = '<b>'.$pcLimit.'</b>';
			} else {
				$preConfiguredLimits[$key] = '<a href="javascript:tf.changeLimit('.$pcLimit.', \''.$this->tabularFormId.'\');">'.$pcLimit.'</a>';
			}
		}
		$delimiter = '<span>&nbsp;|&nbsp;</span>';
		$html .= implode($delimiter, $preConfiguredLimits);
		$html .= '&nbsp;)</span>';
		
		$html .= '</span>';
		
		$saveColSpan = 0;
		$html .= '<table style="float: right">';
		$html .= '<tr>';

		if($this->enableInstanceAdd){
			$html .= '<td style="display: none">';
			$html .= '<input class="tabf_add_button" type="button" value="'.wfmsg('tabf_add_label').'" onclick="tf.addInstance('."'".$tabularFormId."'".')"/>';
			$html .= '</td>';

			$saveColSpan += 1;
		}

		$html .= '<td>';
		$html .= '<input type="button" value="'.wfMsg('tabf_refresh_label').'" onclick="tf.refreshForm('."'".$tabularFormId."'".')"/>';
		$html .= '</td>';
		

		if(!array_key_exists('expert mode', $this->queryParams) || $this->queryParams['expert mode'] != 'true'){
			$html .= '<td>';
			$html .= '<input class="tabf_cancel_button" type="button" value="Cancel" onclick="tf.cancelFormEdit('."'".$tabularFormId."'".')"/ style="display: none">';
			$html .= '<input class="tabf_edit_button" type="button" value="Edit" onclick="tf.switchToEditMode('."'".$tabularFormId."'".')"/>';
			$html .= '</td>';
			$html .= '</tr>';
			
			$html .= '<tr style="display: none">';
			$html .= ($saveColSpan > 0) ? '<td colspan="'.$saveColSpan.'"></td>' : '';
			$html .= '<td style="text-align: right" colspan="2">';
			
			$html .= '<input class="tabf_save_button" disabled="disabled" type="button" value="'.wfMsg('tabf_save_label').'" onclick="tf.saveFormData(event,'."'".$tabularFormId."'".')"/>';
		
			$html .= '</td>';
			$html .= '</tr>';
		
		}else {
			$html .= '<td><span>';
			$html .= '<input class="tabf_save_button" disabled="disabled" type="button" value="'.wfMsg('tabf_save_label').'" onclick="tf.saveFormData(event,'."'".$tabularFormId."'".')"/>';
		
			$html .= '</span></td>';
			$html .= '</tr>';
		}
		
		

		$html .= '</table>';

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
			$title = $row[0]->getResultSubject()->getTitle()->getFullText();
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

		$formRowData->setManuallyWriteProtectedAnnotations($this->writeProtectedAnnotations);
			
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
				if(strpos($printRequest->getHash(), '0:') === 0){

					$label = $printRequest->getText($this->outputMode, $this->linker);
					$label = explode('=', $label);
					$preload = count($label) > 1 ? $label[1] : '';
					$label = $label[0];

					$this->annotationPrintRequests[$count] =
					array('title' => TF_CATEGORY_KEYWORD,
						'label' => $label,
						'preload' => $preload,
						'rawlabel' => $printRequest->getText($this->outputMode, null));
				}

				continue;
			}

			$label = $printRequest->getText($this->outputMode, $this->linker);
			$labelIntro = substr($label, 0, strpos($label, '>') + 1);
			$label = substr($label, strpos($label, '>') + 1);
			$labelOutro = substr($label, strpos($label, '<'));
			$label = substr($label, 0, strpos($label, '<'));
			$label = explode('=', $label);
			$preload = count($label) > 1 ? $label[1] : '';
			$label = $labelIntro.$label[0].$labelOutro;

			$this->annotationPrintRequests[$count] =
			array('title' => $printRequest->getData()->getText(),
				'label' => $label,
				'preload' => $preload,
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

						$label = explode('=', $label, 2);

						$this->templateParameterPrintRequestLabels[$param[0]][$param[1]] = $label[0];

						if(count($label) == 2){
							$this->templateParameterPrintRequestPreload[$param[0]][$param[1]] = $label[1];
						}

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
	private $isReadProtected;
	private $isWriteProtected;


	/*
	 * Constructor, that sets the title of the instance, which is
	 * represented by this row
	 */
	public function __construct($title){
		$this->title = Title::newFromText($title);
		$this->dataAPIAccess = TFDataAPIAccess::getInstance($this->title);
		$this->isReadProtected = $this->dataAPIAccess->isReadProtected;
		$this->isWriteProtected = $this->dataAPIAccess->isWriteProtected;
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
		|| $this->isReadProtected || $this->isWriteProtected){
			foreach($this->annotations as $key => $annotations){
				foreach($annotations as $k => $annotation){
					$this->annotations[$key][$k]->isWritable = false;
				}
			}
		}
	}

	/*
	 * Maek manually write protected annotations
	 */
	public function setManuallyWriteProtectedAnnotations($writeProtectedAnnotations){

		foreach($writeProtectedAnnotations as $name => $dC){
			if(array_key_exists($name, $this->annotations)){
				foreach($this->annotations[$name] as $key => $annotation){
					$annotation->isWritable = false;
					$this->annotations[$name][$key] = 	$annotation;
				};
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
		|| $this->isReadProtected || $this->isWriteProtected){
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
			if($this->isReadProtected){
				$class .= ' tabf_read_protected_row';
			} else if($this->isWriteProtected){
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
			if($enableInstanceDelete && ($this->title instanceof Title && $this->title->exists() && $this->title->userCan('delete'))){
				$html .= '<input class="tabf-delete-button" type="button" value="Delete" style=" display: none" onclick="tf.deleteInstance(event)"/>';
			}
		} else {
			$html .= '<td class="tabf_table_cell" revision-id="-1">';
			$html .= '<textarea disabled="disabled" class="tabf_valid_instance_name" rows="1">';
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

			$autocompletion = '';
			if(!is_null($annotation['autocomplete'])){
				$autocompletion = 'class="wickEnabled" constraints="';
				$autocompletion .= $annotation['autocomplete'];
				$autocompletion .= '"';

				//do not display ns for category column when autocompleting
				if($annotation['title'] == TF_CATEGORY_KEYWORD){
					$autocompletion .= ' pasteNS="" ';
				} else {
					$autocompletion .= ' pasteNS="true" ';
				}
			}

			$annotations = $this->annotations[$annotation['title']];
			$moreThanOne = false;
			foreach($annotations as $annotation){
				if($annotation->isWritable || ($this->title instanceof Title && !$this->title->exists() && $enableInstanceAdd)){
					$moreThanOne == true ? $style = 'style="border-top: 1px inset grey;"' : $style ='';
					$html .= "<textarea disabled=\"disabled\" ".$autocompletion." ". $style ." rows='2' annotation-hash='".$annotation->hash
					."' annotation-type-id='".$annotation->typeId."' pasteNS='true'>".$annotation->currentValue."</textarea>";
				} else {
					$html .= '<div>'.$annotation->renderedValue.'</div>';
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
					if($this->templateParameters[$template][$param]->isWritable
					|| ($this->title instanceof Title && !$this->title->exists() && $enableInstanceAdd)){
						$html .= "<textarea disabled='disabled' rows='1' template-id=".'"'.TF_NEW_TEMPLATE_CALL.'"'."></textarea>";
					} else {
						$html .= '<div></div>';
					}
				} else {
					foreach($this->templateParameters[$template][$param]->currentValues as $templateId => $currentValue){
						if($this->templateParameters[$template][$param]->isWritable
						|| ($this->title instanceof Title && !$this->title->exists() && $enableInstanceAdd) ){
							$html .= "<textarea disabled='disabled' rows='1' template-id=".'"'.$templateId.'"'."'>".$currentValue."</textarea>";
						} else {
							$html .= '<div>'.$currentValue.'</div>';
						}
					}
				}
					
				$html .= '</td>';
			}
		}

		//add status column
		$html .= '<td class="tabf_status_cell" style="display: none">';

		$okDisplay = $readProtectedDisplay = $writeProtectedDisplay =
		$addedDisplay = $notExistsDisplay = 'none';

		if($this->title instanceof Title && $this->title->exists()){
			if($this->isReadProtected == true){
				$readProtectedDisplay = '';
			} else if($this->isWriteProtected == true){
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

		//		$title = wfMsg('tabf_status_unchanged');
		//		$html .= '<img class="tabf_ok_status" title="'.$title.'" style="display: '.$okDisplay.'" src="'.
		//			$smwgHaloScriptPath.'/skins/TabularForms/Unmodified.png"></img>';

		$title = wfMsg('tabf_status_notexist_create');
		$html .= '<img class="tabf_added_status" title="'.$title.'" style="display: '.$addedDisplay.'" src="'
		.$smwgHaloScriptPath.'/skins/TabularForms/Added.png"></img>';

		//		$title = wfMsg('tabf_status_notexist');
		//		$html .= '<img class="tabf_exists_not_status" title="'.$title.'" style="display: '.$notExistsDisplay.'" src="'
		//			.$smwgHaloScriptPath.'/skins/TabularForms/Warning.png"></img>';
		//
		//		$title = wfMsg('tabf_status_readprotected');
		//		$html .= '<img class="tabf_exists_not_status" title="'.$title.'" style="display: '.$readProtectedDisplay.'" src="'
		//			.$smwgHaloScriptPath.'/skins/TabularForms/Warning.png"></img>';
		
		$title = wfMsg('tabf_status_writeprotected');
		$html .= '<img class="tabf_write_protected_status" title="'.$title.'" style="display: '.$writeProtectedDisplay.'" src="'
			.$smwgHaloScriptPath.'/skins/TabularForms/WriteProtected.png"></img>';
			
		$title = wfMsg('tabf_status_delete');
		$html .= '<img class="tabf_deleted_status" title="'.$title.'" style="display: none" src="'
		.$smwgHaloScriptPath.'/skins/TabularForms/Deleted.png"></img>';

		$title = wfMsg('tabf_status_modified');
		$html .= '<img class="tabf_modified_status" title="'.$title.'" style="display: none" src="'
		.$smwgHaloScriptPath.'/skins/TabularForms/Modified.png"></img>';

		//		$title = wfMsg('tabf_status_saved');
		//		$html .= '<img class="tabf_saved_status" title="'.$title.'" style="display: none" src="'
		//		.$smwgHaloScriptPath.'/skins/TabularForms/Saved.png"></img>';

		$title = wfMsg('tabf_nc_icon_title_lost_instance');;
		$html .= '<img class="tabf_getslost_status" title="'.$title.'" style="display: none" src="'
		.$smwgHaloScriptPath.'/skins/TabularForms/Attention.png"></img>';
		
		$title = wfMsg('tabf_nc_icon_title_invalid_value');
		$html .= '<img class="tabf_invalid_value_status" title="'.$title.'" style="display: none" src="'
		.$smwgHaloScriptPath.'/skins/TabularForms/Attention.png"></img>';
		
		$title = wfMsg('tabf_nc_icon_title_save_error');
		$html .= '<img class="tabf_error_status" title="'.$title.'" style="display: none" src="'
		.$smwgHaloScriptPath.'/skins/TabularForms/Error.png"></img>';

		$title = wfMsg('tabf_status_pending');
		$html .= '<img class="tabf_pending_status" title="'.$title.'" style="display: none" src="'
		.$smwgHaloScriptPath.'/skins/TabularForms/Pending.gif"></img>';

		return $html;
	}


}




