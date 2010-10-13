<?php
/**
 * @author Ning Hu
 */

if (!defined('MEDIAWIKI')) die();

class SCViewREST extends SpecialPage {
	public function __construct() {
		parent::__construct('ViewREST');
	}
	
	static function setupHeader(&$out) {
		global $wgOut, $smwgConnectorScriptPath;
		smwfConnectorGetJSLanguageScripts($pathlng, $userpathlng);
		$wgOut->addLink( array( 'rel' => 'stylesheet', 'type' => 'text/css',
			'href' => $smwgConnectorScriptPath . '/scripts/extjs/resources/css/ext-all.css' ) );

//		if ( defined( 'SMW_HALO_VERSION' ) ){
//			// halo uses prototype
//			$wgOut->addScript('<script type="text/javascript" src="' . $smwgConnectorScriptPath . '/scripts/extjs/adapter/prototype/ext-prototype-adapter.js"></script>');
//		} else {
			$wgOut->addScript('<script type="text/javascript" src="' . $smwgConnectorScriptPath . '/scripts/extjs/adapter/ext/ext-base.js"></script>');
//		}
		$wgOut->addScript('<script type="text/javascript" src="' . $smwgConnectorScriptPath . '/scripts/extjs/ext-all.js"></script>');
		$wgOut->addScript('<script type="text/javascript" src="' . $smwgConnectorScriptPath . '/scripts/restviewer.js"></script>');
		
		$ed = SpecialPage::getPage('RESTful');
		$wgOut->addScript('<script type="text/javascript">
		SemanticConnector.restviewer.ajaxUrlPrefix = "' . $ed->getTitle()->getFullURL() . '/";
		</script>');
	}
	
	function execute($query) {
		$this->setHeaders();
		global $wgOut;
		$wgOut->addHTML('<div id="restful-form" style=""></div>');
	}
}