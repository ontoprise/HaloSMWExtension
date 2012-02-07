<?php
/**
 * @author Ning
 *
 * @file
 * @ingroup WikiObjectModels
 */

class WOMRedirectParser extends WOMListItemParser {

	public function __construct() {
		parent::__construct();
		$this->m_parserId = WOM_PARSER_ID_REDIRECT;
	}

	public function parseNext( $text, WikiObjectModelCollection $parentObj, $offset = 0 ) {
		$text = substr( $text, $offset );

		if ( !preg_match( MagicWord::get( 'redirect' )->getRegexStart(), $text, $m ) )
			return null;
		$len = strlen( $m[0] );
		$text = substr( $text, $len );
		if ( !preg_match( '/^\s*\[\[:?(.*?)(\|(.*?))*\]\]/', $text, $m ) )
			return null;

		return array( 'len' => $len + strlen( $m[0] ), 'obj' => new WOMRedirectModel( $m[1] ) );
	}
}
