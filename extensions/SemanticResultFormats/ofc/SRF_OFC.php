<?php
/**
 * A query printer for pie charts using the Google Chart API
 *
 * @note AUTOLOADED
 */

if( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

class SRFOFC extends SMWResultPrinter {
	protected $m_width = 500;
	protected $m_height = 300;
	protected $m_charts = array();
	protected $m_label = '';
	protected $m_hidetable = false;
	protected $m_singlechart = false;

	protected $m_isAjax = false;
	public function __construct($format, $inline) {
		parent::__construct($format, $inline);
		$width = new SMWQPParameter('width', 'Width', '<number>', NULL, "Width of graphic");
		$height = new SMWQPParameter('height', 'Height', '<number>', NULL, "Height of graphic");
		$mainlabel = new SMWQPParameter('mainlabel', 'Mainlabel', '<string>', NULL, "Mainlabel");

		$this->mParameters[] = $width;
		$this->mParameters[] = $height;
		$this->mParameters[] = $mainlabel;
	}

	function getScripts() {
		global $srfgScriptPath;
		$scripts [] = '<script type="text/javascript" src="' . $srfgScriptPath . '/ofc/js/jquery.js"></script>' . "\n";
		$scripts [] = '<script type="text/javascript" src="' . $srfgScriptPath . '/ofc/js/jquery-ui-1.7.2.custom.min.js"></script>' . "\n";
		$scripts [] = '<script type="text/javascript" src="' . $srfgScriptPath . '/ofc/js/swfobject.js"></script>' . "\n";
		$scripts [] = '<script type="text/javascript" src="' . $srfgScriptPath . '/ofc/js/json2.js"></script>' . "\n";
		$scripts [] = '<script type="text/javascript"> var flash_chart_path="' . $srfgScriptPath . '/ofc/open-flash-chart.swf";</script>' . "\n";
		$scripts [] = '<script type="text/javascript" src="' . $srfgScriptPath . '/ofc/ofc_render.js"></script>' . "\n";
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

		if (array_key_exists('hidetable', $this->m_params)) {
			$this->m_hidetable = $this->m_params['hidetable'];
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
			for($i=2;$i<count($arr);++$i) {
				if(preg_match('/^(\d+)x(\d+)$/i', trim($arr[$i]), $m)) {
					$width = $m[1];
					$height = $m[2];
				} else if(strtolower(trim($arr[$i]))=='show') {
					$show = true;
				}
			}
			$this->m_charts[] = array('show'=>$show, 'type'=>$type, 'width'=>$width, 'height'=>$height, 'xlabel'=>trim($arr[0]), 'ylabel'=>trim($arr[1]), 'x'=>strtolower(trim($arr[0])), 'y'=>explode('/', strtolower(trim($arr[1]))));
		}
	}
	static $ofc_color = array("#F65327","#000066","#428BC7","#EE1C2F");
	

    private function setupOFCHeader() {
            global $wgParser;
            $i=0;
            foreach($this->getStylesheets() as $css) {
                $wgParser->getOutput()->addHeadItem('<link rel="stylesheet" type="text/css" href="' . $css['href'] . '" />', "ofc-css$i");
                
                $i++;
            }
            $i=0;
            foreach($this->getScripts() as $script) {
                $wgParser->getOutput()->addHeadItem($script,"ofc-script$i");    
                $i++;
            }
            SMWOutputs::requireHeadItem(SMW_HEADER_SORTTABLE);
    }

	protected function getResultText($res, $outputmode) {
		global $smwgIQRunningNumber;
		$outputmode = SMW_OUTPUT_HTML;
		$this->isHTML = ($outputmode == SMW_OUTPUT_HTML); // yes, our code can be viewed as HTML if requested, no more parsing needed

		if (!$this->m_isAjax) {
			$this->setupOFCHeader();
		}
		$table_id = "querytable" . $smwgIQRunningNumber;

		// print header
		if ('broadtable' == $this->mFormat)
		$widthpara = ' width="100%"';
		else $widthpara = '';
		$table = "<table class=\"smwtable\"$widthpara id=\"$table_id\" " . ($this->m_hidetable?'style="display:none"':'') . ">\n";
		if ($this->mShowHeaders) { // building headers
			$table .= "\t<tr>\n";
			foreach ($res->getPrintRequests() as $pr) {
				$table .= "\t\t<th>" . $pr->getText($outputmode, $this->mLinker) . "</th>\n";
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
			$this->m_charts[] = array('show'=>true, 'type'=>$this->m_singlechart, 'width'=>$this->m_width, 'height'=>$this->m_height,
				'xlabel'=>$headers[0], 'ylabel'=>(($this->m_singlechart == 'pie')?$headers[1]:implode(' / ', array_slice($headers, 1))), 
				'x'=>$l[0], 'y'=>array_slice($l, 1));
		}

		// print all result rows
		$idx = 0;
		$tooltip = array();
		while ( $row = $res->getNext() ) {
			$table .= "\t<tr>\n";
			$firstcol = true;
			$index = 0;
			$tp = '';
			foreach ($row as $field) {
				$table .= "\t\t<td>";
				$first = true;
				$data = '';
				while ( ($object = $field->getNextObject()) !== false ) {
					if ($object->getTypeID() == '_wpg') { // use shorter "LongText" for wikipage
						$text = $object->getLongText($outputmode,$this->getLinker($firstcol));
					} else {
						$text = $object->getShortText($outputmode,$this->getLinker($firstcol));
					}
					if ($first) {
						if ($object->isNumeric()) { // use numeric sortkey
							$table .= '<span class="smwsortkey">' . $object->getNumericValue() . '</span>';
						}
						// get first data only
						$data .= $object->getShortText($outputmode);
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
							$v = intval(str_replace(',', '', $data));
							if(!isset($this->m_charts[$i]['min']) || $v<$this->m_charts[$i]['min']) {
								$this->m_charts[$i]['min'] = $v;
							}
							if(!isset($this->m_charts[$i]['max']) || $v>$this->m_charts[$i]['max']) {
								$this->m_charts[$i]['max'] = $v;
							}
							$this->m_charts[$i]['data'][$idx]['value'] = $v;
						}
					} else {
						if($chart['x'] == $label) {
							$this->m_charts[$i]['data'][$idx]['label'] = $data;
							continue;
						}
						for($yIdx = 0;$yIdx<count($chart['y']);++$yIdx) {
							if($chart['y'][$yIdx] == $label) {
								$v = intval(str_replace(',', '', $data));
								if(!isset($this->m_charts[$i]['min']) || $v<$this->m_charts[$i]['min']) {
									$this->m_charts[$i]['min'] = $v;
								}
								if(!isset($this->m_charts[$i]['max']) || $v>$this->m_charts[$i]['max']) {
									$this->m_charts[$i]['max'] = $v;
								}
								$this->m_charts[$i]['data'][$idx]['value'][$yIdx] = $v;
							}
						}
					}
				}
				$table .= "</td>\n";
				$firstcol = false;
				$tp .= $labels[$index] . ' : ' . $text . '<br>';
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
					$ofc_data_objs[$i] .= '{"value":' . str_replace(',','',$d['value']) . ',"label":"' . str_replace('"','\"',$d['label']). ':' . $d['value'] . '"}';
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
				$max = intval($chart['max']);
				$min = intval($chart['min']);
				$this->getScale($min, $max, $step);

				$ofc_data_objs[$i] .= '
		"elements":[' . $this->getElementText($chart, $chart['type'], $tooltip) . '],
		"title":{"text":"' . str_replace('"', '\"', $chart['xlabel'] . ' - ' . $chart['ylabel']) . '"},
		"x_axis":{';
				if($chart['type'] == 'scatter_line') {
					$first = true;
					foreach($chart['data'] as $data) {
						$v = intval(str_replace(',', '', $data['label']));
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
		if($this->m_singlechart === FALSE) {
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
			$html .= '<div id="div_' . $chart['id'] . '" class="ui-widget-content" style="width:' . $chart['width'] . 'px;height:' . $chart['height'] . 'px"><div id="' . $chart['id'] . '">';
			if($chart['show'] && $first) {
				$html .= 'You do not have flash installed - please go to: <a href="http://get.adobe.com/flashplayer/">http://get.adobe.com/flashplayer/</a> to install it';
				$first = false;
			}
			$html .= '</div></div>';
		}

		if (!$this->m_isAjax) {
			global $wgParser;
			$wgParser->getOutput()->addHeadItem('<script type="text/javascript">ofc_data_objs.push({' . implode(',', $ofc_data_objs) . '});</script>' . "\n");
		
		}
		return !$this->m_isAjax ? $html : $html . '|||ofc_data_objs.push({' . implode(',', $ofc_data_objs) . '});' ;
		
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
					$x = str_replace(',','',$d['label']);
					$scatter[floatval($x)][] = '{"x": ' . $x . ', "y": ' . str_replace(',','',$d['value'][$yIdx]) . ', "tip": "' . str_replace('"','\"',$tooltip[$i++]) . '"}';
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
					$text .= '{"' . ($type=='line'?'y':'top') . '": ' . str_replace(',','',$d['value'][$yIdx]) . ', "tip": "' . str_replace('"','\"',$tooltip[$i++]) . '"}';
				}
			}
			$text .= '],
				"colour":"' . SRFOFC::$ofc_color[$yIdx%4] . '",
				"text":"' . $chart['y'][$yIdx] . '",
				"fill":"transparent"
			}';
		}
		return $text;
	}
	private function getScale(&$min, &$max, &$step) {
		if($min >= 0) {
			$min = 0;
			if($max > 1) {
				$step = 1;
				while(intval($max / $step) > 10) {
					$step *= 10;
				}
			} else {
				$step = 0.1;
				while(intval($max / $step) < 1) {
					$step /= 10;
				}
			}
			$max = (intval($max / $step) + 1) * $step;
		} else if($max <= 0) {
			$max = 0;
			if($min < -1) {
				$step = 1;
				while(intval($min / $step) < -10) {
					$step *= 10;
				}
			} else {
				$step = 0.1;
				while(intval($min / $step) > -1) {
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
				while(intval($min / $step1) > -1) {
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
				while(intval($max / $step2) < 1) {
					$step2 /= 10;
				}
			}
			if($step2>$step1) $step = $step2; else $step = $step1;
			$max = (intval($max / $step) + 1) * $step;
			$min = (intval($min / $step) - 1) * $step;
		}
	}
}