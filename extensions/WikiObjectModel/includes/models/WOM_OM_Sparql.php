<?php
/**
 * This model implements sparql parser function models.
 * {{#sparql: SELECT ...  }}
 *
 * @author Ning
 * @file
 * @ingroup WikiObjectModels
 *
 */

class WOMSparqlModel extends WikiObjectModel {
	private $m_query;

	public function __construct( $query ) {
		parent::__construct( WOM_TYPE_SPARQL );
		$this->m_query = $query;
	}

	public function getWikiText() {
		return $this->m_query . '|';
	}

	protected function getXMLContent() {
		return "<![CDATA[{$this->m_query}]]>";
	}
}
