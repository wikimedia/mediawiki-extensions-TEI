<?php

namespace MediaWiki\Extension\Tei;

use MWException;
use StatusValue;
use TextContent;

/**
 * @license GPL-2.0-or-later
 *
 * TEI content
 */
class TeiContent extends TextContent {

	/**
	 * @param string $text
	 * @param string $modelId
	 * @throws MWException
	 */
	public function __construct( $text, $modelId = CONTENT_MODEL_TEI ) {
		parent::__construct( $text, $modelId );
	}

	/**
	 * @return StatusValue
	 */
	public function getDOMDocumentStatus() {
		return TeiExtension::getDefault()->getDOMDocumentFactory()
			->buildFromXMLString( $this->getText() );
	}

	public function validateContent() {
		$status = $this->getDOMDocumentStatus();
		if ( $status->isOK() ) {
			$status->merge(
				TeiExtension::getDefault()->getValidator()->validateDOM( $status->getValue() )
			);
		}
		return $status;
	}

	/**
	 * @see Content::isValid
	 *
	 * @return bool
	 */
	public function isValid() {
		return $this->validateContent()->isGood();
	}
}
