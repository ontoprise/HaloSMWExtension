<?php
class SMWRFQueryProxy {
	
	private $currentQuery;
	
	static private function getQueryFromQueryString( array $rawparams, $outputmode, $context = SMWQueryProcessor::INLINE_QUERY, $showmode = false ) {
		SMWQueryProcessor::processFunctionParams( $rawparams, $querystring, $params, $printouts, $showmode );
		$query  = SMWQueryProcessor::createQuery( $querystring, $params, $context, '', $printouts);
		return $query;
	}
    
	/**
     * @return SMWQueryProxy
	 */
	static public function replaceEntity($queryString) {
		$query = self::getQueryFromQueryString($queryString);
		
		$currentQuery = self::replaceProperty($query);
		return $currentQuery->toQueryString();
	}
	
	static private function replaceProperty($prefixedTitle) {
		$desc = $currentQuery->getDescription();
		// clone and change 
		// $newdesc = ...
		$currentQuery->setDescription($newdesc);
	}
	
	static public function toQueryString() {
		return $currentQuery->toQueryString();
	}
}