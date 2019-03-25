<?php

namespace MediaWiki\Extension\Tei;

use DOMDocument;
use LibXMLError;
use StatusValue;

/**
 * @license GPL-2.0-or-later
 *
 * Builds a DOMDocument from XML or HTML
 */
class DOMDocumentFactory {

	/**
	 * @param string $xml
	 * @return StatusValue
	 */
	public function buildFromXMLString( $xml ) {
		if ( $xml === '' ) {
			return StatusValue::newFatal( 'tei-libxml-empty-document' );
		}

		return $this->safeDOMDocumentParsing( function ( DOMDocument $dom ) use ( $xml ) {
			$dom->loadXML( $xml );
		} );
	}

	/**
	 * @param string $html
	 * @return StatusValue
	 */
	public function buildFromHTMLString( $html ) {
		if ( $html === '' ) {
			return StatusValue::newFatal( 'tei-libxml-empty-document' );
		}

		return $this->safeDOMDocumentParsing( function ( DOMDocument $dom ) use ( $html ) {
			$dom->loadHTML( $html );
		} );
	}

	private function safeDOMDocumentParsing( callable $loadFunction ) {
		$oldUseInternalErrorsValue = libxml_use_internal_errors( true );

		$oldDisableEntityLoaderValue = libxml_disable_entity_loader( true );
		$dom = new DOMDocument( '1.0', 'UTF-8' );
		$loadFunction( $dom );
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
