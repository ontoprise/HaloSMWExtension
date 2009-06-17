<?php
/**
 * @file
 * @ingroup SpecialPage
 */

function wfSpecialFilepath( $par ) {
	global $wgRequest, $wgOut;

	$file = isset( $par ) ? $par : $wgRequest->getText( 'file' );

	$title = Title::newFromText( $file, NS_IMAGE );
	/*op-patch|BL|2009-06-17|RichMedia|AdditionalNamespaceCheck|start*/
	// NS_IMAGE is not the only Namespace now, so check them all
	// content was:
	// if ( ! $title instanceof Title || $title->getNamespace() != NS_IMAGE ) {
	if ( ! $title instanceof Title || !Namespace::isImage( $title->getNamespace() ) ) {
	/*op-patch|BL|2009-00-17|end*/
		$cform = new FilepathForm( $title );
		$cform->execute();
	} else {
		$file = wfFindFile( $title );
		if ( $file && $file->exists() ) {
			$wgOut->redirect( $file->getURL() );
		} else {
			$wgOut->setStatusCode( 404 );
			$cform = new FilepathForm( $title );
			$cform->execute();
		}
	}
}

/**
 * @ingroup SpecialPage
 */
class FilepathForm {
	var $mTitle;

	function FilepathForm( &$title ) {
		$this->mTitle =& $title;
	}

	function execute() {
		global $wgOut, $wgTitle, $wgScript;

		$wgOut->addHTML(
			Xml::openElement( 'form', array( 'method' => 'get', 'action' => $wgScript, 'id' => 'specialfilepath' ) ) .
			Xml::openElement( 'fieldset' ) .
			Xml::element( 'legend', null, wfMsg( 'filepath' ) ) .
			Xml::hidden( 'title', $wgTitle->getPrefixedText() ) .
			Xml::inputLabel( wfMsg( 'filepath-page' ), 'file', 'file', 25, is_object( $this->mTitle ) ? $this->mTitle->getText() : '' ) . ' ' .
			Xml::submitButton( wfMsg( 'filepath-submit' ) ) . "\n" .
			Xml::closeElement( 'fieldset' ) .
			Xml::closeElement( 'form' )
		);
	}
}
