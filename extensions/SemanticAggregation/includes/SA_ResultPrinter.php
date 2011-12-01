<?php
/**
 * File with abstract base class for printing query results with aggregation supporting.
 * @author Ning Hu
 * @file
 * @ingroup SMWQuery
 */

abstract class SMWAggregateResultPrinter extends SMWResultPrinter {
	protected $m_aggregates;
	protected $m_hasAggregate;
	
	public function getResult($results, $params, $outputmode) {
		$this->m_aggregates = array();
		$this->m_hasAggregate = false;
		
		global $smwgQueryAggregateIDs;
		
		foreach ($results->getPrintRequests() as $pr) {
			if(!($pr instanceof SMWAggregatePrintRequest)) {
				$this->m_aggregates[] = new SMWFakeQueryAggregate();
				continue;
			}
			$t = explode(':',$pr->getAggregation(),2);
			$qaID = strtoupper(trim($t[0]));
			if(isset($smwgQueryAggregateIDs[$qaID])) {
				$clazz = $smwgQueryAggregateIDs[$qaID];
				$aggregate = new $clazz($this->mLinker, $pr);
				if(!$aggregate instanceof SMWFakeQueryAggregate) {
					$this->m_hasAggregate = true;
				}
				$this->m_aggregates[] = $aggregate;
			} else {
				$this->m_aggregates[] = new SMWFakeQueryAggregate();
			}
		}
		
		return parent::getResult($results, $params, $outputmode);
	}
}
