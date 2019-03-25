<?php

namespace MediaWiki\Extension\Tei;

use DOMDocument;
use Html;
use LibXMLError;
use MWException;
use ParserOptions;
use ParserOutput;
use Status;
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
	 * @var DOMDocument|null
	 */
	private $domDocument;

	/**
	 * @var LibXMLError[]|null
	 */
	private $xmlValidationErrors;

	/**
	 * @param string $text
	 * @param string $modelId
	 * @throws MWException
	 */
	public function __construct( $text, $modelId = CONTENT_MODEL_TEI ) {
		parent::__construct( $text, $modelId );
	}

	/**
	 * @return DOMDocument
	 */
	public function domDocument() {
		if ( $this->domDocument === null ) {
			$this->domDocument = $this->buildDomDocument();
		}
		return $this->domDocument;
	}

	private function buildDomDocument() {
		$oldUseInternalErrorsValue = libxml_use_internal_errors( true );

		$oldDisableEntityLoaderValue = libxml_disable_entity_loader( true );
		$dom = new DOMDocument( '1.0', 'UTF-8' );
		$dom->loadXML( $this->getText() );
		libxml_disable_entity_loader( $oldDisableEntityLoaderValue );

		// We put the parsing error and warnings in the relevant property
		$errors = libxml_get_errors();
		libxml_clear_errors();
		$this->xmlValidationErrors = $errors;

		libxml_use_internal_errors( $oldUseInternalErrorsValue );

		return $dom;
	}

	/**
	 * @see Content::isValid
	 *
	 * @return bool
	 */
	public function isValid() {
		return empty( $this->xmlValidationErrors() );
	}

	private function xmlValidationErrors() {
		$this->domDocument();
		return $this->xmlValidationErrors;
	}

	private function addLibxmlErrorToStatus( LibXMLError $error, Status $status ) {
		switch ( $error->level ) {
			case LIBXML_ERR_WARNING:
				$status->warning( 'tei-libxml-error-message', trim( $error->message ), $error->line );
				break;
			case LIBXML_ERR_ERROR:
				$status->error( 'tei-libxml-error-message', trim( $error->message ), $error->line );
				break;
			case LIBXML_ERR_FATAL:
				$status->fatal( 'tei-libxml-error-message', trim( $error->message ), $error->line );
				break;
		}
		return $status;
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
		if ( !$this->isValid() ) {
			return $this;
		}

		$dom = $this->domDocument();
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
		return $this->xmlValidationStatus();
	}

	private function xmlValidationStatus() {
		$status = Status::newGood();
		foreach ( $this->xmlValidationErrors() as $error ) {
			$this->addLibxmlErrorToStatus( $error, $status );
		}
		return $status;
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
		$status = $this->xmlValidationStatus();
		if ( !$status->isOK() ) {
			$output->setText( Html::rawElement( 'div', [ 'class' => 'error' ],  $status->getHTML() ) );
			return;
		}

		$dom = $this->domDocument();
		$output->setText( Html::element( 'pre', [], $dom->saveHTML( $dom->documentElement ) ) );
	}
}
