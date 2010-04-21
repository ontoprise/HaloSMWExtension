<?php
global $smwgIP;
require_once "$smwgIP/includes/SMW_QP_CSV.php";

/**
 * Contains SMW QPs which needs to get overridden for some reason.
 * @author Kai K�hn
 *
 */
class SMWHaloCsvResultPrinter extends SMWCsvResultPrinter {

    public function getParameters() {
        $params = parent::getParameters();
        $params[]= array('name' => 'sep', 'type' => 'string', 'description' => 'Separator used');
        return $params;
    }

	protected function getResultText($res, $outputmode) {
		$result = '';
		if ($outputmode == SMW_OUTPUT_FILE) { // make CSV file
			$result .= parent::getResultText($res, $outputmode);
		} else { // just make link to feed
			if ($this->getSearchLabel($outputmode)) {
				$label = $this->getSearchLabel($outputmode);
			} else {
				wfLoadExtensionMessages('SemanticMediaWiki');
				$label = wfMsgForContent('smw_csv_link');
			}

			$link = $res->getQueryLink($label);
			$link->setParameter('csv','format');
			$link->setParameter($this->m_sep,'sep');
			if (array_key_exists('limit', $this->m_params)) {
				$link->setParameter($this->m_params['limit'],'limit');
			} else { // use a reasonable default limit
				$link->setParameter(100,'limit');
			}
			// KK: support merge option
			if (array_key_exists('merge', $this->m_params)) $link->setParameter($this->m_params['merge'], 'merge');
			$result .= $link->getText($outputmode,$this->mLinker);
			$this->isHTML = ($outputmode == SMW_OUTPUT_HTML); // yes, our code can be viewed as HTML if requested, no more parsing needed
		}
		return $result;
	}
}

