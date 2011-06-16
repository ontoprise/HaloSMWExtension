<?php
/**
 * @author Ning
 *
 * @file
 * @ingroup WikiObjectModels
 */

// little tricky here, just inherit from link parser
class WOMPropertyParser extends WOMLinkParser {

	public function __construct() {
		parent::__construct();
		$this->m_parserId = WOM_PARSER_ID_PROPERTY;
	}

	public function parseNext( $text, WikiObjectModelCollection $parentObj, $offset = 0 ) {
		if ( !defined( 'SMW_VERSION' ) ) {
			return null;
		}

		$text = substr( $text, $offset );

		// copied from SemanticMediaWiki, includes/SMW_ParserExtensions.php
		// not deal with <nowiki>, could be bug here. SMW has the same bug
		// E.g., [[text::this <nowiki> is ]] </nowiki> not good]]
					$semanticLinkPattern = '/^\[\[                 # Beginning of the link
			                        (?:([^:][^]]*):[=:])+ # Property name (or a list of those)
			                        (                     # After that:
			                          (?:[^|\[\]]         #   either normal text (without |, [ or ])
			                          |\[\[[^]]*\]\]      #   or a [[link]]
			                          |\[[^]]*\]          #   or an [external link]
			                        )*)                   # all this zero or more times
			                        (?:\|([^]]*))?        # Display text (like "text" in [[link|text]]), optional
			                        \]\]                  # End of link
			                        /xu';

		$r = preg_match( $semanticLinkPattern, $text, $m );
		if ( $r ) {
			return array( 'len' => strlen( $m[0] ), 'obj' => new WOMPropertyModel( $m[1], $m[2], isset( $m[3] ) ? $m[3] : '' ) );
		}
		return null;
	}
}
