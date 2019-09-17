<?php

namespace MediaWiki\Extension\Tei\Converter;

use DOMDocument;
use Title;

/**
 * @license GPL-2.0-or-later
 *
 * Converts TEI to HTML
 */
class TeiToHtmlConverter {

	/**
	 * @var FileLookup
	 */
	private $fileLookup;

	/**
	 * @param FileLookup $fileLookup
	 */
	public function __construct( FileLookup $fileLookup ) {
		$this->fileLookup = $fileLookup;
	}

	/**
	 * @param DOMDocument $teiDocument
	 * @param Title|null $pageTitle
	 * @return TeiToHtmlConversion
	 */
	public function convert( DOMDocument $teiDocument, Title $pageTitle = null ) {
		return new TeiToHtmlConversion( $this->fileLookup, $teiDocument, $pageTitle );
	}
}
