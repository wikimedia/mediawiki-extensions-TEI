<?php

namespace MediaWiki\Extension\Tei\Api;

use ApiBase;
use ApiMain;
use ApiUsageException;
use MediaWiki\Extension\Tei\DOMDocumentFactory;
use MediaWiki\Extension\Tei\Model\Validator;
use MediaWiki\Extension\Tei\TeiExtension;

/**
 * @license GPL-2.0-or-later
 *
 * API endpoint to convert TEI content
 */
class ApiTeiValidate extends ApiBase {

	/**
	 * @var DOMDocumentFactory
	 */
	private $domDocumentFactory;

	/**
	 * @var Validator
	 */
	private $validator;

	/**
	 * ApiConvertTei constructor.
	 * @param ApiMain $mainModule
	 * @param string $moduleName
	 * @param string $modulePrefix
	 */
	public function __construct( ApiMain $mainModule, $moduleName, $modulePrefix = '' ) {
		parent::__construct( $mainModule, $moduleName, $modulePrefix );
		$this->domDocumentFactory = TeiExtension::getDefault()->getDOMDocumentFactory();
		$this->validator = TeiExtension::getDefault()->getValidator();
	}

	/**
	 * @return bool
	 */
	public function isInternal() {
		return true;
	}

	/**
	 * @throws ApiUsageException
	 */
	public function execute() {
		$params = $this->extractRequestParams();

		$status = $this->domDocumentFactory->buildFromXMLString( $params['text'] );
		if ( $status->isOK() ) {
			$status->merge( $this->validator->validateDOM( $status->getValue() ) );
		}

		$errors = array_values( array_unique( array_map( function ( $error ) {
			return [
				'type' => $error['type'],
				'message' => $this->msg( $error['message'], ...$error['params'] )->plain(),
				'line' => is_int( end( $error['params'] ) ) ? end( $error['params'] ) : null
			];
		}, $status->getErrors() ) ) );

		$this->getResult()->addValue( null, 'validation', $errors );
	}

	/**
	 * @return array
	 */
	public function getAllowedParams() {
		return [
			'text' => [
				ApiBase::PARAM_TYPE => 'text',
				ApiBase::PARAM_REQUIRED => true
			]
		];
	}

	/**
	 * @return array
	 */
	protected function getExamplesMessages() {
		return [];
	}
}
