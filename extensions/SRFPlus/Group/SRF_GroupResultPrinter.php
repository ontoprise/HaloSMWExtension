<?php

/**
 * Various mathematical functions - sum, average, min and max.
 *
 * @file
 * @ingroup SemanticResultFormats
 * @author Ning
 */

if (!defined('MEDIAWIKI')) die();

abstract class SRFGroupResultPrinter extends SMWAggregateResultPrinter {

	protected $mGroupBy = NULL;
	
	protected function readParameters($params,$outputmode) {
		SMWResultPrinter::readParameters($params,$outputmode);

		if (array_key_exists('group by', $params)) {
			$this->mGroupBy = trim($params['group by']);
		}
	}

	public function getName() {
		wfLoadExtensionMessages('SemanticMediaWiki');
		return wfMsg('smw_printername_' . $this->mFormat);
	}

	protected function getGroupResult($res, $outputmode, &$headers) {
		$act_column = 0;
		$headers = array();
		$group_col = NULL;
		foreach ($res->getPrintRequests() as $pr) {
			if($act_column > 0) {
				if($pr->getLabel() == $this->mGroupBy) {
					$group_col = $act_column;
				}
				$headers[$act_column] = /*$this->m_aggregates[$act_column]->getResultPrefix($outputmode) .*/ $pr->getText($outputmode, ($this->mShowHeaders == SMW_HEADERS_PLAIN?NULL:$this->mLinker));
			}
			$act_column ++;
		}

		if($group_col === NULL) {
			return NULL;
		}

		array_unshift($headers, $headers[$group_col]);
		array_splice($headers, $group_col, 1);
		array_splice($this->m_aggregates, $group_col, 1);
		
		// put all result rows into an array, for easier handling
		$result_rows = array();
		// print all result rows
		while ( $row = $res->getNext() ) {
			$keys = array();
			$row_data = array();
			$act_column = 0;
			foreach ($row as $field) {
				$first = true;
				while ( ($object = $field->getNextObject()) !== false ) {
					if($act_column == $group_col) {
						$key = $object->getShortText($outputmode,$this->getLinker(false));
						$keys[] = $key;
						$result_rows[$key][0] = $object;
					} else if($act_column > $group_col) {
						$row_data[$act_column - 1][] = $object;
					} else {
						$row_data[$act_column][] = $object;
					}
				}
				$act_column ++;
			}
			foreach($row_data as $id=>$objs) {
				if($id == 0) continue;

				foreach($keys as $key) {
					if(!isset($result_rows[$key][$id])) {
						$result_rows[$key][$id] = clone $this->m_aggregates[$id];
					}
					// haven't think of nary, tbd
					if($this->m_hasAggregate) {
						foreach($objs as $object) {
							$result_rows[$key][$id]->appendValue($object);
						}
					}
				}
			}
		}

		return $result_rows;
	}
}
