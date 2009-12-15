<?php
/**
 * Parser functions for tab widget.
 */

class UPParserFunctions {
	static function languageGetMagic( &$magicWords, $langCode = "en" ) {
		switch ( $langCode ) {
			default:
				$magicWords['tab'] = array ( 0, 'tab' );
				$magicWords['ajaxask']	= array ( 0, 'ajaxask' );
				$magicWords['ajaxsparql']  = array ( 0, 'ajaxsparql' );
		}
		return true;
	}

	static function registerFunctions( &$parser ) {
		$parser->setFunctionHook('tab', array('UPParserFunctions', 'renderTabWidget'), SFH_OBJECT_ARGS);
		$parser->setFunctionHook('ajaxask', array('UPParserFunctions', 'doAjaxAsk'));
		$parser->setFunctionHook('ajaxsparql', array('UPParserFunctions', 'doAjaxSparql'));
		
		$parser->setHook( "embedwiki", array('UPParserFunctions', 'embedWiki') );
		
		return true;
	}

//	static $inlineParser = false;
	static public function embedWiki( $input, $argv ) {
//		if(!UPParserFunctions::$inlineParser) {
//			global $wgParserConf;
//			UPParserFunctions::$inlineParser = wfCreateObject( $wgParserConf['class'], array( $wgParserConf ) );
//		}
		global $wgParser;
	            
	   	if ( ($wgParser->getTitle() instanceof Title) && ($wgParser->getOptions() instanceof ParserOptions) ) {
			$result = $wgParser->recursiveTagParse($input);
		} else {
			global $wgTitle;
			$popt = new ParserOptions();
			$popt->setEditSection(false);
			$pout = $wgParser->parse($input . '__NOTOC__', $wgTitle, $popt);
			/// NOTE: as of MW 1.14SVN, there is apparently no better way to hide the TOC
			SMWOutputs::requireFromParserOutput($pout);
			$result = $pout->getText();
		}
	    return $result;           
	}

	static function setupAjaxHead($parser) {
		SMWOutputs::requireHeadItem(SMW_HEADER_STYLE);
		// add ajax ask header
		global $smwgUltraPediaScriptPath;
		$parser->getOutput()->addHeadItem('<script type="text/javascript" src="' . $smwgUltraPediaScriptPath . '/scripts/ajaxasks.js"></script>', "ajaxask-header");
	}
	static public function doAjaxAsk(&$parser) {
		global $smwgQEnabled, $smwgIQRunningNumber;
		if ($smwgQEnabled) {
			UPParserFunctions::setupAjaxHead($parser);

			$smwgIQRunningNumber++;
			$params = func_get_args();
			array_shift( $params ); // we already know the $parser ...
			
			global $smwgUltraPediaScriptPath;
			$id = 'AjaxAsk' . $smwgIQRunningNumber;
			// have to enable script and css, tbd
			$parser->getOutput()->addHeadItem('<script type="text/javascript">
				AjaxAsk.queries.push({id:"'.$id.'",qno:'.$smwgIQRunningNumber.',query:"' . str_replace("\n", '', str_replace('"', '\"', implode(' | ', $params))) . '"});
			</script>');
			$result = '<div id="' . $id . '"><img src="' . $smwgUltraPediaScriptPath . '/ajax-loader.gif"></div>';  
		} else {
			wfLoadExtensionMessages('SemanticMediaWiki');
			$result = smwfEncodeMessages(array(wfMsgForContent('smw_iq_disabled')));
		}
		return array($result, 'noparse' => true, 'isHTML' => true);
	}
	
    static public function doAjaxSparql(&$parser) {
        global $smwgQEnabled, $smwgIQRunningNumber;
        if ($smwgQEnabled) {
			UPParserFunctions::setupAjaxHead($parser);
        	
            $smwgIQRunningNumber++;
            $params = func_get_args();
            array_shift( $params ); // we already know the $parser ...
            
            global $smwgUltraPediaScriptPath;
            $id = 'AjaxAsk' . $smwgIQRunningNumber;
            // have to enable script and css, tbd
            $parser->getOutput()->addHeadItem('<script type="text/javascript">
                AjaxSparql.queries.push({id:"'.$id.'",qno:'.$smwgIQRunningNumber.',query:"' . str_replace("\n", '', str_replace('"', '\"', implode(' | ', $params))) . '"});
            </script>');
            $result = '<div id="' . $id . '"><img src="' . $smwgUltraPediaScriptPath . '/ajax-loader.gif"></div>';  
        } else {
            wfLoadExtensionMessages('SemanticMediaWiki');
            $result = smwfEncodeMessages(array(wfMsgForContent('smw_iq_disabled')));
        }
        return array($result, 'noparse' => true, 'isHTML' => true);
    }

	static $tabWidgetId = 0;
	static function renderTabWidget ($parser, $frame, $args) {
		global $smwgUltraPediaScriptPath, $wgTitle;
		$parser->getOutput()->addHeadItem('<link rel="stylesheet" type="text/css" href="' . $smwgUltraPediaScriptPath . '/scripts/extjs/resources/css/ext-all.css" />', "tab_css");
		$parser->getOutput()->addHeadItem('<script type="text/javascript" src="' . $smwgUltraPediaScriptPath . '/scripts/extjs/adapter/ext/ext-base.js"></script>', "tab_js1");
		$parser->getOutput()->addHeadItem('<script type="text/javascript" src="' . $smwgUltraPediaScriptPath . '/scripts/extjs/ext-all.js"></script>', "tab_js2");
		$parser->getOutput()->addHeadItem('<script type="text/javascript" src="' . $smwgUltraPediaScriptPath . '/scripts/tabwidgets.js"></script>', "tab_js3");
		
		$tabs = array();
		$htmls = array();
		$widget_options = array();
		$widget_name = '';
		
		$id = UPParserFunctions::$tabWidgetId ++;
		
		for($i = count($args);$i>0; --$i) {
			$t = trim($frame->expand( array_shift( $args ) ));
			if(!$t) continue;
			$arr = explode('=', $t, 2);
			$t = trim($arr[1]);
			if(strtolower(trim($arr[0])) == 'options') {
				foreach(explode(';', $t) as $opt) {
					$nv = explode(':', $opt);
					$widget_options[strtolower(trim($nv[0]))] = trim($nv[1]);
				}
				continue;
			}
			if(strtolower(trim($arr[0])) == 'name') {
				$widget_name = $t;
				continue;
			}
			
			$var = explode('.', $arr[0], 2);
			if(strtolower(trim($var[1]))=='body') {
				if(preg_match('/^\[\[([^\[\]\|]+)(\|([^\[\]]+))?\]\]$/', $t, $matches)) {
					if(count($matches) == 4) $t = $matches[3]; else $t = $matches[1];
					$tabs[$var[0]] = array( 'title' => $t, 'type' => 'internal', 'html' => $matches[1] );
				} else {
					$val = explode("\n", $t, 2);
					$t = trim($val[0]);
					$html = $val[1];
					$html = $parser->preprocessToDom($html, $frame->isTemplate() ? Parser::PTD_FOR_INCLUSION : 0);
					$html_id = 'tabs' . $id . '_' . $i;
					$htmls[$html_id] = trim($frame->expand($html));
					$tabs[$var[0]] = array( 
						'title' => $t, 'type' => 'html', 'html' => $html_id );
				}
			} else if(strtolower(trim($var[1]))=='option') {
				$options = array();
				foreach(explode(';', $t) as $opt) {
					$nv = explode(':', $opt);
					$options[strtolower(trim($nv[0]))] = trim($nv[1]);
				}
				$tabs[$var[0]]['option'] = $options;
			}
		}
		$tabItems = array();
		global $smwgIQRunningNumber;
		foreach($tabs as $t) {
			$txt = 'title: "' . str_replace('"', '\"', $t['title']) . '",';
			if($t['type'] == 'html') {
				$txt .= 'contentEl:"' . $t['html'] . '"';
			} else if($t['type'] == 'internal') {
				$txt .= 'autoLoad: {url: "", params: "action=ajax&rs=smwf_up_Access&&rsargs[]=internalLoad&rsargs[]=' . ($smwgIQRunningNumber++) . ',' . $t['html'] . '"}';
			}

			$tabItems[] = $txt;
		}
		
		$parser->getOutput()->addHeadItem('<script type="text/javascript">
			UltraPedia.tabWidgets.push({
				id:"tabs' . $id . '",' . 
				(isset($widget_options['height'])?('height:' . intval($widget_options['height']) . ','):'') . 
				(isset($widget_options['width'])?('width:' . intval($widget_options['width']) . ','):'') . 
				'items:[{' . implode('},{', $tabItems) . '}]
			});
			</script>');
		$str = '<div id="tabs' . $id . '">';
		foreach($htmls as $hid => $html) {
			$str .= '<div id="' . $hid . '" class="x-hide-display">' . $html . '</div>';
		}
		$str .= '</div>';
		return array($str, 'noparse' => true, 'isHTML' => false);
	}

}
