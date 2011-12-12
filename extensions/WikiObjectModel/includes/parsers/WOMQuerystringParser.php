<?php
/**
 * @author Ning
 *
 * @file
 * @ingroup WikiObjectModels
 */

class WOMQuerystringParser extends WikiObjectModelParser {

	public function __construct() {
		parent::__construct();
		$this->m_parserId = WOM_PARSER_ID_QUERYSTRING;
	}

	public function parseNext( $text, WikiObjectModelCollection $parentObj, $offset = 0 ) {
		if ( ! ( ( $parentObj instanceof WOMParserFunctionModel ) 
			&& ( strtolower( $parentObj->getFunctionKey() ) == 'ask' ) 
			&& ( count ( $parentObj->getObjects() ) == 0 ) ) ) {
			
			return null;
		}
		
		return array( 'len' => 0, 'obj' => new WOMQuerystringModel() );
	}

	public function isObjectClosed( $obj, $text, $offset ) {
		if ( !( $obj instanceof WOMQuerystringModel ) )
			return false;

		if ( ( strlen( $text ) >= $offset + 1 ) && $text { $offset } == '|' ) {
			return 1;
		}
		$parentClose = WOMProcessor::getObjectParser( $obj->getParent() )
			->isObjectClosed( $obj->getParent(), $text, $offset );
		if ( $parentClose !== false ) return 0;

		return false;
	}
}
