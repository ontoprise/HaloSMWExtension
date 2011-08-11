<?php

/**
 * @addtogroup API
 */
class ApiWOMSetObjectModel extends ApiBase {

	public function __construct( $main, $action ) {
		parent :: __construct( $main, $action );
	}

	public function execute() {
		global $wgUser;

		$params = $this->extractRequestParams();
		if ( is_null( $params['page'] ) )
			$this->dieUsage( 'Must specify page title', 0 );
		if ( is_null( $params['xpath'] ) )
			$this->dieUsage( 'Must specify xpath', 1 );

		$page = $params['page'];
		$verb = $params['verb'];
		$xpath = $params['xpath'];
		$value = $params['value'];
		$summary = $params['summary'];
		$rid = $params['rid'];
		$force_update = ( intval( $params['force_update'] ) == 1 );

		$articleTitle = Title::newFromText( $page );
		if ( !$articleTitle )
			$this->dieUsage( "Can't create title object ($page)", 3 );

		$errors = $articleTitle->getUserPermissionsErrors( 'edit', $wgUser );
		if ( !empty( $errors ) )
			$this->dieUsage( wfMsg( $errors[0][0], $errors[0][1] ), 5 );

		$article = new Article( $articleTitle );
		if ( !$article->exists() )
			$this->dieUsage( "Article doesn't exist ($page)", 4 );


		try {
			$objs = WOMProcessor::getObjIdByXPath( $articleTitle, $xpath, $rid );
			$oid = null;
			foreach ( $objs as $id ) {
				if ( $id != '' ) {
					$oid = $id;
					break;
				}
			}
			if ( $oid == null ) {
				throw new MWException( __METHOD__ . ": object does not found, xpath: {$xpath}" );
			}

			if ( $verb == 'remove' ) {
				WOMProcessor::removePageObject( $articleTitle, $oid, $summary, $rid, $force_update );
			} else if ( $verb == 'removeall' ) {
				$wom = WOMProcessor::getPageObject( $articleTitle, $rid );
				foreach ( $objs as $id ) {
					if ( $id == '' ) continue;
					$wom->removePageObject( $id );
				}
				if ( $rid > 0 ) {
					$revision = Revision::newFromTitle( $articleTitle );
					$id = $revision->getId();
					if ( $id != $rid && !$force_update ) {
						throw new MWException( __METHOD__ . ": Page revision id does not match '{$title} ({$rid}) - {$id}'" );
					}
				}
				// save to wiki
				$article = new Article( $articleTitle );
				$content = $wom->getWikiText();
				$article->doEdit( $content, $summary );

			} else {
				if ( is_null( $params['value'] ) )
					$this->dieUsage( 'Must specify value', 2 );

				if ( $verb == 'insert' ) {
					WOMProcessor::insertPageText( $value, $articleTitle, $oid, $summary, $rid, $force_update );
				} else if ( $verb == 'update' ) {
					WOMProcessor::updatePageText( $value, $articleTitle, $oid, $summary, $rid, $force_update );
				} else if ( $verb == 'append' ) {
					WOMProcessor::appendPageText( $value, $articleTitle, $oid, $summary, $rid, $force_update );
				} else if ( $verb == 'attribute' ) {
					$wom = WOMProcessor::getPageObject( $articleTitle, $rid );
					$obj = $wom->getObject( $oid );
					$kv = explode( '=', $value, 2 );
					if ( count( $kv ) != 2 ) {
						throw new MWException( __METHOD__ . ": value should be 'key=value' in attribute mode" );
					}
					$obj->setXMLAttribute( trim( $kv[0] ), trim( $kv[1] ) );
					// save to wiki
					$article = new Article( $articleTitle );
					$content = $wom->getWikiText();
					$article->doEdit( $content, $summary );
				}
			}
		} catch ( Exception $e ) {
			$err = $e->getMessage();
		}

		$result = array();

		if ( isset( $err ) ) {
			$result = array(
				'result' => 'Failure',
				'message' => array(),
			);
			$this->getResult()->setContent( $result['message'], $err );
		} else {
			$result['result'] = 'Success';
		}

		$this->getResult()->addValue( null, $this->getModuleName(), $result );
	}


	protected function getAllowedParams() {
		return array (
			'page' => null,
			'verb' => array(
				ApiBase :: PARAM_DFLT => 'update',
				ApiBase :: PARAM_TYPE => array(
					'update',
					'attribute',
					'insert',
					'append',
					'remove',
					'removeall',
				),
			),
			'xpath' => null,
			'value' => null,
			'summary' => array(
				ApiBase :: PARAM_DFLT => ''
			),
			'rid' => array (
            	ApiBase :: PARAM_TYPE => 'integer',
                ApiBase :: PARAM_DFLT => 0,
                ApiBase :: PARAM_MIN => 0
            ),
			'force_update' => array(
				ApiBase :: PARAM_DFLT => '1',
				ApiBase :: PARAM_TYPE => array(
					'1',
					'0',
				),
			),
		);
	}

	protected function getParamDescription() {
		return array (
			'page' => 'Title of the page to modify',
			'verb' => 'Action verb to set to change wiki object instances',
			'xpath' => array(
				'DOM-like xpath to locate WOM object instances (http://www.w3schools.com/xpath/xpath_syntax.asp)',
				'verb = update, xpath to elements to be updated',
				'verb = attribute, xpath to elements, the attribute will be updated',
				'verb = insert, the element will be inserted right before the element specified by xpath',
				'verb = append, the element will be appended right to the element children elements specified by xpath',
				'verb = remove, xpath to element to be removed',
				'verb = removeall, xpath to elements to be removed',
			),
			'value' => array(
				'Value to set',
				'verb = attribute, attribute_name=attribute_value',
			),
			'summary' => 'Edit summary',
			'rid' => 'Revision id of specified page - by dafault latest updated revision (0) is used',
			'force_update' => array(
				'Force to update even if the revision id does not match the latest edition',
				'force_update = 0, return "revision not match" exception if rid is not the latest one',
				'force_update = 1, update anyway',
			),
		);
	}

	protected function getDescription() {
		return 'Call to set object values to MW page, by Wiki Object Model';
	}

	protected function getExamples() {
		return array (
			'api.php?action=womset&page=Somepage&xpath=//template[@name=SomeTempate]/template_field[@key=templateparam]&value=It+works!&summary=Editing+template+param+using+Wiki+Object+Model'
		);
	}

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}
}
