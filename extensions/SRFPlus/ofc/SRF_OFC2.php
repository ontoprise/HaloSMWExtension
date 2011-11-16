<?php
/**
 * A query printer for multiple charts using the Open Flash Chart
 *
 * @note AUTOLOADED
 */

if( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

define('OFC_DEFAULT_WIDTH', 500);
define('OFC_MAX_WIDTH', 900);
class SRFOFC extends SMWResultPrinter {
	protected $m_width = 0;
	protected $m_height = 0;
	protected $m_charts = array();
	protected $m_label = '';
	protected $m_hidetable = false;
	protected $m_singlechart = false;
	protected $m_tabview = false;
	protected $m_notoolbar = false;
	protected $m_disableprov = true;

	protected $m_isAjax = false;
	
	function getParameters() {
        return array(
			array('name' => 'width', 'type' => 'int', 'description' => "Width of graphic"),
			array('name' => 'height', 'type' => 'int', 'description' => "Height of graphic"),
			array('name' => 'mainlabel', 'type' => 'string', 'description' => "Mainlabel"),
            array('name' => 'options', 'type' => 'string', 'description' => "Options")
			
		);
    }

    public static function registerResourceModules() {
		global $wgResourceModules, $srfgScriptPath;
		
		$moduleTemplate = array(
			'localBasePath' => dirname( __FILE__ ),
			'remoteBasePath' => $srfgScriptPath . '/ofc',
			'group' => 'ext.srf'
		);
		
		$wgResourceModules['ext.srf.ofc'] = $moduleTemplate + array(
			'scripts' => array( 'js/swfobject.js', 'ofc_render2.js' ),
			'styles' => array( 'css/ofc_style.css' ),
			'dependencies' => array(
		      'ext.jquery.query',
		      'ext.jquery.qtip',
		      'ext.smwhalo.json2',
		      'jquery.ui.dialog',
			)
		);
	}
    
    protected function includeJS() {
		SMWOutputs::requireHeadItem( SMW_HEADER_STYLE );

		// MediaWiki 1.17 introduces the Resource Loader.
		$realFunction = array( 'SMWOutputs', 'requireResource' );
		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
			SMWOutputs::requireResource( 'ext.srf.ofc' );
		}
		else {
			$this->setupOFCHeader();
		}		
	}
    
	function getScripts() {
		global $srfgScriptPath;
		$scripts=array();
		$scripts [] = '<script type="text/javascript" src="' . $srfgScriptPath . '/ofc/js/jquery.js"></script>' . "\n";
		$scripts [] = '<script type="text/javascript" src="' . $srfgScriptPath . '/ofc/js/jquery-ui-1.7.2.custom.min.js"></script>' . "\n";
		$scripts [] = '<script type="text/javascript" src="' . $srfgScriptPath . '/ofc/js/swfobject.js"></script>' . "\n";
		$scripts [] = '<script type="text/javascript" src="' . $srfgScriptPath . '/ofc/js/json2.js"></script>' . "\n";
//		$scripts [] = '<script type="text/javascript"> var flash_chart_path="' . $srfgScriptPath . '/ofc/open-flash-chart.swf";</script>' . "\n";
		$scripts [] = '<script type="text/javascript" src="' . $srfgScriptPath . '/ofc/ofc_render2.js"></script>' . "\n";
		return $scripts;
	}

	function getStylesheets() {
		global $srfgScriptPath;
		$css = array();
		$css[] = array(
            'rel' => 'stylesheet',
            'type' => 'text/css',
            'media' => "screen, projection",
            'href' => $srfgScriptPath . '/ofc/css/ofc_style.css'
            );
            $css[] = array(
            'rel' => 'stylesheet',
            'type' => 'text/css',
            'media' => "screen, projection",
            'href' => $srfgScriptPath . '/ofc/css/ui-lightness/jquery-ui-1.7.2.custom.css'
            );
            return $css;
	}

	function getSupportedParameters() {
		return $this->mParameters;
	}

	protected function readParameters($params,$outputmode) {
		SMWResultPrinter::readParameters($params,$outputmode);
		if (array_key_exists('width', $this->m_params)) {
			$this->m_width = $this->m_params['width'];
		}
		if (array_key_exists('height', $this->m_params)) {
			$this->m_height = $this->m_params['height'];
		} else {
			$this->m_height = $this->m_width * 0.6;
		}
		if (array_key_exists('mainlabel', $this->m_params)) {
			$this->m_label = $this->m_params['mainlabel'];
		}
		if (array_key_exists('ajaxcall', $this->m_params)) {
			$this->m_isAjax = true;
		}

		if(strpos(strtolower($this->mFormat), 'ofc-') === 0) {
			$this->m_singlechart = strtolower(substr($this->mFormat, 4));
			return;
		}

		if (array_key_exists('options', $this->m_params)) {
			$ops = explode(';', $this->m_params['options']);
			foreach($ops as $op) {
				$op = strtolower(trim($op));
				if('hidetable' == $op) {
					$this->m_hidetable = true;
				} else if('tabview' == $op) {
					$this->m_tabview = true;
				} else if('notoolbar' == $op) {
					$this->m_notoolbar = true;
				} else if('disableprov' == $op) {
					$this->m_disableprov = true;
				}
			}
		}
		$this->parseChartSettings('pie');
		$this->parseChartSettings('bar');
		$this->parseChartSettings('bar_3d');
		$this->parseChartSettings('line');
		$this->parseChartSettings('scatter_line');
	}
	private function parseChartSettings($type) {

		if(!array_key_exists($type, $this->m_params)) return;
		$ofc = explode(';', $this->m_params[$type]);

		foreach ($ofc as $c) {
			$arr = explode(',', $c);
			$width = $this->m_width;
			$height = $this->m_height;
			$show = false;
			$autoratio = false;
			for($i=2;$i<count($arr);++$i) {
				if(preg_match('/^(\d+)x(\d+)$/i', trim($arr[$i]), $m)) {
					$width = $m[1];
					$height = $m[2];
				} else if(strtolower(trim($arr[$i]))=='show') {
					$show = true;
				} else if(strtolower(trim($arr[$i]))=='autoratio') {
					$autoratio = true;
				}
			}
			$ys = explode('/', strtolower($arr[1]));
			$ratios = array();
			$yaxis = array();
			$ylabel = '';
			foreach($ys as $yIdx=>$y) {
				if($yIdx>0) $ylabel .= ' / ';
				$p = strrpos($y, '*');
				if($p>0 && !$autoratio) {
					$ratios[$yIdx] = floatval(substr($y, $p+1));
				} else {
					$ratios[$yIdx] = 1;
				}
				if($p!==false) {
					$s = trim(substr($y, 0, $p));
				} else {
					$s = trim($y);
				}
				$yaxis[$yIdx] = strtolower($s);
				$ylabel .= $s;
			}
			$this->m_charts[] = array(
				'show'=>$show, 
				'autoratio'=>$autoratio, 
				'type'=>$type, 
				'width'=>$width, 
				'height'=>$height, 
				'xlabel'=>trim($arr[0]), 
				'ylabel'=>$ylabel, 
				'x'=>strtolower(trim($arr[0])), 
				'y'=>$yaxis,
				'ratio'=>$ratios
			);
		}
	}
	static $ofc_color = array("#F65327","#000066","#428BC7","#EE1C2F");


	private function setupOFCHeader() {
		$i=0;
		foreach($this->getStylesheets() as $css) {
			SMWOutputs::requireHeadItem("ofc-css$i", '<link rel="stylesheet" type="text/css" href="' . $css['href'] . '" />');

			$i++;
		}
		$i=0;
		foreach($this->getScripts() as $script) {
//			if (defined('SMW_HALO_VERSION') && strpos($script, "jquery.js") !== false) continue; // don't include query twice
			SMWOutputs::requireHeadItem("ofc-script$i", $script);
			$i++;
		}
		SMWOutputs::requireHeadItem(SMW_HEADER_SORTTABLE);
	}

	protected function getResultText(SMWQueryResult $res, $outputmode) {
		global $smwgIQRunningNumber;
		$outputmode = SMW_OUTPUT_HTML;
//Bugfix 13446:		$this->isHTML = ($outputmode == SMW_OUTPUT_HTML); // yes, our code can be viewed as HTML if requested, no more parsing needed

        // if there is only one column in the results then stop right away
        if ($res->getColumnCount() == 1) return "";

		if (defined('SMW_UP_RATING_VERSION')) $result .= "UpRatingTable___".$smwgIQRunningNumber."___elbaTgnitaRpU";

		if (!$this->m_isAjax) {
			$this->includeJS();
		}
		$table_id = "querytable" . $smwgIQRunningNumber;

		// print header
		if ('broadtable' == $this->mFormat)
		$widthpara = ' width="100%"';
		else $widthpara = '';
		$table="";
		if (defined('SMW_UP_RATING_VERSION'))
		$table .= "UpRatingTable___".$smwgIQRunningNumber."___elbaTgnitaRpU";
		$table .= "<table class=\"smwtable\"$widthpara id=\"$table_id\" " . ($this->m_hidetable?'style="display:none"':'') . ">\n";
		if ($this->mShowHeaders != SMW_HEADERS_HIDE) { // building headers
			$table .= "\t<tr>\n";
			foreach ($res->getPrintRequests() as $pr) {
				$table .= "\t\t<th>" . $pr->getText($outputmode, ($this->mShowHeaders == SMW_HEADERS_PLAIN?NULL:$this->mLinker) ) . "</th>\n";
			}
			$table .= "\t</tr>\n";
		}

		$labels = array(); $headers = array();
		foreach ($res->getPrintRequests() as $pr) {
			$headers[] = $pr->getText(SMW_OUTPUT_HTML);
			$labels[] = strtolower($pr->getText(SMW_OUTPUT_HTML));
		}
		if($this->m_singlechart !== FALSE) {
			$l = $labels;
			if($this->m_singlechart == 'scatter_line') {
				$headers = array_slice($headers, 1);
				$l = array_slice($l, 1);
			}

			$ratios = array();
			foreach($l as $yIdx=>$y) {
				$ratios[$yIdx] = 1;
			}

			$this->m_charts[] = array(
				'show'=>true,
				'autoratio'=>false,			
				'type'=>$this->m_singlechart, 
				'width'=>$this->m_width, 
				'height'=>$this->m_height,
				'xlabel'=>$headers[0], 
				'ylabel'=>(($this->m_singlechart == 'pie')?$headers[1]:implode(' / ', array_slice($headers, 1))), 
				'x'=>$l[0], 
				'y'=>array_slice($l, 1),
				'ratio'=>array_slice($ratios,1));
		}

		// print all result rows
		$idx = 0;
		$tooltip = array();
		while ( $row = $res->getNext() ) {
			$table .= "\t<tr>\n";
			$firstcol = true;
			$index = 0;
			$tp = '';
			$provURL = '';
			foreach ($row as $field) {
				$table .= "\t\t<td>";
				$first = true;
				$data = '';

				while ( ($object = $field->getNextObject()) !== false ) {
					if ($object->getTypeID() == '_wpg') { // use shorter "LongText" for wikipage
						if($this->m_disableprov) {
							$text = $object->getLongText($outputmode,$this->getLinker($firstcol));
						} else {
                            $provURL = $object->getProvenance();
							if ($firstcol && !is_null($provURL)) {
								//$text = $this->createArticleLinkFromProvenance($provURL, $this->getLinker($firstcol));
								$text = $object->getLongText($outputmode,$this->getLinker($firstcol));
							} else {
								$text = $object->getLongText($outputmode,$this->getLinker($firstcol));
								if (strlen($text) > 0 && !is_null($provURL)) {
									$text .= $this->createProvenanceLink2($provURL);
								}
							}
						}
						$ofc_text = $object->getLongText($outputmode,$this->getLinker($firstcol));
					} else {
						$text = $object->getShortText($outputmode,$this->getLinker($firstcol));
						if(!$this->m_disableprov){
							if (strlen($text) > 0) {
								$provURL = $object->getProvenance();
								if (!is_null($provURL)) $text .= $this->createProvenanceLink2($provURL);
							}
						}
						$ofc_text = $object->getShortText($outputmode,$this->getLinker($firstcol));
					}
					if ($firstcol) {$rowname = $object->getShortText(SMW_OUTPUT_WIKI);}
					if($this->m_disableprov) {
						$provURL = '';
					} else {
						if (strlen($ofc_text) > 0) {
							$provURL = $object->getProvenance();
							if (!is_null($provURL)) {
								$provURL = $this->createProvenanceLink($provURL, $headers[0] . ':' . $rowname, $headers[$index], $ofc_text, $headers[$index].",".$rowname);
							} else {
								$provURL = '';
							}
						}
					}
					if ($first) {
						if ($object->isNumeric()) { // use numeric sortkey
							$table .= '<span class="smwsortkey">' . $object->getWikiValue() . '</span>';
						}
						// get first data only
						$data .= $object->getShortText(SMW_OUTPUT_WIKI);
						$first = false;
					} else {
						$table .= '<br />';
					}
					$table .= $text;
				}
				for($i=0;$i<count($this->m_charts);++$i) {
					$chart = $this->m_charts[$i];
					$label = $labels[$index];
					if($chart['type'] == 'pie') {
						if($chart['x'] == $label) {
							$this->m_charts[$i]['data'][$idx]['label'] = $data;
						} else if($chart['y'][0] == $label) {
							$v = floatval(str_replace(',', '', $data));
							$this->m_charts[$i]['data'][$idx]['value'] = $v;
							$this->m_charts[$i]['data'][$idx]['prov'] = $provURL;
						}
					} else {
						if($chart['x'] == $label) {
							$this->m_charts[$i]['data'][$idx]['label'] = $data;
							continue;
						}
						for($yIdx = 0;$yIdx<count($chart['y']);++$yIdx) {
							if($chart['y'][$yIdx] == $label) {
								$v = floatval(str_replace(',', '', $data));
								//								if($chart['autoratio']) {
								if(!isset($this->m_charts[$i]['minmax'][$yIdx])) {
									$this->m_charts[$i]['minmax'][$yIdx] = array( 'min'=>$v, 'max'=>$v);
								} else if($v<$this->m_charts[$i]['minmax'][$yIdx]['min']) {
									$this->m_charts[$i]['minmax'][$yIdx]['min'] = $v;
								} else if($v>$this->m_charts[$i]['minmax'][$yIdx]['max']) {
									$this->m_charts[$i]['minmax'][$yIdx]['max'] = $v;
								}
								//								} else {
								//									if(!isset($this->m_charts[$i]['min'])) {
								//										$this->m_charts[$i]['min'] = $v;
								//										$this->m_charts[$i]['max'] = $v;
								//									} else if($v<$this->m_charts[$i]['min']) {
								//										$this->m_charts[$i]['min'] = $v;
								//									} else if($v>$this->m_charts[$i]['max']) {
								//										$this->m_charts[$i]['max'] = $v;
								//									}
								//								}
								$this->m_charts[$i]['data'][$idx]['value'][$yIdx] = $v;
								$this->m_charts[$i]['data'][$idx]['prov'][$yIdx] = $provURL;
							}
						}
					}
				}
				$table .= "</td>\n";
				$firstcol = false;
				$tp .= $labels[$index] . ' : ' . implode(' ', preg_split('/<script>(.*?)<\/script>/i', $ofc_text)) . '<br>';
				$index ++;
			}
			$tooltip[] = $tp;
			$table .= "\t</tr>\n";
			$idx ++;
		}
		// print further results footer
		if ( $this->linkFurtherResults($res) ) {
			$link = $res->getQueryLink();
			if ($this->getSearchLabel($outputmode)) {
				$link->setCaption($this->getSearchLabel($outputmode));
			}
			$table .= "\t<tr class=\"smwfooter\"><td class=\"sortbottom\" colspan=\"" . $res->getColumnCount() . '"> ' . $link->getText($outputmode,$this->mLinker) . "</td></tr>\n";
		}
		$table .= "</table>\n"; // print footer

		$this->getAutoRatio();
		$this->enableRatio();

		$ofc_data_objs = array();
		for($i=0;$i<count($this->m_charts);++$i) {
			$chart = $this->m_charts[$i];

			$id = "ofc" . $smwgIQRunningNumber . "_" . $i;
			$this->m_charts[$i]['id'] = $id;
			$ofc_data_objs[$i] = $id . ":{show:" . ($this->m_charts[$i]['show']?"true":"false") . ", data:{";

			if($chart['type'] == 'pie') {
				$ofc_data_objs[$i] .= '
		"elements":[
			{
				"type":"pie",
				"values":[';
				$first = true;
				foreach($chart['data'] as $d) {
					if(!$first) {
						$ofc_data_objs[$i] .= ',';
					} else {
						$first = false;
					}
					$ofc_data_objs[$i] .= '{
						"value":' . str_replace(',','',$d['value']) . ',
						"label":"' . str_replace('"','\"',$d['label']). ':' . $d['value'] . '",
						"on-click": "' . $d['prov'] . '"
					}';
				}
				$ofc_data_objs[$i] .= '
				],
				"colours":["#428BC7","#EE1C2F"],
				"gradient-fill":true,
				"start-angle":35
			}
		],
		"title":{"text":"' . str_replace('"', '\"', $chart['xlabel'] . ' - ' . $chart['ylabel']) . '"},';
			} else {
				$max = floatval($chart['max']);
				$min = floatval($chart['min']);
				$step = floatval($chart['step']);
				//				$this->getScale($min, $max, $step);

				$ofc_data_objs[$i] .= '
		"elements":[' . $this->getElementText($chart, $chart['type'], $tooltip) . '],
		"title":{"text":"' . str_replace('"', '\"', $chart['xlabel'] . ' - ' . $chart['ylabel']) . '"},
		"x_axis":{';
				if($chart['type'] == 'scatter_line') {
					$first = true;
					foreach($chart['data'] as $data) {
						$v = floatval(str_replace(',', '', $data['label']));
						if($first) {
							$maxx = $v;
							$minx = $v;
							$first = false;
						} else {
							if($v>$maxx) $maxx = $v;
							if($v<$minx) $minx = $v;
						}
					}
					$this->getScale($minx, $maxx, $stepx);
					$ofc_data_objs[$i] .= '"min": ' . $minx . ', "max": ' . $maxx . ', "steps": ' . $stepx . '';
				} else {
					$ofc_data_objs[$i] .= '
			"tick-height": 10,
			"colour":"#E2E2E2",
			"grid-colour":"#E2E2E2",
			"labels":{
				"labels":[' . $this->getLabelText($chart) . ']
			}';
				}
				$ofc_data_objs[$i] .= '
		},
		"x_legend":{
			"text":"' . $chart['xlabel'] . '",
			"style":"{font-size: 12px; color: #000033}"
		},
		"y_axis":{
			"colour":"#000066",
			"grid-colour":"#E2E2E2",
			"max":' . $max . ',
			"min":' . $min . ',
			"steps":' . $step . '
		},';
			}
			$ofc_data_objs[$i] .= '"bg_colour":"#ffffff"}}';
		}
		$html = "";
		if($this->m_singlechart === FALSE && !$this->m_notoolbar) {
			$html = '<div class="show_hide_container"><div class="ofc_title">' . $this->m_label . '</div>';
			foreach($this->m_charts as $chart) {
				if($chart['x'] != $labels[0]) {
					$label = $chart['xlabel'] . ' - ' . $chart['ylabel'];
				} else {
					$label = $chart['ylabel'];
				}
				$html .= '<a href="##" id="show_hide_flash_div_' . $chart['id'] . '" class="ofc_' . $chart['type'] . '_link">' .
				($chart['show']?('<b>' . $label . '</b>'):$label) . '</a>';
			}
			$html .= '<a href="##" id="show_hide_table_' . $table_id . '" class="ofc_table_link">' . ($this->m_hidetable?'Show':'Hide') . ' table</a>
				</div>';
			$html .= $table;
		}
		$first = true;

		foreach($this->m_charts as $chart) {
			if ($chart['width']==0) {
				$chart['width'] = (($idx * 50 < OFC_DEFAULT_WIDTH) ? OFC_DEFAULT_WIDTH : $idx * 50);
				if($chart['width'] > OFC_MAX_WIDTH) $chart['width'] = OFC_MAX_WIDTH;
			}
			if ($chart['height']==0) $chart['height'] = $chart['width'] * 0.6;
			$html .= '<div id="div_' . $chart['id'] . '" class="ui-widget-content" style="width:' . $chart['width'] . 'px;height:' . $chart['height'] . 'px"><div id="' . $chart['id'] . '">';
			if($chart['show'] && $first) {
				$html .= 'You do not have flash installed - please go to: <a href="http://get.adobe.com/flashplayer/">http://get.adobe.com/flashplayer/</a> to install it';
				$first = false;
			}
			$html .= '</div></div>';
		}

		$js = 'ofc_data_objs.push({' . implode(',', $ofc_data_objs) . '});';
		if($this->m_singlechart === FALSE) {
			if($this->m_tabview) {
				$js .= 'jQuery(function() {';
				foreach($this->m_charts as $chart) {
					$js .= 'jQuery("#show_hide_flash_div_' . $chart['id'] . '").click(document.ofc.js.tabChart);';
				}
				$js .= '});';
			} else {
				$js .= 'jQuery(function() {';
				foreach($this->m_charts as $chart) {
					$js .= 'jQuery("#show_hide_flash_div_' . $chart['id'] . '").click(document.ofc.js.showHideChart);';
				}
				$js .= '});';
			}
		}
		if (!$this->m_isAjax) {
			SMWOutputs::requireHeadItem("srfofc$smwgIQRunningNumber", '<script type="text/javascript">if(typeof(ofc_data_objs)=="undefined") ofc_data_objs = [];' . $js . '</script>' . "\n");
		}
		return !$this->m_isAjax ? $html : $html . '|||' . $js;

	}
	private function getLabelText($chart) {
		$first = true;
		$text = "";
		foreach($chart['data'] as $d) {
			if(!$first) {
				$text .= ',';
			} else {
				$first = false;
			}
			if (isset($d['label'])) $label = $d['label']; else $label = '';
			$text .= '{ "text":"' . str_replace('"','\"',$label). '", "rotate": 315 }';
		}
		return $text;
	}
	private function getElementText($chart, $type, $tooltip) {
		$text = '';
		$first = true;
		for($yIdx = 0;$yIdx<count($chart['y']);++$yIdx) {
			if(!$first) {
				$text .= ',';
			} else {
				$first = false;
			}

			$text .= '{
				"type":"' . $type . '",
				"alpha":1,
				"dot-style": { "type": "hollow-dot", "dot-size": 3, "halo-size": 2 }, 
				"values":[';
			if($type == 'scatter_line') {
				$scatter = array();
				$i = 0;
				foreach($chart['data'] as $d) {
					$x = floatval(str_replace(',','',$d['label']));
					$scatter[floatval($x)][] = '{
						"x": ' . $x . ', 
						"y": ' . str_replace(',','',$d['value'][$yIdx]) . ', 
						"tip": "' . str_replace('"','\"',$tooltip[$i++]) . '"
					}';
				}
				ksort($scatter);
				$f = true;
				foreach($scatter as $s) {
					if(!$f) {
						$text .= ',';
					} else {
						$f = false;
					}
					$text .= implode(',', $s);
				}
			} else {
				$f = true;
				$i = 0;
				foreach($chart['data'] as $d) {
					if(!$f) {
						$text .= ',';
					} else {
						$f = false;
					}
					$text .= '{
						"' . ($type=='line'?'y':'top') . '": ' . str_replace(',','',$d['value'][$yIdx]) . ', 
						"tip": "' . str_replace('"','\"',$tooltip[$i++]) . '",
						"on-click": "' . $d['prov'][$yIdx] . '"
					}';
				}
			}
			$text .= '],
				"colour":"' . SRFOFC::$ofc_color[$yIdx%4] . '",
				"text":"' . $chart['y'][$yIdx] . (($chart['ratio'][$yIdx]!=1 && $chart['ratio'][$yIdx]>0)?(' (*' . $chart['ratio'][$yIdx] . ')'):'') . '",
				"fill":"transparent"
			}';
		}
		return $text;
	}
	private function getScale(&$min, &$max, &$step) {
		if($min >= 0) {
			$min = 0;
			if($max == 0) {
				$step = 0;
			} else {
				if($max > 1) {
					$step = 1;
					while(intval($max / $step) > 10) {
						$step *= 10;
					}
				} else {
					$step = 0.1;
					while(floatval($max / $step) < 1) {
						$step /= 10;
					}
				}
				$max = (intval($max / $step) + 1) * $step;
			}
		} else if($max <= 0) {
			$max = 0;
			if($min < -1) {
				$step = 1;
				while(intval($min / $step) < -10) {
					$step *= 10;
				}
			} else {
				$step = 0.1;
				while(floatval($min / $step) > -1) {
					$step /= 10;
				}
			}
			$min = (intval($min / $step) - 1) * $step;
		} else {
			if($min < -1) {
				$step1 = 1;
				while(intval($min / $step1) < -10) {
					$step1 *= 10;
				}
			} else {
				$step1 = 0.1;
				while(floatval($min / $step1) > -1) {
					$step1 /= 10;
				}
			}
			if($max > 1) {
				$step2 = 1;
				while(intval($max / $step2) > 10) {
					$step2 *= 10;
				}
			} else {
				$step2 = 0.1;
				while(floatval($max / $step2) < 1) {
					$step2 /= 10;
				}
			}
			if($step2>$step1) $step = $step2; else $step = $step1;
			$max = (intval($max / $step) + 1) * $step;
			$min = (intval($min / $step) - 1) * $step;
		}
	}
	private function getAutoRatio() {
		// get auto ratio data
		for($i=0;$i<count($this->m_charts);++$i) {
			$chart = $this->m_charts[$i];
			if($chart['type'] == 'pie') continue;
			if (array_key_exists('minmax', $chart)) {
				foreach($chart['minmax'] as $yIdx=>$v) {
					$max = floatval($v['max']);
					$min = floatval($v['min']);
					$this->getScale($min, $max, $step);
					$this->m_charts[$i]['minmax'][$yIdx]['min'] = $min;
					$this->m_charts[$i]['minmax'][$yIdx]['max'] = $max;
					$chart['minmax'][$yIdx]['step'] = $step;
				}
			}
			if(!$chart['autoratio']) continue;
			$maxstep = 0;
			foreach($chart['minmax'] as $yIdx=>$v) {
				if($v['step']>$maxstep) $maxstep = $v['step'];
			}
			foreach($chart['minmax'] as $yIdx=>$v) {
				$ratio = intval($maxstep / $v['step']);
				$this->m_charts[$i]['ratio'][$yIdx] = $ratio;
			}
		}
	}
	private function enableRatio() {
		// enable ratio to ofc data
		for($i=0;$i<count($this->m_charts);++$i) {
			$chart = $this->m_charts[$i];
			if($chart['type'] == 'pie') continue;
			foreach($chart['ratio'] as $yIdx=>$ratio) {
				$max = $chart['minmax'][$yIdx]['max'];
				$min = $chart['minmax'][$yIdx]['min'];
				if($ratio != 1 && $ratio>0) {
					foreach($chart['data'] as $did=>$val) {
						$this->m_charts[$i]['data'][$did]['value'][$yIdx] *= $ratio;
					}
					$min = $min * $ratio;
					$max = $max * $ratio;
				}
				if(!isset($this->m_charts[$i]['min'])) {
					$this->m_charts[$i]['min'] = $min;
					$this->m_charts[$i]['max'] = $max;
				} else {
					if($min<$this->m_charts[$i]['min']) {
						$this->m_charts[$i]['min'] = $min;
					}
					if($max>$this->m_charts[$i]['max']) {
						$this->m_charts[$i]['max'] = $max;
					}
				}
			}
			$min = $this->m_charts[$i]['min'];
			$max = $this->m_charts[$i]['max'];
			$this->getScale($min, $max, $step);
			if($min+$step == $this->m_charts[$i]['min']) $min = $this->m_charts[$i]['min'];
			if($max-$step == $this->m_charts[$i]['max']) $max = $this->m_charts[$i]['max'];
			$this->m_charts[$i]['min'] = $min;
			$this->m_charts[$i]['max'] = $max;
			$this->m_charts[$i]['step'] = $step;
		}
	}
	private function createProvenanceLink($url, $row, $col, $value, $cellIdent) {
		global $uprgWikipediaAPI;
		// no provenance link, if there is "novalue" placeholder from OB and if UP rating is not installed
		if ($url != 'http://novalue#0' && defined('SMW_UP_RATING_VERSION')) {
			$url=str_replace('&amp;', '&', $url);
			// hack for Wikipedia clone
			if (strpos($uprgWikipediaAPI, 'vulcan.com')) {
				$url = str_replace('en.wikipedia.org/wiki/', 'wiking.vulcan.com/wp/index.php,title=', $url);
				$url = preg_replace('/relative-line=\d+/', '', $url);
				$url = preg_replace('/absolute-line=(\d+)/', 'line=$1', $url);
				$url = preg_replace('/#[^\?&]*(\?|&){1}/', '', $url);
				$url = str_replace('#', '&', $url);
				$url = str_replace('?', '&', $url);
				$url = str_replace('index.php,title=', 'index.php?title=', $url);
			}
			global $smwgIQRunningNumber;
			return "uprgPopup.cellDataRating({$smwgIQRunningNumber}, '{$row}','{$col}','{$value}','{$cellIdent}', \'$url\')";
		}
	}
	private function createProvenanceLink2($url) {
		global $uprgWikipediaAPI;
		// no provenance link, if there is "novalue" placeholder from OB and if UP rating is not installed
		if ($url != 'http://novalue#0' && defined('SMW_UP_RATING_VERSION')) {
			$url=str_replace('&amp;', '&', $url);
			// hack for Wikipedia clone
			if (strpos($uprgWikipediaAPI, 'vulcan.com')) {
				$url = str_replace('en.wikipedia.org/wiki/', 'wiking.vulcan.com/wp/index.php,title=', $url);
				$url = preg_replace('/relative-line=\d+/', '', $url);
				$url = preg_replace('/absolute-line=(\d+)/', 'line=$1', $url);
				$url = preg_replace('/#[^\?&]*(\?|&){1}/', '', $url);
				$url = str_replace('#', '&', $url);
				$url = str_replace('?', '&', $url);
				$url = str_replace('index.php,title=', 'index.php?title=', $url);
			}
			return "UpRatingCell___".$url."___lleCgnitaRpU";
		}
	}

	/**
	 * Creates article link for instances of first column by using provenance data.
	 *
	 * @param string $url Provenance URL
	 * @param Linker $linker
	 * @return string (wiki markup)
	 */
	private function createArticleLinkFromProvenance($url, $linker) {
		$url_parts = parse_url($url);
		$desturl = $url_parts['path'];
		$title = substr($desturl, strrpos($desturl, "/")+1);
		$title = str_replace("_", " ", $title);
		$wpurl = str_replace('en.wikipedia.org/wiki/', 'wiking.vulcan.com/wp/index.php?title=', $url);
		return '['.$wpurl.' '.$title.']';

	}
}