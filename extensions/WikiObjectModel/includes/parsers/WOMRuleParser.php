<?php
/**
 * @author Ning
 *
 * @file
 * @ingroup WikiObjectModels
 */

class WOMRuleParser extends WOMHTMLTagParser {

	public function __construct() {
		parent::__construct();
		$this->m_parserId = WOM_PARSER_ID_RULE;
	}

	public function parseNext( $text, WikiObjectModelCollection $parentObj, $offset = 0 ) {
		$ret = parent::parseNext( $text, $parentObj, $offset );
		if ( $ret == null ) return null;

		if ( strtolower( $ret['obj']->getName() ) != 'rule' ) return null;

		$ret['obj'] = new WOMRuleModel( $ret['obj'] );
		if ( $ret['closed'] ) return $ret;

		$text = substr( $text, $offset + $ret['len'] );
		if ( preg_match( '/<\/rule\s*>/i', $text, $m, PREG_OFFSET_CAPTURE ) ) {
			$ret['obj']->setLogic( substr( $text, 0, $m[0][1] ) );
			$ret['len'] += $m[0][1] + strlen( $m[0][0] );
			// trick, always close this model, leave logic un-parsed
			$ret['closed'] = true;
			return $ret;
		}

		return null;
	}

//	public function isObjectClosed( $obj, $text, $offset ) {
//		if ( !$obj instanceof WOMRuleParser ) return false;
//
//		if ( preg_match( '/^<\/rule\s*>/i', substr( $text, $offset ), $m ) ) {
//			return strlen( $m[0] );
//		}
//
//		return false;
//	}
}
