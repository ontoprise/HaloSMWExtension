<?php
/**
 * This model implements Rule logic models.
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

class WOMRuleLogicModel extends WikiObjectModel {
	protected $m_logic;

	public function __construct( $logic = '' ) {
		parent::__construct( WOM_TYPE_RULELOGIC );

		$this->m_logic = $logic;
	}

	public function setLogic( $logic ) {
		$this->m_logic = $logic;
	}

	public function getLogic() {
		return $this->m_logic;
	}

	public function getWikiText() {
		return $this->m_logic;
	}

	public function getXMLContent() {
		return htmlspecialchars( $this->m_logic );
	}
}
