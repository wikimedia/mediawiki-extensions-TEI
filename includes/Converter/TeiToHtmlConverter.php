<?php

namespace MediaWiki\Extension\Tei\Converter;

use DOMDocument;
use MediaWiki\BadFileLookup;
use RepoGroup;
use Title;

/**
 * @license GPL-2.0-or-later
 *
 * Converts TEI to HTML
 */
class TeiToHtmlConverter {

	/**
	 * @var RepoGroup
	 */
	private $repoGroup;

	/**
	 * @var BadFileLookup
	 */
	private $badFileLookup;

	/**
	 * @param RepoGroup $repoGroup
	 * @param BadFileLookup $badFileLookup
	 */
	public function __construct( RepoGroup $repoGroup, BadFileLookup $badFileLookup ) {
		$this->repoGroup = $repoGroup;
		$this->badFileLookup = $badFileLookup;
	}

	/**
	 * @param DOMDocument $teiDocument
	 * @param Title|null $pageTitle
	 * @return TeiToHtmlConversion
	 */
	public function convert( DOMDocument $teiDocument, Title $pageTitle = null ) {
		return new TeiToHtmlConversion(
			$this->repoGroup, $this->badFileLookup, $teiDocument, $pageTitle
		);
	}
}
