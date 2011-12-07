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

class DALReadFeed implements IDAL {

	private $feedDefinitions;
	private $dataSourceSpec;
	
	
	public function getSourceSpecification() {
		//todo:language
		
		return 
			'<?xml version="1.0"?>'."\n".
			'<DataSource xmlns="http://www.ontoprise.de/smwplus#">'."\n".
			' 	<supercategory display="Feed definition category:" type="text" autocomplete="namespace: '.NS_CATEGORY.'" ></supercategory>'."\n".
			'	<urlproperty display="Name of URL property" type="text" autocomplete="namespace: '.SMW_NS_PROPERTY.'"></urlproperty>'."\n".
			'	<prefixproperty display="Name of prefix property:" type="text" autocomplete="namespace: '.SMW_NS_PROPERTY.'"></prefixproperty>'."\n".
			'</DataSource>'."\n";
	}
	
	
	public function getImportSets($dataSourceSpec) {
		$importSets = array();
		
		$this->initDataSourceSpecValues($dataSourceSpec);
		if(is_null($this->dataSourceSpec['supercat']) || is_null($this->dataSourceSpec['urlprop'])){
			return 'todo: error message';
		}

		$this->initFeedDefinitions(
			$this->dataSourceSpec['supercat'], $this->dataSourceSpec['urlprop'], $this->dataSourceSpec['prefixprop']);
			
		foreach($this->feedDefinitions as $feed){
			$importSets[$feed['title']] = true;
		}
		
		return array_keys($importSets);
	}

	
	public function getProperties($dataSourceSpec, $importSet) {
		$properties = array();
		
		$properties['articleName'] = true;
		$properties[DI_TI_DAM_FEED_SUBJECT] = true;
		$properties[DI_TI_DAM_FEED_STEMS_FROM] = true;
		$properties[DI_TI_DAM_FEED_CONTENT] = true;
		$properties[DI_TI_DAM_FEED_TAG] = true;
		$properties[DI_TI_DAM_FEED_AUTHOR] = true;
		$properties[DI_TI_DAM_FEED_CONTRIBUTOR] = true;
		$properties[DI_TI_DAM_FEED_COPYRIGHT] = true;
		$properties[DI_TI_DAM_FEED_DATE] = true;
		$properties[DI_TI_DAM_FEED_LOCAL_DATE] = true;
		$properties[DI_TI_DAM_FEED_PERMA_LINK] = true;
		$properties[DI_TI_DAM_FEED_URL] = true;
		$properties[DI_TI_DAM_FEED_ENCLOSURES] = true;
		$properties[DI_TI_DAM_FEED_LATITUDE] = true;
		$properties[DI_TI_DAM_FEED_LONGITUDE] = true;
		$properties[DI_TI_DAM_FEED_SOURCE] = true;
		$properties[DI_TI_DAM_FEED_ID] = true;
		
		return array_keys($properties);
	}
	
	
	public function getTermList($dataSourceSpec, $importSet, $inputPolicy) {
		return $this->createTerms($dataSourceSpec, $importSet, $inputPolicy, true);
	}
	
	
	public function getTerms($dataSourceSpec, $importSet, $inputPolicy, $conflictPolicy) {
		return $this->createTerms($dataSourceSpec, $importSet, $inputPolicy, false);
	}
	
	
	private function initDataSourceSpecValues($dataSourceSpec) {
		
		if(is_array($this->dataSourceSpec)){
			return $this->dataSourceSpec;
		}
		
		$this->dataSourceSpec = array(
			'supercat' => null, 'urlprop' => null, 'prefixprop' => null, 'delimiter' => ',');
		
		$dataSourceSpec = str_replace(
			array('SUPERCATEGORY', 'URLPROPERTY', 'PREFIXPROPERTY', ),
			array('supercategory', 'urlproperty', 'prefixproperty'),
			$dataSourceSpec);
			
		
		if(preg_match('/<supercategory.*?>(.*?)<\/supercategory>/i', $dataSourceSpec, $hit)){
			$this->dataSourceSpec['supercat'] = $hit[1];
		} 
		if(preg_match('/<urlproperty.*?>(.*?)<\/urlproperty>/i', $dataSourceSpec, $hit)){
			$this->dataSourceSpec['urlprop'] = $hit[1];
		}
		if(preg_match('/<prefixproperty.*?>(.*?)<\/prefixproperty>/i', $dataSourceSpec, $hit)){
			$this->dataSourceSpec['prefixprop'] = $hit[1];
		}
		
		return $this->dataSourceSpec;
	}
	
	private function createTerms($dataSourceSpec, $givenImportSet, $inputPolicy, 
			$createTermList) {

		$this->initDataSourceSpecValues($dataSourceSpec);
		if(is_null($this->dataSourceSpec['supercat']) || is_null($this->dataSourceSpec['urlprop'])){
			return 'todo: error message';
		}

		$this->initFeedDefinitions(
			$this->dataSourceSpec['supercat'], $this->dataSourceSpec['urlprop'], $this->dataSourceSpec['prefixprop']);

		$inputPolicy = DIDALHelper::parseInputPolicy($inputPolicy);	
			
		global $smwgDIIP;
		@ require_once($smwgDIIP.'/libs/simplepie/simplepie.inc');
		
		$terms = new DITermCollection();
		error_reporting(E_ALL); //necessary since simplepie produces som strict warnings
		foreach($this->feedDefinitions as $feedDefinition){
			//todo: deal with feed item limits, i.e. importing the same 100 feed items
			//every ten minutes is not a good idea
			
			//only read feed if no other import set was chosen
			if(strlen(trim(''.$givenImportSet)) > 0){
				if(trim($givenImportSet) != trim($feedDefinition['title'])){
					continue;
				}
			}
			
			@$feed = new SimplePie($feedDefinition['url']);
			
			$error = $feed->error();
			
			@$items = $feed->get_items();
			if(count($items) == 0 && !is_null($error)&& strlen($error) > 0){
				echo('It was not possible to connect to '.$feedDefinition['title'].': '.$error);
				$terms->addErrorMsg('It was not possible to connect to '.$feedDefinition['title'].': '.$error);
				continue;
			} 
			
			foreach($items as $item){
				$term = new DITerm();
					
				if(!is_null($title = $item->get_title())){
					$term->setArticleName($feedDefinition['prefix'].$title);
				} else {
					continue;
				}
				
				if(!DIDALHelper::termMatchesRules($feedDefinition['title'], $term->getArticleName(), $givenImportSet, $inputPolicy)){
					continue;
				}
				
				if(!$createTermList){
				
					$term->addProperty('Has subject', $title);
					
					$term->addProperty('Stems from feed', $feedDefinition['title']);
					
					if(!is_null($content = $item->get_content())){
						$term->addProperty('Has content', $content);
					} 
	
					if(!is_null($categories = $item->get_categories())){
						foreach($categories as $category){
							$term->addProperty('Has tag', $category->get_term()); 
						}
					}
	
					if(!is_null($authors = @$item->get_authors())){
						//todo: describe the behaviour below
						foreach($authors as $author){
							if(!is_null($t = @$author->get_name())){
								$term->addProperty('Has author', $t);
							} else if (!is_null($t = @$author->get_email())){
								$term->addProperty('Has author', $t);
							} else if (!is_null($t = @$author->get_link())){
								$term->addProperty('Has author', $t);
							}
						}
					}
	
					if(!is_null($contributors = @$item->get_contributors())){
						//todo: describe the behaviour below
						foreach($contributors as $contributor){
							if(!is_null($t = $contributor->get_name())){
								$term->addProperty('Has contributor', $t);
							} else if (!is_null($t = @$contributor->get_email())){
								$term->addProperty('Has contributor', $t);
							} else if (!is_null($t = @$contributor->get_link())){
								$term->addProperty('Has contributor', $t);
							}
						}
					}
	
					if(!is_null($copyright = @$item->get_copyright())){
						$term->addProperty('Has copyright', $copyright);
					}
	
					//todo: date encoding
					if(!is_null($date = $item->get_date())){
						$term->addProperty('Has publication date', $date);
					}
	
					//todo: date encoding
					if(!is_null($localdate = $item->get_local_date())){
						$term->addProperty('Has local publication date', $localdate);
					}
	
					if(!is_null($permalink = $item->get_permalink())){
						$term->addProperty('Has permalink', $permalink);
					}
	
					if(!is_null($link = $item->get_link())){
						$term->addProperty('Has URL', $link);
					}
	
					if(!is_null($enclosures = $item->get_enclosures())){
						//todo:describe this
						
						//all other enclosures attributes are ignored
						//in the future, we could add the enclosures as their own terms
						//like we handle attachmets for e-mails
						foreach($enclosures as $enclosure){
							if(!is_null($t = $enclosure->get_link())){
								$term->addProperty('Enclosures file', $t);
							}
						}
					}
	
					if(!is_null($latitude = $item->get_latitude())){
						$term->addProperty('Has latitude', $latitude);
					}
	
					if(!is_null($longitude = $item->get_longitude())){
						$term->addProperty('Has longiitude', $longitude);
					}
	
					//ttodo: describe this behaviour
					if(!is_null($source = $item->get_source())){
						if(!is_null($sourcelink = $source->get_permalink() )){
							$term->addProperty('Has source', $sourcelink);
						} else if(!is_null($sourcelink = $source->get_link() )){
							$term->addProperty('Has source', $sourcelink);
						}
					}
					
					if(!is_null($id = $item->get_id())){
						$term->addProperty('Has id', $id);
					}
				}
				
				$terms->addTerm($term);
			}
		}
		
		return $terms;
	}

			
	public function executeCallBack($signature, $templateName, $extraCategories, $delimiter, $conflictPolicy, $termImportName){
		return array(true, array());
	}
	
	private function initFeedDefinitions($superCategory, $urlPropertyName, $prefixPropertyName){
		if(is_array($this->feedDefinitions)){
			return $this->feedDefinitions;
		}
		
		global $smwgResultFormats, $smwgHaloIP;
		require_once "$smwgHaloIP/includes/queryprinters/SMW_QP_XML.php";
		$smwgResultFormats['xml'] = 'SMWXMLResultPrinter';

		global $wgLang;
		if(strpos($superCategory, $wgLang->getNSText(NS_CATEGORY).':') === false){
			$superCategory = $wgLang->getNSText(NS_CATEGORY).':'.$superCategory;
		}
		if(strpos($urlPropertyName, $wgLang->getNSText(SMW_NS_PROPERTY).':') === 0){
			$urlPropertyName = substr($urlPropertyName, strpos($urlPropertyName, ":") +1);
		}
		if(strpos($prefixPropertyName, $wgLang->getNSText(SMW_NS_PROPERTY).':') === 0){
			$prefixPropertyName = substr($prefixPropertyName, strpos($prefixPropertyName, ":") +1);
		}
		
		$rawParams[] = '[['.$superCategory.']] [['.$urlPropertyName.'::+]]';
		$rawParams[] = '?'.$urlPropertyName;
		if(!is_null($prefixPropertyName) && strlen($prefixPropertyName) > 0)
			$rawParams[] = "?".$prefixPropertyName;

		SMWQueryProcessor::processFunctionParams($rawParams,$querystring,$params,$printouts);
		$params['format'] = "xml";
		$params['limit'] = 500;
		
		$xmlResult = SMWQueryProcessor::getResultFromQueryString($querystring,$params,$printouts, SMW_OUTPUT_FILE);
		
		$dom = simplexml_load_string($xmlResult);
		$dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");
		
		$this->feedDefinitions = array();
		global $smwgHaloTripleStoreGraph;
		foreach($dom->xpath('//sparqlxml:result') as $result){
			
			$title = "".$result->binding[0]->uri;
			$title = substr($title, strlen($smwgHaloTripleStoreGraph.'/a/'));
			$title = str_replace('_', ' ', $title);
			
			@$url = "".$result->binding[1]->literal;
			if(strlen($url) == 0){
				$url = "".$result->binding[1]->uri;
				$url = substr($url, strlen($smwgHaloTripleStoreGraph.'/a/'));
			}
			if(strlen($url) == 0){
				//a url is necessary, but this should not happen since having an URL is a query condition
				continue;
			}
			
			$prefix = '';
			if(count($result->binding) > 2){
				@$prefix = "".$result->binding[2]->literal;
				if(strlen($prefix) == 0){
					$prefix = "".$result->binding[2]->uri;
					$prefix = substr($prefix, strlen($smwgHaloTripleStoreGraph.'/a/'));
				}
			}
			
			$this->feedDefinitions[] = array('title' => $title, 'url' => $url, 'prefix' => $prefix);
		}
		
		return $this->feedDefinitions;
	}
}