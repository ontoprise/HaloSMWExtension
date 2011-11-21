<?php

class SMWAQParserFunctions {
	static function registerFunctions( &$parser ) {
		$parser->setFunctionHook('ajaxask', array('SMWAQParserFunctions', 'doAjaxAsk'));
		$parser->setFunctionHook('ajaxsparql', array('SMWAQParserFunctions', 'doAjaxSparql'));
		
		return true;
	}
	
    public static function registerResourceModules() {
		global $wgResourceModules, $smwgHaloScriptPath, $smwgHaloIP;
		
		$moduleTemplate = array(
			'localBasePath' => $smwgHaloIP . '/scripts/ajaxquery',
			'remoteBasePath' => $smwgHaloScriptPath . '/scripts/ajaxquery',
			'group' => 'ext.smwhalo'
		);
		
		$wgResourceModules['ext.smwhalo.ajaxquery'] = $moduleTemplate + array(
			'scripts' => array( 'ajaxquery.js' ),
			'dependencies' => array(
		      'ext.smw.tooltips',
		      'ext.smw.sorttable',
		      'ext.smw.style',
			)
		);
	}
	
    static $_jsIncluded = false;
	static function setupAjaxHead() {
		if($_jsIncluded) return;
		
		SMWOutputs::requireHeadItem("smw_aq", '
<script type="text/javascript">
var AjaxAsk = { queries : [] }, AjaxSparql = { queries : [] };
</script>' . "\n");
		
		// MediaWiki 1.17 introduces the Resource Loader.
		$realFunction = array( 'SMWOutputs', 'requireResource' );
		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) && is_callable( $realFunction ) ) {
			SMWOutputs::requireResource( 'ext.smwhalo.ajaxquery' );
		} else {
			SMWOutputs::requireHeadItem( SMW_HEADER_STYLE );
			SMWOutputs::requireHeadItem( SMW_HEADER_SORTTABLE );
			SMWOutputs::requireHeadItem( SMW_HEADER_TOOLTIP );
		}

		$_jsIncluded = true;
	}
	
	static public function doAjaxAsk(&$parser) {
		global $smwgQEnabled, $smwgIQRunningNumber;
		if ($smwgQEnabled) {
			self::setupAjaxHead();

			$smwgIQRunningNumber++;
			$params = func_get_args();
			array_shift( $params ); // we already know the $parser ...
			
			global $smwgHaloScriptPath;
			$id = 'AjaxAsk' . $smwgIQRunningNumber;
			// have to enable script and css, tbd
			SMWOutputs::requireHeadItem($id, '<script type="text/javascript">/*<![CDATA[*/
			AjaxAsk.queries.push({id:"'.$id.'",qno:'.$smwgIQRunningNumber.',query:"' . str_replace("\n", '', str_replace('"', '\"', implode(' | ', $params))) . '"});
			/*]]>*/</script>');
			$result = '<div id="' . $id . '"><img src="' . $smwgHaloScriptPath . '/skins/ajax-loader.gif"></div>';  
		} else {
			wfLoadExtensionMessages('SemanticMediaWiki');
			$result = smwfEncodeMessages(array(wfMsgForContent('smw_iq_disabled')));
		}
		return array($result, 'noparse' => true, 'isHTML' => true);
	}
	
    static public function doAjaxSparql(&$parser) {
        global $smwgQEnabled, $smwgIQRunningNumber;
        if ($smwgQEnabled) {
			self::setupAjaxHead();
        	
            $smwgIQRunningNumber++;
            $params = func_get_args();
            array_shift( $params ); // we already know the $parser ...
            
			global $smwgHaloScriptPath;
            $id = 'AjaxAsk' . $smwgIQRunningNumber;
            // have to enable script and css, tbd
            SMWOutputs::requireHeadItem($id, '<script type="text/javascript">/*<![CDATA[*/
            AjaxSparql.queries.push({id:"'.$id.'",qno:'.$smwgIQRunningNumber.',query:"' . str_replace("\n", '', str_replace('"', '\"', implode(' | ', $params))) . '"});
            /*]]>*/</script>');
            $result = '<div id="' . $id . '"><img src="' . $smwgHaloScriptPath . '/skins/ajax-loader.gif"></div>';  
        } else {
            wfLoadExtensionMessages('SemanticMediaWiki');
            $result = smwfEncodeMessages(array(wfMsgForContent('smw_iq_disabled')));
        }
        return array($result, 'noparse' => true, 'isHTML' => true);
    }
}
