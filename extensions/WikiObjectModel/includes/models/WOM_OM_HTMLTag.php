<?php
/**
 * This model implements Template models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectModels
 *
 */

class WOMHTMLTagModel extends WikiObjectModelCollection {
	protected $m_name;
	protected $m_attrs = array();

	public function __construct( $name, $attrs = array() ) {
		parent::__construct( WOM_TYPE_HTMLTAG );
		$this->m_name = $name;
		$this->m_attrs = $attrs;
	}

	public function getName() {
		return $this->m_name;
	}

	public function setName( $name ) {
		$this->m_name = $name;
	}

	public function getAttributes() {
		return $this->m_attributes;
	}

	public function setAttributes( $attrs ) {
		$this->m_attributes = $attrs;
	}

	public function getWikiText() {
		return "<{$this->m_name}>" . parent::getWikiText() . "</{$this->m_name}>";
	}

	public function updateOnNodeClosed() {
		// use SemanticForms to bind properties to fields
	}

	public function setXMLAttribute( $key, $value ) {
		if ( $value == '' ) throw new MWException( __METHOD__ . ": value cannot be empty" );

		if ( $key == 'name' ) {
			$this->m_name = $value;
		} else {
			throw new MWException( __METHOD__ . ": invalid key/value pair: name=html_tag_name" );
		}
	}
	protected function getXMLAttributes() {
		return "name=\"{$this->m_name}\"";
	}
}
