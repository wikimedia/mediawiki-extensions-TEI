<?php

namespace MediaWiki\Extension\Tei;

use Html;
use MWException;
use ParserOptions;
use ParserOutput;
use Status;
use StatusValue;
use TextContent;
use Title;
use User;
use WikiPage;

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
	 * @return StatusValue[DOMDocument]
	 */
	public function getDOMDocumentStatus() {
		return TeiExtension::getDefault()->getDOMDocumentFactory()
			->buildFromXMLString( $this->getText() );
	}

	private function validateContent() {
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

	/**
	 * @see Content::preSaveTransform
	 *
	 * @param Title $title
	 * @param User $user
	 * @param ParserOptions $popts
	 * @return TeiContent
	 * @throws MWException
	 */
	public function preSaveTransform( Title $title, User $user, ParserOptions $popts ) {
		$status = $this->getDOMDocumentStatus();

		if ( !$status->isOK() ) {
			return $this;
		}

		$dom = $status->getValue();
		TeiExtension::getDefault()->getNormalizer()->normalizeDOM( $dom );

		return new self( $dom->saveXML( $dom->documentElement ) );
	}

	/**
	 * @see Content::prepareSave
	 *
	 * @param WikiPage $page
	 * @param int $flags
	 * @param int $parentRevId
	 * @param User $user
	 * @return Status
	 */
	public function prepareSave( WikiPage $page, $flags, $parentRevId, User $user ) {
		return Status::wrap( $this->validateContent() );
	}

	/**
	 * @see TextContent::fillParserOutput
	 *
	 * @param Title $title
	 * @param int $revId
	 * @param ParserOptions $options
	 * @param bool $generateHtml
	 * @param ParserOutput &$output
	 */
	protected function fillParserOutput(
		Title $title, $revId, ParserOptions $options, $generateHtml, ParserOutput &$output
	) {
		$status = $this->getDOMDocumentStatus();
		if ( !$status->isOK() ) {
			$output->setText( Html::rawElement(
				'div', [ 'class' => 'error' ], Status::wrap( $status )->getHTML()
			) );
			return;
		}

		$converter = TeiExtension::getDefault()->getTeiToHtmlConverter();
		$html = $converter->convertToHtmlBodyContent( $status->getValue(), $title );
		$output->setText( Html::rawElement( 'div', [ 'class' => 'mw-parser-output' ], $html ) );
	}
}
