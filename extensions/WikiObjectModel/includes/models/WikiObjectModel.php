<?php
/**
 * File holding abstract class WikiObjectModel, the base for all object model in WOM.
 *
 * @author Ning
 *
 * @file
 * @ingroup WikiObjectModels
 */

abstract class WikiObjectModel {
	protected $m_objid;

	protected $m_typeid;

	protected $m_parent = null;

	/**
	 * Array of error text messages. Private to allow us to track error insertion
	 * (PHP's count() is too slow when called often) by using $mHasErrors.
	 * @var array
	 */
	protected $mErrors = array();

	/**
	 * Boolean indicating if there where any errors.
	 * Should be modified accordingly when modifying $mErrors.
	 * @var boolean
	 */
	protected $mHasErrors = false;

	/**
	 * Constructor.
	 *
	 * @param string $typeid
	 */
	public function __construct( $typeid ) {
		$this->m_typeid = $typeid;
	}

// /// Set methods /////
	public function setObjectID( $id ) {
		$this->m_objid = $id;
	}

	public function setParent( $parent ) {
		$this->m_parent = $parent;
	}

// /// Get methods /////
	public function getParent() {
		return $this->m_parent;
	}

	public abstract function getWikiText();

	public function isCollection() {
		return false;
	}

	public function getObjectID() {
		return $this->m_objid;
	}

	public function getTypeID() {
		return $this->m_typeid;
	}

	/**
	 * Return TRUE if a value was defined and understood by the given type,
	 * and false if parsing errors occured or no value was given.
	 */
	public function isValid() {
		return ( ( !$this->mHasErrors ) );
	}

	/**
	 * Return a string that displays all error messages as a tooltip, or
	 * an empty string if no errors happened.
	 */
	public function getErrorText() {
		if ( defined( 'SMW_VERSION' ) )
			return smwfEncodeMessages( $this->mErrors );

		return $this->mErrors;
	}

	/**
	 * Return an array of error messages, or an empty array
	 * if no errors occurred.
	 */
	public function getErrors() {
		return $this->mErrors;
	}

	public function setXMLAttribute( $key, $value ) {
		throw new MWException( __METHOD__ . ": invalid key=value pair: no attribute required" );
	}
	protected function getXMLAttributes() {
		return "";
	}
	protected function getXMLContent() {
		return "";
	}
	public function toXML() {
		return "<{$this->m_typeid} id=\"{$this->m_objid}\" {$this->getXMLAttributes()}>{$this->getXMLContent()}</{$this->m_typeid}>";
	}

	public function objectUpdate( WikiObjectModel $obj ) { }
}
