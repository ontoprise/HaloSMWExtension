<?php
/**
 * Parser functions for Semantic Connector.
 *
 * Reuse Semantic Forms parser function: 'forminput'
 */

class SCParserFunctions {

	static function registerFunctions( &$parser ) {
		$parser->setFunctionHook('mapview', array('SCParserFunctions', 'renderMapView'));
		return true;
	}
	
	static function languageGetMagic( &$magicWords, $langCode = "en" ) {
		switch ( $langCode ) {
		default:
			$magicWords['mapview'] = array ( 0, 'mapview' );
		}
		return true;
	}
	
	static function renderMapView (&$parser, /*$page='', */$text='') {
		global $wgScriptPath, $smwgConnectorScriptPath, $wgTitle;
		if(!isset($wgTitle)) return '';
//		if($page=='' && !isset($wgTitle)) return '';
//		if($page == '') 
		$page = $wgTitle->getText();
		$parser->getOutput()->addHeadItem('<script type="text/javascript">
		SemanticConnector = { 
			form: {
				name: "' . $page . '", ajaxUrl: "' . $wgScriptPath . '/index.php"
			}
		};
		</script>');

		$str = '<a href="#" id="show_mapping">' . ($text=='' ? wfMsg('sc_editmapping') : $text) . '</a>
<div id="shade" style=""></div>
<div id="loading">
  <div class="loading-indicator"><img src="' . $smwgConnectorScriptPath . '/skins/extanim32.gif" width="32" height="32" style="margin-right:8px;" align="absmiddle"/>' . wfMsg('sc_loading') . '</div>
</div>';
		return array($str, 'noparse' => true, 'isHTML' => true);
	}

}
