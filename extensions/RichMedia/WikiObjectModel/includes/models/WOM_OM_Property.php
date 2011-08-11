<?php
/**
 * This model implements Property models.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectModels
 *
 */

class WOMPropertyModel extends WikiObjectModel {
	protected $m_property; // name
	protected $m_smwdatavalue; // value, caption, type
	protected $m_visible;

	public function __construct( $property, $value, $caption = '' ) {
		parent::__construct( WOM_TYPE_PROPERTY );

		if ( !defined( 'SMW_VERSION' ) ) {
			// MW hook will catch this exception
			throw new MWException( __METHOD__ . ": Property model is invalid. Please install 'SemanticMediaWiki extension' first." );
		}

		$property = SMWPropertyValue::makeUserProperty( $property );
		$smwdatavalue = SMWDataValueFactory::newPropertyObjectValue( $property, $value, $caption );

		$this->m_property = $property;
		$this->m_smwdatavalue = $smwdatavalue;
		$this->m_visible = !preg_match( '/\s+/', $caption );
	}

	public function getProperty() {
		return $this->m_property;
	}

	public function setProperty( $property ) {
		$this->m_property = $property;
	}

	public function getSMWDataValue() {
		return $this->m_smwdatavalue;
	}

	public function setSMWDataValue( $smwdatavalue ) {
		$this->m_smwdatavalue = $smwdatavalue;
	}

	public function getWikiText() {
		$res = "[[{$this->getPropertyName()}::{$this->getPropertyValue()}";
		if ( $this->getPropertyValue() != $this->getCaption()
			&& $this->getCaption() != '' ) {
				$res .= "|{$this->getCaption()}";
		} else if ( !$this->m_visible ) {
			$res .= "| ";
		}
		$res .= "]]";

		return $res;
	}

	public function getPropertyName() {
		return $this->m_property->getWikiValue();
	}

	public function getPropertyValue() {
		return $this->m_smwdatavalue->getWikiValue();
	}

	public function getCaption() {
		$caption = $this->m_smwdatavalue->getShortWikiText();
		return ( $caption == $this->getPropertyValue() ) ? '' : $caption;
	}

	public function setXMLAttribute( $key, $value ) {
		if ( $value == '' ) throw new MWException( __METHOD__ . ": value cannot be empty" );

		if ( $key == 'name' ) {
			$property = SMWPropertyValue::makeUserProperty( $value );
		} else {
			throw new MWException( __METHOD__ . ": invalid key/value pair: name=property_name" );
		}
	}
	protected function getXMLAttributes() {
		return "name=\"{$this->getPropertyName()}\"";
	}
	protected function getXMLContent() {
		return "
<value>{$this->getPropertyValue()}</value>
<caption>{$this->getCaption()}</caption>
";
	}
}
