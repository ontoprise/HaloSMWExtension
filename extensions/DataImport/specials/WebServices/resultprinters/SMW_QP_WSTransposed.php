<?php

class SMWQPWSTransposed extends SMWResultPrinter {

	protected $mSep = ',';
	protected $mTemplate = false;

	protected function readParameters( $params, $outputmode ) {
		SMWResultPrinter::readParameters( $params, $outputmode );

		if ( array_key_exists( 'sep', $params ) ) {
			$this->mSep = $params['sep'];
		}

		if ( array_key_exists( 'template', $params ) ) {
			$this->mTemplate = trim( $params['template'] );
		}

	}

	protected function getResultText( $res, $outputmode ) {

		//transpose result
		$results = array();
		while ( $row = $res->getNext() ) {
			foreach ( $row as $key => $field ) {
				while ( ( $object = $field->getNextObject() ) !== false ) {
					if( ( $object->getTypeID() == '_wpg' ) || ( $object->getTypeID() == '__sin' ) ){
						$value = $object->getLongText( $outputmode, null );
					} else {
						$value = $object->getShortText( $outputmode, null );
					}
						
					$results[$key][] = $value;
				}
			}
		}

		$result = "";

		$result .= $this->mTemplate ? '{{'.$this->mTemplate : '';

		$key = 0;
		foreach($results as $values){
			$key += 1;
			$result .= $this->mTemplate ? '| '.$key.'=' : '';
			$result .= implode($this->mSep, $values);
			$result .= $this->mTemplate ? '' : '<br/><br/>';
		}

		$result .= $this->mTemplate ? '}}' : '';

		//do not display further results for this one
		//		if ( $this->linkFurtherResults( $res)){ //] && $this->getSearchLabel( SMW_OUTPUT_WIKI )) {
		//			$link = $res->getQueryLink();
		//			if ( $this->getSearchLabel( SMW_OUTPUT_WIKI ) ) {
		//				$link->setCaption( $this->getSearchLabel( SMW_OUTPUT_WIKI ) );
		//			}
		//
		//			$result .= $rowstart . $link->getText( SMW_OUTPUT_WIKI, $this->mLinker ) . $rowend . "\n";
		//		}

		return $result;
	}

}
