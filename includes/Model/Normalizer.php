<?php

namespace MediaWiki\Extension\Tei\Model;

use DOMDocument;

/**
 * Normalizes a TEI document
 *
 * @license GPL-2.0-or-later
 * @author  Thomas Pellissier Tanon
 */
class Normalizer {

	/**
	 * @param DOMDocument $document
	 */
	public function normalizeDOM( DOMDocument $document ) {
		$this->addXmlnsNamespace( $document );
		$this->ensureRootIsText( $document );
	}

	private function addXmlnsNamespace( DOMDocument $document ) {
		$namespace = $document->documentElement->namespaceURI;
		if ( $namespace !== null ) {
			return;
		}

		$document->documentElement->setAttribute( 'xmlns', TeiRegistry::TEI_NAMESPACE );
		$document->normalizeDocument();
	}

	private function ensureRootIsText( DOMDocument $document ) {
		if (
			$document->documentElement->nodeName === 'text' ||
			$document->documentElement->nodeName == 'TEI'
		) {
			// Nothing to do
		} elseif (
			in_array( $document->documentElement->nodeName, [ 'back', 'body', 'front', 'group' ] )
		) {
			$text = $this->createTeiElement( $document, 'text' );
			$text->appendChild( $document->documentElement );
			$document->appendChild( $text );
		} else {
			$body = $this->createTeiElement( $document, 'body' );
			$body->appendChild( $document->documentElement );
			$text = $this->createTeiElement( $document, 'text' );
			$text->appendChild( $body );
			$document->appendChild( $text );
		}
	}

	private function createTeiElement( DOMDocument $document, $name ) {
		return $document->createElementNS( TeiRegistry::TEI_NAMESPACE, $name );
	}
}
