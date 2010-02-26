<?php

/**
 * @file
 * @ingroup WYSIWYG
 * @ingroup Parser
 */

/**
 * The parser options for the FCKeditorParser.
 *
 * @ingroup WYSIWYG
 * @ingroup Parser
 */
class FCKeditorParserOptions extends ParserOptions
{
	function getNumberHeadings() {return false;}
	function getEditSection() {return false;}

	function getSkin() {
		if ( !isset( $this->mSkin ) ) {
			$this->mSkin = new FCKeditorSkin( $this->mUser->getSkin() );
		}
		return $this->mSkin;
	}
}
