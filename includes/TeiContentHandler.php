<?php

namespace MediaWiki\Extension\Tei;

use Content;
use MediaWiki\Content\Transform\PreSaveTransformParams;
use MWException;
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
	 * @return TeiContent
	 */
	public function makeEmptyContent() {
		return new TeiContent(
			'<text xmlns="http://www.tei-c.org/ns/1.0"><body><p></p></body></text>'
		);
	}

	/**
	 * @see ContentHandler::preSaveTransform
	 *
	 * @param Content $content
	 * @param PreSaveTransformParams $pstParams
	 * @return TeiContent
	 * @throws MWException
	 */
	public function preSaveTransform( Content $content, PreSaveTransformParams $pstParams ): Content {
		'@phan-var TeiContent $content';
		$status = $content->getDOMDocumentStatus();

		if ( !$status->isOK() ) {
			return $content;
		}

		$dom = $status->getValue();
		TeiExtension::getDefault()->getNormalizer()->normalizeDOM( $dom );

		$contentClass = $this->getContentClass();
		return new $contentClass( $dom->saveXML( $dom->documentElement ) );
	}
}
