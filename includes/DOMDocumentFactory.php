<?php

namespace MediaWiki\Extension\Tei;

use DOMDocument;
use LibXMLError;
use RemexHtml\DOM\DOMBuilder;
use RemexHtml\Tokenizer\Tokenizer;
use RemexHtml\TreeBuilder\Dispatcher;
use RemexHtml\TreeBuilder\TreeBuilder;
use StatusValue;

/**
 * @license GPL-2.0-or-later
 *
 * Builds a DOMDocument from XML or HTML
 */
class DOMDocumentFactory {

	private static $skippedRemexErrorMessages = [ 'missing doctype' ];

	/**
	 * @param string $xml
	 * @return StatusValue
	 */
	public function buildFromXMLString( $xml ) {
		if ( $xml === '' ) {
			return StatusValue::newFatal( 'tei-libxml-empty-document' );
		}

		$oldUseInternalErrorsValue = libxml_use_internal_errors( true );

		$oldDisableEntityLoaderValue = libxml_disable_entity_loader( true );
		$dom = new DOMDocument( '1.0', 'UTF-8' );
		$dom->loadXML( $xml );
		libxml_disable_entity_loader( $oldDisableEntityLoaderValue );

		$status = StatusValue::newGood( $dom );

		// Handle errors
		foreach ( libxml_get_errors() as $error ) {
			$this->addLibXMLErrorToStatus( $error, $status );
		}
		libxml_clear_errors();

		libxml_use_internal_errors( $oldUseInternalErrorsValue );

		return $status;
	}

	/**
	 * @param string $html
	 * @return StatusValue
	 */
	public function buildFromHTMLString( $html ) {
		$status = StatusValue::newGood();

		$domBuilder = new DOMBuilder( [ 'errorCallback' => function ( $error, $pos ) use ( $status ) {
			if ( !in_array( $error, self::$skippedRemexErrorMessages ) ) {
				$status->error( 'tei-remex-error-message', $error, $pos );
			}
		} ] );

		( new Tokenizer( new Dispatcher( new TreeBuilder( $domBuilder ) ), $html, [] ) )->execute();
		$document = $domBuilder->getFragment();

		$status->setResult(
			$document instanceof DOMDocument && $document->documentElement !== null,
			$domBuilder->getFragment()
		);
		return $status;
	}

	private function addLibXMLErrorToStatus( LibXMLError $error, StatusValue $status ) {
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
}
