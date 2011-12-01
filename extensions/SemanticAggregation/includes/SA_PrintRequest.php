<?php
/**
 * @file
 * @ingroup SMWQuery
 * @author Ning Hu
 */

class SMWAggregatePrintRequest extends SMWPrintRequest {
	protected $m_aggregation;
	
	public function getAggregation() {
		return $this->m_aggregation;
	}
	
	public function __construct($mode, $label, $data = NULL, $outputformat = '', $aggregation = '') {
		parent::__construct($mode, $label, $data, $outputformat);
		$this->m_aggregation = $aggregation;
	}

	/**
	 * Serialise this object like print requests given in \#ask.
	 */
	public function getSerialisation() {
		switch ($this->m_mode) {
			case SMWPrintRequest::PRINT_PROP: case SMWPrintRequest::PRINT_CCAT:
				if ($this->m_mode == SMWPrintRequest::PRINT_CCAT) {
					$printname = $this->m_data->getPrefixedText();
					$result = '?' . $printname;
					if ( $this->m_outputformat != 'x' ) {
						$result .= '#' . $this->m_outputformat;
					}
				} else {
					$printname = $this->m_data->getWikiValue();
					$result = '?' . $printname;
					if ( $this->m_outputformat != '' ) {
						$result .= '#' . $this->m_outputformat;
					}
				}
				
				if ( $this->m_aggregation ) {
					$result .= '>' . $this->m_aggregation;
				}
				
				if ( $printname != $this->m_label ) {
					$result .= '=' . $this->m_label;
				}
				return $result;
			default: return parent::getSerialisation();
		}
	}
}
