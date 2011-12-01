<?php
/**
 * A query printer for multiple charts using the Open Flash Chart
 *
 * @note AUTOLOADED
 */

if( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

global $srfpgIP;
include_once($srfpgIP . '/Group/SRF_GroupResultPrinter.php');

class SRFGroupOFC extends SRFGroupResultPrinter {
	protected $m_width = 0;
	protected $m_height = 0;
	protected $m_charts = array();
	protected $m_label = '';
	protected $m_hidetable = false;
	protected $m_notable = false;
	protected $m_singlechart = false;
	protected $m_tabview = false;
	protected $m_notoolbar = false;
	
	protected $m_isAjax = false;
	public function __construct($format, $inline) {
		parent::__construct($format, $inline);
	}

    protected function includeJS() {
    	SMWOutputs::requireHeadItem( SMW_HEADER_STYLE );
    	
		// MediaWiki 1.17 introduces the Resource Loader.
		$realFunction = array( 'SMWOutputs', 'requireResource' );
		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
			global $wgOut;
			$wgOut->addModules( 'ext.srf.ofc' );
		}
		else {
			$this->setupOFCHeader();
		}
	}
    
	function getScripts() {
		global $srfpgScriptPath;
		$scripts=array();
		$scripts [] = '<script type="text/javascript" src="' . $srfpgScriptPath . '/ofc/js/jquery.js"></script>' . "\n";
		$scripts [] = '<script type="text/javascript" src="' . $srfpgScriptPath . '/ofc/js/jquery-ui-1.7.2.custom.min.js"></script>' . "\n";
		$scripts [] = '<script type="text/javascript" src="' . $srfpgScriptPath . '/ofc/js/swfobject.js"></script>' . "\n";
		$scripts [] = '<script type="text/javascript" src="' . $srfpgScriptPath . '/ofc/js/json2.js"></script>' . "\n";
//		$scripts [] = '<script type="text/javascript"> var flash_chart_path="' . $srfpgScriptPath . '/ofc/open-flash-chart.swf";</script>' . "\n";
		$scripts [] = '<script type="text/javascript" src="' . $srfpgScriptPath . '/ofc/ofc_render.js"></script>' . "\n";
		return $scripts;
	}

	function getStylesheets() {
		global $srfpgScriptPath;
		$css = array();
		$css[] = array(
			'rel' => 'stylesheet',
			'type' => 'text/css',
			'media' => "screen, projection",
			'href' => $srfpgScriptPath . '/ofc/css/ofc_style.css'
			);
		$css[] = array(
			'rel' => 'stylesheet',
			'type' => 'text/css',
			'media' => "screen, projection",
			'href' => $srfpgScriptPath . '/ofc/css/ui-lightness/jquery-ui-1.7.2.custom.css'
			);

		return $css;
	}
	
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
	
	function getSupportedParameters() {
		return $this->mParameters;
	}

	protected function readParameters($params,$outputmode) {
		SRFGroupResultPrinter::readParameters($params,$outputmode);
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
		if(strpos(strtolower($this->mFormat), 'group ofc-') === 0) {
			$this->m_singlechart = strtolower(substr($this->mFormat, 10));
			return;
		}

		if (array_key_exists('options', $this->m_params)) {
			$ops = explode(';', $this->m_params['options']);
			foreach($ops as $op) {
				$op = strtolower(trim($op));
				if('hidetable' == $op) {
					$this->m_hidetable = true;
				} else if('notable' == $op) {
					$this->m_notable = true;
				} else if('tabview' == $op) {
					$this->m_tabview = true;
				} else if('notoolbar' == $op) {
					$this->m_notoolbar = true;
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


	protected function getResultText($res, $outputmode) {
		global $smwgIQRunningNumber;
		$outputmode = SMW_OUTPUT_HTML;
		$this->isHTML = ($outputmode == SMW_OUTPUT_HTML); // yes, our code can be viewed as HTML if requested, no more parsing needed

		$this->includeJS();
		$table_id = "querytable" . $smwgIQRunningNumber;

//		$linker = $this->mLinker;
//		$this->mLinker = NULL;
		$result_rows = $this->getGroupResult($res, $outputmode, $headers);
//		$this->mLinker = $linker;
		
		// print header
		if ('broadtable' == $this->mFormat)
		$widthpara = ' width="100%"';
		else $widthpara = '';
		$table="";
		$table .= "<table class=\"smwtable\"$widthpara id=\"$table_id\" " . ($this->m_hidetable?'style="display:none"':'') . ">\n";
		if ($this->mShowHeaders != SMW_HEADERS_HIDE) { // building headers
			$table .= "\t<tr>\n";
			foreach ($headers as $h) {
				$table .= "\t\t<th>" . $h . "</th>\n";
			}
			$table .= "\t</tr>\n";
		}


		$hs = array();
		foreach ($headers as $h) {
			$hs[] = preg_replace('/\<[^\>]*\>/', '', $h);
		}
		$headers = $hs;
		$labels = array();
		foreach ($headers as $h) {
			$labels[] = strtolower($h);
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
		foreach($result_rows as $key => $row) {
			$table .= "\t<tr>\n";
			$index = 0;
			$tp = '';

				$table .= "\t\t<td>";
				$first = true;
				$data = $key;
				$table .= $data;
				$provURL = '';
				if ($row[0]->getTypeID() == '_wpg') $provURL = $row[0]->getTitle()->getFullURL();
				$ofc_text = $data;
				if(is_string($data)) $data = preg_replace('/\<[^\>]*\>/', '', $data);
				
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
				$tp .= ($index==0 ? "" : ($labels[$index] . ' : ')) . implode(' ', preg_split('/<script>(.*?)<\/script>/i', $ofc_text)) . '<br>';
				$index ++;
			
			for($j = 1;$j<count($row);++$j) {
				$field = $row[$j];
				$table .= "\t\t<td>";
				$data = $field->getResult($outputmode);
				$table .= $data;
				
				$ofc_text = $data;
				if(is_string($data)) $data = preg_replace('/\<[^\>]*\>/', '', $data);
				
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
				if(is_array($chart['data'])) {
					foreach($chart['data'] as $d) {
						if(!$first) {
							$ofc_data_objs[$i] .= ',';
						} else {
							$first = false;
						}
						if(!$d['value']) $d['value'] = '1';
						$ofc_data_objs[$i] .= '{
							"value":' . str_replace(',','',$d['value']) . ',
							"label":"' . str_replace('"','\"',$d['label']). ':' . $d['value'] . '",
							"on-click": "' . $d['prov'] . '"
						}';
					}
				}
				$ofc_data_objs[$i] .= '
				],
				"colours":["#428BC7","#EE1C2F","#000066"' . (((count($chart['data'])%2)==0)?',"#006600"':'') . '],
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
			if(!$this->m_notable) {
				$html .= '<a href="##" id="show_hide_table_' . $table_id . '" class="ofc_table_link">' . ($this->m_hidetable?'Show':'Hide') . ' table</a>
					</div>';
				$html .= $table;
			} else {
				$html .= '</div>';
			}
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

		$js = 'ofc_data_objs.data.push({' . implode(',', $ofc_data_objs) . '});';
		if($this->m_singlechart === FALSE) {
			if($this->m_tabview) {
				foreach($this->m_charts as $chart) {
					$js .= 'ofc_data_objs.tabs.push("#show_hide_flash_div_' . $chart['id'] . '");';
				}
			} else {
				foreach($this->m_charts as $chart) {
					$js .= 'ofc_data_objs.showhide.push("#show_hide_flash_div_' . $chart['id'] . '");';
				}
			}
		}

		global $wgOut;
		$wgOut->addScript('<script type="text/javascript">' . $js . '</script>' . "\n");
		
		return $html;
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
			if(!$chart['minmax']) continue;
			foreach($chart['minmax'] as $yIdx=>$v) {
				$max = floatval($v['max']);
				$min = floatval($v['min']);
				$this->getScale($min, $max, $step);
				$this->m_charts[$i]['minmax'][$yIdx]['min'] = $min;
				$this->m_charts[$i]['minmax'][$yIdx]['max'] = $max;
				$chart['minmax'][$yIdx]['step'] = $step;
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
}
