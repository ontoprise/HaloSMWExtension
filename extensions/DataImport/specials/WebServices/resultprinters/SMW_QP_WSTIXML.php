<?php

class SMWQPWSTIXML extends SMWResultPrinter {

	protected $mSep = ',';
	protected $mTemplate = false;

	protected function getResultText( $res, $outputmode ) {

		$result = "<?xml version='1.0'?>";
		$result .= "<tixml xmlns='http://www.ontoprise.de/smwplus#'>";
		
		$result.= "<columns>";
		foreach ( $res->getPrintRequests() as $pr ) {
			$result.= "<title>".$pr->getText( $outputmode, null)."</title>";
		}
		$result.= "</columns>";
		
		
		while ( $row = $res->getNext() ) {
			$result.= "<row>";
			foreach ( $row as $key => $field ) {
				$values = array();
				while ( ( $object = $field->getNextObject() ) !== false ) {
					if( ( $object->getTypeID() == '_wpg' ) || ( $object->getTypeID() == '__sin' ) ){
						$values[] = $object->getLongText( $outputmode, null );
					} else {
						$values[] = $object->getShortText( $outputmode, null );
					}
						
				}
				$result.= "<item>".urlencode(htmlspecialchars(implode(',', $values)))."</item>";
			}
			$result.= "</row>";
		}
		
		$result .= "</tixml>";

		return $result;
	}

}
