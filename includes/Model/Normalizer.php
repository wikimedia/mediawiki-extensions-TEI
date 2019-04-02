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
	}

	private function addXmlnsNamespace( DOMDocument $document ) {
		$namespace = $document->documentElement->namespaceURI;
		if ( $namespace !== null ) {
			return;
		}

		$document->documentElement->setAttribute( 'xmlns', TeiRegistry::TEI_NAMESPACE );
		$document->normalizeDocument();
	}
}
