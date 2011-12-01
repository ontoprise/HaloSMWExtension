<?php
/**
 * Use Exhibit to print query results.
 * @author Ning Hu
 * @file
 * @ingroup Halo
 */

/**
 * Result printer using Exhibit to display query results
 *
 * @ingroup Halo
 */

global $smwgSimileSite;
$smwgSimileSite = "http://api.simile-widgets.org";

class SRFExhibit extends SMWResultPrinter {
	public static function registerResourceModules() {
		global $wgResourceModules, $srfpgScriptPath;
		
		$moduleTemplate = array(
			'localBasePath' => dirname( __FILE__ ),
			'remoteBasePath' => $srfpgScriptPath . '/Exhibit',
			'group' => 'ext.srf'
		);

		$wgResourceModules['ext.srf.exhibit'] = $moduleTemplate + array(
			'scripts' => array( 'SRF_Exhibit.js' ),
			'styles' => array( 'SRF_Exhibit.css' ),
			'dependencies' => array(
				'jquery'
			)
		);
	}
	
    protected function includeJS() {
		global $smwgSimileSite, $wgGoogleMapsKey;
		SMWOutputs::requireHeadItem("simile_exhibit_all", '
<script src="' . $smwgSimileSite . '/exhibit/2.2.0/exhibit-api.js?autoCreate=false&safe=true&views=timeline&gmapkey=' . $wgGoogleMapsKey . '"></script>');

    	// MediaWiki 1.17 introduces the Resource Loader.
		$realFunction = array( 'SMWOutputs', 'requireResource' );
		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
			global $wgOut;
			$wgOut->addModules( 'ext.srf.exhibit' );
		}
		else {
			global $srfpgScriptPath;
			SMWOutputs::requireHeadItem('CSS', '<link rel="stylesheet" type="text/css" href="'.$srfpgScriptPath.'/Exhibit/SRF_Exhibit.css"></link>'); //include CSS
			
			//To run Exhibit some links to the scripts of the API need to be included in the header
			SMWOutputs::requireHeadItem('EXHIBIT2', '<script type="text/javascript" src="'.$srfpgScriptPath.'/Exhibit/SRF_Exhibit.js"></script>'); //includes javascript overwriting the Exhibit start-up functions
		}
	}
	
	///mapping between SMW's and Exhibit's the data types
	protected $m_types = array("_wpg" => "text", "_num" => "number", "_dat" => "date", "_geo" => "text", "_uri" => "url");

	protected static $exhibitRunningNumber = 0; //not sufficient since there might be multiple pages rendered within one PHP run; but good enough now

	///overwrite function to allow execution of result printer even if no results are available (in case remote query yields no results in local wiki)
	public function getResult($results, $params, $outputmode) {
		$this->readParameters($params,$outputmode);
		$result = $this->getResultText($results,$outputmode);
		return $result;
	}

	///function aligns the format of SMW's property names to Exhibit's format
	protected function encodePropertyName($property){
		return strtolower(str_replace(" ","_",trim($property)));
	}

	///implements an algorithm for automatic determination of a suitable intervall for numeric facets
	protected function determineSuitableInterval($res,$facet,$fieldcounter){
		$valuestack = array();
		while($row = $res->getNext()){
			$tmp = clone $row[$fieldcounter];
			$object = $tmp->getNextObject();
			if($object instanceof SMWNumberValue) {
				$valuestack[] = version_compare(SMW_VERSION, '1.5', '>=') ? $object->getWikiValue() : $object->getNumericValue();
			}
		}
		if(sizeof($valuestack) > 0){
			$average = (int)((max($valuestack) - min($valuestack))/2);
			$retval = str_pad(1,strlen($average)-1,0,STR_PAD_RIGHT);
		}
		else $retval = 0;
		return $retval;
	}

	protected function determineNamespace($res){
		$row = $res->getNext();
		if($row != null){
			$tmp = clone $row[0];
			$object = $tmp->getNextObject();
			if($object instanceof SMWWikiPageValue){
				$value = $object->getPrefixedText();
				if(strpos($value,":")){
					$value = explode(":",$value,2);
					return $value[0].":";
				}
			}
			return "";
		}
	}

	protected function getResultText($res, $outputmode) {
		$this->includeJS();

		global $smwgIQRunningNumber, $wgScriptPath, $wgGoogleMapsKey, $smwgScriptPath, $srfpgIP, $srfpgScriptPath;

		wfLoadExtensionMessages('SemanticMediaWiki');

		//the following variables indicate the use of special views
		//the variable's values define the way Exhibit is called
		$timeline = false;
		$map = false;

		$itemTypes = "QueryResult_" . SRFExhibit::$exhibitRunningNumber;
		$collection = "ExhibitQueryCol" . SRFExhibit::$exhibitRunningNumber;

		/*The javascript file adopted from Wibbit uses a bunch of javascript variables in the header to store information about the Exhibit markup.
		 The following code sequence creates these variables*/

		//prepare sources (the sources holds information about the table which contains the information)
		$colstack = array();
		$properties = '';
		$geos = array();
		foreach ($res->getPrintRequests() as $pr){
			$type = (array_key_exists($pr->getTypeID(),$this->m_types)?$this->m_types[$pr->getTypeID()]:'text');
			$key = $this->encodePropertyName($pr->getLabel());
			if($pr->getTypeID() == '_geo') $geos[$key] = true;
			if($type != 'text') $properties .= 'smwExhibitJSON.properties.' . $key . ' = { valueType: "' . $type .'" };';
			$colstack[] = $key;
		}
		array_shift($colstack);
		array_unshift($colstack, 'labeltext');

		//prepare facets
		$facetstack = array();
		if(array_key_exists('facets', $this->m_params)){
			$facets = explode(',', $this->m_params['facets']);
			$params = array('height');
			$facparams = array();
			foreach($params as $param){
				if(array_key_exists($param, $this->m_params)) $facparams[] = 'ex:'.$param.'="'.$this->encodePropertyName($this->m_params[$param]).'" ';
			}
			foreach( $facets as $facet ) {
				$fieldcounter=0;
				foreach ($res->getPrintRequests() as $pr){
					if($this->encodePropertyName($pr->getLabel()) == $this->encodePropertyName($facet)){
						switch($pr->getTypeID()){
							case '_num':
								$intervall = $this->determineSuitableInterval(clone $res,$facet,$fieldcounter);
								$facetstack[] = '
<div ex:role="facet" ex:showMissing="false" ex:collectionID="' . $collection . '" ex:facetClass="NumericRange" ex:interval="'.$intervall.'" '.implode(" ",$facparams).' ex:expression=".'.$this->encodePropertyName($facet).'" style="padding: 2px; float: left; width: 15em" ></div>';
								break;
							default:
								$facetstack[] = '
<div ex:role="facet" ex:showMissing="false" ex:collectionID="' . $collection . '" '.implode(" ",$facparams).' ex:expression=".'.$this->encodePropertyName($facet).'" style="padding: 2px; float: left; width: 15em" ></div>';
						}
						break;
					}
					$fieldcounter++;
				}
			}
		}

		//prepare views
		$viewcounter = 0;
		if(array_key_exists('views', $this->m_params)) $views = explode(',', $this->m_params['views']);
		else $views[] = 'tiles';

		$viewstack = array();
		$gmaps = array();
		foreach( $views as $view ){
			switch( strtolower(trim($view)) ){
				case 'thumbnail'://table view (the columns are automatically defined by the selected properties)
					$thstack = array();
					foreach ($res->getPrintRequests() as $pr){
						$thstack[] = ".".$this->encodePropertyName($pr->getLabel());
					}
					array_shift($thstack);
					array_unshift($thstack, '.labeltext');
					$viewstack[] = '
<div ex:role="view" ex:viewClass="Thumbnail" ex:label="Thumbnails" ex:collectionID="' . $collection . '" ex:showSummary="false" ></div>'; 
					break;
				case 'tabular'://table view (the columns are automatically defined by the selected properties)
					$thstack = array();
					foreach ($res->getPrintRequests() as $pr){
						$thstack[] = ".".$this->encodePropertyName($pr->getLabel());
					}
					array_shift($thstack);
					array_unshift($thstack, '.labeltext');
					$viewstack[] = '
<div ex:role="view" ex:viewClass="Tabular" ex:label="Table" ex:collectionID="' . $collection . '" ex:showSummary="false" ex:sortAscending="true" ex:columns="'.implode(',',$thstack).'" ></div>'; 
					break;
				case 'timeline'://timeline view
					$timeline = true;
					$exparams = array('start','end', 'proxy', 'colorkey'); //parameters expecting an Exhibit graph expression
					$usparams = array('timelineheight','topbandheight','bottombandheight','bottombandunit','topbandunit','topbandpixelsperunit'); //parametes expecting a textual or numeric value
					$tlparams = array();
					foreach($exparams as $param){
						if(array_key_exists($param, $this->m_params)) $tlparams[] = 'ex:'.$param.'=\'.'.$this->encodePropertyName($this->m_params[$param]).'\' ';
					}
					foreach($usparams as $param){
						if(array_key_exists($param, $this->m_params)) $tlparams[] = 'ex:'.$param.'=\''.$this->encodePropertyName($this->m_params[$param]).'\' ';
					}
					if(!array_key_exists('start', $this->m_params)){//find out if a start and/or end date is specified
						$dates = array();
						foreach ($res->getPrintRequests() as $pr){
							if($pr->getTypeID() == '_dat') {
								$dates[] = $pr;
								if(sizeof($dates) > 2) break;
							}
						}
						if(sizeof($dates) == 1){
							$tlparams[] = 'ex:start=\'.'.$this->encodePropertyName($dates[0]->getLabel()).'\' ';
						}
						else if(sizeof($dates) == 2){
							$tlparams[] = 'ex:start=\'.'.$this->encodePropertyName($dates[0]->getLabel()).'\' ';
							$tlparams[] = 'ex:end=\'.'.$this->encodePropertyName($dates[1]->getLabel()).'\' ';
						}
					}
					$viewstack[] = '
<div ex:role="view" ex:viewClass="Timeline" ex:label="Timeline" ex:eventLabel=".labeltext" ex:collectionID="' . $collection . '" ex:showSummary="false" '.implode(" ",$tlparams) . ' ></div>';
					break;
				case 'map'://map view
					if(isset($wgGoogleMapsKey)){
						$map = true;
						$exparams = array('latlng','colorkey');
						$usparams = array('type','center','zoom','size','scalecontrol','overviewcontrol','mapheight');
						$mapparams = array();
						foreach($exparams as $param){
							if(array_key_exists($param, $this->m_params)) $mapparams[] = 'ex:'.$param.'=\'.'.$this->encodePropertyName($this->m_params[$param]).'\' ';
						}
						foreach($usparams as $param){
							if(array_key_exists($param, $this->m_params)) $mapparams[] = 'ex:'.$param.'=\''.$this->encodePropertyName($this->m_params[$param]).'\' ';
						}
						if(!array_key_exists('latlng', $this->m_params)) {
							if(array_key_exists('locations', $this->m_params)) {
								$locs = explode(',', $this->m_params['locations']);
								foreach($locs as $loc) {
									$key = $this->encodePropertyName($loc);
									$label = str_replace('"', '\"', $key);
									$latlng = $label;
									if(!$geos[$key]) {
										$latlng .= 'LatLng';
										$gmaps[$key] = 'smwExhibitJSON.gmaps.push({ types: "' . $itemTypes . '", label: "' . $label . '", latlng: "' . $latlng . '" });';
									}
									$viewstack[] = '
<div ex:role="view" ex:viewClass="Map" ex:label="Map of ' . trim($loc) . '" ex:listKey=".labeltext" ex:collectionID="' . $collection . '" ex:showSummary="false" ex:latlng=".'.$latlng.'" ' . implode(" ", $mapparams) . ' > </div>';
								}
							} else { //find out if a geographic coordinate is available
								foreach ($res->getPrintRequests() as $pr){
									if($pr->getTypeID() == '_geo') {
										$mapparams[] = 'ex:latlng=\'.'.$this->encodePropertyName($pr->getLabel()).'\' ';
										$viewstack[] = '
<div ex:role="view" ex:viewClass="Map" ex:label="Map of ' . $pr->getLabel() . '" ex:listKey=".labeltext" ex:collectionID="' . $collection . '" ex:showSummary="false" '.implode(" ", $mapparams) . ' ></div>';
										break;
									}
								}
							}
						} else {
							$key = $this->encodePropertyName($this->m_params['latlng']);
							$label = str_replace('"', '\"', $key);
							$latlng = $label;
							if(!$geos[$key]) {
								$latlng .= 'LatLng';
								$gmaps[$key] = 'smwExhibitJSON.gmaps.push({ types: "' . $itemTypes . '", label: "' . $label . '", latlng: "' . $latlng . '" });';
							}
							$viewstack[] = '
<div ex:role="view" ex:viewClass="Map" ex:label="Map of ' . $this->m_params['latlng'] . '" ex:listKey=".labeltext" ex:collectionID="' . $collection . '" ex:showSummary="false" ex:latlng=".'.$latlng.'" '.implode(" ", $mapparams) . ' > </div>';
						}
					}
					break;
				case 'tiles'://tile view
				default:
					$viewstack[] = '
<div ex:role="view" ex:collectionID="' . $collection . '" ex:showSummary="false" > </div>';
					break;
			}
		}



		//prepare automatic lenses
		global $wgParser;
		$lenscounter = 0;
		$linkcounter = 0;

		if(array_key_exists('lens', $this->m_params)){//a customized lens is specified via the lens parameter within the query
			$lenstitle    = Title::newFromText("Template:".$this->m_params['lens']);
			$lensarticle  = new Article($lenstitle);
			$lenswikitext = $lensarticle->getContent();

			if(preg_match_all("/[\[][\[][{][{][{][1-9A-z\-[:space:]]*[}][}][}][\]][\]]/u",$lenswikitext,$matches)){
				foreach($matches as $match){
					foreach($match as $value){
						$strippedvalue = trim($value,"[[{}]]");
						$lenswikitext = str_replace($value,'<div class="inlines" id="linkcontent'.$linkcounter.'">'.$this->encodePropertyName(strtolower(str_replace("\n","",$strippedvalue))).'</div>',$lenswikitext);
						$linkcounter++;
					}
				}
			}

			if (preg_match_all("/[{][{][{][1-9A-z\-[:space:]]*[}][}][}]/u",$lenswikitext,$matches)) {
				foreach($matches as $match){
					foreach($match as $value){
						$strippedvalue = trim($value,"{}");
						$lenswikitext = str_replace($value,'<div class="inlines" id="lenscontent'.$lenscounter.'">'.$this->encodePropertyName(strtolower(str_replace("\n","",$strippedvalue))).'</div>',$lenswikitext);
						$lenscounter++;
					}
				}
			}

			$lenshtml = $wgParser->internalParse($lenswikitext);//$wgParser->parse($lenswikitext, $lenstitle, new ParserOptions(), true, true)->getText();

			$lenssrc = $lenshtml;
		} else {//generic lens (creates links to further content (property-pages, pages about values)
			foreach ($res->getPrintRequests() as $pr){
				if($pr->getTypeID() == '_wpg') {
					$prefix='';
					if($pr->getLabel()=='Category') $prefix = "Category:";
					$lensstack[] = '<tr ex:if-exists=".'.$this->encodePropertyName($pr->getLabel()).'">
					<td width="20%" style="white-space:nowrap;">'.$pr->getText(0, $this->mLinker).'</td>
					<td width="80%"><a ex:href-subcontent="'.$wgScriptPath.'/index.php?title='.$prefix.'{{.'.$this->encodePropertyName($pr->getLabel()).'}}" ex:content=".'.$this->encodePropertyName($pr->getLabel()).'"></a></td>
					</tr>';
				}
				else{
					$lensstack[] = '<tr ex:if-exists=".'.$this->encodePropertyName($pr->getLabel()).'">
					<td width="20%" style="white-space:nowrap;">'.$pr->getText(0, $this->mLinker).'</td>
					<td width="80%"><div ex:content=".'.$this->encodePropertyName($pr->getLabel()).'" class="name"></div></td>
					</tr>';
				}
			}
			array_shift($lensstack);
			$lenssrc = '
<table width="350px" cellpadding=3>
	<tr>
		<th class="head" align=left bgcolor="#DDDDDD"><a ex:href-subcontent="'.$wgScriptPath.'/index.php?title='.$this->determineNamespace(clone $res).'{{.labeltext}}" class="linkhead" ex:content=".labeltext"></a></th>
	</tr>
</table>
<table width="350px" cellpadding=3>'.implode("", $lensstack).'</table>';
		}

		$items = '';
		$index = 0;
		$locations = array();

		// print all result rows
		while ( $row = $res->getNext() ) {
			$col = 0;
			$textstack = array();
			foreach ($row as $field) {
				while ( ($object = $field->getNextObject()) !== false ) {
					switch($object->getTypeID()){
						case '_wpg':
							$tmp = $object->getText($outputmode,$this->getLinker(0));
							break;
						case '_geo':
							$tmp = version_compare(SMW_VERSION, '1.5', '>=') ? $object->getWikiValue() : $object->getXSDValue($outputmode,$this->getLinker(0));
							break;
						case '_num':
							$tmp = version_compare(SMW_VERSION, '1.5', '>=') ? $object->getWikiValue() : $object->getNumericValue($outputmode,$this->getLinker(0));
							break;
						case '_dat':
							$tmp = $object->getYear()."-".str_pad($object->getMonth(),2,'0',STR_PAD_LEFT)."-".str_pad($object->getDay(),2,'0',STR_PAD_LEFT)." ".$object->getTimeString();
							break;
						case '_uri':
							$tmp = version_compare(SMW_VERSION, '1.5', '>=') ? $object->getWikiValue() : $object->getXSDValue($outputmode,$this->getLinker(0));
							break;
						case '__sin':
							$tmp = $object->getShortText($outputmode,null);
							if(strpos($tmp,":")){
								$tmp = explode(":",$tmp,2);
								$tmp = $tmp[1];
							}
							break;
						default:
							$tmp = $object->getLongHTMLText($outputmode,$this->getLinker(0));
					}
					if($object->getTypeID() == '_num') {
						$textstack[] = '"' . $colstack[$col] . '": ' . str_replace('"', '\"', $tmp);
					} else {
						$textstack[] = '"' . $colstack[$col] . '": "' . str_replace('"', '\"', $tmp) . '"';
					}
					if(array_key_exists($colstack[$col], $gmaps)){$locations[$tmp] = $tmp;}
					if($col==0) $l = str_replace('"', '\"', $tmp);
				}
				$col ++;
			}
//			$idl = $index . '.' . $l;
//			if(strlen($idl)>30) $idl = substr($idl, 0, 27) . '...';
			$items .= 'smwExhibitJSON.items.push({type:"' .$itemTypes . '", label: "' . $index .'", ' . implode(', ', $textstack). '});' . "\n";
			$index ++;
		}

		$locationstr = array();
		if(count($locations) > 0) {
			global $srfpgIP ;
			require_once( $srfpgIP . '/includes/SRF_Storage.php' );
			$srfStore = SRFStorage::getDatabase();
			$locs = $srfStore->lookupLatLngs($locations);
			foreach ($locs as $loc) {
				$locationstr[] = 'smwExhibitJSON.latlngs.push({location:"' . str_replace('"', '\"', $loc['location']) . '", latlng: "' . $loc['latlng'] . '"});';
			}
		}
		
		global $wgOut;
		$wgOut->addScript('
<script type="text/javascript">
' . implode("\n", $gmaps) . '
' . implode("\n", $locationstr) . '
' . $properties . '
smwExhibitJSON.lens.push({id:"' . $collection . '_lens", content: "' . str_replace( "\n", '\n', str_replace( '"', '\"', $lenssrc) ) . '"});
' . $items .'
</script>');

		$result = '<div ex:role="collection" ex:itemTypes="' . $itemTypes . '" id="' . $collection . '"></div>
		<div class="top-facets"></div>
		<table width="100%" style="clear: both">
			<tr class="exhibit-content" valign="top">
				<td class="left-facets" width="0%"></td>
				<td class="view-content">
					<div class="exhibit-view-panel" ex:role="viewPanel">
						<div class="item" ex:role="lens" style="display: none;" id="' . $collection . '_lens">' . $lenssrc . '</div>
						' . implode("\n",$viewstack) . '
					</div>
				</td>
				<td class="right-facets" width="' . ((count($facetstack)>0)?20:0) . '%">' . implode("\n",$facetstack) . '</td>
			</tr>
		</table>
		<div class="bottom-facets"></div>';

		SRFExhibit::$exhibitRunningNumber++;

		// have to be viewed as HTML, no more parsing needed, otherwise, exhibit tags will be filtered
		return array($result, 'isHTML' => true);
	}
}
