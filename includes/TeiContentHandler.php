<?php

namespace MediaWiki\Extension\Tei;

use Content;
use Html;
use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\Content\Transform\PreSaveTransformParams;
use MediaWiki\Content\ValidationParams;
use MWException;
use ParserOutput;
use Status;
use StatusValue;
use TextContentHandler;
use Title;

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

	/**
	 * @param Content $content
	 * @param ContentParseParams $cpoParams
	 * @param ParserOutput &$output
	 */
	public function fillParserOutput(
		Content $content, ContentParseParams $cpoParams, ParserOutput &$output
	) {
		/** @var TeiContent $content */
		'@phan-var TeiContent $content';
		$status = $content->getDOMDocumentStatus();
		if ( !$status->isOK() ) {
			$output->setText( Html::rawElement(
				'div', [ 'class' => 'error' ], Status::wrap( $status )->getHTML()
			) );
			return;
		}

		$converter = TeiExtension::getDefault()->getTeiToHtmlConverter();
		$conversion = $converter->convert( $status->getValue(),
			Title::castFromPageReference( $cpoParams->getPage() ) );

		$output->setText( Html::rawElement(
			'div', [ 'class' => 'mw-parser-output' ], $conversion->getHtml()
		) );
		foreach ( $conversion->getWarnings() as $warningArgs ) {
			$output->addWarningMsg( ...$warningArgs );
		}
		foreach ( $conversion->getExternalLinksUrls() as $externalLink ) {
			$output->addExternalLink( $externalLink );
		}
		foreach ( $conversion->getIncludedFiles() as $file ) {
			$output->addImage( $file->getTitle()->getDBkey(), $file->getTimestamp(), $file->getSha1() );
		}
		$output->addModuleStyles( [ 'ext.tei.style' ] );
	}

	/**
	 * @param Content $content
	 * @param ValidationParams $validationParams
	 * @return StatusValue
	 */
	public function validateSave( Content $content, ValidationParams $validationParams ) {
		/** @var TeiContent $content */
		'@phan-var TeiContent $content';
		return $content->validateContent();
	}
}
