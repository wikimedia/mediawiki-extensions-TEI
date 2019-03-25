<?php

namespace MediaWiki\Extension\Tei;

use Content;
use TextContentHandler;

/**
 * @license GPL-2.0-or-later
 *
 * Content handler for TEI
 */
class TeiContentHandler extends TextContentHandler {

	/**
	 * @param string $modelId
	 */
	public function __construct( $modelId = CONTENT_MODEL_TEI ) {
		parent::__construct( $modelId, [ CONTENT_FORMAT_TEI_XML, CONTENT_FORMAT_XML ] );
	}

	/**
	 * @see TextContentHandler::getContentClass
	 *
	 * @return string
	 */
	protected function getContentClass() {
		return TeiContent::class;
	}

	/**
	 * @see ContentHandler::makeEmptyContent
	 *
	 * @return Content
	 */
	public function makeEmptyContent() {
		$class = $this->getContentClass();
		return new $class( '<text><body><p></p></body></text>' );
	}
}
