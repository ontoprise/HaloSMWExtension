<?php
/**
 * This model implements Rule models.
 * <rule name="myrule" native="true/false" active="true/false" uri="http://...">
 *   ...Object-Logic text...
 * </rule>
 * The Object-Logic content does not have to be parsed, it can be a plain string.
 * Actually, we don't have a PHP parser for this.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectModels
 *
 */

class WOMRuleModel extends WikiObjectModelCollection {
	protected $m_name;
	protected $m_native;
	protected $m_active;
	protected $m_uri;

	public function __construct( $htmlObj, $logic = '' ) {
		parent::__construct( WOM_TYPE_RULE );

		$this->m_name = $htmlObj->getAttribute( 'name' );
		$this->m_native = ( strtolower( $htmlObj->getAttribute( 'native' ) ) == 'true' );
		$this->m_active = ( strtolower( $htmlObj->getAttribute( 'active' ) ) == 'true' );
		$this->m_uri = $htmlObj->getAttribute( 'uri' );

		$this->setLogic( $logic );
	}

	public function setLogic( $logic ) {
		if ( count( $this->m_objects ) == 0 ) {
			$this->m_objects[] = new WOMRuleLogicModel( $logic );
		} else {
			$this->m_objects[0]->setLogic( $logic );
		}
	}

	public function getName() {
		return $this->m_name;
	}

	public function getUri() {
		return $this->m_uri;
	}

	public function isNative() {
		return $this->m_native;
	}

	public function isActive() {
		return $this->m_active;
	}

	public function getLogicText() {
		return parent::getWikiText();
	}

	public function setXMLAttribute( $key, $value ) {
		if ( $value == '' ) throw new MWException( __METHOD__ . ": value cannot be empty" );

		if ( $key == 'name' ) {
			$this->m_name = $value;
		} else if ( $key == 'native' ) {
			$this->m_native = ( strtolower( $value ) == 'true' );
		} else if ( $key == 'active' ) {
			$this->m_active = ( strtolower( $value ) == 'true' );
		} else if ( $key == 'uri' ) {
			$this->m_uri = $value;
		} else {
			throw new MWException( __METHOD__ . ": invalid key/value pair: " .
				"name=\"myrule\" native=\"true/false\" active=\"true/false\" uri=\"http://...\"" );
		}
	}
	protected function getXMLAttributes() {
		$native = ( $this->m_native ? "true" : "false" );
		$active = ( $this->m_active ? "true" : "false" );
		$attr = "name=\"{$this->m_name}\" native=\"{$native}\" active=\"{$active}\" uri=\"{$this->m_uri}\"";
		return $attr;
	}

	public function getWikiText() {
		return "<rule {$this->getXMLAttributes()}>" . parent::getWikiText() . '</rule>';
	}
}
