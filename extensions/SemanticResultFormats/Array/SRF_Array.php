<?php
/**
 * Query format for arrays with features for Extensions 'Arrays' and 'HashTables'
 * @file
 * @ingroup SemanticResultFormats
 * @author Daniel Werner < danweetz@web.de >
 * 
 * Doesn't require 'Arrays' nor 'HashTables' exytensions but has additional features
 * ('name' parameter in either result format) if they are available.
 * 
 * Arrays 2.0+ and HashTables 1.0+ are recommended but not necessary.
 */

/**
 * Array format
 */
class SRFArray extends SMWResultPrinter {
	
	protected static $mDefaultSeps = array();
	protected $mSep;
	protected $mPropSep;
	protected $mManySep;
	protected $mRecordSep;
	protected $mHeaderSep;
	protected $mArrayName = null;
	protected $mShowPageTitles;
	
	protected $mHideRecordGaps;
	protected $mHidePropertyGaps;
	
	/**
	 * @var Boolean true if 'mainlabel' parameter is set to '-'
	 */
	protected $mMainLabelHack = false;
	
	public function __construct( $format, $inline, $useValidator = true ) {
		parent::__construct( $format, $inline, $useValidator );
		//overwrite parent default behavior for linking:
		$this->mLinkFirst = false;
		$this->mLinkOthers = false;
	}

	public function getQueryMode($context) {
		return SMWQuery::MODE_INSTANCES;
	}

	public function getName() {
		return wfMsg( 'srf_printername_' . $this->mFormat );
	}
	
	/*
	// By overwriting this function, we disable default searchlabel handling?
	public function getResult( SMWQueryResult $results, array $params, $outputmode ) {
		$this->handleParameters( $params, $outputmode );
		return $this->getResultText( $results, $outputmode );
	}
	*/

	protected function getResultText( SMWQueryResult $res, $outputmode ) {
		/*
		 * @ToDo:
		 * labels of requested properties could define default values. Seems not possible at the moment because
		 * SMWPrintRequest::getLable() always returns the property name even if no specific label is defined.
		 */
		 
		$perPage_items = array();
		
		//for each page:
		while( $row = $res->getNext() ) {
			$perProperty_items = array();
			
			/**
			 * first field is always the page title, except, mainlabel is set to '-'
			 * @ToDo: Is there some other way to check the data value directly for being the
			 *        page title or not? SMWs behavior could change on mainlabel handling...
			 */
			$isPageTitle = !$this->mMainLabelHack;
			
			//for each property on that page:
			foreach( $row as $field ) { // $row is array(), $field of type SMWResultArray
				$manyValue_items = array();
				$isMissingProperty = false;
				
				$manyValues = $field->getContent();
				
				//If property is not set (has no value) on a page:
				if( empty( $manyValues ) ) {
					$delivery = $this->deliverMissingProperty( $field );
					$manyValue_items = $this->fillDeliveryArray( $manyValue_items, $delivery );
					$isMissingProperty = true;
				} else
				//otherwise collect property value (potentially many values):
				while( $obj = $field->getNextDataValue() ) {
					
					$value_items = array();					
					$isRecord = false;
					
					// handle page Title:
					if( $isPageTitle ) {						
						if( ! $this->mShowPageTitles ) {
							$isPageTitle = false;
							continue 2; //next property
						}						
						$value_items = $this->fillDeliveryArray( $value_items, $this->deliverPageTitle( $obj, $this->mLinkFirst ) );
					}
					// handle record values:
					elseif( $obj instanceof SMWRecordValue ) {												
						$recordItems = $obj->getDataItems();
						// walk all single values of the record set:
						foreach( $recordItems as $dataItem ) {							
							$recordField = $dataItem !== null ? SMWDataValueFactory::newDataItemValue( $dataItem, null ) : null;
							$value_items = $this->fillDeliveryArray( $value_items, $this->deliverRecordField( $recordField, $this->mLinkOthers ) );
						}
						$isRecord = true;
					}
					// handle normal data values:
					else {						
						$value_items = $this->fillDeliveryArray( $value_items, $this->deliverSingleValue( $obj, $this->mLinkOthers ) );
					}
					$delivery = $this->deliverSingleManyValuesData( $value_items, $isRecord, $isPageTitle );
					$manyValue_items = $this->fillDeliveryArray( $manyValue_items, $delivery );
				} // foreach...
				$delivery = $this->deliverPropertiesManyValues( $manyValue_items, $isMissingProperty, $isPageTitle, $field );
				$perProperty_items = $this->fillDeliveryArray( $perProperty_items, $delivery );
				$isPageTitle = false; // next one could be record or normal value
			} // foreach...			
			$delivery = $this->deliverPageProperties( $perProperty_items );
			$perPage_items = $this->fillDeliveryArray( $perPage_items, $delivery );
		} // while...

		$output = $this->deliverQueryResultPages( $perPage_items );
		
		return $output;
	}
	
	protected function fillDeliveryArray( $array = array(), $value = null ) {
		if( ! is_null( $value ) ) { //don't create any empty entries
			$array[] = $value;
		}
		return $array;
	}

	protected function deliverPageTitle( $value, $link = false ) {
		return $this->deliverSingleValue( $value, $link );
	}
	protected function deliverRecordField( $value, $link = false ) {
		if( $value !== null ) // contains value
			return $this->deliverSingleValue( $value, $link );
		elseif( $this->mHideRecordGaps )
			return null; // hide gap
		else
			return ''; // empty string will make sure that record value separators are generated
	}
	protected function deliverSingleValue( $value, $link = false ) {
		//return trim( $value->getShortWikiText( $link ) );
		return trim( Sanitizer::decodeCharReferences( $value->getShortWikiText( $link ) ) ); // decode: better for further processing with array extension
	}
	// Property not declared on a page:
	protected function deliverMissingProperty( SMWResultArray $field ) {
		if( $this->mHidePropertyGaps )
			return null;
		else
			return ''; //empty string will make sure that array separator will be generated
			/** @ToDo: System for Default values?... **/
	}
	//represented by an array of record fields or just a single array value:
	protected function deliverSingleManyValuesData( $value_items, $containsRecord, $isPageTitle ) {
		if( empty( $value_items ) ) //happens when one of the higher functions delivers null
			return null;
		return implode( $this->mRecordSep, $value_items );
	}
	protected function deliverPropertiesManyValues( $manyValue_items, $isMissingProperty, $isPageTitle, SMWResultArray $data ) {
		if( empty( $manyValue_items ) )
			return null;
		
		$text = implode( $this->mManySep, $manyValue_items );
		
		// if property names should be displayed and this is not the page titles value:
		if(  $this->mShowHeaders != SMW_HEADERS_HIDE && ! $isPageTitle ) {
			$linker = $this->mShowHeaders == SMW_HEADERS_PLAIN ? null : $this->mLinker;
			$text = $data->getPrintRequest()->getText( SMW_OUTPUT_WIKI, $linker ) . $this->mHeaderSep . $text;
		}
		return $text;
	}
	protected function deliverPageProperties( $perProperty_items ) {
		if( empty( $perProperty_items ) )
			return null;
		return implode( $this->mPropSep, $perProperty_items );
	}
	protected function deliverQueryResultPages( $perPage_items ) {
		if( $this->mArrayName !== null ) {
			$this->createArray( $perPage_items ); //create Array
			return '';
		} else {
			return implode( $this->mSep, $perPage_items );
		}
	}
	
	/**
	 * Helper function to create a new Array within 'Arrays' extension. Takes care of different versions
	 * as well as the old 'ArrayExtension'.
	 */
	protected function createArray( $array ) {
		global $wgArrayExtension;
		
		$arrayId = $this->mArrayName;
		
		if( defined( 'ExtArrays::VERSION' ) ) {
			// 'Arrays' extension 2+
			global $wgParser; /** ToDo: is there a way to get the actual parser which has started the query? */
			ExtArrays::get( $wgParser )->createArray( $arrayId, $array );
			return true;
		}
		
		// compatbility to 'ArrayExtension' extension before 2.0:
		
		if( ! isset( $wgArrayExtension ) ) {
			//Hash extension is not installed in this wiki
			return false;
		}
		$version = null;		
		if( defined( 'ArrayExtension::VERSION' ) ) {
			$version = ExtArrayExtension::VERSION;
		} elseif( defined( 'ExtArrayExtension::VERSION' ) ) {
			$version = ExtArrayExtension::VERSION;
		}
		if( $version !== null && version_compare( $version, '1.3.2', '>=' ) ) {
			// ArrayExtension 1.3.2+
			$wgArrayExtension->createArray( $arrayId, $array );
		} else {
			// dirty way
			$wgArrayExtension->mArrays[ trim( $arrayId ) ] = $array;
		}
		return true;
	}
	
	protected function initializeCfgValue( $dfltVal, $dfltCacheKey ) {		
		$cache = &self::$mDefaultSeps[ $dfltCacheKey ];
		if( ! isset( $cache ) ) {
			$cache = $this->getCfgSepText( $dfltVal );			
			if( $cache === null ) {
				// cache can't be initialized, propably function-reference in userconfig
				// but format is not used in inline context, use fallback in this case:
				global $srfgArraySepTextualFallbacks;
				$cache = $srfgArraySepTextualFallbacks[ $dfltCacheKey ];
			}
		}
		return $cache;
	}
	protected function getCfgSepText( $obj ) {		
		if( is_array( $obj ) ) {
			// invalid definition:
			if( ! array_key_exists( 0, $obj ) )
				return null;

			// check for config-defined arguments to pass to the page before processing it:			
			if( array_key_exists( 'args', $obj ) && is_array( $obj['args'] ) )
				$params = $obj['args'];
			else
				$params = array(); // no arguments
			
			// create title of page whose text should be used as separator:
			$obj = Title::newFromText( $obj[0], ( array_key_exists( 1, $obj ) ? $obj[1] : NS_MAIN ) );
		}
		if( $obj instanceof Title ) {
			$article = new Article( $obj );
		} elseif( $obj instanceof Article ) {
			$article = obj;
		} else {
			return $obj; //only text
		}
		
		global $wgParser;		
		/*
		 * Feature to use page value as separator only works if Parser::parse() is running!
		 * That's not the case on semantic search special page for example!
		 */
		// can't use $this->mInline here since SMW 1.6.2 had a bug setting it to false in most cases!		
		if( ! isset( $wgParser->mOptions ) ) {
		//if( ! $this->mInline ) {
			return null;
		}
		
		/*
		 * parse page as if it were included like a template. Never use Parser::recursiveTagParse() or similar 
		 * for this since it would call hooks we don't want to call and won't return wiki text for inclusion!
		 */
		$frame = $wgParser->getPreprocessor()->newCustomFrame( $params );		
		$text = $wgParser->preprocessToDom( $article->getRawText(), Parser::PTD_FOR_INCLUSION );
		$text = trim( $frame->expand( $text ) );
		
		return $text;
	}
	
	protected function handleParameters( array $params, $outputmode ) {
		// does the link parameter:
		parent::handleParameters( $params, $outputmode );
		
		$wgParser;
		//die( isset( $wgParser->mOptions ) );
				
		//separators:
		$this->mSep       = $params['sep'];
		$this->mPropSep   = $params['propsep'];
		$this->mManySep   = $params['manysep'];
		$this->mRecordSep = $params['recordsep'];
		$this->mHeaderSep = $params['headersep'];
		
		// only use this in inline mode, if text is given. Since SMW 1.6.2 '' is given, so if
		// we wouldn't check, we would always end up with an array instead of visible output
		if( $params['name'] !== false && ( $this->mInline || trim( $params['name'] ) !== '' ) ) {
			$this->mArrayName = trim( $params['name'] );
			$this->createArray( array() ); //create empty array in case we get no result so we won't have an undefined array in the end.
		}
		
		// if mainlabel set to '-', this will cause the titles not to appear, so make sure we catch this!
		$this->mMainLabelHack = trim( $params['mainlabel'] ) === '-';
		
		// whether or not to display the page title:
		$this->mShowPageTitles = strtolower( $params['titles'] ) != 'hide';
		
		switch( strtolower( $params['hidegaps'] ) ) {
			case 'none':
				$this->mHideRecordGaps = false;
				$this->mHidePropertyGaps = false;
				break;
			case 'all':
				$this->mHideRecordGaps = true;
				$this->mHidePropertyGaps = true;
				break;
			case 'property': case 'prop': case 'attribute': case 'attr':
				$this->mHideRecordGaps = false;
				$this->mHidePropertyGaps = true;
				break;
			case 'record': case 'rec': case 'rcrd': case 'n-ary': case 'nary':
				$this->mHideRecordGaps = true;
				$this->mHidePropertyGaps = false;
				break;
		}
	}
	
	public function getParameters() {		
		global $smwgQMaxInlineLimit;

		$params = array();
		$dfltParams = SMWQueryProcessor::getParameters();
		
		### adjusted basic SMW params: ###
		
		$params['limit'] = $dfltParams['limit'];
		$params['limit']->setDefault( $smwgQMaxInlineLimit );
		
		$params['link'] = $dfltParams['link'];
		$params['link']->setDefault( 'none' );
		
		$params['headers'] = $dfltParams['headers'];
		$params['headers']->setDefault( 'hide' );
		
		### new params: ###
		
		$params['titles'] = new Parameter( 'titles' );
		$params['titles']->setMessage( 'srf_paramdesc_pagetitle' );
		$params['titles']->addCriteria( new CriterionInArray( 'show', 'hide' ) );
		$params['titles']->addAliases( 'pagetitle', 'pagetitles' );
		$params['titles']->setDefault( 'show' );
		
		$params['hidegaps'] = new Parameter( 'hidegaps' );
		$params['hidegaps']->setMessage( 'srf_paramdesc_hidegaps' );
		$params['hidegaps']->addCriteria( new CriterionInArray( 'none', 'all', 'property', 'record' ) );
		$params['hidegaps']->setDefault( 'none' );
		
		# name to create 'real' array with if set (empty string '' counts as set!):
		$params['name'] = new Parameter( 'name' );
		$params['name']->setMessage( 'srf_paramdesc_arrayname' );
		$params['name']->setDefault( false, false );
		
		# separators (default values are defined in the following globals:)
		global $srfgArraySep, $srfgArrayPropSep, $srfgArrayManySep, $srfgArrayRecordSep, $srfgArrayHeaderSep;
		
		$params['sep'] = new Parameter( 'sep' );
		$params['sep']->setMessage( 'smw_paramdesc_sep' );
		$params['sep']->setDefault( $this->initializeCfgValue( $srfgArraySep, 'sep' ) );
		
		$params['propsep'] = new Parameter( 'propsep' );
		$params['propsep']->setMessage( 'srf_paramdesc_propsep' );
		$params['propsep']->setDefault( $this->initializeCfgValue( $srfgArrayPropSep, 'propsep' ) );
		
		$params['manysep'] = new Parameter( 'manysep' );
		$params['manysep']->setMessage( 'srf_paramdesc_manysep' );
		$params['manysep']->setDefault( $this->initializeCfgValue( $srfgArrayManySep, 'manysep' ) );
		
		$params['recordsep'] = new Parameter( 'recordsep' );
		$params['recordsep']->setMessage( 'srf_paramdesc_recordsep' );
		$params['recordsep']->addAliases( 'narysep', 'rcrdsep', 'recsep' );
		$params['recordsep']->setDefault( $this->initializeCfgValue( $srfgArrayRecordSep, 'recordsep' ) );
		
		$params['headersep'] = new Parameter( 'headersep' );
		$params['headersep']->setMessage( 'srf_paramdesc_headersep' );
		$params['headersep']->addAliases( 'narysep', 'rcrdsep', 'recsep' );
		$params['headersep']->setDefault( $this->initializeCfgValue( $srfgArrayHeaderSep, 'headersep' ) );
		
		return $params;
	}
}

class SRFHash extends SRFArray {	
	protected $mLastPageTitle;
	
	protected function deliverPageTitle( $value, $link = false ) {
		$this->mLastPageTitle = $this->deliverSingleValue( $value, $link ); //remember the page title
		return null; //don't add page title into property list
	}
	protected function deliverPageProperties( $perProperty_items ) {
		if( count( $perProperty_items ) < 1 )
			return null;
		return array( $this->mLastPageTitle, implode( $this->mPropSep, $perProperty_items ) );
	}
	protected function deliverQueryResultPages( $perPage_items ) {
		$hash = array();
		foreach( $perPage_items as $page ) {
			$hash[ $page[0] ] = $page[1];  //name of page as key, Properties as value
		}
		return parent::deliverQueryResultPages( $hash );
	}
	
	/**
	 * @ToDo: adjust for HashTables 0.8
	 */
	protected function createArray( $hash ) {
		global $wgHashTables;
		
		$hashId = $this->mArrayName;
		$version = null;
		if( defined( 'ExtHashTables::VERSION' ) ) {
			$version = ExtHashTables::VERSION;
		}
		if( $version !== null && version_compare( $version, '0.999', '>=' ) )	{
			// Version 1.0+, doesn't use $wgHashTables anymore
			global $wgParser; /** ToDo: is there a way to get the actual parser which has started the query? */
			ExtHashTables::get( $wgParser )->createHash( $hashId, $hash );
		}		
		elseif( ! isset( $wgHashTables ) ) {
			//Hash extension is not installed in this wiki
			return false;
		}
		elseif( $version !== null && version_compare( $version, '0.6', '>=' ) )	{
			// HashTables 0.6 to 1.0
			$wgHashTables->createHash( $hashId, $hash );
		}
		else {
			// old HashTables, dirty way
			$wgHashTables->mHashTables[ trim( $hashId ) ] = $hash;
		}
		return true;
	}
	
	protected function handleParameters( array $params, $outputmode ) {
		parent::handleParameters( $params, $outputmode );
		$this->mShowPageTitles = true;
	}
	
	public function getParameters() {
		$params = parent::getParameters();
		
		unset( $params['pagetitle'] ); // page title is Hash key, otherwise, just use Array format!
		$params['name']->setMessage( 'srf_paramdesc_hashname' );
		
		return $params;
	}
}
