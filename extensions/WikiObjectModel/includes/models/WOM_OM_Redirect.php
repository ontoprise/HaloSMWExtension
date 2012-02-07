<?php
/**
 * This model implements redirect models.
 *
 * #REDIRECT [[Pagename]]
 * Consider that #REDIRECT can be localized.
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectModels
 *
 */

class WOMRedirectModel extends WikiObjectModel {
	private $m_to_page;

	public function __construct( $to_page ) {
		parent::__construct( WOM_TYPE_REDIRECT );
		$this->m_to_page = $to_page;
	}

	public function getWikiText() {
		return '#REDIRECT [[' . $this->m_to_page . ']]';
	}

	protected function getXMLContent() {
		return "<![CDATA[{$this->m_to_page}]]>";
	}
}
