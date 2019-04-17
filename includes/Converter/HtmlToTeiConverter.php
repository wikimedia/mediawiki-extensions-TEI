<?php

namespace MediaWiki\Extension\Tei\Converter;

use DOMDocument;

/**
 * @license GPL-2.0-or-later
 *
 * Converts HTML to TEI
 */
class HtmlToTeiConverter {

	/**
	 * @param DOMDocument $htmlDocument
	 * @return HtmlToTeiConversion
	 */
	public function convert( DOMDocument $htmlDocument ) {
		return new HtmlToTeiConversion( $htmlDocument );
	}
}
