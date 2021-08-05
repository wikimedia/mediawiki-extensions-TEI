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
	 * @return StatusValue
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
		$conversion = $converter->convert( $status->getValue(), $title );

		$output->setText( Html::rawElement(
			'div', [ 'class' => 'mw-parser-output' ], $conversion->getHtml()
		) );
		foreach ( $conversion->getWarnings() as $warning ) {
			$output->addWarning( $warning );
		}
		foreach ( $conversion->getExternalLinksUrls() as $externalLink ) {
			$output->addExternalLink( $externalLink );
		}
		foreach ( $conversion->getIncludedFiles() as $file ) {
			$output->addImage( $file->getTitle()->getDBkey(), $file->getTimestamp(), $file->getSha1() );
		}
		$output->addModuleStyles( 'ext.tei.style' );
	}
}
