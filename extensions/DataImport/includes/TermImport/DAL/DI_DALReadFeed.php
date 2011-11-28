<?php

//$feed = new SimplePie('http://smwforum.ontoprise.com/smwbugs/buglist.cgi?bug_severity=blocker&bug_severity=critical&bug_severity=major&bug_severity=normal&bug_status=NEW&bug_status=ASSIGNED&bug_status=REOPENED&cf_issuetype=Bug&email1=steinbauer&emailassigned_to1=1&emailtype1=substring&query_format=advanced&title=Issue%20List&ctype=atom');
//$feed = new SimplePie('http://hsozkult.geschichte.hu-berlin.de/rss.xml');
//$feed = new SimplePie('http://anderstornkvist.se/atomfeed.php');
//$feed = new SimplePie('http://www.w3.org/QA/atom.xml');

class DALReadFeed implements IDAL {

	private $feedDefinitions;
	private $dataSourceSpec;
	
	/**
	 * Returns a specification of the data source.
	 * See further details in SMW_IDAL.php
	 */
	public function getSourceSpecification() {
		//todo: what other data source spec features do we have? autocompletion?
		
		//todo:add labels
		
		return 
			'<?xml version="1.0"?>'."\n".
			'<DataSource xmlns="http://www.ontoprise.de/smwplus#">'."\n".
			' 	<supercategory display="1" type="text"></supercategory>'."\n".
			'	<urlproperty display="2" type="text"></urlproperty>'."\n".
			'	<prefixproperty display="3" type="text"></prefixproperty>'."\n".
			'	<delimiter display="4" type="text"></delimiter>'."\n".
			'</DataSource>'."\n";
	}
	
	
	/**
	 * Returns a list of import sets and their description.
	 */
	public function getImportSets($dataSourceSpec) {
		$importSets = array();
		
		//todo: is it still possible to import all feeds
		
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

	/**
	 * Returns a list of properties and their description.
	 */
	public function getProperties($dataSourceSpec, $importSet) {
		$properties = array();
		
		//todo: deal with properties
		$properties['articleName'] = true;
		$properties['Has title'] = true;
		$properties['Stems from feed'] = true;
		$properties['Has content'] = true;
		$properties['Has tag'] = true;
		$properties['Has author'] = true;
		$properties['Has contributor'] = true;
		$properties['Has copyright'] = true;
		$properties['Has publication date'] = true;
		$properties['Has local publication date'] = true;
		$properties['Has permalink'] = true;
		$properties['Has URL'] = true;
		$properties['Enclosures'] = true;
		$properties['Has latitude'] = true;
		$properties['Has longiitude'] = true;
		$properties['Has sourc'] = true;
		
		return $properties;
	}
	
	
	/**
	 * Returns a list of the names of all terms that match the input policy. 
	 */
	public function getTermList($dataSourceSpec, $importSet, $inputPolicy) {
		return $this->createTerms($dataSourceSpec, $importSet, $inputPolicy, true);
	}
	
	
	/**
	 * Creates the term collection for the term import 
	 */
	public function getTerms($dataSourceSpec, $importSet, $inputPolicy, $conflictPolicy) {
		return $this->createTerms($dataSourceSpec, $importSet, $inputPolicy, false);
	}
	
	
	private function initDataSourceSpecValues($dataSourceSpec) {
		
		if(is_array($this->dataSourceSpec)){
			return $this->dataSourceSpec;
		}
		
		//todo: move this to the DIDALHelper
		
		$this->dataSourceSpec = array(
			'supercat' => null, 'urlprop' => null, 'prefixprop' => null, 'delimiter' => ',');
		
		if(preg_match('/<supercategory.*?>(.*?)<\/supercategory>/i', $dataSourceSpec, $hit)){
			$this->dataSourceSpec['supercat'] = $hit[1];
		} 
		if(preg_match('/<urlproperty.*?>(.*?)<\/urlproperty>/i', $dataSourceSpec, $hit)){
			$this->dataSourceSpec['urlprop'] = $hit[1];
		}
		if(preg_match('/<prefixproperty.*?>(.*?)<\/prefixproperty>/i', $dataSourceSpec, $hit)){
			$this->dataSourceSpec['prefixprop'] = $hit[1];
		}
		if(preg_match('/<delimiter.*?>(.*?)<\/delimiter>/i', $dataSourceSpec, $hit)){
			$this->dataSourceSpec['delimiter'] = $hit[1];
		}
		
		return $this->dataSourceSpec;
	}
	
	private function createTerms($dataSourceSpec, $importSet, $inputPolicy, 
			$createTermList) {

		
		$this->initDataSourceSpecValues($dataSourceSpec);
		if(is_null($this->dataSourceSpec['supercat']) || is_null($this->dataSourceSpec['urlprop'])){
			return 'todo: error message';
		}

		$this->initFeedDefinitions(
			$this->dataSourceSpec['supercat'], $this->dataSourceSpec['urlprop'], $this->dataSourceSpec['prefixprop']);
				
		global $smwgDIIP;
		require_once($smwgDIIP.'/libs/simplepie/simplepie.inc');
		
		
		$terms = new DITermCollection();
		error_reporting(E_ALL);
		foreach($this->feedDefinitions as $feedDefinition){
			//todo: suppress warnings for unavailable feeds
			
			//todo: deal with feed item limits, i.e. importing the same 100 feed items
			//every ten minutes is not a good idea
			
			$feed = new SimplePie($feedDefinition['url']);

			foreach($feed->get_items() as $item){
				$term = new DITerm();
					
				if(!is_null($title = @$item->get_title())){
					$term->setArticleName($title);
				} else {
					//an imported term needs an article name
					continue;
				}
				
				//todo:check input policy and import set
				
				if(!$createTermList){
				
					$term->addProperty('Has title', $title);
					
					$term->addProperty('Stems from feed', $feedDefinition['title']);
					
					if(!is_null($content = $item->get_content())){
						//todo: description or content?
						//todo: deal with special characters
						//todo: deal with HTML
						$term->addProperty('Has content', $content);
					} 
	
					if(!is_null($categories = $item->get_categories())){
						$temp = array();
						foreach($categories as $category){
							$temp[] = $category->get_term();
						}
	
						//todo:enable several values also if no template is used 
						$term->addProperty('Has tag', 
							implode($this->dataSourceSpec['delimiter'], $temp));
					}
	
					if(!is_null($authors = @$item->get_authors())){
						//todo: describe the behaviour below
						$temp = array();
						foreach($authors as $author){
							if(!is_null($t = @$author->get_name())){
								$temp[] = $t;
							} else if (!is_null($t = @$author->get_email())){
								$temp[] = $t;
							} else if (!is_null($t = @$author->get_link())){
								$temp[] = $t;
							}
						}
	
						if(count($temp) > 0){
							$term->addProperty('Has author', 
								implode($this->dataSourceSpec['delimiter'], $temp));
						}
					}
	
					if(!is_null($contributors = @$item->get_contributors())){
						//todo: describe the behaviour below
						$temp = array();
						foreach($contributors as $contributor){
							if(!is_null($t = $contributor->get_name())){
								$temp[] = $t;
							} else if (!is_null($t = @$contributor->get_email())){
								$temp[] = $t;
							} else if (!is_null($t = @$contributor->get_link())){
								$temp[] = $t;
							}
						}
	
						if(count($temp) > 0){
							$term->addProperty('Has contributor', 
								implode($this->dataSourceSpec['delimiter'], $temp));
						}
					}
	
					if(!is_null($copyright = @$item->get_copyright())){
						$term->addProperty('Has copyright', $copyright);
					}
	
					//todo: use language file for property names
					
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
						$temp = array();
						foreach($enclosures as $enclosure){
							if(!is_null($t = $enclosure->get_link())){
								$temp[] = $t;
							}
						}
							
						if(count($temp) > 0){
							//todo: find better property name
							$term->addProperty('Enclosures', 
								implode($this->dataSourceSpec['delimiter'], $temp));
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
				}
				
				$terms->addTerm($term);
			}
		}
		
		//file_put_contents('d://feedterms.rtf', print_r($terms, true));
		
		return $terms;
	}

			
	public function executeCallBack($signature, $mappingPolicy, $conflictPolicy, $termImportName){
		return true;
	}
	
	private function initFeedDefinitions($superCategory, $urlPropertyName, $prefixPropertyName){
		if(is_array($this->feedDefinitions)){
			return $this->feedDefinitions;
		}
		
		global $smwgResultFormats, $smwgHaloIP;
		require_once "$smwgHaloIP/includes/queryprinters/SMW_QP_XML.php";
		$smwgResultFormats['xml'] = 'SMWXMLResultPrinter';

		//todo. deal with namespaces in category and property names and son on
		$rawParams[] = '[['.$superCategory.']] [['.$urlPropertyName.'::+]]';
		$rawParams[] = '?'.$urlPropertyName;
		if(!is_null($prefixPropertyName))
			$rawParams[] = "?".$prefixPropertyName;

		SMWQueryProcessor::processFunctionParams($rawParams,$querystring,$params,$printouts);
		$params['format'] = "xml";
		//todo: deal with limit
		$params['limit'] = 500;
		
		$xmlResult = SMWQueryProcessor::getResultFromQueryString($querystring,$params,$printouts, SMW_OUTPUT_FILE);
		
		$dom = simplexml_load_string($xmlResult);
		$dom->registerXPathNamespace("sparqlxml", "http://www.w3.org/2005/sparql-results#");
		
		$this->feedDefinitions = array();
		global $smwgHaloTripleStoreGraph;
		foreach($dom->xpath('//sparqlxml:result') as $result){
			//todo: deal with case where url and prefix property have several values
			
			//todo: deal with case where url and prefix property are of type page
			
			$title = "".$result->binding[0]->uri;
			$title = substr($title, strlen($smwgHaloTripleStoreGraph.'/a/'));
			$title = str_replace('_', ' ', $title);
			$prefix = '';
			if(count($result->binding) > 2){
				$prefix = "".$result->binding[2]->literal;
			}
			$title = $prefix.$title;
			
			$url = "".$result->binding[1]->literal;
			
			$this->feedDefinitions[] = array('title' => $title, 'url' => $url);
		}
		
		return $this->feedDefinitions;
	}
}